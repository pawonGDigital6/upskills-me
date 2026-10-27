<?php
/**
 * LMS free sessions: what the Free Sessions block shows.
 *
 * A free session is a published LearnDash session (lesson) marked **Sample**
 * (Course → Builder → session → Settings → "Sample lesson"). LearnDash lets
 * anyone, signed in or not, open a sample session, so the block never decides
 * access itself: it lists sample sessions and links to their LearnDash page.
 *
 * Each card reads the session: title (without a "Session:" prefix), code
 * (ACF Session: Details), the course's Category, the session excerpt (else
 * the course summary), the session's LearnDash video, and the session's
 * featured image as the poster (else the course's).
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_is_free_session' ) ) {
	/**
	 * Is this a published LearnDash Sample session?
	 *
	 * @param int $session_id Session (sfwd-lessons) ID.
	 * @return bool
	 */
	function upskill_is_free_session( $session_id ) {
		if ( 'sfwd-lessons' !== get_post_type( $session_id ) || 'publish' !== get_post_status( $session_id ) ) {
			return false;
		}

		if ( function_exists( 'learndash_is_sample' ) ) {
			return (bool) learndash_is_sample( $session_id );
		}

		return function_exists( 'learndash_get_setting' ) && 'on' === learndash_get_setting( $session_id, 'sample_lesson' );
	}
}

if ( ! function_exists( 'upskill_free_session_ids' ) ) {
	/**
	 * The sessions to show: the chosen ones that are still free, in the chosen
	 * order, topped up with the other free sessions (oldest first).
	 *
	 * @param int[] $chosen Session IDs picked in the block.
	 * @param int   $count  How many to return.
	 * @return int[]
	 */
	function upskill_free_session_ids( $chosen = array(), $count = 3 ) {
		$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $chosen ), 'upskill_is_free_session' ) ) );

		if ( count( $ids ) < $count ) {
			$others = get_posts(
				array(
					'post_type'   => 'sfwd-lessons',
					'post_status' => 'publish',
					'numberposts' => -1,
					'fields'      => 'ids',
					'orderby'     => 'date',
					'order'       => 'ASC',
				)
			);

			foreach ( $others as $session_id ) {
				if ( count( $ids ) >= $count ) {
					break;
				}
				if ( ! in_array( (int) $session_id, $ids, true ) && upskill_is_free_session( $session_id ) ) {
					$ids[] = (int) $session_id;
				}
			}
		}

		return array_slice( $ids, 0, $count );
	}
}

if ( ! function_exists( 'upskill_free_session' ) ) {
	/**
	 * What a free session card shows.
	 *
	 * @param int $session_id Session ID.
	 * @return array { title, code, category, description, video, poster, url }
	 */
	function upskill_free_session( $session_id ) {
		$course_id = function_exists( 'learndash_get_course_id' ) ? (int) learndash_get_course_id( $session_id ) : 0;
		$settings  = function_exists( 'learndash_get_setting' ) ? (array) learndash_get_setting( $session_id ) : array();
		$category  = $course_id ? get_the_terms( $course_id, 'training_category' ) : false;
		$video     = ! empty( $settings['lesson_video_enabled'] ) && ! empty( $settings['lesson_video_url'] ) ? (string) $settings['lesson_video_url'] : '';
		$poster    = get_post_thumbnail_id( $session_id );

		if ( ! $poster && $course_id ) {
			$poster = get_post_thumbnail_id( $course_id );
		}

		$description = has_excerpt( $session_id ) ? get_the_excerpt( $session_id ) : '';

		if ( ! $description && $course_id && function_exists( 'upskill_learner_course_summary' ) ) {
			$description = upskill_learner_course_summary( $course_id );
		}

		return array(
			'title'       => preg_replace( '/^\s*session\s*\d*\s*[:\-]\s*/i', '', get_the_title( $session_id ) ),
			'code'        => (string) get_field( 'session_code', $session_id ),
			'category'    => $category && ! is_wp_error( $category ) ? $category[0]->name : '',
			'description' => wp_strip_all_tags( (string) $description ),
			// The card plays files inline; a hosted (YouTube/Vimeo) video opens on the session page.
			'video'       => preg_match( '/\.(mp4|m4v|webm|mov)(\?.*)?$/i', $video ) ? $video : '',
			'poster'      => (int) $poster,
			'url'         => $course_id && function_exists( 'learndash_get_step_permalink' ) ? learndash_get_step_permalink( $session_id, $course_id ) : get_permalink( $session_id ),
		);
	}
}
