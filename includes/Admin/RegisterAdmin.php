<?php
/**
 * Admin registration class.
 *
 * @package WPAccordionBlock
 */

namespace WPAccordionBlock\Admin;

use \WP_Admin_Bar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\Jetpack\Assets;

/**
 * Class RegisterAdmin
 */
class RegisterAdmin {
	/**
	 * Class constructor.
	 */
	public function __construct() {
		// Register Admin Dashboard.
		add_action( 'admin_menu', array( $this, 'register_admin_dashboard' ) );

		// Handles Admin Bar menu setup and actions.
		$this->setup_admin_bar_menu();
	}

	/**
	 * Register admin dashboard.
	 */
	public function register_admin_dashboard() {
		$primary_slug = WP_ACCORDION_BLOCK_SLUG;

		$dashboard_page_suffix = add_menu_page(
			_x( 'WPAccordionBlock Dashboard', 'Page title', 'wp_accordion_block' ),
			_x( 'WPAccordionBlock', 'Menu title', 'wp_accordion_block' ),
			'manage_options',
			$primary_slug,
			array( $this, 'plugin_dashboard_page' ),
			WP_ACCORDION_BLOCK_URL . '/assets/img/icon.svg',
			30
		);

		// Register dashboard hooks.
		add_action( 'load-' . $dashboard_page_suffix, array( $this, 'dashboard_admin_init' ) );

		// Register dashboard submenu nav item.
		add_submenu_page( $primary_slug, 'WPAccordionBlock Dashboard', 'Dashboard', 'manage_options', $primary_slug . '#/dashboard', '__return_null' );

		// Remove duplicate menu hack.
		// Note: It needs to go after the above add_submenu_page call.
		remove_submenu_page( $primary_slug, $primary_slug );

		// Register getting started aka onboarding submenu nav item.
		add_submenu_page( $primary_slug, 'WPAccordionBlock Dashboard', 'Getting Started', 'manage_options', $primary_slug . '#/getting-started', '__return_null' );

		// Register changelog submenu nav item.
		add_submenu_page( $primary_slug, 'WPAccordionBlock Dashboard', 'Changelog', 'manage_options', $primary_slug . '#/changelog', '__return_null' );

		// Register settings submenu nav item.
		add_submenu_page( $primary_slug, 'WPAccordionBlock Dashboard', 'Settings', 'manage_options', $primary_slug . '#/settings', '__return_null' );
	}

	/**
	 * Initialize the Dashboard admin resources.
	 */
	public function dashboard_admin_init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_admin_scripts' ) );
	}

	/**
	 * Enqueue plugin admin scripts and styles.
	 */
	public function enqueue_dashboard_admin_scripts() {
		$prefix = WP_ACCORDION_BLOCK_SLUG;

		Assets::register_script(
			$prefix . '-dashboard',
			'build/dashboard/index.js',
			WP_ACCORDION_BLOCK_ROOT_FILE,
			array(
				'in_footer'  => true,
				'textdomain' => 'wp_accordion_block',
			)
		);

		// Enqueue app script.
		Assets::enqueue_script( $prefix . '-dashboard' );
		// Initial JS state.
		wp_add_inline_script( $prefix . '-dashboard', $this->render_dashboard_initial_state(), 'before' );
	}

	/**
	 * Render the initial state into a JavaScript variable.
	 *
	 * @return string
	 */
	public function render_dashboard_initial_state() {
		return 'var vajraPluginState=JSON.parse(decodeURIComponent("' . rawurlencode( wp_json_encode( $this->initial_dashboard_state() ) ) . '"));';
	}

	/**
	 * Get the initial state data for hydrating the React UI.
	 *
	 * @return array
	 */
	public function initial_dashboard_state() {
		return array(
			'apiRoute'     => WP_ACCORDION_BLOCK_SLUG . '/v1',
			'assetsURL'    => WP_ACCORDION_BLOCK_URL . '/assets',
			// You can also replace this changelog URL to something else so that it loads from one source and stays up-to-date always.
			'changelogURL' => WP_ACCORDION_BLOCK_URL . '/changelog.json?ver=' . filemtime( WP_ACCORDION_BLOCK_DIR . '/changelog.json' ),
			'version'      => WP_ACCORDION_BLOCK_VERSION,
		);
	}

	/**
	 * Plugin Dashboard page.
	 */
	public function plugin_dashboard_page() {
		?>
			<div id="wp_accordion_block-dashboard-root"></div>
		<?php
	}

	/**
	 * Sets up admin bar and related functions.
	 *
	 * @return void
	 */
	public function setup_admin_bar_menu() {

		// Register admin bar menu.
		// Priority level is 1000 intentionally so that it shows up.
		add_action( 'admin_bar_menu', array( $this, 'register_admin_bar_menu' ), 1000 );

		// AJAX action handler for flushing permalinks.
		$action_key = WP_ACCORDION_BLOCK_SLUG . '_flush_rules';
		add_action( 'wp_ajax_' . $action_key, array( $this, 'process_permalinks_flush' ) );
	}

	/**
	 * Register admin bar menu.
	 *
	 * @param WP_Admin_Bar $admin_bar The WP_Admin_Bar instance.
	 *
	 * @return void
	 */
	public function register_admin_bar_menu( WP_Admin_Bar $admin_bar ) {
		$prefix         = WP_ACCORDION_BLOCK_SLUG;
		$parent_menu_id = $prefix . '-dashboard';

		$admin_bar->add_menu(
			array(
				'id'     => $parent_menu_id,
				'parent' => null,
				'title'  => __( 'WPAccordionBlock Tools', 'wp_accordion_block' ),
				'href'   => esc_url( admin_url( 'admin.php?page=' . $prefix ) ),
			)
		);

		$nonce = wp_create_nonce( $prefix . '_flush_rules' );

		$admin_bar->add_menu(
			array(
				'parent' => $parent_menu_id,
				'id'     => $prefix . '-flush-rewrite-rules',
				'title'  => __( 'Flush Permalinks', 'wp_accordion_block' ),
				'href'   => esc_url( admin_url( 'admin-ajax.php?action=' . $prefix . '_flush_rules&nonce=' . $nonce ) ),
			)
		);
	}

	/**
	 * Handles flush permalinks AJAX action from admin bar menu.
	 *
	 * @return void
	 */
	public function process_permalinks_flush() {
		$prefix    = WP_ACCORDION_BLOCK_SLUG;
		$nonce_key = $prefix . '_flush_rules';

		// Do something.
		if ( ! check_ajax_referer( $nonce_key, 'nonce' ) ) {
			exit( 'Unauthorized' );
		}

		// Clear the permalinks in case any changes in permalinks or CPTs.
		flush_rewrite_rules();

        // phpcs:ignore.
		$redirect_uri = wp_unslash( $_SERVER['HTTP_REFERER'] ) && ! empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : admin_url( 'admin.php?page=' . $prefix );

		wp_safe_redirect( esc_url( $redirect_uri ) );
		die();
	}
}
