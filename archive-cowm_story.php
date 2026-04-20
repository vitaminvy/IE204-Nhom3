<?php
/**
 * Story archive template.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;

$story_category = absint( get_query_var( 'story_category' ) );
$story_tag      = absint( get_query_var( 'story_tag' ) );
$archive_url    = cowm_get_story_archive_url();
$current_term   = $story_tag ? get_term( $story_tag, 'post_tag' ) : null;
$page_stories   = $wp_query->posts;
$hero_story     = ! empty( $page_stories ) ? $page_stories[0] : null;
$latest_stories = array_slice( $page_stories, 0, 5 );
$curated_stories = array_slice( $page_stories, 5, 5 );

if ( empty( $curated_stories ) && ! empty( $latest_stories ) ) {
	$curated_stories = array_slice( $latest_stories, 0, min( 5, count( $latest_stories ) ) );
}

$hero_chips = array();

if ( $hero_story instanceof WP_Post ) {
	$hero_chips = cowm_get_story_badges( $hero_story->ID, $story_category );
	$hero_tags  = get_the_tags( $hero_story->ID );

	if ( $hero_tags && ! is_wp_error( $hero_tags ) ) {
		foreach ( $hero_tags as $hero_tag ) {
			$hero_chips[] = $hero_tag->name;
		}
	}

	$hero_chips = array_slice( array_values( array_unique( array_filter( $hero_chips ) ) ), 0, 3 );
}

$hero_title_line = $current_term instanceof WP_Term ? $current_term->name : __( 'Chuyên Án', 'comeout-with-me' );
$hero_description = $current_term instanceof WP_Term
	? sprintf(
		/* translators: %s is the selected tag name. */
		__( 'Tập hợp những hồ sơ xoay quanh nhãn %s, được sắp theo lần cập nhật mới nhất để bạn lần theo từng manh mối nhanh hơn.', 'comeout-with-me' ),
		$current_term->name
	)
	: __( 'Kho lưu trữ những hồ sơ đam mỹ mỹ cường được tuyển chọn, phân loại bằng tag và sắp theo nhịp cập nhật chương mới để bạn tra cứu gọn hơn.', 'comeout-with-me' );

$hero_image_url = $hero_story instanceof WP_Post && has_post_thumbnail( $hero_story )
	? get_the_post_thumbnail_url( $hero_story, 'cowm-featured-story' )
	: cowm_get_theme_asset_image_url(
		array(
			'assets/images/hero-local.webp',
			'assets/images/hero-local.png',
			'assets/images/hero-local.jpg',
			'assets/images/hero-local.jpeg',
		)
	);

$hero_author = $hero_story instanceof WP_Post ? cowm_get_story_author_name( $hero_story->ID ) : '';
$filter_story_args = array(
	'post_type'              => 'cowm_story',
	'post_status'            => 'publish',
	'posts_per_page'         => -1,
	'fields'                 => 'ids',
	'no_found_rows'          => true,
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
	'ignore_sticky_posts'    => true,
);

if ( $story_category || $story_tag ) {
	$tax_query = array();

	if ( $story_category ) {
		$tax_query[] = array(
			'taxonomy' => 'category',
			'field'    => 'term_id',
			'terms'    => $story_category,
		);
	}

	if ( $story_tag ) {
		$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'field'    => 'term_id',
			'terms'    => $story_tag,
		);
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	$filter_story_args['tax_query'] = $tax_query;
}

$filter_story_ids = get_posts( $filter_story_args );
$archive_terms    = array();

if ( ! empty( $filter_story_ids ) ) {
	$term_rows = wp_get_object_terms(
		$filter_story_ids,
		'post_tag',
		array(
			'fields' => 'all_with_object_id',
		)
	);

	if ( ! is_wp_error( $term_rows ) && ! empty( $term_rows ) ) {
		$grouped_terms = array();

		foreach ( $term_rows as $term_row ) {
			$term_id = (int) $term_row->term_id;

			if ( ! isset( $grouped_terms[ $term_id ] ) ) {
				$grouped_terms[ $term_id ] = array(
					'term'       => $term_row,
					'object_ids' => array(),
				);
			}

			$grouped_terms[ $term_id ]['object_ids'][ (int) $term_row->object_id ] = true;
		}

		uasort(
			$grouped_terms,
			static function ( $left, $right ) {
				$left_count  = count( $left['object_ids'] );
				$right_count = count( $right['object_ids'] );

				if ( $left_count !== $right_count ) {
					return $right_count <=> $left_count;
				}

				return strnatcasecmp( $left['term']->name, $right['term']->name );
			}
		);

		foreach ( array_slice( $grouped_terms, 0, 8, true ) as $grouped_term ) {
			$archive_terms[] = $grouped_term['term'];
		}
	}
}

