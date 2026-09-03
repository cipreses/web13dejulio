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

# 8. Levantar el servidor de desarrollo
php -S localhost:8899
```

Abrir `http://localhost:8899/` — debería levantar la home con el tema activo.

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

## Pendiente

- Migrar/curar el contenido real desde `legacy-site/database/` (páginas,
  currícula, historia, cuerpo directivo).
- Unificar los 3 flujos de inscripción (Capital/Provincia/Sindicato).
- Reemplazar "Acceso a la 13" por una página simple de enlaces (sin SSO).
- Feed de Instagram embebido (requiere cuenta Business/Creator vinculada a
  una página de Facebook para usar la API gratuita de Meta).
- Self-host de las fuentes (Archivo, Public Sans) en vez de Google Fonts CDN.
- Configurar locale es_ES.
