<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
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

	global $adb, $app_strings, $current_user, $mod_strings, $theme;

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	try {
		$templateId = PlatzillaUtils::purify ($_GET, 'record');
		if (empty ($templateId)) {
			throw new Exception ('No has suministrado el ID de la plantilla');
		}

		$template = EmailManagerUtils::getTemplateById ($adb, $templateId);
		if (empty ($template)) {
			throw new Exception ('No se encuentra registrada la plantilla con el ID suministrado');
		}
		$template ['templatename'] = null;

		$smarty->assign ('AVAILABLE_LANGUAGES', EmailManagerUtils::getAvailableLanguages ());
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('TEMPLATE', $template);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/emailmanager/TemplateEditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=emailmanager&action=TemplateListView&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}
