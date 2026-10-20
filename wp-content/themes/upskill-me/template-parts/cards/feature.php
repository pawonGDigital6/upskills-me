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
 * A card's tone ("dark") turns a light-surface card into the black textured one
 * Figma uses as the last card of a row.
 *
 * @param array $args {
 *     @type array  $card   icon, label, title, text, link, tone.
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
$tone   = ! empty( $card['tone'] ) ? $card['tone'] : 'light';
$dark   = 'light' !== $tone;
$number = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
$title  = isset( $card['title'] ) ? $card['title'] : '';
$text   = isset( $card['text'] ) ? $card['text'] : '';
$label  = isset( $card['label'] ) ? $card['label'] : '';
$icon   = isset( $card['icon'] ) ? $card['icon'] : '';
$link   = ! empty( $card['link']['url'] ) ? $card['link'] : null;
$points = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) ( $card['points'] ?? '' ) ) ) );
$stagger = 'style="--i:' . (int) min( $index, 5 ) . '"';

// The textured surface of the dark cards: a background layer, so
// the texture's own proportions never set the card's height.
$texture = static function ( $file = 'texture-dark.webp', $classes = '' ) {
	printf(
		'<img class="pointer-events-none absolute inset-0 -z-10 size-full object-cover %1$s" src="%2$s" alt="" loading="lazy" decoding="async">',
		esc_attr( $classes ),
		esc_url( upskill_theme_image( $file ) )
	);
};

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
	// The dark highlight card of a roomy grid takes the 20px title Figma gives it ("Managed Compliance").
	$title_cls = ( ! empty( $args['large'] ) ? ( $side ? 'text-lg lg:text-h6' : 'text-h6' ) : ( $dark && $roomy ? 'text-xl' : 'text-lg' ) ) . ' font-medium ' . ( $dark ? 'text-white' : 'text-gray-950' );
	$chip      = $dark ? 'bg-white/7 text-white' : 'bg-[#F3F1FB] text-brand-700';
	// The principle card's dark tone is a plain gradient in Figma (49325:15598); the others are textured.
	$surface   = $dark ? ( $principle ? 'border-transparent bg-[linear-gradient(163deg,#17132b_8%,#0b0b10_92%)]' : 'border-transparent bg-gray-950' ) : 'border-line bg-white';
	?>
	<li class="reveal feature-card relative isolate flex flex-col overflow-hidden rounded-[22px] border shadow-xs <?php echo esc_attr( $surface . ( $link ? ' feature-card--lift group' : '' ) ); ?>" <?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<?php if ( $dark && ! $principle ) : ?>
			<?php $texture(); ?>
		<?php endif; ?>
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
						<p class="text-base <?php echo $dark ? 'text-white/66' : 'text-gray-500'; ?>"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<?php if ( 'bar' === $icon ) : ?>
					<span class="h-[3px] w-[34px] rounded-full bg-[#6b2fe3]" aria-hidden="true"></span>
				<?php elseif ( $icon ) : ?>
					<span class="grid size-11 place-items-center rounded-[13px] <?php echo esc_attr( $chip ); ?>" aria-hidden="true"><?php upskill_icon( $icon, 'size-[21px]' ); ?></span>
				<?php endif; ?>
				<div class="flex flex-col <?php echo 'bar' === $icon ? 'pt-[22px]' : ( $icon ? ( $side ? 'pt-6 lg:pt-12.5' : ( $roomy && ! $dark ? 'pt-5 lg:pt-10' : 'pt-11' ) ) : '' ); ?>">
					<?php if ( ! empty( $args['framed'] ) && ! empty( $args['number'] ) ) : ?>
						<span class="order-first mb-6 grid size-8 place-items-center rounded-md border text-sm <?php echo $dark ? 'border-white/40 text-white' : 'border-gray-200 text-gray-500'; ?>" aria-hidden="true"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
					<?php elseif ( ! empty( $args['number'] ) ) : ?>
						<?php // Purple when it heads a label (About "Why we exist"), grey when it heads the title; small and spaced in the side layout. ?>
						<span class="<?php echo $label ? 'text-[13px] font-semibold tracking-[0.1em] text-[#6b2fe3]' : ( $side ? 'text-xs font-semibold tracking-[0.1em] text-[#8b8b97]' : 'text-sm text-gray-500' ); ?>" aria-hidden="true"><?php echo esc_html( $number ); ?></span>
					<?php endif; ?>
					<?php if ( $label ) : ?>
						<span class="pt-2 text-sm font-medium uppercase <?php echo $dark ? 'text-white' : 'text-gray-500'; ?>"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
					<?php $title_markup( ( $label ? 'pt-4 ' : ( ! empty( $args['number'] ) ? 'pt-2.5 ' : '' ) ) . $title_cls ); ?>
					<?php if ( $text ) : ?>
						<p class="<?php echo $side ? 'pt-2' : 'pt-2.5'; ?> text-base card-description <?php echo $dark ? 'text-gray-100' : 'text-gray-500'; ?>"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>
					<?php if ( $points ) : ?>
						<ul class="flex list-disc flex-col gap-1 pt-3 pl-5 text-sm <?php echo $dark ? 'text-gray-100 marker:text-white' : 'text-gray-700 marker:text-gray-500'; ?>">
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
