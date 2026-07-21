<?php
	require_once ('modules/Vtiger/EditView.php');

	/** @var $focus CRMEntity|stdClass */
	global $adb, $focus, $smarty;

	$result = $adb->pquery (
		"SELECT
			n.notesid,
			ar.attachmentsid,
			n.title,
			n.filename,
			crmen.modifiedtime,
			TRIM(CONCAT(u.first_name, ' ', u.last_name)) AS assignedto
		FROM
			vtiger_notes n
			INNER JOIN vtiger_senotesrel nr ON nr.notesid=n.notesid
			INNER JOIN vtiger_crmentity crmen on crmen.crmid=n.notesid
			INNER JOIN vtiger_seattachmentsrel ar ON ar.crmid=n.notesid
			INNER JOIN vtiger_crmentity crmea on crmea.crmid=ar.attachmentsid
			LEFT JOIN vtiger_users u ON u.id=crmen.smownerid
		WHERE
			crmen.deleted=0 AND
			crmea.deleted=0 AND
			nr.crmid=?",
		array ($focus->id)
	);

	if (($result) && ($adb->num_rows ($result) > 0)) {
		$notes = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$notes [] = $row;
		}
	} else {
		$notes = null;
	}

	$smarty->assign ('RELATED_NOTES', $notes);
	$smarty->display ('modules/repercusiones_prensa/EditView.tpl');
