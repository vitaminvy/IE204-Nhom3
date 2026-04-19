<?php
/**
 * Search form template.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="cowm-search-field"><?php esc_html_e( 'Search for:', 'comeout-with-me' ); ?></label>
	<input id="cowm-search-field" class="search-field" type="search" name="s" placeholder="<?php esc_attr_e( 'Tim truyen, review, tag...', 'comeout-with-me' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>"/>
	<button class="search-submit" type="submit"><?php esc_html_e( 'Tim', 'comeout-with-me' ); ?></button>
</form>

