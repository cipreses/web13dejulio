#!/usr/bin/env bash
# Corre DENTRO del contenedor. Instala PHP 8.2 + extensiones + git + unzip.
#
# Debian 11 (bullseye) trae PHP 7.4 por default vía apt, pero el plugin de
# integración SQLite de WordPress (v3.0.1, la última que existe) no anda
# bien ahí: is_blog_installed() tira una excepción sin capturar en vez de
# devolver false limpio cuando todavía no hay tablas, cosa que con PHP 8.x
# no pasa. Por eso se agrega el repo de terceros de Ondřej Surý
# (deb.sury.org) — el estándar de facto para tener PHP moderno en Debian/
# Ubuntu — y se instala PHP 8.2 en vez del php7.4 de los repos de Debian.
set -e

export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y --no-install-recommends \
	ca-certificates curl gnupg2 lsb-release apt-transport-https \
	git unzip

curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg
echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" \
	>/etc/apt/sources.list.d/php.list

apt-get update
apt-get install -y --no-install-recommends \
	php8.2-cli php8.2-sqlite3 php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd
