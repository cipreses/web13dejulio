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
#   1. Descarga (si hace falta) la plantilla LXC de Debian 11 (bullseye).
#   2. Crea el contenedor con IP estática, lo arranca.
#   3. Instala PHP + extensiones + git (install-packages.sh).
#   4. Instala WordPress core + integración SQLite, mismo método que
#      new-site/DEV_SETUP.md (install-wordpress.sh + provision.php).
#   5. Copia el tema instituto-13-de-julio y corre migrate-content.php
#      (deploy-theme.sh).
#   6. Deja un servicio systemd (`wp-test.service`) sirviendo el sitio con
#      `php -S` en el puerto 8899, escuchando en la IP del contenedor.
#
# Todos los pasos "dentro del contenedor" corren desde un archivo pusheado
# con 'pct push' + 'pct exec ... -- bash /ruta/script.sh', nunca como bloque
# multilínea pasado a 'bash -c' (ver el comentario en el paso 3 sobre por
# qué). Los scripts de apoyo viven junto a este archivo, en new-site/proxmox/.
#
# Se puede volver a correr para actualizar un contenedor ya creado: los
# pasos 4 y 5 son idempotentes (detectan si WP/el tema ya están y solo
# actualizan tema + contenido + servicio).

set -euo pipefail

# ============================================================
# EDITAR ANTES DE CORRER
# ============================================================

CTID="${CTID:-$(pvesh get /cluster/nextid)}"   # ID del contenedor (auto si no se fija)
CT_HOSTNAME="${CT_HOSTNAME:-mockweb}"
BRIDGE="${BRIDGE:-vmbr0}"

# Red estática pedida: 192.168.0.130, máscara 255.255.240.0 (=/20), gw 192.168.1.1
IP_ADDR="192.168.0.130"
NETMASK_CIDR="20"          # 255.255.240.0 == /20
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

# Alerta si el gateway no pertenece a la subred de la IP estática, calculado
# de verdad a partir de IP/máscara/gateway (no una comparación de octetos a
# ojo, que con máscaras que no sean /24 puede dar falsos negativos/positivos).
ip_to_int() {
	local a b c d
	IFS='.' read -r a b c d <<<"$1"
	echo $(((a << 24) + (b << 16) + (c << 8) + d))
}
prefix_to_mask_int() {
	local prefix=$1
	if [[ "$prefix" -eq 0 ]]; then
		echo 0
	else
		echo $(((0xFFFFFFFF << (32 - prefix)) & 0xFFFFFFFF))
	fi
}

MASK_INT=$(prefix_to_mask_int "$NETMASK_CIDR")
IP_NET=$(($(ip_to_int "$IP_ADDR") & MASK_INT))
GW_NET=$(($(ip_to_int "$GATEWAY") & MASK_INT))

if [[ "$IP_NET" -ne "$GW_NET" ]]; then
	echo "⚠️  ATENCIÓN: con máscara /${NETMASK_CIDR}, la IP ${IP_ADDR} y el gateway"
	echo "   ${GATEWAY} NO están en la misma subred (el gateway no sería alcanzable"
	echo "   por ARP/L2 desde el contenedor). Revisá IP_ADDR / NETMASK_CIDR / GATEWAY"
	echo "   arriba antes de seguir."
	read -r -p "   ¿Continuar de todos modos? [y/N] " confirm
	[[ "${confirm,,}" == "y" ]] || exit 1
fi

echo "== Contenedor ${CTID} (${CT_HOSTNAME}) — IP ${IP_CIDR} vía ${BRIDGE}, gw ${GATEWAY} =="

# ============================================================
# 1. Plantilla LXC (Debian 11 / bullseye)
# ============================================================
#
# Nota: se usa Debian 11 a propósito, no Debian 12 ni Ubuntu 22.04. El
# paquete pve-container de este host (Proxmox VE 7.1-7, de nov. 2021, nunca
# actualizado desde entonces) rechaza cualquier plantilla más nueva que esa
# fecha con "unsupported ... version" — ya lo confirmamos con Debian 12
# (2023) y con Ubuntu 22.04 (abril 2022): ambas posteriores a esa build.
# Debian 11 es la única opción 100% garantizada, porque es el propio sistema
# operativo con el que está armado ese Proxmox. El costo: el PHP 7.4 que
# trae Debian 11 por apt NO sirve para este sitio — el plugin de integración
# SQLite de WordPress no anda bien ahí (is_blog_installed() tira una
# excepción sin capturar en vez de devolver false limpio). Por eso
# install-packages.sh agrega el repo de terceros deb.sury.org e instala
# PHP 8.2 encima de este mismo Debian 11, en vez de usar el php7.4 nativo.
#
# El fix de fondo, si en algún momento querés plantillas más nuevas, es
# actualizar el propio Proxmox: `apt update && apt install pve-container`
# (o un `apt full-upgrade` completo) en el host. Eso es una decisión aparte
# sobre tu infraestructura, no algo que este script toque solo.

pveam update >/dev/null
TEMPLATE_FILE=$(pveam available --section system 2>/dev/null | awk '{print $2}' | grep '^debian-11-standard' | sort -V | tail -1)
if [[ -z "$TEMPLATE_FILE" ]]; then
	echo "No encontré ninguna plantilla debian-11-standard en 'pveam available'." >&2
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
		--hostname "${CT_HOSTNAME}" \
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

if pct status "${CTID}" | grep -q running; then
	echo "El contenedor ${CTID} ya está corriendo."
else
	pct start "${CTID}"
fi

