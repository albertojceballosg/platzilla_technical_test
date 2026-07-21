/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
function customFormValidate() {
        console.log("entrando customFormValidate");
        
        var nombre = jQuery('#proveedor_name').val();
        var telefono = jQuery('#telefono').val();
        var correo = jQuery('#email').val();            
        
        if (nombre == '' || nombre == 'undefined'){
            alert('Nombre'+alert_arr.CANNOT_BE_EMPTY)
            return false;
        }    

        if (telefono == '' || telefono == 'undefined'){
            alert('Teléfono'+alert_arr.CANNOT_BE_EMPTY)
            return false;
        }  

        if (correo == '' || correo == 'undefined'){
            alert('Correo'+alert_arr.CANNOT_BE_EMPTY)
            return false;
        }        
        
    return true;
}

//Validate Email repeat - proveedor Module

function duplicateEmailproveedor(oform){

console.log("entrando duplicateEmailproveedor");
    var result = true;
    var email = jQuery('#email').val();

    if(email != 'undefined' && email != ''){

        var url ="&record="+jQuery('input[name=record]').val()+"&email="+email;
        new Ajax.Request(
                        'index.php',
                        {queue: {position: 'end', scope: 'command'},
                            method: 'post',
                            postBody:"module=proveedor&action=proveedorAjax&ajax=true&file=EmailRepeated"+url,
                            onSuccess: function(response) {
                                console.log(response.responseText);
                                if(response.responseText  == 'email_repeat'){
                                    alert(alert_arr.EAMIL_REPEAT_proveedor);
                                    result = false;
                                    return result;

                                }else{
                                    oform.submit();
                                }
                            }
                    });

    }else{
        
        oform.submit();
    }

    return result;

}
