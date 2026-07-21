<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/VtlibUtils.php');

	global $smarty;

	$recordId = isset ($_GET ['record']) ? vtlib_purify ($_GET ['record']) : null;

	/** @var CRMEntity|stdClass $entity */
	$entity = CRMEntity::getInstance ('invitations');
	if ($recordId) {
		$entity->id = $recordId;
		$entity->retrieve_entity_info ($recordId, 'invitations');
	}

	if (($entity->column_fields ['invitationstatus'])) {
		if ((!isset ($smarty)) || (empty ($smarty))) {
			require_once ('Smarty_setup.php');
			$smarty = new vtigerCRM_Smarty ();
		}
		$smarty->assign ('IS_ERROR', true);
		$smarty->assign ('MESSAGE', 'No está permitido modificar invitaciones enviadas');
		require_once ('modules/invitations/DetailView.php');
	} else {
		require_once ('modules/Vtiger/EditView.php');
	}
