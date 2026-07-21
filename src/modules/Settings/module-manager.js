(function (jQuery) {
	var MODULE_TYPE_USER            = '1',
		FIELD_TYPE_CURRENCY         = 71,
		FIELD_TYPE_GLOBAL_PICKLIST  = 16,
		FIELD_TYPE_MODULE_RECORDS   = 404,
		FIELD_TYPE_MODULE_REFERENCE = 10,
		FIELD_TYPE_MULTI_SELECT     = 33,
		FIELD_TYPE_NUMBER           = 7,
		FIELD_TYPE_PERCENTAGE       = 9,
		FIELD_TYPE_PICKLIST         = 15,
		FIELD_TYPE_PIPELINE         = 8192,
		FIELD_TYPE_TEXT             = 1;

	var wizard            = null,
		totalBlocks       = 0,
		totalFields       = 0,
		totalRelatedLists = 0;

	var addFirstBlock = function (card) {
		var blocks = card.el.find ('.block');
		if (card.isDisabled ()) {
			return;
		}

		card.wizard.hidePopovers ();
		if (blocks.length === 0) {
			card.el.find ('table > tbody').append (buildBlockHtml ());
		}
	};

	var addFirstBlockFields = function (card) {
		var blocks = wizard.cards [ 'blocks' ].el.find ('.block'),
			i, block, blockId, blockFields, blockField, fields;
		if (card.isDisabled ()) {
			return;
		}

		card.wizard.hidePopovers ();
		if (blocks.length === 0) {
			return;
		}

		blockFields = [];
		for (i = 0; i < blocks.length; i += 1) {
			block = jQuery (blocks [ i ]);
			blockId = block.data ('id');
			blockField = card.el.find ('#block-fields-' + blockId + '.block-fields');
			if (blockField.length === 0) {
				blockField = jQuery (buildBlockFieldHtml (blockId, block.find ('.block-label').val ()));
			}
			blockFields.push (blockField);
		}
		card.el.find ('.wizard-input-section').html (blockFields);
	};

	var addFirstViewColumn = function (card) {
		var columns = card.el.find ('.view-column');
		if (card.isDisabled ()) {
			return;
		}

		card.wizard.hidePopovers ();
		if (columns.length === 0) {
			card.el.find ('table > tbody').append (buildViewColumnHtml ());
		}
	};

	var buildBlockFieldHtml = function (blockId, blockLabel) {
		var contents = jQuery ('#module-creator-wizard-block-fields-template').html ().replace (/__BLOCK_ID__/g, blockId).replace (/__BLOCK_LABEL__/g, blockLabel).replace (/__FIELD_ID__/g, totalFields);
		totalFields += 1;
		return contents;
	};

	var buildBlockHtml = function () {
		var contents = jQuery ('#module-creator-wizard-block-template').html ().replace (/__BLOCK_ID__/g, totalBlocks);
		totalBlocks += 1;
		return contents;
	};

	var buildFieldHtml = function (blockId) {
		var contents = jQuery ('#module-creator-wizard-field-template').html ().replace (/__BLOCK_ID__/g, blockId).replace (/__FIELD_ID__/g, totalFields);
		totalFields += 1;
		return contents;
	};

	var buildRelatedListHtml = function () {
		var contents = jQuery ('#module-creator-wizard-related-list-template').html ().replace (/__RELATED_LIST_ID__/g, totalRelatedLists);
		totalRelatedLists += 1;
		return contents;
	};

	var buildViewColumnHtml = function () {
		var contents = jQuery ('#module-creator-wizard-view-column-template').html (),
			fields   = wizard.cards [ 'fields' ].el.find ('.field'),
			field, options, i;

		options = [ '<option value="__code__">Código</option>' ];
		for (i = 0; i < fields.length; i += 1) {
			field = jQuery (fields [ i ]);
			options.push ('<option value="' + field.find ('.field-name').val () + '">' + field.find ('.field-label').val () + '</option>');
		}
		return contents.replace (/__COLUMNS__/g, options.join ());
	};

	var destroyWizard = function () {
		wizard = null;
		window.location.reload ();
	};

	var getNormalizedText = function (value) {
		var from = 'àáäâèéëêìíïîòóöôùúüûñç·/-,:;',
			to   = 'aaaaeeeeiiiioooouuuunc______',
			i, l;

		value = value.toLowerCase ().replace (' ', '_');

		// remove accents, swap ñ for n, etc
		for (i = 0, l = from.length; i < l; i++) {
			value = value.replace (new RegExp (from.charAt (i), 'g'), to.charAt (i));
		}

		value = value.replace (/[^a-z0-9 _]/g, '').replace (/\s+/g, '_').replace (/-+/g, '_');
		return value;
	};

	var setEntityIdentifierOptions = function (card) {
		var options = [ jQuery ('<option value="__code__">Código</option>') ],
			fields  = wizard.cards [ 'fields' ].el.find ('.field'),
			i, field;
		for (i = 0; i < fields.length; i += 1) {
			field = jQuery (fields [ i ]);
			options.push (jQuery ('<option value="' + field.find ('.field-name').val () + '">' + field.find ('.field-label').val () + '</option>'));
		}
		card.el.find ('#entity-identifier-name').append (options);
	};

	var submitWizard = function () {
		jQuery.ajax ('index.php', {
			data:     wizard.serialize (),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			wizard.el.find ('.wizard-success .module-label').text (wizard.cards [ 'basic' ].el.find ('#module-label').val ());
			wizard.el.find ('.wizard-success .module-link').attr ('href', 'index.php?module=' + wizard.cards [ 'basic' ].el.find ('#module-name').val () + '&action=index');
			wizard.submitSuccess ();
			wizard.hideButtons ();
		}).fail (function (jQueryResponse) {
			wizard.el.find ('.wizard-failure .message').text (jQueryResponse.responseJSON);
			wizard.submitFailure ();
			wizard.hideButtons ();
		});
	};

	var updateProgressBar = function () {
		var cards      = wizard.cards,
			activeCard = wizard.getActiveCard (),
			index      = 0,
			cardName;
		for (cardName in cards) {
			if (cardName === activeCard.name) {
				break;
			}
			index += 1;
		}
		wizard.updateProgressBar ((index * 100) / Object.keys (wizard.cards).length);
	};

	var validateAdvancedCard = function (card) {
		var relatedLists = card.el.find ('.related-list'),
			field, value;
		if (card.isDisabled ()) {
			return true;
		}

		card.wizard.hidePopovers ();
		field = card.el.find ('#entity-identifier-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			card.wizard.errorPopover (field, 'Selecciona el campo identificador del módulo');
			field.focus ();
			return false;
		} else if (relatedLists.length > 0) {
			return validateRelatedLists (card, relatedLists);
		} else {
			return true;
		}
	};

	var validateBasicCard = function (card) {
		var field, value;

		card.wizard.hidePopovers ();
		field = card.el.find ('#module-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			card.wizard.errorPopover (field, 'Introduce el nombre código');
			field.focus ();
			return false;
		}

		field = card.el.find ('#module-label');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			card.wizard.errorPopover (field, 'Introduce el nombre público');
			field.focus ();
			return false;
		}

		field = card.el.find ('#module-type');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			card.wizard.errorPopover (field, 'Selecciona el tipo');
			field.focus ();
			return false;
		}

		field = card.el.find ('#module-location');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			card.wizard.errorPopover (field, 'Selecciona la ubicación');
			field.focus ();
			return false;
		} else if (value === 'menu') {
			field = card.el.find ('#menu-label');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				card.wizard.errorPopover (field, 'Selecciona el menú');
				field.focus ();
				return false;
			}
		}

		if (options.length === 0) {
			card.wizard.errorPopover (field, 'Selecciona las columnas del informe');
			return false;
		}
		return true;
	};

	var validateBlocks = function (card, blocks) {
		var processedBlockLabels = [],
			block, field, value, i;

		for (i = 0; i < blocks.length; i += 1) {
			block = jQuery (blocks [ i ]);

			field = block.find ('.block-label');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				card.wizard.errorPopover (field, 'Introduce el nombre');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value, processedBlockLabels) !== -1) {
				card.wizard.errorPopover (field, 'Ya tienes otro bloque con el mismo nombre');
				field.focus ();
				return false;
			}
			processedBlockLabels.push (value);
		}
		return true;
	};

	var validateFields = function (card, fields) {
		var processedFieldNames = [],
			row, field, type, values, value, i;

		for (i = 0; i < fields.length; i += 1) {
			row = jQuery (fields [ i ]);

			field = row.find ('.field-name');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				card.wizard.errorPopover (field, 'Introduce el nombre');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value, processedFieldNames) !== -1) {
				card.wizard.errorPopover (field, 'Ya tienes otro campo con el mismo nombre');
				field.focus ();
				return false;
			}
			processedFieldNames.push (value);

			field = row.find ('.field-label');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				card.wizard.errorPopover (field, 'Introduce la etiqueta');
				field.focus ();
				return false;
			}

			field = row.find ('.field-type');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				card.wizard.errorPopover (field, 'Selecciona el tipo');
				field.focus ();
				return false;
			}

			type = parseInt (value);
			if (jQuery.inArray (type, [ FIELD_TYPE_TEXT, FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
				field = row.find ('.field-length');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					card.wizard.errorPopover (field, 'Introduce la longitud del campo');
					field.focus ();
					return false;
				} else if ((!jQuery.isNumeric (value)) || (value <= 0)) {
					card.wizard.errorPopover (field, 'Introduce una longitud de campo mayor que cero');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
				field = row.find ('.field-precision');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					card.wizard.errorPopover (field, 'Introduce la precisión del número');
					field.focus ();
					return false;
				} else if ((!jQuery.isNumeric (value)) || (value < 0)) {
					card.wizard.errorPopover (field, 'Introduce una precisión de número mayor o igual que cero');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ FIELD_TYPE_PICKLIST, FIELD_TYPE_MULTI_SELECT, FIELD_TYPE_PIPELINE ]) !== -1) {
				field = row.find ('.field-picklist-values');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					card.wizard.errorPopover (field, 'Introduce la lista de valores');
					field.focus ();
					return false;
				}
				values = value.split ('\n');
				if (values.length < 2) {
					card.wizard.errorPopover (field, 'Introduce al menos dos valores');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ FIELD_TYPE_MODULE_REFERENCE, FIELD_TYPE_MODULE_RECORDS ]) !== -1) {
				field = row.find ('.field-referenced-module-name');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					card.wizard.errorPopover (field, 'Selecciona el módulo de la lista');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ FIELD_TYPE_GLOBAL_PICKLIST ]) !== -1) {
				field = row.find ('.field-global-picklist');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					card.wizard.errorPopover (field, 'Selecciona el campo especial');
					field.focus ();
					return false;
				}
			}
		}
		return true;
	};

	var validateBlocksCard = function (card) {
		var blocks = card.el.find ('.block');

		if (card.isDisabled ()) {
			return true;
		}

		card.wizard.hidePopovers ();
		if (blocks.length === 0) {
			card.wizard.errorPopover (card.el.find ('table tfoot'), 'Agrega al menos un bloque');
			return false;
		} else {
			return validateBlocks (card, blocks);
		}
	};

	var validateFieldsCard = function (card) {
		var blocksFields = card.el.find ('.block-fields'),
			fields       = card.el.find ('.field'),
			i, blockField;

		if (card.isDisabled ()) {
			return true;
		}

		card.wizard.hidePopovers ();
		if (fields.length === 0) {
			card.wizard.errorPopover (jQuery (blocksFields[ 0 ]).find ('table tfoot'), 'Agrega al menos un campo');
			return false;
		} else {
			for (i = 0; i < blocksFields.length; i += 1) {
				blockField = jQuery (blocksFields [ i ]);
				if (blockField.find ('.field').length === 0) {
					card.wizard.errorPopover (blockField.find ('table tfoot'), 'Agrega al menos un campo');
					return false;
				}
			}
			return validateFields (card, fields);
		}
	};

	var validateRelatedLists = function (card, relatedLists) {
		var processedLabels      = [],
			processedModuleNames = [],
			relatedList, field, value, i;

		for (i = 0; i < relatedLists.length; i += 1) {
			relatedList = jQuery (relatedLists [ i ]);

			field = relatedList.find ('.related-list-label');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				card.wizard.errorPopover (field, 'Introduce la etiqueta');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value, processedLabels) !== -1) {
				card.wizard.errorPopover (field, 'Ya tienes otra lista con la misma etiqueta');
				field.focus ();
				return false;
			}
			processedLabels.push (value);

			field = relatedList.find ('.related-list-module-name');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				card.wizard.errorPopover (field, 'Selecciona el módulo');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value, processedModuleNames) !== -1) {
				card.wizard.errorPopover (field, 'Ya tienes otra lista con el mismo módulo');
				field.focus ();
				return false;
			}
			processedModuleNames.push (value);

			field = relatedList.find ('.related-list-action:checked');
			value = field.val ();
			if ((value === undefined) || (value === null)) {
				card.wizard.errorPopover (relatedList.find ('.related-list-action:first'), 'Selecciona alguna acción');
				field.focus ();
				return false;
			}
		}
		return true;
	};

	var validateViewColumns = function (card, columns) {
		var processedColumnNames = [],
			column, field, value, i;

		for (i = 0; i < columns.length; i += 1) {
			column = jQuery (columns [ i ]);

			field = column.find ('.view-column-name');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				card.wizard.errorPopover (field, 'Selecciona el campo');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value, processedColumnNames) !== -1) {
				card.wizard.errorPopover (field, 'Ya tienes otra columna con el mismo campo');
				field.focus ();
				return false;
			}
			processedColumnNames.push (value);
		}
		return true;
	};

	var validateViewColumnsCard = function (card) {
		var columns = card.el.find ('.view-column');

		if (card.isDisabled ()) {
			return true;
		}

		card.wizard.hidePopovers ();
		if (columns.length === 0) {
			card.wizard.errorPopover (card.el.find ('table tfoot'), 'Agrega al menos una columna');
			return false;
		} else {
			return validateViewColumns (card, columns);
		}
	};

	var addBlock = function (buttonElement) {
		var card            = wizard.cards [ 'blocks' ],
			blocksContainer = jQuery (buttonElement).closest ('#blocks'),
			blocks          = blocksContainer.find ('.block');

		card.wizard.hidePopovers ();
		if ((blocks.length > 0) && (!validateBlocks (card, blocks))) {
			return;
		}
		blocksContainer.find ('table > tbody').append (buildBlockHtml ());
	};

	var addField = function (buttonElement) {
		var card        = wizard.cards [ 'fields' ],
			blockFields = jQuery (buttonElement).closest ('.block-fields'),
			fields      = blockFields.find ('.field');

		card.wizard.hidePopovers ();
		if ((fields.length > 0) && (!validateFields (card, fields))) {
			return;
		}
		blockFields.find ('table > tbody').append (buildFieldHtml (blockFields.data ('id')));
	};

	var addRelatedList = function (buttonElement) {
		var card                  = wizard.cards [ 'advanced' ],
			relatedListsContainer = jQuery (buttonElement).closest ('#related-lists'),
			relatedLists          = relatedListsContainer.find ('.related-list');

		card.wizard.hidePopovers ();
		if ((relatedLists.length > 0) && (!validateRelatedLists (card, relatedLists))) {
			return;
		}
		relatedListsContainer.find ('table > tbody').append (buildRelatedListHtml ());
	};

	var addViewColumn = function (buttonElement) {
		var card             = wizard.cards [ 'blocks' ],
			columnsContainer = jQuery (buttonElement).closest ('#view-columns'),
			columns          = columnsContainer.find ('.view-column');

		card.wizard.hidePopovers ();
		if ((columns.length > 0) && (!validateViewColumns (card, columns))) {
			return;
		}
		columnsContainer.find ('table > tbody').append (buildViewColumnHtml ());
	};

	var closeModuleCreatorModal = function () {
		wizard.reset ().close ().trigger ('closed');
	};

	var deleteRow = function (buttonElement) {
		if (!confirm ('¿Estás seguro que quieres eliminar el elemento seleccionado?')) {
			return;
		}
		jQuery (buttonElement).closest ('tr').remove ();
	};

	var filterByApplication = function (selectElement) {
		var select          = jQuery (selectElement),
			applicationCode = select.val (),
			modules, module, applicationCodes, i, dummy;

		if (applicationCode.trim () === '') {
			select.closest ('.table').find ('tr.module').show ();
		} else if (applicationCode === '-1') {
			modules = select.closest ('.table').find ('tr.module');
			for (i = 0; i < modules.length; i += 1) {
				module = jQuery (modules [ i ]);
				dummy = module.data ('applications');
				applicationCodes = dummy ? JSON.parse (dummy.split ('\'').join ('"')) : [];
				if (applicationCodes.length === 0) {
					module.show ();
				} else {
					module.hide ();
				}
			}
		} else {
			modules = select.closest ('.table').find ('tr.module');
			for (i = 0; i < modules.length; i += 1) {
				module = jQuery (modules [ i ]);
				dummy = module.data ('applications');
				applicationCodes = dummy ? JSON.parse (dummy.split ('\'').join ('"')) : [];
				if (jQuery.inArray (applicationCode, applicationCodes) !== -1) {
					module.show ();
				} else {
					module.hide ();
				}
			}
		}
	};

	var normalizeFieldContents = function (fieldElement) {
		var field = jQuery (fieldElement);
		field.val (getNormalizedText (field.val ()));
	};

	var openModuleCreatorModal = function () {
		var template = jQuery ('#module-creator-wizard-template');
		wizard = jQuery (template.html ()).wizard ({
			backdrop:   'static',
			showCancel: false,
			buttons:    {
				cancelText:     'Cancelar',
				nextText:       'Siguiente →',
				backText:       '← Atrás',
				submitText:     'Crear',
				submittingText: 'Creando...'
			}
		});

		wizard.cards [ 'basic' ].on ('validate', validateBasicCard);
		wizard.cards [ 'blocks' ].on ('validate', validateBlocksCard)
								 .on ('selected', addFirstBlock);
		wizard.cards [ 'fields' ].on ('validate', validateFieldsCard)
								 .on ('selected', addFirstBlockFields);
		wizard.cards [ 'view-columns' ].on ('validate', validateViewColumnsCard)
									   .on ('selected', addFirstViewColumn);
		wizard.cards [ 'advanced' ].on ('validate', validateAdvancedCard)
								   .on ('selected', setEntityIdentifierOptions);
		wizard.on ('submit', submitWizard)
			  .on ('closed', destroyWizard)
			  .on ('incrementCard', updateProgressBar)
			  .on ('decrementCard', updateProgressBar)
			  .show ();
	};

	var restartModuleCreatorModal = function () {
		wizard.reset ();
	};

	var setFieldType = function (selectElement) {
		var select            = jQuery (selectElement),
			row               = select.closest ('.field'),
			selectedFieldType = select.val (),
			fieldType         = isNaN (selectedFieldType) ? 1 : parseInt (selectedFieldType),
			field;

		field = row.find ('.field-length');
		if (jQuery.inArray (fieldType, [ FIELD_TYPE_TEXT, FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
			field.show ();
			if (jQuery.inArray (fieldType, [ FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
				field.val (18);
			} else {
				field.val (255);
			}
		} else {
			field.hide ();
		}

		field = row.find ('.field-precision');
		if (jQuery.inArray (fieldType, [ FIELD_TYPE_NUMBER, FIELD_TYPE_PERCENTAGE, FIELD_TYPE_CURRENCY ]) !== -1) {
			field.show ();
			field.val (2);
		} else {
			field.hide ();
		}

		field = row.find ('.field-picklist-values');
		if (jQuery.inArray (fieldType, [ FIELD_TYPE_PICKLIST, FIELD_TYPE_MULTI_SELECT, FIELD_TYPE_PIPELINE ]) !== -1) {
			field.show ();
		} else {
			field.hide ();
		}

		field = row.find ('.field-global-picklist');
		if (fieldType === FIELD_TYPE_GLOBAL_PICKLIST) {
			field.show ();
			row.find ('.field-name').prop ('readonly', true);
		} else {
			field.hide ();
			row.find ('.field-name').prop ('readonly', false);
		}

		field = row.find ('.field-referenced-module-name');
		if (jQuery.inArray (fieldType, [ FIELD_TYPE_MODULE_REFERENCE, FIELD_TYPE_MODULE_RECORDS ]) !== -1) {
			field.show ();
		} else {
			field.hide ();
		}
	};

	var setGlobalPicklistFieldName = function (selectElement) {
		var select                  = jQuery (selectElement),
			globalPicklistFieldName = select.val ();

		select.closest ('.field').find ('.field-name').val (globalPicklistFieldName);
	};

	var setModuleLocation = function (selectElement) {
		var select   = jQuery (selectElement),
			location = select.val ();

		if (location === 'menu') {
			select.closest ('.wizard-input-section').find ('#menu-label').prop ('disabled', false);
			select.closest ('.wizard-input-section').find ('#menu-label-container').show ();
		} else {
			select.closest ('.wizard-input-section').find ('#menu-label').prop ('disabled', true);
			select.closest ('.wizard-input-section').find ('#menu-label-container').hide ();
		}
	};

	var setModuleType = function (selectElement) {
		var select = jQuery (selectElement),
			type   = select.val ();

		if (type === MODULE_TYPE_USER) {
			wizard.cards [ 'blocks' ].enable ().deselect ().el.find ('.form-control').prop ('disabled', false);
			wizard.cards [ 'fields' ].enable ().deselect ().el.find ('.form-control').prop ('disabled', false);
			wizard.cards [ 'view-columns' ].enable ().deselect ().el.find ('.form-control').prop ('disabled', false);
			wizard.cards [ 'advanced' ].enable ().deselect ().el.find ('.form-control').prop ('disabled', false);
		} else {
			wizard.cards [ 'blocks' ].disable ().el.find ('.form-control').prop ('disabled', true);
			wizard.cards [ 'fields' ].disable ().el.find ('.form-control').prop ('disabled', true);
			wizard.cards [ 'view-columns' ].disable ().el.find ('.form-control').prop ('disabled', true);
			wizard.cards [ 'advanced' ].disable ().el.find ('.form-control').prop ('disabled', true);
		}
	};

	window.ModuleManager = {
		addBlock:                   addBlock,
		addField:                   addField,
		addRelatedList:             addRelatedList,
		addViewColumn:              addViewColumn,
		closeModuleCreatorModal:    closeModuleCreatorModal,
		deleteBlock:                deleteRow,
		deleteField:                deleteRow,
		deleteRelatedList:          deleteRow,
		deleteViewColumn:           deleteRow,
		filterByApplication:        filterByApplication,
		normalizeFieldContents:     normalizeFieldContents,
		openModuleCreatorModal:     openModuleCreatorModal,
		restartModuleCreatorModal:  restartModuleCreatorModal,
		setGlobalPicklistFieldName: setGlobalPicklistFieldName,
		setFieldType:               setFieldType,
		setModuleLocation:          setModuleLocation,
		setModuleType:              setModuleType
	};
} (jQuery));