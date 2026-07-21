<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/okrs/lib/OkrHelperUtils.php');
	
	global $adb, $currentModule, $mod_strings, $theme, $site_URL;
	
	setBugSnag ($site_URL);
	$description  = PlatzillaUtils::purify ($_POST, 'description');
	$frequency    = PlatzillaUtils::purify ($_POST, 'frequency');
	$goalValue    = PlatzillaUtils::purify ($_POST, 'goal_value');
	$objectiveId  = PlatzillaUtils::purify ($_POST, 'objectiveid');
	$record       = PlatzillaUtils::purify ($_POST, 'record');
	$returnAction = PlatzillaUtils::purify ($_POST, 'return_action', 'EditViewKeyResult');
	$returnModule = PlatzillaUtils::purify ($_POST, 'return_module', $currentModule);
	$status       = PlatzillaUtils::purify ($_POST, 'status');
	$selectedTab  = PlatzillaUtils::purify ($_POST, 'tab', 'key_results');
	
	try {
		$objective = OkrHelperUtils::getInstance ()->getObjectiveById ($objectiveId, true);
		$keyResult = KeyResults::getInstance ()
			->setId ($record)
			->setCompanyArea ($objective->getCompanyArea ())
			->setDescription ($description)
			->setFrequency ($frequency)
			->setGoalValue (intval ($goalValue))
			->setObjectiveId ($objectiveId)
			->setStatus ($status)
			->setValueActual (1);
		
		OkrHelperUtils::getInstance ()->saveKeyResult (array ($keyResult));
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (!empty ($record)) ? '¡Se ha actualizado el resultado clave!' : '¡Se ha creado el resultado clave!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module={$returnModule}&action={$returnAction}&objective={$objectiveId}&record={$record}&parenttab=Settings");