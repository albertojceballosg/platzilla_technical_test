<?php
	require_once ('include/utils/ImageUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CourseManager.php');
	require_once ('modules/Courses/lib/CoursesInterface.php');
	require_once ('modules/Courses/lib/LessonExercises.php');
	require_once ('modules/materials/Exceptions/FolderException.php');

	abstract class CoursesHelper {

		const IMAGEN_TYPE   = array('png', 'jpg', 'jpeg', 'gif');
		const IMAGEN_WIDTH  = 250;
		const IMAGEN_HEIGHT = 181;
		const SQL_DATA      = array (
			'COURSE'   => array('table' => 'vtiger_courses', 'idField' => 'courseid'),
			'CATEGORY' => array('table' => 'vtiger_coursecategories', 'idField' => 'categoryid'),
			'SERIE'    => array('table' => 'vtiger_course_serie', 'idField' => 'serieid'),
		);

		/**buildAnswers
		 * Construye una lista de objetos CourseTestAnswer a partir de los datos de la pregunta.
		 *
		 * @param array $questionData Datos de la pregunta incluyendo respuestas.
		 * @return CourseTestAnswer[]|null Retorna un arreglo de objetos CourseTestAnswer o null si no hay respuestas.
		 */
		private static function buildAnswers ($questionData) {
			$answersData = $questionData ['answers'];
			if ((!is_array ($answersData)) || (empty ($answersData))) {
				return null;
			}

			$answers = array ();
			foreach ($answersData as $answerData) {
				$answers [] = CourseTestAnswer::getInstance ()
					->setId ($answerData ['answerid'])
					->setCorrect ($answerData ['correct'] == 1)
					->setFeedback ($answerData ['feedback'])
					->setQuestionId ($questionData ['questionid'])
					->setStatement ($answerData ['statement']);
			}
			return $answers;
		}

		/**buildLessonExercise
		 * Constructs a LessonExercises object from the provided lesson data.
		 *
		 * @param array $lessonData Data of the lesson including the exercise.
		 * @return LessonExercises|null Returns a LessonExercises object or null if no exercise data is provided.
		 * @usage Used to create instances of exercises associated with lessons.
		 * @invocation Called from buildLessons() for each lesson with exercise data.
		 */
		private static function  buildLessonExercise ($lessonData) {
			$exerciseData = $lessonData ['exercise'];
			if ((!is_array ($lessonData)) || (empty ($lessonData))) {
				return null;
			}
			$exercise = LessonExercises::getInstance ()
				->setId ($lessonData ['exercises_id'])
				->setDescription ($lessonData ['exercis_description'])
				->setHasTest ($lessonData['exercis_hastest'])
				->setLessonId ($lessonData['lessonid'])
				->setName ($lessonData['exercise_name'])
				->setPassingScore ($lessonData['minimum_score']);
			
			return $exercise;
		}

		/**buildLessons
		 * Constructs a list of CourseLesson objects from the provided course data.
		 *
		 * @param array $courseData Data of the course including lessons.
		 * @return CourseLesson[]|null Returns an array of CourseLesson objects or null if no lessons are provided.
		 * @usage Used to create instances of complete lessons with exercises and resources.
		 * @invocation Called from various points in the system where a complete course needs to be constructed.
		 */
		private static function buildLessons ($courseData) {
			$lessonsData = $courseData ['lessons'];
			if ((!is_array ($lessonsData)) || (empty ($lessonsData))) {
				return null;
			}

			$lessons = array ();
			foreach ($lessonsData as $lessonData) {
				if (empty ($lessonData ['lessonname'])) {
					continue;
				}
				$hasTest = intval ($lessonData ['hastest']);
				$lessons [] = CourseLesson::getInstance ()
					->setId ($lessonData ['lessonid'])
					->setCourseId ($courseData ['courseid'])
					->setDescription ($lessonData ['description'])
					->setHasTest ($hasTest)
					->setLessonExercise(self::buildLessonExercise($lessonData))
					->setName ($lessonData ['lessonname'])
					->setResources (self::buildResources ($lessonData))
					->setStatus ($lessonData ['lesson_status'])
					->setTest (($hasTest) ? self::buildTest ($lessonData) : null)
					->setTypeVideo($lessonData['videotype'])
					->setVideoUrl (self::buildUrlVideo ($lessonData['videotype'], $lessonData ['videourl']));
			}
			return $lessons;
		}

		/**
		 * Constructs a list of CourseTestQuestion objects from the provided test data.
		 *
		 * @param array $testData Data of the test including questions.
		 * @param integer $lessonId ID of the lesson associated with the test.
		 * @return CourseTestQuestion[]|null Returns an array of CourseTestQuestion objects or null if no questions are provided.
		 * @usage Used to create instances of questions associated with a test.
		 * @invocation Called from buildTest() for each test in the lesson data.
		 */
		private static function buildQuestions ($testData, $lessonId) {
			$questionsData = $testData ['questions'];
			if ((!is_array ($questionsData)) || (empty ($questionsData))) {
				return null;
			}

			$questions = array ();
			foreach ($questionsData as $questionData) {
				$questions [] = CourseTestQuestion::getInstance ()
					->setId ($questionData ['questionid'])
					->setAnswers (self::buildAnswers ($questionData))
					->setStatement ($questionData ['statement'])
					->setTestId ($lessonId)
					->setType ($questionData ['questiontype']);
			}
			return $questions;
		}

		/**
		 * Constructs a list of CourseResource objects from the provided lesson data.
		 *
		 * @param array $lessonData Data of the lesson including resources.
		 * @return CourseResource[]|null Returns an array of CourseResource objects or null if no resources are provided.
		 * @usage Used to create instances of resources associated with a lesson.
		 * @invocation Called from buildLessons() for each lesson with resource data.
		 */
		private static function buildResources ($lessonData) {
			$resourcesData = $lessonData ['resources'];
			if ((!is_array ($resourcesData)) || (empty ($resourcesData))) {
				return null;
			}

			$resources  = array ();
			foreach ($resourcesData as $resourceData) {
				$resources [] = CourseResource::getInstance ()
					->setId ($resourceData ['resourceid'])
					->setExerciseId ($resourceData ['exerciseid'])
					->setFileContents ($resourceData ['filedata'])
					->setHasExercise ($resourceData ['has_exercise'])
					->setLessonId ($lessonData ['lessonid'])
					->setName ($resourceData ['resourcename'])
					->setType ($resourceData ['resourcetype'])
					->setUrl ($resourceData ['url']);
			}
			return $resources;
		}

		/**
		 * Construye un objeto CourseTest a partir de los datos de la lección.
		 *
		 * @param array $lessonData Datos de la lección incluyendo el test.
		 * @return CourseTest|null Retorna un objeto CourseTest o null si no hay datos de test.
		 */
		private static function buildTest ($lessonData) {
			$testData = $lessonData ['test'];
			if (empty ($testData)) {
				return null;
			} else if (empty ($testData ['totalquestionspertest'])) {
				return null;
			}

			return CourseTest::getInstance ()
				->setDescription ($testData ['description'])
				->setFeedback ($testData ['feedback'])
				->setFeedbackNotApproved ($testData ['feedback_not_approved'])
				->setLessonId ($lessonData ['lessonid'])
				->setMinimumScore ($testData ['minimumscore'])
				->setQuestions (self::buildQuestions ($testData, $lessonData ['lessonid']))
				->setTotalQuestionsPerTest ($testData ['totalquestionspertest']);
		}

		/**
		 * Construye la URL del video basado en el tipo de video y la URL proporcionada.
		 *
		 * @param string $videoType Tipo de video (e.g., VIMEO, YOUTUBE).
		 * @param string $url URL del video.
		 * @return null|string Retorna la URL completa del video o null si el tipo de video no es válido.
		 */
		private static function buildUrlVideo ($videoType, $url) {
			if (!in_array($videoType, CoursesInterface::COURSE_TYPE_VIDEO)) {
				return null;
			} else if($videoType == 'VIMEO') {
				return $url;
			} else {
				$dummy = explode('/', $url);
				return CoursesInterface::YOUTUBE_BASE_URL . $dummy [ (count ($dummy) - 1) ];
			}
		}
		
		/**
		 * Obtiene los recursos de una lección desde la base de datos.
		 *
		 * @param PearDatabase $masterAdb Conexión a la base de datos.
		 * @param integer $lessonId ID de la lección.
		 * @param integer|null $exerciseId ID del ejercicio, si aplica.
		 * @return CourseResource[]|null Retorna un arreglo de objetos CourseResource o null si no hay recursos.
		 */
		private static function fetchResources ($masterAdb, $lessonId, $exerciseId = null) {
			if (empty ($lessonId)) {
				return null;
			}
			if (!empty ($exerciseId)) {
				$result = $masterAdb->pquery ('SELECT * FROM vtiger_courseresources WHERE lessonid=? AND lesson2exercisesid=?', array ($lessonId, $exerciseId));
			} else {
				$result = $masterAdb->pquery ('SELECT * FROM vtiger_courseresources WHERE lessonid=?', array ($lessonId));
			}
			if ($masterAdb->num_rows ($result) > 0) {
				$resources = array ();
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$resources [] = CourseResource::getInstance ()
						->setId (intval ($row ['resourceid']))
						->setExerciseId (intval ($row ['lesson2exercisesid']))
						->setHasExercise ((!empty ($row ['lesson2exercisesid'])) ? 'YES':'NO')
						->setLessonId (intval ($row ['lessonid']))
						->setFileName ((
							$row ['resourcetype'] == 'ATTACHMENT') ? self::getFileMimeType($row ['resourceid'],
							$row ['resourcename']) : null
						)
						->setName ($row ['resourcename'])
						->setType ($row ['resourcetype'])
						->setUrl ($row ['url']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($resources)) ? $resources : null;
		}
		
		/**
		 * Determines the MIME type of a resource file based on its ID and name.
		 *
		 * @param string $resourceId ID of the resource.
		 * @param string $resourceName Name of the resource.
		 * @return string Returns the sanitized resource name with its MIME type appended.
		 * @throws Exception Throws an exception if the resource file does not exist.
		 * @usage Used to retrieve the MIME type for resources, particularly attachments.
		 * @invocation Called internally within the class when handling resource types.
		 */
		private static function getFileMimeType ($resourceId, $resourceName) {
			$resourceFolderPath = CourseResource::getFolderPath ();
			$resourceFilePath   = "{$resourceFolderPath}/{$resourceId}.bin";
			if (!file_exists ($resourceFilePath)) {
				throw new Exception ('No se encuentra el recurso solicitado');
			}
			$finfo               = finfo_open (FILEINFO_MIME_TYPE);
			$resourceContentType = finfo_file ($finfo, $resourceFilePath);
			finfo_close ($finfo);
			$dummy        = (!empty ($resourceContentType)) ? explode('/', $resourceContentType) : null;
			$resourceName = CoursesHelper::sanitizeString ($resourceName);
			return (empty ($dummy)) ? $resourceName : "{$resourceName}.{$dummy [ (count ($dummy) - 2)]}";
		}
		
		/**
		 * Retrieves payment information for a specific lesson by querying the database.
		 *
		 * @param PearDatabase $masterAdb Database connection to execute the query.
		 * @param integer $lessonId ID of the lesson for which payment information is needed.
		 * @return array Returns an associative array containing `lessontopay` and `courseid`.
		 * @throws Exception Throws an exception if the query fails.
		 * @usage Used to obtain payment-related details for a lesson.
		 * @invocation Typically called when payment information is required for a lesson.
		 */
		public static function getLessonToPay ($masterAdb, $lessonId) {
			//Modificado por GGC 20250207 porque no daba resultados
			$vconsulta = "SELECT c.lessontopay, c.courseid FROM vtiger_courses c INNER JOIN vtiger_courselessons l ON l.courseid = c.courseid WHERE l.lessonid = $lessonId ";
			return $masterAdb->run_query_record ($vconsulta);
			
			/*Marcado por GGC. Contenido original
			return $adb->run_query_record (
				'SELECT c.lessontopay, c.courseid FROM vtiger_courses c INNER JOIN vtiger_courselessons l ON l.courseid = c.courseid WHERE l.lessonid = {$lessonId}');
			*/
		}

		/**
		 * Determines if a course is paid based on the lesson ID, instance code, and course ID.
		 *
		 * @param PearDatabase $adb Database connection to execute queries.
		 * @param integer $lessonId ID of the lesson to check.
		 * @param string $instanceCode Code of the instance to verify payment.
		 * @param integer $courseId ID of the course to check.
		 * @return boolean Returns true if the course is paid, false otherwise.
		 * @throws Exception Throws an exception if any query fails.
		 * @usage Used to verify if a course requires payment before granting access.
		 * @invocation Typically called when checking access permissions for course content.
		 */
		public static function isPaidCourse ($masterAdb, $lessonId, $instanceCode, $courseId) {
			$course     = self::getLessonToPay ($masterAdb, $lessonId);
			if (is_array ($course) && !empty ($course ['lessontopay'])) {
				$lessons = $masterAdb->run_query_allrecords ("SELECT lessonid FROM vtiger_courselessons WHERE courseid = {$courseId}  ORDER BY lessonid ASC");
				$lessons = array_column ($lessons, 'lessonid');
			}
			$paidCourse = true;
			if ($course ['lessontopay'] && $lessons) {
				if ($lessonId >= $lessons [$course ['lessontopay']]) {
					$paidCourse = self::isPaidInstance ($masterAdb, $course ['courseid'], $instanceCode);
				}
			}
			return $paidCourse;
		}

		/**
		 * Constructs a Course object from the provided course data.
		 *
		 * @param array $courseData Data of the course including its attributes.
		 * @return Course Returns a Course object with the specified attributes.
		 * @usage Used to create a complete course object with all its associated attributes.
		 * @invocation Typically called when a course needs to be instantiated with full details.
		 */
		public static function buildCourse ($courseData) {
			return Course::getInstance ()
				->setId ($courseData ['courseid'])
				->setCategoryId ($courseData ['categoryid'])
				->setDescription ($courseData ['description'])
				->setImageCourse ($courseData ['imagePhoto'])
				->setImageType ($courseData ['imageType'])
				->setLessons (self::buildLessons ($courseData))
				->setLessonToPay ($courseData ['lessonToPay'])
				->setLevel ($courseData ['level'])
				->setName ($courseData ['coursename'])
				->setPrice ($courseData ['price'])
				->setStatus ($courseData ['status'])
				->setSerieId ($courseData ['targetaudience'])
				->setVideoCourse ($courseData ['videoCourse'])
				->setVideoType ($courseData ['videoType'])
				->setForumName ($courseData ['forumName'])
				->setForumUrl ($courseData ['forumUrl']);
		}
		
		/**
		 * Updates the status of a specific course or related entity in the database.
		 *
		 * @param PearDatabase $masterAdb Database connection to execute the query.
		 * @param integer $codId ID of the entity to update.
		 * @param string $newStatus New status to set for the entity.
		 * @param string $type Type of entity to update (e.g., COURSE, CATEGORY).
		 * @throws Exception Throws an exception if validation fails or update cannot be performed.
		 * @usage Used to update the status of courses or related entities in the system.
		 * @invocation Typically called when a status change is required for a course or related entity.
		 */
		public static function changeStatusInCourses ($masterAdb, $codId, $newStatus, $type) {
			if (!is_numeric ($codId) || empty($type)) {
				throw new Exception ('Imposible actualizar, Error en datos!');
			}
			
			$sqlData = self::SQL_DATA [$type];
			$adb->pquery (
				"UPDATE {$sqlData['table']} SET status=? WHERE {$sqlData['idField']}=?",
				array ($newStatus, $codId)
			);
		}
		
		/**
		 * Deletes a course from the database.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $courseId ID of the course to delete.
		 * @throws Exception Throws an exception if the course cannot be deleted.
		 * @usage Used to remove a course from the system.
		 * @invocation Typically called when a course needs to be permanently removed.
		 */
		public static function deleteCourse (PearDatabase $adb, $courseId) {
			// --- Nueva lógica: Verificar uso del curso en la madre ---
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$sql = "SELECT COUNT(DISTINCT instance_code) as inst_count FROM vtiger_courses_instance_seen WHERE courseid = ?";
			$result = $masterAdb->pquery($sql, array($courseId));
			$instCount = 0;
			if ($result && $masterAdb->num_rows($result) > 0) {
				$instCount = intval($masterAdb->query_result($result, 0, 'inst_count'));
			}
			if ($instCount > 0) {
				// Desactivar el curso
				$masterAdb->pquery("UPDATE vtiger_courses SET status = 'INACTIVE' WHERE courseid = ?", array($courseId));
				throw new Exception("El curso está en uso por $instCount instancia(s) y no puede ser eliminado, sólo deshabilitado.");
			}
			$course = $masterAdb->fetchCourse ($courseId);
			if (empty ($course)) {
				return;
			}
			$masterAdb->deleteCourse ($course);
		}
		
		/**
		 * Deletes an entity associated with a course, such as a lesson or category, from the database.
		 *
		 * @param PearDatabase $masterAdb Database connection to execute the query.
		 * @param integer $codId ID of the entity to delete.
		 * @param string $type Type of entity to delete (e.g., COURSE, LESSON).
		 * @throws Exception Throws an exception if the entity cannot be deleted due to dependencies.
		 * @usage Used to remove entities associated with courses, ensuring no dependencies exist.
		 * @invocation Typically called when an entity associated with a course needs to be removed.
		 */
		public static function deleteInCourse ($masterAdb, $codId, $type) {
			if (!is_numeric ($codId) || empty($type)) {
				throw new Exception ('Imposible eliminar, Error en datos!');
			} else if ($type == 'COURSE') {
				self::deleteCourse ($masterAdb, $codId);
				return;
			}
			
			$sqlData = self::SQL_DATA [$type];
			$result = $masterAdb->pquery ("SELECT courseid FROM vtiger_courses WHERE {$sqlData['idField']}=?", array($codId));
			if ($masterAdb->num_rows ($result) > 0) {
				throw new Exception ("Imposible eliminar por estar asociado(a) a {$masterAdb->num_rows ($result)} curso(s)");
			}
			$masterAdb->pquery ("DELETE FROM {$sqlData['table']} WHERE {$sqlData['idField']}=?", array ($codId));
		}
		
		/**
		 * Evaluates test answers for a lesson and updates the lesson's status based on the evaluation results.
		 *
		 * @param array $data Array containing database connections and user/course information.
		 * @param array &$questions Reference to the array of questions with user-provided answers.
		 * @return array|null Returns updated questions array with evaluation results or null if no questions are provided.
		 * @usage Used to evaluate test answers submitted by a user and update lesson status.
		 * @invocation Typically called when a user submits answers for a test associated with a lesson.
		 */
		public static function evaluateTestAnswers ($data, &$questions) {
			if (!is_array ($questions) || !count ($questions)) {
				return null;
			}
			$masterAdb         = $data ['masterAdb'];
			$adb               = $data ['adb'];
			$userId            = $data ['user'] ;
			$courseId          = $data ['course'];
			$lessonId          = $data ['lesson'];
			$totalWrongAnswers = 0;
			foreach ($questions as $qKey => &$question) {
				foreach ($question['answers'] as $key => $answer) {
					$result = $masterAdb->pquery (
						'SELECT
                            an.answerid,
       						an.feedback,
       						an.statement AS	answer,
						    q.statement,
						    CASE
						        WHEN an.correct = 1 THEN "PASSED"
						        ELSE "TO_BE_PASSED"
						    END AS evaluated
						FROM
						    vtiger_coursetestanswers an
						INNER JOIN vtiger_coursetestquestions q ON q.questionid=an.questionid
						WHERE
						    an.questionid=?
						    AND an.answerid=?',
						array ($question['id'], $answer)
					);
					if ($masterAdb->num_rows ($result) > 0) {
						$row = $masterAdb->fetchByAssoc ($result, -1, false);
						$questions['evaluated'][$answer]['id']        = $question['id'];
						$questions['evaluated'][$answer]['statement'] = $row['statement'];
						$questions['evaluated'][$answer]['answer']    = $row['answer'];
						$questions['evaluated'][$answer]['evaluated'] = $row['evaluated'];
						$questions['evaluated'][$answer]['feedback']  = $row['feedback'];
						if ($row['evaluated'] == 'TO_BE_PASSED') {
							$totalWrongAnswers++;
						}
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
				}
			}
			$lessonStatus = 'LESSON_ASSESSED_BUT_NOT_PASSED';
			$evaluationStatus = 'TEST_NOT_PASSED';
			
			if ($totalWrongAnswers == 0) {
				$evaluationStatus = 'TEST_PASSED';
				
				// Verificar si la lección tiene ejercicio práctico
				$result = $masterAdb->pquery (
					'SELECT lesson2exercisesid 
					FROM vtiger_lessons2exercises 
					WHERE lessonid = ?',
					array($lessonId)
				);
				
				if ($masterAdb->num_rows($result) > 0) {
					// La lección tiene ejercicio práctico, verificar si ya tiene respuesta
					$exerciseId = $masterAdb->query_result($result, 0, 'lesson2exercisesid');
					$attachmentResult = $adb->pquery(
						'SELECT attachmentsid 
						FROM vtiger_attachments2exercises 
						WHERE exercisesid = ? AND userid = ?',
						array($exerciseId, $userId)
					);
					
					if ($adb->num_rows($attachmentResult) > 0) {
						// Ya hay respuesta al ejercicio práctico
						$lessonStatus = 'LESSON_PASSED';
					} else {
						// No hay respuesta al ejercicio práctico
						$lessonStatus = 'LESSON_TEST_PASSED';
					}
					DatabaseUtils::closeResult($attachmentResult);
				} else {
					// La lección no tiene ejercicio práctico
					$lessonStatus = 'LESSON_PASSED';
				}
				DatabaseUtils::closeResult($result);
			}
			
			self::setGoodbyeLesson($adb, $courseId, $lessonId, $userId, $lessonStatus);
			$questions['lessonStatus'] = $lessonStatus;
			$questions['evaluationStatus'] = $evaluationStatus;
			self::saveLessonEvaluated($adb, $questions, $courseId, $lessonId, $userId);
		}
		
		/**
		 * Retrieves a list of course categories from the database.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @return string[]|null Returns an array of category names or null if no categories are found.
		 * @usage Used to obtain available course categories for display or filtering.
		 * @invocation Typically called when a list of course categories is needed for user interfaces or filtering.
		 */
		public static function fetchCategories (PearDatabase $adb) {
			return CourseManager::getInstance ($adb)->fetchCategories ();
		}

		/**
		 * Retrieves a course by its ID from the database.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $courseId ID of the course to retrieve.
		 * @param string|null $instanceCode Optional instance code for additional filtering.
		 * @param PearDatabase $targetAdb Target database connection for fetching course details.
		 * @param integer $userId ID of the user requesting the course details.
		 * @return Course|null Returns a Course object with the specified ID or null if not found.
		 * @throws Exception Throws an exception if the course cannot be retrieved.
		 * @usage Used to obtain detailed information about a specific course.
		 * @invocation Typically called when detailed course information is needed for display or processing.
		 */
		public static function fetchCourseById (PearDatabase $adb, $courseId, $instanceCode = null, $targetAdb, $userId) {
			return !empty ($courseId) ? CourseManager::getInstance ($adb)->fetchCourse ($courseId, $instanceCode, $targetAdb, $userId) : null;
		}

		/**fetchCoursesByCategory
		 * Retrieves courses from the database and organizes them by category.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @return Course[]|null Returns an associative array of courses grouped by category ID or null if no courses are found.
		 * @usage Used to obtain courses grouped by their categories for display or filtering.
		 * @invocation Typically called when courses need to be displayed or filtered by category.
		 */
		public static function fetchCoursesByCategory (PearDatabase $adb) {
			$courses = CourseManager::getInstance ($adb)->fetchCourses ();
			if (empty ($courses)) {
				return null;
			}

			$coursesData = array ();
			foreach ($courses as $course) {
				$categoryId                    = $course->getCategoryId ();
				$coursesData [ $categoryId ][] = $course;
			}
			return $coursesData;
		}

		/**fetchCoursesByTargetAudience
		 * Retrieves courses from the database and organizes them by target audience.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param string|null $instanceCode Optional instance code for additional filtering.
		 * @param PearDatabase $adbTarget Target database connection for fetching course details.
		 * @param integer $userId ID of the user requesting the courses.
		 * @return Course[]|null Returns an associative array of courses grouped by category and series or null if no courses are found.
		 * @throws Exception Throws an exception if courses cannot be retrieved.
		 * @usage Used to obtain courses grouped by their target audience for display or filtering.
		 * @invocation Typically called when courses need to be displayed or filtered by target audience.
		 */
		public static function fetchCoursesByTargetAudience (PearDatabase $adb, $instanceCode = null, $adbTarget, $userId) {
			$objCurseManager = CourseManager::getInstance ($adb);
			$categories      = $objCurseManager->fetchCategories (true);
			$courses         = $objCurseManager->fetchCourses ($instanceCode, $adbTarget, $userId);
			if (empty($courses)) {
				return null;
			}
			$coursesData['category'] = array();
			foreach ($categories as $category) {
				$theCategory = null;
				$series = array();
				foreach ($courses as $course) {
					if (($course->getCategory ()->getId () == $category->getId()) && ($course->getSerie ()->getStatus () == 'ENABLED')) {
						$series[ $course->getSerie ()->getName () ][] = $course;
							
							if (!in_array($category, $coursesData['category'])) {
								$coursesData['category'][] = $category;
							$theCategory               = $category->getName ();
							}
						}
					}
				if (!empty ($theCategory)) {
					$coursesData[ $theCategory ] = $series;
				}
				}
			return $coursesData;
			}

		/**fetchLesson
		 * Retrieves lesson details and checks if the course associated with the lesson is paid.
		 *
		 * @param PearDatabase $masterAdb Database connection to execute the query.
		 * @param integer $lessonId ID of the lesson to retrieve.
		 * @param string $instanceCode Code of the instance for filtering.
		 * @param PearDatabase $targetAdb Target database connection for fetching lesson details.
		 * @param integer $userId ID of the user requesting the lesson details.
		 * @param integer $courseId ID of the course associated with the lesson.
		 * @return array Returns an array containing the payment status and lesson details.
		 * @throws Exception Throws an exception if the lesson cannot be retrieved.
		 * @usage Used to obtain lesson details and payment status for display or processing.
		 * @invocation Typically called when detailed lesson information and payment status are needed.
		 */
		public static function fetchLesson (PearDatabase $masterAdb, $lessonId, $instanceCode, $targetAdb, $userId, $courseId) {
			return array (
				'isPaidCourse' => self::isPaidCourse ($masterAdb, $lessonId, $instanceCode, $courseId),
				'course'       => CourseManager::getInstance ($masterAdb)->fetchLesson ($lessonId, $targetAdb, $userId),
			);
		}
		
		/**fetchLessonExercises
		 * Retrieves exercises associated with a specific lesson from the database.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $lessonId ID of the lesson for which to retrieve exercises.
		 * @param integer $exerciseId ID of the specific exercise to retrieve.
		 * @return LessonExercises[]|null Returns an array of LessonExercises objects or null if no exercises are found.
		 * @usage Used to obtain exercises for a lesson, typically for display or processing.
		 * @invocation Typically called when exercises associated with a lesson are needed.
		 */
		public static function fetchLessonExercises($adb, $lessonId, $exerciseId) {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$result = $masterAdb->pquery (
				'SELECT * FROM vtiger_lessons2exercises WHERE lessonid=? AND lesson2exercisesid=?',
				array ($lessonId, $exerciseId)
			);
			if ($masterAdb->num_rows($result) > 0) {
				$exercises = array();
				while ($row = $masterAdb->fetchByAssoc($result, -1, false)) {
					$exercises[] = LessonExercises::getInstance()
						->setDescription($row['exercises_description'])
						->setExercisesResources(self::fetchResources($masterAdb, $lessonId, $row['lesson2exercisesid']))
						->setHasTest($row['hastest'])
						->setId($row['lesson2exercisesid'])
						->setLessonId($row['lessonid'])
						->setName($row['exercises_name'])
						->setPassingScore($row['passing_score']);
				}
			}

			DatabaseUtils::closeResult($result);
			$result = null;
			return (isset($exercises)) ? $exercises : null;
		}
		
		/**fetchResource
		 * Retrieves a specific course resource from the database.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $resourceId ID of the resource to retrieve.
		 * @return CourseResource|null Returns a CourseResource object with the specified ID or null if not found.
		 * @usage Used to obtain detailed information about a specific course resource.
		 * @invocation Typically called when detailed resource information is needed for display or processing.
		 */
		public static function fetchResource (PearDatabase $adb, $resourceId) {
			return CourseManager::getInstance ($adb)->fetchResource ($resourceId);
		}
		
		/**fetchTest
		 * Retrieves a test associated with a specific lesson from the database.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $lessonId ID of the lesson for which to retrieve the test.
		 * @return CourseTest|null Returns a CourseTest object with the specified lesson ID or null if not found.
		 * @throws Exception Throws an exception if the test cannot be retrieved.
		 * @usage Used to obtain detailed information about a test for a lesson.
		 * @invocation Typically called when detailed test information is needed for display or processing.
		 */
		public static function fetchTest (PearDatabase $adb, $lessonId) {
			return CourseManager::getInstance ($adb)->fetchTest ($lessonId);
		}

		/** getTestResults
		 * Retrieves test results for a specific lesson, course, and user from the database.
		 *
		 * @param PearDatabase $masterAdb Master database connection to execute the query.
		 * @param integer $courseId ID of the course for which to retrieve test results.
		 * @param integer $lessonId ID of the lesson for which to retrieve test results.
		 * @param integer $userId ID of the user for whom to retrieve test results.
		 * @return array|null Returns an array of test results or null if no results are found.
		 * @usage Used to obtain detailed test results for a lesson, typically for display or analysis.
		 * @invocation Typically called when detailed test results are needed for a lesson.
		 */
		public static function getTestResults ($masterAdb, $courseId, $lessonId, $userId) {
			if (empty($courseId) || empty($lessonId) || empty($userId)) {
				return null;
			}
			$masterDbName = $masterAdb -> dbName;
			//20250207-GGC Para tener nombre de base de datos hija 
			if (empty($platInstancia)) {
				$childDbase = $masterDbName;
			} else{
				$childDbase = "pg_crm_" . $platInstancia;
			}
			//
			$result = $masterAdb->pquery (
				'SELECT
				    eu.status AS test_status,
				    e.*,
				    an.feedback,
				    an.statement AS answer,
				    q.statement
				FROM
				    ' . $childDbase . '.vtiger_lesson_evaluated2user eu
				INNER JOIN ' . $childDbase . '.vtiger_lesson_test_results e ON eu.evaluated2userid = e.evaluated2userid
				INNER JOIN vtiger_coursetestanswers an ON an.answerid = e.answerid
				INNER JOIN vtiger_coursetestquestions q ON q.questionid = e.questionid
				WHERE
				    eu.courseid=? AND
				    eu.lessonid=? AND
				    eu.userid=?',
				array ($courseId, $lessonId, $userId)
			);
			if ($masterAdb->num_rows ($result) > 0) {
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$testResults [] = $row;
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($testResults)) ? $testResults: null;
		}

		/**isPaidInstance
		 * Checks if a course instance is paid based on the course ID and instance code.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $courseId ID of the course to check.
		 * @param string $instanceCode Code of the instance to verify payment.
		 * @return boolean Returns true if the course instance is paid, false otherwise.
		 * @throws Exception Throws an exception if the query fails.
		 * @usage Used to determine if access to a course instance requires payment.
		 * @invocation Typically called when verifying payment status for a course instance.
		 */
		public static function isPaidInstance ($adb, $courseId, $instanceCode) {
			/*El curso se paga por usuario. Por tanto se necesita el user_name del usuario autenticado*/
			$userName = $_SESSION[authenticated_user_name];
		
			$masterAdb    = AdbManager::getInstance ()->getMasterAdb ();
			$payment = $masterAdb->run_query_allrecords ("SELECT * FROM vtiger_courses_paid WHERE courseid={$courseId} AND code='{$instanceCode}' AND user_name ='{$userName}'");
			return (count ($payment)) ? true : false;
		}

		/**function sanitizeString
		 * Sanitizes a string by removing special characters and replacing spaces with underscores.
		 *
		 * @param string $string The input string to sanitize.
		 * @return string Returns the sanitized string.
		 * @usage Used to prepare strings for safe use in file names or URLs.
		 * @invocation Typically called when a string needs to be sanitized for file or URL usage.
		 */
		public static function sanitizeString ($string) {
			$string = trim($string);
			$string = str_replace (
				array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$string
			);
			$string = str_replace (
				array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$string
			);
			$string = str_replace (
				array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$string
			);
			$string = str_replace (
				array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$string
			);
			$string = str_replace (
				array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$string
			);
			$string = str_replace (
				array('ñ', 'Ñ', 'ç', 'Ç'),
				array('n', 'N', 'c', 'C'),
				$string
			);
			$string = str_replace (
				array( '·', '$', '%', '&', '/', '(', ')', '?', "'", '¡', '¿', '[', '^', '<code>', ']', '+', '}', '{', '¨', '´', '>', '< ', ';', ',', ':', '.'),
				'',
				$string
			);
			$string = str_replace (' ', '_', $string);
			return strtolower ($string);
		}

		/**getImageCourse
		 * Processes and resizes an uploaded image for a course, returning it as a base64-encoded string.
		 *
		 * @param integer $uploadMax Maximum allowed file size for the upload.
		 * @return string|null Returns the base64-encoded image data or null if no valid image is uploaded.
		 * @throws Exception Throws an exception if the file extension is not allowed or the file size exceeds the limit.
		 * @usage Used to handle image uploads for courses, ensuring they are properly formatted and resized.
		 * @invocation Typically called when an image is uploaded for a course.
		 */
		public static function getImageCourse ($uploadMax) {
			if(!isset ($_FILES['imagePhoto'])) {
				return null;
			} else if (empty ($_FILES ['imagePhoto']['name'])) {
				return null;
			}

			$fileSize = $_FILES ['imagePhoto']['size'];
			$fileTmp  = $_FILES ['imagePhoto']['tmp_name'];
			$fileExt  = strtolower (end (explode ('.',$_FILES ['imagePhoto']['name'])));

			if(!in_array($fileExt, self::IMAGEN_TYPE)) {
				throw new Exception (FolderException::ERROR_EXTENSION_NO_ALLOWED);
			}
			if($fileSize > $uploadMax) {
				throw new Exception(FolderException::ERROR_FILE_TOO_BIG);
			}

			$idPhoto = rand ();
			$fileExt = '.' . $fileExt;

			move_uploaded_file ($fileTmp,'Image/Source_' . $idPhoto . $fileExt);

			$config                   = array();
			$config ['imageLibrary']  = 'gd2';
			$config ['sourceImage']   = 'Image/Source_' . $idPhoto . $fileExt;
			$config ['createThumb']   = false;
			$config ['maintainRatio'] = true;
			$config ['width']         = self::IMAGEN_WIDTH;
			$config ['height']        = self::IMAGEN_HEIGHT;

			$imagLibrary = new ImageUtils ($config);

			$resizeStatus = $imagLibrary->resize();
			if ($resizeStatus) {
				$data = file_get_contents('Image/Source_' . $idPhoto . $fileExt);
				$data = base64_encode ($data);
				unlink ('Image/Source_' . $idPhoto . $fileExt);
			}
			return (isset($data)) ? $data : null;
		}
		
		/**saveCourse
		 * Saves a course object to the database.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param Course $course The course object to save.
		 * @throws Exception Throws an exception if the course cannot be saved.
		 * @usage Used to save or update course information in the database.
		 * @invocation Typically called when a course needs to be persisted or updated in the database.
		 */
		public static function saveCourse (PearDatabase $adb, $course) {
			CourseManager::getInstance ($adb)->saveCourse ($course);
		}
		
		/**saveExercisesAttachment
		 * Saves an attachment associated with a specific exercise for a user in the database.
		 *
		 * @param PearDatabase $masterAdb Master database connection to execute the query.
		 * @param PearDatabase $adb Child database connection to execute the query.
		 * @param integer $exercisesId ID of the exercise to which the attachment belongs.
		 * @param integer $attachmentId ID of the attachment to save.
		 * @param integer $userId ID of the user submitting the attachment.
		 * @return void
		 * @throws Exception Throws an exception if the attachment cannot be saved.
		 * @usage Used to store attachments related to exercises, typically when a user submits an exercise.
		 * @invocation Typically called when an attachment needs to be saved for an exercise.
		 */
		public static function saveExercisesAttachment ($masterAdb, $adb, $exercisesId, $attachmentId, $userId) {
			require_once('modules/Courses/lib/ExerciseTracker.php');
			ExerciseTracker::saveExercisesAttachment($masterAdb, $adb, $exercisesId, $attachmentId, $userId);
		}
		
		/**saveLessonEvaluated
		 * Saves the evaluation results of a lesson for a specific user in the database.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param array $questions Array containing evaluated questions and their results.
		 * @param integer $courseId ID of the course associated with the lesson.
		 * @param integer $lessonId ID of the lesson being evaluated.
		 * @param integer $userId ID of the user whose evaluation is being saved.
		 * @return void
		 * @usage Used to store the evaluation results of a lesson after a user completes a test.
		 * @invocation Typically called when evaluation results need to be saved for a lesson.
		 */
		public static function saveLessonEvaluated ($adb, $questions, $courseId, $lessonId, $userId) {
			if(!is_array ($questions) || empty ($questions['evaluated'])) {
				return;
			}
			$questionsResults = $questions['evaluated'];
			$evaluationStatus = (!empty($questions['evaluationStatus'])) ? $questions['evaluationStatus'] : 'TEST_NOT_PASSED';
			$lessonStatus = (!empty($questions['lessonStatus'])) ? $questions['lessonStatus'] : 'LESSON_ASSESSED_BUT_NOT_PASSED';
			
			$result = $adb->pquery(
				'SELECT evaluated2userid, status FROM vtiger_lesson_evaluated2user WHERE courseid=? AND lessonid=? AND userid=? ORDER BY evaluated2userid DESC LIMIT 1',
				array($courseId, $lessonId, $userId)
			);
			
			if ($adb->num_rows($result) > 0) {
				$evaluatedToUserid = $adb->query_result($result, 0, 'evaluated2userid');
				$evaluatedStatus = $adb->query_result($result, 0, 'status');
				
				
				// Si ya existe una evaluación aprobada y el nuevo estado no es LESSON_TEST_PASSED,
				// no guardamos ni eliminamos nada
				if ($evaluatedStatus == 'TEST_PASSED' && $lessonStatus !== 'LESSON_TEST_PASSED') {
					DatabaseUtils::closeResult($result);
					return;
				}
				
				// Actualizamos el estado de la evaluación
				$adb->pquery('UPDATE vtiger_lesson_evaluated2user SET status=? WHERE evaluated2userid=?', array($evaluationStatus, $evaluatedToUserid));
				
				// Eliminamos los resultados anteriores solo si vamos a guardar nuevos
				$adb->pquery('DELETE FROM vtiger_lesson_test_results WHERE evaluated2userid=?', array($evaluatedToUserid));
			} else {
				// Primera evaluación, siempre se guarda
				$today = date('Y-m-d H:i:s', time());
				$result = $adb->pquery('INSERT INTO vtiger_lesson_evaluated2user (courseid, lessonid, userid, status, dt_created) VALUES (?, ?, ?, ?, ?)',
				array($courseId, $lessonId, $userId, $evaluationStatus, $today)
				//$result = $adb->pquery('INSERT INTO vtiger_lesson_evaluated2user (courseid, lessonid, userid, status) //VALUES (?, ?, ?, ?)',
					//array($courseId, $lessonId, $userId, $evaluationStatus)
				);
				$evaluatedToUserid = $adb->getLastInsertID('vtiger_lesson_evaluated2user');
			}
			DatabaseUtils::closeResult($result);
			$result = null;
			
			// Guardamos los resultados de la evaluación
			foreach ($questionsResults as $answerId => $result) {
				$adb->pquery(
					'INSERT INTO vtiger_lesson_test_results (evaluated2userid, questionid, answerid, status) VALUES (?, ?, ?, ?)',
					array($evaluatedToUserid, $result['id'], $answerId, $result['evaluated'])
				);
			}
		}
		
		/**savePaidCourse
		 * Records a payment for a course in the database.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $courseId ID of the course for which the payment is made.
		 * @param string $instanceCode Code of the instance associated with the payment.
		 * @param integer $paymentId ID of the payment transaction.
		 * @param integer $userId ID of the user making the payment.
		 * @return void
		 * @usage Used to log payments for courses, ensuring that payment details are stored correctly.
		 * @invocation Typically called when a payment is made for a course.
		 */
		public static function savePaidCourse (PearDatabase $masterAdb, $courseId, $instanceCode, $paymentId, $userId, $userName, $adb) {
			if (empty($userName) || $userName === null) {
				$userName = $_SESSION[authenticated_user_email];
				$course_userId = $_SESSION[authenticated_user_id];
			} else {
				/*Obtener el userid del usuario que hará el curso a partir de su userName*/
				$result = $adb->pquery ('SELECT id as uid FROM vtiger_users WHERE user_name = ?', array ($userName));
				$course_userId = $adb->query_result($result, 0,'uid');
			}
			$data = array (
				'courseid'  => $courseId,
				'code'      => $instanceCode,
				'paymentid' => $paymentId,
				'paidon'    => date ('Y-m-d h:i:s', time()),
				'paidby'    => $userId,
				'userid'    => $course_userId,
				'user_name' => $userName
			);
			CourseManager::getInstance ($masterAdb)->savePaidCourse ($data);
		}

		/**setSeenCourse
		 * Marks a course as seen for a specific user by recording the current timestamp.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $courseId ID of the course being marked as seen.
		 * @param integer $userId ID of the user who has seen the course.
		 * @return integer Returns the result of the database operation.
		 * @throws Exception Throws an exception if the operation fails.
		 * @usage Used to track when a user has accessed a course.
		 * @invocation Typically called when a course is accessed by a user to update the seen status.
		 */
		public static function setSeenCourse ($adb, $courseId, $userId) {
			$today = date ('Y-m-d h:i:s', time());
			return CourseManager::getInstance ($adb)->setSeenCourse ($courseId, $userId, $today);
		}

		/**setSeenLesson
		 * Marks a lesson as seen for a specific user by recording the current timestamp.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $courseId ID of the course associated with the lesson.
		 * @param integer $lessonId ID of the lesson being marked as seen.
		 * @param integer $userId ID of the user who has seen the lesson.
		 * @return integer Returns the result of the database operation.
		 * @throws Exception Throws an exception if the operation fails.
		 * @usage Used to track when a user has accessed a lesson.
		 * @invocation Typically called when a lesson is accessed by a user to update the seen status.
		 */
		public static function setSeenLesson ($adb, $courseId, $lessonId, $userId) {
			$today = date ('Y-m-d h:i:s', time());
			return CourseManager::getInstance ($adb)->setSeenLesson ($courseId, $lessonId, $userId, $today);
		}
		
		/**saveCategory
		 * Saves or updates a course category in the database.
		 *
		 * @param PearDatabase $masterAdb Master database connection to execute the query.
		 * @param CourseCategory $category The course category object to save or update.
		 * @throws Exception Throws an exception if the category is not a valid CourseCategory instance.
		 * @usage Used to persist changes to course categories, either by creating new ones or updating existing ones.
		 * @invocation Typically called when a course category needs to be saved or updated in the database.
		 */
		public static function saveCategory ($masterAdb, $category) {
			if (empty($category) || ! $category instanceof CourseCategory) {
				throw new Exception ('Error en instancia CourseCategory!');
			}
			$masterAdb = AdbManager::getInstance()->getMasterAdb();
			if (empty ($category->getId ())) {
				$masterAdb->pquery (
					'INSERT INTO vtiger_coursecategories (categoryname, status) VALUES (?, ?)',
					array ($category->getName (), $category->getStatus ())
				);
			} else {
				$masterAdb->pquery (
					'UPDATE vtiger_coursecategories SET categoryname=?, status=? WHERE categoryid=?',
					array ($category->getName (), $category->getStatus (), $category->getId ())
				);
			}
		}
		
		/**saveSerie
		 * Saves or updates a course series in the database.
		 *
		 * @param PearDatabase $masterAdb Master database connection to execute the query.
		 * @param CourseSerie $serie The course series object to save or update.
		 * @throws Exception Throws an exception if the series is not a valid CourseSerie instance.
		 * @usage Used to persist changes to course series, either by creating new ones or updating existing ones.
		 * @invocation Typically called when a course series needs to be saved or updated in the database.
		 */
		public static function saveSerie ($masterAdb, $serie) {
			if (empty($serie) || ! $serie instanceof CourseSerie) {
				throw new Exception ('Error en instancia CourseSerie!');
			}
			$masterAdb = AdbManager::getInstance()->getMasterAdb();
			if (empty ($serie->getId ())) {
				$masterAdb->pquery (
					'INSERT INTO vtiger_course_serie (seriename, status) VALUES (?, ?)',
					array ($serie->getName (), $serie->getStatus ())
				);
			} else {
				$masterAdb->pquery (
					'UPDATE vtiger_course_serie SET seriename=?, status=? WHERE serieid=?',
					array ($serie->getName (), $serie->getStatus (), $serie->getId ())
				);
			}
		}
		
		/**setGoodbyeCourse
		 * Updates the status of a course based on the progress of its lessons for a specific user.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $courseId ID of the course whose status is being updated.
		 * @param integer $userId ID of the user whose course progress is being evaluated.
		 * @return void
		 * @usage Used to update the course status for a user, typically when a lesson is completed.
		 * @invocation Typically called when a lesson is completed to update the course status for the user.
		 */
		public static function setGoodbyeCourse($adb, $courseId, $userId) {
			// Obtener total de lecciones del curso
			$masterAdb = AdbManager::getInstance()->getMasterAdb();
			$result = $masterAdb->pquery('SELECT COUNT(*) as total FROM vtiger_courselessons WHERE courseid = ?',
			array($courseId)
			);
			$totalLessons = $masterAdb->query_result($result, 0, 'total');
			// Obtener número de lecciones completadas
			$result = $adb->pquery(
				'SELECT COUNT(DISTINCT le.lessonid) as completed 
				FROM vtiger_lesson_evaluated2user le 
				WHERE le.courseid = ? AND le.userid = ? AND le.status = ?',
				array($courseId, $userId, 'LESSON_PASSED')
			);
			$completedLessons = $adb->query_result($result, 0, 'completed');

			// Determinar nuevo estado
			$newStatus = 'NOT_STARTED';
			if ($completedLessons > 0) {
				$newStatus = ($completedLessons == $totalLessons) ? 'MADE' : 'IN_PROGRESS';
			}

			// Actualizar estado del curso
			$result = $adb->pquery('SELECT MAX(course2userid) as maxcourse_user FROM vtiger_courses2user WHERE courseid = ? AND userid = ?', array($courseId, $userId));
			$maxcourse2user = $adb->query_result($result, 0, 'maxcourse_user');
			
			$today = date ('Y-m-d H:i:s', time());
			$adb->pquery(
				'UPDATE vtiger_courses2user 
				SET status = ? , end_date = ?
				WHERE course2userid = ?', array($newStatus,$today, $maxcourse2user)
			);
		}

		/**updateImageCourse
		 * Updates the image associated with a course in the database.
		 *
		 * @param PearDatabase $masterAdb Master database connection to execute the query.
		 * @param integer $courseId ID of the course whose image is being updated.
		 * @param string $typeImage Type of the image being updated.
		 * @param string $image Base64-encoded string of the new image.
		 * @throws Exception Throws an exception if the input parameters are invalid.
		 * @usage Used to change the image of a course, typically when a course image is updated.
		 * @invocation Typically called when a course image needs to be updated in the database.
		 */
		public static function updateImageCourse ($masterAdb, $courseId, $typeImage, $image) {
			if (!is_numeric ($courseId) || empty($typeImage) || empty($image)) {
				throw new Exception ('Imposible actualizar, Error en datos!');
			}
			
			$masterAdb->pquery (
				'UPDATE vtiger_courses SET imagecourse=?, imagetype=? WHERE courseid=?',
				array ($image, $typeImage, $courseId)
			);
		}
		
		/**fetchCourses
		 * Retrieves a list of courses from the database, including category and series information.
		 *
		 * @param PearDatabase $masterAdb Master database connection to execute the query.
		 * @param integer|null $userId Optional ID of the user for whom the courses are being fetched.
		 * @return array Returns an array of courses with associated category and series information.
		 * @usage Used to obtain a comprehensive list of courses for display or processing.
		 * @invocation Typically called when a list of courses is needed, with optional user-specific filtering.
		 */
		public static function fetchCourses($masterAdb, $userId = null) {
			$query = 'SELECT c.*, cc.categoryname as category_name, cs.seriename as serie_name 
					  FROM vtiger_courses c 
					  LEFT JOIN vtiger_coursecategories cc ON c.categoryid = cc.categoryid 
					  LEFT JOIN vtiger_course_serie cs ON c.serieid = cs.serieid';
			
			$result = $masterAdb->pquery($query, array());
			
			$courses = array();
			while ($row = $masterAdb->fetchByAssoc($result)) {
				$courses[] = $row;
			}
			return $courses;
		}
		
		/**setGoodbyeLesson
		 * Updates the status of a lesson for a specific user, marking it as completed or updating its progress.
		 *
		 * @param PearDatabase $adb Database connection to execute the query.
		 * @param integer $courseId ID of the course associated with the lesson.
		 * @param integer $lessonId ID of the lesson whose status is being updated.
		 * @param integer $userId ID of the user whose lesson status is being updated.
		 * @param string $status New status to set for the lesson.
		 * @return void
		 * @throws Exception Throws an exception if the status update fails.
		 * @usage Used to update the lesson status for a user, typically when a lesson is completed or its status changes.
		 * @invocation Typically called when a lesson is completed or its status needs to be updated for a user.
		 */
		public static function setGoodbyeLesson($adb, $courseId, $lessonId, $userId, $status) {
			// Primero verificar si existe el registro
			$checkResult = $adb->pquery(
				'SELECT lessonid FROM vtiger_lessons2user WHERE lessonid=? AND userid=?',
				array($lessonId, $userId)
			);
			
			$today = date ('Y-m-d H:i:s', time());			
			if ($adb->num_rows($checkResult) > 0) {
				// Si existe, actualizar el registro mas reciente de este usuario
				$result = $adb->pquery('SELECT MAX(lesson2userid) as maxlesson2userid FROM vtiger_lessons2user WHERE lessonid=? AND userid=?', array($lessonId, $userId));
				$maxlesson2userid = $adb->query_result($result, 0, 'maxlesson2userid');
				$result = $adb->pquery(
					'UPDATE vtiger_lessons2user SET end_date=?, status=? WHERE lesson2userid=?',
					array($today, $status, $maxlesson2userid));
			} else {
				// Si no existe, insertar
				$result = $adb->pquery(
					'INSERT INTO vtiger_lessons2user (lessonid, userid, courseid, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)',
					array($lessonId, $userId, $courseId, $today, $today, $status)
				);
			}
			
			if (!$result) {
				throw new Exception('Error al actualizar el estado de la lección');
			}
			
			// Actualizar estado del curso cuando se completa una lección
			if ($status === 'LESSON_PASSED') {
				self::setGoodbyeCourse($adb, $courseId, $userId);
			}
		}
	}
