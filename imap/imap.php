<?php
if ( !function_exists( 'flattenParts' ) ) {
    function flattenParts( $messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true ) {
        foreach( $messageParts as $part ) {
            $flattenedParts[$prefix . $index] = $part;
            if( isset( $part->parts ) ) {
                if( $part->type == 2 ) {
                    //$flattenedParts = flattenParts( $part->parts, $flattenedParts, $prefix . $index . '.', 0, false );
                } else if ( $fullPrefix ) {
                    $flattenedParts = flattenParts( $part->parts, $flattenedParts, $prefix . $index . '.' );
                } else {
                    $flattenedParts = flattenParts( $part->parts, $flattenedParts, $prefix );
                }
                unset( $flattenedParts[$prefix.$index]->parts );
            }
            $index++;
        }
        return $flattenedParts;
    }
}

if ( !function_exists( 'getPart' ) ) {
    function getPart( $connection, $messageNumber, $partNumber, $encoding ) {
        $data = imap_fetchbody( $connection, $messageNumber, $partNumber );
        switch( $encoding ) {
            case 0: return $data; // 7BIT
            case 1: return $data; // 8BIT
            case 2: return $data; // BINARY
            case 3: return base64_decode( $data ); // BASE64
            case 4: return quoted_printable_decode( $data ); // QUOTED_PRINTABLE
            case 5: return $data; // OTHER
        }
    }
}

if ( !function_exists( 'getFilenameFromPart' ) ) {
    function getFilenameFromPart( $part ) {
        $filename = '';
        if( $part->ifdparameters ) {
            foreach( $part->dparameters as $object ) {
                if ( strtolower( $object->attribute ) == 'filename' ) {
                    $filename = $object->value;
                }
            }
        }
        if ( !$filename && $part->ifparameters ) {
            foreach ( $part->parameters as $object ) {
                if ( strtolower( $object->attribute ) == 'name' ) {
                    $filename = $object->value;
                }
            }
        }
        return $filename;
    }
}

if ( !function_exists( 'find_wordpress_base_path' ) ) {
    function find_wordpress_base_path() {
        $dir = dirname(__FILE__);
        do {
            if( file_exists( $dir . "/wp-config.php") ) {
                return $dir;
            }
        } while( $dir = realpath( "$dir/.." ) );
        return null;
    }
}

if ( !function_exists( 'isReturnedEmail' ) ) {
    function isReturnedEmail( $from_email, $subject, $message ) {
        if ( preg_match( '/not?[\-_]reply@/i', $from_email ) ) { // Check noreply email addresses
            return true;
        } else if ( preg_match( '/mail(er)?[\-_]daemon@/i', $from_email ) ) { // Check mailer daemon email addresses
            return true;
        } else if ( preg_match( '/^[\[\(]?Auto(mat(ic|ed))?[ \-]?reply/i', $subject ) ) { // Check autoreply subjects
            return true;
        } else if ( preg_match( '/^Out of Office/i', $subject) ) { // Check out of office subjects
            return true;
        } else if ( preg_match( '/DELIVERY FAILURE/i', $subject ) || preg_match( '/Undelivered Mail Returned to Sender/i', $subject ) || preg_match( '/Delivery Status Notification \(Failure\)/i', $subject ) || preg_match( '/Returned mail\: see transcript for details/i', $subject ) ) { // Check delivery failed email subjects
            return true;
        } else if ( preg_match( '/postmaster@/i', $from_email ) && preg_match( '/Delivery has failed to these recipients/i', $message ) ) { // Check Delivery failed message
            return true;
        } else if ( preg_match( '/Auto Reply/i', $subject ) ) { // Check Delivery failed message
            return true;
        } else { // No pattern detected, seems like this is not a returned email
           return false;
        }
    }
}

if ( !function_exists( 'wpscOpenIMAP' ) ) {
    function wpscOpenIMAP( $connect, $username, $password, $readonly = false ) {
		if ( $readonly === false ) {
			$imap = @imap_open( "{" . $connect . "}", $username, $password );
		} else {
			$imap = @imap_open( "{" . $connect . "}", $username, $password, OP_READONLY );
		}
		if ( $imap ) {
			return $imap;
		} else {
			throw new Exception( 'Unable to establish connection to IMAP using ' . $connect );
			return false;
		}
	}
}

