<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<div id="wpsc_settings_category" class="tab-pane fade">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Category Settings</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12">
                        <table id="wpsc_admin_category" class="wpsc_admin_datatable table table-striped table-bordered wpsc_fullwidth display">
                            <thead>
                            	<th>ID</th>
                                <th>Default</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </thead>
                            <tfoot>
                            	<th></th>
                                <th></th>
                                <th>Category</th>
                                <th></th>
                            </tfoot>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_categories WHERE enabled=1";
                                $category_array = $wpdb->get_results( $sql, OBJECT );
                                foreach( $category_array as $category ) {
                                    echo '<tr>';
                                    	echo '<td class="align_centre">' . $category->id . '</td>';
                                        $checked = ( $category->is_default == 1 ) ? ' checked="checked"' : '';
                                        echo '<td class="align_centre"><input type="radio" name="wpsc_category_default" class="wpsc_category_default" value="' . $category->id . '"' . $checked . '></td>';
                                        echo '<td>' . $category->category . '</td>';
                                        echo '<td>';
                                            if( $category->custom == 1 ) {
                                                echo '<span class="wpsc_delete_category" id="delete_' . $category->id . '" data-id="' . $category->id . '"> <img src="' . WPSC_PLUGIN_URL . '/assets/images/32/trash_32.png" title="Delete" class="wpsc_help" /> Delete</span>';
                                            }
                                        echo '</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Add New Category</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12 col-md-4">
                        <label for="wpsc_new_status">Category</label>
                        <input type="input" id="wpsc_new_category" name="wpsc_new_category" class="form-control" />
                    </div>
                    <div class="col-xs-12 col-md-8"></div>
                </div>
                <button type="button" class="wpsc_add_new_category btn btn-primary btn-sm">Save New Category</button>
            </form>
        </div>
    </div>
</div>