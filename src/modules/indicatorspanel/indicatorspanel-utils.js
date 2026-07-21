(function (jQuery) {

    //private

    //public
    var callAddEditIndicators = function (module, type, accountid, monthsearch, app, dataid, mode) {
        jQuery ('.md-overlay').css ({ opacity: 1, visibility: 'visible' });
        var view = jQuery ('#viewScale').val ();
        if (dataid != '') {
            url = 'module=' + module + '&action=indicatorspanelAjax&ajax=true&file=EditViewBox&type=' + type + '&account_id=' + accountid + '&monthsearch=' + monthsearch + '&app=' + app + '&dataid=' + dataid + '&record=' + accountid + '&mode=' + mode + '&viewScale=' + view + '&is_home=1';
        } else {
            url = 'module=' + module + '&action=indicatorspanelAjax&ajax=true&file=EditViewBox&type=' + type + '&account_id=' + accountid + '&monthsearch=' + monthsearch + '&app=' + app + '&mode=' + mode + '&viewScale=' + view + '&is_home=1';
        }
        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   url,
                onComplete: function (response) {
                    jQuery ('#addIndicators').addClass ('md-show');
                    jQuery ('#addIndicators').html (response.responseText);
                }
            }
        );
    };

    var callAddValues = function  (module, type, record, monthsearch, app) {
        jQuery ('.md-overlay').css ({ opacity: 1, visibility: 'visible' });
        var view = jQuery ('#viewScale').val ();
        var url = 'module=' + module + '&action=indicatorspanelAjax&ajax=true&file=EditViewBoxValues&type=' + type + '&boxscoreid=' + record + '&monthsearch=' + monthsearch + '&app=' + app + '&viewScale=' + view + '&is_home=1';

        new Ajax.Request (
            'index.php',
            {
                queue:      { position: 'end', scope: 'command' },
                method:     'post',
                postBody:   url,
                onComplete: function (response) {
                    jQuery ('#addValues').html (response.responseText);
                    jQuery ('#addValues').addClass ('md-show');
                }
            }
        );
    };

    var callDeleteIndicator = function (obj) {
        if (!confirm (alert_arr.MESS_DELETE_INDICATOR)) {
            return false;
        }
        var rowid = obj.id,
        arguments = {
            'module': 'indicatorspanel',
            'action': 'indicatorspanelAjax',
            'file': 'DeleteBox',
            'record': rowid,
            'delete': 'true'
        };
        jQuery.post('index.php', arguments, function (data) {
            try {
                jQuery ('#row-' + rowid).fadeOut (
                    function () {
                        jQuery ('#row-' + rowid).remove ();
                    }
                );
            }
            catch (error) {
            }
        });
    };

    var getIndicatorsMonths = function (obj) {
        var date        = new Date (),
            date_from   = '',
            date_to     = '',
            diaf        = '',
            endDay      = '',
            form        = jQuery ('#form-box-score'),
            favorites   = jQuery ('#box-score-favorites'),
            month       = [],
            monthSearch = jQuery (obj),
            viewScale   = jQuery ('#viewScale');
        if ((monthSearch.val () === '') || (viewScale.val () === '')) {
            return false;
        }
        new Date (date.getFullYear (), date.getMonth () + 1, 0);
        month[ 0 ] = '01';
        month[ 1 ] = '02';
        month[ 2 ] = '03';
        month[ 3 ] = '04';
        month[ 4 ] = '05';
        month[ 5 ] = '06';
        month[ 6 ] = '07';
        month[ 7 ] = '08';
        month[ 8 ] = '09';
        month[ 9 ] = '10';
        month[ 10 ] = '11';
        month[ 11 ] = '12';

        if (monthSearch.val () === month[ date.getMonth () ]) {
            endDay = new Date (date.getFullYear (), date.getMonth () + 1, 0);
            if (endDay.getDate () < 10) {
                diaf = '0' + endDay.getDate ();
            } else {
                diaf = endDay.getDate ();
            }
            date_from = date.getFullYear () + '-' + month[ date.getMonth () ] + '-' + '01';
            date_to = date.getFullYear () + '-' + month[ date.getMonth () ] + '-' + diaf;
        } else {
            endDay = new Date (date.getFullYear (), monthSearch.val () + 1, 0);
            if (endDay.getDate () < 10) {
                diaf = '0' + endDay.getDate ();
            } else {
                diaf = endDay.getDate ();
            }
            date_from = date.getFullYear () + '-' + monthSearch.val () + '-' + '01';
            date_to = date.getFullYear () + '-' + monthSearch.val () + '-' + diaf;
        }
        jQuery ('#date_from').val (date_from);
        jQuery ('#date_to').val (date_to);

        favorites.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"  style="width: 25%;height: 25%"/>');
        jQuery.post('index.php', form.serialize(), function (data) {
            try {
                favorites.html (data);
            }
            catch (error) {
            }
        });

    };

    var getIndicatorsView = function (obj) {
        var monthSearch = jQuery ('#monthsearch'),
            viewScale = jQuery (obj);
        if (viewScale.val () === '') {
            return false
        }
        getIndicatorsMonths (monthSearch);

    };

    var updateFavorite = function (obj, e) {
        var arguments,
            favorite     = jQuery (obj),
            boxScoreName = favorite.attr ('rel');
        arguments = {
            'module': 'indicatorspanel',
            'action': 'AjaxBoxScore',
            'function': 'updateFavorite',
            'Ajax': 'true',
            'boxscorename': favorite.attr ('rel'),
            'fldmodule': 'indicatorspanel'
        };
        favorite.parent ().addClass ('isPiDisabled');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var response = JSON.parse (data);
                favorite.attr ('title', response.title);
                favorite.html(response.faclass);
                favorite.parent ().removeClass ('isPiDisabled');
            }
            catch (error) {
                favorite.parent ().removeClass ('isPiDisabled');
            }
        });
        e.preventDefault();
    };

    var updateRailes = function (obj, e) {
        var arguments,
            railes         = jQuery (obj),
            railesStatus = railes.attr ('data-status'),
            boxScoreName = railes.attr ('rel');
        arguments = {
            'module':       'indicatorspanel',
            'action'  :     'AjaxBoxScore',
            'function':     'UPDATE_RAILES',
            'Ajax':         'true',
            'boxscorename': boxScoreName,
            'status':       railesStatus,
            'fldmodule':    'indicatorspanel'
        };
        railes.parent ().addClass ('isPiDisabled');
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    if (railesStatus === 'HIDE') {
                        railes.attr ('title', 'No mostrar en Raíles')
                        railes.attr ('data-status', 'SHOW')
                        railes.removeClass('railes_red').addClass('railes_green');
                    } else {
                        railes.attr ('title', 'Mostrar en Raíles')
                        railes.attr('data-status', 'HIDE');
                        railes.removeClass('railes_green').addClass('railes_red');
                    }
                    railes.parent ().removeClass ('isPiDisabled');
                }
            } catch (error) {
                railes.parent ().removeClass ('isPiDisabled');
            }
        });
        e.preventDefault();
    }

    window.BoxScoreUtils = {
        callAddEditIndicators: callAddEditIndicators,
        callAddValues:         callAddValues,
        callDeleteIndicator:   callDeleteIndicator,
        getIndicatorsMonths:   getIndicatorsMonths,
        getIndicatorsView:     getIndicatorsView,
        updateFavorite:        updateFavorite,
        updateRailes:          updateRailes
    };

    var onDocumentReadyHandler = function () {

    };
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));