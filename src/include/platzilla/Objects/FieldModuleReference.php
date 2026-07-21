<?php
	require_once ('include/platzilla/Exceptions/FieldModuleReferenceException.php');
	require_once ('include/platzilla/Exceptions/FieldModuleReferenceRelationshipException.php');
	require_once ('include/platzilla/Objects/FieldModuleReferenceFilter.php');
	require_once ('include/platzilla/Objects/FieldModuleReferenceRelationship.php');

	/**
	 * Class FieldModuleReference
	 *
	 * Esta clase define el objeto "Campo Referencia Modulo" hace referencia a los campos del tipo "Referencia a Módulo" que contiene un Módulo.
	 * Este tipo de campos permiten asociar registros provenientes de distintos módulos.
	 * El objeto está asociado al objeto de "Relaciones Campo Referencia Módulo".
	 *
	 */
	class FieldModuleReference {
		/** @var boolean */
		private $deleted;

		/** @var string */
		private $fieldName;

		/** @var FieldModuleReferenceFilter[] */
		private $filters;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $referencedModuleName;

		/** @var FieldModuleReferenceRelationship[] */
		private $relationships;

		/** @var integer */
		private $sequence;

		/** @var string */
		private $status;

		/**
		 * FieldModuleReference constructor.
		 */
		public function __construct () {
			$this->deleted = false;
			$this->locked  = false;
		}

		/**
		 * Realiza copia del filtro que posea el campo referencia a modulo
		 *
		 * @param FieldModuleReferenceFilter[] $sourceFilters
		 */
		private function copyFilters ($sourceFilters) {
			$filters = array ();
			foreach ($sourceFilters as $sourceFilter) {
				$found = false;
				foreach ($this->filters as $targetFilter) {
					if (!$targetFilter->isEqualTo ($sourceFilter)) {
						continue;
					}
					$filters [] = $targetFilter;
					$found      = true;
					break;
				}
				if (!$found) {
					$filters [] = $sourceFilter->duplicate ();
				}
			}
			$this->filters = $filters;
		}

		/**
		 * Realiza copia del filtro que posea el campo referencia a modulo desde otro campo
		 *
		 * @param FieldModuleReference $reference
		 */
		private function copyFiltersFrom ($reference) {
			$sourceFilters = $reference->getFilters ();
			if ((empty ($sourceFilters)) && (empty ($this->filters))) {
				return;
			}

			if (empty ($sourceFilters)) {
				$this->filters = null;
			} else if (empty ($this->filters)) {
				$filters = array ();
				foreach ($sourceFilters as $sourceFilter) {
					$filters [] = $sourceFilter->duplicate ();
				}
				$this->filters = $filters;
			} else {
				$this->copyFilters ($sourceFilters);
			}
		}

		/**
		 * Realiza copia de las relaciones que posea el campo referencia a modulo
		 *
		 * @param FieldModuleReferenceRelationship[] $sourceRelationships
		 */
		private function copyRelationships ($sourceRelationships) {
			$relationships = array ();
			foreach ($sourceRelationships as $sourceRelationship) {
				$found = false;
				foreach ($this->relationships as $targetRelationship) {
					if (!$targetRelationship->isEqualTo ($sourceRelationship)) {
						continue;
					}
					$relationships [] = $targetRelationship;
					$found            = true;
					break;
				}
				if (!$found) {
					$relationships [] = $sourceRelationship->duplicate ();
				}
			}
			$this->relationships = $relationships;
		}

		/**
		 * Realiza copia de las relaciones que posea el campo referencia a modulo desde otro campo
		 *
		 * @param FieldModuleReference $reference
		 */
		private function copyRelationshipsFrom ($reference) {
			$sourceRelationships = $reference->getRelationships ();
			if ((empty ($sourceRelationships)) && (empty ($this->relationships))) {
				return;
			}

			if (empty ($sourceRelationships)) {
				$this->relationships = null;
			} else if (empty ($this->relationships)) {
				$relationships = array ();
				foreach ($sourceRelationships as $sourceRelationship) {
					$relationships [] = $sourceRelationship->duplicate ();
				}
				$this->relationships = $relationships;
			} else {
				$this->copyRelationships ($sourceRelationships);
			}
		}

		/**
		 * Realiza duplicado de los filtros que posea el campo referencia a modulo
		 *
		 * @return FieldModuleReferenceFilter[]|null
		 */
		private function duplicateFilters () {
			if (empty ($this->filters)) {
				return null;
			}
			$filters = array ();
			foreach ($this->filters as $filter) {
				$filters [] = $filter->duplicate ();
			}
			return $filters;
		}

		/**
		 * Duplica las relaciones que posea el campo referencia a modulo
		 *
		 * @return FieldModuleReferenceRelationship[]|null
		 */
		private function duplicateRelationships () {
			if (empty ($this->relationships)) {
				return null;
			}
			$relationships = array ();
			foreach ($this->relationships as $relationship) {
				$relationships [] = $relationship->duplicate ();
			}
			return $relationships;
		}

		/**
		 * Valida el filtro que posea el campo referencia a modulo
		 *
		 * @throws FieldModuleReferenceException
		 */
		private function validateFilters () {
			if (empty ($this->filters)) {
				return;
			}

			foreach ($this->filters as $filter) {
				if (!($filter instanceof FieldModuleReferenceFilter)) {
					throw new FieldModuleReferenceException (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_INVALID_FILTER);
				} else {
					$filter->validate ();
				}
			}
		}

		/**
		 * Valida las relaciones que posea el campo referencia a modulo
		 *
		 * @throws FieldModuleReferenceException
		 * @throws FieldModuleReferenceRelationshipException
		 */
		private function validateRelationships () {
			if (empty ($this->relationships)) {
				return;
			}

			foreach ($this->relationships as $relationship) {
				if (!($relationship instanceof FieldModuleReferenceRelationship)) {
					throw new FieldModuleReferenceException (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_INVALID_RELATIONSHIP);
				} else {
					$relationship->validate ();
				}
			}
		}

		/**
		 * Para obtener el nombre del campo referencia a modulo
		 *
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * Para obtener el filtro que posea el campo referencia a modulo
		 *
		 * @return FieldModuleReferenceFilter[]
		 */
		public function getFilters () {
			return $this->filters;
		}

		/**
		 * Para obtener el nombre del modulo que contiene al campo referencia a modulo
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener el nombre del modulo al cual hace referencia el campo de referencia a modulo
		 *
		 * @return string
		 */
		public function getReferencedModuleName () {
			return $this->referencedModuleName;
		}

		/**
		 * Para obtener las relaciones que posea el campo
		 *
		 * @return FieldModuleReferenceRelationship[]
		 */
		public function getRelationships () {
			return $this->relationships;
		}

		/**
		 * Para obtener la secuencia del campo referencia a modulo
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener el estatus del campo referencia a modulo
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Para realizar el borrado del campo referencia a modulo
		 *
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * Obtiene el valor de la bandera que controla si el campo refencia a modulo puede bloquearse o no
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Establece la accion del borrado del campo referencia a modulo
		 *
		 * @param boolean $deleted
		 *
		 * @return FieldModuleReference
		 */
		public function setDeleted ($deleted) {
			if ((is_bool ($deleted)) && (boolval ($deleted) == $deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * Establece el nombre para el campo referencia a modulo
		 *
		 * @param string $fieldName
		 *
		 * @return FieldModuleReference
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * Establece el valor de la bandera que controla si el campo refencia a modulo puede bloquearse o no
		 *
		 * @param boolean $locked
		 *
		 * @return FieldModuleReference
		 */
		public function setLocked ($locked) {
			if ((is_bool ($locked)) && (boolval ($locked) == $locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * Establece el nombre del modulo que contiene al campo referencia a modulo
		 *
		 * @param string $moduleName
		 *
		 * @return FieldModuleReference
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece el nombre del modulo al cual hace referencia el campo de referencia a modulo
		 *
		 * @param string $referencedModuleName
		 *
		 * @return FieldModuleReference
		 */
		public function setReferencedModuleName ($referencedModuleName) {
			$this->referencedModuleName = $referencedModuleName;
			return $this;
		}

		/**
		 * Establece el filtro que posea el campo referencia a modulo
		 *
		 * @param FieldModuleReferenceFilter[] $filters
		 *
		 * @return FieldModuleReference
		 */
		public function setFilters ($filters) {
			if (($filters === null) || ((is_array ($filters)) && (!empty ($filters)))) {
				$this->filters = $filters;
			}
			return $this;
		}

		/**
		 * Establece las relaciones que posea el campo
		 *
		 * @param FieldModuleReferenceRelationship[] $relationships
		 *
		 * @return FieldModuleReference
		 */
		public function setRelationships ($relationships) {
			if (($relationships === null) || ((is_array ($relationships)) && (!empty ($relationships)))) {
				$this->relationships = $relationships;
			}
			return $this;
		}

		/**
		 * Establece la secuencia del campo referencia a modulo
		 *
		 * @param integer $sequence
		 *
		 * @return FieldModuleReference
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Establece el estatus del campo referencia a modulo
		 *
		 * @param string $status
		 *
		 * @return FieldModuleReference
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * Realiza copia de los atributos/valores del campo referencia a modulo
		 *
		 * @param FieldModuleReference $reference
		 */
		public function copyValuesFrom ($reference) {
			if ((empty ($reference)) || (!($reference instanceof FieldModuleReference))) {
				return;
			}

			$this->fieldName            = $reference->getFieldName ();
			$this->moduleName           = $reference->getModuleName ();
			$this->referencedModuleName = $reference->getReferencedModuleName ();
			$this->sequence             = $reference->getSequence ();
			$this->status               = $reference->getStatus ();
			$this->copyFiltersFrom ($reference);
			$this->copyRelationshipsFrom ($reference);
		}

		/**
		 * Realiza la accion de duplicar el campo referencia a modulo
		 *
		 * @return FieldModuleReference
		 * @throws FieldModuleReferenceException
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setFieldName ($this->fieldName)
				->setFilters ($this->duplicateFilters ())
				->setModuleName ($this->moduleName)
				->setReferencedModuleName ($this->referencedModuleName)
				->setRelationships ($this->duplicateRelationships ())
				->setSequence ($this->sequence)
				->setStatus ($this->status);
		}

		/**
		 * Compara si el campo referencia a modulo es igual a otro
		 *
		 * @param FieldModuleReference $reference
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($reference, $deepCheck = true) {
			if (
				(empty ($reference)) ||
				(!($reference instanceof FieldModuleReference)) ||
				($this->fieldName != $reference->getFieldName ()) ||
				($this->moduleName != $reference->getModuleName ()) ||
				($this->referencedModuleName != $reference->getReferencedModuleName ()) ||
				(($deepCheck) && ((!MiscellaneousUtils::areObjectArraysEqual ($this->relationships, $reference->getRelationships ())) || (!MiscellaneousUtils::areObjectArraysEqual ($this->filters, $reference->getFilters ()))))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida si los valores/atributos (filtro y relaciones) del campo referencia a modulo estan definidos
		 *
		 * @throws FieldModuleReferenceException
		 */
		public function validate () {
			if (empty ($this->referencedModuleName)) {
				throw new FieldModuleReferenceException (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_EMPTY_REFERENCED_MODULE_NAME);
			}
			$this->validateFilters ();
			$this->validateRelationships ();
		}

		/**
		 * Instanciación de la clase FieldModuleReference. Se obtiene un objeto FieldModuleReference con los atributos de la clase
		 *
		 * @return FieldModuleReference
		 */
		public static function getInstance () {
			return new self ();
		}

	}
