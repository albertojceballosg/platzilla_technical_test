<?php
	require_once ('include/platzilla/Managers/AppFieldManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/RoleManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/utils/EntityUtils.class.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('include/utils/UserUtils.class.php');

	abstract class EditViewUtils {

		/**
		 * Construye un arreglo basado en un arreglo de usuarios, cambiando la estructura, más apto para ser convertido a HTML
		 *
		 * @param array $array
		 * @param string $selectedValue
		 *
		 * @return array
		 */
		private static function buildOptions ($array, $selectedValue) {
			if ((empty ($array)) || (!is_array ($array))) {
				return $array;
			}

			$options = array ();
			foreach ($array as $key => $value) {
				$options [ $key ] = array ($value => $key == $selectedValue ? 'selected' : '');
			}
			return $options;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 *
		 * @return string|null
		 */
		private static function fetchEntityType (PearDatabase $adb, $entityId) {
			$data = EntityUtils::fetchCrmEntityHeaders ($adb, $entityId);
			return isset ($data ['setype']) ? $data ['setype'] : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		private static function fetchRelatedRecords (PearDatabase $adb, $moduleName) {
			if ((empty ($moduleName)) || ($moduleName == '-')) {
				return null;
			}

			/** @var CRMEntity|stdClass $entity */
			$entity = CRMEntity::getInstance ($moduleName);
			if (empty ($entity)) {
				return null;
			}

			$result = $adb->query ($entity->getListQuery ($moduleName), false);
			if ($adb->num_rows ($result) > 0) {
				$relatedRecords = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$entityIdFieldName            = $entity->table_index;
					$recordId                     = $row [ $entityIdFieldName ];
					$recordValue                  = $row [ $entity->def_basicsearch_col ];
					$relatedRecords [ $recordId ] = $recordValue;
				}
			} else {
				$relatedRecords = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $relatedRecords;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 *
		 * @return array
		 */
		private static function getActivityDurationFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel) {
			$fieldValue = $fields [ $fieldName ];
			if ($fieldValue == '') {
				$fieldValue = 1;
			}
			$options = array ();
			$result  = $adb->query ('SELECT * FROM vtiger_duration_minutes ORDER BY sortorderid');
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['duration_minutes'] == $fields ['duration_minutes']) {
						$selected = 'selected';
					} else {
						$selected = '';
					}
					$options [ $row ['duration_minutes'] ] = $selected;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => array (
					$fieldValue,
					$options,
				),
			);
		}

		/**
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 *
		 * @return array
		 */
		private static function getAttachmentDownloadTypeFieldOptions ($moduleName, $fields, $fieldName, $fieldLabel) {
			$fieldValue = $fields [ $fieldName ];
			if ($fieldValue == 'E') {
				$externalSelected = 'selected';
				$internalSelected = null;
				$filename         = $fields ['filename'];
			} else {
				$externalSelected = null;
				$internalSelected = 'selected';
				$filename         = $fields ['filename'];
			}
			return array (
				'label'  => array (
					array (Translator::translateFromApplicationDictionary ('Internal'), Translator::translateFromApplicationDictionary ('External')),
					array ($internalSelected, $externalSelected),
					array ('I', 'E'),
					Translator::translate ($fieldLabel, $moduleName),
				),
				'values' => array (
					$fieldValue,
					$filename,
				),
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 *
		 * @return array
		 */
		private static function getAttachmentNameTypeFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel) {
			$fieldValue = $fields [ $fieldName ];
			if (!empty ($fields ['record_id'])) {
				$result = $adb->pquery ('SELECT * FROM vtiger_seattachmentsrel WHERE crmid=?', array ($fields ['record_id']));
				if ($adb->num_rows ($result) > 0) {
					$row          = $adb->fetchByAssoc ($result, -1, false);
					$attachmentId = $row ['attachmentsid'];
				} else {
					$attachmentId = null;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;

				if ((empty ($fields [ $fieldName ])) && (!empty ($attachmentId))) {
					$result = $adb->pquery ('SELECT * FROM vtiger_attachments WHERE attachmentsid=?', array ($attachmentId));
					if ($adb->num_rows ($result) > 0) {
						$row            = $adb->fetchByAssoc ($result, -1, false);
						$attachmentName = $row ['name'];
						$fieldValue     = $row ['name'];
					} else {
						$attachmentName = null;
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
				} else {
					$attachmentName = null;
				}
			} else {
				$attachmentName = null;
			}

			if ((!empty ($attachmentName)) && ($moduleName != 'Documents')) {
				$filename = " [ {$attachmentName} ]";
			} else if ((!empty ($attachmentName)) && ($moduleName == 'Documents')) {
				$filename = $attachmentName;
			} else {
				$filename = null;
			}

			$options = array ();
			if (!empty ($filename)) {
				$options [] = $filename;
			}
			if (!empty ($fieldValue)) {
				$options [] = $fieldValue;
			}
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => $options,
			);
		}

		/**
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 * @param string|integer $uiType
		 * @param Users|stdClass $currentUser
		 *
		 * @return array
		 */
		private static function getDateTimeFieldOptions ($moduleName, $fields, $fieldName, $fieldLabel, $uiType, $currentUser) {
		
			$displayValue = null;
			$currentTime  = null;
			$fieldValue   = $fields [ $fieldName ];
			if ($fieldValue == '') {
				if ((in_array ($moduleName, array ('Events', 'Calendar'))) && ($uiType == 6)) {
					$currentTime = date ('H:i', strtotime ('+5 minutes'));
				} else if ((in_array ($moduleName, array ('Events', 'Calendar'))) && ($uiType == 23)) {
					$currentTime = date ('H:i', strtotime ('+10 minutes'));
				}
			} else {
				if ($uiType == 6) {
					if ((in_array ($moduleName, array ('Events', 'Calendar'))) && ($fields ['time_start'] != '')) {
						$currentTime = $fields ['time_start'];
						$fieldValue  = "{$fieldValue} {$currentTime}";
					} else {
						$currentTime = date ('H:i', strtotime ('+5 minutes'));
					}
				} else if ((in_array ($moduleName, array ('Events', 'Calendar'))) && ($uiType == 23)) {
					if ($fields ['time_end'] != '') {
						$currentTime = $fields ['time_end'];
						$fieldValue  = "{$fieldValue} {$currentTime}";
					} else {
						$currentTime = date ('H:i', strtotime ('+10 minutes'));
					}
				}
				$displayValue = DateTimeField::getValidDisplayDate ($fieldValue, $currentUser);
			}
			$dateFormat = DateTimeField::getUserDateFormat ($currentUser);
			if ((!empty ($currentTime)) && (in_array ($moduleName, array ('Events', 'Calendar'))) && (in_array ($uiType, array (6, 23)))) {
				$currentTime = DateTimeField::convertToUserTimeZone ($currentTime)->format ('H:i');
			}

			$options = array (
				array ($displayValue => $currentTime),
			);
			if (in_array ($uiType, array (5, 23))) {
				$options [] = array ($dateFormat => $currentUser->date_format);
			} else {
				$period     = Translator::translateFromApplicationDictionary ('YEAR_MONTH_DATE');
				$options [] = array ($dateFormat => "{$currentUser->date_format} {$period}");
			}
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => $options,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldLabel
		 *
		 * @return array|null
		 */
		private static function getFolderNameFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldLabel) {
			$folderId = $fields ['folderid'];
			$result   = $adb->pquery (
				'SELECT
					folderid,
					foldername,
					CASE WHEN folderid=? THEN 0 ELSE 1 END AS sortorder
				FROM
					vtiger_attachmentsfolder
				ORDER BY
					sortorder,
					foldername',
				array ($folderId)
			);
			if ($adb->num_rows ($result) > 0) {
				$folderNames = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$folderNames [ $row ['folderid'] ] = $row ['foldername'];
				}
			} else {
				$folderNames = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => $folderNames,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 *
		 * @return array
		 */
		private static function getGlobalPicklistFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel) {
			require_once ('include/platzilla/Managers/GlobalPicklistManager.php');
			$picklist   = GlobalPicklistManager::getInstance ($adb)->fetchPicklistByName ($fieldName);
			$fieldValue = $fields [ $fieldName ];
			$options    = array ();
			if (empty ($picklist)) {
				$options    = null;
				$isMultiple = null;
			} else if (!$picklist->isMultiple ()) {
				$picklistValues = $picklist->getValues ();
				if (!empty ($picklistValues)) {
					foreach ($picklistValues as $picklistValue) {
						$options [] = array (
							$picklistValue->getValue (),
							$picklistValue->getValue (),
							$picklistValue->getValue () == $fieldValue ? 'selected' : '',
						);
					}
				}
				$isMultiple = false;
			} else {
				$dummy          = explode (' |##| ', $fieldValue);
				$picklistValues = $picklist->getValues ();
				if (!empty ($picklistValues)) {
					foreach ($picklistValues as $picklistValue) {
						$options [] = array (
							$picklistValue->getValue (),
							$picklistValue->getValue (),
							in_array ($picklistValue->getValue (), $dummy) ? 'selected' : '',
						);
					}
				}
				$isMultiple = true;
			}
			return array (
				'ismultiple' => $isMultiple,
				'label'      => Translator::translate ($fieldLabel, $moduleName),
				'values'     => $options,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 *
		 * @return array
		 */
		private static function getImageFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel) {
			if (!empty ($fields ['record_id'])) {
				$fieldValue = $fields [ $fieldName ];
				$result     = $adb->pquery (
					'SELECT
						vtiger_attachments.path,
						vtiger_attachments.name
					FROM
						vtiger_seattachmentsrel
						INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
					WHERE
						vtiger_attachments.attachmentsid=?',
					array ($fieldValue)
				);
				if ($adb->num_rows ($result) > 0) {
					$images = array ();
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$images [] = array (
							'name' => $row ['name'],
							'path' => $row ['path'],
							'id'   => $fieldValue,
						);
					}
				} else {
					$images = array ('name' => '', 'path' => '', 'id' => '');
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$images = array ('name' => '', 'path' => '', 'id' => '');
			}
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => $images,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 * @param integer $uiType
		 * @param Users $currentUser
		 *
		 * @return array
		 */
		private static function getModifiedByFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel, $uiType, $currentUser) {
			global $is_admin, $profileGlobalPermission, $defaultOrgSharingPermission;

			$fieldValue = $fields [ $fieldName ];
			if (!empty ($fieldValue)) {
				$assignedUserId = $fieldValue;
			} else {
				$assignedUserId = $currentUser->id;
			}
			$module   = ModuleManager::getInstance ($adb)->fetchModule ($moduleName, true);
			$moduleId = isset ($module) ? $module->getId () : 0;
			if ($uiType == 407) {
				$users       = UserUtils::getUsers ($adb, $moduleName, $currentUser, 'Active', $currentUser->id);
				$fieldValues = array_map ('trim', explode ('|##|', $fieldValue));
				$total       = 0;
				if (!empty ($users)) {
					$options = array ();
					foreach ($users as $userId => $userFullName) {
						if (in_array ($userId, $fieldValues)) {
							$selected = 'selected';
							$total++;
						} else {
							$selected = '';
						}
						$options [] = array (getTranslatedString ($userFullName), $userId, $selected);
					}

					if (($total == 0) && (!empty ($fieldValue))) {
						$options [] = array (Translator::translateFromApplicationDictionary ('LBL_NOT_ACCESSIBLE'), $fieldValue, 'selected');
					}
				} else {
					$options = null;
				}
			} else if (($is_admin == false) && ($profileGlobalPermission[2] == 1) && (in_array ($defaultOrgSharingPermission [ $moduleId ], array (0, 3)))) {
				$users   = UserUtils::getUsers ($adb, $moduleName, $currentUser, 'Active', $currentUser->id, true);
				$options = self::buildOptions ($users, $assignedUserId);
			} else {
				$users   = UserUtils::getUsers ($adb, $moduleName, $currentUser, 'Active', $currentUser->id);
				$options = self::buildOptions ($users, $assignedUserId);
			}
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => $options,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 *
		 * @return array
		 */
		private static function getModuleRecordsFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel) {
			$result = $adb->pquery (
				'SELECT
					fmr.relmodule
				FROM
					vtiger_fieldmodulerel fmr
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=? AND f.presence IN (0, 2)
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($fieldName, $moduleName)
			);
			if ($adb->num_rows ($result) > 0) {
				$row        = $adb->fetchByAssoc ($result, -1, false);
				$entityType = $row ['relmodule'];
			} else {
				$entityType = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (!empty ($entityType)) {
				$relatedRecords = self::fetchRelatedRecords ($adb, $moduleName);
				if (!empty ($relatedRecords)) {
					$fieldValues = array_map ('trim', explode ('|##|', $fields [ $fieldName ]));
					$total       = 0;
					$options     = array ();
					foreach ($relatedRecords as $recordId => $recordValue) {
						if (in_array ($recordId, $fieldValues)) {
							$selected = 'selected';
							$total++;
						} else {
							$selected = '';
						}
						$options [] = array (Translator::translate ($recordValue), $recordId, $selected);
					}

					if (($total == 0) && (!empty ($fields [ $fieldName ]))) {
						$options [] = array (Translator::translateFromApplicationDictionary ('LBL_NOT_ACCESSIBLE'), $fields [ $fieldName ], 'selected');
					}
				} else {
					$options = null;
				}
			} else {
				$options = null;
			}
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => $options,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 *
		 * @return array
		 */
		private static function getModuleReferenceFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel) {
			$result      = $adb->pquery (
				'SELECT
					fmr.relmodule
				FROM
					vtiger_fieldmodulerel fmr
					INNER JOIN vtiger_field f ON f.fieldid=fmr.fieldid AND f.fieldname=? AND f.presence IN (0, 2)
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($fieldName, $moduleName)
			);
			$entityTypes = array ();
			if ($adb->num_rows ($result) > 0) {
				$row            = $adb->fetchByAssoc ($result, -1, false);
				$entityTypes [] = $row ['relmodule'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (!empty ($fields [ $fieldName ])) {
				$entityType   = $fieldName != 'user_id' ? self::fetchEntityType ($adb, $fields [ $fieldName ]) : 'Users';
				$displayValue = EntityUtils::fetchReferencedCrmEntitiesData ($adb, $entityType, $fields [ $fieldName ]);
			} else {
				$entityType   = '';
				$displayValue = '';
			}
			return array (
				'label'  => array ('options' => $entityTypes, 'selected' => $entityType, 'displaylabel' => Translator::translate ($fieldLabel, $moduleName)),
				'values' => array ('displayvalue' => $displayValue, 'entityid' => $fields [ $fieldName ]),
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 * @param Users $currentUser
		 *
		 * @return array
		 */
		private static function getOwnerFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel, $currentUser, $mode) {
			global $is_admin, $profileGlobalPermission, $defaultOrgSharingPermission;

			$module     = ModuleManager::getInstance ($adb)->fetchModule ($moduleName, true);
			$moduleId   = isset ($module) ? $module->getId () : 0;
			$fieldValue = $fields [ $fieldName ];
			if (!empty ($fieldValue)) {
				$assignedUserId = $fieldValue;
			} elseif ($mode == 'mass_edit') {
				$assignedUserId = '';
			} else {
				$assignedUserId = $currentUser->id;
			}

			if (($is_admin == false) && ($profileGlobalPermission[2] == 1) && (in_array ($defaultOrgSharingPermission [ $moduleId ], array (0, 3)))) {
				$users = UserUtils::getModuleUsers ($adb, $moduleName, $currentUser, $assignedUserId, true);
			} else {
				$users = UserUtils::getModuleUsers ($adb, $moduleName, $currentUser, $assignedUserId);
			}
			$usersOptions = self::buildOptions ($users, $assignedUserId);
			if ($mode == 'mass_edit') {
				$usersOptions[''] = array ('Seleccionar usuario' => 'selected');
			}
			if (($fieldName == 'assigned_user_id') && ($is_admin == false) && ($profileGlobalPermission [2] == 1) && (in_array ($defaultOrgSharingPermission [ $moduleId ], array (0, 3)))) {
				$groups = UserUtils::getCurrentUserAccessGroups ($adb, $moduleName);
			} else {
				$groups = UserUtils::getGroups ($adb);
			}
			$groupsOptions = self::buildOptions ($groups, $assignedUserId);
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => array ($usersOptions, $groupsOptions),
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 * @param Users|stdClass $currentUser
		 *
		 * @return array
		 */
		private static function getPicklistFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel, $currentUser) {
			require_once ('modules/PickList/PickListUtils.php');
			$roleid         = $currentUser->roleid;
			$picklistValues = getAssignedPicklistValues ($fieldName, $roleid, $adb);
			
			$total          = 0;
			$options        = null;
			if (!empty ($picklistValues)) {
				$fieldValue  = $fields [ $fieldName ];
				$fieldValues = array_map ('trim', explode ('|##|', $fieldValue));
				$options     = array ();
				foreach ($picklistValues as $pickListValue) {
					if (in_array ($pickListValue, $fieldValues)) {
						$selected = 'selected';
						$total++;
					} else {
						$selected = '';
					}
					$options [] = array (Translator::translateFromApplicationDictionary ($pickListValue), $pickListValue, $selected);
				}
				if (($total == 0) && (!empty ($fieldValue))) {
					
					
					// Mostrar el valor real en rojo con tooltip en lugar de "No Accesible"
					$displayValue = !empty($fieldValue) ? $fieldValue : '';
					$redDisplayValue = '<span style="color: red; font-weight: bold;" title="Valor no disponible para su rol">' . $displayValue . '</span>';
					$options [] = array ($redDisplayValue, $fieldValue, 'selected');
				}
			}
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => $options,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return array|null
		 */
		private static function getPipelineFieldOptions (PearDatabase $adb, $moduleName, $fieldName) {
			require_once ('include/platzilla/Managers/PipelineManager.php');
			$pipeline = PipelineManager::getInstance ($adb)->fetchPipeline ($moduleName, $fieldName);
			return !empty ($pipeline) ? $pipeline->getValues () : null;
		}

		/**
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 *
		 * @return array
		 */
		private static function getReminderTimeFieldOptions ($moduleName, $fields, $fieldName, $fieldLabel) {
			$reminderDays    = floor ($fields[ $fieldName ] / (24 * 60));
			$reminderHours   = floor (($fields[ $fieldName ] - $reminderDays * 24 * 60) / 60);
			$reminderMinutes = (($fields[ $fieldName ] - $reminderDays * 24 * 60) % 60);

			$options = array (
				array (
					array (0, 32, 'remdays', Translator::translateFromApplicationDictionary ('LBL_DAYS'), $reminderDays),
					array (0, 24, 'remhrs', Translator::translateFromApplicationDictionary ('LBL_HOURS'), $reminderHours),
					array (1, 60, 'remmin', Translator::translateFromApplicationDictionary ('LBL_MINUTES') . '&nbsp;&nbsp;' . Translator::translateFromApplicationDictionary ('LBL_BEFORE_EVENT'), $reminderMinutes),
				),
				array (
					!empty ($fields [ $fieldName ]) ? 'checked' : '',
					getTranslatedString ('LBL_YES'),
					getTranslatedString ('LBL_NO'),
				),
			);
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => $options,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $fields
		 * @param string $fieldName
		 * @param string $fieldLabel
		 * @param boolean $isAdmin
		 *
		 * @return array
		 */
		private static function getRoleFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel, $isAdmin) {
			$fieldValue = $fields [ $fieldName ];
			$role       = RoleManager::getInstance ($adb)->fetchRole ($fieldValue, true);
			if (!empty ($role)) {
				$roleName = $role->getName ();
			} else {
				$roleName = null;
			}
			return array (
				'label'  => Translator::translate ($fieldLabel, $moduleName),
				'values' => array (
					$fieldValue,
					$roleName,
					$isAdmin,
				),
			);
		}

		/**
		 * No tengo la menor idea de qué quiso hacer APU con esta lógica. Deduzco que preparar la data de una forma bastante rara para después poder renderizarla
		 *
		 * @param array $oldBlockOptions
		 *
		 * @return array
		 */
		private static function fixBlocksOptions ($oldBlockOptions) {
			if (empty ($oldBlockOptions)) {
				return $oldBlockOptions;
			}
			$newBlockOptions = array ();
			foreach ($oldBlockOptions as $blockId => $oldFieldOptions) {
				$newFieldOptions = array ();
				$n               = count ($oldFieldOptions);
				$i               = 0;
				for ($j = 0; $i < $n; $j++) {
					$keyOne = $oldFieldOptions [ $i ];
					if ((is_array ($oldFieldOptions [ ($i + 1) ])) && (!in_array ($keyOne [0][0], array (19, 20, 256)))) {
						$keyTwo = $oldFieldOptions [ ($i + 1) ];
					} else {
						$keyTwo = array ();
					}
					if (!in_array ($keyOne [0][0], array (19, 20, 256))) {
						$newFieldOptions [ $j ] = array (0 => $keyOne, 1 => $keyTwo);
						$i += 2;
					} else {
						$newFieldOptions [ $j ] = array (0 => $keyOne);
						$i++;
					}
				}
				$newBlockOptions [ $blockId ] = $newFieldOptions;
			}
			return $newBlockOptions;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return array|null
		 */
		private static function getReferencedModuleFilterParameters (PearDatabase $adb, $moduleName, $fieldName) {
			$references = FieldModuleReferenceManager::getInstance ($adb)->fetchReferences ($moduleName, $fieldName);
			if (empty ($references)) {
				return null;
			}

			$reference = $references [0];
			$filters   = $reference->getFilters ();
			if (empty ($filters)) {
				return null;
			}

			$requestedFieldValues = array ();
			$description = array ();
			foreach ($filters as $index => $filter) {
				if ($filter->getValueType () == FieldModuleReferenceFilter::TYPE_SOURCE_FIELD) {
					$requestedFieldValues [] =  "{$filter->getValue ()}@{$filter->getFieldName ()}";
				}
				$comparator = $filter->getComparator ();
				$operator = $filter->getOperator ();
				$valueType = $filter->getValueType ();
				$description [] = $filter->getFieldName ();
				$description [] = $comparator == FieldModuleReferenceFilter::COMPARATOR_EQUALS ? 'IGUAL A' : 'DIFERENTE DE';
				$description [] = $valueType == FieldModuleReferenceFilter::TYPE_SOURCE_FIELD ? "|{$filter->getValue ()}|" : $filter->getValue ();
				if ($index < count ($filters) - 1) {
					$description [] = $operator == FieldModuleReferenceFilter::OPERATOR_AND ? 'Y' : 'O';
				}
			}
			return array (
				'description' => '', // No mostrar la descripción del filtro en la interfaz
				'fieldnames' => $requestedFieldValues,
			);
		}

		public static function fetchDailyReportJobs ($adb, $userId, $period) {
			$result = $adb->pquery (
				'SELECT
					crm.description,
				    ot.orden_de_trabajoid,
       				ot.cod_orden_de_tra,
       				ot.titulo, ot.fecha_de_emision,
       				ot.tipo_dactividad,
				    task.activityid,
				    task.subject,
				    (SELECT COUNT(*) 
				     FROM vtiger_activity_report ar_check
				     WHERE ar_check.activityid = task.activityid
				       AND ar_check.userid = ?
				       AND DATE(ar_check.report_on) = ?) as has_report
				FROM
				    vtiger_crmentity crm
				INNER JOIN vtiger_orden_de_trabajo ot ON
				    crm.crmid = ot.orden_de_trabajoid
				LEFT JOIN vtiger_activity task ON
			    task.related_id = ot.orden_de_trabajoid
			    AND task.eventstatus NOT IN(?,?,?)
			WHERE
			    crm.deleted = 0 AND
			  ot.estado_de_la_orden NOT IN(?,?) AND
			  crm.smownerid = ? AND
			  (
			        (taskToMatrix(task.date_start, task.due_date, task.eventstatus, crm.createdtime, ?, ?) = 1
			         AND (task.progress < 100 OR task.progress IS NULL
			              OR EXISTS (
			                  SELECT 1 
			                  FROM vtiger_activity_report ar2
			                  WHERE ar2.activityid = task.activityid
			                    AND ar2.userid = ?
			                    AND DATE(ar2.report_on) = ?
			              )))
			        OR EXISTS (
			            SELECT 1 
			            FROM vtiger_activity_report ar
			            INNER JOIN vtiger_activity a ON a.activityid = ar.activityid
			            WHERE a.related_id = ot.orden_de_trabajoid
			              AND ar.userid = ?
			              AND DATE(ar.report_on) = ?
			        )
			        OR (task.activityid IS NOT NULL AND (task.progress < 100 OR task.progress IS NULL))
			    )
			ORDER BY
			     ot.orden_de_trabajoid  ASC',
				array ($userId, $period, '','NULL', 'Held', 'Terminado', 'Cancelado', $userId, $period, $period, $userId, $period, $userId, $period)
			);
			if ($adb->num_rows ($result) > 0) {
				$relatedTasks = array ();
				$preloadedTasks = array ();
				$jobIds       = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					if (!empty($row['activityid'])) {
						$relatedTasks [ $row ['orden_de_trabajoid'] ][] = json_encode (
							array (
								$row ['activityid'],
								(!empty($row ['subject'])) ? $row ['subject'] : 'Tarea sin titulo',
							)
							, JSON_FORCE_OBJECT
						);
						
						// Si la tarea tiene reporte para esta fecha, agregarla a preloadedTasks
						if ($row['has_report'] > 0) {
							$preloadedTasks[] = array(
								'activityid' => $row['activityid'],
								'subject' => (!empty($row ['subject'])) ? $row ['subject'] : 'Tarea sin titulo',
								'jobid' => $row['orden_de_trabajoid'],
								'jobtitle' => $row['titulo']
							);
						}
					}
					if (!in_array ($row ['orden_de_trabajoid'], $jobIds)) {
						$jobIds [] = $row ['orden_de_trabajoid'];
						$jobs []   = $row;
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($jobs)) ?  array ('jobs' => $jobs, 'tasks' => $relatedTasks, 'preloaded' => $preloadedTasks) :null;
		}

		/**
		 * Obtiene la estructura de campos en forma de un arreglo
		 *
		 * @codingStandardsIgnoreStart
		 * NOTA: CodeSniffer detecta una violación de complejidad ciclomática (22) . La misma ocurre por la cantidad de if anidados. Dado que refactorizar la función
		 * resultaría en pérdida de la legibilidad, se ignorará tal reporte
		 *
		 * @param PearDatabase $adb
		 * @param Users $currentUser
		 * @param integer|string $uiType
		 * @param string $fieldName
		 * @param string $fieldLabel
		 * @param array $fields
		 * @param string $moduleName
		 * @param string $typeOfData
		 *
		 * @return array
		 */
		public static function getFieldOptions (PearDatabase $adb, $moduleName, $fields, $fieldName, $fieldLabel, $uiType, $typeOfData, $currentUser, $mode = '') {
			global $defaultOrgSharingPermission, $is_admin, $profileGlobalPermission;

			$defaultOrgSharingPermission = null;
			$is_admin                    = null;
			$profileGlobalPermission     = null;
			require ('user_privileges/current_user_privileges.php');
			$numberingHelper = NumberHelper::getInstance ($adb, $currentUser);
			$fieldLabel      = from_html ($fieldLabel);

			$fieldLabels = array ();
			$fieldValues = array ();
			$isMultiple  = false;
			if (in_array ($uiType, array (5, 6, 23))) {
				$options        = self::getDateTimeFieldOptions ($moduleName, $fields, $fieldName, $fieldLabel, $uiType, $currentUser);
				$fieldLabels [] = $options ['label'];
				$fieldValues    = array_merge ($fieldValues, $options ['values']);
			} else if ($uiType == 10) {
				$options        = self::getModuleReferenceFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel);
				$fieldLabels [] = $options ['label'];
				$fieldValues [] = $options ['values'];
			} else if (in_array ($uiType, array (14, 17, 21, 22, 56, 85, 5006))) {
				$fieldLabels[]  = Translator::translate ($fieldLabel, $moduleName);
				$fieldValues [] = $fields [ $fieldName ];
			} else if (in_array ($uiType, array (15, 33))) {
				$options        = self::getPicklistFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel, $currentUser);
				$fieldLabels [] = $options ['label'];
				$fieldValues [] = $options ['values'];
			} else if ($uiType == 16) {
				$options        = self::getGlobalPicklistFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel);
				$fieldLabels [] = $options ['label'];
				$fieldValues [] = $options ['values'];
				$isMultiple     = $options ['ismultiple'];
			} else if ($uiType == 19) {
				if (isset ($_REQUEST ['body'])) {
					$value = ($_REQUEST ['body']);
				} else {
					$value = $fields [ $fieldName ];
				}
				$fieldLabels [] = Translator::translate ($fieldLabel, $moduleName);
				$fieldValues [] = $value;
			} else if ($uiType == 26) {
				$options        = self::getFolderNameFieldOptions ($adb, $moduleName, $fields, $fieldLabel);
				$fieldLabels [] = $options ['label'];
				$fieldValues [] = $options ['values'];
			} else if ($uiType == 27) {
				$options     = self::getAttachmentDownloadTypeFieldOptions ($moduleName, $fields, $fieldName, $fieldLabel);
				$fieldLabels = array_merge ($fieldLabels, $options ['label']);
				$fieldValues = array_merge ($fieldValues, $options ['values']);
			} else if ($uiType == 28) {
				$options        = self::getAttachmentNameTypeFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel);
				$fieldLabels [] = $options ['label'];
				$fieldValues    = array_merge ($fieldValues, $options ['values']);
			} else if ($uiType == 30) {
				$options        = self::getReminderTimeFieldOptions ($moduleName, $fields, $fieldName, $fieldLabel);
				$fieldLabels [] = $options ['label'];
				$fieldValues    = array_merge ($fieldValues, $options ['values']);
			} else if ($uiType == 31) {
				$fieldLabels [] = Translator::translate ($fieldLabel, $moduleName);
				$fieldValues [] = array ('Centaurus', 'centaurus', 'selected');
			} else if ($uiType == 32) {
				$fieldLabels [] = Translator::translate ($fieldLabel, $moduleName);
				$fieldValues [] = array ('Español', 'es_es', 'selected');
			} else if (in_array ($uiType, array (52, 407))) {
				$options        = self::getModifiedByFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel, $uiType, $currentUser);
				$fieldLabels [] = $options ['label'];
				$fieldValues [] = $options ['values'];
			} else if ($uiType == 53) {
				$options        = self::getOwnerFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel, $currentUser, $mode);
				$fieldLabels [] = $options ['label'];
				$fieldValues    = array_merge ($fieldValues, $options ['values']);
			} else if ($uiType == 63) {
				$options        = self::getActivityDurationFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel);
				$fieldLabels [] = $options ['label'];
				$fieldValues    = array_merge ($fieldValues, $options ['values']);
			} else if ($uiType == 98) {
				$options        = self::getRoleFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel, $is_admin);
				$fieldLabels [] = $options ['label'];
				$fieldValues    = array_merge ($fieldValues, $options ['values']);
			} else if ($uiType == 156) {
				$fieldLabels [] = Translator::translate ($fieldLabel, $moduleName);
				$fieldValues [] = $fields [ $fieldName ];
				$fieldValues [] = $is_admin;
			} else if (in_array ($uiType, array (257, 258))) {
				$options        = self::getImageFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel);
				$fieldLabels [] = $options ['label'];
				$fieldValues    = array_merge ($fieldValues, $options ['values']);
			} else if ($uiType == 404) {
				$options        = self::getModuleRecordsFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel);
				$fieldLabels [] = $options ['label'];
				$fieldValues [] = $options ['values'];
			} else if ($uiType == 5010) {
				$appFieldParameters   = (isset ($_REQUEST['afp'])) ? $_REQUEST ['afp'] : null;
				$crmId                = (isset($fields['record_id'])) ? $fields['record_id'] : null;
				$appFieldClass        = AppFieldManager::getInstance ($adb)->fetchAppFieldByName ($fieldName, $moduleName);
				$handlerClassName     = $appFieldClass->getHandlerClass ();
				$handlerMethodName    = $appFieldClass->getHandlerMethod ();
				$handlerClassFilePath = "modules/{$moduleName}/handlers/{$handlerClassName}.class.php";
				if (!file_exists ($_SERVER['DOCUMENT_ROOT'] . '/' . $handlerClassFilePath)) {
					$fieldValues [] = "No se encuentra la clase gestora {$handlerClassName}";
				}
				require_once ($handlerClassFilePath);
				/** @var  $handler */
				$handler = call_user_func_array (array ($handlerClassName, 'getInstance'), array ($adb));
				if (!is_callable (array ($handler, $handlerMethodName))) {
					$fieldValues [] = "No se encuentra el método {$handlerMethodName} en la clase gestora {$handlerClassName}";
				}
				$fieldLabels [] = $handlerClassName;
				$fieldValues [] = call_user_func_array (array ($handler, $handlerMethodName), array ($crmId, null, $currentUser, $appFieldParameters));
			} elseif (!empty ($typeOfData) && in_array ($uiType, array (7, 9, 71))) {
				$fieldLabels []  = getTranslatedString ($fieldLabel, $moduleName);
				$fieldValues [] =  $numberingHelper->setNumberFormat ($fields [ $fieldName ], $fieldName);
			} else {
				$fieldLabels [] = getTranslatedString ($fieldLabel, $moduleName);
				$fieldValues [] = ($moduleName == 'Documents') && ($fieldName == 'fileversion') && empty ($fields [ $fieldName ]) ? '' : $fields [ $fieldName ];
			}
			$dummy        = explode ('~', $typeOfData);
			$mandatory    = $dummy [1];
			$fieldOptions = array (
				array ($uiType),
				$fieldLabels,
				array ($fieldName),
				$fieldValues,
				$mandatory,
			);
			if (isset ($isMultiple)) {
				$fieldOptions [8] = $isMultiple;
			}
			
			return $fieldOptions;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param ADORecordSet $fieldsResult
		 * @param array $fields
		 * @param array $blockLabels
		 * @param string $mode
		 * @param string $platform
		 * @param Users $currentUser
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getBlockOptions (PearDatabase $adb, $moduleName, $fieldsResult, $fields, $blockLabels, $mode, $platform, $currentUser) {
			if ($adb->num_rows ($fieldsResult) > 0) {
				$blocksOptions = array ();
				$uitype5010Count = 0;
				while ($row = $adb->fetchByAssoc ($fieldsResult)) {
					$blockId       = $row ['block'];
					$calculationId = $row ['paradicional'];
					$defaultValue  = $row ['defaultvalue'];
					$fieldId       = $row ['fieldid'];
					$fieldLabel    = $row ['fieldlabel'];
					$fieldName     = $row ['fieldname'];
					$typeOfData    = $row ['typeofdata'];
					$uiType        = $row ['uitype'];
					
					if ($uiType == 5010) {
						$uitype5010Count++;
					}

					$fields [ $fieldName ] = ((empty ($mode)) || ($mode == 'create')) && (empty ($fields [ $fieldName ])) ? $defaultValue : $fields [ $fieldName ];
					// Procesar expresiones de fecha dinámicas (TODAY+X, CURRENT_DATE-X)
					if ($uiType == 5 && ((empty ($mode)) || ($mode == 'create')) && !empty($fields [ $fieldName ])) {
						require_once('modules/Settings/lib/DateDefaultValueUtils.php');
						$processedValue = processDateDefaultValue($fields [ $fieldName ]);
						if ($processedValue !== $fields [ $fieldName ] && !empty($processedValue)) {
							$fields [ $fieldName ] = $processedValue;
						}
					}
					$fieldOptions          = self::getFieldOptions ($adb, $moduleName, $fields, $fieldName, $fieldLabel, $uiType, $typeOfData, $currentUser, $mode);
					$fieldOptions [6]      = $calculationId;
					$fieldOptions [7]     = $fieldId;
					if ($uiType == 8192) {
						$fieldOptions [8] = self::getPipelineFieldOptions ($adb, $moduleName, $fieldName);
					} else if ($uiType == 10) {
						$fieldOptions [9] = self::getReferencedModuleFilterParameters ($adb, $moduleName, $fieldName);
					}
					$blocksOptions [ $blockId ][] = $fieldOptions;
				}
				$blocksOptions = self::fixBlocksOptions ($blocksOptions);

				$blocksWithLabelsOptions = array ();
				foreach ($blockLabels as $blockId => $label) {
					if (!isset ($blocksOptions [ $blockId ])) {
						continue;
					}

					$translatedLabel = !empty ($label) ? Translator::translate ($label, $moduleName) : '';
					if (isset ($blocksWithLabelsOptions [ $translatedLabel ])) {
						$blocksWithLabelsOptions [ $translatedLabel ] = array_merge ($blocksWithLabelsOptions [ $translatedLabel ], $blocksOptions [ $blockId ]);
					} else {
						$blocksWithLabelsOptions [ $translatedLabel ] = $blocksOptions [ $blockId ];
					}
				}
				
			} else {
				$blocksWithLabelsOptions = array ();
			}
			DatabaseUtils::closeResult ($fieldsResult);
			$fieldsResult = null;
			return $blocksWithLabelsOptions;
		}

		/**
		 * This function returns the data type of the vtiger_fields, with vtiger_field label, which is used for javascript validation.
		 * Param array $validationData array of vtiger_fieldnames with datatype
		 * Return array
		 *
		 * @param array $validationData
		 *
		 * @return array
		 */
		public static function splitValidationData ($validationData) {
			$fieldName   = '';
			$fieldLabel  = '';
			$fldDataType = '';
			foreach ($validationData as $fldName => $fldLabels) {
				if ($fieldName == '') {
					$fieldName = "'" . $fldName . "'";
				} else {
					$fieldName .= ",'" . $fldName . "'";
				}
				foreach ($fldLabels as $fldLabel => $datatype) {
					if ($fieldLabel == '') {
						$fieldLabel = "'" . addslashes ($fldLabel) . "'";
					} else {
						$fieldLabel .= ",'" . addslashes ($fldLabel) . "'";
					}
					if ($fldDataType == '') {
						$fldDataType = "'" . $datatype . "'";
					} else {
						$fldDataType .= ",'" . $datatype . "'";
					}
				}
			}
			return array (
				'datatype'   => $fldDataType,
				'fieldlabel' => $fieldLabel,
				'fieldname'  => $fieldName,
			);
		}

	}
