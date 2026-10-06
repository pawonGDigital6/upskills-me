<?php
/**
 * Home Hero block.
 *
 * Figma: Homepage section 49325:14380 (1440) / Frame 96 + Container
 * 49196:7746, 49196:7747 (390).
 *
 * The showcase panels and the pathway selector are driven by Industry and
 * Category terms, so names, links and session counts stay in step with the
 * training library. Only the showcase photography is owned by the block.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$attributes  = upskill_block_attributes( $block, 'block-hero overflow-hidden bg-gray-25 pt-[46px] pb-[46px] lg:pt-[104px] lg:pb-20' );
$count_label = get_field( 'showcase_count_label' );
$showcase    = array();

// Resolve each panel's term once; a panel whose term is gone is skipped.
foreach ( (array) get_field( 'showcase' ) as $row ) {
	$taxonomy = ( isset( $row['taxonomy'] ) && 'training_category' === $row['taxonomy'] ) ? 'training_category' : 'industry';
	$term     = upskill_selected_terms( 'industry' === $taxonomy ? $row['industry'] : $row['category'], $taxonomy );

	if ( $term && ! empty( $row['image'] ) ) {
		$showcase[] = array(
			'term'  => $term[0],
			'image' => $row['image'],
			'focus' => ! empty( $row['focus'] ) ? $row['focus'] : 'center',
		);
	}
}

$tabs = array_filter(
	array(
		'industry'          => array(
			'label' => __( 'By Industry', 'upskill-me' ),
			'terms' => upskill_selected_terms( get_field( 'pathway_industries' ), 'industry' ),
		),
		'training_category' => array(
			'label' => __( 'By Category', 'upskill-me' ),
			'terms' => upskill_selected_terms( get_field( 'pathway_categories' ), 'training_category' ),
		),
	),
	static function ( $tab ) {
		return ! empty( $tab['terms'] );
	}
);

$focus_classes = array(
	'left'         => 'object-left',
	'left-center'  => 'object-[30%_center]',
	'center'       => 'object-center',
	'right-center' => 'object-[70%_center]',
	'right'        => 'object-right',
);

$uid = wp_unique_id( 'hero-' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<div class="flex flex-col gap-[46px] lg:flex-row lg:items-stretch lg:gap-16">
			<div class="flex flex-col justify-center gap-6 lg:min-w-0 lg:flex-1">
				<div class="flex flex-col items-start gap-3">
					<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
					<?php upskill_heading( get_field( 'heading' ), 'h1', 'text-h1 font-medium text-gray-900 lg:font-semibold' ); ?>
				</div>
				<?php if ( get_field( 'intro' ) ) : ?>
					<p class="text-lead text-gray-500"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $showcase ) : ?>
				<?php // 502 + 156 + 156 + 2x16 = 846 of the 1360 content box. ?>
				<div class="grid grid-cols-2 gap-4 lg:flex lg:h-[590px] lg:w-[62.2%] lg:flex-none" data-showcase>
					<?php foreach ( $showcase as $index => $panel ) : ?>
						<?php
						$name  = $panel['term']->name;
						$count = upskill_term_session_count( $panel['term'] );
						?>
						<?php // One grid cell holds the photo, gradient and labels stacked in normal flow; panel size comes from Figma ratios (390) or the 590 row (desktop). ?>
						<a class="group grid grid-cols-1 grid-rows-1 overflow-hidden rounded-[17px] lg:rounded-xl lg:transition-[flex-grow] lg:duration-500 lg:ease-out <?php echo 0 === $index ? 'col-span-2 aspect-[362/418] is-active sm:aspect-[16/9]' : 'aspect-[170/200] sm:aspect-[4/3]'; ?> lg:aspect-auto lg:basis-0 lg:[&.is-active]:grow-[502] lg:[&:not(.is-active)]:grow-[156]" href="<?php echo esc_url( upskill_term_url( $panel['term'] ) ); ?>" data-showcase-item>
							<?php
							upskill_image(
								$panel['image'],
								// Images are uploaded pre-cropped to the panel ratio at 2x, so the full
								// file plus its proportional sizes make the srcset.
								'full',
								array(
									'class'   => 'col-start-1 row-start-1 size-full object-cover ' . ( isset( $focus_classes[ $panel['focus'] ] ) ? $focus_classes[ $panel['focus'] ] : 'object-center' ),
									'alt'     => '',
									// Above the fold, so never lazy. Sizes are the width the photo is
									// drawn at under object-cover, not the panel width: a folded 156px
									// panel still shows ~502px of image at 590px tall.
									'loading' => 'eager',
									'sizes'   => 0 === $index ? '(min-width: 1025px) 502px, 92vw' : '(min-width: 1025px) 502px, 46vw',
								)
							);
							?>
							<?php // `relative` keeps the gradient and labels painted above the photo while it zooms on hover (a transform lifts it into the positioned layer). ?>
							<span class="relative col-start-1 row-start-1 bg-linear-to-b from-black/0 from-50% to-black/80" aria-hidden="true"></span>

							<?php // The open panel's label: a translucent bar with the name and, on desktop, the topic count. ?>
							<span class="relative col-start-1 row-start-1 flex items-center justify-between gap-4 self-end bg-white/10 px-3 py-2 text-white lg:hidden lg:py-[22px] lg:pr-0 lg:pl-4 lg:group-[.is-active]:flex">
								<span class="min-w-0 text-[24px] leading-[1.1] font-medium tracking-[-0.01em] lg:text-[clamp(2rem,3.3vw,3rem)]"><?php echo esc_html( $name ); ?></span>
								<?php if ( $count > 0 ) : ?>
									<span class="hidden w-[91px] flex-none flex-col items-center text-center lg:flex">
										<span class="text-[52px] leading-[52px]"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
										<?php if ( $count_label ) : ?>
											<span class="text-sm leading-[22px] uppercase"><?php echo esc_html( $count_label ); ?></span>
										<?php endif; ?>
									</span>
								<?php endif; ?>
							</span>

							<?php // A closed panel's label runs up its left edge on a deep purple tab, sized by its own text (vertical writing mode, read bottom to top). ?>
							<span class="relative col-start-1 row-start-1 hidden self-end justify-self-start bg-brand-950 p-4 text-[40px] leading-[1.1] font-medium tracking-[-0.01em] whitespace-nowrap text-white [writing-mode:vertical-rl] rotate-180 lg:block lg:group-[.is-active]:hidden" aria-hidden="true"><?php echo esc_html( $name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $tabs ) : ?>
			<div class="mt-[46px] overflow-hidden rounded-2xl border border-gray-900/10 bg-white shadow-card lg:mt-12 lg:shadow-[0_2px_4px_rgb(26_26_26/3%)] lg:rounded-[32px] lg:border-gray-100" data-pathway>
				<div class="flex flex-col gap-4 px-5 py-[19px] lg:flex-row lg:items-center lg:gap-6 lg:pt-5 lg:pb-4">
					<?php if ( count( $tabs ) > 1 ) : ?>
						<div class="flex w-full rounded-full border border-gray-100 bg-gray-50 p-1.5 lg:w-auto" role="tablist" aria-label="<?php esc_attr_e( 'Find training', 'upskill-me' ); ?>">
							<?php $first = true; ?>
							<?php foreach ( $tabs as $key => $tab ) : ?>
								<button class="h-[37px] flex-1 rounded-full text-sm font-medium text-gray-950 transition-colors lg:w-[98px] lg:flex-none aria-selected:bg-gray-900 aria-selected:text-white aria-selected:shadow-btn" type="button" role="tab" id="<?php echo esc_attr( $uid . '-tab-' . $key ); ?>" aria-controls="<?php echo esc_attr( $uid . '-panel-' . $key ); ?>" aria-selected="<?php echo $first ? 'true' : 'false'; ?>" tabindex="<?php echo $first ? '0' : '-1'; ?>" data-pathway-tab><?php echo esc_html( $tab['label'] ); ?></button>
								<?php $first = false; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php if ( get_field( 'pathway_text' ) ) : ?>
						<p class="text-base text-gray-500 lg:text-sm"><?php echo esc_html( get_field( 'pathway_text' ) ); ?></p>
					<?php endif; ?>
				</div>

				<?php // Below 1280 the three terms and the 382px search no longer share a row, so the search wraps under them. ?>
				<div class="lg:flex lg:flex-wrap lg:items-center lg:gap-4 lg:border-t lg:border-gray-100 lg:px-5 lg:pt-2 lg:pb-5">
					<?php $first = true; ?>
					<?php foreach ( $tabs as $key => $tab ) : ?>
						<ul class="flex flex-col lg:flex-row lg:items-center lg:gap-4 lg:pl-3" id="<?php echo esc_attr( $uid . '-panel-' . $key ); ?>"<?php echo count( $tabs ) > 1 ? ' role="tabpanel" aria-labelledby="' . esc_attr( $uid . '-tab-' . $key ) . '"' : ''; ?><?php echo $first ? '' : ' hidden'; ?>>
							<?php foreach ( $tab['terms'] as $term ) : ?>
								<li class="lg:min-w-[200px] lg:border-r lg:border-gray-100">
									<a class="group flex items-center justify-between gap-4 px-5 py-3 lg:items-start lg:p-0 lg:pr-[7px] lg:whitespace-nowrap" href="<?php echo esc_url( upskill_term_url( $term ) ); ?>">
										<span class="flex flex-col gap-1">
											<span class="text-[17px] leading-[1.62] font-medium tracking-[-0.022em] text-gray-900 lg:text-h6 lg:leading-[1.1]"><?php echo esc_html( $term->name ); ?></span>
											<?php upskill_session_chip( upskill_term_session_count( $term ) ); ?>
										</span>
										<span class="icon-link size-7 bg-gray-25 text-gray-900 group-hover:bg-brand-700 group-hover:text-white lg:size-[30px] lg:bg-[#ededef]" aria-hidden="true">
											<?php upskill_icon( 'arrow-up-right', 'size-[11px] lg:size-3' ); ?>
										</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php $first = false; ?>
					<?php endforeach; ?>

					<form class="border-t border-gray-900/10 px-5 py-4 lg:flex lg:w-full lg:items-center lg:gap-4 lg:border-0 lg:p-0 mc:ml-auto mc:w-auto" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<label class="screen-reader-text" for="<?php echo esc_attr( $uid . '-search' ); ?>"><?php esc_html_e( 'Search for training', 'upskill-me' ); ?></label>
						<?php // The pill is the field: icon, input and (on the 390 frame) the round submit sit side by side inside it. ?>
						<span class="flex min-h-12 items-center gap-[11px] rounded-full border border-gray-900/20 bg-white py-1 pr-1 pl-4 focus-within:outline-2 focus-within:outline-offset-3 focus-within:outline-brand-700 lg:min-h-[46px] lg:flex-1 lg:py-0 lg:pr-[18px] lg:pl-[17px] mc:w-[382px] mc:flex-none">
							<?php upskill_icon( 'search', 'hidden size-[18px] flex-none text-[#6e6e74] lg:block' ); ?>
							<input class="min-w-0 flex-1 bg-transparent text-base text-gray-950 placeholder:text-[#6b6b72] focus:outline-none" id="<?php echo esc_attr( $uid . '-search' ); ?>" type="search" name="s" placeholder="<?php echo esc_attr( get_field( 'search_placeholder' ) ? get_field( 'search_placeholder' ) : __( 'Or describe what you need', 'upskill-me' ) ); ?>">
							<?php // On the 390 frame the submit is a round button inside the field. ?>
							<button class="grid size-10 flex-none place-items-center rounded-full bg-brand-700 text-white shadow-btn lg:hidden" type="submit">
								<span class="screen-reader-text"><?php esc_html_e( 'Search', 'upskill-me' ); ?></span>
								<span class="grid size-6 place-items-center rounded-full bg-white/20"><?php upskill_icon( 'arrow-up-right', 'size-3' ); ?></span>
							</button>
						</span>
						<button class="btn btn--primary btn--md hidden lg:inline-flex" type="submit">
							<span class="btn__label"><?php esc_html_e( 'Search', 'upskill-me' ); ?></span>
							<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
						</button>
					</form>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
