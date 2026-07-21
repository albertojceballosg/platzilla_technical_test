<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/ModuleManagerHelper.class.php');

	global $adb, $current_user;

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');
		ModuleManagerHelper::deleteModule ($adb, $moduleName);
		create_tab_data_file ();
		create_parenttab_data_file ();
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El módulo ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Settings&action=ModuleManager&parenttab=Settings');
	exit ();
