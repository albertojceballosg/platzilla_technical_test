<?php
	require_once ('modules/Courses/lib/CourseException.php');
	require_once ('modules/Courses/lib/CoursesInterface.php');
	require_once ('modules/Courses/lib/CourseLesson.php');

	class Course implements Serializable {
		const LEVEL_BEGINNER     = 'BEGINNER';
		const LEVEL_INTERMEDIATE = 'INTERMEDIATE';
		const LEVEL_ADVANCED     = 'ADVANCED';

		const STATUS_ACTIVE   = 'ACTIVE';
		const STATUS_INACTIVE = 'INACTIVE';

		/** @var integer */
		private $id;
		
		/** @var CourseCategory */
		private $category;
		
		/** @var integer */
		private $categoryId;
		
		/** @var string */
		private $description;
		
		/** @var string */
		private $forumName;
		
		/** @var string */
		private $forumUrl;

		/** @var boolean */
		private $idPaid;

		/** @var string */
		private $imageCourse;

		/** @var string */
		private $imageType;

		/** @var CourseLesson[] */
		private $lessons;

		/** @var integer */
		private $lessonIndex;

		/** @var integer */
		private $lessonToPay;

		/** @var string */
		private $level;

		/** @var string */
		private $name;

		/** @var float */
		private $price;

		/** @var integer */
		private $seenBy;
		
		/** @var CourseSerie */
		private $serie;
		
		/** @var integer */
		private $serieId;
		
		/** @var string */
		private $status;

		/** @var string */
		private $targetAudience;
		
		/** @var string */
		private $videoCourse;

		/** @var string */
		private $videoType;
		
		/** @var string */
		private $userCourseStatus;
		
		/**
		 * @throws CourseException
		 * @throws CourseLessonException
		 * @throws CourseResourceException
		 * @throws CourseTestException
		 */
		private function validateLessons () {
			if (empty ($this->lessons)) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY_LESSONS);
			}
			foreach ($this->lessons as $lesson) {
				if (!($lesson instanceof CourseLesson)) {
					throw new CourseException (CourseException::ERROR_COURSE_INVALID_LESSON);
				} else {
					$lesson->validate ();
				}
			}
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return CourseCategory
		 */
		public function getCategory () {
			return $this->category;
		}
		
		/**
		 * @return integer
		 */
		public function getCategoryId () {
			return $this->categoryId;
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
		public function getForumName () {
			return $this->forumName;
		}
		
		/**
		 * @return string
		 */
		public function getForumUrl () {
			return $this->forumUrl;
		}

		/**
		 * @return boolean
		 */
		public function isPaid () {
			return $this->idPaid;
		}

		/**
		 * @return string
		 */
		public function getImageCourse () {
			return $this->imageCourse;
		}

		/**
		 * @return string
		 */
		public function getImageType () {
			return $this->imageType;
		}

		/**
		 * @return CourseLesson[]
		 */
		public function getLessons () {
			return $this->lessons;
		}

		/**
		 * @return integer
		 */
		public function getLessonIndex() {
			return $this->lessonIndex;
		}

		/**
		 * @return integer
		 */
		public function getLessonToPay () {
			return $this->lessonToPay;
		}

		/**
		 * @return string
		 */
		public function getLevel () {
			return $this->level;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return float
		 */
		public function getPrice () {
			return $this->price;
		}

		/**
		 * @return integer
		 */
		public function getSeenBy () {
			return $this->seenBy;
		}
		
		/**
		 * @return CourseSerie
		 */
		public function getSerie () {
			return $this->serie;
		}
		
		/**
		 * @return integer
		 */
		public function getSerieId () {
			return $this->serieId;
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
		public function getTargetAudience () {
			return $this->targetAudience;
		}

		/**
		 * @return string
		 */
		public function getVideoCourse () {
			return $this->videoCourse;
		}

		/**
		 * @return string
		 */
		public function getVideoType () {
			return $this->videoType;
		}
		
		/**
		 * @return string
		 */
		public function getUserCourseStatus () {
			return $this->userCourseStatus;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return Course
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
		 * @param CourseCategory $category
		 *
		 * @return Course
		 */
		public function setCategory ($category) {
			if ($category instanceof CourseCategory) {
				$this->category = $category;
			} else {
				$this->category = null;
			}
			return $this;
		}
		
		/**
		 * @param integer $categoryId
		 *
		 * @return Course
		 */
		public function setCategoryId ($categoryId) {
			if ((is_numeric ($categoryId)) && ($categoryId > 0) && (intval ($categoryId) == $categoryId)) {
				$this->categoryId = $categoryId;
			} else {
				$this->categoryId = null;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return Course
		 */
		public function setDescription ($description) {
			if (is_scalar ($description)) {
				$this->description = $description;
			} else {
				$this->description = null;
			}
			return $this;
		}
		
		/**
		 * @param string $forumName
		 *
		 * @return Course
		 */
		public function setForumName ($forumName) {
			if (is_scalar ($forumName)) {
				$this->forumName = $forumName;
			} else {
				$this->forumName = null;
			}
			return $this;
		}
		
		/**
		 * @param string $forumUrl
		 *
		 * @return Course
		 */
		public function setForumUrl ($forumUrl) {
			if (is_scalar ($forumUrl)) {
				$this->forumUrl = $forumUrl;
			} else {
				$this->forumUrl = null;
			}
			return $this;
		}
		
		/**
		 * @param boolean $idPaid
		 *
		 * @return Course
		 */
		public function setPaid ($idPaid) {
			$this->idPaid = $idPaid;
			return $this;
		}

		/**
		 * @param string $imageCourse
		 *
		 * @return Course
		 */
		public function setImageCourse ($imageCourse) {
			$this->imageCourse = $imageCourse;
			return $this;
		}

		/**
		 * @param string $imageType
		 *
		 * @return Course
		 */
		public function setImageType ($imageType) {
			$this->imageType = $imageType;
			return $this;
		}

		/**
		 * @param CourseLesson[] $lessons
		 *
		 * @return Course
		 */
		public function setLessons ($lessons) {
			if ((is_array ($lessons)) && (!empty ($lessons))) {
				$this->lessons = $lessons;
			} else {
				$this->lessons = null;
			}
			return $this;
		}

		/**
		 * @param integer $lessonIndex
		 *
		 * @return Course
		 */
		public function setLessonIndex ($lessonIndex) {
			$this->lessonIndex = $lessonIndex;
			return $this;
		}

		/**
		 * @param integer $lessonId
		 *
		 * @return Course
		 */
		public function setLessonToPay ($lessonId) {
			if ((is_numeric ($lessonId)) && ($lessonId > 0) && (intval ($lessonId) == $lessonId)) {
				$this->lessonToPay = $lessonId;
			} else {
				$this->lessonToPay = 0;
			}
			return $this;
		}

		/**
		 * @param string $level
		 *
		 * @return Course
		 */
		public function setLevel ($level) {
			if (in_array ($level, self::getAvailableLevels ())) {
				$this->level = $level;
			} else {
				$this->level = null;
			}
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return Course
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
		 * @param float $price
		 *
		 * @return Course
		 */
		public function setPrice ($price) {
			if ((is_numeric ($price)) && ($price >= 0)) {
				$this->price = floatval ($price);
			} else {
				$this->price = null;
			}
			return $this;
		}

		/**
		 * @param integer $seenBy
		 *
		 * @return Course
		 */
		public function setSeenBy($seenBy) {
			if ((is_numeric ($seenBy)) && ($seenBy > 0) && (intval ($seenBy) == $seenBy)) {
				$this->seenBy = intval ($seenBy);
			} else {
				$this->seenBy = null;
			}
			return $this;
		}
		
		/**
		 * @param CourseSerie $serie
		 *
		 * @return Course
		 */
		public function setSerie ($serie) {
			if ($serie instanceof CourseSerie) {
				$this->serie = $serie;
			} else {
				$this->serie = null;
			}
			return $this;
		}
		
		/**
		 * @param $serieId
		 *
		 * @return Course
		 */
		public function setSerieId ($serieId) {
			$this->serieId = $serieId;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return Course
		 */
		public function setStatus ($status) {
			if (in_array ($status, self::getAvailableStatuses ())) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}

		/**
		 * @param string $targetAudience
		 *
		 * @return Course
		 */
		public function setTargetAudience ($targetAudience) {
			if (is_scalar ($targetAudience)) {
				$this->targetAudience = $targetAudience;
			} else {
				$this->targetAudience = null;
			}
			return $this;
		}

		/**
		 * @param string $videoCourse
		 *
		 * @return Course
		 */
		public function setVideoCourse ($videoCourse) {
			$this->videoCourse = $videoCourse;
			return $this;
		}

		/**
		 * @param string $videoType
		 *
		 * @return Course
		 */
		public function setVideoType ($videoType) {
			$this->videoType = $videoType;
			return $this;
		}
		
		/**
		 * @param integer $userCourseStatus
		 *
		 * @return Course
		 */
		public function setUserCourseStatus ($userCourseStatus) {
			$this->userCourseStatus = $userCourseStatus;
			return $this;
		}
		
		/**
		 * @throws CourseException
		 * @throws CourseLessonException
		 */
		public function validate () {
			if (empty ($this->categoryId)) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY_CATEGORY);
			} else if (empty ($this->description)) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY_DESCRIPTION);
			} else if (!empty ($this->forumUrl)) {
				if (!filter_var($this->forumUrl, FILTER_VALIDATE_URL)) {
					throw new CourseException (CourseException::ERROR_FORUM_URL_INVALID);
				}
			} else if (empty ($this->level)) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY_LEVEL);
			} else if (empty ($this->name)) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY_NAME);
			} else if ($this->price === null) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY_PRICE);
			} else if (empty ($this->status)) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY_STATUS);
			} else if (empty ($this->serieId)) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY_TARGET_AUDIENCE);
			}
			$this->validateLessons ();
		}

		/**
		 * @return string
		 */
		public function serialize () {
			if (!empty ($this->lessons)) {
				$serializedLessons = array ();
				foreach ($this->lessons as $lesson) {
					$serializedLessons [] = $lesson->serialize ();
				}
			} else {
				$serializedLessons = null;
			}

			return serialize (
				array (
					$this->id,
					$this->categoryId,
					$this->description,
					$this->forumName,
					$this->forumUrl,
					$this->imageCourse,
					$this->imageType,
					$this->level,
					$this->lessonIndex,
					$this->name,
					$this->price,
					$this->seenBy,
					$this->status,
					$this->serieId,
					$this->targetAudience,
					$this->videoCourse,
					$this->videoType,
					$serializedLessons,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->categoryId,
				$this->description,
				$this->forumName,
				$this->forumUrl,
				$this->imageCourse,
				$this->imageType,
				$this->level,
				$this->lessonIndex,
				$this->name,
				$this->price,
				$this->seenBy,
				$this->status,
				$this->serieId,
				$this->targetAudience,
				$this->videoCourse,
				$this->videoType,
				$serializedLessons,
			) = unserialize ($serialized);

			if (!empty ($serializedLessons)) {
				foreach ($serializedLessons as $serializedLesson) {
					$lesson = CourseLesson::getInstance ();
					$lesson->unserialize ($serializedLesson);
					$this->lessons [] = $lesson;
				}
			} else {
				$this->lessons = null;
			}
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableLevels () {
			return array (self::LEVEL_ADVANCED, self::LEVEL_BEGINNER, self::LEVEL_INTERMEDIATE);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_ACTIVE, self::STATUS_INACTIVE);
		}

		/**
		 * @return Course
		 */
		public static function getInstance () {
			return new self ();
		}

	}
