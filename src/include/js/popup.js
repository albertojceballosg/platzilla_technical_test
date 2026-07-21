function newpopup (str) {
	window.open (str, "mywinw", "menubar=1,resizable=1,scrollbars=yes");
}

function serializeNonEmptyFormData (formName) {
	var fields = jQuery ('form[name=' + formName + ']').find ('.for-filter'),
		n = fields.length,
		i, field, name, value, serialized = [];
	if (n === 0) {
		return '';
	}
	for (i = 0; i < n; i += 1) {
		field = jQuery (fields [ i ]);
		name = field.attr ('name');
		value = field.val ();
		if ((!name) || (value === undefined) || (value === null) || (value.trim () === '')) {
			continue;
		}
		serialized.push (encodeURIComponent (name) + '=' + encodeURIComponent (value));
	}
	return serialized.length > 0 ? '&' + serialized.join ('&') : '';
}