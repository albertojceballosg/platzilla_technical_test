<?php
	require_once('Smarty_setup.php');
	include_once('include/utils/comunesTareas.php');
	
	global $mod_strings, $app_strings, $currentModule, $current_user, $theme, $singlepane_view;
	$smarty = new vtigerCRM_Smarty();
	
	$smarty->assign('APP', $app_strings);
	$smarty->assign('MOD', $mod_strings);
	$smarty->assign('MODULE', $currentModule);
	// TODO: Update Single Module Instance name here.
	$smarty->assign('SINGLE_MOD', 'SINGLE_'.$currentModule); 
	$smarty->assign('CATEGORY', $category);
	$smarty->assign('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign('THEME', $theme);
	$smarty->assign('ID', $focus->id);
	$smarty->assign('MODE', $focus->mode);
	$smarty->assign("dateFormat",parse_calendardate($current_user->date_format));
	$smarty->assign("dateStr",$current_user->date_format);
	$smarty->assign("LISTHITOS", obtenerHitosProyectos($_REQUEST['proyectosid']));
	
	$smarty->display('modules/hito/mostrarHitos.tpl');
	
	
?>