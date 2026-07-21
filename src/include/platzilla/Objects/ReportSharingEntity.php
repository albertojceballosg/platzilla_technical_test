<?php
	require_once ('include/platzilla/Exceptions/ReportSharingEntityException.php');
	require_once ('include/platzilla/Objects/ReportSharingEntityInterface.php');

	class ReportSharingEntity implements ReportSharingEntityInterface {
		/** @var integer */
		private $id;

		/** @var string */
		private $type;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * @param integer $id
		 *
		 * @return ReportSharingEntity
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return ReportSharingEntity
		 */
		public function setType ($type) {
			if (in_array ($type, array (self::TYPE_GROUP, self::TYPE_USER))) {
				$this->type = $type;
			}
			return $this;
		}

		/**
		 * @param ReportSharingEntity $entity
		 */
		public function copyValuesFrom ($entity) {
			if ((empty ($entity)) || (!($entity instanceof ReportSharingEntity))) {
				return;
			}

			$this->id   = $entity->getId ();
			$this->type = $entity->getType ();
		}

		/**
		 * @param integer $newId
		 *
		 * @return ReportSharingEntity
		 * @throws ReportException
		 */
		public function duplicate ($newId = null) {
			$this->validate ();

			$object = new self ();
			return $object->setId ($newId)
				->setType ($this->type);
		}

		/**
		 * @param ReportSharingEntity $entity
		 *
		 * @return boolean
		 */
		public function isEqualTo ($entity) {
			if (
				(empty ($entity)) ||
				(!($entity instanceof ReportSharingEntity)) ||
				($this->id != $entity->getId ()) ||
				($this->type != $entity->getType ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ReportSharingEntityException
		 */
		public function validate () {
			if (empty ($this->id)) {
				throw new ReportSharingEntityException (ReportSharingEntityException::ERROR_REPORT_SHARING_ENTITY_EMPTY_ID);
			} else if (empty ($this->type)) {
				throw new ReportSharingEntityException (ReportSharingEntityException::ERROR_REPORT_SHARING_ENTITY_EMPTY_TYPE);
			}
		}

		/**
		 * @return ReportSharingEntity
		 */
		public static function getInstance () {
			return new self ();
		}

	}
