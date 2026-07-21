/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
function customFormValidate() {
	var ret = true;
	var fend = jQuery('#jscal_field_enddate').val();
	var fstart = jQuery('#jscal_field_inidate').val();
	
	if (fend.substring(2, 3) == '/' || fend.substring(2, 3) == '-') {
		strFechaEnd = fend.substring(6, 10) +'-'+fend.substring(3, 5)+'-'+fend.substring(0, 2);
		strFechaStart = fstart.substring(6, 10) +'-'+fstart.substring(3, 5)+'-'+fstart.substring(0, 2);
	} else {
		strFechaEnd = fend;
		strFechaStart = fstart;
	}
	
	if (strFechaEnd && strFechaStart && strFechaStart > strFechaEnd && ret) {
		alert('Fecha de inicio no puede ser mayor a la fecha de finalzacion');
		ret = false;
	}

	return ret;
}

function onchangeproyectosid() {
	jQuery.getJSON('index.php?module=hito&action=hitoAjax&file=DetailViewAjax&ajxaction=GET_PROJECT_DETAILS', {'proyectosid': jQuery('#proyectosid').val()}, function (response) {
		jQuery('#cliente').val(response.accountid);
		jQuery('#cliente_display').val(response.accountname);
		jQuery('#tipoproyecto').val(response.tipoproyecto);
	});
}