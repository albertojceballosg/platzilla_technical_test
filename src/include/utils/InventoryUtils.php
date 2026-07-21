<?php
	function updateReserva ($product_id, $qty) {
		global $log;
		$log->debug ("Entering updateReserva(" . $product_id . "," . $qty);
		global $adb;
		global $current_user;

		$log->debug ("Inside updateStk function, module=" . $module);
		$log->debug ("Product Id = $product_id & Qty = $qty");

		deductFromProductReserva ($product_id, $qty);

		$log->debug ("Exiting updateStk method ...");
	}

	/**    Function used to add the history entry in the relevant tables for PO, SO, Quotes and Invoice modules
	 *
	 * @param string $module - current module name
	 * @param int $id - entity id
	 * @param string $relatedname - parent name of the entity ie, required field venor name for PO and account name for SO, Quotes and Invoice
	 * @param float $total - grand total value of the product details included tax
	 * @param string $history_fldval - history field value ie., quotestage for Quotes and status for PO, SO and Invoice
	 */
	function addInventoryHistory ($module, $id, $relatedname, $total, $history_fldval) {
		global $log, $adb;
		$log->debug ("Entering into function addInventoryHistory($module, $id, $relatedname, $total, $history_fieldvalue)");

		$history_table_array = Array (
			"PurchaseOrder" => "vtiger_postatushistory",
			"SalesOrder"    => "vtiger_sostatushistory",
			"Quotes"        => "vtiger_quotestagehistory",
		);

		$histid       = $adb->getUniqueID ($history_table_array[ $module ]);
		$modifiedtime = $adb->formatDate (date ('Y-m-d H:i:s'), true);
		$query        = "insert into $history_table_array[$module] values(?,?,?,?,?,?)";
		$qparams      = array ($histid, $id, $relatedname, $total, $history_fldval, $modifiedtime);
		$adb->pquery ($query, $qparams);

		$log->debug ("Exit from function addInventoryHistory");
	}

	/**    Function used to get the list of Tax types as a array
	 *
	 * @param string $available - available or empty where as default is all, if available then the taxes which are available now will be returned otherwise all taxes will be returned
	 * @param string $sh - sh or empty, if sh passed then the shipping and handling related taxes will be returned
	 * @param string $mode - edit or empty, if mode is edit, then it will return taxes including desabled.
	 * @param string $id - crmid or empty, getting crmid to get tax values..
	 *    return array $taxtypes - return all the tax types as a array
	 */
	function getAllTaxes ($available = 'all', $sh = '', $mode = '', $id = '') {
		return array ();
	}

	/**    function used to get the tax type for the entity (PO, SO, Quotes or Invoice)
	 *
	 * @param string $module - module name
	 * @param int $id - id of the PO or SO or Quotes or Invoice
	 *
	 * @return string $taxtype - taxtype for the given entity which will be individual or group
	 */
	function getInventoryTaxType ($module, $id) {
		global $log, $adb;

		$log->debug ("Entering into function getInventoryTaxType($module, $id).");

		$inv_table_array = Array ('PurchaseOrder' => 'vtiger_purchaseorder', 'SalesOrder' => 'vtiger_salesorder', 'Quotes' => 'vtiger_quotes', 'Invoice' => 'vtiger_invoice', 'myinvoice' => 'vtiger_myinvoice');
		$inv_id_array    = Array ('PurchaseOrder' => 'purchaseorderid', 'SalesOrder' => 'salesorderid', 'Quotes' => 'quoteid', 'Invoice' => 'invoiceid', 'myinvoice' => 'myinvoiceid');

		$res = $adb->pquery ("select taxtype from $inv_table_array[$module] where $inv_id_array[$module]=?", array ($id));

		$taxtype = $adb->query_result ($res, 0, 'taxtype');

		$log->debug ("Exit from function getInventoryTaxType($module, $id).");

		return $taxtype;
	}

	/**    function used to get the price type for the entity (PO, SO, Quotes or Invoice)
	 *
	 * @param string $module - module name
	 * @param int $id - id of the PO or SO or Quotes or Invoice
	 *
	 * @return string $pricetype - pricetype for the given entity which will be unitprice or secondprice
	 */
	function getInventoryCurrencyInfo ($module, $id) {
		global $log, $adb;

		$log->debug ("Entering into function getInventoryCurrencyInfo($module, $id).");

		$inv_table_array = Array ('PurchaseOrder' => 'vtiger_purchaseorder', 'SalesOrder' => 'vtiger_salesorder', 'Quotes' => 'vtiger_quotes', 'Invoice' => 'vtiger_invoice');
		$inv_id_array    = Array ('PurchaseOrder' => 'purchaseorderid', 'SalesOrder' => 'salesorderid', 'Quotes' => 'quoteid', 'Invoice' => 'invoiceid');

		$inventory_table = $inv_table_array[ $module ];
		$inventory_id    = $inv_id_array[ $module ];
		$res             = $adb->pquery ("select currency_id, $inventory_table.conversion_rate as conv_rate, vtiger_currency_info.* from $inventory_table
						inner join vtiger_currency_info on $inventory_table.currency_id = vtiger_currency_info.id
						where $inventory_id=?", array ($id));

		$currency_info                    = array ();
		$currency_info['currency_id']     = $adb->query_result ($res, 0, 'currency_id');
		$currency_info['conversion_rate'] = $adb->query_result ($res, 0, 'conv_rate');
		$currency_info['currency_name']   = $adb->query_result ($res, 0, 'currency_name');
		$currency_info['currency_code']   = $adb->query_result ($res, 0, 'currency_code');
		$currency_info['currency_symbol'] = $adb->query_result ($res, 0, 'currency_symbol');

		$log->debug ("Exit from function getInventoryCurrencyInfo($module, $id).");

		return $currency_info;
	}

	/**
	 *[ TT11172 ] Ajustes Exportar PDF Factura
	 *DM
	 * 12/07/2016
	 * Funci�n que dado un n�mero, le da formato seg�n la configuraci�n de usuario
	 */
	function formatNumber ($number, $format = 'N') {
		global $adb, $current_user;

		//Obteniemdo el separador decimal
		$decimalSeparator = $current_user->currency_decimal_separator;
		if ($decimalSeparator == '') {
			$decimalSeparator = '.';
		}

		if ($format == 'N') {
			$number = number_format ($number, 2, $decimalSeparator, ''); //Convert to 2 decimals

		}

		if ($format == 'C') {
			$number = CurrencyField::convertToUserFormat ($number, null, true);
		}

		return $number;
	}

	/**
	 *[ TT11172 ] Ajustes Exportar PDF Factura
	 *DM
	 * 52/07/2016
	 * Funci�n que obtiene los productos asociados a una entidad
	 */
	function getProductServEntity () {
	}

?>