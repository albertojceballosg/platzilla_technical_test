<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');

	global $adb;

	$record    = PlatzillaUtils::purify ($_POST, 'record');
	$arguments = array (
		'id'                   => $record,
		'applicationid'        => PlatzillaUtils::purify ($_POST, 'applicationid'),
		'articles'             => PlatzillaUtils::purify ($_POST, 'articles'),
		'moredescription'      => PlatzillaUtils::purify ($_POST, 'moredescription'),
		'moreurl'              => PlatzillaUtils::purify ($_POST, 'moreurl'),
		'questions'            => PlatzillaUtils::purify ($_POST, 'questions'),
		'questionsdescription' => PlatzillaUtils::purify ($_POST, 'questionsdescription'),
		'tips'                 => PlatzillaUtils::purify ($_POST, 'tips'),
		'tipsdescription'      => PlatzillaUtils::purify ($_POST, 'tipsdescription'),
		'tutorialsdescription' => PlatzillaUtils::purify ($_POST, 'tutorialsdescription'),
		'videos'               => PlatzillaUtils::purify ($_POST, 'videos'),
	);

	try {
		HelpSettingsHelper::saveHelpItem ($adb, $arguments);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha almacenado la información de ayuda',
		);
		header ('Location: index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $arguments,
		);
		header ("Location: index.php?module=Settings&action=HelpSettingsEditView&record={$record}&parenttab=Settings");
	}
	exit ();