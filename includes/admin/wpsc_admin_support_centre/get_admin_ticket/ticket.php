<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$output .= '<div id="wpsc_ticket_' . $ticket_id . '" class="tab-pane fade active in">';
    $sql = "
        SELECT
            t.id,t.subject,t.updated_timestamp,t.client_id,t.client,t.agent_id,t.category_id,t.priority_id,t.status_id,t.client_email,t.client_phone,t.shared_users,
            s.status,s.colour AS status_colour,
            c.category,
            p.priority,p.priority_sla,p.colour AS priority_colour,
            ua.display_name AS agent
        FROM " . $wpdb->prefix . "wpsc_tickets t
        LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
        LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
        LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
        LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id
        WHERE t.id=" . $ticket_id;
    $ticket = $wpdb->get_row( $sql, OBJECT );
    if ( $wpdb->num_rows > 0 ) {
    	// new layout
    	$output .= '<div class="panel panel-default">';
            $output .= '<div class="panel-heading"><h4 class="panel-title">[' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ': ' . $ticket->id . '] ' . stripcslashes( $ticket->subject ) . ' <span class="wpsc_required">* = required</span></h4></div>';
            $output .= '<div class="panel-body panel-body-wheat">';
                $output .= '<form method="post" class="form-horizontal">';
                    $output .= '<div class="row row-eq-height">';
                    	// ***************************************************************************************************
                    	// left column
						$output .= '<div class="col-xs-12 col-sm-4">';
							// ***************************************************************************************************
                    		// client name
							$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
									$client_name = '';
		                            $client_name .= '<label for="wpsc_admin_ticket_client_name_' . $ticket->id . '">' . apply_filters( 'wpsc_client', 'Client', $wpsc_options ) . ' Name (<a href="' . admin_url( 'admin.php?page=wp-support-centre&filter=true&client_id=' . $ticket->client_id ) . '" target="_blank">Ticket History</a>)</label>';
		                            $client_name .= '<input type="text" id="wpsc_admin_ticket_client_name_' . $ticket->id . '" value="' . stripcslashes( $ticket->client ) . '" class="form-control" readonly>';
									$output .= apply_filters( 'wpsc_admin_ticket_client_name', $client_name, $ticket->client, $ticket->id, $wpsc_options );
		                        $output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// client email
							$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
		                            $client_email = '';
		                            $client_email .= '<label for="wpsc_admin_ticket_client_email_' . $ticket->id . '">' . apply_filters( 'wpsc_client', 'Client', $wpsc_options ) . ' Email</label>';
                            		$client_email .= '<input type="text" id="wpsc_admin_ticket_client_email_' . $ticket->id . '" value="' . stripcslashes( $ticket->client_email ) . '" class="form-control" readonly>';
									$output .= apply_filters( 'wpsc_admin_ticket_client_email', $client_email, $ticket->client_email, $ticket->id, $wpsc_options );
		                        $output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// client phone
							$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
									$client_phone = '';
		                            $client_phone .= '<label for="wpsc_admin_new_thread_client_phone_' . $ticket->id . '">' . apply_filters( 'wpsc_client', 'Client', $wpsc_options ) . ' Phone</label>';
                            		$client_phone .= '<input type="text" id="wpsc_admin_new_thread_client_phone_' . $ticket->id . '" value="' . stripcslashes( $ticket->client_phone ) . '" class="wpsc_new_thread form-control" placeholder="' . apply_filters( 'wpsc_client', 'Client', $wpsc_options ) . ' Phone">';
									$output .= apply_filters( 'wpsc_admin_ticket_client_phone', $client_phone, $ticket->client_phone, $ticket->id, $wpsc_options );
		                        $output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// status
							$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
									$ticket_status = '';
									$ticket_status .= '<label for="wpsc_admin_new_thread_status_' . $ticket->id . '">Status <span class="wpsc_required">*</span></label>';
		                            $ticket_status .= '<select id="wpsc_admin_new_thread_status_' . $ticket->id . '" class="wpsc_admin_new_thread_status wpsc_new_thread wpsc_new_thread_validate form-control wpsc_status_select" data-id="' . $ticket->id . '">';
		                                $ticket_status .= '<option value="">Please select...</option>';
		                                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_status WHERE enabled=1 ORDER BY status ASC";
		                                $result = $wpdb->get_results( $sql );
		                                foreach ( $result as $status ) {
		                                    $selected = ( $status->id == $ticket->status_id ) ? ' selected="selected"' : '';
		                                    $ticket_status .= '<option value="' . $status->id . '"' . $selected . '>' . stripcslashes( $status->status ) . '</option>';
		                                }
		                            $ticket_status .= '</select>';
									$output .= apply_filters( 'wpsc_admin_ticket_status', $ticket_status, $ticket->status_id, $ticket->id, $wpsc_options );
								$output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
							// additional fields
							$output .= apply_filters( 'wpsc_additional_fields', '', $ticket, 'status' );
							// ***************************************************************************************************
                    		// category
							$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
									$ticket_category = '';
									$ticket_category .= '<label for="wpsc_admin_new_thread_category_' . $ticket->id . '">Category <span class="wpsc_required">*</span></label>';
		                            $ticket_category .= '<select id="wpsc_admin_new_thread_category_' . $ticket->id . '" class="wpsc_new_thread wpsc_new_thread_validate form-control wpsc_category_select">';
		                                $ticket_category .= '<option value="">Please select...</option>';
		                                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_categories WHERE enabled=1 ORDER BY category ASC";
		                                    $result = $wpdb->get_results( $sql );
		                                    foreach ( $result as $category ) {
		                                        $selected = ( $category->id == $ticket->category_id ) ? ' selected="selected"' : '';
		                                        $ticket_category .= '<option value="' . $category->id . '"' . $selected . '>' . $category->category . '</option>';
		                                    }
		                            $ticket_category .= '</select>';
									$output .= apply_filters( 'wpsc_admin_ticket_category', $ticket_category, $ticket->category_id, $ticket->id, $wpsc_options );
								$output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// agent
							$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
									$agent = '';
									$agent .= '<label for="wpsc_admin_new_thread_agent_' . $ticket->id . '">Agent <span class="wpsc_required">*</span></label>';
		                            $agent .= '<select id="wpsc_admin_new_thread_agent_' . $ticket->id . '" class="wpsc_new_thread wpsc_new_thread_validate form-control">';
		                                $agent .= '<option value="">Please select...</option>';
		                                $args = array(
		                                    'orderby' => 'display_name',
		                                    'order' => 'ASC'
		                                );
		                                $all_users = get_users( $args );
		                                foreach ( $all_users as $user ) {
		                                    if ( $user->has_cap( 'manage_wpsc_ticket' ) ) {
		                                        $selected = ( $user->ID == $ticket->agent_id ) ? ' selected="selected"' : '';
		                                        $agent .= '<option value="' . $user->ID . '"' . $selected . '>' . $user->display_name . '</option>';
		                                    }
		                                }
		                            $agent .= '</select>';
		                            $output .= apply_filters( 'wpsc_admin_ticket_agent', $agent, $ticket->agent_id, $ticket->id, $wpsc_options );
								$output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// priority
							$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
									$ticket_priority = '';
									$ticket_priority .= '<label for="wpsc_admin_new_thread_priority_' . $ticket->id . '">Priority <span class="wpsc_required">*</span></label>';
		                            $ticket_priority .= '<select id="wpsc_admin_new_thread_priority_' . $ticket->id . '" class="wpsc_new_thread wpsc_new_thread_validate form-control">';
		                                $ticket_priority .= '<option value="">Please select...</option>';
		                                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority WHERE enabled=1";
		                                $result = $wpdb->get_results( $sql );
		                                foreach ( $result as $priority ) {
		                                    $selected = ( $priority->id == $ticket->priority_id ) ? ' selected="selected"' : '';
		                                    $ticket_priority .= '<option value="' . $priority->id . '"' . $selected . '>' . $priority->priority . '</option>';
		                                }
		                            $ticket_priority .= '</select>';
		                            $output .= apply_filters( 'wpsc_admin_ticket_priority', $ticket_priority, $ticket->priority_id, $ticket->id, $wpsc_options );
								$output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// before largescreen buttons filter
							$output .= apply_filters( 'wpsc_admin_tickets_before_largescreen_buttons' ,'' );
							// ***************************************************************************************************
                    		// largescreen buttons
							$output .= '<div class="form-group largescreen-buttons">';
								// ***************************************************************************************************
                    			// save button
								$output .= '<div class="col-xs-4">';
									$button_save = '';
									$output .= apply_filters( 'wpsc_admin_ticket_save_button', $button_save, $ticket->id );
								$output .= '</div>';
								// ***************************************************************************************************
                    			// note button
								$output .= '<div class="col-xs-4">';
									$button_note = '';
									$output .= apply_filters( 'wpsc_admin_ticket_note_button', $button_note, $ticket->id );
								$output .= '</div>';
								// ***************************************************************************************************
                    			// reply button
								$output .= '<div class="col-xs-4">';
									$button_reply = '';
									$output .= apply_filters( 'wpsc_admin_ticket_reply_button', $button_reply, $ticket->id );
								$output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// after largescreen buttons filter
							$output .= apply_filters( 'wpsc_admin_tickets_after_largescreen_buttons' ,'' );
						$output .= '</div>';
						// ***************************************************************************************************
                    	// right column
						$output .= '<div class="col-xs-12 col-sm-8">';
							// ***************************************************************************************************
							// additional fields
							$output .= apply_filters( 'wpsc_additional_fields', '', $ticket, 'category' );
							// ***************************************************************************************************
                    		// subject
							$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
									$subject = '';
									$subject .= '<label for="wpsc_admin_ticket_subject_' . $ticket->id . '">' . apply_filters( 'wpsc_ticket', 'Ticket', $wpsc_options ) . ' Subject <span class="wpsc_required">*</span></label>';
    								//$subject .= '<input type="text" id="wpsc_admin_ticket_subject_' . $ticket->id . '" value="' . stripcslashes( $ticket->subject ) . '" class="form-control" placeholder="' . apply_filters( 'wpsc_ticket', 'Ticket', $wpsc_options ) . ' Subject">';
    								$subject .= '<input type="text" id="wpsc_admin_ticket_subject_' . $ticket->id . '" value="' . htmlentities( stripcslashes( $ticket->subject ) ) . '" class="form-control" placeholder="' . apply_filters( 'wpsc_ticket', 'Ticket', $wpsc_options ) . ' Subject">';
									$output .= apply_filters( 'wpsc_admin_ticket_subject', $subject, $ticket->subject, $ticket->id, $wpsc_options );
								$output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// details
                    		$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
									$details = '';
		                            $details .= '<label for="wpsc_admin_ticket_note_' . $ticket->id . '">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' Details <span class="wpsc_required">*</span></label>';
                            		//$details .= '<textarea style="width:100%;height:100%;" class="wpsc_ckeditor wpsc_admin_ticket_note form-control" id="wpsc_admin_ticket_note_' . $ticket->id . '" name="wpsc_admin_ticket_note_' . $ticket->id . '">' . html_entity_decode( stripcslashes( $ticket->message ) ) . '</textarea>';
                            		$details .= '<textarea style="width:100%;height:100%;" class="wpsc_ckeditor wpsc_admin_ticket_note form-control" id="wpsc_admin_ticket_note_' . $ticket->id . '" name="wpsc_admin_ticket_note_' . $ticket->id . '"></textarea>';
									$output .= apply_filters( 'wpsc_admin_ticket_details', $details, '', $ticket->id, $wpsc_options );
		                        $output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// attachments
							$output .= '<div class="form-group">';
								$output .= '<div class="col-xs-12 col-md-12">';
									$output .= '<div class="form-group">';
										// ***************************************************************************************************
                    					// new attachments
										$output .= '<div class="col-xs-12 col-md-4">';
											$new_attachments = '';
											$new_attachments .= '<label for="wpsc_admin_new_thread_attachments_' . $ticket->id . '">Add Attachments</label>';
		                            		$new_attachments .= '<input type="file" id="wpsc_admin_new_thread_attachments_' . $ticket->id . '"  class="wpsc_admin_new_thread_attachments" multiple="multiple" >';
											$output .= apply_filters( 'wpsc_admin_ticket_new_attachments', $new_attachments, $ticket->id );
										$output .= '</div>';
										// ***************************************************************************************************
                    					// existing attachments
										$output .= '<div class="col-xs-12 col-md-4">';
											$existing_attachments = '';
											$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE ticket_id=" . $ticket_id . " AND attachments!=''";
			                                $attachment_results = $wpdb->get_results( $sql );
			                                if ( !empty( $attachment_results ) ) {
												$existing_attachments .= '<label for="wpsc_admin_existing_thread_attachments_' . $ticket->id . '">Existing Attachments</label>';
		                            			$existing_attachments .= '<select id="wpsc_admin_existing_thread_attachments_' . $ticket->id . '" class="wpsc_admin_existing_thread_attachments wpsc_chosen wpsc_attachment_chosen form-control" multiple="multiple" >';
				                                    $attachments = array();
				                                    foreach ( $attachment_results as $attachment ) {
				                                        $split = explode( ',', $attachment->attachments );
				                                        foreach ( $split as $id ) {
				                                            if ( !in_array( $id, $attachments ) ) {
				                                                $attachments[] = $id;
				                                            }
				                                        }
				                                    }
				                                    foreach ( $attachments as $attachment ) {
				                                        $existing_attachments .= '<option value="' . $attachment . '">' . basename( get_attached_file( $attachment ) ) . '</option>';
				                                    }
				                            	$existing_attachments .= '</select>';
				                            }
											$output .= apply_filters( 'wpsc_admin_ticket_existing_attachments', $existing_attachments, $ticket->id );
										$output .= '</div>';
										// ***************************************************************************************************
                    					//
										$output .= '<div class="col-xs-12 col-md-4">';
										$output .= '</div>';
									$output .= '</div>';
								$output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// thread creator
							$output .= '<div class="form-group">';
								// ***************************************************************************************************
                    			// agent
		                        $output .= '<div class="col-xs-12 col-md-3">';
		                            $output .= '<div class="radio">';
										$agent = '';
		                                $agent .= '<input type="radio" name="wpsc_admin_thread_create_as_' . $ticket->id . '" id="wpsc_admin_thread_create_as_agent_' . $ticket->id . '" class="wpsc_admin_thread_create_as" data-id="' . $ticket->id . '" value="agent" checked="checked"><label for="wpsc_admin_thread_create_as_agent_' . $ticket->id . '"> Create As Agent </label>';
										$output .= apply_filters( 'wpsc_admin_ticket_create_as_agent', $agent, $ticket->id );
		                            $output .= '</div>';
		                        $output .= '</div>';
								// ***************************************************************************************************
                    			// client
		                        $output .= '<div class="col-xs-12 col-md-3">';
		                            $output .= '<div class="radio">';
										$client = '';
		                                $client .= '<input type="radio" name="wpsc_admin_thread_create_as_' . $ticket->id . '" id="wpsc_admin_thread_create_as_client_' . $ticket->id . '" class="wpsc_admin_thread_create_as" data-id="' . $ticket->id . '" value="client"><label for="wpsc_admin_thread_create_as_client_' . $ticket->id . '"> Create As Client </label>';
										$output .= apply_filters( 'wpsc_admin_ticket_create_as_client', $client, $ticket->id );
		                            $output .= '</div>';
		                        $output .= '</div>';
								// ***************************************************************************************************
                    			// other
		                        $output .= '<div class="col-xs-12 col-md-3">';
		                            $output .= '<div class="radio">';
										$other = '';
		                                $other .= '<input type="radio" name="wpsc_admin_thread_create_as_' . $ticket->id . '" id="wpsc_admin_thread_create_as_other_' . $ticket->id . '" class="wpsc_admin_thread_create_as" data-id="' . $ticket->id . '" value="other"><label for="wpsc_admin_thread_create_as_' . $ticket->id . '"> Create As Other </label>';
		                                $output .= apply_filters( 'wpsc_admin_ticket_create_as_other', $other, $ticket->id );
		                            $output .= '</div>';
		                        $output .= '</div>';
								// ***************************************************************************************************
                    			// private
		                        $output .= '<div class="col-xs-12 col-md-3">';
		                            $output .= '<div class="radio">';
										$private = '';
										$private .= '<input type="checkbox" name="wpsc_admin_thread_is_private_' . $ticket->id . '" id="wpsc_admin_thread_is_private_' . $ticket->id . '" data-id="' . $ticket->id . '" value="1"><label for="wpsc_admin_thread_is_private_' . $ticket->id . '"> Private? <a href="#wpsc_ticket_private_thread_dialog" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_private_thread_dialog" class="wpsc_help"></a> </label>';
		                        		$output .= apply_filters( 'wpsc_admin_ticket_private_thread', $private, $ticket->id );
									$output .= '</div>';
		                        $output .= '</div>';
		                    $output .= '</div>';
							// ***************************************************************************************************
                    		// thread creator additional fields
		                    $output .= '<div class="form-group wpsc_hidden wpsc_admin_thread_create_as_other">';
								// ***************************************************************************************************
                    			// other from name
		                        $output .= '<div class="col-xs-12 col-md-6">';
									$from_name = '';
		                            $from_name .= '<label for="wpsc_admin_new_thread_from_name_' . $ticket->id . '">From Name <span class="wpsc_required">*</span></label>';
		                            $from_name .= '<input type="text" id="wpsc_admin_new_thread_from_name_' . $ticket->id . '" class="wpsc_new_thread form-control wpsc_new_thread_validate" placeholder="From Name">';
									$output .= apply_filters( 'wpsc_admin_ticket_other_from_name', $from_name, $ticket->id );
		                        $output .= '</div>';
								// ***************************************************************************************************
                    			// other from email
		                        $output .= '<div class="col-xs-12 col-md-6">';
									$from_email = '';
		                            $from_email .= '<label for="wpsc_admin_new_thread_from_email_' . $ticket->id . '">From Email <span class="wpsc_required">*</span></label>';
		                            $from_email .= '<input type="text" id="wpsc_admin_new_thread_from_email_' . $ticket->id . '" value="' . $ticket->client_email . '" class="wpsc_new_thread form-control wpsc_new_thread_validate" placeholder="From Email">';
									$output .= apply_filters( 'wpsc_admin_ticket_other_from_email', $from_email, $ticket->id );
		                        $output .= '</div>';
		                    $output .= '</div>';
							// ***************************************************************************************************
                    		// email addressing
							$output .= '<div class="form-group">';
		                        $output .= '<div class="col-xs-12 col-md-4">';
		                        	$output .= '<div class="form-group">';
										// ***************************************************************************************************
                    					// to
                    					$to = '';
		                        		$to .= '<div class="col-xs-12">';
				                            $known_user = false;
				                            $to .= '<label for="wpsc_admin_new_thread_to_select_' . $ticket->id . '">To <span class="wpsc_required">*</span> <a href="#wpsc_ticket_email_input" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_email_input" class="wpsc_help"></a></label>';
											$to .= wpsc_address_book( 'wpsc_admin_new_thread_to_select_' . $ticket->id, 'wpsc_admin_new_thread_email_select', $ticket->id, array( $ticket->client_email ) );
										$to .= '</div>';
										$to .= '<div class="col-xs-12">';
											$to .= '<input type="text" id="wpsc_admin_new_thread_to_' . $ticket->id . '" value="" class="wpsc_new_thread form-control wpsc_new_thread_validate" placeholder="To Email">';
										$to .= '</div>';
										$output .= apply_filters( 'wpsc_admin_ticket_to', $to, $ticket->id );
									$output .= '</div>';
		                        $output .= '</div>';
		                        $output .= '<div class="col-xs-12 col-md-4">';
									$output .= '<div class="form-group">';
										// ***************************************************************************************************
                    					// cc
                    					$cc = '';
		                        		$cc .= '<div class="col-xs-12">';
				                            $cc .= '<label for="wpsc_admin_new_thread_cc_select_' . $ticket->id . '">CC <a href="#wpsc_ticket_email_input" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_email_input" class="wpsc_help"></a></label>';
				                            $cc .= wpsc_address_book( 'wpsc_admin_new_thread_cc_select_' . $ticket->id, 'wpsc_admin_new_thread_email_select', $ticket->id );
										$cc .= '</div>';
										$cc .= '<div class="col-xs-12">';
											$cc .= '<input type="text" id="wpsc_admin_new_thread_cc_' . $ticket->id . '" value="" class="wpsc_new_thread form-control wpsc_new_thread_validate" placeholder="CC Email">';
										$cc .= '</div>';
										$output .= apply_filters( 'wpsc_admin_ticket_cc', $cc, $ticket->id );
									$output .= '</div>';
		                        $output .= '</div>';
		                        $output .= '<div class="col-xs-12 col-md-4">';
									$output .= '<div class="form-group">';
		                        		// ***************************************************************************************************
                    					// bcc
                    					$bcc = '';
		                        		$bcc .= '<div class="col-xs-12">';
				                            $bcc .= '<label for="wpsc_admin_new_thread_bcc_select_' . $ticket->id . '">BCC <a href="#wpsc_ticket_email_input" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_email_input" class="wpsc_help"></a></label>';
											$bcc .= wpsc_address_book( 'wpsc_admin_new_thread_bcc_select_' . $ticket->id, 'wpsc_admin_new_thread_email_select', $ticket->id );
										$bcc .= '</div>';
										$bcc .= '<div class="col-xs-12">';
											$bcc .= '<input type="text" id="wpsc_admin_new_thread_bcc_' . $ticket->id . '" value="" class="wpsc_new_thread form-control wpsc_new_thread_validate" placeholder="BCC Email">';
										$bcc .= '</div>';
										$output .= apply_filters( 'wpsc_admin_ticket_bcc', $bcc, $ticket->id );
									$output .= '</div>';
		                        $output .= '</div>';
		                    $output .= '</div>';
							// ***************************************************************************************************
                    		// before smallscreen buttons filter
							$output .= apply_filters( 'wpsc_admin_tickets_before_smallscreen_buttons' ,'' );
		                    $output .= '<div class="form-group smallscreen-buttons">';
								// ***************************************************************************************************
                    			// save button
								$output .= '<div class="col-xs-4">';
									$button_save = '';
									$output .= apply_filters( 'wpsc_admin_ticket_save_button', $button_save, $ticket->id );
								$output .= '</div>';
								// ***************************************************************************************************
                    			// note button
								$output .= '<div class="col-xs-4">';
									$button_note = '';
									$output .= apply_filters( 'wpsc_admin_ticket_note_button', $button_note, $ticket->id );
								$output .= '</div>';
								// ***************************************************************************************************
                    			// reply button
								$output .= '<div class="col-xs-4">';
									$button_reply = '';
									$output .= apply_filters( 'wpsc_admin_ticket_reply_button', $button_reply, $ticket->id );
								$output .= '</div>';
							$output .= '</div>';
							// ***************************************************************************************************
                    		// after smallscreen buttons filter
							$output .= apply_filters( 'wpsc_admin_tickets_after_largescreen_buttons', '' );
						$output .= '</div>';
					// ***************************************************************************************************
                    // hidden fields
					$output .= '</div>';
					$output .= '<input type="hidden" id="wpsc_admin_new_thread_client_' . $ticket->id . '" class="wpsc_new_thread" value="' . $ticket->client . '">';
                    $output .= '<input type="hidden" id="wpsc_admin_new_thread_client_id_' . $ticket->id . '" class="wpsc_new_thread" value="' . $ticket->client_id . '">';
                    $output .= '<input type="hidden" id="wpsc_admin_new_thread_client_email_' . $ticket->id . '" class="wpsc_new_thread" value="' . $ticket->client_email . '">';
                    $agent = get_userdata( $ticket->agent_id );
                    $output .= '<input type="hidden" id="wpsc_admin_new_thread_agent_name_' . $ticket->id . '" class="wpsc_new_thread" value="' . $agent->display_name . '">';
                    $output .= '<input type="hidden" id="wpsc_admin_new_thread_agent_id_' . $ticket->id . '" class="wpsc_new_thread" value="' . $agent->ID . '">';
                    $output .= '<input type="hidden" id="wpsc_admin_new_thread_agent_email_' . $ticket->id . '" class="wpsc_new_thread" value="' . $agent->user_email . '">';
                    $output .= '<input type="hidden" id="wpsc_admin_new_thread_agent_reply_to_' . $ticket->id . '" class="wpsc_new_thread" value="' . $wpsc_options['wpsc_email_reply_to'] . '">';
				$output .= '</form>';
			$output .= '</div>';
		$output .= '</div>';
		// ***************************************************************************************************
        // threads
        $output .= apply_filters( 'wpsc_admin_ticket_threads', '', $ticket, $wpsc_options );
    } else {
        $output .= 'false';
    }
$output .= '</div>';