<?php
/**
 * Corre DENTRO del contenedor LXC, parado en /var/www/wordpress.
 * Genera wp-config.php (si no existe), instala WordPress y configura
 * permalinks + locale. Idempotente: si ya está instalado, no hace nada.
 *
 * Uso: php provision.php <ip> <puerto>
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( "Correr por CLI.\n" );
}

array_shift( $argv );
[ $ip, $port ] = $argv + [ null, null ];

if ( ! $ip || ! $port ) {
	fwrite( STDERR, "Uso: php provision.php <ip> <puerto>\n" );
	exit( 1 );
}

$site_url = "http://{$ip}:{$port}";

chdir( __DIR__ );

if ( ! file_exists( 'wp-content/db.php' ) ) {
	copy( 'wp-content/plugins/sqlite-database-integration/db.copy', 'wp-content/db.php' );
	$content = file_get_contents( 'wp-content/db.php' );
	$content = str_replace( "'{SQLITE_IMPLEMENTATION_FOLDER_PATH}'", "__DIR__ . '/plugins/sqlite-database-integration'", $content );
	$content = str_replace( '{SQLITE_PLUGIN}', 'sqlite-database-integration/load.php', $content );
	file_put_contents( 'wp-content/db.php', $content );
	echo "db.php generado.\n";
}

if ( ! file_exists( 'wp-config.php' ) ) {
	copy( 'wp-config-sample.php', 'wp-config.php' );
	$content = file_get_contents( 'wp-config.php' );

	$content = str_replace(
		"define( 'DB_NAME'",
		"define( 'DB_ENGINE', 'sqlite' );\n\ndefine( 'DB_NAME'",
		$content
	);

	$extra = "define( 'WP_DEBUG', false );\n"
		. "define( 'WP_HOME', '{$site_url}' );\n"
		. "define( 'WP_SITEURL', '{$site_url}' );\n"
		. "define( 'WPLANG', 'es_ES' );";
	$content = str_replace( "define( 'WP_DEBUG', false );", $extra, $content );

	$keys = [ 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT' ];
	foreach ( $keys as $key ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
		$salt = '';
		for ( $i = 0; $i < 64; $i++ ) {
			$salt .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
		}
		$pattern = "/define\\(\\s*'" . $key . "',\\s*'put your unique phrase here'\\s*\\);/";
		$replacement = "define( '" . $key . "', '" . addcslashes( $salt, "'\\" ) . "' );";
		$content = preg_replace( $pattern, $replacement, $content );
	}

	file_put_contents( 'wp-config.php', $content );
	echo "wp-config.php generado.\n";
}

define( 'WP_INSTALLING', true );
require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

if ( ! is_blog_installed() ) {
	wp_install( 'Instituto 13 de Julio (test)', 'admin', 'admin@13dejulio.edu.ar', true, '', 'CAMBIAR-ESTA-CLAVE' );
	echo "WordPress instalado (usuario: admin / clave: CAMBIAR-ESTA-CLAVE).\n";
} else {
	echo "WordPress ya estaba instalado.\n";
}

update_option( 'permalink_structure', '/%postname%/' );
update_option( 'WPLANG', 'es_ES' );

global $wp_rewrite;
$wp_rewrite->init();
$wp_rewrite->flush_rules();

echo "Permalinks y locale configurados.\n";
