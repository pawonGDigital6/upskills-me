<?php
/**
 * Category List block.
 *
 * Figma: Homepage section 49325:14465 (1440) / Section 49196:7750 (390).
 *
 * One numbered row per selected Category term: name, the term description and
 * the session count. An optional closing row links to the full library.
 *
 * On desktop a row's hover reveals its photograph in a reserved column (the
 * designer's approved hover state, see style.scss). The photo is the
 * category's "Card image"; the closing row has its own.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$attributes = upskill_block_attributes( $block, 'block-category-list pt-section pb-section lg:pb-0' );
$rows       = array();

foreach ( upskill_selected_terms( get_field( 'categories' ), 'training_category' ) as $term ) {
	$rows[] = array(
		'title'       => $term->name,
		'description' => $term->description,
		'url'         => upskill_term_url( $term ),
		'count'       => upskill_term_session_count( $term ),
		'image'       => get_field( 'card_image', $term ),
	);
}

$all = get_field( 'all_row' );

if ( ! empty( $all['link']['url'] ) ) {
	$library = 0;

	// The full library's size, once LearnDash provides the course post type.
	if ( post_type_exists( UPSKILL_COURSE_POST_TYPE ) ) {
		$library = (int) wp_count_posts( UPSKILL_COURSE_POST_TYPE )->publish;
	}

	$rows[] = array(
		'title'       => ! empty( $all['link']['title'] ) ? $all['link']['title'] : __( 'All Categories', 'upskill-me' ),
		'description' => isset( $all['description'] ) ? $all['description'] : '',
		'url'         => $all['link']['url'],
		'count'       => (int) apply_filters( 'upskill_library_session_count', $library ),
		'image'       => isset( $all['image'] ) ? $all['image'] : 0,
	);
}
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php upskill_section_header(); ?>

		<?php if ( $rows ) : ?>
			<ol class="mt-stack border-t border-gray-900/10">
				<?php foreach ( $rows as $index => $row ) : ?>
					<li class="reveal border-b border-gray-100" style="--i:<?php echo (int) min( $index, 3 ); ?>">
						<a class="category-row group grid grid-cols-[28px_1fr] items-start gap-x-4 gap-y-6 py-6 lg:flex lg:items-center" href="<?php echo esc_url( $row['url'] ); ?>">
							<span class="w-7 flex-none pt-1 text-sm text-[#6e6e74] lg:pt-0 lg:font-bold" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<span class="flex min-w-0 flex-1 flex-col gap-1.5">
								<span class="text-h4 font-medium text-gray-900 transition-colors group-hover:text-brand-700"><?php echo esc_html( $row['title'] ); ?></span>
								<?php if ( $row['description'] ) : ?>
									<span class="max-w-[560px] text-lg text-gray-500 lg:min-h-12 lg:text-base"><?php echo esc_html( $row['description'] ); ?></span>
								<?php endif; ?>
							</span>
							<?php if ( $row['image'] ) : ?>
								<?php // The column is always reserved, so the row never reflows when the photo appears. ?>
								<span class="category-row__pic hidden flex-none items-center wd:flex" aria-hidden="true">
									<?php upskill_image( $row['image'], 'medium', array( 'alt' => '', 'sizes' => '150px' ) ); ?>
								</span>
							<?php endif; ?>
							<span class="col-span-2 flex items-center justify-between gap-[30px] lg:flex-none lg:justify-start">
								<?php upskill_session_chip( $row['count'], 'session-chip--lg' ); ?>
								<span class="category-row__go icon-link ml-auto size-10 border-[1.5px] border-gray-900/20 text-gray-900 group-hover:border-brand-700 group-hover:bg-brand-700 group-hover:text-white lg:ml-0 lg:size-[60px]" aria-hidden="true">
									<?php upskill_icon( 'arrow-up-right', 'size-4 lg:size-[21px]' ); ?>
								</span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>
</section>
