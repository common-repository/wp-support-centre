<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<div id="wpsc_settings_status" class="tab-pane fade">
    <?php
    $output = '';
	$output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-heading"><h4 class="panel-title">' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . ' Status Settings</h4></div>';
        $output .= '<div class="panel-body panel-body-wheat">';
            $output .= '<form method="post" class="form-horizontal">';
                $output .= '<div class="form-group">';
                    $output .= '<div class="col-xs-12">';
                        $output .= '<table id="wpsc_admin_status" class="wpsc_admin_datatable table table-striped table-bordered wpsc_fullwidth display">';
                            $output .= '<thead>';
                                $output .= '<th>Default</th>';
                                $output .= '<th>Status</th>';
								$output .= '<th>Email Subject Prefix</th>';
                                $output .= '<th>Colour</th>';
                                $output .= '<th>Actions</th>';
                            $output .= '</thead>';
                            $output .= '<tfoot>';
                                $output .= '<th></th>';
                                $output .= '<th>Status</th>';
								$output .= '<th>Email Subject Prefix</th>';
                                $output .= '<th></th>';
                                $output .= '<th></th>';
                            $output .= '</tfoot>';
                            $output .= '<tbody>';
                                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_status WHERE enabled=1";
                                $status_array = $wpdb->get_results( $sql, OBJECT );
                                foreach( $status_array as $status ) {
                                    $output .= '<tr>';
                                        $checked = ( $status->is_default == 1 ) ? ' checked="checked"' : '';
                                        $output .= '<td class="align_centre"><input type="radio" name="wpsc_status_default" class="wpsc_status_default" value="' . $status->id . '"' . $checked . '></td>';
                                        $output .= '<td class="wpsc_editable_status" data-id="' . $status->id . '"><span id="wpsc_text_status_' . $status->id . '">' . $status->status . '</span><input type="text" id="wpsc_status_' . $status->id . '" value="' . $status->status . '" class="wpsc_hidden" /></td>';
										$output .= '<td class="wpsc_editable_prefix" data-id="' . $status->id . '"><span id="wpsc_text_prefix_' . $status->id . '">' . $status->status_prefix . '</span><input type="text" id="wpsc_prefix_' . $status->id . '" value="' . $status->status_prefix . '" class="wpsc_hidden" /></td>';
                                        $output .= '<td><input type="text" value="' . $status->colour . '" class="wpsc_colour_picker form-control" data-default-color="' . $status->colour . '" id="status_colour_' . $status->id . '" data-id="' . $status->id . '" data-type="status" /></td>';
                                        $output .= '<td>';
                                            $output .= '<span class="wpsc_save_status_colour wpsc_hidden" id="save_status_' . $status->id . '" data-id="' . $status->id . '"> <img src="' . WPSC_PLUGIN_URL . '/assets/images/32/save_32.png" title="Save" class="wpsc_help" /> Save</span>';
                                            if( $status->custom == 1 ) {
                                                $output .= '<span class="wpsc_delete_status" id="delete_' . $status->id . '" data-id="' . $status->id . '"> <img src="' . WPSC_PLUGIN_URL . '/assets/images/32/trash_32.png" title="Delete" class="wpsc_help" /> Delete</span>';
                                            }
                                        $output .= '</td>';
                                    $output .= '</tr>';
                                }
                            $output .= '</tbody>';
                        $output .= '</table>';
                    $output .= '</div>';
                $output .= '</div>';
            $output .= '</form>';
        $output .= '</div>';
    $output .= '</div>';
    $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-heading"><h4 class="panel-title">Add New Custom Status</h4></div>';
        $output .= '<div class="panel-body panel-body-wheat">';
            $output .= '<form method="post" class="form-horizontal">';
                $output .= '<div class="form-group">';
                    $output .= '<div class="col-xs-12 col-md-4">';
                        $output .= '<label for="wpsc_new_status">Status</label>';
                        $output .= '<input type="input" id="wpsc_new_status" name="wpsc_new_status" class="form-control" placeholder="Status" />';
                    $output .= '</div>';
					$output .= '<div class="col-xs-12 col-md-4">';
                        $output .= '<label for="wpsc_new_status_subject_prefix">Email Subject Prefix</label>';
                        $output .= '<input type="input" id="wpsc_new_status_subject_prefix" name="wpsc_new_status_subject_prefix" class="form-control" placeholder="Email Subject Prefix" />';
                    $output .= '</div>';
                    $output .= '<div class="col-xs-12 col-md-4">';
                        $output .= '<label for="wpsc_new_status_colour">Colour</label><br />';
                        $output .= '<input type="text" class="wpsc_colour_picker" data-default-color="#ffffff" id="wpsc_new_status_colour" />';
                    $output .= '</div>';
                $output .= '</div>';
                $output .= '<button type="button" class="wpsc_add_new_status btn btn-primary btn-sm">Save New Status</button>';
            $output .= '</form>';
        $output .= '</div>';
    $output .= '</div>';
    echo apply_filters( 'wpsc_admin_status', $output, $wpsc_options );
    ?>
</div>