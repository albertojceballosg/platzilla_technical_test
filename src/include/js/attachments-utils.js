(function (jQuery) {
	var attachmentTemplate = jQuery ('#attachment-template').html (),
		totalAttachments   = 0;

	var loadFileData = function (file, attachment) {
		var reader = new FileReader ();
		reader.onload = function (evt) {
			attachment.find ('.attachment-data').val (evt.target.result);
		};
		reader.readAsDataURL (file);
	};

	var getAttachment = function (file, fieldName, numRow) {
		var attachment = jQuery (attachmentTemplate);
		attachment.find ('.attachment-name').text (file.name);
		attachment.find ('.attachment-size').text (' (' + (file.size / 1024).toFixed (2) + ' KB)');

		if (fieldName.indexOf ('[]') != -1) {
			attachment.find ('.attachment-data').attr ('name', fieldName + '[data]');
			attachment.find ('.attachment-filename').attr ('name', fieldName + '[filename]').val (numRow + '@' + file.name);
		} else {
			attachment.find ('.attachment-data').attr ('name', fieldName + '[' + totalAttachments + '][data]');
			attachment.find ('.attachment-filename').attr ('name', fieldName + '[' + totalAttachments + '][filename]').val (file.name);
		}
		return attachment;
	};

	var addAttachment = function (evt) {
		var target               = evt.target ? evt.target : evt.srcElement,
			files                = target.files,
			attachmentsContainer = jQuery (target).closest ('.attachments-field').find ('.attachments-container'),
			allowedMimeTypes     = attachmentsContainer.attr ('data-allowed-mimetypes'),
			fieldName            = attachmentsContainer.attr ('data-field-name'),
			maximumFileSize      = attachmentsContainer.attr ('data-maximum-file-size') ? attachmentsContainer.attr ('data-maximum-file-size') : 10,
			attachment, file;

		if ((!files) || (!(files instanceof FileList)) || (files.length === 0)) {
			return;
		}

		file = files [ 0 ];
		if ((allowedMimeTypes !== null) && (allowedMimeTypes !== undefined) && (allowedMimeTypes.trim () !== '')) {
			allowedMimeTypes = allowedMimeTypes.replace (new RegExp (' ', 'g'), '').split (',');
			if (jQuery.inArray (file.type, allowedMimeTypes) === -1) {
				alert ('El archivo suministrado no es de un tipo válido');
				return;
			}
		}

		if (file.size > (maximumFileSize * 1024 * 1024)) {
			alert ('El archivo suministrado supera el tamaño máximo permitido (' + maximumFileSize + 'MB)');
			return;
		}

		attachment = getAttachment (file, fieldName, 0);
		loadFileData (file, attachment);
		attachmentsContainer.empty ().append (attachment);
	};

	var addAttachments = function (evt) {
		var target               = evt.target ? evt.target : evt.srcElement,
			files                = target.files,
			attachmentsContainer = jQuery (target).closest ('.attachments-field').find ('.attachments-container'),
			allowedMimeTypes     = attachmentsContainer.attr ('data-allowed-mimetypes'),
			maximumFileSize      = attachmentsContainer.attr ('data-maximum-file-size') ? attachmentsContainer.attr ('data-maximum-file-size') : 10,
			numRow               = (((((jQuery (target)).parent ()).parent ()).parent ()).parent ()).parent ().attr ('numrowtr'),
			i, attachment, file;

		if ((!files) || (!(files instanceof FileList)) || (files.length === 0)) {
			return;
		}
		numRow = ( typeof numRow !== 'undefined') ? numRow : 0;

		for (i = 0; i < files.length; i += 1) {
			file = files [ i ];
			if ((allowedMimeTypes !== null) && (allowedMimeTypes !== undefined) && (allowedMimeTypes.trim () !== '')) {
				allowedMimeTypes = allowedMimeTypes.replace (new RegExp (' ', 'g'), '').split (',');
				if (jQuery.inArray (file.type, allowedMimeTypes) === -1) {
					alert ('El archivo suministrado no es de un tipo válido');
					return;
				}
			}

			if (file.size > (maximumFileSize * 1024 * 1024)) {
				alert ('El archivo ' + file.name + ' supera el tamaño máximo permitido (' + maximumFileSize + 'MB)');
				return;
			}
		}

		for (i = 0; i < files.length; i += 1) {
			file = files [ i ];
			attachment = getAttachment (file, attachmentsContainer.attr ('data-field-name'), numRow);
			loadFileData (file, attachment);
			attachmentsContainer.append (attachment);
			totalAttachments += 1;
		}
	};

	var addEntityAttachment = function (evt) {
		var target             = evt.target ? evt.target : evt.srcElement,
			files              = target.files,
			url                = window.location.href,
			dummy              = url.split ('&'),
			attachmentsSection = jQuery (target).closest ('.attachments-section'),
			allowedMimeTypes   = attachmentsSection.data ('allowed-mimetypes'),
			maximumFileSize    = attachmentsSection.data ('maximum-file-size') ? attachmentsSection.data ('maximum-file-size') : 10,
			moduleName         = attachmentsSection.data ('module-name'),
			entityId           = attachmentsSection.data ('entity-id'),
            isModal            = attachmentsSection.data ('modal'),
			idElement 		   = attachmentsSection.data ('element-id'),
			reportId           = attachmentsSection.data ('report-id') || 0,
			file, reader, row, template;

		if ((!files) || (!(files instanceof FileList)) || (files.length === 0)) {
			return;
		}

		file = files [ 0 ];
		if ((allowedMimeTypes !== null) && (allowedMimeTypes !== undefined) && (allowedMimeTypes.trim () !== '')) {
			allowedMimeTypes = allowedMimeTypes.replace (new RegExp (' ', 'g'), '').split (',');
			if (jQuery.inArray (file.type, allowedMimeTypes) === -1) {
				alert ('El archivo suministrado no es de un tipo válido');
				return;
			}
		}

		if (file.size > (maximumFileSize * 1024 * 1024)) {
			alert ('El archivo suministrado supera el tamaño máximo permitido (' + maximumFileSize + 'MB)');
			return;
		}

		reader = new FileReader ();
		reader.onload = function (evt) {
			var attachmentData = evt.target.result,
				arguments;
			arguments = [
				'module=' + encodeURIComponent (moduleName),
				'action=UploadAttachment',
				'entityid=' + encodeURIComponent (entityId),
				'filedata=' + encodeURIComponent (attachmentData),
				'filename=' + encodeURIComponent (file.name),
				'reportid=' + encodeURIComponent (reportId),
				'Ajax=true'
			];
			jQuery.ajax ('index.php', {
				data:     arguments.join ('&'),
				dataType: 'json',
				method:   'post'
			}).done (function (response) {
				var attachment;

				if ((!response.hasOwnProperty ('attachmentid')) || (!response.hasOwnProperty ('url'))) {
					alert ('Se ha presentado un error. Intenta más tarde');
					return;
				}

				if (attachmentTemplate === undefined) {
					attachmentTemplate = jQuery ('#attachment-template').html ();
				}

				attachment = jQuery (attachmentTemplate).attr ('data-attachment-id', response [ 'attachmentid' ]);
				attachment.find ('a').attr ('href', response.url).attr ('title', file.name);
				attachment.find ('.attachment-name').text (file.name);
				attachment.find ('.attachment-size').text (' (' + file.size + ' KB)');
				console.log(attachment);
				jQuery ('.attachments-container').append (attachment);
				console.log (idElement);
                if (isModal == 1) {
					if (moduleName === 'diagnostic_report') {
						if (dummy[4] !== 'tab=destination') {
							window.location.href += '&tab=destination';
						} else {
							window.location.reload ();
						}
					} else {
						window.location.reload ();
					}
                    window.location.reload ();
                } else if (isModal == 4) {
					// Cerrar el modal de adjuntos para reportes de actividad
					jQuery('.modal').modal('hide');
					jQuery('.modal-backdrop').remove();
					jQuery('body').removeClass('modal-open');
				} else if ((idElement !== undefined) && isModal == 3) {
					row      = jQuery ('#tbody-' + idElement);
					template = jQuery ('#document-tr-' + idElement).html ()
						.replace (/__ID__/g, response [ 'attachmentid' ])
						.replace (/__NAME__/g, encodeURIComponent (file.name));
					row.append (template);
					console.log (row);
					console.log(idElement)
				}
			}).fail (function (jQueryResponse) {
				alert ('Se ha presentado un error: ' + jQueryResponse.responseText);
			});
		};
		reader.readAsDataURL (file);
	};

	var deleteAttachment = function (button) {
		var attachment = jQuery (button).closest ('.attachment');
		if (!confirm ("¿Estás seguro que quieres eliminar el anexo '" + attachment.find ('.attachment-name').text () + "'?")) {
			return;
		}
		attachment.remove ();
	};

	var deleteEntityAttachment = function (button) {
		var attachment   = jQuery (button).closest ('.attachment'),
			attachmentId = attachment.data ('attachment-id'),
			entityId     = attachment.closest ('.attachments-section').data ('entity-id'),
			moduleName   = attachment.closest ('.attachments-section').data ('module-name'),
            isModal      = attachment.closest ('.attachments-section').data ('modal'),
			arguments;
		if (!confirm ("¿Estás seguro que quieres eliminar el anexo '" + attachment.find ('.attachment-name').text () + "'?")) {
			return;
		}

		arguments = [
			'module=' + encodeURIComponent (moduleName),
			'action=DeleteAttachment',
			'entityid=' + encodeURIComponent (entityId),
			'attachmentid=' + encodeURIComponent (attachmentId),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			attachment.remove ();
            if (isModal == 1) {
                window.location.reload ();
            }
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error: ' + jQueryResponse.responseText);
		});
	};

	window.AttachmentsUtils = {
		addAttachment:          addAttachment,
		addEntityAttachment:    addEntityAttachment,
		addAttachments:         addAttachments,
		deleteAttachment:       deleteAttachment,
		deleteEntityAttachment: deleteEntityAttachment
	};
} (jQuery));