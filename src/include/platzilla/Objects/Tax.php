<?php
	require_once ('include/platzilla/Exceptions/TaxException.php');
	require_once ('include/platzilla/Objects/TaxConditionGroup.php');

	class Tax implements Serializable {
		/** @var TaxConditionGroup[] */
		private $conditionGroups;

		/** @var boolean */
		private $default;

		/** @var string */
		private $description;

		/** @var integer */
		private $id;

		/** @var string */
		private $name;

		/** @var float */
		private $percentage;

		private function validateConditionGroups () {
			if (empty ($this->conditionGroups)) {
				return;
			}

			foreach ($this->conditionGroups as $group) {
				if (empty ($group)) {
					throw new TaxException (TaxException::ERROR_TAX_EMPTY_CONDITION_GROUP);
				} else if (!($group instanceof TaxConditionGroup)) {
					throw new TaxException (TaxException::ERROR_TAX_INVALID_CONDITION_GROUP);
				} else {
					$group->validate ();
				}
			}
		}

		/**
		 * @return TaxConditionGroup[]
		 */
		public function getConditionGroups () {
			return $this->conditionGroups;
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
		 * @return float
		 */
		public function getPercentage () {
			return $this->percentage;
		}

		/**
		 * @return boolean
		 */
		public function isDefault () {
			return $this->default;
		}

		/**
		 * @param TaxConditionGroup[] $conditionGroups
		 *
		 * @return Tax
		 */
		public function setConditionGroups ($conditionGroups) {
			if ((is_array ($conditionGroups)) && (!empty ($conditionGroups))) {
				$this->conditionGroups = $conditionGroups;
			} else {
				$this->conditionGroups = null;
			}
			return $this;
		}

		/**
		 * @param boolean $default
		 *
		 * @return Tax
		 */
		public function setDefault ($default) {
			if ((is_bool ($default)) && (boolval ($default) === $default)) {
				$this->default = boolval ($default);
			} else {
				$this->default = null;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return Tax
		 */
		public function setDescription ($description) {
			if (is_scalar ($description)) {
				$this->description = $description;
			} else {
				$this->description = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return Tax
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = $id;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return Tax
		 */
		public function setName ($name) {
			if (is_scalar ($name)) {
				$this->name = $name;
			} else {
				$this->name = null;
			}
			return $this;
		}

		/**
		 * @param float $percentage
		 *
		 * @return Tax
		 */
		public function setPercentage ($percentage) {
			if ((is_numeric ($percentage)) && ($percentage >= 0)) {
				$this->percentage = floatval ($percentage);
			} else {
				$this->percentage = null;
			}
			return $this;
		}

		/**
		 * @throws TaxException
		 */
		public function validate () {
			if ($this->default === null) {
				throw new TaxException (TaxException::ERROR_TAX_EMPTY_DEFAULT);
			} else if (empty ($this->name)) {
				throw new TaxException (TaxException::ERROR_TAX_EMPTY_NAME);
			} else if ($this->percentage === null) {
				throw new TaxException (TaxException::ERROR_TAX_EMPTY_PERCENTAGE);
			}
			$this->validateConditionGroups ();
		}

		/**
		 * @return string
		 */
		public function serialize () {
			$groups = $this->conditionGroups;
			if (!empty ($groups)) {
				$serializedGroups = array ();
				foreach ($groups as $group) {
					$serializedGroups [] = $group->serialize ();
				}
			} else {
				$serializedGroups = null;
			}

			return serialize (
				array (
					$this->id,
					$this->default,
					$this->description,
					$this->name,
					$this->percentage,
					$serializedGroups,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->default,
				$this->description,
				$this->name,
				$this->percentage,
				$serializedGroups,
				) = unserialize ($serialized);

			if (!empty ($serializedGroups)) {
				$this->conditionGroups = array ();
				foreach ($serializedGroups as $serializedGroup) {
					$group = TaxConditionGroup::getInstance ();
					$group->unserialize ($serializedGroup);
					$this->conditionGroups [] = $group;
				}
			}
		}

		/**
		 * @return Tax
		 */
		public static function getInstance () {
			return new self ();
		}

	}
