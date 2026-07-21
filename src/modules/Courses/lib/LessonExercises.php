<?php
	require_once ('modules/Courses/lib/CoursesInterface.php');
	require_once ('modules/Courses/lib/CourseException.php');
	class LessonExercises implements CoursesInterface {
		/** @var string */
		private $description;
		
		/** @var CourseResource[] */
		private $exercisesResources;
		
		/** @var integer */
		private $hasTest;
		
		/** @var integer */
		private $id;
		
		/** @var integer */
		private $lessonId;
		
		/** @var string */
		private $name;
		
		/** @var float */
		private $passingScore;
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}
		
		/**
		 * @return CourseResource[]
		 */
		public function getExercisesResources () {
			return $this->exercisesResources;
		}
		
		/**
		 * @return integer
		 */
		public function getHasTest () {
			return $this->hasTest;
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
		 * @return float
		 */
		public function getPassingScore () {
			return $this->passingScore;
		}
		
		/**
		 * @param string $description
		 *
		 * @return LessonExercises
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param CourseResource[] $exercisesResources
		 *
		 * @return LessonExercises
		 */
		public function setExercisesResources ($exercisesResources) {
			$this->exercisesResources = $exercisesResources;
			return $this;
		}
		
		/**
		 * @param integer $hasTest
		 *
		 * @return LessonExercises
		 */
		public function setHasTest ($hasTest) {
			$this->hasTest = $hasTest;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return LessonExercises
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param integer $lessonId
		 *
		 * @return LessonExercises
		 */
		public function setLessonId ($lessonId) {
			$this->lessonId = $lessonId;
			return $this;
		}
		
		/**
		 * @param string $name
		 *
		 * @return LessonExercises
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}
		
		/**
		 * @param float $passingScore
		 *
		 * @return LessonExercises
		 */
		public function setPassingScore ($passingScore) {
			$this->passingScore = $passingScore;
			return $this;
		}
		
		/**
		 * @param array $data
		 *
		 * @return LessonExercises
		 */
		public function populate ($data) {
			$this->setId ($data['id']);
			$this->setLessonId ($data['lesson_id']);
			$this->setName ($data['name']);
			$this->setDescription ($data['description']);
			$this->setHasTest ($data['has_test']);
			$this->setPassingScore ($data['passing_score']);
			return $this;
		}
		
		/**
		 * @return LessonExercises
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
