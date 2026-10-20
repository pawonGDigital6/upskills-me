<?php
/**
 * Feature card — the cards the Card Grid block draws.
 *
 * Styles, from the Figma frames:
 * - stacked:   icon (or accent bar), number, label, title, text. Why 49325:15613,
 *              About 49325:14680 / 14730 / 14758.
 * - principle: icon top left, label top right, title and text at the foot. Why 49325:15528.
 * - glass:     translucent card for dark sections. Case Studies 49325:18246.
 *
 * Figma draws one card of a row in black (textured; the principle card as a
 * plain gradient). Rather than fixing it to one card, every light card takes
 * that look on hover, so the row stays even on touch screens.
 *
 * @param array $args {
 *     @type array  $card   icon, label, title, text, points, link.
 *     @type int    $index  Position in the grid (0-based).
 *     @type string $style  stacked|principle|glass.
 *     @type bool   $number Show the 01/02 number.
 *     @type bool   $large  24px titles instead of 18px.
 *     @type bool   $roomy  30px padding instead of 16px.
 *     @type bool   $framed In a framed grid: the number sits in a box.
 *     @type bool   $side   In the two-up "side" layout: Figma gives these cards
 *                          30px padding, 18px titles and a 24px gap under the
 *                          icon on phones (About 49374:47139).
 * }
 *
 * @package upskill-me
 */

$card   = $args['card'];
$index  = (int) $args['index'];
$style  = $args['style'];
$number = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
$title  = isset( $card['title'] ) ? $card['title'] : '';
$text   = isset( $card['text'] ) ? $card['text'] : '';
$label  = isset( $card['label'] ) ? $card['label'] : '';
$icon   = isset( $card['icon'] ) ? $card['icon'] : '';
$link   = ! empty( $card['link']['url'] ) ? $card['link'] : null;
$points = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $card['points'] ?? '' ) ) ) );
$stagger = 'style="--i:' . (int) min( $index, 5 ) . '"';

/*
 * The title, as the card's link when it has one: a stretched pseudo-element
 * makes the whole card the target without nesting interactive content.
 */
