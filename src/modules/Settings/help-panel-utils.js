(function (jQuery) {

    var changeStatusHelp = function (obj) {
        var sendButton = jQuery (obj),
            record     = sendButton.attr('data-record'),
            status     = sendButton.attr('data-status'),
            arguments  = {
                'module':   'Settings',
                'action':   'HelpSystemAjaxUtils',
                'record':   parseInt (record),
                'status':   status,
                'Ajax':     'true',
                'function': 'CHANGE_STATUS_FIELD_HELP'
            },
            change = (status === 'ENABLED') ? '𝐃𝐞𝐬𝐚𝐜𝐭𝐢𝐯𝐚𝐝𝐚' : '𝐀𝐜𝐭𝐢𝐯𝐚𝐝𝐚';
        if (!confirm ('Con esta acción la ayuda quedará ' + change+ '\n ¿Continuar?')) {
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
                    alert('La ayuda ha sido ' + change + '!');
                    if (location.href.indexOf('&tab=fields') !== -1) {
                        location.reload();
                    } else {
                        window.location = location.href +'&tab=fields';
                    }
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var changeHowToStatus = function (obj) {
        var sendButton = jQuery (obj),
            record     = sendButton.attr('data-record'),
            status     = sendButton.attr('data-status'),
            arguments  = {
            'module':   'Settings',
                'action':   'HelpSystemAjaxUtils',
                'record':   parseInt (record),
                'status':   status,
                'Ajax':     'true',
                'function': 'CHANGE_STATUS_HOW_TO'
            },
            change = (status === 'ENABLED') ? '𝐃𝐞𝐬𝐚𝐜𝐭𝐢𝐯𝐚𝐝𝐚' : '𝐀𝐜𝐭𝐢𝐯𝐚𝐝𝐚';
        if (!confirm ('Con esta acción la ayuda quedará ' + change+ '\n ¿Continuar?')) {
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
                    alert('La ayuda ha sido ' + change + '!');
                    if (location.href.indexOf('&tab=how_to') !== -1) {
                        location.reload();
                    } else {
                        window.location = location.href +'&tab=how_to';
                    }
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    }

    var changeEditableHelp = function (obj) {
        var sendButton = jQuery (obj),
            record     = sendButton.attr('data-record'),
            status     = sendButton.attr('data-status'),
            arguments  = {
                'module':   'Settings',
                'action':   'HelpSystemAjaxUtils',
                'record':   parseInt (record),
                'status':   status,
                'Ajax':     'true',
                'function': 'CHANGE_EDITABLE_FIELD_HELP'
            },
            change = (status === 'YES') ? '𝐃𝐞𝐬𝐚𝐜𝐭𝐢𝐯𝐚𝐝𝐚' : '𝐀𝐜𝐭𝐢𝐯𝐚𝐝𝐚';
        if (!confirm ('Con esta acción la edición del campo quedará ' + change+ '\n ¿Continuar?')) {
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
                    alert('La edición del campo ha sido ' + change + '!');
                    if (location.href.indexOf('&tab=fields') !== -1) {
                        location.reload();
                    } else {
                        window.location = location.href +'&tab=fields';
                    }
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var copyUrl = function (id) {
        var $temp = jQuery('<input>'),
            url   = jQuery ('#url-' + id);
        url.closest('.btn-group').append ($temp);
        $temp.val (url.val()).select();
        try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
            alert ('El comando copiar texto fue ' + msg);
        } catch (err) {
            alert ('Oops, no se puede copiar');
        }
        $temp.remove();
    }

    var deleteHelp = function (obj) {
        var sendButton   = jQuery (obj),
            record     = sendButton.attr('data-record'),
            arguments = {
                'module':   'Settings',
                'action':   'HelpSystemAjaxUtils',
                'record':   parseInt (record),
                'Ajax':     'true',
                'function': 'DELETE_FIELD_HELP',
                'from':     'COURSE'
            };
        if (!confirm ('La ayuda seleccionada será 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐨!\n ¿Continuar?')) {
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
                    alert('La ayuda ha sido 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐨!');
                    if (location.href.indexOf('&tab=fields') !== -1) {
                        location.reload();
                    } else {
                        window.location = location.href +'&tab=fields';
                    }
                }
            }
            catch (e) {
                alert(e);
            }
        });
        sendButton.removeAttr('disabled');
    };

    var deleteHowTop = function (obj) {
        var sendButton = jQuery (obj),
            assignData = sendButton.attr('data-assign').replace (/;/g, ' \n'),
            message    = '',
            record     = sendButton.attr('data-record'),
            arguments  = {
                'module':   'Settings',
                'action':   'HelpSystemAjaxUtils',
                'record':   parseInt (record),
                'Ajax':     'true',
                'function': 'DELETE_HOW_TO'
             };
        message = (assignData !== '') ? assignData : 'el HowTo seleccionado será 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐨!\n ¿Continuar?'
        if (!confirm (message)) {
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
                    alert('El HowTo ha sido 𝐄𝐥𝐢𝐦𝐢𝐧𝐚𝐝𝐨!');
                    if (location.href.indexOf('&tab=how_to') !== -1) {
                        location.reload();
                    } else {
                        window.location = location.href +'&tab=how_to';
                    }
                }
            } catch (e) {
                alert(e);
            }
        });
                sendButton.removeAttr('disabled');
    }

    var filterModule = function (obj) {
        var moduleSelected = jQuery (obj).val (),
            tbody            = jQuery('#help-field-panel-table > tr');
        if (moduleSelected !== '') {
            jQuery('tr[id ^= row-' + moduleSelected+ '-]').removeClass ('hide');
            tbody.each(function (i, elemnet) {
                var tr = jQuery (elemnet),
                    trId = tr.attr('id').split('-');
                if (trId[ 1 ] !== moduleSelected) {
                    tr.addClass('hide')
                }
            })
        } else {
            tbody.each(function (i, elemnet) {
                jQuery (elemnet).removeClass ('hide');
            })
        }
    };

    window.HelpSysPanelUtils = {
        changeEditableHelp: changeEditableHelp,
        changeHowToStatus:  changeHowToStatus,
        changeStatusHelp:   changeStatusHelp,
        copyUrl:            copyUrl,
        deleteHelp:         deleteHelp,
        deleteHowTop:       deleteHowTop,
        filterModule:       filterModule
    };

    jQuery (document).ready (function () {
    });
} (jQuery));