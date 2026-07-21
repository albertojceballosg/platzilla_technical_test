<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/questionnaire/handlers/Question.class.php');
	
	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
	
	setBugSnag ($site_URL);
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	if ($function == 'ASNWERES_OPTIONS') {
		$idRow      = PlatzillaUtils::purify ($_REQUEST, 'sequence');
		$optionType = PlatzillaUtils::purify ($_REQUEST, 'optiontype');
		
		try {
			if(empty ($optionType)) {
				throw new Exception ('Tipo de repuesta no identificada');
			}
			
			if(empty ($idRow) && $idRow != 0) {
				throw new Exception ('Fila de pregunta no identificada');
			}
			$optionType = strtolower ($optionType);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ID', $idRow);
			$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
			$htmlOutput = $smarty->fetch ("modules/Questionnaires/Questions/Question_options/{$optionType}.tpl");
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage()));
		}
	} else if ($function == 'DELETE_QUESTION') {
		try {
			$idQuestion = PlatzillaUtils::purify ($_REQUEST, 'idQuestion');
			if(empty ($idQuestion)) {
				throw new Exception ('ID de pregunta no encontrado!');
			}
			$adb->pquery ('DELETE FROM vtiger_question2answeres WHERE questionid=?', array ($idQuestion));
			$adb->pquery ('DELETE FROM vtiger_question WHERE questionid=?', array ($idQuestion));
			$smarty = new vtigerCRM_Smarty ();
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => 'deleted'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'CHAMGE_NAVI') {
		try {
			$idQuestion = PlatzillaUtils::purify ($_REQUEST, 'record');
			if(empty ($idQuestion)) {
				throw new Exception ('ID de pregunta no encontrado!');
			}
			
			$survey = Question::getInstance ($adb)->fetchQuestionById ($idQuestion);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ASKING_FOR', $survey);
			$smarty->assign ('ANSWERS_OPTIONS', QuestionInterface::ANSWERS_OPTIONS);
			$smarty->assign ('ONLY_OPTIONS', array('SORT_OPTIONS', 'MULTIPLE_CHOICE'));
			$smarty->assign ('QUESTIONNAIRE_ID', $idQuestion);
			$smarty->assign ('TOTAL_QUESTION', (count ($survey) - 1));
		} catch (Exception $e) {
			$smarty->assign ('IS_ERROR', true);
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		}
		$smarty->display ('modules/Questionnaires/survey/SurveyNavi.tpl');
	} else if ($function == 'CHAMGE_RANGES') {
		try {
			$idQuestion = PlatzillaUtils::purify ($_REQUEST, 'record');
			if(empty ($idQuestion)) {
				throw new Exception ('ID de pregunta no encontrado!');
			}
			
			$groupAndTheme = Question::getInstance ($adb)->fetchGroupAndThemes ($idQuestion);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('GROUP_THEME', $groupAndTheme);
			$smarty->assign ('QUESTIONNAIRE_ID', $idQuestion);
			$smarty->assign ('TOTAL_GROUP', (count ($groupAndTheme) - 1));
		} catch (Exception $e) {
			$smarty->assign ('IS_ERROR', true);
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		}
		$smarty->display ('modules/Questionnaires/Questions/GroupAndThemes.tpl');
	} else if ($function == 'SAVE_SURVEY_NAV') {
		try {
			$idQuestion = PlatzillaUtils::purify ($_REQUEST, 'record');
			$arrayNav  = PlatzillaUtils::purify ($_REQUEST, 'nav');
			if(empty ($idQuestion)) {
				throw new Exception ('ID de pregunta no encontrado!');
			}
			$questionObj = Question::getInstance ($adb);
			foreach ($arrayNav as $key => $value) {
				if(empty($key) || empty($value)) {
					continue;
				}
				$surveyNav = SurveyNav::getInstance ()
					->setQuestionnairesId ($idQuestion)
					->setResponseName ($key)->setQuestionId ($value);
				$questionObj->saveSurveyNav ($surveyNav);
			}
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'SAVE_GROUP_THEME') {
		try {
			$idQuestion  = PlatzillaUtils::purify ($_REQUEST, 'record');
			$arrayRanges = PlatzillaUtils::purify ($_REQUEST, 'ranges');
			if(empty ($idQuestion)) {
				throw new Exception ('Cuetionario no identificado!');
			} else if (!count($arrayRanges)) {
				throw new Exception ('Upoos! Algo salio mal');
			}
			
			$questionObj   = Question::getInstance ($adb);
			$groupAndTheme = $questionObj->fetchGroupAndThemes ($idQuestion);
			if (count ($groupAndTheme)) {
				foreach ($groupAndTheme as $group) {
					$myTheme         = $group->getThemeName () . '-' . $group->getQuestionId ();
					$themeProperties = array_keys ($arrayRanges[ $myTheme ]);
					if (count ($group->getRanges())) {
						foreach ($group->getRanges () as $range) {
							$myTheme         = $range->getThemeName () . '-' . $range->getQuestionId ();
							$themeProperties = array_keys ($arrayRanges[ $myTheme ]);
							if (!count ($arrayRanges) || !in_array ('minimum', $themeProperties)) {
								continue;
							}
							$theme     = $arrayRanges[ $myTheme ];
							$forIndex  = array_keys ($theme['minimum']);
							$index     = $forIndex [0];
							$lastIndex = ($forIndex [(count($theme['minimum']) - 1)] + 1);
							for ($k = $index; $k < $lastIndex; $k++) {
								if (empty ($theme ['feedback'][ $k ])) {
									continue;
								}
								$rangeTheme = RangeGroup::getInstance ()
									->setFeedBack ($theme ['feedback'][ $k ])
									->setId (intval ($theme ['ID'][ $k ]))
									->setMaximum (intval ($theme ['maximum'][ $k ]))
									->setMinimum (intval ($theme ['minimum'][ $k ]))
									->setQuestionId ($theme ['idquestion'][ 0 ])
									->setThemeName ($range->getThemeName ());
								$questionObj->saveRange ($rangeTheme);
								unset ($rangeTheme);
							}
							unset ($theme);
							unset ($totalThemes);
							unset ($myTheme);
						}
					} else if (count ($arrayRanges) || in_array ('minimum', $themeProperties)) {
						$theme     = $arrayRanges[ $myTheme ];
						$forIndex  = array_keys ($theme['minimum']);
						$index     = $forIndex [0];
						$lastIndex = ($forIndex [(count($theme['minimum']) - 1)] + 1);
						for ($k = $index; $k < $lastIndex; $k++) {
							if (empty ($theme ['feedback'][ $k ])) {
								continue;
							}
							$rangeTheme = RangeGroup::getInstance ()
								->setFeedBack ($theme ['feedback'][ $k ])
								->setId (null)
								->setMaximum (intval ($theme ['maximum'][ $k ]))
								->setMinimum (intval ($theme ['minimum'][ $k ]))
								->setQuestionId ($theme ['idquestion'][ 0 ])
								->setThemeName ($group->getThemeName ());
							$questionObj->saveRange ($rangeTheme);
							unset ($rangeTheme);
						}
						unset ($theme);
						unset ($totalThemes);
						unset ($myTheme);
					}
				}
			}
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'DELETE_RANGE') {
		try {
			$idRange = PlatzillaUtils::purify ($_REQUEST, 'record');
			if(empty ($idRange)) {
				throw new Exception ('ID de rango no encontrado!');
			}
			$adb->pquery ('DELETE FROM vtiger_question2group_range WHERE grouptorangeid=?', array ($idRange));
			$smarty = new vtigerCRM_Smarty ();
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => 'deleted'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	exit();
