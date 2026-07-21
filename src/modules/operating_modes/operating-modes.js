(function (jQuery) {

    var changeOperating = function (obj, event) {
        var buttonClassOptions = ['btn-success', 'btn-warning', 'btn-primary'],
            selectedElement    = jQuery (obj),
            operatingAdnModule = selectedElement.attr ('rel').split ('@'),
            mainButton         = selectedElement.parent ().parent ().prev (),
            arguments          = {
                'module':       'operating_modes',
                'action':       'ChangeOperatingMode',
                'selectedmode': operatingAdnModule[ 0 ],
                'flmodule':     operatingAdnModule[ 1 ],
                'Ajax':         'true'
            };
        mainButton.find('span').eq (0).removeClass('hide');
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    if (operatingAdnModule[ 1 ] === 'Home') {
                        //alert ('El modo de operación se ha actualizado con éxito!. \n Se actualizará esta página. Por favor espere');
                        var dummy = location.href.split ('?');
                        window.location = location.href = dummy[0] + '?module=Home&action=index';
                    } else {
                        //alert ('El modo de operación se ha actualizado con éxito!. \n Se recargará la vista home. Por favor espere');
                        if (location.href.indexOf('?') === -1) {
                            window.location = location.href += '?index.php?module=Home&action=index';
                        } else {
                            var dummy = location.href.split ('?');
                            window.location = location.href = dummy[0] + '?module=Home&action=index';
                        }
                    }
                }
            }
            catch (e) {
                alert(e);
            }
        });
        event.preventDefault ();
    };

    window.OperatingModesUtils = {
        changeOperating: changeOperating
    };

    var onDocumentReadyHandler = function () {

    };
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));