(function (jQuery) {
	var container    = null,
		dependencies = null;

	var handleChangeByEvent = function (evt) {
		var fieldSelector = '#' + evt.currentTarget.id;
		handleChange (fieldSelector);
	};

	var handleChange = function (fieldSelector) {
		var sourceField      = container.find (fieldSelector),
			sourceFieldName  = sourceField.attr ('name'),
			sourceFieldValue = sourceField.val () !== null ? sourceField.val () : '',
			blocks, block, i, targetFields, targetFieldName, targetFieldContainer,
			dataFieldType    = '';
		if (
			(dependencies.hasOwnProperty (sourceFieldName)) &&
			(dependencies [ sourceFieldName ].hasOwnProperty (sourceFieldValue))
		) {
			targetFields = dependencies [ sourceFieldName ][ sourceFieldValue ];
			for (targetFieldName in targetFields) {
				if (!targetFields.hasOwnProperty (targetFieldName)) {
					continue;
				}

				targetFieldContainer = container.find ('#td_' + targetFieldName);
				dataFieldType = targetFieldContainer.attr ('data-field-type');
				if (targetFields [ targetFieldName ] === 0) {
					if (dataFieldType === 'Grid') {
						targetFieldContainer.parent ().parent ().parent ().parent ().removeClass ('hidden').show ();
					} else {
						targetFieldContainer.removeClass ('hidden').show ();
						targetFieldContainer.parent ().show ();
					}
					if (dataFieldType === 'TABLE-FIELD') {
						targetFieldContainer.parent ().parent ().parent ().removeClass ('hidden').show ();
					} else {
						targetFieldContainer.removeClass ('hidden').show ();
						targetFieldContainer.parent ().show ();
					}
				} else {
					if (dataFieldType === 'Grid') {
						targetFieldContainer.parent ().parent ().parent ().parent ().addClass ('hidden').trigger ('change').hide ();
					} else {
						targetFieldContainer.addClass ('hidden').trigger ('change').hide ();
						targetFieldContainer.parent ().hide ();
					}
					if (dataFieldType === 'TABLE-FIELD') {
						targetFieldContainer.parent ().parent ().parent ().addClass ('hidden').trigger ('change').hide ();
					} else {
						targetFieldContainer.addClass ('hidden').trigger ('change').hide ();
						targetFieldContainer.parent ().hide ();
					}
				}
			}
		}

		blocks = jQuery ('.block-container');
		if (blocks.length === 0) {
			return;
		}

		for (i = 0; i < blocks.length; i += 1) {
			block = jQuery (blocks [ i ]);
			if (block.find ('.field-container.hidden').length === block.find ('.field-container').length) {
				block.hide ();
			} else {
				block.show ();
			}
		}
	};

	var init = function (containerSelector, data) {
		var sourceFieldName, sourceFieldSelector, sourceFieldNames, sourceField, targetFields, targetField;

		dependencies = data;
		if (dependencies === null) {
			return;
		}

		container = jQuery (containerSelector);
		if (container.length === 0) {
			return;
		}

		for (sourceFieldName in dependencies) {
			sourceFieldSelector = '#' + sourceFieldName;
			FieldDependenciesUtils.handleChange (sourceFieldSelector);
			container.find (sourceFieldSelector).on ('change', handleChangeByEvent);
		}
	};

	window.FieldDependenciesUtils = {
		init:         init,
		handleChange: handleChange
	};
} (jQuery));
