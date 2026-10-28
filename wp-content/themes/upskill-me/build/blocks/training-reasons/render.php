<?php
/**
 * Training Reasons block.
 *
 * Figma: Manufacturing "Why manufacturing training matters" 49325:14854 (1440)
 * / 49354:95612 (390); the industry, category and level pages.
 *
 * A centred section header (left-aligned on phones) over three textured cards:
 * an icon, a numbered label ("01 - Practical"), then the title and text. Light
 * cards lie on a soft white texture, purple ones on the purple texture or, when
 * the card has a photo, on that photo; a card with a photo is the wide one on
 * desktop (Figma 354 / 620 / 354). Unlike Textured Cards (Why page), the
 * number goes with the label rather than on the right.
 *
 * Titles accept <br> and <em> for a forced line break.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$cards = array_values(
	array_filter(
		(array) get_field( 'cards' ),
		static function ( $card ) {
			return ! empty( $card['title'] );
		}
	)
);

// Surface, texture, icon disc, title, text — Figma light #e6e6e6 + #ccc stroke, purple #2f1a51; textures at 20%.
$tones = array(
	'light' => array( 'border border-gray-200 bg-gray-100 text-gray-950', 'texture-outline.webp', 'bg-gray-25 text-gray-950', 'text-[#1a1a1a]' ),
	'brand' => array( 'bg-[#2f1a51] text-white', 'texture-brand.webp', 'bg-brand-900 text-white', 'text-brand-50' ),
);

$columns    = array();
$attributes = upskill_block_attributes( $block, 'block-training-reasons section-y bg-white' );

foreach ( $cards as $card ) {
	$columns[] = empty( $card['image'] ) ? '354fr' : '620fr';
}
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'center',
				'reveal' => true,
				'class'  => 'section-header--left-phones',
			)
		);
		?>

		<?php if ( $cards ) : ?>
			<?php // Figma: the cards touch on phones, sit 16 apart from tablets; desktop columns follow the cards (a photo card is 620 wide, the rest 354). ?>
			<ul class="training-reasons__list mt-6 grid sm:grid-cols-2 sm:gap-4 lg:mt-[62px] lg:grid-cols-(--columns)" style="--columns:<?php echo esc_attr( implode( ' ', $columns ) ); ?>">
				<?php foreach ( $cards as $index => $card ) : ?>
					<?php
					$t     = isset( $tones[ $card['tone'] ] ) ? $tones[ $card['tone'] ] : $tones['light'];
					$link  = ! empty( $card['link']['url'] ) ? $card['link'] : null;
					$photo = ! empty( $card['image'] ) ? (int) $card['image'] : 0;
					$label = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) . ( empty( $card['label'] ) ? '' : ' - ' . $card['label'] );
					?>
					<li class="training-reasons__item reveal group relative isolate grid grid-cols-1 grid-rows-1 overflow-hidden rounded-[32px] shadow-xs *:col-start-1 *:row-start-1 <?php echo esc_attr( $t[0] . ( $photo ? ' sm:col-span-2 lg:col-span-1' : '' ) ); ?>" style="--i:<?php echo (int) min( $index, 5 ); ?>">
						<?php if ( $photo ) : ?>
							<?php // Drawn by height on phones (334 × 1.345), by width from tablets. ?>
							<?php upskill_image( $photo, 'full', array( 'class' => 'training-reasons__texture pointer-events-none h-0 min-h-full w-full object-cover opacity-20 transition-transform duration-700 ease-out group-hover:scale-105', 'alt' => '', 'sizes' => '(min-width: 1025px) 620px, (min-width: 576px) calc(100vw - 32px), 450px' ) ); ?>
						<?php else : ?>
							<img class="training-reasons__texture pointer-events-none h-0 min-h-full w-full object-cover opacity-20 transition-transform duration-700 ease-out group-hover:scale-105" src="<?php echo esc_url( upskill_theme_image( $t[1] ) ); ?>" alt="" loading="lazy" decoding="async">
						<?php endif; ?>

						<?php // The texture/photo layer has no height of its own (h-0 min-h-full), so the copy sizes the card. The copy block is 174 tall, so on the 461-tall desktop row every title starts 270 down. ?>
						<div class="training-reasons__body relative flex flex-col p-4 lg:min-h-[461px]">
							<div class="training-reasons__head flex flex-col items-start gap-2">
								<?php if ( ! empty( $card['icon'] ) ) : ?>
									<span class="training-reasons__icon grid size-10 place-items-center rounded-full <?php echo esc_attr( $t[2] ); ?>" aria-hidden="true"><?php upskill_icon( $card['icon'], 'size-6' ); ?></span>
								<?php endif; ?>
								<p class="training-reasons__label text-base"><?php echo esc_html( $label ); ?></p>
							</div>
							<div class="training-reasons__content mt-14 flex min-h-[174px] flex-col gap-1 lg:mt-auto">
								<h3 class="training-reasons__title text-[2.25rem] leading-[1.1] font-medium tracking-[-0.01em]<?php echo $photo ? ' pr-[34px]' : ''; ?>">
									<?php if ( $link ) : ?>
										<?php // A stretched pseudo-element makes the whole card the link target. ?>
										<a class="training-reasons__link after:absolute after:inset-0 after:z-10 after:rounded-[inherit] focus-visible:outline-none"<?php echo upskill_link_attributes( $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>><?php echo wp_kses( $card['title'], upskill_inline_kses() ); ?></a>
									<?php else : ?>
										<?php echo wp_kses( $card['title'], upskill_inline_kses() ); ?>
									<?php endif; ?>
								</h3>
								<?php if ( ! empty( $card['text'] ) ) : ?>
									<p class="training-reasons__text text-base <?php echo esc_attr( $t[3] . ( $photo ? ' lg:max-w-[472px]' : '' ) ); ?>"><?php echo esc_html( $card['text'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
