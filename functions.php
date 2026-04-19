<?php
/**
 * Theme bootstrap.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/story-library.php';
require_once get_template_directory() . '/inc/importer.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/meta-boxes.php';

/**
 * Setup theme defaults.
 */
function cowm_setup() {
	load_theme_textdomain( 'comeout-with-me', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-spacing' );
	add_theme_support( 'custom-units' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 280,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'comeout-with-me' ),
			'footer'  => __( 'Footer Menu', 'comeout-with-me' ),
		)
	);

	add_image_size( 'cowm-hero', 960, 1200, true );
	add_image_size( 'cowm-featured-story', 700, 860, true );
	add_image_size( 'cowm-story-thumb', 160, 220, true );
	add_image_size( 'cowm-blog-card', 720, 420, true );
}
add_action( 'after_setup_theme', 'cowm_setup' );

/**
 * Enqueue front-end assets.
 */
function cowm_enqueue_assets() {
	$theme = wp_get_theme();

	wp_enqueue_style(
		'cowm-google-fonts',
		'https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,200..800&family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Work+Sans:wght@100..900&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'cowm-style', get_stylesheet_uri(), array(), $theme->get( 'Version' ) );
	wp_enqueue_style(
		'cowm-main',
		get_template_directory_uri() . '/assets/css/main.css',
		array( 'cowm-style', 'cowm-google-fonts' ),
		filemtime( get_template_directory() . '/assets/css/main.css' )
	);
	wp_enqueue_script(
		'cowm-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		filemtime( get_template_directory() . '/assets/js/navigation.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'cowm_enqueue_assets' );

/**
 * Add small body class hooks.
 *
 * @param string[] $classes Existing classes.
 * @return string[]
 */
function cowm_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'is-front-page';
	}

	if ( is_singular( 'cowm_story' ) ) {
		$classes[] = 'is-single-story';
	}

	if ( is_singular( 'cowm_chapter' ) ) {
		$classes[] = 'is-single-chapter';
	}

	return $classes;
}
add_filter( 'body_class', 'cowm_body_classes' );