$title_markup = static function ( $classes ) use ( $title, $link ) {
	if ( ! $title ) {
		return;
	}

	if ( $link ) {
		printf(
			'<h3 class="%1$s"><a class="after:absolute after:inset-0 after:z-10 after:rounded-[inherit] focus-visible:outline-none"%2$s>%3$s</a></h3>',
			esc_attr( $classes ),
			upskill_link_attributes( $link ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
			esc_html( $title )
		);
		return;
	}

	printf( '<h3 class="%1$s">%2$s</h3>', esc_attr( $classes ), esc_html( $title ) );
};

if ( 'glass' === $style ) :
	?>
	<li class="reveal feature-card relative flex flex-col rounded-[24px] border border-white/14 bg-white/4 p-6" <?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<?php if ( $icon ) : ?>
			<span class="grid size-10 place-items-center rounded-[11px] bg-white/10 text-white" aria-hidden="true"><?php upskill_icon( $icon, 'size-[19px]' ); ?></span>
		<?php endif; ?>
		<?php $title_markup( 'pt-5 text-xl font-medium text-white' ); ?>
		<?php if ( $text ) : ?>
			<p class="pt-3 text-base text-gray-100"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
	</li>
	<?php
else :
	$principle = 'principle' === $style;
	$roomy     = ! empty( $args['roomy'] );
	$side      = ! empty( $args['side'] ) && ! $principle;
	// Light at rest; the group-hover colours are the dark card's (see the file header).
	$title_cls = ( ! empty( $args['large'] ) ? ( $side ? 'text-lg lg:text-h6' : 'text-h6' ) : 'text-lg' ) . ' font-medium text-gray-950 group-hover:text-white';
	$chip      = 'bg-[#F3F1FB] text-brand-700 group-hover:bg-white/7 group-hover:text-white';
	?>
	<li class="reveal feature-card feature-card--hover group relative isolate flex flex-col overflow-hidden rounded-[22px] border border-line bg-white shadow-xs hover:border-transparent<?php echo $link ? ' feature-card--lift' : ''; ?>" <?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<?php // The dark surface fades in behind the content: Figma's gradient for the principle card (49325:15598), black and textured for the others. ?>
		<span class="feature-card__dark pointer-events-none absolute inset-0 -z-10 opacity-0 group-hover:opacity-100 <?php echo $principle ? 'bg-[linear-gradient(163deg,#17132b_8%,#0b0b10_92%)]' : 'bg-gray-950'; ?>" aria-hidden="true">
			<?php if ( ! $principle ) : ?>
				<img class="size-full object-cover" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">
			<?php endif; ?>
		</span>
		<div class="flex flex-1 flex-col <?php echo esc_attr( $principle ? 'p-7 lg:min-h-[236px] lg:p-4' : ( $roomy ? 'p-[30px]' : ( $side ? 'min-h-[273px] p-[30px] lg:p-4' : 'min-h-[240px] p-4' ) ) ); ?>">
			<?php if ( $principle ) : ?>
				<?php // Phones: label, icon, then text (Figma 49374:44476). Desktop: icon left, label right. ?>
				<div class="flex flex-col items-start gap-[18px] lg:flex-row lg:justify-between lg:gap-4">
					<?php if ( $icon ) : ?>
						<span class="order-2 grid size-11 lg:order-none lg:mt-[18px] place-items-center rounded-[13px] <?php echo esc_attr( $chip ); ?>" aria-hidden="true"><?php upskill_icon( $icon, 'size-[21px]' ); ?></span>
					<?php endif; ?>
					<?php if ( $label ) : ?>
						<span class="order-1 text-sm font-medium text-[#8b8b97] uppercase lg:order-none"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
				</div>
				<div class="mt-auto flex flex-col gap-2 pt-3 lg:pt-6">
					<?php $title_markup( $title_cls ); ?>
					<?php if ( $text ) : ?>
						<p class="text-base text-gray-500 group-hover:text-white/66"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<?php if ( 'bar' === $icon ) : ?>
					<span class="h-[3px] w-[34px] rounded-full bg-[#6b2fe3]" aria-hidden="true"></span>
				<?php elseif ( $icon ) : ?>
					<span class="grid size-11 place-items-center rounded-[13px] <?php echo esc_attr( $chip ); ?>" aria-hidden="true"><?php upskill_icon( $icon, 'size-[21px]' ); ?></span>
				<?php endif; ?>
				<div class="flex flex-col <?php echo 'bar' === $icon ? 'pt-[22px]' : ( $icon ? ( $side ? 'pt-6 lg:pt-12.5' : ( $roomy ? 'pt-5 lg:pt-10' : 'pt-11' ) ) : '' ); ?>">
					<?php if ( ! empty( $args['framed'] ) && ! empty( $args['number'] ) ) : ?>
						<span class="order-first mb-6 grid size-8 place-items-center rounded-md border border-gray-200 text-sm text-gray-500 group-hover:border-white/40 group-hover:text-white" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
					<?php elseif ( ! empty( $args['number'] ) ) : ?>
						<?php // Purple when it heads a label (About "Why we exist"), grey when it heads the title; small and spaced in the side layout. ?>
						<span class="<?php echo $label ? 'text-[13px] font-semibold tracking-[0.1em] text-[#6b2fe3]' : ( $side ? 'text-xs font-semibold tracking-[0.1em] text-[#8b8b97]' : 'text-sm text-gray-500' ); ?>" aria-hidden="true"><?php echo esc_html( $number ); ?></span>
					<?php endif; ?>
					<?php if ( $label ) : ?>
						<span class="pt-2 text-sm font-medium text-gray-500 uppercase group-hover:text-white"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
					<?php $title_markup( ( $label ? 'pt-4 ' : ( ! empty( $args['number'] ) ? 'pt-2.5 ' : '' ) ) . $title_cls ); ?>
					<?php if ( $text ) : ?>
						<p class="<?php echo $side ? 'pt-2' : 'pt-2.5'; ?> text-base card-description text-gray-500 group-hover:text-gray-100"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>
					<?php if ( $points ) : ?>
						<ul class="flex list-disc flex-col gap-1 pt-3 pl-5 text-sm text-gray-700 marker:text-gray-500 group-hover:text-gray-100 group-hover:marker:text-white">
							<?php foreach ( $points as $point ) : ?>
								<li><?php echo esc_html( $point ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</li>
	<?php
endif;
