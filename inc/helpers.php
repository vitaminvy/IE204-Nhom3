<?php
/**
 * Theme helper functions.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize post type from Customizer.
 *
 * @param string $value Post type slug.
 * @return string
 */
function cowm_sanitize_post_type( $value ) {
	$post_type = get_post_type_object( $value );

	return $post_type ? $value : 'post';
}

/**
 * Get an image URL from a media setting.
 *
 * @param string $setting_name Theme mod name.
 * @param string $fallback     Fallback URL.
 * @param string $size         Image size.
 * @return string
 */
function cowm_get_theme_image_url( $setting_name, $fallback = '', $size = 'full' ) {
	$attachment_id = absint( get_theme_mod( $setting_name, 0 ) );

	if ( $attachment_id ) {
		$image = wp_get_attachment_image_url( $attachment_id, $size );
		if ( $image ) {
			return $image;
		}
	}

	return $fallback;
}

/**
 * Get the first existing theme asset image URL from a list of relative paths.
 *
 * @param array<int, string> $relative_paths Relative file paths inside the theme.
 * @param string             $fallback       Fallback URL.
 * @return string
 */
function cowm_get_theme_asset_image_url( $relative_paths, $fallback = '' ) {
	foreach ( $relative_paths as $relative_path ) {
		$relative_path = ltrim( (string) $relative_path, '/' );

		if ( '' === $relative_path ) {
			continue;
		}

		$absolute_path = get_template_directory() . '/' . $relative_path;

		if ( file_exists( $absolute_path ) ) {
			return get_template_directory_uri() . '/' . $relative_path;
		}
	}

	return $fallback;
}

/**
 * Replace legacy placeholder copy with updated copy.
 *
 * This is useful when an older theme version stored default-like text in
 * theme mods and we want the front-end to show corrected copy without
 * overwriting custom user content.
 *
 * @param string               $value        Current value.
 * @param array<string,string> $replacements Legacy-to-new copy map.
 * @return string
 */
function cowm_normalize_legacy_copy( $value, $replacements ) {
	$value = (string) $value;

	if ( isset( $replacements[ $value ] ) ) {
		return $replacements[ $value ];
	}

	$normalized_value = trim( preg_replace( '/\s+/', ' ', remove_accents( $value ) ) );

	if ( function_exists( 'mb_strtolower' ) ) {
		$normalized_value = mb_strtolower( $normalized_value, 'UTF-8' );
	} else {
		$normalized_value = strtolower( $normalized_value );
	}

	foreach ( $replacements as $legacy => $replacement ) {
		$normalized_legacy = trim( preg_replace( '/\s+/', ' ', remove_accents( (string) $legacy ) ) );

		if ( function_exists( 'mb_strtolower' ) ) {
			$normalized_legacy = mb_strtolower( $normalized_legacy, 'UTF-8' );
		} else {
			$normalized_legacy = strtolower( $normalized_legacy );
		}

		if ( $normalized_value === $normalized_legacy ) {
			return $replacement;
		}
	}

	return $value;
}

/**
 * Get attachment alt text.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $fallback      Fallback text.
 * @return string
 */
function cowm_get_attachment_alt( $attachment_id, $fallback = '' ) {
	$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );

	return $alt ? $alt : $fallback;
}

/**
 * Get post thumbnail alt text.
 *
 * @param int    $post_id   Post ID.
 * @param string $fallback  Fallback text.
 * @return string
 */
function cowm_get_post_thumbnail_alt( $post_id, $fallback = '' ) {
	$thumbnail_id = get_post_thumbnail_id( $post_id );

	if ( ! $thumbnail_id ) {
		return $fallback;
	}

	return cowm_get_attachment_alt( $thumbnail_id, $fallback );
}

/**
 * Check whether a post belongs to the story library.
 *
 * @param int|string|WP_Post|null $post Post object, ID, or type slug.
 * @return bool
 */
function cowm_is_story_post_type( $post = null ) {
	if ( is_string( $post ) ) {
		return 'cowm_story' === $post;
	}

	$post_type = get_post_type( $post );

	return 'cowm_story' === $post_type;
}

/**
 * Check whether a post is a chapter.
 *
 * @param int|string|WP_Post|null $post Post object, ID, or type slug.
 * @return bool
 */
function cowm_is_chapter_post_type( $post = null ) {
	if ( is_string( $post ) ) {
		return 'cowm_chapter' === $post;
	}

	$post_type = get_post_type( $post );

	return 'cowm_chapter' === $post_type;
}

/**
 * Get the parent story ID for a chapter.
 *
 * @param int $chapter_id Chapter post ID.
 * @return int
 */
function cowm_get_chapter_story_id( $chapter_id ) {
	return absint( get_post_meta( $chapter_id, 'cowm_story_id', true ) );
}

/**
 * Get numeric chapter number.
 *
 * @param int $chapter_id Chapter post ID.
 * @return int
 */
function cowm_get_chapter_number( $chapter_id ) {
	return absint( get_post_meta( $chapter_id, 'cowm_chapter_number', true ) );
}

/**
 * Format chapter label.
 *
 * @param int $chapter_id Chapter post ID.
 * @return string
 */
function cowm_get_chapter_label( $chapter_id ) {
	$chapter_number = cowm_get_chapter_number( $chapter_id );

	if ( $chapter_number ) {
		return sprintf(
			/* translators: %d is the chapter number. */
			__( 'Chương %d', 'comeout-with-me' ),
			$chapter_number
		);
	}

	return __( 'Chương mới', 'comeout-with-me' );
}

