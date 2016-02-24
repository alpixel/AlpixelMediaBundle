CKEDITOR.plugins.add( 'fileuploader', {
    icons: 'fileuploader',
    init: function( editor ) {
        editor.addCommand( 'uploader-cmd', new CKEDITOR.dialogCommand('uploaderDialog') );
        editor.ui.addButton( 'Uploader', {
            label: 'Upload your files',
            command: 'uploader-cmd',
            icons: this.path + 'fileuploader/fileuploader.png',
            toolbar: 'upload'
        });

        CKEDITOR.dialog.add('uploaderDialog', this.path + 'dialogs/uploader.js' );
    }
});

// Without this global variable we can't pass url to editor
var ALPIXEL_CKEDITOR_URL_UPLOAD = '';

// Callback function triggered by javascript content from response ajax
var uploadedFile = CKEDITOR.tools.addFunction( function(url) {
    ALPIXEL_CKEDITOR_URL_UPLOAD = url;
} );
