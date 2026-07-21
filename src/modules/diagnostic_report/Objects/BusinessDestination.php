<?php
	
	class BusinessDestination {
		
		/** @var array */
		private $businessPhase;
		
		/** @var array */
		private $businessType;
		
		/** @var array */
		private $categories;
		
		/** @var string */
		private $codeDestination;
		
		/** @var string */
		Private $description;
		
		/** @var string */
		private $destinationName;
		
		/** @var string */
		private $ending;
		
		/** @var string */
		private $endingUnit;
		
		/**
		 * @return array
		 */
		public function getBusinessPhase () {
			return $this->businessPhase;
		}
		
		/**
		 * @return array
		 */
		public function getBusinessType () {
			return $this->businessType;
		}
		
		/**
		 * @return array
		 */
		public function getCategories () {
			return $this->categories;
		}
		
		/**
		 * @return string
		 */
		public function getCodeDestination () {
			return $this->codeDestination;
		}
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}
		
		/**
		 * @return integer
		 */
		public function getDestinationId () {
			return $this->destinationId;
		}
		
		/**
		 * @return string
		 */
		public function getDestinationName (){
			return $this->destinationName;
		}
		
		/** @var integer */
		private $destinationId;
		
		/**
		 * @return string
		 */
		public function getEnding () {
			return $this->ending;
		}
		
		/**
		 * @return string
		 */
		public function getEndingUnit () {
			return $this->endingUnit;
		}
		
		/**
		 * @param array $businessPhase
		 *
		 * @return BusinessDestination
		 */
		public function setBusinessPhase ($businessPhase) {
			if (is_array ($businessPhase)) {
				foreach ($businessPhase as $phase) {
					$this->businessPhase [] = trim ($phase);
				}
			} else {
				$this->businessPhase = array();
			}
			return $this;
		}
		
		/**
		 * @param array $businessType
		 *
		 * @return BusinessDestination
		 */
		public function setBusinessType ($businessType) {
			if (is_array ($businessType)) {
				foreach ($businessType as $type) {
					$this->businessType [] = trim ($type);
				}
			} else {
				$this->businessType = array ();
			}
			return $this;
		}
		
		/**
		 * @param array $categories
		 *
		 * @return BusinessDestination
		 */
		public function setCategories ($categories) {
			if (is_array ($categories)) {
				foreach ($categories as $category) {
					$this->categories [] = trim ($category);
				}
			} else {
				$this->categories = array ();
			}
			return $this;
		}
		
		/**
		 * @param string $codeDestination
		 *
		 * @return BusinessDestination
		 */
		public function setCodeDestination ($codeDestination) {
			$this->codeDestination = $codeDestination;
			return $this;
		}
		
		/**
		 * @param string $description
		 *
		 * @return BusinessDestination
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param integer $destinationId
		 *
		 * @return BusinessDestination
		 */
		public function setDestinationId ($destinationId) {
			$this->destinationId = $destinationId;
			return $this;
		}
		
		
		/**
		 * @param $destinationName
		 *
		 * @return BusinessDestination
		 */
		public function setDestinationName ($destinationName) {
			$this->destinationName = $destinationName;
			return $this;
		}
		
		/**
		 * @param string $ending
		 *
		 * @return BusinessDestination
		 */
		public function setEnding ($ending) {
			$this->ending = $ending;
			return $this;
		}
		
		/**
		 * @param string $endingUnit
		 *
		 * @return BusinessDestination
		 */
		public function setEndingUnit ($endingUnit) {
			$this->endingUnit = $endingUnit;
			return $this;
		}
		
		/**
		 * @return BusinessDestination
		 */
		public static function getInstance () {
	            return new self();
		}
		
	}
