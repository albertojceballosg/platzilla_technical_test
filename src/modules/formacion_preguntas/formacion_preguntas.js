function customFormValidate() {
    console.log("entrando customFormValidate preguntas");

    var ponderacion = jQuery('#ponderacion').val();

    var pregunta = jQuery("[name='pregunta']").val();
    var tipo_de_pregunta = jQuery('#tipo_pregunta').val();

    if(isNaN(ponderacion)){
        alert('La ponderación debe ser un valor numérico');
        return false;
    }
    if(ponderacion<0){
        alert('el valor de la ponderación debe ser positiva');
        return false;
    }

    if(ponderacion.length>9){
        alert('La longitud del campo ponderación es muy larga');
        return false;
    }

    ///////////////////////////////////////////////////////////
    if(tipo_de_pregunta===null){
        alert('Debe seleccionar un tipo de pregunta');
        return false;
    }
    if(pregunta===''){
        alert('La pregunta no puede estar vacia');
        return false;
    }

    if(tipo_de_pregunta==='Multiple Choice' || tipo_de_pregunta==='Respuesta Multiple' ){
        if (jQuery('#respuesta_1').val()==='' || jQuery('#respuesta_1').val()==='undefined') {
            alert('Debes colocar al menos una opcion de respuesta');
            return false;
        }

        if(jQuery('#multiple_choice')==='undefined'){
            alert('Debe asociar el bloque de respuestas');
            return false;
        }

    }

    if(tipo_de_pregunta==='Verdadero/Falso' ){
        if (jQuery('#vf_respuesta_1').val()==='' ) {
            alert('Debes colocar al menos una opcion de respuesta');
            return false;
        }
        if(jQuery('#verdadero_falso')==='undefined'){
            alert('Debe asociar el bloque de respuestas');
            return false;
        }
    }
    return true;
}
