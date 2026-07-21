<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/answers/handlers/SurveyProcessing.class.php');
	require_once ('modules/answers/lib/AnswerHelperUtils.class.php');
	require_once ('modules/questionnaire/handlers/Question.class.php');
	
	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
	
	setBugSnag ($site_URL);
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	if ($function == 'EXPORT_RECORD') {
		$idQuestionnaire = PlatzillaUtils::purify ($_REQUEST, 'questionnaire');
		$idAskingFor     = PlatzillaUtils::purify ($_REQUEST, 'askingfor');
		$isOpenQuestion  = PlatzillaUtils::purify ($_REQUEST, 'open', null);
		
		try {
			if(empty ($idQuestionnaire)) {
				throw new Exception ('Uoops! Cuestionario no identificado');
			} else if(empty($idAskingFor)) {
				throw new Exception('Pregunta no identificada');
			}
			if (empty ($isOpenQuestion)) {
				$exportData = AnswerHelperUtils::getExportRecordData ($adb, $idQuestionnaire, $idAskingFor);
			} else {
				$exportData = AnswerHelperUtils::getExportOpenQuestionData ($adb, $idQuestionnaire, $idAskingFor);
			}
			
			if (!$exportData) {
				throw new Exception('Uoops! ha ocurrido un error');
			}
			$header       = (empty ($isOpenQuestion)) ? AnswerHelperUtils::HEADER_RECORD ['NO_OPEN'] : AnswerHelperUtils::HEADER_RECORD ['OPEN'];
			$columnHeader = '';
			foreach ($header as $title) {
				$columnHeader .= '"' . $title . '"' . "\t";
			}
			$cvsSerial = 'records_answer_' . rand (100, 1000) .'.csv';
			header('Content-type: application/octet-stream');
			header("Content-Disposition: attachment; filename={$cvsSerial}");
			header('Pragma: no-cache');
			header('Expires: 0');
			
			echo ucwords ($columnHeader) . "\n" . $exportData . "\n";
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'EXPORT_QUESTION') {
		$idQuestionnaire = PlatzillaUtils::purify ($_REQUEST, 'questionnaire');
		
		try {
			if(empty ($idQuestionnaire)) {
				throw new Exception ('Uoops! Cuestionario no identificado');
			}
			$exportData = AnswerHelperUtils::getExportQuestionnaireData ($adb, $idQuestionnaire);
			if (!$exportData) {
				throw new Exception('Uoops! ha ocurrido un error');
			}
			$header       = AnswerHelperUtils::HEADER_QUESTIONNAIRE;
			$columnHeader = '';
			foreach ($header as $title) {
				$columnHeader .= '"' . $title . '"' . "\t";
			}
			$cvsSerial = 'questionnaire_answeres_' . rand (100, 1000) .'.csv';
			header ('Content-type: application/octet-stream');
			header ("Content-Disposition: attachment; filename={$cvsSerial}");
			header ('Pragma: no-cache');
			header ('Expires: 0');
			
			echo ucwords ($columnHeader) . "\n" . $exportData . "\n";
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'VALIDATE_TEMPLATE') {
		try {
			$tabid = getTabid ('answers');
			$result = $adb->pquery ('SELECT * FROM vtiger_report2module WHERE tabid = ? AND active = 1', array($tabid));
			$num_rows = $adb->num_rows ($result);
			if ($num_rows > 0) {
				header ('Access-Control-Allow-Origin: *');
				header ('HTTP/1.1 200 OK');
				header ('Content-Type: application/json; charset=utf-8');
				echo json_encode (array('error' => 'OK'));
			} else {
				throw new Exception('Uoops! ha ocurrido un error');
			}
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	} else if ($function == 'SAVE_TEMPLATE') {
		$id    = PlatzillaUtils::purify ($_REQUEST, 'record');
		$photo = PlatzillaUtils::purify ($_REQUEST, 'imgpage');
		try {
			if (empty($photo) || empty($id)) {
				throw new Exception('Uoops! ha ocurrido un error');
			}
			AnswerHelperUtils::saveImages ($adb, $id, 'image/png', $photo);
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	exit();
