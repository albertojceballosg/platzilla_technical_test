<?php
	require_once ('data/CRMEntity.php');
    require_once ('include/platzilla/Managers/PlatformManager.php');
    require_once ('include/utils/AdbManager.class.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('modules/diagnostic_report/Objects/BusinessDestination.php');
    require_once ('modules/diagnostic_report/Objects/ValuedFunctions.php');
	require_once ('modules/questionnaire/handlers/Question.class.php');
	require_once ('modules/questionnaire/Objects/QuestionInterface.class.php');
	require_once ('modules/diagnostic_report_builder/lib/DiagnosticReportBuilderHelper.class.php');
    require_once ('modules/store/lib/StoreUtils.class.php');
	
	abstract  class DiagnosticReportHelper {

	    protected static $topicsValue = array ();

	    protected static $processedQuestion = array ();

	    /** @var ValuedFunctions[] */
	    protected static $valuedFunctions = array ();
        
        /**
         * @param PearDatabase $adb
         * @param array $systemData
         * @param array $diagnosticReport
         *
         * @return null|PlatformInstance
         * @throws PlatformException
         */
        private static function assignInstance ($adb, $systemData, $diagnosticReport) {
            if (!count ($diagnosticReport) || empty ($diagnosticReport['email_response'])) {
                return null;
            }
            if (filter_var ($diagnosticReport ['email_response'], FILTER_VALIDATE_EMAIL) === false) {
                return null;
            }
            
            $dummy  = explode ('@', $diagnosticReport['email_response']);
            $result = getmxrr ($dummy [1], $mxhosts);
            if ((empty ($result)) || (empty ($mxhosts))) {
                return null;
            }
            
            $dbconfig      = $systemData ['dbconfig'];
            $platPrincipal = $systemData ['platPrincipal'];
            $masterAdb     = ($adb->dbName != $dbconfig['db_name']) ? AdbManager::getInstance ()->getMasterAdb () : $adb;
            $pm            = PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers']);
            if ($pm->userHasInstance ($diagnosticReport['email_response'])) {
	            throw new Exception ('Ya estás registrado en Platzilla', 400);
            }
            if (!empty ($diagnosticReport['prospect_name'])) {
                list ($firstName, $lastName) = explode (' ', $diagnosticReport['prospect_name'], 2);
            } else {
                $firstName = $dummy[0];
                $lastName  = (!empty($diagnosticReport['business_name'])) ? $diagnosticReport['business_name'] : $dummy[1];
            }
            $password      = (!empty($diagnosticReport['prospect_password'])) ? $diagnosticReport['prospect_password'] : StoreUtils::randomPassword (4, true);
            $administrator = User::getInstance ()
                ->setAdministrator (true)
                ->setEmail ($diagnosticReport ['email_response'])
                ->setFirstName ($firstName)
                ->setLastName ($lastName)
                ->setPlainPassword ($password)
                ->setDefaultModuleName('Home')
                ->setDefaultOperating ('DIRECTION_MODE')
                ->setDefaultHomeTab ('ACTIVITY')
                ->setUserName ($diagnosticReport['email_response']);
            $instance = PlatformManager::getInstance ($masterAdb, $dbconfig ['db_serverForNewUsers'])->assignInstance ($platPrincipal, $diagnosticReport['email_response'], $administrator);
            self::updateContactAndInstance ($adb, $instance, $systemData, $diagnosticReport);
	        
			require_once ('modules/emailmanager/emailmanager.php');
			emailmanager::getInstance ($masterAdb, $platPrincipal)->addSender (
				'Platzilla',
				'no_reply@platzilla.com'
			)->send (
				$diagnosticReport ['email_response'],
				'es',
				'WELCOME_PLATZILLA',
				array (
					'NOMBRE_DE_EMPRESA'  => $lastName,
					'CORREO_ELECTRONICO' => $diagnosticReport ['email_response'],
					'CONTRASENA'         => $password,
				)
			);
            return $instance;
        }
        
        /**
         * @param integer $crmId
         * @param PlatformInstance $instance
         * @param array $systemData
         * @param array $diagnosticReport
         *
         * @return void
         */
        private static function AuthenticateUser ($crmId, $instance, $systemData, $diagnosticReport) {
            try {
                if (empty($crmId) || !$instance instanceof PlatformInstance) {
                    return;
                }
                global $site_URL;
                $GLOBALS ['adb'] = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
            		// Autenticando usuario
                $focus                              = new Users ();
                $focus->column_fields ['user_name'] = $diagnosticReport ['email_response'];
                $focus->retrieve_entity_info (1, 'Users');
            
                $_SESSION ['authenticated_user_menu']          = array ();
                $_SESSION ['is_authenticated']                 = 1;
                $_SESSION ['authenticated_user_id']            = $focus->id;
                $_SESSION ['app_unique_key']                   = $systemData['application_unique_key'];
                $_SESSION ['plat']                             = $instance->getCode ();
                $_SESSION ['platInstancia']                    = $instance->getCode (); // servirá para determinar ls bd correcta en login
                $_SESSION ['vtiger_authenticated_user_theme']  = 'centaurus';
                $_SESSION ['authenticated_user_language']      = $focus->column_fields['language'];
                unset ($_SESSION['briefing']);
                create_tab_data_file ();
                create_parenttab_data_file ();
                createUserPrivilegesfile ($focus->id);
                createUserSharingPrivilegesfile ($focus->id);
	            unset ($_SESSION ['flashmessage']);
                $url = $site_URL . 'index.php?module=diagnostic_report&parenttab=&action=DetailView&record=' . $crmId;
                header ('HTTP/1.1 200 OK');
                header ('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('error' => 'OK', 'html' => '', 'url' => $url));
            } catch (Exception $e) {
                $statusCode = !empty ($e->getCode ()) ? $e->getCode () : 500;
                switch ($statusCode) {
                    case 400:
                        $statusMessage = 'Bad request';
                        break;
                    case 401:
                        $statusMessage = 'Access denied';
                        break;
                    default:
                        $statusMessage = 'Internal server error';
                        break;
                }
            	header ("HTTP/1.1 {$statusCode} {$statusMessage}");
            	header ('Content-Type: application/json');
            	echo json_encode ($e->getMessage ());
            }
            exit();
        }
        
        /**
		 * @param DiagnosticReportToAnswer[] $blocks
		 * @param array $answerData
		 * @param integer $blockIndex
         * @param array $group
		 *
         * @return array
		 */
		private static function checkAnswersUser ($blocks, $answerData, $blockIndex, $group) {
			$totalBlocks  = count ($blocks);
			$totalAnswers = count ($answerData);
			$result       = false;
			$elementType  = $blocks [$blockIndex]->getElementType();
			for ($j = 0; $j < $totalAnswers; $j++) {
				$isKnown = false;
				$index   = 0;
				for ($k = 0; $k < $totalBlocks; $k++) {
                    $questionToId = "{$blocks[$k]->getQuestionId()}-{$blocks[$k]->getDiagnosticReportId()}";
				    if (
				        (
				            $blocks [$k]->getElementType() != $elementType && (!empty ($blocks [$k]->getElementType())) ||
                            (isset(self::$processedQuestion[ $blocks[$k]->getReportBlock() ])  && in_array ($questionToId, self::$processedQuestion[ $blocks[$k]->getReportBlock() ]))
                        ) ||
                        (empty ($blocks [$k]->getElementType()) && !in_array ($blocks [$k]->getId(), $group))
                    ) {
				        continue;
                    }
					$dummy = explode ('@', $answerData[$j]['answerName']);
					if (
						$answerData[$j]['questionId'] == $blocks [$k]->getQuestionId () &&
						in_array ($blocks [$k]->getAnswerName (), $dummy)
					) {
						$isKnown = true;
						$index   = $k;
						break;
					} else if (empty ($blocks[$k]->getQuestionId()) && !empty ($blocks[$k]->getHandler())) {
					    $dummyAnswer = explode ('-', $blocks[$k]->getAnswerName());
					    if (isset (self::$topicsValue [$dummyAnswer[0]])) {
					        $range = json_decode ($blocks[$k]->getHandler(), true);
					        if (
					            (self::$topicsValue [$dummyAnswer [0]] >= floatval ($range ['min']))&&
                                (self::$topicsValue [$dummyAnswer [0]] <= floatval ($range ['max']))
                            ) {
                                $isKnown = true;
                                $index   = $k;
                                break;
                            }
                        }
					    unset ($dummyAnswer);
					    unset ($range);
                    }
				}
				unset ($dummy);
				if (
					($totalBlocks == 1 && $isKnown) ||
					($isKnown && ($blocks [$index]->getJoinType () == 'OR')) ||
                    ($isKnown && empty ($blocks [$index]->getJoinType ()))
				) {
					$result = $isKnown;
					break;
				} else if ($index > 0) {
					$join   = ($blocks [($index - 1)]->getJoinType () == 'AND') ? ' AND ' : ' OR ';
					$result = eval ("return (". $result . $join . $isKnown . ");");
				} else {
					$result =  $isKnown;
				}
			}
			return array ('isKnown' => $result, 'index' => $index);
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return BusinessDestination[]|null
		 */
		private static function fetchBusinessDestination ($adb) {
			$result = $adb->query ("SELECT * FROM vtiger_business_destination WHERE 1");
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false))  {
					$businessDestination [] =  BusinessDestination::getInstance ()
						->setBusinessPhase (explode ('|##|', $row ['business_phase_m']))
						->setBusinessType (explode ('|##|', $row ['business_type_m']))
						->setCategories (explode ('|##|', $row ['destination_category']))
						->setCodeDestination ($row ['cod_business_destination'])
						->setDescription ($row ['destination_description'])
						->setDestinationId ($row ['business_destinationid'])
						->setDestinationName ($row ['destination_name'])
						->setEnding ($row ['destination_term'])
						->setEndingUnit ($row ['term_unit']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($businessDestination)) ? $businessDestination : null;
		}
		
        /**
         * @param  DiagnosticReportToAnswer[] $blocks
         * @param integer $blockIndex
         *
         * @return array
         */
		private static function getGroupBlock ($blocks, $blockIndex) {
		    $elementType = $blocks[$blockIndex]->getElementType();
		    $group       = array ();
		    $lastJoinId  = null;
		    $totalBlocks = count($blocks);
		    for ($k = $blockIndex; $k < $totalBlocks; $k++ ) {
		        if(!$blocks[$k] instanceof DiagnosticReportToAnswer) {
		            continue;
                }
                if ($blocks[$k]->getElementType() == $elementType) {
                    $group []   = $blocks[$k]->getId();
                    $lastJoinId = $blocks[$k]->getQuestionJoin();
                } else if (count ($group) && ($blocks[$k]->getQuestionId() == $lastJoinId)) {
                    $group [] = $blocks[$k]->getId();
                    $lastJoinId = $blocks[$k]->getQuestionJoin();
                    if (empty($lastJoinId)) {
                        break;
                    }
                }
            }
		    return $group;
        }

        /**
         * @param DiagnosticReportToAnswer $block
         * @param integer $level
         *
         * @return string|null
         */
        private static function getManagementLevelName ($block, $level) {
            if(!$block instanceof DiagnosticReportToAnswer) {
                return null;
            }
            $ranges          = json_decode ($block->getResult(), true);
            $totalRangeName  = count ($ranges['name']);
            $managementLevel = null;
            for ($k = 0; $k < $totalRangeName; $k++) {
                if (
                    (intval ($level) >= intval ($ranges['min'][$k])) &&
                    (intval ($level) <= intval ($ranges['max'][$k]))
                ) {
                    $managementLevel = $ranges ['name'][$k];
                    break;
                }
            }
            return  $managementLevel;
        }

		/**
		 * @param PearDatabase $adb
		 * @param string $surveyCod
		 * @param integer $questionId
		 * @param string $answerName
		 * @param  $singleAnswer
		 *
         * @return string|null
		 * @throws Exception
		 */
		private static function getResponseFromAnswer ($adb, $surveyCod, $questionId, $answerName, $singleAnswer = false) {
		    if (!$singleAnswer) {
		        $where = "answer_name LIKE '%{$answerName}%'";
            } else {
                $where = "answer_name = '{$answerName}'";
            }

			$result = $adb->pquery (
				"SELECT
						useranswer
					  FROM
						vtiger_answers
					  WHERE
						cod_survey=? AND
						questionid=? AND
						{$where}",
				array ($surveyCod, $questionId)
			);
			
			if ($adb->num_rows ($result) > 0) {
				$row      = $adb->fetchByAssoc ($result, -1, false);
				$response = $row ['useranswer'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($response)) ? $response : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $questionId
		 * @param string $answerName
		 * @param string $surveyCod
		 *
         * @return null
		 * @throws Exception
		 */
		private static function getResponseFromSurvey ($adb, $questionId, $answerName, $surveyCod) {
            $hasResponse = self::getResponseFromAnswer ($adb, $surveyCod, $questionId, $answerName);
            if (empty ($hasResponse)) {
                return '';
            }
			$result = $adb->pquery ('SELECT label_a, label_b FROM vtiger_question2answeres WHERE questionid = ? AND  name = ?',
                array ($questionId, $answerName)
            );

			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$response = $row ['label_a'] . ' ' . $row ['label_b'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($response)) ? trim ($response) : '';
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $answer
		 * @param DiagnosticReportToAnswer $block
		 *
		 * @return string|null
		 * @throws Exception
		 */
		private static function getResponseToDinamicText ($adb, $answer, $block) {
			$variables        = explode ('@', $block->getAttributes ());
			$dynamicVariables = array ();
			foreach ($variables as $variable) {
				list ($answerName, $questionId) = explode ('-', $variable);
				$result = $adb->pquery ('SELECT question_type FROM vtiger_question WHERE questionid=?', array ($questionId));
				if ($adb->num_rows ($result) > 0) {
					$row = $adb->fetchByAssoc ($result, -1, false);
					$questionType = $row ['question_type'];
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (!empty ($questionType) && in_array ($questionType, array_keys (QuestionInterface::ANSWERS_OPTIONS['OPEN_QUESTION']))) {
					$dynamicVariables["[{$variable}]"] = self::getResponseFromAnswer ($adb, $answer['surveyCod'], $questionId, $answerName, true);
				} else if (!empty ($questionType)) {
					$dynamicVariables["[{$variable}]"] = self::getResponseFromSurvey ($adb, $questionId, $answerName, $answer['surveyCod']);
				}
				$questionType = null;
			}
			if (count ($dynamicVariables)) {
				$search   = array_keys ($dynamicVariables);
				$replace  = array_values ($dynamicVariables);
				$response = str_replace ($search, $replace, $block->getResult ());
				return $response;
			}
			return null;
		}

        /**
         * @param PearDatabase $adb
         * @param DiagnosticReportToAnswer $block
         * @param array $answerData
         * @param integer $questionnaireId
         *
         * @return integer|null
         */
        private static function getTopicCalculation ($adb, $block, $answerData, $questionnaireId) {
            if (empty ($block->getAnswerName()) || empty ($answerData) || empty ($questionnaireId)) {
                return null;
            }
            $codSurvey  = $answerData ['surveyCod'];
            $ranges     = json_decode ($block->getResult(), true);
            $operation  = $ranges ['oper'][0];
            $topic      = $block->getAnswerName();
            $topicValue = null;
            $sqlSearch = "SELECT `answer_name` FROM `vtiger_answers` WHERE `cod_survey`= '{$codSurvey}' AND `questionstage` = '{$topic}' AND `answer_name` IS NOT NULL";
            $result    = $adb->query (
                "SELECT {$operation}(`value`) AS value
                    FROM 
                        vtiger_question2answeres qa
                    INNER JOIN vtiger_question qt on qt.questionid = qa.questionid
                    WHERE
                        qt.questionnairesid = {$questionnaireId} AND 
                        name IN ({$sqlSearch})"
            );
            if ($adb->num_rows ($result) > 0) {
                $row = $adb->fetchByAssoc ($result, -1, false);
                $topicValue = $row ['value'];
            }
            DatabaseUtils::closeResult ($result);
            $result = null;
            return  $topicValue;
        }

		/**
		 * @param PearDatabase $adb
		 * @param DiagnosticReportToAnswer $block
		 * @param array $answerData
		 *
		 * @return string|null
		 * @throws Exception
		 */
		private static function getUserResponse ($adb, $block, $answerData ) {
			$totalAnswers = count ($answerData);
			$index        = 0;
			$isKnown      = false;
            if ((!$block->getQuestionId() && !empty ($block->getHandler()))) {
                $isKnown = true;
            } else {
                for ($j = 0; $j < $totalAnswers; $j++) {
                    $dummy = explode('@', $answerData[$j]['answerName']);
                    if (
                        $answerData[$j]['questionId'] == $block->getQuestionId() &&
                        in_array($block->getAnswerName(), $dummy)
                    ) {
                        $index = $j;
                        $isKnown = true;
                        break;
                    }
                    unset ($dummy);
                }
            }
			if (!$isKnown) {
				return null;
			} else if ($block->getElementType () == DiagnosticReportBuilderInterface::ELEMENT_TYPE_DYNAMIC_TEXT) {
				if (empty ($block->getAttributes ())) {
					$response = $block->getResult ();
				} else {
					$response = self::getResponseToDinamicText ($adb, $answerData [$index], $block);
				}
			} else if ($block->getElementType () == DiagnosticReportBuilderInterface::ELEMENT_TYPE_PROSPECTUS_DATA) {
				$response = self::getResponseFromAnswer ($adb, $answerData [$index]['surveyCod'], $block->getQuestionId (), $block->getAnswerName (), true);
			} else {
				$response = $block->getResult ();
			}
			return $response;
		}

        private static function saveValuedFunction ($adb, $crmId) {
		    if (empty ($crmId) || !count (self::$valuedFunctions)) {
		        return;
            }
		    foreach (self::$valuedFunctions as $valuedFunction) {
		        if (!$valuedFunction instanceof ValuedFunctions) {
		            continue;
                }
                $adb->pquery(
                    'INSERT INTO vtiger_diagnostic_valued_functions (crmid, function_question, function_name, function_label, function_value, descripcion, survey_cod) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    array($crmId, $valuedFunction->getQuestion(), $valuedFunction->getFunctionName(), $valuedFunction->getFunctionLabel(), $valuedFunction->getFunctionValue(), $valuedFunction->getDescription(), $valuedFunction->getSurveyCod())
                );
            }
        }
        
        /**
         * @param PearDatabase $adb
         * @param array $crmId
         * @param PlatformInstance $instance
         * @param array $systemData
         *
         * @return integer
         */
        private static function setInstanceDiagnosticReport ($adb, $diagnosticReport, $instance, $systemData) {
            if (empty ($instance) || empty ($diagnosticReport) || !$instance instanceof PlatformInstance) {
                return;
            }
			
            $dbconfig        = $systemData ['dbconfig'];
            $masterAdb       = ($adb->dbName != $dbconfig['db_name']) ? AdbManager::getInstance ()->getMasterAdb () : $adb;
            $GLOBALS ['adb'] = AdbManager::getInstance ()->getTargetInstanceAdb ($instance->getCode ());
            
            $diagnosticReportInstance                = CRMEntity::getInstance ('diagnostic_report');
            $diagnosticReportInstance->mode          = 'create';
            $diagnosticReportInstance->column_fields = array_merge (array(), $diagnosticReport);
            $diagnosticReportInstance->id            = null;
            $diagnosticReportInstance->save ('diagnostic_report');
            self::saveValuedFunction ($GLOBALS['adb'], $diagnosticReportInstance->id);
            $GLOBALS ['adb']  = $masterAdb;
            return $diagnosticReportInstance->id;
        }
        
        /**
         * @param PearDatabase $adb
         * @param CRMEntity $diagnosticReport
         * @param DiagnosticReportToAnswer[] $arrayBlocks
         * @param array $answerData
         * @param integer $questionnaireId
         *
         * @throws Exception
         */
        private static function setReportData ($adb, &$diagnosticReport, $arrayBlocks, $answerData, $questionnaireId) {
            $totalBlocks  = count ($arrayBlocks);
            for ($j = 0; $j < $totalBlocks; $j++) {
                $questionToId = "{$arrayBlocks [$j]->getQuestionId()}-{$arrayBlocks [$j]->getDiagnosticReportId()}";
                if (isset(self::$processedQuestion[ $arrayBlocks[$j]->getReportBlock() ])) {
                    if (in_array ($questionToId, self::$processedQuestion[ $arrayBlocks[$j]->getReportBlock() ])) {
                        continue;
                    }
                }

                $reportBlock = strtolower ($arrayBlocks [$j]->getReportBlock ());
                $fieldValue  = null;
                if ($arrayBlocks [$j]->getElementType() == DiagnosticReportBuilderInterface::ELEMENT_TYPE_PROSPECTUS_DATA) {
                    $reportBlock = strtolower ($arrayBlocks[$j]->getResult ());
                } else if ($arrayBlocks[$j]->getElementType() == DiagnosticReportBuilderInterface::ELEMENT_TYPE_MANAGEMENT_LEVEL) {
                    $reportBlock = strtolower ($arrayBlocks[$j]->getElementType());
                    $fieldValue  = self::getTopicCalculation ($adb, $arrayBlocks [$j], $answerData [0], $questionnaireId);
                    self::$topicsValue[$arrayBlocks [$j]->getAnswerName()] = floatval ($fieldValue);
                    $diagnosticReport->column_fields [$reportBlock]        = self::getManagementLevelName ($arrayBlocks [$j], $fieldValue);
                    $arrayBlocks [$j]->setAnswerName (null);
                    self::$processedQuestion[ $arrayBlocks[$j]->getReportBlock() ][] = "{$arrayBlocks [$j]->getQuestionId()}-{$arrayBlocks [$j]->getDiagnosticReportId()}";
                    continue;
                } else if ($arrayBlocks[$j]->getReportBlock () == DiagnosticReportBuilderInterface::ELEMENT_TYPE_DIAGNOSTIC_DATA) {
                    $reportBlock = strtolower ($arrayBlocks[$j]->getElementType());
                }
                $groupBlock  = (!empty ($arrayBlocks[$j]->getJoinType ())) ? self::getGroupBlock ($arrayBlocks, $j) : array();
                $hasResponse = self::checkAnswersUser ($arrayBlocks, $answerData, $j, $groupBlock);

                if ($hasResponse ['isKnown']) {
                    $index = $hasResponse ['index'];
                    if (
                        $arrayBlocks[$j]->getElementType() == DiagnosticReportBuilderInterface::ELEMENT_TYPE_TARGET_CATEGORY ||
                        $arrayBlocks[$j]->getElementType() == DiagnosticReportBuilderInterface::ELEMENT_TYPE_IMAGE
                    ) {
                        $fieldValue = $arrayBlocks[$index]->getResult();
                    } else if ($arrayBlocks[$j]->getElementType() == DiagnosticReportBuilderInterface::ELEMENT_TYPE_IMAGE_CURRENT_STATUS) {
                        $fieldValue = $arrayBlocks[$j]->getResult();
                    } else if ($arrayBlocks[$j]->getElementType() == DiagnosticReportBuilderInterface::ELEMENT_TYPE_VALUED_FUNCTIONS) {
                        self::setValuedFunction ($adb, $arrayBlocks [$index], $answerData[0]['surveyCod']);
                        self::$processedQuestion[ $arrayBlocks[$j]->getReportBlock() ][] = "{$arrayBlocks [$index]->getQuestionId()}-{$arrayBlocks [$index]->getDiagnosticReportId()}";
                        continue;
                    } else {
                        if (empty ($arrayBlocks [$hasResponse ['index']]->getElementType())) {
                            $objBlock = $arrayBlocks [$j];
                            $objBlock->setAnswerName($arrayBlocks [$hasResponse ['index']]->getAnswerName());
                        } else {
                            $objBlock = $arrayBlocks [$index];
                        }

                        $fieldValue = self::getUserResponse ($adb, $objBlock, $answerData);
                        if ($reportBlock == strtolower (DiagnosticReportBuilderInterface::ELEMENT_IMPROVEMENT_OPPORTUNITY) && !empty ($fieldValue)) {
                            $fieldValue = "<li>{$fieldValue}</li>";
                        }
                    }
                }
                if ($arrayBlocks [$index] instanceof DiagnosticReportToAnswer) {
                    self::$processedQuestion[ $arrayBlocks[$j]->getReportBlock() ][] = "{$arrayBlocks [$index]->getQuestionId()}-{$arrayBlocks [$index]->getDiagnosticReportId()}";
                }

                if (
                    !empty ($fieldValue) &&
                    (
                        empty ($diagnosticReport->column_fields [$reportBlock]) ||
                        $reportBlock == strtolower (DiagnosticReportBuilderInterface::BLOCKS_TYPE_CURRENT_STATUS) ||
                        $reportBlock == strtolower (DiagnosticReportBuilderInterface::BLOCKS_TYPE_INFORMATIVE_VIDEO)
                    )
                ) {
                    $diagnosticReport->column_fields [$reportBlock] = $fieldValue;
                } else if (!empty ($fieldValue)) {
                    $diagnosticReport->column_fields [$reportBlock] .= "&nbsp;{$fieldValue}";
                }
            }
        }

        /**
         * @param PearDatabase $adb
         * @param DiagnosticReportToAnswer $block
         * @param string $codSurvey
         */
        private static function setValuedFunction ($adb, $block, $codSurvey) {
            if (empty ($block->getAnswerName())) {
                return;
            }
            $result  = $adb->pquery (
                'SELECT
                        qa.value,
                        qa.label_a,
                        q.question
                    FROM
                        vtiger_question2answeres qa RIGHT JOIN vtiger_question q ON q.questionid = qa.questionid
                    WHERE
                        qa.name=? AND
                        qa.questionid=?',
                array ($block->getAnswerName(), $block->getQuestionId())
            );
            if ($adb->num_rows ($result) > 0) {
                $row = $adb->fetchByAssoc ($result, -1, false);
                $functionValue = $row ['value'];
                $functionLabel = $row ['label_a'];
                $functionQuestion = $row ['question'];
            }
            DatabaseUtils::closeResult ($result);
            $result = null;
            if (isset ($functionValue)) {
                self::$valuedFunctions [] = ValuedFunctions::getInstance()
                    ->setDescription ($block->getAttributes())
                    ->setFunctionLabel ($functionLabel)
                    ->setFunctionName ($block->getResult())
                    ->setFunctionValue ($functionValue)
                    ->setQuestion ($functionQuestion)
                    ->setSurveyCod ($codSurvey);
            }
        }
        
        /**
         * @param PearDatabase $adb
         * @param PlatformInstance $instance
         * @param array $systemData
         * @param array $diagnosticReport
         *
         * @return void
         */
        private static function updateContactAndInstance ($adb, $instance, $systemData, $diagnosticReport) {
            $dbconfig      = $systemData ['dbconfig'];
            $platPrincipal = $systemData ['platPrincipal'];
            $masterAdb     = ($adb->dbName != $dbconfig['db_name']) ? AdbManager::getInstance ()->getMasterAdb () : $adb;
            list ($firstName, $lastName) = explode (' ', $diagnosticReport['prospect_name'], 2);
            $profile = $diagnosticReport ['report_title'];
            $masterAdb->pquery (
            			'UPDATE
            				vtiger_clientes
            			SET
            				alias=?,
            				nombre_comercial=?,
            				observaciones=?
            			WHERE
            				clientesid IN (SELECT accountid FROM vtiger_instances WHERE code=?)',
            			array (trim ("{$firstName} {$lastName}"), trim ("{$firstName} {$lastName}"), $profile, $instance->getCode ())
            		);
            		$masterAdb->pquery (
            			'UPDATE
            				vtiger_contactos
            			SET
            				nombre=?,
            				apellidos=?,
            				observaciones=?
            			WHERE
            				clientes IN (SELECT accountid FROM vtiger_instances WHERE code=?)',
            			array ($firstName, $lastName, $profile, $instance->getCode ())
            		);
            		$masterAdb->pquery ('UPDATE vtiger_instances SET status=?, source=? WHERE code=?', array ('verified', 'Railes', $instance->getCode ()));
        }
        
        /**
         * @param PearDatabase $adb
         * @param array $systemData
         * @param array $answerData
         * @param integer $questionnaireId
         *
         * @return void
         * @throws WebServiceException
         */
		public static function createDiagnosticReport ($adb, $systemData, $answerData, $questionnaireId) {
            $platform = $systemData ['platform'];
			if (!count ($answerData) || empty ($answerData) || empty ($questionnaireId)) {
				return;
			}
			$drb = DiagnosticReportBuilderHelper::getInstance ($adb, $platform)->getDiagnosticReportByQuestionnaire ($questionnaireId);
			if (empty ($drb)) {
				return;
			}
			$reportBlocks           = array_keys (DiagnosticReportBuilderInterface::REPORT_BLOCKS);
			$questionToAnswers      = $drb->getReportsToAnswer ();
			$diagnosticReport       = CRMEntity::getInstance ('diagnostic_report');
			$diagnosticReport->mode = 'create';
			$diagnosticReport->column_fields = getColumnFields ('diagnostic_report');
			foreach ($reportBlocks as $block) {
				$arrayBlocks = array ();
				$isFound     = false;
				foreach ($questionToAnswers as $reportToAnswer) {
					if ($reportToAnswer->getReportBlock () == $block) {
						$arrayBlocks [] = $reportToAnswer;
						self::$processedQuestion[ $block ] = array ();
						$isFound        = true;
					}
				}
				if ($isFound) {
                    self::setReportData ($adb, $diagnosticReport, $arrayBlocks, $answerData, $questionnaireId);
				}
			}
			$diagnosticReport->column_fields ['report_title']   = $drb->getName () . "-{$answerData[0]['surveyCod']}";
            $diagnosticReport->column_fields ['reportstatus']   = 'Borrador';
            $diagnosticReport->column_fields ['business_type']  = (!empty($answerData[0]['type'])) ? DiagnosticReportBuilderInterface::BUSINESS_TYPE [ $answerData[0]['type'] ] : $diagnosticReport->column_fields ['business_type'];
			$diagnosticReport->column_fields ['business_phase'] = (!empty ($answerData[0]['stage'])) ? DiagnosticReportBuilderInterface::IMAGE_CURRENT_STATUS[ $answerData[0]['stage'] ] : DiagnosticReportBuilderInterface::IMAGE_CURRENT_STATUS[ $diagnosticReport->column_fields ['current_status']];
            $diagnosticReport->column_fields ['current_status'] = (!empty ($answerData[0]['stage'])) ? $answerData[0]['stage'] : $diagnosticReport->column_fields ['current_status'];
            $diagnosticReport->column_fields ['prospect_email'] = $diagnosticReport->column_fields ['email_response'];
			$diagnosticReport->save ('diagnostic_report');
			self::saveValuedFunction ($adb, $diagnosticReport->id);
            $instance = self::assignInstance ($adb, $systemData, $diagnosticReport->column_fields);
            $crmId    = self::setInstanceDiagnosticReport ($adb, $diagnosticReport->column_fields, $instance, $systemData);
            self::AuthenticateUser ($crmId, $instance, $systemData, $diagnosticReport->column_fields);
		}

        /**
         * @param PearDatabase $adb
         * @param array $diagnosticData
         *
         * @return array|null
         */
        public static function fetchAvailableDestinations ($adb, $diagnosticData) {
	        if (
				empty ($diagnosticData ['business_type']) ||
				empty ($diagnosticData ['business_phase']) ||
				empty ($diagnosticData ['target_category'])
	        ) {
				return null;
			} else {
				$businessType   = trim ($diagnosticData ['business_type']);
				$businessPhase  = trim ($diagnosticData ['business_phase']);
				$targetCategory = trim ($diagnosticData ['target_category']);
	        }
			
	        $businessDestination = self::fetchBusinessDestination ($adb);
			if (empty ($businessDestination)) {
				return null;
			}
			foreach ($businessDestination as $destination) {
				if (
					in_array ($businessType, $destination->getBusinessType ()) &&
					in_array ($businessPhase, $destination->getBusinessPhase ()) &&
					in_array ($targetCategory, $destination->getCategories ())
				) {
					$destinations [] = array (
						'crmid'               => $destination->getDestinationId (),
						'destinationName'     => $destination->getDestinationName (),
						'destinationCategory' => $destination->getCategories (),
					);
				}
				
			}
	        return (isset ($destinations)) ? $destinations : null;
        }

        /**
         * @param PearDatabase $adb
         * @param integer $crmId
         *
         * @return ValuedFunctions[]|null
         * @throws Exception
         */
        public static function fetchValuedFunction ($adb, $crmId) {
            $result = $adb->pquery ('SELECT * FROM vtiger_diagnostic_valued_functions WHERE crmid=?', array ($crmId));
            if ($adb->num_rows ($result) > 0) {
                while ($row = $adb->fetchByAssoc ($result, -1, false))  {
                    $valuedFunctions [] = ValuedFunctions::getInstance()
                        ->setBarColor ($row['function_value'])
                        ->setCrmId ($row['crmid'])
                        ->setDescription ($row['descripcion'])
                        ->setFunctionId ($row['functionid'])
                        ->setQuestion ($row['function_question'])
                        ->setFunctionLabel ($row['function_label'])
                        ->setFunctionName ($row['function_name'])
                        ->setFunctionValue ($row['function_value'])
                        ->setSurveyCod ($row['survey_cod']);
                }
            }
            DatabaseUtils::closeResult ($result);
            $result = null;
            return (isset ($valuedFunctions)) ? $valuedFunctions : null;
        }

    }
