<?php
/**
 * Homepage pillar cards.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$legacy_copy_map = array(
	'Danh muc noi dung noi bat'                                                                     => 'Danh mục nội dung nổi bật',
	'Ba loi vao chinh giup doc gia tim truyen, loc gu doc va theo doi review mot cach ro rang hon.' => 'Ba lối vào chính giúp độc giả tìm truyện, lọc gu đọc và theo dõi review một cách rõ ràng hơn.',
	'Chuyen An'                                                                                     => 'Chuyên Án',
	'Luu tru cac bo truyen BL my cuong dai ky, duoc bien tap ti mi va de dang mo rong thanh kho ho so rieng.' => 'Lưu trữ các bộ truyện BL mỹ cường dài kỳ, được biên tập tỉ mỉ và dễ dàng mở rộng thành kho hồ sơ riêng.',
	'Xem tat ca'                                                                                    => 'Xem tất cả',
	'Phac Hoa'                                                                                      => 'Phác Họa',
	'He thong loc gu doc theo tag, boi canh, trang thai va nhan vat de doc gia tim dung bo phu hop.' => 'Hệ thống lọc gu đọc theo tag, bối cảnh, trạng thái và nhân vật để độc giả tìm đúng bộ phù hợp.',
	'Tim kiem gu'                                                                                   => 'Tìm kiếm gu',
	'Tra da via he'                                                                                 => 'Trà đá vỉa hè',
	'Goc blog, review va toplist de chia se cam nhan, mood doc va goi y cho cong dong.'             => 'Góc blog, review và toplist để chia sẻ cảm nhận, mood đọc và gợi ý cho cộng đồng.',
	'Ghe quan'                                                                                      => 'Ghé quán',
);

$profile_board_url = trim( (string) get_theme_mod( 'cowm_pillar_2_url', '' ) );

if (
	'' === $profile_board_url
	|| in_array(
		$profile_board_url,
		array(
			home_url( '/#phac-hoa' ),
			home_url( '/#phac-hoa/' ),
			'/#phac-hoa',
			'#phac-hoa',
		),
		true
	)
) {
	$profile_board_url = cowm_get_profile_board_page_url();
}

$pillars = array(
	array(
		'icon'  => 'folder',
		'title' => cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillar_1_title', __( 'Chuyên Án', 'comeout-with-me' ) ), $legacy_copy_map ),
		'text'  => cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillar_1_text', __( 'Lưu trữ các bộ truyện BL mỹ cường dài kỳ, được biên tập tỉ mỉ và dễ dàng mở rộng thành kho hồ sơ riêng.', 'comeout-with-me' ) ), $legacy_copy_map ),
		'label' => cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillar_1_label', __( 'Xem tất cả', 'comeout-with-me' ) ), $legacy_copy_map ),
		'url'   => get_theme_mod( 'cowm_pillar_1_url', '' ),
	),
	array(
		'icon'  => 'filter',
		'title' => cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillar_2_title', __( 'Phác Họa', 'comeout-with-me' ) ), $legacy_copy_map ),
		'text'  => cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillar_2_text', __( 'Hệ thống lọc gu đọc theo tag, bối cảnh, trạng thái và nhân vật để độc giả tìm đúng bộ phù hợp.', 'comeout-with-me' ) ), $legacy_copy_map ),
		'label' => cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillar_2_label', __( 'Tìm kiếm gu', 'comeout-with-me' ) ), $legacy_copy_map ),
		'url'   => $profile_board_url,
	),
	array(
		'icon'  => 'cafe',
		'title' => cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillar_3_title', __( 'Trà đá vỉa hè', 'comeout-with-me' ) ), $legacy_copy_map ),
		'text'  => cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillar_3_text', __( 'Góc blog, review và toplist để chia sẻ cảm nhận, mood đọc và gợi ý cho cộng đồng.', 'comeout-with-me' ) ), $legacy_copy_map ),
		'label' => cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillar_3_label', __( 'Ghé quán', 'comeout-with-me' ) ), $legacy_copy_map ),
		'url'   => get_theme_mod( 'cowm_pillar_3_url', '' ),
	),
);
?>
<section class="pillars-section" id="phac-hoa">
	<div class="site-shell">
		<div class="section-heading section-heading--centered">
			<h2 class="section-title"><?php echo esc_html( cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillars_title', __( 'Danh mục nội dung nổi bật', 'comeout-with-me' ) ), $legacy_copy_map ) ); ?></h2>
			<p class="section-description"><?php echo esc_html( cowm_normalize_legacy_copy( (string) get_theme_mod( 'cowm_pillars_description', __( 'Ba lối vào chính giúp độc giả tìm truyện, lọc gu đọc và theo dõi review một cách rõ ràng hơn.', 'comeout-with-me' ) ), $legacy_copy_map ) ); ?></p>
		</div>

		<div class="pillars-grid">
			<?php foreach ( $pillars as $pillar ) : ?>
				<article class="pillar-card">
					<div class="pillar-card__icon">
						<?php echo cowm_get_icon( $pillar['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<h3 class="pillar-card__title"><?php echo esc_html( $pillar['title'] ); ?></h3>
					<p class="pillar-card__text"><?php echo esc_html( $pillar['text'] ); ?></p>
					<?php if ( ! empty( $pillar['url'] ) ) : ?>
						<a class="pillar-card__link" href="<?php echo esc_url( $pillar['url'] ); ?>"><?php echo esc_html( $pillar['label'] ); ?></a>
					<?php else : ?>
						<span class="pillar-card__link pillar-card__link--disabled"><?php echo esc_html( $pillar['label'] ); ?></span>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
