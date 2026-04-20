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

	if ( function_exists( 'cowm_is_profile_board_screen' ) && cowm_is_profile_board_screen() ) {
		$classes   = array_values( array_diff( $classes, array( 'home', 'blog' ) ) );
		$classes[] = 'is-profile-board-page';
	}

	if ( function_exists( 'cowm_is_sidewalk_page' ) && cowm_is_sidewalk_page() ) {
		$classes   = array_values( array_diff( $classes, array( 'home', 'blog' ) ) );
		$classes[] = 'is-sidewalk-page';
	}

	if ( is_singular( 'post' ) && 'chao-moi-nguoi' === get_post_field( 'post_name', get_queried_object_id() ) ) {
		$classes[] = 'is-intro-post-page';
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

/**
 * Keep front-end search focused on stories and editorial posts.
 *
 * @param WP_Query $query Main query instance.
 * @return void
 */
function cowm_tune_search_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$post_type = $query->get( 'post_type' );

	if ( empty( $post_type ) ) {
		$query->set(
			'post_type',
			array(
				'post',
				'cowm_story',
			)
		);
		return;
	}

	if ( is_string( $post_type ) ) {
		$post_type = array( $post_type );
	}

	if ( ! is_array( $post_type ) ) {
		return;
	}

	$allowed_post_types = array_values(
		array_intersect(
			$post_type,
			array(
				'post',
				'cowm_story',
			)
		)
	);

	if ( empty( $allowed_post_types ) ) {
		$allowed_post_types = array(
			'post',
			'cowm_story',
		);
	}

	$query->set( 'post_type', $allowed_post_types );
}
add_action( 'pre_get_posts', 'cowm_tune_search_queries' );

/**
 * Extend search to story metadata and genre taxonomies.
 *
 * Keeps WordPress title/excerpt/content search intact, then ORs in matches for:
 * author, status text, badges, tags, and categories.
 *
 * @param string   $search Search SQL fragment.
 * @param WP_Query $query  Current query.
 * @return string
 */
function cowm_extend_search_sql( $search, $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return $search;
	}

	$search_terms = $query->get( 'search_terms' );

	if ( ! is_array( $search_terms ) || empty( $search_terms ) ) {
		$raw_search = trim( (string) $query->get( 's' ) );

		if ( '' === $raw_search ) {
			return $search;
		}

		$search_terms = array( $raw_search );
	}

	global $wpdb;

	$meta_keys = array(
		'cowm_story_author_name',
		'cowm_story_status_text',
		'cowm_status_label',
		'cowm_secondary_label',
	);

	$prepared_meta_keys = implode(
		', ',
		array_map(
			static function ( $meta_key ) use ( $wpdb ) {
				return $wpdb->prepare( '%s', $meta_key );
			},
			$meta_keys
		)
	);

	$extra_term_groups = array();

	foreach ( $search_terms as $search_term ) {
		$search_term = trim( (string) $search_term );

		if ( '' === $search_term ) {
			continue;
		}

		$like = '%' . $wpdb->esc_like( $search_term ) . '%';

		$extra_term_groups[] = sprintf(
			'(%1$s OR %2$s)',
			$wpdb->prepare(
				"EXISTS (
					SELECT 1
					FROM {$wpdb->postmeta} cowm_pm
					WHERE cowm_pm.post_id = {$wpdb->posts}.ID
						AND cowm_pm.meta_key IN ({$prepared_meta_keys})
						AND cowm_pm.meta_value LIKE %s
				)",
				$like
			),
			$wpdb->prepare(
				"EXISTS (
					SELECT 1
					FROM {$wpdb->term_relationships} cowm_tr
					INNER JOIN {$wpdb->term_taxonomy} cowm_tt
						ON cowm_tt.term_taxonomy_id = cowm_tr.term_taxonomy_id
					INNER JOIN {$wpdb->terms} cowm_t
						ON cowm_t.term_id = cowm_tt.term_id
					WHERE cowm_tr.object_id = {$wpdb->posts}.ID
						AND cowm_tt.taxonomy IN ('post_tag', 'category')
						AND cowm_t.name LIKE %s
				)",
				$like
			)
		);
	}

	if ( empty( $extra_term_groups ) ) {
		return $search;
	}

	$password_clause = '';
	$base_search     = (string) $search;
	$password_regex  = '/\s+AND\s+\(' . preg_quote( $wpdb->posts, '/' ) . "\.post_password = ''\)\s*$/";

	if ( preg_match( $password_regex, $base_search, $password_match ) ) {
		$password_clause = $password_match[0];
		$base_search     = preg_replace( $password_regex, '', $base_search );
	}

	$base_search = preg_replace( '/^\s*AND\s*/', '', trim( $base_search ), 1 );
	$extra_search = '(' . implode( ' AND ', $extra_term_groups ) . ')';

	if ( '' === $base_search ) {
		return ' AND ' . $extra_search . $password_clause;
	}

	return ' AND (' . $base_search . ' OR ' . $extra_search . ')' . $password_clause;
}
add_filter( 'posts_search', 'cowm_extend_search_sql', 20, 2 );
