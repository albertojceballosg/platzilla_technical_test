<?php
	require_once ('include/platzilla/Managers/ButtonManager.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/CreateCustomButtonHelper.class.php');

	global $adb;

	$backgroundTaskAction = SettingsUtils::purify ($_REQUEST, 'backgroundtaskaction');
	$clickAction          = SettingsUtils::purify ($_REQUEST, 'clickaction');
	$description          = SettingsUtils::purify ($_REQUEST, 'description');
	$isActive             = SettingsUtils::purify ($_REQUEST, 'active');
	$linkAction           = SettingsUtils::purify ($_REQUEST, 'linkaction');
	$moduleName           = SettingsUtils::purify ($_REQUEST, 'modulo');
	$recordId             = SettingsUtils::purify ($_REQUEST, 'record');
	$runInNewWindow       = SettingsUtils::purify ($_REQUEST, 'runinnewwindow', 0);
	$style                = SettingsUtils::purify ($_REQUEST, 'styleButton');
	$title                = SettingsUtils::purify ($_REQUEST, 'title');
	$type                 = SettingsUtils::purify ($_REQUEST, 'type');
	$viewName             = SettingsUtils::purify ($_REQUEST, 'vista');
	$faIcon               = SettingsUtils::purify ($_REQUEST, 'faIcon');

	$filterData = array (
		'filterField'     => PlatzillaUtils::purify ($_REQUEST, 'filterField'),
		'filterOperator'  => PlatzillaUtils::purify ($_REQUEST, 'filterOperator'),
		'filterValue'     => PlatzillaUtils::purify ($_REQUEST, 'filterValue'),
		'filterJoin'      => PlatzillaUtils::purify ($_REQUEST, 'filterJoin'),
		'filterGroupJoin' => PlatzillaUtils::purify ($_REQUEST, 'conditionGroups'),
		'indexGrupo'      => PlatzillaUtils::purify ($_REQUEST, 'indexGrupo'),
		'moduleFilter'    => $moduleName,
	);

	$isInstance = !empty ($_SESSION ['platInstancia']);

	if ($type == 'backgroundtask') {
		$taskName   = sha1 ($backgroundTaskAction);
		$linkAction = "/index.php?module=backgroundtasks&action=RunTask&Ajax=true&taskname={$taskName}&record=[record]&return_module=[module]&return_action=[action]&return_record=[record]";
		$type       = 'link';
	}

	if ($type == ButtonInterface::TYPE_JAVASCRIPT) {
		$onClick = $clickAction;
		$link    = null;
	} else {
		$onClick = null;
		$link    = $linkAction;
	}

	$sqlFilter = CreateCustomButtonHelper::getSqlFilter ($adb, $filterData);

	$button   = ButtonManager::getInstance ($adb)->saveButton (
		Button::getInstance ()
			->setAction ($type == ButtonInterface::TYPE_JAVASCRIPT ? $clickAction : $linkAction)
			->setArrayVisibility ($filterData)
			->setDescription ($description)
			->setId ($recordId)
			->setIsActive ($isActive == 1)
			->setLabel ($title)
			->setLocation ($viewName)
			->setLocked ($isInstance)
			->setModuleName ($moduleName)
			->setRunInNewWindow ($runInNewWindow == 1)
			->setSqlVisibility ($sqlFilter)
			->setStyle ($style)
			->setType ($type)
			->setFaIcon ($faIcon)
	);
	$buttonId = $button->getId ();

	if (!empty ($buttonId)) {
		header ("Location: index.php?module=Settings&action=DetailCustomButtons&parenttab=Settings&record={$buttonId}");
	} else {
		header ('Location: index.php?module=Settings&action=CustomButtons&parenttab=Settings');
	}
	exit ();
