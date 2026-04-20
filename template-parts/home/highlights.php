<?php
/**
 * Profile board section.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card_limit = absint( get_theme_mod( 'cowm_highlights_limit', 7 ) );

if ( function_exists( 'cowm_is_profile_board_screen' ) && cowm_is_profile_board_screen() ) {
	$card_limit = 0;
}

$cards = cowm_get_profile_board_cards( $card_limit );

if ( empty( $cards ) ) {
	return;
}

$section_title = cowm_normalize_legacy_copy(
	get_theme_mod( 'cowm_highlights_title', __( 'Phác Họa Chân Dung', 'comeout-with-me' ) ),
	array(
		'Danh mục hồ sơ nổi bật' => 'Phác Họa Chân Dung',
		'Danh muc ho so noi bat' => 'Phác Họa Chân Dung',
	)
);
$section_eyebrow = cowm_normalize_legacy_copy(
	get_theme_mod( 'cowm_highlights_eyebrow', 'Behavioral Analysis' ),
	array(
		'Classification' => 'Behavioral Analysis',
	)
);
$title_words   = preg_split( '/\s+/u', trim( $section_title ) );
$title_words   = array_values( array_filter( is_array( $title_words ) ? $title_words : array() ) );
$story_counts  = wp_count_posts( 'cowm_story' );
$story_total   = $story_counts && isset( $story_counts->publish ) ? absint( $story_counts->publish ) : count( $cards );
$archive_url   = cowm_get_story_archive_url();

if ( empty( $title_words ) ) {
	$title_words = array( 'Phác', 'Họa', 'Chân', 'Dung' );
}
?>
<section class="highlights-section profile-board-section" id="phac-hoa" data-profile-board>
	<div class="site-shell profile-board-section__shell">
		<aside class="profile-board-sidebar">
			<div class="profile-board-sidebar__panel">
				<p class="profile-board-sidebar__eyebrow"><?php echo esc_html( $section_eyebrow ); ?></p>
				<h2 class="profile-board-sidebar__title"><?php esc_html_e( 'Bộ lọc hồ sơ', 'comeout-with-me' ); ?></h2>
				<p class="profile-board-sidebar__caption"><?php esc_html_e( 'Gõ từ khóa, chọn nhãn rồi mở đúng Chuyên Án bạn muốn lần theo.', 'comeout-with-me' ); ?></p>

				<label class="profile-board-sidebar__search">
					<span class="profile-board-sidebar__search-icon" aria-hidden="true">
						<?php echo cowm_get_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<input
						type="search"
						data-profile-search-input
						placeholder="<?php esc_attr_e( 'Tìm tag, truyện, tác giả...', 'comeout-with-me' ); ?>"
						aria-label="<?php esc_attr_e( 'Tìm hồ sơ trong Phác Họa chân dung', 'comeout-with-me' ); ?>"
					/>
				</label>

				<div class="profile-board-sidebar__stats">
					<div class="profile-board-sidebar__stat">
						<span><?php esc_html_e( 'Tag nổi bật', 'comeout-with-me' ); ?></span>
						<strong><?php echo esc_html( count( $cards ) ); ?></strong>
					</div>
					<div class="profile-board-sidebar__stat">
						<span><?php esc_html_e( 'Hồ sơ truyện', 'comeout-with-me' ); ?></span>
						<strong><?php echo esc_html( $story_total ); ?></strong>
					</div>
				</div>

				<div class="profile-board-sidebar__filters" role="list" aria-label="<?php esc_attr_e( 'Bộ lọc hồ sơ', 'comeout-with-me' ); ?>">
					<button class="profile-board-filter is-active" type="button" data-profile-filter-button data-profile-filter="all" aria-pressed="true" role="listitem">
						<span><?php esc_html_e( 'Tất cả hồ sơ', 'comeout-with-me' ); ?></span>
						<em><?php echo esc_html( count( $cards ) ); ?></em>
					</button>

					<?php foreach ( $cards as $card ) : ?>
						<button
							class="profile-board-filter"
							type="button"
							data-profile-filter-button
							data-profile-filter="<?php echo esc_attr( (string) $card['term_id'] ); ?>"
							aria-pressed="false"
							role="listitem"
						>
							<span><?php echo esc_html( $card['term']->name ); ?></span>
							<em><?php echo esc_html( max( 1, (int) $card['count'] ) ); ?></em>
						</button>
					<?php endforeach; ?>
				</div>

				<p class="profile-board-sidebar__result" data-profile-result-count>
					<?php
					printf(
						/* translators: %d is the number of visible cards. */
						esc_html__( '%d hồ sơ đang mở trên bàn', 'comeout-with-me' ),
						count( $cards )
					);
					?>
				</p>

				<a class="button button--primary profile-board-sidebar__cta" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Mở Chuyên Án', 'comeout-with-me' ); ?></a>
			</div>
		</aside>

		<div class="profile-board-canvas">
			<header class="profile-board-header">
				<p class="section-eyebrow"><?php esc_html_e( 'Criminal Profiling', 'comeout-with-me' ); ?></p>
				<h2 class="screen-reader-text"><?php echo esc_html( $section_title ); ?></h2>
				<div class="profile-board-header__collage" aria-hidden="true">
					<?php foreach ( $title_words as $word_index => $word ) : ?>
						<span class="profile-board-header__word profile-board-header__word--<?php echo esc_attr( (string) ( ( $word_index % 4 ) + 1 ) ); ?>"><?php echo esc_html( $word ); ?></span>
					<?php endforeach; ?>
				</div>
				<p class="profile-board-header__description"><?php esc_html_e( 'Xác định mục tiêu. Kết nối manh mối. Chọn một hồ sơ để đổ sang Chuyên Án với đúng nhãn đang cần truy dấu.', 'comeout-with-me' ); ?></p>
			</header>

			<div class="profile-board-grid">
				<?php foreach ( $cards as $card_index => $card ) : ?>
					<article
						class="profile-board-card<?php echo 0 === $card_index ? ' is-featured' : ''; ?>"
						data-profile-card
						data-profile-filter-value="<?php echo esc_attr( (string) $card['term_id'] ); ?>"
						data-profile-search="<?php echo esc_attr( $card['search_text'] ); ?>"
					>
						<a class="profile-board-card__link" href="<?php echo esc_url( $card['url'] ); ?>">
							<span class="profile-board-card__pin" aria-hidden="true"></span>

							<div class="profile-board-card__sheet">
								<div class="profile-board-card__media-wrap">
									<?php if ( ! empty( $card['story_image_url'] ) ) : ?>
										<img
											class="profile-board-card__media"
											src="<?php echo esc_url( $card['story_image_url'] ); ?>"
											alt="<?php echo esc_attr( $card['story_image_alt'] ); ?>"
											loading="lazy"
										/>
									<?php else : ?>
										<div class="profile-board-card__media profile-board-card__media--placeholder" aria-hidden="true"></div>
									<?php endif; ?>

									<span class="profile-board-card__code"><?php echo esc_html( $card['case_code'] ); ?></span>
								</div>

								<div class="profile-board-card__body">
									<p class="profile-board-card__label"><?php echo esc_html( $card['supporting_label'] ); ?></p>
									<h3 class="profile-board-card__title"><?php echo esc_html( $card['term']->name ); ?></h3>
									<p class="profile-board-card__lead"><?php echo esc_html( $card['lead_line'] ); ?></p>
									<p class="profile-board-card__excerpt"><?php echo esc_html( $card['excerpt'] ); ?></p>

									<div class="profile-board-card__facts">
										<span class="profile-board-card__fact">
											<?php
											printf(
												/* translators: %d is the number of matching stories. */
												esc_html__( '%d hồ sơ', 'comeout-with-me' ),
												max( 1, (int) $card['count'] )
											);
											?>
										</span>

										<?php foreach ( $card['facts'] as $card_fact ) : ?>
											<span class="profile-board-card__fact"><?php echo esc_html( $card_fact ); ?></span>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</a>
					</article>
				<?php endforeach; ?>
			</div>

			<div class="empty-state profile-board-empty" data-profile-empty hidden>
				<p><?php esc_html_e( 'Chưa thấy hồ sơ nào khớp với bộ lọc hiện tại. Thử đổi từ khóa hoặc mở toàn bộ Chuyên Án để dò thêm manh mối.', 'comeout-with-me' ); ?></p>
			</div>
		</div>
	</div>
</section>