/**
 * Get ordered published chapters for a story.
 *
 * @param int    $story_id Story post ID.
 * @param string $order    ASC or DESC.
 * @return WP_Post[]
 */
function cowm_get_story_chapters( $story_id, $order = 'ASC' ) {
	$chapters = function_exists( 'cowm_get_story_chapter_posts' ) ? cowm_get_story_chapter_posts( $story_id, array( 'publish' ) ) : array();

	if ( 'DESC' === strtoupper( $order ) ) {
		return array_reverse( $chapters );
	}

	return $chapters;
}

/**
 * Get the first readable chapter for a story.
 *
 * @param int $story_id Story post ID.
 * @return WP_Post|null
 */
function cowm_get_story_first_chapter( $story_id ) {
	$chapters = cowm_get_story_chapters( $story_id, 'ASC' );

	return ! empty( $chapters ) ? $chapters[0] : null;
}

/**
 * Get the latest published chapter for a story.
 *
 * @param int $story_id Story post ID.
 * @return WP_Post|null
 */
function cowm_get_story_latest_chapter( $story_id ) {
	$latest_id = absint( get_post_meta( $story_id, 'cowm_latest_chapter_id', true ) );

	if ( $latest_id ) {
		$chapter = get_post( $latest_id );

		if ( $chapter instanceof WP_Post && 'publish' === $chapter->post_status && cowm_is_chapter_post_type( $chapter ) ) {
			return $chapter;
		}
	}

	$chapters = cowm_get_story_chapters( $story_id, 'DESC' );

	return ! empty( $chapters ) ? $chapters[0] : null;
}

/**
 * Get number of published chapters in a story.
 *
 * @param int $story_id Story post ID.
 * @return int
 */
function cowm_get_story_chapter_count( $story_id ) {
	$count = absint( get_post_meta( $story_id, 'cowm_chapter_count', true ) );

	if ( $count ) {
		return $count;
	}

	return count( cowm_get_story_chapters( $story_id, 'ASC' ) );
}

/**
 * Get the latest activity timestamp for a story.
 *
 * @param int $story_id Story post ID.
 * @return int
 */
function cowm_get_story_latest_activity_timestamp( $story_id ) {
	$timestamp = absint( get_post_meta( $story_id, 'cowm_latest_chapter_timestamp', true ) );

	if ( $timestamp ) {
		return $timestamp;
	}

	$latest_chapter = cowm_get_story_latest_chapter( $story_id );

	if ( $latest_chapter instanceof WP_Post ) {
		return (int) get_post_modified_time( 'U', true, $latest_chapter );
	}

	return (int) get_post_modified_time( 'U', true, $story_id );
}

/**
 * Get previous or next chapter within the same story.
 *
 * @param int    $chapter_id Current chapter ID.
 * @param string $direction  next or prev.
 * @return WP_Post|null
 */
function cowm_get_adjacent_story_chapter( $chapter_id, $direction = 'next' ) {
	$story_id = cowm_get_chapter_story_id( $chapter_id );

	if ( ! $story_id ) {
		return null;
	}

	$chapters = cowm_get_story_chapters( $story_id, 'ASC' );

	foreach ( $chapters as $index => $chapter ) {
		if ( (int) $chapter->ID !== (int) $chapter_id ) {
			continue;
		}

		if ( 'prev' === $direction ) {
			return isset( $chapters[ $index - 1 ] ) ? $chapters[ $index - 1 ] : null;
		}

		return isset( $chapters[ $index + 1 ] ) ? $chapters[ $index + 1 ] : null;
	}

	return null;
}

/**
 * Get the story archive URL.
 *
 * @return string
 */
function cowm_get_story_archive_url() {
	$post_type   = cowm_sanitize_post_type( get_theme_mod( 'cowm_stories_post_type', 'cowm_story' ) );
	$category_id = absint( get_theme_mod( 'cowm_stories_category', 0 ) );

	if ( $category_id && is_object_in_taxonomy( $post_type, 'category' ) ) {
		if ( cowm_is_story_post_type( $post_type ) ) {
			$archive = get_post_type_archive_link( 'cowm_story' );

			if ( $archive ) {
				return add_query_arg( 'story_category', $category_id, $archive );
			}
		}

		$link = get_category_link( $category_id );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}

	$archive = get_post_type_archive_link( $post_type );

	if ( $archive ) {
		return $archive;
	}

	return home_url( '/' );
}

/**
 * Get the dedicated profile board page slug.
 *
 * @return string
 */
function cowm_get_profile_board_page_slug() {
	return 'phac-hoa-chan-dung';
}

/**
 * Get the dedicated profile board page URL.
 *
 * @return string
 */
function cowm_get_profile_board_page_url() {
	return home_url( user_trailingslashit( cowm_get_profile_board_page_slug() ) );
}

/**
 * Determine whether the current request is the dedicated profile board screen.
 *
 * @return bool
 */
function cowm_is_profile_board_screen() {
	return (bool) get_query_var( 'cowm_profile_board' ) || is_page( cowm_get_profile_board_page_slug() );
}

/**
 * Register the public query var for the dedicated profile board page.
 *
 * @param string[] $vars Existing public query vars.
 * @return string[]
 */
function cowm_register_profile_board_query_var( $vars ) {
	$vars[] = 'cowm_profile_board';

	return $vars;
}
add_filter( 'query_vars', 'cowm_register_profile_board_query_var' );

/**
 * Register rewrite rules for the dedicated profile board page.
 *
 * @return void
 */
