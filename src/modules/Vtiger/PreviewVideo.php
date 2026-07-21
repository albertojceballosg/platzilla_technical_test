<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');

	global $site_URL;
	setBugSnag ($site_URL);

	$urlVideo = PlatzillaUtils::purify ($_GET, 'url');

	try {
		if (empty ($urlVideo)) {
			throw new Exception ('url del video no encontrada!');
		}

		$smarty = new vtigerCRM_Smarty ();
		if (strpos ($urlVideo,'vimeo.com') !== false) {
			$smarty->assign ('VIDEO_TYPE', 'VIMEO');
		} else if (strpos ($urlVideo,'youtube.com') !== false) {
			$smarty->assign ('VIDEO_TYPE', 'YOUTUBE');
		} else {
			throw new Exception ('No es una url conocida para videos!');
		}
		$smarty->assign ('URL_VIDEO', $urlVideo);
	} catch (Exception $e) {
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('MESSAGE', $e->getMessage ());
	}
	$smarty->display ('ListViewPreviewVideo.tpl');
