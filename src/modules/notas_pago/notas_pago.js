(function (jQuery) {
	var addAttachment = function () {
		var attachmentsBody = jQuery ('table.attachments').find ('tbody'),
			template        = jQuery ('#attachment-template').html ();
		attachmentsBody.append (jQuery (template));
	};

	var deleteRow = function (button) {
		var row = jQuery (button).closest ('tr');
		if (!confirm ('¿Estás seguro de borrar el registro seleccionado?')) {
			return;
		}
		row.remove ();
	};

	window.PaymentNotesUtils = {
		addAttachment:       addAttachment,
		deleteRow:           deleteRow
	};
} (jQuery));

