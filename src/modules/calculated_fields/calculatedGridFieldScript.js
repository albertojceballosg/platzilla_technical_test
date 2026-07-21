(function (jQuery) {
    // Private variables
    var labels = 'abcdefghijklmnopqrstuvwxyz';
    var idForm = 'formGridCalculatedField';
    // Private methods

    //Public methods
    var addCalculatedGroup = function() {
        var templateGroup = jQuery ('#grid-calculated-group').html(),
            row,
            label = labels[labelIndex++ % labels.length],
            elementReference;
        if (!templateGroup) {
            return;
        }
        row = jQuery(templateGroup);
        row.find ('span').eq(0).html('&nbsp;'+label+' = (&nbsp;')
        row.find ('.removeButton').removeClass('hide').attr('data-control',label);
        row.find ('.operandoValue').keydown(function (e) {
            if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                (e.keyCode >= 35 && e.keyCode <= 40 && e.keyCode == 188 )) {
                return;
            }
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        row.find ('select').eq(0).append (jQuery ('<option>', {
            value: 'r',
            text:  'CALCULO PREVIO'
        }));
        row.find ('select').eq(4).append (jQuery ('<option>', {
            value: 'r',
            text:  'CALCULO PREVIO'
        }));
        elementReference =  row.find('select').eq(2);
        for(var r= 0; r < calculatedFieldsGroups.length; r++ ){
            elementReference.append (jQuery ('<option>', {
                value: calculatedFieldsGroups[ r ],
                text:  calculatedFieldsGroups[ r ]
            }))
        }
        elementReference =  row.find('select').eq(6);
        for(var r= 0; r < calculatedFieldsGroups.length; r++ ){
            elementReference.append (jQuery ('<option>', {
                value: calculatedFieldsGroups[ r ],
                text:  calculatedFieldsGroups[ r ]
            }))
        }

        calculatedFieldsGroups.push(label);
        jQuery ('#grid-calculated-action').before(row);
        setEquation (idForm);


    };

    var removeCalculatedGroup = function (obj) {
        calculatedGroup = jQuery (obj).attr('data-control');
        jQuery (obj).parent().parent().parent().remove();
        for(var r= 0; r < calculatedFieldsGroups.length; r++ ){
            if(calculatedFieldsGroups[r] == calculatedGroup){
                calculatedFieldsGroups.splice(r,1)   ;
                break;
            }
        }
        setEquation (idForm);
    };

    var saveGridCalculatedField = function () {
        var form = jQuery ('#'+idForm),
            serialized;

        serialized = form.serialize ();
        jQuery.ajax ({
            cache:   false,
            data:    serialized,
            type:    "POST",
            url:     'index.php?module=calculated_fields&action=calculated_fieldsAjax&file=saveGridCalculatedfields&Ajax=true',
            success: function (message) {
               setEquation (idForm);
                jQuery ('#equation').append('<span class="help-block"style="color:#A94442">Campo de cáculo ha sido actualizado correctamente! </span>')
                jQuery ('html, body').animate({scrollTop:0}, 'slow');
            }
        })
    };
    
    var selectElement = function (obj) {
        group = jQuery(obj).parent().parent();
        postElement = jQuery(obj).attr('data-position');
        if(jQuery(obj).val() == 'c') {
            // cálculo con columnas
            group.find ('.'+postElement+'Element').removeClass('hide');
            group.find ('.'+postElement+'Value').addClass('hide');
            group.find ('.'+postElement+'Reference').addClass('hide');
            group.find ('select').eq(1).attr('required','required')
            group.find ('input').eq(0).removeAttr('required')
            group.find ('select').eq(2).removeAttr('required').val('')
        } else if(jQuery(obj).val() == 'v') {
            // cálculos con valor
            group.find ('.'+postElement+'Value').removeClass('hide');
            group.find ('.'+postElement+'Element').addClass('hide');
            group.find ('.'+postElement+'Reference').addClass('hide');
            group.find ('input').eq(0).attr('required','required');
            group.find ('select').eq(1).removeAttr('required').val('');
            group.find ('select').eq(2).removeAttr('required').val('')
        } else {
            //Cálculos con refrencias previas
            group.find ('.'+postElement+'Reference').removeClass('hide');
            group.find ('.'+postElement+'Element').addClass('hide');
            group.find ('.'+postElement+'Value').addClass('hide');
            group.find ('select').eq(2).attr('required','required');
            group.find ('select').eq(1).removeAttr('required').val('');
            group.find ('input').eq(0).removeAttr('required')

        }

    };

    var setEquation = function(id){
       var  form = jQuery('#'+id),
            operator,
            equationTxt = '';

        for(var r= 0; r < calculatedFieldsGroups.length; r++ ){
            operator = form.find('.update-equqtion').eq(r).val();
            equationTxt += calculatedFieldsGroups[r]+'&nbsp;'+operator+'&nbsp;'
        }
        jQuery('#equation').html('Cálculo = '+equationTxt)
    };

    window.calculatedGridFieldUtils = {
        addCalculatedGroup:      addCalculatedGroup,
        removeCalculatedGroup:   removeCalculatedGroup,
        saveGridCalculatedField: saveGridCalculatedField,
        selectElement:           selectElement,
        setEquation:             setEquation
    };

    jQuery('.operandoValue').keydown(function (e) {
        if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            (e.keyCode >= 35 && e.keyCode <= 40 && e.keyCode == 188 )) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
} (jQuery));