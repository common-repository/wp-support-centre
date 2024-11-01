<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Process Recurring Tickets
 *
 * $messageParts    array
 * $flattenedParts  array
 * $prefix          string
 * $index           integer
 * $fullPrefix      boolean
 *
 * return:          $flattenedParts
 *
 */
if ( !function_exists( 'do_wpsc_recurring_tickets' ) ) {
    function do_wpsc_recurring_tickets( $recurring_ticket_id = 0 ) {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        //$run_date = date( 'Y-m-d H:i:s', strtotime( date( 'Y-m-d', current_time( 'timestamp' ) ) . ' ' . $wpsc_options['wpsc_recurring_tickets_scheduled_time'] ) );
        $run_date = date( 'Y-m-d H:i:s', strtotime( date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) ) );
        if ( $recurring_ticket_id > 0 ) {
            $sql = "SELECT
            			r.*,
            			t.client_phone
            		FROM
            			" . $wpdb->prefix . "wpsc_tickets_recurring r
        			LEFT JOIN
        				(SELECT
        					MAX(client_phone) AS client_phone,client_id
        				FROM
        					" . $wpdb->prefix . "wpsc_tickets
        				GROUP BY
        					client_id)
        				AS
        					t
						ON
							t.client_id=r.client_id
        			WHERE
        				r.id=" . $recurring_ticket_id . "
        			AND
        				r.enabled=1";
        } else {
            $sql = "SELECT
            			r.*,
            			t.client_phone
            		FROM
            			" . $wpdb->prefix . "wpsc_tickets_recurring r
        			LEFT JOIN
        				(SELECT
        					MAX(client_phone) AS client_phone,client_id
        				FROM
        					" . $wpdb->prefix . "wpsc_tickets
        				GROUP BY
        					client_id)
        				AS
        					t
						ON
							t.client_id=r.client_id
        			WHERE
						r.enabled=1
					AND
						r.next_timestamp<='" . $run_date . "'";
        }
        $wpsc_options['wpsc_cron_sql'] = $sql;
        $result = $wpdb->get_results( $sql, OBJECT );
        if ( $wpdb->num_rows > 0 ) {
            foreach( $result as $ticket ) {
                $user = get_user_by( 'id', $ticket->client_id );
                if ( $user ) {
                    $data = array(
                        'subject' => $ticket->subject,
                        'client_id' => $ticket->client_id,
                        'category_id' => $ticket->category_id,
                        'agent_id' => $ticket->agent_id,
                        'priority_id' => $ticket->priority_id,
                        'updated_by' => $ticket->client_id,
                        'created_timestamp' => current_time( 'mysql', 1 ),
                        'updated_timestamp' => current_time( 'mysql', 1 ),
                        'client' => $user->display_name,
                        'client_email' => $user->user_email,
                        'client_phone' => $ticket->client_phone
                    );
                    $format = array(
                        '%s',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    );
                    $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_tickets', $data, $format );
                    $ticket_id = $wpdb->insert_id;
                    wpsc_update_ticket_status( $ticket_id, $ticket->status_id );
                    $data = array(
                        'ticket_id' => $ticket_id,
                        'message' => base64_encode( $ticket->thread ),
                        'attachments' => $ticket->attachments,
                        'author_id' => $ticket->client_id,
                        'author' => $user->display_name,
                        'author_email' => $user->user_email,
                        'to_email' => $user->user_email,
                        'thread_timestamp' => current_time( 'mysql', 1 )
                    );
                    $format = array(
                        '%d',
                        '%s',
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    );
                    $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
                    $thread_id = $wpdb->insert_id;
                    $next_timestamp = '';
                    $date = new DateTime( $ticket->next_timestamp );
                    switch ( $ticket->schedule ) {
                        case '1':
                            $interval = new DateInterval( 'P1D' );
                            $date->add( $interval );
                            $next_timestamp = $date->format( 'Y-m-d' );
                            break;
                        case '2':
                            $interval = new DateInterval( 'P1W' );
                            $date->add( $interval );
                            $next_timestamp = $date->format( 'Y-m-d' );
                            break;
                        case '3':
                            $interval = new DateInterval( 'P2W' );
                            $date->add( $interval );
                            $next_timestamp = $date->format( 'Y-m-d' );
                            break;
                        case '4':
                            $split = explode( '-', date( "Y-m-d", strtotime( $ticket->next_timestamp ) ) );
                            $yy = (int)$split[0];
                            $mm = (int)$split[1] + 1;
                            $dd = (int)$split[2];
                            $yy = ( $mm > 12 ) ? $yy + 1 : $yy;
                            $mm = ( $mm > 12 ) ? $mm - 12 : $mm;
                            $next_timestamp = $yy . '-' . $mm . '-' . $dd;
                            break;
                        case '5':
                            $split = explode( '-', date( "Y-m-d", strtotime( $ticket->next_timestamp ) ) );
                            $yy = (int)$split[0];
                            $mm = (int)$split[1] + 3;
                            $dd = (int)$split[2];
                            $yy = ( $mm > 12 ) ? $yy + 1 : $yy;
                            $mm = ( $mm > 12 ) ? $mm - 12 : $mm;
                            $next_timestamp = $yy . '-' . $mm . '-' . $dd;
                            break;
                        case '6':
                            $interval = new DateInterval( 'P1Y' );
                            $date->add( $interval );
                            $next_timestamp = $date->format( 'Y-m-d' );
                            break;
                        default:
                            $next_timestamp = "0000-00-00 00:00:00";
                            break;
                    }
                    if ( $next_timestamp != "0000-00-00 00:00:00" ) {
                        $next_timestamp .= ' ' . $wpsc_options['wpsc_recurring_tickets_scheduled_time'];
                    }
                    $data = array(
                        'next_timestamp' => $next_timestamp
                    );
                    $format = array(
                        '%s'
                    );
                    $where = array(
                        'id' => $ticket->id
                    );
                    $where_format = array(
                        '%d'
                    );
                    $update = $wpdb->update( $wpdb->prefix . 'wpsc_tickets_recurring', $data, $where, $format, $where_format );
                    $wpsc_options['wpsc_next_timestamp'] = $next_timestamp;
                    if ( $ticket->notify == 1 ) {
                        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
                        wpsc_notification( 'new_recurring_ticket', $ticket_id, $thread_id );
                    }
                }
            }
        }
        $wpsc_options['wpsc_recurring_tickets_last_run'] = current_time( 'mysql', 1 );
        update_option( 'wpsc_options', $wpsc_options );
    }
}

