# PROMPTS Y AGENTE — Prueba Técnica Platzilla

Este documento cubre lo pedido en el enunciado:

1. **Auditoría** del código legacy y cómo se abordó.
2. **Iteraciones y consultas exactas a la IA**, incluyendo **cómo se resolvieron los errores**.
3. **Diseño del "System Prompt"** para un Agente de IA que gestione, sobre una arquitectura
   **multi-tenant de bases de datos individuales**, los **cambios por cliente** y las
   **migraciones estructurales** hacia **PHP 8.4 + MariaDB 10.5**, sin afectar a otras instancias.

> **Objetivo de modernización (según el enunciado):** llevar el código de **PHP 5.6 → PHP 8.4**
> y la base de datos de **MySQL 5.6 → MariaDB 10.5**. No se exige que el sitio quede 100 %
> funcional; se evalúa la calidad de lo modernizado y el criterio técnico.
>
> Documentos complementarios: `docs/AUDITORIA_MYSQL.md` (números) y
> `docs/BACKLOG_MODERNIZACION.md` (qué se dejó fuera y por qué).

---

## 1. Metodología: cómo se usó la IA

Se usó **Claude Code** (asistente de IA de Anthropic) como copiloto, con un método deliberado en
tres tiempos para **no romper código legacy que no se puede recompilar fácilmente**:

1. **Auditar antes de tocar** — medir el problema (cuántas llamadas, dónde, de qué tipo).
2. **Cambiar en incrementos verificables** — validar cada cambio contra la app corriendo.
3. **Documentar y versionar en pasos pequeños** — commits granulares en `development`, dejando
   `main` para la entrega final.

---

## 2. Auditoría asistida por IA (resumen)

Detalle en `docs/AUDITORIA_MYSQL.md`. Titulares:

- **434** llamadas a la extensión legacy `mysql_*` (eliminada en PHP 7) en ~40 archivos: 35 en la
  librería `adodb/` y 399 en código de aplicación.
- Además, el análisis reveló otras incompatibilidades hacia PHP 8.4 concentradas sobre todo en
  **librerías**: `ereg`/`split` (~256), `each()` (~90), `create_function()` (~13), acceso con
  llaves `$var{...}` (~503).
- **Dos palancas de modernización aplicadas:**
  - **Palanca 1** — driver de BD `mysql` → `mysqli` (vía ADOdb): compatible con PHP 8.4 y con
    MariaDB 10.5, sin tocar las 434 llamadas.
  - **Palanca 2 (PoC)** — refactor manual del módulo `notificaciones.php` al wrapper ADOdb,
    validado funcionalmente.

---

## 3. Iteraciones y consultas a la IA

### 3.1 Prompts representativos y su resultado

**Consulta 1 — Dimensionar el problema**
> *"Audita todas las llamadas `mysql_*`, cuéntalas por función y archivo, y **separa librerías de
> terceros del código de la aplicación**."*

→ Reveló que 35/434 están en ADOdb y que el 61 % de las de aplicación se concentran en 8
archivos. Cambió la estrategia: no reescribir 434 llamadas, sino **cambiar un driver** +
refactorizar puntos concentrados.

**Consulta 2 — Elegir un objetivo de PoC verificable**
> *"De los archivos con `mysql_*`, ¿cuáles golpean la BD local (testeable) y cuáles van a
> sistemas externos o credenciales que no tenemos?"*

→ Descartó `Users.php` (integra OrangeHRM/ProcessMaker/dotProject, no verificable en local) y
eligió `notificaciones.php` (usa la BD de la instancia, testeable).

**Consulta 3 — Refactor con patrón consistente**
> *"Refactoriza `notificaciones.php` a la abstracción ADOdb que la clase ya usa, **sin cambiar el
> comportamiento**, y valida ejecutándolo contra la BD real."*

→ Se detectó que las llamadas crudas eran **fallbacks muertos** (conexión `$this->gdb` nunca
inicializada). Se colapsaron al camino ADOdb; 0 `mysql_*` restantes.

### 3.2 Cómo se resolvieron los errores (iteración de depuración)

El valor real del uso de IA estuvo en el **diagnóstico iterativo**. Casos concretos:

**Error A — `Fatal: unsupported dbtype "mysqli"` al cambiar el driver.**
- *Síntoma:* tras poner `db_type='mysqli'`, algunas pantallas lanzaban excepción.
- *Iteración con IA:* "busca comparaciones exactas `== 'mysql'` / `case 'mysql'` que no
  contemplen `mysqli`". Encontró `PearDatabase::sql_concat()` con un `switch` que lanzaba en el
  `default`.
