<?php
/**
 * Story and chapter content model.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register story and chapter post types.
 */
function cowm_register_story_post_types() {
	register_post_type(
		'cowm_story',
		array(
			'labels' => array(
				'name'               => __( 'Truyện', 'comeout-with-me' ),
				'singular_name'      => __( 'Truyện', 'comeout-with-me' ),
				'add_new'            => __( 'Thêm truyện', 'comeout-with-me' ),
				'add_new_item'       => __( 'Thêm truyện mới', 'comeout-with-me' ),
				'edit_item'          => __( 'Sửa truyện', 'comeout-with-me' ),
				'new_item'           => __( 'Truyện mới', 'comeout-with-me' ),
				'view_item'          => __( 'Xem truyện', 'comeout-with-me' ),
				'search_items'       => __( 'Tìm truyện', 'comeout-with-me' ),
				'not_found'          => __( 'Chưa có truyện nào.', 'comeout-with-me' ),
				'not_found_in_trash' => __( 'Không có truyện nào trong thùng rác.', 'comeout-with-me' ),
				'all_items'          => __( 'Tất cả truyện', 'comeout-with-me' ),
				'archives'           => __( 'Kho truyện', 'comeout-with-me' ),
				'menu_name'          => __( 'Truyện', 'comeout-with-me' ),
			),
			'public'             => true,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_in_nav_menus'  => true,
			'has_archive'        => true,
			'rewrite'            => array(
				'slug'       => 'truyen',
				'with_front' => false,
			),
			'menu_icon'          => 'dashicons-book-alt',
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'taxonomies'         => array( 'category', 'post_tag' ),
		)
	);

	register_post_type(
		'cowm_chapter',
		array(
			'labels' => array(
				'name'               => __( 'Chương', 'comeout-with-me' ),
				'singular_name'      => __( 'Chương', 'comeout-with-me' ),
				'add_new'            => __( 'Thêm chương', 'comeout-with-me' ),
				'add_new_item'       => __( 'Thêm chương mới', 'comeout-with-me' ),
				'edit_item'          => __( 'Sửa chương', 'comeout-with-me' ),
				'new_item'           => __( 'Chương mới', 'comeout-with-me' ),
				'view_item'          => __( 'Xem chương', 'comeout-with-me' ),
				'search_items'       => __( 'Tìm chương', 'comeout-with-me' ),
				'not_found'          => __( 'Chưa có chương nào.', 'comeout-with-me' ),
				'not_found_in_trash' => __( 'Không có chương nào trong thùng rác.', 'comeout-with-me' ),
				'all_items'          => __( 'Tất cả chương', 'comeout-with-me' ),
				'menu_name'          => __( 'Chương', 'comeout-with-me' ),
			),
			'public'             => true,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_in_nav_menus'  => false,
			'has_archive'        => false,
			'exclude_from_search'=> true,
			'rewrite'            => array(
				'slug'       => 'chuong',
				'with_front' => false,
			),
			'menu_icon'          => 'dashicons-media-document',
			'supports'           => array( 'title', 'editor', 'excerpt', 'revisions' ),
		)
	);
}
add_action( 'init', 'cowm_register_story_post_types', 5 );

/**
 * Register chapter relationship and cache meta.
 */
