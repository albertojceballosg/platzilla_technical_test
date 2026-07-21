<?php

	class TableFieldList {
		
		/** @var string */
		private $fieldName;
		
		/** @var array */
		private $fieldValues;
		
		/** @var array */
		private $relatedPickList;
		
		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}
		
		/**
		 * @return array
		 */
		public function getFieldValues () {
			return $this->fieldValues;
		}
		
		/**
		 * @return array
		 */
		public function getRelatedPickList () {
			return $this->relatedPickList;
		}
		
		/**
		 * @param string $fieldName
		 *
		 * @return TableFieldList
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}
		
		/**
		 * @param string $fieldValues
		 *
		 * @return TableFieldList
		 */
		public function setFieldValues ($fieldValues) {
			$this->fieldValues = explode ('@', $fieldValues);
			return $this;
		}
		
		/**
		 * @param array $relatedPickLists
		 *
		 * @return TableFieldList
		 */
		public function setRelatedPickList ($relatedPickLists) {
			if (is_array ($relatedPickLists) && count ($relatedPickLists)) {
				$this->relatedPickList = $relatedPickLists;
			} else {
				$this->relatedPickList = null;
			}
			return $this;
		}
		
		/**
		 * @return TableFieldList
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
