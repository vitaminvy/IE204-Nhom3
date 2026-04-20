<?php
/**
 * Special introduction post template for "chao-moi-nguoi".
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();

	$story_archive_url  = function_exists( 'cowm_get_story_archive_url' ) ? cowm_get_story_archive_url() : home_url( '/' );
	$profile_board_url  = function_exists( 'cowm_get_profile_board_page_url' ) ? cowm_get_profile_board_page_url() : home_url( '/' );
	$sidewalk_page_url  = function_exists( 'cowm_get_blog_archive_url' ) ? cowm_get_blog_archive_url() : home_url( '/' );
	$contact_page       = get_page_by_path( 'hop-thu-mat' );
	$contact_url        = $contact_page instanceof WP_Post ? get_permalink( $contact_page ) : home_url( '/#lien-he' );
	$published_stories  = (int) wp_count_posts( 'cowm_story' )->publish;
	$published_chapters = (int) wp_count_posts( 'cowm_chapter' )->publish;
	$published_posts    = (int) wp_count_posts( 'post' )->publish;

	$raw_content = trim( (string) get_post_field( 'post_content', $post_id ) );
	$is_default  = false;

	if ( '' !== $raw_content ) {
		$is_default = (
			( false !== strpos( $raw_content, 'Cảm ơn vì đã sử dụng WordPress' ) && false !== strpos( $raw_content, 'bài viết đầu tiên của bạn' ) )
			|| ( false !== strpos( $raw_content, 'Thank you for using WordPress' ) && false !== strpos( $raw_content, 'first post' ) )
		);
	}

	$custom_note_html = '';

	if ( '' !== $raw_content && ! $is_default ) {
		$custom_note_html = apply_filters( 'the_content', $raw_content );
	}

	$intro_text = has_excerpt()
		? get_the_excerpt()
		: __( 'Đây là một website nhỏ dành cho những người yêu thích đam mỹ, được dựng lên như một góc lưu trữ và đọc truyện thật gọn gàng, dễ tìm và dễ ở lại.', 'comeout-with-me' );
	?>
	<main id="primary" class="site-main intro-post-page">
		<section class="intro-hero">
			<div class="site-shell intro-hero__grid">
				<div class="intro-hero__content">
					<p class="section-eyebrow"><?php esc_html_e( 'Về tụi mình // Come Out With Me Local', 'comeout-with-me' ); ?></p>
					<h1 class="intro-hero__title"><?php bloginfo( 'name' ); ?></h1>
					<p class="intro-hero__lede"><?php echo esc_html( $intro_text ); ?></p>

					<div class="intro-hero__actions">
						<a class="button button--primary" href="<?php echo esc_url( $story_archive_url ); ?>"><?php esc_html_e( 'Vào Chuyên Án', 'comeout-with-me' ); ?></a>
						<a class="button button--secondary" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Nhắn tụi mình', 'comeout-with-me' ); ?></a>
					</div>
				</div>

				<div class="intro-hero__panel">
					<p class="intro-hero__panel-label"><?php esc_html_e( 'Giới thiệu', 'comeout-with-me' ); ?></p>
					<h2 class="intro-hero__panel-title"><?php esc_html_e( 'Một góc nhỏ cho người thích đam mỹ', 'comeout-with-me' ); ?></h2>
					<p class="intro-hero__panel-text"><?php esc_html_e( 'Website này hoạt động hoàn toàn phi lợi nhuận, được làm ra để lưu lại những bộ truyện tụi mình yêu thích và chia sẻ cùng những người có chung gu đọc.', 'comeout-with-me' ); ?></p>
					<p class="intro-hero__panel-text"><?php esc_html_e( 'Nếu bạn muốn tìm truyện để đọc, lọc theo tag, hoặc chỉ đơn giản là ghé qua ngồi xem review và tâm sự một chút, thì nơi này là dành cho bạn.', 'comeout-with-me' ); ?></p>
				</div>
			</div>
		</section>

		<section class="intro-stats">
			<div class="site-shell intro-stats__grid">
				<div class="intro-stat">
					<span class="intro-stat__value"><?php echo esc_html( $published_stories ); ?></span>
					<span class="intro-stat__label"><?php esc_html_e( 'Hồ sơ truyện', 'comeout-with-me' ); ?></span>
				</div>
				<div class="intro-stat">
					<span class="intro-stat__value"><?php echo esc_html( $published_chapters ); ?></span>
					<span class="intro-stat__label"><?php esc_html_e( 'Chương đã lên', 'comeout-with-me' ); ?></span>
				</div>
				<div class="intro-stat">
					<span class="intro-stat__value"><?php echo esc_html( $published_posts ); ?></span>
					<span class="intro-stat__label"><?php esc_html_e( 'Bài review và ghi chú', 'comeout-with-me' ); ?></span>
				</div>
			</div>
		</section>

		<section class="intro-note">
			<div class="site-shell">
				<div class="intro-note__card">
					<p class="section-eyebrow"><?php esc_html_e( 'Đi tiếp từ đây', 'comeout-with-me' ); ?></p>
					<h2 class="section-title"><?php esc_html_e( 'Bạn có thể bắt đầu đọc ngay', 'comeout-with-me' ); ?></h2>
					<div class="intro-entry-grid">
						<article class="intro-entry-card">
							<p class="intro-entry-card__label"><?php esc_html_e( 'Chuyên Án', 'comeout-with-me' ); ?></p>
							<h3 class="intro-entry-card__title"><?php esc_html_e( 'Mở kho truyện', 'comeout-with-me' ); ?></h3>
							<p class="intro-entry-card__text"><?php esc_html_e( 'Vào thẳng danh sách hồ sơ truyện để chọn bộ muốn đọc.', 'comeout-with-me' ); ?></p>
							<a class="intro-entry-card__link" href="<?php echo esc_url( $story_archive_url ); ?>">
								<?php esc_html_e( 'Vào Chuyên Án', 'comeout-with-me' ); ?>
								<?php echo cowm_get_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</article>

						<article class="intro-entry-card">
							<p class="intro-entry-card__label"><?php esc_html_e( 'Phác Họa Chân Dung', 'comeout-with-me' ); ?></p>
							<h3 class="intro-entry-card__title"><?php esc_html_e( 'Lọc theo gu', 'comeout-with-me' ); ?></h3>
							<p class="intro-entry-card__text"><?php esc_html_e( 'Nếu chưa biết đọc gì, bạn có thể lọc theo tag và thể loại trước.', 'comeout-with-me' ); ?></p>
							<a class="intro-entry-card__link" href="<?php echo esc_url( $profile_board_url ); ?>">
								<?php esc_html_e( 'Mở Phác Họa', 'comeout-with-me' ); ?>
								<?php echo cowm_get_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</article>

						<article class="intro-entry-card">
							<p class="intro-entry-card__label"><?php esc_html_e( 'Trà Đá Vỉa Hè', 'comeout-with-me' ); ?></p>
							<h3 class="intro-entry-card__title"><?php esc_html_e( 'Xem review', 'comeout-with-me' ); ?></h3>
							<p class="intro-entry-card__text"><?php esc_html_e( 'Ghé quầy để đọc note, review và mấy bài tâm sự nho nhỏ.', 'comeout-with-me' ); ?></p>
							<a class="intro-entry-card__link" href="<?php echo esc_url( $sidewalk_page_url ); ?>">
								<?php esc_html_e( 'Ghé Trà Đá', 'comeout-with-me' ); ?>
								<?php echo cowm_get_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</article>

						<article class="intro-entry-card">
							<p class="intro-entry-card__label"><?php esc_html_e( 'Hộp Thư Mật', 'comeout-with-me' ); ?></p>
							<h3 class="intro-entry-card__title"><?php esc_html_e( 'Nhắn tụi mình', 'comeout-with-me' ); ?></h3>
							<p class="intro-entry-card__text"><?php esc_html_e( 'Có góp ý hay muốn đề cử truyện thì cứ để lại lời nhắn nhé.', 'comeout-with-me' ); ?></p>
							<a class="intro-entry-card__link" href="<?php echo esc_url( $contact_url ); ?>">
								<?php esc_html_e( 'Mở Hộp Thư', 'comeout-with-me' ); ?>
								<?php echo cowm_get_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</article>
					</div>
				</div>
			</div>
		</section>

		<?php if ( $custom_note_html ) : ?>
			<section class="intro-note">
				<div class="site-shell">
					<div class="intro-note__card">
						<p class="section-eyebrow"><?php esc_html_e( 'Lời nhắn riêng', 'comeout-with-me' ); ?></p>
						<h2 class="section-title"><?php esc_html_e( 'Một chút từ bài viết này', 'comeout-with-me' ); ?></h2>
						<div class="intro-note__content">
							<?php echo $custom_note_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</main>
	<?php
endwhile;

get_footer();
