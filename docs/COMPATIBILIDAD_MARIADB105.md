# Compatibilidad del dump: MySQL 5.6 → MariaDB 10.5 — Platzilla

> Análisis **empírico** de la migración del dump de datos desde MySQL 5.6 hacia MariaDB 10.5.
> En lugar de enumerar incompatibilidades teóricas, se levanta un MariaDB 10.5 real, se importa
> el dump completo y se mide qué falla de verdad. Fecha: 2026-07-22.

## Resumen ejecutivo

- El dump **importa sin un solo error fatal** (RC=0) en MariaDB 10.5.29. Las 1264 tablas,
  vistas, triggers y rutinas se cargan.
- El riesgo real **no** está en la carga, sino en el **cambio de `sql_mode` por defecto**:
  MySQL 5.6 venía permisivo; MariaDB 10.5 arranca con `STRICT_TRANS_TABLES`.
- Instancia concreta medida: **75 filas** con cadena vacía `''` en columnas **ENUM** que no la
  admiten. Entran en el import (permisivo) como warning, pero en runtime con strict mode
  cualquier `INSERT`/`UPDATE` equivalente **falla con ERROR 1265**.
- El sospechoso obvio —las **fechas cero** (`0000-00-00`)— queda **descartado por medición**:
  el `sql_mode` por defecto de 10.5 no incluye `NO_ZERO_DATE`/`NO_ZERO_IN_DATE`, y se guardan
  sin error.

## Metodología

Dos fases, ambas reproducibles:

1. **Escaneo estático** del dump `db_init/base-datos-platzilla.sql` (~103 MB) con `grep`, sin
   cargar el fichero, buscando patrones que cambian de comportamiento entre 5.6 y 10.5
   (motores, `DEFINER`, fechas cero, charsets, `FULLTEXT`, `sql_mode` embebido).
2. **Prueba en real**: contenedor Docker `mariadb:10.5` (versión efectiva **10.5.29**), creación
   de la BD `pg_crm_madre`, import del dump completo capturando **todos** los errores y warnings,
   y pruebas aisladas de escritura bajo el `sql_mode` por defecto de 10.5 (el que sufre la app).

Origen confirmado en la cabecera del dump: `MySQL dump 10.13 Distrib 5.6.51`, BD `pg_crm_madre`.

## Fase 1 — Escaneo estático (inventario de riesgo)

| Patrón | Conteo | Veredicto |
|---|---:|---|
| `ENGINE=InnoDB` | 1243 | ✅ soportado |
| `ENGINE=MyISAM` | 21 | ✅ soportado |
| `TYPE=…` (sintaxis obsoleta pre-5.5) | 0 | ✅ no aparece |
| `DEFINER=\`root\`@\`localhost\`` (vistas/triggers/rutinas) | 24 | ✅ import como root |
| `TIMESTAMP … DEFAULT '0000-00-00 00:00:00'` (columnas) | 3 | ⚠️ a verificar |
| Literales `0000-00-00` (datos) | 2552 | ⚠️ a verificar |
| Índices `FULLTEXT` | 0 | ✅ sin líos InnoDB fulltext |
| Charsets DDL | utf8 (1255), latin1 (6), utf8mb4/utf16/utf32 (1 c/u) | ✅ soportados |

Dato clave detectado en la cabecera: el propio dump fija `SQL_MODE='NO_AUTO_VALUE_ON_ZERO'`
durante la carga. Es decir, **el import se ejecuta en modo permisivo**, lo que puede enmascarar
en la carga problemas que luego reaparecen en runtime. Esta sospecha es la que dirige la Fase 2.

## Fase 2 — Import real en MariaDB 10.5.29

**Import completo: RC=0, sin errores fatales.** Los únicos avisos fueron **75 warnings**, todos
del mismo tipo (`Code 1265: Data truncated`), concentrados en columnas ENUM:

| Tabla · columna | Definición ENUM | Filas con `''` |
|---|---|---:|
| `vtiger_help_fields.videotype` | `enum('YOUTUBE','VIMEO')` | 45 |
| `vtiger_activity.planned_task` | `enum('PLANNED_AND_RECORDED','PLANNED_UNREGISTERED','UNEXPECTED')` | 12 |
| `vtiger_activity.show_in_matrix` | `enum('YES','NO')` | 12 |
| `vtiger_courselessons.videotype` | `enum('VIMEO','YOUTUBE')` | 6 |
| **Total** | | **75** |

Estas filas traen **cadena vacía `''`**, que no es miembro válido del ENUM. Bajo el modo
permisivo del import, MariaDB la guarda como el valor-error `''` (se lee como `[]`) y solo avisa.

## El hallazgo: `STRICT_TRANS_TABLES` cambia el contrato

`sql_mode` por defecto de MariaDB 10.5.29:

```
STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
```

MySQL 5.6 traía `sql_mode` **vacío** (todo permisivo), así que la app **nunca vio** estos
errores. Prueba aislada de `INSERT ''` en un ENUM:

