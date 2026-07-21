/**
 * Settings javascript functions
 * @author Etienne Gómez (EGC)
 * @copyright Copyright (c) 2013, Timemanagement_
 * @version 1.0 17/11/2013 02:46:32
 * @filesource
 */
function fillRelFieldsModulePickList(module) {
	var url = "index.php?module=Settings&action=ActivityAjax&funcion=REL_FIELDS_MODULE_4PICKLIST&formodule="+module;
	jQuery.get(url, {}, function (response) {
		jQuery('#relfieldname').html(response);
	});
}
