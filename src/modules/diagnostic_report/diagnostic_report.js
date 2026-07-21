(function (jQuery) {

    var activeTabActionPlan = function (event, moduleName, idTab, recordId) {
        var destinationId = jQuery ('#destination-id-' + idTab). val(),
            arguments     = {
                'module':        moduleName,
                'action':        'AjaxDetailViewUtils',
                'flmodule':      moduleName,
                'function':      'VIEW-ACTION-PLAN-TAB',
                'tabid':         idTab,
                'record':        recordId,
                'idDestination': destinationId,
                'Ajax':          true
            },
            content = jQuery ('#tab-diagnostic-plan-view-' + idTab),
            mainTab = jQuery ('#detal-view-group-tab-' + idTab);
        if ((content.contents ().length <= 3) && moduleName !== '') {
            jQuery.post ('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html (message.html);
                    }
                }
                catch (e) {
                    if (e === undefined) {
                        alert('¡Uoooops! Esto es un poco embarazoso, pero ha ocurrido un pequeño error');
                        mainTab.find ('#detail-view-btn-tab').trigger('click');
                    } else {
                        if (destinationId === '' || destinationId === undefined || destinationId === 'undefined') {
                            alert ('¡Uooops! No ha seleccionado un destino');
                            mainTab.find ('#diagnostic-destination-btn-tab').trigger ('click');
                        } else {
                            alert(e);
                            mainTab.find ('#detail-view-btn-tab').trigger('click');
                        }
                    }
                }
            });
        }
    };

    var activeTabDestination = function (event, moduleName, idTab, recordId) {
        var arguments = {
                'module':   moduleName,
                'action':   'AjaxDetailViewUtils',
                'flmodule':  moduleName,
                'function': 'VIEW-DESTINATION-TAB',
                'tabid':    idTab,
                'record':   recordId,
                'Ajax':     true
            },
            content = jQuery ('#tab-diagnostic-destination-view-' + idTab),
            mainTab = jQuery ('#detal-view-group-tab-' + idTab);
        if ((content.contents ().length <= 3) && moduleName !== '') {
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        content.html (message.html);
                    }
                }
                catch (e) {
                    if (e === undefined) {
                        alert('¡Uoooops! Esto es un poco embarazoso, pero ha ocurrido un pequeño error');
                        mainTab.find ('#detail-view-btn-tab').trigger('click');
                    } else {
                        alert(e);
                        mainTab.find ('#detail-view-btn-tab').trigger('click');
                    }
                }
            });
        }
    };

    var activeTabEvolution = function () {

    };

    var getDestination = function (obj, idTab) {
        var record = jQuery (obj).attr ('data-id'),
            dummy  = window.location.href.split('?'),
            url    = '?module=business_destination&parenttab=&action=DetailView&record=';
        url = dummy [0] + url + record;
        window.open (url, '_blank');
    };

    var selectDestination = function (event, obj, idDestination, idTab) {
        var destination  = jQuery (obj),
            selectedCard = jQuery ('#selected-destination-' + idTab),
            btnView      = jQuery ('#btn-view-' + idTab),
            textCard     = jQuery ('#destination-text-' + idTab),
            btnSet       = jQuery ('#btn-set-' + idTab),
            btnModal     = jQuery ('#btn-modal-' + idTab),
            btnOpenModal = jQuery ('#go-modal-' + idTab);

        textCard.html (destination.html());
        btnView.attr('data-id', idDestination);
        btnSet.attr ('data-id', idDestination);
        selectedCard.removeClass ('hide');
        btnOpenModal.html ('&nbsp;Cambiar destino&nbsp;');
        btnOpenModal.removeClass ('btn-success');
        btnOpenModal.addClass ('btn-primary');
        btnModal.trigger ('click');
        event.preventDefault ();
    };

    var setDestination = function (obj, idTab, idRow) {
        var btnDestination = jQuery ('#go-modal-' + idRow),
            content        = jQuery ('#tab-diagnostic-plan-view-' + idTab),
            checkBox       = jQuery (obj),
            destinationObj = jQuery ('#destination-id-' + idTab),
            destinationId  = checkBox.attr('data-id'),
            isChecked      = checkBox[0].checked,
            mainTab        = jQuery ('#detal-view-group-tab-' + idTab),
            emptyTab       = jQuery ('#empty-template-' + idTab).html();
        console.log(isChecked)
        if (isChecked) {
            destinationObj.val (destinationId)
            btnDestination.prop('disabled', true);
            mainTab.find ('#diagnostic-plan-btn-tab').trigger ('click');
        } else {
            destinationObj.val ('')
            content.empty ();
            content.html (emptyTab)
            btnDestination.prop('disabled', false);
        }
    };

    window.DiagnosticRerportUtils = {
        activeTabActionPlan:	activeTabActionPlan,
        activeTabDestination:	activeTabDestination,
        activeTabEvolution:		activeTabEvolution,
        getDestination:         getDestination,
        selectDestination:      selectDestination,
        setDestination:         setDestination
    };

    var onDocumentReadyHandler = function () {
        var url            = window.location.href,
            destinationTab = jQuery ('#diagnostic-destination-btn-tab'),
            dummy          = url.split ('&');
        if (dummy[4] === 'tab=destination') {
            destinationTab.trigger('click');
        }
    };

    jQuery(document).ready(onDocumentReadyHandler);

}(jQuery));
