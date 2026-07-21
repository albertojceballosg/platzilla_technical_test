<?php
	require_once ('include/platzilla/Objects/CalculationElement.php');
	require_once ('include/platzilla/Objects/CalculationSystem.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class CalculationElementManager {
		/** @var CalculationElementManager */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * Constructor
		 *
		 * @param PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param CalculationElement $element
		 */
		public function changeStatusToCalculatedElement ($element) {
			if ((empty ($element)) || (!($element instanceof CalculationElement))) {
				return;
			}
			$status = ($element->getStatus() == $element::STATUS_ACTIVE) ? 1 : 0;

			$this->adb->pquery (
				'UPDATE  vtiger_calculated_fields SET active=? WHERE calculated_fieldsid=?',
				array ($status, $element->getId ())
			);
		}

		/**
		 * @param CalculationElement $element
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public function deleteCalculationElement ($element) {
			if ((empty ($element)) || (!($element instanceof CalculationElement))) {
				return false;
			}
			$query = $this->adb->pquery (
				'SELECT
						ce.calculated_equationid
					FROM
						vtiger_calculated_fields cf
					LEFT JOIN vtiger_calculated_equation ce ON ((ce.firstelement = cf.element_name AND ce.firstelemettype= ? ) OR (ce.secondelement = cf.element_name AND ce.secondelementtype= ? ) )
					WHERE
						cf.calculated_fieldsid = ?
					GROUP BY
					ce.calculated_equationid',
				array (CalculationSystem::ELEMENT, CalculationSystem::ELEMENT, $element->getId ())
			);
			$numOfRows = $this->adb->num_rows ($query);
			if ($numOfRows > 0) {
				$sqlEquation    = 'DELETE FROM vtiger_calculated_equation WHERE calculated_equationid=?';
				$sqlCalculation = 'DELETE FROM vtiger_calculated_system WHERE equationid=?';
				while ($row = $this->adb->fetchByAssoc ($query)) {
						$this->adb->pquery ($sqlEquation, array ($row['calculated_equationid']));
						$this->adb->pquery ($sqlCalculation, array ($row['calculated_equationid']));
				}
			}
			$this->adb->pquery ('DELETE FROM vtiger_calculated_fields WHERE calculated_fieldsid=?', array ($element->getId ()));
			return true;
		}

		/**
		 * @param string|integer $searchElement
		 *
		 * @return boolean|CalculationElement
		 * @throws Exception
		 */
		public function fetchCalculationElement ($searchElement) {
			if (empty ($searchElement)) {
				return false;
			}
			$fieldWhere = (is_numeric ($searchElement)) ? 'calculated_fieldsid=?' : 'element_name=?';

			$query = $this->adb->pquery ("SELECT * FROM vtiger_calculated_fields WHERE {$fieldWhere}", array ($searchElement));
			if ($this->adb->num_rows ($query) > 0) {
				$row     = $this->adb->fetchByAssoc ($query, -1, false);
				$element = CalculationElement::getInstance ()
					->setId ($row ['calculated_fieldsid'])
					->setColumnName ($row ['columnname'])
					->setElementName ($row ['element_name'])
					->setDescription ($row ['description'])
					->setModuleName ($row ['modulename'])
					->setName ($row ['name'])
					->setPeriod ($row ['period'])
					->setPeriodField ($row ['period_field'])
					->setOperationName ($row ['name_operation'])
					->setRelatedModules ($row ['relmodules'])
					->setResult ($row ['results'])
					->setSqlFilter ($row ['script'])
					->setSqlData ($row ['arrayscript'])
					->setStatus (($row ['active']) ? CalculationElementInterface::STATUS_ACTIVE : CalculationElementInterface::STATUS_INACTIVE)
					->setUpdatedDate ($row ['dt_updated']);
			} else {
				$element = false;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $element;
		}

		/**
		 * @return CalculationElement[]|null
		 * @throws Exception
		 */
		public function fetchCalculationsElements () {
			$result = $this->adb->query (
				'SELECT
					cf.*,
					t.tablabel AS module,
					f.fieldlabel AS label
				FROM
					vtiger_calculated_fields cf
               		LEFT JOIN vtiger_tab t ON t.name = cf.modulename
               		LEFT JOIN vtiger_field f ON f.columnname = cf.columnname
               	GROUP BY
					cf.calculated_fieldsid
               	ORDER BY
               		cf.calculated_fieldsid ASC'
			);
			if ($this->adb->num_rows ($result) > 0) {
				$elements = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$fldLabel = (!empty ($row ['fieldlabel'])) ? $row ['fieldlabel']: $row ['label'];
					$elements[] = CalculationElement::getInstance ()
						->setId ($row ['calculated_fieldsid'])
						->setColumnName ($row ['columnname'])
						->setElementName ($row ['element_name'])
						->setDescription ($row ['description'])
						->setFieldLabel ($fldLabel)
						->setModuleName ($row ['modulename'])
						->setName ($row ['name'])
						->setOperationName ($row ['name_operation'])
						->setRelatedModules ($row ['relmodules'])
						->setResult ($row ['results'])
						->setSqlFilter ($row ['script'])
						->setSqlData ($row ['arrayscript'])
						->setStatus (($row ['active']) ? CalculationElementInterface::STATUS_ACTIVE : CalculationElementInterface::STATUS_INACTIVE)
						->setTabLabel ($row ['module'])
						->setUpdatedDate ($row ['dt_updated']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($elements) ? $elements : null;
		}

		/**
		 * @param CalculationElement $element
		 * @param string $action
		 *
		 * @return CalculationElement|null
		 * @throws CalculationElementException
		 */
		public function saveCalculationElement ($element, $action = 'insert') {
			if ((empty ($element)) || (!($element instanceof CalculationElement))) {
				return null;
			}
			$element->validate ();

			$elementValues = array (
				'modulename'     => $element->getModuleName (),
				'columnname'     => $element->getColumnName (),
				'name'           => $element->getName (),
				'description'    => $element->getDescription (),
				'name_operation' => $element->getOperationName (),
				'script'         => $element->getSqlFilter (),
				'arrayscript'    => $element->getSqlData (),
				'period'         => $element->getPeriod (),
				'period_field'   => $element->getPeriodField (),
				'relmodules'     => $element->getRelatedModules (),
				'fieldlabel'     => $element->getFieldLabel(),
				'results'        => floatval ($element->getResult ()),
				'active'         => ($element->getStatus () == CalculationElementInterface::STATUS_ACTIVE) ? 1 : 0,
				'locked'         => intval ($element->isLocked ()),
				'dt_updated'     => $element->getUpdatedDate (),
			);

			if ($action =='insert') {
				$elementValues ['element_name'] = $element->getElementName ();
				$sql = 'INSERT INTO `vtiger_calculated_fields` (`' . implode ('`, `', array_keys ($elementValues)) . '`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)';
				$this->adb->pquery ($sql, array_values ($elementValues));
				$element->setId ($this->adb->getLastInsertID ());
			} else {
				$sql = 'UPDATE `vtiger_calculated_fields` SET `' . implode ('`=?, `', array_keys ($elementValues)) . '`=? WHERE `calculated_fieldsid`=' . $element->getId ();
				$this->adb->pquery ($sql, array_values ($elementValues));
			}
			return $element;
		}

		/**
		 * @param CalculationElement $element
		 *
		 * @return null
		 * @throws CalculationElementException
		 */
		public function updateCalculationElementResult ($element) {
			if ((empty ($element)) || (!($element instanceof CalculationElement))) {
				return null;
			}
			$element->validate ();

			$sql = 'UPDATE vtiger_calculated_fields SET results = ?,dt_updated=?  WHERE calculated_fieldsid=?';
			$this->adb->pquery (
				$sql,
				array (
					$element->getResult (),
					$element->getUpdatedDate (),
					$element->getId (),
				)
			);
			return $element;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return CalculationElementManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = array ();
			}
			if (!isset (self::$INSTANCE [ $adb->dbName ])) {
				self::$INSTANCE[ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCE [ $adb->dbName ];
		}

	}
