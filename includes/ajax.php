<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
final class wpscAjax {
    /**
     * the plugin instance
     */
    private static $instance = NULL;
    /**
     * get the plugin instance
     *
     * @return wpscAjax
     */
    public static function get_instance() {
        if ( NULL === self::$instance )
            self::$instance = new self;
        return self::$instance;
    }
    /**
     * Save admin general settings
     *
     *
     */
    function save_wpsc_general() {
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $return = array();
        $wpsc_item = trim( $_POST['wpsc_item'] );
        $wpsc_client = trim( $_POST['wpsc_client'] );
        $wpsc_support_page = trim( $_POST['wpsc_support_page'] );
        $wpsc_thanks_page = trim( $_POST['wpsc_thanks_page'] );
        $wpsc_recurring_tickets_scheduled_time = trim( $_POST['wpsc_recurring_tickets_scheduled_time'] );
        $wpsc_options['wpsc_item_history'] = ( isset( $wpsc_options['wpsc_item_history'] ) && is_array( $wpsc_options['wpsc_item_history'] ) ) ? $wpsc_options['wpsc_item_history'] : array();
        if ( !in_array( $wpsc_options['wpsc_item'] ) ) {
            $wpsc_options['wpsc_item_history'][] = $wpsc_options['wpsc_item'];
        }
        $wpsc_options['wpsc_item'] = $wpsc_item;
        $wpsc_options['wpsc_client'] = $wpsc_client;
        $wpsc_options['wpsc_support_page'] = $wpsc_support_page;
        $wpsc_options['wpsc_thanks_page'] = $wpsc_thanks_page;
        $wpsc_options['wpsc_recurring_tickets_scheduled_time'] = $wpsc_recurring_tickets_scheduled_time;
        update_option( 'wpsc_options', $wpsc_options );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save admin email settings
     *
     *
     */
    function save_wpsc_email() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $return = array();
        $wpsc_email_from_name = trim( $_POST['wpsc_email_from_name'] );
        $wpsc_email_from_email = trim( $_POST['wpsc_email_from_email'] );
        $wpsc_email_reply_to = trim( $_POST['wpsc_email_reply_to'] );
        $wpsc_use_agent_email = trim( $_POST['wpsc_use_agent_email'] );
        $wpsc_enable_email_piping = trim( $_POST['wpsc_enable_email_piping'] );
        $wpsc_enable_email_piping_catch_all = trim( $_POST['wpsc_enable_email_piping_catch_all'] );
        $wpsc_email_piping = trim( $_POST['wpsc_email_piping'] );
        $wpsc_options['wpsc_email_from_name'] = $wpsc_email_from_name;
        $wpsc_options['wpsc_email_from_email'] = $wpsc_email_from_email;
        $wpsc_options['wpsc_email_reply_to'] = $wpsc_email_reply_to;
        $wpsc_options['wpsc_use_agent_email'] = $wpsc_use_agent_email;
        $wpsc_options['wpsc_enable_email_piping'] = $wpsc_enable_email_piping;
        $wpsc_options['wpsc_enable_email_piping_catch_all'] = $wpsc_enable_email_piping_catch_all;
        $wpsc_options['wpsc_email_piping'] = $wpsc_email_piping;
        update_option( 'wpsc_options', $wpsc_options );
        $wpsc_admin_signature = trim( $_POST['wpsc_admin_signature'] );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_settings SET signature='" . $wpsc_admin_signature . "' WHERE ID=1";
        $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save custom status colour
     *
     *
     */
    function wpsc_save_status_colour() {
        global $wpdb;
        $return = array();
        $wpsc_id = trim( $_POST['wpsc_id'] );
		$wpsc_status = trim( $_POST['wpsc_status'] );
		$wpsc_prefix = trim( $_POST['wpsc_prefix'] );
        $wpsc_colour = trim( $_POST['wpsc_colour'] );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_status SET status='" . esc_sql( $wpsc_status ) . "', status_prefix='" . esc_sql( $wpsc_prefix ) . "', colour='" . esc_sql( $wpsc_colour ) . "' WHERE id=" . esc_sql( $wpsc_id );
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Add New Custom Status
     *
     *
     */
    function wpsc_add_new_status() {
        global $wpdb;
        $return = array();
        $wpsc_new_status = trim( $_POST['wpsc_new_status'] );
		$wpsc_new_status_subject_prefix = trim( $_POST['wpsc_new_status_subject_prefix'] );
        $wpsc_new_status_colour = trim( $_POST['wpsc_new_status_colour'] );
        $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_status (status, status_prefix, colour, custom, enabled) VALUES ('" . esc_sql( $wpsc_new_status ) . "', '" . esc_sql( $wpsc_new_status_subject_prefix ) . "', '" . esc_sql( $wpsc_new_status_colour ) . "', 1, 1)";
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Delete Custom Status
     *
     *
     */
    function wpsc_delete_status() {
        global $wpdb;
        $return = array();
        $wpsc_id = trim( $_POST['wpsc_id'] );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_status SET enabled=0 WHERE id=" . $wpsc_id;
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Set Default Status
     *
     *
     */
    function wpsc_status_default() {
        global $wpdb;
        $return = array();
        $wpsc_id = trim( $_POST['wpsc_id'] );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_status SET is_default=0";
        $result = $wpdb->query( $sql );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_status SET is_default=1 WHERE id=" . $wpsc_id;
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Add New Category
     *
     *
     */
    function wpsc_add_new_category() {
        global $wpdb;
        $return = array();
        $wpsc_new_category = trim( $_POST['wpsc_new_category'] );
        $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_categories (category, custom, enabled) VALUES ('" . esc_sql( $wpsc_new_category ) . "', 1, 1)";
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Delete Category
     *
     *
     */
    function wpsc_delete_category() {
        global $wpdb;
        $return = array();
        $wpsc_id = trim( $_POST['wpsc_id'] );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_categories SET enabled=0 WHERE id=" . $wpsc_id;
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Set Default Category
     *
     *
     */
    function wpsc_category_default() {
        global $wpdb;
        $return = array();
        $wpsc_id = trim( $_POST['wpsc_id'] );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_categories SET is_default=0";
        $result = $wpdb->query( $sql );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_categories SET is_default=1 WHERE id=" . $wpsc_id;
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save custom priority colour
     *
     *
     */
    function wpsc_save_priority_colour() {
        global $wpdb;
        $return = array();
        $wpsc_id = trim( $_POST['wpsc_id'] );
        $wpsc_priority = trim( $_POST['wpsc_priority'] );
        $wpsc_sla = trim( $_POST['wpsc_sla'] );
		$wpsc_colour = trim( $_POST['wpsc_colour'] );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_priority SET priority='" . esc_sql( $wpsc_priority ) . "', priority_sla='" . esc_sql( $wpsc_sla ) . "', colour='" . esc_sql( $wpsc_colour ) . "' WHERE id=" . esc_sql( $wpsc_id );
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Add New Priority
     *
     *
     */
    function wpsc_add_new_priority() {
        global $wpdb;
        $return = array();
        $wpsc_new_priority = trim( $_POST['wpsc_new_priority'] );
		$wpsc_new_priority_sla = trim( $_POST['wpsc_new_priority_sla'] );
        $wpsc_new_priority_colour = trim( $_POST['wpsc_new_priority_colour'] );
        $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_priority (priority, colour, custom, enabled) VALUES ('" . esc_sql( $wpsc_new_priority ) . "', '" . esc_sql( $wpsc_new_priority_sla ) . "', '" . esc_sql( $wpsc_new_priority_colour ) . "', 1, 1)";
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Delete Priority
     *
     *
     */
    function wpsc_delete_priority() {
        global $wpdb;
        $return = array();
        $wpsc_id = trim( $_POST['wpsc_id'] );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_priority SET enabled=0 WHERE id=" . $wpsc_id;
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Set Default Priority
     *
     *
     */
    function wpsc_priority_default() {
        global $wpdb;
        $return = array();
        $wpsc_id = trim( $_POST['wpsc_id'] );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_priority SET is_default=0";
        $result = $wpdb->query( $sql );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_priority SET is_default=1 WHERE id=" . $wpsc_id;
        $result = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save Notifications - New Ticket
     *
     *
     */
    function wpsc_save_notifications_ticket_new() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $return = array();
        $wpsc_notification_ticket_new_client = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_new_client'] ) );
        $wpsc_notification_ticket_new_admin = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_new_admin'] ) );
        $wpsc_notification_ticket_new_client_enable = $_POST['wpsc_notification_ticket_new_client_enable'];
        $wpsc_notification_ticket_new_admin_enable = $_POST['wpsc_notification_ticket_new_admin_enable'];
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . $wpsc_notification_ticket_new_client . "' WHERE title='notification_ticket_new_client'";
        $wpdb->query( $sql );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . $wpsc_notification_ticket_new_admin . "' WHERE title='notification_ticket_new_admin'";
        $wpdb->query( $sql );
        $wpsc_options['wpsc_notification_ticket_new_client_enable'] = $wpsc_notification_ticket_new_client_enable;
        $wpsc_options['wpsc_notification_ticket_new_admin_enable'] = $wpsc_notification_ticket_new_admin_enable;
        update_option( 'wpsc_options', $wpsc_options );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Reset Notifications - New Ticket
     *
     *
     */
    function wpsc_reset_notifications_ticket_new() {
        global $wpdb;
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification=default_notification WHERE title='notification_ticket_new_admin' OR title='notification_ticket_new_client'";
        $wpdb->query( $sql );
        //copy( WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_new_admin_default.tpl', WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_new_admin.tpl' );
        //copy( WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_new_client_default.tpl', WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_new_client.tpl' );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save Notifications - Reply Ticket
     *
     *
     */
    function wpsc_save_notifications_ticket_reply() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $return = array();
        $wpsc_notification_ticket_reply_client = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_reply_client'] ) );
        $wpsc_notification_ticket_reply_admin = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_reply_admin'] ) );
        $wpsc_notification_ticket_reply_client_enable = $_POST['wpsc_notification_ticket_reply_client_enable'];
        $wpsc_notification_ticket_reply_admin_enable = $_POST['wpsc_notification_ticket_reply_admin_enable'];
        $wpsc_reply_include = $_POST['wpsc_reply_include'];
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . $wpsc_notification_ticket_reply_client . "' WHERE title='notification_ticket_reply_client'";
        $wpdb->query( $sql );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . $wpsc_notification_ticket_reply_admin . "' WHERE title='notification_ticket_reply_admin'";
        $wpdb->query( $sql );
        $wpsc_options['wpsc_notification_ticket_reply_client_enable'] = $wpsc_notification_ticket_reply_client_enable;
        $wpsc_options['wpsc_notification_ticket_reply_admin_enable'] = $wpsc_notification_ticket_reply_admin_enable;
        $wpsc_options['wpsc_reply_include'] = $wpsc_reply_include;
        update_option( 'wpsc_options', $wpsc_options );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Reset Notifications - Reply Ticket
     *
     *
     */
    function wpsc_reset_notifications_ticket_reply() {
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification=default_notification WHERE title='notification_ticket_reply_admin' OR title='notification_ticket_reply_client'";
        $wpdb->query( $sql );
        //copy( WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_reply_admin_default.tpl', WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_reply_admin.tpl' );
        //copy( WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_reply_client_default.tpl', WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_reply_client.tpl' );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save Notifications - Change Ticket
     *
     *
     */
    function wpsc_save_notifications_ticket_change() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $return = array();
        $wpsc_notification_ticket_change_client = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_change_client'] ) );
        $wpsc_notification_ticket_change_admin = html_entity_decode( stripcslashes( $_POST['wpsc_notification_ticket_change_admin'] ) );
        $wpsc_notification_ticket_change_client_enable = $_POST['wpsc_notification_ticket_change_client_enable'];
        $wpsc_notification_ticket_change_admin_enable = $_POST['wpsc_notification_ticket_change_admin_enable'];
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . $wpsc_notification_ticket_change_client . "' WHERE title='notification_ticket_change_client'";
        $wpdb->query( $sql );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification='" . $wpsc_notification_ticket_change_admin . "' WHERE title='notification_ticket_change_admin'";
        $wpdb->query( $sql );
        $wpsc_options['wpsc_notification_ticket_change_client_enable'] = $wpsc_notification_ticket_change_client_enable;
        $wpsc_options['wpsc_notification_ticket_change_admin_enable'] = $wpsc_notification_ticket_change_admin_enable;
        update_option( 'wpsc_options', $wpsc_options );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Reset Notifications - Change Ticket
     *
     *
     */
    function wpsc_reset_notifications_ticket_change() {
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_notifications SET notification=default_notification WHERE title='notification_ticket_change_admin' OR title='notification_ticket_change_client'";
        $wpdb->query( $sql );
        //copy( WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_change_admin_default.tpl', WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_change_admin.tpl' );
        //copy( WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_change_client_default.tpl', WPSC_PLUGIN_DIR . '/includes/templates/notification_ticket_change_client.tpl' );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Get New Ticket & Reply Notifications
     *
     *
     */
    function wpsc_get_notifications() {
    	global $wpdb;
        $return = array();
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        if ( isset( $wpsc_options['wpsc_new_tickets'] ) && !empty( $wpsc_options['wpsc_new_tickets'] ) ) {
            $return['wpscNew'] = implode( ',', $wpsc_options['wpsc_new_tickets'] );
        } else {
            $return['wpscNew'] = '';
        }
        if ( isset( $wpsc_options['wpsc_replies'] ) && !empty( $wpsc_options['wpsc_replies'] ) ) {
            $return['wpscReply'] = implode( ',', $wpsc_options['wpsc_replies'] );
        } else {
            $return['wpscReply'] = '';
        }
        $wpsc_options['wpsc_new_tickets'] = array();
        $wpsc_options['wpsc_replies'] = array();
		$wpscUpdated = array();
		$return['wpscSLA'] = '';
		$sql = "
            SELECT
                t.id,t.subject,t.created_timestamp,t.updated_timestamp,t.client_id,t.client,t.agent_id,t.updated_by,t.status_id,
                s.status,s.colour AS status_colour,
                c.category,
                p.priority,p.priority_sla,p.colour AS priority_colour,
                ua.display_name AS agent
            FROM " . $wpdb->prefix . "wpsc_tickets t
            LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
            LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id" . $filter . ' ORDER BY t.updated_timestamp DESC';
		$tickets = $wpdb->get_results( $sql, OBJECT );
		$wpsc_sla = array();
    	if ( $wpdb->num_rows > 0 ) {
    		foreach( $tickets as $ticket ) {
    			if ( $ticket->agent_id != $ticket->updated_by && !user_can( $ticket->updated_by, 'manage_wpsc_agent' ) && ( $ticket->status_id != '2' && $ticket->status_id != '3' ) ) {
                    $sla = $ticket->priority_sla;
                    $updated = new DateTime( $ticket->updated_timestamp );
					$now = new DateTime( current_time( 'mysql',1 ) );
					$timeDiff = $updated->diff( $now );
					$diff = ( $timeDiff->days * 24 * 60 ) + ( $timeDiff->h * 60 ) + ( $timeDiff->i );
                    //$return['upd'][$ticket->id] = $updated;
                    //$return['now'][$ticket->id] = $now;
                    //$return['sla'][$ticket->id] = $sla;
                    if ( $sla != 0 ) {
	                    $calc = round( ( 255 / $sla ) * $diff );
	                    if ( $calc > 255 ) {
							$calc = 255;
						}
						//$return['wpsc_calc'][$ticket->id] = $calc;
						//$return['wpsc_diff'][$ticket->id] = $diff;
	                    $updated_background = '#' . sprintf( '%02x', 255 ) . sprintf( '%02x', 255 - $calc ) . sprintf( '%02x', 255 - $calc );
	                    $updated_text = ( wpSupportCentre::wpsc_lightness( $updated_background ) === true ) ? '#000000' : '#ffffff';
						$wpscUpdated[] = array(
							'id' => $ticket->id,
							'background' => $updated_background,
							'text' => $updated_text
						);
						if ( isset( $wpsc_options['wpsc_sla'][$ticket->id] ) && $wpsc_options['wpsc_sla'][$ticket->id] == '1' ) {
							if ( $calc == 255 ) {
								$wpsc_sla[] = $ticket->id;
								unset( $wpsc_options['wpsc_sla'][$ticket->id] );
							}
						}
			        }
                }
			}
		}
		//$return['wpsc_sla'] = json_encode( $wpsc_options['wpsc_sla'] );
		$return['wpscSLA'] = ( is_array( $wpsc_sla ) && !empty( $wpsc_sla ) ) ? implode( ',', $wpsc_sla ) : '';
		$return['wpscUpdated'] = json_encode( $wpscUpdated );
		update_option( 'wpsc_options', $wpsc_options );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Autocomplete - Registered Users
     *
     *
     */
    function wpsc_registered_users_search() {
        global $wpdb;
        $users = array();
        $wpsc_term = trim( strtolower( $_GET['term'] ) );
        $sql = "SELECT ID,user_email,display_name FROM " . $wpdb->prefix . "users WHERE user_email LIKE '%" . $wpsc_term . "%' OR display_name LIKE '%" . $wpsc_term . "%'";
        $result = $wpdb->get_results( $sql );
        foreach ( $result as $user_array) {
            $user = array();
            $user['id'] = $user_array->ID;
            $user['label'] = $user_array->display_name . ' (' . $user_array->user_email . ')';
            $user['value'] = $user_array->display_name;
            $user['email'] = $user_array->user_email;
            $users[] = $user;
        }
        echo json_encode( $users );
        wp_die();
    }
    /**
     * Create New Ticket Admin
     *
     *
     */
    function wpsc_admin_new_ticket_save() {
        //$erlevel = error_reporting(E_ALL);
        global $wpdb;
        $status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
        // save attachments
        $existing_attachments = $_POST['wpsc_admin_new_ticket_existing_attachments'];
        $attachments = $this->wpsc_save_attachments();
        if ( $existing_attachments != '' ) {
            $attachments = ( $attachments != '' ) ? $existing_attachments . ',' . $attachments : $existing_attachments;
        }
        // check if existing client or create new user
        $client_id = $_POST['wpsc_admin_new_ticket_client_id'];
        $client_email = $_POST['wpsc_admin_new_ticket_client_email'];
        $client_phone = isset( $_POST['wpsc_admin_new_ticket_phone'] ) ? $_POST['wpsc_admin_new_ticket_phone'] : '';
        if ( $client_id != 0 ) {
            if ( false !== get_user_by( 'id', $client_id ) ) {
                $user = get_user_by( 'id', $client_id );
                $client_name = $user->display_name;
                $client_email = $user->user_email;
            } else if ( false !== get_user_by( 'email', $client_email ) ) {
                $user = get_user_by( 'email', $client_email );
                $client_id = $user->ID;
                $client_name = $user->display_name;
            } else if ( false !== get_user_by( 'login', $client_email ) ) {
                $user = get_user_by( 'login', $client_email );
                $client_id = $user->ID;
                $client_name = $user->display_name;
            } else {
                $client_id = 0;
            }
        } else if ( false !== get_user_by( 'email', $client_email ) ) {
            $user = get_user_by( 'email', $client_email );
            $client_id = $user->ID;
            $client_name = $user->display_name;
        } else if ( false !== get_user_by( 'login', $client_email ) ) {
            $user = get_user_by( 'login', $client_email );
            $client_id = $user->ID;
            $client_name = $user->display_name;
        } else {
            $client_id = 0;
        }
        if ( $client_id == 0 ) {
            $split_name = explode( ' ', $_POST['wpsc_admin_new_ticket_client'], 2 );
            $first_name = $split_name[0];
            $last_name = $split_name[1];
            $client_name = $_POST['wpsc_admin_new_ticket_client'];
            $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
            $args = array(
                'user_pass' => $random_password,
                'user_login' => $client_email,
                'user_email' => $client_email,
                'display_name' => $client_name,
                'first_name' => trim( $first_name ),
                'last_name' => trim( $last_name )
            );
            $client_id = wp_insert_user( $args );
            wp_new_user_notification( $client_id, null, 'both' );
        }
        // create ticket
        if ( $_POST['wpsc_admin_new_ticket_timestamp'] != '' ) {
            $created_timestamp = $_POST['wpsc_admin_new_ticket_timestamp'];
            $updated_timestamp = $_POST['wpsc_admin_new_ticket_timestamp'];
        } else {
            $created_timestamp = current_time( 'mysql', 1 );
            $updated_timestamp =current_time( 'mysql', 1 );
        }
        $data = array(
            'status_id' => $status,
            'subject' => $_POST['wpsc_admin_new_ticket_subject'],
            'client_id' => $client_id,
            'client' => $client_name,
            'client_email' => $client_email,
            'client_phone' => $client_phone,
            'category_id' => $_POST['wpsc_new_ticket_category'],
            'agent_id' => $_POST['wpsc_new_ticket_agent'],
            'priority_id' => $_POST['wpsc_new_ticket_priority'],
            'created_timestamp' => $created_timestamp,
            'updated_timestamp' => $updated_timestamp,
            'updated_by'=> $_POST['wpsc_new_ticket_agent']
        );
        $format = array(
            '%d',
            '%s',
            '%d',
            '%s',
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
        // add thread
        $ticket_id = $wpdb->insert_id;
        $notification = ( $_POST['action_type'] == 'wpsc_admin_new_ticket_save_notify' ) ? 1 : 0;
        $data = array(
            'ticket_id' => $ticket_id,
            'message' => $_POST['wpsc_admin_new_ticket_details'],
            'attachments' => $attachments,
            'author_id' => $_POST['wpsc_admin_new_ticket_client_id'],
            'author' => $_POST['wpsc_admin_new_ticket_client'],
            'author_email' => $_POST['wpsc_admin_new_ticket_client_email'],
            'notification' => $notification,
            'thread_timestamp' => current_time( 'mysql', 1 )
        );
        $format = array(
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%d',
            '%s'
        );
        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
        $thread_id = $wpdb->insert_id;
        // send notifications
        if ( $_POST['action_type'] == 'wpsc_admin_new_ticket_save_notify' ) {
            include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
            wpsc_notification( 'new_ticket', $ticket_id, $thread_id );
        }
        $return['status'] = 'true';
        $return['ticket_id'] = $ticket_id;
        echo json_encode( $return );
        //error_reporting($erlevel);
        wp_die();
    }
    /**
     * Create New Recurring Ticket Admin
     *
     *
     */
    function wpsc_new_recurring_ticket_save() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
        // save attachments
        $attachments = $this->wpsc_save_attachments();
        // check if existing client or create new user
        $client_id = $_POST['wpsc_new_recurring_ticket_client_id'];
        $client_email = $_POST['wpsc_new_recurring_ticket_client_email'];
        if ( $client_id != 0 ) {
            if ( false !== get_user_by( 'id', $client_id ) ) {
                $user = get_user_by( 'id', $client_id );
                $client_name = $user->display_name;
                $client_email = $user->user_email;
            } else if ( false !== get_user_by( 'email', $client_email ) ) {
                $user = get_user_by( 'email', $client_email );
                $client_id = $user->ID;
                $client_name = $user->display_name;
            } else if ( false !== get_user_by( 'login', $client_email ) ) {
                $user = get_user_by( 'login', $client_email );
                $client_id = $user->ID;
                $client_name = $user->display_name;
            } else {
                $client_id = 0;
            }
        } else if ( false !== get_user_by( 'email', $client_email ) ) {
            $user = get_user_by( 'email', $client_email );
            $client_id = $user->ID;
            $client_name = $user->display_name;
        } else if ( false !== get_user_by( 'login', $client_email ) ) {
            $user = get_user_by( 'login', $client_email );
            $client_id = $user->ID;
            $client_name = $user->display_name;
        } else {
            $client_id = 0;
        }
        if ( $client_id == 0 ) {
            $split_name = explode( ' ', $_POST['wpsc_admin_client_autocomplete_recurring'], 2 );
            $first_name = $split_name[0];
            $last_name = $split_name[1];
            $client_name = $_POST['wpsc_admin_client_autocomplete_recurring'];
            $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
            $args = array(
                'user_pass' => $random_password,
                'user_login' => $client_email,
                'user_email' => $client_email,
                'display_name' => $client_name,
                'first_name' => trim( $first_name ),
                'last_name' => trim( $last_name )
            );
            $client_id = wp_insert_user( $args );
            wp_new_user_notification( $client_id, null, 'both' );
        }
        $enabled = $_POST['wpsc_admin_new_recurring_ticket_enable'];
        $data = array(
            'client_id' => $client_id,
            'subject' => $_POST['wpsc_admin_new_recurring_ticket_subject'],
            'thread' => $_POST['wpsc_new_recurring_ticket_details'],
            'attachments' => $attachments,
            'status_id' => $status,
            'category_id' => $_POST['wpsc_new_recurring_ticket_category'],
            'priority_id' => $_POST['wpsc_new_recurring_ticket_priority'],
            'agent_id' => $_POST['wpsc_new_recurring_ticket_agent_id'],
            'enabled' => $enabled,
            'notify' => $_POST['wpsc_admin_new_recurring_ticket_notify'],
            'schedule' => $_POST['wpsc_new_recurring_ticket_schedule'],
            'start_timestamp' => date( "Y-m-d H:i:s", strtotime( $_POST['wpsc_recurring_ticket_date_from'] ) ),
            'next_timestamp' => date( "Y-m-d H:i:s", strtotime( $_POST['wpsc_recurring_ticket_date_from'] ) )
        );
        $format = array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%s'
        );
        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_tickets_recurring', $data, $format );
        $return['status'] = 'true';
        $return['ticket_id'] = $ticket_id;
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save Recurring Ticket Changes Admin
     *
     *
     */
    function wpsc_edit_recurring_ticket_save() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $ticket_id = $_POST['ticket_id'];
        $status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
        // save attachments
        $attachments = $this->wpsc_save_attachments();
        // update ticket
        $enabled = $_POST['wpsc_admin_edit_recurring_ticket_enable'];
        $data = array(
            'client_id' => $_POST['wpsc_edit_recurring_ticket_client_id_' . $ticket_id],
            'subject' => $_POST['wpsc_admin_edit_recurring_ticket_subject_' . $ticket_id],
            'thread' => $_POST['wpsc_edit_recurring_ticket_details'],
            'attachments' => $attachments,
            'status_id' => $status,
            'category_id' => $_POST['wpsc_edit_recurring_ticket_category_' . $ticket_id],
            'priority_id' => $_POST['wpsc_edit_recurring_ticket_priority_' . $ticket_id],
            'agent_id' => $_POST['wpsc_edit_recurring_ticket_agent_id_' . $ticket_id],
            'enabled' => $enabled,
            'notify' => $_POST['wpsc_admin_edit_recurring_ticket_notify'],
            'schedule' => $_POST['wpsc_edit_recurring_ticket_schedule_' . $ticket_id],
            'start_timestamp' => date( "Y-m-d H:i:s", strtotime( $_POST['wpsc_edit_recurring_ticket_date_from_' . $ticket_id] ) ),
            'next_timestamp' => date( "Y-m-d H:i:s", strtotime( $_POST['wpsc_edit_recurring_ticket_date_from_' . $ticket_id] ) )
        );
        $format = array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%s'
        );
        $where = array( 'id' => $ticket_id );
        $where_format = array( '%d' );
        $update = $wpdb->update( $wpdb->prefix . 'wpsc_tickets_recurring', $data, $where, $format, $where_format );
        $return['status'] = 'true';
        $return['ticket_id'] = $ticket_id;
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Delete Recurring Ticket Admin
     *
     *
     */
    function wpsc_delete_recurring_ticket() {
        global $wpdb;
        $ticket_id = $_POST['ticket_id'];
        $where = array( 'id' => $ticket_id );
        $where_format = array( '%d' );
        $delete = $wpdb->delete( $wpdb->prefix . 'wpsc_tickets_recurring', $where, $where_format );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Get Ticket
     *
     *
     */
    function wpsc_get_admin_ticket() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $ticket_id = $_POST['ticket_id'];
        include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket.php' );
        $ticket = wpsc_get_the_admin_ticket( $ticket_id );
        $return['status'] = 'true';
        $return['ticket'] = $ticket;
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Get User Information
     *
     *
     */
    function wpsc_get_user_data() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $user_id = $_POST['user_id'];
        $display_name = $_POST['display_name'];
        include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_ticket.php' );
        $content = wpsc_get_user_information( $user_id, $display_name );
        $return['status'] = 'true';
        $return['content'] = $content;
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Get Recurring Ticket
     *
     *
     */
    function wpsc_get_recurring_ticket() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $ticket_id = $_POST['ticket_id'];
        include_once( WPSC_PLUGIN_DIR . '/includes/get_recurring_ticket.php' );
        $ticket = wpsc_get_the_recurring_ticket( $ticket_id );
        if ( $ticket != 'false' ) {
            $return['status'] = 'true';
            $return['ticket'] = $ticket;
        } else {
            $return['status'] = 'false';
        }
        echo json_encode( $return );
        wp_die();
    }
    /**
     * apply ticket actions
     *
     *
     */
    function wpsc_admin_apply_actions() {
        global $wpdb;
        $theSelectedIDs = explode( ',', $_POST['theSelectedIDs'] );
        $wpsc_ticket_action_status = $_POST['wpsc_ticket_action_status'];
        $wpsc_ticket_action_category = $_POST['wpsc_ticket_action_category'];
        $wpsc_ticket_action_agent = $_POST['wpsc_ticket_action_agent'];
        $wpsc_ticket_action_priority = $_POST['wpsc_ticket_action_priority'];
        $update_array = array();
        if ( $wpsc_ticket_action_status != '' ) {
            $update_array[] = 'status_id=' . $wpsc_ticket_action_status;
        }
        if ( $wpsc_ticket_action_category != '' ) {
            $update_array[] = 'category_id=' . $wpsc_ticket_action_category;
        }
        if ( $wpsc_ticket_action_agent != '' ) {
            $update_array[] = 'agent_id=' . $wpsc_ticket_action_agent;
        }
        if ( $wpsc_ticket_action_priority != '' ) {
            $update_array[] = 'priority_id=' . $wpsc_ticket_action_priority;
        }
        $update_array[] = "updated_timestamp='" . current_time( 'mysql', 1 ) . "'";
        $update = implode( ',', $update_array );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET " . $update . " WHERE ";
        $i = 0;
        foreach ( $theSelectedIDs as $ticket_id ) {
            $sql .= ( $i == 0 ) ? ' id=' . $ticket_id : ' OR id=' . $ticket_id;
            $i = 1;
        }
        $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * save ticket changes
     *
     *
     */
    function wpsc_ticket_save_changes() {
        global $wpdb;
        $ticket_id = $_POST['wpsc_ticket_id'];
        $wpsc_admin_new_thread_status = $_POST['wpsc_admin_new_thread_status'];
        $wpsc_admin_new_thread_category = $_POST['wpsc_admin_new_thread_category'];
        $wpsc_admin_new_thread_agent = $_POST['wpsc_admin_new_thread_agent'];
        $wpsc_admin_new_thread_priority = $_POST['wpsc_admin_new_thread_priority'];
        $wpsc_admin_new_thread_client_phone = $_POST['wpsc_admin_new_thread_client_phone'];
        $wpsc_ticket_subject = $_POST['wpsc_ticket_subject'];
        $update_array = array();
        if ( $wpsc_admin_new_thread_status != '' ) {
            $update_array[] = 'status_id=' . $wpsc_admin_new_thread_status;
        }
        if ( $wpsc_admin_new_thread_category != '' ) {
            $update_array[] = 'category_id=' . $wpsc_admin_new_thread_category;
        }
        if ( $wpsc_admin_new_thread_agent != '' ) {
            $update_array[] = 'agent_id=' . $wpsc_admin_new_thread_agent;
        }
        if ( $wpsc_admin_new_thread_priority != '' ) {
            $update_array[] = 'priority_id=' . $wpsc_admin_new_thread_priority;
        }
        if ( $wpsc_ticket_subject != '' ) {
            $update_array[] = "subject='" . $wpsc_ticket_subject . "'";
        }
        $update_array[] = "client_phone='" . $wpsc_admin_new_thread_client_phone . "'";
        $update_array[] = "updated_by=" . get_current_user_id();
        $update_array[] = "updated_timestamp='" . current_time( 'mysql', 1 ) . "'";
        $update = implode( ',', $update_array );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET " . $update . " WHERE id=" . $ticket_id;
        $wpdb->query( $sql );
        // send notifications
        if ( $_POST['wpsc_notify'] == 'true' ) {
            include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
            wpsc_notification( 'change_ticket', $ticket_id, 0 );
            $return['notify'] = 'true';
        }
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Create New Ticket Thread
     *
     *
     */
    function wpsc_new_note() {
        global $wpdb;
        $ticket_id = $_POST['wpsc_ticket_id'];
        $notify = $_POST['wpsc_notify'];
        // save attachments
        $attachments = $this->wpsc_save_attachments();
        // identify user
        if ( $_POST['wpsc_admin_thread_create_as'] == 'agent' ) {
            $user = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'users WHERE ID=' . $_POST['wpsc_admin_new_thread_agent'] );
            $author_id = $user->ID;
            $author = $user->display_name;
            $author_email = $user->user_email;
        } else if ( $_POST['wpsc_admin_thread_create_as'] == 'client' ) {
            $author_id = $wpdb->get_var( "SELECT client_id FROM " . $wpdb->prefix . "wpsc_tickets WHERE id=" . $ticket_id );
            $user = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'users WHERE ID=' . $author_id );
            $author = $user->display_name;
            $author_email = $user->user_email;
        } else {
            $author_email = $_POST['wpsc_admin_thread_from_email'];
            $user = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'users WHERE user_email=' . $author_email );
            if ( null !== $user ) {
                $author_id = $user->ID;
                $author = $user->display_name;
            } else {
                $author_id = 0;
                $author = $_POST['wpsc_admin_thread_from_name'];
            }
        }
        // update ticket
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET
            status_id='" . $_POST['wpsc_admin_new_thread_status'] . "',
            subject='" . $_POST['wpsc_ticket_subject'] . "',
            category_id='" . $_POST['wpsc_admin_new_thread_category'] . "',
            agent_id='" . $_POST['wpsc_admin_new_thread_agent'] . "',
            priority_id='" . $_POST['wpsc_admin_new_thread_priority'] . "',
            client_phone='" . $_POST['wpsc_admin_new_thread_client_phone'] . "',
            updated_timestamp='" . current_time( 'mysql', 1 ) . "',
            updated_by='" . $author_id . "'
            WHERE id=" . $ticket_id;
        $update = $wpdb->query( $sql );
        // add thread
        $notification = ( $notify == 'true' ) ? 1 : 0;
        $data = array(
            'ticket_id' => $ticket_id,
            'message' => $_POST['wpsc_admin_ticket_note'],
            'attachments' => $attachments,
            'author_id' => $author_id,
            'author' => $author,
            'author_email' => $author_email,
            'is_private' => $_POST['wpsc_admin_thread_is_private'],
            'notification' => $notification,
            'thread_timestamp' => current_time( 'mysql', 1 )
        );
        $format = array(
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s'
        );
        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
        $thread_id = $wpdb->insert_id;
        // send notifications
        if ( $notify == 'true' ) {
            include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
            wpsc_notification( 'reply_ticket', $ticket_id, $thread_id );
        }
        $return['status'] = 'true';
        $return['ticket_id'] = $ticket_id;
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save User Account Information
     *
     *
     */
    function wpsc_account_save_changes() {
        global $wpdb;
        $user_id = $_POST['wpsc_user_id'];
        $account_id = $_POST['wpsc_account_id'];
        // save attachments
        $attachments = $this->wpsc_save_attachments();
        // update or insert account information
        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_account WHERE user_id=" . $account_id;
        $result = $wpdb->get_row( $sql );
        if ( null !== $result ) {
            $sql = "UPDATE " . $wpdb->prefix . "wpsc_account SET
                content='" . $_POST['wpsc_account_information'] . "',
                attachments='" . $attachments . "',
                updated_timestamp='" . current_time( 'mysql', 1 ) . "',
                updated_by='" . $user_id . "'
                WHERE user_id=" . $account_id;
            $update = $wpdb->query( $sql );
        } else {
            $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_account (user_id,content,attachments,created_timestamp,updated_timestamp,updated_by) VALUES (" . esc_sql( $account_id ) . ",'" . esc_sql( $_POST['wpsc_account_information'] ) . "','" . esc_sql( $attachments ) . "','" . current_time( 'mysql', 1 ) . "','" . current_time( 'mysql', 1 ) . "'," . esc_sql( $user_id ) . ")";
            $insert = $wpdb->query( $sql );
        }

        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Resend Thread Notification
     *
     *
     */
    function wpsc_resend_thread_notifications() {
        global $wpdb;
        $ticket_id = $_POST['ticket_id'];
        $thread_id = $_POST['thread_id'];
        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
        $return = wpsc_notification( 'resend_notification', $ticket_id, $thread_id );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Get Template
     *
     *
     */
    function wpsc_get_template() {
        global $wpdb;
        $template_id = $_POST['template_id'];
        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_templates WHERE id=" . $template_id;
        $template = $wpdb->get_row( $sql );
        if ( $wpdb-> num_rows > 0 ) {
            $return['status'] = 'true';
            $return['label'] = $template->label;
            $return['template'] = html_entity_decode( stripcslashes( $template->template ) );
        } else {
            $return['status'] = 'false';
            $return['label'] = '';
            $return['template'] = '';
        }
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Get Template For Edit
     *
     *
     */
    function wpsc_get_template_for_edit() {
        global $wpdb;
        $template_id = $_POST['template_id'];
        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_templates WHERE id=" . $template_id;
        $template = $wpdb->get_row( $sql );
        if ( $wpdb-> num_rows > 0 ) {
            $output = '';
            $output .= '<form method="post" class="form-horizontal">';
                $output .= '<div class="form-group">';
                    $output .= '<div class="col-xs-6">';
                        $output .= '<label for="wpsc_edit_template_label">Label <span class="wpsc_required">*</span></label>';
                        $output .= '<input type="text" class="form-control" id="wpsc_edit_template_label" value="' . $template->label . '">';
                    $output .= '</div>';
                    $output .= '<div class="col-xs-6"></div>';
                $output .= '</div>';
                $output .= '<div class="form-group">';
                    $output .= '<div class="col-xs-12">';
                        $output .= '<label for="wpsc_edit_template">Template <span class="wpsc_required">*</span></label>';
                        $output .= '<textarea class="wpsc_ckeditor" id="wpsc_edit_template" name="wpsc_edit_template">' . $template->template . '</textarea>';
                    $output .= '</div>';
                $output .= '</div>';
                $output .= '<button type="button" class="wpsc_admin_button btn btn-primary btn-sm" id="wpsc_save_edit_template" data-id="' . $template->id . '">Save Changes</button>';
            $output .= '</form>';
            $return['status'] = 'true';
            $return['template'] = $output;
        } else {
            $return['status'] = 'false';
        }
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Delete Template
     *
     *
     */
    function wpsc_delete_template() {
        global $wpdb;
        $template_id = $_POST['template_id'];
        $where = array(
            'id' => $template_id
        );
        $where_format = array(
            '%d'
        );
        if ( $wpdb->delete( $wpdb->prefix . 'wpsc_templates', $where, $where_format ) ) {
            $return['status'] = 'true';
        } else {
            $return['status'] = 'false';
        }
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Delete Selected Templates
     *
     *
     */
    function wpsc_delete_selected_templates() {
        global $wpdb;
        $theIDs = explode( ',', $_POST['theIDs'] );
        foreach ( $theIDs as $template_id ) {
            $where = array(
                'id' => $template_id
            );
            $where_format = array(
                '%d'
            );
            $wpdb->delete( $wpdb->prefix . 'wpsc_templates', $where, $where_format );
        }
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save New Template
     *
     *
     */
    function wpsc_save_new_template() {
        global $wpdb;
        $wpsc_template_label = $_POST['wpsc_template_label'];
        $wpsc_template = $_POST['wpsc_template'];
        $data = array(
            'label' => $wpsc_template_label,
            'template' => $wpsc_template
        );
        $format = array(
            '%s',
            '%s'
        );
        if ( false !== $wpdb->insert( $wpdb->prefix . 'wpsc_templates', $data, $format ) ) {
            $return['status'] = 'true';
        } else {
            $return['status'] = 'false';
        }
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save Template Changes
     *
     *
     */
    function wpsc_save_template_changes() {
        global $wpdb;
        $template_id = $_POST['template_id'];
        $wpsc_template_label_edit = $_POST['wpsc_template_label_edit'];
        $wpsc_edit_template = $_POST['wpsc_edit_template'];
        $data = array(
            'label' => $wpsc_template_label_edit,
            'template' => $wpsc_edit_template
        );
        $format = array(
            '%s',
            '%s'
        );
        $where = array(
            'id' => $template_id
        );
        $where_format = array(
            '%d'
        );
        if ( false !== $wpdb->update( $wpdb->prefix . 'wpsc_templates', $data, $where, $format, $where_format ) ) {
            $return['status'] = 'true';
        } else {
            $return['status'] = 'false';
        }
        echo json_encode( $return );
        wp_die();
    }
    /**
     * apply recurring ticket actions
     *
     *
     */
    function wpsc_admin_apply_recurring_actions() {
        global $wpdb;
        $theSelectedIDs = explode( ',', $_POST['theSelectedIDs'] );
        $wpsc_recurring_action_status = $_POST['wpsc_recurring_action_status'];
        $wpsc_recurring_action_category = $_POST['wpsc_recurring_action_category'];
        $wpsc_recurring_action_priority = $_POST['wpsc_recurring_action_priority'];
        $wpsc_recurring_action_agent = $_POST['wpsc_recurring_action_agent'];
        $update_array = array();
        if ( $wpsc_recurring_action_status != '' ) {
            $update_array[] = 'status_id=' . $wpsc_recurring_action_status;
        }
        if ( $wpsc_recurring_action_category != '' ) {
            $update_array[] = 'category_id=' . $wpsc_recurring_action_category;
        }
        if ( $wpsc_recurring_action_priority != '' ) {
            $update_array[] = 'priority_id=' . $wpsc_recurring_action_priority;
        }
        if ( $wpsc_recurring_action_agent != '' ) {
            $update_array[] = 'agent_id=' . $wpsc_recurring_action_agent;
        }
        $update = implode( ',', $update_array );
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets_recurring SET " . $update . " WHERE ";
        $i = 0;
        foreach ( $theSelectedIDs as $ticket_id ) {
            $sql .= ( $i == 0 ) ? ' id=' . $ticket_id : ' OR id=' . $ticket_id;
            $i = 1;
        }
        $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Get Front Ticket
     *
     *
     */
    function wpsc_get_front_ticket() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $ticket_id = $_POST['ticket_id'];
        include_once( WPSC_PLUGIN_DIR . '/includes/get_front_ticket.php' );
        $ticket = wpsc_get_the_front_ticket( $ticket_id );
        $return['status'] = 'true';
        $return['ticket'] = $ticket;
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Create New Ticket Reply
     *
     *
     */
    function wpsc_client_reply() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $ticket_id = $_POST['wpsc_ticket_id'];
        $user_id = $_POST['wpsc_uid'];
        // save attachments
        $attachments = $this->wpsc_save_attachments();
        // update ticket
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET
            status_id=5,
            priority_id='" . $_POST['wpsc_front_new_thread_priority'] . "',
            updated_timestamp='" . current_time( 'mysql', 1 ) . "',
            updated_by='" . $user_id . "'
            WHERE id=" . $ticket_id;
        $update = $wpdb->query( $sql );
        if ( is_array( $wpsc_options['wpsc_replies'] ) ) {
            if ( !in_array( $ticket_id, $wpsc_options['wpsc_replies'] ) ) {
                array_push( $wpsc_options['wpsc_replies'], $ticket_id );
            }
        } else {
            $wpsc_options['wpsc_replies'] = array( $ticket_id );
        }
        // add thread
        if ( $user_id != 0 ) {
            $user = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'users WHERE ID=' . $user_id );
            if ( $user ) {
                $author = $user->display_name;
                $author_email = $user->user_email;
            } else {
                $author = ( isset( $_POST['wpsc_front_new_ticket_client_name'] ) ) ? $_POST['wpsc_front_new_ticket_client_name'] : 'Guest';
                $author_email = ( isset( $_POST['wpsc_front_new_ticket_client_email'] ) ) ? $_POST['wpsc_front_new_ticket_client_email'] : 'Guest Email';;
            }
        } else {
            $author = ( isset( $_POST['wpsc_front_new_ticket_client_name'] ) ) ? $_POST['wpsc_front_new_ticket_client_name'] : 'Guest';
            $author_email = ( isset( $_POST['wpsc_front_new_ticket_client_email'] ) ) ? $_POST['wpsc_front_new_ticket_client_email'] : 'Guest Email';;
        }
        $data = array(
            'ticket_id' => $ticket_id,
            'message' => $_POST['wpsc_front_ticket_note'],
            'attachments' => $attachments,
            'author_id' => $user_id,
            'author' => $author,
            'author_email' => $author_email,
            'thread_timestamp' => current_time( 'mysql', 1 )
        );
        $format = array(
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s'
        );
        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
        $thread_id = $wpdb->insert_id;
        // send notifications
        update_option( 'wpsc_options', $wpsc_options );
        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
        wpsc_notification( 'client_reply', $ticket_id, $thread_id );
        $return['status'] = 'true';
        $return['ticket_id'] = $ticket_id;
        $return['attachments'] = $attachments;
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Create New Ticket Front
     *
     *
     */
    function wpsc_front_new_ticket_save() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $status = $wpdb->get_var( "SELECT id FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1" );
        // save attachments
        $attachments = $this->wpsc_save_attachments();
        $client_id = $_POST['wpsc_front_new_ticket_client_id'];
        $client_email = $_POST['wpsc_front_new_ticket_client_email'];
        $client_phone = isset( $_POST['wpsc_front_new_ticket_client_phone'] ) ? $_POST['wpsc_front_new_ticket_client_phone'] : '';
        if ( $client_id != 0 ) {
            if ( false !== get_user_by( 'id', $client_id ) ) {
                $user = get_user_by( 'id', $client_id );
                $client_name = $user->display_name;
                $client_email = $user->user_email;
            } else if ( false !== get_user_by( 'email', $client_email ) ) {
                $user = get_user_by( 'email', $client_email );
                $client_id = $user->ID;
                $client_name = $user->display_name;
            } else if ( false !== get_user_by( 'login', $client_email ) ) {
                $user = get_user_by( 'login', $client_email );
                $client_id = $user->ID;
                $client_name = $user->display_name;
            } else {
                $client_id = 0;
            }
        } else if ( false !== get_user_by( 'email', $client_email ) ) {
            $user = get_user_by( 'email', $client_email );
            $client_id = $user->ID;
            $client_name = $user->display_name;
        } else if ( false !== get_user_by( 'login', $client_email ) ) {
            $user = get_user_by( 'login', $client_email );
            $client_id = $user->ID;
            $client_name = $user->display_name;
        } else {
            $client_id = 0;
        }
        if ( $client_id == 0 ) {
            if ( isset( $_POST['wpsc_front_new_ticket_client_name'] ) && $_POST['wpsc_front_new_ticket_client_name'] != '' ) {
                if ( strpos( $_POST['wpsc_front_new_ticket_client_name'], ' ') > 0 ) {
                    $split_name = explode( ' ', $_POST['wpsc_front_new_ticket_client_name'], 2 );
                    $first_name = $split_name[0];
                    $last_name = $split_name[1];
                } else {
                    $first_name = $_POST['wpsc_front_new_ticket_client_name'];
                    $last_name = '';
                }
            } else {
                $first_name = $_POST['wpsc_front_new_ticket_client_first_name'];
                $last_name = $_POST['wpsc_front_new_ticket_client_last_name'];
            }
            $client_name = trim( $first_name . ' ' . $last_name );
            $random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
            $args = array(
                'user_pass' => $random_password,
                'user_login' => $client_email,
                'user_email' => $client_email,
                'display_name' => $client_name,
                'first_name' => trim( $first_name ),
                'last_name' => trim( $last_name )
            );
            $client_id = wp_insert_user( $args );
            wp_new_user_notification( $client_id, null, 'both' );
            $return['new_user'] = 'true';
        } else {
            $return['new_user'] = 'false';
        }
        // select agent
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
        // create ticket
        $data = array(
            'status_id' => $status,
            'subject' => $_POST['wpsc_front_new_ticket_subject'],
            'client_id' => $client_id,
            'client' => $client_name,
            'client_email' => $client_email,
            'client_phone' => $client_phone,
            'category_id' => $_POST['wpsc_front_new_ticket_category'],
            'agent_id' => $agent_id,
            'priority_id' => $_POST['wpsc_front_new_ticket_priority'],
            'created_timestamp' => current_time( 'mysql', 1 ),
            'updated_timestamp' => current_time( 'mysql', 1 ),
            'updated_by'=> $client_id
        );
        $format = array(
            '%d',
            '%s',
            '%d',
            '%s',
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
        // add thread
        $ticket_id = $wpdb->insert_id;
        if ( is_array( $wpsc_options['wpsc_new_tickets'] ) ) {
            if ( !in_array( $ticket_id, $wpsc_options['wpsc_new_tickets'] ) ) {
                array_push( $wpsc_options['wpsc_new_tickets'], $ticket_id );
            }
        } else {
            $wpsc_options['wpsc_new_tickets'] = array( $ticket_id );
        }
        $data = array(
            'ticket_id' => $ticket_id,
            'message' => $_POST['wpsc_front_new_ticket_details'],
            'attachments' => $attachments,
            'author_id' => $client_id,
            'author' => $client_name,
            'author_email' => $client_email,
            'thread_timestamp' => current_time( 'mysql', 1 )
        );
        $format = array(
            '%d',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s'
        );
        $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
        $thread_id = $wpdb->insert_id;
        // send notifications
        update_option( 'wpsc_options', $wpsc_options );
        include_once( WPSC_PLUGIN_DIR . '/includes/notifications.php' );
        wpsc_notification( 'new_ticket_front', $ticket_id, $thread_id );
        $return['status'] = 'true';
        $return['ticket_id'] = $ticket_id;
        $return['uid'] = $client_id;
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Create New Ticket From Thread
     *
     *
     */
    function wpsc_new_ticket_from_thread() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $thread_id = $_POST['thread_id'];
        $client_id = $_POST['client_id'];
        if ( $client_id != 0 ) {
            if ( false !== get_user_by( 'id', $client_id ) ) {
                $user = get_user_by( 'id', $client_id );
                $client_name = $user->display_name;
                $client_email = $user->user_email;
            } else {
                $client_id = 0;
            }
        }
        if ( $client_id == 0 ) {
            $return['status'] = 'false';
            echo json_encode( $return );
            wp_die();
        }
        // select thread contents
        $sql = "SELECT message FROM " . $wpdb->prefix . "wpsc_threads WHERE id=" . $thread_id;
        $thread = $wpdb->get_var( $sql );
        $return['status'] = 'true';
        $return['client'] = $client_name;
        $return['client_email'] = $client_email;
        $return['thread'] = html_entity_decode( stripcslashes( $thread ) );
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Copy Thread to Ticket
     *
     *
     */
    function wpsc_copy_thread_to_ticket() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $thread_id = $_POST['thread_id'];
        $ticket_id = $_POST['ticket_id'];
        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE id=" . $thread_id;
        $thread = $wpdb->get_row( $sql, ARRAY_A );
        $count = 0;
        $keys = '';
        $values = '';
        foreach ( $thread as $k => $v ) {
            if ( $k != 'id' ) {
                $keys .= ( $count == 0 ) ? $k : ',' . $k;
                if ( $k == 'ticket_id' ) {
                    $values .= ( $count == 0 ) ? "'" . $ticket_id . "'" : ",'" . $ticket_id . "'";
                } else {
                    $values .= ( $count == 0 ) ? "'" . esc_sql( $v ) . "'" : ",'" . esc_sql( $v ) . "'";
                }
                $count++;
            }
        }
		$wpdb->show_errors();
        $sql = "INSERT INTO " . $wpdb->prefix . "wpsc_threads (" . $keys . ") VALUES (" . $values . ")";
        $wpdb->query( $sql );
		$wpdb->hide_errors();
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save Agent Settings
     *
     *
     */
    function wpsc_save_agent_settings() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $return = array();
        $wpsc_agent_default = trim( $_POST['wpsc_agent_default'] );
        $wpsc_support_page = trim( $_POST['wpsc_support_page'] );
        $wpsc_options['wpsc_default_agent'] = $wpsc_agent_default;
        update_option( 'wpsc_options', $wpsc_options );
        $wpsc_agents = explode( ',', $_POST['wpsc_agents'] );
        $wpsc_supers = explode( ',', $_POST['wpsc_supers'] );
        $wpsc_agents_remove = explode( ',', $_POST['wpsc_agents_remove'] );
        $wpsc_supers_remove = explode( ',', $_POST['wpsc_supers_remove'] );
        foreach ( $wpsc_agents as $user_id ) {
            $user = new WP_User( $user_id );
            $user->add_cap( 'manage_wpsc_ticket' );
        }
        foreach ( $wpsc_supers as $user_id ) {
            $user = new WP_User( $user_id );
            $user->add_cap( 'manage_wpsc_agent' );
        }
        foreach ( $wpsc_agents_remove as $user_id ) {
            $user = new WP_User( $user_id );
            $user->remove_cap( 'manage_wpsc_ticket' );
        }
        foreach ( $wpsc_supers_remove as $user_id ) {
            $user = new WP_User( $user_id );
            $user->remove_cap( 'manage_wpsc_agent' );
        }
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Refresh Admin Tickets Table
     *
     *
     */
    function wpsc_doRefreshAdminTicketsTable() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $return = array();
        $filter = '';
        include_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_support_centre/get_admin_tickets_table.php' );
        $output = apply_filters( 'do_wpsc_admin_get_tickets_table', '', $filter, $wpsc_options );
        $return['status'] = 'true';
        $return['table'] = $output;
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Delete Attachment
     *
     *
     */
    function wpsc_delete_attachment() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $return = array();
        $attachment_id = $_POST['attachment_id'];
        wp_delete_attachment( $attachment_id, true );
        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads WHERE attachments LIKE '%" . $attachment_id . "%'";
        $result = $wpdb->get_results( $sql );
        if ( null !== $result ) {
            foreach ( $result as $thread ) {
                $attachments = explode( ',', $thread->attachments );
                if ( ( $key = array_search( $attachment_id, $attachments ) ) !== false ) {
                    unset( $attachments[$key] );
                    $new_attachments = implode( ',', $attachments );
                    $sql = "UPDATE " . $wpdb->prefix . "wpsc_threads SET attachments='" . $new_attachments . "' WHERE id=" . $thread->id;
                    $update = $wpdb->query( $sql );
                }
            }
        }
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Share Ticket
     *
     *
     */
    function wpsc_ticket_shared_users() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $return = array();
        $ticket_id = $_POST['ticket_id'];
        $wpsc_ticket_shared_users = $_POST['wpsc_ticket_shared_users'];
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_tickets SET shared_users='" . $wpsc_ticket_shared_users . "' WHERE id=" . $ticket_id;
        $update = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Pinned Thread
     *
     *
     */
    function wpsc_pinned_thread() {
        global $wpdb;
        $return = array();
        $thread_id = $_POST['thread_id'];
        $val = $_POST['val'];
        $sql = "UPDATE " . $wpdb->prefix . "wpsc_threads SET is_pinned='" . $val . "' WHERE id=" . $thread_id;
        $update = $wpdb->query( $sql );
        $return['status'] = 'true';
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Save Attachments
     *
     *
     */
    function wpsc_save_attachments() {
        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
        require_once(ABSPATH . "wp-admin" . '/includes/file.php');
        require_once(ABSPATH . "wp-admin" . '/includes/media.php');
        $attach_ids = array();
        $attachments = ( isset( $_POST['wpsc_admin_existing_thread_attachments'] ) && $_POST['wpsc_admin_existing_thread_attachments'] != '' ) ? $_POST['wpsc_admin_existing_thread_attachments'] : '';
        $attach_ids = ( $attachments == '' ) ? array() : explode( ',', $attachments );
        $wp_upload_dir = wp_upload_dir();
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
                $theID = media_handle_upload( 'new_files', 0 );
                if ( !is_wp_error( $theID ) ) {
                    $attach_ids[] = $theID;
                } else {
                    $upload_dir = wp_upload_dir();
                    $tmp_file = $upload_dir['path'] . '/' . time() . '_' . $file['name'];
                    $zip_file = $upload_dir['path'] . '/' . $file['name'] . '.zip';
                    while ( file_exists( $zip_file ) ) {
                        $zip_file = $upload_dir['path'] . '/' . time() . '_' . $file['name'] . '.zip';
                    }
                    move_uploaded_file( $file['tmp_name'], $tmp_file );
                    $zip = new ZipArchive();
                    if ( $zip->open( $zip_file, ZipArchive::CREATE ) ) {
                        $zip->addFile( $tmp_file, $file['name'] );
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
                            $attach_ids[] = $attach_id;
                        }
                    }
                    unlink( $tmp_file );
                }
            }
            $attachments = ( !empty( $attach_ids ) ) ? implode( ',', $attach_ids ) : '';
        } else if ( isset( $_POST['attachment_ids'] ) ) {
            $attachments = $_POST['attachment_ids'];
        } else {
            $attachments = ( !empty( $attach_ids ) ) ? implode( ',', $attach_ids ) : '';
        }
        return $attachments;
    }
    /**
     * Get Email Preview
     *
     *
     */
    function wpsc_get_email_preview() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $email_id = $_POST['email_id'];
        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_piping_preview WHERE id=" . $email_id;
        $select = $wpdb->get_row( $sql );
        if ( !is_null( $select ) ) {
            $thread_date = date_i18n( get_option( 'date_format' ), strtotime( get_date_from_gmt( $select->thread_timestamp, 'Y-m-d H:i:s') ) ) . ' ' . get_date_from_gmt( $select->thread_timestamp, 'H:i:s');
            $email = '';
            $email .= '<div id="wpsc_email_preview_modal" class="modal fade" data-backdrop="static">';
                $email .= '<div class="modal-dialog piping-modal">';
                    $email .= '<div class="modal-content">';
                        $email .= '<div class="modal-header">';
                            $email .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
                            $email .= '<h4 class="modal-title">' . $select->subject . '</h4>';
                        $email .= '</div>';
                        $email .= '<div class="modal-body piping-modal-body">';
                            $email .= '<div class="panel panel-default">';
                                $email .= '<table class="wpsc_fullwidth wpsc_thread_header">';
                                    $email .= '<tr>';
                                        $email .= '<td class="wpsc_width106px">';
                                            $email .= get_avatar( $select->author_id );
                                        $email .= '</td>';
                                        $email .= '<td>';
                                            $email .= '<span class="wpsc_thread_header_author">' . $select->author . '</span><br />';
                                            $email .= '<em>' . $select->author_email . '</em><br />';
                                            $email .= '<em>' . $thread_date . '</em>';
                                        $email .= '</td>';
                                    $email .= '</tr>';
                                $email .= '</table>';
                                $email .= html_entity_decode( stripcslashes( $select->message ) );
                                $email .= '<hr />';
                                if ( $select->attachments != '' ) {
                                    $email .= '<h3>Attachments</h3>';
                                    $attachments = explode( ',', $select->attachments );
                                    foreach ( $attachments as $attachment ) {
                                        $filename = basename( get_attached_file( $attachment ) );
                                        if ( $filename != '' ) {
                                            $url = wp_get_attachment_url( $attachment );
                                            $email .= '<em><a href="' . $url . '" target="_blank">' . $filename . '</a></em><br />';
                                        } else {
                                            $email .= '<em>Error ' . $attachment  . ': File Not Found</em><br />';
                                        }
                                    }
                                }
                            $email .= '</div>';
                        $email .= '</div>';
                        $email .= '<div class="modal-footer">';
                            $email .= '<button type="button" class="btn btn-primary btn-sm" id="wpsc_piping_new_ticket" data-id="' . $email_id . '">Create New Ticket</button> ';
                            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_tickets WHERE status_id!=2 AND status_id!=3 ORDER BY id DESC";
                            $tickets = $wpdb->get_results( $sql );
                            if ( !empty( $tickets ) && !is_null( $tickets ) ) {
                                $email .= '<button type="button" class="btn btn-primary btn-sm" id="wpsc_piping_new_thread">Add To Ticket</button> ';
                                $email .= '<select id="wpsc_open_tickets" style="display:none" data-id="' . $email_id . '">';
                                    foreach ( $tickets as $ticket ) {
                                        $email .= '<option value="' . $ticket->id . '">[' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ': ' . $ticket->id . '] ' . stripcslashes( $ticket->subject ) . '</option>';
                                    }
                                $email .= '</select> ';
                            }
                            $email .= '<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>';
                        $email .= '</div>';
                    $email .= '</div>';
                $email .= '</div>';
            $email .= '</div>';
            $return['status'] = 'true';
            $return['modal'] = $email;
        } else {
            $return['status'] = 'false';
        }
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Add Email To Ticket
     *
     *
     */
    function wpsc_add_email_to_ticket() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $email_id = $_POST['email_id'];
        $ticket_id = $_POST['ticket_id'];
        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_piping_preview WHERE id=" . $email_id;
        $select = $wpdb->get_row( $sql );
        if ( !is_null( $select ) ) {
            // add thread
            $data = array(
                'ticket_id' => $ticket_id,
                'message' => $select->message,
                'attachments' => $select->attachments,
                'author_id' => $select->author_id,
                'author' => $select->author,
                'author_email' => $select->author_email,
                'thread_timestamp' => $select->thread_timestamp
            );
            $format = array(
                '%d',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s'
            );
            $insert = $wpdb->insert( $wpdb->prefix . 'wpsc_threads', $data, $format );
            $return['status'] = 'true';
        } else {
            $return['status'] = 'false';
        }
        echo json_encode( $return );
        wp_die();
    }
    /**
     * Create New Ticket From Piping
     *
     *
     */
    function wpsc_new_ticket_from_piping() {
        global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $email_id = $_POST['email_id'];
        $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_piping_preview WHERE id=" . $email_id;
        $select = $wpdb->get_row( $sql );
        if ( !is_null( $select ) ) {
            $return['status'] = 'true';
            $return['client_id'] = $select->author_id;
            $return['client'] = $select->author;
            $return['client_email'] = $select->author_email;
            $return['subject'] = $select->subject;
            $return['thread'] = $select->message;
            $return['attachments'] = $select->attachments;
            $return['timestamp'] = $select->thread_timestamp;
        } else {
            $return['status'] = 'false';
        }
        echo json_encode( $return );
        wp_die();
    }
}