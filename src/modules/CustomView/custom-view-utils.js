(function (jQuery) {
	// Private variables
	var availableColumnsData      = null,
		totalAdvancedFilterGroups = -1,
		totalColorFilterGroups    = -1;

	// Private functions
	var addFilter = function (buttonElement, templateSelector) {
		var colorPeriod,
			group              = jQuery (buttonElement).closest ('.filter-group'),
			groupId            = group.attr ('data-id'),
			filters            = group.find ('.filters'),
			filterId           = getFilterId (group),
			filterTemplateHtml = jQuery (templateSelector).html ().replace (/__GROUP_ID__/g, groupId).replace (/__FILTER_ID__/g, filterId),
			filterTemplate     = jQuery (filterTemplateHtml),
            selectedFrom       = (templateSelector === '#color-filter-template' ) ? 'color' : 'advanced';
		if (selectedFrom == 'color') {
            colorPeriod    = filterTemplate.find ('.color-filter-date');
            console.log(colorPeriod);
            colorPeriod.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
            colorPeriod.mask ("9999/99/99");
		}
		setFilterFields (filterTemplate.find ('.filter-field'), selectedFrom);
		filters.find ('.operator:last').removeClass ('hidden').removeAttr ('disabled');
		filters.append (filterTemplate);
	};

	var addFilterGroup = function (buttonElement, groupTemplateSelector, filterTemplateSelector, totalFilterGroups) {
		var colorPeriod,
			button              = jQuery (buttonElement),
			filterGroups        = button.closest ('.filters-container').find ('.filter-groups'),
			filterGroupTemplate = jQuery (jQuery (groupTemplateSelector).html ().replace (/__GROUP_ID__/g, totalFilterGroups)),
			filterTemplateHtml  = jQuery (filterTemplateSelector).html ().replace (/__GROUP_ID__/g, totalFilterGroups).replace (/__FILTER_ID__/g, -1),
			filterTemplate      = jQuery (filterTemplateHtml),
            selectedFrom        = (groupTemplateSelector === '#color-filter-group-template' ) ? 'color' : 'advanced';

        if (selectedFrom == 'color') {
            colorPeriod    = filterTemplate.find ('.color-filter-date');
            console.log(colorPeriod);
            colorPeriod.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
            colorPeriod.mask ("9999/99/99");
        }
		setFilterFields (filterTemplate.find ('.filter-field'), selectedFrom);
		filterGroupTemplate.find ('.filters').append (filterTemplate);
		filterGroups.find ('.filter-group-operator:last > .operator').removeClass ('hidden').removeAttr ('disabled');
		filterGroups.append (filterGroupTemplate);
		totalFilterGroups -= 1;
		return totalFilterGroups;
	};

	var getFilterId = function (filterGroup) {
		var filters  = filterGroup.find ('.filter'),
			n        = filters.length,
			filterId = 0,
			filter, i;
		if (n > 0) {
			for (i = 0; i < n; i += 1) {
				filter = jQuery (filters [ i ]);
				if (parseInt (filter.attr ('data-id')) < filterId) {
					filterId = parseInt (filter.attr ('data-id'));
				}
			}
		}
		return (filterId - 1);
	};

	var setFilterFields = function (filterFields, selectedFrom) {
		var option, dataType, columnName, dummy;
		filterFields.empty ().append ('<option></option>');
		if ((availableColumnsData === null) || (availableColumnsData === undefined) || (jQuery.isEmptyObject (availableColumnsData))) {
			return;
		}

		for (columnName in availableColumnsData) {
			if (!availableColumnsData.hasOwnProperty (columnName)) {
				continue;
			}

			dummy = columnName.split (':');
            if ((jQuery.inArray(dummy [4], ['D', 'DT','T']) !== -1) && selectedFrom === 'advanced') {
                continue
            }
			switch (dummy [ 4 ]) {
				case 'D':
                case 'DT':
                case 'T':
                    dataType = 'DATE';
                    break;
				case 'I':
				case 'N':
				case 'NN':
					dataType = 'NUMBER';
					break;
				default:
					dataType = 'TEXT';
					break;
			}
			option = jQuery ('<option></option>')
				.attr ('data-type', dataType)
				.val (columnName)
				.text (availableColumnsData [ columnName ]);
			filterFields.append (option);
		}
		if (selectedFrom === 'color') {
            var colorPeriod    = jQuery('.color-filter-date');
            colorPeriod.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
            colorPeriod.mask ("9999/99/99");
		}
	};

	// Public functions
	var addAdvancedFilter = function (buttonElement) {
		addFilter (buttonElement, '#advanced-filter-template');
	};

	var addColorFilter = function (buttonElement) {
		addFilter (buttonElement, '#color-filter-template');
	};

	var addAdvancedFilterGroup = function (buttonElement) {
		totalAdvancedFilterGroups = addFilterGroup (buttonElement, '#advanced-filter-group-template', '#advanced-filter-template', totalAdvancedFilterGroups);
	};

	var addColorFilterGroup = function (groupId) {
		totalColorFilterGroups = addFilterGroup (groupId, '#color-filter-group-template', '#color-filter-template', totalColorFilterGroups);
	};

	var deleteFilter = function (buttonElement) {
		var button = jQuery (buttonElement),
			group  = button.closest ('.filters'),
			filter = button.closest ('.filter');
		if (!confirm ('¿Estás seguro de borrar el filtro seleccionado?')) {
			return;
		}
		filter.remove ();
		group.find ('.operator:last').addClass ('hidden').attr ('disabled', 'disabled');
	};

	var deleteFilterGroup = function (buttonElement) {
		var group = jQuery (buttonElement).closest ('.filter-group-container');
		if (!confirm ('¿Estás seguro de borrar el grupo de filtros seleccionado?')) {
			return;
		}
		group.remove ();
		jQuery ('.filter-groups').find ('.filter-group-operator:last > .operator').addClass ('hidden').attr ('disabled', 'disabled');
	};

	var setFilterField = function (selectElement) {
		var filterField                 = jQuery (selectElement),
			selectedFilterField         = filterField.find ('option:selected'),
			selectedFilterFieldDataType = selectedFilterField.attr ('data-type'),
			selectedDataType            = selectedFilterFieldDataType ? selectedFilterFieldDataType : 'TEXT',
			comparator                  = filterField.closest ('.filter').find ('.comparator'),
			options                     = comparator.find ('option');

		options.each (function (index, optionElement) {
			var option   = jQuery (optionElement),
				dataType = option.attr ('data-type');
            if((selectedDataType === 'DATE') && (!dataType)) {
                option.prop('selected', false).hide();
            } else {
                if ((!selectedDataType) || (!dataType) || (selectedDataType === dataType)) {
                    option.show();
                } else {
                    option.prop('selected', false).hide();
                }
            }
		})
	};

	var validateForm = function (formElement) {
		var form = jQuery (formElement),
			field, value, columns, i, notEmpty, standardFilterColumn, standardFilterPeriod, standardFilterColumnValue, standardFilterPeriodValue;

		field = form.find ('#name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el nombre del filtro');
			field.focus ();
			return false;
		}

		columns = form.find ('.column');
		if (columns.length > 0) {
			notEmpty = false;
			for (i = 0; i < columns.length; i += 1) {
				field = jQuery (columns [ i ]);
				value = field.val ();
				if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
					notEmpty = true;
					break;
				}
			}
			if (!notEmpty) {
				alert ('Selecciona al menos una columna');
				jQuery (columns [0]).focus ();
				return false;
			}
		}

		standardFilterColumn = form.find ('#standard-filter-column');
		standardFilterColumnValue = standardFilterColumn.val ();
		standardFilterPeriod = form.find ('#standard-filter-period');
		standardFilterPeriodValue = standardFilterPeriod.val ();
		if (
			((standardFilterColumnValue === null) || (standardFilterColumnValue === undefined) || (standardFilterColumnValue.trim () === '')) &&
			((standardFilterPeriodValue !== null) && (standardFilterPeriodValue !== undefined) && (standardFilterPeriodValue.trim () !== ''))
		) {
			alert ('Selecciona la columna');
			standardFilterColumn.focus ();
			return false;
		} else if (
			((standardFilterColumnValue !== null) && (standardFilterColumnValue !== undefined) && (standardFilterColumnValue.trim () !== '')) &&
			((standardFilterPeriodValue === null) || (standardFilterPeriodValue === undefined) || (standardFilterPeriodValue.trim () === ''))
		) {
			alert ('Selecciona la duración');
			standardFilterPeriod.focus ();
			return false;
		} else if (standardFilterPeriodValue === 'custom') {
			field = form.find ('#standard-filter-start-date');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Selecciona la fecha de inicio');
				field.focus ();
				return false;
			}

			field = form.find ('#standard-filter-end-date');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Selecciona la fecha de fin');
				field.focus ();
				return false;
			}
		}

		return true;
	};

	var init = function (availableColumnsDataArgument) {
		var form           = jQuery ('form'),
			startDateField = form.find ('#standard-filter-start-date'),
			endDateField   = form.find ('#standard-filter-end-date'),
			colorPeriod    = form.find ('.color-filter-date');

		availableColumnsData = availableColumnsDataArgument;

		startDateField.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
		startDateField.mask ("9999/99/99");
		endDateField.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
		endDateField.mask ("9999/99/99");
        colorPeriod.datepicker ({ format: 'yyyy/mm/dd', language: 'es', weekStart: 1 });
        colorPeriod.mask ("9999/99/99");
	};

	var preventDuplicates = function (selectElement) {
		var select           = jQuery (selectElement),
			columnsSection   = select.closest ('.columns'),
			columns          = columnsSection.find ('.column'),
			processedColumns = [],
			column, i, value;

		for (i = 0; i < columns.length; i += 1) {
			column = jQuery (columns [ i ]);
			if (column.val () === '') {
				continue;
			}

			if (jQuery.inArray (column.val (), processedColumns) !== -1) {
				value = column.attr ('data-last-value');
				alert ('La columna ' + column.find ('option:selected').text () + ' ya ha sido seleccionada');
				column.val (value ? value : '').focus ();
				return;
			}
			processedColumns.push (column.val ());
		}
	};

	var setLastSelectedValue = function (selectElement) {
		var select = jQuery (selectElement);

		select.attr ('data-last-value', select.val ());
	};

	var selectGroup = function (event, obj) {
		var selectedGroup = jQuery(obj),
			idGroup       = jQuery ('#cv-group-id'),
			nameGroup     = jQuery ('#cv-group-name'),
			selectedId    = selectedGroup.attr('rel'),
			selectedName  = selectedGroup.html();
		if (selectedId === '') {
            nameGroup.val('');
			nameGroup.attr('readonly', false);
            idGroup.val('');
		} else {
            nameGroup.val(selectedName);
            nameGroup.attr('readonly', false);
            idGroup.val(selectedId);
		}
        event.preventDefault()
    };

	var setColorPeriod = function (selectElement) {
        var filterField                 = jQuery (selectElement),
			idContoller                 = filterField.attr('data-control'),
            selectedFilterField         = filterField.find ('option:selected'),
            selectedFilterFieldDataType = selectedFilterField.attr ('data-type'),
			stdDiv                      = jQuery('#color-filter-std' + idContoller),
			periodDiv                   = jQuery('#color-filter-period' + idContoller);
        setHelpToField (selectElement);
        if ((selectedFilterFieldDataType === 'DATE') && (selectedFilterField.val () === 'custom')) {
            stdDiv.hide();
            periodDiv.show();
        } else if ((selectedFilterFieldDataType === 'DATE') && (selectedFilterField.val () !== 'custom')) {
            stdDiv.hide();
            periodDiv.hide();
        } else {
            stdDiv.show ();
            periodDiv.hide();
        }
	};

    var setHelpToField = function (obj) {
        var elementRow       = '',
            selectedOperator = '',
			value            = '';
        selectedOperator = jQuery (obj).val ();
        elementRow       = jQuery (obj).parent ().parent ();
        value            = elementRow.find ('input').eq (0);
        if ((selectedOperator === 'e') || (selectedOperator === 'n')) {
            value.attr ('placeholder', 'Usar NULL para comparar  con un valor nulo o vacío');
        } else {
            value.attr ('placeholder', '');
		}
    };

	var setPeriod = function (selectElement) {
		var select         = jQuery (selectElement),
			type           = select.val (),
			form           = select.closest ('form'),
			startDateField = form.find ('#standard-filter-start-date'),
			endDateField   = form.find ('#standard-filter-end-date');

		if (type !== 'custom') {
			startDateField.prop ('disabled', true).closest ('.standard-filter-date').hide ();
			endDateField.prop ('disabled', true).closest ('.standard-filter-date').hide ();
		} else {
			startDateField.prop ('disabled', false).val ('').closest ('.standard-filter-date').show ();
			endDateField.prop ('disabled', false).val ('').closest ('.standard-filter-date').show ();
		}
	};

	var setStandardFilter = function (selectElement) {
		var select                = jQuery (selectElement),
			columnName            = select.val (),
			standardFilterSection = select.closest ('.standard-filter');

		if ((columnName === null) || (columnName === undefined) || (columnName.trim () === '')) {
			standardFilterSection.find ('#standard-filter-period').val ('');
			standardFilterSection.find ('#standard-filter-start-date').val ('');
			standardFilterSection.find ('#standard-filter-end-date').val ('');
		}
	};

	window.CustomViewUtils = {
		addAdvancedFilter:      addAdvancedFilter,
		addColorFilter:         addColorFilter,
		addAdvancedFilterGroup: addAdvancedFilterGroup,
		addColorFilterGroup:    addColorFilterGroup,
		deleteFilter:           deleteFilter,
		deleteFilterGroup:      deleteFilterGroup,
		init:                   init,
		preventDuplicates:      preventDuplicates,
		setFilterField:         setFilterField,
		setLastSelectedValue:   setLastSelectedValue,
		selectGroup:            selectGroup,
        setColorPeriod:         setColorPeriod,
        setHelpToField:         setHelpToField,
		setPeriod:              setPeriod,
		setStandardFilter:      setStandardFilter,
		validateForm:           validateForm
	};
} (jQuery));