define( 'BASE_PATH', find_wordpress_base_path() . "/" );
require( BASE_PATH . 'wp-load.php' );
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header, $wpdb;
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
if ( $wpsc_options['wpsc_email_method'] == 2 ) {
	$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_imap";
	$results = $wpdb->get_results( $sql );
	if ( !empty( $results ) && $results !== NULL ) {
		foreach ( $results as $imap_account ) {
			$server = $imap_account->imap_server;
			$port = $imap_account->imap_port;
			if ( $port == '143' ) {
				$argstrings = array(
	                '/imap/novalidate-cert',
	                '/imap/notls'
	            );
			} else {
				$argstrings = array(
	                '/imap/ssl/novalidate-cert',
	                '/imap/notls/ssl/novalidate-cert',
	            );
			}
			$username = $imap_account->imap_username;
			$password = $imap_account->imap_password;
			$type = $imap_account->imap_type;
			if ( $type == 1 ) { // account will create / update tickets
                foreach ( $argstrings as $argstring ) {
                    $connect = $server . ':' . $port . $argstring;
					try {
						$imap = wpscOpenIMAP( $connect, $username, $password );
					}
					catch ( imapException $e ) {
						//echo $e->getMessage();
					}
                    //$imap = imap_open( "{" . $connect . "}", $username, $password );
                    if ( $imap ) {
                        break;
                    }
                }
				$search_args = 'UNSEEN';
				$emails = imap_search( $imap, $search_args );
				if ( $emails ) {
					rsort( $emails );
					foreach ( $emails as $email_id ) {
						$message = '';
						$attachments = array();
						$header = imap_headerinfo($imap, $email_id );
						$timestamp = ( isset( $header->udate ) ) ? date( "Y-m-d H:i:s", $header->udate ) : current_time( 'mysql', 1 );
						if ( isset ( $header->to[0]->personal ) ) {
							$to_name = imap_mime_header_decode( $header->to[0]->personal );
							$to_name = ( isset( $to_name[0]->text ) ) ? $to_name[0]->text : $header->to[0]->personal;
							$to_email = $header->to[0]->mailbox . '@' . $header->to[0]->host;
							$to = $to_name . ' (' . $to_email . ')';
						} else {
							$to_name = imap_mime_header_decode( $header->to[0]->mailbox );
							$to_name = ( isset( $to_name[0]->text ) ) ? $to_name[0]->text : $header->to[0]->mailbox;
							$to_email = $header->to[0]->mailbox . '@' . $header->to[0]->host;
							$to = $to_email;
						}
						if ( isset ( $header->from[0]->personal ) ) {
							$from_name = imap_mime_header_decode( $header->from[0]->personal );
							$from_name = ( isset( $from_name[0]->text ) ) ? $from_name[0]->text : $header->from[0]->personal;
							$from_email = $header->from[0]->mailbox . '@' . $header->from[0]->host;
							$from = $from_name . ' (' . $from_email . ')';
						} else {
							$from_name = imap_mime_header_decode( $header->from[0]->mailbox );
							$from_name = ( isset( $from_name[0]->text ) ) ? $from_name[0]->text : $header->from[0]->mailbox;
							$from_email = $header->from[0]->mailbox . '@' . $header->from[0]->host;
							$from = $from_email;
						}
						if ( empty( $from_name ) || $from_name == '' || !is_string( $from_name) ) {
							$from_name = $from_email;
						}
						$subject = imap_mime_header_decode( $header->subject );
						$subject = ( isset( $subject[0]->text ) ) ? $subject[0]->text : $header->subject;
                        $isReturnedMail = isReturnedEmail( $from_email, $subject, '' );
                        if ( !$isReturnedMail ) {
    						$structure = imap_fetchstructure( $imap, $email_id );
    						if ( isset( $structure->parts ) ) {
    							$parts = flattenParts( $structure->parts );
    							foreach ( $parts as $partno=>$part ) {
    								switch( $part->type ) {
    									case 0: // the HTML or plain text part of the email
    										if ( $part->subtype == 'HTML' ) {
				                            	$message = getPart( $imap, $email_id, $partno, $part->encoding );
											} else {
												$message .= getPart( $imap, $email_id, $partno, $part->encoding ) . '<br /><br />';
											}
    										break;
    									case 1: // multi-part headers, can ignore
    										break;
    									case 2: // attached message headers, can ignore
    										break;
    									case 3: // application
    									case 4: // audio
    									case 5: // image
    									case 6: // video
    									case 7: // other
    										$filename = getFilenameFromPart( $part );
    										if ( $filename ) { // it's an attachment
    											$inline = ( isset( $part->ifparameters ) && $part->ifparameters == 1 ) ? true : false;
    											require_once( ABSPATH . 'wp-admin/includes/image.php' );
    									        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    									        require_once( ABSPATH . 'wp-admin/includes/media.php' );
    											$upload_dir = wp_upload_dir();
    				        					$save_directory = $upload_dir['basedir'] . '/';
    				        					$save_path = $upload_dir['baseurl'] . '/';
    											$attachment = getPart( $imap, $email_id, $partno, $part->encoding );
    											$mimeinfo = imap_fetchmime( $imap, $email_id, $partno );
    											$mimeinfo = explode( ';', $mimeinfo );
    											$mimeinfo = $mimeinfo[0];
    											$mimeinfo = explode( ':', $mimeinfo );
    											$mimetype = trim( $mimeinfo[1] );
    											$filetype = wp_check_filetype( $filename );
    				        					$fileext = strtolower( $filetype['ext'] );
    											$allowed = false;
    											$mimes = get_allowed_mime_types();
    									        foreach ( $mimes as $type=>$mime ) {
    									            if ( strpos( $type, $fileext ) !== false ) {
    									                $allowed = true;
    									            }
    									        }
    											if ( $allowed ) {
    												$savename = $filename;
    									            while( file_exists( $save_directory . $savename ) ) {
    									                $savename = time() . "_" . $filename;
    									            }
    												$fp = fopen( $save_directory . $savename, "w+" );
    								                fwrite( $fp, $attachment );
    								                fclose( $fp );
                                                    $filehash = hash_file( 'md5', $save_directory . $savename );
                                                    $sql = "SELECT * FROM " . $wpdb->prefix . "postmeta WHERE meta_value='" . $filehash . "'";
                                                    $result = $wpdb->get_row( $sql );
                                                    if ( null === $result ) {
                                                        $data = array(
                                                            'guid' => $upload_dir['url'] . '/' . $savename,
                                                            'post_title' => $savename,
                                                            'post_content' => '',
                                                            'post_status' => 'inherit',
                                                            'post_mime_type' => $mimetype
                                                        );
                                                        $attach_id = wp_insert_attachment( $data, $save_directory . $savename );
                                                        update_post_meta( $attach_id, 'filehash', $filehash );
                                                    } else {
                                                        $attach_id = $result->post_id;
                                                        unlink( $save_directory . $savename );
                                                    }
    									            //$attach_data = wp_generate_attachment_metadata( $attach_id, $save_directory . $savename );
    									            //wp_update_attachment_metadata( $attach_id, $attach_data );
    											} else {
    												$tmpdir = get_temp_dir();
    												$savename = $filename;
    									            while( file_exists( $tmpdir . $savename ) ) {
    									                $savename = time() . "_" . $filename;
    									            }
    												$fp = fopen( $tmpdir . $savename, "w+" );
    								                fwrite( $fp, $attachment );
    								                fclose( $fp );
    												$zipfile = $savename . '.zip';
    												while( file_exists( $save_directory . $zipfile ) ) {
    									                $zipfile = time() . '_' . $savename . '.zip';
    									            }
    												$zip = new ZipArchive();
    									            if ( $zip->open( $save_directory . $zipfile, ZipArchive::CREATE ) ) {
    									                $zip->addFile( $tmpfile, $savename );
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
    									            	//$attach_data = wp_generate_attachment_metadata( $attach_id, $save_directory . $zipfile );
    									            	//wp_update_attachment_metadata( $attach_id, $attach_data );
    									            }
    									            unlink( $tmpdir . $savename );
    											}
    											$attachments[] = array( $attach_id, $filename, $inline );
    										} else { // don't know what it is

    										}
    										break;
    								}
    							}
    						} else {
    							$message = imap_fetchbody( $imap, $email_id, 1 );
    						}
    						preg_match_all('/src="cid:(.*)"/Uims', $message, $matches);
    						if ( !empty( $matches[1] ) ) {
    							foreach ( $matches[1] as $cid ) {
    								$embedded_file = explode( '@', $cid );
    								$embedded_file = $embedded_file[0];
    								foreach ( $attachments as $attachment ) {
    									if ( $attachment[2] && $embedded_file == $attachment[1] ) {
    										$attach_url = wp_get_attachment_url( $attachment[0] );
    										$message = str_replace( 'cid:' . $cid, $attach_url, $message );
    									}
    								}
    							}
    						}
							$is_new = true;
						    if ( isset( $wpsc_options['wpsc_item_history'] ) && is_array( $wpsc_options['wpsc_item_history'] ) )  {
						        foreach ( $wpsc_options['wpsc_item_history'] as $wpsc_item ) {
						            if ( stristr( $subject, '[' . $wpsc_item . ': ' ) ) { // is existing ticket
						                $is_new = false;
						            }
						        }
						    } else if ( stristr( $subject, '[' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ': ' ) ) { // is existing ticket
						        $is_new = false;
						    }
							if ( $is_new === false ) {
								$pattern = '/\[([^]]+)\]/';
						        preg_match( $pattern, $subject, $matches );
						        $ticket_id = preg_replace( "/[^0-9,.]/", "", $matches[0] );
								$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_tickets WHERE id=" . $ticket_id;
						        $select = $wpdb->get_results( $sql );
						        if ( !$select || $select == NULL ) {
						            $is_new == true;
						        }
							}
							if ( $is_new ) {
								$status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
						        $category = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_categories WHERE is_default=1" );
						        $priority = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_priority WHERE is_default=1" );
								$attach_ids = array();
								foreach ( $attachments as $attachment ) {
									$attach_ids[] = $attachment[0];
								}
								$attach = ( !empty( $attach_ids ) ) ? implode( ',', $attach_ids ) : '';
								$user = get_user_by( 'email', $from_email );
						        if ( $user ) {
						            $client_id = $user->ID;
						            $client_name = $user->display_name;
						            $client_email = $user->user_email;
						        } else {
						            if ( $from_name != '' ) {
						            	if ( strpos( $from_name, '@' ) ) {
						            		$first_name = '';
						                	$last_name = '';
						            	} else if ( substr_count( $from_name, ' ' ) == 1 ) {
						            		$name_parts = explode( ' ', $from_name );
						            		$first_name = $name_parts[0];
						                    $last_name = $name_parts[1];
						            	} else {
						            		$first_name = '';
						                	$last_name = '';
						            	}
						            } else {
						                $first_name = '';
						                $last_name = '';
						            }
						            $client_name = trim( $first_name . ' ' . $last_name );
						            $client_email = $from_email;
						            $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
						            $args = array(
						                'user_pass' => $random_password,
						                'user_login' => $client_email,
						                'user_email' => $client_email,
						                'display_name' => $client_name,
						                'first_name' => $first_name,
						                'last_name' => $last_name
						            );
						            $client_id = wp_insert_user( $args );
						            wp_new_user_notification( $client_id, null, 'both' );
						        }
								if ( $wpsc_options['wpsc_default_agent'] == 's' ) {
						            $users = array();
						            $all_users = get_users();
						            foreach ( $all_users as $user ) {
						                if ( $user->has_cap( 'manage_wpsc_agent' ) ) {
						                    $users[] = $user->ID;
						                }
						            }
						            $rand = array_rand( $users );
						            $agent_id = $users[$rand];
						        } else if ( $wpsc_options['wpsc_default_agent'] == 'a' ) {
						            $users = array();
						            $all_users = get_users();
						            foreach ( $all_users as $user ) {
						                if ( $user->has_cap( 'manage_wpsc_ticket' ) ) {
						                    $users[] = $user->ID;
						                }
						            }
						            $rand = array_rand( $users );
						            $agent_id = $users[$rand];
						        } else {
						            $agent_id = $wpsc_options['wpsc_default_agent'];
						        }
								$data = array(
						            'subject' => $subject,
						            'client_id' => $client_id,
						            'client' => $client_name,
						            'client_email' => $client_email,
						            'category_id' => $category,
						            'agent_id' => $agent_id,
						            'priority_id' => $priority,
						            'created_timestamp' => $timestamp,
						            'updated_timestamp' => $timestamp,
						            'updated_by'=> $client_id
						        );
						        $format = array(
						            '%s',
						            '%d',
						            '%s',
						            '%s',
						            '%d',
						            '%d',
						            '%d',
						            '%s',
						            '%s',
						            '%d'
						        );
						        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_tickets', $data, $format );
						        $ticket_id = $wpdb->insert_id;
								wpsc_update_ticket_status( $ticket_id, $status );
								if ( is_array( $wpsc_options['wpsc_new_tickets'] ) ) {
						            if ( !in_array( $ticket_id, $wpsc_options['wpsc_new_tickets'] ) ) {
						                array_push( $wpsc_options['wpsc_new_tickets'], $ticket_id );
						            }
						        } else {
						            $wpsc_options['wpsc_new_tickets'] = array( $ticket_id );
						        }
								$wpsc_options['wpsc_sla'][$ticket_id] = '1';
								$cc_email = array();
								if ( isset( $header->cc ) ) {
									$cc_array = $header->cc;
									foreach ( $cc_array as $cc_item ) {
										$cc_email[] = $cc_item->mailbox . '@' . $cc_item->host;
									}
								}
								$cc = ( is_array( $cc_email ) ) ? implode( ',', $cc_email ) : '';
								$data = array(
						            'ticket_id' => $ticket_id,
						            'message' => base64_encode( $message ),
						            'attachments' => $attach,
						            'author_id' => $client_id,
						            'author' => $client_name,
						            'author_email' => $client_email,
						            'to_email' => $to,
						            'cc_email' => $cc,
						            'thread_timestamp' => $timestamp
						        );
						        $format = array(
						            '%d',
						            '%s',
						            '%s',
						            '%d',
						            '%s',
						            '%s',
						            '%s',
						            '%s',
						            '%s'
						        );
						        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
						        $thread_id = $wpdb->insert_id;
								include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
				        		wpsc_notification( 'new_ticket_front', $ticket_id, $thread_id );
							} else {
								$attach_ids = array();
								foreach ( $attachments as $attachment ) {
									$attach_ids[] = $attachment[0];
								}
								$attach = ( !empty( $attach_ids ) ) ? implode( ',', $attach_ids ) : '';
								$pattern = '/\[([^]]+)\]/';
						        preg_match( $pattern, $subject, $matches );
						        $ticket_id = preg_replace( "/[^0-9,.]/", "", $matches[0] );
								$user = get_user_by( 'email', $from_email );
						        if ( $user ) {
						            $user_id = $user->ID;
						            $author = $user->display_name;
						            $author_email = $user->user_email;
						        } else {
						            $user_id = 0;
						            $author = $from_name;
						            $author_email = $from_email;
						        }
								$sql = "SELECT agent_id FROM " . $wpdb->prefix . "wpsc_tickets WHERE id=" . $ticket_id;
						        $agent_id = $wpdb->get_var( $sql );
                                $wpsc_do_set_reminders = apply_filters( 'wpsc_do_set_reminders', true, $author_email );
						        if ( $user_id != $agent_id && false !== $wpsc_do_set_reminders ) {
						            if ( is_array( $wpsc_options['wpsc_replies'] ) ) {
						                if ( !in_array( $ticket_id, $wpsc_options['wpsc_replies'] ) ) {
						                    array_push( $wpsc_options['wpsc_replies'], $ticket_id );
						                }
						            } else {
						                $wpsc_options['wpsc_replies'] = array( $ticket_id );
						            }
									$wpsc_options['wpsc_sla'][$ticket_id] = '1';
						        }
						        $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET
						            updated_timestamp='" . $timestamp . "',
						            updated_by='" . $user_id . "'
						            WHERE id=" . $ticket_id;
						        $update = $wpdb->query( $sql );
						        if ( $user_id != $agent_id ) {
						            $status = apply_filters( 'wpsc_check_status_to_set', 5, $author_email );
						        	wpsc_update_ticket_status( $ticket_id, $status );
								}
						        $seperator = $wpsc_options['wpsc_seperator'];
						        if ( strpos( $message, $seperator ) !== false ) {
						            $message = substr( $message, 0, strpos( $message, $seperator ) );
						        }
								$cc_email = array();
								if ( isset( $header->cc ) ) {
									$cc_array = $header->cc;
									foreach ( $cc_array as $cc_item ) {
										$cc_email[] = $cc_item->mailbox . '@' . $cc_item->host;
									}
								}
								$cc = ( is_array( $cc_email ) ) ? implode( ',', $cc_email ) : '';
						        $data = array(
						            'ticket_id' => $ticket_id,
						            'message' => base64_encode( $message ),
						            'attachments' => $attach,
						            'author_id' => $user_id,
						            'author' => $author,
						            'author_email' => $author_email,
						            'to_email' => $to,
						            'cc_email' => $cc,
						            'thread_timestamp' => $timestamp
						        );
						        $format = array(
						            '%d',
						            '%s',
						            '%s',
						            '%d',
						            '%s',
						            '%s',
						            '%s',
						            '%s',
						            '%s'
						        );
						        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
						        $thread_id = $wpdb->insert_id;
						        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
						        wpsc_notification( 'client_reply', $ticket_id, $thread_id );
							}
						}
						$status = imap_setflag_full( $imap, $email_id, '\\SEEN' );
						update_option( 'wpsc_options', $wpsc_options );
					}
				}
				imap_errors();
				imap_alerts();
				imap_close( $imap );
			}
		}
	}
}
?>