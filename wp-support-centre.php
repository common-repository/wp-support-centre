<?php
/**
 * Plugin Name: WP Support Centre
 * Description: WordPress Support Centre Ticketing System
 * Author:      Clough I.T. Solutions
 * Author URI:  https://cloughit.com.au
 * Version:     2017.12.02
 * Text Domain: wp-support-centre
 * License:     GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !function_exists( 'flattenParts' ) ) {
    function flattenParts( $messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true ) {
        foreach( $messageParts as $part ) {
            $flattenedParts[$prefix . $index] = $part;
            if( isset( $part->parts ) ) {
                if( $part->type == 2 ) {
                    //$flattenedParts = flattenParts( $part->parts, $flattenedParts, $prefix . $index . '.', 0, false );
                } else if ( $fullPrefix ) {
                    $flattenedParts = flattenParts( $part->parts, $flattenedParts, $prefix . $index . '.' );
                } else {
                    $flattenedParts = flattenParts( $part->parts, $flattenedParts, $prefix );
                }
                unset( $flattenedParts[$prefix.$index]->parts );
            }
            $index++;
        }
        return $flattenedParts;
    }
}

if ( !function_exists( 'getPart' ) ) {
    function getPart( $connection, $messageNumber, $partNumber, $encoding ) {
        $data = imap_fetchbody( $connection, $messageNumber, $partNumber );
        switch( $encoding ) {
            case 0: return imap_qprint( $data ); // 7BIT
            case 1: return quoted_printable_decode( $data ); // 8BIT
            case 2: return imap_binary( $data ); // BINARY
            case 3: return base64_decode( $data ); // BASE64
            case 4: return quoted_printable_decode( $data ); // QUOTED_PRINTABLE
            case 5: return $data; // OTHER
            default: return $data;
        }
    }
}

if ( !function_exists( 'getFilenameFromPart' ) ) {
    function getFilenameFromPart( $part ) {
        $filename = '';
        if( $part->ifdparameters ) {
            foreach( $part->dparameters as $object ) {
                if ( strtolower( $object->attribute ) == 'filename' ) {
                    $filename = $object->value;
                }
            }
        }
        if ( !$filename && $part->ifparameters ) {
            foreach ( $part->parameters as $object ) {
                if ( strtolower( $object->attribute ) == 'name' ) {
                    $filename = $object->value;
                }
            }
        }
        return $filename;
    }
}

if ( !function_exists( 'getImageID' ) ) {
    function getImageID( $url ) {
        global $wpdb;
        $attachment = $wpdb->get_var( "SELECT ID FROM " . $wpdb->posts . " WHERE guid='" . $url . "'" );
        return $attachment;
    }
}

if ( !function_exists( 'get_string_between' ) ) {
    function get_string_between( $string, $start, $end ) {
        $string = ' ' . $string;
        $ini = strpos( $string, $start );
        if ( $ini == 0 ) return '';
        $ini += strlen( $start );
        $len = strpos( $string, $end, $ini ) - $ini;
        return substr( $string, $ini, $len );
    }
}

$pages = array(
    'wp-support-centre',
    'wpsc_admin_recurring',
    'wpsc_admin_mailbox',
    'wpsc_admin_settings',
    'wpsc_admin_agent_client',
    'wpsc_admin_notifications',
    'wpsc_admin_templates',
    'wpsc_admin_utilities'
);
$is_page = ( isset( $_GET['page'] ) && in_array( $_GET['page'], $pages ) ) ? true : false;

register_activation_hook( __FILE__, array( 'wpSupportCentre', 'wpsc_activate' ) );
register_deactivation_hook( __FILE__, array( 'wpSupportCentre', 'wpsc_deactivate' ) );
register_uninstall_hook( __FILE__, array( 'wpSupportCentre', 'wpsc_uninstall' ) );

add_action( 'wp_loaded', array( wpSupportCentre::get_instance(), 'init_plugin' ) );
if ( is_admin() && $is_page ) {
    add_action( 'admin_enqueue_scripts', array( wpSupportCentre::get_instance(), 'wpsc_enqueue_assets' ) );
	add_action( 'admin_notices', array( wpSupportCentre::get_instance(), 'wpsc_bootstrap_alert' ) );
	add_action( 'admin_notices', array( wpSupportCentre::get_instance(), 'wpsc_discontinued_alert' ) );
	add_filter( 'media_upload_tabs', array( wpSupportCentre::get_instance(), 'wpsc_media_upload_tabs' ) );
} else {
    add_action( 'wp_enqueue_scripts', array( wpSupportCentre::get_instance(), 'wpsc_enqueue_assets' ) );
}

function add_viewport() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
}

function wpsc_cookies() {
	$tickets = ( isset( $_COOKIE['wpsc_open_tickets'] ) && $_COOKIE['wpsc_open_tickets'] != '' ) ? explode( ',', $_COOKIE['wpsc_open_tickets'] ) : array();
	if ( isset( $_REQUEST['ticket_id'] ) && $_REQUEST['ticket_id'] != '' ) {
	    if ( !in_array( $_REQUEST['ticket_id'], $tickets ) ) {
	        $tickets[] = $_REQUEST['ticket_id'];
	        setcookie( 'wpsc_open_tickets', implode(',', $tickets ), time() + ( 30 * 86400 ) );
	        $_COOKIE['wpsc_open_tickets'] = implode(',', $tickets );
	        setcookie( 'wpsc_active_ticket', $_REQUEST['ticket_id'], time() + ( 30 * 86400 ) );
	        $_COOKIE['wpsc_active_ticket'] = implode(',', $tickets );
	    }
	}
}
add_action( 'init', 'wpsc_cookies' );

class wpSupportCentre {

    function __construct() {
    	$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
		$wpsc_title = ( isset( $wpsc_options['wpsc_rename'] ) && !empty( $wpsc_options['wpsc_rename'] ) ) ? $wpsc_options['wpsc_rename'] : 'Support Centre';
        define( 'WPSC_TITLE', $wpsc_title );
        define( 'WPSC_ADMIN_CAPABILITY', 'manage_wpsc_ticket' );
        define( 'WPSC_ADMIN_MENU_SLUG', 'wp-support-centre' );
        define( 'WPSC_ADMIN_ICON_URL', plugin_dir_url( __FILE__ ) . 'assets/images/support-centre-16x16.png' );
        define( 'WPSC_ADMIN_POSITION', 3 );
        define( 'WPSC_ADMIN_URL', admin_url() );
        define( 'WPSC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        define( 'WPSC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        define( 'WPSC_REPLY_ABOVE', '-------- Reply Above --------' );
        define( 'WPSC_TICKET_URL', site_url() );
        define( 'WPSC_VERSION', '2017.12.02' );
        define( 'WPSC_JQUERY_VER', '1.12.3' );
        define( 'WPSC_JQUERYUI_VER', '1.11.4' );
    }

    /**
     * the plugin instance
     */
    private static $instance = NULL;

    /**
     * get the plugin instance
     *
     * @return wpSupportCentre
     */
    public static function get_instance() {

        if ( NULL === self::$instance )
            self::$instance = new self;

        return self::$instance;
    }

    /**
     * hook in
     *
     * @wp-hook plugins_loaded
     */
    public function init_plugin() {
    	global $wpdb;
    	include_once( WPSC_PLUGIN_DIR . '/includes/cron.php' );
        chmod( WPSC_PLUGIN_DIR . 'piping/piping.php', 0755 );
        chmod( WPSC_PLUGIN_DIR . 'piping/piping_preview.php', 0755 );
        // set email piping settings
        $url = network_site_url();
        $site_url = preg_replace( "(^https?://)", "", $url );
        $site_url = str_replace( '/', '', $site_url );
        $blog_id = get_current_blog_id();
        file_put_contents( WPSC_PLUGIN_DIR . '/piping/http_host.dat', $site_url );
        file_put_contents( WPSC_PLUGIN_DIR . '/piping/blog_id.dat', $blog_id );
        include_once( WPSC_PLUGIN_DIR . '/includes/functions.php' );
        include_once( WPSC_PLUGIN_DIR . '/ajax/_ajax-functions.php' );
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        $user_id = get_current_user_id();
		$cururl = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		$wpsc_support_page_url = $wpsc_options['wpsc_support_page_url'];
		$wpsc_support_page_url = str_replace( array( 'http://', 'https://' ), '', $wpsc_support_page_url );
        define( 'WPSC_JQUERYUI_THEME', ( isset( $wpsc_options['wpsc_jqueryui_theme'] ) && $wpsc_options['wpsc_jqueryui_theme'] != '') ? $wpsc_options['wpsc_jqueryui_theme'] : 'smoothness' );
		// image sizes
        add_image_size( 'wpsc_thumbnail', 64, 64 );
		// filters
		add_filter( 'wpsc_agent', array( $this, 'wpsc_agent_filter', 10, 2 ) );
		add_filter( 'wpsc_client', array( $this, 'wpsc_client_filter' ), 10, 2 );
		add_filter( 'wpsc_item', array( $this, 'wpsc_item_filter' ), 10, 2 );
		// set cron time for imap
		add_filter( 'cron_schedules', array( $this, 'wpsc_cron_schedules' ) );
        $this->maybe_update();
		// admin bar
		add_action( 'wp_before_admin_bar_render', array( $this, 'do_admin_bar' ) );
        $cururl = strtolower( $cururl );
		$turl = home_url( add_query_arg( array() ) );
		$tid = url_to_postid( $turl );
        $wpsc_support_page_url = strtolower( $wpsc_support_page_url );
        if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            // add filter to log update_option calls
            // enable this for debugging
            add_filter( 'pre_update_option_wpsc_options', array( $this, 'wpsc_update_option' ), 10, 2 );
			// admin notices
			add_action( 'admin_notices', array( $this, 'wpsc_notices' ) );
            // admin menu
            add_action( 'admin_menu', array( $this, 'wpsc_admin_menu' ) );
            // settings - general
            add_action( 'wpsc_ajax_wpsc_save_general', 'wpsc_save_general' );
            // settings - email
            add_action( 'wpsc_ajax_wpsc_save_email', 'wpsc_save_email' );
			add_action( 'wpsc_ajax_wpsc_delete_imap', 'wpsc_delete_imap' );
            // settings - agent
            add_action( 'wpsc_ajax_wpsc_save_agent_settings', 'wpsc_save_agent_settings' );
            add_action( 'wpsc_ajax_wpsc_get_user_data', 'wpsc_get_user_data' );
            // settings - status
            add_action( 'wpsc_ajax_wpsc_save_status_colour', 'wpsc_save_status_colour' );
            add_action( 'wpsc_ajax_wpsc_add_new_status', 'wpsc_add_new_status' );
            add_action( 'wpsc_ajax_wpsc_delete_status', 'wpsc_delete_status' );
            add_action( 'wpsc_ajax_wpsc_status_default', 'wpsc_status_default' );
            // settings - category
            add_action( 'wpsc_ajax_wpsc_add_new_category', 'wpsc_add_new_category' );
            add_action( 'wpsc_ajax_wpsc_delete_category', 'wpsc_delete_category' );
            add_action( 'wpsc_ajax_wpsc_category_default', 'wpsc_category_default' );
            // settings - priority
            add_action( 'wpsc_ajax_wpsc_save_priority_colour', 'wpsc_save_priority_colour' );
            add_action( 'wpsc_ajax_wpsc_add_new_priority', 'wpsc_add_new_priority' );
            add_action( 'wpsc_ajax_wpsc_delete_priority', 'wpsc_delete_priority' );
            add_action( 'wpsc_ajax_wpsc_priority_default', 'wpsc_priority_default' );
            // settings - misc
            add_action( 'wpsc_ajax_wpsc_save_misc', 'wpsc_save_misc' );
            // notifications - new ticket
            add_action( 'wpsc_ajax_wpsc_save_notifications_ticket_new', 'wpsc_save_notifications_ticket_new' );
            add_action( 'wpsc_ajax_wpsc_reset_notifications_ticket_new', 'wpsc_reset_notifications_ticket_new' );
            // notifications - reply ticket
            add_action( 'wpsc_ajax_wpsc_save_notifications_ticket_reply', 'wpsc_save_notifications_ticket_reply' );
            add_action( 'wpsc_ajax_wpsc_reset_notifications_ticket_reply', 'wpsc_reset_notifications_ticket_reply' );
            // notifications - change ticket
            add_action( 'wpsc_ajax_wpsc_save_notifications_ticket_change', 'wpsc_save_notifications_ticket_change' );
            add_action( 'wpsc_ajax_wpsc_reset_notifications_ticket_change', 'wpsc_reset_notifications_ticket_change' );
            // browser notifications
            add_action( 'wpsc_ajax_wpsc_get_notifications', 'wpsc_get_notifications' );
            // registered users search
            add_action( 'wpsc_ajax_wpsc_registered_users_search', 'wpsc_registered_users_search' );
            // tickets - create new
            add_action( 'wpsc_ajax_wpsc_admin_new_ticket_save', 'wpsc_admin_new_ticket_save' );
            // tickets - create new recurring
            add_action( 'wpsc_ajax_wpsc_new_recurring_ticket_save', 'wpsc_new_recurring_ticket_save' );
            // tickets - edit recurring
            add_action( 'wpsc_ajax_wpsc_edit_recurring_ticket_save', 'wpsc_edit_recurring_ticket_save' );
            // tickets - delete recurring
            add_action( 'wpsc_ajax_wpsc_delete_recurring_ticket', 'wpsc_delete_recurring_ticket' );
            // tickets - get ticket table
            add_action( 'wpsc_ajax_wpsc_doRefreshAdminTicketsTable', 'wpsc_doRefreshAdminTicketsTable' );
            // tickets - get ticket
            add_action( 'wpsc_ajax_wpsc_get_admin_ticket', 'wpsc_get_admin_ticket' );
            // tickets - get ticket
            add_action( 'wpsc_ajax_wpsc_account_save_changes', 'wpsc_account_save_changes' );
            // tickets - get recurring ticket
            add_action( 'wpsc_ajax_wpsc_get_recurring_ticket', 'wpsc_get_recurring_ticket' );
            // tickets - apply actions
            add_action( 'wpsc_ajax_wpsc_admin_apply_actions', 'wpsc_admin_apply_actions' );
            // tickets - apply recurring ticket actions
            add_action( 'wpsc_ajax_wpsc_admin_apply_recurring_actions', 'wpsc_admin_apply_recurring_actions' );
            // tickets - save changes
            add_action( 'wpsc_ajax_wpsc_ticket_save_changes', 'wpsc_ticket_save_changes' );
            // tickets - add note / add note and notify
            add_action( 'wpsc_ajax_wpsc_new_note', 'wpsc_new_note' );
            // tickets - create new ticket from thread
            add_action( 'wpsc_ajax_wpsc_new_ticket_from_thread', 'wpsc_new_ticket_from_thread' );
            // tickets - copy thread to ticket
            add_action( 'wpsc_ajax_wpsc_copy_thread_to_ticket', 'wpsc_copy_thread_to_ticket' );
            // tickets - resend thread notifications
            add_action( 'wpsc_ajax_wpsc_resend_thread_notifications', 'wpsc_resend_thread_notifications' );
            // tickets - delete attachment
            add_action( 'wpsc_ajax_wpsc_delete_attachment', 'wpsc_delete_attachment' );
            // tickets - share ticket
            add_action( 'wpsc_ajax_wpsc_ticket_shared_users', 'wpsc_ticket_shared_users' );
            // tickets - pinned thread
            add_action( 'wpsc_ajax_wpsc_pinned_thread', 'wpsc_pinned_thread' );
            // templates - get template
            add_action( 'wpsc_ajax_wpsc_get_template', 'wpsc_get_template' );
            // templates - get template for edit
            add_action( 'wpsc_ajax_wpsc_get_template_for_edit', 'wpsc_get_template_for_edit' );
            // templates - delete template
            add_action( 'wpsc_ajax_wpsc_delete_template', 'wpsc_delete_template' );
            // templates - save new template
            add_action( 'wpsc_ajax_wpsc_save_new_template', 'wpsc_save_new_template' );
            // templates - save template changes
            add_action( 'wpsc_ajax_wpsc_save_template_changes', 'wpsc_save_template_changes' );
            // templates - delete selected templates
            add_action( 'wpsc_ajax_wpsc_delete_selected_templates', 'wpsc_delete_selected_templates' );
            // tickets - create new
            add_action( 'wpsc_ajax_wpsc_front_new_ticket_save', 'wpsc_front_new_ticket_save' );
            add_action( 'wpsc_ajax_nopriv_wpsc_front_new_ticket_save', 'wpsc_front_new_ticket_save' );
            // tickets - load ticket
            add_action( 'wpsc_ajax_wpsc_get_front_ticket', 'wpsc_get_front_ticket' );
            add_action( 'wpsc_ajax_nopriv_wpsc_get_front_ticket', 'wpsc_get_front_ticket' );
            // tickets - new reply
            add_action( 'wpsc_ajax_wpsc_client_reply', 'wpsc_client_reply' );
            add_action( 'wpsc_ajax_nopriv_wpsc_client_reply', 'wpsc_client_reply' );
            // piping - get email preview
            add_action( 'wpsc_ajax_wpsc_get_email_preview', 'wpsc_get_email_preview' );
            // piping - add email to ticket
            add_action( 'wpsc_ajax_wpsc_add_email_to_ticket', 'wpsc_add_email_to_ticket' );
            // piping - create new ticket from email
            add_action( 'wpsc_ajax_wpsc_new_ticket_from_piping', 'wpsc_new_ticket_from_piping' );
			// notice - dismiss
            add_action( 'wpsc_ajax_wpsc_dismiss_notice', 'wpsc_dismiss_notice' );
			// modal - admin
			add_action( 'admin_footer', array( $this, 'wpsc_admin_modal' ) );
        //} else if ( strpos( $cururl, $wpsc_support_page_url ) !== false ) {
        } else if ( $tid == $wpsc_options['wpsc_support_page'] ) {
        	// tickets - create new
            add_action( 'wpsc_ajax_wpsc_front_new_ticket_save', 'wpsc_front_new_ticket_save' );
            add_action( 'wpsc_ajax_nopriv_wpsc_front_new_ticket_save', 'wpsc_front_new_ticket_save' );
            // tickets - load ticket
            add_action( 'wpsc_ajax_wpsc_get_front_ticket', 'wpsc_get_front_ticket' );
            add_action( 'wpsc_ajax_nopriv_wpsc_get_front_ticket', 'wpsc_get_front_ticket' );
            // tickets - new reply
            add_action( 'wpsc_ajax_wpsc_client_reply', 'wpsc_client_reply' );
            add_action( 'wpsc_ajax_nopriv_wpsc_client_reply', 'wpsc_client_reply' );
            include_once( WPSC_PLUGIN_DIR . 'includes/front/front.php' );
			// modal - front
			add_action( 'wp_footer', array( $this, 'wpsc_front_modal' ) );
			// shortcode
            add_shortcode( 'wpsc_tickets', 'wpsc_tickets_shortcode' );
        }
    }

    /**
     * plugin activate
     *
     *
     */
    public static function wpsc_activate() {
        require_once( WPSC_PLUGIN_DIR . 'includes/activate.php' );
    }

    /**
     * plugin deactivate
     *
     *
     */
    public static function wpsc_deactivate() {
        require_once( WPSC_PLUGIN_DIR . 'includes/deactivate.php' );
    }

    /**
     * plugin uninstaller
     *
     *
     */
    public static function wpsc_uninstall() {
        require_once( WPSC_PLUGIN_DIR . 'includes/uninstall.php' );
    }

    /**
     * plugin updater
     *
     *
     */
    public static function maybe_update() {
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        if( $wpsc_options['wpsc_version'] == WPSC_VERSION && !isset( $_GET['wpsc_debug'] ) ) {
            return;
        }
        require_once( WPSC_PLUGIN_DIR . 'includes/update.php' );
    }

	/**
     * wpsc_agent filter
     *
     *
     */
    public static function wpsc_agent_filter( $agent, $wpsc_options ) {
		$agent = ( isset( $wpsc_options['wpsc_agent'] ) && $wpsc_options['wpsc_agent'] != '' ) ? $wpsc_options['wpsc_agent'] : $agent;
	    return $agent;
    }

	/**
     * wpsc_client filter
     *
     *
     */
    public static function wpsc_client_filter( $client, $wpsc_options ) {
		$client = ( isset( $wpsc_options['wpsc_client'] ) && $wpsc_options['wpsc_client'] != '' ) ? $wpsc_options['wpsc_client'] : $client;
	    return $client;
    }

	/**
     * wpsc_item filter
     *
     *
     */
    public static function wpsc_item_filter( $item, $wpsc_options ) {
		$item = ( isset( $wpsc_options['wpsc_item'] ) && $wpsc_options['wpsc_item'] != '' ) ? $wpsc_options['wpsc_item'] : $item;
	    return $item;
    }

	/**
     * cron schedules
     *
     *
     */
    public static function wpsc_cron_schedules( $schedules ) {
        if(!isset($schedules["twomin"])){
	        $schedules["twomin"] = array(
	            'interval' => 2*60,
	            'display' => __('Once every 2 minutes'));
	    }
	    return $schedules;
    }

    public static function wpsc_enqueue_assets( $hook ) {
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
		$cururl = strtolower( $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
		$wpsc_support_page_url = strtolower( $wpsc_options['wpsc_support_page_url'] );
		$wpsc_support_page_url = str_replace( array( 'http://', 'https://' ), '', $wpsc_support_page_url );
        $user_id = get_current_user_id();
        if ( !defined('DOING_AJAX') ) {
        	//if ( $cururl == $wpsc_support_page_url || is_admin() ) {
        	if ( is_admin() || get_the_ID() == $wpsc_options['wpsc_support_page'] ) {
	            // javascript
	            if ( is_admin() ) {
                    $dependencies = array( 'jquery', 'jquery-ui-core', 'jquery-ui-accordion', 'jquery-ui-autocomplete', 'jquery-ui-button', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-tabs', 'iris' );
	            } else {
	                $dependencies = array( 'jquery', 'jquery-ui-core', 'jquery-ui-accordion', 'jquery-ui-autocomplete', 'jquery-ui-button', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-tabs' );
	            }
                $bootstrap = array( 'jquery', 'datatables' );
	            $wpsc_is_agent = ( is_user_logged_in() && current_user_can( 'manage_wpsc_ticket' ) ) ? 'true' : 'false';
	            $wpsc_is_super = ( is_user_logged_in() && current_user_can( 'manage_wpsc_agent' ) ) ? 'true' : 'false';
	            $wpsc_salt = ( is_user_logged_in() && current_user_can( 'manage_wpsc_ticket' ) ) ? md5( get_current_user_id() ) : md5( time() );
	            $wpsc_thanks_page = ( isset( $wpsc_options['wpsc_thanks_page'] ) && is_numeric( $wpsc_options['wpsc_thanks_page'] ) && get_permalink( $wpsc_options['wpsc_thanks_page'] ) ) ? get_permalink( $wpsc_options['wpsc_thanks_page'] ) : '0';
	            $localize = array(
	                'wpsc_ajax_url' => apply_filters ('wpsc_ajax_url', WPSC_PLUGIN_URL . 'ajax/_ajax-handler.php' ), // admin_url( 'admin-ajax.php' ),
	                'wpsc_site_url' => site_url(),
	                'wpsc_admin_url' => admin_url( 'admin.php' ),
	                'wpsc_plugin_url' => WPSC_PLUGIN_URL,
	                'wpsc_plugin_dir' => WPSC_PLUGIN_DIR,
	                'wpsc_item' => apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ),
	                'wpsc_client' => apply_filters( 'wpsc_client', 'Client', $wpsc_options ),
	                'wpsc_login_url' => wp_login_url(),
	                'wpsc_is_agent' => $wpsc_is_agent,
	                'wpsc_is_super' => $wpsc_is_super,
	                'wpsc_salt' => $wpsc_salt,
	                'wpsc_thanks_page' => $wpsc_thanks_page,
	                'wpsc_page' => ( isset( $_GET['page'] ) ) ? $_GET['page'] : '',
	                'wpsc_locale' => get_locale()
	            );
	            wp_enqueue_script( 'jquery' );
	            wp_enqueue_script( 'jquery-ui-core' );
	            wp_enqueue_script( 'jquery-ui-accordion' );
	            wp_enqueue_script( 'jquery-ui-autocomplete' );
	            wp_enqueue_script( 'jquery-ui-button' );
	            wp_enqueue_script( 'jquery-ui-datepicker' );
	            wp_enqueue_script( 'jquery-ui-dialog' );
	            wp_enqueue_script( 'jquery-ui-tabs' );
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_script( 'media-upload' );
	            if ( is_admin() ) {
	                wp_enqueue_script( 'iris' );
	                wp_enqueue_script( 'wp-color-picker' );
	                wp_enqueue_script( 'chosen', WPSC_PLUGIN_URL . 'assets/lib/chosen_v1.4.2/chosen.jquery.min.js', $dependencies, WPSC_VERSION, true );
                    if ( isset( $wpsc_options['wpsc_load_bootstrap_js_a'] ) && $wpsc_options['wpsc_load_bootstrap_js_a'] == 1 ) {
                        wp_enqueue_script( 'wpsc-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', $dependencies, WPSC_VERSION, true );
                    }
	            } else {
	                wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), $dependencies, WPSC_VERSION, true );
	                wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), WPSC_VERSION, true );
	                $colorpicker_l10n = array(
	                    'clear'         => __( 'Clear' ),
	                    'defaultString' => __( 'Default' ),
	                    'pick'          => __( 'Select Color' ),
	                    'current'       => __( 'Current Color' ),
	                );
	                wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
                    if ( isset( $wpsc_options['wpsc_load_bootstrap_js_f'] ) && $wpsc_options['wpsc_load_bootstrap_js_f'] == 1 ) {
                        wp_enqueue_script( 'wpsc-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', $dependencies, WPSC_VERSION, true );
                    }
	            }
				wp_enqueue_script( 'datatables', 'https://cdn.datatables.net/v/dt/jszip-2.5.0/pdfmake-0.1.18/dt-1.10.13/b-1.2.4/b-html5-1.2.4/b-print-1.2.4/r-2.1.0/datatables.min.js', $dependencies, WPSC_VERSION, true );
	            wp_enqueue_script( 'ckeditor', WPSC_PLUGIN_URL . 'assets/lib/ckeditor/ckeditor.js', $dependencies, WPSC_VERSION, true );
	            wp_enqueue_script( 'ckeditor-jquery', WPSC_PLUGIN_URL . 'assets/lib/ckeditor/adapters/jquery-adapter.js', $dependencies, WPSC_VERSION, true );
	            wp_enqueue_script( 'wpsc-admin', WPSC_PLUGIN_URL . 'assets/js/admin.min.js', $dependencies, WPSC_VERSION, true );
	            wp_localize_script( 'wpsc-admin', 'wpsc_localize_admin', $localize );
                // divi compatibility
                $cur_theme = wp_get_theme();
                $my_theme = wp_get_theme( 'Divi' );
                $themes_root = get_theme_root();
                if ( $my_theme->exists() ) {
                    $divi_ver = ( isset( $wpsc_options['wpsc_divi_version'] ) && $wpsc_options['wpsc_divi_version'] != '' ) ? $wpsc_options['wpsc_divi_version'] : '';
                    if ( $my_theme->get( 'Version' ) != $divi_ver ) {
                    	if ( file_exists( $themes_root . '/Divi/js/custom.js' ) ) {
                            //$custom = file_get_contents( 'https://cdn.elegantthemesdemo.com/wp-content/themes/Divi/js/custom.js?ver=3.0.45' );
                            //$write = file_put_contents( $themes_root . '/Divi/js/custom.js', $custom );
                            $custom = file_get_contents( $themes_root . '/Divi/js/custom.js' );
                            if ( !file_exists( $themes_root . '/Divi/js/custom.js.' . $divi_ver ) ) {
                                rename( $themes_root . '/Divi/js/custom.js', $themes_root . '/Divi/js/custom.js.' . $divi_ver );
                            }
                            $function = get_string_between( $custom, '$( \'a[href*="#"]:not([href="#"])\' ).click( function() {', '});' );
                            $insert = "\r\n" . 'if ( !$(this).closest( \'.wpsc-bootstrap-styles\' ).length ) {' . "\r\n";
                            $insert .= $function . "\r\n";
                            $insert .= '}' . "\r\n";
                            $updated = str_replace( $function, $insert, $custom );
                            $write = file_put_contents( $themes_root . '/Divi/js/custom.js', $updated );
                            $wpsc_options['wpsc_divi_version'] = $my_theme->get( 'Version' );
                            update_option( 'wpsc_options', $wpsc_options );
						}
                    }
                    if( $cur_theme->get( 'Name' ) == 'Divi' || $cur_theme->get( 'template' ) == 'Divi' ) {
                        wp_enqueue_script( 'wpsc-divi-custom-script', WPSC_PLUGIN_URL . 'assets/js/custom.js', array( 'wpsc-admin' ) ,WPSC_VERSION, true );
                    }
                }
	            // css
	            $wp_jqueryui_ver = ( $GLOBALS['wp_scripts']->registered['jquery-ui-core']->ver != '' ) ? $GLOBALS['wp_scripts']->registered['jquery-ui-core']->ver : WPSC_JQUERYUI_VER;
				$wp_jquery_theme = ( isset( $wpsc_options['wpsc_jqueryui_theme'] ) && $wpsc_options['wpsc_jqueryui_theme'] != '' ) ? $wpsc_options['wpsc_jqueryui_theme'] : WPSC_JQUERYUI_THEME;
				wp_enqueue_style( 'datatables', 'https://cdn.datatables.net/v/dt/jszip-2.5.0/pdfmake-0.1.18/dt-1.10.13/b-1.2.4/b-html5-1.2.4/b-print-1.2.4/r-2.1.0/datatables.min.css' );
	            wp_enqueue_style( 'jqueryui-themeroller', 'https://code.jquery.com/ui/' . $wp_jqueryui_ver . '/themes/' . $wp_jquery_theme . '/jquery-ui.css' );
	            wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'thickbox' );
	            wp_enqueue_style( 'wpsc-style', WPSC_PLUGIN_URL . 'assets/css/style.min.css' );
	            if ( is_admin() ) {
	                wp_enqueue_style( 'chosen', WPSC_PLUGIN_URL . 'assets/lib/chosen_v1.4.2/chosen.min.css' );
                    if ( isset( $wpsc_options['wpsc_load_bootstrap_css_a'] ) && $wpsc_options['wpsc_load_bootstrap_css_a'] == 1 ) {
                        wp_enqueue_style( 'wpsc-bs', WPSC_PLUGIN_URL . 'assets/css/bs.css' );
                    }
	            } else {
	                if ( isset( $wpsc_options['wpsc_load_bootstrap_css_f'] ) && $wpsc_options['wpsc_load_bootstrap_css_f'] == 1 ) {
                        wp_enqueue_style( 'wpsc-bs', WPSC_PLUGIN_URL . 'assets/css/bs.css' );
                    }
	            }
	        }
        }
    }

	/**
     * Bootstrap Alert - will show if Bootstrap is not loaded
     *
     */
    public static function wpsc_bootstrap_alert() {
    	echo '<div class="notice notice-info wpsc-bootstrap-alert collapse hidden"><strong>WP Support Centre:</strong> If you can see this message please <a href="/wp-admin/admin.php?page=wp-support-centre&wpsc_debug=true&wpsc_ebs=true">click here</a> to enable BootStrap</div>';
    }

	/**
     * Bootstrap Alert - will show if Bootstrap is not loaded
     *
     */
    public static function wpsc_discontinued_alert() {
    	echo '<div class="notice notice-info"><strong>WP Support Centre:</strong> This plugin is no longer under development. If you would like to take over development of this plugin please email support@cloughit.com.au.  You can download an updated version (incomplete) here: <a href="https://cloughit.com.au/wordpress-plugins/wpsc.zip">https://cloughit.com.au/wordpress-plugins/wpsc.zip</a></div>';
    }

	/**
     * WPSC Notices
     *
     */
	public function wpsc_notices() {
		if ( is_admin() ) {
			$options = array(
				'timeout' => 10, //seconds
				'headers' => array(
					'Accept' => 'application/json',
				)
			);
			$wpsc_notice_url = 'https://cloughit.com.au/wordpress-plugins/wp-support-centre/wpsc-notice.json';
			$wpsc_notice_json = wp_remote_get( $wpsc_notice_url, $options );
			if ( is_array( $wpsc_notice_json ) && ! is_wp_error( $wpsc_notice_json ) ) {
			    $body = wp_remote_retrieve_body( $wpsc_notice_json );
			    $wpsc_notice = json_decode( $body );
				$wpsc_notice_log = get_option( 'wpsc_notice_log' );
				if ( $wpsc_notice_log != $wpsc_notice->notice_id ) {
					$exp = strtotime( $wpsc_notice->expire );
					$title = $wpsc_notice->title != '' ? '<string><em>' . stripslashes( $wpsc_notice->title ) . '</em></strong>' : '';
					$message = stripslashes( $wpsc_notice->message );
					$time = current_time( 'timestamp', 1 );
					if ( $time < $exp ) {
				        echo '<div class="notice notice-info is-dismissible wpsc-notice" data-id="' . $wpsc_notice->notice_id . '">' . $title . $message . '</div>';
				    }
				}
			}
		}
	}

    /**
     * add admin options menu
     *
     *
     */
    public static function wpsc_admin_menu() {
    	global $wpdb;
        $wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
        add_menu_page( // WP Support Centre
            WPSC_TITLE,
            WPSC_TITLE,
            WPSC_ADMIN_CAPABILITY,
            WPSC_ADMIN_MENU_SLUG,
            array( wpSupportCentre::get_instance(), 'wpsc_admin_support_centre' ),
            WPSC_ADMIN_ICON_URL,
            WPSC_ADMIN_POSITION
        );
		if ( current_user_can( 'manage_wpsc_agent' ) ) {
			$sql = "SELECT COUNT(*) FROM " . $wpdb->prefix . "wpsc_imap WHERE imap_type=2";
			$count = $wpdb->get_var( $sql );
			if ( $count > 0 ) {
				add_submenu_page( // Mailbox
	                WPSC_ADMIN_MENU_SLUG,
	                'Mailbox',
	                'Mailbox',
	                WPSC_ADMIN_CAPABILITY,
	                'wpsc_admin_mailbox',
	                array( wpSupportCentre::get_instance(), 'wpsc_admin_mailbox' )
	            );
			}
		}
		add_submenu_page( // Recurring Tickets
            WPSC_ADMIN_MENU_SLUG,
            'Recurring ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's',
            'Recurring ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's',
            WPSC_ADMIN_CAPABILITY,
            'wpsc_admin_recurring',
            array( wpSupportCentre::get_instance(), 'wpsc_admin_recurring' )
        );
        if ( current_user_can( 'manage_wpsc_agent' ) ) {
            add_submenu_page( // Settings
                WPSC_ADMIN_MENU_SLUG,
                'Settings',
                'Settings',
                WPSC_ADMIN_CAPABILITY,
                'wpsc_admin_settings',
                array( wpSupportCentre::get_instance(), 'wpsc_admin_settings' )
            );
			add_submenu_page( // Agent / Client
                WPSC_ADMIN_MENU_SLUG,
                'Agent / Client',
                'Agent / Client',
                WPSC_ADMIN_CAPABILITY,
                'wpsc_admin_agent_client',
                array( wpSupportCentre::get_instance(), 'wpsc_admin_agent_client' )
            );
			add_submenu_page( // Notifications
	            WPSC_ADMIN_MENU_SLUG,
	            'Notifications',
	            'Notifications',
	            WPSC_ADMIN_CAPABILITY,
	            'wpsc_admin_notifications',
	            array( wpSupportCentre::get_instance(), 'wpsc_admin_notifications' )
	        );
			add_submenu_page( // Reply Templates
	            WPSC_ADMIN_MENU_SLUG,
	            'Reply Templates',
	            'Reply Templates',
	            WPSC_ADMIN_CAPABILITY,
	            'wpsc_admin_templates',
	            array( wpSupportCentre::get_instance(), 'wpsc_admin_templates' )
	        );
            add_submenu_page( // Utilities
	            WPSC_ADMIN_MENU_SLUG,
	            'Utilities',
	            'Utilities',
	            WPSC_ADMIN_CAPABILITY,
	            'wpsc_admin_utilities',
	            array( wpSupportCentre::get_instance(), 'wpsc_admin_utilities' )
	        );
        }
    }

	/*
     * admin bar link
     *
     *
     */
    function do_admin_bar() {
    	$wpsc_options = ( !isset( $wpsc_options ) || ( isset( $wpsc_options ) && !is_array( $wpsc_options ) ) ) ? get_option( 'wpsc_options' ) : $wpsc_options;
		$wpsc_support_page_url = strtolower( $wpsc_options['wpsc_support_page_url'] );
    	if ( current_user_can( 'manage_wpsc_ticket' ) ) {
	        $GLOBALS[ 'wp_admin_bar' ]->add_menu(
	            array(
	                'id'    => 'wpsc-admin-bar',
	                'title' => '<img src="' . WPSC_PLUGIN_URL . 'assets/images/support-centre-16x16.png" /> ' . WPSC_TITLE,
	                'href'  => esc_url( admin_url( 'admin.php?page=wp-support-centre' ) )
	            )
	        );
	        $GLOBALS[ 'wp_admin_bar' ]->add_node(
	            array(
	                'parent' => 'site-name',
	                'id'     => 'wpsc-support-centre',
	                'title' => '<img src="' . WPSC_PLUGIN_URL . 'assets/images/support-centre-16x16.png" /> ' . WPSC_TITLE . '<span class="wpsc-current-ticket"></span>',
	                'href'   => esc_url( admin_url( 'admin.php?page=wp-support-centre' ) )
	            )
	        );
		} else {
			$GLOBALS[ 'wp_admin_bar' ]->add_menu(
	            array(
	                'id'    => 'wpsc-admin-bar',
	                'title' => '<img src="' . WPSC_PLUGIN_URL . 'assets/images/support-centre-16x16.png" /> ' . WPSC_TITLE,
	                'href'  => esc_url( $wpsc_support_page_url )
	            )
	        );
	        $GLOBALS[ 'wp_admin_bar' ]->add_node(
	            array(
	                'parent' => 'site-name',
	                'id'     => 'wpsc-support-centre',
	                'title' => '<img src="' . WPSC_PLUGIN_URL . 'assets/images/support-centre-16x16.png" /> ' . WPSC_TITLE . '<span class="wpsc-current-ticket"></span>',
	                'href'   => esc_url( $wpsc_support_page_url )
	            )
	        );
		}
    }

    /**
     * WP Support Centre Admin Page
     *
     *
     */
    public static function wpsc_admin_support_centre() {
        require_once( WPSC_PLUGIN_DIR . '/includes/admin/wpsc_admin_support_centre.php' );
    }

	/**
     * WP Support Centre Mailbox Page
     *
     *
     */
	public static function wpsc_admin_mailbox() {
        require_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_mailbox.php' );
    }

	/**
     * WP Support Centre Recurring Tickets Page
     *
     *
     */
	public static function wpsc_admin_recurring() {
        require_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_recurring.php' );
    }

    /**
     * WP Support Centre Settings Page
     *
     *
     */
    public static function wpsc_admin_settings() {
        require_once( WPSC_PLUGIN_DIR . '/includes/admin/wpsc_admin_settings.php' );
    }

	/**
     * WP Support Centre Agent / Client Page
     *
     *
     */
    public static function wpsc_admin_agent_client() {
        require_once( WPSC_PLUGIN_DIR . '/includes/admin/wpsc_admin_agent_client.php' );
    }

	/**
     * WP Support Centre Notifications Page
     *
     *
     */
    public static function wpsc_admin_notifications() {
        require_once( WPSC_PLUGIN_DIR . '/includes/admin/wpsc_admin_notifications.php' );
    }

	/**
     * WP Support Centre Templates Page
     *
     *
     */
	public static function wpsc_admin_templates() {
        require_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_templates.php' );
    }

	/**
     * WP Support Centre Utilities Page
     *
     *
     */
	public static function wpsc_admin_utilities() {
        require_once( WPSC_PLUGIN_DIR . 'includes/admin/wpsc_admin_utilities.php' );
    }

	/**
     * WP Support Centre Admin Modals
     *
     *
     */
	public static function wpsc_admin_modal() {
		include_once( WPSC_PLUGIN_DIR . '/includes/modal/admin.php' );
	}

	/**
     * WP Support Centre Front Modals
     *
     *
     */
	public static function wpsc_front_modal() {
		include_once( WPSC_PLUGIN_DIR . '/includes/modal/front.php' );
	}

	/**
     * Remove Insert From URL from Media Uploader
     *
     *
     */
	public static function wpsc_media_upload_tabs( $tabs ) {
		unset( $tabs['type_url'] );
    	return $tabs;
	}

    /**
     * Check Color Lightness
     *
     * return: true if light
     * return: false if dark
     */
    public static function wpsc_lightness( $hex ) {
        $r = hexdec( substr( $hex, 0, 2 ) );
        $g = hexdec( substr( $hex, 2, 2 ) );
        $b = hexdec( substr( $hex, 4, 2 ) );
        $contrast = sqrt(
            $r * $r * .241 +
            $g * $g * .691 +
            $b * $b * .068
        );
        if ( $contrast > 130 ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Log update option
     *
     * return: $new_value
     *
     */
    public static function wpsc_update_option( $new_value, $old_value ) {
        $update_option = print_r( $new_value, true );
        $time = date( 'Y-m-d H:i:s', time() ) . ': ';
        return $new_value;
    }

}