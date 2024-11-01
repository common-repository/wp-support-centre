<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Get New Ticket & Reply Notifications
 *
 *
 */
function wpsc_get_notifications() {
	global $wpdb;
    $return = array();
	$agent_id = get_current_user_id();
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
	$wpscReminders = array();
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
        LEFT JOIN " . $wpdb->prefix . "users ua ON ua.ID=t.agent_id
        WHERE t.agent_id=" . $agent_id . " AND (t.status_id=1 OR t.status_id=5)
        ORDER BY t.updated_timestamp DESC";
	$tickets = $wpdb->get_results( $sql, OBJECT );
	$wpsc_sla = array();
	if ( $wpdb->num_rows > 0 ) {
		foreach( $tickets as $ticket ) {
            $sla = $ticket->priority_sla;
            $updated = new DateTime( $ticket->updated_timestamp );
			$now = new DateTime( current_time( 'mysql',1 ) );
			$timeDiff = $updated->diff( $now );
			$diff = ( $timeDiff->days * 24 * 60 ) + ( $timeDiff->h * 60 ) + ( $timeDiff->i );
            if ( $sla != 0 ) {
                $calc = round( ( 255 / $sla ) * $diff );
                if ( $calc > 255 ) {
					$calc = 255;
				}
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
	$now = current_time( 'mysql', 1 );
	$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_reminders WHERE due_timestamp<'" . $now . "'";
	$reminders = $wpdb->get_results( $sql );
	if ( !empty( $reminders ) && !is_null( $reminders ) ) {
		foreach( $reminders as $reminder ) {
			$wpscReminders[] = array(
				'ticket_id' => $reminder->ticket_id,
				'subject' => $reminder->subject
			);
			$sql = "DELETE FROM " . $wpdb->prefix . "wpsc_reminders WHERE id=" . $reminder->id;
			$delete = $wpdb->query( $sql );
		}
	}
	$return['wpscSLA'] = ( is_array( $wpsc_sla ) && !empty( $wpsc_sla ) ) ? implode( ',', $wpsc_sla ) : '';
	$return['wpscUpdated'] = json_encode( $wpscUpdated );
	$return['wpscReminders'] = json_encode( $wpscReminders );
	update_option( 'wpsc_options', $wpsc_options );
    $return['status'] = 'true';
    echo json_encode( $return );
    wp_die();
}