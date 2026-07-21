<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/BlockManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/ActivityAjaxHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $mod_strings;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$function = SettingsUtils::purify ($_REQUEST, 'funcion');
	switch ($function) {
		case 'REL_MODULES_4PICKLIST':
			$module  = SettingsUtils::purify ($_REQUEST, 'formodule');
			$options = ActivityAjaxHelper::getRelatedModulesPicklistOptions ($adb, $module);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('PLACEHOLDER', getTranslatedString ('-Select-'));
			$smarty->assign ('OPTIONS', $options);
			echo $smarty->fetch ('Settings/PickListOptions.tpl');
			break;
		case 'REL_FIELDS_MODULE_4PICKLIST':
			$module = SettingsUtils::purify ($_REQUEST, 'formodule');
			$options = ActivityAjaxHelper::getRelatedModuleFieldsPicklistOptions ($adb, $module);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('PLACEHOLDER', getTranslatedString ('-Select-'));
			$smarty->assign ('OPTIONS', $options);
			echo $smarty->fetch ('Settings/PickListOptions.tpl');
			break;
		case 'obtieneCamposModulo':
			$basePlatform  = SettingsUtils::purify ($_REQUEST, 'plat_base');
			$childPlatform = SettingsUtils::purify ($_REQUEST, 'nombreCodigo');
			$module        = SettingsUtils::purify ($_REQUEST, 'modules');
			$relatedModule = SettingsUtils::purify ($_REQUEST, 'modulerel');
			$fieldMapping  = ActivityAjaxHelper::getFieldMapping ($basePlatform, $childPlatform, $module, $relatedModule);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('LBL_CAMPO_PLATAFORMA_HIJA', getTranslatedString ('LBL_CAMPO_PLATAFORMA_HIJA'));
			$smarty->assign ('LBL_CAMPO_PLATAFORMA_BASE', getTranslatedString ('LBL_CAMPO_PLATAFORMA_BASE'));
			$smarty->assign ('SOURCE_FIELDS', $fieldMapping ['source']);
			$smarty->assign ('TARGET_FIELDS', $fieldMapping ['target']);
			$smarty->assign ('TARGET_PLACEHOLDER', $mod_strings ['Seleccione']);
			echo $smarty->fetch ('Settings/ModuleMapping.tpl');
			break;
		case 'cfgPermisos':
			ActivityAjaxHelper::setPlatformsPermissions ($_REQUEST);
			break;
		case 'hacerCombinable':
			$status        = SettingsUtils::purify ($_REQUEST, 'estado');
			$relatedModule = SettingsUtils::purify ($_REQUEST, 'modulerel');
			ActivityAjaxHelper::setModuleAsCombinable ($adb, $status, $relatedModule);
			break;
		case 'DEL_VAR':
			$variableId = SettingsUtils::purify ($_REQUEST, 'variableid');
			ActivityAjaxHelper::deleteSystemVariable ($adb, $variableId);
			break;
		case 'CHANGE-BLOCKS':
			$moduleName = SettingsUtils::purify ($_GET, 'formodule');
			$record     = SettingsUtils::purify ($_GET, 'record');
			$blocks     = BlockManager::getInstance($adb)->fetchBlocks($moduleName);
			$mandatoryFields = array ();
			foreach ($blocks as $block) {
				foreach ($block->getFields() as $field) {
					$field->setLabel(getTranslatedString($field->getLabel(), $moduleName));
					if ($field->isMandatory()) {
						$mandatoryFields [$block->getId()] [] = $field->getLabel();
					}
				}
			}
			$smarty     = new vtigerCRM_Smarty ();
			$smarty->assign('BLOCKS', $blocks);
			$smarty->assign ('MANDATORY_FIELDS', $mandatoryFields);
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('RECORD', $record);
			echo $smarty->fetch ('Settings/ChangeStatusBlocks.tpl');
			break;
		default:
			// No hacer nada
			break;
	}
