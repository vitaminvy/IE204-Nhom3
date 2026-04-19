<?php
/**
 * Single chapter template.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$chapter_id      = get_the_ID();
$story_id        = cowm_get_chapter_story_id( $chapter_id );
$story           = $story_id ? get_post( $story_id ) : null;
$story_title     = $story instanceof WP_Post ? get_the_title( $story ) : '';
$chapter_label   = cowm_get_chapter_label( $chapter_id );
$chapter_title   = get_the_title();
$display_title   = $chapter_title && $chapter_title !== $chapter_label ? $chapter_label . ': ' . $chapter_title : $chapter_label;
$previous_chapter = cowm_get_adjacent_story_chapter( $chapter_id, 'prev' );
$next_chapter     = cowm_get_adjacent_story_chapter( $chapter_id, 'next' );
$story_chapters   = $story instanceof WP_Post ? cowm_get_story_chapters( $story->ID, 'ASC' ) : array();
?>
<main id="primary" class="site-main">
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'chapter-detail site-shell' ); ?>>
		<section class="chapter-shell">
			<?php if ( $story instanceof WP_Post ) : ?>
				<p class="chapter-breadcrumb">
					<a href="<?php echo esc_url( get_permalink( $story ) ); ?>"><?php echo esc_html( $story_title ); ?></a>
					<span aria-hidden="true">/</span>
					<span><?php echo esc_html( $chapter_label ); ?></span>
				</p>
			<?php endif; ?>

			<header class="chapter-header">
				<p class="section-eyebrow"><?php echo esc_html( $story_title ? $story_title : __( 'Chương truyện', 'comeout-with-me' ) ); ?></p>
				<h1 class="chapter-detail__title"><?php echo esc_html( $display_title ); ?></h1>
				<p class="chapter-detail__meta">
					<span><?php echo esc_html( get_the_date() ); ?></span>
					<span aria-hidden="true">•</span>
					<span><?php echo esc_html( cowm_get_relative_post_time( $chapter_id ) ); ?></span>
				</p>

				<div class="chapter-nav">
					<?php if ( $previous_chapter instanceof WP_Post ) : ?>
						<a class="button button--secondary" href="<?php echo esc_url( get_permalink( $previous_chapter ) ); ?>"><?php esc_html_e( 'Chương trước', 'comeout-with-me' ); ?></a>
					<?php endif; ?>

					<?php if ( $story instanceof WP_Post ) : ?>
						<a class="button button--secondary" href="<?php echo esc_url( get_permalink( $story ) ); ?>"><?php esc_html_e( 'Về truyện', 'comeout-with-me' ); ?></a>
					<?php endif; ?>

					<?php if ( $next_chapter instanceof WP_Post ) : ?>
						<a class="button button--primary" href="<?php echo esc_url( get_permalink( $next_chapter ) ); ?>"><?php esc_html_e( 'Chương sau', 'comeout-with-me' ); ?></a>
					<?php endif; ?>
				</div>
			</header>

			<div class="chapter-content">
				<?php the_content(); ?>
			</div>

			<?php if ( $story instanceof WP_Post && ! empty( $story_chapters ) ) : ?>
				<section class="chapter-related">
					<div class="section-heading section-heading--split">
						<div>
							<p class="section-eyebrow"><?php esc_html_e( 'Browse Story', 'comeout-with-me' ); ?></p>
							<h2 class="section-title"><?php esc_html_e( 'Các chương khác', 'comeout-with-me' ); ?></h2>
						</div>
					</div>

					<ol class="chapter-inline-list">
						<?php foreach ( $story_chapters as $chapter ) : ?>
							<?php
							$is_current = (int) $chapter->ID === (int) $chapter_id;
							$chapter_item_title = get_the_title( $chapter );
							?>
							<li class="chapter-inline-list__item<?php echo $is_current ? ' is-current' : ''; ?>">
								<a href="<?php echo esc_url( get_permalink( $chapter ) ); ?>">
									<span class="chapter-inline-list__label"><?php echo esc_html( cowm_get_chapter_label( $chapter->ID ) ); ?></span>
									<?php if ( $chapter_item_title ) : ?>
										<span class="chapter-inline-list__title"><?php echo esc_html( $chapter_item_title ); ?></span>
									<?php endif; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ol>
				</section>
			<?php endif; ?>
		</section>
	</article>
</main>
<?php
get_footer();
