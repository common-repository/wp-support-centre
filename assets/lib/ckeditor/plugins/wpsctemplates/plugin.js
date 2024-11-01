CKEDITOR.plugins.add( 'wpsctemplates', {
    init: function( editor ) {
        // Plugin logic goes here...
        editor.addCommand( 'wpsctemplates', {
            exec: function( editor ) {
                wpsc_template_dialog( editor );
            }
        });
        editor.ui.addButton( 'wpsctemplates', {
            label: 'Insert Reply Template',
            command: 'wpsctemplates',
            icon: this.path + 'icons/template.png'
        });
    }
});