echo "Esperando a que el contenedor esté listo..."
sleep 5   # margen para que termine de arrancar antes del primer pct exec

# set +e acá a propósito: no queremos que un fallo transitorio de 'pct exec'
# justo después de arrancar el contenedor tire abajo todo el script por
# set -e (nos pasó: un intento fallido apenas hecho el pct start terminaba
# el script entero en vez de reintentar).
set +e
READY=0
for i in $(seq 1 30); do
	pct exec "${CTID}" -- echo ready >/dev/null 2>&1
	if [[ $? -eq 0 ]]; then
		READY=1
		break
	fi
	sleep 1
done
set -e

if [[ "$READY" -ne 1 ]]; then
	echo "El contenedor no respondió a 'pct exec' después de 30s. Revisá 'pct status ${CTID}' y" >&2
	echo "'pct exec ${CTID} -- echo ready' a mano antes de seguir." >&2
	exit 1
fi

set +e
pct exec "${CTID}" -- ping -c1 -W2 "${GATEWAY}" >/dev/null 2>&1
if [[ $? -ne 0 ]]; then
	echo "⚠️  El contenedor todavía no responde ping a su gateway (${GATEWAY})."
	echo "   Sigo igual, pero si los pasos de git clone / apt de más abajo fallan por"
	echo "   falta de red, revisá la config de red del contenedor (pct config ${CTID})."
fi
set -e

# ============================================================
# 3. Paquetes base dentro del contenedor
# ============================================================
#
# Nota: los pasos 3, 4 y 6 empujan un script con 'pct push' y lo corren con
# 'pct exec ... -- bash /ruta/script.sh' en vez de pasarle un bloque
# multilínea a 'bash -c'. Este pve-container (4.1-2, de Proxmox 7.1) tiene
# un bug real con argumentos de 'bash -c' que contienen saltos de línea:
# los pierde y termina llamando "bash -c" sin argumento, con el error
# "bash: -c: option requires an argument". Con un archivo + un solo comando
# simple como argumento, no hay saltos de línea en el argv y no pasa.

pct push "${CTID}" "${SITE_DIR}/proxmox/install-packages.sh" /tmp/install-packages.sh
pct exec "${CTID}" -- bash /tmp/install-packages.sh

# ============================================================
# 4. WordPress core + integración SQLite (mismo método que DEV_SETUP.md)
# ============================================================

pct push "${CTID}" "${SITE_DIR}/proxmox/install-wordpress.sh" /tmp/install-wordpress.sh
pct exec "${CTID}" -- bash /tmp/install-wordpress.sh "${WP_TAG}" "${SQLITE_TAG}"

# ============================================================
# 5. Provisionar wp-config.php + instalar WP + permalinks/locale
#    (provision.php se empuja al contenedor y se ejecuta ahí)
# ============================================================

pct push "${CTID}" "${SITE_DIR}/proxmox/provision.php" /var/www/wordpress/provision.php
pct exec "${CTID}" -- php8.2 /var/www/wordpress/provision.php "${IP_ADDR}" "${WEB_PORT}"

# ============================================================
# 6. Copiar el tema + migrar contenido (esto SÍ se re-corre cada vez:
#    permite usar el script para actualizar un contenedor ya creado)
# ============================================================

TMP_TAR="/tmp/i13j-new-site-$$.tar"
tar -cf "${TMP_TAR}" -C "${SITE_DIR}" wp-content/themes/instituto-13-de-julio migrate-content.php
pct push "${CTID}" "${TMP_TAR}" /tmp/new-site.tar
rm -f "${TMP_TAR}"

pct push "${CTID}" "${SITE_DIR}/proxmox/deploy-theme.sh" /tmp/deploy-theme.sh
pct exec "${CTID}" -- bash /tmp/deploy-theme.sh

# ============================================================
# 7. Servicio systemd sirviendo el sitio en :8899
# ============================================================

TMP_SERVICE="/tmp/wp-test-$$.service"
cat >"${TMP_SERVICE}" <<EOF
[Unit]
Description=Instituto 13 de Julio - servidor de testing (php -S)
After=network.target

[Service]
WorkingDirectory=/var/www/wordpress
ExecStart=/usr/bin/php8.2 -S ${IP_ADDR}:${WEB_PORT}
Restart=always
User=www-data

[Install]
WantedBy=multi-user.target
EOF
pct push "${CTID}" "${TMP_SERVICE}" /etc/systemd/system/wp-test.service
rm -f "${TMP_SERVICE}"

pct exec "${CTID}" -- systemctl daemon-reload
pct exec "${CTID}" -- systemctl enable --now wp-test.service
pct exec "${CTID}" -- systemctl restart wp-test.service

# ============================================================
# 8. Verificación
# ============================================================

sleep 2
echo
echo "== Listo =="
echo "Contenedor: CTID ${CTID}, hostname ${CT_HOSTNAME}, IP ${IP_ADDR}"
echo "Sitio de testing: http://${IP_ADDR}:${WEB_PORT}/"
echo
if command -v curl >/dev/null; then
	curl -sS -o /dev/null -w "HTTP %{http_code}\n" --max-time 5 "http://${IP_ADDR}:${WEB_PORT}/" \
		|| echo "No pude verificar el sitio desde el host (¿está en la misma red/VLAN que ${IP_ADDR}?)."
fi
echo
echo "Admin de WordPress: http://${IP_ADDR}:${WEB_PORT}/wp-admin/  (usuario: admin / clave: CAMBIAR-ESTA-CLAVE)"
echo "Cambiá esa clave si vas a dejar el contenedor accesible por más de una prueba rápida."
