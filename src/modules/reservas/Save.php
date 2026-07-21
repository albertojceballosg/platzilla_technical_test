<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $adb, $currentModule;

	checkFileAccessForInclusion ("modules/$currentModule/$currentModule.php");
	require_once ("modules/$currentModule/$currentModule.php");

	$mode               = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$record             = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;
	$assignType         = isset ($_REQUEST ['assigntype']) ? vtlib_purify ($_REQUEST ['assigntype']) : null;
	$assignedUserId     = isset ($_REQUEST ['assigned_user_id']) ? vtlib_purify ($_REQUEST ['assigned_user_id']) : null;
	$assignedGroupId    = isset ($_REQUEST ['assigned_group_id']) ? vtlib_purify ($_REQUEST ['assigned_group_id']) : null;
	$search             = isset ($_REQUEST ['search_url']) ? vtlib_purify ($_REQUEST ['search_url']) : '';
	$relatedLocationIds = isset ($_REQUEST ['relatedlocationids']) ? vtlib_purify ($_REQUEST ['relatedlocationids']) : null;
	$relatedUserIds     = isset ($_REQUEST ['relateduserids']) ? vtlib_purify ($_REQUEST ['relateduserids']) : null;
	$returnAction       = (isset ($_REQUEST ['return_action'])) && ($_REQUEST ['return_action'] != '') ? vtlib_purify ($_REQUEST ['return_action']) : 'DetailView';
	$returnModule       = (isset ($_REQUEST ['return_module'])) && ($_REQUEST ['return_module'] != '') ? vtlib_purify ($_REQUEST ['return_module']) : $currentModule;
	$returnId           = (isset ($_REQUEST ['return_id'])) && ($_REQUEST ['return_id'] != '') ? vtlib_purify ($_REQUEST ['return_id']) : '';
	$urlPlatDb          = (isset ($_REQUEST ['platdb'])) && (!empty ($_REQUEST ['platdb'])) ? '&platdb=' . vtlib_purify ($_REQUEST ['platdb']) : '';
	$pageNumber         = isset ($_REQUEST ['pagenumber']) ? vtlib_purify ($_REQUEST ['pagenumber']) : 1;
	$parentTab          = getParentTab ();

	/** @var CRMEntity|stdClass $focus */
	$focus = new $currentModule ();
	setObjectValuesFromRequest ($focus);
	if ($mode) {
		$focus->mode = $mode;
	}
	if ($record) {
		$focus->id = $record;
	}
	if ($assignType == 'U') {
		$focus->column_fields ['assigned_user_id'] = $assignedUserId;
	} else if ($assignType == 'T') {
		$focus->column_fields ['assigned_user_id'] = $assignedGroupId;
	}
	$focus->save ($currentModule);

	if ((!$returnId) && (!$mode)) {
		$returnId = $focus->id;
	}

	if (!empty ($relatedUserIds)) {
		$focus->save_related_module ($currentModule, $focus->id, 'usuarios_colladito', $relatedUserIds);
	}
	if (!empty ($relatedLocationIds)) {
		$focus->save_related_module ($currentModule, $focus->id, 'espacios', $relatedLocationIds);
	}

	header ("Location: index.php?module=$returnModule&action=$returnAction&record=$returnId&parenttab=$parentTab&start={$pageNumber}{$search}{$urlPlatDb}");
