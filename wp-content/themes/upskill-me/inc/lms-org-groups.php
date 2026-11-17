<?php
/**
 * Organisation groups (call 13 Nov, item 5): coordinators make their own
 * groups, e.g. RSL Queensland's branches (Chermside, Gaythorne, Bribie
 * Island…) or a small business's groundskeepers, cleaners and sales team.
 *
 * Seats and enrolment stay with Group Registration: each purchase is still a
 * LearnDash group that holds the seats and gives the course. A coordinator's
 * "Group" only gathers people they have already enrolled, from any purchase
 * and at any level, so one purchase's seats can be spread over several
 * groups, and a group can mix workers, team leaders, managers and directors.
 * Its figures (people, seats used, progress, completions) are the
 * organisation dashboard's own rows (upskill_org_overview()) for its people,
 * so groups add up to the organisation's totals.
 *
 * Stored as a private post type (`upskill_team`, author = the coordinator,
 * members in `_upskill_team_members`), so it never appears in Group
 * Registration's screens or gives anyone a course. Screens: the "Groups" page
 * (template-org-groups.php) and a summary on the organisation dashboard.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const UPSKILL_TEAM_TYPE    = 'upskill_team';
const UPSKILL_TEAM_MEMBERS = '_upskill_team_members';

add_action(
	'init',
	static function () {
		register_post_type(
			UPSKILL_TEAM_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Organisation groups', 'upskill-me' ),
					'singular_name' => __( 'Organisation group', 'upskill-me' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				// Administrators see them under LearnDash LMS; coordinators use the Groups page.
				'show_ui'             => current_user_can( 'manage_options' ),
				'show_in_menu'        => 'learndash-lms',
				'show_in_rest'        => false,
				'supports'            => array( 'title', 'author' ),
				'capability_type'     => 'post',
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
				'rewrite'             => false,
				'query_var'           => false,
			)
		);
	}
);

if ( ! function_exists( 'upskill_org_groups_page_id' ) ) {
	/**
	 * The page using the "Organisation groups" template, or 0.
	 *
	 * @return int
	 */
	function upskill_org_groups_page_id() {
		static $id = null;

		if ( null === $id ) {
			$pages = get_posts(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'numberposts' => 1,
					'fields'      => 'ids',
					'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'  => 'template-org-groups.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			$id    = $pages ? (int) $pages[0] : 0;
		}

		return $id;
	}
}

if ( ! function_exists( 'upskill_org_groups_url' ) ) {
	/**
	 * The Groups page, optionally one group on it.
	 *
	 * @param int $team_id Group to open (0: the list).
	 * @return string '' when there is no Groups page.
	 */
	function upskill_org_groups_url( $team_id = 0 ) {
		$page = upskill_org_groups_page_id();

		if ( ! $page ) {
			return '';
		}

		return $team_id ? add_query_arg( 'group', (int) $team_id, get_permalink( $page ) ) : (string) get_permalink( $page );
	}
}

if ( ! function_exists( 'upskill_org_people' ) ) {
	/**
	 * Everyone the coordinator has enrolled, once each, with their levels.
	 *
	 * @param int $user_id Coordinator.
	 * @return array user ID => { id, name, email, levels (string[]) }, by name.
	 */
	function upskill_org_people( $user_id ) {
		$people = array();

		foreach ( upskill_org_overview( $user_id )['rows'] as $row ) {
			$id = (int) $row['user'];

			if ( ! isset( $people[ $id ] ) ) {
				$people[ $id ] = array(
					'id'     => $id,
					'name'   => $row['name'],
					'email'  => $row['email'],
					'levels' => array(),
				);
			}

			if ( $row['level'] && ! in_array( $row['level'], $people[ $id ]['levels'], true ) ) {
				$people[ $id ]['levels'][] = $row['level'];
			}
		}

		uasort( $people, static fn( $a, $b ) => strcasecmp( $a['name'], $b['name'] ) );

		return $people;
	}
}

if ( ! function_exists( 'upskill_org_team_ids' ) ) {
	/**
	 * The coordinator's groups, by name.
	 *
	 * @param int $user_id Coordinator.
	 * @return int[]
	 */
	function upskill_org_team_ids( $user_id ) {
		if ( ! $user_id ) {
			return array();
		}

		return array_map(
			'intval',
			get_posts(
				array(
					'post_type'   => UPSKILL_TEAM_TYPE,
					'post_status' => 'private',
					'author'      => (int) $user_id,
					'numberposts' => -1,
					'orderby'     => 'title',
					'order'       => 'ASC',
					'fields'      => 'ids',
				)
			)
		);
	}
}

if ( ! function_exists( 'upskill_org_team_owned' ) ) {
	/**
	 * Is this one of the coordinator's groups?
	 *
	 * @param int $team_id Group.
	 * @param int $user_id Coordinator.
	 * @return bool
	 */
	function upskill_org_team_owned( $team_id, $user_id ) {
		$team = get_post( (int) $team_id );

		return $team && UPSKILL_TEAM_TYPE === $team->post_type && 'private' === $team->post_status && (int) $team->post_author === (int) $user_id;
	}
}

