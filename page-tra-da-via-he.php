<?php
/**
 * Dedicated sidewalk editorial page.
 *
 * @package ComeOutWithMe
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_category_id = absint( get_theme_mod( 'cowm_blog_category', 0 ) );
$active_term      = function_exists( 'cowm_get_sidewalk_filter_term' ) ? cowm_get_sidewalk_filter_term() : null;
$base_query_args  = cowm_get_blog_query_args( 0 );

$filter_post_ids = get_posts(
	array_merge(
		$base_query_args,
		array(
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	)
);

$category_buckets = array();

foreach ( $filter_post_ids as $post_id ) {
	$post_categories = get_the_category( $post_id );

	foreach ( $post_categories as $post_category ) {
		if ( 'uncategorized' === $post_category->slug ) {
			continue;
		}

		if ( $base_category_id && (int) $post_category->term_id === $base_category_id ) {
			continue;
		}

		if ( ! isset( $category_buckets[ $post_category->term_id ] ) ) {
			$category_buckets[ $post_category->term_id ] = array(
				'term'  => $post_category,
				'count' => 0,
			);
		}

		$category_buckets[ $post_category->term_id ]['count']++;
	}
}

uasort(
	$category_buckets,
	static function ( $left, $right ) {
		if ( $left['count'] === $right['count'] ) {
			return strcasecmp( $left['term']->name, $right['term']->name );
		}

		return $right['count'] <=> $left['count'];
	}
);

$display_query_args = array_merge(
	$base_query_args,
	array(
		'no_found_rows' => true,
	)
);

if ( $active_term instanceof WP_Term ) {
	if ( $base_category_id && $base_category_id !== (int) $active_term->term_id ) {
		unset( $display_query_args['cat'] );
		$display_query_args['category__and'] = array( $base_category_id, (int) $active_term->term_id );
	} else {
		$display_query_args['cat'] = (int) $active_term->term_id;
	}
}

$displayed_posts = get_posts( $display_query_args );
$lead_post       = ! empty( $displayed_posts ) ? $displayed_posts[0] : null;
$spotlight_posts = array_slice( $displayed_posts, 1, 2 );
$grid_posts      = count( $displayed_posts ) > 1 ? array_slice( $displayed_posts, 1 ) : $displayed_posts;

if ( empty( $spotlight_posts ) ) {
	$spotlight_posts = array_slice( $displayed_posts, 0, 2 );
}

$selected_label = $active_term instanceof WP_Term ? $active_term->name : __( 'Tất cả hồ sơ', 'comeout-with-me' );
$hero_note      = cowm_normalize_legacy_copy(
	get_theme_mod( 'cowm_blog_description', __( 'Góc blog dành cho review truyện, top list tuyển chọn và những cuộc trò chuyện nhỏ xoay quanh thế giới đam mỹ mỹ cường.', 'comeout-with-me' ) ),
	array(
		'Doc, ngam va cung chia se nhung cam nhan ve the gioi my cuong qua review, toplist va bai viet mang tinh bien tap.' => 'Góc blog dành cho review truyện, top list tuyển chọn và những cuộc trò chuyện nhỏ xoay quanh thế giới đam mỹ mỹ cường.',
	)
);

$get_post_categories = static function ( $post_id ) use ( $base_category_id ) {
	$category_names = array();
	$post_terms     = get_the_category( $post_id );

	foreach ( $post_terms as $post_term ) {
		if ( 'uncategorized' === $post_term->slug ) {
			continue;
		}

		if ( $base_category_id && (int) $post_term->term_id === $base_category_id ) {
			continue;
		}

		$category_name = trim( (string) $post_term->name );

		if ( '' === $category_name ) {
			continue;
		}

		$category_names[] = $category_name;
	}

	return array_values( array_unique( $category_names ) );
};

$get_read_time = static function ( $post_id ) {
	$content = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ) );

	if ( '' === $content ) {
		return '';
	}

	$word_count = count( preg_split( '/\s+/u', $content ) );
	$minutes    = max( 1, (int) ceil( $word_count / 220 ) );

	return sprintf(
		/* translators: %d is the estimated reading time in minutes. */
		_n( '%d phút đọc', '%d phút đọc', $minutes, 'comeout-with-me' ),
		$minutes
	);
};

