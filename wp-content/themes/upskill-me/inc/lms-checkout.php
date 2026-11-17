<?php
/**
 * Checkout: the organisation's details (client answer 13 Nov, 4b: "All of
 * these are great, but no jurisdiction").
 *
 * Organisations get their account when they buy, so checkout is their
 * sign-up. Beside WooCommerce's billing contact (name, email, phone, address)
 * it asks for the organisation name (WooCommerce's company field), ABN,
 * organisation type, industry, number of people and website.
 *
 * WooCommerce's own checkout fields: as billing fields, WooCommerce saves them
 * on the order (`_billing_<field>`) and the customer (`billing_<field>`), and
 * fills them in again on the next purchase. They also show on the order
 * screen (Billing) and in the order emails.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_org_detail_fields' ) ) {
	/**
	 * The organisation fields, keyed without the "billing_" prefix.
	 *
	 * Organisation types follow the course data sheet's Lists tab; industries
	 * are the site's Industry terms.
	 *
	 * @return array
	 */
	function upskill_org_detail_fields() {
		$industries = array( '' => __( 'Choose an industry', 'upskill-me' ) );
		$terms      = taxonomy_exists( 'industry' ) ? get_terms(
			array(
				'taxonomy'   => 'industry',
				'hide_empty' => false,
			)
		) : array();

		foreach ( is_array( $terms ) ? $terms : array() as $term ) {
			$industries[ $term->name ] = $term->name;
		}
		$industries['Other'] = __( 'Other', 'upskill-me' );

		return array(
			'abn'      => array(
				'label'        => __( 'ABN', 'upskill-me' ),
				'placeholder'  => __( '11 digits, e.g. 51 824 753 556', 'upskill-me' ),
				'required'     => true,
				'class'        => array( 'form-row-first' ),
				'autocomplete' => 'off',
				'priority'     => 31,
			),
			'org_type' => array(
				'type'     => 'select',
				'label'    => __( 'Organisation type', 'upskill-me' ),
				'required' => true,
				'class'    => array( 'form-row-last' ),
				'options'  => array(
					''           => __( 'Choose a type', 'upskill-me' ),
					'Government' => __( 'Government', 'upskill-me' ),
					'NFP'        => __( 'Not-for-profit', 'upskill-me' ),
					'Private'    => __( 'Private', 'upskill-me' ),
					'Public'     => __( 'Public', 'upskill-me' ),
				),
				'priority' => 32,
			),
			'industry' => array(
				'type'     => 'select',
				'label'    => __( 'Industry', 'upskill-me' ),
				'required' => true,
				'class'    => array( 'form-row-first' ),
				'options'  => $industries,
				'priority' => 33,
			),
			'org_size' => array(
				'type'              => 'number',
				'label'             => __( 'Number of people', 'upskill-me' ),
				'placeholder'       => __( 'e.g. 25', 'upskill-me' ),
				'required'          => true,
				'class'             => array( 'form-row-last' ),
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
				'priority'          => 34,
			),
			'website'  => array(
				'type'        => 'url',
				'label'       => __( 'Website', 'upskill-me' ),
				'placeholder' => 'https://',
				'required'    => false,
				'class'       => array( 'form-row-wide' ),
				'priority'    => 35,
			),
		);
	}
}

if ( ! function_exists( 'upskill_is_valid_abn' ) ) {
	/**
	 * The Australian Business Register's ABN check: 11 digits whose weighted
	 * sum (first digit minus 1) divides by 89.
	 *
	 * @param string $abn ABN, spaces allowed.
	 * @return bool
	 */
	function upskill_is_valid_abn( $abn ) {
		$digits = preg_replace( '/\s+/', '', (string) $abn );

		if ( ! preg_match( '/^\d{11}$/', $digits ) ) {
			return false;
		}

		$weights = array( 10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19 );
		$sum     = 0;

		foreach ( str_split( $digits ) as $i => $digit ) {
			$sum += ( 0 === $i ? (int) $digit - 1 : (int) $digit ) * $weights[ $i ];
		}

		return 0 === $sum % 89;
	}
}

