<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/answers/handlers/SurveyProcessing.class.php');
	require_once ('modules/answers/lib/AnswerHelperUtils.class.php');
	require_once ('modules/questionnaire/handlers/Question.class.php');
	
	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
	
	$idAskingFor = PlatzillaUtils::purify ($_REQUEST, 'askingforid');
	
	try {
		if (empty ($record)) {
			throw new Exception ('Uoops! algo salio mal');
		}
		
		$exportData = AnswerHelperUtils::getExportOpenQuestionData ($adb, $record, $idAskingFor, 100, 'ARRAY');
		$askingFor  = Question::getInstance ($adb)->getQuestionById ($record, $idAskingFor);
		
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ASKING_FOR', $askingFor);
		$smarty->assign ('DATA', $exportData);
		$smarty->assign ('HEADER', AnswerHelperUtils::HEADER_RECORD ['OPEN']);
		$html      = $smarty->fetch ('modules/answers/Answer_options/survey_open_question.tpl');
		$pdfSerial = 'answeres_' . rand (100, 1000) .'.pdf';
		if (!$html) {
			throw new Exception ('Uoops! imposible exportar PDF');
		}
		
	} catch (Exception $e) {
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => $e->getMessage()));
	}
