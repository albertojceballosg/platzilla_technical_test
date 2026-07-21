<?php
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/PHPExcel-1.8/Classes/PHPExcel.php');
	require_once ('include/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php');

	class XlsxFileImporter {
		private static $INSTANCE = null;
		private        $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		private function fetchGroupId ($rowValue) {
			if (empty ($rowValue)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_groups WHERE groupname=?', array ($rowValue));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			return $row ['groupid'];
		}

		private function fetchReferencedModuleRecordId ($referencedModuleTableName, $referencedModuleFieldName, $referencedModuleIdColumnName, $rowValue) {
			$result = $this->adb->pquery (
				"SELECT {$referencedModuleIdColumnName} FROM {$referencedModuleTableName} WHERE {$referencedModuleFieldName}=?",
				array ($rowValue)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				$value = null;
			} else {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$value = $row [ $referencedModuleIdColumnName ];
			}
			return $value;
		}

		private function fetchUserId ($rowValue) {
			if (empty ($rowValue)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_users WHERE user_name=?', array ($rowValue));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			return $row ['id'];
		}

		/**
		 * @param string $moduleName
		 * @param Users $user
		 *
		 * @return array|null
		 */
		private function getAllowedFields ($moduleName, Users $user) {
			if (empty ($moduleName)) {
				return null;
			}

			$current_user_groups          = null;
			$current_user_parent_role_seq = null;
			$defaultOrgSharingPermission  = null;
			$is_admin                     = null;
			$profileGlobalPermission      = null;
			$local_user                   = clone $user;
			require ('user_privileges/user_privileges.php');

			if (($is_admin == true) || ($profileGlobalPermission [1] == 0) || ($profileGlobalPermission [2] == 0) || ($moduleName == 'Users')) {
				$result = $this->adb->pquery (
					"SELECT
						f.*,
						enmr.fieldname AS referencedmodulefieldname,
						enmr.entityidcolumn AS referencedmoduleidcolumn,
						enmr.tablename AS referencedmoduletablename
					FROM
						vtiger_field f
						INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
						INNER JOIN vtiger_blocks b ON b.blockid=f.block AND b.detail_view=0 AND b.visible=0
						LEFT JOIN vtiger_fieldmodulerel fmr ON fmr.fieldid=f.fieldid
						LEFT JOIN vtiger_entityname enmr ON enmr.modulename=fmr.relmodule
					WHERE
						f.displaytype IN (1, 2, 4) AND
						f.presence IN (0, 2) AND
						f.uitype NOT IN (?, ?, ?, ?, ?, ?, ?, ?, ?)
					ORDER BY
						f.block,
						f.sequence",
					array ($moduleName, FieldInterface::UI_TYPE_ATTACHMENTS, FieldInterface::UI_TYPE_CALCULATED, FieldInterface::UI_TYPE_CODE, FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_GRID, FieldInterface::UI_TYPE_IMAGE_DISPLAY, FieldInterface::UI_TYPE_IMAGE_REFERENCE, FieldInterface::UI_TYPE_MODIFIED_BY, FieldInterface::UI_TYPE_MODULE_RECORDS)
				);
			} else {
				$profileList = implode (', ', getCurrentUserProfileList ());
				$result      = $this->adb->pquery (
					"SELECT
						f.*,
						enmr.fieldname AS referencedmodulefieldname,
						enmr.entityidcolumn AS referencedmoduleidcolumn,
						enmr.tablename AS referencedmoduletablename
					FROM
						vtiger_field f
						INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
						INNER JOIN vtiger_blocks b ON b.blockid=f.block AND b.detail_view=0 AND b.visible=0
						INNER JOIN vtiger_profile2field p2f ON p2f.fieldid=f.fieldid
						INNER JOIN vtiger_def_org_field dof ON dof.fieldid=f.fieldid
						LEFT JOIN vtiger_fieldmodulerel fmr ON fmr.fieldid=f.fieldid
						LEFT JOIN vtiger_entityname enmr ON enmr.modulename=fmr.relmodule
					WHERE
						f.displaytype IN (1, 2, 4) AND
						f.presence IN (0, 2) AND
						f.uitype NOT IN (?, ?, ?, ?, ?, ?, ?, ?, ?) AND
						p2f.visible=0 AND
						p2f.profileid IN ({$profileList}) AND
						dof.visible=0
					GROUP BY
						f.fieldid
					ORDER BY
						f.block,
						f.sequence",
					array ($moduleName, FieldInterface::UI_TYPE_ATTACHMENTS, FieldInterface::UI_TYPE_CALCULATED, FieldInterface::UI_TYPE_CODE, FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_GRID, FieldInterface::UI_TYPE_IMAGE_DISPLAY, FieldInterface::UI_TYPE_IMAGE_REFERENCE, FieldInterface::UI_TYPE_MODIFIED_BY, FieldInterface::UI_TYPE_MODULE_RECORDS)
				);
			}
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$fields = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$fieldLabel             = getTranslatedString ($row ['fieldlabel'], $moduleName);
				$fields [ $fieldLabel ] = $row;
			}

			return $fields;
		}

		private function validateFilePath ($filePath) {
			if (!$filePath) {
				throw new Exception ('No se ha suministrado la ruta del archivo');
			}

			if ((!file_exists ($filePath)) || (!is_file ($filePath))) {
				throw new Exception ("El archivo {$filePath} no existe");
			}

			if (!is_readable ($filePath)) {
				throw new Exception ("El archivo {$filePath} no puede ser leído");
			}
		}

		public function import ($xlsFilePath, $moduleName, Users $user) {
			gc_enable ();
			if (empty ($moduleName)) {
				throw new Exception ('No se ha suministrado el nombre del módulo');
			} else if (empty ($user)) {
				throw new Exception ('No se ha suministrado el ID del usuario');
			}
			$this->validateFilePath ($xlsFilePath);

			$xlsReader   = PHPExcel_IOFactory::load ($xlsFilePath);
			$totalSheets = $xlsReader->getSheetCount ();
			if ($totalSheets <= 0) {
				throw new Exception ('El archivo suministrado no tiene hojas activas');
			}

			$worksheet        = $xlsReader->setActiveSheetIndex (0);
			$highestRowNumber = $worksheet->getHighestRow ();
			if ($highestRowNumber <= 1) {
				throw new Exception ('El archivo suministrado no tiene registros o encabezados');
			}

			$highestColumn       = $worksheet->getHighestColumn ();
			$highestColumnNumber = PHPExcel_Cell::columnIndexFromString ($highestColumn);
			if ($highestColumnNumber <= 0) {
				throw new Exception ('El archivo suministrado no tiene columnas');
			}

			$dummy   = $worksheet->rangeToArray ("A1:{$highestColumn}1", null, true, true, true);
			$headers = array_filter ($dummy [1], 'strlen');
			if (empty ($headers)) {
				throw new Exception ('El archivo suministrado no tiene encabezados en la fila 1');
			}
			$headers = array_values ($headers);

			$fields = $this->getAllowedFields ($moduleName, $user);
			if (empty ($fields)) {
				throw new Exception ("El módulo {$moduleName} no tiene campos configurados");
			}

			$importedRows = 0;
			for ($rowNumber = 2; $rowNumber <= $highestRowNumber; $rowNumber++) {
				$rowData = array ();
				$isRowEmpty = true;
				for ($columnNumber = 0; $columnNumber < $highestColumnNumber; $columnNumber++) {
					$header = isset ($headers [ $columnNumber ]) ? $headers [ $columnNumber ] : null;
					if ((empty ($header)) || (!isset ($fields [ $header ]))) {
						continue;
					}

					$cell = $worksheet->getCellByColumnAndRow ($columnNumber, $rowNumber, false);
					if (empty ($cell)) {
						$rowData [ $fields [ $header ]['fieldname'] ] = null;
					} else {
						$dummy = $cell->getFormattedValue ();
						if (!empty ($dummy)) {
							$isRowEmpty = false;
						}
						$uiType = $fields [ $header ]['uitype'];
						switch ($uiType) {
							case FieldInterface::UI_TYPE_MODULE_REFERENCE:
								$value = $this->fetchReferencedModuleRecordId ($fields [ $header ]['referencedmoduletablename'], $fields [ $header ]['referencedmodulefieldname'], $fields [ $header ]['referencedmoduleidcolumn'], $cell->getFormattedValue ());
								break;
							case FieldInterface::UI_TYPE_OWNER:
								$groupId = $this->fetchGroupId ($cell->getFormattedValue ());
								$userId  = $this->fetchUserId ($cell->getFormattedValue ());
								if (!empty ($groupId)) {
									$value                  = $groupId;
									$rowData ['assigntype'] = 'T';
								} else if (!empty ($userId)) {
									$value                  = $userId;
									$rowData ['assigntype'] = 'U';
								} else {
									$value                  = $user->id;
									$rowData ['assigntype'] = 'U';
								}
								break;
							default:
								$value = $cell->getFormattedValue ();
								break;
						}
						$rowData [ $fields [ $header ]['fieldname'] ] = $value;
					}
				}
				if ($isRowEmpty) {
					break;
				}

				/** @var CRMEntity|stdClass $entity */
				$entity                = CRMEntity::getInstance ($moduleName);
				$entity->column_fields = $rowData;
				$entity->save ($moduleName);
				unset ($entity);
				gc_collect_cycles ();
				$importedRows++;
			}
			return $importedRows;
		}

		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}
