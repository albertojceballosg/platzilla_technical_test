<?php
	require_once ('modules/Vtiger/EditView.php');
	global $currentModule;
	global $smarty;
	$productid      = '';
	$retrieve_taxes = false;

	$record      = $_REQUEST['record'];
	$isduplicate = vtlib_purify ($_REQUEST['isDuplicate']);

	$focus = CRMEntity::getInstance ($currentModule);

	if ($record) {
		$focus->id   = $record;
		$focus->mode = 'edit';
	}
	if ($isduplicate == 'true') {
		$focus->id   = '';
		$focus->mode = '';
	}
	if ($focus->mode == 'edit') {
		$retrieve_taxes        = true;
		$productid             = $focus->id;
		$tax_details           = getTaxDetailsForProduct ($productid, 'available_associated');
		$service_base_currency = getProductBaseCurrency ($record, $currentModule);
	} else if ($_REQUEST['isDuplicate'] == 'true') {
		$retrieve_taxes        = true;
		$productid             = $_REQUEST['record'];
		$tax_details           = getTaxDetailsForProduct ($productid, 'available_associated');
		$service_base_currency = getProductBaseCurrency ($record, $currentModule);
	} else {
		$tax_details           = getAllTaxes ('available');
		$service_base_currency = fetchCurrency ($current_user->id);
	}

	$n = count ($tax_details);
	for ($i = 0; $i < $n; $i++) {
		$tax_details[ $i ]['check_name']  = $tax_details[ $i ]['taxname'] . '_check';
		$tax_details[ $i ]['check_value'] = 0;
	}

	//For Edit and Duplicate we have to retrieve the service associated taxes and show them
	if ($retrieve_taxes) {
		$n = count ($tax_details);
		for ($i = 0; $i < $n; $i++) {
			$tax_value                        = getProductTaxPercentage ($tax_details[ $i ]['taxname'], $productid);
			$tax_details[ $i ]['percentage']  = $tax_value;
			$tax_details[ $i ]['check_value'] = 1;
			//if the tax is not associated with the service then we should get the default value and unchecked
			if ($tax_value == '') {
				$tax_details[ $i ]['check_value'] = 0;
				$tax_details[ $i ]['percentage']  = getTaxPercentage ($tax_details[ $i ]['taxname']);
			}
		}
	}

	$smarty->assign ('TAX_DETAILS', $tax_details);
	//Tax handling - ends

	$unit_price    = $focus->column_fields['unit_price'];
	$price_details = getPriceDetailsForProduct ($productid, $unit_price, 'available', $currentModule);
	$smarty->assign ('PRICE_DETAILS', $price_details);

	$base_currency = 'curname' . $service_base_currency;
	$smarty->assign ('BASE_CURRENCY', $base_currency);

	if (isset($_REQUEST['action2']) && vtlib_purify ($_REQUEST['action2']) == vtlib_purify ('Popup')) {
		$smarty->assign ('POPUPCREATE', vtlib_purify ('create'));
	}

	if ($focus->mode == 'edit') {
		$smarty->display ('salesEditView.tpl');
	} else {
		$smarty->display ('CreateView.tpl');
	}
