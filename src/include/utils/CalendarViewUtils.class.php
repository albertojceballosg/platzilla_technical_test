<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Managers/CalendarViewManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/utils/CommonUtils.php');

	abstract class CalendarViewUtils {
		const RECORDS_PER_PAGE = 25;
		
		private static $processedRuleIds = array();
		
		private static function getAvailableModuleFields (PearDatabase $adb, $moduleName) {
			if (!$moduleName) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					f.*,
					t.name AS modulename
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					t.presence IN (0, 2) AND
					t.name=?
				ORDER BY
					f.fieldlabel',
				array ($moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['fieldlabel'] = getTranslatedString ($row ['fieldlabel'], $moduleName);
				$fields []          = $row;
			}
			return $fields;
		}

		private static function getCalendarViewApplicationCodes (PearDatabase $adb, $viewId) {
			if (empty ($viewId)) {
				throw new Exception ('No se ha suministrado el ID de la vista');
			}

			$result = $adb->pquery ('SELECT cva.* FROM vtiger_calendarviews_applications cva WHERE cva.calendarviewid=?', array ($viewId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$applicationCodes = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$applicationCodes [] = $row ['applicationcode'];
			}
			return $applicationCodes;
		}

		private static function getCalendarViewRules (PearDatabase $adb, $viewId) {
			if (empty ($viewId)) {
				throw new Exception ('No se ha suministrado el ID de la vista');
			}

			$result = $adb->pquery (
				'SELECT
					cvr.*,
					t.tablabel,
					f.fieldlabel,
					f.uitype
				FROM
					vtiger_calendarviews_rules cvr
					INNER JOIN vtiger_tab t ON t.name=cvr.modulename
					INNER JOIN vtiger_field f ON f.tabid=t.tabid AND f.fieldname=cvr.fieldname
				WHERE
					cvr.calendarviewid=?
				ORDER BY
					cvr.ruleid',
				array ($viewId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$rules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row['value'] = trim ($row['value']);
				$rules []     = $row;
			}
			return $rules;
		}

		private static function getCalendarViewsWhereClausesByApplications ($applications) {
			if (!empty ($applications)) {
				$applicationCodes = array_keys ($applications);
				$questionMarks    = str_repeat ('?, ', (count ($applicationCodes) - 1)) . '?';
				$whereClauses     = array ("cv.calendarviewid IN (SELECT cva.calendarviewid FROM vtiger_calendarviews_applications cva WHERE cva.applicationcode IN ({$questionMarks}))");
				$arguments        = $applicationCodes;
			} else {
				$whereClauses = array ();
				$arguments    = array ();
			}

			return array (
				'where'     => $whereClauses,
				'arguments' => $arguments,
			);
		}

		private static function getCalendarViewsWhereClausesByKeyword ($keyword) {
			if (!empty ($keyword)) {
				$whereClauses = array ('(cv.label LIKE ? OR cv.modulename LIKE ?)');
				$arguments    = array ("%{$keyword}%", "%{$keyword}%");
			} else {
				$whereClauses = array ();
				$arguments    = array ();
			}

			return array (
				'where'     => $whereClauses,
				'arguments' => $arguments,
			);
		}

		private static function getCalendarViewsWhereClausesByModuleName ($moduleNames) {
			if (!empty ($moduleNames)) {
				$questionMarks = str_repeat ('?, ', (count ($moduleNames) - 1)) . '?';
				$whereClauses  = array ("t.name IN ({$questionMarks})");
				$arguments     = $moduleNames;
			} else {
				$whereClauses = array ();
				$arguments    = array ();
			}

			return array (
				'where'     => $whereClauses,
				'arguments' => $arguments,
			);
		}

		private static function getColorsFromRules ($view, $record) {
			if (!isset ($view ['rules'])) {
				return array (
					'background' => $view ['backgroundcolor'],
					'foreground' => '#000000',
				);
			}
			
			$totalRules = count ($view ['rules']);
			for ($k = 0; $k < $totalRules; $k++) {
				$theRules = self::getRulesToCheck ($view ['rules']);
				$background = null;
				$foreground = '#000000';
				foreach ($theRules as $rule) {
					$found[] = self::recordMeetsRule ($record, $rule);
					$joinRule[] = $rule['joinrule'];
					if (! $found && false) {
						continue;
					}
					if (empty ($background)) {
						$background = $rule ['backgroundcolor'];
					}
				}
				$totalFound = count ($found);
				$lastFound  = $found [0];
				if ($totalFound > 1) {
					for ($i = 1; $i > $totalFound; $i++) {
						if ($joinRule[($i - 1)] == 'AND') {
							$lastFound = ($found [$i] && $lastFound);
						} else {
							$lastFound = ($found[$i] || $lastFound);
						}
					}
				}
				if ($lastFound) {
					self::$processedRuleIds = array();
					return array(
						'background' => $background,
						'foreground' => $foreground,
					);
				} else {
					unset($found);
					unset($joinRule);
				}
			}
			self::$processedRuleIds = array();
			return array(
				'background' => '#FFFFFF',
				'foreground' => $foreground,
			);
		}

		private static function getMainModuleSqlClauses ($mainModuleSqlData) {
			$selectClauses = array ();
			$whereClauses  = array ();
			if (!empty ($mainModuleSqlData ['fieldnames'])) {
				foreach ($mainModuleSqlData ['fieldnames'] as $index => $fieldName) {
					$selectClauses [] = $fieldName;
					if ($mainModuleSqlData ['required'][ $index ]) {
						$whereClauses [] = "{$fieldName} IS NOT NULL";
					}
				}
			}

			return array (
				'select' => $selectClauses,
				'where'  => $whereClauses,
			);
		}

		private static function getModuleRelatedSqlData (PearDatabase $adb, $view) {
			$joinClauses    = array (
				'(en.modulename=? AND f.fieldname=?)',
				'(en.modulename=? AND f.fieldname=?)',
				'(en.modulename=? AND f.fieldname=?)',
				'(en.modulename=? AND f.fieldname=?)',
			);
			$joinArguments  = array ($view ['titlemodulename'], $view ['titlefieldname'], $view ['titlemodulename'], $view ['subtitlefieldname'], $view ['frommodulename'], $view ['fromfieldname'], $view ['tomodulename'], $view ['tofieldname']);
			$whereArguments = array ($view ['modulename'], $view ['titlemodulename'], $view ['subtitlefieldname'], $view ['frommodulename'], $view ['tomodulename']);
			if (!empty ($view ['rules'])) {
				foreach ($view ['rules'] as $rule) {
					$joinClauses []    = '(en.modulename=? AND f.fieldname=?)';
					$joinArguments []  = $rule ['modulename'];
					$joinArguments []  = $rule ['fieldname'];
					$whereArguments [] = $rule ['modulename'];
				}
			}
			$questionMarks = str_repeat ('?, ', (count ($whereArguments) - 1)) . '?';
			$arguments     = array_merge ($joinArguments, $whereArguments);
			$joinClause    = join (' OR ', $joinClauses);
			$result        = $adb->pquery (
				"SELECT DISTINCT
					en.tablename,
					en.entityidfield,
					en.modulename,
					f.uitype,
					f.fieldname,
					f.tablename AS fieldtablename
				FROM
					vtiger_entityname en
					LEFT JOIN vtiger_field f ON f.tabid=en.tabid AND ({$joinClause})
				WHERE
					en.modulename IN ({$questionMarks})
				ORDER BY
					IF(en.modulename=?, 1, 0) DESC",
				array_merge ($arguments, array ($view ['modulename']))
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$sqlData = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				if (!isset ($sqlData [ $row ['modulename'] ])) {
					$sqlData [ $row ['modulename'] ] = array (
						'tablename'       => $row ['tablename'],
						'entityidfield'   => $row ['entityidfield'],
						'referencedfield' => null,
						'referencedalias' => null,
						'fieldnames'      => array (),
						'required'        => array (),
					);
				}
				if (empty ($row ['fieldname'])) {
					$sqlData [ $row ['modulename'] ]['fieldnames'] = null;
					$sqlData [ $row ['modulename'] ]['required']   = null;
				} else if ($row ['uitype'] == 10) {
					$sqlData ['clientes']                 = array (
						'tablename'       => 'vtiger_clientes',
						'entityidfield'   => 'clientesid',
						'referencedfield' => "{$row ['tablename']}.{$row ['fieldname']}",
						'referencedalias' => $row ['fieldname'],
						'fieldnames'      => array (),
						'required'        => array (),
					);
					$sqlData ['clientes']['fieldnames'][] = "vtiger_clientes.alias";
					if (
						(($row ['modulename'] == $view ['titlemodulename']) && ($row ['fieldname'] == $view ['titlefieldname'])) ||
						(($row ['modulename'] == $view ['titlemodulename']) && ($row ['fieldname'] == $view ['subtitlefieldname'])) ||
						(($row ['modulename'] == $view ['frommodulename']) && ($row ['fieldname'] == $view ['fromfieldname'])) ||
						(($row ['modulename'] == $view ['tomodulename']) && ($row ['fieldname'] == $view ['tofieldname']))
					) {
						$sqlData ['clientes']['required'][] = true;
					} else {
						$sqlData ['clientes']['required'][] = false;
					}
				} else {
					if (($row ['fieldname'] == 'assigned_user_id') && ($row ['fieldtablename'] == 'vtiger_crmentity')) {
						$sqlData [ $row ['modulename'] ]['fieldnames'][] = "{$row ['fieldtablename']}.smownerid";
					} else {
						$sqlData [ $row ['modulename'] ]['fieldnames'][] = "{$row ['fieldtablename']}.{$row ['fieldname']}";
					}
					
					if (
						(($row ['modulename'] == $view ['titlemodulename']) && ($row ['fieldname'] == $view ['titlefieldname'])) ||
						(($row ['modulename'] == $view ['titlemodulename']) && ($row ['fieldname'] == $view ['subtitlefieldname'])) ||
						(($row ['modulename'] == $view ['frommodulename']) && ($row ['fieldname'] == $view ['fromfieldname'])) ||
						(($row ['modulename'] == $view ['tomodulename']) && ($row ['fieldname'] == $view ['tofieldname']))
					) {
						$sqlData [ $row ['modulename'] ]['required'][] = true;
					} else {
						$sqlData [ $row ['modulename'] ]['required'][] = false;
					}
				}
			}
			return $sqlData;
		}

		private static function getNonAdminUserFromClause ($viewModuleName, $currentUser) {
			/** @var CRMEntity $entity */
			$entity = CRMEntity::getInstance ($viewModuleName);
			return !is_admin ($currentUser) ? array ($entity->getNonAdminAccessControlQuery ($viewModuleName, $currentUser)) : array ();
		}

		private static function getReferencedModuleSqlClauses ($viewModuleName, $currentUser, $mainModuleSqlData, $relatedSqlData) {
			$selectClauses = array ();
			$fromClauses   = array ();
			$whereClauses  = array ();
			if (!empty ($relatedSqlData)) {
				foreach ($relatedSqlData as $moduleName => $moduleSqlData) {
					if (!$moduleSqlData ['referencedfield']) {
						continue;
					}

					/** @var CRMEntity $entity */
					$entity = CRMEntity::getInstance ($moduleName);

					$fromClauses [] = "LEFT JOIN {$moduleSqlData ['tablename']} ON {$moduleSqlData ['tablename']}.{$moduleSqlData ['entityidfield']}={$moduleSqlData ['referencedfield']}";
					if (!is_admin ($currentUser)) {
						$fromClauses [] = $entity->getNonAdminAccessControlQuery ($viewModuleName, $currentUser);
					}

					if (empty ($moduleSqlData ['fieldnames'])) {
						continue;
					}

					foreach ($moduleSqlData ['fieldnames'] as $index => $fieldName) {
						$selectClauses [] = "{$fieldName} AS {$moduleSqlData ['referencedalias']}";
						if ($mainModuleSqlData ['required'][ $index ]) {
							$whereClauses [] = "{$fieldName} IS NOT NULL";
						}
					}
				}
			}

			return array (
				'select' => $selectClauses,
				'from'   => $fromClauses,
				'where'  => $whereClauses,
			);
		}

		private static function getRelatedDataSqlClauses ($viewModuleName, $currentUser, $mainModuleSqlData, $relatedSqlData) {
			$selectClauses = array ();
			$fromClauses   = array ();
			$whereClauses  = array ();
			if (!empty ($relatedSqlData)) {
				foreach ($relatedSqlData as $moduleName => $moduleSqlData) {
					if ($moduleSqlData ['referencedfield']) {
						continue;
					}

					/** @var CRMEntity $entity */
					$entity = CRMEntity::getInstance ($moduleName);

					$fromClauses [] = "LEFT JOIN {$moduleSqlData ['tablename']} ON {$moduleSqlData ['tablename']}.{$moduleSqlData ['entityidfield']}=vtiger_crmentityrel.relcrmid";
					if (!is_admin ($currentUser)) {
						$fromClauses [] = $entity->getNonAdminAccessControlQuery ($viewModuleName, $currentUser);
					}

					if (empty ($moduleSqlData ['fieldnames'])) {
						continue;
					}

					foreach ($moduleSqlData ['fieldnames'] as $index => $fieldName) {
						$selectClauses [] = $fieldName;
						if ($mainModuleSqlData ['required'][ $index ]) {
							$whereClauses [] = "{$fieldName} IS NOT NULL";
						}
					}
				}
			}

			return array (
				'select' => $selectClauses,
				'from'   => $fromClauses,
				'where'  => $whereClauses,
			);
		}

		private static function getRuleById ($rules, $ruleId) {
			if (empty ($ruleId)) {
				return null;
			}

			$selectedRule = null;
			foreach ($rules as $rule) {
				if ($rule ['ruleid'] == $ruleId) {
					$selectedRule = $rule;
					break;
				}
			}
			return $selectedRule;
		}

		private static function recordMeetsRule ($record, $rule) {
			if (empty ($rule)) {
				return true;
			}

			$fieldName = ($rule ['fieldname'] == 'assigned_user_id') ? 'smownerid' : $rule ['fieldname'];
			$operator  = $rule ['operator'];

			if (!isset ($record [ $fieldName ])) {
				return false;
			}

			$value = self::substituteVariables ($rule ['value']);
			switch ($operator) {
				case '=':
					$result = ($record [ $fieldName ] == $value);
					break;
				case '<':
					$result = ($record [ $fieldName ] < $value);
					break;
				case '<=':
					$result = ($record [ $fieldName ] <= $value);
					break;
				case '>':
					$result = ($record [ $fieldName ] > $value);
					break;
				case '=>':
					$result = ($record [ $fieldName ] >= $value);
					break;
				case '!=':
					$result = ($record [ $fieldName ] != $value);
					break;
				default:
					$result = false;
					break;
			}

			return $result;
		}

		private static function substituteVariables ($value) {
			$availableVariables = array (
				'NOW'      => date ('Y-m-d H:i:s'),
				'NOW_ES'   => date ('d/m/Y h:i:s a'),
				'TODAY'    => date ('Y-m-d'),
				'TODAY_ES' => date ('d/m/Y'),
			);
			foreach ($availableVariables as $variableName => $variableValue) {
				$value = str_replace ('{' . $variableName . '}', $variableValue, $value);
			}

			return $value;
		}
		
		private static function getFullFieldName ($fields, $search) {
			foreach ($fields as $field) {
				$dummy = explode ('.', $field);
				if ($search == $dummy[1]) {
					if ($search == 'assigned_user_id') {
						$search = "{$dummy[0]}.smownerid";
					} else {
						$search = $field;
					}
					break;
				} else if ($search == 'assigned_user_id') {
					$search = 'vtiger_crmentity.smownerid';
					break;
				}
				
			}
			return $search;
		}
		
		private static function getRulesToCheck ($theRules) {
			$rules = array ();
			$ruleId = null;
			if (empty(self::$processedRuleIds)) {
				foreach ($theRules as $rule) {
					if (empty($ruleId)) {
						$rules []                  = $rule;
						$ruleId                    = $rule ['ruleid'];
						self::$processedRuleIds [] = $rule ['ruleid'];
					} else if ($rule['ruleid'] == $ruleId) {
						$rules[] = $rule;
					}
					continue;
				}
			} else {
				foreach ($theRules as $rule) {
					if (in_array ($rule['ruleid'], self::$processedRuleIds)) {
						continue;
					}
					if (empty ($ruleId)) {
						$rules[] = $rule;
						$ruleId  = $rule['ruleid'];
					} else if ($rule['ruleid'] == $ruleId) {
						$rules[] = $rule;
					}
					continue;
				}
				if (!empty ($ruleId)) {
					self::$processedRuleIds [] = $ruleId;
				}
				
			}
			return $rules;
		}
		
		private static function getWhereClausesFromRules ($view, $ruleId, $mainModuleSqlData) {
			if (empty ($ruleId) || empty($mainModuleSqlData)) {
				return '';
			}
			$fields     = $mainModuleSqlData['fieldnames'];
			$sqlResult  = null;
			$clauses    = array ();
			$rulesIndex = 1;
			$joinRule   = null;
			$thisRules  = array();
			foreach ($view ['rules'] as $rule) {
				if ($rule ['ruleid'] == $ruleId) {
					$thisRules[] = $rule;
					$fieldName   = self::getFullFieldName ($fields, $rule['fieldname']);
					$operator    = $rule['operator'];
					$value       = self::substituteVariables (trim ($rule ['value']));
					$quotes      = (!is_numeric ($value)) ? "'" : '';
					if ($rulesIndex %2 == 0) {
						$clauses[] = $fieldName . $operator . $quotes . $value . $quotes;
						$joinRule  = " {$thisRules[($rulesIndex-2)]['joinrule']} ";
						if (empty ($sqlResult)) {
							$sqlResult = ' ('.  join ($joinRule, $clauses) . ') ';
						} else {
							$joinRule = " {$thisRules[($rulesIndex-2)]['joinrule']} ";
							$sqlResult = $sqlResult . $joinRule. '( '. join ($joinRule, $clauses) . ') ';
						}
						unset ($clauses);
					} else {
						$clauses[] = $fieldName . $operator . $quotes . $value . $quotes;
					}
					$rulesIndex++;
				}
			}
			
			$joinRule = " {$thisRules[(count($thisRules)-2)]['joinrule']} ";
			if (empty ($sqlResult) && count ($clauses)) {
				$sqlResult = " AND ({$clauses[0]})";
			} else if (!empty ($sqlResult) && count ($clauses)) {
				$sqlResult = ' AND (('.  $sqlResult . ')' . $joinRule . '('. $clauses[0] . '))';
			} else {
				$sqlResult = " AND ({$sqlResult})";
			}
			return $sqlResult;
		}
		
		public static function deleteView (PearDatabase $adb, $viewId) {
			if (empty ($viewId)) {
				throw new Exception ('No se ha suministrado el ID de la vista a eliminar');
			}

			$adb->pquery ('DELETE FROM vtiger_calendarviews_applications WHERE calendarviewid=?', array ($viewId));
			$adb->pquery ('DELETE FROM vtiger_calendarviews_rules WHERE calendarviewid=?', array ($viewId));
			$adb->pquery ('DELETE FROM vtiger_calendarviews WHERE calendarviewid=?', array ($viewId));
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchDefaultView ($adb) {
			$result = $adb->pquery (
				'SELECT
					cv.calendarviewid,
					cv.modulename
				FROM
					vtiger_calendarviews cv
					LEFT JOIN vtiger_tab tfrom ON tfrom.name=cv.frommodulename
				WHERE
					tfrom.presence IN (0, 2) AND
					cv.setdefault=?',
				array (1)
			);
			if ($adb->num_rows ($result) > 0) {
				return $adb->fetchByAssoc ($result, -1, false);
			}
			return null;
		}
		
		public static function getAvailableFields (PearDatabase $adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $adb->pquery ('SELECT t.* FROM vtiger_tab t WHERE t.name=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row         = $adb->fetchByAssoc ($result, -1, false);
			$moduleLabel = getTranslatedString ($row ['tablabel'], $moduleName);
			$fields      = array (
				$moduleLabel => self::getAvailableModuleFields ($adb, $moduleName),
			);

			$result = $adb->pquery (
				'SELECT
					rlt.*
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab t ON t.tabid=rl.tabid
					INNER JOIN vtiger_tab rlt ON rlt.tabid=rl.related_tabid
				WHERE
					t.presence IN (0, 2) AND
					t.name=?',
				array ($moduleName)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$moduleLabel             = getTranslatedString ($row ['tablabel'], $moduleName);
					$fields [ $moduleLabel ] = self::getAvailableModuleFields ($adb, $row ['name']);
				}
			}

			return $fields;
		}

		public static function getAvailableDateFields ($availableFields) {
			if (empty ($availableFields)) {
				return null;
			}

			$dateFields = array ();
			foreach ($availableFields as $moduleLabel => $fields) {
				foreach ($fields as $field) {
					if (in_array ($field ['uitype'], array ('5', '6', '23', '70'))) {
						$dateFields [ $moduleLabel ][] = $field;
					}
				}
			}
			return $dateFields;
		}

		public static function getAvailableApplications (PearDatabase $adb) {
			$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_status=? ORDER BY app_name', array ('Activa'));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$applications = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$applications [ $row ['app_code'] ] = $row;
			}
			return $applications;
		}

		public static function getAvailableModules (PearDatabase $adb) {
			$result = $adb->query ('SELECT t.* FROM vtiger_tab t WHERE t.presence IN (0, 2) AND t.customized IN (0, 1) AND t.isentitytype=1 ORDER BY t.tablabel');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$modules [] = $row;
			}
			return $modules;
		}

		public static function getCalendarData (PearDatabase $adb, $view, $currentUser, $ruleId = null) {
			$relatedSqlData = self::getModuleRelatedSqlData ($adb, $view);
			if (empty ($relatedSqlData)) {
				return null;
			}

			$mainModuleSqlData = array_shift ($relatedSqlData);

			$selectClauses = array (
				'vtiger_crmentity.crmid AS __record_id__',
				"'{$view ['modulename']}' AS __module_name__",
				"CONCAT(`vtiger_users`.`first_name`, ' ', `vtiger_users`.`last_name`) AS user_name",
			);
			$fromClauses   = array_filter (
				array_merge (
					array (
						$mainModuleSqlData ['tablename'],
						"INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid={$mainModuleSqlData ['tablename']}.{$mainModuleSqlData ['entityidfield']} AND vtiger_crmentity.deleted=0",
						"LEFT JOIN vtiger_crmentityrel ON vtiger_crmentityrel.crmid={$mainModuleSqlData ['tablename']}.{$mainModuleSqlData ['entityidfield']}",
						'LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid',
						'LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid',
					),
					self::getNonAdminUserFromClause ($view ['modulename'], $currentUser)
				)
			);
			$whereClauses  = array ();

			$sqlClauses    = self::getMainModuleSqlClauses ($mainModuleSqlData);
			$selectClauses = array_filter (array_merge ($selectClauses, $sqlClauses ['select']));
			$whereClauses  = array_filter (array_merge ($whereClauses, $sqlClauses ['where']));

			$sqlClauses    = self::getReferencedModuleSqlClauses ($view ['modulename'], $currentUser, $mainModuleSqlData, $relatedSqlData);
			$selectClauses = array_filter (array_merge ($selectClauses, $sqlClauses ['select']));
			$fromClauses   = array_filter (array_merge ($fromClauses, $sqlClauses ['from']));
			$whereClauses  = array_filter (array_merge ($whereClauses, $sqlClauses ['where']));

			$sqlClauses    = self::getRelatedDataSqlClauses ($view ['modulename'], $currentUser, $mainModuleSqlData, $relatedSqlData);
			$selectClauses = array_filter (array_merge ($selectClauses, $sqlClauses ['select']));
			$fromClauses   = array_filter (array_merge ($fromClauses, $sqlClauses ['from']));
			$whereClauses  = array_filter (array_merge ($whereClauses, $sqlClauses ['where']));
			
			$sqlRule = self::getWhereClausesFromRules ($view, $ruleId, $mainModuleSqlData);
			$selectClause = join (', ', array_unique ($selectClauses));
			$fromClause   = join (' ', array_unique ($fromClauses));
			$whereClause  = count ($whereClauses) > 0 ? 'WHERE ' . join (' AND ', array_unique ($whereClauses)) : '';
			$whereClause .= $sqlRule;
			$result = $adb->query ("SELECT DISTINCT {$selectClause} FROM {$fromClause} {$whereClause}");
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$um    = UserManager::getInstance ($adb, null);
			$rule  = self::getRuleById ($view ['rules'], $ruleId);
			$data  = array ();
			$records = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				if (!self::recordMeetsRule ($row, $rule) && false) {
					continue;
				}
				if (in_array ($row ['__record_id__'], $records)) {
					continue;
				}
				$records[] = $row ['__record_id__'];
				$colors    = self::getColorsFromRules ($view, $row);
				$startDate = date_create ($row [ $view ['fromfieldname'] ])->format ('Y-m-d\TH:i:s');
				$endDate   = !empty ($view ['tofieldname']) && (isset ($row [ $view ['tofieldname'] ])) ? date_create ($row [ $view ['tofieldname'] ])->format ('Y-m-d\TH:i:s') : $startDate;
				if ($view ['titlefieldname'] == 'assigned_user_id') {
					$user = $um->fetchUserById ($row ['smownerid'], true);
				$row [ $view ['titlefieldname'] ] = $user->getFirstName () . ' ' . $user->getLastName ();
				}
				if ($view ['subtitlefieldname'] == 'assigned_user_id') {
					$user = $um->fetchUserById ($row ['smownerid'], true);
					$row [ $view ['subtitlefieldname'] ] = $user->getFirstName () . ' ' . $user->getLastName ();
				}
				if (in_array ($row ['__module_name__'], array ('daily_report'))) {
					$title = (!empty($row [ $view ['subtitlefieldname']] && !empty ($row['user_name']))) ? $row['user_name'] . ' / ' . $row [ $view ['subtitlefieldname'] ] : $row [ $view ['titlefieldname'] ];
				} else {
					$title = (!empty($row [ $view ['subtitlefieldname'] ])) ? $row [ $view ['titlefieldname'] ] . ' / ' . $row [ $view ['subtitlefieldname'] ] : $row [ $view ['titlefieldname'] ];
				}
				
				$data[] = array (
					'backgroundColor' => $colors ['background'],
					'borderColor'     => $colors ['background'],
					'crmid'           => $row ['__record_id__'],
					'end'             => $endDate,
					'start'           => $startDate,
					'textColor'       => $colors ['foreground'],
					'title'           => $title,
					'url'             => "index.php?module={$row ['__module_name__']}&action=DetailView&record={$row ['__record_id__']}",
				);
			}
			return $data;
		}

		public static function getCalendarViewById (PearDatabase $adb, $viewId) {
			if (empty ($viewId)) {
				throw new Exception ('No se ha suministrado el ID de la vista');
			}

			$applications = self::getAvailableApplications ($adb);
			if (empty ($applications)) {
				throw new Exception ("La vista con el ID {$viewId} no se encuentra registrada");
			}

			$applicationCodes = array_keys ($applications);
			$questionMarks    = str_repeat ('?, ', (count ($applicationCodes) - 1)) . '?';
			$result           = $adb->pquery (
				"SELECT
					cv.*,
					ffrom.uitype AS fromfieldtype,
					fto.uitype AS tofieldtype
				FROM
					vtiger_calendarviews cv
					INNER JOIN vtiger_calendarviews_applications cva ON cva.calendarviewid=cv.calendarviewid AND cva.applicationcode IN ({$questionMarks})
					INNER JOIN vtiger_tab tfrom ON tfrom.name=cv.frommodulename
					INNER JOIN vtiger_field ffrom ON ffrom.fieldname=cv.fromfieldname AND ffrom.tabid=tfrom.tabid
					LEFT JOIN vtiger_tab tto ON tto.name=cv.tomodulename
					LEFT JOIN vtiger_field fto ON fto.fieldname=cv.tofieldname AND fto.tabid=tto.tabid
				WHERE
					cv.calendarviewid=?",
				array_merge ($applicationCodes, array ($viewId))
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ("La vista con el ID {$viewId} no se encuentra registrada");
			}

			$view                      = $adb->fetchByAssoc ($result, -1, false);
			$view ['rules']            = self::getCalendarViewRules ($adb, $viewId);
			$view ['applicationcodes'] = self::getCalendarViewApplicationCodes ($adb, $viewId);
			return $view;
		}

		public static function getCalendarViews (PearDatabase $adb, $keyword = null, $page = null, $moduleNames = null) {
			$applications             = self::getAvailableApplications ($adb);
			$moduleNamesWhereClauses  = self::getCalendarViewsWhereClausesByModuleName ($moduleNames);
			$keywordWhereClauses      = self::getCalendarViewsWhereClausesByKeyword ($keyword);
			$applicationsWhereClauses = self::getCalendarViewsWhereClausesByApplications ($applications);

			$whereClauses = array_filter (array_merge ($moduleNamesWhereClauses ['where'], $keywordWhereClauses ['where'], $applicationsWhereClauses ['where']));
			$arguments    = array_filter (array_merge ($moduleNamesWhereClauses ['arguments'], $keywordWhereClauses ['arguments'], $applicationsWhereClauses ['arguments']));
			$whereClause  = !empty ($whereClauses) ? ' WHERE ' . join (' AND ', $whereClauses) : '';

			if ((empty ($page)) || ($page <= 0)) {
				$startRecord = 0;
			} else {
				$startRecord = (($page - 1) * self::RECORDS_PER_PAGE);
			}

			$limit = self::RECORDS_PER_PAGE;

			$result = $adb->pquery (
				"SELECT
					cv.*,
					t.tablabel AS modulelabel,
					ttitle.tablabel AS titlemodulelabel,
					ftitle.fieldlabel AS titlefieldlabel,
					tfrom.tablabel AS frommodulelabel,
					ffrom.fieldlabel AS fromfieldlabel,
					tto.tablabel AS tomodulelabel,
					fto.fieldlabel AS tofieldlabel,
					total.__total_records__
				FROM
					vtiger_calendarviews cv
					INNER JOIN vtiger_tab t ON t.name=cv.modulename AND t.presence IN (0, 2)
					INNER JOIN vtiger_tab ttitle ON ttitle.name=cv.titlemodulename
					INNER JOIN vtiger_field ftitle ON ftitle.fieldname=cv.titlefieldname AND ftitle.tabid=ttitle.tabid
					INNER JOIN vtiger_tab tfrom ON tfrom.name=cv.frommodulename AND tfrom.presence IN (0, 2)
					INNER JOIN vtiger_field ffrom ON ffrom.fieldname=cv.fromfieldname AND ffrom.tabid=tfrom.tabid
					LEFT JOIN vtiger_tab tto ON tto.name=cv.tomodulename
					LEFT JOIN vtiger_field fto ON fto.fieldname=cv.tofieldname AND fto.tabid=tto.tabid
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_calendarviews cv2
						INNER JOIN vtiger_tab t2 ON t2.name=cv2.modulename AND t2.presence IN (0, 2)
						INNER JOIN vtiger_tab tfrom2 ON tfrom2.name=cv2.frommodulename AND tfrom2.presence IN (0, 2)) AS total
				{$whereClause}
				ORDER BY
					cv.calendarviewid
				LIMIT {$startRecord}, {$limit}",
				$arguments
			);
			if ($adb->num_rows ($result) > 0) {
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($totalRecords === null) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					$row ['applicationcodes'] = self::getCalendarViewApplicationCodes ($adb, $row ['calendarviewid']);
					$records []               = $row;
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / self::RECORDS_PER_PAGE);
			} else {
				$totalRecords = 0;
				$records      = null;
				$endRecord    = 0;
				$totalPages   = 0;
			}

			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => empty ($page) ? 1 : intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $records,
			);
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getCalendarViewByModules (PearDatabase $adb) {
			// Obtener aplicaciones activas para filtrar vistas válidas
			$applications = self::getAvailableApplications ($adb);
			if (empty ($applications)) {
				return null;
			}
			
			$applicationCodes = array_keys ($applications);
			$questionMarks    = str_repeat ('?, ', (count ($applicationCodes) - 1)) . '?';
			
			$result = $adb->pquery (
			"SELECT DISTINCT
				cv.calendarviewid,
				cv.label,
				cv.modulename,
				tfrom.tablabel
			FROM
				vtiger_calendarviews cv
				INNER JOIN vtiger_calendarviews_applications cva ON cva.calendarviewid=cv.calendarviewid AND cva.applicationcode IN ({$questionMarks})
				INNER JOIN vtiger_tab tfrom ON tfrom.name=cv.frommodulename
				INNER JOIN vtiger_tab tmodule ON tmodule.name=cv.modulename
			WHERE tmodule.presence IN (0, 2) AND tfrom.presence IN (0, 2)
			ORDER BY cv.modulename DESC",
			$applicationCodes
		);
			if ($adb->num_rows ($result) > 0) {
				$views      = array ();
				$lastModule = null;
				$um         = UserManager::getInstance ($adb, null);
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				// Verificar que la vista tiene al menos el módulo FROM válido
				if (empty($row['calendarviewid']) || empty($row['modulename'])) {
					continue;
				}
				
				$thisRules = self::getCalendarViewRules ($adb, $row['calendarviewid']);
					if (!empty($thisRules)) {
						$selectedRule = null;
						$ruleGroup    = array();
						$ruleIndex    = -1;
						$indexFor     = 0;
						$joinField    = array ('OR' => 'O', 'AND' => 'Y');
						foreach ($thisRules as $rule) {
							if ($rule['fieldlabel'] == 'Assigned To') {
								$rule['fieldlabel'] = 'Asignado a';
							}
							
							if ($rule ['fieldname'] == 'assigned_user_id') {
								$user = $um->fetchUserById ($rule['value'], true);
								$rule['value'] = $user->getFirstName () . ' ' . $user->getLastName ();
							}
							
							if (($rule['ruleid'] == $ruleGroup[$ruleIndex]['ruleId']) && count ($ruleGroup)) {
								$ruleGroup[$ruleIndex]['title'] .= " {$joinField[$thisRules[($indexFor - 1)]['joinrule']]} &#10;{$rule['fieldlabel']} {$rule['operator']} {$rule['value']}";
								$ruleGroup[$ruleIndex]['option'] = 'Grupo de opciones';
							} else {
								$ruleGroup[] = array (
									'ruleId' => $rule['ruleid'],
									'title'  => "{$rule['fieldlabel']} {$rule['operator']} {$rule['value']}",
									'option' => "{$rule['fieldlabel']} {$rule['operator']} {$rule['value']}",
								);
								$ruleIndex++;
							}
							$indexFor++;
						}
						$row['rules'] = $ruleGroup;
						unset($ruleGroup);
					} else {
						$row['rules'] = null;
					}
					if ($row['modulename'] != $lastModule) {
						$lastModule = $row['modulename'];
						$views[ $lastModule ][] = $row;
					} else {
						$views[ $lastModule ][] = $row;
					}
				}
			}
			
			return (isset($views)) ? $views : null;
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param integer $viewId
		 *
		 * @throws Exception
		 */
		public static function setDefaultView ($adb, $viewId) {
			$adb->pquery ('UPDATE vtiger_calendarviews SET setdefault=? WHERE 1',array(0));
			$adb->pquery ('UPDATE vtiger_calendarviews SET setdefault=? WHERE calendarviewid=?',array(1, $viewId));
		}
		
		public static function updateView (PearDatabase $adb, $arguments, $isInstance) {
			if (!empty ($arguments ['rules'])) {
				$ruleGroup = array();
				foreach ($arguments ['rules'] as $thisRules) {
					$ruleId = $thisRules[0]['ruleid'];
					$ruleColor = $thisRules[0]['backgroundcolor'];
					if (!empty ($ruleId)) {
						$adb->pquery ('DELETE FROM vtiger_calendarviews_rules WHERE ruleid=?', array($ruleId));
					}
					
					$rules = array();
					foreach ($thisRules as $rule) {
						$rules [] = CalendarViewRule::getInstance ()
							->setBackgroundColor (empty ($rule ['backgroundcolor']) ? $ruleColor : $rule ['backgroundcolor'])
							->setFieldName ($rule ['fieldname'])
							->setJoinRule ($rule ['glue'])
							->setId (null)
							->setModuleName ($rule ['modulename'])
							->setOperator ($rule ['operator'])
							->setValue ($rule ['value'])
							->setViewId (! empty ($arguments ['calendarviewid']) ? $arguments ['calendarviewid'] : null);
					}
					$ruleGroup[] = $rules;
					unset($rules);
				}
			} else {
				$rules = null;
			}
			
			$view = CalendarView::getInstance ()
				->setApplicationCodes ($arguments ['applicationcodes'])
				->setBackgroundColor ($arguments ['backgroundcolor'])
				->setFromFieldName ($arguments ['fromfieldname'])
				->setFromModuleName ($arguments ['frommodulename'])
				->setId (!empty ($arguments ['calendarviewid']) ? $arguments ['calendarviewid'] : null)
				->setLabel ($arguments ['label'])
				->setLocked ($isInstance)
				->setModuleName ($arguments ['modulename'])
				->setRules ($ruleGroup)
				->setSubTitle ($arguments ['subtitlefieldname'])
				->setTitleFieldName ($arguments ['titlefieldname'])
				->setTitleModuleName ($arguments ['titlemodulename'])
				->setToFieldName ($arguments ['tofieldname'])
				->setToModuleName ($arguments ['tomodulename']);
			CalendarViewManager::getInstance ($adb)->saveView ($view);
		}

	}