$archive_sections = array(
	array(
		'eyebrow' => __( 'Recents // Archive', 'comeout-with-me' ),
		'title'   => __( 'Hồ sơ mới tiếp nhận', 'comeout-with-me' ),
		'link'    => __( 'Xem tất cả', 'comeout-with-me' ),
		'id'      => 'ho-so-moi',
		'stories' => $latest_stories,
	),
	array(
		'eyebrow' => __( 'Curated // Special', 'comeout-with-me' ),
		'title'   => __( 'Chuyên án tiêu biểu', 'comeout-with-me' ),
		'link'    => __( 'Duyệt thêm', 'comeout-with-me' ),
		'id'      => 'ho-so-tieu-bieu',
		'stories' => $curated_stories,
	),
);
?>
<main id="primary" class="site-main">
	<section class="story-archive-hero">
		<div class="site-shell story-archive-hero__grid">
			<div class="story-archive-hero__copy">
				<p class="section-eyebrow"><?php esc_html_e( 'Classification // Archive', 'comeout-with-me' ); ?></p>

				<?php if ( ! empty( $hero_chips ) ) : ?>
					<div class="story-archive-hero__chips" role="list" aria-label="<?php esc_attr_e( 'Hero tags', 'comeout-with-me' ); ?>">
						<?php foreach ( $hero_chips as $hero_chip ) : ?>
							<span class="story-archive-hero__chip" role="listitem"><?php echo esc_html( $hero_chip ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<h1 class="story-archive-hero__title">
					<span><?php esc_html_e( 'Hồ sơ', 'comeout-with-me' ); ?></span>
					<strong><?php echo esc_html( $hero_title_line ); ?></strong>
				</h1>

				<p class="story-archive-hero__description"><?php echo esc_html( $hero_description ); ?></p>

				<div class="story-archive-hero__actions">
					<a class="button button--primary" href="#ho-so-moi"><?php esc_html_e( 'Khám phá ngay', 'comeout-with-me' ); ?></a>
					<p class="story-archive-hero__meta">
						<?php
						printf(
							/* translators: %d is the number of stories. */
							esc_html__( '%d hồ sơ đang hoạt động', 'comeout-with-me' ),
							absint( $wp_query->found_posts )
						);
						?>
					</p>
				</div>
			</div>

			<div class="story-archive-hero__media">
				<div class="story-archive-hero__glow" aria-hidden="true"></div>
				<div class="story-archive-hero__frame">
					<?php if ( $hero_image_url ) : ?>
						<img
							class="story-archive-hero__image"
							src="<?php echo esc_url( $hero_image_url ); ?>"
							alt="<?php echo esc_attr( $hero_story instanceof WP_Post ? cowm_get_post_thumbnail_alt( $hero_story->ID, get_the_title( $hero_story ) ) : __( 'Chuyên Án hero image', 'comeout-with-me' ) ); ?>"
						/>
					<?php else : ?>
						<div class="story-archive-hero__image story-archive-hero__image--placeholder" aria-hidden="true"></div>
					<?php endif; ?>

					<?php if ( $hero_story instanceof WP_Post ) : ?>
						<div class="story-archive-hero__dossier">
							<p class="story-archive-hero__dossier-label"><?php esc_html_e( 'Hồ sơ tiêu điểm', 'comeout-with-me' ); ?></p>
							<h2 class="story-archive-hero__dossier-title">
								<a href="<?php echo esc_url( get_permalink( $hero_story ) ); ?>"><?php echo esc_html( get_the_title( $hero_story ) ); ?></a>
							</h2>
							<p class="story-archive-hero__dossier-meta">
								<?php if ( $hero_author ) : ?>
									<span><?php echo esc_html( $hero_author ); ?></span>
									<span aria-hidden="true">•</span>
								<?php endif; ?>
								<span><?php echo esc_html( cowm_get_story_progress_label( $hero_story->ID ) ); ?></span>
							</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="story-archive-filterbar">
		<div class="site-shell">
			<div class="story-archive-filterbar__inner" role="list" aria-label="<?php esc_attr_e( 'Story filters', 'comeout-with-me' ); ?>">
				<a class="story-archive-filter<?php echo 0 === $story_tag ? ' is-active' : ''; ?>" href="<?php echo esc_url( $archive_url ); ?>" role="listitem"><?php esc_html_e( 'Tất cả', 'comeout-with-me' ); ?></a>
				<?php foreach ( $archive_terms as $archive_term ) : ?>
					<a
						class="story-archive-filter<?php echo (int) $archive_term->term_id === $story_tag ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( add_query_arg( 'story_tag', (int) $archive_term->term_id, $archive_url ) ); ?>"
						role="listitem"
					>
						<?php echo esc_html( $archive_term->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( have_posts() ) : ?>
		<?php foreach ( $archive_sections as $archive_section ) : ?>
			<?php if ( empty( $archive_section['stories'] ) ) : ?>
				<?php continue; ?>
			<?php endif; ?>

			<section class="story-archive-section site-shell" id="<?php echo esc_attr( $archive_section['id'] ); ?>">
				<header class="story-archive-section__header">
					<div>
						<p class="section-eyebrow"><?php echo esc_html( $archive_section['eyebrow'] ); ?></p>
						<h2 class="story-archive-section__title"><?php echo esc_html( $archive_section['title'] ); ?></h2>
					</div>
					<a class="section-link" href="<?php echo esc_url( $archive_url ); ?>"><?php echo esc_html( $archive_section['link'] ); ?></a>
				</header>

				<div class="story-archive-grid">
					<?php foreach ( $archive_section['stories'] as $story_post ) : ?>
						<?php
						$story_id      = $story_post->ID;
						$story_badges  = cowm_get_story_badges( $story_id, $story_category );
						$story_author  = cowm_get_story_author_name( $story_id );
						$story_tags    = get_the_tags( $story_id );
						$story_filters = array_values( array_unique( array_filter( $story_badges ) ) );

						if ( count( $story_filters ) < 2 && $story_tags && ! is_wp_error( $story_tags ) ) {
							foreach ( $story_tags as $story_tag_term ) {
								$story_filters[] = $story_tag_term->name;
							}
						}

						$story_filters = array_slice( array_values( array_unique( array_filter( $story_filters ) ) ), 0, 2 );
						?>
						<article id="post-<?php echo esc_attr( $story_id ); ?>" <?php post_class( 'story-archive-card', $story_id ); ?>>
							<a class="story-archive-card__media" href="<?php echo esc_url( get_permalink( $story_id ) ); ?>">
								<?php if ( has_post_thumbnail( $story_id ) ) : ?>
									<?php echo get_the_post_thumbnail( $story_id, 'cowm-featured-story', array( 'alt' => cowm_get_post_thumbnail_alt( $story_id, get_the_title( $story_id ) ) ) ); ?>
								<?php else : ?>
									<div class="story-archive-card__placeholder" aria-hidden="true"></div>
								<?php endif; ?>
							</a>

							<div class="story-archive-card__body">
								<?php if ( ! empty( $story_filters ) ) : ?>
									<div class="story-badges">
										<?php foreach ( $story_filters as $story_filter ) : ?>
											<span class="story-badge"><?php echo esc_html( $story_filter ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>

								<h3 class="story-archive-card__title">
									<a href="<?php echo esc_url( get_permalink( $story_id ) ); ?>"><?php echo esc_html( get_the_title( $story_id ) ); ?></a>
								</h3>

								<?php if ( $story_author ) : ?>
									<p class="story-archive-card__author">
										<?php
										printf(
											/* translators: %s is the story author. */
											esc_html__( 'Tác giả: %s', 'comeout-with-me' ),
											$story_author
										);
										?>
									</p>
								<?php endif; ?>

								<p class="story-archive-card__meta">
									<span><?php echo esc_html( cowm_get_story_progress_label( $story_id ) ); ?></span>
									<span aria-hidden="true">•</span>
									<span><?php echo esc_html( cowm_get_relative_post_time( $story_id ) ); ?></span>
								</p>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>

		<div class="archive-pagination site-shell">
			<?php the_posts_pagination(); ?>
		</div>
	<?php else : ?>
		<section class="archive-shell site-shell">
			<div class="empty-state">
				<p><?php esc_html_e( 'Chưa có hồ sơ nào trong kho. Hãy import thêm truyện để trang Chuyên Án tự đổ dữ liệu.', 'comeout-with-me' ); ?></p>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
