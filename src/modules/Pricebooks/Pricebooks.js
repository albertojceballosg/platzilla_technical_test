(function (jQuery) {
	var totalConditionGroups = -1;

	var getConditionId = function (conditionGroup) {
		var conditions  = conditionGroup.find ('.condition'),
			n           = conditions.length,
			conditionId = 0,
			condition, i;
		if (n > 0) {
			for (i = 0; i < n; i += 1) {
				condition = jQuery (conditions [ i ]);
				if (parseInt (condition.attr ('data-id')) < conditionId) {
					conditionId = parseInt (condition.attr ('data-id'));
				}
			}
		}
		return (conditionId - 1);
	};

	var addCondition = function (buttonElement) {
		var conditionGroup    = jQuery (buttonElement).closest ('.condition-group'),
			conditionGroupId  = conditionGroup.attr ('data-id'),
			conditions        = conditionGroup.find ('.conditions'),
			conditionId       = getConditionId (conditionGroup),
			conditionTemplate = jQuery ('#condition-template').html ().replace (/__GROUP_ID__/g, conditionGroupId).replace (/__CONDITION_ID__/g, conditionId);
		conditions.find ('.operator:last').removeClass ('hidden').removeAttr ('disabled');
		conditions.append (conditionTemplate);
	};

	var addConditionGroup = function () {
		var conditionGroups        = jQuery ('.condition-groups'),
			conditionGroupTemplate = jQuery (jQuery ('#condition-group-template').html ().replace (/__GROUP_ID__/g, totalConditionGroups)),
			conditionTemplate      = jQuery ('#condition-template').html ().replace (/__GROUP_ID__/g, totalConditionGroups).replace (/__CONDITION_ID__/g, -1);
		conditionGroupTemplate.find ('.conditions').append (conditionTemplate);
		conditionGroups.find ('.condition-group-operator:last > .operator').removeClass ('hidden').removeAttr ('disabled');
		conditionGroups.append (conditionGroupTemplate);
		totalConditionGroups -= 1;
	};

	var deleteCondition = function (buttonElement) {
		var button         = jQuery (buttonElement),
			conditionGroup = button.closest ('.condition-group'),
			condition      = button.closest ('.condition');
		if (!confirm ('¿Estás seguro de borrar la condición seleccionada?')) {
			return;
		}
		condition.remove ();
		conditionGroup.find ('.operator:last').addClass ('hidden').attr ('disabled', 'disabled');
	};

	var deleteConditionGroup = function (buttonElement) {
		var conditionGroup = jQuery (buttonElement).closest ('.condition-group');
		if (!confirm ('¿Estás seguro de borrar el grupo de condiciones seleccionado?')) {
			return;
		}
		conditionGroup.next ('.condition-group-operator').remove ();
		conditionGroup.remove ();
		jQuery ('.condition-groups').find ('.condition-group-operator:last > .operator').addClass ('hidden').attr ('disabled', 'disabled');
	};

	var setVariableType = function (selectElement) {
		var select         = jQuery (selectElement),
			selectedOption = select.find ('option:selected'),
			value          = selectedOption.val (),
			type;

		if ((selectedOption.length === 0) || (value === undefined) || (value === null) || (value.trim () === '')) {
			type = '';
		} else {
			type = selectedOption.closest ('optgroup').attr ('data-type');
		}

		select.closest ('.variable-cell').find ('.variable-type').val (type);
	};

	var validateForm = function (formElement) {
		var form = jQuery (formElement),
			field, value, i, j, conditionGroups, conditionGroup, conditions, condition;

		field = form.find ('#pricebookname');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el nombre');
			field.focus ();
			return false;
		}

		field = form.find ('#multiplier');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el multiplicador');
			field.focus ();
			return false;
		} else if ((!jQuery.isNumeric (value)) || (value < 0)) {
			alert ('Introduce un multiplicador válido');
			field.focus ();
			return false;
		}

		conditionGroups = form.find ('.condition-groups');
		if (conditionGroups.length > 0) {
			for (i = 0; i < conditionGroups.length; i += 1) {
				conditionGroup = jQuery (conditionGroups [ i ]);
				conditions = conditionGroup.find ('.conditions > .condition');
				for (j = 0; j < conditions.length; j += 1) {
					condition = jQuery (conditions [ j ]);

					field = condition.find ('.variable-name');
					value = field.val ();
					if ((value === undefined) || (value === null) || (value.trim () === '')) {
						alert ('Debes seleccionar la variable de la condición');
						field.focus ();
						return false;
					}

					field = condition.find ('.variable-type');
					value = field.val ();
					if ((value === undefined) || (value === null) || (value.trim () === '')) {
						alert ('Debes seleccionar el tipo de variable de la condición');
						field.focus ();
						return false;
					}

					field = condition.find ('.comparator');
					value = field.val ();
					if ((value === undefined) || (value === null) || (value.trim () === '')) {
						alert ('Debes seleccionar el operador de comparación de la condición');
						field.focus ();
						return false;
					}

					field = condition.find ('.operator');
					value = field.val ();
					if ((j < (conditions.length - 1)) && ((value === undefined) || (value === null) || (value.trim () === ''))) {
						alert ('Debes seleccionar el operador entre condiciones');
						field.focus ();
						return false;
					}
				}

				field = conditionGroup.next ('.condition-group-operator').find ('.operator');
				value = field.val ();
				if ((i < (conditionGroups.length - 1)) && ((value === undefined) || (value === null) || (value.trim () === ''))) {
					alert ('Debes seleccionar el operador entre grupos de condiciones');
					field.focus ();
					return false;
				}
			}
		}
		return true;
	};

	window.PricebookUtils = {
		addCondition:         addCondition,
		addConditionGroup:    addConditionGroup,
		deleteCondition:      deleteCondition,
		deleteConditionGroup: deleteConditionGroup,
		setVariableType:      setVariableType,
		validateForm:         validateForm
	};
} (jQuery));