/**
 * Flatten Parts
 *
 * $messageParts    array
 * $flattenedParts  array
 * $prefix          string
 * $index           integer
 * $fullPrefix      boolean
 *
 * return:          $flattenedParts
 *
 */
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

/**
 * Get Part
 *
 * $connection      object
 * $messageNumber   integer
 * $partNumber      integer
 * $encoding        string
 *
 * return:          decoded part
 *
 */
if ( !function_exists( 'getPart' ) ) {
    function getPart( $connection, $messageNumber, $partNumber, $encoding ) {
        $data = imap_fetchbody( $connection, $messageNumber, $partNumber );
        switch( $encoding ) {
            case 0: return imap_qprint( $data ); // 7BIT
            case 1: return quoted_printable_decode( $data ); // 8BIT
            case 2: return imap_binary( $data ); // BINARY
            case 3: return base64_decode( $data ); // BASE64
            case 4: return quoted_printable_decode( $data ); // QUOTED_PRINTABLE
            case 5: return $data; // OTHER
            default: return $data;
        }
    }
}

/**
 * Get Filename From Part
 *
 * $part            object
 *
 * return:          $filename
 *
 */
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

/**
 * Get Image ID from Filename
 *
 * $url             string
 *
 * return:          $attachment
 *
 */
if ( !function_exists( 'getImageID' ) ) {
    function getImageID( $url ) {
        global $wpdb;
        $attachment = $wpdb->get_var( "SELECT ID FROM " . $wpdb->posts . " WHERE guid='" . $url . "'" );
        return $attachment;
    }
}

/**
 * Return Known Email Addresses
 *
 * $id              string
 * $class           string
 * $ticket_id       integer
 * $selection       array
 *
 * return:          $return
 *
 */
