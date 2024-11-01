<?php
require_once( 'mimeDecode.php' );

class mailReader {
    var $raw = '';
    var $decoded;
    var $to;
    var $to_name;
    var $to_email;
    var $from;
    var $from_name;
    var $from_email;
    var $cc;
    var $cc_name;
    var $cc_email;
    var $subject;
    var $body;
	var $attachment_ids;

    public function __construct() {
    	$attachment_ids = array();
    }

    public function readEmail( $src = 'php://stdin' ) {
        // Process the e-mail from stdin
        $fd = fopen( $src, 'r' );
        while( !feof( $fd ) ) {
            $this->raw .= fread( $fd, 1024 );
        }

        // Now decode it!
        $decoder = new Mail_mimeDecode( $this->raw );
        $this->decoded = $decoder->decode(
            Array(
                'decode_headers' => TRUE,
                'include_bodies' => TRUE,
                'decode_bodies' => TRUE,
            )
        );
        //error_log( print_r( $this->decoded, TRUE ), 3, 'mailReader.log' );

        $this->to = $this->decoded->headers['to'];
        $this->to_name = preg_replace( '/"(.*)"(.*)/i', '$1', $this->to );
        $this->to_name = preg_replace( '/^[^a-zA-Z0-9]*|[^a-zA-Z0-9]*$/i', '$1', $this->to_name );
        $this->to_email = preg_replace( "/(.*)\<(.*)\>/i", '$2', $this->to );
        //error_log( print_r( $this->from, TRUE ), 3, 'mailReader.log' );

        $this->from = $this->decoded->headers['from'];
        $this->from_name = preg_replace( '/"(.*)"(.*)/i', '$1', $this->from );
        $this->from_name = preg_replace( '/^[^a-zA-Z0-9]*|[^a-zA-Z0-9]*$/i', '$1', $this->from_name );
        $from_name_parts = explode( '<', $this->from_name );
        $this->from_name = trim( $from_name_parts[0] );
        $this->from_email = preg_replace( "/(.*)\<(.*)\>/i", '$2', $this->from );
        //error_log( print_r( $this->from, TRUE ), 3, 'mailReader.log' );

        $this->cc = $this->decoded->headers['cc'];
        $this->cc_name = preg_replace( '/"(.*)"(.*)/i', '$1', $this->cc );
        $this->cc_name = preg_replace( '/^[^a-zA-Z0-9]*|[^a-zA-Z0-9]*$/i', '$1', $this->cc_name );
        $this->cc_email = preg_replace( "/(.*)\<(.*)\>/i", '$2', $this->cc );
        //error_log( print_r( $this->cc, TRUE ), 3, 'mailReader.log' );

        // Set the $this->subject
        $this->subject = $this->decoded->headers['subject'];

        if( isset( $this->decoded->parts ) && is_array( $this->decoded->parts ) ) {
            foreach( $this->decoded->parts as $idx => $body_part ) {
                $this->decodePart( $body_part );
            }
        }

        // We might also have uuencoded files. Check for those.
        if( !isset( $this->body ) ) {
            if( isset( $this->decoded->body ) ) {
                $this->body = $this->decoded->body;
            } else {
                $this->body = "No plain text body found";
            }
        }
    }

    private function decodePart( $body_part ) {
        if( is_array( $body_part->ctype_parameters ) && array_key_exists( 'name', $body_part->ctype_parameters ) ) {
            $filename = $body_part->ctype_parameters['name'];
        } else if ( is_array( $body_part->ctype_parameters ) && array_key_exists( 'filename', $body_part->ctype_parameters ) ) {
            $filename = $body_part->ctype_parameters['filename'];
        } else {
            $filename = time() . "-file";
        }

        $mimeType = "{$body_part->ctype_primary}/{$body_part->ctype_secondary}";

		switch( $body_part->ctype_primary ) {
			case 'text':
				switch( $body_part->ctype_secondary ) {
					case 'plain':
						$this->body = $body_part->body;
						break;
                    case 'html':
						$modbod = '';
                        $body = $body_part->body;
						$body = str_replace( array( '<br/>', '<br />' ), array( '<br>', '<br>' ), $body );
                        $body = strip_tags( $body, '<p><table><thead><tbody><tfoot><tr><th><td><ul><ol><li><b><strong><em><a><br><img>' );
						$body = str_replace( '<br><br>', '', $body );
						$body = str_replace( '<p></p>', '', $body );
						$body = str_replace( '<p>&nbsp;</p>', '', $body );
                        $this->body = $body;
                        break;
					default:
                        $this->body = $body_part->body;
						break;
				}
				break;
			case 'application':
				switch( $body_part->ctype_secondary ) {
					case 'pdf':
					case 'zip':
					case 'x-zip':
					case 'x-zip-compressed':
					case 'octet-stream':
					case 'x-gzip':
					case 'x-tar':
                    case 'docm':
                    case 'dotm':
                    case 'docx':
                    case 'dotx':
                    case 'doc':
                    case 'msword':
                    case 'vnd.openxmlformats-officedocument.wordprocessingml.document':
						//$this->attachment_ids[] = $this->saveFile( $filename, $body_part->body, $mimeType );
						$attach_id = $this->saveFile( $filename, $body_part->body, $mimeType );
						$attach_url = wp_get_attachment_url( $attach_id );
						$this->attachment_ids[] = array(
							'id' => $attach_id,
							'url' => $attach_url,
							'filename' => $filename
						);
						break;
					default:
						break;
				}
				break;
			case 'image':
				switch($body_part->ctype_secondary){
					case 'jpeg':
					case 'png':
					case 'gif':
	    				//$this->attachment_ids[] = $this->saveFile( $filename, $body_part->body, $mimeType );
						$attach_id = $this->saveFile( $filename, $body_part->body, $mimeType );
						$attach_url = wp_get_attachment_url( $attach_id );
						$this->attachment_ids[] = array(
							'id' => $attach_id,
							'url' => $attach_url,
							'filename' => $filename
						);
	    				break;
					default:
	    				break;
				}
				break;
			case 'multipart':
				if( is_array( $body_part->parts ) ) {
				    foreach( $body_part->parts as $ix => $sub_part ) {
						$this->decodePart( $sub_part );
				    }
				}
				break;
			default:
                //$this->attachment_ids[] = $this->saveFile( $filename, $body_part->body, $mimeType );
				$attach_id = $this->saveFile( $filename, $body_part->body, $mimeType );
				$attach_url = wp_get_attachment_url( $attach_id );
				$this->attachment_ids[] = array(
					'id' => $attach_id,
					'url' => $attach_url,
					'filename' => $filename
				);
				break;
		}
    }

