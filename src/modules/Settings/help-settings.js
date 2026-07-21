(function (jQuery) {
	var applications = null,
	editorDescription;

	/* Private methods */
    var loadCkEditor = function (inputId) {
        console.log ('cargando editor');
        var options = {
            contentsCss:   [ 'themes/centaurus/css/bootstrap/bootstrap.min.css' ],
            entities:      false,
            language:      'es',
            removePlugins: 'elementspath',
            height:        90,
            toolbar:       [
                [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
                [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
                [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
                [ 'Link', 'Unlink', 'Anchor', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor' ],
                '/',
                [ 'Styles', 'Format', 'Font', 'FontSize', '-', 'EmailTemplateVariables', '-', 'Source' ]
            ]
        };
        return CKEDITOR.replace (inputId, options);
    };

	var getFieldOptions = function (selectedApplicationCode, selectedModuleName) {
		var applicationCode, modules, moduleName, fields, fieldName, options;

		options = [
			jQuery ('<option></option>').val ('').text ('Selecciona')
		];
		if (
			(selectedApplicationCode === null) || (selectedApplicationCode === undefined) || (selectedApplicationCode.trim () === '') ||
			(selectedModuleName === null) || (selectedModuleName === undefined) || (selectedModuleName.trim () === '')
		) {
			return options;
		}

		for (applicationCode in applications) {
			//noinspection JSUnfilteredForInLoop
			if ((!applications.hasOwnProperty (applicationCode)) || (applicationCode !== selectedApplicationCode)) {
				continue;
			}

			//noinspection JSUnfilteredForInLoop
			modules = applications [ applicationCode ].modules;
			for (moduleName in modules) {
				//noinspection JSUnfilteredForInLoop
				if ((!modules.hasOwnProperty (moduleName)) || (moduleName !== selectedModuleName)) {
					continue;
				}

				//noinspection JSUnfilteredForInLoop
				fields = modules [ moduleName ].fields;
				for (fieldName in fields) {
					if (!fields.hasOwnProperty (fieldName)) {
						continue;
					}

					options.push (jQuery ('<option></option>').val (fieldName).text (fields [ fieldName ].fieldlabel));
				}
			}
			break;
		}
		return options;
	};

	var getModuleSelectOptions = function (selectedApplicationCode) {
		var applicationCode, modules, moduleName, options;

		options = [
			jQuery ('<option></option>').val ('').text ('Selecciona')
		];
		if ((selectedApplicationCode === null) || (selectedApplicationCode === undefined) || (selectedApplicationCode.trim () === '')) {
			return options;
		}

		for (applicationCode in applications) {
			//noinspection JSUnfilteredForInLoop
			if ((!applications.hasOwnProperty (applicationCode)) || (applicationCode !== selectedApplicationCode)) {
				continue;
			}

			//noinspection JSUnfilteredForInLoop
			modules = applications [ applicationCode ].modules;
			for (moduleName in modules) {
				if (!modules.hasOwnProperty (moduleName)) {
					continue;
				}

				options.push (jQuery ('<option></option>').val (moduleName).text (modules [ moduleName ].tablabel + ' (' + moduleName + ')'));
			}
			break;
		}
		return options;
	};

	var getYouTubeIdFromUrl = function (url) {
		var pattern = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/,
			matches = url.match (pattern);
		return matches ? matches [ 1 ] : null;
	};

    var validateForm = function (objForm) {
        var formElement    = jQuery("form[name='" + objForm.attr ('name') +"'] :input"),
            isValidate     = true,
            selectedFields = [],
            field, operationValue, value;
        jQuery('span[id ^= help-field-]').html('');
        jQuery('span[id ^= help-sys-field-]').html('');
        jQuery('div[id ^= help-field-div-]').removeClass('has-error');
        formElement.map(function (index, elm) {
            var element = jQuery(elm),
                elementTitle = element.attr('title'),
                elementName  = element.attr ('name'),
                value = element.val();
            if ((jQuery.inArray(elm.type, ['hidden', 'button', 'submit', 'select-multiple', 'checkbox', 'undefined']) === -1) && elementTitle !== '' && elementTitle !== undefined) {
                if ((value === null) || (value === undefined) || (value.trim() === '')) {
                    element.parent().addClass('has-error');
                    if (element.parent().find('.help-block').length) {
                        element.parent().find('.help-block').html(elementTitle + ' requerido');
                    } else {
                        element.parent().parent().find('.help-block').html(elementTitle + ' requerido');
                    }
                    isValidate = false;
                }
            }
        });
        value = editorDescription.getData();
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
        	jQuery('#help-sys-field-description').html('La descripción de la ayuda es requerida');
            isValidate = false;
        } else if (value.trim ().length < 45) {
            jQuery('#help-sys-field-description').html('La descripción de la ayuda, parece estar vacía o es muy corta! introduce al menos 50 carácteres!');
            isValidate = false;
        }
        value = jQuery('#url').val ();
        if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
            value = jQuery('#video-type').val ();
            if ((value === null) || (value === undefined) || (value.trim () === '')) {
                jQuery('#help-field-div-video-type').addClass('has-error');
                jQuery('#help-sys-field-video-type').html('Tipo de video?');
                isValidate = false;
            }
        }
        return isValidate;
    };

	/* Public methods */
	var init = function (platformApplications) {
		applications = platformApplications;
	};

	var setFieldsByModule = function (moduleSelect) {
		var moduleName = jQuery(moduleSelect).val ();
        jQuery ("#help-field-fieldname").val('');

        jQuery ("#help-field-fieldname > option").each(function() {
            var option     = jQuery(this),
                dataModule = option.attr('data-module');
            if (moduleName != dataModule) {
                option.hide();
            } else {
                option.show();
            }
        });
	};

	var setFieldOptions = function (moduleSelect) {
		var module                  = jQuery (moduleSelect),
			field                   = module.closest ('.main-box-body').find ('.field-name'),
			selectedApplicationCode = jQuery ('#application').val (),
			selectedModuleName      = module.val ();

		field.empty ().append (getFieldOptions (selectedApplicationCode, selectedModuleName));
	};

	var setModuleOptions = function (applicationSelect) {
		var selectedApplicationCode = jQuery (applicationSelect).val ();

		jQuery ('.module-name').empty ().append (getModuleSelectOptions (selectedApplicationCode));
		jQuery ('.field-name').empty ().append (getFieldOptions (selectedApplicationCode, null));
	};

	var setSectionAndTabNames = function (selectElement) {
		var select = jQuery (selectElement),
			selectedOption = select.find ('option:selected');
		select.closest ('.field-container').find ('input[name="sectionname"]').val (selectedOption.data ('section-name'));
		select.closest ('.field-container').find ('input[name="tabname"]').val (selectedOption.data ('tab-name'));
	};

	var setTutorialPreview = function (fieldElement) {
		var field          = jQuery (fieldElement),
			type           = field.closest ('.main-box-body').find ('#type').val (),
			url            = field.val (),
			previewSection = field.closest ('.main-box-body').find ('#preview'),
			youTubeVideoId;

		if ((url === undefined) || (url === null) || (url.trim () === '')) {
			previewSection.addClass ('hidden');
			return;
		}

		if (type === 'VIDEO') {
			youTubeVideoId = getYouTubeIdFromUrl (url);
			if (!youTubeVideoId) {
				previewSection.addClass ('hidden');
				alert ('El enlace debe ser de YouTube');
				field.focus ();
				return;
			}
			url = '//www.youtube.com/embed/' + youTubeVideoId + '?rel=0';
		}

		previewSection.find ('iframe').attr ('src', url);
		previewSection.removeClass ('hidden');
	};

	var validateConfiguration = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#block-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el bloque');
			field.focus ();
			return false;
		}

		field = form.find ('#type');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el tipo');
			field.focus ();
			return false;
		}

		field = form.find ('#title');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el título');
			field.focus ();
			return false;
		}

		field = form.find ('#url');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el URL');
			field.focus ();
			return false;
		}

		return true;
	};

	var saveHelpField = function (obj, id) {
		var btnSend = jQuery (obj),
			form    = jQuery ('#help-form-' + id);

		if (validateForm (form)) {
			form.submit();
		}

    };

	var validateQuestion = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#application');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona la aplicación');
			field.focus ();
			return false;
		}

		field = form.find ('#title');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el título');
			field.focus ();
			return false;
		}

		field = form.find ('#description');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce la descripción');
			field.focus ();
			return false;
		}

		field = form.find ('#tags');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce las etiquetas de búsqueda');
			field.focus ();
			return false;
		}

		return true;
	};

	var validateTip = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#title');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el título');
			field.focus ();
			return false;
		}

		field = form.find ('#description');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce la descripción');
			field.focus ();
			return false;
		}

		field = form.find ('#tags');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce las etiquetas de búsqueda');
			field.focus ();
			return false;
		}

		return true;
	};

	var validateTutorial = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#type');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el tipo');
			field.focus ();
			return false;
		}

		field = form.find ('#category');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona la categoría');
			field.focus ();
			return false;
		}

		field = form.find ('#title');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el título');
			field.focus ();
			return false;
		}

		field = form.find ('#url');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el URL');
			field.focus ();
			return false;
		}

		field = form.find ('#application-codes');
		value = field.val ();
		if ((value === null) || (value === undefined) || (!jQuery.isArray (value)) || (value.length === 0)) {
			alert ('Selecciona la(s) aplicación(es)');
			field.focus ();
			return false;
		}

		field = form.find ('#tags');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce las etiquetas de búsqueda');
			field.focus ();
			return false;
		}

		return true;
	};

	var validateUseCase = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#category');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona la categoría');
			field.focus ();
			return false;
		}

		field = form.find ('#title');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el título');
			field.focus ();
			return false;
		}

		field = form.find ('#url');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el URL');
			field.focus ();
			return false;
		}

		field = form.find ('#application-codes');
		value = field.val ();
		if ((value === null) || (value === undefined) || (!jQuery.isArray (value)) || (value.length === 0)) {
			alert ('Selecciona la(s) aplicación(es)');
			field.focus ();
			return false;
		}

		field = form.find ('#tags');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce las etiquetas de búsqueda');
			field.focus ();
			return false;
		}

		return true;
	};

	window.HelpSettingsUtils = {
		init:                  init,
        saveHelpField:         saveHelpField,
        setFieldsByModule:     setFieldsByModule,
		setFieldOptions:       setFieldOptions,
		setModuleOptions:      setModuleOptions,
		setSectionAndTabNames: setSectionAndTabNames,
		setTutorialPreview:    setTutorialPreview,
		validateConfiguration: validateConfiguration,
		validateQuestion:      validateQuestion,
		validateTip:           validateTip,
		validateTutorial:      validateTutorial,
		validateUseCase:       validateUseCase
	};
    jQuery (document).on ('ready', function () {
		var moduleName = jQuery('#help-field-module').val();
        editorDescription = loadCkEditor ('help-field-description');
        jQuery ("#help-field-fieldname > option").each(function() {
        	var option     = jQuery(this),
				dataModule = option.attr('data-module');
            if (moduleName != dataModule) {
                option.hide();
			} else {
            	option.show();
			}
        });
    });
} (jQuery));