<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//include_once( WPSC_PLUGIN_DIR . 'ajax/functions.php' );

include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_account_save_changes.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_add_email_to_ticket.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_add_new_category.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_add_new_priority.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_add_new_status.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_admin_apply_actions.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_admin_apply_recurring_actions.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_admin_new_ticket_save.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_category_default.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_client_reply.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_copy_thread_to_ticket.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_delete_attachment.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_delete_category.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_delete_imap.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_delete_priority.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_delete_recurring_ticket.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_delete_selected_templates.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_delete_status.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_delete_template.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_dismiss_notice.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_doRefreshAdminTicketsTable.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_edit_recurring_ticket_save.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_front_new_ticket_save.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_get_admin_ticket.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_get_email_preview.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_get_front_ticket.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_get_notifications.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_get_recurring_ticket.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_get_template.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_get_template_for_edit.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_get_user_data.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_new_note.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_new_recurring_ticket_save.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_new_ticket_from_piping.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_new_ticket_from_thread.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_pinned_thread.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_priority_default.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_registered_users_search.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_resend_thread_notifications.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_reset_notifications_ticket_change.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_reset_notifications_ticket_new.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_reset_notifications_ticket_reply.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_agent_settings.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_attachments.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_email.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_general.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_misc.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_new_template.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_notifications_ticket_change.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_notifications_ticket_new.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_notifications_ticket_reply.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_priority_colour.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_status_colour.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_save_template_changes.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_status_default.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_ticket_save_changes.php' );
include_once( WPSC_PLUGIN_DIR . 'ajax/wpsc_ticket_shared_users.php' );