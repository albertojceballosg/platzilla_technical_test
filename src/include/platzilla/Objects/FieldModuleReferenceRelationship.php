<?php

	/**
	 * Class FieldModuleReferenceRelationship
	 *
	 * Esta clase "Relaciones Campo Referencia Modulo" hace referencia a las relaciones que se establecen con otros módulos,
	 * en los campos del tipo "Referencia a Módulo" que contiene un Módulo
	 */
	class FieldModuleReferenceRelationship {
		/** @var string */
		private $fieldName;

		/** @var string */
		private $referencedFieldName;

		/**
		 * Para obtener el nombre del campo
		 *
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * Para obtener el nombre de la referencia del campo
		 *
		 * @return string
		 */
		public function getReferencedFieldName () {
			return $this->referencedFieldName;
		}

		/**
		 * Estable el nombre del campo
		 *
		 * @param string $fieldName
		 *
		 * @return FieldModuleReferenceRelationship
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}

		/**
		 * Establece el nombre de la referencia del campo
		 *
		 * @param string $referencedFieldName
		 *
		 * @return FieldModuleReferenceRelationship
		 */
		public function setReferencedFieldName ($referencedFieldName) {
			$this->referencedFieldName = $referencedFieldName;
			return $this;
		}

		/**
		 * Realiza copia de los valores/atributos de las relaciones del campo
		 *
		 * @param FieldModuleReferenceRelationship $relationship
		 */
		public function copyValuesFrom ($relationship) {
			if ((empty ($relationship)) || (!($relationship instanceof FieldModuleReferenceRelationship))) {
				return;
			}

			$this->fieldName = $relationship->getFieldName ();
			$this->referencedFieldName = $relationship->getReferencedFieldName ();
		}

		/**
		 * Realiza la accion de duplicar las relaciones del campo referencia a modulo
		 *
		 * @return FieldModuleReferenceRelationship
		 * @throws FieldModuleReferenceRelationshipException
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setFieldName ($this->fieldName)
				->setReferencedFieldName ($this->referencedFieldName);
		}

		/**
		 * Compara si las relaciones del campo referencia a modulo es igual a otro
		 *
		 * @param FieldModuleReferenceRelationship $relationship
		 *
		 * @return boolean
		 */
		public function isEqualTo ($relationship) {
			if (
				(empty ($relationship)) ||
				(!($relationship instanceof FieldModuleReferenceRelationship)) ||
				($this->fieldName != $relationship->getFieldName ()) ||
				($this->referencedFieldName != $relationship->getReferencedFieldName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida si los valores/atributos (nombre del campo y las relaciones) del campo referencia a modulo estan definidos
		 *
		 * @throws FieldModuleReferenceRelationshipException
		 */
		public function validate () {
			if (empty ($this->fieldName)) {
				throw new FieldModuleReferenceRelationshipException (FieldModuleReferenceRelationshipException::ERROR_FIELD_MODULE_REFERENCE_RELATIONSHIP_EMPTY_FIELD_NAME);
			} else if (empty ($this->referencedFieldName)) {
				throw new FieldModuleReferenceRelationshipException (FieldModuleReferenceRelationshipException::ERROR_FIELD_MODULE_REFERENCE_RELATIONSHIP_EMPTY_REFERENCED_FIELD_NAME);
			}
		}

		/**
		 * Instanciación de la clase FieldModuleReferenceRelationship. Se obtiene un objeto FieldModuleReferenceRelationship con los atributos de la clase
		 *
		 * @return FieldModuleReferenceRelationship
		 */
		public static function getInstance () {
			return new self ();
		}

	}