- *Resolución:* añadir `case 'mysqli':` junto a `case 'mysql':`. Única incompatibilidad real del
  flip en el código de vtiger.

**Error B — `Access denied for user 'usr_madre'` en el dashboard tras migrar el driver.**
- *Síntoma:* el login funcionaba (302) pero el dashboard autenticado fallaba.
- *Iteración con IA:* "aísla si lo causó el driver o es preexistente" → se comparó el mismo flujo
  bajo `mysql` vs `mysqli`. Bajo `mysql` funcionaba; bajo `mysqli` no. Luego "encuentra de dónde
  sale `usr_madre`, no está en config" → se rastreó a `'usr_'.$_SESSION['plat']` con
  `password = md5('usr_'+instancia)`.
- *Causa raíz:* la arquitectura multi-tenant abre una **segunda conexión por instancia** con un
  usuario derivado que **no existía** en el entorno local (solo estaba `superuser`).
- *Resolución:* provisionar el usuario y **codificarlo** en `db_init/01-instance-users.sql` para
  que sea reproducible. (Este hallazgo es la semilla del agente de la sección 5.)

**Error C — Un bucle `fetch_array()` no iteraba durante la validación.**
- *Síntoma:* `num_rows()` devolvía 3 pero el `while (fetch_array())` no entraba.
- *Iteración con IA:* "¿por qué?" → en ADOdb, llamar `num_rows()` **antes** del bucle mueve el
  cursor. Es un patrón **preexistente**, no introducido por el refactor. Se documentó como
  lección, no como bug a corregir en la PoC.

**Error D — Reproducibilidad: `chmod` del README fallaba en un clon limpio.**
- *Síntoma:* al probar desde cero, `cache/logs/storage` no existían.
- *Causa:* están en `.gitignore`, así que no vienen en el clon.
- *Resolución:* el `entrypoint.sh` ahora hace `mkdir -p` + `chmod` de las carpetas de escritura
  en cada arranque. Re-validado recreando el contenedor desde cero.

---

## 4. La arquitectura multi-tenant (base del agente)

Platzilla es **multi-tenant con base de datos por cliente**: una sola base de código sirve a
todas las instancias; cada instancia tiene **su propia BD**. Reglas deducidas del código real:

| Concepto | Regla | Fuente |
|---|---|---|
| Enrutamiento | La instancia se resuelve por la **primera etiqueta del `HTTP_HOST`** | `index.php` (`$lstPlatsFijas`) |
| Base de datos | `pg_crm_<instancia>` | `InstanceDatabaseUtils.php`, `customerPortal2/include.php` |
| Usuario MySQL | `usr_<instancia>` | idem |
| Contraseña | `md5('usr_' + <instancia>)` | idem |
| Esquema | Las tablas se **clonan de la madre** (`SHOW CREATE TABLE`) | `InstanceDatabaseUtils.php` |
| Plantilla | `madre` (base de datos `pg_crm_madre`) | `config.inc.php` |

El punto crítico: **el código es central, pero un cambio estructural (p. ej. `ALTER TABLE`) debe
aplicarse a la BD de cada instancia por separado**, sin afectar a las demás. Eso es exactamente lo
que el agente debe gobernar.

---

## 5. System Prompt: Agente Multi-Tenant de Cambios y Migración

Diseño del **System Prompt** para un agente de IA alojado en el servidor que (a) aplica
**cambios/personalizaciones por cliente** sobre bases de datos individuales, y (b) ejecuta la
**migración estructural a MariaDB 10.5**, con reglas de seguridad que impiden dañar otras
instancias.

### 5.1 El System Prompt

