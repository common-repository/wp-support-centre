<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="panel panel-default">
    <div class="panel-heading" data-toggle="collapse" data-target="#wpsc_quick_find_div" data-parent="#wpsc_ticket_filters">
    	<h4 class="panel-title">Quick Find</h4>
    </div>
    <div id="wpsc_quick_find_div" class="panel-collapse collapse">
        <div class="panel-body panel-body-wheat">
            <form method="post" id="wpsc_quick_find_form">
                <div class="form-group">
                    <div class="col-xs-12">
                        <label for="wpsc_item">Ticket # </label>
                        <input type="input" id="wpsc_quick_find" name="wpsc_quick_find" value="" class="" placeholder="<?php echo apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ); ?> #" /> <button type="button" id="wpsc_quick_find_button" class="wpsc_admin_button btn btn-primary btn-sm">Go</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>