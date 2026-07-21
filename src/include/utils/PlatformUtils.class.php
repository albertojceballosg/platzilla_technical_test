<?php
	require_once ('include/CustomFieldUtil.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/PickList/PickListUtils.php');

	/**
	 * Class PlatformUtils
	 *
	 * En esta clase se desarrollan los metodos y utilerias necesarias para la gestion de la plataforma
	 */
	abstract class PlatformUtils {
		/**
		 * @const INSTANCE_PREFIX
		 */
		const INSTANCE_PREFIX = 'appef';

		/**
		 * Crear la imagen
		 *
		 * @param string $filePath
		 *
		 * @return boolean
		 */
		private static function createImage ($filePath) {
			if (empty ($filePath)) {
				return false;
			}
			$type = exif_imagetype ($filePath); // [] if you don't have exif you could use getImageSize()
			switch ($type) {
				case IMAGETYPE_GIF:
					return imagecreatefromgif ($filePath);
				case IMAGETYPE_JPEG:
					return imagecreatefromjpeg ($filePath);
				case IMAGETYPE_PNG:
					return imagecreatefrompng ($filePath);
				case IMAGETYPE_BMP:
					return imagecreatefromwbmp ($filePath);
				default:
					return null;
			}
		}

		/**
		 * Verificar si el campo por defecto es permitido
		 *
		 * @param \CRMEntity $entity
		 * @param string $fieldName Nombre del campo
		 * @param string $fieldUiType Tipo de Ui del campo
		 * @param integer $fieldDisplayType Tipo de despliegue del campo
		 * @param array $nonEditableUiTypes Tipo de campos no editable
		 * @param array $referenceFieldNames Referencia de los nombres del campo
		 *
		 * @return boolean
		 */
		private static function isFieldDefaultPermitted (CRMEntity $entity, $fieldName, $fieldUiType, $fieldDisplayType, $nonEditableUiTypes, $referenceFieldNames) {
			if (
				((isset ($entity->mandatory_fields)) && (!empty ($entity->mandatory_fields)) && (in_array ($fieldName, $entity->mandatory_fields))) ||
				((in_array ($fieldUiType, $nonEditableUiTypes)) || ($fieldDisplayType == 2)) ||
				(in_array ($fieldName, $referenceFieldNames))
			) {
				return false;
			}
			return true;
		}

		/**
		 * Verifica si el campo es obligatorio
		 *
		 * @param boolean $isStrictlyMandatory
		 * @param string $fieldType
		 * @param integer $fieldUiType
		 * @param integer $fieldDisplayType
		 *
		 * @return string
		 */
		private static function isFieldMandatory ($isStrictlyMandatory, $fieldType, $fieldUiType, $fieldDisplayType) {
			if (($fieldUiType == 4) || ($fieldDisplayType == 2)) {
				$mandatory = '3';
			} else if ($isStrictlyMandatory) {
				// fields without which the CRM Record will be inconsistent
				$mandatory = '0';
			} else if ((isset ($fieldType [1])) && ($fieldType [1] == 'M')) {
				// fields which are made mandatory
				$mandatory = '2';
			} else {
				//fields not mandatory
				$mandatory = '1';
			}

			return $mandatory;
		}

		/**
		 * Verifica si el campo es estrictamente obligatorio
		 *
		 * @param \CRMEntity $entity
		 * @param string $fieldName
		 * @param integer $fieldUiType
		 * @param integer $fieldDisplayType
		 * @param array $nonEditableUiTypes
		 *
		 * @return bool
		 */
		private static function isFieldStrictlyMandatory (CRMEntity $entity, $fieldName, $fieldUiType, $fieldDisplayType, $nonEditableUiTypes) {
			if (
				((isset ($entity->mandatory_fields)) && (!empty ($entity->mandatory_fields)) && (in_array ($fieldName, $entity->mandatory_fields))) ||
				(in_array ($fieldUiType, $nonEditableUiTypes)) ||
				($fieldDisplayType == 2)
			) {
				return true;
			}
			return false;
		}

		/**
		 * Obtener los bloques de campos en CRMEntity
		 *
		 * @param \PearDatabase $adb
		 * @param int $moduleId
		 * @param int $blockId
		 *
		 * @return array
		 */
		private static function getBlockFieldsNotInCrmEntity (PearDatabase $adb, $moduleId, $blockId) {
			$result = $adb->pquery ("SELECT * FROM vtiger_field WHERE tabid=? AND block=? AND tablename NOT IN ('vtiger_crmentity') ORDER BY sequence", array ($moduleId, $blockId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$fields [] = $row;
			}
			return $fields;
		}

		/**
		 * Obtener el sql del bloque y los parametros
		 *
		 * @param \PearDatabase $adb
		 * @param int $moduleId
		 *
		 * @return array
		 */
		private static function getBlocksSqlQueryAndParameters (PearDatabase $adb, $moduleId) {
			$result = $adb->query ('SHOW COLUMNS FROM vtiger_blocks_properties');
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$joinBlockProperties = 'LEFT JOIN vtiger_blocks_properties bp ON bp.blockid=b.blockid';
				$propertiesField     = ', bp.blocktype';
			} else {
				$joinBlockProperties = '';
				$propertiesField     = '';
			}
			$sql = "SELECT
						b.*,
						t.presence AS tabpresence
						$propertiesField
					FROM
						vtiger_blocks b
						INNER JOIN vtiger_tab t ON t.tabid=b.tabid
						$joinBlockProperties
					WHERE
						b.tabid=? AND
						t.presence=0
					ORDER BY
						sequence";
			return array (
				'sql'        => $sql,
				'parameters' => array ($moduleId),
			);
		}

		/**
		 * Obtener los valores de CRMEntity
		 *
		 * @param \PearDatabase $adb
		 * @param array $tables
		 * @param int $entityId
		 *
		 * @return array|null
		 */
		private static function getCrmEntityDatabaseValues (PearDatabase $adb, $tables, $entityId) {
			$databaseValues = array ();
			$result         = $adb->pquery ('SELECT * FROM vtiger_crmentity crme WHERE crme.crmid=?', array ($entityId));
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$databaseValues ['vtiger_crmentity'] = $adb->fetchByAssoc ($result, -1, false);
				if (!empty ($databaseValues ['vtiger_crmentity']['deleted'])) {
					return null;
				}
			}

			unset ($tables ['vtiger_crmentity']);
			foreach ($tables as $tableName => $indexColumn) {
				$result = $adb->pquery ("SELECT * FROM {$tableName} WHERE {$indexColumn}=?", array ($entityId));
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					continue;
				}
				$databaseValues [ $tableName ] = $adb->fetchByAssoc ($result, -1, false);
			}
			return $databaseValues;
		}

		/**
		 * Obtener la longitud del campo del tipo de dato
		 *
		 * @param string $typeOfData
		 *
		 * @return mixed
		 */
		private static function getFieldLengthFromTypeOfData ($typeOfData) {
			$typeOfData = explode ('~', $typeOfData);
			if ($typeOfData [2] == 'LE') {
				return $typeOfData [3];
			}

			$dummy = explode (',', $typeOfData [2]);
			return $dummy [0];
		}

		/**
		 * Obtener la precision del campo del tipo de dato
		 *
		 * @param string $typeOfData
		 *
		 * @return string
		 */
		private static function getFieldPrecisionFromTypeOfData ($typeOfData) {
			$typeOfData = explode ('~', $typeOfData);
			if ($typeOfData [2] == 'LE') {
				return '';
			}

			$dummy = explode (',', $typeOfData [2]);
			return $dummy [1];
		}

		/**
		 * Obtener los valores del campo
		 *
		 * @param array $uiType
		 * @param string $fieldName
		 *
		 * @return string
		 */
		private static function getFieldValues ($uiType, $fieldName) {
			return in_array ($uiType, array ('15', '16', '33')) ? join ("\n", getAllPickListValues ($fieldName)) : '';
		}

		/**
		 * Obtener los SQL de los campos y los parametros
		 *
		 * @param string $moduleName
		 * @param int $blockId
		 *
		 * @return array
		 */
		private static function getFieldsSqlQueryAndParameters ($moduleName, $blockId) {
			if (($moduleName != 'Invoices') && ($moduleName != 'Quotes') && ($moduleName != 'SalesOrder') && ($moduleName != 'Invoice')) {
				$sql        = 'SELECT * FROM vtiger_field WHERE block=? AND displaytype IN (1,2,4) ORDER BY sequence';
				$parameters = array ($blockId);
			} else {
				$sql        = "SELECT * FROM vtiger_field WHERE block=? AND fieldlabel<>'Total' AND fieldlabel<>'Sub Total' AND fieldlabel<>'Tax' AND displaytype IN (1,2,4) ORDER BY sequence";
				$parameters = array ($blockId);
			}
			return array (
				'sql'        => $sql,
				'parameters' => $parameters,
			);
		}

		/**
		 * Obtener los valores de la lista de seleccion global
		 *
		 * @param \PearDatabase $adb
		 * @param string $picklistName
		 *
		 * @return array
		 */
		private static function getGlobalPicklistValues (PearDatabase $adb, $picklistName) {
			if (empty ($picklistName)) {
				return array ();
			}

			$picklist = GlobalPicklistManager::getInstance ($adb)->fetchPicklistByName ($picklistName);
			if (empty ($picklist)) {
				return array ();
			}

			$picklistValues = $picklist->getValues ();
			if (empty ($picklistValues)) {
				return array ();
			}

			$values = array ();
			foreach ($picklistValues as $picklistValue) {
				$values [ $picklistValue->getValue () ] = $picklistValue->getValue ();
			}
			return $values;
		}

		/**
		 * Obtener los datos de los campos en el bloque
		 *
		 * @param \PearDatabase $adb
		 * @param \CRMEntity $entity
		 * @param string $moduleName
		 * @param int $blockId
		 * @param int $moduleId
		 * @param array $referenceFieldNames
		 * @param array $nonEditableUiTypes
		 *
		 * @return array|null
		 */
		private static function getFieldsDataInBlock (PearDatabase $adb, CRMEntity $entity, $moduleName, $blockId, $moduleId, $referenceFieldNames, $nonEditableUiTypes) {
			$queryAndParameters = self::getFieldsSqlQueryAndParameters ($moduleName, $blockId);
			$result             = $adb->pquery ($queryAndParameters ['sql'], $queryAndParameters ['parameters']);
			if ($adb->num_rows ($result) == 0) {
				return null;
			}

			$visibleFields = array ();
			$hiddenFields  = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$strictlyMandatory = self::isFieldStrictlyMandatory ($entity, $row ['fieldname'], $row ['uitype'], $row ['displaytype'], $nonEditableUiTypes);
				$fieldType         = explode ('~', $row ['typeofdata']);

				if (in_array ($row ['uitype'], array (FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_PICKLIST))) {
					$picklistValues = getAllPickListValues ($row ['fieldname']);
				} else if ($row ['uitype'] == FieldInterface::UI_TYPE_GLOBAL_PICKLIST) {
					$picklistValues = self::getGlobalPicklistValues ($adb, $row ['fieldname']);
				} else {
					$picklistValues = array ();
				}
				$fieldInformation = array (
					'blockid'         => $blockId,
					'columnname'      => $row ['columnname'],
					'customfieldflag' => substr_count ($row ['fieldname'], 'cf_'),
					'defaultvalue'    => array (
						'_allvalues' => $picklistValues,
						'permitted'  => self::isFieldDefaultPermitted ($entity, $row ['fieldname'], $row ['uitype'], $row ['displaytype'], $nonEditableUiTypes, $referenceFieldNames),
						'value'      => (!empty ($row ['defaultvalue'])) && (in_array ($row ['uitype'], array ('5', '6', '23'))) ? getValidDisplayDate ($row ['defaultvalue']) : $row ['defaultvalue'],
					),
					'displaytype'     => $row ['displaytype'],
					'fieldlabel'      => $row ['fieldlabel'],
					'fieldselect'     => $row ['fieldid'],
					'fieldtype'       => $fieldType [1],
					'label'           => getTranslatedString ($row ['fieldlabel'], $moduleName),
					'mandatory'       => self::isFieldMandatory ($strictlyMandatory, $fieldType, $row ['uitype'], $row ['displaytype']),
					'massedit'        => $row ['masseditable'],
					'name'            => $row ['fieldname'],
					'presence'        => $row ['presence'],
					'quickcreate'     => $row ['quickcreate'],
					'tabid'           => $moduleId,
					'type'            => getCustomFieldTypeName ($row ['uitype']),
					'typeofdata'      => $row ['typeofdata'],
					'uitype'          => $row ['uitype'],
				);

				if (in_array ($row ['presence'], array (0, 2))) {
					$fieldInformation ['no'] = count ($visibleFields);
					$visibleFields []        = $fieldInformation;
				} else {
					$fieldInformation ['no'] = count ($hiddenFields);
					$hiddenFields []         = $fieldInformation;
				}
			}

			return array (
				'hidden'  => $hiddenFields,
				'visible' => $visibleFields,
			);
		}

		/**
		 * Obtener los datos de los campos no en el bloque
		 *
		 * @param \PearDatabase $adb
		 * @param string $moduleName
		 * @param int $blockId
		 * @param int $moduleId
		 *
		 * @return array|null
		 */
		private static function getFieldsDataNotInBlock (PearDatabase $adb, $moduleName, $blockId, $moduleId) {
			$result = $adb->pquery (
				"SELECT
					fieldid,
					fieldlabel
				FROM
					vtiger_field f
					INNER JOIN vtiger_blocks b ON b.blockid=f.block
				WHERE
					f.block<>? AND
					b.blocklabel NOT IN ('LBL_TICKET_RESOLUTION', 'LBL_COMMENTS', 'LBL_COMMENT_INFORMATION') AND
					f.tabid=? AND
					f.displaytype IN (1,2,4)
				ORDER BY
					f.sequence",
				array ($blockId, $moduleId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$fields = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$fields [] = array (
					'fieldid'    => $row ['fieldid'],
					'fieldlabel' => getTranslatedString ($row ['fieldlabel'], $moduleName),
				);
			}
			return $fields;
		}

		/**
		 * Obtener los datos del modulo en el bloque
		 *
		 * @param \PearDatabase $adb
		 * @param int $moduleId
		 *
		 * @return array
		 */
		private static function getModuleBlocksData (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_blocks WHERE tabid=? ORDER BY sequence', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$blocks = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$blocks [] = $row;
			}
			return $blocks;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return null
		 */
		private static function getModulePrefix (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT prefix FROM vtiger_modentity_num WHERE semodule=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['prefix'];
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 * @param $uiType
		 *
		 * @return null|string
		 */
		private static function getModulePrefixFromUiType (PearDatabase $adb, $moduleName, $uiType) {
			return ($uiType == 4) ? self::getModulePrefix ($adb, $moduleName) : '';
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleId
		 *
		 * @return array
		 */
		private static function getModuleRelatedLists (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=? ORDER BY sequence', array ($moduleId));
			$lists  = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$lists [] = $row;
			}
			return $lists;
		}

		/**
		 * @param $uiType
		 *
		 * @return string
		 */
		private static function getModuleSequenceFromUiType ($uiType) {
			return ($uiType == 4) ? '001' : '';
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $fieldId
		 *
		 * @return null
		 */
		private static function getRelatedModuleName (PearDatabase $adb, $fieldId) {
			$result = $adb->pquery ('SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid=?', array ($fieldId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result);
			return $row ['relmodule'];
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $fieldId
		 * @param $uiType
		 *
		 * @return null|string
		 */
		private static function getRelatedModuleNameFromUiType (PearDatabase $adb, $fieldId, $uiType) {
			return ($uiType == 10) ? self::getRelatedModuleName ($adb, $fieldId) : '-';
		}

		/**
		 * @param $word
		 *
		 * @return bool|string
		 */
		private static function getSingleFromPlural ($word) {
			$length = strlen ($word);
			$ending = substr ($word, ($length - 2), 2);
			if (($ending == 'es') || ($ending == 'os')) {
				$singular = substr ($word, 0, ($length - 2));
			} else {
				$ending = substr ($word, ($length - 1), 1);
				if ($ending == 's') {
					$singular = substr ($word, 0, ($length - 1));
				} else {
					$singular = $word;
				}
			}
			return $singular;
		}

		/**
		 * @param \Users $currentUser
		 * @param $moduleName
		 *
		 * @return array
		 */
		private static function getReferenceFieldNames (Users $currentUser, $moduleName) {
			require_once ('include/Webservices/Utils.php');
			$handler = vtws_getModuleHandlerFromName ($moduleName, $currentUser);
			/** @var EntityMeta $meta */
			$meta = $handler->getMeta ();
			return array_keys ($meta->getReferenceFieldDetails ());
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 * @param string $viewName
		 *
		 * @return array
		 */
		private static function getViewFilterColumnNames (PearDatabase $adb, $moduleName, $viewName = 'All') {
			$result = $adb->pquery (
				'SELECT
					cl.*
				FROM
					vtiger_cvcolumnlist cl
					INNER JOIN vtiger_customview cv ON cv.cvid=cl.cvid
				WHERE
					cv.entitytype=? AND
					cv.viewname=?
				ORDER BY
					cl.columnindex',
				array ($moduleName, $viewName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$columnNames = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$columnData     = explode (':', $row ['columnname']);
				$columnNames [] = $columnData [1];
			}
			return $columnNames;
		}

		/**
		 * Obtener las columnas existentes de una tabla
		 * @param \PearDatabase $adb
		 * @param string $tableName
		 * @return array
		 */
		private static function getTableColumns (PearDatabase $adb, $tableName) {
			$columns = array ();
			$result  = $adb->query ("SHOW COLUMNS FROM {$tableName}");
			if ($result && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$columns [] = $row ['Field'];
				}
			}
			return $columns;
		}

		/**
		 * Extraer nombres de columnas referenciadas en una cláusula WHERE
	 * @param string $where
		 * @return array
		 */
		private static function extractColumnsFromWhere ($where) {
			$columns = array ();
			// Reemplazar cadenas entre comillas para evitar falsos positivos
			$where = preg_replace ('/(["\'])(?:(?=(\\\\?))\2.)*?\1/', '0', $where);
			// Extraer columnas con prefijo tq.
			if (preg_match_all ('/\btq\.([a-zA-Z_][a-zA-Z0-9_]*)\b/', $where, $matches)) {
				foreach ($matches [1] as $column) {
					$columns [] = $column;
				}
			}
			return array_unique ($columns);
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $buttons
		 * @param $moduleName
		 * @param $arguments
		 *
		 * @return bool
		 */
		private static function isButtonVisibility (PearDatabase $adb, $buttons, $moduleName, $arguments) {
			$where  = json_decode ($buttons['sqlvisibility']);
			$entity = self::getCrmEntity ($adb, $moduleName);
			if (!empty ($arguments) && !empty ($entity)) {
				$where  = (($arguments['action'] == 'DetailView') || isset ($arguments ['isActionButton'])) ? " ({$where}) AND ({$entity->table_index} = {$arguments['record']})" : $where;

				// Validar que las columnas referenciadas en el WHERE existan en la tabla
				$whereColumns = self::extractColumnsFromWhere ($where);
				if (!empty ($whereColumns)) {
					$tableColumns = self::getTableColumns ($adb, $entity->table_name);
					foreach ($whereColumns as $column) {
						if (!in_array ($column, $tableColumns)) {
							// Columna inexistente: no mostrar el botón para evitar error SQL
							return false;
						}
					}
				}

				$result = $adb->query (
					"SELECT *
					FROM
					{$entity->table_name} tq
				  	INNER JOIN vtiger_crmentity crm ON crm.crmid = tq.{$entity->table_index}
					WHERE
					 crm.deleted = 0
					AND
					 ({$where})"
				);
				if (($result) && ($adb->num_rows ($result) > 0)) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param CRMEntity $entity
		 * @param integer $entityId
		 */
		private static function setGridFieldValues (PearDatabase $adb, $moduleName, $entity, $entityId) {
			$gridFieldsData = GridFieldUtils::getAvailableGridFields ($adb, $moduleName);
			if (empty ($gridFieldsData)) {
				return;
			}
			$gridFieldNames = array_keys ($gridFieldsData);
			foreach ($gridFieldNames as $gridFieldName) {
				$entity->column_fields [ $gridFieldName ] = GridFieldUtils::getGridValues ($adb, $moduleName, $gridFieldName, $entityId);
			}
		}

		/**
		 * @param $platformName
		 * @param $userId
		 * @param $instanceCode
		 */
		public static function createPermissionFiles ($platformName, $userId, $instanceCode) {
			global $adb, $current_user;
			$oldGlobalAdb = !empty ($adb) ? clone ($adb) : null;
			$adbm         = AdbManager::getInstance ();
			$adb          = !empty ($instanceCode) ? $adbm->getTargetInstanceAdb ($instanceCode) : $adbm->getMasterAdb ();

			$oldSessionPlatformName         = isset ($_SESSION ['plat']) ? $_SESSION ['plat'] : null;
			$oldSessionPlatformInstanceName = isset ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : '';
			$_SESSION ['plat']              = $platformName;
			$_SESSION ['platInstancia']     = $instanceCode;

			$oldGlobalCurrentUser = !empty ($current_user) ? clone ($current_user) : null;
			$current_user         = CRMEntity::getInstance ('Users');
			$current_user->retrieve_entity_info ($userId, 'Users');
			create_tab_data_file ();
			create_parenttab_data_file ();
			createUserPrivilegesfile ($userId);
			createUserSharingPrivilegesfile ($userId);
			unset ($adb);
			$adb = $oldGlobalAdb;
			unset ($current_user);
			$current_user               = $oldGlobalCurrentUser;
			$_SESSION ['plat']          = $oldSessionPlatformName;
			$_SESSION ['platInstancia'] = $oldSessionPlatformInstanceName;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $applicationId
		 *
		 * @return array|mixed|null
		 */
		public static function getApplicationData (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE config_applicationsid=?', array ($applicationId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			return $adb->fetchByAssoc ($result, -1, false);
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $applicationCode
		 * @param $applicationName
		 *
		 * @return array|mixed|null
		 */
		public static function getApplicationDataByCodeOrName (PearDatabase $adb, $applicationCode, $applicationName) {
			$result = $adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_code=? OR app_name=?', array ($applicationCode, $applicationName));
			return ($result) && ($adb->num_rows ($result) > 0) ? $adb->fetchByAssoc ($result, -1, false) : null;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array|null
		 */
		public static function getApplicationsByModuleName (PearDatabase $adb, $moduleName) {
			$moduleId = getTabid ($moduleName);
			$result   = $adb->pquery (
				'SELECT
					a.*
				FROM
					vtiger_config_applications a
					INNER JOIN vtiger_configapps_tab cat ON cat.config_applicationsid=a.config_applicationsid
				WHERE
					a.app_status=? AND
					cat.tabid=?',
				array ('Activa', $moduleId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$applications = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$applications [] = $row;
			}
			return $applications;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $roleId
		 * @param $moduleName
		 *
		 * @return array|null
		 */
		public static function getApplicationsByUserRole (PearDatabase $adb, $roleId, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					p.*
				FROM
					vtiger_profile p
					INNER JOIN vtiger_role2profile r2p ON r2p.profileid=p.profileid AND r2p.roleid=?
				WHERE
					p.profileid=1',
				array ($roleId)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				return self::getApplicationsByModuleName ($adb, $moduleName);
			}

			$moduleId = getTabid ($moduleName);
			$result   = $adb->pquery (
				'SELECT
					a.*
				FROM
					vtiger_role2profile r2p
					INNER JOIN vtiger_config_applications a ON a.app_profile=r2p.profileid AND a.app_status=?
					INNER JOIN vtiger_configapps_tab cat ON cat.config_applicationsid=a.config_applicationsid
				WHERE
					cat.tabid=? AND
					r2p.roleid=?',
				array ('Activa', $moduleId, $roleId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$applications = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$applications [] = $row;
			}
			return $applications;
		}

		/**
		 * @param \PearDatabase $adb
		 *
		 * @return array
		 */
		public static function getAvailableParentModuleNames (PearDatabase $adb) {
			$result = $adb->query ('SELECT parenttab_label FROM vtiger_parenttab WHERE visible=0 AND avaliable=1 ORDER BY sequence');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row ['parenttab_label'];
			}
			return $modules;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 * @param $action
		 * @param array|null $arguments
		 *
		 * @return array|null
		 */
		public static function getCustomButtons (PearDatabase $adb, $moduleName, $action, array $arguments = null) {
			$result = $adb->pquery ('SELECT DISTINCT cb.* FROM vtiger_custombuttons cb WHERE cb.active=1 AND cb.module=? AND cb.action=?', array ($moduleName, $action));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$buttons = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$swithButton = true;
				if (!empty($row['sqlvisibility']) && $row['sqlvisibility'] != null) {
					$swithButton = self::isButtonVisibility ($adb, $row, $moduleName, $arguments);
				}
				if ($swithButton) {
					if (!empty ($arguments)) {
						foreach ($arguments as $parameterName => $parameterValue) {
							if (!empty ($row ['onclick'])) {
								$row ['onclick'] = str_replace ("[{$parameterName}]", $parameterValue, $row ['onclick']);
							} else if (!empty ($row ['link'])) {
								$row ['link'] = str_replace ("[{$parameterName}]", $parameterValue, $row ['link']);
							}
						}
					}
					$buttons [] = $row;
				}
			}
			return $buttons;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 * @param null $entityId
		 *
		 * @return \CRMEntity|null|\stdClass
		 */
		public static function getCrmEntity (PearDatabase $adb, $moduleName, $entityId = null) {
			$className = $moduleName != 'Calendar' ? $moduleName : 'Activity';
			$moduleFilePath = "modules/{$moduleName}/{$className}.php";
			if (!file_exists (__DIR__ . "/../../{$moduleFilePath}")) {
				return null;
			}
			$moduleId = self::getModuleId ($adb, $moduleName);
			if (empty ($moduleId)) {
				return null;
			}
			require_once ($moduleFilePath);
			/** @var CRMEntity|stdClass $entity */
			$entity     = new $className ();
			$entity->db = $adb;

			if (empty ($entityId)) {
				return $entity;
			}
			$tables         = $entity->tab_name_index;
			$databaseValues = self::getCrmEntityDatabaseValues ($adb, $tables, $entityId);
			if (empty ($databaseValues)) {
				return $entity;
			}
			$result = $adb->pquery (
				'SELECT f.fieldname, f.columnname, f.tablename FROM vtiger_field f WHERE f.presence IN (0, 2) AND f.tabid=?',
				array ($moduleId)
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$tableName  = $row ['tablename'];
					$fieldName  = $row ['fieldname'];
					$columnName = $row ['columnname'];
					if (isset ($databaseValues [ $tableName ][ $columnName ])) {
						$entity->column_fields [ $fieldName ] = $databaseValues [ $tableName ][ $columnName ];
						if ($moduleName == 'Users') {
							$entity->$fieldName = $databaseValues [ $tableName ][ $columnName ];
						}
					} else {
						$entity->column_fields [ $fieldName ] = '';
						if ($moduleName == 'Users') {
							$entity->$fieldName = null;
						}
					}
				}
			}
			self::setGridFieldValues ($adb, $moduleName, $entity, $entityId);
			$entity->column_fields ['record_id']     = $entityId;
			$entity->column_fields ['record_module'] = $moduleName;
			$entity->id                              = $entityId;
			return $entity;
		}

		/**
		 * @param \PearDatabase $adb
		 *
		 * @return array
		 */
		public static function getDuplicatableApplicationsData (PearDatabase $adb) {
			$result = $adb->query ("SELECT * FROM vtiger_config_applications WHERE app_status='Activa' AND config_applicationsid<>1");
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$applications = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$applications [] = $row;
			}
			return $applications;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $applicationId
		 *
		 * @return array
		 */
		public static function getDuplicatableApplicationModulesData (PearDatabase $adb, $applicationId) {
			$result = $adb->pquery (
				"SELECT
					t.tabid,
					t.name,
					t.tablabel
				FROM
					vtiger_tab t
					INNER JOIN vtiger_configapps_tab cat ON cat.tabid=t.tabid
					INNER JOIN vtiger_config_applications ca ON ca.config_applicationsid=cat.config_applicationsid
					INNER JOIN vtiger_entityname en ON en.tabid=t.tabid
				WHERE
					ca.app_status='Activa' AND
					cat.config_applicationsid=?",
				array ($applicationId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row;
			}
			return $modules;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleId
		 *
		 * @return array|mixed|null
		 */
		public static function getEntityData (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_entityname WHERE tabid=?', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			return $adb->fetchByAssoc ($result);
		}

		/**
		 * @param \PearDatabase $adb
		 * @param \Users $currentUser
		 * @param $moduleName
		 * @param array|null $activeApplications
		 *
		 * @return array
		 */
		public static function getFieldListEntries (PearDatabase $adb, Users $currentUser, $moduleName, array $activeApplications = null) {
			$moduleId = getTabid ($moduleName);

			$queryAndParameters = self::getBlocksSqlQueryAndParameters ($adb, $moduleId);
			$result             = $adb->pquery ($queryAndParameters ['sql'], $queryAndParameters ['parameters']);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$entity              = CRMEntity::getInstance ($moduleName);
			$nonEditableUiTypes  = array ('4', '70');
			$referenceFieldNames = self::getReferenceFieldNames ($currentUser, $moduleName);
			if (!empty ($activeApplications)) {
				$activeApplicationIds = array ();
				foreach ($activeApplications as $activeApplication) {
					$activeApplicationIds [] = $activeApplication ['config_applicationsid'];
				}
			} else {
				$activeApplicationIds = null;
			}
			$entries = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				if (!$row ['blocklabel']) {
					continue;
				}
				$entry = array (
					'blockid'        => $row ['blockid'],
					'blocklabel'     => getTranslatedString ($row ['blocklabel'], $moduleName),
					'blockselect'    => $row ['blockid'],
					'blocktype'      => intval ($row ['blocktype']),
					'display_status' => $row ['display_status'],
					'hascustomtable' => $entity->customFieldTable,
					'iscustom'       => $row ['iscustom'],
					'module'         => $moduleName,
					'sequence'       => $row ['sequence'],
					'tabid'          => $moduleId,
					'tabpresence'    => $row ['tabpresence'],
				);

				$fieldsInBlock = self::getFieldsDataInBlock ($adb, $entity, $moduleName, $row ['blockid'], $moduleId, $referenceFieldNames, $nonEditableUiTypes);
				if ($fieldsInBlock) {
					$entry ['field']        = $fieldsInBlock ['visible'];
					$entry ['hidden_count'] = count ($fieldsInBlock ['hidden']);
					$entry ['hiddenfield']  = $fieldsInBlock ['hidden'];
					$entry ['no']           = count ($fieldsInBlock ['visible']);
				} else {
					$entry ['field']       = null;
					$entry ['hiddenfield'] = null;
					$entry ['no']          = 0;
				}

				$fieldsNotInBlock = self::getFieldsDataNotInBlock ($adb, $moduleName, $row ['blockid'], $moduleId);
				if ($fieldsNotInBlock) {
					$entry ['movefield']      = $fieldsNotInBlock;
					$entry ['movefieldcount'] = count ($fieldsNotInBlock);
				} else {
					$entry ['movefield']      = null;
					$entry ['movefieldcount'] = 0;
				}
				$entries [] = $entry;
			}
			return $entries;
		}

		/**
		 * @param $filePath
		 *
		 * @return bool|null|string
		 */
		public static function getImageExtension ($filePath) {
			if (empty ($filePath)) {
				return false;
			}
			$type = exif_imagetype ($filePath); // [] if you don't have exif you could use getImageSize()
			switch ($type) {
				case IMAGETYPE_GIF:
					return 'gif';
				case IMAGETYPE_JPEG:
					return 'jpg';
				case IMAGETYPE_PNG:
					return 'png';
				case IMAGETYPE_BMP:
					return 'bmp';
				default:
					return null;
			}
		}

		/**
		 * @return array|null
		 */
		public static function getValidInstances () {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->query ('SELECT i.* FROM vtiger_instances i ORDER BY i.name');
			if ($adb->num_rows ($result) > 0) {
				$instances = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$dummy = $adb->pquery ('SHOW DATABASES LIKE ?', array ("pg_crm_{$row ['code']}"));
					$hasDatabase = ($adb->num_rows ($dummy) > 0);
					if ($dummy instanceof ADORecordSet) {
						$dummy->Close ();
						$dummy = null;
					}
					if ($hasDatabase) {
						$instances [] = $row;
					}
				}
			} else {
				$instances = null;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $instances;
		}

		/**
		 * @param \PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function getMenuData (PearDatabase $adb) {
			$result = $adb->query ('SELECT pt.parenttabid, pt.parenttab_label FROM vtiger_parenttab pt');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$menuData = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$menuData [] = $row;
			}
			return $menuData;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return array|mixed|null
		 */
		public static function getMenuDataByModuleName (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT DISTINCT
					pt.parenttabid,
					pt.parenttab_label
				FROM
					vtiger_parenttab pt
					INNER JOIN vtiger_parenttabrel ptr ON ptr.parenttabid=pt.parenttabid
					INNER JOIN vtiger_tab t ON t.tabid=ptr.tabid
				WHERE
					t.name=?',
				array ($moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			return $adb->fetchByAssoc ($result, -1, false);
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return null
		 */
		public static function getModuleAdminVisibility (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['isvisibleinadmin'];
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return null
		 */
		public static function getModuleId (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT tabid FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['tabid'];
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleId
		 *
		 * @return null
		 */
		public static function getModuleLabel (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery ('SELECT tablabel FROM vtiger_tab WHERE tabid=?', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['tablabel'];
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleId
		 *
		 * @return string
		 */
		public static function getModuleMenuLabel (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery (
				'SELECT parenttab_label FROM vtiger_parenttabrel ptr JOIN vtiger_parenttab pt ON pt.parenttabid=ptr.parenttabid WHERE tabid=?',
				array ($moduleId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return 'Lo último';
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['parenttab_label'];
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleId
		 *
		 * @return null
		 */
		public static function getModuleName (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery ('SELECT name FROM vtiger_tab WHERE tabid=?', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['name'];
		}

		/**
		 * @param \PearDatabase $adb
		 *
		 * @return array
		 */
		public static function getVisibleEntityModulesData (PearDatabase $adb) {
			$result = $adb->query ('SELECT t.* FROM vtiger_tab t INNER JOIN vtiger_entityname en ON en.tabid=t.tabid WHERE t.presence=0');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modules [] = $row;
			}
			usort (
				$modules,
				function ($moduleA, $moduleB) {
					if ($moduleA ['tablabel'] < $moduleB ['tablabel']) {
						return -1;
					} else if ($moduleA ['tablabel'] == $moduleB ['tablabel']) {
						return 0;
					} else {
						return 1;
					}
				}
			);
			return $modules;
		}

		/**
		 * @param $email
		 *
		 * @return bool
		 */
		public static function isInstanceEmailRegistered ($email) {
			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery (
				'SELECT
					*
				FROM
					vtiger_instanceusers iu
					INNER JOIN vtiger_instances i ON i.code=iu.instancecode
				WHERE
					iu.username=?',
				array ($email)
			);
			return $adb->num_rows ($result) > 0;
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleName
		 *
		 * @return bool
		 */
		public static function isModuleEnabled (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE presence IN (0, 2) AND name=?', array ($moduleName));
			return ($result) && ($adb->num_rows ($result) > 0);
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $moduleId
		 *
		 * @return bool
		 */
		public static function isPlatzillaModule (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery ('SELECT isplatzilla FROM vtiger_tab WHERE tabid=?', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return false;
			}

			$row = $adb->fetchByAssoc ($result);
			return $row ['isplatzilla'] == 1;
		}

		/**
		 * @param $imageUrl
		 * @param $thumbnailUrl
		 */
		public static function resizeImage ($imageUrl, $thumbnailUrl) {
			// Creamos una variable imagen a partir de la imagen original
			$originalImage = self::createImage ($imageUrl);
			if (!$originalImage) {
				return;
			}
			// Se define el maximo ancho y alto que tendra la imagen final
			$maxWidth  = 200;
			$maxHeight = 200;
			// Definimos la calidad de la imagen final
			$quality = 95;
			// Ancho y alto de la imagen original
			list($width, $height) = getimagesize ($imageUrl);
			// Se calcula ancho y alto de la imagen final
			$xRatio = ($maxWidth / $width);
			$yRatio = ($maxHeight / $height);
			if (($width <= $maxWidth) && ($height <= $maxHeight)) { //Si ancho
				// Si el ancho y el alto de la imagen no superan los maximos, ancho final y alto final son los que tiene actualmente
				$finalWidth  = $width;
				$finalHeight = $height;
			} else if (($xRatio * $height) < $maxHeight) {
				// si proporcion horizontal*alto mayor que el alto maximo, alto final es alto por la proporcion horizontal
				// es decir, le quitamos al ancho, la misma proporcion que le quitamos al alto
				$finalHeight = ceil ($xRatio * $height);
				$finalWidth  = $maxWidth;
			} else {
				// Igual que antes pero a la inversa
				$finalWidth  = ceil ($yRatio * $width);
				$finalHeight = $maxHeight;
			}
			// Creamos una imagen en blanco de tamaño $finalWidth  por $finalHeight .
			$tmp = imagecreatetruecolor ($finalWidth, $finalHeight);
			// FIX FOR BACKGROUND
			$white = imagecolorallocate ($tmp, 255, 255, 255);
			imagefill ($tmp, 0, 0, $white);
			// Copiamos $originalImage sobre la imagen que acabamos de crear en blanco ($tmp)
			imagecopyresampled ($tmp, $originalImage, 0, 0, 0, 0, $finalWidth, $finalHeight, $width, $height);
			// Se destruye variable $originalImage para liberar memoria
			imagedestroy ($originalImage);
			// Se crea la imagen final en el directorio indicado
			imagejpeg ($tmp, $thumbnailUrl, $quality);
		}

		/**
		 * @param \PearDatabase $adb
		 * @param $oldModuleId
		 * @param $newModuleName
		 * @param $newModuleLabel
		 * @param $parentModuleName
		 */
		public static function initializeModuleDuplicationSessionVariables (PearDatabase $adb, $oldModuleId, $newModuleName, $newModuleLabel, $parentModuleName) {
			$i             = 0;
			$oldModuleName = self::getModuleName ($adb, $oldModuleId);
			$entityData    = self::getEntityData ($adb, $oldModuleId);
			$relatedLists  = self::getModuleRelatedLists ($adb, $oldModuleId);
			$blocksData    = self::getModuleBlocksData ($adb, $oldModuleId);
			foreach ($blocksData as $blockIndex => $blockData) {
				$_SESSION ['nombreBloque'][ $blockIndex ]      = $blockData ['blocklabel'];
				$_SESSION ['visibilidadBloque'][ $blockIndex ] = $blockData ['display_status'];
				$_SESSION ['numBloque'][ $i ]                  = $blockIndex;

				$fieldsData = self::getBlockFieldsNotInCrmEntity ($adb, $oldModuleId, $blockData ['blockid']);
				foreach ($fieldsData as $fieldData) {
					$_SESSION ['numeroBloqueCampo'][ $i ] = $blockIndex;
					$_SESSION ['nombreCampo'][ $i ]       = $fieldData ['columnname'];
					$_SESSION ['etiquetaCampo'][ $i ]     = $fieldData ['fieldlabel'];
					$_SESSION ['prefijoCampo'][ $i ]      = self::getModulePrefixFromUiType ($adb, $oldModuleName, $fieldData ['uitype']);
					$_SESSION ['secuenciaCampo'][ $i ]    = self::getModuleSequenceFromUiType ($fieldData ['uitype']);
					$_SESSION ['valoresCampo'][ $i ]      = self::getFieldValues ($fieldData ['uitype'], $fieldData ['columnname']);
					$_SESSION ['tipoCampo'][ $i ]         = $fieldData ['uitype'];
					$_SESSION ['moduloCampo'][ $i ]       = self::getRelatedModuleNameFromUiType ($adb, $fieldData ['fieldid'], $fieldData ['uitype']);
					$_SESSION ['tamanoCampo'][ $i ]       = self::getFieldLengthFromTypeOfData ($fieldData ['typeofdata']);
					$_SESSION ['precisionCampo'][ $i ]    = self::getFieldPrecisionFromTypeOfData ($fieldData ['typeofdata']);
					$i++;
				}
			}
			$_SESSION ['campoIdentificador']            = $entityData ['fieldname'];
			$_SESSION ['columnasFiltro']                = self::getViewFilterColumnNames ($adb, $oldModuleName);
			$_SESSION ['nombrePublicoADuplicar']        = ucwords (self::getSingleFromPlural ($oldModuleName));
			$_SESSION ['nombrePublicoSingleLanguage']   = self::getSingleFromPlural ($newModuleLabel);
			$_SESSION ['labelModulos']                  = array ();
			$_SESSION ['listaModulos']                  = array ();
			$_SESSION ['campoOrigenRelacionAutomatica'] = '';
			$_SESSION ['campoRelacionAutomatica']       = '';
			$i                                          = 0;
			$_SESSION ['labelModulos'][0]               = '';
			$_SESSION ['listaModulos'][0]               = '-';
			foreach ($relatedLists as $relatedList) {
				$i++;
				$_SESSION ['labelModulos'][ $i ] = $relatedList ['label'];
				$_SESSION ['listaModulos'][ $i ] = self::getModuleName ($adb, $relatedList ['related_tabid']);
				$actions                         = explode (',', $relatedList ['actions']);
				if (in_array ('ADD', $actions)) {
					$_SESSION ['listaAccionAdd'][ ($i - 1) ] = 'on';
				}
				if (in_array ('SELECT', $actions)) {
					$_SESSION ['listaAccionSelect'][ ($i - 1) ] = 'on';
				}
				if (in_array ('PATRON', $actions)) {
					$_SESSION ['listaAccionPatron'][ ($i - 1) ] = 'on';
				}
			}
			$_SESSION ['module']          = 'Settings';
			$_SESSION ['action']          = 'wizardPaso5';
			$_SESSION ['Ajax']            = true;
			$_SESSION ['nombreCodigo']    = $newModuleName;
			$_SESSION ['nombrePublico']   = $newModuleLabel;
			$_SESSION ['tipoModulo']      = 'Completo';
			$_SESSION ['moduloPadre']     = $parentModuleName;
			$_SESSION ['file']            = '';
			$_SESSION ['reportAvailable'] = 'on';
			$_SESSION ['duplicar']        = 'duplicar';
			$_SESSION ['moduloaduplicar'] = $oldModuleId;
		}

		public static function clearModuleDuplicationSessionVariables () {
			unset ($_SESSION ['numeroBloqueCampo']);
			unset ($_SESSION ['etiquetaCampo']);
			unset ($_SESSION ['moduloCampo']);
			unset ($_SESSION ['nombreCampo']);
			unset ($_SESSION ['precisionCampo']);
			unset ($_SESSION ['prefijoCampo']);
			unset ($_SESSION ['secuenciaCampo']);
			unset ($_SESSION ['tamanoCampo']);
			unset ($_SESSION ['tipoCampo']);
			unset ($_SESSION ['valoresCampo']);
			unset ($_SESSION ['campoBarra']['min']);
			unset ($_SESSION ['campoBarra']['max']);
			unset ($_SESSION ['campoBarra']['ini']);
			unset ($_SESSION ['campoBarra']['ord']);
			unset ($_SESSION ['labelModulos']);
			unset ($_SESSION ['listaModulos']);
			unset ($_SESSION ['listaAccionAdd']);
			unset ($_SESSION ['listaAccionPatron']);
			unset ($_SESSION ['listaAccionSelect']);
			unset ($_SESSION ['linea']);
			unset ($_SESSION ['nombreBloque']);
			unset ($_SESSION ['numBloque']);
			unset ($_SESSION ['visibilidadBloque']);
			unset ($_SESSION ['campoIdentificador']);
			unset ($_SESSION ['nombrePublicoADuplicar']);
			unset ($_SESSION ['nombrePublicoSingleLanguage']);
			unset ($_SESSION ['campoOrigenRelacionAutomatica']);
			unset ($_SESSION ['campoRelacionAutomatica']);
			unset ($_SESSION ['columnasFiltro']);
			unset ($_SESSION ['file']);
			unset ($_SESSION ['moduloaduplicar']);
			unset ($_SESSION ['modulonuevo']);
			unset ($_SESSION ['moduloPadre']);
			unset ($_SESSION ['nombreCodigo']);
			unset ($_SESSION ['nombrePublico']);
			unset ($_SESSION ['reportAvailable']);
			unset ($_SESSION ['tipoModulo']);
			unset ($_SESSION ['isAdmin']);
			unset ($_SESSION ['duplicar']);
			unset ($_SESSION ['globalpicklists']);
		}

		/**
		 * @param \PearDatabase $adb
		 *
		 * @return bool
		 */
		public static function areApplicationViewsEnabled (PearDatabase $adb) {
			$result = $adb->pquery ('SELECT * FROM vtiger_variables WHERE varname=?', array ('APPLICATION_VIEWS_ENABLED'));
			if ($adb->num_rows ($result) > 0) {
				$row     = $adb->fetchByAssoc ($result, -1, false);
				$enabled = $row ['value'] == '1';
			} else {
				$enabled = false;
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
			return $enabled;
		}

		/**
		 * @param \PearDatabase $targetAdb
		 * @param $moduleName
		 * @param $entityId
		 * @param null $currentUser
		 *
		 * @return \CRMEntity
		 */
		public static function loadCrmEntity (PearDatabase $targetAdb, $moduleName, $entityId, $currentUser = null) {
			// La inclusión de estas globales es gracias a que la clase CRMEntity depende de ellas, pero en general esto no debería ocurrir
			global $adb, $current_user;
			$oldGlobalAdb = !empty ($adb) ? clone ($adb) : null;
			$adb          = $targetAdb;

			$oldGlobalCurrentUser = !empty ($current_user) ? clone ($current_user) : null;
			if (!empty ($currentUser)) {
				$current_user = $currentUser;
			} else if (empty ($current_user)) {
				$current_user = CRMEntity::getInstance ('Users');
				$current_user->retrieve_entity_info (1, 'Users');
				create_tab_data_file ();
				create_parenttab_data_file ();
				createUserPrivilegesfile (1);
				createUserSharingPrivilegesfile (1);
			}

			$entity = CRMEntity::getInstance ($moduleName);
			$entity->retrieve_entity_info ($entityId, $moduleName);

			unset ($adb);
			$adb = $oldGlobalAdb;
			unset ($current_user);
			$current_user = $oldGlobalCurrentUser;
			return $entity;
		}

		/**
		 * @param \PearDatabase $targetAdb
		 * @param \CRMEntity $entity
		 * @param $moduleName
		 * @param null $currentUser
		 *
		 * @return \CRMEntity
		 */
		public static function saveCrmEntity (PearDatabase $targetAdb, CRMEntity $entity, $moduleName, $currentUser = null) {
			// La inclusión de estas globales es gracias a que la clase CRMEntity depende de ellas, pero en general esto no debería ocurrir
			global $adb, $current_user;
			$oldGlobalAdb = !empty ($adb) ? clone ($adb) : null;
			$adb          = $targetAdb;

			$oldGlobalCurrentUser = !empty ($current_user) ? clone ($current_user) : null;
			if (!empty ($currentUser)) {
				$current_user = $currentUser;
			} else if (empty ($current_user)) {
				$current_user = CRMEntity::getInstance ('Users');
				$current_user->retrieve_entity_info (1, 'Users');
				create_tab_data_file ();
				create_parenttab_data_file ();
				createUserPrivilegesfile (1);
				createUserSharingPrivilegesfile (1);
			}

			if (empty ($entity->column_fields ['assigned_user_id'])) {
				$entity->column_fields ['assigned_user_id'] = 1;
			}
			$entity->saveentity ($adb, $moduleName);
			$gridFieldsData = GridFieldUtils::getAvailableGridFields ($adb, $moduleName);
			if (!empty ($gridFieldsData)) {
				$gridFieldNames = array_keys ($gridFieldsData);
				foreach ($gridFieldNames as $gridFieldName) {
					GridFieldUtils::setGridValues ($targetAdb, $moduleName, $gridFieldName, $entity->id, $entity->column_fields [ $gridFieldName ]);
				}
			}
			$entity->column_fields ['record_id']     = $entity->id;
			$entity->column_fields ['record_module'] = $moduleName;
			unset ($adb);
			$adb = $oldGlobalAdb;
			unset ($current_user);
			$current_user = $oldGlobalCurrentUser;
			return $entity;
		}

		/**
		 * @param \PearDatabase $adb
		 */
		public static function toggleApplicationViewsAvailability (PearDatabase $adb) {
			$result = $adb->pquery ('SELECT * FROM vtiger_variables WHERE varname=?', array ('APPLICATION_VIEWS_ENABLED'));
			if ($adb->num_rows ($result) > 0) {
				$row     = $adb->fetchByAssoc ($result, -1, false);
				$enabled = $row ['value'] == '1';
				$adb->pquery ('UPDATE vtiger_variables SET value=? WHERE varname=?', array (!$enabled, 'APPLICATION_VIEWS_ENABLED'));
			} else {
				$enabled = false;
				$adb->pquery ('INSERT INTO vtiger_variables (tabid, varname, value) VALUES (?, ?, ?)', array (0, 'APPLICATION_VIEWS_ENABLED', !$enabled));
			}
			if ($result instanceof ADORecordSet) {
				$result->Close ();
				$result = null;
			}
		}

	}
