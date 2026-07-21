# Platzilla (vtiger CRM legacy) — Entorno de despliegue local con Docker

Despliegue local y reproducible de **Platzilla**, un CRM basado en una versión antigua de
**vtiger CRM**. El código original depende de la extensión nativa `mysql` de PHP (eliminada
en PHP 7.0), por lo que debe ejecutarse sobre **PHP 5.6 / Apache 2.4 / MySQL 5.6**. Este
repositorio empaqueta todo ese entorno en contenedores para poder levantarlo con un par de
comandos, sin instalar nada de esa pila legacy en la máquina anfitriona.

---

## Stack

| Componente | Versión | Notas |
|------------|---------|-------|
| PHP        | 5.6     | Imagen `php:5.6-apache` (Debian Jessie, EOL) |
| Apache     | 2.4     | `mod_rewrite` + `AllowOverride All` habilitados |
| MySQL      | 5.6     | Imagen oficial `mysql:5.6` |
| Extensiones PHP | `mysql` (legacy), `mysqli`, `pdo_mysql` | requeridas por vtiger/ADOdb |

---

## Requisitos previos

- **Docker** y **Docker Compose v2** (`docker compose`).
- El **dump de la base de datos** `base-datos-platzilla.sql` (~103 MB). No se incluye en el
  repositorio por su tamaño (ver [Nota sobre la base de datos](#nota-sobre-la-base-de-datos)).

---

## Estructura del proyecto

```
platzilla/
├── docker-compose.yml     # Orquesta los servicios web (PHP/Apache) y db (MySQL)
├── Dockerfile             # Imagen PHP 5.6 + extensiones + config de Apache
├── entrypoint.sh          # Ajusta config.inc.php e inicia Apache al arrancar
├── .gitignore
├── README.md
├── db_init/               # Se monta en /docker-entrypoint-initdb.d (importa el dump)
│   └── base-datos-platzilla.sql   ← colócalo aquí (no versionado)
└── src/                   # Código de la aplicación (montado en /var/www/html)
```

---

## Instalación limpia (paso a paso)

### 1. Clonar el repositorio

```bash
git clone <URL-DE-TU-REPO> platzilla
cd platzilla
```

### 2. Colocar el dump de la base de datos

Copia el archivo del dump dentro de `db_init/` con este nombre exacto:

```bash
cp /ruta/a/base-datos-platzilla.sql db_init/base-datos-platzilla.sql
```

> MySQL ejecuta automáticamente cualquier `.sql` de esa carpeta **solo en el primer arranque**
> (cuando el volumen de datos está vacío).

### 3. Construir y levantar los contenedores

```bash
docker compose up -d --build
```

El primer arranque tarda varios minutos: MySQL importa el dump de ~103 MB. Puedes seguir el
progreso con:

```bash
docker compose logs -f db
```

Espera a ver el mensaje `ready for connections` antes de continuar.

### 4. Asignar permisos de escritura (Smarty, caché, logs)

vtiger necesita escribir en varias carpetas. Como `src/` se monta como volumen, hay que
otorgar permisos dentro del contenedor:

```bash
docker exec -u root platzilla_web bash -c \
  "chmod -R 777 /var/www/html/Smarty /var/www/html/user_privileges /var/www/html/cache /var/www/html/logs /var/www/html/storage /var/www/html/test"
```

### 5. Limpiar la caché compilada de Smarty

Evita que se sirvan plantillas compiladas con rutas antiguas:

```bash
docker exec -u root platzilla_web bash -c "rm -rf /var/www/html/Smarty/templates_c/*"
```

### 6. Acceder a la aplicación

Abre **http://localhost:8080/**

---

## Configuración y credenciales

Valores definidos en `docker-compose.yml` y `src/config.inc.php`:

| Parámetro | Valor |
|-----------|-------|
| URL de la app        | http://localhost:8080/ |
| Puerto MySQL (host)  | `33066` → `3306` (contenedor) |
| Base de datos        | `pg_crm_madre` |
| Usuario MySQL        | `superuser` |
| Contraseña MySQL     | `8hYLKcthnx` |
| Contraseña root      | `root` |
| Host de BD (interno) | `db` (nombre del servicio) |

El script [`entrypoint.sh`](entrypoint.sh) reescribe automáticamente el host de la BD de
`localhost` a `db` en `config.inc.php` al arrancar, para que la app encuentre el contenedor
de MySQL dentro de la red de Docker.

---

## Comandos útiles

```bash
docker compose ps                 # Estado de los contenedores
docker compose logs -f web        # Logs de Apache/PHP
docker compose down               # Detener (conserva los datos)
docker compose down -v            # Detener y BORRAR la BD (reimporta el dump al reiniciar)
docker exec -it platzilla_web bash   # Shell dentro del contenedor web
```

---

## Solución de problemas

| Síntoma | Causa / Solución |
|---------|------------------|
| `Call to undefined function mysql_connect()` | Falta la extensión `mysql` legacy. Reconstruye la imagen: `docker compose build --no-cache`. |
| `Smarty: unable to write file ...templates_c/...` | Faltan permisos. Ejecuta el paso **4**. |
| La página carga sin estilos (CSS roto) | `$site_URL` apunta a producción o la caché de Smarty está oxidada. Verifica `src/config.inc.php` (`http://localhost:8080/`) y ejecuta el paso **5**. |
| La BD no se importó | El dump se ejecuta solo con el volumen vacío. Fuerza una reimportación con `docker compose down -v` y vuelve a levantar. |

> Los errores de consola del navegador relacionados con `contentscript.js` u
> `operating-modes.js` provienen de extensiones del navegador (p. ej. MetaMask), no del CRM.

---

## Nota sobre la base de datos

El dump `base-datos-platzilla.sql` (~103 MB) **no está versionado** (ver `.gitignore`), tanto
por su peso como porque GitHub rechaza archivos de más de 100 MB. Debe conseguirse por
separado y colocarse en `db_init/` antes del primer `docker compose up` (paso **2**).

Si se necesita versionar, considera **Git LFS** o comprimir el archivo.
