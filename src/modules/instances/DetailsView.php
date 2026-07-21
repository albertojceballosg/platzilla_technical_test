<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
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

	global $adb, $app_strings, $current_user, $mod_strings;

	$smarty = new vtigerCRM_Smarty();
	if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
		$smarty->assign ('IS_ADMIN', false);
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$instanceCode = PlatzillaUtils::purify ($_GET, 'code');
		$page         = PlatzillaUtils::purify ($_GET, 'page');
		if (empty ($instanceCode)) {
			throw new Exception ('No has suministrado el código de la instancia');
		}

		$instance = PlatformManager::getInstance ($adb)->fetchInstance ($instanceCode);
		if (empty ($instance)) {
			throw new Exception ("La instancia con el código {$instanceCode} no está registrada");
		}

		$result = $adb->pquery ('SELECT COUNT(*) AS total FROM vtiger_config_applications WHERE app_status=?', array ('Activa'));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			$totalApplications = 0;
		} else {
			$row               = $adb->fetchByAssoc ($result, -1, false);
			$totalApplications = intval ($row ['total']);
		}

		$accountName = null;
		$accountId   = $instance->getAccountId ();
		if (!empty ($accountId)) {
			$result = $adb->pquery ('SELECT * FROM vtiger_clientes WHERE clientesid=?', array ($accountId));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row         = $adb->fetchByAssoc ($result, -1, false);
				$accountName = $row ['nombre_comercial'];
			}
		}

		$result = $adb->pquery (
			'SELECT
				ia.*,
				ia.applicationcode AS app_code
			FROM
				vtiger_instanceapplications ia
			WHERE
				ia.instancecode=?',
			array ($instanceCode)
		);
		if (($result) && ($adb->num_rows ($result) > 0)) {
			$instanceApplications = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$instanceApplications [ $row ['app_code'] ] = $row;
			}
		} else {
			$instanceApplications = null;
		}

		$smarty->assign ('ACCOUNT_NAME', $accountName);
		$smarty->assign ('INSTANCE', $instance);
		$smarty->assign ('INSTANCE_APPLICATIONS', $instanceApplications);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('PAGE', $page);
		$smarty->assign ('TOTAL_APPLICATIONS', $totalApplications);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/instances/DetailsView.tpl');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ('Location: index.php?module=instances&action=index&parenttab=Settings');
		exit ();
	}
