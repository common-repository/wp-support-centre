<?php
if ( !isset( $_REQUEST['action'] ) ) {
	die('-1');
}

define( 'DOING_AJAX', true );
define( 'BASE_PATH', find_wordpress_base_path() . "/" );

require_once( BASE_PATH . 'wp-load.php' );
require_once( BASE_PATH . 'wp-includes/pluggable.php' );

header( 'Content-Type: text/html' );
send_nosniff_header();
header( 'Cache-Control: no-cache' );
header( 'Pragma: no-cache' );

require_once( WPSC_PLUGIN_DIR . '/includes/functions.php' );

$action = esc_attr( trim( $_REQUEST['action'] ) );

$allowed_actions = array(
	'wpsc_account_save_changes',
	'wpsc_add_email_to_ticket',
	'wpsc_add_new_category',
	'wpsc_add_new_priority',
	'wpsc_add_new_status',
	'wpsc_admin_apply_actions',
	'wpsc_admin_apply_recurring_actions',
	'wpsc_admin_new_ticket_save',
	'wpsc_category_default',
	'wpsc_client_reply',
	'wpsc_copy_thread_to_ticket',
	'wpsc_delete_attachment',
	'wpsc_delete_category',
	'wpsc_delete_imap',
	'wpsc_delete_priority',
	'wpsc_delete_recurring_ticket',
	'wpsc_delete_selected_templates',
	'wpsc_delete_status',
	'wpsc_delete_template',
	'wpsc_dismiss_notice',
	'wpsc_doRefreshAdminTicketsTable',
	'wpsc_edit_recurring_ticket_save',
	'wpsc_front_new_ticket_save',
	'wpsc_get_admin_ticket',
	'wpsc_get_email_preview',
	'wpsc_get_front_ticket',
	'wpsc_get_notifications',
	'wpsc_get_recurring_ticket',
	'wpsc_get_template',
	'wpsc_get_template_for_edit',
	'wpsc_get_user_data',
	'wpsc_new_note',
	'wpsc_new_recurring_ticket_save',
	'wpsc_new_ticket_from_piping',
	'wpsc_new_ticket_from_thread',
	'wpsc_pinned_thread',
	'wpsc_priority_default',
	'wpsc_registered_users_search',
	'wpsc_resend_thread_notifications',
	'wpsc_reset_notifications_ticket_change',
	'wpsc_reset_notifications_ticket_new',
	'wpsc_reset_notifications_ticket_reply',
	'wpsc_save_agent_settings',
	'wpsc_save_email',
	'wpsc_save_general',
	'wpsc_save_misc',
	'wpsc_save_new_template',
	'wpsc_save_notifications_ticket_change',
	'wpsc_save_notifications_ticket_new',
	'wpsc_save_notifications_ticket_reply',
	'wpsc_save_priority_colour',
	'wpsc_save_status_colour',
	'wpsc_save_template_changes',
	'wpsc_status_default',
	'wpsc_ticket_save_changes',
	'wpsc_ticket_shared_users'
);

if ( in_array( $action, $allowed_actions ) ) {
	if ( function_exists( $action ) ) {
		if( !is_user_logged_in() ) {
			do_action('wpsc_ajax_nopriv_' . $action );
		} else {
			do_action('wpsc_ajax_' . $action );
		}
	} else {
		die('-1');
	}
} else {
	die('-1');
}

function find_wordpress_base_path() {
    $dir = dirname(__FILE__);
    do {
        if( file_exists( $dir . "/wp-config.php") ) {
            return $dir;
        }
    } while( $dir = realpath( "$dir/.." ) );
    return null;
}