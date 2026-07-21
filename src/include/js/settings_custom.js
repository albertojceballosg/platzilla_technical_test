/**
 * Settings javascript functions
 * @author Etienne Gómez (EGC)
 * @copyright Copyright (c) 2013, Timemanagement_
 * @version 1.0 17/11/2013 02:46:32
 * @filesource
 */
	/*[ TT11223 ] Migrar funcionalidad “Administrador de Módulos” a un módulo simple
	 * DM 26/07/2016
	*/
function updateBlockProperties(value, module) {
	if (value == "2") {
		fillRelModulesPickList(module);
		jQuery('#relmodule').parent().parent().show();
		jQuery('#relfieldname').parent().parent().show();
	}
	else {
		jQuery('#relmodule').parent().parent().hide();
		jQuery('#relfieldname').parent().parent().hide();
	}
	if (value == "1") {
		jQuery('#update_parentfield').parent().parent().show();
		jQuery('#oncomplete_value').parent().parent().show();
		jQuery('#onprogress_value').parent().parent().show();
	} else {
		jQuery('#update_parentfield').parent().parent().hide();
		jQuery('#oncomplete_value').parent().parent().hide();
		jQuery('#onprogress_value').parent().parent().hide();
	}
}

function fillRelModulesPickList(module) {
	var url = "index.php?module=gestion_module&action=ActivityAjax&funcion=REL_MODULES_4PICKLIST&formodule="+module;
	jQuery.get(url, {}, function (response) {
		jQuery('#relmodule').html(response);
	});
}

function fillRelFieldsModulePickList(module) {
	var url = "index.php?module=gestion_module&action=ActivityAjax&funcion=REL_FIELDS_MODULE_4PICKLIST&formodule="+module;
	jQuery.get(url, {}, function (response) {
		jQuery('#relfieldname').html(response);
	});
}
