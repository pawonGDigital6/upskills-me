<?php
/**
 * Video Feature block.
 *
 * Figma: Why UpSkill Me 49325:15427 (1440) / 49374:44290 (390).
 *
 * A dark section: header and two-up feature cards on the left, a video card
 * and three short facts on the right. The video starts on demand (view.js);
 * until then only its poster is loaded.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$features = array_values( array_filter( (array) get_field( 'features' ), static fn( $item ) => ! empty( $item['title'] ) ) );
$facts    = array_values( array_filter( (array) get_field( 'facts' ), static fn( $item ) => ! empty( $item['title'] ) ) );
$video    = get_field( 'video' );
$poster   = get_field( 'poster' );
$title    = get_field( 'video_title' );

$attributes = upskill_block_attributes( $block, 'block-video-feature is-dark relative isolate section-y bg-[#0e0e10] text-white' );
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<img class="absolute inset-0 -z-10 size-full object-cover opacity-20" src="<?php echo esc_url( upskill_theme_image( 'texture-dark.webp' ) ); ?>" alt="" loading="lazy" decoding="async">

	<div class="container">
		<?php // One grid: header and features share the left column, the video spans the right. Phones read header, video, facts, then features (Figma 49374:44290). ?>
		<div class="grid gap-10 lg:grid-cols-2 lg:gap-x-14">
			<div class="lg:col-start-1 lg:row-start-1">
				<?php
				upskill_section_header(
					array(
						'layout' => 'stack',
						'dark'   => true,
						'button' => false,
						'class'  => 'section-header--base block-video-feature__header',
					)
				);
				?>
			</div>

			<?php if ( $poster || ! empty( $video['url'] ) ) : ?>
				<div class="flex flex-col gap-6 lg:col-start-2 lg:row-span-2 lg:row-start-1 lg:min-w-0">
					<?php // Poster, play button and caption share one grid cell; the video replaces them once started. ?>
					<div class="video-card grid aspect-[358/256] grid-cols-1 grid-rows-1 overflow-hidden rounded-[24px] border border-white/10 bg-[linear-gradient(157deg,#241946_8%,#14102a_54%,#0e0c1a_92%)] sm:aspect-[652/476]" data-video-card>
						<?php
						upskill_image(
							$poster,
							'full',
							array(
								'class' => 'col-start-1 row-start-1 size-full object-cover object-left opacity-20',
								'alt'   => '',
								'sizes' => '(min-width: 1025px) 46vw, 92vw',
							)
						);
						?>
						<?php if ( ! empty( $video['url'] ) ) : ?>
							<button class="video-card__play col-start-1 row-start-1 grid size-[76px] place-items-center self-center justify-self-center rounded-full border border-white/24 bg-white/14 text-white transition-transform duration-300 hover:scale-105" type="button" data-video-play data-src="<?php echo esc_url( $video['url'] ); ?>">
								<span class="screen-reader-text">
									<?php
									/* translators: %s: video title. */
									printf( esc_html__( 'Play video: %s', 'upskill-me' ), esc_html( $title ) );
									?>
								</span>
								<?php upskill_icon( 'play', 'size-6' ); ?>
							</button>
						<?php endif; ?>
						<div class="video-card__caption pointer-events-none col-start-1 row-start-1 hidden flex-col items-start self-end bg-[linear-gradient(180deg,rgb(11_11_16/0%),rgb(11_11_16/86%)_70%)] p-6 sm:flex">
							<?php if ( get_field( 'video_tag' ) ) : ?>
								<span class="rounded-sm bg-white/12 px-2.5 py-1 text-2xs font-medium text-white/80"><?php echo esc_html( get_field( 'video_tag' ) ); ?></span>
							<?php endif; ?>
							<?php if ( $title ) : ?>
								<p class="pt-3 text-h6 font-medium text-white"><?php echo esc_html( $title ); ?></p>
							<?php endif; ?>
							<?php if ( get_field( 'video_meta' ) ) : ?>
								<p class="pt-1.5 text-sm text-white/55"><?php echo esc_html( get_field( 'video_meta' ) ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<?php if ( $facts ) : ?>
						<ul class="grid grid-cols-2 gap-3.5 sm:grid-cols-3">
							<?php foreach ( $facts as $index => $fact ) : ?>
								<li class="reveal rounded-2xl border border-white/10 bg-white/4 p-4 last:odd:col-span-2 sm:last:odd:col-span-1" style="--i:<?php echo (int) $index; ?>">
									<p class="text-base font-medium text-white"><?php echo esc_html( $fact['title'] ); ?></p>
									<?php if ( ! empty( $fact['text'] ) ) : ?>
										<p class="pt-1 text-base text-gray-100"><?php echo esc_html( $fact['text'] ); ?></p>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $features ) : ?>
				<ul class="grid gap-4 sm:grid-cols-2 lg:col-start-1 lg:row-start-2 lg:self-start">
					<?php foreach ( $features as $index => $feature ) : ?>
						<li class="reveal flex flex-col rounded-[18px] border border-white/10 bg-white/4 p-[22px] lg:min-h-[203px]" style="--i:<?php echo (int) $index; ?>">
							<?php if ( ! empty( $feature['icon'] ) ) : ?>
								<span class="grid size-[38px] place-items-center rounded-[11px] bg-white/7 text-brand-300" aria-hidden="true"><?php upskill_icon( $feature['icon'], 'size-[19px]' ); ?></span>
							<?php endif; ?>
							<h3 class="pt-4 text-lg font-medium text-white"><?php echo esc_html( $feature['title'] ); ?></h3>
							<?php if ( ! empty( $feature['text'] ) ) : ?>
								<p class="pt-2 text-base text-gray-100"><?php echo esc_html( $feature['text'] ); ?></p>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
