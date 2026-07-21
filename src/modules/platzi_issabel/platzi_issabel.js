(function (jQuery) {

    var goToPage = function (event, buttonElement, id) {
        var button       = jQuery (buttonElement),
            form         = jQuery ('#platzi-issabel_form-' + id),
            arguments,
            argumentArr,
            page          = button.attr ('data-pagination-page'),
            period        = jQuery ('#period-dates-' + id).val (),
            paginator     = jQuery ('#pager-' + id),
            records       = jQuery ('#show-records-' + id),
            tBody         = jQuery ('#platzi-issabel-' + id),
            fronDate      = jQuery ('#start-date-' + id).val(),
            toDate        = jQuery ('#end-date-' + id).val(),
            functionName  = jQuery ('#function-name-' + id).val(),
            searchOption  = jQuery ('#search_option-' + id).val(),
            recordingType = jQuery ('#recording_type-' + id).val(),
            searchInput   = jQuery ('#search_input-' + id).val(),
            dateFrom      = new Date (fronDate),
            dateTo        = new Date (toDate);
        if (searchOption !== '') {
           if (searchOption === 'recordingfile' && recordingType === '') {
               alert ('Seleccione el tipo de grabación');
               return false;
           } else if (searchOption !== 'recordingfile' && searchInput === '') {
               alert ('Por favor insertar un valor de búsqueda');
               return false;
           }
        }
        if (period === '') {
            alert ('Uoops! seleccione un período.');
            return;
        } else if (period === 'custom') {
            if ((dateFrom.getTime() > dateTo.getTime()) || fronDate === '' || toDate === '' ) {
                alert ('Uoops! error en periodo personalizado');
                return;
            }
        }

    	form.find('input[name="page"]').val (page);
        arguments   = form.serialize ()
        argumentArr = arguments.split ('&');
        tBody.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
        arguments = form.serialize();
        jQuery.post('index.php', argumentArr.join ('&'), function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    tBody.html (message.html.rows);
                    paginator.html (message.html.paginator);
                    records.html (message.html.records);
                }
            } catch (e) {
                alert(e);
            }
        });
        event.preventDefault();

    };

    var searchBy = function (obj, id) {
        var search     = jQuery (obj).val (),
            type       = jQuery ('#platzi-issabel-type-' + id),
            typeValue  = type.find('select').eq(0),
            searchData = jQuery ('#platzi-issabel-search-' + id),
            searchValue = searchData.find('input').eq(0);
        if (search === '') {
            type.addClass ('hide');
            typeValue.val ('');
            searchData.addClass ('hide');
            searchValue.val ('');
        } else if (search === 'recordingfile') {
            type.removeClass ('hide');
            searchData.addClass ('hide');
            searchValue.val ('');
        } else {
            type.addClass ('hide');
            typeValue.val ('');
            searchData.removeClass ('hide');
        }
    }

    var selectedPeriod = function (obj, id) {
        var period = jQuery (obj).val (),
            customDate = jQuery ('.platzi-issabel-date-' + id);
        if (period === 'custom') {
            customDate.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
            customDate.parent ().parent ().removeClass ('hide');
        } else {
            customDate.parent ().parent ().addClass ('hide');
            customDate.val('');
        }
    };

    window.PlatziIssabelUtils = {
        goToPage:       goToPage,
        searchBy:       searchBy,
        selectedPeriod: selectedPeriod
    };

    jQuery(document).on('ready', function () {

    });
}(jQuery));