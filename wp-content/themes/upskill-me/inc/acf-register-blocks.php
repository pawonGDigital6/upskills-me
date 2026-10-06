<?php
/**
 * ACF block registration.
 *
 * Every directory under build/blocks that carries a block.json is registered
 * automatically, so creating src/blocks/<name>/ is the whole job.
 *
 * @package upskill-me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the theme's ACF blocks and keeps their assets off pages that do not
 * use them.
 */
class Upskill_ACF_Blocks {

	/**
	 * Namespace every block registers under. Must match the "name" prefix in
	 * each block.json — the conditional asset loading matches against it.
	 *
	 * @var string
	 */
	private $namespace = 'acf-block';

	/**
	 * Absolute path to the compiled blocks directory.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->path = get_template_directory() . '/build/blocks/';

		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_unused_block_assets' ), 20 );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );
	}

	/**
	 * Register every compiled block directory.
	 *
	 * @return void
	 */
	public function register_blocks() {
		if ( ! is_dir( $this->path ) ) {
			return;
		}

		foreach ( (array) glob( $this->path . '*', GLOB_ONLYDIR ) as $directory ) {
			if ( is_readable( $directory . '/block.json' ) ) {
				register_block_type_from_metadata( $directory );
			}
		}
	}

	/**
	 * Drop the front-end style and script of any theme block not on the page.
	 *
	 * @return void
	 */
	public function dequeue_unused_block_assets() {
		foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $name => $block_type ) {
			if ( 0 !== strpos( $name, $this->namespace . '/' ) || has_block( $name ) ) {
				continue;
			}

			$handles = array_merge(
				(array) $block_type->style_handles,
				(array) $block_type->view_style_handles,
				(array) $block_type->view_script_handles
			);

			foreach ( array_filter( $handles ) as $handle ) {
				wp_dequeue_style( $handle );
				wp_dequeue_script( $handle );
			}
		}
	}

	/**
	 * Add the theme's block category so the blocks group together in the inserter.
	 *
	 * @param array $categories Registered categories.
	 * @return array
	 */
	public function register_block_category( $categories ) {
		array_unshift(
			$categories,
			array(
				'slug'  => 'acf-blocks',
				'title' => __( 'UpSkill Me Blocks', 'upskill-me' ),
			)
		);

		return $categories;
	}
}

new Upskill_ACF_Blocks();

/**
 * Keep ACF's local JSON in the theme so field groups stay in version control.
 *
 * @return string
 */
function upskill_acf_json_save_point() {
	return get_template_directory() . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'upskill_acf_json_save_point' );

/**
 * Load field groups from the theme's acf-json directory.
 *
 * @return array
 */
function upskill_acf_json_load_point() {
	return array( get_template_directory() . '/acf-json' );
}
add_filter( 'acf/settings/load_json', 'upskill_acf_json_load_point' );
