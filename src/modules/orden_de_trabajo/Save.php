<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	checkFileAccessForInclusion ("modules/$currentModule/$currentModule.php");
	require_once ("modules/$currentModule/$currentModule.php");

	$mode            = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$record          = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;
	$isDuplicate     = isset ($_REQUEST ['isDuplicate']) && $_REQUEST ['isDuplicate'] == 'true';
	$assignType      = isset ($_REQUEST ['assigntype']) ? vtlib_purify ($_REQUEST ['assigntype']) : null;
	$assignedUserId  = isset ($_REQUEST ['assigned_user_id']) ? vtlib_purify ($_REQUEST ['assigned_user_id']) : null;
	$assignedGroupId = isset ($_REQUEST ['assigned_group_id']) ? vtlib_purify ($_REQUEST ['assigned_group_id']) : null;
	$search          = isset ($_REQUEST ['search_url']) ? vtlib_purify ($_REQUEST ['search_url']) : '';
	$returnAction    = (isset ($_REQUEST ['return_action'])) && ($_REQUEST ['return_action'] != '') ? vtlib_purify ($_REQUEST ['return_action']) : 'DetailView';
	$returnModule    = (isset ($_REQUEST ['return_module'])) && ($_REQUEST ['return_module'] != '') ? vtlib_purify ($_REQUEST ['return_module']) : $currentModule;
	$returnId        = (isset ($_REQUEST ['return_id'])) && ($_REQUEST ['return_id'] != '') ? vtlib_purify ($_REQUEST ['return_id']) : '';
	$urlPlatDb       = (isset ($_REQUEST ['platdb'])) && (!empty ($_REQUEST ['platdb'])) ? '&platdb=' . vtlib_purify ($_REQUEST ['platdb']) : '';
	$pageNumber      = isset ($_REQUEST ['pagenumber']) ? vtlib_purify ($_REQUEST ['pagenumber']) : 1;
	$parentTab       = getParentTab ();

	/** @var CRMEntity|stdClass $focus */
	$focus     = new $currentModule ();
	$modeEvent = '';
	setObjectValuesFromRequest ($focus);
	if ($isDuplicate) {
		// En duplicación se debe crear un nuevo registro. Se limpian isDuplicate/record
		// durante el save para evitar que el ajuste genérico de fechas de CRMEntity::save
		// use el nombre incorrecto de tabla. Las relaciones uitype=10 se duplican
		// manualmente después del save con el ID original.
		$mode = 'create';
		$focus->mode = 'create';
		$focus->id = '';
	} else if ($mode) {
		$focus->mode = $mode;
	}
	if (!$isDuplicate && $record) {
		$focus->id = $record;
		$modeEvent = 'save';
	}
	if ($assignType == 'U') {
		$focus->column_fields ['assigned_user_id'] = $assignedUserId;
	} else if ($assignType == 'T') {
		$focus->column_fields ['assigned_user_id'] = $assignedGroupId;
	}

	if ($returnAction == 'KANBA-SAVE') {
		try {
			$focus->retrieve_entity_info ($record, $currentModule);
			$focus->column_fields ['assigned_user_id'] = $assignedUserId;
			$focus->save ($currentModule);
			header('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array ('error' => 'OK'));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
		exit();
	} else {
		if ($isDuplicate) {
			$savedIsDuplicate = $_REQUEST['isDuplicate'];
			$savedRecord = $_REQUEST['record'];
			unset($_REQUEST['isDuplicate']);
			unset($_REQUEST['record']);
		}
		$focus->save ($currentModule);
		if ($isDuplicate) {
			$_REQUEST['isDuplicate'] = $savedIsDuplicate;
			$_REQUEST['record'] = $savedRecord;
			if (!empty($focus->id)) {
				$focus->duplicateUitype10Relations($currentModule, $savedRecord, $focus->id);
			}
		}
		if ((!$returnId) && ($mode == 'create')) {
			$returnId  = $focus->id;
			$modeEvent = 'save';
		}
	}

	header ("Location: index.php?module=$returnModule&action=$returnAction&record=$returnId&parenttab=$parentTab&mode={$modeEvent}&start={$pageNumber}{$search}{$urlPlatDb}");
