# Compatibilidad del código: PHP 5.6 → PHP 8.4 — Platzilla

> Análisis **empírico** de la migración del código desde PHP 5.6 hacia PHP 8.4. Mismo método que
> el análisis de BD (ver `COMPATIBILIDAD_MARIADB105.md`): en vez de teorizar, se levanta un PHP
> 8.4 real, se pasa el linter sobre todo el árbol y se ejecutan las funciones sospechosas para
> capturar los fallos de verdad. Fecha: 2026-07-22.

## Resumen ejecutivo

- El código **no arranca en PHP 8.4**: hay dos clases de rotura, ambas confirmadas ejecutando.
- **Rotura en parseo (bloquea el fichero entero):** **16 archivos** de código de aplicación no
  compilan en 8.4. Causa dominante: sintaxis de offset con llaves `$var{...}`, **eliminada en
  PHP 8.0**. Detectable con `php -l`.
- **Rotura en runtime (fatal al ejecutar la línea):** uso masivo de **funciones eliminadas** que
  `php -l` **no** detecta: `each()`, `ereg()/split()`, `create_function()`, `get_magic_quotes_*`
  y la extensión nativa `mysql_*`. Todas confirmadas como *undefined* en 8.4.
- Implicación de alcance: la migración a 8.4 es **más grande que la de BD**. El trabajo previo
  (`mysql_*`) es solo una de varias familias eliminadas. Encaja con el criterio del enunciado:
  se moderniza una **porción representativa** con calidad, no el 100%.

## Metodología

Contenedor `php:8.4-cli` (efímero, no toca el stack 5.6 vivo). Dos barridos:

1. **Parse-time** — `php -l` sobre los **11.821** `.php` de `src/`, en paralelo, capturando solo
   los que fallan. Esto caza la sintaxis eliminada (p.ej. `$s{0}`), que es un *parse error* real.
2. **Runtime** — ejecutar en 8.4 las funciones sospechosas (`function_exists` + llamada real)
   para demostrar que son fatales, ya que `php -l` no las ve (una función eliminada compila; solo
   revienta al ejecutarse).

Los conteos por patrón excluyen librerías de terceros (adodb, Smarty, PHPExcel, mpdf, tcpdf,
htmlpurifier, webmail, phpmailer, google-api, Braintree, fpdf, tcpdf, log4php, etc.): esas se
**actualizan**, no se refactorizan a mano.

## Fase 1 — Parse errors (`php -l` sobre 11.821 archivos)

**107** archivos no compilan en 8.4: **91** en librerías de terceros (se resuelven actualizando
la librería) y **16 en código de aplicación/vtiger-core**:

| Archivo (código app) | Relevancia |
|---|---|
| `include/database/PearDatabase.php` | **Wrapper de BD central** |
| `include/platzilla/Objects/PlatformInstance.php` | **Multi-instancia (custom Platzilla)** |
| `include/utils/encryption.php` | Cifrado |
| `include/utils/GraphUtils.php`, `include/utils/InstanceCreator.class.php` | Utils core |
| `modules/Calendar/Appointment.php` (+ iCal, calendarLayout.2) | Módulo Calendar |
| `modules/orden_de_trabajo/handlers/taskToWork_methods.php` | Módulo custom |
| `modules/Settings/EditCustomButtons.php` | Settings |
| `modules/System/includes/common_functions.php`, `XPath.class.php` | System core |
| `vtlib/ModuleDir/5.4.0/ModuleFile.php` | vtlib |

### Causas raíz (evidencia real, `archivo:línea`)

**a) Offset con llaves `$var{...}` — eliminado en PHP 8.0** (causa dominante; ~46 usos en 19
archivos app):

```
include/database/PearDatabase.php:777       switch ($data{'type'}) {
include/platzilla/Objects/PlatformInstance.php:93   $code .= $pattern{mt_rand (0, $max)};
include/utils/encryption.php:28             $charBin = decbin(ord($inputString{$x}));
modules/System/includes/common_functions.php:231    ord($header_and_lsd{8}) ...
```
Corrección mecánica y de bajo riesgo: `$var{i}` → `$var[i]`.

