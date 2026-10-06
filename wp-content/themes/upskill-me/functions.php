<?php
/**
 * UpSkill Me functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'UPSKILL_VERSION' ) ) {
	// Bump on release. Also keys the one-time rewrite flush in inc/taxonomies.php.
	define( 'UPSKILL_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function upskill_setup() {
	load_theme_textdomain( 'upskill-me', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );

	// Blocks bring their own colour system, so the editor's pickers would only
	// offer ways to break it.
	add_theme_support( 'disable-custom-colors' );
	add_theme_support( 'disable-custom-gradients' );
	add_theme_support( 'editor-color-palette', array() );
	add_theme_support( 'editor-gradient-presets', array() );

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	register_nav_menus(
		array(
			'primary'           => esc_html__( 'Primary', 'upskill-me' ),
			'footer-industries' => esc_html__( 'Footer — Industries', 'upskill-me' ),
			'footer-categories' => esc_html__( 'Footer — Categories', 'upskill-me' ),
			'footer-resources'  => esc_html__( 'Footer — Resources', 'upskill-me' ),
			'footer-company'    => esc_html__( 'Footer — Company', 'upskill-me' ),
			'footer-account'    => esc_html__( 'Footer — Account', 'upskill-me' ),
			'footer-legal'      => esc_html__( 'Footer — Legal', 'upskill-me' ),
		)
	);

	/*
	 * Crops taken from the Figma frames (1440 desktop, rendered at 2x):
	 * - session cards are 16:10 at up to 820px wide,
	 * - cohort cards are 437x534 portraits,
	 * - the hero showcase panels are tall portraits.
	 */
	add_image_size( 'upskill-card', 900, 1100, true );
	add_image_size( 'upskill-wide', 1640, 1024, true );
	add_image_size( 'upskill-portrait', 1100, 1300, true );
}
add_action( 'after_setup_theme', 'upskill_setup' );

/**
 * Set the content width in pixels, based on the theme's design.
 *
 * @global int $content_width
 */
function upskill_content_width() {
	// 1440 artboard with 40px gutters.
	$GLOBALS['content_width'] = apply_filters( 'upskill_content_width', 1360 );
}
add_action( 'after_setup_theme', 'upskill_content_width', 0 );

/**
 * Read a compiled bundle's asset manifest.
 *
 * @param string $entry Entry name inside build/global (index|editor).
 * @return array{version:string,dependencies:array}
 */
function upskill_asset_manifest( $entry ) {
	$file     = get_template_directory() . '/build/global/' . $entry . '.asset.php';
	$manifest = array(
		'version'      => UPSKILL_VERSION,
		'dependencies' => array(),
	);

	if ( file_exists( $file ) ) {
		$manifest = wp_parse_args( require $file, $manifest );
	}

	return $manifest;
}

/**
 * Enqueue front-end scripts and styles.
 */
function upskill_scripts() {
	$uri   = get_template_directory_uri() . '/build/global/';
	$asset = upskill_asset_manifest( 'index' );

	wp_enqueue_style( 'upskill-style', $uri . 'index.css', array(), $asset['version'] );
	wp_add_inline_style( 'upskill-style', upskill_font_face_css() );

	wp_enqueue_script( 'upskill-script', $uri . 'index.js', $asset['dependencies'], $asset['version'], true );
}
add_action( 'wp_enqueue_scripts', 'upskill_scripts' );

/**
 * Mark the document as scripted before first paint.
 *
 * Reveal animations start hidden only under `.js`, so with scripting off (or if
 * the bundle fails) every section is visible with no flash.
 */
function upskill_js_flag() {
	echo '<script>document.documentElement.classList.add("js");</script>' . "\n";
}
add_action( 'wp_head', 'upskill_js_flag', 0 );

/**
 * Enqueue block editor styles.
 */
function upskill_editor_assets() {
	$asset = upskill_asset_manifest( 'editor' );

	wp_enqueue_style( 'upskill-editor-style', get_template_directory_uri() . '/build/global/editor.css', array(), $asset['version'] );
	wp_add_inline_style( 'upskill-editor-style', upskill_font_face_css() );
}
add_action( 'enqueue_block_editor_assets', 'upskill_editor_assets' );

/**
 * Show each ACF block's preview.png in the inserter.
 */
function upskill_admin_assets() {
	wp_enqueue_script(
		'upskill-block-preview',
		get_template_directory_uri() . '/js/acf_block_preview.js',
		array( 'wp-blocks', 'wp-data' ),
		UPSKILL_VERSION,
		true
	);

	wp_localize_script(
		'upskill-block-preview',
		'upskillBlockPreview',
		array( 'templateUrl' => get_template_directory_uri() )
	);
}
add_action( 'admin_enqueue_scripts', 'upskill_admin_assets' );

require get_template_directory() . '/inc/fonts.php';
require get_template_directory() . '/inc/template-helpers.php';
require get_template_directory() . '/inc/taxonomies.php';
require get_template_directory() . '/inc/post-types.php';
require get_template_directory() . '/inc/options.php';
require get_template_directory() . '/inc/nav-menu.php';
require get_template_directory() . '/inc/acf-register-blocks.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/template-tags.php';
