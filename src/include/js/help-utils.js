(function (jQuery) {
	// Private variables
	var modal = null;

	// Private methods
	var contains = function (array, string) {
		var i, found;
		if ((array === null) || (!jQuery.isArray (array))) {
			return false;
		}

		found = false;
		for (i = 0; i < array.length; i += 1) {
			if (array [ i ].indexOf (string) !== -1) {
				found = true;
				break;
			}
		}
		return found;
	};

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var scrollToRelatedContent = function (modalTabId, helpItemId) {
		var tab;
		if ((!modalTabId) || (!helpItemId)) {
			return;
		}

		tab = modal.find ('a[href="#' + modalTabId + '"]');
		if (tab.length === 0) {
			return;
		}
		modal.find (tab).click ();
		modal.find ('#' + modalTabId).find ('#' + helpItemId).closest ('.panel').find ('.panel-heading > .panel-title > .accordion-toggle').click ();
		modal.find ('.modal-body').animate ({
			scrollTop: jQuery ('#' + helpItemId).position ().top
		}, 'slow');
	};

	var trimAndToLowerCase = function (string) {
		return string ? string.trim ().toLowerCase () : '';
	};

	// Public methods
	var addFields = function (obj) {
        var helpFieldId = parseInt (jQuery(obj).attr('data-id-btn')),
            moduleName  = jQuery (obj).attr ('data-module');

        ekkoLightBox = jQuery('<a href=index.php?module=' + moduleName + '&action=AjaxEditViewUtils&function=ADD_FIELD_HELP&Ajax=true&idhelp=' + helpFieldId + ' data-toggle="lightbox" data-max-width="600" data-title="Agregar campo">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
                var modalBackdrop = jQuery('.modal-backdrop');
                modalBackdrop.removeClass('bottom');
                modalBackdrop.removeClass('z-index');
                if (ekkoLightBox.attr('data-process') === 'YES') {
                    location.reload()
                }
            }
        });
        event.stopPropagation();
        event.preventDefault();
    };

	var addPicklistValue = function (obj, id) {
		var btn        = jQuery (obj),
			msn        =jQuery('#help-pick-list-msn-' + id),
			tableBody  = jQuery('#help-pick-list-' + id).find('tbody'),
			trLast     = tableBody.find('tr:last'),
			idLastt    = trLast.attr('id'),
			beforeLast = trLast.prev(),
			sequence   = parseInt(beforeLast.find('input').eq(1).val()) + 1,
			newTr     = trLast.clone();
		btn.attr('disabled', 'disabled');
		newTr.attr ('id', '');
		newTr.addClass ('picklist-value').removeClass ('hide');
        newTr.find('input').eq(1).val(sequence);
		newTr.insertBefore (trLast);
		msn.html('Se ha incluido una fila al final de la lista');
        setTimeout(function(){
        	btn.removeAttr('disabled');
            msn.html('');
        	}, 2000);
    };

	var clearFilter = function (buttonElement) {
		jQuery (buttonElement).closest ('.filter-group').find ('.keyword').val ('');
		jQuery (buttonElement).closest ('.filter-group').find ('.application-code').val ('');
	};

	var changeFieldLabel = function (obj, id) {
        var sendButton = jQuery(obj),
            form = jQuery('#form-help-label-' + id),
            newLabel = jQuery('#label-' + id).val();
        if ((newLabel === null) || (newLabel === undefined) || (newLabel.trim() === '')) {
            jQuery('#help-label-' + id).html('𝐔𝐩𝐨𝐨𝐬! El nombre del campo no puede estar vacío');
        } else {
            sendButton.attr('disabled', 'disabled');
				jQuery.post('index.php', form.serialize(), function (data) {
				var message;
				try {
					message = JSON.parse(JSON.stringify(data));
					if (message.error !== 'OK') {
						throw message.error;
					} else {
						alert('El nombre del campo ha sido cambiado con éxito \n 𝑺𝒆 𝒓𝒆𝒄𝒂𝒓𝒈𝒂𝒓𝒂́ 𝒆𝒔𝒕𝒂 𝒑𝒂𝒈𝒊𝒏𝒂...');
                        location.reload()
					}
				}
				catch (e) {
					alert(e);
				}
        	});
        	sendButton.removeAttr('disabled');
    	}
	};

	var deletePicklistValue = function (obj) {
        var button          = jQuery (obj);
        if (!confirm ('Vas a eliminar el valor seleccionado. ¿Estás seguro?')) {
            return;
        }
        button.closest ('.picklist-value').remove ();
    };

	var filterByKeyword = function (formElement) {
		var form    = jQuery (formElement),
			keyword = form.find ('.keyword').val (),
			items   = form.closest ('.tab-pane').find ('.help-items .help-item'),
			item, tags, i;

		for (i = 0; i < items.length; i += 1) {
			item = jQuery (items [ i ]);
			tags = item.data ('tags') ? jQuery.map (item.data ('tags').split (','), trimAndToLowerCase) : [];
			if ((keyword.trim () === '') || (contains (tags, keyword.trim ().toLowerCase ()))) {
				item.show ();
			} else {
				item.hide ();
			}
		}
	};

	var filterByKeywordAndApplicationCode = function (formElement) {
		var form            = jQuery (formElement),
			keyword         = form.find ('.keyword').val (),
			applicationCode = form.find ('.application-code').val (),
			items           = form.closest ('.tab-pane').find ('.help-items .help-item'),
			item, applicationCodes, tags, i;

		for (i = 0; i < items.length; i += 1) {
			item = jQuery (items [ i ]);
			applicationCodes = item.data ('application-codes') ? jQuery.map (item.data ('application-codes').split (','), trimAndToLowerCase) : [];
			tags = item.data ('tags') ? jQuery.map (item.data ('tags').split (','), trimAndToLowerCase) : [];
			if ((keyword.trim () === '') && (applicationCode.trim () === '')) {
				item.show ();
			} else if ((keyword.trim () === '') && (applicationCode.trim () !== '')) {
				if (contains (applicationCodes, applicationCode.trim ().toLowerCase ())) {
					item.show ();
				} else {
					item.hide ();
				}
			} else if ((keyword.trim () !== '') && (applicationCode.trim () === '')) {
				if (contains (tags, keyword.trim ().toLowerCase ())) {
					item.show ();
				} else {
					item.hide ();
				}
			} else {
				if ((contains (applicationCodes, applicationCode.trim ().toLowerCase ())) && (contains (tags, keyword.trim ().toLowerCase ()))) {
					item.show ();
				} else {
					item.hide ();
				}
			}
		}
	};

	var showHelp = function (moduleName, modalTabId, helpItemId) {
		var arguments = [
			'module=Settings',
			'action=GetHelpItems',
			'modulename=' + encodeURIComponent (moduleName),
			'Ajax=true'
		];

		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (function (response) {
			var modalTemplate = jQuery ('#help-modal-template'),
				activeTab     = null,
				itemTemplate, videoTemplate, i, item, videos, articles;

			modal = jQuery (modalTemplate.html ());
			if ((response.hasOwnProperty ('tips')) && (jQuery.isPlainObject (response [ 'tips' ])) && (response [ 'tips' ].hasOwnProperty ('items')) && (jQuery.isArray (response [ 'tips' ][ 'items' ])) && (response [ 'tips' ][ 'items' ].length > 0)) {
				itemTemplate = jQuery ('#help-modal-tip-template');
				for (i = 0; i < response [ 'tips' ][ 'items' ].length; i += 1) {
					item = jQuery (itemTemplate.html ());
					item.attr ('data-tags', response [ 'tips' ][ 'items' ][ i ][ 'tags' ]);
					item.find ('.tip-title').attr ('href', '#tip-' + response [ 'tips' ][ 'items' ][ i ][ 'id' ])
						.text (response [ 'tips' ][ 'items' ][ i ][ 'title' ]);
					item.find ('.tip-content').attr ('id', 'tip-' + response [ 'tips' ][ 'items' ][ i ][ 'id' ]);
					item.find ('.tip-description').text (response [ 'tips' ][ 'items' ][ i ][ 'description' ]);
					modal.find ('#panel-tips').append (item);
				}
				modal.find ('#tab-tips > .description').text (response [ 'tips' ][ 'description' ]);
				if (activeTab === null) {
					modal.find ('.tab-tips-control').addClass ('active');
					modal.find ('#send_help_request').hide ();
					activeTab = 'tips';
				}
			} else {
				modal.find ('.tab-tips-control').remove ();
			}

			if ((response.hasOwnProperty ('tutorials')) && (jQuery.isPlainObject (response [ 'tutorials' ])) && (response [ 'tutorials' ].hasOwnProperty ('items')) && (jQuery.isArray (response [ 'tutorials' ][ 'items' ])) && (response [ 'tutorials' ][ 'items' ].length > 0)) {
				videoTemplate = jQuery ('#help-modal-video-template');
				itemTemplate = jQuery ('#help-modal-article-template');
				for (i = 0; i < response [ 'tutorials' ][ 'items' ].length; i += 1) {
					if (response [ 'tutorials' ][ 'items' ][ i ][ 'tutorialtype' ] === 'VIDEO') {
						item = jQuery (videoTemplate.html ());
						item.attr ('data-tags', response [ 'tutorials' ][ 'items' ][ i ][ 'tags' ])
							.attr ('id', 'video-' + response [ 'tutorials' ][ 'items' ][ i ][ 'id' ]);
						if (jQuery.isArray (response [ 'tutorials' ][ 'items' ][ i ][ 'applicationcodes' ])) {
							item.attr ('data-application-codes', response [ 'tutorials' ][ 'items' ][ i ][ 'applicationcodes' ].join (', '));
						}
						item.find ('.video-title').text (response [ 'tutorials' ][ 'items' ][ i ][ 'title' ]);
						item.find ('iframe').attr ('src', response [ 'tutorials' ][ 'items' ][ i ][ 'urliframe' ] + '&modestbranding=1&showinfo=0');
						modal.find ('#panel-videos').append (item);
					} else {
						item = jQuery (itemTemplate.html ());
						item.attr ('data-tags', response [ 'tutorials' ][ 'items' ][ i ][ 'tags' ])
							.attr ('id', 'article-' + response [ 'tutorials' ][ 'items' ][ i ][ 'id' ]);
						if (jQuery.isArray (response [ 'tutorials' ][ 'items' ][ i ][ 'applicationcodes' ])) {
							item.attr ('data-application-codes', response [ 'tutorials' ][ 'items' ][ i ][ 'applicationcodes' ].join (', '));
						}
						item.find ('.article-title').attr ('href', response [ 'tutorials' ][ 'items' ][ i ][ 'url' ]).text (response [ 'tutorials' ][ 'items' ][ i ][ 'title' ]);
						modal.find ('#panel-articles').append (item);
					}
				}
				modal.find ('#tab-walkthroughs > .description').text (response [ 'tutorials' ][ 'description' ]);
				if (activeTab === null) {
					modal.find ('.tab-walkthroughs-control').addClass ('active');
					modal.find ('#send_help_request').hide ();
					activeTab = 'tutorials';
				}
			} else {
				modal.find ('.tab-walkthroughs-control').remove ();
			}

			if ((response.hasOwnProperty ('usecases')) && (jQuery.isPlainObject (response [ 'usecases' ])) && (response [ 'usecases' ].hasOwnProperty ('items')) && (jQuery.isArray (response [ 'usecases' ][ 'items' ])) && (response [ 'usecases' ][ 'items' ].length > 0)) {
				itemTemplate = jQuery ('#help-modal-article-template');
				for (i = 0; i < response [ 'usecases' ][ 'items' ].length; i += 1) {
					item = jQuery (itemTemplate.html ());
					item.attr ('data-tags', response [ 'usecases' ][ 'items' ][ i ][ 'tags' ])
						.attr ('id', 'usecase-' + response [ 'usecases' ][ 'items' ][ i ][ 'id' ]);
					if (jQuery.isArray (response [ 'usecases' ][ 'items' ][ i ][ 'applicationcodes' ])) {
						item.attr ('data-application-codes', response [ 'usecases' ][ 'items' ][ i ][ 'applicationcodes' ].join (', '));
					}
					item.find ('.article-title').attr ('href', response [ 'usecases' ][ 'items' ][ i ][ 'url' ]).text (response [ 'usecases' ][ 'items' ][ i ][ 'title' ]);
					modal.find ('#panel-usecases').append (item);
				}
				modal.find ('#tab-usecases > .description').text (response [ 'usecases' ][ 'description' ]);
				if (activeTab === null) {
					modal.find ('.tab-usecases-control').addClass ('active');
					modal.find ('#send_help_request').hide ();
					activeTab = 'usecases';
				}
			} else {
				modal.find ('.tab-usecases-control').remove ();
			}

			if ((response.hasOwnProperty ('questions')) && (jQuery.isPlainObject (response [ 'questions' ])) && (response [ 'questions' ].hasOwnProperty ('items')) && (jQuery.isArray (response [ 'questions' ][ 'items' ])) && (response [ 'questions' ][ 'items' ].length > 0)) {
				itemTemplate = jQuery ('#help-modal-question-template');
				for (i = 0; i < response [ 'questions' ][ 'items' ].length; i += 1) {
					item = jQuery (itemTemplate.html ());
					item.attr ('data-tags', response [ 'questions' ][ 'items' ][ i ][ 'tags' ]).attr ('data-application-code', response [ 'questions' ][ 'items' ][ i ][ 'applicationcode' ]);
					item.find ('.question-title').attr ('href', '#question-' + response [ 'questions' ][ 'items' ][ i ][ 'id' ]).text (response [ 'questions' ][ 'items' ][ i ][ 'title' ]);
					item.find ('.question-content').attr ('id', 'question-' + response [ 'questions' ][ 'items' ][ i ][ 'id' ]);
					item.find ('.question-description').text (response [ 'questions' ][ 'items' ][ i ][ 'description' ]);
					modal.find ('#panel-faq').append (item);
				}
				modal.find ('#tab-faq > .description').text (response [ 'questions' ][ 'description' ]);
				if (activeTab === null) {
					modal.find ('.tab-faq-control').addClass ('active');
					modal.find ('#send_help_request').hide ();
					activeTab = 'questions';
				}
			} else {
				modal.find ('.tab-faq-control').remove ();
			}

			if (activeTab === null) {
				modal.find ('#tab-support').addClass ('active');
				modal.find ('#send_help_request').show ();
			}

			modal
				.modal ({ backdrop: 'static' })
				.on ('shown.bs.modal', function () {
					scrollToRelatedContent (modalTabId, helpItemId);
				})
				.on ('hidden.bs.modal', destroyModal);
		}).fail (function () {
			alert ('Se ha presentado un error. Intenta más tarde');
		});
		return false;
	};

	var showHelpByField = function (obj){
		var helpFieldId = parseInt (jQuery(obj).attr('data-help-id')),
			modalTitle  = jQuery(obj).attr ('title'),
			moduleName  = jQuery (obj).attr ('data-module');

        ekkoLightBox = jQuery('<a href=index.php?module=' + moduleName + '&action=AjaxEditViewUtils&function=SHOW_FIELD_HELP&Ajax=true&record='+helpFieldId+' data-toggle="lightbox" data-max-width="800" data-title="' + modalTitle + '">&nbsp;</a>');
        ekkoLightBox.ekkoLightbox({
            loadingMessage: "Cargando...",
            onHidden: function () {
                var modalBackdrop = jQuery('.modal-backdrop');
                modalBackdrop.removeClass('bottom');
                modalBackdrop.removeClass('z-index');
                if (ekkoLightBox.attr('data-process') === 'YES') {
                    location.reload()
                }
            }
        });
        event.stopPropagation();
        event.preventDefault();
	};

	var ShowIconHelp = function (obj) {
		var btnIconHelp  = jQuery(obj),
			id           = btnIconHelp.attr ('id'),
			btnAdd       = jQuery('#' + id +'-add-field'),
			btnColor     = btnIconHelp.attr ('data-color');

		if (btnIconHelp.hasClass('btn-default')) {
            jQuery('span[id ^= ' + id + '-]').show().parent().addClass('animate__headShake').addClass('animate__repeat-3');
            btnAdd.show().addClass('animate__fadeIn');
            btnIconHelp.removeClass('btn-default').addClass('btn-primary');

		} else {
            jQuery('span[id ^= ' + id + '-]').hide().parent().removeClass('animate__headShake').removeClass('animate__repeat-3');
            btnAdd.hide ().removeClass('animate__fadeIn');
            btnIconHelp.removeClass('btn-primary').addClass('btn-default');
		}


    };

	var sendSupportRequest = function () {
		var description = modal.find ('#help-request-description');
		if (description.val ().trim () === '') {
			alert ('Debe indicar el mensaje');
			description.focus ();
			return false;
		}
		jQuery.ajax ('index.php', {
			data:     {
				module:    'Settings',
				action:    'SettingsAjax',
				file:      'sendEmail',
				Ajax:      'true',
				categoria: modal.find ('#categoria').val (),
				mensaje:   description.val ()
			},
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			alert (response);
			modal.find ('#help-request-description').val ('');
		}).fail (function (jQueryResponse) {
			alert (jQueryResponse.responseText);
		});
	};

	var movePickListRowDown = function (btn) {
        var rowToMove = jQuery (btn).parent ().parents ('tr.picklist-value'),
            idTo         = rowToMove.find('input').eq(0),
            seqTo        = rowToMove.find('input').eq(1),
            next         = rowToMove.next ('tr.picklist-value'),
            idNext       = next.find('input').eq (0),
            seqNext      = next.find('input').eq (1),
            tempValue    = idNext.val (),
            tempSeqValue = seqNext.val ();

        idNext.val (idTo.val ());
        idTo.val (tempValue);
        //seqNext.val(seqTo.val ());
        //seqTo.val (tempSeqValue);
        next.after (rowToMove);
    };

	var movePickListRowUp = function (btn) {
        var rowToMove = jQuery (btn).parent ().parents ('tr.picklist-value'),
            idTo      = rowToMove.find('input').eq(0),
			seqTo     = rowToMove.find('input').eq(1),
            prev      = rowToMove.prev ('tr.picklist-value'),
            idPrev    = prev.find('input').eq (0),
            seqPrev    = prev.find('input').eq (1),
            tempValue    = idPrev.val (),
            tempSeqValue = seqPrev.val ();

        idPrev.val (idTo.val ());
        idTo.val (tempValue);
        //seqPrev.val(seqTo.val ());
        //seqTo.val (tempSeqValue);
        prev.before (rowToMove);
    };

	var savePicklistValue = function (obj, id, fieldName, moduleName) {
		var arguments,
			sendButton = jQuery(obj),
			sequence   = [],
			values     = [];

		jQuery('#help-pick-list-' + id + ' > tbody  > tr').each(function(index, tr) {
            if(!jQuery(tr).hasClass('hide')) {
            	var seq, id, label, row = jQuery(tr);
            	id    = row.find('input').eq(0).val();
                seq   = row.find('input').eq(1).val();
                label = row.find('input').eq(2).val();
                if ((label !== null) && (label !== undefined) && (label.trim() !== '')) {
                    sequence[index] = seq;
                    values[index] = label + '@' + id;
                }
			}
        });

        arguments = {
            'module':   encodeURIComponent (moduleName),
            'action':   'AjaxEditViewUtils',
            'Ajax':     true,
            'function': 'UPDATE_PICK_LIST',
            'fieldName': encodeURIComponent (fieldName),
            'picklist': values,
			'sequence': sequence
        };
        sendButton.attr('disabled', 'disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('El campo lista se ha actualizado con éxito! \n 𝑺𝒆 𝒓𝒆𝒄𝒂𝒓𝒈𝒂𝒓𝒂́ 𝒆𝒔𝒕𝒂 𝒑𝒂𝒈𝒊𝒏𝒂...');
                    location.reload()
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

	var setFieldVisibility = function (obj, mandatory, moduleName, fieldId) {
        var sendButton = jQuery(obj), arguments = {};
		if (mandatory !== '') {
            var infoMandatory  = 'No se puede ocultar el campo' + '\n';
            infoMandatory += 'ya que es un campo obligatorio' + '\n';
            infoMandatory += 'Debe cambiar esa condición' + '\n';
            alert (infoMandatory);
            return;
        } else {
			arguments = {
				'module': encodeURIComponent (moduleName),
				'action': 'AjaxEditViewUtils',
				'Ajax':   true,
				'function': 'HIDDEN_FIELDS',
				'fieldid': fieldId
			};
            sendButton.attr('disabled', 'disabled');
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        alert('El campo se ha ocultado con éxito! \n 𝑺𝒆 𝒓𝒆𝒄𝒂𝒓𝒈𝒂𝒓𝒂́ 𝒆𝒔𝒕𝒂 𝒑𝒂𝒈𝒊𝒏𝒂...');
                        location.reload()
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
            sendButton.removeAttr('disabled');
        }
    };

	var setMandatory = function (obj, madatory, fieldName, moduleName) {
        var sendButton = jQuery(obj),
            arguments = {
                'module':   encodeURIComponent (moduleName),
                'action':   'AjaxEditViewUtils',
                'Ajax':     true,
                'function': 'MANDATORY_FIELDS',
                'fieldName': encodeURIComponent (fieldName),
				'mandatory': madatory
            };
            sendButton.attr('disabled', 'disabled');
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        alert('El campo se ha actualizado con éxito! \n 𝑺𝒆 𝒓𝒆𝒄𝒂𝒓𝒈𝒂𝒓𝒂́ 𝒆𝒔𝒕𝒂 𝒑𝒂𝒈𝒊𝒏𝒂...');
                        location.reload()
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
            sendButton.removeAttr('disabled');
    };

	window.HelpUtils = {
		addFields:                         addFields,
        addPicklistValue:                  addPicklistValue,
		clearFilter:                       clearFilter,
		changeFieldLabel:                  changeFieldLabel,
        deletePicklistValue:               deletePicklistValue,
		filterByKeyword:                   filterByKeyword,
		filterByKeywordAndApplicationCode: filterByKeywordAndApplicationCode,
        movePickListRowDown:               movePickListRowDown,
        movePickListRowUp:				   movePickListRowUp,
        savePicklistValue:                 savePicklistValue,
		sendSupportRequest:                sendSupportRequest,
        setFieldVisibility:                setFieldVisibility,
        setMandatory:                      setMandatory,
		showHelp:                          showHelp,
		showHelpByField:                   showHelpByField,
		ShowIconHelp:                      ShowIconHelp
	};
} (jQuery));
