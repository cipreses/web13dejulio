#!/usr/bin/env bash
#
# Crea y provisiona un contenedor LXC en Proxmox VE 7.x para testear el
# sitio de new-site/ (tema instituto-13-de-julio + contenido migrado).
#
# Se corre COMO ROOT EN EL HOST de Proxmox (no dentro del contenedor), parado
# en la raíz del repo (o desde cualquier lado, pasando REPO_ROOT):
#
#   bash new-site/proxmox/create-test-container.sh
#
# Qué hace:
#   1. Descarga (si hace falta) la plantilla LXC de Debian 12.
#   2. Crea el contenedor con IP estática, lo arranca.
#   3. Instala PHP + extensiones + git dentro del contenedor.
#   4. Instala WordPress core + integración SQLite (mismo método que
#      new-site/DEV_SETUP.md), vía new-site/proxmox/provision.php.
#   5. Copia el tema instituto-13-de-julio y corre migrate-content.php.
#   6. Deja un servicio systemd (`wp-test.service`) sirviendo el sitio con
#      `php -S` en el puerto 8899, escuchando en la IP del contenedor.
#
# Se puede volver a correr para actualizar un contenedor ya creado: los
# pasos 4 y 5 son idempotentes (detectan si WP/el tema ya están y solo
# actualizan tema + contenido + servicio).

set -euo pipefail

# ============================================================
# EDITAR ANTES DE CORRER
# ============================================================

CTID="${CTID:-$(pvesh get /cluster/nextid)}"   # ID del contenedor (auto si no se fija)
HOSTNAME="${HOSTNAME:-mockweb}"
BRIDGE="${BRIDGE:-vmbr0}"

# Red estática pedida: 192.168.0.130, máscara 255.255.255.240 (=/28), gw 192.168.1.1
IP_ADDR="192.168.0.130"
NETMASK_CIDR="28"          # 255.255.255.240 == /28
GATEWAY="192.168.1.1"
DNS="${DNS:-1.1.1.1}"

STORAGE="${STORAGE:-local-lvm}"                # storage para el rootfs del contenedor
TEMPLATE_STORAGE="${TEMPLATE_STORAGE:-local}"  # storage donde viven las plantillas LXC

CORES="${CORES:-1}"
MEMORY_MB="${MEMORY_MB:-512}"
SWAP_MB="${SWAP_MB:-512}"
DISK_GB="${DISK_GB:-4}"

# Carpeta del repo que contiene new-site/ (por default, dos niveles arriba de
# este script: new-site/proxmox/create-test-container.sh -> repo root)
REPO_ROOT="${REPO_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
SITE_DIR="${REPO_ROOT}/new-site"

WP_TAG="7.1"                  # misma versión usada en DEV_SETUP.md
SQLITE_TAG="v3.0.1"

WEB_PORT="8899"

# ============================================================
# Chequeos previos
# ============================================================

if [[ $EUID -ne 0 ]]; then
	echo "Este script se corre como root en el host de Proxmox." >&2
	exit 1
fi

if [[ ! -d "${SITE_DIR}/wp-content/themes/instituto-13-de-julio" ]]; then
	echo "No encuentro ${SITE_DIR}/wp-content/themes/instituto-13-de-julio" >&2
	echo "Corré este script desde el repo (o seteá REPO_ROOT=/ruta/al/repo)." >&2
	exit 1
fi

IP_CIDR="${IP_ADDR}/${NETMASK_CIDR}"

# Alerta si la IP y el gateway están en octetos de red distintos (chequeo
# simple; con /28 eso normalmente no es ruteable salvo routing entre VLANs).
IP_NET2=$(echo "$IP_ADDR" | cut -d. -f2)
GW_NET2=$(echo "$GATEWAY" | cut -d. -f2)
if [[ "$IP_NET2" != "$GW_NET2" ]]; then
	echo "⚠️  ATENCIÓN: la IP (${IP_ADDR}) y el gateway (${GATEWAY}) están en octetos"
	echo "   distintos (192.168.0.x vs 192.168.1.x). Con máscara /28 eso normalmente"
	echo "   NO es ruteable salvo que tengas routing entre VLANs armado a propósito."
	echo "   Revisá IP_ADDR / GATEWAY arriba antes de seguir si no es así en tu red."
	read -r -p "   ¿Continuar de todos modos? [y/N] " confirm
	[[ "${confirm,,}" == "y" ]] || exit 1
fi

echo "== Contenedor ${CTID} (${HOSTNAME}) — IP ${IP_CIDR} vía ${BRIDGE}, gw ${GATEWAY} =="

# ============================================================
# 1. Plantilla LXC (Debian 12)
# ============================================================

pveam update >/dev/null
TEMPLATE_FILE=$(pveam available --section system 2>/dev/null | awk '{print $2}' | grep '^debian-12-standard' | sort -V | tail -1)
if [[ -z "$TEMPLATE_FILE" ]]; then
	echo "No encontré ninguna plantilla debian-12-standard en 'pveam available'." >&2
	exit 1
fi
if [[ ! -f "/var/lib/vz/template/cache/${TEMPLATE_FILE}" ]]; then
	echo "Descargando plantilla ${TEMPLATE_FILE}..."
	pveam download "${TEMPLATE_STORAGE}" "${TEMPLATE_FILE}"
fi
TEMPLATE="${TEMPLATE_STORAGE}:vztmpl/${TEMPLATE_FILE}"

# ============================================================
# 2. Crear y arrancar el contenedor
# ============================================================

if pct status "${CTID}" >/dev/null 2>&1; then
	echo "El contenedor ${CTID} ya existe, salto pct create."
