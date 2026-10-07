<?php
/**
 * Media Story block.
 *
 * Figma: About 49325:14636 + quote card 49325:14673 (1440) / 49374:47032 (390).
 *
 * A photograph with a quote card hanging over its lower edge, beside the
 * section header, story paragraphs and a short checklist.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$image     = get_field( 'image' );
$quote     = get_field( 'quote' );
$checklist = array_filter( (array) get_field( 'checklist' ), static fn( $item ) => ! empty( $item['title'] ) );

$attributes = upskill_block_attributes( $block, 'block-media-story section-y bg-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<div class="flex flex-col gap-10 lg:flex-row lg:items-center lg:gap-14">
			<?php if ( $image ) : ?>
				<?php // The quote card shares the photo's grid cell and hangs 36px below it (the wrapper's padding). ?>
				<div class="grid grid-cols-1 grid-rows-1 lg:min-w-0 lg:flex-1 lg:self-stretch <?php echo ! empty( $quote['text'] ) ? 'pb-9' : ''; ?>">
					<?php
					upskill_image(
						$image,
						'full',
						array(
							'class' => 'col-start-1 row-start-1 aspect-[358/360] size-full rounded-[24px] object-cover shadow-xs sm:aspect-[4/3] lg:aspect-auto lg:min-h-[560px]',
							'sizes' => '(min-width: 1025px) 46vw, 92vw',
						)
					);
					?>
					<?php if ( ! empty( $quote['text'] ) ) : ?>
						<div class="reveal relative col-start-1 row-start-1 mx-3 -mb-9 self-end rounded-[22px] border border-line bg-white px-5 py-5 shadow-xs lg:mr-auto lg:ml-[55px] lg:max-w-[536px] lg:px-7 lg:py-[26px]">
							<span class="block h-6 text-[30px] leading-[30px] font-semibold text-brand-500" aria-hidden="true">&ldquo;</span>
							<blockquote class="pt-1.5 text-base font-medium text-gray-950"><?php echo esc_html( $quote['text'] ); ?></blockquote>
							<?php if ( ! empty( $quote['caption'] ) ) : ?>
								<p class="pt-3 text-sm text-gray-500"><?php echo esc_html( $quote['caption'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="flex flex-col gap-[18px] lg:min-w-0 lg:flex-1">
				<?php
				upskill_section_header(
					array(
						'layout' => 'stack',
						'button' => false,
					'reveal' => true,
						'class'  => 'block-media-story__header',
					)
				);
				?>

				<?php if ( $checklist ) : ?>
					<ul class="flex flex-col gap-3 pt-2.5">
						<?php foreach ( $checklist as $item ) : ?>
							<li class="flex items-start gap-3.5">
								<span class="mt-0.5 grid size-6 flex-none place-items-center rounded-full bg-brand-100 text-[#6b2fe3]" aria-hidden="true"><?php upskill_icon( 'check', 'size-3' ); ?></span>
								<p class="text-base text-[#55555f]">
									<strong class="font-medium text-[#0e0e14]"><?php echo esc_html( $item['title'] ); ?></strong>
									<?php if ( ! empty( $item['text'] ) ) : ?>
										<?php echo esc_html( ' - ' . $item['text'] ); ?>
									<?php endif; ?>
								</p>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
