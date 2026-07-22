# Backlog de modernización — decisiones de alcance

> Registro de elementos **modernizables que se detectaron pero NO se abordaron** en esta
> Prueba de Concepto (48 h), con la justificación de por qué se dejaron fuera. El objetivo es
> demostrar que se entiende el alcance completo y que la priorización fue deliberada, no por
> desconocimiento. Fecha: 2026-07-21.

## Plan de ejecución (ventana de 24 h) — prioridad × impacto ÷ riesgo

> Backlog acotado a lo realizable en ~24 h, con impacto real en entregables y sin dañar la
> estabilidad del stack 5.6 vivo. Las sondas empíricas corren en contenedores desechables
> (riesgo de estabilidad nulo); solo el refactor de código toca el árbol de la app.

| # | Tarea | Entregable | Esfuerzo | Riesgo estabilidad |
|---|---|---|---:|---|
| T1 | Commit saneado ENUM + doc MariaDB 10.5 + `--sql-mode` | 1, 2 | 15 min | Nulo (aditivo) |
| T2 | Sonda empírica PHP 8.4 (`php -l` + ejecución) → `docs/COMPATIBILIDAD_PHP84.md` | 1 | 2-3 h | Nulo (contenedor `php:8.4`) |
| T4 | Cerrar eje BD: DEFINER/rutinas como `superuser` + integridad 5.6 vs 10.5 | 1, 3 | 1-1.5 h | Nulo (contenedor) |
| T3 | PoC refactor PHP 8.4 sobre porción representativa (notificaciones) | 1 | 3-4 h | Medio, acotado a esa porción |
| T5 | Evidencia multi-instancia: replay de migración estructural aislada | 3 | 2 h | Nulo (contenedor) |
| T6 | Consolidar: enlazar docs, revisar commits, preparar merge a `main` | 1,2,3 | 1 h | Bajo |

Secuencia: **T1 → T2 → T4 → T3 → T5 → T6**. Sacrificables si aprieta el tiempo: T3 y T5
(T2+T4 ya equilibran los dos ejes de modernización).

## Segunda tanda de modernizaciones puntuales (en curso)

Backlog de mejoras puntuales detectadas con evidencia, atacadas por prioridad (impacto ÷ riesgo):

| # | Tarea | Riesgo | Estado |
|---|---|---|---|
| M1 | 6 fixes PHP 8.4 mecánicos (`$var{}`→`$var[]`, `&new`) en app-code | Muy bajo (retro-compat) | ✅ hecho |
| M2 | Higiene `docker-compose`: quitar `version:` obsoleta + healthcheck `db` | Bajo | ✅ hecho |
| M3 | Externalizar credenciales de BD a variables de entorno (12-factor) | Bajo-medio (bootstrap) | ✅ hecho |
| M4 | 4 ficheros con errores 8.4 **estructurales** (no mecánicos) | Medio (caso a caso) | ✅ triado |

M1 cubre: `include/utils/{encryption,GraphUtils,InstanceCreator.class}.php`,
`modules/Calendar/Appointment.php`, `modules/Settings/EditCustomButtons.php`,
`modules/System/includes/common_functions.php`. M4 cubre:
`modules/orden_de_trabajo/handlers/taskToWork_methods.php`, `modules/Calendar/calendarLayout.2.php`,
`modules/System/includes/XPath.class.php`, `vtlib/ModuleDir/5.4.0/ModuleFile.php`.

## Tercera tanda: funciones eliminadas, charset y deprecaciones (en curso)

| # | Tarea | Evidencia | Riesgo | Estado |
|---|---|---|---|---|
| N1 | `create_function()` → closures/eval | 1 app (FieldCalculate) + 6 en libs | Bajo | ✅ hecho (app) |
| N4 | Medir/documentar "próxima ola" de deprecaciones 8.x | 21 avisos compile-time en 15 ficheros + runtime | Nulo | ✅ hecho |
| N2 | BD `utf8`(utf8mb3) → `utf8mb4` | 1200/1264 directo; 64 bloqueadas por FK (ERROR 1832) | Medio | ✅ validado+documentado |
| N5 | `utf8_encode/decode` → `mb_convert_encoding` | tanda: 9 en 2 ficheros; ~63 restantes (mismo patron) | Bajo-medio | ✅ tanda hecha |
| N3 | `each()` → `foreach` | 2 app genuinas (resto en libs/JS) | Medio | ✅ hecho |
| N6 | Higiene: `.gitattributes` (CRLF→LF) + target 8.4 en Dockerfile | — | Bajo | pendiente |

Nota N1: los 6 `create_function` restantes están en librerías de terceros (Smarty, webmail,
iCal, vtlib/thirdparty, ADOdb) → se actualizan, no se parchean a mano.

## Cuarta tanda: ereg/split, deprecaciones retro-compatibles, utf8 (en curso)

Nota transversal: mientras el runtime vivo sea **PHP 5.6**, todo cambio debe ser
**retro-compatible con 5.6**. Algunas correcciones 8.x (p. ej. tipos `?Type` nullable) romperían
5.6 → se **difieren** hasta que el `Dockerfile` pase a 8.4.

