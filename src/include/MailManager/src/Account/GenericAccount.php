<?php
	namespace Platzilla\MailManager\Account;

	use League\OAuth2\Client\Token\AccessToken;
	use League\OAuth2\Client\Token\AccessTokenInterface;
	use Platzilla\MailManager\Provider\GenericProvider;

	class GenericAccount implements \JsonSerializable {
		/** @var AccessTokenInterface */
		private $accessToken;

		/** @var string */
		private $emailAddress;

		/** @var string */
		private $incomingFolderName;

		/** @var string */
		private $outgoingFolderName;

		/** @var string */
		private $password;

		/** @var GenericProvider */
		private $provider;

		/**
		 * @param string $emailAddress
		 * @param GenericProvider $provider
		 */
		public function __construct ($emailAddress, $provider) {
			$this->emailAddress = $emailAddress;
			$this->provider     = $provider;
		}

		/**
		 * @return AccessTokenInterface
		 */
		public function getAccessToken () {
			return $this->accessToken;
		}

		/**
		 * @return string
		 */
		public function getEmailAddress () {
			return $this->emailAddress;
		}

		/**
		 * @return string
		 */
		public function getIncomingFolderName () {
			return $this->incomingFolderName;
		}

		/**
		 * @return string
		 */
		public function getOutgoingFolderName () {
			return $this->outgoingFolderName;
		}

		/**
		 * @return string
		 */
		public function getPassword () {
			return $this->password;
		}

		/**
		 * @return GenericProvider
		 */
		public function getProvider () {
			return $this->provider;
		}

		/**
		 * @param AccessTokenInterface $accessToken
		 *
		 * @return GenericAccount
		 */
		public function setAccessToken ($accessToken) {
			$this->accessToken = $accessToken;
			return $this;
		}

		/**
		 * @param string $incomingFolderName
		 *
		 * @return GenericAccount
		 */
		public function setIncomingFolderName ($incomingFolderName) {
			$this->incomingFolderName = $incomingFolderName;
			return $this;
		}

		/**
		 * @param string $outgoingFolderName
		 *
		 * @return GenericAccount
		 */
		public function setOutgoingFolderName ($outgoingFolderName) {
			$this->outgoingFolderName = $outgoingFolderName;
			return $this;
		}

		/**
		 * @param string $password
		 *
		 * @return GenericAccount
		 */
		public function setPassword ($password) {
			$this->password = $password;
			return $this;
		}

		/**
		 * @throws GenericAccountException
		 */
		public function validate () {
			if (empty ($this->emailAddress)) {
				throw new GenericAccountException (GenericAccountException::EMPTY_EMAIL_ADDRESS);
			} else if (filter_var ($this->emailAddress, FILTER_VALIDATE_EMAIL) === false) {
				throw new GenericAccountException (GenericAccountException::INVALID_EMAIL_ADDRESS);
			} else if (!($this->provider instanceof GenericProvider)) {
				throw new GenericAccountException (GenericAccountException::INVALID_PROVIDER);
			} else if ((!empty ($this->accessToken)) && (!($this->accessToken instanceof AccessTokenInterface))) {
				throw new GenericAccountException (GenericAccountException::INVALID_ACCESS_TOKEN);
			}
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
				'accesstoken'        => isset ($this->accessToken) ? $this->accessToken->jsonSerialize () : null,
				'emailaddress'       => $this->emailAddress,
				'incomingfoldername' => $this->incomingFolderName,
				'outgoingfoldername' => $this->outgoingFolderName,
				'password'           => $this->password,
				'provider'           => isset ($this->provider) ? $this->provider->jsonSerialize () : null,
			);
		}

		/**
		 * @param string $emailAddress
		 * @param GenericProvider $provider
		 *
		 * @return GenericAccount
		 */
		public static function getInstance ($emailAddress, $provider) {
			return new self ($emailAddress, $provider);
		}

		/**
		 * @param array $data
		 *
		 * @return GenericAccount
		 */
		public static function jsonDeserialize ($data) {
			$accessToken        = isset ($data ['accesstoken']) ? new AccessToken ($data ['accesstoken']) : null;
			$emailAddress       = isset ($data ['emailaddress']) ? $data ['emailaddress'] : null;
			$incomingFolderName = isset ($data ['incomingfoldername']) ? $data ['incomingfoldername'] : null;
			$outgoingFolderName = isset ($data ['outgoingfoldername']) ? $data ['outgoingfoldername'] : null;
			$password           = isset ($data ['password']) ? $data ['password'] : null;
			$provider           = isset ($data ['provider']) ? new GenericProvider ($data ['provider']) : null;
			return self::getInstance ($emailAddress, $provider)
				->setAccessToken ($accessToken)
				->setIncomingFolderName ($incomingFolderName)
				->setOutgoingFolderName ($outgoingFolderName)
				->setPassword ($password);
		}

	}
