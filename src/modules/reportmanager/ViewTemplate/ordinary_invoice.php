<?php
	// Aumentar límite de memoria y tiempo de ejecución
	@ini_set('memory_limit', '1024M');
	@ini_set('max_execution_time', 300);

	require_once ('include/platzilla/Managers/GridManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DetailViewUtils.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('Smarty_setup.php');

	global $adb, $log, $theme, $mod_strings;

	$record = SettingsUtils::purify ($_REQUEST, 'record');

	if (empty($record)) {
		$record = 0;
		$module = 'facturas';
		$view   = $idview;
	}

	$objectDate = new DateTime();
	$today      = $objectDate->format ('Y-m-d');
	$focus      = CRMEntity::getInstance ($module);
	$focus->id  = $record;

	$focus->retrieve_entity_info ($record, $module);
	$invoice_no = getModuleSequenceNumber ($module, vtlib_purify ($record));

	$sql   = 'SELECT * FROM vtiger_organizationdetails';
	$query = $adb->pquery ($sql, 0);
	$owner = $adb->fetchByAssoc ($query, -1, false);

	$owner_address = "{$owner ['address']} {$owner ['code']}, {$owner ['city']}-{$owner ['country']}";

	$query_customer = $adb->pquery (
		'SELECT c.*, f.*, f.observaciones AS fact_observaciones FROM vtiger_facturas f
		INNER JOIN vtiger_clientes c ON f.cliente = c.clientesid
		INNER JOIN vtiger_crmentity crm ON crm.crmid = f.facturasid
		WHERE f.facturasid =?',
		array ($record)
	);

	$clients = $adb->fetchByAssoc ($query_customer, -1, false);

	if (!empty($clients ['direccion'])) {
		$address = $clients ['direccion'];
	}
	if (!empty($clients ['ciudad'])) {
		$address .= ', ' . $clients ['ciudad'];
	}
	if (!empty($clients ['ciudad'])) {
		$address .= '-' . $clients ['pais'];
	}
	if (empty($address)) {
		$address = 'Direcci&#243;n no registrada';
	}

	$invoiceTable        = array ();
	$totalAmount         = 0;
	$subTotalAmount      = 0;
	$totalTax            = 0;
	$invoiceFieldObjects = FieldManager::getInstance ($adb)->fetchFields ($module);
	foreach ($invoiceFieldObjects as $field) {
		if (!empty($field->getGrid ())) {
			$invoiceTable = GridFieldUtils::getGridValues ($adb, $module, $field->getName (), $record);
			break;
		}
	}
	if (!empty($invoiceTable)) {
		$totalItems = count ($invoiceTable);
		for ($k = 0; $k < $totalItems; $k++) {
			$invoiceTable [ $k ]['valor_impuesto'] = ($invoiceTable [ $k ]['total'] - $invoiceTable [ $k ]['subtotal']);

			$totalAmount += $invoiceTable [ $k ]['total'];
			$subTotalAmount += $invoiceTable [ $k ]['subtotal'];
			$totalTax += $invoiceTable [ $k ]['valor_impuesto'];
		}
	}

	$dtCreated = isset($clients['createdtime']) ? explode (' ', $clients['createdtime']) : array('', '');
	$dummy     = explode ('_', $adb->dbName);
	$code      = array_pop ($dummy);

	$urlLogo = "./{$code}/{$owner['logoname']}";
	$proportionality = -1;
	if (!file_exists ($urlLogo)) {
		$urlLogo = null;
	} else {
		$infoLogo = getimagesize ($urlLogo);
		if (($infoLogo[0] > 350) || ($infoLogo[1] > 250)) {
			$proportionality = ($infoLogo[0] > $infoLogo[1]) ? floor ((42000 / $infoLogo[0])) : floor ((18000 / $infoLogo[1]));
		} else {
			$proportionality = -1;
		}
	}
	//get organization currency
	$orgCurrency = HomeUtils::getOrganizationCurrency ($adb);

	$smarty = new vtigerCRM_Smarty;
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('MODSTRING', $mod_strings);
	$smarty->assign ('IMAGE_PATH', $urlLogo);
	$smarty->assign ('IMAGE_PROP', $proportionality);
	$smarty->assign ('NAME_ORGANIZATION', sanitizeString ($owner['organizationname']));
	$smarty->assign ('ADDRESS_ORGANIZATION', $owner_address);
	$smarty->assign ('ORGANIZATION_CFICAL', $owner['cif']);
	$smarty->assign ('NUM_INVOICE', $clients['cod_facturas']);
	$smarty->assign ('TODAY', $today);
	$smarty->assign ('NUM_ORDER', $clients['orden_de_venta']);
	$smarty->assign ('DT_EXPIRATION', $clients['fecha_de_vencimiento']);
	$smarty->assign ('DT_CREATED', $dtCreated[0]);
	$smarty->assign ('DT_ISSUE', $clients['fecha_de_emision']);
	$smarty->assign ('CUSTOMER_NAME', sanitizeString ($clients['nombre_comercial']));
	$smarty->assign ('CUSTOMER_ADDRESS', $address);
	$smarty->assign ('CUSTOMER_ORDER', $invoiceTable);
	$smarty->assign ('CURRENCY', (!empty($orgCurrency)) ? $orgCurrency['currency_symbol'] : null);
	$smarty->assign ('TOTAL_AMOUNT', $totalAmount);
	$smarty->assign ('SUBTOTAL_AMOUNT', $subTotalAmount);
	$smarty->assign ('TOTAL_TAX', $totalTax);
	$smarty->assign ('PAYMENT_CONDITIONS', $clients['condiciones_de_p']);
	$smarty->assign ('OBSERVATIONS', $clients['fact_observaciones']);
	$smarty->assign ('FISCAL_CODE', $clients['numero_fiscal']);

	try {
		$html = $smarty->fetch('modules/reportmanager/' . $view . '.tpl');
	} catch (Exception $e) {
		error_log("[ordinary_invoice.php] ERROR: No se puede cargar la plantilla Smarty: " . $e->getMessage());
		echo "<html><head><title>Error</title>";
		echo "<style>";
		echo "body { margin: 0; padding: 0; font-family: Arial, sans-serif; background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; height: 100vh; }";
		echo ".modal-error { background: #fff; border-radius: 6px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); padding: 30px 40px; max-width: 500px; text-align: center; }";
		echo ".modal-error h1 { color: #d9534f; font-size: 24px; margin: 0 0 20px 0; }";
		echo ".modal-error p { color: #333; font-size: 14px; line-height: 1.5; margin: 0 0 10px 0; }";
		echo ".btn-platzilla { background: #337ab7; color: #fff; border: none; border-radius: 4px; padding: 10px 24px; font-size: 14px; cursor: pointer; margin-top: 20px; }";
		echo ".btn-platzilla:hover { background: #286090; }";
		echo "</style></head><body>";
		echo "<div class='modal-error'>";
		echo "<h1>Error al generar PDF</h1>";
		echo "<p><strong>No se puede construir el PDF debido a que la plantilla personalizada '<em>" . htmlspecialchars($view) . ".tpl</em>' no existe.</strong></p>";
		echo "<p>Por favor, contacte al administrador del sistema para crear la plantilla necesaria.</p>";
		echo "<button class='btn-platzilla' onclick='history.back()'>Regresar</button>";
		echo "</div></body></html>";
		exit;
	}

	$donwLoadField = "{$view}_{$clients['cod_facturas']}.pdf";
