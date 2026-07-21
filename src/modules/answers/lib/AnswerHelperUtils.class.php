<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/answers/handlers/SurveyProcessing.class.php');
	
	abstract class AnswerHelperUtils {
		
		const HEADER_RECORD = array (
			'NO_OPEN' => array (
				'Cod Cuestionario',
				'Fecha de encuesta',
				'ID Respuesta',
				'Cod Respuesta',
				'Pregunta',
				'ID Cliente',
				'Respuesta',
				'Respuesta (valor)',
				'ID Cuestionario',
				'Cuestionario',
			),
			'OPEN' => array (
				'Cod Cuestionario',
				'Fecha de encuesta',
				'ID Cuestionario',
				'Cuestionario',
				'Pregunta',
				'Grupo',
				'Tema',
				'Respuesta',
			),
		);
		
		const HEADER_QUESTIONNAIRE = array (
			'Cod Cuestionario',
			'Fecha de encuesta',
			'ID Cuestionario',
			'Cuestionario',
			'Grupo',
			'Etapa',
			'ID Respuesta',
			'Cod Respuesta',
			'ID Cliente',
			'Forma de la pregunta',
			'Tipo de pregunta',
			'Pregunta',
			'Respuesta',
			'Respuesta (valor)',
		);
		
		/**
		 * @param PearDatabase $adb
		 * @param string $temporaryTable
		 *
		 * @throws Exception
		 */
		private static  function createTempTable ($adb, $temporaryTable) {
			$adb->query (
				"CREATE TEMPORARY TABLE IF NOT EXISTS `{$temporaryTable}` (
					`username` varchar(150) NULL,
					`codename` varchar(65) NULL,
					KEY `username` (`username`)
				) ENGINE=InnoDB AUTO_INCREMENT=1  DEFAULT CHARSET=utf8"
			);
			
			$result = $adb->query ('SELECT DISTINCT username,  UPPER(SUBSTRING(TRIM(username),1, 1)) AS initial FROM vtiger_answers WHERE 1');
			if ($adb->num_rows ($result) > 0) {
				$index = 10;
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$codeUser = "{$row ['initial']}-{$index}";
					
					$adb->pquery ("INSERT INTO {$temporaryTable} (username, codename) VALUES (?, ?)",
						array ($row['username'], $codeUser)
					);
					$index += 10;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $idQuestionnaire
		 * @param integer $idAskingFor
		 * @param null|integer $limit
		 * @param string $returnType
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public static function getExportOpenQuestionData ($adb, $idQuestionnaire, $idAskingFor, $limit = null, $returnType = 'STRING') {
			$tableName = 'vtiger_export_' . rand (100, 1000);
			self::createTempTable ($adb, $tableName);
			$sqlLimit = (!empty($limit)) ? 'LIMIT  ' . $limit : '';
			$result = $adb->pquery (
				"SELECT
						a.cod_survey,
						a.surveydate,
						qq.cod_questionnaire,
						qq.name,
						a.question,
						a.questiongroup,
						a.questionstage,
						REPLACE(a.useranswer, '</br>',';') AS user_answere
					  FROM vtiger_answers a
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = a.answersid AND crm.deleted=0
					  INNER JOIN vtiger_questionnaire qq ON qq.questionnaireid = a.questionnaire
					  INNER JOIN vtiger_question q ON q.questionnairesid = a.questionnaire AND q.question = a.question
					  WHERE
					  	questionnaire=? AND
					  	questionid=?
					  ORDER BY user_answere ASC
					  {$sqlLimit}",
				array ($idQuestionnaire, $idAskingFor)
			);
			if ($adb->num_rows ($result) > 0) {
				$setData = '';
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($returnType == 'STRING') {
						$rowData = '';
						foreach ($row as $value) {
							$rowData .= '"' . strip_tags ($value) . '"' . "\t";
						}
						$setData .= trim ($rowData) . "\n";
					} else {
						$setData [] = $row;
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($setData)) ? $setData : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $idQuestionnaire
		 * @param integer $idAskingFor
		 *
		 * @return null|string
		 * @throws Exception
		 */
		public static function getExportRecordData ($adb, $idQuestionnaire, $idAskingFor) {
			$tableName = 'vtiger_export_' . rand (100, 1000);
			self::createTempTable ($adb, $tableName);
			$result = $adb->pquery (
				"SELECT
						a.cod_survey,
						a.surveydate,
						a.answersid,
						a.cod_answers,
						a.question,
						(SELECT codename FROM {$tableName} WHERE username = a.username) AS user_code,
						REPLACE(a.useranswer, '</br>','; ') AS user_answere,
						IF(q.question_form = 'MULTIPLE_CHOICE',ExtractSum(CONCAT(a.answervalue,';')),a.answervalue) AS num,
						qq.cod_questionnaire,
						qq.name
					  FROM vtiger_answers a
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = a.answersid AND crm.deleted=0
					  INNER JOIN vtiger_questionnaire qq ON qq.questionnaireid = a.questionnaire
					  INNER JOIN vtiger_question q ON q.questionnairesid = a.questionnaire  AND q.question = a.question
					  WHERE
					  	questionnaire=? AND
					  	questionid=?",
				array ($idQuestionnaire, $idAskingFor)
			);
			if ($adb->num_rows ($result) > 0) {
				$setData = '';
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$rowData = '';
					foreach ($row as $value) {
						$rowData .= '"' . strip_tags ($value) . '"' . "\t";
					}
					$setData .= trim ($rowData) . "\n";
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($setData)) ? $setData : null;
		}
		/**
		 * @param PearDatabase $adb
		 * @param integer $questionId
		 * @param integer $anwerValue
		 *
		 * @throws Exception
		 * @return null|string
		 */
		public static function getFeedbackGroup ($adb, $questionId, $anwerValue) {
			if (empty($questionId) || empty($anwerValue)) {
				return null;
			}
			$result = $adb->pquery ('SELECT feedback_range FROM vtiger_question2group_range WHERE ? BETWEEN minimum AND maximum AND questionid=? ',array ($anwerValue, $questionId));
			if ($adb->num_rows ($result) > 0) {
				$feedBackRange = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$feedBackRange [] = $row ['feedback_range'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($feedBackRange)) ? join ('<br>', $feedBackRange) : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $idQuestionnaire
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public static function getExportQuestionnaireData ($adb, $idQuestionnaire) {
			$tableName = 'vtiger_export_' . rand (100, 1000);
			self::createTempTable ($adb, $tableName);
			$result = $adb->pquery (
				"SELECT
						a.cod_survey,
						a.surveydate,
						qq.cod_questionnaire,
						qq.name,
						a.questiongroup AS groupQ,
						a.questionstage AS stageQ,
						a.answersid,
						a.cod_answers,
						(SELECT codename FROM {$tableName} WHERE username = a.username) AS user_code,
						q.question_form,
						q.question_type,
						a.question,
						REPLACE(a.useranswer, '</br>','; ') AS user_answere,
						IF(q.question_form = 'MULTIPLE_CHOICE',ExtractSum(CONCAT(a.answervalue,';')),a.answervalue) AS num
					  FROM vtiger_answers a
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = a.answersid AND crm.deleted=0
					  INNER JOIN vtiger_questionnaire qq ON qq.questionnaireid = a.questionnaire
					  INNER JOIN vtiger_question q ON q.questionnairesid = a.questionnaire  AND q.question = a.question
					  WHERE
					  	questionnaire=?",
				array ($idQuestionnaire)
			);
			if ($adb->num_rows ($result) > 0) {
				$setData = '';
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$rowData = '';
					foreach ($row as $value) {
						$rowData .= '"' . strip_tags ($value) . '"' . "\t";
					}
					$setData .= trim ($rowData) . "\n";
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($setData)) ? $setData : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $id
		 * @param string $type
		 * @param string $photo
		 */
		public static function saveImages ($adb, $id, $type, $photo) {
			$adb->pquery (
				'INSERT INTO vtiger_question2image (queston2answereid, imagetype, image)
					VALUES (?, ?, ?)',
				array ($id, $type, $photo)
			);
		}
		
	}
