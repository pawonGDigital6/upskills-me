<?php
/**
 * Self-hosted webfonts.
 *
 * The @font-face rules are generated in PHP rather than authored in CSS so the
 * URLs come from get_template_directory_uri() (correct in a subdirectory
 * install), the files stay out of the webpack bundle where they would be
 * content-hashed, and the preload hints point at exactly the same file.
 *
 * Lay Grotesk is the Figma typeface for every text style. The files shipped
 * here are the designer's TRIAL build: 69 glyphs (A-Z a-z 0-9 space ! , . ?).
 * Their unicode-range is limited to exactly those characters, so anything else
 * — apostrophes, &, $, %, dashes — is drawn by the next family in the stack,
 * Geist, instead of a missing-glyph box. Replace the files with the licensed
 * build before launch and widen the range.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'upskill_font_files' ) ) {
	/**
	 * The font faces the theme ships.
	 *
	 * @return array
	 */
	function upskill_font_files() {
		// The trial build's 69 glyphs: space ! , . ? 0-9 A-Z a-z.
		$trial     = 'U+0020-0021, U+002C, U+002E, U+0030-0039, U+003F, U+0041-005A, U+0061-007A';
		$latin     = 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD';
		$latin_ext = 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF';

		$faces = array();

		foreach ( array(
			'regular'  => 400,
			'medium'   => 500,
			'semibold' => 600,
			'bold'     => 700,
		) as $file => $weight ) {
			$faces[] = array(
				'family'  => 'Lay Grotesk',
				'file'    => 'lay-grotesk/laygrotesk-trial-' . $file . '.otf',
				'format'  => 'opentype',
				'weight'  => (string) $weight,
				'range'   => $trial,
				// Regular and medium carry almost every line of text.
				'preload' => in_array( $weight, array( 400, 500 ), true ),
			);
		}

		$faces[] = array(
			'family'  => 'Geist',
			'file'    => 'geist/Geist-latin.woff2',
			'format'  => 'woff2',
			'weight'  => '300 700',
			'range'   => $latin,
			'preload' => false,
		);

		$faces[] = array(
			'family'  => 'Geist',
			'file'    => 'geist/Geist-latin-ext.woff2',
			'format'  => 'woff2',
			'weight'  => '300 700',
			'range'   => $latin_ext,
			'preload' => false,
		);

		return $faces;
	}
}

if ( ! function_exists( 'upskill_font_face_css' ) ) {
	/**
	 * The @font-face rules, as CSS.
	 *
	 * @return string
	 */
	function upskill_font_face_css() {
		$base = get_template_directory_uri() . '/fonts/';
		$css  = '';

		foreach ( upskill_font_files() as $font ) {
			$css .= sprintf(
				'@font-face{font-family:"%1$s";font-style:normal;font-weight:%2$s;font-display:swap;src:url("%3$s") format("%4$s");unicode-range:%5$s;}',
				$font['family'],
				$font['weight'],
				esc_url( $base . $font['file'] ),
				$font['format'],
				$font['range']
			);
		}

		return $css;
	}
}

/**
 * Preload the faces every page needs.
 */
function upskill_preload_fonts() {
	$base = get_template_directory_uri() . '/fonts/';

	foreach ( upskill_font_files() as $font ) {
		if ( empty( $font['preload'] ) ) {
			continue;
		}

		printf(
			'<link rel="preload" href="%1$s" as="font" type="font/%2$s" crossorigin>' . "\n",
			esc_url( $base . $font['file'] ),
			'opentype' === $font['format'] ? 'otf' : esc_attr( $font['format'] )
		);
	}
}
add_action( 'wp_head', 'upskill_preload_fonts', 1 );
