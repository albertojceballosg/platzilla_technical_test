<div id="td_{$fieldname}" class="row field-container" data-field-type="Grid">
    {if  $swDetailView}
        {assign var="hideBlock" value=false}
        {foreach from=$dataValues item=row}
            {foreach from= $row item=itemValue}
                {if ! empty($itemValue)}
                    {$hideBlock = false}
                {/if}
            {/foreach}
        {/foreach}
    {else}
        {assign var="hideBlock" value=false}
    {/if}
    <div class=" col-md-12" style="margin: 0px">
        {if ! $swDetailView }
            <!-- {$fieldlabel} -->
            <div class="pull-right" style="padding: 0px 12px 6px 12px; margin: -40px 12px 0px 12px">
                <input name="addBtnRow{$fieldname}" onclick="addRow{$fieldname}(); searchCheckedField('{$fieldname}') "
                    class="btn btn-success btn-sm" value="Agregar Fila" type="button">
            </div>
        {/if}
    </div>
    <div class="col-md-12">
        <div class="main-box-body clearfix">
            <div class="table-responsive">
                <script language="JavaScript">
                    // Función para convertir número del formato del usuario al formato numérico (para cálculos)
                    function parseGridNumber(value) {
                        if (value === null || value === undefined || value === '') return 0;

                        // Si ya es un número, devolverlo directamente
                        if (typeof value === 'number') return isNaN(value) ? 0 : value;

                        var strValue = String(value).trim();
                        if (strValue === '') return 0;

                        var userFormat = (typeof gUserNumberFormat !== 'undefined') ? gUserNumberFormat :
                            'AMERICAN_FORMAT';

                        if (userFormat === 'EUROPEAN_FORMAT') {
                            // Formato europeo: 1.234,56 -> 1234.56
                            // Detectar si tiene coma (separador decimal europeo)
                            if (strValue.indexOf(',') !== -1) {
                                // Tiene coma, es formato europeo
                                strValue = strValue.replace(/\./g, ''); // Quitar separadores de miles
                                strValue = strValue.replace(',', '.'); // Cambiar coma decimal por punto
                            }
                            // Si no tiene coma, asumir que ya está en formato numérico (ej: de BD)
                        } else {
                            // Formato americano: 1,234.56 -> 1234.56
                            strValue = strValue.replace(/,/g, ''); // Quitar separadores de miles
                        }

                        var result = parseFloat(strValue);
                        return isNaN(result) ? 0 : result;
                    }

                    // Función para formatear número del formato numérico al formato del usuario (para mostrar)
                    function formatGridNumberForDisplay(value, decimals) {literal}{
                        if (value === null || value === undefined || value === '') return '';

                        var numValue = (typeof value === 'number') ? value : parseFloat(value);
                        if (isNaN(numValue)) return '';

                        decimals = (typeof decimals !== 'undefined') ? decimals : 2;
                        var userFormat = (typeof gUserNumberFormat !== 'undefined') ? gUserNumberFormat : 'AMERICAN_FORMAT';

                        // Formatear con separadores de miles y decimales según el formato del usuario
                        if (userFormat === 'EUROPEAN_FORMAT') {
                            // Formato europeo: 1234567.89 -> 1.234.567,89
                            return numValue.toLocaleString('de-DE', {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
                        } else {
                            // Formato americano: 1234567.89 -> 1,234,567.89
                            return numValue.toLocaleString('en-US', {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
                        }
                    }{/literal}

                    var iNumRows_{$fieldname} = -1;
                    var numrow_{$fieldname} = 2;
                    {assign var="summaryClass" value=""}
                    {assign var="calculatedClass" value=""}
                    {assign var="numrow" value= 0}
                    {section name=key loop=$lstSubCampos}
                        {if $lstSubCampos[key].uitype eq 2203 }
                            {assign var="hasTFoot" value=1}
                            {assign var="summaryClass" value=" tr_{$fieldname}_summary"}
                            {assign var="summaryName" value={$lstSubCampos[key].name}}

                            function getSummary_{$fieldname} (objThis) {literal}{
                                var objSummary = {/literal}{$lstSubCampos[key].data_field|@json_encode};{literal}
                                var summary = 0,
                                    myField = '',
                                    total = 0,
                                    indexField = 0;
                                objSummary.each(function(element, index, array) {
                                    if (element.field != 'false') {
                                        summary = 0;
                                        myField = element.field + '[]';
                                        hiddenField = '{/literal}{$summaryName}{literal}['+index+']';
                                        if (element.action == 'sum') {
                                            jQuery("input[name='" + myField + "']").each(function(element, index, array) {
                                                summary += parseGridNumber(jQuery(this).val());
                                            });
                                            // Mostrar el total formateado según el formato del usuario
                                            jQuery('#td_' + element.field).html(formatGridNumberForDisplay(summary, 2));
                                            // Guardar el valor en formato numérico estándar para la BD
                                            jQuery("input[name='" + hiddenField + "']").val(summary.toFixed(2));
                                        }
                                    }
                                });
                            }{/literal}


                        {/if}
                        {if $lstSubCampos[key].uitype eq 2204}
                            {assign var="calculatedClass" value="{$fieldname}_observed"}
                        {/if}
                    {/section}

                    function addRow{$fieldname}() {
                    {if $hasTFoot == 1}
                        trRow = jQuery('#{$fieldname} tr:last').prev().attr("numrowtr");
                    {else}
                        trRow = jQuery('#{$fieldname} tr:last').attr("numrowtr");
                    {/if}
                    trRow = Number(trRow) + 1;
                    numrow_{$fieldname} = trRow;
                    ctrlTable = document.getElementById('{$fieldname}');
                    tableLength = ctrlTable.rows.length;
                    tfoot = jQuery('#{$fieldname}').find('#{$fieldname}-summary',true,true);
                    if (tfoot.length > 0) {
                        tableLength -= 1;
                    }
                    if (ctrlTable) {
                        if (iNumRows_{$fieldname} == -1) {
                        iNumRows_{$fieldname} = (ctrlTable.rows.length);
                    } else {
                        iNumRows_{$fieldname}++;
                    }
                    iNumRows_{$fieldname} = trRow;
                    var row = ctrlTable.insertRow(tableLength);
                    {section name=row start=0 loop=$numSubCampos step=1}
                        var x{$smarty.section.row.index}=row.insertCell({$smarty.section.row.index});
                    {/section}
                    row.id = 'row_{$fieldname}_'+iNumRows_{$fieldname};
                    row.className = 'gridvalidationtr{$summaryClass}';
                    row.setAttribute('numrowtr', trRow);
                    {section name=key loop=$lstSubCampos}
                        {if $lstSubCampos[key].uitype neq 2202 && $lstSubCampos[key].uitype neq 2203 }
                            str = document.getElementById('td_{$fieldname}_{$lstSubCampos[key].name}_Campo0').innerHTML;
                            str = str.replace(/_Campo0/g,'_Campo'+iNumRows_{$fieldname});
                            str = str.replace(/{$lstSubCampos[key].name}0/g,'{$lstSubCampos[key].name}'+numrow_{$fieldname});
                            str = str.replace(/_template_0/g,'_template_'+iNumRows_{$fieldname});
                            x{$smarty.section.key.index}.innerHTML=str.replace('(0)','('+iNumRows_{$fieldname}+')');
                            x{$smarty.section.key.index}.id= 'td_{$lstSubCampos[key].name}_Campo'+numrow_{$fieldname};
                            x{$smarty.section.key.index}.className = 'grid-cell-compact {if $calculatedClass neq ""}{$calculatedClass}{/if}';
                            {if $lstSubCampos[key].uitype eq '99'}
                                x{$smarty.section.key.index}.width = '6%';
                                x{$smarty.section.key.index}.align='center';
                            {else}
                                x{$smarty.section.key.index}.width = 'auto';
                            {/if}

                            var scriptTags = x{$smarty.section.key.index}.getElementsByTagName("script");
                            for (var i = 0; i < scriptTags.length; i++) {
                                var scriptTag = scriptTags[i];
                                var script = document.createElement("script");
                                script.type = "text/javascript";
                                var head = document.getElementsByTagName("head")[0];
                                if (scriptTag.src == '') {
                                    script.appendChild(document.createTextNode(scriptTag.innerHTML));
                                    head.appendChild(script);
                                }
                            }
                        {elseif $lstSubCampos[key].uitype eq 2203 }
                            x{$smarty.section.key.index}.remove();
                        {/if}
                    {/section}
                    }
                    }
                </script>

                <script language="JavaScript">
                    function deleteRow{$fieldname}(iNumRow) {
                    var td = jQuery(iNumRow).parent();
                    var tr = jQuery(td).parent();
                    if (tr) {
                        jQuery(tr).remove();
                    }
                    {if $summaryClass neq "" && !$swDetailView}
                        getSummary_{$fieldname} ();
                    {/if}
                    }
                </script>
                <script language="JavaScript">
                    var tablaName = '{$fieldname}';
                    {section name=key loop=$lstSubCampos}
                        {if $lstSubCampos[key].uitype eq 2204 }

                            function {$lstSubCampos[key].name}_calculatedField(x) {
                            var calculate = 0,
                                n = 0;
                            {* Reemplazar jQuery(this).val() por parseGridNumber(jQuery(this).val()) para soportar formato europeo *}
                            {assign var="calcEquation" value=$lstSubCampos[key].data_field|unescape:"htmlall"|replace:"jQuery(this).val()":"parseGridNumber(jQuery(this).val())"}
                            calculate =  {$calcEquation};

                            if (!jQuery.isNumeric(calculate) || isNaN(calculate)) {
                                n = x;
                                for (x = n; x > 0; x--) {
                                    calculate =  {$calcEquation};
                                    if (jQuery.isNumeric(calculate) && !isNaN(calculate)) {
                                        break;
                                    }
                                }
                            }

                            // Devolver el número (no formateado) para que pueda usarse en otros cálculos
                            return isNaN(calculate) ? 0 : parseFloat(calculate.toFixed(2));
                            }

                        {/if}
                        {if ($lstSubCampos[key].uitype eq 10) && ($lstSubCampos[key].action_field neq 'false')}

                            // Cargar configuración de importación para {$lstSubCampos[key].name}
                            try {literal}{{/literal}
                            window.{$lstSubCampos[key].name} = {$lstSubCampos[key].action_field};
                            if (window.{$lstSubCampos[key].name} && typeof window.{$lstSubCampos[key].name} === 'object' && Object.keys(window.{$lstSubCampos[key].name}).length > 0) {literal}{{/literal}
                            {literal}}{/literal} else {literal}{{/literal}
                            window.{$lstSubCampos[key].name} = null;
                            console.warn('⚠ No import config for {$lstSubCampos[key].name}');
                            {literal}}{/literal}
                            {literal}}{/literal} catch(e) {literal}{{/literal}
                            console.error('✗ Error loading import config for {$lstSubCampos[key].name}:', e.message);
                            window.{$lstSubCampos[key].name} = null;
                            {literal}}{/literal}

                        {/if}
                    {/section}

                    function removeListFromEdit(obj) {
                        var myRow = jQuery(obj).parent().parent();
                        myRow.remove();
                    }

                    function searchCheckedField(objId) {
                        var $check = jQuery('#' + objId).find('input:checkbox'),
                            myId, indexPos,
                            objCheckDestination,
                            actived, deactived, fields, fieldIds, fieldType, fieldSelectedId,
                            delButton;
                        $check.each(function(key, value) {
                            if (key > 0) {
                                delButton = jQuery(this).parent().parent().find('button');
                                objCheckDestination = JSON.parse(jQuery(this).attr('data-action'));
                                actived = objCheckDestination['activado'];
                                deactived = objCheckDestination['desactivado'];
                                myId = jQuery(this).attr('id');
                                indexPos = searchIndexRow('#' + myId);
                                if (!(jQuery(this).is(':checked'))) {
                                    // is checked and action = active
                                    if (typeof actived !== 'undefined') {
                                        delButton.attr('disabled', true);
                                        if (actived.indexOf(',') !== -1) {
                                            fieldIds = actived.split('_');
                                            fields = actived.split(',');
                                            for (f = 0; f < fields.length; f++) {
                                                if (fields[f].indexOf(fieldIds[(fieldIds.length - 1)]) === -1) {
                                                    fieldSelectedId = ('#' + fields[f] + '_' + fieldIds[(
                                                        fieldIds.length - 1)] + indexPos);
                                                } else {
                                                    fieldSelectedId = ('#' + fields[f] + indexPos);
                                                }
                                                fieldType = jQuery(fieldSelectedId).attr('type');
                                                if (typeof fieldType === 'undefined') {
                                                    jQuery(fieldSelectedId + 'option:not(:selected)').attr(
                                                        'disabled', true);
                                                    jQuery(fieldSelectedId).attr('readonly', true);
                                                } else if ((fieldType === 'checkbox') || (fieldType ===
                                                        'file')) {
                                                    jQuery(fieldSelectedId).attr('disabled', true);
                                                } else {
                                                    if (fieldSelectedId.indexOf('jscal') == -1) {
                                                        jQuery(fieldSelectedId).attr('readonly', true);
                                                    } else {
                                                        jQuery(fieldSelectedId).css({literal}{pointerEvents: "none"}{/literal});
                                                    }
                                                }
                                            }
                                        } else {
                                            fieldSelectedId = ('#' + actived + indexPos);
                                            fieldType = jQuery(fieldSelectedId).attr('type');
                                            if (typeof fieldType === 'undefined') {
                                                jQuery(fieldSelectedId + 'option:not(:selected)').attr(
                                                    'disabled', true);
                                                jQuery(fieldSelectedId).attr('readonly', true);
                                            } else if (fieldType === 'checkbox') {
                                                jQuery(fieldSelectedId).attr('disabled', true);
                                            } else if (fieldType === 'file') {
                                                jQuery(fieldSelectedId).attr('disabled', true);
                                                jQuery(fieldSelectedId).parent().next().find('.btn-close').attr(
                                                    'disabled', true);
                                            } else {
                                                if (fieldSelectedId.indexOf('jscal') == -1) {
                                                    jQuery(fieldSelectedId).attr('readonly', true);
                                                } else {
                                                    jQuery(fieldSelectedId).css({literal}{pointerEvents: "none"}{/literal});
                                                }
                                            }
                                        } //is checked and action = deactive
                                    } else if (typeof deactived !== 'undefined') {
                                        delButton.attr('disabled', false);
                                        if (deactived.indexOf(',') !== -1) {
                                            fieldIds = deactived.split('_');
                                            fields = deactived.split(',');
                                            for (f = 0; f < fields.length; f++) {
                                                if (fields[f].indexOf(fieldIds[(fieldIds.length - 1)]) === -1) {
                                                    fieldSelectedId = ('#' + fields[f] + '_' + fieldIds[(
                                                        fieldIds.length - 1)] + indexPos);
                                                } else {
                                                    fieldSelectedId = ('#' + fields[f] + indexPos);
                                                }
                                                fieldType = jQuery(fieldSelectedId).attr('type');
                                                if (typeof fieldType == 'undefined') {
                                                    jQuery(fieldSelectedId + 'option:not(:selected)').attr(
                                                        'disabled', false);
                                                    jQuery(fieldSelectedId).attr('readonly', false);
                                                } else if (fieldType === 'checkbox') {
                                                    jQuery(fieldSelectedId).attr('disabled', false);
                                                } else if (fieldType === 'file') {
                                                    jQuery(fieldSelectedId).attr('disabled', false);
                                                    jQuery(fieldSelectedId).parent().next().find('.btn-close')
                                                        .attr('disabled', false);
                                                } else {
                                                    if (fieldSelectedId.indexOf('jscal') != -1) {
                                                        jQuery(fieldSelectedId).css({literal}{pointerEvents: "auto"}{/literal});
                                                    } else if (jQuery(fieldSelectedId).parent().find('div').eq(
                                                            0).attr('data-referenced-module') != undefined) {
                                                        jQuery(fieldSelectedId).parent().find('div').eq(0).css({literal}{pointerEvents: "auto"}{/literal});
                                                    } else {
                                                        jQuery(fieldSelectedId).attr('readonly', false);
                                                    }

                                                }

                                            }
                                        } else {
                                            fieldSelectedId = ('#' + deactived + indexPos);
                                            fieldType = jQuery(fieldSelectedId).attr('type');
                                            if (typeof fieldType === 'undefined') {
                                                jQuery(fieldSelectedId + 'option:not(:selected)').attr(
                                                    'disabled', false);
                                                jQuery(fieldSelectedId).attr('readonly', false);
                                            } else if (fieldType === 'checkbox') {
                                                jQuery(fieldSelectedId).attr('disabled', false);
                                            } else if (fieldType === 'file') {
                                                jQuery(fieldSelectedId).attr('disabled', false);
                                                jQuery(fieldSelectedId).parent().next().find('.btn-close').attr(
                                                    'disabled', false);
                                            } else {
                                                if (fieldSelectedId.indexOf('jscal') != -1) {
                                                    jQuery(fieldSelectedId).css({literal}{pointerEvents: "auto"}{/literal});
                                                } else if (jQuery(fieldSelectedId).parent().find('div').eq(0)
                                                    .attr('data-referenced-module') != undefined) {
                                                    jQuery(fieldSelectedId).parent().find('div').eq(0).css({literal}{pointerEvents: "auto"}{/literal});
                                                } else {
                                                    jQuery(fieldSelectedId).attr('readonly', false);
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    // is not checked and action = active
                                    if (typeof actived !== 'undefined') {
                                        delButton.attr('disabled', false);
                                        if (actived.indexOf(',') !== -1) {
                                            fieldIds = actived.split('_');
                                            fields = actived.split(',');
                                            for (f = 0; f < fields.length; f++) {
                                                if (fields[f].indexOf(fieldIds[(fieldIds.length - 1)]) === -1) {
                                                    fieldSelectedId = ('#' + fields[f] + '_' + fieldIds[(
                                                        fieldIds.length - 1)] + indexPos);
                                                } else {
                                                    fieldSelectedId = ('#' + fields[f] + indexPos);
                                                }
                                                fieldType = jQuery(fieldSelectedId).attr('type');
                                                if (typeof fieldType == 'undefined') {
                                                    jQuery(fieldSelectedId + 'option:not(:selected)').attr(
                                                        'disabled', false);
                                                    jQuery(fieldSelectedId).attr('readonly', false);
                                                } else if (fieldType === 'checkbox') {
                                                    jQuery(fieldSelectedId).attr('disabled', false);
                                                } else if (fieldType === 'file') {
                                                    jQuery(fieldSelectedId).attr('disabled', false);
                                                    jQuery(fieldSelectedId).parent().next().find('.btn-close')
                                                        .attr('disabled', false);
                                                } else {
                                                    if (fieldSelectedId.indexOf('jscal') == -1) {
                                                        jQuery(fieldSelectedId).attr('readonly', false);
                                                    } else {
                                                        jQuery(fieldSelectedId).css({literal}{pointerEvents: "auto"}{/literal});
                                                    }
                                                }
                                            }
                                        } else {
                                            fieldSelectedId = ('#' + actived + indexPos);
                                            fieldType = jQuery(fieldSelectedId).attr('type');
                                            if (typeof fieldType == 'undefined') {
                                                jQuery(fieldSelectedId + 'option:not(:selected)').attr(
                                                    'disabled', false);
                                                jQuery(fieldSelectedId).attr('readonly', false);
                                            } else if (fieldType === 'checkbox') {
                                                jQuery(fieldSelectedId).attr('disabled', false);
                                            } else if (fieldType === 'file') {
                                                jQuery(fieldSelectedId).attr('disabled', false);
                                                jQuery(fieldSelectedId).parent().next().find('.btn-close').attr(
                                                    'disabled', false);
                                            } else {
                                                if (fieldSelectedId.indexOf('jscal') == -1) {
                                                    jQuery(fieldSelectedId).attr('readonly', false);
                                                } else {
                                                    jQuery(fieldSelectedId).css({literal}{pointerEvents: "auto"}{/literal});
                                                }
                                            }
                                        } //is not checked and action = deactived
                                    } else if (typeof deactived !== 'undefined') {
                                        delButton.attr('disabled', true);
                                        if (deactived.indexOf(',') !== -1) {
                                            fieldIds = deactived.split('_');
                                            fields = deactived.split(',');
                                            for (f = 0; f < fields.length; f++) {
                                                if (fields[f].indexOf(fieldIds[(fieldIds.length - 1)]) === -1) {
                                                    fieldSelectedId = ('#' + fields[f] + '_' + fieldIds[(
                                                        fieldIds.length - 1)] + indexPos);
                                                } else {
                                                    fieldSelectedId = ('#' + fields[f] + indexPos);
                                                }
                                                fieldType = jQuery(fieldSelectedId).attr('type');
                                                if (typeof fieldType === 'undefined') {
                                                    jQuery(fieldSelectedId + ' option:not(:selected)').attr(
                                                        'disabled', true);
                                                    jQuery(fieldSelectedId).attr('readonly', true);
                                                } else if (fieldType === 'checkbox') {
                                                    jQuery(fieldSelectedId).attr('disabled', true);
                                                } else if (fieldType === 'file') {
                                                    jQuery(fieldSelectedId).attr('disabled', true);
                                                    jQuery(fieldSelectedId).parent().next().find('.btn-close')
                                                        .attr('disabled', true);
                                                } else {
                                                    if (fieldSelectedId.indexOf('jscal') != -1) {
                                                        jQuery(fieldSelectedId).css({literal}{pointerEvents: "none"}{/literal});
                                                    } else if (jQuery(fieldSelectedId).parent().find('div').eq(
                                                            0).attr('data-referenced-module') != undefined) {
                                                        jQuery(fieldSelectedId).parent().find('div').eq(0).css({literal}{pointerEvents: "none"}{/literal});
                                                    } else {
                                                        jQuery(fieldSelectedId).attr('readonly', true);
                                                    }

                                                }
                                            }
                                        } else {
                                            fieldSelectedId = ('#' + deactived + indexPos);
                                            fieldType = jQuery(fieldSelectedId).attr('type');
                                            if (typeof fieldType === 'undefined') {
                                                jQuery(fieldSelectedId + ' option:not(:selected)').attr(
                                                    'disabled', true);
                                                jQuery(fieldSelectedId).attr('readonly', true);
                                            } else if (fieldType === 'checkbox') {
                                                jQuery(fieldSelectedId).attr('disabled', true);
                                            } else if (fieldType === 'file') {
                                                jQuery(fieldSelectedId).attr('disabled', true);
                                                jQuery(fieldSelectedId).parent().next().find('.btn-close').attr(
                                                    'disabled', true);
                                            } else {
                                                if (fieldSelectedId.indexOf('jscal') != -1) {
                                                    jQuery(fieldSelectedId).css({literal}{pointerEvents: "none"}{/literal});
                                                } else if (jQuery(fieldSelectedId).parent().find('div').eq(0)
                                                    .attr('data-referenced-module') != undefined) {
                                                    jQuery(fieldSelectedId).parent().find('div').eq(0).css({literal}{pointerEvents: "none"}{/literal});
                                                } else {
                                                    jQuery(fieldSelectedId).attr('readonly', true);
                                                }
                                            }
                                        }
                                    }

                                }
                            }
                        })
                    }

                    function searchIndexRow(element) {
                        nameElement = jQuery(element).attr('name');
                        firstComponent = nameElement.split('_');
                        secondComponet = firstComponent[(firstComponent.length - 1)].split('[]');
                        fieldNumber = secondComponet[0];
                        idComponent = element.split('_');
                        idComponent.forEach(function(theValue) {
                            if (Number(theValue)) {
                                numDigist = (theValue.length - fieldNumber.length);
                                if (numDigist == 0) {
                                    numDigist = 1;
                                }
                                myIndex = theValue.substr(-numDigist);
                            }
                        });
                        return myIndex;
                    }

                    function {$fieldname}_getFilter(obj) {
                    var arrayNames = [
                        {section name=key loop=$lstSubCampos}
                            {if $lstSubCampos[key].filter_field neq '' }
                                '{$lstSubCampos[key].name}',
                            {/if}
                        {/section}
                    ];
                    var arrayFilters = [
                        {section name=key loop=$lstSubCampos}
                            {if $lstSubCampos[key].filter_field neq '' }
                                {$lstSubCampos[key].filter_field},
                            {/if}
                        {/section}
                    ];
                    var tablaName = '{$fieldname}';
                    var status = [];
                    var myColor = '';
                    if (obj == undefined) {
                        $myId = arrayNames[0] + '2'

                    } else {
                        $myId = jQuery(obj).attr('id');
                    }
                    indexPos = searchIndexRow('#' + $myId);
                    index = 0;
                    fieldName = jQuery(obj).attr('name');
                    for (i = 0; i < arrayNames.length; i++) {
                        if (status.length > 1) {
                            status.splice(0, status.length)
                        }
                        myColor = '';
                        evaluateCondition = '';
                        index = i;
                        td = jQuery('#' + arrayNames[i] + indexPos).parent();
                        numOfObjects = arrayFilters[index].length;
                        jQuery.each(arrayFilters[index], function(key, value) {
                            nameField = "input[name='" + value.field + "[]']";
                            {literal}
                                fieldValue =  parseGridNumber(jQuery(nameField).map(function(){return jQuery(this).val();}).get( indexPos-1 ));
                            {/literal}
                            status[key] = 0;
                            switch (value.condition) {
                                case 'e':
                                    if (value.value == fieldValue) status[key] = 1;
                                    break;
                                case 'n':
                                    if (value.value != fieldValue) status[key] = 1;
                                    break;
                                case 'c':
                                    {literal}
                                        fieldValue =  (jQuery(nameField).map(function(){return jQuery(this).val();}).get( indexPos-1));
                                    {/literal}
                                    if (fieldValue.indexOf(value.value) !== -1) status[key] = 1;
                                    break;
                                case 'k':
                                    {literal}
                                        fieldValue =  (jQuery(nameField).map(function(){return jQuery(this).val();}).get( indexPos-1));
                                    {/literal}
                                    if (fieldValue.indexOf(value.value) === -1) status[key] = 1;
                                    break;
                                case 'l':
                                    if (parseInt(fieldValue) < parseInt(value.value)) status[key] = 1;
                                    break;
                                case 'g':
                                    if (parseInt(fieldValue) > parseInt(value.value)) status[key] = 1;
                                    break;
                                case 'm':
                                    if (parseInt(fieldValue) <= parseInt(value.value)) status[key] = 1;
                                    break;
                                case 'h':
                                    if (parseInt(fieldValue) >= parseInt(value.value)) status[key] = 1;
                                    break;
                                default:
                            }
                            if (status[key] == 1) {
                                myColor = value.color;
                            }

                            if (key < (numOfObjects - 1)) {
                                status[key] += ' ' + value.join
                            }
                        });
                        evaluateCondition = status.join(' ');
                        if (!eval(evaluateCondition)) {
                            myColor = '#FFFFFF';
                        }
                        td.css('background-color', myColor)
                    }
                    }

                    jQuery(document).ready(function() {
                        var gridForm = jQuery("form[name='EditView']");
                        jQuery('.numericvalidate').trigger('change');

                        function getRecordDetails(ImportObj, theModule, record, seqNumber) {
                            var recordId = record,
                                theModule = theModule,
                                fieldToImport,
                                fieldToExport,
                                nameField,
                                fieldValues,
                                arguments,
                                hasImport = false,
                                actionMode = gridForm.find("input[name='mode']").val(),
                                toModule = gridForm.find("input[name='module']").val(),
                                recordTo = gridForm.find("input[name='record']").val();

                            fieldToExport = Object.values(ImportObj);

                            for (m = 0; m < fieldToExport.length; m++) {
                                arrData = fieldToExport[m].split('@');
                                if (arrData[0] == theModule) {
                                    hasImport = true;
                                    break;
                                }
                            }

                            if (hasImport) {
                                fieldToImport = Object.keys(ImportObj);
                                arguments = [
                                    'module=Settings',
                                    'action=SettingsAjax',
                                    'theModule=' + encodeURIComponent(theModule),
                                    'file=GetRecordToGrid',
                                    'Ajax=true',
                                    'record=' + encodeURIComponent(recordId),
                                    'fieldToImport=' + fieldToImport,
                                    'fieldToExport=' + fieldToExport,
                                    'toModule=' + toModule,
                                    'actionMode=' + actionMode,
                                    'recordTo=' + recordTo,
                                    'rowIndex=' + seqNumber
                                ];

                                jQuery.ajax('index.php', {
                                    data: arguments.join('&'),
                                    dataType: 'json',
                                    method: 'get'
                                }).done(function(response) {
                                    nameField = Object.keys(response);
                                    fieldValues = Object.values(response);

                                    for (k = 0; k < fieldToImport.length; k++) {
                                        arrData = fieldToExport[k].split('@');

                                        for (i = 0; i < nameField.length; i++) {
                                            if (nameField[i] == arrData[1]) {
                                                if (jQuery.isNumeric(fieldValues[i])) {
                                                    // Formatear el número según las preferencias del usuario
                                                    theValue = formatGridNumberForDisplay(Number(
                                                        fieldValues[i]), 2);
                                                } else {
                                                    theValue = fieldValues[i];
                                                }
                                                var targetId = '#' + fieldToImport[k] + seqNumber;
                                                jQuery(targetId).val(theValue);
                                                jQuery(targetId).trigger("change");
                                                break;
                                            }
                                        }
                                    }
                                });
                            }
                        };

                        // Al entrar al campo (focus): quitar separadores de miles para facilitar edición
                        jQuery("#{$fieldname}").on('focus', 'input.numericvalidate, input.currencyvalidate, input.percentvalidate', function() {
                        var val = jQuery(this).val();
                        if (val !== '') {
                            var numVal = parseGridNumber(val);
                            if (!isNaN(numVal)) {
                                var userFormat = (typeof gUserNumberFormat !== 'undefined') ?
                                    gUserNumberFormat : 'AMERICAN_FORMAT';
                                // Mostrar solo con separador decimal, sin separadores de miles
                                if (userFormat === 'EUROPEAN_FORMAT') {
                                    jQuery(this).val(numVal.toFixed(2).replace('.', ','));
                                } else {
                                    jQuery(this).val(numVal.toFixed(2));
                                }
                                // Seleccionar todo el texto para facilitar reemplazo
                                jQuery(this).select();
                            }
                        }
                    });

                    // Al salir del campo (blur): reformatear con separadores de miles
                    jQuery("#{$fieldname}").on('blur', 'input.numericvalidate, input.currencyvalidate, input.percentvalidate', function() {
                    var val = jQuery(this).val();
                    if (val !== '') {
                        var numVal = parseGridNumber(val);
                        if (!isNaN(numVal)) {
                            jQuery(this).val(formatGridNumberForDisplay(numVal, 2));
                        }
                    }
                    });

                    jQuery("#{$fieldname}").off('change').on('change', '.action_grid{if $calculatedClass neq ""}, .{$fieldname}_observed input{/if}{if $summaryClass neq ""}, .tr_{$fieldname}_summary input{/if}', function (event) {

                    {if $calculatedClass neq ""}

                        var row, indexField, calculo;
                        row = jQuery(this).parent().parent();
                        indexField = jQuery(this).parent().parent().attr('numrowtr');

                        // Recalcular TODOS los campos calculados de esta fila
                        {section name=key loop=$lstSubCampos}
                            {if $lstSubCampos[key].uitype eq 2204}
                                calculo = {$lstSubCampos[key].name}_calculatedField(indexField);
                                row.find('.calculated-{$lstSubCampos[key].name}-table').val(formatGridNumberForDisplay(calculo, 2));
                                {if $summaryClass neq "" && !$swDetailView}
                                    getSummary_{$fieldname}('{$lstSubCampos[key].name}[]');
                                {/if}
                            {/if}
                        {/section}

                    {/if}

                    if (jQuery(this).hasClass('action_grid')) {
                        indexField = jQuery(this).parent().parent().attr('numrowtr');
                        idField = jQuery(this).val();
                        dataAction = jQuery(this).attr('select-action');
                        if ((dataAction != 'false') && (idField != '1')) {
                            objDestinationValue = JSON.parse(dataAction);
                            if (idField != '1' && idField != '0' && idField != 'Seleccionar')
                                fieldName = objDestinationValue[idField];
                            $myId = jQuery(this).attr('id');
                            indexPos = indexField;
                            idElement = fieldName + indexPos;
                            objTd = jQuery('#' + idElement).parent();
                            jQuery.ajax({
                                url: 'index.php?module=Settings&action=SettingsAjax&file=getLinkFieldToGrid&parenttab=Settings&ajax=true',
                                data: 'fieldId=' + idField + '&fieldName=' + fieldName + '&id=' + idElement,
                                method: "POST",
                                success: function(data) {
                                    jQuery('#' + objTd[0].id).html(data)
                                }
                            });
                        }
                    }
                    {if $summaryClass neq ""}
                        else
                        {literal}
                            {
                                if (!{/literal}
                                    {if $swDetailView}true
                                    {else}false
                                    {/if}
                                    {literal}) {
                                        getSummary_{/literal}{$fieldname}{literal}(jQuery(this).attr('name'));
                                    }
                                    }
                                {/literal}
                            {/if}
                            });

                            jQuery("#{$fieldname}").off('click').on('click', '.action_chk', function (event) {
                            var $myId = jQuery(this).attr('id'),
                                indexPos = jQuery(this).parent().parent().attr('numrowtr'),
                                dataAction = jQuery(this).attr('data-action'),
                                objCheckDestination, actived, deactived, fields, fieldIds, fieldType, fieldSelectedId,
                                delButton = jQuery(this).parent().parent().find('button'),
                                today = new Date(),
                                date = today.getFullYear() + '-' + (today.getMonth() + 1) + '-' + today.getDate();
                            if (dataAction != 'false') {
                                objCheckDestination = JSON.parse(jQuery(this).attr('data-action'));
                                actived = objCheckDestination['activado'];
                                deactived = objCheckDestination['desactivado'];
                                if (!(jQuery(this).is(':checked'))) {
                                    // is checked and action = active
                                    if (typeof actived !== 'undefined') {
                                        delButton.attr('disabled', true);
                                        if (actived.indexOf(',') !== -1) {
                                            fieldIds = actived.split('_');
                                            fields = actived.split(',');
                                            for (f = 0; f < fields.length; f++) {
                                                if (fields[f].indexOf(fieldIds[(fieldIds.length - 1)]) === -1) {
                                                    fieldSelectedId = ('#' + fields[f] + '_' + fieldIds[(fieldIds.length - 1)] +
                                                        indexPos);
                                                } else {
                                                    fieldSelectedId = ('#' + fields[f] + indexPos);
                                                }
                                                fieldType = jQuery(fieldSelectedId).attr('type');
                                                if (typeof fieldType == 'undefined') {
                                                    jQuery(fieldSelectedId + 'option:not(:selected)').attr('disabled', true);
                                                    jQuery(fieldSelectedId).attr('readonly', true);
                                                } else if (fieldType === 'checkbox') {
                                                    jQuery(fieldSelectedId).attr('disabled', true);
                                                } else if (fieldType === 'file') {
                                                    jQuery(fieldSelectedId).attr('disabled', true);
                                                    jQuery(fieldSelectedId).parent().next().find('.btn-close').attr('disabled',
                                                        true);
                                                } else {
                                                    if (fieldSelectedId.indexOf('jscal') == -1) {
                                                        jQuery(fieldSelectedId).attr('readonly', true);
                                                    } else {
                                                        jQuery(fieldSelectedId).css({literal}{pointerEvents: "none"}{/literal});
                                                    }
                                                }
                                            }
                                        } else {
                                            fieldSelectedId = ('#' + actived + indexPos);
                                            fieldType = jQuery(fieldSelectedId).attr('type');
                                            if (typeof fieldType === 'undefined') {
                                                jQuery(fieldSelectedId + 'option:not(:selected)').attr('disabled', true);
                                                jQuery(fieldSelectedId).attr('readonly', true);
                                            } else if (fieldType === 'checkbox') {
                                                jQuery(fieldSelectedId).attr('disabled', true);
                                            } else if (fieldType === 'file') {
                                                jQuery(fieldSelectedId).attr('disabled', true);
                                                jQuery(fieldSelectedId).parent().next().find('.btn-close').attr('disabled',
                                                    true);
                                            } else {
                                                if (fieldSelectedId.indexOf('jscal') == -1) {
                                                    jQuery(fieldSelectedId).attr('readonly', true);
                                                } else {
                                                    jQuery(fieldSelectedId).css({literal}{pointerEvents: "none"}{/literal});
                                                }
                                            }
                                        } //is checked and action = deactive
                                    } else if (typeof deactived !== 'undefined') {
                                        delButton.attr('disabled', false);
                                        if (deactived.indexOf(',') !== -1) {
                                            fieldIds = deactived.split('_');
                                            fields = deactived.split(',');
                                            for (f = 0; f < fields.length; f++) {
                                                if (fields[f].indexOf(fieldIds[(fieldIds.length - 1)]) === -1) {
                                                    fieldSelectedId = ('#' + fields[f] + '_' + fieldIds[(fieldIds.length - 1)] +
                                                        indexPos);
                                                } else {
                                                    fieldSelectedId = ('#' + fields[f] + indexPos);
                                                }
                                                fieldType = jQuery(fieldSelectedId).attr('type');
                                                if (typeof fieldType == 'undefined') {
                                                    jQuery(fieldSelectedId + ' option:not(:selected)').attr('disabled', false);
                                                    jQuery(fieldSelectedId).attr('readonly', false);
                                                } else if (fieldType === 'checkbox') {
                                                    jQuery(fieldSelectedId).attr('disabled', false);
                                                } else if (fieldType === 'file') {
                                                    jQuery(fieldSelectedId).attr('disabled', false);
                                                    jQuery(fieldSelectedId).parent().next().find('.btn-close').attr('disabled',
                                                        false);
                                                } else {
                                                    if (fieldSelectedId.indexOf('jscal') != -1) {
                                                        jQuery(fieldSelectedId).css({literal}{pointerEvents: "auto"}{/literal});
                                                    } else if (jQuery(fieldSelectedId).parent().find('div').eq(0).attr(
                                                            'data-referenced-module') != undefined) {
                                                        jQuery(fieldSelectedId).parent().find('div').eq(0).css({literal}{pointerEvents: "auto"}{/literal});
                                                    } else {
                                                        jQuery(fieldSelectedId).attr('readonly', false);
                                                    }
                                                }
                                            }
                                        } else {

                                            fieldSelectedId = ('#' + deactived + indexPos);
                                            fieldType = jQuery(fieldSelectedId).attr('type');
                                            if (typeof fieldType === 'undefined') {
                                                jQuery(fieldSelectedId + ' option:not(:selected)').attr('disabled', false);
                                                jQuery(fieldSelectedId).attr('readonly', false);
                                            } else if (fieldType === 'checkbox') {
                                                jQuery(fieldSelectedId).attr('disabled', false);
                                            } else if (fieldType === 'file') {
                                                jQuery(fieldSelectedId).attr('disabled', false);
                                                jQuery(fieldSelectedId).parent().next().find('.btn-close').attr('disabled',
                                                    false);
                                            } else {
                                                if (fieldSelectedId.indexOf('jscal') != -1) {
                                                    jQuery(fieldSelectedId).css({literal}{pointerEvents: "auto"}{/literal});
                                                } else if (jQuery(fieldSelectedId).parent().find('div').eq(0).attr(
                                                        'data-referenced-module') != undefined) {
                                                    jQuery(fieldSelectedId).parent().find('div').eq(0).css({literal}{pointerEvents: "auto"}{/literal});
                                                } else {
                                                    jQuery(fieldSelectedId).attr('readonly', false);
                                                }
                                            }
                                        }
                                    }
                                    jQuery('#chk_' + $myId).val('No');
                                } else {
                                    // is not checked and action = active
                                    if (typeof actived !== 'undefined') {
                                        delButton.attr('disabled', false);
                                        if (actived.indexOf(',') !== -1) {
                                            fieldIds = actived.split('_');
                                            fields = actived.split(',');
                                            for (f = 0; f < fields.length; f++) {
                                                if (fields[f].indexOf(fieldIds[(fieldIds.length - 1)]) === -1) {
                                                    fieldSelectedId = ('#' + fields[f] + '_' + fieldIds[(fieldIds.length - 1)] +
                                                        indexPos);
                                                } else {
                                                    fieldSelectedId = ('#' + fields[f] + indexPos);
                                                }
                                                fieldType = jQuery(fieldSelectedId).attr('type');
                                                if (typeof fieldType === 'undefined') {
                                                    jQuery(fieldSelectedId + ' option:not(:selected)').attr('disabled', false);
                                                    jQuery(fieldSelectedId).attr('readonly', false);
                                                } else if (fieldType === 'checkbox') {
                                                    jQuery(fieldSelectedId).attr('disabled', false);
                                                } else if (fieldType === 'file') {
                                                    jQuery(fieldSelectedId).attr('disabled', false);
                                                    jQuery(fieldSelectedId).parent().next().find('.btn-close').attr('disabled',
                                                        false);
                                                } else {
                                                    jQuery(fieldSelectedId).attr('readonly', false);
                                                }
                                            }
                                        } else {
                                            fieldSelectedId = ('#' + actived + indexPos);
                                            fieldType = jQuery(fieldSelectedId).attr('type');
                                            if (typeof fieldType === 'undefined') {
                                                jQuery(fieldSelectedId + ' option:not(:selected)').attr('disabled', false);
                                                jQuery(fieldSelectedId).attr('readonly', false);
                                            } else if (fieldType === 'checkbox') {
                                                jQuery(fieldSelectedId).attr('disabled', false);
                                            } else if (fieldType === 'file') {
                                                jQuery(fieldSelectedId).attr('disabled', false);
                                                jQuery(fieldSelectedId).parent().next().find('.btn-close').attr('disabled',
                                                    false);
                                            } else {
                                                jQuery(fieldSelectedId).attr('readonly', false);
                                            }
                                        } //is not checked and action = deactived
                                    } else if (typeof deactived !== 'undefined') {
                                        delButton.attr('disabled', true);
                                        if (deactived.indexOf(',') !== -1) {
                                            fieldIds = deactived.split('_');
                                            fields = deactived.split(',');
                                            for (f = 0; f < fields.length; f++) {
                                                if (fields[f].indexOf(fieldIds[(fieldIds.length - 1)]) === -1) {
                                                    fieldSelectedId = ('#' + fields[f] + '_' + fieldIds[(fieldIds.length - 1)] +
                                                        indexPos);
                                                } else {
                                                    fieldSelectedId = ('#' + fields[f] + indexPos);
                                                }
                                                fieldType = jQuery(fieldSelectedId).attr('type');
                                                if (typeof fieldType === 'undefined') {
                                                    jQuery(fieldSelectedId + ' option:not(:selected)').attr('disabled', true);
                                                    jQuery(fieldSelectedId).attr('readonly', true);
                                                } else if (fieldType === 'checkbox') {
                                                    jQuery(fieldSelectedId).attr('disabled', true);
                                                } else if (fieldType === 'file') {
                                                    jQuery(fieldSelectedId).attr('disabled', true);
                                                    jQuery(fieldSelectedId).parent().next().find('.btn-close').attr('disabled',
                                                        true);
                                                } else {
                                                    if (fieldSelectedId.indexOf('jscal') != -1) {
                                                        jQuery(fieldSelectedId).css({literal}{pointerEvents: "none"}{/literal});
                                                        jQuery(fieldSelectedId).val(date)
                                                    } else if (jQuery(fieldSelectedId).parent().find('div').eq(0).attr(
                                                            'data-referenced-module') != undefined) {
                                                        jQuery(fieldSelectedId).parent().find('div').eq(0).css({literal}{pointerEvents: "none"}{/literal});
                                                        jQuery(fieldSelectedId).parent().find('div').eq(1).css({literal}{pointerEvents: "none"}{/literal});
                                                    } else {
                                                        jQuery(fieldSelectedId).attr('readonly', true);
                                                    }
                                                }
                                            }
                                        } else {
                                            fieldSelectedId = ('#' + deactived + indexPos);
                                            fieldType = jQuery(fieldSelectedId).attr('type');
                                            if (typeof fieldType === 'undefined') {
                                                jQuery(fieldSelectedId + ' option:not(:selected)').attr('disabled', true);
                                                jQuery(fieldSelectedId).attr('readonly', true);
                                            } else if (fieldType === 'checkbox') {
                                                jQuery(fieldSelectedId).attr('disabled', true);
                                            } else if (fieldType === 'file') {
                                                jQuery(fieldSelectedId).attr('disabled', true);
                                                jQuery(fieldSelectedId).parent().next().find('.btn-close').attr('disabled',
                                                    true);
                                            } else {
                                                if (fieldSelectedId.indexOf('jscal') != -1) {
                                                    jQuery(fieldSelectedId).css({literal}{pointerEvents: "none"}{/literal});
                                                } else if (jQuery(fieldSelectedId).parent().find('div').eq(0).attr(
                                                        'data-referenced-module') != undefined) {
                                                    jQuery(fieldSelectedId).parent().find('div').eq(0).css({literal}{pointerEvents: "none"}{/literal});
                                                } else {
                                                    jQuery(fieldSelectedId).attr('readonly', true);
                                                }

                                            }
                                        }
                                    }
                                    jQuery('#chk_' + $myId).val('Si');
                                }
                            }
                            });

                            function searchIndexRow(element) {
                                nameElement = jQuery(element).attr('name');
                                firstComponent = nameElement.split('_');
                                secondComponet = firstComponent[(firstComponent.length - 1)].split('[]');
                                fieldNumber = secondComponet[0];
                                idComponent = element.split('_')
                                idComponent.forEach(function(theValue) {
                                    if (Number(theValue)) {
                                        numDigist = (theValue.length - fieldNumber.length);
                                        if (numDigist == 0) {
                                            numDigist = 1;
                                        }
                                        myIndex = theValue.substr(-numDigist);
                                    }
                                });
                                if (Number(myIndex) > 2) {
                                    myIndex = Number(myIndex) + 1;
                                }
                                return myIndex;
                            }

                            jQuery(document).off("relatedModuleRecordSelected").on("relatedModuleRecordSelected", function(evt,
                            modalTitle, targetDisplayFieldId, targetDataFieldId) {
                            if (targetDisplayFieldId === "shareDisplay") {
                                jQuery('#showShare').html('Compartir con: ' + jQuery('#shareDisplay').val());
                                return;
                            }
                            modal = jQuery(this);
                            formTitle = modalTitle;
                            fieldDisplay = '#' + targetDisplayFieldId;
                            fieldId = '#' + targetDataFieldId;

                            // Extraer seqNumber del ID del campo (ej: articulo_371702 -> 2)
                            // El ID tiene formato: nombreCampo_fieldId + seqNumber (ej: articulo_371702)
                            // Necesitamos extraer solo el último dígito
                            var fieldIdValue = targetDataFieldId;
                            var lastChar = fieldIdValue.charAt(fieldIdValue.length - 1);
                            seqNumber = lastChar;

                            record = jQuery(fieldId).val();
                            rowId = '#related-list-' + formTitle + '_template_' + seqNumber;
                            if (jQuery(rowId).hasClass('hide')) {
                                rowElement = jQuery(rowId).clone().removeAttr('id').insertBefore(rowId).removeClass(
                                    'hide');
                                rowElement.find(fieldDisplay).removeAttr('id')
                                rowElement.off('click').on('click', '.removeButton', function(e) {
                                    var myRow = jQuery(this).parent().parent();
                                    myRow.remove();
                                });
                                jQuery(fieldDisplay).val()
                            }

                            fieldDisplayName = jQuery(fieldDisplay).attr('name').split('[]');
                            objImport = fieldDisplayName[0];

                            // Verificar si existe el objeto de importación y ejecutar la importación
                            try {
                                var importConfig = window[objImport];
                                if (typeof importConfig !== 'undefined' && importConfig !== null) {
                                    getRecordDetails(importConfig, formTitle, record, seqNumber);
                                } else {
                                    console.warn('⚠ No hay configuración de importación para el campo:', objImport);
                                }
                            } catch (e) {
                                console.error('✗ Error al ejecutar importación para campo ' + objImport + ':', e);
                            }
                            });
                            searchCheckedField('{$fieldname}');
                            });

                            {if $summaryClass neq "" && !$swDetailView}
                                {literal}
                                    jQuery(document).ready(function() {
                                    getSummary_{/literal}{$fieldname} ();
                                    {literal}
                                    });
                                {/literal}
                            {/if}

                            {if  $swDetailView && $hideBlock }
                                {literal}
                                    jQuery(document).ready(function() {
                                        jQuery('#td_{/literal}{$fieldname}{literal}').parent ().parent ().parent ().addClass ('hidden');
                                    });
                                {/literal}
                            {/if}
                        </script>

                        <script type="text/javascript">
                            // Función para formatear números según las preferencias del usuario
                            function formatGridNumber(value, uitype) {
                                if (!value || value === '' || !$.isNumeric(value)) {
                                    return value;
                                }

                                var numValue = parseFloat(value);
                                var userFormat = '{$current_user->numbering_format|default:"AMERICAN_FORMAT"}';

                                // Determinar precisión según uitype
                                var precision = 2;
                                if (uitype == 7 || uitype == 71 || uitype == 72) {
                                    precision = 2;
                                } else if (uitype == 9) {
                                    precision = 2;
                                }

                                if (userFormat === 'EUROPEAN_FORMAT') {
                                    // Formato europeo: 1.234,56
                                    return numValue.toLocaleString('de-DE', {
                                        minimumFractionDigits: precision,
                                        maximumFractionDigits: precision
                                    });
                                } else {
                                    // Formato americano: 1,234.56
                                    return numValue.toLocaleString('en-US', {
                                        minimumFractionDigits: precision,
                                        maximumFractionDigits: precision
                                    });
                                }
                            }
                        </script>

                        <table id="{$fieldname}" class="table table-bordered tablegridvalidate">
                            {assign var="hasTFoot" value=0}
                            {assign var="summaryRow" value=''}
                            {assign var="summaryName" value=''}
                            {assign var="numrowtr" value=0}
                            <thead>
                                <tr valign="top">
                                    {foreach from=$lstSubCampos key=k item=v}
                                        {assign var="module" value=''}
                                        {assign var="lista" value=''}
                                        {if $v.uitype eq 2203}
                                            {$hasTFoot = 1}
                                            {$summaryRow = $v.data_field}
                                            {$summaryName = $v.name}
                                        {else}
                                            {if $v.uitype eq 10 &&  $swDetailView }
                                                {if $v.relmodule|strpos:"@" !== false}
                                                    {assign var=relateModuleValues value="@"|explode:$v.relmodule}
                                                    {assign var="module" value=$relateModuleValues[2]}
                                                    {assign var="lista" value=$relateModuleValues[1]}
                                                    {assign var="moduleLabel" value=$relateModuleValues[4]}
                                                {/if}
                                            {/if}

                                            <td width="{$v.proportional_width}%" class="">
                                                {if $v.uitype neq 99}{$v.label}
                                                    {if $module ne ''}:<span style="text-transform: capitalize"> {$moduleLabel}</span> y
                                                        {$lista}{/if}{else}{if ! $swDetailView }Eliminar{else}&nbsp;{/if}{/if}</td>
                                                {/if}
                                            {/foreach}
                                        </tr>
                                    </thead>
                                    <tbody rowtotal="0">
                                        {assign var="keyValue" value=0}
                                        {assign var="tdNum" value=1}
                                        <tr numrowtr="{$keyValue}" id="row_{$fieldname}_{$keyValue+1}" style="display:none"
                                            class="gridvalidationtr{$summaryClass}">
                                            <!--- Edit-->
                                            {foreach from=$lstSubCampos key=k item=v}
                                                {if $v.uitype neq 2203}
                                                    {assign var="fieldValue" value=$v.defaultvalue}
                                                    {include file='Settings/GridContenet.tpl'}
                                                {/if}
                                            {/foreach}
                                        </tr>
                                        {if isset($dataValues) && $dataValues|is_array}
                                            {assign var="tdNum" value=1}
                                            {assign var = "j" value=0}
                                            {foreach from=$dataValues key=keyValue item=row}
                                                {$numrowtr = $numrowtr+1}
                                                <!-- {$keyValue+$tdNum}numrowtr   -->
                                                <tr numrowtr="{$numrowtr}" id="row_{$fieldname}_{$keyValue}"
                                                    class="gridvalidationtr{$summaryClass}">
                                                    {foreach from=$lstSubCampos key=k item=v}
                                                        {if $v.uitype neq 2203}
                                                            {if isset($row[$k])}
                                                                {assign var="fieldValue" value=$row[$k]}
                                                            {else}
                                                                {assign var="fieldValue" value=$v.defaultvalue}
                                                            {/if}
                                                            {include file='Settings/GridContenet.tpl'}
                                                        {/if}
                                                    {/foreach}
                                                    {$j = $j+1}
                                                </tr>
                                            {/foreach}
                                        {elseif $numberOfRows >= 1}
                                            {assign var="tdNum" value=2}
                                            {for $j = 0 to ($numberOfRows-1)}
                                                <!-- {$keyValue+$tdNum}numrowtr   -->
                                                <tr numrowtr="{$numrowtr}" id="row_{$fieldname}_{$keyValue}"
                                                    class="gridvalidationtr{$summaryClass}">
                                                    <!-- Created import -->
                                                    {assign var="tdNum" value=(2+$j)}
                                                    {foreach from=$lstSubCampos key=k item=v}
                                                        {if $v.uitype neq 2203}
                                                            {if $v.defaultvalue neq 'NULL'}
                                                                {assign var="fieldValue" value={$v.defaultvalue}}
                                                            {else}
                                                                {assign var="fieldValue" value=""}
                                                            {/if}
                                                            {include file='Settings/GridContenet.tpl'}
                                                        {/if}
                                                    {/foreach}
                                                </tr>
                                            {/for}
                                        {else}
                                            {assign var="tdNum" value=2}
                                            {$numrowtr = $numrowtr+1}
                                            <tr numrowtr="{$numrowtr}" id="row_{$fieldname}_{$keyValue}"
                                                class="gridvalidationtr{$summaryClass}">
                                                <!-- created  -->
                                                {assign var = "j" value=0}
                                                {foreach from=$lstSubCampos key=k item=v}
                                                    {if $v.uitype neq 2203}
                                                        {if $v.defaultvalue neq 'NULL'}
                                                            {assign var="fieldValue" value={$v.defaultvalue}}
                                                        {else}
                                                            {assign var="fieldValue" value=""}
                                                        {/if}
                                                        {include file='Settings/GridContenet.tpl'}
                                                    {/if}
                                                {/foreach}
                                            </tr>
                                        {/if}

                                        {if $hasTFoot == 1}
                                            <!-- Summry row -->
                                            <tr id="{$fieldname}-summary">
                                                {foreach from=$summaryRow key=k item=v}
                                                    {if $v.action eq '' }
                                                        <td class="" align="center" style="border: 0px">
                                                            {if $k eq 0}<p style="text-align: center">TOTALES</p>{/if}
                                                        </td>
                                                        <input value="0" name="{$summaryName}[{$k}]" type="hidden">
                                                    {elseif $v.action eq 'sum' }
                                                        <!--<td width="auto" class="" id="td_{$v.field}" align="center" style="border: 0px">-->
                                                        <td width="auto" class="" id="td_{$v.field}" style="border: 0px">
                                                            {if isset($summaryValues) && $summaryValues|is_array}
                                                                {$summaryValues[$k]}
                                                            {/if}
                                                        </td>
                                                        <input
                                                            value="{if isset($summaryValues) && $summaryValues|is_array}{$summaryValues[{$k}]}{else}0{/if}"
                                                            name="{$summaryName}[{$k}]" type="hidden">
                                                    {else}
                                                        <td class="" align="center" style="border: 0px">
                                                            {if isset($summaryValues) && $summaryValues|is_array}
                                                                {$summaryValues[$k]}
                                                            {else}
                                                                {$v.calculatedId}
                                                            {/if}
                                                        </td>
                                                        <input
                                                            value="{if isset($summaryValues) && $summaryValues|is_array}{$summaryValues[$k]}{else}{$v.calculatedId}{/if}"
                                                            name="{$summaryName}[{$k}]" type="hidden">
                                                    {/if}
                                                {/foreach}
                                                <td width="auto" class="" align="center" style="border: 0px">&nbsp;&nbsp</td>
                                            </tr>
                                            <!-- Fila resumen-->
                                        {/if}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>