function cowm_register_profile_board_rewrite_rules() {
	$slug = trim( (string) cowm_get_profile_board_page_slug(), '/' );

	if ( '' === $slug ) {
		return;
	}

	add_rewrite_rule(
		'^' . preg_quote( $slug, '/' ) . '/?$',
		'index.php?cowm_profile_board=1',
		'top'
	);
}
add_action( 'init', 'cowm_register_profile_board_rewrite_rules', 20 );

/**
 * Flush rewrite rules once when the profile board route changes.
 *
 * @return void
 */
function cowm_maybe_flush_profile_board_rewrite_rules() {
	$version = '2026-04-20-profile-board-v1';

	if ( get_option( 'cowm_profile_board_rewrite_version' ) === $version ) {
		return;
	}

	cowm_register_profile_board_rewrite_rules();
	flush_rewrite_rules( false );
	update_option( 'cowm_profile_board_rewrite_version', $version, false );
}
add_action( 'init', 'cowm_maybe_flush_profile_board_rewrite_rules', 100 );

/**
 * Swap in the dedicated template for the profile board route.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function cowm_include_profile_board_template( $template ) {
	if ( ! get_query_var( 'cowm_profile_board' ) ) {
		return $template;
	}

	$profile_template = locate_template( 'page-phac-hoa-chan-dung.php' );

	return $profile_template ? $profile_template : $template;
}
add_filter( 'template_include', 'cowm_include_profile_board_template', 99 );

/**
 * Get the dedicated sidewalk editorial page slug.
 *
 * @return string
 */
function cowm_get_sidewalk_page_slug() {
	return 'tra-da-via-he';
}

/**
 * Get the dedicated sidewalk editorial page URL.
 *
 * @return string
 */
function cowm_get_sidewalk_page_url() {
	return home_url( user_trailingslashit( cowm_get_sidewalk_page_slug() ) );
}

/**
 * Determine whether the current request is the dedicated sidewalk editorial page.
 *
 * @return bool
 */
function cowm_is_sidewalk_page() {
	return (bool) get_query_var( 'cowm_sidewalk_page' ) || is_page( cowm_get_sidewalk_page_slug() );
}

/**
 * Register the public query var for the dedicated sidewalk editorial page.
 *
 * @param string[] $vars Existing public query vars.
 * @return string[]
 */
function cowm_register_sidewalk_query_var( $vars ) {
	$vars[] = 'cowm_sidewalk_page';

	return $vars;
}
add_filter( 'query_vars', 'cowm_register_sidewalk_query_var' );

/**
 * Register rewrite rules for the dedicated sidewalk editorial page.
 *
 * @return void
 */
function cowm_register_sidewalk_rewrite_rules() {
	$slug = trim( (string) cowm_get_sidewalk_page_slug(), '/' );

	if ( '' === $slug ) {
		return;
	}

	add_rewrite_rule(
		'^' . preg_quote( $slug, '/' ) . '/?$',
		'index.php?cowm_sidewalk_page=1',
		'top'
	);
}
add_action( 'init', 'cowm_register_sidewalk_rewrite_rules', 20 );

/**
 * Flush rewrite rules once when the sidewalk editorial route changes.
 *
 * @return void
 */
function cowm_maybe_flush_sidewalk_rewrite_rules() {
	$version = '2026-04-20-sidewalk-v1';

	if ( get_option( 'cowm_sidewalk_rewrite_version' ) === $version ) {
		return;
	}

	cowm_register_sidewalk_rewrite_rules();
	flush_rewrite_rules( false );
	update_option( 'cowm_sidewalk_rewrite_version', $version, false );
}
add_action( 'init', 'cowm_maybe_flush_sidewalk_rewrite_rules', 100 );

/**
 * Swap in the dedicated template for the sidewalk editorial route.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function cowm_include_sidewalk_template( $template ) {
	if ( ! get_query_var( 'cowm_sidewalk_page' ) ) {
		return $template;
	}

	$sidewalk_template = locate_template( 'page-tra-da-via-he.php' );

	return $sidewalk_template ? $sidewalk_template : $template;
}
add_filter( 'template_include', 'cowm_include_sidewalk_template', 99 );

/**
 * Get the blog archive URL.
 *
 * @return string
 */
function cowm_get_blog_archive_url() {
	return cowm_get_sidewalk_page_url();
}

/**
 * Build query args for the story section.
 *
 * @param int $count Number of posts.
 * @return array<string, mixed>
 */
function cowm_get_story_query_args( $count = 6 ) {
	$post_type   = cowm_sanitize_post_type( get_theme_mod( 'cowm_stories_post_type', 'cowm_story' ) );
	$category_id = absint( get_theme_mod( 'cowm_stories_category', 0 ) );
	$count       = max( 2, absint( $count ) );

	$args = array(
		'post_type'           => $post_type,
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( cowm_is_story_post_type( $post_type ) ) {
		$args['meta_key'] = 'cowm_latest_chapter_timestamp';
		$args['orderby']  = array(
			'meta_value_num' => 'DESC',
			'date'           => 'DESC',
		);
	}

	if ( $category_id && is_object_in_taxonomy( $post_type, 'category' ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => $category_id,
			),
		);
	}

	return $args;
}

/**
 * Build query args for the blog section.
 *
 * @param int $count Number of posts.
 * @return array<string, mixed>
 */
function cowm_get_blog_query_args( $count = 2 ) {
	$category_id = absint( get_theme_mod( 'cowm_blog_category', 0 ) );
	$count       = (int) $count;
	$count       = $count < 1 ? -1 : max( 1, absint( $count ) );

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'orderby'             => 'date',
		'order'               => 'DESC',
	);

	if ( $category_id ) {
		$args['cat'] = $category_id;
	}

	return $args;
}

