<?php
	require_once ('modules/Courses/lib/CourseLessonException.php');
	require_once ('modules/Courses/lib/CourseResource.php');
	require_once ('modules/Courses/lib/CourseTest.php');
	require_once ('modules/Courses/lib/LessonExercises.php');

	class CourseLesson implements Serializable {

		/** @var integer */
		private $id;

		/** @var integer */
		private $courseId;

		/** @var string */
		private $description;

		/** @var integer */
		private $hasTest;

		/** @var LessonExercises */
		private $lessonExercise;
		
		/** @var string */
		private $name;

		/** @var CourseResource[] */
		private $resources;

		/** @var integer */
		private $status;
		
		/** @var CourseTest */
		private $test;

		/** @var string */
		private $typeVideo;

		/** @var string */
		private $videoUrl;

		/** @var string */
		private $userLessonStatus;
		
		/**
		 * @throws CourseLessonException
		 * @throws CourseResourceException
		 */
		private function validateResources () {
			if (empty ($this->resources)) {
				return;
			}
			foreach ($this->resources as $resource) {
				if (!($resource instanceof CourseResource)) {
					throw new CourseLessonException (CourseLessonException::ERROR_COURSE_LESSON_INVALID_RESOURCE);
				} else {
					$resource->validate ();
				}
			}
		}

		/**
		 * @throws CourseLessonException
		 * @throws CourseTestException
		 */
		private function validateTest () {
			if ($this->getHasTest ()) {
				if (empty ($this->test)) {
					throw new CourseLessonException (CourseLessonException::ERROR_COURSE_LESSON_EMPTY_TEST);
				} else {
					$this->test->validate ();
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
		 * @return integer
		 */
		public function getCourseId () {
			return $this->courseId;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return integer
		 */
		public function getHasTest () {
			return $this->hasTest;
		}
		
		/**
		 * @return LessonExercises
		 */
		public function getLessonExercise () {
			return $this->lessonExercise;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return CourseResource[]
		 */
		public function getResources () {
			return $this->resources;
		}
		
		/**
		 * @return integer
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @return CourseTest
		 */
		public function getTest () {
			return $this->test;
		}

		/**
		 * @return string
		 */
		public function getTypeVideo () {
			return $this->typeVideo;
		}

		/**
		 * @return string
		 */
		public function getVideoUrl () {
			return $this->videoUrl;
		}
		
		/**
		 * @return string
		 */
		public function getUserLessonStatus () {
			return $this->userLessonStatus;
		}

		/**
		 * @param integer $id
		 *
		 * @return CourseLesson
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
		 * @param integer $courseId
		 *
		 * @return CourseLesson
		 */
		public function setCourseId ($courseId) {
			if ((is_numeric ($courseId)) && ($courseId > 0) && (intval ($courseId) == $courseId)) {
				$this->courseId = intval ($courseId);
			} else {
				$this->courseId = null;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return CourseLesson
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
		 * @param integer $hasTest
		 *
		 * @return CourseLesson
		 */
		public function setHasTest ($hasTest) {
			$this->hasTest = $hasTest;
			return $this;
		}
		
		/**
		 * @param LessonExercises $lessonExercise
		 *
		 * @return CourseLesson
		 */
		public function setLessonExercise ($lessonExercise) {
			if ($lessonExercise instanceof LessonExercises) {
				$this->lessonExercise = $lessonExercise;
			} else {
				$this->lessonExercise = null;
			}
			return $this;
		}
		
		/**
		 * @param string $name
		 *
		 * @return CourseLesson
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
		 * @param CourseResource[] $resources
		 *
		 * @return CourseLesson
		 */
		public function setResources ($resources) {
			if ((is_array ($resources)) && (!empty ($resources))) {
				$this->resources = $resources;
			} else {
				$this->resources = null;
			}
			return $this;
		}
		
		/**
		 * @param integer $status
		 *
		 * @return CourseLesson
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}

		/**
		 * @param CourseTest $test
		 *
		 * @return CourseLesson
		 */
		public function setTest ($test) {
			if ($test instanceof CourseTest) {
				$this->test = $test;
			} else {
				$this->test = null;
			}
			return $this;
		}

		/**
		 * @param $typeVideo
		 *
		 * @return CourseLesson
		 */
		public function setTypeVideo ($typeVideo) {
			$this->typeVideo = $typeVideo;
			return $this;
		}

		/**
		 * @param string $videoUrl
		 *
		 * @return CourseLesson
		 */
		public function setVideoUrl ($videoUrl) {
			$url = filter_var ($videoUrl, FILTER_SANITIZE_URL);
			if (filter_var ($url, FILTER_VALIDATE_URL)) {
				$this->videoUrl = $url;
			} else {
				$this->videoUrl = null;
			}
			return $this;
		}
		
		/**
		 * @param string $userLessonStatus
		 *
		 * @return CourseLesson
		 */
		public function setUserLessonStatus ($userLessonStatus) {
			$this->userLessonStatus = $userLessonStatus;
			return $this;
		}
		
		/**
		 * @throws CourseLessonException
		 * @throws CourseResourceException
		 * @throws CourseTestException
		 */
		public function validate () {
			if (empty ($this->description)  && $this->hasTest) {
				throw new CourseLessonException (CourseLessonException::ERROR_COURSE_LESSON_EMPTY_DESCRIPTION);
			} else if (empty ($this->name)) {
				throw new CourseLessonException (CourseLessonException::ERROR_COURSE_LESSON_EMPTY_NAME);
			}
			$this->validateResources ();
			$this->validateTest ();
		}

		/**
		 * @return string
		 */
		public function serialize () {
			if (!empty ($this->resources)) {
				$serializedResources = array ();
				foreach ($this->resources as $resource) {
					$serializedResources [] = $resource->serialize ();
				}
			} else {
				$serializedResources = null;
			}

			$serializedTest = !empty ($this->test) ? $this->test->serialize () : null;

			return serialize (
				array (
					$this->id,
					$this->courseId,
					$this->description,
					$this->name,
					$this->videoUrl,
					$this->hasTest,
					$serializedResources,
					$serializedTest,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->courseId,
				$this->description,
				$this->name,
				$this->videoUrl,
				$this->hasTest,
				$serializedResources,
				$serializedTest,
			) = unserialize ($serialized);

			if (!empty ($serializedResources)) {
				foreach ($serializedResources as $serializedResource) {
					$resource = CourseResource::getInstance ();
					$resource->unserialize ($serializedResource);
					$this->resources [] = $resource;
				}
			} else {
				$this->resources = null;
			}

			if (!empty ($serializedTest)) {
				$test = CourseTest::getInstance ();
				$test->unserialize ($serializedTest);
				$this->test = $test;
			} else {
				$this->test = null;
			}
		}

		/**
		 * @return CourseLesson
		 */
		public static function getInstance () {
			return new self ();
		}

	}
