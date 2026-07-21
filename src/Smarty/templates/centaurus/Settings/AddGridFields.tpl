{strip}
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/wizard.css" />
	<style>
		.hideToMe {
			display: none;
	}
        .steps-table li {
            font-size: 14px !important;
            padding: 0 14px 0 26px !important;
        }
        .steps-titles {
            font-size: 14px !important;
        }
        h4 {
            font-size: 14px !important;
        }
        .wizard {
            display:block;
        }
</style>
<script type="text/javascript">
{literal}
(function (jQuery) {
    lastModuleFieldId= 0;
    labelField = [];
    nameField = [];
    typeField = [];
    valueField = [];
    campoModulos = [];
    totalField = 0;
    swListField = false;
    swChktField = false;
    swImportField = false;
    swColorFilter = false;
    swSummary = false;
    swCalculatedMsn = true;
    historyReferntModule =[];
    gridSelectedField = [];
    tableImportIndex  = 0;

    // private function
	var getNormalizedText = function (value) {
		var from = 'àáäâèéëêìíïîòóöôùúüûñç·/-,:;',
			to   = 'aaaaeeeeiiiioooouuuunc______',
			i, l;

		value = value.toLowerCase ().replace (' ', '_');

		// remove accents, swap ñ for n, etc
		for (i = 0, l = from.length; i < l; i++) {
			value = value.replace (new RegExp (from.charAt (i), 'g'), to.charAt (i));
		}

		value = value.replace (/[^a-z0-9 _]/g, '').replace (/\s+/g, '_').replace (/-+/g, '_');
		return value;
	};

    var isGridImportValid = function(){
        gridTable = jQuery('#mix-grid').html();
        gridSelectedId = jQuery('#idSelected').val();
        if((gridTable != '&nbsp;')&& (gridTable != undefined)){
            if ((gridSelectedId === undefined) || (gridSelectedId === null) || (gridSelectedId.trim () === '')) {
                alert ('Seleccione un registro');
                return false;
            } else if(gridSelectedField.length == 0) {
                alert ('Seleccione al menos una columna');
                return false;
            } else {
                setImportColumn();
                return true;
            }
        } else {
            return true;
        }
    };

	var isFormValid = function (form) {
		var fields = form.find ('input.field-name'),
			row, field, type, value, i, n, labels, names, values, label, name;

		field = form.find ('.grid-label');
		label = field.val ();
		if ((label === undefined) || (label === null) || (label.trim () === '')) {
			alert ('Introduce el nombre de la tabla');
			field.focus ();
			return false;
		}

		labels = [];
		names = [];
		n = fields.length;
		for (i = 0; i < n; i += 1) {
			field = jQuery (fields [ i ]);
			row = field.closest ('tr');
			type = row.find ('.field-type').val () ? parseInt (row.find ('.field-type').val ()) : '';
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce el nombre del campo');
				row.find ('.field-label').focus ();
				return false;
			} else if (jQuery.inArray (value.toLowerCase (), names) !== -1) {
				alert ('Introduce un nombre de campo único');
				row.find ('.field-label').focus ();
				return false;
			}
			names.push (value.toLowerCase ());

			field = row.find ('.field-label');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce la etiqueta del campo');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value.toLowerCase (), labels) !== -1) {
				alert ('Introduce una etiqueta de campo única');
				field.focus ();
				return false;
			}
			labels.push (value.toLowerCase ());

			if (jQuery.inArray (type, [ 1, 7, 9 ]) !== -1) {
				field = row.find ('.field-length');
				value = field.val ();
				if (!value) {
					alert ('Introduce la longitud del campo');
					field.focus ();
					return false;
				} else if ((!jQuery.isNumeric (value)) || (value <= 0)) {
					alert ('Introduce una longitud de campo mayor que cero');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ 7, 9 ]) !== -1) {
				field = row.find ('.field-precision');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Introduce la precisión del número');
					field.focus ();
					return false;
				} else if ((!jQuery.isNumeric (value)) || (value <= 0)) {
					alert ('Introduce una precisión de número mayor que cero');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ 15 ]) !== -1) {
				field = row.find ('.field-values');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '')) {
					alert ('Introduce la lista de valores');
					field.focus ();
					return false;
				}
				values = value.split ('\n');
				if (values.length < 2) {
					alert ('Introduce al menos dos valores');
					field.focus ();
					return false;
				}
			}

			if (jQuery.inArray (type, [ 10 ]) !== -1) {
				field = row.find ('.field-modules');
				value = field.val ();
				if ((value === undefined) || (value === null) || (value.trim () === '') || (value.trim () === '-')) {
					alert ('Selecciona el módulo de la lista');
					field.focus ();
					return false;
				}
			}

            if (jQuery.inArray (type, [ 2204 ]) !== -1) {
                field = row.find ('.field-label');
                value = field.val ();
                var totalNumFields = 0,
                fieldsTypes = [];
                jQuery("select[name='tipoCampo[]']").each(function (element, index, array) {
                    fieldsTypes.push(jQuery(this).val());
                });

                for (var k = 0; k < fieldsTypes.length; k++){
                    if(jQuery.inArray (fieldsTypes[k], [ '7', '9' ]) !== -1) {
                        totalNumFields+= 1;
                    }
                }
                if(totalNumFields > 1) {
                    if (swCalculatedMsn) {
                            alert('Las operaciones de cálculo para la columna "' + value + '" se deben crear en el Motor de Cálculos del Sistema! ');
                            swCalculatedMsn = false;
                    }
                } else {
                    alert('Las columnas tipo "Campos de Calculos" requieren de al menos dos columnas tipos Número o (%)  adicionales ');
                    field.focus();
                    return false;
                }
            }
		}

		jQuery.ajax ('index.php', {
			async: false,
			data: 'module=Settings&action=SettingsAjax&file=validarGrid&ajax=true&modulename=' + {/literal} encodeURIComponent ('{$_FLD_MODULE}') {literal}  + '&etiquetaGrid=' + encodeURIComponent (label),
			dataType: 'text',
			method:   'get'
		}).done (function (response) {
			if (response === 'field_exists') {
				alert ('El nombre o la etiqueta de la tabla inteligente ya existen');
				return false;
			}
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error. Intenta más tarde');
			return false;
		});
		return true;
	};

	var closeModal = function () {
        AddGridFieldsUtils.actualStep = 1;
        AddGridFieldsUtils.removeClassToNav();
		jQuery ('#camposGrid').removeClass ('md-show');
		jQuery ('.md-overlay').css ({
			opacity: 0.0,
			visibility: 'hidden'
		});
	};

	var editDefaultValues = function (fldmodule, fieldid) {
		new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'module=Settings&action=SettingsAjax&file=editGridValues&parenttab=Settings&fieldid=' + fieldid + '&ajax=true' + '&fldmodule=' + fldmodule,
					onComplete: function (response) {
						var camposGridValues = $ ('camposGridValues');
						camposGridValues.innerHTML = response.responseText;
						fnvshNrm ('camposGridValues');
						var scriptTags = camposGridValues.getElementsByTagName ('script');
						for (var i = 0; i < scriptTags.length; i++) {
							var scriptTag = scriptTags[ i ];
							var script = document.createElement ('script');
							script.type = 'text/javascript';
							var head = document.getElementsByTagName ('head')[ 0 ];
							if (scriptTag.src == '') {
								script.appendChild (document.createTextNode (scriptTag.innerHTML));//txt is the code
								head.appendChild (script);
							}
						}
						fnvshNrm ('camposGridValues');
					}
				}
		);
	};

	var setImportFieldFromModule = function (moduleName) {
        var templateContents = jQuery ('#import-module-fields-template').html().replace (/__MODULE__/g, moduleName),
            table            = jQuery ('#importFieldsTable-' + tableImportIndex),
            row,
            listFieldName,
            listFieldLabel,
            listValue  = '',
            moduleReference,
            fieldImportModule = jQuery ('#importModuleReference');

        if (!templateContents) {
            return;
        }
        listValue = getFieldsFromModule (moduleName);
        if(listValue !== '') {
            listFieldName  = Object.keys (listValue);
            listFieldLabel = Object.values (listValue);
            row = jQuery(templateContents);
            option = row.find('select').eq(0);
            for (var op = 0; op < listFieldName.length; op++) {
                option.append(jQuery('<option>', {
                    value: listFieldName[op],
                    text: listFieldLabel[op],
                }));
            }
            option = row.find('select').eq(1);
            for (var op = 0; op < labelField.length; op++) {
                if (jQuery.inArray(typeField[op], ['1', '7', '9']) !== -1) {
                    option.append(jQuery('<option>', {
                        value: getNormalizedText (nameField[op]),
                        text: labelField[op],
                    }));
                }
            }
            if (fieldImportModule.val () == '') {
                fieldImportModule.val (moduleName);
            } else {
                moduleReference = fieldImportModule.val ().split(';');
                moduleReference.push(moduleName);
                fieldImportModule.val (moduleReference.join(';'));
            }
            row.insertBefore(table.find('.import-bar'));
            jQuery ('.import-bar').removeClass ('hide');
            jQuery ('#gridImportValues').removeClass ('hide');
            swImportField = true;
            tableImportIndex += 1;
        }
    };

	var addGridFields = function () {
		var form = jQuery ('form[name="add-grid-field"]');
		if (!isFormValid (form)) {
			return;
		}

		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'text',
			method:   form.attr ('method')
		}).done (function () {
			window.location.reload ();
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error. Intenta más tarde');
		});
	};

	var addImportRelationship = function (obj) {
	    var tr = '',
            table = jQuery (jQuery(obj).attr ('data-target'));
	    tr = table.find('#grid-relationship');
	    tr.clone().insertBefore(table.find('.import-bar')).find('button').eq(0).removeClass('hide');
    };

	var deleteImportRelationship = function (obj) {
	  var table = jQuery (jQuery(obj).attr ('data-target')),
          moduleName = table.attr ('data-module'),
          importModule     = jQuery ('#importModuleReference'),
          moduleReference  = importModule.val ().split(';');
        if (moduleReference.length > 1) {
            for (var j = 0; j < moduleReference.length; j++) {
                if (moduleReference [j] === moduleName) {
                    moduleReference.splice(j, 1);
                    break;
                }
            }
            if (moduleReference.length > 1) {
                importModule.val (moduleReference.join(';'))
            } else {
                importModule.val (moduleReference [0]);
            }
        } else {
            importModule.val ('');
        }
	  table.remove ();
    };

	var removeClassToNav = function () {
        // Ajustar Clases tab-content
        jQuery('.tab-content').find("*").removeClass('active');
        jQuery('.steps').find("*").removeClass('active');
        jQuery('.steps').find("*").removeClass('badge-success');
        jQuery('.steps').find("*").removeClass('badge-primary');
        jQuery("input[type='submit']").addClass('hide')
        jQuery("button[type='submit']").addClass('hide')
        jQuery('.btn-prev').removeClass('hide');
        jQuery('.btn-next').removeClass('hide');

    };

    var removeActions = function (trObject) {
        var row = jQuery('.' + trObject).closest('tr');
        if (!row) {
            return;
        }
        row.remove();
        if (trObject == 'tr-list-action'){
            swListField = false;
            jQuery('#linkField').addClass('hide')
        }else{
            swChktField = false;
            jQuery('#checkField').addClass('hide')
        }
    };

    var checkFieldAction = function () {
	    if(swChktField){
            var r = confirm("¡Esta operación eliminará todas las acciones de activar/desactivar! ¿Deseas Continuar?");
            if (r == true) {
                AddGridFieldsUtils.removeActions('tr-check-action');
            }else{
                return;
            }
        }
        if (jQuery.inArray ('56', typeField) == -1) {
            alert('¡No hay columnas checkbox para acciones!');
            return;
        }
        valueCheckBox=['activado'];
        for (var f = 0; f < typeField.length; f++) {
            if( typeField[f] == 56) {
                for (var k = 0; k < valueCheckBox.length; k++){
                        var templateContents = jQuery('#fieldCheck-action-template').html(),
                            table = jQuery('#actionCheck'),
                            rows = table.find('tbody > tr'),
                            preId = '',
                            row;
                        if (!templateContents) {
                            return;
                        }

                        row = jQuery(templateContents);
                        row.find('input').eq(0).attr('name', 'checkField[]').val(labelField[f]);
                    	row.find('input').eq(1).attr('name', 'checkNameField[]').val(nameField[f]);
                        option = row.find('select').eq(1).attr('name', 'checkFieldDest[]');
                        for (var op = 0; op < labelField.length; op++ ){
                            if((typeField[op] != 2202) && (typeField[op] != 2203) && (typeField[op] != 2204)) {
                                preId = (typeField[op] == 5) ? 'jscal_field_' : '';
                                option.append(jQuery('<option>', {
                                    value: nameField[f] + '@' + preId + getNormalizedText(labelField[op]),
                                    text: labelField[op]
                                }));
                            }
                        }
                        table.append(row);
                    jQuery('#checkField').removeClass('hide');
                    swChktField = true;
                    }
            }
        }
        if(! swChktField){
	        alert('¡No hay columnas checkbox para acciones!')
        }
    };

    var importFieldAction = function () {
        var moduleName = '',
            listValue  = '',
            r = false,
            numModule = 0;

        if(swImportField){
            var r = confirm("¡Esta operación eliminará todas los campos importados anteriores! ¿Deseas Continuar?");
            if (r == true) {
                jQuery('.import-field-row').remove();
                jQuery('.import-bar').addClass('hide');
                removeImport ();
            }else{
                return;
            }
        }

        if (jQuery.inArray ('10', typeField) == -1) {
            alert('¡No hay columnas tipo modulo relacionado para importar!');
            return;
        } else if((jQuery.inArray ('1', typeField) == -1) && (jQuery.inArray ('7', typeField) == -1) && (jQuery.inArray ('9', typeField) == -1) ){
            alert('¡Se requiere de al menos un campo tipo texto o numérico ');
            return;
        }
        jQuery('#select-modules-to-import').empty();
        jQuery ('#select-modules-to-import').append (
            jQuery (
                '<option>',
                {
                    value: '',
                    text:  'Selecionar columna'
                }
            )
        );
        for (var f = 0; f < typeField.length; f++) {
            if( typeField[f] == 10) {
                numModule++;
                jQuery ('#select-modules-to-import').append (
                    jQuery (
                        '<option>',
                        {
                            value: campoModulos[f],
                            text:  labelField[f]
                        }
                    )
                );
                moduleName = campoModulos[f];
            }
        }
        jQuery('#gridImportValues').removeClass('hide');
        jQuery('#div-modules-to-import').removeClass('hide');
    };

    var linkFieldAction = function () {
        if(swListField){
            var r = confirm("¡Esta operación eliminará todas las vinculaciones! ¿Deseas Continuar?");
            if (r == true) {
                AddGridFieldsUtils.removeActions('tr-list-action');
            }else{
                return;
            }
        }
        if (jQuery.inArray ('15', typeField) == -1) {
            alert('¡No hay campos tipo listas para vincular!');
            return;
        } else if(jQuery.inArray ('1', typeField) == -1){
            alert('¡Se requiere de al menos un campo tipo texto!');
            return;
        }
        for (var f = 0; f < typeField.length; f++) {
            if( typeField[f] == 15) {
                listValue = valueField[f].split('\n');
                for (var k = 0; k < listValue.length; k++){
                    if(listValue != ' '){
                        var templateContents = jQuery('#field-action-template').html(),
                            table = jQuery('#actionTable'),
                            rows = table.find('tbody > tr'),
                            row;
                        if (!templateContents) {
                            return;
                        }
                        templateContents
                        row = jQuery(templateContents);
                        row.find('input').eq(0).attr('name', 'selectField[]').val(labelField[f]);
                        row.find('input').eq(1).attr('name', 'selectValue[]').val(listValue[k]);
                        row.find('input').eq(2).attr('name', 'selectNameField[]').val(nameField[f]);
                        row.find('select').eq(0).attr('data-select','destinationField_'+f+'_'+k);
                        option = row.find('select').eq(1).attr('name', 'destinationField[]').attr('id','destinationField_'+f+'_'+k);
                        for (var op = 0; op < labelField.length; op++ ){
                            if((labelField[op] != labelField[f]) && (typeField[op] == 1)) {
                                option.append(jQuery('<option>', {
                                    value: getNormalizedText(labelField[op]),
                                    text: labelField[op],
                                }));
                            }
                        }
                        table.append(row);
                        jQuery('#linkField').removeClass('hide');
                        swListField = true;
                    }

                }

            }
        }
        if(! swListField){
            alert('¡No hay columnas listas para vincular!')
        }
    };

    var removeColorFilter = function (trObject) {
        var row = jQuery('.' + trObject).closest('tr');
        if (!row) {
            return;
        }
        row.remove();
        swColorFilter = false;

    };

    var removeRowColorFilter = function (obj) {
        var myRow = jQuery(obj).parent().parent();
        myRow.remove();

    };

    var colorFilter = function () {
        var templateContents = jQuery('#filter-color-template').html(),
            table = jQuery('#filterTable'),
            rows = table.find('tbody > tr'),
            row;
        if (!templateContents) {
            return;
        }
        row = jQuery(templateContents);
        option = row.find('select').eq(0);
        for (var op = 0; op < labelField.length; op++ ){
            if (jQuery.inArray (typeField[op], [ '1','7', '9', '21' ]) !== -1) {
                option.append(jQuery('<option>', {
                    value: getNormalizedText(labelField[op]),
                    text: labelField[op],
                }));
            }
        }
        option = row.find('select').eq(1);
        for (var op = 0; op < labelField.length; op++ ){
            if (jQuery.inArray (typeField[op], [ '1','7', '9', '21' ]) !== -1) {
                option.append(jQuery('<option>', {
                    value: getNormalizedText(labelField[op]),
                    text: labelField[op],
                }));
            }
        }
        table.append(row);
        jQuery('#filterFieldTable').removeClass('hide');
        swColorFilter = true;
    };

    var searchByAction = function () {
		if(AddGridFieldsUtils.actualStep == 4){
            labelField = [];
            typeField = [];
            valueField = [];
            nameField = [];
            campoModulos = [];

            jQuery("input[name='etiquetaCampo[]']").each(function (element, index, array) {
                labelField.push(jQuery(this).val());
            });
            jQuery("input[name='nombreCampo[]']").each(function (element, index, array) {
                nameField.push(jQuery(this).val());
            });
            jQuery("select[name='tipoCampo[]']").each(function (element, index, array) {
                typeField.push(jQuery(this).val());
            });
            jQuery("textarea[name='valoresCampo[]']").each(function (element, index, array) {
                valueField.push(jQuery(this).val());
            });
            jQuery("select[name='moduloCampo[]']").each(function (element, index, array) {
                campoModulos.push(jQuery(this).val());
            });

        }
    };

    var getFieldsFromModule = function (moduleName) {
        var fieldsObject = '';
        arguments = [
            'module=Settings',
            'action=SettingsAjax',
            'file=GetAvailableFieldsData',
            'modulename=' + encodeURIComponent (moduleName),
            'Ajax=true'
        ];
        jQuery.ajax ('index.php', {
            data:     arguments.join ('&'),
            async: false,
            dataType: 'json',
            method:   'get'
        }).done (function (response) {
            fieldsObject =  response;
        }).fail (function (){
            alert ('Se ha presentado un error: ' + jQueryResponse.responseText);
        });
        return fieldsObject;
    };

	var goToBackStep = function() {
	    if(AddGridFieldsUtils.actualStep > 1){
            AddGridFieldsUtils.actualStep -= 1;
            AddGridFieldsUtils.removeClassToNav();
            step = AddGridFieldsUtils.actualStep;
            jQuery('#nav-tab-'+step).addClass('active');
            jQuery('#nav-tab-'+step).tab('show');
            jQuery('#nav-li-'+step).addClass('active');
            jQuery('#nav-sp-'+step).addClass('badge-primary');
            AddGridFieldsUtils.searchByAction();
            if(step == 1){
                jQuery('.btn-prev').addClass('hide')
            }
        }
    };

	var goToForwardToStep = function () {
        if(AddGridFieldsUtils.actualStep < 5){
            var form = jQuery('form[name="add-grid-field"]');
            if(AddGridFieldsUtils.actualStep == 1){
                field = form.find ('.grid-label');
                label = field.val ();
                if ((label === undefined) || (label === null) || (label.trim () === '')) {
                    alert ('Introduce nombre de la tabla');
                    field.focus ();
                    return false;
                }
                jQuery.ajax ('index.php', {
                    async: false,
                    data: 'module=Settings&action=SettingsAjax&file=validarGrid&ajax=true&modulename=' + {/literal} encodeURIComponent ('{$_FLD_MODULE}') {literal}  + '&etiquetaGrid=' + encodeURIComponent (label),
                    dataType: 'text',
                    method:   'get'
                }).done (function (response) {
                    if (response === 'field_exists') {
                        alert ('El nombre de la tabla ya existe');
                        field.focus ();
                        return false;
                    }else {
            AddGridFieldsUtils.actualStep += 1;
                    }
                }).fail (function (jQueryResponse) {
                    alert ('Se ha presentado un error. Intenta más tarde');
                    field.focus ();
                    return false;
                });
            } else if ((AddGridFieldsUtils.actualStep == 2)&&(!isGridImportValid())) {
                return;
            } else if ((AddGridFieldsUtils.actualStep == 3)&&(!isFormValid(form))) {
                return;
            } else {
                AddGridFieldsUtils.actualStep += 1;
            }

            AddGridFieldsUtils.removeClassToNav();
            step = AddGridFieldsUtils.actualStep;
            jQuery('#nav-tab-'+step).addClass('active');
            jQuery('#nav-tab-'+step).tab('show');
            jQuery('#nav-li-'+step).addClass('active');
            jQuery('#nav-sp-'+step).addClass('badge-primary');
            jQuery('#nav-sp-'+(step-1)).addClass('badge-success');
            AddGridFieldsUtils.searchByAction();
            if(step == 5){
                jQuery("input[type='submit']").removeClass('hide');
                jQuery("button[type='submit']").removeClass('hide');
                jQuery('.btn-next').addClass('hide')
            }
        }
    };

	var checkSelection = function (obj) {
        selectedValue = jQuery(obj).val();
        myDestination = jQuery(obj).attr('data-select');
        if(selectedValue == '1'){
            jQuery('#'+myDestination).removeAttr('required');
            jQuery('#'+myDestination).addClass('hide')
        } else if(jQuery('#'+myDestination).hasClass('hide')){
            jQuery('#'+myDestination).attr('required', 'required');
            jQuery('#'+myDestination).removeClass('hide')
        }
    };

    var updateFieldGridPropertiesUI = function (row, selectedFieldType) {
        var field,
            fieldType = isNaN (selectedFieldType) ? 1 : parseInt (selectedFieldType);

        field = row.find ('.field-length');
        if (jQuery.inArray (fieldType, [ 1, 7, 9, 71 ]) !== -1) {
            field.css ('display', 'inline');
            if (jQuery.inArray (fieldType, [ 7, 9, 71 ]) !== -1) {
                field.val (18);
            } else {
                field.val ('');
            }
        } else {
            field.css ('display', 'none');
        }

        field = row.find ('.field-values');
        if (jQuery.inArray (fieldType, [ 15, 33 ]) !== -1) {
            field.css ('display', 'inline');
        } else {
            field.css ('display', 'none');
        }

        field = row.find ('.field-modules');
        if (jQuery.inArray (fieldType, [ 10, 404 ]) !== -1) {
            field.css ('display', 'inline');
        } else {
            field.css ('display', 'none');
        }

        field = row.find ('.field-progress-bar');
        if (jQuery.inArray (fieldType, [ 108 ]) !== -1) {
            field.css ('display', 'inline');
        } else {
            field.css ('display', 'none');
        }

        field = row.find ('.field-prefix');
        if (jQuery.inArray (fieldType, [ 4 ]) !== -1) {
            field.css ('display', 'inline');
        } else {
            field.css ('display', 'none');
        }

        field = row.find ('.field-precision');
        if (jQuery.inArray (fieldType, [ 7, 9, 71 ]) !== -1) {
            field.css ('display', 'inline');
            field.val (2);
        } else {
            field.css ('display', 'none');
        }

        field = row.find ('.field-sequence');
        if (jQuery.inArray (fieldType, [ 4 ]) !== -1) {
            field.css ('display', 'inline');
        } else {
            field.css ('display', 'none');
        }
    };

    var setImportColumn = function(){
        for (k = 0; k < gridSelectedField.length; k++){
            dataField = gridSelectedField[k].split('@');
            inField = true;
            jQuery("input[name='nombreCampo[]']").each(function (element, index, array) {
                if (jQuery(this).val() == dataField[1]){
                    inField = false;
                };
            });
            if(inField) {
                thiz = jQuery('#add-field');
                var templateContents = jQuery('#field-template').html(),
                    table            = thiz.closest ('tr').find ('.block-fields > tbody'),
                    rows = table.find('tbody > tr'),
                    row;
                if (!templateContents) {
                    return;
                }

                row = jQuery(templateContents);
                row.find('.block-number').val(1);
                row.find('.import-code').val(dataField[2]);
                row.find('.field-label').val(dataField[0]);
                row.find('.field-name').val(dataField[1]);
                option = row.find('.field-type');
                option.append(jQuery('<option>', {
                    value: '2202',
                    text: 'Importada',
                }));
                option.val('2202');
                updateFieldGridPropertiesUI(row, 2202);
                table.append(row);
                swImportField = true;
            }
        }
    };

    var addFieldToGrid = function (link) {
        var thiz = jQuery (link),
            blockNumber = thiz.attr ('data-block-number'),
            templateContents = jQuery ('#field-template').html (),
            table            = thiz.closest ('tr').find ('.block-fields > tbody'),
            row;
        if (!templateContents) {
            return;
        }
        row = jQuery (templateContents);
        row.find ('.block-number').val (blockNumber);
        row.find ('.field-type').val ('1');
        updateFieldGridPropertiesUI (row, 1);
        table.append (row);

    };

    var deleteRowOfGrid = function (link) {
        var row = jQuery (link).closest ('tr');
        if (!row) {
            return;
        }
        row.remove ();
    };

    var deleteImport = function (obj) {
        jQuery(obj).parent().parent().remove()

    };

    var changeGridFieldPropertiesUI = function (thiz) {
        var selectField             = jQuery (thiz),
            row                     = selectField.closest ('tr'),
            mainProperties          = row.find ('field-main-properties'),
            fieldType               = selectField.val (),
            field;

        mainProperties.css ('visibility', 'hidden');
        updateFieldGridPropertiesUI (row, fieldType);
        mainProperties.css ('visibility', 'visible');
    };

    var gridNormalizedFieldName = function (thiz) {
        var sourceField = jQuery (thiz),
            destinationField = sourceField.closest ('tr').find ('.field-name');
        destinationField.val (getNormalizedText (sourceField.val ()));
    };

    var rowDown = function(btn){
        var rowToMove = jQuery(btn).parents('tr.MoveableRow');

        var next = rowToMove.next('tr.MoveableRow');
        next.after(rowToMove);
    };

    var rowUp = function(btn){
        var rowToMove = jQuery(btn).parents('tr.MoveableRow');

        var prev = rowToMove.prev('tr.MoveableRow');
        prev.before(rowToMove);
    };

    var summaryFieldAction = function () {
        if(swSummary){
            AddGridFieldsUtils.removeSummary('td-summary-action')
        }
        var templateContents = jQuery('#grid-summary-template').html(),
            table = jQuery('#actionSummary'),
            rows = table.find('thead'),
            row,
            heads = '';
        if (!templateContents) {
            return;
    }

        for (var op = 0; op < labelField.length; op++ ){
        heads += '<th>'+labelField[op]+'</th> '
        row = jQuery(templateContents);
            tr = table.find('.tr-summary-action');
            row.find('.summary-name').html(labelField[op])
            row.find('input').eq(0).val(nameField[op]);
            jQuery(tr).append(row);
        }
        jQuery(rows).append('<tr>'+heads+'</tr>')
        jQuery('#summaryField').removeClass('hide');
        swSummary = true;
    };

    var removeSummary = function (tdClass) {
        var table = jQuery('#actionSummary'),
            rows = table.find('thead > tr'),
            row = jQuery('.' + tdClass);
        if (!row) {
            return;
        }
        row.remove();
        rows.remove();
        jQuery('#summaryField').addClass('hide');
        swSummary = false;
    };

    var removeImport = function () {
        jQuery('.import-field-row').remove();
        jQuery('.import-bar').addClass('hide');
        jQuery('#select-modules-to-import').empty();
        jQuery('#gridImportValues').addClass('hide');
        jQuery('#div-modules-to-import').addClass('hide');
        jQuery('#importModuleReference').val('');
        swImportField    = false;
        for (i = 1; i < tableImportIndex ; i++) {
            jQuery('#importFieldsTable-' + i).remove ();
        }
    };

    var searchFieldToImport = function (obj) {
        var gridImportValues = jQuery('#gridImportValues'),
            moduleName       = jQuery(obj).val(),
            module           = jQuery(obj).find ('option:selected').text (),
            importTable      = jQuery ('#import-module-fields-tabla-template').html().replace (/__ID__/g, tableImportIndex).replace (/__DATA_MODULE__/g, moduleName),
            importModule     = jQuery ('#importModuleReference'),
            moduleReference  = importModule.val ().split(';'),
            r                = false,
            tableImport;
        if(moduleName !== '') {
                if (jQuery.inArray(moduleName, moduleReference) !== -1) {
                    var r = confirm("¡Esta operación eliminará todas los campos importados de la columna " + module + "! ¿Continuar?");
                    if (r == true) {
                        gridImportValues.find('[data-module="' + moduleName + '"]').remove();
                        if (moduleReference.length > 1) {
                            for (var j = 0; j < moduleReference.length; j++) {
                                if (moduleReference [j] === moduleName) {
                                    moduleReference.splice(j, 1);
                                    break;
                                }
                            }
                            if (moduleReference.length > 1) {
                                importModule.val (moduleReference.join(';'))
                            } else {
                                importModule.val (moduleReference [0]);
                            }
                        } else {
                            importModule.val ('');
                        }
                    } else {
                        return;
                    }
                }
            jQuery (importTable).insertBefore('#separator-line');
            gridImportValues.find('[data-module=""]').attr ('data-module', moduleName);
            setImportFieldFromModule (moduleName);
        }

    };

    var summarySelection = function (obj) {
        field = jQuery(obj).val();
        row = jQuery(obj).parent().parent();
        if(jQuery(obj).is(':checked')){
            row.find('input').eq(1).val(field);
        } else {
            row.find('input').eq(1).val('false');
            tr = jQuery(obj).parent().parent().parent();
            tr.find('select').eq(0).val('');
            tr = jQuery(obj).parent().parent().parent().next();
            tr.find('select').eq(0).val('');
            tr.find('.calculated').addClass('hide');
        }
    };

    var summaryActionSelection = function (obj) {
        actionSlected = jQuery(obj).val();
        row = jQuery(obj).parent().parent().next();;
        objSelect = row.find('select').eq(1)
        if(actionSlected == 'sys') {
            row.find('.calculated').removeClass('hide');
            if(objSelect.length > 0){
                objSelect.attr('required', 'required')
            }
        } else {
            row.find('.calculated').addClass('hide');
            if(objSelect.length > 0){
                objSelect.removeAttr('required')
            }
        }
    };

	window.AddGridFieldsUtils = {
	    actualStep: 1,
		addGridFields:               addGridFields,
		closeModal:                  closeModal,
		editDefaultValues:           editDefaultValues,
        goToBackStep:                goToBackStep,
        goToForwardToStep:           goToForwardToStep,
		removeClassToNav:            removeClassToNav,
		searchByAction:              searchByAction,
		linkFieldAction:             linkFieldAction,
        removeActions:               removeActions,
        checkFieldAction:            checkFieldAction,
        colorFilter:                 colorFilter,
        removeColorFilter:           removeColorFilter,
        removeRowColorFilter:        removeRowColorFilter,
        checkSelection:              checkSelection,
        addFieldToGrid:              addFieldToGrid,
        deleteRowOfGrid:             deleteRowOfGrid,
        changeGridFieldPropertiesUI: changeGridFieldPropertiesUI,
        gridNormalizedFieldName:     gridNormalizedFieldName,
        rowDown:                     rowDown,
        rowUp:                       rowUp,
        summaryFieldAction:          summaryFieldAction,
        removeSummary:               removeSummary,
        summarySelection:            summarySelection,
        summaryActionSelection:      summaryActionSelection,
        importFieldAction:           importFieldAction,
        deleteImport:                deleteImport,
        addImportRelationship:       addImportRelationship,
        deleteImportRelationship:    deleteImportRelationship,
        searchFieldToImport:         searchFieldToImport,
        removeImport:                removeImport
	};

	jQuery (document).ready (function () {
		jQuery ('#grid-name').keyup (function (evt) {
			var field = jQuery (evt.currentTarget);
			field.val (getNormalizedText (field.val ()));
		});

        jQuery("[data-toggle='tooltip']").tooltip();
	});

	var getGridTable = function (id, gridModule) {
        new Ajax.Request(
            'index.php',
            { async: false,
                cache: false,
                queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody:'module=Settings&action=SettingsAjax&file=gridToEspecialFields&parenttab=Settings&moduleFieldId=' + id +'&gridModule='+gridModule+'&ajax=true',
                onComplete: function(response) {
                    response.responseText = response.responseText.replace(/^[\s\ufeff\xA0]+|[\s\uFEFF\xA0]+$/g, '');
                    jQuery('#mix-grid').html( response.responseText);
                }
            }
        );
    };

    jQuery('#moduleFieldId').change(function(e){
        arrayValue = jQuery(this).val().split('@');
        idField = arrayValue[0];
        gridModule = arrayValue[1];
        gridTable = jQuery('#mix-grid').html();
        if(idField == 0) {
            if(gridTable != '&nbsp;'){
                    var r = confirm("¡Esta operación eliminará todas las columnas seleccionadas! ¿Deseas Continuar?");
                    if (r == true) {
                        jQuery('#other-table-info').html();
                        jQuery('#mix-grid').html('&nbsp;');
                        lastModuleFieldId = 0;
                        jQuery('#column0').val(0)
                    } else {
                        jQuery(this).val(lastModuleFieldId);
                    }
            }
        } else {
            if(gridTable != '&nbsp;'){
                var r = confirm("¡Esta operación eliminará todas las columnas seleccionadas! ¿Deseas Continuar?");
                if (r == true) {
                    lastModuleFieldId = idField;
                    jQuery('#column0').val(idField)
                   getGridTable(idField, gridModule)
                } else {
                    jQuery(this).val(lastModuleFieldId);
                }
            } else {
                lastModuleFieldId = idField;
                jQuery('#column0').val(idField)
                getGridTable(idField, gridModule);
            }
        }
    });

    jQuery("#lvt-table").off('change').on('change', '.search-list', function (e) {
        mySelectedModule = jQuery(this).val();
        clearSelect = [];
        if (jQuery.inArray (mySelectedModule, historyReferntModule) === -1) {
            historyReferntModule.push(mySelectedModule);
        }
        actualList = jQuery('#field-list').val().split(';');
        jQuery("select[name='moduloCampo[]']").each(function (element, index, array) {
            clearSelect.push(jQuery(this).val())
        });
        historyReferntModule.each(function(element, index){
            if (jQuery.inArray (element, clearSelect) === -1) {
                jQuery('#list-'+element).remove();
            }
        })

        actualList.each(function(element, index){
            swRemove = true;
            for (k = 0; k < clearSelect.length; k++) {
                if(element.indexOf(clearSelect[k]) !== -1){
                    swRemove = false;
                    break
                }
            }
            if(swRemove){
                actualList.splice(index,1);
            }

        })


        jQuery('#field-list').val(actualList.join(';'))

        if(mySelectedModule  != "-") {
            objTd = jQuery(this).parent();
            objNextTd = objTd.next();
            jQuery.ajax({
                url: 'index.php?module=Settings&action=SettingsAjax&file=getRelatedListToGrid&parenttab=Settings&ajax=true',
                data: 'selectedModule=' + mySelectedModule,
                method: "POST",
                success: function (data) {
                    if(data != 'false') {
                        jQuery(data).appendTo(objNextTd);
                        if(jQuery('#field-list').val() == "") {
                            jQuery('#field-list').val(mySelectedModule);
                        } else {
                            jQuery('#field-list').val(jQuery('#field-list').val()+';'+mySelectedModule);
                        }
                    }
                }
            });
        }
    });

    jQuery("#lvt-table").off('click').on('click', '.list-select', function (e) {
        swReplace = true;
       myListData =  jQuery(this).attr('data-record');
       arrListData = myListData.split('@');
       actualList = jQuery('#field-list').val().split(';');
       for (i = 0; i < actualList.length; i++ ){
           if(actualList[i] == arrListData[2] ){
               actualList[i] = myListData;
               swReplace = false;
               break
           }
       }
       if(swReplace){
           for (i = 0; i < actualList.length; i++ ){
               if(actualList[i].indexOf(arrListData[2]) ){
                   actualList[i] = myListData;
                   break
               }
           }
       }
       jQuery('#list-name-'+arrListData[2]).html(arrListData[1]);
        jQuery('#field-list').val(actualList.join(';'))
    })

}) (jQuery);
{/literal}
</script>
	<div class="md-content">
		<div class="panel panel-primary">
			<div class="panel-heading">Configuración de campos tabla.</div>
			<div class="panel-body" style="max-height: 800px; overflow-y: auto">
				<form method="post" action="index.php" onsubmit="AddGridFieldsUtils.addGridFields (); return false;" name="add-grid-field"  id="editGridValues">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="fldmodule" value="{$_FLD_MODULE}" />
					<input type="hidden" name="action" id="action" value="AddGridFields" />
					<input type="hidden" name="Ajax" value="true" />

                    <input type="hidden" name="idSelected"  id="idSelected" value="" />
                    <input type="hidden" id="valSelected" value="" />
                    <input type="hidden" name="importModuleReference" id="importModuleReference" value="" />


					<div class="wizard" id="myWizard">
					<div class="wizard-inner">
						<ul class="steps steps-table ">
							<li id="nav-li-1" class="active"><span id="nav-sp-1" class="badge badge-primary">1</span>Nombre<span class="chevron"></span></li>
                            <li id="nav-li-2" ><span  id="nav-sp-2" class="badge">2</span>Importar<span class="chevron"></span></li>
                            <li id="nav-li-3" ><span  id="nav-sp-3"  class="badge">3</span>Columnas<span class="chevron"></span></li>
							<li id="nav-li-4" ><span  id="nav-sp-4" class="badge">4</span>Acciones<span class="chevron"></span></li>
                            <li id="nav-li-5" ><span  id="nav-sp-5" class="badge">5</span>Aspecto<span class="chevron"></span></li>
						</ul>
						<div class="actions">
							<button type="button" class="btn btn-default btn-mini btn-prev hide" onclick="AddGridFieldsUtils.goToBackStep ();">
								&laquo;&nbsp;{$MOD.LBL_ANTERIOR}
							</button>
							&nbsp;
							<button data-last="Finish" class="btn btn-success btn-mini btn-next" id="button_next" type="button" onclick="AddGridFieldsUtils.goToForwardToStep ();">
                                {$MOD.LBL_SIGUIENTE}&nbsp;&raquo;
							</button>
							&nbsp;
							<button type="submit" class="btn btn-primary hide">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
							&nbsp;
							<button type="button" class="btn btn-danger md-close" id="btnclose" onclick="GridUtils.closeModal ();">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
					</div>
					</div>
						<div class="tab-content clearfix">
							<!-- Table name-->
							<div role="tabpanel" class="tab-pane active" id="nav-tab-1">
								<div class="row" style="margin: 12px 0px; min-height: 200px;padding: 0px  ">
									<div class="col-md-12" >
										<h4 class ="pull-left bloc-name">Nombre de la tabla  (<span style="color: red;">*</span> Requerido)</h4>
									</div>

								<div class="form-group col-md-12">
                                    <!-- <label for="grid-label"><span style="color: red;">*</span> Requerido</label>    {$MOD.LBL_ETIQUETA_GRID}   -->
						<input type="text" id="grid-label" name="etiquetaGrid" value="" class="form-control grid-label" maxlength="100" />
					</div>
								</div> <!-- row -->
							</div>
							<!-- Columnas manual -->
							<div role="tabpanel" class="tab-pane fade in" id="nav-tab-3">
								<div class="row" style="margin: 6px 0px 0px 0px; min-height: 200px  ">
                                    <div id="other-table-info"  class=" table-responsive col-xs-12" style="margin: 3px;"></div>
								<div class="col-md-12" >
									<h4 class ="pull-left bloc-name">Agregar Columnas a la tabla (<span style="color: red;">*</span> Requerido)</h4>
								</div>
								<div class="table-responsive col-md-12" style="top: -48px;">
						<table class="table" id="proTabFields">
                                        {$MOD.POS_CAMPO = 'Orden'}
										{$MOD.LBL_ETIQUETA_CAMPO= 'Columna'}
                                        {$MOD.LBL_NOMBRE_CAMPO = '&nbsp;'}
                                        {$MOD.LBL_ADD_CAMPO = ' Añadir columna'}
										{$NOMBRE_WIDTH = '10%'}
                                        {include file='Settings/GridManager/GridStep3Field.tpl' BLOCK_NAME='' BLOCK_NUMBER='1' FIELD_NAMES=array()}
						</table>
									<hr>
					</div>
					</div>
			</div>
							<!-- Columnas acciones -->
							<div role="tabpanel" class="tab-pane" id="nav-tab-4">
                                <div class="row" style="margin: 12px 0px; min-height: 200px ">
                                    <div class="col-md-12" style="display: inline">

                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                                    Vinculaciones <span class="caret"></span>
                                                </button>&nbsp;
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a href="#" onclick="AddGridFieldsUtils.linkFieldAction();">Vincular listas</a></li>
                                                    <li><a href="#" onclick="AddGridFieldsUtils.importFieldAction();">Importar Valores</a></li>
                                                </ul>
                                    </div>
										<div class="btn-group">
                                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                                    Activaciones <span class="caret"></span>
                                                </button>&nbsp;
											<ul class="dropdown-menu" role="menu">
                                                    <li><a href="#" onclick="AddGridFieldsUtils.checkFieldAction()">Acivar campos</a></li>
											</ul>
										</div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                                Fila Resumen <span class="caret"></span>
                                            </button>&nbsp;
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" onclick="AddGridFieldsUtils.summaryFieldAction()">Campos resumen</a></li>
                                            </ul>
                                        </div>
									</div>
                                    <!-- Vincular listas -->
								<div id="linkField"   class="table-responsive col-md-12 hide">
                                    <h5 class ="pull-left bloc-name">Vincular listas.</h5>
                                    <div class="pull-right">
                                        <button type="button" class="btn btn-danger"  onclick="AddGridFieldsUtils.removeActions ('tr-list-action');">Eliminar acciones de lista</button>
                                    </div>
                                    <table class="table action-fields" id="actionTable">
                                        <thead>
						<tr>
                                            <th>Campo Lista</th>
                                            <th>Valor de lista</th>
                                            <th>Vincular con:</th>
                                            <th>Campo Destino</th>
						</tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
					</table>
									<hr>
								</div>
                                    <!-- Importar Valores -->
                                    <div id="gridImportValues"   class="table-responsive col-md-12 hide">
                                        <h5 class ="pull-left bloc-name">Importar Valores.</h5>
                                        <div  id="div-modules-to-import" class="table-responsive hide col-md-12" style="margin: 12px 0px; padding: 0px 25px">
                                            <select id="select-modules-to-import" class="center-block form-control" title="Del modulo" onchange="AddGridFieldsUtils.searchFieldToImport (this)"></select>
                                        </div>
                                        <div class="pull-right">
                                            <button type="button" class="btn btn-danger"  onclick="AddGridFieldsUtils.removeImport ();">Eliminar Valores Importados</button>
                                        </div>
                                        <hr id="separator-line">
                                    </div>
                                    <!-- Aciones de checkbox -->
                                    <div id="checkField"   class="table-responsive col-md-12 hide">
                                        <h5 class ="pull-left bloc-name">Activar campos.</h5>
                                        <div class="pull-right">
                                            <button type="button" class="btn btn-danger"  onclick="AddGridFieldsUtils.removeActions ('tr-check-action');">Eliminar acciones de Checkbox</button>
                                        </div>
                                        <table class="table action-fields" id="actionCheck">
                                            <thead>
                                            <tr>
                                                <th>Campo Checkbox</th>
                                                <th>Estado al marcar</th>
                                                <th>Campo(s) afectado(s)</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                        <hr>
                                    </div>
                                    <div id="summaryField"   class="table-responsive col-md-12 hide">
                                        <h5 class ="pull-left bloc-name">Fila resumen.</h5>
                                        <div class="pull-right" style="margin-bottom: 12px">
                                            <button type="button" class="btn btn-danger"  onclick="AddGridFieldsUtils.removeSummary ('td-summary-action');">Eliminar fila resumen</button>
                                        </div>
                                        <table class="table table-bordered action-summary" id="actionSummary">
                                            <thead>

                                            </thead>
                                            <tbody>
                                            <tr class="tr-summary-action">
                                            </tr>
                                            </tbody>
                                        </table>
                                        <hr>
                                    </div>
                                </div>
							</div>
							<!-- Columnas desde otros grid -->
								<div role="tabpanel" class="tab-pane" id="nav-tab-2">
									<div class="row" style="margin: 12px 0px ">
										<div class="col-md-12" >
											<h4 class ="pull-left bloc-name">Agregar columnas de otra tabla (Opcional)</h4>
										</div>
                                        {if  $MODULE_WITH_GRID }
										<div class="col-md-12">
											<select class="form-control" id="moduleFieldId" name="moduleFieldId" >
												<option value="0">Seleccionar tabla</option>
												{foreach from=$MODULE_WITH_GRID key=k item=v}
													<option value="{$v.fieldid}@{$v.name}">{$v.tablabel}: {$v.fieldlabel}</option>
												{/foreach}
											</select>
										</div>
										<div class="col-md-12">&nbsp;</div>
										<div class="row">
											<div class="col-md-12" style="display: none">
												<input type="checkbox" id="column0" value="0" checked name="column[0]">
											</div>
											<div class="col-xs-12" id="mix-grid">&nbsp;</div>
										</div>
										{else}
										<div class="col-md-12" style="margin-top: 25px">
										<div class="alert alert-info alert-dismissable">
											<button type="button" class="close" data-dismiss="alert">&times;</button>
											No se encontraron tablas para combinar!
				</div>
			</div>
{/if}
		</div>
	</div>
                            <!-- Columnas Filtros color -->
                            <div role="tabpanel" class="tab-pane" id="nav-tab-5">
                                <div class="row" style="margin: 12px 0px; min-height: 200px ">
                                    <div class="col-md-12" >
                                        <div class="btn-toolbar" role="toolbar">
                                            <div class="btn-group-sm">
                                                <button type="button" class="btn btn-success" onclick="AddGridFieldsUtils.colorFilter();">Filtro de color</button>&nbsp;
                                                <button type="button" class="btn btn-success">Filtro de datos</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">

                                    </div>
                                    <div id="filterFieldTable"   class="table-responsive col-md-12 hide">
                                        <div class="pull-right">
                                            <button type="button" class="btn btn-success btn-sm addButton" onclick="AddGridFieldsUtils.colorFilter();"><i class="fa fa-plus" aria-hidden="true" style="margin-right: 1px"></i> Condición</button>&nbsp;
                                        </div>
                                        <table class="table action-fields" id="filterTable">
                                            <thead>
                                            <tr>
                                                <th width="20%">Columna a pintar</th>
                                                <th  width="10%">Color</th>
                                                <th width="20%">Columna condicionante</th>
                                                <th>Condición</th>
                                                <th>Valor</th>
                                                <th  width="10%">&nbsp</th>
                                                <th>&nbsp</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                        <hr>
                                    </div>
                                </div>
                            </div>
						</div> <!-- Tab content -->
					</div>
                    <input id="field-list" type="hidden" name="listaCampo" value="">
				</form>
			</div> <!-- panel body-->
		</div> <!-- Panel -->
	</div> <!-- /Contenet-->
