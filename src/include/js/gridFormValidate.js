/*********************************************************************************

** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

 /*
 * [ TT11273 ] Ajustes Modales Editor Disposición: Campos Grid/Matriz - Parte2
 * JR 15/08/2016
 * $field.length verifica si el selector esta disponible
 * $field.parent().is(':visible') verifica si el field no esta oculto para validar
 */

function gridFormValidate(){
	
	var all_ok=true;
	var startTime = performance.now();
	var maxIterations = 1000; // Límite de seguridad para evitar bucles infinitos
	var iterationCount = 0;

	// Cache de selectores para mejor rendimiento
	var $gridRows = jQuery(".gridvalidationtr");
	var totalRows = $gridRows.length;
	
	// Optimización: si no hay filas, salir temprano
	if (totalRows <= 1) {
		return true;
	}

	$gridRows.each(function(i, obj){
		iterationCount++;
		if (iterationCount > maxIterations) {
			console.warn('gridFormValidate: Demasiadas iteraciones, deteniendo para evitar bucle infinito');
			return false; // Detener el bucle
		}
		
		if(i>0){
			var $row = jQuery(this);
			
			// Optimización: buscar todos los campos a la vez
			var $percentFields = $row.find('.percentvalidate');
			var $numericFields = $row.find('.numericvalidate');
			var $currencyFields = $row.find('.currencyvalidate');
			var $urlFields = $row.find('.urlvalidate');
			
			// Validación porcentual
			if($percentFields.length > 0 && $percentFields.parent().is(':visible') && $percentFields.val()!=''){
				if(!validateAmount($percentFields.val()) || !formatDecimal($percentFields.val(),'.') || !validatePercentageCustom($percentFields.val())){
					alert('Valor de campo tipo porcentual inválido');
					$percentFields.focus();
					all_ok=false;
					return false;					
				}
			}

			// Validación numérica
			if($numericFields.length > 0 && $numericFields.parent().is(':visible') && $numericFields.val()!=''){
				if(!validateAmount($numericFields.val()) || !formatDecimal($numericFields.val(),'.') ){
					alert('Valor de campo tipo numérico inválido');
					$numericFields.focus();
					all_ok=false;
					return false;					
				}
			}

			// Validación de moneda
			if($currencyFields.length > 0 && $currencyFields.parent().is(':visible') && $currencyFields.val()!=''){
				if(!validateAmount($currencyFields.val()) || !formatDecimal($currencyFields.val(),'.') ){
					alert('Valor de campo tipo moneda inválido');
					$currencyFields.focus();
					all_ok=false;
					return false;					
				}
			}

			// Validación URL
			if($urlFields.length > 0 && $urlFields.parent().is(':visible') && $urlFields.val()!=''){
				if(!validateurlcustom($urlFields.val())){
					alert('Valor de campo tipo url inválido');
					$urlFields.focus();
					all_ok=false;
					return false;					
				}
			}

		}		
	});
	
	var endTime = performance.now();
	if (endTime - startTime > 500) { // Reducir umbral a 500ms para warning
		console.warn('gridFormValidate tomó ' + (endTime - startTime).toFixed(2) + ' ms para ' + iterationCount + ' iteraciones');
	}

	if(all_ok === true){
	    return true;
	}else{
		return false;
	}
}