<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('vtlib/Vtiger/Utils.php');
	
	global $adb, $current_language, $current_user, $site_URL;
	setBugSnag ($site_URL);
	
	$platform            = $_SESSION ['plat'];
	$objCalculatedFields = new CalculatedFieldsUtils ($adb, $platform);
	$smarty              = new vtigerCRM_Smarty ();
	$method              = SettingsUtils::purify ($_REQUEST, 'method');
	$calculatedFilter    = '';
	$operation           = '';
	$operationFieldId    = '';
	$inRecordData        = '';
	
	try {
		$smarty     = new vtigerCRM_Smarty ();
		$recordEdit = SettingsUtils::purify ($_REQUEST, 'record');
		if ($recordEdit != null) {
			$calculateFieldData = $objCalculatedFields->getCalculateFieldsById ($recordEdit);
			$smarty->assign ('DESCRIPTION', $calculateFieldData->getDescription ());
			$smarty->assign ('TITLE', $calculateFieldData->getName ());
			$smarty->assign ('TABID', $calculateFieldData->getModuleName ());
			$smarty->assign ('COD', $recordEdit);
			$operation        = $calculateFieldData->getOperationName ();
			$operationFieldId = $calculateFieldData->getColumnName ();
			$inRecordData     = $calculateFieldData->getRelatedModules ();
			$period           = $calculateFieldData->getPeriod ();
			$periodField      = $calculateFieldData->getPeriodField ();
			if (!empty($calculateFieldData->getSqlData ())) {
				$calculatedFilter = json_decode (str_replace ('&quot;', '"', $calculateFieldData->getSqlData ()), true);
				$calculatedFilter = base64_encode (serialize ($calculatedFilter));
			}
		} else if ($method != null) {
			$smarty->assign ('DESCRIPTION', SettingsUtils::purify ($_REQUEST, 'description'));
			$smarty->assign ('TITLE', SettingsUtils::purify ($_REQUEST, 'title'));
			$smarty->assign ('TABID', SettingsUtils::purify ($_REQUEST, 'moduleId'));
			$operation         = SettingsUtils::purify ($_REQUEST, 'operation');
			$operationFieldId = SettingsUtils::purify ($_REQUEST, 'operationfieldId');
			$period           = SettingsUtils::purify ($_REQUEST, 'period');
			$periodField      = SettingsUtils::purify ($_REQUEST, 'periodfieldId');
			if ($recordEdit != null) {
				$smarty->assign ('COD', $recordEdit);
			}
			$filterField  = SettingsUtils::purify ($_REQUEST, 'filterField');
			$inRecordData = SettingsUtils::purify ($_REQUEST, 'inRecord');
			if ($filterField != null) {
				$filterData       = array (
					'filterField'     => $filterField,
					'filterOperator'  => SettingsUtils::purify ($_REQUEST, 'filterOperator'),
					'filterValue'     => SettingsUtils::purify ($_REQUEST, 'filterValue'),
					'filterJoin'      => SettingsUtils::purify ($_REQUEST, 'filterJoin'),
					'filterGroupJoin' => SettingsUtils::purify ($_REQUEST, 'conditionGroups'),
					'indexGrupo'      => SettingsUtils::purify ($_REQUEST, 'indexGrupo'),
					'moduleFilter'    => $moduleName,
				);
				$calculatedFilter = base64_encode (serialize ($filterData));
			}
		}
		
		$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));
		$smarty->assign ('TGM', $objCalculatedFields->getAllModules ());
		$smarty->assign ('CFF', $calculatedFilter);
		$smarty->assign ('OPERID', $operation);
		$smarty->assign ('OPERFID', $operationFieldId);
		$smarty->assign ('PERIOD', $period);
		$smarty->assign ('PERIOD_FIELDID', $periodField);
		$smarty->assign ('IN_RECORD', $inRecordData);
		$smarty->display ('modules/calculated_fields/CreateCalculatedFieldsStepOne.tpl');
	} catch (Exception $e) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $e->getMessage ());
		unset ($_SESSION ['flashmessage']);
		header ('Location: index.php?module=calculated_fields&action=index&parenttab=Settings');
	}
