(function (jQuery) {
	// Private variables
	var modal                      = null,
		currentEntityId            = null,
		currentModuleName          = null,
		multipleSelection          = null,
		relationships              = null,
		referencedModuleName       = null,
		requestedFilterDescription = null,
		requestedFilterValues      = null,
		searchFieldName            = null,
		searchKeyword              = null,
		targetDataFieldId          = null,
		targetDisplayFieldId       = null,
		modalTitle                 = null;

	// Private methods
    String.prototype.capitalize = function() {
        return this.charAt(0).toUpperCase() + this.slice(1);
    };

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		// Mover foco al body antes de remover el modal para evitar aria-hidden warning
		jQuery('body').focus();
		
		jQuery (this).remove ();
		currentEntityId = null;
		currentModuleName = null;
		multipleSelection = null;
		relationships = null;
		referencedModuleName = null;
		requestedFilterDescription = null;
		requestedFilterValues = null;
		searchFieldName = null;
		searchKeyword = null;
		targetDisplayFieldId = null;
		modalTitle = null;
		modal = null;
	};

	var executeSearch = function (page, field, keyword) {
		var queryParams = [
			'module=' + encodeURIComponent (referencedModuleName),
			'action=Modal',
			'Ajax=true'
		];
		if (currentModuleName) {
			queryParams.push ('formodulename=' + encodeURIComponent (currentModuleName));
		}
		
		// SIEMPRE incluir forfieldname para mantener filtros configurados
		if (targetDataFieldId) {
			queryParams.push ('forfieldname=' + encodeURIComponent (targetDataFieldId));
		}

		if (
			(keyword !== undefined) && (keyword !== null) && (keyword.trim () !== '') &&
			(field !== undefined) && (field !== null) && (field.trim () !== '')
		) {
			queryParams.push ('keyword=' + encodeURIComponent (keyword));
			queryParams.push ('field=' + encodeURIComponent (field));
			searchFieldName = field;
			searchKeyword = keyword;
		} else if ((jQuery.isArray (requestedFilterValues)) && (requestedFilterValues.length > 0)) {
			queryParams.push (requestedFilterValues);
		} else {
			searchFieldName = null;
			searchKeyword = null;
		}

		if ((page !== undefined) && (page !== null) && (page !== '')) {
			queryParams.push ('page=' + encodeURIComponent (page));
		}

		jQuery.ajax ('index.php', {
			data:     queryParams.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (onExecuteSearchSuccessHandler).fail (onFailureHandler);
	};

	var onFailureHandler = function (jQueryResponse) {
		var message;
		if ((jQueryResponse) && (jQueryResponse.hasOwnProperty ('responseText')) && (jQueryResponse.responseText)) {
			message = 'Se ha presentado un error: ' + jQueryResponse.responseText;
		} else {
			message = 'Se ha presentado un error inesperado. Intenta más tarde';
		}
		alert (message);
	};

	var onExecuteSearchSuccessHandler = function (response) {
		setSelectionStuff (response, false);
		setPagerStuff (response);
		showSelectionStuff ();
	};

	var onGetRecordsSuccessHandler = function (response) {
		var modalTemplate = jQuery ('#related-module-records-modal-template'),
			dummies, dummy, i;
		if (!response) {
			alert ('Se ha recibido una respuesta inesperada. Intenta más tarde');
			return;
		}

		relationships = response [ 'relationships' ];
		modal = jQuery (modalTemplate.html ());
		
		// Construir título con información de filtros aplicados
		var titleText = modalTitle;
		if (response.hasOwnProperty('appliedFiltersDescription') && response['appliedFiltersDescription']) {
			titleText += ' <span style="float: right; font-size: 0.7em; font-weight: normal; color: #666; margin-top: 3px; margin-right: 35px;">[Filtrado: ' + response['appliedFiltersDescription'] + ']</span>';
		}
		modal.find ('.modal-title').html (titleText);
		if (response [ 'fields' ] !== null) {
			setSearchStuff (response);
			setSelectionStuff (response, true);
			setPagerStuff (response);
			setQuickCreateStuff (response);
			showSelectionStuff ();
			modal.find ('#initial-date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
			modal.find ('#maximum-date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
			if (requestedFilterValues) {
				dummies = modal.find ('#search.selection-stuff .radio');
				for (i = 0; i < dummies.length; i += 1) {
					dummy = jQuery (dummies [ i ]);
					if (dummy.find ('[value=""]').length > 0) {
						selectSearchField (dummy [ 0 ]);
						break;
					}
				}
				modal.find ('#search.selection-stuff #search-keywords').val (requestedFilterDescription);
			} else if ((searchFieldName) && (searchKeyword)) {
				dummies = modal.find ('#search.selection-stuff .radio');
				for (i = 0; i < dummies.length; i += 1) {
					dummy = jQuery (dummies [ i ]);
					if (dummy.find ('[value="' + searchFieldName + '"]').length > 0) {
						selectSearchField (dummy [ 0 ]);
						break;
					}
				}
				modal.find ('#search.selection-stuff #search-keywords').val (searchKeyword);
			}
		} else {
			if(response [ 'isAdmin' ]) {
                dummy = (response.hasOwnProperty ('applications')) && (jQuery.isArray (response [ 'applications' ])) ? ': ' + response [ 'applications' ].join (', ') : '';
                modal.find ('.modal-body').html ('<div class="selection-stuff"><h4 class="text-center">El módulo no se encuentra instalado o está deshabilitado.</h4><h4 class="text-center">Para habilitar debe ir a la página de <i>Mi Suscripción: Uso del sistema</i>.</h4><h4 class="text-center">Ir a <a href="index.php?module=Home&action=ViewSubscriptionDetails&tab=sistema">Mi suscripción: Uso del sistema.</a></h4></div>');
			} else {
                modal.find ('.modal-body').html ('<div class="selection-stuff"><h4 class="text-center">El módulo no se encuentra instalado o está deshabilitado.</h4><h4 class="text-center">¡Por favor, contacte al usuario ' +  response ['administrador'] + ', quien es el administrador de la Plataforma!</h4></div>');
			}

		}
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var onSaveRecordSuccessHandler = function (response) {
		var relatedFieldName, fieldName;

		if (!response) {
			alert ('Se ha recibido una respuesta inesperada del servidor. Intenta más tarde');
			return;
		}

		if (relationships) {
			for (relatedFieldName in relationships) {
				if (!relationships.hasOwnProperty (relatedFieldName)) {
					continue;
				}

				fieldName = relationships [ relatedFieldName ];
				if (response.hasOwnProperty (relatedFieldName)) {
					jQuery ('[name="' + fieldName + '"]').val (response [ relatedFieldName ]);
				}
			}
		}
		jQuery ('#' + targetDisplayFieldId).val (response [ 'entityidentifiervalue' ]);
		jQuery ('#' + targetDataFieldId).val (response [ 'crmid' ]);
		
		// Mover foco al campo de destino antes de ocultar el modal para evitar aria-hidden warning
		jQuery ('#' + targetDisplayFieldId).focus();
		
		modal.modal ('hide');
		jQuery (document).trigger ('relatedModuleRecordSelected', [ modalTitle, targetDisplayFieldId, targetDataFieldId ]);
	};

	var setPagerStuff = function (response) {
		var currentPage   = response [ 'page' ],
			totalPages    = response [ 'totalPages' ],
			pagerSection  = modal.find ('#pager'),
			pagerTemplate = jQuery ('#related-module-records-pager-template'),
			pager, pagerItems, pagerItem, i;

		pagerSection.empty ();
        pagerSection.html ('');
		if (totalPages === 0) {
			return;
		}

        pagerSection.html (response [ 'pagination' ]);
	};

	var setQuickCreateStuff = function (response) {
		var fields                    = response.fields,
			quickCreateSection        = modal.find ('#quick-create'),
			quickCreateFieldTemplate  = jQuery ('#related-module-records-quick-create-field-template'),
			quickCreateSelectTemplate = jQuery ('#related-module-records-quick-create-select-template'),
			quickCreateField, picklistValues, options, option, i, j, dummy, isMandatory;

		quickCreateSection.empty ();
		for (i = 0; i < fields.length; i += 1) {
			if (fields [ i ].hasOwnProperty ('typeofdata')) {
				dummy = fields [ i ][ 'typeofdata' ].split ('~');
				isMandatory = dummy [ 1 ] === 'M';
			} else {
				isMandatory = false;
			}
			if ((!isMandatory) || (jQuery.inArray (fields [ i ][ 'uitype' ], [ '4', '53' ]) !== -1)) {
				continue;
			}

			if (jQuery.inArray (fields [ i ][ 'uitype' ], [ '15', '16' ]) !== -1) {
				quickCreateField = jQuery (quickCreateSelectTemplate.html ());
				quickCreateField.find ('label').attr ('for', fields [ i ][ 'fieldname' ]).text (fields [ i ][ 'fieldlabel' ]);
				quickCreateField.find ('.field-container').attr ('id', 'td_' + fields [ i ][ 'fieldname' ]);
				quickCreateField.find ('select').attr ('id', fields [ i ][ 'fieldname' ]).attr ('name', fields [ i ][ 'fieldname' ]);
				picklistValues = fields [ i ].hasOwnProperty ('picklistvalues') ? fields [ i ][ 'picklistvalues' ] : null;
				if (jQuery.isArray (picklistValues)) {
					options = [ jQuery ('<option></option>').val ('').text ('') ];
					for (j = 0; j < picklistValues.length; j += 1) {
						option = jQuery ('<option></option>').val (picklistValues [ j ]).text (picklistValues [ j ]);
						options.push (option);
					}
					quickCreateField.find ('select').append (options);
				}
				quickCreateSection.append (quickCreateField);
			} else if (fields [ i ][ 'uitype' ] === '33') {
				quickCreateField = jQuery (quickCreateSelectTemplate.html ());
				quickCreateField.find ('label').attr ('for', fields [ i ][ 'fieldname' ]).text (fields [ i ][ 'fieldlabel' ]);
				quickCreateField.find ('.field-container').attr ('id', 'td_' + fields [ i ][ 'fieldname' ]);
				quickCreateField.find ('select').attr ('id', fields [ i ][ 'fieldname' ]).attr ('name', fields [ i ][ 'fieldname' ]).attr ('multiple', 'multiple');
				picklistValues = fields [ i ].hasOwnProperty ('picklistvalues') ? fields [ i ][ 'picklistvalues' ] : null;
				if (jQuery.isArray (picklistValues)) {
					options = [ jQuery ('<option></option>').val ('').text ('') ];
					for (j = 0; j < picklistValues.length; j += 1) {
						option = jQuery ('<option></option>').val (picklistValues [ j ]).text (picklistValues [ j ]);
						options.push (option);
					}
					quickCreateField.find ('select').append (options);
				}
				quickCreateSection.append (quickCreateField);
            } else if (fields [ i ][ 'uitype' ] === '5') {
                quickCreateField = jQuery (quickCreateFieldTemplate.html ());
                quickCreateField.find ('label').attr ('for', fields [ i ][ 'fieldname' ]).text (fields [ i ][ 'fieldlabel' ]);
                quickCreateField.find ('.field-container').attr ('id', 'td_' + fields [ i ][ 'fieldname' ]);
                quickCreateField.find ('input[type="text"]').attr ('id', fields [ i ][ 'fieldname' ]).attr ('name', fields [ i ][ 'fieldname' ]);
                quickCreateField.find ('input[type="text"]').attr('readonly', true);
                quickCreateField.find ('input[type="text"]').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
                quickCreateSection.append (quickCreateField);
			} else if (fields [ i ][ 'uitype' ] !== '4') {
				quickCreateField = jQuery (quickCreateFieldTemplate.html ());
				quickCreateField.find ('label').attr ('for', fields [ i ][ 'fieldname' ]).text (fields [ i ][ 'fieldlabel' ]);
				quickCreateField.find ('.field-container').attr ('id', 'td_' + fields [ i ][ 'fieldname' ]);
				quickCreateField.find ('input[type="text"]').attr ('id', fields [ i ][ 'fieldname' ]).attr ('name', fields [ i ][ 'fieldname' ]);
				quickCreateSection.append (quickCreateField);
			}
		}
	};

	var setSearchStuff = function (response) {
		var records             = response [ 'records' ],
			fields              = response [ 'fields' ],
			searchSection       = modal.find ('#search'),
			searchFieldTemplate = jQuery ('#related-module-records-search-field-template'),
			searchFieldsList    = searchSection.find ('#fields-list'),
			searchFieldsListItems, searchFieldsListItem, fieldLabel, fieldName, i;

		searchFieldsList.empty ();
		searchSection.find ('#search-keywords').val ('');
		if (fields.length === 0) {
			return;
		}

		searchFieldsListItems = [];
		for (i = 0; i < fields.length; i += 1) {
			fieldName = fields [ i ] [ 'fieldname' ];
			fieldLabel = fields [ i ][ 'fieldlabel' ];
			searchFieldsListItem = jQuery (searchFieldTemplate.html ());
			searchFieldsListItem.find ('.search-field').attr ('id', 'search-' + fieldName).val (fieldName).prop ('checked', (i === 0));
			searchFieldsListItem.find ('label').attr ('for', 'search-' + fieldName).text (fieldLabel);
			searchFieldsListItems.push (searchFieldsListItem);
			if (i === 0) {
				searchSection.find ('#selected-search-field-label').text (fieldLabel.trim ());
			}
		}
		if (requestedFilterValues) {
			searchFieldsListItem = jQuery (searchFieldTemplate.html ());
			searchFieldsListItem.find ('.search-field').attr ('id', 'search-custom').val ('');
			searchFieldsListItem.find ('label').attr ('for', 'search-custom').text ('Personalizado');
			searchFieldsListItems.push (searchFieldsListItem);
		}

		searchFieldsList.append (searchFieldsListItems);
	};

	var setSelectionStuff = function (response, isOpened) {
		var records          = response [ 'records' ],
			fields           = response [ 'fields' ],
			recordsContainer = modal.find ('table#records'),
			titlesSection    = recordsContainer.find ('thead'),
			recordsSection   = recordsContainer.find ('tbody'),
			titlesRow, recordsRow, fieldNames, fieldName, label, i, j, relatedFieldName;

		titlesSection.empty ();
		recordsSection.empty ();

		fieldNames = [];
		titlesRow = jQuery ('<tr></tr>');
		if (multipleSelection) {
			label = jQuery ('<input>')
				.attr ('type', 'checkbox')
				.attr ('id', 'multiple-select-all')
				.attr ('onchange', 'return RelatedModuleModalUtils.toggleAllMultipleSelection (this);');
			titlesRow.append (jQuery ('<th></th>').append (label));
		}
		for (i = 0; i < fields.length; i += 1) {
			titlesRow.append ('<th>' + fields [ i ][ 'fieldlabel' ] + '</th>');
			fieldNames.push (fields [ i ][ 'fieldname' ]);
		}
		titlesSection.append (titlesRow);

		if ((records !== undefined) && (records !== null) && (records.length > 0)) {
			for (j = 0; j < records.length; j += 1) {
				recordsRow = jQuery ('<tr></tr>');
				if (multipleSelection) {
					label = jQuery ('<input>')
						.attr ('type', 'checkbox')
						.attr ('onchange', 'RelatedModuleModalUtils.toggleMultipleSelection (this);')
						.attr ('value', records [ j ][ 'crmid' ])
						.addClass ('multiple-select');
					recordsRow.append (jQuery ('<td></td>').append (label));
				}
				for (i = 0; i < fieldNames.length; i += 1) {
					fieldName = fields [ i ][ 'fieldname' ];
					if ((!multipleSelection) && (i === 0)) {
						label = jQuery ('<a></a>')
							.text (records [ j ][ fieldName ])
							.attr ('href', 'javascript:;')
							.attr ('onclick', 'return RelatedModuleModalUtils.selectRelatedModuleRecord (this);')
							.attr ('data-recordid', records [ j ][ 'crmid' ]);
						if (relationships) {
							for (relatedFieldName in relationships) {
								if (!relationships.hasOwnProperty (relatedFieldName)) {
									continue;
								}
								label.attr ('data-relationship-' + relationships [ relatedFieldName ], records [ j ][ relatedFieldName ]);
							}
						}
					} else {
						label = document.createTextNode (records [ j ][ fieldName ]);
					}
					recordsRow.append (jQuery ('<td></td>').append (label));
				}
				recordsSection.append (recordsRow);
			}
		} else if (isOpened) {
			recordsRow = jQuery ('<tr></tr>').append (jQuery ('<td colspan="' + fields.length + '" class="text-center"></td>').append ('No hay registros a mostrar ¡Crea tu primer ' + modalTitle + '!'));
			recordsSection.append (recordsRow);
		} else {
			recordsRow = jQuery ('<tr></tr>').append (jQuery ('<td colspan="' + fields.length + '" class="text-center"></td>').append ('No se encuentran registros que cumplan con el criterio de búsqueda'));
			recordsSection.append (recordsRow);
		}
	};

	// Public methods

	var goToPage = function (event, buttonElement) {
		var button = jQuery (buttonElement),
			page   = button.attr ('data-pagination-page');
        event.preventDefault();
		executeSearch (page, searchFieldName, searchKeyword);
	};

	var openModal = function (buttonElement) {
		var button           = jQuery (buttonElement),
			filterFieldNames = button.attr ('data-filter-field-names') ? JSON.parse (button.attr ('data-filter-field-names').replace (/'/gi, '"')) : null,
			title            = button.attr ('data-title').split ('_').join (' ').capitalize (),
			filterField, queryParams, i, dummy;

		currentEntityId      = button.attr ('data-current-entity-id');
		currentModuleName    = button.attr ('data-current-module');
		modalTitle           = title;
		multipleSelection    = (button.attr ('data-multiple-selection') === 'true');
		referencedModuleName = button.attr ('data-referenced-module');
		targetDataFieldId    = button.attr ('data-field-id');
		targetDisplayFieldId = button.attr ('data-display-field-id');

		queryParams = [
			'module=' + encodeURIComponent (referencedModuleName),
			'action=Modal',
			'Ajax=true'
		];
		if (currentModuleName) {
			queryParams.push ('formodulename=' + encodeURIComponent (currentModuleName));
		}
		if (targetDataFieldId) {
			queryParams.push ('forfieldname=' + encodeURIComponent (targetDataFieldId));
		}
		if (jQuery.isArray (filterFieldNames)) {
			requestedFilterValues = [];
			for (i = 0; i < filterFieldNames.length; i += 1) {
				dummy = filterFieldNames [ i ].split('@');
				filterField = jQuery ('[name="' + dummy [ 0 ] + '"]');
				filterField = filterField.hasClass ('module-reference') ? jQuery ('[name="' + dummy [ 0 ] + '_display"]:visible') : jQuery ('[name="' + dummy [ 0 ] + '"]:visible');
				if (filterField.length > 0) {
					requestedFilterValues.push ('requestedfiltervalues[' + dummy [ 1 ] + ']=' + encodeURIComponent (filterField.val ()));
				}
			}
			if (requestedFilterValues.length > 0) {
				queryParams.push (requestedFilterValues);
			}
			requestedFilterDescription = button.attr ('data-filter-description');
		}

		jQuery.ajax ('index.php', {
			data:     queryParams.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (onGetRecordsSuccessHandler).fail (onFailureHandler);
	};

	var relateRecords = function () {
		var multipleSelectCheckboxes = modal.find ('#records .multiple-select:checked'),
			queryParams, i;

		if (multipleSelectCheckboxes.length === 0) {
			alert ('Selecciona algún registro para relacionar');
			return false;
		}

		queryParams = [
			'module=' + encodeURIComponent (currentModuleName),
			'action=UpdateRelatedRecords',
			'relatedmodule=' + encodeURIComponent (referencedModuleName),
			'record=' + encodeURIComponent (currentEntityId),
			'operation=update',
			'Ajax=true'
		];

		for (i = 0; i < multipleSelectCheckboxes.length; i += 1) {
			queryParams.push ('relatedrecords[]=' + encodeURIComponent (jQuery (multipleSelectCheckboxes [ i ]).val ()));
		}

		jQuery.ajax('index.php', {
			data: queryParams.join('&'),
			dataType: 'json',
			method: 'post'
		}).done(function (response) {
			if (response && response.message) {
				alert(response.message);
			}
			if (response && response.status === 'success') {
				window.location.href = 'index.php?module=' + currentModuleName + '&action=DetailView&record=' + encodeURIComponent(currentEntityId) + '&tab=related_list';
			}
		}).fail(onFailureHandler);
	};

	var saveRecord = function () {
		var fields = modal.find ('.quick-create-field'),
			field, value, i, queryParams;

		queryParams = [
			'module=' + encodeURIComponent (referencedModuleName),
			'action=QuickCreate',
			'Ajax=true'
		];

		for (i = 0; i < fields.length; i += 1) {
			field = jQuery (fields [ i ]);
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce el valor del campo');
				field.focus ();
				return false;
			}
			queryParams.push (encodeURIComponent (field.attr ('id')) + '=' + encodeURIComponent (value));
		}

		jQuery.ajax ('index.php', {
			data:     queryParams.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (onSaveRecordSuccessHandler).fail (onFailureHandler);
	};

	var search = function (formElement) {
		var form    = jQuery (formElement),
			keyword = form.find ('#search-keywords').val (),
			field   = form.find ('input[name="field"]:checked').val ();

		executeSearch (1, field, keyword);
	};

	var selectRelatedModuleRecord = function (recordElement) {
		var record      = jQuery (recordElement),
			recordId    = record.attr ('data-recordid'),
			recordValue = record.text (),
			data, attribute, hasEvent, targetFieldName, targetFieldValue;
		data = record.data ();
		for (attribute in data) {
			if (!data.hasOwnProperty (attribute)) {
				continue;
			} else if (!attribute.match (/^relationship(.+)/g)) {
				continue;
			}

			targetFieldName = attribute.replace ('relationship', '').toLowerCase ();
			targetFieldValue = record.attr ('data-relationship-' + targetFieldName);
			jQuery ('[name="' + targetFieldName + '"]').val (targetFieldValue);
		}
		jQuery ('#' + targetDataFieldId).val (recordId);
		jQuery ('#' + targetDisplayFieldId).val (recordValue);
		hasEvent = jQuery ('#' + targetDataFieldId).attr('onchange');
	
		// Remover foco del modal antes de cerrarlo para evitar aria-hidden warning
		// No intentamos mover foco al campo destino porque puede estar en un grid no accesible
		if (document.activeElement && jQuery(document.activeElement).closest('#related-module-records').length > 0) {
			document.activeElement.blur();
		}
	
		modal.modal ('hide');
		if (typeof hasEvent !== "undefined" && hasEvent !== false && hasEvent !== null) {
            jQuery ('#' + targetDataFieldId).trigger ('onchange', recordId);
		} else {
            jQuery (document).trigger ('relatedModuleRecordSelected', [ modalTitle.toLowerCase(), targetDisplayFieldId, targetDataFieldId, recordValue ]);
		}

		return false;
	};

	var selectSearchField = function (selectedElement) {
		var row           = jQuery (selectedElement),
			searchSection = modal.find ('#search');
		searchSection.find ('#selected-search-field-label').text (row.text ().trim ());
		searchSection.find ('#fields-list').find ('.search-field').prop ('checked', false);
		row.find ('.search-field').prop ('checked', true);
		if (row.find ('.search-field').val () === '') {
			searchSection.find ('#search-keywords').prop ('disabled', true);
			searchSection.find ('button[type="submit"]').prop ('disabled', true);
		} else {
			searchSection.find ('#search-keywords').prop ('disabled', false);
			searchSection.find ('button[type="submit"]').prop ('disabled', false);
		}
	};

	var showQuickCreateStuff = function () {
		modal.find ('.quick-create-stuff').show ();
		modal.find ('.selection-stuff').hide ();
	};

	var showSelectionStuff = function () {
		modal.find ('.quick-create-stuff').hide ();
		modal.find ('.selection-stuff').show ();
		if (multipleSelection) {
			modal.find ('.modal-footer .selection-stuff').hide ();
			modal.find ('.modal-footer .related-records-stuff').show ();
		}
	};

	var toggleAllMultipleSelection = function (checkboxElement) {
		var checkbox                 = jQuery (checkboxElement),
			isChecked                = checkbox.prop ('checked'),
			multipleSelectCheckboxes = checkbox.closest ('#records').find ('.multiple-select');

		multipleSelectCheckboxes.prop ('checked', isChecked);
	};

	var toggleMultipleSelection = function (checkboxElement) {
		var checkbox                     = jQuery (checkboxElement),
			recordsSection               = checkbox.closest ('#records'),
			allMultipleSelectionCheckbox = recordsSection.find ('#multiple-select-all'),
			multipleSelectCheckboxes     = recordsSection.find ('.multiple-select'),
			isChecked, i;

		isChecked = true;
		for (i = 0; i < multipleSelectCheckboxes.length; i += 1) {
			if (jQuery (multipleSelectCheckboxes [ i ]).prop ('checked') === false) {
				isChecked = false;
				break;
			}
		}
		allMultipleSelectionCheckbox.prop ('checked', isChecked);
	};

	var unrelateRecord = function (buttonElement) {
		var button = jQuery(buttonElement),
			getQuery = function (name) {
				var m = new RegExp('[?&]' + name + '=([^&#]*)').exec(window.location.search);
				return m ? decodeURIComponent(m[1].replace(/\+/g, ' ')) : null;
			},
			currentModule = button.attr('data-current-module') || currentModuleName || getQuery('module') || jQuery('[name="module"]').val(),
			currentRecord = button.attr('data-current-record') || currentEntityId || getQuery('record') || jQuery('[name="record"]').val(),
			relatedModuleParam = button.attr('data-related-module') || referencedModuleName || button.closest('[data-related-module]').attr('data-related-module'),
			relatedRecord = button.attr('data-related-record') || button.data('relatedRecord') || button.closest('[data-related-record]').attr('data-related-record'),
			args;

			// Dentro de unrelateRecord, justo antes del if de validación:
		/* console.log('[DBG] currentModule=', currentModule,
            ' currentRecord=', currentRecord,
            ' relatedModuleParam=', relatedModuleParam,
            ' relatedRecord=', relatedRecord); */

		// Respaldo extra: intenta tomar el módulo desde un anchor de la misma fila (href con "module=")
		if (!relatedModuleParam) {
			var row = button.closest('tr');
			var anchor = row.find('a[href*="module="]').first();
			if (anchor && anchor.length) {
				var href = anchor.attr('href');
				var m = /[?&]module=([^&#]*)/.exec(href);
				if (m && m[1]) {
					relatedModuleParam = decodeURIComponent(m[1].replace(/\+/g, ' '));
				}
			}
		}

		if (!confirm('Se eliminará la relación con el registro seleccionado. ¿Estás seguro?')) {
			return;
		}

		// Validación previa para evitar llamados inválidos. Retrocompatible.
		if (!currentModule || !currentRecord || !relatedModuleParam || !relatedRecord) {
			alert('Faltan parámetros requeridos para eliminar la relación.');
			return;
		}

		args = [
			'module=' + encodeURIComponent(currentModule),
			'action=UpdateRelatedRecords',
			'record=' + encodeURIComponent(currentRecord),
			'relatedmodule=' + encodeURIComponent(relatedModuleParam),
			'relatedrecords[]=' + encodeURIComponent(relatedRecord),
			'operation=delete',
			'Ajax=true'
		];

		jQuery.ajax('index.php', {
			data: args.join('&'),
			dataType: 'json',
			method: 'post'
		}).done(function (response) {
			if (response && response.message) {
				alert(response.message);
			}
			if (response && response.status === 'success') {
				// Quitar la fila en el modal
				button.closest('tr').fadeOut(500, function() {
					jQuery(this).remove();
				});
				// Quitar la(s) fila(s) correspondiente(s) en la tarjeta/listado relacionado, fuera del modal
				var removedId = relatedRecord;
				if (removedId) {
					var selector = 'button[data-related-record="' + removedId + '"]';
					jQuery(selector).each(function() {
						var $btn = jQuery(this);
						// Ignorar botones dentro del modal
						if ($btn.closest('#related-module-records').length > 0) return;
						// Si existen, validar coincidencia con módulo y registro principal para mayor precisión
						var btnModule = $btn.attr('data-related-module') || '';
						var btnCurrentRecord = $btn.attr('data-current-record') || '';
						if ((relatedModuleParam && btnModule && btnModule !== relatedModuleParam) ||
							(currentRecord && btnCurrentRecord && btnCurrentRecord !== String(currentRecord))) {
							return; // no coincide, saltar
						}
						$btn.closest('tr').fadeOut(500, function(){ jQuery(this).remove(); });
					});
				}
			}
		}).fail(onFailureHandler);
	};

	window.RelatedModuleModalUtils = {
		goToPage:                   goToPage,
		openModal:                  openModal,
		relateRecords:              relateRecords,
		saveRecord:                 saveRecord,
		selectSearchField:          selectSearchField,
		showQuickCreateStuff:       showQuickCreateStuff,
		showSelectionStuff:         showSelectionStuff,
		search:                     search,
		selectRelatedModuleRecord:  selectRelatedModuleRecord,
		toggleAllMultipleSelection: toggleAllMultipleSelection,
		toggleMultipleSelection:    toggleMultipleSelection,
		unrelateRecord:             unrelateRecord
	};
} (jQuery));