| `sql_mode` | Resultado del `INSERT ''` en ENUM |
|---|---|
| **Strict** (default 10.5 = runtime de la app) | **ERROR 1265, la transacción falla** |
| Permisivo (`NO_AUTO_VALUE_ON_ZERO`, modo del import) | guarda `[]`, solo warning |

El ENUM vacío es la instancia concreta en **estos** datos de un cambio más amplio: strict mode
también endurece strings demasiado largos, números fuera de rango y columnas `NOT NULL` sin
default. Cualquiera de esos, silencioso en 5.6, pasa a error duro en 10.5.

## Descartado por medición: las fechas cero **no** rompen

Pese a los 2552 literales `0000-00-00` y las 3 columnas `TIMESTAMP DEFAULT '0000-00-00'`, el
`sql_mode` por defecto de 10.5 **no** incluye `NO_ZERO_DATE` ni `NO_ZERO_IN_DATE`. Prueba bajo
strict:

```sql
CREATE TEMPORARY TABLE zt (id INT, ts TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00', d DATE);
INSERT INTO zt (id, ts, d) VALUES (1, '0000-00-00 00:00:00', '0000-00-00');  -- OK, sin error
```

La teoría "las fechas cero romperán la migración" queda **refutada** para esta configuración.

## Mitigación (opciones)

1. **Sanear el dump antes de cargar** — reemplazar los `''` de las columnas ENUM afectadas por
   su `DEFAULT` (o `NULL` donde aplique). Deja los datos correctos y compatibles con strict mode.
   Es la opción "limpia".
2. **Relajar `sql_mode` en la conexión de la app** — configurar la sesión a un modo permisivo
   (equivalente al de 5.6). Rápido y no toca datos, pero perpetúa la laxitud y puede ocultar
   otros errores reales. Es la opción "compatibilidad".

Recomendación: (1) para los 75 valores ya conocidos + (2) transitorio mientras se audita si la
app escribe `''` en esas columnas en algún flujo.

## Implementación (aplicada)

Ambas vías quedan implementadas en el repo y **validadas empíricamente** en MariaDB 10.5.29:

### Vía A — Saneado de datos: `db_init/zz-sanitize-enums.sql`

`UPDATE` idempotentes que corrigen los 75 `''` según la nulabilidad/DEFAULT reales de cada
columna (ver tabla arriba). Se ejecuta **automáticamente tras el dump**: los scripts de
`/docker-entrypoint-initdb.d` corren en orden alfabético (`LANG=C`) y el prefijo `zz-` lo ordena
al final:

```
db_init/01-instance-users.sql
db_init/base-datos-platzilla.sql     <- dump
db_init/zz-sanitize-enums.sql        <- saneado (corre después)
```

**Evidencia:** recarga limpia (dump + saneado) bajo strict mode → `RC=0` y **0 filas inválidas**
en las 4 columnas afectadas.

### Vía B — Red de compatibilidad: `--sql-mode` en `docker-compose.yml`

El servicio `db` añade `--sql-mode=NO_ENGINE_SUBSTITUTION` a su `command`. Quita strict mode
(imitando a MySQL 5.6) sin renunciar a la seguridad de sustitución de motores. Es la red
transitoria que evita que *otras* escrituras laxas de la app rompan mientras no se auditan.

**Evidencia:** con `sql_mode='NO_ENGINE_SUBSTITUTION'`, el `INSERT ''` en ENUM que antes daba
`ERROR 1265` ahora `RC=0` (se comporta como 5.6).

> Nota de alcance: la Vía A es el arreglo definitivo de los datos; la Vía B es un parche de
> compatibilidad. A largo plazo, lo correcto es corregir el código de la app que escribe `''`
> y retirar la Vía B. Una alternativa más quirúrgica a la Vía B es fijar el `sql_mode` por
> conexión en la app (ADOdb/`config.inc.php`) en vez de a nivel de servidor.

## Cierre del eje: integridad de datos y objetos con `DEFINER`

Dos comprobaciones adicionales para dar por cerrado el eje BD.

### Integridad: row-count 5.6 vs 10.5 (sin pérdida en la migración)

`COUNT(*)` exacto de las **1264** tablas base, comparando la instancia viva 5.6 (`platzilla_db`)
contra el dump cargado en 10.5 (`mdb105-test`):

- **1262 tablas cuadran exacto.** Solo difieren 2: `vtiger_audit_trial` (5.6: 147892 / 10.5:
  147870) y `vtiger_loginhistory` (5.6: 6921 / 10.5: 6914).
- Ambas son tablas **append-only** (auditoría y logins) que la app viva 5.6 ha seguido
  escribiendo **después** de tomarse el dump. La diferencia (29 filas, todas del lado vivo) es
  **deriva de la instancia**, no pérdida de la migración.
- **Conclusión:** el import a MariaDB 10.5 es **sin pérdida estructural** de datos.

### Objetos con `DEFINER=root@localhost` (24: 2 vistas, 6 triggers, ~16 rutinas)

Todos con `SQL SECURITY DEFINER`. Se probó ejecutándolos como el **usuario real de la app**
(`superuser`, con grants de esquema, NO root):

