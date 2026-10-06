<?php
/**
 * Custom post types.
 *
 * FAQs are independent records: the same question appears on the home page,
 * the FAQs page and industry pages, grouped by topic. They have no page of
 * their own, so the type is not public.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the FAQ post type and its topic taxonomy.
 */
function upskill_register_post_types() {
	register_post_type(
		'faq',
		array(
			'labels'              => array(
				'name'          => __( 'FAQs', 'upskill-me' ),
				'singular_name' => __( 'FAQ', 'upskill-me' ),
				'add_new_item'  => __( 'Add New FAQ', 'upskill-me' ),
				'edit_item'     => __( 'Edit FAQ', 'upskill-me' ),
				'all_items'     => __( 'All FAQs', 'upskill-me' ),
				'menu_name'     => __( 'FAQs', 'upskill-me' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-editor-help',
			'menu_position'       => 27,
			// The question is the title, the answer is the content.
			'supports'            => array( 'title', 'editor', 'page-attributes' ),
		)
	);

	register_taxonomy(
		'faq_category',
		array( 'faq' ),
		array(
			'labels'            => array(
				'name'          => __( 'FAQ Topics', 'upskill-me' ),
				'singular_name' => __( 'FAQ Topic', 'upskill-me' ),
				'menu_name'     => __( 'Topics', 'upskill-me' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'hierarchical'      => true,
		)
	);
}
add_action( 'init', 'upskill_register_post_types' );
