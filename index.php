<?php
/**
 * Main fallback template.
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
		<header class="archive-header">
			<?php if ( is_search() ) : ?>
				<h1 class="archive-title">
					<?php
					printf(
						esc_html__( 'Kết quả cho: %s', 'comeout-with-me' ),
						esc_html( get_search_query( false ) )
					);
					?>
				</h1>
			<?php elseif ( is_home() && ! is_front_page() ) : ?>
				<h1 class="archive-title"><?php single_post_title(); ?></h1>
			<?php else : ?>
				<h1 class="archive-title"><?php bloginfo( 'name' ); ?></h1>
			<?php endif; ?>
		</header>

		<div class="archive-list">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : ?>
					<?php
					the_post();

					$post_id             = get_the_ID();
					$is_story            = cowm_is_story_post_type( $post_id );
					$story_badges        = $is_story ? cowm_get_story_badges( $post_id, $story_category ) : array();
					$story_author        = $is_story ? cowm_get_story_author_name( $post_id ) : '';
					$story_status        = $is_story ? cowm_get_story_status_text( $post_id ) : '';
					$story_chapter_count = $is_story ? cowm_get_story_chapter_count( $post_id ) : 0;
					$story_genres        = $is_story ? cowm_get_story_genres( $post_id, 6 ) : array();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'archive-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="archive-card__media" href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'large', array( 'alt' => cowm_get_post_thumbnail_alt( get_the_ID(), get_the_title() ) ) ); ?>
							</a>
						<?php endif; ?>
						<div class="archive-card__content">
							<?php if ( $is_story && ! empty( $story_badges ) ) : ?>
								<div class="story-badges archive-card__badges">
									<?php foreach ( $story_badges as $story_badge ) : ?>
										<span class="story-badge"><?php echo esc_html( $story_badge ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<h2 class="archive-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="archive-card__meta"><?php echo esc_html( get_the_date() ); ?></p>

							<?php if ( $is_story && $story_author ) : ?>
								<p class="archive-card__author">
									<?php
									printf(
										/* translators: %s is the story author. */
										esc_html__( 'Tác giả: %s', 'comeout-with-me' ),
										esc_html( $story_author )
									);
									?>
								</p>
							<?php endif; ?>

							<?php if ( $is_story && ( $story_status || $story_chapter_count ) ) : ?>
								<div class="archive-card__details">
									<?php if ( $story_status ) : ?>
										<div class="archive-card__detail">
											<span class="archive-card__detail-label"><?php esc_html_e( 'Tình trạng', 'comeout-with-me' ); ?></span>
											<span class="archive-card__detail-value"><?php echo esc_html( $story_status ); ?></span>
										</div>
									<?php endif; ?>

									<?php if ( $story_chapter_count ) : ?>
										<div class="archive-card__detail">
											<span class="archive-card__detail-label"><?php esc_html_e( 'Số chương', 'comeout-with-me' ); ?></span>
											<span class="archive-card__detail-value">
												<?php
												echo esc_html(
													sprintf(
														/* translators: %d is the chapter count. */
														_n( '%d chương', '%d chương', $story_chapter_count, 'comeout-with-me' ),
														$story_chapter_count
													)
												);
												?>
											</span>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ( $is_story && ! empty( $story_genres ) ) : ?>
								<div class="archive-card__taxonomy">
									<span class="archive-card__taxonomy-label"><?php esc_html_e( 'Thể loại', 'comeout-with-me' ); ?></span>
									<div class="story-badges archive-card__genres">
										<?php foreach ( $story_genres as $story_genre ) : ?>
											<span class="story-badge"><?php echo esc_html( $story_genre ); ?></span>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>

							<div class="archive-card__excerpt"><?php the_excerpt(); ?></div>
						</div>
					</article>
				<?php endwhile; ?>

				<div class="archive-pagination">
					<?php the_posts_pagination(); ?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'No posts found yet.', 'comeout-with-me' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
