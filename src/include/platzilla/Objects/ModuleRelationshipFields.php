<?php
	require_once ('include/platzilla/Exceptions/ModuleRelationshipException.php');
	require_once ('include/platzilla/Objects/ModuleRelationshipInterface.php');
	
	class ModuleRelationshipFields implements ModuleRelationshipInterface {
		
		/** @var array */
		private $fieldImport;
		
		/** @var array */
		private $fieldList;
		
		/** @var boolean */
		private $locked;
		
		/** @var string */
		private $moduleName;
		
		/** @var integer */
		private $relationId;
		
		/**
		 * @return array
		 */
		public function getFieldImport () {
			return $this->fieldImport;
		}
		
		/**
		 * @return array
		 */
		public function getFieldList () {
			return $this->fieldList;
		}
		
		/**
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}
		
		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}
		
		/**
		 * @return integer
		 */
		public function getRelationId () {
			return $this->relationId;
		}
		
		/**
		 * @param array $fieldImport
		 *
		 * @return ModuleRelationshipFields
		 */
		public function setFieldImport ($fieldImport) {
			$this->fieldImport = $fieldImport;
			return $this;
		}
		
		/**
		 * @param array $fieldList
		 *
		 * @return ModuleRelationshipFields
		 */
		public function setFieldList ($fieldList) {
			$this->fieldList = $fieldList;
			return $this;
		}
		
		/**
		 * @param boolean $locked
		 *
		 * @return ModuleRelationshipFields
		 */
		public function setLocked ($locked) {
			$this->locked = $locked;
			return $this;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return ModuleRelationshipFields
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}
		
		/**
		 * @param integer $relationId
		 *
		 * @return ModuleRelationshipFields
		 */
		public function setRelationId ($relationId) {
			$this->relationId = $relationId;
			return $this;
		}
		
		/**
		 * @return ModuleRelationshipFields
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
