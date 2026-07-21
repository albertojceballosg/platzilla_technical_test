<?php
	require_once ('modules/materials/Exceptions/FolderException.php');
	require_once ('modules/materials/Objects/Folder.php');
	require_once ('modules/materials/Objects/FolderInterface.php');

	class Category implements FolderInterface {

		/** @var datetime */
		private $createDate;

		/** @var string */
		private $description;

		/** @var Folder[] */
		private $folders;

		/** @var integer */
		private $id;

		/** @var string */
		private $name;

		/** @var string */
		private $status;

		/**
		 * @return datetime
		 */
		public function getCreateDate () {
			return $this->createDate;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return Folder[]
		 */
		public function getFolders () {
			return $this->folders;
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
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @param string $createDate
		 *
		 * @return Category
		 */
		public function setCreateDate ($createDate) {
			if (!empty ($createDate)) {
				$this->createDate = DateTime::createFromFormat ('Y-m-d H:i:s', $createDate);
			} else {
				$this->createDate = null;
			}
			return $this;
		}

		/**
		 * @param $description
		 *
		 * @return Category
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param array $folders
		 *
		 * @return Category
		 */
		public function setFolders ($folders) {
			if (!empty ($folders) && is_array ($folders)) {
				foreach ($folders as $folder) {
					if (!$folder instanceof Folder) {
						continue;
					}
					$this->folders [] = $folder;
				}
			} else {
				$this->folders = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return Category
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return Category
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param $status
		 *
		 * @return Category
		 */
		public function setStatus ($status) {
			if (in_array($status, self::FILE_CATEGORY_STATUS)) {
				$this->status = $status;
			} else {
				$this->status = self::FILE_CATEGORY_STATUS[ 0 ];
			}
			return $this;
		}

		/**
		 * @return Category
		 */
		public static function getInstance () {
			return new self ();
		}

	}
