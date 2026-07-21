<?php
	require_once ('include/platzilla/Exceptions/PricebookException.php');
	require_once ('include/platzilla/Objects/PricebookConditionGroup.php');

	class Pricebook implements Serializable {
		/** @var PricebookConditionGroup[] */
		private $conditionGroups;

		/** @var boolean */
		private $default;

		/** @var string */
		private $description;

		/** @var integer */
		private $id;

		/** @var float */
		private $multiplier;

		/** @var string */
		private $name;

		/**
		 * @throws PricebookConditionGroupException
		 * @throws PricebookException
		 */
		private function validateConditionGroups () {
			if (empty ($this->conditionGroups)) {
				return;
			}

			foreach ($this->conditionGroups as $group) {
				if (empty ($group)) {
					throw new PricebookException (PricebookException::ERROR_PRICEBOOK_EMPTY_CONDITION_GROUP);
				} else if (!($group instanceof PricebookConditionGroup)) {
					throw new PricebookException (PricebookException::ERROR_PRICEBOOK_INVALID_CONDITION_GROUP);
				} else {
					$group->validate ();
				}
			}
		}

		public function __construct () {
			$this->default = false;
		}

		/**
		 * @return PricebookConditionGroup[]
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
		 * @return float
		 */
		public function getMultiplier () {
			return $this->multiplier;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return boolean
		 */
		public function isDefault () {
			return $this->default;
		}

		/**
		 * @param PricebookConditionGroup[] $conditionGroups
		 *
		 * @return Pricebook
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
		 * @return Pricebook
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
		 * @return Pricebook
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
		 * @return Pricebook
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = intval ($id);
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param float $multiplier
		 *
		 * @return Pricebook
		 */
		public function setMultiplier ($multiplier) {
			if ((is_numeric ($multiplier)) && ($multiplier >= 0)) {
				$this->multiplier = floatval ($multiplier);
			}
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return Pricebook
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
		 * @throws PricebookException
		 */
		public function validate () {
			if ($this->default === null) {
				throw new PricebookException (PricebookException::ERROR_PRICEBOOK_EMPTY_DEFAULT);
			} else if ($this->multiplier === null) {
				throw new PricebookException (PricebookException::ERROR_PRICEBOOK_EMPTY_MULTIPLIER);
			} else if (empty ($this->name)) {
				throw new PricebookException (PricebookException::ERROR_PRICEBOOK_EMPTY_NAME);
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
					$this->description,
					$this->default,
					$this->multiplier,
					$this->name,
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
				$this->description,
				$this->default,
				$this->multiplier,
				$this->name,
				$serializedGroups,
				) = unserialize ($serialized);

			if (!empty ($serializedGroups)) {
				$this->conditionGroups = array ();
				foreach ($serializedGroups as $serializedGroup) {
					$group = PricebookConditionGroup::getInstance ();
					$group->unserialize ($serializedGroup);
					$this->conditionGroups [] = $group;
				}
			}
		}

		/**
		 * @return Pricebook
		 */
		public static function getInstance () {
			return new self ();
		}

	}
