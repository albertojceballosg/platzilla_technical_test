<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb, $current_user;

	$id = PlatzillaUtils::purify ($_POST, 'record');

	try {
		if ((!empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		} else if (empty ($id)) {
			throw new Exception ('No has suministrado el ID');
		}

		HelpSettingsHelper::deleteHelpTutorial ($adb, $id);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El tutorial ha sido eliminado',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=tutorials');
	exit ();