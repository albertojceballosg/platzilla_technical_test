<?php
	require ('config.inc.php');
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');


	global $application_unique_key, $dbconfig, $platPrincipal, $app_strings, $current_language, $mod_strings, $theme;

	$processCode    = PlatzillaUtils::purify ($_GET, 'id');
	$processCode    = urldecode ($processCode);
	$processCode    = base64_decode ($processCode);
	$processActions = explode (';', $processCode);

	$themePath = "themes/$theme";
	try {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('CMOD', $mod_strings);
		$smarty->assign ('IMAGE_PATH', "$themePath/images/");
		$smarty->assign ('PROMO_TEXT', 'Boletín informativo, artículos, recursos, cursos y mas..');
		$smarty->assign ('PROMO_VIDEO', 'https://player.vimeo.com/video/438095242');
		$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('THEME_PATH', "{$themePath}/");
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		if ($processActions [0] == 'ebook') {
			$smarty->assign ('EBOOK', (!empty($processActions[1])) ? FolderUtils::getInstance ($platPrincipal)->fetchDocumentById ($processActions[1]) : null);
			$smarty->assign ('PASS_WORD', base64_encode (StoreUtils::randomPassword (6, true)));
			$smarty->display ('modules/store/EbookOnBoard.tpl');
		} else {
			$smarty->assign ('AD_QUEUES', AdQueueHelper::getInstance ()->fetchAdQueus (true));
			$smarty->display ('modules/store/BulletinBoard.tpl');
		}
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		if ($processActions [0] == 'ebook') {
			$smarty->assign('EBOOK', null);
			$smarty->display ('modules/store/EbookOnBoard.tpl');
		} else {
			$smarty->assign('AD_QUEUES',null);
			$smarty->display ('modules/store/BulletinBoard.tpl');
		}
	}
