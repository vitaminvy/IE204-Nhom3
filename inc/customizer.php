<?php
/**
 * Theme Customizer configuration.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get category choices for select controls.
 *
 * @return array<int, string>
 */
function cowm_get_category_choices() {
	$choices = array(
		0 => __( 'Tat ca danh muc', 'comeout-with-me' ),
	);

	$categories = get_categories(
		array(
			'hide_empty' => false,
		)
	);

	foreach ( $categories as $category ) {
		$choices[ (int) $category->term_id ] = $category->name;
	}

	return $choices;
}

/**
 * Get post type choices for select controls.
 *
 * @return array<string, string>
 */
function cowm_get_post_type_choices() {
	$choices = array();
	$types   = get_post_types(
		array(
			'public'  => true,
			'show_ui' => true,
		),
		'objects'
	);

	foreach ( $types as $slug => $type ) {
		if ( 'attachment' === $slug || 'page' === $slug || 'cowm_chapter' === $slug ) {
			continue;
		}

		$choices[ $slug ] = $type->labels->singular_name;
	}

	return $choices;
}

/**
 * Register theme options.
 *
 * @param WP_Customize_Manager $wp_customize Manager instance.
 */
function cowm_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'cowm_homepage_panel',
		array(
			'title'       => __( 'Come Out With Me Homepage', 'comeout-with-me' ),
			'description' => __( 'Edit hero content, homepage cards and dynamic section sources.', 'comeout-with-me' ),
			'priority'    => 30,
		)
	);

	$wp_customize->add_section(
		'cowm_hero_section',
		array(
			'title' => __( 'Hero Section', 'comeout-with-me' ),
			'panel' => 'cowm_homepage_panel',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_eyebrow',
		array(
			'default'           => 'Dossier Vol. 01',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_hero_eyebrow',
		array(
			'label'   => __( 'Hero Eyebrow', 'comeout-with-me' ),
			'section' => 'cowm_hero_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_title',
		array(
			'default'           => 'Come Out With Me',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_hero_title',
		array(
			'label'   => __( 'Hero Title', 'comeout-with-me' ),
			'section' => 'cowm_hero_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_description',
		array(
			'default'           => 'Nơi lưu trữ những hồ sơ "mỹ cường" đầy mê hoặc. Khám phá bản ngã, tìm kiếm những mảnh ghép tâm hồn qua từng chương truyện được tuyển chọn kỹ lưỡng.',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'cowm_hero_description',
		array(
			'label'   => __( 'Hero Description', 'comeout-with-me' ),
			'section' => 'cowm_hero_section',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_primary_label',
		array(
			'default'           => __( 'Bắt đầu đọc', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_hero_primary_label',
		array(
			'label'   => __( 'Primary Button Label', 'comeout-with-me' ),
			'section' => 'cowm_hero_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_primary_url',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'cowm_hero_primary_url',
		array(
			'label'   => __( 'Primary Button URL', 'comeout-with-me' ),
			'section' => 'cowm_hero_section',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_secondary_label',
		array(
			'default'           => __( 'Khám phá web', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_hero_secondary_label',
		array(
			'label'   => __( 'Secondary Button Label', 'comeout-with-me' ),
			'section' => 'cowm_hero_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_secondary_url',
		array(
			'default'           => home_url( '/#phac-hoa' ),
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'cowm_hero_secondary_url',
		array(
			'label'   => __( 'Secondary Button URL', 'comeout-with-me' ),
			'section' => 'cowm_hero_section',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_card_label',
		array(
			'default'           => 'Case #2024-001',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_hero_card_label',
		array(
			'label'   => __( 'Hero Card Label', 'comeout-with-me' ),
			'section' => 'cowm_hero_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_card_title',
		array(
			'default'           => 'The Secret History',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_hero_card_title',
		array(
			'label'   => __( 'Hero Card Title', 'comeout-with-me' ),
			'section' => 'cowm_hero_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_hero_image',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'cowm_hero_image',
			array(
				'label'      => __( 'Hero Image', 'comeout-with-me' ),
				'section'    => 'cowm_hero_section',
				'mime_type'  => 'image',
			)
		)
	);

	$wp_customize->add_section(
		'cowm_pillars_section',
		array(
			'title' => __( 'Content Pillars', 'comeout-with-me' ),
			'panel' => 'cowm_homepage_panel',
		)
	);

	$wp_customize->add_setting(
		'cowm_pillars_title',
		array(
			'default'           => __( 'Danh mục nội dung nổi bật', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_pillars_title',
		array(
			'label'   => __( 'Section Title', 'comeout-with-me' ),
			'section' => 'cowm_pillars_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_pillars_description',
		array(
			'default'           => __( 'Ba lối vào chính giúp độc giả tìm truyện, lọc gu đọc và theo dõi review một cách rõ ràng hơn.', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'cowm_pillars_description',
		array(
			'label'   => __( 'Section Description', 'comeout-with-me' ),
			'section' => 'cowm_pillars_section',
			'type'    => 'textarea',
		)
	);

	foreach ( array( 1, 2, 3 ) as $index ) {
			$wp_customize->add_setting(
				"cowm_pillar_{$index}_title",
				array(
					'default'           => 1 === $index ? __( 'Chuyên Án', 'comeout-with-me' ) : ( 2 === $index ? __( 'Phác Họa Chân Dung', 'comeout-with-me' ) : __( 'Trà đá vỉa hè', 'comeout-with-me' ) ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
		$wp_customize->add_control(
			"cowm_pillar_{$index}_title",
			array(
				'label'   => sprintf( __( 'Card %d Title', 'comeout-with-me' ), $index ),
				'section' => 'cowm_pillars_section',
				'type'    => 'text',
			)
		);

			$wp_customize->add_setting(
				"cowm_pillar_{$index}_text",
				array(
					'default'           => 1 === $index
						? __( 'Lưu trữ các bộ truyện BL mỹ cường dài kỳ, được biên tập tỉ mỉ và dễ dàng mở rộng thành kho hồ sơ riêng.', 'comeout-with-me' )
						: ( 2 === $index
							? __( 'Hệ thống lọc gu đọc theo tag, bối cảnh, trạng thái và nhân vật để độc giả tìm đúng bộ phù hợp.', 'comeout-with-me' )
							: __( 'Góc blog, review và toplist để chia sẻ cảm nhận, mood đọc và gợi ý cho cộng đồng.', 'comeout-with-me' ) ),
					'sanitize_callback' => 'sanitize_textarea_field',
				)
			);
		$wp_customize->add_control(
			"cowm_pillar_{$index}_text",
			array(
				'label'   => sprintf( __( 'Card %d Description', 'comeout-with-me' ), $index ),
				'section' => 'cowm_pillars_section',
				'type'    => 'textarea',
			)
		);

			$wp_customize->add_setting(
				"cowm_pillar_{$index}_label",
				array(
					'default'           => 1 === $index ? __( 'Xem tất cả', 'comeout-with-me' ) : ( 2 === $index ? __( 'Tìm kiếm gu', 'comeout-with-me' ) : __( 'Ghé quán', 'comeout-with-me' ) ),
					'sanitize_callback' => 'sanitize_text_field',
				)
			);
		$wp_customize->add_control(
			"cowm_pillar_{$index}_label",
			array(
				'label'   => sprintf( __( 'Card %d Button Label', 'comeout-with-me' ), $index ),
				'section' => 'cowm_pillars_section',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			"cowm_pillar_{$index}_url",
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			"cowm_pillar_{$index}_url",
			array(
				'label'   => sprintf( __( 'Card %d Button URL', 'comeout-with-me' ), $index ),
				'section' => 'cowm_pillars_section',
				'type'    => 'url',
			)
		);
	}

	$wp_customize->add_section(
		'cowm_stories_section',
		array(
			'title' => __( 'Story Feed', 'comeout-with-me' ),
			'panel' => 'cowm_homepage_panel',
		)
	);

	$wp_customize->add_setting(
		'cowm_stories_eyebrow',
		array(
			'default'           => 'Latest Files',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_stories_eyebrow',
		array(
			'label'   => __( 'Section Eyebrow', 'comeout-with-me' ),
			'section' => 'cowm_stories_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_stories_title',
		array(
			'default'           => __( 'Truyện mới cập nhật', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_stories_title',
		array(
			'label'   => __( 'Section Title', 'comeout-with-me' ),
			'section' => 'cowm_stories_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_stories_link_label',
		array(
			'default'           => __( 'Xem dòng thời gian', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_stories_link_label',
		array(
			'label'   => __( 'Archive Link Label', 'comeout-with-me' ),
			'section' => 'cowm_stories_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_stories_post_type',
		array(
			'default'           => 'cowm_story',
			'sanitize_callback' => 'cowm_sanitize_post_type',
		)
	);
	$wp_customize->add_control(
		'cowm_stories_post_type',
		array(
			'label'   => __( 'Source Post Type', 'comeout-with-me' ),
			'section' => 'cowm_stories_section',
			'type'    => 'select',
			'choices' => cowm_get_post_type_choices(),
		)
	);

	$wp_customize->add_setting(
		'cowm_stories_category',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'cowm_stories_category',
		array(
			'label'   => __( 'Source Category', 'comeout-with-me' ),
			'section' => 'cowm_stories_section',
			'type'    => 'select',
			'choices' => cowm_get_category_choices(),
		)
	);

	$wp_customize->add_setting(
		'cowm_stories_count',
		array(
			'default'           => 6,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'cowm_stories_count',
		array(
			'label'       => __( 'Number of Story Cards', 'comeout-with-me' ),
			'description' => __( 'The first two posts render as featured cards. The next four render as compact cards.', 'comeout-with-me' ),
			'section'     => 'cowm_stories_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 2,
				'max' => 12,
			),
		)
	);

	$wp_customize->add_section(
		'cowm_highlights_section',
		array(
			'title' => __( 'Highlight Chips', 'comeout-with-me' ),
			'panel' => 'cowm_homepage_panel',
		)
	);

	$wp_customize->add_setting(
		'cowm_highlights_eyebrow',
		array(
			'default'           => 'Classification',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_highlights_eyebrow',
		array(
			'label'   => __( 'Section Eyebrow', 'comeout-with-me' ),
			'section' => 'cowm_highlights_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_highlights_title',
		array(
			'default'           => __( 'Danh mục hồ sơ nổi bật', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_highlights_title',
		array(
			'label'   => __( 'Section Title', 'comeout-with-me' ),
			'section' => 'cowm_highlights_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_highlights_limit',
		array(
			'default'           => 7,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'cowm_highlights_limit',
		array(
			'label'       => __( 'Number of Highlight Chips', 'comeout-with-me' ),
			'description' => __( 'Terms are pulled from popular post tags first, then categories if no tags exist.', 'comeout-with-me' ),
			'section'     => 'cowm_highlights_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 3,
				'max' => 12,
			),
		)
	);

	$wp_customize->add_section(
		'cowm_blog_section',
		array(
			'title' => __( 'Blog and Review Feed', 'comeout-with-me' ),
			'panel' => 'cowm_homepage_panel',
		)
	);

	$wp_customize->add_setting(
		'cowm_blog_eyebrow',
		array(
			'default'           => 'Notes from the Sidewalk',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_blog_eyebrow',
		array(
			'label'   => __( 'Section Eyebrow', 'comeout-with-me' ),
			'section' => 'cowm_blog_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_blog_title',
		array(
			'default'           => __( 'Trà đá vỉa hè và tâm tình', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_blog_title',
		array(
			'label'   => __( 'Section Title', 'comeout-with-me' ),
			'section' => 'cowm_blog_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_blog_description',
		array(
			'default'           => __( 'Góc blog dành cho review truyện, top list tuyển chọn và những cuộc trò chuyện nhỏ xoay quanh thế giới đam mỹ mỹ cường.', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'cowm_blog_description',
		array(
			'label'   => __( 'Section Description', 'comeout-with-me' ),
			'section' => 'cowm_blog_section',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'cowm_blog_button_label',
		array(
			'default'           => __( 'Khám phá blog', 'comeout-with-me' ),
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_blog_button_label',
		array(
			'label'   => __( 'Button Label', 'comeout-with-me' ),
			'section' => 'cowm_blog_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'cowm_blog_category',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'cowm_blog_category',
		array(
			'label'   => __( 'Blog Category', 'comeout-with-me' ),
			'section' => 'cowm_blog_section',
			'type'    => 'select',
			'choices' => cowm_get_category_choices(),
		)
	);

	$wp_customize->add_setting(
		'cowm_blog_count',
		array(
			'default'           => 2,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'cowm_blog_count',
		array(
			'label'       => __( 'Number of Blog Cards', 'comeout-with-me' ),
			'description' => __( 'Designed for 2 cards, but can render more if needed.', 'comeout-with-me' ),
			'section'     => 'cowm_blog_section',
			'type'        => 'number',
			'input_attrs' => array(
				'min' => 1,
				'max' => 6,
			),
		)
	);

	$wp_customize->add_section(
		'cowm_footer_section',
		array(
			'title' => __( 'Footer', 'comeout-with-me' ),
			'panel' => 'cowm_homepage_panel',
		)
	);

	$wp_customize->add_setting(
		'cowm_footer_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'cowm_footer_text',
		array(
			'label'       => __( 'Footer Copyright Text', 'comeout-with-me' ),
			'description' => __( 'Leave blank to use the default auto-generated copyright.', 'comeout-with-me' ),
			'section'     => 'cowm_footer_section',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'cowm_customize_register' );
