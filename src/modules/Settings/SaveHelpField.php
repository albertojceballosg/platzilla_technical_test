<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
	
	global $adb;
	
	$description = PlatzillaUtils::purify ($_POST, 'description');
	$fieldName   = PlatzillaUtils::purify ($_POST, 'fieldname');
	$isEditable  = PlatzillaUtils::purify ($_POST, 'iseditable');
	$moduleName  = PlatzillaUtils::purify ($_POST, 'modulename');
	$record      = PlatzillaUtils::purify ($_POST, 'record');
	$status      = PlatzillaUtils::purify ($_POST, 'statushelp');
	$title       = PlatzillaUtils::purify ($_POST, 'title');
	$typeVideo   = PlatzillaUtils::purify ($_POST, 'videotype');
	$video       = PlatzillaUtils::purify ($_POST, 'url');
	
	try {
		HelpSettingsHelper::saveHelpField (
			$adb,
			HelpField::getInstance ()
				->setDescription ($description)
				->setFieldName ($fieldName)
				->setIsEditable ($isEditable)
				->setModuleName ($moduleName)
				->setId ($record)
				->setStatus ($status)
				->setTitle ($title)
				->setUrlVideo ($video)
				->setVideoType ($typeVideo)
		);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha almacenado la información de ayuda',
		);
		header ('Location: index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=fields');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=Settings&action=HelpSettingsFieldEditView&record={$record}&parenttab=Settings&tab=fields");
	}
	exit ();
