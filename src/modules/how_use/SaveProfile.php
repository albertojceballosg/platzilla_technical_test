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
	$howUse       = PlatzillaUtils::purify ($_POST, 'howuse');
	$name         = PlatzillaUtils::purify ($_POST, 'name');
	$phaseIds     = PlatzillaUtils::purify ($_POST, 'phase');
	$profileTab   = PlatzillaUtils::purify ($_POST, 'profiletab');
	$sectorIds    = PlatzillaUtils::purify ($_POST, 'sector');
	$status       = PlatzillaUtils::purify ($_POST, 'status');
	$typeIds      = PlatzillaUtils::purify ($_POST, 'type');

	$record       = PlatzillaUtils::purify ($_POST, 'record', null);
	$returnAction = PlatzillaUtils::purify ($_POST, 'return_action');
	$returnModule = PlatzillaUtils::purify ($_POST, 'return_module');

	try {
		if (
			!count ($howUse) ||
			!count ($profileTab) ||
			!count ($phaseIds) ||
			!count ($sectorIds) ||
			!count ($typeIds) ||
			(count ($howUse) != count ($profileTab))
		) {
			throw new Exception ('Imposible guardar el perfil, información del incompleta');
		}

		$totalTab     = count ($profileTab);
		$relatedNames = array ();
		for ($k = 0; $k < $totalTab; $k++) {
			$relatedNames[ $profileTab[ $k ] ] = $howUse[ $k ];
		}

		$profile= ProfilesHowToUse::getInstance ()
			->setId ($record)
			->setName ($name)
			->setDescription ($description)
			->setCompanyPhase ($phaseIds)
			->setCompanySector ($sectorIds)
			->setCompanyType ($typeIds)
			->setHowToUse (null)
			->setStatus ($status);

		HowToUseHelper::saveHowToUseProfile ($adb, $profile, $relatedNames);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (!empty ($record)) ? 'Se ha actualizado el perfil de uso' : 'Se ha creado el perfil de uso!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module={$returnModule}&action={$returnAction}&tab=profile_use&parenttab=Settings");
