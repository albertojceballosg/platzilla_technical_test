<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Data/FieldGridManager.php');
	require_once ('include/platzilla/Data/TaskActivity.php');
	require_once ('include/platzilla/Data/ActivityReportManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/ViewManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/Translator.class.php');

	abstract class DataViewUtils {
		const RECORDS_PER_PAGE = 25;

		const PERMISSION_CAN_DELETE = 'DELETE';
		const PERMISSION_CAN_EDIT   = 'EDIT';
		const PERMISSION_CAN_USE    = 'USE';

		const REAL_DATA   = array ('Held', 'Not Held', 'Planned', 'Postponed', 'Activity', 'Call', 'Meeting');
		const CUSTOM_DATA = array ('Realizada', 'Pendiente','Planeado', 'Pospuesto', 'Actividad', 'Llamada', 'Reunión' );

		/** @var array */
		private static $summaryTable = array ();

		/** @var array */
		private static $summaryLabels  = array ();

		/** @var array */
		private static $summaryColumns = array ();
		/**
		 * @param Field[] $fields
		 * @param FieldModuleReferenceFilter[] $filters
		 * @param Module[] $referencedModules
		 *
		 * @return string|null
		 */
		private static function buildModalViewWhereClauses ($fields, $filters, $referencedModules) {
			if (empty ($filters)) {
				return null;
			}

			$searchCriteria = '';
			foreach ($filters as $index => $filter) {
				if (!isset ($fields [ $filter->getFieldName () ])) {
					continue;
				}

				$field  = $fields [ $filter->getFieldName () ];
				$uiType = $field->getUiType ();
				switch ($filter->getComparator ()) {
					case FieldModuleReferenceFilter::COMPARATOR_EQUALS:
						$comparator = '=';
						$value      = $filter->getValue ();
						break;
					case FieldModuleReferenceFilter::COMPARATOR_NOT_EQUALS:
						$comparator = '!=';
						$value      = $filter->getValue ();
						break;
					default:
						$comparator = 'LIKE';
						$value      = "%{$filter->getValue ()}%";
						break;
				}
				$operator = $filter->getOperator ();
				$operator = (isset ($operator)) && ($index < (count ($filters) - 1)) ? $operator : '';
				switch ($uiType) {
					case FieldInterface::UI_TYPE_MODULE_REFERENCE:
						$references       = $field->getModuleReferences ();
						$reference        = $references [0];
						$referencedModule = $referencedModules [ $reference->getReferencedModuleName () ];
						$sqlCondition = "`{$referencedModule->getTableName ()}`.`{$referencedModule->getEntityIdentifier ()}` {$comparator} '{$value}' {$operator} ";
						$searchCriteria .= $sqlCondition;
						break;
					case FieldInterface::UI_TYPE_OWNER:
						$sqlCondition = "CONCAT(`vtiger_users`.`first_name`, ' ', `vtiger_users`.`last_name`) {$comparator} '{$value}' {$operator} ";
						$searchCriteria .= $sqlCondition;
						break;
					default:
						$sqlCondition = "`{$field->getTableName ()}`.`{$field->getColumnName ()}` {$comparator} '{$value}' {$operator} ";
						$searchCriteria .= $sqlCondition;
						break;
				}
			}
			$finalCondition = !empty ($searchCriteria) ? "({$searchCriteria})" : null;
			
			return $finalCondition;
		}

		/**
		 * @param integer $page
		 *
		 * @return string
		 */
		private static function buildViewQueryLimitClause ($page) {
			if ($page !== null) {
				$startRecord    = (!empty ($page)) && ($page > 0) ? (($page - 1) * self::RECORDS_PER_PAGE) : 0;
				$recordsPerPage = self::RECORDS_PER_PAGE;
				$limitClause    = "{$startRecord}, {$recordsPerPage}";
			} else {
				$limitClause = '';
			}
			return $limitClause;
		}

		/**
		 * @param Field[] $fields
		 * @param string[] $orderBy
		 *
		 * @return array|null
		 */
		private static function buildViewQueryOrderByClauses ($fields, $orderBy) {
			if ((is_array ($orderBy)) && (!empty ($orderBy))) {
				$orderByClauses = array();
				foreach ($orderBy as $orderByFieldName => $orderByDirection) {
					if (isset ($fields [$orderByFieldName])) {
						$orderByClauses [] = "`{$fields [$orderByFieldName]->getTableName ()}`.`{$fields [$orderByFieldName]->getColumnName ()}` {$orderByDirection}";
					}
				}
			} else if (is_scalar ($orderBy)) {
				$orderByClauses = array ($orderBy);
			} else {
				$orderByClauses = array ('`vtiger_crmentity`.`createdtime` DESC');
			}

			return $orderByClauses;
		}

		/**
		 * @param Field[] $fields
		 * @param string[] $searchCriteria
		 *
		 * @return array|null
		 */
		private static function buildViewQueryWhereClauses ($fields, $searchCriteria) {
			$whereClauses = array ();
			$arguments    = array ();
			if ((is_array ($searchCriteria)) && (!empty ($searchCriteria))) {
				foreach ($searchCriteria as $criteria) {
					list ($searchFieldName, $operator, $searchValue) = $criteria;
					if (isset ($fields [ $searchFieldName ])) {
						$whereClauses [] = "`{$fields [$searchFieldName]->getTableName ()}`.`{$fields [$searchFieldName]->getColumnName ()}`{$operator}?";
						$arguments []    = $searchValue;
					}
				}
			}

			return array (
				'where'     => $whereClauses,
				'arguments' => $arguments,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param Users $currentUser
		 *
		 * @return Field[]|null
		 */
		private static function fetchAvailableFields (PearDatabase $adb, $moduleName, Users $currentUser) {
			$is_admin                = null;
			$profileGlobalPermission = null;
			$local_user              = clone $currentUser;
			require ('user_privileges/user_privileges.php');

			if (($is_admin == true) || ($profileGlobalPermission [1] == 0) || ($profileGlobalPermission [2] == 0) || (in_array ($moduleName, array ('Users')))) {
				$sql       = 'SELECT
									f.*
								FROM
									vtiger_field f
									INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
								WHERE
									f.displaytype IN (?, ?, ?, ?) AND
									f.presence IN (?, ?) AND
									f.uitype NOT IN (?, ?, ?, ?,?,?)';
				$arguments = array ($moduleName, Field::DISPLAY_TYPE_ALL, Field::DISPLAY_TYPE_DETAIL_VIEW_ONLY, Field::DISPLAY_TYPE_LIST_VIEW_ONLY, Field::DISPLAY_TYPE_PASSWORD, Field::PRESENCE_USER_DEFINED, Field::PRESENCE_VISIBLE, Field::UI_TYPE_APP, Field::UI_TYPE_TABLE_FIELD, Field::UI_TYPE_ATTACHMENTS, Field::UI_TYPE_IMAGE_REFERENCE, Field::UI_TYPE_IMAGE_DISPLAY, Field::UI_TYPE_GRID);
			} else {
				$sql       = 'SELECT DISTINCT
								f.*
							FROM
								vtiger_field f
								INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
								INNER JOIN vtiger_profile2field p2f ON p2f.fieldid=f.fieldid
								INNER JOIN vtiger_def_org_field dof ON dof.fieldid=f.fieldid
							WHERE
								f.displaytype IN (?, ?, ?, ?) AND
								f.presence IN (?, ?) AND
								f.uitype NOT IN (?, ?, ?, ?, ?, ?) AND
								p2f.visible=? AND
								dof.visible=?';
				$arguments = array ($moduleName, Field::DISPLAY_TYPE_ALL, Field::DISPLAY_TYPE_DETAIL_VIEW_ONLY, Field::DISPLAY_TYPE_LIST_VIEW_ONLY, Field::DISPLAY_TYPE_PASSWORD, Field::PRESENCE_USER_DEFINED, Field::PRESENCE_VISIBLE, Field::UI_TYPE_APP, Field::UI_TYPE_TABLE_FIELD, Field::UI_TYPE_ATTACHMENTS, Field::UI_TYPE_IMAGE_REFERENCE, Field::UI_TYPE_IMAGE_DISPLAY, Field::UI_TYPE_GRID ,0, 0);
			}
			$result = $adb->pquery ($sql, $arguments);
			if ($adb->num_rows ($result) > 0) {
				$fields = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$fieldName             = $row ['fieldname'];
					$fields [ $fieldName ] = Field::getInstance ($row ['typeofdata'])
						->setBlockId (intval ($row ['block']))
						->setCalculationName ($row ['paradicional'])
						->setColumnName ($row ['columnname'])
						->setDefaultValue ($row ['defaultvalue'])
						->setDisplayType (intval ($row ['displaytype']))
						->setGeneratedType (intval ($row ['generatedtype']))
						->setId (intval ($row ['fieldid']))
						->setLabel ($row ['fieldlabel'])
						->setLocked ($row ['locked'] == 1)
						->setMassEditable (intval ($row ['masseditable']))
						->setModuleName ($moduleName)
						->setName ($fieldName)
						->setPresence (intval ($row ['presence']))
						->setQuickCreate (intval ($row ['quickcreate']))
						->setQuickCreateSequence (isset ($row ['quickcreatesequence']) ? intval ($row ['quickcreatesequence']) : null)
						->setReadOnly (intval ($row ['readonly']))
						->setSequence (intval ($row ['sequence']))
						->setTableName ($row ['tablename'])
						->setUiType ($row ['uitype']);
				}
			} else {
				$fields = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $fields;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Field[] $fields
		 * @param ViewColumn[] $columns
		 *
		 * @return array
		 */
		private static function fetchColumnsData (PearDatabase $adb, $fields, $columns) {
			$columnsData = array ();
			foreach ($columns as $column) {
				$field = isset ($fields [ $column->getFieldName () ]) ? $fields [ $column->getFieldName () ] : null;
				if (empty ($field)) {
					continue;
				}

				$columnData = array (
					'fieldlabel' => ($field->getUiType () != $field::UI_TYPE_GRID) ? Translator::translate ($field->getLabel (), $field->getModuleName ()) : $column->getLabel(),
					'fieldname'  => ($field->getUiType () != $field::UI_TYPE_GRID) ? $field->getName () : $column->getColumnName(),
					'typeofdata' => $field->getTypeOfData (),
					'uitype'     => ($field->getUiType () != $field::UI_TYPE_GRID) ? $field->getUiType () : '9',
				);
				if ($field->isMandatory ()) {
					if (in_array ($field->getUiType (), array (Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST))) {
						$columnData ['picklistvalues'] = PicklistManager::getInstance ($adb)->fetchPicklistRawValues ($field->getName (), true);
					} else if ($field->getUiType () == Field::UI_TYPE_GLOBAL_PICKLIST) {
						$columnData ['picklistvalues'] = GlobalPicklistManager::getInstance ($adb)->fetchPicklistRawValues ($field->getName ());
					}
				}

				$columnsData [] = $columnData;
			}
			return $columnsData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param string $referencedModuleName
		 * @param string $keywordField
		 * @param string $keywordValue
		 * @param string[] $requestedFieldValues
		 *
		 * @return array
		 */
		private static function getFieldModuleReferenceFilters (PearDatabase $adb, $moduleName, $fieldName, $referencedModuleName, $keywordField, $keywordValue, $requestedFieldValues) {

			$filters = array ();
		
			// Si moduleName está vacío, es un subcampo de grid - no tiene referencias configuradas
			if (empty($moduleName)) {
				// Los subcampos de grid no tienen referencias en vtiger_fieldmodulerel
				// Solo se aplicará el filtro de keyword si existe
			} else {
				// Obtener filtros configurados para campos normales
				$reference = FieldModuleReferenceManager::getInstance ($adb)->fetchReference ($moduleName, $fieldName, $referencedModuleName);

				// Procesar filtros configurados
				if (!empty ($reference)) {
					$oldFilters   = $reference->getFilters ();
					$totalFilters = count ($oldFilters);
					for ($i = 0; $i < $totalFilters; $i++) {
						$filterFieldName = $oldFilters [ $i ]->getFieldName ();
						if (($oldFilters [ $i ]->getValueType () == FieldModuleReferenceFilter::TYPE_SOURCE_FIELD) && (empty ($requestedFieldValues [ $filterFieldName ]))) {
							continue;
						} else if ($oldFilters [ $i ]->getValueType () == FieldModuleReferenceFilter::TYPE_SOURCE_FIELD) {
								$sourceFieldValue = isset ($requestedFieldValues [ $oldFilters [ $i ]->getFieldName () ]) ? $requestedFieldValues [ $oldFilters [ $i ]->getFieldName () ] : null;
								$filters [] = $oldFilters [ $i ]->setValue ($sourceFieldValue);
							} else {
								$filters [] = $oldFilters [ $i ];
							}
						}
				}
				// No registrar error si no hay referencia - es normal para algunos campos
			}
			if (isset ($keywordField)) {
				$searchFilter = FieldModuleReferenceFilter::getInstance ()
					->setComparator (FieldModuleReferenceFilter::COMPARATOR_CONTAINS)
					->setFieldName ($keywordField)
					->setValue ($keywordValue);
				
				// Si hay filtros configurados, agregar operador AND
				if (!empty($filters)) {
					$lastFilter = &$filters[count($filters) - 1];
					$lastFilter->setOperator(FieldModuleReferenceFilter::OPERATOR_AND);
				}

				$filters [] = $searchFilter;
			}

			return array_values ($filters);
		}

		/**
		 * @param ViewColorFilterGroup[] $colorCondition
		 * @param string $moduleName
		 * @param array $resultAll
		 *
		 * @return string
		 */
		private static function getColorRule ($colorCondition, $moduleName, $resultAll) {
			$color        = 'white';
			$numCondition = count ($colorCondition);
			for ($i = 0; $i < $numCondition; $i++) {
				$result = false;
				$glue   = '';
				if ($colorCondition[ $i ]->getColor() != null) {
					$j = 0;
					foreach ($colorCondition[ $i ]->getFilters() as $condition) {
						$rowfilter  = count ($colorCondition[ $i ]->getFilters());
						$operator   = $condition->getComparator();
						$value      = $condition->getValue();
						$fieldValue = ($condition->getDataType() == 'V') ? Translator::translate ($resultAll[ $condition->getFieldName() ], $moduleName) : $resultAll[ $condition->getFieldName() ];
						$filter     = self::getConditionColor ($operator, $fieldValue, $value);
						if ($condition->getOperator() != '') {
							$glue = $condition->getOperator ();
						}
						if ($j == 0) {
							$result = $filter;
							if ($rowfilter == 1) {
								break;
							}
						} else if (!empty($glue)) {
							$result = self::getOperatorColor ($result, $glue, $filter);
							$glue   = '';
						}
						$j++;
					}

					if ($result) {
						$color = $colorCondition[ $i ]->getColor();
						break;
					}
				}
			}

			return $color;
		}

		/**
		 * @param boolean $result
		 * @param string $operator
		 * @param string $condition
		 *
		 * @return boolean
		 */
		private static function getOperatorColor (&$result, $operator, $condition) {
			switch ($operator) {
				case 'and':
					$result = $result && $condition;
					break;
				case 'or':
					$result = $result || $condition;
					break;
				default:
					return $result;
			}
			return $result;
		}

		/**
		 * @param string $operator
		 * @param string $field
		 * @param string $value
		 *
		 * @return boolean
		 */
		private static function getConditionColor ($operator, $field, $value) {
			$resultOperation = false;
			switch ($operator) {
				case 'e':
					if ($field == $value) {
						$resultOperation = true;
					}
					break;
				case 'n':
					if ($field != $value) {
						$resultOperation = true;
					}
					break;
				case 's':
					// Comienza con
					if (strpos ($field, $value) === 0) {
						$resultOperation = true;
					}
					break;
				case 'ew':
					// Termina en
					$lfd       = strlen ($field);
					$lvl       = strlen ($value);
					$subCadena = substr ($field, ($lfd - $lvl));
					if ($subCadena == $value) {
						$resultOperation = true;
					}
					break;
				case 'c':
					//Contiene
					$pos = strpos ($field, $value);
					if ($pos !== false) {
						$resultOperation = true;
					}
					break;
				case 'k':
					//No contiene
					$pos = strpos ($field, $value);
					if ($pos === false) {
						$resultOperation = true;
					}
					break;
				case 'l':
					if ($field < $value) {
						$resultOperation = true;
					}
					break;
				case 'g':
					if ($field > $value) {
						$resultOperation = true;
					}
					break;
				case 'm':
					if ($field <= $value) {
						$resultOperation = true;
					}
					break;
				case 'h':
					if ($field >= $value) {
						$resultOperation = true;
					}
					break;
				case 'a':
					//Despues
					if ($field > $value) {
						$resultOperation = true;
					}
					break;
				case 'b':
					//Antes
					if ($field < $value) {
						$resultOperation = true;
					}
					break;
				default:
					$resultOperation = false;
					break;
			}
			return $resultOperation;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private static function createFirstTaskGroup ($adb, $userId) {
			$adb->pquery (
				'INSERT INTO vtiger_activity_categories (categoryid, smownerid, name, description) VALUES (?, ?, ?, ?)',
				array (10, $userId, 'Grupo actual', 'Esta es la categoría de actividades comunes')
			);
			return self::getAvailableTaskCategories ($adb, $userId);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $module
		 *
		 * @return array
		 */
		private static function getActivityRelByModule ($adb, $module) {
			if (empty($module)) {
				return array ();
			}
			$result = $adb->pquery (
				'SELECT
						activityid
					  FROM
					  	vtiger_seactivityrel rel
					  INNER JOIN
					  	vtiger_crmentity crm ON crm.crmid = rel.crmid AND crm.deleted=?
					  WHERE
					  	crm.setype=?',
				array (0, $module)
			);
			if ($adb->num_rows ($result) > 0) {
				$activityIds = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$activityIds[] = $row ['activityid'];
				}
			}
			DatabaseUtils::closeResult ($result);
			return isset ($activityIds) ? $activityIds : array ();
		}
		
		private static function getRelatedTo ($adb, $activityId) {
			$result = $adb->pquery (
				'SELECT
						sea.crmid,
						crm.setype
					FROM vtiger_seactivityrel sea
					INNER JOIN vtiger_crmentity crm ON crm.crmid = sea.crmid AND deleted=0
					WHERE sea.activityid=?
					LIMIT 1',
				array ($activityId)
			);
			
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$relatedTo = array();
				$relatedTo['relatedTo'] = null;
				$relatedTo['relatedId'] = $row ['crmid'];
				$relatedTo['tabName']   = $row ['setype'];
			}
			DatabaseUtils::closeResult ($result);
			return isset ($relatedTo) ? $relatedTo : null;
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param integer $activityId
		 * @param string $moduleName
		 *
		 * @return array|string
		 * @throws Exception
		 */
		private static function getActivityRelatedTo ($adb, $activityId, $moduleName) {
			if (empty($activityId)) {
				return null;
			} else if (empty($moduleName)) {
				return self::getRelatedTo($adb, $activityId);
			}
			$result = $adb->pquery (
				'SELECT
						sea.crmid,
						en.*
					FROM vtiger_seactivityrel sea
					INNER JOIN vtiger_crmentity crm ON crm.crmid = sea.crmid AND deleted=0
					INNER JOIN vtiger_entityname en ON en.modulename = crm.setype
					WHERE sea.activityid=? AND
					crm.setype=?
					LIMIT 1',
				array ($activityId, $moduleName)
			);
			
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				
				if (!empty($row ['fieldidentifier'])) {
					$fieldName = $row ['fieldidentifier'];
				} else if (!empty($row ['fieldname'])) {
					$fieldName = $row ['fieldname'];
				} else {
					$fieldName = $row ['entityidcolumn'];
				}
				DatabaseUtils::closeResult ($result);
				$resultRelated = $adb->query ("SELECT {$fieldName} FROM {$row ['tablename']} WHERE {$row ['entityidfield']}= {$row ['crmid']}");
				$relatedTo = array();
				if ($adb->num_rows ($resultRelated) > 0) {
					$rowRelated             = $adb->fetchByAssoc ($resultRelated, -1, false);
					$relatedTo['relatedTo'] = $rowRelated[ $fieldName ];
					$relatedTo['relatedId'] = $row ['crmid'];
					$relatedTo['tabName']   = $row ['modulename'];
					
				}
			}
			DatabaseUtils::closeResult ($resultRelated);
			return isset ($relatedTo) ? $relatedTo : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $activityId
		 *
		 * @return integer
		 * @throws Exception
		 */
		private static function getTotalFeeddBacks ($adb, $activityId) {
			if (empty($activityId)) {
				return 0;
			}
			$result = $adb->pquery('SELECT COUNT(activityid) AS total FROM vtiger_activity_feedback WHERE activityid=?', array($activityId));
			$totalResult = 0;
			if ($adb->num_rows ($result) > 0) {
				$row         = $adb->fetchByAssoc ($result, -1, false);
				$totalResult = $row ['total'];
			}
			DatabaseUtils::closeResult ($result);
			return isset ($totalResult) ? $totalResult : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $activityId
		 *
		 * @return integer
		 * @throws Exception
		 */
		private static function getTotalReports($adb, $activityId) {
			if (empty($activityId)) {
				return 0;
			}
			$result = $adb->pquery('SELECT COUNT(activityid) AS total FROM vtiger_activity_report WHERE activityid=? AND deleted = 0', array($activityId));
			$totalResult = 0;
			if ($adb->num_rows ($result) > 0) {
				$row         = $adb->fetchByAssoc ($result, -1, false);
				$totalResult = $row ['total'];
			}
			DatabaseUtils::closeResult ($result);
			return isset ($totalResult) ? $totalResult : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param View $view
		 * @param string $parentRoles
		 *
		 * @return boolean
		 */
		private static function isPrivateViewOwnedBySubordinateRole (PearDatabase $adb, View $view, $parentRoles) {
			$result               = $adb->pquery (
				'SELECT
					cv.cvid
				FROM
					vtiger_customview cv
				WHERE
					cv.cvid=? AND
					cv.userid IN (SELECT u2r.userid FROM vtiger_user2role u2r INNER JOIN vtiger_role r ON r.roleid=u2r.roleid AND r.parentrole LIKE ?)
				LIMIT 1',
				array ($view->getId (), "%{$parentRoles}::%")
			);
			$isOwnedBySubordinate = ($adb->num_rows ($result) > 0);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $isOwnedBySubordinate;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $forModuleName
		 * @param string $forFieldName
		 * @param string $keywordField
		 * @param string $keywordValue
		 * @param string[] $requestedFieldValues
		 * @param Users $currentUser
		 *
		 * @return array|null
		 */
		public static function buildDefaultModalViewQueryParts (PearDatabase $adb, $moduleName, $forModuleName, $forFieldName, $keywordField, $keywordValue, $requestedFieldValues, $currentUser) {
			if ($moduleName == 'Calendar') {
				$view = ViewManager::getInstance ($adb)->fetchView ($moduleName, 'Modal proyectos', true);
				if (empty ($view)) {
					$view = ViewManager::getInstance ($adb)->fetchDefaultView ($moduleName, true);
				}
			} else {
				$view = ViewManager::getInstance ($adb)->fetchDefaultView ($moduleName, true);
			}

			if (empty ($view)) {
				return null;
			} else {
				return self::buildModalViewQueryParts ($adb, $moduleName, $view->getName (), $forModuleName, $forFieldName, $keywordField, $keywordValue, $requestedFieldValues, $currentUser);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $viewName
		 * @param string $forModuleName
		 * @param string $forFieldName
		 * @param string $keywordField
		 * @param string $keywordValue
		 * @param string[] $requestedFieldValues
		 * @param Users $currentUser
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function buildModalViewQueryParts (PearDatabase $adb, $moduleName, $viewName, $forModuleName, $forFieldName, $keywordField, $keywordValue, $requestedFieldValues, $currentUser) {
			if ((empty ($moduleName)) || (empty ($viewName))) {
				return null;
			}

			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule ($moduleName, true);
			if (empty ($module)) {
				return null;
			}

			$view = ViewManager::getInstance ($adb)->fetchView ($moduleName, $viewName);
			if (empty ($view)) {
				return null;
			}

			$columns = $view->getColumns ();
			if (empty ($columns)) {
				return null;
			}

			$fields = self::fetchAvailableFields ($adb, $moduleName, $currentUser);
			if (empty ($fields)) {
				return null;
			}

			$referencedModules = array ();
			$selectClauses     = array ('`vtiger_crmentity`.`crmid`');
			if (!empty ($currentUser->id)) {
				$selectClauses [] = '`vtiger_crmentity`.`smcreatorid`';
				$selectClauses [] = '`vtiger_crmentity`.`smownerid`';
			}
			$fromClauses       = array (
				'`vtiger_crmentity`',
				"INNER JOIN `{$module->getTableName ()}` ON `{$module->getTableName ()}`.`{$module->getEntityIdColumnName ()}`=`vtiger_crmentity`.`crmid`",
			);
			$whereClauses      = array (
				'`vtiger_crmentity`.`deleted`=0',
				"`vtiger_crmentity`.`setype`='{$module->getName ()}'",
			);
			foreach ($fields as $field) {
				$tableName = $field->getTableName ();
				$uiType    = $field->getUiType ();
				if ($uiType == Field::UI_TYPE_MODULE_REFERENCE) {
					$references = FieldModuleReferenceManager::getInstance ($adb)->fetchReferences ($moduleName, $field->getName ());
					$field->setModuleReferences ($references);
					$referencedModuleName                        = $references [0]->getReferencedModuleName ();
					$referencedModule                            = $mm->fetchModule ($referencedModuleName, true);
					$referencedModuleName                        = $referencedModule->getName ();
					if (in_array ($referencedModuleName, array_keys ($referencedModules))) {
						continue;
					}
					$referencedModules [ $referencedModuleName ] = $referencedModule;
					$selectClauses []                            = "`{$referencedModule->getTableName ()}`.`{$referencedModule->getEntityIdentifier ()}` AS `{$field->getName ()}`";
					$fromClauses []                              = "LEFT JOIN `{$referencedModule->getTableName ()}` ON `{$referencedModule->getTableName ()}`.`{$referencedModule->getEntityIdColumnName ()}`=`{$tableName}`.`{$field->getColumnName ()}`";
				} else if ($uiType == Field::UI_TYPE_OWNER) {
					$selectClauses [] = "(SELECT CONCAT(`vtiger_users`.`first_name`, ' ', `vtiger_users`.`last_name`) FROM `vtiger_users` WHERE `vtiger_users`.`id`=`{$field->getTableName ()}`.`{$field->getColumnName ()}`) AS `{$field->getName ()}`";
				} else if ($uiType == Field::UI_TYPE_GRID) {
					foreach ($columns as $column) {
						if ($column->getFieldName () != $field->getName()) {
							continue;
						}
						$tableName = 'vtiger_grid_summary_' . $field->getName();
						$gridColumn = "`{$tableName}`.`{$column->getColumnName()}` AS `{$column->getColumnName()}`";
						$fromGridColumn = "LEFT JOIN `{$tableName}` ON `{$tableName}`.`recordid`=`vtiger_crmentity`.`crmid`";
						if (!in_array ($tableName, array_values (self::$summaryTable))) {
							self::$summaryTable = array ($column->getModuleName () => $tableName);
						}
						if (!in_array ($gridColumn, $selectClauses)) {
							$selectClauses [] = $gridColumn;
						}
						if (!in_array($fromGridColumn, $fromClauses)) {
							$fromClauses [] = $fromGridColumn;
						}
						self::$summaryLabels = array ($field->getColumnName() => $field->getLabel());
					}
				} else if (isset ($uiType)) {
					$selectClauses [] = "`{$field->getTableName ()}`.`{$field->getColumnName ()}` AS `{$field->getName ()}`";
					if (!in_array ($tableName, array ('vtiger_crmentity', $module->getTableName ()))) {
						if($field->getTableName () == 'vtiger_activity_reminder') {
							$fromClauses [] = 'LEFT OUTER JOIN vtiger_activity_reminder ON vtiger_activity_reminder.activity_id = vtiger_activity.activityid';
						} else {
							$fromClauses [] = "INNER JOIN `{$field->getTableName ()}` ON `{$field->getTableName ()}`.`{$field->getColumnName ()}`=`vtiger_crmentity`.`crmid`";
						}
					}
				}
			}

			$filters             = self::getFieldModuleReferenceFilters ($adb, $forModuleName, $forFieldName, $moduleName, $keywordField, $keywordValue, $requestedFieldValues);
			$filtersWhereClauses = self::buildModalViewWhereClauses ($fields, $filters, $referencedModules);
			if (!empty ($filtersWhereClauses)) {
				$whereClauses [] = $filtersWhereClauses;
			}

			if (count (self::$summaryTable)) {
				foreach (self::$summaryTable as $moduleName => $tableName) {
					$dummy = explode ('_', $tableName, 4);
					self::$summaryColumns = GridFieldUtils::createTempGridValues ($adb, $moduleName, $dummy [3], $tableName, false);
				}
			}
			return array (
				'fields' => self::fetchColumnsData ($adb, $fields, $columns),
				'select' => join (', ', array_unique ($selectClauses)),
				'from'   => join (' ', array_unique ($fromClauses)),
				'where'  => join (' AND ', $whereClauses),
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param View $view
		 * @param Users $currentUser
		 * @param integer|null $page
		 * @param string[]|null $orderBy
		 * @param string[]|null $searchCriteria
		 *
		 * @return array|null
		 */
		public static function buildViewQueryParts (PearDatabase $adb, View $view, Users $currentUser, $page = null, $orderBy = null, $searchCriteria = null) {
			if (empty ($view)) {
				return null;
			}
			$moduleName = $view->getModuleName ();
			$mm         = ModuleManager::getInstance ($adb);
			$module     = $mm->fetchModule ($moduleName, true);
			if (empty ($module)) {
				return null;
			}

			$columns = $view->getColumns ();
			$fields  = self::fetchAvailableFields ($adb, $moduleName, $currentUser);
			$entity  = PlatformUtils::getCrmEntity ($adb, $moduleName);

			$referencedModules = array ();
			$selectClauses     = array ('`vtiger_crmentity`.`crmid`', '`vtiger_crmentity`.`smcreatorid`', '`vtiger_crmentity`.`smownerid`', 'TIMESTAMPDIFF(DAY,`vtiger_crmentity`.`createdtime`,NOW()) AS days', '`vtiger_activity`.`progress` AS progreso');
			$fromClauses       = array (
				'`vtiger_crmentity`',
				"INNER JOIN `{$module->getTableName ()}` ON `{$module->getTableName ()}`.`{$module->getEntityIdColumnName ()}`=`vtiger_crmentity`.`crmid`",
			);
			if (!empty ($entity)) {
				$fromClauses [] = $entity->getNonAdminAccessControlQuery ($moduleName, $currentUser);
			}
			$whereClauses = array (
				'`vtiger_crmentity`.`deleted`=?',
				'`vtiger_crmentity`.`setype`=?',
			);
			$arguments    = array (0, $module->getName ());
			foreach ($fields as $field) {
				$tableName = $field->getTableName ();
				$uiType    = $field->getUiType ();
				if ($uiType == Field::UI_TYPE_MODULE_REFERENCE) {
					$references = FieldModuleReferenceManager::getInstance ($adb)->fetchReferences ($moduleName, $field->getName ());
					$field->setModuleReferences ($references);
					$referencedModuleName                        = $references [0]->getReferencedModuleName ();
					$referencedModule                            = $mm->fetchModule ($referencedModuleName, true);
					$referencedModuleName                        = $referencedModule->getName ();
					$referencedModules [ $referencedModuleName ] = $referencedModule;
					$selectClauses []                            = "`{$referencedModule->getTableName ()}`.`{$referencedModule->getEntityIdentifier ()}` AS `{$field->getName ()}`";
					$fromClauses []                              = "LEFT JOIN `{$referencedModule->getTableName ()}` ON `{$referencedModule->getTableName ()}`.`{$referencedModule->getEntityIdColumnName ()}`=`{$tableName}`.`{$field->getColumnName ()}`";
				} else if ($uiType == Field::UI_TYPE_OWNER) {
					$selectClauses [] = "(SELECT CONCAT(`vtiger_users`.`first_name`, ' ', `vtiger_users`.`last_name`) FROM `vtiger_users` WHERE `vtiger_users`.`id`=`{$field->getTableName ()}`.`{$field->getColumnName ()}`) AS `{$field->getName ()}`";
					$selectClauses [] = "(SELECT imagename FROM `vtiger_users` WHERE `vtiger_users`.`id`=`{$field->getTableName ()}`.`{$field->getColumnName ()}`) AS `useravatar`";
				} else if (isset ($uiType)) {
					$selectClauses [] = "`{$field->getTableName ()}`.`{$field->getColumnName ()}` AS `{$field->getName ()}`";
					if (!in_array ($tableName, array ('vtiger_crmentity', $module->getTableName ()))) {
						$fromClauses [] = "INNER JOIN `{$field->getTableName ()}` ON `{$field->getTableName ()}`.`{$field->getColumnName ()}`=`vtiger_crmentity`.`crmid`";
					}
				}
			}
			$dummyColumns = self::fetchColumnsData ($adb, $fields, $columns);
			if ($moduleName == 'Calendar') {
				$selectClauses [] = 'vtiger_activity.activityid AS idActividad';
				$selectClauses [] = 'vtiger_activity.combined_condition AS combined_condition';
				$selectClauses [] = "IFNULL((SELECT vtiger_crmentity.setype FROM  vtiger_seactivityrel INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seactivityrel.crmid WHERE  vtiger_crmentity.setype IS NOT NULL AND vtiger_seactivityrel.activityid = idActividad GROUP by idActividad),'Tarea') AS Modulename";
				$dummyColumns     = array_merge (array(array ('fieldlabel' => 'Relacionado a', 'fieldname' => 'modulename', 'typeofdata' => '', 'uitype' => '')), $dummyColumns);
			}
			$orderByClauses     = self::buildViewQueryOrderByClauses ($fields, $orderBy);
			$limitClause        = self::buildViewQueryLimitClause ($page);
			$searchWhereClauses = self::buildViewQueryWhereClauses ($fields, $searchCriteria);

			$whereClauses       = array_merge ($whereClauses, $searchWhereClauses ['where']);
			$arguments          = array_merge ($arguments, $searchWhereClauses ['arguments'], array (0, $module->getName ()), $searchWhereClauses ['arguments']);

			return array (
				'columns'          => $dummyColumns,
				'fields'           => $fields,
				'select'           => join (', ', array_unique ($selectClauses)),
				'from'             => join (' ', array_unique ($fromClauses)),
				'where'            => join (' AND ', array_unique ($whereClauses)),
				'orderby'          => join (', ', array_unique ($orderByClauses)),
				'limit'            => $limitClause,
				'arguments'        => $arguments,
				'entityidentifier' => $module->getEntityIdentifier (),
			);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $mod_strings
		 *
		 * @return array|null
		 */
		public static function fetchActivityType ($adb, $moduleName, $mod_strings) {
			$result = $adb->query('SELECT * FROM vtiger_activitytype ORDER BY activitytype');
			if ($adb->num_rows($result) > 0) {
				$availableActivityTypes = array ();
				while ($row = $adb->fetchByAssoc($result, -1, false)) {
					$activityType = $row ['activitytype'];
					if ($activityType == 'Activity') {
						if ($moduleName == 'orden_de_trabajo') {
							$availableActivityTypes [$activityType] = $mod_strings [$activityType];
						}
					} else {
						$availableActivityTypes [$activityType] = $mod_strings [$activityType];
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($availableActivityTypes)) ? $availableActivityTypes : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param Users $currentUser
		 *
		 * @return null|View[]
		 */
		public static function fetchAvailableViews (PearDatabase $adb, $moduleName, Users $currentUser) {
			$views = ViewManager::getInstance ($adb)->fetchViews ($moduleName, false, true);
			if (empty ($views)) {
				return null;
			}

			$availableViews = array ();
			foreach ($views as $view) {
				$permissions = self::fetchViewPermissions ($adb, $view, $currentUser);
				if ((is_array ($permissions)) && (in_array (self::PERMISSION_CAN_USE, $permissions))) {
					if ($view->getName () == 'PENDING TASK') {
						$view->setName ('Tareas pendientes');
					} else if ($view->getName () == 'COMPLETED TASK') {
						$view->setName ('Tareas realizadas');
					}
					$availableViews [] = $view;
				}
			}
			return $availableViews;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return View|null
		 */
		public static function fetchDefaultView (PearDatabase $adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}

			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule ($moduleName, true);
			if (empty ($module)) {
				return null;
			}
			return ViewManager::getInstance ($adb)->fetchDefaultView ($moduleName);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $activityId
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchInviteesByActivity ($adb, $activityId, $userId) {
			if (empty($activityId)) {
				return null;
			}
			$result = $adb->pquery(
				'SELECT
						  inviteeid,
						  CONCAT(u.first_name, " ",u.last_name) AS username
					  FROM
					  	  vtiger_invitees sa
					  INNER JOIN vtiger_users u ON u.id = sa.inviteeid
					  WHERE
					  	activityid=? AND
					  	u.id !=?',
				array ($activityId, $userId)
			);
			if ($adb->num_rows ($result) > 0) {
				$usersId   = array ();
				$usersName = array();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$usersId   []  = $row ['inviteeid'];
					$usersName [] = $row ['username'];
				}
				$names = '(' . join (', ', $usersName) . ')';
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return(isset($usersId)) ? array ('userId' => $usersId, 'userName' => $names) : array ('userId' => '', 'userName' => '');
		}
		
		public static function fetchRelatedActivities ($adb, $crmId, $eventoStatus = '') {
			if (empty ($crmId)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT DISTINCT rel.activityid, act.date_start, act.due_date
				FROM vtiger_seactivityrel rel
				INNER JOIN vtiger_crmentity crm ON rel.crmid = rel.crmid AND crm.deleted=?
				INNER JOIN vtiger_activity act ON act.activityid = rel.activityid
				WHERE rel.crmid=? AND act.eventstatus IS NOT NULL AND act.eventstatus NOT IN (?)',
				array (0, $crmId, $eventoStatus)
			);
			if ($adb->num_rows ($result)) {
				$activities = array();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$activities [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			return (isset($activities)) ? $activities : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $period
		 * @param integer $userId
		 * @param boolean $withReports
		 *
		 * @return null|TaskActivity[]
		 * @throws Exception
		 */
		public static function fetchTaskToDailyReport ($adb, $period, $userId, $withReports = false) {
			
			$rangeInit = date('Y-m-d');
			$rangeEnd = date('Y-m-d');
			if (count ($period)) {
				$rangeInit = $period ['startdate'];
				$rangeEnd = $period ['enddate'];
				if (strpos ($rangeInit, '/') !== false) {
					$dateParts = explode ('/', $rangeInit);
					if (count ($dateParts) == 3) {
						$rangeInit = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";
					}
				} else if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $rangeInit)) {
					$dateParts = explode ('-', $rangeInit);
					$rangeInit = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";
				}
				if (strpos ($rangeEnd, '/') !== false) {
					$dateParts = explode ('/', $rangeEnd);
					if (count ($dateParts) == 3) {
						$rangeEnd = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";
					}
				} else if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $rangeEnd)) {
					$dateParts = explode ('-', $rangeEnd);
					$rangeEnd = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";
				}
			} else {
				$period ['startdate'] = $rangeInit;
				$period ['enddate'] = $rangeEnd;
			}
			// IMPORTANTE: Actualizar period con fechas en formato BD para fetchActivityReportByActivityId
			$period ['startdate'] = $rangeInit;
			$period ['enddate'] = $rangeEnd;
			
			$sql = "SELECT task.*, IFNULL((SELECT vtiger_crmentity.setype FROM vtiger_crmentity WHERE vtiger_crmentity.crmid = task.related_id), 'Calendar') AS relatedmodule
				FROM vtiger_activity task
				INNER JOIN vtiger_crmentity crm ON crm.crmid = task.activityid
				WHERE crm.deleted = 0 AND crm.smcreatorid={$userId}
					AND task.eventstatus != 'Held'
					AND DATE(IFNULL(task.date_start, crm.createdtime)) <= DATE('{$rangeInit}')
					AND (task.due_date IS NULL OR task.due_date = '' OR DATE(task.due_date) >= DATE('{$rangeInit}'))
				UNION
				SELECT ac.*, IFNULL((SELECT vtiger_crmentity.setype FROM vtiger_crmentity WHERE vtiger_crmentity.crmid = ac.related_id), 'Calendar') AS relatedmodule
				FROM vtiger_activity_report ar
				INNER JOIN vtiger_crmentity crm ON crm.crmid = ar.activityid
				INNER JOIN vtiger_activity ac ON ac.activityid = ar.activityid
				WHERE crm.deleted = 0 AND ar.deleted = 0 AND ar.userid = {$userId} AND DATE(ar.activity_report_date) = DATE('{$rangeInit}')";
			
			$result = $adb->query ($sql);
			$numRows = $adb->num_rows ($result);
			
			if ($numRows > 0) {
				$idProcessed = array ();
				$arm = ActivityReportManager::getInstance ($adb);
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (in_array ($row ['activityid'], $idProcessed)) {
						continue;
					}
					$relatedModule = isset($row['relatedmodule']) ? $row['relatedmodule'] : 'Calendar';
					$activityReports = null;
					if ($withReports) {
						$activityReports = $arm->fetchActivityReportByActivityId ($row ['activityid'], $period);
					}
					// Obtener el título del registro relacionado usando vtiger_entityname
					$relatedTitle = '';
					$relatedId = intval($row['related_id']);
					if ($relatedId > 0 && !empty($relatedModule)) {
						$entityNames = getEntityName($relatedModule, array($relatedId));
						if (!empty($entityNames) && isset($entityNames[$relatedId])) {
							$relatedTitle = $entityNames[$relatedId];
						}
					}
					$activityTask [] = TaskActivity::getInstance ()
						->setActivityId ($row ['activityid'])
						->setDescription ($row ['description'])
						->setProgress (floatval ($row ['progress']))
						->setRelatedId ($relatedId)
						->setModuleName (null)
						->setRelatedModule ($relatedModule)
						->setRelatedTitle ($relatedTitle)
						->setActivityReports ($activityReports)
						->setTimeDuration (floatval ($row ['estimated_time']))
						->setStatus ($row ['eventstatus'])
						->setDueDate ($row ['due_date'])
						->setActivityCondition ($row ['planned_task'])
						->setActivityType ($row ['activitytype'])
						->setImportance ($row ['importance'])
						->setPriority ($row ['priority'])
						->setStartDate ($row ['date_start'])
						->setSubject ($row ['subject']);
					$idProcessed [] = $row ['activityid'];
				}
			}
			unset ($idProcessed);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($activityTask)) ? $activityTask : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $period
		 * @param array $users
		 * @param boolean $withReports
		 *
		 * @return TaskActivity []|null
		 * @throws Exception
		 */
		public static function fetchTaskToMatrix (PearDatabase $adb, $period, $users, $withReports = false) {
			$rangeInit = date('Y-m-d');
			$rangeEnd = date('Y-m-d');
			$whereUsers = '';
			if (count($users)) {
				$whereUsers = "crm.smownerid IN{$adb->sql_expr_datalist ($users)} AND";
			}
			if (count ($period)) {
				$rangeInit = $period ['startdate'];
				$rangeEnd = $period ['enddate'];
			} else {
				$period ['startdate'] = $rangeInit;
				$period ['enddate'] = $rangeEnd;
			}
			// Normalizar fechas a formato MySQL Y-m-d para la consulta
			if (strpos ($rangeInit, '/') !== false) {
				$dateParts = explode ('/', $rangeInit);
				if (count ($dateParts) == 3) {
					$rangeInit = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";
				}
			} else if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $rangeInit)) {
				$dateParts = explode ('-', $rangeInit);
				$rangeInit = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";
			}
			if (strpos ($rangeEnd, '/') !== false) {
				$dateParts = explode ('/', $rangeEnd);
				if (count ($dateParts) == 3) {
					$rangeEnd = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";
				}
			} else if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $rangeEnd)) {
				$dateParts = explode ('-', $rangeEnd);
				$rangeEnd = "{$dateParts[2]}-{$dateParts[1]}-{$dateParts[0]}";
			}
			$result = $adb->query (
				"SELECT crm.crmid, crm.smcreatorid, crm.smownerid, crm.description,
					CONCAT(user.first_name, ' ', user.last_name) AS username, user.imagename, task.*,
					IFNULL((SELECT vtiger_crmentity.setype FROM vtiger_crmentity WHERE vtiger_crmentity.crmid = task.related_id),'Tarea') AS modulename
				FROM vtiger_crmentity crm
				INNER JOIN vtiger_activity task ON task.activityid = crm.crmid
				INNER JOIN vtiger_users user ON user.id = crm.smownerid
				WHERE crm.deleted = 0 AND crm.setype = 'Calendar' AND task.show_in_matrix = 'YES' AND task.activitytype != 'Job' AND
					{$whereUsers} taskToMatrix(task.date_start, IFNULL(NULLIF(task.due_date, ''), task.date_start), task.eventstatus, crm.createdtime, '{$rangeInit}', '{$rangeEnd}') = 1
				ORDER BY task.date_start ASC"
			);
			if ($adb->num_rows ($result) > 0) {
				$arm = ActivityReportManager::getInstance ($adb);
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['modulename'] != 'Tarea') {
						$tabName = $row ['modulename'];
						$moduleName = getTabIdLabelByName ($row ['modulename']);
					} else {
						$tabName = 'Calendar';
						$moduleName = $row ['modulename'];
					}
					$relatedTitle = '';
					if (!empty($row['related_id']) && $row['modulename'] != 'Tarea') {
						$relatedData = self::getActivityRelatedTo($adb, $row['activityid'], $row['modulename']);
						if (!empty($relatedData) && !empty($relatedData['relatedTo'])) {
							$relatedTitle = $relatedData['relatedTo'];
						}
					}
					$dueDate = (!empty($row['due_date'])) ? $row['due_date'] : $row['date_start'];
					$activityTask [] = TaskActivity::getInstance ()
						->setActivityId ($row ['activityid'])
						->setDescription ($row ['description'])
						->setProgress (floatval ($row ['progress']))
						->setRelatedId (intval ($row ['related_id']))
						->setRelatedTitle ($relatedTitle)
						->setModuleName ($moduleName)
						->setRelatedModule ($tabName)
						->setActivityReports (($withReports) ? $arm->fetchActivityReportByActivityId ($row ['activityid'], $period) : null)
						->setTimeDuration (floatval ($row ['estimated_time']))
						->setStatus ($row ['eventstatus'])
						->setDueDate ($dueDate)
						->setGroupId ($row ['categoryid'])
						->setActivityCondition ($row ['planned_task'])
						->setActivityType ($row ['activitytype'])
						->setImportance ($row ['importance'])
						->setPriority ($row ['priority'])
						->setStartDate ($row ['date_start'])
						->setSubject ($row ['subject'])
						->setUserAvatar ($row ['imagename'])
						->setUserName ($row ['username']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($activityTask)) ? $activityTask : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $viewName
		 *
		 * @return View|null
		 */
		public static function fetchView (PearDatabase $adb, $moduleName, $viewName) {
			if ((empty ($moduleName)) || (empty ($viewName))) {
				return null;
			}

			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule ($moduleName, true);
			if (empty ($module)) {
				return null;
			}

			return ViewManager::getInstance ($adb)->fetchView ($moduleName, $viewName);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $viewId
		 *
		 * @return View|null
		 */
		public static function fetchViewById (PearDatabase $adb, $moduleName, $viewId) {
			if ((empty ($moduleName)) || (empty ($viewId))) {
				return null;
			}

			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule ($moduleName, true);
			if (empty ($module)) {
				return null;
			}

			return ViewManager::getInstance ($adb)->fetchViewById ($moduleName, $viewId);
		}

		/**
		 * @param PearDatabase $adb
		 * @param View $view
		 * @param Users $currentUser
		 * @param integer $page
		 * @param string[] $orderBy
		 * @param string $searchCriteria
		 * @param array|null $filters
		 * @param string|null $relatedModule
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchViewData (PearDatabase $adb, View $view, Users $currentUser, $page = null, $orderBy = null, $searchCriteria = null, $filters = null, $relatedModule = null) {
			$queryParts = self::buildViewQueryParts ($adb, $view, $currentUser, $page, $orderBy, $filters);
			
			if (empty ($queryParts)) {
				return null;
			}
			$fromToCross  = $queryParts['from'];
			if ($currentUser->is_admin == 'off') {
				$fromToCross = '`vtiger_crmentity` INNER JOIN `vtiger_activity` ON `vtiger_activity`.`activityid`=`vtiger_crmentity`.`crmid`';
			}
			$activityRelIds = (!empty ($relatedModule)) ? self::getActivityRelByModule ($adb, $relatedModule) : null;
			$moduleName     = $view->getModuleName ();
			if (!empty($searchCriteria)) {
				$searchCriteria = str_replace (self::CUSTOM_DATA, self::REAL_DATA, $searchCriteria);
				$where          = "({$queryParts ['where']}) AND $searchCriteria";
			} else {
				$where = $queryParts ['where'];
			}

			$limitClause = !empty ($queryParts ['limit']) ? "LIMIT {$queryParts ['limit']}" : '';
			$result      = $adb->pquery (
				"SELECT
					{$queryParts ['select']},
					total.__total_records__
				FROM
					{$queryParts ['from']}
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM {$fromToCross} WHERE {$queryParts ['where']}) AS total
				WHERE
					{$where}
				ORDER BY
					{$queryParts ['orderby']}
				{$limitClause}",
				$queryParts ['arguments']
			);
			if ($adb->num_rows ($result) > 0) {
				$totalRecords = null;
				$records      = array ();
				$totalNew     = 0;
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($totalRecords === null) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					$fieldNames = array_keys ($row);
					foreach ($fieldNames as $fieldName) {
						if ($row [ $fieldName ] === null) {
							$row [ $fieldName ] = '';
						}
					}
					unset ($row ['__total_records__']);
					$row ['color'] = self::getColorRule ($view->getColorFilterGroups(), $view->getModuleName (), $row);
					if (($row ['progreso'] <= 1) && ($row ['days'] < 7) && empty($relatedModule)) {
						$row ['isNew'] = true;
						$totalNew++;
					} else {
						$row ['isNew'] = false;
					}
					if ($moduleName == 'Calendar') {
						if (count ($activityRelIds) && (!in_array ($row ['crmid'], $activityRelIds))) {
							continue;
						} else if ($row ['modulename'] != 'Tarea') {
							$row['tab_name']  = $row ['modulename'];
							$row ['modulename'] = getTabIdLabelByName ($row ['modulename']);
							if (($row ['progreso'] <= 1) && ($row ['days'] < 7)) {
								$row ['isNew'] = true;
								$totalNew++;
							} else {
								$row ['isNew'] = false;
							}
						} else {
							$row['tab_name']  = 'Calendar';
						}
						$row ['reports']   = self::getTotalReports ($adb, $row['crmid']);
						$row ['feedbacks'] = self::getTotalFeeddBacks ($adb, $row['crmid']);
						$relatedData       = self::getActivityRelatedTo ($adb, $row['crmid'], $relatedModule);
						if (!empty ($relatedData)) {
							$row['related_to'] = $relatedData['relatedTo'];
							$row['related_id'] = $relatedData['relatedId'];
							$row['tab_name']   = $relatedData['tabName'];
						} else {
							$row['related_to'] = null;
						}
					}
					$row['useravatar'] = (empty($row['useravatar'])) ? '/Image/avatar/png/man.png' : "{$_SESSION ['plat']}/user_images/{$row['useravatar']}";
					$records []       = $row;
				}
				$endRecord  = count ($records);
				$totalPages = ($endRecord < self::RECORDS_PER_PAGE) ? 1 : ceil ($totalRecords / self::RECORDS_PER_PAGE);
			} else {
				$totalRecords = 0;
				$records      = null;
				$endRecord    = 0;
				$totalPages   = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return array (
				'columns'          => $queryParts ['columns'],
				'entityidentifier' => $queryParts ['entityidentifier'],
				'startRecord'      => (is_numeric ($page)) && ($page > 0) ? (($page - 1) * self::RECORDS_PER_PAGE) : 0,
				'endRecord'        => $endRecord,
				'totalRecords'     => $totalRecords,
				'page'             => empty ($page) ? 1 : intval ($page),
				'totalPages'       => $totalPages,
				'records'          => $records,
				'orderby'          => $orderBy,
				'totalNewTask'     => $totalNew,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param View $view
		 * @param Users $currentUser
		 *
		 * @return array
		 */
		public static function fetchViewPermissions (PearDatabase $adb, View $view, Users $currentUser) {
			$is_admin                     = null;
			$current_user_parent_role_seq = null;
			$local_user                   = clone $currentUser;
			require ('user_privileges/user_privileges.php');

			$permissions = array ();
			if (
				($is_admin) ||
				($view->getDefault () == View::DEFAULT_YES) ||
				(in_array ($view->getStatus (), array (View::STATUS_PUBLIC, View::STATUS_APPROVED))) ||
				($view->getOwner () == $currentUser->id) ||
				(self::isPrivateViewOwnedBySubordinateRole ($adb, $view, $current_user_parent_role_seq))
			) {
				$permissions [] = self::PERMISSION_CAN_USE;
			}

			if (($is_admin) || ($view->getOwner () == $currentUser->id)) {
				$permissions [] = self::PERMISSION_CAN_EDIT;
			}

			if (($view->getDefault () != View::DEFAULT_YES) && (($is_admin) || ($view->getOwner () == $currentUser->id))) {
				$permissions [] = self::PERMISSION_CAN_DELETE;
			}

			return $permissions;
		}
		
		/**
		 * @return array
		 */
		public static function getAvailableImportanceOfTasks () {
			return array (
				'HIGH' => Translator::translate ('HIGH'),
				'LOW'  => Translator::translate ('LOW'),
			);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableTaskCategories ($adb, $userId) {
			$result = $adb->pquery ('SELECT DISTINCT categoryid,  name FROM vtiger_activity_categories WHERE smownerid=? ORDER BY name ASC', array ($userId));
			if ($adb->num_rows ($result) > 0) {
				$taskCategoryies = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$taskCategoryies [ $row ['categoryid'] ] = $row ['name'];
				}
			} else {
				DatabaseUtils::closeResult ($result);
				return self::createFirstTaskGroup ($adb, $userId);
			}
			DatabaseUtils::closeResult ($result);
			return isset ($taskCategoryies) ? $taskCategoryies : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $mod_strings
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableEventStatuses ($adb, $mod_strings) {
			$result = $adb->query ('SELECT * FROM vtiger_eventstatus ORDER BY eventstatus');
			if ($adb->num_rows ($result) > 0) {
				$availableEventStatuses = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$eventStatus                             = $row ['eventstatus'];
					$availableEventStatuses [ $eventStatus ] = $mod_strings [ $eventStatus ];
				}
			}
			DatabaseUtils::closeResult ($result);
			return isset ($availableEventStatuses) ? $availableEventStatuses : null;
		}
		
		/**
		 * @param $adb
		 *
		 * @return null|array
		 * @throws Exception
		 */
		public static function getAvailableGroups ($adb) {
			$result = $adb->query ('SELECT * FROM vtiger_groups ORDER BY groupid');
			if ($adb->num_rows ($result) > 0) {
				$availableGroups = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$availableGroups [ $row ['groupid'] ] = $row ['groupname'];
				}
			} else {
				$availableGroups = null;
			}
			DatabaseUtils::closeResult ($result);
			return $availableGroups;
		}

		/**
		 * @param PearDatabase $adb
		 * @param User $user
		 *
		 * @return null|array
		 * @throws Exception
		 */
		public static function getAvailableUser ($adb, $user) {
			if ($user->is_admin == 'on') {
				$whereUser = '';
			} else {
				$userList  = self::getRelatedUserIds ($adb, $user);
				$whereUser = '';
				if (!empty ($userList)) {
					$whereUser = "WHERE id IN{$adb->sql_expr_datalist ($userList)}";
				}
			}
			$result = $adb->query ("SELECT * FROM vtiger_users {$whereUser} ORDER BY id");
			if ($adb->num_rows ($result) > 0) {
				$availableUsers = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$availableUsers [ $row ['id'] ] = trim ("{$row ['first_name']} {$row ['last_name']}");
				}
			} else {
				$availableUsers = null;
			}
			DatabaseUtils::closeResult ($result);
			return $availableUsers;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param User $user
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getAvailableUserAndAvatar ($adb, $user) {
			if ($user->is_admin == 'on') {
				$whereUser = '';
			} else {
				$userList  = self::getRelatedUserIds ($adb, $user);
				$whereUser = '';
				if (!empty ($userList)) {
					$whereUser = "WHERE id IN{$adb->sql_expr_datalist ($userList)}";
				}
			}
			$result = $adb->query ("SELECT id,  CONCAT(first_name, ' ', last_name) AS username, imagename FROM vtiger_users {$whereUser} ORDER BY id");
			if ($adb->num_rows ($result) > 0) {
				$availableUsers = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row['imagename'] = (empty($row['imagename'])) ? '/Image/avatar/png/man.png' : "{$_SESSION ['plat']}/user_images/{$row['imagename']}";
					$availableUsers [ $row ['id'] ] = array (
						'name' => trim ($row ['username']),
						'avatar' => $row['imagename'],
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			return isset ($availableUsers) ? $availableUsers : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer$categiryId
		 * @param string $relatedTo
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getCategoryByRelatedTo ($adb, $categiryId, $relatedTo) {
			if (empty($categiryId) || empty($relatedTo)) {
				return null;
			}
			$result = $adb->pquery(
				'SELECT
					ep.etapas_proyectoid,
					ep.titulo
				  FROM
				  	vtiger_etapas_proyecto ep
				  INNER JOIN vtiger_crmentity crm ON crm.crmid = ep.etapas_proyectoid
				  WHERE
				  	crm.deleted = 0 AND
				  	ep.etapas_proyectoid=?',
				array ($categiryId)
			);
			if ($adb->num_rows ($result) > 0) {
				$taskCategories = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$taskCategories [ $row ['etapas_proyectoid'] ] = $row ['titulo'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($taskCategories)) ? $taskCategories : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getRelatedModule ($adb) {
			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE presence=0 AND isentitytype=1 ORDER BY tablabel', array ());
			if ($adb->num_rows ($result) == 0) {
				$relatedModules = null;
			} else {
				$relatedModules = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$relatedModules [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			return $relatedModules;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param User $user
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getRelatedUserIds ($adb, $user) {
			$local_user = clone $user;
			require('user_privileges/user_privileges.php');
			$sqlGroups = '';
			if (isset ($current_user_groups) && !empty ($current_user_groups)) {
				$groups    = $adb->sql_expr_datalist ($current_user_groups);
				$sqlGroups = "UNION SELECT vtiger_users2group.userid FROM vtiger_users2group WHERE vtiger_users2group.groupid IN {$groups}";
			}
			$result = $adb->run_query_allrecords (
				"SELECT
    				vtiger_user2role.userid
				  FROM
    				vtiger_user2role
				  INNER JOIN vtiger_users ON vtiger_users.id = vtiger_user2role.userid
				  INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid
				  WHERE
    				vtiger_role.parentrole LIKE '{$current_user_parent_role_seq}::%'
    			 {$sqlGroups}"
			);
			return (count ($result)) ? array_column ($result, 'userid') : null;
		}
		
		/**
		 * @param PearDatabase$adb
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getTaskPriorities ($adb) {
			$result = $adb->query ('SELECT * FROM vtiger_taskpriority ORDER BY taskpriority');
			if ($adb->num_rows ($result) > 0) {
				$availableTaskPriorities = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$availableTaskPriorities [] = $row ['taskpriority'];
				}
			}
			DatabaseUtils::closeResult ($result);
			return isset ($availableTaskPriorities) ? $availableTaskPriorities : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string$modulename
		 *
		 * @return integer
		 */
		public static function getTotalNewTasks ($adb, $modulename) {
			$result = $adb->pquery (
				'SELECT 
						 TIMESTAMPDIFF(DAY,crme.createdtime,NOW()) AS days,
						 act.progress 
					  FROM 
					  	vtiger_crmentity crme 
					  INNER JOIN vtiger_activity act ON act.activityid = crme.crmid 
					  WHERE 
					  	crme.deleted=? 
					  	AND crme.setype=?
						AND act.progress <=?
						AND TIMESTAMPDIFF(DAY,crme.createdtime,NOW()) < ?',
				array (0, $modulename, 1, 7)
			);
			DatabaseUtils::closeResult ($result);
			return $adb->getRowCount ($result);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return integer|null
		 */
		public static function hasRelatedActivities ($adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT
						activityid
					  FROM
					  	vtiger_seactivityrel rel
					  INNER JOIN
					  	vtiger_crmentity crm ON crm.crmid = rel.crmid AND crm.deleted=?
					  WHERE
					  	crm.setype=?',
				array (0, $moduleName)
			);
			$totalRecords = $adb->num_rows ($result);
			DatabaseUtils::closeResult ($result);
			return $totalRecords;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return boolean
		 */
		public static function isRelatedToCalendar (PearDatabase $adb, $moduleName) {
			$result    = $adb->pquery (
				'SELECT
					rl.*
				FROM
					vtiger_relatedlists rl
					INNER JOIN vtiger_tab rm ON rm.tabid=rl.tabid AND rm.name=?
					INNER JOIN vtiger_tab rrm ON rrm.tabid=rl.related_tabid AND rrm.name=?
				WHERE
					rl.presence=0
				LIMIT 1',
				array ($moduleName, 'Calendar')
			);
			$isRelated = ($adb->num_rows ($result) > 0);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $isRelated;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param User $current_user
		 * @param array $data
		 *
		 * @return string
		 * @throws WebServiceException
		 */
		public static function updateCalendarDate ($adb, $current_user, $data) {
			$objStartDate    = new DateTime ($data['startDate']);
			$objNewStartDate = new DateTime ($data['newStartDate']);
			$difference      = $objStartDate->diff ($objNewStartDate);
			$inverted        = $difference->invert ? -1 : 1;
			$daysDifference  = $difference->days * $inverted;
			if (!empty ($data['dueDate'])) {
				$objDueDate    = new DateTime ($data['dueDate']);
				$objNewDueDate = clone $objDueDate;
				$objNewDueDate->modify ($daysDifference >= 0 ? '+' : ''. $daysDifference . ' days');
				$newDueDate      = $objNewDueDate->format ('Y-m-d');
			} else {
				$newDueDate = null;
			}
			
			if ($data['calendarType'] == 'task') {
				if (empty ($data['activityId'])) {
					throw new Exception ('Actividad no encontrada!');
				} elseif (empty ($data['newStartDate'])) {
					throw new Exception ('Fecha de inicio no encontrada');
				}
				$message = '';
				$activity = new Activity();
				$activity->retrieve_entity_info ($data['activityId'], 'Calendar');
				$activity->column_fields ['due_date']   = $newDueDate;
				$activity->column_fields ['date_start'] = $data['newStartDate'];
				$activity->mode = 'edit';
				$activity->id = $data['activityId'];
				$activity->save ('Calendar');
				$message .=  "\n - Actividad actualizada exitosamente" ;
			} else {
				$message = '';
				if (!empty ($data['crmId']) && !empty($data['flModule'])) {
					$entity       = CRMEntity::getInstance ($data['flModule']);
					$entity->mode = 'edit';
					$entity->retrieve_entity_info (intVal ($data['crmId']), $data['flModule']);
					$entity->id = intVal ($data['crmId']);
					$fields     = array_keys ($entity->column_fields);
					
					foreach ($fields as $field) {
						if (in_array ($field, array ('createdtime', 'modifiedtime'))) {
							continue;
						}
						if ($entity->column_fields[$field] == $data['startDate']) {
							$entity->column_fields[$field] = $data ['newStartDate'];
							$message .= (!empty($message)) ?  "\n Fecha de inicio actualizado" : '- Fecha de inicio actualizado ';
							continue;
						}
						if (!empty ($data['dueDate']) &&  $entity->column_fields[$field] == $data['dueDate']) {
							$entity->column_fields[$field] = $newDueDate;
							$message .= (!empty($message)) ?  "\n Fecha de fin actualizada" : '- Fecha de fin actualizada ';
						}
					}
					if (!empty ($message)) {
						$entity->save($data ['flModule']);
						$activities = self::fetchRelatedActivities ($adb, $entity->id, 'Held');
						if (!empty ($activities)) {
							$data['calendarType'] = 'task';
							foreach ($activities as $activity) {
								$data ['activityId'] = $activity ['activityid'];
								$data ['startDate']  = $activity ['date_start'];
								$data ['dueDate']    = $activity ['due_date'];
								$activityStartDate   = new DateTime ($activity['date_start']);
								$activityStartDate->modify ($daysDifference >= 0 ? '+' : ''. $daysDifference . ' days');
								$data['newStartDate'] = $activityStartDate->format ('Y-m-d');
								$message             .= self::updateCalendarDate ($adb, $current_user, $data);
							}
						} else {
							$message .= (!empty($message)) ?  "\n Registro sin tareas" : '- Registro sin tareas';
						}
					} else {
						throw new Exception ('Fechas no encontradas');
					}
					
				} else {
					throw new Exception ('Imposible actualizar fechas!');
				}
			}
			return $message;
		}

	}
