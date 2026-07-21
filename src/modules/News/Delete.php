<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');

	global $adb, $current_user;

	try {
		$record = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($record)) {
			throw new Exception ('No has suministrado el ID del anuncio');
		}

		AdQueueHelper::getInstance()->deleteNewsData ($record);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El anuncio ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=News&action=ListView&parenttab=Settings');
	exit ();
