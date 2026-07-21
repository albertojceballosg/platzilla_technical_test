(function (jQuery) {
	// Private variables
	var modal = null;

	// Private functions
    var createFormTags = function (formName) {
        var html       = '',
            mainDiv    = jQuery('#div-' + formName),
            formIniTag = '<form action="index.php" name="' + formName + '">',
            formEndTag = '</form>';

        html = formIniTag + mainDiv.html () + formEndTag;
        mainDiv.html (jQuery (html));
    };

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var hideEditView = function () {
        var sendButton     = jQuery ('#ec-btn-send'),
            sendEditButton = jQuery ('#ec-btn-edit-send'),
            navTabs = jQuery('#ce-nav-tab li'),
            editTab = jQuery('#ec-edit-view'),
            listTab = jQuery('#ce-nav-tab:nth-child(2)');

        navTabs.each(function (index, element) {
            var li = jQuery(element);
            if (li.hasClass('active')) {
                li.removeClass ('active');
            }
        });
        listTab.addClass ('active');
        jQuery ('#ec-list-view').addClass ('active').addClass ('in');

        editTab.removeClass ('active').removeClass ('in');
        editTab.html('');
        if (!sendButton.hasClass('hide')) {
            sendButton.addClass('hide')
        }
        if (!sendEditButton.hasClass('hide')) {
            sendEditButton.addClass('hide')
        }
    };

    var isValidFieldContentByType = function (uiType, value) {
        switch (uiType) {
            case 5: // Date
                isValid = isValidDateTime (value);
                break;
            case 6: // DateTime
                isValid = isValidDateTime (value);
                break;
            case 7:  // Number
            case 9:  // Percentage
            case 71: // Currency
                isValid = isValidNumber (value);
                break;
            case 13: // Email
                isValid = isValidEmail (value);
                break;
            case 14:
                isValid = isValidTime (value);
                break;
            default:
                isValid = true;
                break;
        }
        return isValid;
    };

    var isValidEmail = function (value) {
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            return true;
        } else {
            return /^([a-zA-Z0-9_.+-])+@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test (value);
        }
    };

    var isValidNumber = function (value) {
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            return true;
        } else {
            return jQuery.isNumeric (value);
        }
    };

    var isValidDateTime = function (value) {
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            return true;
        } else {
            return !isNaN (Date.parse (value));
        }
    };

    var isValidTime = function (value) {
        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            return true;
        } else {
            return /^(?:1[0-2]|0[0-9]):[0-5][0-9]:[0-5][0-9]$/.test (value);
        }
    };

    var showEditView = function () {
        var navTabs = jQuery('#ce-nav-tab li'),
            editTab = jQuery('#ec-edit-view');

        navTabs.each(function (index, element) {
            var li = jQuery(element);
            if (li.hasClass('active')) {
                li.removeClass ('active');
            }
        });

        jQuery ('#ec-create-view').removeClass ('active').removeClass ('in');
        jQuery ('#ec-list-view').removeClass ('active').removeClass ('in');


        jQuery ('#ec-list-table > tbody  > tr').each(function(index, tr) {
            var unllock = jQuery(tr).find('button').eq(0);
            if (!unllock.hasClass('hide')) {
                unllock.addClass ('hide');
            }
        });

        editTab.addClass('active').addClass ('in');
        editTab.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"  style="width: 75%;height: 30%"/>');
    };

	var validateForm = function (objForm) {
        var formElement    = jQuery("form[name='" + objForm.attr ('name') +"'] :input"),
            form           = jQuery (objForm),
            isValidate     = true,
            selectedFields = [],
            field, operationValue, value;
        jQuery('span[id ^= ce-]').html('');
        jQuery('div[id ^= ce-div-]').removeClass('has-error');
        jQuery('div[id ^= td_]').removeClass('has-error');

        formElement.map(function (index, elm) {
            var element = jQuery(elm),
                elementTitle = element.attr('title'),
                elementName  = element.attr ('name'),
                uitype       = (elementName === 'fields[]') ? undefined : element.closest('div[id=td_' + elementName + ']').attr('data-uitype'),
                value = element.val();
            if ((jQuery.inArray(elm.type, ['hidden', 'button', 'submit', 'select-multiple', 'checkbox', 'undefined']) === -1) && elementTitle !== '' && elementTitle !== undefined) {
                if ((value === null) || (value === undefined) || (value.trim() === '')) {
                    element.parent().addClass('has-error');
                    if (element.parent().find('.help-block').length) {
                        element.parent().find('.help-block').html(elementTitle + ' requerido');
                    } else {
                        element.parent().parent().find('.help-block').html(elementTitle + ' requerido');
                    }
                    isValidate = false;
                } else if(elementName === 'fields[]') {
                    if(jQuery.inArray(value, selectedFields)  !== -1) {
                        element.parent().addClass('has-error');
                        element.parent().parent().find('.help-block').html('Campo duplicado');
                        isValidate = false;
                    } else {
                        selectedFields.push(value);
                    }
                } else if (uitype !== undefined) {
                    if(!isValidFieldContentByType(uitype, value)) {
                        element.parent().addClass('has-error');
                        element.parent().parent().find('.help-block').html('Error de formato o valor invalido!');
                        isValidate = false;
                    }
                }
            }
        });

        return isValidate;
    };

	// Public functions
	var addField = function (obj) {
		var button        = jQuery(obj),
			fieldDiv      = jQuery (button.attr('data-action')),
			fieldTemplate = jQuery ('#editable-fields-template');

        var newField = fieldDiv.clone().attr('id', '').appendTo (fieldDiv.parent ());

        newField.find('select').eq(0).val ('');
        newField.find ('button').eq(0).removeClass('hide');
    };

	var createView = function (obj) {
      var sendButton     = jQuery ('#ec-btn-send'),
          sendEditButton = jQuery ('#ec-btn-edit-send'),
          parentTabId    = jQuery(obj).attr('href'),
          parentTab      = jQuery(parentTabId);

      if (sendButton.hasClass ('hide')) {
          sendButton.removeClass('hide');
      }
        if (!sendEditButton.hasClass('hide')) {
            sendEditButton.addClass('hide')
        }

    };

	var editEditableButton = function (obj) {
        var arguments,
            sendEditButton = jQuery ('#ec-btn-edit-send'),
            editButton     = jQuery (obj),
            editTab        = jQuery('#ec-edit-view'),
            buttonName     = editButton.attr ('rel'),
            navEdit        = jQuery ('#ce-nav-tab li:last-child'),
            navEditLink    = navEdit.find ('a').eq (0),
            linkContent    = '<span class="glyphicon glyphicon-pencil"></span>&nbsp;',
            label          = jQuery (obj).closest ('tr').find('td').eq(1).html();

        showEditView();
        jQuery('td[class = isDisabled]').removeClass('isDisabled');
        editButton.parent().addClass ('isDisabled');
        jQuery (obj).closest ('tr').find('button').eq(0).removeClass('hide');
        arguments = {
            'module':     'Settings',
            'action':     'EditEditableFieldsButton',
            'Ajax':       'true',
            'buttonname': buttonName
        };

        jQuery.post('index.php', arguments, function (data) {
            try {
                if(data.indexOf('<form') === -1) {
                    throw data;
                } else {
                    editTab.html(data);
                    sendEditButton.removeClass('hide');
                    navEditLink.html (linkContent + label);
                    navEditLink.removeClass ('hide');
                    navEdit.addClass ('active');
                }
            }
            catch (e) {
                alert(e);
                editButton.parent().removeClass('isDisabled');
                navEditLink.html ('');
                navEditLink.addClass ('hide');
                hideEditView();
            }
        });

    };

	var editView = function (obj){
        var sendButton     = jQuery ('#ec-btn-send'),
            sendEditButton = jQuery ('#ec-btn-edit-send'),
            parentTabId    = jQuery(obj).attr('href'),
            parentTab      = jQuery(parentTabId);

        if (!sendButton.hasClass('hide')) {
            sendButton.addClass('hide')
        }
        if (sendEditButton.hasClass('hide')) {
            sendEditButton.removeClass('hide')
        }
    };

	var delEditableButton = function (obj) {
	    var arguments,
            buttonName = jQuery (obj).attr ('rel'),
            delButton  = jQuery (obj),
            label      = jQuery (obj).closest ('tr').find('td').eq(1).html(),
            message,
            modalTitle = jQuery ('.modal-title');

        delButton.parent().addClass ('isDisabled');
        if (confirm('¿Eliminar el botón ' + label +' ?')) {
            arguments = {
                'module': 'Settings',
                'action': 'DeleteEditableFieldsButton',
                'Ajax': 'true',
                'buttonname': buttonName
            };

            jQuery.post('index.php', arguments, function (data) {
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if(message.error !== 'OK') {
                        throw message.error;
                    } else {
                        alert('El botón ha sido eliminado con éxito');
                        modalTitle.html(modalTitle.html() + '&nbsp;<span class="help-block" style="color: red">Recargando editor de disposición....</span>');
                        location.reload();
                    }
                }
                catch (e) {
                    alert(e);
                    delButton.parent().removeClass ('isDisabled');
                }
            });
        } else {
            delButton.parent().removeClass ('isDisabled');
        }
    };

	var delField = function (obj) {
		var row = jQuery(obj).parent ().parent ().parent ();
		row.remove ();
    };

	var hideWindowsFields = function (obj, event) {
        var windows        = jQuery(obj),
            windowsId      = windows.attr('rel'),
            control        = windows.attr ('data-control'),
            dummy          = windowsId.split('-'),
            formElement    = jQuery("form[name='windows-form-" + dummy[1] +"'] :input"),
            fieldContainer = jQuery('#' + windowsId);

        formElement.map(function (index, elm) {
            var element = jQuery(elm);
            if (elm.type === 'text') {
                element.datepicker ('remove');
            }
        });
        fieldContainer.parent().find('a').eq(0).attr ('data-control', 'out');
        fieldContainer.fadeToggle (300);

        jQuery('ul[id ^= ul-group-]').css({'display':''});
        jQuery('div[id ^= btn-group-]').removeClass('open');

        event.preventDefault ();
        event.stopPropagation ();
    };

	var listView = function (obj) {
        var sendButton     = jQuery ('#ec-btn-send'),
            sendEditButton = jQuery ('#ec-btn-edit-send'),
            parentTabId    = jQuery(obj).attr('href'),
            parentTab      = jQuery(parentTabId);

        if (!sendButton.hasClass('hide')) {
            sendButton.addClass('hide')
        }
        if (!sendEditButton.hasClass('hide')) {
            sendEditButton.addClass('hide')
        }
    };

	var openModal = function (moduleName, prefix, initialSequence, currentSequence) {
		var modalTemplate = jQuery ('#editable-fields-modal-template');

        modal = jQuery (modalTemplate.html ());
        modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var saveEditableFields = function (obj) {
	    var sendButton = jQuery(obj),
		    myForm     = jQuery("form[name='" + sendButton.attr('data-action') + "']"),
            modalTitle = jQuery ('.modal-title');
	    sendButton.attr('disabled','disabled');
		if (!validateForm (myForm)) {
            sendButton.removeAttr('disabled');
			return false;
		}
		var arguments = myForm.serialize();

        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                  alert('El botón ha sido guardado con éxito');
                  modalTitle.html(modalTitle.html() + '&nbsp;<span class="help-block" style="color: red">Recargando editor de disposición....</span>');
                  location.reload();
                }
            }
            catch (e) {
                alert(e);
                sendButton.removeAttr('disabled');
            }
        });

	};

	var saveWindowsFields = function (obj, event) {
        var btnSave   = jQuery(obj),
            formName  = btnSave.attr('rel'),
            checkFrom = jQuery ('#div-' + formName).find('form').eq(0),
            form      = jQuery("form[name='" + btnSave.attr('rel') + "']");

        if (checkFrom.length === 0) {
            createFormTags (formName);
            form = jQuery("form[name='" + formName + "']");
        }
        btnSave.parent().addClass ('isDisabled');
        if (!validateForm (form)) {
            btnSave.parent().removeClass ('isDisabled');
            return false;
        }

        var arguments = form.serialize(),
            record = form.find('input[name=record]').val(),
            btnName = form.find('input[name=buttonname]').val();

        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('Campo(s) actualizado(s) con éxtito');
                    btnSave.parent().removeClass ('isDisabled');
                    hideWindowsFields ('<a href="#"  rel="' + btnName + '-' + record + '"></a>', event);
                }
            }
            catch (e) {
                alert(e);
                btnSave.parent().removeClass ('isDisabled');
            }
        });

        event.preventDefault ();
        event.stopPropagation ();
    };

	var showWindowsFields = function (obj, event) {
	    var windows        = jQuery(obj),
            windowsId      = windows.attr('rel'),
            control        = windows.attr ('data-control'),
            dummy          = windowsId.split('-'),
            formName       = 'windows-form-' + windowsId,
            checkFrom      = jQuery ('#div-' + formName).find('form').eq(0),
            fieldContainer = jQuery('#' + windowsId),
            totalLinks     = windows.parent().closest('ul').children().length,
            topPos         = fieldContainer.css('top');

	    jQuery ('.editableFieldContainer').get ().forEach (function (entity, index) {
	        var container   = jQuery (entity),
                containerId = container.attr('id');
            if (container.is(":visible") && (containerId !== windowsId)) {
                jQuery('#' + containerId).fadeToggle (150);
                jQuery('#' + containerId).parent().find('a').eq(0).attr ('data-control', 'out');
            }
        });
        if (totalLinks > 1){
            fieldContainer.css('top', (15 + (totalLinks * 27.5)) + 'px');
        }
        if(control === 'out') {
            if (checkFrom.length === 0) {
                createFormTags (formName);
            }
            jQuery('#ul-group-' + dummy[1]).css({'display':'block'});
            fieldContainer.fadeToggle (300);
            windows.attr ('data-control', 'in')
        }

        event.preventDefault ();
        event.stopPropagation ();
    };

	var unlockEditableButton = function (obj) {
	    var navEdit        = jQuery ('#ce-nav-tab li:last-child'),
            navEditLink    = navEdit.find ('a').eq (0);
        navEditLink.html ('');
        navEditLink.addClass ('hide');
        hideEditView();
	    jQuery('td[class = isDisabled]').removeClass('isDisabled');
        jQuery(obj).addClass('hide');

    };

	window.EditableFieldsUtils = {
        addField:             addField,
        createView:           createView,
        editEditableButton:   editEditableButton,
        editView:             editView,
        delEditableButton:    delEditableButton,
        delField:             delField,
        hideWindowsFields:    hideWindowsFields,
        listView:             listView,
		openModal:            openModal,
		saveEditableFields:   saveEditableFields,
        saveWindowsFields:    saveWindowsFields,
        showWindowsFields:    showWindowsFields,
        unlockEditableButton: unlockEditableButton
	};

    var onDocumentReadyHandler = function () {

    };
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
