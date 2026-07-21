(function (jQuery) {
	var attachmentTemplate = jQuery ('#attachment-template').html ();

	var getAttachment = function (file) {
		var attachment = jQuery (attachmentTemplate);
		attachment.find ('.name').html (file.name);
		attachment.find ('.attachment-filename').val (file.name);
		return attachment;
	};

	var loadCkEditor = function (inputId, additionalOptions) {
		var options = {
			contentsCss:   [ 'themes/centaurus/css/bootstrap/bootstrap.min.css', '/modules/emailmanager/templateeditor.css' ],
			entities:      false,
			extraPlugins:  'emailtemplatevariables',
			language:      'es',
			removePlugins: 'elementspath'
		};
		jQuery.extend (options, additionalOptions);
		return CKEDITOR.replace (inputId, options);
	};

	var loadTemplateAttachment = function (file, attachment) {
		var reader = new FileReader ();
		reader.onload = function (evt) {
			attachment.find ('.attachment-data').val (evt.target.result);
		};
		reader.readAsDataURL (file);
	};

	var addTemplateAttachments = function (evt) {
		var target               = evt.target ? evt.target : evt.srcElement,
			files                = target.files,
			attachmentsContainer = jQuery ('.attachments-container'),
			i, n, attachment, file;

		if ((!files) || (!(files instanceof FileList)) || (files.length === 0)) {
			return;
		}
		n = files.length;
		for (i = 0; i < n; i += 1) {
			file = files [ i ];
			attachment = getAttachment (file);
			attachmentsContainer.append (attachment);
			loadTemplateAttachment (file, attachment);
		}
	};

	var deleteTemplate = function (templateName) {
		return confirm ('¿Estás seguro que quieres eliminar la plantilla "' + templateName + '"?');
	};

	var deleteTemplateAttachment = function (button) {
		var row = jQuery (button).closest ('li');
		if (!confirm ('¿Estás seguro de eliminar el anexo seleccionado?')) {
			return;
		}
		row.remove ();
	};

	var loadDatePickers = function () {
		jQuery ('.date-field').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	};

	var loadTemplateSubjectEditor = function (inputId) {
		var editor = loadCkEditor (
			inputId,
			{
				height:         [ '38px' ],
				resize_enabled: false,
				toolbar:        [
					[ 'EmailTemplateVariables', '-', 'Source' ]
				]
			},
			true
		);
		editor.on (
			'key',
			function (e) {
				if (e.data.keyCode == 13) {
					e.cancel ();
				}
			}
		);
	};

	var loadTemplateBodyEditor = function (textareaId) {
		loadCkEditor (
			textareaId,
			{
				toolbar: [
					[ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
					[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
					[ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
					[ 'Link', 'Unlink', 'Anchor', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor' ],
					'/',
					[ 'Styles', 'Format', 'Font', 'FontSize', '-', 'EmailTemplateVariables', '-', 'Source' ]
				]
			}
		);
	};

	var setupEmailViewer = function () {
		var emailViewer = jQuery ('#email-viewer');
		emailViewer.on (
			'show.bs.modal',
			function (e) {
				jQuery (this).find ('.modal-body').load (jQuery (e.relatedTarget).attr ('href'));
			}
		);
		emailViewer.on (
			'hidden.bs.modal',
			function () {
				jQuery (this).find ('.modal-body').html ('<div class="text-center">Cargando...</div>');
			}
		);
	};

	var validateTemplate = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		jQuery ('#subject').val (CKEDITOR.instances.subject.getData ());
		jQuery ('#body').val (CKEDITOR.instances.body.getData ());

		field = form.find ('.templatename');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el nombre de la plantilla');
			field.focus ();
			return false;
		}

		field = form.find ('.language');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el idioma');
			field.focus ();
			return false;
		}

		field = form.find ('.subject');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el asunto del correo');
			field.focus ();
			return false;
		}

		field = form.find ('.body');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el cuerpo del correo');
			field.focus ();
			return false;
		}

		return true;
	};

	window.EmailManagerUtils = {
		addTemplateAttachments:    addTemplateAttachments,
		deleteTemplate:            deleteTemplate,
		deleteTemplateAttachment:  deleteTemplateAttachment,
		loadTemplateAttachment:    loadTemplateAttachment,
		loadTemplateBodyEditor:    loadTemplateBodyEditor,
		loadTemplateSubjectEditor: loadTemplateSubjectEditor,
		validateTemplate:          validateTemplate
	};

	var onDocumentReadyHandler = function () {
		loadDatePickers ();
		setupEmailViewer ();
	};

	jQuery (document).ready (onDocumentReadyHandler);
}) (jQuery);