get_header();
?>
<main id="primary" class="site-main sidewalk-page">
	<section class="sidewalk-hero">
		<div class="site-shell sidewalk-hero__panel">
			<div class="sidewalk-hero__content">
				<p class="section-eyebrow"><?php esc_html_e( 'Editorial Desk // Trà đá vỉa hè', 'comeout-with-me' ); ?></p>
				<h1 class="sidewalk-hero__title">
					<?php esc_html_e( 'Trà Đá', 'comeout-with-me' ); ?>
					<span class="sidewalk-hero__title-accent"><?php esc_html_e( 'Vỉa Hè', 'comeout-with-me' ); ?></span>
				</h1>
				<p class="sidewalk-hero__description"><?php echo esc_html( $hero_note ); ?></p>

				<div class="sidewalk-hero__stats">
					<div class="sidewalk-hero__stat">
						<span class="sidewalk-hero__stat-value"><?php echo esc_html( count( $filter_post_ids ) ); ?></span>
						<span class="sidewalk-hero__stat-label"><?php esc_html_e( 'Hồ sơ biên tập', 'comeout-with-me' ); ?></span>
					</div>
					<div class="sidewalk-hero__stat">
						<span class="sidewalk-hero__stat-value"><?php echo esc_html( count( $displayed_posts ) ); ?></span>
						<span class="sidewalk-hero__stat-label"><?php esc_html_e( 'Đang trên bàn', 'comeout-with-me' ); ?></span>
					</div>
					<div class="sidewalk-hero__stat">
						<span class="sidewalk-hero__stat-value"><?php echo esc_html( count( $category_buckets ) ); ?></span>
						<span class="sidewalk-hero__stat-label"><?php esc_html_e( 'Chuyên đề', 'comeout-with-me' ); ?></span>
					</div>
				</div>

				<div class="hero-actions">
					<a class="button button--primary" href="#tra-da-feed"><?php esc_html_e( 'Mở bàn review', 'comeout-with-me' ); ?></a>
					<?php if ( $lead_post instanceof WP_Post ) : ?>
						<a class="button button--secondary" href="<?php echo esc_url( get_permalink( $lead_post ) ); ?>"><?php esc_html_e( 'Đọc hồ sơ nổi bật', 'comeout-with-me' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="sidewalk-hero__feature-wrap">
				<?php if ( $lead_post instanceof WP_Post ) : ?>
					<a class="sidewalk-hero__feature" href="<?php echo esc_url( get_permalink( $lead_post ) ); ?>">
						<div class="sidewalk-hero__feature-media">
							<?php if ( has_post_thumbnail( $lead_post ) ) : ?>
								<?php echo get_the_post_thumbnail( $lead_post, 'large', array( 'alt' => cowm_get_post_thumbnail_alt( $lead_post->ID, get_the_title( $lead_post ) ) ) ); ?>
							<?php else : ?>
								<div class="sidewalk-hero__feature-placeholder" aria-hidden="true"></div>
							<?php endif; ?>
						</div>
						<div class="sidewalk-hero__feature-content">
							<p class="sidewalk-hero__feature-label"><?php echo esc_html( $selected_label ); ?></p>
							<h2 class="sidewalk-hero__feature-title"><?php echo esc_html( get_the_title( $lead_post ) ); ?></h2>
							<p class="sidewalk-hero__feature-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $lead_post ), 24, '...' ) ); ?></p>
							<span class="sidewalk-hero__feature-link">
								<?php esc_html_e( 'Mở hồ sơ', 'comeout-with-me' ); ?>
								<?php echo cowm_get_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						</div>
					</a>
				<?php else : ?>
					<div class="empty-state sidewalk-empty">
						<p><?php esc_html_e( 'Chưa có bài review nào để đưa lên bàn biên tập. Đăng bài post mới là trang này sẽ tự cập nhật.', 'comeout-with-me' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<div class="site-shell sidewalk-layout" id="tra-da-feed">
		<aside class="sidewalk-sidebar">
			<section class="sidewalk-panel">
				<p class="sidewalk-panel__eyebrow"><?php esc_html_e( 'Lệnh triệu tập', 'comeout-with-me' ); ?></p>
				<h2 class="sidewalk-panel__title"><?php esc_html_e( 'Vào bàn ngay', 'comeout-with-me' ); ?></h2>
				<p class="sidewalk-panel__text"><?php esc_html_e( 'Những hồ sơ mới nhất đang được mở ra ngay tại quầy Trà Đá Vỉa Hè.', 'comeout-with-me' ); ?></p>

				<div class="sidewalk-summons-list">
					<?php if ( ! empty( $spotlight_posts ) ) : ?>
						<?php foreach ( $spotlight_posts as $spotlight_post ) : ?>
							<a class="sidewalk-summons" href="<?php echo esc_url( get_permalink( $spotlight_post ) ); ?>">
								<div class="sidewalk-summons__thumb">
									<?php if ( has_post_thumbnail( $spotlight_post ) ) : ?>
										<?php echo get_the_post_thumbnail( $spotlight_post, 'medium', array( 'alt' => cowm_get_post_thumbnail_alt( $spotlight_post->ID, get_the_title( $spotlight_post ) ) ) ); ?>
									<?php else : ?>
										<div class="sidewalk-summons__placeholder" aria-hidden="true"></div>
									<?php endif; ?>
								</div>
								<div class="sidewalk-summons__content">
									<p class="sidewalk-summons__meta"><?php echo esc_html( get_the_date( 'd.m.Y', $spotlight_post ) ); ?></p>
									<h3 class="sidewalk-summons__title"><?php echo esc_html( get_the_title( $spotlight_post ) ); ?></h3>
								</div>
							</a>
						<?php endforeach; ?>
					<?php else : ?>
						<p class="sidewalk-panel__text"><?php esc_html_e( 'Chưa có hồ sơ nào chờ triệu tập.', 'comeout-with-me' ); ?></p>
					<?php endif; ?>
				</div>
			</section>

			<section class="sidewalk-panel">
				<p class="sidewalk-panel__eyebrow"><?php esc_html_e( 'Thanh lọc nhanh', 'comeout-with-me' ); ?></p>
				<h2 class="sidewalk-panel__title"><?php echo esc_html( $selected_label ); ?></h2>
				<p class="sidewalk-panel__text"><?php esc_html_e( 'Đổi chuyên đề review ngay trong quầy để lọc bài theo mood đọc hiện tại.', 'comeout-with-me' ); ?></p>

				<div class="sidewalk-filter-list">
					<a class="sidewalk-filter-chip<?php echo $active_term instanceof WP_Term ? '' : ' is-active'; ?>" href="<?php echo esc_url( trailingslashit( cowm_get_sidewalk_page_url() ) . '#tra-da-feed' ); ?>">
						<?php esc_html_e( 'Tất cả', 'comeout-with-me' ); ?>
					</a>

					<?php foreach ( $category_buckets as $category_bucket ) : ?>
						<?php
						$filter_term = $category_bucket['term'];
						$filter_url  = add_query_arg( 'review_cat', $filter_term->slug, cowm_get_sidewalk_page_url() ) . '#tra-da-feed';
						$is_active   = $active_term instanceof WP_Term && (int) $active_term->term_id === (int) $filter_term->term_id;
						?>
						<a class="sidewalk-filter-chip<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $filter_url ); ?>">
							<?php echo esc_html( $filter_term->name ); ?>
							<span><?php echo esc_html( $category_bucket['count'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		</aside>

		<section class="sidewalk-main">
			<header class="sidewalk-feed__header">
				<p class="section-eyebrow"><?php esc_html_e( 'Case Files // Review archive', 'comeout-with-me' ); ?></p>
				<h2 class="section-title"><?php esc_html_e( 'Hồ sơ review mới lên bàn', 'comeout-with-me' ); ?></h2>
				<p class="section-description">
					<?php
					printf(
						/* translators: 1: current filter label, 2: number of posts. */
						esc_html__( 'Đang mở chuyên đề %1$s với %2$d hồ sơ để bạn đọc, ngẫm và chọn đúng mood.', 'comeout-with-me' ),
						$selected_label,
						count( $displayed_posts )
					);
					?>
				</p>
			</header>

			<?php if ( empty( $grid_posts ) ) : ?>
				<div class="empty-state sidewalk-empty">
					<p><?php esc_html_e( 'Bộ lọc này chưa có bài review nào. Bạn thử đổi chuyên đề khác hoặc đăng thêm bài post mới nhé.', 'comeout-with-me' ); ?></p>
				</div>
			<?php else : ?>
				<div class="sidewalk-grid">
					<?php foreach ( $grid_posts as $index => $grid_post ) : ?>
						<?php
						$post_categories = $get_post_categories( $grid_post->ID );
						$primary_label   = ! empty( $post_categories ) ? $post_categories[0] : __( 'Biên tập', 'comeout-with-me' );
						$read_time       = $get_read_time( $grid_post->ID );
						$file_label      = sprintf( 'FILE #%02d', $index + 1 );
						?>
						<article class="sidewalk-card">
							<a class="sidewalk-card__media" href="<?php echo esc_url( get_permalink( $grid_post ) ); ?>">
								<?php if ( has_post_thumbnail( $grid_post ) ) : ?>
									<?php echo get_the_post_thumbnail( $grid_post, 'large', array( 'alt' => cowm_get_post_thumbnail_alt( $grid_post->ID, get_the_title( $grid_post ) ) ) ); ?>
								<?php else : ?>
									<div class="sidewalk-card__placeholder" aria-hidden="true"></div>
								<?php endif; ?>
								<span class="sidewalk-card__tag"><?php echo esc_html( $primary_label ); ?></span>
							</a>

							<div class="sidewalk-card__content">
								<p class="sidewalk-card__meta">
									<?php echo esc_html( get_the_date( 'd.m.Y', $grid_post ) ); ?>
									<?php if ( $read_time ) : ?>
										<span>&bull;</span>
										<?php echo esc_html( $read_time ); ?>
									<?php endif; ?>
									<span>&bull;</span>
									<?php echo esc_html( cowm_get_relative_post_time( $grid_post->ID ) ); ?>
								</p>

								<h3 class="sidewalk-card__title">
									<a href="<?php echo esc_url( get_permalink( $grid_post ) ); ?>"><?php echo esc_html( get_the_title( $grid_post ) ); ?></a>
								</h3>

								<p class="sidewalk-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $grid_post ), 30, '...' ) ); ?></p>

								<?php if ( ! empty( $post_categories ) ) : ?>
									<div class="sidewalk-card__chips">
										<?php foreach ( array_slice( $post_categories, 0, 4 ) as $post_category_name ) : ?>
											<span class="sidewalk-chip"><?php echo esc_html( $post_category_name ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>

								<div class="sidewalk-card__footer">
									<span class="sidewalk-card__file"><?php echo esc_html( $file_label ); ?></span>
									<a class="sidewalk-card__cta" href="<?php echo esc_url( get_permalink( $grid_post ) ); ?>">
										<?php esc_html_e( 'Mở hồ sơ', 'comeout-with-me' ); ?>
										<?php echo cowm_get_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</a>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	</div>
</main>
<?php
get_footer();
