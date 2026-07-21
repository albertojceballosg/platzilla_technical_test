document.write ('<script type="text/javascript" src="include/js/Inventory.js"></script>');

function validateDates (date1, date2) {
	var valuesStart;
	var valuesEnd;
	valuesStart = date1.split ("-");
	valuesEnd = date2.split ("-");

	var dateStart = new Date (valuesStart[ 2 ], (valuesStart[ 1 ] - 1), valuesStart[ 0 ]);
	var dateEnd = new Date (valuesEnd[ 2 ], (valuesEnd[ 1 ] - 1), valuesEnd[ 0 ]);
	if (dateStart == dateEnd) {
		return 0; // igual
	} else {
		if (dateStart > dateEnd) {
			// mayor
			return 1;
		} else if (dateStart < dateEnd) {
			// menor
			return -1;
		}
	}
}

function customFormValidate () {
	var unitPrice = jQuery ("#unit_price").val ();
	unitPrice = unitPrice.replace (',', '');

	if (isNaN (unitPrice)) {
		alert ('El precio unitario debe ser un valor numérico');
		return false;
	} else {
		if (unitPrice < 0) {
			alert ('El valor del precio unitario debe ser positivo');
			return false;
		}

		if (unitPrice.length > 13) {
			alert ('La longitud del campo precio unitario es muy largo');
			return false;
		}
	}

	var weight = jQuery ("#weight").val ();
	weight = weight.replace (',', '');
	if (weight.length > 0 && isNaN (weight)) {
		alert ('La Medida debe ser un valor numérico');
		return false;
	} else {
		if (weight < 0) {
			alert ('La Medida debe ser positivo');
			return false;
		}

		if (weight.length > 12) {
			alert ('La longitud del campo Medida es muy largo');
			return false;
		}
	}

	var dateInitialSale = jQuery ("#jscal_field_sales_start_date").val ();
	var v_dateInitSale = dateInitialSale.split ("-");
	dateInitialSale = v_dateInitSale[ 2 ] + '-' + v_dateInitSale[ 1 ] + '-' + v_dateInitSale[ 0 ];
	var hoy = new Date ();
	//noinspection JSUnusedAssignment
	hoy = hoy.getDate () + "-" + (hoy.getMonth () + 1) + "-" + hoy.getFullYear ();

	var finalSaleDate = jQuery ("#jscal_field_sales_end_date").val ();
	var v_finalSaleDate = finalSaleDate.split ("-");
	finalSaleDate = v_finalSaleDate[ 2 ] + '-' + v_finalSaleDate[ 1 ] + '-' + v_finalSaleDate[ 0 ];

	var validaFecha = validateDates (dateInitialSale, finalSaleDate);

	if (validaFecha == 1) {
		alert ("La Fecha Venta Inicial no puede ser mayor a " + finalSaleDate);
		return false;
	}

	var usageunit = jQuery ("#usageunit").val ();
	if (isNaN (weight)) {
		alert ('La Unidad de Uso debe ser un valor numérico');
		return false;
	} else {
		if (usageunit < 0) {
			alert ('La Unidad de Uso debe ser positivo');
			return false;
		}

		if (usageunit.length > 12) {
			alert ('La longitud del campo Unidad de Uso es muy largo');
			return false;
		}
	}

	var qtyinstock = jQuery ("#qtyinstock").val ();
	if (isNaN (weight)) {
		alert ('La Cantidad en Existencia debe ser un valor numérico');
		return false;
	} else {
		if (qtyinstock < 0) {
			alert ('La Cantidad en Existencia debe ser positivo');
			return false;
		}

		if (qtyinstock.length > 12) {
			alert ('La longitud del campo Cantidad en Existencia es muy largo');
			return false;
		}
	}

	var imagename_hidden = document.getElementsByName ('imagename_hidden')[ 0 ].value;
	if (imagename_hidden != '') {
		if (!imagename_hidden.match (/(?:gif|jpg|png|bmp)$/)) {
			alert ("El Archivo ingresado no es de tipo imagen");
			return false;
		}
	}
	return true;
}

/*Function that returns product associated values, via PopUp
 [ TT11173 ] Fallos-Ajustes 2 Facturas
 DM 15/06/2016
 */
function set_return_inventory (product_id, product_name, unitprice, qtyinstock, taxstr, curr_row, desc, subprod_id) {
	var subprod = subprod_id.split ("::");

	window.opener.document.EditView.elements[ "subproduct_ids" + curr_row ].value = subprod[ 0 ];
	window.opener.document.getElementById ("subprod_names" + curr_row).innerHTML = subprod[ 1 ];

	window.opener.document.EditView.elements[ "productName" + curr_row ].value = product_name;
	window.opener.document.EditView.elements[ "hdnProductId" + curr_row ].value = product_id;
	window.opener.document.EditView.elements[ "listPrice" + curr_row ].value = unitprice;
	window.opener.document.EditView.elements[ "comment" + curr_row ].value = desc.replace ("&quot;", "\"");
	getOpenerObj ("qtyInStock" + curr_row).innerHTML = qtyinstock;

	// Apply decimal round-off to value
	if (!isNaN (parseFloat (unitprice))) {
		unitprice = roundPriceValue (unitprice);
	}
	window.opener.document.EditView.elements[ "listPrice" + curr_row ].value = unitprice;

	var tax_array;
	var tax_details = [];
	tax_array = taxstr.split (',');
	var n = tax_array.length;
	for (var i = 0; i < n; i++) {
		tax_details = tax_array[ i ].split ('=');
	}

	window.opener.document.EditView.elements[ "qty" + curr_row ].focus ();
}

// Function to Round off the Price Value
// 	[ TT11173 ] Fallos-Ajustes 2 Facturas
//  DM 15/06/2016
function roundPriceValue (val) {
	val = parseFloat (val);
	val = (Math.round (val * 100) / 100);
	val = val.toString ();

	if (val.indexOf (".") < 0) {
		val += ".00"
	} else {
		var dec = val.substring (val.indexOf (".") + 1, val.length);
		if (dec.length > 2) {
			val = val.substring (0, val.indexOf (".")) + "." + dec.substring (0, 2);
		} else if (dec.length == 1) {
			val = val + "0"
		}
	}

	return val;
}
