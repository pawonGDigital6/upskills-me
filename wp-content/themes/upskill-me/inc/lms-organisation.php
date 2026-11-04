<?php
/**
 * LMS organisations: what a group leader sees.
 *
 * A paid order makes the buyer a LearnDash Group Leader of a group with N seats
 * (Group Registration, Phase 5). The organisation dashboard
 * (template-org-dashboard.php) summarises those groups read-only from
 * LearnDash and Group Registration data: seats, members, course progress.
 * Enrolling, removing and reporting stay in Group Registration's own groups
 * dashboard ([wdm_group_users] on /groups-dashboard/), which is only restyled.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_org_dashboard_page_id' ) ) {
	/**
	 * The page using the organisation dashboard template.
	 *
	 * @return int
	 */
	function upskill_org_dashboard_page_id() {
		static $id = null;

		if ( null === $id ) {
			$pages = get_posts(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'numberposts' => 1,
					'fields'      => 'ids',
					'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'  => 'template-org-dashboard.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			$id    = $pages ? (int) $pages[0] : 0;
		}

		return $id;
	}
}

if ( ! function_exists( 'upskill_groups_dashboard_url' ) ) {
	/**
	 * Group Registration's groups dashboard (enrol and manage people). It picks
	 * a group from a POSTed wdm_group_id, so links to one group are small forms
	 * (template-org-dashboard.php).
	 *
	 * @return string
	 */
	function upskill_groups_dashboard_url() {
		$page = function_exists( 'ldgr_get_groups_dashboard_page' ) ? (int) ldgr_get_groups_dashboard_page() : 0;
		$url  = $page ? get_permalink( $page ) : home_url( '/groups-dashboard/' );

		return $url;
	}
}

if ( ! function_exists( 'upskill_org_groups' ) ) {
	/**
	 * Groups the user leads.
	 *
	 * @param int $user_id User ID.
	 * @return int[]
	 */
	function upskill_org_groups( $user_id ) {
		if ( ! function_exists( 'learndash_get_administrators_group_ids' ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'intval', (array) learndash_get_administrators_group_ids( $user_id ) ), static fn( $id ) => 'publish' === get_post_status( $id ) ) );
	}
}

if ( ! function_exists( 'upskill_org_progress' ) ) {
	/**
	 * One member's progress in one course.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return array { percent (int), status: not_started|in_progress|completed, activity (int timestamp) }
	 */
	function upskill_org_progress( $user_id, $course_id ) {
		$status  = function_exists( 'learndash_course_status' ) ? learndash_course_status( $course_id, $user_id, true ) : 'not_started';
		$total   = (int) learndash_get_course_steps_count( $course_id );
		$done    = (int) learndash_course_get_completed_steps( $user_id, $course_id );
		$percent = 'completed' === $status ? 100 : ( $total ? (int) round( 100 * min( $done, $total ) / $total ) : 0 );
		$touched = (int) get_user_meta( $user_id, 'learndash_course_' . $course_id . '_enrolled_at', true );
		$last    = function_exists( 'learndash_activity_course_get_latest_completed_step' ) ? (array) learndash_activity_course_get_latest_completed_step( $user_id, $course_id ) : array();

		return array(
			'percent'  => $percent,
			'status'   => in_array( $status, array( 'completed', 'in_progress' ), true ) ? $status : ( $done ? 'in_progress' : 'not_started' ),
			'activity' => ! empty( $last['activity_completed'] ) ? (int) $last['activity_completed'] : $touched,
		);
	}
}

if ( ! function_exists( 'upskill_org_group_summary' ) ) {
	/**
	 * Seats, members and completion for one group.
	 *
	 * @param int $group_id Group ID.
	 * @return array { id, title, courses (int[]), members (int[]), seats, used, remaining, unlimited, completion (int %), rows (array[]) }
	 */
	function upskill_org_group_summary( $group_id ) {
		$courses   = array_map( 'intval', (array) learndash_group_enrolled_courses( $group_id ) );
		$members   = array_map( 'intval', (array) learndash_get_groups_user_ids( $group_id ) );
		$unlimited = (bool) get_post_meta( $group_id, 'ldgr_unlimited_seats', true );
		$total     = (int) get_post_meta( $group_id, 'wdm_group_total_users_limit_' . $group_id, true );
		$left      = (int) get_post_meta( $group_id, 'wdm_group_users_limit_' . $group_id, true );
		$seats     = $total ? $total : count( $members ) + $left;
		$rows      = array();
		$sum       = 0;

		foreach ( $members as $member ) {
			foreach ( $courses as $course ) {
				$progress = upskill_org_progress( $member, $course );
				$sum     += $progress['percent'];
				$rows[]   = array_merge( $progress, array( 'user' => $member, 'course' => $course ) );
			}
		}

		return array(
			'id'         => (int) $group_id,
			'title'      => get_the_title( $group_id ),
			'courses'    => $courses,
			'members'    => $members,
			'seats'      => $seats,
			'used'       => count( $members ),
			'remaining'  => max( 0, $unlimited ? 0 : $seats - count( $members ) ),
			'unlimited'  => $unlimited,
			'completion' => $rows ? (int) round( $sum / count( $rows ) ) : 0,
			'rows'       => $rows,
		);
	}
}