    private function saveFile( $filename, $contents, $mimeType ) {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        $filename = preg_replace( '/[^a-zA-Z0-9_.-]/', '_', $filename );
        $upload_dir = wp_upload_dir();
        $save_directory = $upload_dir['basedir'] . '/';
        $save_path = $upload_dir['baseurl'] . '/';
        $unlocked_and_unique = FALSE;
        while( !$unlocked_and_unique ) {
            // Find unique
            $name = $filename;
            while( file_exists( $save_directory . $name ) ) {
                $name = time() . "_" . $filename;
            }
            // Attempt to lock
            $outfile = fopen( $save_directory . $name, 'w' );
            if( flock( $outfile, LOCK_EX ) ) {
                $unlocked_and_unique = TRUE;
            } else {
                flock( $outfile, LOCK_UN );
                fclose( $outfile );
            }
        }
        fwrite( $outfile, $contents );
        fclose( $outfile );
        if ( $this->is_upload_allowed( $save_directory . $name ) ) {
            $data = array(
                'guid' => $upload_dir['url'] . '/' . $name,
                'post_title' => $name,
                'post_content' => '',
                'post_status' => 'inherit',
                'post_mime_type' => $mimeType
            );
            $attach_id = wp_insert_attachment( $data, $save_directory . $name );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $save_directory . $name );
            wp_update_attachment_metadata( $attach_id, $attach_data );
        } else {
            $tmp_file = $save_directory . $name;
            $zip_file = $upload_dir['path'] . '/' . $name . '.zip';
            while ( file_exists( $zip_file ) ) {
                $zip_file = $upload_dir['path'] . '/' . time() . '_' . $name . '.zip';
            }
            $zip = new ZipArchive();
            if ( $zip->open( $zip_file, ZipArchive::CREATE ) ) {
                $zip->addFile( $tmp_file, $name );
                $zip->close();
                $filetype = wp_check_filetype( $zip_file );
                $attachment = array(
                    'guid'           => $zip_file,
                    'post_mime_type' => $filetype['type'],
                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $zip_file ) ),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                $attach_id = wp_insert_attachment( $attachment, $zip_file );
                if ( $attach_id != 0 ) {
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $zip_file );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                }
            }
            unlink( $tmp_file );
        }
        return $attach_id;
    }

    function isReturnedEmail( $obj ) {
    	if ( preg_match( '/not?[\-_]reply@/i', $obj->from_email ) ) { // Check noreply email addresses
    		return true;
    	} else if ( preg_match( '/mail(er)?[\-_]daemon@/i', $obj->from_email ) ) { // Check mailer daemon email addresses
    		return true;
    	} else if ( preg_match('/^[\[\(]?Auto(mat(ic|ed))?[ \-]?reply/i', $obj->subject) ) { // Check autoreply subjects
    		return true;
    	} else if ( preg_match('/^Out of Office/i', $obj->subject) ) { // Check out of office subjects
    		return true;
    	} else if ( preg_match( '/DELIVERY FAILURE/i', $obj->subject ) || preg_match( '/Undelivered Mail Returned to Sender/i', $obj->subject ) || preg_match( '/Delivery Status Notification \(Failure\)/i', $obj->subject ) || preg_match( '/Returned mail\: see transcript for details/i', $obj->subject ) ) { // Check delivery failed email subjects
    		return true;
    	} else if ( preg_match('/postmaster@/i', $obj->from_email) && preg_match('/Delivery has failed to these recipients/i', $obj->body) ) { // Check Delivery failed message
    		return true;
        } else if ( preg_match( '/Auto Reply/i', $obj->subject ) ) { // Check Delivery failed message
            return true;
    	} else { // No pattern detected, seems like this is not a returned email
    	   return false;
        }
    }

    function is_upload_allowed( $file ) {
        $filetype = wp_check_filetype( $file );
        $file_ext = $filetype['ext'];
        $mimes = get_allowed_mime_types();
        foreach ( $mimes as $type => $mime ) {
            if ( strpos( $type, $file_ext ) !== false ) {
                return true;
            }
        }
        return false;
    }
}