if ( ! function_exists( 'upskill_org_team' ) ) {
	/**
	 * One group with its figures: its people who are still enrolled with the
	 * coordinator, their rows (one per person and course, as on the
	 * dashboard), seats used (its people), and the status counts.
	 *
	 * @param int $team_id Group.
	 * @param int $user_id Coordinator.
	 * @return array|null { id, title, members (int[]), people, rows, assigned, completed, in_progress, not_started, completion (int %) }
	 */
	function upskill_org_team( $team_id, $user_id ) {
		if ( ! upskill_org_team_owned( $team_id, $user_id ) ) {
			return null;
		}

		$people  = upskill_org_people( $user_id );
		$members = array_values( array_intersect( array_filter( array_map( 'intval', (array) get_post_meta( $team_id, UPSKILL_TEAM_MEMBERS, true ) ) ), array_keys( $people ) ) );
		$rows    = array_values( array_filter( upskill_org_overview( $user_id )['rows'], static fn( $row ) => in_array( (int) $row['user'], $members, true ) ) );
		$status  = array_count_values( wp_list_pluck( $rows, 'status' ) );

		return array(
			'id'          => (int) $team_id,
			// The plain title: get_the_title() prefixes private posts with "Private:".
			'title'       => (string) get_post_field( 'post_title', $team_id, 'raw' ),
			'members'     => $members,
			'people'      => count( $members ),
			'rows'        => $rows,
			'assigned'    => count( $rows ),
			'completed'   => $status['completed'] ?? 0,
			'in_progress' => $status['in_progress'] ?? 0,
			'not_started' => $status['not_started'] ?? 0,
			'completion'  => $rows ? (int) round( array_sum( wp_list_pluck( $rows, 'percent' ) ) / count( $rows ) ) : 0,
		);
	}
}

if ( ! function_exists( 'upskill_org_team_notice' ) ) {
	/**
	 * The message after a change on the Groups page (?notice=).
	 *
	 * @param string $key Notice key.
	 * @return string
	 */
	function upskill_org_team_notice( $key ) {
		$notices = array(
			'created' => __( 'Group created. Add people to it below.', 'upskill-me' ),
			'renamed' => __( 'Group renamed.', 'upskill-me' ),
			'deleted' => __( 'Group deleted. Its people keep their training and seats.', 'upskill-me' ),
			'added'   => __( 'People added to the group.', 'upskill-me' ),
			'removed' => __( 'Removed from the group. They keep their training and seat.', 'upskill-me' ),
			'name'    => __( 'Please give the group a name.', 'upskill-me' ),
			'none'    => __( 'Choose at least one person to add.', 'upskill-me' ),
		);

		return $notices[ $key ] ?? '';
	}
}

/**
 * Changes from the Groups page (create, rename, delete, add people, remove a
 * person). Only the coordinator who owns a group can change it, and only
 * people they have enrolled can join it.
 */
add_action(
	'admin_post_upskill_org_team',
	static function () {
		$user_id = get_current_user_id();

		if ( ! $user_id || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'upskill_org_team' ) ) {
			wp_die( esc_html__( 'This link has expired. Please go back and try again.', 'upskill-me' ), '', array( 'response' => 403 ) );
		}

		$op      = isset( $_POST['op'] ) ? sanitize_key( wp_unslash( $_POST['op'] ) ) : '';
		$team_id = isset( $_POST['team'] ) ? absint( $_POST['team'] ) : 0;
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$notice  = '';

		if ( 'create' !== $op && ! upskill_org_team_owned( $team_id, $user_id ) ) {
			wp_die( esc_html__( 'You can only change your own groups.', 'upskill-me' ), '', array( 'response' => 403 ) );
		}

		switch ( $op ) {
			case 'create':
				if ( '' === $name ) {
					$notice = 'name';
					break;
				}
				$team_id = (int) wp_insert_post(
					array(
						'post_type'   => UPSKILL_TEAM_TYPE,
						'post_status' => 'private',
						'post_title'  => $name,
						'post_author' => $user_id,
					)
				);
				$notice  = $team_id ? 'created' : '';
				break;

			case 'rename':
				if ( '' === $name ) {
					$notice = 'name';
					break;
				}
				wp_update_post(
					array(
						'ID'         => $team_id,
						'post_title' => $name,
					)
				);
				$notice = 'renamed';
				break;

			case 'delete':
				wp_delete_post( $team_id, true );
				$team_id = 0;
				$notice  = 'deleted';
				break;

			case 'add':
				$chosen  = isset( $_POST['people'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['people'] ) ) : array();
				$allowed = array_intersect( $chosen, array_keys( upskill_org_people( $user_id ) ) );

				if ( ! $allowed ) {
					$notice = 'none';
					break;
				}
				$members = array_filter( array_map( 'intval', (array) get_post_meta( $team_id, UPSKILL_TEAM_MEMBERS, true ) ) );
				update_post_meta( $team_id, UPSKILL_TEAM_MEMBERS, array_values( array_unique( array_merge( $members, $allowed ) ) ) );
				$notice = 'added';
				break;

			case 'remove':
				$person  = isset( $_POST['person'] ) ? absint( $_POST['person'] ) : 0;
				$members = array_filter( array_map( 'intval', (array) get_post_meta( $team_id, UPSKILL_TEAM_MEMBERS, true ) ) );
				update_post_meta( $team_id, UPSKILL_TEAM_MEMBERS, array_values( array_diff( $members, array( $person ) ) ) );
				$notice = 'removed';
				break;
		}

		$url = upskill_org_groups_url( $team_id );
		wp_safe_redirect( add_query_arg( 'notice', $notice, $url ? $url : home_url( '/' ) ) );
		exit;
	}
);

if ( ! function_exists( 'upskill_org_team_fields' ) ) {
	/**
	 * Hidden fields every Groups form sends.
	 *
	 * @param string $op      create|rename|delete|add|remove.
	 * @param int    $team_id Group (0 to create).
	 */
	function upskill_org_team_fields( $op, $team_id = 0 ) {
		?>
		<input type="hidden" name="action" value="upskill_org_team">
		<input type="hidden" name="op" value="<?php echo esc_attr( $op ); ?>">
		<input type="hidden" name="team" value="<?php echo esc_attr( (int) $team_id ); ?>">
		<?php
		wp_nonce_field( 'upskill_org_team', '_wpnonce', false );
	}
}
