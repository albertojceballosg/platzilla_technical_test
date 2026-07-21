<?php
require_once('Smarty_setup.php');
require_once('data/Tracker.php');
require_once('include/database/PearDatabase.php');
require_once('include/utils/utils.php');
require_once('modules/admin_widgets/admin_widgets.php');

global $adb;
global $log;
global $mod_strings;
global $app_strings;
global $current_language;
global $theme;
$theme_path='themes/'.$theme.'/';
$image_path=$theme_path.'images/';

$smarty = new vtigerCRM_smarty;

$Widget = new Widgets();

$smarty->assign('APP', $app_strings);
$smarty->assign('THEME', $theme);
$smod_strings = return_module_language($current_language,'admin_widgets');
$smarty->assign('MOD', $smod_strings);
$smarty->assign('MODULE', 'admin_widgets');

$record = $_REQUEST['record'];
$sql = 'select * from vtiger_widgets where widgetid=?';
$result = $adb->pquery($sql, array($record));
$widget = $adb->fetchByAssoc($result);

$tabId = $Widget->getTabId($widget['fld_module']);
$widget['campofechaTraducido'] = html_entity_decode(getTranslatedString($widget['campofecha'], $widget['fld_module']), ENT_QUOTES, 'UTF-8');

// Agregamos la fecha real de acuerdo al valor del campo de la BD para calcular las fechas desde y hasta
if ($widget['tiempofecha'] != 1) {
	$fechas = $Widget->getDateBetween($widget['tiempofecha']);
	$widget['fechadesde'] = $fechas['fechaDesde'];
	$widget['fechahasta'] = $fechas['fechaHasta'];
	// debemos reconstruir el query SQLPrimario
	$sqlPrimario = construirSqlPrimario($widget, $tabId);
	$widget['sqlprimario'] = $sqlPrimario;
	switch ($widget['tiempofecha']) {
		case '2':
			$widget['tiempofecha'] = 'Hoy';
			break;
		case '3':
			$widget['tiempofecha'] = 'Última Semana';
			break;
		case '4':
			$widget['tiempofecha'] = 'Semana Actual';
			break;
		case '5':
			$widget['tiempofecha'] = 'Último Mes';
			break;
		case '6':
			$widget['tiempofecha'] = 'Mes Actual';
			break;
		case '7':
			$widget['tiempofecha'] = 'Últimos 7 días';
			break;
		case '8':
			$widget['tiempofecha'] = 'Últimos 30 días';
			break;
		case '9':
			$widget['tiempofecha'] = 'Últimos 60 días';
			break;
		case '10':
			$widget['tiempofecha'] = 'Últimos 90 días';
			break;
		case '11':
			$widget['tiempofecha'] = 'Últimos 120 días';
			break;
		default:
			$widget['tiempofecha'] = 'Personalizado';
			break;
	}
} else {
	$widget['tiempofecha'] = 'Personalizado';
}

switch ($widget['operation']) {
	case '2':
		$widget['operacion'] = 'Suma';
		break;
	
	case '3':
		$widget['operacion'] = 'Promedio';
		break;

	default:
		$widget['operacion'] = 'Conteo';
		break;
}

$widget['module'] = getTabIdLabelByName($widget['fld_module']);
$widget['status'] = ($widget['estatus'] == '1' ? 'Activo' : 'Inactivo');

$sqlValor = $widget['sqlprimario'];
$sqlValor = html_entity_decode($sqlValor, ENT_QUOTES, 'UTF-8');

$resultValor = $adb->query($sqlValor);
$widget['valor'] = $adb->fetchByAssoc($resultValor);

$colorValue = explode('-',$widget['color']);
$widget['colorValue'] = $colorValue[0];
$widget['fieldO'] = $Widget->getFieldLabel($widget['fieldoperation']);
$widget['fieldO'] = getTranslatedString(str_replace('tq.','',$widget['fieldO']),$widget['fld_module']);
$widget['fieldgrouping'] = getTranslatedString(str_replace('tq.','',$widget['fieldgrouping']),$widget['fld_module']);
$smarty->assign('DETAILWIDGET', $widget);
$smarty->display('modules/admin_widgets/DetailWidget.tpl');

?>
