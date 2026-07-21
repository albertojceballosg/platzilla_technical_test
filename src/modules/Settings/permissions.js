(function (jQuery) {
	// Private variables
	var modal             = null,
		totalFilterGroups = -1;

	// Private functions
	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var getFilterId = function (filterGroup) {
		var filters  = filterGroup.find ('.filter'),
			filterId = 0,
			filter, i;
		if (filters.length > 0) {
			for (i = 0; i < filters.length; i += 1) {
				filter = jQuery (filters [ i ]);
				if (parseInt (filter.attr ('data-id')) < filterId) {
					filterId = parseInt (filter.attr ('data-id'));
				}
			}
		}
		return (filterId - 1);
	};

	// Public functions

	var addFilter = function (buttonElement) {
		var group              = jQuery (buttonElement).closest ('.filter-group'),
			groupId            = group.attr ('data-id'),
			filters            = group.find ('.filters'),
			filterId           = getFilterId (group),
			filterTemplateHtml = jQuery ('#permissions-filter-template').html ().replace (/__GROUP_ID__/g, groupId).replace (/__FILTER_ID__/g, filterId),
			filterTemplate     = jQuery (filterTemplateHtml);

		filters.find ('.operator:last').removeClass ('hidden').removeAttr ('disabled');
		filters.append (filterTemplate);
	};

	var addFilterGroup = function (groupId) {
		var filterGroups        = jQuery ('.filter-groups'),
			key                 = groupId ? groupId : totalFilterGroups,
			filterGroupTemplate = jQuery (jQuery ('#permissions-filter-group-template').html ().replace (/__GROUP_ID__/g, key)),
			filterTemplateHtml  = jQuery ('#permissions-filter-template').html ().replace (/__GROUP_ID__/g, key).replace (/__FILTER_ID__/g, -1),
			filterTemplate      = jQuery (filterTemplateHtml);

		filterGroupTemplate.find ('.filters').append (filterTemplate);
		filterGroups.find ('.filter-group-operator:last > .operator').removeClass ('hidden').removeAttr ('disabled');
		filterGroups.append (filterGroupTemplate);
		if (!groupId) {
			totalFilterGroups -= 1;
		}
	};

	var deleteFilter = function (buttonElement) {
		var button = jQuery (buttonElement),
			group  = button.closest ('.filter-group-container'),
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

	var openModal = function () {
		var modalTemplate = jQuery ('#permissions-modal-template');

		modal = jQuery (modalTemplate.html ());
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
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
			if ((!selectedDataType) || (!dataType) || (selectedDataType === dataType)) {
				option.show ();
			} else {
				option.prop ('selected', false).hide ();
			}
		})
	};

	var validateFilters = function (formElement) {
		var form = jQuery (formElement),
			field, value, groups, group, filters, filter, i, j, m, n;

		groups = form.find ('.filter-group-container');
		if (groups.length === 0) {
			return true;
		}

		n = groups.length;
		for (i = 0; i < n; i += 1) {
			group = jQuery (groups [ i ]);
			filters = group.find ('.filter');
			if (filters.length === 0) {
				alert ('El grupo de condiciones no puede estar vacío');
				return false;
			}

			m = filters.length;
			for (j = 0; j < m; j += 1) {
				filter = jQuery (filters [ j ]);

				field = filter.find ('.filter-field');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Selecciona el campo');
					field.focus ();
					return false;
				}

				field = filter.find ('.comparator');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Selecciona el operador de comparación');
					field.focus ();
					return false;
				}
			}
		}

		return true;
	};

	window.PermissionUtils = {
		addFilter:         addFilter,
		addFilterGroup:    addFilterGroup,
		deleteFilter:      deleteFilter,
		deleteFilterGroup: deleteFilterGroup,
		openModal:         openModal,
		setFilterField:    setFilterField,
		validateFilters:   validateFilters
	};
} (jQuery));