**b) `&new` (asignar por referencia el resultado de `new`) — eliminado en PHP 7.0** (~9 usos):

```
modules/Calendar/Appointment.php:149        $obj = &new Appointment();
```
Corrección: `$obj = new Appointment();`.

**c) Método fuera de clase / estructura inválida:**

```
modules/orden_de_trabajo/handlers/taskToWork_methods.php:10   public function ... (token "public" inesperado)
```
Requiere revisión estructural del fichero (no es sustitución mecánica).

## Fase 2 — Funciones eliminadas (fatales en runtime, invisibles a `php -l`)

Demostración ejecutada en 8.4 — todas responden *"Call to undefined function"*:

```
each()                 -> ELIMINADA (8.0)
ereg() / split()       -> ELIMINADA (7.0)
create_function()      -> ELIMINADA (8.0)
get_magic_quotes_gpc() -> ELIMINADA (8.0)
```

Inventario en **código de aplicación** (excluidas librerías de terceros):

| Familia | Estado en 8.4 | Archivos | Usos |
|---|---|---:|---:|
| `mysql_*` (extensión nativa) | eliminada en 7.0 | 38 | 390 |
| `ereg` / `eregi` / `split` / `ereg_replace` | eliminada en 7.0 | 41 | 118 |
| `each()` | eliminada en 8.0 | 38 | 68 |
| `get_magic_quotes_*` | eliminada en 8.0 | 13 | 17 |
| `create_function()` | eliminada en 8.0 | 7 | 8 |
| `utf8_encode` / `utf8_decode` | **deprecada** en 8.2 (aún existe) | 31 | 68 |

Nota sobre `mysql_*`: la migración de driver (Palanca 1, ver `AUDITORIA_MYSQL.md`) ya enruta por
ADOdb el acceso que pasa por `PearDatabase`; el conteo aquí es de llamadas **crudas** que aún
saltan la abstracción y hay que refactorizar a `mysqli`/PDO.

## Diferencia clave entre las dos clases

- **Parse error** (Fase 1): el fichero **no se puede ni cargar** → tumba cualquier petición que
  lo incluya, aunque la línea nunca se ejecute. Por eso son los primeros a corregir.
- **Función eliminada** (Fase 2): el fichero compila; el fatal salta **solo cuando el flujo llega
  a la línea**. Más difuso de detectar sin cobertura de ejecución, pero igual de mortal.

## Priorización recomendada (PoC)

1. **Desbloquear el parseo** de los 16 ficheros app: `$v{}`→`$v[]` y `&new`→`new` son
   sustituciones seguras y de alto impacto (incluyen `PearDatabase.php` y `PlatformInstance.php`,
   piezas centrales). El caso estructural (`taskToWork_methods.php`) se anota aparte.
2. **Refactor de una porción representativa** a 8.4 de punta a punta (módulo ya aislado en
   Palanca 2), cubriendo también funciones eliminadas (`each`→`foreach`, `ereg`→`preg_*`,
   `create_function`→closures). Ver el commit de la PoC.
3. El resto queda inventariado en `BACKLOG_MODERNIZACION.md` con su justificación de alcance.

## PoC aplicada (desbloqueo de parseo)

Como prueba de concepto se corrigieron dos ficheros **centrales y representativos**, elegidos por
impacto (no por facilidad):

La corrección es **retro-compatible**: todos estos ficheros pasan `php -l` tanto en **PHP 8.4**
como en **PHP 5.6**, así que desbloquean el parseo en 8.4 sin desestabilizar el entorno 5.6.

**Primera tanda (piezas centrales):**

| Fichero | Rompedores | Fix |
|---|---|---|
| `include/database/PearDatabase.php` | 6× `$var{...}` | `$var{k}` → `$var[k]` |
| `include/platzilla/Objects/PlatformInstance.php` | 1× `$pattern{...}` | ídem |

