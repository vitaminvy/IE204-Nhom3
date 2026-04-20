<?php
/**
 * Story importer for text chapter files.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the absolute import root inside the theme.
 *
 * @return string
 */
function cowm_get_import_root() {
	return trailingslashit( get_template_directory() ) . 'import';
}

/**
 * Normalize an import folder path and ensure it stays inside the theme import root.
 *
 * @param string $input Raw folder input from admin or CLI.
 * @return string|WP_Error
 */
function cowm_normalize_import_folder_path( $input ) {
	$input      = trim( (string) $input );
	$import_root = realpath( cowm_get_import_root() );

	if ( ! $import_root || ! is_dir( $import_root ) ) {
		return new WP_Error( 'missing_import_root', __( 'Không tìm thấy thư mục import trong theme.', 'comeout-with-me' ) );
	}

	if ( '' === $input ) {
		return new WP_Error( 'missing_folder', __( 'Bạn cần nhập tên thư mục hoặc đường dẫn import.', 'comeout-with-me' ) );
	}

	$candidates = array();

	if ( str_starts_with( $input, $import_root ) || str_starts_with( $input, '/' ) ) {
		$candidates[] = $input;
	} else {
		$relative = preg_replace( '#^import/#', '', ltrim( $input, '/' ) );
		$candidates[] = trailingslashit( $import_root ) . $relative;
	}

	foreach ( $candidates as $candidate ) {
		$resolved = realpath( $candidate );

		if ( ! $resolved || ! is_dir( $resolved ) ) {
			continue;
		}

		if ( 0 !== strpos( $resolved, $import_root ) ) {
			return new WP_Error( 'invalid_folder_scope', __( 'Thư mục import phải nằm bên trong thư mục theme/import.', 'comeout-with-me' ) );
		}

		return $resolved;
	}

	return new WP_Error( 'missing_folder', __( 'Không tìm thấy thư mục import bạn đã nhập.', 'comeout-with-me' ) );
}

/**
 * Parse a key-value line.
 *
 * @param string $line Line content.
 * @param string $label Expected label.
 * @return string|null
 */
function cowm_parse_import_scalar_line( $line, $label ) {
	$prefix = $label . ':';

	if ( 0 !== strpos( $line, $prefix ) ) {
		return null;
	}

	return trim( mb_substr( $line, mb_strlen( $prefix ) ) );
}

/**
 * Parse one chapter import file.
 *
 * @param string $file_path Absolute file path.
 * @return array|WP_Error
 */
function cowm_parse_import_chapter_file( $file_path ) {
	$contents = file_get_contents( $file_path );

	if ( false === $contents ) {
		return new WP_Error( 'read_failed', sprintf( __( 'Không đọc được file %s.', 'comeout-with-me' ), basename( $file_path ) ) );
	}

	$contents = preg_replace( "/^\xEF\xBB\xBF/", '', (string) $contents );
	$contents = str_replace( array( "\r\n", "\r" ), "\n", $contents );
	$lines    = explode( "\n", $contents );

	$data = array(
		'story'   => array(
			'title'       => '',
			'slug'        => '',
			'author'      => '',
			'status'      => '',
			'badge_1'     => '',
			'badge_2'     => '',
			'genres'      => array(),
			'cover'       => '',
			'description' => '',
		),
		'chapter' => array(
			'number'  => 0,
			'title'   => '',
			'slug'    => '',
			'date'    => '',
			'summary' => '',
			'content' => '',
		),
		'source'  => array(
			'file'     => $file_path,
			'basename' => basename( $file_path ),
		),
	);

	$scalar_map = array(
		'Tên truyện'         => array( 'story', 'title' ),
		'Slug truyện'        => array( 'story', 'slug' ),
		'Tác giả'            => array( 'story', 'author' ),
		'Tình trạng truyện'  => array( 'story', 'status' ),
		'Badge 1'            => array( 'story', 'badge_1' ),
		'Badge 2'            => array( 'story', 'badge_2' ),
		'Thể loại'           => array( 'story', 'genres' ),
		'Ảnh bìa'            => array( 'story', 'cover' ),
		'Mô tả truyện'       => array( 'story', 'description' ),
		'Số chương'          => array( 'chapter', 'number' ),
		'Tên chương'         => array( 'chapter', 'title' ),
		'Slug chương'        => array( 'chapter', 'slug' ),
		'Ngày đăng'          => array( 'chapter', 'date' ),
	);

	$mode          = 'header';
	$summary_lines = array();
	$content_lines = array();

	foreach ( $lines as $line ) {
		if ( 'content' === $mode ) {
			$content_lines[] = $line;
			continue;
		}

		if ( 'summary' === $mode ) {
			$maybe_content = cowm_parse_import_scalar_line( $line, 'Nội dung' );

			if ( null !== $maybe_content ) {
				$mode = 'content';

				if ( '' !== $maybe_content ) {
					$content_lines[] = $maybe_content;
				}

				continue;
			}

			$summary_lines[] = $line;
			continue;
		}

		$summary_start = cowm_parse_import_scalar_line( $line, 'Tóm tắt chương' );

		if ( null !== $summary_start ) {
			$mode = 'summary';

			if ( '' !== $summary_start ) {
				$summary_lines[] = $summary_start;
			}

			continue;
		}

		$content_start = cowm_parse_import_scalar_line( $line, 'Nội dung' );

		if ( null !== $content_start ) {
			$mode = 'content';

			if ( '' !== $content_start ) {
				$content_lines[] = $content_start;
			}

			continue;
		}

		foreach ( $scalar_map as $label => $target ) {
			$value = cowm_parse_import_scalar_line( $line, $label );

			if ( null === $value ) {
				continue;
			}

			if ( 'genres' === $target[1] ) {
				$data[ $target[0] ][ $target[1] ] = array_values(
					array_filter(
						array_map(
							'trim',
							explode( ',', $value )
						)
					)
				);
			} elseif ( 'number' === $target[1] ) {
				$data[ $target[0] ][ $target[1] ] = absint( $value );
			} else {
				$data[ $target[0] ][ $target[1] ] = $value;
			}

			break;
		}
	}

	$data['chapter']['summary'] = trim( implode( "\n", $summary_lines ) );
	$data['chapter']['content'] = trim( implode( "\n", $content_lines ) );

	return $data;
}

/**
 * Extract a chapter number from a slug or file name.
 *
 * @param string $value Raw slug or filename.
 * @return int
 */
