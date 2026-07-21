<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule, $current_user;

	$instanceCode = PlatzillaUtils::purify ($_POST, 'instancecode');
	$totalUsers   = PlatzillaUtils::purify ($_POST, 'totalusers');

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		} else if (empty ($instanceCode)) {
			throw new Exception ('No has suministrado el código de la instancia');
		} else if ((!is_numeric ($totalUsers)) || ($totalUsers <= 0)) {
			throw new Exception ('El total de usuarios suministrado no es válido');
		}

		$adb->pquery ('UPDATE vtiger_instances SET totalusers=? WHERE code=?', array ($totalUsers, $instanceCode));

		$isError = false;
		$message = 'Se ha actualizado la instancia';
	} catch (Exception $e) {
		$isError = true;
		$message = $e->getMessage ();
	}
	$_SESSION ['flashmessage'] = array (
		'iserror' => $isError,
		'message' => $message,
	);
	header ("Location: index.php?module={$currentModule}&action=index");
	exit ();
