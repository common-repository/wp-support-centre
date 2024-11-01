<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
function wpsc_tickets_shortcode( $atts ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $output = '';
    $output .= '<div class="container-fluid wpsc-bootstrap-styles">';
        $output .= '<h2 id="wpsc_wrap" data-id="' . get_current_user_id() . '">' . apply_filters( 'wpsc_front_title', get_bloginfo( 'name' ) . ' - ' . WPSC_TITLE ) . '</h2>';
        $output .= '<div id="wpsc_front_message" class="wpsc_hidden"></div>';
        $output .= '<ul id="wpsc_front_tabs" class="nav nav-tabs">';
            $active = ( !isset( $_POST['wpsc_front_ticket_search'] ) || ( isset( $_POST['wpsc_front_ticket_search'] ) && trim( $_POST['wpsc_front_ticket_search'] ) == '' ) ) ? ' active' : '';
            $tabs = '<li class="' . $active . '"><a data-toggle="tab" href="#wpsc_front_new_ticket">Create New ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '</a></li>';
            $output .= apply_filters( 'wpsc_front_tabs', $tabs, $atts );
        $output .= '</ul>';
        $output .= '<div class="tab-content">';
            $active = ( !isset( $_POST['wpsc_front_ticket_search'] ) || ( isset( $_POST['wpsc_front_ticket_search'] ) && trim( $_POST['wpsc_front_ticket_search'] ) == '' ) || $active_ticket == '' ) ? ' active in' : '';
            $output .= '<div id="wpsc_front_new_ticket" class="tab-pane fade' . $active . '">';
                $output .= '<div class="panel panel-default">';
                    $output .= '<div class="panel-heading"><h4 class="panel-title"><span class="wpsc_required">* = required</span></h4></div>';
                    $output .= '<div class="panel-body">';
                        $output .= '<form method="post" class="form-horizontal">';
                            $output .= '<div class="form-group">';
                                if ( is_user_logged_in() ) {
                                    $user = wp_get_current_user();
                                    $client_id = $user->ID;
                                    $client_name = $user->display_name;
                                    $client_email = $user->user_email;
                                    $output .= '<div class="col-xs-12 col-md-4">';
                                        $output .= '<label for="wpsc_front_new_ticket_client_name">Your Name <span class="wpsc_required">*</span></label>';
                                        $output .= '<input type="text" id="wpsc_front_new_ticket_client_name" class="wpsc_front_new_ticket wpsc_front_new_ticket_validate form-control" value="' . $client_name . '" readonly>';
                                    $output .= '</div>';
                                    $output .= '<div class="col-xs-12 col-md-4">';
                                        $output .= '<label for="wpsc_front_new_ticket_client_email">Your Email <span class="wpsc_required">*</span></label>';
                                        $output .= '<input type="text" id="wpsc_front_new_ticket_client_email" class="wpsc_front_new_ticket wpsc_front_new_ticket_validate form-control" value="' . $client_email . '" readonly>';
                                    $output .= '</div>';
                                    $output .= '<div class="col-xs-12 col-md-4">';
                                        $output .= '<label for="wpsc_front_new_ticket_client_email">Your Phone</label>';
                                        $output .= '<input type="text" id="wpsc_front_new_ticket_client_phone" class="wpsc_front_new_ticket form-control" value="">';
                                    $output .= '</div>';
                                } else {
                                    $client_id = 0;
                                    $client_name = '';
                                    $client_email = '';
                                    $output .= '<div class="col-xs-12 col-md-6">';
                                        $output .= '<label for="wpsc_front_new_ticket_client_first_name">Your First Name <span class="wpsc_required">*</span></label>';
                                        $output .= '<input type="text" id="wpsc_front_new_ticket_client_first_name" class="wpsc_front_new_ticket wpsc_front_new_ticket_validate form-control" value="">';
                                    $output .= '</div>';
                                    $output .= '<div class="col-xs-12 col-md-6">';
                                        $output .= '<label for="wpsc_front_new_ticket_client_last_name">Your Last Name <span class="wpsc_required">*</span></label>';
                                        $output .= '<input type="text" id="wpsc_front_new_ticket_client_last_name" class="wpsc_front_new_ticket wpsc_front_new_ticket_validate form-control" value="">';
                                    $output .= '</div>';
                                $output .= '</div>';
                                $output .= '<div class="form-group">';
                                    $output .= '<div class="col-xs-12 col-md-6">';
                                        $output .= '<label for="wpsc_front_new_ticket_client_email">Your Email <span class="wpsc_required">*</span></label>';
                                        $output .= '<input type="text" id="wpsc_front_new_ticket_client_email" class="wpsc_front_new_ticket wpsc_front_new_ticket_validate form-control" value="">';
                                    $output .= '</div>';
                                    $output .= '<div class="col-xs-12 col-md-6">';
                                        $output .= '<label for="wpsc_front_new_ticket_client_email">Your Phone</label>';
                                        $output .= '<input type="text" id="wpsc_front_new_ticket_client_phone" class="wpsc_front_new_ticket form-control" value="">';
                                    $output .= '</div>';
                                }
                            $output .= '</div>';
                            $output .= '<div class="form-group">';
                                $output .= '<div class="col-xs-12 col-md-6">';
                                    $output .= '<label for="wpsc_front_new_ticket_category">Category <span class="wpsc_required">*</span></label>';
                                    $output .= '<select id="wpsc_front_new_ticket_category" class="wpsc_front_new_ticket wpsc_front_new_ticket_validate form-control wpsc_category_select">';
                                        $output .= '<option value="">Please select...</option>';
    									$compare = ( isset( $_GET['wpsc_category'] ) && $_GET['wpsc_category'] != '' ) ? $_GET['wpsc_category'] : '';
                                        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_categories WHERE enabled=1 ORDER BY category ASC";
                                        $result = $wpdb->get_results( $sql );
                                        foreach ( $result as $category ) {
                                            $selected = ( $category->is_default == 1 && $compare == '' ) ? ' selected="selected"' : '';
    										$selected = ( $compare != '' && $compare == $category->id ) ? ' selected="selected"' : '';
                                            $output .= '<option value="' . $category->id . '"' . $selected . '>' . $category->category . '</option>';
                                        }
                                    $output .= '</select>';
                                $output .= '</div>';
                                $output .= '<div class="col-xs-12 col-md-6">';
                                    $output .= '<label for="wpsc_front_new_ticket_priority">Priority <span class="wpsc_required">*</span></label>';
                                    $output .= '<select id="wpsc_front_new_ticket_priority" class="wpsc_front_new_ticket wpsc_front_new_ticket_validate form-control">';
                                        $output .= '<option value="">Please select...</option>';
                                        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority WHERE enabled=1";
                                        $result = $wpdb->get_results( $sql );
                                        foreach ( $result as $priority ) {
                                            $selected = ( $priority->is_default == 1 ) ? ' selected="selected"' : '';
                                            $output .= '<option value="' . $priority->id . '"' . $selected . '>' . $priority->priority . '</option>';
                                        }
                                    $output .= '</select>';
                                $output .= '</div>';
                            $output .= '</div>';
    						$output .= apply_filters( 'wpsc_additional_fields', '', '', 'category' );
                            $output .= '<div class="form-group">';
                                $output .= '<div class="col-xs-12">';
                                    $output .= '<label for="wpsc_front_new_ticket_subject">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' Subject <span class="wpsc_required">*</span></label>';
                                    $output .= '<input type="text" id="wpsc_front_new_ticket_subject" class="wpsc_front_new_ticket wpsc_front_new_ticket_validate form-control" value="">';
                                $output .= '</div>';
                            $output .= '</div>';
                            $output .= '<div class="form-group">';
                                $output .= '<div class="col-xs-12">';
    								$label = '<label for="wpsc_front_new_ticket_details">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' Details <span class="wpsc_required">*</span></label>';
                                    $output .= apply_filters( 'wpsc_front_ticket_details', $label );
                                    $output .= '<textarea class="wpsc_ckeditor wpsc_front_ticket_note form-control" id="wpsc_front_new_ticket_details" name="wpsc_front_new_ticket_details"></textarea>';
                                $output .= '</div>';
                            $output .= '</div>';
    						if ( !isset( $wpsc_options['wpsc_file_upload'] ) || ( isset( $wpsc_options['wpsc_file_upload'] ) && $wpsc_options['wpsc_file_upload'] == '1' ) ) {
    	                        $output .= '<div class="form-group">';
    	                            $output .= '<div class="col-xs-4">';
    	                                $output .= '<label for="wpsc_front_new_ticket_attachments">Attachments</label>';
    	                                $output .= '<input type="file" id="wpsc_front_new_ticket_attachments"  class="wpsc_front_new_ticket_attachments" multiple="multiple" >';
    	                            $output .= '</div>';
    	                            $output .= '<div class="col-xs-8"></div>';
    	                        $output .= '</div>';
    	                    }
                            $output .= '<input type="hidden" id="wpsc_front_new_ticket_client_id" class="wpsc_front_new_ticket wpsc_front_new_ticket_validate" value="' . $client_id . '">';
    						$output .= apply_filters( 'wpsc_recaptcha', '' );
                            $output .= '<button type="button" class="wpsc_front_button wpsc_front_new_ticket_button btn btn-primary btn-sm" id="wpsc_front_new_ticket_save">Create ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '</button>';
                        $output .= '</form>';
                    $output .= '</div>';
                $output .= '</div>';
            $output .= '</div>';
            $output .= apply_filters( 'wpsc_front_content', '', $atts );
        $output .= '</div>';
    $output .= '</div>';
    return $output;
}