function cowm_extract_import_chapter_number( $value ) {
	$value = (string) $value;

	if ( preg_match( '/(?:chuong|chapter)[^0-9]*0*([0-9]+)/iu', $value, $matches ) ) {
		return absint( $matches[1] );
	}

	if ( preg_match( '/(^|[^0-9])0*([0-9]+)([^0-9]|$)/u', $value, $matches ) ) {
		return absint( $matches[2] );
	}

	return 0;
}

/**
 * Resolve the effective chapter number from slug, file name, or fallback field.
 *
 * @param array $entry Parsed chapter entry.
 * @return array{number:int,warnings:string[]}
 */
function cowm_resolve_import_chapter_number( $entry ) {
	$declared_number = absint( $entry['chapter']['number'] );
	$slug_number     = cowm_extract_import_chapter_number( $entry['chapter']['slug'] );
	$file_number     = cowm_extract_import_chapter_number( $entry['source']['basename'] );
	$warnings        = array();
	$resolved_number = 0;

	if ( $slug_number ) {
		$resolved_number = $slug_number;
	} elseif ( $file_number ) {
		$resolved_number = $file_number;
	} else {
		$resolved_number = $declared_number;
	}

	if ( $declared_number && $resolved_number && $declared_number !== $resolved_number ) {
		$warnings[] = sprintf(
			/* translators: %1$d is the declared number, %2$d is the resolved number. */
			__( 'Dòng "Số chương" đang là %1$d nhưng importer sẽ dùng chương %2$d theo slug hoặc tên file.', 'comeout-with-me' ),
			$declared_number,
			$resolved_number
		);
	}

	return array(
		'number'   => $resolved_number,
		'warnings' => $warnings,
	);
}

/**
 * Merge story metadata, keeping the preferred values first and filling blanks
 * from fallback entries.
 *
 * @param array $preferred Preferred story metadata.
 * @param array $fallback  Fallback story metadata.
 * @return array
 */
function cowm_merge_import_story_data( $preferred, $fallback ) {
	$merged       = is_array( $preferred ) ? $preferred : array();
	$fallback     = is_array( $fallback ) ? $fallback : array();
	$scalar_keys  = array( 'title', 'slug', 'author', 'status', 'badge_1', 'badge_2', 'cover', 'description' );
	$merged_genres = isset( $merged['genres'] ) && is_array( $merged['genres'] ) ? $merged['genres'] : array();
	$fallback_genres = isset( $fallback['genres'] ) && is_array( $fallback['genres'] ) ? $fallback['genres'] : array();

	foreach ( $scalar_keys as $key ) {
		$current_value  = trim( (string) ( isset( $merged[ $key ] ) ? $merged[ $key ] : '' ) );
		$fallback_value = trim( (string) ( isset( $fallback[ $key ] ) ? $fallback[ $key ] : '' ) );

		if ( '' === $current_value && '' !== $fallback_value ) {
			$merged[ $key ] = $fallback_value;
		}
	}

	$merged['genres'] = array_values(
		array_unique(
			array_filter(
				array_map(
					'trim',
					array_merge( $merged_genres, $fallback_genres )
				)
			)
		)
	);

	return $merged;
}

/**
 * Resolve the story metadata source, preferring chapter 1 when present.
 *
 * @param array<int, array> $entries Parsed import entries.
 * @return array
 */
function cowm_resolve_import_story_data( $entries ) {
	if ( empty( $entries ) ) {
		return array(
			'title'       => '',
			'slug'        => '',
			'author'      => '',
			'status'      => '',
			'badge_1'     => '',
			'badge_2'     => '',
			'genres'      => array(),
			'cover'       => '',
			'description' => '',
		);
	}

	$candidates = array_values(
		array_filter(
			$entries,
			static function ( $entry ) {
				$story = isset( $entry['story'] ) && is_array( $entry['story'] ) ? $entry['story'] : array();

				return '' !== trim( (string) ( isset( $story['title'] ) ? $story['title'] : '' ) )
					|| '' !== sanitize_title( isset( $story['slug'] ) ? $story['slug'] : '' )
					|| ! empty( $story['genres'] );
			}
		)
	);

	if ( empty( $candidates ) ) {
		$first_entry = reset( $entries );

		return isset( $first_entry['story'] ) && is_array( $first_entry['story'] ) ? $first_entry['story'] : array();
	}

	usort(
		$candidates,
		static function ( $left, $right ) {
			$left_number  = absint( isset( $left['chapter']['resolved_number'] ) ? $left['chapter']['resolved_number'] : 0 );
			$right_number = absint( isset( $right['chapter']['resolved_number'] ) ? $right['chapter']['resolved_number'] : 0 );
			$left_rank    = 1 === $left_number ? 0 : ( $left_number ? 1 : 2 );
			$right_rank   = 1 === $right_number ? 0 : ( $right_number ? 1 : 2 );

			if ( $left_rank !== $right_rank ) {
				return $left_rank <=> $right_rank;
			}

			if ( $left_number !== $right_number ) {
				return $left_number <=> $right_number;
			}

			$left_name  = isset( $left['source']['basename'] ) ? (string) $left['source']['basename'] : '';
			$right_name = isset( $right['source']['basename'] ) ? (string) $right['source']['basename'] : '';

			return strnatcasecmp( $left_name, $right_name );
		}
	);

	$story_data = isset( $candidates[0]['story'] ) && is_array( $candidates[0]['story'] ) ? $candidates[0]['story'] : array();

	foreach ( array_slice( $candidates, 1 ) as $candidate ) {
		$story_data = cowm_merge_import_story_data(
			$story_data,
			isset( $candidate['story'] ) && is_array( $candidate['story'] ) ? $candidate['story'] : array()
		);
	}

	return $story_data;
}

/**
 * Validate parsed import data before writing to WordPress.
 *
 * @param array $entry Parsed file data.
 * @return array{errors:string[],warnings:string[]}
 */
