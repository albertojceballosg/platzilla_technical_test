<?php
	require_once ('include/platzilla/Managers/CalculationElementManager.php');
	require_once ('include/platzilla/Managers/CalculationSystemManager.php');
	require_once ('include/platzilla/Objects/CalculationElement.php');
	require_once ('include/platzilla/Objects/CalculationEquation.php');
	require_once ('include/platzilla/Objects/CalculationSystem.php');
	require_once ('include/platzilla/Objects/CalculationSystem.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class CalculationManager {
		/** @var CalculationManager */
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
		 * @return integer
		 * @throws Exception
		 */
		private function getLastEquationId () {
			$results   = 0;
			$sql       = 'SELECT `calculated_equationid` FROM `vtiger_calculated_equation` WHERE 1 ORDER BY calculated_equationid DESC LIMIT 1';
			$query     = $this->adb->query ($sql);
			$numOfRows = $this->adb->num_rows ($query);
			if ($numOfRows > 0) {
				while ($row = $this->adb->fetchByAssoc ($query)) {
					$results = $row['calculated_equationid'];
				}
			}
			return ($results + 1);
		}

		/**
		 * @param CalculationElement[] $sourceElements
		 * @param integer $elementName
		 *
		 * @return integer
		 * @throws CalculationElementException
		 * @throws Exception
		 */
		private function searchAndSaveElement ($sourceElements, $elementName) {
			$targetElementName = null;
			$cem               = CalculationElementManager::getInstance ($this->adb);
			foreach ($sourceElements as $sourceElement) {
				if ($sourceElement->getElementName() == $elementName) {
					$targetElement = $cem->fetchCalculationElement($elementName);
					if (!$targetElement) {
						$sourceElement->setId (null);
						$sourceElement->setLocked (false);
						$targetElement  = $cem->saveCalculationElement ($sourceElement, 'insert');
						$targetElementName = $targetElement->getElementName ();
					} else if(!$targetElement->isLocked()) {
						$sourceElement->setId ($targetElement->getId ());
						$targetElement  = $cem->saveCalculationElement ($sourceElement, 'update');
						$targetElementName = $targetElement->getElementName ();
					}
					break;
				}
			}
			return $targetElementName;
		}

		/**
		 * @param CalculationEquation[] $equations
		 * @param CalculationElement[] $sourceElements
		 * @param integer $equationId
		 *
		 * @return array|null
		 * @throws CalculationElementException
		 * @throws Exception
		 */
		private function updateEquation ($equations, $sourceElements, $equationId) {
			if ((empty ($equations))) {
				return null;
			}
			$sourceEquations = array ();
			foreach ($equations as $theEquation) {
				if ((! empty ($theEquation)) && (($theEquation instanceof CalculationEquation))) {
					$addEquation = true;
					$theEquation->setId($equationId);
					if ($theEquation->getFirstElementType() == CalculationSystemInterface::ELEMENT) {
						$sourceElementName = $this->searchAndSaveElement($sourceElements, $theEquation->getFirstElement());
						if ($sourceElementName) {
							$theEquation->setFirstElement($sourceElementName);
						} else {
							$addEquation = false;
						}
					}
					if ($theEquation->getSecondElementType() == CalculationSystemInterface::ELEMENT) {
						$sourceElementName = $this->searchAndSaveElement($sourceElements, $theEquation->getSecondElement());
						if ($sourceElementName) {
							$theEquation->setSecondElement($sourceElementName);
						} else {
							$addEquation = false;
						}
					}
					if ($addEquation) {
						$sourceEquations [] = $theEquation;
					}
				}
			}
			return $sourceEquations;
		}

		/**
		 * @param string $moduleName
		 * @param boolean $status
		 *
		 * @return CalculationSystem[]|null
		 * @throws Exception
		 */
		public function fetchCalculations ($moduleName, $status = true) {
			if ($moduleName != 'calculated_fields') {
				return null;
			}
			$calculationStatus = ($status) ? 'ACTIVE' : 'INACTIVE';
			$calculations = CalculationSystemManager::getInstance ($this->adb)->fetchCalculationsSystem ($calculationStatus);
			return !empty ($calculations) ? $calculations : null;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return CalculationElement[]|null
		 * @throws Exception
		 */
		public function fetchElements ($moduleName) {
			if ($moduleName != 'calculated_fields') {
				return null;
			}
			$elements  = CalculationElementManager::getInstance ($this->adb)->fetchCalculationsElements ();
			return !empty ($elements) ? $elements : null;
		}

		/**
		 * @param Module $module
		 * @param boolean $ignoreLock
		 *
		 * @return null
		 * @throws CalculationElementException
		 * @throws CalculationSystemException
		 * @throws Exception
		 */
		public function saveCalculationsSystem ($module, $ignoreLock = true) {
			if ((empty ($module)) || (!($module instanceof Module))) {
				return null;
			}
			$sourceCalculations = $module->getCalculationsSystem ();
			if ( empty($sourceCalculations)) {
				return null;
			}
			$sourceElements = $module->getCalculatedElements ();
			$csm            = CalculationSystemManager::getInstance ($this->adb);
			foreach ($sourceCalculations as $sourceCalculation) {
				if ((! empty ($sourceCalculation)) && (($sourceCalculation instanceof CalculationSystem))) {
					$equationId = $this->getLastEquationId();
					$sourceEquations = $this->updateEquation($sourceCalculation->getEquation(), $sourceElements, $equationId);
					$sourceCalculation->setLocked(false);
					if (!empty($sourceEquations)) {
						$sourceCalculation->clearEquation();
						$sourceCalculation->setEquationId($equationId);
						$sourceCalculation->setEquation($sourceEquations);
					}
					$targetCalculation = $csm->fetchCalculationSystem($sourceCalculation->getCalculationName());
					if (!$targetCalculation) {
						$csm->saveCalculationSystem($sourceCalculation, 'insert');
					} else if (($targetCalculation->isLocked () == 0) && $ignoreLock) {
						$sourceCalculation->setId($targetCalculation->getId());
						$csm->saveCalculationSystem($sourceCalculation, 'update');
					}
				}
			}

			return null;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return CalculationManager
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
