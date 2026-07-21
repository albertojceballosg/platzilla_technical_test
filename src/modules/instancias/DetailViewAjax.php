<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	/** @var CRMEntity|stdClass $entity */
	$entity = CRMEntity::getInstance ($currentModule);

	$ajaxAction = isset ($_REQUEST ['ajxaction']) ? $_REQUEST ['ajxaction'] : null;
	if ($ajaxAction == 'DETAILVIEW') {
		$crmId      = isset ($_REQUEST ['recordid']) ? vtlib_purify ($_REQUEST ['recordid']) : '';
		$fieldName  = isset ($_REQUEST ['fldName']) ? vtlib_purify ($_REQUEST ['fldName']) : '';
		$fieldValue = isset ($_REQUEST ['fieldValue']) ? utf8RawUrlDecode ($_REQUEST ['fieldValue']) : null;
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