function cowm_validate_import_entry( $entry ) {
	$errors   = array();
	$warnings = array();

	$placeholders = array(
		'Điền nếu có',
		'Điền tên chương',
		'Để trống nếu không có',
		'Điền nếu có. Không bắt buộc.',
		'Dán toàn bộ nội dung chương ở đây.',
		'Điền mô tả ngắn của truyện ở đây.',
	);

	$story_title   = trim( (string) $entry['story']['title'] );
	$story_slug    = sanitize_title( $entry['story']['slug'] );
	$resolved      = cowm_resolve_import_chapter_number( $entry );
	$chapter_number = absint( $resolved['number'] );
	$content       = trim( (string) $entry['chapter']['content'] );

	if ( '' === $story_title ) {
		$errors[] = __( 'Thiếu Tên truyện.', 'comeout-with-me' );
	}

	if ( '' === $story_slug ) {
		$errors[] = __( 'Thiếu Slug truyện hợp lệ.', 'comeout-with-me' );
	}

	if ( ! $chapter_number ) {
		$errors[] = __( 'Số chương phải lớn hơn 0.', 'comeout-with-me' );
	}

	if ( '' === $content ) {
		$errors[] = __( 'Nội dung chương đang trống.', 'comeout-with-me' );
	}

	foreach ( $entry['story'] as $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $nested_value ) {
				if ( in_array( trim( (string) $nested_value ), $placeholders, true ) ) {
					$warnings[] = __( 'File vẫn còn text mẫu trong phần thông tin truyện.', 'comeout-with-me' );
					break 2;
				}
			}
			continue;
		}

		if ( in_array( trim( (string) $value ), $placeholders, true ) ) {
			$warnings[] = __( 'File vẫn còn text mẫu trong phần thông tin truyện.', 'comeout-with-me' );
			break;
		}
	}

	foreach ( $entry['chapter'] as $value ) {
		if ( in_array( trim( (string) $value ), $placeholders, true ) ) {
			$warnings[] = __( 'File vẫn còn text mẫu trong phần thông tin chương.', 'comeout-with-me' );
			break;
		}
	}

	return array(
		'errors'   => array_values( array_unique( $errors ) ),
		'warnings' => array_values( array_unique( array_merge( $warnings, $resolved['warnings'] ) ) ),
	);
}

/**
 * Build a unique global chapter slug from story and chapter values.
 *
 * @param string $story_slug Story slug.
 * @param string $chapter_slug Chapter slug from file.
 * @param int    $chapter_number Chapter number.
 * @return string
 */
function cowm_build_imported_chapter_slug( $story_slug, $chapter_slug, $chapter_number ) {
	$story_slug   = sanitize_title( $story_slug );
	$chapter_slug = sanitize_title( $chapter_slug );

	if ( '' === $chapter_slug ) {
		$chapter_slug = sprintf( 'chuong-%03d', max( 1, absint( $chapter_number ) ) );
	}

	if ( '' !== $story_slug && ! str_starts_with( $chapter_slug, $story_slug . '-' ) ) {
		return sanitize_title( $story_slug . '-' . $chapter_slug );
	}

	return $chapter_slug;
}

/**
 * Remove a duplicated chapter heading from the start of imported content.
 *
 * Many raw chapter files include a first line like "Chương 1" even though the
 * theme already renders the chapter heading separately.
 *
 * @param string $content Imported content.
 * @param int    $chapter_number Chapter number.
 * @param string $chapter_title Chapter title.
 * @return string
 */
function cowm_cleanup_imported_chapter_content( $content, $chapter_number, $chapter_title ) {
	$content = trim( (string) $content );

	if ( '' === $content ) {
		return '';
	}

	$lines = preg_split( "/\n+/", $content );

	if ( empty( $lines ) ) {
		return $content;
	}

	$first_line         = trim( (string) $lines[0] );
	$normalized_first   = strtolower( preg_replace( '/\s+/u', ' ', $first_line ) );
	$expected_headings  = array(
		strtolower( preg_replace( '/\s+/u', ' ', sprintf( 'Chương %d', absint( $chapter_number ) ) ) ),
	);

	$chapter_title = trim( (string) $chapter_title );

	if ( '' !== $chapter_title ) {
		$expected_headings[] = strtolower( preg_replace( '/\s+/u', ' ', sprintf( 'Chương %d: %s', absint( $chapter_number ), $chapter_title ) ) );
		$expected_headings[] = strtolower( preg_replace( '/\s+/u', ' ', $chapter_title ) );
	}

	if ( in_array( $normalized_first, array_unique( $expected_headings ), true ) ) {
		array_shift( $lines );
	}

	return trim( implode( "\n", $lines ) );
}

/**
 * Find an existing imported chapter for a story and number.
 *
 * @param int    $story_id Story post ID.
 * @param int    $chapter_number Chapter number.
 * @param string $chapter_slug Unique chapter slug.
 * @return WP_Post|null
 */
