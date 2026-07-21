<?php
	require_once ('modules/materials/Exceptions/FolderException.php');
	require_once ('modules/materials/Objects/Document.php');
	require_once ('modules/materials/Objects/FolderInterface.php');

	class Folder implements FolderInterface {

		/** @var integer */
		private $category;

		/** @var datetime */
		private $createDate;

		/** @var string */
		private $createTime;

		/** @var string */
		private $description;

		/** @var Document[] */
		private $files;

		/** @var string */
		private $folderName;

		/** @var integer */
		private $id;

		/** @var string */
		private $name;

		/** @var string */
		private $photo;

		/** @var string */
		private $status;

		/** @var string */
		private $url;

		/** @var string */
		private $video;

		/**
		 * @param integer $original
		 *
		 * @return null|string
		 */
		private function timeSince ($original) {
			$chunks = array (
				array (60 * 60 * 24 * 365, 'año'),
				array (60 * 60 * 24 * 30, 'mes'),
				array (60 * 60 * 24 * 7, 'sem'),
				array (60 * 60 * 24, 'día'),
				array (60 * 60, 'h'),
				array (60, 'min'),
			);
			$today  = time ();
			$since  = ($today - intval ($original));
			if ($since < 0) {
				return null;
			}
			$j = count ($chunks);
			for ($i = 0; $i < $j; $i++) {
				$seconds = $chunks[ $i ][0];
				$name    = $chunks[ $i ][1];
				$count   = floor ($since / $seconds);
				if ($count != 0) {
					$print = ($count == 1) ? '1 ' . $name : (($name == 'mes') ? "$count {$name}es" : "$count {$name}s");
					if (($i + 1) < $j) {
						$secondsTwo = $chunks[ ($i + 1) ][0];
						$nameTwo    = $chunks[ ($i + 1) ][1];
						$countTwo   = floor (($since - ($seconds * $count)) / $secondsTwo);
						if ($countTwo != 0) {
							$print .= ($countTwo == 1) ? ', 1 ' . $nameTwo : ", $countTwo {$nameTwo}s";
						}
					}
					break;
				}
			}
			return isset ($print) ? $print : null;
		}

		/**
		 * @return integer
		 */
		public function getCategory () {
			return $this->category;
		}

		/**
		 * @return datetime
		 */
		public function getCreateDate () {
			return $this->createDate;
		}

		/**
		 * @return string
		 */
		public function getCreateTime () {
			return $this->createTime;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return Document[]|null
		 */
		public function getFiles () {
			return $this->files;
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
		public function getPhoto () {
			return $this->photo;
		}

		/**
		 * @return string
		 */
		public function getFolderName () {
			return $this->folderName;
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
		public function getUrl () {
			return $this->url;
		}

		/**
		 * @return string
		 */
		public function getVideo () {
			return $this->video;
		}

		/**
		 * @param $category
		 *
		 * @return Folder
		 */
		public function setCategory ($category) {
			$this->category = $category;
			return $this;
		}

		/**
		 * @param string $createDate
		 *
		 * @return Folder
		 */
		public function setCreateDate ($createDate) {
			if (!empty($createDate)) {
				$this->createDate = DateTime::createFromFormat ('Y-m-d H:i:s', $createDate);
			} else {
				$this->createDate = null;
			}
			return $this;
		}

		/**
		 * @param integer $createTime
		 *
		 * @return Folder
		 */
		public function setCreateTime ($createTime) {
			$this->createTime = $this->timeSince ($createTime);
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return Folder
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param array $files
		 *
		 * @return Folder
		 */
		public function setFiles ($files) {
			if (count ($files)) {
				foreach ($files as $file) {
					if (!$file instanceof Document) {
						continue;
					}
					$this->files [] = $file;
				}
			} else {
				$this->files = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return Folder
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return Folder
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param $photo
		 *
		 * @return Folder
		 */
		public function setPhoto ($photo) {
			$this->photo = $photo;
			return $this;
		}

		/**
		 * @param $folderName
		 *
		 * @return Folder
		 */
		public function setFolderName ($folderName) {
			$this->folderName = $folderName;
			return $this;
		}

		/**
		 * @param $status
		 *
		 * @return Folder
		 */
		public function setStatus ($status) {
			if (in_array($status, FolderInterface::FOLDER_AVAILABLE_STATUS)) {
				$this->status = $status;
			} else {
				$this->status = FolderInterface::FOLDER_AVAILABLE_STATUS [0];
			}
			return $this;
		}

		/**
		 * @param string $url
		 *
		 * @return Folder
		 */
		public function setUrl ($url) {
			$this->url = $url;
			return $this;
		}

		/**
		 * @param string $video
		 *
		 * @return Folder
		 */
		public function setVideo ($video) {
			$this->video = $video;
			return $this;
		}

		/**
		 * @return Folder
		 */
		public static function getInstance () {
			return new self ();
		}

	}
