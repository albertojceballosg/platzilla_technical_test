# Backlog de modernización — decisiones de alcance

> Registro de elementos **modernizables que se detectaron pero NO se abordaron** en esta
> Prueba de Concepto (48 h), con la justificación de por qué se dejaron fuera. El objetivo es
> demostrar que se entiende el alcance completo y que la priorización fue deliberada, no por
> desconocimiento. Fecha: 2026-07-21.

## Lo que SÍ se hizo (contexto)

- **Palanca 1 — migración del driver de BD `mysql` → `mysqli`** (vía ADOdb): moderniza todo el
  acceso que pasa por `PearDatabase`. Aplicada y verificada. Ver `AUDITORIA_MYSQL.md`.
- **Palanca 2 — PoC de refactor manual:** `modules/notificaciones/notificaciones.php`
  (7 llamadas crudas eliminadas, enrutadas por el wrapper ADOdb; 0 `mysql_*` restantes;
  validado funcionalmente sobre mysqli).

## Backlog: modernizable, NO ahora

### 1. `modules/Users/Users.php` — 103 llamadas crudas a sistemas externos
- **Qué es:** al crear/editar usuarios, sincroniza con OrangeHRM, ProcessMaker y dotProject
  mediante conexiones `mysql_connect` directas a esas BD externas.
- **Por qué no ahora:** esos sistemas **no existen en el entorno local**, así que el refactor
  sería mecánico pero **no verificable funcionalmente**. Modernizar sin poder probar introduce
  riesgo alto en un flujo crítico (alta de usuarios). Además, la parte de `Users.php` que sí se
  ejercita (login) **ya usa ADOdb**.
- **Cómo se haría:** `mysql_connect/query/select_db` → `mysqli_*` (con inversión de argumentos),
  validado contra instancias de prueba de cada sistema externo.

### 2. `customerPortal2/Notifications/*` — credenciales hardcodeadas
- **Qué es:** `mysql_connect('127.0.0.1:3306','timeuser','Eceptu.2011', true)` — credenciales
  **en código fuente**.
- **Por qué no ahora:** (a) la conexión apunta a un host/usuario que no existe en local
  (no testeable); (b) es un **hallazgo de seguridad** que merece su propio tratamiento
  (mover credenciales a configuración/variables de entorno), no solo un cambio de API.
- **Prioridad:** media-alta como deuda de **seguridad**, independiente de la migración mysqli.

### 3. Los ~30 archivos restantes con `mysql_connect()` crudo
- **Por qué no ahora:** son **heterogéneos** (sistemas externos, portal de clientes, conexiones
  por-instancia). Requieren tratamiento caso por caso y validación individual. Fuera del alcance
  de una PoC de 48 h. El **patrón de migración** ya está documentado en `AUDITORIA_MYSQL.md`.

### 4. `adodb/` — 35 llamadas `mysql_*` en la librería
- **Por qué no ahora (ni nunca a mano):** es **librería de terceros**. Editarla a mano rompería
  la trazabilidad y la mantenibilidad. Ya quedó resuelto por la **Palanca 1** (usar el driver
  `adodb-mysqli.inc.php` que la propia librería incluye). La ruta correcta a futuro es
  **actualizar ADOdb**, no parchearla.

### 5. `include/security.php` — 12 llamadas (BD de login `$db_login`)
- **Por qué no ahora:** depende de una **BD de login separada** cuya disponibilidad en local no
  está confirmada. Testeable solo si se replica esa BD. Pendiente de confirmar destino.

## Otros hallazgos de modernización (no-mysql) anotados

- **Credenciales de BD en `config.inc.php`:** deberían externalizarse a variables de entorno del
  contenedor (12-factor). No ahora: cambiaría el modelo de arranque de vtiger; requiere adaptar
  el `entrypoint.sh`.
- **Password por-instancia = `md5('usr_'+instancia)`:** esquema **predecible/débil**. Nota de
  seguridad para rediseño del provisioning multi-instancia.
- **Fin de línea CRLF (Windows)** en parte del código: cosmético; normalizar a LF sería ruidoso
  en el histórico ahora. Candidato a un commit dedicado con `.gitattributes`.
- **`error_reporting(E_ERROR)` global** oculta warnings/deprecations: útil bajarlo temporalmente
  durante el refactor para cazar usos de APIs eliminadas, pero es un cambio transversal.

## Criterio de priorización aplicado

Se priorizó **impacto × verificabilidad**: primero lo que moderniza mucho con bajo riesgo y es
comprobable en el entorno local (driver + un módulo que golpea la BD local), dejando para
después lo que no se puede validar aquí (integraciones externas) o que es deuda de otra
naturaleza (seguridad, librerías de terceros).
