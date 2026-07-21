(function (jQuery) {

    // public methods
    var changeStatusBoxScore = function (obj, boxscoreId, pageId) {
        var sendButton   = jQuery (obj),
            dummy        = window.location.href.split ('instance'),
            record       = sendButton.attr('data-record'),
            bsName       = sendButton.attr('data-bs-name'),
            theStatus    = sendButton.attr('data-status'),
            codeInstance = sendButton.attr('data-instance'),
            bsTitle      = jQuery ('#bs-title-' + boxscoreId + '-' + pageId).html(),
            arguments    = {
                'module':   'Settings',
                'action':   'AjaxBoxScoreUtils',
                'record':   parseInt (record),
                'name':     bsName,
                'status':   theStatus,
                'code':     codeInstance,
                'Ajax':     'true',
                'function': 'CHANGE_STATUS'
            },
            change = (theStatus === 'ENABLED') ? '𝐃𝐞𝐬𝐚𝐜𝐭𝐢𝐯𝐚𝐝o' : '𝐀𝐜𝐭𝐢𝐯𝐚𝐝o';
        if (!confirm ('Con esta acción el indicador "' + bsTitle +   '" quedará ' + change+ '\n ¿Continuar?')) {
            return
        }
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('El indicador ha sido ' + change + '!');
                    if (codeInstance !== 'MOTHER') {
                        if (dummy.length === 1) {
                            window.location.href = window.location.href + '&instance=' + codeInstance+ '&tab=BOX_SCORE_DAUGHTERS';
                        } else if(dummy.length > 1) {
                            window.location.href = dummy[0].slice(0, -1) + '&instance=' + codeInstance+ '&tab=BOX_SCORE_DAUGHTERS'
                        }
                    } else if (dummy.length > 1) {
                        window.location.href = dummy[0].slice(0, -1);
                    } else {
                        location.reload();
                    }
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    }

    var clonarBoxScore = function (obj, boxScoreId, pageId) {
        var button       = jQuery (obj),
            record       = button.attr('data-record'),
            bsName       = button.attr('data-bs-name'),
            shareModal   = jQuery ('#share_box_score-' + pageId),
            bsTitle      = jQuery ('#bs-title-' + boxScoreId + '-' + pageId).html(),
            arguments    = {
                'module':   'Settings',
                'action':   'AjaxBoxScoreUtils',
                'record':   parseInt (record),
                'name':     bsName,
                'title':    bsTitle,
                'id_page':  pageId,
                'Ajax':     'true',
                'function': 'CALL_CLONE_BOXSCORE'
        };
        button.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    shareModal.find('.modal-content').html (message.html);
                    shareModal.modal('show');
                }
            }
            catch (e) {
                alert(e);
            }
        });
        button.removeAttr('disabled');
    }

    var deleteBoxScore = function (obj, boxscoreId, pageId) {
        var deleteButton   = jQuery (obj),
            record       = deleteButton.attr ('data-record'),
            bsName       = deleteButton.attr ('data-bs-name'),
            codeInstance = deleteButton.attr ('data-instance'),
            bsTitle      = jQuery ('#bs-title-' + boxscoreId + '-' + pageId).html(),
            row          = jQuery ('#tabble-row-' + boxscoreId + '-' + pageId),
            arguments    = {
            'module':   'Settings',
                'action':   'AjaxBoxScoreUtils',
                'record':   parseInt (record),
                'name':     bsName,
                'code':     codeInstance,
                'Ajax':     'true',
                'function': 'DELETE_BOXSCORE'
            };
        if (!confirm ('Con esta acción el indicador "' + bsTitle +   '" quedará eliminado \n ¿Continuar?')) {
            return
        }
        deleteButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('El indicador ha sido Eliminado!');
                    row.remove()
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
            }
        });
        deleteButton.removeAttr('disabled');
    }

    var sendClonData = function (obj,pageId) {
        var btnShare   = jQuery (obj),
            form       = jQuery ('#form-share-box-score-' + pageId),
            infoText   = jQuery ('#info-clon-' + pageId),
            loadingIng = jQuery ('#loading-graphic-' + pageId);
        btnShare.attr('disabled','disabled');
        infoText.html('<p class="center">Este proceso tomará unos minutos, por favor espere sin cerrar esta ventana</p>');
        loadingIng.removeClass('hide');

        jQuery.post('index.php', form.serialize(), function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    infoText.html('<p class="center">El indicador ha sido copiado con éxito</p>');
                    loadingIng.addClass ('hide');
                    btnShare.attr ('disabled',false);
                }
            }
            catch (e) {
                infoText.html('<p class="center  text-danger">' + e + '</p>');
                loadingIng.addClass ('hide');
                btnShare.attr('disabled',false);
            }
        });

    }

    var getInstance = function (obj) {
        var instance       = jQuery (obj),
            dummy        = window.location.href.split ('instance'),
            codeInstance = instance.val(),
            actualInstance = instance.attr('data-instance');
        if (codeInstance === ''){
            instance.val(actualInstance);
            return false;
        } else {
            if (dummy.length === 1) {
                window.location.href = window.location.href + '&instance=' + codeInstance+ '&tab=BOX_SCORE_DAUGHTERS';
            } else if(dummy.length > 1) {
                window.location.href = dummy[0].slice(0, -1) + '&instance=' + codeInstance+ '&tab=BOX_SCORE_DAUGHTERS'
            }
        }
    }

    var setEditableBoxScore = function (obj, boxscoreId, pageId) {
        var sendButton   = jQuery (obj),
            dummy        = window.location.href.split ('instance'),
            record       = sendButton.attr('data-record'),
            isEditable   = sendButton.attr('data-is_editable'),
            codeInstance = sendButton.attr('data-instance'),
            bsTitle      = jQuery ('#bs-title-' + boxscoreId + '-' + pageId).html(),
            arguments    = {
                'module':   'Settings',
                'action':   'AjaxBoxScoreUtils',
                'record':   parseInt (record),
                'status':   isEditable,
                'code':     codeInstance,
                'Ajax':     'true',
                'function': 'CHANGE_EDITABLE'
            },
                change = (isEditable === 'NO') ? 'Liberado para edición' : 'bloqueado para edición';
            if (!confirm ('Con esta acción el indicador "' + bsTitle +   '" quedará ' + change+ '\n ¿Continuar?')) {
                return
            }
            sendButton.attr('disabled','disabled');
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if(message.error !== 'OK') {
                        throw message.error;
                    } else {
                        alert ('El indicador ha sido ' + change + '!');
                        if (codeInstance !== 'MOTHER') {
                            if (dummy.length === 1) {
                                window.location.href = window.location.href + '&instance=' + codeInstance+ '&tab=BOX_SCORE_DAUGHTERS';
                            } else if(dummy.length > 1) {
                                window.location.href = dummy[0].slice(0, -1) + '&instance=' + codeInstance+ '&tab=BOX_SCORE_DAUGHTERS'
                            }
                        } else if (dummy.length > 1) {
                            window.location.href = dummy[0].slice(0, -1);
                        } else {
                            location.reload();
                        }
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
            sendButton.removeAttr('disabled');
        }

    window.BoxScoreInventoryUtils = {
        changeStatusBoxScore: changeStatusBoxScore,
        clonarBoxScore:       clonarBoxScore,
        deleteBoxScore:       deleteBoxScore,
        getInstance:          getInstance,
        sendClonData:         sendClonData,
        setEditableBoxScore:  setEditableBoxScore
    };

    jQuery(document).on('ready', function () {

    });

}(jQuery));