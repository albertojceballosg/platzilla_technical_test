<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$blockId    = PlatzillaUtils::purify ($_POST, 'blockId');
	$visibility = PlatzillaUtils::purify ($_POST, 'visibility');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($blockId)) {
			throw new Exception ('No has suministrado el nombre del bloque');
		} else if ($visibility === null) {
			throw new Exception ('No has suministrado el estado del bloque');
		}

		$visibility = ($visibility) ? 0 : 1;

		$bm    = BlockManager::getInstance ($adb);
		$block = $bm->fetchBlock (intval ($blockId), true);
		if (empty ($block)) {
			throw new Exception ('El bloque suministrado no está registrado '.$blockId);
		} else {
			$block->setVisibility ($visibility);
			$block->setLocked ((!empty ($_SESSION ['platInstancia'])));
			$bm->updateBlockHeader ($block);
		}

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode (array ('error' => 'OK'));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit ();
