<?php
/**
 * Meta box support for story cards.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register story meta so it is available in admin and REST.
 */
function cowm_register_story_meta() {
	$post_types = get_post_types(
		array(
			'public'  => true,
			'show_ui' => true,
		),
		'names'
	);

	foreach ( $post_types as $post_type ) {
		if ( 'attachment' === $post_type || 'page' === $post_type || 'cowm_chapter' === $post_type ) {
			continue;
		}

		register_post_meta(
			$post_type,
			'cowm_status_label',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_post_meta(
			$post_type,
			'cowm_secondary_label',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		register_post_meta(
			$post_type,
			'cowm_progress_label',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'cowm_register_story_meta', 20 );

/**
 * Add meta box to public content types.
 */
function cowm_add_story_meta_box() {
	$post_types = get_post_types(
		array(
			'public'  => true,
			'show_ui' => true,
		),
		'names'
	);

	foreach ( $post_types as $post_type ) {
		if ( 'attachment' === $post_type || 'page' === $post_type || 'cowm_chapter' === $post_type ) {
			continue;
		}

		add_meta_box(
			'cowm_story_card_meta',
			__( 'Homepage Story Card Details', 'comeout-with-me' ),
			'cowm_render_story_meta_box',
			$post_type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'cowm_add_story_meta_box' );

/**
 * Render meta box HTML.
 *
 * @param WP_Post $post Current post object.
 */
function cowm_render_story_meta_box( $post ) {
	wp_nonce_field( 'cowm_save_story_meta', 'cowm_story_meta_nonce' );

	$status   = get_post_meta( $post->ID, 'cowm_status_label', true );
	$secondary = get_post_meta( $post->ID, 'cowm_secondary_label', true );
	$progress = get_post_meta( $post->ID, 'cowm_progress_label', true );
	?>
	<p>
		<label for="cowm_status_label"><strong><?php esc_html_e( 'Status badge', 'comeout-with-me' ); ?></strong></label>
		<input type="text" class="widefat" id="cowm_status_label" name="cowm_status_label" value="<?php echo esc_attr( (string) $status ); ?>" placeholder="<?php esc_attr_e( 'Dang ra / Hoan thanh', 'comeout-with-me' ); ?>"/>
	</p>
	<p>
		<label for="cowm_secondary_label"><strong><?php esc_html_e( 'Secondary badge', 'comeout-with-me' ); ?></strong></label>
		<input type="text" class="widefat" id="cowm_secondary_label" name="cowm_secondary_label" value="<?php echo esc_attr( (string) $secondary ); ?>" placeholder="<?php esc_attr_e( 'My Cuong / Co Trang', 'comeout-with-me' ); ?>"/>
	</p>
	<p>
		<label for="cowm_progress_label"><strong><?php esc_html_e( 'Progress label', 'comeout-with-me' ); ?></strong></label>
		<input type="text" class="widefat" id="cowm_progress_label" name="cowm_progress_label" value="<?php echo esc_attr( (string) $progress ); ?>" placeholder="<?php esc_attr_e( 'Chuong 45 / END', 'comeout-with-me' ); ?>"/>
	</p>
	<?php
}

/**
 * Save meta box data.
 *
 * @param int $post_id Post ID.
 */
function cowm_save_story_meta( $post_id ) {
	if ( ! isset( $_POST['cowm_story_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cowm_story_meta_nonce'] ) ), 'cowm_save_story_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'cowm_status_label',
		'cowm_secondary_label',
		'cowm_progress_label',
	);

	foreach ( $fields as $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';

		if ( '' === $value ) {
			delete_post_meta( $post_id, $field );
		} else {
			update_post_meta( $post_id, $field, $value );
		}
	}
}
add_action( 'save_post', 'cowm_save_story_meta' );
