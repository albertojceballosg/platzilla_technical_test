<?php
	require_once ('modules/materials/Exceptions/FolderException.php');
	require_once ('modules/materials/Objects/FolderInterface.php');

	class Document implements FolderInterface {

		/** @var datetime */
		private $createDate;

		/** @var string */
		private $createTime;

		/** @var integer */
		private $createRealTime;

		/** @var string */
		private $description;

		/** @var string */
		private $featured;

		/** @var integer */
		private $folderId;

		/** @var string */
		private $folderName;

		/** @var integer */
		private $id;

		/** @var integer */
		private $locked;

		/** @var string */
		private $photo;

		/** @var string */
		private $photoType;

		/** @var string */
		private $publicName;

		/** @var string */
		private $name;

		/** @var array */
		private $relatedFiles;

		/** @var string */
		private $type;

		/** @var string */
		private $url;

		/** @var string */
		private $urlBlog;

		/** @var string */
		private $urlPublic;
		
		/** @var integer */
		private $viewed;

		/**
		 * @param $string
		 *
		 * @return string
		 */
		private function sanearString ($string) {
			$string = trim ($string);
			$string = str_replace (
				array ( '·', '$', '%', '&', '/', '?', "'", '¡', '¿', '[', '^', '_', ']', '+', '}', '{', '-', '´', '>', '< ', ';', ',', ':', '.'),
				' ',
				$string
			);
			return ucwords (strtolower ($string));
		}

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
		 * @return integer
		 */
		public function getRealCreateTime () {
			return $this->createRealTime;
		}
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return string
		 */
		public function getFeatured () {
			return $this->featured;
		}

		/**
		 * @return integer
		 */
		public function getFolderId () {
			return $this->folderId;
		}

		/**
		 * @return string
		 */
		public function getFolderName () {
			return $this->folderName;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return mixed
		 */
		public function getLocked () {
			return $this->locked;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return array
		 */
		public function getRelatedFiles () {
			return $this->relatedFiles;
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
		public function getPhotoType () {
			return $this->photoType;
		}

		/**
		 * @return string
		 */
		public function getPublicName () {
			return $this->publicName;
		}

		/**
		 * @return string
		 */
		public function getType () {
			return $this->type;
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
		public function getUrlBlog () {
			return $this->urlBlog;
		}
		
		/**
		 * @return string
		 */
		public function getUrlPublic () {
			return $this->urlPublic;
		}
		
		/**
		 * @return integer
		 */
		public function getViewed () {
			return $this->viewed;
		}

		/**
		 * @param string $createDate
		 *
		 * @return Document
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
		 * @param integer $createTime
		 *
		 * @return Document
		 */
		public function setCreateTime ($createTime) {
			$this->createTime     = $this->timeSince ($createTime);
			$this->createRealTime = $createTime;
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return Document
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * @param string $featured
		 *
		 * @return Document
		 */
		public function setFeatured ($featured) {
			if (in_array($featured, self::FILE_FEATURED_STATUS)) {
				$this->featured = $featured;
			} else {
				$this->featured = self::FILE_FEATURED_STATUS[ 1 ];
			}
			return $this;
		}

		/**
		 * @param $folderId
		 *
		 * @return Document
		 */
		public function setFolderId ($folderId) {
			$this->folderId = $folderId;
			return $this;
		}

		/**
		 * @param $folderName
		 *
		 * @return Document
		 */
		public function setFolderName ($folderName) {
			$this->folderName = $folderName;
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return Document
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param integer $locked
		 *
		 * @return Document
		 */
		public function setLocked ($locked) {
			$this->locked = $locked;
			return $this;
		}

		/**
		 * @param $photo
		 *
		 * @return Document
		 */
		public function setPhoto ($photo) {
			$this->photo = $photo;
			return $this;
		}

		/**
		 * @param $photoType
		 *
		 * @return Document
		 */
		public function setPhotoType ($photoType) {
			$this->photoType = $photoType;
			return $this;
		}

		/**
		 * @param $publicName
		 *
		 * @return Document
		 */
		public function setPublicName ($publicName) {
			$this->publicName = $publicName;
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return Document
		 */
		public function setName ($name) {
			if ($this->type == 'UPLOADED') {
				$dummy = explode ('.', $name);
				if (count($dummy) > 1) {
					$type     = array_pop ($dummy);
					$type     = " ({$type})";
					$fileName = implode ('_', $dummy);
				} else {
					$type = '';
					$fileName = $name;
				}
				$this->name = $this->sanearString ($fileName). $type;
			} else {
				$this->name = $name;
			}
			return $this;
		}

		/**
		 * @param $relatedFiles
		 *
		 * @return Document
		 */
		public function setRelatedFiles ($relatedFiles) {
			$this->relatedFiles = $relatedFiles;
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return Document
		 */
		public function setType ($type) {
			if (in_array ($type, self::FILE_AVAILABLE_TYPES)) {
				$this->type = $type;
			} else {
				$this->type = null;
			}
			return $this;
		}

		/**
		 * @param string $url
		 *
		 * @return Document
		 */
		public function setUrl ($url) {
			$this->url = $url;
			return $this;
		}

		/**
		 * @param integer $fieldId
		 *
		 * @return Document
		 */
		public function setUrlBlog ($fieldId) {
			if (!empty($fieldId)) {
				$this->urlBlog = urlencode (base64_encode ('ebook;' . $fieldId));
			} else {
				$this->urlBlog = null;
			}
			return $this;
		}
		
		/**
		 * @param string $urlPublic
		 *
		 * @return Document
		 */
		public function setUrlPublic ($urlPublic) {
			$this->urlPublic = $urlPublic;
			return $this;
		}
		
		/**
		 * @param integer $viewed
		 *
		 * @return Document
		 */
		public function setViewed ($viewed) {
			if (!empty ($viewed)) {
				$this->viewed = $viewed;
			} else {
				$this->viewed = 0;
			}
			return $this;
		}

		/**
		 * @return Document
		 */
		public static function getInstance () {
			return new self ();
		}

	}
