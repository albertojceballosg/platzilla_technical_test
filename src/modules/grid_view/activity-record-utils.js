(function (jQuery) {
    
    //public method 
    var addRow = function (obj) {
        var activityTable = jQuery (obj).closest ('table'),
            clonedRow = activityTable.find ('tbody tr:first').clone ();
        clonedRow.find('button').eq(0).removeClass('hide');
        clonedRow.find ('.activity-start-date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 }).val('');
        clonedRow.find ('.activity-end-date').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 }).val('');
       activityTable.find (jQuery ('tbody')).append (clonedRow);
    };

    var deleteRow = function (obj) {
        jQuery(obj).parent().parent().remove ();
    };
    window.ActivityRecordUtils = {
        addRow:   addRow,
        deleteRow: deleteRow
    };

    var onDocumentReadyHandler = function () {
        var tBody = jQuery ('#activity-tbody');
        tBody.find ('.activity-start-date:last-child').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
        tBody.find ('.activity-end-date:last-child').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
    };


    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));