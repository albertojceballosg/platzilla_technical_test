<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	global $adb, $currentModule;

	$centro = PlatzillaUtils::purify ($_REQUEST, 'centro');
	if ($centro) {
		$whereClause = ' AND e.centro=?';
		$arguments   = array ($centro);
	} else {
		$whereClause = '';
		$arguments   = array ();
	}

	$result = $adb->pquery (
		"SELECT
				e.*,
				crme.*
			FROM
				vtiger_espacios e
				INNER JOIN vtiger_crmentity crme ON crme.crmid=e.espaciosid
			WHERE
				crme.deleted=0
				{$whereClause}",
		$arguments
	);
	if (($result) && ($adb->num_rows ($result))) {
		$availableLocations = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$availableLocations [] = $row;
		}
	} else {
		$availableLocations = null;
	}

	header ('Content-Type: application/json');
	echo json_encode ($availableLocations);
	exit ();
