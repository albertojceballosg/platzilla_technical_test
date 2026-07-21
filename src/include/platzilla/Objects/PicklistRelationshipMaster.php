<?php
	require_once ('include/platzilla/Objects/PicklistRelationshipInterface.php');

	/**
	 * Class PicklistRelationshipMaster
	 *
	 * En este clase se define el objeto "Relaciones Picklist Maestro" el cual hace referencia a las relaciones del picklist padre
	 */
	class PicklistRelationshipMaster implements PicklistRelationshipInterface {

		/** @var integer */
		private $id;

		/** @var array */
		private $daughterPicklistValuesId;

		/** @var integer */
		private $motherPicklistValueId;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $relationshipName;

		/** @var array */
		private $relationshipValues;

		/**
		 * Realiza copia de los valores del picklist padre desde otro picklist
		 *
		 * @param PicklistRelationshipMaster $prsm
		 */
		public function copyValuesFrom ($prsm) {
			if ((empty ($prsm)) || (!($prsm instanceof PicklistRelationshipMaster))) {
				return;
			}
			$this->id                       = $prsm->getId ();
			$this->daughterPicklistValuesId = $prsm->getDaughterPicklistValuesId ();
			$this->motherPicklistValueId    = $prsm->getMotherPicklistValueId ();
			$this->locked                   = $prsm->isLocked ();
			$this->relationshipName         = $prsm->getRelationshipName ();
			$this->relationshipValues       = $prsm->getRelationshipValues ();
		}

		/**
		 * Duplica las relaciones del picklist padre
		 *
		 * @param null|integer $relationshipMasterId
		 *
		 * @return PicklistRelationshipMaster
		 */
		public function duplicate ($relationshipMasterId = null) {
			$object = new self ();
			return $object->setId ($relationshipMasterId)
				->setDaughterPicklistValuesId ($this->daughterPicklistValuesId)
				->setMotherPicklistValueId ($this->motherPicklistValueId)
				->setLocked ($this->locked)
				->setRelationshipName ($this->relationshipName)
				->setRelationshipValues ($this->relationshipValues);
		}

		/**
		 * Para obtener el ID del picklist padre relacionado
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return array
		 */
		public function getDaughterPicklistValuesId () {
			return $this->daughterPicklistValuesId;
		}

		/**
		 * Realiza comparacion si es igual la relacion de un picklist padre con otro
		 *
		 * @param PicklistRelationshipMaster $prsm
		 *
		 * @return boolean
		 */
		public function isEqualTo ($prsm) {
			if (
				(empty ($prsm)) ||
				(count (array_diff_assoc ($this->daughterPicklistValuesId, $prsm->getDaughterPicklistValuesId ()))) ||
				(count (array_diff_assoc ($prsm->getDaughterPicklistValuesId (), $this->daughterPicklistValuesId))) ||
				($this->motherPicklistValueId != $prsm->getMotherPicklistValueId ()) ||
				($this->relationshipName != $prsm->getRelationshipName ()) ||
				(count (array_diff_assoc ($prsm->getRelationshipValues (), $this->relationshipValues))) ||
				(count (array_diff_assoc ($this->relationshipValues, $prsm->getRelationshipValues ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Para realizar el bloqueo de la relacion del picklist padre
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Obtiene el valor del ID del picklist padre
		 *
		 * @return integer
		 */
		public function getMotherPicklistValueId () {
			return $this->motherPicklistValueId;
		}

		/**
		 * Obtiene el nombre de la relación del picklist padre
		 *
		 * @return string
		 */
		public function getRelationshipName () {
			return $this->relationshipName;
		}

		/**
		 * Obtiene los valores de la relacion del picklist padre
		 *
		 * @return array
		 */
		public function getRelationshipValues () {
			return $this->relationshipValues;
		}

		/**
		 * Establece el id de la relacion del picklist padre
		 *
		 * @param integer $id
		 *
		 * @return PicklistRelationshipMaster
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece el valor del ID para la relacion del picklist hijo
		 *
		 * @param array $daughterPicklistValuesId
		 *
		 * @return PicklistRelationshipMaster
		 */
		public function setDaughterPicklistValuesId ($daughterPicklistValuesId) {
			$this->daughterPicklistValuesId = $daughterPicklistValuesId;
			return $this;
		}

		/**
		 * Para establecer el bloqueo de la relacion del picklist padre
		 *
		 * @param boolean $locked
		 *
		 * @return PicklistRelationshipMaster
		 */
		public function setLocked ($locked) {
			$this->locked = $locked;
			return $this;
		}

		/**
		 * Establece el valor del ID para la relacion del picklist padre
		 *
		 * @param integer $motherPicklistValueId
		 *
		 * @return PicklistRelationshipMaster
		 */
		public function setMotherPicklistValueId ($motherPicklistValueId) {
			$this->motherPicklistValueId = $motherPicklistValueId;
			return $this;
		}

		/**
		 * Establece el nombre de la relacion del picklist padre
		 *
		 * @param string $relationshipName
		 *
		 * @return PicklistRelationshipMaster
		 */
		public function setRelationshipName ($relationshipName) {
			$this->relationshipName = $relationshipName;
			return $this;
		}

		/**
		 * Establece el valor de la relacion del picklist padre
		 *
		 * @param array $relationshipValues
		 *
		 * @return PicklistRelationshipMaster
		 */
		public function setRelationshipValues ($relationshipValues) {
			$this->relationshipValues = $relationshipValues;
			return $this;
		}

		/**
		 * Valida si la relaciones del picklist padre tiene asignado picklist hijo, nombre del modulo, nombre del picklist padre y las relaciones no estan vacias
		 *
		 * @throws PicklistRelationshipException
		 */
		public function validate () {
			if (!count ($this->daughterPicklistValuesId)) {
				throw new PicklistRelationshipException (PicklistRelationshipException::ERROR_PICKLIST_RELATIONSHIP_DAUGHTER_IDS_EMPTY);
			} else if (empty ($this->relationshipName)) {
				throw new PicklistRelationshipException (PicklistRelationshipException::ERROR_PICKLIST_RELATIONSHIP_RELATIONSHIP_NAME_EMPTY);
			} else if (empty ($this->motherPicklistValueId)) {
				throw new PicklistRelationshipException (PicklistRelationshipException::ERROR_PICKLIST_RELATIONSHIP_MOTHER_IDS_EMPTY);
			}
		}

		/**
		 * Se obtiene un objeto PicklistRelationshipMaster con los atributos de la clase
		 *
		 * @return PicklistRelationshipMaster
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
