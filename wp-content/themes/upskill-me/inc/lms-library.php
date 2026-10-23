<?php
/**
 * LMS library: the public training catalogue with search and filters.
 *
 * Lists published LearnDash courses (what is sold and assigned), filtered by
 * the course lists Industry, Category and Level (inc/taxonomies.php) and by
 * "Free training" (a course with a LearnDash Sample session). Filters are
 * plain GET parameters, so every result set has a shareable URL and works
 * without JavaScript; with it, the page asks the REST route below for the
 * same results and updates in place:
 *
 *   ?q=text&industry[]=slug&category[]=slug&level[]=slug&free=1&sort=newest|title&show=12
 *
 * Within a list the choices are alternatives (OR); across lists they narrow
 * (AND). Each card describes the course through its first session (code,
 * duration, video), since every course is currently one session (Q4).
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_library_facets' ) ) {
	/**
	 * The filter lists: query key => [ taxonomy, label ].
	 *
	 * @return array
	 */
	function upskill_library_facets() {
		return array(
			'industry' => array( 'industry', __( 'Industry', 'upskill-me' ) ),
			'category' => array( 'training_category', __( 'Category', 'upskill-me' ) ),
			'level'    => array( 'cohort', __( 'Level / Cohort', 'upskill-me' ) ),
		);
	}
}

if ( ! function_exists( 'upskill_library_request' ) ) {
	/**
	 * The current filters, sanitised.
	 *
	 * @param array|null $source Raw parameters (slashed, as in $_GET); defaults to $_GET.
	 * @return array { q, industry[], category[], level[], free (bool), sort, show (int) }
	 */
	function upskill_library_request( $source = null ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public, read-only filters.
		$source  = null === $source ? $_GET : $source;
		$request = array(
			'q'    => isset( $source['q'] ) && is_string( $source['q'] ) ? sanitize_text_field( wp_unslash( $source['q'] ) ) : '',
			'free' => ! empty( $source['free'] ),
			'sort' => isset( $source['sort'] ) && in_array( $source['sort'], array( 'newest', 'title' ), true ) ? sanitize_key( $source['sort'] ) : 'newest',
			'show' => isset( $source['show'] ) ? min( 120, max( upskill_library_per_page(), absint( $source['show'] ) ) ) : upskill_library_per_page(),
		);

		foreach ( array_keys( upskill_library_facets() ) as $key ) {
			$request[ $key ] = isset( $source[ $key ] ) ? array_values( array_filter( array_map( 'sanitize_title', (array) wp_unslash( $source[ $key ] ) ) ) ) : array();
		}
		// phpcs:enable

		return $request;
	}
}

