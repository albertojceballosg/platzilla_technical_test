<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('user_privileges/default_module_view.php');

	global $singlepane_view, $currentModule;

	$idList            = isset ($_REQUEST ['idlist']) ? vtlib_purify ($_REQUEST ['idlist']) : '';
	$destinationModule = isset ($_REQUEST ['destination_module']) ? vtlib_purify ($_REQUEST ['destination_module']) : '';
	$forCrmRecord      = isset ($_REQUEST ['parentid']) ? vtlib_purify ($_REQUEST ['parentid']) : '';
	$mode              = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$action            = ($singlepane_view == 'true') ? 'DetailView' : 'CallRelatedList';
	$parentTab         = getParentTab ();

	/** @var CRMEntity $focus */
	$focus = CRMEntity::getInstance ($currentModule);

	if ($mode == 'delete') {
		// Split the string of ids
		$ids = explode (';', $idList);
		if (!empty ($ids)) {
			$focus->delete_related_module ($currentModule, $forCrmRecord, $destinationModule, $ids);
		}
	} else {
		if (!empty ($idList)) {
			// Split the string of ids
			$ids = explode (';', trim ($idList, ';'));
		} else if ((isset ($_REQUEST ['entityid'])) && (!empty ($_REQUEST ['entityid']))) {
			$ids = vtlib_purify ($_REQUEST ['entityid']);
		}
		if (!empty ($ids)) {
			$focus->save_related_module ($currentModule, $forCrmRecord, $destinationModule, $ids);
		}
	}
	header ("Location: index.php?module=$currentModule&record=$forCrmRecord&action=$action&parenttab=$parentTab");
