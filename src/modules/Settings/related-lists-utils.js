(function (jQuery) {
	// Private variables
	var modal = null;

	// Private functions

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var setButtonsVisibility = function () {
		modal.find ('.related-list .btn-up-dummy').addClass ('hidden');
		modal.find ('.related-list .btn-up').removeClass ('hidden');
		modal.find ('.related-list .btn-down-dummy').addClass ('hidden');
		modal.find ('.related-list .btn-down').removeClass ('hidden');
		modal.find ('.related-list:first-child .btn-up-dummy').removeClass ('hidden');
		modal.find ('.related-list:first-child .btn-up').addClass ('hidden');
		modal.find ('.related-list:last-child .btn-down').addClass ('hidden');
		modal.find ('.related-list:last-child .btn-down-dummy').removeClass ('hidden');
	};

	var setRelatedListsSequences = function (relatedLists) {
		var i, relatedList;

		for (i = 0; i < relatedLists.length; i += 1) {
			relatedList = jQuery (relatedLists [i]);
			relatedList.find ('.related-list-sequence').val (i + 1);
		}
	};

	var validateRelatedLists = function (relatedLists) {
		var i, relatedList, field, value;

		if (relatedLists.length === 0) {
			return true;
		}

		for (i = 0; i < relatedLists.length; i += 1) {
			relatedList = jQuery (relatedLists [i]);

			field = relatedList.find ('.related-list-label');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce la etiqueta de la lista');
				field.focus ();
				return false;
			}

			field = relatedList.find ('.related-list-module-name');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Selecciona el nombre del módulo');
				field.focus ();
				return false;
			}

			field = relatedList.find ('.related-list-action:checked');
			if (field.length === 0) {
				alert ('Selecciona alguna de las disponibilidades');
				relatedList.find ('.related-list-action:first-child').focus ();
				return false;
			}
		}

		return true;
	};
	var updateButtons = function (tBody) {
        var totalAction = tBody.find('.col-actions-field').length;
        tBody.find('.col-actions-field').each(function (index, element) {
        	if (totalAction > 1) {
                if (index === 0) {
                    jQuery(element).find('button').eq(0).addClass('hide');
                    jQuery(element).find('button').eq(1).removeClass('hide');
                    jQuery(element).find('button').eq(2).addClass('hide');
                } else if (index === (totalAction - 1)) {
                    jQuery(element).find('button').eq(1).addClass('hide');
                    jQuery(element).find('button').eq(0).removeClass('hide');
                    jQuery(element).find('button').eq(2).removeClass('hide');
                } else {
                    jQuery(element).find('button').eq(0).removeClass('hide');
                    jQuery(element).find('button').eq(1).removeClass('hide');
                    jQuery(element).find('button').eq(2).removeClass('hide');
                }
            } else {
                jQuery(element).find('button').eq(0).addClass('hide');
                jQuery(element).find('button').eq(1).addClass('hide');
                jQuery(element).find('button').eq(2).addClass('hide');
			}
        })
	};
	// Public functions

	var addFieldImport = function (obj, id, modulename, moduleHome) {
        var tBody            = jQuery('#' + id + '-' + modulename + '-field-import'),
            lastSelect       = tBody.find ('tr:last').find('select').eq(0),
            options          = lastSelect.find('option').clone(),
            nextId           = tBody.find('tr').length,
            relatedFieldsRow = jQuery('#related-field-import-row-template').html()
                .replace(/__INDEX__/g, id)
                .replace(/__ID__/g, nextId)
                .replace(/__MODULE__/g, modulename),
            newLastSelected, totalAction;
        jQuery (tBody).append (relatedFieldsRow);
        newLastSelected = jQuery ('#' + id + '-fields-import-' + modulename + '-' + nextId);
        newLastSelected.append(options);
        newLastSelected.val('');
        updateButtons (tBody);
	};

	var addFieldList = function (obj, id, modulename) {
		var tBody            = jQuery('#' + id + '-' + modulename),
			lastSelect       = tBody.find ('tr:last').find('select').eq(0),
			options          = lastSelect.find('option').clone(),
            nextId           = tBody.find('tr').length,
			relatedFieldsRow = jQuery('#related-field-row-template').html()
				.replace(/__INDEX__/g, id)
                .replace(/__ID__/g, nextId)
                .replace(/__MODULE__/g, modulename),
			newLastSelected, totalAction;

        jQuery (tBody).append (relatedFieldsRow);
        newLastSelected = jQuery ('#' + id + '-fields-' + modulename + '-' + nextId);
        newLastSelected.append(options);
        newLastSelected.val('');
        updateButtons (tBody);
    };

	var addList = function (buttonElement) {
		var button       = jQuery (buttonElement),
			template     = jQuery ('#related-list-template'),
			relatedList  = jQuery (template.html ()),
			relatedLists = button.closest ('.related-lists-container').find ('.related-lists'),
			dummies      = relatedLists.find ('.related-list'),
			dummy, newRelatedListIndex, i;

		if (dummies.length > 0) {
			newRelatedListIndex = -1;
			for (i = 0; i < dummies.length; i += 1) {
				dummy = jQuery (dummies [ i ]);
				newRelatedListIndex = Math.max (newRelatedListIndex, parseInt (dummy.data ('index')));
			}
			newRelatedListIndex += 1;
		} else {
			newRelatedListIndex = 0;
		}

		relatedList.attr ('data-index', newRelatedListIndex);
		relatedList.find ('.related-list-id').attr ('name', 'relatedlists[' + newRelatedListIndex + '][id]').val (0);
		relatedList.find ('.related-list-sequence').attr ('name', 'relatedlists[' + newRelatedListIndex + '][sequence]');
		relatedList.find ('.related-list-label').attr ('name', 'relatedlists[' + newRelatedListIndex + '][label]');
		relatedList.find ('.related-list-module-name').attr ('name', 'relatedlists[' + newRelatedListIndex + '][relatedmodulename]');
		relatedList.find ('.related-list-action-add').attr ('name', 'relatedlists[' + newRelatedListIndex + '][actions][]');
		relatedList.find ('.related-list-action-select').attr ('name', 'relatedlists[' + newRelatedListIndex + '][actions][]');
		relatedLists.append (relatedList);
		setButtonsVisibility ();
		setRelatedListsSequences (relatedLists.find ('.related-list'));
	};

    var deleteFieldList = function (obj, id, modulename) {
        var row       = jQuery (obj).parent ().parent (),
			tBody     = jQuery('#' + id + '-' + modulename);
        if (!confirm ('¿Estás seguro que quieres eliminar el campo leccionado?')) {
            return;
        }
        row.remove ();
        updateButtons (tBody);
    };
	var deleteList = function (buttonElement, id) {
		var button       = jQuery (buttonElement),
			relatedLists = button.closest ('.related-lists'),
            moduleList   = jQuery('#related-module-list-' + id),
			moduleImport = jQuery('#related-module-import-' + id),
			module       = button.parent().parent().find('select').eq(0).val();

		if (!confirm ('¿Estás seguro que quieres eliminar la lista relacionada seleccionada?')) {
			return;
		}
        moduleList.find('option').each(function (index, element) {
            if (module === jQuery(element).val()) {
            	moduleList.find('option[value ="' + module + '"]').remove();
                jQuery('#' + module).remove()
            }
        });
        moduleImport.find('option').each(function (index, element) {
            if (module === jQuery(element).val()) {
                moduleImport.find('option[value ="' + module + '"]').remove();
                jQuery('#' + module + '-field-import').remove()
            }
        });
		button.closest ('.related-list').remove ();
		setButtonsVisibility ();
		setRelatedListsSequences (relatedLists.find ('.related-list'));
	};

    var moveListFieldDown = function (obj, id, modulename) {
        var tBody     = jQuery('#' + id + '-' + modulename),
			rowToMove = jQuery(obj).parents('tr.related-list-field'),
        	next      = rowToMove.next();
        next.after(rowToMove);
        updateButtons(tBody);

    };

    var moveListDown = function (buttonElement) {
		var button              = jQuery (buttonElement),
			relatedLists        = button.closest ('.related-lists-container').find ('.related-list'),
			selectedRelatedList = button.closest ('.related-list'),
			nextRelatedList, i, found;

		if (relatedLists.length === 0) {
			return;
		}

		found = false;
		for (i = 0; i < relatedLists.length; i += 1) {
			nextRelatedList = jQuery (relatedLists [ i ]);
			if (selectedRelatedList.data ('index') === nextRelatedList.data ('index')) {
				found = true;
			} else if (found) {
				selectedRelatedList.detach ().insertAfter (nextRelatedList);
				break;
			}
		}
		setButtonsVisibility ();
		setRelatedListsSequences (button.closest ('.related-lists-container').find ('.related-list'));
	};

	var moveListFieldUp = function (obj, id, modulename) {
        var tBody     = jQuery('#' + id + '-' + modulename),
			rowToMove = jQuery(obj).parents('tr.related-list-field'),
			prev      = rowToMove.prev();

        prev.before (rowToMove);
        updateButtons (tBody);
    };

	var moveListUp = function (buttonElement) {
		var button              = jQuery (buttonElement),
			relatedLists        = button.closest ('.related-lists-container').find ('.related-list'),
			selectedRelatedList = button.closest ('.related-list'),
			previousRelatedList, nextRelatedList, i;

		if (relatedLists.length === 0) {
			return;
		}

		previousRelatedList = null;
		for (i = 0; i < relatedLists.length; i += 1) {
			nextRelatedList = jQuery (relatedLists [ i ]);
			if (selectedRelatedList.data ('index') === nextRelatedList.data ('index')) {
				selectedRelatedList.detach ().insertBefore (previousRelatedList);
				break;
			}
			previousRelatedList = nextRelatedList;
		}
		setButtonsVisibility ();
		setRelatedListsSequences (button.closest ('.related-lists-container').find ('.related-list'));
	};

	var relatedFieldToImport = function (obj, id, moduleName) {
        var destinyField = jQuery (obj),
            destinyUiType = parseInt (destinyField.find ('option:selected').attr('data-uitype')),
            row           = destinyField.parent().parent(),
            originField   = row.find ('select').eq(1),
			importType    = row.find('input').eq(0),
			fieldDv       = jQuery ('#OTHER-' + moduleName + '-' + id),
			listDv        = jQuery ('#LIST-' + moduleName + '-' + id),
			dateDv        = jQuery ('#DATE-' + moduleName + '-' + id),
			checkDv       = jQuery ('#CHECK-' + moduleName + '-' + id),
            functionAction, index;

        ['OTHER-','LIST-', 'DATE-', 'CHECK-'].each(function (element, i) {
        	var divId = jQuery ('#' + element + moduleName + '-' + id);
            divId.find ('select').eq(0).val('');
            divId.find ('select').eq(0).prop ('disabled', true);
            divId.addClass ('hide')
        });

        if (jQuery.inArray (destinyUiType, [ 15, 8192 ]) !== -1) {
        	listDv.find('select').eq(0).prop ('disabled', false);
			listDv.removeClass('hide');
            importType.val('LIST');
            functionAction = (destinyUiType === 15) ? 'FETCH-PICKLIST' : 'FETCH-PIPELINE';
            var arguments  = {
                'module':    'Settings',
                'action':    'AjaxSettingsUtils',
				'flmodule':  moduleName,
                'fieldname': destinyField.val(),
                'function':  functionAction,
                'index':     index,
                'Ajax':      true
            };
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        listDv.find('select').eq(0).empty();
                        listDv.find('select').eq(0).append(message.html)
                    }
                }
                catch (e) {
                    alert (e);
                }
            });
        } else if (jQuery.inArray (destinyUiType, [ 5,6 ]) !== -1) {
            dateDv.find('select').eq(0).prop ('disabled', false);
            dateDv.removeClass('hide');
            importType.val('DATE');
        } else if (jQuery.inArray (destinyUiType, [ 56 ]) !== -1) {
            checkDv.find('select').eq(0).prop ('disabled', false);
            checkDv.removeClass('hide')
            importType.val('CHECK');
        } else if (jQuery.inArray (destinyUiType, [ 5,6,15,56 ]) === -1) {
            fieldDv.find('select').eq(0).prop ('disabled', false);
            fieldDv.removeClass('hide');
            importType.val('FIELD');
            originField.find('option').each(function (i, element) {
                var theOption = jQuery(element),
					uitype    = parseInt (theOption.attr('data-uitype'));
            	if (jQuery.inArray (destinyUiType, [ 53 ]) !== -1) {
            		if (jQuery.inArray (uitype, [ 53 ]) !== -1) {
            			theOption.prop ('disabled', false);
					} else {
                    	theOption.prop ('disabled', true)
					}
                } else if (jQuery.inArray (destinyUiType, [ 10 ]) !== -1) {
                    if (jQuery.inArray (uitype, [ 10 ]) !== -1) {
                        theOption.prop ('disabled', false);
                    } else {
                        theOption.prop ('disabled', true)
                    }
                } else if (jQuery.inArray (destinyUiType, [ 13 ]) !== -1) {
                    if (jQuery.inArray (uitype, [ 13 ]) !== -1) {
                        theOption.prop ('disabled', false);
                    } else {
                        theOption.prop ('disabled', true)
                    }
                } else if (jQuery.inArray (destinyUiType, [ 2202 ]) !== -1) {
                    if (jQuery.inArray (uitype, [ 2202 ]) !== -1) {
                        theOption.prop ('disabled', false);
                        importType.val('GRID');
                    } else {
                        theOption.prop ('disabled', true)
                    }
                } else if (jQuery.inArray (destinyUiType, [ 7]) !== -1) {
                    if (jQuery.inArray (uitype, [ 7 ]) !== -1) {
                        theOption.prop ('disabled', false);
                    } else {
                        theOption.prop ('disabled', true)
                    }
                } else if (jQuery.inArray (destinyUiType, [ 1, 7, 9, 11, 17, 21, 85, 71 ]) !== -1) {
                    if (jQuery.inArray (uitype, [ 1, 7, 9, 11, 17, 21, 85, 71 ]) !== -1) {
                        theOption.prop ('disabled', false);
                    } else {
                        theOption.prop ('disabled', true)
                    }
				}
            });
        }

        return;
        if (destinyField.val() !== '') {
            if(relatedFields.length > 0) {
                relatedFields.removeClass('hide')
            } else {
                index = destinyField.find ('option:selected').attr ('data-index');
            }
        }

    };

	var openModal = function () {
		var modalTemplate = jQuery ('#related-lists-modal-template');

		modal = jQuery (modalTemplate.html ());
		setButtonsVisibility ();
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var saveRelatedLists = function (formElement) {
		var form = jQuery (formElement),
			relatedLists = form.find ('.related-list');

		if (!validateRelatedLists (relatedLists)) {
			return;
		}

		jQuery.ajax ('index.php', {
			data: form.serialize (),
			dataType: 'json',
			method: 'post'
		}).done (function () {
			alert ('Se han guardado las listas relacionadas');
			window.location.reload ();
		}).fail (function (jQueryResponse) {
			var message;
			try {
				message = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				message = 'Se ha presentado un error. Intenta más tarde';
			}
			alert (message);
		});
	};

	var setActiveTab = function (obj) {
		var btn = jQuery(obj),
			ul  = btn.parent().parent().find('li');
		ul.each(function () {
            jQuery(this).find('a').removeClass('btn-primary');
        });
		btn.addClass('btn-primary');
    };

	var selectedModule = function (obj, id) {
		var module       = jQuery(obj),
			moduleList   = jQuery('#related-module-list-' + id),
			moduleImport = jQuery('#related-module-import-' + id),
			index        = module.parent().parent().attr('data-index'),
            found        = false;

        moduleList.find('option').each(function (index, element) {
        	if (module.val() === jQuery(element).val()) {
                found = true;
			}
        });
		if (found) {
			alert('Uoops! el módulo ya ha sido seleccionado');
            module.val('');
		} else {
            moduleList.append (
                jQuery ('<option>', {
                        value: module.val (),
                        text:  module.find ('option:selected').text ()
                    }
                ).attr ('data-index', index)
            );
            moduleImport.append (
                jQuery ('<option>', {
                        value: module.val (),
                        text:  module.find ('option:selected').text ()
                    }
                ).attr ('data-index', index)
            );
		}
    };

	var selectRelatedModule = function (obj, id, mainModule) {
		var action         = (mainModule === '') ? 'RELATED-LIST-FIELDS' : 'RELATED-IMPORT-FIELDS',
			appendDiv      = (mainModule === '') ? '-field-list' : '-field-import',
            relatedFieldId = (mainModule === '') ? '' : '-field-import',
			selectedModule = jQuery (obj),
			relatedTable   = (mainModule === '') ? jQuery ('.related-fields') : jQuery ('.related-fields-import') ,
			relatedFields  = jQuery ('#' + selectedModule.val() + relatedFieldId),
			fieldListDv    = jQuery ('#' + id + appendDiv),
			index;
		relatedTable.addClass('hide');
		if (selectedModule.val() !== '') {
			if(relatedFields.length > 0) {
                relatedFields.removeClass('hide')
			} else {
				index = selectedModule.find ('option:selected').attr ('data-index');
                arguments = {
                    'module':     'Settings',
                    'action':     'AjaxSettingsUtils',
					'flmodule':   selectedModule.val(),
					'mainmodule': mainModule,
                    'function':   action,
                    'index':      index,
                    'Ajax':       true
                };
                jQuery.post('index.php', arguments, function (data) {
                    var message;
                    try {
                        message = JSON.parse (JSON.stringify (data));
                        if (message.error !== 'OK') {
                            throw message.error;
                        } else {
                            fieldListDv.append(message.html)
                        }
                    }
                    catch (e) {
                        alert (e);
                    }
                });
			}
		}
    };

	window.RelatedListsUtils = {
        addFieldImport:		 addFieldImport,
        addFieldList:        addFieldList,
		addList:             addList,
		deleteList:          deleteList,
        deleteFieldList:     deleteFieldList,
        moveListFieldDown:   moveListFieldDown,
		moveListDown:        moveListDown,
        moveListFieldUp:     moveListFieldUp,
		moveListUp:          moveListUp,
        relatedFieldToImport: relatedFieldToImport,
		openModal:           openModal,
		saveRelatedLists:    saveRelatedLists,
        setActiveTab:	     setActiveTab,
        selectedModule:      selectedModule,
        selectRelatedModule: selectRelatedModule
	};
} (jQuery));