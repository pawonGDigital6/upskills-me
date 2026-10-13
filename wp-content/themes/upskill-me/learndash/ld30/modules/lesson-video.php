<?php
/**
 * LearnDash: join a session's video and its text.
 *
 * Override of sfwd-lms/themes/ld30/templates/modules/lesson-video.php (LearnDash maps
 * "learndash_lesson_video" to this LD30 module; overrides live in learndash/ld30/).
 * LearnDash normally prepends the video to the session text. The session page
 * (learndash/ld30/lesson.php) shows them in different places (video left, text
 * in the "Session information" panel), so this keeps both intact and puts a
 * marker between them for lesson.php to split on. The [ld_video] placeholder
 * still works as in the original.
 *
 * @var string $content       Session text (already filtered).
 * @var string $video_content LearnDash video markup (.ld-video), or empty.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$content = str_replace( array( '<p>[ld_video]</p>', '[ld_video]' ), '', $content );

if ( ! empty( $video_content ) ) {
	echo $video_content . '<!--upskill-session-text-->' . $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- LearnDash-generated markup.
} else {
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- LearnDash-generated markup.
}
