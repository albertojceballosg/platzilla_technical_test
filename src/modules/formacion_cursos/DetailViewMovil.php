<?php

require_once('Smarty_setup.php');
require_once('user_privileges/default_module_view.php');

global $adb, $mod_strings, $app_strings, $currentModule, $current_user, $theme, $singlepane_view, $plat;

$focus = CRMEntity::getInstance($currentModule);

$tool_buttons = Button_Check($currentModule);
$smarty = new vtigerCRM_Smarty();

$record = $_REQUEST['record'];
$isduplicate = vtlib_purify($_REQUEST['isDuplicate']);
$tabid = getTabid($currentModule);
$category = getParentTab();

if ($record != '') {
	$focus->id = $record;
	$focus->retrieve_entity_info($record, $currentModule);
}
if ($isduplicate == 'true') {
	$focus->id = '';
}

// Identify this module as custom module.
$smarty->assign('CUSTOM_MODULE', true);

$smarty->assign('APP', $app_strings);
$smarty->assign('MOD', $mod_strings);
$smarty->assign('MODULE', $currentModule);
// All: Update Single Module Instance name here.
$smarty->assign('SINGLE_MOD', 'SINGLE_'.$currentModule);
$smarty->assign('CATEGORY', $category);
$smarty->assign('IMAGE_PATH', 'themes/$theme/images/');
$smarty->assign('THEME', $theme);
$smarty->assign('ID', $focus->id);
$smarty->assign('MODE', $focus->mode);

$recordName = array_values(getEntityName($currentModule, $focus->id));
$recordName = $recordName[0];
$smarty->assign('NAME', $recordName);
$smarty->assign('UPDATEINFO',updateInfo($focus->id));

// Module Sequence Numbering
$mod_seq_field = getModuleSequenceField($currentModule);
if ($mod_seq_field != null) {
	$mod_seq_id = $focus->column_fields[$mod_seq_field['name']];
} else {
	$mod_seq_id = $focus->id;
}
$smarty->assign('MOD_SEQ_ID', $mod_seq_id);
// END

$validationArray = split_validationdataArray(getDBValidationData($focus->tab_name, $tabid));
$smarty->assign('VALIDATION_DATA_FIELDNAME',$validationArray['fieldname']);
$smarty->assign('VALIDATION_DATA_FIELDDATATYPE',$validationArray['datatype']);
$smarty->assign('VALIDATION_DATA_FIELDLABEL',$validationArray['fieldlabel']);

$smarty->assign('EDIT_PERMISSION', isPermitted($currentModule, 'EditView', $record));
$smarty->assign('CHECK', $tool_buttons);

if (PerformancePrefs::getBoolean('DETAILVIEW_RECORD_NAVIGATION', true) && isset($_SESSION[$currentModule.'_listquery'])) {
	$recordNavigationInfo = call_user_func('ListViewSession::getListViewNavigation($focus->id)');
	VT_detailViewNavigation($smarty,$recordNavigationInfo,$focus->id);
}

$smarty->assign('IS_REL_LIST', isPresentRelatedLists($currentModule));
$smarty->assign('SinglePane_View', $singlepane_view);

$singlepane_view = 'true';
if ($singlepane_view == 'true') {
	$related_array = getRelatedLists($currentModule,$focus);
	$smarty->assign('RELATEDLISTS', $related_array);
		
	require_once('include/ListView/RelatedListViewSession.php');
	if (!empty($_REQUEST['selected_header']) && !empty($_REQUEST['relation_id'])) {
		RelatedListViewSession::addRelatedModuleToSession(
			vtlib_purify($_REQUEST['relation_id']),
			vtlib_purify($_REQUEST['selected_header'])
		);
	}
	$open_related_modules = RelatedListViewSession::getRelatedModulesFromSession();
	$smarty->assign('SELECTEDHEADERS', $open_related_modules);
}

if (isPermitted($currentModule, 'EditView', $record) == 'yes') {
	$smarty->assign('EDIT_DUPLICATE', 'permitted');
}
if (isPermitted($currentModule, 'Delete', $record) == 'yes') {
	$smarty->assign('DELETE', 'permitted');
}