else
	pct create "${CTID}" "${TEMPLATE}" \
		--hostname "${HOSTNAME}" \
		--cores "${CORES}" \
		--memory "${MEMORY_MB}" \
		--swap "${SWAP_MB}" \
		--rootfs "${STORAGE}:${DISK_GB}" \
		--net0 "name=eth0,bridge=${BRIDGE},ip=${IP_CIDR},gw=${GATEWAY}" \
		--nameserver "${DNS}" \
		--unprivileged 1 \
		--features "nesting=1" \
		--onboot 1
fi

pct start "${CTID}"

echo "Esperando red dentro del contenedor..."
for i in $(seq 1 30); do
	if pct exec "${CTID}" -- sh -c 'command -v ip >/dev/null && ip addr show eth0 2>/dev/null | grep -q "inet "'; then
		break
	fi
	sleep 1
done

# ============================================================
# 3. Paquetes base dentro del contenedor
# ============================================================

pct exec "${CTID}" -- bash -c '
	export DEBIAN_FRONTEND=noninteractive
	apt-get update
	apt-get install -y --no-install-recommends \
		php php-sqlite3 php-xml php-mbstring php-curl php-zip php-gd \
		git unzip ca-certificates curl
'

# ============================================================
# 4. WordPress core + integración SQLite (clone, mismo método que
#    DEV_SETUP.md; el bash acá es simple, sin comillas anidadas)
# ============================================================

pct exec "${CTID}" -- bash -c "
	set -e
	mkdir -p /var/www
	cd /var/www
	if [ ! -d wordpress ]; then
		git clone --depth 1 --branch '${WP_TAG}' https://github.com/WordPress/WordPress.git wordpress
	fi
	if [ ! -d wordpress/wp-content/plugins/sqlite-database-integration ]; then
		rm -rf /tmp/sqlite-staging
		git clone --depth 1 --branch '${SQLITE_TAG}' https://github.com/WordPress/sqlite-database-integration.git /tmp/sqlite-staging
		cp -rL /tmp/sqlite-staging/packages/plugin-sqlite-database-integration wordpress/wp-content/plugins/sqlite-database-integration
		rm -rf /tmp/sqlite-staging
	fi
"

# ============================================================
# 5. Provisionar wp-config.php + instalar WP + permalinks/locale
#    (provision.php se empuja al contenedor y se ejecuta ahí)
# ============================================================

pct push "${CTID}" "${SITE_DIR}/proxmox/provision.php" /var/www/wordpress/provision.php
pct exec "${CTID}" -- php /var/www/wordpress/provision.php "${IP_ADDR}" "${WEB_PORT}"

# ============================================================
# 6. Copiar el tema + migrar contenido (esto SÍ se re-corre cada vez:
#    permite usar el script para actualizar un contenedor ya creado)
# ============================================================

TMP_TAR="/tmp/i13j-new-site-$$.tar"
tar -cf "${TMP_TAR}" -C "${SITE_DIR}" wp-content/themes/instituto-13-de-julio migrate-content.php
pct push "${CTID}" "${TMP_TAR}" /tmp/new-site.tar
rm -f "${TMP_TAR}"

pct exec "${CTID}" -- bash -c '
	set -e
	rm -rf /var/www/wordpress/wp-content/themes/instituto-13-de-julio
	mkdir -p /tmp/new-site-extract
	tar -xf /tmp/new-site.tar -C /tmp/new-site-extract
	cp -r /tmp/new-site-extract/wp-content/themes/instituto-13-de-julio /var/www/wordpress/wp-content/themes/
	cp /tmp/new-site-extract/migrate-content.php /var/www/wordpress/migrate-content.php
	rm -rf /tmp/new-site-extract /tmp/new-site.tar

	cd /var/www/wordpress
	php -r "require \"/var/www/wordpress/wp-load.php\"; switch_theme(\"instituto-13-de-julio\");"
	php migrate-content.php

	chown -R www-data:www-data /var/www/wordpress
'

# ============================================================
# 7. Servicio systemd sirviendo el sitio en :8899
# ============================================================

pct exec "${CTID}" -- bash -c "cat > /etc/systemd/system/wp-test.service" <<EOF
[Unit]
Description=Instituto 13 de Julio - servidor de testing (php -S)
After=network.target

[Service]
WorkingDirectory=/var/www/wordpress
ExecStart=/usr/bin/php -S ${IP_ADDR}:${WEB_PORT}
Restart=always
User=www-data

[Install]
WantedBy=multi-user.target
EOF

pct exec "${CTID}" -- systemctl daemon-reload
pct exec "${CTID}" -- systemctl enable --now wp-test.service
pct exec "${CTID}" -- systemctl restart wp-test.service

# ============================================================
# 8. Verificación
# ============================================================

sleep 2
echo
echo "== Listo =="
echo "Contenedor: CTID ${CTID}, hostname ${HOSTNAME}, IP ${IP_ADDR}"
echo "Sitio de testing: http://${IP_ADDR}:${WEB_PORT}/"
echo
if command -v curl >/dev/null; then
	curl -sS -o /dev/null -w "HTTP %{http_code}\n" --max-time 5 "http://${IP_ADDR}:${WEB_PORT}/" \
		|| echo "No pude verificar el sitio desde el host (¿está en la misma red/VLAN que ${IP_ADDR}?)."
fi
echo
echo "Admin de WordPress: http://${IP_ADDR}:${WEB_PORT}/wp-admin/  (usuario: admin / clave: CAMBIAR-ESTA-CLAVE)"
echo "Cambiá esa clave si vas a dejar el contenedor accesible por más de una prueba rápida."
