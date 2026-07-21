<?php
	global $currentModule;
	$modObj = CRMEntity::getInstance($currentModule);

	$ajaxAction = $_REQUEST['ajxaction'];
	if($ajaxAction == 'DETAILVIEW') {
		$crmId = $_REQUEST['recordid'];
		$tableName = $_REQUEST['tableName'];
		$fieldName = $_REQUEST['fldName'];
		$fieldValue = utf8RawUrlDecode($_REQUEST['fieldValue']);
		if($crmId != '') {
			$modObj->retrieve_entity_info($crmId, $currentModule);
			$modObj->column_fields[$fieldName] = $fieldValue;
			$modObj->id = $crmId;
			$modObj->mode = 'edit';
			$modObj->save($currentModule);
			if($modObj->id != '') {
				echo ':#:SUCCESS';
			} else {
				echo ':#:FAILURE';
			}
		} else {
			echo ':#:FAILURE';
		}
	} else if($ajaxAction == 'LOADRELATEDLIST' || $ajaxAction == 'DISABLEMODULE') {
		require_once 'include/ListView/RelatedListViewContents.php';
	}