function cowm_register_story_library_meta() {
	register_post_meta(
		'cowm_chapter',
		'cowm_story_id',
		array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'cowm_chapter',
		'cowm_chapter_number',
		array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	foreach ( array( 'cowm_latest_chapter_id', 'cowm_latest_chapter_number', 'cowm_latest_chapter_timestamp', 'cowm_chapter_count' ) as $meta_key ) {
		register_post_meta(
			'cowm_story',
			$meta_key,
			array(
				'show_in_rest'      => false,
				'single'            => true,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'cowm_register_story_library_meta', 20 );

/**
 * Flush rewrite rules once when story library schema changes.
 */
function cowm_maybe_flush_story_rewrite_rules() {
	$version = '2026-04-19-story-library-v1';

	if ( get_option( 'cowm_story_rewrite_version' ) === $version ) {
		return;
	}

	cowm_register_story_post_types();
	flush_rewrite_rules( false );
	update_option( 'cowm_story_rewrite_version', $version, false );
}
add_action( 'init', 'cowm_maybe_flush_story_rewrite_rules', 99 );

/**
 * Return raw chapter posts for a story, sorted in PHP for reliable ordering.
 *
 * @param int         $story_id     Story post ID.
 * @param string[]    $post_status  Allowed post statuses.
 * @return WP_Post[]
 */
function cowm_get_story_chapter_posts( $story_id, $post_status = array( 'publish' ) ) {
	$story_id = absint( $story_id );

	if ( ! $story_id || 'cowm_story' !== get_post_type( $story_id ) ) {
		return array();
	}

	$chapters = get_posts(
		array(
			'post_type'              => 'cowm_chapter',
			'post_status'            => $post_status,
			'posts_per_page'         => -1,
			'orderby'                => 'date',
			'order'                  => 'ASC',
			'meta_key'               => 'cowm_story_id',
			'meta_value'             => $story_id,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);

	if ( empty( $chapters ) ) {
		return array();
	}

	usort(
		$chapters,
		static function ( $left, $right ) {
			$left_number  = absint( get_post_meta( $left->ID, 'cowm_chapter_number', true ) );
			$right_number = absint( get_post_meta( $right->ID, 'cowm_chapter_number', true ) );

			if ( $left_number !== $right_number ) {
				return $left_number <=> $right_number;
			}

			$left_timestamp  = get_post_time( 'U', true, $left );
			$right_timestamp = get_post_time( 'U', true, $right );

			if ( $left_timestamp !== $right_timestamp ) {
				return $left_timestamp <=> $right_timestamp;
			}

			return $left->ID <=> $right->ID;
		}
	);

	return $chapters;
}

/**
 * Sync story cache data from its published chapters.
 *
 * @param int $story_id Story post ID.
 * @return void
 */
function cowm_sync_story_chapter_cache( $story_id ) {
	$story_id = absint( $story_id );

	if ( ! $story_id || 'cowm_story' !== get_post_type( $story_id ) ) {
		return;
	}

	$chapters      = cowm_get_story_chapter_posts( $story_id, array( 'publish' ) );
	$chapter_count = count( $chapters );

	if ( 0 === $chapter_count ) {
		delete_post_meta( $story_id, 'cowm_latest_chapter_id' );
		delete_post_meta( $story_id, 'cowm_latest_chapter_number' );
		delete_post_meta( $story_id, 'cowm_latest_chapter_timestamp' );
		update_post_meta( $story_id, 'cowm_chapter_count', 0 );
		return;
	}

	$latest_chapter     = $chapters[ $chapter_count - 1 ];
	$latest_timestamp   = get_post_modified_time( 'U', true, $latest_chapter );
	$latest_number      = absint( get_post_meta( $latest_chapter->ID, 'cowm_chapter_number', true ) );

	update_post_meta( $story_id, 'cowm_latest_chapter_id', $latest_chapter->ID );
	update_post_meta( $story_id, 'cowm_latest_chapter_number', $latest_number );
	update_post_meta( $story_id, 'cowm_latest_chapter_timestamp', absint( $latest_timestamp ) );
	update_post_meta( $story_id, 'cowm_chapter_count', $chapter_count );
}

/**
 * Add chapter relationship meta box.
 */
function cowm_add_chapter_meta_box() {
	add_meta_box(
		'cowm_chapter_relationship',
		__( 'Thông tin chương', 'comeout-with-me' ),
		'cowm_render_chapter_meta_box',
		'cowm_chapter',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'cowm_add_chapter_meta_box' );

/**
 * Render chapter meta box.
 *
 * @param WP_Post $post Current chapter post.
 * @return void
 */
function cowm_render_chapter_meta_box( $post ) {
	$selected_story  = absint( get_post_meta( $post->ID, 'cowm_story_id', true ) );
	$chapter_number  = absint( get_post_meta( $post->ID, 'cowm_chapter_number', true ) );
	$stories         = get_posts(
		array(
			'post_type'              => 'cowm_story',
			'post_status'            => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	wp_nonce_field( 'cowm_save_chapter_meta', 'cowm_chapter_meta_nonce' );
	?>
	<p>
		<label for="cowm_story_id"><strong><?php esc_html_e( 'Thuộc truyện', 'comeout-with-me' ); ?></strong></label>
		<select class="widefat" id="cowm_story_id" name="cowm_story_id">
			<option value="0"><?php esc_html_e( 'Chọn truyện', 'comeout-with-me' ); ?></option>
			<?php foreach ( $stories as $story ) : ?>
				<option value="<?php echo esc_attr( $story->ID ); ?>" <?php selected( $selected_story, $story->ID ); ?>><?php echo esc_html( get_the_title( $story ) ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="cowm_chapter_number"><strong><?php esc_html_e( 'Số chương', 'comeout-with-me' ); ?></strong></label>
		<input type="number" class="widefat" min="1" step="1" id="cowm_chapter_number" name="cowm_chapter_number" value="<?php echo esc_attr( $chapter_number ); ?>" placeholder="<?php esc_attr_e( 'Ví dụ: 45', 'comeout-with-me' ); ?>"/>
	</p>
	<p class="description"><?php esc_html_e( 'Tiêu đề bài viết có thể là tên riêng của chương, ví dụ “Ánh sáng cuối đường hầm”.', 'comeout-with-me' ); ?></p>
	<?php
}

/**
 * Save chapter meta and sync story caches.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @return void
 */
function cowm_save_chapter_meta( $post_id, $post ) {
	if ( 'cowm_chapter' !== $post->post_type ) {
		return;
	}

	if ( ! isset( $_POST['cowm_chapter_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cowm_chapter_meta_nonce'] ) ), 'cowm_save_chapter_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$old_story_id    = absint( get_post_meta( $post_id, 'cowm_story_id', true ) );
	$new_story_id    = isset( $_POST['cowm_story_id'] ) ? absint( wp_unslash( $_POST['cowm_story_id'] ) ) : 0;
	$chapter_number  = isset( $_POST['cowm_chapter_number'] ) ? absint( wp_unslash( $_POST['cowm_chapter_number'] ) ) : 0;

	if ( $new_story_id && 'cowm_story' !== get_post_type( $new_story_id ) ) {
		$new_story_id = 0;
	}

	if ( $new_story_id ) {
		update_post_meta( $post_id, 'cowm_story_id', $new_story_id );
	} else {
		delete_post_meta( $post_id, 'cowm_story_id' );
	}

	if ( $chapter_number ) {
		update_post_meta( $post_id, 'cowm_chapter_number', $chapter_number );
	} else {
		delete_post_meta( $post_id, 'cowm_chapter_number' );
	}

	if ( $old_story_id && $old_story_id !== $new_story_id ) {
		cowm_sync_story_chapter_cache( $old_story_id );
	}

	if ( $new_story_id ) {
		cowm_sync_story_chapter_cache( $new_story_id );
	}
}
add_action( 'save_post_cowm_chapter', 'cowm_save_chapter_meta', 10, 2 );

/**
 * Sync story caches when a chapter is deleted or trashed.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function cowm_sync_story_on_chapter_lifecycle( $post_id ) {
	if ( 'cowm_chapter' !== get_post_type( $post_id ) ) {
		return;
	}

	$story_id = absint( get_post_meta( $post_id, 'cowm_story_id', true ) );

	if ( $story_id ) {
		cowm_sync_story_chapter_cache( $story_id );
	}
}
add_action( 'wp_trash_post', 'cowm_sync_story_on_chapter_lifecycle' );
add_action( 'untrashed_post', 'cowm_sync_story_on_chapter_lifecycle' );
add_action( 'before_delete_post', 'cowm_sync_story_on_chapter_lifecycle' );

/**
 * Add useful admin columns for chapters.
 *
 * @param array<string, string> $columns Existing columns.
 * @return array<string, string>
 */
function cowm_set_chapter_admin_columns( $columns ) {
	$columns['cowm_story']   = __( 'Truyện', 'comeout-with-me' );
	$columns['cowm_number']  = __( 'Chương', 'comeout-with-me' );
	return $columns;
}
add_filter( 'manage_cowm_chapter_posts_columns', 'cowm_set_chapter_admin_columns' );

/**
 * Render chapter admin column values.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function cowm_render_chapter_admin_columns( $column, $post_id ) {
	if ( 'cowm_story' === $column ) {
		$story_id = absint( get_post_meta( $post_id, 'cowm_story_id', true ) );
		echo $story_id ? esc_html( get_the_title( $story_id ) ) : '—';
		return;
	}

	if ( 'cowm_number' === $column ) {
		$number = absint( get_post_meta( $post_id, 'cowm_chapter_number', true ) );
		echo $number ? esc_html( sprintf( __( 'Chương %d', 'comeout-with-me' ), $number ) ) : '—';
	}
}
add_action( 'manage_cowm_chapter_posts_custom_column', 'cowm_render_chapter_admin_columns', 10, 2 );

/**
 * Register custom public query vars for story archives.
 *
 * @param string[] $vars Existing public query vars.
 * @return string[]
 */
function cowm_register_story_query_vars( $vars ) {
	$vars[] = 'story_category';
	$vars[] = 'story_tag';

	return $vars;
}
add_filter( 'query_vars', 'cowm_register_story_query_vars' );

/**
 * Sort story archives by latest chapter activity.
 *
 * @param WP_Query $query Main query.
 * @return void
 */
function cowm_tune_story_archive_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$post_type = $query->get( 'post_type' );
	$is_story_query = $query->is_post_type_archive( 'cowm_story' );

	if ( ! $is_story_query ) {
		if ( is_array( $post_type ) ) {
			$is_story_query = in_array( 'cowm_story', $post_type, true );
		} else {
			$is_story_query = 'cowm_story' === $post_type;
		}
	}

	if ( ! $is_story_query ) {
		return;
	}

	$story_category = absint( $query->get( 'story_category' ) );
	$story_tag      = absint( $query->get( 'story_tag' ) );
	$tax_query      = array();

	if ( $story_category ) {
		$tax_query[] = array(
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => $story_category,
		);
	}

	if ( $story_tag ) {
		$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'field'    => 'term_id',
			'terms'    => $story_tag,
		);
	}

	if ( ! empty( $tax_query ) ) {
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		$query->set( 'tax_query', $tax_query );
	}

	$query->set( 'meta_key', 'cowm_latest_chapter_timestamp' );
	$query->set( 'posts_per_page', 10 );
	$query->set(
		'orderby',
		array(
			'meta_value_num' => 'DESC',
			'date'           => 'DESC',
		)
	);
}
add_action( 'pre_get_posts', 'cowm_tune_story_archive_queries' );