function cowm_find_existing_imported_chapter( $story_id, $chapter_number, $chapter_slug ) {
	$matches = get_posts(
		array(
			'post_type'              => 'cowm_chapter',
			'post_status'            => array( 'publish', 'draft', 'pending', 'future', 'private' ),
			'posts_per_page'         => 1,
			'meta_query'             => array(
				array(
					'key'   => 'cowm_story_id',
					'value' => absint( $story_id ),
				),
				array(
					'key'   => 'cowm_chapter_number',
					'value' => absint( $chapter_number ),
				),
			),
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( ! empty( $matches ) ) {
		return $matches[0];
	}

	$by_slug = get_page_by_path( $chapter_slug, OBJECT, 'cowm_chapter' );

	return $by_slug instanceof WP_Post ? $by_slug : null;
}

/**
 * Copy a local cover image into the media library and reuse prior imports when possible.
 *
 * @param string $file_path Absolute file path.
 * @param int    $parent_post_id Parent story post ID.
 * @return int|WP_Error
 */
function cowm_import_local_attachment( $file_path, $parent_post_id = 0 ) {
	$resolved = realpath( $file_path );

	if ( ! $resolved || ! is_file( $resolved ) ) {
		return new WP_Error( 'missing_cover', __( 'Không tìm thấy file ảnh bìa.', 'comeout-with-me' ) );
	}

	$source_hash = md5( $resolved . '|' . filemtime( $resolved ) . '|' . filesize( $resolved ) );
	$existing    = get_posts(
		array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'meta_key'               => 'cowm_import_source_hash',
			'meta_value'             => $source_hash,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$uploads = wp_upload_dir();

	if ( ! empty( $uploads['error'] ) ) {
		return new WP_Error( 'upload_dir_failed', $uploads['error'] );
	}

	$filename    = wp_unique_filename( $uploads['path'], wp_basename( $resolved ) );
	$destination = trailingslashit( $uploads['path'] ) . $filename;

	if ( ! copy( $resolved, $destination ) ) {
		return new WP_Error( 'copy_cover_failed', __( 'Không copy được ảnh bìa vào thư mục uploads.', 'comeout-with-me' ) );
	}

	$filetype = wp_check_filetype( $filename, null );

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$destination,
		$parent_post_id,
		true
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $destination );
		return $attachment_id;
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $destination );
	wp_update_attachment_metadata( $attachment_id, $metadata );
	update_post_meta( $attachment_id, 'cowm_import_source_hash', $source_hash );
	update_post_meta( $attachment_id, 'cowm_import_source_file', wp_basename( $resolved ) );

	return (int) $attachment_id;
}

/**
 * Resolve an imported asset path and gracefully fall back across common image extensions.
 *
 * @param string $folder_path Story folder path.
 * @param string $relative_path Relative file path from the import file.
 * @return string
 */
function cowm_resolve_import_asset_path( $folder_path, $relative_path ) {
	$relative_path = ltrim( (string) $relative_path, '/' );

	if ( '' === $relative_path ) {
		return '';
	}

	$exact_path = trailingslashit( $folder_path ) . $relative_path;
	$resolved   = realpath( $exact_path );

	if ( $resolved && is_file( $resolved ) ) {
		return $resolved;
	}

	$dirname   = pathinfo( $relative_path, PATHINFO_DIRNAME );
	$filename  = pathinfo( $relative_path, PATHINFO_FILENAME );
	$extension = strtolower( (string) pathinfo( $relative_path, PATHINFO_EXTENSION ) );
	$image_ext = array( 'jpg', 'jpeg', 'png', 'webp', 'gif', 'avif' );

	if ( '' === $filename || ! in_array( $extension, $image_ext, true ) ) {
		return '';
	}

	foreach ( $image_ext as $candidate_extension ) {
		if ( $candidate_extension === $extension ) {
			continue;
		}

		$candidate_relative = '.' === $dirname || '' === $dirname
			? $filename . '.' . $candidate_extension
			: trailingslashit( $dirname ) . $filename . '.' . $candidate_extension;
		$candidate_path     = trailingslashit( $folder_path ) . $candidate_relative;
		$candidate_real     = realpath( $candidate_path );

		if ( $candidate_real && is_file( $candidate_real ) ) {
			return $candidate_real;
		}
	}

	return '';
}

/**
 * Check if local auto sync should run for imports.
 *
 * @return bool
 */
function cowm_is_import_auto_sync_enabled() {
	$host = strtolower( (string) parse_url( home_url( '/' ), PHP_URL_HOST ) );
	$env  = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : '';

	$is_local_host = in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) || str_ends_with( $host, '.local' );
	$enabled       = $is_local_host || in_array( $env, array( 'local', 'development' ), true );

	/**
	 * Filter whether story import auto sync is enabled.
	 *
	 * @param bool $enabled Whether auto sync is enabled.
	 */
	return (bool) apply_filters( 'cowm_enable_import_auto_sync', $enabled );
}

/**
 * Return direct story folders inside the import root.
 *
 * @return string[]
 */
function cowm_get_import_story_folders() {
	$import_root = cowm_get_import_root();
	$folders     = glob( trailingslashit( $import_root ) . '*', GLOB_ONLYDIR );

	if ( empty( $folders ) ) {
		return array();
	}

	sort( $folders, SORT_NATURAL | SORT_FLAG_CASE );

	return array_values( array_filter( $folders, 'is_dir' ) );
}

/**
 * Create a stable state key for one import folder.
 *
 * @param string $folder_path Absolute folder path.
 * @return string
 */
function cowm_get_import_folder_state_key( $folder_path ) {
	$import_root = realpath( cowm_get_import_root() );
	$folder_path = realpath( $folder_path );

	if ( ! $import_root || ! $folder_path ) {
		return sanitize_title( basename( (string) $folder_path ) );
	}

	$relative = ltrim( str_replace( $import_root, '', $folder_path ), DIRECTORY_SEPARATOR );

	return '' !== $relative ? str_replace( DIRECTORY_SEPARATOR, '/', $relative ) : basename( $folder_path );
}

/**
 * Build a change signature for one import folder.
 *
 * @param string $folder_path Absolute folder path.
 * @return string
 */
function cowm_get_import_folder_signature( $folder_path ) {
	$folder_path = realpath( $folder_path );

	if ( ! $folder_path || ! is_dir( $folder_path ) ) {
		return '';
	}

	$entries = array();

	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator(
			$folder_path,
			FilesystemIterator::SKIP_DOTS
		)
	);

	foreach ( $iterator as $file_info ) {
		if ( ! $file_info instanceof SplFileInfo || ! $file_info->isFile() ) {
			continue;
		}

		$filename = $file_info->getFilename();

		if ( '.' === $filename[0] ) {
			continue;
		}

		$pathname = $file_info->getPathname();
		$relative = ltrim( str_replace( $folder_path, '', $pathname ), DIRECTORY_SEPARATOR );

		$entries[] = implode(
			'|',
			array(
				str_replace( DIRECTORY_SEPARATOR, '/', $relative ),
				(string) $file_info->getMTime(),
				(string) $file_info->getSize(),
			)
		);
	}

	sort( $entries, SORT_NATURAL | SORT_FLAG_CASE );

	return md5( implode( "\n", $entries ) );
}

/**
 * Count non-empty chapter text files inside one import folder.
 *
 * @param string $folder_path Absolute folder path.
 * @return int
 */
function cowm_count_non_empty_import_text_files( $folder_path ) {
	$chapter_files = glob( trailingslashit( $folder_path ) . '*.txt' );
	$count         = 0;

	if ( empty( $chapter_files ) ) {
		return 0;
	}

	foreach ( $chapter_files as $chapter_file ) {
		$raw_contents = file_get_contents( $chapter_file );

		if ( false === $raw_contents ) {
			continue;
		}

		if ( '' !== trim( (string) $raw_contents ) ) {
			$count++;
		}
	}

	return $count;
}

/**
 * Get stored auto sync state.
 *
 * @return array<string,mixed>
 */
function cowm_get_import_auto_sync_state() {
	$version = '2026-04-20-auto-sync-v3';
	$state = get_option( 'cowm_import_auto_sync_state', array() );

	if ( ! is_array( $state ) ) {
		$state = array();
	}

	if ( ! isset( $state['version'] ) || $version !== $state['version'] ) {
		$state = array(
			'version'        => $version,
			'success_hashes' => array(),
			'error_hashes'   => array(),
		);
	}

	$state['version']        = $version;
	$state['success_hashes'] = isset( $state['success_hashes'] ) && is_array( $state['success_hashes'] ) ? $state['success_hashes'] : array();
	$state['error_hashes']   = isset( $state['error_hashes'] ) && is_array( $state['error_hashes'] ) ? $state['error_hashes'] : array();

	return $state;
}

