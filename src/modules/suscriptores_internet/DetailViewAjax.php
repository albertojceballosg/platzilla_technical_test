<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	/** @var CRMEntity|stdClass $modObj */
	$modObj = CRMEntity::getInstance ($currentModule);

	$ajaxaction = isset ($_REQUEST ['ajxaction']) ? $_REQUEST ['ajxaction'] : null;
	if ($ajaxaction == 'DETAILVIEW') {
		$crmId      = isset ($_REQUEST ['recordid']) ? vtlib_purify ($_REQUEST ['recordid']) : '';
		$fieldName  = isset ($_REQUEST ['fldName']) ? vtlib_purify ($_REQUEST ['fldName']) : '';
		$fieldValue = isset ($_REQUEST ['fieldValue']) ? utf8RawUrlDecode ($_REQUEST ['fieldValue']) : null;
		if ($crmId != '') {
			$modObj->retrieve_entity_info ($crmId, $currentModule);
			$modObj->column_fields [ $fieldName ] = $fieldValue;
			$modObj->id                           = $crmId;
			$modObj->mode                         = 'edit';
			$modObj->save ($currentModule);
			if ($modObj->id != '') {
				echo ':#:SUCCESS';
			} else {
				echo ':#:FAILURE';
			}
		} else {
			echo ':#:FAILURE';
		}
	} else if (($ajaxaction == 'LOADRELATEDLIST') || ($ajaxaction == 'DISABLEMODULE')) {
		require_once ('include/ListView/RelatedListViewContents.php');
	}