$focus->column_fields['image']=getFileFieldValue($currentModule,'img_curso',$focus->id);
$smarty->assign('FIELDS', $focus->column_fields);

function getExtension($str) {
	$pospunto = strrpos($str,'.');
	if (!$pospunto) {
		return '';
	}
	$largo = (strlen($str) - $pospunto);
	$comienzo = ($pospunto + 1);
	$ext = substr($str, $comienzo, $largo);
	return $ext;
}

function getEvaluacion($fpid) {
	global $adb;
	$ret = array();
	$sql='SELECT vfp.* FROM `vtiger_formacion_pruebas` vfp 
			INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfp.`formacion_pruebasid` AND crm.`deleted`=0 
			INNER JOIN vtiger_crmentityrel crmrel ON crmrel.`relcrmid`=crm.`crmid` AND crmrel.`crmid`='.$fpid;
	$q=$adb->pquery($sql, array());
	while($r=$adb->fetchByAssoc($q)){
		$ret[]=$r;
	}
	return $ret;
}

$sql="SELECT vfl.*,v.*,CONCAT(va.`path`,va.`attachmentsid`,'_',va.`name`) AS material,va.`name` AS archivo FROM vtiger_formacion_lecciones vfl
		INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfl.`formacion_leccionesid` AND crm.`deleted`=0
		INNER JOIN vtiger_crmentityrel crmrel ON crmrel.`relcrmid`=crm.`crmid` AND crmrel.`crmid`=$focus->id
		LEFT JOIN vtiger_videos v ON v.`idvideo`=vfl.`videoid`
		LEFT JOIN vtiger_attachments va ON va.`attachmentsid`=vfl.`materiales`
		ORDER by vfl.orden ASC";
$q=$adb->pquery($sql, array());
$lecciones = array();
while ($r=$adb->fetchByAssoc($q)) {
	$r['ext']=getExtension($r['file']);
	$r['ext_arch']=strtolower(getExtension($r['archivo']));
	$eval=getEvaluacion($r['formacion_leccionesid']);
	if ($eval) {
		$r['eval'] = $eval;
	}
	$lecciones[]=$r;
}

$smarty->assign('LECCIONES', $lecciones);
$smarty->assign('LECCIONES_OBJ', json_encode($lecciones));
$smarty->assign('RECORD', $focus->id);

// Gather the custom link information to display
require_once ('vtlib/Vtiger/Link.php');
$customlink_params = array('MODULE' => $currentModule, 'RECORD' => $focus->id, 'ACTION' => vtlib_purify($_REQUEST['action']));
$smarty->assign('CUSTOM_LINKS', Vtiger_Link::getAllByType(getTabid($currentModule), array('DETAILVIEWBASIC', 'DETAILVIEW', 'DETAILVIEWWIDGET'), $customlink_params));
// END

// Record Change Notification
$focus->markAsViewed($current_user->id);
// END
$smarty->assign('CAMPOS_TIPO_GRID', escribeDetalleCamposGrid($currentModule,$focus->id));
$smarty->assign('CAMPOS_TIPO_MATRIX', escribeDetalleCamposMatrix($currentModule,$focus->id));

$smarty->assign('DETAILVIEW_AJAX_EDIT', PerformancePrefs::getBoolean('DETAILVIEW_AJAX_EDIT', true));
$buttons = sendNotificationButton($currentModule, $focus->id);
$smarty->assign('CUSTOM_BUTTONS', $buttons);

$nplat=$plat;
if (strstr($plat,'cliente-') || strstr($plat,'clienteweb-')) {
	$lstPlat = explode('-',$plat);
	$nplat=$lstPlat[1];
}
$smarty->assign('PLAT_CODE', $nplat);
$sql='SELECT * FROM vtiger_organizationdetails';
$result = $adb->pquery($sql, array());
$organization_logo = decode_html($adb->query_result($result,0,'logoname'));
$smarty->assign('LOGO',$organization_logo);
$smarty->assign('MOVIL','1');

$smarty->display('DetailView.tpl');

?>