/**
 * Persist auto sync state.
 *
 * @param array<string,mixed> $state State payload.
 * @return void
 */
function cowm_set_import_auto_sync_state( $state ) {
	update_option( 'cowm_import_auto_sync_state', $state, false );
}

/**
 * Save the latest auto sync report for inspection in admin.
 *
 * @param array<string,mixed> $report Report payload.
 * @return void
 */
function cowm_set_import_auto_sync_report( $report ) {
	update_option( 'cowm_import_auto_sync_last_report', $report, false );
}

/**
 * Return the latest auto sync report.
 *
 * @return array<string,mixed>|null
 */
function cowm_get_import_auto_sync_report() {
	$report = get_option( 'cowm_import_auto_sync_last_report', null );

	return is_array( $report ) ? $report : null;
}

/**
 * Import every ready story folder inside the import root.
 *
 * @return array<string, mixed>
 */
function cowm_import_all_story_folders() {
	$folders = cowm_get_import_story_folders();
	$report  = array(
		'successes' => array(),
		'skipped'   => array(),
		'errors'    => array(),
	);

	foreach ( $folders as $folder_path ) {
		$folder_key = cowm_get_import_folder_state_key( $folder_path );

		if ( 0 === cowm_count_non_empty_import_text_files( $folder_path ) ) {
			$report['skipped'][] = array(
				'folder'  => $folder_key,
				'message' => __( 'Folder này chưa có file chương hoàn chỉnh.', 'comeout-with-me' ),
			);
			continue;
		}

		$result = cowm_import_story_folder(
			$folder_path,
			array(
				'skip_invalid_entries' => true,
			)
		);

		if ( is_wp_error( $result ) ) {
			$report['errors'][] = array(
				'folder'  => $folder_key,
				'message' => $result->get_error_message(),
			);
			continue;
		}

		$report['successes'][] = array(
			'folder'        => $folder_key,
			'story_title'   => $result['story_title'],
			'chapter_count' => $result['chapter_count'],
			'warnings'      => $result['warnings'],
		);
	}

	return $report;
}

/**
 * Get the remote sync token from wp-config.php.
 *
 * @return string
 */
function cowm_get_remote_sync_token() {
	return defined( 'COWM_REMOTE_SYNC_TOKEN' ) ? trim( (string) COWM_REMOTE_SYNC_TOKEN ) : '';
}

/**
 * Check whether a remote sync token is valid.
 *
 * @param string $provided_token Token from request.
 * @return bool
 */
function cowm_is_valid_remote_sync_token( $provided_token ) {
	$configured_token = cowm_get_remote_sync_token();
	$provided_token   = trim( (string) $provided_token );

	if ( '' === $configured_token || '' === $provided_token ) {
		return false;
	}

	return hash_equals( $configured_token, $provided_token );
}

/**
 * Handle a protected remote sync request after deployment.
 *
 * This endpoint is intended for deployment tools. Example:
 * /wp-admin/admin-post.php?action=cowm_story_remote_sync&token=YOUR_SECRET
 *
 * @return void
 */
function cowm_handle_remote_story_sync_request() {
	file_put_contents( WP_CONTENT_DIR . '/debug-manual.log', "sync reached\n", FILE_APPEND );
	error_log( 'COWM sync: handler reached' );
	error_log( 'COWM sync action: ' . ( isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'missing' ) );
	error_log( 'COWM sync token: ' . ( isset( $_REQUEST['token'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['token'] ) ) : 'missing' ) );
	$token = isset( $_REQUEST['token'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['token'] ) ) : '';

	if ( ! cowm_is_valid_remote_sync_token( $token ) ) {
		error_log( 'COWM sync: invalid token' );
		status_header( 403 );
		wp_send_json(
			array(
				'success' => false,
				'message' => __( 'Remote sync token không hợp lệ hoặc chưa được cấu hình.', 'comeout-with-me' ),
			),
			403
		);
	}

	$report = cowm_import_all_story_folders();
	$has_errors = ! empty( $report['errors'] );

	if ( $has_errors ) {
		error_log( 'COWM sync: import has errors' );
		status_header( 500 );
		wp_send_json(
			array(
				'success' => false,
				'message' => __( 'Đã chạy remote sync nhưng vẫn còn folder lỗi.', 'comeout-with-me' ),
				'report'  => $report,
			),
			500
		);
	}

	error_log( 'COWM sync: success' );
	wp_send_json(
		array(
			'success' => true,
			'message' => __( 'Remote sync hoàn tất.', 'comeout-with-me' ),
			'report'  => $report,
		)
	);
}
add_action( 'admin_post_nopriv_cowm_story_remote_sync', 'cowm_handle_remote_story_sync_request' );
add_action( 'admin_post_cowm_story_remote_sync', 'cowm_handle_remote_story_sync_request' );

/**
 * Auto sync import folders on local environments when files change.
 *
 * @return void
 */
