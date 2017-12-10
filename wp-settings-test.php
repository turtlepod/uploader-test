<?php
/**
 * Plugin Name: Uploader Test
 * Plugin URI: https://github.com/turtlepod/wp-settings-test
 * Description: Simple multiple file uploader field.
 * Version: 1.0.0
 * Author: David Chandra Purnama
 * Author URI: http://shellcreeper.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
**/

namespace mycut;
if ( ! defined( 'WPINC' ) ) {
	die;
}

/* Constants
------------------------------------------ */

define( __NAMESPACE__ . '\PREFIX', 'mycut' );
define( __NAMESPACE__ . '\URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( __NAMESPACE__ . '\PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( __NAMESPACE__ . '\FILE', __FILE__ );
define( __NAMESPACE__ . '\PLUGIN', plugin_basename( __FILE__ ) );
define( __NAMESPACE__ . '\VERSION', '1.0.0' );


/* Init
------------------------------------------ */

add_action( 'plugins_loaded', function() {

	require_once( PATH . 'test/functions.php' );

	// Register Settings.
	add_action( 'admin_init', function() {
		register_setting(
			$option_group      = PREFIX,
			$option_name       = PREFIX,
			$sanitize_callback = function( $in ) { // Sanitize Here!
				$out = $in;
				return $out;
			}
		);
	} );

	// Add Settings Page.
	add_action( 'admin_menu', function() {

		// Add page.
		$page = add_menu_page(
			$page_title  = 'Test Uploader',
			$menu_title  = 'Test Uploader!',
			$capability  = 'manage_options',
			$menu_slug   = PREFIX,
			$function    = function() {
				?>
				<div class="wrap">
					<h1>Upload Stuff</h1>
					<form method="post" enctype="multipart/form-data">
						<?php settings_errors(); ?>
						<?php require_once( PATH . 'test/html.php' ); ?>
						<?php do_settings_sections( PREFIX ); ?>
						<?php settings_fields( PREFIX ); ?>
						<?php submit_button(); ?>
						<?php wp_nonce_field( __FILE__, '_nonce_' . PREFIX ); ?>
					</form>
				</div><!-- wrap -->
				<?php
			},
			$icon        = '',
			$position    = 2
		);

		// Custom save action. Not using settings API.
		if ( $page && isset( $_POST['_nonce_' . PREFIX] ) && $_POST['_nonce_' . PREFIX] && wp_verify_nonce( $_POST['_nonce_' . PREFIX], __FILE__ ) ) {
			$attachment_ids = mycut_handle_upload( 'gallery' );

			// Stored IDs.
			$ids = get_option( 'mycut', array() );
			$ids = $ids && is_array( $ids ) ? $ids : array();

			// Keep this image ids.
			$keep_ids = isset( $_POST['ids'] ) && $_POST['ids']  && is_array( $_POST['ids'] ) ? $_POST['ids'] : array();
			$deleted = array_diff( $ids, $keep_ids );

			// Delete attachment not in list.
			foreach( $deleted as $del_id ) {
				wp_delete_attachment( $del_id, true );
			}

			update_option( 'mycut', array_merge( $keep_ids, $attachment_ids ) );

			// Redirect back for clearner browser state.
			wp_safe_redirect( esc_url_raw( add_query_arg( 'page', PREFIX, admin_url( 'admin.php' ) ) ) );
		}

		// Load assets.
		add_action( 'admin_enqueue_scripts', function( $hook_suffix ) use( $page ) {
			if ( $page === $hook_suffix ) {

				// CSS.
				wp_enqueue_style( PREFIX . '_settings', URI . 'test/style.css', array(), time() );

				// JS.
				wp_enqueue_media();
				$deps = array(
					'jquery',
					'jquery-ui-sortable',
					'wp-backbone',
					'wp-util',
				);
				wp_enqueue_script( PREFIX . '_settings', URI . 'test/script.js', $deps, time(), true );

				// JS Data.
				$option = get_option( PREFIX );
				$option = is_array( $option ) ? $option : array();
				wp_localize_script( PREFIX . '_settings', PREFIX . 'Data', $option );
			}
		} );
	} );

} );
