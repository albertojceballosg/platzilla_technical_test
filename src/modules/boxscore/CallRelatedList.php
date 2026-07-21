<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/ListView/RelatedListViewSession.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DetailViewUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('user_privileges/default_module_view.php');

	global $mod_strings, $app_strings, $currentModule, $theme, $singlepane_view, $smarty;

	if(isset($_REQUEST['action'])) {
		$action = vtlib_purify($_REQUEST['action']);
	} else{
		$action = null;
	}

	if(isset($_REQUEST['record'])) {
		$record = vtlib_purify($_REQUEST['record']);
	} else{
		$record = '';
	}


	$category = getParentTab ();

	if (($singlepane_view != 'true') || ($action != 'CallRelatedList')) {
		$isDuplicate = isset ($_REQUEST ['isDuplicate']) ? vtlib_purify ($_REQUEST ['isDuplicate']) : null;
		$relationId = (isset ($_REQUEST ['relation_id'])) && (!empty ($_REQUEST ['relation_id'])) ? vtlib_purify ($_REQUEST ['relation_id']) : null;
		$selectedHeader = (isset ($_REQUEST ['selected_header'])) && (!empty ($_REQUEST ['selected_header'])) ? vtlib_purify ($_REQUEST ['selected_header']) : null;
		$mode = (isset ($_REQUEST ['mode'])) && (!empty ($_REQUEST ['mode'])) ? trim (vtlib_purify ($_REQUEST ['mode'])) : null;
		$platformDatabase = (isset ($_REQUEST ['platdb'])) && (!empty ($_REQUEST ['platdb'])) ? vtlib_purify ($_REQUEST ['platdb']) : null;

		$toolButtons = Button_Check ($currentModule);

		/** @var CRMEntity|stdClass $focus */
		$focus = CRMEntity::getInstance ($currentModule);
		if ($record != '') {
			$focus->retrieve_entity_info ($record, $currentModule);
			$focus->id = $record;
		}

		if ($isDuplicate == 'true') {
			$focus->id = '';
		}

		if (!$_SESSION ['rlvs'][ $currentModule ]) {
			unset ($_SESSION ['rlvs']);
		}

		// Module Sequence Numbering
		$modSeqField = getModuleSequenceField ($currentModule);
		$modSeqId    = ($modSeqField != null) ? $focus->column_fields [ $modSeqField ['name'] ] : $focus->id;

		if ((!empty ($_REQUEST ['selected_header'])) && ($relationId !== null)) {
			RelatedListViewSession::addRelatedModuleToSession ($relationId, $selectedHeader);
		}

		$smarty->assign ('CUSTOM_MODULE', true);
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE', $currentModule);
		$smarty->assign ('SINGLE_MOD', getTranslatedString ("SINGLE_{$currentModule}", $currentModule));
		$smarty->assign ('CATEGORY', $category);
		$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('ID', $focus->id);
		$smarty->assign ('MODE', $focus->mode);
		$smarty->assign ('CHECK', $toolButtons);
		$smarty->assign ('NAME', $focus->column_fields[ $focus->def_detailview_recname ]);
		$smarty->assign ('UPDATEINFO', updateInfo ($focus->id));
		$smarty->assign ('MOD_SEQ_ID', $modSeqId);
		$smarty->assign ('RELATEDLISTS', getRelatedLists ($currentModule, $focus));
		$smarty->assign ('SELECTEDHEADERS', RelatedListViewSession::getRelatedModulesFromSession ());
		if ($platformDatabase) {
			$smarty->assign ('PLATDB', $platformDatabase);
		}
		if ($mode != '') {
			$smarty->assign ('OP_MODE', $mode);
		}
		$smarty->display ('RelatedLists.tpl');
	} else {
		header ("Location:index.php?action=DetailView&module=$currentModule&record=$record&parenttab=$category");
	}