| # | Tarea | Evidencia | Retro-compat 5.6 | Estado |
|---|---|---|---|---|
| O1 | `ereg/split` → `preg_*`/`explode` | 5 ficheros app genuinos (resto en libs/JS) | ✅ | ✅ hecho |
| O3 | Deprecaciones 8.x retro-compatibles | `${expr}`→`{${expr}}`, reordenar params, `#[\ReturnTypeWillChange]` | ✅ | pendiente |
| O2 | Completar `utf8_encode/decode` → `mb_convert_encoding` | ~63 restantes | ✅ | pendiente |
| — | Implicit nullable `?Type $x = null` (14, de N4) | **DIFERIDO**: rompe 5.6; se hará al pasar el runtime a 8.4 | ❌ | diferido |

O1 cubrió: `include/utils/utils.php`, `modules/System/systemconfig.php` (ya libre de funciones
eliminadas), `modules/Calendar/{RepeatEvents,iCalExport}.php`, `customerPortal2/HelpDesk/TicketsList.php`.
`modules/Dashboard/Forms.php` era `String.split` de **JavaScript** (falso positivo). El resto de
`ereg/split` del árbol están en librerías (phpsysinfo, Image, XPath).

## Lo que SÍ se hizo (contexto)

- **Palanca 1 — migración del driver de BD `mysql` → `mysqli`** (vía ADOdb): moderniza todo el
  acceso que pasa por `PearDatabase`. Aplicada y verificada. Ver `AUDITORIA_MYSQL.md`.
- **Palanca 2 — PoC de refactor manual:** `modules/notificaciones/notificaciones.php`
  (7 llamadas crudas eliminadas, enrutadas por el wrapper ADOdb; 0 `mysql_*` restantes;
  validado funcionalmente sobre mysqli).

## Reevaluación (2026-07-22): "inabordable" → mayormente abordable

Revisión del backlog tras las tandas T/M/N. La etiqueta original "NO ahora" era casi siempre
**"no verificable localmente"** o **"es una librería"**, no imposibilidad técnica:

- **Hecho desde entonces:** credenciales de `config.inc.php` (M3), credenciales hardcodeadas de
  Notifications (externalizadas, ver más abajo), CRLF→LF como política (N6).
- **Abordable fabricando un mock:** `Users.php` (HR/ProcessMaker/dotProject) y `security.php`
  conectan con **arrays de configuración** (`$db_hrm`, `$db_process`, `$db_login`), no con hosts
  fijos → se levanta una BD *stub* con el esquema mínimo, se apunta la config ahí y el refactor
  `mysql_*`→`mysqli` pasa a ser testeable. El patrón ya está documentado.
- **Actualizar dependencia (no parchear):** ADOdb — confirmado empíricamente (D3/N1/N3) que tiene
  bloqueos 8.4 más allá de `mysql_*` (`unset $this`, ficheros que no parsean) → subir a una versión
  de ADOdb compatible con 8.4 + regresión.
- **Irreducible localmente:** validar contra los sistemas externos **reales** (OrangeHRM/
  ProcessMaker/dotProject de producción) requiere acceso a ellos — pero eso es *validación de
  integración*, no *modernización*.

## Backlog: modernizable, NO ahora (estado revisado)

### 1. `modules/Users/Users.php` — 103 llamadas crudas a sistemas externos
- **Qué es:** al crear/editar usuarios, sincroniza con OrangeHRM, ProcessMaker y dotProject
  mediante conexiones `mysql_connect` directas a esas BD externas.
- **Por qué no ahora:** esos sistemas **no existen en el entorno local**, así que el refactor
  sería mecánico pero **no verificable funcionalmente**. Modernizar sin poder probar introduce
  riesgo alto en un flujo crítico (alta de usuarios). Además, la parte de `Users.php` que sí se
  ejercita (login) **ya usa ADOdb**.
- **Cómo se haría:** `mysql_connect/query/select_db` → `mysqli_*` (con inversión de argumentos),
  validado contra instancias de prueba de cada sistema externo.

### 2. `customerPortal2/Notifications/*` — credenciales hardcodeadas ✅ HECHO (parte seguridad)
- **Qué era:** `mysql_connect('127.0.0.1:3306','timeuser','Eceptu.2011', true)` — credenciales
  **en código fuente**, repetidas en **10 ficheros**.
- **Hecho:** el secreto se sacó del código a variables de entorno (`getenv('NOTIF_DB_*')`,
  inyectadas por `docker-compose`, valor real en `.env` no versionado, default vacío). 0
  apariciones de la password en el código. Validado `php -l` en 8.4 y 5.6.
- **Pendiente (no testeable):** el refactor de driver `mysql_connect`→`mysqli` de estos ficheros
  sigue sin poder validarse en local (el host `127.0.0.1:3306` de la BD de notificaciones no
  existe aquí); abordable con un mock de esa BD.

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

- **Credenciales de BD en `config.inc.php`:** ✅ **hecho (M3)** — externalizadas a variables de
  entorno con `getenv()`, inyectadas por `docker-compose`; el secreto ya no vive en el código.
  (Las credenciales hardcodeadas del portal de notificaciones —punto 2 de arriba— siguen
  pendientes por no ser testeables en local.)
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
