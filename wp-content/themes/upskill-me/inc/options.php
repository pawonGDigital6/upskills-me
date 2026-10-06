<?php
/**
 * ACF Options pages for genuinely global content.
 *
 * The announcement bar, header actions, menu promo card, the "Get a head start"
 * band above the footer and the footer itself are site-wide. They are never
 * read from the current post; upskill_option() is the only accessor.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Site Settings options pages.
 */
function upskill_register_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'Site Settings', 'upskill-me' ),
			'menu_title' => __( 'Site Settings', 'upskill-me' ),
			'menu_slug'  => 'site-settings',
			'capability' => 'edit_theme_options',
			'redirect'   => true,
			'icon_url'   => 'dashicons-admin-settings',
			'position'   => 59,
		)
	);

	foreach ( array(
		'site-settings-header' => __( 'Header', 'upskill-me' ),
		'site-settings-footer' => __( 'Footer', 'upskill-me' ),
	) as $slug => $title ) {
		acf_add_options_sub_page(
			array(
				'page_title'  => $title,
				'menu_title'  => $title,
				'menu_slug'   => $slug,
				'parent_slug' => 'site-settings',
				'capability'  => 'edit_theme_options',
			)
		);
	}
}
add_action( 'acf/init', 'upskill_register_options_pages' );

if ( ! function_exists( 'upskill_social_links' ) ) {
	/**
	 * The social links repeater, normalised.
	 *
	 * @return array List of [ 'network' => slug, 'url' => string, 'label' => string ].
	 */
	function upskill_social_links() {
		$networks = array(
			'linkedin' => __( 'LinkedIn', 'upskill-me' ),
			'youtube'  => __( 'YouTube', 'upskill-me' ),
		);

		$links = array();

		foreach ( (array) upskill_option( 'social_links', array() ) as $row ) {
			if ( empty( $row['url'] ) || empty( $row['network'] ) || ! isset( $networks[ $row['network'] ] ) ) {
				continue;
			}

			$links[] = array(
				'network' => $row['network'],
				'url'     => $row['url'],
				/* translators: %s: social network name. */
				'label'   => sprintf( __( 'UpSkill Me on %s', 'upskill-me' ), $networks[ $row['network'] ] ),
			);
		}

		return $links;
	}
}

if ( ! function_exists( 'upskill_hide_footer_cta' ) ) {
	/**
	 * Has the current page switched off the "Get a head start" band?
	 *
	 * The band sits above the footer on every marketing page in the design and
	 * is absent from the account, cart and checkout screens, so it is on by
	 * default and a page opts out.
	 *
	 * @return bool
	 */
	function upskill_hide_footer_cta() {
		if ( ! is_singular() || ! function_exists( 'get_field' ) ) {
			return false;
		}

		return (bool) get_field( 'hide_footer_cta', get_queried_object_id() );
	}
}
