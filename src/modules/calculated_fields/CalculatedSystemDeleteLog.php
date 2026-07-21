<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200326
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
	// Agregado por EB para integrar BUGSNAG - 20200326

	global $adb, $current_user;

	$platform            = $_SESSION ['plat'];
	$objCalculatedFields = new CalculatedFieldsUtils ($adb, $platform);

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('No estás autorizado a realizar la operación solicitada');
		}

		$calculatedSystemId = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($calculatedSystemId)) {
			throw new Exception ('No has suministrado el ID del cálculo');
		}

		$calculatedSystemData = $objCalculatedFields->getCalculateSystemDataById ($calculatedSystemId);
		if (empty ($calculatedSystemData)) {
			throw new Exception ('No se encuentra registrado cálculo con el ID suministrado');
		}

		$calculationName = $calculatedSystemData->getName ();
		$logFielName     = 'calculo_sistema_id_'.$calculatedSystemId;
		$logFilePath     = __DIR__ . "/../../{$platform}/logs/calculatedsystem/{$logFielName}.log";
		if (file_exists ($logFilePath)) {
			unlink ($logFilePath);
		}

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => "Se ha eliminado el registro de eventos del cálculo: {$calculationName}",
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=calculated_fields&action=index&tab=system');
	exit ();
