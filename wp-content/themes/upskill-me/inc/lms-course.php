<?php
/**
 * LMS course page: data for the course blocks and the course editor's
 * starting layout.
 *
 * The course page (learndash/ld30/course.php) draws the hero; everything under
 * it is the course's own block content: At a Glance, About This Training, What
 * You'll Learn (Training Needs), Course Details, Course Content, Reviews, then
 * the shared "Course page sections" synced pattern. The blocks read LearnDash
 * (sessions, order, durations, Sample flag, certificate, reviews) through the
 * helpers here, so a fact is entered once.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_course_sessions' ) ) {
	/**
	 * A course's sessions (LearnDash lessons) in the course builder's order.
	 *
	 * @param int $course_id Course ID.
	 * @return int[]
	 */
	function upskill_course_sessions( $course_id ) {
		if ( ! $course_id || ! function_exists( 'learndash_course_get_steps_by_type' ) ) {
			return array();
		}

		return array_values( array_map( 'intval', (array) learndash_course_get_steps_by_type( $course_id, learndash_get_post_type_slug( 'lesson' ) ) ) );
	}
}

if ( ! function_exists( 'upskill_block_course_id' ) ) {
	/**
	 * The course a course block belongs to: the course being viewed or edited.
	 *
	 * @param int $post_id The post ACF renders the block for (0 = current).
	 * @return int 0 outside a course.
	 */
	function upskill_block_course_id( $post_id = 0 ) {
		$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

		return 'sfwd-courses' === get_post_type( $post_id ) ? $post_id : 0;
	}
}

if ( ! function_exists( 'upskill_course_fact_sources' ) ) {
	/**
	 * The course facts a block row can take from LearnDash instead of typing
	 * them (At a Glance tiles, Course Details rows).
	 *
	 * @return array<string,string> source => admin label.
	 */
	function upskill_course_fact_sources() {
		return array(
			''            => __( 'Typed below', 'upskill-me' ),
			'sessions'    => __( 'Number of sessions', 'upskill-me' ),
			'duration'    => __( 'Total duration', 'upskill-me' ),
			'level'       => __( 'Learning level (course field)', 'upskill-me' ),
			'category'    => __( 'Training category', 'upskill-me' ),
			'title'       => __( 'Course title', 'upskill-me' ),
			'certificate' => __( 'Certificate (shown only when set)', 'upskill-me' ),
		);
	}
}

if ( ! function_exists( 'upskill_course_fact' ) ) {
	/**
	 * One fact about a course, read from LearnDash and the course's fields.
	 *
	 * @param int    $course_id Course ID.
	 * @param string $source    A key of upskill_course_fact_sources().
	 * @return string '' when the course has no value for it.
	 */
	function upskill_course_fact( $course_id, $source ) {
		switch ( $source ) {
			case 'sessions':
				$count = count( upskill_course_sessions( $course_id ) );
				return $count ? (string) $count : '';
			case 'duration':
				return function_exists( 'upskill_course_duration' ) ? upskill_course_duration( upskill_course_sessions( $course_id ) ) : '';
			case 'level':
				return function_exists( 'get_field' ) ? trim( (string) get_field( 'learning_level', $course_id ) ) : '';
			case 'category':
				$terms = get_the_terms( $course_id, 'training_category' );
				return is_array( $terms ) ? $terms[0]->name : '';
			case 'title':
				return get_the_title( $course_id );
			case 'certificate':
				return function_exists( 'learndash_get_setting' ) && learndash_get_setting( $course_id, 'certificate' ) ? __( 'Certificate of completion', 'upskill-me' ) : '';
		}

		return '';
	}
}

if ( ! function_exists( 'upskill_course_reviews' ) ) {
	/**
	 * LearnDash Course Reviews for a course: switched on, how many, average.
	 *
	 * Reviews are LearnDash's own (Course → Settings → "Show reviews"); learners
	 * who have started the course can leave one.
	 *
	 * @param int $course_id Course ID.
	 * @return array{enabled: bool, count: int, average: float}
	 */
	function upskill_course_reviews( $course_id ) {
		$summary = array(
			'enabled' => false,
			'count'   => 0,
			'average' => 0.0,
		);

		if ( ! $course_id || ! function_exists( 'learndash_course_reviews_is_review_enabled' ) || ! learndash_course_reviews_is_review_enabled( (int) $course_id ) ) {
			return $summary;
		}

		$summary['enabled'] = true;
		$summary['count']   = (int) get_comments(
			array(
				'post_id' => $course_id,
				'type'    => 'ld_review',
				'status'  => 'approve',
				'count'   => true,
			)
		);
		$summary['average'] = (float) learndash_course_reviews_get_average_review_score( (int) $course_id );

		return $summary;
	}
}