function cowm_maybe_auto_sync_story_imports() {
	static $running = false;

	if ( $running || ! cowm_is_import_auto_sync_enabled() ) {
		return;
	}

	if ( wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}

	if ( isset( $_REQUEST['action'] ) && 'cowm_story_import' === $_REQUEST['action'] ) {
		return;
	}

	$folders = cowm_get_import_story_folders();

	if ( empty( $folders ) ) {
		return;
	}

	$running      = true;
	$state        = cowm_get_import_auto_sync_state();
	$successes    = $state['success_hashes'];
	$errors       = $state['error_hashes'];
	$active_keys  = array();
	$events       = array();

	foreach ( $folders as $folder_path ) {
		$key       = cowm_get_import_folder_state_key( $folder_path );
		$signature = cowm_get_import_folder_signature( $folder_path );

		if ( '' === $signature ) {
			continue;
		}

		$active_keys[] = $key;

		if ( ( isset( $successes[ $key ] ) && $signature === $successes[ $key ] ) || ( isset( $errors[ $key ] ) && $signature === $errors[ $key ] ) ) {
			continue;
		}

		if ( 0 === cowm_count_non_empty_import_text_files( $folder_path ) ) {
			$errors[ $key ] = $signature;
			unset( $successes[ $key ] );

			$events[] = array(
				'status'  => 'skipped',
				'folder'  => $key,
				'message' => __( 'Folder này chưa có file chương hoàn chỉnh nên auto sync đang tạm bỏ qua.', 'comeout-with-me' ),
			);

			continue;
		}

		$result = cowm_import_story_folder(
			$folder_path,
			array(
				'skip_invalid_entries' => true,
			)
		);

		if ( is_wp_error( $result ) ) {
			$errors[ $key ] = $signature;
			unset( $successes[ $key ] );

			$events[] = array(
				'status'  => 'error',
				'folder'  => $key,
				'message' => $result->get_error_message(),
			);

			continue;
		}

		$successes[ $key ] = $signature;
		unset( $errors[ $key ] );

		$events[] = array(
			'status'        => 'success',
			'folder'        => $key,
			'story_title'   => $result['story_title'],
			'chapter_count' => $result['chapter_count'],
			'warnings'      => $result['warnings'],
		);
	}

	$active_lookup         = array_fill_keys( $active_keys, true );
	$state['success_hashes'] = array_intersect_key( $successes, $active_lookup );
	$state['error_hashes']   = array_intersect_key( $errors, $active_lookup );
	cowm_set_import_auto_sync_state( $state );

	if ( ! empty( $events ) ) {
		cowm_set_import_auto_sync_report(
			array(
				'time'   => current_time( 'timestamp' ),
				'events' => $events,
			)
		);
	}

	$running = false;
}
add_action( 'init', 'cowm_maybe_auto_sync_story_imports', 50 );

/**
 * Upsert the parent story post from parsed import data.
 *
 * @param array  $story_data Parsed story data.
 * @param string $folder_path Source folder path.
 * @return array|WP_Error
 */
function cowm_upsert_import_story( $story_data, $folder_path ) {
	$story_slug  = sanitize_title( $story_data['slug'] );
	$story_title = trim( (string) $story_data['title'] );
	$story_post  = get_page_by_path( $story_slug, OBJECT, 'cowm_story' );

	$postarr = array(
		'post_type'    => 'cowm_story',
		'post_status'  => 'publish',
		'post_title'   => $story_title,
		'post_name'    => $story_slug,
		'post_excerpt' => trim( (string) $story_data['description'] ),
		'post_content' => '',
	);

	if ( $story_post instanceof WP_Post ) {
		$postarr['ID'] = $story_post->ID;
	}

	$story_id = wp_insert_post( wp_slash( $postarr ), true );

	if ( is_wp_error( $story_id ) ) {
		return $story_id;
	}

	$display_badge_1 = trim( (string) $story_data['badge_1'] );
	$display_badge_2 = trim( (string) $story_data['badge_2'] );
	$status_text     = trim( (string) $story_data['status'] );

	if ( '' === $display_badge_1 ) {
		$display_badge_1 = $status_text;
	}

	if ( '' === $display_badge_1 ) {
		delete_post_meta( $story_id, 'cowm_status_label' );
	} else {
		update_post_meta( $story_id, 'cowm_status_label', $display_badge_1 );
	}

	if ( '' === $display_badge_2 ) {
		delete_post_meta( $story_id, 'cowm_secondary_label' );
	} else {
		update_post_meta( $story_id, 'cowm_secondary_label', $display_badge_2 );
	}

	delete_post_meta( $story_id, 'cowm_progress_label' );

	if ( '' === trim( (string) $story_data['author'] ) ) {
		delete_post_meta( $story_id, 'cowm_story_author_name' );
	} else {
		update_post_meta( $story_id, 'cowm_story_author_name', trim( (string) $story_data['author'] ) );
	}

	if ( '' === $status_text ) {
		delete_post_meta( $story_id, 'cowm_story_status_text' );
	} else {
		update_post_meta( $story_id, 'cowm_story_status_text', $status_text );
	}

	update_post_meta( $story_id, 'cowm_story_import_folder', $folder_path );

	$genres = array_values(
		array_unique(
			array_filter(
				array_map(
					'sanitize_text_field',
					(array) $story_data['genres']
				)
			)
		)
	);

	if ( ! empty( $genres ) ) {
		wp_set_object_terms( $story_id, $genres, 'post_tag', false );
	}

	$cover = trim( (string) $story_data['cover'] );

	if ( '' !== $cover ) {
		$cover_path    = cowm_resolve_import_asset_path( $folder_path, $cover );

		if ( '' === $cover_path ) {
			return new WP_Error(
				'missing_cover',
				sprintf(
					/* translators: %s is the requested cover path from the import file. */
					__( 'Không tìm thấy ảnh bìa: %s', 'comeout-with-me' ),
					$cover
				)
			);
		}

		$attachment_id = cowm_import_local_attachment( $cover_path, $story_id );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		set_post_thumbnail( $story_id, $attachment_id );
	}

	return array(
		'story_id' => (int) $story_id,
		'updated'  => $story_post instanceof WP_Post,
	);
}

/**
 * Upsert one chapter post from parsed import data.
 *
 * @param int   $story_id Story post ID.
 * @param array $chapter_data Parsed chapter data.
 * @param string $story_slug Parent story slug.
 * @return array|WP_Error
 */
function cowm_upsert_import_chapter( $story_id, $chapter_data, $story_slug ) {
	$chapter_number = absint( ! empty( $chapter_data['resolved_number'] ) ? $chapter_data['resolved_number'] : $chapter_data['number'] );
	$chapter_title  = (string) $chapter_data['title'];
	$chapter_slug   = cowm_build_imported_chapter_slug( $story_slug, $chapter_data['slug'], $chapter_number );
	$existing       = cowm_find_existing_imported_chapter( $story_id, $chapter_number, $chapter_slug );
	$chapter_content = cowm_cleanup_imported_chapter_content( $chapter_data['content'], $chapter_number, $chapter_title );

	$postarr = array(
		'post_type'    => 'cowm_chapter',
		'post_status'  => 'publish',
		'post_title'   => $chapter_title,
		'post_name'    => $chapter_slug,
		'post_excerpt' => trim( (string) $chapter_data['summary'] ),
		'post_content' => $chapter_content,
	);

	if ( $existing instanceof WP_Post ) {
		$postarr['ID'] = $existing->ID;
	}

	$raw_date = trim( (string) $chapter_data['date'] );

	if ( '' !== $raw_date ) {
		$timestamp = strtotime( $raw_date );

		if ( false !== $timestamp ) {
			$timezone     = wp_timezone();
			$local_string = wp_date( 'Y-m-d H:i:s', $timestamp, $timezone );
			$postarr['post_date']     = $local_string;
			$postarr['post_date_gmt'] = get_gmt_from_date( $local_string );
		}
	}

	$chapter_id = wp_insert_post( wp_slash( $postarr ), true );

	if ( is_wp_error( $chapter_id ) ) {
		return $chapter_id;
	}

	update_post_meta( $chapter_id, 'cowm_story_id', absint( $story_id ) );
	update_post_meta( $chapter_id, 'cowm_chapter_number', $chapter_number );

	return array(
		'chapter_id' => (int) $chapter_id,
		'updated'    => $existing instanceof WP_Post,
		'slug'       => $chapter_slug,
	);
}

