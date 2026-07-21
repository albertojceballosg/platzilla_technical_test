<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$gridViewName  = PlatzillaUtils::purify ($_POST, 'gridviewname');
	$moduleName    = PlatzillaUtils::purify ($_POST, 'tabname');

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('No autorizado para realizar esta operación!');
		}
		if (empty ($moduleName)) {
			throw new Exception ('No se encontró el modulo para la vista!');
		}

		if (empty ($gridViewName)) {
			throw new Exception ('Vista cuadricula no identificada');
		}

		GridViewHelper::deleteGridView ($adb, $gridViewName);

		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK'));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit();
