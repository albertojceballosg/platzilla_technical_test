<?php
	
	class TableFieldImport {
		
		/** @var string */
		private $fieldName;
		
		/** @var string */
		private $moduleName;
		
		/** @var array */
		private $relatedFields;
		
		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}
		
		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}
		
		/**
		 * @return array
		 */
		public function getRelatedFields () {
			return $this->relatedFields;
		}
		
		/**
		 * @param string $fieldName
		 *
		 * @return TableFieldImport
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return TableFieldImport
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}
		
		/**
		 * @param array $relatedFields
		 *
		 * @return TableFieldImport
		 */
		public function setRelatedFields ($relatedFields) {
			if (is_array ($relatedFields) && count ($relatedFields)) {
				$row = 0;
				$relationship = array();
				foreach ($relatedFields as $relatedField) {
					$relationship [$relatedField['modulefield'][$row]] = $relationship ['tablefield'][$row];
					$row++;
				}
				$this->relatedFields = $relationship;
			} else {
				$this->relatedFields  = null;
			}
			
			return $this;
		}
		
		/**
		 * @return TableFieldImport
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
