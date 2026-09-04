# Entorno de desarrollo local — Instituto 13 de Julio (nuevo sitio)

Este sitio se construye como un tema de WordPress a medida (bloques nativos /
FSE, sin Elementor ni plugins pagos). WordPress core **no** se versiona en
este repo — se descarga aparte.

## Por qué vía GitHub y no wordpress.org

Algunos entornos (incluida la sesión donde se armó esto originalmente) tienen
bloqueado el acceso directo a `wordpress.org`. Los mirrors oficiales en
GitHub sí son accesibles y sirven como fuente alternativa.

## Pasos

```bash
# 1. Core de WordPress (usar la última tag estable)
git clone --depth 1 --branch <ULTIMA_VERSION_ESTABLE> https://github.com/WordPress/WordPress.git core

# 2. Integración de SQLite (para desarrollar sin levantar un MySQL)
mkdir -p /tmp/staging && cd /tmp/staging
git clone --depth 1 --branch v3.0.1 https://github.com/WordPress/sqlite-database-integration.git monorepo
cp -rL monorepo/packages/plugin-sqlite-database-integration core/wp-content/plugins/sqlite-database-integration

# 3. Instalar el drop-in db.php
cd core
cp wp-content/plugins/sqlite-database-integration/db.copy wp-content/db.php
python3 -c "
content = open('wp-content/db.php').read()
content = content.replace(\"'{SQLITE_IMPLEMENTATION_FOLDER_PATH}'\", \"__DIR__ . '/plugins/sqlite-database-integration'\")
content = content.replace('{SQLITE_PLUGIN}', 'sqlite-database-integration/load.php')
open('wp-content/db.php', 'w').write(content)
"

# 4. wp-config.php
cp wp-config-sample.php wp-config.php
# Agregar ANTES de la línea de DB_NAME:
#   define( 'DB_ENGINE', 'sqlite' );
# Generar claves/salts únicas (no usar las de ejemplo)
# Opcional para desarrollo: WP_HOME / WP_SITEURL apuntando a localhost
# Locale: agregar   define( 'WPLANG', 'es_ES' );   y correr una vez
#   update_option( 'WPLANG', 'es_ES' );   (ver wp-load.php)
# Nota: wordpress.org está bloqueado en este entorno, así que los .mo de
# traducción del core (wp-content/languages/es_ES.mo, admin-es_ES.mo, etc.)
# no se pudieron descargar acá. El locale queda seteado (html lang="es-ES",
# fechas vía wp_locale) pero el admin y algunas cadenas del core seguirán
# en inglés hasta instalar el paquete de idioma con `wp language core
# install es_ES` (o descargándolo a mano) en un entorno con acceso normal
# a wordpress.org.

# 5. Copiar el tema de este repo al core
cp -r ../new-site/wp-content/themes/instituto-13-de-julio wp-content/themes/

# 6. Instalar WordPress sin pasar por el navegador
php -r "
define('WP_INSTALLING', true);
require dirname(__FILE__) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
if (!is_blog_installed()) {
    wp_install('Instituto 13 de Julio', 'admin', 'admin@13dejulio.edu.ar', true, '', 'CAMBIAR-ESTA-CLAVE');
}
"

# 7. Activar el tema
php -r "require dirname(__FILE__) . '/wp-load.php'; switch_theme('instituto-13-de-julio');"

# 7b. Permalinks bonitos (imprescindible: sin esto, /historia/, /inscripciones/,
# etc. devuelven el home en vez de la página real, porque WordPress no puede
# matchear la URL contra ninguna regla de reescritura)
php -r "
require dirname(__FILE__) . '/wp-load.php';
update_option('permalink_structure', '/%postname%/');
\$wp_rewrite->init();
\$wp_rewrite->flush_rules();
"

# 8. Levantar el servidor de desarrollo
php -S localhost:8899
```

Abrir `http://localhost:8899/` — debería levantar la home con el tema activo.

## Migrar contenido real del legacy

`new-site/migrate-content.php` crea/actualiza (de forma idempotente) las
páginas curadas a partir de `legacy-site/database/`: Historia, Cuerpo
Directivo, Proyecto Institucional, Acceso a la 13 e Inscripciones. Correrlo
parado en la raíz del core, después de instalar y activar el tema:

```bash
cd core
php ../new-site/migrate-content.php
```

## Estructura del tema

```
wp-content/themes/instituto-13-de-julio/
├── style.css          — cabecera del tema (nombre, versión, etc.)
├── theme.json          — paleta de colores, tipografía, tokens de diseño
├── functions.php        — carga de fuentes y assets
├── templates/          — plantillas de bloques (front-page, index, page)
├── parts/              — header y footer reutilizables
└── assets/              — CSS propio e imágenes de referencia
```

## Paleta (extraída del isologotipo real, no inventada)

| Token | Hex | Uso |
|---|---|---|
| `primary` | `#3E72D4` | Botones, links, acentos |
| `primary-light` | `#5490FC` | Azul del logo original |
| `navy` | `#17284A` | Fondos oscuros (footer), texto de títulos |
| `accent-red` | `#A80000` | Rojo del logo — usar con moderación (alertas, destacados) |
| `contrast` | `#181818` | Texto / negro del logo |
| `base` | `#FCFCFC` | Fondo |

## Hecho

- Migrado el contenido real desde `legacy-site/database/` para Historia,
  Cuerpo Directivo y Acceso a la 13 (ver `new-site/migrate-content.php`).
  Proyecto Institucional queda con el mismo placeholder "Próximamente" que
  tenía en el legacy, a la espera del texto real.
- Unificados los 3 flujos de inscripción (Capital/Provincia/Sindicato) en
  `/inscripciones/`: proceso común de 3 pasos + un bloque desplegable por
  situación con los 2 Google Forms reales y el requisito adicional de cada
  caso.
- Reemplazada "Acceso a la 13" por una grilla simple de 6 enlaces reales
  (Mis licencias, Gmail, Xhendra, Recibos, Reserva de salas, Recursos para
  docentes), sin flujo de SSO.
- Self-host de Archivo, Public Sans e IBM Plex Mono (`assets/fonts/`, subset
  latin) — ya no depende del CDN de Google Fonts.
- Locale es_ES configurado (`WPLANG` en wp-config.php + opción de DB). Los
  `.mo` de traducción del core no se pudieron bajar en este entorno porque
  wordpress.org está bloqueado; instrucciones para completarlo en el paso 4
  más arriba.

## Pendiente

- Feed de Instagram embebido: se descartó por ahora la integración vía API
  de Meta (requiere cuenta Business/Creator vinculada a una página de
  Facebook). Se definió reemplazarla por una galería estática curada a
  mano, pero el repo solo tiene 3 fotos institucionales reales
  (`assets/images/`), ya usadas en Historia y en la home — hacen falta más
  fotos reales del Instituto para armar una galería que no sea repetitiva.
