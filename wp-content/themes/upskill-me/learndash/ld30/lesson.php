<?php
/**
 * LearnDash LD30: a session (lesson) page.
 *
 * Override of sfwd-lms/themes/ld30/templates/lesson.php (LearnDash 4.21.3 template).
 * Figma: User Course Flow 49325:18592 (1440) / 49374:113832 (390).
 *
 * Laid out to the design (course progress bar and session tabs, video card,
 * previous/next session, "Session information" panel), but LearnDash still owns
 * the behaviour: its hooks, the lesson-progression message, the "step completed"
 * alert, the video (with Video Progression) and the Mark Complete form that video
 * auto-completion submits. Styles: src/global/scss/plugins/learndash/_session.scss.
 *
 * Variables from LearnDash: $course_id, $user_id, $post, $content, $materials,
 * $show_content, $lesson_progression_enabled, $previous_lesson_completed,
 * $has_access, $all_quizzes_completed, $logged_in.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'comments_array', 'learndash_remove_comments', 1, 2 );

$upskill_sessions = array_values( array_map( 'intval', (array) learndash_course_get_steps_by_type( $course_id, learndash_get_post_type_slug( 'lesson' ) ) ) );
$upskill_index    = array_search( (int) $post->ID, $upskill_sessions, true );
$upskill_index    = false === $upskill_index ? 0 : $upskill_index;
$upskill_total    = count( $upskill_sessions );
$upskill_steps    = (int) learndash_get_course_steps_count( $course_id );
$upskill_done     = $user_id ? (int) learndash_course_get_completed_steps( $user_id, $course_id ) : 0;
$upskill_percent  = $upskill_steps ? (int) round( 100 * min( $upskill_done, $upskill_steps ) / $upskill_steps ) : 0;
$upskill_category = get_the_terms( $course_id, 'training_category' );
$upskill_category = is_array( $upskill_category ) ? $upskill_category[0] : null;
$upskill_previous = $upskill_index > 0 ? get_post( $upskill_sessions[ $upskill_index - 1 ] ) : null;
$upskill_next     = $upskill_index < $upskill_total - 1 ? get_post( $upskill_sessions[ $upskill_index + 1 ] ) : null;
$upskill_points   = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_field( 'key_points', $post->ID ) ) ) );

// The video and the session text arrive joined (modules/lesson-video.php adds the marker).
$upskill_parts = explode( '<!--upskill-session-text-->', (string) $content, 2 );
$upskill_video = 2 === count( $upskill_parts ) ? $upskill_parts[0] : '';
$upskill_text  = 2 === count( $upskill_parts ) ? $upskill_parts[1] : (string) $content;

// A title written as "Session: Fire safety" reads "Fire safety" where the page already says "Session".
$upskill_session_name = static function ( $session ) {
	return preg_replace( '/^\s*session\s*\d*\s*[:\-]\s*/i', '', get_the_title( $session ) );
};