if ( ! function_exists( 'upskill_library_page_url' ) ) {
	/**
	 * The library page's address (the page using template-library.php).
	 *
	 * @return string Empty when no page uses the template.
	 */
	function upskill_library_page_url() {
		static $url = null;

		if ( null === $url ) {
			$pages = get_posts(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'numberposts' => 1,
					'fields'      => 'ids',
					'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'  => 'template-library.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			$url   = $pages ? (string) get_permalink( $pages[0] ) : '';
		}

		return $url;
	}
}

if ( ! function_exists( 'upskill_library_url' ) ) {
	/**
	 * The library address for a request, with some values changed.
	 *
	 * @param array $request upskill_library_request().
	 * @param array $changes Values to change (e.g. [ 'show' => 12 ]).
	 * @return string
	 */
	function upskill_library_url( $request, $changes = array() ) {
		$args = array_merge(
			array(
				'q'        => $request['q'],
				'industry' => $request['industry'],
				'category' => $request['category'],
				'level'    => $request['level'],
				'free'     => $request['free'] ? 1 : '',
				'sort'     => 'newest' === $request['sort'] ? '' : $request['sort'],
			),
			$changes
		);

		return add_query_arg( array_map( 'rawurlencode_deep', array_filter( $args ) ), upskill_library_page_url() );
	}
}

if ( ! function_exists( 'upskill_library_labels' ) ) {
	/**
	 * The words around the results that change with the filters.
	 *
	 * @param array $request upskill_library_request().
	 * @param int   $total   Number of matching courses.
	 * @return array { heading, total, show }
	 */
	function upskill_library_labels( $request, $total ) {
		return array(
			/* translators: %s: search words. */
			'heading' => '' !== $request['q'] ? sprintf( __( 'Results for “%s”', 'upskill-me' ), $request['q'] ) : __( 'Featured training', 'upskill-me' ),
			/* translators: %s: number of training sessions. */
			'total'   => sprintf( _n( '%s training session', '%s training sessions', $total, 'upskill-me' ), number_format_i18n( $total ) ),
			/* translators: %d: number of results. */
			'show'    => sprintf( _n( 'Show %d result', 'Show %d results', $total, 'upskill-me' ), $total ),
		);
	}
}

if ( ! function_exists( 'upskill_library_rest' ) ) {
	/**
	 * GET /wp-json/upskill/v1/library: the same filters as the page, answered
	 * with the result cards' HTML, the labels and the filter counts, so
	 * js/components/library.js can update the page without reloading it.
	 *
	 * @return void
	 */
	function upskill_library_rest() {
		register_rest_route(
			'upskill/v1',
			'/library',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'permission_callback' => '__return_true',
				'callback'            => static function ( WP_REST_Request $rest ) {
					$request = upskill_library_request( wp_slash( $rest->get_query_params() ) );
					$results = upskill_library_results( $request );
					$total   = (int) $results->found_posts;

					ob_start();
					get_template_part( 'template-parts/library/results', null, array( 'request' => $request, 'results' => $results, 'total' => $total ) );

					return rest_ensure_response(
						array(
							'html'   => ob_get_clean(),
							'total'  => $total,
							'labels' => upskill_library_labels( $request, $total ),
							'counts' => upskill_library_counts( $request ),
							'active' => (bool) array_filter( array( $request['industry'], $request['category'], $request['level'], $request['free'] ) ),
						)
					);
				},
			)
		);
	}
}
add_action( 'rest_api_init', 'upskill_library_rest' );

if ( ! function_exists( 'upskill_library_search_redirect' ) ) {
	/**
	 * Site searches (the header search, the Home hero search) go to the
	 * training library, which searches the courses. Searches that name a post
	 * type, and the admin, are left to WordPress.
	 *
	 * @return void
	 */
	function upskill_library_search_redirect() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
		if ( ! is_search() || is_admin() || is_feed() || isset( $_GET['post_type'] ) || '' === upskill_library_page_url() ) {
			return;
		}

		$words = trim( get_search_query( false ) );

		wp_safe_redirect( ( '' !== $words ? add_query_arg( 'q', rawurlencode( $words ), upskill_library_page_url() ) : upskill_library_page_url() ) . '#results' );
		exit;
	}
}
add_action( 'template_redirect', 'upskill_library_search_redirect' );

if ( ! function_exists( 'upskill_library_per_page' ) ) {
	/**
	 * Cards shown before "Load more training" (Figma shows two rows of three).
	 *
	 * @return int
	 */
	function upskill_library_per_page() {
		return (int) apply_filters( 'upskill_library_per_page', 6 );
	}
}

