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
?>
<main id="primary" class="site-main">
	<section class="archive-shell site-shell">
		<header class="archive-header">
			<?php if ( is_home() && ! is_front_page() ) : ?>
				<h1 class="archive-title"><?php single_post_title(); ?></h1>
			<?php else : ?>
				<h1 class="archive-title"><?php bloginfo( 'name' ); ?></h1>
			<?php endif; ?>
		</header>

		<div class="archive-list">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'archive-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="archive-card__media" href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'large', array( 'alt' => cowm_get_post_thumbnail_alt( get_the_ID(), get_the_title() ) ) ); ?>
							</a>
						<?php endif; ?>
						<div class="archive-card__content">
							<h2 class="archive-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="archive-card__meta"><?php echo esc_html( get_the_date() ); ?></p>
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