/**
 * Import a story folder that contains one or more chapter text files.
 *
 * @param string              $folder_input Folder name, relative path, or absolute path.
 * @param array<string,mixed> $args         Import behavior flags.
 * @return array|WP_Error
 */
function cowm_import_story_folder( $folder_input, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'skip_invalid_entries' => false,
		)
	);

	$folder_path = cowm_normalize_import_folder_path( $folder_input );

	if ( is_wp_error( $folder_path ) ) {
		return $folder_path;
	}

	$chapter_files = glob( trailingslashit( $folder_path ) . '*.txt' );

	if ( empty( $chapter_files ) ) {
		return new WP_Error( 'missing_chapter_files', __( 'Không có file .txt nào trong thư mục import này.', 'comeout-with-me' ) );
	}

	natsort( $chapter_files );
	$chapter_files = array_values( $chapter_files );

	$entries       = array();
	$story_sources = array();
	$warnings      = array();

	foreach ( $chapter_files as $chapter_file ) {
		$raw_contents = file_get_contents( $chapter_file );

		if ( false === $raw_contents ) {
			return new WP_Error(
				'read_failed',
				sprintf(
					__( 'Không đọc được file %s.', 'comeout-with-me' ),
					basename( $chapter_file )
				)
			);
		}

		if ( '' === trim( (string) $raw_contents ) ) {
			$warnings[] = sprintf(
				/* translators: %s is the empty file name. */
				__( 'Đã bỏ qua file rỗng: %s', 'comeout-with-me' ),
				basename( $chapter_file )
			);
			continue;
		}

		$entry = cowm_parse_import_chapter_file( $chapter_file );

		if ( is_wp_error( $entry ) ) {
			if ( ! empty( $args['skip_invalid_entries'] ) ) {
				$warnings[] = sprintf(
					/* translators: 1: file name, 2: error message. */
					__( 'Đã bỏ qua file chưa hoàn chỉnh %1$s: %2$s', 'comeout-with-me' ),
					basename( $chapter_file ),
					$entry->get_error_message()
				);
				continue;
			}

			return $entry;
		}

		$resolved_number                    = cowm_resolve_import_chapter_number( $entry );
		$entry['chapter']['resolved_number'] = $resolved_number['number'];
		$story_sources[]                    = $entry;
		$validation                         = cowm_validate_import_entry( $entry );

		if ( ! empty( $validation['errors'] ) ) {
			if ( ! empty( $args['skip_invalid_entries'] ) ) {
				$warnings[] = sprintf(
					/* translators: 1: file name, 2: validation error list. */
					__( 'Đã bỏ qua file chưa hoàn chỉnh %1$s: %2$s', 'comeout-with-me' ),
					basename( $chapter_file ),
					implode( ' ', $validation['errors'] )
				);
				continue;
			}

			return new WP_Error(
				'invalid_import_file',
				sprintf(
					/* translators: %1$s is the file name, %2$s is the validation error list. */
					__( 'File %1$s chưa hợp lệ: %2$s', 'comeout-with-me' ),
					basename( $chapter_file ),
					implode( ' ', $validation['errors'] )
				)
			);
		}

		if ( ! empty( $validation['warnings'] ) ) {
			$warnings = array_merge(
				$warnings,
				array_map(
					static function ( $message ) use ( $chapter_file ) {
						return basename( $chapter_file ) . ': ' . $message;
					},
					$validation['warnings']
				)
			);
		}

		$entries[] = $entry;
	}

	if ( empty( $entries ) ) {
		return new WP_Error( 'missing_valid_chapters', __( 'Không có file chương hợp lệ nào để import.', 'comeout-with-me' ) );
	}

	$primary_story = cowm_resolve_import_story_data( ! empty( $story_sources ) ? $story_sources : $entries );
	$story_result  = cowm_upsert_import_story( $primary_story, $folder_path );

	if ( is_wp_error( $story_result ) ) {
		return $story_result;
	}

	$story_id       = (int) $story_result['story_id'];
	$story_slug     = sanitize_title( $primary_story['slug'] );
	$chapter_report = array();

	foreach ( $entries as $entry ) {
		$chapter_result = cowm_upsert_import_chapter( $story_id, $entry['chapter'], $story_slug );

		if ( is_wp_error( $chapter_result ) ) {
			return $chapter_result;
		}

		$chapter_report[] = $chapter_result;
	}

	cowm_sync_story_chapter_cache( $story_id );

	return array(
		'folder'          => $folder_path,
		'story_id'        => $story_id,
		'story_title'     => get_the_title( $story_id ),
		'story_permalink' => get_permalink( $story_id ),
		'story_updated'   => ! empty( $story_result['updated'] ),
		'chapter_count'   => count( $chapter_report ),
		'chapters'        => $chapter_report,
		'warnings'        => array_values( array_unique( $warnings ) ),
	);
}

/**
 * Add importer screen under Tools.
 *
 * @return void
 */
function cowm_register_story_import_admin_page() {
	add_management_page(
		__( 'Import truyện', 'comeout-with-me' ),
		__( 'Import truyện', 'comeout-with-me' ),
		'manage_options',
		'cowm-story-import',
		'cowm_render_story_import_admin_page'
	);
}
add_action( 'admin_menu', 'cowm_register_story_import_admin_page' );

/**
 * Store an importer notice for the current admin user.
 *
 * @param array $payload Notice payload.
 * @return void
 */
function cowm_set_story_import_notice( $payload ) {
	set_transient( 'cowm_story_import_notice_' . get_current_user_id(), $payload, MINUTE_IN_SECONDS * 5 );
}

/**
 * Get and clear the importer notice for the current admin user.
 *
 * @return array|null
 */
