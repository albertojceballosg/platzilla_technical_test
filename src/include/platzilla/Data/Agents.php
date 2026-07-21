<?php
	require_once ('include/platzilla/Data/AgentsInterface.php');
	require_once ('include/platzilla/Data/AgentsException.php');
	require_once ('include/platzilla/Objects/PlatformInstance.php');
	class Agents implements AgentsInterface {
		
		/** @var string */
		private $description;
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $name;
		
		/** @var PlatformInstance[] */
		private $platformInstance;
		
		/** @var string */
		private $status;
		
		/** @var string */
		private $userAvatar;
		
		/** @var string */
		private $userName;
		
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
		public function getName () 	{
			return $this->name;
		}
		
		/**
		 * @return PlatformInstance[]
		 */
		public function getPlatformInstance () 	{
			return $this->platformInstance;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @return string
		 */
		public function getUserAvatar () {
			return $this->userAvatar;
		}
		
		/**
		 * @return string
		 */
		public function getUserName () {
			return $this->userName;
		}
		
		/**
		 * @param string $description
		 *
		 * @return Agents
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return Agents
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $name
		 *
		 * @return Agents
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}
		
		/**
		 * @param PlatformInstance[] $platformInstance
		 *
		 * @return Agents
		 */
		public function setPlatformInstance ($platformInstance) {
			$this->platformInstance = $platformInstance;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return Agents
		 */
		public function setStatus ($status) {
			if (!in_array ($status, array_keys (self::AGENT_STATUS))) {
				throw new AgentsException (AgentsException::ERROR_INVALID_STATUS);
			}
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param string $userAvatar
		 *
		 * @return Agents
		 */
		public function setUserAvatar ($userAvatar) {
			$this->userAvatar = $userAvatar;
			return $this;
		}
		
		/**
		 * @param string $userName
		 *
		 * @return Agents
		 */
		public function setUserName ($userName) {
			$this->userName = $userName;
			return $this;
		}
		
		/**
		 * @return void
		 * @throws AgentsException
		 */
		public function validateAgent () {
			if (empty($this->name)) {
				throw new AgentsException(AgentsException::ERROR_ENTITY_USER_NAME);
			} else if (empty ($this->id)) {
				throw new AgentsException(AgentsException::ERROR_ENTITY_USER_ID);
			} else if (!count ($this->platformInstance)) {
				throw new AgentsException(AgentsException::ERROR_ENTITY_INSTANCES);
			}
		}
		
		/**
		 * @return Agents
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
