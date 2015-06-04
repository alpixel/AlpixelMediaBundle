CKEDITOR.dialog.add( 'uploaderDialog', function ( editor ) {
    return {
        title: 'Upload manager',
        minWidth: 800,
        minHeight: 400,
        contents: [
            {
                id:     'tab-basic',
                label:  'Upload files',
                elements: [
                    {
                        type:   'file',
                        id:     'upload',
                        label:  'Séléctionner un fichier (Veuillez attendre la fin du téléchargement avant de cliquer sur OK)',
                        size:   38,
                        // filebrowserUploadUrl From Symfony app/config.yml
                        action: editor.ui.editor.config.filebrowserUploadUrl,
                    },
                    {
                        type:   'fileButton',
                        id:     'fileId',
                        label: 'Upload',
                        'for': [ 'tab-basic', 'upload' ]
                    }
                ]
            },
        ],
        onOk: function() {
            var dialog = this;
            var img    = editor.document.createElement('img');

            img.setAttribute('src', ALPIXEL_CKEDITOR_URL_UPLOAD);
            editor.insertElement( img );
        }
    };
});