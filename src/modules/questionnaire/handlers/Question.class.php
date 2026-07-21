<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/ImageUtils.class.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/questionnaire/Objects/AskingFor.class.php');
	require_once ('modules/questionnaire/Objects/QuestionException.class.php');
	require_once ('modules/questionnaire/Objects/QuestionGroups.class.php');
	require_once ('modules/questionnaire/Objects/QuestionannaireStages.class.php');
	require_once ('modules/questionnaire/Objects/ResponseOption.class.php');
	require_once ('modules/questionnaire/Objects/QuestionType.class.php');
	require_once ('modules/questionnaire/Objects/QuestionToGroup.class.php');
	require_once ('modules/questionnaire/Objects/RangeGroup.class.php');
	
	class Question {
		
		const IMAGEN_TYPE   = array('png', 'jpg', 'jpeg', 'gif');
		const IMAGEN_WIDTH  = 250;
		const IMAGEN_HEIGHT = 181;
		const SURVEY_URL    = 'index.php?module=store&action=fetchSurvey&surveytoken=';
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		protected $processedId = array();
		
		protected $photoIndex = 0;
		
		/** @var PearDatabase */
		protected $adb;
		
		public function __construct($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		private function buildupResponseOption ($question) {
			if (!isset($question['response']) || empty ($question['response'])) {
				return null;
			}
			$index = 0;
			foreach ($question['response'] as $response) {
				if (is_array ($response['label'])) {
					$mainLabel = $response['label']['a'];
					$secondLabel = $response['label']['b'];
				} else {
					$mainLabel = $response['label'];
					$secondLabel = null;
				}
				$responseOptions [] = ResponseOption::getInstance ()
					->setId ((isset($response['answereid'])) ? $response['answereid'] : null)
					->setAdditionalData ((isset($response['data'])) ? $response['data'] : 0)
					->setImageType ((isset($response['image_type'])) ? $response['image_type'] : null)
					->setMainLabel ($mainLabel)
					->setQuestionId ($question['questionid'])
					->setSecondLabel ($secondLabel)
					->setFeedBack ((isset($response['feedback'])) ? $response['feedback'] : null)
					->setSequence ($index)
					->setSelected ((isset($response['selected'])) ? $response['selected'] : null)
					->setValue ($response['value']);
				$index++;
			}
			return (isset($responseOptions)) ? $responseOptions : null;
		}
		
		private function deleteJunkId ($questionnaireId) {
			if (!count ($this->processedId)) {
				return;
			}
			$ids = $this->adb->sql_expr_datalist ($this->processedId);
			$this->adb->query (
				"DELETE
						q2a.*
					FROM
						vtiger_question2answeres q2a
					INNER JOIN vtiger_question q ON q2a.questionid = q.questionid
					WHERE
						questionnairesid={$questionnaireId} AND
						q2a.queston2answereid NOT IN {$ids}"
			);
		}
		
		private function fetchImagesByResponse ($idResponse) {
			if (empty($idResponse)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question2image WHERE queston2answereid=?', array ($idResponse));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					if (empty($row['image'])) {
						$row['imagetype'] = 'image/png';
						$row['image']     = QuestionInterface::DEFAULT_IMAGES;
					}
					$photos = array (
						'image'     => "data:{$row['imagetype']};base64,{$row['image']}",
						'imageType' => $row['imagetype'],
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($photos)) ? $photos : null;
		}
		
		/**
		 * @param string $nameTheme
		 * @param integer
		 *
		 * @return null|RangeGroup[]
		 * @throws Exception
		 */
		private function fetchRangeByTheme ($nameTheme, $questionId) {
			if (empty($questionId)) {
				return  null;
			} else if(empty($nameTheme)) {
				$nameTheme='';
			}
			
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question2group_range WHERE theme_name=? AND questionid=?', array ($nameTheme, $questionId));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					$ranges[] = RangeGroup::getInstance ()
						->setFeedBack ($row['feedback_range'])
						->setId ($row['grouptorangeid'])
						->setMaximum ($row['maximum'])
						->setMinimum ($row['minimum'])
						->setQuestionId ($row['questionid'])
						->setThemeName ($row['theme_name']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($ranges)) ? $ranges : null;
		}
		
		/**
		 * @param AskingFor $question
		 * @param string $view
		 *
		 * @throws Exception
		 * @throws SmartyException
		 */
		private function getHtmlResponse (&$question, $view) {
			if (empty ($question->getResponseOption())) {
				$question->setHtmlResponse(null);
				return;
			} else if (empty($question->getQuestionType ())) {
				$question->setHtmlResponse(null);
				return;
			}
			$smarty = new vtigerCRM_Smarty ();
			$answerTemplate = strtolower ($question->getQuestionType ());
			$smarty->assign ('ANSWERS_OPTIONS', $question->getResponseOption());
			$smarty->assign ('ID', $question->getSequence ());
			$smarty->assign ('ID_QUESTION', $question->getId ());
			$smarty->assign ('NUM_ROWS', count($question->getResponseOption()));
			$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
			$htmlOut = $smarty->fetch ("modules/Questionnaires/Questions/Question_options/{$answerTemplate}{$view}.tpl");
			$question->setHtmlResponse ($htmlOut);
		}
		
		/**
		 * @param integer $uploadMax
		 * @param integer $k
		 *
		 * @return null|array
		 * @throws Exception
		 */
		private function getImageResponse ($uploadMax, $k) {
			if(!isset ($_FILES['question'])) {
				return null;
			}
			$i = $this->photoIndex;
			if ($_FILES['question']['error'][$k][$i] > 0) {
				return null;
			}
			$fileSize = $_FILES['question']['size'][$k][$i];
			$fileTmp = $_FILES['question']['tmp_name'][$k][$i];
			$fileExt = strtolower (end (explode ('.', $_FILES['question']['name'][$k][$i])));
			if (!in_array ($fileExt, self::IMAGEN_TYPE)) {
				throw new Exception (QuestionException::ERROR_EXTENSION_NO_ALLOWED);
			}
			if ($fileSize > $uploadMax) {
				throw new Exception(QuestionException::ERROR_FILE_TOO_BIG);
			}
			
			$idPhoto = rand ();
			$fileExt = '.' . $fileExt;
			
			move_uploaded_file ($fileTmp, 'Image/Source_' . $idPhoto . $fileExt);
			
			$config = array();
			$config ['imageLibrary'] = 'gd2';
			$config ['sourceImage'] = 'Image/Source_' . $idPhoto . $fileExt;
			$config ['createThumb'] = false;
			$config ['maintainRatio'] = true;
			$config ['width'] = self::IMAGEN_WIDTH;
			$config ['height'] = self::IMAGEN_HEIGHT;
			
			$imagLibrary = new ImageUtils ($config);
			
			$resizeStatus = $imagLibrary->resize ();
			if ($resizeStatus) {
				$data[$i] = file_get_contents ('Image/Source_' . $idPhoto . $fileExt);
				$data[$i] = base64_encode ($data[$i]);
				unlink ('Image/Source_' . $idPhoto . $fileExt);
			}
			return (isset($data[$i])) ? $data[$i] : null;
		}
		
		/**
		 * @return QuestionGroups[]|null
		 *
		 * @throws Exception
		 */
		private function getQuestionsGroup () {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question_group WHERE status=?', array ('ENABLED'));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					$questionGroup[] = QuestionGroups::getInstance ()
						->setDescription ($row ['description_group'])
						->setId ($row ['questiongroupid'])
						->setName ($row ['group_name']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($questionGroup)) ? $questionGroup : null;
		}
		
		/**
		 * @return QuestionannaireStages[]|null
		 *
		 * @throws Exception
		 */
		private function getQuestionnaireStages () {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_questionannaire_stages WHERE status=?', array ('ENABLED'));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false))  {
					$questionStage[] = QuestionannaireStages::getInstance ()
						->setDescription ($row ['stagedescription'])
						->setId ($row ['questionannairestagesid'])
						->setName ($row ['stagename']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($questionStage)) ? $questionStage : null;
		}
		
		/**
		 * @param integer $idResponse
		 *
		 * @return null|string
		 * @throws Exception
		 */
		private function getResponseName ($idResponse) {
			if (empty($idResponse)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT name FROM vtiger_question2answeres WHERE queston2answereid=?', array ($idResponse));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$responseName = $row['name'];
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($responseName)) ? $responseName : null;
		}
		
		/**
		 * @param string $nameResponse
		 *
		 * @return null|SurveyNav
		 * @throws Exception
		 */
		private function getSurveyNavByResponse ($nameResponse) {
			if (empty($nameResponse)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT *  FROM vtiger_survey_nav WHERE response_name=? ', array ($nameResponse) );
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$surveyNav = SurveyNav::getInstance ()
						->setSurveyNavId ($row['survey_navid'])
						->setQuestionnairesId ($row['questionnairesid'])
						->setQuestionId ($row['questionid'])
						->setQuestonToAnswereId ($row['survey_navid'])
						->setResponseName ($row['response_name']);
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($surveyNav)) ? $surveyNav : null;
		}
		
		
		private function saveSurvey ($crmId, $instanceCode, $siteUrl) {
			if (empty($crmId)) {
				return;
			}
			$result = $this->masterAdb->pquery ('SELECT surveyappid FROM vtiger_survey_app WHERE crmid=? AND code=?', array($crmId, $instanceCode));
			if ($this->masterAdb->num_rows ($result) == 0) {
				$this->masterAdb->pquery ('INSERT INTO vtiger_survey_app (crmid, code) VALUES (?, ?)', array ($crmId, $instanceCode));
				$token     = sha1 ("{$instanceCode}-{$crmId}");
				$surveyUrl = $siteUrl . self::SURVEY_URL . $token;
				$this->adb->pquery ('UPDATE vtiger_questionnaire SET presentation_url=?  WHERE  questionnaireid=?',	array ($surveyUrl, $crmId));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}
		
		/**
		 * @param ResponseOption $responseOption
		 * @param integer $sequence
		 * @param integer $deleteId
		 *
		 * @throws Exception
		 */
		private function saveImages ($responseOption, $sequence, $deleteId) {
			$uploadMax  = (PlatzillaUtils::getMaxFileSizeInMb () * (1024 * 1024));
			$photos     = $this->getImageResponse ($uploadMax, $sequence);
			if (empty ($photos) && $deleteId) {
				$this->adb->pquery (
					'UPDATE vtiger_question2image SET queston2answereid=?  WHERE queston2answereid=?',
					array ($responseOption->getId (), $deleteId)
				);
				return;
			}
			$this->adb->pquery (
				'INSERT INTO vtiger_question2image (queston2answereid, imagetype, image)
					VALUES (?, ?, ?)',
				array ($responseOption->getId (), $responseOption->getImageType (), $photos)
			);
		}
		
		/**
		 * @param integer $questionnairesId
		 * @param null|string
		 *
		 * @return string
		 * @throws Exception
		 * @throws SmartyException
		 */
		public function run ($questionnairesId, $view = null) {
			$theQuestions = $this->fetchQuestionById ($questionnairesId);
			
			if (!empty($theQuestions)) {
				$totalQuestion = count ($theQuestions);
				foreach ($theQuestions as $thisQuestion) {
					$thisQuestion->idQuestionRow = rand (1000, 10000);
					$this->getHtmlResponse ($thisQuestion, $view);
				}
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ASKING_FOR', $theQuestions);
			$smarty->assign ('ANSWERS_OPTIONS', QuestionInterface::ANSWERS_OPTIONS);
			$smarty->assign ('CALCULATION_TYPE', QuestionInterface::CALCULATION_TYPE);
			$smarty->assign ('QUESTION_FORM', QuestionInterface::QUESTION_FORM);
			$smarty->assign ('QUESTION_GROUP', $this->getQuestionsGroup ());
			$smarty->assign ('QUESTIONNAIRE_ID', $questionnairesId);
			$smarty->assign ('STAGES', $this->getQuestionnaireStages ());
			$smarty->assign ('TOTAL_QUESTION', (isset($totalQuestion)) ? $totalQuestion : 0);
			$smarty->assign ('VIEW', $view);
			return $smarty->fetch ('modules/Questionnaires/Questions/question.tpl');
		}
		
		/**
		 * @param integer $questionnaireId
		 * @param string $instanceCode
		 * @param string $siteUrl
		 *
		 * @return null|AskingFor[]
		 * @throws Exception
		 */
		public function buildupAskingFor ($questionnaireId, $instanceCode, $siteUrl) {
			if (empty($questionnaireId)) {
				return null;
			} else if (empty($_REQUEST['question'])) {
				throw new Exception ('No hay preguntas en el questionario!');
			}
			$sequence = 0;
			foreach ($_REQUEST['question'] as $keyRequest => $question) {
				$this->photoIndex = 0;
				$askingFor = AskingFor::getInstance ()
					->setDescription ($question['description'])
					->setFeedBack (null)
					->setHelp ($question['help'])
					->setCalculationType ($question['calculation_type'])
					->setId ($question['questionid'])
					->setPuctuation ($question['points'])
					->setQuestion ($question['title'])
					->setQuestionForm ($question['form'])
					->setQuestionGroupId (!empty($question['group']) ? $question['group'] : null)
					->setQuestionnaireId ($questionnaireId)
					->setQuestionStageId ((!empty($question['stages']) && !empty($question['group'])) ? $question['stages'] : '')
					->setQuestionType ($question['answere'])
					->setResponseOption ($this->buildupResponseOption ($question))
					->setSequence ($sequence)
					->setUrlVideo ($question['video_url'])
					->setWeighing ($question['weight']);
				$sequence++;
				$this->saveQuestion ($askingFor, $keyRequest);
				$questions[] = $askingFor;
			}
			$this->deleteJunkId ($questionnaireId);
			$this->saveSurvey ($questionnaireId, $instanceCode, $siteUrl);
			return (isset($questions)) ? $questions : null;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return boolean
		 */
		public function checkSurvey ($crmId) {
			if (empty($crmId)) {
				return false;
			}
			$result = $this->adb->pquery (
				'SELECT * FROM 	vtiger_crmentity  WHERE crmid=? AND deleted=?',
				array($crmId, 0)
			);
			
			
			DatabaseUtils::closeResult ($result);
			
			return ($this->adb->num_rows ($result) > 0);
		}
		
		/**
		 * @param integer $groupId
		 *
		 * @throws Exception
		 */
		public function deleteQuestionGoup ($groupId) {
			if (empty ($groupId) || !is_numeric ($groupId)) {
				throw new Exception ('Fundamento de cuestionario no encontrado!');
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question WHERE questiongroupid=?', array ($groupId));
			if ($this->adb->num_rows ($result) > 0) {
				DatabaseUtils::closeResult ($result);
				throw new Exception ("Imposible eliminar el fundamento hay {$this->adb->num_rows ($result)} pregunta(s) relacionada(s)");
			} else {
				$this->adb->pquery ('DELETE FROM vtiger_question_group WHERE questiongroupid=?',array ($groupId));
			}
		}
		
		/**
		 * @param integer $stageId
		 *
		 * @throws Exception
		 */
		public function deleteQuestionStage ($stageId) {
			if (empty ($stageId) || !is_numeric ($stageId)) {
				throw new Exception ('Etapa de cuestionario no encontrado!');
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question WHERE questionstageid=?', array ($stageId));
			if ($this->adb->num_rows ($result) > 0) {
				DatabaseUtils::closeResult ($result);
				throw new Exception ("Imposible eliminar la etapa hay {$this->adb->num_rows ($result)} pregunta(s) relacionada(s)");
			} else {
				$this->adb->pquery ('DELETE FROM vtiger_questionannaire_stages WHERE questionannairestagesid=?',array ($stageId));
			}
		}
		
		/**
		 * @param integer $questionnaireId
		 *
		 * @return null|QuestionToGroup[]
		 * @throws Exception
		 * @throws Exception
		 */
		public function fetchGroupAndThemes ($questionnaireId) {
			if (empty($questionnaireId)) {
				return null;
			}
			
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question WHERE questionnairesid=? AND status=? AND question_form!=? AND questiongroupid IS NOT NULL ORDER BY sequence ASC', array ($questionnaireId, 'ENABLED', 'OPEN_QUESTION'));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if (empty($row ['questiongroupid'])) {
						continue;
					}
					$groupAdnThemes [] = QuestionToGroup::getInstance ()
						->setGroupName ($row ['questiongroupid'])
						->setId ($row['questionnairesid'])
						->setQuestionId ($row['questionid'])
						->setQuestion ($row ['question'])
						->setRanges ($this->fetchRangeByTheme ($row ['questionstageid'], $row['questionid']))
						->setThemeName ($row ['questionstageid']);
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($groupAdnThemes)) ? $groupAdnThemes : null;
		}
		
		/**
		 * @param integer $questionnairesId
		 *
		 * @return null|AskingFor[]
		 * @throws Exception
		 */
		public function fetchQuestionById ($questionnairesId) {
			if (empty($questionnairesId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question WHERE questionnairesid=? AND status=?', array ($questionnairesId, 'ENABLED'));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$askingFor [] = AskingFor::getInstance ()
						->setCalculationType ($row ['calculation_type'])
						->setDescription ($row ['description_question'])
						->setFeedBack ($row ['feedback'])
						->setHelp ($row ['help_text'])
						->setId ($row ['questionid'])
						->setPuctuation ($row ['puctuation'])
						->setQuestion ($row ['question'])
						->setQuestionForm ($row ['question_form'])
						->setQuestionGroupId ($row ['questiongroupid'])
						->setQuestionnaireId ($row ['questionnairesid'])
						->setQuestionStageId ($row ['questionstageid'])
						->setQuestionType ($row ['question_type'])
						->setResponseOption ($this->fetchResponseOptionById ($row ['questionid']))
						->setSequence ($row ['sequence'])
						->setUrlVideo ($row ['url_video'])
						->setWeighing ($row ['weighing']);
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($askingFor)) ? $askingFor : null;
		}
		
		/**
		 * @return null|QuestionGroups[]
		 *
		 * @throws Exception
		 */
		public function fetchQuestionGroup () {
			return $this->getQuestionsGroup ();
		}
		
		/**
		 * @return null|QuestionannaireStages[]
		 *
		 * @throws Exception
		 */
		public function fetchQuestionStages () {
			return $this->getQuestionnaireStages();
		}
		
		/**
		 * @param integer $questionId
		 *
		 * @return null|ResponseOption[]
		 * @throws Exception
		 */
		public function fetchResponseOptionById ($questionId) {
			if (empty($questionId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question2answeres WHERE questionid=? AND status=?', array ($questionId, 'ENABLED'));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$imageResponse = $this->fetchImagesByResponse ($row['queston2answereid']);
					$responseOptions [] = ResponseOption::getInstance ()
						->setId ($row ['queston2answereid'])
						->setAdditionalData ($row ['additional_data'])
						->setImage ((!empty($imageResponse)) ? $imageResponse['image'] : null)
						->setImageType ((!empty($imageResponse)) ? $imageResponse['imageType'] : null)
						->setName ($row['name'])
						->setMainLabel ($row ['label_a'])
						->setQuestionId ($row ['questionid'])
						->setSecondLabel ($row ['label_b'])
						->setSelected ($row ['selected'])
						->setFeedBack ($row ['feedback'])
						->setSequence ($row ['sequence'])
						->setSurveyNav ($this->getSurveyNavByResponse ($row ['name']))
						->setValue ($row ['value']);
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($responseOptions)) ? $responseOptions : null;
		}
		
		/**
		 * @param string $token
		 *
		 * @return array|mixed|null
		 * @throws Exception
		 */
		public function fetchSurveyDataFromToken ($token) {
			if (empty($token)) {
				return null;
			}
			$result = $this->masterAdb->pquery (
				"SELECT * FROM 	vtiger_survey_app  WHERE SHA1(CONCAT(code,'-', crmid))=?",
				array($token)
			);
			
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row = $this->masterAdb->fetchByAssoc ($result, -1, false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($row)) ? $row : null;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return AskingFor[]|null
		 * @throws Exception
		 * @throws SmartyException
		 */
		public function fetchQuestionnaireToSurvey ($crmId) {
			$view = 'Survey';
			$theQuestions = $this->fetchQuestionById ($crmId);
			if (!empty($theQuestions)) {
				foreach ($theQuestions as $thisQuestion) {
					$this->getHtmlResponse ($thisQuestion, $view);
				}
			}
			return $theQuestions;
		}
		
		/**
		 * @param integer $questionnairesId
		 * @param integer $questionId
		 *
		 * @return AskingFor|null
		 * @throws Exception
		 */
		public function getQuestionById ($questionnairesId, $questionId) {
			if (empty($questionnairesId) || empty($questionId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question WHERE questionnairesid=? AND questionid=?', array ($questionnairesId, $questionId));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$askingFor = AskingFor::getInstance ()
					->setCalculationType ($row ['calculation_type'])
					->setDescription ($row ['description_question'])
					->setFeedBack ($row ['feedback'])
					->setHelp ($row ['help_text'])
					->setId ($row ['questionid'])
					->setPuctuation ($row ['puctuation'])
					->setQuestion ($row ['question'])
					->setQuestionForm ($row ['question_form'])
					->setQuestionGroupId ($row ['questiongroupid'])
					->setQuestionnaireId ($row ['questionnairesid'])
					->setQuestionStageId ($row ['questionstageid'])
					->setQuestionType ($row ['question_type'])
					->setResponseOption ($this->fetchResponseOptionById ($row ['questionid']))
					->setSequence ($row ['sequence'])
					->setUrlVideo ($row ['url_video'])
					->setWeighing ($row ['weighing']);
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($askingFor)) ? $askingFor : null;
		}
		
		/**
		 * @param integer $questionGroupId
		 * @param boolean $onlyName
		 *
		 * @return string|QuestionGroups|null
		 * @throws Exception
		 */
		public function getQuestionsGroupBy ($questionGroupId, $onlyName = true) {
			if (empty($questionGroupId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question_group WHERE questiongroupid=?', array ($questionGroupId));
			if ($this->adb->num_rows ($result) > 0) {
				$row           = $this->adb->fetchByAssoc ($result, -1, false);
				if ($onlyName) {
					$questionGroup = $row ['group_name'];
				} else {
					$questionGroup = QuestionGroups::getInstance ()
						->setDescription ($row ['description_group'])
						->setId ($row ['questiongroupid'])
						->setName ($row ['group_name']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($questionGroup)) ? $questionGroup : null;
		}
		
		/**
		 * @param integer $id
		 * @param boolean $onlyName
		 *
		 * @return string|QuestionannaireStages|null
		 * @throws Exception
		 */
		public function getQuestionnaireStagesBy ($id, $onlyName = true) {
			if (empty ($id)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_questionannaire_stages WHERE questionannairestagesid=?', array ($id));
			if ($this->adb->num_rows ($result) > 0) {
				$row           = $this->adb->fetchByAssoc ($result, -1, false);
				if ($onlyName) {
					$questionStage = $row ['stagename'];
				} else {
					$questionStage = QuestionannaireStages::getInstance ()
						->setDescription ($row ['stagedescription'])
						->setId ($row ['questionannairestagesid'])
						->setName ($row ['stagename']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($questionStage)) ? $questionStage : null;
		}
		
		/**
		 * @param AskingFor $askingFor
		 * @param  integer $keyRequest
		 *
		 * @throws Exception
		 */
		public function saveQuestion ($askingFor, $keyRequest) {
			if (empty($askingFor) || !$askingFor instanceof AskingFor) {
				return;
			}
			$askingFor->validate ();
			$this->adb->startTransaction ();
			if (empty($askingFor->getId ())) {
				$this->adb->pquery (
					'INSERT INTO vtiger_question (questionnairesid, questiongroupid, questionstageid, question, description_question, help_text, calculation_type, url_video, feedback, question_form, question_type, puctuation, weighing, sequence) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($askingFor->getQuestionnaireId (), $askingFor->getQuestionGroupId (), $askingFor->getQuestionStageId (), $askingFor->getQuestion (), $askingFor->getDescription (), $askingFor->getHelp (), $askingFor->getCalculationType (),$askingFor->getUrlVideo (), $askingFor->getFeedBack (), $askingFor->getQuestionForm (), $askingFor->getQuestionType (), $askingFor->getPuctuation (), $askingFor->getWeighing (), $askingFor->getSequence ())
				);
				$askingFor->setId ($this->adb->getLastInsertID ());
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_question SET questiongroupid=?, questionstageid=?, question=?, description_question=?, help_text=?, calculation_type=?, url_video=?, feedback=?, question_form=?, question_type=?, puctuation=?, weighing=?, sequence=?  WHERE questionid=?',
					array ($askingFor->getQuestionGroupId (), $askingFor->getQuestionStageId (), $askingFor->getQuestion (), $askingFor->getDescription (), $askingFor->getHelp (), $askingFor->getCalculationType (), $askingFor->getUrlVideo (), $askingFor->getFeedBack (), $askingFor->getQuestionForm (), $askingFor->getQuestionType (), $askingFor->getPuctuation (), $askingFor->getWeighing (), $askingFor->getSequence (), $askingFor->getId ())
				);
			}
			 $this->saveResponseOptions ($askingFor, $keyRequest);
		}
		
		/**
		 * @param AskingFor $askingFor
		 * @param integer $keyRequest
		 *
		 * @throws Exception
		 */
		public function saveResponseOptions ($askingFor, $keyRequest) {
			if (empty($askingFor->getResponseOption ())) {
				return;
			}
			
			foreach ($askingFor->getResponseOption () as $responseOption) {
				if (! $responseOption instanceof ResponseOption) {
					continue;
				}
				$deleteId = 0;
				if (!empty ($responseOption->getId ())) {
					$deleteId     = $responseOption->getId ();
					$responseName = $this->getResponseName ($deleteId);
					$responseOption->setName ((!empty($responseName) ? $responseName : $responseOption->randomName ()));
					$this->adb->pquery ('DELETE FROM vtiger_question2answeres WHERE queston2answereid=?', array ($responseOption->getId ()));
				} else {
					$responseOption->setName ($responseOption->randomName ());
				}
				$this->adb->pquery (
					'INSERT INTO vtiger_question2answeres (questionid, name, label_a, label_b, value, feedback, additional_data, selected, sequence)
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($askingFor->getId (), $responseOption->getName (), $responseOption->getMainLabel (), $responseOption->getSecondLabel (), $responseOption->getValue (), $responseOption->getFeedBack (), $responseOption->getAdditionalData (), $responseOption->getSelected (), $responseOption->getSequence ())
				);
				$responseOption->setId ($this->adb->getLastInsertID ());
				$this->processedId [] = $responseOption->getId();
				if (in_array ($askingFor->getQuestionType (), QuestionInterface::IMAGES_TYPE)) {
					$this->saveImages ($responseOption, $keyRequest, $deleteId);
					$this->photoIndex++;
				}
			}
			$this->adb->completeTransaction ();
		}
		
		/**
		 * @param QuestionGroups $questionGroup
		 *
		 * @throws Exception
		 * @throws QuestionException
		 */
		public function saveQuestionGroup ($questionGroup) {
			if (empty($questionGroup) || !$questionGroup instanceof QuestionGroups) {
				throw new Exception ('Imposible guardar el fundamento, información incompleta!');
			}
			$questionGroup->validate ();
			$this->adb->startTransaction ();
			if (empty($questionGroup->getId ())) {
				$this->adb->pquery (
					'INSERT INTO vtiger_question_group (group_name, description_group)
					VALUES (?, ?)',
					array ($questionGroup->getName (), $questionGroup->getDescription ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_question_group SET group_name=?, description_group=?  WHERE questiongroupid=?',
					array ($questionGroup->getName (), $questionGroup->getDescription (), $questionGroup->getId ())
				);
			}
		}
		
		/**
		 * @param $questionStage
		 *
		 * @throws Exception
		 */
		public function saveQuestionStage ($questionStage) {
			if (empty($questionStage) || !$questionStage instanceof QuestionannaireStages) {
				throw new Exception ('Imposible guardar de la etapa, información incompleta!');
			}
			$questionStage->validate ();
			$this->adb->startTransaction ();
			if (empty($questionStage->getId ())) {
				$this->adb->pquery (
					'INSERT INTO vtiger_questionannaire_stages (stagename, stagedescription)
					VALUES (?, ?)',
					array ($questionStage->getName (), $questionStage->getDescription ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_questionannaire_stages SET stagename=?, stagedescription=?  WHERE questionannairestagesid=?',
					array ($questionStage->getName (), $questionStage->getDescription (), $questionStage->getId ())
				);
			}
		}
		
		/**
		 * @param RangeGroup $range
		 *
		 * @throws Exception
		 */
		public function saveRange ($range) {
			if (empty($range) || !$range instanceof RangeGroup) {
				throw new Exception ('Imposible guardar rango, información incompleta!');
			}
			
			if (!empty ($range->getId())) {
				$this->adb->pquery (
					'UPDATE vtiger_question2group_range SET minimum=?, maximum=?, feedback_range=? WHERE grouptorangeid=?',
					array ($range->getMinimum (), $range->getMaximum (), $range->getFeedBack (), $range->getId ())
				);
			} else {
				$this->adb->pquery (
					'INSERT INTO vtiger_question2group_range (questionid, theme_name, minimum, maximum, feedback_range)
					VALUES (?, ?, ?, ?, ?)',
					array ($range->getQuestionId (), $range->getThemeName (), $range->getMinimum (), $range->getMaximum (), $range->getFeedBack ())
				);
			}
		}
		
		/**
		 * @param SurveyNav $surveyNav
		 *
		 * @throws Exception
		 */
		public function saveSurveyNav ($surveyNav) {
			if (empty($surveyNav) || !$surveyNav instanceof SurveyNav) {
				throw new Exception ('Imposible guardar secuencia del cuestionario, información incompleta!');
			}
			
			$result = $this->adb->pquery ('SELECT * FROM vtiger_survey_nav WHERE response_name=? AND questionnairesid=?', array($surveyNav->getResponseName (), $surveyNav->getQuestionnairesId ()));
			if ($this->adb->num_rows ($result) > 0) {
				$this->adb->pquery (
					'UPDATE vtiger_survey_nav SET questionid=? WHERE response_name=?',
					array ($surveyNav->getQuestionId (), $surveyNav->getResponseName ())
				);
			} else {
				$this->adb->pquery (
					'INSERT INTO vtiger_survey_nav (questionnairesid, questionid, response_name)
					VALUES (?, ?, ?)',
					array ($surveyNav->getQuestionnairesId (), $surveyNav->getQuestionId (), $surveyNav->getResponseName ())
				);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return Question|null
		 */
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
		
	}
