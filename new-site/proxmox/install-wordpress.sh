#!/usr/bin/env bash
# Corre DENTRO del contenedor. Clona WordPress core + la integración SQLite
# (mismo método que new-site/DEV_SETUP.md). No pisa nada si ya existe
# (idempotente, para poder re-correr el script principal sin recrear todo).
#
# Uso: install-wordpress.sh <wp_tag> <sqlite_tag>
set -e

WP_TAG="$1"
SQLITE_TAG="$2"

mkdir -p /var/www
cd /var/www

if [ ! -d wordpress ]; then
	git clone --depth 1 --branch "${WP_TAG}" https://github.com/WordPress/WordPress.git wordpress
fi

if [ ! -d wordpress/wp-content/plugins/sqlite-database-integration ]; then
	rm -rf /tmp/sqlite-staging
	git clone --depth 1 --branch "${SQLITE_TAG}" https://github.com/WordPress/sqlite-database-integration.git /tmp/sqlite-staging
	cp -rL /tmp/sqlite-staging/packages/plugin-sqlite-database-integration wordpress/wp-content/plugins/sqlite-database-integration
	rm -rf /tmp/sqlite-staging
fi
