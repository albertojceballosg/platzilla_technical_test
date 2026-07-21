<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/platzilla/Managers/PicklistManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/diagnostic_report_builder/lib/DiagnosticReportBuilderHelper.class.php');
	
	global $adb, $current_user, $mod_strings, $theme;
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	if ($function == 'GET-ELEMENT-REPORT') {
		try {
			$reportElement   = PlatzillaUtils::purify ($_POST,   'element');
			$rowId           = PlatzillaUtils::purify ($_POST,   'rowid');
			$questionnaireId = PlatzillaUtils::purify ($_POST,   'reportid');
			if (empty ($reportElement)) {
				throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
			}
			$plm                 = PicklistManager::getInstance ($adb);
			$businessPhase       = $plm->fetchPicklistByName ('business_phase_m');
			$businessType        = $plm->fetchPicklistByName ('business_type_m');
			$destinationCategory = $plm->fetchPicklistByName ('destination_category');
			$reportElement       = strtolower ($reportElement);
			$fieldId             = rand (101, 100001);
			$questions           = DiagnosticReportBuilderHelper::getInstance ($adb, $_SESSION['plat'])->fetchQuestionsByQuestionnaire ($questionnaireId);
			$smarty = new vtigerCRM_Smarty ();
			
			$smarty->assign ('BUSINESS_PHASE', $businessPhase);
			$smarty->assign ('BUSINESS_TYPE', $businessType);
			$smarty->assign ('DESTINATION_CATEGORY', $destinationCategory);
			$smarty->assign ('FIELD_ID', $fieldId);
			$smarty->assign ('ID', $rowId);
			$smarty->assign ('IMAGE_CURRENT_STATUS', DiagnosticReportBuilderInterface::IMAGE_CURRENT_STATUS);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('PROSPECTUS_DATA', DiagnosticReportBuilderInterface::PROSPECTUS_DATA);
			$smarty->assign ('REPORT_CURRENT_STATUS', ReportBuilderCurrentStatus::IMAGE_CURRENT_STATUS);
			$smarty->assign ('REPORT_BLOCKS',DiagnosticReportBuilderInterface::REPORT_BLOCKS);
			$smarty->assign ('QUESTIONS', $questions);
			$smarty->assign ('THEME', $theme);
			$smarty->assign ('TOPICS_OPERATIONS', DiagnosticReportBuilderInterface::TOPICS_OPERATIONS);
			$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
			$smarty->assign ('VALUED_FUNCTIONS', DiagnosticReportBuilderInterface::VALUED_FUNCTIONS);
			$htmlOutput = $smarty->fetch ("modules/diagnostic_report_builder/elements/report_builder_{$reportElement}.tpl");
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK', 'html' => $htmlOutput, 'fieldId' => $fieldId));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	} else if ($function == 'GET-QUESTION-BLOCK') {
		try {
			$questionnaireId = PlatzillaUtils::purify ($_POST,   'record', null);
			$reportId        = PlatzillaUtils::purify ($_POST,   'reportid');
            $relatedTopics   = PlatzillaUtils::purify ($_POST,   'topics', null);
			if (empty ($questionnaireId)) {
				throw new Exception ('Algo salió mal, por favor inténtelo mas tarde');
			}
			$drbHelper = DiagnosticReportBuilderHelper::getInstance ($adb, $_SESSION['plat']);
            $selectedTopic = null;
			if (empty($relatedTopics)) {
			    $topics = $drbHelper->getTopicsFromQuestionnaire ($questionnaireId);
            } else {
			    $topics        = explode (';', $relatedTopics);
			    $selectedTopic = DiagnosticReportBuilderInterface::ELEMENT_TYPE_SELECTED_TOPIC;
            }

			$questions = $drbHelper->fetchQuestionsByQuestionnaire ($questionnaireId);
			$smarty    = new vtigerCRM_Smarty ();
			$smarty->assign ('ELEMENT_TYPE',DiagnosticReportBuilderInterface::ELEMENT_TYPE);
			$smarty->assign ('ID', $reportId);
			$smarty->assign ('IS_INSTANCE', $isInstance);
			$smarty->assign ('JOIN_CONDITIONS', DiagnosticReportBuilderInterface::JOIN_CONDITIONS);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('QUESTIONS', $questions);
            $smarty->assign ('QUESTIONNAIRE_TOPICS', null);
			$smarty->assign ('REPORT_BLOCKS',DiagnosticReportBuilderInterface::REPORT_BLOCKS);
			$smarty->assign ('SELECTED_TOPIC', $selectedTopic);
			$smarty->assign ('TAB_SECTION', DiagnosticReportBuilderInterface::TAB_SECTION);
            $smarty->assign ('TOPICS', $topics);
			$htmlOutput = $smarty->fetch ('modules/diagnostic_report_builder/blocks/QuestionMainBlock.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	}