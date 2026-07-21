(function (jQuery) {
	var fieldHeaders = null,
		recordId     = null;
	var isEuropeanFloat = function (value) {
	    return /^[0-9]{1,3}(\.[0-9]{3})*(,[0-9]+)?$/.test(value);
	}

	var isAmericanFloat = function (value) {
	    return /^[0-9]{1,3}(,[0-9]{3})*(\.[0-9]+)?$/.test(value);
	}
	var checkRule = function (field, fieldName, fieldLabel, rule) {
		var minValue, maxValue, value, arguments, result;

		switch (rule.type) {
			case 'D': // Date range validation
				minValue = (rule.hasOwnProperty ('initialvalue')) && (!isNaN (Date.parse (rule.initialvalue))) ? new Date (Date.parse (rule.initialvalue)) : null;
				maxValue = (rule.hasOwnProperty ('maximumvalue')) && (!isNaN (Date.parse (rule.maximumvalue))) ? new Date (Date.parse (rule.maximumvalue)) : null;
				value = !isNaN (Date.parse (field.val ())) ? new Date (Date.parse (field.val ())) : null;
				if ((minValue !== null) && (value !== null) && (value < minValue)) {
					alert ('La fecha mínima de ' + fieldLabel + ' es: ' + rule.initialvalue);
					return false;
				} else if ((maxValue !== null) && (value !== null) && (value > maxValue)) {
					alert ('La fecha máxima de ' + fieldLabel + ' es: ' + rule.maximumvalue);
					return false;
				}
				break;
			case 'N': // Number range validation
				minValue = (rule.hasOwnProperty ('initialvalue')) && (!isNaN (rule.initialvalue)) ? parseFloat (rule.initialvalue) : null;
				maxValue = (rule.hasOwnProperty ('maximumvalue')) && (!isNaN (rule.maximumvalue)) ? parseFloat (rule.maximumvalue) : null;
				value = field.val ();
				if ((minValue !== null) && (parseFloat (value) < minValue)) {
					alert ('El valor mínimo de ' + fieldLabel + ' es: ' + minValue);
					return false;
				} else if ((maxValue !== null) && (parseFloat (value) > maxValue)) {
					alert ('El valor máximo de ' + fieldLabel + ' es: ' + maxValue);
					return false;
				}
				break;
			case 'U': // Record uniqueness validation
				value = field.val ();
				arguments = [
					'module=Settings',
					'action=SettingsAjax',
					'file=fieldValidationsAjax',
					'sub_mode=validationField',
					'ajax=true',
					'modulename=' + encodeURIComponent (rule.modulename),
					'tablename=' + encodeURIComponent (rule.tablename),
					'recordid=' + encodeURIComponent (recordId),
					'fieldname=' + encodeURIComponent (fieldName),
					'fieldValue=' + encodeURIComponent (value),
					'validationtype=' + rule.type
				];
				result = null;
				jQuery.ajax ('index.php', {
					async:    false,
					data:     arguments.join ('&'),
					dataType: 'text',
					method:   'get'
				}).done (function (response) {
					result = response;
				});
				if (result !== '') {
					alert (result);
					return false;
				}
				break;
		}
		return true;
	};

	var isFieldEmpty = function (container, fieldName) {
		var field  = container.find ('[name="' + fieldName + '"]'),
			uiType = fieldHeaders.hasOwnProperty (fieldName) ? fieldHeaders [ fieldName ].uitype : 1,
			value, isMultiple, isEmpty, dummy, i, choices;
		if (((field.is (':hidden')) && (uiType !== 8192)) || (jQuery.inArray (uiType, [ 4, 52, 70, 101, 258, 2202, 2204, 2206 ]) !== -1)) {
			return false;
		}

		switch (uiType) {
			case 16: // Global Picklist
			case 33: // Multi select
				isMultiple = field.prop ('multiple');
				if (isMultiple) {
					isEmpty = field.find ('option:selected:visible').length === 0;
				} else {
					isEmpty = (field.val ().trim ()) === '';
				}
				break;
			case 56: // Checkbox
				isEmpty = !field.prop ('checked');
				break;
			case 4096: // Attachments
				dummy = container.find ('#td_' + fieldName);
				if (dummy.is (':hidden')) {
					isEmpty = false;
				} else {
					isEmpty = dummy.find ('.attachments-container > .attachment').length === 0;
				}
				break;
			case 8192: //Pipeline
				dummy = field.closest ('.pipeline-container').find ('.pipeline-element');
				if ((!dummy) || (!dummy.hasOwnProperty ('length')) || (dummy.length === 0)) {
					isEmpty = true;
					break;
				}
				value = field.val ();
				if ((!value) || (value.trim () === '')) {
					isEmpty = true;
					break;
				}

				choices = [];
				for (i = 0; i < dummy.length; i += 1) {
					choices.push (jQuery (dummy [i]).data ('choice').trim ());
				}
				isEmpty = jQuery.inArray (value.trim (), choices) === -1;
				break;
			default:
				value = field.val ();
				if ((!value)) {
					isEmpty = true;
				} else {
					isEmpty = value.trim () === '';
				}
				break;
		}
		return isEmpty;
	};

	var isValidDateTime = function (value) {
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			return true;
		}
		
		// Obtener el formato de fecha del usuario
		var userFormat = (typeof gUserDateFormat !== 'undefined') ? gUserDateFormat : 'yyyy-mm-dd';
		var parts, year, month, day;
		
		// Determinar el separador según el formato
		var separator = (userFormat.indexOf('/') !== -1) ? '/' : '-';
		
		// Parsear según el formato del usuario (soporta - y /)
		if (userFormat === 'dd-mm-yyyy' || userFormat === 'dd/mm/yyyy') {
			parts = value.split(separator);
			if (parts.length !== 3) return false;
			day = parseInt(parts[0], 10);
			month = parseInt(parts[1], 10);
			year = parseInt(parts[2], 10);
		} else if (userFormat === 'mm-dd-yyyy' || userFormat === 'mm/dd/yyyy') {
			parts = value.split(separator);
			if (parts.length !== 3) return false;
			month = parseInt(parts[0], 10);
			day = parseInt(parts[1], 10);
			year = parseInt(parts[2], 10);
		} else { // yyyy-mm-dd o yyyy/mm/dd (formato por defecto)
			parts = value.split(separator);
			if (parts.length !== 3) return false;
			year = parseInt(parts[0], 10);
			month = parseInt(parts[1], 10);
			day = parseInt(parts[2], 10);
		}
		
		// Validar rangos
		if (isNaN(year) || isNaN(month) || isNaN(day)) return false;
		if (month < 1 || month > 12) return false;
		if (day < 1 || day > 31) return false;
		if (year < 1900 || year > 2100) return false;
		
		// Validar fecha válida
		var date = new Date(year, month - 1, day);
		return date.getFullYear() === year && date.getMonth() === (month - 1) && date.getDate() === day;
	};

	var isValidEmail = function (value) {
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			return true;
		} else {
			return /^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test (value);
		}
	};

	var isValidNumber = function (value, format) {
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			return true;
		}
		let cleanedValue = value.replace(/[^0-9,.-]/g, '');

		let number;
		if (format === 'EUROPEAN_FORMAT') {
			number = parseFloat(cleanedValue.replace(/\./g, '').replace(',', '.'));
		} else {
			number = parseFloat(cleanedValue.replace(/,/g, ''));
		}

		if (isNaN(number)) return false;
		let formattedValue;
		if (format === 'EUROPEAN_FORMAT') {
			formattedValue = number.toLocaleString('de-DE');
		} else {
			formattedValue = number.toLocaleString('en-US');
		}
		return formattedValue;
	};

	var isValidTime = function (value) {
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			return true;
		} else {
			return /^(?:1[0-2]|0[0-9]):[0-5][0-9]:[0-5][0-9]$/.test (value);
		}
	};

	var isValidUrlVideo = function (value) {
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            return true;
        } else {
            return /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test (value);
        }
	};

	var isValidFieldContentByType = function (container, fieldName) {
		var field  = container.find ('[name="' + fieldName + '"]'),
			uiType = fieldHeaders.hasOwnProperty (fieldName) ? fieldHeaders [ fieldName ].uitype : 1,
			value;
		if (field.is (':hidden')) {
			return true;
		}

		switch (uiType) {
			case 5: // Date
				value = field.val ();
				isValid = isValidDateTime (value);
				break;
			case 6: // DateTime
				value = field.val ();
				isValid = isValidDateTime (value);
				break;
			case 7:  // Number
			case 9:  // Percentage
			case 71: // Currency
				value = field.val ();
				var format = field.attr('data-number-format');
				isValid = isValidNumber (value, format);
				break;
			case 13: // Email
				value = field.val ();
				isValid = isValidEmail (value);
				break;
			case 14: // Email
				value = field.val ();
				isValid = isValidTime (value);
				break;
            case 5006: // Video field
                value = field.val ();
                isValid = isValidUrlVideo (value);
                break;
			default:
				isValid = true;
				break;
		}
		return isValid;
	};

	var isValidFieldContentByRules = function (container, fieldName, fieldLabel) {
		var field = container.find ('[name="' + fieldName + '"]'),
			rules = fieldHeaders.hasOwnProperty (fieldName) ? fieldHeaders [ fieldName ].validations : null,
			rule, i, value, isValid, minValue, maxValue;

		if ((rules === null) || (!jQuery.isArray (rules)) || (rules.length === 0)) {
			return true;
		}

		isValid = true;
		for (i = 0; i < rules.length; i += 1) {
			rule = rules [ i ];
			if (!checkRule (field, fieldName, fieldLabel, rules [ i ])) {
				isValid = false;
				break;
			}
		}
		return isValid;
	};

	var init = function (data, record) {
		fieldHeaders = data;
		recordId = record;
	};

	var validateForm = function (formElement) {
		var form = jQuery (formElement),
			field, fieldName, isMandatory, fieldLabel, result;

		for (fieldName in fieldHeaders) {
			if (!fieldHeaders.hasOwnProperty (fieldName)) {
				continue;
			}

			field = form.find ('#' + fieldName);
			isMandatory = fieldHeaders [ fieldName ].ismandatory;
			fieldLabel = fieldHeaders [ fieldName ].label;
			if ((isMandatory) && (isFieldEmpty (form, fieldName))) {
				alert ('El campo ' + fieldLabel + ' no puede estar vacío');
				if (field) {
					field.focus ();
				}
				return false;
			} else if (!isValidFieldContentByType (form, fieldName)) {
				alert ('El valor del campo ' + fieldLabel + ' no es válido');
				if (field) {
					field.focus ();
				}
				return false;
			} else if (!isValidFieldContentByRules (form, fieldName, fieldLabel)) {
				if (field) {
					field.focus ();
				}
				return false;
			}
		}
		if ((typeof (customFormValidate) === 'function') && (!customFormValidate ())) {
			return false;
		} else if ((typeof (gridFormValidate) === 'function') && (!gridFormValidate ())) {
			return false;
		}

		return true;
	};

	window.FieldValidationUtils = {
		init:         init,
		validateForm: validateForm
	};
} (jQuery));