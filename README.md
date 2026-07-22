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

### 4. Permisos de escritura (automático)

vtiger necesita escribir en varias carpetas (Smarty, caché, logs, etc.). El `entrypoint.sh`
las **crea y les da permisos automáticamente** en cada arranque, así que no hay que hacer nada.

> Si por alguna razón necesitas re-aplicarlos manualmente:
> ```bash
> docker exec -u root platzilla_web bash -c \
>   "chmod -R 777 /var/www/html/Smarty /var/www/html/user_privileges /var/www/html/cache /var/www/html/logs /var/www/html/storage /var/www/html/test"
> ```

### 5. Limpiar la caché compilada de Smarty

Evita que se sirvan plantillas compiladas con rutas antiguas:

```bash
docker exec -u root platzilla_web bash -c "rm -rf /var/www/html/Smarty/templates_c/*"
```

### 6. Configurar el acceso por dominio (arquitectura multi-instancia)

Platzilla decide **qué instancia / base de datos** cargar a partir del `HTTP_HOST` con el
que se accede (ver `src/index.php`). El código mapea la primera etiqueta del dominio a una
instancia: `app` → base de datos `madre`. Por eso hay que entrar por un dominio, no por
`localhost` (que resolvería a una instancia inexistente).

Añade esta línea a tu archivo `hosts`:

```
127.0.0.1   app.platzilla.local
```

- **Windows** (si abres el navegador en Windows con WSL2): edita
  `C:\Windows\System32\drivers\etc\hosts` **como Administrador**.
- **Linux / WSL nativo**: edita `/etc/hosts` con `sudo`.

### 7. Acceder a la aplicación

Abre **http://app.platzilla.local:8080/**

Credenciales de acceso al CRM (instancia madre):

| Campo | Valor |
|-------|-------|
| Usuario    | `admin` |
| Contraseña | `uLd15YR86M0U` |

---

## Configuración y credenciales

Valores definidos en `docker-compose.yml` y `src/config.inc.php`:

| Parámetro | Valor |
|-----------|-------|
| URL de la app        | http://app.platzilla.local:8080/ (requiere entrada en `hosts`) |
| Puerto MySQL (host)  | `33066` → `3306` (contenedor) |
| Base de datos        | `pg_crm_madre` |
| Usuario MySQL        | `superuser` |
| Contraseña MySQL     | `8hYLKcthnx` |
| Contraseña root      | `root` |
| Host de BD (interno) | `db` (nombre del servicio) |
| Driver de BD (`db_type`) | `mysqli` (modernizado desde `mysql` legacy) |

> **Arquitectura multi-instancia:** además de la conexión principal (`superuser`), la app abre
> una conexión por instancia con usuario `usr_<instancia>` y clave `md5('usr_<instancia>')`.
> El script `db_init/01-instance-users.sql` provisiona `usr_madre` automáticamente en el primer
> arranque. Sin él, el flujo autenticado falla con `Access denied for user 'usr_madre'`.

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
| La página carga sin estilos (CSS roto) | `$site_URL` apunta a producción o la caché de Smarty está oxidada. Verifica `src/config.inc.php` (`$site_URL = 'http://app.platzilla.local:8080/'`) y ejecuta el paso **5**. |
| La BD no se importó | El dump se ejecuta solo con el volumen vacío. Fuerza una reimportación con `docker compose down -v` y vuelve a levantar. |

> Los errores de consola del navegador relacionados con `contentscript.js` u
> `operating-modes.js` provienen de extensiones del navegador (p. ej. MetaMask), no del CRM.

---

## Documentación técnica

Análisis y decisiones de la modernización (carpeta `docs/`):

| Documento | Contenido |
|---|---|
| [`docs/AUDITORIA_MYSQL.md`](docs/AUDITORIA_MYSQL.md) | Auditoría del código legacy `mysql_*` y estrategia de migración de driver (dos palancas). |
| [`docs/COMPATIBILIDAD_PHP84.md`](docs/COMPATIBILIDAD_PHP84.md) | Análisis **empírico** PHP 5.6 → 8.4: parse errors (`php -l`) y funciones eliminadas; PoC aplicada. |
| [`docs/COMPATIBILIDAD_MARIADB105.md`](docs/COMPATIBILIDAD_MARIADB105.md) | Análisis **empírico** del dump MySQL 5.6 → MariaDB 10.5: `sql_mode` estricto, ENUM `''`, integridad y `DEFINER`; mitigación aplicada. |
| [`docs/BACKLOG_MODERNIZACION.md`](docs/BACKLOG_MODERNIZACION.md) | Plan de ejecución y elementos modernizables aplazados, con justificación de alcance. |

El diseño del agente de IA multi-instancia y las iteraciones con IA están en
[`PROMPTS_Y_AGENTE.md`](PROMPTS_Y_AGENTE.md).

### Prueba de migración a PHP 8.4 (experimental)

El runtime de producción es PHP 5.6 (`Dockerfile`). Para **medir/probar** la migración a PHP 8.4
existe un build experimental [`Dockerfile.php84`](Dockerfile.php84) (php:8.4-apache + `mysqli`):

```bash
docker build -f Dockerfile.php84 -t platzilla-web84 .
docker run -d --name platzilla_web84 --network platzilla_default \
  -v "$PWD/src:/var/www/html" platzilla-web84
```

Sirve para reproducir el análisis de [`docs/COMPATIBILIDAD_PHP84.md`](docs/COMPATIBILIDAD_PHP84.md).
No sustituye al runtime 5.6: el sitio aún no arranca 100% en 8.4 (bloqueo en librerías de terceros
como ADOdb, documentado).

## Nota sobre la base de datos

El dump `base-datos-platzilla.sql` (~103 MB) **no está versionado** (ver `.gitignore`), tanto
por su peso como porque GitHub rechaza archivos de más de 100 MB. Debe conseguirse por
separado y colocarse en `db_init/` antes del primer `docker compose up` (paso **2**).

Si se necesita versionar, considera **Git LFS** o comprimir el archivo.
