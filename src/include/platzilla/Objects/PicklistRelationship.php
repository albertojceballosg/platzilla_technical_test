<?php
	require_once ('include/platzilla/Exceptions/PicklistRelationshipException.php');
	require_once ('include/platzilla/Objects/PicklistRelationshipMaster.php');

	/**
	 * Class PicklistRelationship
	 *
	 * En este clase se define el objeto "Relaciones Picklist" el cual hace referencia a las relaciones que se pueden establecer con los campos del tipo picklist
	 */
	class PicklistRelationship implements PicklistRelationshipInterface {

		/** @var integer */
		private $id;

		/** @var string */
		private $daughterPicklistName;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $motherPicklistName;

		/** @var boolean */
		private $locked;

		/** @var PicklistRelationshipMaster [] */
		private $picklistRelationshipMaster;

		/** @var string */
		private $relationshipName;

		/**
		 * Para copiar la relacion maestra del picklist
		 *
		 * @param PicklistRelationshipMaster[] $relationshipMaster
		 *
		 * @return array|null
		 */
		private function copyRelationshipMaster ($relationshipMaster) {
			if (empty($relationshipMaster)) {
				return null;
			}
			$relationshipMasters = array();
			foreach ($relationshipMaster as $prsm) {
				if (empty($prsm) || !$prsm instanceof PicklistRelationshipMaster) {
					continue;
				}
				$relationshipMasters [] = $prsm->duplicate ();
			}
			return (count ($relationshipMasters)) ? $relationshipMasters : null;
		}

		/**
		 * Para duplicar la relacion maestra del picklist desde otro picklist
		 *
		 * @param PicklistRelationshipMaster[] $relationshipMaster
		 *
		 * @return PicklistRelationshipMaster[]
		 */
		private function duplicateFromRelationshipMaster ($relationshipMaster) {
			$relationshipMasters = array ();
			foreach ($relationshipMaster as $prsm) {
				$relationshipMasters [] = $prsm->duplicate ();
			}
			return $relationshipMasters;
		}

		/**
		 * Para realizar comparacion si es igual la relacion maestra de un picklist con otro
		 *
		 * @param PicklistRelationshipMaster[] $theseRelationshipMaster
		 * @param PicklistRelationshipMaster[] $thoseRelationshipMaster
		 *
		 * @return boolean
		 */
		private function isRelationshipMasterEqualTo ($theseRelationshipMaster, $thoseRelationshipMaster) {
			$totalFieldsField = count ($theseRelationshipMaster);
			$equals          = true;
			if ($totalFieldsField != count ($thoseRelationshipMaster)) {
				return false;
			}

			for ($k = 0; $k < $totalFieldsField; $k++) {
				if (!$theseRelationshipMaster [ $k ]->isEqualTo ($thoseRelationshipMaster [ $k ])) {
					$equals = false;
				}
			}
			return $equals;
		}

		/**
		 * Realiza copia de los valores del picklist desde otro picklist
		 *
		 * @param PicklistRelationship $prs
		 */
		public function copyValuesFrom ($prs) {
			if ((empty ($prs)) || (!($prs instanceof PicklistRelationship))) {
				return;
			}
			$this->id                         = $prs->getId ();
			$this->daughterPicklistName       = $prs->getDaughterPicklistName ();
			$this->moduleName                 = $prs->getModuleName ();
			$this->motherPicklistName         = $prs->getMotherPicklistName ();
			$this->locked                     = $prs->isLocked ();
			$this->picklistRelationshipMaster = $prs->copyRelationshipMaster ($prs->getPicklistRelationshipMaster());
			$this->relationshipName           = $prs->getRelationshipName ();
		}

		/**
		 * Duplica las relaciones del picklist
		 *
		 * @param null|integer $relationshipId
		 *
		 * @return PicklistRelationship
		 */
		public function duplicate ($relationshipId = null) {
			$object = new self ();
			return $object->setId ($relationshipId)
				->setDaughterPicklistName ($this->daughterPicklistName)
				->setModuleName ($this->moduleName)
				->setMotherPicklistName ($this->motherPicklistName)
				->setLocked ($this->locked)
				->setPicklistRelationshipMaster ($this->duplicateFromRelationshipMaster ($this->picklistRelationshipMaster))
				->setRelationshipName ($this->relationshipName);
		}

		/**
		 * Para obtener el ID del picklist relacionado
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Obtiene el nombre del picklist hijo a relacionar
		 *
		 * @return string
		 */
		public function getDaughterPicklistName () {
			return $this->daughterPicklistName;
		}

		/**
		 * Realiza comparacion si es igual la relacion de un picklist con otro
		 *
		 * @param $prs
		 *
		 * @return boolean
		 */
		public function isEqualTo ($prs) {
			if (
				(empty ($prs)) ||
				($this->isRelationshipMasterEqualTo ($this->picklistRelationshipMaster, $prs->getPicklistRelationshipMaster ())) ||
				($this->daughterPicklistName != $prs->getDaughterPicklistName ()) ||
				($this->moduleName != $prs->getModuleName ()) ||
				($this->motherPicklistName != $prs->getMotherPicklistName ()) ||
				($this->relationshipName != $prs->getRelationshipName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Para realizar el bloqueo de la relacion del picklist
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Para obtener el nombre del modulo donde se encuentra el picklist y su relacion
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Obtiene el nombre del picklist padre desde donde se establece la relacion
		 *
		 * @return string
		 */
		public function getMotherPicklistName () {
			return $this->motherPicklistName;
		}

		/**
		 * Obtiene la relacion del picklist padre
		 *
		 * @return PicklistRelationshipMaster[]
		 */
		public function getPicklistRelationshipMaster() {
			return $this->picklistRelationshipMaster;
		}

		/**
		 * Obtiene el nombre de la relacion
		 *
		 * @return string
		 */
		public function getRelationshipName () {
			return $this->relationshipName;
		}

		/**
		 * Establece el ID del picklist y su relacion
		 *
		 * @param integer $id
		 *
		 * @return PicklistRelationship
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece el nombre del picklist hijo a relacionar
		 *
		 * @param string $daughterPicklistName
		 *
		 * @return PicklistRelationship
		 */
		public function setDaughterPicklistName ($daughterPicklistName) {
			$this->daughterPicklistName = $daughterPicklistName;
			return $this;
		}

		/**
		 * Para establecer el bloqueo de la relacion del picklist
		 *
		 * @param boolean $locked
		 *
		 * @return PicklistRelationship
		 */
		public function setLocked ($locked) {
			$this->locked = $locked;
			return $this;
		}

		/**
		 * Establece el nombre del modulo donde se encuentra el picklist y su relacion
		 *
		 * @param string $moduleName
		 *
		 * @return PicklistRelationship
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece el nombre del picklist padre
		 *
		 * @param string $motherPicklistName
		 *
		 * @return PicklistRelationship
		 */
		public function setMotherPicklistName ($motherPicklistName) {
			$this->motherPicklistName = $motherPicklistName;
			return $this;
		}

		/**
		 * Establece la relación para el picklist maestro
		 *
		 * @param PicklistRelationshipMaster[] $picklistRelationshipMaster
		 *
		 * @return PicklistRelationship
		 */
		public function setPicklistRelationshipMaster ($picklistRelationshipMaster) {
			$this->picklistRelationshipMaster = $picklistRelationshipMaster;
			return $this;
		}

		/**
		 * Establece el nombre de la relacion del picklist
		 *
		 * @param string $relationshipName
		 *
		 * @return PicklistRelationship
		 */
		public function setRelationshipName ($relationshipName) {
			$this->relationshipName = $relationshipName;
			return $this;
		}

		/**
		 * Valida si la relaciones del picklist tiene asignado picklist padre, nombre del modulo, nombre del picklist padre y las relaciones no estan vacias
		 *
		 * @throws PicklistRelationshipException
		 */
		public function validate () {
			if (empty ($this->daughterPicklistName)) {
				throw new PicklistRelationshipException (PicklistRelationshipException::ERROR_PICKLIST_RELATIONSHIP_DAUGHTER_NAME_EMPTY);
			} else if (empty ($this->moduleName)) {
				throw new PicklistRelationshipException (PicklistRelationshipException::ERROR_PICKLIST_RELATIONSHIP_MODULE_NAME_EMPTY);
			} else if (empty ($this->motherPicklistName)) {
				throw new PicklistRelationshipException (PicklistRelationshipException::ERROR_PICKLIST_RELATIONSHIP_MOTHER_NAME_EMPTY);
			} else if (empty ($this->relationshipName)) {
				throw new PicklistRelationshipException (PicklistRelationshipException::ERROR_PICKLIST_RELATIONSHIP_RELATIONSHIP_NAME_EMPTY);
			} else if (!count ($this->picklistRelationshipMaster)) {
				throw new PicklistRelationshipException (PicklistRelationshipException::ERROR_PICKLIST_RELATIONSHIP_RELATIONSHIP_MASTER_EMPTY);
			}
		}

		/**
		 * Se obtiene un objeto PicklistRelationship con los atributos de la clase
		 *
		 * @return PicklistRelationship
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
