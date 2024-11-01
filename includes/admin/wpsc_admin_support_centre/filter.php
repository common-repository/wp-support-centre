<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="panel panel-default">
    <div class="panel-heading" data-toggle="collapse" data-target="#wpsc_admin_filter" data-parent="#wpsc_ticket_filters">
        <h4 class="panel-title">Filter</h4>
    </div>
    <div id="wpsc_admin_filter" class="panel-collapse collapse">
        <div class="panel-body panel-body-wheat">
            <form method="post" id="wpsc_filter_form" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12 col-md-3">
                        <label for="wpsc_ticket_filter_status">Status</label>
                        <select id="wpsc_ticket_filter_status" name="wpsc_ticket_filter_status" class="wpsc_ticket_filter form-control">
                            <?php
                            $theStatus = ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_status'] ) ) ? $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_status'] : '';
                            echo '<option value="">Please select...</option>';
                            $selected = ( $theStatus == 'all' ) ? ' selected="selected"' : '';
                            echo '<option value="all"' . $selected . '>All ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's</option>';
                            $selected = ( $theStatus == 'active' ) ? ' selected="selected"' : '';
                            echo '<option value="active"' . $selected . '>All Active ' . apply_filters( 'wpsc_item', 'Ticket', $wpsc_options ) . 's</option>';
                            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_status ORDER BY status ASC";
                            $results = $wpdb->get_results( $sql, OBJECT );
                            if ( $wpdb->num_rows > 0 ) {
                                foreach ($results as $status) {
                                    $selected = ( $theStatus == $status->id ) ? ' selected="selected"' : '';
                                    echo '<option value="' . $status->id . '"' . $selected . '>' . $status->status . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-3">
                        <label for="wpsc_ticket_filter_category">Category</label>
                        <select id="wpsc_ticket_filter_category" name="wpsc_ticket_filter_category" class="wpsc_ticket_filter form-control">
                            <?php
                            $theCategory = ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_category'] ) ) ? $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_category'] : '';
                            echo '<option value="">Please select...</option>';
                            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_categories ORDER BY category ASC";
                            $results = $wpdb->get_results( $sql, OBJECT );
                            if ( $wpdb->num_rows > 0 ) {
                                foreach ($results as $category) {
                                    $selected = ( $theCategory == $category->id ) ? ' selected="selected"' : '';
                                    echo '<option value="' . $category->id . '"' . $selected . '>' . $category->category . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-3">
                        <label for="wpsc_ticket_filter_agent">Agent</label>
                        <select id="wpsc_ticket_filter_agent" name="wpsc_ticket_filter_agent" class="wpsc_ticket_filter form-control">
                            <?php
                            $theAgent = ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_agent'] ) ) ? $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_agent'] : '';
                            echo '<option value="">Please select...</option>';
                            $args = array(
                                'orderby' => 'display_name',
                                'order' => 'ASC'
                            );
                            $all_users = get_users( $args );
                            foreach ( $all_users as $user ) {
                                if ( $user->has_cap( 'manage_wpsc_ticket' ) ) {
                                    $selected = ( $theAgent == $user->ID ) ? ' selected="selected"' : '';
                                    echo '<option value="' . $user->ID . '"' . $selected . '>' . $user->display_name . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-3">
                        <label for="wpsc_ticket_filter_priority">Priority</label>
                        <select id="wpsc_ticket_filter_priority" name="wpsc_ticket_filter_priority" class="wpsc_ticket_filter form-control">
                            <?php
                            $thePriority = ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_priority'] ) ) ? $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_priority'] : '';
                            echo '<option value="">Please select...</option>';
                            $sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_priority ORDER BY priority ASC";
                            $results = $wpdb->get_results( $sql, OBJECT );
                            if ( $wpdb->num_rows > 0 ) {
                                foreach ($results as $priority) {
                                    $selected = ( $thePriority == $priority->id ) ? ' selected="selected"' : '';
                                    echo '<option value="' . $priority->id . '"' . $selected . '>' . $priority->priority . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <label for="wpsc_ticket_filter_client"><?php echo apply_filters( 'wpsc_client', 'Client', $wpsc_options ); ?> <a href="#wpsc_ticket_client_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_ticket_client_dialog" class="wpsc_help"></a></label>
                        <?php
                        $theClient = ( isset( $wpsc_options['wpsc_ticket_filter_client_' . get_current_user_id()] ) ) ? $wpsc_options['wpsc_ticket_filter_client_' . get_current_user_id()] : '';
                        $theClientID = ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_client_id'] ) ) ? $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_client_id'] : '';
                        echo '<input type="text" id="wpsc_ticket_filter_client" name="wpsc_ticket_filter_client" class="wpsc_ticket_filter form-control" value="' . $theClient . '">';
                        echo '<input type="hidden" id="wpsc_ticket_filter_client_id" name="wpsc_ticket_filter_client_id" class="wpsc_ticket_filter" value="' . $theClientID . '">';
                        ?>
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <label for="wpsc_ticket_filter_date_from">From Date</label>
                        <input id="wpsc_ticket_filter_date_from" name="wpsc_ticket_filter_date_from" class="wpsc_ticket_filter wpsc_ticket_filter_date form-control" value="<?php echo ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_date_from'] ) ) ? $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_date_from'] : ''; ?>">
                    </div>
                    <div class="col-xs-12 col-md-4">
                        <label for="wpsc_ticket_filter_date_to">To Date</label>
                        <input id="wpsc_ticket_filter_date_to" name="wpsc_ticket_filter_date_to" class="wpsc_ticket_filter wpsc_ticket_filter_date form-control" value="<?php echo ( isset( $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_date_to'] ) ) ? $wpsc_options[get_current_user_id()]['wpsc_ticket_filter_date_to'] : ''; ?>">
                    </div>
                </div>
                <div style="clear:both;"></div>
                <input type="hidden" name="wpsc_do_admin_apply_filter" value="0" />
                <input type="hidden" name="wpsc_do_admin_clear_filter" value="0" />
                <button type="button" class="wpsc_admin_button btn btn-primary btn-sm" id="wpsc_admin_apply_filter">Apply Filters</button>
                <button type="button" class="wpsc_admin_button btn btn-primary btn-sm" name="wpsc_admin_clear_filter" id="wpsc_admin_clear_filter">Clear Filters</button>
            </form>
        </div>
    </div>
</div>