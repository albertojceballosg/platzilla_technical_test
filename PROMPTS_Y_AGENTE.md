# PROMPTS Y AGENTE — Prueba Técnica Platzilla

Este documento cubre los tres puntos pedidos en el enunciado:

1. **Auditoría** del código legacy y cómo se abordó.
2. **Consultas / uso de IA** durante el desarrollo (prompts representativos y sus resultados).
3. **Diseño de un "System Prompt"** para un agente de IA orientado a la **arquitectura
   multi-instancia** de Platzilla.

> Documentos complementarios: `docs/AUDITORIA_MYSQL.md` (números de la auditoría) y
> `docs/BACKLOG_MODERNIZACION.md` (qué se dejó fuera y por qué).

---

## 1. Metodología: cómo se usó la IA

El ejercicio se desarrolló con **Claude Code** (asistente de IA de Anthropic) como copiloto,
siguiendo un método deliberado en tres tiempos para **no romper código legacy que no se puede
recompilar fácilmente**:

1. **Auditar antes de tocar.** Medir el problema (cuántas llamadas, dónde, de qué tipo) antes de
   escribir una sola línea.
2. **Cambiar en incrementos verificables.** Cada cambio se validó funcionalmente contra la
   aplicación corriendo (peticiones reales, consultas a la BD), no solo con revisión estática.
3. **Documentar y versionar en pasos pequeños.** Commits granulares en la rama `development`,
   dejando `main` para la entrega final.

La IA se usó para cuatro cosas concretas: **auditar** el código a escala, **diagnosticar**
errores (root-cause), **refactorizar** con un patrón consistente, y **documentar** los hallazgos.

---

## 2. Auditoría asistida por IA (resumen)

Detalle completo en `docs/AUDITORIA_MYSQL.md`. Titulares:

- **434** llamadas a la extensión legacy `mysql_*` (eliminada en PHP 7) en ~40 archivos.
- Reparto: **35** en la librería `adodb/` (se resuelve cambiando de driver, no a mano) y
  **399** en código de aplicación.
- **Dos palancas de modernización** identificadas y **ambas aplicadas**:
  - **Palanca 1:** cambiar el driver de BD de `mysql` a `mysqli` (vía ADOdb) → moderniza todo lo
    que pasa por el wrapper `PearDatabase` sin tocar las 434 llamadas.
  - **Palanca 2 (PoC):** refactor manual de un módulo (`notificaciones.php`) de `mysql_*` al
    wrapper ADOdb, validado funcionalmente sobre mysqli.

---

## 3. Consultas a la IA (prompts representativos y resultados)

Ejemplos reales del tipo de indicaciones dadas a la IA y lo que produjeron. Muestran el
razonamiento, no solo el resultado.

### Consulta 1 — Dimensionar el problema
> *"Audita todas las llamadas `mysql_*` del proyecto, cuéntalas por función y por archivo, y
> **separa las que están en librerías de terceros de las del código de la aplicación**."*

**Resultado:** reveló que 35 de 434 llamadas están en ADOdb (librería) y que el 61 % de las
llamadas de aplicación se concentran en 8 archivos. Esto cambió la estrategia: no había que
reescribir 434 llamadas, sino **cambiar un driver** + refactorizar puntos concentrados.

### Consulta 2 — Diagnóstico de una regresión (root-cause)
> *"Al cambiar `db_type` a `mysqli` el login funciona pero el dashboard autenticado falla.
> Aísla si lo causó el cambio de driver o es preexistente, y encuentra la causa raíz."*

**Resultado:** comparando el flujo bajo `mysql` vs `mysqli` se descubrió que la arquitectura
multi-instancia abre una **segunda conexión** con usuario `usr_<instancia>` que **no existía**
en el entorno local. Se creó ese usuario y se **codificó su provisión** en
`db_init/01-instance-users.sql`. (Este hallazgo es la base del agente diseñado en la sección 5.)

