<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Data/EntityHistoryManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/utils.php');

	class RecordHistoryHelper {

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private static function getEntityFromPikList (PearDatabase $adb, $arguments) {
			$module          = $arguments ['module'];
			$dayFrom         = $arguments ['dayFrom'];
			$dayUntil        = $arguments ['dayTo'];
			$record          = $arguments ['record'];
			$sql             = $arguments ['sql'];
			$fieldNames      = $arguments ['fieldName'];
			$histories       = null;
			$allowedFields   = self::getFieldsList();
			$entityHistory   = EntityHistoryManager::getInstance($adb)->searchEntityHistory($record, $dayFrom, $dayUntil, $fieldNames, $sql);

			if ($entityHistory) {
				$filteredHistory = array();
				foreach ($entityHistory as $history) {
					if (in_array($history->getUiType(), $allowedFields)) {
						$filteredHistory[] = $history;
					}
				}
				if (!empty($filteredHistory)) {
					$histories [] = array(
						'type'         => 'entity',
						'relationship' => $module,
						'histories'    => $filteredHistory,
					);
				}
			}
			return $histories;
		}

		/**
		 * @param string $fields
		 * @param string $values
		 * @param string $operators
		 *
		 * @return string
		 */
		private static function getEquation ($fields, $values, $operators) {
			$equation   = '';
			$typeofdata = array (
				'V'  => array ('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"'),
				'N'  => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= '),
				'T'  => array ('e' => ' = "@"', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' * < DATE( "@" )', 'a' => ' * > DATE( "@" )'),
				'I'  => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= '),
				'C'  => array ('e' => ' = ', 'n' => ' != '),
				'D'  => array ('e' => ' = "@"', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' DATE( * ) < DATE( "@" )', 'a' => ' DATE( * ) > DATE( "@" )'),
				'DT' => array ('e' => ' = "@"', 'n' => ' != "@"', 'l' => ' < "@"', 'g' => ' > "@"', 'm' => ' <= "@"', 'h' => ' >= "@"', 'bw' => ' * BETWEEN DATE( "@" ) AND DATE( "_"  )', 'b' => ' * < DATE( "@" )', 'a' => ' * > DATE( "@" )'),
				'NN' => array ('e' => ' = ', 'n' => ' != ', 'l' => ' < ', 'g' => ' > ', 'm' => ' <= ', 'h' => ' >= '),
				'E'  => array ('e' => ' LIKE "@"', 'n' => ' NOT LIKE "@"', 's' => ' LIKE "@%"', 'ew' => ' LIKE "%@"', 'c' => ' LIKE "%@%"', 'k' => ' NOT LIKE "%@%"'),
			);

			list($fieldType, $fieldName) = explode ('@', $fields);
			$fieldName = 'crmu.' . $fieldName;
			list($min, $max) = explode (',', $values);

			$operated = $typeofdata[ $fieldType ][ $operators ];

			$posValue = strripos ($operated, '@');
			if ($posValue !== false) {
				$operated = str_replace ('@', $min, $operated);
				if (!empty($max)) {
					$operated = str_replace ('_', $max, $operated);
				}
			}
			$posField = strripos ($operated, '*');
			if ($posField !== false) {
				$equation .= str_replace ('*', $fieldName, $operated);
			} else if ($posValue === false) {
				$equation .= $fieldName . $operated . $min;
			} else {
				$equation .= $fieldName . $operated;
			}
			return $equation;
		}

		/**
		 *
		 * @return array
		 */
		private static function getFieldsList () {
			return array (
				FieldInterface::UI_TYPE_TEXT,
				FieldInterface::UI_TYPE_CODE,
				FieldInterface::UI_TYPE_DATE,
				FieldInterface::UI_TYPE_DATETIME,
				FieldInterface::UI_TYPE_NUMBER,
				FieldInterface::UI_TYPE_PERCENTAGE,
				FieldInterface::UI_TYPE_CURRENCY,
				FieldInterface::UI_TYPE_MODULE_REFERENCE,
				FieldInterface::UI_TYPE_PICKLIST,
				FieldInterface::UI_TYPE_GLOBAL_PICKLIST,
				FieldInterface::UI_TYPE_TEXTAREA,
				FieldInterface::UI_TYPE_OWNER,
				FieldInterface::UI_TYPE_CHECKBOX,
				FieldInterface::UI_TYPE_APP,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $tableName
		 *
		 * @return null|string|string[]
		 * @throws Exception
		 */
		private static function getMandatoryfield (PearDatabase $adb, $tableName) {
			$result     = $adb->pquery (
				'SELECT columnname FROM vtiger_field WHERE tablename=? AND typeofdata LIKE ? LIMIT 1',
				array ($tableName, '%~M~%')
			);
			$columnName = $adb->query_result ($result, 0, 'columnname');
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $columnName;
		}

		/**
		 * @param array $entityrelHistory
		 * @param array $activityrelHistory
		 * @param array $entityPikList
		 *
		 * @return array|null
		 */
		private static function getMergeArray ($entityrelHistory, $activityrelHistory, $entityPikList) {
			$result = array();
			
			if (!empty($entityPikList)) {
				$result = array_merge($result, $entityPikList);
			}
			if (!empty($entityrelHistory)) {
				$result = array_merge($result, $entityrelHistory);
			}
			if (!empty($activityrelHistory)) {
				$result = array_merge($result, $activityrelHistory);
			}
			
			return !empty($result) ? $result : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $recordId
		 *
		 * @return null|string
		 * @throws Exception
		 */
		private static function getModuleDataByRecord (PearDatabase $adb, $recordId) {
			$result = $adb->pquery ('SELECT setype FROM vtiger_crmentity WHERE crmid=?', array ($recordId));
			if ($adb->num_rows ($result) > 0) {
				$row    = $adb->fetchByAssoc ($result, -1, false);
				$entity = PlatformUtils::getCrmEntity ($adb, $row ['setype']);
				DatabaseUtils::closeResult ($result);
				if (!empty ($entity)) {
					$returnField = self::getMandatoryfield ($adb, $entity->table_name);
					$result      = $adb->query ("SELECT * FROM {$entity->table_name} WHERE 1 AND {$entity->table_index} = {$recordId}");
					$row         = $adb->fetchByAssoc ($result, -1, false);
					$moduleData  = $row [ $returnField ];
				} else {
					$moduleData = null;
				}
			} else {
				$moduleData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $moduleData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param string $moduleName
		 *
		 * @return string
		 */
		private static function getRelHistoryTitle ($adb, $entityId, $moduleName) {
			$title  = '';
			$entity = PlatformUtils::getCrmEntity ($adb, $moduleName);
			if($entity) {
				$codField = $entity->list_fields_name ['Código'];
				$codValues  = $adb->run_query_allrecords("SELECT {$codField} FROM {$entity->table_name} WHERE {$entity->table_index} = {$entityId}");
				$title = $codValues [0][ $codField ];
			}
			return $title;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return null|string
		 * @throws Exception
		 */
		private static function getUsersById (PearDatabase $adb, $userId) {
			$user = UserManager::getInstance ($adb, null)->fetchUserById ($userId, true);
			if (!empty ($user)) {
				$userName = ucwords (strtolower ($user->getFirstName ()) . ' ' . strtolower ($user->getLastName ()));
			} else {
				$userName = null;
			}
			return $userName;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $userName
		 *
		 * @return null|integer
		 * @throws Exception
		 */
		private static function getUsersByName (PearDatabase $adb, $userName) {
			$userName = "%{$userName}%";
			$result   = $adb->pquery ('SELECT * FROM vtiger_users WHERE first_name LIKE ? OR last_name LIKE ? LIMIT 1', array ($userName, $userName));
			if ($adb->num_rows ($result) > 0) {
				$row    = $adb->fetchByAssoc ($result, -1, false);
				$userId = $row ['id'];
			} else {
				$userId = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $userId;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $row
		 *
		 * @throws Exception
		 */
		private static function humanizeData (PearDatabase $adb, &$row) {
			if ($row ['uitype'] == FieldInterface::UI_TYPE_OWNER) {
				$row ['oldvalue'] = self::getUsersById ($adb, $row ['oldvalue']);
				$row ['newvalue'] = self::getUsersById ($adb, $row ['newvalue']);
			} else if ($row ['uitype'] == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
				$row ['oldvalue'] = self::getModuleDataByRecord ($adb, $row ['oldvalue']);
				$row ['newvalue'] = self::getModuleDataByRecord ($adb, $row ['newvalue']);
			} else if ($row ['uitype'] == FieldInterface::UI_TYPE_CHECKBOX) {
				$row ['oldvalue'] = ($row ['oldvalue']) ? 'Si' : 'No';
				$row ['newvalue'] = ($row ['newvalue']) ? 'Si' : 'No';
			}
		}

		/**
		 * @return array
		 */
		public static function getDefinedGraphTypes () {
			return array (
				'GRAPH_TYPE_BARS'   => 'Barras',
				'GRAPH_TYPE_POINTS' => 'Puntos',
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getColumnsByModule (PearDatabase $adb, $moduleName) {
			$presenceValues = array (FieldInterface::PRESENCE_VISIBLE, FieldInterface::PRESENCE_USER_DEFINED);
			$sqlData        = array ($moduleName);
			$uitypeValues   = array (
				FieldInterface::UI_TYPE_MODULE_REFERENCE,
				FieldInterface::UI_TYPE_PHONE,
				FieldInterface::UI_TYPE_EMAIL,
				FieldInterface::UI_TYPE_URL,
				FieldInterface::UI_TYPE_MODIFIED_BY,
				FieldInterface::UI_TYPE_CREATED_TIME,
				FieldInterface::UI_TYPE_IMAGE_REFERENCE,
				FieldInterface::UI_TYPE_GRID,
			);

			$sqlValues = array_merge ($presenceValues, $uitypeValues, $sqlData);

			$result = $adb->pquery (
				'SELECT
					f.fieldid,
					f.fieldname,
					f.fieldlabel,
					f.tablename,
					f.uitype,
					f.typeofdata
				FROM
					vtiger_field f
					INNER JOIN vtiger_blocks b ON f.block=b.blockid AND b.visible=0 AND b.display_status=1
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					f.presence IN (?, ?) AND
					f.uitype NOT IN (?, ?, ?, ?, ?, ?, ?, ?) AND
					t.name=?',
				$sqlValues
			);
			if ($adb->num_rows ($result) > 0) {
				$columns = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$fieldtype  = explode ('~', $row ['typeofdata']);
					$columns [] = array (
						'fieldname'  => $row ['fieldname'],
						'label'      => html_entity_decode (getTranslatedString ($row ['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8'),
						'tablename'  => $row ['tablename'],
						'uitype'     => $row ['uitype'],
						'typeofdata' => $fieldtype[0],
						'fieldid'    => $row ['fieldid'],
					);
				}
				usort (
					$columns,
					function ($columnA, $columnB) {
						return strcmp ($columnA ['label'], $columnB ['label']);
					}
				);
			} else {
				$columns = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $columns;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $fields
		 * @param array $fieldData
		 * @param array $values
		 *
		 * @throws Exception
		 */
		public static function getFieldDataType (PearDatabase $adb, &$fields, $fieldData, &$values) {
			$totalFields = count ($fields);
			foreach ($fieldData as $field) {
				for ($k = 0; $k < $totalFields; $k++) {
					if ($fields[ $k ] == $field['fieldname']) {
						$fields[ $k ] = $field['typeofdata'] . '@' . $fields[ $k ];
						if ($field['uitype'] == FieldInterface::UI_TYPE_OWNER) {
							$values[ $k ] = self::getUsersByName ($adb, $values[ $k ]);
						} else if ($field['uitype'] == FieldInterface::UI_TYPE_CHECKBOX) {
							$values[ $k ] = ($values[ $k ] == 'Si') ? 1 : 0;
						}
					}
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getHistoryDataFromModule (PearDatabase $adb, $arguments) {
			$formodule = $arguments ['module'];
			$dayFrom   = $arguments ['dayFrom'];
			$dayUntil  = $arguments ['dayTo'];
			$record    = $arguments ['record'];
			$sql       = $arguments ['sql'];
			$fieldIds  = (!empty($arguments ['fieldIds'])) ? " crmu.field IN ({$arguments ['fieldIds']}) AND " : '';

			$module = ModuleManager::getInstance ($adb)->fetchModule ($formodule, true);
			if (empty ($module)) {
				return null;
			}

			$dateRange = (!empty($dayFrom) && !empty($dayUntil)) ? "AND (DATE(crmu.date) BETWEEN STR_TO_DATE('{$dayFrom}','%Y-%m-%d') AND STR_TO_DATE('{$dayUntil}','%Y-%m-%d'))" : '';
			$where     = " {$dateRange} AND ({$sql})";
			$result    = $adb->pquery (
				"SELECT
					crmu.crmentityid,
					crmu.module,
					crmu.field,
					crmu.oldvalue,
					crmu.newvalue,
					crmu.modifiedby,
					crmu.modifiedon,
					crmu.date,
					b.user_name,
					b.first_name,
					b.last_name,
					c.tabid,
					c.name,
					c.tablabel,
					d.fieldname,
					d.fieldlabel,
					d.uitype
			  	FROM
					vtiger_crmentityutils AS crmu
			  		INNER JOIN  vtiger_users AS b ON crmu.modifiedby = b.id
					INNER JOIN  vtiger_tab AS c ON crmu.module = c.tabid
					INNER JOIN  vtiger_field AS d ON crmu.field = d.fieldid
				WHERE
					{$fieldIds}
					registryid=? AND
					c.tabid=?
					{$where}",
				array ($record, $module->getId ())
			);
			if ($adb->num_rows ($result) > 0) {
				$rows = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					self::humanizeData ($adb, $row);
					$rows [] = $row;
				}
			} else {
				$rows = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rows;
		}

		/**
		 * @param PearDatabase $adb
		 * @param $arguments
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getHistoryGraphicsData (PearDatabase $adb, $arguments) {
			$dayFrom     = $arguments ['dayFrom'];
			$dayUntil    = $arguments ['dayTo'];
			$record      = $arguments ['record'];
			$sql         = $arguments ['sql'];
			$fieldNames  = $arguments ['fieldName'];
			$dataGraphic = array ();

			$field = FieldManager::getInstance ($adb)->fetchFieldByName ($arguments['module'], $fieldNames[0], true);
			if ($field->getUiType () == FieldInterface::UI_TYPE_PICKLIST) {
				$pickListValues = array ();
			}

			$histories = EntityHistoryManager::getInstance ($adb)->searchEntityHistory ($record, $dayFrom, $dayUntil, $fieldNames, $sql);
			if (!empty ($histories)) {
				foreach ($histories as $history) {
					if (isset ($pickListValues)) {
						if ((!in_array ($history->getNewValue (), array_keys ($pickListValues)))) {
							$pickListValues [ $history->getNewValue () ] = 1;
						} else {
							$pickListValues [ $history->getNewValue () ] += 1;
						}
					} else {
						$dataGraphic [] = array (
							$history->getFieldName () => $history->getCreatedDate (),
							'contador'                => $history->getNewValue (),
						);
					}
				}
				if (empty ($dataGraphic) && isset($pickListValues)) {
					foreach ($pickListValues as $key => $value) {
						$dataGraphic [] = array (
							$field->getName () => $key,
							'contador'         => $value,
						);
					}
				}
			}
			return $dataGraphic;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 *
		 * @return array|string
		 * @throws Exception
		 */
		public static function getHistoricalRelatedEvents (PearDatabase $adb, $arguments) {
			$dayFrom              = $arguments ['dayFrom'];
			$dayUntil             = $arguments ['dayTo'];
			$record               = $arguments ['record'];
			$sql                  = $arguments ['sql'];
			$fieldNames           = $arguments ['fieldName'];
			$current_language     = $arguments ['language'];
			$relatedHistoryEvents = null;
			$allowedFields        = self::getFieldsList();

			$monthES = array ('ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC');
			$monthEN = array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

			$ehm                = EntityHistoryManager::getInstance ($adb);
			$entityrelHistory   = $ehm->fetchEntityRelationshipHistory ($record, $dayFrom, $dayUntil, $fieldNames, $sql);
			$activityrelHistory = $ehm->fetchTaskToRelatedRecord ($record, $dayFrom, $dayUntil, $fieldNames, $sql);
			$entityPikList      = self::getEntityFromPikList($adb, $arguments);
			$dataRelHistory     = self::getMergeArray($entityrelHistory, $activityrelHistory, $entityPikList);

			foreach ($dataRelHistory as $item) {
				foreach ($item ['histories'] as $historyObject) {
					if (in_array ($historyObject->getUiType (),$allowedFields)) {
						$translators = return_module_language($current_language, $historyObject->getModuleName ());
						$thisTitle   = (($item ['type'] == 'crmentityrel') || ($item ['type'] == 'entity')) ? self::getRelHistoryTitle ($adb,  $historyObject->getRegistryId (), $item ['relationship']) : $item ['relationship'];
						$month       = date('M', strtotime ($historyObject->getCreatedDate ()));

						$row = array(
							'id'          => $historyObject->getId(),
							'createdDay'  => date ('d-m-Y H:i:s', strtotime($historyObject->getCreatedDate ())),
							'day'         => date ('d', strtotime ($historyObject->getCreatedDate ())),
							'field'       => ($historyObject->getUiType () == FieldInterface::UI_TYPE_OWNER) ? 'Asignado a' : $historyObject->getFieldLabel (),
							'module'      => $historyObject->getModuleName (),
							'modulelabel' => $historyObject->getModuleLabel (),
							'month'       => str_replace ($monthEN, $monthES, $month),
							'newvalue'    => (!empty ($translators [ $historyObject->getNewValue () ])) ? $translators [ $historyObject->getNewValue () ] : $historyObject->getNewValue (),
							'oldvalue'    => (!empty ($translators [ $historyObject->getOldValue () ])) ? $translators [ $historyObject->getOldValue () ] : $historyObject->getOldValue (),
							'record'      => $historyObject->getRegistryId (),
							'title'       => $thisTitle,
							'type'        => $item ['type'],
							'uitype'      => $historyObject->getUiType (),
							'userName'    => $historyObject->getUserName (),
						);
						self::humanizeData ($adb, $row);
						$relatedHistoryEvents [] = $row;
					}
				}
			}
			usort (
				$relatedHistoryEvents,
				function ($a, $b) {
					return ($a ['id'] <= $b ['id']) ? 1 : -1;
				}
			);
			return $relatedHistoryEvents;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $filterData
		 *
		 * @return string
		 * @throws Exception
		 */
		public static function getSqlFilter (PearDatabase $adb, $filterData) {
			$fields      = $filterData ['filterField'];
			$operators   = $filterData ['filterOperator'];
			$values      = $filterData ['filterValue'];
			$joins       = $filterData ['filterJoin'];
			$groupJoins  = $filterData ['filterGroupJoin'];
			$moduleGraph = $filterData ['moduleFilter'];
			$grupoIndex  = $filterData ['indexGrupo'];

			$fieldData = self::getColumnsByModule ($adb, $moduleGraph);
			self::getFieldDataType ($adb, $fields, $fieldData, $values);

			$totalOperations = count ($fields);
			$totalGroup      = count ($groupJoins);
			$myGroup         = $grupoIndex[0];
			$nextOper        = 0;
			$equation        = '( ';
			$indexGroup      = 0;
			$indexJoin       = 0;

			if ($totalOperations > 0) {
				for ($op = 0; $op < $totalOperations; $op++) {
					$equation .= self::getEquation ($fields[ $op ], $values[ $op ], $operators[ $op ]);
					$nextOper = ($nextOper < $totalOperations) ? ($nextOper + 1) : $op;
					if ($grupoIndex[ $nextOper ] != $myGroup) {
						$myGroup = $grupoIndex[ $nextOper ];
						if ($op < ($totalOperations - 1)) {
							$equation .= ' )';
							if ($indexGroup == 0) {
								$equation = '( ' . $equation;
							}
							$equation = $equation . ' ) ' . $groupJoins[ $indexGroup ] . ' ( ( ';
							$indexGroup++;
						} else if ($totalGroup > 0) {
							$equation = $equation . ' ))';
						} else {
							$equation = $equation . ' )';
						}
					} else {
						if ($op < ($totalOperations - 1)) {
							$equation = $equation . ' ) ' . $joins[ $indexJoin ] . ' ( ';
							$indexJoin++;
						} else {
							if ($indexGroup == 0) {
								$equation = $equation . ' ) ';
							} else {
								$equation = $equation . ' ) )';
							}
						}
					}
				}
				return $equation;
			} else {
				return '';
			}
		}

	}
