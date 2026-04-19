<?php
/**
 * Front page template.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$front_page_content = '';
$front_page_id      = (int) get_option( 'page_on_front' );

if ( $front_page_id ) {
	$front_page = get_post( $front_page_id );

	if ( $front_page instanceof WP_Post && trim( (string) $front_page->post_content ) ) {
		$front_page_content = apply_filters( 'the_content', $front_page->post_content );
	}
}
?>
<main id="primary" class="site-main">
	<?php get_template_part( 'template-parts/home/hero' ); ?>
	<?php if ( $front_page_content ) : ?>
		<section class="front-page-content site-shell">
			<div class="front-page-content__inner">
				<?php echo $front_page_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</section>
	<?php endif; ?>
	<?php get_template_part( 'template-parts/home/pillars' ); ?>
	<?php get_template_part( 'template-parts/home/stories' ); ?>
	<?php get_template_part( 'template-parts/home/highlights' ); ?>
	<?php get_template_part( 'template-parts/home/blog' ); ?>
</main>
<?php
get_footer();

