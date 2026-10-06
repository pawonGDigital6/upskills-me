<?php
/**
 * Free Sessions block.
 *
 * Figma: Homepage section 49325:14389 (1440) / Frame 98 49196:7748 (390). The
 * same three sessions also appear on every industry and category page, so the
 * block is meant to be saved once as a synced pattern and reused.
 *
 * The sessions are block-owned for now. Once LearnDash is installed they
 * become LearnDash "sample lessons" (free, playable without enrolment), and
 * this block switches to reading those instead of its own rows.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$attributes = upskill_block_attributes( $block, 'block-free-sessions pt-[46px] pb-section' );
$sessions   = array_values(
	array_filter(
		(array) get_field( 'sessions' ),
		static function ( $session ) {
			return ! empty( $session['title'] );
		}
	)
);
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php upskill_section_header(); ?>

		<?php if ( $sessions ) : ?>
			<?php // Desktop: the first card is 820 of 1360 wide, the next two stack beside it. ?>
			<div class="mt-8 grid gap-4 lg:mt-16 lg:grid-cols-[60.3fr_39.7fr] lg:grid-rows-2">
				<?php foreach ( $sessions as $index => $session ) : ?>
					<?php
					$featured = 0 === $index;
					$video    = ! empty( $session['video']['url'] ) ? $session['video']['url'] : '';
					$poster   = ! empty( $session['poster'] ) ? wp_get_attachment_image_url( is_array( $session['poster'] ) ? $session['poster']['ID'] : (int) $session['poster'], $featured ? 'upskill-wide' : 'large' ) : '';
					$title_id = wp_unique_id( 'free-session-' );
					?>
					<article class="reveal flex flex-col overflow-hidden rounded-lg bg-gray-950 shadow-card lg:rounded-[32px] lg:border lg:border-gray-100<?php echo $featured ? ' lg:row-span-2' : ''; ?>" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
						<div class="relative isolate bg-[#202020] lg:bg-gray-950 <?php echo $featured ? 'lg:flex lg:min-h-0 lg:flex-1 lg:flex-col lg:p-4' : 'lg:p-2'; ?>">
							<div class="video-card relative aspect-[358/224] overflow-hidden lg:rounded-[24px] <?php echo $featured ? 'lg:aspect-auto lg:min-h-[320px] lg:flex-1 lg:rounded-[32px]' : 'lg:aspect-auto lg:h-[208px]'; ?>" data-video-card>
								<?php if ( $video ) : ?>
									<video class="absolute inset-0 size-full object-cover" preload="metadata" playsinline<?php echo $poster ? ' poster="' . esc_url( $poster ) . '"' : ''; ?> data-video controls>
										<source src="<?php echo esc_url( $video ); ?>" type="video/mp4">
									</video>
								<?php elseif ( $poster ) : ?>
									<img class="absolute inset-0 size-full object-cover" src="<?php echo esc_url( $poster ); ?>" alt="" loading="lazy" decoding="async">
								<?php endif; ?>

								<div class="pointer-events-none absolute inset-0 hidden bg-linear-to-t from-[rgb(14_14_16/82%)] via-[rgb(14_14_16/34%)] via-38% to-transparent to-72% lg:block" aria-hidden="true" data-video-overlay></div>

								<div class="pointer-events-none absolute top-4 left-4 hidden items-center gap-2 lg:flex">
									<?php if ( ! empty( $session['is_free'] ) ) : ?>
										<span class="tag tag--free"><?php esc_html_e( 'Free', 'upskill-me' ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $session['code'] ) ) : ?>
										<span class="tag tag--code"><?php echo esc_html( $session['code'] ); ?></span>
									<?php endif; ?>
								</div>

								<?php if ( $video ) : ?>
									<?php // Custom transport, revealed by view.js; the native controls remain the no-JS fallback. ?>
									<div class="video-card__bar absolute inset-x-0 bottom-0 flex items-center gap-0.5 px-2 pb-2 text-white <?php echo $featured ? 'lg:px-6' : ''; ?>" hidden data-video-bar>
										<button class="video-card__btn" type="button" data-video-play>
											<span class="screen-reader-text" data-label-play="<?php esc_attr_e( 'Play', 'upskill-me' ); ?>" data-label-pause="<?php esc_attr_e( 'Pause', 'upskill-me' ); ?>"><?php esc_html_e( 'Play', 'upskill-me' ); ?></span>
											<?php upskill_icon( 'play', 'video-card__icon video-card__icon--play' ); ?>
											<?php upskill_icon( 'pause', 'video-card__icon video-card__icon--pause' ); ?>
										</button>
										<button class="video-card__btn" type="button" aria-pressed="false" data-video-mute>
											<span class="screen-reader-text"><?php esc_html_e( 'Mute', 'upskill-me' ); ?></span>
											<?php upskill_icon( 'volume', 'video-card__icon' ); ?>
										</button>
										<div class="flex min-w-0 flex-1 items-center gap-2 px-1">
											<span class="w-9 flex-none text-xs font-semibold" data-video-current>00:00</span>
											<input class="video-card__progress" type="range" min="0" max="100" step="0.1" value="0" aria-label="<?php esc_attr_e( 'Seek', 'upskill-me' ); ?>" data-video-progress>
											<span class="w-[42px] flex-none text-xs font-semibold" data-video-remaining></span>
										</div>
										<button class="video-card__btn" type="button" data-video-fullscreen>
											<span class="screen-reader-text"><?php esc_html_e( 'Full screen', 'upskill-me' ); ?></span>
											<?php upskill_icon( 'maximize', 'video-card__icon' ); ?>
										</button>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<div class="flex flex-col gap-2 px-4 py-5 lg:border-t lg:border-gray-700 lg:p-5<?php echo $featured ? '' : ' flex-1'; ?>">
							<?php if ( ! empty( $session['category'] ) ) : ?>
								<p class="text-2xs leading-[16.2px] font-medium tracking-[1.4px] text-brand-300 uppercase lg:text-sm lg:leading-[1.5] lg:tracking-normal"><?php echo esc_html( $session['category'] ); ?></p>
							<?php endif; ?>
							<h3 class="text-xl font-medium text-white lg:text-h6" id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $session['title'] ); ?></h3>
							<?php if ( $featured && ! empty( $session['description'] ) ) : ?>
								<p class="font-[Geist] text-[15px] leading-[1.62] tracking-[-0.006em] text-white/62 lg:text-gray-100"><?php echo esc_html( $session['description'] ); ?></p>
							<?php endif; ?>
							<?php if ( $featured && ! empty( $session['link']['url'] ) ) : ?>
								<a class="group mt-auto hidden items-center gap-2 pt-3 text-[13px] leading-[1.62] font-medium text-brand-500 hover:text-brand-400 lg:inline-flex"<?php echo upskill_link_attributes( $session['link'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
									<?php echo esc_html( $session['link']['title'] ? $session['link']['title'] : __( 'Watch now', 'upskill-me' ) ); ?>
									<?php upskill_icon( 'arrow-up-right', 'size-3 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
