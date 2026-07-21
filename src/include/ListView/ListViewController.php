<?php
	/*+*******************************************************************************
	 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
	 * ("License"); You may not use this file except in compliance with the License
	 * The Original Code is:  vtiger CRM Open Source
	 * The Initial Developer of the Original Code is vtiger.
	 * Portions created by vtiger are Copyright (C) vtiger.
	 * All Rights Reserved.
	 *
	 *********************************************************************************/
	require_once ('include/platzilla/Utils/ListViewUtils.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once 'modules/CustomView/CustomView.php';
	require_once ('modules/CustomView/lib/CustomViewHelper.class.php');
	/**
	 * Description of ListViewController
	 *
	 * @author MAK
	 */
	class ListViewController {
		/**
		 *
		 * @var QueryGenerator
		 */
		private $queryGenerator;
		/**
		 *
		 * @var PearDatabase
		 */
		private $db;
		private $nameList;
		private $typeList;
		private $ownerNameList;
		private $user;
		private $picklistValueMap;
		private $picklistRoleMap;
		private $headerSortingEnabled;
		private $lastCrmId;
		private $lastGridValue;
		private $lastGeidField;

		public function __construct ($db, $user, $generator) {
			$this->queryGenerator       = $generator;
			$this->db                   = $db;
			$this->user                 = $user;
			$this->nameList             = array ();
			$this->typeList             = array ();
			$this->ownerNameList        = array ();
			$this->picklistValueMap     = array ();
			$this->picklistRoleMap      = array ();
			$this->headerSortingEnabled = true;
			$this->lastCrmId            = 0;
			$this->lastGeidField        = '';
			$this->lastGridValue        = 0;
		}
		
		private function sanitizeString ($string) {
			if (!is_scalar ($string)) {
				return $string;
			}
			
			$string = str_replace (
				array ('á', 'á', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$string
			);
			$string = str_replace (
				array ('é', 'é', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$string
			);
			$string = str_replace (
				array ('í', 'í', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$string
			);
			$string = str_replace (
				array ('ó', 'ó', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$string
			);
			$string = str_replace (
				array ('ú', 'ú', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$string
			);
			$string = str_replace (
				array ('ñ', 'Ñ', 'ç', 'Ç'),
				array ('n', 'N', 'c', 'C'),
				$string
			);
			return $string;
		}

		private function getValueFromGrid ($gridName, $gridColmnName, $module, $crmId){
			if (($crmId == $this->lastCrmId) && ($gridColmnName == $this->lastGeidField)) {
				return $this->lastGridValue;
			}
			$this->lastCrmId     = $crmId;
			$this->lastGeidField = $gridColmnName;
			$dummy               = explode ('_', $gridColmnName);
			array_pop ($dummy);
			$gridFieldName     = join ('_', $dummy);
			$gridRow = GridFieldUtils::getGridValues ($this->db, $module, $gridName, $crmId, true);
			$this->lastGridValue = $gridRow['summary'][ $gridFieldName ];
			return $this->lastGridValue;
		}

		/**
		 * @param array $periodData
		 * @param integer $crmId
		 * @param string $fieldIndex
		 *
		 * @return boolean
		 * @throws Exception
		 */
		private function isOnThePeriod ($periodData, $crmId, $fieldIndex) {
			$customView = new CustomView ();
			$field      = explode (':', $periodData ['columnname']);
			$query      = "SELECT * FROM {$field [0]} ";
			if ($periodData ['comparator'] == 'custom' || $periodData['comparator'] == '') {
				if ($periodData ['startdate'] != '0000-00-00' && $periodData['startdate'] != '') {
					$startDate = $periodData['startdate'];
				}
				if ($periodData ['enddate'] != '0000-00-00' && $periodData ['enddate'] != '') {
					$endDate = $periodData ['enddate'];
				}
			} else {
				$datefilter = $customView->getDateforStdFilterBytype ($periodData ['comparator']);
				$startDate  = $datefilter [0];
				$endDate    = $datefilter [1];
			}
			if (!isset ($startDate) || !isset ($endDate)) {
				return false;
			}
			$where    = ($field [0] == 'vtiger_crmentity') ? "WHERE crmid = {$crmId}" : "WHERE {$fieldIndex} = {$crmId}";
			$where   .= " AND ({$field [1]}  BETWEEN '{$startDate}' AND '{$endDate}')";
			$query    .= $where;
			$inPeriod = $this->db->run_query_allrecords ($query);
			return (!empty ($inPeriod)) ? true : false;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $crmId
		 * @return boolean
		 */
		private function isWorkCompleted ($adb, $crmId) {
			if (empty ($crmId)) {
				return true;
			}
			$isWorkCompleted = false;
			$result = $adb->pquery (
				"SELECT
       				orden_de_trabajoid
				FROM
				    vtiger_orden_de_trabajo
				WHERE
				    orden_de_trabajoid=? AND
				      estado_de_la_orden IN ('Terminado','Cancelado')",
				array ($crmId)
			);
			if ($adb->num_rows ($result) > 0) {
				$isWorkCompleted = true;
			}
			return $isWorkCompleted;
		}
		
		public function isHeaderSortingEnabled () {
			return $this->headerSortingEnabled;
		}

		public function setHeaderSorting ($enabled) {
			$this->headerSortingEnabled = $enabled;
		}

		public function setupAccessiblePicklistValueList ($name) {
			$isRoleBased                    = vtws_isRoleBasedPicklist ($name);
			$this->picklistRoleMap[ $name ] = $isRoleBased;
			if ($this->picklistRoleMap[ $name ]) {
				$this->picklistValueMap[ $name ] = getAssignedPicklistValues ($name, $this->user->roleid, $this->db);
			}
		}

		/**
		 * Verifica si un valor de picklist existe en la tabla de valores del picklist
		 * @param string $fieldName - nombre del campo picklist
		 * @param string $value - valor a verificar
		 * @return boolean - true si el valor existe en la tabla, false si no
		 */
		private function picklistValueExists ($fieldName, $value) {
			if (empty($value) || empty($fieldName)) {
				return false;
			}
			
			$tableName = 'vtiger_' . $this->db->sql_escape_string($fieldName);
			$columnName = $this->db->sql_escape_string($fieldName);
			
			// Verificar si la tabla existe
			$tableExists = $this->db->pquery("SHOW TABLES LIKE ?", array($tableName));
			if ($this->db->num_rows($tableExists) == 0) {
				return false;
			}
			
			// Verificar si el valor existe en la tabla
			$sql = "SELECT COUNT(*) as count FROM {$tableName} WHERE {$columnName} = ?";
			$result = $this->db->pquery($sql, array($value));
			
			if ($result && $this->db->num_rows($result) > 0) {
				$count = $this->db->query_result($result, 0, 'count');
				return ($count > 0);
			}
			
			return false;
		}


		public function fetchNameList ($field, $result) {
			$referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList ();
			$fieldName              = $field->getFieldName ();
			$rowCount               = $this->db->num_rows ($result);

			$idList = array ();
			for ($i = 0; $i < $rowCount; $i++) {
				$id = $this->db->query_result ($result, $i, $field->getColumnName ());
				if (!isset($this->nameList[ $fieldName ][ $id ])) {
					$idList[ $id ] = $id;
				}
			}
			$idList = array_keys ($idList);
			if (count ($idList) == 0) {
				return;
			}
			$moduleList = $referenceFieldInfoList[ $fieldName ];
			foreach ($moduleList as $module) {
				$meta = $this->queryGenerator->getMeta ($module);
				if ($meta->isModuleEntity ()) {
					if ($module == 'Users') {
						$nameList = getOwnerNameList ($idList);
					} else {
						//TODO handle multiple module names overriding each other.
						$nameList = getEntityName ($module, $idList);
					}
				} else {
					$nameList = vtws_getActorEntityName ($module, $idList);
				}
				$entityTypeList = array_intersect (array_keys ($nameList), $idList);
				foreach ($entityTypeList as $id) {
					$this->typeList[ $id ] = $module;
				}
				if (empty($this->nameList[ $fieldName ])) {
					$this->nameList[ $fieldName ] = array ();
				}
				foreach ($entityTypeList as $id) {
					$this->typeList[ $id ]               = $module;
					$this->nameList[ $fieldName ][ $id ] = $nameList[ $id ];
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param integer $userId
		 *
		 * @return null|string
		 * @throws Exception
		 */
		public function getDefaultListViewByUser ($adb, $moduleName, $userId) {
			if (empty ($moduleName) || empty ($userId)) {
				return null;
			}
			return ListViewUtils::getDefaultListViewByUser ($adb, $moduleName, $userId);
		}

		/**
		 * @param CRMEntity $focus
		 * @param string $module
		 * @param ADORecordSet $result
		 * @param array $navigationInfo
		 * @param boolean $skipActions
		 * @param null $colorCondition
		 * @param null $resultAll
		 *
		 * @return array
		 * @throws Exception
		 */
		public function getListViewEntries ($focus, $module, $result, $navigationInfo, $skipActions = false, $colorCondition = null, $resultAll = null) {
			$local_user = clone $this->user;
			require_once ('user_privileges/user_privileges.php');

			global $adb, $listview_max_textlength, $theme, $default_charset, $demoMode, $root_directory;
			$fields               = $this->queryGenerator->getFields ();
			$meta                 = $this->queryGenerator->getMeta ($this->queryGenerator->getModule ());
			$gridFields           = $this->queryGenerator->getSummaryLabels ();
			$girdColumns          = array_keys ($gridFields);
			$baseTable            = $meta->getEntityBaseTable();
			$moduleTableIndexList = $meta->getEntityTableIndexList();
			$fieldIndex           = $moduleTableIndexList [ $baseTable ];
			$moduleFields         = $meta->getModuleFields ();
			$accessibleFieldList  = array_keys ($moduleFields);
			$listViewFields       = array_intersect ($fields, $accessibleFieldList);
			$numberingHelper      = NumberHelper::getInstance ($adb);
			
			$referenceFieldList = $this->queryGenerator->getReferenceFieldList ();

			foreach ($referenceFieldList as $fieldName) {
				if (in_array ($fieldName, $listViewFields)) {
					$field = $moduleFields[ $fieldName ];
					$this->fetchNameList ($field, $result);
				}
			}
			$db             = PearDatabase::getInstance ();
			$rowCount       = $db->num_rows ($result);
			$ownerFieldList = $this->queryGenerator->getOwnerFieldList ();

			foreach ($ownerFieldList as $fieldName) {
				if (in_array ($fieldName, $listViewFields)) {
					$field  = $moduleFields[ $fieldName ];
					$idList = array ();
					for ($i = 0; $i < $rowCount; $i++) {
						$id = $this->db->query_result ($result, $i, $field->getColumnName ());
						if (!isset($this->ownerNameList[ $fieldName ][ $id ])) {
							$idList[] = $id;
						}
					}
					if (count ($idList) > 0) {
						if (!is_array ($this->ownerNameList[ $fieldName ])) {
							$this->ownerNameList[ $fieldName ] = getOwnerNameList ($idList);
						} else {
							//array_merge API loses key information so need to merge the arrays
							// manually.
							$newOwnerList = getOwnerNameList ($idList);
							foreach ($newOwnerList as $id => $name) {
								$this->ownerNameList[ $fieldName ][ $id ] = $name;
							}
						}
					}
				}
			}

			foreach ($listViewFields as $fieldName) {
				$field = $moduleFields[ $fieldName ];
				if (!$is_admin && ($field->getFieldDataType () == 'picklist' || $field->getFieldDataType () == 'multipicklist')) {
					$this->setupAccessiblePicklistValueList ($fieldName);
				}
			}

			// Cache de relaciones Picklist->Pipeline del módulo (vtiger_picklist2pipeline)
			// Indexado por pipelinefieldname para filtrar opciones del pipeline según el
			// valor del picklist madre en cada fila.
			require_once ('include/platzilla/Managers/PicklistPipelineRelationshipManager.php');
			$picklistPipelineCache = array ();
			$p2pRelationships = PicklistPipelineRelationshipManager::getInstance ($adb)
				->fetchPicklistPipelineRelationshipByModule ($module);
			if (!empty ($p2pRelationships)) {
				foreach ($p2pRelationships as $rel) {
					$pipelineField = $rel ['pipelinefieldname'];
					$motherField   = $rel ['motherpicklistname'];
					$motherValue   = $rel ['motherlistvalue'];
					$visible       = !empty ($rel ['pipelinevaluesvisible']) ? json_decode ($rel ['pipelinevaluesvisible'], true) : array ();
					if (!isset ($picklistPipelineCache [ $pipelineField ])) {
						$motherColumn = isset ($moduleFields [ $motherField ]) ? $moduleFields [ $motherField ]->getColumnName () : $motherField;
						$picklistPipelineCache [ $pipelineField ] = array (
							'motherField'   => $motherField,
							'motherColumn'  => $motherColumn,
							'byMotherValue' => array (),
						);
					}
					$picklistPipelineCache [ $pipelineField ]['byMotherValue'][ $motherValue ] = is_array ($visible) ? $visible : array ();
				}
			}

			$data         = array ();
			// Obtener nombres de columnas usando FetchField() de ADOdb
			$resultFields = array();
			if ($rowCount > 0) {
				$numFields = $result->FieldCount();
				for ($f = 0; $f < $numFields; $f++) {
					$fieldObj = $result->FetchField($f);
					if ($fieldObj) {
						$resultFields[$fieldObj->name] = $f;
					}
				}
			} else {
				$resultFields = null;
			}
			for ($i = 0; $i < $rowCount; ++$i) {
				try {
				//Getting the recordId
				$isRemovable = true;
				$modalTitle = $module;
				if ($module != 'Users') {
					$baseTable            = $meta->getEntityBaseTable ();
					$moduleTableIndexList = $meta->getEntityTableIndexList ();
					$baseTableIndex       = strtolower ($moduleTableIndexList[ $baseTable ]);

					$recordId = $db->query_result ($result, $i, $baseTableIndex);
					$ownerId  = $db->query_result ($result, $i, "smownerid");
				} else {
					$recordId = $db->query_result ($result, $i, "id");
				}
				$rawRow           = array ();
				$row              = array ();
				$girdColumnsFound = array ();
				if (!empty ($result->Fields ('smownerid')) && !in_array ('imagename', $listViewFields)) {
					$listViewFields[] = 'imagename';
				}
				
				foreach ($listViewFields as $fieldName) {
					/** @var WebserviceField $field */
					if ($fieldName == 'imagename') {
						continue;
					}
					$field    = $moduleFields[ $fieldName ];
					$uitype   = $field->getUIType ();
					$gridFieldName = '';
					$rawValue = $this->db->query_result ($result, $i, $field->getColumnName ());
					// resultFields ya está inicializado antes del loop con FetchField()
					if ($module == 'Calendar') {
						$activityType = $this->db->query_result ($result, $i, 'activitytype');
					}

					if (($uitype != 8) && ($uitype != 2202)) {
						$value = html_entity_decode ($rawValue, ENT_QUOTES, $default_charset);
					} else {
						$value = $rawValue;
					}

					if ((in_array ($uitype, array ('7', '9', '71', '2206')))) {
						$value = $numberingHelper->setNumberFormat ($value, $field->getFieldName ());
					} else if ($field->getUIType () == 8192) {
						require_once ('include/platzilla/Managers/PipelineManager.php');
						$pipeline = PipelineManager::getInstance ($adb)->fetchPipeline ($module, $fieldName);
						if (!empty ($pipeline)) {
							$choices = $pipeline->getValues ();

							// Aplicar dependencia picklist->pipeline si está configurada
							// para este campo: filtrar $choices según el valor del picklist
							// madre en la fila actual.
							if (isset ($picklistPipelineCache [ $fieldName ])) {
								$motherColumn   = $picklistPipelineCache [ $fieldName ]['motherColumn'];
								$motherValueRaw = $this->db->query_result ($result, $i, $motherColumn);
								// Los picklists en las tablas del módulo se guardan con entidades
								// HTML (ej. "Campa&ntilde;a"), mientras que vtiger_picklist2pipeline
								// los guarda en UTF-8 plano. Decodificar para que coincidan.
								$motherValueDec = html_entity_decode ($motherValueRaw, ENT_QUOTES, $default_charset);
								$byMotherVal    = $picklistPipelineCache [ $fieldName ]['byMotherValue'];
								$allowed        = null;
								if (!empty ($motherValueDec) && isset ($byMotherVal [ $motherValueDec ])) {
									$allowed = $byMotherVal [ $motherValueDec ];
								} else if (!empty ($motherValueRaw) && isset ($byMotherVal [ $motherValueRaw ])) {
									$allowed = $byMotherVal [ $motherValueRaw ];
								}
								if (is_array ($allowed)) {
									$filtered = array ();
									foreach ($choices as $choice) {
										if (in_array ($choice, $allowed, true)) {
											$filtered [] = $choice;
										}
									}
									$choices = $filtered;
								}
							}

							$selectedChoicePosition = array_search ($value, $choices);
							$selectedChoicePosition = $selectedChoicePosition !== false ? $selectedChoicePosition : -1;
							$dummy                  = array ();
							$totalChoices           = count ($choices);
							foreach ($choices as $index => $choice) {
								$isSelected   = $selectedChoicePosition >= $index ? ' selected' : '';
								$dummy []     = "<button type=\"button\" class=\"pipeline-element{$isSelected}\" style=\"width: calc((100% - 16px) / {$totalChoices});\" title=\"{$choice}\" disabled=\"disabled\"></button>";
							}
							$value = '<div class="pipeline-chart">' . join ('', $dummy) . '</div>';
						}
					} else if ($module == 'Documents' && $fieldName == 'filename') {
						$fileicon     = "";
						$downloadtype = $db->query_result ($result, $i, 'filelocationtype');
						if ($downloadtype == 'I') {
							$ext = substr ($value, strrpos ($value, ".") + 1);
							$ext = strtolower ($ext);
							if ($value != '') {
								if ($ext == 'bin' || $ext == 'exe' || $ext == 'rpm') {
									$fileicon = "<img src='" . vtiger_imageurl ('fExeBin.gif', $theme) .
												"' hspace='3' align='absmiddle' border='0'>";
								} elseif ($ext == 'jpg' || $ext == 'gif' || $ext == 'bmp') {
									$fileicon = "<img src='" . vtiger_imageurl ('fbImageFile.gif', $theme) .
												"' hspace='3' align='absmiddle' border='0'>";
								} elseif ($ext == 'txt' || $ext == 'doc' || $ext == 'xls') {
									$fileicon = "<img src='" . vtiger_imageurl ('fbTextFile.gif', $theme) .
												"' hspace='3' align='absmiddle' border='0'>";
								} elseif ($ext == 'zip' || $ext == 'gz' || $ext == 'rar') {
									$fileicon = "<img src='" . vtiger_imageurl ('fbZipFile.gif', $theme) .
												"' hspace='3' align='absmiddle'	border='0'>";
								} else {
									$fileicon = "<img src='" . vtiger_imageurl ('fbUnknownFile.gif', $theme)
												. "' hspace='3' align='absmiddle' border='0'>";
								}
							}
						} elseif ($downloadtype == 'E') {
							if (trim($value) != '') {
								$fileicon = "<img src='" . vtiger_imageurl('fbLink.gif', $theme) .
									"' alt='" . getTranslatedString('LBL_EXTERNAL_LNK', $module) .
									"' title='" . getTranslatedString('LBL_EXTERNAL_LNK', $module) .
									"' hspace='3' align='absmiddle' border='0'>";
							} else {
								$value = '--';
								$fileicon = '';
							}
						} else {
							$value    = ' --';
							$fileicon = '';
						}
						//Cambio para nuevo theme... selecciona en el tpl
						$fileicon = array ('oldicon' => $fileicon, 'ext' => $ext, 'value' => $value);

						$fileName = $db->query_result ($result, $i, 'filename');

						$downloadType = $db->query_result ($result, $i, 'filelocationtype');
						$status       = $db->query_result ($result, $i, 'filestatus');
						// $fileIdQuery = "select attachmentsid from vtiger_seattachmentsrel where crmid=?";
						$fileIdQuery = "SELECT * FROM vtiger_seattachmentsrel vs
									LEFT JOIN `vtiger_attachments` va ON va.`attachmentsid`=vs.`attachmentsid`
									WHERE crmid=?";
						$fileIdRes   = $db->pquery ($fileIdQuery, array ($recordId));
						$attach      = $db->fetchByAssoc ($fileIdRes);
						// echo "<pre>".print_r(array($recordId,$fileIdQuery),true)."</pre>";
						// echo "<pre>".print_r($attach,true)."</pre>";
						$attach['isimage'] = false;
						if (preg_match ('/image/i', $attach['type'])) {
							$attach['isimage'] = true;
						}
						$fileId = $attach['attachmentsid'];
						// $fileId = $db->query_result($fileIdRes,0,'attachmentsid');
						if ($fileName != '' && $status == 1) {
							if ($downloadType == 'I') {
								$value = "<a href='index.php?module=uploads&action=downloadfile&" .
										 "entityid=$recordId&fileid=$fileId' title='" .
										 getTranslatedString ("LBL_DOWNLOAD_FILE", $module) .
										 "' onclick='javascript:dldCntIncrease($recordId);'>" . textlength_check ($value) .
										 "</a>";
							} elseif ($downloadType == 'E') {
								$value = "<a target='_blank' href='$fileName' onclick='javascript:" .
										 "dldCntIncrease($recordId);' title='" .
										 getTranslatedString ("LBL_DOWNLOAD_FILE", $module) . "'>" . textlength_check ($value) .
										 "</a>";
							} else {
								$value = ' --';
							}
							$value             = $attach;
							$value             = array ('fileicon' => $fileicon, 'value' => $value, 'href' => 'index.php?module=Documents&parenttab=&action=DetailView&record=' . $recordId);
							$row[ $fieldName ] = $value;
						} else {
							$row[ $fieldName ] = array ('href' => 'index.php?module=Documents&parenttab=&action=DetailView&record=' . $recordId);
						}
						continue;
					} elseif ($module == 'Documents' && $fieldName == 'filesize') {
						$downloadType = $db->query_result ($result, $i, 'filelocationtype');
						if ($downloadType == 'I') {
							$filesize = $value;
							if ($filesize < 1024) {
								$value = $filesize . ' B';
							} elseif ($filesize > 1024 && $filesize < 1048576) {
								$value = round ($filesize / 1024, 2) . ' KB';
							} else if ($filesize > 1048576) {
								$value = round ($filesize / (1024 * 1024), 2) . ' MB';
							}
						} else {
							$value = ' --';
						}
					} elseif ($module == 'Documents' && $fieldName == 'filestatus') {
						if ($value == 1) {
							$value = getTranslatedString ('yes', $module);
						} elseif ($value == 0) {
							$value = getTranslatedString ('no', $module);
						} else {
							$value = '--';
						}
					} elseif ($module == 'Documents' && $fieldName == 'filetype') {
						$downloadType = $db->query_result ($result, $i, 'filelocationtype');
						if ($downloadType == 'E' || $downloadType != 'I') {
							$value = '--';
						}
					} elseif ($uitype == '258') {
						if (!empty ($value)) {
							$dummyResult = $adb->pquery ('SELECT CONCAT(path, attachmentsid, \'_\', name) AS filepath FROM vtiger_attachments WHERE attachmentsid=?', array ($value));
							if ($adb->num_rows ($dummyResult) > 0) {
								$dummyRow = $adb->fetchByAssoc ($dummyResult, -1, false);
								$dummyFilePath = $dummyRow ['filepath'];
								if (is_file (__DIR__ . "/../../{$dummyFilePath}")) {
									$value = "<figure style=\"border: 1px solid rgb(231, 235, 238); border-radius: 50%; height: 40px; overflow: hidden; width: 40px;\"><img src=\"{$dummyFilePath}\" class=\"img-responsive\"></figure>";
								} else {
									$dummyFilePath = 'themes/centaurus/img/avatar_2x.png';
									$value = "<figure style=\"border: 1px solid rgb(231, 235, 238); border-radius: 50%; height: 40px; overflow: hidden; width: 40px;\"><img src=\"{$dummyFilePath}\" class=\"img-responsive\"></figure>";
								}
							}
							if ($dummyResult instanceof ADORecordSet) {
								$dummyResult->Close ();
								$dummyResult = null;
							}
						}
					} elseif ($field->getUIType () == '27') {
						if ($value == 'I') {
							$value = getTranslatedString ('LBL_INTERNAL', $module);
						} elseif ($value == 'E') {
							$value = getTranslatedString ('LBL_EXTERNAL', $module);
						} else {
							$value = ' --';
						}
/*
					} elseif ($field->getFieldDataType () == 'picklist') {
						if ($value != '' && !$is_admin && $this->picklistRoleMap[ $fieldName ] &&
							!in_array ($value, $this->picklistValueMap[ $fieldName ])
						) {
							$value = "<font color='red'>" . getTranslatedString ('LBL_NOT_ACCESSIBLE',
									$module) . "</font>";
						} else {
							$value = getTranslatedString ($value, $module);
							$value = textlength_check ($value);
						}
*/
					} elseif ($field->getFieldDataType () == 'picklist') {
						if ($value != '' && !$is_admin && $this->picklistRoleMap[ $fieldName ] &&
							!in_array ($value, $this->picklistValueMap[ $fieldName ])
						) {
							// Verificar si el valor existe en la tabla de picklist
							if ($this->picklistValueExists($fieldName, $value)) {
								// El valor existe pero el usuario no tiene permiso para verlo
								$value = "<font color='red'>" . getTranslatedString ('LBL_NOT_ACCESSIBLE', $module) . "</font>";
							} else {
								// El valor no existe en la tabla (valor obsoleto/huérfano)
								$translatedValue = getTranslatedString ($value, $module);
								$value = "<font color='red'>" . textlength_check ($translatedValue) . "</font>";
							}
						} else {
							$value = getTranslatedString ($value, $module);
							$value = textlength_check ($value);
						}
					} elseif ($field->getFieldDataType () == 'date' || $field->getFieldDataType () == 'datetime') {
						if ($value != '' && $value != '0000-00-00') {
							$date  = new DateTimeField($value);
							$value = $date->getDisplayDate ();
							if ($field->getFieldDataType () == 'datetime') {
								$value .= (' ' . $date->getDisplayTime ());
							}
						} elseif ($value == '0000-00-00') {
							$value = '';
						}
					} elseif ($field->getFieldDataType () == 'currency') {
						if ($value != '') {
							if ($field->getUIType () == 72) {
								if ($fieldName == 'unit_price') {
									$currencyId      = getProductBaseCurrency ($recordId, $module);
									$cursym_convrate = getCurrencySymbolandCRate ($currencyId);
									$currencySymbol  = $cursym_convrate['symbol'];
								} else {
									$currencyInfo   = getInventoryCurrencyInfo ($module, $recordId);
									$currencySymbol = $currencyInfo['currency_symbol'];
								}
								$value         = number_format ($value, 2, '.', '');
								$currencyValue = CurrencyField::convertToUserFormat ($value, null, true);
								$value         = CurrencyField::appendCurrencySymbol ($currencyValue, $currencySymbol);
							} else {
								//changes made to remove vtiger_currency symbol infront of each
								//vtiger_potential amount
								if ($value != 0) {
									$value = CurrencyField::convertToUserFormat ($value);
								}
							}
						}
					} elseif ($field->getFieldDataType () == 'url') {
						$matchPattern = "^[\w]+:\/\/^";
						preg_match ($matchPattern, $rawValue, $matches);
						if (!empty ($matches[0])) {
							$value = '<a href="' . $rawValue . '" target="_blank">' . textlength_check ($value) . '</a>';
						} else {
							$value = '<a href="http://' . $rawValue . '" target="_blank">' . textlength_check ($value) . '</a>';
						}
					} elseif ($field->getFieldDataType () == 'email') {
						$value = textlength_check ($value);
					} elseif ($field->getFieldDataType () == 'boolean') {
						if ($value == 1) {
							$value = getTranslatedString ('si', $module);
						} elseif ($value == 0) {
							$value = getTranslatedString ('no', $module);
						} else {
							$value = '--';
						}
					} elseif ($field->getUIType () == 98) {
						$value = '<a href="index.php?action=RoleDetailView&module=Settings&parenttab=' .
								 'Settings&roleid=' . $value . '">' . textlength_check (getRoleName ($value)) . '</a>';
					} elseif ($field->getFieldDataType () == 'multipicklist') {
						$value = ($value != "") ? str_replace (' |##| ', ', ', $value) : "";
						if (!$is_admin && $value != '') {
							$valueArray = ($rawValue != "") ? explode (' |##| ', $rawValue) : array ();
							$notaccess  = '<font color="red">' . getTranslatedString ('LBL_NOT_ACCESSIBLE',
									$module) . "</font>";
							$tmp        = '';
							$tmpArray   = array ();
							foreach ($valueArray as $index => $val) {
								if (!$listview_max_textlength ||
									!(strlen (preg_replace ("/(<\/?)(\w+)([^>]*>)/i", "", $tmp)) >
									  $listview_max_textlength)
								) {
/*
									if (!$is_admin && $this->picklistRoleMap[ $fieldName ] &&
										!in_array (trim ($val), $this->picklistValueMap[ $fieldName ])
									) {
										$tmpArray[] = $notaccess;
										$tmp .= ', ' . $notaccess;
									} else {
										$tmpArray[] = $val;
										$tmp .= ', ' . $val;
									}
*/
									if (!$is_admin && $this->picklistRoleMap[ $fieldName ] &&
										!in_array (trim ($val), $this->picklistValueMap[ $fieldName ])
									) {
										// Verificar si el valor existe en la tabla de picklist
										if ($this->picklistValueExists($fieldName, trim($val))) {
											// El valor existe pero el usuario no tiene permiso
											$tmpArray[] = $notaccess;
											$tmp .= ', ' . $notaccess;
										} else {
											// El valor no existe en la tabla (valor obsoleto/huérfano)
											$obsoleteValue = '<font color="red">' . trim($val) . '</font>';
											$tmpArray[] = $obsoleteValue;
											$tmp .= ', ' . $obsoleteValue;
										}
									} else {
										$tmpArray[] = $val;
										$tmp .= ', ' . $val;
									}
								} else {
									$tmpArray[] = '...';
									$tmp .= '...';
								}
							}
							$value = implode (', ', $tmpArray);
							$value = textlength_check ($value);
						}
					} elseif ($field->getFieldDataType () == 'skype') {
						$value = ($value != "") ? "<a href='skype:$value?call'>" . textlength_check ($value) . "</a>" : "";
					} elseif ($field->getFieldDataType () == 'phone') {
						$value = textlength_check ($value);
					} elseif ($field->getFieldDataType () == 'reference') {
						$referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList ();
						$moduleList             = $referenceFieldInfoList[ $fieldName ];
						if (count ($moduleList) == 1) {
							$parentModule = $moduleList[0];
						} else {
							$parentModule = $this->typeList[ $value ];
						}
						if (!empty($value) && !empty($this->nameList[ $fieldName ]) && !empty($parentModule)) {
							$parentMeta = $this->queryGenerator->getMeta ($parentModule);
							$value      = textlength_check ($this->nameList[ $fieldName ][ $value ]);

							list($deleted) = $adb->fetch_row ($adb->pquery ("SELECT deleted FROM vtiger_crmentity WHERE crmid=?", array ($rawValue)));

							if ($deleted == '1') {    // EGC No link para registros borrados
								$value .= " " . getTranslatedString ('LBL_DELETED');
							}

							if ($parentMeta->isModuleEntity () && $parentModule != "Users" && $deleted != '1') {
								$value = "<a href='index.php?module=$parentModule&action=DetailView&" .
										 "record=$rawValue' title='" . getTranslatedString ($parentModule, $parentModule) . "'>$value</a>";
							}
						} else {
							$value = '--';
						}
					} elseif ($field->getFieldDataType () == 'owner') {
						if (in_array ('imagename', $listViewFields)) {
							$myUser   = textlength_check ($this->ownerNameList[ $fieldName ][ $value ]);
							$myAvatar = $this->db->query_result ($result, $i, 'imagename');
							$myAvatar = (empty($myAvatar)) ? '/Image/avatar/png/man.png' : "{$_SESSION ['plat']}/user_images/{$myAvatar}";
							$value =   '<figure class="center-block" style="border-radius: 50%; height: 40px; overflow: hidden; width: 40px;"><img class="img-responsive img-circle" alt="'.$myUser.'"  title="'.$myUser.'" src="'.$myAvatar.'"></figure>';
						} else {
							$value = textlength_check ($this->ownerNameList[ $fieldName ][ $value ]);
						}
					} elseif ($field->getUIType () == 25) {
						//TODO clean request object reference.
						$contactId = $_REQUEST['record'];
						$emailId   = $this->db->query_result ($result, $i, "activityid");
						$result1   = $this->db->pquery ("SELECT access_count FROM vtiger_email_track WHERE " .
														"crmid=? AND mailid=?", array ($contactId, $emailId));
						$value     = $this->db->query_result ($result1, 0, "access_count");
						if (!$value) {
							$value = 0;
						}
					} elseif ($field->getUIType () == 8) {
						if (!empty($value)) {
							$temp_val = html_entity_decode ($value, ENT_QUOTES, $default_charset);
							$json     = new Zend_Json();
							$value    = vt_suppressHTMLTags (implode (',', $json->decode ($temp_val)));
						}
					} elseif (in_array ($uitype, array (7, 9, 90))) {
						$value = "<span align='right'>" . textlength_check ($value) . "</div>";
					} else if (($field->getUIType () == 2202) && (count ($gridFields)) && !empty($resultFields)) {
						foreach (array_keys ($resultFields) as $columnname) {
							if ((!is_numeric ($columnname)) && (in_array ($columnname, $girdColumns)) && (!in_array ($columnname, $girdColumnsFound))) {
								$rawGridValue        = $this->db->query_result($result, $i, $columnname);
								$value               = $numberingHelper->setNumberFormat ($rawGridValue);
								$girdColumnsFound [] = $columnname;
								$gridFieldName       = $gridFields [$columnname];
								break;
							}
						}
					} else {
						$value = textlength_check ($value);
					}

					$parenttab     = getParentTab ();
					$nameFields    = $this->queryGenerator->getModuleNameFields ($module);
					$nameFieldList = explode (',', $nameFields);
					if ($demoMode) {
						global $demoModeStyle;
						$value = '<span style="' . $demoModeStyle . '">' . $value . '</span>';
					}

					if (in_array ($fieldName, $nameFieldList) && $module != 'Emails') {
						$modalTitle = (!empty ($value)) ? $value : $modalTitle;
						$value = "<a href='index.php?module=$module&parenttab=$parenttab&action=DetailView&record=" .
								 "$recordId' title='" . getTranslatedString ($module, $module) . "'>$value</a>";
					} else if (($fieldName == $focus->list_link_field || $fieldName == $focus->defaultListLink) && $module != 'Emails' && $deleted != '1') {
						$modalTitle = (!empty ($value)) ? $value : $modalTitle;
						$value = "<a href='index.php?module=$module&parenttab=$parenttab&action=DetailView&record=" .
								 "$recordId' title='" . getTranslatedString ($module, $module) . "'>$value</a>";
					} else if (($modalTitle == $module) && (!key_exists('modal-detail-row', $row))) {
						$modalTitle = $this->db->query_result ($result, $i, $listViewFields[ 1 ]);
					}
					if (!key_exists('modal-detail-row', $row)) {
						// Personalización para módulo orden_de_trabajo: usar vista especial de expediente
						if ($module == 'orden_de_trabajo') {
							$modalDetail = "<a data-modal='modal-detail-row' class='md-trigger sjv-special-preview' data-target='#modal-detail-row' data-module='orden_de_trabajo' data-record='".$recordId."' modal-title='".$modalTitle."' href='javascript:void(0)' title='Ver expediente del trabajo'><span style='display: none'></span><i class=\"fa fa-eye\"></i></a>";
						} else {
							$modalDetail = "<a data-modal='modal-detail-row' class='md-trigger' data-target='#modal-detail-row' modal-title='".$modalTitle."'  href='index.php?module=$module&parenttab=$parenttab&action=DetailView&tab=detail&record=" .
								"$recordId' title='" . getTranslatedString($module, $module) . "'><span style='display: none'></span><i class=\"fa fa-eye\"></i></a>";
						}
						$modalDetail = "$modalDetail <span type='vtlib_metainfo' vtrecordid='{$recordId}' vtfieldname=" .
							"'{$fieldName}' vtmodule='$module' style='display:none;'></span>";
						$row['modal-detail-row'] = $modalDetail;
					}

					// vtlib customization: For listview javascript triggers
					$value = "$value <span type='vtlib_metainfo' vtrecordid='{$recordId}' vtfieldname=" .
							 "'{$fieldName}' vtmodule='$module' style='display:none;'></span>";
					// END
					if (empty ($gridFieldName)) {
						$row[ $fieldName ]     = $value;
						$rawRow [ $fieldName ] = $rawValue;
					} else {
						$row[ $gridFieldName ]     = $value;
						$rawRow [ $gridFieldName ] = $rawGridValue;
					}
				}
				//Added for Actions ie., edit and delete links in listview
				$entity                = clone $focus;
				$entity->column_fields = $rawRow;
				$editPermission        = isPermitted ($module, 'EditView', '');
				if ($editPermission == 'yes') {
					require_once ('include/platzilla/Managers/ModuleEditPermissionManager.php');
					$editPermission = ModuleEditPermissionManager::getInstance ($adb)->isEditable ($module, $entity) ? 'yes' : 'no';
				}
				$actionLinkInfo = "";
				if ($editPermission == 'yes') {
					$edit_link = $this->getListViewEditLink ($module, $recordId);
					if (isset($navigationInfo['start']) && $navigationInfo['start'] > 1 && $module != 'Emails') {
						$actionLinkInfo .= '<a href="' . $edit_link . '&start=' . $navigationInfo['start'] . '"><i class="fa fa-pencil listview-controller btn btn-link" data-toggle="tooltip" title="' . getTranslatedString ("LNK_EDIT", $module) . '"></i></a>';
					} else {
						$actionLinkInfo .= '<a href="' . $edit_link . '&selectFilter=' . $_REQUEST['viewname'] . '"><i class="fa fa-pencil listview-controller wa1 btn btn-link" data-toggle="tooltip" title="' . getTranslatedString ("LNK_EDIT", $module) . '"></i></a>';
					}
				}

				if (isPermitted ($module, "Delete", "") == 'yes') {
					if ($module == 'orden_de_trabajo') {

						$isRemovable = !$this->isWorkCompleted ($adb, $recordId);
						if ($isRemovable){
							$isRemovable = !(taskToWork::getInstance($adb)->hasRelatedTask ($recordId));
						}
					}
					$del_link = $this->getListViewDeleteLink ($module, $recordId);
					if (($del_link != "") && $isRemovable) {
						$actionLinkInfo .= "<a href='javascript:confirmdelete(\"" . addslashes (urlencode ($del_link)) . "\")' title='" . getTranslatedString ("LNK_DELETE", $module) . "' data-toggle='tooltip' ><i class='fa fa-trash-o listview-controller btn btn-link'></i></a></a>";
					}
				}
				//Boton de acciones para el listview
				if (true) {
					$actionLinkInfo .= getActionButton ($recordId, $focus);
					$actionLinkInfo .= getRelateListButton ($module, $recordId);
				}
				// END
				if ($actionLinkInfo != "" && !$skipActions) {
					$row[] = $actionLinkInfo;
				}
				//Color background
				if (method_exists ($focus, 'getColorRow')) {
					$data[ $recordId ]['records']     = $row;
					$data[ $recordId ]['color']       = $focus->getColorRow ($recordId);
					$data[ $recordId ]['onmouseover'] = $focus->getOnMouseOverRow ($recordId);
					$data[ $recordId ]['onmouseout']  = $focus->getOnMouseOutRow ($recordId);
				} else {
					$data[ $recordId ]['records'] = $row;
				}

				if (!empty($colorCondition)) {
					$data[ $recordId ]['color'] = $this->getColorRule ($colorCondition, $result, $i, $fieldIndex);
				}

				if ($res = getFieldAttributes ($listViewFields, $recordId, $focus)) {
					$data[ $recordId ]['fields_attributes'] = $res;
				}
				$data[ $recordId ]['isRemovable'] = $isRemovable;
				} catch (Exception $e) {
					continue;
				}
			}

			return $data;
		}

		public function getColorRule ($colorCondition, $resultAll, $kk, $fieldIndex) {
			$color          = 'white';
			$numCondition   = count ($colorCondition);
			$avaiablePerios = array_keys (CustomViewHelper::getAvailablePeriods ());
			for ($i = 0; $i < $numCondition; $i++) {
				$result = false;
				if ($colorCondition[ $i ]['columns'] != null) {
					$j = 0;
					foreach ($colorCondition[ $i ]['columns'] as $condition) {
						$rowfilter = count ($colorCondition[ $i ]['columns']);
						$field     = explode (':', $condition['columnname']);
						$fieldName = $field[1];
						$operator  = $condition['comparator'];
						$value     = $this->sanitizeString ($condition['value']);
						$crmId     = $this->db->query_result ($resultAll, $kk, $fieldIndex);
						if (in_array ($operator, $avaiablePerios)) {
							$filter = $this->isOnThePeriod ($condition, $crmId, $fieldIndex);
						} else if ($field [0] == 'vtiger_subfields_values') {
							$moduleAndFieldLabel = explode('@', $field[3], 2);
							$gridVale = $this->getValueFromGrid ($field [2], $field [1], $moduleAndFieldLabel [0], $crmId);
							$gridVale = $this->sanitizeString ($gridVale);
							$filter   = $this->getConditionColor ($operator, $gridVale, $value);
						} else {
							$fieldValue = $this->db->query_result ($resultAll, $kk, $fieldName);
							$fieldValue = $this->sanitizeString (html_entity_decode($fieldValue, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
							$filter     = $this->getConditionColor ($operator, $fieldValue, $value);
						}
						if ($condition['column_condition'] != '') {
							$glue = $condition['column_condition'];
						} else {
							$glue = '';
						}
						if ($j == 0) {
							$result = $filter;
							if ($rowfilter == 1) {
								break;
							}
						} else {
							$result = $this->getOperatorColor ($result, $glue, $filter);
							$glue   = '';
						}
						$j++;
					}

					if ($result) {
						$color = $colorCondition[ $i ]['condition'];
						break;
					}
				}
			}

			return $color;
		}

		public function getOperatorColor (&$result, $operator, $condition) {
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

		public function getConditionColor ($operator, $field, $value) {
			switch ($operator) {
				case 'e':
					if ($value == 'NULL') {
						return (empty ($field));
					} else if ($field == $value) {
						return true;
					}
					break;
				case 'n':
					if ($value == 'NULL') {
						return (!empty ($field));
					} else if ($field != $value) {
						return true;
					}
					break;
				case 's':
					// Comienza con
					if (strpos ($field, $value) === 0) {
						return true;
					}
					break;
				case 'ew':
					// Termina en
					$lfd       = strlen ($field);
					$lvl       = strlen ($value);
					$subCadena = substr ($field, ($lfd - $lvl));
					if ($subCadena == $value) {
						return true;
					}
					break;
				case 'c':
					//Contiene
					$pos = strpos ($field, $value);
					if ($pos !== false) {
						return true;
					}
					break;
				case 'k':
					//No contiene
					$pos = strpos ($field, $value);
					if ($pos === false) {
						return true;
					}
					break;
				case 'l':
					if ($field < $value) {
						return true;
					}
					break;
				case 'g':
					if ($field > $value) {
						return true;
					}
					break;
				case 'm':
					if ($field <= $value) {
						return true;
					}
					break;
				case 'h':
					if ($field >= $value) {
						return true;
					}
					break;
				case 'a':
					//Despues
					if ($field > $value) {
						return true;
					}
					break;
				case 'b':
					//Antes
					if ($field < $value) {
						return true;
					}
					break;
				default:
					return false;
			}
		}

		public function getListViewEditLink ($module, $recordId, $activityType = '') {
			if ($module == 'Emails') {
				return 'javascript:;" onclick="OpenCompose(\'' . $recordId . '\',\'edit\');';
			}
			if ($module != 'Calendar') {
				if ($module == 'Potentials') {
					$return_action = "DetailView";
				} else {
					$return_action = "index";
				}
			} else {
				$return_action = 'ListView';
			}
			//Added to fix 4600
			$url    = getBasic_Advance_SearchURL ();
			$parent = getParentTab ();
			//Appending view name while editing from ListView
			//
			if (isset($_REQUEST['viewname']) && $_REQUEST['viewname'] != '') {

				$link = "index.php?module=$module&action=EditView&record=$recordId&return_module=$module" .
						"&return_action=$return_action&parenttab=$parent" . $url . "&return_viewname=" .
						$_REQUEST['viewname'];
			} else {

				$link = "index.php?module=$module&action=EditView&record=$recordId&return_module=$module" .
						"&return_action=$return_action&parenttab=$parent" . $url . "&return_viewname=" .
						$_SESSION['lvs'][ $module ]["viewname"];
			}

			if ($module == 'Calendar') {
				if ($activityType == 'Task') {
					$link .= '&activity_mode=Task';
				} else {
					$link .= '&activity_mode=Events';
				}
			}

			if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
				$link .= "&platdb=" . vtlib_purify ($_REQUEST['platdb']);
			}
			return $link;
		}

		public function getListViewDeleteLink ($module, $recordId) {
			$parenttab = getParentTab ();

			if (isset($_REQUEST['viewname']) && $_REQUEST['viewname'] != '') {

				$viewname = $_REQUEST['viewname'];
			} else {
				$viewname = $_SESSION['lvs'][ $module ]['viewname'];
			}

			//Added to fix 4600
			$url = getBasic_Advance_SearchURL ();
			if ($module == "Calendar") {
				$return_action = "ListView";
			} else {
				$return_action = "index";
			}
			//This is added to avoid the del link in Product related list for the following modules
			$link = "index.php?module=$module&action=Delete&record=$recordId" .
					"&return_module=$module&return_action=$return_action" .
					"&parenttab=$parenttab&return_viewname=" . $viewname . $url;

			// vtlib customization: override default delete link for custom modules
			$requestModule  = vtlib_purify ($_REQUEST['module']);
			$requestRecord  = vtlib_purify ($_REQUEST['record']);
			$requestAction  = vtlib_purify ($_REQUEST['action']);
			$requestFile    = vtlib_purify ($_REQUEST['file']);
			$isCustomModule = vtlib_isCustomModule ($requestModule);

			if ($isCustomModule && (!in_array ($requestAction, Array ('index', 'ListView')) &&
									($requestAction == $requestModule . 'Ajax' && !in_array ($requestFile, Array ('index', 'ListView'))))
			) {

				$link = "index.php?module=$requestModule&action=updateRelations&parentid=$requestRecord";
				$link .= "&destination_module=$module&idlist=$entity_id&mode=delete&parenttab=$parenttab";
			}
			if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
				$link .= "&platdb=" . vtlib_purify ($_REQUEST['platdb']);
			}
			// END
			return $link;
		}

		public function getListViewHeader (
			$focus, $module, $sort_qry = '', $sorder = '', $orderBy = '',
			$skipActions = false
		) {
			global $log, $singlepane_view;
			global $theme;
			$sorder     = strtoupper ($sorder);
			$arrow      = '';
			$qry        = getURLstring ($focus);
			$theme_path = "themes/" . $theme . "/";
			$image_path = $theme_path . "images/";
			$header     = Array ();
			$header []  = ' ';

			//Get the vtiger_tabid of the module
			$tabid   = getTabid ($module);
			$tabname = getParentTab ();
			global $current_user;

			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');

			$fields      = $this->queryGenerator->getFields ();
			$whereFields = $this->queryGenerator->getWhereFields ();
			$gridFields  = $this->queryGenerator->getSummaryLabels ();
			$girdColumns = array_keys ($gridFields);
			$meta        = $this->queryGenerator->getMeta ($this->queryGenerator->getModule ());

			$moduleFields        = $meta->getModuleFields ();
			$accessibleFieldList = array_keys ($moduleFields);
			$listViewFields      = array_intersect ($fields, $accessibleFieldList);

			// OPTIMIZACIÓN: Cachear traducciones y valores de sesión y permisos
			$translatedLabels = array();
			foreach ($listViewFields as $fieldName) {
				$field = $moduleFields[$fieldName];
				$translatedLabels[$fieldName] = getTranslatedString($field->getFieldLabelKey(), $module);
			}
			$sessionStart = isset($_SESSION['lvs'][$module]['start']) ? $_SESSION['lvs'][$module]['start'] : 1;
			$permEdit = isPermitted($module, "EditView", "") == 'yes';
			$permDelete = isPermitted($module, "Delete", "") == 'yes';

			//Added on 14-12-2005 to avoid if and else check for every list
			//vtiger_field for arrow image and change order
			$change_sorder = array ('ASC' => 'DESC', 'DESC' => 'ASC');
			//La asignación del icono de ordenación se realiza mediante css.
			$arrow_gif = array ('ASC' => 'up', 'DESC' => 'down');
			$girdColumnsFound = array ();
			foreach ($listViewFields as $fieldName) {
				$field = $moduleFields[ $fieldName ];
				$label = $translatedLabels[$fieldName];

				if (in_array ($field->getColumnName (), $focus->sortby_fields)) {
					if ($orderBy == $field->getColumnName ()) {
						$temp_sorder = $change_sorder[ $sorder ];
						//$arrow = "&nbsp;<img src ='".vtiger_imageurl($arrow_gif[$sorder], $theme)."' border='0'>";
						$arrow = '<i class="fa fa-caret-' . $arrow_gif[ $sorder ] . '" aria-hidden="true" style="margin-left:.5em;"></i>';
					} else {
						$temp_sorder = 'ASC';
						$arrow = '<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;" ></i>';
					}
					//added to display vtiger_currency symbol in listview header
					if ($label == 'Amount') {
						$label .= ' (' . getTranslatedString ('LBL_IN', $module) . ' ' .
								  $user_info['currency_symbol'] . ')';
					}
					if ($field->getUIType () == '9') {
						$label .= ' (%)';
					}
					if ($module == 'Users' && $fieldName == 'User Name') {
						$name = '<div class="title-overflow">' . "<a href='javascript:;' onClick='getListViewEntries_js(\"" . $module .
							"\",\"parenttab=" . $tabname . "&order_by=" . $field->getColumnName() . "&sorder=" .
							$temp_sorder . $sort_qry . "\");' class='listFormHeaderLinks" . "' > " .
							getTranslatedString('LBL_LIST_USER_NAME_ROLE', $module) . "</a></div>" . $arrow;
					} else if ($field->getUIType () == '2202') {
						$gridTableName = 'vtiger_grid_summary_'.$fieldName;
						foreach ($girdColumns as $column) {
							if (!in_array($column, $girdColumnsFound)) {
								$girdColumnsFound [] = $column;
								$name = '<div class="title-overflow">' . "<a href='javascript:;' onClick='getListViewEntries_js(\"" . $module .
									"\",\"parenttab=" . $tabname . "&foldername=Default&order_by=" . $gridTableName . '.' .$column . "&start=" .
									$sessionStart . "&sorder=" . $temp_sorder . "" .
									$sort_qry . "\");' class='listFormHeaderLinks ' " . ' title="' . $label . ' ('. $gridFields [ $column ] . ') "> ' . $gridFields [ $column ] . "</a></div>" . $arrow;
								break;
							}
						}
					} else {
						if ($this->isHeaderSortingEnabled ()) {
							$name = '<div class="title-overflow">' . "<a href='javascript:;' onClick='getListViewEntries_js(\"" . $module .
									"\",\"parenttab=" . $tabname . "&foldername=Default&order_by=" . $field->getColumnName () . "&start=" .
									$sessionStart . "&sorder=" . $temp_sorder . "" .
									$sort_qry . "\");' class='listFormHeaderLinks ' " . ' title="' . $label . '"> ' . $label . "</a></div>" . $arrow;
						} else {
							$name = '<div class="title-overflow">' . $label . " " . $arrow . '</div>';
						}
					}
					$arrow = '';
				} else {
					$title_head = $label;
					$name       = '<div class="title-overflow" title="' . $title_head . '">' . $title_head . '</div>';
				}
				//added to display vtiger_currency symbol in related listview header
				if ($name == 'Amount') {
					$name .= ' (' . getTranslatedString ('LBL_IN') . ' ' . $user_info['currency_symbol'] . ')';
				}

				$header[] = $name;
			}

			//Added for Action - edit and delete link header in listview
			if (!$skipActions && ($permEdit || $permDelete)) {
				$header[] = getTranslatedString ("LBL_ACTION", $module);
			}
			return $header;
		}

		public function getBasicSearchFieldInfoList () {
			$fields      = $this->queryGenerator->getFields ();
			$whereFields = $this->queryGenerator->getWhereFields ();
			$meta        = $this->queryGenerator->getMeta ($this->queryGenerator->getModule ());

			$moduleFields             = $meta->getModuleFields ();
			$accessibleFieldList      = array_keys ($moduleFields);
			$listViewFields           = array_intersect ($fields, $accessibleFieldList);
			$basicSearchFieldInfoList = array ();
			foreach ($listViewFields as $fieldName) {
				$field                                  = $moduleFields[ $fieldName ];
				$basicSearchFieldInfoList[ $fieldName ] = getTranslatedString ($field->getFieldLabelKey (),
					$this->queryGenerator->getModule ());
			}
			return $basicSearchFieldInfoList;
		}

		public function getAdvancedSearchOptionString () {
			$module = $this->queryGenerator->getModule ();
			$meta   = $this->queryGenerator->getMeta ($module);

			$moduleFields = $meta->getModuleFields ();
			$i            = 0;
			foreach ($moduleFields as $fieldName => $field) {
				if ($field->getFieldDataType () == 'reference') {
					$typeOfData = 'V';
				} else if ($field->getFieldDataType () == 'boolean') {
					$typeOfData = 'C';
				} else {
					$typeOfData = $field->getTypeOfData ();
					$typeOfData = explode ("~", $typeOfData);
					$typeOfData = $typeOfData[0];
				}
				$label = getTranslatedString ($field->getFieldLabelKey (), $module);
				if (empty($label)) {
					$label = $field->getFieldLabelKey ();
				}
				if ($label == "Start Date & Time") {
					$fieldlabel = "Start Date";
				}
				$selected = '';
				if ($i++ == 0) {
					$selected = "selected";
				}

				// place option in array for sorting later
				//$blockName = getTranslatedString(getBlockName($field->getBlockId()), $module);
				$blockName = getTranslatedString ($field->getBlockName (), $module);

				$fieldLabelEscaped = str_replace (" ", "_", $field->getFieldLabelKey ());
				$optionvalue       = $field->getTableName () . ":" . $field->getColumnName () . ":" . $fieldName . ":" . $module . "_" . $fieldLabelEscaped . ":" . $typeOfData;

				$OPTION_SET[ $blockName ][ $label ] = "<option value=\'$optionvalue\' $selected>$label</option>";
			}
			// sort array on block label
			ksort ($OPTION_SET, SORT_STRING);

			foreach ($OPTION_SET as $key => $value) {
				$shtml .= "<optgroup label='$key' class='select' style='border:none'>";
				// sort array on field labels
				ksort ($value, SORT_STRING);
				$shtml .= implode ('', $value);
			}

			return $shtml;
		}

	}
