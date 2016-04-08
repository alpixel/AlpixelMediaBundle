(function ($) {
    $(function () {

        function showHideDropzoneButton(id, nb, max) {
            if (nb < max)
                $('div#' + id).parent().find('.dz-clickable').show();
            else
                $('div#' + id).parent().find('.dz-clickable').hide();
        }

        function refreshDropzoneValue(resultInput, uploadedFiles) {
            resultInput.val(uploadedFiles.join('#&#'));
        }

        $('.dropzone_widget').each(function (i, el) {
            Dropzone.autoDiscover = false;

            var uploadedFiles = [];
            var dropzoneId = $(this).data('id');
            var dropzoneUri = $(this).data('url');
            var uploadMultiple = ($(this).data('multiple') > 0);

            if (uploadMultiple)
                var maxFile = $(this).data('max-file');
            else
                var maxFile = 1;

            var resultInput = $('#' + dropzoneId + ' input');

            var mediaDropzone = new Dropzone("div#" + dropzoneId, {
                url: dropzoneUri,
                maxFiles: maxFile,
                uploadMultiple: uploadMultiple,
                parallelUploads: 1,
                maxFilesize: 10,
                clickable: 'p.add-' + dropzoneId,
                acceptedFiles: '.jpg, .png, .jpeg, .pdf, .doc, .docx, .xls, .xlsx',
                addRemoveLinks: true,
                dictDefaultMessage: '',
                previewTemplate: $('div#' + dropzoneId).parent().find('.previewTemplateFileDrop').html(),
                thumbnailWidth: "200",
                thumbnailHeight: "135",
                dictInvalidFileType: 'Mauvais type de fichier',
                dictRemoveFile: '',
                dictCancelUpload: '',
                dictCancelUploadConfirmation: 'Confirmer',
                dictMaxFilesExceeded: 'Limite de photo atteinte.',
                dictFileTooBig: 'Le fichier est trop volumineux (max 10 Mo)',
                init: function () {

                    /* DropZone */
                    var $this = this;

                    /* set MaxFiles to 1 by default */
                    $this.options.maxFiles = maxFile;

                    /* Load existing images for this okaz */
                    var backup_folder = $('div#' + dropzoneId).parent().find('.dropzone-backup');

                    if (backup_folder.length && backup_folder.has('img')) {
                        backup_folder.find('img').each(function (i, el) {
                            var mockFile = {name: "Filename", size: 12345};
                            var key = $(this).data('key');
                            Dropzone.forElement('div#' + dropzoneId).emit("addedfile", mockFile);
                            Dropzone.forElement('div#' + dropzoneId).emit("thumbnail", mockFile, $(this).attr('src'));

                            // Set $this.files.length right
                            $this.files.push(mockFile);

                            // set data-nb attr for remove link and remove associated hidden input */
                            mockFile.previewTemplate.setAttribute('data-rel', key);
                        });

                        $('.dz-remove').show();
                    }


                    /* Show clickable button if limit upload */
                    if ($this.files.length < $this.options.maxFiles)
                        $('div#' + dropzoneId).parent().find('.dz-clickable').show();
                    else {
                        $('div#' + dropzoneId).parent().find('.dz-clickable').hide();
                    }

                    /* Show clickable button only if file length < to the maxFile limit */
                    showHideDropzoneButton(dropzoneId, $this.files.length, $this.options.maxFiles);


                    /* Create Waiting message */
                    if ($('#pictureLoader').length == 0) {
                        var loader = $('<div />', {
                            'id': 'pictureLoader',
                            'html': '<div class="sk-fading-circle"><div class="sk-circle1 sk-circle"></div><div class="sk-circle2 sk-circle"></div><div class="sk-circle3 sk-circle"></div><div class="sk-circle4 sk-circle"></div><div class="sk-circle5 sk-circle"></div><div class="sk-circle6 sk-circle"></div><div class="sk-circle7 sk-circle"></div><div class="sk-circle8 sk-circle"></div><div class="sk-circle9 sk-circle"></div><div class="sk-circle10 sk-circle"></div><div class="sk-circle11 sk-circle"></div><div class="sk-circle12 sk-circle"></div></div><p>Merci de patienter pendant l\'upload de votre fichier...</p>'
                        }).insertAfter($('form .form-actions')).hide();
                    }
                }
            });

            mediaDropzone
                .on("success", function (file, response) {
                    file.previewElement.setAttribute('data-rel', response[0].id);
                    $(file.previewElement).find('.dz-progress').hide();
                    $(file.previewElement).find('.dz-remove').show();

                    file['data'] = response[0]
                    uploadedFiles.push(response[0].id);

                    refreshDropzoneValue(resultInput, uploadedFiles);

                    /* Show clickable button only if file length < to the maxFile limit */
                    showHideDropzoneButton(dropzoneId, mediaDropzone.files.length, mediaDropzone.options.maxFiles);

                    /* Show uploaded img : pdf or img */
                    if (response[0].path) {
                        var path = response[0].path;
                        $('div#' + dropzoneId).find('.dz-details img').attr('src', path);
                    }
                });

            mediaDropzone
                .on("removedfile", function (file) {
                    var newData = [];
                    $(uploadedFiles).each(function (i, el) {
                        if (el != file.data.id)
                            newData.push(uploadedFiles[i]);
                    });
                    uploadedFiles = newData;
                    refreshDropzoneValue(resultInput, uploadedFiles);
                });

            mediaDropzone
                .on("complete", function (file, response) {
                    /* Show clickable button only if file length < to the maxFile limit */
                    showHideDropzoneButton(dropzoneId, mediaDropzone.files.length, mediaDropzone.options.maxFiles);

                    /* Show submit button when uploading is finished */
                    $('form .form-actions').show();
                    $('#pictureLoader').hide();
                });

            mediaDropzone
                .on("maxfilesreached", function (file) {
                    $('div#' + dropzoneId).parent().find('.dz-clickable').hide();
                });

            mediaDropzone
                .on("maxfilesexceeded", function (file) {
                    $('div#' + dropzoneId).parent().find('.dz-clickable').hide();
                });

            mediaDropzone
                .on("error", function (file) {
                    mediaDropzone.removeFile(file);
                });


            mediaDropzone
                .on("sending", function (file, xhr, formData) {

                    /* Hide submit button while uploading */
                    $('form .form-actions').hide();
                    $('#pictureLoader').show();
                });


            mediaDropzone
                .on("removedfile", function (file) {
                    /* Show clickable button only if file length < to the maxFile limit */
                    showHideDropzoneButton(dropzoneId, mediaDropzone.files.length, mediaDropzone.options.maxFiles);
                });
        });
    });
})(jQuery);
