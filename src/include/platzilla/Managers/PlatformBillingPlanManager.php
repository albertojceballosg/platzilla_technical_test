<?php
	require_once ('include/platzilla/Managers/ProductManager.php');
	require_once ('include/platzilla/Objects/PlatformBillingPlan.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class PlatformBillingPlanManager
	 *
	 * Clase donde se encuentran la implementación de los metodos que permiten la gestion de los planes de facturacion de las plataformas (instancias)
	 */
	class PlatformBillingPlanManager {
		/** @var PlatformBillingPlanManager|null */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * PlatformBillingPlanManager constructor.
		 *
		 * @param \PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Para validar el plan de facturacion asociado
		 *
		 * @param PlatformBillingPlan $plan
		 *
		 * @throws PlatformBillingPlanException
		 */
		private function validate ($plan) {
			if ((empty ($plan)) || (!($plan instanceof PlatformBillingPlan))) {
				throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_INVALID);
			} else {
				$plan->validate ();
			}

			$result = null;
			try {
				$planId = $plan->getId ();
				if (empty ($planId)) {
					$result = $this->adb->pquery ('SELECT * FROM vtiger_instancebillingplans WHERE planname=?', array ($plan->getName ()));
				} else {
					$result = $this->adb->pquery ('SELECT * FROM vtiger_instancebillingplans WHERE planid<>? AND planname=?', array ($planId, $plan->getName ()));
				}
				if (($result) && ($this->adb->num_rows ($result) > 0)) {
					throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_DUPLICATED_NAME);
				}
			} catch (PlatformBillingPlanException $ie) {
				$e = $ie;
			} finally {
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}
			}
		}

		/**
		 * Para eliminar el plan de facturacion asociado
		 *
		 * @param PlatformBillingPlan $plan
		 *
		 * @throws PlatformBillingPlanException
		 */
		public function deletePlan ($plan) {
			if ((empty ($plan)) || (!($plan instanceof PlatformBillingPlan))) {
				return;
			}

			$result = null;
			try {
				$planId = $plan->getId ();
				/** @var ADORecordSet $result */
				$result = $this->adb->pquery ('SELECT * FROM vtiger_instances WHERE billingplanid=?', array ($planId));
				if ($this->adb->num_rows ($result) > 0) {
					throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_IN_USE);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;

				$result = $this->adb->pquery ('SELECT * FROM vtiger_instancebillingplans WHERE planid=?', array ($planId));
				if ($this->adb->num_rows ($result) > 0) {
					$row = $this->adb->fetchByAssoc ($result, -1, false);
					$this->adb->pquery ('DELETE FROM vtiger_instancebillingplans WHERE planid=?', array ($planId));
					ProductManager::getInstance ($this->adb)->deleteProduct (Product::getInstance ()->setId ($row ['productid']));
				}
			} catch (PlatformBillingPlanException $ie) {
				$e = $ie;
			} finally {
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}
			}
		}

		/**
		 * Busca el plan actual contratado
		 *
		 * @param string $instanceCode
		 *
		 * @return null|PlatformBillingPlan
		 */
		public function fetchCurrentPlan ($instanceCode, $numUsers = 0) {
			if (empty ($instanceCode)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					ibp.*
				FROM
					vtiger_instancebillingplans ibp
					INNER JOIN vtiger_instances i ON i.billingplanid=ibp.planid AND i.code=?',
				array ($instanceCode)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$plan = PlatformBillingPlan::getInstance ()
					->setDescription ($row ['description'])
					->setId ($row ['planid'])
					->setName ($row ['planname'])
					->setProduct (ProductManager::getInstance ($this->adb)->fetchCurrentProduct ($instanceCode, $numUsers))
					->setStatus ($row ['status'])
					->setTotalApplications (intval ($row ['totalapplications']))
					->setTotalDiskSpace (intval ($row ['totaldiskspace']))
					->setTotalUsers (intval ($row ['totalusers']));
			} else {
				$plan = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $plan;
		}

		/**
		 * Buscar el plan free
		 *
		 * @return null|PlatformBillingPlan
		 */
		public function fetchFreePlan () {
			$result = $this->adb->query (
				'SELECT
					bp.*
				FROM
					vtiger_instancebillingplans bp
					INNER JOIN vtiger_products p ON p.productid=bp.productid
				WHERE
					p.baseprice=0
				ORDER BY
					bp.planid
				LIMIT 1'
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$plan = PlatformBillingPlan::getInstance ()
					->setDescription ($row ['description'])
					->setId ($row ['planid'])
					->setName ($row ['planname'])
					->setProduct (ProductManager::getInstance ($this->adb)->fetchProduct ($row ['productid']))
					->setStatus ($row ['status'])
					->setTotalApplications (intval ($row ['totalapplications']))
					->setTotalDiskSpace (intval ($row ['totaldiskspace']))
					->setTotalUsers (intval ($row ['totalusers']));
			} else {
				$plan = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $plan;
		}

		/**
		 * Buscar plan
		 *
		 * @param integer $planId
		 * @param string $instanceCode
		 * @param integer $numUsers
		 *
		 * @return null|PlatformBillingPlan
		 */
		public function fetchPlan ($planId, $instanceCode = null, $numUsers = 0) {
			if (empty ($planId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancebillingplans WHERE planid=?', array ($planId));
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$plan = PlatformBillingPlan::getInstance ()
					->setDescription ($row ['description'])
					->setId ($row ['planid'])
					->setName ($row ['planname'])
					->setProduct (ProductManager::getInstance ($this->adb)->fetchProduct ($row ['productid'], $instanceCode, $numUsers))
					->setStatus ($row ['status'])
					->setTotalApplications (intval ($row ['totalapplications']))
					->setTotalDiskSpace (intval ($row ['totaldiskspace']))
					->setTotalUsers (intval ($row ['totalusers']));
			} else {
				$plan = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $plan;
		}

		/**
		 * Para buscar los planes disponibles
		 *
		 * @param string $instanceCode
		 *
		 * @return null|PlatformBillingPlan[]
		 */
		public function fetchPlans ($instanceCode = null) {
			$result = $this->adb->query ('SELECT * FROM vtiger_instancebillingplans ORDER BY planid');
			if ($this->adb->num_rows ($result) > 0) {
				$pm    = ProductManager::getInstance ($this->adb);
				$plans = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$plans [] = PlatformBillingPlan::getInstance ()
						->setDescription ($row ['description'])
						->setId ($row ['planid'])
						->setName ($row ['planname'])
						->setProduct ($pm->fetchProduct ($row ['productid'], $instanceCode))
						->setStatus ($row ['status'])
						->setTotalApplications (intval ($row ['totalapplications']))
						->setTotalDiskSpace (intval ($row ['totaldiskspace']))
						->setTotalUsers (intval ($row ['totalusers']));
				}
			} else {
				$plans = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $plans;
		}

		/**
		 * Para guardar el plan
		 *
		 * @param PlatformBillingPlan $plan
		 *
		 * @return PlatformBillingPlan
		 * @throws PlatformBillingPlanException
		 */
		public function savePlan ($plan) {
			$this->validate ($plan);

			$planId = $plan->getId ();
			ProductManager::getInstance ($this->adb)->saveProduct ($plan->getProduct ());
			if (empty ($planId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_instancebillingplans (planname, description, totalapplications, totalusers, totaldiskspace, productid, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
					array ($plan->getName (), $plan->getDescription (), $plan->getTotalApplications (), $plan->getTotalUsers (), $plan->getTotalDiskSpace (), $plan->getProduct ()->getId (), $plan->getStatus ())
				);
				$planId = $this->adb->getLastInsertID ();
				$plan->setId ($planId);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_instancebillingplans SET planname=?, description=?, totalapplications=?, totalusers=?, totaldiskspace=?, productid=?, status=? WHERE planid=?',
					array ($plan->getName (), $plan->getDescription (), $plan->getTotalApplications (), $plan->getTotalUsers (), $plan->getTotalDiskSpace (), $plan->getProduct ()->getId (), $plan->getStatus (), $planId)
				);
			}
			return $plan;
		}

		/**
		 * Instanciación de la clase PlatformBillingPlanManager. Se obtiene un objeto PlatformBillingPlanManager con los atributos de la clase
		 *
		 * @param PearDatabase $adb
		 *
		 * @return PlatformBillingPlanManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}
