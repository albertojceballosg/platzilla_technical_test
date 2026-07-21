<?php
	require_once ('include/platzilla/Data/FieldGridManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/GetUserGroups.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/Reports/CustomReportUtils.php');

	abstract class ReportUtils {

		private static function getAvailableDateFieldsByModuleName (PearDatabase $adb, $moduleName, $currentUser) {
			if (!is_admin ($currentUser)) {
				$profileIds  = getCurrentUserProfileList ();
				$fromClauses = array (
					'vtiger_profile2field p2f ON p2f.fieldid=f.fieldid',
					'vtiger_def_org_field dof ON dof.fieldid=f.fieldid',
				);
				if (count ($profileIds) > 0) {
					$profileIdsQuestionMarks = str_repeat ('?, ', (count ($profileIds) - 1)) . '?';
					$whereClauses            = array (
						'p2f.visible=0',
						"p2f.profileid IN ({$profileIdsQuestionMarks})",
						'dof.visible=0',
					);
				} else {
					$whereClauses = array ();
				}
				$fromClause  = 'INNER JOIN ' . join (' INNER JOIN ', $fromClauses);
				$whereClause = join (' AND ', $whereClauses) . ' AND';
			} else {
				$profileIds  = array ();
				$fromClause  = '';
				$whereClause = '';
			}

			if ($moduleName == 'Calendar') {
				$groupByClause            = 'GROUP BY f.fieldlabel';
				$moduleNamesQuestionMarks = '?, ?';
				$moduleNames              = array ('Calendar', 'Events');
			} else {
				$groupByClause            = '';
				$moduleNamesQuestionMarks = '?';
				$moduleNames              = array ($moduleName);
			}

			$result = $adb->pquery (
				"SELECT
					f.*,
					t.name
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
					{$fromClause}
				WHERE
					{$whereClause}
					t.name IN ({$moduleNamesQuestionMarks}) AND
					f.uitype IN (5, 6, 23, 70) AND
					f.presence in (0, 2)
				{$groupByClause}
				ORDER BY
					f.sequence",
				array_merge ($profileIds, $moduleNames)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fieldLabel      = str_replace (' ', '_', $row ['fieldlabel']);
				$key             = "{$row ['tablename']}:{$row ['columnname']}:{$row ['fieldname']}:{$moduleName}_{$fieldLabel}";
				$fields [ $key ] = getTranslatedString ($row ['fieldlabel'], $row ['name']);
			}

			return $fields;
		}

		private static function getAvailableFieldsByModuleName (PearDatabase $adb, $moduleName, $currentUser) {
			if (!is_admin ($currentUser)) {
				$profileIds  = getCurrentUserProfileList ();
				$fromClauses = array (
					'vtiger_profile2field p2f ON p2f.fieldid=f.fieldid',
					'vtiger_def_org_field dof ON dof.fieldid=f.fieldid',
				);
				if (count ($profileIds) > 0) {
					$profileIdsQuestionMarks = str_repeat ('?, ', (count ($profileIds) - 1)) . '?';
					$whereClauses            = array (
						'p2f.visible=0',
						"p2f.profileid IN ({$profileIdsQuestionMarks})",
						'dof.visible=0',
					);
				} else {
					$whereClauses = array ();
				}
				$fromClause  = 'INNER JOIN ' . join (' INNER JOIN ', $fromClauses);
				$whereClause = join (' AND ', $whereClauses) . ' AND';
			} else {
				$profileIds  = array ();
				$fromClause  = '';
				$whereClause = '';
			}

			if ($moduleName == 'Calendar') {
				$groupByClause            = 'GROUP BY f.fieldlabel';
				$moduleNamesQuestionMarks = '?, ?';
				$moduleNames              = array ('Calendar', 'Events');
			} else {
				$groupByClause            = '';
				$moduleNamesQuestionMarks = '?';
				$moduleNames              = array ($moduleName);
			}

			$result = $adb->pquery (
				"SELECT
					f.*,
					t.name
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
					{$fromClause}
				WHERE
					{$whereClause}
					t.name IN ({$moduleNamesQuestionMarks}) AND
					f.displaytype IN (1, 2, 3) AND
					f.presence in (0, 2) AND 
					f.uitype NOT IN ('2202', '2203', '2204', '4096' )
				{$groupByClause}
				ORDER BY
					f.block, f.fieldlabel",
				array_merge ($profileIds, $moduleNames)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$moduleLabel = getTranslatedString ($moduleName, $moduleName);
			$fields      = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fieldLabel = str_replace (' ', '_', $row ['fieldlabel']);
				$typeOfData = explode ('~', $row ['typeofdata']);
				$typeOfData = ChangeTypeOfData_Filter ($row ['tablename'], $row ['columnname'], $typeOfData [0]);

				if (in_array ($row ['uitype'], array (59, 68))) {
					$typeOfData = 'V';
				}
				if ($row ['tablename'] == 'vtiger_crmentity') {
					$row ['tablename'] = "{$row ['tablename']}{$moduleName}";
				}
				if ($row ['fieldname'] == 'assigned_user_id') {
					$row ['tablename']  = "vtiger_users{$moduleName}";
					$row ['columnname'] = 'user_name';
				}

				$key                             = "{$row ['tablename']}:{$row ['columnname']}:{$moduleName}_{$fieldLabel}:{$row ['fieldname']}:{$typeOfData}";
				$fields [ $moduleLabel ][ $key ] = getTranslatedString ($row ['fieldlabel'], $row ['name']);
			}
			return $fields;
		}

		private static function getAvailableFieldsGridByModuleName (PearDatabase $adb, $moduleName, $currentUser) {
			if (!is_admin ($currentUser)) {
				$profileIds  = getCurrentUserProfileList ();
				$fromClauses = array (
					'vtiger_profile2field p2f ON p2f.fieldid=f.fieldid',
					'vtiger_def_org_field dof ON dof.fieldid=f.fieldid',
				);
				if (count ($profileIds) > 0) {
					$profileIdsQuestionMarks = str_repeat ('?, ', (count ($profileIds) - 1)) . '?';
					$whereClauses            = array (
						'p2f.visible=0',
						"p2f.profileid IN ({$profileIdsQuestionMarks})",
						'dof.visible=0',
					);
				} else {
					$whereClauses = array ();
				}
				$fromClause  = 'INNER JOIN ' . join (' INNER JOIN ', $fromClauses);
				$whereClause = join (' AND ', $whereClauses) . ' AND';
			} else {
				$profileIds  = array ();
				$fromClause  = '';
				$whereClause = '';
			}

			if ($moduleName == 'Calendar') {
				$groupByClause            = 'GROUP BY f.fieldlabel';
				$moduleNamesQuestionMarks = '?, ?';
				$moduleNames              = array ('Calendar', 'Events');
			} else {
				$groupByClause            = '';
				$moduleNamesQuestionMarks = '?';
				$moduleNames              = array ($moduleName);
			}

			$result = $adb->pquery (
				"SELECT
					f.*,
					t.name
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
					{$fromClause}
				WHERE
					{$whereClause}
					t.name IN ({$moduleNamesQuestionMarks}) AND
					f.displaytype IN (1, 2, 3) AND
					f.presence IN (0, 2) AND 
					f.uitype = '2202'
				{$groupByClause}
				ORDER BY
					f.sequence",
				array_merge ($profileIds, $moduleNames)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$moduleLabel = getTranslatedString ($moduleName, $moduleName);
			$fields      = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				if (!$fieldsGrid = FieldGridManager::getInstance ($adb)->fetchFieldGrid ($moduleName, $row ['fieldname'])) {
					continue;
				};
				foreach ($fieldsGrid as $field) {
					$fieldLabel = html_entity_decode(getTranslatedString($row ['fieldlabel'], $moduleName), ENT_QUOTES, 'UTF-8');
					$dummy      = explode ('_', $field->getName ());
					array_pop ($dummy);
					$fieldName     = join ('_', $dummy);

					if ($field->getUiType () == FieldInterface::UI_TYPE_SUMMARY_ROW) {
						$summaryConfig = unserialize (base64_decode ($field->getDataField ()));
						$summaryFields = array_column($summaryConfig, 'field');
						foreach ($summaryFields as $column) {
							if($column != 'false') {
								$dummy = explode ('_', $column);
								array_pop ($dummy);
								$colunmName = join ('_', $dummy);
								
								// Formatear nombre de columna: subtotal_articulos → Subtotal Articulos
								$columnLabel = ucfirst(str_replace('_', ' ', $colunmName));
								
								// Construir label mejorado con prefijo [GRID] para mejor visibilidad
								$improvedLabel = '[GRID] ' . $fieldLabel . ' - Total: ' . $columnLabel;
								
								$key = "vtiger_subfields_values:{$colunmName}@{$row ['fieldname']}:{$moduleName}@{$row ['tablename']}:{$colunmName}:N";
								$fields[$moduleLabel . ' Tabla: ' . $fieldLabel][$key] = $improvedLabel;
							}
						}
					}
				}
			}
			return $fields;
		}

		private static function getAvailableReportsByFolderId (PearDatabase $adb, $folderId, $currentUser, $moduleName, $isLocked) {
			global $current_user_parent_role_seq;

			$arguments    = array ();
			$whereClauses = array ();
			if (!is_admin ($currentUser)) {
				$userGroups = new GetUserGroups ();
				$userGroups->getAllUserGroups ($currentUser->id);
				if (!empty ($userGroups->user_groups)) {
					$userGroupsQuestionMarks = str_repeat ('?, ', (count ($userGroups->user_groups) - 1)) . '?';
					$nonAdminWhereClause     = "r.reportid IN (SELECT reportid from vtiger_reportsharing WHERE (shareid IN ({$userGroupsQuestionMarks}) AND setype=?) OR (shareid=? AND setype=?))";
					$arguments               = array_merge ($arguments, $userGroups->user_groups, array ('groups', $currentUser->id, 'users'));
				} else {
					$nonAdminWhereClause = 'r.reportid IN (SELECT reportid from vtiger_reportsharing WHERE shareid=? AND setype=?)';
					$arguments           = array_merge ($arguments, array ($currentUser->id, 'users'));
				}
				$whereClauses [] = "{$nonAdminWhereClause}";
				$whereClauses [] = 'r.sharingtype=?';
				$whereClauses [] = 'r.owner=?';
				$whereClauses [] = 'r.owner IN (
										SELECT
											vtiger_user2role.userid
										FROM
											vtiger_user2role
											INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
											INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
										WHERE
											vtiger_role.parentrole like ?
									)';
				$arguments       = array_merge ($arguments, array ('Public', $currentUser->id, "{$current_user_parent_role_seq}::%"));
				$whereClause     = '(' . join (' OR ', $whereClauses) . ') AND';
				if (!empty ($moduleName)) {
					$whereClause .= ' rm.primarymodule=? AND';
					$arguments [] = $moduleName;
				}
				if (($isLocked === 0) || ($isLocked === 1)) {
					$whereClause .= ' r.locked=? AND';
					$arguments [] = $isLocked;
				}
			} else {
				if (!empty ($moduleName)) {
					$arguments   = array ($moduleName);
					$whereClause = 'rm.primarymodule=? AND';
				} else {
					$arguments   = array ();
					$whereClause = '';
				}
				if (($isLocked === 0) || ($isLocked === 1)) {
					$whereClause .= ' r.locked=? AND';
					$arguments [] = $isLocked;
				}
			}

			$result = $adb->pquery (
				"SELECT
					r.*,
					rm.*,
					rf.folderid
				FROM
					vtiger_report r
					INNER JOIN vtiger_reportfolder rf ON rf.folderid=r.folderid
					INNER JOIN vtiger_reportmodules rm ON rm.reportmodulesid=r.reportid
				WHERE
					{$whereClause}
					r.folderid=?",
				array_merge ($arguments, array ($folderId))
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$subordinatedUsers = self::getSubordinatedUserIds ($adb, $current_user_parent_role_seq);
			$reports           = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				if (isPermitted ($row ['primarymodule'], 'index') != 'yes') {
					continue;
				}

				if ((is_admin ($currentUser)) || ($row ['owner'] == $currentUser->id) || (in_array ($row ['owner'], $subordinatedUsers))) {
					$row ['editable'] = 'true';
				} else {
					$row ['editable'] = 'false';
				}

				$reports [] = $row;
			}

			return $reports;
		}

		/**
		 * NOTA: Este método puede ser refactorizado. Pero será más adelante. El módulo de reportes en el cual se basa es basura. Seguramente cuando se cambie.
		 * @codingStandardsIgnoreStart
		 * @SuppressWarnings(PHPMD.NPathComplexity)
		 *
		 * @param PearDatabase $adb
		 * @param $moduleName
		 * @param $currentUser
		 *
		 * @return array|null
		 */
		private static function getAvailableTotalColumnsByModuleName (PearDatabase $adb, $moduleName, $currentUser) {
			if (empty ($moduleName)) {
				return null;
			}

			if (!is_admin ($currentUser)) {
				$fromClauses  = array (
					'vtiger_def_org_field dof ON dof.fieldid=f.fieldid',
					'vtiger_profile2field p2f ON p2f.fieldid=f.fieldid',
				);
				$whereClauses = array (
					'dof.visible=0',
					'p2f.visible=0',
				);
				$profileList  = getCurrentUserProfileList ();
				if (count ($profileList) > 0) {
					$questionMarks   = str_repeat ('?, ', (count ($profileList) - 1)) . '?';
					$whereClauses [] = "p2f.profileid IN ({$questionMarks})";
				}
			} else {
				$profileList  = array ();
				$fromClauses  = array ();
				$whereClauses = array ();
			}

			if ($moduleName == 'Calendar') {
				$moduleNamesQuestionMarks = '?, ?';
				$moduleNames              = array ('Calendar', 'Events');
			} else {
				$moduleNamesQuestionMarks = '?';
				$moduleNames              = array ($moduleName);
			}

			switch ($moduleName) {
				case 'Calendar':
					$whereClauses [] = "f.fieldname NOT IN ('parent_id','contact_id')";
					break;
				default:
					break;
			}

			$fromClause  = count ($fromClauses) > 0 ? 'INNER JOIN ' . join (' INNER JOIN ', $fromClauses) : '';
			$whereClause = count ($whereClauses) > 0 ? join (' AND ', $whereClauses) . ' AND ' : '';

			$result = $adb->pquery (
				"SELECT
					f.*,
					t.tablabel
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
					{$fromClause}
				WHERE
					f.uitype<>50 AND
					f.displaytype IN (1, 2, 3) AND
					f.presence IN (0, 2) AND
					{$whereClause}
					(f.typeofdata LIKE ? OR f.typeofdata LIKE ? OR f.typeofdata LIKE ?) AND
					t.name IN ({$moduleNamesQuestionMarks})
				ORDER BY
					sequence",
				array_merge ($profileList, array ('NN~%', 'N~%', 'I~%'), $moduleNames)
			);
			if ($adb->num_rows ($result) == 0) {
				return null;
			}

			$columns = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fieldLabel              = getTranslatedString ($row ['fieldlabel'], $moduleName);
				$columns [ $fieldLabel ] = array (
					"cb:{$row ['tablename']}:{$row ['columnname']}:{$row ['fieldlabel']}_SUM:2",
					"cb:{$row ['tablename']}:{$row ['columnname']}:{$row ['fieldlabel']}_AVG:3",
					"cb:{$row ['tablename']}:{$row ['columnname']}:{$row ['fieldlabel']}_MIN:4",
					"cb:{$row ['tablename']}:{$row ['columnname']}:{$row ['fieldlabel']}_MAX:5",
				);
			}

			return $columns;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return null|string
		 * @throws Exception
		 */
		private static function getModuleLabel (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT tablabel FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return getTranslatedString ($row ['tablabel'], $moduleName);
		}

		private static function getReportAdvancedFilters (PearDatabase $adb, $reportId) {
			$groupsResult = $adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			if ((!$groupsResult) || ($adb->num_rows ($groupsResult) == 0)) {
				return null;
			}

			$filters = array ();
			while ($group = $adb->fetchByAssoc ($groupsResult, -1, false)) {
				$conditionsResult = $adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=? AND groupid=? ORDER BY columnindex', array ($reportId, $group ['groupid']));
				if ((!$conditionsResult) || ($adb->num_rows ($conditionsResult) == 0)) {
					continue;
				}

				$conditions = array ();
				while ($condition = $adb->fetchByAssoc ($conditionsResult, -1, false)) {
					$conditions [] = array (
						'conditionid' => $condition ['columnindex'],
						'columnname'  => $condition ['columnname'],
						'glue'        => $condition ['column_condition'],
						'operator'    => $condition ['comparator'],
						'value'       => $condition ['value'],
					);
				}

				$filters [] = array (
					'groupid'    => $group ['groupid'],
					'conditions' => $conditions,
					'glue'       => $group ['group_condition'],
				);
			}
			return $filters;
		}

		private static function getReportColumns (PearDatabase $adb, $reportId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$columns = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$columns [] = $row ['columnname'];
			}
			return $columns;
		}

		private static function getReportData (PearDatabase $adb, $reportId) {
			if (empty ($reportId)) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			return $adb->fetchByAssoc ($result, -1, false);
		}

		private static function getReportModules (PearDatabase $adb, $reportId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return array (
				'main'    => $row ['primarymodule'],
				'related' => explode (':', $row ['secondarymodules']),
			);
		}

		/**
		 * NOTA: Este método puede ser refactorizado. Pero será más adelante. El módulo de reportes en el cual se basa es basura. Seguramente cuando se cambie.
		 *
		 * @param PearDatabase $adb
		 * @param $reportId
		 *
		 * @return array|null
		 *
		 * @SuppressWarnings(PHPMD.NPathComplexity)
		 */
		private static function getReportScheduling (PearDatabase $adb, $reportId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row        = $adb->fetchByAssoc ($result, -1, false);
			$schedule   = json_decode ($row ['schedule'], true);
			$dummy      = json_decode ($row ['recipients'], true);
			$recipients = array ();
			if (!empty ($dummy ['groups'])) {
				foreach ($dummy ['groups'] as $groupId) {
					$recipients [] = "group::{$groupId}";
				}
			}
			if (!empty ($dummy ['roles'])) {
				foreach ($dummy ['roles'] as $roleId) {
					$recipients [] = "role::{$roleId}";
				}
			}
			if (!empty ($dummy ['rs'])) {
				foreach ($dummy ['rs'] as $roleId) {
					$recipients [] = "role::{$roleId}";
				}
			}
			if (!empty ($dummy ['users'])) {
				foreach ($dummy ['users'] as $userId) {
					$recipients [] = "user::{$userId}";
				}
			}

			return array (
				'day'        => $schedule ['date'],
				'format'     => $row ['format'],
				'frequency'  => $schedule ['scheduletype'],
				'month'      => $schedule ['month'],
				'recipients' => count ($recipients) > 0 ? $recipients : null,
				'time'       => $schedule ['time'],
				'weekday'    => $schedule ['day'],
			);
		}

		private static function getReportSharing (PearDatabase $adb, $reportId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$sharing = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$sharing [] = "{$row ['setype']}::{$row ['shareid']}";
			}

			return $sharing;
		}

		private static function getReportSortColumns (PearDatabase $adb, $reportId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$columns = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				// Mantener compatibilidad con claves legacy (first, second, third)
				if ($row ['sortcolid'] == 1) {
					$key = 'first';
				} else if ($row ['sortcolid'] == 2) {
					$key = 'second';
				} else if ($row ['sortcolid'] == 3) {
					$key = 'third';
				} else {
					// Para campos 4-10, usar formato 'group4', 'group5', etc.
					$key = 'group' . $row ['sortcolid'];
				}
				$columns [ $key ] = array (
					'grouping' => $row ['columnname'],
					'sorting'  => $row ['sortorder'],
				);
			}

			return $columns;
		}

		private static function getReportStandardFilter (PearDatabase $adb, $reportId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return array (
				'column' => $row ['datecolumnname'],
				'from'   => $row ['startdate'],
				'period' => $row ['datefilter'],
				'to'     => $row ['enddate'],
			);
		}

		private static function getSubordinatedUserIds (PearDatabase $adb, $currentUserParentRole) {
			$result = $adb->pquery (
				'SELECT
					u.id
				FROM
					vtiger_user2role u2r
					INNER JOIN vtiger_users u ON u.id=u2r.userid
					INNER JOIN vtiger_role r ON r.roleid=u2r.roleid
				WHERE
					r.parentrole LIKE ?',
				array ("{$currentUserParentRole}::%")
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$users = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$users [] = $row ['id'];
			}

			return $users;
		}

		private static function saveReport (PearDatabase $adb, $reportId, $arguments) {
			$type       = $arguments ['type'];
			$visibility = $arguments ['visibility'];

			self::saveReportModules ($adb, $reportId, $arguments ['modulename'], $arguments ['relatedmodulenames']);
			self::saveReportColumns ($adb, $reportId, $arguments ['columns']);
			if ($type == 'summary') {
				// Procesar campos de agrupación dinámicamente
				if (isset($arguments['groupings']) && is_array($arguments['groupings'])) {
					// Primero limpiar todas las columnas de ordenamiento existentes (hasta 10)
					for($i = 1; $i <= 10; $i++) {
						$adb->pquery('DELETE FROM vtiger_reportgroupbycolumn WHERE reportid=? AND sortid=?', array($reportId, $i));
						$adb->pquery('DELETE FROM vtiger_reportsortcol WHERE reportid=? AND sortcolid=?', array($reportId, $i));
					}
					
					// Guardar los nuevos campos de agrupación
					foreach($arguments['groupings'] as $index => $grouping) {
						$sorting = isset($arguments['sortings'][$index]) ? $arguments['sortings'][$index] : 'Ascending';
						self::saveReportSortColumn($adb, $reportId, $index, $grouping, $sorting);
					}
				} else {
					// Compatibilidad con código legacy (3 campos fijos)
					self::saveReportSortColumn ($adb, $reportId, 1, $arguments ['firstgrouping'], $arguments ['firstsorting']);
					self::saveReportSortColumn ($adb, $reportId, 2, $arguments ['secondgrouping'], $arguments ['secondsorting']);
					self::saveReportSortColumn ($adb, $reportId, 3, $arguments ['thirdgrouping'], $arguments ['thirdsorting']);
				}
			} else {
				// Para reportes tabulares, limpiar campos de agrupación
				for($i = 1; $i <= 10; $i++) {
					self::saveReportSortColumn ($adb, $reportId, $i);
				}
			}
			self::saveReportTotalColumns ($adb, $reportId, $arguments ['totalcolumns']);
			if (!empty ($arguments ['standardfilter'])) {
				self::saveReportStandardFilterColumn ($adb, $reportId, $arguments ['standardfilter']['column'], $arguments ['standardfilter']['period'], $arguments ['standardfilter']['from'], $arguments ['standardfilter']['to']);
			}
			self::saveReportAdvancedFilters ($adb, $reportId, $arguments ['advancedfilters']);
			self::saveReportVisibility ($adb, $reportId, $visibility, ($visibility == 'Shared' ? $arguments ['sharewith'] : null));
			if (!empty ($arguments ['schedule'])) {
				self::saveReportScheduling ($adb, $reportId, $arguments ['schedule']['frequency'], $arguments ['schedule']['day'], $arguments ['schedule']['format'], $arguments ['schedule']['month'], $arguments ['schedule']['time'], $arguments ['schedule']['weekday'], $arguments ['schedule']['recipients']);
			}
		}

		/**
		 * NOTA: Este método puede ser refactorizado. Pero será más adelante. El módulo de reportes en el cual se basa es basura. Seguramente cuando se cambie.
		 *
		 * @param PearDatabase $adb
		 * @param $reportId
		 * @param $advancedFilters
		 *
		 * @SuppressWarnings(PHPMD.NPathComplexity)
		 */
		private static function saveReportAdvancedFilters (PearDatabase $adb, $reportId, $advancedFilters) {
			if (!empty ($reportId)) {
				$adb->pquery ('DELETE FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
				$adb->pquery ('DELETE FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			}

			if (empty ($advancedFilters)) {
				return;
			}

			$columnIndex = 0;
			$lastGroupId = end (array_keys ($advancedFilters));
			foreach ($advancedFilters as $groupId => $advancedFilterGroup) {
				if ((empty ($advancedFilterGroup)) || (empty ($advancedFilterGroup ['conditions']))) {
					continue;
				}

				if ($groupId < 0) {
					$result = $adb->query ('SELECT MAX(groupid) AS groupid FROM vtiger_relcriteria_grouping');
					if ((!$result) || ($adb->num_rows ($result) == 0)) {
						$newGroupId = 0;
					} else {
						$row        = $adb->fetchByAssoc ($result, -1, false);
						$newGroupId = (intval ($row ['groupid']) + 1);
					}
				} else {
					$newGroupId = $groupId;
				}

				$lastConditionId = end (array_keys ($advancedFilterGroup ['conditions']));
				$expression      = '';
				foreach ($advancedFilterGroup ['conditions'] as $conditionId => $condition) {
					if ($conditionId === $lastConditionId) {
						$glue = '';
						$expression .= " {$columnIndex}";
					} else {
						$glue = $condition ['glue'];
						$expression .= " {$columnIndex} {$glue}";
					}

					$adb->pquery (
						'INSERT INTO vtiger_relcriteria (queryid, columnindex, columnname, comparator, value, groupid, column_condition) VALUES (?, ?, ?, ?, ?, ?, ?)',
						array ($reportId, $columnIndex, $condition ['columnname'], $condition ['operator'], $condition ['value'], $newGroupId, $glue)
					);
					$columnIndex++;
				}

				if ($groupId === $lastGroupId) {
					$glue = '';
				} else {
					$glue = $advancedFilterGroup ['glue'];
				}

				$adb->pquery (
					'INSERT INTO vtiger_relcriteria_grouping (groupid, queryid, group_condition, condition_expression) VALUES (?, ?, ?, ?)',
					array ($newGroupId, $reportId, $glue, $expression)
				);
			}
		}

		private static function saveReportColumns (PearDatabase $adb, $reportId, $columns) {
			if (!empty ($reportId)) {
				$adb->pquery ('DELETE FROM vtiger_selectcolumn WHERE queryid=?', array ($reportId));
			}

			if (empty ($columns)) {
				return;
			}

			$index = 0;
			foreach ($columns as $column) {
				$adb->pquery ('INSERT INTO vtiger_selectcolumn (queryid, columnindex, columnname) VALUES (?, ?, ?)', array ($reportId, $index, $column));
				$index++;
			}
		}

		private static function saveReportModules (PearDatabase $adb, $reportId, $moduleName, $relatedModuleNames) {
			if (!empty ($reportId)) {
				$adb->pquery ('DELETE FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			}

			$adb->pquery (
				'INSERT INTO vtiger_reportmodules (reportmodulesid, primarymodule, secondarymodules) VALUES (?, ?, ?)',
				array ($reportId, $moduleName, join (':', $relatedModuleNames))
			);
		}

		private static function saveReportScheduling (PearDatabase $adb, $reportId, $frequency, $day, $format, $month, $time, $weekDay, $recipients) {
			if (!empty ($reportId)) {
				$adb->pquery ('DELETE FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			}

			$schedule = array (
				'date'         => in_array ($frequency, array ('5', '6')) ? $day : '',
				'day'          => in_array ($frequency, array ('3', '4')) ? $weekDay : '',
				'month'        => $frequency == '6' ? $month : '',
				'scheduletype' => $frequency,
				'time'         => $time,
			);

			$dummy = array ('groups' => array (), 'roles' => array (), 'rs' => array (), 'users' => array ());
			if (!empty ($recipients)) {
				foreach ($recipients as $recipient) {
					list ($recipientType, $recipientId) = explode ('::', $recipient);
					if ($recipientType == 'group') {
						$dummy ['groups'][] = intval ($recipientId);
					} else if ($recipientType == 'role') {
						$dummy ['roles'][] = $recipientId;
					} else if ($recipientType == 'rs') {
						$dummy ['rs'][] = $recipientId;
					} else if ($recipientType == 'user') {
						$dummy ['users'][] = intval ($recipientId);
					}
				}
			}

			$adb->pquery (
				'INSERT INTO vtiger_scheduled_reports (reportid, recipients, schedule, format, next_trigger_time) VALUES (?, ?, ?, ?, ?)',
				array ($reportId, json_encode ($dummy), json_encode ($schedule), $format, date ('Y-m-d H:i:s'))
			);
		}

		private static function saveReportSortColumn (PearDatabase $adb, $reportId, $columnIndex, $columnName = null, $sortOrder = null) {
			if (!empty ($reportId)) {
				$adb->pquery ('DELETE FROM vtiger_reportgroupbycolumn WHERE reportid=? AND sortid=?', array ($reportId, $columnIndex));
				$adb->pquery ('DELETE FROM vtiger_reportsortcol WHERE reportid=? AND sortcolid=?', array ($reportId, $columnIndex));
			}

			if (!empty ($columnName)) {
				$adb->pquery ('INSERT INTO vtiger_reportsortcol (sortcolid, reportid, columnname, sortorder) VALUES (?, ?, ?, ?)', array ($columnIndex, $reportId, $columnName, $sortOrder));
				if (CustomReportUtils::isDateField ($columnName)) {
					$adb->pquery ('INSERT INTO vtiger_reportgroupbycolumn (reportid, sortid, sortcolname, dategroupbycriteria) VALUES (?, ?, ?, ?)', array ($reportId, $columnIndex, $columnName, 'None'));
				}
			} else {
				$adb->pquery ('INSERT INTO vtiger_reportsortcol (sortcolid, reportid, columnname, sortorder) VALUES (?, ?, ?, ?)', array ($columnIndex, $reportId, 'none', 'Ascending'));
			}
		}

		private static function saveReportStandardFilterColumn (PearDatabase $adb, $reportId, $columnName, $period, $from, $to) {
			if (!empty ($reportId)) {
				$adb->pquery ('DELETE FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			}

			if (empty ($columnName)) {
				return;
			}

			$adb->pquery (
				'INSERT INTO vtiger_reportdatefilter (datefilterid, datecolumnname, datefilter, startdate, enddate) VALUES (?, ?, ?, ?, ?)',
				array ($reportId, $columnName, $period, $from, $to)
			);
		}

		private static function saveReportTotalColumns (PearDatabase $adb, $reportId, $columns) {
			if (!empty ($reportId)) {
				$adb->pquery ('DELETE FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			}

			if (empty ($columns)) {
				return;
			}

			$index = 0;
			foreach ($columns as $column) {
				$adb->pquery ('INSERT INTO vtiger_reportsummary (reportsummaryid, summarytype, columnname) VALUES (?, ?, ?)', array ($reportId, $index, $column));
				$index++;
			}
		}

		private static function saveReportVisibility (PearDatabase $adb, $reportId, $visibility, $shareWith) {
			if (!empty ($reportId)) {
				$adb->pquery ('DELETE FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			}

			if (($visibility != 'Shared') || (empty ($shareWith))) {
				return;
			}

			foreach ($shareWith as $entity) {
				list ($entityType, $entityId) = explode ('::', $entity);
				$adb->pquery ('INSERT INTO vtiger_reportsharing (reportid, shareid, setype) VALUES (?, ?, ?)', array ($reportId, $entityId, $entityType));
			}
		}

		public static function getApplicationModules (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery (
				'SELECT t.* FROM vtiger_tab t INNER JOIN vtiger_configapps_tab cat ON cat.tabid=t.tabid WHERE cat.config_applicationsid=? ORDER BY t.tabid',
				array ($applicationId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row;
			}
			return $modules;
		}

		public static function getAvailableColumns (PearDatabase $adb, $moduleName, $relatedModuleNames, $currentUser) {
			$columns = array ();
			if (!empty ($moduleName)) {
				$columns     = array_merge ($columns, self::getAvailableFieldsByModuleName ($adb, $moduleName, $currentUser));
				$gridColumns = self::getAvailableFieldsGridByModuleName ($adb, $moduleName, $currentUser);
				if (!empty ($gridColumns) && count ($gridColumns)) {
					$columns = array_merge ($columns, $gridColumns);
				}
			}
			if (!empty ($relatedModuleNames)) {
				foreach ($relatedModuleNames as $relatedModuleName) {
					$columns = array_merge ($columns, self::getAvailableFieldsByModuleName ($adb, $relatedModuleName, $currentUser));
				}
			}
			return count ($columns) > 0 ? $columns : null;
		}

		public static function getAvailableFolders (PearDatabase $adb, $currentUser, $moduleName = null, $isLocked = null) {
			$result = $adb->pquery ('SELECT * FROM vtiger_reportfolder WHERE state=? ORDER BY foldername, folderid', array ('SAVED'));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$folders = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$reports = self::getAvailableReportsByFolderId ($adb, $row ['folderid'], $currentUser, $moduleName, $isLocked);
				if (!empty ($moduleName) && empty($reports)) {
					continue;
				}
				$row ['reports'] = $reports;
				$folders []      = $row;
			}
			return $folders;
		}

		public static function getAvailableModules (PearDatabase $adb) {
			$result = $adb->query (
				'SELECT
					*
				FROM
					vtiger_tab t
				WHERE
					t.presence=0 AND
					t.customized=1 AND
					t.isentitytype=1'
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['tablabel'] = getTranslatedString ($row ['tablabel'], $row ['name']);
				$modules []       = $row;
			}
			usort (
				$modules,
				function ($moduleA, $moduleB) {
					return strcmp ($moduleA ['tablabel'], $moduleB ['tablabel']);
				}
			);
			return $modules;
		}

		public static function getAvailableStandardFilterColumns (PearDatabase $adb, $moduleName, $relatedModuleNames, $currentUser) {
			$columns = array ();
			if (!empty ($moduleName)) {
				$moduleLabel  = self::getModuleLabel ($adb, $moduleName);
				$totalColumns = self::getAvailableDateFieldsByModuleName ($adb, $moduleName, $currentUser);
				if (!empty ($totalColumns)) {
					$columns [ $moduleLabel ] = $totalColumns;
				}
			}
			if (!empty ($relatedModuleNames)) {
				foreach ($relatedModuleNames as $relatedModuleName) {
					$relatedModuleLabel = self::getModuleLabel ($adb, $relatedModuleName);
					$totalColumns       = self::getAvailableDateFieldsByModuleName ($adb, $relatedModuleName, $currentUser);
					if (!empty ($totalColumns)) {
						$columns [ $relatedModuleLabel ] = $totalColumns;
					}
				}
			}
			return count ($columns) > 0 ? $columns : null;
		}

		public static function getAvailableStandardFilterPeriods () {
			$periods = array (
				'prevfy'      => getTranslatedString ('Previous FY', 'Reports'),
				'thisfy'      => getTranslatedString ('Current FY', 'Reports'),
				'nextfy'      => getTranslatedString ('Next FY', 'Reports'),
				'prevfq'      => getTranslatedString ('Previous FQ', 'Reports'),
				'thisfq'      => getTranslatedString ('Current FQ', 'Reports'),
				'nextfq'      => getTranslatedString ('Next FQ', 'Reports'),
				'yesterday'   => getTranslatedString ('Yesterday', 'Reports'),
				'today'       => getTranslatedString ('Today', 'Reports'),
				'tomorrow'    => getTranslatedString ('Tomorrow', 'Reports'),
				'lastweek'    => getTranslatedString ('Last Week', 'Reports'),
				'thisweek'    => getTranslatedString ('Current Week', 'Reports'),
				'nextweek'    => getTranslatedString ('Next Week', 'Reports'),
				'lastmonth'   => getTranslatedString ('Last Month', 'Reports'),
				'thismonth'   => getTranslatedString ('Current Month', 'Reports'),
				'nextmonth'   => getTranslatedString ('Next Month', 'Reports'),
				'last7days'   => getTranslatedString ('Last 7 Days', 'Reports'),
				'last30days'  => getTranslatedString ('Last 30 Days', 'Reports'),
				'last60days'  => getTranslatedString ('Last 60 Days', 'Reports'),
				'last90days'  => getTranslatedString ('Last 90 Days', 'Reports'),
				'last120days' => getTranslatedString ('Last 120 Days', 'Reports'),
				'next30days'  => getTranslatedString ('Next 30 Days', 'Reports'),
				'next60days'  => getTranslatedString ('Next 60 Days', 'Reports'),
				'next90days'  => getTranslatedString ('Next 90 Days', 'Reports'),
				'next120days' => getTranslatedString ('Next 120 Days', 'Reports'),
			);
			asort ($periods);
			return array_merge (array ('custom' => getTranslatedString ('Custom', 'Reports')), $periods);
		}

		public static function getAvailableTotalColumns (PearDatabase $adb, $moduleName, $relatedModuleNames, $currentUser) {
			$columns = array ();
			if (!empty ($moduleName)) {
				$moduleLabel  = self::getModuleLabel ($adb, $moduleName);
				$totalColumns = self::getAvailableTotalColumnsByModuleName ($adb, $moduleName, $currentUser);
				if (!empty ($totalColumns)) {
					$columns [ $moduleLabel ] = $totalColumns;
				}
			}
			if (!empty ($relatedModuleNames)) {
				foreach ($relatedModuleNames as $relatedModuleName) {
					$relatedModuleLabel = self::getModuleLabel ($adb, $relatedModuleName);
					$totalColumns       = self::getAvailableTotalColumnsByModuleName ($adb, $relatedModuleName, $currentUser);
					if (!empty ($totalColumns)) {
						$columns [ $relatedModuleLabel ] = $totalColumns;
					}
				}
			}
			return count ($columns) > 0 ? $columns : null;
		}

		public static function getRelatedModulesByName (PearDatabase $adb, $moduleName) {
		$result = $adb->pquery (
			'SELECT DISTINCT
				trel.tabid,
				trel.name,
				trel.tablabel,
				trel.isentitytype,
				trel.presence,
				trel.customized
			FROM
				vtiger_tab trel
				INNER JOIN vtiger_relatedlists rl ON rl.related_tabid=trel.tabid
				INNER JOIN vtiger_module_report mr ON mr.tabid=rl.related_tabid
				INNER JOIN vtiger_tab t ON t.tabid=rl.tabid
			WHERE
				trel.isentitytype=1 AND
				trel.name NOT IN (?, ?) AND
				trel.presence=0 AND
				rl.label<>? AND
				mr.reportavailable=1 AND
				t.name=?
			UNION
			SELECT DISTINCT
				trel.tabid,
				trel.name,
				trel.tablabel,
				trel.isentitytype,
				trel.presence,
				trel.customized
			FROM
				vtiger_tab trel
				INNER JOIN vtiger_module_report mr ON mr.tabid=trel.tabid
				INNER JOIN vtiger_fieldmodulerel fmr ON fmr.relmodule=trel.name
				INNER JOIN vtiger_tab t ON t.name=fmr.module
			WHERE
				trel.isentitytype=1 AND
				trel.name NOT IN (?, ?) AND
				trel.presence=0 AND
				mr.reportavailable=1 AND
				t.name=?
			ORDER BY name',
			array ('Emails', 'Webmails', 'Activity History', $moduleName, 'Emails', 'Webmails', $moduleName)
		);

		$relatedModules = array ();
		if ($result && ($adb->num_rows ($result) > 0)) {
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['tablabel']                 = getTranslatedString ($row ['tablabel'], $row ['name']);
				$relatedModules [ $row ['name'] ] = $row;
			}
		}

		// Lógica especial para incluir Calendar (Tareas)
		// Criterio 1: Si el módulo tiene la pestaña de Acciones visible en vtiger_views_task
		// Criterio 2: Si el módulo principal es orden_de_trabajo
		$shouldIncludeCalendar = false;

		// Verificar si el módulo tiene la pestaña de Acciones visible en vtiger_views_task
		$taskViewResult = $adb->pquery(
			'SELECT COUNT(*) as count 
			FROM vtiger_views_task 
			WHERE tab_name=? AND status=?',
			array($moduleName, 'SHOW')
		);
		if ($taskViewResult && $adb->num_rows($taskViewResult) > 0) {
			$row = $adb->fetchByAssoc($taskViewResult);
			if ($row['count'] > 0) {
				$shouldIncludeCalendar = true;
			}
		}

		// Verificar si es orden_de_trabajo
		if ($moduleName === 'orden_de_trabajo') {
			$shouldIncludeCalendar = true;
		}

		// Agregar Calendar si cumple los criterios y no está ya en la lista
		if ($shouldIncludeCalendar && !isset($relatedModules['Calendar'])) {
			$calendarResult = $adb->pquery(
				'SELECT 
					t.tabid,
					t.name,
					t.tablabel,
					t.isentitytype,
					t.presence,
					t.customized
				FROM vtiger_tab t
				INNER JOIN vtiger_module_report mr ON mr.tabid=t.tabid
				WHERE t.name=? AND t.presence=0 AND mr.reportavailable=1',
				array('Calendar')
			);
			if ($calendarResult && $adb->num_rows($calendarResult) > 0) {
				$calendarRow = $adb->fetchByAssoc($calendarResult);
				$calendarRow['tablabel'] = getTranslatedString($calendarRow['tablabel'], $calendarRow['name']);
				$relatedModules['Calendar'] = $calendarRow;
			}
		}

		if (empty($relatedModules)) {
			return null;
		}

		uasort (
			$relatedModules,
			function ($moduleA, $moduleB) {
				return strcmp ($moduleA ['tablabel'], $moduleB ['tablabel']);
			}
		);
		return $relatedModules;
	}

		private static function getReportTotalColumns (PearDatabase $adb, $reportId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$columns = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$columns [] = $row ['columnname'];
			}

			return $columns;
		}

		public static function getReportById (PearDatabase $adb, $reportId) {
			$report = self::getReportData ($adb, $reportId);
			if (empty ($report)) {
				return null;
			}

			$groupings = self::getReportSortColumns ($adb, $reportId);
			$modules   = self::getReportModules ($adb, $reportId);
		
			$result = array (
				'advancedfilters'    => self::getReportAdvancedFilters ($adb, $reportId),
				'applicationcodes'   => !empty ($report ['applicationcodes']) ? json_decode ($report ['applicationcodes']) : null,
				'columns'            => self::getReportColumns ($adb, $reportId),
				'description'        => $report ['description'],
				'firstgrouping'      => isset($groupings ['first']) ? $groupings ['first']['grouping'] : null,
				'firstsorting'       => isset($groupings ['first']) ? $groupings ['first']['sorting'] : null,
				'folderid'           => $report ['folderid'],
				'modulename'         => $modules ['main'],
				'name'               => $report ['reportname'],
				'relatedmodulenames' => $modules ['related'],
				'schedule'           => self::getReportScheduling ($adb, $reportId),
				'secondgrouping'     => isset($groupings ['second']) ? $groupings ['second']['grouping'] : null,
				'secondsorting'      => isset($groupings ['second']) ? $groupings ['second']['sorting'] : null,
				'sharewith'          => self::getReportSharing ($adb, $reportId),
				'standardfilter'     => self::getReportStandardFilter ($adb, $reportId),
				'thirdgrouping'      => isset($groupings ['third']) ? $groupings ['third']['grouping'] : null,
				'thirdsorting'       => isset($groupings ['third']) ? $groupings ['third']['sorting'] : null,
				'totalcolumns'       => self::getReportTotalColumns ($adb, $reportId),
				'type'               => $report ['reporttype'],
				'visibility'         => $report ['sharingtype'] != 'Public' ? $report ['sharingtype'] : null,
				'locked'             => $report ['locked'],
			);
		
			// Agregar campos de agrupación adicionales (4-10) si existen
			for ($i = 4; $i <= 10; $i++) {
				$key = 'group' . $i;
				if (isset($groupings[$key])) {
					$result[$key] = $groupings[$key]['grouping'];
					$result['sort' . $i] = $groupings[$key]['sorting'];
				}
			}
		
			return $result;
		}

		public static function createReport (PearDatabase $adb, $arguments, $currentUser) {
			$applicationCodes = !empty ($arguments ['applicationcodes']) ? json_encode ($arguments ['applicationcodes']) : null;
			$type             = $arguments ['type'];
			$visibility       = !empty ($arguments ['visibility']) ? $arguments ['visibility'] : 'Public';

			$result = $adb->query ('SELECT id FROM vtiger_selectquery_seq');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				$adb->pquery ('INSERT INTO vtiger_selectquery_seq (id) VALUES (?)', array (1));
			}

			$adb->startTransaction ();
			$reportId = $adb->getUniqueID ('vtiger_selectquery');
			$adb->pquery ('INSERT INTO vtiger_selectquery (queryid, startindex, numofobjects) VALUES (?, ?, ?)', array ($reportId, 0, 0));
			$adb->pquery (
				'INSERT INTO vtiger_report (reportid, folderid, reportname, description, reporttype, queryid, state, owner, sharingtype, applicationcodes, locked) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($reportId, $arguments ['folderid'], $arguments ['name'], $arguments ['description'], $type, $reportId, 'CUSTOM', $currentUser->id, $visibility, $applicationCodes, $arguments ['locked'])
			);
			self::saveReport ($adb, $reportId, $arguments);
			$adb->completeTransaction ();
		}

		public static function updateReport (PearDatabase $adb, $reportId, $arguments, $currentUser) {
			$applicationCodes = !empty ($arguments ['applicationcodes']) ? json_encode ($arguments ['applicationcodes']) : null;
			$visibility       = !empty ($arguments ['visibility']) ? $arguments ['visibility'] : 'Public';
			$adb->startTransaction ();
			$adb->pquery (
				'UPDATE vtiger_report SET folderid=?, reportname=?, description=?, reporttype=?, owner=?, sharingtype=?, applicationcodes=?, locked=? WHERE reportid=?',
				array ($arguments ['folderid'], $arguments ['name'], $arguments ['description'], $arguments ['type'], $currentUser->id, $visibility, $applicationCodes, $arguments ['locked'], $reportId)
			);
			self::saveReport ($adb, $reportId, $arguments);
			$adb->completeTransaction ();
		}

	}
