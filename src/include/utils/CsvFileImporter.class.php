<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/VtlibUtils.php');

	class CsvFileImporter {
		private static $INSTANCE = null;
		private        $adb;
		private        $autoDetectLineEndings;
		private        $fp = null;

		public function __construct (PearDatabase $adb) {
			$this->adb                   = $adb;
			$this->autoDetectLineEndings = ini_get ('auto_detect_line_endings');
			ini_set ('auto_detect_line_endings', true);
		}

		public function __destruct () {
			if ($this->fp) {
				fclose ($this->fp);
			}
			ini_set ('auto_detect_line_endings', $this->autoDetectLineEndings);
		}

		private function getEntityDataFromCsv ($headings, $data) {
			$entityData = array ();
			foreach ($headings as $index => $heading) {
				$entityData [ $heading ] = $data [ $index ];
			}
			return $entityData;
		}

		private function getModuleFields ($moduleName) {
			$result = $this->adb->pquery (
				"SELECT
					f.fieldlabel,
					f.fieldname
				FROM
					vtiger_field f
					INNER JOIN vtiger_blocks b ON b.blockid=f.block
				WHERE
					f.tabid=(SELECT tabid FROM vtiger_tab WHERE name=?) AND
					f.presence IN (0,2) AND
					b.visible=0 AND
					f.columnname<>'createdtime'
				ORDER BY
					b.blockid ASC",
				array ($moduleName)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				unset ($result);
				return null;
			}

			$fieldLabels = array ();
			$fieldNames  = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$fieldLabels [] = $row ['fieldlabel'];
				$fieldNames []  = $row ['fieldname'];
			}
			unset ($result);
			return array (
				'labels' => $fieldLabels,
				'names'  => $fieldNames,
			);
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

		private function getUserId ($userFullName, $userId) {
			$result = $this->adb->pquery ("SELECT id FROM vtiger_users WHERE CONCAT(first_name, ' ', last_name)=?", array ($userFullName));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				unset ($result);
				return $userId;
			}
			$row = $this->adb->fetchByAssoc ($result, -1, false);
			unset ($result);
			return $row ['id'];
		}

		public function import ($csvFilePath, $moduleName, $userId) {
			gc_enable ();
			$this->validateFilePath ($csvFilePath);
			if (!$moduleName) {
				throw new Exception ('No se ha suministrado el nombre del módulo');
			}

			$moduleFields = $this->getModuleFields ($moduleName);
			if ($moduleFields == null) {
				throw new Exception ("El módulo {$moduleName} no tiene campos configurados");
			}

			$fieldLabels = $moduleFields ['labels'];
			$fieldNames  = $moduleFields ['names'];

			$headings = null;
			$this->fp       = fopen ($csvFilePath, 'r');
			while (($data = fgetcsv ($this->fp)) !== false) {
				if ($headings === null) {
					$headings = $data;
				} else {
					/** @var CRMEntity|stdClass $entity */
					$entity     = CRMEntity::getInstance ($moduleName);
					$entityData = $this->getEntityDataFromCsv ($headings, $data);
					foreach ($fieldLabels as $index => &$fieldLabel) {
						if ($fieldNames [ $index ] == 'assigned_user_id') {
							$entity->column_fields [ $fieldNames [ $index ] ] = $this->getUserId (vtlib_purify ($entityData [ $fieldLabel ]), $userId);
						} else {
							$entity->column_fields [ $fieldNames[ $index ] ] = vtlib_purify ($entityData [ $fieldLabel ]);
						}
					}
					$entity->save ($moduleName);
					unset ($entityData);
					unset ($entity);
				}
				unset ($data);
				gc_collect_cycles ();
			}
			fclose ($this->fp);
			$this->fp = null;
		}

		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new CSVFileImporter ($adb);
			}
			return self::$INSTANCE;
		}
	}