$upskill_session_label = static function ( $index, $session ) use ( $upskill_session_name ) {
	/* translators: 1: session number, 2: session title. */
	return sprintf( __( 'Session %1$d - %2$s', 'upskill-me' ), $index + 1, $upskill_session_name( $session ) );
};
?>
<div class="<?php echo esc_attr( learndash_the_wrapper_class() ); ?> session">

	<?php
	/** This action is documented in sfwd-lms/themes/ld30/templates/lesson.php. */
	do_action( 'learndash-lesson-before', get_the_ID(), $course_id, $user_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- LearnDash hook.
	?>

	<section class="session__progress" aria-label="<?php esc_attr_e( 'Course progress', 'upskill-me' ); ?>">
		<div class="container">
			<p class="session__progress-meta">
				<span><?php echo esc_html( get_the_title( $course_id ) ); ?></span>
				<span>
					<?php
					/* translators: 1: current session number, 2: number of sessions, 3: percent complete. */
					printf( esc_html__( 'Session %1$d of %2$d · %3$d%% Complete', 'upskill-me' ), (int) $upskill_index + 1, (int) $upskill_total, (int) $upskill_percent );
					?>
				</span>
			</p>
			<ol class="session__tabs" style="--sessions: <?php echo (int) max( 1, $upskill_total ); ?>">
				<?php foreach ( $upskill_sessions as $upskill_i => $upskill_session_id ) : ?>
					<?php
					$upskill_complete = $user_id && learndash_is_lesson_complete( $user_id, $upskill_session_id, $course_id );
					$upskill_current  = $upskill_session_id === (int) $post->ID;
					?>
					<li class="session__tab<?php echo $upskill_complete ? ' is-complete' : ''; ?><?php echo $upskill_current ? ' is-current' : ''; ?>">
						<a class="session__tab-link" href="<?php echo esc_url( learndash_get_step_permalink( $upskill_session_id, $course_id ) ); ?>"<?php echo $upskill_current ? ' aria-current="step"' : ''; ?>>
							<span class="session__tab-bar" aria-hidden="true"></span>
							<span class="session__tab-label">
								<?php if ( $upskill_complete ) : ?>
									<?php upskill_icon( 'check', 'session__tab-icon' ); ?>
									<span class="screen-reader-text"><?php esc_html_e( 'Completed:', 'upskill-me' ); ?></span>
								<?php endif; ?>
								<?php
								/* translators: %d: session number. */
								printf( esc_html__( 'Session %d', 'upskill-me' ), (int) $upskill_i + 1 );
								?>
								<span class="screen-reader-text"><?php echo esc_html( ': ' . $upskill_session_name( $upskill_session_id ) ); ?></span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<section class="session__main">
		<div class="container">
			<header class="session__header">
				<?php
				if ( $upskill_category ) {
					upskill_eyebrow( $upskill_category->name, 'book-open' );
				}
				?>
				<h1 class="session__course-title"><?php echo esc_html( get_the_title( $course_id ) ); ?></h1>
				<p class="session__title"><?php echo esc_html( $upskill_session_label( $upskill_index, $post ) ); ?></p>
			</header>

			<?php
			learndash_30_show_step_completed_alert( $post->ID, $course_id, $user_id, 'lesson' );

			// The previous session must be completed first (LearnDash lesson progression).
			if ( isset( $lesson_progression_enabled, $previous_lesson_completed ) && $lesson_progression_enabled && true !== $previous_lesson_completed ) {
				if ( ! learndash_is_sample( $post ) || $has_access ) {
					$upskill_previous_item = learndash_user_progress_get_previous_incomplete_step( $user_id, $course_id, $post->ID );
					if ( $upskill_previous_item ) {
						learndash_get_template_part(
							'modules/messages/lesson-progression.php',
							array(
								'previous_item' => get_post( $upskill_previous_item ),
								'course_id'     => $course_id,
								'context'       => 'lesson',
								'user_id'       => $user_id,
							),
							true
						);
					}
				}
			}
			?>

			<?php if ( $show_content ) : ?>
				<div class="session__layout">
					<div class="session__primary">
						<?php if ( $upskill_video ) : ?>
							<div class="session__player">
								<?php echo $upskill_video; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- LearnDash video markup. ?>
							</div>
						<?php endif; ?>

						<?php
						// LearnDash's Mark Complete form: video auto-completion submits it, and it stays hidden until the video has been watched.
						$upskill_can_complete = $all_quizzes_completed && $logged_in && ! empty( $course_id ) && $previous_lesson_completed;
						/** This filter is documented in sfwd-lms/themes/ld30/templates/lesson.php. */
						$upskill_can_complete = apply_filters( 'learndash-lesson-can-complete', $upskill_can_complete, get_the_ID(), $course_id, $user_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- LearnDash hook.
						if ( $upskill_can_complete && $user_id && ! learndash_is_lesson_complete( $user_id, $post->ID, $course_id ) ) :
							?>
							<div class="session__complete">
								<?php
								echo learndash_mark_complete( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- LearnDash form markup.
									$post,
									array(
										'form'   => array( 'class' => 'session__complete-form' ),
										'button' => array( 'class' => 'btn btn--primary btn--md session__complete-button' ),
									)
								);
								?>
							</div>
						<?php endif; ?>

						<?php if ( $upskill_previous || $upskill_next ) : ?>
							<nav class="session__nav" aria-label="<?php esc_attr_e( 'Session navigation', 'upskill-me' ); ?>">
								<?php if ( $upskill_previous ) : ?>
									<a class="session__nav-link session__nav-link--previous" href="<?php echo esc_url( learndash_get_step_permalink( $upskill_previous->ID, $course_id ) ); ?>">
										<span class="session__nav-icon" aria-hidden="true"><?php upskill_icon( 'arrow-right', 'session__nav-arrow' ); ?></span>
										<span class="session__nav-text">
											<span class="session__nav-label"><?php esc_html_e( 'Previous session', 'upskill-me' ); ?></span>
											<span class="session__nav-title"><?php echo esc_html( $upskill_session_name( $upskill_previous ) ); ?></span>
										</span>
									</a>
								<?php endif; ?>
								<?php if ( $upskill_next ) : ?>
									<a class="session__nav-link session__nav-link--next" href="<?php echo esc_url( learndash_get_step_permalink( $upskill_next->ID, $course_id ) ); ?>">
										<span class="session__nav-text">
											<span class="session__nav-label"><?php esc_html_e( 'Next session', 'upskill-me' ); ?></span>
											<span class="session__nav-title"><?php echo esc_html( $upskill_session_name( $upskill_next ) ); ?></span>
										</span>
										<span class="session__nav-icon" aria-hidden="true"><?php upskill_icon( 'arrow-right', 'session__nav-arrow' ); ?></span>
									</a>
								<?php endif; ?>
							</nav>
						<?php endif; ?>
					</div>

					<aside class="session__info" aria-labelledby="session-info-title">
						<p class="session__info-label"><?php esc_html_e( 'Session information', 'upskill-me' ); ?></p>
						<h2 class="session__info-title" id="session-info-title"><?php esc_html_e( 'What you need to know.', 'upskill-me' ); ?></h2>
						<?php if ( trim( wp_strip_all_tags( $upskill_text ) ) ) : ?>
							<div class="session__info-text"><?php echo $upskill_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output. ?></div>
						<?php endif; ?>

						<?php if ( $upskill_points ) : ?>
							<p class="session__info-subtitle"><?php esc_html_e( 'Key points', 'upskill-me' ); ?></p>
							<ul class="session__points">
								<?php foreach ( $upskill_points as $upskill_point ) : ?>
									<li><?php upskill_icon( 'check', 'session__point-icon' ); ?><span><?php echo esc_html( $upskill_point ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<?php if ( ! empty( $materials ) ) : ?>
							<p class="session__info-subtitle"><?php esc_html_e( 'Supporting material', 'upskill-me' ); ?></p>
							<div class="session__materials"><?php echo $materials; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- LearnDash materials output. ?></div>
						<?php endif; ?>

						<?php
						// LearnDash's Profile lists every course and certificate (as on My learning).
						$upskill_all_url = is_user_logged_in() && function_exists( 'upskill_learndash_profile_url' ) ? upskill_learndash_profile_url() : '';

						if ( $upskill_all_url ) {
							upskill_button(
								array(
									'url'   => $upskill_all_url,
									'title' => __( 'View All Completed Courses', 'upskill-me' ),
								),
								array(
									'variant' => 'light',
									'class'   => 'session__info-button',
								)
							);
						}
						?>
					</aside>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	/** This action is documented in sfwd-lms/themes/ld30/templates/lesson.php. */
	do_action( 'learndash-lesson-after', get_the_ID(), $course_id, $user_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- LearnDash hook.
	learndash_load_login_modal_html();
	?>
</div>
