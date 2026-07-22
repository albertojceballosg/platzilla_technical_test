# Auditoría de código legacy `mysql_*` — Platzilla

> Análisis del uso de la extensión nativa `mysql` de PHP (obsoleta desde PHP 5.5, **eliminada
> en PHP 7.0**) como base para la modernización del código. Fecha: 2026-07-21.

## Resumen ejecutivo

- **434** llamadas reales a funciones `mysql_*()` en **~40** archivos `.php`.
- El código depende de la extensión `mysql`, que **no existe en PHP ≥ 7**. Modernizar =
  migrar a **`mysqli`** (o PDO).
- El trabajo se divide en **dos palancas** de muy distinto esfuerzo (ver abajo).

## Metodología

Conteo con `ripgrep` sobre archivos `.php`, patrón `\bmysql_[a-z_]+\s*\(` (exige el paréntesis
de llamada para evitar falsos positivos de propiedades/arrays internos como
`mysql_server_version`). `mysqli_*` no genera falsos positivos (no contiene la subcadena
`mysql_`).

## Distribución: librería de terceros vs código de aplicación

| Categoría | Llamadas | Tratamiento |
|---|---:|---|
| `adodb/` (librería ADOdb) | 35 | **No** se refactoriza a mano — ADOdb ya incluye driver `mysqli` |
| `include/database/` (wrapper `PearDatabase`) | 3 | Parte del cambio de driver |
| Código de aplicación (resto) | 399 | **Refactor manual** (rompe en PHP 7+) |
| **Total** | **434** | |

## Funciones más usadas

| Función | Nº | Función | Nº |
|---|---:|---|---:|
| `mysql_query` | 184 | `mysql_error` | 26 |
| `mysql_fetch_array` | 80 | `mysql_fetch_assoc` | 24 |
| `mysql_connect` | 33 | `mysql_real_escape_string` | 10 |
| `mysql_select_db` | 27 | `mysql_close` | 8 |
| `mysql_num_rows` | 26 | (otras ~26 distintas) | resto |

## Concentración (top archivos de la app)

El **top 8 de archivos concentra el 61%** de las 399 llamadas de aplicación:

| Archivo | Llamadas |
|---|---:|
| `modules/Users/Users.php` | **103** |
| `include/utils/comunesTareas.php` | 34 |
| `customerPortal2/Notifications/nuevomail.php` | 19 |
| `customerPortal2/Notifications/NotificationsDetail.php` | 19 |
| `customerPortal2/Notifications/leermensaje.php` | 19 |
| `customerPortal2/Notifications2/NotificationsDetail.php` | 19 |
| `customerPortal2/Notifications/index3.php` | 17 |
| `customerPortal2/Notifications2/index.php` | 17 |
| `include/db_backup/backup.php` | 15 |
| `include/security.php` | 12 |

## Estrategia de modernización (dos palancas)

### Palanca 1 — Cambiar el driver de ADOdb (bajo esfuerzo, alto impacto)
- La mayoría del acceso a datos de vtiger pasa por el wrapper `PearDatabase`, que usa
  `ADONewConnection($dbconfig['db_type'])`.
- Hoy: `config.inc.php` → `$dbconfig['db_type'] = 'mysql'`.
- ADOdb **ya trae** `adodb/drivers/adodb-mysqli.inc.php` (y `adodb-pdo_mysql.inc.php`).
- Cambiar `db_type` a `'mysqli'` moderniza todo ese acceso **sin tocar las 434 llamadas**.
- ⚠️ Requiere validar que la aplicación sigue funcional tras el cambio.

### Palanca 2 — Refactor manual de las 399 llamadas crudas
- Son llamadas que **saltan la abstracción** y usan la extensión nativa directamente.
- Rompen en PHP 7+ independientemente del driver de ADOdb.
- Concentradas: `Users.php` solo = 103 (26%); top 8 = 61%.
- Patrón de migración: `mysql_query($q)` → `mysqli_query($conn, $q)`, `mysql_fetch_array` →
  `mysqli_fetch_array`, `mysql_real_escape_string` → `mysqli_real_escape_string`, etc.
  (todas requieren pasar la conexión como primer argumento).

## Alcance recomendado para la Prueba de Concepto (48 h)

No es viable ni es el objetivo refactorizar las 434 llamadas con calidad en 48 h. Plan que
demuestra modernización con **evidencia funcional**:

1. **Palanca 1:** flip del driver `mysql` → `mysqli` + validación de que la app sigue viva.
2. **Palanca 2 (PoC):** refactor completo de **`modules/Users/Users.php`** — es autenticación/
   login, se ejercita de inmediato al entrar y concentra 103 llamadas → demuestra el patrón
   `mysql_*` → `mysqli` de punta a punta.
3. **Documentar** la estrategia para el resto (este documento + `PROMPTS_Y_AGENTE.md`).
