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

	var saveFieldsOrdering = function (evt) {
		var block = jQuery (evt.currentTarget),
			fields, i, field, arguments;

		if (block.length === 0) {
			return;
		}

		arguments = [
			'module=Settings',
			'action=SaveFieldsSequences',
			'blockid=' + encodeURIComponent (block.data ('id')),
			'Ajax=true'
		];
		fields = block.find ('.block-field');
		for (i = 0; i < fields.length; i += 1) {
			field = jQuery (fields [i]);
			arguments.push ('sequences[' + field.data ('id') + ']=' + (i + 1));
		}

		jQuery.ajax ('index.php', {
			data: arguments.join ('&'),
			dataType: 'json',
			method: 'post'
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

	var validateForm = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#block-label');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce la etiqueta del bloque');
			field.focus ();
			return false;
		}

		field = form.find ('#block-sequence');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el bloque siguiente');
			field.focus ();
			return false;
		}

		return true;
	};

	// Public functions
	var deleteBlock = function (moduleName, blockId, totalFields) {
		var arguments;

		if (totalFields > 0) {
			alert ('El bloque contiene campos. Debes eliminarlos antes de poder eliminar el bloque');
			return;
		} else if (!confirm ('¿Estás seguro que quieres eliminar el bloque seleccionado?')) {
			return;
		}

		arguments = [
			'module=Settings',
			'action=DeleteBlock',
			'modulename=' + encodeURIComponent (moduleName),
			'blockid=' + encodeURIComponent (blockId),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			alert ('El bloque ha sido eliminado');
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

	var hideBlockLabelForm = function (formElement) {
		var form     = jQuery (formElement),
			oldlabel = form.closest ('.block-label').find ('.old-label');
		form.addClass ('hidden').find ('.new-label').val (oldlabel.text ());
		oldlabel.removeClass ('hidden');
	};

	var moveBlock = function (moduleName, blockId, sequence) {
		var arguments;

		arguments = [
			'module=Settings',
			'action=MoveBlock',
			'modulename=' + encodeURIComponent (moduleName),
			'blockid=' + encodeURIComponent (blockId),
			'sequence=' + encodeURIComponent (sequence),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function () {
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

	var openModal = function (moduleName) {
		var modalTemplate = jQuery ('#block-modal-template');

		modal = jQuery (modalTemplate.html ());
		modal.find ('input[name="modulename"]').val (moduleName);
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var saveBlock = function (formElement) {
		if (!validateForm (formElement)) {
			return;
		}

		jQuery.ajax ('index.php', {
			data:     jQuery (formElement).serialize (),
			dataType: 'json',
			'method': 'post'
		}).done (function () {
			alert ('El bloque ha sido creado');
			modal.modal ('hide');
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

	var saveBlockLabel = function (formElement) {
		var form     = jQuery (formElement),
			oldlabel = form.closest ('.block-label').find ('.old-label'),
			newlabel = form.find ('.new-label');
		if (newlabel.val () === oldlabel.text ()) {
			hideBlockLabelForm (formElement);
			return;
		}
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			oldlabel.text (newlabel.val ());
			form.addClass ('hidden');
			oldlabel.removeClass ('hidden');
		}).fail (function (jQueryResponse) {
			var message;
			try {
				message = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				message = 'Se ha presentado un error. Intenta más tarde';
			}
			alert (message);
		});
		return false;
	};

	var showBlockLabelForm = function (labelElement) {
		var label = jQuery (labelElement),
			form  = label.closest ('.block-label').find ('.block-label-form');
		label.addClass ('hidden');
		form.removeClass ('hidden');
	};

	var updateVisibility = function (obj, event) {
		var link       = jQuery (obj),
			blockName  = link.attr ('data-block-name'),
			blockTitle = link.parent ().parent ().prev ().find ('h2'),
			idBlock    = link.attr ('rel'),
			span       = link.find('span').eq (0),
			mandatory  = link.attr ('data-mandatory-fields'),
			visibility = parseInt (link.attr ('data-visibility')),
			verified   = true,infoMandatory;

		if (mandatory !== 'NO-MANDATORY') {
			mandatory = JSON.parse (mandatory).join ('\n - ');
			infoMandatory  = 'No se puede ocultar el bloque: ' + blockName + '\n';
			infoMandatory += 'ya que contiene los siguientes campos obligatorios: \n - ' + mandatory + '\n';
			infoMandatory += 'Debe editar cada uno de los campos para cambiar esa condición';
			alert (infoMandatory);
			event.preventDefault ();
			return;
		}

		link.parent().addClass ('isDisabled');
		if (!visibility) {
			verified = confirm('¿Ocultar el bloque: ' + blockName +' ?')
		}
        if (verified) {
            arguments = {
                'module':     'Settings',
                'action':     'UpdateBlockVisibility',
                'Ajax':       'true',
                'blockId':    idBlock,
				'visibility': visibility
            };

            jQuery.post('index.php', arguments, function (data) {
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if(message.error !== 'OK') {
                        throw message.error;
                    } else {
                    	if (visibility) {
							span.removeClass ('glyphicon-eye-close');
							span.addClass ('glyphicon-eye-open');
                            link.attr ('data-visibility', '0');
                            link.attr ('title', 'Ocultar bloque');
                            blockTitle.removeClass ('block-hide');
						} else {
                            span.removeClass('glyphicon-eye-open');
                            span.addClass ('glyphicon-eye-close');
                            link.attr ('data-visibility', '1');
                            link.attr ('title', 'Mostrar bloque');
                            blockTitle.addClass ('block-hide');
						}
                        alert('El bloque ha sido actualizado');
                        link.parent().removeClass ('isDisabled');
                    }
                }
                catch (e) {
                    alert(e);
                    link.parent().removeClass ('isDisabled');
                }
            });
        } else {
            link.parent().removeClass ('isDisabled');
        }
        event.preventDefault ();
        event.stopPropagation ();
    };

    window.BlockUtils = {
		deleteBlock:        deleteBlock,
		hideBlockLabelForm: hideBlockLabelForm,
		moveBlock:          moveBlock,
		openModal:          openModal,
		saveBlock:          saveBlock,
		saveBlockLabel:     saveBlockLabel,
		showBlockLabelForm: showBlockLabelForm,
        updateVisibility:   updateVisibility
    };

	jQuery (document).ready (function () {
		var blockHandles = jQuery ('#layout-editor').find ('.block-fields'),
			i;

		if (blockHandles.length === 0) {
			return;
		}

		for (i = 0; i < blockHandles.length; i += 1) {
			jQuery (blockHandles [ i ])
				.nestable ({ group: i })
				.on ('change', saveFieldsOrdering);
		}
	});
} (jQuery));
