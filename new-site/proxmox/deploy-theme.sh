#!/usr/bin/env bash
# Corre DENTRO del contenedor, después de que /tmp/new-site.tar ya fue
# empujado ahí con 'pct push'. Instala el tema + migra el contenido.
set -e

rm -rf /var/www/wordpress/wp-content/themes/instituto-13-de-julio
mkdir -p /tmp/new-site-extract
tar -xf /tmp/new-site.tar -C /tmp/new-site-extract
cp -r /tmp/new-site-extract/wp-content/themes/instituto-13-de-julio /var/www/wordpress/wp-content/themes/
cp /tmp/new-site-extract/migrate-content.php /var/www/wordpress/migrate-content.php
rm -rf /tmp/new-site-extract /tmp/new-site.tar

cd /var/www/wordpress
php8.2 -r 'require "/var/www/wordpress/wp-load.php"; switch_theme("instituto-13-de-julio");'
php8.2 migrate-content.php

chown -R www-data:www-data /var/www/wordpress
