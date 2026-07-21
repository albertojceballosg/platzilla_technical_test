<?php
/**
 * CancelCreateView.php
 * Vista intermedia que muestra el modal de notificación CANCEL_RECORD
 * y luego redirige al usuario
 */

require_once('Smarty_setup.php');
require_once('include/database/PearDatabase.php');

global $adb, $current_user, $currentModule, $theme;

$forModule = isset($_REQUEST['formodule']) ? vtlib_purify($_REQUEST['formodule']) : $currentModule;
$returnAction = isset($_REQUEST['return_action']) ? vtlib_purify($_REQUEST['return_action']) : 'ListView';
$returnModule = isset($_REQUEST['return_module']) ? vtlib_purify($_REQUEST['return_module']) : $forModule;
$modalId = isset($_REQUEST['modalId']) ? intval($_REQUEST['modalId']) : null;

$smarty = new vtigerCRM_Smarty();
$smarty->assign('FOR_MODULE', $forModule);
$smarty->assign('RETURN_ACTION', $returnAction);
$smarty->assign('RETURN_MODULE', $returnModule);
$smarty->assign('MODAL_ID', $modalId);
$smarty->assign('THEME', $theme);

$smarty->display('CancelCreateView.tpl');
