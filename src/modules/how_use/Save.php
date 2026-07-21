<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/how_use/lib/HowToUseHelper.php');

	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}

	$defaultView  = PlatzillaUtils::purify ($_POST, 'defaultview');
	$description  = PlatzillaUtils::purify ($_POST, 'description');
	$moduleName   = PlatzillaUtils::purify ($_POST, 'formodule');
	$howUseName   = PlatzillaUtils::purify ($_POST, 'howUseName');
	$isDefault    = PlatzillaUtils::purify ($_POST, 'isdefault');
	$listViewTab  = PlatzillaUtils::purify ($_POST, 'listviewtab');
	$defaultTab   = PlatzillaUtils::purify ($_POST, 'mainmaster');
	$name         = PlatzillaUtils::purify ($_POST, 'name');
	$record       = PlatzillaUtils::purify ($_POST, 'record');
	$returnAction = PlatzillaUtils::purify ($_POST, 'return_action');
	$returnModule = PlatzillaUtils::purify ($_POST, 'return_module');
	$status       = PlatzillaUtils::purify ($_POST, 'status');
	$views        = PlatzillaUtils::purify ($_POST, 'views');

	try {
		if (empty ($moduleName)) {
			throw new Exception ('No se encontró el modulo para la vista!');
		} else if (
			!count ($defaultView) ||
			!count ($listViewTab) ||
			!count ($views) ||
			(count ($defaultView) != count ($listViewTab)) ||
			(count ($defaultView) != count ($views)) ||
			(count ($views) != count ($listViewTab))
		) {
			throw new Exception ('información de vistas incompleta');
		}

		if (empty($howUseName)) {
			$howUseName = HowToUseHelper::getHowToUseName ($name);
		}

		$howUseView = array();
		foreach ($listViewTab as $key => $tab) {
			$howUseView [] = HowUseView::getInstance()
				->setHowUseId ($record)
				->setName ($name)
				->setRelatedId ($defaultView[ $key ])
				->setRelatedViews (json_encode(array ($tab => $views[ $key ])))
				->setMasterView (HowToUseHelper::fetchMasterViewsByName ($adb, $tab));
		}

		$defaultViewObject = DefaultView::getInstance()
			->setHowUseId ($record)
			->setMasterView (HowToUseHelper::fetchMasterViewsByName ($adb, $defaultTab))
			->setModuleName ($moduleName)
			->setUserId ($current_user->id);

		HowToUseManager::getInstance($adb)->saveHowToUse (
			HowToUse::getInstance()
				->setId ($record)
				->setHowUseName ($howUseName)
				->setDefaultView ($defaultViewObject)
				->setDescription ($description)
				->setHowUseView ($howUseView)
				->setDefault (($isDefault) ? true : false)
				->setName ($name)
				->setStatus ($status)
				->setTabName ($moduleName)
		);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (!empty($record)) ? 'Se ha actualizado el modo de uso' : 'Se ha creado el modo de uso!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module={$returnModule}&action={$returnAction}&parenttab=Settings");
