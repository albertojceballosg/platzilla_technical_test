<?php
	require_once ('modules/operating_modes/Exceptions/OperatingModesException.php');
	require_once ('modules/operating_modes/Objects/OperatingModesInterface.php');

	class ModesContent implements OperatingModesInterface {

		/** @var string */
		private $action;

		/** @var string */
		private $attributes;

		/** @var string|array */
		private $bufferOut;

		/** @var integer */
		private $id;

		/** @var string */
		private $label;

		/** @var string */
		private $name;

		/** @var string */
		private $script;

		/** @var string|integer */
		private $value;

		/**
		 * @return string
		 */
		public function getAction () {
			return $this->action;
		}

		/**
		 * @return string
		 */
		public function getAttributes () {
			return $this->attributes;
		}

		/**
		 * @return array|string
		 */
		public function getBufferOut () {
			return $this->bufferOut;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
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
		public function getScript () {
			return $this->script;
		}

		/**
		 * @return integer|string
		 */
		public function getValue() {
			return $this->value;
		}

		/**
		 * @param string $action
		 *
		 * @return ModesContent
		 */
		public function setAction ($action) {
			$this->action = $action;
			return $this;
		}

		/**
		 * @param string $attributes
		 *
		 * @return ModesContent
		 */
		public function setAttributes ($attributes) {
			$this->attributes = $attributes;
			return $this;
		}

		/**
		 * @param array|string $bufferOut
		 *
		 * @return ModesContent
		 */
		public function setBufferOut ($bufferOut) {
			$this->bufferOut = $bufferOut;
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return ModesContent
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return ModesContent
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return ModesContent
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param string $script
		 *
		 * @return ModesContent
		 */
		public function setScript ($script) {
			$this->script = $script;
			return $this;
		}

		/**
		 * @param integer|string $value
		 */
		public function setValue ($value) {
			$this->value = $value;
		}

		/**
		 * @return ModesContent
		 */
		public static function getInstance () {
			return new self ();
		}

	}
