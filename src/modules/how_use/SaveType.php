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

	$description  = PlatzillaUtils::purify ($_POST, 'description');
	$name         = PlatzillaUtils::purify ($_POST, 'name');
	$record       = PlatzillaUtils::purify ($_POST, 'record', null);
	$returnAction = PlatzillaUtils::purify ($_POST, 'return_action');
	$returnModule = PlatzillaUtils::purify ($_POST, 'return_module');
	$sectorId     = PlatzillaUtils::purify ($_POST, 'company_sector');

	try {
		if (empty ($name)) {
			throw new Exception ('Imposible guardar el Tipo de empresa, información incompleta!');
		}

		$sector = CompanyType::getInstance ()
			->setName ($name)
			->setDescription ($description)
			->setId ($record);
		HowToUseHelper::saveCompanyType ($adb, $sector);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (!empty($record)) ? 'Se ha actualizado tipo de empresa' : 'Se ha creado el tipo de empresa!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module={$returnModule}&action={$returnAction}&tab=company_type&parenttab=Settings");
