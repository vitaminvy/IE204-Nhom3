<?php
/**
 * Theme footer.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$copyright = trim( (string) get_theme_mod( 'cowm_footer_text', '' ) );

if ( '' === $copyright ) {
	$copyright = sprintf(
		/* translators: 1: year, 2: site title. */
		__( '© %1$s %2$s. All rights reserved.', 'comeout-with-me' ),
		wp_date( 'Y' ),
		get_bloginfo( 'name' )
	);
}
?>
<footer class="site-footer" id="lien-he">
	<div class="site-footer__inner site-shell">
		<div class="site-footer__branding">
			<a class="site-branding__title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
		</div>

		<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'comeout-with-me' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'menu',
					'fallback_cb'    => 'cowm_menu_fallback',
				)
			);
			?>
		</nav>

		<p class="site-footer__copy"><?php echo esc_html( $copyright ); ?></p>
	</div>
</footer>
<button class="back-to-top" type="button" data-back-to-top aria-hidden="true" tabindex="-1">
	<span class="screen-reader-text"><?php esc_html_e( 'Lên đầu trang', 'comeout-with-me' ); ?></span>
	<?php echo cowm_get_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<span class="back-to-top__label"><?php esc_html_e( 'Lên đầu', 'comeout-with-me' ); ?></span>
</button>
<?php wp_footer(); ?>
</body>
</html>