function cowm_pull_story_import_notice() {
	$key    = 'cowm_story_import_notice_' . get_current_user_id();
	$notice = get_transient( $key );

	if ( false === $notice ) {
		return null;
	}

	delete_transient( $key );

	return is_array( $notice ) ? $notice : null;
}

/**
 * Handle the admin import form.
 *
 * @return void
 */
function cowm_handle_story_import_admin_action() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Bạn không có quyền import truyện.', 'comeout-with-me' ) );
	}

	check_admin_referer( 'cowm_story_import' );

	$folder = isset( $_POST['cowm_import_folder'] ) ? sanitize_text_field( wp_unslash( $_POST['cowm_import_folder'] ) ) : '';
	$result = cowm_import_story_folder( $folder );

	if ( is_wp_error( $result ) ) {
		cowm_set_story_import_notice(
			array(
				'type'    => 'error',
				'message' => $result->get_error_message(),
			)
		);
	} else {
		$state     = cowm_get_import_auto_sync_state();
		$signature = cowm_get_import_folder_signature( $result['folder'] );
		$key       = cowm_get_import_folder_state_key( $result['folder'] );

		if ( '' !== $signature ) {
			$state['success_hashes'][ $key ] = $signature;
			unset( $state['error_hashes'][ $key ] );
			cowm_set_import_auto_sync_state( $state );
		}

		cowm_set_story_import_notice(
			array(
				'type'    => 'success',
				'message' => sprintf(
					/* translators: %1$s is story title, %2$d is number of imported chapters. */
					__( 'Đã import truyện "%1$s" với %2$d chương.', 'comeout-with-me' ),
					$result['story_title'],
					$result['chapter_count']
				),
				'result'  => $result,
			)
		);
	}

	wp_safe_redirect( admin_url( 'tools.php?page=cowm-story-import' ) );
	exit;
}
add_action( 'admin_post_cowm_story_import', 'cowm_handle_story_import_admin_action' );

/**
 * Render the admin importer page.
 *
 * @return void
 */
function cowm_render_story_import_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$import_root = cowm_get_import_root();
	$folders     = glob( trailingslashit( $import_root ) . '*', GLOB_ONLYDIR );
	$notice      = cowm_pull_story_import_notice();
	$auto_report = cowm_get_import_auto_sync_report();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import truyện từ folder', 'comeout-with-me' ); ?></h1>
		<p><?php esc_html_e( 'Nhập tên folder trong thư mục import của theme, ví dụ: mot-vien-keo. Bạn cũng có thể dán đường dẫn tuyệt đối nếu nó nằm trong theme/import.', 'comeout-with-me' ); ?></p>
		<p><code><?php echo esc_html( $import_root ); ?></code></p>
		<p>
			<strong><?php esc_html_e( 'Auto sync local:', 'comeout-with-me' ); ?></strong>
			<?php echo cowm_is_import_auto_sync_enabled() ? esc_html__( 'Đang bật. Khi file trong import/ đổi, lần tải trang local kế tiếp sẽ tự import lại. File chương đang soạn dở sẽ được bỏ qua thay vì chặn cả folder.', 'comeout-with-me' ) : esc_html__( 'Đang tắt.', 'comeout-with-me' ); ?>
		</p>

		<?php if ( $notice ) : ?>
			<div class="notice notice-<?php echo 'error' === $notice['type'] ? 'error' : 'success'; ?> is-dismissible">
				<p><?php echo esc_html( $notice['message'] ); ?></p>

				<?php if ( ! empty( $notice['result']['warnings'] ) ) : ?>
					<p><strong><?php esc_html_e( 'Cảnh báo:', 'comeout-with-me' ); ?></strong></p>
					<ul>
						<?php foreach ( $notice['result']['warnings'] as $warning ) : ?>
							<li><?php echo esc_html( $warning ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( ! empty( $notice['result']['story_permalink'] ) ) : ?>
					<p>
						<a class="button button-secondary" href="<?php echo esc_url( $notice['result']['story_permalink'] ); ?>" target="_blank" rel="noreferrer">
							<?php esc_html_e( 'Mở truyện vừa import', 'comeout-with-me' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $auto_report && ! empty( $auto_report['events'] ) ) : ?>
			<div class="notice notice-info">
				<p>
					<strong><?php esc_html_e( 'Lần auto sync gần nhất:', 'comeout-with-me' ); ?></strong>
					<?php echo esc_html( wp_date( 'H:i d/m/Y', absint( $auto_report['time'] ) ) ); ?>
				</p>
				<ul>
					<?php foreach ( $auto_report['events'] as $event ) : ?>
						<li>
							<?php
							if ( 'success' === $event['status'] ) {
								echo esc_html(
									sprintf(
										/* translators: %1$s is the folder name, %2$s is the story title, %3$d is imported chapter count. */
										__( '[OK] %1$s -> %2$s (%3$d chương)', 'comeout-with-me' ),
										$event['folder'],
										$event['story_title'],
										$event['chapter_count']
									)
								);
							} elseif ( 'skipped' === $event['status'] ) {
								echo esc_html(
									sprintf(
										/* translators: %1$s is the folder name, %2$s is the skip reason. */
										__( '[Bỏ qua] %1$s -> %2$s', 'comeout-with-me' ),
										$event['folder'],
										$event['message']
									)
								);
							} else {
								echo esc_html(
									sprintf(
										/* translators: %1$s is the folder name, %2$s is the error message. */
										__( '[Lỗi] %1$s -> %2$s', 'comeout-with-me' ),
										$event['folder'],
										$event['message']
									)
								);
							}
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="cowm_story_import"/>
			<?php wp_nonce_field( 'cowm_story_import' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cowm_import_folder"><?php esc_html_e( 'Folder cần import', 'comeout-with-me' ); ?></label></th>
					<td>
						<input name="cowm_import_folder" type="text" id="cowm_import_folder" class="regular-text" placeholder="mot-vien-keo"/>
						<p class="description"><?php esc_html_e( 'Importer sẽ đọc tất cả file .txt trong folder này.', 'comeout-with-me' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Import ngay', 'comeout-with-me' ) ); ?>
		</form>

		<?php if ( ! empty( $folders ) ) : ?>
			<hr/>
			<h2><?php esc_html_e( 'Folder đang có sẵn', 'comeout-with-me' ); ?></h2>
			<ul>
				<?php foreach ( $folders as $folder ) : ?>
					<li><code><?php echo esc_html( basename( $folder ) ); ?></code></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}
