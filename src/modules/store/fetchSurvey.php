<?php
	require ('config.inc.php');
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/questionnaire/handlers/Question.class.php');
	
	global $adb, $app_strings, $dbconfig, $mod_strings, $site_URL, $theme;
	
	setBugSnag ($site_URL);
	
	$surveyToken = PlatzillaUtils::purify ($_GET, 'surveytoken');
    $business    = PlatzillaUtils::purify ($_GET,'business');
	try {
		if (empty($surveyToken)) {
			throw new Exception ('Cuestionario no definido');
		}
		
		$surveyData = Question::getInstance ($adb)->fetchSurveyDataFromToken ($surveyToken);
		if (empty($surveyData)) {
			throw new Exception ('Cuestionario no definido o ha sido eliminado');
		}
		if (!empty($surveyData['code'])) {
			$adb       = AdbManager::getInstance ()->getTargetInstanceAdb ($surveyData['code']);
			$isChecked = Question::getInstance ($adb)->checkSurvey ($surveyData['crmid']);
			if (!$isChecked) {
				throw new Exception ('Cuestionario no definido o ha sido eliminado');
			}
		} else {
			$adb = AdbManager::getInstance ()->getMasterAdb ();
		}
		$survey = Question::getInstance ($adb)->fetchQuestionnaireToSurvey ($surveyData['crmid']);
		if (empty($survey)) {
			throw new Exception ('No se encontraron preguntas para este cuestionario');
		}

		if (!empty ($business)) {
		    list ($stage, $type) = explode ('@', $business);
        }
		$entity = CRMEntity::getInstance ('questionnaire');
		$entity->id   = $surveyData['crmid'];
		$entity->retrieve_entity_info ($surveyData['crmid'], 'questionnaire');
		$videoType = null;
		if (isset($entity->column_fields['presentation_video']) && !empty($entity->column_fields['presentation_video'])) {
			$pos = strpos($entity->column_fields['presentation_video'], 'youtube');
			if ($pos === false) {
				$videoType = 'VIMEO';
			} else {
				$videoType = 'YOUTUBE';
			}
		}
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ASKING_FOR', $survey);
		$smarty->assign ('QUESTONNAIRE', $entity->column_fields);
		$smarty->assign ('STEPS', count ($survey));
		$smarty->assign ('STAGE', isset ($stage) ? $stage : null);
		$smarty->assign ('TYPE', isset($type) ? $type : null);
		$smarty->assign ('TOKEN', $surveyToken);
		$smarty->assign ('VIDEO_TYPE', $videoType);
		$smarty->display ('modules/Questionnaires/survey/Survey.tpl');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
	}
