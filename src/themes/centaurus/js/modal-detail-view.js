(function (jQuery) {
    // Private variables
    var modalId ='#modal-detail-body-';

    var getRecordDetail = function (obj, event) {
        var url     = jQuery (obj).attr ('href') + '&Ajax=true',
            modal   = jQuery (modalId);

        modal.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"  style="width: 75%;height: 30%"/>');
        jQuery.get (url, function (data) {
            try {
                if ((data !== '') && data !== undefined) {
                    modal.html(data);
                } else {
                    modal.html('<h2>Información no encontrada!</h2>h2>');
                }
            }
            catch (e) {
                alert(e);
                modal.html('');
            }
        });
        event.preventDefault();
        event.stopPropagation ();
    };

    var getRecordHistory = function (obj, event) {
        var url     = jQuery (obj).attr ('href') + '&Ajax=true',
            modal   = jQuery (modalId);

        modal.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"  style="width: 75%;height: 30%"/>');
        jQuery.get (url, function (data) {
            try {
                if((data !== '') && data !== undefined) {
                    modal.html (data);
                } else {
                    modal.html ('<h2>Información no encontrada!</h2>h2>');
                }
            }
            catch (e) {
            }
        });
        event.preventDefault();
        event.stopPropagation ();
    };

     var getRelatedList = function (obj, event) {
         var url     = jQuery (obj).attr ('href') + '&Ajax=true',
             modal   = jQuery (modalId);

         modal.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"  style="width: 75%;height: 30%"/>');
         jQuery.get (url, function (data) {
             try {
                 if((data !== '') && data !== undefined) {
                     modal.html (data);
                 } else {
                     modal.html ('<h2>Información no encontrada!</h2>h2>');
                 }
             }
             catch (e) {
                 alert(e);
                 modal.html ('');
             }
         });
         event.preventDefault();
         event.stopPropagation ();
     };

window.ModalDetailViewUtils = {
    getRecordDetail:  getRecordDetail,
    getRecordHistory: getRecordHistory,
    getRelatedList:   getRelatedList
};

var onDocumentReadyHandler = function () {
    modalId += document.getElementById ("detail-over-listview").getAttribute ("data-id-modal");
};
jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));