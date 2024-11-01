<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
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
                    $output .= '<label for="wpsc_edit_template_label">' . apply_filters( 'wpsc_label_text', 'Label' ) . ' <span class="wpsc_required">*</span></label>';
                    $output .= '<input type="text" class="form-control" id="wpsc_edit_template_label" value="' . $template->label . '">';
                $output .= '</div>';
                $output .= '<div class="col-xs-6"></div>';
            $output .= '</div>';
            $output .= '<div class="form-group">';
                $output .= '<div class="col-xs-12">';
                    $output .= '<label for="wpsc_edit_template">' . apply_filters( 'wpsc_template_text', 'Template' ) . ' <span class="wpsc_required">*</span></label>';
                    $output .= '<textarea class="wpsc_ckeditor" id="wpsc_edit_template" name="wpsc_edit_template">' . $template->template . '</textarea>';
                $output .= '</div>';
            $output .= '</div>';
            $output .= '<button type="button" class="wpsc_admin_button btn btn-primary btn-sm" id="wpsc_save_edit_template" data-id="' . $template->id . '">' . apply_filters( 'wpsc_save_changes_text', 'Save Changes' ) . '</button>';
        $output .= '</form>';
        $return['status'] = 'true';
        $return['template'] = $output;
    } else {
        $return['status'] = 'false';
    }
    echo json_encode( $return );
    wp_die();
}