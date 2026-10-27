<?php
/**
 * Free Sessions block.
 *
 * Figma: Homepage section 49325:14389 (1440) / Frame 98 49196:7748 (390). Also
 * on every industry, category and level page (patterns #944–946).
 *
 * The sessions are LearnDash Sample sessions (inc/lms-free-sessions.php): the
 * ones chosen in the block, topped up with the other free sessions. A card
 * plays the session's video inline (no progress is recorded, as for any
 * visitor) and its title opens the LearnDash session page.
 *
 * @param array $block The block settings and attributes.
 *
 * @package upskill-me
 */

$attributes = upskill_block_attributes( $block, 'block-free-sessions pt-[46px] pb-section' );
$sessions   = function_exists( 'upskill_free_session_ids' ) ? array_map( 'upskill_free_session', upskill_free_session_ids( (array) get_field( 'sessions' ) ) ) : array();
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php // Figma top-aligns the title column with the intro here (other split headers sit on the baseline). ?>
		<?php upskill_section_header( array( 'class' => 'free-sessions__header lg:items-start' ) ); ?>

		<?php if ( $sessions ) : ?>
			<?php // Desktop: the first card is 820 of 1360 wide, the next two stack beside it; tablets put those two side by side. ?>
			<div class="free-sessions__grid mt-8 grid gap-4 sm:grid-cols-2 lg:mt-16 lg:grid-cols-[820fr_524fr] lg:grid-rows-2">
				<?php foreach ( $sessions as $index => $session ) : ?>
					<?php
					$featured = 0 === $index;
					$title_id = wp_unique_id( 'free-session-' );
					// Covers are drawn at the box width on desktop; on phones the wide covers of the small cards are drawn by height (up to 1.46x the box width).
					// Covers carry their title artwork on the left, so a narrower box crops from the right.
					$sizes = $featured
						? '(min-width: 1025px) min(calc(60vw - 72px), 790px), calc(100vw - 32px)'
						: '(min-width: 1025px) min(calc(39vw - 24px), 510px), (min-width: 768px) calc((50vw - 24px) * 1.46), calc((100vw - 32px) * 1.46)';
					?>
					<article class="free-sessions__card group reveal flex flex-col overflow-hidden rounded-lg bg-gray-950 shadow-card lg:rounded-[32px] lg:border lg:border-gray-100 lg:bg-[#0e0e10]<?php echo $featured ? ' sm:col-span-2 lg:col-span-1 lg:row-span-2' : ''; ?>" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
						<?php // Figma: the cover sits in a dark frame padded on three sides and runs into the divider below. The large cover is taken out of flow so the stack beside it sets the height. ?>
						<div class="free-sessions__media relative isolate flex flex-col bg-[#202020] lg:bg-gray-950 <?php echo $featured ? 'lg:min-h-0 lg:flex-1 lg:px-4 lg:pt-4' : 'lg:px-2 lg:pt-2'; ?>">
							<div class="video-card free-sessions__video relative grid aspect-[358/224] grid-cols-1 grid-rows-1 overflow-hidden *:col-start-1 *:row-start-1 <?php echo $featured ? 'lg:aspect-auto lg:min-h-[320px] lg:flex-1 lg:rounded-t-[32px]' : 'lg:aspect-auto lg:h-[216px] lg:rounded-t-[24px]'; ?>" data-video-card>
								<?php if ( $session['video'] ) : ?>
									<video class="free-sessions__player size-full object-cover<?php echo $featured ? ' lg:absolute lg:inset-0' : ''; ?>" preload="metadata" playsinline data-video controls>
										<source src="<?php echo esc_url( $session['video'] ); ?>">
									</video>
								<?php endif; ?>

								<?php if ( $session['poster'] ) : ?>
									<?php
									upskill_image(
										$session['poster'],
										'full',
										array(
											'class'         => 'video-card__poster free-sessions__poster pointer-events-none size-full object-cover group-hover:scale-[1.03]' . ( $featured ? ' object-left lg:absolute lg:inset-0' : ' object-left lg:object-[50%_30%]' ),
											'alt'           => '',
											'sizes'         => $sizes,
											'loading'       => 'lazy',
											'data-video-poster' => '',
										)
									);
									?>
								<?php endif; ?>

								<div class="free-sessions__overlay pointer-events-none relative hidden bg-linear-to-t from-[rgb(14_14_16/82%)] via-[rgb(14_14_16/34%)] via-38% to-transparent to-72% lg:block" aria-hidden="true" data-video-overlay></div>

								<?php if ( $session['video'] ) : ?>
									<?php // Custom transport, revealed by view.js; the native controls remain the no-JS fallback. Phones show it once the session starts. ?>
									<div class="video-card__bar free-sessions__bar relative flex items-center gap-0.5 self-end px-2 pb-2 text-white <?php echo $featured ? 'lg:pr-6' : 'lg:px-0 lg:pb-0'; ?>" hidden data-video-bar>
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
											<span class="w-9 flex-none text-xs leading-[18px]" data-video-current>00:00</span>
											<input class="video-card__progress" type="range" min="0" max="100" step="0.1" value="0" aria-label="<?php esc_attr_e( 'Seek', 'upskill-me' ); ?>" data-video-progress>
											<span class="w-[42px] flex-none text-xs leading-[18px]" data-video-remaining></span>
										</div>
										<button class="video-card__btn" type="button" data-video-fullscreen>
											<span class="screen-reader-text"><?php esc_html_e( 'Full screen', 'upskill-me' ); ?></span>
											<?php upskill_icon( 'maximize', 'video-card__icon' ); ?>
										</button>
									</div>
								<?php endif; ?>
							</div>

							<div class="free-sessions__tags pointer-events-none absolute top-4 left-4 hidden items-center gap-2 lg:flex">
								<span class="tag tag--free"><?php esc_html_e( 'Free', 'upskill-me' ); ?></span>
								<?php if ( $session['code'] ) : ?>
									<span class="tag tag--code"><?php echo esc_html( $session['code'] ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<div class="free-sessions__body flex flex-col gap-2 py-5 lg:border-t lg:border-gray-700 lg:p-5<?php echo $featured ? ' px-4' : ' flex-1 px-5 lg:pb-[25px]'; ?>">
							<?php if ( $session['category'] ) : ?>
								<p class="free-sessions__category text-2xs leading-[16.2px] font-medium tracking-[1.4px] text-brand-300 uppercase lg:text-sm lg:leading-[1.5] lg:tracking-normal"><?php echo esc_html( $session['category'] ); ?></p>
							<?php endif; ?>
							<h3 class="free-sessions__title text-xl font-medium text-white lg:text-h6" id="<?php echo esc_attr( $title_id ); ?>">
								<a class="transition-colors hover:text-brand-300 focus-visible:text-brand-300" href="<?php echo esc_url( $session['url'] ); ?>"><?php echo esc_html( $session['title'] ); ?></a>
							</h3>
							<?php if ( $featured && $session['description'] ) : ?>
								<p class="free-sessions__description font-secondary text-[15px] leading-[1.62] tracking-[-0.006em] text-white/62 lg:text-gray-100"><?php echo esc_html( $session['description'] ); ?></p>
							<?php endif; ?>
							<?php if ( $featured ) : ?>
								<a class="free-sessions__link group mt-auto hidden items-center gap-2 pt-3 text-[13px] leading-[1.62] font-medium tracking-[-0.1px] text-brand-500 hover:text-brand-400 lg:inline-flex lg:self-start" href="<?php echo esc_url( $session['url'] ); ?>" aria-describedby="<?php echo esc_attr( $title_id ); ?>">
									<?php esc_html_e( 'Watch now', 'upskill-me' ); ?>
									<?php upskill_icon( 'arrow-up-right', 'size-3 text-brand-700 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
