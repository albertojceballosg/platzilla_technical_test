<?php
	require_once ('data/CrmEntityUtils.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Objects/DataSharingRequest.php');
	require_once ('include/platzilla/Objects/DataSharingRule.php');
	require_once ('include/platzilla/Objects/DataSharingSync.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/Translator.class.php');
	require_once ('include/utils/SystemVariables.class.php');
	require_once ('modules/emailmanager/emailmanager.php');

	class DataSharingManager {
		const REQUEST_TYPE_CREATE_INSTANCE     = 0;
		const REQUEST_TYPE_INSTALL_APPLICATION = 1;

		/** @var DataSharingManager[] */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * DataSharingManager constructor.
		 *
		 * @param PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param PlatformManager $pm
		 * @param DataSharingRequest $request
		 * @param array $additionalArguments
		 *
		 * @return PlatformInstance
		 * @throws Exception
		 */
		private function createInstance ($pm, $request, $additionalArguments) {
			$this->validateRequestAdditionalArguments ($additionalArguments, self::REQUEST_TYPE_CREATE_INSTANCE);
			$companyName      = empty ($additionalArguments ['companyname']) ? trim ("{$additionalArguments ['firstname']} {$additionalArguments ['lastname']}") : $additionalArguments ['companyname'];
			$administrator    = User::getInstance ()
				->setAdministrator (true)
				->setEmail ($request->getRecipientAddress ())
				->setFirstName ($additionalArguments ['firstname'])
				->setLastName ($additionalArguments ['lastname'])
				->setPlainPassword ($additionalArguments ['password'])
				->setUserName ($request->getRecipientAddress ());
			$applicationCodes = array ($additionalArguments ['applicationcode']);
			return $pm->createInstance ($additionalArguments ['platform'], $companyName, $administrator, null, $applicationCodes);
		}

		/**
		 * @param CRMEntity $sourceEntity
		 * @param DataSharingRuleDetail $detail
		 *
		 * @return null|string
		 */
		private function evaluateParameterFormula ($sourceEntity, $detail) {
			$actionType       = $detail->getActionType ();
			$parameterType    = $detail->getParameterType ();
			$parameterFormula = $detail->getParameterFormula ();

			if ($actionType == DataSharingRuleDetail::ACTION_RECEIVE_ONLY) {
				$value = null;
			} else if (in_array ($parameterType, array (DataSharingRuleDetail::PARAMETER_TYPE_LITERAL))) {
				$value = $parameterFormula;
			} else if (in_array ($parameterType, array (DataSharingRuleDetail::PARAMETER_TYPE_SOURCE_FIELD))) {
				$value = isset ($sourceEntity->column_fields [ $parameterFormula ]) ? $sourceEntity->column_fields [ $parameterFormula ] : null;
			} else if (in_array ($parameterType, array (DataSharingRuleDetail::PARAMETER_TYPE_SOURCE_GRID_FIELD))) {
				$value = isset ($sourceEntity->column_fields [ $parameterFormula ]) ? $sourceEntity->column_fields [ $parameterFormula ] : null;
			} else if (in_array ($parameterType, array (DataSharingRuleDetail::PARAMETER_TYPE_VARIABLE))) {
				$value = SystemVariables::getValue ($this->adb, str_replace ('}', '', str_replace ('{', '', $parameterFormula)), $sourceEntity->column_fields);
			} else {
				$value = null;
			}
			return $value;
		}

		/**
		 * @param $moduleName
		 *
		 * @return DataSharingRule[]
		 */
		private function fetchDeletedRules ($moduleName) {
			if (empty ($moduleName)) {
				return array ();
			}

			$rules  = array ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_deletedelements WHERE elementtype=? AND modulename=?', array ('datasharingrule', $moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					/** @var DataSharingRule $rule */
					$rule = unserialize ($row ['serializedobject']);
					$rule->setDeleted (true);
					$rules [] = $rule;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rules;
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 * @param integer $sourceEntityId
		 *
		 * @return CRMEntity|null
		 */
		private function fetchReferencedEntity ($moduleName, $fieldName, $sourceEntityId) {
			if ((empty ($moduleName)) || (empty ($fieldName)) || (empty ($sourceEntityId))) {
				return null;
			}

			$field = FieldManager::getInstance ($this->adb)->fetchFieldByName ($moduleName, $fieldName);
			if (empty ($field)) {
				return null;
			}

			$moduleReferences = $field->getModuleReferences ();
			if (empty ($moduleReferences)) {
				return null;
			}

			$referencedModuleName = $moduleReferences [0]->getReferencedModuleName ();
			return PlatformUtils::getCrmEntity ($this->adb, $referencedModuleName, $sourceEntityId);
		}

		/**
		 * @param $ruleId
		 *
		 * @return DataSharingRuleDetail[]|null
		 */
		private function fetchRuleDetails ($ruleId) {
			if (empty ($ruleId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rulesdetails WHERE ruleid=?', array ($ruleId));
			if ($this->adb->num_rows ($result) > 0) {
				$details = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$details [] = DataSharingRuleDetail::getInstance ()
						->setId (intval ($row ['ruledetailid']))
						->setActionType ($row ['actiontype'])
						->setParameterFormula ($row ['parameterformula'])
						->setParameterType ($row ['parametertype'])
						->setRuleId (intval ($row ['ruleid']))
						->setSourceModuleName ($row ['sourcemodulename'])
						->setTargetFieldName ($row ['targetfieldname'])
						->setTargetModuleName ($row ['targetmodulename']);
				}
			} else {
				$details = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $details;
		}

		/**
		 * @param string $ruleId
		 * @param string $moduleName
		 *
		 * @return DataSharingRule|null
		 */
		private function fetchStandardRule ($ruleId, $moduleName) {
			$fields = FieldManager::getInstance ($this->adb)->fetchFields ($moduleName);
			if (empty ($fields)) {
				return null;
			}

			$details = array ();
			foreach ($fields as $field) {
				$uiType    = $field->getUiType ();
				$fieldName = $field->getName ();
				if (in_array ($uiType, array (Field::UI_TYPE_CODE, Field::UI_TYPE_CREATED_TIME, Field::UI_TYPE_OWNER))) {
					continue;
				} else if (in_array ($uiType, array (Field::UI_TYPE_MODULE_RECORDS, Field::UI_TYPE_MODULE_REFERENCE))) {
					if ($ruleId == DataSharingRequest::RULE_FULL) {
						$actionType       = DataSharingRuleDetail::ACTION_SEND_AND_RECEIVE;
						$parameterType    = DataSharingRuleDetail::PARAMETER_TYPE_SHARING_RULE;
						$parameterFormula = DataSharingRequest::RULE_FULL;
					} else {
						$actionType       = DataSharingRuleDetail::ACTION_SEND_ONLY;
						$parameterType    = DataSharingRuleDetail::PARAMETER_TYPE_LITERAL;
						$parameterFormula = null;
					}
				} else {
					$actionType       = DataSharingRuleDetail::ACTION_SEND_AND_RECEIVE;
					$parameterType    = $uiType == Field::UI_TYPE_GRID ? DataSharingRuleDetail::PARAMETER_TYPE_SOURCE_GRID_FIELD : DataSharingRuleDetail::PARAMETER_TYPE_SOURCE_FIELD;
					$parameterFormula = $fieldName;
				}
				$details [] = DataSharingRuleDetail::getInstance ()
					->setId ($field->getId ())
					->setActionType ($actionType)
					->setParameterFormula ($parameterFormula)
					->setParameterType ($parameterType)
					->setRuleId (0)
					->setSourceModuleName ($moduleName)
					->setTargetFieldName ($fieldName)
					->setTargetModuleName ($moduleName);
			}

			$rule = DataSharingRule::getInstance ()
				->setId (0)
				->setDetails ($details)
				->setLocked (false)
				->setModuleName ($moduleName)
				->setName ($ruleId)
				->setStatus (DataSharingRule::STATUS_ACTIVE);
			return $rule;
		}

		/**
		 * @param DataSharingSync $sync
		 * @param string $instanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 *
		 * @return DataSharingRuleDetail[]|null
		 */
		private function fetchSyncRuleMap ($sync, $instanceCode, $moduleName, $recordId) {
			$ruleId = $sync->getRuleId ();
			if (is_numeric ($ruleId)) {
				if (($sync->getSourceInstanceCode () == $instanceCode) && ($sync->getSourceModuleName () == $moduleName) && ($sync->getSourceRecordId () == $recordId)) {
					// El registro es de la instancia de origen
					$sourceAdb = $this->adb;
				} else {
					// El registro a sincronizar es de la instancia destino
					$sourceAdb = AdbManager::getInstance ()->getSourceInstanceAdb ($sync->getSourceInstanceCode ());
				}
				$rule = self::getInstance ($sourceAdb)->fetchRuleById ($ruleId);
			} else {
				$rule = $this->fetchStandardRule ($ruleId, $sync->getSourceModuleName ());
			}
			if (empty ($rule)) {
				return null;
			} else {
				return $this->getRuleMap ($rule);
			}
		}

		/**
		 * @param DataSharingRule $rule
		 *
		 * @return array|null
		 */
		private function getRuleMap ($rule) {
			$ruleMap = array ();
			$details = $rule->getDetails ();
			foreach ($details as $detail) {
				$ruleMap [ $detail->getTargetFieldName () ] = $detail;
			}
			return !empty ($ruleMap) ? $ruleMap : null;
		}

		/**
		 * @param DataSharingRule $rule
		 */
		private function saveRuleDetails ($rule) {
			$details = $rule->getDetails ();
			if (empty ($details)) {
				return;
			}

			$ruleId             = $rule->getId ();
			$processedDetailIds = array ();
			foreach ($details as $detail) {
				$detailId = $detail->getId ();
				if (empty ($detailId)) {
					$this->adb->pquery (
						'INSERT INTO vtiger_instancesdatasharing_rulesdetails (ruleid, sourcemodulename, targetmodulename, targetfieldname, actiontype, parametertype, parameterformula) VALUES (?, ?, ?, ?, ?, ?, ?)',
						array ($ruleId, $detail->getSourceModuleName (), $detail->getTargetModuleName (), $detail->getTargetFieldName (), $detail->getActionType (), $detail->getParameterType (), $detail->getParameterFormula ())
					);
					$detailId = $this->adb->getLastInsertID ();
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_instancesdatasharing_rulesdetails SET ruleid=?, sourcemodulename=?, targetmodulename=?, targetfieldname=?, actiontype=?, parametertype=?, parameterformula=? WHERE ruledetailid=?',
						array ($ruleId, $detail->getSourceModuleName (), $detail->getTargetModuleName (), $detail->getTargetFieldName (), $detail->getActionType (), $detail->getParameterType (), $detail->getParameterFormula (), $detailId)
					);
				}
				$detail->setId ($detailId)
					->setRuleId ($ruleId);
				$processedDetailIds [] = $detailId;
			}

			$questionMarks = str_repeat ('?, ', (count ($processedDetailIds) - 1)) . '?';
			$this->adb->pquery ("DELETE FROM vtiger_instancesdatasharing_rulesdetails WHERE ruleid=? AND ruledetailid NOT IN ({$questionMarks})", array_merge (array ($ruleId), $processedDetailIds));
		}

		/**
		 * @param DataSharingRequest $request
		 * @param string $token
		 *
		 * @throws Exception
		 */
		private function sendEmail ($request, $token) {
			$platzillaRootUri = PlatzillaUtils::getPlatzillaRootUri ();
			$sender           = $request->getCreatedBy ();

			$targetInstanceCode = $request->getTargetInstanceCode ();
			if (!empty ($targetInstanceCode)) {
				$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($targetInstanceCode);
				$recipient = UserManager::getInstance ($targetAdb, null)->fetchUserByUsername ($request->getRecipientAddress ());
				if (!empty ($recipient)) {
					$recipientFullName = trim ("{$recipient->getFirstName ()} {$recipient->getLastName ()}");
				} else {
					$recipientFullName = $request->getRecipientAddress ();
				}
			} else {
				$recipientFullName = $request->getRecipientAddress ();
			}

			$status = emailmanager::getInstance ($this->adb)->addSender (
				'Platzilla',
				'no_reply@platzilla.com'
			)->send (
				$request->getRecipientAddress (),
				'es',
				'Invitación a compartir contenido',
				array (
					'ENVIAR_A'       => $recipientFullName,
					'ENVIADA_POR'    => trim ("{$sender->getFirstName ()} {$sender->getLastName ()}"),
					'TIPO_CONTENIDO' => Translator::translate ($request->getModuleName (), $request->getModuleName ()),
					'URL'            => "{$platzillaRootUri}/index.php?module=store&action=invitation&id={$token}&Popup=true",
				)
			);
			if ($status != emailmanager::STATUS_SENT) {
				throw new Exception ("Se ha presentado un error al enviar el correo: código {$status}");
			}
		}

		/**
		 * @param DataSharingRequest $request
		 * @param CRMEntity $sourceEntity
		 * @param PearDatabase $sourceAdb
		 * @param PearDatabase $targetAdb
		 * @param DataSharingRuleDetail[] $ruleMap
		 * @param integer[] $processedEntityIds
		 *
		 * @return array
		 */
		private function shareEntity ($request, $sourceEntity, $sourceAdb, $targetAdb, $ruleMap, $processedEntityIds) {
			if ((empty ($sourceEntity)) || (!($sourceEntity instanceof CRMEntity))) {
				return null;
			}

			$entityId = $sourceEntity->column_fields ['record_id'];
			if (in_array ($entityId, array_keys ($processedEntityIds))) {
				return null;
			}

			$processedEntityIds [ $entityId ] = null;
			$moduleName                       = $sourceEntity->column_fields ['record_module'];
			$targetEntity                     = PlatformUtils::getCrmEntity ($targetAdb, $moduleName);
			foreach ($ruleMap as $fieldName => $detail) {
				if ($detail->getParameterType () == DataSharingRuleDetail::PARAMETER_TYPE_SHARING_RULE) {
					$referencedSourceEntity = $this->fetchReferencedEntity ($moduleName, $fieldName, $sourceEntity->column_fields [ $fieldName ]);
					if (!empty ($referencedSourceEntity)) {
						$referencedRuleId = $detail->getParameterFormula ();
						if (!in_array ($referencedRuleId, array (DataSharingRequest::RULE_FULL, DataSharingRequest::RULE_MINIMAL))) {
							$rule = $this->fetchRuleById ($referencedRuleId);
						} else {
							$rule = $this->fetchStandardRule ($referencedRuleId, $referencedSourceEntity->column_fields ['record_module']);
						}
						if (!empty ($rule)) {
							$referencedRuleMap = $this->getRuleMap ($rule);
							$sharedEntityIds   = $this->shareEntity ($request, $referencedSourceEntity, $sourceAdb, $targetAdb, $referencedRuleMap, $processedEntityIds);
						} else {
							$sharedEntityIds = null;
						}
						if (!empty ($sharedEntityIds)) {
							foreach ($sharedEntityIds as $sourceEntityId => $targetEntityId) {
								$processedEntityIds [ $sourceEntityId ] = $targetEntityId;
							}
						}
						$value = $processedEntityIds [ $referencedSourceEntity->column_fields ['record_id'] ];
					} else {
						$value = null;
					}
				} else {
					$value = $this->evaluateParameterFormula ($sourceEntity, $detail);
				}
				$targetEntity->column_fields [ $fieldName ] = $value;
			}
			PlatformUtils::saveCrmEntity ($targetAdb, $targetEntity, $moduleName);
			$targetAdb->pquery (
				'INSERT INTO vtiger_instancesdatasharing_syncs (sourceinstancecode, sourcemodulename, sourcerecordid, sourceemailaddress, targetinstancecode, targetmodulename, targetrecordid, targetemailaddress, ruleid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($request->getSourceInstanceCode (), $sourceEntity->column_fields ['record_module'], $sourceEntity->column_fields ['record_id'], $request->getCreatedBy ()->getEmail (), $request->getTargetInstanceCode (), $targetEntity->column_fields ['record_module'], $targetEntity->column_fields ['record_id'], $request->getRecipientAddress (), $request->getRuleId ())
			);
			$sourceAdb->pquery (
				'INSERT INTO vtiger_instancesdatasharing_syncs (sourceinstancecode, sourcemodulename, sourcerecordid, sourceemailaddress, targetinstancecode, targetmodulename, targetrecordid, targetemailaddress, ruleid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($request->getSourceInstanceCode (), $sourceEntity->column_fields ['record_module'], $sourceEntity->column_fields ['record_id'], $request->getCreatedBy ()->getEmail (), $request->getTargetInstanceCode (), $targetEntity->column_fields ['record_module'], $targetEntity->column_fields ['record_id'], $request->getRecipientAddress (), $request->getRuleId ())
			);
			$processedEntityIds [ $entityId ] = $targetEntity->column_fields ['record_id'];
			return $processedEntityIds;
		}

		/**
		 * @param DataSharingRequest $request
		 * @param PearDatabase $targetAdb
		 *
		 * @return array|null
		 */
		private function shareEntities ($request, $targetAdb) {
			$ruleId = $request->getRuleId ();
			if (is_numeric ($ruleId)) {
				$rule = $this->fetchRuleById ($ruleId);
			} else {
				$rule = $this->fetchStandardRule ($ruleId, $request->getModuleName ());
			}
			if (empty ($rule)) {
				return null;
			}

			$ruleMap = $this->getRuleMap ($rule);
			if (empty ($ruleMap)) {
				return null;
			}

			$moduleName         = $request->getModuleName ();
			$recordIds          = array_unique ($request->getRecordIds ());
			$processedRecordIds = array ();
			foreach ($recordIds as $recordId) {
				if ($this->isRecordShared ($request->getSourceInstanceCode (), $moduleName, $recordId, $request->getTargetInstanceCode ())) {
					// Ya el registro está compartido con esa instancia, ignorar
					continue;
				}

				$sourceEntity    = PlatformUtils::getCrmEntity ($this->adb, $moduleName, $recordId);
				$sharedEntityIds = !empty ($sourceEntity) ? $this->shareEntity ($request, $sourceEntity, $this->adb, $targetAdb, $ruleMap, $processedRecordIds) : null;
				if (!empty ($sharedEntityIds)) {
					foreach ($sharedEntityIds as $sourceEntityId => $targetEntityId) {
						$processedRecordIds [ $sourceEntityId ] = $targetEntityId;
					}
				}
			}
			return $processedRecordIds;
		}

		/**
		 * @param PearDatabase $targetAdb
		 * @param CRMEntity $targetEntity
		 * @param array $changes
		 */
		private function addAuditingInformation ($targetAdb, $targetEntity, $changes) {
			if (empty ($changes)) {
				return;
			}

			foreach ($changes as $change) {
				if ((!is_scalar ($change ['oldvalue'])) || (!is_scalar ($change ['newvalue']))) {
					continue;
				}
				$fieldName = $change ['fieldname'];
				$newValue  = $change ['newvalue'];
				$oldValue  = $change ['oldvalue'];
				$targetAdb->setDebug (true);
				$targetAdb->pquery (
					'INSERT INTO vtiger_crmentityutils (module, field, oldvalue, newvalue, modifiedby, modifiedon, registryid, date)
					SELECT
						t.tabid,
						f.fieldid,
						?,
						?,
						?,
						?,
						?,
						?
					FROM
						vtiger_tab t
						INNER JOIN vtiger_field f ON f.tabid=t.tabid AND f.fieldname=?
					WHERE
						t.name=?',
					array ($oldValue, $newValue, 0, 0, $targetEntity->column_fields ['record_id'], date ('Y-m-d h:i:s'), $fieldName, $targetEntity->column_fields ['record_module'])
				);
			}
		}

		/**
		 * @param PearDatabase $targetAdb
		 * @param CRMEntity $sourceEntity
		 * @param CRMEntity $targetEntity
		 * @param DataSharingRuleDetail[] $ruleMap
		 * @param string $skippableAction
		 */
		private function synchronizeRecord ($targetAdb, $sourceEntity, $targetEntity, $ruleMap, $skippableAction) {
			$changes = array ();
			foreach ($ruleMap as $fieldName => $detail) {
				if ($detail->getActionType () == $skippableAction) {
					continue;
				}

				$newValue = $this->evaluateParameterFormula ($sourceEntity, $detail);
				if ($targetEntity->column_fields [ $fieldName ] != $newValue) {
					$changes []                                 = array (
						'fieldname' => $fieldName,
						'newvalue'  => $newValue,
						'oldvalue'  => $targetEntity->column_fields [ $fieldName ],
					);
					$targetEntity->column_fields [ $fieldName ] = $newValue;
				}
			}
			if (!empty ($changes)) {
				$targetEntity->mode = 'edit';
				PlatformUtils::saveCrmEntity ($targetAdb, $targetEntity, $targetEntity->column_fields ['record_module']);
				$this->addAuditingInformation ($targetAdb, $targetEntity, $changes);
			}
		}

		/**
		 * @param array $additionalArguments
		 * @param string $requestType
		 *
		 * @throws DataSharingRequestException
		 */
		private function validateRequestAdditionalArguments ($additionalArguments, $requestType) {
			if ($requestType == self::REQUEST_TYPE_CREATE_INSTANCE) {
				if (empty ($additionalArguments ['firstname'])) {
					throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_FIRST_NAME);
				} else if (empty ($additionalArguments ['lastname'])) {
					throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_LAST_NAME);
				} else if (empty ($additionalArguments ['password'])) {
					throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_PASSWORD);
				} else if (empty ($additionalArguments ['repeatedpassword'])) {
					throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_REPEATED_PASSWORD);
				} else if ($additionalArguments ['password'] !== $additionalArguments ['repeatedpassword']) {
					throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_PASSWORDS_DO_NOT_MATCH);
				} else if (empty ($additionalArguments ['platform'])) {
					throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_PLATFORM);
				}
			}

			if ((in_array ($requestType, array (self::REQUEST_TYPE_CREATE_INSTANCE, self::REQUEST_TYPE_INSTALL_APPLICATION))) && (empty ($additionalArguments ['applicationcode']))) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_APPLICATION_CODE);
			}
		}

		/**
		 * @param DataSharingRule $rule
		 *
		 * @throws DataSharingRuleDetailException
		 * @throws DataSharingRuleException
		 */
		private function validateRule ($rule) {
			if ((empty ($rule)) || (!($rule instanceof DataSharingRule))) {
				throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_EMPTY);
			}

			$rule->validate ();

			$moduleName = $rule->getModuleName ();
			$result     = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			$totalRows  = $this->adb->num_rows ($result);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ($totalRows == 0) {
				throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_INVALID_MODULE_NAME);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE modulename=? AND rulename=?', array ($moduleName, $rule->getName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				DatabaseUtils::closeResult ($result);
				$result = null;
				$ruleId = $rule->getId ();
				if ((empty ($ruleId)) || ($row ['ruleid'] != $ruleId)) {
					throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_DUPLICATE_NAME);
				}
				$this->validateRuleDetails ($rule);
			} else {
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}

		/**
		 * @param DataSharingRule $rule
		 *
		 * @throws DataSharingRuleDetailException
		 */
		private function validateRuleDetails ($rule) {
			$details = $rule->getDetails ();
			if (empty ($details)) {
				return;
			}

			foreach ($details as $detail) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($detail->getSourceModuleName ()));
				if ($this->adb->num_rows ($result) == 0) {
					$e = new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_INVALID_SOURCE_MODULE_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}

				$result = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($detail->getTargetModuleName ()));
				if ($this->adb->num_rows ($result) == 0) {
					$e = new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_INVALID_TARGET_MODULE_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}

				$result = $this->adb->pquery (
					'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
					array ($detail->getTargetModuleName (), $detail->getTargetFieldName ())
				);
				if ($this->adb->num_rows ($result) == 0) {
					$e = new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_INVALID_TARGET_FIELD_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}
			}
		}

		/**
		 * @param DataSharingRule $rule
		 */
		public function deleteRule ($rule) {
			if ((empty ($rule)) || (!($rule instanceof DataSharingRule))) {
				return;
			}

			$ruleId = $rule->getId ();
			if (empty ($ruleId)) {
				return;
			}

			$moduleName = $rule->getModuleName ();
			$identifier = $rule->getName ();
			$this->adb->startTransaction ();
			if ((!empty ($moduleName)) && (!empty ($identifier))) {
				$this->adb->pquery ('DELETE FROM vtiger_deletedelements WHERE elementtype=? AND modulename=? AND identifier=?', array ('datasharingrule', $moduleName, $identifier));
				$this->adb->pquery ('INSERT INTO vtiger_deletedelements (elementtype, modulename, identifier, deletedon, serializedobject) VALUES (?, ?, ?, ?, ?)', array ('datasharingrule', $moduleName, $identifier, date ('Y-m-d h:i:s'), serialize ($rule)));
			}
			$this->adb->pquery ('DELETE FROM vtiger_instancesdatasharing_rulesdetails WHERE ruleid=?', array ($ruleId));
			$this->adb->pquery ('DELETE FROM vtiger_instancesdatasharing_rules WHERE ruleid=?', array ($ruleId));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $moduleName
		 * @param boolean $ignoreLock
		 */
		public function deleteRules ($moduleName, $ignoreLock = true) {
			if (empty ($moduleName)) {
				return;
			}

			if (!$ignoreLock) {
				$whereClause = 'AND locked=0';
			} else {
				$whereClause = '';
			}
			$this->adb->startTransaction ();
			$this->adb->pquery ("DELETE FROM vtiger_instancesdatasharing_rulesdetails WHERE ruleid IN (SELECT ruleid FROM vtiger_instancesdatasharing_rules WHERE modulename=? {$whereClause})", array ($moduleName));
			$this->adb->pquery ("DELETE FROM vtiger_instancesdatasharing_rules WHERE modulename=? {$whereClause}", array ($moduleName));
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $instanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 */
		public function deleteSync ($instanceCode, $moduleName, $recordId) {
			if ((empty ($moduleName)) || (empty ($recordId))) {
				return;
			}

			$this->adb->pquery (
				'DELETE FROM vtiger_instancesdatasharing_syncs WHERE (sourceinstancecode=? AND sourcemodulename=? AND sourcerecordid=?) OR (targetinstancecode=? AND targetmodulename=? AND targetrecordid=?)',
				array ($instanceCode, $moduleName, $recordId, $instanceCode, $moduleName, $recordId)
			);
		}

		/**
		 * @param string $token
		 * @param string $sourceInstanceCode
		 *
		 * @return DataSharingRequest|null
		 */
		public function fetchRequestByToken ($token, $sourceInstanceCode) {
			if ((empty ($token)) || (strlen ($token) < 80)) {
				return null;
			}

			$encodedRequestId = substr ($token, 40, 40);
			$result           = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_requests WHERE SHA1(requestid)=?', array ($encodedRequestId));
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$request = DataSharingRequest::getInstance ()
					->setId ($row ['requestid'])
					->setComments ($row ['comments'])
					->setCreatedBy (UserManager::getInstance ($this->adb, null)->fetchUserById (intval ($row ['createdby'])))
					->setCreationDate (date_create ($row ['creationdate']))
					->setModuleName ($row ['modulename'])
					->setProcessingDate (!empty ($row ['processingdate']) ? date_create ($row ['processingdate']) : null)
					->setRecipientAddress ($row ['recipientaddress'])
					->setRecordIds (json_decode ($row ['recordids']))
					->setRuleId ($row ['ruleid'])
					->setStatus ($row ['status'])
					->setSourceInstanceCode (empty ($sourceInstanceCode) ? self::fetchSourceInstanceCodeByToken ($token) : $sourceInstanceCode);
			} else {
				$request = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $request;
		}

		/**
		 * @param integer $ruleId
		 *
		 * @return DataSharingRule|null
		 */
		public function fetchRuleById ($ruleId) {
			if (empty ($ruleId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE ruleid=?', array ($ruleId));
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$rule = DataSharingRule::getInstance ()
					->setId (intval ($row ['ruleid']))
					->setDetails ($this->fetchRuleDetails ($row ['ruleid']))
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($row ['modulename'])
					->setName ($row ['rulename'])
					->setStatus ($row ['rulestatus']);
			} else {
				$rule = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rule;
		}

		/**
		 * @param string $moduleName
		 * @param string $ruleName
		 *
		 * @return DataSharingRule|null
		 */
		public function fetchRuleByName ($moduleName, $ruleName) {
			if ((empty ($moduleName)) || (empty ($ruleName))) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE modulename=? AND rulename=?', array ($moduleName, $ruleName));
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$rule = DataSharingRule::getInstance ()
					->setId (intval ($row ['ruleid']))
					->setDetails ($this->fetchRuleDetails ($row ['ruleid']))
					->setLocked ($row ['locked'] == 1)
					->setModuleName ($row ['modulename'])
					->setName ($row ['rulename'])
					->setStatus ($row ['rulestatus']);
			} else {
				$rule = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rule;
		}

		/**
		 * @param string|null $moduleName
		 * @param boolean $includeDeleted
		 *
		 * @return DataSharingRule[]|null
		 */
		public function fetchRules ($moduleName, $includeDeleted = false) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE modulename=? ORDER BY rulename', array ($moduleName));
			if ($this->adb->num_rows ($result) > 0) {
				$rules = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$rules [] = DataSharingRule::getInstance ()
						->setId (intval ($row ['ruleid']))
						->setDetails ($this->fetchRuleDetails ($row ['ruleid']))
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($row ['modulename'])
						->setName ($row ['rulename'])
						->setStatus ($row ['rulestatus']);
				}
				if ($includeDeleted) {
					$deletedRules = $this->fetchDeletedRules ($moduleName);
				} else {
					$deletedRules = array ();
				}
				$rules = array_merge ($rules, $deletedRules);
			} else {
				$rules = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $rules;
		}

		/**
		 * @param integer $syncId
		 *
		 * @return DataSharingSync|null
		 */
		public function fetchSync ($syncId) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_syncs WHERE syncid=?', array ($syncId));
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$sync = DataSharingSync::getInstance ()
					->setId (intval ($row ['syncid']))
					->setRuleId ($row ['ruleid'])
					->setSourceEmailAddress ($row ['sourceemailaddress'])
					->setSourceInstanceCode ($row ['sourceinstancecode'])
					->setSourceModuleName ($row ['sourcemodulename'])
					->setSourceRecordId (intval ($row ['sourcerecordid']))
					->setTargetEmailAddress ($row ['targetemailaddress'])
					->setTargetInstanceCode ($row ['targetinstancecode'])
					->setTargetModuleName ($row ['targetmodulename'])
					->setTargetRecordId (intval ($row ['targetrecordid']));
			} else {
				$sync = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $sync;
		}

		/**
		 * @param string $instanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 *
		 * @return DataSharingSync[]|null
		 */
		public function fetchSyncs ($instanceCode, $moduleName, $recordId) {
			if (empty ($moduleName)) {
				return null;
			} else if (!empty ($recordId)) {
				$whereClause = '(sourceinstancecode=? AND sourcemodulename=? AND sourcerecordid=?) OR (targetinstancecode=? AND targetmodulename=? AND targetrecordid=?)';
				$arguments   = array ($instanceCode, $moduleName, $recordId, $instanceCode, $moduleName, $recordId);
			} else {
				$whereClause = '(sourceinstancecode=? AND sourcemodulename=?) OR (targetinstancecode=? AND targetmodulename=?)';
				$arguments   = array ($instanceCode, $moduleName, $instanceCode, $moduleName);
			}

			$result = $this->adb->pquery ("SELECT * FROM vtiger_instancesdatasharing_syncs WHERE {$whereClause}", $arguments);
			if ($this->adb->num_rows ($result) > 0) {
				$syncs = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$syncs [] = DataSharingSync::getInstance ()
						->setId (intval ($row ['syncid']))
						->setRuleId ($row ['ruleid'])
						->setSourceEmailAddress ($row ['sourceemailaddress'])
						->setSourceInstanceCode ($row ['sourceinstancecode'])
						->setSourceModuleName ($row ['sourcemodulename'])
						->setSourceRecordId (intval ($row ['sourcerecordid']))
						->setTargetEmailAddress ($row ['targetemailaddress'])
						->setTargetInstanceCode ($row ['targetinstancecode'])
						->setTargetModuleName ($row ['targetmodulename'])
						->setTargetRecordId (intval ($row ['targetrecordid']));
				}
			} else {
				$syncs = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $syncs;
		}

		/**
		 * @param string $instanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 *
		 * @return integer
		 */
		public function fetchTotalSyncs ($instanceCode, $moduleName, $recordId = null) {
			if (empty ($moduleName)) {
				return null;
			} else if (!empty ($recordId)) {
				$whereClause = '(sourceinstancecode=? AND sourcemodulename=? AND sourcerecordid=?) OR (targetinstancecode=? AND targetmodulename=? AND targetrecordid=?)';
				$arguments   = array ($instanceCode, $moduleName, $recordId, $instanceCode, $moduleName, $recordId);
			} else {
				$whereClause = '(sourceinstancecode=? AND sourcemodulename=?) OR (targetinstancecode=? AND targetmodulename=?)';
				$arguments   = array ($instanceCode, $moduleName, $instanceCode, $moduleName);
			}

			$result    = $this->adb->pquery ("SELECT * FROM vtiger_instancesdatasharing_syncs WHERE {$whereClause} LIMIT 1", $arguments);
			$totalRows = $this->adb->num_rows ($result);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $totalRows;
		}

		/**
		 * @param string $sourceInstanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 * @param string $targetInstanceCode
		 *
		 * @return boolean
		 */
		public function isRecordShared ($sourceInstanceCode, $moduleName, $recordId, $targetInstanceCode) {
			if (empty ($recordId)) {
				return false;
			}

			$result    = $this->adb->pquery (
				'SELECT * FROM vtiger_instancesdatasharing_syncs WHERE sourceinstancecode=? AND sourcemodulename=? AND sourcerecordid=? AND targetinstancecode=? LIMIT 1',
				array ($sourceInstanceCode, $moduleName, $recordId, $targetInstanceCode)
			);
			$totalRows = $this->adb->num_rows ($result);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return ($totalRows > 0);
		}

		/**
		 * @param DataSharingRequest $request
		 * @param array $additionalArguments
		 *
		 * @return array
		 * @throws DataSharingRequestException
		 * @throws PlatformException
		 * @throws PlatformSubscriptionException
		 */
		public function processRequest ($request, $additionalArguments) {
			if ((empty ($request)) || (!($request instanceof DataSharingRequest))) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY);
			} else if (empty ($request->getId ())) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_ID);
			} else if ((!is_array ($additionalArguments)) || (empty ($additionalArguments))) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_ADDITIONAL_ARGUMENTS);
			} else if (empty ($additionalArguments ['serverfornewusers'])) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_USERS_SERVER);
			}
			$this->validateRequest ($request);

			$adbm           = AdbManager::getInstance ();
			$masterAdb      = $adbm->getMasterAdb ();
			$pm             = PlatformManager::getInstance ($masterAdb, $additionalArguments ['serverfornewusers']);
			$targetInstance = $pm->fetchInstanceByUserName ($request->getRecipientAddress (), true);
			if (empty ($targetInstance)) {
				$targetInstance     = $this->createInstance ($pm, $request, $additionalArguments);
				$targetInstanceCode = $targetInstance->getCode ();
			} else {
				$targetInstanceCode = $targetInstance->getCode ();
			}
			$request->setTargetInstanceCode ($targetInstanceCode);

			$targetAdb = $adbm->getTargetInstanceAdb ($targetInstanceCode);
			$module    = ModuleManager::getInstance ($targetAdb)->fetchModule ($request->getModuleName (), true);
			if ((empty ($module)) || (!in_array ($module->getPresence (), array (Module::PRESENCE_USER_DEFINED, Module::PRESENCE_VISIBLE)))) {
				$this->validateRequestAdditionalArguments ($additionalArguments, self::REQUEST_TYPE_INSTALL_APPLICATION);
				$pm->installInstanceApplication ($targetInstanceCode, $additionalArguments ['applicationcode']);
			}

			$processedRecordIds = $this->shareEntities ($request, $targetAdb);
			$this->adb->pquery ('UPDATE vtiger_instancesdatasharing_requests SET status=? WHERE requestid=?', array (DataSharingRequest::STATUS_ACCEPTED, $request->getId ()));
			return $processedRecordIds;
		}

		/**
		 * @param DataSharingRule $rule
		 * @param boolean $ignoreLock
		 *
		 * @return DataSharingRule
		 * @throws DataSharingRuleException
		 * @throws DataSharingRuleDetailException
		 */
		public function saveRule ($rule, $ignoreLock = true) {
			$this->validateRule ($rule);

			$isDeleted = $rule->isDeleted ();
			if ($isDeleted) {
				return $rule;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancesdatasharing_rules WHERE modulename=? AND rulename=?', array ($rule->getModuleName (), $rule->getName ()));
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$ruleId   = intval ($row ['ruleid']);
				$isLocked = ($row ['locked'] == 1);
			} else {
				$ruleId   = null;
				$isLocked = false;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$this->adb->startTransaction ();
			if (empty ($ruleId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_instancesdatasharing_rules (rulename, modulename, rulestatus, locked) VALUES (?, ?, ?, ?)',
					array ($rule->getName (), $rule->getModuleName (), $rule->getStatus (), $rule->isLocked ())
				);
				$rule->setId ($this->adb->getLastInsertID ());
				$this->saveRuleDetails ($rule);
			} else if (($ignoreLock) || (!$isLocked)) {
				$this->adb->pquery (
					'UPDATE vtiger_instancesdatasharing_rules SET rulename=?, modulename=?, rulestatus=?, locked=? WHERE ruleid=?',
					array ($rule->getName (), $rule->getModuleName (), $rule->getStatus (), $rule->isLocked (), $ruleId)
				);
				$rule->setId ($ruleId);
				$this->saveRuleDetails ($rule);
			}
			$this->adb->completeTransaction ();
			return $rule;
		}

		/**
		 * @param string|null $keyword
		 * @param integer|null $page
		 * @param integer|null $recordsPerPage
		 *
		 * @return DataSharingRule[]
		 */
		public function searchRules ($keyword = null, $page = null, $recordsPerPage = null) {
			$whereClauses = array ();
			$arguments    = array ();
			if (!empty ($keyword)) {
				$whereClauses [] = 'rulename LIKE ?';
				$arguments []    = "%{$keyword}%";
				$arguments []    = "%{$keyword}%";
			}
			$whereClause = !empty ($whereClauses) ? 'WHERE ' . join (' AND ', $whereClauses) : '';

			if ((!empty ($recordsPerPage)) && (is_numeric ($recordsPerPage))) {
				$startRecord = (!empty ($page)) && ($page > 0) ? (($page - 1) * $recordsPerPage) : 0;
				$limit       = $recordsPerPage;
				$limitClause = "LIMIT {$startRecord}, {$limit}";
			} else {
				$startRecord = 0;
				$limitClause = '';
			}

			$result = $this->adb->pquery (
				"SELECT
					*
				FROM
					vtiger_instancesdatasharing_rules
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_instancesdatasharing_rules {$whereClause}) AS total
				{$whereClause}
				ORDER BY
					rulename
				{$limitClause}",
				$arguments
			);
			if ($this->adb->num_rows ($result) > 0) {
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$totalRecords = intval ($row ['__total_records__']);
					$records []   = DataSharingRule::getInstance ()
						->setId (intval ($row ['ruleid']))
						->setDetails ($this->fetchRuleDetails ($row ['ruleid']))
						->setLocked ($row ['locked'] == 1)
						->setModuleName ($row ['modulename'])
						->setName ($row ['rulename'])
						->setStatus ($row ['rulestatus']);
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / $recordsPerPage);
			} else {
				$totalRecords = 0;
				$records      = null;
				$endRecord    = 0;
				$totalPages   = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

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
		 * @param DataSharingRequest $request
		 *
		 * @throws DataSharingRequestException
		 */
		public function sendRequest ($request) {
			if ((empty ($request)) || (!($request instanceof DataSharingRequest))) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY);
			}

			$this->validateRequest ($request);
			$this->adb->pquery (
				'INSERT INTO vtiger_instancesdatasharing_requests (recipientaddress, modulename, ruleid, recordids, status, createdby, creationdate, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
				array ($request->getRecipientAddress (), $request->getModuleName (), $request->getRuleId (), json_encode ($request->getRecordIds ()), DataSharingRequest::STATUS_SENT, $request->getCreatedBy ()->getId (), $request->getCreationDate ()->format ('Y-m-d H:i:s'), $request->getComments ())
			);
			$request->setId ($this->adb->getLastInsertID ());
			$encodedSourceInstanceCode = sha1 ($request->getSourceInstanceCode ());
			$encodedRequestId          = sha1 ($request->getId ());
			$token                     = "{$encodedSourceInstanceCode}{$encodedRequestId}";
			try {
				$this->sendEmail ($request, $token);
			} catch (Exception $e) {
				$this->adb->pquery ('DELETE FROM vtiger_instancesdatasharing_requests WHERE requestid=?', array ($request->getId ()));
				throw new DataSharingRequestException ($e->getMessage ());
			}
		}

		/**
		 * @param string $instanceCode
		 * @param string $moduleName
		 * @param integer $recordId
		 */
		public function synchronize ($instanceCode, $moduleName, $recordId) {
			$syncs = $this->fetchSyncs ($instanceCode, $moduleName, $recordId);
			if (empty ($syncs)) {
				return;
			}

			foreach ($syncs as $sync) {
				$ruleMap = $this->fetchSyncRuleMap ($sync, $instanceCode, $moduleName, $recordId);
				if (empty ($ruleMap)) {
					return;
				}

				$sourceEntity = PlatformUtils::getCrmEntity ($this->adb, $sync->getSourceModuleName (), $recordId);
				if (($sync->getSourceInstanceCode () == $instanceCode) && ($sync->getSourceModuleName () == $moduleName) && ($sync->getSourceRecordId () == $recordId)) {
					$targetAdb       = AdbManager::getInstance ()->getTargetInstanceAdb ($sync->getTargetInstanceCode ());
					$targetEntity    = PlatformUtils::getCrmEntity ($targetAdb, $sync->getTargetModuleName (), $sync->getTargetRecordId ());
					$skippableAction = DataSharingRuleDetail::ACTION_RECEIVE_ONLY;
				} else {
					if (empty ($sync->getSourceInstanceCode ())) {
						$targetAdb = AdbManager::getInstance ()->getMasterAdb ();
					} else {
						$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($sync->getSourceInstanceCode ());
					}
					$targetEntity    = PlatformUtils::getCrmEntity ($targetAdb, $sync->getSourceModuleName (), $sync->getSourceRecordId ());
					$skippableAction = DataSharingRuleDetail::ACTION_SEND_ONLY;
				}
				if ((empty ($sourceEntity->id)) || (empty ($targetEntity->id))) {
					return;
				}
				$this->synchronizeRecord ($targetAdb, $sourceEntity, $targetEntity, $ruleMap, $skippableAction);
			}
		}

		/**
		 * @param DataSharingRequest $request
		 *
		 * @throws DataSharingRequestException
		 */
		public function validateRequest ($request) {
			if ((empty ($request)) || (!($request instanceof DataSharingRequest))) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY);
			}

			$request->validate ();

			$recipientAddress = $request->getRecipientAddress ();
			$user             = UserManager::getInstance ($this->adb, null)->fetchUserByUsername ($recipientAddress);
			if (!empty ($user)) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_SAME_INSTANCE_RECIPIENT);
			}

			$result    = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($request->getModuleName ()));
			$totalRows = $this->adb->num_rows ($result);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ($totalRows == 0) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_INVALID_MODULE_NAME);
			}
		}

		/**
		 * @param string $token
		 *
		 * @return null|string
		 * @throws PlatformException
		 */
		public static function fetchSourceInstanceCodeByToken ($token) {
			if ((empty ($token)) || (strlen ($token) < 40)) {
				return null;
			}

			$encodedSourceInstanceCode = substr ($token, 0, 40);
			if ($encodedSourceInstanceCode == sha1 ('')) {
				return '';
			} else {
				$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
				$instance  = PlatformManager::getInstance ($masterAdb, null)->fetchInstanceByShaOneEncodedCode ($encodedSourceInstanceCode, true);
				return !empty ($instance) ? $instance->getCode () : null;
			}
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return DataSharingManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}
