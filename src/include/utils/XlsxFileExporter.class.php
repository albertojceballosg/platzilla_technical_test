<?php
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/PHPExcel-1.8/Classes/PHPExcel.php');

	class XlsxFileExporter {
		private static $INSTANCE = null;
		private        $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param string $moduleName
		 * @param Users $user
		 *
		 * @return array|null
		 */
		private function getAllowedFieldsSqlClauses ($moduleName, Users $user) {
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
					'SELECT
						f.*,
						en.entityidcolumn
					FROM
						vtiger_field f
						INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
						INNER JOIN vtiger_blocks b ON b.blockid=f.block AND b.detail_view=0 AND b.visible=0
						INNER JOIN vtiger_entityname en ON en.tabid=f.tabid
					WHERE
						f.displaytype IN (1, 2, 4) AND
						f.presence IN (0, 2) AND
						f.uitype NOT IN (?, ?, ?, ?, ?, ?, ?, ?, ?)
					ORDER BY
						f.block,
						f.sequence',
					array ($moduleName, FieldInterface::UI_TYPE_ATTACHMENTS, FieldInterface::UI_TYPE_CALCULATED, FieldInterface::UI_TYPE_CODE, FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_GRID, FieldInterface::UI_TYPE_IMAGE_DISPLAY, FieldInterface::UI_TYPE_IMAGE_REFERENCE, FieldInterface::UI_TYPE_MODIFIED_BY, FieldInterface::UI_TYPE_MODULE_RECORDS)
				);
			} else {
				$profileList = implode (', ', getCurrentUserProfileList ());
				$result      = $this->adb->pquery (
					"SELECT
						f.*,
						en.entityidcolumn
					FROM
						vtiger_field f
						INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
						INNER JOIN vtiger_blocks b ON b.blockid=f.block AND b.detail_view=0 AND b.visible=0
						INNER JOIN vtiger_entityname en ON en.tabid=f.tabid
						INNER JOIN vtiger_profile2field p2f ON p2f.fieldid=f.fieldid
						INNER JOIN vtiger_def_org_field dof ON dof.fieldid=f.fieldid
					WHERE
						f.displaytype IN (1, 2, 4) AND
						f.presence IN (0, 2) AND
						f.uitype NOT IN (?, ?, ?, ?, ?) AND
						p2f.visible=0 AND
						p2f.profileid IN ({$profileList}) AND
						dof.visible=0
					GROUP BY
						f.fieldid
					ORDER BY
						f.block,
						f.sequence",
					array ($moduleName, FieldInterface::UI_TYPE_ATTACHMENTS, FieldInterface::UI_TYPE_GRID, FieldInterface::UI_TYPE_IMAGE_DISPLAY, FieldInterface::UI_TYPE_IMAGE_REFERENCE, FieldInterface::UI_TYPE_MODULE_RECORDS)
				);
			}
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$selectClauses   = array ();
			$fromClauses     = array ();
			$searchFirstFrom = '';
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$fieldLabel = getTranslatedString ($row ['fieldlabel'], $moduleName);
				if ($row ['uitype'] == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
					$referencedModuleFieldsSqlClauses = $this->getReferencedModuleFieldsSqlClauses ($moduleName, $row ['fieldname']);
					if (!empty ($referencedModuleFieldsSqlClauses)) {
						$selectClauses [] = $referencedModuleFieldsSqlClauses ['select'];
						$fromClauses []   = "LEFT JOIN {$referencedModuleFieldsSqlClauses ['from']}";
					}
				} else if ($row ['uitype'] == FieldInterface::UI_TYPE_OWNER) {
					$selectClauses [] = "IFNULL(vtiger_users.user_name, vtiger_groups.groupname) AS `{$fieldLabel}`";
					$fromClauses []   = "LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id AND vtiger_users.status='Active'";
					$fromClauses []   = 'LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid';
				} else {
					$selectClauses [] = "{$row ['tablename']}.{$row ['columnname']} AS `{$fieldLabel}`";
					if ($row ['tablename'] != 'vtiger_crmentity') {
						$fromClauses [] = "INNER JOIN {$row ['tablename']} ON {$row ['tablename']}.{$row ['entityidcolumn']}=vtiger_crmentity.crmid";
						if (empty ($searchFirstFrom)) {
							$searchFirstFrom = "INNER JOIN {$row ['tablename']} ON {$row ['tablename']}.{$row ['entityidcolumn']}=vtiger_crmentity.crmid";
						}
					}
				}
			}
			$select = array_filter (array_unique ($selectClauses));
			$from   = array_filter (array_unique ($fromClauses));
			if ( !empty($searchFirstFrom) && ($from[ 0 ] != $searchFirstFrom)) {
				return $this->swapToFirst ($select, $from, $searchFirstFrom);
			} else {
				return array (
					'select' => $select,
					'from'   => $from,
				);
			}
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return array|null
		 */
		private function getReferencedModuleFieldsSqlClauses ($moduleName, $fieldName) {
			$result = $this->adb->pquery (
				'SELECT DISTINCT
					f.columnname AS modulecolumnname,
					f.fieldlabel AS modulefieldlabel,
					f.tablename AS moduletablename,
					en.entityidcolumn AS relatedmoduleidcolumnname,
					(SELECT f2.columnname FROM vtiger_field f2 WHERE f2.tabid=en.tabid AND f2.fieldname=en.fieldname LIMIT 1) AS relatedmodulecolumnname,
					en.tablename AS relatedmoduletablename
				FROM
					vtiger_field f
					INNER JOIN vtiger_fieldmodulerel fmr ON fmr.fieldid=f.fieldid
					INNER JOIN vtiger_entityname en ON en.modulename=fmr.relmodule
				WHERE
					f.uitype=? AND
					f.fieldname=? AND
					fmr.module=?',
				array (FieldInterface::UI_TYPE_MODULE_REFERENCE, $fieldName, $moduleName)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$row          = $this->adb->fetchByAssoc ($result, -1, false);
			$fieldLabel   = getTranslatedString ($row ['modulefieldlabel'], $moduleName);
			$selectClause = "{$row ['relatedmoduletablename']}.{$row ['relatedmodulecolumnname']} AS `{$fieldLabel}`";
			$fromClause   = "{$row ['relatedmoduletablename']} ON {$row ['relatedmoduletablename']}.{$row ['relatedmoduleidcolumnname']}={$row ['moduletablename']}.{$row ['modulecolumnname']}";

			return array (
				'select' => $selectClause,
				'from'   => $fromClause,
			);
		}

		/**
		 * @param array $select
		 * @param array $from
		 * @param string $searchFirstFrom
		 *
		 * @return array
		 */
		private function swapToFirst ($select, $from, $searchFirstFrom) {
			$firstSelectClause = $select[ 0 ];
			$firstFromClause   = $from[ 0 ];
			$totalClauses      = count ($select);

			for ($k = 1; $k <= $totalClauses; $k++) {
				if ($from[ $k ] == $searchFirstFrom) {
					$select[ 0 ]  = $select[ $k ];
					$select[ $k ] = $firstSelectClause;
					$from[ 0 ]    = $from[ $k ];
					$from[ $k ]   = $firstFromClause;
					break;
				}
			}
			return array (
				'select' => $select,
				'from'   => $from,
			);
		}

		/**
		 * @param string $moduleName
		 * @param Users $user
		 * @param boolean $onlyHeaders
		 *
		 * @return PHPExcel
		 * @throws Exception
		 * @throws PHPExcel_Exception
		 */
		public function export ($moduleName, Users $user, $onlyHeaders = false) {
			gc_enable ();
			if (empty ($moduleName)) {
				throw new Exception ('No se ha suministrado el nombre del módulo');
			} else if (empty ($user)) {
				throw new Exception ('No se ha suministrado el usuario');
			}

			$platzillaRootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			if (!file_exists ("{$platzillaRootFolderPath}/modules/{$moduleName}/{$moduleName}.php")) {
				throw new Exception ("El módulo {$moduleName} no es un módulo con campos");
			}

			$allowedFieldsSqlClauses = $this->getAllowedFieldsSqlClauses ($moduleName, $user);
			if (empty ($allowedFieldsSqlClauses)) {
				throw new Exception ("El módulo {$moduleName} no tiene campos configurados");
			}

			$entity        = CRMEntity::getInstance ($moduleName);
			$selectClauses = join (', ', $allowedFieldsSqlClauses ['select']);
			$fromClauses   = join (' ', $allowedFieldsSqlClauses ['from']);
			$whereClause   = !$onlyHeaders ? 'vtiger_crmentity.deleted=0' : '0=1';
			$result        = $this->adb->query (
				"SELECT
					{$selectClauses}
				FROM
					vtiger_crmentity
					{$fromClauses}
					{$entity->getNonAdminAccessControlQuery ($moduleName, $user)}
				WHERE
					{$whereClause}"
			);
			if (!$result) {
				throw new Exception ('Se ha presentado un error al exportar los registros del módulo');
			}

			$moduleLabel = getTranslatedString ($moduleName, $moduleName);
			$now         = date_create ()->format ('Y-m-d h:i:s');
			$objPHPExcel = new PHPExcel ();
			$objPHPExcel->getProperties ()
				->setCreator ('Platzilla')
				->setLastModifiedBy ('Platzilla')
				->setTitle ($moduleLabel)
				->setSubject ($moduleLabel)
				->setDescription ("{$moduleLabel} al {$now}");
			$headers = $this->adb->getFieldsArray ($result);
			if (empty ($headers)) {
				throw new Exception ('No hay resultados para exportar');
			}

			$rowNumber = 1;
			foreach ($headers as $columnNumber => $header) {
				$objPHPExcel->setActiveSheetIndex (0)->setCellValueByColumnAndRow ($columnNumber, $rowNumber, $header);
			}

			if ($this->adb->num_rows ($result) > 0) {
				$rowNumber    = 2;
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$columnNumber = 0;
					foreach ($row as $value) {
						$objPHPExcel->setActiveSheetIndex (0)->setCellValueByColumnAndRow ($columnNumber, $rowNumber, $value);
						$columnNumber++;
					}
					$rowNumber++;
				}
			}
			$objPHPExcel->getActiveSheet ()->setTitle ($moduleLabel);
			$objPHPExcel->setActiveSheetIndex (0);
			return $objPHPExcel;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return XlsxFileExporter
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}
