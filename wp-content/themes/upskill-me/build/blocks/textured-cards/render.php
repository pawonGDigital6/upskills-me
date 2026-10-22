<?php
/**
 * Textured Cards block.
 *
 * Figma: Why UpSkill Me 49325:15353 (1440) / 49372:42236 (390); the industry,
 * category and level pages' "Why … training matters".
 *
 * A centred section header (left-aligned below desktop, as the phone frame
 * draws it) over three tall textured cards. Each card picks its tone: dark,
 * light or purple.
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

$tones = array(
	'dark'  => array( 'bg-gray-950 text-white', 'texture-dark.webp', 'bg-gray-900', 'text-white/60' ),
	'light' => array( 'bg-gray-100 text-gray-950', 'texture-light.webp', 'bg-gray-200', 'text-gray-950' ),
	'brand' => array( 'bg-[#2f1a51] text-white', 'texture-brand.webp', 'bg-brand-900', 'text-brand-50' ),
);

$attributes = upskill_block_attributes( $block, 'block-textured-cards section-y bg-white' );
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
			<ul class="textured-cards__list mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:items-end">
				<?php foreach ( $cards as $index => $card ) : ?>
					<?php
					$t    = isset( $tones[ $card['tone'] ] ) ? $tones[ $card['tone'] ] : $tones['light'];
					$link = ! empty( $card['link']['url'] ) ? $card['link'] : null;
					?>
					<li class="textured-cards__item reveal textured-card group relative isolate flex min-h-[366px] flex-col overflow-hidden rounded-card shadow-[0_1px_1px_rgb(10_13_18/5%)] <?php echo esc_attr( $t[0] ); ?>" style="--i:<?php echo (int) min( $index, 5 ); ?>">
						<?php // The texture is a background layer, so its own proportions never set the card's height. ?>
						<img class="textured-cards__texture pointer-events-none absolute inset-0 -z-10 size-full object-cover opacity-20 transition-transform duration-700 ease-out group-hover:scale-105" src="<?php echo esc_url( upskill_theme_image( $t[1] ) ); ?>" alt="" loading="lazy" decoding="async">
						<div class="textured-cards__body flex flex-1 flex-col p-4">
							<div class="textured-cards__head flex items-center justify-between pt-10">
								<span class="textured-cards__icon grid size-10 place-items-center rounded-full <?php echo esc_attr( $t[2] ); ?>" aria-hidden="true"><?php upskill_icon( $card['icon'], 'size-6' ); ?></span>
								<span class="textured-cards__number text-sm font-medium uppercase" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							</div>
							<div class="textured-cards__content mt-auto flex flex-col gap-1 pt-10">
								<h3 class="textured-cards__title text-h6 font-medium lg:pr-[34px] lg:text-h5">
									<?php if ( $link ) : ?>
										<?php // A stretched pseudo-element makes the whole card the link target. ?>
										<a class="textured-cards__link after:absolute after:inset-0 after:z-10 after:rounded-[inherit] focus-visible:outline-none"<?php echo upskill_link_attributes( $link ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>><?php echo esc_html( $card['title'] ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $card['title'] ); ?>
									<?php endif; ?>
								</h3>
								<?php if ( ! empty( $card['text'] ) ) : ?>
									<p class="textured-cards__text text-base <?php echo esc_attr( $t[3] ); ?>"><?php echo esc_html( $card['text'] ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
