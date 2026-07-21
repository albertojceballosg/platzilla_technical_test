<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');

	global $currentModule;

	/** @var CRMEntity|stdClass $modObj */
	$modObj = CRMEntity::getInstance ($currentModule);

	$ajaxaction = isset ($_REQUEST ['ajxaction']) ? $_REQUEST ['ajxaction'] : null;
	$actionType = isset ($_REQUEST ['type']) ? vtlib_purify ($_REQUEST ['type']) : null;
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
		if ($actionType == 'card') {
			$record     = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : '';
			$relationId = isset ($_REQUEST ['relation_id']) ? vtlib_purify ($_REQUEST ['relation_id']) : null;
			$actions    = isset ($_REQUEST ['actions']) ? vtlib_purify ($_REQUEST ['actions']) : null;
			$orderBy    = isset ($_REQUEST ['order_by']) ? vtlib_purify ($_REQUEST ['order_by']) : null;
			$modalEdit  = getRelatedListProperty ($relationId, 'modaledit');
			$_SESSION ['rlvs'][ $currentModule ][ $relationId ]['currentRecord'] = $record;
			try {
				$relatedListCard = GridViewHelper::getRelatedListByRelatedModule (
					array (
						'actions'       => $actions,
						'recordId'      => $record,
						'relationId'    => $relationId,
						'currentModule' => $currentModule,
						'entity'        => $modObj,
						'app_strings'   => $app_strings,
						'mod_strings'   => $mod_strings,
						'theme'         => $theme,
						'orderBy'       => $orderBy,
					)
				);
				$_SESSION ['rlvs'][ $currentModule ][ $relationId ]['currentRecord'] = $record;
			} catch (Exception $e) {
				$relatedListCard = null;
			}
			echo $relatedListCard;
		} else {
			require_once ('include/ListView/RelatedListViewContents.php');
		}
	}
