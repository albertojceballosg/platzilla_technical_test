(function (jQuery) {
	// constants
	var EVENT_ALWAYS = 'ALWAYS';
	var fLabels = [];
	var hfLabels = [];
	var typeofdata = [];
	var cloneGroup = '';
	var moduleData = '';
	var lastModule = jQuery ('#module-name').val ();

	// private methods
	var loadCkEditor = function (inputId, additionalOptions) {
		var options = {
			contentsCss:   [
				'themes/centaurus/css/bootstrap/bootstrap.min.css',
				'themes/centaurus/css/libs/font-awesome.css',
				'themes/centaurus/css/compiled/theme_styles.css',
				'themes/centaurus/css/compiled/theme_custom.css',
				'//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400'
			],
			entities:      false,
			language:      'es',
			removePlugins: 'elementspath',
			height:        160
		};
		jQuery.extend (options, additionalOptions);
		return CKEDITOR.replace (inputId, options);
	};

	var upDateContent = function (style, reason) {
		if ((typeof style === 'undefined') ||
			(typeof reason === 'undefined') ||
			(style === '') || (reason === '')) {
			return
		}
		var simpleTemplate = '',
			reasonTemplate = 'alert-' + reason.toLowerCase (),
			reasonMessage  = '!Cuidado!',
			type           = jQuery ('#notification-type').val ();
		checkInstance.setData (simpleTemplate);

		if (reason === 'SUCCESS') {
			reasonMessage = 'Muy bien';
		} else if (reason === 'INFO') {
			reasonMessage = 'Atento';
		} else if (reason === 'WARNING') {
			reasonMessage = 'Cuidado';
		} else {
			reasonMessage = 'Error';
		}
		if (type === 'ALERT') {
			simpleTemplate = jQuery ('#simple-alert-template').html ().replace (/__ACTION__/g, reasonTemplate);
		} else {
			if (style === 'SIMPLE') {
				simpleTemplate = jQuery ('#simple-template').html ().replace (/__ACTION__/g, reasonTemplate).replace (/__MESSAGE__/g, reasonMessage);
            } else if (style === 'EXPANDABLE') {
                simpleTemplate = jQuery ('#simple-template-collapse').html ().replace (/__ACTION__/g, reasonTemplate).replace (/__MESSAGE__/g, reasonMessage);
			}
		}
		checkInstance.setData (simpleTemplate);
	};

	var onGetModuleColumnsSuccessHandler = function (responseText) {
		var fieldSelect = jQuery ('#filter-column'),
			tableAlias  = '',
			fields      = JSON.parse (responseText);
		if ((fields === null) || (fields === undefined)) {
			return;
		}
		moduleData = responseText;
		fieldSelect.empty ();
		fieldSelect.append (
			jQuery (
				'<option>',
				{
					value: '',
					text:  ''
				}
			)
		);
		jQuery.each (
			fields,
			function (i, field) {
				if ((field === null) || (field === undefined) || (!(field instanceof Object)) || (jQuery.isEmptyObject (field))) {
					return;
				}
				if (jQuery.inArray (field.typeofdata, [ 'T', 'D', 'DT' ]) !== -1) {
					if (field.uitype === '70') {
						tableAlias = 'crm.'
					} else {
						tableAlias = 'tq.'
					}
					fieldSelect.append (
						jQuery (
							'<option>',
							{
								value: tableAlias + field.fieldname,
								text:  field.label
							}
						)
					);
				}
			}
		);
	};

	var onAjaxFailureHandler = function (jQueryResponse) {
		alert ('Se ha presentado un error. Intenta más tarde');
	};

	var setFieldsOptions = function (obj) {
		var fieldSelect,
			fields = JSON.parse (moduleData);
		if ((fields === null) || (fields === undefined)) {
			return;
		}
		fieldSelect = obj.find ('select').eq (0);

		jQuery.each (
			fields,
			function (i, field) {
				if ((field === null) || (field === undefined) || (!(field instanceof Object)) || (jQuery.isEmptyObject (field))) {
					return;
				}
				if (field.typeofdata != '') {
					fieldSelect.append (
						jQuery (
							'<option>',
							{
								value: field.fieldname,
								text:  field.label
							}
						).attr ('data-type', field.typeofdata).attr ('data-uitype', field.uitype)
					);
				}
			}
		);
	};

	// public methods
	var action = function (obj) {
		var reason = jQuery (obj).val (),
			style  = jQuery ('#notification-html').val ();
		upDateContent (style, reason);
	};

	var init = function (textareaId) {
		return loadCkEditor (
			textareaId,
			{
				toolbar: [
					[ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
					[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
					[ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
					[ 'Link', 'Unlink', 'Anchor', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor' ],
					'/',
					[ 'Styles', 'Format', 'Font', 'FontSize', '-', '-', 'Source' ]
				]
			}
		);
	};

	var addFilterGroup = function (obj) {
		var module = jQuery ('#module-name'),
			group  = '';
		if (module.val () == '') {
			module.parent ().addClass ('has-error');
			module.parent ().find ('.help-block').html ('Selecciona el modulo');
			return false;
		}

		var conditionGroups        = jQuery ('.action-bar'),
			conditionGroupTemplate = jQuery (jQuery ('#condition-group-template').html ().replace (/__GROUP_ID__/g, totalFilterGroup)),
			conditionTemplate      = jQuery ('#condition-template').html ().replace (/__GROUP_ID__/g, totalFilterGroup); //.replace(/__CONDITION_ID__/g, -1)
		conditionGroupTemplate.find ('.conditions').append (conditionTemplate);
		conditionGroups.before (conditionGroupTemplate);
		group = jQuery ('#group-' + totalFilterGroup);
		totalFilterGroup += 1;
		totalFilterRow += 1;
		hasGroup = true;
		jQuery (obj).attr ('data-group', totalFilterGroup);
		setFieldsOptions (group);
		if (totalFilterGroup > 1) {
			jQuery ('#group-' + (totalFilterGroup - 2)).find ('.operator').removeClass ('hidden').removeAttr ('disabled');
		}
	};

	var createNotification = function (obj) {
		var form = jQuery (obj);
		if (validateNotification (form) && validateFilters (form)) {
			return true;
		} else {
			return false;
		}
	};

	var eraseFilterGroup = function (obj) {
		var elementGroup, thisGroup, idGroup, lastGroup,
			infoTexto = '¿Esás seguro de borrar el grupo de condiciones seleccionado?';
		thisGroup = jQuery (obj).parent ().parent ().parent ().parent ();
		idGroup = thisGroup.attr ('id');
		var r = confirm (infoTexto);
		if (r == true) {
			lastGroup = jQuery ('div.filter_goup').last ().attr ('id');
			if (idGroup == lastGroup) {
				thisGroup.prev ().find ('.operator').addClass ('hidden').attr ('disabled', 'disabled');
				totalFilterGroup -= 1;
			}
			thisGroup.remove ();
		}
	};

	var eraseFilterRow = function (obj) {
		var prevElementRow, thisRow, thisId, lastRowId,
			infoTexto = '¿Esás seguro de borrar la condción seleccionada?';
		var r = confirm (infoTexto);
		if (r == true) {
			thisRow = jQuery (obj).parent ().parent ().parent ();
			lastRowId = thisRow.parent ().find ('li:last-child').attr ('id');
			thisId = thisRow.attr ('id');
			prevElementRow = thisRow.prev ();
			if (thisId == lastRowId) {
				prevElementRow.find ('select').eq (2).addClass ('hidden').attr ('disabled', 'disabled');
			}
			thisRow.remove ()
		}
	};

	var eraseFilterValue = function (obj) {
		var elementRow = '';
		elementRow = jQuery (obj).parent ();
		elementRow.find ('input').eq (0).val ('');
	};

	var getModuleColumns = function (obj) {
		var module     = jQuery (obj),
			moduleName = module.val (),
			infoTexto  = 'Esta operación borrará todos los filtros, ¿Desea continuar?',
			arguments;
		if (module.val () != '') {
			module.parent ().removeClass ('has-error');
			module.parent ().find ('.help-block').html ('');
		}

		if ((totalFilterGroup >= 1) && (hasGroup)) {
			var r = confirm (infoTexto);
			if (r == true) {
				jQuery ('div[id ^= group-]').each (function (index, item) {
					jQuery (item).remove ();
					totalFilterGroup = 0;
					hasGroup = false;
				});
			} else {
				module.val (lastModule);
				return;
			}
		}
		hasGroup = false;

		if ((moduleName === null) || (moduleName === undefined) || (moduleName.trim () === '')) {
			return;
		}
		lastModule = moduleName;
		arguments = [
			'module=notifications',
			'action=AjaxActions',
			'function=getColumns',
			'Ajax=true',
			'fld_module=' + encodeURIComponent (moduleName)
		];
		jQuery.ajax (
			'index.php',
			{
				data:     arguments.join ('&'),
				dataType: 'text',
				method:   'post'
			}
		).done (onGetModuleColumnsSuccessHandler).fail (onAjaxFailureHandler);
	};

	var setAmbit = function (obj) {
		var ambit = jQuery (obj).val (),
			user  = jQuery ('#notification-users'),
			users = jQuery ('#notification-users > option');
		if (ambit === 'SYSTEM') {
			user.val ('0');
			jQuery ('#notification-users option:not(:selected)').attr ('disabled', true);
		} else {
			jQuery ('#notification-users option:not(:selected)').attr ('disabled', false);
			users.attr ("selected", false);
		}
	};

	var setEvent = function (selectElement) {
		var select = jQuery (selectElement),
			event  = select.val ();

		if (jQuery.inArray (event, [ '', EVENT_ALWAYS ]) === -1) {
			select.closest ('form').find ('#event-parameter-container').show ();
		} else {
			select.closest ('form').find ('#event-parameter-container').hide ();
		}
	};

	var setFilterOperators = function (obj) {
		var filterRow    = '',
			selectedType = '',
			thisOperator = '',
			thisInput    = '';
		selectedType = jQuery (obj).children ('option:selected').attr ('data-type');
		filterRow = jQuery (obj).parent ().parent ();
		thisOperator = filterRow.find ('select').eq (1);
		thisInput = filterRow.find ('input').eq (0);
		thisInput.val ('');
		if (selectedType != null && selectedType.length != 0) {
			if (jQuery.inArray (selectedType, [ 'T', 'D', 'DT' ]) !== -1) {
				filterRow.find ('.is-date').removeClass ('hide');
				thisInput.attr ('readonly', true);
				thisInput.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
			} else {
				filterRow.find ('.is-date').addClass ('hide');
				thisInput.attr ('readonly', false);
				thisInput.datepicker ('remove');
			}
			ops = typeofdata[ selectedType ];
			if (ops != null) {
				thisOperator.empty ();
				jQuery (thisOperator).append (
					jQuery (
						'<option>',
						{
							value: '',
							text:  '-Ninguno-'
						}
					)
				);
				for (var i = 0; i < ops.length; i++) {
					var label = fLabels[ ops[ i ] ];
					if (label == null) {
						continue;
					}
					jQuery (thisOperator).append (
						jQuery (
							'<option>',
							{
								value: ops[ i ],
								text:  label
							}
						)
					);

				}
			}
		} else {
			if (selectedType == '') {
				thisOperator.options[ 0 ].selected = true;
			}
		}
	};

	var setFilterRow = function (obj) {
		var elementRow, newElementRow, numRow, fieldSelect, totalRow;
		elementRow = jQuery (obj).parent ().parent ().parent ().find ('li:last-child');
		newElementRow = elementRow.clone ().attr ('id', 'row-' + totalFilterRow);
		elementRow.find ('select').eq (2).removeClass ('hidden').removeAttr ('disabled');
		newElementRow.find ('button').eq (0).removeClass ('hidden');
		newElementRow.find ('select').eq (0).val ('');
		newElementRow.find ('select').eq (1).val ('');
		newElementRow.find ('input').eq (0).val ('');
		newElementRow.appendTo (elementRow.parent ());
		totalFilterRow += 1;
	};

	var setHelpToField = function (obj) {
		var elementRow       = '',
			selectedOperator = '';
		selectedOperator = jQuery (obj).val ();
		elementRow = jQuery (obj).parent ().parent ();
		elementRow.find ('input').eq (0).val ('');
		elementRow.find ('input').eq (0).attr ('placeholder', hfLabels[ selectedOperator ]);
	};

	var setPeriod = function (obj) {
		var period    = jQuery (obj).val (),
			startDate = jQuery ('#filter-start-date'),
			endDate   = jQuery ('#filter-end-date');

		if (period === 'custom') {
			startDate.attr ('disabled', false);
			endDate.attr ('disabled', false);
			jQuery ('.custom-filter-date').show ();
		} else {
			jQuery ('.custom-filter-date').hide ();
			startDate.val ('');
			endDate.val ('');
			startDate.attr ('disabled', true);
			endDate.attr ('disabled', true);
		}
	};

	var selectedStyle = function (obj) {
		var style  = jQuery (obj).val (),
			reason = jQuery ('#notification-action').val ();
		upDateContent (style, reason);
	};

	var selectedType = function (obj) {
		var type    = jQuery (obj).val (),
			html    = jQuery ('#notification-html'),
			modules = jQuery ('#module-names > option'),
			view    = jQuery ('#notification-veiw'),
			reason  = jQuery ('#notification-action').val ();

		if (type === 'NOTIFY') {
			html.val ('');
			jQuery ('#notification-html option:not(:selected)').attr ('disabled', false);
			jQuery ('#notification-system').removeClass ('hide');
		} else if (type === 'ALERT') {
			html.val ('SIMPLE');
			jQuery ('#notification-html option:not(:selected)').attr ('disabled', true);
			view.val ('');
			modules.attr ("selected", false);
			jQuery ('#notification-system').addClass ('hide');
		} else {
			html.val ('');
			jQuery ('#notification-html option:not(:selected)').attr ('disabled', false);
			jQuery ('#notification-system').addClass ('hide');
		}
		upDateContent (html.val (), reason);
	};

	var validateFilters = function (formElement) {
		var field, value, isValidate = true, error = true,
			form                                   = jQuery (formElement);

		jQuery ('span[id ^= sp-n-]').html ('');
		jQuery ('div[id ^= dv-n-]').removeClass ('has-error');
		jQuery ('li[id ^= row-]').each (function (index, item) {

			jQuery (item).find ('span').eq (0).html ('');
			jQuery (item).find ('span').eq (1).html ('');
			jQuery (item).find ('span').eq (2).html ('');

			if (jQuery (item).find ('select').eq (0).val () == '') {
				jQuery (item).find ('span').eq (0).html ('La Variable es requerida');
				isValidate = false;
				jQuery (item).find ('select').eq (0).parent ().addClass ('has-error')
			} else {
				jQuery (item).find ('select').eq (0).parent ().removeClass ('has-error')
			}

			if (jQuery (item).find ('select').eq (1).val () == '') {
				jQuery (item).find ('span').eq (1).html ('El Operador es requerido');
				isValidate = false;
				jQuery (item).find ('select').eq (1).parent ().addClass ('has-error')
			} else {
				jQuery (item).find ('select').eq (1).parent ().removeClass ('has-error')
			}

			if (jQuery (item).find ('input').eq (0).val () == '') {
				idfield = jQuery (item).find ('input').eq (0).attr ('id')
				jQuery (item).find ('span').eq (2).html ('El Valor es requerido');
				isValidate = false;
				jQuery (item).find ('input').eq (0).parent ().parent ().addClass ('has-error')
			} else {
				jQuery (item).find ('input').eq (0).parent ().parent ().removeClass ('has-error')
			}
		});

		field = form.find ('#filter-period');
		value = field.val ();
		if (value === 'custom') {
			field = form.find ('#filter-start-date');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.length === 0)) {
				jQuery ('#sp-n-filter-start-date').html ('Selecciona el módulo objetivo');
				jQuery ('#dv-n-filter-start-date').addClass ('has-error');
				error = false;
			}
			field = form.find ('#filter-end-date');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.length === 0)) {
				jQuery ('#sp-n-filter-end-date').html ('Selecciona el módulo objetivo');
				jQuery ('#dv-n-filter-end-date').addClass ('has-error');
				error = false;
			}
			field = form.find ('#filter-column');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.length === 0)) {
				jQuery ('#sp-n-filter-column').html ('Selecciona una columna');
				jQuery ('#dv-n-filter-column').addClass ('has-error');
				error = false;
			}
		} else if ((value !== null) && (value !== undefined) && (value.length !== 0)) {
			field = form.find ('#filter-column');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.length === 0)) {
				jQuery ('#sp-n-filter-column').html ('Selecciona una columna');
				jQuery ('#dv-n-filter-column').addClass ('has-error');
				error = false;
			}
		} else {
			field = form.find ('#filter-column');
			value = field.val ();
			if ((value !== null) && (value !== undefined) && (value.length !== 0)) {
				jQuery ('#sp-n-filter-period').html ('Selecciona la duración');
				jQuery ('#dv-n-filter-period').addClass ('has-error');
				error = false;
			}
		}

		if (!error) {
			jQuery ('.nav-tabs a[href="#period"]').tab ('show');
			return error;
		}

		if (!isValidate) {
			jQuery ('.nav-tabs a[href="#advanced"]').tab ('show');
			return isValidate;
		}

		return true;
	};

	var validateNotification = function (formElement, step) {
		var form = jQuery (formElement), error = true,
			field, value, infoText             = '';
		step = typeof step !== 'undefined' ? step : 0;

		jQuery ('span[id ^= sp-n-]').html ('');
		jQuery ('div[id ^= dv-n-]').removeClass ('has-error');

		field = form.find ('#module-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.length === 0)) {
			jQuery ('#sp-n-module-name').html ('Selecciona el módulo objetivo');
			jQuery ('#dv-n-module-name').addClass ('has-error');
			error = false;
		}

		field = form.find ('#event');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			jQuery ('#sp-n-event').html ('Selecciona el evento');
			jQuery ('#dv-n-event').addClass ('has-error');
			error = false;
		} else if (jQuery.inArray (value, [ '', EVENT_ALWAYS ]) === -1) {
			field = form.find ('#event-parameter');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {

				jQuery ('#sp-n-event-parameter').html ('Introduce el parámetro');
				jQuery ('#dv-n-event-parameter').addClass ('has-error');
				error = false;
			}
		}

		field = form.find ('#notification-from');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.length === 0)) {
			jQuery ('#sp-n-notification-from').html ('Selecciones para quien es la notificación');
			jQuery ('#dv-n-notification-from').addClass ('has-error');
			error = false;
		}

		if (step == 1) {
			return error
		}

		field = form.find ('#notification-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			jQuery ('#sp-n-notification-name').html ('Introduce el nombre');
			jQuery ('#dv-n-notification-name').addClass ('has-error');
			error = false;
		}

		field = form.find ('#notification-status');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			jQuery ('#sp-n-notification-status').html ('Selecciona el estatus');
			jQuery ('#dv-n-notification-status').addClass ('has-error');
			error = false;
		}

		field = form.find ('#notification-users');
		value = field.val ();
		if ((value === null) || (value === undefined) || ((value.length === 0))) {
			jQuery ('#sp-n-notification-users').html ('Selecciona uno o todos');
			jQuery ('#dv-n-notification-users').addClass ('has-error');
			error = false;
		}

		field = form.find ('#notification-type');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			jQuery ('#sp-n-notification-type').html ('Seleccione el tipo de comunicación');
			jQuery ('#dv-n-notification-type').addClass ('has-error');
			error = false;
		} else if (value === 'ALERT') {
			field = form.find ('#notification-action');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				jQuery ('#sp-n-notification-action').html ('Selecciona el motivo de la comunicación');
				jQuery ('#dv-n-notification-action').addClass ('has-error');
				error = false;
			}

			field = form.find ('#contents');
			value = checkInstance.getData ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				jQuery ('#sp-n-contents').html ('Introduce el contenido de la notificación');
				jQuery ('#dv-n-contents').addClass ('has-error');
				error = false;
			}
		} else {
			field = form.find ('#notification-veiw');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				jQuery ('#sp-n-notification-veiw').html ('Ubicacion de la comunicación');
				jQuery ('#dv-n-notification-veiw').addClass ('has-error');
				error = false;
			}

			field = form.find ('#module-names');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.length === 0)) {
				jQuery ('#sp-n-module-names').html ('Selecciona el o los módulo');
				jQuery ('#dv-n-module-names').addClass ('has-error');
				error = false;
			}

			field = form.find ('#notification-action');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				jQuery ('#sp-n-notification-action').html ('Selecciona el motivo de la comunicación');
				jQuery ('#dv-n-notification-action').addClass ('has-error');
				error = false;
			}

			field = form.find ('#notification-html');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				jQuery ('#sp-n-notification-html').html ('Tipo de Notificación');
				jQuery ('#dv-n-notification-html').addClass ('has-error');
				error = false;
			}
			field = form.find ('#contents');
			//field.val ()
			value = checkInstance.getData ();

			if ((value === null) || (value === undefined) || (value.trim () === '') || (value.length < 10)) {
				jQuery ('#sp-n-contents').html ('Introduce el contenido de la notificación');
				jQuery ('#dv-n-contents').addClass ('has-error');
				error = false;
			}
		}

		return error;
	};

	window.NotificationUtils = {
		action:               action,
		addFilterGroup:       addFilterGroup,
		createNotification:   createNotification,
		eraseFilterGroup:     eraseFilterGroup,
		eraseFilterRow:       eraseFilterRow,
		eraseFilterValue:     eraseFilterValue,
		init:                 init,
		getModuleColumns:     getModuleColumns,
		setAmbit:             setAmbit,
		setEvent:             setEvent,
		setFilterOperators:   setFilterOperators,
		setFilterRow:         setFilterRow,
		setHelpToField:       setHelpToField,
		setPeriod:            setPeriod,
		selectedStyle:        selectedStyle,
		selectedType:         selectedType,
		validateFilters:      validateFilters,
		validateNotification: validateNotification
	};

	fLabels[ 'l' ] = alert_arr.LESS_THAN;
	fLabels[ 'g' ] = alert_arr.GREATER_THAN;
	fLabels[ 'm' ] = alert_arr.LESS_OR_EQUALS;

	jQuery.fn.formToWizard = function (options) {
		options = jQuery.extend ({
			submitButton: ''
		}, options);

		var element          = this,
			steps            = jQuery (element).find ('fieldset'),
			count            = steps.size (),
			submitButtonName = '#' + options.submitButton;
		jQuery (submitButtonName).hide ();
		jQuery (submitButtonName).click (function () {
			element.submit ();
		});

		steps.each (function (i) {
			var name;
			jQuery (this).wrap ("<div id='step" + i + "'></div>");
			jQuery (this).append ("<p style='margin-left: 6px' id='step" + i + "commands'></p>");

			// 2
			name = jQuery (this).find ("legend").html ();
			jQuery ("#steps").append ("<li id='stepDesc" + i + "'><span id='setpSpan" + i + "' class='badge'>" + (i + 1) + "</span>" + name + "<span class='chevron'></li>");
			if (i == 0) {
				createNextButton (i);
				selectStep (i);
			} else if (i == count - 1) {
				jQuery ("#step" + i).hide ();
				createPrevButton (i);
			} else {
				jQuery ("#step" + i).hide ();
				createPrevButton (i);
				createNextButton (i);
			}
		});

		function createPrevButton (i) {
			var stepName = "step" + i;
			jQuery ("#" + stepName + "commands").append ("<button type='button' id='" + stepName + "Prev' class='btn btn-success btn-mini btn-prev  prev'>&laquo; Anterior</button>");
			jQuery ("#" + stepName + "Prev").bind ("click", function () {
				jQuery ("#" + stepName).hide ();
				jQuery ("#step" + (i - 1)).show ();
				jQuery (submitButtonName).hide ();
				selectStep (i - 1);
			});
		}

		function createNextButton (i) {
			//actions
			var stepName = "step" + i,
				switchStep;
			jQuery ("#" + stepName + "commands").append ("<button type='button' id='" + stepName + "Next' class='btn btn-success btn-mini btn-next next'>Siguiente &raquo;</button>");
			jQuery ("#" + stepName + "Next").bind ("click", function () {
				switch (i) {
					case 0:
						switchStep = NotificationUtils.validateNotification (element, 1);
						break;
					case 1:
						switchStep = NotificationUtils.validateFilters (element);
						break;
					case 2:
						//switchStep = true;
						switchStep = NotificationUtils.validateNotification (element, 2);
						break;
					default:
						switchStep = true;
						break;
				}
				if (switchStep) {
					jQuery ("#" + stepName).hide ();
					jQuery ("#step" + (i + 1)).show ();
					if (i + 2 == count) {
						jQuery (submitButtonName).show ();
					}
					selectStep (i + 1);
				}
			});
		}

		function selectStep (i) {
			var steps = jQuery ('#steps');
			steps.find ('li').removeClass ("active");
			steps.find ('span').removeClass ("badge-primary");
			jQuery ("#stepDesc" + i).addClass ("active");
			jQuery ("#setpSpan" + i).addClass ("badge-primary");
			jQuery ("html, body").animate ({ scrollTop: 0 }, 800);
		}

	};

	jQuery ('#event-parameter').keydown (function (e) {
		if (jQuery.inArray (e.keyCode, [ 46, 8, 9, 27, 13, 110 ]) !== -1 ||
			(e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
			(e.keyCode >= 35 && e.keyCode <= 40 && (e.keyCode == 188 || e.keyCode == 190) )) {
			return;
		}
		if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
			e.preventDefault ();
		}
	});

	var onDocumentReadyHandler = function () {
		if (typeof alert_arr !== "undefined") {
			fLabels[ 'e' ] = alert_arr.EQUALS;
			fLabels[ 'n' ] = alert_arr.NOT_EQUALS_TO;
			fLabels[ 's' ] = alert_arr.STARTS_WITH;
			fLabels[ 'ew' ] = alert_arr.ENDS_WITH;
			fLabels[ 'c' ] = alert_arr.CONTAINS;
			fLabels[ 'k' ] = alert_arr.DOES_NOT_CONTAINS;
			fLabels[ 'h' ] = alert_arr.GREATER_OR_EQUALS;
			fLabels[ 'bw' ] = alert_arr.BETWEEN;
			fLabels[ 'b' ] = alert_arr.BEFORE;
			fLabels[ 'a' ] = alert_arr.AFTER;
			hfLabels[ 'e' ] = 'texto o valor para comparar';
			hfLabels[ 'n' ] = 'texto o valor para comparar';
			hfLabels[ 's' ] = 'Comienza con el texto?';
			hfLabels[ 'ew' ] = 'Termina con el texto?';
			hfLabels[ 'c' ] = 'Contiene el texto?';
			hfLabels[ 'k' ] = 'No contiene el texto?';
			hfLabels[ 'l' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'g' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'm' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'h' ] = 'Valor o aaaa-mm-dd si es fecha';
			hfLabels[ 'bw' ] = 'inferior,superior o fechas: aaaa-mm-dd,aaaa-mm-dd';
			hfLabels[ 'b' ] = 'antes de aaaa-mm-dd';
			hfLabels[ 'a' ] = 'despues de aaaa-mm-dd';
		}

		typeofdata[ 'V' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];
		typeofdata[ 'N' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
		typeofdata[ 'T' ] = [ 'e', 'b', 'a' ];
		typeofdata[ 'I' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
		typeofdata[ 'C' ] = [ 'e', 'n' ];
		typeofdata[ 'D' ] = [ 'e', 'b', 'a' ];
		typeofdata[ 'DT' ] = [ 'e', 'b', 'a' ];
		typeofdata[ 'NN' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
		typeofdata[ 'E' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];

		if (jQuery ("#advanced").find (".condition-group").length > 0) {
			var dataType = '', filterRow, thisInput;
			jQuery ('li[id ^= row-]').each (function (index, item) {
				dataType = jQuery (item).find ('select').eq (0).children ('option:selected').attr ('data-type');
				thisInput = jQuery (item).find ('input').eq (0);
				if (jQuery.inArray (dataType, [ 'T', 'D', 'DT' ]) !== -1) {
					filterRow = jQuery (item).find ('select').eq (0).parent ().parent ();
					filterRow.find ('.is-date').removeClass ('hide');
					thisInput.attr ('readonly', true);
					thisInput.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
				}

			});
		}

		jQuery ('#filter-start-date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		jQuery ('#filter-end-date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });

	};

	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));