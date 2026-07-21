var moduleobj = {
	modulename: 'Settings',
	modstrings: {},
	appstrings: {},

	/*
	 * Funcion para validar pasos en formulario de Crear Plataforma Paso 1
	 */
	validaPaso: function (form) {
		var valida = true;
		jQuery (form).find ('[required_type]').each (
			function () {
				if (!moduleobj.validate (this, this.attributes.required_type.value, this.attributes.field_label.value)) {
					return valida = false;
				}
			}
		);
		return valida;
	},

	validate: function (obj, type, field_label) {
		switch (type) {
			case 'text':
				if (obj.value == '') {
					this.showErrorMessage (this.modstrings[ field_label ] + " " + this.modstrings.LBL_MANDATORY_FIELD, 2);
					return false;
				}
				break;
			case 'textnowhitespaces':
				if (obj.value == '' || obj.value.indexOf (' ') >= 0) {
					this.showErrorMessage (this.modstrings[ field_label ] + " " + this.modstrings.LBL_MANDATORY_FIELD, 2);
					return false;
				}
				break;
			case 'select':
				if (obj.value == '') {
					if (!obj.attributes.dependency || jQuery ("input:radio[name=" + obj.attributes.dependency + "]:checked").val () == 0) {
						this.showErrorMessage (this.modstrings[ field_label ] + " " + this.modstrings.LBL_MANDATORY_FIELD, 2);
						return false;
					}
				}
				break;
			case 'email':
				if (!obj.value) {
					this.showErrorMessage (this.modstrings[ field_label ] + " " + this.modstrings.LBL_MANDATORY_FIELD, 2);
					return false;
				}
				break;
			default:
				// Unexpected. Ignored
				break;
		}
		return true;
	},

	validateemail: function (str) {
		var re = /^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$/;
		return re.exec (str);
	},

	showErrorMessage: function (msg, time) {
		time = (time * 1000);
		jQuery ('#inshowErrorMessage').html (msg);
		jQuery ('#showErrorMessage').fadeIn ();
		setTimeout ("jQuery('#showErrorMessage').fadeOut();", time)
	},

	/*
	 * Funcion de echo en consola
	 */
	echo: function (str) {
		console.log (str);
	},

	enableDisbleApp: function (ed) {
		if (ed == 1) {
			jQuery ('#instanceapps').prop ('disabled', true);
		} else if (ed == 0) {
			jQuery ('#instanceapps').prop ('disabled', false);
		}
	}
};

function editVariable (el) {
	jQuery ('#value').val (jQuery (el).attr ('data-value'));
	jQuery ('#varname').val (jQuery (el).attr ('data-name'));
	jQuery ('#variableid').val (jQuery (el).attr ('data-id'));
	jQuery ('#tabid').val (jQuery (el).attr ('data-module-id'));
	abreidUI ('editVarUI');
}

function deleteVariable (el) {
	if (!confirm ('Confirma que desea eliminar esta variable?')) {
		return;
	}
	jQuery.get (
		'index.php?module=Settings&action=SettingsAjax&file=ActivityAjax&funcion=DEL_VAR&variableid=' + jQuery (el).attr ('data-id') + '&parenttab=Settings',
		function () {
			jQuery (el.parentNode.parentNode).remove ();
		}
	);
}

function addVariable (formodule) {
	jQuery ('#value').val ('');
	jQuery ('#varname').val ('');
	jQuery ('#variableid').val ('');
	if (formodule == '') {
		jQuery ('#tabid').val ('');
	}
	abreidUI ('editVarUI');
}

function validField (id) {
	var idSelector = jQuery ('#' + id),
		str;
	idSelector.val (idSelector.val ().toLowerCase ());
	idSelector.val (idSelector.val ().replace (' ', '_'));
	str = idSelector.val ();

	// remove accents, swap ñ for n, etc
	var from = "àáäâèéëêìíïîòóöôùúüûñç·/-,:;";
	var to = "aaaaeeeeiiiioooouuuunc______";
	for (var i = 0, l = from.length; i < l; i++) {
		str = str.replace (new RegExp (from.charAt (i), 'g'), to.charAt (i));
	}

	str = str.replace (/[^a-z0-9 _]/g, '') // remove invalid chars
			 .replace (/\s+/g, '_') // collapse whitespace and replace by -
			 .replace (/-+/g, '_'); // collapse dashes

	idSelector.val (str);
}

function activaMensaje () {
	document.getElementById ('formid').style.display = 'none';
	document.getElementById ('mensaje').style.display = 'block';
}

function validateText (str) {
	var exp_reg = /^[a-z\d\u00C0-\u00ff]+$/i; // expresión regular para letras(máy o minus), acentuadas o no, y números
	return exp_reg.test (str);

}