if ( !function_exists( 'wpsc_address_book' ) ) {
    function wpsc_address_book( $id = '', $class = '', $ticket_id = 0, $selection = array() ) {
    	global $wpdb;
    	$addresses = array();
    	$return = '';
    	$return .= '<select id="' . $id . '" class="' . $class . ' wpsc_chosen wpsc_address_book_chosen form-control" multiple="multiple" >';
    		$args = array(
    			'orderby' => 'email',
    			'order' => 'ASC'
    		);
    		$users = get_users( $args );
    		foreach( $users as $user ) {
    			if ( !isset( $addresses[$user->user_email] ) ) {
    				$addresses[$user->user_email] = $user->display_name;
    			}
    		}
    		$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads GROUP BY author_email ORDER BY author ASC, author_email ASC";
            $results = $wpdb->get_results( $sql );
    		if ( null !== $results ) {
    			foreach( $results as $result ) {
    				if ( !isset( $addresses[$result->author_email] ) ) {
    					$addresses[$result->author_email] = $result->author;
    				} else {
    					if ( $addresses[$result->author_email] == '' && $result->author != '' ) {
    						$addresses[$result->author_email] = $result->author;
    					}
    				}
    			}
    			if ( $result->cc_email != '' ) {
    				$cc_emails = explode( ',', $result->cc_email );
    				foreach( $cc_emails as $cc_email ) {
    					if ( !isset( $addresses[$cc_email] ) ) {
    						$user = get_user_by( 'email', $cc_email );
    						if ( $user ) {
    							$addresses[$cc_email] = $user->display_name;
    						} else {
    							$addresses[$cc_email] = '';
    						}
    					}
    				}
    			}
    			if ( $result->bcc_email != '' ) {
    				$bcc_emails = explode( ',', $result->bcc_email );
    				foreach( $bcc_emails as $bcc_email ) {
    					if ( !isset( $addresses[$bcc_email] ) ) {
    						$user = get_user_by( 'email', $bcc_email );
    						if ( $user ) {
    							$addresses[$bcc_email] = $user->display_name;
    						} else {
    							$addresses[$bcc_email] = '';
    						}
    					}
    				}
    			}
    		}
    		$address_list = array();
    		if ( $ticket_id != 0 ) {
            	$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE ticket_id=" . $ticket_id . " GROUP BY author_email ORDER BY author ASC, author_email ASC";
                $results = $wpdb->get_results( $sql );
    			if ( null !== $results ) {
    				foreach( $results as $result ) {
    					if ( !isset( $addresses[$result->author_email] ) ) {
    						$address_list[$result->author_email] = $result->author;
    					} else {
    						$address_list[$result->author_email] = $addresses[$result->author_email];
    						unset( $addresses[$result->author_email] );
    					}
    				}
    			}
    		}
    		ksort( $address_list );
    		ksort( $addresses );
    		foreach( $address_list as $address=>$display_name ) {
    			$selected = ( in_array( $address, $selection ) ) ? ' selected="selected"' : '';
    			if ( $display_name != '' ) {
    				$return .= '<option value="' . $address . '"' . $selected . '>' . $display_name . ' (' . $address . ')</option>';
    			} else {
    				$return .= '<option value="' . $address . '"' . $selected . '>' . $address . '</option>';
    			}
    		}
    		foreach( $addresses as $address=>$display_name ) {
    			$selected = ( in_array( $address, $selection ) ) ? ' selected="selected"' : '';
    			if ( $display_name != '' ) {
    				$return .= '<option value="' . $address . '"' . $selected . '>' . $display_name . ' (' . $address . ')</option>';
    			} else {
    				$return .= '<option value="' . $address . '"' . $selected . '>' . $address . '</option>';
    			}
    		}
        $return .= '</select>';
    	return $return;
    }
}

/**
 * Update Ticket Status
 *
 * $ticket_id       integer
 * $status_id       integer
 *
 * return:          N/A
 *
 */
