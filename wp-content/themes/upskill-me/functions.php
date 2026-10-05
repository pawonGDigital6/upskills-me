<?php
/**
 * upskill-me functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package upskill-me
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function upskill_me_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on upskill-me, use a find and replace
		* to change 'upskill-me' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'upskill-me', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'upskill-me' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'upskill_me_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'upskill_me_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function upskill_me_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'upskill_me_content_width', 640 );
}
add_action( 'after_setup_theme', 'upskill_me_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function upskill_me_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'upskill-me' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'upskill-me' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'upskill_me_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function upskill_me_scripts()
{

	$path = '/build/global/';

	$version = '';
	$dependencies = array();

	if (file_exists(get_template_directory() . $path . 'index.asset.php')) {
		$version_details = require get_template_directory() . $path . 'index.asset.php';

		if (isset($version_details['version'])) {
			$version = $version_details['version'];
		}

		if (isset($version_details['dependencies'])) {
			$dependencies = array_merge($dependencies, $version_details['dependencies']);
		}
	}

	wp_enqueue_style('upskill-me-style', get_stylesheet_uri(), array(), $version);
	wp_style_add_data('upskill-me-style', 'rtl', 'replace');

	// Theme custom styles.
	wp_register_style('upskill-me-custom-style', get_template_directory_uri() . $path . 'index.css', '', $version);
	wp_enqueue_style('upskill-me-custom-style');
	wp_style_add_data('upskill-me-custom-style', 'rtl', 'replace');

	// Theme custom scripts.
	wp_register_script('upskill-me-custom-script', get_template_directory_uri() . $path . 'index.js', $dependencies, $version, true);
	wp_enqueue_script('upskill-me-custom-script');

}
add_action('wp_enqueue_scripts', 'upskill_me_scripts');

/**
 * Enqueue Block Editor style
 */
function upskill_me_global_editor_style()
{
	$path = '/build/global/';

	$version = '';
	$dependencies = array();

	if (file_exists(get_template_directory() . $path . 'editor.asset.php')) {
		$version_details = require get_template_directory() . $path . 'editor.asset.php';

		if (isset($version_details['version'])) {
			$version = $version_details['version'];
		}

		if (isset($version_details['dependencies'])) {
			$dependencies = array_merge($dependencies, $version_details['dependencies']);
		}
	}

	// Editor custom styles.
	wp_register_style('upskill-me-editor-style', get_template_directory_uri() . $path . 'editor.css', '', $version);
	wp_enqueue_style('upskill-me-editor-style');
	wp_style_add_data('upskill-me-editor-style', 'rtl', 'replace');

}
add_action('enqueue_block_editor_assets', 'upskill_me_global_editor_style');

/**
 * We use WordPress's admin_enqueue_scripts hook to
 * enqueue our custom admin scripts and styles.
 *
 * @link https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
 */
function enqueue_admin_scripts_and_styles()
{
	wp_enqueue_script('admin-scripts', get_stylesheet_directory_uri() . '/js/acf_block_preview.js', array('wp-blocks', 'wp-element', 'wp-hooks'), '', true);

	// We use wp_localize_script to pass data
	wp_localize_script('admin-scripts', 'passed_data', array('templateUrl' => get_stylesheet_directory_uri()));
}

add_action('admin_enqueue_scripts', 'enqueue_admin_scripts_and_styles');

/**
 * Load ACF Blocks.
 */
require get_template_directory() . '/inc/acf-register-blocks.php';
require get_template_directory() . '/inc/acf-block-settings.php';

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

