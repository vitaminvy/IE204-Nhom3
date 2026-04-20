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
 * Get the blog archive URL.
 *
 * @return string
 */
function cowm_get_blog_archive_url() {
	$category_id = absint( get_theme_mod( 'cowm_blog_category', 0 ) );

	if ( $category_id ) {
		$link = get_category_link( $category_id );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}

	$posts_page = (int) get_option( 'page_for_posts' );

	if ( $posts_page ) {
		return get_permalink( $posts_page );
	}

	return home_url( '/' );
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
	$count       = max( 1, absint( $count ) );

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
 * Return term chips for the highlights section.
 *
 * @param int $limit Maximum terms.
 * @return WP_Term[]
 */
function cowm_get_highlight_terms( $limit = 7 ) {
	$limit = max( 1, absint( $limit ) );
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

	if ( ! empty( $story_ids ) ) {
		$terms = wp_get_object_terms(
			$story_ids,
			'post_tag',
			array(
				'fields' => 'all_with_object_id',
			)
		);

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
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

			$highlight_terms = array();

			foreach ( array_slice( $grouped, 0, $limit, true ) as $item ) {
				$item['term']->count = count( $item['object_ids'] );
				$highlight_terms[]   = $item['term'];
			}

			if ( ! empty( $highlight_terms ) ) {
				return $highlight_terms;
			}
		}
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
			'number'     => $limit,
			'exclude'    => $excluded,
		)
	);

	return is_array( $fallback ) ? $fallback : array();
}

/**
 * Get the archive URL for a story tag filter.
 *
 * @param WP_Term $term Tag term object.
 * @return string
 */
function cowm_get_story_tag_archive_url( $term ) {
	if ( ! ( $term instanceof WP_Term ) ) {
		return cowm_get_story_archive_url();
	}

	$archive = get_post_type_archive_link( 'cowm_story' );

	if ( $archive ) {
		return add_query_arg( 'story_tag', (int) $term->term_id, $archive );
	}

	$link = get_term_link( $term );

	return is_wp_error( $link ) ? home_url( '/' ) : $link;
}

/**
 * Get the default primary menu items.
 *
 * @return array<int, array<string, mixed>>
 */
function cowm_get_default_primary_menu_items() {
	$story_archive_url = cowm_get_story_archive_url();
	$is_story_screen   = is_post_type_archive( 'cowm_story' ) || is_singular( 'cowm_story' ) || is_singular( 'cowm_chapter' );

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
			'url'        => home_url( '/#phac-hoa' ),
			'is_current' => false,
		),
		array(
			'label'      => 'Trà Đá Vỉa Hè',
			'url'        => home_url( '/#tra-da-via-he' ),
			'is_current' => false,
		),
		array(
			'label'      => 'Liên Hệ',
			'url'        => home_url( '/#lien-he' ),
			'is_current' => false,
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
 * Customize document titles for story archive screens.
 *
 * @param array<string, string> $parts Title parts.
 * @return array<string, string>
 */
function cowm_filter_document_title_parts( $parts ) {
	if ( ! is_post_type_archive( 'cowm_story' ) ) {
		return $parts;
	}

	$story_tag = absint( get_query_var( 'story_tag' ) );

	if ( $story_tag ) {
		$term = get_term( $story_tag, 'post_tag' );

		if ( $term instanceof WP_Term ) {
			$parts['title'] = sprintf(
				/* translators: %s is the selected tag name. */
				__( 'Chuyên Án: %s', 'comeout-with-me' ),
				$term->name
			);

			return $parts;
		}
	}

	$parts['title'] = __( 'Chuyên Án', 'comeout-with-me' );

	return $parts;
}
add_filter( 'document_title_parts', 'cowm_filter_document_title_parts' );