if ( !function_exists( 'wpsc_update_ticket_status' ) ) {
    function wpsc_update_ticket_status( $ticket_id, $status_id ) {
    	if ( is_numeric( $ticket_id ) && is_numeric( $status_id ) ) {
    	    global $wpdb;
    		$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    		$time = current_time( 'timestamp', 1 );
    		$insert = array();
    		$sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET status_id=" . $status_id . " WHERE id=" . $ticket_id;
    	    $update = $wpdb->query( $sql );
    		$sql = "DELETE FROM " . $wpdb->prefix . "wpsc_reminders WHERE ticket_id=" . $ticket_id . " AND reminder=0";
    		$delete = $wpdb->query( $sql );
    		if ( $status_id == 1 || $status_id == 5 ) {
    			$sql = "SELECT
    		    			p.priority_sla
    		    		FROM
    		    			" . $wpdb->prefix . "wpsc_tickets t
    		    		LEFT JOIN
    		    			" . $wpdb->prefix . "wpsc_priority p
    		    		ON
    		    			p.id=t.priority_id
    		    		WHERE
    		    			t.id=" . $ticket_id;
    			$priority_sla = $wpdb->get_var( $sql );
    		    $priority_sla_reminder = $time + ( 60 * $priority_sla );
    			$priority_sla_timestamp = date( "Y-m-d H:i:s", $priority_sla_reminder );
    			$priority_sla_subject = 'SLA for Ticket # ' . $ticket_id . ' has been reached.';
    			$insert[] = "INSERT INTO " . $wpdb->prefix . "wpsc_reminders (ticket_id,subject,reminder,due_timestamp) VALUES (" . esc_sql( $ticket_id ) . ",'" . $priority_sla_subject . "',0,'" . $priority_sla_timestamp . "')";
    		}
    		if ( $status_id != 2 && $status_id != 3 ) {
    			$inactivity_sla = ( isset( $wpsc_options['wpsc_inactivity'] ) && is_numeric( $wpsc_options['wpsc_inactivity'] ) && $wpsc_options['wpsc_inactivity'] > 0 ) ? $wpsc_options['wpsc_inactivity'] : 7;
    		    $inactivity_sla_reminder = $time + ( 86400 * $inactivity_sla );
    			$inactivity_sla_timestamp = date( "Y-m-d H:i:s", $inactivity_sla_reminder );
    			$inactivity_sla_subject = 'Ticket # ' . $ticket_id . ' has been idle for ' . $inactivity_sla . ' days. Please review this ticket.';
    			$insert[] = "INSERT INTO " . $wpdb->prefix . "wpsc_reminders (ticket_id,subject,reminder,due_timestamp) VALUES (" . esc_sql( $ticket_id ) . ",'" . $inactivity_sla_subject . "',0,'" . $inactivity_sla_timestamp . "')";
    		}
    		foreach( $insert as $sql ) {
    			$query = $wpdb->query( $sql );
    		}
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
		}
	}
}

