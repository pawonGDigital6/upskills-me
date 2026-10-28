<?php
/**
 * Hub Hero block.
 *
 * Figma: Industry Hub 49325:14554 (390: 49354:95609), Categories 49325:14596.
 * Centred eyebrow, headline, intro and button with a compact tag either side
 * (count, title, icon and a photo tile; shown from 1200, where they clear the
 * heading) and three photo cards underneath (354 / 620 / 354 at 1440).
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$tags  = array_slice( array_values( array_filter( (array) get_field( 'tags' ), static fn( $tag ) => ! empty( $tag['title'] ) ) ), 0, 2 );
$cards = array_slice( array_values( array_filter( (array) get_field( 'cards' ), static fn( $card ) => ! empty( $card['image'] ) ) ), 0, 3 );

// Inner pages leave 56px under the header (Figma frames start every inner hero
// at y 176); the 390 frame has 46px above the copy and the cards end the section on desktop.
$attributes = upskill_block_attributes( $block, 'block-hub-hero overflow-hidden bg-gray-25 pt-[46px] pb-[46px] lg:pt-14 ' . ( $cards ? 'lg:pb-0' : 'lg:pb-20' ) );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php // The tags share the copy's grid cell so they float beside it without leaving the flow. ?>
		<div class="hub-hero__intro grid grid-cols-1 grid-rows-1">
			<div class="hub-hero__copy col-start-1 row-start-1 flex flex-col items-start gap-6 lg:mx-auto lg:max-w-[792px] lg:items-center lg:text-center">
				<div class="flex flex-col items-start gap-3 sm:gap-5 lg:items-center">
					<?php upskill_eyebrow( get_field( 'eyebrow' ), (string) get_field( 'eyebrow_icon' ) ); ?>
					<?php upskill_heading( get_field( 'heading' ), 'h1', 'hub-hero__heading text-h1 font-medium text-gray-900 lg:font-semibold lg:tracking-[-0.01em]' ); ?>
				</div>
				<?php if ( get_field( 'intro' ) ) : ?>
					<p class="hub-hero__text reveal max-w-[666px] text-base text-gray-500" style="--i:1"><?php echo esc_html( get_field( 'intro' ) ); ?></p>
				<?php endif; ?>
				<?php upskill_button( get_field( 'button' ) ); ?>
			</div>

			<?php foreach ( $tags as $index => $tag ) : ?>
				<?php
				// First tag left at y 159 with the tile 96px in; second right at y 142 with the tile under its start.
				$right = 1 === $index;
				$icon  = ! empty( $tag['icon'] ) ? $tag['icon'] : ( $right ? 'check' : 'play' );
				?>
				<div class="hub-hero__tag reveal col-start-1 row-start-1 hidden flex-col items-start gap-3 self-start wd:flex <?php echo $right ? 'mt-[142px] justify-self-end' : 'mt-[159px] justify-self-start'; ?>" style="--i:<?php echo $right ? 2 : 1; ?>">
					<p class="hub-hero__tag-label flex items-center gap-[7px] rounded-[8px] border border-line bg-white py-2 pr-3.5 pl-2.5 text-xs leading-[1.62] tracking-[-0.1px] shadow-[0_4px_14px_-6px_rgb(47_26_81/10%),0_1px_2px_0_rgb(26_26_26/4%)]">
						<?php if ( ! $right ) : ?>
							<?php upskill_icon( $icon, 'size-[15px] flex-none text-[#6744a8]' ); ?>
						<?php endif; ?>
						<?php if ( ! empty( $tag['count'] ) ) : ?>
							<strong class="font-semibold text-gray-900"><?php echo esc_html( $tag['count'] ); ?></strong>
						<?php endif; ?>
						<span class="font-secondary text-[#55555a]"><?php echo esc_html( $tag['title'] ); ?></span>
						<?php if ( $right ) : ?>
							<?php upskill_icon( $icon, 'size-[15px] flex-none text-[#6744a8]' ); ?>
						<?php endif; ?>
					</p>
					<?php if ( ! empty( $tag['photo'] ) ) : ?>
						<?php upskill_image( $tag['photo'], 'full', array( 'class' => 'hub-hero__tag-photo size-[54px] rounded-[16px] border-[3px] border-white object-cover shadow-[0_14px_34px_-10px_rgb(47_26_81/14%),0_2px_4px_0_rgb(26_26_26/3%)] ' . ( $right ? '' : 'ml-24' ), 'alt' => '', 'loading' => 'eager', 'sizes' => '48px' ) ); ?>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $cards ) : ?>
			<?php
			// 62px under the button at 1440. Phones: the wide (middle) card first and
			// full width, the other two side by side underneath.
			?>
			<ul class="hub-hero__cards mt-[46px] grid grid-cols-2 gap-4 sm:grid-cols-[354fr_620fr_354fr] lg:mt-[62px]" role="list">
				<?php foreach ( $cards as $index => $card ) : ?>
					<?php $wide = 1 === $index; ?>
					<li class="hub-hero__card reveal grid grid-cols-1 grid-rows-1 overflow-hidden rounded-[17px] bg-gray-950 *:col-start-1 *:row-start-1 sm:rounded-[20px] <?php echo $wide ? 'order-first col-span-2 aspect-[358/418] sm:order-none sm:col-span-1 sm:aspect-[620/590]' : 'aspect-[170/200] sm:aspect-[354/590]'; ?>" style="--i:<?php echo (int) $index + 2; ?>">
						<?php
						upskill_image(
							$card['image'],
							'full',
							array(
								'class'   => 'hub-hero__card-image size-full object-cover',
								'loading' => 'eager',
								// Drawn at 620 / 354 of 1360 (cover in a taller box draws them wider on phones).
								'sizes'   => $wide ? '(min-width: 1440px) 620px, (min-width: 768px) 46vw, 100vw' : '(min-width: 1440px) 354px, (min-width: 768px) 26vw, 50vw',
							)
						);
						?>
						<div class="bg-[linear-gradient(180deg,rgb(0_0_0/0%)_50%,rgb(0_0_0/80%)_100%)]" aria-hidden="true"></div>
						<?php if ( ! empty( $card['title'] ) || ! empty( $card['text'] ) ) : ?>
							<div class="flex flex-col justify-end">
								<p class="hub-hero__caption flex flex-col gap-2 border-t border-white/15 bg-white/10 px-3 sm:py-4 py-3 text-white sm:backdrop-blur-md backdrop-blur-[4px] sm:gap-1 sm:pr-0 sm:pl-6">
									<?php if ( ! empty( $card['title'] ) ) : ?>
										<span class="hub-hero__card-title text-[clamp(1.5rem,1.2214rem+1.1429vw,2.25rem)] leading-[1.1] font-medium tracking-[-0.01em]"><?php echo esc_html( $card['title'] ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $card['text'] ) ) : ?>
										<span class="hub-hero__card-text text-sm"><?php echo esc_html( $card['text'] ); ?></span>
									<?php endif; ?>
								</p>
							</div>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
