<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');

	global $adb;

	try {
		$moduleName = PlatzillaUtils::purify ($_GET, 'modulename');
		$data       = array (
			'contacts'  => DataSharingUtils::hasAvailableContacts ($adb),
			'customers' => DataSharingUtils::hasAvailableCustomers ($adb),
			'rules'     => DataSharingUtils::fetchAvailableRules ($adb, $moduleName),
		);
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($data);
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();
