/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
function customFormValidate() {
        console.log("entrando customFormValidate preguntas");
        
		
        var ponderacion = jQuery('#puntuacion').val();
		
		 /*var pregunta = jQuery("[name='img_curso']");
		  var file = pregunta.files[0];
		 alert(file.size);*/

        if(isNaN(ponderacion)){ 
			alert('La puntuación debe ser un valor numérico'); 
			return false;
		}
			if(ponderacion<0){ 
				alert('el valor de la puntuación debe ser positivo');
				return false;
			} 
			
			if(ponderacion.length>9){
				alert('La longitud del campo puntuación es muy larga');
				return false;
			}
      
    return true;
}