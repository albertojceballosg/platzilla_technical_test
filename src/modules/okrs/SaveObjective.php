<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/okrs/lib/OkrHelperUtils.php');
	
	global $adb, $currentModule, $mod_strings, $theme, $site_URL;
	
	setBugSnag ($site_URL);
	$companyArea  = PlatzillaUtils::purify ($_POST, 'companyarea');
	$companyPhase = PlatzillaUtils::purify ($_POST, 'companyphase');
	$companyType  = PlatzillaUtils::purify ($_POST, 'companytype');
	$frequency    = PlatzillaUtils::purify ($_POST, 'frequency');
	$howToDo      = PlatzillaUtils::purify ($_POST, 'howtodo');
	$isOnBoarding = PlatzillaUtils::purify ($_POST, 'onboarding');
	$record       = PlatzillaUtils::purify ($_POST, 'record');
	$returnAction = PlatzillaUtils::purify ($_POST, 'return_action', 'index');
	$returnModule = PlatzillaUtils::purify ($_POST, 'return_module', $currentModule);
	$status       = PlatzillaUtils::purify ($_POST, 'status');
	$selectedTab  = PlatzillaUtils::purify ($_POST, 'tab', 'objectives');
	$toDo         = PlatzillaUtils::purify ($_POST, 'todo');
	
	try {
		$objective = OkrsObjectives::getInstance ()
			->setId ($record)
			->setCompanyArea ($companyArea)
			->setCompanyPhases ($companyPhase)
			->setCompanyTypes ($companyType)
			->setFrequency ($frequency)
			->setHowToDo ($howToDo)
			->setIsOnBoarding ($isOnBoarding)
			->setStatus ($status)
			->setToDo ($toDo);
		
		OkrHelperUtils::getInstance ()->saveObjective ($objective);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (!empty ($record)) ? 'Se ha actualizado el objectivo' : 'Se ha creado el objectivo!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module={$returnModule}&action={$returnAction}&parenttab=Settings");