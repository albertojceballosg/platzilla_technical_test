<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200316
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
	// Agregado por EB para integrar BUGSNAG - 20200316

	global $adb;

	$templateData = array (
		'templateid'       => PlatzillaUtils::purify ($_POST, 'record'),
		'templatename'     => PlatzillaUtils::purify ($_POST, 'templatename'),
		'language'         => PlatzillaUtils::purify ($_POST, 'language'),
		'subject'          => vtlib_purify ($_POST ['subject'], true),
		'body'             => vtlib_purify ($_POST ['body'], true),
		'adddefaultheader' => isset ($_POST ['adddefaultheader']) ? true : false,
		'adddefaultfooter' => isset ($_POST ['adddefaultfooter']) ? true : false,
		'attachments'      => PlatzillaUtils::purify ($_POST, 'attachments'),
	);

	try {
		EmailManagerUtils::saveTemplate ($adb, $templateData, $_SESSION ['plat']);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La plantilla ha sido guardada',
		);
		header ('Location: index.php?module=emailmanager&action=TemplateListView&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $templateData,
		);
		$recordUriPart             = !empty ($templateData ['templateid']) ? "&record={$templateData ['templateid']}" : '';
		header ("Location: index.php?module=emailmanager&action=TemplateEditView&parenttab=Settings{$recordUriPart}");
	}