```text
# ROL
Eres el Agente de Operaciones de Base de Datos de Platzilla, un CRM MULTI-TENANT donde el
código es central y compartido, pero CADA CLIENTE tiene su propia base de datos independiente
(pg_crm_<instancia>). La instancia "madre" (pg_crm_madre) es la PLANTILLA de la que derivan las
demás. Tu misión es aplicar cambios de esquema por cliente y migraciones a MariaDB 10.5 de
forma segura, aislada, reversible y auditable.

# MODELO MENTAL (invariables)
- Convención de nombres derivada del <codigo> de instancia (nunca la inventes):
    · Base de datos : pg_crm_<codigo>
    · Usuario MySQL : usr_<codigo>   (conéctate SIEMPRE con este usuario, no con root)
- El código PHP es central: un cambio de código afecta a TODAS las instancias; un cambio de
  ESQUEMA afecta solo a la BD donde lo apliques. No confundas ambos planos.
- Toda instancia deriva su esquema de "madre". Si un cambio estructural debe existir en las
  instancias futuras, hay que aplicarlo también a "madre" (la plantilla) de forma explícita.

# ALCANCE DE UN CAMBIO POR CLIENTE
Cuando te pidan un cambio para el cliente <codigo>:
1. Confirma el destino EXACTO: solo pg_crm_<codigo>. Si la petición no nombra la instancia,
   detente y pídela. Jamás uses comodines (*.*) ni "todas las instancias" sin autorización
   explícita y un plan de despliegue por lotes.
2. Clasifica el cambio: ¿es solo de datos, o ESTRUCTURAL (ALTER/CREATE/DROP)? ¿debe quedarse en
   este cliente o promoverse a "madre" para las instancias futuras? Explícalo antes de actuar.

# REGLAS DE SEGURIDAD PARA MIGRACIONES ESTRUCTURALES (obligatorias)
- AISLAMIENTO: cada sentencia DDL nombra explícitamente `pg_crm_<codigo>`. Está PROHIBIDO
  ejecutar DDL sin base de datos calificada o que pueda alcanzar otra instancia.
- "MADRE" PROTEGIDA: no modifiques ni borres "madre" salvo instrucción explícita de "promover a
  plantilla"; nunca la borres.
- RESPALDO PREVIO: antes de cualquier ALTER/DROP, genera un respaldo lógico de las tablas
  afectadas (mysqldump acotado) y un SCRIPT DE ROLLBACK. Recuerda que en MySQL/MariaDB el DDL
  hace commit implícito y NO es transaccional: la reversibilidad se garantiza con respaldo +
  rollback, no con BEGIN/ROLLBACK.
- DRY-RUN PRIMERO: muestra el DDL exacto y su impacto estimado (filas, bloqueos, tiempo) y espera
  confirmación antes de ejecutar en producción.
- IDEMPOTENCIA: usa IF [NOT] EXISTS y comprobaciones previas; re-ejecutar no debe duplicar ni
  fallar.
- MÍNIMO PRIVILEGIO: opera como usr_<codigo>, acotado a su propia BD.
- VALIDACIÓN POSTERIOR: tras aplicar, verifica el esquema resultante y ejecuta una consulta de
  sanidad. Reporta OK/FALLO y cómo revertir.
- UNA INSTANCIA A LA VEZ: en despliegues multi-instancia, procede por lotes, validando cada una
  antes de continuar; si una falla, DETENTE y reporta (no sigas con las demás).

# LÓGICA DE MIGRACIÓN A MariaDB 10.5 (MySQL 5.6 -> MariaDB 10.5)
Al migrar o generar DDL nuevo, produce SQL compatible con MariaDB 10.5:
- Juego de caracteres: migra de utf8 (utf8mb3) a utf8mb4 / utf8mb4_unicode_ci.
- Motor: asegura ENGINE=InnoDB (evita MyISAM para integridad y bloqueos por fila).
- sql_mode: MariaDB 10.5 aplica modo ESTRICTO por defecto; valida datos "sucios" (fechas
  0000-00-00, valores fuera de rango) ANTES de migrar, o fallará el INSERT/ALTER.
- Palabras reservadas: entrecomilla con backticks identificadores que hayan pasado a ser
  reservados en MariaDB 10.5.
- Usuarios/privilegios: usa la sintaxis de MariaDB 10.5 (CREATE USER ... IDENTIFIED BY ...;
  GRANT ...). No dependas de formas obsoletas de MySQL 5.6.
- Estrategia de despliegue: migra "madre" primero como CANARIO, valida, y luego las instancias
  una a una con el mismo procedimiento (respaldo -> dry-run -> aplicar -> validar).

# ENTRADA
Una petición en lenguaje natural con: la acción (cambio de esquema | migración | verificación),
la <instancia> objetivo, y el detalle del cambio.

# SALIDA (siempre en este orden)
1) Interpretación y plano afectado (código central vs esquema de esta instancia).
2) Plan: destino exacto, respaldo, DDL propuesto (compatible MariaDB 10.5) y rollback.
3) Dry-run / impacto estimado.
4) Tras confirmación: ejecución + validación posterior.
5) Si algo falla: causa raíz, paso exacto y cómo revertir con el rollback generado.
```

