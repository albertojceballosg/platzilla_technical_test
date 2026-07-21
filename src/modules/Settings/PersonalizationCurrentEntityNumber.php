<?php

	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb;

	$id_entity_number = PlatzillaUtils::purify ($_POST, 'id_entity_number');
	$prefix           = PlatzillaUtils::purify ($_POST, 'prefix');
	$sequence         = PlatzillaUtils::purify ($_POST, 'sequence');
	$current_sequence = PlatzillaUtils::purify ($_POST, 'current_sequence');

	$adb->pquery (
		'UPDATE vtiger_modentity_num SET prefix=?, start_id=?, cur_id=? WHERE num_id=?',
		array ($prefix, $sequence, $current_sequence, $id_entity_number)
	);
	exit ();
