<?php
/**
 * Course Content block (course page).
 *
 * Figma: Product Detail "Course content" 49325:21042 (1440).
 *
 * The split section header over an accordion of the course's sessions, read
 * from LearnDash in the course builder's order: number, title, "Free preview"
 * for Sample sessions, duration (session field); open, the session excerpt
 * and, for a free session, "Watch Free Training". The first session starts
 * open. Panels use the shared Disclosure script; view.js adds "Expand all"
 * and opens the session named in the URL hash (the hero's session tabs).
 *
 * Learners who already have the course get LearnDash's own course page, so
 * this list is only ever the preview.
 *
 * @param array $block   The block settings and attributes.
 * @param int   $post_id The post the block belongs to.
 *
 * @package upskill-me
 */

$course_id = function_exists( 'upskill_block_course_id' ) ? upskill_block_course_id( $post_id ?? 0 ) : 0;
$sessions  = $course_id ? upskill_course_sessions( $course_id ) : array();

if ( ! $sessions ) {
	return;
}

$count    = count( $sessions );
$duration = upskill_course_duration( $sessions );
$uid      = wp_unique_id( 'course-session-' );

$attributes = upskill_block_attributes( $block, 'block-course-content section-y bg-[#f3f1fb]' ); // Figma #f3f1fb, no theme token.
?>
<section<?php echo $attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>>
	<div class="container">
		<?php
		upskill_section_header(
			array(
				'layout' => 'split',
				'button' => false,
				'reveal' => true,
			)
		);
		?>

		<div class="course-content__box reveal mt-10 lg:mt-20 overflow-hidden rounded-[22px] border border-line bg-white shadow-xs" data-course-content>
			<div class="course-content__summary flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 bg-brand-25 px-4 py-4 lg:px-6">
				<p class="text-lg text-gray-950 lg:text-xl">
					<?php
					echo esc_html(
						/* translators: %d: number of sessions. */
						sprintf( _n( '%d session', '%d sessions', $count, 'upskill-me' ), $count ) . ( $duration ? ' · ' . $duration : '' )
					);
					?>
				</p>
				<?php if ( $count > 1 ) : ?>
					<button class="course-content__expand group inline-flex h-[42px] flex-none items-center gap-2 rounded-full border border-gray-100 bg-white pr-2 pl-[18px] text-sm font-medium text-gray-950 hover:border-brand-300" type="button" aria-pressed="false" data-course-expand data-label-expand="<?php esc_attr_e( 'Expand all', 'upskill-me' ); ?>" data-label-collapse="<?php esc_attr_e( 'Collapse all', 'upskill-me' ); ?>">
						<span data-course-expand-label><?php esc_html_e( 'Expand all', 'upskill-me' ); ?></span>
						<span class="grid size-7 place-items-center rounded-full bg-brand-25 text-brand-700 transition-transform group-aria-pressed:rotate-180" aria-hidden="true"><?php upskill_icon( 'chevron-sm', 'size-[11px]' ); ?></span>
					</button>
				<?php endif; ?>
			</div>

			<ol class="course-content__list">
				<?php foreach ( $sessions as $index => $session ) : ?>
					<?php
					$open     = 0 === $index;
					$free     = 'on' === learndash_get_setting( $session, 'sample_lesson' );
					$panel_id = $uid . '-' . ( $index + 1 );
					$excerpt  = has_excerpt( $session ) ? get_the_excerpt( $session ) : '';
					$minutes  = upskill_course_duration( array( $session ) );
					?>
					<li class="course-content__item border-t border-line" id="session-<?php echo (int) ( $index + 1 ); ?>">
						<h3>
							<button class="course-content__toggle group flex w-full items-center gap-3 px-4 py-4 text-left lg:gap-5 lg:px-6 lg:py-5" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>" data-disclosure>
								<span class="course-content__number grid size-[38px] flex-none place-items-center rounded-lg bg-brand-25 text-sm font-medium text-brand-700"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span class="flex min-w-0 flex-1 flex-wrap items-center gap-x-6 gap-y-1">
									<span class="course-content__title text-base leading-[1.3] font-medium text-gray-950 lg:text-xl lg:leading-normal"><?php echo esc_html( upskill_session_title( $session ) ); ?></span>
									<?php if ( $free ) : ?>
										<?php // Figma's green "Free preview" pill (no theme token). ?>
										<span class="course-content__free rounded-sm bg-[#e6f4d7] px-2 py-0.5 text-sm text-[#36621a]"><?php esc_html_e( 'Free preview', 'upskill-me' ); ?></span>
									<?php endif; ?>
								</span>
								<?php if ( $minutes ) : ?>
									<?php // Phones show no duration (Figma 49369:27747): the title takes the width. ?>
									<span class="course-content__duration flex-none text-sm text-gray-500 max-sm:hidden"><?php echo esc_html( $minutes ); ?></span>
								<?php endif; ?>
								<?php // Figma 16px chevron (8px glyph), #333. ?>
								<span class="flex-none text-gray-800 transition-transform group-aria-expanded:rotate-180" aria-hidden="true"><?php upskill_icon( 'chevron-row', 'size-4' ); ?></span>
							</button>
						</h3>
						<div class="collapse<?php echo $open ? ' is-open' : ''; ?>" id="<?php echo esc_attr( $panel_id ); ?>">
							<div>
								<div class="course-content__panel flex flex-col items-start gap-4 px-4 pb-6 lg:pr-6 lg:pl-[120px]">
									<?php if ( $excerpt ) : ?>
										<p class="text-base text-gray-500"><?php echo esc_html( $excerpt ); ?></p>
									<?php endif; ?>
									<?php if ( $free ) : ?>
										<?php
										upskill_button(
											array(
												'url'   => learndash_get_step_permalink( $session, $course_id ),
												'title' => __( 'Watch Free Training', 'upskill-me' ),
											),
											array(
												'variant' => 'light',
												'icon'    => 'play',
												'class'   => 'min-h-[50px]',
											)
										);
										?>
									<?php elseif ( ! $excerpt ) : ?>
										<p class="text-base text-gray-500"><?php esc_html_e( 'Included when your organisation buys this training.', 'upskill-me' ); ?></p>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>
