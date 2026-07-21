<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ModuleEditPermissionManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/TableFieldManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/Settings/lib/EditableFieldsHelper.class.php');
	require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');
	require_once ('modules/Settings/lib/ModuleManagerHelper.class.php');
	/**
	 * Archivo que controla las funcionalidades de Configuración (Editor de Disposición)
	 *
	 * @var PearDatabase $adb
	 * @var string $app_strings
	 * @var string $current_user
	 * @var string $currentModule
	 * @var string $mod_strings
	 * @var string $theme
	 */

	global $adb, $app_strings, $current_user, $currentModule, $mod_strings, $theme;

	$moduleName       = PlatzillaUtils::purify ($_GET, 'formodule');
	$isInstance       = !empty ($_SESSION ['platInstancia']);
	$asMother         = PlatzillaUtils::purify ($_GET, 'asmother', null);
	$userProfilesList = getCurrentUserProfileList ();

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$moduleObject = ModuleManager::getInstance ($adb)->fetchModule ($moduleName, false);
	if (empty ($moduleObject)) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', 'El módulo solicitado no está registrado');
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=Settings&action=ModuleManager&parenttab=Settings');
		$smarty->display ('Message.tpl');
		exit ();
	}
	$isInstance = (empty ($asMother)) ? $isInstance : false;
	if ($isInstance) {
		$customBlock = LayoutBlockListHelper::getCustomBlock ($adb, $moduleName);
	}

	$fields          = $moduleObject->getFields ();
	$mandatoryFields = array ();
	foreach ($fields as $field) {
		$field->setLabel (getTranslatedString ($field->getLabel (), $moduleName));
		if ($field->isMandatory()) {
			$mandatoryFields [$field->getBlockId()] [] = $field->getLabel ();
		}
	}

	$relatedLists           = ModuleRelationshipManager::getInstance ($adb)->fetchRelationships ($moduleName);
	$availableRelatedFields = LayoutBlockListHelper::getAvailableFieldByRelatedList ($adb, $relatedLists, $moduleName);
	$entityModules          = LayoutBlockListHelper::getEntityModules ($adb, $moduleName);
	$entityModulesName      = (count ($entityModules)) ? array_column ($entityModules,'name') : array ();
	$calculatedFields       = new CalculatedFieldsUtils ($adb, $_SESSION ['plat']);
	$calculatedSystems      = $calculatedFields->getAllCalculateSystem ($current_user);
	usort ($calculatedSystems, function (CalculationSystem $calculatedSystemA, CalculationSystem $calculatedSystemB) {
		return strcmp ($calculatedSystemA->getName (), $calculatedSystemB->getName ());
	});

	$sortFieldByLabelFunction = function (Field $fieldA, Field $fieldB) {
		return strcmp ($fieldA->getLabel (), $fieldB->getLabel ());
	};

	$fieldsVisibility = LayoutBlockListHelper::getVisibilityFieldByProfiles ($adb, $moduleName, $userProfilesList);
	$allModules                 = ModuleManagerHelper::fetchModules ($adb);
	$moduleApplications         = ModuleManagerHelper::fetchModuleApplications ($adb, array_merge ($allModules ['admin'], $allModules ['tool'], $allModules ['user']));
	$applications               = ModuleManagerHelper::fetchApplications ($adb);
	$availableEntityTypeModules = ModuleManagerHelper::fetchAvailableEntityTypeModules ($adb);
	$availableFieldTypes        = ModuleManagerHelper::fetchAvailableFieldTypes ();
	$availableGlobalPicklists   = ModuleManagerHelper::fetchAvailableGlobalPicklists ($adb);
	

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('AVAILABLE_EDIT_PERMISSIONS', ModuleEditPermissionManager::getInstance ($adb)->fetchConditionGroups ($moduleName));
	$smarty->assign ('AVAILABLE_ENTITY_MODULES', $entityModules);
	$smarty->assign ('AVAILABLE_RELATED_FIELDS', $availableRelatedFields);
	$smarty->assign ('AVAILABLE_GLOBAL_PICKLISTS', GlobalPicklistManager::getInstance ($adb)->fetchPicklists ());
	$smarty->assign ('AVAILABLE_MENU_DATA', PlatformUtils::getMenuData ($adb));
	$blocks = $moduleObject->getBlocks();
	
	$smarty->assign ('APPLICATIONS', $applications);
	$smarty->assign ('AVAILABLE_ENTITY_TYPE_MODULES', $availableEntityTypeModules);
	$smarty->assign ('AVAILABLE_FIELD_TYPES', $availableFieldTypes);
	$smarty->assign ('AVAILABLE_GLOBAL_PICKLISTS', $availableGlobalPicklists);
	
	
	$smarty->assign ('CALCULATED_SYSTEMS', $calculatedSystems);
	$smarty->assign ('CUSTOM_BLOCK', $customBlock);
	$smarty->assign ('DATE_FIELD_IMPORT', LayoutBlockListHelper::DATE_FIELD_IMPORT);
	$smarty->assign ('ENTITY_MODULES_NAME', $entityModulesName);
	$smarty->assign ('EDITABLE_FIELDS', EditableFieldsHelper::getEditableFields ($adb, $moduleName));
	$smarty->assign ('EDITABLE_FIELDS_LIST', EditableFieldsHelper::getEditableListHtml ($adb, $moduleName, $mod_strings));
	$smarty->assign ('FIELD_GRID_OPTIONS', WizardUtils::getFieldTypesAsOptions (true));
	$smarty->assign ('FIELD_TYPE_OPTIONS', WizardUtils::getFieldTypesAsOptions ());
	$smarty->assign ('FIELDS_VISIBILITY', $fieldsVisibility);
	$smarty->assign ('IS_INSTANCE', $isInstance);
	$smarty->assign ('MANDATORY_FIELDS', $mandatoryFields);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $moduleObject);
	$smarty->assign ('MODULE_OPTIONS', WizardUtils::getModuleListAsOptions ($adb));
	$smarty->assign ('MODULE_WITH_GRID', getModulesWithGridFields ($moduleName, '2202'));
	$smarty->assign ('MODULE_WITH_LIST', getModulesWithGridFields ($moduleName, '15'));
	$smarty->assign ('N0_IMPORT_FIELD', LayoutBlockListHelper::N0_IMPORT_FIELD);
	$smarty->assign ('RELATED_LISTS', $relatedLists);
	$smarty->assign ('SORT_BY_LABEL_FUNCTION', $sortFieldByLabelFunction);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('TABLE_FIELDS', TableFieldManager::getInstance ($adb)->fetchTableFieldByModule ($moduleName));
	$smarty->assign ('UNMODIFIABLE_FIELDS', LayoutBlockListHelper::getUnmodifiableFields ($adb, $moduleName));
	$smarty->display ('Settings/LayoutBlockList.tpl');
