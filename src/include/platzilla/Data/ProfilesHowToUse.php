<?php
	require_once ('include/platzilla/Data/CompanyPhase.php');
	require_once ('include/platzilla/Data/CompanySector.php');
	require_once ('include/platzilla/Data/CompanyType.php');
	require_once ('include/platzilla/Objects/HowToUse.php');
	class ProfilesHowToUse implements ProfilesHowToUseInterface {

		/** @var string */
		private $code;

		/** @var array */
		private $companyPhase;

		/** @var array */
		private $companySector;

		/** @var array */
		private $companyType;

		/** @var string */
		private $description;

		/** @var HowToUse[] */
		private $howToUse;

		/** @var integer */
		private $id;

		/** @var string */
		private $name;

		/** @var string */
		private $status;

		/**
		 * @return string
		 */
		public function getCode () {
			return $this->code;
		}

		/**
		 * @return array|null
		 */
		public function getCompanyPhase () {
			return $this->companyPhase;
		}

		/**
		 * @return array|null
		 */
		public function getCompanySector () {
			return $this->companySector;
		}

		/**
		 * @return array|null
		 */
		public function getCompanyType () {
			return $this->companyType;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return HowToUse[]
		 */
		public function getHowToUse () {
			return $this->howToUse;
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
		 * @return string
		 */
		public function getStatus() {
			return $this->status;
		}

		/**
		 * @param string $code
		 *
		 * @return ProfilesHowToUse
		 */
		public function setCode ($code) {
			$this->code = $code;
			return $this;
		}

		/**
		 * @param array $companyPhase
		 *
		 * @return ProfilesHowToUse
		 */
		public function setCompanyPhase ($companyPhase) {
			$this->companyPhase = $companyPhase;
			return $this;
		}

		/**
		 * @param array $companySector
		 *
		 * @return ProfilesHowToUse
		 */
		public function setCompanySector ($companySector) {
			$this->companySector = $companySector;
			return $this;
		}

		/**
		 * @param array $companyType
		 *
		 * @return ProfilesHowToUse
		 */
		public function setCompanyType ($companyType) {
			$this->companyType = $companyType;
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return ProfilesHowToUse
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param HowToUse[] $howToUse
		 *
		 * @return ProfilesHowToUse
		 */
		public function setHowToUse ($howToUse) {
			if (empty($howToUse) || !is_array($howToUse)) {
				$this->howToUse = null;
			} else {
				$this->howToUse = $howToUse;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return ProfilesHowToUse
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return ProfilesHowToUse
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return ProfilesHowToUse
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * @throws HowToUseException
		 * @throws ProfilesHowToUseException
		 */
		public function validate () {
			if (empty($this->name)) {
				throw new ProfilesHowToUseException(ProfilesHowToUseException::ERROR_COMPANY_PROFILE_EMPTY_NAME);
			} else if (!empty($this->companyPhase)) {
				throw new ProfilesHowToUseException(ProfilesHowToUseException::ERROR_COMPANY_PHASE_EMPTY_NAME);
			} else if (!empty($this->companySector)) {
				throw new ProfilesHowToUseException(ProfilesHowToUseException::ERROR_COMPANY_SECTOR_EMPTY_ID);
			} else if (!empty($this->companyType)) {
				throw new ProfilesHowToUseException(ProfilesHowToUseException::ERROR_COMPANY_TYPE_EMPTY_NAME);
			}
			if (!empty($this->howToUse)) {
				foreach ($this->howToUse as $howToUse) {
					$howToUse->validate();
				}
			}
		}

		/**
		 * @return ProfilesHowToUse
		 */
		public static function getInstance () {
			return new self ();
		}

	}
