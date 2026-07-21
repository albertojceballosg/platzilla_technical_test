<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/questionnaire/handlers/Question.class.php');

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
	
	try {
		if (empty ($name)) {
			throw new Exception ('Imposible guardar el fundamento, información incompleta!');
		}

		$stage = QuestionannaireStages::getInstance ()
			->setName ($name)
			->setDescription ($description)
			->setId ($record);
		Question::getInstance ($adb)->saveQuestionStage ($stage);
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (!empty($record)) ? 'Se ha actualizado el fundamento' : 'Se ha creado el fundamento!',
		);
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
	header ("Location: index.php?module={$returnModule}&action={$returnAction}&tab=stages&parenttab=Settings");
