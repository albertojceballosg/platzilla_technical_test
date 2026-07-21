<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	checkFileAccessForInclusion ("modules/$currentModule/$currentModule.php");
	require_once ("modules/$currentModule/$currentModule.php");

	$mode            = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$record          = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;
	$assignType      = isset ($_REQUEST ['assigntype']) ? vtlib_purify ($_REQUEST ['assigntype']) : null;
	$assignedUserId  = isset ($_REQUEST ['assigned_user_id']) ? vtlib_purify ($_REQUEST ['assigned_user_id']) : null;
	$assignedGroupId = isset ($_REQUEST ['assigned_group_id']) ? vtlib_purify ($_REQUEST ['assigned_group_id']) : null;
	$search          = isset ($_REQUEST ['search_url']) ? vtlib_purify ($_REQUEST ['search_url']) : '';
	$returnAction    = (isset ($_REQUEST ['return_action'])) && ($_REQUEST ['return_action'] != '') ? vtlib_purify ($_REQUEST ['return_action']) : 'DetailView';
	$returnModule    = (isset ($_REQUEST ['return_module'])) && ($_REQUEST ['return_module'] != '') ? vtlib_purify ($_REQUEST ['return_module']) : $currentModule;
	$urlPlatDb       = (isset ($_REQUEST ['platdb'])) && (!empty ($_REQUEST ['platdb'])) ? '&platdb=' . vtlib_purify ($_REQUEST ['platdb']) : '';
	$pageNumber      = isset ($_REQUEST ['pagenumber']) ? vtlib_purify ($_REQUEST ['pagenumber']) : 1;
	$parentTab       = getParentTab ();

	try {
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
		if ((isset ($_REQUEST ['return_id'])) && ($_REQUEST ['return_id'] != '')) {
			$returnId = vtlib_purify ($_REQUEST ['return_id']);
		} else if ($record != '') {
			$returnId = $record;
		} else {
			$returnId = $focus->id;
		}
		header ("Location: index.php?action=$returnAction&module=$returnModule&record=$returnId&parenttab=$parentTab&start={$pageNumber}{$search}{$urlPlatDb}");
	} catch (Exception $e) {
		$module          = isset ($_POST ['module']) ? urlencode (vtlib_purify ($_POST ['module'])) : '';
		$parentTab       = isset ($_POST ['parenttab']) ? urlencode (vtlib_purify ($_POST ['parenttab'])) : '';
		$guest           = isset ($_POST ['guest']) ? urlencode (vtlib_purify ($_POST ['guest'])) : '';
		$entityIdType    = isset ($_POST ['entityid_type']) ? urlencode (vtlib_purify ($_POST ['entityid_type'])) : '';
		$entityId        = isset ($_POST ['entityid']) ? urlencode (vtlib_purify ($_POST ['entityid'])) : '';
		$entityIdDisplay = isset ($_POST ['entityid_display']) ? urlencode (vtlib_purify ($_POST ['entityid_display'])) : '';
		$error           = urlencode (vtlib_purify ($e->getMessage ()));
		header ("Location: index.php?module=$module&action=EditView&record=$record&parenttab=$parentTab&guest=$guest&entityid_type=$entityIdType&entityid=$entityId&entityid_display=$entityIdDisplay&search_url=$search&error=$error");
	}