if ( ! function_exists( 'upskill_library_free_course_ids' ) ) {
	/**
	 * Published courses holding at least one Sample (free) session.
	 *
	 * @return int[]
	 */
	function upskill_library_free_course_ids() {
		static $ids = null;

		if ( null !== $ids ) {
			return $ids;
		}

		$ids = array();

		if ( ! function_exists( 'learndash_get_setting' ) ) {
			return $ids;
		}

		$courses = get_posts(
			array(
				'post_type'   => 'sfwd-courses',
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);

		foreach ( $courses as $course_id ) {
			foreach ( upskill_library_course_sessions( $course_id ) as $session_id ) {
				if ( 'on' === learndash_get_setting( $session_id, 'sample_lesson' ) ) {
					$ids[] = (int) $course_id;
					break;
				}
			}
		}

		return $ids;
	}
}

if ( ! function_exists( 'upskill_library_course_sessions' ) ) {
	/**
	 * A course's sessions (LearnDash lessons) in course order.
	 *
	 * @param int $course_id Course ID.
	 * @return int[]
	 */
	function upskill_library_course_sessions( $course_id ) {
		if ( ! class_exists( 'LDLMS_Factory_Post' ) ) {
			return array();
		}

		$steps = LDLMS_Factory_Post::course_steps( $course_id );

		return $steps ? array_map( 'intval', (array) $steps->get_children_steps( $course_id, 'sfwd-lessons' ) ) : array();
	}
}

if ( ! function_exists( 'upskill_library_query_args' ) ) {
	/**
	 * WP_Query arguments for a request; $skip leaves one list out (used for
	 * that list's own counts).
	 *
	 * @param array  $request upskill_library_request().
	 * @param string $skip    Facet key to ignore.
	 * @return array
	 */
	function upskill_library_query_args( $request, $skip = '' ) {
		$args = array(
			'post_type'           => 'sfwd-courses',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false,
			'orderby'             => 'title' === $request['sort'] ? 'title' : 'date',
			'order'               => 'title' === $request['sort'] ? 'ASC' : 'DESC',
			'tax_query'           => array( 'relation' => 'AND' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		);

		if ( '' !== $request['q'] ) {
			$args['s'] = $request['q'];
		}

		foreach ( upskill_library_facets() as $key => $facet ) {
			if ( $key !== $skip && $request[ $key ] ) {
				$args['tax_query'][] = array(
					'taxonomy' => $facet[0],
					'field'    => 'slug',
					'terms'    => $request[ $key ],
				);
			}
		}

		if ( 'free' !== $skip && $request['free'] ) {
			$free             = upskill_library_free_course_ids();
			$args['post__in'] = $free ? $free : array( 0 );
		}

		return $args;
	}
}

if ( ! function_exists( 'upskill_library_results' ) ) {
	/**
	 * The courses for a request, up to the number shown.
	 *
	 * @param array $request upskill_library_request().
	 * @return WP_Query
	 */
	function upskill_library_results( $request ) {
		return new WP_Query( array_merge( upskill_library_query_args( $request ), array( 'posts_per_page' => $request['show'] ) ) );
	}
}

if ( ! function_exists( 'upskill_library_counts' ) ) {
	/**
	 * How many courses each choice would give, with the other lists applied.
	 *
	 * @param array $request upskill_library_request().
	 * @return array facet key => [ term slug => count ], plus 'free' => int.
	 */
	function upskill_library_counts( $request ) {
		$counts = array();

		foreach ( upskill_library_facets() as $key => $facet ) {
			$ids            = get_posts( array_merge( upskill_library_query_args( $request, $key ), array( 'fields' => 'ids', 'numberposts' => -1 ) ) );
			$counts[ $key ] = array();

			foreach ( get_terms( array( 'taxonomy' => $facet[0], 'hide_empty' => false ) ) as $term ) {
				$in_term                       = array_map( 'intval', (array) get_objects_in_term( $term->term_id, $facet[0] ) );
				$counts[ $key ][ $term->slug ] = count( array_intersect( array_map( 'intval', $ids ), $in_term ) );
			}
		}

		$ids            = get_posts( array_merge( upskill_library_query_args( $request, 'free' ), array( 'fields' => 'ids', 'numberposts' => -1 ) ) );
		$counts['free'] = count( array_intersect( $ids, upskill_library_free_course_ids() ) );

		return $counts;
	}
}

if ( ! function_exists( 'upskill_library_next' ) ) {
	/**
	 * Courses to suggest after one is finished: same category first, then the
	 * newest, leaving out courses the user already has.
	 *
	 * @param int $course_id Finished course ID.
	 * @param int $user_id   User ID.
	 * @param int $limit     How many.
	 * @return int[]
	 */
	function upskill_library_next( $course_id, $user_id, $limit = 3 ) {
		$exclude = array_map( 'intval', function_exists( 'learndash_user_get_enrolled_courses' ) ? (array) learndash_user_get_enrolled_courses( $user_id ) : array() );
		$exclude = array_merge( $exclude, array( (int) $course_id ) );
		$terms   = wp_get_post_terms( $course_id, 'training_category', array( 'fields' => 'ids' ) );
		$base    = array(
			'post_type'      => 'sfwd-courses',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => $limit,
			'post__not_in'   => $exclude, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- small catalogue.
		);
		$ids     = $terms && ! is_wp_error( $terms ) ? get_posts( array_merge( $base, array( 'tax_query' => array( array( 'taxonomy' => 'training_category', 'terms' => $terms ) ) ) ) ) : array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

		if ( count( $ids ) < $limit ) {
			$base['posts_per_page'] = $limit - count( $ids );
			$base['post__not_in']   = array_merge( $exclude, $ids );
			$ids                    = array_merge( $ids, get_posts( $base ) );
		}

		return array_map( 'intval', $ids );
	}
}

if ( ! function_exists( 'upskill_library_industry_courses' ) ) {
	/**
	 * Courses for an industry, newest first, topped up with the newest of the
	 * rest so a section always has its full row (Industry Resources block).
	 *
	 * @param int $industry Industry term ID (0 for none).
	 * @param int $count    How many.
	 * @return int[]
	 */
	function upskill_library_industry_courses( $industry, $count = 3 ) {
		$args = array(
			'post_type'           => 'sfwd-courses',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'fields'              => 'ids',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);
		$ids  = array();

		if ( $industry ) {
			$ids = get_posts(
				array_merge(
					$args,
					array(
						'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							array(
								'taxonomy' => 'industry',
								'terms'    => (int) $industry,
							),
						),
					)
				)
			);
		}

		if ( count( $ids ) < $count ) {
			$ids = array_merge(
				$ids,
				get_posts(
					array_merge(
						$args,
						array(
							'posts_per_page' => $count - count( $ids ),
							'post__not_in'   => $ids, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- a handful of IDs.
						)
					)
				)
			);
		}

		return array_map( 'intval', $ids );
	}
}

