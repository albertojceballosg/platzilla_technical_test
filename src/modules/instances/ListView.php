<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/Pagination.php');
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

	$fieldName   = PlatzillaUtils::purify ($_GET, 'fieldname');
	$keyword     = PlatzillaUtils::purify ($_GET, 'keyword');
	$page        = PlatzillaUtils::purify ($_GET, 'page');
	$rowsPerPage = 25;

	$result = $adb->pquery ('SELECT COUNT(*) AS total FROM vtiger_config_applications WHERE app_status=?', array ('Activa'));
	if ($adb->num_rows ($result) == 0) {
		$totalApplications = 0;
	} else {
		$row               = $adb->fetchByAssoc ($result, -1, false);
		$totalApplications = intval ($row ['total']);
	}
	if ($result instanceof ADORecordSet) {
		$result->Close ();
		$result = null;
	}
	$dataInstances   = PlatformManager::getInstance ($adb)->fetchInstances ($keyword, $fieldName, $page, $rowsPerPage, true, true);
	$paginator       = Pagination::getInstance ();
	$paginatorConfig = array (
		'totalRows'       => $dataInstances ['totalRecords'],
		'perPage'         => $rowsPerPage,
		'baseUrl'         => 'index.php?module=instances&action=ListView&parenttab=Settings',
		'numLinks'        => 5,
		'attributes'      => array('class' => 'linkPag'),
		'firstTagOpen'    => "<li class='Pages'>",
		'firstTagClose'   => '</li>',
		'lastTagOpen'     => "<li class='Pages'>",
		'lastTagClose'    => '</li>',
		'currentTagOpen'  => "<li class='Pages'><a href='#'><strong>",
		'currentTagClose' => '</strong></a></li>',
		'numTagOpen'      => "<li class='Pages'>",
		'numTagClose'     => '</li>',
		'prevTagOpen'     => "<li class='Pages'>",
		'prevTagClose'    => '</li>',
		'nextTagOpen'     => "<li class='Pages'>",
		'nextTagClose'    => '</li>',
	);
	$paginator->initialize ($paginatorConfig);

	$smarty->assign ('DATA', $dataInstances);
	$smarty->assign ('FIELD_NAME', $fieldName);
	$smarty->assign ('KEYWORD', $keyword);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('PAGE', $page);
	$smarty->assign ('PAGINATOR', $paginator->createLinks ());
	$smarty->assign ('TOTAL_APPLICATIONS', $totalApplications);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/instances/ListView.tpl');