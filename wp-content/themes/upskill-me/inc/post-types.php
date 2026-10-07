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

	/*
	 * Resources. Blogs are core posts; case studies and guides are their own
	 * types so each keeps its own archive page, card and (later) detail design.
	 * Their single URLs sit beside, not under, the Resources pages
	 * (/case-study/<slug>/) so they never collide with those page slugs.
	 */
	register_post_type(
		'case_study',
		array(
			'labels'        => array(
				'name'          => __( 'Case Studies', 'upskill-me' ),
				'singular_name' => __( 'Case Study', 'upskill-me' ),
				'add_new_item'  => __( 'Add New Case Study', 'upskill-me' ),
				'edit_item'     => __( 'Edit Case Study', 'upskill-me' ),
				'all_items'     => __( 'All Case Studies', 'upskill-me' ),
				'menu_name'     => __( 'Case Studies', 'upskill-me' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'show_in_rest'  => true,
			'rewrite'       => array(
				'slug'       => 'case-study',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 24,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		)
	);

	register_post_type(
		'guide',
		array(
			'labels'        => array(
				'name'          => __( 'Guides', 'upskill-me' ),
				'singular_name' => __( 'Guide', 'upskill-me' ),
				'add_new_item'  => __( 'Add New Guide', 'upskill-me' ),
				'edit_item'     => __( 'Edit Guide', 'upskill-me' ),
				'all_items'     => __( 'All Guides', 'upskill-me' ),
				'menu_name'     => __( 'Guides', 'upskill-me' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'show_in_rest'  => true,
			'rewrite'       => array(
				'slug'       => 'guide',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-book-alt',
			'menu_position' => 25,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
		)
	);

	// Testimonials only ever appear inside the carousel block.
	register_post_type(
		'testimonial',
		array(
			'labels'              => array(
				'name'          => __( 'Testimonials', 'upskill-me' ),
				'singular_name' => __( 'Testimonial', 'upskill-me' ),
				'add_new_item'  => __( 'Add New Testimonial', 'upskill-me' ),
				'edit_item'     => __( 'Edit Testimonial', 'upskill-me' ),
				'all_items'     => __( 'All Testimonials', 'upskill-me' ),
				'menu_name'     => __( 'Testimonials', 'upskill-me' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_rest'        => true,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-format-quote',
			'menu_position'       => 28,
			// Title = the headline, content = the full quote; name and rating are fields.
			'supports'            => array( 'title', 'editor', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'upskill_register_post_types' );
