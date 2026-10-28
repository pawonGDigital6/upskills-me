<?php
/**
 * Principles block.
 *
 * Figma: Why UpSkill Me "Compliance & quality" 49325:15528 (1440) /
 * 49374:44476 (390).
 *
 * The section header and a side note in the left column; four principle cards
 * two-up on the right (icon and label at the top, title and text at the foot).
 * Each card takes Figma's dark gradient on hover
 * (src/global/scss/components/_feature-card.scss).
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$principles = array_values(
	array_filter(
		(array) get_field( 'principles' ),
		static function ( $principle ) {
			return ! empty( $principle['title'] );
		}
	)
);

$note       = (array) get_field( 'note' );
$attributes = upskill_block_attributes( $block, 'block-principles section-y bg-brand-50' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php // Columns 88px apart at 1440, the cards 48px down. ?>
		<div class="principles__layout flex flex-col lg:gap-10 gap-4 lg:flex-row lg:items-center">
			<div class="principles__aside flex flex-col gap-12 lg:min-w-0 lg:flex-1 lg:gap-10 lg:self-stretch">
				<?php
				upskill_section_header(
					array(
						'layout' => 'stack',
						'button' => false,
						'reveal' => true,
						'class'  => 'section-header--base',
					)
				);
				?>

				<?php if ( ! empty( $note['title'] ) ) : ?>
					<div class="principles__note reveal flex items-start gap-4 rounded-[20px] border border-line bg-white p-6 lg:max-w-[507px]" style="--i:2">
						<span class="principles__note-icon grid size-11 flex-none place-items-center rounded-[13px] bg-brand-100 text-brand-700" aria-hidden="true"><?php upskill_icon( empty( $note['icon'] ) ? 'info' : $note['icon'], 'size-5' ); ?></span>
						<div class="principles__note-body flex flex-col gap-2">
							<p class="principles__note-title text-lg font-medium text-gray-950"><?php echo esc_html( $note['title'] ); ?></p>
							<?php if ( ! empty( $note['text'] ) ) : ?>
								<p class="principles__note-text text-base text-gray-500"><?php echo esc_html( $note['text'] ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $principles ) : ?>
				<div class="principles__cards lg:min-w-0 lg:flex-1 lg:self-stretch lg:pt-12">
					<ul class="principles__list grid gap-4 sm:grid-cols-2">
						<?php foreach ( $principles as $index => $principle ) : ?>
							<li class="principles__item feature-card reveal group relative isolate flex flex-col overflow-hidden rounded-[22px] border border-line bg-white shadow-xs hover:border-transparent" style="--i:<?php echo (int) min( $index, 5 ); ?>">
								<span class="principles__surface pointer-events-none absolute inset-0 -z-10 bg-[linear-gradient(163deg,#17132b_8%,#0b0b10_92%)] opacity-0 group-hover:opacity-100" aria-hidden="true"></span>
								<div class="principles__body flex flex-1 flex-col p-7 lg:min-h-[236px] lg:p-4 lg:pt-8">
									<?php // Phones: label, icon, then text (Figma 49374:44476). Desktop: icon left, label right. ?>
									<div class="principles__head flex flex-col items-start gap-[18px] lg:flex-row lg:justify-between lg:gap-4">
										<?php if ( ! empty( $principle['icon'] ) ) : ?>
											<span class="principles__icon order-2 grid size-11 place-items-center rounded-[13px] bg-[#F3F1FB] text-brand-700 group-hover:bg-white/7 group-hover:text-white lg:order-none" aria-hidden="true"><?php upskill_icon( $principle['icon'], 'size-[21px]' ); ?></span>
										<?php endif; ?>
										<?php if ( ! empty( $principle['label'] ) ) : ?>
											<span class="principles__label order-1 text-sm font-medium text-[#8b8b97] uppercase lg:order-none mt-1"><?php echo esc_html( $principle['label'] ); ?></span>
										<?php endif; ?>
									</div>
									<div class="principles__content mt-auto flex flex-col gap-2 pt-3 lg:pt-6">
										<h3 class="principles__title text-lg font-medium text-gray-950 group-hover:text-white"><?php echo esc_html( $principle['title'] ); ?></h3>
										<?php if ( ! empty( $principle['text'] ) ) : ?>
											<p class="principles__text text-base text-gray-500 group-hover:text-white/66"><?php echo esc_html( $principle['text'] ); ?></p>
										<?php endif; ?>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
