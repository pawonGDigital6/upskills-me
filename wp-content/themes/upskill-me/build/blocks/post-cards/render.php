<?php
/**
 * Resource Cards block.
 *
 * Figma: Blogs 49325:16914 (listing), Blog Detail 49325:16710 (grid),
 * Resources 49325:16442 / 16528 (split) and 16462 (guides), Case Studies
 * 49325:18149 (featured) and 18182 (grid), Why UpSkill Me 49325:15680 (grid).
 *
 * One block for every list of blogs, case studies or guides. The cards are
 * template-parts/cards/resource.php and guide.php; the block chooses which
 * posts and how they are laid out:
 * - grid:     section header over three-up cards.
 * - split:    header in a left column, three narrow cards beside it.
 * - guides:   header left, one featured guide and a stack of rows.
 * - featured: header over a single wide card.
 * - listing:  header, the featured post (first page only), a paginated grid.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$post_type = get_field( 'post_type' );
$post_type = in_array( $post_type, array( 'post', 'case_study', 'guide' ), true ) ? $post_type : 'post';
$layout    = get_field( 'layout' ) ? get_field( 'layout' ) : 'grid';
$count     = max( 1, (int) get_field( 'count' ) );
$surface   = get_field( 'surface' ) ? get_field( 'surface' ) : 'white';
$selected  = 'selected' === get_field( 'selection' ) ? array_map( 'absint', (array) get_field( 'posts' ) ) : array();
$exclude   = is_singular() ? array( get_the_ID() ) : array();

$surfaces = array(
	'white' => 'bg-white',
	'tint'  => 'bg-brand-50',
	'pale'  => 'bg-brand-25',
	'gray'  => 'bg-gray-25',
);

$query_args = array(
	'post_type'           => $post_type,
	'post_status'         => 'publish',
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);

$featured = null;

if ( 'listing' === $layout ) {
	// The featured post leads page one; the paginated grid never repeats it.
	$featured = get_field( 'featured_post' ) ? get_post( (int) get_field( 'featured_post' ) ) : null;

	if ( ! $featured ) {
		$latest   = get_posts(
			array(
				'post_type'   => $post_type,
				'numberposts' => 1,
				'exclude'     => $exclude,
			)
		);
		$featured = $latest ? $latest[0] : null;
	}

	$query_args['posts_per_page'] = $count;
	$query_args['paged']          = max( 1, (int) get_query_var( 'paged' ) );
	$query_args['no_found_rows']  = false;
	$query_args['post__not_in']   = array_merge( $exclude, $featured ? array( $featured->ID ) : array() );
} elseif ( $selected ) {
	$query_args['post__in']       = $selected;
	$query_args['orderby']        = 'post__in';
	$query_args['posts_per_page'] = count( $selected );
} else {
	$query_args['posts_per_page'] = 'featured' === $layout ? 1 : $count;
	$query_args['post__not_in']   = $exclude;
}

$query = new WP_Query( $query_args );
$posts = $query->posts;

if ( 'featured' === $layout ) {
	$featured = $posts ? $posts[0] : null;
	$posts    = array();
}

// Case studies use their own card; blogs (and anything else) the blog card.
$variant = 'case_study' === $post_type ? 'case' : 'card';

$links = array_filter( array( get_field( 'featured_link_one' ), get_field( 'featured_link_two' ) ) );

$attributes = upskill_block_attributes( $block, 'block-post-cards section-y ' . ( isset( $surfaces[ $surface ] ) ? $surfaces[ $surface ] : $surfaces['white'] ) );

$header_args = array(
	'layout' => 'split',
	'lead'   => (bool) get_field( 'lead_intro' ),
	'reveal' => true,
);

$grid = static function ( $items ) use ( $variant ) {
	if ( ! $items ) {
		return;
	}
	?>
	<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 <?php echo 'case' === $variant ? 'lg:gap-5' : ''; ?>">
		<?php
		foreach ( array_values( $items ) as $index => $item ) {
			get_template_part(
				'template-parts/cards/resource',
				null,
				array(
					'post'    => $item,
					'variant' => $variant,
					'index'   => $index,
				)
			);
		}
		?>
	</div>
	<?php
};
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php if ( in_array( $layout, array( 'split', 'guides' ), true ) ) : ?>
			<div class="flex flex-col gap-8 lg:flex-row lg:gap-11">
				<div class="lg:min-w-0 lg:flex-1">
					<?php
					upskill_section_header(
						array(
							'layout'         => 'stack',
							'button_variant' => 'light',
							'reveal'         => true,
							'class'          => 'section-header--base block-post-cards__side',
						)
					);
					?>
				</div>

				<?php if ( 'guides' === $layout && $posts ) : ?>
					<div class="grid grid-cols-1 gap-4 md:grid-cols-[428fr_380fr] lg:w-[824px] lg:flex-none">
						<?php
						get_template_part(
							'template-parts/cards/guide',
							null,
							array(
								'post'    => $posts[0],
								'variant' => 'feature',
							)
						);
						?>
						<div class="flex flex-col gap-4">
							<?php
							foreach ( array_slice( $posts, 1 ) as $index => $item ) {
								get_template_part(
									'template-parts/cards/guide',
									null,
									array(
										'post'    => $item,
										'variant' => 'row',
										'index'   => $index + 1,
									)
								);
							}
							?>
						</div>
					</div>
				<?php elseif ( $posts ) : ?>
					<?php // Three 264px cards (824 with gaps) on desktop; a swipeable row on phones. ?>
					<div class="-mx-gutter flex snap-x snap-mandatory gap-4 overflow-x-auto px-gutter pb-1 [scrollbar-width:none] sm:mx-0 sm:grid sm:grid-cols-3 sm:overflow-visible sm:px-0 lg:w-[824px] lg:flex-none">
						<?php foreach ( $posts as $index => $item ) : ?>
							<div class="flex w-[264px] flex-none snap-start sm:w-auto *:flex-1">
								<?php
								get_template_part(
									'template-parts/cards/resource',
									null,
									array(
										'post'    => $item,
										'variant' => 'compact',
										'index'   => $index,
									)
								);
								?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

		<?php else : ?>
			<?php upskill_section_header( $header_args ); ?>

			<?php if ( $featured instanceof WP_Post && ( 'featured' === $layout || 1 === max( 1, (int) get_query_var( 'paged' ) ) ) ) : ?>
				<div class="mt-stack<?php echo 'listing' === $layout ? ' lg:mt-[70px]' : ''; ?>">
					<?php
					get_template_part(
						'template-parts/cards/resource',
						null,
						array(
							'post'    => $featured,
							'variant' => 'featured',
							'tag'     => 'h2',
							'links'   => $links,
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( $posts ) : ?>
				<div class="<?php echo 'listing' === $layout && $featured ? 'mt-4' : 'mt-stack'; ?>">
					<?php $grid( $posts ); ?>
				</div>
			<?php endif; ?>

			<?php
			if ( 'listing' === $layout ) {
				upskill_pagination( $query );
			}
			?>
		<?php endif; ?>
	</div>
</section>
