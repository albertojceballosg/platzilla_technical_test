<?php
	class TableFieldCheckbox {
		
		/** @var string */
		private $fieldName;
		
		/** @var array */
		private $relatedFields;
		
		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
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
		 * @return TableFieldCheckbox
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}
		
		/**
		 * @param array $relatedFields
		 *
		 * @return TableFieldCheckbox
		 */
		public function setRelatedFields ($relatedFields) {
			$this->relatedFields = $relatedFields;
			return $this;
		}
		
		/**
		 * @return TableFieldCheckbox
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
