<?php
/**
 * Story archive template.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$story_category = absint( get_theme_mod( 'cowm_stories_category', 0 ) );
?>
<main id="primary" class="site-main">
	<section class="archive-shell site-shell">
		<header class="archive-header archive-header--story">
			<p class="section-eyebrow"><?php esc_html_e( 'Story Archive', 'comeout-with-me' ); ?></p>
			<h1 class="archive-title"><?php post_type_archive_title(); ?></h1>
		</header>

		<div class="archive-list archive-list--stories">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : ?>
					<?php
					the_post();
					$story_id      = get_the_ID();
					$badges        = cowm_get_story_badges( $story_id, $story_category );
					$progress      = cowm_get_story_progress_label( $story_id );
					$latest_chapter = cowm_get_story_latest_chapter( $story_id );
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'archive-card archive-card--story' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="archive-card__media" href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'cowm-featured-story', array( 'alt' => cowm_get_post_thumbnail_alt( $story_id, get_the_title() ) ) ); ?>
							</a>
						<?php endif; ?>

						<div class="archive-card__content">
							<?php if ( ! empty( $badges ) ) : ?>
								<div class="story-badges">
									<?php foreach ( $badges as $badge ) : ?>
										<span class="story-badge"><?php echo esc_html( $badge ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<h2 class="archive-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="archive-card__meta">
								<span><?php echo esc_html( $progress ); ?></span>
								<span aria-hidden="true">•</span>
								<span><?php echo esc_html( cowm_get_relative_post_time( $story_id ) ); ?></span>
							</p>
							<div class="archive-card__excerpt"><?php the_excerpt(); ?></div>

							<div class="archive-card__actions">
								<a class="button button--secondary" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Mở hồ sơ', 'comeout-with-me' ); ?></a>
								<?php if ( $latest_chapter instanceof WP_Post ) : ?>
									<a class="button button--primary" href="<?php echo esc_url( get_permalink( $latest_chapter ) ); ?>"><?php esc_html_e( 'Đọc chap mới', 'comeout-with-me' ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					</article>
				<?php endwhile; ?>

				<div class="archive-pagination">
					<?php the_posts_pagination(); ?>
				</div>
			<?php else : ?>
				<div class="empty-state">
					<p><?php esc_html_e( 'Chưa có truyện nào trong kho. Hãy tạo truyện mới và thêm chương để hiển thị ở đây.', 'comeout-with-me' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
