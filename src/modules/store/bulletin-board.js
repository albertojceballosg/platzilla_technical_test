(function (jQuery) {

	var password = '';

	var createFormativeInstance = function () {
		if (!validateRegisterForm ()) {
			return false;
		} else {
			var form      = (password === '') ? jQuery ('#bolletin-board-form') : jQuery ('#ebook-bording-form'),
                arguments = form.serialize(),
                tab       = (password === '') ? '' : '&tab=MATERIALS&ebook=' + jQuery('#ebookId').val (),
				interval, i, messages;

			jQuery ('body').css ({
				overflow: 'hidden'
			});
			jQuery ('#clock').show ();
			messages = [
				'Recopilando información necesaria',
				'Instalando módulos',
				'Instalando aplicaciones',
				'Configurando permisologías',
				'Personalizando la nueva cuenta',
				'Ejecutando limpieza final',
				'Preparando para iniciar por primera vez'
			];
			i = 0;
			interval = setInterval (function () {
				jQuery ('#clock').find ('.message').text (messages [ i ]);
				i += 1;
				if (i === 7) {
					clearInterval (interval);
				}
			}, Math.random () * (10000 - 7000) + 7000);

            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if(message.error !== 'OK') {
                        throw message.error;
                    } else {
                        location.href = 'index.php?module=Home&action=index' + tab;
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
		}
	};

	var sendPassWord = function() {
		var form          = jQuery ('#ebook-bording-form'),
			actionForm    = jQuery ('#action-form-bording'),
			userEmail     = jQuery ('#usuarioEmail'),
            errorEmail    = jQuery ('#error_email'),
			infoText      = jQuery ('#onbording-info'),
			passContainer = jQuery ('#onbording-password'),
			sendButton    = jQuery ('#submitBtn'),
            doAction      = jQuery ('#onbordig-process');

        if (!userEmail.val ()) {
            errorEmail.html ('Debes suministrar tu dirección de correo electrónico');
            userEmail.trigger ('focus');
            return false;
        }

        regex = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
        if (!regex.test (userEmail.val ())) {
            errorEmail.html ('Debes suministrar una dirección de correo válida');
            userEmail.trigger ('focus');
            return false;
        }
        sendButton.attr('disabled','disabled');
        doAction.html ('<img src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block" />');
        password = atob (jQuery ('#code-token-bordig').val());

        jQuery.post('index.php', form.serialize(), function (data) {
            var message, info;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    doAction.html ('&nbsp;');
                    info = infoText.html ();
                    info = info.replace ('__EMAIL__' , userEmail.val());

                    infoText.html (info);
                    passContainer.removeClass ('hide');
                    actionForm.val ('CreateFormativeInstance');
                    sendButton.html ('<span>Entrar </span> <span class="fa fa-arrow-right"></span>');
                    sendButton.attr ('onclick', 'BulletinBoardUtils.createFormativeInstance ()')
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');

	};

	var validateRegisterForm = function () {
		var erorr        = true,
			errorAdQueue = jQuery ('#error-queue'),
			errorEmail   = jQuery ('#error_email'),
            errorPass    = jQuery ('#error_pass'),
            checked      = [],
			regex, field, passwordField;

        errorAdQueue.html ('');
		errorEmail.html ('');
        if (password === '') {
            jQuery("input[name='adQueueIds[]']:checked").each(function () {
                checked.push(parseInt(jQuery(this).val()));
            });

            if (checked.length === 0) {
                errorAdQueue.html('Debes seleccionar al menos un item de información');
                erorr = false;
            }
        } else {
            errorPass.html ('');
            field = jQuery ('#userPass');
            if (!field.val ()) {
                errorPass.html ('Debes suministrar la clave enviada');
                field.trigger ('focus');
                return false;
            } else if (field.val () !== password) {
                errorPass.html ('clave incorrecta!');
                field.trigger ('focus');
                return false;
            }
        }

		field = jQuery ('#usuarioEmail');
		if (!field.val ()) {
			errorEmail.html ('Debes suministrar tu dirección de correo electrónico');
			field.trigger ('focus');
			return false;
		}

		regex = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
		if (!regex.test (field.val ())) {
			errorEmail.html ('Debes suministrar una dirección de correo válida');
			field.trigger ('focus');
            erorr = false;
		}

		return erorr;
	};

	window.BulletinBoardUtils = {
		createFormativeInstance: createFormativeInstance,
        sendPassWord:            sendPassWord,
		validateRegisterForm:    validateRegisterForm
	};
} (jQuery));