if ( ! function_exists( 'upskill_library_card' ) ) {
	/**
	 * What a library card shows for a course.
	 *
	 * @param int $course_id Course ID.
	 * @return array
	 */
	function upskill_library_card( $course_id ) {
		$sessions = upskill_library_course_sessions( $course_id );
		$first    = $sessions ? $sessions[0] : 0;
		$settings = $first && function_exists( 'learndash_get_setting' ) ? (array) learndash_get_setting( $first ) : array();
		$free     = in_array( (int) $course_id, upskill_library_free_course_ids(), true );
		$sample   = 0;

		foreach ( $free ? $sessions : array() as $session_id ) {
			if ( 'on' === learndash_get_setting( $session_id, 'sample_lesson' ) ) {
				$sample = $session_id;
				break;
			}
		}
		$category = get_the_terms( $course_id, 'training_category' );
		$image    = get_post_thumbnail_id( $course_id ) ? get_post_thumbnail_id( $course_id ) : ( $first ? get_post_thumbnail_id( $first ) : 0 );

		return array(
			'id'       => (int) $course_id,
			'title'    => get_the_title( $course_id ),
			'summary'  => function_exists( 'upskill_learner_course_summary' ) ? upskill_learner_course_summary( $course_id ) : '',
			'category' => $category && ! is_wp_error( $category ) ? $category[0]->name : '',
			'code'     => $first ? (string) get_field( 'session_code', $first ) : '',
			'duration' => $first ? (string) get_field( 'duration', $first ) : '',
			'free'     => $free,
			'image'    => (int) $image,
			'video'    => ! empty( $settings['lesson_video_enabled'] ) && ! empty( $settings['lesson_video_url'] ) ? (string) $settings['lesson_video_url'] : '',
			// A free session opens straight away; anything else opens its course.
			'url'      => $sample ? learndash_get_step_permalink( $sample, $course_id ) : get_permalink( $course_id ),
		);
	}
}
