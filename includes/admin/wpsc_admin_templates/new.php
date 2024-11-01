<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div id="wpsc_admin_new_template" class="tab-pane fade">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">New Reply Template <span class="wpsc_required">* = required</span></h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12 col-md-4">
                        <label for="wpsc_new_template_label">Label <span class="wpsc_required">*</span></label>
                        <input type="text" class="form-control" id="wpsc_new_template_label">
                    </div>
                    <div class="col-xs-12 col-md-8"></div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <label for="wpsc_new_template">Template <span class="wpsc_required">*</span></label>
                        <textarea class="wpsc_ckeditor" id="wpsc_new_template" name="wpsc_new_template"></textarea>
                    </div>
                </div>
                <button type="button" class="wpsc_admin_button btn btn-primary btn-sm" id="wpsc_save_new_template">Save Template</button>
            </form>
        </div>
    </div>
</div>