//Valida campos configuración de aplicación
function validateConfigApps (id) {
	var appCode,
		appName,
		appPrice,
		nestableOutput;
	if (id == 0) {
		appCode = jQuery ('#app_code');
		if (appCode.val () == 'undefined' || appCode.val () == '') {
			alert (jQuery ('#label_app_code').text () + ' ' + alert_arr.CANNOT_BE_EMPTY);
			return false;
		}

		appName = jQuery ('#app_name');
		if (appName.val () == 'undefined' || appName.val () == '') {
			alert (jQuery ('#label_app_name').text () + ' ' + alert_arr.CANNOT_BE_EMPTY);
			return false;
		} else if (!validateText (appName.val ())) {
			alert (jQuery ('#label_app_name').text () + ' ' + alert_arr.INVALID);
			return false;
		}

		appPrice = jQuery ('#app_price');
		if (appPrice.val () == 'undefined' || appPrice.val () == '') {
			alert (jQuery ('#label_app_price').text () + ' ' + alert_arr.CANNOT_BE_EMPTY);
			return false;
		}

		nestableOutput = jQuery ('#nestable-output');
		if (nestableOutput.val () == 'undefined' || nestableOutput.val () == '' || nestableOutput.val () == '[]') {
			alert (alert_arr.CONFIG_APPS_LISTMODULE + ' ' + alert_arr.CANNOT_BE_EMPTY);
			return false;
		}
	} else {
		appCode = jQuery ('#app_code_' + id);
		if (appCode.val () == 'undefined' || appCode.val () == '') {
			alert (jQuery ('#label_app_code').text () + ' ' + alert_arr.CANNOT_BE_EMPTY);
			return false;
		}

		appName = jQuery ('#app_name_' + id);
		if (appName.val () == 'undefined' || appName.val () == '') {
			alert (jQuery ('#label_app_name').text () + ' ' + alert_arr.CANNOT_BE_EMPTY);
			return false;
		} else if (!validateText (appName.val ())) {
			alert (jQuery ('#label_app_name').text () + ' ' + alert_arr.INVALID);
			return false;
		}

		nestableOutput = jQuery ('#nestable-output_' + id);
		if (nestableOutput.val () == 'undefined' || nestableOutput.val () == '' || nestableOutput.val () == '[]') {
			alert (alert_arr.CONFIG_APPS_LISTMODULE + ' ' + alert_arr.CANNOT_BE_EMPTY);
			return false;
		}
	}
	return true;
}

function validateDecimal (obj) {
	var RE       = /^\d*\.?\d{0,2}$/,
		selector = jQuery ('#' + obj),
		value    = selector.val ();
	if (RE.test (value)) {
		return true;
	} else {
		selector.val (value.substring (0, (value.length - 1)));
		return false;
	}
}

function hideInfo ($id) {
	var userInfo;
	if ($id != 6) {
		jQuery ("#tab-1").hide ();
		userInfo = jQuery ("#div-userinfo");
		if (userInfo.css ('display') != 'none') {
			userInfo.hide ()
		} else if (jQuery ("#div-imgusr").css ('display') != 'none') {
			jQuery ("#div-imgusr").hide ()
		} else if (jQuery ("#div-info").css ('display') != 'none') {
			jQuery ("#div-info").hide ()
		} else if (jQuery ("#div-group").css ('display') != 'none') {
			jQuery ("#div-group").hide ()
		} else if (jQuery ("#div-currency").css ('display') != 'none') {
			jQuery ("#div-currency").hide ()
		} else if (jQuery ("#div-advanced").css ('display') != 'none') {
			jQuery ("#div-advanced").hide ()
		} else if (jQuery ("#div-history").css ('display') != 'none') {
			jQuery ("#div-history").hide ()
		}
	} else {
		jQuery ("#tab-1").show ()
	}
}

function showInfo (value, count, link, label) {
	if (value == 'Datos de Usuario') {
		jQuery ('#tab-' + count).hide ();
		jQuery (link).show ();
		jQuery (link).find ('.main-box-body').html (label);
	}
}

function showSubBlock (blockId) {
	jQuery ('.sub-block').hide ();
	jQuery (blockId).show ();
}

function showDivMenu (link, idDiv, label) {
	jQuery ('#' + idDiv).hide ();
	jQuery (link).show ();
	jQuery (link).find ('.main-box-body').html (label);
}

function fetchGroups_js (id, div) {
	var selector = jQuery ('#' + div);
	if (selector.css ('display') != 'none') {
		selector.fadeIn ();
	} else {
		fetchUserGroups (id, div);
	}
}

function fetchUserGroups (id, div) {
	new Ajax.Request (
		'index.php',
		{
			queue:      { position: 'end', scope: 'command' },
			method:     'post',
			postBody:   'module=Users&action=UsersAjax&file=UserGroups&ajax=true&record=' + id,
			onComplete: function (response) {
				jQuery ('#' + div).show ().html (response.responseText);
			}
		}
	);
}

function labelCopyFieldValue (id_out, id) {
	var idOut = jQuery ('#' + id_out),
		str;
	idOut.val (jQuery ('#' + id).val ());

	//Replace Uppercase and spaces
	idOut.val (idOut.val ().toLowerCase ());
	idOut.val (idOut.val ().replace (' ', '_'));

	str = idOut.val ();

	var length = 30;

	//Remove accents, swap ñ for n, etc
	var from = "àáäâèéëêìíïîòóöôùúüûñç·/-,:;";
	var to = "aaaaeeeeiiiioooouuuunc______";
	for (var i = 0, l = from.length; i < l; i++) {
		str = str.replace (new RegExp (from.charAt (i), 'g'), to.charAt (i));
	}

	str = str.replace (/[^a-z0-9 _]/g, '') // remove invalid chars
			 .replace (/\s+/g, '_') // collapse whitespace and replace by -
			 .replace (/-+/g, '_'); // collapse dashes

	//Truncates the value of the maximum field size
	idOut.val (str.substring (0, length));
}

function convertirCustomized (module, val) {
	new Ajax.Request (
		'index.php',
		{
			queue:      { position: 'end', scope: 'command' },
			method:     'post',
			postBody:   'module=Settings&action=SettingsAjax&setting_action=convertirCustomized&valToModify=' + val + '&moduleToModify=' + module,
			onComplete: function (response) {
				var resultado = response.responseText;
				if (resultado == 1) {
					alert ('El módulo se ha actualizado correctamente');
					location.reload ();
				} else {
					alert ('No ha sido posible actualizadar el módulo');
				}
			}
		}
	);
}
