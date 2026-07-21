(function (jQuery) {
	// Private variables
	var modal                  = null,
		availableRoles         = null,
		moduleFields           = null,
		moduleReference        = null,
		presence               = null,
		referencedModuleFields = null,
		sourceModuleName       = null,
		uiType                 = null,
		totalPicklistValues    = -1,
		totalPipelineValues    = -100,
		totalReferenceFilters  = -1;

	// Private methods
	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var onFailureHandler = function (jQueryResponse) {
		alert ('Se ha presentado un error: ' + jQueryResponse.responseText);
	};

	var onGetPropertiesSuccessHandler = function (response) {
		var modalTemplate = jQuery ('#field-properties-modal-template');
		if (!response) {
			alert ('Se ha recibido una respuesta inesperada. Intenta más tarde');
			return;
		}

		availableRoles = response.hasOwnProperty ('availableroles') ? response [ 'availableroles' ] : null;
		moduleFields = response.hasOwnProperty ('modulefields') ? response [ 'modulefields' ] : null;
		presence = response.hasOwnProperty ('presence') ? response [ 'presence' ] : null;
		moduleReference = response.hasOwnProperty ('modulereference') ? response [ 'modulereference' ] : null;
		referencedModuleFields = response.hasOwnProperty ('referencedmodulefields') ? response [ 'referencedmodulefields' ] : null;
		uiType = response.hasOwnProperty ('uitype') ? response [ 'uitype' ] : null;
		modal = jQuery (modalTemplate.html ());
		setBasicProperties (response);
		setValidationProperties (response);
		setModuleReferencesProperties (response);
		setPicklistDependenciesProperties (response);
		setPicklistValuesProperties (response);
		setPipelineDependenciesProperties (response);
		setPipelineValuesProperties (response);
		setCalculatedFieldProperties (response);
		modal.find ('#initial-date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		modal.find ('#maximum-date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var onSavePropertiesSuccessHandler = function () {
		modal.modal ('hide');
		window.location.reload ();
	};

	var setBasicProperties = function (response) {
		var isMandatoryField   = modal.find ('#ismandatory'),
			presenceField      = modal.find ('#presence'),
			defaultValueField  = modal.find ('#default-value'),
			calculatedSystemId = modal.find ('#calculatedSystemId');

		modal.find ('#field-name').text (response.label);
		modal.find ('input[name="fieldname"]').val (response.name);
		isMandatoryField.prop ('checked', response [ 'ismandatory' ] ? true : false);
		if (presence === 0) {
			presenceField.prop ('checked', true).attr ('disabled', 'disabled');
		} else if (presence === 2) {
			presenceField.prop ('checked', true).removeAttr ('disabled');
		} else {
			presenceField.prop ('checked', false).removeAttr ('disabled');
		}
		calculatedSystemId.val (response [ 'calculationid' ]);
		if (uiType == '2206') {
			defaultValueField.val (response [ 'defaultvalue' ]).hide ();
		} else {
			defaultValueField.val (response [ 'defaultvalue' ]).show ();
		}
		if (jQuery.inArray (uiType, [ '1', '7', '9', '71' ]) !== -1) {
			modal.find ('#field-length-container').show ();
			modal.find ('#field-length').val (response.length);
		} else {
			modal.find ('#field-length-container').hide ();
			modal.find ('#field-length').val (null);
		}
		if (jQuery.inArray (uiType, [ '7', '9', '71' ]) !== -1) {
			modal.find ('#field-precision-container').show ();
			modal.find ('#field-precision').val (response.precision);
		} else {
			modal.find ('#field-precision-container').hide ();
			modal.find ('#field-precision').val (null);
		}
	};

	var setCalculatedFieldProperties = function (response) {
		if (response [ 'calculatedSystem' ] === null) {
			modal.find ('#calculation').closest ('.panel').addClass ('hidden');
			return false;
		}
		var calculatedList, li,
			row           = '',
			selectedValue = response [ 'calculationid' ],
			template      = modal.find ('#calculate-template'),
			divList       = modal.find ('.calculated-list');
		calculatedList = jQuery.parseJSON (response [ 'calculatedSystem' ]);
		for (li = 0; li < calculatedList.length; li++) {
			row = template.clone ().attr ('id', 'cs-' + li).attr ('rel', calculatedList[ li ][ 'calculationName' ]).attr ('title', calculatedList[ li ].description)
						  .html (calculatedList[ li ].name).removeClass ('hide');
			if (calculatedList[ li ][ 'calculationName' ] == selectedValue) {
				row.addClass ('active')
			}
			row.appendTo (divList);
		}
	};

	var setModuleReferenceFilterProperties = function () {
		var filters        = (moduleReference) && (moduleReference.hasOwnProperty ('filters')) ? moduleReference [ 'filters' ] : null,
			filtersSection = modal.find ('#reference-filters'),
			i, filterTemplate, j, dummies, dummy;

		modal.find ('#module-references-filters .target-module-label').text (modal.find ('#module-reference option:selected').text ());

		if (!filters) {
			return;
		}

		for (i = 0; i < filters.length; i += 1) {
			addModuleReferenceFilter ();
			filterTemplate = filtersSection.find ('.filter:last');
			filterTemplate.find ('.module-fields > option[value="' + filters [ i ][ 'field' ] + '"]').prop ('selected', true);
			filterTemplate.find ('.comparator > option[value="' + filters [ i ][ 'comparator' ] + '"]').prop ('selected', true);
			filterTemplate.find ('.filter-type[value="' + filters [ i ][ 'valuetype' ] + '"]').prop ('checked', true).click ();
			if (filters [ i ][ 'valuetype' ] === 'SOURCE FIELD') {
				dummies = filterTemplate.find ('.filter-fields > option[value="' + filters [ i ][ 'value' ] + '"]');
				for (j = 0; j < dummies.length; j += 1) {
					dummy = jQuery (dummies [ j ]);
					if (dummy.data ('module-name') === filters [ i ][ 'valuemodulename' ]) {
						dummy.prop ('selected', true);
						break;
					}
				}
			} else {
				filterTemplate.find ('.filter-value').val (filters [ i ][ 'value' ]);
			}
			filterTemplate.find ('.operator > option[value="' + filters [ i ][ 'operator' ] + '"]').prop ('selected', true);
		}
	};

	var setModuleReferenceRelationshipProperties = function () {
		var relationships            = (moduleReference) && (moduleReference.hasOwnProperty ('relationships')) ? moduleReference [ 'relationships' ] : null,
			relationshipsSection     = modal.find ('#relationships'),
			relationshipTemplateHtml = jQuery ('#relationship-template').html (),
			fieldName, referencedModuleFieldName, moduleFieldOptions, referencedModuleFieldOptions, relationshipTemplate;

		if (!relationships) {
			return;
		}

		for (referencedModuleFieldName in relationships) {
			if (!relationships.hasOwnProperty (referencedModuleFieldName)) {
				continue;
			}

			moduleFieldOptions = [];
			for (fieldName in moduleFields) {
				if (!moduleFields.hasOwnProperty (fieldName)) {
					continue;
				}

				moduleFieldOptions.push (jQuery ('<option></option>').text (moduleFields [ fieldName ].label).val (fieldName).prop ('selected', (relationships [ referencedModuleFieldName ] === fieldName)));
			}

			referencedModuleFieldOptions = [];
			if (referencedModuleFields) {
				for (fieldName in referencedModuleFields) {
					if (!referencedModuleFields.hasOwnProperty (fieldName)) {
						continue;
					}

					referencedModuleFieldOptions.push (jQuery ('<option></option>').text (referencedModuleFields [ fieldName ]).val (fieldName).prop ('selected', (referencedModuleFieldName === fieldName)));
				}
			}

			relationshipTemplate = jQuery (relationshipTemplateHtml);
			relationshipTemplate.find ('.referenced-module-fields').append (referencedModuleFieldOptions);
			relationshipTemplate.find ('.module-fields').append (moduleFieldOptions);
			relationshipsSection.append (relationshipTemplate);
		}
	};

	var setModuleReferencesProperties = function () {
		var moduleReferencesProperties = modal.find ('#module-references-properties'),
			moduleReferenceFiltersProperties = modal.find ('#module-references-filters'),
			referencedModuleName       = (moduleReference) && (moduleReference.hasOwnProperty ('name')) ? moduleReference [ 'name' ] : null;
		if (uiType !== '10') {
			moduleReferencesProperties.closest ('.panel').addClass ('hidden');
			moduleReferenceFiltersProperties.closest ('.panel').addClass ('hidden');
			return;
		}

		if (moduleReference) {
			moduleReferencesProperties.find ('#module-reference').val (referencedModuleName);
		}

		setModuleReferenceRelationshipProperties ();
		setModuleReferenceFilterProperties ();
		moduleReferencesProperties.closest ('.panel').removeClass ('hidden');
		moduleReferenceFiltersProperties.closest ('.panel').removeClass ('hidden');
	};

	var setPicklistDependenciesProperties = function (response) {
		var dependenciesSection    = modal.find ('#dependencies'),
			dependencyTemplateHtml = jQuery ('#dependency-template').html (),
			dependencyTemplate, field, fieldName, mandatoryFieldOptions, optionalFieldOptions, visibleFieldOptions, hiddenFieldOptions, value, picklistValues, picklistValue;

		if (jQuery.inArray (uiType, [ '15', '16', '8192' ]) === -1) {
			dependenciesSection.find ('.dependency').remove ();
			modal.find ('#dependencies-properties').closest ('.panel').addClass ('hidden');
		}
		if ((jQuery.inArray (uiType, [ '15', '16' ]) === -1) || (!moduleFields) || (!response.hasOwnProperty ('picklistvalues')) || (!response [ 'picklistvalues' ])) {
			return;
		}

		picklistValues = {
			'__NO_SELECTION__': {
				id:    0,
				label: '(Sin selección)',
				value: null
			}
		};
		for (value in response [ 'picklistvalues' ]) {
			if (!response [ 'picklistvalues' ].hasOwnProperty (value)) {
				continue;
			}

			picklistValues [ value ] = response [ 'picklistvalues' ][ value ];
		}

		for (value in picklistValues) {
			if (!picklistValues.hasOwnProperty (value)) {
				continue;
			}

			mandatoryFieldOptions = [];
			optionalFieldOptions = [];
			visibleFieldOptions = [];
			hiddenFieldOptions = [];
			picklistValue = picklistValues [ value ];
			for (fieldName in moduleFields) {
				if (!moduleFields.hasOwnProperty (fieldName)) {
					continue;
				} else if (fieldName === response.name) {
					continue;
				}

				field = moduleFields [ fieldName ];
				if ((field [ 'hiddenfor' ]) && (jQuery.inArray (picklistValue.value, field [ 'hiddenfor' ]) !== -1)) {
					hiddenFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
					if (field [ 'ismandatory' ]) {
						mandatoryFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName).hide ());
					} else {
						optionalFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName).hide ());
					}
				} else if ((field [ 'visiblefor' ]) && (jQuery.inArray (picklistValue.value, field [ 'visiblefor' ]) !== -1)) {
					visibleFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
					if (field [ 'ismandatory' ]) {
						mandatoryFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName).hide ());
					} else {
						optionalFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName).hide ());
					}
				} else if (field [ 'ismandatory' ]) {
					mandatoryFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
				} else {
					optionalFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
				}
			}
			dependencyTemplate = jQuery (dependencyTemplateHtml).attr ('data-picklist-value-id', picklistValue.id);
			dependencyTemplate.find ('.picklist-value').val (value);
			dependencyTemplate.find ('.picklist-label').val (picklistValue.hasOwnProperty ('label') ? picklistValue.label : picklistValue.value);
			dependencyTemplate.find ('.available-fields > .optional-fields').append (optionalFieldOptions);
			dependencyTemplate.find ('.available-fields > .mandatory-fields').append (mandatoryFieldOptions);
			dependencyTemplate.find ('.hidden-fields').append (hiddenFieldOptions);
			dependencyTemplate.find ('.visible-fields').append (visibleFieldOptions);
			dependenciesSection.append (dependencyTemplate);

		}
		modal.find ('#dependencies-properties').closest ('.panel').removeClass ('hidden');
	};

	var setPipelineDependenciesProperties = function (response) {
		var dependenciesSection    = modal.find ('#dependencies'),
			dependencyTemplateHtml = jQuery ('#dependency-template').html (),
			dependencyTemplate, i, pipelineValue, field, fieldName, mandatoryFieldOptions, optionalFieldOptions, visibleFieldOptions, hiddenFieldOptions;

		if (jQuery.inArray (uiType, [ '15', '16', '8192' ]) === -1) {
			dependenciesSection.find ('.dependency').remove ();
			modal.find ('#dependencies-properties').closest ('.panel').addClass ('hidden');
		}
		if ((uiType !== '8192') || (!moduleFields) || (!response.hasOwnProperty ('pipelinevalues')) || (!response [ 'pipelinevalues' ])) {
			return;
		}

		for (i = 0; i < response [ 'pipelinevalues' ].length; i += 1) {
			mandatoryFieldOptions = [];
			optionalFieldOptions = [];
			visibleFieldOptions = [];
			hiddenFieldOptions = [];
			pipelineValue = response [ 'pipelinevalues' ][ i ];
			for (fieldName in moduleFields) {
				if (!moduleFields.hasOwnProperty (fieldName)) {
					continue;
				} else if (fieldName === response.name) {
					continue;
				}

				field = moduleFields [ fieldName ];
				if ((field [ 'hiddenfor' ]) && (jQuery.inArray (pipelineValue, field [ 'hiddenfor' ]) !== -1)) {
					hiddenFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
					if (field [ 'ismandatory' ]) {
						mandatoryFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName).hide ());
					} else {
						optionalFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName).hide ());
					}
				} else if ((field [ 'visiblefor' ]) && (jQuery.inArray (pipelineValue, field [ 'visiblefor' ]) !== -1)) {
					visibleFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
					if (field [ 'ismandatory' ]) {
						mandatoryFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName).hide ());
					} else {
						optionalFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName).hide ());
					}
				} else if (field [ 'ismandatory' ]) {
					mandatoryFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
				} else {
					optionalFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
				}
			}
			dependencyTemplate = jQuery (dependencyTemplateHtml).attr ('data-pipeline-value-id', i);
			dependencyTemplate.find ('.picklist-value').val (pipelineValue);
			dependencyTemplate.find ('.picklist-label').val (pipelineValue);
			dependencyTemplate.find ('.available-fields > .optional-fields').append (optionalFieldOptions);
			dependencyTemplate.find ('.available-fields > .mandatory-fields').append (mandatoryFieldOptions);
			dependencyTemplate.find ('.hidden-fields').append (hiddenFieldOptions);
			dependencyTemplate.find ('.visible-fields').append (visibleFieldOptions);
			dependenciesSection.append (dependencyTemplate);
		}
		modal.find ('#dependencies-properties').closest ('.panel').removeClass ('hidden');
	};

	var setPicklistValuesProperties = function (response) {
		var picklistValuesSection = modal.find ('#picklist-values'),
			picklistValueHtml     = jQuery ('#picklist-value-template').html (),
			picklistValues        = response.hasOwnProperty ('picklistvalues') ? response [ 'picklistvalues' ] : null,
			picklistValueTemplate, availableRoleId, picklistValue, hiddenRolesOptions, visibleRolesOptions, option, value;

		picklistValuesSection.find ('.picklist-value').remove ();
		if ((jQuery.inArray (uiType, [ '15', '16', '33' ]) === -1) || (!picklistValues)) {
			modal.find ('#picklist-values-properties').closest ('.panel').addClass ('hidden');
			return;
		} else if (uiType === '16') {
			modal.find ('.add-value-button').prop ('disabled', true);
		}

		for (value in picklistValues) {
			if (!picklistValues.hasOwnProperty (value)) {
				continue;
			}
			picklistValue = picklistValues [ value ];
			if (uiType === '16') {
				picklistValueTemplate = jQuery (picklistValueHtml).attr ('data-picklist-value-id', picklistValue.id);
				picklistValueTemplate.find ('.picklist-value-id').val (picklistValue.id).prop ('disabled', true);
				picklistValueTemplate.find ('.picklist-label').val (picklistValue.value).prop ('disabled', true);
				picklistValueTemplate.find ('.visible-roles').prop ('disabled', true);
				picklistValueTemplate.find ('.hidden-roles').prop ('disabled', true);
				picklistValueTemplate.find ('.hide-value-button').prop ('disabled', true);
				picklistValueTemplate.find ('.show-value-button').prop ('disabled', true);
				picklistValueTemplate.find ('.delete-value-button').prop ('disabled', true);
			} else {
				hiddenRolesOptions = [];
				visibleRolesOptions = [];
				for (availableRoleId in availableRoles) {
					if (!availableRoles.hasOwnProperty (availableRoleId)) {
						continue;
					}

					option = jQuery ('<option></option>').text (availableRoles [ availableRoleId ]).val (availableRoleId);

					if (jQuery.inArray (availableRoleId, picklistValue.roles) !== -1) {
						visibleRolesOptions.push (option);
					} else {
						hiddenRolesOptions.push (option);
						visibleRolesOptions.push (jQuery ('<option></option>').text (availableRoles [ availableRoleId ]).val (availableRoleId).hide ());
					}
				}

				picklistValueTemplate = jQuery (picklistValueHtml).attr ('data-picklist-value-id', picklistValue.id);
				picklistValueTemplate.find ('.picklist-value-id').val (picklistValue.id);
				picklistValueTemplate.find ('.picklist-label').val (picklistValue.value);
				picklistValueTemplate.find ('.visible-roles').append (visibleRolesOptions);
				picklistValueTemplate.find ('.hidden-roles').append (hiddenRolesOptions);
			}
			picklistValuesSection.append (picklistValueTemplate);
			modal.find ('#picklist-values-properties').closest ('.panel').removeClass ('hidden');
		}
	};

	var setPipelineValuesProperties = function (response) {
		var pipelineValuesSection = modal.find ('#pipeline-values'),
			pipelineValueHtml     = jQuery ('#pipeline-value-template').html (),
			pipelineValues        = response.hasOwnProperty ('pipelinevalues') ? response [ 'pipelinevalues' ] : null,
			pipelineValueTemplate, i;

		pipelineValuesSection.find ('.pipeline-value').remove ();
		if (uiType !== '8192') {
			modal.find ('#pipeline-values-properties').closest ('.panel').addClass ('hidden');
			return;
		}

		for (i = 0; i < pipelineValues.length; i += 1) {
			pipelineValueTemplate = jQuery (pipelineValueHtml).attr ('data-pipeline-value-id', i);
			pipelineValueTemplate.find ('.pipeline-label').val (pipelineValues [ i ]);
			pipelineValuesSection.append (pipelineValueTemplate);
			modal.find ('#pipeline-values-properties').closest ('.panel').removeClass ('hidden');
		}
	};

	var setValidationProperties = function (response) {
		var validationType,
			validations = response.hasOwnProperty ('validations') ? response [ 'validations' ] : null;

		if (jQuery.inArray (uiType, [ '5', '6' ]) !== -1) {
			modal.find ('.number-validation').addClass ('hidden');
			modal.find ('.date-validation').removeClass ('hidden');
		} else if (uiType === '7') {
			modal.find ('.number-validation').removeClass ('hidden');
			modal.find ('.date-validation').addClass ('hidden');
		} else {
			modal.find ('.number-validation').addClass ('hidden');
			modal.find ('.date-validation').addClass ('hidden');
		}

		if (!validations) {
			return;
		}
		for (validationType in validations) {
			if (!validations.hasOwnProperty (validationType)) {
				continue;
			}

			if (validationType === 'unique') {
				modal.find ('#unique').prop ('checked', validations.unique);
			} else if (validationType === 'date') {
				if (validations.date [ 'initialvalue' ] === 'today') {
					modal.find ('#initial-date-select').val ('today');
					modal.find ('#initial-date').val (validations.date [ 'initialvalue' ]);
				} else if (validations.date [ 'initialvalue' ]) {
					modal.find ('#initial-date-select').val ('custom');
					modal.find ('#initial-date').val (validations.date [ 'initialvalue' ]);
				} else {
					modal.find ('#initial-date-select').val ('');
					modal.find ('#initial-date').val ('');
				}
				setDateValidationFields (modal.find ('#initial-date-select'));
				if (validations.date [ 'maximumvalue' ] === 'today') {
					modal.find ('#maximum-date-select').val ('today');
					modal.find ('#maximum-date').val (validations.date [ 'maximumvalue' ]);
				} else if (validations.date [ 'maximumvalue' ]) {
					modal.find ('#maximum-date-select').val ('custom');
					modal.find ('#maximum-date').val (validations.date [ 'maximumvalue' ]);
				} else {
					modal.find ('#maximum-date-select').val ('');
					modal.find ('#maximum-date').val ('');
				}
				setDateValidationFields (modal.find ('#maximum-date-select'));
			} else if (validationType === 'number') {
				modal.find ('#initial-value').val (validations.number [ 'initialvalue' ]);
				modal.find ('#maximum-value').val (validations.number [ 'maximumvalue' ]);
			}
		}
	};

	var validateProperties = function () {
		var section, rows, row, selectedValues, value, filterType, i;

		if (uiType === '10') {
			section = modal.find ('#reference-filters');
			rows = section.find ('.filter');
			if (rows.length === 0) {
				return true;
			}

			for (i = 0; i < rows.length; i += 1) {
				row = jQuery (rows [ i ]);

				value = row.find ('.module-fields').val ();
				if ((value === null) || (value === undefined) || (value.trim () === '')) {
					alert ('Seleciona el campo');
					return false;
				}

				value = row.find ('.comparator').val ();
				if ((value === null) || (value === undefined) || (value.trim () === '')) {
					alert ('Seleciona el operador de comparación');
					return false;
				}

				filterType = row.find ('.filter-type:checked').val ();
				if ((filterType === null) || (filterType === undefined) || (filterType.trim () === '')) {
					alert ('Seleciona el tipo de valor');
					return false;
				}

				if (filterType === 'SOURCE FIELD') {
					value = row.find ('.filter-fields').val ();
					if ((value === null) || (value === undefined) || (value.trim () === '')) {
						alert ('Seleciona el campo');
						return false;
					}
				} else if (filterType === 'LITERAL') {
					value = row.find ('.filter-value').val ();
					if ((value === null) || (value === undefined) || (value.trim () === '')) {
						alert ('Introduce el valor');
						return false;
					}
				}
			}
		} else if (jQuery.inArray (uiType, [ '15', '16', '33' ]) !== -1) {
			section = modal.find ('#picklist-values');
			rows = section.find ('.picklist-value');
			if (rows.length === 0) {
				alert ('Debes suministrar las opciones del campo');
				return false;
			}

			selectedValues = [];
			for (i = 0; i < rows.length; i += 1) {
				row = jQuery (rows [ i ]);
				value = row.find ('.picklist-label').val ();
				if (jQuery.inArray (value, selectedValues) !== -1) {
					alert ('El valor ' + (value ? "'" + value + "'" : '(vacío)') + ' está repetido');
					return false;
				}
				selectedValues.push (value);
			}
		} else if (uiType === '8192') {
			section = modal.find ('#pipeline-values');
			rows = section.find ('.pipeline-value');
			if (rows.length === 0) {
				alert ('Debes suministrar las opciones del campo');
				return false;
			}
		}

		return true;
	};

	// Public methods
	var addModuleReferenceFilter = function () {
		var filtersSection       = modal.find ('#reference-filters'),
			filterTemplateHtml   = jQuery ('#reference-filter-template').html (),
			referencedModuleName = (moduleReference) && (moduleReference.hasOwnProperty ('name')) ? moduleReference [ 'name' ] : null,
			fieldName, moduleFieldOptions, referencedModuleFieldOptions, filterTemplate;

		moduleFieldOptions = [];
		for (fieldName in moduleFields) {
			if (!moduleFields.hasOwnProperty (fieldName)) {
				continue;
			}

			moduleFieldOptions.push (jQuery ('<option></option>').attr ('data-module-name', sourceModuleName).text (moduleFields [ fieldName ].label).val (fieldName));
		}

		referencedModuleFieldOptions = [];
		if (referencedModuleFields) {
			for (fieldName in referencedModuleFields) {
				if (!referencedModuleFields.hasOwnProperty (fieldName)) {
					continue;
				}

				referencedModuleFieldOptions.push (jQuery ('<option></option>').attr ('data-module-name', referencedModuleName).text (referencedModuleFields [ fieldName ]).val (fieldName));
			}
		}

		filtersSection.find ('.operator:last').prop ('disabled', false).show ();
		filterTemplate = jQuery (filterTemplateHtml);
		filterTemplate.find ('.module-fields').append (referencedModuleFieldOptions);
		filterTemplate.find ('.filter-type').attr ('name', 'filtertype' + totalReferenceFilters);
		filterTemplate.find ('.filter-fields').append (moduleFieldOptions);
		filtersSection.append (filterTemplate);
		totalReferenceFilters -= 1;
	};

	var addModuleReferenceRelationship = function () {
		var relationshipsSection     = modal.find ('#relationships'),
			relationshipTemplateHtml = jQuery ('#relationship-template').html (),
			fieldName, moduleFieldOptions, referencedModuleFieldOptions, relationshipTemplate;

		moduleFieldOptions = [];
		for (fieldName in moduleFields) {
			if (!moduleFields.hasOwnProperty (fieldName)) {
				continue;
			}

			moduleFieldOptions.push (jQuery ('<option></option>').text (moduleFields [ fieldName ].label).val (fieldName));
		}

		referencedModuleFieldOptions = [];
		if (referencedModuleFields) {
			for (fieldName in referencedModuleFields) {
				if (!referencedModuleFields.hasOwnProperty (fieldName)) {
					continue;
				}

				referencedModuleFieldOptions.push (jQuery ('<option></option>').text (referencedModuleFields [ fieldName ]).val (fieldName));
			}
		}

		relationshipTemplate = jQuery (relationshipTemplateHtml);
		relationshipTemplate.find ('.referenced-module-fields').append (referencedModuleFieldOptions);
		relationshipTemplate.find ('.module-fields').append (moduleFieldOptions);
		relationshipsSection.append (relationshipTemplate);
	};

	var addPicklistValue = function () {
		var picklistValuesSection  = modal.find ('#picklist-values'),
			dependenciesSection    = modal.find ('#dependencies'),
			picklistValueHtml      = jQuery ('#picklist-value-template').html (),
			dependencyTemplateHtml = jQuery ('#dependency-template').html (),
			dependencyTemplate, field, fieldName, mandatoryFieldOptions, visibleFieldOptions, picklistValueTemplate, availableRoleId, visibleRolesOptions, option, value;

		visibleRolesOptions = [];
		for (availableRoleId in availableRoles) {
			if (!availableRoles.hasOwnProperty (availableRoleId)) {
				continue;
			}

			option = jQuery ('<option></option>').text (availableRoles [ availableRoleId ]).val (availableRoleId);
			visibleRolesOptions.push (option);
		}

		mandatoryFieldOptions = [];
		visibleFieldOptions = [];
		for (fieldName in moduleFields) {
			if (!moduleFields.hasOwnProperty (fieldName)) {
				continue;
			}

			field = moduleFields [ fieldName ];
			if (field [ 'ismandatory' ]) {
				mandatoryFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
			} else {
				visibleFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
			}
		}

		picklistValueTemplate = jQuery (picklistValueHtml).attr ('data-picklist-value-id', totalPicklistValues);
		picklistValueTemplate.find ('.picklist-value-id').val (totalPicklistValues);
		picklistValueTemplate.find ('.picklist-label').val ();
		picklistValueTemplate.find ('.visible-roles').append (visibleRolesOptions);
		picklistValuesSection.append (picklistValueTemplate);

		dependencyTemplate = jQuery (dependencyTemplateHtml).attr ('data-picklist-value-id', totalPicklistValues);
		dependencyTemplate.find ('.picklist-value').val ('');
		dependencyTemplate.find ('.picklist-label').val ('');
		dependencyTemplate.find ('.available-fields > .optional-fields').append (visibleFieldOptions);
		dependencyTemplate.find ('.available-fields > .mandatory-fields').append (mandatoryFieldOptions);
		dependenciesSection.append (dependencyTemplate);

		totalPicklistValues -= 1;
	};

	var addPipelineValue = function () {
		var pipelineValuesSection  = modal.find ('#pipeline-values'),
			dependenciesSection    = modal.find ('#dependencies'),
			pipelineValueHtml      = jQuery ('#pipeline-value-template').html (),
			dependencyTemplateHtml = jQuery ('#dependency-template').html (),
			dependencyTemplate, pipelineValueTemplate, mandatoryFieldOptions, visibleFieldOptions, fieldName, field;

		mandatoryFieldOptions = [];
		visibleFieldOptions = [];
		for (fieldName in moduleFields) {
			if (!moduleFields.hasOwnProperty (fieldName)) {
				continue;
			}

			field = moduleFields [ fieldName ];
			if (field [ 'ismandatory' ]) {
				mandatoryFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
			} else {
				visibleFieldOptions.push (jQuery ('<option></option>').text (field.label).val (fieldName));
			}
		}

		pipelineValueTemplate = jQuery (pipelineValueHtml).attr ('data-pipeline-value-id', totalPipelineValues);
		pipelineValueTemplate.find ('.pipeline-label').val ('');
		pipelineValuesSection.append (pipelineValueTemplate);

		dependencyTemplate = jQuery (dependencyTemplateHtml).attr ('data-pipeline-value-id', totalPipelineValues);
		dependencyTemplate.find ('.picklist-value').val ('');
		dependencyTemplate.find ('.picklist-label').val ('');
		dependencyTemplate.find ('.available-fields > .optional-fields').append (visibleFieldOptions);
		dependencyTemplate.find ('.available-fields > .mandatory-fields').append (mandatoryFieldOptions);
		dependenciesSection.append (dependencyTemplate);

		totalPipelineValues -= 1;
	};

	var deleteModuleReferenceFilter = function (buttonElement) {
		var button         = jQuery (buttonElement),
			filtersSection = modal.find ('#reference-filters');

		if (!confirm ('Vas a eliminar el filtro seleccionado. ¿Estás seguro?')) {
			return;
		}

		button.closest ('.filter').remove ();
		filtersSection.find ('.operator:last').prop ('disabled', true).hide ();
	};

	var deleteModuleReferenceRelationship = function (buttonElement) {
		var button = jQuery (buttonElement);

		if (!confirm ('Vas a eliminar la relación seleccionada. ¿Estás seguro?')) {
			return;
		}

		button.closest ('.relationship').remove ();
	};

	var deletePicklistValue = function (buttonElement) {
		var button          = jQuery (buttonElement),
			picklistValueId = button.closest ('.picklist-value').attr ('data-picklist-value-id');

		if (!confirm ('Vas a eliminar el valor seleccionado. ¿Estás seguro?')) {
			return;
		}

		modal.find ('.dependency[data-picklist-value-id="' + picklistValueId + '"]').remove ();
		button.closest ('.picklist-value').remove ();
	};

	var deletePipelineValue = function (buttonElement) {
		var button          = jQuery (buttonElement),
			pipelineValueId = button.closest ('.pipeline-value').attr ('data-pipeline-value-id');

		if (!confirm ('Vas a eliminar el valor seleccionado. ¿Estás seguro?')) {
			return;
		}

		modal.find ('.dependency[data-pipeline-value-id="' + pipelineValueId + '"]').remove ();
		button.closest ('.pipeline-value').remove ();
	};

	var editFieldProperties = function (moduleName, fieldName) {
		sourceModuleName = moduleName;
		jQuery.ajax ('index.php?module=Settings&action=SettingsAjax&file=GetFieldProperties&ajax=true&modulename=' + encodeURIComponent (moduleName) + '&fieldname=' + encodeURIComponent (fieldName), {
			dataType: 'json',
			method:   'get'
		}).done (onGetPropertiesSuccessHandler).fail (onFailureHandler);
	};

	var hideDependencyFields = function (buttonElement) {
		var button          = jQuery (buttonElement),
			dependency      = button.closest ('.dependency'),
			availableFields = dependency.find ('.available-fields'),
			hiddenFields    = dependency.find ('.hidden-fields'),
			fields          = availableFields.find ('option:selected:visible'),
			field, i, n;

		if (fields.length === 0) {
			return;
		}

		n = fields.length;
		for (i = 0; i < n; i += 1) {
			field = jQuery (fields [ i ]);
			hiddenFields.append (jQuery ('<option></option>').text (field.text ()).val (field.val ()));
			field.removeAttr ('selected').hide ();
		}
	};

	var hidePicklistValues = function (buttonElement) {
		var button           = jQuery (buttonElement),
			picklistValueRow = button.closest ('.picklist-value'),
			visibleRoles     = picklistValueRow.find ('.visible-roles'),
			hiddenRoles      = picklistValueRow.find ('.hidden-roles'),
			roles            = visibleRoles.find ('option:selected'),
			role, i, n;

		if (roles.length === 0) {
			return;
		}

		n = roles.length;
		for (i = 0; i < n; i += 1) {
			role = jQuery (roles [ i ]);
			hiddenRoles.append (jQuery ('<option></option>').text (role.text ()).val (role.val ()));
			role.removeAttr ('selected').hide ();
		}
	};

	var removeHiddenDependencyFields = function (buttonElement) {
		var button          = jQuery (buttonElement),
			dependency      = button.closest ('.dependency'),
			availableFields = dependency.find ('.available-fields'),
			hiddenFields    = dependency.find ('.hidden-fields'),
			fields          = hiddenFields.find ('option:selected'),
			field, i, n;

		if (fields.length === 0) {
			return;
		}

		n = fields.length;
		for (i = 0; i < n; i += 1) {
			field = jQuery (fields [ i ]);
			availableFields.find ('option[value="' + field.val () + '"]').show ();
			field.remove ();
		}
	};

	var removeVisibleDependencyFields = function (buttonElement) {
		var button          = jQuery (buttonElement),
			dependency      = button.closest ('.dependency'),
			availableFields = dependency.find ('.available-fields'),
			visibleFields   = dependency.find ('.visible-fields'),
			fields          = visibleFields.find ('option:selected'),
			field, i, n;

		if (fields.length === 0) {
			return;
		}

		n = fields.length;
		for (i = 0; i < n; i += 1) {
			field = jQuery (fields [ i ]);
			availableFields.find ('option[value="' + field.val () + '"]').show ();
			field.remove ();
		}
	};

	var saveProperties = function () {
		var modal                      = jQuery ('#field-properties-modal'),
			fieldName                  = modal.find ('input[name="fieldname"]').val (),
			moduleName                 = modal.find ('input[name="modulename"]').val (),
			calculationId              = modal.find ('input[name="calculatedSystemId"]').val (),
			basicProperties            = modal.find ('#basic-properties'),
			validationProperties       = modal.find ('#validation-properties'),
			moduleReferencesProperties = modal.find ('#module-references-properties'),
			moduleReferencesFilters = modal.find ('#module-references-filters'),
			dependenciesProperties     = modal.find ('#dependencies-properties'),
			picklistValuesProperties   = modal.find ('#picklist-values-properties'),
			pipelineValuesProperties   = modal.find ('#pipeline-values-properties'),
			i, j, m, n, data, dependencies, dependency, value, hiddenFields, visibleFields, picklistValueRows, picklistValueRow, pipelineValueRows, pipelineValueRow, relationshipRows, relationshipRow, filterRows, filterRow, id, roles, role;

		if (!validateProperties ()) {
			return;
		}

		data = [
			'module=Settings',
			'action=SettingsAjax',
			'file=SaveFieldProperties',
			'Ajax=true',
			'fieldname=' + encodeURIComponent (fieldName),
			'modulename=' + encodeURIComponent (moduleName),
			'calculationid=' + encodeURIComponent (calculationId)
		];

		// Propiedades básicas
		if (basicProperties.find ('#ismandatory').is (':checked')) {
			data.push ('ismandatory=true');
		}
		if (basicProperties.find ('#presence').is (':checked')) {
			data.push ('presence=' + (presence === 0 ? 0 : 2));
		} else {
			data.push ('presence=1');
		}
		data.push ('defaultvalue=' + encodeURIComponent (basicProperties.find ('#default-value').val ()));
		if (jQuery.inArray (uiType, [ '1', '7', '9', '71' ]) !== -1) {
			data.push ('length=' + encodeURIComponent (basicProperties.find ('#field-length').val ()));
		}
		if (jQuery.inArray (uiType, [ '7', '9', '71' ]) !== -1) {
			data.push ('precision=' + encodeURIComponent (basicProperties.find ('#field-precision').val ()));
		}

		// Validaciones
		if (validationProperties.find ('#unique').is (':checked')) {
			data.push ('validationunique=true');
		}

		if (jQuery.inArray (uiType, [ '5', '6' ]) !== -1) {
			// Validaciones de campos tipo fecha
			data.push ('validationdateinitialvalue=' + encodeURIComponent (validationProperties.find ('#initial-date').val ()));
			data.push ('validationdatemaximumvalue=' + encodeURIComponent (validationProperties.find ('#maximum-date').val ()));
		} else if (uiType === '7') {
			// Validaciones de campos tipo número
			data.push ('validationnumberinitialvalue=' + encodeURIComponent (validationProperties.find ('#initial-value').val ()));
			data.push ('validationnumbermaximumvalue=' + encodeURIComponent (validationProperties.find ('#maximum-value').val ()));
		} else if (uiType === '10') {
			// Referencias a módulos
			data.push ('modulereference[name]=' + encodeURIComponent (moduleReferencesProperties.find ('#module-reference').val ()));
			relationshipRows = moduleReferencesProperties.find ('.relationship');
			if (relationshipRows.length > 0) {
				n = relationshipRows.length;
				for (i = 0; i < n; i += 1) {
					relationshipRow = jQuery (relationshipRows [ i ]);
					data.push ('modulereference[relationships][' + encodeURIComponent (relationshipRow.find ('.referenced-module-fields').val ()) + ']=' + encodeURIComponent (relationshipRow.find ('.module-fields').val ()));
				}
			}

			filterRows = moduleReferencesFilters.find ('.filter');
			if (filterRows.length > 0) {
				for (i = 0; i < filterRows.length; i += 1) {
					filterRow = jQuery (filterRows [ i ]);
					data.push ('modulereference[filters][' + i + '][field]=' + encodeURIComponent (filterRow.find ('.module-fields').val ()));
					data.push ('modulereference[filters][' + i + '][comparator]=' + encodeURIComponent (filterRow.find ('.comparator').val ()));
					data.push ('modulereference[filters][' + i + '][valuetype]=' + encodeURIComponent (filterRow.find ('.filter-type:checked').val ()));
					if (filterRow.find ('.filter-type:checked').val () === 'SOURCE FIELD') {
						data.push ('modulereference[filters][' + i + '][valuemodulename]=' + encodeURIComponent (filterRow.find ('.filter-fields option:selected').data ('module-name')));
						data.push ('modulereference[filters][' + i + '][value]=' + encodeURIComponent (filterRow.find ('.filter-fields').val ()));
					} else if (filterRow.find ('.filter-type:checked').val () === 'LITERAL') {
						data.push ('modulereference[filters][' + i + '][value]=' + encodeURIComponent (filterRow.find ('.filter-value').val ()));
					}
					if (filterRow.find ('.operator').prop ('disabled') === false) {
						data.push ('modulereference[filters][' + i + '][operator]=' + encodeURIComponent (filterRow.find ('.operator').val ()));
					}
				}
			}
		} else if (jQuery.inArray (uiType, [ '15', '16', '33' ]) !== -1) {
			// Picklist values
			picklistValueRows = picklistValuesProperties.find ('.picklist-value');
			n = picklistValueRows.length;
			for (i = 0; i < n; i += 1) {
				picklistValueRow = jQuery (picklistValueRows [ i ]);
				id = picklistValueRow.find ('.picklist-value-id').val ();
				value = picklistValueRow.find ('.picklist-label').val ();
				roles = picklistValueRow.find ('select.visible-roles > option');
				if (roles.length > 0) {
					m = roles.length;
					for (j = 0; j < m; j += 1) {
						role = jQuery (roles [ j ]);
						if (role.css ('display') !== 'none') {
							data.push ('picklistvalues[' + id + '][roles][]=' + encodeURIComponent (role.val ()));
						}
					}
				}
				data.push ('picklistvalues[' + id + '][value]=' + encodeURIComponent (value))
			}

			// Dependencias
			if (jQuery.inArray (uiType, [ '15', '16' ]) !== -1) {
				dependencies = dependenciesProperties.find ('.dependency');
				n = dependencies.length;
				for (i = 0; i < n; i += 1) {
					dependency = jQuery (dependencies [ i ]);
					value = dependency.find ('.picklist-value').val ();
					hiddenFields = dependency.find ('select.hidden-fields > option');
					if (hiddenFields.length > 0) {
						m = hiddenFields.length;
						for (j = 0; j < m; j += 1) {
							data.push ('hiddenfields[' + (value ? encodeURIComponent (value) : '__EMPTY__') + '][]=' + encodeURIComponent (jQuery (hiddenFields [ j ]).val ()))
						}
					}
					visibleFields = dependency.find ('select.visible-fields > option');
					if (visibleFields.length > 0) {
						m = visibleFields.length;
						for (j = 0; j < m; j += 1) {
							data.push ('visiblefields[' + (value ? encodeURIComponent (value) : '__EMPTY__') + '][]=' + encodeURIComponent (jQuery (visibleFields [ j ]).val ()))
						}
					}
				}
			}
		} else if (uiType === '8192') {
			// Pipeline values
			pipelineValueRows = pipelineValuesProperties.find ('.pipeline-value');
			for (i = 0; i < pipelineValueRows.length; i += 1) {
				pipelineValueRow = jQuery (pipelineValueRows [ i ]);
				value = pipelineValueRow.find ('.pipeline-label').val ();
				data.push ('pipelinevalues[' + i + ']=' + encodeURIComponent (value))
			}

			// Dependencias
			dependencies = dependenciesProperties.find ('.dependency');
			for (i = 0; i < dependencies.length; i += 1) {
				dependency = jQuery (dependencies [ i ]);
				value = dependency.find ('.picklist-value').val ();
				hiddenFields = dependency.find ('select.hidden-fields > option');
				if (hiddenFields.length > 0) {
					m = hiddenFields.length;
					for (j = 0; j < m; j += 1) {
						data.push ('hiddenfields[' + (value ? encodeURIComponent (value) : '__EMPTY__') + '][]=' + encodeURIComponent (jQuery (hiddenFields [ j ]).val ()))
					}
				}
				visibleFields = dependency.find ('select.visible-fields > option');
				if (visibleFields.length > 0) {
					m = visibleFields.length;
					for (j = 0; j < m; j += 1) {
						data.push ('visiblefields[' + (value ? encodeURIComponent (value) : '__EMPTY__') + '][]=' + encodeURIComponent (jQuery (visibleFields [ j ]).val ()))
					}
				}
			}
		}

		jQuery.ajax ('index.php', {
			data:     data.join ('&'),
			dataType: 'text',
			method:   'post'
		}).done (onSavePropertiesSuccessHandler).fail (onFailureHandler);
	};

	var searchCalculated = function (obj) {
		var filter = jQuery (obj).val (),
			list   = modal.find ('.calculated-list');

		if (filter != '') {
			jQuery.expr[ ':' ].Contains = function (a, i, m) {
				return (a.textContent || a.innerText || "").toUpperCase ().indexOf (m[ 3 ].toUpperCase ()) >= 0;
			};

			list.find ("a:not(:Contains('" + filter + "'))").slideUp ();
			list.find ("a:Contains('" + filter + "')").slideDown ();
		} else {
			list.find ('a').slideDown ();
		}
		return false;
	};

	var setCalculatedSystem = function (obj) {
		var mySelection        = jQuery (obj),
			selectionValues,
			calculatedSystemId = modal.find ('#calculatedSystemId');
		mySelection.parent ().each (function (index, item) {
			jQuery (item).find ('a').removeClass ('active');
		});
		mySelection.addClass ('active');
		selectionValues = mySelection.attr ('rel');
		calculatedSystemId.val (selectionValues);
	};

	var setDateValidationFields = function (selectElement) {
		var select     = jQuery (selectElement),
			group      = select.closest ('.date-validation').find ('.custom-date-group'),
			dateChoice = select.val ();

		if (dateChoice === 'today') {
			group.find ('#initial-date').val ('today');
			group.hide ();
		} else if (dateChoice === 'custom') {
			group.find ('#initial-date').val ('');
			group.show ();
		} else {
			group.find ('#initial-date').val ('');
			group.hide ();
		}
	};

	var setPicklistDependencyLabel = function (fieldElement) {
		var field           = jQuery (fieldElement),
			picklistValueId = field.closest ('.picklist-value').attr ('data-picklist-value-id'),
			dependency      = modal.find ('.dependency[data-picklist-value-id="' + picklistValueId + '"]'),
			value           = field.val ();

		dependency.find ('.picklist-value').val (value !== '' ? value : '__EMPTY__');
		dependency.find ('.picklist-label').val (value !== '' ? value : '(Vacío)');
	};

	var setPipelineDependencyLabel = function (fieldElement) {
		var field           = jQuery (fieldElement),
			pipelineValueId = field.closest ('.pipeline-value').attr ('data-pipeline-value-id'),
			dependency      = modal.find ('.dependency[data-pipeline-value-id="' + pipelineValueId + '"]'),
			value           = field.val ();

		dependency.find ('.picklist-label').val (value !== '' ? value : '(Vacío)');
	};

	var setModuleReferenceFilterType = function (radioElement) {
		var radio     = jQuery (radioElement),
			container = radio.closest ('.filter-target'),
			type      = radio.val ();

		if (type === 'SOURCE FIELD') {
			container.find ('.filter-fields').show ();
			container.find ('.filter-value').hide ();
		} else if (type === 'LITERAL') {
			container.find ('.filter-value').show ();
			container.find ('.filter-fields').hide ();
		} else {
			container.find ('.filter-fields').hide ();
			container.find ('.filter-value').hide ();
		}
	};

	var setModuleReferenceRelationships = function (selectElement) {
		var select               = jQuery (selectElement),
			relatedModuleName    = select.val (),
			relationshipsSection = select.closest ('.panel-body').find ('#relationships'),
			arguments;

		relationshipsSection.closest ('.table-responsive').hide ();
		relationshipsSection.find ('.relationship').remove ();
		if ((relatedModuleName === null) || (relatedModuleName === undefined) || (relatedModuleName.trim () === '')) {
			return;
		}

		arguments = [
			'module=Settings',
			'action=SettingsAjax',
			'file=GetAvailableFieldsData',
			'modulename=' + encodeURIComponent (relatedModuleName),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (function (response) {
			referencedModuleFields = response;
			relationshipsSection.closest ('.table-responsive').show ();
		}).fail (onFailureHandler);
	};

	var showDependencyFields = function (buttonElement) {
		var button          = jQuery (buttonElement),
			dependency      = button.closest ('.dependency'),
			availableFields = dependency.find ('.available-fields'),
			visibleFields   = dependency.find ('.visible-fields'),
			fields          = availableFields.find ('option:selected:visible'),
			field, i, n;

		if (fields.length === 0) {
			return;
		}

		n = fields.length;
		for (i = 0; i < n; i += 1) {
			field = jQuery (fields [ i ]);
			visibleFields.append (jQuery ('<option></option>').text (field.text ()).val (field.val ()));
			field.removeAttr ('selected').hide ();
		}
	};

	var showPicklistValues = function (buttonElement) {
		var button           = jQuery (buttonElement),
			picklistValueRow = button.closest ('.picklist-value'),
			visibleRoles     = picklistValueRow.find ('.visible-roles'),
			hiddenRoles      = picklistValueRow.find ('.hidden-roles'),
			roles            = hiddenRoles.find ('option:selected'),
			role, i, n;

		if (roles.length === 0) {
			return;
		}

		n = roles.length;
		for (i = 0; i < n; i += 1) {
			role = jQuery (roles [ i ]);
			visibleRoles.find ('option[value="' + role.val () + '"]').show ();
			role.remove ();
		}
	};

	var showUnmodifiableReasons = function (reasons) {
		var message, objectType, objectLabel, i;

		if ((reasons === null) || (reasons === undefined)) {
			return;
		}

		message = '';
		for (objectType in reasons) {
			if (!reasons.hasOwnProperty (objectType)) {
				continue;
			}

			switch (objectType) {
				case 'backgroundtasksfilters':
					objectLabel = 'Filtro de la tarea oculta';
					break;
				case 'backgroundtasksparameters':
					objectLabel = 'Parámetro de la tarea oculta';
					break;
				case 'calendarviews':
					objectLabel = 'Vista calendario';
					break;
				case 'charts':
					objectLabel = 'Gráfico';
					break;
				default:
					objectLabel = '';
					break;
			}

			for (i = 0; i < reasons [ objectType ].length; i += 1) {
				message += '+ ' + objectLabel + ' "' + reasons [ objectType ][ i ] + '"\n';
			}
		}

		if (message.trim () !== '') {
			alert ('El campo no puede ser eliminado, pues forma parte de:\n\n' + message);
		}
	};

	window.FieldPropertiesUtils = {
		addModuleReferenceFilter:          addModuleReferenceFilter,
		addModuleReferenceRelationship:    addModuleReferenceRelationship,
		addPicklistValue:                  addPicklistValue,
		addPipelineValue:                  addPipelineValue,
		deleteModuleReferenceRelationship: deleteModuleReferenceRelationship,
		deleteModuleReferenceFilter:       deleteModuleReferenceFilter,
		deletePicklistValue:               deletePicklistValue,
		deletePipelineValue:               deletePipelineValue,
		editFieldProperties:               editFieldProperties,
		hideDependencyFields:              hideDependencyFields,
		hidePicklistValues:                hidePicklistValues,
		removeHiddenDependencyFields:      removeHiddenDependencyFields,
		removeVisibleDependencyFields:     removeVisibleDependencyFields,
		saveProperties:                    saveProperties,
		searchCalculated:                  searchCalculated,
		setCalculatedSystem:               setCalculatedSystem,
		setDateValidationFields:           setDateValidationFields,
		setModuleReferenceFilterType:      setModuleReferenceFilterType,
		setModuleReferenceRelationships:   setModuleReferenceRelationships,
		setPicklistDependencyLabel:        setPicklistDependencyLabel,
		setPipelineDependencyLabel:        setPipelineDependencyLabel,
		showDependencyFields:              showDependencyFields,
		showPicklistValues:                showPicklistValues,
		showUnmodifiableReasons:           showUnmodifiableReasons
	};
} (jQuery));