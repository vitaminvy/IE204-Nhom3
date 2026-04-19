<?php
/**
 * Homepage story feed.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$story_count      = max( 2, absint( get_theme_mod( 'cowm_stories_count', 6 ) ) );
$story_category   = absint( get_theme_mod( 'cowm_stories_category', 0 ) );
$story_query      = new WP_Query( cowm_get_story_query_args( $story_count ) );
$story_posts      = $story_query->posts;
$featured_posts   = array_slice( $story_posts, 0, min( 2, count( $story_posts ) ) );
$compact_posts    = array_slice( $story_posts, 2, 4 );
$section_link     = cowm_get_story_archive_url();
$story_copy_map   = array(
	'Truyen moi cap nhat' => 'Truyện mới cập nhật',
	'Xem dong thoi gian'  => 'Xem dòng thời gian',
);
$section_link_text = cowm_normalize_legacy_copy(
	trim( (string) get_theme_mod( 'cowm_stories_link_label', __( 'Xem dòng thời gian', 'comeout-with-me' ) ) ),
	$story_copy_map
);
$section_title = cowm_normalize_legacy_copy(
	trim( (string) get_theme_mod( 'cowm_stories_title', __( 'Truyện mới cập nhật', 'comeout-with-me' ) ) ),
	$story_copy_map
);
?>
<section class="stories-section site-shell" id="chuyen-an">
	<div class="section-heading section-heading--split">
		<div>
			<p class="section-eyebrow"><?php echo esc_html( get_theme_mod( 'cowm_stories_eyebrow', 'Latest Files' ) ); ?></p>
			<h2 class="section-title"><?php echo esc_html( $section_title ); ?></h2>
		</div>
		<a class="section-link" href="<?php echo esc_url( $section_link ); ?>"><?php echo esc_html( $section_link_text ); ?></a>
	</div>

	<?php if ( ! empty( $featured_posts ) ) : ?>
		<div class="stories-featured-grid">
			<?php foreach ( $featured_posts as $post ) : ?>
				<?php
				setup_postdata( $post );
				$badges   = cowm_get_story_badges( $post->ID, $story_category );
				$progress = cowm_get_story_progress_label( $post->ID );
				?>
				<article <?php post_class( 'story-feature-card', $post->ID ); ?>>
					<a class="story-feature-card__media" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail( $post ) ) : ?>
							<?php echo get_the_post_thumbnail( $post->ID, 'cowm-featured-story', array( 'alt' => cowm_get_post_thumbnail_alt( $post->ID, get_the_title( $post ) ) ) ); ?>
						<?php else : ?>
							<div class="story-feature-card__placeholder" aria-hidden="true"></div>
						<?php endif; ?>
					</a>
					<div class="story-feature-card__body">
						<?php if ( ! empty( $badges ) ) : ?>
							<div class="story-badges">
								<?php foreach ( $badges as $badge ) : ?>
									<span class="story-badge"><?php echo esc_html( $badge ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<h3 class="story-feature-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="story-feature-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 28, '...' ) ); ?></p>
						<p class="story-feature-card__meta">
							<span><?php echo esc_html( $progress ); ?></span>
							<span aria-hidden="true">•</span>
							<span><?php echo esc_html( cowm_get_relative_post_time( $post->ID ) ); ?></span>
						</p>
					</div>
				</article>
			<?php endforeach; ?>
			<?php wp_reset_postdata(); ?>
		</div>

		<?php if ( ! empty( $compact_posts ) ) : ?>
			<div class="stories-compact-grid">
				<?php foreach ( $compact_posts as $post ) : ?>
					<?php setup_postdata( $post ); ?>
					<article <?php post_class( 'story-compact-card', $post->ID ); ?>>
						<a class="story-compact-card__media" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail( $post ) ) : ?>
								<?php echo get_the_post_thumbnail( $post->ID, 'cowm-story-thumb', array( 'alt' => cowm_get_post_thumbnail_alt( $post->ID, get_the_title( $post ) ) ) ); ?>
							<?php else : ?>
								<div class="story-compact-card__placeholder" aria-hidden="true"></div>
							<?php endif; ?>
						</a>
						<div class="story-compact-card__body">
							<h3 class="story-compact-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="story-compact-card__meta"><?php echo esc_html( cowm_get_story_progress_label( $post->ID ) . ' • ' . cowm_get_relative_post_time( $post->ID ) ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
				<?php wp_reset_postdata(); ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="empty-state">
			<p><?php esc_html_e( 'Chua co bai viet nao trong nguon du lieu hien tai. Hay tao bai viet moi hoac doi category trong Customizer.', 'comeout-with-me' ); ?></p>
		</div>
	<?php endif; ?>
</section>
<?php wp_reset_postdata(); ?>
