CKEDITOR.dialog.add('uploaderDialog', function (editor) {
    return {
        title: 'Gestionnaire de téléchargement',
        minWidth: 300,
        minHeight: 150,
        contents: [
            {
                id: 'tab-basic',
                label: 'Upload files',
                elements: [
                    {
                        type: 'file',
                        id: 'upload',
                        label: 'Sélectionner un fichier',
                        size: 38,
                        // filebrowserUploadUrl From Symfony app/config.yml
                        action: editor.ui.editor.config.filebrowserUploadUrl
                    },
                    {
                        type: 'fileButton',
                        id: 'fileId',
                        class: 'cke_dialog_ui_button cke_dialog_ui_button_ok',
                        label: 'Envoyer le fichier',
                        labelStyle: 'color: #fff',
                        'for': ['tab-basic', 'upload'],
                        onClick: function () {
                            var ckDialog = window.CKEDITOR.dialog.getCurrent();
                            ckDialog.setState(CKEDITOR.DIALOG_STATE_BUSY);
                        }
                    }
                ]
            },
        ],
        onShow: function () {
            var ALPIXEL_CKEDITOR_UPLOAD = {};
        },
        onOk: function () {
            var isMimeType = /^image\//g;
            var testMatch = isMimeType.test(ALPIXEL_CKEDITOR_UPLOAD.mime);
            if (testMatch) {
                var img = editor.document.createElement('img');
                img.setAttribute('src', ALPIXEL_CKEDITOR_UPLOAD.url);
                editor.insertElement(img);
            } else if (ALPIXEL_CKEDITOR_UPLOAD.name != undefined) {
                var p = editor.document.createElement('p');
                p.appendHtml("<a href='" + ALPIXEL_CKEDITOR_UPLOAD.url + "'>Lien vers le fichier " + ALPIXEL_CKEDITOR_UPLOAD.name + "</a>");
                editor.insertElement(p);
            }
        }
    };
});

function uploadingError(error) {
    var ckDialog = window.CKEDITOR.dialog.getCurrent();
    ckDialog.setState(CKEDITOR.DIALOG_STATE_IDLE);
    ckDialog._.buttons['cancel'].click();
    alert(error);
};

$(document).on('media-wysiwyg-uploaded', function () {
    var ckDialog = window.CKEDITOR.dialog.getCurrent();
    ckDialog.setState(CKEDITOR.DIALOG_STATE_IDLE);
    var ckOk = ckDialog._.buttons['ok'];
    ckOk.click();
});
