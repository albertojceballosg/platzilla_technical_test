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
		$emailId = PlatzillaUtils::purify ($_GET, 'record');
		if (empty ($emailId)) {
			throw new Exception ('No has suministrado el ID del correo');
		}

		$email = EmailManagerUtils::getEmailById ($adb, $emailId);
		if (empty ($email)) {
			throw new Exception ('El correo con el ID suministrado no está registrado');
		}

		if (!empty ($email ['body'])) {
			$email ['body'] = substr ($email ['body'], (strpos ($email ['body'], '<body>') + 6), (strpos ($email ['body'], '</body>') - 7));
		}

		$criteria = PlatzillaUtils::purify ($_GET, 'criteria');
		$page     = PlatzillaUtils::purify ($_GET, 'page');
		$isAjaxRequest = PlatzillaUtils::purify ($_GET, 'Ajax', false);

		$queryStringParts = array ();
		if (!empty ($criteria ['email'])) {
			$queryStringParts [] = 'criteria[email]=' . urlencode ($criteria ['email']);
		}
		if (!empty ($criteria ['date'])) {
			$queryStringParts [] = 'criteria[date]=' . urlencode ($criteria ['date']);
		}
		if (!empty ($criteria ['status'])) {
			$queryStringParts [] = 'criteria[status]=' . urlencode ($criteria ['status']);
		}
		if (!empty ($criteria ['templatename'])) {
			$queryStringParts [] = 'criteria[templatename]=' . urlencode ($criteria ['templatename']);
		}
		if (!empty ($page)) {
			$queryStringParts [] = 'page=' . urlencode ($page);
		}
		if (!empty ($queryStringParts)) {
			$queryString = '&' . join ('&', $queryStringParts);
		} else {
			$queryString = null;
		}

		$smarty->assign ('EMAIL', $email);
		$smarty->assign ('IS_AJAX_REQUEST', $isAjaxRequest);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('QUERY_STRING', $queryString);
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$smarty->display ('modules/emailmanager/EmailHistoryDetailView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'javascript:;');
		$smarty->display ('Message.tpl');
	}
