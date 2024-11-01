<?php
/**
 * Plugin Name: WP Support Centre Helper
 * Description: Expands WP Support Centre to provide support for additional fields
 * Author:      Clough I.T. Solutions
 * Author URI:  https://cloughit.com.au
 * Version:     2016.08.15
 * Text Domain: wp-support-centre
 * License:     GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/*
 * How To Use WP Support Centre Helper
 * ===================================
 * 
 * 1. Modify this file accordingly
 * 2. Copy the 'wp-support-centre-helper' directory and all contained files to your plugins directory
 * 3. Activate the WP Support Centre Helper plugin
 * 
 * Available Filters
 * =================
 * 
 * wpsc_additional_fields
 * ----------------------------
 * 
 * Provides the hook to use to insert the additional fields into the New Ticket form on the Front Page
 * 
 * When adding a new field it must contain:
 * - id="wpsc_additional_field_{name}" - {name} must be unique
 * - class="wpsc_additional_field class" - this identifies the additional field to WP Support Centre
 *
 * When adding the fields, the following classes can be added to allow additional functionality
 * - class="wpsc_additional_field_required" -> identifies the field as a required field
 * 
 */
 
add_filter( 'wpsc_additional_fields', 'my_wpsc_additional_fields', 10, 2 );
function my_wpsc_additional_fields( $ticket ) {
	//****************************************************************************************************************************
	/* The following section is common amongst examples and must be included within the helper */
	// Declare database support
	global $wpdb;
	// Check if $ticket exists
	if ( $ticket != '' ) {
		// $ticket exists, get data from database
		$meta = array();
		$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_additional_fields_meta WHERE ticket_id=" . $ticket->id;
		$metas = $wpdb->get_results( $sql );
		if ( !empty( $metas ) && !is_null( $metas ) ) {
			foreach ( $metas as $data ) {
				$field_id = $data->field_id;
				$meta_value = $data->meta_value;
				$meta[$field_id] = $meta_value; // loads the value of the meta into a variable matching the additional field id
			}
		}
	} else {
		$ticket = new stdClass();
	}
	$output = '';
	//****************************************************************************************************************************
	/*
	 * Example 1
	 * =========
	 * This example adds 3 new form fields and will be shown always:
	 * - Make (text, required)
	 * - Model (text, required)
	 * - Serial Number (text, required)
	 */
	/*$output .= '<div class="form-group wpsc-additional-fields" >';
		$output .= '<div class="col-xs-4">'; // Using Bootstrap creates a column 1/3 width of container
	        $output .= '<label for="wpsc_additional_field_make">Make <span class="wpsc_required">*</span></label>';
	        $output .= '<input type="text" id="wpsc_additional_field_make"  class="form-control wpsc_additional_field wpsc_additional_field_required" value="' . $wpsc_additional_field_make . '" data-ticket-id="' . $ticket->id . '" >';
	    $output .= '</div>';
	    $output .= '<div class="col-xs-4">'; // Using Bootstrap creates a column 1/3 width of container
	        $output .= '<label for="wpsc_additional_field_model">Model <span class="wpsc_required">*</span></label>';
	        $output .= '<input type="text" id="wpsc_additional_field_model"  class="form-control wpsc_additional_field wpsc_additional_field_required" value="' . $wpsc_additional_field_model . '" data-ticket-id="' . $ticket->id . '" >';
	    $output .= '</div>';
		$output .= '<div class="col-xs-4">'; // Using Bootstrap creates a column 1/3 width of container
	        $output .= '<label for="wpsc_additional_field_serial">Serial Number <span class="wpsc_required">*</span></label>';
	        $output .= '<input type="text" id="wpsc_additional_field_serial"  class="form-control wpsc_additional_field wpsc_additional_field_required" value="' . $wpsc_additional_field_serial . '" data-ticket-id="' . $ticket->id . '" >';
	    $output .= '</div>';
	$output .= '</div>';
    return $output;*/
    //****************************************************************************************************************************
    /*
	 * Example 2
	 * =========
	 * This example adds 3 new form fields and will be shown only if the selected category is 'General':
	 * - Make (text, required)
	 * - Model (text, required)
	 * - Serial Number (text, required)
	 * 
	 * By adding data-category="{category_id}" to each additional fields row container WP Support Centre will be able to identify that when a category is selected
	 * and it matches the data-category then the additional fields become visible
	 */
	/*
	$wpsc_hidden = ( !isset( $_GET['wpsc_category'] ) || ( isset( $_GET['wpsc_category'] ) && $_GET['wpsc_category'] != '1' ) ) ? ' wpsc_hidden' : '';
	$wpsc_hidden = ( isset( $ticket->category_id ) && $ticket->category_id == '1' ) ? '' : $wpsc_hidden;
	$output .= '<div class="form-group wpsc-additional-fields wpsc_hidden" data-category="1" >'; // fields are initially hidden. The id of the general category is 1
		$output .= '<div class="col-xs-4">'; // Using Bootstrap creates a column 1/3 width of container
	        $output .= '<label for="wpsc_additional_field_make">Make <span class="wpsc_required">*</span></label>';
	        $output .= '<input type="text" id="wpsc_additional_field_make"  class="form-control wpsc_additional_field wpsc_additional_field_required" value="' . $wpsc_additional_field_make . '" data-ticket-id="' . $ticket->id . '" >';
	    $output .= '</div>';
	    $output .= '<div class="col-xs-4">'; // Using Bootstrap creates a column 1/3 width of container
	        $output .= '<label for="wpsc_additional_field_model">Model <span class="wpsc_required">*</span></label>';
	        $output .= '<input type="text" id="wpsc_additional_field_model"  class="form-control wpsc_additional_field wpsc_additional_field_required" value="' . $wpsc_additional_field_model . '" data-ticket-id="' . $ticket->id . '" >';
	    $output .= '</div>';
		$output .= '<div class="col-xs-4">'; // Using Bootstrap creates a column 1/3 width of container
	        $output .= '<label for="wpsc_additional_field_serial">Serial Number <span class="wpsc_required">*</span></label>';
	        $output .= '<input type="text" id="wpsc_additional_field_serial"  class="form-control wpsc_additional_field wpsc_additional_field_required" value="' . $wpsc_additional_field_serial . '" data-ticket-id="' . $ticket->id . '" >';
	    $output .= '</div>';
	$output .= '</div>';
    return $output;*/
	//****************************************************************************************************************************
    /*
	 * Example 3
	 * =========
	 * This example expands on Example 2 and adds 3 new form fields and will be shown only if the selected category id is 1, 3 or 5:
	 * - Make (text, required)
	 * - Model (text, required)
	 * - Serial Number (text, required)
	 * 
	 * By adding data-category="{category_id1,category_id2,...,category_idn}" to each additional fields row container WP Support Centre will be able to identify 
	 * that when a category is selected and it matches one of the data-category values then the additional fields become visible
	 */
	/*
	$categories = array( 1, 3, 5 );
	$wpsc_hidden = ( !isset( $_GET['wpsc_category'] ) || ( isset( $_GET['wpsc_category'] ) && !in_array( $_GET['wpsc_category'], $categories ) ) ) ? ' wpsc_hidden' : '';
	$wpsc_hidden = ( isset( $ticket->category_id ) && in_array( $ticket->category_id, $categories ) ) ? '' : $wpsc_hidden;
	$output .= '<div class="form-group wpsc-additional-fields wpsc_hidden" data-category="1,3,5" >'; // fields are initially hidden. data-category is a comma separated list of category ids
		$output .= '<div class="col-xs-4">'; // Using Bootstrap creates a column 1/3 width of container
	        $output .= '<label for="wpsc_additional_field_make">Make <span class="wpsc_required">*</span></label>';
	        $output .= '<input type="text" id="wpsc_additional_field_make"  class="form-control wpsc_additional_field wpsc_additional_field_required" value="' . $wpsc_additional_field_make . '" data-ticket-id="' . $ticket->id . '" >';
	    $output .= '</div>';
	    $output .= '<div class="col-xs-4">'; // Using Bootstrap creates a column 1/3 width of container
	        $output .= '<label for="wpsc_additional_field_model">Model <span class="wpsc_required">*</span></label>';
	        $output .= '<input type="text" id="wpsc_additional_field_model"  class="form-control wpsc_additional_field wpsc_additional_field_required" value="' . $wpsc_additional_field_model . '" data-ticket-id="' . $ticket->id . '" >';
	    $output .= '</div>';
		$output .= '<div class="col-xs-4">'; // Using Bootstrap creates a column 1/3 width of container
	        $output .= '<label for="wpsc_additional_field_serial">Serial Number <span class="wpsc_required">*</span></label>';
	        $output .= '<input type="text" id="wpsc_additional_field_serial"  class="form-control wpsc_additional_field wpsc_additional_field_required" value="' . $wpsc_additional_field_serial . '" data-ticket-id="' . $ticket->id . '" >';
	    $output .= '</div>';
	$output .= '</div>';
    return $output;*/
}