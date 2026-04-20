<?php
/**
 * Single story template.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$story_id        = get_the_ID();
$story_category  = absint( get_theme_mod( 'cowm_stories_category', 0 ) );
$badges          = cowm_get_story_badges( $story_id, $story_category );
$progress_label  = cowm_get_story_progress_label( $story_id );
$relative_time   = cowm_get_relative_post_time( $story_id );
$chapter_count   = cowm_get_story_chapter_count( $story_id );
$story_genres    = cowm_get_story_genres( $story_id, 0 );
$first_chapter   = cowm_get_story_first_chapter( $story_id );
$latest_chapter  = cowm_get_story_latest_chapter( $story_id );
$chapters        = cowm_get_story_chapters( $story_id, 'ASC' );
$story_excerpt   = has_excerpt() ? get_the_excerpt() : '';
$has_story_cover = has_post_thumbnail();
?>
<main id="primary" class="site-main">
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'story-detail site-shell' ); ?>>
		<section class="story-shell">
			<div class="story-hero<?php echo $has_story_cover ? '' : ' story-hero--no-media'; ?>">
				<?php if ( $has_story_cover ) : ?>
					<div class="story-hero__media">
						<?php the_post_thumbnail( 'cowm-featured-story', array( 'alt' => cowm_get_post_thumbnail_alt( $story_id, get_the_title() ) ) ); ?>
					</div>
				<?php endif; ?>

				<div class="story-hero__content">
					<p class="section-eyebrow"><?php esc_html_e( 'Hồ sơ truyện', 'comeout-with-me' ); ?></p>

					<?php if ( ! empty( $badges ) ) : ?>
						<div class="story-badges">
							<?php foreach ( $badges as $badge ) : ?>
								<span class="story-badge"><?php echo esc_html( $badge ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $story_genres ) ) : ?>
						<div class="story-badges story-badges--genres">
							<?php foreach ( $story_genres as $story_genre ) : ?>
								<span class="story-badge"><?php echo esc_html( $story_genre ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h1 class="story-detail__title"><?php the_title(); ?></h1>

					<?php if ( $story_excerpt ) : ?>
						<p class="story-detail__excerpt"><?php echo esc_html( $story_excerpt ); ?></p>
					<?php endif; ?>

					<p class="story-detail__meta">
						<span><?php echo esc_html( $progress_label ); ?></span>
						<span aria-hidden="true">•</span>
						<span>
							<?php
							echo esc_html(
								sprintf(
									/* translators: %d is the chapter count. */
									_n( '%d chương', '%d chương', $chapter_count, 'comeout-with-me' ),
									$chapter_count
								)
							);
							?>
						</span>
						<span aria-hidden="true">•</span>
						<span><?php echo esc_html( $relative_time ); ?></span>
					</p>

					<div class="story-detail__actions">
						<?php if ( $first_chapter instanceof WP_Post ) : ?>
							<a class="button button--primary" href="<?php echo esc_url( get_permalink( $first_chapter ) ); ?>"><?php esc_html_e( 'Đọc từ đầu', 'comeout-with-me' ); ?></a>
						<?php endif; ?>

						<?php if ( $latest_chapter instanceof WP_Post ) : ?>
							<a class="button button--secondary" href="<?php echo esc_url( get_permalink( $latest_chapter ) ); ?>"><?php esc_html_e( 'Chương mới nhất', 'comeout-with-me' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if ( trim( (string) get_the_content() ) ) : ?>
				<section class="story-summary">
					<div class="section-heading section-heading--split">
						<div>
							<p class="section-eyebrow"><?php esc_html_e( 'Giới thiệu', 'comeout-with-me' ); ?></p>
							<h2 class="section-title"><?php esc_html_e( 'Tóm tắt nội dung', 'comeout-with-me' ); ?></h2>
						</div>
					</div>
					<div class="story-summary__content">
						<?php the_content(); ?>
					</div>
				</section>
			<?php endif; ?>

			<section class="chapter-list-section">
				<div class="section-heading section-heading--split">
					<div>
						<p class="section-eyebrow"><?php esc_html_e( 'Reading Order', 'comeout-with-me' ); ?></p>
						<h2 class="section-title"><?php esc_html_e( 'Danh sách chương', 'comeout-with-me' ); ?></h2>
					</div>
				</div>

				<?php if ( ! empty( $chapters ) ) : ?>
					<ol class="chapter-list">
						<?php foreach ( $chapters as $chapter ) : ?>
							<?php
							$chapter_label = cowm_get_chapter_label( $chapter->ID );
							$chapter_title = get_the_title( $chapter );
							?>
							<li class="chapter-list__item">
								<a class="chapter-list__link" href="<?php echo esc_url( get_permalink( $chapter ) ); ?>">
									<span class="chapter-list__label"><?php echo esc_html( $chapter_label ); ?></span>
									<?php if ( $chapter_title ) : ?>
										<span class="chapter-list__heading"><?php echo esc_html( $chapter_title ); ?></span>
									<?php endif; ?>
									<span class="chapter-list__meta"><?php echo esc_html( cowm_get_relative_post_time( $chapter->ID ) ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php else : ?>
					<div class="empty-state">
						<p><?php esc_html_e( 'Truyện này chưa có chương nào. Hãy thêm chương mới trong admin để bắt đầu.', 'comeout-with-me' ); ?></p>
					</div>
				<?php endif; ?>
			</section>
		</section>
	</article>
</main>
<?php
get_footer();