function wpsc_pro_do_front_tabs( $tabs, $atts ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $output = '';
    $active = ( !isset( $_POST['wpsc_front_ticket_search'] ) || ( isset( $_POST['wpsc_front_ticket_search'] ) && trim( $_POST['wpsc_front_ticket_search'] ) == '' ) ) ? ' active' : '';
    $output .= '<li class="' . $active . '"><a data-toggle="tab" href="#wpsc_front_new_ticket">Create New ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . '</a></li>';
    $active = ( isset( $_POST['wpsc_front_ticket_search'] ) && trim( $_POST['wpsc_front_ticket_search'] ) != '' ) ? ' active in' : '';
    $output .= '<li class="' . $active . '"><a data-toggle="tab" href="#wpsc_front_tickets">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's <i class="glyphicon glyphicon-refresh wpsc_tickets_refresh"></i></a></li>';
    if ( is_user_logged_in() ) {
        $open_tickets = ( isset( $_COOKIE['wpsc_open_tickets_' . get_current_user_id()] ) && $_COOKIE['wpsc_open_tickets_' . get_current_user_id()] != '' ) ? explode( ',', $_COOKIE['wpsc_open_tickets_' . get_current_user_id()] ) : array();
        if ( isset( $_GET['ticket_id'] ) && ( is_numeric( $_GET['ticket_id'] ) && !in_array( $_GET['ticket_id'], $open_tickets ) ) ) {
            $open_tickets[] = $_GET['ticket_id'];
        }
        foreach ( $open_tickets as $open_ticket_id ) {
            $output .= '<li class="wpsc_front_ticket_tab" data-id="' . $open_ticket_id . '"><a data-toggle="tab" href="#wpsc_front_ticket_' . $open_ticket_id . '">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' #' . $open_ticket_id . ' <i class="glyphicon glyphicon-remove-sign wpsc_front_tab_close"></i></a></li>';
        }
        $output .= '<li class="wpsc_front_account_page_tab" data-id="' . get_current_user_id() . '"><a data-toggle="tab" href="#wpsc_front_account_page_tab">Your Account</a></li>';
    }
    return $output;
}
add_filter( 'wpsc_front_tabs', 'wpsc_pro_do_front_tabs', 20, 2 );

