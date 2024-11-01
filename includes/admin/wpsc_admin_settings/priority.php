<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<div id="wpsc_settings_priority" class="tab-pane fade">
    <?php
    $output = '';
    $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-heading"><h4 class="panel-title">Priority Settings</h4></div>';
        $output .= '<div class="panel-body panel-body-wheat">';
            $output .= '<form method="post" class="form-horizontal">';
                $output .= '<div class="form-group">';
                    $output .= '<div class="col-xs-12">';
                        $output .= '<table id="wpsc_admin_priority" class="wpsc_admin_datatable table table-striped table-bordered wpsc_fullwidth display">';
                            $output .= '<thead>';
                                $output .= '<th>Default</th>';
                                $output .= '<th>Priority</th>';
								$output .= '<th>SLA</th>';
                                $output .= '<th>Colour</th>';
                                $output .= '<th>Actions</th>';
                            $output .= '</thead>';
                            $output .= '<tfoot>';
                                $output .= '<th></th>';
                                $output .= '<th>Priority</th>';
                                $output .= '<th></th>';
								$output .= '<th></th>';
                                $output .= '<th></th>';
                            $output .= '</tfoot>';
                            $output .= '<tbody>';
                                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority WHERE enabled=1";
                                $priority_array = $wpdb->get_results( $sql, OBJECT );
                                foreach( $priority_array as $priority ) {
                                    $output .= '<tr>';
                                        $checked = ( $priority->is_default == 1 ) ? ' checked="checked"' : '';
                                        $output .= '<td class="align_centre"><input type="radio" name="wpsc_priority_default" class="wpsc_priority_default" value="' . $priority->id . '"' . $checked . '></td>';
                                        $output .= '<td class="wpsc_editable_priority" data-id="' . $priority->id . '"><span id="wpsc_text_priority_' . $priority->id . '">' . $priority->priority . '</span><input type="text" id="wpsc_priority_' . $priority->id . '" value="' . $priority->priority . '" class="wpsc_hidden" /></td>';
										$output .= '<td class="wpsc_editable_sla" data-id="' . $priority->id . '"><span id="wpsc_text_sla_' . $priority->id . '">' . $priority->priority_sla . '</span><input type="number" id="wpsc_sla_' . $priority->id . '" value="' . $priority->priority_sla . '" class="wpsc_hidden" min="0" /></td>';
                                        $output .= '<td><input type="text" value="' . $priority->colour . '" class="wpsc_colour_picker form-control" data-default-color="' . $priority->colour . '" id="priority_colour_' . $priority->id . '" data-id="' . $priority->id . '" data-type="priority" /></td>';
                                        $output .= '<td>';
                                            $output .= '<span class="wpsc_save_priority_colour wpsc_hidden" id="save_priority_' . $priority->id . '" data-id="' . $priority->id . '"> <img src="' . WPSC_PLUGIN_URL . '/assets/images/32/save_32.png" title="Save" class="wpsc_help" /> Save</span>';
                                            if( $priority->custom == 1 ) {
                                                $output .= '<span class="wpsc_delete_priority" id="delete_' . $priority->id . '" data-id="' . $priority->id . '"> <img src="' . WPSC_PLUGIN_URL . '/assets/images/32/trash_32.png" title="Delete" class="wpsc_help" /> Delete</span>';
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
        $output .= '<div class="panel-heading"><h4 class="panel-title">Add New Priority</h4></div>';
        $output .= '<div class="panel-body panel-body-wheat">';
            $output .= '<form method="post" class="form-horizontal">';
                $output .= '<div class="form-group">';
                    $output .= '<div class="col-xs-12 col-md-4">';
                        $output .= '<label for="wpsc_new_status">Priority</label>';
                        $output .= '<input type="input" id="wpsc_new_priority" name="wpsc_new_priority" class="form-control" placeholder="Priority" />';
                    $output .= '</div>';
					$output .= '<div class="col-xs-12 col-md-4">';
                        $output .= '<label for="wpsc_new_status_sla">SLA (minutes)</label>';
                        $output .= '<input type="number" id="wpsc_new_priority_sla" name="wpsc_new_priority_sla" class="form-control" placeholder="SLA (in minutes, 0 = no SLA)" min="0" value="0" />';
                    $output .= '</div>';
                    $output .= '<div class="col-xs-12 col-md-4">';
                        $output .= '<label for="wpsc_new_priority_colour">Color</label><br />';
                        $output .= '<input type="text" class="wpsc_colour_picker" data-default-color="#ffffff" id="wpsc_new_priority_colour" />';
                    $output .= '</div>';
                $output .= '</div>';
                $output .= '<button type="button" class="wpsc_add_new_priority btn btn-primary btn-sm">Save New Priority</button>';
            $output .= '</form>';
        $output .= '</div>';
    $output .= '</div>';
	echo $output;
    ?>
</div>