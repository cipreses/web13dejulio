#!/usr/bin/env bash
# Corre DENTRO del contenedor. Instala PHP + extensiones + git + unzip.
set -e
export DEBIAN_FRONTEND=noninteractive
apt-get update
apt-get install -y --no-install-recommends \
	php php-sqlite3 php-xml php-mbstring php-curl php-zip php-gd \
	git unzip ca-certificates curl