/**
 * Resolve the active category filter for the sidewalk editorial page.
 *
 * @return WP_Term|null
 */
function cowm_get_sidewalk_filter_term() {
	$category_slug = isset( $_GET['review_cat'] ) ? sanitize_title( wp_unslash( $_GET['review_cat'] ) ) : '';

	if ( '' === $category_slug ) {
		return null;
	}

	$term = get_term_by( 'slug', $category_slug, 'category' );

	return $term instanceof WP_Term ? $term : null;
}

/**
 * Return badge labels for a story card.
 *
 * @param int $post_id            Post ID.
 * @param int $story_category_id  Primary story category ID.
 * @return string[]
 */
function cowm_get_story_badges( $post_id, $story_category_id = 0 ) {
	$badges    = array();
	$status    = trim( (string) get_post_meta( $post_id, 'cowm_status_label', true ) );
	$secondary = trim( (string) get_post_meta( $post_id, 'cowm_secondary_label', true ) );

	if ( $status ) {
		$badges[] = $status;
	}

	if ( $secondary ) {
		$badges[] = $secondary;
	}

	if ( count( $badges ) < 2 ) {
		$categories = get_the_category( $post_id );

		foreach ( $categories as $category ) {
			if ( $story_category_id && (int) $category->term_id === $story_category_id ) {
				continue;
			}

			if ( 'uncategorized' === $category->slug ) {
				continue;
			}

			$badges[] = $category->name;
			break;
		}
	}

	if ( count( $badges ) < 2 ) {
		$tags = get_the_tags( $post_id );
		if ( $tags && ! is_wp_error( $tags ) ) {
			$badges[] = $tags[0]->name;
		}
	}

	return array_slice( array_values( array_unique( array_filter( $badges ) ) ), 0, 2 );
}

/**
 * Get progress label for story cards.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function cowm_get_story_progress_label( $post_id ) {
	$progress = trim( (string) get_post_meta( $post_id, 'cowm_progress_label', true ) );

	if ( $progress ) {
		return $progress;
	}

	if ( cowm_is_story_post_type( $post_id ) ) {
		$latest_chapter = cowm_get_story_latest_chapter( $post_id );

		if ( $latest_chapter instanceof WP_Post ) {
			return cowm_get_chapter_label( $latest_chapter->ID );
		}
	}

	return __( 'Mới cập nhật', 'comeout-with-me' );
}

/**
 * Get canonical story status text.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function cowm_get_story_status_text( $post_id ) {
	$status = trim( (string) get_post_meta( $post_id, 'cowm_story_status_text', true ) );

	if ( $status ) {
		return $status;
	}

	$status = trim( (string) get_post_meta( $post_id, 'cowm_status_label', true ) );

	return $status ? $status : '';
}

/**
 * Get author label for a story card.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function cowm_get_story_author_name( $post_id ) {
	$author = trim( (string) get_post_meta( $post_id, 'cowm_story_author_name', true ) );

	if ( $author ) {
		return $author;
	}

	return '';
}

/**
 * Get genre chips for a story card.
 *
 * @param int $post_id Post ID.
 * @param int $limit   Maximum number of terms.
 * @return string[]
 */
function cowm_get_story_genres( $post_id, $limit = 6 ) {
	$limit       = absint( $limit );
	$unlimited   = $limit < 1;
	$genres      = array();
	$excluded_id = absint( get_theme_mod( 'cowm_stories_category', 0 ) );
	$tags        = get_the_tags( $post_id );

	if ( $tags && ! is_wp_error( $tags ) ) {
		foreach ( $tags as $tag ) {
			$tag_name = trim( (string) $tag->name );

			if ( '' === $tag_name ) {
				continue;
			}

			$genres[] = $tag_name;

			if ( ! $unlimited && count( $genres ) >= $limit ) {
				return array_values( array_unique( $genres ) );
			}
		}
	}

	$categories = get_the_category( $post_id );

	foreach ( $categories as $category ) {
		if ( $excluded_id && (int) $category->term_id === $excluded_id ) {
			continue;
		}

		if ( 'uncategorized' === $category->slug ) {
			continue;
		}

		$category_name = trim( (string) $category->name );

		if ( '' === $category_name ) {
			continue;
		}

		$genres[] = $category_name;

		if ( ! $unlimited && count( array_unique( $genres ) ) >= $limit ) {
			break;
		}
	}

	$genres = array_values( array_unique( $genres ) );

	return $unlimited ? $genres : array_slice( $genres, 0, $limit );
}

/**
 * Get time ago text for a post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function cowm_get_relative_post_time( $post_id ) {
	$timestamp = cowm_is_story_post_type( $post_id ) ? cowm_get_story_latest_activity_timestamp( $post_id ) : get_post_modified_time( 'U', true, $post_id );

	if ( ! $timestamp ) {
		return get_the_date( '', $post_id );
	}

	return sprintf(
		/* translators: %s is a human-readable time difference. */
		__( '%s trước', 'comeout-with-me' ),
		human_time_diff( $timestamp, current_time( 'timestamp' ) )
	);
}

/**
 * Get popular story tags for archive filtering.
 *
 * @param int $limit Maximum terms.
 * @return WP_Term[]
 */