| Prueba | Resultado |
|---|---|
| `SELECT` sobre vista `v_activity_cost_analysis` | ✅ 295 filas |
| Invocar función `ExtractNumber(...)` | ✅ devuelve valor |
| Vista con `DEFINER` **inexistente** (`fantasma@localhost`) | ❌ **ERROR 1449** |

**Veredicto:** los objetos `DEFINER=root@localhost` funcionan para la app en 10.5 **siempre que
`root@localhost` exista** en el servidor destino (existe por defecto en la imagen MariaDB) y el
usuario de la app tenga `EXECUTE`/`SELECT`. **Riesgo:** en un despliegue endurecido o
multi-instancia donde `root@localhost` se elimine o renombre, **los 24 objetos rompen con ERROR
1449**. Mitigación en migración: garantizar la existencia del usuario definer, o reescribir los
objetos con `SQL SECURITY INVOKER` / un definer estable por instancia. (Esto alimenta las reglas
de seguridad del System Prompt del agente multi-instancia.)

## Estado: stack migrado a MariaDB 10.5 (verificado)

El servicio `db` de `docker-compose.yml` **ya usa `mariadb:10.5`** (antes `mysql:5.6`). Tras
recrear el volumen (`docker compose down -v && up`), el primer arranque ejecuta automáticamente
`01-instance-users.sql` + el dump + `zz-sanitize-enums.sql`. Verificación end-to-end:

- Motor: `10.5.29-MariaDB`; 1264 tablas base; **0 ENUM inválidos** (saneado aplicado en el init).
- `GRANT ... IDENTIFIED BY` de `01-instance-users.sql` funciona en 10.5 (MariaDB conserva esa
  sintaxis; MySQL 8 no).
- Usuarios de la app conectan: `superuser` y `usr_madre`.
- **El sitio (PHP 5.6) responde HTTP 200 contra MariaDB 10.5** y renderiza el formulario de login
  sin errores de BD.

## Modernización de charset: utf8 (utf8mb3) → utf8mb4 (N2, validado empíricamente)

El esquema está en `utf8`, que en MySQL/MariaDB es **utf8mb3** (máx. 3 bytes/char): **no** admite
emoji ni caracteres Unicode de 4 bytes. `utf8mb4` es el Unicode completo. El System Prompt del
agente ya lo recomienda; aquí se prueba **en real** en `mdb105-test` (1264 tablas) qué implica.

**Resultado del `ALTER TABLE … CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci` masivo:**

- **1200 / 1264 tablas convierten directo.**
- **No hay problema de "key too long":** el `ROW_FORMAT=Dynamic` (1258 tablas) admite prefijos de
  índice hasta 3072 B, y un `VARCHAR(255)` utf8mb4 ocupa 1020 B. (Habría fallado con el antiguo
  límite de 767 B o en MyISAM.)
- **64 tablas fallan con `ERROR 1832`:** no se puede cambiar el charset de una columna que
  participa en una **foreign key** (el esquema tiene **382 FKs**). **`SET FOREIGN_KEY_CHECKS=0` NO
  lo evita** — es una restricción estructural, no de verificación.

**Estrategia correcta (validada):** `DROP FOREIGN KEY` → `CONVERT` → **re-crear la FK** (con ambas
columnas ya en utf8mb4, el charset casa). Probado: al soltar `fk_1_vtiger_role2picklist`, la tabla
convierte sin error. Recipe reproducible con `information_schema` para generar el guion:

```sql
-- 1) Snapshot de las FKs para poder recrearlas (guardar la salida):
SELECT CONCAT('ALTER TABLE `',TABLE_NAME,'` ADD CONSTRAINT `',CONSTRAINT_NAME,
  '` FOREIGN KEY (`',COLUMN_NAME,'`) REFERENCES `',REFERENCED_TABLE_NAME,'`(`',
  REFERENCED_COLUMN_NAME,'`);')
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA='pg_crm_madre' AND REFERENCED_TABLE_NAME IS NOT NULL;
-- 2) Generar los DROP FOREIGN KEY (análogo). 3) SET FOREIGN_KEY_CHECKS=0;
-- 4) DROP FKs -> ALTER DATABASE + CONVERT de todas las tablas -> re-ADD FKs.
-- 5) SET FOREIGN_KEY_CHECKS=1; validar.
```

No se ejecuta en la BD viva: la app funciona correctamente en utf8 y esta migración es *one-time*,
con respaldo + rollback y despliegue **madre-canario luego por instancia** (mismas reglas del
System Prompt). Queda como guion validado y documentado.

## Reproducir

```bash
docker run -d --name mdb105-test -e MARIADB_ROOT_PASSWORD=root mariadb:10.5
# esperar readiness (mysqladmin ping)
docker exec mdb105-test mysql -uroot -proot -e "CREATE DATABASE pg_crm_madre CHARACTER SET utf8;"
docker exec -i mdb105-test mysql -uroot -proot --show-warnings pg_crm_madre \
  < db_init/base-datos-platzilla.sql 2>&1 | tee import.log
grep -c Warning import.log   # -> 75, todos Code 1265 en columnas ENUM
docker rm -f mdb105-test     # limpieza
```
