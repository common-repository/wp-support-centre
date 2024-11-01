var editorID;
// ***************************************************************************************************
// set cookie
function setCookie( cname, cvalue, exdays ) {
    var d = new Date();
    d.setTime( d.getTime() + ( exdays * 24 * 60 * 60 * 1000 ) );
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}
// ***************************************************************************************************
// get cookie
function getCookie( cname ) {
    var name = cname + "=";
    var ca = document.cookie.split( ';' );
    for ( var i=0; i<ca.length; i++ ) {
        var c = ca[i];
        while ( c.charAt(0)==' ' ) c = c.substring(1);
        if ( c.indexOf( name ) == 0 ) return c.substring( name.length, c.length );
    }
    return "";
}
// ***************************************************************************************************
// reply templates
// called by ckeditor
function wpsc_template_dialog( editor ) {
    var theID = editor.name;
    jQuery( '#wpsc_admin_templates_select_table' ).attr( 'data-editor', theID );
    jQuery( '#wpsc_templates_dialog' ).modal( 'show' );
}

function initChosen() {
	jQuery( '.wpsc_chosen' ).each( function() {
		if ( jQuery( this ).hasClass( 'wpsc_address_book_chosen' ) ) {
    		jQuery( this ).chosen({
				width: '100%',
				no_results_text: "No contacts found",
				placeholder_text_multiple: "Select contacts",
				placeholder_text_single: "Select contact"
			});
	    } else if ( jQuery( this ).hasClass( 'wpsc_attachment_chosen' ) ) {
			jQuery( this ).chosen({
				width: '100%',
				no_results_text: "No contacts found",
				placeholder_text_multiple: "Select contacts",
				placeholder_text_single: "Select contact"
			});
		} else {
			jQuery( this ).chosen({
				width: '100%'
			});
		}
	});
}
// ***************************************************************************************************
// ckeditor override image button
CKEDITOR.on('instanceReady', function( ev ) {
	var editor = ev.editor;
	var editor_name = editor.name;
	if ( editor_name.indexOf( '_front_' ) == -1 ) {
		var overridecmd = new CKEDITOR.command(editor, {
			exec: function (editor) {
				tb_show('Upload a file', 'media-upload.php?TB_iframe=true&post_id=0', false);
				editorID = editor_name;
				return false;
		    }
		});
		ev.editor.commands.image.exec = overridecmd.exec;
	}
});
// ***************************************************************************************************
// ckeditor check for change and alert on page navigation
function beforeUnload( evt ) {
    for ( var name in CKEDITOR.instances ) {
        if ( CKEDITOR.instances[ name ].checkDirty() )
            return evt.returnValue = "You will lose the changes made in the editor.";
    }
}
if ( window.addEventListener ) {
	window.addEventListener( "beforeunload", beforeUnload, false );
} else {
	window.attachEvent( "onbeforeunload", beforeUnload );
}
// ***************************************************************************************************
// Refresh admin tickets table
function doRefreshAdminTicketsTable() {
    if ( jQuery( '#wpsc_admin_tickets_table_container' ).length ) {
        jQuery( '#wpsc_admin_tickets_table_container' ).html('<img src="' + wpsc_localize_admin.wpsc_plugin_url + '/assets/images/ajax-loader@2x.gif" style="vertical-align: middle;" /> Loading... Please Wait...');
        var data = {
            'action': 'wpsc_doRefreshAdminTicketsTable'
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '#wpsc_admin_tickets_table_container' ).html( response.table );
                    if ( jQuery( '#wpsc_admin_tickets_table' ).is( ':visible' ) ) {
                    	doDataTables( '#wpsc_admin_tickets' );
                    }
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    }
}
// ***************************************************************************************************
// load ticket (admin)
function loadAdminTicket( theID, doRefresh ) {
    if ( doRefresh ) {
        doRefreshAdminTicketsTable();
    }
    jQuery( '#wpsc_view_ticket_' + theID ).html('<img src="' + wpsc_localize_admin.wpsc_plugin_url + '/assets/images/ajax-loader@2x.gif" style="vertical-align: middle;" /> Loading... Please Wait...');
    var data = {
        'action': 'wpsc_get_admin_ticket',
        'ticket_id': theID
    };
    jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
        response = jQuery.trim( response );
        try {
            response = jQuery.parseJSON( response );
            if ( response.status == 'true' && response.ticket != 'false' ) {
                if ( jQuery( 'li[data-id="' + theID + '"]' ).length == 0 ) {
                    jQuery( '<li data-id="' + theID + '" class="wpsc_admin_ticket_tab"><a href="#wpsc_view_ticket_' + theID + '" data-toggle="tab">' + wpsc_localize_admin.wpsc_item + ': ' + theID + ' <i class="glyphicon glyphicon-remove-sign wpsc_tab_close"></i></a></li>' ).appendTo( '#wpsc_admin_tabs' );
                    jQuery( '<div class="tab-pane" id="wpsc_view_ticket_' + theID + '"></div>' ).appendTo( '#admin-tab-content' );
                }
                jQuery( '#wpsc_view_ticket_' + theID ).html( response.ticket );
                doDataTables( '#wpsc_view_ticket_' + theID );
                //jQuery( 'li[data-id="' + theID + '"] a' ).tab( 'show' );
                jQuery( 'a[href="#wpsc_view_ticket_' + theID + '"]' ).trigger( 'click' );
                if ( jQuery( '.wpsc_chosen' ).length ) {
                	initChosen();
                }
                jQuery( "#wpsc_processing" ).modal( 'hide' );
                if ( doRefresh ) {
                    doRefreshAdminTicketsTable();
                }
                var theTickets = [];
                jQuery( '.wpsc_admin_ticket_tab' ).each( function() {
                    theTickets.push( jQuery( this ).closest( "li" ).attr( 'data-id' ) );
                });
                var theIDs = theTickets.join();
                setCookie( 'wpsc_open_tickets', theIDs, 30 );
                setCookie( 'wpsc_active_ticket', theID, 30 );
            } else if ( response.status == 'false' || response.ticket == 'false' ) {
                jQuery( '#wpsc_quick_find' ).val( '' );
                jQuery( "#wpsc_processing" ).modal( 'hide' );
                jQuery( "#wpsc_ticket_not_found" ).modal( 'show' );
            } else {
                console.log(response);
                jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                jQuery( "#wpsc_processing" ).modal( 'hide' );
            }
        }
        catch( err ) {
            console.log( err );
            jQuery( '#wpsc_processing' ).modal( 'hide' );
        }
    });
}
// ***************************************************************************************************
// load ticket (recurring)
function loadRecurringTicket( theID ) {
    jQuery( '#wpsc_view_recurring_ticket_' + theID ).html('<img src="' + wpsc_localize_admin.wpsc_plugin_url + '/assets/images/ajax-loader@2x.gif" style="vertical-align: middle;" /> Loading... Please Wait...');
    var data = {
        'action': 'wpsc_get_recurring_ticket',
        'ticket_id': theID
    };
    jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
        response = jQuery.trim( response );
        try {
            response = jQuery.parseJSON( response );
            if ( response.status == 'true' && response.ticket != 'false' ) {
                jQuery( '<li data-id="' + theID + '" class="wpsc_recurring_ticket_tab"><a href="#wpsc_view_recurring_ticket_' + theID + '" data-toggle="tab">Edit Recurring ' + wpsc_localize_admin.wpsc_item + ' <i class="glyphicon glyphicon-remove-sign wpsc_recurring_tab_close"></i></a></li>' ).appendTo( '#wpsc_admin_recurring_tabs' );
                jQuery( '<div class="tab-pane" id="wpsc_view_recurring_ticket_' + theID + '"></div>' ).appendTo( '.tab-content' );
                jQuery( '#wpsc_view_recurring_ticket_' + theID ).html( response.ticket );
                // init datepicker
                jQuery( '#wpsc_edit_recurring_ticket_date_from_' + theID).datepicker({
                    minDate: 0,
                    dateFormat: "yy-mm-dd"
                });
                jQuery( '#wpsc_admin_recurring_tabs a:last').tab( 'show' );
                jQuery( "#wpsc_processing" ).modal( 'hide' );
            } else {
                console.log(response);
                jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                jQuery( "#wpsc_processing" ).modal( 'hide' );
            }
        }
        catch( err ) {
            console.log( err );
            jQuery( '#wpsc_processing' ).modal( 'hide' );
        }
    });
}
// ***************************************************************************************************
// load ticket (front)
function loadFrontTicket( theID ) {
    var uid = jQuery( '#wpsc_wrap').attr( 'data-id' );
    jQuery( '#wpsc_front_ticket_' + theID ).html('<img src="' + wpsc_localize_admin.wpsc_plugin_url + '/assets/images/ajax-loader@2x.gif" style="vertical-align: middle;" /> Loading... Please Wait...');
    var theTickets = [];
    jQuery( '.wpsc_front_ticket_tab' ).each( function() {
        theTickets.push( jQuery( this ).attr( 'data-id' ) );
    });
    var theIDs = theTickets.join();
    setCookie( 'wpsc_open_tickets_' + uid, theIDs, 30 );
    var data = {
        'action': 'wpsc_get_front_ticket',
        'ticket_id': theID
    };
    jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
        response = jQuery.trim( response );
        try {
            response = jQuery.parseJSON( response );
            if ( response.status == 'true' && response.ticket != 'false' ) {
                jQuery( '<li data-id="' + theID + '" class="wpsc_front_ticket_tab"><a href="#wpsc_front_ticket_' + theID + '" data-toggle="tab">' + wpsc_localize_admin.wpsc_item + ' #' + theID + ' <i class="glyphicon glyphicon-remove-sign wpsc_front_tab_close"></i></a></li>' ).appendTo( '#wpsc_front_tabs' );
                jQuery( '<div class="tab-pane" id="wpsc_front_ticket_' + theID + '"></div>' ).appendTo( '.tab-content' );
                jQuery( '#wpsc_front_ticket_' + theID).html( response.ticket );
                jQuery( '#wpsc_front_tabs a:last').tab( 'show' );
                jQuery( "#wpsc_processing" ).modal( 'hide' );
                setCookie( 'wpsc_active_ticket_' + uid, theID, 30 );
            } else {
                console.log(response);
                jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                jQuery( "#wpsc_processing" ).modal( 'hide' );
            }
        }
        catch( err ) {
            console.log( err );
            jQuery( '#wpsc_processing' ).modal( 'hide' );
        }
    });
}
// ***************************************************************************************************
// sort DOM elements
function getSorted( selector, attrName ) {
    return jQuery( jQuery( selector ).toArray().sort( function( a, b ) {
        var aVal = parseInt( a.getAttribute( attrName ) );
        var bVal = parseInt( b.getAttribute( attrName ) );
        return aVal - bVal;
    }));
}
// ***************************************************************************************************
// tab functions
function doDataTables( targetHref ) {
    if ( targetHref == '#wpsc_admin_tickets' ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_tickets_table' ) ) {
            var theTable = jQuery( '#wpsc_admin_tickets_table' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_tickets_table' ).DataTable({
            	"dom": '<"top">rt<"bottom"lpi>',
                'responsive': {
		            'details': {
		                'type': 'column'
		            }
		        },
                'columnDefs': [
                	{
                		'className': 'control',
			            'orderable': false,
			            'width': '20px',
			            'targets':   0
                	},
                    {
                        'orderable': false,
                        'searchable': false,
                        'targets': 1
                    }
                ],
                'order': [
                    [ 10, 'desc' ]
                ],
                "autoWidth": true
            });
            jQuery( '#wpsc_admin_tickets_table tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref == '#wpsc_ticket_new' ) {
    	jQuery( '#wpsc_notification_ticket_new_client' ).ckeditor({
            height: 280,
            removeButtons: 'wpsctemplates'
        });
        jQuery( '#wpsc_notification_ticket_new_admin' ).ckeditor({
            height: 280,
            removeButtons: 'wpsctemplates'
        });
    } else if ( targetHref == '#wpsc_ticket_reply' ) {
    	jQuery( '#wpsc_notification_ticket_reply_client' ).ckeditor({
            height: 280,
            removeButtons: 'wpsctemplates'
        });
        jQuery( '#wpsc_notification_ticket_reply_admin' ).ckeditor({
            height: 280,
            removeButtons: 'wpsctemplates'
        });
    } else if ( targetHref == '#wpsc_ticket_change' ) {
    	jQuery( '#wpsc_notification_ticket_change_client' ).ckeditor({
            height: 280,
            removeButtons: 'wpsctemplates'
        });
        jQuery( '#wpsc_notification_ticket_change_admin' ).ckeditor({
            height: 280,
            removeButtons: 'wpsctemplates'
        });
    } else if ( targetHref == '#wpsc_admin_new_recurring_ticket' ) {
    	jQuery( '#wpsc_new_recurring_ticket_details' ).ckeditor({
            height: 280,
            removeButtons: 'wpsctemplates'
        });
    } else if ( targetHref == '#wpsc_admin_recurring_tickets' ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_recurring_tickets_table' ) ) {
            var theTable = jQuery( '#wpsc_admin_recurring_tickets_table' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_recurring_tickets_table' ).DataTable({
                'columnDefs': [
                    {
                        'orderable': false,
                        'searchable': false,
                        'targets': 0
                    }
                ],
                'order': [
                    [ 8, 'asc' ]
                ]
            });
            jQuery( '#wpsc_admin_recurring_tickets_table tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref.indexOf( '#wpsc_reminders_' ) > -1 ) {
    	var theID = targetHref.replace( '#wpsc_reminders_', '' );
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_reminders_table_' + theID ) ) {
            var theTable = jQuery( '#wpsc_admin_reminders_table_' + theID ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_reminders_table_' + theID ).DataTable({
                'columnDefs': [
                    {
                        'orderable': false,
                        'searchable': false,
                        'targets': [ 1, 3 ]
                    }
                ],
                'order': [
                    [ 2, 'asc' ]
                ]
            });
            jQuery( '#wpsc_admin_reminders_table_' + theID + ' tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref.indexOf( '#wpsc_participants_' ) > -1 ) {
        var theID = targetHref.replace( '#wpsc_participants_', '' );
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_participants_table_' + theID ) ) {
            var theTable = jQuery( '#wpsc_admin_participants_table_' + theID ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_participants_table_' + theID ).DataTable({
                'columnDefs': [
                    {
                        'orderable': false,
                        'searchable': false,
                        'targets': 0
                    }
                ],
                'order': [
                    [ 1, 'asc' ]
                ]
            });
            jQuery( '#wpsc_admin_participants_table_' + theID + ' tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref.indexOf( '#wpsc_attachments_' ) > -1 ) {
        var theID = targetHref.replace( '#wpsc_attachments_', '' );
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_attachments_table_' + theID ) ) {
            var theTable = jQuery( '#wpsc_admin_attachments_table_' + theID ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_attachments_table_' + theID ).DataTable({
                'columnDefs': [
                    {
                        'orderable': false,
                        'searchable': false,
                        'targets': [ 0, 2 ]
                    }
                ],
                'order': [
                    [ 1, 'asc' ]
                ]
            });
            jQuery( '#wpsc_admin_attachments_table_' + theID + ' tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref == '#wpsc_settings_status' ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_status' ) ) {
            var theTable = jQuery( '#wpsc_admin_status' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_status' ).DataTable({
                'columnDefs': [
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '10%',
                        'targets': 0
                    },
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '20%',
                        'targets': 3
                    },
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '20%',
                        'targets': 4
                    }
                ],
                'order': [
                    [ 1, 'asc' ]
                ],
                'autoWidth': false
            });
            jQuery( '#wpsc_admin_status tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
            jQuery( '#wpsc_admin_status' ).on( 'click', 'tbody td.wpsc_editable_status', function( e ) {
            	var theID = jQuery( this ).attr( 'data-id' );
            	jQuery( '#wpsc_text_status_' + theID ).hide();
            	jQuery( '#save_status_' + theID ).show();
            	jQuery( '#wpsc_status_' + theID ).show().focus();
            });
            jQuery( '#wpsc_admin_status' ).on( 'blur', 'tbody td.wpsc_editable_status', function( e ) {
            	var theID = jQuery( this ).attr( 'data-id' );
            	var theVal = jQuery( '#wpsc_status_' + theID ).val();
            	jQuery( '#wpsc_text_status_' + theID ).html( theVal );
            	jQuery( '#wpsc_text_status_' + theID ).show();
            	jQuery( '#wpsc_status_' + theID ).hide();
            });
            jQuery( '#wpsc_admin_status' ).on( 'click', 'tbody td.wpsc_editable_prefix', function( e ) {
            	var theID = jQuery( this ).attr( 'data-id' );
            	jQuery( '#wpsc_text_prefix_' + theID ).hide();
            	jQuery( '#save_status_' + theID ).show();
            	jQuery( '#wpsc_prefix_' + theID ).show().focus();
            });
            jQuery( '#wpsc_admin_status' ).on( 'blur', 'tbody td.wpsc_editable_prefix', function( e ) {
            	var theID = jQuery( this ).attr( 'data-id' );
            	var theVal = jQuery( '#wpsc_prefix_' + theID ).val();
            	jQuery( '#wpsc_text_prefix_' + theID ).html( theVal );
            	jQuery( '#wpsc_text_prefix_' + theID ).show();
            	jQuery( '#wpsc_prefix_' + theID ).hide();
            });
        }
    } else if ( targetHref == '#wpsc_settings_category' ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_category' ) ) {
            var theTable = jQuery( '#wpsc_admin_category' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_category' ).DataTable({
                'columnDefs': [
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '5%',
                        'targets': 0
                    },
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '10%',
                        'targets': 1
                    },
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '20%',
                        'targets': 3
                    }
                ],
                'order': [
                    [ 2, 'asc' ]
                ],
                'autoWidth': false
            });
            jQuery( '#wpsc_admin_category tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref == '#wpsc_settings_priority' ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_priority' ) ) {
            var theTable = jQuery( '#wpsc_admin_priority' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_priority' ).DataTable({
                'columnDefs': [
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '10%',
                        'targets': 0
                    },
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '20%',
                        'targets': 2
                    },
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '20%',
                        'targets': 3
                    },
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '20%',
                        'targets': 4
                    }
                ],
                'order': [
                    [ 1, 'asc' ]
                ],
                'autoWidth': false
            });
            jQuery( '#wpsc_admin_priority tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
            jQuery( '#wpsc_admin_priority' ).on( 'click', 'tbody td.wpsc_editable_priority', function( e ) {
            	var theID = jQuery( this ).attr( 'data-id' );
            	jQuery( '#wpsc_text_priority_' + theID ).hide();
            	jQuery( '#save_priority_' + theID ).show();
            	jQuery( '#wpsc_priority_' + theID ).show().focus();
            });
            jQuery( '#wpsc_admin_priority' ).on( 'blur', 'tbody td.wpsc_editable_priority', function( e ) {
            	var theID = jQuery( this ).attr( 'data-id' );
            	var theVal = jQuery( '#wpsc_priority_' + theID ).val();
            	jQuery( '#wpsc_text_priority_' + theID ).html( theVal );
            	jQuery( '#wpsc_text_priority_' + theID ).show();
            	jQuery( '#wpsc_priority_' + theID ).hide();
            });
            jQuery( '#wpsc_admin_priority' ).on( 'click', 'tbody td.wpsc_editable_sla', function( e ) {
            	var theID = jQuery( this ).attr( 'data-id' );
            	jQuery( '#wpsc_text_sla_' + theID ).hide();
            	jQuery( '#save_priority_' + theID ).show();
            	jQuery( '#wpsc_sla_' + theID ).show().focus();
            });
            jQuery( '#wpsc_admin_priority' ).on( 'blur', 'tbody td.wpsc_editable_sla', function( e ) {
            	var theID = jQuery( this ).attr( 'data-id' );
            	var theVal = jQuery( '#wpsc_sla_' + theID ).val();
            	jQuery( '#wpsc_text_sla_' + theID ).html( theVal );
            	jQuery( '#wpsc_text_sla_' + theID ).show();
            	jQuery( '#wpsc_sla_' + theID ).hide();
            });
        }
    } else if ( targetHref == '#wpsc_settings_agent' ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_users_table' ) ) {
            var theTable = jQuery( '#wpsc_admin_users_table' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_users_table' ).DataTable({
                'aoColumnDefs': [
                    {
                        'sWidth': '30%',
                        'aTargets': [ 0 ]
                    },
                    {
                        'sWidth': '50%',
                        'aTargets': [ 1 ]
                    },
                    {
                        'sWidth': '10%',
                        'iDataSort': 3,
                        'aTargets': [ 2 ]
                    },
                    {
                        'bVisible': false,
                        'bSearchable': false,
                        'targets': [ 3 ]
                    },
                    {
                        'sWidth': '10%',
                        'iDataSort': 5,
                        'aTargets': [ 4 ]
                    },
                    {
                        'bVisible': false,
                        'bSearchable': false,
                        'targets': [ 5 ]
                    }
                ],
                'order': [ [ 4, 'asc' ], [ 2, 'asc' ], [ 0, 'asc' ], [ 1, 'asc' ] ],
                'autoWidth': false
            });
            jQuery( '#wpsc_admin_users_table tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref == '#wpsc_admin_templates' ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_templates_table' ) ) {
            var theTable = jQuery( '#wpsc_admin_templates_table' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_templates_table' ).DataTable({
                'columnDefs': [
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '10%',
                        'targets': 0
                    },
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '10%',
                        'targets': 2
                    }
                ],
                'order': [
                    [ 1, 'asc' ]
                ],
                'autoWidth': false,
                "oLanguage": {
                    "oPaginate": {
                        "sFirst": "<<", // This is the link to the first page
                        "sPrevious": "<", // This is the link to the previous page
                        "sNext": ">", // This is the link to the next page
                        "sLast": ">>" // This is the link to the last page
                    },
                    "sInfo": "_START_ to _END_ (_TOTAL_)",
                    "sInfoEmpty": "0 Found",
                    "sInfoFiltered": " [_MAX_]"
                }
            });
            jQuery( '#wpsc_admin_templates_table tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref == '#wpsc_admin_piping_catch_all' || targetHref == '#wpsc_mailbox' ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_email_piping_preview' ) ) {
            var theTable = jQuery( '#wpsc_admin_email_piping_preview' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_email_piping_preview' ).DataTable({
                'columnDefs': [
                    {
                        'orderable': false,
                        'searchable': false,
                        'targets': 3
                    }
                ],
                'order': [
                    [ 2, 'desc' ]
                ],
                'autoWidth': false
            });
            jQuery( '#wpsc_admin_email_piping_preview tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref.indexOf( '#wpsc_view_ticket_' ) > -1 ) {
        var theID = targetHref.replace( '#wpsc_view_ticket_', '' );
        jQuery( '#wpsc_admin_ticket_note_' + theID ).ckeditor({
        	height: 280
        });
    } else if ( targetHref.indexOf( '#wpsc_account_' ) > -1 ) {
        var theID = targetHref.replace( '#wpsc_account_', '' );
        jQuery( '#wpsc_account_information_' + theID ).ckeditor({
        	height: 280,
            removeButtons: 'wpsctemplates'
        });
    } else if ( targetHref == '#wpsc_admin_new_ticket' ) {
        jQuery( '#wpsc_admin_new_ticket_details' ).ckeditor({
        	height: 280
        });
    } else if ( targetHref == '#wpsc_front_new_ticket' ) {
        jQuery( '#wpsc_front_new_ticket_details' ).ckeditor({
        	height: 280,
            removeButtons: 'wpsctemplates'
        });
    } else if ( targetHref.indexOf( '#wpsc_view_recurring_ticket_' ) > -1 ) {
        var theID = targetHref.replace( '#wpsc_view_recurring_ticket_', '' );
        jQuery( '#wpsc_edit_recurring_ticket_details_' + theID ).ckeditor({
        	height: 280
        });
    } else if ( targetHref == '#wpsc_front_tickets' || targetHref == '#wpsc_front_datatable' ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_front_tickets_table' ) ) {
            var theTable = jQuery( '#wpsc_front_tickets_table' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_front_tickets_table' ).DataTable({
                'responsive': {
		            'details': {
		                'type': 'column'
		            }
		        },
                'columnDefs': [
					{
                		'className': 'control',
			            'orderable': false,
			            'width': '20px',
			            'targets':   0
                	},
                    {
                        'width': '10%',
                        'targets': 1
                    },
                    {
                        'width': '25%',
                        'targets': 3
                    },
                    {
                        'width': '20%',
                        'targets': 5
                    },
                    {
                        'width': '20%',
                        'targets': 7
                    }
                ],
                'order': [
                    [ 7, 'desc' ]
                ],
                'autoWidth': false
            });
            jQuery( '#wpsc_front_tickets_table tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    } else if ( targetHref.indexOf( '#wpsc_front_ticket_' ) > -1 ) {
        var theID = targetHref.replace( '#wpsc_front_ticket_', '' );
        jQuery( '#wpsc_front_ticket_note_' + theID ).ckeditor({
        	height: 280,
            removeButtons: 'wpsctemplates'
        });
    } else if ( targetHref == '#wpsc_admin_new_template' ) {
    	jQuery( '#wpsc_new_template' ).ckeditor({
    		height: 280,
            removeButtons: 'wpsctemplates'
        });
    } else if ( targetHref == '#wpsc_settings_email' ) {
    	jQuery( '#wpsc_admin_signature' ).ckeditor({
    		height: 280,
            removeButtons: 'wpsctemplates'
        });
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_imap_accounts' ) ) {
            var theTable = jQuery( '#wpsc_admin_imap_accounts' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_imap_accounts' ).DataTable({
                'columnDefs': [
                    {
                        'width': '18%',
                        'targets': [ 0, 4 ]
                    },
                    {
                    	'width': '10%',
                    	'targets': 1
                    },
                    {
                        'orderable': false,
                        'searchable': false,
                        'width': '18%',
                        'targets': 3
                    },
                    {
                    	'width': '10%',
                    	'targets': 5
                    }
                ],
                'autoWidth': false
            });
            jQuery( '#wpsc_admin_imap_accounts tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    }
}
// ***************************************************************************************************
// media upload functions
window.send_to_editor = function(html) {
    var image_url = jQuery('img',html).attr('src');
    CKEDITOR.instances[editorID].insertHtml( html );
    tb_remove();
};
// ***************************************************************************************************
// document ready
// ***************************************************************************************************
jQuery( document ).ready( function() {
	var theTitle = jQuery(this).attr('title');
	// ***************************************************************************************************
	// remove hidden class from bootstrap alert
	jQuery( '.wpsc-bootstrap-alert' ).removeClass( 'hidden' );
	// ***************************************************************************************************
	// email piping method
	jQuery( document ).on( 'change', 'input[name=wpsc_email_method]', function() {
		var theOption = jQuery( 'input[name=wpsc_email_method]:checked' ).val();
		if ( theOption == '0' ) {
			jQuery( '#wpsc_email_method_piping_settings' ).hide();
			jQuery( '#wpsc_email_method_imap_settings' ).hide();
		} else if ( theOption == '1' ) {
			jQuery( '#wpsc_email_method_piping_settings' ).show();
			jQuery( '#wpsc_email_method_imap_settings' ).hide();
		} else if ( theOption == '2' ) {
			jQuery( '#wpsc_email_method_piping_settings' ).hide();
			jQuery( '#wpsc_email_method_imap_settings' ).show();
		}
	});

	// ***************************************************************************************************
	// front end category selection
	jQuery( document ).on( 'change', '#wpsc_front_new_ticket_category', function() {
		var theVal = jQuery( this ).val();
	});
	// ***************************************************************************************************
	// browser resize functions
	jQuery( window ).resize( function() {
		jQuery.fn.dataTable
	        .tables( { visible: true, api: true } )
	        .columns.adjust()
	        .draw();
	});
    // ***************************************************************************************************
    // init colour picker
    jQuery( '.wpsc_colour_picker' ).each( function() {
        jQuery( this ).wpColorPicker({
            change: function( event, ui ) {
                var theID = jQuery( this ).attr( 'data-id' );
                var theType = jQuery( this ).attr( 'data-type' );
                jQuery( '#save_' + theType + '_' + theID ).show();
            }
        });
    });
    // ***************************************************************************************************
    // detect enter pressed
    jQuery( ':input' ).keypress( function( e ) {
        if ( e.which === 13 ) {
            e.preventDefault();
            var theFocus = jQuery( ':focus' );
            var theForm = theFocus.closest( 'form ' ).attr( 'id' );
            if ( theForm == 'wpsc_quick_find_form' ) {
                jQuery( '#wpsc_quick_find_button' ).click();;
            }
        }
    });
    // ***************************************************************************************************
    // refresh admin tickets table
    jQuery( document ).on( 'click', '.wpsc_tickets_refresh', function() {
        document.location.href = document.location.href;
    });
    // ***************************************************************************************************
    // initialise chosen
    initChosen();
    // ***************************************************************************************************
    // check for and display notifications
    if ( ( wpsc_localize_admin.wpsc_is_agent == 'true' || wpsc_localize_admin.wpsc_is_super == 'true' ) && wpsc_localize_admin.wpsc_page == 'wp-support-centre' ) {
        setInterval( function() {
            if ( !( 'Notification' in window ) ) {
                console.log('Notifications not supported');
            } else {
                data = {
                    'action': 'wpsc_get_notifications'
                };
                jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                    response = jQuery.trim( response );
                    try {
                        response = jQuery.parseJSON( response );
                        if ( response.status == 'true' ) {
                            var wpscNew = response.wpscNew;
                            var wpscReply = response.wpscReply;
                            var wpscSLA = response.wpscSLA;
                            var theIcon = wpsc_localize_admin.wpsc_plugin_url + '/assets/images/support-centre-60x60.png';
                            if ( wpscNew != '' ) {
                                var theIds = wpscNew.split( ',' );
                                jQuery.each( theIds, function( index, value ) {
                                	var theDateTime = new Date().toLocaleString(wpsc_localize_admin.wpsc_locale);
                                	var title = 'New Ticket (' + theDateTime + ')';
                                    var theBody = 'A new ticket has been submitted: ' + value;
                                    var options = {
	                                    tag: 'wpscNew' + value,
	                                    body: theBody,
	                                    icon: theIcon,
	                                    requireInteraction: true
	                                };
                                    Notification.requestPermission( function() {
                                        var notification = new Notification( title, options );
                                        notification.onclick = function() {
                                        	var tag = notification.tag;
                                        	var theID = tag.replace( 'wpscNew', '' );
                                        	if ( jQuery( '#wpsc_view_ticket_' + theID ).length == 0 ) {
								                jQuery( "#wpsc_processing" ).modal( 'show' );
								                loadAdminTicket( theID, false );
								            } else {
								            	jQuery( 'a[href="#wpsc_view_ticket_' + theID + '"]' ).trigger( 'click' );
								            }
								            window.focus();
                                        	notification.close();
                                        };
                                    });
                                });
                            }
                            if ( wpscReply != '' ) {
                                var theIds = wpscReply.split( ',' );
                                jQuery.each( theIds, function( index, value ) {
                                	var theDateTime = new Date().toLocaleString(wpsc_localize_admin.wpsc_locale);
                                	var title = 'New Reply (' + theDateTime + ')';
                                    var theBody = 'A new reply has been submitted for ticket: ' + value;
                                    var options = {
	                                    tag: 'wpscReply' + value,
	                                    body: theBody,
	                                    icon: theIcon,
	                                    requireInteraction: true
	                                };
                                    Notification.requestPermission( function() {
                                        var notification = new Notification( title, options );
                                        notification.onclick = function() {
                                        	var tag = notification.tag;
                                        	var theID = tag.replace( 'wpscReply', '' );
                                        	if ( jQuery( '#wpsc_view_ticket_' + theID ).length == 0 ) {
								                jQuery( "#wpsc_processing" ).modal( 'show' );
								                loadAdminTicket( theID, false );
								            } else {
								            	jQuery( 'a[href="#wpsc_view_ticket_' + theID + '"]' ).trigger( 'click' );
								            }
								            window.focus();
                                        	notification.close();
                                        };
                                    });
                                });
                            }
                            if ( wpscNew != '' || wpscReply != '' ) {
                                doRefreshAdminTicketsTable();
                            } else {
                            	var wpscUpdated = jQuery.parseJSON( response.wpscUpdated );
                            	jQuery( wpscUpdated ).each( function() {
                            		var updID = jQuery( this ).attr( 'id' );
                            		var updBack = jQuery( this ).attr( 'background' );
                            		var updText = jQuery( this ).attr( 'text' );
                            		/*jQuery( '#wpsc_ticket_updated_' + updID ).css({
                            			'backgroundColor': updBack,
                            			'color': updText
                            		});*/
                            		jQuery( '#wpsc_ticket_updated_' + updID ).attr( 'style', function( i, s ) {
                           				return 'background-color:' + updBack + ' !important;color:' + updText + ' !important;';
                            		});
                            	});
                            	var wpscReminders = jQuery.parseJSON( response.wpscReminders );
                            	jQuery( wpscReminders ).each( function() {
                            		var ticket_id = jQuery( this ).attr( 'ticket_id' );
                            		var theDateTime = new Date().toLocaleString(wpsc_localize_admin.wpsc_locale);
                            		var title = 'Attention Required (' + theDateTime + ')';
                                    var theBody = jQuery( this ).attr( 'subject' );
                                    var options = {
	                                    tag: 'wpscReminder' + ticket_id,
	                                    body: theBody,
	                                    icon: theIcon,
	                                    requireInteraction: true
	                                };
                                    Notification.requestPermission( function() {
                                        var notification = new Notification( title, options );
                                        notification.onclick = function( id ) {
                                        	var tag = notification.tag;
                                        	var theID = tag.replace( 'wpscReminder', '' );
                                        	if ( jQuery( '#wpsc_view_ticket_' + theID ).length == 0 ) {
								                jQuery( "#wpsc_processing" ).modal( 'show' );
								                loadAdminTicket( theID, false );
								            } else {
								            	jQuery( 'a[href="#wpsc_view_ticket_' + theID + '"]' ).trigger( 'click' );
								            }
								            window.focus();
                                        	notification.close();
                                        };
                                    });
                            	});
                            }
                        } else {
                            console.log(response);
                        }
                    }
                    catch( err ) {
                        console.log( err );
                    }
                });
            }
        }, 30000 );
    }
    // ***************************************************************************************************
    // insert reply template
    jQuery( document ).on( 'click', '.wpsc_template_insert_row', function() {
        var theEditor = jQuery( '#wpsc_admin_templates_select_table' ).attr( 'data-editor' );
        var theID = jQuery( this ).attr( 'data-id' );
        var ticketID = theEditor.replace( 'wpsc_admin_ticket_note_', '' );
        jQuery( '#wpsc_processing' ).modal( 'show' );
        jQuery( '#wpsc_templates_dialog' ).modal( 'hide' );
        data = {
            'action': 'wpsc_get_template',
            'template_id': theID,
            'ticket_id': ticketID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            jQuery( '#wpsc_processing' ).modal( 'hide' );
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    CKEDITOR.instances[theEditor].insertHtml( response.template );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // open selected tickets
    jQuery( document ).on( 'click', '#wpsc_admin_open_selected', function() {
    	jQuery( '#wpsc_processing' ).modal( 'show' );
        jQuery( '.wpsc_select_ticket' ).each( function() {
            if ( jQuery( this ).is( ':checked' ) ) {
                var theID = jQuery( this ).val();
                loadAdminTicket( theID, false );
            }
        });
    });
    // ***************************************************************************************************
    // init autocomplete - wpsc_admin_client_autocomplete_recurring
    jQuery( '#wpsc_admin_client_autocomplete_recurring' ).autocomplete({
        source: function( req, response ) {
            jQuery.getJSON( wpsc_localize_admin.wpsc_ajax_url + '?action=wpsc_registered_users_search', req, response );
        },
        change: function( event, ui ) {
            if ( ui.item ) {
                jQuery( '#wpsc_new_recurring_ticket_client_id' ).val( ui.item.id );
                jQuery( '#wpsc_new_recurring_ticket_client_email' ).val( ui.item.email ).attr( 'readonly', true );
                jQuery( '#wpsc_new_recurring_ticket_client_phone' ).val( ui.item.phone );
            } else {
                jQuery( '#wpsc_new_recurring_ticket_client_id' ).val( '0' );
                jQuery( '#wpsc_new_recurring_ticket_client_email' ).val( '' ).attr( 'readonly', false );
                jQuery( '#wpsc_new_recurring_ticket_client_phone' ).val( '' );
            }
        },
        minLength: 3
    });
    // ***************************************************************************************************
    // init autocomplete - wpsc_admin_client_autocomplete
    jQuery( '#wpsc_admin_client_autocomplete' ).autocomplete({
        source: function( req, response ) {
            jQuery.getJSON( wpsc_localize_admin.wpsc_ajax_url + '?action=wpsc_registered_users_search', req, response );
        },
        change: function( event, ui ) {
            if ( ui.item ) {
                jQuery( '#wpsc_admin_new_ticket_client_id' ).val( ui.item.id );
                jQuery( '#wpsc_admin_new_ticket_client' ).val( ui.item.value );
                jQuery( '#wpsc_admin_new_ticket_client_email' ).val( ui.item.email );
                jQuery( '#wpsc_admin_new_ticket_phone' ).val( ui.item.phone );
                jQuery( '#wpsc_admin_new_ticket_to' ).val( ui.item.email ).removeClass( 'wpsc_admin_new_ticket_validate' );
                jQuery( '#wpsc_admin_new_ticket_to_required' ).hide();
            } else {
                var theVal = jQuery( this ).val();
                var theEmail = jQuery( '#wpsc_admin_new_ticket_to' ).val();
                jQuery( '#wpsc_admin_new_ticket_client_id' ).val( '0' );
                jQuery( '#wpsc_admin_new_ticket_client' ).val( theVal );
                jQuery( '#wpsc_admin_new_ticket_client_email' ).val( theEmail );
                jQuery( '#wpsc_admin_new_ticket_phone' ).val( '' );
                jQuery( '#wpsc_admin_new_ticket_to_required' ).show();
                jQuery( '#wpsc_admin_new_ticket_to' ).val( '' ).addClass( 'wpsc_admin_new_ticket_validate' );
            }
        },
        minLength: 3
    });
    // ***************************************************************************************************
    // init autocomplete - wpsc_admin_new_ticket_client_email
    jQuery( '#wpsc_admin_new_ticket_client_email' ).autocomplete({
        source: function( req, response ) {
            jQuery.getJSON( wpsc_localize_admin.wpsc_ajax_url + '?action=wpsc_registered_users_search', req, response );
        },
        change: function( event, ui ) {
            if ( ui.item ) {
                jQuery( '#wpsc_admin_new_ticket_client_id' ).val( ui.item.id );
                jQuery( '#wpsc_admin_new_ticket_client' ).val( ui.item.value );
                jQuery( '#wpsc_admin_client_autocomplete' ).val( ui.item.value );
                jQuery( '#wpsc_admin_new_ticket_client_email' ).val( ui.item.email );
                jQuery( '#wpsc_admin_new_ticket_phone' ).val( ui.item.phone );
                jQuery( '#wpsc_admin_new_ticket_to' ).val( ui.item.email ).removeClass( 'wpsc_admin_new_ticket_validate' );
                jQuery( '#wpsc_admin_new_ticket_to_required' ).hide();
            } else {
                var theVal = jQuery( this ).val();
                var theClient = jQuery( '#wpsc_admin_client_autocomplete' ).val();
                jQuery( '#wpsc_admin_new_ticket_client_id' ).val( '0' );
                jQuery( '#wpsc_admin_new_ticket_client' ).val( theClient );
                jQuery( '#wpsc_admin_new_ticket_client_email' ).val( theVal );
                jQuery( '#wpsc_admin_new_ticket_phone' ).val( '' );
                jQuery( '#wpsc_admin_new_ticket_to_required' ).show();
                jQuery( '#wpsc_admin_new_ticket_to' ).val( theVal ).addClass( 'wpsc_admin_new_ticket_validate' );
            }
        },
        minLength: 3
    });
    // ***************************************************************************************************
    // init autocomplete - wpsc_ticket_filter_client
    jQuery( '#wpsc_ticket_filter_client' ).autocomplete({
        source: function( req, response ) {
            jQuery.getJSON( wpsc_localize_admin.wpsc_ajax_url + '?action=wpsc_registered_users_search', req, response );
        },
        select: function( event, ui ) {
            jQuery( '#wpsc_ticket_filter_client_id' ).val( ui.item.id );
        },
        minLength: 3
    });
    // ***************************************************************************************************
    // init datepicker - wpsc_recurring_ticket_date_from
    jQuery( '#wpsc_recurring_ticket_date_from' ).datepicker({
        minDate: 0,
        dateFormat: "yy-mm-dd"
    });
    // ***************************************************************************************************
    // init datepicker - wpsc_ticket_filter_date_from
    jQuery( '#wpsc_ticket_filter_date_from' ).datepicker({
        maxDate: 0,
        dateFormat: "yy-mm-dd",
        onSelect: function(selected) {
            jQuery( '#wpsc_ticket_filter_date_to' ).datepicker( 'option', 'minDate', selected );
            if ( jQuery( '#wpsc_ticket_filter_date_to' ).datepicker( 'getDate') == null || jQuery( '#wpsc_ticket_filter_date_to' ).datepicker( 'getDate') == '' ) {
                jQuery( '#wpsc_ticket_filter_date_to' ).datepicker( 'setDate', new Date() );
            }
        }
    });
    // ***************************************************************************************************
    // init datepicker - wpsc_ticket_filter_date_to
    jQuery( '#wpsc_ticket_filter_date_to' ).datepicker({
        maxDate: 0,
        dateFormat: "yy-mm-dd",
        onSelect: function(selected) {
            jQuery( '#wpsc_ticket_filter_date_from' ).datepicker( 'option', 'maxDate', selected );
            if ( jQuery( '#wpsc_ticket_filter_date_from' ).datepicker( 'getDate') == null || jQuery( '#wpsc_ticket_filter_date_from' ).datepicker( 'getDate') == '' ) {
                jQuery( '#wpsc_ticket_filter_date_from' ).datepicker( 'setDate', jQuery( '#wpsc_ticket_filter_date_to' ).datepicker( 'getDate') );
            }
        }
    });
    if ( jQuery( '#wpsc_ticket_filter_date_from' ).val() != '' ) {
        jQuery( '#wpsc_ticket_filter_date_to' ).datepicker( 'option', 'minDate', jQuery( '#wpsc_ticket_filter_date_from' ).datepicker( 'getDate') );
    }
    if ( jQuery( '#wpsc_ticket_filter_date_to' ).val() != '' ) {
        jQuery( '#wpsc_ticket_filter_date_from' ).datepicker( 'option', 'maxDate', jQuery( '#wpsc_ticket_filter_date_to' ).datepicker( 'getDate') );
    }
    // ***************************************************************************************************
    // select / deselect all tickets
    jQuery( document ).on( 'click', '#wpsc_select_all', function() {
        if ( jQuery( this ).is( ':checked' ) ) {
            jQuery( '.wpsc_select_ticket' ).prop( 'checked', true );
            jQuery( '#wpsc_ticket_actions').show();
        } else {
            jQuery( '.wpsc_select_ticket' ).prop( 'checked', false );
            jQuery( '#wpsc_ticket_actions').hide();
        }
    });
    // ***************************************************************************************************
    // select / deselect single ticket
    jQuery( document ).on( 'click', '.wpsc_select_ticket', function() {
        if ( jQuery( this ).is( ':checked' ) ) {
            if ( jQuery( '.wpsc_select_ticket' ).length == jQuery( '.wpsc_select_ticket:checked' ).length ) {
                jQuery( '#wpsc_select_all' ).prop( 'checked', true );
            } else {
                jQuery( '#wpsc_select_all' ).prop( 'checked', false );
            }
        } else {
            jQuery( '#wpsc_select_all' ).prop( 'checked', false );
        }
        if ( jQuery( '.wpsc_select_ticket:checked' ).length == 0 ) {
            jQuery( '#wpsc_ticket_actions').hide();
        } else {
            jQuery( '#wpsc_ticket_actions').show();
        }
    });
    // ***************************************************************************************************
    // wpsc_admin_apply_actions
    jQuery( document ).on( 'click', '#wpsc_admin_apply_actions', function( e ) {
        var theSelectedIDs = '';
        jQuery( '.wpsc_select_ticket:checked' ).each( function() {
            var theVal = jQuery( this ).val();
            if ( theSelectedIDs == 'undefined' || theSelectedIDs == '' ) {
                theSelectedIDs = theVal;
            } else {
                theSelectedIDs = theSelectedIDs + ',' + theVal;
            }
        });
        var wpsc_ticket_action_status = jQuery( '#wpsc_ticket_action_status' ).val();
        var wpsc_ticket_action_category = jQuery( '#wpsc_ticket_action_category' ).val();
        var wpsc_ticket_action_agent = jQuery( '#wpsc_ticket_action_agent' ).val();
        var wpsc_ticket_action_priority = jQuery( '#wpsc_ticket_action_priority' ).val();
        if ( wpsc_ticket_action_status == '' && wpsc_ticket_action_category == '' && wpsc_ticket_action_agent == '' && wpsc_ticket_action_priority == '' ) {
            return false;
        } else {
            jQuery( "#wpsc_processing" ).modal( 'show' );
            var data = {
                'action': 'wpsc_admin_apply_actions',
                'theSelectedIDs': theSelectedIDs,
                'wpsc_ticket_action_status': wpsc_ticket_action_status,
                'wpsc_ticket_action_category': wpsc_ticket_action_category,
                'wpsc_ticket_action_agent': wpsc_ticket_action_agent,
                'wpsc_ticket_action_priority': wpsc_ticket_action_priority
            };
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        document.location.href = document.location.href;
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // wpsc_admin_apply_filter
    jQuery( document ).on( 'click', '#wpsc_admin_apply_filter', function( e ) {
        var hasData = false;
        jQuery( '.wpsc_ticket_filter' ).each( function() {
            if ( jQuery( this).val() != '' ) {
                hasData = true;
            }
        });
        if ( false === hasData ) {
            return false;
        }
        if ( jQuery( '#wpsc_ticket_filter_date_from' ).val() != '' && jQuery( '#wpsc_ticket_filter_date_to' ).val() == '' ) {
            jQuery( '#wpsc_ticket_filter_date_to' ).addClass( 'wpsc_field_error' );
            return false;
        }
        if ( jQuery( '#wpsc_ticket_filter_date_to' ).val() != '' && jQuery( '#wpsc_ticket_filter_date_from' ).val() == '' ) {
            jQuery( '#wpsc_ticket_filter_date_from' ).addClass( 'wpsc_field_error' );
            return false;
        }
        jQuery( "#wpsc_processing" ).modal( 'show' );
        jQuery( '#wpsc_filter_form' ).submit();
    });
    // ***************************************************************************************************
    // wpsc_admin_clear_filter
    jQuery( document ).on( 'click', '#wpsc_admin_clear_filter', function( e ) {
        jQuery( '.wpsc_ticket_filter' ).val('');
        jQuery( "#wpsc_processing" ).modal( 'show' );
        jQuery( '#wpsc_filter_form' ).submit();
    });
    // ***************************************************************************************************
    // wpsc_admin_search_button
    jQuery( document ).on( 'click', '#wpsc_admin_search_button', function( e ) {
        jQuery( "#wpsc_processing" ).modal( 'show' );
        jQuery( this ).closest( 'form' ).submit();
    });
    // ***************************************************************************************************
    // wpsc_admin_clear_search
    jQuery( document ).on( 'click', '#wpsc_admin_clear_search', function( e ) {
        jQuery( "#wpsc_processing" ).modal( 'show' );
        document.location.href = document.location.href;
    });
    // ***************************************************************************************************
    // wpsc_admin_ticket_row
    jQuery( document ).on( 'click', '.wpsc_admin_ticket_row td', function() {
        if ( false === jQuery( this ).hasClass( 'wpsc_select_ticket_td' ) ) {
            var theID = jQuery( this ).closest( 'tr' ).attr( 'data-id' );
            if ( jQuery( '#wpsc_view_ticket_' + theID ).length == 0 ) {
                jQuery( "#wpsc_processing" ).modal( 'show' );
                loadAdminTicket( theID, false );
            } else {
            	jQuery( 'a[href="#wpsc_view_ticket_' + theID + '"]' ).trigger( 'click' );
            }
        }
    });
    // ***************************************************************************************************
    // wpsc_user_row
    jQuery( document ).on( 'click', '.wpsc_user_row td', function() {
        if ( false === jQuery( this ).hasClass( 'wpsc_user_agent_td' ) && false === jQuery( this ).hasClass( 'wpsc_user_supervisor_td' ) ) {
            var user_id = jQuery( this ).closest( 'tr' ).attr( 'data-user-id' );
            var display_name = jQuery( this ).closest( 'tr' ).attr( 'data-display-name' );
            if ( jQuery( '#wpsc_view_user_' + user_id ).length == 0 ) {
                var data = {
                    'action': 'wpsc_get_user_data',
                    'user_id': user_id,
                    'display_name': display_name
                };
                jQuery( "#wpsc_processing" ).modal( 'show' );
                jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                    response = jQuery.trim( response );
                    try {
                        response = jQuery.parseJSON( response );
                        if ( response.status == 'true' ) {
                            jQuery( '<li data-id="' + user_id + '" class="wpsc_admin_user_tab" id="wpsc_account_tab_' + user_id + '"><a href="#wpsc_account_' + user_id + '" data-toggle="tab">' + display_name + ' (' + user_id + ') <i class="glyphicon glyphicon-remove-sign wpsc_user_tab_close"></i></a></li>' ).appendTo( '#wpsc_admin_settings_users_tabs' );
                            jQuery( response.content ).appendTo( '#wpsc_users_content' );
                            jQuery( '#wpsc_admin_settings_users_tabs a:last').tab( 'show' );
                            jQuery( '#wpsc_account_information_' + user_id ).ckeditor({
                            	height: 280,
                                removeButtons: 'wpsctemplates'
                            });
                            jQuery( "#wpsc_processing" ).modal( 'hide' );
                        } else {
                            console.log(response);
                            jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                            jQuery( "#wpsc_processing" ).modal( 'hide' );
                        }
                    }
                    catch( err ) {
                        console.log( err );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    }
                });
            }
        }
    });
    // ***************************************************************************************************
    // wpsc_tab_close
    jQuery( document ).on( 'click', '.wpsc_tab_close', function() {
        var theID = jQuery( this).closest( 'li' ).attr( 'data-id' );
        var editor1 = CKEDITOR.instances[ 'wpsc_admin_ticket_note_' + theID];
		if ( editor1 ) {
	        if ( editor1.checkDirty() ) {
	        	var doClose = confirm( "You will lose the changes made in the editor." );
	        	if ( doClose != true ) {
	        		return;
	        	}
	        }
		}
        var editor2 = CKEDITOR.instances[ 'wpsc_account_information_' + theID];
        if ( editor2 ) {
	        if ( editor2.checkDirty() ) {
	        	var doClose = confirm( "You will lose the changes made in the editor." );
	        	if ( doClose != true ) {
	        		return;
	        	}
	        }
	    }
	    if ( editor1 ) {
	    	editor1.resetDirty();
			editor1.destroy();
		}
		if ( editor2 ) {
			editor2.resetDirty();
			editor2.destroy();
		}
		jQuery( this).closest( 'li' ).remove();
        jQuery( '#wpsc_view_ticket_' + theID ).remove();
        var theTickets = [];
        jQuery( '.wpsc_admin_ticket_tab' ).each( function() {
            theTickets.push( jQuery( this ).closest( "li" ).attr( 'data-id' ) );
        });
        var theIDs = theTickets.join();
        setCookie( 'wpsc_open_tickets', theIDs, 30 );
        setCookie( 'wpsc_active_ticket', '', 30 );
        jQuery( '#wpsc_admin_tabs a:first').tab( 'show' );
    });
    // ***************************************************************************************************
    // wpsc_user_tab_close
    jQuery( document ).on( 'click', '.wpsc_user_tab_close', function() {
        var theID = jQuery( this).closest( 'li' ).remove().attr( 'data-id' );
        jQuery( '#wpsc_account_' + theID ).remove();
        jQuery( '#wpsc_admin_settings_users_tabs a:first').tab( 'show' );
    });
    // ***************************************************************************************************
    // wpsc_admin_new_ticket_to
    jQuery( document ).on( 'change', '#wpsc_admin_new_ticket_to', function() {
        var theVal = jQuery( this ).val();
        jQuery( '#wpsc_admin_new_ticket_client_email' ).val( theVal );
        jQuery( '#wpsc_admin_new_ticket_client_id' ).val( '0' );
        jQuery( '#wpsc_admin_new_ticket_to_required' ).show();
        jQuery( '#wpsc_admin_new_ticket_to' ).addClass( 'wpsc_admin_new_ticket_validate' );
    });
    // ***************************************************************************************************
    // new ticket from thread
    jQuery( document ).on( 'click', '.glyphicon-new-window', function() {
        var thread_id = jQuery( this ).attr( 'data-id' );
        var client_id = jQuery( this ).attr( 'data-client' );
        var data = {
            'action': 'wpsc_new_ticket_from_thread',
            'thread_id': thread_id,
            'client_id': client_id
        };
        jQuery( "#wpsc_processing" ).modal( 'show' );
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '.nav-tabs a[href="#wpsc_admin_new_ticket"' ).tab( 'show' );
                    jQuery( '#wpsc_admin_client_autocomplete' ).val( response.client );
                    jQuery( '#wpsc_admin_new_ticket_client' ).val( response.client );
                    jQuery( '#wpsc_admin_new_ticket_client_email' ).val( response.client_email );
                    jQuery( '#wpsc_admin_new_ticket_to' ).val( response.client_email );
                    jQuery( '#wpsc_admin_new_ticket_client_id' ).val( client_id );
                    var editor = CKEDITOR.instances['wpsc_admin_new_ticket_details'];
                    if ( editor ) {
                    	CKEDITOR.instances['wpsc_admin_new_ticket_details'].setData( response.thread );
                    } else {
                    	jQuery( '#wpsc_admin_new_ticket_details' ).val( response.thread );
                    }
                    jQuery( '#wpsc_admin_new_ticket_subject' ).focus();
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // copy thread to ticket
    jQuery( document ).on( 'click', '.glyphicon-share-alt', function() {
        var thread_id = jQuery( this ).attr( 'data-thread_id' );
        var new_ticket_id = prompt( 'Please enter the ticket number to copy this thread to', '' );
        if ( new_ticket_id != null ) {
            var data = {
                'action': 'wpsc_copy_thread_to_ticket',
                'thread_id': thread_id,
                'ticket_id': new_ticket_id
            };
            jQuery( "#wpsc_processing" ).modal( 'show' );
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        document.location.href = document.location.href;
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // resend thread notification
    jQuery( document ).on( 'click', '.glyphicon-envelope', function() {
    	var do_resend = confirm( 'Press OK to resend notifications.' );
    	if ( do_resend == true ) {
	        var ticket_id = jQuery( this ).attr( 'data-ticket-id' );
	        var thread_id = jQuery( this ).attr( 'data-thread_id' );
	        var data = {
	            'action': 'wpsc_resend_thread_notifications',
	            'ticket_id': ticket_id,
	            'thread_id': thread_id
	        };
	        jQuery( "#wpsc_processing" ).modal( 'show' );
	        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
	            response = jQuery.trim( response );
	            try {
	                response = jQuery.parseJSON( response );
	                if ( response.status == 'true' ) {
	                    if ( response.client == 'true' && response.admin == 'true' ) {
	                        var theMessage = wpsc_localize_admin.wpsc_client + ' and admin.';
	                    } else if ( response.client == 'true' ) {
	                        var theMessage = wpsc_localize_admin.wpsc_client + '.';
	                    } else if ( response.admin == 'true' ) {
	                        var theMessage = 'admin.';
	                    }
	                    jQuery( '#wpsc_thread_resend_message' ).html( theMessage );
	                    jQuery( "#wpsc_thread_notifications_sent_dialog" ).modal( 'show' );
	                    jQuery( "#wpsc_processing" ).modal( 'hide' );
	                } else {
	                    console.log(response);
	                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
	                    jQuery( "#wpsc_processing" ).modal( 'hide' );
	                }
	            }
	            catch( err ) {
	                console.log( err );
	                jQuery( '#wpsc_processing' ).modal( 'hide' );
	            }
	        });
	    }
    });
    // ***************************************************************************************************
    // wpsc_admin_new_ticket_save
    jQuery( document ).on( 'click', '.wpsc_admin_new_ticket_button', function( e ) {
    	e.preventDefault();
        var action_type = jQuery( this ).attr( 'id' );
        var doValidate = true;
        jQuery( '.wpsc_field_error' ).removeClass( 'wpsc_field_error' );
        jQuery( '.wpsc_admin_new_ticket_validate' ).each( function() {
            if ( jQuery( this ).val() == '' ) {
                if ( jQuery( this ).attr( 'id' ) == 'wpsc_admin_new_ticket_client_id' || jQuery( this ).attr( 'id' ) == 'wpsc_admin_new_ticket_client'  || jQuery( this ).attr( 'id' ) == 'wpsc_admin_new_ticket_client_email') {
                    jQuery( '#wpsc_admin_client_autocomplete' ).addClass( 'wpsc_field_error' );
                } else {
                    jQuery( this ).addClass( 'wpsc_field_error' );
                }
                doValidate = false;
            } else if ( jQuery( this ).attr( 'id' ) == 'wpsc_admin_new_ticket_client' ) {
                var theVal = jQuery( this ).val();
                if ( theVal.indexOf( ' ' ) == -1 ) {
                    jQuery( '#wpsc_admin_client_autocomplete' ).addClass( 'wpsc_field_error' );
                    doValidate = false;
                }
            }
        });
        jQuery( '.wpsc_additional_field_required' ).each( function() {
        	var theDataID = jQuery( this ).attr( 'data-ticket-id' );
        	//var theCat = jQuery( this ).closest( '.wpsc-additional-fields' ).attr( 'data-category' );
			//if ( isNaN( theDataID ) || theDataID == '' && theCat == jQuery( '#wpsc_new_ticket_category' ).val() ) {
			if ( isNaN( theDataID ) || theDataID == '' ) {
				if ( jQuery( this).is( ':visible' ) && jQuery( this ).val() == '' ) {
					jQuery( this ).addClass( 'wpsc_field_error' );
					doValidate = false;
				}
			}
		});
        if ( doValidate !== false ) {
            jQuery( "#wpsc_processing" ).modal( 'show' );
            var data = new FormData();
            data.append( 'action', 'wpsc_admin_new_ticket_save' );
            data.append( 'action_type', action_type );
            jQuery( '.wpsc_new_ticket' ).each( function() {
                var theID = jQuery( this ).attr( 'id' );
                var theVal = jQuery( this ).val();
                data.append( theID, theVal );
            });
            var wpsc_admin_new_ticket_details = CKEDITOR.instances['wpsc_admin_new_ticket_details'].getData();
            data.append( 'wpsc_admin_new_ticket_details', wpsc_admin_new_ticket_details );
            var theFiles = jQuery( '#wpsc_admin_new_ticket_attachments' ).prop( 'files' );
            jQuery.each( theFiles, function( i, obj ) {
                data.append( 'wpsc_file[' + i + ']', obj);
            });
            var c = 0;
			var additional_fields = [];
			if ( jQuery( '.wpsc_additional_field' ).length ) {
				jQuery( '.wpsc_additional_field' ).each( function() {
					if ( jQuery( this).is( ':visible' ) ) {
						var theDataID = jQuery( this ).attr( 'data-ticket-id' );
						//var theCat = jQuery( this ).closest( '.wpsc-additional-fields' ).attr( 'data-category' );
						//if ( isNaN( theDataID ) || theDataID == '' && theCat == jQuery( '#wpsc_new_ticket_category' ).val() ) {
						if ( isNaN( theDataID ) || theDataID == '' ) {
							additional_fields[c] = {};
							additional_fields[c].field_id = jQuery( this ).attr( 'id' );
							additional_fields[c].meta_value = jQuery( this ).val();
							c = c + 1;
						}
					}
				});
				var json = JSON.stringify( additional_fields );
				data.append( 'wpsc_additional_field', json );
			}
			CKEDITOR.instances['wpsc_admin_new_ticket_details'].resetDirty();
            jQuery.ajax({
                url: wpsc_localize_admin.wpsc_ajax_url,
                type: 'POST',
                data: data,
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                success: function( response ) {
                    response = jQuery.trim( response );
                    try {
                        response = jQuery.parseJSON( response );
                        if ( response.status == 'true' ) {
                            var theTickets = [];
                            theTickets.push( response.ticket_id );
                            jQuery( '.wpsc_admin_ticket_tab' ).each( function() {
                                theTickets.push( jQuery( this ).closest( "li" ).attr( 'data-id' ) );
                            });
                            var theIDs = theTickets.join();
                            setCookie( 'wpsc_open_tickets', theIDs, 30 );
                            setCookie( 'wpsc_active_ticket', response.ticket_id, 30 );
                            document.location.href = wpsc_localize_admin.wpsc_admin_url + '?page=wp-support-centre';
                        } else {
                            console.log(response);
                            jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                            jQuery( "#wpsc_processing" ).modal( 'hide' );
                        }
                    }
                    catch( err ) {
                        console.log( err );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    }
                }
            });
        }
    });
    // ***************************************************************************************************
    // delete attachment
    jQuery( document ).on( 'click', '.wpsc_delete_attachment', function() {
        var doDelete = confirm( 'Are you sure you wish to delete this file?' );
        if ( doDelete == true ) {
            var theID = jQuery( this ).attr( 'data-id' );
            var theTicketID = jQuery( this ).attr( 'data-ticket-id' );
            var theRow = jQuery( this ).parents( 'tr' );
            var data = {
                'action': 'wpsc_delete_attachment',
                'attachment_id': theID
            };
            jQuery( "#wpsc_processing" ).modal( 'show' );
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        var theTable = jQuery( '#wpsc_admin_attachments_table_' + theTicketID ).DataTable();
                        theTable.row( theRow ).remove().draw();
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // thread inclusion checkbox
    jQuery( document ).on( 'click', '.wpsc_admin_thread_include_all', function() {
        var theID = jQuery( this ).attr( 'data-id' );
        if ( jQuery( this ).is( ':checked' ) ) {
            jQuery( '.wpsc_admin_thread_include_' + theID ).prop( 'checked', true );
        } else {
            jQuery( '.wpsc_admin_thread_include_' + theID ).prop( 'checked', false );
        }
    });
    // ***************************************************************************************************
    // wpsc_ticket_save_changes_button
    jQuery( document ).on( 'click', '.wpsc_ticket_save_changes_button', function( e ) {
    	e.preventDefault();
        var theID = jQuery( this ).attr( 'data-id' );
        if ( jQuery( this ).hasClass( 'wpsc_ticket_save_changes_notify' ) ) {
            var wpsc_notify = 'true';
        } else {
            var wpsc_notify = 'false';
        }
        if ( jQuery( this ).hasClass( 'wpsc_close' ) ) {
            var wpsc_close = true;
        } else {
            var wpsc_close = false;
        }
        var wpsc_admin_new_thread_status = jQuery( '#wpsc_admin_new_thread_status_' + theID ).val();
        if (wpsc_admin_new_thread_status == '' ) {
            jQuery( '#wpsc_admin_new_thread_status_' + theID ).addClass( 'wpsc_field_error' );
            return false;
        }
        var wpsc_admin_new_thread_category = jQuery( '#wpsc_admin_new_thread_category_' + theID ).val();
        if (wpsc_admin_new_thread_category == '' ) {
            jQuery( '#wpsc_admin_new_thread_category_' + theID ).addClass( 'wpsc_field_error' );
            return false;
        }
        var wpsc_admin_new_thread_agent = jQuery( '#wpsc_admin_new_thread_agent_' + theID ).val();
        if (wpsc_admin_new_thread_agent == '' ) {
            jQuery( '#wpsc_admin_new_thread_agent_' + theID ).addClass( 'wpsc_field_error' );
            return false;
        }
        var wpsc_admin_new_thread_priority = jQuery( '#wpsc_admin_new_thread_priority_' + theID ).val();
        if (wpsc_admin_new_thread_priority == '' ) {
            jQuery( '#wpsc_admin_new_thread_priority_' + theID ).addClass( 'wpsc_field_error' );
            return false;
        }
        var wpsc_admin_new_thread_client_phone = jQuery( '#wpsc_admin_new_thread_client_phone_' + theID ).val();
        if ( jQuery( '#wpsc_admin_ticket_subject_' + theID ).length ) {
            var wpsc_ticket_subject = jQuery( '#wpsc_admin_ticket_subject_' + theID ).val();
            if (wpsc_ticket_subject == '' ) {
                jQuery( '#wpsc_admin_ticket_subject_' + theID ).addClass( 'wpsc_field_error' );
                return false;
            }
        } else {
            var wpsc_ticket_subject = '';
        }
        jQuery( '.wpsc_additional_field_required' ).each( function() {
        	var theDataID = jQuery( this ).attr( 'data-ticket-id' );
        	//var theCat = jQuery( this ).closest( '.wpsc-additional-fields' ).attr( 'data-category' );
			//if ( !isNaN( theDataID ) && theDataID == theID && theCat == wpsc_admin_new_thread_category ) {
			if ( !isNaN( theDataID ) && theDataID == theID ) {
				if ( jQuery( this).is( ':visible' ) && jQuery( this ).val() == '' ) {
					jQuery( this ).addClass( 'wpsc_field_error' );
					return false;
				}
			}
		});
		var c = 0;
		var additional_fields = [];
		if ( jQuery( '.wpsc_additional_field' ).length ) {
			jQuery( '.wpsc_additional_field' ).each( function() {
				if ( jQuery( this).is( ':visible' ) ) {
					var theDataID = jQuery( this ).attr( 'data-ticket-id' );
					//var theCat = jQuery( this ).closest( '.wpsc-additional-fields' ).attr( 'data-category' );
					//if ( !isNaN( theDataID ) && theDataID == theID && theCat == wpsc_admin_new_thread_category ) {
					if ( !isNaN( theDataID ) && theDataID == theID ) {
						var ticket_tag = '_' + theID;
						var field_id = jQuery( this ).attr( 'id' );
						var field_id = field_id.replace( ticket_tag, '' );
						additional_fields[c] = {};
						additional_fields[c].field_id = field_id;
						additional_fields[c].meta_value = jQuery( this ).val();
						c = c + 1;
					}
				}
			});
			var json = JSON.stringify( additional_fields );
		}
        var data = {
            'action': 'wpsc_ticket_save_changes',
            'wpsc_notify': wpsc_notify,
            'wpsc_ticket_id': theID,
            'wpsc_admin_new_thread_status': wpsc_admin_new_thread_status,
            'wpsc_admin_new_thread_category': wpsc_admin_new_thread_category,
            'wpsc_admin_new_thread_agent': wpsc_admin_new_thread_agent,
            'wpsc_admin_new_thread_priority': wpsc_admin_new_thread_priority,
            'wpsc_admin_new_thread_client_phone': wpsc_admin_new_thread_client_phone,
            'wpsc_ticket_subject': wpsc_ticket_subject,
            'wpsc_additional_field': json
        };
        jQuery( "#wpsc_processing" ).modal( 'show' );
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                    doRefreshAdminTicketsTable();
                    if ( wpsc_close ) {
                    	doRefreshAdminTicketsTable();
                		jQuery( 'a[href="#wpsc_view_ticket_' + theID + '"]' ).closest( 'li' ).remove();
                    	jQuery( '#wpsc_view_ticket_' + theID ).remove();
				        var theTickets = [];
				        jQuery( '.wpsc_admin_ticket_tab' ).each( function() {
				            theTickets.push( jQuery( this ).closest( "li" ).attr( 'data-id' ) );
				        });
				        var theIDs = theTickets.join();
				        setCookie( 'wpsc_open_tickets', theIDs, 30 );
				        setCookie( 'wpsc_active_ticket', '', 30 );
				        jQuery( '#wpsc_admin_tabs a:first').tab( 'show' );
				        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    } else {
                    	jQuery( '#wpsc_ticket_changes_saved' ).modal( 'show' );
                    }
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // wpsc_ticket_new_thread_button
    jQuery( document ).on( 'click', '.wpsc_ticket_new_thread_button', function( e ) {
    	e.preventDefault();
    	var isFalse = true;
        var theID = jQuery( this ).attr( 'data-id' );
        var theButton = jQuery( this ).attr( 'id' );
        if ( theButton.indexOf( 'wpsc_new_note' ) > -1 ) {
            var wpsc_notify = 'false';
        } else if ( theButton.indexOf( 'wpsc_new_thread' ) > -1 ) {
            var wpsc_notify = 'true';
        }
        if ( jQuery( this ).hasClass( 'wpsc_close' ) ) {
            var wpsc_close = true;
        } else {
            var wpsc_close = false;
        }
        var data = new FormData();
        data.append( 'action', 'wpsc_new_note' );
        data.append( 'wpsc_ticket_id', theID );
        data.append( 'wpsc_notify', wpsc_notify );
        var wpsc_admin_new_thread_client_phone = jQuery( '#wpsc_admin_new_thread_client_phone_' + theID ).val();
        data.append( 'wpsc_admin_new_thread_client_phone', wpsc_admin_new_thread_client_phone );
        var wpsc_admin_new_thread_status = jQuery( '#wpsc_admin_new_thread_status_' + theID ).val();
        if (wpsc_admin_new_thread_status == '' ) {
            jQuery( '#wpsc_admin_new_thread_status_' + theID ).addClass( 'wpsc_field_error' );
            isFalse = false;
            return false;
        }
        data.append( 'wpsc_admin_new_thread_status', wpsc_admin_new_thread_status );
        var wpsc_admin_new_thread_category = jQuery( '#wpsc_admin_new_thread_category_' + theID ).val();
        if (wpsc_admin_new_thread_category == '' ) {
            jQuery( '#wpsc_admin_new_thread_category_' + theID ).addClass( 'wpsc_field_error' );
            isFalse = false;
            return false;
        }
        data.append( 'wpsc_admin_new_thread_category', wpsc_admin_new_thread_category );
        var wpsc_admin_new_thread_agent = jQuery( '#wpsc_admin_new_thread_agent_' + theID ).val();
        if (wpsc_admin_new_thread_agent == '' ) {
            jQuery( '#wpsc_admin_new_thread_agent_' + theID ).addClass( 'wpsc_field_error' );
            isFalse = false;
            return false;
        }
        data.append( 'wpsc_admin_new_thread_agent', wpsc_admin_new_thread_agent );
        var wpsc_admin_new_thread_priority = jQuery( '#wpsc_admin_new_thread_priority_' + theID ).val();
        if (wpsc_admin_new_thread_priority == '' ) {
            jQuery( '#wpsc_admin_new_thread_priority_' + theID ).addClass( 'wpsc_field_error' );
            isFalse = false;
            return false;
        }
        data.append( 'wpsc_admin_new_thread_priority', wpsc_admin_new_thread_priority );
        CKEDITOR.instances['wpsc_admin_ticket_note_' + theID].updateElement();
        var wpsc_admin_ticket_note = CKEDITOR.instances['wpsc_admin_ticket_note_' + theID].getData();
        if (wpsc_admin_ticket_note == '' ) {
            alert( 'Thread content cannot be empty.' );
            isFalse = false;
            return false;
        }
        data.append( 'wpsc_admin_ticket_note', wpsc_admin_ticket_note );
        if ( jQuery( '#wpsc_admin_ticket_subject_' + theID ).length ) {
            var wpsc_ticket_subject = jQuery( '#wpsc_admin_ticket_subject_' + theID ).val();
            if (wpsc_ticket_subject == '' ) {
                jQuery( '#wpsc_admin_ticket_subject_' + theID ).addClass( 'wpsc_field_error' );
                isFalse = false;
                return false;
            }
        } else {
            var wpsc_ticket_subject = '';
        }
        data.append( 'wpsc_ticket_subject', wpsc_ticket_subject );
        var wpsc_admin_existing_thread_attachments = jQuery( '#wpsc_admin_existing_thread_attachments_' + theID ).val();
        if ( jQuery.isArray( wpsc_admin_existing_thread_attachments ) ) {
            wpsc_admin_existing_thread_attachments = wpsc_admin_existing_thread_attachments.join( ',' );
        } else {
            wpsc_admin_existing_thread_attachments = '';
        }
        data.append( 'wpsc_admin_existing_thread_attachments', wpsc_admin_existing_thread_attachments );
        var wpsc_admin_thread_create_as = jQuery( 'input[name=wpsc_admin_thread_create_as_' + theID + ']:checked' ).val();
        data.append( 'wpsc_admin_thread_create_as', wpsc_admin_thread_create_as );
        if ( wpsc_admin_thread_create_as == 'other' ) {
            var wpsc_admin_thread_from_name = jQuery( '#wpsc_admin_new_thread_from_name_' + theID ).val();
            if ( wpsc_admin_thread_from_name == '' ) {
                jQuery( '#wpsc_admin_new_thread_from_name_' + theID ).addClass( 'wpsc_field_error' );
                isFalse = false;
                return false;
            }
            var wpsc_admin_thread_from_email = jQuery( '#wpsc_admin_new_thread_from_email_' + theID ).val();
            if ( wpsc_admin_thread_from_email == '' ) {
                jQuery( '#wpsc_admin_new_thread_from_email_' + theID ).addClass( 'wpsc_field_error' );
                isFalse = false;
                return false;
            }
        } else if ( wpsc_admin_thread_create_as == 'client' ){
            var wpsc_admin_thread_from_name = jQuery( '#wpsc_admin_new_thread_client_' + theID ).val();
            var wpsc_admin_thread_from_email = jQuery( '#wpsc_admin_new_thread_client_email_' + theID ).val();
        } else {
            var wpsc_admin_thread_from_name = jQuery( '#wpsc_admin_new_thread_agent_name_' + theID ).val();
            var wpsc_admin_thread_from_email = jQuery( '#wpsc_admin_new_thread_agent_email_' + theID ).val();
        }
        data.append( 'wpsc_admin_thread_from_name', wpsc_admin_thread_from_name );
        data.append( 'wpsc_admin_thread_from_email', wpsc_admin_thread_from_email );
        if ( theButton.indexOf( 'wpsc_new_thread' ) > -1 ) {
            var wpsc_admin_new_thread_to_select = jQuery( '#wpsc_admin_new_thread_to_select_' + theID ).val();
            var wpsc_admin_new_thread_to = jQuery( '#wpsc_admin_new_thread_to_' + theID ).val();
            if ( wpsc_admin_new_thread_to == '' && ( wpsc_admin_new_thread_to_select == '' || wpsc_admin_new_thread_to_select == '0' ) ) {
                jQuery( '#wpsc_admin_new_thread_to_select_' + theID ).addClass( 'wpsc_field_error' );
                jQuery( '#wpsc_admin_new_thread_to_' + theID ).addClass( 'wpsc_field_error' );
                isFalse = false;
                return false;
            }
            if ( wpsc_admin_new_thread_to != '' && wpsc_admin_new_thread_to_select != '' ) {
                wpsc_admin_new_thread_to = wpsc_admin_new_thread_to + ',' + wpsc_admin_new_thread_to_select;
            } else if ( wpsc_admin_new_thread_to == '' && wpsc_admin_new_thread_to_select != '' ) {
                wpsc_admin_new_thread_to = wpsc_admin_new_thread_to_select;
            }
            data.append( 'wpsc_admin_new_thread_to', wpsc_admin_new_thread_to );
        }
        var wpsc_admin_new_thread_cc_select = jQuery( '#wpsc_admin_new_thread_cc_select_' + theID ).val();
        var wpsc_admin_new_thread_cc = jQuery( '#wpsc_admin_new_thread_cc_' + theID ).val();
        if ( jQuery.isArray( wpsc_admin_new_thread_cc_select ) ) {
            wpsc_admin_new_thread_cc_select = wpsc_admin_new_thread_cc_select.join( ',' );
        } else {
            wpsc_admin_new_thread_cc_select = '';
        }
        if ( wpsc_admin_new_thread_cc != '' && wpsc_admin_new_thread_cc_select != '' ) {
            wpsc_admin_new_thread_cc = wpsc_admin_new_thread_cc + ',' + wpsc_admin_new_thread_cc_select;
        } else if ( wpsc_admin_new_thread_cc == '' && wpsc_admin_new_thread_cc_select != '' ) {
            wpsc_admin_new_thread_cc = wpsc_admin_new_thread_cc_select;
        }
        data.append( 'wpsc_admin_new_thread_cc', wpsc_admin_new_thread_cc );
        var wpsc_admin_new_thread_bcc_select = jQuery( '#wpsc_admin_new_thread_bcc_select_' + theID ).val();
        var wpsc_admin_new_thread_bcc = jQuery( '#wpsc_admin_new_thread_bcc_' + theID ).val();
        if ( jQuery.isArray( wpsc_admin_new_thread_bcc_select ) ) {
            wpsc_admin_new_thread_bcc_select = wpsc_admin_new_thread_bcc_select.join( ',' );
        } else {
            wpsc_admin_new_thread_bcc_select = '';
        }
        if ( wpsc_admin_new_thread_bcc != '' && wpsc_admin_new_thread_bcc_select != '' ) {
            wpsc_admin_new_thread_bcc = wpsc_admin_new_thread_bcc + ',' + wpsc_admin_new_thread_bcc_select;
        } else if ( wpsc_admin_new_thread_bcc == '' && wpsc_admin_new_thread_bcc_select != '' ) {
            wpsc_admin_new_thread_bcc = wpsc_admin_new_thread_bcc_select;
        }
        data.append( 'wpsc_admin_new_thread_bcc', wpsc_admin_new_thread_bcc );
        if ( jQuery( '#wpsc_admin_thread_is_private_' + theID ).is( ':checked' ) ) {
            var wpsc_admin_thread_is_private = 1;
        } else {
            var wpsc_admin_thread_is_private = 0;
        }
        data.append( 'wpsc_admin_thread_is_private', wpsc_admin_thread_is_private );
        var wpsc_admin_thread_inlcudes = '';
        jQuery( '.wpsc_admin_thread_include_' + theID + ':checked' ).each( function() {
            if ( wpsc_admin_thread_inlcudes == '' ) {
                wpsc_admin_thread_inlcudes = jQuery( this ).attr( 'data-id' );
            } else {
                wpsc_admin_thread_inlcudes = wpsc_admin_thread_inlcudes + ',' + jQuery( this ).attr( 'data-id' );
            }
        });
        data.append( 'wpsc_admin_thread_inlcudes', wpsc_admin_thread_inlcudes );
        var theFiles = jQuery( '#wpsc_admin_new_thread_attachments_' + theID ).prop( 'files' );
        jQuery.each( theFiles, function( i, obj ) {
            data.append( 'wpsc_file[' + i + ']', obj);
        });
        jQuery( '.wpsc_additional_field_required' ).each( function() {
        	var theDataID = jQuery( this ).attr( 'data-ticket-id' );
        	//var theCat = jQuery( this ).closest( '.wpsc-additional-fields' ).attr( 'data-category' );
			//if ( !isNaN( theDataID ) && theDataID == theID && theCat == wpsc_admin_new_thread_category ) {
			if ( !isNaN( theDataID ) && theDataID == theID ) {
				if ( jQuery( this).is( ':visible' ) && jQuery( this ).val() == '' ) {
					jQuery( this ).addClass( 'wpsc_field_error' );
					isFalse = false;
					return false;
				}
			}
		});
		if ( isFalse === false ) {
			return false;
		}
		var c = 0;
		var additional_fields = [];
		if ( jQuery( '.wpsc_additional_field' ).length ) {
			jQuery( '.wpsc_additional_field' ).each( function() {
				if ( jQuery( this).is( ':visible' ) ) {
					var theDataID = jQuery( this ).attr( 'data-ticket-id' );
					//var theCat = jQuery( this ).closest( '.wpsc-additional-fields' ).attr( 'data-category' );
					//if ( !isNaN( theDataID ) && theDataID == theID && theCat == wpsc_admin_new_thread_category ) {
					if ( !isNaN( theDataID ) && theDataID == theID ) {
						var ticket_tag = '_' + theID;
						var field_id = jQuery( this ).attr( 'id' );
						var field_id = field_id.replace( ticket_tag, '' );
						additional_fields[c] = {};
						additional_fields[c].field_id = field_id;
						additional_fields[c].meta_value = jQuery( this ).val();
						c = c + 1;
					}
				}
			});
			var json = JSON.stringify( additional_fields );
			data.append( 'wpsc_additional_field', json );
		}
		CKEDITOR.instances['wpsc_admin_ticket_note_' + theID].resetDirty();
        jQuery( '#wpsc_processing' ).modal( 'show' );
        jQuery.ajax({
            url: wpsc_localize_admin.wpsc_ajax_url,
            type: 'POST',
            data: data,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                    	if ( wpsc_close ) {
                    		doRefreshAdminTicketsTable();
                    		jQuery( 'a[href="#wpsc_view_ticket_' + theID + '"]' ).closest( 'li' ).remove();
	                    	jQuery( '#wpsc_view_ticket_' + theID ).remove();
					        var theTickets = [];
					        jQuery( '.wpsc_admin_ticket_tab' ).each( function() {
					            theTickets.push( jQuery( this ).closest( "li" ).attr( 'data-id' ) );
					        });
					        var theIDs = theTickets.join();
					        setCookie( 'wpsc_open_tickets', theIDs, 30 );
					        setCookie( 'wpsc_active_ticket', '', 30 );
					        jQuery( '#wpsc_admin_tabs a:first').tab( 'show' );
					        jQuery( "#wpsc_processing" ).modal( 'hide' );
	                    } else {
	                    	loadAdminTicket( theID, true );
	                    }
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            }
        });
    });
    // ***************************************************************************************************
    // wpsc_account_save_changes_button
    jQuery( document ).on( 'click', '.wpsc_account_save_changes_button', function() {
        var theID = jQuery( this ).attr( 'data-id' );
        var userID = jQuery( this ).attr( 'data-user-id' );
        var accountID = jQuery( this ).attr( 'data-account-id' );
        var isTicket = jQuery( this ).attr( 'data-is-ticket' );
        var data = new FormData();
        data.append( 'action', 'wpsc_account_save_changes' );
        data.append( 'wpsc_user_id', userID );
        data.append( 'wpsc_account_id', accountID );
        var wpsc_account_information = CKEDITOR.instances['wpsc_account_information_' + theID].getData();
        data.append( 'wpsc_account_information', wpsc_account_information );
        CKEDITOR.instances['wpsc_account_information_' + theID].resetDirty();
        jQuery( '#wpsc_processing' ).modal( 'show' );
        jQuery.ajax({
            url: wpsc_localize_admin.wpsc_ajax_url,
            type: 'POST',
            data: data,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        if ( isTicket == 'true' ) {
                            loadAdminTicket( theID, true );
                        } else {
                            jQuery( "#wpsc_account_tab_" + theID ).remove();
                            jQuery( '#wpsc_account_' + theID ).remove();
                            jQuery( '#wpsc_admin_settings_users_tabs a:first').tab( 'show' );
                            jQuery( "#wpsc_processing" ).modal( 'hide' );
                        }
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            }
        });
    });
    // ***************************************************************************************************
    // thread create as
    jQuery( document ).on( 'click', '.wpsc_admin_thread_create_as', function() {
        var theAs = jQuery( this ).val();
        var theID = jQuery( this ).attr( 'data-id' );
        switch( theAs ) {
            case 'agent':
                jQuery( '.wpsc_admin_thread_create_as_other' ).hide();
                jQuery( '#wpsc_admin_new_thread_from_name_' + theID ).val( '' );
                jQuery( '#wpsc_admin_new_thread_from_email_' + theID ).val( '' );
                var theEmail = jQuery( '#wpsc_admin_new_thread_client_email_' + theID ).val();
                jQuery( '#wpsc_admin_new_thread_to_' + theID ).val( theEmail );
                jQuery( '#wpsc_admin_new_thread_cc_' + theID ).val( '' );
                jQuery( '#wpsc_admin_new_thread_bcc_' + theID ).val( '' );
                break;
            case 'client':
                jQuery( '.wpsc_admin_thread_create_as_other' ).hide();
                jQuery( '#wpsc_admin_new_thread_from_name_' + theID ).val( '' );
                jQuery( '#wpsc_admin_new_thread_from_email_' + theID ).val( '' );
                var theEmail = jQuery( '#wpsc_admin_new_thread_agent_email_' + theID ).val();
                jQuery( '#wpsc_admin_new_thread_to_' + theID ).val( theEmail );
                jQuery( '#wpsc_admin_new_thread_cc_' + theID ).val( '' );
                jQuery( '#wpsc_admin_new_thread_bcc_' + theID ).val( '' );
                break;
            case 'other':
                jQuery( '.wpsc_admin_thread_create_as_other' ).show();
                jQuery( '#wpsc_admin_new_thread_from_name_' + theID ).val( '' );
                jQuery( '#wpsc_admin_new_thread_from_email_' + theID ).val( '' );
                var theEmail = jQuery( '#wpsc_admin_new_thread_agent_email_' + theID ).val();
                jQuery( '#wpsc_admin_new_thread_to_' + theID ).val( theEmail );
                jQuery( '#wpsc_admin_new_thread_cc_' + theID ).val( '' );
                jQuery( '#wpsc_admin_new_thread_bcc_' + theID ).val( '' );
                break;
        }
    });
    // ***************************************************************************************************
    // include threads in reply
    jQuery('.wpsc_admin_thread_include').click(function() {
        var theID = jQuery( this ).attr( 'data-ticket' );
        if ( jQuery( this ).is( ':checked' ) ) {
            if ( jQuery( '.wpsc_admin_thread_include_' + theID ).length == jQuery( '.wpsc_admin_thread_include_' + theID + ':checked' ).length ) {
                jQuery( '#wpsc_admin_thread_include_all_' + theID ).prop( 'checked', true );
            } else {
                jQuery( '#wpsc_admin_thread_include_all_' + theID ).prop( 'checked', false );
            }
        } else {
            jQuery( '#wpsc_admin_thread_include_all_' + theID ).prop( 'checked', false );
        }
    });
    // ***************************************************************************************************
    // redraw table on page resize
    jQuery( window ).on( 'resize', function() {
        var theTable = jQuery( '#wpsc_admin_tickets_table' ).DataTable();
        theTable.columns.adjust().draw();
    });
    // ***************************************************************************************************
    // quick find
    jQuery( document ).on( 'click', '#wpsc_quick_find_button', function() {
        var theID = jQuery( '#wpsc_quick_find' ).val();
        if ( jQuery.isNumeric( theID ) ) {
            jQuery( '#wpsc_quick_find' ).removeClass( 'wpsc_field_error' );
            if ( jQuery( '#wpsc_view_ticket_' + theID ).length == 0 ) {
                jQuery( "#wpsc_processing" ).modal( 'show' );
                loadAdminTicket( theID, false );
            }
        } else {
            jQuery( '#wpsc_quick_find' ).addClass( 'wpsc_field_error' );
            return false;
        }
    });
    // ***************************************************************************************************
    // select / deselect all recurring tickets
    jQuery( document ).on( 'click', '#wpsc_select_all_recurring', function() {
        if ( jQuery( this ).is( ':checked' ) ) {
            jQuery( '.wpsc_select_recurring_ticket' ).prop( 'checked', true );
            jQuery( '#wpsc_recurring_actions').show();
        } else {
            jQuery( '.wpsc_select_recurring_ticket' ).prop( 'checked', false );
            jQuery( '#wpsc_recurring_actions').hide();
        }
    });
    // ***************************************************************************************************
    // select / deselect single recurring ticket
    jQuery( document ).on( 'click', '.wpsc_select_recurring_ticket', function() {
        if ( jQuery( this ).is( ':checked' ) ) {
            if ( jQuery( '.wpsc_select_recurring_ticket' ).length == jQuery( '.wpsc_select_recurring_ticket:checked' ).length ) {
                jQuery( '#wpsc_select_all_recurring' ).prop( 'checked', true );
            } else {
                jQuery( '#wpsc_select_all_recurring' ).prop( 'checked', false );
            }
        } else {
            jQuery( '#wpsc_select_all_recurring' ).prop( 'checked', false );
        }
        if ( jQuery( '.wpsc_select_recurring_ticket:checked' ).length == 0 ) {
            jQuery( '#wpsc_recurring_actions').hide();
        } else {
            jQuery( '#wpsc_recurring_actions').show();
        }
    });
    // ***************************************************************************************************
    // apply recurring ticket actions
    jQuery( document ).on( 'click', '#wpsc_admin_apply_recurring_actions', function() {
        var theSelectedIDs = '';
        jQuery( '.wpsc_select_recurring_ticket:checked' ).each( function() {
            var theVal = jQuery( this ).val();
            if ( theSelectedIDs == 'undefined' || theSelectedIDs == '' ) {
                theSelectedIDs = theVal;
            } else {
                theSelectedIDs = theSelectedIDs + ',' + theVal;
            }
        });
        var wpsc_recurring_action_status = jQuery( '#wpsc_recurring_action_status' ).val();
        var wpsc_recurring_action_category = jQuery( '#wpsc_recurring_action_category' ).val();
        var wpsc_recurring_action_priority = jQuery( '#wpsc_recurring_action_priority' ).val();
        var wpsc_recurring_action_agent = jQuery( '#wpsc_recurring_action_agent' ).val();
        if ( wpsc_recurring_action_status == '' && wpsc_recurring_action_category == '' && wpsc_recurring_action_priority == '' && wpsc_recurring_action_agent == '' ) {
            return false;
        } else {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = {
                'action': 'wpsc_admin_apply_recurring_actions',
                'theSelectedIDs': theSelectedIDs,
                'wpsc_recurring_action_status': wpsc_recurring_action_status,
                'wpsc_recurring_action_category': wpsc_recurring_action_category,
                'wpsc_recurring_action_priority': wpsc_recurring_action_priority,
                'wpsc_recurring_action_agent': wpsc_recurring_action_agent
            };
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        document.location.href = document.location.href;
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // navigate to recurring ticket
    jQuery( document ).on( 'click', '.wpsc_recurring_ticket_row td', function() {
        if ( false === jQuery( this ).hasClass( 'wpsc_select_recurring_ticket_td' ) && false === jQuery( this ).hasClass( 'wpsc_run_recurring_ticket_td' ) ) {
            var theID = jQuery( this ).closest( 'tr' ).attr( 'id' );
            if ( jQuery( '#wpsc_view_recurring_ticket_' + theID ).length == 0 ) {
                jQuery( "#wpsc_processing" ).modal( 'show' );
                loadRecurringTicket( theID );
            }
        }
    });
    // ***************************************************************************************************
    // wpsc_recurring_tab_close
    jQuery( document ).on( 'click', '.wpsc_recurring_tab_close', function() {
        var theID = jQuery( this).closest( 'li' ).remove().attr( 'data-id' );
        jQuery( '#wpsc_view_recurring_ticket_' + theID ).remove();
        jQuery( '#wpsc_admin_recurring_tabs a:first').tab( 'show' );
    });
    // ***************************************************************************************************
    // create recurring ticket
    jQuery( document ).on( 'click', '.wpsc_new_recurring_ticket_button', function() {
        var theID = jQuery( this ).attr( 'id' );
        var doValidate = true;
        jQuery( '.wpsc_field_error' ).removeClass( 'wpsc_field_error' );
        jQuery( '.wpsc_new_recurring_ticket' ).each( function() {
            if ( jQuery( this ).val() == '' ) {
                if ( jQuery( this ).attr( 'id' ) == 'wpsc_new_recurring_ticket_client_id' ) {
                    jQuery( '#wpsc_admin_client_autocomplete_recurring' ).addClass( 'wpsc_field_error' );
                } else {
                    jQuery( this ).addClass( 'wpsc_field_error' );
                }
                doValidate = false;
            } else if ( jQuery( this ).attr( 'id' ) == 'wpsc_admin_client_autocomplete_recurring' ) {
                var theVal = jQuery( this ).val();
                if ( theVal.indexOf( ' ' ) == -1 ) {
                    jQuery( '#wpsc_admin_client_autocomplete_recurring' ).addClass( 'wpsc_field_error' );
                    doValidate = false;
                }
            }
        });
        if ( doValidate !== false ) {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = new FormData();
            data.append( 'action', 'wpsc_new_recurring_ticket_save' );
            data.append( 'action_type', theID );
            jQuery( '.wpsc_new_recurring_ticket' ).each( function() {
                var theID = jQuery( this ).attr( 'id' );
                var theVal = jQuery( this ).val();
                data.append( theID, theVal );
            });
            var wpsc_new_recurring_ticket_details = CKEDITOR.instances['wpsc_new_recurring_ticket_details'].getData();
            data.append( 'wpsc_new_recurring_ticket_details', wpsc_new_recurring_ticket_details );
            if ( jQuery( '#wpsc_admin_new_recurring_ticket_enable' ).is( ':checked' ) ) {
                data.append( 'wpsc_admin_new_recurring_ticket_enable', '1' );
            } else {
                data.append( 'wpsc_admin_new_recurring_ticket_enable', '0' );
            }
            if ( jQuery( '#wpsc_admin_new_recurring_ticket_notify' ).is( ':checked' ) ) {
                data.append( 'wpsc_admin_new_recurring_ticket_notify', '1' );
            } else {
                data.append( 'wpsc_admin_new_recurring_ticket_notify', '0' );
            }
            var theFiles = jQuery( '#wpsc_admin_new_recurring_ticket_attachments' ).prop( 'files' );
            jQuery.each( theFiles, function( i, obj ) {
                data.append( 'wpsc_file[' + i + ']', obj);
            });
            CKEDITOR.instances['wpsc_new_recurring_ticket_details'].resetDirty();
            jQuery.ajax({
                url: wpsc_localize_admin.wpsc_ajax_url,
                type: 'POST',
                data: data,
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                success: function( response ) {
                    response = jQuery.trim( response );
                    try {
                        response = jQuery.parseJSON( response );
                        if ( response.status == 'true' ) {
                            document.location.href = document.location.href;
                        } else {
                            console.log(response);
                            jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                            jQuery( "#wpsc_processing" ).modal( 'hide' );
                        }
                    }
                    catch( err ) {
                        console.log( err );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    }
                }
            });
        }
    });
    // ***************************************************************************************************
    // save recurring ticket changes
    jQuery( document ).on( 'click', '.wpsc_save_recurring_ticket_button', function() {
        var theID = jQuery( this ).attr( 'data-id' );
        var doValidate = true;
        jQuery( '.wpsc_field_error' ).removeClass( 'wpsc_field_error' );
        jQuery( '.wpsc_edit_recurring_ticket_validate' ).each( function() {
            if ( jQuery( this ).val() == '' ) {
                if ( jQuery( this ).attr( 'id' ) == 'wpsc_edit_recurring_ticket_client_id_' + theID ) {
                    jQuery( '#wpsc_admin_client_autocomplete_recurring_' + theID ).addClass( 'wpsc_field_error' );
                } else {
                    jQuery( this ).addClass( 'wpsc_field_error' );
                }
                doValidate = false;
            }
        });
        if ( doValidate !== false ) {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = new FormData();
            data.append( 'action', 'wpsc_edit_recurring_ticket_save' );
            data.append( 'ticket_id', theID );
            jQuery( '.wpsc_edit_recurring_ticket' ).each( function() {
                var theID = jQuery( this ).attr( 'id' );
                var theVal = jQuery( this ).val();
                data.append( theID, theVal );
            });
            var wpsc_edit_recurring_ticket_details = CKEDITOR.instances['wpsc_edit_recurring_ticket_details_' + theID].getData();
            data.append( 'wpsc_edit_recurring_ticket_details', wpsc_edit_recurring_ticket_details );
            if ( jQuery( '#wpsc_admin_edit_recurring_ticket_enable_' + theID ).is( ':checked' ) ) {
                data.append( 'wpsc_admin_edit_recurring_ticket_enable', '1' );
            } else {
                data.append( 'wpsc_admin_edit_recurring_ticket_enable', '0' );
            }
            if ( jQuery( '#wpsc_admin_edit_recurring_ticket_notify_' + theID ).is( ':checked' ) ) {
                data.append( 'wpsc_admin_edit_recurring_ticket_notify', '1' );
            } else {
                data.append( 'wpsc_admin_edit_recurring_ticket_notify', '0' );
            }
            var theFiles = jQuery( '#wpsc_admin_edit_recurring_ticket_attachments_' + theID ).prop( 'files' );
            jQuery.each( theFiles, function( i, obj ) {
                data.append( 'wpsc_file[' + i + ']', obj);
            });
            data.append( 'wpsc_edit_recurring_ticket_attachments_existing', jQuery( '#wpsc_edit_recurring_ticket_attachments_existing_' + theID ).val() );
            CKEDITOR.instances['wpsc_edit_recurring_ticket_details_' + theID].resetDirty();
            jQuery.ajax({
                url: wpsc_localize_admin.wpsc_ajax_url,
                type: 'POST',
                data: data,
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                success: function( response ) {
                    response = jQuery.trim( response );
                    try {
                        response = jQuery.parseJSON( response );
                        if ( response.status == 'true' ) {
                            document.location.href = document.location.href;
                        } else {
                            console.log(response);
                            jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                            jQuery( "#wpsc_processing" ).modal( 'hide' );
                        }
                    }
                    catch( err ) {
                        console.log( err );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    }
                }
            });
        }
    });
    // ***************************************************************************************************
    // delete recurring ticket
    jQuery( document ).on( 'click', '.wpsc_delete_recurring_ticket_button', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var theID = jQuery( this ).attr( 'data-id' );
        var data = {
            'action': 'wpsc_delete_recurring_ticket',
            'ticket_id': theID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // delete recurring ticket attachment
    jQuery( document ).on( 'click', '.wpsc_recurring_ticket_delete_attachment', function() {
        var theID = jQuery( this ).attr( 'data-id' );
        var ticketID = jQuery( this ).attr( 'data-ticket' );
        var theAttachments = jQuery( '#wpsc_edit_recurring_ticket_attachments_existing_' + ticketID ).val();
        var attachments = theAttachments.split( ',' );
        var index = jQuery.inArray( theID, attachments );
        if ( index > -1 ) {
            attachments.splice( index, 1 );
        }
        var modified = attachments.join();
        jQuery( '#wpsc_edit_recurring_ticket_attachments_existing_' + ticketID ).val( modified );
        jQuery( this ).closest( 'tr' ).remove();
    });
    // ***************************************************************************************************
    // wpsc_ticket_share_button
    jQuery( document ).on( 'click', '.wpsc_ticket_share_button', function() {
        var theID = jQuery( this ).attr( 'data-id' );
        var wpsc_ticket_shared_users = jQuery( '#wpsc_ticket_shared_users_' + theID ).val();
        if ( jQuery.isArray( wpsc_ticket_shared_users ) ) {
            wpsc_ticket_shared_users = wpsc_ticket_shared_users.join( ',' );
        } else {
            wpsc_ticket_shared_users = '';
        }
        var data = {
            'action': 'wpsc_ticket_shared_users',
            'ticket_id': theID,
            'wpsc_ticket_shared_users': wpsc_ticket_shared_users
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // save settings - general
    jQuery( document ).on( 'click', '.wpsc_wpsc_save_general', function() {
    	var wpsc_rename = jQuery( '#wpsc_rename' );
        var wpsc_item = jQuery( '#wpsc_item' );
        var wpsc_client = jQuery( '#wpsc_client' );
        var wpsc_support_page = jQuery( '#wpsc_support_page' );
        var wpsc_thanks_page = jQuery( '#wpsc_thanks_page' );
        if ( jQuery( '#wpsc_file_upload' ).is( ':checked' ) ) {
            var wpsc_file_upload = 1;
        } else {
            var wpsc_file_upload = 0;
        }
        var wpsc_recurring_tickets_scheduled_time = jQuery( '#wpsc_recurring_tickets_scheduled_time' );
        if ( wpsc_rename.val() == '' ) {
        	wpsc_rename.addClass( 'wpsc_field_error' ).focus();
            return false;
        } else if ( wpsc_item.val() == '' ) {
            wpsc_item.addClass( 'wpsc_field_error' ).focus();
            return false;
        } else if ( wpsc_client.val() == '' ) {
            wpsc_client.addClass( 'wpsc_field_error' ).focus();
            return false;
        } else {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = {
                'action': 'wpsc_save_general',
                'wpsc_rename': wpsc_rename.val(),
                'wpsc_item': wpsc_item.val(),
                'wpsc_client': wpsc_client.val(),
                'wpsc_support_page': wpsc_support_page.val(),
                'wpsc_thanks_page': wpsc_thanks_page.val(),
                'wpsc_file_upload': wpsc_file_upload,
                'wpsc_recurring_tickets_scheduled_time': wpsc_recurring_tickets_scheduled_time.val()
            };
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // save settings - email
    jQuery( document ).on( 'click', '.wpsc_wpsc_save_email', function() {
        var wpsc_email_from_name = jQuery( '#wpsc_email_from_name' );
        var wpsc_email_from_email = jQuery( '#wpsc_email_from_email' );
        var wpsc_email_reply_to = jQuery( '#wpsc_email_reply_to' );
        var wpsc_admin_signature = CKEDITOR.instances['wpsc_admin_signature'].getData();
        var wpsc_email_method = jQuery( 'input[name=wpsc_email_method]:checked' ).val();
        if ( wpsc_email_method == 1 ) {
        	var wpsc_email_piping = jQuery( '#wpsc_email_piping' );
	        var wpsc_email_piping_catch_all = jQuery( '#wpsc_enable_email_piping_catch_all' );
	        if ( jQuery( '#wpsc_use_agent_email' ).is( ':checked' ) ) {
	            var wpsc_use_agent_email = 1;
	        } else {
	            var wpsc_use_agent_email = 0;
	        }
	        if ( jQuery( '#wpsc_enable_email_piping' ).is( ':checked' ) ) {
	            /*if ( wpsc_email_piping.val() == '' ) {
	                wpsc_email_piping.addClass( 'wpsc_field_error' ).focus();
	                return false;
	            }*/
	            var wpsc_enable_email_piping = 1;
	        } else {
	            var wpsc_enable_email_piping = 0;
	        }
	        if ( jQuery( '#wpsc_enable_email_piping_catch_all' ).is( ':checked' ) ) {
	            var wpsc_enable_email_piping_catch_all = 1;
	        } else {
	            var wpsc_enable_email_piping_catch_all = 0;
	        }
        } else if ( wpsc_email_method == 2 ) {
			var wpsc_imap_server = jQuery( '#wpsc_imap_server' );
			var wpsc_imap_port = jQuery( '#wpsc_imap_port' );
			/*var wpsc_imap_argstring = jQuery( '#wpsc_imap_argstring' );*/
			var wpsc_imap_username = jQuery( '#wpsc_imap_username' );
			var wpsc_imap_password = jQuery( '#wpsc_imap_password' );
			var wpsc_imap_type = jQuery( '#wpsc_imap_type' );
        }
        if ( wpsc_email_from_name.val() == '' ) {
            wpsc_email_from_name.addClass( 'wpsc_field_error' ).focus();
            return false;
        } else if ( wpsc_email_from_email.val() == '' ) {
            wpsc_email_from_email.addClass( 'wpsc_field_error' ).focus();
            return false;
        } else if ( wpsc_email_reply_to.val() == '' ) {
            wpsc_email_reply_to.addClass( 'wpsc_field_error' ).focus();
            return false;
        } else {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            if ( wpsc_email_method == 1 ) {
	            var data = {
	                'action': 'wpsc_save_email',
	                'wpsc_email_from_name': wpsc_email_from_name.val(),
	                'wpsc_email_from_email': wpsc_email_from_email.val(),
	                'wpsc_email_reply_to': wpsc_email_reply_to.val(),
	                'wpsc_use_agent_email': wpsc_use_agent_email,
	                'wpsc_admin_signature': wpsc_admin_signature,
	                'wpsc_email_method': wpsc_email_method,
	                'wpsc_enable_email_piping': wpsc_enable_email_piping,
	                'wpsc_enable_email_piping_catch_all': wpsc_enable_email_piping_catch_all,
	                'wpsc_email_piping': wpsc_email_piping.val()
				};
			} else if ( wpsc_email_method == 2 ) {
				/*var data = {
	                'action': 'wpsc_save_email',
	                'wpsc_email_from_name': wpsc_email_from_name.val(),
	                'wpsc_email_from_email': wpsc_email_from_email.val(),
	                'wpsc_email_reply_to': wpsc_email_reply_to.val(),
	                'wpsc_use_agent_email': wpsc_use_agent_email,
	                'wpsc_admin_signature': wpsc_admin_signature,
	                'wpsc_email_method': wpsc_email_method,
	                'wpsc_imap_server': wpsc_imap_server.val(),
	                'wpsc_imap_port': wpsc_imap_port.val(),
	                'wpsc_imap_argstring': wpsc_imap_argstring.val(),
	                'wpsc_imap_username': wpsc_imap_username.val(),
	                'wpsc_imap_password': wpsc_imap_password.val(),
	                'wpsc_imap_type': wpsc_imap_type.val()
				};*/
				var data = {
	                'action': 'wpsc_save_email',
	                'wpsc_email_from_name': wpsc_email_from_name.val(),
	                'wpsc_email_from_email': wpsc_email_from_email.val(),
	                'wpsc_email_reply_to': wpsc_email_reply_to.val(),
	                'wpsc_use_agent_email': wpsc_use_agent_email,
	                'wpsc_admin_signature': wpsc_admin_signature,
	                'wpsc_email_method': wpsc_email_method,
	                'wpsc_imap_server': wpsc_imap_server.val(),
	                'wpsc_imap_port': wpsc_imap_port.val(),
	                'wpsc_imap_username': wpsc_imap_username.val(),
	                'wpsc_imap_password': wpsc_imap_password.val(),
	                'wpsc_imap_type': wpsc_imap_type.val()
				};
			} else {
				var data = {
	                'action': 'wpsc_save_email',
	                'wpsc_email_from_name': wpsc_email_from_name.val(),
	                'wpsc_email_from_email': wpsc_email_from_email.val(),
	                'wpsc_email_reply_to': wpsc_email_reply_to.val(),
	                'wpsc_use_agent_email': wpsc_use_agent_email,
	                'wpsc_admin_signature': wpsc_admin_signature,
	                'wpsc_email_method': wpsc_email_method
				};
			}
			CKEDITOR.instances['wpsc_admin_signature'].resetDirty();
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // save agent settings
    jQuery( document ).on( 'click', '.wpsc_save_wpsc_agent', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var wpsc_agent_default = jQuery( '#wpsc_agent_default' );
        if ( wpsc_agent_default.val() == '' ) {
            wpsc_agent_default.addClass( 'wpsc_field_error' ).focus();
            return false;
        }
        var theAgents = [];
        var theSupers = [];
        var theAgentsRemove = [];
        var theSupersRemove = [];
        if ( jQuery( '.wpsc_user_agent:checked' ).length == 0 || jQuery( '.wpsc_user_supervisor:checked' ).length == 0 ) {
            alert( 'You must assign at least one agent and one supervisor.');
            return false;
        }
        jQuery( '.wpsc_user_agent' ).each( function() {
            if ( jQuery( this ).is( ':checked' ) ) {
                theAgents.push( jQuery( this ).attr( 'data-id' ) );
            } else {
                theAgentsRemove.push( jQuery( this ).attr( 'data-id' ) );
            }
        });
        jQuery( '.wpsc_user_supervisor' ).each( function() {
            if ( jQuery( this ).is( ':checked' ) ) {
                theSupers.push( jQuery( this ).attr( 'data-id' ) );
            } else {
                theSupersRemove.push( jQuery( this ).attr( 'data-id' ) );
            }
        });

        var wpsc_agents = theAgents.join();
        var wpsc_supers = theSupers.join();
        var wpsc_agents_remove = theAgentsRemove.join();
        var wpsc_supers_remove = theSupersRemove.join();
        var data = {
            'action': 'wpsc_save_agent_settings',
            'wpsc_agent_default': wpsc_agent_default.val(),
            'wpsc_agents': wpsc_agents,
            'wpsc_supers': wpsc_supers,
            'wpsc_agents_remove': wpsc_agents_remove,
            'wpsc_supers_remove': wpsc_supers_remove

        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // save status colour
    jQuery( document ).on( 'click', '.wpsc_save_status_colour', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        jQuery( this ).hide();
        var theThis = jQuery( this );
        var theID = theThis.attr( 'data-id' );
        var theStatus = jQuery( '#wpsc_status_' + theID ).val();
        var thePrefix = jQuery( '#wpsc_prefix_' + theID ).val();
        var theColour = jQuery( '#status_colour_' + theID ).val();
        var data = {
            'action': 'wpsc_save_status_colour',
            'wpsc_id': theID,
            'wpsc_status': theStatus,
            'wpsc_prefix': thePrefix,
            'wpsc_colour': theColour
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // set default status
    jQuery( document ).on( 'click', '.wpsc_status_default', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var theThis = jQuery( this );
        var theID = theThis.val();
        var data = {
            'action': 'wpsc_status_default',
            'wpsc_id': theID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // delete custom status
    jQuery( document ).on( 'click', '.wpsc_delete_status', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var theThis = jQuery( this );
        var theID = theThis.attr( 'data-id' );
        var data = {
            'action': 'wpsc_delete_status',
            'wpsc_id': theID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // delete imap
    jQuery( document ).on( 'click', '.wpsc_delete_imap', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var theThis = jQuery( this );
        var theID = theThis.attr( 'data-id' );
        var data = {
            'action': 'wpsc_delete_imap',
            'imap_id': theID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // save new custom status
    jQuery( document ).on( 'click', '.wpsc_add_new_status', function() {
        var wpsc_new_status = jQuery( '#wpsc_new_status' );
        var wpsc_new_status_colour = jQuery( '#wpsc_new_status_colour' );
        var wpsc_new_status_subject_prefix = jQuery( '#wpsc_new_status_subject_prefix' );
        if ( wpsc_new_status.val() == '' ) {
            wpsc_new_status.addClass( 'wpsc_field_error' ).focus();
            return false;
        } else if (wpsc_new_status_colour.val() == '' ) {
            wpsc_new_status_colour.closest('.wp-picker-container').addClass( 'wpsc_field_error' ).focus();
            return false;
        } else {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = {
                'action': 'wpsc_add_new_status',
                'wpsc_new_status': wpsc_new_status.val(),
                'wpsc_new_status_subject_prefix': wpsc_new_status_subject_prefix.val(),
                'wpsc_new_status_colour': wpsc_new_status_colour.val()
            };
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        document.location.href = document.location.href;
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // set default category
    jQuery( document ).on( 'click', '.wpsc_category_default', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var theThis = jQuery( this );
        var theID = theThis.val();
        var data = {
            'action': 'wpsc_category_default',
            'wpsc_id': theID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // delete category
    jQuery( document ).on( 'click', '.wpsc_delete_category', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var theThis = jQuery( this );
        var theID = theThis.attr( 'data-id' );
        var data = {
            'action': 'wpsc_delete_category',
            'wpsc_id': theID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // save new category
    jQuery( document ).on( 'click', '.wpsc_add_new_category', function() {
        var wpsc_new_category = jQuery( '#wpsc_new_category' );
        if ( wpsc_new_category.val() == '' ) {
            wpsc_new_category.addClass( 'wpsc_field_error' ).focus();
            return false;
        } else {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = {
                'action': 'wpsc_add_new_category',
                'wpsc_new_category': wpsc_new_category.val()
            };
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        document.location.href = document.location.href;
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // save priority colour
    jQuery( document ).on( 'click', '.wpsc_save_priority_colour', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        jQuery( this ).hide();
        var theThis = jQuery( this );
        var theID = theThis.attr( 'data-id' );
        var thePriority = jQuery( '#wpsc_priority_' + theID ).val();
        var theSLA = jQuery( '#wpsc_sla_' + theID ).val();
        var theColour = jQuery( '#priority_colour_' + theID ).val();
        var data = {
            'action': 'wpsc_save_priority_colour',
            'wpsc_id': theID,
            'wpsc_priority': thePriority,
            'wpsc_sla': theSLA,
            'wpsc_colour': theColour
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // set default priority
    jQuery( document ).on( 'click', '.wpsc_priority_default', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var theThis = jQuery( this );
        var theID = theThis.val();
        var data = {
            'action': 'wpsc_priority_default',
            'wpsc_id': theID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // delete priority
    jQuery( document ).on( 'click', '.wpsc_delete_priority', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var theThis = jQuery( this );
        var theID = theThis.attr( 'data-id' );
        var data = {
            'action': 'wpsc_delete_priority',
            'wpsc_id': theID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // save new priority
    jQuery( document ).on( 'click', '.wpsc_add_new_priority', function() {
        var wpsc_new_priority = jQuery( '#wpsc_new_priority' );
        var wpsc_new_priority_sla = jQuery( '#wpsc_new_priority_sla' );
        var wpsc_new_priority_colour = jQuery( '#wpsc_new_priority_colour' );
        if ( wpsc_new_priority.val() == '' ) {
            wpsc_new_priority.addClass( 'wpsc_field_error' ).focus();
            return false;
        } else if (wpsc_new_priority_colour.val() == '' ) {
            wpsc_new_priority_colour.closest('.wp-picker-container').addClass( 'wpsc_field_error' ).focus();
            return false;
        } else {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = {
                'action': 'wpsc_add_new_priority',
                'wpsc_new_priority': wpsc_new_priority.val(),
                'wpsc_new_priority_sla': wpsc_new_priority_sla.val(),
                'wpsc_new_priority_colour': wpsc_new_priority_colour.val()
            };
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        document.location.href = document.location.href;
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // save settings - misc
    jQuery( document ).on( 'click', '.wpsc_wpsc_save_misc', function() {
        if ( jQuery( '#wpsc_load_bootstrap_js_f' ).is( ':checked' ) ) {
            var wpsc_load_bootstrap_js_f = 1;
        } else {
            var wpsc_load_bootstrap_js_f = 0;
        }
        if ( jQuery( '#wpsc_load_bootstrap_js_a' ).is( ':checked' ) ) {
            var wpsc_load_bootstrap_js_a = 1;
        } else {
            var wpsc_load_bootstrap_js_a = 0;
        }
        if ( jQuery( '#wpsc_load_bootstrap_css_f' ).is( ':checked' ) ) {
            var wpsc_load_bootstrap_css_f = 1;
        } else {
            var wpsc_load_bootstrap_css_f = 0;
        }
        if ( jQuery( '#wpsc_load_bootstrap_css_a' ).is( ':checked' ) ) {
            var wpsc_load_bootstrap_css_a = 1;
        } else {
            var wpsc_load_bootstrap_css_a = 0;
        }
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var data = {
            'action': 'wpsc_save_misc',
            'wpsc_load_bootstrap_js_f': wpsc_load_bootstrap_js_f,
            'wpsc_load_bootstrap_js_a': wpsc_load_bootstrap_js_a,
            'wpsc_load_bootstrap_css_f': wpsc_load_bootstrap_css_f,
            'wpsc_load_bootstrap_css_a': wpsc_load_bootstrap_css_a
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // save notifications - new ticket
    jQuery( document ).on( 'click', '#wpsc_save_notifications_ticket_new', function() {
        var wpsc_notification_ticket_new_client_val = CKEDITOR.instances['wpsc_notification_ticket_new_client'].getData();
        var wpsc_notification_ticket_new_admin_val = CKEDITOR.instances['wpsc_notification_ticket_new_admin'].getData();
        if ( jQuery( '#wpsc_notification_ticket_new_client_enable').is( ':checked' ) ) {
            var wpsc_notification_ticket_new_client_enable = 1;
        } else {
            var wpsc_notification_ticket_new_client_enable = 0;
        }
        if ( jQuery( '#wpsc_notification_ticket_new_admin_enable').is( ':checked' ) ) {
            var wpsc_notification_ticket_new_admin_enable = 1;
        } else {
            var wpsc_notification_ticket_new_admin_enable = 0;
        }
        if ( wpsc_notification_ticket_new_client_val == '' ) {
            alert( 'Notification cannot be empty!' );
            return false;
        } else if (wpsc_notification_ticket_new_admin_val == '' ) {
            alert( 'Notification cannot be empty!' );
            return false;
        } else {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = {
                'action': 'wpsc_save_notifications_ticket_new',
                'wpsc_notification_ticket_new_client': wpsc_notification_ticket_new_client_val,
                'wpsc_notification_ticket_new_admin': wpsc_notification_ticket_new_admin_val,
                'wpsc_notification_ticket_new_client_enable': wpsc_notification_ticket_new_client_enable,
                'wpsc_notification_ticket_new_admin_enable': wpsc_notification_ticket_new_admin_enable
            };
            CKEDITOR.instances['wpsc_notification_ticket_new_client'].resetDirty();
            CKEDITOR.instances['wpsc_notification_ticket_new_admin'].resetDirty();
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // reset notifications - new ticket
    jQuery( document ).on( 'click', '#wpsc_reset_notifications_ticket_new', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var data = {
            'action': 'wpsc_reset_notifications_ticket_new'
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // save notifications - ticket reply
    jQuery( document ).on( 'click', '#wpsc_save_notifications_ticket_reply', function() {
        var wpsc_notification_ticket_reply_client_val = CKEDITOR.instances['wpsc_notification_ticket_reply_client'].getData();
        var wpsc_notification_ticket_reply_admin_val = CKEDITOR.instances['wpsc_notification_ticket_reply_admin'].getData();
        if ( jQuery( '#wpsc_notification_ticket_reply_client_enable').is( ':checked' ) ) {
            var wpsc_notification_ticket_reply_client_enable = 1;
        } else {
            var wpsc_notification_ticket_reply_client_enable = 0;
        }
        if ( jQuery( '#wpsc_notification_ticket_reply_admin_enable').is( ':checked' ) ) {
            var wpsc_notification_ticket_reply_admin_enable = 1;
        } else {
            var wpsc_notification_ticket_reply_admin_enable = 0;
        }
        var wpsc_reply_include = jQuery( '.wpsc_reply_include:checked' ).val();
        if ( wpsc_notification_ticket_reply_client_val == '' ) {
            alert( 'Notification cannot be empty!' );
            return false;
        } else if (wpsc_notification_ticket_reply_admin_val == '' ) {
            alert( 'Notification cannot be empty!' );
            return false;
        } else {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = {
                'action': 'wpsc_save_notifications_ticket_reply',
                'wpsc_notification_ticket_reply_client': wpsc_notification_ticket_reply_client_val,
                'wpsc_notification_ticket_reply_admin': wpsc_notification_ticket_reply_admin_val,
                'wpsc_notification_ticket_reply_client_enable': wpsc_notification_ticket_reply_client_enable,
                'wpsc_notification_ticket_reply_admin_enable': wpsc_notification_ticket_reply_admin_enable,
                'wpsc_reply_include': wpsc_reply_include
            };
            CKEDITOR.instances['wpsc_notification_ticket_reply_client'].resetDirty();
            CKEDITOR.instances['wpsc_notification_ticket_reply_admin'].resetDirty();
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // reset notifications - ticket reply
    jQuery( document ).on( 'click', '#wpsc_reset_notifications_ticket_reply', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var data = {
            'action': 'wpsc_reset_notifications_ticket_reply'
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // save notifications - ticket change
    jQuery( document ).on( 'click', '#wpsc_save_notifications_ticket_change', function() {
        var wpsc_notification_ticket_change_client_val = CKEDITOR.instances['wpsc_notification_ticket_change_client'].getData();
        var wpsc_notification_ticket_change_admin_val = CKEDITOR.instances['wpsc_notification_ticket_change_admin'].getData();
        if ( jQuery( '#wpsc_notification_ticket_change_client_enable').is( ':checked' ) ) {
            var wpsc_notification_ticket_change_client_enable = 1;
        } else {
            var wpsc_notification_ticket_change_client_enable = 0;
        }
        if ( jQuery( '#wpsc_notification_ticket_change_admin_enable').is( ':checked' ) ) {
            var wpsc_notification_ticket_change_admin_enable = 1;
        } else {
            var wpsc_notification_ticket_change_admin_enable = 0;
        }
        if ( wpsc_notification_ticket_change_client_val == '' ) {
            alert( 'Notification cannot be empty!' );
            return false;
        } else if (wpsc_notification_ticket_change_admin_val == '' ) {
            alert( 'Notification cannot be empty!' );
            return false;
        } else {
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = {
                'action': 'wpsc_save_notifications_ticket_change',
                'wpsc_notification_ticket_change_client': wpsc_notification_ticket_change_client_val,
                'wpsc_notification_ticket_change_admin': wpsc_notification_ticket_change_admin_val,
                'wpsc_notification_ticket_change_client_enable': wpsc_notification_ticket_change_client_enable,
                'wpsc_notification_ticket_change_admin_enable': wpsc_notification_ticket_change_admin_enable
            };
            CKEDITOR.instances['wpsc_notification_ticket_change_client'].resetDirty();
            CKEDITOR.instances['wpsc_notification_ticket_change_admin'].resetDirty();
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        jQuery( '#wpsc_settings_saved' ).modal( 'show' );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // reset notifications - ticket change
    jQuery( document ).on( 'click', '#wpsc_reset_notifications_ticket_change', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var data = {
            'action': 'wpsc_reset_notifications_ticket_change'
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // load template for edit
    jQuery( document ).on( 'click', '.wpsc_template_row', function() {
        var theID = jQuery( this ).attr( 'id' );
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var data = {
            'action': 'wpsc_get_template_for_edit',
            'template_id': theID
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '#wpsc_template_body' ).html( response.template );
                    jQuery( '#wpsc_edit_template' ).ckeditor({
                    	height: 280,
                        removeButtons: 'wpsctemplates'
                    });
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // save template changes
    jQuery( document ).on( 'click', '#wpsc_save_edit_template', function() {
        var theID = jQuery( this ).attr( 'data-id' );
        var wpsc_template_label_edit = jQuery( '#wpsc_edit_template_label' ).val();
        if ( wpsc_template_label_edit == '' ) {
            jQuery( '#wpsc_edit_template_label' ).addClass( 'wpsc_field_error' ).focus();
            return false;
        }
        var wpsc_edit_template = CKEDITOR.instances['wpsc_edit_template'].getData();
        if ( wpsc_edit_template == '' ) {
            alert( 'Template cannot be empty!' );
            return false;
        }
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var data = {
            'action': 'wpsc_save_template_changes',
            'template_id': theID,
            'wpsc_template_label_edit': wpsc_template_label_edit,
            'wpsc_edit_template': wpsc_edit_template
        };
        CKEDITOR.instances['wpsc_edit_template'].resetDirty();
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // delete template
    jQuery( document ).on( 'click', '.wpsc_template_delete', function() {
        var doConfirm = confirm( 'Are you sure you want to delete this reply template?' );
        if ( doConfirm == true ) {
            var theID = jQuery( this ).attr( 'data-id' );
            jQuery( '#wpsc_processing' ).modal( 'show' );
            var data = {
                'action': 'wpsc_delete_template',
                'template_id': theID
            };
            jQuery.post(wpsc_localize_tickets.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        document.location.href = document.location.href;
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // select / deselect all templates
    jQuery( document ).on( 'click', '#wpsc_select_all_templates', function() {
        if ( jQuery( this ).is( ':checked' ) ) {
            jQuery( '.wpsc_select_template' ).prop( 'checked', true );
            jQuery( '#wpsc_template_actions').show();
        } else {
            jQuery( '.wpsc_select_template' ).prop( 'checked', false );
            jQuery( '#wpsc_template_actions').hide();
        }
    });
    // ***************************************************************************************************
    // select / deselect single template
    jQuery( document ).on( 'click', '.wpsc_select_template', function() {
        if ( jQuery( this ).is( ':checked' ) ) {
            if ( jQuery( '.wpsc_select_template' ).length == jQuery( '.wpsc_select_template:checked' ).length ) {
                jQuery( '#wpsc_select_all_templates' ).prop( 'checked', true );
            } else {
                jQuery( '#wpsc_select_all_templates' ).prop( 'checked', false );
            }
        } else {
            jQuery( '#wpsc_select_all_templates' ).prop( 'checked', false );
        }
        if ( jQuery( '.wpsc_select_template:checked' ).length == 0 ) {
            jQuery( '#wpsc_template_actions').hide();
        } else {
            jQuery( '#wpsc_template_actions').show();
        }
    });
    // ***************************************************************************************************
    // delete selected template
    jQuery( document ).on( 'click', '#wpsc_delete_selected_templates', function() {
        var theIDs = '';
        jQuery( '.wpsc_select_template' ).each( function() {
            if ( jQuery( this ).is( ':checked' ) ) {
                if ( theIDs == '' ) {
                    theIDs = jQuery( this ).val();
                } else {
                    theIDs = theIDs + ',' + jQuery( this ).val();
                }
            }
        });
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var data = {
            'action': 'wpsc_delete_selected_templates',
            'theIDs': theIDs
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // create new reply template
    jQuery( document ).on( 'click', '#wpsc_save_new_template', function() {
        var wpsc_template_label = jQuery( '#wpsc_new_template_label' ).val();
        if ( wpsc_template_label == '' ) {
            jQuery( '#wpsc_new_template_label' ).addClass( 'wpsc_field_error' ).focus();
            return false;
        }
        var wpsc_template = CKEDITOR.instances['wpsc_new_template'].getData();
        if ( wpsc_template == '' ) {
            alert( 'Template cannot be empty!' );
            return false;
        }
        jQuery( '#wpsc_processing' ).modal( 'show' );
        var data = {
            'action': 'wpsc_save_new_template',
            'wpsc_template_label': wpsc_template_label,
            'wpsc_template': wpsc_template
        };
        CKEDITOR.instances['wpsc_new_template'].resetDirty();
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    document.location.href = document.location.href;
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // ticket search
    jQuery( document ).on( 'click', '#wpsc_front_search', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
    });
    // ***************************************************************************************************
    // clear ticket search
    jQuery( document ).on( 'click', '#wpsc_front_clear_search', function() {
        jQuery( '#wpsc_processing' ).modal( 'show' );
        document.location.href = document.location.href;
    });
    // ***************************************************************************************************
    // wpsc_front_ticket_row
    jQuery( document ).on( 'click', '.wpsc_front_ticket_row td', function() {
    	if ( false === jQuery( this ).hasClass( 'wpsc_select_ticket_td' ) ) {
	        var theID = jQuery( this ).closest( 'tr' ).attr( 'id' );
	        if ( jQuery( '#wpsc_front_ticket_' + theID ).length == 0 ) {
	            jQuery( "#wpsc_processing" ).modal( 'show' );
	            loadFrontTicket( theID );
	        }
		}
    });
    // ***************************************************************************************************
    // wpsc_front_tab_close
    jQuery( document ).on( 'click', '.wpsc_front_tab_close', function() {
        var uid = jQuery( '#wpsc_wrap').attr( 'data-id' );
        var theID = jQuery( this ).closest( 'li' ).remove().attr( 'data-id' );
        jQuery( '#wpsc_front_ticket_' + theID ).remove();
        var theTickets = [];
        jQuery( '.wpsc_front_ticket_tab' ).each( function() {
            theTickets.push( jQuery( this ).closest( "li" ).attr( 'data-id' ) );
        });
        var theIDs = theTickets.join();
        setCookie( 'wpsc_open_tickets_' + uid, theIDs, 30 );
        setCookie( 'wpsc_active_ticket_' + uid, '', 30 );
        jQuery( '#wpsc_front_tabs a:first').tab( 'show' );
    });
    // ***************************************************************************************************
    // tab status glyphicon
    jQuery( document ).on( 'shown.bs.collapse', '.collapse', function() {
        jQuery( this ).parent().find( ".glyphicon-plus" ).removeClass( "glyphicon-plus" ).addClass( "glyphicon-minus" );
    }).on( 'hidden.bs.collapse', '.collapse', function() {
        jQuery( this ).parent().find( ".glyphicon-minus" ).removeClass( "glyphicon-minus" ).addClass( "glyphicon-plus" );
    });
    // ***************************************************************************************************
    // send reply
    jQuery( document ).on( 'click', '.wpsc_front_new_thread_button', function() {
        var uid = jQuery( '#wpsc_wrap').attr( 'data-id' );
        var theID = jQuery( this ).attr( 'data-id' );
        var data = new FormData();
        data.append( 'action', 'wpsc_client_reply' );
        data.append( 'wpsc_ticket_id', theID );
        data.append( 'wpsc_uid', uid );
        var wpsc_front_new_thread_priority = jQuery( '#wpsc_front_new_thread_priority_' + theID ).val();
        if (wpsc_front_new_thread_priority == '' ) {
            jQuery( '#wpsc_front_new_thread_priority_' + theID ).addClass( 'wpsc_field_error' );
            return false;
        }
        data.append( 'wpsc_front_new_thread_priority', wpsc_front_new_thread_priority );
        var wpsc_front_ticket_note = CKEDITOR.instances['wpsc_front_ticket_note_' + theID].getData();
        if (wpsc_front_ticket_note == '' ) {
            alert( 'Reply content cannot be empty.' );
            return false;
        }
        data.append( 'wpsc_front_ticket_note', wpsc_front_ticket_note );
        var wpsc_front_new_thread_to = jQuery( '#wpsc_front_new_thread_to_' + theID ).val();
        data.append( 'wpsc_front_new_thread_to', wpsc_front_new_thread_to );
        var wpsc_front_new_thread_cc = jQuery( '#wpsc_front_new_thread_cc_' + theID ).val();
        data.append( 'wpsc_front_new_thread_cc', wpsc_front_new_thread_cc );
        var wpsc_front_new_thread_bcc = jQuery( '#wpsc_front_new_thread_bcc_' + theID ).val();
        data.append( 'wpsc_front_new_thread_bcc', wpsc_front_new_thread_bcc );
        var theFiles = jQuery( '#wpsc_front_new_thread_attachments_' + theID ).prop( 'files' );
        jQuery.each( theFiles, function( i, obj ) {
            data.append( 'wpsc_file[' + i + ']', obj);
        });
        CKEDITOR.instances['wpsc_front_ticket_note_' + theID].resetDirty();
        jQuery( '#wpsc_processing' ).modal( 'show' );
        jQuery.ajax({
            url: wpsc_localize_admin.wpsc_ajax_url,
            type: 'POST',
            data: data,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        loadFrontTicket( theID );
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            }
        });
    });
    // ***************************************************************************************************
    // wpsc_front_new_ticket_save
    jQuery( document ).on( 'click', '.wpsc_front_new_ticket_button', function() {
        var doValidate = true;
        var uid = jQuery( '#wpsc_wrap').attr( 'data-id' );
        jQuery( '.wpsc_field_error' ).removeClass( 'wpsc_field_error' );
        jQuery( '.wpsc_front_new_ticket_validate' ).each( function() {
            if ( jQuery( this ).val() == '' ) {
                jQuery( this ).addClass( 'wpsc_field_error' );
                doValidate = false;
            }
        });
        jQuery( '.wpsc_additional_field_required' ).each( function() {
        	var theDataID = jQuery( this ).attr( 'data-ticket-id' );
        	//var theCat = jQuery( this ).closest( '.wpsc-additional-fields' ).attr( 'data-category' );
			//if ( isNaN( theDataID ) || theDataID == '' && theCat == jQuery( '#wpsc_front_new_ticket_category' ).val() ) {
			if ( isNaN( theDataID ) || theDataID == '' ) {
	            if ( jQuery( this).is( ':visible' ) && jQuery( this ).val() == '' ) {
	                jQuery( this ).addClass( 'wpsc_field_error' );
	                doValidate = false;
	            }
			}
        });
        if ( doValidate !== false ) {
            jQuery( "#wpsc_processing" ).modal( 'show' );
            var data = new FormData();
            data.append( 'action', 'wpsc_front_new_ticket_save' );
            jQuery( '.wpsc_front_new_ticket' ).each( function() {
                var theID = jQuery( this ).attr( 'id' );
                var theVal = jQuery( this ).val();
                data.append( theID, theVal );
            });
            var wpsc_front_new_ticket_details = CKEDITOR.instances['wpsc_front_new_ticket_details'].getData();
            data.append( 'wpsc_front_new_ticket_details', wpsc_front_new_ticket_details );
            if ( jQuery( '#wpsc_front_new_ticket_attachments' ).length ) {
	            var theFiles = jQuery( '#wpsc_front_new_ticket_attachments' ).prop( 'files' );
	            jQuery.each( theFiles, function( i, obj ) {
	                data.append( 'wpsc_file[' + i + ']', obj);
	            });
			}
			var c = 0;
            var additional_fields = [];
            if ( jQuery( '.wpsc_additional_field' ).length ) {
            	jQuery( '.wpsc_additional_field' ).each( function() {
            		if ( jQuery( this).is( ':visible' ) ) {
	            		var theDataID = jQuery( this ).attr( 'data-ticket-id' );
	            		//var theCat = jQuery( this ).closest( '.wpsc-additional-fields' ).attr( 'data-category' );
						//if ( isNaN( theDataID ) || theDataID == '' && theCat == jQuery( '#wpsc_front_new_ticket_category' ).val() ) {
						if ( isNaN( theDataID ) || theDataID == '' ) {
		            		additional_fields[c] = {};
		            		additional_fields[c].field_id = jQuery( this ).attr( 'id' );
		            		additional_fields[c].meta_value = jQuery( this ).val();
		            		c = c + 1;
		            	}
		           }
            	});
            	var json = JSON.stringify( additional_fields );
            	data.append( 'wpsc_additional_field', json );
            }
            CKEDITOR.instances['wpsc_front_new_ticket_details'].resetDirty();
            jQuery.ajax({
                url: wpsc_localize_admin.wpsc_ajax_url,
                type: 'POST',
                data: data,
                async: true,
                cache: false,
                contentType: false,
                processData: false,
                success: function( response ) {
                    response = jQuery.trim( response );
                    try {
                        response = jQuery.parseJSON( response );
                        if ( response.status == 'true' ) {
                            var uid = response.uid;
                            var theTickets = [];
                            theTickets.push( response.ticket_id );
                            jQuery( '.wpsc_front_ticket_tab' ).each( function() {
                                theTickets.push( jQuery( this ).closest( "li" ).attr( 'data-id' ) );
                            });
                            var theIDs = theTickets.join();
                            setCookie( 'wpsc_open_tickets_' + uid, theIDs, 30 );
                            if ( response.new_user != 'true' ) {
                                setCookie( 'wpsc_active_ticket_' + uid, response.ticket_id, 30 );
                            } else {
                                setCookie( 'wpsc_active_ticket_' + uid, '', 30 );
                            }
                            if ( wpsc_localize_admin.wpsc_thanks_page != '0') {
                                document.location.href = wpsc_localize_admin.wpsc_thanks_page;
                            } else {
                                var theMessage = '';
                                theMessage = theMessage + '<p><strong>Congratulations! ' + wpsc_localize_admin.wpsc_item + ' #' + response.ticket_id + ' has been created!</strong></p>';
                                if ( response.new_user == 'true' ) {
                                    theMessage = theMessage + '<p>As part of the creation of this ' + wpsc_localize_admin.wpsc_item + ' you have been registered as a new user.  Please check your email for your registration activation email and follow the instructions to complete your registration.</p>';
                                } else {
                                    if ( jQuery( '#wpsc_wrap').attr( 'data-id' ) == 0 ) {
                                        theMessage = theMessage + '<p>Please <a href="' + wpsc_localize_admin.wpsc_login_url + '">log in</a> to view your ticket.</p>';
                                    }
                                }
                                jQuery( '#wpsc_front_new_ticket_created_body').html( theMessage );
                                jQuery( "#wpsc_front_new_ticket_created_dialog" ).modal( 'show' );
                                jQuery( "#wpsc_processing" ).modal( 'hide' );
                            }
                        } else {
                            console.log(response);
                            jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                            jQuery( "#wpsc_processing" ).modal( 'hide' );
                        }
                    }
                    catch( err ) {
                        console.log( err );
                        jQuery( '#wpsc_processing' ).modal( 'hide' );
                    }
                }
            });
        }
    });
    // ***************************************************************************************************
    // redirect after new ticket creation modal close
    jQuery( '#wpsc_front_new_ticket_created_dialog' ).on( 'hidden.bs.modal', function() {
        jQuery( "#wpsc_processing" ).modal( 'show' );
        document.location.href = document.location.href;
    });
    // ***************************************************************************************************
    // piping catch all email preview
    jQuery( document ).on( 'click', '.email_row', function() {
        jQuery( "#wpsc_processing" ).modal( 'show' );
        var theID = jQuery( this ).attr( 'data-id' );
        var theAccount = jQuery( this ).attr( 'data-account' );
        var data = {
            'action': 'wpsc_get_email_preview',
            'email_id': theID,
            'account': theAccount,
            'cit_debug': 'false'
        };
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                    if ( jQuery( '#wpsc_email_preview_modal' ).length ) {
                        jQuery( '#wpsc_email_preview_modal' ).each( function() {
                            jQuery( this ).remove();
                        });
                    }
                    jQuery( response.modal ).appendTo( 'body' );
                    jQuery( '#wpsc_email_preview_modal' ).modal( 'show' );
                    jQuery( '#wpsc_email_preview_modal .modal-dialog' ).css( { 'width': jQuery( window ).width() * 0.8, 'height': jQuery( window ).height() * 0.8, 'overflow': 'auto' } );
                    jQuery( '#wpsc_email_preview_modal iframe' ).css( { 'width': '100%', 'height': jQuery( '#wpsc_email_preview_modal .modal-dialog' ).height() * 0.45 } );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
    });
    // ***************************************************************************************************
    // piping catch all copy to new thread
    jQuery( document ).on( 'click', '#wpsc_piping_new_thread', function() {
        if ( jQuery( '#wpsc_open_tickets' ).is( ':visible' ) ) {
            jQuery( '#wpsc_email_preview_modal' ).modal( 'hide' );
            jQuery( "#wpsc_processing" ).modal( 'show' );
            var uid = jQuery( this ).attr( 'data-uid' );
            var accountID = jQuery( this ).attr( 'data-accountid' );
            var ticketID = jQuery( '#wpsc_open_tickets').val();
            var data = {
                'action': 'wpsc_add_email_to_ticket',
                'uid': uid,
                'account_id': accountID,
                'ticket_id': ticketID
            }; //'email_id': emailID,
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status == 'true' ) {
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                        jQuery( '#wpsc_add_email_to_ticket_dialog' ).modal( 'show' );
                    } else {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        } else {
            jQuery( '#wpsc_open_tickets' ).show();
        }
    });
    // ***************************************************************************************************
    // new ticket from piping email
    jQuery( document ).on( 'click', '#wpsc_piping_new_ticket', function() {
        jQuery( '#wpsc_email_preview_modal' ).modal( 'hide' );
        jQuery( "#wpsc_processing" ).modal( 'show' );
        var uid = jQuery( this ).attr( 'data-uid' );
        var accountID = jQuery( this ).attr( 'data-accountid' );
        var email_id = jQuery( this ).attr( 'data-id' );
        document.location.href = wpsc_localize_admin.wpsc_admin_url + '?page=wp-support-centre&uid=' + uid + '&account_id=' + accountID;
        /*var data = {
            'action': 'wpsc_new_ticket_from_piping',
            'uid': uid,
            'account_id': accountID
        }; // 'email_id': email_id
        jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
                if ( response.status == 'true' ) {
                    jQuery( '.nav-tabs a[href="#wpsc_admin_new_ticket"' ).tab( 'show' );
                    jQuery( '#wpsc_admin_client_autocomplete' ).val( response.client );
                    jQuery( '#wpsc_admin_new_ticket_client' ).val( response.client );
                    jQuery( '#wpsc_admin_new_ticket_client_email' ).val( response.client_email );
                    jQuery( '#wpsc_admin_new_ticket_to' ).val( response.client_email );
                    jQuery( '#wpsc_admin_new_ticket_client_id' ).val( response.client_id );
                    jQuery( '#wpsc_admin_new_ticket_subject' ).val( response.subject );
                    jQuery( '#wpsc_admin_new_ticket_existing_attachments' ).val( response.attachments );
                    jQuery( '#wpsc_admin_new_ticket_timestamp' ).val( response.timestamp );
                    var editor = CKEDITOR.instances['wpsc_admin_new_ticket_details'];
                    if ( editor ) {
                    	CKEDITOR.instances['wpsc_admin_new_ticket_details'].setData( response.thread );
                    } else {
                    	jQuery( '#wpsc_admin_new_ticket_details' ).val( response.thread );
                    }
                    jQuery( '#wpsc_admin_new_ticket_subject' ).focus();
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                } else {
                    console.log(response);
                    jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                    jQuery( "#wpsc_processing" ).modal( 'hide' );
                }
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });*/
    });
    // ***************************************************************************************************
    // pinned thread
    jQuery( document ).on( 'click', '.wpsc_pinned_thread', function() {
        var theID = jQuery( this ).attr( 'data-id' );
        var ticketID = jQuery( this ).closest( '.wpsc_thread_panel_group' ).attr( 'data-id' );
        var theThread = jQuery( '.wpsc_thread_' + theID );
        var theCount = jQuery( this ).attr( 'data-count' );
        theSlot = 0;
        putThread = 0;
        if ( jQuery( this ).hasClass( 'glyphicon-star-empty' ) ) {
            jQuery( this ).removeClass( 'glyphicon-star-empty wpsc_unpinned_thread_' + ticketID ).addClass( 'glyphicon-star wpsc_pinned_thread_' + ticketID );
            jQuery( this ).closest( '.panel-heading' ).addClass( 'wpsc_thread_is_pinned' );
            jQuery( this ).closest( '.wpsc_ticket_thread_' + ticketID ).removeClass( 'wpsc_unpinned_thread_panel_' + ticketID ).addClass( 'wpsc_pinned_thread_panel_' + ticketID );
            var lenPinned = jQuery( '.wpsc_pinned_thread_' + ticketID ).length;
            if ( lenPinned == 1 ) {
                jQuery( theThread ).insertBefore( jQuery( '.wpsc_ticket_thread_' + ticketID + ':first' ) );
            } else {
                var pinnedList = jQuery( '.wpsc_pinned_thread_panel_' + ticketID );
                pinnedList.sort( function( a, b ) {
                    var contentA = parseInt( jQuery( a ).attr( 'data-count' ) );
                    var contentB = parseInt( jQuery( b ).attr( 'data-count' ) );
                    return ( contentA < contentB ) ? -1 : ( contentA > contentB ) ? 1 : 0;
                });
                jQuery( pinnedList ).prependTo( '#wpsc_admin_ticket_threads_' + ticketID );
            }
            var data = {
                'action': 'wpsc_pinned_thread',
                'thread_id': theID,
                'val': 1
            };
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status != 'true' ) {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        } else {
            jQuery( this ).removeClass( 'glyphicon-star wpsc_pinned_thread_' + ticketID ).addClass( 'glyphicon-star-empty wpsc_unpinned_thread_' + ticketID );
            jQuery( this ).closest( '.panel-heading' ).removeClass( 'wpsc_thread_is_pinned' );
            jQuery( this ).closest( '.wpsc_ticket_thread_' + ticketID ).removeClass( 'wpsc_pinned_thread_panel_' + ticketID ).addClass( 'wpsc_unpinned_thread_panel_' + ticketID );
            var pinnedList = jQuery( '.wpsc_unpinned_thread_panel_' + ticketID );
            pinnedList.sort( function( a, b ) {
                var contentA = parseInt( jQuery( a ).attr( 'data-count' ) );
                var contentB = parseInt( jQuery( b ).attr( 'data-count' ) );
                return ( contentA < contentB ) ? -1 : ( contentA > contentB ) ? 1 : 0;
            });
            var lenPinned = jQuery( '.wpsc_pinned_thread_' + ticketID ).length;
            if ( lenPinned == 0 ) {
                jQuery( pinnedList ).prependTo( '#wpsc_admin_ticket_threads_' + ticketID );
            } else {
                jQuery( pinnedList ).insertAfter( '.wpsc_pinned_thread_panel_' + ticketID + ':last' );
            }
            var data = {
                'action': 'wpsc_pinned_thread',
                'thread_id': theID,
                'val': 0
            };
            jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
                response = jQuery.trim( response );
                try {
                    response = jQuery.parseJSON( response );
                    if ( response.status != 'true' ) {
                        console.log(response);
                        jQuery( "#wpsc_ajax_error" ).modal( 'show' );
                        jQuery( "#wpsc_processing" ).modal( 'hide' );
                    }
                }
                catch( err ) {
                    console.log( err );
                    jQuery( '#wpsc_processing' ).modal( 'hide' );
                }
            });
        }
    });
    // ***************************************************************************************************
    // templates dialog modal actions
    jQuery( '#wpsc_templates_dialog' ).on( 'shown.bs.modal', function( e ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_admin_templates_select_table' ) ) {
            var theTable = jQuery( '#wpsc_admin_templates_select_table' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_admin_templates_select_table' ).DataTable();
            jQuery( '#wpsc_admin_templates_select_table tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    });
    // ***************************************************************************************************
    // notification shortcodes dialog modal actions
    jQuery( '#wpsc_notification_shortcodes' ).on( 'shown.bs.modal', function( e ) {
        if ( jQuery.fn.dataTable.isDataTable( '#wpsc_notification_shortcodes_table' ) ) {
            var theTable = jQuery( '#wpsc_notification_shortcodes_table' ).DataTable();
        } else {
            var theTable = jQuery( '#wpsc_notification_shortcodes_table' ).DataTable();
            jQuery( '#wpsc_notification_shortcodes_table tfoot th' ).each( function() {
                var theTitle = jQuery( this ).text();
                if ( theTitle != '' ) {
                    jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
                }
            });
            theTable.columns().every( function() {
                var that = this;
                jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                    if ( that.search() !== this.value ) {
                        that.search( this.value ).draw();
                    }
                });
            });
        }
    });
    // ***************************************************************************************************
    // signature shortcodes dialog modal actions
    jQuery( '#wpsc_signature_shortcodes' ).on( 'shown.bs.modal', function( e ) {
        var theTable = jQuery( '#wpsc_signature_shortcodes_table' ).DataTable();
        jQuery( '#wpsc_signature_shortcodes_table tfoot th' ).each( function() {
            var theTitle = jQuery( this ).text();
            if ( theTitle != '' ) {
                jQuery( this ).html( '<input type="text" placeholder="Search ' + theTitle + '" class="wpsc_datatables_search" />' );
            }
        });
        theTable.columns().every( function() {
            var that = this;
            jQuery( 'input', this.footer() ).on( 'keyup change', function () {
                if ( that.search() !== this.value ) {
                    that.search( this.value ).draw();
                }
            });
        });
    });
    // ***************************************************************************************************
    // tab actions on page load
    jQuery( 'a[data-toggle="tab"]' ).each( function() {
        var targetHref = jQuery( this ).attr( 'href' );
        var isVis = jQuery( targetHref ).is( ':visible' );
        if ( isVis ) {
            doDataTables( targetHref );
        }
    });
    // ***************************************************************************************************
    // accordion actions on accordion change
    jQuery( document ).on( 'shown.bs.collapse', '.wpsc_admin_ticket_thread_accordion', function( e ) {
		var theiFrame = jQuery( this ).find( 'iframe ');
		if ( !theiFrame.hasClass( 'wpsc_shown' ) ) {
			var theiFrameSrc = theiFrame.attr( 'data-src' );
			theiFrame.attr( 'src', theiFrameSrc ).addClass( 'wpsc_shown' );
		}
    });
    // ***************************************************************************************************
    // tab actions on tab change
    jQuery( document ).on( 'shown.bs.tab', 'a[data-toggle="tab"]', function( e ) {
        var theContainer = jQuery( this ).closest( 'ul' );
        if ( theContainer.attr( 'id' ) == 'wpsc_admin_tabs' ) {
            var theParent = jQuery( this ).closest( 'li' );
            if ( theParent.hasClass( 'wpsc_admin_ticket_tab' ) ) {
                setCookie( 'wpsc_active_ticket', theParent.attr( 'data-id' ), 30 );
                var adminBar = jQuery( '#wp-admin-bar-wpsc-admin-bar a' ).html().split( ' [' );
                var newAdminBar = adminBar[0] + ' [' + theParent.attr( 'data-id' ) + ']';
                jQuery( '#wp-admin-bar-wpsc-admin-bar a' ).html( newAdminBar );
                var getTitle = jQuery( 'title' ).text().split( ' - ' );
                if ( undefined !== getTitle[1] ) {
                	curTitle = getTitle[1];
                } else {
                	curTitle = getTitle[0];
                }
                var newTitle = '[' + theParent.attr( 'data-id' ) + '] - ' + curTitle;
                document.title = newTitle;
            } else {
                setCookie( 'wpsc_active_ticket', '', 30 );
                var adminBar = jQuery( '#wp-admin-bar-wpsc-admin-bar a' ).html().split( ' [' );
                var newAdminBar = adminBar[0];
                jQuery( '#wp-admin-bar-wpsc-admin-bar a' ).html( newAdminBar );
                var getTitle = jQuery( 'title' ).text().split( ' - ' );
                if ( undefined !== getTitle[1] ) {
                	curTitle = getTitle[1];
                } else {
                	curTitle = getTitle[0];
                }
                var newTitle = curTitle;
                document.title = newTitle;
            }
        } else if ( theContainer.attr( 'id' ) == 'wpsc_front_tabs' ) {
            var uid = jQuery( '#wpsc_wrap').attr( 'data-id' );
            var theParent = jQuery( this ).closest( 'li' );
            if ( theParent.hasClass( 'wpsc_front_ticket_tab' ) ) {
                setCookie( 'wpsc_active_ticket_' + uid, theParent.attr( 'data-id' ), 30 );
            } else {
                setCookie( 'wpsc_active_ticket_' + uid, '', 30 );
            }
        }
        var targetHref = jQuery( e.target ).attr( 'href' );
        doDataTables( targetHref );
    });
    // ***************************************************************************************************
	// Accordion exclusion dom elements
	jQuery( document ).on( 'click', '.no-collapsable', function( e ) {
		e.stopPropagation();
	});

	jQuery( document ).on( 'change', '.wpsc_category_select', function() {
		var theCategory = jQuery( this ).val();
		if ( jQuery( '.wpsc-additional-fields').length && theCategory != '' ) {
			jQuery( '.wpsc-additional-fields' ).each( function() {
				if ( jQuery( this ).attr( 'data-category' ) ) {
					var theCategories = jQuery( this ).attr( 'data-category' );
					var theCatsArray = theCategories.split( ',' );
					if ( theCatsArray.indexOf( theCategory ) != -1 ) {
						jQuery( this ).show();
					} else {
						jQuery( this ).hide();
					}
				}
			});
		}
	});

	jQuery( document ).on( 'change', '.wpsc_status_select', function() {
		var theStatus = jQuery( this ).val();
		if ( jQuery( '.wpsc-additional-fields').length && theStatus != '' ) {
			jQuery( '.wpsc-additional-fields' ).each( function() {
				if ( jQuery( this ).attr( 'data-status' ) ) {
					var theStatuses = jQuery( this ).attr( 'data-status' );
					var theStatsArray = theStatuses.split( ',' );
					if ( theStatsArray.indexOf( theStatus ) != -1 ) {
						jQuery( this ).show();
					} else {
						jQuery( this ).hide();
					}
				}
			});
		}
	});

	jQuery( '.wpsc_email_filter' ).click( function( e ) {
		e.preventDefault();
		var theDays = jQuery( this ).attr( 'data-days' );
		theHref = wpsc_localize_admin.wpsc_admin_url + '?page=wpsc_admin_mailbox&wpsc_mailbox_days=' + theDays;
		document.location.href = theHref;
	});

	jQuery( document ).on( 'click', '.wpsc-notice .notice-dismiss', function() {
		var notice_id = jQuery( '.wpsc-notice' ).attr( 'data-id' );
		var data = {
            action: 'wpsc_dismiss_notice',
            notice_id: notice_id
		};
		jQuery.post(wpsc_localize_admin.wpsc_ajax_url, data, function( response ) {
            response = jQuery.trim( response );
            try {
                response = jQuery.parseJSON( response );
            }
            catch( err ) {
                console.log( err );
                jQuery( '#wpsc_processing' ).modal( 'hide' );
            }
        });
	});
});