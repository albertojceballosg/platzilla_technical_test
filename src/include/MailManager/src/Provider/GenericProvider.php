<?php
	namespace Platzilla\MailManager\Provider;

	use Platzilla\MailManager\Type\AuthenticationMethod;
	use Platzilla\MailManager\Type\AuthenticationMethodException;
	use Platzilla\MailManager\Type\SecurityType;
	use Platzilla\MailManager\Type\SecurityTypeException;
	use Platzilla\MailManager\Type\ServiceType;
	use Platzilla\MailManager\Type\ServiceTypeException;
	use Platzilla\MailManager\Type\UserNameType;
	use Platzilla\MailManager\Type\UserNameTypeException;

	class GenericProvider implements \JsonSerializable {
		/** @var string */
		private $incomingAuthenticationMethod;

		/** @var string */
		private $incomingHostName;

		/** @var string */
		private $incomingPort;

		/** @var string */
		private $incomingSecurityType;

		/** @var string */
		private $incomingService;

		/** @var string */
		private $incomingUserNameType;

		/** @var string */
		private $outgoingAuthenticationMethod;

		/** @var string */
		private $outgoingHostName;

		/** @var string */
		private $outgoingPort;

		/** @var string */
		private $outgoingSecurityType;

		/** @var string */
		private $outgoingService;

		/** @var string */
		private $outgoingUserNameType;

		/**
		 * MailManagerConfiguration constructor.
		 *
		 * @param string[] $arguments
		 * @param boolean $validateIncomingSettings
		 * @param boolean $validateOutgoingSettings
		 */
		public function __construct ($arguments, $validateIncomingSettings = true, $validateOutgoingSettings = true) {
			$this->validate ($arguments, $validateIncomingSettings, $validateOutgoingSettings);
			$this->incomingAuthenticationMethod = strtolower ($arguments ['incomingauthenticationmethod']);
			$this->incomingHostName             = $arguments ['incominghostname'];
			$this->incomingPort                 = intval ($arguments ['incomingport']);
			$this->incomingSecurityType         = strtolower ($arguments ['incomingsecuritytype']);
			$this->incomingService              = strtolower ($arguments ['incomingservice']);
			$this->incomingUserNameType         = isset ($arguments ['incomingusernametype']) ? strtolower ($arguments ['incomingusernametype']) : UserNameType::EMAIL_ADDRESS;
			$this->outgoingAuthenticationMethod = strtolower ($arguments ['outgoingauthenticationmethod']);
			$this->outgoingHostName             = $arguments ['outgoinghostname'];
			$this->outgoingPort                 = intval ($arguments ['outgoingport']);
			$this->outgoingSecurityType         = strtolower ($arguments ['outgoingsecuritytype']);
			$this->outgoingService              = strtolower ($arguments ['outgoingservice']);
			$this->outgoingUserNameType         = isset ($arguments ['outgoingusernametype']) ? strtolower ($arguments ['outgoingusernametype']) : UserNameType::EMAIL_ADDRESS;
		}

		/**
		 * @param string $authenticationMethod
		 *
		 * @throws AuthenticationMethodException
		 */
		private function validateAuthenticationMethod ($authenticationMethod) {
			if ((empty ($authenticationMethod)) || (!in_array (strtolower ($authenticationMethod), AuthenticationMethod::getAll ()))) {
				throw new AuthenticationMethodException (AuthenticationMethodException::INVALID);
			}
		}

		/**
		 * @param string $hostName
		 *
		 * @throws GenericProviderException
		 */
		private function validateHostName ($hostName) {
			if (empty ($hostName)) {
				throw new GenericProviderException (GenericProviderException::EMPTY_HOST_NAME);
			}
		}

		/**
		 * @param integer $port
		 *
		 * @throws GenericProviderException
		 */
		private function validatePort ($port) {
			if ((empty ($port)) || (!is_numeric ($port)) || ($port < 1) || ($port > 65535)) {
				throw new GenericProviderException (GenericProviderException::INVALID_PORT_NUMBER);
			}
		}

		/**
		 * @param string $securityType
		 *
		 * @throws SecurityTypeException
		 */
		private function validateSecurityType ($securityType) {
			if ((empty ($securityType)) || (!in_array (strtolower ($securityType), SecurityType::getAll ()))) {
				throw new SecurityTypeException (SecurityTypeException::INVALID);
			}
		}

		/**
		 * @param string $service
		 *
		 * @throws ServiceTypeException
		 */
		private function validateService ($service) {
			if ((empty ($service)) || (!in_array (strtolower ($service), ServiceType::getAll ()))) {
				throw new ServiceTypeException (ServiceTypeException::INVALID);
			}
		}

		/**
		 * @param string $userNameType
		 *
		 * @throws UserNameTypeException
		 */
		private function validateUserNameType ($userNameType) {
			if ((!empty ($userNameType)) && (!in_array (strtolower ($userNameType), UserNameType::getAll ()))) {
				throw new UserNameTypeException (UserNameTypeException::INVALID);
			}
		}

		/**
		 * @param string[] $arguments
		 * @param boolean $validateIncomingSettings
		 * @param boolean $validateOutgoingSettings
		 *
		 * @throws AuthenticationMethodException
		 * @throws GenericProviderException
		 * @throws SecurityTypeException
		 * @throws ServiceTypeException
		 * @throws UserNameTypeException
		 */
		private function validate ($arguments, $validateIncomingSettings, $validateOutgoingSettings) {
			if ($validateIncomingSettings) {
				$this->validateHostName ($arguments ['incominghostname']);
				$this->validatePort ($arguments ['incomingport']);
				$this->validateSecurityType ($arguments ['incomingsecuritytype']);
				$this->validateAuthenticationMethod ($arguments ['incomingauthenticationmethod']);
				$this->validateService ($arguments ['incomingservice']);
				$this->validateUserNameType ($arguments ['incomingusernametype']);
			}
			if ($validateOutgoingSettings) {
				$this->validateHostName ($arguments ['outgoinghostname']);
				$this->validatePort ($arguments ['outgoingport']);
				$this->validateSecurityType ($arguments ['outgoingsecuritytype']);
				$this->validateAuthenticationMethod ($arguments ['outgoingauthenticationmethod']);
				$this->validateService ($arguments ['outgoingservice']);
				$this->validateUserNameType ($arguments ['outgoingusernametype']);
			}
		}

		/**
		 * @return string
		 */
		public function getIncomingAuthenticationMethod () {
			return $this->incomingAuthenticationMethod;
		}

		/**
		 * @return string
		 */
		public function getIncomingHostName () {
			return $this->incomingHostName;
		}

		/**
		 * @return string
		 */
		public function getIncomingPort () {
			return $this->incomingPort;
		}

		/**
		 * @return string
		 */
		public function getIncomingSecurityType () {
			return $this->incomingSecurityType;
		}

		/**
		 * @return string
		 */
		public function getIncomingService () {
			return $this->incomingService;
		}

		/**
		 * @return string
		 */
		public function getIncomingUserNameType () {
			return $this->incomingUserNameType;
		}

		/**
		 * @return string
		 */
		public function getOutgoingAuthenticationMethod () {
			return $this->outgoingAuthenticationMethod;
		}

		/**
		 * @return string
		 */
		public function getOutgoingHostName () {
			return $this->outgoingHostName;
		}

		/**
		 * @return string
		 */
		public function getOutgoingPort () {
			return $this->outgoingPort;
		}

		/**
		 * @return string
		 */
		public function getOutgoingSecurityType () {
			return $this->outgoingSecurityType;
		}

		/**
		 * @return string
		 */
		public function getOutgoingService () {
			return $this->outgoingService;
		}

		/**
		 * @return string
		 */
		public function getOutgoingUserNameType () {
			return $this->outgoingUserNameType;
		}

		/**
		 * Specify data which should be serialized to JSON
		 *
		 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
		 * @return mixed data which can be serialized by <b>json_encode</b>, which is a value of any type other than a resource.
		 * @since 5.4.0
		 */
		public function jsonSerialize () {
			return array (
				'incomingauthenticationmethod' => $this->incomingAuthenticationMethod,
				'incominghostname'             => $this->incomingHostName,
				'incomingport'                 => $this->incomingPort,
				'incomingsecuritytype'         => $this->incomingSecurityType,
				'incomingservice'              => $this->incomingService,
				'incomingusernametype'         => $this->incomingUserNameType,
				'outgoingauthenticationmethod' => $this->outgoingAuthenticationMethod,
				'outgoinghostname'             => $this->outgoingHostName,
				'outgoingport'                 => $this->outgoingPort,
				'outgoingsecuritytype'         => $this->outgoingSecurityType,
				'outgoingservice'              => $this->outgoingService,
				'outgoingusernametype'         => $this->outgoingUserNameType,
			);
		}

	}
