<?php
/**
 * Training taxonomies.
 *
 * Industry, Category and Level (cohort) classify the training library. They
 * are the filters the Figma design shows on every library, hub and search
 * screen, so they are public taxonomies.
 *
 * They belong to LearnDash courses (`sfwd-courses`), which become the source of
 * truth for training content once LearnDash is installed. Registering against
 * a post type that does not exist yet is allowed, so the vocabularies, their
 * term fields and every block that reads them can be built now. Until LearnDash
 * provides a Courses menu to hang them under, a temporary "Training" menu
 * exposes the term screens (see upskill_training_menu()).
 *
 * The specification's back-office vocabularies (jurisdiction, organisation
 * type, compliance/performance) are not shown anywhere in the design and are
 * deferred to the LearnDash phase.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'UPSKILL_COURSE_POST_TYPE' ) ) {
	// LearnDash's course post type.
	define( 'UPSKILL_COURSE_POST_TYPE', 'sfwd-courses' );
}

/**
 * The training taxonomies: slug => [ singular, plural, rewrite base ].
 *
 * @return array
 */
function upskill_training_taxonomies() {
	return array(
		'industry'          => array( __( 'Industry', 'upskill-me' ), __( 'Industries', 'upskill-me' ), 'industry' ),
		'training_category' => array( __( 'Category', 'upskill-me' ), __( 'Categories', 'upskill-me' ), 'training-category' ),
		'cohort'            => array( __( 'Level', 'upskill-me' ), __( 'Levels', 'upskill-me' ), 'level' ),
	);
}

/**
 * Register the training taxonomies.
 */
function upskill_register_taxonomies() {
	foreach ( upskill_training_taxonomies() as $taxonomy => $names ) {
		list( $singular, $plural, $slug ) = $names;

		register_taxonomy(
			$taxonomy,
			array( UPSKILL_COURSE_POST_TYPE ),
			array(
				'labels'            => array(
					'name'          => $plural,
					'singular_name' => $singular,
					/* translators: %s: taxonomy name. */
					'search_items'  => sprintf( __( 'Search %s', 'upskill-me' ), $plural ),
					/* translators: %s: taxonomy name. */
					'all_items'     => sprintf( __( 'All %s', 'upskill-me' ), $plural ),
					/* translators: %s: taxonomy name. */
					'edit_item'     => sprintf( __( 'Edit %s', 'upskill-me' ), $singular ),
					/* translators: %s: taxonomy name. */
					'add_new_item'  => sprintf( __( 'Add New %s', 'upskill-me' ), $singular ),
					'menu_name'     => $plural,
				),
				'public'            => true,
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => $slug,
					'with_front' => false,
				),
			)
		);
	}
}
add_action( 'init', 'upskill_register_taxonomies', 5 );

/**
 * Temporary admin menu for the term screens while LearnDash is absent.
 *
 * WordPress hangs taxonomy screens under their post type's menu; without the
 * course post type there is nowhere for them to appear. This disappears on its
 * own as soon as LearnDash registers `sfwd-courses`.
 */
function upskill_training_menu() {
	if ( post_type_exists( UPSKILL_COURSE_POST_TYPE ) ) {
		return;
	}

	add_menu_page(
		__( 'Training', 'upskill-me' ),
		__( 'Training', 'upskill-me' ),
		'manage_categories',
		'upskill-training',
		'__return_null',
		'dashicons-welcome-learn-more',
		26
	);

	foreach ( upskill_training_taxonomies() as $taxonomy => $names ) {
		add_submenu_page(
			'upskill-training',
			$names[1],
			$names[1],
			'manage_categories',
			'edit-tags.php?taxonomy=' . $taxonomy
		);
	}

	// The parent only exists to group the term screens.
	remove_submenu_page( 'upskill-training', 'upskill-training' );
}
add_action( 'admin_menu', 'upskill_training_menu' );

/**
 * Keep the temporary menu highlighted while a term screen is open.
 *
 * @param string $parent_file Current parent menu slug.
 * @return string
 */
function upskill_training_menu_parent( $parent_file ) {
	global $taxnow;

	if ( ! post_type_exists( UPSKILL_COURSE_POST_TYPE ) && array_key_exists( (string) $taxnow, upskill_training_taxonomies() ) ) {
		return 'upskill-training';
	}

	return $parent_file;
}
add_filter( 'parent_file', 'upskill_training_menu_parent' );

/**
 * Send training term links to their designed landing page.
 *
 * Industries, categories and levels each have a page built from blocks,
 * chosen on the term ("Landing page"). Filtering the term link itself means
 * menus, blocks and any core output all agree on one address; a term without
 * a landing page keeps its archive URL.
 *
 * @param string  $url  Term link.
 * @param WP_Term $term Term.
 * @return string
 */
function upskill_training_term_link( $url, $term ) {
	if ( ! array_key_exists( $term->taxonomy, upskill_training_taxonomies() ) || ! function_exists( 'get_field' ) ) {
		return $url;
	}

	$page = (int) get_field( 'landing_page', $term );

	return $page && 'publish' === get_post_status( $page ) ? (string) get_permalink( $page ) : $url;
}
add_filter( 'term_link', 'upskill_training_term_link', 10, 2 );

/**
 * Flush rewrite rules once per theme version so new taxonomy bases resolve
 * without a manual Permalinks save.
 */
function upskill_maybe_flush_rewrites() {
	if ( get_option( 'upskill_rewrite_version' ) === UPSKILL_VERSION ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'upskill_rewrite_version', UPSKILL_VERSION );
}
add_action( 'init', 'upskill_maybe_flush_rewrites', 99 );
