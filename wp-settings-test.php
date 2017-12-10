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

			// Handle Uploads.
			// Format multiple files into individual $_FILES data.
			// This is becuase WordPress media_handle_upload() only support single input file (not multiple).
			$_files_gallery = $_FILES['gallery'];
			$files_data = array();
			if ( isset( $_files_gallery['name'] ) && is_array( $_files_gallery['name'] ) ) {
				$file_count = count( $_files_gallery['name'] );
				for ( $n = 0; $n < $file_count; $n++ ) {
					if( $_files_gallery['name'][$n] && $_files_gallery['type'][$n] && $_files_gallery['tmp_name'][$n] ){
						if( ! $_files_gallery['error'][$n] ){ // Check error.
							$type = wp_check_filetype( $_files_gallery['name'][$n] );

							// Only image allowed.
							if ( strpos( $type['type'], 'image' ) !== false ) {
								$files_data[] = array(
									'name'     => $_files_gallery['name'][$n],
									'type'     => $type['type'],
									'tmp_name' => $_files_gallery['tmp_name'][$n],
									'error'    => $_files_gallery['error'][$n],
									'size'     => filesize( $_files_gallery['tmp_name'][$n] ), // in byte.
								);
							}
						}
					}
				}
			} // end if().

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

			// Upload each file.
			$attachment_ids = $keep_ids;
			foreach ( $files_data as $file_data ) {

				// Load WP Media.
				if ( ! function_exists( 'media_handle_upload' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					require_once( ABSPATH . 'wp-admin/includes/media.php' );
				}

				// Set files data to upload.
				$_FILES['gallery'] = $file_data;
				$attachment_id = media_handle_upload( 'gallery', 0 );

				if ( $attachment_id ) {
					$attachment_ids[] = $attachment_id;
				}
			}

			update_option( 'mycut', $attachment_ids );

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
