#!/usr/bin/env bash
# Corre DENTRO del contenedor. Instala PHP 8.2 + extensiones + git + unzip,
# y una libsqlite3 lo bastante nueva para que el plugin de SQLite funcione.
#
# La causa real de por qué el plugin de integración SQLite de WordPress
# (v3.0.1, la última que existe) no arrancaba: exige SQLite >= 3.37.0, y la
# libsqlite3 que trae Debian 11 (bullseye) por default es la 3.34.1 (de
# 2020). El mensaje de error real ("Cannot escape data without an active
# database connection") queda escondido — el plugin traga la excepción real
# de conexión y no la muestra. Se instala PHP 8.2 vía el repo de terceros de
# Ondřej Surý (deb.sury.org, el estándar de facto para PHP moderno en
# Debian/Ubuntu) porque de cualquier forma conviene sobre el php7.4 nativo,
# y se actualiza libsqlite3-0 puntualmente desde Debian 12 (bookworm, que
# trae 3.40.1) sin pasar a usar bookworm para todo el sistema.
set -e

# Bullseye ya salió del soporte activo en deb.debian.org / security.debian.org
# (404 consistentes en paquetes puntuales, no un glitch pasajero de mirror).
# Se repunta a archive.debian.org — el archivo oficial de Debian, no un
# mirror de terceros — que sí sigue sirviendo todas las versiones viejas.
# check-valid-until=no porque los Release files ahí ya vencieron hace rato
# y apt los rechazaría por "desactualizados" si no se lo decimos.
cat >/etc/apt/sources.list <<'APTSOURCES'
deb [check-valid-until=no] http://archive.debian.org/debian bullseye main
APTSOURCES
rm -f /etc/apt/sources.list.d/*.list 2>/dev/null || true

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

# libsqlite3-0 puntual desde bookworm (ver nota arriba). Se agrega la fuente,
# se instala SOLO ese paquete, y se saca la fuente de nuevo enseguida para
# no dejar el sistema con un mix de versiones de dos releases de Debian.
echo "deb http://deb.debian.org/debian bookworm main" >/etc/apt/sources.list.d/bookworm-tmp.list
apt-get update
apt-get install -y --only-upgrade libsqlite3-0
rm -f /etc/apt/sources.list.d/bookworm-tmp.list
apt-get update
