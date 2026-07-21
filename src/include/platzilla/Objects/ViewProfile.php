<?php
	require_once ('include/platzilla/Exceptions/ViewProfileException.php');
	require_once ('include/platzilla/Objects/ViewProfileInterface.php');

	class ViewProfile implements ViewProfileInterface {
		/** @var integer */
		private $accessPermission;

		/** @var integer */
		private $default;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $profileName;

		/** @var string */
		private $viewName;

		public function __construct () {
			$this->accessPermission = self::PERMISSION_ALLOW;
			$this->default          = self::DEFAULT_NO;
		}

		/**
		 * @return integer
		 */
		public function getAccessPermission () {
			return $this->accessPermission;
		}

		/**
		 * @return integer
		 */
		public function getDefault () {
			return $this->default;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return string
		 */
		public function getProfileName () {
			return $this->profileName;
		}

		/**
		 * @return string
		 */
		public function getViewName () {
			return $this->viewName;
		}

		/**
		 * @param integer $permission
		 *
		 * @return ViewProfile
		 */
		public function setAccessPermission ($permission) {
			if (in_array ($permission, array (self::PERMISSION_ALLOW, self::PERMISSION_DENY))) {
				$this->accessPermission = $permission;
			}
			return $this;
		}

		/**
		 * @param integer $default
		 *
		 * @return ViewProfile
		 */
		public function setDefault ($default) {
			if (in_array ($default, array (self::DEFAULT_NO, self::DEFAULT_YES))) {
				$this->default = $default;
			}
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ViewProfile
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param string $profileName
		 *
		 * @return ViewProfile
		 */
		public function setProfileName ($profileName) {
			$this->profileName = $profileName;
			return $this;
		}

		/**
		 * @param string $viewName
		 *
		 * @return ViewProfile
		 */
		public function setViewName ($viewName) {
			$this->viewName = $viewName;
			return $this;
		}

		/**
		 * @param ViewProfile $profile
		 */
		public function copyValuesFrom ($profile) {
			if ((empty ($profile)) || (!($profile instanceof ViewProfile))) {
				return;
			}

			$this->accessPermission = $profile->getAccessPermission ();
			$this->default          = $profile->getDefault ();
			$this->moduleName       = $profile->getModuleName ();
			$this->profileName      = $profile->getProfileName ();
			$this->viewName         = $profile->getViewName ();
		}

		/**
		 * @param string $newProfileName
		 *
		 * @return ViewProfile
		 * @throws ViewProfileException
		 */
		public function duplicate ($newProfileName) {
			$this->validate ();

			$object = new self ();
			return $object->setAccessPermission ($this->accessPermission)
				->setDefault ($this->default)
				->setModuleName ($this->moduleName)
				->setProfileName ($newProfileName)
				->setViewName ($this->viewName);
		}

		/**
		 * @param ViewProfile $profile
		 *
		 * @return boolean
		 */
		public function isEqualTo ($profile) {
			if (
				(empty ($profile)) ||
				(!($profile instanceof ViewProfile)) ||
				($this->accessPermission != $profile->getAccessPermission ()) ||
				($this->default != $profile->getDefault ()) ||
				($this->moduleName != $profile->getModuleName ()) ||
				($this->profileName != $profile->getProfileName ()) ||
				($this->viewName != $profile->getViewName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ViewProfileException
		 */
		public function validate () {
			if (empty ($this->moduleName)) {
				throw new ViewProfileException (ViewProfileException::ERROR_VIEW_PROFILE_EMPTY_MODULE_NAME);
			} else if (empty ($this->profileName)) {
				throw new ViewProfileException (ViewProfileException::ERROR_VIEW_PROFILE_EMPTY_PROFILE_NAME);
			} else if (empty ($this->viewName)) {
				throw new ViewProfileException (ViewProfileException::ERROR_VIEW_PROFILE_EMPTY_VIEW_NAME);
			}
		}

		/**
		 * @return ViewProfile
		 */
		public static function getInstance () {
			return new self ();
		}

	}