`PearDatabase.php` es el **wrapper de BD** del que depende toda la app (y las Palancas 1/2);
`PlatformInstance.php` es el objeto **custom de multi-instancia**.

**Segunda tanda (6 ficheros de app-code, mismo patrón mecánico):**

| Fichero | Rompedores |
|---|---|
| `include/utils/encryption.php` | `$inputString{$x}` |
| `include/utils/GraphUtils.php` | `$start{0}`, `$step{0}` |
| `include/utils/InstanceCreator.class.php` | `$pattern{...}` |
| `modules/Calendar/Appointment.php` | 2× `&new Appointment()` → `new Appointment()` |
| `modules/Settings/EditCustomButtons.php` | 2× `$customButton{'module'}` |
| `modules/System/includes/common_functions.php` | `$header_and_lsd{8/9}` |

Con esto, **8 de los 16** ficheros de app que no parseaban en 8.4 quedan corregidos por
sustitución mecánica retro-compatible. Los restantes tienen errores **estructurales** (no
mecánicos) y se tratan caso a caso (ver `BACKLOG_MODERNIZACION.md`, tarea M4).

## Sonda de arranque real en PHP 8.4 (bootstrap del login)

Además del análisis estático, se intentó **arrancar el CRM en PHP 8.4** para medir hasta dónde
llega el bootstrap. Montaje: imagen experimental `Dockerfile.php84` (`php:8.4-apache` + `mysqli`/
`pdo_mysql` — la extensión nativa `mysql` ya no existe en 8.4), el **mismo `src`** y la **misma
BD MariaDB 10.5** que el stack real. Resultados, en orden:

1. **Fatal de app (corregido):** `index.php:22` llamaba `deviceDetect::mobile_device_detect()`
   (método de instancia) de forma **estática** → fatal en PHP 8.0. Fix retro-compatible: declarar
   el método `static` (no usa `$this`). Valida en `php -l` 8.4 **y** 5.6; el sitio 5.6 sigue
   sirviendo el login (HTTP 200).
2. **El driver de BD funciona:** `mysqli` desde PHP 8.4 conecta a MariaDB 10.5 en ~0,01 s y
   consulta `vtiger_users` sin problema. El eje BD no es el cuello de botella.
3. **Muro de librería de terceros (ADOdb):** superado el fatal de app, el bootstrap alcanza
   **ADOdb**, que no es 8.4-compatible: fatal de compilación **"Cannot unset `$this`"**
   (`adodb/adodb-xmlschema.inc.php`), **2** usos de `unset($this)` y **3** ficheros de `adodb/`
   que **no parsean** en 8.4.

**Conclusión (confirma el límite de la PoC):** la capa de **código de aplicación** se moderniza
progresivamente (2 fatales de app ya corregidos: `PearDatabase`/`PlatformInstance` y
`deviceDetect`), pero el arranque completo choca contra **librerías empaquetadas** (ADOdb, y por
extensión PHPExcel/mpdf/tcpdf/Smarty…). Esas se **actualizan a una versión compatible con 8.4**,
no se parchean a mano (ver `BACKLOG_MODERNIZACION.md`). Un sitio 100% funcional en 8.4 excede una
PoC de 24 h; lo demostrable es que el camino de refactor de app-code es viable y que el bloqueo
restante es de **dependencias**, no de criterio.

## Reproducir

```bash
# Fase 1: parse errors sobre todo src/ en PHP 8.4
docker run --rm -v "$PWD/src:/src:ro" php:8.4-cli bash -c '
  cd /src; find . -name "*.php" -print0 \
  | xargs -0 -P8 -I{} sh -c "php -l \"{}\" >/dev/null 2>&1 || echo PARSE_FAIL {}"'

# Fase 2: confirmar funciones eliminadas
docker run --rm php:8.4-cli php -r '
  foreach (["each","ereg","split","create_function","get_magic_quotes_gpc"] as $f)
    echo "$f: ".(function_exists($f)?"existe":"ELIMINADA")."\n";'
```
