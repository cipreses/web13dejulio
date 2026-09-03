<?php
/**
 * Instituto 13 de Julio theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function i13j_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
}
add_action( 'after_setup_theme', 'i13j_setup' );

function i13j_enqueue_assets() {
	wp_enqueue_style(
		'i13j-fonts',
		'https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Public+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'i13j-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);
	wp_enqueue_style(
		'i13j-theme',
		get_theme_file_uri( 'assets/theme.css' ),
		array(),
		wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'i13j_enqueue_assets' );