if ( ! function_exists( 'upskill_org_dashboard_guard' ) ) {
	/**
	 * The organisation dashboard is for group leaders (and administrators):
	 * visitors go to log in, other signed-in people to their learning.
	 *
	 * @return void
	 */
	function upskill_org_dashboard_guard() {
		if ( ! is_page_template( array( 'template-org-dashboard.php', 'template-org-training.php' ) ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( get_permalink() ) );
			exit;
		}

		$user = wp_get_current_user();

		if ( ! current_user_can( 'manage_options' ) && ! in_array( 'group_leader', (array) $user->roles, true ) ) {
			wp_safe_redirect( function_exists( 'upskill_learner_dashboard_url' ) ? upskill_learner_dashboard_url() : home_url( '/' ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'upskill_org_dashboard_guard' );

if ( ! function_exists( 'upskill_group_registration_needs_assets' ) ) {
	/**
	 * Keep LearnDash's scripts (and jQuery) on pages with a Group Registration
	 * shortcode ([wdm_group_users], [ldgr_…]): its dashboard uses them, and
	 * inc/learndash.php only recognises LearnDash's own shortcodes.
	 *
	 * @param bool $needs Whether the view renders LearnDash content.
	 * @return bool
	 */
	function upskill_group_registration_needs_assets( $needs ) {
		if ( $needs || ! is_singular() ) {
			return $needs;
		}

		return (bool) preg_match( '/\[(wdm_|ldgr)/', (string) get_post_field( 'post_content', get_queried_object_id() ) );
	}
}
add_filter( 'upskill_needs_learndash_assets', 'upskill_group_registration_needs_assets' );

if ( ! function_exists( 'upskill_org_course_groups' ) ) {
	/**
	 * The groups a user leads that hold a course, for the course page's
	 * "Manage team" / "Buy more seats" bar and its add-seats buy box.
	 *
	 * Each group's level is the WooCommerce variation of the order that
	 * created it (Group Registration keeps the order IDs on the group), so
	 * extra seats are bought at the level the group already has. Group
	 * Registration itself re-checks every add-seats purchase.
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return array[] upskill_org_group_summary() fields (no rows) plus
	 *                 product_id and variation_id (0 when unknown).
	 */
	function upskill_org_course_groups( $user_id, $course_id ) {
		$groups = array();

		foreach ( upskill_org_groups( $user_id ) as $group_id ) {
			if ( ! in_array( (int) $course_id, array_map( 'intval', (array) learndash_group_enrolled_courses( $group_id ) ), true ) ) {
				continue;
			}

			$summary = upskill_org_group_summary( $group_id );
			unset( $summary['rows'] );
			$summary = array_merge( $summary, upskill_org_group_purchase( $group_id ) );

			$groups[] = $summary;
		}

		return $groups;
	}
}

if ( ! function_exists( 'upskill_org_training_page_id' ) ) {
	/**
	 * The page using the organisation "Courses & seats" template (the
	 * organisation's full training and team lists, under the dashboard).
	 *
	 * @return int
	 */
	function upskill_org_training_page_id() {
		static $id = null;

		if ( null === $id ) {
			$pages = get_posts(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'numberposts' => 1,
					'fields'      => 'ids',
					'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'  => 'template-org-training.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			$id    = $pages ? (int) $pages[0] : 0;
		}

		return $id;
	}
}

if ( ! function_exists( 'upskill_org_urls' ) ) {
	/**
	 * Where the organisation pages link to.
	 *
	 * @return array { dashboard, training (the Courses & seats page, else the dashboard), team (its people list), manage (Group Registration's groups dashboard), library }
	 */
	function upskill_org_urls() {
		$dashboard = upskill_org_dashboard_page_id() ? (string) get_permalink( upskill_org_dashboard_page_id() ) : '';
		$training  = upskill_org_training_page_id() ? (string) get_permalink( upskill_org_training_page_id() ) : $dashboard;

		return array(
			'dashboard' => $dashboard,
			'training'  => $training,
			'team'      => $training ? $training . '#org-team' : '',
			'manage'    => upskill_groups_dashboard_url(),
			'library'   => function_exists( 'upskill_library_page_url' ) ? upskill_library_page_url() : home_url( '/courses/' ),
		);
	}
}

if ( ! function_exists( 'upskill_org_group_purchase' ) ) {
	/**
	 * The WooCommerce product and level (variation) a group was bought as:
	 * the first line of its orders (Group Registration keeps their IDs on the
	 * group) that sells one of the group's courses. Seat top-ups are bought at
	 * this level too.
	 *
	 * @param int $group_id Group ID.
	 * @return array { product_id, variation_id, level (label, '' when none) }
	 */
	function upskill_org_group_purchase( $group_id ) {
		$courses = array_map( 'intval', (array) learndash_group_enrolled_courses( $group_id ) );

		foreach ( (array) get_post_meta( $group_id, 'wdm_group_reg_order_id_' . $group_id ) as $order_id ) {
			$order = function_exists( 'wc_get_order' ) ? wc_get_order( (int) $order_id ) : null;

			if ( ! $order ) {
				continue;
			}

			foreach ( $order->get_items() as $item ) {
				$parent = $item instanceof WC_Order_Item_Product ? wc_get_product( $item->get_product_id() ) : null;

				if ( $parent && function_exists( 'upskill_product_course' ) && in_array( (int) upskill_product_course( $parent ), $courses, true ) ) {
					$variation = $item->get_variation_id() ? wc_get_product( $item->get_variation_id() ) : null;

					return array(
						'product_id'   => (int) $item->get_product_id(),
						'variation_id' => (int) $item->get_variation_id(),
						'level'        => $variation && function_exists( 'upskill_store_item' ) ? (string) upskill_store_item( $variation )['level'] : '',
					);
				}
			}
		}

		return array(
			'product_id'   => 0,
			'variation_id' => 0,
			'level'        => '',
		);
	}
}

if ( ! function_exists( 'upskill_org_overview' ) ) {
	/**
	 * Everything the organisation dashboard and its Courses & seats page show,
	 * read from LearnDash and Group Registration (nothing is stored):
	 *
	 * - groups: the groups the user leads, newest first, each with seats,
	 *   members, completion, its (first) course, category, summary and level;
	 * - rows: one per member and course of those groups (an "assignment"),
	 *   with progress, status, last activity, the member's name and email and
	 *   the group's level ("cohort"), most recent activity first;
	 * - totals: groups, members (people, once each), assigned (rows),
	 *   in_progress, completed, not_started (rows by status).
	 *
	 * @param int $user_id Group leader.
	 * @return array { groups, rows, totals }
	 */
	function upskill_org_overview( $user_id ) {
		static $cache = array();

		if ( isset( $cache[ $user_id ] ) ) {
			return $cache[ $user_id ];
		}

		$groups  = array();
		$rows    = array();
		$members = array();

		$group_ids = upskill_org_groups( $user_id );
		rsort( $group_ids );

		foreach ( $group_ids as $group_id ) {
			$group    = upskill_org_group_summary( $group_id );
			$purchase = upskill_org_group_purchase( $group_id );
			$course   = $group['courses'] ? (int) $group['courses'][0] : 0;
			$category = $course ? get_the_terms( $course, 'training_category' ) : null;

			$group = array_merge(
				$group,
				$purchase,
				array(
					'course'   => $course,
					'category' => is_array( $category ) ? $category[0]->name : '',
					'summary'  => $course && function_exists( 'upskill_learner_course_summary' ) ? upskill_learner_course_summary( $course ) : '',
				)
			);

			foreach ( $group['rows'] as $row ) {
				$member = get_userdata( $row['user'] );

				if ( ! $member ) {
					continue;
				}

				$rows[]    = array_merge(
					$row,
					array(
						'group' => (int) $group_id,
						'level' => $purchase['level'],
						'name'  => $member->display_name,
						'email' => $member->user_email,
					)
				);
				$members[] = (int) $row['user'];
			}

			unset( $group['rows'] );
			$groups[] = $group;
		}

		usort( $rows, static fn( $a, $b ) => array( $b['activity'], $a['name'] ) <=> array( $a['activity'], $b['name'] ) );

		$status = array_count_values( wp_list_pluck( $rows, 'status' ) );

		$cache[ $user_id ] = array(
			'groups' => $groups,
			'rows'   => $rows,
			'totals' => array(
				'groups'      => count( $groups ),
				'members'     => count( array_unique( $members ) ),
				'assigned'    => count( $rows ),
				'in_progress' => $status['in_progress'] ?? 0,
				'completed'   => $status['completed'] ?? 0,
				'not_started' => $status['not_started'] ?? 0,
			),
		);

		return $cache[ $user_id ];
	}
}

if ( ! function_exists( 'upskill_org_status_badge' ) ) {
	/**
	 * Status pill of the organisation tables (Figma: lilac with a tick for
	 * Active and Completed, amber with a clock for In progress, grey for Not
	 * started).
	 *
	 * @param string $status active|completed|in_progress|not_started.
	 * @return void
	 */
	function upskill_org_status_badge( $status ) {
		$badges = array(
			'active'      => array( __( 'Active', 'upskill-me' ), 'border-[#e5def6] bg-[#f1edfa] text-[#5a3d8c]', 'check' ),
			'completed'   => array( __( 'Completed', 'upskill-me' ), 'border-[#e5def6] bg-[#f1edfa] text-[#5a3d8c]', 'check' ),
			'in_progress' => array( __( 'In progress', 'upskill-me' ), 'border-[#f0dfb8] bg-[#fff6e5] text-[#7a5410]', 'clock' ),
			'not_started' => array( __( 'Not started', 'upskill-me' ), 'border-line bg-gray-25 text-gray-500', '' ),
		);
		$badge  = $badges[ $status ] ?? $badges['not_started'];
		?>
		<span class="org-badge inline-flex items-center gap-1.5 rounded-full border px-[11px] py-1 text-sm leading-[1.6] font-medium whitespace-nowrap <?php echo esc_attr( $badge[1] ); ?>">
			<?php if ( $badge[2] ) : ?>
				<?php upskill_icon( $badge[2], 'size-[11px]' ); ?>
			<?php endif; ?>
			<?php echo esc_html( $badge[0] ); ?>
		</span>
		<?php
	}
}

if ( ! function_exists( 'upskill_org_team_form' ) ) {
	/**
	 * A link-like button that opens one group in Group Registration's groups
	 * dashboard (it reads the group from a POSTed wdm_group_id, so this is a
	 * small form): "Enrol", "View User", "View".
	 *
	 * @param int    $group_id Group ID.
	 * @param string $label    Visible label.
	 * @param string $style    link (brand text with arrow) | primary | light (pill buttons).
	 * @param string $a11y     Extra screen-reader text (e.g. the person's name).
	 * @return void
	 */
	function upskill_org_team_form( $group_id, $label, $style = 'link', $a11y = '' ) {
		$url = upskill_groups_dashboard_url();

		if ( ! $url || ! $group_id ) {
			return;
		}
		?>
		<form class="org-team-form inline-flex" action="<?php echo esc_url( $url ); ?>" method="post">
			<input type="hidden" name="wdm_group_id" value="<?php echo esc_attr( $group_id ); ?>">
			<?php if ( 'link' === $style ) : ?>
				<button class="group inline-flex cursor-pointer items-center gap-[7px] text-sm font-medium whitespace-nowrap text-brand-700 hover:underline" type="submit">
					<?php echo esc_html( $label ); ?>
					<?php if ( $a11y ) : ?>
						<span class="screen-reader-text"><?php echo esc_html( $a11y ); ?></span>
					<?php endif; ?>
					<?php upskill_icon( 'arrow-up-right', 'size-[11px] transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5' ); ?>
				</button>
			<?php else : ?>
				<button class="btn <?php echo 'primary' === $style ? 'btn--primary' : 'btn--light'; ?> btn--sm" type="submit">
					<span class="btn__label"><?php echo esc_html( $label ); ?></span>
					<span class="btn__nest" aria-hidden="true"><?php upskill_icon( 'arrow-up-right', 'btn__icon' ); ?></span>
				</button>
			<?php endif; ?>
		</form>
		<?php
	}
}
