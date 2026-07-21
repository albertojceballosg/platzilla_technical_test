<?php
require_once('Smarty_setup.php');
require_once('include/utils/utils.php');
require_once('modules/Vtiger/layout_utils.php');
require_once('modules/admin_widgets/admin_widgets.php');

global $mod_strings,$app_strings,$log,$theme,$currentModule;
$theme_path='themes/'.$theme.'/';
$image_path=$theme_path.'images/';

$smarty=new vtigerCRM_Smarty;

$Widgets = new Widgets();

$smarty->assign('MODULE',$currentModule);
$smarty->assign('MOD',$mod_strings);
$smarty->assign('APP',$app_strings);
$smarty->assign('THEME', $theme);
$smarty->assign('OPERATIONS',$Widgets->obtenerTiposDeCalculo());
$smarty->assign('LISTAMODULOS',$Widgets->getModules());

$smarty->display('modules/admin_widgets/crearWidget.tpl');

?>
