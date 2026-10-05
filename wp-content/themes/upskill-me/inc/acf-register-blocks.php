<?php
/**
 * Class ACF_Block_Register
 *
 * This class handles the registration and management of ACF blocks in WordPress.
 * It sets the block path, registers blocks, reorders block assets, and adds custom block categories.
 */
class ACF_Block_Register {
	private $block_path;
	private $acf_blocks;
	private $block_namespace = 'upskill-acf-block';
	private $text_domain     = 'upskill-me';

	public function __construct() {
		// Set the block path.
		$this->block_path = dirname( __FILE__, 2 ) . '/build/blocks/';
		$this->acf_blocks = array_filter( glob( $this->block_path . '*' ), 'is_dir' );

		// Register actions.
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'reorder_editor_block_assets' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'conditionally_enqueue_block_assets' ), 20 );
		add_filter( 'block_categories_all', array( $this, 'custom_block_category' ), 10, 2 );
	}

	/**
	 * Register ACF Blocks.
	 */
	public function register_blocks() {
		foreach ( $this->acf_blocks as $block ) {
			// Register each block using the directory path.
			register_block_type_from_metadata( $block );
		}
	}

	/**
	 * Get asset handles for all registered ACF blocks.
	 *
	 * @return array Array of block data (block_name, editor_script, editor_style, script, style).
	 */
	private function get_block_data() {
		$block_registry    = WP_Block_Type_Registry::get_instance();
		$registered_blocks = $block_registry->get_all_registered();

		$block_index = 0;

		$block_data = array(
			$block_index => array(
				'block_name'    => '',
				'editor_script' => '',
				'editor_style'  => '',
				'script'        => '',
				'style'         => '',
				'view_style'    => '',
				'view_script'    => '',
			),
		);

		foreach ( $registered_blocks as $block_name => $block_type ) {
			if ( strpos( $block_name, $this->block_namespace ) !== 0 ) {
				continue;
			}

			$block_data[ $block_index ]['block_name'] = $block_name;

			// Check if scripts or styles are defined for the block.
			if ( ! empty( $block_type->editor_script_handles ) ) {
				$block_data[ $block_index ]['editor_script'] = $block_type->editor_script_handles;
			}
			if ( ! empty( $block_type->view_script_handles ) ) {
				$block_data[ $block_index ]['script'] = $block_type->view_script_handles;
			}
			if ( ! empty( $block_type->editor_style_handles ) ) {
				$block_data[ $block_index ]['editor_style'] = $block_type->editor_style_handles;
			}
			if ( ! empty( $block_type->style_handles ) ) {
				$block_data[ $block_index ]['style'] = $block_type->style_handles;
			}
			if ( ! empty( $block_type->view_style_handles ) ) {
				$block_data[ $block_index ]['view_style'] = $block_type->view_style_handles;
			}
			if ( ! empty( $block_type->view_script_handles ) ) {
				$block_data[ $block_index ]['view_script'] = $block_type->view_script_handles;
			}
			$block_index++;
		}

		return $block_data;
	}

	/**
	 * Reorder editor block assets by dequeuing and enqueuing them.
	 */
	public function reorder_editor_block_assets() {
		$blocks = $this->get_block_data();

		foreach ( $blocks as $block ) {
			$script_handle = $block['editor_script'];
			$style_handle  = $block['editor_style'];
	
			wp_dequeue_script( $script_handle );
			wp_enqueue_script( $script_handle );
	
			wp_dequeue_style( $style_handle );
			wp_enqueue_style( $style_handle );
		}
	}

	/**
	 * Reorder front-end block assets by dequeuing and enqueuing them.
	 */
	public function conditionally_enqueue_block_assets() {
		$blocks = $this->get_block_data();

		foreach ( $blocks as $block ) {
			$script_handle     = $block['script'];
			$style_handle      = $block['style'];
			$view_style_handle = $block['view_style'];
			$view_script_handle     = $block['view_script'];


			// Dequeue the block assets.
			wp_dequeue_script( $script_handle );
			wp_dequeue_script( $view_script_handle );
			wp_dequeue_style( $view_style_handle );
			wp_dequeue_style( $style_handle );
			
			// Enqueue the block assets if the block is present on the page.
			if ( has_block( $block['block_name'] ) ) {
				wp_enqueue_script( $script_handle );
				wp_enqueue_script( $view_script_handle );
				wp_enqueue_style( $view_style_handle );
				wp_enqueue_style( $style_handle );
			}
		}
	}

	/**
	 * Add custom block category.
	 */
	public function custom_block_category( $categories, $post ) {
        array_unshift(
            $categories, 
            array(
                'slug'  => 'acf-blocks',
                'title' => __( 'ACF Blocks', $this->text_domain ),
            )
        );
        return $categories;
    }
}

// Instantiate the ACF_Block_Register class
new ACF_Block_Register();
