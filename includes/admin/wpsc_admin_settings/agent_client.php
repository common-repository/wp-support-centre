<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<div id="wpsc_settings_agent" class="tab-pane fade">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Default Agent</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <div class="col-xs-12 col-md-4">
                        <label for="wpsc_agent_default">Default Agent <a href="#wpsc_default_agent_dialog" data-toggle="modal"><img src="<?php echo WPSC_PLUGIN_URL; ?>assets/images/32/info-circled_32.png" title="Help" data-id="wpsc_default_agent_dialog" class="wpsc_help"></a></label>
                        <select id="wpsc_agent_default" name="wpsc_agent_default" class="form-control">
                            <option value="">Please select...</option>
                            <?php $selected = ( $wpsc_options['wpsc_default_agent'] == 's' ) ? ' selected="selected"' : ''; ?>
                            <option value="s"<?php echo $selected; ?>>Random Supervisor</option>
                            <?php $selected = ( $wpsc_options['wpsc_default_agent'] == 'a' ) ? ' selected="selected"' : ''; ?>
                            <option value="a"<?php echo $selected; ?>>Random Agent</option>
                            <?php
                            $args = array(
                                'orderby' => 'display_name',
                                'order' => 'ASC'
                            );
                            $all_users = get_users( $args );
                            $wpsc_supervisor_users = array();
                            $wpsc_agent_users = array();
                            foreach ( $all_users as $user ) {
                                if ( $user->has_cap( 'manage_wpsc_agent' ) ) {
                                    $wpsc_supervisor_users[] = $user;
                                } else if ( $user->has_cap( 'manage_wpsc_agent' ) ) {
                                    $wpsc_agent_users[] = $user;
                                }
                            }
                            $count = 1;
                            foreach ( $wpsc_supervisor_users as $wpsc_supervisor ) {
                                if ( $count == 1 ) {
                                    echo '<option value="">-- Supervisors --</option>';
                                    $count++;
                                }
                                $selected = ( $wpsc_supervisor->ID == $wpsc_options['wpsc_default_agent'] ) ? ' selected="selected"' : '';
                                echo '<option value="' . $wpsc_supervisor->ID . '"' . $selected . '>' . $wpsc_supervisor->display_name . ' (' . $wpsc_supervisor->user_email . ')</option>';
                                $wpsc_users[] = $wpsc_supervisor->ID;
                            }
                            $count = 1;
                            foreach ( $wpsc_agent_users as $wpsc_agent ) {
                                if ( $count == 1 ) {
                                    echo '<option value="">-- Agents --</option>';
                                    $count++;
                                }
                                $selected = ( $wpsc_agent->ID == $wpsc_options['wpsc_default_agent'] ) ? ' selected="selected"' : '';
                                echo '<option value="' . $wpsc_agent->ID . '"' . $selected . '>' . $wpsc_agent->display_name . ' (' . $wpsc_agent->user_email . ')</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-md-8"></div>
                </div>
                <button type="button" class="wpsc_save_wpsc_agent btn btn-primary btn-sm">Save Settings</button>
            </form>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Registered Users</h4></div>
        <div class="panel-body panel-body-wheat">
            <ul id="wpsc_admin_settings_users_tabs" class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#wpsc_settings_users">Users</a></li>
            </ul>
            <div class="tab-content" id="wpsc_users_content">
                <div id="wpsc_settings_users" class="tab-pane fade active in">
                    <div class="panel panel-default">
                        <div class="panel-body panel-body-wheat">
                            <form method="post" class="form-horizontal">
                                <div class="form-group">
                                    <div class="col-xs-12">
                                        <table id="wpsc_admin_users_table" class="wpsc_admin_datatable table table-striped table-bordered wpsc_fullwidth display">
                                            <thead>
                                                <th>User</th>
                                                <th>Email</th>
                                                <th>Agent</th>
                                                <th></th>
                                                <th>Supervisor</th>
                                                <th></th>
                                            </thead>
                                            <tfoot>
                                                <th>User</th>
                                                <th>Email</th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                                <th></th>
                                            </tfoot>
                                            <tbody>
                                                <?php
                                                $all_users = get_users();
                                                foreach ( $all_users as $user ) {
                                                    echo '<tr class="wpsc_user_row" data-user-id="' . $user->ID . '" data-display-name="' . $user->display_name . '">';
                                                        echo '<td class="valign_middle">' . $user->display_name . '</td>';
                                                        echo '<td class="valign_middle">' . $user->user_email . '</td>';
                                                        $checked = ( $user->has_cap( 'manage_wpsc_ticket' ) ) ? ' checked="checked"' : '';
                                                        echo '<td class="align_centre valign_middle wpsc_user_agent_td"><input type="checkbox" class="wpsc_user_agent" data-id="' . $user->ID . '" value="' . $user->ID . '"' . $checked . '></td>';
                                                        echo '<td class="valign_middle">' . ( ( $checked != '' ) ? '0' : '1' ) . '</td>';
                                                        $checked = ( $user->has_cap( 'manage_wpsc_agent' ) ) ? ' checked="checked"' : '';
                                                        echo '<td class="align_centre valign_middle wpsc_user_supervisor_td"><input type="checkbox" class="wpsc_user_supervisor" data-id="' . $user->ID . '" value="' . $user->ID . '"' . $checked . '></td>';
                                                        echo '<td class="valign_middle">' . ( ( $checked != '' ) ? '0' : '1' ) . '</td>';
                                                    echo '</tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <button type="button" class="wpsc_save_wpsc_agent btn btn-primary btn-sm">Save Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>