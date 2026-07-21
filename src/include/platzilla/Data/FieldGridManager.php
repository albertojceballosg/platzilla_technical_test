<?php
	require_once ('include/platzilla/Data/FieldGrid.php');
	require_once ('include/platzilla/Data/FieldGridValues.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Esta clase hace referencia a los métodos que sirven de utilería en la gestión a los campos del tipo "Grid" o tablas inteligentes.
	 *
	 * Class FieldGridManager
	 */
	class FieldGridManager {

		/** @var FieldGridManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Elimina los registros del la tabla vtiger_subfields_values basada en el registro del módulo que contiene la tabla
		 *
		 * @param integer $entityId
		 */
		public function deleteFieldGridValues ($entityId) {
			if (!is_numeric ($entityId) || empty($entityId)) {
				return;
			}
			$this->adb->pquery ('DELETE FROM vtiger_subfields_values WHERE modulecfid=?', array ($entityId));
		}

		/**
		 * Elimina un registro de la tabla vtiger_subfields_values basada en el id de un campo de la tabla
		 *
		 * @param FieldGridValues $fieldValue
		 */
		public function deleteFieldGridValue ($fieldValue) {
			if ((empty ($fieldValue)) || (!($fieldValue instanceof FieldGridValues))) {
				return;
			}

			$this->adb->pquery (
				'DELETE FROM 
						vtiger_subfields_values 
					  WHERE 
					  	subfieldsid = ? AND 
					  	modulecfid=?',
				array ($fieldValue->getSubFieldId (), $fieldValue->getModulecfId())
			);
		}

		/**
		 * Obtiene una lista de objetos FieldGridbasado basado en el nombre del módulo
		 *
		 * @param string $moduleName
		 *
		 * @return FieldGrid[]|null
		 * @throws Exception
		 */
		public function fetchAvailableFieldsGrid ($moduleName) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					sfs.*,
					f.fieldname,
					f.fieldlabel
				FROM
					vtiger_subfields_special sfs
					INNER JOIN vtiger_field f ON f.fieldid=sfs.fieldid AND f.uitype=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				ORDER BY
					sfs.fieldid,
					sfs.sequence',
				array (FieldInterface::UI_TYPE_GRID, $moduleName)
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$fields = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$fields [] = FieldGrid::getInstance()
						->setActionField ($row ['action_field'])
						->setDataField ($row ['data_field'])
						->setDefaultValue ($row ['defaultvalue'])
						->setFilterField ($row ['filter_field'])
						->setFieldId (intval ($row ['fieldid']))
						->setFieldLabel ($row ['fieldlabel'])
						->setFieldName ($row ['fieldname'])
						->setLabel ($row ['label'])
						->setLength ($row ['length'])
						->setLocked (($row ['loked']) ? true : false)
						->setModuleReferences ('relmodule')
						->setName ($row ['name'])
						->setPrecision ($row ['precision'])
						->setSequence ($row ['sequence'])
						->setSubFieldId (intval ($row ['subfieldsid']))
						->setUiType ($row ['uitype'])
						->setValues ($row ['values']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($fields)) ? $fields : null;
		}

		/**
		 * Obtiene una lista de objetos FieldGrid basado basado en el nombre de la tabla y del módulo
		 *
		 * @param string $moduleName
		 * @param string $gridName
		 *
		 * @return FieldGrid[]|null
		 * @throws Exception
		 */
		public function fetchFieldGrid ($moduleName, $gridName) {
			if ((empty ($moduleName)) || (empty ($gridName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					sfs.*,
					f.fieldlabel,
					f.fieldname
				FROM
					vtiger_subfields_special sfs
					INNER JOIN vtiger_field f ON f.fieldid=sfs.fieldid AND f.fieldname=? AND f.uitype=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($gridName, FieldInterface::UI_TYPE_GRID, $moduleName)
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$fields = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$fields [] = FieldGrid::getInstance()
						->setActionField ($row ['action_field'])
						->setDataField ($row ['data_field'])
						->setDefaultValue ($row ['defaultvalue'])
						->setFilterField ($row ['filter_field'])
						->setFieldId (intval ($row ['fieldid']))
						->setFieldLabel ($row ['fieldlabel'])
						->setFieldName ($row ['fieldname'])
						->setLabel ($row ['label'])
						->setLength ($row ['length'])
						->setLocked (($row ['loked']) ? true : false)
						->setModuleReferences ($row ['relmodule'])
						->setName ($row ['name'])
						->setPrecision ($row ['precision'])
						->setSequence ($row ['sequence'])
						->setSubFieldId (intval ($row ['subfieldsid']))
						->setUiType ($row ['uitype'])
						->setValues ($row ['values']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($fields)) ? $fields : null;
		}

		/**
		 * Devuelve una colección de objetos FieldGridValues basado en el módulo, tabla  y record del módulo
		 *
		 * @param string $moduleName
		 * @param string $gridName
		 * @param integer $entityId
		 * @param boolean $summaryRow
		 *
		 * @return FieldGridValues[]|null
		 * @throws Exception
		 */
		public function fetchFieldGridValues ($moduleName, $gridName, $entityId, $summaryRow = false) {
			if ((empty ($moduleName)) || (empty ($gridName)) || (empty ($entityId))) {
				return null;
			}

			$uiType = ($summaryRow) ? 0 : FieldInterface::UI_TYPE_SUMMARY_ROW;
			$result = $this->adb->pquery (
				'SELECT
					sfv.*,
					sfs.name,
					sfs.label
				FROM
					vtiger_subfields_values sfv
					INNER JOIN vtiger_subfields_special sfs ON sfs.subfieldsid=sfv.subfieldsid
					INNER JOIN vtiger_field f ON f.fieldid=sfs.fieldid AND f.fieldname=? AND f.uitype=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				WHERE
					sfv.modulecfid=? AND
					sfs.uitype NOT IN (?)',
				array ($gridName, FieldInterface::UI_TYPE_GRID, $moduleName, $entityId, $uiType)
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$values = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$values [] = FieldGridValues::getInstance()
						->setFieldLabel($row ['label'])
						->setFieldName ($row ['name'])
						->setSubFieldId ($row ['subfieldsid'])
						->setModulecfId ($row ['modulecfid'])
						->setGridFieldValue ($row ['field_values']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($values)) ? $values : null;
		}

		/**
		 * Devuelve una colección de objetos FieldGridValues basado en el id del campo grid y el record del módulo
		 *
		 * @param integer $entityId
		 * @param integer $subFieldId
		 *
		 * @return FieldGridValues
		 * @throws Exception
		 */
		public function fetchFieldGridValuesByEntityId ($entityId, $subFieldId) {
			if ((empty ($entityId)) || empty ($subFieldId)) {
				return FieldGridValues::getInstance();
			}

			$result = $this->adb->pquery (
				'SELECT
					sfv.*,
					sfs.name,
					sfs.label
				FROM
					vtiger_subfields_values sfv
					INNER JOIN vtiger_subfields_special sfs ON sfs.subfieldsid=sfv.subfieldsid
					INNER JOIN vtiger_field f ON f.fieldid=sfs.fieldid
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					sfv.modulecfid=? AND 
					sfv.subfieldsid=?',
				array ($entityId, $subFieldId)
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$values = FieldGridValues::getInstance()
						->setFieldLabel($row ['label'])
						->setFieldName ($row ['name'])
						->setSubFieldId ($row ['subfieldsid'])
						->setModulecfId ($row ['modulecfid'])
						->setGridFieldValue ($row ['field_values']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($values)) ? $values : FieldGridValues::getInstance();
		}

		/**
		 * Guarda una colección de objetos FieldGridValues basado
		 *
		 * @param FieldGridValues[] $fieldValues
		 *
		 * @throws FieldGridException
		 */
		public function saveFieldGridValues ($fieldValues) {
			if (!is_array($fieldValues) || empty($fieldValues)) {
				return;
			}
			foreach ($fieldValues as $fieldValue) {
				if ((empty ($fieldValue)) || (!($fieldValue instanceof FieldGridValues))) {
					continue;
				}
				$fieldValue->validate ();
				$this->deleteFieldGridValue ($fieldValue);
				$this->adb->pquery (
					'INSERT INTO `vtiger_subfields_values` (`modulecfid`, `subfieldsid`, `field_values`) VALUES (?, ?, ?);',
					array ($fieldValue->getModulecfId (), $fieldValue->getSubFieldId (), $fieldValue->getGridFieldValue ())
				);
			}
		}

		/**
		 * Se obtiene un objeto FieldGridManager con los atributos de la clase
		 *
		 * @param PearDatabase $adb
		 *
		 * @return FieldGridManager
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
