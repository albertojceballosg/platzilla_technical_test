<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/diagnostic_report_builder/lib/DiagnosticReportBuilderHelper.class.php');
	
	global $adb, $app_strings, $current_user, $mod_strings, $theme;
	
	$isInstance = !empty ($_SESSION ['platInstancia']);
    $platform   = $_SESSION ['plat'];
	$smarty     = new vtigerCRM_Smarty ();
	if (!is_admin ($current_user)) {
		$smarty->assign ('APP', $app_strings);
		$smarty->assign ('ICON_URL', vtiger_imageurl ('denied.gif', $theme));
		$smarty->display ('AccessDenied.tpl');
		exit ();
	}
	try {
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$record = PlatzillaUtils::purify ($_GET, 'record', null);

        $selectedTopic       = null;
        $topics              = null;
        $questionnaireTopics = null;
		$drb           = DiagnosticReportBuilderHelper::getInstance ($adb, $_SESSION['plat']);
		if (!empty ($record)) {
			$diagnosticBuilder   = DiagnosticReportBuilderHelper::getInstance ($adb, $platform)->getDiagnosticReportById ($record);
			$questions           = DiagnosticReportBuilderHelper::getInstance ($adb, $_SESSION['plat'])->fetchQuestionsByQuestionnaire ($diagnosticBuilder->getQuestionnaireId ());
            $questionnaireTopics = $drb->getTopicsFromQuestionnaire ($diagnosticBuilder->getQuestionnaireId ());
			$fieldIds            = array ();
			foreach ($diagnosticBuilder->getReportsToAnswer () as $reportToAnswer) {;
			    if ($reportToAnswer->getElementType() == DiagnosticReportBuilderInterface::ELEMENT_TYPE_MANAGEMENT_LEVEL) {
                    $selectedTopic = DiagnosticReportBuilderInterface::ELEMENT_TYPE_SELECTED_TOPIC;
                    $ranges        = json_decode ($reportToAnswer->getResult(), true);
                    $theTopic      = $reportToAnswer->getAnswerName();
                    foreach ($ranges ['name'] as $rangeName) {
                        $topics [] = "{$theTopic}-{$rangeName}";
                    }
                }
				$fieldId = $drb->getHtmlBlock ($smarty, $reportToAnswer, $questions);
				if (!empty ($fieldId)) {
					$fieldIds [] = $fieldId;
				}
			}
		}
		
		$smarty->assign ('DIAGNOSTIC_BUILDER', (isset($diagnosticBuilder)) ? $diagnosticBuilder : null);
		$smarty->assign ('DINAMIC_TEXT_IDS', (isset($fieldIds) && count($fieldIds)) ? $fieldIds : null);
		$smarty->assign ('ELEMENT_TYPE',DiagnosticReportBuilderInterface::ELEMENT_TYPE);
		$smarty->assign ('IS_INSTANCE', $isInstance);
        $smarty->assign ('ID', $record);
		$smarty->assign ('JOIN_CONDITIONS', DiagnosticReportBuilderInterface::JOIN_CONDITIONS);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('QUESTIONS', (isset($questions)) ? $questions : null);
		$smarty->assign ('QUESTIONNAIRE', $drb->fetchQuestionnaires ());
		$smarty->assign ('QUESTIONNAIRE_TOPICS', $questionnaireTopics);
		//$smarty->assign ('QUESTIONS', $drb->fetchQuestionsByQuestionnaire (527503));
		$smarty->assign ('REPORT_CURRENT_STATUS', ReportBuilderCurrentStatus::IMAGE_CURRENT_STATUS);
		$smarty->assign ('REPORT_BLOCKS',DiagnosticReportBuilderInterface::REPORT_BLOCKS);
        $smarty->assign ('TOPICS', (!empty ($questionnaireTopics) && empty ($topics)) ? $questionnaireTopics : $topics);
        $smarty->assign ('SELECTED_TOPIC', $selectedTopic);

		$smarty->display ('modules/diagnostic_report_builder/EditView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=work_views&action=index&parenttab=Settings');
		$smarty->display ('Message.tpl');
	}