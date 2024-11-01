<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
$colors = array(
	'#FFEBEE', '#D1C4E9', '#B3E5FC', '#C8E6C9', '#FFF9C4', '#FFCCBC',
	'#FCE4EC', '#C5CAE9', '#B2EBF2', '#DCEDC8', '#FFECB3', '#D7CCC8',
	'#F3E5F5', '#BBDEFB', '#B2DFDB', '#F0F4C3', '#FFE0B2', '#FAFAFA'
);

if ( !function_exists( 'wpscOpenIMAP' ) ) {
    function wpscOpenIMAP( $connect, $username, $password, $readonly = false ) {
		if ( $readonly === false ) {
			$imap = @imap_open( "{" . $connect . "}", $username, $password );
		} else {
			$imap = @imap_open( "{" . $connect . "}", $username, $password, OP_READONLY );
		}
		if ( $imap ) {
			return $imap;
		} else {
			throw new Exception( 'Unable to establish connection to IMAP using ' . $connect );
			return false;
		}
	}
}
?>
<div id="wpsc_mailbox" class="tab-pane fade active in">
    <div class="panel panel-default">
        <div class="panel-heading"><h4 class="panel-title">Mailbox</h4></div>
        <div class="panel-body panel-body-wheat">
            <form method="post" class="form-horizontal">
				<div class="form-group">
					<div class="col-xs-12 col-md-12">
						<button class="wpsc_email_filter wpsc_admin_button btn btn-primary btn-sm" data-days="1">Today</button> <button class="wpsc_email_filter wpsc_admin_button btn btn-primary btn-sm" data-days="3">Last 3 Days</button> <button class="wpsc_email_filter wpsc_admin_button btn btn-primary btn-sm" data-days="7">Last 7 Days</button> <button class="wpsc_email_filter wpsc_admin_button btn btn-primary btn-sm" data-days="30">Last 30 Days</button>
					</div>
				</div>
				<div class="form-group">
                    <div class="col-xs-12 col-md-12">
                    	<?php
                    	$sql = "SELECT * FROM " . $wpdb->prefix . "wpsc_imap WHERE imap_type=2 ORDER BY imap_username ASC";
						$accounts = $wpdb->get_results( $sql, ARRAY_A );
					    if ( $wpdb->num_rows > 0 ) {
					    	$count = 0;
					        foreach( $accounts as $account ) {
					        	echo '<span class="wpsc_email_account" style="background-color:' . $colors[$count] . '">' . $account['imap_username'] . '</span>';
								$count++;
							}
						} else {
							$accounts = array();
						}
						?>
					</div>
				</div>
				<div class="form-group">
					<div class="col-xs-12 col-md-12">
						<?php
						$output = '';
						reset( $accounts );
						$count = 0;
						$days = ( isset( $_GET['wpsc_mailbox_days'] ) && $_GET['wpsc_mailbox_days'] != '' && is_numeric( $_GET['wpsc_mailbox_days'] ) ) ? $_GET['wpsc_mailbox_days'] : 3;
						$output .= '<table id="wpsc_admin_email_piping_preview" class="wpsc_admin_datatable table table-striped table-bordered wpsc_fullwidth display">';
                            $output .= '<thead>';
                                $output .= '<th>From</th>';
                                $output .= '<th>Subject</th>';
                                $output .= '<th>Received</th>';
                                $output .= '<th></th>';
                            $output .= '</thead>';
                            $output .= '<tfoot>';
                                $output .= '<th>From</th>';
                                $output .= '<th>Subject</th>';
                                $output .= '<th>Received</th>';
                                $output .= '<th></th>';
                            $output .= '</tfoot>';
                            $output .= '<tbody>';
								foreach ( $accounts as $account ) {
									$server = $account['imap_server'];
									$port = $account['imap_port'];
									if ( $port == '143' ) {
										$argstrings = array(
							                '/imap/novalidate-cert',
							                '/imap/notls'
							            );
									} else {
										$argstrings = array(
							                '/imap/ssl/novalidate-cert',
							                '/imap/notls/ssl/novalidate-cert',
							            );
									}
									$username = $account['imap_username'];
									$password = $account['imap_password'];
									foreach ( $argstrings as $argstring ) {
										$connect = $server . ':' . $port . $argstring;
										try {
											$imap = wpscOpenIMAP( $connect, $username, $password, true );
										}
										catch ( Exception $e ) {
											//echo $e->getMessage();
										}
					                    //$imap = imap_open( "{" . $connect . "}", $username, $password, OP_READONLY );
					                    if ( isset( $imap ) && $imap ) {
					                    	//echo 'Connection established using: ' . $connect;
					                        break;
					                    }
					                }
									$search_args = 'SINCE "' . date( 'j F Y', strtotime( '-' . $days . ' day' ) ) . '"';
									$emails = imap_search( $imap, $search_args );
									if ( $emails ) {
										rsort( $emails );
                                        //$count = 1;
										foreach ( $emails as $email_id ) {
											$uid = imap_uid( $imap, $email_id );
											$header = imap_headerinfo($imap, $email_id );
											if ( isset ( $header->from[0]->personal ) ) {
												$from_name = imap_mime_header_decode( $header->from[0]->personal );
												$from_name = ( isset( $from_name[0]->text ) ) ? $from_name[0]->text : $header->from[0]->personal;
												$from_email = $header->from[0]->mailbox . '@' . $header->from[0]->host;
												$from = $from_name . ' (' . $from_email . ')';
											} else {
												$from_name = imap_mime_header_decode( $header->from[0]->mailbox );
												$from_name = ( isset( $from_name[0]->text ) ) ? $from_name[0]->text : $header->from[0]->mailbox;
												$from_email = $header->from[0]->mailbox . '@' . $header->from[0]->host;
												$from = $from_email;
											}
											$subject = imap_mime_header_decode( $header->subject );
											$subject = ( isset( $subject[0]->text ) ) ? $subject[0]->text : $header->subject;
											$timestamp = ( isset( $header->Date ) ) ? date( "Y-m-d H:i:s", strtotime( $header->Date ) ) : current_time( 'mysql', 1 );
											$attachments = false;
											$structure = imap_fetchstructure( $imap, $email_id );
											if ( isset( $structure->parts ) ) {
												$parts = flattenParts( $structure->parts );
												foreach ( $parts as $partno=>$part ) {
													switch( $part->type ) {
														case 0: // the HTML or plain text part of the email
															break;
														case 1: // multi-part headers, can ignore
															break;
														case 2: // attached message headers, can ignore
															break;
														case 3: // application
														case 4: // audio
														case 5: // image
														case 6: // video
														case 7: // other
															$attachments = true;
															break;
													}
												}
											}
											$output .= '<tr class="email_row" style="background-color:' . $colors[$count] . '" data-id="' . $uid . '" data-account="' . $account['id'] . '">';
	                                            $output .= '<td style="width:35%">' . $from_name . ' (' . $from_email . ')</td>';
	                                            $output .= '<td style="width:50%">' . utf8_decode( imap_utf8( $subject ) ) . '</td>';
	                                            $output .= '<td style="width:10%">' . get_date_from_gmt( $timestamp ) . '</td>';
	                                            $output .= '<td style="width:5%" class="align_centre">';
	                                                if ( $attachments !== false ) {
	                                                    $output .= '<span class="glyphicon glyphicon-paperclip"></span>';
	                                                }
	                                            $output .= '</td>';
	                                        $output .= '</tr>';
										}
									}
									imap_errors();
									imap_alerts();
									imap_close( $imap );
									$count++;
								}
							$output .= '</tbody>';
	                    $output .= '</table>';
						echo $output;
						?>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>