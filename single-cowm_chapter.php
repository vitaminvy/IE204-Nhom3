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
$chapter_count    = count( $story_chapters );
$current_position = 0;

if ( ! empty( $story_chapters ) ) {
	foreach ( $story_chapters as $chapter_index => $story_chapter ) {
		if ( (int) $story_chapter->ID === (int) $chapter_id ) {
			$current_position = $chapter_index + 1;
			break;
		}
	}
}
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

					<?php if ( $story instanceof WP_Post && ! empty( $story_chapters ) ) : ?>
						<a class="button button--secondary" href="#chapter-browser"><?php esc_html_e( 'Mục lục chương', 'comeout-with-me' ); ?></a>
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
				<section class="chapter-related" id="chapter-browser">
					<div class="section-heading section-heading--split">
						<div>
							<p class="section-eyebrow"><?php esc_html_e( 'Browse Story', 'comeout-with-me' ); ?></p>
							<h2 class="section-title"><?php esc_html_e( 'Các chương khác', 'comeout-with-me' ); ?></h2>
						</div>
					</div>

					<div class="chapter-browser-nav" aria-label="<?php esc_attr_e( 'Điều hướng chương', 'comeout-with-me' ); ?>">
						<?php if ( $previous_chapter instanceof WP_Post ) : ?>
							<a class="chapter-browser-nav__card" href="<?php echo esc_url( get_permalink( $previous_chapter ) ); ?>">
								<span class="chapter-browser-nav__eyebrow"><?php esc_html_e( 'Chương trước', 'comeout-with-me' ); ?></span>
								<span class="chapter-browser-nav__title"><?php echo esc_html( cowm_get_chapter_label( $previous_chapter->ID ) ); ?></span>
								<?php if ( get_the_title( $previous_chapter ) ) : ?>
									<span class="chapter-browser-nav__subtitle"><?php echo esc_html( get_the_title( $previous_chapter ) ); ?></span>
								<?php endif; ?>
							</a>
						<?php endif; ?>

						<div class="chapter-browser-nav__card chapter-browser-nav__card--current">
							<span class="chapter-browser-nav__eyebrow"><?php esc_html_e( 'Đang đọc', 'comeout-with-me' ); ?></span>
							<span class="chapter-browser-nav__title"><?php echo esc_html( $chapter_label ); ?></span>
							<?php if ( $chapter_title && $chapter_title !== $chapter_label ) : ?>
								<span class="chapter-browser-nav__subtitle"><?php echo esc_html( $chapter_title ); ?></span>
							<?php endif; ?>
							<?php if ( $chapter_count > 0 && $current_position > 0 ) : ?>
								<span class="chapter-browser-nav__meta">
									<?php
									echo esc_html(
										sprintf(
											/* translators: 1: current chapter position, 2: chapter count. */
											__( '%1$d / %2$d chương', 'comeout-with-me' ),
											$current_position,
											$chapter_count
										)
									);
									?>
								</span>
							<?php endif; ?>
						</div>

						<?php if ( $next_chapter instanceof WP_Post ) : ?>
							<a class="chapter-browser-nav__card" href="<?php echo esc_url( get_permalink( $next_chapter ) ); ?>">
								<span class="chapter-browser-nav__eyebrow"><?php esc_html_e( 'Chương sau', 'comeout-with-me' ); ?></span>
								<span class="chapter-browser-nav__title"><?php echo esc_html( cowm_get_chapter_label( $next_chapter->ID ) ); ?></span>
								<?php if ( get_the_title( $next_chapter ) ) : ?>
									<span class="chapter-browser-nav__subtitle"><?php echo esc_html( get_the_title( $next_chapter ) ); ?></span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
					</div>

					<ol class="chapter-browser-toc">
						<?php foreach ( $story_chapters as $chapter ) : ?>
							<?php
							$is_current          = (int) $chapter->ID === (int) $chapter_id;
							$chapter_item_title  = get_the_title( $chapter );
							$has_item_title      = '' !== trim( (string) $chapter_item_title );
							?>
							<li class="chapter-browser-toc__item<?php echo $is_current ? ' is-current' : ''; ?><?php echo $has_item_title ? '' : ' is-titleless'; ?>">
								<a class="chapter-browser-toc__link" href="<?php echo esc_url( get_permalink( $chapter ) ); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
									<span class="chapter-browser-toc__label"><?php echo esc_html( cowm_get_chapter_label( $chapter->ID ) ); ?></span>
									<?php if ( $has_item_title ) : ?>
										<span class="chapter-browser-toc__title"><?php echo esc_html( $chapter_item_title ); ?></span>
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