if ( !function_exists( 'wpsc_imap_get_by_id' ) ) {
    function wpsc_imap_get_by_id( $uid, $account_id ) {
        global $wpdb;
        $transient_id = $uid . ':' . $account_id;
        $transient = get_transient( $transient_id );
        if ( $transient ) {
        	delete_transient( $transient_id );
        }
        $transient = false;
        if ( false === $transient ) {
            $transient = array();
            $transient['transient_id'] = $transient_id;
            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_imap WHERE id=" . esc_sql( $account_id );
            $account = $wpdb->get_row( $sql, ARRAY_A );
            $server = $account['imap_server'];
            $port = $account['imap_port'];
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
            $username = $account['imap_username'];
            $password = $account['imap_password'];
            $type = $account['imap_type'];
			foreach ( $argstrings as $argstring ) {
				$connect = $server . ':' . $port . $argstring;
				try {
					$imap = wpscOpenIMAP( $connect, $username, $password, true );
				}
				catch ( Exception $e ) {
					//echo $e->getMessage();
				}
                //$imap = imap_open( "{" . $connect . "}", $username, $password, OP_READONLY );
                if ( isset( $imap ) && $imap ) {
                	//echo 'Connection established using: ' . $connect;
                    break;
                }
            }
            $transient['email_id'] = imap_msgno( $imap, $uid );
            $transient['attachments'] = array();
            $header = imap_headerinfo( $imap, $transient['email_id'] );
            $transient['header'] = $header;

			// ** TIMEZONE CALCULATOR **
            $timestamp = ( isset( $header->Date ) ) ? date( "Y-m-d H:i:s", strtotime( $header->Date ) ) : current_time( 'mysql', 1 );
            /*if ( isset( $header->Date ) ) {
            	$timestamp_array = date_parse( $header->Date );
				$offset = $timestamp_array['zone'] * 60; // gives a value in seconds to be subtracted from the unix timestamp;
				$unix_timestamp = strtotime( $header->Date );
				$gmt_timestamp = $unix_timestamp + $offset;
				$timestamp = date( "Y-m-d H:i:s", $gmt_timestamp );
            } else {
            	$timestamp = current_time( 'mysql', 1 );
            }*/
            $transient['timestamp'] = get_date_from_gmt( $timestamp );
            if ( isset ( $header->from[0]->personal ) ) {
                $from_name = imap_mime_header_decode( $header->from[0]->personal );
                $transient['from_name'] = ( $from_name[0]->text != '' ) ? $from_name[0]->text : $header->from[0]->mailbox . '@' . $header->from[0]->host;
                $transient['from_email'] = $header->from[0]->mailbox . '@' . $header->from[0]->host;
                $transient['from'] = $transient['from_name'] . ' (' . $transient['from_email'] . ')';
            } else {
                $from_name = imap_mime_header_decode( $header->from[0]->personal );
                $transient['from_name'] = ( $from_name[0]->text != '' ) ? $from_name[0]->text : $header->from[0]->mailbox . '@' . $header->from[0]->host;
                $transient['from_email'] = $header->from[0]->mailbox . '@' . $header->from[0]->host;
                $transient['from'] = $transient['from_email'];
            }
			$to_email = array();
			if ( isset( $header->to ) ) {
                $to_array = $header->to;
                foreach ( $to_array as $to_item ) {
                    $to_email[] = $to_item->mailbox . '@' . $to_item->host;
                }
            }
            $cc_email = array();
            if ( isset( $header->cc ) ) {
                $cc_array = $header->cc;
                foreach ( $cc_array as $cc_item ) {
                    $cc_email[] = $cc_item->mailbox . '@' . $cc_item->host;
                }
            }
			$transient['to_email'] = ( is_array( $to_email ) ) ? implode( ',', $to_email ) : '';
            $transient['cc_email'] = ( is_array( $cc_email ) ) ? implode( ',', $cc_email ) : '';
            $transient['bcc_email'] = '';
            $transient['subject'] = imap_mime_header_decode( $header->subject );
            $transient['subject'] = $transient['subject'][0]->text;
            $structure = imap_fetchstructure( $imap, $transient['email_id'] );
            if ( isset( $structure->parts ) ) {
				$parts = flattenParts( $structure->parts );
                foreach ( $parts as $partno=>$part ) {
                    switch( $part->type ) {
                        case 0: // the HTML or plain text part of the email
                        	$message = getPart( $imap, $transient['email_id'], $partno, $part->encoding );
							if ( $part->subtype == 'HTML' ) {
                            	$transient['message'] = $message;
							} else {
								$transient['message'] .= $message . '<br /><br />';
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
                            $fileid = preg_match( "/<(.*)>/", $part->id, $matches );
                            $fileid = $matches[1];
                            if ( $filename ) { // it's an attachment
                                clearstatcache();
                                $inline = ( isset( $part->ifparameters ) && $part->ifparameters == 1 ) ? true : false;
                                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                                require_once( ABSPATH . 'wp-admin/includes/media.php' );
                                $upload_dir = wp_upload_dir();
                                $save_directory = $upload_dir['basedir'] . '/wpsc_temp/';
                                $save_path = $upload_dir['baseurl'] . '/wpsc_temp/';
                                $save_url = $upload_dir['url'] . '/wpsc_temp/';
                                if ( !file_exists( $save_directory ) ) {
                                    mkdir( $save_directory, 0755 );
                                }
                                $attachment = getPart( $imap, $transient['email_id'], $partno, $part->encoding );
                                $mimeinfo = imap_fetchmime( $imap, $transient['email_id'], $partno );
                                $mimeinfo = explode( ';', $mimeinfo );
                                $mimeinfo = $mimeinfo[0];
                                $mimeinfo = explode( ':', $mimeinfo );
                                $mimetype = trim( $mimeinfo[1] );
                                $filetype = wp_check_filetype( $filename );
                                $fileext = strtolower( $filetype['ext'] );
                                $allowed = false;
                                $mimes = get_allowed_mime_types();
                                foreach ( $mimes as $type=>$mime ) {
                                    if ( $fileext != ''  && !empty( $fileext ) ) {
                                        if ( strpos( $type, $fileext ) !== false ) {
                                            $allowed = true;
                                        }
                                    }
                                }
                                if ( $allowed ) {
                                	$tmpdir = get_temp_dir();
									$savename = $filename;
                                    $tmpname = time() . "_" . $filename;
                                    $fp = fopen( $tmpdir . $tmpname, "w+" );
                                    fwrite( $fp, $attachment );
                                    fclose( $fp );
                                    $filehash = hash_file( 'md5', $tmpdir . $tmpname );
                                    $sql = "SELECT * FROM " . $wpdb->prefix . "postmeta WHERE meta_value='" . $filehash . "'";
                                    $result = $wpdb->get_row( $sql );
                                    if ( null === $result ) {
										$tmpfile = $tmpdir . $tmpname;
										while( file_exists( $save_directory . $savename ) ) {
											$savename = time() . "_" . $filename;
										}
										copy( $tmpfile, $save_directory . $savename );
                                        $data = array(
                                            'guid' => $upload_dir['url'] . '/' . $savename,
                                            'post_title' => $savename,
                                            'post_content' => '',
                                            'post_status' => 'inherit',
                                            'post_mime_type' => $mimetype
                                        );
                                        $attach_id = wp_insert_attachment( $data, $save_directory . $savename );
                                        update_post_meta( $attach_id, 'filehash', $filehash );
                                    } else if ( !file_exists( get_attached_file( $result->post_id ) ) ) {
                                    	$tmpfile = $tmpdir . $tmpname;
                                    	copy( $tmpfile, get_attached_file( $result->post_id ) );
										$attach_id = $result->post_id;
                                        unlink( $tmpdir . $tmpname );
									} else {
                                        $attach_id = $result->post_id;
                                        unlink( $tmpdir . $tmpname );
                                    }
                                    //$attach_data = wp_generate_attachment_metadata( $attach_id, $save_directory . $savename );
                                    //wp_update_attachment_metadata( $attach_id, $attach_data );
                                } else {
                                    $tmpdir = get_temp_dir();
                                    $savename = $filename;
                                    $tmpname = time() . "_" . $filename;
                                    $fp = fopen( $tmpdir . $tmpname, "w+" );
                                    fwrite( $fp, $attachment );
                                    fclose( $fp );
                                    $zipfile = $savename . '.zip';
                                    $zip = new ZipArchive();
                                    if ( $zip->open( $tmpdir . $zipfile, ZipArchive::CREATE ) ) {
                                        $zip->addFile( $tmpdir . $tmpname, $savename );
                                        $zip->close();
                                        $filetype = wp_check_filetype( $zipfile );
                                        $filehash = hash_file( 'md5', $tmpdir . $zipfile );
                                        $sql = "SELECT * FROM " . $wpdb->prefix . "postmeta WHERE meta_value='" . $filehash . "'";
                                        $result = $wpdb->get_row( $sql );
                                        if ( null === $result ) {
											$tmpfile = $tmpdir . $tmpname;
											while( file_exists( $save_directory . $zipfile ) ) {
												$zipfile = time() . '_' . $savename . '.zip';
											}
											copy( $tmpfile, $save_directory . $zipfile );
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
                                    unlink( $tmpdir . $tmpname );
                                }
                                $transient['attachments'][] = array( $attach_id, $filename, $fileid, $inline );
                            } else { // don't know what it is

                            }
                            break;
                    }
                }
            } else {
                $transient['message'] = imap_fetchbody( $imap, $transient['email_id'], 1 );
            }
            $transient['message'] = imap_utf8( $transient['message'] );
            $transient['message'] = utf8_decode( $transient['message'] );
            $transient['message'] = str_replace ( '&nbsp;', ' ', $transient['message'] );
            preg_match_all('/src="cid:(.*)"/Uims', $transient['message'], $matches);
            if ( !empty( $matches[1] ) ) {
                foreach ( $matches[1] as $cid ) {
                    $embedded_file = explode( '@', $cid );
                    $embedded_file = $embedded_file[0];
                    foreach ( $transient['attachments'] as $attachment ) {
                        if ( $attachment[1] && $embedded_file == $attachment[1] ) {
                            $attach_url = wp_get_attachment_url( $attachment[0] );
                            $transient['message'] = str_replace( 'cid:' . $cid, $attach_url, $transient['message'] );
                        }
                    }
                }
            }
            $transient['message'] = base64_encode( $transient['message'] );
            $transient['attach_ids'] = array();
            foreach ( $transient['attachments'] as $attachment ) {
                $transient['attach_ids'][] = $attachment[0];
            }
            $transient['attach'] = ( !empty( $transient['attach_ids'] ) ) ? implode( ',', $transient['attach_ids'] ) : '';
            $user = get_user_by( 'email', $transient['from_email'] );
            if ( $user ) {
                $transient['user']['user_id'] = $user->ID;
                $transient['user']['author'] = $user->display_name;
                $transient['user']['author_email'] = $user->user_email;
            } else {
                $transient['user']['user_id'] = 0;
                $transient['user']['author'] = $transient['from_name'];
                $transient['user']['author_email'] = $transient['from_email'];
            }
            $transient['set'] = set_transient( $transient_id, $transient, 24 * HOUR_IN_SECONDS );
			imap_errors();
			imap_alerts();
            imap_close( $imap );
        }
        return $transient;
    }
}
?>