<?php
/**
 * Functions
 */


/**
 * Handle Upload for Multiple Field Input
 * WordPress have media_handle_upload() but it only support single file input, so this function separate each into different request.
 *
 * @since 1.0.0
 * @uses media_handle_upload()
 * @link https://developer.wordpress.org/reference/functions/media_handle_upload/
 *
 * @param string $field_name The name in submitted $_FILES['name'].
 * @param string $file_type  Check if the file type contain this string. Default to "image".
 * @param int    $post_id    Post ID to attach the attachment. Default to none.
 * @return array Uploaded Attachment IDs.
 */
function mycut_handle_upload( $field_name, $file_type = 'image', $post_id = 0 ) {
	// Get files from form.
	$_files_gallery = isset( $_FILES[ $field_name ] ) ? $_FILES[ $field_name ] : array();

	// Bail, if no file submitted.
	if ( ! $_files_gallery || ! is_array( $_files_gallery ) ) {
		return array();
	}

	// Format multiple $_FILES into useable array. 
	$files_data = array();
	if ( isset( $_files_gallery['name'] ) && is_array( $_files_gallery['name'] ) ) {
		$file_count = count( $_files_gallery['name'] );
		for ( $n = 0; $n < $file_count; $n++ ) {
			if( $_files_gallery['name'][$n] && $_files_gallery['type'][$n] && $_files_gallery['tmp_name'][$n] ){
				if( ! $_files_gallery['error'][$n] ){ // Check error.
					$type = wp_check_filetype( $_files_gallery['name'][$n] );

					// If file type is set, and not in file type, skip file.
					if ( $file_type && strpos( $type['type'], $file_type ) === false ) {
						continue;
					}

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
	} // end if().

	// Upload each file.
	$attachment_ids = array();

	foreach ( $files_data as $file_data ) {

		// Load WP Media.
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		// Set files data to upload.
		$_FILES[ $field_name ] = $file_data;
		$attachment_id = media_handle_upload( $field_name, intval( $post_id ) );

		// Success upload, add attachment ID.
		if ( $attachment_id ) {
			$attachment_ids[] = $attachment_id;
		}
	}

	return $attachment_ids;
}







