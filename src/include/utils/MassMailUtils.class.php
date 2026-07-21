<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/SystemVariables.class.php');
	require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');
	
	abstract class MassMailUtils 	{
		
		private static  $unAvailableModules = array (
			'answers',
			'daily_report',
			'etapas_proyecto',
			'key_result',
			'management_mechanisms',
			'platzi_issabel',
			'predefined_initiatives',
			'process_cases',
			'process_steps',
			'questionnaire',
			'reportes',
			'risk_control_actions',
		);
		
		/**
		 * @param array $row
		 *
		 * @return array|null
		 */
		private static function getTemplateVariableNames ($row) {
			$subject = htmlspecialchars_decode ($row ['subject'], ENT_QUOTES);
			$body    = htmlspecialchars_decode ($row ['body'], ENT_QUOTES);
			$result  = preg_match_all ("'<var>(.*?)</var>'si", "{$subject}\n{$body}", $matches);
			
			if (!$result) {
				return null;
			}
			$variables = array ();
			foreach ($matches [1] as $match) {
				$variables [ $match ] = $match;
			}
			$variables = array_unique ($variables);
			asort ($variables);
			return $variables;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string       $formula
		 * @param array        $dataSourceValues
		 *
		 * @return string
		 */
		public static function evaluateVariables ($adb, $formula, $dataSourceValues) {
			$availableVariables = SystemVariables::getAvailableVariableValues ($adb, $dataSourceValues);
			foreach ($availableVariables as $variableName => $variableValue) {
				$formula = str_replace ('{' . $variableName . '}', $variableValue, $formula);
			}
			
			if (!empty ($dataSourceValues)) {
				foreach ($dataSourceValues as $parameterName => $parameterValue) {
					$formula = str_replace ("|{$parameterName}|", $parameterValue, $formula);
				}
			}
			
			return $formula;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchAvailableModules ($adb) {
			$result = $adb->run_query_allrecords (
				'SELECT
       				tabid,
       				name,
       				tablabel
				FROM `vtiger_tab`
				WHERE presence IN(0,2) AND
				    tabsequence != -1 AND
				    isplatzilla = 0 AND
				    isvisibleinadmin = 1
				ORDER BY  tablabel ASC');
			if (count ($result)) {
				foreach ($result as $moduleData) {
					if (in_array ($moduleData['name'], self::$unAvailableModules)) {
						continue;
					}
					$availableModules[] = array(
						'label' => $moduleData ['tablabel'],
						'value' => "{$moduleData ['name']}@{$moduleData ['tabid']}",
					);
				}
			}
			return (isset($availableModules)) ? $availableModules : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchEmailManagerTemplates ($adb) {
			$result = $adb->query ('SELECT * FROM vtiger_emailmanager_templates ORDER BY templatename, language');
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$templates = array();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$templateName = $row ['templatename'];
					$language = $row ['language'];
					$variables = self::getTemplateVariableNames ($row);
					$templates [] = array(
						'templateid'   => intval ($row ['templateid']),
						'templatename' => $row ['templatename'],
						'language'	    => $row ['language'],
						'variables'    => !empty ($variables) ? join (';',(array_keys ($variables))) : null,
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($templates)) ? $templates : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchFieldsByModule ($adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
       				fieldname,
       				fieldlabel
				FROM vtiger_field f
				INNER JOIN vtiger_tab t ON f.tabid = t.tabid
				WHERE t.name=?',
				array($moduleName)
			);
			if ($adb->num_rows ($result) > 0) {
				$fields = array();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$fields [$row ['fieldname']] = getTranslatedString ($row ['fieldlabel'], $moduleName);
				}
				asort ($fields);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($fields)) ? $fields : null;
		}
		
		/**
		 * @return string[]
		 */
		public static function getAvailableLanguages() {
			return array('es' => 'Español', 'en' => 'Ingles');
			//, 'pt' => 'Portuguese'
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $tempalteId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getEmailManagerTemplatesById ($adb, $tempalteId) {
			if (!is_numeric ($tempalteId) || empty($tempalteId)) {
				throw new Exception ('Invalid template id');
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_emailmanager_templates WHERE templateid=?', array ($tempalteId));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$variables = self::getTemplateVariableNames ($row);
				$template = array(
					'templateid'   => intval ($row ['templateid']),
					'templatename' => $row ['templatename'],
					'language'	   => $row ['language'],
					'subject'      => $row ['subject'],
					'body'         => $row ['body'],
					'variables'    => !empty ($variables) ? join (';',(array_keys ($variables))) : null,
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($template)) ? $template : null;
		}
		
		/**
		 * @param string       $relatedModulo
		 * @param integer	  $realtedRecord
		 * @param string       $fieldName
		 *
		 * @return string|null
		 */
		public static function getVariablesFromSourceModule ($relatedModulo, $realtedRecord, $fieldName) {
			if (empty ($relatedModulo) || empty ($realtedRecord) || empty ($fieldName)) {
				return null;
			}
			$dummy  = explode ('@', $relatedModulo);
			$entity = CRMEntity::getInstance ($dummy[0]);
			$entity->retrieve_entity_info ($realtedRecord, $dummy[0]);
			$dataSourceValues = $entity->column_fields [$fieldName];
			unset ($entity);
			return $dataSourceValues;
		}
		
	}
