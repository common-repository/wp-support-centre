<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
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
    $sql = "SELECT
				u.ID,u.user_email,u.display_name,
				ma.meta_value AS first_name,
				mb.meta_value AS last_name,
				MAX(t.client_phone) AS client_phone
			FROM
				" . $wpdb->prefix . "users u
			LEFT JOIN
				" . $wpdb->prefix . "usermeta ma
			ON
				( u.ID=ma.user_id AND ma.meta_key='first_name' )
			LEFT JOIN
				" . $wpdb->prefix . "usermeta mb
			ON
				( u.ID=mb.user_id AND mb.meta_key='last_name' )
			LEFT JOIN
				" . $wpdb->prefix . "wpsc_tickets t
			ON
				( u.ID=t.client_id AND t.client_phone!= '' )
			WHERE
		    	u.user_email LIKE '%" . $wpsc_term . "%'
		    OR
		    	u.display_name LIKE '%" . $wpsc_term . "%'
		    OR
		    	ma.meta_value LIKE '%" . $wpsc_term . "%'
		    OR
		    	mb.meta_value LIKE '%" . $wpsc_term . "%'
			GROUP BY
				u.user_email
			ORDER BY
				u.user_email ASC";
    $result = $wpdb->get_results( $sql );
    foreach ( $result as $user_array) {
        $user = array();
        $user['id'] = $user_array->ID;
		if ( false !== strpos( $user_array->display_name, ' ' ) ) {
			$display_name = $user_array->display_name;
		} else if ( $user_array->first_name != '' && $user_array->last_name != '' ) {
			$display_name = $user_array->first_name . ' ' . $user_array->last_name;
		} else {
			$display_name = '';
		}
		if ( $display_name != '' ) {
			$user['label'] = $display_name . ' (' . $user_array->user_email . ')';
        	$user['value'] = $display_name;
        	$user['email'] = $user_array->user_email;
        	$user['phone'] = ( is_null( $user_array->client_phone ) ) ? '' : $user_array->client_phone;
        	$users[] = $user;
        }
    }
    echo json_encode( $users );
    wp_die();
}