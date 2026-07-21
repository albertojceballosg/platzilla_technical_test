<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/platzilla/Managers/PicklistManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/ImageUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/diagnostic_report_builder/Objects/DiagnosticReportBuilder.php');
	require_once ('modules/diagnostic_report_builder/Objects/ReportBuilderCurrentStatus.php');
	require_once ('modules/questionnaire/handlers/Question.class.php');
	
	class DiagnosticReportBuilderHelper implements diagnosticReportBuilderInterface {
		
		const IMAGEN_TYPE   = array ('png', 'jpg', 'jpeg', 'gif');
		const IMAGEN_WIDTH  = 250;
		const IMAGEN_HEIGHT = 181;
		
		/** @var PearDatabase */
		private $adb;
		
		/** @var string */
		private $createDate;
		
		/** @var boolean */
		private $isMother;
		
		/** @var PearDatabase */
		private $masterAdb;
		
		/** @var string */
		private $platform;
		
		/**
		 * DiagnosticReportBuilderHelper constructor.
		 * @param PearDatabase $adb
		 * @param string $platform
		 */
		public function __construct (PearDatabase $adb, $platform) {
			$this->adb        = $adb;
			$this->createDate = date('Y-m-d h:i:s');
			$this->isMother   = ($platform == 'madre');
			$this->masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
			$this->platform   = $platform;
		}
		
		/**
		 * @param integer $questionnaireId
		 * @param null|integer $drmId
		 */
		private function disableQuestionnaires ($questionnaireId, $drmId = null) {
			if (empty ($questionnaireId)) {
				return;
			}
			$where = '';
			if (!empty ($drmId)) {
				$where = "AND drmid != {$drmId}";
			}
			$this->adb->pquery (
				"UPDATE vtiger_diagnostic_report_builder SET status=? WHERE questionnaireid=? {$where}" ,
				array ('DISABLED', $questionnaireId)
			);
		}
		
		/**
		 * @param integer $drmId
		 *
		 * @return null|DiagnosticReportToAnswer[]
		 * @throws Exception
		 */
		private function fetchReportToAnswer ($drmId) {
			if (empty($drmId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_diagnostic_report2answer WHERE drmid=?', array ($drmId));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					$reportToAnswers [] = DiagnosticReportToAnswer::getInstance ()
						->setAnswerName ($row ['answer_name'])
						->setAttributes ($row ['attribute'])
						->setDiagnosticReportId ($row ['drmid'])
						->setElementType ($row ['element_type'])
						->setHandler ($row ['handler'])
						->setId ($row ['report_answerid'])
						->setJoinType ($row ['join_type'])
						->setQuestionId ($row ['questionid'])
						->setQuestionJoin ($row ['question_join'])
						->setReportBlock ($row ['report_block'])
						->setResult ($row ['result']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($reportToAnswers)) ? $reportToAnswers : null;
		}
		
		/**
		 * @param DiagnosticReportBuilder $diagnosticBuilder
		 *
		 * @throws Exception
		 */
		private function saveQyestionAndAnswers ($diagnosticBuilder) {
			if (empty ($diagnosticBuilder->getReportsToAnswer ())) {
				throw new Exception('Algo salio mal intenta mas tarde');
			}
			$swIni = true;
			foreach ($diagnosticBuilder->getReportsToAnswer () as $reportToAnswer) {
				if (!$reportToAnswer instanceof DiagnosticReportToAnswer) {
					continue;
				}
				
				$this->adb->pquery (
					'INSERT INTO vtiger_diagnostic_report2answer (drmid, questionid, answer_name, question_join, join_type, element_type, report_block, result, handler, attribute) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($diagnosticBuilder->getId (), $reportToAnswer->getQuestionId (), $reportToAnswer->getAnswerName (), $reportToAnswer->getQuestionJoin (), $reportToAnswer->getJoinType (), $reportToAnswer->getElementType (), $reportToAnswer->getReportBlock (),$reportToAnswer->getResult (), $reportToAnswer->getHandler(), $reportToAnswer->getAttributes ())
				);
				$reportAnswerId = intval ($this->adb->getLastInsertID ());
				
			}
		}
		
		/**
		 * @param integer $drbId
		 * @param string $status
		 *
		 * @throws Exception
		 */
		public function changeStatusToDiagnosticReport ($drbId, $status) {
			if (empty($drbId)) {
				throw new Exception('Informe de diagnóstico desconocido');
			} else if (empty($status)) {
				throw new Exception('Imposible cambiar el informe a un estado desconocido');
			}
			$this->adb->pquery (
				'UPDATE vtiger_diagnostic_report_builder SET status=? WHERE drmid=?',
				array ($status, $drbId)
			);
			if ($status == 'ENABLED') {
				$result = $this->adb->pquery (
					'SELECT questionnaireid FROM vtiger_diagnostic_report_builder WHERE drmid=?',
					array ($drbId)
				);
				if ($this->adb->num_rows ($result) > 0) {
					$row             = $this->adb->fetchByAssoc ($result, -1, false);
					$questionnaireId = $row ['questionnaireid'];
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$this->disableQuestionnaires ($questionnaireId, $drbId);
			}
		}
		
		/**
		 * @param integer $drbId
		 *
		 * @throws Exception
		 */
		public function deleteDiagnosticReport ($drbId) {
			if (empty($drbId)) {
				throw new Exception('Informe de diagnóstico desconocido');
			}
			$this->adb->startTransaction ();
			$this->adb->pquery ('DELETE FROM vtiger_diagnostic_report2answer WHERE drmid=?', array ($drbId)	);
			$this->adb->pquery ('DELETE FROM vtiger_diagnostic_report_builder WHERE drmid=?', array ($drbId));
			$this->adb->completeTransaction ();
		}
		
		/**
		 * @param integer $questionnaireId
		 * @param string $userMail
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchAnswersByQuestionnaire ($questionnaireId, $userMail) {
			if (empty ($questionnaireId) || empty($userMail)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT
						an.answersid,
						an.question,
						an.useranswer,
						an.questionid
					  FROM
					  	vtiger_answers an
					  INNER JOIN vtiger_crmentity crm ON an.answersid = crm.crmid
					  WHERE
					  	crm.deleted=? AND
					  	an.questionnaire=? AND
					  	an.useremail=?
					  ',
				array(0, $questionnaireId, $userMail)
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					$answers [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($answers)) ? $answers : null;
		}
		
		/**
		 * @param boolean $headersOnly
		 * @param boolean $enabledOnly
		 *
		 * @return DiagnosticReportBuilder[]|null
		 * @throws Exception
		 */
		public function fetchDiagnosticReportBuilder ($headersOnly = false, $enabledOnly = false) {
			$where  = ($enabledOnly) ? "AND status = 'ENABLED'" : '';
			$result = $this->adb->query (
				"SELECT
						drb.*,
						q.name AS title
					  FROM
					  	vtiger_diagnostic_report_builder drb
					  INNER JOIN vtiger_questionnaire q ON q.questionnaireid = drb.questionnaireid
					  WHERE
					  	1 {$where}"
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					$diagnosticReports [] = DiagnosticReportBuilder::getInstance ()
						->setId ($row ['drmid'])
						->setName ($row ['name'])
						->setQuestionnaireId ($row ['questionnaireid'])
						->setQuestionnaireName ($row ['title'])
						->setReportsToAnswer ((!$headersOnly) ? $this->fetchReportToAnswer ($row ['drmid']) : null)
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($diagnosticReports)) ? $diagnosticReports : null;
		}
		
		/**
		 * @param integer $drbId
		 * @param boolean $headersOnly
		 *
		 * @return DiagnosticReportBuilder|null
		 * @throws Exception
		 */
		public function getDiagnosticReportById ($drbId, $headersOnly = false) {
			if (empty($drbId)) {
				throw new Exception('Reporte de reporte desconocido!');
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_diagnostic_report_builder WHERE drmid=?', array($drbId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					$diagnosticReport = DiagnosticReportBuilder::getInstance ()
						->setId ($row ['drmid'])
						->setName ($row ['name'])
						->setQuestionnaireId ($row ['questionnaireid'])
						->setReportsToAnswer ((!$headersOnly) ? $this->fetchReportToAnswer ($row ['drmid']) : null)
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($diagnosticReport)) ? $diagnosticReport : null;
		}

        /**
         * @param integer $questionnaireId
         * @param boolean $headersOnly
         *
         * @return DiagnosticReportBuilder|null
         * @throws Exception
         */
		public function getDiagnosticReportByQuestionnaire ($questionnaireId, $headersOnly = false) {
			if (empty ($questionnaireId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_diagnostic_report_builder drb WHERE drb.questionnaireid=? AND status=?  LIMIT 1', array($questionnaireId, 'ENABLED'));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					$diagnosticReport = DiagnosticReportBuilder::getInstance ()
						->setId ($row ['drmid'])
						->setName ($row ['name'])
						->setQuestionnaireId ($row ['questionnaireid'])
						->setReportsToAnswer ((!$headersOnly) ? $this->fetchReportToAnswer ($row ['drmid']) : null)
						->setStatus ($row ['status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($diagnosticReport)) ? $diagnosticReport : null;
		}
		
		/**
		 * @param Smarty $smarty
		 * @param DiagnosticReportToAnswer $reportToAnswer
		 * @param AskingFor[] $questions
		 *
		 * @return integer|null
		 * @throws Exception
		 */
		public function getHtmlBlock ($smarty, &$reportToAnswer, $questions) {
			if (!$reportToAnswer instanceof DiagnosticReportToAnswer) {
				return null;
			} if (empty($reportToAnswer->getElementType ())) {
				$blockId  = rand (1001, 100001);
				$reportToAnswer->setIdQuestionBlock ($blockId);
				return  null;
			}
			$fieldId  = rand (101, 100001);
			$blockId  = rand (1001, 100001);
			$template = strtolower ($reportToAnswer->getElementType ());
			
			$plm                 = PicklistManager::getInstance ($this->adb);
			$businessPhase       = $plm->fetchPicklistByName ('business_phase_m');
			$businessType        = $plm->fetchPicklistByName ('business_type_m');
			$destinationCategory = $plm->fetchPicklistByName ('destination_category');
			
			$smarty->assign ('BUSINESS_PHASE', $businessPhase);
			$smarty->assign ('BUSINESS_TYPE', $businessType);
			$smarty->assign ('DESTINATION_CATEGORY', $destinationCategory);
			$smarty->assign ('FIELD_ID', $fieldId);
			$smarty->assign ('IMAGE_CURRENT_STATUS', DiagnosticReportBuilderInterface::IMAGE_CURRENT_STATUS);
			$smarty->assign ('PROSPECTUS_DATA', DiagnosticReportBuilderInterface::PROSPECTUS_DATA);
			$smarty->assign ('QUESTIONS', $questions);
			$smarty->assign ('RANGES', ($reportToAnswer->getElementType () == DiagnosticReportBuilderInterface::ELEMENT_TYPE_MANAGEMENT_LEVEL) ? json_decode($reportToAnswer->getResult(), true) : null);
			$smarty->assign ('REPORT_ANSWER', $reportToAnswer);
			$smarty->assign ('REPORT_CURRENT_STATUS', ReportBuilderCurrentStatus::IMAGE_CURRENT_STATUS);
            $smarty->assign ('TOPICS_OPERATIONS', DiagnosticReportBuilderInterface::TOPICS_OPERATIONS);
            $smarty->assign ('VALUED_FUNCTIONS', DiagnosticReportBuilderInterface::VALUED_FUNCTIONS);
			$smarty->assign ('idRowBuilder', $blockId);
			$htmlOutput = $smarty->fetch ("modules/diagnostic_report_builder/elements/edit_{$template}.tpl");
			$reportToAnswer->setHtmlBlock ($htmlOutput);
			$reportToAnswer->setIdQuestionBlock ($blockId);
			if (
                $reportToAnswer->getElementType () == DiagnosticReportBuilderInterface::ELEMENT_TYPE_DYNAMIC_TEXT ||
                $reportToAnswer->getElementType () == DiagnosticReportBuilderInterface::ELEMENT_TYPE_VALUED_FUNCTIONS
            ) {
			    return $fieldId;
            }
			return null;
		}
		
		/**
		 * @return array|null
		 *
		 * @throws Exception
		 */
		public function fetchQuestionnaires () {
			$result = $this->masterAdb->pquery (
				'SELECT
							q.questionnaireid,
							q.name, q.descrption
						  FROM
						  	vtiger_questionnaire q
						  INNER JOIN vtiger_crmentity crm ON q.questionnaireid = crm.crmid
						  WHERE
						  	crm.deleted=? AND
						  	q.questionnairestatus=?',
				array (0, 'Habilitado')
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					$questionnaires [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($questionnaires)) ? $questionnaires : null;
		}
		
		/**
		 * @param integer $questionnaireId
		 *
		 * @return AskingFor[]|null
		 * @throws Exception
		 */
		public function fetchQuestionsByQuestionnaire ($questionnaireId) {
			return Question::getInstance ($this->masterAdb)->fetchQuestionById ($questionnaireId);
		}
		
		/**
		 * @param integer$blockId
		 *
		 * @return null|string
		 * @throws Exception
		 */
		public function getImageResponse ($blockId) {
			if(!isset ($_FILES['block'])) {
				return null;
			}
			
			if ($_FILES['block']['error'][$blockId]['element-field'] > 0) {
				return null;
			}
			$uploadMax = (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024);
			$fileSize  = $_FILES['block']['size'][$blockId]['element-field'];
			$fileTmp   = $_FILES['block']['tmp_name'][$blockId]['element-field'];
			$fileExt   = strtolower (end (explode ('.', $_FILES['block']['name'][$blockId]['element-field'])));
			if (!in_array ($fileExt, self::IMAGEN_TYPE)) {
				throw new Exception (DiagnosticReportBuilderException::ERROR_EXTENSION_NO_ALLOWED);
			}
			if ($fileSize > $uploadMax) {
				throw new Exception(DiagnosticReportBuilderException::ERROR_FILE_TOO_BIG);
			}
			
			$idPhoto = rand ();
			$fileExt = '.' . $fileExt;
			
			move_uploaded_file ($fileTmp, 'Image/Source_' . $idPhoto . $fileExt);
			
			$config = array();
			$config ['imageLibrary']  = 'gd2';
			$config ['sourceImage']   = 'Image/Source_' . $idPhoto . $fileExt;
			$config ['createThumb']   = false;
			$config ['maintainRatio'] = true;
			$config ['width']         = self::IMAGEN_WIDTH;
			$config ['height']        = self::IMAGEN_HEIGHT;
			
			$imagLibrary = new ImageUtils ($config);
			
			$resizeStatus = $imagLibrary->resize ();
			if ($resizeStatus) {
				$data = file_get_contents ('Image/Source_' . $idPhoto . $fileExt);
				$data = base64_encode ($data);
				unlink ('Image/Source_' . $idPhoto . $fileExt);
			}
			return (isset($data)) ? $data : null;
		}

        /**
         * @param integer $questionnaireId
         *
         * @return array|null
         * @throws Exception
         */
        public function getTopicsFromQuestionnaire ($questionnaireId) {
            $result = $this->adb->pquery ('SELECT DISTINCT questionstageid FROM vtiger_question WHERE questionnairesid=?', array ($questionnaireId));
            if ($this->adb->num_rows ($result) > 0) {
                while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
                    $topics [] = $row ['questionstageid'];
                }
            }
            DatabaseUtils::closeResult ($result);
            $result = null;
            return (isset ($topics)) ? $topics : null;
        }


        public function getTopicRange($ranges, $topicOptions) {
            if (empty($ranges)) {
                return null;
            }
            $dummy       = explode ('-', $topicOptions);
            $rangeName   = $dummy [1];
            $totalRanges = count ($ranges['name']);
            $isKnown     = false;
            for ($i = 0; $i < $totalRanges; $i++) {
                if ($ranges['name'][$i] == $rangeName) {
                    $index = $i;
                    $isKnown = true;
                    break;
                }
            }
            if ($isKnown) {
                return json_encode(array ('min' => $ranges['min'][$index], 'max' => $ranges['max'][$index]));
            }
            return  null;

        }

        /**
		 * @param DiagnosticReportBuilder $diagnosticBuilder
		 *
		 * @throws Exception
		 */
		public function saveDiagnosticReportBuilder ($diagnosticBuilder) {
			if (!$diagnosticBuilder instanceof DiagnosticReportBuilder) {
				throw new Exception('Algo salio mal intenta mas tarde');
			}
			$this->adb->startTransaction ();
			if (empty ($diagnosticBuilder->getId ())) {
				$this->disableQuestionnaires ($diagnosticBuilder->getQuestionnaireId ());
				$this->adb->pquery (
					'INSERT INTO vtiger_diagnostic_report_builder (questionnaireid, name, status) VALUES (?, ?, ?)',
					array ($diagnosticBuilder->getQuestionnaireId (), $diagnosticBuilder->getName (), $diagnosticBuilder->getStatus ())
				);
				$diagnosticBuilderId = intval ($this->adb->getLastInsertID ());
				$diagnosticBuilder->setId ($diagnosticBuilderId);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_diagnostic_report_builder SET questionnaireid=?, name=? WHERE drmid=?',
					array ($diagnosticBuilder->getQuestionnaireId (), $diagnosticBuilder->getName (), $diagnosticBuilder->getId ())
				);
				$this->adb->pquery (
					'DELETE FROM vtiger_diagnostic_report2answer WHERE drmid=?',
					array ($diagnosticBuilder->getId ())
				);
			}
			$this->saveQyestionAndAnswers ($diagnosticBuilder);
			$this->adb->completeTransaction ();
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param $platform
		 *
		 * @return DiagnosticReportBuilderHelper
		 */
		public static function getInstance (PearDatabase $adb, $platform) {
			return new self ($adb, $platform);
		}
		
	}
