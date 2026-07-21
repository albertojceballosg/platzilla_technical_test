(function (jQuery) {
	// Private methods

	var loadCkEditor = function (inputId, additionalOptions) {
		var options = {
			contentsCss:   [ 'themes/centaurus/css/bootstrap/bootstrap.min.css' ],
			entities:      false,
			language:      'es',
			removePlugins: 'elementspath'
		};
		jQuery.extend (options, additionalOptions);
		if (CKEDITOR.instances[ inputId ]) {
			CKEDITOR.instances[ inputId ].setData (jQuery ('#' + inputId).val ());
		} else {
			CKEDITOR.replace (inputId, options);
		}
	};

	var loadContentEditor = function () {
		loadCkEditor (
			'news-content',
			{
				toolbar: [
					[ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
					[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
					[ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
					[ 'Link', 'Unlink', 'Anchor', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor' ],
					'/',
					[ 'Styles', 'Format', 'Font', 'FontSize' ]
				]
			}
		);
	};

	// Public methods

	var addAllSharingItems = function (buttonElement) {
		var button           = jQuery (buttonElement),
			container        = button.closest ('.sharing-items'),
			availableOptions = container.find ('.available-items > option'),
			selectedOptions  = container.find ('.selected-items > option'),
			option, i;

		for (i = 0; i < availableOptions.length; i += 1) {
			option = jQuery (availableOptions [ i ]);
			if (jQuery.inArray (option.val (), [ '-ALL-', '-ALL-CUSTOMERS-', '-ALL-PROVIDERS-' ]) !== -1) {
				option.removeAttr ('selected').hide ();
			} else {
				option.removeAttr ('selected').show ();
			}
		}
		for (i = 0; i < availableOptions.length; i += 1) {
			option = jQuery (selectedOptions [ i ]);
			if (jQuery.inArray (option.val (), [ '-ALL-', '-ALL-CUSTOMERS-', '-ALL-PROVIDERS-' ]) !== -1) {
				option.removeAttr ('selected').prop ('disabled', false).show ();
			} else {
				option.removeAttr ('selected').prop ('disabled', true).hide ();
			}
		}
	};

	var addSelectedSharingItems = function (buttonElement) {
		var button           = jQuery (buttonElement),
			container        = button.closest ('.sharing-items'),
			availableOptions = container.find ('.available-items > option:selected'),
			selectedOptions  = container.find ('.selected-items > option'),
			option, values, i;

		if (availableOptions.length === 0) {
			return;
		}

		values = [];
		for (i = 0; i < availableOptions.length; i += 1) {
			option = jQuery (availableOptions [ i ]);
			values.push (option.val ());
		}

		if ((jQuery.inArray ('-ALL-', values) !== -1) || (jQuery.inArray ('-ALL-CUSTOMERS-', values) !== -1) || (jQuery.inArray ('-ALL-PROVIDERS-', values) !== -1)) {
			addAllSharingItems (buttonElement);
		} else {
			for (i = 0; i < availableOptions.length; i += 1) {
				option = jQuery (availableOptions [ i ]);
				option.removeAttr ('selected').hide ();
			}
			for (i = 0; i < selectedOptions.length; i += 1) {
				option = jQuery (selectedOptions [ i ]);
				if (jQuery.inArray (option.val (), values) !== -1) {
					option.removeAttr ('selected').prop ('disabled', false).show ();
				}
			}
		}
	};

	var removeAllSharingItems = function (buttonElement) {
		var button           = jQuery (buttonElement),
			container        = button.closest ('.sharing-items'),
			availableOptions = container.find ('.available-items > option'),
			selectedOptions  = container.find ('.selected-items > option'),
			option, values, i;

		if (selectedOptions.length === 0) {
			return;
		}

		values = [];
		for (i = 0; i < selectedOptions.length; i += 1) {
			option = jQuery (selectedOptions [ i ]);
			values.push (option.val ());
		}
		for (i = 0; i < availableOptions.length; i += 1) {
			option = jQuery (availableOptions [ i ]);
			if (jQuery.inArray (option.val (), values) !== -1) {
				option.removeAttr ('selected').show ();
			}
		}
		for (i = 0; i < selectedOptions.length; i += 1) {
			option = jQuery (selectedOptions [ i ]);
			option.removeAttr ('selected').hide ();
		}
	};

	var removeSelectedSharingItems = function (buttonElement) {
		var button           = jQuery (buttonElement),
			container        = button.closest ('.sharing-items'),
			availableOptions = container.find ('.available-items > option'),
			selectedOptions  = container.find ('.selected-items > option:selected'),
			option, values, i;

		if (selectedOptions.length === 0) {
			return;
		}

		values = [];
		for (i = 0; i < selectedOptions.length; i += 1) {
			option = jQuery (selectedOptions [ i ]);
			values.push (option.val ());
		}
		for (i = 0; i < availableOptions.length; i += 1) {
			option = jQuery (availableOptions [ i ]);
			if (jQuery.inArray (option.val (), values) !== -1) {
				option.removeAttr ('selected').show ();
			}
		}
		for (i = 0; i < selectedOptions.length; i += 1) {
			option = jQuery (selectedOptions [ i ]);
			option.removeAttr ('selected').hide ();
		}
	};

	var selectedCatogory = function (obj) {
		var category  = jQuery(obj).val (),
			queues    = jQuery ('#QUEUE-CATEGORY'),
			platzilla = jQuery('#PLATZILLA');
		if (category === 'PLATZILLA') {
			if (!queues.hasClass('hide')) {
                queues.addClass('hide');
			}
			platzilla.removeClass('hide')
		} else {
            if (!platzilla.hasClass('hide')) {
                platzilla.addClass('hide');
            }
            queues.removeClass('hide')
		}
    };

	var validateForm = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#news-title');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el título');
			field.focus ();
			return false;
		}

		CKEDITOR.instances [ 'news-content' ].updateElement ();
		field = form.find ('#news-content');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el contenido');
			field.focus ();
			return false;
		}

		value = form.find ('.sharing-items .selected-items > option:visible');
		if (value.length === 0) {
			alert ('Comparte el anuncio con alguien');
			field.focus ();
			return false;
		} else {
			value.attr ('selected', 'selected');
		}


		return true;
	};

	window.NewsUtils = {
		addAllSharingItems:         addAllSharingItems,
		addSelectedSharingItems:    addSelectedSharingItems,
		removeAllSharingItems:      removeAllSharingItems,
		removeSelectedSharingItems: removeSelectedSharingItems,
		selectedCatogory:           selectedCatogory,
		validateForm:               validateForm
	};

	jQuery (document).ready (function () {
		loadContentEditor ();
		jQuery ('.date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		jQuery ('.time').timepicker ({ minuteStep: 5, showMeridian: false, disableFocus: false, showWidget: true });
	});
} (jQuery));
