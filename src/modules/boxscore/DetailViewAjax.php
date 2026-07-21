<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	/** @var CRMEntity|stdClass $entity */
	$entity = CRMEntity::getInstance ($currentModule);

	$ajaxAction = isset ($_REQUEST ['ajxaction']) ? $_REQUEST ['ajxaction'] : null;
	if ($ajaxAction == 'DETAILVIEW') {
		if(isset($_REQUEST['recordid'])) {
			$crmId = vtlib_purify($_REQUEST['recordid']);
		} else{
			$crmId = '';
		}

		if(isset($_REQUEST['fldName'])) {
			$fieldName = vtlib_purify($_REQUEST['fldName']);
		} else{
			$fieldName = '';
		}

		if(isset($_REQUEST['fieldValue'])) {
			$fieldValue = utf8RawUrlDecode($_REQUEST['fieldValue']);
		} else{
			$fieldValue = null;
		}

		if ($crmId != '') {
			$entity->retrieve_entity_info ($crmId, $currentModule);
			$entity->column_fields [ $fieldName ] = $fieldValue;
			$entity->id                           = $crmId;
			$entity->mode                         = 'edit';
			$entity->save ($currentModule);
			if ($entity->id != '') {
				echo ':#:SUCCESS';
			} else {
				echo ':#:FAILURE';
			}
		} else {
			echo ':#:FAILURE';
		}
	} else if (($ajaxAction == 'LOADRELATEDLIST') || ($ajaxAction == 'DISABLEMODULE')) {
		require_once ('include/ListView/RelatedListViewContents.php');
	}
