<?php
	require ('config.inc.php');
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/answers/answers.php');
	require_once ('modules/answers/lib/AnswerHelperUtils.class.php');
	require_once ('modules/diagnostic_report/lib/DiagnosticReportHelper.php');
	require_once ('modules/questionnaire/handlers/Question.class.php');
 
	global $adb, $app_strings, $application_unique_key, $dbconfig, $mod_strings, $site_URL, $theme, $platPrincipal;
	
	$idQuestionnaire = PlatzillaUtils::purify ($_REQUEST, 'record');

	$userName        = PlatzillaUtils::purify ($_REQUEST, 'username', null);
	$email           = PlatzillaUtils::purify ($_REQUEST, 'email', null);
	$phone           = PlatzillaUtils::purify ($_REQUEST, 'phone', null);
	$reference       = PlatzillaUtils::purify ($_REQUEST, 'referen', null);
	$stage           = PlatzillaUtils::purify ($_REQUEST, 'businessStage');
	$survey          = PlatzillaUtils::purify ($_REQUEST, 'survey');
	$surveyToken     = PlatzillaUtils::purify ($_REQUEST, 'surveytoken');
	$type            = PlatzillaUtils::purify ($_REQUEST, 'businessType');
	
	try {
		if (empty ($idQuestionnaire)) {
			throw new Exception ('Uoops! Cuestionario no ha sido encontrado');
		}
		
		if (empty ($survey)) {
			throw new Exception ('Uoops! algo ha salido mal, Por favor contacte al administrador...');
		}
		
		$surveyData = Question::getInstance ($adb)->fetchSurveyDataFromToken ($surveyToken);
		if (empty ($surveyData)) {
			throw new Exception ('Uoops! algo ha salido mal, Por favor contacte al administrador...');
		}
		if (!empty ($surveyData['code'])) {
			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($surveyData['code']);
		} else {
			$adb = AdbManager::getInstance ()->getMasterAdb ();
		}
		
		$questionnaire     = CRMEntity::getInstance ('questionnaire');
		$questionnaire->id = $idQuestionnaire;
		$questionnaire->retrieve_entity_info ($idQuestionnaire, 'questionnaire');
		
		$answers = CRMEntity::getInstance ('answers');
		$answers->mode = 'create';
		$answers->column_fields = getColumnFields ('answers');
		$qObj           = Question::getInstance ($adb);
		$askingFor      = null;
		$today          = date ('Y-m-d');
		$surveyCod      = 'SUR-' . rand ();
		$resultFeedBack = array ();
		$answersOption  = QuestionInterface::ANSWERS_OPTIONS;
		$answerIds      = array ();
		foreach ($survey as $theAnswers) {
			$selectedValue = 0;
			if (isset ($theAnswers['response'][0]['questionid'])) {
				$questionId  = $theAnswers['response'][0]['questionid'];
				$askingFor   = $qObj->getQuestionById ($idQuestionnaire, $questionId);
			}
			if (!$askingFor instanceof AskingFor) {
				continue;
			}
			$answers->column_fields ['questionnaire']    = $idQuestionnaire;
			$answers->column_fields ['cod_survey']       = $surveyCod;
			$answers->column_fields ['questiongroup']    = $askingFor->getQuestionGroupId ();
			$answers->column_fields ['questionstage']    = $askingFor->getquestionStageId ();
			$answers->column_fields ['question']         = $askingFor->getQuestion ();
			$answers->column_fields ['username']         = $userName;
			$answers->column_fields ['useremail']        = $email;
			$answers->column_fields ['phone']            = $phone;
			$answers->column_fields ['reference']        = $reference;
			$answers->column_fields ['assigned_user_id'] = $questionnaire->column_fields ['assigned_user_id'];
			$answers->column_fields ['createdtime']      = $today;
			$answers->column_fields ['modifiedtime']     = $today;
			$answers->column_fields ['surveydate']       = $today;
			$answers->column_fields ['sequence']         = (intval ($askingFor->getSequence ()) + 1);
			$answers->column_fields ['questionid']       = (intval ($questionId));
			$userAnswer  = null;
			$answerValue = null;
			$isCorrect  = false;
			if (in_array ($askingFor->getQuestionType (), array_keys ($answersOption ['OPEN_QUESTION']))) {
				$userAnswer = $theAnswers['response'][0]['selected'];
				$answerName = $theAnswers['response'][0]['answere-name'];
				$isCorrect  = ($theAnswers['response'][0]['selected'] == $askingFor->getResponseOption ()[0]->getValue ());
			} else if (in_array ($askingFor->getQuestionType (), array_keys ($answersOption ['SIMPLE_SELECTION']))) {
				$answerLabel = null;
				foreach ($theAnswers ['response'] as $thisAnswer) {
					foreach ($askingFor->getResponseOption () as $responseOption) {
						if (($responseOption->getId () == $thisAnswer['answereid']) && isset ($thisAnswer['selected']) && !empty ($thisAnswer['question_name'])) {
							$userAnswer    = $thisAnswer['selected'];
							$selectedValue = (is_numeric ($userAnswer)) ? intval ($userAnswer) : 0;
							$isCorrect     = (is_numeric ($responseOption->getSelected ()));
							$answerName    = $thisAnswer['answere-name'];
						}
						if (empty ($answerLabel) && !empty ($thisAnswer['question_name'])) {
							foreach ($askingFor->getResponseOption () as $response) {
								if ($response->getName () == $thisAnswer['question_name']) {
									$answerLabel = $response->getMainLabel ();
									$answerName  = $thisAnswer['question_name'];
									break;
								}
							}
						}
					}
				}
				$answerValue = $userAnswer;
				$userAnswer  = (!empty($answerLabel)) ? $answerLabel . ' ' . $userAnswer : $userAnswer;
			} else {
				$isCorrect   = 'no aplica';
				$answerName = null;
				foreach ($theAnswers ['response'] as $thisAnswer) {
					foreach ($askingFor->getResponseOption () as $responseOption) {
						if (($responseOption->getId () == $thisAnswer['answereid']) && isset($thisAnswer['selected'])) {
							$selectedValue += (is_numeric ($thisAnswer['selected'])) ? intval ($thisAnswer['selected']) : 0;
							if (!empty($responseOption->getSecondLabel ())) {
								$userAnswer .= $responseOption->getMainLabel () . ' ' . $thisAnswer['selected'] . ' ' . $responseOption->getSecondLabel () . '</br>';
							} else {
								$userAnswer .= $responseOption->getMainLabel () . ' ' . $thisAnswer['selected'] . '</br>';
							}
							$answerValue = (empty($answerValue)) ? $thisAnswer['selected'] : $answerValue . ';' . $thisAnswer['selected'];
							$answerName  = (empty($answerName)) ? $thisAnswer['answere-name'] : "{$answerName}@{$thisAnswer['answere-name']}";
						}
					}
				}
			}
			$answers->column_fields ['answer_name']   = $answerName;
			$answers->column_fields ['useranswer']    = $userAnswer;
			$answers->column_fields ['answervalue']   = $answerValue;
			$answers->column_fields ['correctanswer'] = (!is_bool ($isCorrect)) ? 'no aplica' : (($isCorrect) ? 'si' : 'no');
			
			if (!empty ($askingFor->getQuestionGroupId ())) {
				$feedBackGroup = AnswerHelperUtils::getFeedbackGroup ($adb, $questionId, $selectedValue);
				if (!empty ($feedBackGroup)) {
					$resultFeedBack [] = array(
						'question' => $askingFor->getQuestion (),
						'feedback' => $feedBackGroup,
						'group'    => $askingFor->getQuestionGroupId (),
						'theme'    => $askingFor->getquestionStageId (),
					);
				}
			}
			$askingFor = null;
			$answers->save ('answers');
			$answerData [] = array (
				'surveyCod'       => $surveyCod,
				'answerName'      => $answerName,
				'questionId'      => intval ($questionId),
                'stage'           => $stage,
                'type'            => $type,
			);
		}
		$systemData  = array (
            'application_unique_key' => $application_unique_key,
            'dbconfig'               => $dbconfig,
            'plat'                   => $_SESSION['plat'],
            'platPrincipal'          => $platPrincipal,
        );
        
		DiagnosticReportHelper::createDiagnosticReport ($adb, $systemData, $answerData, $idQuestionnaire);
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('FEDD_BACKS', count ($resultFeedBack) ? $resultFeedBack : null);
		$smarty->assign ('HAS_VIDEO', (!empty ($questionnaire->column_fields ['presentation_video'])) ? true : false);
		$smarty->assign ('QUESTONNAIRE', $questionnaire->column_fields);
		$htmlOutput = $smarty->fetch ('modules/Questionnaires/QuestionsFeddback.tpl');
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => 'OK', 'html' => $htmlOutput, 'url' => ''));
	} catch (Exception $e) {
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => $e->getMessage()));
	}
	exit();
