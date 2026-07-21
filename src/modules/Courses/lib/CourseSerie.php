<?php
	
	class CourseSerie  implements Serializable {
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $name;
		
		/** @var string */
		private $status;
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @param $id
		 *
		 * @return CourseSerie
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param $status
		 *
		 * @return CourseSerie
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param $name
		 * @return CourseSerie
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}
		
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->name,
					$this->status
				)
			);
		}
		
		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->name,
				$this->status
				) = unserialize ($serialized);
		}
		
		/**
		 * @return CourseSerie
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
