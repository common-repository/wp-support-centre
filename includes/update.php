<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
global $wpdb;
$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
// define roles
// agent
// agent
if( !get_role( 'wpsc_agent' ) ) {
    add_role( 'wpsc_agent', 'Support Centre Agent' );
    $role = get_role( 'wpsc_agent' );
    $role->add_cap( 'manage_wpsc_ticket' );
    $role->add_cap( 'read' );
	$role->add_cap( 'upload_files' );
    $role = get_role( 'administrator' );
    $role->add_cap( 'manage_wpsc_ticket' );
} else {
	$role = get_role( 'wpsc_agent' );
	$capabilities = $role->capabilities;
	if ( !isset( $capabilities['upload_files'] ) ) {
		$role->add_cap( 'upload_files' );
	}
}
// supervisor
if( !get_role( 'wpsc_supervisor' ) ) {
    add_role( 'wpsc_supervisor', 'Support Centre Supervisor' );
    $role = get_role( 'wpsc_supervisor' );
    $role->add_cap( 'manage_wpsc_ticket' );
    $role->add_cap( 'manage_wpsc_agent' );
    $role->add_cap( 'read' );
	$role->add_cap( 'upload_files' );
    $role = get_role( 'administrator' );
    $role->add_cap( 'manage_wpsc_agent' );
} else {
	$role = get_role( 'wpsc_supervisor' );
	$capabilities = $role->capabilities;
	if ( !isset( $capabilities['upload_files'] ) ) {
		$role->add_cap( 'upload_files' );
	}
}
// configure database
// wpsc_tickets
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_tickets (
        id int(11) NOT NULL AUTO_INCREMENT,
        status_id int(11) NOT NULL,
        subject varchar(255) NOT NULL,
        client_id int(11) NOT NULL,
        client varchar(255) NOT NULL,
        client_email varchar(255) NOT NULL,
        client_phone varchar(255) NOT NULL,
        category_id int(11) NOT NULL,
        agent_id int(11) NOT NULL,
        priority_id int(11) NOT NULL,
        shared_users varchar(255) NOT NULL,
        created_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        updated_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        updated_by int(11) NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
    ";
dbDelta( $create );
// wpsc_threads
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_threads (
        id int(11) NOT NULL AUTO_INCREMENT,
        ticket_id int(11) NOT NULL,
        message longtext NOT NULL,
        attachments varchar(255) NOT NULL,
        author_id int(11) NOT NULL,
        author varchar(255) NOT NULL,
        author_email varchar(255) NOT NULL,
        to_email longtext,
        cc_email longtext,
        bcc_email longtext,
        is_private int(11) NOT NULL DEFAULT '0',
        is_pinned int(11) NOT NULL DEFAULT '0',
        notification int(11) NOT NULL DEFAULT '0',
        thread_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
";
dbDelta( $create );
$sql = 'ALTER TABLE ' . $wpdb->prefix . 'wpsc_threads CHANGE to_email to_email LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;';
$wpdb->query( $sql );
$sql = 'ALTER TABLE ' . $wpdb->prefix . 'wpsc_threads CHANGE cc_email cc_email LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;';
$wpdb->query( $sql );
$sql = 'ALTER TABLE ' . $wpdb->prefix . 'wpsc_threads CHANGE bcc_email bcc_email LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;';
$wpdb->query( $sql );
// wpsc_additional_fields_meta
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_additional_fields_meta (
        id int(11) NOT NULL AUTO_INCREMENT,
        ticket_id int(11) NOT NULL,
        field_id varchar(255) NOT NULL,
        meta_value longtext NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
";
dbDelta( $create );
// wpsc_threads_read
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_threads_read (
        id int(11) NOT NULL AUTO_INCREMENT,
        thread_id int(11) NOT NULL,
        ip varchar(255) NOT NULL,
        read_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
";
dbDelta( $create );
// wpsc_reminders
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_reminders (
        id int(11) NOT NULL AUTO_INCREMENT,
        ticket_id int(11) NOT NULL,
        subject varchar(255) NOT NULL,
        reminder int(11) NOT NULL DEFAULT '0',
        due_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
";
dbDelta( $create );
// wpsc_piping_preview
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_piping_preview (
        id int(11) NOT NULL AUTO_INCREMENT,
        uid int(11) NOT NULL,
        subject varchar(255) NOT NULL,
        message longtext NOT NULL,
        attachments varchar(255) NOT NULL,
        author_id int(11) NOT NULL,
        author varchar(255) NOT NULL,
        author_email varchar(255) NOT NULL,
        thread_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
";
dbDelta( $create );
// wpsc_piping_preview
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_imap (
        id int(11) NOT NULL AUTO_INCREMENT,
        imap_server varchar(255) NOT NULL,
        imap_port varchar(255) NOT NULL,
        imap_argstring varchar(255) NOT NULL,
        imap_username varchar(255) NOT NULL,
        imap_password varchar(255) NOT NULL,
        imap_type int(11) NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
";
dbDelta( $create );
// wpsc_tickets_recurring
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_tickets_recurring (
        id int(11) NOT NULL AUTO_INCREMENT,
        client_id int(11) NOT NULL,
        subject varchar(255) NOT NULL,
        thread longtext NOT NULL,
        attachments varchar(255) NOT NULL,
        status_id int(11) NOT NULL,
        category_id int(11) NOT NULL,
        priority_id int(11) NOT NULL,
        agent_id int(11) NOT NULL,
        enabled int(11) NOT NULL DEFAULT '1',
        notify int(11) NOT NULL DEFAULT '1',
        schedule int(11) NOT NULL,
        start_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        next_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
";
dbDelta( $create );
// wpsc_status
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_status (
        id int(11) NOT NULL AUTO_INCREMENT,
        status varchar(255) NOT NULL,
        status_prefix varchar(255) NOT NULL,
        colour varchar(10) NOT NULL,
        custom int(11) NOT NULL DEFAULT '1',
        enabled int(11) NOT NULL DEFAULT '1',
        is_default int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
    ";
dbDelta( $create );
$status_array = array(
    array(
        'id' => 1,
        'status' => 'Open',
        'status_prefix' => '',
        'colour' => '#008000',
        'custom' => 0,
        'enabled' => 1
    ),
    array(
        'id' => 2,
        'status' => 'Closed',
        'status_prefix' => '',
        'colour' => '#FF0000',
        'custom' => 0,
        'enabled' => 1
    ),
    array(
        'id' => 3,
        'status' => 'Deleted',
        'status_prefix' => '',
        'colour' => '#FFA500',
        'custom' => 0,
        'enabled' => 1
    ),
    array(
        'id' => 4,
        'status' => 'Pending',
        'status_prefix' => '',
        'colour' => '#FFFF00',
        'custom' => 0,
        'enabled' => 1
    ),
    array(
        'id' => 5,
        'status' => 'Reply Received',
        'status_prefix' => '',
        'colour' => '#0000FF',
        'custom' => 0,
        'enabled' => 1
    )
);
$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_status WHERE id=5";
$result = $wpdb->get_row( $sql );
if ( $result !== NULL ) {
    if ( $result->status != 'Reply Received' ) {
        $table = $wpdb->prefix . 'wpsc_status';
        $data = array(
            'status' => $result->status,
            'status_prefix' => $result->status_prefix,
            'colour' => $result->colour,
            'custom' => $result->custom,
            'enabled' => $result->enabled,
            'is_default' => $result->is_default
        );
        $format = array(
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d'
        );
        $wpdb->insert( $table, $data, $format );
        $table = $wpdb->prefix . 'wpsc_status';
        $data = array(
            'is_default' => 0
        );
        $where = array(
            'id' => 5
        );
        $format = array(
            '%d'
        );
        $where_format = array(
            '%d'
        );
        $wpdb->update( $table, $data, $where, $format, $where_format );
    }
}
foreach( $status_array as $status ) {
    $table = $wpdb->prefix . 'wpsc_status';
    $data = array(
        'id' => $status['id'],
        'status' => $status['status'],
        'status_prefix' => $status['status_prefix'],
        'colour' => $status['colour'],
        'custom' => $status['custom'],
        'enabled' => $status['enabled']
    );
    $format = array(
        '%d',
        '%s',
        '%s',
        '%s',
        '%d',
        '%d'
    );
    $wpdb->replace( $table, $data, $format );
}
$sql = "SELECT is_default FROM " . $wpdb->prefix . "wpsc_status WHERE is_default=1";
$check = $wpdb->get_results( $sql );
if ( $wpdb->num_rows != 1 ) {
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_status SET is_default=0";
    $wpdb->query( $sql );
    $table = $wpdb->prefix . 'wpsc_status';
    $data = array(
        'is_default' => 1
    );
    $where = array(
        'id' => 1
    );
    $format = array(
        '%d'
    );
    $where_format = array(
        '%d'
    );
    $wpdb->update( $table, $data, $where, $format, $where_format );
}
// wpsc_categories
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_categories (
        id int(11) NOT NULL AUTO_INCREMENT,
        category varchar(255) NOT NULL,
        custom int(11) NOT NULL DEFAULT '1',
        enabled int(11) NOT NULL DEFAULT '1',
        is_default int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
    ";
dbDelta( $create );
$categories_array = array(
    array(
        'id' => 1,
        'category' => 'General',
        'custom' => 0,
        'enabled' => 1,
        'is_default' => 1
    )
);
foreach( $categories_array as $category ) {
    $table = $wpdb->prefix . 'wpsc_categories';
    $data = array(
        'id' => $category['id'],
        'category' => $category['category'],
        'custom' => $category['custom'],
        'enabled' => $category['enabled']
    );
    $format = array(
        '%d',
        '%s',
        '%d',
        '%d'
    );
    $wpdb->replace( $table, $data, $format );
}
$sql = "SELECT is_default FROM " . $wpdb->prefix . "wpsc_categories WHERE is_default=1";
$check = $wpdb->get_results( $sql );
if ( $wpdb->num_rows != 1 ) {
    $sql = "UPDATE " . $wpdb->prefix . "wpsc_categories SET is_default=0";
    $wpdb->query( $sql );
    $table = $wpdb->prefix . 'wpsc_categories';
    $data = array(
        'is_default' => 1
    );
    $where = array(
        'id' => 1
    );
    $format = array(
        '%d'
    );
    $where_format = array(
        '%d'
    );
    $wpdb->update( $table, $data, $where, $format, $where_format );
}
// wpsc_priority
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_priority (
        id int(11) NOT NULL AUTO_INCREMENT,
        priority varchar(255) NOT NULL,
        priority_sla int(11) NOT NULL DEFAULT '0',
        colour varchar(10) NOT NULL,
        custom int(11) NOT NULL DEFAULT '1',
        enabled int(11) NOT NULL DEFAULT '1',
        is_default int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
    ";
dbDelta( $create );
$priority_array = array(
    array(
        'id' => 1,
        'priority' => 'Low',
        'custom' => 0,
        'enabled' => 1,
        'colour' => '#FFFF00',
        'is_default' => 0,
        'priority_sla' => 0
    ),
    array(
        'id' => 2,
        'priority' => 'Normal',
        'custom' => 0,
        'enabled' => 1,
        'colour' => '#008000',
        'is_default' => 1,
        'priority_sla' => 0
    ),
    array(
        'id' => 3,
        'priority' => 'High',
        'custom' => 0,
        'enabled' => 1,
        'colour' => '#FF0000',
        'is_default' => 0,
        'priority_sla' => 0
    )
);
foreach( $priority_array as $priority ) {
	$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority WHERE id=" . $priority['id'];
	$check = $wpdb->get_row( $sql );
	if ( null !== $check ) {
		$table = $wpdb->prefix . 'wpsc_priority';
		$data = array(
	        'id' => $priority['id'],
	        'priority' => $priority['priority'],
	        'custom' => $priority['custom'],
	        'enabled' => $priority['enabled'],
	        'colour' => $priority['colour'],
	        'is_default' => $check->is_default,
	        'priority_sla' => $check->priority_sla
	    );
	    $format = array(
	        '%d',
	        '%s',
	        '%d',
	        '%d',
	        '%s',
	        '%d',
	        '%d'
	    );
	    $wpdb->replace( $table, $data, $format );
	} else {
		$table = $wpdb->prefix . 'wpsc_priority';
		$data = array(
	        'id' => $priority['id'],
	        'priority' => $priority['priority'],
	        'custom' => $priority['custom'],
	        'enabled' => $priority['enabled'],
	        'colour' => $priority['colour'],
	        'is_default' => $priority['is_default'],
	        'priority_sla' => $priority['priority_sla']
	    );
	    $format = array(
	        '%d',
	        '%s',
	        '%d',
	        '%d',
	        '%s',
	        '%d',
	        '%d'
	    );
	    $wpdb->replace( $table, $data, $format );
	}
}
// wpsc_templates
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_templates (
        id int(11) NOT NULL AUTO_INCREMENT,
        label varchar(255) NOT NULL,
        template longtext NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
    ";
dbDelta( $create );
// wpsc_settings
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_settings (
        id int(11) NOT NULL AUTO_INCREMENT,
        signature longtext NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";
dbDelta( $create );
$check = "SELECT * FROM " . $wpdb->prefix . "wpsc_settings";
$results = $wpdb->get_results( $check );
if( $wpdb->num_rows == 0 ) {
    $table = $wpdb->prefix . 'wpsc_settings';
    $data = array(
        'id' => 1,
        'signature' => ''
    );
    $format = array(
        '%d',
        '%s'
    );
    $wpdb->replace( $table, $data, $format );
}
// wpsc_account
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_account (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL,
        content longtext NOT NULL,
        attachments varchar(255) NOT NULL,
        created_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        updated_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
        updated_by int(11) NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
    ";
dbDelta( $create );
// wpsc_notifications
$create = "
    CREATE TABLE " . $wpdb->prefix . "wpsc_notifications (
        id int(11) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        notification longtext NOT NULL,
        default_notification longtext NOT NULL,
        PRIMARY KEY  (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";
dbDelta( $create );
if ( !isset( $wpsc_options['wpsc_notifications_firstrun'] ) || $wpsc_options['wpsc_notifications_firstrun'] != 'false' ) {
    $find = array(
        '[wpsc_plugin_url]'
    );
    $replace = array(
        WPSC_PLUGIN_URL
    );
    $table = $wpdb->prefix . 'wpsc_notifications';

    $notification_ticket_new_admin = '';
    $notification_ticket_new_admin_default = '';
    $notification_ticket_new_client = '';
    $notification_ticket_new_client_default = '';
    $notification_ticket_reply_admin = '';
    $notification_ticket_reply_admin_default = '';
    $notification_ticket_reply_client = '';
    $notification_ticket_reply_client_default = '';
    $notification_ticket_change_admin = '';
    $notification_ticket_change_admin_default = '';
    $notification_ticket_change_client = '';
    $notification_ticket_change_client_default = '';

    $check = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_new_admin'" );
    if ( $wpdb->num_rows == 0 ) {
        if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_new_admin.tpl' ) ) {
            $notification_ticket_new_admin = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_new_admin.tpl' ) ) );
        } else if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_new_admin_default.tpl' ) ) {
            $notification_ticket_new_admin_default = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_new_admin_default.tpl' ) ) );
            $notification_ticket_new_admin = ( $notification_ticket_new_admin == '' ) ? $notification_ticket_new_admin_default : $notification_ticket_new_admin;
        }
        $data = array(
            'title' => 'notification_ticket_new_admin',
            'notification' => $notification_ticket_new_admin,
            'default_notification' => $notification_ticket_new_admin_default
        );
        $format = array(
            '%s',
            '%s',
            '%s'
        );
        $wpdb->insert( $table, $data, $format );
    }

    $check = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_new_client'" );
    if ( $wpdb->num_rows == 0 ) {
        if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_new_client.tpl' ) ) {
            $notification_ticket_new_client = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_new_client.tpl' ) ) );
        } else if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_new_client_default.tpl' ) ) {
            $notification_ticket_new_client_default = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_new_client_default.tpl' ) ) );
            $notification_ticket_new_client = ( $notification_ticket_new_client == '' ) ? $notification_ticket_new_client_default : $notification_ticket_new_client;
        }
        $data = array(
            'title' => 'notification_ticket_new_client',
            'notification' => $notification_ticket_new_client,
            'default_notification' => $notification_ticket_new_client_default
        );
        $format = array(
            '%s',
            '%s',
            '%s'
        );
        $wpdb->insert( $table, $data, $format );
    }

    $check = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_admin'" );
    if ( $wpdb->num_rows == 0 ) {
        if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_reply_admin.tpl' ) ) {
            $notification_ticket_reply_admin = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_reply_admin.tpl' ) ) );
        } else if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_reply_admin_default.tpl' ) ) {
            $notification_ticket_reply_admin_default = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_reply_admin_default.tpl' ) ) );
            $notification_ticket_reply_admin = ( $notification_ticket_reply_admin == '' ) ? $notification_ticket_reply_admin_default : $notification_ticket_reply_admin;
        }
        $data = array(
            'title' => 'notification_ticket_reply_admin',
            'notification' => $notification_ticket_reply_admin,
            'default_notification' => $notification_ticket_reply_admin_default
        );
        $format = array(
            '%s',
            '%s',
            '%s'
        );
        $wpdb->insert( $table, $data, $format );
    }

    $check = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_reply_client'" );
    if ( $wpdb->num_rows == 0 ) {
        if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_reply_client.tpl' ) ) {
            $notification_ticket_reply_client = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_reply_client.tpl' ) ) );
        } else if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_reply_client_default.tpl' ) ) {
            $notification_ticket_reply_client_default = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_reply_client_default.tpl' ) ) );
            $notification_ticket_reply_client = ( $notification_ticket_reply_client == '' ) ? $notification_ticket_reply_client_default : $notification_ticket_reply_client;
        }
        $data = array(
            'title' => 'notification_ticket_reply_client',
            'notification' => $notification_ticket_reply_client,
            'default_notification' => $notification_ticket_reply_client_default
        );
        $format = array(
            '%s',
            '%s',
            '%s'
        );
        $wpdb->insert( $table, $data, $format );
    }

    $check = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_change_admin'" );
    if ( $wpdb->num_rows == 0 ) {
        if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_change_admin.tpl' ) ) {
            $notification_ticket_change_admin = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_change_admin.tpl' ) ) );
        } else if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_change_admin_default.tpl' ) ) {
            $notification_ticket_change_admin_default = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_change_admin_default.tpl' ) ) );
            $notification_ticket_change_admin = ( $notification_ticket_change_admin == '' ) ? $notification_ticket_change_admin_default : $notification_ticket_change_admin;
        }
        $data = array(
            'title' => 'notification_ticket_change_admin',
            'notification' => $notification_ticket_change_admin,
            'default_notification' => $notification_ticket_change_admin_default
        );
        $format = array(
            '%s',
            '%s',
            '%s'
        );
        $wpdb->insert( $table, $data, $format );
    }

    $check = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "wpsc_notifications WHERE title='notification_ticket_change_client'" );
    if ( $wpdb->num_rows == 0 ) {
        if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_change_client.tpl' ) ) {
            $notification_ticket_change_client = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_change_client.tpl' ) ) );
        } else if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_change_client_default.tpl' ) ) {
            $notification_ticket_change_client_default = str_replace( $find, $replace, wp_remote_retrieve_body( wp_remote_get( WPSC_PLUGIN_URL . 'includes/templates/notification_ticket_change_client_default.tpl' ) ) );
            $notification_ticket_change_client = ( $notification_ticket_change_client == '' ) ? $notification_ticket_change_client_default : $notification_ticket_change_client;
        }
        $data = array(
            'title' => 'notification_ticket_change_client',
            'notification' => $notification_ticket_change_client,
            'default_notification' => $notification_ticket_change_client_default
        );
        $format = array(
            '%s',
            '%s',
            '%s'
        );
        $wpdb->insert( $table, $data, $format );
    }

    if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_new_admin.tpl' ) ) { unlink( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_new_admin.tpl' ); }
    if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_new_client.tpl' ) ) { unlink( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_new_client.tpl' ); }
    if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_reply_admin.tpl' ) ) { unlink( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_reply_admin.tpl' ); }
    if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_reply_client.tpl' ) ) { unlink( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_reply_client.tpl' ); }
    if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_change_admin.tpl' ) ) { unlink( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_change_admin.tpl' ); }
    if ( file_exists( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_change_client.tpl' ) ) { unlink( WPSC_PLUGIN_DIR . 'includes/templates/notification_ticket_change_client.tpl' ); }

    $wpsc_options['wpsc_notifications_firstrun'] = 'false';

}

// set options
// general
$wpsc_options['wpsc_plugin_url'] = network_site_url();
$wpsc_options['wpsc_blog_id'] = get_current_blog_id();
$wpsc_options['wpsc_item'] = ( !isset( $wpsc_options['wpsc_item'] ) || $wpsc_options['wpsc_item'] == '' ) ? 'Ticket' : $wpsc_options['wpsc_item'];
$wpsc_options['wpsc_client'] = ( !isset( $wpsc_options['wpsc_client'] ) || $wpsc_options['wpsc_client'] == '' ) ? 'Client' : $wpsc_options['wpsc_client'];
$wpsc_options['wpsc_support_page'] = ( !isset( $wpsc_options['wpsc_support_page'] ) || $wpsc_options['wpsc_support_page'] == '' ) ? '0' : $wpsc_options['wpsc_support_page'];
$wpsc_options['wpsc_thanks_page'] = ( !isset( $wpsc_options['wpsc_thanks_page'] ) || $wpsc_options['wpsc_thanks_page'] == '' ) ? '0' : $wpsc_options['wpsc_thanks_page'];
$wpsc_options['wpsc_seperator'] = '-------- Reply Above --------';
// agent
$wpsc_options['wpsc_default_agent'] = ( !isset( $wpsc_options['wpsc_default_agent'] ) || $wpsc_options['wpsc_default_agent'] == '' ) ? 's' : $wpsc_options['wpsc_default_agent'];
// email
$wpsc_options['wpsc_email_from_name'] = ( !isset( $wpsc_options['wpsc_email_from_name'] ) || $wpsc_options['wpsc_email_from_name'] == '' ) ? get_option( 'blogname' ) : $wpsc_options['wpsc_email_from_name'];
$wpsc_options['wpsc_email_from_email'] = ( !isset( $wpsc_options['wpsc_email_from_email'] ) || $wpsc_options['wpsc_email_from_email'] == '' ) ? get_option( 'admin_email' ) : $wpsc_options['wpsc_email_from_email'];
$wpsc_options['wpsc_email_reply_to'] = ( !isset( $wpsc_options['wpsc_email_reply_to'] ) || $wpsc_options['wpsc_email_reply_to'] == '' ) ? get_option( 'admin_email' ) : $wpsc_options['wpsc_email_reply_to'];
$wpsc_options['wpsc_use_agent_email'] = ( !isset( $wpsc_options['wpsc_use_agent_email'] ) || $wpsc_options['wpsc_use_agent_email'] == '' ) ? 0 : $wpsc_options['wpsc_use_agent_email'];
$wpsc_options['wpsc_email_method'] = ( !isset( $wpsc_options['wpsc_email_method'] ) || $wpsc_options['wpsc_email_method'] == '' ) ? 0 : $wpsc_options['wpsc_email_method'];
$wpsc_options['wpsc_enable_email_piping'] = ( !isset( $wpsc_options['wpsc_enable_email_piping'] ) || $wpsc_options['wpsc_enable_email_piping'] == '' ) ? 0 : $wpsc_options['wpsc_enable_email_piping'];
$wpsc_options['wpsc_email_piping'] = ( !isset( $wpsc_options['wpsc_email_piping'] ) || $wpsc_options['wpsc_email_piping'] == '' ) ? get_option( 'admin_email' ) : $wpsc_options['wpsc_email_piping'];
// signature
$wpsc_options['wpsc_signature_from_name'] = ( !isset( $wpsc_options['wpsc_signature_from_name'] ) || $wpsc_options['wpsc_signature_from_name'] == '' ) ? '' : $wpsc_options['wpsc_signature_from_name'];
$wpsc_options['wpsc_signature_from_email'] = ( !isset( $wpsc_options['wpsc_signature_from_email'] ) || $wpsc_options['wpsc_signature_from_email'] == '' ) ? '' : $wpsc_options['wpsc_signature_from_email'];
$wpsc_options['wpsc_signature_reply_to'] = ( !isset( $wpsc_options['wpsc_signature_reply_to'] ) || $wpsc_options['wpsc_signature_reply_to'] == '' ) ? '' : $wpsc_options['wpsc_signature_reply_to'];
// misc
$wpsc_options['wpsc_load_bootstrap_js_f'] = ( !isset( $wpsc_options['wpsc_load_bootstrap_js_f'] ) || $wpsc_options['wpsc_load_bootstrap_js_f'] == '' ) ? '1' : $wpsc_options['wpsc_load_bootstrap_js_f'];
$wpsc_options['wpsc_load_bootstrap_js_a'] = ( !isset( $wpsc_options['wpsc_load_bootstrap_js_a'] ) || $wpsc_options['wpsc_load_bootstrap_js_a'] == '' ) ? '1' : $wpsc_options['wpsc_load_bootstrap_js_a'];
$wpsc_options['wpsc_load_bootstrap_css_f'] = ( !isset( $wpsc_options['wpsc_load_bootstrap_css_f'] ) || $wpsc_options['wpsc_load_bootstrap_css_f'] == '' ) ? '1' : $wpsc_options['wpsc_load_bootstrap_css_f'];
$wpsc_options['wpsc_load_bootstrap_css_a'] = ( !isset( $wpsc_options['wpsc_load_bootstrap_css_a'] ) || $wpsc_options['wpsc_load_bootstrap_css_a'] == '' ) ? '1' : $wpsc_options['wpsc_load_bootstrap_css_a'];
if ( isset( $_GET['wpsc_ebs'] ) && $_GET['wpsc_ebs'] == 'true' ) {
	$wpsc_options['wpsc_load_bootstrap_js_f'] = '1';
	$wpsc_options['wpsc_load_bootstrap_js_a'] = '1';
	$wpsc_options['wpsc_load_bootstrap_css_f'] = '1';
	$wpsc_options['wpsc_load_bootstrap_css_a'] = '1';
}
// notifications
$wpsc_options['wpsc_notification_ticket_new_client_enable'] = ( !isset( $wpsc_options['wpsc_notification_ticket_new_client_enable'] ) || $wpsc_options['wpsc_notification_ticket_new_client_enable'] == '' ) ? '1' : $wpsc_options['wpsc_notification_ticket_new_client_enable'];
$wpsc_options['wpsc_notification_ticket_new_admin_enable'] = ( !isset( $wpsc_options['wpsc_notification_ticket_new_admin_enable'] ) || $wpsc_options['wpsc_notification_ticket_new_admin_enable'] == '' ) ? '1' : $wpsc_options['wpsc_notification_ticket_new_admin_enable'];
$wpsc_options['wpsc_notification_ticket_reply_client_enable'] = ( !isset( $wpsc_options['wpsc_notification_ticket_reply_client_enable'] ) || $wpsc_options['wpsc_notification_ticket_reply_client_enable'] == '' ) ? '1' : $wpsc_options['wpsc_notification_ticket_reply_client_enable'];
$wpsc_options['wpsc_notification_ticket_reply_admin_enable'] = ( !isset( $wpsc_options['wpsc_notification_ticket_reply_admin_enable'] ) || $wpsc_options['wpsc_notification_ticket_reply_admin_enable'] == '' ) ? '1' : $wpsc_options['wpsc_notification_ticket_reply_admin_enable'];
$wpsc_options['wpsc_notification_ticket_change_client_enable'] = ( !isset( $wpsc_options['wpsc_notification_ticket_change_client_enable'] ) || $wpsc_options['wpsc_notification_ticket_change_client_enable'] == '' ) ? '1' : $wpsc_options['wpsc_notification_ticket_change_client_enable'];
$wpsc_options['wpsc_notification_ticket_change_admin_enable'] = ( !isset( $wpsc_options['wpsc_notification_ticket_change_admin_enable'] ) || $wpsc_options['wpsc_notification_ticket_change_admin_enable'] == '' ) ? '1' : $wpsc_options['wpsc_notification_ticket_change_admin_enable'];
$wpsc_options['wpsc_reply_include'] = ( !isset( $wpsc_options['wpsc_reply_include'] ) || $wpsc_options['wpsc_reply_include'] == '' ) ? '0' : $wpsc_options['wpsc_reply_include'];
// recurring tickets
$wpsc_options['wpsc_recurring_tickets_scheduled_time'] = ( !isset( $wpsc_options['wpsc_recurring_tickets_scheduled_time'] ) || $wpsc_options['wpsc_recurring_tickets_scheduled_time'] == '' ) ? '06:00:00' : $wpsc_options['wpsc_recurring_tickets_scheduled_time'];
$wpsc_options['wpsc_recurring_tickets_last_run'] = ( !isset( $wpsc_options['wpsc_recurring_tickets_last_run'] ) || $wpsc_options['wpsc_recurring_tickets_last_run'] == '' ) ? strtotime( date( "Y-m-d", current_time( 'timestamp' ) ) ) - ( 60 * 60 * 24 ) : $wpsc_options['wpsc_recurring_tickets_last_run'];
// set email piping settings
$url = network_site_url();
$site_url = preg_replace( "(^https?://)", "", $url );
$site_url = str_replace( '/', '', $site_url );
$blog_id = get_current_blog_id();
file_put_contents( WPSC_PLUGIN_DIR . '/piping/http_host.dat', $site_url );
file_put_contents( WPSC_PLUGIN_DIR . '/piping/blog_id.dat', $blog_id );
// check for cron for recurring tickets and add if not found
if ( !wp_next_scheduled( 'wpsc_recurring_tickets' ) ) {
    wp_schedule_event( time(), 'twomin', 'wpsc_recurring_tickets' );
} else {
	$schedule = wp_get_schedule( 'wpsc_recurring_tickets' );
	if ( $schedule != 'twomin' ) {
		wp_clear_scheduled_hook( 'wpsc_recurring_tickets' );
		wp_schedule_event( time(), 'twomin', 'wpsc_recurring_tickets' );
	}
}
if ( !wp_next_scheduled( 'wpsc_assign_threads' ) ) {
    wp_schedule_event( time(), 'hourly', 'wpsc_assign_threads' );
}
if ( !wp_next_scheduled( 'wpsc_search_fallback' ) ) {
    wp_schedule_event( time(), 'hourly', 'wpsc_search_fallback' );
}
if ( !wp_next_scheduled( 'wpsc_clean_attachments' ) ) {
    wp_schedule_event( time(), 'twicedaily', 'wpsc_clean_attachments' );
}
if ( !wp_next_scheduled( 'wpsc_check_imap' ) ) {
    wp_schedule_event( time(), 'twomin', 'wpsc_check_imap' );
}
// update plugin database version
$wpsc_options['wpsc_version'] = WPSC_VERSION;
// save options
unset( $wpsc_options['wpsc_new_note_update_ticket_sql'] );
$wpsc_support_page = $wpsc_options['wpsc_support_page'];
if ( !empty( $wpsc_support_page ) && $wpsc_support_page != 0 ) {
	$wpsc_support_page_url = get_permalink( $wpsc_support_page );
	$wpsc_options['wpsc_support_page_url'] = $wpsc_support_page_url;
} else {
	$wpsc_options['wpsc_support_page_url'] = ( !isset( $wpsc_options['wpsc_support_page_url'] ) || $wpsc_options['wpsc_support_page_url'] == '' ) ? '' : $wpsc_options['wpsc_support_page_url'];
}
$base64_convert = get_option( 'wpsc_base64_convert' );
if ( false === $base64_convert || (false !== $base64_convert && $base64_convert < 1 ) ) {
	$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_threads";
	$threads = $wpdb->get_results( $sql );
	if ( ( is_array( $threads ) && !empty( $threads) ) && !is_null( $threads ) ) {
		foreach ( $threads as $thread ) {
			$message = $thread->message;
			$message = base64_encode( $message );
			$sql = "UPDATE " . $wpdb->prefix . "wpsc_threads SET message='" . $message . "' WHERE id=" . $thread->id;
			$update = $wpdb->query( $sql );
		}
	}
	update_option( 'wpsc_base64_convert', 1 );
}
update_option( 'wpsc_options', $wpsc_options );
if ( isset( $_GET['wpsc_debug'] ) ) {
    if ( function_exists( 'get_blog_details' ) ) {
        $site = get_blog_details();
        $site->wpsc = $wpsc_options;
		$server = print_r( $_SERVER , true );
		$site->server = $server;
		$cron = get_option( 'cron' );
		$cron = print_r( $cron, true );
		$site->cron = $cron;
		$db = print_r( $wpdb, true );
		$site->wpdb = $db;
    } else {
        $show = array(
            'name',
            'description',
            'wpurl',
            'url',
            'admin_email',
            'charset',
            'version',
            'html_type',
            'text_direction',
            'language',
            'stylesheet_url',
            'stylesheet_directory',
            'template_url',
            'pingback_url',
            'atom_url',
            'rdf_url',
            'rss_url',
            'rss2_url',
            'comments_atom_url',
            'comments_rss2_url'
        );
        $site = array();
        foreach ( $show as $key ) {
        	if ( $key == 'text_direction' ) {
        		$site[$key] = is_rtl();
        	} else {
        		$site[$key] = get_bloginfo( $key );
        	}
        }
        $site['wpsc'] = $wpsc_options;
		$server = print_r( $_SERVER , true );
		$site['server'] = $server;
		$cron = get_option( 'cron' );
		$cron = print_r( $cron, true );
		$site['cron'] = $cron;
		$db = print_r( $wpdb, true );
		$site['wpdb'] = $db;
    }
    $site = print_r( $site, true );
	if ( isset( $_GET['wpsc_report'] ) && $_GET['wpsc_report'] == 'true' ) {
		error_log( $site, 1, 'test@cloughit.com.au' );
	}
}