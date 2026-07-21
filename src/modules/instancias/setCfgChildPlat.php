<?php
	require_once ('Smarty_setup.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/Settings/lib/ConfigRelationshipsHelper.class.php');

	global $adb, $mod_strings, $app_strings, $currentModule, $current_user, $theme, $singlepane_view;

	$recordId = isset ($_REQUEST ['instanciasid']) ? vtlib_purify ($_REQUEST ['instanciasid']) : null;

	/** @var CRMEntity|stdClass $entity */
	$entity = CRMEntity::getInstance ($currentModule);
	if ($recordId != '') {
		$entity->id = $recordId;
		$entity->retrieve_entity_info ($recordId, $currentModule);
	}
	$cfg = ConfigRelationshipsHelper::getRelationshipData ($adb, $entity->column_fields ['code']);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('RECORD', $recordId);
	if (count ($cfg) == 0) {
		$smarty->assign ('LIST_MODULES', ConfigRelationshipsHelper::renderExportableModules ($adb));
		$smarty->assign ('LIST_ROLES', getAllRoleDetails ());
		$smarty->assign ('NOMBRE_CODIGO', $entity->column_fields['code']);
		$smarty->assign ('NOMBRE_ORGANIZACION', $entity->column_fields['name']);
		$smarty->display ('modules/instancias/createRelationPlat.tpl');
	} else {
		echo "<script type=\"text/javascript\">javascript:window.location.href='index.php?module=Settings&action=cfgRelations&record={$cfg ['relationsship_platid']}';</script>";
	}
