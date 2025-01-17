<?php
/**
 * WPAccordionBlock
 *
 * @package           WPAccordionBlock
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WP Accordion Block
 * Plugin URI:        https://wordpress.org/plugins/wp-accordion-block/
 * Description:       A starter WordPress plugin scaffold which comes pre-configured for block development, admin dashboard with settings and standard plugin code.
 * Version:           0.4.0-beta
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Mehul Gohil
 * Author URI:        https://mehulgohil.com
 * Text Domain:       wp_accordion_block
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 *
 */

 /*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_ACCORDION_BLOCK_VERSION', '1.0.0' );
define( 'WP_ACCORDION_BLOCK_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_ACCORDION_BLOCK_ROOT_FILE', __FILE__ );
define( 'WP_ACCORDION_BLOCK_ROOT_FILE_RELATIVE_PATH', plugin_basename( __FILE__ ) );
define( 'WP_ACCORDION_BLOCK_SLUG', 'wp-accordion-block' );
define( 'WP_ACCORDION_BLOCK_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
define( 'WP_ACCORDION_BLOCK_URL', plugins_url( '', __FILE__ ) );

// WPAccordionBlock Autoloader.
$wp_accordion_block_autoloader = WP_ACCORDION_BLOCK_DIR . 'vendor/autoload_packages.php';
if ( is_readable( $wp_accordion_block_autoloader ) ) {
	require_once $wp_accordion_block_autoloader;
} else { // Something very unexpected. Error out gently with an admin_notice and exit loading.
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			__( 'Error loading autoloader file for WPAccordionBlock plugin', 'wp-accordion-block' )
		);
	}

	add_action(
		'admin_notices',
		function () {
			?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: Placeholder is a link to a support document. */
						__( 'Your installation of WPAccordionBlock is incomplete. If you installed WPAccordionBlock from GitHub, please refer to <a href="%1$s" target="_blank" rel="noopener noreferrer">this document</a> to set up your development environment. WPAccordionBlock must have Composer dependencies installed and built via the build command.', 'wp-accordion-block' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
						)
					),
					'https://github.com/SmallTownDev/wp-accordion-block'
				);
				?>
			</p>
		</div>
			<?php
		}
	);

	return;
}

// Redirect to plugin onboarding when the plugin is activated.
add_action( 'activated_plugin', 'wp_accordion_block_activation' );

/**
 * Redirects to plugin onboarding when the plugin is activated
 *
 * @param string $plugin Path to the plugin file relative to the plugins directory.
 */
function wp_accordion_block_activation( $plugin ) {
	// Clear the permalinks after the post type has been registered.
	flush_rewrite_rules();
	if (
		WP_ACCORDION_BLOCK_ROOT_FILE_RELATIVE_PATH === $plugin &&
		\Automattic\Jetpack\Plugins_Installer::is_current_request_activating_plugin_from_plugins_screen( WP_ACCORDION_BLOCK_ROOT_FILE_RELATIVE_PATH )
	) {
		wp_safe_redirect( esc_url( admin_url( 'admin.php?page=wp_accordion_block#/getting-started' ) ) );
		exit;
	}
}

register_activation_hook( __FILE__, array( '\WPAccordionBlock\Plugin', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( '\WPAccordionBlock\Plugin', 'plugin_deactivation' ) );

// Main plugin class.
if ( class_exists( \WPAccordionBlock\Plugin::class ) ) {
	new \WPAccordionBlock\Plugin();
}
