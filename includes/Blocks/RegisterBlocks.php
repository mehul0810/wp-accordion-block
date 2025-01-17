<?php
/**
 * Blocks registration class.
 *
 * @package WPAccordionBlock
 */

namespace WPAccordionBlock\Blocks;

/**
 * Class RegisterBlocks
 */
class RegisterBlocks {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'blocks' ) );
	}

	/**
	 * Register blocks and scripts.
	 */
	public function blocks() {
		$blocks = array(
			'demo-block',
		);

		foreach ( $blocks as $block ) {
			register_block_type( WP_ACCORDION_BLOCK_DIR . "/build/blocks/{$block}" );
		}

		add_action( 'admin_enqueue_scripts', array( $this, 'block_scripts' ) );
	}

	/**
	 * Register block scripts and styles.
	 */
	public function block_scripts() {
		/**
		 * Note that in the block json the block name is "wp_accordion_block/demo-block" though you also need to add a suffix
		 * of editor-script to make it work at block editor.
		 */
		$handle = 'wp_accordion_block-demo-block-editor-script';

		$data = get_transient( 'wp_accordion_block-demo-block-data' );

		if ( ! $data ) {
			$response = wp_remote_get( 'https://jsonplaceholder.typicode.com/users' );
			$data     = wp_remote_retrieve_body( $response );
			$data     = json_decode( $data );

			set_transient( 'wp_accordion_block-demo-block-data', $data, 7 * DAY_IN_SECONDS );
		}

		wp_localize_script( $handle, 'wp_accordion_blockDemoBlockData', $data );
	}
}
