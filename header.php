<?php
/**
 * Theme header.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bookmark_url = cowm_get_story_archive_url();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#primary"><?php esc_html_e( 'Skip to content', 'comeout-with-me' ); ?></a>

<header class="site-header" id="masthead">
	<div class="site-header__inner site-shell">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-branding__title" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>

		<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
			<span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'comeout-with-me' ); ?></span>
			<?php echo cowm_get_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>

		<nav class="primary-nav" id="primary-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'comeout-with-me' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'fallback_cb'    => 'cowm_menu_fallback',
				)
			);
			?>
		</nav>

		<div class="site-header__actions">
			<button class="site-header__action" type="button" data-search-toggle aria-expanded="false" aria-controls="site-search-panel">
				<span class="screen-reader-text"><?php esc_html_e( 'Open search', 'comeout-with-me' ); ?></span>
				<?php echo cowm_get_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<a class="site-header__action" href="<?php echo esc_url( $bookmark_url ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Browse story archive', 'comeout-with-me' ); ?></span>
				<?php echo cowm_get_icon( 'bookmark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
	</div>

	<div class="site-search-panel" id="site-search-panel" data-search-panel hidden>
		<div class="site-shell">
			<?php get_search_form(); ?>
		</div>
	</div>
</header>