### 5.2 Por qué está diseñado así (responde a las preguntas del enunciado)

**"¿Cómo gestionar cambios para un cliente específico si el código es central pero las BD son
individuales?"**
El prompt separa explícitamente **dos planos**: el *código* (central, afecta a todos) y el
*esquema* (por instancia). Obliga a nombrar la instancia destino, prohíbe comodines, y fuerza a
declarar si el cambio debe **promoverse a la plantilla "madre"** para que lo hereden las
instancias futuras. Así un mismo "cambio" no se aplica por error a todos los clientes.

**"¿Qué reglas de seguridad para migraciones estructurales sin afectar otras instancias?"**
Aislamiento (DDL siempre calificado con `pg_crm_<codigo>`), "madre" protegida, respaldo + script
de rollback antes de todo `ALTER/DROP` (porque el DDL en MySQL/MariaDB no es transaccional),
dry-run con confirmación, idempotencia, mínimo privilegio (`usr_<codigo>`), validación posterior
y despliegue **una instancia a la vez** que se detiene ante el primer fallo.

**Cobertura de MariaDB 10.5:** el bloque de migración codifica las diferencias reales frente a
MySQL 5.6 (utf8→utf8mb4, InnoDB, `sql_mode` estricto, palabras reservadas, sintaxis de usuarios)
y una estrategia de despliegue con "madre" como canario.

### 5.3 Ejemplo de uso
> **Operador:** *"Añade el campo `nif_secundario VARCHAR(20)` a la tabla `vtiger_account` del
> cliente `acme`."*
>
> **Agente (resumido):**
> 1. *Plano:* es un cambio de **esquema**, afecta solo a `pg_crm_acme` (no al código central).
> 2. *¿Promover a madre?* pregunta si las instancias futuras deben incluirlo.
> 3. *Plan:* respaldo de `vtiger_account`; DDL `ALTER TABLE pg_crm_acme.vtiger_account ADD COLUMN
>    IF NOT EXISTS nif_secundario VARCHAR(20) ...` (utf8mb4); script de rollback (`DROP COLUMN`).
> 4. *Dry-run:* muestra impacto; espera confirmación.
> 5. *Ejecuta como `usr_acme`*, valida el esquema, reporta OK y cómo revertir.

### 5.4 Evidencia empírica del aislamiento (la regla clave, demostrada)

La regla de **AISLAMIENTO** no es teórica: se validó en un MariaDB 10.5 real. Con la "madre"
(`pg_crm_madre`) y una 2ª instancia (`pg_crm_cliente_x`) cargadas, se aplicó una migración
estructural **calificada por BD** solo a la instancia:

```sql
ALTER TABLE pg_crm_cliente_x.vtiger_courses
  ADD COLUMN certificado_habilitado TINYINT(1) NOT NULL DEFAULT 0,
  MODIFY COLUMN videotype ENUM('YOUTUBE','VIMEO','DAILYMOTION') DEFAULT NULL;
```

Comparando la "madre" antes y después (hash de su DDL + `CHECKSUM TABLE` de sus datos):

| Medida de `pg_crm_madre.vtiger_courses` | Antes | Después |
|---|---|---|
| Hash de DDL | `dfc00df8…` | `dfc00df8…` (idéntico) |
| `CHECKSUM TABLE` | `3741947163` | `3741947163` (idéntico) |
| Columna `certificado_habilitado` | no existe | **sigue sin existir** |

`cliente_x` recibió el cambio; la "madre" quedó **bit a bit intacta**. Esto confirma que, con DDL
calificado por `pg_crm_<codigo>`, una migración por-cliente **no puede alcanzar otra instancia** —
justo la garantía que el System Prompt exige. (Procedimiento reproducible análogo al de
`docs/COMPATIBILIDAD_MARIADB105.md`.)

---

## 6. Conclusión

La IA se usó como **acelerador con criterio**: auditar a escala, **diagnosticar causas raíz de
forma iterativa** (errores A–D), refactorizar con un patrón verificable y documentar decisiones.
El resultado es una modernización **demostrable** encaminada a **PHP 8.4 + MariaDB 10.5**, y un
diseño de agente que captura el conocimiento de la arquitectura multi-tenant para **gestionar
cambios por cliente y migraciones estructurales sin afectar a otras instancias** — convirtiendo un
proceso legacy y arriesgado en uno seguro, aislado, idempotente y auditable.
