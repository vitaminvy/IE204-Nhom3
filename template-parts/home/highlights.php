<?php
/**
 * Homepage highlight chips.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$terms = cowm_get_highlight_terms( absint( get_theme_mod( 'cowm_highlights_limit', 7 ) ) );
$highlights_title = cowm_normalize_legacy_copy(
	get_theme_mod( 'cowm_highlights_title', __( 'Danh mục hồ sơ nổi bật', 'comeout-with-me' ) ),
	array(
		'Danh muc ho so noi bat' => 'Danh mục hồ sơ nổi bật',
	)
);

if ( empty( $terms ) ) {
	return;
}
?>
<section class="highlights-section">
	<div class="site-shell">
		<div class="section-heading section-heading--centered">
			<p class="section-eyebrow"><?php echo esc_html( get_theme_mod( 'cowm_highlights_eyebrow', 'Classification' ) ); ?></p>
			<h2 class="section-title"><?php echo esc_html( $highlights_title ); ?></h2>
		</div>

		<div class="highlight-chips" role="list">
			<?php foreach ( $terms as $term ) : ?>
				<a class="highlight-chip" href="<?php echo esc_url( cowm_get_story_tag_archive_url( $term ) ); ?>" role="listitem"><?php echo esc_html( $term->name ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
