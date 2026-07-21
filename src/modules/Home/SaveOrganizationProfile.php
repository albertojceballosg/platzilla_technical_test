<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200330
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200330

	global $adb, $platPrincipal;

	$address       = PlatzillaUtils::purify ($_POST, 'address');
	$cif           = PlatzillaUtils::purify ($_POST, 'cif');
	$city          = PlatzillaUtils::purify ($_POST, 'city');
	$country       = PlatzillaUtils::purify ($_POST, 'country');
	$currencyCode  = PlatzillaUtils::purify ($_POST, 'currencycode');
	$defaultModule = PlatzillaUtils::purify ($_POST, 'default_module');
	$logoContents  = PlatzillaUtils::purify ($_POST, 'logocontents');
	$name          = PlatzillaUtils::purify ($_POST, 'organizationname');
	$startDayWeek  = PlatzillaUtils::purify ($_POST,'start_day_week');
	$state         = PlatzillaUtils::purify ($_POST, 'state');
	$website       = PlatzillaUtils::purify ($_POST, 'website');
	$zipCode       = PlatzillaUtils::purify ($_POST, 'zipcode');

	$platform      = !empty ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : $platPrincipal;
	$imageContents = null;

	try {
		if (empty ($name)) {
			throw new Exception ('No se ha suministrado el nombre de la organización');
		} else if (empty ($cif)) {
			throw new Exception ('No se ha suministrado el identificador fiscal');
		} else if (empty ($currencyCode)) {
			throw new Exception ('No se ha suministrado la moneda');
		} else if (empty ($address)) {
			throw new Exception ('No se ha suministrado la dirección fiscal');
		} else if (!empty ($logoContents)) {
			$imageType      = substr ($logoContents, (strpos ($logoContents, 'data:') + 5), (strpos ($logoContents, ';base64,') - 5));
			$imageExtension = substr ($imageType, (strpos ($imageType, '/') + 1));
			$imageContents  = base64_decode (str_replace (' ', '+', substr ($logoContents, (strpos ($logoContents, 'base64,') + 7))));
			if (!in_array ($imageExtension, array ('jpeg', 'jpg', 'pjpeg', 'png', 'x-png'))) {
				throw new Exception ('El tipo de imagen del logo suministrado deme ser JPG o PNG');
			} else if (empty ($imageContents)) {
				throw new Exception ('El logo suministrado está vacío o hubo un error al enviar la información');
			}
		}

		if ((!empty ($imageExtension)) && (!empty ($imageContents))) {
			$logoFileName = "logo-{$platform}.{$imageExtension}";
		} else {
			$logoFileName = null;
		}

		$result = $adb->query ('SELECT * FROM vtiger_organizationdetails LIMIT 1');
		if ($adb->num_rows ($result) == 0) {
			$adb->pquery (
				'INSERT INTO vtiger_organizationdetails (organization_id, organizationname, address, city, state, country, code, website, logoname, cif, default_module, start_day_week) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array (1, $name, $address, $city, $state, $country, $zipCode, $website, $logoFileName, $cif, $defaultModule, $startDayWeek)
			);
		} else {
			$row = $adb->fetchByAssoc ($result, -1, false);
			$adb->pquery (
				'UPDATE vtiger_organizationdetails SET organizationname=?, address=?, city=?, state=?, country=?, code=?, website=?, logoname=?, cif=?, default_module=?, start_day_week=? WHERE organization_id=?',
				array ($name, $address, $city, $state, $country, $zipCode, $website, (!empty ($logoFileName) ? $logoFileName : $row ['logoname']), $cif, $defaultModule, $startDayWeek, $row ['organization_id'])
			);
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}

		$result = $adb->pquery ('SELECT * FROM vtiger_currency_info WHERE currency_code=? AND defaultid=?', array ($currencyCode, -11));
		if ($adb->num_rows ($result) == 0) {
			$adb->query ('DELETE FROM vtiger_currency_info');
			$adb->pquery (
				'INSERT INTO vtiger_currency_info (id, currency_name, currency_code, currency_symbol, conversion_rate, currency_status, defaultid, deleted)
				SELECT ?, currency_name, currency_code, currency_symbol, ?, ?, ?, ? FROM vtiger_currencies WHERE currency_code=?',
				array (1, 1.0, 'Active', -11, 0, $currencyCode)
			);
		}

		if (!empty ($logoFileName)) {
			$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			file_put_contents ("{$rootFolderPath}/{$platform}/{$logoFileName}", $imageContents);
		}

		if (!empty ($_SESSION ['platInstancia'])) {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$result    = $masterAdb->pquery ('SELECT * FROM vtiger_instances WHERE code=?', array ($_SESSION ['platInstancia']));
			if ((!$result) || ($masterAdb->num_rows ($result) == 0)) {
				throw new Exception ('La instancia no se encuentra registrada');
			}

			$row       = $masterAdb->fetchByAssoc ($result, -1, false);
			$accountId = $row ['accountid'];
			$masterAdb->pquery (
				'UPDATE vtiger_clientes SET alias=?, nombre_comercial=?, numero_fiscal=?, direccion=?, codigo_postal=?, ciudad=?, provincia=?, pais=? WHERE clientesid=?',
				array ($name, $name, $cif, $address, $zipCode, $city, $state, $country, $accountId)
			);
		}

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha actualizado el perfil de la organización',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $_POST,
		);
	}
	header ('Location: index.php?module=Home&action=ViewSubscriptionDetails&tab=organization');
	exit ();
