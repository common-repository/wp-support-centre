<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div id="wpsc_admin_templates" class="tab-pane fade active in">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Reply Templates</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12 col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading"><h4 class="panel-title">Templates</h4></div>
                            <div class="panel-body panel-body-wheat">
                                <?php
                                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_templates";
                                $templates = $wpdb->get_results( $sql, OBJECT );
                                if ( $wpdb->num_rows > 0 ) {
                                    ?>
                                    <table class="table table-striped table-bordered wpsc_admin_datatable wpsc_fullwidth" id="wpsc_admin_templates_table">
                                        <thead>
                                            <tr>
                                                <th class="thead"><input type="checkbox" id="wpsc_select_all_templates" /></th>
                                                <th class="thead">Template</th>
                                                <th class="thead"></th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th class="thead"></th>
                                                <th class="thead">Template</th>
                                                <th class="thead"></th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <?php
                                            foreach( $templates as $template ) {
                                                echo '<tr class="wpsc_template_row" id="' . $template->id . '">';
                                                    echo '<td class="align_centre wpsc_select_template_td"><input type="checkbox" class="wpsc_select_template" value="' . $template->id . '" /></td>';
                                                    echo '<td>' . $template->label . '</td>';
                                                    echo '<td class="align_centre"><img src="' . WPSC_PLUGIN_URL . '/assets/images/32/trash_32.png" class="wpsc_help wpsc_template_delete" data-id="' . $template->id . '"></td>';
                                                echo '</tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <?php
                                } else {
                                    echo '<p>No reply templates found.</p>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="panel wpsc_hidden" id="wpsc_template_actions">
                            <div class="panel-body panel-body-wheat">
                                <button type="button" class="wpsc_admin_button btn btn-primary btn-sm" name="wpsc_delete_selected_templates" id="wpsc_delete_selected_templates">Delete Selected Templates</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-8">
                        <div class="panel panel-default">
                            <div class="panel-heading"><h4 class="panel-title">Edit Template <span class="wpsc_required">* = required</span></h4></div>
                            <div class="panel-body panel-body-wheat">
                                <div id="wpsc_template_body">Select a template to edit.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>