"use strict";

// Show or hide "Ajouter une nouvelle image" if current number of image is inferior to the max file limit
function showHideDropzoneButton(id, nb, max) {
    if (nb < max) {
        jQuery('div#' + id).parent().find('.dz-clickable').show();
    }
    else {
        jQuery('div#' + id).parent().find('.dz-clickable').hide();
    }
}

// Re-write the saved input value
function refreshDropzoneValue(resultInput, uploadedFiles) {
    // uploadedFiles is an array, so we use .join method to convert array key to string, with a tag separator #&#
    resultInput.val(uploadedFiles.join('#&#'));
}

function setupDropzone() {
    if ($ == undefined) {
        var $ = jQuery;
    }
    $('.dropzone_widget').each(function (i, el) {
        Dropzone.autoDiscover = false;

        // Set vars
        var
            uploadedFiles = [],
            dropzoneId = $(this).data('id'),
            dropzoneUri = $(this).data('url'),
            uploadMultiple = ($(this).data('multiple') > 0),
            mimeTypes = $(this).data('allowed-mime-types'),
            thumbnailWidth = ($(this).data('thumbnail-width') === 'undefined') ? 140 : $(this).data('thumbnail-width'),
            thumbnailHeight = ($(this).data('thumbnail-height') === 'undefined') ? 93 : $(this).data('thumbnail-height');

        if (window.console && window.console.log) {
            window.console.log('Setup dropzone ' + dropzoneId);
        }


        // Is multiple upload allowed ?
        var maxFile = 1;
        if (uploadMultiple)
            var maxFile = $(this).data('max-file');

        // Relative to the saved input
        var resultInput = $('#' + dropzoneId + ' input');
        try {
            var mediaDropzone = new Dropzone("div#" + dropzoneId, {
                url: dropzoneUri,
                maxFiles: maxFile,
                uploadMultiple: uploadMultiple,
                parallelUploads: 1,
                maxFilesize: 10,
                clickable: 'p.add-' + dropzoneId,
                acceptedFiles: mimeTypes,
                addRemoveLinks: true,
                dictDefaultMessage: '',
                previewTemplate: $('div#' + dropzoneId).parent().find('.previewTemplateFileDrop').html(),
                createImageThumbnails: false,
                thumbnailWidth: thumbnailWidth,
                thumbnailHeight: thumbnailHeight,
                dictInvalidFileType: 'Mauvais type de fichier',
                dictRemoveFile: '',
                dictCancelUpload: '',
                dictCancelUploadConfirmation: 'Confirmer',
                dictMaxFilesExceeded: 'Limite de photo atteinte.',
                dictFileTooBig: 'Le fichier est trop volumineux (max 10 Mo)',
                init: function () {

                    // relative to DropZone
                    var $this = this;

                    // set MaxFiles to 1 or more function to uploadMultiple var
                    $this.options.maxFiles = maxFile;

                    // Load existing images for this okaz
                    var backup_folder = $('div#' + dropzoneId).parent().find('.dropzone-backup');

                    // S'il y a des images existantes au chargement de la page
                    if (backup_folder.length && backup_folder.has('img')) {

                        // Boucle sur chaque image existante
                        backup_folder.find('img').each(function (i, el) {

                            // On set une variable mockFile qui servira ensuite notemment pour le removeFile
                            var mockFile = {
                                name: "preview_" + $(this).data('key'),
                                id: $(this).data('key') // Used for removeFile method
                            };

                            // Get the file ID
                            var key = $(this).data('key');

                            // Let Dropzone know there are images
                            Dropzone.forElement('div#' + dropzoneId).emit("addedfile", mockFile);
                            Dropzone.forElement('div#' + dropzoneId).emit("thumbnail", mockFile, $(this).attr('src'));

                            // Complete the var 'files' with existing files
                            $this.files.push(mockFile);

                            // Set to the thumbnail container with the file ID in a data-rel attribut
                            mockFile.previewTemplate.setAttribute('data-rel', key);
                            $(mockFile.previewElement).find('.dz-label').text($(this).attr('data-label'));

                            // Complete the uploadedFiles with existing files for the resultInput
                            uploadedFiles.push(key);
                        });

                        // Show all the remove button above existing images
                        $('.dz-remove').show();
                    }


                    // Show clickable button if limit upload
                    if ($this.files.length < $this.options.maxFiles) {
                        $('div#' + dropzoneId).parent().find('.dz-clickable').show();
                    }
                    else {
                        $('div#' + dropzoneId).parent().find('.dz-clickable').hide();
                    }

                    // Show clickable button only if file length < to the maxFile limit
                    showHideDropzoneButton(dropzoneId, $this.files.length, $this.options.maxFiles);


                    /* Create Waiting message */
                    if ($('#pictureLoader').length == 0) {
                        var loader = $('<div />', {
                            'id': 'pictureLoader',
                            'html': '<div class="sk-fading-circle"><div class="sk-circle1 sk-circle"></div><div class="sk-circle2 sk-circle"></div><div class="sk-circle3 sk-circle"></div><div class="sk-circle4 sk-circle"></div><div class="sk-circle5 sk-circle"></div><div class="sk-circle6 sk-circle"></div><div class="sk-circle7 sk-circle"></div><div class="sk-circle8 sk-circle"></div><div class="sk-circle9 sk-circle"></div><div class="sk-circle10 sk-circle"></div><div class="sk-circle11 sk-circle"></div><div class="sk-circle12 sk-circle"></div></div><p>Merci de patienter pendant l\'upload de votre fichier...</p>'
                        }).insertAfter($('form .form-actions')).hide();
                    }


                    // -- @-@ --
                    // EVENT LISTENERS
                    // -- @-@ --
                    $this
                        .on("success", function (file, response) {

                            if (response[0]['error'] == false) {
                                $('div#' + dropzoneId).trigger('alpixel_media.dropzone.upload.pre.success', file);
                                file['id'] = response[0].id;

                                // Set to the thumbnail container with the file ID in a data-rel attribut
                                file.previewElement.setAttribute('data-rel', response[0].id);

                                // Hide progress bar
                                $(file.previewElement).find('.dz-progress').hide();

                                // Show remove button
                                $(file.previewElement).find('.dz-remove').show();

                                // Create an array with the new uploaded file
                                var newFile = [response[0].id];

                                if (response[0]['path'] != undefined && $(file.previewElement).find('img').attr('src') == undefined) {
                                    $(file.previewElement).find('img').attr('src', response[0]['path']);
                                }

                                if (response[0]['name'] != undefined) {
                                    $(file.previewElement).find('.dz-label').text(response[0]['name']);
                                }

                                // Merge new array with existing files
                                $.merge(uploadedFiles, newFile);

                                // Refresh input value
                                refreshDropzoneValue(resultInput, uploadedFiles);
                                $('div#' + dropzoneId).trigger('alpixel_media.dropzone.upload.post.success', file);
                            } else {
                                $('div#' + dropzoneId).trigger('alpixel_media.dropzone.upload.error', file);
                                Dropzone.forElement('div#' + dropzoneId).emit("error", file, response[0]['errorMessage']);
                            }
                        })

                        .on("removedfile", function (file) {
                            // Create a new array. It will use to re-write uploadedFiles array
                            var newData = [];

                            // Fill the newData array
                            $(uploadedFiles).each(function (i, el) {
                                if (el != file.id) {
                                    newData.push(uploadedFiles[i]);
                                }
                            });

                            // Set uploadedFiles
                            uploadedFiles = newData;

                            // Refresh input value
                            refreshDropzoneValue(resultInput, uploadedFiles);

                            // Show clickable button only if file length < to the maxFile limit
                            showHideDropzoneButton(dropzoneId, $this.files.length, $this.options.maxFiles);
                        })

                        .on("complete", function (file) {
                            // Show clickable button only if file length < to the maxFile limit
                            showHideDropzoneButton(dropzoneId, $this.files.length, $this.options.maxFiles);

                            // Show submit button when uploading is finished
                            $('form .form-actions').show();

                            // Hide loader
                            $('#pictureLoader').hide();
                        })

                        .on("maxfilesreached", function (file) {
                            // Hide "Ajouter une nouvelle image" button
                            $('div#' + dropzoneId).parent().find('.dz-clickable').hide();
                        })

                        .on("maxfilesexceeded", function (file) {
                            // Hide "Ajouter une nouvelle image" button
                            $('div#' + dropzoneId).parent().find('.dz-clickable').hide();
                        })

                        .on("error", function (file, message) {

                            alert(message);

                            // Remove Preview file && cancel upload
                            $this.removeFile(file);
                        })

                        .on("sending", function (file, xhr, formData) {
                            // Hide submit button when sending new file
                            $('form .form-actions').hide();

                            // Show loader loader
                            $('#pictureLoader').show();
                        })

                        .on("addedfile", function (file, xhr, formData) {
                              var self = this;
                      
                              try {
                                  window.loadImage.parseMetaData(file, function (data) {

                                      // use embedded thumbnail if exists.
                                      if (data.exif) {

                                          var thumbnail = data.exif.get('Thumbnail');
                                          var orientation = data.exif.get('Orientation');

                                          if (thumbnail && orientation) {
                                              window.loadImage(thumbnail, function (img) {
                                                  self.emit('thumbnail', file, img.toDataURL());
                                              }, { orientation: orientation });
                                              return;
                                          }
                                      }
                                      // use default implementation for PNG, etc.
                                      self.createThumbnail(file);
                                  });
                              }
                              catch(err) {
                                  self.createThumbnail(file);
                                  console.warn('Merci de mettre à jour le alpixelmediabundle avec la dernière version du load-image.js');
                                  console.log(err);
                              }

                            // Check if nb of files is always inferior to the max files allowed
                            if ($this.files.length > $this.options.maxFiles) {
                                // Remove Preview file && cancel upload
                                this.removeFile(file);
                            }
                        });
                }
            });
        } catch (e) {

        }
    });
}

(function ($) {
    $(function () {
        setupDropzone();
    });
})(jQuery);
