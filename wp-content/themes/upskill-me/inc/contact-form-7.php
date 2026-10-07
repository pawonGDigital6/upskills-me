<?php
/**
 * Contact Form 7 integration.
 *
 * The forms are styled by src/global/scss/plugins/_contact-form-7.scss, so the
 * plugin's own stylesheet and automatic paragraphs are switched off. Form
 * templates mark a field's icon with <i data-icon="name"></i>; it is replaced
 * here with the theme's SVG (images/icons/<name>.svg) in the grey disc, or,
 * given data-class, with the bare SVG carrying that class (button arrows).
 *
 * @package upskill-me
 */

add_filter( 'wpcf7_load_css', '__return_false' );
add_filter( 'wpcf7_autop_or_not', '__return_false' );

add_filter(
	'wpcf7_form_elements',
	static function ( $html ) {
		return preg_replace_callback(
			'/<i data-icon="([a-z0-9-]+)"(?: data-class="([a-z0-9_ -]+)")?><\/i>/',
			static function ( $match ) {
				if ( ! empty( $match[2] ) ) {
					return upskill_get_icon( $match[1], $match[2] );
				}

				return '<span class="wpcf7-field-icon" aria-hidden="true">' . upskill_get_icon( $match[1], 'size-[18px]' ) . '</span>';
			},
			$html
		);
	}
);
