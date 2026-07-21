<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	global $adb;

	$cliente = PlatzillaUtils::purify ($_REQUEST, 'cliente');

	if ($cliente) {
		$selectClause = 'c.*,';
		$joinClause  = 'INNER JOIN vtiger_clientes c ON c.clientesid=u.cliente INNER JOIN vtiger_crmentity crmec ON crmec.crmid=c.clientesid';
		$whereClause = ' AND crmec.deleted=0 AND c.clientesid=?';
		$arguments   = array ($cliente);
	} else {
		$whereClause = '';
		$arguments   = array ();
	}

	$result = $adb->pquery (
		"SELECT
				u.*,
				{$selectClause}
				crmeu.*
			FROM
				vtiger_usuarios_colladito u
				INNER JOIN vtiger_crmentity crmeu ON crmeu.crmid=u.usuarios_colladitoid
				{$joinClause}
			WHERE
				crmeu.deleted=0
				{$whereClause}",
		$arguments
	);
	if (($result) && ($adb->num_rows ($result) > 0)) {
		$availableUsers = array ();
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$availableUsers [] = $row;
		}
	} else {
		$availableUsers = null;
	}

	header ('Content-Type: application/json');
	echo json_encode ($availableUsers);
	exit ();
