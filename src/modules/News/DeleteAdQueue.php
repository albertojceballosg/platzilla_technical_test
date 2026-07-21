<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');

	try {
		$record = PlatzillaUtils::purify ($_POST, 'record');
		if (empty ($record)) {
			throw new Exception ('No has suministrado el ID de la cola de anuncios');
		}

		AdQueueHelper::getInstance()->deleteAdQueue ($record);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'La cola de anuncios ha sido eliminada',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=News&action=ListView&tab=ad-queue-tab&parenttab=Settings');
	exit ();
