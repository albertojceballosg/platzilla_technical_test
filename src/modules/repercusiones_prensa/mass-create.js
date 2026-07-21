(function (jQuery) {
	var IMAGE_MAX_WIDTH              = 600,
		DUPLICATE_COMPLETE           = 0,
		DUPLICATE_TITLE_AND_IMAGES   = 1,
		DUPLICATE_ONLY_TITLE         = 2,
		DUPLICATE_ALL_BUT_RELATED_TO = 3,
		attachmentTemplate           = jQuery ('#attachment-template').html (),
		repercussionTemplate         = jQuery ('#repercussion-template').html (),
		totalAttachments             = 0,
		totalRepercussions           = 1;

	var render = function (src, attachment) {
		var image = new Image ();
		image.onload = function () {
			var canvas = attachment.find ('canvas.image') [ 0 ];
			if (image.width > IMAGE_MAX_WIDTH) {
				image.height = image.height * (IMAGE_MAX_WIDTH / image.width);
				image.width = IMAGE_MAX_WIDTH;
			}
			var ctx = canvas.getContext ('2d');
			ctx.clearRect (0, 0, canvas.width, canvas.height);
			canvas.width = image.width;
			canvas.height = image.height;
			ctx.drawImage (image, 0, 0, image.width, image.height);
		};
		image.src = src;
		attachment.find ('.field-attachment-data').val (src);
	};

	var loadImage = function (file, attachment) {
		var reader = new FileReader ();
		reader.onload = function (evt) {
			render (evt.target.result, attachment);
		};
		reader.readAsDataURL (file);
	};

	var getAttachment = function (file, repercussionId, totalAttachments) {
		var attachment = jQuery (attachmentTemplate.replace (new RegExp ('__repercussion-id__', 'g'), repercussionId).replace (new RegExp ('__attachment-id__', 'g'), totalAttachments));
		attachment.find ('.name').html (file.name);
		attachment.find ('.field-attachment-filename').val (file.name);
		return attachment;
	};

	var addAttachments = function (evt) {
		var target               = evt.target ? evt.target : evt.srcElement,
			files                = target.files,
			errors               = [],
			repercussion         = jQuery (evt.currentTarget).closest ('.repercussion'),
			attachmentsContainer = repercussion.find ('.attachments-container'),
			repercussionId       = repercussion.attr ('data-id'),
			i, n, attachment, file;

		if ((!files) || (!(files instanceof FileList)) || (files.length === 0)) {
			return;
		}
		n = files.length;
		for (i = 0; i < n; i += 1) {
			file = files [ i ];
			if (!file.type.match (/image.*/)) {
				errors.push ('El archivo ' + file.name + ' no es una imagen');
				continue;
			}

			attachment = getAttachment (file, repercussionId, totalAttachments);
			attachmentsContainer.append (attachment);
			loadImage (file, attachment);
			totalAttachments += 1;
		}
	};

	var addRepercussion = function (actionButton) {
		var button, panel, repercussionId, template;

		if (!validateRepercussions ()) {
			return;
		}

		button = jQuery (actionButton);
		panel = button.closest ('.panel');
		repercussionId = totalRepercussions;
		template = jQuery (repercussionTemplate.replace (new RegExp ('__repercussion-id__', 'g'), repercussionId));
		template.find ('.date-field').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		if (panel.length === 0) {
			jQuery ('#repercussions').append (template);
		} else {
			panel.after (template);
		}
		totalRepercussions += 1;
	};

	var closeImageViewer = function (closeButton) {
		jQuery (closeButton).closest ('.image-viewer').remove ();
	};

	var deleteAttachment = function (deleteButton) {
		var attachment = jQuery (deleteButton).closest ('.attachment');
		if (!confirm ("¿Estás seguro que quieres eliminar el anexo '" + attachment.find ('.name').html () + "'?")) {
			return;
		}
		attachment.remove ();
	};

	var deleteRepercussion = function (deleteButton) {
		var panel = jQuery (deleteButton).closest ('.panel');
		if (!confirm ('¿Estás seguro que quieres eliminar la repercusión?')) {
			return;
		}
		panel.remove ();
	};

	var duplicateRepercussion = function (actionButton, duplicationType) {
		var button                     = jQuery (actionButton),
			panel                      = button.closest ('.panel'),
			oldRepercussion            = panel.find ('.repercussion'),
			oldRepercussionOtherFields = oldRepercussion.find ('.other-field'),
			newRepercussionId          = totalRepercussions,
			newRepercussionTemplate    = repercussionTemplate.replace (new RegExp ('__repercussion-id__', 'g'), newRepercussionId),
			newRepercussion            = jQuery (newRepercussionTemplate),
			newAttachmentsContainer    = newRepercussion.find ('.attachments-container'),
			oldAttachments, oldAttachment, newAttachment, oldAttachmentData, i, n, selector;

		if ((oldRepercussion.length === 0) || (!validateRepercussions ())) {
			return;
		}

		newRepercussion.find ('.field-title').val (oldRepercussion.find ('.field-title').val ());
		if (duplicationType === DUPLICATE_COMPLETE) {
			newRepercussion.find ('.field-related-id').val (oldRepercussion.find ('.field-related-id').val ());
			newRepercussion.find ('.field-related').val (oldRepercussion.find ('.field-related').val ());
		}
		if ((duplicationType === DUPLICATE_COMPLETE) || (duplicationType === DUPLICATE_ALL_BUT_RELATED_TO)) {
			newRepercussion.find ('.field-media-id').val (oldRepercussion.find ('.field-media-id').val ());
			newRepercussion.find ('.field-media').val (oldRepercussion.find ('.field-media').val ());
			newRepercussion.find ('.date-field').val (oldRepercussion.find ('.date-field').val ());
			newRepercussion.find ('.field-url').val (oldRepercussion.find ('.field-url').val ());
			n = oldRepercussionOtherFields.length;
			for (i = 0; i < n; i += 1) {
				selector = '.' + jQuery (oldRepercussionOtherFields [ i ]).attr ('class').replace (new RegExp (' ', 'g'), '.');
				newRepercussion.find (selector).val (oldRepercussion.find (selector).val ());
			}
		}
		if ((duplicationType === DUPLICATE_COMPLETE) || (duplicationType === DUPLICATE_ALL_BUT_RELATED_TO) || (duplicationType === DUPLICATE_TITLE_AND_IMAGES)) {
			oldAttachments = oldRepercussion.find ('.attachment');
			n = oldAttachments.length;
			for (i = 0; i < n; i += 1) {
				oldAttachment = jQuery (oldAttachments [ i ]);
				oldAttachmentData = oldAttachment.find ('.field-attachment-data').val ();
				newAttachment = jQuery (attachmentTemplate.replace (new RegExp ('__repercussion-id__', 'g'), newRepercussionId).replace (new RegExp ('__attachment-id__', 'g'), totalAttachments));
				newAttachment.find ('.name').html (oldAttachment.find ('name').html ());
				newAttachmentsContainer.append (newAttachment);
				render (oldAttachmentData, newAttachment);
				totalAttachments += 1;
			}
		}

		newRepercussion.find ('.field-title').change ();
		newRepercussion.find ('input.date-field').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		panel.after (newRepercussion);
		totalRepercussions += 1;
	};

	var updatePanelTitle = function (field) {
		var textField    = jQuery (field),
			repercussion = textField.closest ('.repercussion'),
			title        = field.id === 'title' ? textField : repercussion.find ('.field-title'),
			media        = field.id === 'media' ? textField : repercussion.find ('.field-media'),
			panelTitle   = repercussion.closest ('.panel').find ('.panel-title');
		panelTitle.find ('.title').html (title.val ());
		panelTitle.find ('.media').html (media.val ());
	};

	var validateRepercussions = function () {
		var field, value, attachments, repercussions, repercussion, i, n;

		repercussions = jQuery ('.repercussions').find ('.repercussion');
		if (repercussions.length === 0) {
			return true;
		}

		n = repercussions.length;
		for (i = 0; i < n; i += 1) {
			repercussion = jQuery (repercussions [ i ]);
			field = repercussion.find ('.field-title');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				repercussion.collapse ('show');
				field.focus ();
				alert ('Introduce el titular de la repercusión');
				return false;
			}

			field = repercussion.find ('.field-related');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				repercussion.collapse ('show');
				field.focus ();
				alert ('Selecciona la entidad relacionada a la publicación');
				return false;
			}

			field = repercussion.find ('.field-media');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				repercussion.collapse ('show');
				field.focus ();
				alert ('Selecciona el medio donde aparece la publicación');
				return false;
			}

			field = repercussion.find ('.date-field');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				repercussion.collapse ('show');
				field.focus ();
				alert ('Selecciona la fecha de la publicación');
				return false;
			}

			field = repercussion.find ('.field-url');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				repercussion.collapse ('show');
				field.focus ();
				alert ('Introduce el URL de la publicación');
				return false;
			}

			attachments = repercussion.find ('.attachment');
			if (attachments.length === 0) {
				repercussion.collapse ('show');
				field.focus ();
				alert ('Agrega las imágenes de la publicación');
				return false;
			}
		}
		return true;
	};

	var viewImage = function (link) {
		var attachment     = jQuery (link).closest ('.attachment'),
			viewerTemplate = jQuery ('#image-viewer-template').html (),
			viewer         = jQuery (viewerTemplate);
		viewer.find ('.viewer-content').attr ('src', attachment.find ('.field-attachment-data').val ());
		viewer.find ('.viewer-caption').html (attachment.find ('.name').html ());
		jQuery ('body').append (viewer);
	};

	window.MassCreateUtils = {
		DUPLICATE_COMPLETE:           DUPLICATE_COMPLETE,
		DUPLICATE_TITLE_AND_IMAGES:   DUPLICATE_TITLE_AND_IMAGES,
		DUPLICATE_ONLY_TITLE:         DUPLICATE_ONLY_TITLE,
		DUPLICATE_ALL_BUT_RELATED_TO: DUPLICATE_ALL_BUT_RELATED_TO,
		addAttachments:               addAttachments,
		addRepercussion:              addRepercussion,
		closeImageViewer:             closeImageViewer,
		deleteAttachment:             deleteAttachment,
		deleteRepercussion:           deleteRepercussion,
		duplicateRepercussion:        duplicateRepercussion,
		updatePanelTitle:             updatePanelTitle,
		validateRepercussions:        validateRepercussions,
		viewImage:                    viewImage
	};

	var onDocumentReadyHandler = function () {
		var titles = jQuery ('.field-title'),
			n      = titles.length,
			i;
		if (n === 0) {
			return;
		}

		for (i = 0; i < n; i += 1) {
			updatePanelTitle (titles [ i ]);
		}

		jQuery ('input.date-field').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	};

	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));