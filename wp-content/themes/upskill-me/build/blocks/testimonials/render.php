<?php
/**
 * Testimonials block.
 *
 * Figma: Why UpSkill Me 49325:15681 / Case Studies 49325:18295 (1440),
 * 49374:45600 (390).
 *
 * A scroll-snap carousel of Testimonial records: the current card is centred
 * with its neighbours peeking in (left-aligned on phones). view.js wires the
 * arrows and the progress bars; without it the row still scrolls and snaps.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$query_args = array(
	'post_type'              => 'testimonial',
	'post_status'            => 'publish',
	'no_found_rows'          => true,
	'update_post_term_cache' => false,
);

$selected = (array) get_field( 'selected_testimonials' );

if ( 'selected' === get_field( 'selection' ) && $selected ) {
	$query_args['post__in']       = array_map( 'absint', $selected );
	$query_args['orderby']        = 'post__in';
	$query_args['posts_per_page'] = count( $selected );
} else {
	$query_args['posts_per_page'] = max( 1, (int) get_field( 'count' ) );
	$query_args['orderby']        = array(
		'menu_order' => 'ASC',
		'date'       => 'DESC',
	);
}

$items = get_posts( $query_args );

if ( ! $items ) {
	return;
}

$uid        = wp_unique_id( 'testimonials-' );
$attributes = upskill_block_attributes( $block, 'block-testimonials relative section-y overflow-hidden bg-brand-100' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?> aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Testimonials', 'upskill-me' ); ?>" data-carousel>
	<ul class="testimonials__track flex snap-x snap-mandatory gap-4 overflow-x-auto overscroll-x-contain px-gutter [scrollbar-width:none] lg:gap-16" id="<?php echo esc_attr( $uid ); ?>" data-carousel-track>
		<?php foreach ( $items as $index => $item ) : ?>
			<?php
			$rating = (int) get_field( 'rating', $item );
			$name   = get_field( 'name', $item );
			?>
			<li class="testimonials__slide relative flex w-[77%] flex-none snap-start flex-col rounded-[24px] bg-brand-50 p-4 sm:w-[66%] sm:p-6 lg:min-h-[450px] lg:w-[min(1120px,78vw)] lg:snap-center lg:p-10" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: total. */ __( '%1$d of %2$d', 'upskill-me' ), $index + 1, count( $items ) ) ); ?>" data-carousel-slide>
				<div class="flex items-start justify-between gap-4">
					<?php upskill_icon( 'quote', 'h-[46px] w-[49px] text-brand-500 lg:h-[66px] lg:w-[71px]' ); ?>
					<?php if ( $rating > 0 ) : ?>
						<p class="flex gap-1 text-[#fbbf24]">
							<span class="screen-reader-text">
								<?php
								/* translators: %d: rating out of five. */
								printf( esc_html__( 'Rated %d out of 5', 'upskill-me' ), (int) min( $rating, 5 ) );
								?>
							</span>
							<?php for ( $star = 0; $star < min( $rating, 5 ); $star++ ) : ?>
								<?php upskill_icon( 'star', 'size-[22px]' ); ?>
							<?php endfor; ?>
						</p>
					<?php endif; ?>
				</div>
				<blockquote class="flex flex-col pt-[72px] lg:pt-[94px]">
					<p class="text-h6 font-medium text-gray-950"><?php echo esc_html( get_the_title( $item ) ); ?></p>
					<div class="pt-4 text-base text-gray-950"><?php echo wp_kses_post( wpautop( get_post_field( 'post_content', $item ) ) ); ?></div>
				</blockquote>
				<?php if ( $name ) : ?>
					<p class="mt-auto pt-10 text-xl font-medium text-gray-950"><?php echo esc_html( $name ); ?></p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( count( $items ) > 1 ) : ?>
		<div class="container mt-7 lg:mt-14">
			<div class="mx-auto flex max-w-[1184px] items-center justify-between gap-4">
				<ol class="flex gap-2" aria-hidden="true">
					<?php foreach ( $items as $index => $item ) : ?>
						<li class="testimonials__bar h-1 w-[38px] bg-white<?php echo 0 === $index ? ' is-filled' : ''; ?>" data-carousel-bar></li>
					<?php endforeach; ?>
				</ol>
				<div class="flex gap-2">
					<?php foreach ( array( 'prev' => __( 'Previous testimonial', 'upskill-me' ), 'next' => __( 'Next testimonial', 'upskill-me' ) ) as $direction => $label ) : ?>
						<button class="testimonials__nav grid size-[42px] place-items-center rounded-full bg-brand-700 text-white shadow-btn transition-colors hover:bg-brand-800 disabled:opacity-40" type="button" aria-controls="<?php echo esc_attr( $uid ); ?>" data-carousel-<?php echo esc_attr( $direction ); ?>>
							<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
							<span class="grid size-6 place-items-center rounded-full bg-white/16" aria-hidden="true"><?php upskill_icon( 'arrow-right', 'size-[13px]' . ( 'prev' === $direction ? ' rotate-180' : '' ) ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</section>
