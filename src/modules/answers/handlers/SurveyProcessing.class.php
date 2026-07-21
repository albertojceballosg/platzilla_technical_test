<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/ImageUtils.class.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/questionnaire/handlers/Question.class.php');
	
	class SurveyProcessing {
		
		const SORT_MULTIPLE_MAX  = 7;
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		/** @var PearDatabase */
		protected $adb;
		
		public function __construct($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		private function createExtractNumber () {
			$result = $this->adb->query ("SHOW FUNCTION STATUS WHERE `name` = 'ExtractNumber'");
			if ($result->fields) {
				return;
			}
			$sql = "
			CREATE 	DEFINER = `root`@`localhost`
			FUNCTION ExtractNumber (`in_string` TEXT CHARSET utf8)
			RETURNS VARCHAR(25)
			NO SQL
			BEGIN
			    DECLARE ctrNumber VARCHAR(250);
			    DECLARE finNumber VARCHAR(250) DEFAULT '';
			    DECLARE sChar VARCHAR(1);
			    DECLARE inti INTEGER DEFAULT 1;
			    IF LENGTH(in_string) > 0 THEN
			        WHILE(inti <= LENGTH(in_string)) DO
			            SET sChar = SUBSTRING(in_string, inti, 1);
			            SET ctrNumber = FIND_IN_SET(sChar, '0,1,2,3,4,5,6,7,8,9,<');
			            IF (ctrNumber > 0) THEN
			            IF (ctrNumber = '<') THEM
			                SET finNumber = CONCAT(finNumber,'+');
			            ELSE
			                SET finNumber = CONCAT(finNumber,sChar);
			            END IF;
			            SET inti = inti + 1;
			        END WHILE;
			        RETURN finNumber;
			    ELSE
			        RETURN 0;
			    END IF;
			END";
			$this->adb->query ($sql);
		}
		
		/**
		 * @param AskingFor $askingFor
		 *
		 * @throws Exception
		 */
		private function getAskingForCalculation (&$askingFor) {
			if (empty($askingFor->getCalculationType ())) {
				return;
			}
			
			if ($askingFor->getCalculationType () == 'WEIGHTED_AVERAGE') {
				$result = $this->adb->query (
					"SELECT
							IFNULL(SUM((q.puctuation * q.weighing)) / SUM(q.weighing),0) AS op
						  FROM
						  	vtiger_answers a
						  INNER JOIN vtiger_crmentity crm ON crm.crmid = a.answersid
						  INNER JOIN vtiger_question q ON a.questionnaire = q.questionnairesid
						  WHERE
						  	crm.deleted=0 AND
						  	a.questionnaire = {$askingFor->getQuestionnaireId ()} AND
						  	a.question = '{$askingFor->getQuestion ()}' AND
						  	q.question = '{$askingFor->getQuestion ()}'"
				);
			} else {
				$result = $this->adb->query (
					"SELECT
							IFNULL({$askingFor->getCalculationType ()}(q.puctuation),0) AS op
						  FROM
						  	vtiger_answers a
						  INNER JOIN vtiger_crmentity crm ON crm.crmid = a.answersid
						  INNER JOIN vtiger_question q ON a.questionnaire = q.questionnairesid
						  WHERE
						  	crm.deleted=0 AND
						  	a.questionnaire = {$askingFor->getQuestionnaireId ()} AND
						  	a.question = '{$askingFor->getQuestion ()}' AND
						  	q.question = '{$askingFor->getQuestion ()}'"
				);
			}
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$askingFor->setCalculationResult ($row['op']);
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}
		/**
		 * @param integer $crmId
		 *
		 * @return AskingFor|null
		 * @throws Exception
		 */
		private function getAskingForData ($crmId) {
			if (empty($crmId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT question, questionnaire FROM vtiger_answers WHERE answersid=?', array ($crmId));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$askingFor       = $row['question'];
				$questionnaireId = $row ['questionnaire'];
				DatabaseUtils::closeResult ($result);
				$result = null;
				return $this->getQuestionByQuestion ($questionnaireId, $askingFor);
			}
			return null;
		}
		
		/**
		 * @param integer $questionnairesId
		 * @param string $question
		 *
		 * @return AskingFor|null
		 * @throws Exception
		 */
		private function getQuestionByQuestion ($questionnairesId, $question) {
			if (empty($questionnairesId) || empty($question)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_question WHERE questionnairesid=? AND question=?', array ($questionnairesId, $question));
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
					->setResponseOption (Question::getInstance ($this->adb)->fetchResponseOptionById ($row ['questionid']))
					->setSequence ($row ['sequence'])
					->setUrlVideo ($row ['url_video'])
					->setWeighing ($row ['weighing']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($askingFor)) ? $askingFor : null;
		}
		
		/**
		 * @param integer $questionnaireId
		 * @param ResponseOption[] $responseOption
		 * @param $question
		 *
		 * @throws Exception
		 */
		private function getChoiceMultiSelection ($questionnaireId, &$responseOption, $question) {
			if (empty($questionnaireId)) {
				return;
			}
			$this->createExtractNumber ();
			$totalOptions = count ($responseOption);
			$responseOption[0]->setAdditionalData ($totalOptions);
			$result = $this->adb->pquery (
				'SELECT
					  	ExtractNumber(a.useranswer) AS num,
					  	a.useranswer
					  FROM vtiger_answers a
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = a.answersid
					  WHERE
					  	crm.deleted=0 AND
					  	a.questionnaire=? AND
					  	a.question=?',
				array ($questionnaireId, $question)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					for ($k = 0; $k < $totalOptions; $k++) {
						if (!count ($responseOption[$k]->getSummaryRow())) {
							$responseOption[$k]->setSummaryRow (array_fill (0, $totalOptions, 0));
						}
						$isFoundIn   = stripos ($row['useranswer'],$responseOption[$k]->getMainLabel ());
						if (is_numeric ($responseOption[$k]->getValue ())) {
							$numOption = ($isFoundIn !== false) ? intval ($responseOption[$k]->getValue ()) : 0;
						} else {
							$numOption = ($isFoundIn !== false) ? 1 : 0;
						}
						$responseOption[$k]->summaryRow[$k] += $numOption;
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}
		
		/**
		 * @param integer $questionnaireId
		 * @param ResponseOption $responseOption
		 * @param string $question
		 * @param integer $totalOption
		 *
		 * @throws Exception
		 */
		private function getChoiceSimpleSelection ($questionnaireId, &$responseOption, $question, $totalOption) {
			if (empty($questionnaireId)) {
				return;
			}
			
			$whereValue  = "an.useranswer LIKE '{$responseOption->getMainLabel ()}%'";
			
			$result = $this->adb->pquery (
				"SELECT
					  	(ask.op * 100 / COUNT(a.useranswer)) AS porcent,
						ask.op
					  FROM `vtiger_answers` a
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = a.answersid
					  CROSS JOIN (
					  	SELECT COUNT(an.useranswer) AS op
					  	FROM vtiger_answers an
					  	INNER JOIN vtiger_crmentity c ON c.crmid = an.answersid
					  	WHERE
					  		c.deleted=0 AND
					  		an.questionnaire=? AND
					  		an.question=? AND
					  		{$whereValue}
					  ) ask
					  WHERE
					  	crm.deleted=0 AND
					  	a.questionnaire=? AND
					  	a.question=?
					  	LIMIT 1",
				array ($questionnaireId, $question, $questionnaireId, $question)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$porcent = ($row['porcent'] > 100) ? ($row['porcent'] - 100) : $row['porcent'];
				$responseOption->setSuveyPorcent ($porcent)
					->setSurveyTotal ($row['op']);
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}
		
		/**
		 * @param ResponseOption[] $responseOptions
		 *
		 * @return null|string
		 */
		private function getPieChartData ($responseOptions) {
			if (empty($responseOptions)) {
				return null;
			}
			$dataTable [] = array('opciones', 'total');
			foreach ($responseOptions as $responseOption) {
				$dataTable[] = array(
					preg_replace ("[\n|\r|\n\r|\t|\0|\x0B]", "", $responseOption->getMainLabel ()),
					intval ($responseOption->getSurveyTotal ()),
				);
			}
			return json_encode ($dataTable);
		}
		
		/**
		 * @param AskingFor $askingFor
		 * @param array $summaryTable
		 *
		 * @return null|string
		 */
		private  function getStackedBars ($askingFor, $summaryTable) {
			if (!$askingFor instanceof AskingFor) {
				return null;
			} else if (empty ($askingFor->getResponseOption ())) {
				return null;
			}
			$responseOptions = $askingFor->getResponseOption ();
			$totalCategories = ($responseOptions[0]->getAdditionalData () + 1);
			$dataTable = array_fill (0, $totalCategories, 'Importancia');
			$dataTable[0] = 'Áreas de Aplicación ';
			if ($askingFor->getQuestionType () == 'SORT_SIMPLE') {
				array_unshift ($summaryTable, $dataTable);
				$dataTable = $summaryTable;
			} else {
				array_pop ($dataTable);
				$temp = $dataTable;
				unset ($dataTable);
				$dataTable [] = $temp;
				unset ($temp);
				foreach ($summaryTable as $tabla) {
					$dataTable [] = $tabla ['a'];
					$dataTable [] = $tabla ['b'];
				}
			}
			return json_encode ($dataTable);
		}
		
		/**
		 * @param integer $questionnaireId
		 * @param ResponseOption[] $responseOption
		 * @param $question
		 *
		 * @throws Exception
		 */
		private function getSortMultiple ($questionnaireId, &$responseOption, $question) {
			if (empty($questionnaireId)) {
				return;
			}
			$this->createExtractNumber ();
			$totalOptions = count ($responseOption);
			$result = $this->adb->pquery (
				'SELECT
					  	ExtractNumber(answervalue) AS num
					  FROM vtiger_answers
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = vtiger_answers.answersid
					  WHERE
					  	crm.deleted=0 AND
					  	questionnaire=? AND
					  	question=?',
				array ($questionnaireId, $question)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$totalValues = 0;
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					for ($k = 0; $k < $totalOptions; $k++) {
						if (!count ($responseOption[$k]->getSummaryRow())) {
							$totalFill = (self::SORT_MULTIPLE_MAX - 1);
							$categories['a'] = array_fill (0, $totalFill, 0);
							$categories['b'] = array_fill (0, $totalFill, 0);
							$responseOption[$k]->setSummaryRow ($categories);
							unset($categories);
						}
						$numOption = intval (substr($row['num'], $k, 1));
						($responseOption[$k]->summaryRow['b'][$numOption] += 1);
						($numOption = self::SORT_MULTIPLE_MAX - $numOption);
						($responseOption[$k]->summaryRow['a'][$numOption] += 1);
						$totalValues += $numOption;
					}
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}
		
		/**
		 * @param integer $questionnaireId
		 * @param ResponseOption $responseOption
		 * @param string $question
		 *
		 * @throws Exception
		 */
		private function getOpenQuestion ($questionnaireId, &$responseOption, $question) {
			if (empty($questionnaireId)) {
				return;
			}
			
			$whereValue = (!empty($responseOption->getValue ())) ? " an.useranswer LIKE '%{$responseOption->getValue ()}%'" : 1;
			$result = $this->adb->pquery (
				"SELECT
					  	(ask.op * 100/COUNT(a.answersid))  AS porcent,
						ask.op
					  FROM `vtiger_answers` a
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = a.answersid
					  CROSS JOIN (
					  	SELECT COUNT(an.answersid) AS op
					  	FROM vtiger_answers an
					  	INNER JOIN vtiger_crmentity c ON c.crmid = an.answersid
					  	WHERE
					  		c.deleted=0 AND
					  		an.questionnaire=? AND
					  		an.question=? AND
					  		{$whereValue}
					  ) ask
					  WHERE
					  	crm.deleted=0 AND
					  	a.questionnaire=? AND
					  	a.question=?",
				array ($questionnaireId, $question, $questionnaireId, $question)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$porcent = ($row['porcent'] > 100) ? ($row['porcent'] - 100) : $row['porcent'];
				$responseOption->setSuveyPorcent ($porcent)
					->setSurveyTotal ($row['op']);
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}
		
		/**
		 * @param $questionnaireId
		 * @param ResponseOption[] $responseOption
		 * @param $question
		 *
		 * @throws Exception
		 */
		private function getSortSimple ($questionnaireId, &$responseOption, $question) {
			if (empty($questionnaireId)) {
				return;
			}
			$this->createExtractNumber ();
			$totalOptions = count ($responseOption);
			$totalCategories = $responseOption[0]->getAdditionalData ();
			$result = $this->adb->pquery (
				'SELECT
					  	ExtractNumber(useranswer) AS num
					  FROM vtiger_answers
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = vtiger_answers.answersid
					  WHERE
					  	crm.deleted=0 AND
					  	questionnaire=? AND
					  	question=?',
				array ($questionnaireId, $question)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					for ($k = 0; $k < $totalOptions; $k++) {
						if (!count ($responseOption[$k]->getSummaryRow())) {
							$responseOption[$k]->setSummaryRow (array_fill (0, $totalCategories, 0));
						}
						$numOption = intval (substr($row['num'], $k, 1));
						$responseOption[$k]->summaryRow[($numOption - 1)] += 1;
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}
		
		/**
		 * @param ResponseOption[] $responseOptions
		 * @param boolean $totalized
		 *
		 * @return array|null
		 */
		private function getSummaryMultipleTable (&$responseOptions, $totalized = false) {
			if (empty($responseOptions)) {
				return null;
			}
			$totalOptions = count ($responseOptions);
			$totalCategories = count ($responseOptions[0]->summaryRow['a']);
			$summaryTable = array();
			$totalAValues  = 0;
			$totalBValues  = 0;
			for ($k = 0; $k < $totalOptions; $k++) {
				$totalAValues += array_sum ($responseOptions[$k]->summaryRow['a']);
				$totalBValues += array_sum ($responseOptions[$k]->summaryRow['b']);
			}
			for ($k = 0; $k < $totalOptions; $k++) {
				$summaryTable[$k]['a'][0] = preg_replace ("[\n|\r|\n\r|\t|\0|\x0B]", "", $responseOptions[$k]->getMainLabel ());
				$summaryTable[$k]['b'][0] = preg_replace ("[\n|\r|\n\r|\t|\0|\x0B]", "", $responseOptions[$k]->getSecondLabel ());
				for ($c = 0; $c < $totalCategories; $c++) {
					if ($totalized) {
						$summaryTable[$k]['a'][($c + 1)] = intval ($responseOptions[$k]->summaryRow['a'][$c]);
						$summaryTable[$k]['b'][($c + 1)] = intval ($responseOptions[$k]->summaryRow['b'][$c]);
					} else {
						$summaryTable[$k]['a'][($c + 1)] = floatval (($responseOptions[$k]->summaryRow['a'][$c] * 100) / $totalAValues);
						$summaryTable[$k]['b'][($c + 1)] = floatval (($responseOptions[$k]->summaryRow['b'][$c] * 100) / $totalBValues);
					}
				}
			}
			return $summaryTable;
		}
		
		/**
		 * @param ResponseOption[] $responseOptions
		 * @param boolean $totalized
		 *
		 * @return array|null
		 */
		private function getSummarySimpleTable (&$responseOptions, $totalized = false) {
			if (empty($responseOptions)) {
				return null;
			}
			$totalOptions = count ($responseOptions);
			$totalCategories = $responseOptions[0]->getAdditionalData ();
			$summaryTable = array();
			$totalValues  = 0;
			for ($k = 0; $k < $totalOptions; $k++) {
				$totalValues += array_sum ($responseOptions[$k]->summaryRow);
			}
			for ($k = 0; $k < $totalOptions; $k++) {
				$totalPoint = array_sum ($responseOptions[$k]->summaryRow);
				$responseOptions[$k]->setSurveyTotal ($totalPoint);
				$responseOptions[$k]->setSuveyPorcent (($totalPoint * 100)/ $totalValues);
				$summaryTable[$k][0] = $responseOptions[$k]->getMainLabel ();
				for ($c = 0; $c < $totalCategories; $c++) {
					if ($totalized) {
						$summaryTable[$k][($c + 1)] = intval ($responseOptions[$k]->summaryRow[$c]);
					} else {
						$summaryTable[$k][($c + 1)] = floatval (($responseOptions[$k]->summaryRow[$c] * 100) / $totalPoint);
					}
				}
			}
			return $summaryTable;
		}
		
		/**
		 * @param integer $questionnaireId
		 *
		 * @return integer
		 * @throws Exception
		 */
		private function getTotalSurvey ($questionnaireId, $question) {
			if (empty($questionnaireId)) {
				return 0;
			}
			$result = $this->adb->pquery (
				'SELECT
						COUNT(answersid) AS total
					  FROM vtiger_answers
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = vtiger_answers.answersid
					  WHERE
					  	crm.deleted=0 AND
					  	questionnaire=? AND
					  	question=?',
				array ($questionnaireId, $question)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row         = $this->adb->fetchByAssoc ($result, -1, false);
				$totalSurvey = $row ['total'];
				DatabaseUtils::closeResult ($result);
				$result = null;
				return $totalSurvey;
			}
			return 0;
		}
		
		/**
		 * @param integer $crmId
		 * @param string|null $view
		 *
		 * @return string
		 * @throws Exception
		 * @throws SmartyException
		 */
		public function run ($crmId, $view = null) {
			if (empty ($view)) {
				return '<strong>Valores no edtables</strong>';
			}
			$askingFor = $this->getAskingForData ($crmId);
			$askingFor->setSurveyTotal ($this->getTotalSurvey ($askingFor->getQuestionnaireId (), $askingFor->getQuestion ()));
			$askingFor->setSequence (($askingFor->getSequence () + 1));
			$this->getAskingForCalculation ($askingFor);
			if (!empty($askingFor->getResponseOption ())) {
				$totalOptions = count ($askingFor->getResponseOption ());
				for ($k = 0; $k < $totalOptions; $k++) {
					if (!$askingFor->getResponseOption ()[$k] instanceof ResponseOption) {
						continue;
					}
					if (in_array ($askingFor->getQuestionType (), array_keys (QuestionInterface::ANSWERS_OPTIONS['SIMPLE_SELECTION']))) {
						$this->getChoiceSimpleSelection ($askingFor->getQuestionnaireId (), $askingFor->getResponseOption ()[$k], $askingFor->getQuestion (), $totalOptions);
						$answerTemplate = 'ChoiceAndSelecction.tpl';
					} else if(in_array ($askingFor->getQuestionType (), array_keys (QuestionInterface::ANSWERS_OPTIONS['MULTIPLE_CHOICE']))) {
						$this->getChoiceMultiSelection ($askingFor->getQuestionnaireId (), $askingFor->getResponseOption (), $askingFor->getQuestion ());
						$summaryTable    = $this->getSummarySimpleTable ($askingFor->getResponseOption (), true);
						$totalCategories = $askingFor->getResponseOption ()[0]->getAdditionalData ();
						$valueType       = (is_numeric ($askingFor->getResponseOption ()[0]->getValue ())) ? 'NUMBER' : 'TEXT';
						$answerTemplate  = 'multiple_choice_answer.tpl';
						break;
					} else if(in_array ($askingFor->getQuestionType (), array_keys (QuestionInterface::ANSWERS_OPTIONS['OPEN_QUESTION']))) {
						$this->getOpenQuestion ($askingFor->getQuestionnaireId (), $askingFor->getResponseOption ()[$k], $askingFor->getQuestion ());
						$answerTemplate = 'open_question.tpl';
					} else {
						if ($askingFor->getQuestionType () == 'SORT_SIMPLE') {
							$this->getSortSimple ($askingFor->getQuestionnaireId (), $askingFor->getResponseOption (), $askingFor->getQuestion ());
							$summaryTable    = $this->getSummarySimpleTable ($askingFor->getResponseOption ());
							$totalCategories = $askingFor->getResponseOption ()[0]->getAdditionalData ();
							$answerTemplate = 'sort_simple_answer.tpl';
							break;
						} else {
							$this->getSortMultiple($askingFor->getQuestionnaireId (), $askingFor->getResponseOption (), $askingFor->getQuestion ());
							$summaryTable    = $this->getSummaryMultipleTable ($askingFor->getResponseOption ());
							$totalCategories = (count ($askingFor->getResponseOption ()[0]->summaryRow['a']) + 1);
							$askingFor->getResponseOption ()[0]->setAdditionalData ($totalCategories);
							$answerTemplate  = 'sort_multiple_answer.tpl';
							break;
						}
					}
				}
			}
			if (
				in_array ($askingFor->getQuestionType (), array_keys (QuestionInterface::ANSWERS_OPTIONS['SIMPLE_SELECTION'])) ||
				in_array ($askingFor->getQuestionType (), array_keys (QuestionInterface::ANSWERS_OPTIONS['MULTIPLE_CHOICE']))
			) {
				$graphicData = $this->getPieChartData($askingFor->getResponseOption ());
			} else if ($askingFor->getQuestionType () == 'SORT_SIMPLE' || $askingFor->getQuestionType () == 'SORT_MULTIPLE') {
				$graphicData = $this->getStackedBars ($askingFor, $summaryTable);
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ASKING_FOR', $askingFor);
			$smarty->assign ('CALCULATION', (!empty($askingFor->getCalculationType ())) ? QuestionInterface::CALCULATION_TYPE[$askingFor->getCalculationType ()] : null);
			$smarty->assign ('GRAPHIC_DATA', $graphicData);
			$smarty->assign ('QUESTION_TYPE', QuestionInterface::ANSWERS_OPTIONS[$askingFor->getQuestionForm ()][$askingFor->getQuestionType ()]);
			$smarty->assign ('SUMMARY_TABLE', isset($summaryTable) ? $summaryTable : null);
			$smarty->assign ('RECORD_ID', $crmId);
			$smarty->assign ('TOTAL_CATEGRIES', isset($totalCategories) ? $totalCategories : null);
			$smarty->assign ('VALUE_TYPE', (isset($valueType)) ? $valueType : null);
			return $smarty->fetch ("modules/answers/Answer_options/{$answerTemplate}");
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return SurveyProcessing
		 */
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
		
	}
