<?php
	require_once ('include/platzilla/Managers/GridManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DetailViewUtils.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('Smarty_setup.php');

	global $adb, $log, $theme, $mod_strings;
	ini_set ('memory_limit', '512M');

	$record = SettingsUtils::purify ($_REQUEST, 'record');

	if (empty($record)) {
		$record = 0;
		$module = 'presupuestos_cotizacion';
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
		'SELECT c.*, p.* FROM vtiger_presupuestos_cotizacion p
		INNER JOIN vtiger_clientes c ON p.cliente = c.clientesid
		INNER JOIN vtiger_crmentity crm ON crm.crmid = p.presupuestos_cotizacionid
		WHERE p.presupuestos_cotizacionid =?',
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

	$dtCreated = explode (' ', $clients['createdtime']);
	$dummy     = explode ('_', $adb->dbName);
	$code      = array_pop ($dummy);

	$urlLogo = "./{$code}/{$owner['logoname']}";
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

	$smarty = new vtigerCRM_Smarty;
	$smarty->assign ('ADDRESS_ORGANIZATION', $owner_address);
	$smarty->assign ('CUSTOMER_ADDRESS', $address);
	$smarty->assign ('CUSTOMER_NAME', sanitizeString ($clients['nombre_comercial']));
	$smarty->assign ('CUSTOMER_ORDER', $invoiceTable);
	$smarty->assign ('DT_CREATED', $dtCreated[0]);
	$smarty->assign ('DT_EXPIRATION', $clients['fecha_de_validez']);
	$smarty->assign ('DT_ISSUE', $clients['fecha_emision']);
	$smarty->assign ('FISCAL_CODE', $clients['numero_fiscal']);
	$smarty->assign ('IMAGE_PATH', $urlLogo);
	$smarty->assign ('IMAGE_PROP', $proportionality);
	$smarty->assign ('MODSTRING', $mod_strings);
	$smarty->assign ('NAME_ORGANIZATION', sanitizeString ($owner['organizationname']));
	$smarty->assign ('NUM_CODE', $clients['cod_presupuestos_cotizacion']);
	$smarty->assign ('OBSERVATIONS', $clients['observaciones']);
	$smarty->assign ('ORGANIZATION_CFICAL', $owner['cif']);
	$smarty->assign ('PAYMENT_CONDITIONS', $clients['condiciones_de_pago']);
	$smarty->assign ('SUBTOTAL_AMOUNT', $subTotalAmount);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('TODAY', $today);
	$smarty->assign ('TOTAL_AMOUNT', $totalAmount);
	$smarty->assign ('TOTAL_TAX', $totalTax);
	$html          = $smarty->fetch ('modules/reportmanager/' . $view . '.tpl');
	$donwLoadField = "{$view}_{$clients['presupuestos_cotizacionid']}.pdf";
