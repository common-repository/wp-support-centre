<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="panel panel-default">
    <div class="panel-heading" data-toggle="collapse" data-target="#wpsc_admin_search" data-parent="#wpsc_ticket_filters">
        <h4 class="panel-title">Search</h4>
    </div>
    <div id="wpsc_admin_search" class="panel-collapse collapse">
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12 col-md-3">
                        <?php $search = ( isset( $_POST['wpsc_admin_ticket_search'] ) ) ? $_POST['wpsc_admin_ticket_search'] : ''; ?>
                        <input type="text" id="wpsc_admin_ticket_search" name="wpsc_admin_ticket_search" placeholder="Search" value="<?php echo $search; ?>" required class="form-control">
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <div class="radio">
                            <label><input type="radio" id="wpsc_admin_ticket_search_type" name="wpsc_admin_ticket_search_type" value="any" checked="checked"> Any Word </label>
                            <label><input type="radio" id="wpsc_admin_ticket_search_type" name="wpsc_admin_ticket_search_type" value="all"> All Words </label>
                            <label><input type="radio" id="wpsc_admin_ticket_search_type" name="wpsc_admin_ticket_search_type" value="exact"> Exact Phrase </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-1">Search In:</div>
                    <div class="col-xs-12 col-md-4">
                        <div class="radio">
                            <label><input type="radio" id="wpsc_admin_ticket_search_item" name="wpsc_admin_ticket_search_item" value="both" checked="checked"> Subject &amp; Body </label>
                            <label><input type="radio" id="wpsc_admin_ticket_search_item" name="wpsc_admin_ticket_search_item" value="subject"> Subject Only </label>
                            <label><input type="radio" id="wpsc_admin_ticket_search_item" name="wpsc_admin_ticket_search_item" value="body"> Body Only </label>
                        </div>
                    </div>
                </div>
                <button type="button" class="wpsc_admin_search_button btn btn-primary btn-sm" name="wpsc_admin_search_button" id="wpsc_admin_search_button">Search</button>
            </form>
        </div>
    </div>
</div>