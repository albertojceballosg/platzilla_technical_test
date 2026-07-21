<?php
	require_once ('include/platzilla/Objects/CalculationEquation.php');
	require_once ('include/platzilla/Objects/CalculationSystem.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class CalculationSystemManager {
		/** @var CalculationSystemManager */
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
		 * @param CalculationSystem $calculation
		 */
		public function changeStatusToCalculatedSystem ($calculation) {
			if ((empty ($calculation)) || (!($calculation instanceof CalculationSystem))) {
				return;
			}
			$status = ($calculation->getStatus() == $calculation::STATUS_ACTIVE) ? 1 : 0;

			$this->adb->pquery (
				'UPDATE  vtiger_calculated_system SET active=? WHERE calculated_systemid=?',
				array ($status, $calculation->getId ())
			);
		}

		/**
		 * @param CalculationSystem $calculation
		 */
		public function deleteCalculationSystem ($calculation) {
			if ((empty ($calculation)) || (!($calculation instanceof CalculationSystem))) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_calculated_system WHERE calculated_systemid=?', array ($calculation->getId ()));
			$this->adb->pquery ('DELETE FROM vtiger_calculated_equation WHERE calculated_equationid=?', array ($calculation->getEquationId()));
		}

		/**
		 * @param integer $equationId
		 *
		 * @return CalculationEquation[]|null
		 * @throws Exception
		 */
		public function fetchCalculationEquations ($equationId) {
			if (empty($equationId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_calculated_equation WHERE calculated_equationid=? ORDER BY groupindex ASC ', array($equationId));
			if ($this->adb->num_rows ($result) > 0) {
				$equations = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$equations[] = CalculationEquation::getInstance ()
						->setId ($row ['calculated_equationid'])
						->setFirstElement ($row ['firstelement'])
						->setFirstElementType ($row ['firstelemettype'])
						->setIndexGroup ($row ['groupindex'])
						->setOperationGroup ($row ['groupoperation'])
						->setOperation ($row ['operation'])
						->setSecondElement ($row ['secondelement'])
						->setSecondElementType($row ['secondelementtype']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($equations) ? $equations : null;
		}

		/**
		 * @param string $calculationName
		 *
		 * @return CalculationSystem|null
		 * @throws Exception
		 */
		public function getCalculationSystemBayName ($calculationName) {
			if (empty ($calculationName)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_calculated_system  WHERE calculated_name=? AND active=?',
				array ($calculationName, 1)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row         = $this->adb->fetchByAssoc ($result, -1, false);
				$calculation = CalculationSystem::getInstance ()
					->setId ($row ['calculated_systemid'])
					->setCalculatedData ($row ['calculated_data'])
					->setCalculationName ($row ['calculated_name'])
					->setDescription ($row ['description'])
					->setEquation ($this->fetchCalculationEquations ($row ['equationid']))
					->setEquationId ($row ['equationid'])
					->setModuleName ($row ['modulename'])
					->setName ($row ['name'])
					->setRelatedModules ($row ['relmodules'])
					->setResult ($row ['results'])
					->setSql ($row ['sql'])
					->setStatus (($row ['active']) ? CalculationSystemInterface::STATUS_ACTIVE : CalculationSystemInterface::STATUS_INACTIVE)
					->setUpdatedDate ($row ['dt_updated']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($calculation) ? $calculation : null;
		}
		
		
		/**
		 * @param string $status
		 *
		 * @return CalculationSystem[]|null
		 * @throws Exception
		 */
		public function fetchCalculationsSystem ($status = '') {
			$where = '';
			if (!empty ($status)) {
				$where = ($status == 'ACTIVE') ? 'WHERE active=1' : 'WHERE active=0';
			}
			$result = $this->adb->query ("SELECT * FROM vtiger_calculated_system  {$where}  ORDER BY calculated_systemid ASC");
			if ($this->adb->num_rows ($result) > 0) {
				$calculations = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$calculations[] = CalculationSystem::getInstance ()
						->setId ($row ['calculated_systemid'])
						->setCalculatedData ($row ['calculated_data'])
						->setCalculationName ($row ['calculated_name'])
						->setDescription ($row ['description'])
						->setEquation ($this->fetchCalculationEquations ($row ['equationid']))
						->setEquationId ($row ['equationid'])
						->setModuleName ($row ['modulename'])
						->setName ($row ['name'])
						->setRelatedModules ($row ['relmodules'])
						->setResult ($row ['results'])
						->setSql ($row ['sql'])
						->setStatus (($row ['active']) ? CalculationSystemInterface::STATUS_ACTIVE : CalculationSystemInterface::STATUS_INACTIVE)
						->setUpdatedDate ($row ['dt_updated']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($calculations) ? $calculations : null;
		}

		/**
		 * @param integer|string $searchCalculation
		 *
		 * @return boolean|CalculationSystem
		 * @throws Exception
		 */
		public function fetchCalculationSystem ($searchCalculation) {
			if (empty($searchCalculation)) {
				return null;
			}
			$searchWhere = (is_numeric($searchCalculation)) ? 'calculated_systemid=?' : 'calculated_name=?';

			$query = $this->adb->pquery ("SELECT * FROM vtiger_calculated_system WHERE {$searchWhere} ", array ($searchCalculation));
			if ($this->adb->num_rows ($query) > 0) {
				$row         = $this->adb->fetchByAssoc ($query, -1, false);
				$calculation = CalculationSystem::getInstance ()
					->setId ($row ['calculated_systemid'])
					->setCalculatedData ($row ['calculated_data'])
					->setCalculationName ($row ['calculated_name'])
					->setDescription ($row ['description'])
					->setEquation ($this->fetchCalculationEquations ($row ['equationid']))
					->setEquationId ($row ['equationid'])
					->setModuleName ($row ['modulename'])
					->setName ($row ['name'])
					->setRelatedModules ($row ['relmodules'])
					->setResult ($row ['results'])
					->setSql ($row ['sql'])
					->setStatus (($row ['active']) ? CalculationSystemInterface::STATUS_ACTIVE : CalculationSystemInterface::STATUS_INACTIVE)
					->setUpdatedDate ($row ['dt_updated']);
			} else {
				$calculation = false;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $calculation;
		}

		/**
		 * @param CalculationSystem $calculation
		 *
		 * @return null
		 */
		public function saveCalculationEquations ($calculation) {
			$equations  = $calculation->getEquation ();
			foreach ($equations as $equation) {
				if ((empty ($equation)) || (!($equation instanceof CalculationEquation))) {
					return null;
				}

				$equationValues = array(
					'calculated_equationid' => $equation->getId (),
					'firstelement'          => $equation->getFirstElement (),
					'firstelemettype'       => $equation->getFirstElementType (),
					'groupindex'            => $equation->getIndexGroup (),
					'groupOperation'        => $equation->getOperationGroup (),
					'operation'             => $equation->getOperation (),
					'secondelement'         => $equation->getSecondElement(),
					'secondelementtype'     => $equation->getSecondElementType (),
				);
				$sql = 'INSERT INTO `vtiger_calculated_equation` (`' . implode('`, `', array_keys($equationValues)) . '`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
				$this->adb->pquery($sql, array_values($equationValues));
				$equation->setId($this->adb->getLastInsertID());
			}
			return $equations;
		}

		/**
		 * @param CalculationSystem $calculation
		 * @param string $action
		 *
		 * @return CalculationSystem|null
		 * @throws CalculationSystemException
		 * @throws Exception
		 */
		public function saveCalculationSystem ($calculation, $action = 'insert') {
			if ((empty ($calculation)) || (!($calculation instanceof CalculationSystem))) {
				return null;
			}
			$calculation->validate ();

			$calculationValues = array (
				'active'          => ($calculation->getStatus () == CalculationSystemInterface::STATUS_ACTIVE) ? 1 : 0,
				'calculated_data' => $calculation->getCalculatedData (),
				'description'     => $calculation->getDescription (),
				'dt_updated'      => $calculation->getUpdatedDate (),
				'equationid'      => intval ($calculation->getEquationId ()),
				'locked'          => intval ($calculation->isLocked ()),
				'modulename'      => $calculation->getModuleName (),
				'relmodules'      => $calculation->getRelatedModules (),
				'name'            => $calculation->getName (),
				'results'         => floatval($calculation->getResult ()),
				'sql'             => $calculation->getSql (),
			);
			if ($action =='insert') {
				$calculationValues['calculated_name'] = $calculation->getCalculationName ();
				$sql = 'INSERT INTO `vtiger_calculated_system`  (`' . implode ('`, `', array_keys ($calculationValues)) . '`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
				$this->adb->pquery ($sql, array_values ($calculationValues));
				$calculation->setId ($this->adb->getLastInsertID ());
			} else {
				$sql = 'UPDATE `vtiger_calculated_system` SET `' . implode ('`=?, `', array_keys ($calculationValues)) . '`=? WHERE `calculated_systemid`=' . $calculation->getId ();

				$this->adb->pquery ($sql, array_values ($calculationValues));
			}
			if (!empty($calculation->getEquation ())) {
				$equation = $this->fetchCalculationEquations($calculation->getEquationId());
				if ($equation) {
					$this->adb->pquery ('DELETE FROM vtiger_calculated_equation WHERE calculated_equationid=?', array ($calculation->getEquationId()));
				}
				$this->saveCalculationEquations($calculation);
			}
			return $calculation;
		}

		/**
		 * @param CalculationSystem $calculation
		 *
		 * @return null
		 * @throws CalculationSystemException
		 */
		public function updateCalculationResult ($calculation) {
			if ((empty ($calculation)) || (!($calculation instanceof CalculationSystem))) {
				return null;
			}
			$calculation->validate ();

			$sql = 'UPDATE vtiger_calculated_system SET results = ?, dt_updated= ? WHERE calculated_systemid=?';
			$this->adb->pquery (
				$sql,
				array (
					$calculation->getResult (),
					$calculation->getUpdatedDate (),
					$calculation->getId (),
				)
			);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return CalculationSystemManager
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
