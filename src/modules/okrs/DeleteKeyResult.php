<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/okrs/lib/OkrHelperUtils.php');
	
	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);
	
	$smarty = new vtigerCRM_Smarty ();
	
	$record      = PlatzillaUtils::purify ($_POST, 'record');
	$selectedTab = PlatzillaUtils::purify ($_POST, 'tab', 'key_results');
	
	try {
		if (empty ($record)) {
			throw new Exception ('Resultado Clave no encontrado');
		}
		
		OkrHelperUtils::getInstance ()->deleteKeyResult ($record);
		
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha eliminado el resultado clave!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ('Location: index.php?module=okrs&action=ListView&tab=key_results&parenttab=Settings');
