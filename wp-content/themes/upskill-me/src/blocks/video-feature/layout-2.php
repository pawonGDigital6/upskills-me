<?php
/**
 * Video Feature block — Layout 2 (course page, "The people behind the training").
 *
 * Figma: 49325:21289 (1440) / 49371:40935 (390).
 *
 * The header runs across the top (heading left, intro right from lg), then
 * the video sits beside a single column of feature cards (icon beside the
 * text). Phones and tablets stack header, video, then cards. Facts and the
 * video caption are not part of this design and are not shown.
 *
 * Included from render.php, which provides $block, $features, $video, $poster
 * and $title.
 *
 * @package upskill-me
 */

$attributes = upskill_block_attributes( $block, 'block-video-feature block-video-feature--layout-2 is-dark relative isolate section-y bg-[#0e0e10] text-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<img class="absolute inset-0 -z-10 size-full object-cover opacity-20" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">

	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'split',
				'dark'   => true,
				'button' => false,
				'reveal' => true,
				'class'  => 'block-video-feature__header-split max-lg:gap-6',
			)
		);
		?>

		<?php // Figma: video 713 and cards 591 of 1360, 56 apart; 54 under the header (32 on phones). ?>
		<div class="grid gap-6 pt-8 lg:grid-cols-[713fr_591fr] lg:gap-x-14 lg:pt-[54px]">
			<?php if ( $poster || ! empty( $video['url'] ) ) : ?>
				<?php // Poster and play button share one grid cell; the video replaces them once started (view.js). ?>
				<div class="reveal video-card block-video-feature__video grid aspect-[356/240] grid-cols-1 grid-rows-1 overflow-hidden rounded-[24px] border border-white/10 bg-[linear-gradient(157deg,#241946_8%,#14102a_54%,#0e0c1a_92%)] sm:aspect-[713/476] lg:min-w-0 lg:self-start" data-video-card>
					<?php
					upskill_image(
						$poster,
						'full',
						array(
							'class' => 'col-start-1 row-start-1 size-full object-cover object-left opacity-20',
							'alt'   => '',
							'sizes' => '(min-width: 1025px) 50vw, 92vw',
						)
					);
					?>
					<?php if ( ! empty( $video['url'] ) ) : ?>
						<button class="video-card__play relative col-start-1 row-start-1 grid size-[76px] place-items-center self-center justify-self-center rounded-full border border-white/24 bg-white/14 text-white transition-transform duration-300 hover:scale-105" type="button" data-video-play data-src="<?php echo esc_url( $video['url'] ); ?>">
							<span class="screen-reader-text">
								<?php
								/* translators: %s: video title. */
								printf( esc_html__( 'Play video: %s', 'upskill-me' ), esc_html( $title ) );
								?>
							</span>
							<?php upskill_icon( 'play', 'size-6' ); ?>
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $features ) : ?>
				<ul class="block-video-feature__list grid content-start gap-3 lg:min-w-0">
					<?php foreach ( $features as $index => $feature ) : ?>
						<li class="reveal block-video-feature__item flex items-start gap-[18px] rounded-[20px] border border-white/10 bg-white/4 p-4 sm:p-6" style="--i:<?php echo (int) $index; ?>">
							<?php if ( ! empty( $feature['icon'] ) ) : ?>
								<span class="block-video-feature__item-icon grid size-11 shrink-0 place-items-center rounded-[13px] bg-white/7 text-[#b79bff]" aria-hidden="true"><?php upskill_icon( $feature['icon'], 'size-5' ); ?></span>
							<?php endif; ?>
							<div class="block-video-feature__item-body min-w-0">
								<h3 class="text-lg font-medium text-white"><?php echo esc_html( $feature['title'] ); ?></h3>
								<?php if ( ! empty( $feature['text'] ) ) : ?>
									<p class="pt-2 text-base text-gray-100"><?php echo esc_html( $feature['text'] ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
