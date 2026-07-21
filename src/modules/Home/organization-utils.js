(function (jQuery) {
	var changeImage = function (evt) {
		var target = evt.target ? evt.target : evt.srcElement,
			files  = target.files,
			reader = new FileReader (),
			file;

		if ((!files) || (!(files instanceof FileList)) || (files.length === 0)) {
			return;
		}

		file = files [ 0 ];
		if (!file.type.match (/image.*/)) {
			alert ('El archivo ' + file.name + ' no es una imagen');
			return;
		}

		reader.onload = function (evt) {
			var imageContainer = jQuery ('.image-container');
			imageContainer.find ('.image-data').attr ('src', evt.target.result);
			imageContainer.find ('.image-name').html (file.name);
			imageContainer.find ('input[name="logocontents"]').val (evt.target.result);
		};
		reader.readAsDataURL (file);
	};

	var restoreImage = function (buttonElement) {
		var button = jQuery (buttonElement),
			image, name;
		if (!confirm ('Esto restaurará la última imagen almacenada. ¿Estás seguro?')) {
			return;
		}
		image = button.closest ('.image-container').find ('.image-data');
		image.attr ('src', image.attr ('data-original-src'));
		name = button.closest ('.image-container').find ('.image-name');
		name.html (name.attr ('data-original-name'));
		button.closest ('.image-container').find ('input[name="logocontents"]').val ('');
		button.closest ('.image-container').find ('input[type="file"]').val ('');
	};

	var validateOrganizationProfile = function () {
		var form = jQuery ('form[name="organizationprofile"]'),
			field, value;

		field = form.find ('#organization-name');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('No has suministrado el nombre de la organización');
			field.focus ();
			return false;
		}

		field = form.find ('#cif');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('No has suministrado el identificador fiscal');
			field.focus ();
			return false;
		}

		field = form.find ('#address');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('No has suministrado la dirección fiscal');
			field.focus ();
			return false;
		}

		return true;
	};

	window.OrganizationUtils = {
		changeImage:                 changeImage,
		restoreImage:                restoreImage,
		validateOrganizationProfile: validateOrganizationProfile
	}
} (jQuery));