function cowm_get_story_filter_terms( $limit = 12 ) {
	$limit     = absint( $limit );
	$unlimited = $limit < 1;
	$story_ids = get_posts(
		array(
			'post_type'              => 'cowm_story',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( empty( $story_ids ) ) {
		return array();
	}

	$terms = wp_get_object_terms(
		$story_ids,
		'post_tag',
		array(
			'fields' => 'all_with_object_id',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$grouped = array();

	foreach ( $terms as $term ) {
		$term_id = (int) $term->term_id;

		if ( ! isset( $grouped[ $term_id ] ) ) {
			$grouped[ $term_id ] = array(
				'term'       => $term,
				'object_ids' => array(),
			);
		}

		$grouped[ $term_id ]['object_ids'][ (int) $term->object_id ] = true;
	}

	uasort(
		$grouped,
		static function ( $left, $right ) {
			$left_count  = count( $left['object_ids'] );
			$right_count = count( $right['object_ids'] );

			if ( $left_count !== $right_count ) {
				return $right_count <=> $left_count;
			}

			return strnatcasecmp( $left['term']->name, $right['term']->name );
		}
	);

	$filter_terms = array();

	$term_items = $unlimited ? $grouped : array_slice( $grouped, 0, $limit, true );

	foreach ( $term_items as $item ) {
		$item['term']->count = count( $item['object_ids'] );
		$filter_terms[]      = $item['term'];
	}

	return $filter_terms;
}

/**
 * Return term chips for the highlights section.
 *
 * @param int $limit Maximum terms. Use `0` for unlimited.
 * @return WP_Term[]
 */
function cowm_get_highlight_terms( $limit = 7 ) {
	$limit           = absint( $limit );
	$unlimited       = $limit < 1;
	$highlight_terms = cowm_get_story_filter_terms( $limit );

	if ( ! empty( $highlight_terms ) ) {
		return $highlight_terms;
	}

	$excluded = array_filter(
		array(
			absint( get_theme_mod( 'cowm_stories_category', 0 ) ),
			absint( get_theme_mod( 'cowm_blog_category', 0 ) ),
		)
	);

	$fallback = get_categories(
		array(
			'hide_empty' => true,
			'number'     => $unlimited ? 0 : $limit,
			'exclude'    => $excluded,
		)
	);

	return is_array( $fallback ) ? $fallback : array();
}

/**
 * Get the archive URL for a story taxonomy term.
 *
 * @param WP_Term $term Term object.
 * @return string
 */
function cowm_get_story_term_archive_url( $term ) {
	$archive = cowm_get_story_archive_url();

	if ( ! ( $term instanceof WP_Term ) ) {
		return $archive;
	}

	if ( 'post_tag' === $term->taxonomy ) {
		return add_query_arg( 'story_tag', (int) $term->term_id, $archive );
	}

	if ( 'category' === $term->taxonomy ) {
		return add_query_arg( 'story_category', (int) $term->term_id, $archive );
	}

	$link = get_term_link( $term );

	return is_wp_error( $link ) ? $archive : $link;
}

/**
 * Build homepage profiling cards from popular story terms.
 *
 * @param int $limit Maximum cards. Use `0` for unlimited.
 * @return array<int, array<string, mixed>>
 */
function cowm_get_profile_board_cards( $limit = 6 ) {
	$limit          = absint( $limit );
	$unlimited      = $limit < 1;
	$terms          = cowm_get_highlight_terms( $unlimited ? 0 : max( $limit * 4, $limit ) );
	$cards          = array();
	$used_story_ids = array();

	foreach ( $terms as $term ) {
		if ( ! ( $term instanceof WP_Term ) ) {
			continue;
		}

		$story_ids = array_map(
			'absint',
			get_posts(
				array(
					'post_type'              => 'cowm_story',
					'post_status'            => 'publish',
					'posts_per_page'         => -1,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'ignore_sticky_posts'    => true,
					'tax_query'              => array(
						array(
							'taxonomy' => $term->taxonomy,
							'field'    => 'term_id',
							'terms'    => (int) $term->term_id,
						),
					),
					'meta_key'               => 'cowm_latest_chapter_timestamp',
					'orderby'                => array(
						'meta_value_num' => 'DESC',
						'date'           => 'DESC',
					),
				)
			)
		);
		$story_id          = 0;
		$fallback_story_id = 0;

		foreach ( $story_ids as $candidate_story_id ) {
			if ( ! $candidate_story_id ) {
				continue;
			}

			if ( ! $fallback_story_id ) {
				$fallback_story_id = $candidate_story_id;
			}

			if ( in_array( $candidate_story_id, $used_story_ids, true ) ) {
				continue;
			}

			$story_id = $candidate_story_id;
			break;
		}

		if ( ! $story_id && $fallback_story_id ) {
			$story_id = $fallback_story_id;
		}

		if ( ! $story_id ) {
			continue;
		}

		if ( ! in_array( $story_id, $used_story_ids, true ) ) {
			$used_story_ids[] = $story_id;
		}

		$story_title      = get_the_title( $story_id );
		$story_author     = cowm_get_story_author_name( $story_id );
		$story_progress   = cowm_get_story_progress_label( $story_id );
		$story_chapters   = cowm_get_story_chapter_count( $story_id );
		$story_badges     = cowm_get_story_badges( $story_id );
		$story_genres     = cowm_get_story_genres( $story_id, 2 );
		$story_image_url  = get_the_post_thumbnail_url( $story_id, 'cowm-featured-story' );
		$story_image_alt  = cowm_get_post_thumbnail_alt( $story_id, $story_title );
		$story_excerpt    = '';
		$supporting_label = '';
		$lead_line        = '';
		$facts            = array();

		$story_excerpt = trim( (string) get_post_field( 'post_excerpt', $story_id ) );

		if ( '' === $story_excerpt ) {
			$story_excerpt = (string) get_the_excerpt( $story_id );
		}

		$story_excerpt = wp_trim_words( wp_strip_all_tags( $story_excerpt ), 24, '...' );

		if ( ! empty( $story_badges ) ) {
			$supporting_label = $story_badges[0];
		} elseif ( ! empty( $story_genres ) ) {
			$supporting_label = $story_genres[0];
		} elseif ( $story_progress ) {
			$supporting_label = $story_progress;
		} else {
			$supporting_label = __( 'Mở hồ sơ', 'comeout-with-me' );
		}

		$lead_line = sprintf(
			/* translators: %s is the representative story title. */
			__( 'Hồ sơ tiêu điểm: %s', 'comeout-with-me' ),
			$story_title
		);

		if ( $story_author ) {
			$facts[] = sprintf(
				/* translators: %s is the story author. */
				__( 'Tác giả: %s', 'comeout-with-me' ),
				$story_author
			);
		}

		if ( $story_progress ) {
			$facts[] = $story_progress;
		}

		if ( $story_chapters ) {
			$facts[] = sprintf(
				_n( '%d chương', '%d chương', $story_chapters, 'comeout-with-me' ),
				$story_chapters
			);
		}

		if ( '' === $story_excerpt ) {
			$story_excerpt = sprintf(
				/* translators: %s is the term name. */
				__( 'Lần theo nhãn %s để mở đúng chuyên án và xem những hồ sơ cập nhật gần đây nhất.', 'comeout-with-me' ),
				$term->name
			);
		}

		$cards[] = array(
			'term'             => $term,
			'term_id'          => (int) $term->term_id,
			'url'              => cowm_get_story_term_archive_url( $term ),
			'count'            => absint( $term->count ),
			'case_code'        => sprintf( 'FILE #%02d', count( $cards ) + 1 ),
			'supporting_label' => $supporting_label,
			'lead_line'        => $lead_line,
			'excerpt'          => $story_excerpt,
			'story_id'         => $story_id,
			'story_title'      => $story_title,
			'story_image_url'  => $story_image_url,
			'story_image_alt'  => $story_image_alt,
			'facts'            => array_slice( array_values( array_unique( array_filter( $facts ) ) ), 0, 2 ),
			'search_text'      => implode(
				' ',
				array_filter(
					array(
						$term->name,
						$supporting_label,
						$lead_line,
						$story_excerpt,
						$story_title,
						$story_author,
						implode( ' ', $story_badges ),
						implode( ' ', $story_genres ),
					)
				)
			),
		);

		if ( ! $unlimited && count( $cards ) >= $limit ) {
			break;
		}
	}

	return $cards;
}

/**
 * Get the archive URL for a story tag filter.
 *
 * @param WP_Term $term Tag term object.
 * @return string
 */
function cowm_get_story_tag_archive_url( $term ) {
	return cowm_get_story_term_archive_url( $term );
}

/**
 * Get the default primary menu items.
 *
 * @return array<int, array<string, mixed>>
 */
function cowm_get_default_primary_menu_items() {
	$story_archive_url       = cowm_get_story_archive_url();
	$profile_board_url       = cowm_get_profile_board_page_url();
	$sidewalk_page_url       = cowm_get_sidewalk_page_url();
	$is_story_screen         = is_post_type_archive( 'cowm_story' ) || is_singular( 'cowm_story' ) || is_singular( 'cowm_chapter' );
	$is_profile_board_screen = cowm_is_profile_board_screen();
	$is_sidewalk_screen      = cowm_is_sidewalk_page();

	// Resolve the contact page URL.
	$contact_url       = home_url( '/#lien-he' );
	$is_contact_screen = false;
	$contact_page      = get_page_by_path( 'hop-thu-mat' );

	if ( $contact_page instanceof WP_Post ) {
		$contact_url       = get_permalink( $contact_page );
		$is_contact_screen = is_page( $contact_page->ID );
	}

	return array(
		array(
			'label'      => 'Trang chủ',
			'url'        => home_url( '/' ),
			'is_current' => is_front_page(),
		),
		array(
			'label'      => 'Chuyên Án',
			'url'        => $story_archive_url,
			'is_current' => $is_story_screen,
		),
		array(
			'label'      => 'Phác Họa chân dung',
			'url'        => $profile_board_url,
			'is_current' => $is_profile_board_screen,
		),
		array(
			'label'      => 'Trà Đá Vỉa Hè',
			'url'        => $sidewalk_page_url,
			'is_current' => $is_sidewalk_screen,
		),
		array(
			'label'      => 'Hộp Thư Mật',
			'url'        => $contact_url,
			'is_current' => $is_contact_screen,
		),
	);
}

/**
 * Render a simple fallback menu.
 *
 * @param array|object $args Menu arguments passed by wp_nav_menu().
 */
function cowm_menu_fallback( $args = array() ) {
	$theme_location = '';

	if ( is_array( $args ) && ! empty( $args['theme_location'] ) ) {
		$theme_location = (string) $args['theme_location'];
	} elseif ( is_object( $args ) && ! empty( $args->theme_location ) ) {
		$theme_location = (string) $args->theme_location;
	}

	if ( 'primary' === $theme_location ) {
		$items = cowm_get_default_primary_menu_items();

		echo '<ul class="menu">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		foreach ( $items as $item ) {
			$classes = array( 'menu-item' );

			if ( ! empty( $item['is_current'] ) ) {
				$classes[] = 'current-menu-item';
			}

			printf(
				'<li class="%1$s"><a href="%2$s"%3$s>%4$s</a></li>',
				esc_attr( implode( ' ', $classes ) ),
				esc_url( $item['url'] ),
				! empty( $item['is_current'] ) ? ' aria-current="page"' : '',
				esc_html( $item['label'] )
			);
		}

		echo '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	echo '<ul class="menu">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	wp_list_pages(
		array(
			'title_li' => '',
		)
	);
	echo '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Normalize legacy primary menu links that still point to the old homepage anchor.
 *
 * @param WP_Post[] $items Menu items.
 * @param stdClass  $args  Menu arguments.
 * @return WP_Post[]
 */
function cowm_normalize_primary_menu_items( $items, $args ) {
	if ( empty( $items ) || ! isset( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $items;
	}

	$legacy_profile_urls = array(
		home_url( '/#phac-hoa' ),
		home_url( '/#phac-hoa/' ),
		'/#phac-hoa',
		'#phac-hoa',
	);
	$legacy_sidewalk_urls = array(
		home_url( '/#tra-da-via-he' ),
		home_url( '/#tra-da-via-he/' ),
		'/#tra-da-via-he',
		'#tra-da-via-he',
	);
	$profile_board_url    = cowm_get_profile_board_page_url();
	$sidewalk_page_url    = cowm_get_sidewalk_page_url();
	$profile_urls         = array_merge( $legacy_profile_urls, array( $profile_board_url ) );
	$sidewalk_urls        = array_merge( $legacy_sidewalk_urls, array( $sidewalk_page_url ) );

	foreach ( $items as $item ) {
		if ( ! isset( $item->url ) ) {
			continue;
		}

		$item_url = (string) $item->url;

		if ( in_array( $item_url, $profile_urls, true ) ) {
			$item->url = $profile_board_url;

			if ( cowm_is_profile_board_screen() ) {
				$item->current   = true;
				$item->classes   = isset( $item->classes ) && is_array( $item->classes ) ? $item->classes : array();
				$item->classes[] = 'current-menu-item';
				$item->classes   = array_values( array_unique( $item->classes ) );
			}

			continue;
		}

		if ( ! in_array( $item_url, $sidewalk_urls, true ) ) {
			continue;
		}

		$item->url = $sidewalk_page_url;

		if ( cowm_is_sidewalk_page() ) {
			$item->current   = true;
			$item->classes   = isset( $item->classes ) && is_array( $item->classes ) ? $item->classes : array();
			$item->classes[] = 'current-menu-item';
			$item->classes   = array_values( array_unique( $item->classes ) );
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'cowm_normalize_primary_menu_items', 10, 2 );

/**
 * Return inline SVG icon markup.
 *
 * @param string $icon Icon slug.
 * @return string
 */
function cowm_get_icon( $icon ) {
	$icons = array(
		'search'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.5 4a6.5 6.5 0 1 1 0 13a6.5 6.5 0 0 1 0-13Zm0-2a8.5 8.5 0 1 0 5.33 15.12l4.52 4.52l1.41-1.41l-4.52-4.52A8.5 8.5 0 0 0 10.5 2Z"/></svg>',
		'bookmark' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4l-7 4V4a1 1 0 0 1 1-1Zm1 2v12.55l5-2.86l5 2.86V5H7Z"/></svg>',
		'menu'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v2H4V6Zm0 5h16v2H4v-2Zm0 5h16v2H4v-2Z"/></svg>',
		'folder'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4l2 2h8a1 1 0 0 1 1 1v10.5A2.5 2.5 0 0 1 18.5 20h-13A2.5 2.5 0 0 1 3 17.5v-11A2.5 2.5 0 0 1 5.5 4H10Zm9 6H5v7.5c0 .28.22.5.5.5h13a.5.5 0 0 0 .5-.5V10Z"/></svg>',
		'filter'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v2H4V6Zm3 5h10v2H7v-2Zm3 5h4v2h-4v-2Z"/></svg>',
		'cafe'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h13v5a4 4 0 0 1-4 4h-1v2h5v2H6v-2h4v-2H9a5 5 0 0 1-5-5V7Zm15 1h1a2 2 0 0 1 0 4h-1V8Z"/></svg>',
		'arrow'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m13.17 5.59 1.41-1.42L22.41 12l-7.83 7.83-1.41-1.42L18.59 13H2v-2h16.59l-5.42-5.41Z"/></svg>',
	);

	return isset( $icons[ $icon ] ) ? $icons[ $icon ] : '';
}

/**
 * Build SEO description for the story archive.
 *
 * @param WP_Term|null $term Active tag term.
 * @return string
 */
function cowm_get_story_archive_seo_description( $term = null ) {
	if ( $term instanceof WP_Term ) {
		return sprintf(
			/* translators: %s is the selected story tag. */
			__( 'Khám phá truyện đam mỹ theo từ khóa %s tại Chuyên Án: lọc nhanh theo tag, xem tác giả, badge, thể loại, tình trạng truyện và số chương cập nhật mới nhất.', 'comeout-with-me' ),
			$term->name
		);
	}

	return __( 'Chuyên Án tổng hợp truyện đam mỹ theo từ khóa, tag, tác giả, thể loại, badge và tình trạng để bạn tìm truyện nhanh hơn và theo dõi chương mới thuận tiện hơn.', 'comeout-with-me' );
}

/**
 * Build SEO description for the profile board page.
 *
 * @return string
 */
function cowm_get_profile_board_seo_description() {
	return __( 'Phác Họa Chân Dung giúp bạn lọc truyện đam mỹ theo tag, tác giả, badge, thể loại và tình trạng trước khi mở đúng hồ sơ trong Chuyên Án.', 'comeout-with-me' );
}

/**
 * Build SEO description for the sidewalk editorial page.
 *
 * @param WP_Term|null $term Active category term.
 * @return string
 */
function cowm_get_sidewalk_page_seo_description( $term = null ) {
	if ( $term instanceof WP_Term ) {
		return sprintf(
			/* translators: %s is the selected category name. */
			__( 'Trà Đá Vỉa Hè tổng hợp review truyện, toplist và ghi chú biên tập theo chủ đề %s để độc giả tìm bài cảm nhận, bài gợi ý và danh sách đọc phù hợp nhanh hơn.', 'comeout-with-me' ),
			$term->name
		);
	}

	return __( 'Trà Đá Vỉa Hè là góc review truyện, toplist và ghi chú biên tập để bạn theo dõi cảm nhận đọc, bài tuyển chọn và hồ sơ biên tập mới nhất từ Come Out With Me Local.', 'comeout-with-me' );
}

/**
 * Customize document titles for story archive screens.
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function cowm_filter_document_title_parts( $parts ) {
	if ( cowm_is_sidewalk_page() ) {
		$active_term = cowm_get_sidewalk_filter_term();

		if ( $active_term instanceof WP_Term ) {
			$parts['title'] = sprintf(
				/* translators: %s is the active review category. */
				__( 'Trà Đá Vỉa Hè review truyện %s', 'comeout-with-me' ),
				$active_term->name
			);
		} else {
			$parts['title'] = __( 'Trà Đá Vỉa Hè review truyện đam mỹ', 'comeout-with-me' );
		}

		return $parts;
	}

	if ( cowm_is_profile_board_screen() ) {
		$parts['title'] = __( 'Phác Họa Chân Dung truyện đam mỹ theo tag', 'comeout-with-me' );

		return $parts;
	}

	if ( ! is_post_type_archive( 'cowm_story' ) ) {
		return $parts;
	}

	$story_tag_raw = get_query_var( 'story_tag' );
	$story_tag_ids = array_filter( array_map( 'absint', explode( ',', (string) $story_tag_raw ) ) );

	if ( ! empty( $story_tag_ids ) ) {
		$term_names = array();

		foreach ( $story_tag_ids as $tag_id ) {
			$term = get_term( $tag_id, 'post_tag' );

			if ( $term instanceof WP_Term ) {
				$term_names[] = $term->name;
			}
		}

		if ( ! empty( $term_names ) ) {
			$parts['title'] = sprintf(
				/* translators: %s is the selected tag name(s). */
				__( 'Chuyên Án truyện đam mỹ tag %s', 'comeout-with-me' ),
				implode( ', ', $term_names )
			);

			return $parts;
		}
	}

	$parts['title'] = __( 'Chuyên Án truyện đam mỹ theo tag', 'comeout-with-me' );

	return $parts;
}
add_filter( 'document_title_parts', 'cowm_filter_document_title_parts' );

/**
 * Output archive meta description for story landing pages.
 *
 * @return void
 */
function cowm_output_story_archive_meta_description() {
	if ( cowm_is_sidewalk_page() ) {
		$active_term   = cowm_get_sidewalk_filter_term();
		$description   = wp_strip_all_tags( cowm_get_sidewalk_page_seo_description( $active_term ) );
		$canonical_url = $active_term instanceof WP_Term
			? add_query_arg( 'review_cat', $active_term->slug, cowm_get_sidewalk_page_url() )
			: cowm_get_sidewalk_page_url();

		printf(
			"<meta name=\"description\" content=\"%s\" />\n",
			esc_attr( $description )
		);
		printf(
			"<meta property=\"og:description\" content=\"%s\" />\n",
			esc_attr( $description )
		);
		printf(
			"<link rel=\"canonical\" href=\"%s\" />\n",
			esc_url( $canonical_url )
		);

		return;
	}

	if ( cowm_is_profile_board_screen() ) {
		$description = wp_strip_all_tags( cowm_get_profile_board_seo_description() );

		printf(
			"<meta name=\"description\" content=\"%s\" />\n",
			esc_attr( $description )
		);
		printf(
			"<meta property=\"og:description\" content=\"%s\" />\n",
			esc_attr( $description )
		);
		printf(
			"<link rel=\"canonical\" href=\"%s\" />\n",
			esc_url( cowm_get_profile_board_page_url() )
		);

		return;
	}

	if ( ! is_post_type_archive( 'cowm_story' ) ) {
		return;
	}

	$story_tag_raw = get_query_var( 'story_tag' );
	$story_tag_ids = array_filter( array_map( 'absint', explode( ',', (string) $story_tag_raw ) ) );
	$current_terms = array();

	foreach ( $story_tag_ids as $tag_id ) {
		$term = get_term( $tag_id, 'post_tag' );

		if ( $term instanceof WP_Term ) {
			$current_terms[] = $term;
		}
	}

	$description = ! empty( $current_terms )
		? cowm_get_story_archive_seo_description( $current_terms[0] )
		: cowm_get_story_archive_seo_description();

	if ( '' === trim( $description ) ) {
		return;
	}

	$description = wp_strip_all_tags( $description );

	printf(
		"<meta name=\"description\" content=\"%s\" />\n",
		esc_attr( $description )
	);
	printf(
		"<meta property=\"og:description\" content=\"%s\" />\n",
		esc_attr( $description )
	);
}
add_action( 'wp_head', 'cowm_output_story_archive_meta_description', 1 );
