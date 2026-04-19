<?php
/**
 * Homepage blog/review section.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blog_count  = max( 1, absint( get_theme_mod( 'cowm_blog_count', 2 ) ) );
$blog_query  = new WP_Query( cowm_get_blog_query_args( $blog_count ) );
$archive_url = cowm_get_blog_archive_url();
$blog_title = cowm_normalize_legacy_copy(
	get_theme_mod( 'cowm_blog_title', __( 'Trà đá vỉa hè và tâm tình', 'comeout-with-me' ) ),
	array(
		'Tra da via he va tam tinh' => 'Trà đá vỉa hè và tâm tình',
	)
);
$blog_description = cowm_normalize_legacy_copy(
	get_theme_mod( 'cowm_blog_description', __( 'Góc blog dành cho review truyện, top list tuyển chọn và những cuộc trò chuyện nhỏ xoay quanh thế giới đam mỹ mỹ cường.', 'comeout-with-me' ) ),
	array(
		'Doc, ngam va cung chia se nhung cam nhan ve the gioi my cuong qua review, toplist va bai viet mang tinh bien tap.' => 'Góc blog dành cho review truyện, top list tuyển chọn và những cuộc trò chuyện nhỏ xoay quanh thế giới đam mỹ mỹ cường.',
	)
);
$blog_button_label = cowm_normalize_legacy_copy(
	get_theme_mod( 'cowm_blog_button_label', __( 'Khám phá blog', 'comeout-with-me' ) ),
	array(
		'Kham pha blog' => 'Khám phá blog',
	)
);
?>
<section class="blog-section site-shell" id="tra-da-via-he">
	<div class="blog-grid">
		<div class="blog-intro">
			<p class="section-eyebrow"><?php echo esc_html( get_theme_mod( 'cowm_blog_eyebrow', 'Notes from the Sidewalk' ) ); ?></p>
			<h2 class="section-title"><?php echo esc_html( $blog_title ); ?></h2>
			<p class="section-description"><?php echo esc_html( $blog_description ); ?></p>
			<a class="section-link section-link--with-icon" href="<?php echo esc_url( $archive_url ); ?>">
				<?php echo esc_html( $blog_button_label ); ?>
				<?php echo cowm_get_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<div class="blog-cards">
			<?php if ( $blog_query->have_posts() ) : ?>
				<?php while ( $blog_query->have_posts() ) : ?>
					<?php $blog_query->the_post(); ?>
					<article <?php post_class( 'blog-card' ); ?>>
						<a class="blog-card__media" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'cowm-blog-card', array( 'alt' => cowm_get_post_thumbnail_alt( get_the_ID(), get_the_title() ) ) ); ?>
							<?php else : ?>
								<div class="blog-card__placeholder" aria-hidden="true"></div>
							<?php endif; ?>
						</a>

						<div class="blog-card__body">
							<p class="blog-card__meta">
								<?php
								$category_list = get_the_category();
								$category_name = ! empty( $category_list ) ? $category_list[0]->name : __( 'Bài viết', 'comeout-with-me' );
								echo esc_html( $category_name . ' • ' . get_the_date( 'd.m.Y' ) );
								?>
							</p>
							<h3 class="blog-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '...' ) ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="empty-state">
					<p><?php esc_html_e( 'Chưa có bài review nào trong category đang chọn. Hãy cập nhật category trong Customizer hoặc đăng bài mới.', 'comeout-with-me' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
