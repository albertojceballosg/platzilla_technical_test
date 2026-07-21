<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CourseResourceException.php');

	class CourseResource implements Serializable {
		const TYPE_ATTACHMENT = 'ATTACHMENT';
		const TYPE_URL        = 'URL';

		private static $FOLDER_PATH = 'storage/Courses/resources';

		/** @var integer */
		private $id;
		
		/** @var integer */
		private $exerciseId;

		/** @var string */
		private $fileContents;
		
		/** @var string */
		private $fileName;
		
		/** @var string */
		private $fileType;
		
		/** @var string */
		private $hasExercise;

		/** @var integer */
		private $lessonId;

		/** @var string */
		private $name;

		/** @var string */
		private $type;

		/** @var string */
		private $url;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return integer
		 */
		public function getExerciseId () {
			return $this->exerciseId;
		}

		/**
		 * @return string
		 */
		public function getFileContents () {
			return $this->fileContents;
		}
		
		/**
		 * @return string
		 */
		public function getFileName () {
			return $this->fileName;
		}
		
		/**
		 * @return string
		 */
		public function getFileType () {
		
		}
		
		/**
		 * @return string
		 */
		public function getHasExercise () {
			return $this->hasExercise;
		}
		
		/**
		 * @return integer
		 */
		public function getLessonId () {
			return $this->lessonId;
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
		 * @param integer $id
		 *
		 * @return CourseResource
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = intval ($id);
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param integer $exerciseId
		 *
		 * @return CourseResource
		 */
		public function setExerciseId ($exerciseId) {
			$this->exerciseId = $exerciseId;
			return $this;
		}
		
		
		/**
		 * @param string $encodedFileContents Base 64 encoded file contents
		 *
		 * @return CourseResource
		 */
		public function setFileContents ($encodedFileContents) {
			if (is_scalar ($encodedFileContents)) {
				$this->fileContents = base64_decode (str_replace (' ', '+', substr ($encodedFileContents, (strpos ($encodedFileContents, 'base64,') + 7))));
			} else {
				$this->fileContents = null;
			}
			return $this;
		}

		/**
		 * @param string $fileName
		 *
		 * @return CourseResource
		 */
		public function setFileName ($fileName) {
			if (!empty ($fileName)) {
				$this->fileName = $fileName;
				$dummy          = explode ('.', $fileName);
				$this->fileType = end ($dummy);
			} else {
				$this->fileName = null;
				$this->fileType = null;
			}
			return $this;
		}
		
		/**
		 * @param string $hasExercise
		 *
		 * @return CourseResource
		 */
		public function setHasExercise ($hasExercise) {
			if (!empty ($hasExercise)) {
				$this->hasExercise = $hasExercise;
			} else {
				$this->hasExercise = 'NO';
			}
			return $this;
		}
		
		/**
		 * @param integer $lessonId
		 *
		 * @return CourseResource
		 */
		public function setLessonId ($lessonId) {
			if ((is_numeric ($lessonId)) && ($lessonId > 0) && (intval ($lessonId) == $lessonId)) {
				$this->lessonId = intval ($lessonId);
			} else {
				$this->lessonId = null;
			}
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return CourseResource
		 */
		public function setName ($name) {
			if (is_scalar ($name)) {
				$this->name = $name;
			} else {
				$this->name = null;
			}
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return CourseResource
		 */
		public function setType ($type) {
			if (is_scalar ($type)) {
				$this->type = $type;
			} else {
				$this->type = null;
			}
			return $this;
		}

		/**
		 * @param string $url
		 *
		 * @return CourseResource
		 */
		public function setUrl ($url) {
			if (is_scalar ($url)) {
				$this->url = $url;
			} else {
				$this->url = null;
			}
			return $this;
		}

		/**
		 * @throws CourseResourceException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new CourseResourceException (CourseResourceException::ERROR_COURSE_RESOURCE_EMPTY_NAME);
			} else if (empty ($this->type)) {
				throw new CourseResourceException (CourseResourceException::ERROR_COURSE_RESOURCE_EMPTY_TYPE);
			} else if (($this->type == self::TYPE_ATTACHMENT) && (empty ($this->id)) && (empty ($this->fileContents))) {
				throw new CourseResourceException (CourseResourceException::ERROR_COURSE_RESOURCE_EMPTY_FILE_CONTENTS);
			} else if (($this->type == self::TYPE_URL) && (empty ($this->url))) {
				throw new CourseResourceException (CourseResourceException::ERROR_COURSE_RESOURCE_EMPTY_URL);
			}
		}

		/**
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					!empty ($this->fileContents) ? base64_encode ($this->fileContents) : null,
					$this->lessonId,
					$this->name,
					$this->type,
					$this->url,
					$this->exerciseId,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->fileContents,
				$this->lessonId,
				$this->name,
				$this->type,
				$this->url,
				$this->exerciseId,
			) = unserialize ($serialized);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableTypes () {
			return array (self::TYPE_ATTACHMENT, self::TYPE_URL);
		}

		/**
		 * @return string
		 */
		public static function getFolderPath () {
			return PlatzillaUtils::getPlatzillaRootFolderPath () . '/' . self::$FOLDER_PATH;
		}

		/**
		 * @return string
		 */
		public static function getFolderUri () {
			return PlatzillaUtils::getPlatzillaRootUri () . '/' . self::$FOLDER_PATH;
		}

		/**
		 * @return CourseResource
		 */
		public static function getInstance () {
			return new self ();
		}

	}
