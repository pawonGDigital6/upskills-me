<?php
/**
 * Statement block.
 *
 * Figma: Project Case Study Detail 49325:16734 (overview), 49325:16761 (the
 * challenge) / 49374:107273.
 *
 * A centred eyebrow over one statement: the opening phrase dark, the rest
 * grey. The overview adds a button and a row of details (Industry, Focus,
 * Need) under a rule.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$lead    = get_field( 'lead' );
$text    = get_field( 'text' );
$details = array_filter( (array) get_field( 'details' ), static fn( $row ) => ! empty( $row['term'] ) && ! empty( $row['description'] ) );

if ( ! $lead && ! $text ) {
	return;
}

$surface = 'white' === get_field( 'surface' ) ? 'white' : 'pale';

// Pale (the overview) is 80 tall at desktop; white (the challenge) 108.
$attributes = upskill_block_attributes(
	$block,
	'block-statement py-[46px] ' . ( 'white' === $surface ? 'bg-white lg:py-[108px]' : 'bg-brand-25 lg:py-20' )
);
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<div class="mx-auto flex max-w-[800px] flex-col items-center text-center<?php echo 'white' === $surface ? ' px-6 sm:px-0' : ''; ?>">
			<?php upskill_eyebrow( get_field( 'eyebrow' ), get_field( 'eyebrow_icon' ), 'reveal' ); ?>
			<p class="reveal pt-[18px] font-['Geist'] text-xl leading-[1.55] tracking-[-0.005em] text-gray-500 lg:pt-[26px]" style="--i:1">
				<?php if ( $lead ) : ?>
					<span class="text-gray-950"><?php echo esc_html( $lead ); ?></span>
				<?php endif; ?>
				<?php echo esc_html( $text ); ?>
			</p>
			<?php
			upskill_button(
				get_field( 'button' ),
				array(
					'size'  => 'sm',
					'class' => 'reveal mt-[18px] lg:mt-6',
				)
			);
			?>
		</div>

		<?php if ( $details ) : ?>
			<?php // Two up then one on phones (Need wraps); three across from tablet. ?>
			<dl class="reveal mx-auto mt-8 grid max-w-[720px] grid-cols-2 gap-x-5 border-t border-gray-900/10 pt-8 text-left sm:grid-cols-3" style="--i:2">
				<?php foreach ( array_values( $details ) as $index => $row ) : ?>
					<div class="flex flex-col gap-1.5<?php echo 2 === $index ? ' col-span-2 sm:col-span-1' : ''; ?>">
						<dt class="text-sm font-medium text-[#6b6b72] uppercase"><?php echo esc_html( $row['term'] ); ?></dt>
						<dd class="text-base text-gray-950"><?php echo esc_html( $row['description'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</div>
</section>