### Consulta 3 — Refactor con patrón consistente y verificable
> *"Refactoriza `notificaciones.php` de `mysql_*` a la abstracción ADOdb que ya usa la clase,
> **sin cambiar el comportamiento**, y valida el resultado ejecutándolo contra la BD real."*

**Resultado:** se detectó que las llamadas crudas eran **fallbacks muertos** (usaban una
conexión `$this->gdb` nunca inicializada). Se colapsaron al camino moderno; 0 `mysql_*`
restantes; validado con `php -l` y un harness que ejecutó `query/fetch_array/num_rows` sobre
mysqli.

### Consulta 4 — Documentar decisiones de alcance
> *"Lista lo modernizable que NO abordaremos en esta PoC y **explica por qué** cada cosa se
> queda fuera."*

**Resultado:** `docs/BACKLOG_MODERNIZACION.md`, con criterio de priorización
**impacto × verificabilidad**.

### Lecciones capturadas (sutilezas técnicas encontradas con IA)
- En ADOdb, llamar `num_rows()` **antes** de un bucle `fetch_array()` mueve el cursor y vacía la
  iteración. Es un patrón preexistente, no introducido por el refactor.
- El driver `mysqli` de ADOdb ya venía incluido (`adodb-mysqli.inc.php`); modernizar era
  **configuración**, no reescritura de la librería.

---

## 4. La arquitectura multi-instancia (base del agente)

Platzilla es **multi-tenant**: una sola base de código sirve a muchas instancias (clientes),
cada una con **su propia base de datos**. Reglas deducidas del código real:

| Concepto | Regla | Fuente en el código |
|---|---|---|
| Enrutamiento | La instancia se resuelve por la **primera etiqueta del `HTTP_HOST`** | `index.php` (`$lstPlatsFijas`) |
| Base de datos | `pg_crm_<instancia>` | `InstanceDatabaseUtils.php`, `customerPortal2/include.php` |
| Usuario MySQL | `usr_<instancia>` | idem |
| Contraseña | `md5('usr_' + <instancia>)` | idem |
| Semilla de datos | Las tablas se **clonan de la madre** (`SHOW CREATE TABLE`) | `InstanceDatabaseUtils.php` |
| Instancia base | `madre` (plantilla / molde) | `config.inc.php` (`$platPrincipal = 'madre'`) |

En resumen, **dar de alta una instancia** implica de forma reproducible: crear la BD
`pg_crm_<instancia>`, crear el usuario `usr_<instancia>` con su contraseña derivada, otorgar
privilegios, clonar el esquema desde la madre y registrar la instancia.

---

## 5. System Prompt: Agente de Aprovisionamiento Multi-Instancia

Diseño de un **System Prompt** para un agente de IA que **automatiza el alta de nuevas
instancias** de Platzilla — exactamente el proceso que la sección 4 describe y que hoy está
disperso en el código legacy.

### 5.1 Qué debe saber el agente (contexto de dominio)
El prompt inyecta las reglas de la arquitectura para que el agente no las adivine: convención de
nombres, derivación de credenciales, origen del esquema y el modelo de enrutamiento.

### 5.2 El System Prompt

