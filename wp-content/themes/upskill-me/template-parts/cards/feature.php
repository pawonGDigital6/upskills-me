<?php
/**
 * Feature card — the cards the Card Grid block draws.
 *
 * Styles, from the Figma frames:
 * - stacked:   icon (or accent bar), number, label, title, text. Why 49325:15613,
 *              About 49325:14680 / 14730 / 14758.
 * - principle: icon top left, label top right, title and text at the foot. Why 49325:15528.
 * - link:      the whole card links; chip, arrow and title. Resources 49325:16433.
 * - texture:   tall textured card in a dark, light or purple tone. Why 49325:15353.
 * - glass:     translucent card for dark sections. Case Studies 49325:18246.
 *
 * A card's tone ("dark") turns a light-surface card into the black textured one
 * Figma uses as the last card of a row.
 *
 * @param array $args {
 *     @type array  $card   icon, label, title, text, link, tone.
 *     @type int    $index  Position in the grid (0-based).
 *     @type string $style  stacked|principle|link|texture|glass.
 *     @type bool   $number Show the 01/02 number.
 *     @type bool   $large  24px titles instead of 18px.
 *     @type bool   $roomy  30px padding instead of 16px.
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
$stagger = 'style="--i:' . (int) min( $index, 5 ) . '"';

// The textured surface of the dark and textured cards: a background layer, so
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

if ( 'texture' === $style ) :
	$tones = array(
		'dark'  => array( 'bg-gray-950 text-white', 'texture-dark.webp', 'bg-gray-900', 'text-white/60' ),
		'light' => array( 'bg-gray-100 text-gray-950', 'texture-light.webp', 'bg-gray-200', 'text-gray-950' ),
		'brand' => array( 'bg-[#2f1a51] text-white', 'texture-brand.webp', 'bg-brand-900', 'text-brand-50' ),
	);
	$t     = isset( $tones[ $tone ] ) ? $tones[ $tone ] : $tones['light'];
	?>
	<li class="reveal feature-card feature-card--lift group relative isolate flex min-h-[340px] flex-col overflow-hidden rounded-card shadow-[0_1px_1px_rgb(10_13_18/5%)] lg:min-h-[366px] <?php echo esc_attr( $t[0] ); ?>" <?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<?php $texture( $t[1], 'opacity-20 transition-transform duration-700 ease-out group-hover:scale-105' ); ?>
		<div class="flex flex-1 flex-col p-4">
			<div class="flex items-center justify-between pt-10">
				<span class="grid size-10 place-items-center rounded-full <?php echo esc_attr( $t[2] ); ?>" aria-hidden="true"><?php upskill_icon( $icon, 'size-6' ); ?></span>
				<span class="text-sm font-medium uppercase" aria-hidden="true"><?php echo esc_html( $number ); ?></span>
			</div>
			<div class="mt-auto flex flex-col gap-1 pt-10">
				<?php $title_markup( 'text-h6 font-medium lg:pr-[34px] lg:text-h5' ); ?>
				<?php if ( $text ) : ?>
					<p class="text-base <?php echo esc_attr( $t[3] ); ?>"><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</li>
	<?php
elseif ( 'glass' === $style ) :
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
elseif ( 'link' === $style ) :
	?>
	<li class="reveal feature-card feature-card--lift group relative flex min-h-[323px] flex-col rounded-[22px] border border-brand-200 bg-white p-[30px] shadow-xs" <?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<div class="flex items-start justify-between gap-4">
			<?php if ( $label ) : ?>
				<span class="rounded-sm bg-brand-25 px-2.5 py-1 text-sm leading-[1.55] font-semibold tracking-[0.02em] text-brand-700 uppercase"><?php echo esc_html( $label ); ?></span>
			<?php endif; ?>
			<span class="icon-link ml-auto size-[60px] border-[1.5px] border-gray-900/20 text-gray-950 group-hover:border-brand-700 group-hover:bg-brand-700 group-hover:text-white" aria-hidden="true">
				<?php upskill_icon( 'arrow-up-right', 'size-[21px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
			</span>
		</div>
		<div class="mt-auto flex flex-col pt-[26px]">
			<span class="text-[13px] leading-[1.55] font-semibold tracking-[0.1em] text-brand-700" aria-hidden="true"><?php echo esc_html( $number ); ?></span>
			<?php if ( $label ) : ?>
				<span class="pt-1 text-[10.5px] leading-[1.55] font-semibold tracking-[0.16em] text-gray-500 uppercase" aria-hidden="true"><?php echo esc_html( $label ); ?></span>
			<?php endif; ?>
			<?php $title_markup( 'pt-3 text-xl font-medium text-gray-950' ); ?>
		</div>
	</li>
	<?php
else :
	$principle = 'principle' === $style;
	$roomy     = ! empty( $args['roomy'] );
	// The dark highlight card of a roomy grid takes the 20px title Figma gives it ("Managed Compliance").
	$title_cls = ( ! empty( $args['large'] ) ? 'text-h6' : ( $dark && $roomy ? 'text-xl' : 'text-lg' ) ) . ' font-medium ' . ( $dark ? 'text-white' : 'text-gray-950' );
	$chip      = $dark ? 'bg-white/7 text-white' : 'bg-brand-100 text-brand-700';
	// The principle card's dark tone is a plain gradient in Figma (49325:15598); the others are textured.
	$surface   = $dark ? ( $principle ? 'border-transparent bg-[linear-gradient(163deg,#17132b_8%,#0b0b10_92%)]' : 'border-transparent bg-gray-950' ) : 'border-line bg-white';
	?>
	<li class="reveal feature-card relative isolate flex flex-col overflow-hidden rounded-[22px] border shadow-xs <?php echo esc_attr( $surface . ( $link ? ' feature-card--lift group' : '' ) ); ?>" <?php echo $stagger; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above. ?>>
		<?php if ( $dark && ! $principle ) : ?>
			<?php $texture(); ?>
		<?php endif; ?>
		<div class="flex flex-1 flex-col <?php echo esc_attr( $principle ? 'p-6 lg:min-h-[236px] lg:p-4' : ( $roomy ? 'p-6 lg:p-[30px]' : 'min-h-[240px] p-4' ) ); ?>">
			<?php if ( $principle ) : ?>
				<?php // Phones: label, icon, then text (Figma 49374:44476). Desktop: icon left, label right. ?>
				<div class="flex flex-col items-start gap-3 lg:flex-row lg:justify-between lg:gap-4">
					<?php if ( $icon ) : ?>
						<span class="order-2 grid size-11 lg:order-none lg:mt-[18px] place-items-center rounded-[13px] <?php echo esc_attr( $chip ); ?>" aria-hidden="true"><?php upskill_icon( $icon, 'size-[21px]' ); ?></span>
					<?php endif; ?>
					<?php if ( $label ) : ?>
						<span class="order-1 text-sm font-medium text-[#8b8b97] uppercase lg:order-none"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
				</div>
				<div class="mt-auto flex flex-col gap-2 pt-4 lg:pt-6">
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
				<div class="flex flex-col <?php echo 'bar' === $icon ? 'pt-[22px]' : ( $icon ? 'pt-10' : '' ); ?>">
					<?php if ( ! empty( $args['number'] ) ) : ?>
						<?php // Purple when it heads a label (About "Why we exist"), grey when it heads the title. ?>
						<span class="<?php echo $label ? 'text-[13px] font-semibold tracking-[0.1em] text-[#6b2fe3]' : 'text-sm text-gray-500'; ?>" aria-hidden="true"><?php echo esc_html( $number ); ?></span>
					<?php endif; ?>
					<?php if ( $label ) : ?>
						<span class="pt-1 text-sm font-medium uppercase <?php echo $dark ? 'text-white' : 'text-gray-500'; ?>"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
					<?php $title_markup( ( $label ? 'pt-5 ' : ( ! empty( $args['number'] ) ? 'pt-2.5 ' : '' ) ) . $title_cls ); ?>
					<?php if ( $text ) : ?>
						<p class="pt-2.5 text-base <?php echo $dark ? 'text-gray-100' : 'text-gray-500'; ?>"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</li>
	<?php
endif;
