<?php
	require_once ('include/platzilla/Managers/PicklistRelationshipManager.php');
	require_once ('include/platzilla/Objects/Role.php');

	/**
	 * Class SaveFieldPropertiesHelper
	 *
	 * Clase obstracta donde se manejan los metodos para el rol y perfil de los campos de un modulo
	 */
	abstract class SaveFieldPropertiesHelper {

		/**
		 * Obtiene el ID del campo para la entidad
		 *
		 * @param \PearDatabase $adb
		 * @param $tableName
		 *
		 * @return null
		 */
		private static function getEntityIdField (PearDatabase $adb, $tableName) {
			$result = $adb->pquery ('SELECT entityidfield FROM vtiger_entityname WHERE tablename=?', array ($tableName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['entityidfield'];
		}

		/**
		 * Para actualizar la data del filtro de la tareas automatizadas
		 *
		 * @param $adb
		 * @param $parameter
		 */
		private static function updateBgTasksDataFilters ($adb, $parameter) {
			$adb->pquery(
				'UPDATE 
						vtiger_bgtasks_data_filters
					  SET 
						`value`=?  
					  WHERE 
					  	fieldname=? AND
					  	`value`=?',
				$parameter
			);
			$adb->pquery(
				'UPDATE 
						vtiger_bgtasks_data_parameters
					  SET 
						parameterformula=?  
					  WHERE 
					  	parametername=? AND
					  	expandedkey=? AND
					  	parameterformula=?',
				array ($parameter[0], 'fieldnames', $parameter[1], $parameter[2])
			);
		}

		/**
		 * Para actualizar los filtros de color
		 *
		 * @param $adb
		 * @param $parameter
		 */
		private static function updateCvAdvColor ($adb, $parameter) {
			$adb->pquery(
				'UPDATE 
					vtiger_cvadvcolor
				 SET 
					`value`=?  
				 WHERE 
					SUBSTRING_INDEX(SUBSTRING_INDEX(`columnname`,":",3),":", -1)=? AND
					`value`=?',
				$parameter
			);
		}

		/**
		 * Para actualizar los filtros de los filtros de color
		 *
		 * @param $adb
		 * @param $parameter
		 */
		private static function updateCvAdvfilter ($adb, $parameter) {
			$adb->pquery(
				'UPDATE 
						vtiger_cvadvfilter
					  SET 
						`value`=?  
					  WHERE 
					  	SUBSTRING_INDEX(SUBSTRING_INDEX(`columnname`,":",3),":", -1)=? AND
					  	`value`=?',
				$parameter
			);
		}

		/**
		 * Para actualizar los filtros de las vistas kanban en los modulos
		 *
		 * @param $adb
		 * @param $parameter
		 */
		private static function updateKanbaAdvfilter ($adb, $parameter) {
			$adb->pquery(
				'UPDATE 
						vtiger_kvadvfilter
					  SET 
						`value`=?  
					  WHERE 
					  	SUBSTRING_INDEX(SUBSTRING_INDEX(`columnname`,":",3),":", -1)=? AND
					  	`value`=?',
				$parameter
			);
		}

		/**
		 * Para actualizar los filtros de las vistas de los modulos
		 *
		 * @param $arrayFilter
		 * @param $parameter
		 *
		 * @return string
		 */
		private static function updateFilters ($arrayFilter, $parameter) {
			list ($changedValues, $fieldName ,$currentValues) = $parameter;

			$oneChanged   = false;
			$filterFields = $arrayFilter ['filterField'];
			$filterValues = $arrayFilter ['filterValue'];
			$totalFields  = count ($filterFields);

			for ($k = 0; $k < $totalFields; $k++) {
				$dummy       = explode ('.', $filterFields[ $k ]);
				$filterField = (count ($dummy) > 1) ? $dummy [1] : $dummy [0];
				if ($filterField != $fieldName) {
					continue;
				} else if ($filterValues[ $k ] == $currentValues) {
					$filterValues[ $k ] = $changedValues;
					$oneChanged         = true;
				}
			}
			if ($oneChanged) {
				$arrayFilter ['filterValue'] = $filterValues;
			}
			return json_encode ($arrayFilter);
		}

		/**
		 * Para actualizar los graficos
		 *
		 * @param $adb
		 * @param $parameter
		 */
		private static function updateGraphics ($adb, $parameter) {
			list ($changedValues, $fieldName,$currentValues) = $parameter;
			$searchValue = "%{$currentValues}%";
			$fieldName   = "%{$fieldName}%";
			$result = $adb->pquery ('SELECT graficoid, sqlprimarioreporte, varreporte FROM vtiger_graficos WHERE sqlprimarioreporte  LIKE ? AND sqlprimarioreporte  LIKE ?', array ($fieldName, $searchValue));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			while ($row = $adb->fetchByAssoc($result, -1, false)) {
				$sqlChart = json_decode(str_replace('&quot;', '"', $row ['sqlprimarioreporte']));
				$sqlChart = str_replace($currentValues, $changedValues, $sqlChart);
				$sqlChart = json_encode($sqlChart, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT));
				$filters = json_decode($row ['varreporte'], true);
				$filtersChar = self::updateFilters($filters, $parameter);
				$adb->pquery('UPDATE vtiger_graficos SET sqlprimarioreporte=?, varreporte=? WHERE graficoid=?', array($sqlChart, $filtersChar, $row ['graficoid']));
			}
			DatabaseUtils::closeResult($result);
		}

		/**
		 * Para actualizar las notificaciones
		 *
		 * @param $adb
		 * @param $parameter
		 */
		private static function updateNotifications ($adb, $parameter) {
			list ($changedValues, $fieldName,$currentValues) = $parameter;
			$searchValue = "%{$currentValues}%";
			$fieldName   = "%{$fieldName}%";
			$result = $adb->pquery ('SELECT notificationid, sqlfilter, advancedfilter FROM vtiger_notifications_filters WHERE sqlfilter LIKE ? AND sqlfilter LIKE ?', array ($fieldName, $searchValue));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			while ($row = $adb->fetchByAssoc($result, -1, false)) {
				$sqlNotification     = json_decode (str_replace('&quot;', '"', $row ['sqlfilter']));
				$sqlNotification     = str_replace ($currentValues, $changedValues, $sqlNotification);
				$sqlNotification     = json_encode ($sqlNotification, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT));
				$filters             = json_decode ($row ['advancedfilter'], true);
				$filtersNotification = self::updateFilters($filters, $parameter);
				$adb->pquery('UPDATE vtiger_notifications_filters SET sqlfilter=?, advancedfilter=? WHERE notificationid=?', array($sqlNotification, $filtersNotification, $row ['notificationid']));
			}
			DatabaseUtils::closeResult($result);
		}

		/**
		 * Comparar si 2 roles son iguales
		 *
		 * @param Role[] $oldRoles
		 * @param Role[] $newRoles
		 *
		 * @return boolean
		 */
		public static function areRolesEqual ($oldRoles, $newRoles) {
			if ((empty ($oldRoles)) && (empty ($newRoles))) {
				return true;
			} else if (
				(empty ($oldRoles) !== empty ($newRoles)) ||
				(!is_array ($newRoles)) ||
				(count ($oldRoles) != count ($newRoles))
			) {
				return false;
			} else {
				foreach ($oldRoles as $oldRole) {
					$equals = false;
					foreach ($newRoles as $newRole) {
						if ($oldRole->getId () == $newRole->getId ()) {
							$equals = true;
							break;
						}
					}
					if (!$equals) {
						return false;
					}
				}
				return true;
			}
		}

		/**
		 * Establece la visibilidad de los campos por perfiles
		 *
		 * @param PearDatabase $adb
		 * @param array $pickListRelationships
		 * @param array $parameters
		 *
		 * @throws Exception
		 */
		public static function savePicklistRelationship ($adb, $pickListRelationships, $parameters) {
			$relationshipName = $parameters ['relationshipsName'];
			if (empty ($relationshipName)) {
				$relationshipName = $parameters ['motherPicklist'] . '@' . $parameters ['daughterPicklist'] . '-' . rand (1, 9999);
			}

			$masterPicklist = array ();
			foreach ($pickListRelationships as $motherId => $daughterIds) {
				$masterPicklist [] = PicklistRelationshipMaster::getInstance ()
					->setRelationshipName ($relationshipName)
					->setMotherPicklistValueId ($motherId)
					->setDaughterPicklistValuesId ($daughterIds)
					->setId ($parameters ['locked']);
			}

			$picklistToPicklist = PicklistRelationship::getInstance ()
				->setDaughterPicklistName ($parameters ['daughterPicklist'])
				->setModuleName ($parameters ['moduleName'])
				->setMotherPicklistName ($parameters ['motherPicklist'])
				->setRelationshipName ($relationshipName)
				->setPicklistRelationshipMaster ($masterPicklist)
				->setLocked ($parameters ['locked']);

			PicklistRelationshipManager::getInstance ($adb)->savePicklistRelationship ($picklistToPicklist);
		}

		/**
		 * Establece la relación Picklist → Pipeline
		 *
		 * @param PearDatabase $adb
		 * @param array $pickListPipelineRelationships
		 * @param array $parameters
		 *
		 * @throws Exception
		 */
		public static function savePicklistPipelineRelationship ($adb, $pickListPipelineRelationships, $parameters) {
			require_once ('include/platzilla/Managers/PicklistPipelineRelationshipManager.php');

			$moduleName = $parameters ['moduleName'];
			$motherPicklistName = $parameters ['motherPicklist'];
			$pipelineFieldName = $parameters ['daughterPipeline'];
			$relationshipName = $parameters ['relationshipsName'];
			$locked = !empty ($parameters ['locked']);

			// Obtener el fieldid del picklist madre
			$motherPicklistFieldId = self::getFieldIdByName ($adb, $moduleName, $motherPicklistName);
			if (empty ($motherPicklistFieldId)) {
				throw new Exception ('No se encontró el fieldid del picklist madre');
			}

			// Obtener el fieldid del pipeline
			$pipelineFieldId = self::getFieldIdByName ($adb, $moduleName, $pipelineFieldName);
			if (empty ($pipelineFieldId)) {
				throw new Exception ('No se encontró el fieldid del pipeline');
			}

			// Usar el nuevo manager para guardar la relación
			PicklistPipelineRelationshipManager::getInstance ($adb)->savePicklistPipelineRelationship (
				$moduleName,
				$motherPicklistName,
				$motherPicklistFieldId,
				$pipelineFieldName,
				$pipelineFieldId,
				$pickListPipelineRelationships,
				$relationshipName,
				$locked
			);
		}

		/**
		 * Obtiene el fieldid de un campo por su nombre
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return integer|null
		 */
		private static function getFieldIdByName ($adb, $moduleName, $fieldName) {
			$result = $adb->pquery (
				'SELECT fieldid FROM vtiger_field 
				 INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid
				 WHERE vtiger_tab.name = ? AND vtiger_field.fieldname = ?',
				array ($moduleName, $fieldName)
			);
			if ($adb->num_rows ($result) > 0) {
				$fieldId = $adb->query_result ($result, 0, 'fieldid');
				return $fieldId;
			}
			return null;
		}

		/**
		 * Obtiene el tabid de un módulo por su nombre
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return integer|null
		 */
		private static function getTabIdByName ($adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT tabid FROM vtiger_tab WHERE name = ?',
				array ($moduleName)
			);
			if ($adb->num_rows ($result) > 0) {
				$tabId = $adb->query_result ($result, 0, 'tabid');
				return $tabId;
			}
			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $fieldId
		 * @param string $moduleName
		 *
		 * @return null|string
		 */
		public static function setVisibilityAllProfiles ($adb, $fieldId, $moduleName) {
			if (empty($fieldId) || !is_numeric ($fieldId)) {
				return null;
			}
			$adb->pquery(
				'UPDATE
						vtiger_profile2field p2f
					  INNER JOIN
					  	vtiger_tab t ON t.tabId = p2f.tabid
					  SET
					  	visible=?
					  WHERE
					  	p2f.fieldid=? AND
					  	t.name=',
				array (1, $fieldId, $moduleName)
			);
			return 'ok';
		}
		
		/**
		 * Establece la visibilidad de los campos por perfiles de las apps
		 * @param PearDatabase $adb
		 * @param string $visibleProfiles
		 * @param string $hiddenProfiles
		 *
		 * @throws Exception
		 */
		public static function setVisibilityByProfiles ($adb, $visibleProfiles, $hiddenProfiles) {
			if (!empty ($visibleProfiles)) {
				$visibles = explode (',', $visibleProfiles);
				foreach ($visibles as $profiles) {
					list ($profileId, $fieldId, $visible) = explode ('@', $profiles);
					$adb->pquery('UPDATE vtiger_profile2field SET visible=? WHERE profileid=? AND fieldid=?', array ($visible, $profileId, $fieldId));
				}
			}
			if (!empty ($hiddenProfiles)) {
				$hiddens = explode (',', $hiddenProfiles);
				foreach ($hiddens as $profiles) {
					list ($profileId, $fieldId, $visible) = explode ('@', $profiles);
					$adb->pquery('UPDATE vtiger_profile2field SET visible=? WHERE profileid=? AND fieldid=?', array ($visible, $profileId, $fieldId));
				}
			}
		}

		/**
		 * Actualiza los valores de los picklist presentes en los modulos
		 *
		 * @param PearDatabase $adb
		 * @param array $changedValues
		 * @param array $currentValues
		 * @param string $fieldName
		 * @param string $tableName
		 */
		public static function updatePickListValues ($adb, $changedValues, $currentValues, $fieldName, $tableName) {
			if (empty($tableName)) {
				return;
			}
			$entityFieldId = self::getEntityIdField($adb, $tableName);
			if (empty ($entityFieldId)) {
				return;
			}
			$totalUpdates = (count ($changedValues) < count ($currentValues)) ? count ($changedValues) : count ($currentValues);

			for ($k = 0; $k < $totalUpdates; $k++) {
				$adb->pquery(
					"UPDATE 
						{$tableName} tn
					INNER JOIN vtiger_crmentity crm ON crm.crmid = tn.{$entityFieldId}
					SET 
						{$fieldName}=?  
					WHERE tn.{$fieldName}=? AND crm.deleted=?",
					array ($changedValues[ $k ], $currentValues[ $k ], 0)
				);
				self::updateBgTasksDataFilters ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateCvAdvColor ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateCvAdvfilter ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateGraphics ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateKanbaAdvfilter ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateNotifications ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
			}
		}

		/**
		 * Actualiza los valores de los pipeline presentes en los modulos
		 *
		 * @param PearDatabase $adb
		 * @param array $changedValues
		 * @param array $currentValues
		 * @param string $fieldName
		 * @param string $tableName
		 * @param string $moduleName
		 * @param integer $userId
		 */
		public static function updatePipelineValues ($adb, $changedValues, $currentValues, $fieldName, $tableName, $moduleName, $userId) {
			if (empty($tableName)) {
				return;
			}
			$entityFieldId = self::getEntityIdField($adb, $tableName);
			if (empty ($entityFieldId)) {
				return;
			}
			
			// Obtener fieldid y tabid para el histórico
			$fieldId = self::getFieldIdByName($adb, $moduleName, $fieldName);
			$tabId = self::getTabIdByName($adb, $moduleName);
			
			require_once ('include/platzilla/Data/EntityHistoryManager.php');
			require_once ('include/platzilla/Data/EntityHistory.php');
			$historyManager = EntityHistoryManager::getInstance ($adb);
			
			$totalUpdates = (count ($changedValues) < count ($currentValues)) ? count ($changedValues) : count ($currentValues);

			for ($k = 0; $k < $totalUpdates; $k++) {
				// Actualizar los registros del módulo
				$result = $adb->pquery(
					"SELECT tn.{$entityFieldId} AS recordid
					 FROM {$tableName} tn
					 INNER JOIN vtiger_crmentity crm ON crm.crmid = tn.{$entityFieldId}
					 WHERE tn.{$fieldName}=? AND crm.deleted=?",
					array ($currentValues[ $k ], 0)
				);
				
				// Generar histórico para cada registro actualizado
				while ($row = $adb->fetchByAssoc($result, -1, false)) {
					$recordId = $row['recordid'];
					$history = EntityHistory::getInstance ()
						->setModuleId ($tabId)
						->setFieldId ($fieldId)
						->setOldValue ($currentValues[ $k ])
						->setNewValue ($changedValues[ $k ])
						->setModifiedBy ($userId)
						->setModifiedOn (1)
						->setRegistryId ($recordId)
						->setCreatedDate (date ('Y-m-d H:i:s'));
					$historyManager->saveEntityHistory ($history);
				}
				DatabaseUtils::closeResult($result);
				
				// Actualizar los valores
				$adb->pquery(
					"UPDATE 
							{$tableName} tn
						INNER JOIN vtiger_crmentity crm ON crm.crmid = tn.{$entityFieldId}
						SET 
							{$fieldName}=?  
						WHERE tn.{$fieldName}=? AND crm.deleted=?",
					array ($changedValues[ $k ], $currentValues[ $k ], 0)
				);
				self::updateBgTasksDataFilters ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateCvAdvColor ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateCvAdvfilter ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateGraphics ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateKanbaAdvfilter ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
				self::updateNotifications ($adb, array ($changedValues[ $k ], $fieldName, $currentValues[ $k ]));
			}
		}
	}
