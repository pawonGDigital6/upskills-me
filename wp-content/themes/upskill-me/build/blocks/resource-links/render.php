<?php
/**
 * Resource Links block.
 *
 * Figma: Resources 49325:16433 (1440) / 49374:101476 (390).
 *
 * A split section header over a row of numbered link cards: the whole card is
 * the link, with its label chip, arrow and title.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$cards = array_values(
	array_filter(
		(array) get_field( 'cards' ),
		static function ( $card ) {
			return ! empty( $card['title'] ) && ! empty( $card['link']['url'] );
		}
	)
);

$attributes = upskill_block_attributes( $block, 'block-resource-links section-y bg-brand-25' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'split',
				'reveal' => true,
			)
		);
		?>

		<?php if ( $cards ) : ?>
			<ul class="resource-links__list mt-stack grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
				<?php foreach ( $cards as $index => $card ) : ?>
					<li class="resource-links__item reveal resource-link group relative flex min-h-[323px] flex-col rounded-[22px] border border-brand-200 bg-white p-[30px] shadow-xs" style="--i:<?php echo (int) min( $index, 5 ); ?>">
						<div class="resource-links__head flex items-start justify-between gap-4">
							<?php if ( ! empty( $card['label'] ) ) : ?>
								<span class="resource-links__chip rounded-sm bg-brand-25 px-2.5 py-1 text-sm leading-[1.55] font-semibold tracking-[0.02em] text-brand-700 uppercase"><?php echo esc_html( $card['label'] ); ?></span>
							<?php endif; ?>
							<span class="resource-links__arrow icon-link ml-auto size-[60px] border-[1.5px] border-gray-900/20 text-gray-950 group-hover:border-brand-700 group-hover:bg-brand-700 group-hover:text-white" aria-hidden="true">
								<?php upskill_icon( 'arrow-up-right', 'size-[21px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
							</span>
						</div>
						<div class="resource-links__body mt-auto flex flex-col pt-[26px]">
							<span class="resource-links__number text-[13px] leading-[1.55] font-semibold tracking-[0.1em] text-brand-700" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<?php if ( ! empty( $card['label'] ) ) : ?>
								<span class="resource-links__label pt-1 text-[10.5px] leading-[1.55] font-semibold tracking-[0.16em] text-gray-500 uppercase" aria-hidden="true"><?php echo esc_html( $card['label'] ); ?></span>
							<?php endif; ?>
							<?php // A stretched pseudo-element makes the whole card the link target. ?>
							<h3 class="resource-links__title pt-3 text-xl font-medium text-gray-950"><a class="resource-links__link after:absolute after:inset-0 after:z-10 after:rounded-[inherit] focus-visible:outline-none"<?php echo upskill_link_attributes( $card['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>><?php echo esc_html( $card['title'] ); ?></a></h3>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
