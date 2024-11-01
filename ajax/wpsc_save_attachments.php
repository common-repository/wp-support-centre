<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save Attachments
 *
 *
 */
function wpsc_save_attachments() {
	global $wpdb;
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');
    $attach_ids = array();
    $attachments = ( isset( $_POST['wpsc_admin_existing_thread_attachments'] ) && $_POST['wpsc_admin_existing_thread_attachments'] != '' ) ? $_POST['wpsc_admin_existing_thread_attachments'] : '';
    $attach_ids = ( $attachments == '' ) ? array() : explode( ',', $attachments );
    $wp_upload_dir = wp_upload_dir();
	$save_directory = $wp_upload_dir['basedir'] . '/';
	$save_path = $wp_upload_dir['baseurl'] . '/';
    if ( isset( $_FILES['wpsc_file'] ) ) {
        $files = $_FILES['wpsc_file'];
        foreach ( $files['name'] as $key => $value ) {
            $file = array(
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key],
            );
            $_FILES['new_files'] = $file;
			$filehash = hash_file( 'md5', $_FILES['new_files']['tmp_name'] );
			$sql = "SELECT * FROM " . $wpdb->prefix . "postmeta WHERE meta_value='" . $filehash . "'";
			$result = $wpdb->get_row( $sql );
			if ( null === $result ) {
				$filetype = wp_check_filetype( $_FILES['new_files']['name'] );
				$fileext = strtolower( $filetype['ext'] );
				$allowed = false;
				$mimes = get_allowed_mime_types();
		        foreach ( $mimes as $type=>$mime ) {
		            if ( strpos( $type, $fileext ) !== false ) {
		                $allowed = true;
		            }
		        }
				if ( $allowed ) {
					move_uploaded_file( $_FILES['new_files']["tmp_name"], $save_directory . $_FILES['new_files']['name'] );
					$mimetype = mime_content_type( $_FILES['new_files']['name'] );
					$data = array(
	                    'guid' => $wp_upload_dir['url'] . '/' . $_FILES['new_files']['name'],
	                    'post_title' => $_FILES['new_files']['name'],
	                    'post_content' => '',
	                    'post_status' => 'inherit',
	                    'post_mime_type' => $mimetype
	                );
	                $attach_id = wp_insert_attachment( $data, $save_directory . $_FILES['new_files']['name'] );
	                update_post_meta( $attach_id, 'filehash', $filehash );
				} else {
					$zipfile = $_FILES['new_files']['name'] . '.zip';
					while( file_exists( $save_directory . $zipfile ) ) {
		                $zipfile = time() . '_' . $_FILES['new_files']['name'] . '.zip';
		            }
					$zip = new ZipArchive();
		            if ( $zip->open( $save_directory . $zipfile, ZipArchive::CREATE ) ) {
		                $zip->addFile( $_FILES['new_files']['tmp_name'], $_FILES['new_files']['name'] );
		                $zip->close();
		                $filetype = wp_check_filetype( $zipfile );
                        $filehash = hash_file( 'md5', $save_directory . $zipfile );
                        $sql = "SELECT * FROM " . $wpdb->prefix . "postmeta WHERE meta_value='" . $filehash . "'";
                        $result = $wpdb->get_row( $sql );
                        if ( null === $result ) {
							$data = array(
				                'guid' => $save_directory . $zipfile,
				                'post_title' => $zipfile,
				                'post_content' => '',
				                'post_status' => 'inherit',
				                'post_mime_type' => 'application/zip'
				            );
							$attach_id = wp_insert_attachment( $data, $save_directory . $zipfile );
                            update_post_meta( $attach_id, 'filehash', $filehash );
                        } else {
                            $attach_id = $result->post_id;
                            unlink( $save_directory . $zipfile );
                        }
		            }
				}
			} else {
				$attach_id = $result->post_id;
				$existing = get_attached_file( $attach_id );
				if ( !file_exists( $existing ) ) {
					$log = move_uploaded_file( $_FILES['new_files']["tmp_name"], $existing );
				}
			}
			$attach_ids[] = $attach_id;
        }
        $attachments = ( !empty( $attach_ids ) ) ? implode( ',', $attach_ids ) : '';
    } else if ( isset( $_POST['attachment_ids'] ) ) {
        $attachments = $_POST['attachment_ids'];
    } else {
        $attachments = ( !empty( $attach_ids ) ) ? implode( ',', $attach_ids ) : '';
    }
    return $attachments;
}