if ( ! function_exists( 'upskill_course_preview_session' ) ) {
	/**
	 * The course's first free (Sample) session that has a video: the hero plays
	 * it as the course preview.
	 *
	 * @param int $course_id Course ID.
	 * @return array{id: int, video: string}|null
	 */
	function upskill_course_preview_session( $course_id ) {
		foreach ( upskill_course_sessions( $course_id ) as $session ) {
			$video = (string) learndash_get_setting( $session, 'lesson_video_url' );

			if ( 'on' === learndash_get_setting( $session, 'sample_lesson' ) && $video && 'on' === learndash_get_setting( $session, 'lesson_video_enabled' ) ) {
				return array(
					'id'    => $session,
					'video' => $video,
				);
			}
		}

		return null;
	}
}

if ( ! function_exists( 'upskill_session_title' ) ) {
	/**
	 * A session's title without the "Session:" prefix the catalogue uses.
	 *
	 * @param int $session Session ID.
	 * @return string
	 */
	function upskill_session_title( $session ) {
		return (string) preg_replace( '/^\s*session\s*:\s*/i', '', get_the_title( $session ) );
	}
}

/**
 * Course content: text typed into the editor between the course blocks keeps
 * a reading container (the blocks are full-width sections of their own).
 * Runs before blocks render, on the course being viewed only.
 */
add_filter(
	'the_content',
	static function ( $content ) {
		if ( ! is_singular( 'sfwd-courses' ) || ! in_the_loop() || ! is_main_query() || ! function_exists( 'upskill_contain_editor_text' ) ) {
			return $content;
		}

		return upskill_contain_editor_text(
			$content,
			'<div class="section-y bg-white"><div class="container"><div class="entry-content article-body">',
			'</div></div></div>'
		);
	},
	8
);