<script type="text/html" id="field-template">
	{include file='Settings/GridManager/GridStep3FieldDetail.tpl'
	BLOCK_NUMBER=''
	FIELD_LABEL=''
	FIELD_LENGTH=''
	FIELD_MODULE=''
	FIELD_NAME=''
	FIELD_PRECISION=''
	FIELD_PREFIX=''
	FIELD_SEQUENCE=''
	FIELD_TYPE=1
	FIELD_VALUE=''
	VISIBLE=true
	}
</script>
    <script type="text/html" id="field-action-template">
        {include file='Settings/fieldActionTable.tpl' }
    </script>
    <script type="text/html" id="fieldCheck-action-template">
        {include file='Settings/fieldActionChk.tpl' }
    </script>
    <script type="text/html" id="filter-color-template">
        {include file='Settings/filterColorTable.tpl' }
    </script>
    <script type="text/html" id="grid-summary-template">
        {include file='Settings/summaryActionTable.tpl' }
    </script>
    <script type="text/html" id="import-module-fields-template">
        {include file='Settings/GridManager/moduleRelationShip.tpl' }
    </script>
    <script type="text/html" id="import-module-fields-tabla-template">
    <table class="table action-fields" data-module="__DATA_MODULE__" id="importFieldsTable-__ID__">
        <thead>
        <tr>
            <th>Importar el valor del Campo</th>
            <th>A la Columna</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <tr id="import-bar-__ID__" class="import-bar hide">
            <td colspan="3" align="center" style="padding: 1px;">
                <div class="center-block">
                <button type="button" class="btn btn-primary btn-icon " data-target="#importFieldsTable-__ID__" onclick="AddGridFieldsUtils.addImportRelationship (this);"><i class="fa fa-plus"></i></button>
                <button type="button" class="btn btn-danger btn-icon" data-target="#importFieldsTable-__ID__" onclick="AddGridFieldsUtils.deleteImportRelationship (this);"><i class="fa fa-minus"></i></button>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    </script>
{/strip}