```text
# ROL
Eres el Agente de Aprovisionamiento de Platzilla, un CRM multi-instancia (multi-tenant)
sobre PHP/MySQL. Tu único objetivo es dar de alta, verificar o dar de baja instancias de
cliente de forma segura, reproducible e idempotente.

# CONOCIMIENTO DE LA ARQUITECTURA (reglas invariables)
- Cada instancia se identifica por un <codigo> en minúsculas, sin espacios (p.ej. "acme").
- La aplicación resuelve la instancia por la PRIMERA etiqueta del HTTP_HOST del navegador.
- Por cada instancia existen, con nombres DERIVADOS del código (no los inventes):
    · Base de datos : pg_crm_<codigo>
    · Usuario MySQL : usr_<codigo>
    · Contraseña    : md5("usr_" + <codigo>)     # hash hex de 32 caracteres
- El esquema de tablas de una instancia se CLONA desde la instancia plantilla "madre"
  (base de datos pg_crm_madre). "madre" nunca se modifica ni se borra.

# PROCEDIMIENTO DE ALTA (ejecútalo en este orden)
1. Validar el <codigo> (regex ^[a-z][a-z0-9_]{2,30}$). Si no cumple, aborta y explica.
2. Comprobar idempotencia: si la BD o el usuario ya existen, NO recrear; reportar estado.
3. Crear la base de datos pg_crm_<codigo> (utf8) si no existe.
4. Crear el usuario usr_<codigo> con la contraseña derivada y otorgarle privilegios
   ACOTADOS a pg_crm_<codigo>.* (nunca privilegios globales *.*).
5. Clonar el esquema desde pg_crm_madre (estructura; los datos según política de la semilla).
6. Registrar la instancia (tabla vtiger_instances) y dejar constancia del HTTP_HOST esperado.
7. Verificar: conectar como usr_<codigo> y ejecutar una consulta de sanidad. Reportar OK/FALLO.

# GUARDARRAÍLES (obligatorios)
- IDEMPOTENCIA: ejecutar el alta dos veces no debe duplicar ni corromper nada.
- MÍNIMO PRIVILEGIO: los GRANT se limitan a la BD de la instancia.
- NO DESTRUCTIVO: cualquier DROP/DELETE/baja requiere confirmación explícita del operador y
  jamás puede afectar a "madre" ni a otra instancia distinta de la indicada.
- IDIOMA DE CREDENCIALES: la contraseña SIEMPRE es md5("usr_"+<codigo>); no aceptes otra.
- TRAZABILIDAD: antes de actuar, muestra el plan (qué BD/usuario/privilegios) y, al terminar,
  un resumen del resultado y cómo revertirlo.

# ENTRADA
Un <codigo> de instancia y la acción: "alta" | "verificar" | "baja".

# SALIDA
1) Plan de acciones.  2) Comandos SQL/pasos ejecutados.  3) Resultado de la verificación.
En caso de error, la causa raíz y el paso exacto donde falló.
```

### 5.3 Por qué está diseñado así (decisiones)
- **Reglas como invariantes, no como sugerencias:** el nombre de BD/usuario y la contraseña se
  *derivan* del código. Al fijarlas en el prompt, el agente no puede introducir credenciales
  inconsistentes con lo que la aplicación espera en runtime (fue justo el bug `usr_madre` que
  encontramos).
- **Idempotencia y mínimo privilegio:** el entorno original creaba usuarios con privilegios
  amplios; el prompt fuerza `GRANT` acotado a la BD de la instancia, reduciendo el radio de
  daño entre tenants.
- **"madre" es sagrada:** al ser la plantilla, un borrado accidental rompería todas las altas
  futuras; el prompt lo prohíbe explícitamente.
- **Plan → ejecución → verificación:** el mismo método de tres tiempos que usamos en el
  refactor, ahora codificado para que el agente siempre valide su propio trabajo.

### 5.4 Ejemplo de uso
> **Operador:** `alta acme`
>
> **Agente (resumido):**
> 1. *Plan:* crear `pg_crm_acme`, usuario `usr_acme` (pass = md5("usr_acme")), GRANT sobre
>    `pg_crm_acme.*`, clonar esquema de `pg_crm_madre`, registrar instancia, verificar.
> 2. *Ejecución:* (comandos SQL correspondientes).
> 3. *Verificación:* conexión como `usr_acme` OK; consulta de sanidad OK.
> 4. *Acceso esperado:* `http://acme.platzilla.local` → instancia `acme`.

---

## 6. Conclusión

La IA se usó como **acelerador con criterio**: auditar a escala, diagnosticar causas raíz,
refactorizar con un patrón verificable y documentar decisiones. El resultado es una modernización
**demostrable** (driver migrado + módulo refactorizado y probado) y un diseño de agente que
**captura el conocimiento de la arquitectura multi-instancia** para automatizar su operación —
convirtiendo un proceso legacy disperso en un procedimiento seguro, idempotente y auditable.
