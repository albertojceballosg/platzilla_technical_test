(function (jQuery) {
	// Private variables
	var modal                     = null,
		gridProperties            = null,
		indexField                = -1,
		swCalculatedMsn           = true,
		configCalculatedFieldLink = null,
        tableImportIndex          = 0;

	// Private methods
	var getFieldsFromModules = function (moduleName) {
		var fieldsObject = '',
			arguments    = [
				'module=Settings',
				'action=SettingsAjax',
				'file=GetAvailableFieldsData',
				'modulename=' + encodeURIComponent (moduleName),
				'Ajax=true'
			];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			async:    false,
			dataType: 'json',
			method:   'get'
		}).done (function (response) {
			fieldsObject = response;
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error: ' + jQueryResponse.responseText);
		});
		return fieldsObject;
	};

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
		gridProperties = null
	};

	var onFailureHandler = function (jQueryResponse) {
		alert ('Se ha presentado un error: ' + jQueryResponse.responseText);
	};

	var onGetPropertiesSuccessHandler = function (response) {
		var modalTemplate = jQuery ('#grid-properties-modal-template');
		if (!response) {
			alert ('Se ha recibido una respuesta inesperada. Intenta más tarde');
			return;
		}

		modal = jQuery (modalTemplate.html ());
		setGridProperties (response);
		setCalculatedLink (response);
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	// Private methods
	var selectDefaultType = function (row, defaultType) {
		var defaultValue, defaultSelect, propertiesTd;
		propertiesTd = row.find ('td').eq (4);
		defaultValue = propertiesTd.find ('input').eq (7);
		defaultSelect = propertiesTd.find ('select').eq (2);

		if (defaultType == '') {
			defaultValue.attr ('disabled', false);
			defaultValue.addClass ('hide');
			defaultSelect.attr ('disabled', true);
			defaultSelect.addClass ('hide');
			return false;
		}

		defaultValue.attr ('placeholder', 'Valor inicial');
		if (defaultType == 'valor') {
			defaultValue.attr ('disabled', false);
			defaultValue.removeClass ('hide');

			defaultSelect.attr ('disabled', true);
			defaultSelect.addClass ('hide');
		} else {
			defaultValue.attr ('disabled', true);
			defaultValue.addClass ('hide');

			defaultSelect.attr ('disabled', false);
			defaultSelect.removeClass ('hide');
		}
	};

	var setImportModulesList = function () {
        searchInGridByAction ();
        modal.find ('#select-modules-to-editImport').empty ();
        modal.find ('#select-modules-to-editImport').append (
            jQuery (
                '<option>',
                {
                    value: '',
                    text:  'Selecionar columna'
                }
            )
        );
        for (var f = 0; f < gridProperties.typeField.length; f++) {
            if (gridProperties.typeField[ f ] == 10) {
                modal.find ('#select-modules-to-editImport').append (
                    jQuery (
                        '<option>',
                        {
                            value: gridProperties.moduleField[ f ],
                            text:  gridProperties.labelField[ f ]
                        }
                    )
                );
                moduleName = gridProperties.moduleField[ f ];
            }
        }
	};

	var editImportFieldAction = function () {
		var moduleName = '',
			numModule  = 0;

		if (gridProperties.swImportAction) {
			if (confirm ("¡Esta operación eliminará todas los campos importados anteriores! ¿Continuar?")) {
                removeEditImport();
			} else {
				return;
			}
		}
		searchInGridByAction ();
		if (jQuery.inArray ('10', gridProperties.typeField) == -1) {
			alert ('¡No hay columnas tipo modulo relacionado para importar!');
			return;
		} else if ((jQuery.inArray ('1', gridProperties.typeField) == -1) && (jQuery.inArray ('7', gridProperties.typeField) == -1) && (jQuery.inArray ('9', gridProperties.typeField) == -1)) {
			alert ('¡Se requiere de al menos un campo tipo texto o numerico ');
			return;
		}
        setImportModulesList ();
        modal.find ('#div-modules-to-editImport').removeClass ('hide');
        modal.find ('#gridEditImportValues').removeClass ('hide');
	};

	var searchFieldToEditImport = function (obj) {
        var gridImportValues = jQuery('#gridEditImportValues'),
            moduleName       = jQuery(obj).val(),
            module           = jQuery(obj).find ('option:selected').text (),
            importTable      = jQuery ('#import-module-fields-edit-tabla-template').html().replace (/__ID__/g, tableImportIndex).replace (/__DATA_MODULE__/g, moduleName),
            importModule     = jQuery ('#importEditModuleReference'),
            moduleReference  = importModule.val ().split(';'),
            r                = false,
            tableImport;
        if (moduleName === '') {
            return;
        }
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
        jQuery (importTable).insertBefore('#separator-edit-line');
		searchInGridByAction ();
		setEditImportFieldFromModule (moduleName);
	};

	var setEditImportFieldFromModule = function (moduleName) {
		var templateContents = jQuery ('#grid-RelationShip-edit-template').html().replace (/__MODULE__/g, moduleName),
			table            = jQuery (modal.find ('#editGridImportValuesTable-' + tableImportIndex)),
			row, listFieldName, listFieldLabel, listValue, option, op,
            importModule     = modal.find ('#importEditModuleReference'),
            moduleReference;

		if (!templateContents) {
			return;
		}

		listValue = getFieldsFromModules (moduleName);
		if (listValue !== '') {
			listFieldName = Object.keys (listValue);
			listFieldLabel = Object.values (listValue);
			row = jQuery (templateContents);
			option = row.find ('select').eq (0);
			for (op = 0; op < listFieldName.length; op++) {
				option.append (jQuery ('<option>', {
					value: listFieldName[ op ],
					text:  listFieldLabel[ op ]
				}));
			}
			option = row.find ('select').eq (1);
			for (op = 0; op < gridProperties.labelField.length; op++) {
				if (jQuery.inArray (gridProperties.typeField[ op ], [ '1', '7', '9' ]) !== -1) {
					option.append (jQuery ('<option>', {
						value: gridProperties.nameField[ op ],
						text:  gridProperties.labelField[ op ]
					}));
				}
			}
            if (importModule.val () === '') {
                importModule.val (moduleName);
            } else {
                moduleReference = importModule.val ().split(';');
                moduleReference.push(moduleName);
                importModule.val (moduleReference.join(';'));
            }
			row.insertBefore (modal.find ('#edit-import-bar-' + tableImportIndex));
			modal.find ('.edit-import-bar').removeClass ('hide');
			modal.find ('#gridEditImportValues').removeClass ('hide');
			gridProperties.swImportAction = true;
            tableImportIndex += 1;
		}
	};

	var getGridEditTable = function (id, gridModule) {
		var arguments = [
			'module=Settings',
			'action=SettingsAjax',
			'file=gridToEspecialFields',
			'parenttab=Settings',
			'moduleFieldId=' + encodeURIComponent (id),
			'gridModule=' + encodeURIComponent (gridModule),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			async:  false,
			data:   arguments.join ('&'),
			method: 'post'
		}).done (function (response) {
			var gridTable, row, myDisplay, changeDisplay, myId, changeId;
			gridTable = response;
			row = jQuery (gridTable);
			myDisplay = row.find ('.btn-reg-modal').attr ('data-display-field-id');
			changeDisplay = myDisplay.replace ("valSelected", "valEditSelected");
			gridTable = gridTable.replace (myDisplay, changeDisplay);
			myId = row.find ('.btn-reg-modal').attr ('data-field-id');
			changeId = myId.replace ("idSelected", "idEditSelected");
			gridTable = gridTable.replace (myId, changeId);
			modal.find ('#mix-edit-grid').html (gridTable);
		}).fail (onFailureHandler);
	};

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

	var isGridEditFormValid = function (form) {
		var fields = form.find ('input.field-name'),
			row, field, type, value, i, n, labels, names, values, label, name;

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
					fieldsTypes    = [];
				jQuery ("select[name='tipoCampo[]']").each (function () {
					fieldsTypes.push (jQuery (this).val ());
				});

				for (var k = 0; k < fieldsTypes.length; k++) {
					if (jQuery.inArray (fieldsTypes[ k ], [ '7', '9' ]) !== -1) {
						totalNumFields += 1;
					}
				}
				if (totalNumFields > 1) {
					if (swCalculatedMsn) {
						alert ('Las operciones de cálculo para la columna "' + value + '" se deben crear en el motor de cálculos del sistema! ');
						swCalculatedMsn = false;
					}
				} else {
					alert ('Las columnas tipo "Campos de Calculos" requieren de al menos dos columnas tipos Número o (%)  adicionales ');
					field.focus ();
					return false;
				}
			}
		}
		return true;
	};

	var searchInGridByAction = function () {
		gridProperties.labelField = [];
		gridProperties.typeField = [];
		gridProperties.valueField = [];
		gridProperties.nameField = [];
		gridProperties.moduleField = [];
		modal.find ("input[name='etiquetaCampo[]']").each (function () {
			gridProperties.labelField.push (jQuery (this).val ());
		});
		modal.find ("input[name='nombreCampo[]']").each (function () {
			gridProperties.nameField.push (jQuery (this).val ());
		});
		modal.find ("select[name='tipoCampo[]']").each (function () {
			gridProperties.typeField.push (jQuery (this).val ());
		});
		modal.find ("textarea[name='valoresCampo[]']").each (function () {
			gridProperties.valueField.push (jQuery (this).val ());
		});
		modal.find ("select[name='moduloCampo[]']").each (function () {
			gridProperties.moduleField.push (jQuery (this).val ());
		});
	};

	var setCalculatedLink = function (response) {
		if (response.calculatedSystem === null) {
			modal.find ('#Link-to-calculation').closest ('.panel').addClass ('hidden');
			return false;
		}
		var calculatedList, li,
			row           = '',
			selectedValue = response [ 'calculationid' ],
			template      = modal.find ('#calculate-template'),
			divList       = modal.find ('.calculated-list');
		calculatedList = jQuery.parseJSON (response.calculatedSystem);
		for (li = 0; li < calculatedList.length; li++) {
			row = template.clone ().attr ('id', 'cs-' + li).attr ('rel', calculatedList[ li ].calculationName).attr ('title', calculatedList[ li ].description)
						  .html (calculatedList[ li ].name).removeClass ('hide');
			if (calculatedList[ li ].calculationName == selectedValue) {
				row.addClass ('active')
			}
			row.appendTo (divList);
		}
	};

	var setGridCheckAction = function () {
		var f, k, op, option, valueCheckBox, valueOptions, valueOptionArray, preId;
		for (f = 0; f < gridProperties.typeField.length; f++) {
			if (gridProperties.typeField[ f ] == 56) {
				valueOptions = Object.values (gridProperties.actionField[ f ]);
				valueCheckBox = Object.keys (gridProperties.actionField[ f ]);
				if (valueOptions[ 0 ] !== undefined) {
					if (valueOptions[ 0 ] !== '' && valueOptions[ 0 ].indexOf (',')) {
						valueOptionArray = valueOptions[ 0 ].split ('_');
						valueOptions[ 0 ] = valueOptions[ 0 ].replace (/,/g, '_' + valueOptionArray[ (valueOptionArray.length - 1) ] + ',');
						valueOptions[ 0 ] = valueOptions[ 0 ].replace (/,/g, ',' + gridProperties.nameField[ f ] + '@');
						valueOptions[ 0 ] = gridProperties.nameField[ f ] + '@' + valueOptions[ 0 ];
					}
				}
				for (k = 0; k < valueCheckBox.length; k++) {
					var templateContents = jQuery ('#edit-check-action-template').html (),
						table            = modal.find ('#editActionCheck'),
						row;
					if (!templateContents) {
						return;
					}

					row = jQuery (templateContents);
					row.find ('input').eq (0).attr ('name', 'checkField[]').val (gridProperties.labelField[ f ]);
					row.find ('select').eq (0).attr ('name', 'checkValue[]').val (valueCheckBox[ k ]);
					row.find ('input').eq (1).attr ('name', 'checkNameField[]').val (gridProperties.nameField[ f ]);
					option = row.find ('select').eq (1).attr ('name', 'checkFieldDest[]').attr ('onchange', 'GridPropertiesUtils.checkGridSelection (this);');
					for (op = 0; op < gridProperties.labelField.length; op++) {
						if ((gridProperties.typeField[ op ] != 2202) && (gridProperties.typeField[ op ] != 2203) && (gridProperties.typeField[ op ] != 2204)) {
							preId = (gridProperties.typeField[ op ] == 5) ? 'jscal_field_' : '';
							option.append (jQuery ('<option>', {
									value: gridProperties.nameField[ f ] + '@' + preId + gridProperties.nameField[ op ],
									text:  gridProperties.labelField[ op ]
								})
							);
						}
					}
					if (valueOptions[ 0 ] !== undefined) {
						option.val (valueOptions[ 0 ].split (','));
					} else {
						option.val ('')
					}
					table.append (row);
					modal.find ('#editCheckField').removeClass ('hide');
					gridProperties.swChktField = true;
				}
			}
		}
	};

	var setGridColorFilter = function () {
		var i, c, option, op;
		for (i = 0; i < gridProperties.filterColor.length; i++) {
			if (gridProperties.filterColor[ i ] != null) {
				for (c = 0; c < gridProperties.filterColor[ i ].length; c++) {
					var templateContents = jQuery ('#edit-filter-color-template').html (),
						table            = modal.find ('#editFilterTable'),
						row;
					if (!templateContents) {
						return;
					}
					row = jQuery (templateContents);
					row.find ('button').eq (0).attr ('onclick', 'GridPropertiesUtils.removeRowColor(this);');
					row.find ('input').eq (0).attr ('name', 'selectedColor[]').val (gridProperties.filterColor[ i ][ c ].color);
					row.find ('input').eq (1).attr ('name', 'selectedValue[]').val (gridProperties.filterColor[ i ][ c ].value);
					option = row.find ('select').eq (0);
					for (op = 0; op < gridProperties.labelField.length; op++) {
						if (jQuery.inArray (gridProperties.typeField[ op ], [ '1', '7', '9', '21' ]) !== -1) {
							option.append (jQuery ('<option>', {
								value: gridProperties.nameField[ op ],
								text:  gridProperties.labelField[ op ]
							}));
						}
					}
					option.val (gridProperties.nameField[ i ]);
					option = row.find ('select').eq (1);
					for (op = 0; op < gridProperties.labelField.length; op++) {
						if (jQuery.inArray (gridProperties.typeField[ op ], [ '1', '7', '9', '21' ]) !== -1) {
							option.append (jQuery ('<option>', {
								value: gridProperties.nameField[ op ],
								text:  gridProperties.labelField[ op ]
							}));
						}
					}
					option.val (gridProperties.filterColor[ i ][ c ].field);
					option = row.find ('select').eq (2).val (gridProperties.filterColor[ i ][ c ][ 'condition' ]);
					option = row.find ('select').eq (3).val (gridProperties.filterColor[ i ][ c ].join);
					table.append (row);
				}
			}
		}
		modal.find ('#editFilterFieldTable').removeClass ('hide');
	};

	var setGridFieldColumn = function () {
		var k, inField, option, propertiesTd, defaultSelect;

		for (k = 0; k < gridProperties.nameField.length; k++) {
			inField = true;
			indexField = k;
			modal.find ('.field-name').each (function () {
				if (jQuery (this).val () == gridProperties.nameField[ k ]) {
					inField = false;
					indexField = -1;
				}
			});
			if (inField) {
				var templateContents = jQuery ('#field-edit-template').html (),
					table            = modal.find ('#lvt-edit-table'),
					defaultValue,
					row;
				if (!templateContents) {
					return;
				}
				row = jQuery (templateContents);
				row.find ('.block-number').val (1);
				row.find ('.import-code').val (0);
				row.find ('.field-label').val (gridProperties.labelField[ k ]).attr ('readonly', true);
				row.find ('.field-name').val (gridProperties.nameField[ k ]);
				row.find ('.cod-field').val (gridProperties.subfieldsidField[ k ]);
				row.find ('.sequence-field').val (gridProperties.sequenceField[ k ]);
				option = row.find ('.field-type');
				option.val (gridProperties.typeField[ k ]);
				updateFieldGridPropertiesUI (row, gridProperties.typeField[ k ]);
				if (gridProperties.typeField[ k ] == '2204') {
					row.find ('.config').removeClass ('hide');
				}
				propertiesTd = row.find ('td').eq (4);
				defaultValue = propertiesTd.find ('input').eq (7);
				defaultSelect = propertiesTd.find ('select').eq (2);
				if (!defaultValue.hasClass ('hide')) {
					defaultValue.val (gridProperties.defaultValue[ k ]);
				} else if ((gridProperties.defaultValue[ k ] == 'edit') || (gridProperties.defaultValue[ k ] == 'create')) {
					defaultSelect.val (gridProperties.defaultValue[ k ]);
				} else if (gridProperties.typeField[ k ] == 5) {
					selectDefaultType (row, 'valor');
					defaultValue.val (gridProperties.defaultValue[ k ]);
				}
				table.append (row);
				indexField = -1;
			}
		}
	};

	var setGridImportAction = function () {
		var templateContents,
            importTable,
			table,
			moduleFound = false,
			row, listFieldName, listFieldLabel, listValue, selected = '', f, op, k, moduleFieldValue, FieldGridName, moduleAndField, moduleName, option;

        setImportModulesList ();
        for (f = 0; f < gridProperties.modulesImportAction.length; f++) {
        	moduleFieldValue = Object.values (gridProperties.modulesImportAction [f]);
        	FieldGridName    = Object.keys (gridProperties.modulesImportAction[f]);
        	moduleAndField   = moduleFieldValue[0].split('@');
        	importTable      = jQuery ('#import-module-fields-edit-tabla-template').html().replace (/__ID__/g, f).replace (/__DATA_MODULE__/g, moduleAndField [0]);
        	templateContents = jQuery ('#grid-RelationShip-edit-template').html().replace(/__MODULE__/g, moduleAndField [0]);
        	modal.find('#div-modules-to-editImport').removeClass ('hide');
            modal.find('#gridEditImportValues').removeClass ('hide');
            jQuery(importTable).insertBefore (modal.find ('#separator-edit-line'));
            table     = jQuery (modal.find ('#importEditFieldsTable-' + f));
            listValue = getFieldsFromModules (moduleAndField [0]);
            if (listValue !== '') {
            	listFieldName  = Object.keys(listValue);
            	listFieldLabel = Object.values(listValue);
            	for (k = 0; k < moduleFieldValue.length; k++) {
            		selected       = '';
            		moduleAndField = moduleFieldValue[k].split('@');
            		row            = jQuery(templateContents);
            		option         = row.find ('select').eq (0);
            		for (op = 0; op < listFieldName.length; op++) {
            			if (listFieldName [ op ] === moduleAndField [ 1 ]) {
            				option.append (jQuery ('<option>', {
            					value: listFieldName [ op ],
								text: listFieldLabel [ op ]
							}).attr ('selected', true));
            			} else {
            				option.append (jQuery('<option>', {
            					value: listFieldName [ op ],
								text: listFieldLabel [ op ]
							}));
            			}
            		}
                        option = row.find ('select').eq (1);
                        for (op = 0; op < gridProperties.typeField.length; op++) {
                            if (jQuery.inArray (gridProperties.typeField [ op ], ['1', '7', '9']) !== -1) {

                                if (FieldGridName [ k ] === gridProperties.nameField [ op ]) {
                                    option.append (jQuery('<option>', {
                                        value: gridProperties.nameField [ op ],
                                        text: gridProperties.labelField [ op ]
                                    }).attr('selected', true));
                                } else {
                                    option.append (jQuery('<option>', {
                                        value: gridProperties.nameField [ op ],
                                        text: gridProperties.labelField [ op ]
                                    }));
                                }
                            }
                        }
                        if (k > 0) {
                            row.find ('button').eq(0).removeClass ('hide')
                        }
                        row.insertBefore(modal.find('#edit-import-bar-' + f));
                        modal.find('.edit-import-bar').removeClass('hide');
                    }
                }
                tableImportIndex += 1;
        }
        modal.find ('#gridEditImportValues').removeClass ('hide');
        if (gridProperties.modulesReference.length > 1){
            modal.find('#importEditModuleReference').val (gridProperties.modulesReference.join(';'));
		} else {
            modal.find('#importEditModuleReference').val (gridProperties.modulesReference [ 0 ]);
		}
        tableImportIndex += 1;
        gridProperties.swImportAction = true;
	};

	var setGridImportColumn = function (seq, cod) {
		var k, dataField, inField, option;

		for (k = 0; k < gridProperties.gridSelectedField.length; k++) {
			dataField = gridProperties.gridSelectedField[ k ].split ('@');
			inField = true;
			modal.find ('.field-name').each (function () {
				if (jQuery (this).val () == dataField[ 1 ]) {
					inField = false;
				}
			});

			if (inField) {
				var templateContents = jQuery ('#field-edit-template').html (),
					table            = modal.find ('#lvt-edit-table'),
					row;
				if (!templateContents) {
					return;
				}

				row = jQuery (templateContents);

				if (seq != undefined) {
					row.find ('.sequence-field').val (seq);
				}
				if (cod != undefined) {
					row.find ('.cod-field').val (cod);
				}
				row.find ('.block-number').val (1);
				row.find ('.import-code').val (dataField[ 3 ]);
				row.find ('.import-reg').val (dataField[ 2 ]);
				row.find ('.field-label').val (dataField[ 0 ]).attr ('readonly', true);
				row.find ('.field-name').val (dataField[ 1 ]);
				option = row.find ('.field-type');
				option.append (jQuery ('<option>', {
					value: '2202',
					text:  'Importada'
				}));
				option.val ('2202');
				updateFieldGridPropertiesUI (row, 2202);
				table.append (row);

			}
		}
	};

	var setGridListActions = function () {
		var f, k, listText, listValue, actionText, option;
		for (f = 0; f < gridProperties.typeField.length; f++) {
			if (gridProperties.typeField[ f ] == 15) {
				listText = Object.values (gridProperties.valueField[ f ]);
				listValue = Object.keys (gridProperties.valueField[ f ]);
				actionText = Object.values (gridProperties.actionField[ f ]);
				for (k = 0; k < listText.length; k++) {
					if (listText[ k ] != 'Seleccionar') {
						var templateContents = jQuery ('#edit-action-template').html (),
							table            = modal.find ('#editActionTable'),
							row              = table.find ('tbody > tr');
						if (!templateContents) {
							return;
						}
						row = jQuery (templateContents);
						row.find ('input').eq (0).attr ('name', 'selectField[]').val (gridProperties.labelField[ f ]);
						row.find ('input').eq (1).attr ('name', 'selectValue[]').val (listText[ k ]);
						row.find ('input').eq (2).attr ('name', 'selectNameField[]').val (gridProperties.nameField[ f ]);
						row.find ('select').eq (0).attr ('data-select', 'destinationField_' + f + '_' + k).val (listValue[ k ]).attr ('onchange', 'GridPropertiesUtils.checkGridSelection (this);');
						option = row.find ('select').eq (1).attr ('name', 'destinationField[]').attr ('id', 'destinationField_' + f + '_' + k);
						for (var op = 0; op < gridProperties.labelField.length; op++) {
							if ((gridProperties.labelField[ op ] != gridProperties.labelField[ f ]) && (gridProperties.typeField[ op ] == 1)) {
								option.append (jQuery ('<option>', {
									value: gridProperties.nameField[ op ],
									text:  gridProperties.labelField[ op ]
								}));
							}
						}
						option.val (actionText[ (k - 1) ]);
						table.append (row);
						modal.find ('#editLinkField').removeClass ('hide');
					}
				}
			}
		}
	};

	var setGridProperties = function (response) {
		if (!response.hasOwnProperty ('grid')) {
			modal.find ('#grid-properties').addClass ('hide').closest ('.panel').addClass ('hidden');
			return;
		}
		modal.find ('input[name="fieldId"]').val (response.id);
		modal.find ('#grid-properties').removeClass ('collapse');
		modal.find ('.MoveableEditRow').remove ();
		gridProperties = {
			actionField:          [],
			filterColor:          [],
			gridSelectedField:    [],
			historyReferntModule: [],
			labelField:           [],
			lengthField:          [],
			nameField:            [],
			precisionField:       [],
			relmoduleField:       [],
			sequenceField:        [],
			subfieldsidField:     [],
			swCheckAction:        false,
			swColorFilter:        false,
			swListAction:         false,
			swSummaryAction:      false,
			swImportAction:       false,
			typeField:            [],
			valueField:           [],
			summaryRow:           [],
			moduleField:          [],
			defaultValue:         [],
			modulesReference:     [],
			modulesImportAction:  []
		};

		response.grid.each (function (element, index, array) {
			gridProperties.labelField.push (element.label);
			gridProperties.lengthField.push (element.length);
			gridProperties.nameField.push (element.name);
			gridProperties.precisionField.push (element.precision);
			gridProperties.relmoduleField.push (element.relmodule);
			gridProperties.sequenceField.push (element.sequence);
			gridProperties.subfieldsidField.push (element.subfieldsid);
			gridProperties.typeField.push (element.uitype);
			gridProperties.valueField.push (element.values);
			gridProperties.defaultValue.push (element.defaultvalue);

			if (element [ 'uitype' ] == '15') {
				if ((element.action_field != '') && (element.action_field != null) && (element.action_field != 'false')) {
					if (typeof element.action_field != 'object') {
						array[ index ].action_field = jQuery.parseJSON (element.action_field);

					} else {
						array[ index ].action_field = element.action_field;
					}
				}
				gridProperties.swListAction = true;
				setGridFieldColumn ();
			} else if (element [ 'uitype' ] == '56') {
				if ((element.action_field != '') && (element.action_field != null) && (element.action_field != 'false')) {
					gridProperties.swCheckAction = true;
					if (typeof element.action_field != 'object') {
						array[ index ].action_field = jQuery.parseJSON (element.action_field);
						gridProperties.swCheckAction = true;
					} else {
						array[ index ].action_field = element.action_field;
					}
				}
				setGridFieldColumn ();
			} else if (element [ 'uitype' ] == '10') {
				array[ index ].action_field = jQuery.parseJSON (element.action_field);
				if ((array[ index ].action_field != null) && (array[ index ].action_field != false)) {
					gridProperties.swImportAction = true;
					gridProperties.modulesImportAction.push (array[ index ].action_field);
					gridProperties.modulesReference.push(array[ index ].relmodule);
				}
				setGridFieldColumn ();
			} else if (element.uitype == '2202') {
				gridProperties.gridSelectedField.push (element.label + '@' + element.name + '@' + element.data_field);
				setGridImportColumn (element.sequence, element.subfieldsid);
			} else if (element.uitype == '2203') {
				gridProperties.summaryRow = jQuery.parseJSON (element.data_field);
				if (( gridProperties.summaryRow != null) && ( gridProperties.summaryRow != false) && ( gridProperties.summaryRow != '')) {
					gridProperties.swSummaryAction = true;
				}
			} else if (element.uitype == '2204') {
				configCalculatedFieldLink = 'index.php?module=calculated_fields&action=addGridCalculatedField&record=' + element.fieldid + '&subRecord=' + element.subfieldsid + '&parenttab=Settings';
				setGridFieldColumn ();
			} else {
				setGridFieldColumn ();
			}

			gridProperties.actionField.push (element.action_field);
			if ((element.filter_field != '') && (element.filter_field != null)) {
				gridProperties.swColorFilter = true;
				if (typeof element.filter_field != 'object') {
					array[ index ].filter_field = jQuery.parseJSON (element.filter_field);
				} else {
					array[ index ].filter_field = element.filter_field;
				}
			}
			gridProperties.filterColor.push (element.filter_field);
		});
		if (gridProperties.swListAction) {
			setGridListActions ();
		}
		if (gridProperties.swColorFilter) {
			setGridColorFilter ();
		}
		if (gridProperties.swCheckAction) {
			setGridCheckAction ();
		}
		if (gridProperties.swImportAction) {
			setGridImportAction ();
		}
		if (gridProperties.swSummaryAction) {
			gridProperties.swSummaryAction = false;
			GridPropertiesUtils.summaryEditFieldAction ();
		}
		modal.find ('.nav-tabs a[href="#tab-columnas"]').tab ('show');
		modal.find ('.modal-footer button.non-grid-stuff').addClass ('hidden');
		modal.find ('.modal-footer button.grid-stuff').removeClass ('hidden');
		modal.find ('#basic-properties').closest ('.panel').addClass ('hidden');
		modal.find ('#validation-properties').closest ('.panel').addClass ('hidden');
		modal.find ('#module-references-properties').closest ('.panel').addClass ('hidden');
		modal.find ('#picklist-values-properties').closest ('.panel').addClass ('hidden');
		modal.find ('#dependencies-properties').closest ('.panel').addClass ('hidden');
		modal.find ('#grid-properties').closest ('.panel').removeClass ('hidden');

		modal.find ('#lvt-edit-table').on ('change', '.search-list', function () {
			var mySelectedModule = jQuery (this).val (),
				clearSelect      = [],
				actualList, objTd, objNextTd;

			if (jQuery.inArray (mySelectedModule, gridProperties.historyReferntModule) === -1) {
				gridProperties.historyReferntModule.push (mySelectedModule);
			}

			actualList = modal.find ('#field-list-edit').val ().split (';');
			modal.find ("select[name='moduloCampo[]']").each (function () {
				clearSelect.push (jQuery (this).val ())
			});

			gridProperties.historyReferntModule.each (function (element) {
				if (jQuery.inArray (element, clearSelect) === -1) {
					modal.find ('#list-' + element).remove ();
				}
			});

			actualList.each (function (element, index) {
				var swRemove = true,
					k;

				for (k = 0; k < clearSelect.length; k++) {
					if (element.indexOf (clearSelect[ k ]) !== -1) {
						swRemove = false;
						break
					}
				}
				if (swRemove) {
					actualList.splice (index, 1);
				}
			});

			modal.find ('#field-edit-list').val (actualList.join (';'));

			if (mySelectedModule == "-") {
				return;
			}

			objTd = (jQuery (this)).parent ();
			objNextTd = objTd.next ();
			jQuery.ajax ({
				url:     'index.php?module=Settings&action=SettingsAjax&file=getRelatedListToGrid&parenttab=Settings&ajax=true',
				data:    'selectedModule=' + mySelectedModule,
				method:  "POST",
				success: function (data) {
					var dummy;
					if (data == 'false') {
						return;
					}
					jQuery (data).appendTo (objNextTd);
					dummy = modal.find ('#field-list-edit');
					if (dummy.val () == "") {
						dummy.val (mySelectedModule);
					} else {
						dummy.val (dummy.val () + ';' + mySelectedModule);
					}
				}
			});
		}).on ('click', '.list-select', function () {
			var swReplace   = true,
				myListData  = jQuery (this).attr ('data-record'),
				arrListData = myListData.split ('@'),
				actualList  = modal.find ('#field-list-edit').val ().split (';'),
				i;

			for (i = 0; i < actualList.length; i++) {
				if (actualList[ i ] == arrListData[ 2 ]) {
					actualList[ i ] = myListData;
					swReplace = false;
					break
				}
			}
			if (swReplace) {
				for (i = 0; i < actualList.length; i++) {
					if (actualList[ i ].indexOf (arrListData[ 2 ])) {
						actualList[ i ] = myListData;
						break
					}
				}
			}
			modal.find ('#list-name-' + arrListData[ 2 ]).html (arrListData[ 1 ]);
			modal.find ('#field-list-edit').val (actualList.join (';'))
		})
	};

	var updateFieldGridPropertiesUI = function (row, selectedFieldType) {
		var field, valueText, textVal, arrayFieldModule, referenList, dummy,
			fieldType = isNaN (selectedFieldType) ? 1 : parseInt (selectedFieldType);

		field = row.find ('.field-length');
		if (jQuery.inArray (fieldType, [ 1, 7, 9, 71 ]) !== -1) {
			field.css ('display', 'inline');
			selectDefaultType (row, 'valor');
			if (jQuery.inArray (fieldType, [ 7, 9, 71 ]) !== -1) {
				if (gridProperties.lengthField.length > 0 && indexField != -1) {
					field.val (gridProperties.lengthField[ indexField ]);
				} else {
					field.val (18);
				}
			} else {
				if (gridProperties.lengthField.length > 0 && indexField != -1) {
					field.val (gridProperties.lengthField[ indexField ]);
				} else {
					field.val ('');
				}
			}
		} else if (fieldType == 5) {
			selectDefaultType (row, 'hoy');
			field.css ('display', 'none');
		} else if (fieldType == 21) {
			selectDefaultType (row, 'valor');
			field.css ('display', 'none');
		} else {
			field.css ('display', 'none');
			selectDefaultType (row, '');
		}

		field = row.find ('.field-values');
		if (jQuery.inArray (fieldType, [ 15, 33 ]) !== -1) {
			field.css ('display', 'inline');
			if (gridProperties.valueField.length > 0 && indexField != -1) {
				valueText = Object.values (gridProperties.valueField[ indexField ]);
				textVal = '';
				valueText.each (function (element) {
					if (element != 'Seleccionar') {
						if (textVal != '') {
							textVal += '\n' + element.replace (/^\s+|\s+$/gm, '');
						} else {
							textVal = element.replace (/^\s+|\s+$/gm, '');
						}
					}
				});
				field.val (textVal);
			} else {
				field.val ('');
			}
		} else {
			field.css ('display', 'none');
		}

		field = row.find ('.field-modules');
		if (jQuery.inArray (fieldType, [ 10, 404 ]) !== -1) {
			var moduleName = '';
			field.css ('display', 'inline');
			if (gridProperties.relmoduleField.length > 0 && indexField != -1) {
				if (gridProperties.relmoduleField[ indexField ].indexOf ('@') !== -1) {
					arrayFieldModule = gridProperties.relmoduleField[ indexField ].split ('@');
					referenList = arrayFieldModule[ 3 ] + '@' + arrayFieldModule[ 1 ] + '@' + arrayFieldModule[ 2 ];
					moduleName = arrayFieldModule[ 2 ];
				} else {
					referenList = '';
					moduleName = gridProperties.relmoduleField[ indexField ];
				}
				dummy = modal.find ('#module-list-edit');
				if (dummy.val () == '') {
					dummy.val (referenList)
				} else {
					dummy.val (dummy.val () + ';' + referenList)
				}
				field.val (moduleName);
			}
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
			if (gridProperties.precisionField.length > 0 && indexField != -1) {
				field.val (gridProperties.precisionField[ indexField ]);
			} else {
				field.val (2);
			}
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

	// Public methods
	var editGridProperties = function (moduleName, fieldName) {
		jQuery.ajax ('index.php?module=Settings&action=SettingsAjax&file=GetFieldProperties&ajax=true&modulename=' + encodeURIComponent (moduleName) + '&fieldname=' + encodeURIComponent (fieldName), {
			dataType: 'json',
			method:   'get'
		}).done (onGetPropertiesSuccessHandler).fail (onFailureHandler);
	};

	var addEditImportRelationship = function (obj) {
		var table = modal.find (jQuery(obj).attr ('data-target')),
			tr    = table.find ('#grid-editRelationship');
		tr.clone ().insertBefore (table.find ('.edit-import-bar')).find ('button').eq (0).removeClass ('hide');
	};

	var deleteEditImport = function (obj) {
		((jQuery (obj)).parent ()).parent ().remove ()
	};

    var deleteEditImportRelationship = function (obj) {
        var table      = jQuery (jQuery(obj).attr ('data-target')),
            moduleName = table.attr ('data-module'),
            importModule     = modal.find  ('#importEditModuleReference'),
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

	var addGridColorFilter = function () {
		var templateContents = jQuery ('#edit-filter-color-template').html (),
			table            = modal.find ('#editFilterTable'),
			row, option, op;

		if (!templateContents) {
			return;
		}

		row = jQuery (templateContents);
		row.find ('button').eq (0).attr ('onclick', 'GridPropertiesUtils.removeRowColor (this);');
		option = row.find ('select').eq (0);
		for (op = 0; op < gridProperties.labelField.length; op++) {
			if (jQuery.inArray (gridProperties.typeField[ op ], [ '1', '7', '9', '21' ]) !== -1) {
				option.append (jQuery ('<option>', {
					value: gridProperties.nameField[ op ],
					text:  gridProperties.labelField[ op ]
				}));
			}
		}
		option = row.find ('select').eq (1);
		for (op = 0; op < gridProperties.labelField.length; op++) {
			if (jQuery.inArray (gridProperties.typeField[ op ], [ '1', '7', '9', '21' ]) !== -1) {
				option.append (jQuery ('<option>', {
					value: gridProperties.nameField[ op ],
					text:  gridProperties.labelField[ op ]
				}));
			}
		}
		table.append (row);
		modal.find ('#editFilterFieldTable').removeClass ('hide');
	};

	var addFieldToGrid = function (link) {
		var thiz             = jQuery (link),
			blockNumber      = thiz.attr ('data-block-number'),
			templateContents = jQuery ('#field-edit-template').html (),
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

	var changeGridFieldPropertiesUI = function (thiz) {
		var selectField    = jQuery (thiz),
			row            = selectField.closest ('tr'),
			mainProperties = row.find ('field-main-properties'),
			fieldType      = selectField.val (),
			field;

		mainProperties.css ('visibility', 'hidden');
		updateFieldGridPropertiesUI (row, fieldType);
		mainProperties.css ('visibility', 'visible');
	};

	var checkGridEditAction = function () {
		var r, f, k, valueCheckBox, option, preId;

		searchInGridByAction ();
		if (gridProperties.swCheckAction) {
			r = confirm ("¡Esta operación eliminará todas las acciones de activar/desactivar! ¿Continuar?");
			if (r == true) {
				removeGridActions ('tr-check-action');
			} else {
				return;
			}
		}

		if (jQuery.inArray ('56', gridProperties.typeField) == -1) {
			alert ('¡No hay columnas checkbox para acciones!');
			return;
		}

		valueCheckBox = [ 'activado' ];
		for (f = 0; f < gridProperties.typeField.length; f++) {
			if (gridProperties.typeField[ f ] == 56) {
				for (k = 0; k < valueCheckBox.length; k++) {
					var templateContents = jQuery ('#edit-check-action-template').html (),
						table            = jQuery ('#editActionCheck'),
						row;
					if (!templateContents) {
						return;
					}

					row = jQuery (templateContents);
					row.find ('input').eq (0).attr ('name', 'checkField[]').val (gridProperties.labelField[ f ]);
					row.find ('select').eq (0).attr ('name', 'checkValue[]').val (valueCheckBox[ k ]);
					row.find ('input').eq (1).attr ('name', 'checkNameField[]').val (gridProperties.nameField[ f ]);
					option = row.find ('select').eq (1).attr ('name', 'checkFieldDest[]').attr ('onchange', 'GridPropertiesUtils.checkGridSelection (this);');
					for (var op = 0; op < gridProperties.labelField.length; op++) {
						if ((gridProperties.typeField[ op ] != 2202) && (gridProperties.typeField[ op ] != 2203) && (gridProperties.typeField[ op ] != 2204)) {
							preId = (gridProperties.typeField[ op ] == 5) ? 'jscal_field_' : '';
							option.append (jQuery ('<option>', {
								value: gridProperties.nameField[ f ] + '@' + preId + gridProperties.nameField[ op ],
								text:  gridProperties.labelField[ op ]
							}));
						}
					}
					table.append (row);
					modal.find ('#editCheckField').removeClass ('hide');
					gridProperties.swCheckAction = true;
				}
			}
		}
	};

	var checkGridSelection = function (obj) {
		var selectedValue = jQuery (obj).val (),
			myDestination = jQuery (obj).attr ('data-select');
		if (selectedValue == '1') {
			jQuery ('#' + myDestination).removeAttr ('required').addClass ('hide');
		} else if (jQuery ('#' + myDestination).hasClass ('hide')) {
			jQuery ('#' + myDestination).attr ('required', 'required').removeClass ('hide');
		}
	};

	var deleteGridRow = function (link) {
		var row = jQuery (link).closest ('tr');
		if (!row) {
			return;
		}
		row.remove ();
		if (gridProperties.swSummaryAction) {
			GridPropertiesUtils.summaryEditFieldAction ();
		}

	};

	var getConfigCalculated = function () {
		if (configCalculatedFieldLink != null) {
			window.location.href = configCalculatedFieldLink;
		}
	};

	var getGridImportField = function (obj) {
		var arrayValue = jQuery (obj).val ().split ('@'),
			idField    = arrayValue[ 0 ],
			gridModule = arrayValue[ 1 ],
			gridTable  = modal.find ('#mix-edit-grid').html (),
			lastModuleFieldId;
		if (idField == 0) {
			if (gridTable != '&nbsp;') {
				if (confirm ("¡Esta operación eliminará todas las columnas seleccionadas! ¿Continuar?")) {
					jQuery ('#other-table-info').html ();
					modal.find ('#mix-edit-grid').html ('&nbsp;');
					lastModuleFieldId = 0;
					modal.find ('#column0').val (0)
				} else {
					jQuery (obj).val (lastModuleFieldId);
				}
			}
		} else {
			if (gridTable != '&nbsp;') {
				if (confirm ("¡Esta operación eliminará todas las columnas seleccionadas! ¿Continuar?")) {
					lastModuleFieldId = idField;
					modal.find ('#column0').val (idField);
					getGridEditTable (idField, gridModule)
				} else {
					jQuery (this).val (lastModuleFieldId);
				}
			} else {
				lastModuleFieldId = idField;
				modal.find ('#column0').val (idField);
				getGridEditTable (idField, gridModule);
			}
		}
	};

	var isSetGridObjectProperties = function () {
		return gridProperties != null;
	};

	var linkGridEditAction = function () {
		var r, f, k, listValue, option;

		searchInGridByAction ();
		if (gridProperties.swListAction) {
			r = confirm ("¡Esta operación eliminará todas las vinculaciones! ¿Continuar?");
			if (r == true) {
				removeGridActions ('tr-list-action');
			} else {
				return;
			}
		}
		if (jQuery.inArray ('15', gridProperties.typeField) == -1) {
			alert ('¡No hay campos tipo listas para vincular!');
			return;
		} else if (jQuery.inArray ('1', gridProperties.typeField) == -1) {
			alert ('¡Se requiere de al menos un campo tipo texto!');
			return;
		}

		for (f = 0; f < gridProperties.typeField.length; f++) {
			if (gridProperties.typeField[ f ] == 15) {
				listValue = gridProperties.valueField[ f ].split ('\n');
				for (k = 0; k < listValue.length; k++) {
					if (listValue != ' ') {
						var templateContents = jQuery ('#edit-action-template').html (),
							table            = modal.find ('#editActionTable'),
							row;
						if (!templateContents) {
							return;
						}

						row = jQuery (templateContents);
						row.find ('input').eq (0).attr ('name', 'selectField[]').val (gridProperties.labelField[ f ]);
						row.find ('input').eq (1).attr ('name', 'selectValue[]').val (listValue[ k ]);
						row.find ('input').eq (2).attr ('name', 'selectNameField[]').val (gridProperties.nameField[ f ]);
						row.find ('select').eq (0).attr ('data-select', 'destinationField_' + f + '_' + k).attr ('onchange', 'GridPropertiesUtils.checkGridSelection (this)');
						option = row.find ('select').eq (1).attr ('name', 'destinationField[]').attr ('id', 'destinationField_' + f + '_' + k);
						for (var op = 0; op < gridProperties.labelField.length; op++) {
							if ((gridProperties.labelField[ op ] != gridProperties.labelField[ f ]) && (gridProperties.typeField[ op ] == 1)) {
								option.append (jQuery ('<option>', {
									value: gridProperties.nameField[ op ],
									text:  gridProperties.labelField[ op ]
								}));
							}
						}
						table.append (row);
						modal.find ('#editLinkField').removeClass ('hide');
						gridProperties.swListAction = true;
					}
				}
			}
		}
	};

	var moveGridRowDown = function (btn) {
		var rowToMove = jQuery (btn).parents ('tr.MoveableEditRow');
		var next = rowToMove.next ('tr.MoveableEditRow');
		next.after (rowToMove);
	};

	var moveGridRowUp = function (btn) {
		var rowToMove = jQuery (btn).parents ('tr.MoveableEditRow');
		var prev = rowToMove.prev ('tr.MoveableEditRow');
		prev.before (rowToMove);
	};

	var normalizeGridFieldName = function (thiz) {
		var sourceField      = jQuery (thiz),
			destinationField = sourceField.closest ('tr').find ('.field-name');
		destinationField.val (getNormalizedText (sourceField.val ()));
	};

	var removeGridActions = function (trObject) {
		var row = jQuery ('.' + trObject).closest ('tr');
		if (!row) {
			return;
		}
		row.remove ();
		if (trObject == 'tr-list-action') {
			modal.find ('#editLinkField').addClass ('hide')
		} else if (trObject == 'tr-check-action') {
			modal.find ('#editCheckField').addClass ('hide')
		} else {
			modal.find ('#editFilterFieldTable').addClass ('hide')
		}
	};

	var removeRowColor = function (obj) {
		((jQuery (obj)).parent ()).parent ().remove ();
	};

	var saveDataGrid = function () {
		var form = jQuery ('form[name="grid-form"]'),
			serialized;

		if (!isGridEditFormValid (form)) {
			return;
		}
		serialized = form.serialize ();
		jQuery.ajax ({
			cache:   false,
			data:    serialized,
			type:    "POST",
			url:     'index.php?module=Settings&action=SettingsAjax&file=editGridValues&ajax=true',
			success: function (message) {
				var partsMessage = message.split ('@');
				partsMessage.pop ();
				message = partsMessage.join ('\n');
				alert (message);
				modal.modal ('hide');
				window.location.reload ();
			}
		})
	};

	var setGridColorEditFilter = function () {
		searchInGridByAction ();
		var templateContents = jQuery ('#edit-filter-color-template').html (),
			table            = modal.find ('#editFilterTable'),
			row, option, op;
		if (!templateContents) {
			return;
		}

		row = jQuery (templateContents);
		row.find ('button').eq (0).attr ('onclick', 'GridPropertiesUtils.removeRowColor(this);');
		option = row.find ('select').eq (0);
		for (op = 0; op < gridProperties.labelField.length; op++) {
			if (jQuery.inArray (gridProperties.typeField[ op ], [ '1', '7', '9', '21' ]) !== -1) {
				option.append (jQuery ('<option>', {
					value: gridProperties.nameField[ op ],
					text:  gridProperties.labelField[ op ]
				}));
			}
		}
		option = row.find ('select').eq (1);
		for (op = 0; op < gridProperties.labelField.length; op++) {
			if (jQuery.inArray (gridProperties.typeField[ op ], [ '1', '7', '9', '21' ]) !== -1) {
				option.append (jQuery ('<option>', {
					value: gridProperties.nameField[ op ],
					text:  gridProperties.labelField[ op ]
				}));
			}
		}
		table.append (row);
		modal.find ('#editFilterFieldTable').removeClass ('hide');
		gridProperties.swColorFilter = true;
	};

	var setGridEditImportColumn = function (columna) {
		var dataField = columna.split ('@'),
			inField   = true,
			option;

		modal.find ("input[name='nombreCampo[]']").each (function () {
			if (jQuery (this).val () == dataField[ 1 ]) {
				inField = false;
			}
		});
		if (inField) {
			var templateContents = jQuery ('#field-edit-template').html (),
				table            = modal.find ('#lvt-edit-table'),
				row;
			if (!templateContents) {
				return;
			}

			row = jQuery (templateContents);
			row.find ('.block-number').val (1);
			row.find ('.import-code').val (dataField[ 2 ]);
			row.find ('.field-label').val (dataField[ 0 ]);
			row.find ('.field-name').val (dataField[ 1 ]);
			option = row.find ('.field-type');
			option.append (jQuery ('<option>', {
				value: '2202',
				text:  'Importada'
			}));
			option.val ('2202');
			updateFieldGridPropertiesUI (row, 2202);
			table.append (row);
			gridProperties.swImportField = true;
		}
	};

	var removeEditSummary = function (tdClass) {
		var table = jQuery ('#actionSummary'),
			rows  = table.find ('thead > tr'),
			row   = jQuery ('.' + tdClass);
		if (!row) {
			return;
		}
		row.remove ();
		rows.remove ();
		jQuery ('#summaryField').addClass ('hide');
		gridProperties.swSummaryAction = false;
	};

	var removeEditImport = function () {
		modal.find ('.editImport-field-row').remove ();
		modal.find ('.edit-import-bar').addClass ('hide');
		modal.find ('#select-modules-to-import').empty ();
		modal.find ('#gridEditImportValues').addClass ('hide');
		modal.find ('#div-modules-to-import').addClass ('hide');
		modal.find ('#importEditModuleReference').val ('');
        for (i = 0; i < tableImportIndex ; i++) {
            jQuery('#importEditFieldsTable-' + i).remove ();
        }
		gridProperties.swImportAction = false;
        tableImportIndex = 0;
	};

	var summaryEditActionSelection = function (obj) {
		var actionSlected = jQuery (obj).val (),
			row           = ((jQuery (obj)).parent ()).parent ().next (),
			objSelect     = row.find ('select').eq (1);
		if (actionSlected == 'sys') {
			row.find ('.calculated').removeClass ('hide');
			if (objSelect.length > 0) {
				objSelect.attr ('required', 'required')
			}
		} else {
			row.find ('.calculated').addClass ('hide');
			if (objSelect.length > 0) {
				objSelect.removeAttr ('required')
			}
		}
	};

	var summaryEditFieldAction = function () {
		searchInGridByAction ();
		if (gridProperties.swSummaryAction) {
			removeEditSummary ('td-summary-action')
		}
		var templateContents = jQuery ('#grid-summary-edit-template').html (),
			table            = modal.find ('#actionSummary'),
			rows             = table.find ('thead'),
			heads            = '',
			row, tr, numField, cellwidth;
		if (!templateContents) {
			return;
		}
		numField = (gridProperties.labelField.length);
		cellwidth = Math.floor (100 / numField);
		for (var op = 0; op < gridProperties.labelField.length; op++) {
			if (gridProperties.labelField[ op ] != 'Fila resumen') {
				heads += '<th width="' + cellwidth + '%"  ><small>' + gridProperties.labelField[ op ] + '</small></th> ';
				row = jQuery (templateContents);
				if ((gridProperties.summaryRow.length > 0) && gridProperties.summaryRow[ op ] != undefined) {
					if (gridProperties.summaryRow[ op ].field != 'false') {
						row.find ('input').eq (0).attr ('checked', 'checked');
						row.find ('select').eq (0).val (gridProperties.summaryRow[ op ].action);
						row.find ('input').eq (1).val (gridProperties.nameField[ op ]);
						if (gridProperties.summaryRow[ op ].action == 'sys') {
							row.find ('select').eq (1).val (gridProperties.summaryRow[ op ].calculatedId);
							row.find ('.calculated').removeClass ('hide');
						}
					}
				}
				row.find ('select').eq (0).attr ('onchange', 'GridPropertiesUtils.summaryEditActionSelection(this)');
				tr = table.find ('.tr-summary-action');
				row.find ('.summary-name').html (gridProperties.labelField[ op ]);
				row.find ('input').eq (0).val (gridProperties.nameField[ op ]);
				jQuery (tr).append (row);
			}
		}
		jQuery (rows).append ('<tr>' + heads + '</tr>');
		modal.find ('#summaryField').removeClass ('hide');
		gridProperties.swSummaryAction = true;
	};

	var summaryEditSelection = function (obj) {
		var field = jQuery (obj).val (),
			row   = ((jQuery (obj)).parent ()).parent (),
			tr;
		if (jQuery (obj).is (':checked')) {
			row.find ('input').eq (1).val (field);
		} else {
			jQuery (obj).removeAttr ('checked');
			row.find ('input').eq (1).val ('false');
			tr = (((jQuery (obj)).parent ()).parent ()).parent ();
			tr.find ('select').eq (0).val ('');
			tr = (((jQuery (obj)).parent ()).parent ()).parent ().next ();
			tr.find ('select').eq (0).val ('');
			tr.find ('.calculated').addClass ('hide');
		}
	};

	var searchCalculated = function (obj) {
		var filter = jQuery (obj).val (),
			list   = modal.find ('.calculated-list');

		if (filter != '') {
			jQuery.expr[ ':' ].Contains = function (a, i, m) {
				return (a.textContent || a.innerText || "").toUpperCase ().indexOf (m[ 3 ].toUpperCase ()) >= 0;
			};

			list.find ("a:not(:Contains('" + filter + "'))").slideUp ();
			list.find ("a:Contains('" + filter + "')").slideDown ();
		} else {
			list.find ('a').slideDown ();
		}
		return false;
	};

	var setCalculatedSystem = function (obj) {
		var mySelection        = jQuery (obj),
			selectionValues,
			calculatedSystemId = modal.find ('#calculatedSystemId');
		mySelection.parent ().each (function (index, item) {
			jQuery (item).find ('a').removeClass ('active');
		});
		mySelection.addClass ('active');
		selectionValues = mySelection.attr ('rel');
		calculatedSystemId.val (selectionValues);
	};

	var setDefaultTypeDate = function (obj) {
		var defaultValue, defaultSelect, propertiesTd;
		propertiesTd = jQuery (obj).parent ();
		defaultValue = propertiesTd.find ('input').eq (7);
		defaultSelect = jQuery (obj);
		if (defaultSelect.val () == 'valor') {
			defaultValue.attr ('disabled', false);
			defaultValue.removeClass ('hide');
			defaultValue.attr ('placeholder', 'AAAA-MM-DD');

			defaultSelect.attr ('disabled', true);
			defaultSelect.addClass ('hide');
		} else {
			defaultValue.attr ('disabled', true);
			defaultValue.addClass ('hide');
			defaultValue.attr ('placeholder', 'Valor inicial');

			defaultSelect.attr ('disabled', false);
			defaultSelect.removeClass ('hide');
		}
	};

	window.GridPropertiesUtils = {
		addGridColorFilter:          addGridColorFilter,
		addFieldToGrid:              addFieldToGrid,
		addEditImportRelationship:   addEditImportRelationship,
		changeGridFieldPropertiesUI: changeGridFieldPropertiesUI,
		checkGridEditAction:         checkGridEditAction,
		checkGridSelection:          checkGridSelection,
		deleteGridRow:               deleteGridRow,
		deleteEditImport:            deleteEditImport,
        deleteEditImportRelationship: deleteEditImportRelationship,
		editGridProperties:          editGridProperties,
		editImportFieldAction:       editImportFieldAction,
		getConfigCalculated:         getConfigCalculated,
		getGridImportField:          getGridImportField,
		isSetGridObjectProperties:   isSetGridObjectProperties,
		linkGridEditAction:          linkGridEditAction,
		moveGridRowDown:             moveGridRowDown,
		moveGridRowUp:               moveGridRowUp,
		normalizeGridFieldName:      normalizeGridFieldName,
		removeGridActions:           removeGridActions,
		removeRowColor:              removeRowColor,
		removeEditSummary:           removeEditSummary,
		removeEditImport:            removeEditImport,
		saveDataGrid:                saveDataGrid,
		searchCalculated:            searchCalculated,
		searchFieldToEditImport:     searchFieldToEditImport,
		setCalculatedSystem:         setCalculatedSystem,
		setDefaultTypeDate:          setDefaultTypeDate,
		setGridColorEditFilter:      setGridColorEditFilter,
		setGridEditImportColumn:     setGridEditImportColumn,
		summaryEditActionSelection:  summaryEditActionSelection,
		summaryEditFieldAction:      summaryEditFieldAction,
		summaryEditSelection:        summaryEditSelection
	};
} (jQuery));