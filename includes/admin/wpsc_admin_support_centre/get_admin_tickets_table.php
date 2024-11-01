<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function wpsc_admin_get_tickets_table( $data, $wpsc_options ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    $filter = '';
    $where = '';
    $filter = ' WHERE (1=1';
    if ( isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] == 'true' ) {
        if ( isset( $_REQUEST['client_id'] ) && $_REQUEST['client_id'] != '' ) {
            $filter .= ' AND t.client_id=' . $_REQUEST['client_id'];
        }
    } else {
        if ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_status'] ) && $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_status'] != '' ) {
            if ( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_status'] == 'active' ) {
                $filter .= ' AND (t.status_id!=2 AND t.status_id!=3)';
            } else if ( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_status'] == 'all' ) {
                $filter .= '';
            } else {
                $filter .= ' AND t.status_id=' . $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_status'];
            }
        }
        if ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_client_id'] ) && $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_client_id'] != '' ) {
            $filter .= ' AND t.client_id=' . $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_client_id'];
        }
        if ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_category'] ) && $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_category'] != '' ) {
            $filter .= ' AND t.category_id=' . $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_category'];
        }
        if ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_agent'] ) && $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_agent'] != '' ) {
            $filter .= ' AND t.agent_id=' . $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_agent'];
        }
        if ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_priority'] ) && $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_priority'] != '' ) {
            $filter .= ' AND t.priority_id=' . $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_priority'];
        }
        if ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_date_to'] ) && $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_date_to'] != '' && $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_date_from'] != '' ) {
            $filter .= " AND (created_timestamp BETWEEN '" . date( 'Y-m-d H:i:s', strtotime( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_date_from'] ) ) . "' AND '" . date( 'Y-m-d H:i:s', strtotime( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_date_to'] ) ) . "')";
        }
    }
    $filter .= ')';
    $output = '';
    if ( isset( $_POST['wpsc_admin_ticket_search'] ) && trim( $_POST['wpsc_admin_ticket_search'] != '' ) ) {
    	$output .= '<button type="button" class="wpsc_admin_button btn btn-primary btn-sm" name="wpsc_admin_clear_search" id="wpsc_admin_clear_search">Clear Search</button>';
    	$mysqlver = $wpdb->dbh->server_info;
		$type = $_POST['wpsc_admin_ticket_search_type'];
		$item = $_POST['wpsc_admin_ticket_search_item'];
		$where = ' WHERE t.status_id!=3 AND ( ';
		if ( $mysqlver < '5.6.1' ) {
			do_action( 'wpsc_search_fallback' );
			$wpsc_search_table = 'wpsc_threads_raw';
			switch( $type ) {
	            case 'any':
	                $terms = explode( ' ', $_POST['wpsc_admin_ticket_search'] );
	                $i = 1;
	                foreach ( $terms as $term ) {
	                    if ( $i == 0 ) {
	                        $where .= ' OR ';
	                    }
						if ( $item == 'body' ) {
	                    	$where .= " h.message LIKE '%" . $term . "%' ";
						} else if ( $item == 'subject' ) {
							$where .= " t.subject LIKE '%" . $term . "%' ";
						} else {
							$where .= " ( h.message LIKE '%" . $term . "%' OR t.subject LIKE '%" . $term . "%' ) ";
						}
	                    $i = 0;
	                }
	                break;
	            case 'all':
	                $terms = explode( ' ', $_POST['wpsc_admin_ticket_search'] );
	                $i = 1;
	                foreach ( $terms as $term ) {
	                    if ( $i == 0 ) {
	                        $where .= ' AND ';
	                    }
						if ( $item == 'body' ) {
	                    	$where .= " h.message LIKE '%" . $term . "%' ";
						} else if ( $item == 'subject' ) {
							$where .= " t.subject LIKE '%" . $term . "%' ";
						} else {
							$where .= " ( h.message LIKE '%" . $term . "%' OR t.subject LIKE '%" . $term . "%' ) ";
						}
	                    $i = 0;
	                }
	                break;
	            case 'exact':
					if ( $item == 'body' ) {
	                	$where .= " h.message LIKE '%" . $_POST['wpsc_admin_ticket_search'] . "%' ";
					} else if ( $item == 'subject' ) {
						$where .= " t.subject LIKE '%" . $_POST['wpsc_admin_ticket_search'] . "%' ";
					} else {
						$where .= " ( h.message LIKE '%" . $_POST['wpsc_admin_ticket_search'] . "%' OR t.subject LIKE '%" . $_POST['wpsc_admin_ticket_search'] . "%' ) ";
					}
	                break;
	        }
		} else {
			$wpsc_search_table = 'wpsc_threads';
			switch( $type ) {
	            case 'any':
	                $terms = explode( ' ', $_POST['wpsc_admin_ticket_search'] );
	                $i = 1;
	                foreach ( $terms as $term ) {
	                    if ( $i == 0 ) {
	                        $where .= ' OR ';
	                    }
						if ( $item == 'body' ) {
	                    	$where .= " FROM_BASE64( h.message ) LIKE '%" . $term . "%' ";
						} else if ( $item == 'subject' ) {
							$where .= " t.subject LIKE '%" . $term . "%' ";
						} else {
							$where .= " ( FROM_BASE64( h.message ) LIKE '%" . $term . "%' OR t.subject LIKE '%" . $term . "%' ) ";
						}
	                    $i = 0;
	                }
	                break;
	            case 'all':
	                $terms = explode( ' ', $_POST['wpsc_admin_ticket_search'] );
	                $i = 1;
	                foreach ( $terms as $term ) {
	                    if ( $i == 0 ) {
	                        $where .= ' AND ';
	                    }
						if ( $item == 'body' ) {
	                    	$where .= " FROM_BASE64( h.message ) LIKE '%" . $term . "%' ";
						} else if ( $item == 'subject' ) {
							$where .= " t.subject LIKE '%" . $term . "%' ";
						} else {
							$where .= " ( FROM_BASE64( h.message ) LIKE '%" . $term . "%' OR t.subject LIKE '%" . $term . "%' ) ";
						}
	                    $i = 0;
	                }
	                break;
	            case 'exact':
					if ( $item == 'body' ) {
	                	$where .= " FROM_BASE64( h.message ) LIKE '%" . $_POST['wpsc_admin_ticket_search'] . "%' ";
					} else if ( $item == 'subject' ) {
						$where .= " t.subject LIKE '%" . $_POST['wpsc_admin_ticket_search'] . "%' ";
					} else {
						$where .= " ( FROM_BASE64( h.message ) LIKE '%" . $_POST['wpsc_admin_ticket_search'] . "%' OR t.subject LIKE '%" . $_POST['wpsc_admin_ticket_search'] . "%' ) ";
					}
	                break;
	        }
		}
        $where .= ' )';
		$sql = "
            SELECT DISTINCT
                t.id,t.subject,t.created_timestamp,t.updated_timestamp,t.client_id,t.client,t.agent_id,t.updated_by,
                s.status,s.colour AS status_colour,
                c.category,
                p.priority,p.priority_sla,p.colour AS priority_colour,
                ua.display_name AS agent
            FROM " . $wpdb->prefix . $wpsc_search_table . " h
            LEFT JOIN " . $wpdb->prefix . "wpsc_tickets t ON t.id=h.ticket_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
            LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id " . $where . ' ORDER BY t.updated_timestamp DESC';
    } else {
        $sql = "
            SELECT
                t.id,t.subject,t.created_timestamp,t.updated_timestamp,t.client_id,t.client,t.agent_id,t.updated_by,
                s.status,s.colour AS status_colour,
                c.category,
                p.priority,p.priority_sla,p.colour AS priority_colour,
                ua.display_name AS agent
            FROM " . $wpdb->prefix . "wpsc_tickets t
            LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
            LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id" . $filter . ' ORDER BY t.updated_timestamp DESC';
    }
    $tickets = $wpdb->get_results( $sql, OBJECT );
    if ( $wpdb->num_rows > 0 ) {
        $output .= '<table id="wpsc_admin_tickets_table" class="wpsc_admin_datatable display responsive table table-striped table-bordered wpsc_fullwidth" style="max-width:100%;">';
            $output .= '<thead>';
                $output .= '<tr>';
                	$output .= '<th class="min-tablet-l"></th>';
                    $output .= '<th class="wpsc_align_center min-tablet-l"><input type="checkbox" id="wpsc_select_all" /></th>';
                    $output .= '<th class="all">ID</th>';
                    $output .= '<th class="all">Status</th>';
                    $output .= '<th class="all">Subject</th>';
                    $output .= '<th class="all">Client</th>';
                    $output .= '<th class="min-tablet-l">Category</th>';
                    $output .= '<th class="min-tablet-l">Agent</th>';
                    $output .= '<th class="min-tablet-l">Priority</th>';
                    $output .= '<th class="min-tablet-l">Created</th>';
                    $output .= '<th class="min-tablet-l">Updated</th>';
                $output .= '</tr>';
            $output .= '</thead>';
            $output .= '<tfoot>';
                $output .= '<tr>';
                    $output .= '<th></th>';
					$output .= '<th></th>';
                    $output .= '<th>ID</th>';
                    $output .= '<th>Status</th>';
                    $output .= '<th>Subject</th>';
                    $output .= '<th>Client</th>';
                    $output .= '<th>Category</th>';
                    $output .= '<th>Agent</th>';
                    $output .= '<th>Priority</th>';
                    $output .= '<th>Created</th>';
                    $output .= '<th>Updated</th>';
                $output .= '</tr>';
            $output .= '</tfoot>';
            $output .= '<tbody>';
                foreach( $tickets as $ticket ) {
                    $status_background = $ticket->status_colour;
                    $status_text = ( wpSupportCentre::wpsc_lightness( $status_background ) === true ) ? '#000000' : '#ffffff';
                    $priority_background = $ticket->priority_colour;
                    $priority_text = ( wpSupportCentre::wpsc_lightness( $priority_background ) === true ) ? '#000000' : '#ffffff';
                    $updated = get_date_from_gmt( $ticket->updated_timestamp );
                    $updated = strtotime( $updated );
                    $updated = date( 'Y-m-d', $updated );
                    $updated = strtotime( $updated );
                    $now = strtotime( date( 'Y-m-d', current_time( 'timestamp' ) ) );
                    $diff = $now - $updated;
                    $days = floor( $diff / (60 * 60 * 24 ) );
                    $updated_style = '';
                    if ( $days > 6 ) {
                        $updated_background = '#FF0000';
                        $updated_text = ( wpSupportCentre::wpsc_lightness( $updated_background ) === true ) ? '#000000' : '#ffffff';
                        $updated_style = ' style="background-color:' . $updated_background . ';color:' . $updated_text . '"';
                    }
                    //var_dump( $diff );
                    $output .= '<tr class="wpsc_admin_ticket_row" data-id="' . $ticket->id . '">';
                        $output .= '<td class="wpsc_select_ticket_td"></td>';
                        $output .= '<td class="align_centre wpsc_select_ticket_td"><input type="checkbox" class="wpsc_select_ticket" value="' . $ticket->id . '" /></td>';
                        $output .= '<td class="align_centre">' . $ticket->id . '</td>';
                        $output .= '<td class="align_centre" style="background-color:' . $status_background . ';color:' . $status_text . '">' . $ticket->status . '</td>';
                        $output .= ( strlen( $ticket->subject ) > 25 ) ? '<td class="align_centre"><span title="' . htmlentities( stripcslashes( $ticket->subject ) ) . '">' . substr( htmlentities( stripcslashes( $ticket->subject ) ), 0 ,25 ) . '...</span></td>' : '<td class="align_centre">' . htmlentities( stripcslashes( $ticket->subject ) ) . '</td>';
                        $output .= '<td class="align_centre">' . $ticket->client . '</td>';
                        $output .= '<td class="align_centre">' . $ticket->category . '</td>';
                        $output .= '<td class="align_centre">' . $ticket->agent . '</td>';
                        $output .= '<td class="align_centre" style="background-color:' . $priority_background . ';color:' . $priority_text . '">' . $ticket->priority . '</td>';
                        $output .= '<td class="align_centre">' . get_date_from_gmt( $ticket->created_timestamp ) . '</td>';
                        $output .= '<td class="align_centre"' . $updated_style . ' id="wpsc_ticket_updated_' . $ticket->id . '"><span title="' . $days . ' days">' . get_date_from_gmt( $ticket->updated_timestamp ) . '</span></td>';
                    $output .= '</tr>';
                }
            $output .= '</tbody>';
        $output .= '</table>';
        $output .= '<div id="wpsc_ticket_actions" class="wpsc_hidden">';
            $output .= '<div class="panel panel-default">';
                $output .= '<div class="panel-heading"><h4 class="panel-title">With selected...</h4></div>';
                $output .= '<div class="panel-body panel-body-wheat">';
                    $output .= '<form method="post" class="form-horizontal">';
                        $output .= '<div class="form-group">';
                            $output .= '<div class="col-xs-12">';
                                $output .= '<button type="button" class="wpsc_admin_button btn btn-primary btn-sm" name="wpsc_admin_open_selected" id="wpsc_admin_open_selected">Open ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's</button>';
                            $output .= '</div>';
                        $output .= '</div>';
                        $output .= '<div class="form-group">';
                            $output .= '<div class="col-xs-12 col-md-3">';
                                $output .= '<label for="wpsc_ticket_action_status">Status</label>';
                                $output .= '<select id="wpsc_ticket_action_status" name="wpsc_ticket_action_status" class="wpsc_ticket_action form-control">';
                                    $output .= '<option value="">Please select...</option>';
                                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_status ORDER BY status ASC";
                                    $results = $wpdb->get_results( $sql, OBJECT );
                                    if ( $wpdb->num_rows > 0 ) {
                                        foreach ($results as $status) {
                                            $output .= '<option value="' . $status->id . '">' . $status->status . '</option>';
                                        }
                                    }
                                $output .= '</select>';
                            $output .= '</div>';
                            $output .= '<div class="col-xs-12 col-md-3">';
                                $output .= '<label for="wpsc_ticket_action_category">Category</label>';
                                $output .= '<select id="wpsc_ticket_action_category" name="wpsc_ticket_action_category" class="wpsc_ticket_action form-control">';
                                    $output .= '<option value="">Please select...</option>';
                                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_categories ORDER BY category ASC";
                                    $results = $wpdb->get_results( $sql, OBJECT );
                                    if ( $wpdb->num_rows > 0 ) {
                                        foreach ($results as $category) {
                                            $output .= '<option value="' . $category->id . '">' . $category->category . '</option>';
                                        }
                                    }
                                $output .= '</select>';
                            $output .= '</div>';
                            $output .= '<div class="col-xs-12 col-md-3">';
                                $output .= '<label for="wpsc_ticket_action_agent">Agent</label>';
                                $output .= '<select id="wpsc_ticket_action_agent" name="wpsc_ticket_action_agent" class="wpsc_ticket_action form-control">';
                                    $output .= '<option value="">Please select...</option>';
                                    $args = array(
                                        'orderby' => 'display_name',
                                        'order' => 'ASC'
                                    );
                                    $all_users = get_users( $args );
                                    foreach ( $all_users as $user ) {
                                        if ( $user->has_cap( 'manage_wpsc_ticket' ) ) {
                                            $output .= '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
                                        }
                                    }
                                $output .= '</select>';
                            $output .= '</div>';
                            $output .= '<div class="col-xs-12 col-md-3">';
                                $output .= '<label for="wpsc_ticket_action_priority">Priority</label>';
                                $output .= '<select id="wpsc_ticket_action_priority" name="wpsc_ticket_action_priority" class="wpsc_ticket_action form-control">';
                                    $output .= '<option value="">Please select...</option>';
                                    $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority ORDER BY priority ASC";
                                    $results = $wpdb->get_results( $sql, OBJECT );
                                    if ( $wpdb->num_rows > 0 ) {
                                        foreach ($results as $priority) {
                                            $output .= '<option value="' . $priority->id . '">' . $priority->priority . '</option>';
                                        }
                                    }
                                $output .= '</select>';
                            $output .= '</div>';
                        $output .= '</div>';
                        $output .= '<div class="form-group">';
                            $output .= '<div class="col-xs-12">';
                                $output .= '<button type="button" class="wpsc_admin_button btn btn-primary btn-sm" name="wpsc_admin_apply_actions" id="wpsc_admin_apply_actions">Apply Changes</button> <a href="#wpsc_admin_apply_actions_dialog" data-toggle="modal"><img src="' . WPSC_PLUGIN_URL . 'assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_admin_apply_actions_dialog" class="wpsc_help"></a>';
                            $output .= '</div>';
                        $output .= '</div>';
                    $output .= '</form>';
                $output .= '</div>';
            $output .= '</div>';
        $output .= '</div>';
    } else {
        $output .= '<p>No ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's found.</p>';
    }
    return $output;
}
add_filter( 'do_wpsc_admin_get_tickets_table', 'wpsc_admin_get_tickets_table', 10, 2 );