<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/ImageUtils.class.php');
	require_once ('modules/Settings/Objects/HelpField.php');
	require_once ('include/platzilla/Objects/Field.php');

	abstract class HelpSettingsHelper {
		const TUTORIAL_TYPE_ARTICLE = 'ARTICLE';
		const TUTORIAL_TYPE_VIDEO   = 'VIDEO';

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		private static function fetchApplicationModuleFields (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?', array ($moduleName));
			if ($adb->num_rows ($result) > 0) {
				$fields = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['fieldlabel']            = getTranslatedString ($row ['fieldlabel'], $moduleName);
					$fields [ $row ['fieldname'] ] = $row;
				}
				uasort (
					$fields,
					function ($fieldA, $fieldB) {
						if ($fieldA ['fieldlabel'] < $fieldB ['fieldlabel']) {
							return -1;
						} else if ($fieldA ['fieldlabel'] == $fieldB ['fieldlabel']) {
							return 0;
						} else {
							return 1;
						}
					}
				);
			} else {
				$fields = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fields;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $applicationId
		 *
		 * @return array|null
		 */
		private static function fetchApplicationModules (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery (
				'SELECT
					t.*
				FROM
					vtiger_tab t
					INNER JOIN vtiger_configapps_tab cat ON cat.tabid=t.tabid
				WHERE
					t.isentitytype=1 AND
					t.presence IN (0, 2) AND
					cat.config_applicationsid=?',
				array ($applicationId)
			);
			if ($adb->num_rows ($result) > 0) {
				$modules = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$row ['fields']            = self::fetchApplicationModuleFields ($adb, $row ['name']);
					$modules [ $row ['name'] ] = $row;
				}
				uasort (
					$modules,
					function ($moduleA, $moduleB) {
						if ($moduleA ['tablabel'] < $moduleB ['tablabel']) {
							return -1;
						} else if ($moduleA ['tablabel'] == $moduleB ['tablabel']) {
							return 0;
						} else {
							return 1;
						}
					}
				);
			} else {
				$modules = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $modules;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $categoryName
		 *
		 * @return string
		 */
		private static function fetchHelpCategoryDescription (PearDatabase $adb, $categoryName) {
			$result = $adb->pquery ('SELECT * FROM vtiger_help_categories WHERE category=?', array ($categoryName));
			if ($adb->num_rows ($result) > 0) {
				$row         = $adb->fetchByAssoc ($result, -1, false);
				$description = $row ['description'];
			} else {
				$description = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $description;
		}

		/**
		 * @param string $url
		 *
		 * @return string
		 */
		private static function getYouTubeIdFromUrl ($url) {
			if (stristr ($url, 'youtu.be/')) {
				preg_match ('/(http:|https:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $finalId);
				return $finalId [4];
			} else {
				preg_match ('/(http:|https:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $finalId);
				return $finalId [5];
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $codId
		 * @param string$newStatus
		 *
		 * @throws Exception
		 */
		public static function changeEditableFieldHelp ($adb, $codId, $newStatus) {
			if (!is_numeric ($codId) || empty($codId)) {
				throw new Exception ('Imposible actualizar, Error en datos!');
			}
			$adb->pquery ('UPDATE vtiger_help_fields SET editablefield=? WHERE helpfieldid=?',array ($newStatus, $codId));
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $codId
		 * @param string $newStatus
		 *
		 * @throws Exception
		 */
		public static function changeStatusFieldHelp ($adb, $codId, $newStatus) {
			if (!is_numeric ($codId) || empty($codId)) {
				throw new Exception ('Imposible actualizar, Error en datos!');
			}
			$adb->pquery ('UPDATE vtiger_help_fields SET status=? WHERE helpfieldid=?',array ($newStatus, $codId));
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $codId
		 *
		 * @throws Exception
		 */
		public static function deleteFieldHelp ($adb, $codId) {
			if (!is_numeric ($codId) || empty($codId)) {
				throw new Exception ('Imposible eliminar ayuda!');
			}
			$adb->pquery ('DELETE FROM vtiger_help_fields WHERE helpfieldid=?', array ($codId));
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $tutorialId
		 */
		public static function deleteHelpConfigurationTutorial (PearDatabase $adb, $tutorialId) {
			if (empty ($tutorialId)) {
				return;
			}
			$adb->pquery ('DELETE FROM vtiger_help_configuration WHERE tutorialid=?', array ($tutorialId));
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $questionId
		 */
		public static function deleteHelpQuestion (PearDatabase $adb, $questionId) {
			if (empty ($questionId)) {
				return;
			}
			$adb->pquery ('DELETE FROM vtiger_help_questions WHERE questionid=?', array ($questionId));
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $tipId
		 */
		public static function deleteHelpTip (PearDatabase $adb, $tipId) {
			if (empty ($tipId)) {
				return;
			}
			$adb->pquery ('DELETE FROM vtiger_help_tips WHERE tipid=?', array ($tipId));
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $tutorialId
		 */
		public static function deleteHelpTutorial (PearDatabase $adb, $tutorialId) {
			if (empty ($tutorialId)) {
				return;
			}
			$adb->pquery ('DELETE FROM vtiger_help_tutorials WHERE tutorialid=?', array ($tutorialId));
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $useCaseId
		 */
		public static function deleteHelpUseCase (PearDatabase $adb, $useCaseId) {
			if (empty ($useCaseId)) {
				return;
			}
			$adb->pquery ('DELETE FROM vtiger_help_usecases WHERE usecaseid=?', array ($useCaseId));
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchApplications (PearDatabase $adb) {
			$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_status=? ORDER BY app_name', array ('Activa'));
			if ($adb->num_rows ($result) > 0) {
				$applications = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['modules']                    = self::fetchApplicationModules ($adb, $row ['config_applicationsid']);
					$applications [ $row ['app_code'] ] = $row;
				}
			} else {
				$applications = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $applications;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchAvailableBlockNames (PearDatabase $adb) {
			$result = $adb->query (
				"SELECT
					IFNULL(f.tab, '') AS tabname,
					f.name AS blockname,
					b.label AS sectionname
				FROM
					vtiger_settings_field f
					INNER JOIN vtiger_settings_blocks b ON b.blockid=f.blockid
				ORDER BY
					f.sequence"
			);
			if ($adb->num_rows ($result) > 0) {
				$blockNames = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$tabName                                   = !empty ($row ['tabname']) ? $row ['tabname'] : '';
					$blockNames [ $row ['sectionname'] ][ $tabName ][ $row ['blockname'] ] = Translator::translate ($row ['blockname'], 'Settings');
				}
			} else {
				$blockNames = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $blockNames;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchAvailableSectionNames (PearDatabase $adb) {
			$result = $adb->query ('SELECT * FROM vtiger_settings_blocks ORDER BY sequence');
			if ($adb->num_rows ($result) > 0) {
				$sectionNames = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$sectionNames [ $row ['label'] ] = Translator::translate ($row ['label'], 'Settings');
				}
			} else {
				$sectionNames = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $sectionNames;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return null|Module[]
		 */
		public static function fetchAvailableModule ($adb) {
			$modules = ModuleManager::getInstance ($adb)->fetchModulesByType (1, true, false);
			foreach ($modules as $module) {
				if (($module->getPresence () < 0) || $module->getSequence () < 0) {
					continue;
				}
				$availableModules [] = $module;
			}
			return (isset($availableModules)) ? $availableModules : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param Module[]|null $modules
		 *
		 * @return Field[]
		 * @throws Exception
		 */
		public static function fetchAvailableFieldByModules ($adb, $modules = null) {
			if (empty ($modules)) {
				$modules = self::fetchAvailableModule ($adb);
			}
			foreach ($modules as $module) {
				$result = $adb->pquery (
					'SELECT f.*, t.name AS modulename FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
					array($module->getName ())
				);
				if ($adb->num_rows ($result) > 0) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$fields[ $module->getName () ][] = Field::getInstance ()
							->setId (intval ($row ['fieldid']))
							->setLabel ($row ['fieldlabel'])
							->setLocked ($row ['locked'] == 1)
							->setName ($row ['fieldname'])
							->setPresence (intval ($row ['presence']))
							->setReadOnly (intval ($row ['readonly']))
							->setSequence (intval ($row ['sequence']))
							->setUiType ($row ['uitype']);
					}
				}
			}
			return (isset($fields)) ? $fields : null;
		}
		
		/**
		 * @param array $applications
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		public static function fetchFieldHelpItems ($applications, $moduleName) {
			if (empty ($applications)) {
				return null;
			}

			$applicationCodes = array ();
			foreach ($applications as $application) {
				$applicationCodes [] = $application ['app_code'];
			}

			$adb           = AdbManager::getInstance ()->getMasterAdb ();
			$questionMarks = str_repeat ('?, ', (count ($applicationCodes) - 1)) . '?';
			$result        = $adb->pquery (
				"SELECT
					q.*
				FROM
					vtiger_help_questions q
				WHERE
					q.applicationcode IN ({$questionMarks}) AND
					q.modulename=?",
				array_merge ($applicationCodes, array ($moduleName))
			);
			if ($adb->num_rows ($result) > 0) {
				$items = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$items [ $row ['fieldname'] ] = array (
						'id'  => "question-{$row ['questionid']}",
						'tab' => 'tab-faq',
					);
				}
			} else {
				$items = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $items;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return HelpField[]|null
		 * @throws Exception
		 */
		public static function fetchHelpField ($adb) {
			$result = $adb->query (
				'SELECT
						*
					  FROM
					  	vtiger_help_fields hf
					  INNER JOIN
					  	vtiger_field f ON f.fieldname = hf.fieldname
					  INNER JOIN
					  	vtiger_tab t ON t.name = hf.tabname AND t.presence != -1
					  GROUP BY helpfieldid'
			);
			if ($adb->num_rows ($result) > 0) {
				$helpFields = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$helpFields [] = HelpField::getInstance ()
						->setId ($row ['helpfieldid'])
						->setDescription ($row ['description'])
						->setFieldLabel ($row ['fieldlabel'])
						->setFieldName ($row ['fieldname'])
						->setIsEditable ($row ['editablefield'])
						->setImage ($row ['image'])
						->setModuleLabel ($row ['tablabel'])
						->setModuleName ($row ['tabname'])
						->setStatus ($row ['status'])
						->setTitle ($row ['title'])
						->setUiType ($row ['uitype'])
						->setUrlVideo ($row ['urlvideo'])
						->setVideoType ($row ['videotype']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($helpFields)) ? $helpFields : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $helpFieldId
		 * @param string|null $moduleName
		 * @param PearDatabase|null $adbInstance
		 *
		 * @return null|HelpField
		 * @throws Exception
		 */
		public static function fetchHelpFieldById ($adb, $helpFieldId, $moduleName = null, $adbInstance = null) {
			if (empty($helpFieldId) || !is_numeric ($helpFieldId)) {
				throw new Exception ('Ayuda de campo no encontrada!');
			}
			$motherDbase   = $adb->dbName;
			$daughterDbase = (!empty($adbInstance)) ? $adbInstance->dbName : $motherDbase;
			$whereModule   = (!empty($moduleName)) ? " AND t.name='{$moduleName}'" : '';
			$result = $adb->pquery (
				"SELECT
						*
					  FROM
						 {$motherDbase}.vtiger_help_fields hf
					  INNER JOIN
						 {$daughterDbase}.vtiger_field f ON f.fieldname = hf.fieldname
					  INNER JOIN
						  {$daughterDbase}.vtiger_tab t ON t.name = hf.tabname AND t.presence != -1 AND f.tabid = t.tabid
					  WHERE
						  helpfieldid=?
						  {$whereModule}
					  GROUP BY
						  helpfieldid",
				array ($helpFieldId)
				);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$helpField = HelpField::getInstance ()
					->setId ($row ['helpfieldid'])
					->setDescription ($row ['description'])
					->setFieldId ($row ['fieldid'])
					->setFieldLabel ($row ['fieldlabel'])
					->setFieldName ($row ['fieldname'])
					->setIsEditable ($row ['editablefield'])
					->setImage ($row ['image'])
					->setModuleLabel ($row ['tablabel'])
					->setModuleName ($row ['tabname'])
					->setStatus ($row ['status'])
					->setTitle ($row ['title'])
					->setUiType ($row ['uitype'])
					->setUrlVideo ($row ['urlvideo'])
					->setVideoType ($row ['videotype']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			
			return (isset($helpField)) ? $helpField : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return HelpField[]|null
		 * @throws Exception
		 */
		public static function fetchHelpFieldByModule ($adb, $moduleName) {
			if (empty($moduleName) || !is_scalar ($moduleName)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT
						hf.*,
    					f.fieldlabel,
    					f.uitype,
    					t.tablabel
					  FROM
					  	vtiger_help_fields hf
					  INNER JOIN
					  	vtiger_field f ON f.fieldname = hf.fieldname
					  INNER JOIN
					  	vtiger_tab t ON t.name = hf.tabname AND t.presence != -1
					  WHERE hf.tabname=? AND status=?
					  GROUP BY helpfieldid',
				array ($moduleName, 'ENABLED')
			);
			if ($adb->num_rows ($result) > 0) {
				$helpFields = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$helpFields [] = HelpField::getInstance ()
						->setId ($row ['helpfieldid'])
						->setDescription ($row ['description'])
						->setFieldLabel ($row ['fieldlabel'])
						->setFieldName ($row ['fieldname'])
						->setIsEditable ($row ['editablefield'])
						->setImage ($row ['image'])
						->setModuleLabel ($row ['tablabel'])
						->setModuleName ($row ['tabname'])
						->setStatus ($row ['status'])
						->setTitle ($row ['title'])
						->setUiType ($row ['uitype'])
						->setUrlVideo ($row ['urlvideo'])
						->setVideoType ($row ['videotype']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($helpFields)) ? $helpFields : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function fetchHelpCategories (PearDatabase $adb) {
			$result = $adb->query ('SELECT * FROM vtiger_help_categories');
			if ($adb->num_rows ($result) > 0) {
				$categories = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$categories [ $row ['category'] ] = $row ['description'];
				}
			} else {
				$categories = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $categories;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $tutorialId
		 *
		 * @return array|null
		 */
		public static function fetchHelpConfiguration (PearDatabase $adb, $tutorialId) {
			if (empty ($tutorialId)) {
				return null;
			}

			$result = $adb->pquery ('SELECT c.* FROM vtiger_help_configuration c WHERE c.tutorialid=?', array ($tutorialId));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				if ($row ['tutorialtype'] == self::TUTORIAL_TYPE_VIDEO) {
					$youTubeVideoId    = self::getYouTubeIdFromUrl ($row ['url']);
					$row ['urliframe'] = "https://www.youtube.com/embed/{$youTubeVideoId}?rel=0";
				} else {
					$row ['urliframe'] = $row ['url'];
				}
			} else {
				$row = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $row;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array
		 */
		public static function fetchHelpConfigurations (PearDatabase $adb) {
			$result = $adb->query ('SELECT c.* FROM vtiger_help_configuration c ORDER BY c.title');
			if ($adb->num_rows ($result) > 0) {
				$tutorials = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['tutorialtype'] == self::TUTORIAL_TYPE_VIDEO) {
						$youTubeVideoId    = self::getYouTubeIdFromUrl ($row ['url']);
						$row ['urliframe'] = "https://www.youtube.com/embed/{$youTubeVideoId}?rel=0";
					} else {
						$row ['urliframe'] = $row ['url'];
					}
					$tutorials [] = $row;
				}
			} else {
				$tutorials = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $tutorials;
		}

		/**
		 * @param PearDatabase $adb
		 * @param $questionId
		 *
		 * @return array|null
		 */
		public static function fetchHelpQuestion (PearDatabase $adb, $questionId) {
			if (empty ($questionId)) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					q.questionid AS id,
					q.applicationcode,
					q.title,
					q.answer AS description,
					q.modulename,
					q.fieldname,
					q.tags
				FROM
					vtiger_help_questions q
				WHERE
					q.questionid=?',
				array ($questionId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
			} else {
				$row = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $row;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array
		 */
		public static function fetchHelpQuestions (PearDatabase $adb) {
			$result = $adb->query (
				'SELECT
					q.questionid AS id,
					q.applicationcode,
					q.title,
					q.answer AS description,
					q.modulename,
					q.fieldname
				FROM
					vtiger_help_questions q
				ORDER BY
					q.applicationcode, q.title'
			);
			if ($adb->num_rows ($result) > 0) {
				$questions = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$applicationCode                  = $row ['applicationcode'];
					$questions [ $applicationCode ][] = $row;
				}
			} else {
				$questions = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return array (
				'description' => self::fetchHelpCategoryDescription ($adb, 'questions'),
				'items'       => $questions,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $applicationCodes
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		public static function fetchHelpQuestionsByModuleName (PearDatabase $adb, $applicationCodes = null, $moduleName = null) {
			if (empty ($applicationCodes)) {
				return null;
			}

			$questionMarks = str_repeat ('?, ', (count ($applicationCodes) - 1)) . '?';
			$whereClauses  = array ("q.applicationcode IN ({$questionMarks})");
			$arguments     = $applicationCodes;
			if (!empty ($moduleName)) {
				$whereClauses [] = '(q.modulename IS NULL OR q.modulename=?)';
				$arguments []    = $moduleName;
			} else {
				$whereClauses [] = 'q.modulename IS NULL';
			}
			$whereClause = 'WHERE ' . join (' AND ', $whereClauses);

			$result = $adb->pquery (
				"SELECT
					q.questionid AS id,
					q.applicationcode,
					q.title,
					q.answer AS description,
					q.modulename,
					q.fieldname,
					q.tags
				FROM
					vtiger_help_questions q
				{$whereClause}
				ORDER BY
					q.applicationcode, q.title",
				$arguments
			);
			if ($adb->num_rows ($result) > 0) {
				$questions = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$questions [] = $row;
				}
			} else {
				$questions = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return array (
				'description' => self::fetchHelpCategoryDescription ($adb, 'questions'),
				'items'       => $questions,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $tipId
		 *
		 * @return array|null
		 */
		public static function fetchHelpTip (PearDatabase $adb, $tipId) {
			if (empty ($tipId)) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					t.tipid AS id,
					t.title,
					t.description,
					t.tags
				FROM
					vtiger_help_tips t
				WHERE
					t.tipid=?',
				array ($tipId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
			} else {
				$row = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $row;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array
		 */
		public static function fetchHelpTips (PearDatabase $adb) {
			$result = $adb->query (
				'SELECT
					t.tipid AS id,
					t.title,
					t.description,
					t.tags
				FROM
					vtiger_help_tips t
				ORDER BY
					t.title'
			);
			if ($adb->num_rows ($result) > 0) {
				$tips = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$tips [] = $row;
				}
			} else {
				$tips = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return array (
				'description' => self::fetchHelpCategoryDescription ($adb, 'tips'),
				'items'       => $tips,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $tutorialId
		 *
		 * @return array|null
		 */
		public static function fetchHelpTutorial (PearDatabase $adb, $tutorialId) {
			if (empty ($tutorialId)) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					t.tutorialid AS id,
					t.category,
					t.tags,
					t.title,
					t.tutorialtype,
					t.url,
					t.applicationcodes
				FROM
					vtiger_help_tutorials t
				WHERE
					t.tutorialid=?',
				array ($tutorialId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row                      = $adb->fetchByAssoc ($result, -1, false);
				$row ['applicationcodes'] = json_decode ($row ['applicationcodes'], true);
				if ($row ['tutorialtype'] == self::TUTORIAL_TYPE_VIDEO) {
					$youTubeVideoId    = self::getYouTubeIdFromUrl ($row ['url']);
					$row ['urliframe'] = "https://www.youtube.com/embed/{$youTubeVideoId}?rel=0";
				} else {
					$row ['urliframe'] = $row ['url'];
				}
			} else {
				$row = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $row;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array
		 */
		public static function fetchHelpTutorials (PearDatabase $adb) {
			$result = $adb->query (
				'SELECT
					t.tutorialid AS id,
					t.category,
					t.tags,
					t.title,
					t.tutorialtype,
					t.url,
					t.applicationcodes
				FROM
					vtiger_help_tutorials t
				ORDER BY
					t.title'
			);
			if ($adb->num_rows ($result) > 0) {
				$tutorials = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['applicationcodes'] = json_decode ($row ['applicationcodes'], true);
					if ($row ['tutorialtype'] == self::TUTORIAL_TYPE_VIDEO) {
						$youTubeVideoId    = self::getYouTubeIdFromUrl ($row ['url']);
						$row ['urliframe'] = "https://www.youtube.com/embed/{$youTubeVideoId}?rel=0";
					} else {
						$row ['urliframe'] = $row ['url'];
					}
					$tutorials [] = $row;
				}
			} else {
				$tutorials = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return array (
				'description' => self::fetchHelpCategoryDescription ($adb, 'tutorials'),
				'items'       => $tutorials,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $useCaseId
		 *
		 * @return array|null
		 */
		public static function fetchHelpUseCase (PearDatabase $adb, $useCaseId) {
			if (empty ($useCaseId)) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					uc.usecaseid AS id,
					uc.category,
					uc.tags,
					uc.title,
					uc.url,
					uc.applicationcodes
				FROM
					vtiger_help_usecases uc
				WHERE
					uc.usecaseid=?',
				array ($useCaseId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row                      = $adb->fetchByAssoc ($result, -1, false);
				$row ['applicationcodes'] = json_decode ($row ['applicationcodes'], true);
			} else {
				$row = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $row;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array
		 */
		public static function fetchHelpUseCases (PearDatabase $adb) {
			$result = $adb->query (
				'SELECT
					uc.usecaseid AS id,
					uc.category,
					uc.tags,
					uc.title,
					uc.url,
					uc.applicationcodes
				FROM
					vtiger_help_usecases uc
				ORDER BY
					uc.title'
			);
			if ($adb->num_rows ($result) > 0) {
				$useCases = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['applicationcodes'] = json_decode ($row ['applicationcodes'], true);
					$useCases []              = $row;
				}
			} else {
				$useCases = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return array (
				'description' => self::fetchHelpCategoryDescription ($adb, 'usecases'),
				'items'       => $useCases,
			);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 *
		 * @throws Exception|null
		 */
		public static function saveHelpConfiguration (PearDatabase $adb, $arguments) {
			$id    = $arguments ['id'];
			$title = $arguments ['title'];
			if (!empty ($id)) {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_configuration WHERE title=? AND tutorialid<>?', array ($title, $id));
			} else {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_configuration WHERE title=?', array ($title));
			}
			if ($adb->num_rows ($result) > 0) {
				$e = new Exception ("Ya está registrado un tutorial con el título \"{$title}\"");
			} else {
				$e = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			if ($e instanceof Exception) {
				throw $e;
			}

			if (empty ($id)) {
				$adb->pquery (
					'INSERT INTO vtiger_help_configuration (tutorialtype, title, url, sectionname, tabname, blockname) VALUES (?, ?, ?, ?, ?, ?)',
					array ($arguments ['type'], $title, $arguments ['url'], $arguments ['sectionname'], $arguments ['tabname'], $arguments ['blockname'])
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_help_configuration SET tutorialtype=?, title=?, url=?, sectionname=?, tabname=?, blockname=? WHERE tutorialid=?',
					array ($arguments ['type'], $title, $arguments ['url'], $arguments ['sectionname'], $arguments ['tabname'], $arguments ['blockname'], $id)
				);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param HelpField $helpField
		 *
		 * @throws Exception
		 */
		public static function saveHelpField ($adb, $helpField) {
			if (! $helpField instanceof HelpField) {
				throw new Exception ('Imposible guardar ayuda!');
			}
			if (empty ($helpField->getId ())) {
				$adb->pquery (
					'INSERT INTO vtiger_help_fields (fieldname, title, description, urlvideo, videotype, tabname, editablefield, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
					array ($helpField->getFieldName (), $helpField->getTitle (), $helpField->getDescription (), $helpField->getUrlVideo (), $helpField->getVideoType (), $helpField->getModuleName (), $helpField->isEditable (), $helpField->getStatus ())
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_help_fields SET fieldname=?, title=?, description=?, urlvideo=?, videotype=?, tabname=?, editablefield=?, status=? WHERE helpfieldid=?',
					array ($helpField->getFieldName (), $helpField->getTitle (), $helpField->getDescription (), $helpField->getUrlVideo (), $helpField->getVideoType (),  $helpField->getModuleName (), $helpField->isEditable (), $helpField->getStatus (), $helpField->getId ())
				);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 *
		 * @throws Exception|null
		 */
		public static function saveHelpQuestion (PearDatabase $adb, $arguments) {
			$id              = $arguments ['id'];
			$applicationCode = $arguments ['applicationcode'];
			$title           = $arguments ['title'];
			if (!empty ($id)) {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_questions WHERE applicationcode=? AND title=? AND questionid<>?', array ($applicationCode, $title, $id));
			} else {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_questions WHERE applicationcode=? AND title=?', array ($applicationCode, $title));
			}
			if ($adb->num_rows ($result) > 0) {
				$e = new Exception ("Ya está registrada la pregunta frecuente \"{$title}\" para la aplicación \"{$applicationCode}\"");
			} else {
				$e = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			if ($e instanceof Exception) {
				throw $e;
			}

			$fieldName  = !empty ($arguments ['fieldname']) ? $arguments ['fieldname'] : null;
			$moduleName = !empty ($arguments ['modulename']) ? $arguments ['modulename'] : null;
			if (empty ($id)) {
				$adb->pquery ('INSERT INTO vtiger_help_questions (applicationcode, title, answer, modulename, fieldname, tags) VALUES (?, ?, ?, ?, ?, ?)', array ($applicationCode, $title, $arguments ['description'], $moduleName, $fieldName, $arguments ['tags']));
			} else {
				$adb->pquery ('UPDATE vtiger_help_questions SET applicationcode=?, title=?, answer=?, modulename=?, fieldname=?, tags=? WHERE questionid=?', array ($applicationCode, $title, $arguments ['description'], $moduleName, $fieldName, $arguments ['tags'], $id));
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 *
		 * @throws Exception|null
		 */
		public static function saveHelpTip (PearDatabase $adb, $arguments) {
			$id    = $arguments ['id'];
			$title = $arguments ['title'];
			if (!empty ($id)) {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_tips WHERE title=? AND tipid<>?', array ($title, $id));
			} else {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_tips WHERE title=?', array ($title));
			}
			if ($adb->num_rows ($result) > 0) {
				$e = new Exception ("Ya está registrado un tip con el título \"{$title}\"");
			} else {
				$e = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			if ($e instanceof Exception) {
				throw $e;
			}

			if (empty ($id)) {
				$adb->pquery ('INSERT INTO vtiger_help_tips (title, description, tags) VALUES (?, ?, ?)', array ($title, $arguments ['description'], $arguments ['tags']));
			} else {
				$adb->pquery ('UPDATE vtiger_help_tips SET title=?, description=?, tags=? WHERE tipid=?', array ($title, $arguments ['description'], $arguments ['tags'], $id));
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 *
		 * @throws Exception|null
		 */
		public static function saveHelpTutorial (PearDatabase $adb, $arguments) {
			$id    = $arguments ['id'];
			$title = $arguments ['title'];
			if (!empty ($id)) {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_tutorials WHERE title=? AND tutorialid<>?', array ($title, $id));
			} else {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_tutorials WHERE title=?', array ($title));
			}
			if ($adb->num_rows ($result) > 0) {
				$e = new Exception ("Ya está registrado un tutorial con el título \"{$title}\"");
			} else {
				$e = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			if ($e instanceof Exception) {
				throw $e;
			}

			if (empty ($id)) {
				$adb->pquery (
					'INSERT INTO vtiger_help_tutorials (tutorialtype, category, title, url, applicationcodes, tags) VALUES (?, ?, ?, ?, ?, ?)',
					array ($arguments ['type'], $arguments ['category'], $title, $arguments ['url'], json_encode ($arguments ['applicationcodes']), $arguments ['tags'])
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_help_tutorials SET tutorialtype=?, category=?, title=?, url=?, applicationcodes=?, tags=? WHERE tutorialid=?',
					array ($arguments ['type'], $arguments ['category'], $title, $arguments ['url'], json_encode ($arguments ['applicationcodes']), $arguments ['tags'], $id)
				);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 *
		 * @throws Exception|null
		 */
		public static function saveHelpUseCase (PearDatabase $adb, $arguments) {
			$id    = $arguments ['id'];
			$title = $arguments ['title'];
			if (!empty ($id)) {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_usecases WHERE title=? AND usecaseid<>?', array ($title, $id));
			} else {
				$result = $adb->pquery ('SELECT * FROM vtiger_help_usecases WHERE title=?', array ($title));
			}
			if ($adb->num_rows ($result) > 0) {
				$e = new Exception ("Ya está registrado un caso de uso con el título \"{$title}\"");
			} else {
				$e = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			if ($e instanceof Exception) {
				throw $e;
			}

			if (empty ($id)) {
				$adb->pquery (
					'INSERT INTO vtiger_help_usecases (category, title, url, applicationcodes, tags) VALUES (?, ?, ?, ?, ?)',
					array ($arguments ['category'], $title, $arguments ['url'], json_encode ($arguments ['applicationcodes']), $arguments ['tags'])
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_help_usecases SET category=?, title=?, url=?, applicationcodes=?, tags=? WHERE usecaseid=?',
					array ($arguments ['category'], $title, $arguments ['url'], json_encode ($arguments ['applicationcodes']), $arguments ['tags'], $id)
				);
			}
		}

	}
