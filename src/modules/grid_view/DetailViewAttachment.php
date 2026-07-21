<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/AttachmentsUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$moduleName = PlatzillaUtils::purify ($_GET, 'formodule');
	$record     = PlatzillaUtils::purify ($_GET, 'record');

	try {
		if (empty ($moduleName)) {
			throw new Exception ('Módulo no recibido!');
		}

		if (empty ($record)) {
			throw new Exception ('Numero de registro no recibido');
		}

		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ENTITY_ATTACHMENTS', AttachmentsUtils::fetchEntityAttachments ($adb, $record));
		$smarty->assign ('MODULE', $moduleName);
		$smarty->assign ('RECORD', $record);
		$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
	} catch (Exception $e) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('IS_ERROR', true);
	}
	$smarty->display ('modules/grid_view/BoxContenets/DetailViewAttachment.tpl');
