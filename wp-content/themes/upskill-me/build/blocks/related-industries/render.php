<?php
/**
 * Related Industries block.
 *
 * Figma: Manufacturing "Keep exploring" 49325:15019 (1440) / 49354:94802 (390).
 *
 * A split section header over two wide industry cards. On an industry page the
 * cards are the other industries (not coming soon); the block's own choice, if
 * any, comes first, still without the industry being viewed. Each card keeps
 * its number in the full industry list (Manufacturing 01, Training 02, …) and
 * takes its words, icon and tags from the industry's card fields, as in
 * Industry Showcase; the first card is light for light/outlined industries,
 * purple for dark/purple, and the second always takes the other look.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$all     = get_terms(
	array(
		'taxonomy'   => 'industry',
		'hide_empty' => false,
		'orderby'    => 'term_id',
	)
);
$all     = is_wp_error( $all ) ? array() : $all;
$numbers = array_flip( wp_list_pluck( $all, 'term_id' ) );
$current = is_tax( 'industry' ) ? get_queried_object_id() : 0;
$chosen  = upskill_selected_terms( get_field( 'industries' ), 'industry' );
$terms   = array_slice(
	array_values(
		array_filter(
			$chosen ? $chosen : $all,
			static function ( $term ) use ( $current, $chosen ) {
				return $term->term_id !== $current && ( $chosen || ! get_field( 'coming_soon', $term ) );
			}
		)
	),
	0,
	2
);

/*
 * The two Figma cards. Light: #f7f7f7 under a soft white swirl, square glassy
 * icon tile. Purple: #2f1a51 under the purple swirl.
 */
$looks = array(
	'light' => array(
		'card'    => 'border border-gray-100 bg-gray-25 text-[#1a1a1a]',
		'texture' => 'texture-soft.webp',
		'chip'    => 'border-[#1a1a1a]/10 bg-white/70 text-brand-700',
		'number'  => 'text-[#6b6b72]',
		'text'    => 'text-[#55555a]',
		'link'    => 'text-[#6744a8]',
		'tag'     => 'border-[#1a1a1a]/10 bg-white/62 text-[#55555a]',
	),
	'dark'  => array(
		'card'    => 'bg-[#2f1a51] text-white',
		'texture' => 'card-bg-grph.webp',
		'chip'    => 'border-white/18 bg-white/10 text-white',
		'number'  => 'text-white/60',
		'text'    => 'text-white/68',
		'link'    => 'text-[#d0c3ef]',
		'tag'     => 'border-white/18 bg-white/10 text-white/86',
	),
);

$attributes = upskill_block_attributes( $block, 'block-related-industries bg-white py-[46px] lg:py-[108px]' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php // Figma top-aligns the intro and button with the eyebrow. ?>
		<?php upskill_section_header( array( 'class' => 'related-industries__header lg:items-start' ) ); ?>

		<?php if ( $terms ) : ?>
			<ul class="related-industries__list mt-12 grid gap-4 md:grid-cols-2">
				<?php $first = ''; ?>
				<?php foreach ( $terms as $index => $term ) : ?>
					<?php
					// The first card follows its industry; the second always takes the other look, so the pair never repeats a background.
					$key   = $first ? ( 'dark' === $first ? 'light' : 'dark' ) : ( in_array( get_field( 'card_style', $term ), array( 'dark', 'brand' ), true ) ? 'dark' : 'light' );
					$first = $first ? $first : $key;
					$look  = $looks[ $key ];
					$tags  = array_filter( array_map( 'trim', explode( ',', (string) get_field( 'card_tags', $term ) ) ) );
					$label = (string) get_field( 'card_link_label', $term );
					$icon  = (string) get_field( 'card_icon', $term );
					?>
					<li class="related-industries__item reveal group relative isolate grid grid-cols-1 grid-rows-1 overflow-hidden rounded-[24px] shadow-xs *:col-start-1 *:row-start-1 <?php echo esc_attr( $look['card'] ); ?>" style="--i:<?php echo (int) $index; ?>">
						<img class="related-industries__texture pointer-events-none h-0 min-h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105" src="<?php echo esc_url( upskill_theme_image( $look['texture'] ) ); ?>" alt="" loading="lazy" decoding="async">

						<?php // 16px in (20 on desktop); on desktop the card is 350 tall with the copy and footer at the bottom. ?>
						<div class="related-industries__body relative flex flex-col p-4 lg:min-h-[350px] lg:p-5">
							<div class="related-industries__head flex items-start justify-between">
								<span class="related-industries__icon grid size-9 place-items-center rounded-[10px] border <?php echo esc_attr( $look['chip'] ); ?>" aria-hidden="true">
									<?php upskill_icon( $icon ? $icon : 'industry', 'size-[17px]' ); ?>
								</span>
								<span class="related-industries__number text-xs leading-[1.62] font-medium tracking-[-0.1px] <?php echo esc_attr( $look['number'] ); ?>" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( ( $numbers[ $term->term_id ] ?? $index ) + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							</div>

							<div class="related-industries__content mt-auto flex flex-col gap-2 pt-[86px]">
								<h3 class="related-industries__title text-h6 font-medium lg:text-h5">
									<?php // The whole card is the link; the title carries its name. ?>
									<a class="related-industries__link after:absolute after:inset-0 after:z-10 after:rounded-[inherit] focus-visible:outline-none" href="<?php echo esc_url( upskill_term_url( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
								</h3>
								<?php if ( $term->description ) : ?>
									<p class="related-industries__text text-base <?php echo esc_attr( $look['text'] ); ?>"><?php echo esc_html( $term->description ); ?></p>
								<?php endif; ?>
								<?php if ( $label ) : ?>
									<span class="related-industries__more inline-flex items-center gap-[7px] pt-2 text-sm font-medium capitalize <?php echo esc_attr( $look['link'] ); ?>" aria-hidden="true">
										<?php echo esc_html( $label ); ?>
										<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
									</span>
								<?php endif; ?>
							</div>

							<div class="related-industries__foot mt-3.5 flex items-end justify-between gap-4">
								<?php if ( $tags ) : ?>
									<ul class="related-industries__tags flex flex-wrap gap-1.5" aria-label="<?php esc_attr_e( 'Topics', 'upskill-me' ); ?>">
										<?php foreach ( $tags as $tag ) : ?>
											<li class="related-industries__tag rounded-full border px-[11px] py-1 text-[10px] leading-[15px] font-bold whitespace-nowrap <?php echo esc_attr( $look['tag'] ); ?>"><?php echo esc_html( $tag ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
								<span class="related-industries__arrow ml-auto grid size-10 flex-none place-items-center rounded-full border border-[#1a1a1a]/20 bg-white text-gray-900 transition-colors group-hover:border-brand-700 group-hover:bg-brand-700 group-hover:text-white" aria-hidden="true">
									<?php upskill_icon( 'arrow-up-right', 'size-3.5' ); ?>
								</span>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
