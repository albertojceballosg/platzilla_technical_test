<?php
	require_once ('include/platzilla/Exceptions/AppFieldException.php');
	require_once ('include/platzilla/Objects/AppFieldInterface.php');
	
	class AppField implements AppFieldInterface {
		
		/** @var string */
		private $applicationName;
		
		/** @var string */
		private $handlerClass;
		
		/** @var string */
		private $handlerMethod;
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $status;
		
		/** @var string */
		private $tabName;
		
		/**
		 * @return string
		 */
		public function getApplicationName () {
			return $this->applicationName;
		}
		
		/**
		 * @return string
		 */
		public function getHandlerClass () {
			return $this->handlerClass;
		}
		
		/**
		 * @return string
		 */
		public function getHandlerMethod () {
			return $this->handlerMethod;
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
		public function getTabName () {
			return $this->tabName;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @param $applicationName
		 *
		 * @return AppField
		 */
		public function setApplicationName ($applicationName) {
			$this->applicationName = $applicationName;
			return $this;
		}
		
		/**
		 * @param $handlerClass
		 *
		 * @return AppField
		 */
		public function setHandlerClass ($handlerClass) {
			$this->handlerClass = $handlerClass;
			return $this;
		}
		
		/**
		 * @param $handlerMethod
		 *
		 * @return AppField
		 */
		public function setHandlerMethod ($handlerMethod) {
			$this->handlerMethod = $handlerMethod;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return AppField
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $tabName
		 *
		 * @return AppField
		 */
		public function setTabName ($tabName) {
			$this->tabName = $tabName;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return AppField
		 */
		public function setStatus ($status) {
			if (empty($status) || !in_array ($status, AppFieldInterface::APP_FIELD_STATUS)) {
				$this->status = 'ENABLED';
			} else {
				$this->status = $status;
			}
			return $this;
		}
		
		/**
		 * @throws AppFieldException
		 */
		public function validate () {
			if (empty ($this->applicationName)) {
				throw new AppFieldException (AppFieldException::ERROR_APPLICATION_FIELD_NAME);
			} else if (empty ($this->handlerClass)) {
				throw new AppFieldException (AppFieldException::ERROR_APPLICATION_FIELD_HANDLE_CLASS);
			} else if (empty ($this->handlerMethod)) {
				throw new AppFieldException (AppFieldException::ERROR_APPLICATION_FIELD_HANDLE_METHOD);
			} else if (empty ($this->tabName)) {
				throw new AppFieldException (AppFieldException::ERROR_APPLICATION_FIELD_MODULE);
			}
		}
		
		/**
		 * @return AppField
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
