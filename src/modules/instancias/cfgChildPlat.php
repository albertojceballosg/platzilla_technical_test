<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/jQueryUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $currentModule;

	$recordId = SettingsUtils::purify ($_REQUEST, 'record');

	$dialogId = 'dlgAcciones';

	$smartyDlg = new vtigerCRM_Smarty ();
	$smartyDlg->assign ('DIALOG_ID', $dialogId);
	$smartyDlg->assign ('LABEL', getTranslatedString ('Definir/Configurar'));
	$smartyDlg->assign ('URL', "index.php?module={$currentModule}&action=setCfgChildPlat&Ajax=true&instanciasid={$recordId}");
	echo $smartyDlg->fetch ('modules/instancias/cfgChildPlat.tpl');
	echo escribeDlgModal ($dialogId, '');
