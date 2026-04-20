<?php
/**
 * Homepage hero section.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero_title         = trim( (string) get_theme_mod( 'cowm_hero_title', 'Come Out With Me' ) );
$hero_description   = trim( (string) get_theme_mod( 'cowm_hero_description', 'Nơi lưu trữ những hồ sơ "mỹ cường" đầy mê hoặc. Khám phá bản ngã, tìm kiếm những mảnh ghép tâm hồn qua từng chương truyện được tuyển chọn kỹ lưỡng.' ) );
$hero_primary_label = cowm_normalize_legacy_copy(
	trim( (string) get_theme_mod( 'cowm_hero_primary_label', __( 'Bắt đầu đọc', 'comeout-with-me' ) ) ),
	array(
		'Bat dau doc' => 'Bắt đầu đọc',
		'BAT DAU DOC' => 'Bắt đầu đọc',
		'Đọc'         => 'Bắt đầu đọc',
	)
);
$hero_secondary_label = cowm_normalize_legacy_copy(
	trim( (string) get_theme_mod( 'cowm_hero_secondary_label', __( 'Khám phá web', 'comeout-with-me' ) ) ),
	array(
		'Ve du an'  => 'Khám phá web',
		'Về dự án' => 'Khám phá web',
	)
);
$hero_primary_url   = get_theme_mod( 'cowm_hero_primary_url', '' );
$hero_secondary_url = get_theme_mod( 'cowm_hero_secondary_url', '' );
$hero_image_id      = absint( get_theme_mod( 'cowm_hero_image', 0 ) );
$hero_local_image_url = cowm_get_theme_asset_image_url(
	array(
		'assets/images/hero-local.webp',
		'assets/images/hero-local.png',
		'assets/images/hero-local.jpg',
		'assets/images/hero-local.jpeg',
	)
);
$hero_image_url     = $hero_local_image_url ? $hero_local_image_url : cowm_get_theme_image_url(
	'cowm_hero_image',
	'https://lh3.googleusercontent.com/aida-public/AB6AXuBQEAGyWY6L8YnqZeLrwFi4Ji1wshsUO6FKPpxAsuIym7UQzGKoEXUtLBjtm60Z2PABDWlWrceBAsxMYNhBJGHbVV6aJGt49kDoBvvCHPjU6a0yu1UHHBycY-0aD34J9hJwE5X8D9uqcTb4F78RhgaAdpLYJwG1RyKG6_5F89E-wKImXgsBzsj6IFkJXq4sUBuwq0ML5qjZeFfb5jm4tGxdGWCKcnRf25AIBhwJStPzzmXnsbqkxpMbCeH6mTH7cyHJwLfph4K7oFxQ',
	'cowm-hero'
);
$hero_image_alt     = $hero_image_id ? cowm_get_attachment_alt( $hero_image_id, $hero_title ) : sprintf( __( 'Hero image for %s', 'comeout-with-me' ), $hero_title );
$hero_primary_url   = $hero_primary_url ? $hero_primary_url : cowm_get_story_archive_url();
$hero_secondary_url = trim( (string) $hero_secondary_url );

if (
	'' === $hero_secondary_url
	|| in_array(
		$hero_secondary_url,
		array(
			home_url( '/about/' ),
			home_url( '/about' ),
			'/about/',
			'/about',
			home_url( '/#phac-hoa' ),
			home_url( '/#phac-hoa/' ),
			'/#phac-hoa',
			'#phac-hoa',
		),
		true
	)
) {
	$hero_secondary_url = cowm_get_profile_board_page_url();
}
?>
<section class="hero-section site-shell">
	<div class="hero-grid">
		<div class="hero-copy">
			<p class="section-eyebrow"><?php echo esc_html( get_theme_mod( 'cowm_hero_eyebrow', 'Dossier Vol. 01' ) ); ?></p>
			<h1 class="hero-title"><?php echo esc_html( $hero_title ); ?></h1>
			<p class="hero-description"><?php echo esc_html( $hero_description ); ?></p>

			<div class="hero-actions">
				<a class="button button--primary" href="<?php echo esc_url( $hero_primary_url ); ?>"><?php echo esc_html( $hero_primary_label ); ?></a>
				<a class="button button--secondary" href="<?php echo esc_url( $hero_secondary_url ); ?>"><?php echo esc_html( $hero_secondary_label ); ?></a>
			</div>
		</div>

		<div class="hero-media">
			<div class="hero-media__glow" aria-hidden="true"></div>
			<div class="hero-media__frame">
				<img src="<?php echo esc_url( $hero_image_url ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" class="hero-media__image"/>
				<div class="hero-media__case-card">
					<p class="hero-media__case-label"><?php echo esc_html( get_theme_mod( 'cowm_hero_card_label', 'Case #2024-001' ) ); ?></p>
					<p class="hero-media__case-title"><?php echo esc_html( get_theme_mod( 'cowm_hero_card_title', 'The Secret History' ) ); ?></p>
				</div>
			</div>
		</div>
	</div>
</section>
