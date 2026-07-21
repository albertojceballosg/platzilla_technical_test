(function (jQuery) {
    var excludeFields  = ['module', 'action', 'record', 'gridviewname', 'gridbox[]'],
        excludeElement = ['button', 'submit', 'select-multiple', 'checkbox', 'undefined'];

    var changeStatus = function (obj){
        var form       = jQuery(obj).parent(),
            arguments  = form.serialize(),
            status     = form.find('input[name=viewstatus]').val(),
            label      = form.find('input[name=tablabel]').val(),
            sendButton = jQuery (obj);

        if (status === 'DISABLED') {
            if (!confirm('Esta operación deshabilitará cualquier otra vista del modulo ' + label +' ¿Continuar?')) {
                return
            }
        }
        sendButton.attr('disabled','disabled');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('la vista ha sido actualizada con éxito. \n Se actualizará esta pagina. Por favor espere');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
                sendButton.removeAttr('disabled');
            }
        });
    };

    var deleteGridView = function (obj) {
        var form       = jQuery(obj).parent(),
            arguments  = form.serialize(),
            tablabel   = form.find('input[name=tablabel]').val(),
            viewlabel  = form.find('input[name=viewlabel]').val(),
            sendButton = jQuery (obj);

        if (!confirm('¿Eliminar la vista '+ viewlabel +' del módulo '+ tablabel +'?')) {
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
                    alert('la vista ha sido eliminada con éxito. \n Se actualizará esta pagina. Por favor espere');
                    location.reload();
                }
            }
            catch (e) {
                alert(e);
                sendButton.removeAttr('disabled');
            }
        });
    };

    var validateForm = function (objForm) {
        var form        = jQuery (objForm),
            formElement = jQuery("form[name='" + form.attr ('name') +"'] :input"),
            isValidate  = true,
            totalBoxes  = 0,
            field, operationValue, value;
        jQuery('span[id ^= gv-]').html('');
        jQuery('div[id ^= gv-div-]').removeClass('has-error');
        //girdviewbox
        formElement.map(function (index, elm) {
            var element = jQuery(elm),
                elementTitle = element.attr('title'),
                elementName  = element.attr ('name'),
                value = element.val();
            if ((jQuery.inArray(elm.type, excludeElement) === -1) && (jQuery.inArray(elementName, excludeFields) === -1) && elementTitle !== '' && elementTitle !== undefined) {
                if ((value === null) || (value === undefined) || (value.trim() === '')) {
                    element.parent().addClass('has-error');
                    if (element.parent().find('.help-block').length) {
                        element.parent().find('.help-block').html(elementTitle + ' requerido');
                    } else {
                        element.parent().parent().find('.help-block').html(elementTitle + ' requerido');
                    }
                    isValidate = false;
                }
            }  else if(elementName === 'gridbox[]') {
                totalBoxes++
            }
        });
        if (totalBoxes === 0) {
            jQuery('#gv-girdviewbox').html ('Seleccione al menos una Cuadricula');
            isValidate = false;
        }
        return isValidate;
    };

    window.GridViewUtils = {
        changeStatus:   changeStatus,
        deleteGridView: deleteGridView,
        validateForm:   validateForm
    };

    var onDocumentReadyHandler = function () {
        var box    = document.querySelectorAll('#draggble-boxes .box-element'),
            grid   = document.querySelectorAll('#draggble-grid .grid-element'),
            dragSrcEl;

        function handleDragStart (e) {
            this.style.opacity = '0.4';

            dragSrcEl = jQuery ('#simple-template').html ().replace (/__BOX_NAME__/g, this.innerHTML).replace (/__VALUE__/g, this.getAttribute ('rel'));
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData ('text/html', jQuery (dragSrcEl).html());
        }

        function handleDragOver (e) {
            if (e.preventDefault) {
                e.preventDefault ();
            }
            e.dataTransfer.dropEffect = 'copy';
            return false;
        }

        function handleDragEnter (e) {
            this.classList.remove ('border-info');
            this.classList.add ('over');
        }

        function handleDragLeave (e) {
            this.classList.remove ('over');
            this.classList.add ('border-info');
        }

        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation ();
            }

            if (dragSrcEl !== this) {
                e.dataTransfer.dropEffect = 'copy';
                this.innerHTML = e.dataTransfer.getData('text/html');
            }

            [].forEach.call(grid, function (grid) {
                grid.classList.remove ('over');
                grid.classList.add ('border-info');
            });
            return false;
        }
        function handleDragEnd (e) {
            [].forEach.call(box, function (box) {
                box.removeAttribute ('style');
            });
        }

        [].forEach.call(box, function(box) {
            box.addEventListener('dragstart', handleDragStart, false);
            box.addEventListener('dragend', handleDragEnd, false);
        });
        [].forEach.call(grid, function(grid) {
            grid.addEventListener ('dragenter', handleDragEnter, false);
            grid.addEventListener ('dragover', handleDragOver, false);
            grid.addEventListener ('dragleave', handleDragLeave, false);
            grid.addEventListener ('drop', handleDrop, false);
        });
    };
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