/**
 * The fields at checkout: the organisation fields follow the organisation name
 * (WooCommerce's company field, labelled in inc/lms-store.php). The
 * organisation name and phone are required through WooCommerce's own setting
 * (Customizer → WooCommerce → Checkout: Company name and Phone = Required).
 */
add_filter(
	'woocommerce_billing_fields',
	static function ( $fields ) {
		if ( isset( $fields['billing_company'] ) ) {
			$fields['billing_company']['priority'] = 30;
			$fields['billing_company']['class']    = array( 'form-row-wide' );
		}

		foreach ( upskill_org_detail_fields() as $key => $field ) {
			$fields[ 'billing_' . $key ] = $field;
		}

		return $fields;
	},
	20
);

/**
 * Checkout checks: a valid ABN (spaces allowed, saved without them), a whole
 * number of people, and a website address WooCommerce can link to.
 */
add_action(
	'woocommerce_after_checkout_validation',
	static function ( $data, $errors ) {
		if ( ! empty( $data['billing_abn'] ) && ! upskill_is_valid_abn( $data['billing_abn'] ) ) {
			$errors->add( 'billing_abn_validation', __( 'Please enter a valid 11-digit ABN.', 'upskill-me' ), array( 'id' => 'billing_abn' ) );
		}

		if ( isset( $data['billing_org_size'] ) && '' !== $data['billing_org_size'] && ( ! ctype_digit( (string) $data['billing_org_size'] ) || (int) $data['billing_org_size'] < 1 ) ) {
			$errors->add( 'billing_org_size_validation', __( 'Please enter the number of people as a whole number.', 'upskill-me' ), array( 'id' => 'billing_org_size' ) );
		}

		if ( ! empty( $data['billing_website'] ) && ! wp_http_validate_url( esc_url_raw( $data['billing_website'] ) ) ) {
			$errors->add( 'billing_website_validation', __( 'Please enter a full website address, e.g. https://example.com.au', 'upskill-me' ), array( 'id' => 'billing_website' ) );
		}
	},
	10,
	2
);

add_filter(
	'woocommerce_process_checkout_field_billing_abn',
	static function ( $value ) {
		return preg_replace( '/\s+/', '', (string) $value );
	}
);

add_filter(
	'woocommerce_process_checkout_field_billing_website',
	static function ( $value ) {
		return '' === trim( (string) $value ) ? '' : esc_url_raw( $value );
	}
);

/**
 * Order screen → Billing: the organisation fields beside WooCommerce's own
 * (shown, and editable when the billing details are edited).
 */
add_filter(
	'woocommerce_admin_billing_fields',
	static function ( $fields ) {
		foreach ( upskill_org_detail_fields() as $key => $field ) {
			$fields[ $key ] = array(
				'label'   => $field['label'],
				'show'    => true,
				'type'    => 'select' === ( $field['type'] ?? '' ) ? 'select' : 'text',
				'options' => $field['options'] ?? array(),
			);

			if ( empty( $fields[ $key ]['options'] ) ) {
				unset( $fields[ $key ]['options'] );
			}
		}

		return $fields;
	}
);

/**
 * Order emails (to the organisation and to the site admin): the organisation's
 * details under the order.
 */
add_filter(
	'woocommerce_email_order_meta_fields',
	static function ( $fields, $sent_to_admin, $order ) {
		if ( ! $order instanceof WC_Order ) {
			return $fields;
		}

		// The organisation name is already in the email's billing address.
		foreach ( wp_list_pluck( upskill_org_detail_fields(), 'label' ) as $key => $label ) {
			$value = (string) $order->get_meta( '_billing_' . $key );

			if ( '' !== $value ) {
				$fields[ 'billing_' . $key ] = array(
					'label' => $label,
					'value' => $value,
				);
			}
		}

		return $fields;
	},
	10,
	3
);
