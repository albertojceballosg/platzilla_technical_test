<?php
	require_once ('include/platzilla/Data/ProfilesHowToUseException.php');
	require_once ('include/platzilla/Data/ProfilesHowToUseInterface.php');
	class CompanySector implements ProfilesHowToUseInterface {

		/** @var string */
		private $description;

		/** @var integer */
		private $id;

		/** @var string */
		private $name;

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
		 * @param $description
		 *
		 * @return CompanySector
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param $id
		 *
		 * @return CompanySector
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param $name
		 *
		 * @return CompanySector
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @throws ProfilesHowToUseException
		 */
		public function validate () {
			if (empty($this->name)) {
				throw new ProfilesHowToUseException (ProfilesHowToUseException::ERROR_COMPANY_SECTOR_EMPTY_NAME);
			}
		}

		/**
		 * @return CompanySector
		 */
		public static function getInstance () {
			return new self ();
		}

	}