function wpsc_pro_do_front_content( $content, $atts ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    include_once( WPSC_PLUGIN_DIR . '/includes/get_front_ticket.php' );
    $output = '';
    $active = ( isset( $_POST['wpsc_front_ticket_search'] ) && trim( $_POST['wpsc_front_ticket_search'] ) != '' ) ? ' active in' : '';
    $output .= '<div id="wpsc_front_tickets" class="tab-pane fade' . $active . '">';
        if ( is_user_logged_in() ) {
            //$filter = ' WHERE t.client_id=' . get_current_user_id();
            $filter = " WHERE (t.client_id=" . get_current_user_id() . " OR t.shared_users LIKE '%" . get_current_user_id() . "%')";
            if ( isset( $_POST['wpsc_front_ticket_search'] ) && trim( $_POST['wpsc_front_ticket_search'] ) != '' ) {
                $where = ' AND ( ';
                $output .= '<button type="button" class="wpsc_front_button btn" id="wpsc_front_clear_search">Clear Search</button>';
                $output .= '<hr />';
                $sql = "
                    SELECT DISTINCT
                        t.id,t.subject,t.updated_timestamp,t.client_id,t.client,t.agent_id,t.shared_users,
                        s.status,s.colour AS status_colour,
                        c.category,
                        p.priority,p.colour AS priority_colour,
                        ua.display_name AS agent
                    FROM " . $wpdb->prefix . "wpsc_threads h
                    LEFT JOIN " . $wpdb->prefix . "wpsc_tickets t ON t.id=h.ticket_id
                    LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
                    LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
                    LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
                    LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id " . $filter;
                $type = $_POST['wpsc_front_ticket_search_type'];
                switch( $type ) {
                    case 'any':
                        $terms = explode( ' ', trim( $_POST['wpsc_front_ticket_search'] ) );
                        $i = 1;
                        foreach ( $terms as $term ) {
                            if ( $i == 0 ) {
                                $where .= ' OR ';
                            }
                            $where .= " FROM_BASE64( h.message ) LIKE '%" . $term . "%' ";
                            $i = 0;
                        }
                        break;
                    case 'all':
                        $terms = explode( ' ', trim( $_POST['wpsc_front_ticket_search'] ) );
                        $i = 1;
                        foreach ( $terms as $term ) {
                            if ( $i == 0 ) {
                                $where .= ' AND ';
                            }
                            $where .= " FROM_BASE64( h.message ) LIKE '%" . $term . "%' ";
                            $i = 0;
                        }
                        break;
                    case 'exact':
                        $where .= " FROM_BASE64( h.message ) LIKE '%" . trim( $_POST['wpsc_front_ticket_search'] ) . "%' ";
                        break;
                }
                $where .= ')';
                $sql .= $where;
            } else {
                $sql = "
                    SELECT
                        t.id,t.subject,t.updated_timestamp,t.client_id,t.client,t.agent_id,t.shared_users,
                        s.status,s.colour AS status_colour,
                        c.category,
                        p.priority,p.colour AS priority_colour,
                        ua.display_name AS agent
                    FROM " . $wpdb->prefix . "wpsc_tickets t
                    LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
                    LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
                    LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
                    LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id" . $filter;
            }
            $tickets = $wpdb->get_results( $sql, OBJECT );
            if ( $wpdb->num_rows > 0 ) {
                $output .= '<table class="wpsc_front_datatable display responsive table table-striped table-bordered wpsc_fullwidth" id="wpsc_front_tickets_table">';
                    $output .= '<thead>';
                        $output .= '<tr>';
                        	$output .= '<th class="min-tablet-l"></th>';
                            $output .= '<th>ID</th>';
                            $output .= '<th>Status</th>';
                            $output .= '<th>Subject</th>';
                            $output .= '<th>Category</th>';
                            $output .= '<th>Agent</th>';
                            $output .= '<th>Priority</th>';
                            $output .= '<th>Updated</th>';
                        $output .= '</tr>';
                    $output .= '</thead>';
                    $output .= '<tfoot>';
                        $output .= '<tr>';
							$output .= '<th class="min-tablet-l"></th>';
                            $output .= '<th>ID</th>';
                            $output .= '<th>Status</th>';
                            $output .= '<th>Subject</th>';
                            $output .= '<th>Category</th>';
                            $output .= '<th>Agent</th>';
                            $output .= '<th>Priority</th>';
                            $output .= '<th>Updated</th>';
                        $output .= '</tr>';
                    $output .= '</tfoot>';
                    $output .= '<tbody>';
                        foreach( $tickets as $ticket ) {
                            $shared_users = explode( ',', $ticket->shared_users);
                            if ( ( $ticket->client_id == get_current_user_id() ) || ( in_array( get_current_user_id(), $shared_users ) ) ) {
                                $status_background = $ticket->status_colour;
                                $status_text = ( wpSupportCentre::wpsc_lightness( $background ) === true ) ? '#000000' : '#ffffff';
                                $priority_background = $ticket->priority_colour;
                                $priority_text = ( wpSupportCentre::wpsc_lightness( $background ) === true ) ? '#000000' : '#ffffff';
                                $output .= '<tr class="wpsc_front_ticket_row" id="' . $ticket->id . '">';
								$output .= '<td class="wpsc_select_ticket_td"></td>';
                                    $output .= '<td class="align_centre">' . $ticket->id . '</td>';
                                    $output .= '<td class="align_centre" style="background-color:' . $status_background . ';color:' . $status_text . '">' . $ticket->status . '</td>';
                                    $output .= ( strlen( $ticket->subject ) > 25 ) ? '<td class="align_centre">' . substr( $ticket->subject, 0 ,25 ) . '...</td>' : '<td class="align_centre">' . $ticket->subject . '</td>';
                                    $output .= '<td class="align_centre">' . $ticket->category . '</td>';
                                    $output .= '<td class="align_centre">' . $ticket->agent . '</td>';
                                    $output .= '<td class="align_centre" style="background-color:' . $priority_background . ';color:' . $priority_text . '">' . $ticket->priority . '</td>';
                                    $output .= '<td class="align_centre">' . $ticket->updated_timestamp . '</td>';
                                $output .= '</tr>';
                            }
                        }
                    $output .= '</tbody>';
                $output .= '</table>';
                $output .= '<div style="clear:both;"></div>';
                $output .= '<div id="wpsc_front_ticket_filters" class="panel-group">';
                    $output .= '<div class="panel panel-default">';
                        $output .= '<div class="panel-heading" data-toggle="collapse" data-target="#wpsc_front_search_accordion" data-parent="#wpsc_front_ticket_filters">';
                            $output .= '<h4 class="panel-title">Search</h4>';
                        $output .= '</div>';
                        $output .= '<div id="wpsc_front_search_accordion" class="panel-collapse collapse in">';
                            $output .= '<div class="panel-body">';
                                $output .= '<form method="post" class="form-horizontal">';
                                    $output .= '<div class="form-group">';
                                        $output .= '<div class="col-xs-4">';
                                            $output .= '<input type="text" id="wpsc_front_ticket_search" name="wpsc_front_ticket_search" value="' . trim( $_POST['wpsc_front_ticket_search'] ) . '" required class="form-control">';
                                        $output .= '</div>';
                                        $output .= '<div class="col-xs-8">';
                                            $output .= '<label for="wpsc_front_ticket_search_type_any" class="radio-inline"><input type="radio" id="wpsc_front_ticket_search_type_any" name="wpsc_front_ticket_search_type" value="any" checked="checked"> Any Word </label>';
                                            $output .= '<label for="wpsc_front_ticket_search_type_all" class="radio-inline"><input type="radio" id="wpsc_front_ticket_search_type_all" name="wpsc_front_ticket_search_type" value="all"> All Words </label>';
                                            $output .= '<label for="wpsc_front_ticket_search_type_exact" class="radio-inline"><input type="radio" id="wpsc_front_ticket_search_type_exact" name="wpsc_front_ticket_search_type" value="exact"> Exact Phrase </label>';
                                        $output .= '</div>';
                                    $output .= '</div>';
                                    $output .= '<button type="submit" class="wpsc_front_button btn" id="wpsc_front_search">Search</button>';
                                $output .= '</form>';
                            $output .= '</div>';
                        $output .= '</div>';
                    $output .= '</div>';
                $output .= '</div>';
            } else {
                $output .= '<div class="panel panel-default">';
                    $output .= '<div class="panel-heading"><h4 class="panel-title">' . get_bloginfo( 'name' ) . ' - ' . WPSC_TITLE . '</h4></div>';
                    $output .= '<div class="panel-body">';
                        $output .= '<p>No ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's found.</p>';
                    $output .= '</div>';
                $output .= '</div>';
            }
        } else {
            $output .= '<div class="panel panel-default">';
                $output .= '<div class="panel-heading"><h4 class="panel-title">' . get_bloginfo( 'name' ) . ' - ' . WPSC_TITLE . '</h4></div>';
                $output .= '<div class="panel-body">';
                    $output .= '<p>We\'re ready to help!</p>';
                    $output .= '<p>You can create a ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' at any time, or to view your ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' history, please <a href="' . wp_login_url( get_permalink() ) . '">log in.</a></p>';
                    $output .= '<p>If you don\'t already have an account with us, you can <a href="' . wp_registration_url() . '">register here</a>';
                $output .= '</div>';
            $output .= '</div>';
        }
    $output .= '</div>';
    if ( is_user_logged_in() ) {
        $open_tickets = ( isset( $_COOKIE['wpsc_open_tickets_' . get_current_user_id()] ) && $_COOKIE['wpsc_open_tickets_' . get_current_user_id()] != '' ) ? explode( ',', $_COOKIE['wpsc_open_tickets_' . get_current_user_id()] ) : array();
        if ( isset( $_GET['ticket_id'] ) && ( is_numeric( $_GET['ticket_id'] ) && !in_array( $_GET['ticket_id'], $open_tickets ) ) ) {
            $open_tickets[] = $_GET['ticket_id'];
        }
        foreach ( $open_tickets as $open_ticket_id ) {
            $active = ( $active_ticket == $open_ticket_id && ( !isset( $_POST['wpsc_front_ticket_search'] ) || ( isset( $_POST['wpsc_front_ticket_search'] ) && trim( $_POST['wpsc_front_ticket_search'] ) == '' ) ) ) ? ' active in' : '';
            $output .= '<div id="wpsc_front_ticket_' . $open_ticket_id . '" class="tab-pane fade' . $active . '">';
                $output .= wpsc_get_the_front_ticket( $open_ticket_id );
            $output .= '</div>';
        }
        $output .= '<div id="wpsc_front_account_page_tab" class="tab-pane fade">';
            $user_id = get_current_user_id();
            $user = wp_get_current_user();
            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_account WHERE user_id=" . $user_id;
            $account = $wpdb->get_row( $sql );
            if ( null !== $account ) {
                $output .= '<div class="panel panel-default">';
                    $output .= '<div class="panel-heading"><h4 class="panel-title">' . $user->display_name . '</h4></div>';
                    $output .= '<div class="panel-body">';
                        $output .= $account->content;
                    $output .= '</div>';
                $output .= '</div>';
            } else {
                $output .= '<div class="panel panel-default">';
                    $output .= '<div class="panel-heading"><h4 class="panel-title">' . $user->display_name . '</h4></div>';
                    $output .= '<div class="panel-body">';
                        $output .= '<p>There is no information available to display for ' . $user->display_name . ' at this time.</p>';
                    $output .= '</div>';
                $output .= '</div>';
            }
        $output .= '</div>';
    }
    return $output;
}
add_filter( 'wpsc_front_content', 'wpsc_pro_do_front_content', 20, 2 );