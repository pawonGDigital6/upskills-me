<?php
/**
 * Industry Showcase block.
 *
 * Figma: Homepage section 49325:14430 (1440) / Frame 99 49196:7749 (390).
 *
 * Each card is an Industry term. The card's look (dark, light, purple or
 * outlined), icon, tags and "coming soon" flag are fields on the term, so the
 * same industry looks the same wherever it is listed.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$attributes = upskill_block_attributes( $block, 'block-industry-showcase section-y bg-white' );
$terms      = upskill_selected_terms( get_field( 'industries' ), 'industry' );

/*
 * Per-style tokens, from the four Figma cards. `surface` is the card colour
 * under its 20% texture; `chip` is the icon disc.
 */
$styles = array(
	'dark'    => array(
		'surface' => 'bg-gray-950 text-white',
		'texture' => 'texture-dark.webp',
		'chip'    => 'bg-gray-900 text-white',
		'text'    => 'text-white/60',
		'link'    => 'text-white',
	),
	'light'   => array(
		'surface' => 'bg-gray-100 text-gray-950',
		'texture' => 'texture-light.webp',
		'chip'    => 'bg-gray-200 text-gray-950',
		'text'    => 'text-gray-950',
		'link'    => 'text-brand-700',
	),
	'brand'   => array(
		'surface' => 'bg-brand-950 text-white',
		'texture' => 'texture-brand.webp',
		'chip'    => 'bg-brand-900 text-white',
		'text'    => 'text-brand-50',
		'link'    => 'text-white',
	),
	'outline' => array(
		'surface' => 'bg-gray-100 text-gray-950 ring-1 ring-inset ring-gray-200',
		'texture' => 'texture-outline.webp',
		'chip'    => 'bg-gray-25 text-gray-800',
		'text'    => 'text-gray-900',
		'link'    => 'text-brand-700',
	),
);
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php upskill_section_header( array( 'layout' => 'center' ) ); ?>

		<?php if ( $terms ) : ?>
			<ul class="industry-showcase__list sm:mt-18 mt-8 grid gap-4 sm:grid-cols-2 md:grid-cols-3 wd:flex wd:items-end">
				<?php foreach ( $terms as $index => $term ) : ?>
					<?php
					$style       = get_field( 'card_style', $term );
					$tokens      = isset( $styles[ $style ] ) ? $styles[ $style ] : $styles['light'];
					$tags        = array_filter( array_map( 'trim', explode( ',', (string) get_field( 'card_tags', $term ) ) ) );
					$link_label  = get_field( 'card_link_label', $term );
					$coming_soon = (bool) get_field( 'coming_soon', $term );
					$url         = upskill_term_url( $term );
					// Figma staggers the row: the outer cards stand taller (524) than the inner two (404).
					$tall = in_array( $index % 4, array( 0, 3 ), true );
					?>
					<li class="industry-showcase__item reveal group relative isolate flex min-h-[404px] flex-col overflow-hidden sm:rounded-card rounded-4xl p-4 shadow-[0_1px_1px_rgb(10_13_18/5%)] wd:flex-1 <?php echo esc_attr( $tokens['surface'] . ( $tall ? ' wd:min-h-[524px]' : ' wd:min-h-[414px]' ) ); ?>" style="--i:<?php echo (int) min( $index, 3 ); ?>">
						<img class="industry-showcase__texture absolute inset-0 -z-10 size-full object-cover opacity-20 transition-transform duration-700 ease-out group-hover:scale-105" src="<?php echo esc_url( upskill_theme_image( $tokens['texture'] ) ); ?>" alt="" loading="lazy" decoding="async">

						<div class="industry-showcase__head relative flex items-center justify-between sm:pt-10">
							<span class="industry-showcase__icon grid size-10 place-items-center rounded-full <?php echo esc_attr( $tokens['chip'] ); ?>" aria-hidden="true">
								<?php upskill_icon( get_field( 'card_icon', $term ) ? get_field( 'card_icon', $term ) : 'industry', 'size-6' ); ?>
							</span>
							<span class="industry-showcase__number text-base" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						</div>

						<div class="industry-showcase__body mt-10 flex flex-col gap-1">
							<h3 class="industry-showcase__title text-h5 max-sm:text-[36px] font-medium">
								<?php // The whole card is the link; the title carries its name. ?>
								<a class="industry-showcase__link after:absolute after:inset-0 after:z-10 focus-visible:outline-none" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $term->name ); ?></a>
							</h3>
							<?php if ( $coming_soon ) : ?>
								<span class="industry-showcase__badge self-start rounded-sm bg-brand-100/10 px-[9px] py-1 text-2xs font-medium text-brand-500"><?php esc_html_e( 'Coming Soon', 'upskill-me' ); ?></span>
							<?php endif; ?>
							<?php if ( $term->description ) : ?>
								<p class="industry-showcase__text mb-4 text-base <?php echo esc_attr( $tokens['text'] ); ?>"><?php echo esc_html( $term->description ); ?></p>
							<?php endif; ?>
							<?php if ( $link_label ) : ?>
								<span class="industry-showcase__more inline-flex items-center gap-2 text-sm font-medium <?php echo esc_attr( $tokens['link'] ); ?>" aria-hidden="true">
									<?php echo esc_html( $link_label ); ?>
									<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
								</span>
							<?php endif; ?>
						</div>

						<div class="industry-showcase__foot mt-auto flex items-end justify-between gap-4 wd:pt-1 pt-3">
							<?php if ( $tags ) : ?>
								<ul class="industry-showcase__tags flex flex-wrap gap-1.5<?php echo $tall ? ' max-w-[141px]' : ''; ?>" aria-label="<?php esc_attr_e( 'Topics', 'upskill-me' ); ?>">
									<?php foreach ( $tags as $tag ) : ?>
										<li class="industry-showcase__tag rounded-full border border-white/28 bg-[rgb(14_14_16/34%)] px-2.25 py-1.5 font-[Geist] text-xs leading-none tracking-[-0.008em] whitespace-nowrap text-white"><?php echo esc_html( $tag ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<span class="industry-showcase__arrow icon-link ml-auto size-[38px] bg-white/92 text-gray-900 shadow-btn group-hover:bg-brand-700 group-hover:text-white" aria-hidden="true">
								<?php upskill_icon( 'arrow-up-right', 'size-3.5' ); ?>
							</span>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
