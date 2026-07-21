<?php
	require_once ('include/utils/VtlibUtils.php');

	$_SESSION ['tipopago'] = isset ($_REQUEST ['tipopago']) ? vtlib_purify ($_REQUEST ['tipopago']) : null;
	echo json_encode ($_SESSION ['tipopago']);
	exit ();