if ( ! function_exists( 'upskill_course_block_template' ) ) {
	/**
	 * The blocks a new course starts with, in Figma's order (Product Detail
	 * 49325:20483), so the client fills in a ready layout. LearnDash facts fill
	 * themselves; the shared sections come from the "Course page sections"
	 * synced pattern.
	 *
	 * @return array[] Block template for register_post_type().
	 */
	function upskill_course_block_template() {
		$header = static function ( $icon, $eyebrow, $heading = '', $intro = '' ) {
			return array(
				'eyebrow_icon'  => $icon,
				'_eyebrow_icon' => 'field_upskill_section_header_eyebrow_icon',
				'eyebrow'       => $eyebrow,
				'_eyebrow'      => 'field_upskill_section_header_eyebrow',
				'heading'       => $heading,
				'_heading'      => 'field_upskill_section_header_heading',
				'intro'         => $intro,
				'_intro'        => 'field_upskill_section_header_intro',
			);
		};

		$rows = static function ( $name, $key, $rows ) {
			$data = array(
				$name       => count( $rows ),
				'_' . $name => $key,
			);

			foreach ( $rows as $i => $row ) {
				foreach ( $row as $field => $value ) {
					$data[ "{$name}_{$i}_{$field}" ]       = $value;
					$data[ "_{$name}_{$i}_{$field}" ] = "{$key}_{$field}";
				}
			}

			return $data;
		};

		$template = array(
			array(
				'acf-block/course-glance',
				array(
					'data' => array_merge(
						array(
							'eyebrow_icon'  => 'binoculars',
							'_eyebrow_icon' => 'field_upskill_course_glance_eyebrow_icon',
							'eyebrow'       => __( 'At a glance', 'upskill-me' ),
							'_eyebrow' => 'field_upskill_course_glance_eyebrow',
						),
						$rows(
							'items',
							'field_upskill_course_glance_items',
							array(
								array( 'icon' => 'play-box', 'source' => 'sessions', 'title' => '', 'text' => __( 'Focused video-based learning', 'upskill-me' ) ),
								array( 'icon' => 'clock', 'source' => 'duration', 'title' => '', 'text' => __( 'Total learning time', 'upskill-me' ) ),
								array( 'icon' => 'refresh', 'source' => '', 'title' => __( 'On Demand', 'upskill-me' ), 'text' => __( 'Learn when you need it', 'upskill-me' ) ),
								array( 'icon' => 'briefcase', 'source' => '', 'title' => __( 'On The Job', 'upskill-me' ), 'text' => __( 'Practical workplace knowledge', 'upskill-me' ) ),
								array( 'icon' => 'chart', 'source' => 'level', 'title' => '', 'text' => __( 'Designed for everyday workplace learning', 'upskill-me' ) ),
								array( 'icon' => 'award', 'source' => 'certificate', 'title' => __( 'Certificate', 'upskill-me' ), 'text' => __( 'Certificate of completion', 'upskill-me' ) ),
							)
						)
					),
				),
			),
			array( 'acf-block/course-about', array( 'data' => $header( 'play-square', __( 'About this training', 'upskill-me' ) ) ) ),
			array(
				'acf-block/training-needs',
				array(
					'data' => array_merge(
						$header( 'book-open-alt', __( "What you'll learn", 'upskill-me' ), __( 'Knowledge you can <em>use on the job.</em>', 'upskill-me' ) ),
						array(
							'card_style'  => 'plain',
							'_card_style' => 'field_upskill_training_needs_card_style',
						)
					),
				),
			),
			array(
				'acf-block/course-details',
				array(
					'data' => array_merge(
						$header( 'book-open-alt', __( 'Course details', 'upskill-me' ), __( 'Everything <em>specified.</em>', 'upskill-me' ), __( 'A quick overview of the course format, content, learning level, access, duration and completion details, so learners know exactly what to expect before getting started.', 'upskill-me' ) ),
						$rows(
							'rows',
							'field_upskill_course_details_rows',
							array(
								array( 'label' => __( 'Category', 'upskill-me' ), 'source' => 'category', 'value' => '' ),
								array( 'label' => __( 'Course', 'upskill-me' ), 'source' => 'title', 'value' => '' ),
								array( 'label' => __( 'Format', 'upskill-me' ), 'source' => '', 'value' => __( 'Video sessions', 'upskill-me' ) ),
								array( 'label' => __( 'Sessions', 'upskill-me' ), 'source' => 'sessions', 'value' => '' ),
								array( 'label' => __( 'Duration', 'upskill-me' ), 'source' => 'duration', 'value' => '' ),
								array( 'label' => __( 'Level', 'upskill-me' ), 'source' => 'level', 'value' => '' ),
								array( 'label' => __( 'Access', 'upskill-me' ), 'source' => '', 'value' => __( 'On demand', 'upskill-me' ) ),
								array( 'label' => __( 'Language', 'upskill-me' ), 'source' => '', 'value' => __( 'English', 'upskill-me' ) ),
								array( 'label' => __( 'Assessment', 'upskill-me' ), 'source' => '', 'value' => __( 'Video-based learning', 'upskill-me' ) ),
								array( 'label' => __( 'Completion', 'upskill-me' ), 'source' => 'certificate', 'value' => '' ),
							)
						)
					),
				),
			),
			array( 'acf-block/course-content', array( 'data' => $header( 'book-open-alt', __( 'Course content', 'upskill-me' ), __( 'Explore <em>the training.</em>', 'upskill-me' ) ) ) ),
			array( 'acf-block/course-reviews', array( 'data' => $header( 'award', __( 'Reviews', 'upskill-me' ), __( 'What learners <em>are saying.</em>', 'upskill-me' ) ) ) ),
		);

		$pattern = get_posts(
			array(
				'post_type'   => 'wp_block',
				'name'        => 'course-page-sections',
				'post_status' => 'publish',
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);

		if ( $pattern ) {
			$template[] = array( 'core/block', array( 'ref' => (int) $pattern[0] ) );
		}

		return $template;
	}
}

/**
 * New courses open in the editor with the course page layout already placed.
 */
add_filter(
	'register_post_type_args',
	static function ( $args, $post_type ) {
		if ( 'sfwd-courses' === $post_type && is_admin() ) {
			$args['template'] = upskill_course_block_template();
		}

		return $args;
	},
	10,
	2
);

/**
 * LearnDash's session list on the course view (learner with access): session
 * titles without the "Session:" prefix, as the product page's Course Content
 * block prints them (upskill_session_title()). Only while that list renders;
 * the session posts keep their titles.
 */
add_action(
	'learndash-course-content-list-before',
	static function () {
		add_filter( 'the_title', 'upskill_strip_session_prefix', 10, 2 );
	}
);

add_action(
	'learndash-course-content-list-after',
	static function () {
		remove_filter( 'the_title', 'upskill_strip_session_prefix', 10 );
	}
);

if ( ! function_exists( 'upskill_strip_session_prefix' ) ) {
	/**
	 * The title without a leading "Session:" for sessions (LearnDash lessons).
	 *
	 * @param string $title   Title.
	 * @param int    $post_id Post ID.
	 * @return string
	 */
	function upskill_strip_session_prefix( $title, $post_id = 0 ) {
		if ( $post_id && 'sfwd-lessons' === get_post_type( $post_id ) ) {
			return (string) preg_replace( '/^\s*session\s*:\s*/i', '', (string) $title );
		}

		return $title;
	}
}
