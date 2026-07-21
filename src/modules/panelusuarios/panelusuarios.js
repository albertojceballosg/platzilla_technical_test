(function (jQuery) {
	var modulesWithTabs = ['Home'];

	var addUsers = function (obj, event) {
        var sendButton = jQuery (obj),
            userModal  = jQuery('#go-plan-modal-template').html(),
			dialog,
            arguments  = {
                'module':   'panelusuarios',
                'action':   'AjaxPanelUserUtils',
                'Ajax':     'true',
                'function': 'ADD_USERS'
            };
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    window.location = sendButton.attr('href');
                }
            }
            catch (e) {
                dialog = jQuery(userModal).clone();
                jQuery(dialog).modal('show');
            }
        });
        sendButton.removeAttr('disabled');
        event.preventDefault();
        event.stopPropagation();
	};

	var changeImage = function (evt) {
		var target = evt.target ? evt.target : evt.srcElement,
			files  = target.files,
			reader = new FileReader (),
			file;

		if ((!files) || (!(files instanceof FileList)) || (files.length === 0)) {
			return;
		}

		file = files [ 0 ];
		if (!file.type.match (/image.*/)) {
			alert ('El archivo ' + file.name + ' no es una imagen');
			return;
		}

		reader.onload = function (evt) {
			var imageContainer = jQuery ('.image-container');
			imageContainer.find ('.image-data').attr ('src', evt.target.result);
			imageContainer.find ('.image-name').html (file.name);
			imageContainer.find ('input[name="userimage[data]"]').val (evt.target.result);
		};
		reader.readAsDataURL (file);
	};

	var deleteUser = function () {
		return confirm ('¿Estás seguro que deseas eliminar el usuario seleccionado?');
	};

	var restoreImage = function (buttonElement) {
		var button = jQuery (buttonElement),
			image, name;
		if (!confirm ('Esto restaurará la última imagen almacenada. ¿Estás seguro?')) {
			return;
		}
		image = button.closest ('.image-container').find ('.image-data');
		image.attr ('src', image.attr ('data-original-src'));
		name = button.closest ('.image-container').find ('.image-name');
		name.html (name.attr ('data-original-name'));
		button.closest ('.image-container').find ('input[name="user-image-data"]').val ('');
	};

	var saveAgent = function (buttonElement, objFrom) {
		var sendButton = jQuery(buttonElement),
			myForm     = jQuery(objFrom);
		sendButton.attr('disabled','disabled');
		if (!validateAgent (myForm)) {
			sendButton.removeAttr('disabled');
			return false;
		}
		myForm.submit();
	}

	var setAvatarData = function (radioElement) {
		var radio = jQuery (radioElement),
			gallery  = radio.closest ('.gallery-photos');

		gallery.find ('input[name="userimage[data]"]').val (radio.val ());
	};

	var setDefaultModule = function (moduleElement) {
		var defaultTabRow    = jQuery ('#default-tab-row'),
			operatingModeRow = jQuery ('#operating-mode-row'),
			selectedModule   = jQuery (moduleElement).val ();

		if (jQuery.inArray (selectedModule, modulesWithTabs) !== -1) {
			operatingModeRow.removeClass('hide');
			defaultTabRow.removeClass('hide')
		} else {
			if (!operatingModeRow.hasClass('hide')) {
                operatingModeRow.addClass('hide');
                defaultTabRow.addClass('hide');
            }
        }
	};

	var setDefaultOperMode = function (obj) {
		var modeSelected = jQuery(obj).val (),
			tabOption    = jQuery ('#default_home_tab');

        tabOption.val ('');
		tabOption.find('option').each (function (index){
			var optionGroup = jQuery(this).closest('optgroup').attr('id');
			if (optionGroup == modeSelected) {
				jQuery(this).prop( "disabled", false);
			} else if (optionGroup !== undefined) {
                jQuery(this).prop( "disabled", true);
			}

		});
	};

	var showAvatarSelection = function (radioElement) {
		var radio = jQuery (radioElement),
			form  = radio.closest ('form'),
			container;

		container = form.find ('#image-selection');
		container.find ('input').prop ('disabled', true);
		container.hide ();

		container = form.find ('#avatar-selection');
		container.find ('input').prop ('disabled', false);
		container.show ();
	};

	var showImageSelection = function (radioElement) {
		var radio = jQuery (radioElement),
			form  = radio.closest ('form'),
			container;

		container = form.find ('#avatar-selection');
		container.find ('input').prop ('disabled', true);
		container.hide ();

		container = form.find ('#image-selection');
		container.find ('input').prop ('disabled', false);
		container.show ();
	};

	var validateAgent = function (objForm) {
	        var formElement    = jQuery("form[name='" + objForm.attr ('name') +"'] :input"),
	            form           = jQuery (objForm),
	            isValidate     = true;

	        jQuery('span[id ^= ag-]').html('');
	        jQuery('div[id ^= ag-div-]').removeClass('has-error');
	        jQuery('div[id ^= td_]').removeClass('has-error');
			//'select-multiple',
	        formElement.map(function (index, elm) {
	            var element = jQuery(elm),
	                elementTitle = element.attr('title'),
	                elementName  = element.attr ('name'),
	                value        = element.val();
				console.log(elementName);
				console.log(value);
	            if ((jQuery.inArray(elm.type, ['hidden', 'button', 'submit', 'checkbox', 'undefined']) === -1) && elementTitle !== '' && elementTitle !== undefined) {
	                if ((value === null) || (value === undefined) || ((elm.type !== 'select-multiple') && (value.trim() === ''))) {
	                    element.parent().addClass('has-error');
	                    if (element.parent().find('.help-block').length) {
	                        element.parent().find('.help-block').html(elementTitle + ' requerido');
	                    } else {
	                        element.parent().parent().find('.help-block').html(elementTitle + ' requerido');
	                    }
	                    isValidate = false;
	                }
	            }
	        });
	        return isValidate;
	    };

	var validateUser = function (formElement) {
		var form = jQuery (formElement),
			field, passwordField, repeatedPasswordField, value, passwordValue, repeatedPasswordValue;

		if (form.find ('.record').length === 0) {
			field = form.find ('.username');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('No has suministrado la dirección de correo del usuario');
				field.focus ();
				return false;
			}

			passwordField = form.find ('.password');
			passwordValue = passwordField.val ();
			if ((passwordValue === undefined) || (passwordValue === null) || (passwordValue.trim () === '')) {
				alert ('No has suministrado la contraseña del usuario');
				passwordField.focus ();
				return false;
			}

			repeatedPasswordField = form.find ('.repeated-password');
			repeatedPasswordValue = repeatedPasswordField.val ();
			if ((repeatedPasswordValue === undefined) || (repeatedPasswordValue === null) || (repeatedPasswordValue.trim () === '')) {
				alert ('No has repetido la contraseña del usuario');
				repeatedPasswordField.focus ();
				return false;
			}
			if (passwordValue !== repeatedPasswordValue) {
				alert ('Las contraseñas del usuario no coinciden');
				passwordField.focus ();
				return false;
			}
		}

		field = form.find ('.lastname');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('No has suministrado los apellidos del usuario');
			field.focus ();
			return false;
		}

		field = form.find ('.role');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('No has suministrado el rol del usuario');
			field.focus ();
			return false;
		}

		field = form.find ('.status');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('No has suministrado el status del usuario');
			field.focus ();
			return false;
		}

		return true;
	};

	var validateUserProfile = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('.lastname');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('No has suministrado tus apellidos');
			field.focus ();
			return false;
		}

		return true;
	};

	window.UsersUtils = {
		addUsers:            addUsers,
		changeImage:         changeImage,
		deleteUser:          deleteUser,
		restoreImage:        restoreImage,
		saveAgent:           saveAgent,
		setAvatarData:       setAvatarData,
        setDefaultModule:    setDefaultModule,
		setDefaultOperMode:  setDefaultOperMode,
		showAvatarSelection: showAvatarSelection,
		showImageSelection:  showImageSelection,
		validateUser:        validateUser,
		validateUserProfile: validateUserProfile
	}
} (jQuery));
