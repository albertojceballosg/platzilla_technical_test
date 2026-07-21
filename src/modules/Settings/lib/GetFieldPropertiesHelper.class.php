<?php
	require_once ('include/platzilla/Data/FieldGridManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/PicklistRelationshipManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');

	/**
	 * Class GetFieldPropertiesHelper
	 *
	 * Esta clase contiene los metodos para la creación del bloque que contendrá las tablas inteligentes
	 */
	class GetFieldPropertiesHelper {

		/**
		 * Para obtener la visibilidad de la tabla
		 *
		 * @param PearDatabase $adb
		 * @param integer $fieldId
		 *
		 * @return array
		 */
		private static function getFieldVisibility ($adb, $fieldId) {
			$result = $adb->pquery(
				'SELECT 
					p2f.profileid,
					p2f.visible,
					p.profilename,
					p.description
				FROM 
					vtiger_profile2field p2f
				INNER JOIN vtiger_profile p ON p.profileid = p2f.profileid
				WHERE 
					p2f.fieldid=?',
				array ($fieldId)
			);
			$fieldVisibility = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$fieldVisibility [] = array (
						'profileid'   => $row ['profileid'],
						'profilename' => $row ['profilename'],
						'visible'     => $row ['visible'],
						'fieldid'     => $fieldId,
						'title'       => $row ['description'],
					);
				}
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $fieldVisibility;
		}

		/**
		 * Para obtener resumen de los campos que componen la tabla
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $row
		 * @param array $fields
		 *
		 * @throws Exception
		 */
		private static function getSummaryGridFields (PearDatabase $adb, $moduleName, $row, &$fields) {
			if (!$fieldsGrid = FieldGridManager::getInstance ($adb)->fetchFieldGrid ($moduleName, $row ['fieldname'])) {
				return;
			};

			foreach ($fieldsGrid as $field) {
				if ($field->getUiType () != FieldInterface::UI_TYPE_SUMMARY_ROW) {
					continue;
				}
				$summaryConfig = unserialize (base64_decode ($field->getDataField ()));
				$summaryFields = array_column ($summaryConfig, 'field');

				foreach ($summaryFields as $column) {
					if($column != 'false') {
						$dummy = explode ('_', $column);
						array_pop ($dummy);
						$columnName  = join ('_', $dummy);
						$label       = 'Tabla: '. getTranslatedString ($row ['fieldlabel'], $moduleName) . '. Columna: ' . $field->getLabel () . ' (' . ucfirst ($columnName) . ')';
						$fields      = array_merge ($fields, array ($column => $label));
					}
				}
			}
		}

		/**
		 * Para obtener datos de los campos disponibles
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return string[]|null
		 * @throws Exception
		 */
		public static function getAvailableFieldsData (PearDatabase $adb, $moduleName) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT * FROM vtiger_field WHERE tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)',
				array ($moduleName)
			);
			if (($result) || ($adb->num_rows ($result) > 0)) {
				$fields = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['uitype'] == 2202) {
						self::getSummaryGridFields ($adb, $moduleName, $row, $fields);
					} else {
						$fields [ $row ['fieldname'] ] = getTranslatedString ($row ['fieldlabel'], $moduleName);
					}
				}
				asort ($fields);
			} else {
				$fields = null;
			}
			return $fields;
		}

		/**
		 * Para obtener los roles disponibles
		 *
		 * @param PearDatabase $adb
		 *
		 * @return array
		 */
		public static function getAvailableRoles (PearDatabase $adb) {
			$availableRoles = RoleManager::getInstance ($adb)->fetchRoles (true);
			if (!empty ($availableRoles)) {
				$roles = array ();
				foreach ($availableRoles as $availableRole) {
					$roles [ $availableRole->getId () ] = $availableRole->getName ();
				}
			} else {
				$roles = null;
			}
			return $roles;
		}

		/**
		 * Para obtener los campos
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return Field|null
		 */
		public static function getField (PearDatabase $adb, $moduleName, $fieldName) {
			$fm = FieldManager::getInstance ($adb);
			$field = $fm->fetchFieldByName ($moduleName, $fieldName);
			return $field;
		}

		/**
		 * Para obtener las propiedades basicas del campo
		 *
		 * @param PearDatabase $adb
		 * @param Field $field
		 *
		 * @return array
		 */
		public static function getFieldBasicProperties ($adb, $field) {
			$properties = array (
				'calculationid' => $field->getCalculationName (),
				'defaultvalue'  => $field->getDefaultValue (),
				'id'            => $field->getId (),
				'ismandatory'   => $field->isMandatory (),
				'label'         => getTranslatedString ($field->getLabel (), $field->getModuleName ()),
				'length'        => $field->getLength (),
				'name'          => $field->getName (),
				'precision'     => $field->getPrecision (),
				'presence'      => $field->getPresence (),
				'uitype'        => $field->getUiType (),
				'blockId'       => $field->getBlockId (),
				'visibility'    => self::getFieldVisibility ($adb, $field->getId ()),
			);
			return $properties;
		}

		/**
		 * Para obtener los valores disponible del campo picklist
		 *
		 * @param Field $field
		 *
		 * @return array|null
		 */
		public static function getFieldPicklistValues ($field) {
			$picklist       = $field->getPicklist ();
			$picklistValues = $picklist->getValues ();
			if (empty ($picklistValues)) {
				return null;
			}

			$values = array ();
			foreach ($picklistValues as $picklistValue) {
				$picklistValueRoles = $picklistValue->getRoles ();
				if (!empty ($picklistValueRoles)) {
					$roleIds = array ();
					foreach ($picklistValueRoles as $picklistValueRole) {
						$roleIds [] = $picklistValueRole->getId ();
					}
				} else {
					$roleIds = null;
				}

				$values [ $picklistValue->getValue () ] = array (
					'id'       => $picklistValue->getId (),
					'presence' => $picklistValue->getPresence (),
					'roles'    => $roleIds,
					'value'    => $picklistValue->getValue (),
				);
			}
			return $values;
		}

		/**
		 * Para obtener los valores del campo Pipeline
		 *
		 * @param Field $field
		 *
		 * @return array|null
		 */
		public static function getFieldPipelineValues ($field) {
			$pipeline = $field->getPipeline ();
			if (empty ($pipeline)) {
				return null;
			} else {
				return $pipeline->getValues ();
			}
		}

		/**
		 * Para obtener las propiedades del campo de referencia a modulo
		 *
		 * @param Field $field
		 *
		 * @return string|null
		 */
		public static function getFieldReferencedModuleProperties ($field) {
			$references = $field->getModuleReferences ();
			if (empty ($references)) {
				return null;
			}

			$referencedModuleName          = $references [0]->getReferencedModuleName ();
			$referencedModuleRelationships = $references [0]->getRelationships ();
			if (!empty ($referencedModuleRelationships)) {
				$relationships = array ();
				foreach ($referencedModuleRelationships as $referencedModuleRelationship) {
					$relationships [ $referencedModuleRelationship->getReferencedFieldName () ] = $referencedModuleRelationship->getFieldName ();
				}
			} else {
				$relationships = null;
			}

			$referencedModuleFilters = $references [0]->getFilters ();
			if (!empty ($referencedModuleFilters)) {
				$filters = array ();
				foreach ($referencedModuleFilters as $referencedModuleFilter) {
					$filters [] = array (
						'comparator'      => $referencedModuleFilter->getComparator (),
						'field'           => $referencedModuleFilter->getFieldName (),
						'operator'        => $referencedModuleFilter->getOperator (),
						'value'           => $referencedModuleFilter->getValue (),
						'valuemodulename' => $referencedModuleFilter->getValueModuleName (),
						'valuetype'       => $referencedModuleFilter->getValueType (),
					);
				}
			} else {
				$filters = null;
			}

			return array (
				'name'          => $referencedModuleName,
				'filters'       => $filters,
				'relationships' => $relationships,
			);
		}

		/**
		 * Para obtener las validaciones del campo
		 *
		 * @param Field $field
		 *
		 * @return array
		 */
		public static function getFieldValidations ($field) {
			$fieldValidations = $field->getValidations ();
			if (empty ($fieldValidations)) {
				return null;
			}

			$validations = array ();
			foreach ($fieldValidations as $fieldValidation) {
				$validationType = $fieldValidation->getType ();
				if ($validationType == FieldValidationInterface::VALIDATION_TYPE_UNIQUE) {
					$validations ['unique'] = true;
				} else if ($validationType == FieldValidationInterface::VALIDATION_TYPE_DATE) {
					$validations ['date']['initialvalue'] = $fieldValidation->getInitialValue ();
					$validations ['date']['maximumvalue'] = $fieldValidation->getMaximumValue ();
				} else if ($validationType == FieldValidationInterface::VALIDATION_TYPE_NUMBER) {
					$validations ['number']['initialvalue'] = $fieldValidation->getInitialValue ();
					$validations ['number']['maximumvalue'] = $fieldValidation->getMaximumValue ();
				}
			}
			return $validations;
		}

		/**
		 * Para obtener los campos de la tabla
		 *
		 * @param Field $field
		 *
		 * @return array|null
		 */
		public static function getGridFields ($field) {
			$uiType = $field->getUiType ();
			if ($uiType != FieldInterface::UI_TYPE_GRID) {
				return null;
			}

			$fieldId      = $field->getId ();
			$listSubField = obtieneListaSubCamposCampoGrid ($fieldId, false, false);
			searchByActionInGrid ($listSubField);
			searchByFilterInGrid ($listSubField);
			searchBySummaryRow ($listSubField);
			$totalFields = count ($listSubField);
			for ($k = 0; $k < $totalFields; $k++) {
				if ($listSubField[ $k ]['uitype'] == '2203') {
					$listSubField[ $k ]['data_field'] = json_encode ($listSubField[ $k ]['data_field']);
					break;
				}
			}
			return $listSubField;
		}

		/**
		 * Para obtener los campos del modulo
		 *
		 * @param PearDatabase $adb
		 * @param Field $field
		 *
		 * @return array
		 */
		public static function getModuleFields (PearDatabase $adb, $field) {
			$fieldDependencies = $field->getDependencies ();
			if (!empty ($fieldDependencies)) {
				$hiddenFieldValues  = array ();
				$visibleFieldValues = array ();
				foreach ($fieldDependencies as $fieldDependency) {
					if ($fieldDependency->getTargetFieldVisibility () == FieldDependencyInterface::VISIBILITY_HIDDEN) {
						$hiddenFieldValues [ $fieldDependency->getTargetFieldName () ][] = $fieldDependency->getSourceFieldValue ();
					} else if ($fieldDependency->getTargetFieldVisibility () == FieldDependencyInterface::VISIBILITY_VISIBLE) {
						$visibleFieldValues [ $fieldDependency->getTargetFieldName () ][] = $fieldDependency->getSourceFieldValue ();
					}
				}
			} else {
				$hiddenFieldValues  = null;
				$visibleFieldValues = null;
			}

			// Obtener el resto de campos del módulo junto con la información de visibilidad según las dependencias
			$moduleName   = $field->getModuleName ();
			$moduleFields = FieldManager::getInstance ($adb)->fetchFields ($moduleName);
			$fields       = array ();
			foreach ($moduleFields as $moduleField) {
				$moduleFieldName = $moduleField->getName ();
				if ($moduleField == $field->getName ()) {
					continue;
				}

				$fields [ $moduleFieldName ] = array (
					'label'       => getTranslatedString ($moduleField->getLabel (), $moduleFieldName),
					'hiddenfor'   => (!empty ($hiddenFieldValues)) && (isset ($hiddenFieldValues [ $moduleFieldName ])) ? $hiddenFieldValues [ $moduleFieldName ] : null,
					'ismandatory' => $moduleField->isMandatory (),
					'visiblefor'  => (!empty ($visibleFieldValues)) && (isset ($visibleFieldValues [ $moduleFieldName ])) ? $visibleFieldValues [ $moduleFieldName ] : null,
				);
			}
			uasort (
				$fields,
				function ($fieldA, $fieldB) {
					return strcmp ($fieldA ['label'], $fieldB ['label']);
				}
			);
			return $fields;
		}

		/**
		 * Para Obtener los Pikclist por Modulo
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $motherPicklist
		 *
		 * @return array|null
		 */
		public static function getPickListByModule ($adb, $moduleName, $motherPicklist) {
			if (empty($moduleName) || empty($motherPicklist)) {
				return null;
			}
			$availablePicklist  = PicklistRelationshipManager::getInstance($adb)->fetchPicklistByModule ($moduleName, $motherPicklist);
			if (!empty ($availablePicklist)) {
				$totalPicklist = count($availablePicklist);
				for ($k= 0; $k < $totalPicklist; $k++) {
					if (!empty ($availablePicklist[ $k ]['values'])) {
						$availablePicklist[ $k ]['values'] = json_encode ($availablePicklist[ $k ]['values']);
					}
				}
			}
			return $availablePicklist;
		}

		/**
		 * Para obtener las relaciones de los picklist
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $motherPicklist
		 *
		 * @return null|PicklistRelationship[]
		 */
		public static function getPicklistRelationship ($adb, $moduleName, $motherPicklist) {
			if (empty($moduleName) || empty($motherPicklist)) {
				return null;
			}

			$relationship = PicklistRelationshipManager::getInstance($adb)->fetchPicklistRelationshipByModule ($moduleName, $motherPicklist);
			if (!empty($relationship)) {
				$resultsArray = array ();
				foreach ($relationship as $relation) {
					$resultsArray[ $relation->getMotherPicklistName() ]['relationname'] = $relation->getRelationshipName ();
					$resultsArray[ $relation->getMotherPicklistName() ]['daughter']     = $relation->getDaughterPicklistName();
					$relationsMaster                                                    = $relation->getPicklistRelationshipMaster();

					if (!empty ($relationsMaster)) {
						foreach ($relationsMaster as $relationMaster) {
							$resultsArray [ $relation->getMotherPicklistName() ]['relation'] [$relationMaster->getMotherPicklistValueId ()] = $relationMaster->getDaughterPicklistValuesId();
						}
					}
				}
				return $resultsArray;
			} else {
				return null;
			}
		}

		/**
		 * Obtiene los campos pipeline del módulo
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param boolean $excludeRelated Excluir pipelines que ya tienen relación con picklists
		 * @param string|null $currentPicklist Nombre del picklist actual (para excluir su relación actual del filtrado)
		 *
		 * @return array|null
		 */
		public static function getPipelineFields ($adb, $moduleName, $excludeRelated = true, $currentPicklist = null) {
			if (empty($moduleName)) {
				return null;
			}

			$result = $adb->pquery(
				'SELECT fieldname, fieldlabel 
				 FROM vtiger_field 
				 WHERE tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?)
				 AND uitype = ?',
				array($moduleName, FieldInterface::UI_TYPE_PIPELINE)
			);

			if ($adb->num_rows($result) > 0) {
				$fields = array();
				while ($row = $adb->fetchByAssoc($result, -1, false)) {
					// Obtener valores del pipeline
					$pipelineValues = self::getPipelineValues($adb, $moduleName, $row['fieldname']);
					
					$fields[] = array(
						'fieldname' => $row['fieldname'],
						'fieldlabel' => getTranslatedString($row['fieldlabel'], $moduleName),
						'values' => $pipelineValues
					);
				}
				
				// Filtrar pipelines que ya tienen relación con otros picklists
				if ($excludeRelated && !empty($fields)) {
					require_once ('include/platzilla/Managers/PicklistPipelineRelationshipManager.php');
					$manager = PicklistPipelineRelationshipManager::getInstance ($adb);
					$relatedPipelines = $manager->getPipelinesWithRelationship ($moduleName);
					
					if (!empty ($relatedPipelines)) {
						$fields = array_filter ($fields, function ($field) use ($relatedPipelines, $currentPicklist, $manager, $moduleName) {
							$isRelatedToCurrent = $currentPicklist && $manager->isPipelineRelated ($moduleName, $field ['fieldname'], $currentPicklist);
							$isInRelated = in_array ($field ['fieldname'], $relatedPipelines);
							
							// Si es el pipeline actualmente relacionado con este picklist, mantenerlo
							if ($isRelatedToCurrent) {
								return true;
							}
							// Excluir si tiene relación con otro picklist
							return !$isInRelated;
						});
						
						// Re-indexar array
						$fields = array_values ($fields);
					}
				}
				
				return $fields;
			}
			return null;
		}

		/**
		 * Para obtener las relaciones Picklist → Pipeline
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $motherPicklist
		 *
		 * @return array|null
		 */
		public static function getPicklistPipelineRelationship ($adb, $moduleName, $motherPicklist) {
			require_once ('include/platzilla/Managers/PicklistPipelineRelationshipManager.php');

			if (empty($moduleName) || empty($motherPicklist)) {
				return null;
			}

			// Usar el nuevo manager para obtener las relaciones
			$manager = PicklistPipelineRelationshipManager::getInstance ($adb);
			$relationships = $manager->fetchPicklistPipelineRelationshipByModule ($moduleName, $motherPicklist);

			if (empty ($relationships)) {
				return null;
			}

			// Obtener el nombre del pipeline y sus valores
			$pipelineFieldName = $relationships[0]['pipelinefieldname'];
			$pipelineValues = self::getPipelineValues($adb, $moduleName, $pipelineFieldName);

			// Parsear relaciones
			$relationshipData = array();
			foreach ($relationships as $row) {
				$motherValue = $row['motherlistvalue']; // Usar directamente el valor de texto
				$visiblePipelineValues = json_decode($row['pipelinevaluesvisible'], true);

				if ($motherValue && is_array($visiblePipelineValues)) {
					// Calcular valores ocultos
					$hiddenValues = array_diff($pipelineValues, $visiblePipelineValues);

					$relationshipData[$motherValue] = array(
						'visible' => array_values($visiblePipelineValues),
						'hidden' => array_values($hiddenValues),
						'all' => $pipelineValues
					);
				}
			}

			return array(
				'relationshipname' => $relationships[0]['relationshipname'],
				'pipelinename' => $pipelineFieldName,
				'relationships' => $relationshipData
			);
		}

		/**
		 * Método auxiliar para obtener valores de picklist por ID
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return array
		 */
		private static function getPicklistValuesById ($adb, $moduleName, $fieldName) {
			$result = $adb->pquery(
				'SELECT picklist_valueid, picklist_value 
				 FROM vtiger_' . $fieldName,
				array()
			);
			
			$values = array();
			if ($adb->num_rows($result) > 0) {
				while ($row = $adb->fetchByAssoc($result, -1, false)) {
					$values[$row['picklist_valueid']] = $row['picklist_value'];
				}
			}
			return $values;
		}

		/**
		 * Método auxiliar para obtener valores de pipeline
		 *
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return array
		 */
		private static function getPipelineValues ($adb, $moduleName, $fieldName) {
			$result = $adb->pquery(
				'SELECT `values` FROM vtiger_pipelines
				 WHERE modulename = ? AND fieldname = ?',
				array($moduleName, $fieldName)
			);

			if ($adb->num_rows($result) > 0) {
				$valuesJson = $adb->query_result($result, 0, 'values');
				// Decode HTML entities before JSON decode
				$valuesJson = html_entity_decode($valuesJson, ENT_QUOTES, 'UTF-8');
				$values = json_decode($valuesJson, true);
				return is_array($values) ? $values : array();
			}
			return array();
		}

	}
