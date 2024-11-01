<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
function wpsc_do_shortcodes( $message, $ticket_id, $thread_id ) {
    global $wpdb;
    $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
    if ( $thread_id != 0 ) {
        $thread_select = ' ,th.message ';
        $thread_join = ' LEFT JOIN ' . $wpdb->prefix . 'wpsc_threads th ON th.id=' . $thread_id;
    } else {
        $thread_select = '';
        $thread_join = '';
    }
    $sql = "SELECT
                t.id,t.subject,t.updated_timestamp,t.client_id,t.client,t.agent_id,t.category_id,t.priority_id,t.status_id,t.client_email,
                s.status,s.colour AS status_colour,
                c.category,
                p.priority,p.colour AS priority_colour,
                ua.display_name AS agent,ua.user_email as agent_email
                " . $thread_select . "
            FROM " . $wpdb->prefix . "wpsc_tickets t
            LEFT JOIN " . $wpdb->prefix . "wpsc_status s ON s.id=t.status_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_categories c ON c.id=t.category_id
            LEFT JOIN " . $wpdb->prefix . "wpsc_priority p ON p.id=t.priority_id
            LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id
            " . $thread_join . "
            WHERE t.id=" . $ticket_id;
    $ticket = $wpdb->get_row( $sql );
    $message = str_replace( '[wpsc_item]', apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ), $message );
    $message = str_replace( '[wpsc_client]', apply_filters( 'wpsc_client', 'Client', $wpsc_options ), $message );
    $message = str_replace( '[wpsc_plugin_url]', WPSC_PLUGIN_URL, $message );
    if ( $wpsc_options['wpsc_support_page'] != 0 ) {
        $message = str_replace( '[wpsc_ticket_url]', get_permalink( $wpsc_options['wpsc_support_page'] ) . '?ticket_id=' . $ticket_id, $message );
    } else {
        $message = str_replace( '[wpsc_ticket_url]', '#' );
    }
    $message = str_replace( '[wpsc_admin_ticket_url]', admin_url( 'admin.php' ) . '?page=wp-support-centre&ticket_id=' . $ticket_id, $message );
    $message = str_replace( '[wpsc_ticket_no]', $ticket_id, $message );
    $message = str_replace( '[wpsc_ticket_subject]', stripcslashes( $ticket->subject ), $message );
    $message = str_replace( '[wpsc_ticket_status]', $ticket->status, $message );
    $message = str_replace( '[wpsc_ticket_category]', $ticket->category, $message );
    $message = str_replace( '[wpsc_ticket_priority]', $ticket->priority, $message );
    $message = str_replace( '[wpsc_thread_message]', base64_decode( $ticket->message ), $message );
    $message = str_replace( '[wpsc_ticket_agent]', $ticket->agent, $message );
    $message = str_replace( '[wpsc_blog_name]', get_option( 'blogname' ), $message );
    $message = str_replace( '[wpsc_site_url]', home_url( '/' ), $message );
    if ( $wpsc_options['wpsc_support_page'] != 0 ) {
        $message = str_replace( '[wpsc_support_centre_url]', get_permalink( $wpsc_options['wpsc_support_page'] ), $message );
    } else {
        $message = str_replace( '[wpsc_ticket_url]', '#' );
    }
    $message = str_replace( '[wpsc_signature_from_name]', $wpsc_options['wpsc_signature_from_name'], $message );
    $message = str_replace( '[wpsc_signature_from_email]', $wpsc_options['wpsc_signature_from_email'], $message );
    $message = str_replace( '[wpsc_signature_reply_to]', $wpsc_options['wpsc_signature_reply_to'], $message );
    $message = str_replace( '[wpsc_signature_agent_name]', $ticket->agent, $message );
    $message = str_replace( '[wpsc_signature_agent_email]', $ticket->agent_email, $message );
    return html_entity_decode( stripcslashes( $message ) );
}
add_filter( 'wpsc_do_shortcodes_filter', 'wpsc_do_shortcodes', 10, 3 );
?>