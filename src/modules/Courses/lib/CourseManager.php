<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/Courses/lib/Course.php');
	require_once ('modules/Courses/lib/CourseCategory.php');
	require_once ('modules/Courses/lib/CourseSerie.php');
	require_once ('modules/Courses/lib/CoursesStatistics.php');
	require_once ('modules/Courses/lib/LessonsStatistics.php');
	require_once ('modules/Courses/lib/TrackCourseProgress.php');
	require_once ('modules/Courses/lib/TrackLessonProgress.php');

	class CourseManager {

		/** @var CourseManager[] */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/** @var PearDatabase  */
		private $masterAdb;

		public function __construct (PearDatabase $adb) {
			$this->adb       = $adb;
			$pos             = strpos ($this->adb->dbName, 'madre');
			$this->masterAdb = ($pos !== false) ? $adb : AdbManager::getInstance ()->getMasterAdb ();
		}
		/**
		 * calculateCourseStatus
		 * 
		 * Calcula el estado de un curso basado en el progreso del usuario.
		 * 
		 * @param integer $courseId ID del curso que se evalúa.
		 * @param integer $userId ID del usuario cuyo progreso se evalúa.
		 * 
		 * @return string Retorna el estado del curso: 'NOT_STARTED', 'IN_PROGRESS', o 'MADE'.
		 * 
		 * @throws Exception Lanza una excepción si ocurre un error durante el cálculo.
		 * 
		 * @usage Se utiliza para determinar el estado actual de un curso para un usuario específico.
		 * 
		 * @author Staff de Time Management
		 * @copyright Todos los derechos reservados. Time Management
		 * @since Versión: 1.0
		 * @version documentación: 1.0 Revisión: 10-04-2025
		 */
		private function calculateCourseStatus ($courseId, $userId) {
			if (empty($courseId) || empty($userId)) {
				return 'NOT_STARTED';
			}
			try {
				// 1. Verificar si existe registro en courses2user
				$result = $this->adb->pquery(
					'SELECT COUNT(*) as count FROM vtiger_courses2user WHERE courseid = ? AND userid = ?',
					array($courseId, $userId)
				);
				$cantidad = $this->adb->query_result($result, 0, 'count');
				if ($cantidad == 0) {
					return 'NOT_STARTED';
				}

				// 2. Verificar si existen registros en lessons2user
				$result = $this->adb->pquery(
					'SELECT COUNT(*) as count FROM vtiger_lessons2user WHERE courseid = ? AND userid = ?',
					array($courseId, $userId)
				);
				$cantidad = $this->adb->query_result($result, 0, 'count');
				if ($cantidad == 0) {
					return 'NOT_STARTED';
				}

				// 3. Obtener total de lecciones del curso
				$result = $this->masterAdb->pquery(
					'SELECT COUNT(*) as total FROM vtiger_courselessons WHERE courseid = ?',
					array($courseId)
				);
				$totalLessons = $this->masterAdb->query_result($result, 0, 'total');
				if ($totalLessons == 0) {
					// Si el curso no tiene lecciones, se considera no iniciado
					return 'NOT_STARTED';
				}

				// 4. Verificar si todas las lecciones están en estado LESSON_PASSED
				$result = $this->masterAdb->pquery(
					'SELECT COUNT(DISTINCT l.lessonid) as completed
					FROM vtiger_courselessons l 
					INNER JOIN ' . $this->adb->dbName . '.vtiger_lessons2user lu ON lu.lessonid = l.lessonid 
					WHERE l.courseid = ? AND lu.userid = ? AND lu.status = ?',
					array($courseId, $userId, 'LESSON_PASSED')
				);
				//$completedLessons = $this->adb->fetchByAssoc($result)['completed'];
				$completedLessons = $this->adb->query_result($result, 0, 'completed');

				// 5. Determinar estado según las reglas simplificadas
				if ($completedLessons == $totalLessons) {
					return 'MADE';
				}
				return 'IN_PROGRESS';

			} catch (Exception $e) {
				// Registrar el error en el log de PHP con información detallada
				echo $e ."<br>";
				$mensajelog = "[CourseManager::calculateCourseStatus] Error al calcular estado del curso. CourseID:".	$courseId. ", UserID: " .$userId . ", Database: " .$this->adb->dbName. ", Detalles: ". $e->getMessage(). ", Trace: ". getTraceAsString();
				echo "ERROR:  " .$mensajelog;
				error_log(sprintf($mensajelog));
				// En caso de error, asumimos el estado más seguro
				return 'NOT_STARTED';
			} finally {
				// Asegurar que los recursos se liberan
				if (isset($result)) {
					DatabaseUtils::closeResult($result);
				}
			}
		}


		/**
		 * @param integer $courseId
		 * @param integer $lessonToPay
		 *
		 * @return array|null
		 * @throws Exception
		 * @param object $adb Database connection for user progress
		 * @param integer $userId ID of the user
		 * @return array|null Array containing lessons and index, or null if no lessons found
		 * @throws Exception If database query fails		 */
		private function fetchLessons ($courseId, $lessonToPay, $adb, $userId) {
			if (empty ($courseId)) {
				return null;
			}

			$result = $this->masterAdb->pquery (
				'SELECT
       					cl.*,
       					cl.lessonid AS lesson_id,
	       				cl.hastest AS lesson_hastest,
       					le.*
					FROM vtiger_courselessons cl
					LEFT JOIN vtiger_lessons2exercises le ON le.lessonid = cl.lessonid
					WHERE cl.courseid=? ORDER BY cl.lessonid ASC',
				array ($courseId)
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				$lessons     = array ();
				$rowIndex    = 1;
				$lessonIndex = 0;
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					if (($lessonToPay) && (!$lessonIndex) && ($lessonToPay == $row ['lesson_id'])) {
						$lessonIndex = $rowIndex;
					}
					$exercise = null;
					$hasTest  = intval($row['lesson_hastest']);
					if (!empty ($row['lesson2exercisesid'])) {
						$exercise = LessonExercises::getInstance ()
							->setId ($row['lesson2exercisesid'])
							->setLessonId (intval ($row ['lessonid']))
							->setName ($row ['exercises_name'])
							->setDescription ($row ['exercises_description'])
							->setPassingScore (floatval ($row ['passing_score']))
							->setHasTest ($row ['hastest']);
					}
					$lessons [] = CourseLesson::getInstance ()
						->setId (intval ($row ['lesson_id']))
						->setCourseId (intval ($row ['courseid']))
						->setDescription ($row ['description'])
						->setHasTest ($hasTest)
						->setName ($row ['lessonname'])
						->setResources ($this->fetchResources (intval ($row ['lesson_id'])))
						->setStatus (intval ($row ['status']))
						->setTest (($hasTest) ? $this->fetchTest (intval ($row ['lesson_id'])) : null)
						->setTypeVideo ($row ['videotype'])
						->setVideoUrl ($row ['videourl'])
						->setLessonExercise ($exercise)
						->setUserLessonStatus($this->getUserLessonStatus ($adb, $courseId, $row['lesson_id'], $userId));
					$rowIndex++;
				}
			} else {
				$lessons = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return array ('lessons' => $lessons, 'index' => $lessonIndex);
		}

		/**
		 * @param array $row
		 *
		 * @return array|null
		 * @throws Exception
		 * @return array|null Array of LessonsStatistics objects, or null if no statistics found
		 * @throws Exception If database query fails		 */
		private function fetchLessonStatistics ($row, $userId) {
			if (empty($row)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT *  FROM vtiger_lessons_seen  WHERE courseid=? AND seenby=? ',
				array($row['courseid'], $row ['seenby'])
			);
			if ($this->adb->num_rows ($result) > 0) {
				$lessonSeen = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$lessonSeen [] = LessonsStatistics::getInstance ()
						->setCourseId ($row ['courseid'])
						->setLastTime ($row ['lasttime'])
						->setLessonSeen ($this->fetchLesson($row ['lessonid'], $this->adb, $userId, true))
						->setSeenDate ($row ['seenon'])
						->setUserId ($row ['seenby']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($lessonSeen)) ? $lessonSeen : null;
		}

		/**
		 * @param integer $lessonId
		 *
		 * @return CourseResource[]|null
		 * @return array|null Array of CourseResource objects, or null if no resources found
		 * @throws Exception If database query fails		 */
		private function fetchResources ($lessonId) {
			if (empty ($lessonId)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courseresources WHERE lessonid=?', array ($lessonId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$resources = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$resources [] = CourseResource::getInstance ()
						->setId (intval ($row ['resourceid']))
						->setExerciseId (intval ($row ['lesson2exercisesid']))
						->setHasExercise ((!empty ($row ['lesson2exercisesid'])) ? 'YES':'NO')
						->setLessonId (intval ($row ['lessonid']))
						->setName ($row ['resourcename'])
						->setType ($row ['resourcetype'])
						->setUrl ($row ['url']);
				}
			} else {
				$resources = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $resources;
		}

		/**
		 * Retrieves answers for a test question
		 * 
		 * @param integer $questionId ID of the question
		 * @return array|null Array of CourseTestAnswer objects, or null if no answers found
		 * @throws Exception If database query fails
		 */		
		 private function fetchTestAnswers ($questionId) {
			if (empty ($questionId)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_coursetestanswers WHERE questionid=?', array ($questionId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$answers = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$answers [] = CourseTestAnswer::getInstance ()
						->setId (intval ($row ['answerid']))
						->setCorrect ($row ['correct'] == 1)
						->setFeedback ($row ['feedback'])
						->setQuestionId (intval ($row ['questionid']))
						->setStatement ($row ['statement']);
				}
			} else {
				$answers = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $answers;
		}

		/**
		 * @param integer $lessonId
		 *
		 * @return CourseTestQuestion[]|null
		 * @throws Exception
		 */
		private function fetchTestQuestions ($lessonId) {
			if (empty ($lessonId)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_coursetestquestions WHERE lessonid=?  ORDER BY questionid ASC', array ($lessonId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$questions = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$questions [] = CourseTestQuestion::getInstance ()
						->setId (intval ($row ['questionid']))
						->setAnswers ($this->fetchTestAnswers (intval ($row ['questionid'])))
						->setStatement ($row ['statement'])
						->setTestId (intval ($row ['testid']))
						->setType ($row ['questiontype']);
				}
			} else {
				$questions = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $questions;
		}

		/**fillCourse
		 * Llena un objeto Course con los datos proporcionados.
		 * 
		 * @param array $courseData Datos del curso.
		 * @param string|null $instanceCode Código de instancia opcional.
		 * @param PearDatabase $adb Conexión a la base de datos.
		 * @param integer $userId ID del usuario.
		 * 
		 * @return Course Retorna una instancia de Course con los datos llenados.
		 * @throws Exception Lanza una excepción si ocurre un error durante el proceso.
		 */
		private function fillCourse ($courseData, $instanceCode = null, $adb, $userId) {
			/*En algunos casos el código de instancia viene con valor nulo pero se necesita. GGC 2025-04-13*/
			if ($instanceCode === null) {
				// Obtener el nombre de usuario a partir del userId
				$result = $adb->pquery(
					'SELECT user_name FROM vtiger_users WHERE id = ?',
					array($userId)
				);
				if ($adb->num_rows($result) > 0) {
					$username = $adb->query_result($result, 0, 'user_name');
					// Obtener el instancecode usando el nombre de usuario
					$result = $this->masterAdb->pquery(
						'SELECT instancecode FROM vtiger_instanceusers WHERE username = ?',
						array($username)
					);
					if ($this->masterAdb->num_rows($result) > 0) {
						$instanceCode = $this->masterAdb->query_result($result, 0, 'instancecode');
					}
				}
			}
			$lessonsValues = $this->fetchLessons (intval ($courseData ['courseid']), intval ($courseData ['lessontopay']), $adb, $userId);

			return Course::getInstance ()
				->setCategoryId (intval ($courseData ['categoryid']))
				->setCategory ($this->fetchCategoryById ($courseData ['categoryid']))
				->setDescription ($courseData ['description'])
				->setPaid ((!empty ($instanceCode)) ? ($this->isPaidCourse ($courseData ['courseid'], $instanceCode)) : false)
				->setId (intval ($courseData ['courseid']))
				->setImageCourse ($courseData ['imagecourse'])
				->setImageType ($courseData ['imagetype'])
				->setLessons ($lessonsValues ['lessons'])
				->setLessonIndex ($lessonsValues ['index'])
				->setLessonToPay ($courseData ['lessontopay'])
				->setLevel ($courseData ['level'])
				->setName ($courseData ['coursename'])
				->setPrice (floatval ($courseData ['price']))
				->setSeenBy  ((isset($courseData ['seenby'])) ? (intval ($courseData ['seenby'])) : null)
				->setSerie ($this->fetchSerieById ($courseData ['serieid']))
				->setStatus ($courseData ['status'])
				->setVideoCourse ($courseData ['videocurse'])
				->setVideoType ($courseData ['videotype'])
				->setForumName ($courseData ['forum_name'])
				->setForumUrl ($courseData ['forum_url'])
				->setUserCourseStatus ($this->getUserCourseStatus ($adb, $courseData ['courseid'], $userId));
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $courseId
		 * @param integer $lessonToPay
		 *
		 * @return string
		 * @throws Exception
		 */
		private function getUserCourseStatus ($adb, $courseId, $userId) {
			$result = $adb->pquery (
				'SELECT status FROM vtiger_courses2user WHERE courseid=? AND userid=? ORDER BY course2userid DESC LIMIT 1',
				array ($courseId, $userId)
			);
			$status = 'NOT_STARTED';
			if ($adb->num_rows ($result) > 0) {
				$row    = $adb->fetchByAssoc ($result, -1, false);
				$status = $row ['status'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $status;
		}
		
		private function getUserLessonStatus ($adb, $courseId, $lessonId, $userId) {
			$result = $adb->pquery (
				'SELECT status FROM vtiger_lessons2user WHERE courseid=? AND lessonid=? AND userid=? ORDER BY lesson2userid DESC LIMIT 1',
				array ($courseId, $lessonId, $userId)
			);
			$status = 'LESSON_NOT_VISITED';
			if ($adb->num_rows ($result) > 0) {
				$row    = $adb->fetchByAssoc ($result, -1, false);
				$status = $row ['status'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $status;
		}
		
		/**
		 * hasUserPaidForCourse
		 * 
		 * Determina si un usuario ha realizado el pago del curso.
		 * Este método consulta la tabla `vtiger_courses_paid` para verificar si el usuario ha pagado.
		 * 
		 * @param PearDatabase $masterAdb Conexión a la base de datos maestra.
		 * @param integer $courseId ID del curso.
		 * @param integer $userId ID del usuario.
		 * 
		 * @return integer Retorna 1 si el usuario ha pagado, 0 en caso contrario.
		 * 
		 * @author Gladys Granados
		 * @since 2025-04-06
		 */
		public static function hasUserPaidForCourse($masterAdb, $courseId, $user_name) {
			$query = "SELECT * FROM vtiger_courses_paid WHERE courseid = ? AND user_name = ?";
			$result = $masterAdb->pquery($query, array($courseId, $user_name));
			if ($masterAdb->num_rows($result) > 0) {
				$userHasPaid = 1;
			} else {
				$userHasPaid = 0;
			}
			return ($userHasPaid);
		}
		
		/**
		 * @param integer $courseId
		 * @param string $instanceCode
		 *
		 * @return boolean
		 */
		private function isPaidCourse ($courseId, $instanceCode) {
			/*El curso se paga por usuario. Por tanto se necesita el user_name autenticado 2025-04-13 GGC*/
			$userName = $_SESSION[authenticated_user_name];
			$payment = $this->masterAdb->run_query_allrecords ("SELECT * FROM vtiger_courses_paid WHERE courseid={$courseId} AND code='{$instanceCode}' AND user_name ='{$userName}'");
			
			if (count($payment) > 0) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * @param CourseResource $resource
		 */
		private function saveAttachment ($resource) {
			$type         = $resource->getType ();
			$fileContents = $resource->getFileContents ();
			if (($type != CourseResource::TYPE_ATTACHMENT) || (empty ($fileContents))) {
				return;
			}

			$resourcesFolderPath = CourseResource::getFolderPath ();
			if (!is_dir ($resourcesFolderPath)) {
				$oldumask = umask (0);
				mkdir ($resourcesFolderPath, 0777, true);
				umask ($oldumask);
			}
			$filePath = "{$resourcesFolderPath}/{$resource->getId ()}.bin";
			file_put_contents ($filePath, $resource->getFileContents ());
		}
		
		/**
		 * @param Course $course
		 */
		private function saveLessons ($course) {
			$courseId = $course->getId ();
			$lessons  = $course->getLessons ();
			if (!empty ($lessons)) {
				$processedLessonIds = array ();
				$lessonIndex        = 0;
				foreach ($lessons as $lesson) {
					$lessonId = $lesson->getId ();
					if (empty ($lessonId)) {
						$this->masterAdb->pquery (
							'INSERT INTO vtiger_courselessons (courseid, lessonname, description, videourl, videotype, hastest, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
							array ($courseId, $lesson->getName (), $lesson->getDescription (), $lesson->getVideoUrl (), $lesson->getTypeVideo (), $lesson->getHasTest (), $lesson->getStatus ())
						);
						$lesson->setId ($this->masterAdb->getLastInsertID ());
						$exercise = $lesson->getLessonExercise ();
						if (!empty ($exercise) && !empty ($exercise->getDescription ())) {
							$this->masterAdb->pquery (
								'INSERT INTO vtiger_lessons2exercises (lessonid, exercises_name, exercises_description, hastest, Passing_score) VALUES (?, ?, ?, ?, ?)',
								array($lesson->getId (), $exercise->getName (), $exercise->getDescription (), $exercise->getHasTest (), $exercise->getPassingScore ())
							);
							$lesson->getLessonExercise ()->setId ($this->masterAdb->getLastInsertID ());
						} else {
							$lesson->getLessonExercise ()->setId(null);
						}
					} else {
						$this->masterAdb->pquery (
							'UPDATE vtiger_courselessons SET lessonname=?, description=?, videourl=?, videotype=?, hastest=?, status=? WHERE lessonid=?',
							array ($lesson->getName (), $lesson->getDescription (), $lesson->getVideoUrl (), $lesson->getTypeVideo (), $lesson->getHasTest (), $lesson->getStatus (), $lessonId)
						);
						$exercise = $lesson->getLessonExercise ();
						if (!empty ($exercise) && !empty ($exercise->getDescription ())) {
							$result = $this->masterAdb->pquery (
								'SELECT lesson2exercisesid FROM vtiger_lessons2exercises WHERE lessonid=?',
								array ($lessonId)
							);
							if (!$this->masterAdb->num_rows ($result)) {
								$this->masterAdb->pquery (
									'INSERT INTO vtiger_lessons2exercises (lessonid, exercises_name, exercises_description, hastest, Passing_score) VALUES (?, ?, ?, ?, ?)',
									array($lessonId, $exercise->getName (), $exercise->getDescription (), $exercise->getHasTest (), $exercise->getPassingScore ())
								);
								$lesson->getLessonExercise ()->setId ($this->masterAdb->getLastInsertID ());
							} else {
								$exercise->setId ($this->masterAdb->query_result ($result, 0, 'lesson2exercisesid'));
								$this->masterAdb->pquery (
									'UPDATE vtiger_lessons2exercises SET exercises_name=?, exercises_description=?, hastest=?, Passing_score=? WHERE lesson2exercisesid=?',
									array ($exercise->getName (), $exercise->getDescription (), $exercise->getHasTest (), $exercise->getPassingScore (), $exercise->getId ())
								);
							}
						} else if (!empty($exercise->getId ())) {
							$this->masterAdb->pquery (
								'DELETE FROM vtiger_lessons2exercises WHERE lesson2exercisesid=?',
								array ($exercise->getId ())
							);
							$lesson->getLessonExercise ()->setId(null);
						} else {
							$lesson->getLessonExercise ()->setId(null);
						}
					}
					$this->saveResources ($lesson);
					$this->saveTest ($lesson);
					$processedLessonIds [] = $lesson->getId ();
					$lessonIndex++;
				}
				$questionMarks = str_repeat ('?, ', (count ($processedLessonIds) - 1)) . '?';
				$this->masterAdb->pquery ("DELETE FROM vtiger_coursetestanswers WHERE questionid IN (SELECT questionid FROM vtiger_coursetestquestions WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=? AND lessonid NOT IN ({$questionMarks})))", array_merge (array ($courseId), $processedLessonIds));
				$this->masterAdb->pquery ("DELETE FROM vtiger_coursetestquestions WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=? AND lessonid NOT IN ({$questionMarks}))", array_merge (array ($courseId), $processedLessonIds));
				$this->masterAdb->pquery ("DELETE FROM vtiger_coursetests WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=? AND lessonid NOT IN ({$questionMarks}))", array_merge (array ($courseId), $processedLessonIds));
				$this->masterAdb->pquery ("DELETE FROM vtiger_courseresources WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=? AND lessonid NOT IN ({$questionMarks}))", array_merge (array ($courseId), $processedLessonIds));
				$this->masterAdb->pquery ("DELETE FROM vtiger_courselessons WHERE courseid=? AND lessonid NOT IN ({$questionMarks})", array_merge (array ($courseId), $processedLessonIds));
			} else {
				$this->masterAdb->pquery ('DELETE FROM vtiger_coursetestanswers WHERE questionid IN (SELECT questionid FROM vtiger_coursetestquestions WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=?))', array ($courseId));
				$this->masterAdb->pquery ('DELETE FROM vtiger_coursetestquestions WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=?)', array ($courseId));
				$this->masterAdb->pquery ('DELETE FROM vtiger_coursetests WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=?)', array ($courseId));
				$this->masterAdb->pquery ('DELETE FROM vtiger_courseresources WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=?)', array ($courseId));
				$this->masterAdb->pquery ('DELETE FROM vtiger_courselessons WHERE courseid=?', array ($courseId));
			}
		}

		/**
		 * @param CourseLesson $lesson
		 */
		private function saveResources ($lesson) {
			$lessonId  = $lesson->getId ();
			$exerciseId = $lesson->getLessonExercise ()->getId ();
			$resources = $lesson->getResources ();
			if (!empty ($resources)) {
				$processedResourceIds = array ();
				foreach ($resources as $resource) {
					$resourceId = $resource->getId ();
					$exerciseId = ($resource->getHasExercise () == 'YES') ? $exerciseId : null;
					if (empty ($resourceId)) {
						$this->masterAdb->pquery (
							'INSERT INTO vtiger_courseresources (lessonid, resourcename, resourcetype, url, lesson2exercisesid) VALUES (?, ?, ?, ?, ?)',
							array ($lessonId, $resource->getName (), $resource->getType (), $resource->getUrl (), $exerciseId)
						);
						$resource->setId ($this->masterAdb->getLastInsertID ());
					} else {
						$this->masterAdb->pquery (
							'UPDATE vtiger_courseresources SET resourcename=?, resourcetype=?, url=?, lesson2exercisesid=? WHERE resourceid=?',
							array ($resource->getName (), $resource->getType (), $resource->getUrl (),$exerciseId,  $resourceId)
						);
					}
					$this->saveAttachment ($resource);
					$processedResourceIds [] = $resource->getId ();
				}
				$questionMarks = str_repeat ('?, ', (count ($processedResourceIds) - 1)) . '?';
				$this->masterAdb->pquery ("DELETE FROM vtiger_courseresources WHERE lessonid=? AND resourceid NOT IN ({$questionMarks})", array_merge (array ($lessonId), $processedResourceIds));
			} else {
				$this->masterAdb->pquery ('DELETE FROM vtiger_courseresources WHERE lessonid=?', array ($lessonId));
			}
		}

		/**
		 * @param CourseLesson $lesson
		 */
		private function saveTest ($lesson) {
			$lessonId = intval ($lesson->getId ());
			$test     = $lesson->getTest ();
			if (!empty ($test)) {
				$test->setLessonId ($lessonId);
				$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_coursetests WHERE lessonid=?', array ($lessonId));
				if ($this->masterAdb->num_rows ($result) == 0) {
					$this->masterAdb->pquery (
						'INSERT INTO vtiger_coursetests (lessonid, description, feedback, feedback_no_approved, minimumscore, totalquestionspertest) VALUES (?, ?, ?, ?, ?, ?)',
						array ($lessonId, $test->getDescription (), $test->getFeedback (), $test->getFeedbackNotApproved (), $test->getMinimumScore (), $test->getTotalQuestionsPerTest ())
					);
				} else {
					$this->masterAdb->pquery (
						'UPDATE vtiger_coursetests SET description=?, feedback=?, feedback_no_approved=?, minimumscore=?, totalquestionspertest=? WHERE lessonid=?',
						array ($test->getDescription (), $test->getFeedback (), $test->getFeedbackNotApproved (), $test->getMinimumScore (), $test->getTotalQuestionsPerTest (), $lessonId)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				$this->saveTestQuestions ($test);
			} else if ($lesson->getHasTest()) {
				$this->masterAdb->pquery ('DELETE FROM vtiger_courseresources WHERE lessonid=?', array ($lessonId));
			}
		}

		/**
		 * @param CourseTestQuestion $question
		 */
		private function saveTestAnswers ($question) {
			$questionId = $question->getId ();
			$answers    = $question->getAnswers ();
			if (!empty ($answers)) {
				$processedAnswerIds = array ();
				foreach ($answers as $answer) {
					$answerId = $answer->getId ();
					if (empty ($answerId)) {
						$this->masterAdb->pquery (
							'INSERT INTO vtiger_coursetestanswers (questionid, statement, correct, feedback) VALUES (?, ?, ?, ?)',
							array ($questionId, $answer->getStatement (), $answer->isCorrect (), $answer->getFeedback ())
						);
						$answer->setId ($this->masterAdb->getLastInsertID ());
					} else {
						$this->masterAdb->pquery (
							'UPDATE vtiger_coursetestanswers SET statement=?, correct=?, feedback=? WHERE answerid=?',
							array ($answer->getStatement (), $answer->isCorrect (), $answer->getFeedback (), $answerId)
						);
					}
					$processedAnswerIds [] = $answer->getId ();
				}
				$questionMarks = str_repeat ('?, ', (count ($processedAnswerIds) - 1)) . '?';
				$this->masterAdb->pquery ("DELETE FROM vtiger_coursetestanswers WHERE questionid=? AND answerid NOT IN ({$questionMarks})", array_merge (array ($questionId), $processedAnswerIds));
			} else {
				$this->masterAdb->pquery ('DELETE FROM vtiger_coursetestanswers WHERE questionid=?', array ($questionId));
			}
		}

		/**
		 * @param CourseTest $test
		 */
		private function saveTestQuestions ($test) {
			$lessonId  = $test->getLessonId ();
			$questions = $test->getQuestions ();
			if (!empty ($questions)) {
				$processedQuestionIds = array ();
				foreach ($questions as $question) {
					$questionId = $question->getId ();
					if (empty ($questionId)) {
						$this->masterAdb->pquery (
							'INSERT INTO vtiger_coursetestquestions (lessonid, statement, questiontype) VALUES (?, ?, ?)',
							array ($lessonId, $question->getStatement (), $question->getType ())
						);
						$question->setId ($this->masterAdb->getLastInsertID ());
					} else {
						$this->masterAdb->pquery (
							'UPDATE vtiger_coursetestquestions SET statement=?, questiontype=? WHERE questionid=?',
							array ($question->getStatement (), $question->getType (), $questionId)
						);
					}
					$this->saveTestAnswers ($question);
					$processedQuestionIds [] = $question->getId ();
				}
				$questionMarks = str_repeat ('?, ', (count ($processedQuestionIds) - 1)) . '?';
				$this->masterAdb->pquery ("DELETE FROM vtiger_coursetestanswers WHERE questionid IN (SELECT questionid FROM vtiger_coursetestquestions WHERE lessonid=? AND questionid NOT IN ({$questionMarks}))", array_merge (array ($lessonId), $processedQuestionIds));
				$this->masterAdb->pquery ("DELETE FROM vtiger_coursetestquestions WHERE lessonid=? AND questionid NOT IN ({$questionMarks})", array_merge (array ($lessonId), $processedQuestionIds));
			} else {
				$this->masterAdb->pquery ('DELETE FROM vtiger_coursetestanswers WHERE questionid IN (SELECT questionid FROM vtiger_coursetestquestions WHERE lessonid=?)', array ($lessonId));
				$this->masterAdb->pquery ('DELETE FROM vtiger_coursetestquestions WHERE lessonid=?', array ($lessonId));
			}
		}

		/**
		 * @param Course $course
		 *
		 * @throws CourseException
		 * @throws CourseLessonException
		 */
		private function validate ($course) {
			if ((empty ($course)) || (!($course instanceof Course))) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY);
			}

			$course->validate ();
			$this->validateCourseCategory ($course);
			$this->validateCourseName ($course);
			$this->validateLessons ($course->getLessons ());
		}

		/**
		 * @param Course $course
		 *
		 * @throws CourseException
		 */
		private function validateCourseCategory ($course) {
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_coursecategories WHERE categoryid=?', array ($course->getCategoryId ()));
			if ($this->masterAdb->num_rows ($result) == 0) {
				$e = new CourseException (CourseException::ERROR_COURSE_INVALID_CATEGORY);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param CourseLesson[] $lessons
		 *
		 * @throws CourseLessonException
		 */
		private function validateLessons ($lessons) {
			foreach ($lessons as $lesson) {
				$courseId   = $lesson->getCourseId ();
				$lessonId   = $lesson->getId ();
				$lessonName = $lesson->getName ();
				if (empty ($lessonId)) {
					$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courselessons WHERE courseid=? AND lessonname=?', array ($courseId, $lessonName));
				} else {
					$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courselessons WHERE courseid=? AND lessonid<>? AND lessonname=?', array ($courseId, $lessonId, $lessonName));
				}
				if ($this->masterAdb->num_rows ($result) > 0) {
					$e = new CourseLessonException (CourseLessonException::ERROR_COURSE_LESSON_DUPLICATE_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}
				//$this->validateResources ($lesson->getResources ());
			}
		}

		/**
		 * @param CourseResource[] $resources
		 *
		 * @throws CourseResourceException
		 */
		private function validateResources ($resources) {
			if (empty ($resources)) {
				return;
			}

			foreach ($resources as $resource) {
				$lessonId     = $resource->getLessonId ();
				$resourceId   = $resource->getId ();
				$resourceName = $resource->getName ();
				if (empty ($resourceId)) {
					$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courseresources WHERE lessonid=? AND resourcename=?', array ($lessonId, $resourceName));
				} else {
					$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courseresources WHERE lessonid=? AND resourceid<>? AND resourcename=?', array ($lessonId, $resourceId, $resourceName));
				}
				if ($this->masterAdb->num_rows ($result) > 0) {
					$e = new CourseResourceException (CourseResourceException::ERROR_COURSE_RESOURCE_DUPLICATE_NAME);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}
			}
		}

		/**
		 * @param Course $course
		 *
		 * @throws CourseException
		 */
		private function validateCourseName ($course) {
			$courseId   = $course->getId ();
			$courseName = $course->getName ();

			if (empty ($courseId)) {
				$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courses WHERE coursename=?', array ($courseName));
			} else {
				$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courses WHERE courseid<>? AND coursename=?', array ($courseId, $courseName));
			}
			if ($this->masterAdb->num_rows ($result) > 0) {
				$e = new CourseException (CourseException::ERROR_COURSE_DUPLICATE_NAME);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		public function fetchCategoryById ($categoryId) {
			if (empty($categoryId)) {
				return null;
			}
			
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_coursecategories WHERE categoryid=?', array($categoryId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$category = CourseCategory::getInstance ()
						->setId (intval ($row ['categoryid']))
						->setName($row ['categoryname'])
						->setStatus ($row ['status']);
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($category)) ? $category: null;
		}
		
		/**
		 * @param Course $course
		 */
		public function deleteCourse ($course) {
			if ((empty ($course)) || (!($course instanceof Course))) {
				return;
			}

			$courseId = $course->getId ();
			if (empty ($courseId)) {
				return;
			}

			$this->masterAdb->pquery ('DELETE FROM vtiger_coursetestanswers WHERE questionid IN (SELECT questionid FROM vtiger_coursetestquestions WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=?))', array ($courseId));
			$this->masterAdb->pquery ('DELETE FROM vtiger_coursetestquestions WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=?)', array ($courseId));
			$this->masterAdb->pquery ('DELETE FROM vtiger_coursetests WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=?)', array ($courseId));
			$this->masterAdb->pquery ('DELETE FROM vtiger_courseresources WHERE lessonid IN (SELECT lessonid FROM vtiger_courselessons WHERE courseid=?)', array ($courseId));
			$this->masterAdb->pquery ('DELETE FROM vtiger_courselessons WHERE courseid=?', array ($courseId));
			$this->masterAdb->pquery ('DELETE FROM vtiger_courses WHERE courseid=?', array ($courseId));
		}
		
		/**
		 * @param boolean $enabledOnly
		 *
		 * @return CourseCategory[]|null
		 * @throws Exception
		 */
		public function fetchCategories ($enabledOnly = true) {
			$where = '';
			if ($enabledOnly) {
				$where = " WHERE status='ENABLED' ";
			}
			$result = $this->masterAdb->query ("SELECT * FROM vtiger_coursecategories {$where} ORDER BY categoryname");
			if ($this->masterAdb->num_rows ($result) > 0) {
				$categories = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$categories[] = CourseCategory::getInstance ()
						->setId (intval ($row ['categoryid']))
						->setName ($row ['categoryname'])
						->setStatus ($row ['status']);
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($categories)) ? $categories: null;
		}
		
		/**
		 * @return Course[]|null
		 * @throws Exception
		 */
		public function fetchAllCourses () {
			$result = $this->masterAdb->query ('SELECT * FROM vtiger_courses  ORDER BY coursename');
			if ($this->masterAdb->num_rows ($result) > 0) {
				$courses = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$courses [] = Course::getInstance ()
						->setCategoryId (intval ($row ['categoryid']))
						->setCategory ($this->fetchCategoryById ($row ['categoryid']))
						->setDescription ($row ['description'])
						->setPaid ((!empty ($instanceCode)) ? ($this->isPaidCourse ($row ['courseid'], $instanceCode)) : false)
						->setId (intval ($row ['courseid']))
						->setImageCourse ($row ['imagecourse'])
						->setImageType ($row ['imagetype'])
						->setLessons (null)
						->setLessonIndex (null)
						->setLessonToPay ($row ['lessontopay'])
						->setLevel ($row ['level'])
						->setName ($row ['coursename'])
						->setPrice (floatval ($row ['price']))
						->setSeenBy  ((isset($row ['seenby'])) ? (intval ($row ['seenby'])) : null)
						->setSerie ($this->fetchSerieById ($row ['serieid']))
						->setStatus ($row ['status'])
						->setVideoCourse ($row ['videocurse'])
						->setVideoType ($row ['videotype']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($courses)) ? $courses : null;
		}

		/**
		 * @param integer $courseId
		 * @param string|null $instanceCode
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return Course|null
		 * @throws Exception
		 */
		public function fetchCourse ($courseId, $instanceCode, $adb, $userId) {
			if (empty ($courseId)) {
				return null;
			}
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courses WHERE courseid=?', array ($courseId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row    = $this->masterAdb->fetchByAssoc ($result, -1, false);
				if ($adb === null) {$adb = $this->adb;}
				$course = $this->fillCourse ($row, $instanceCode, $adb, $userId);
			} else {
				$course = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $course;
		}

		/**
		 * Retrieves a specific course by its ID
		 * 
		 * @param integer $courseId ID of the course
		 * @param boolean $onlyHeader If true, only fetches course header without lessons
		 * @return Course|null Course object, or null if not found
		 * @throws Exception If database query fails
		 */
		public function fetchCourseById ($courseId, $onlyHeader = false) {
			if (empty ($courseId)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courses WHERE courseid=?', array ($courseId));
			if ($this->masterAdb->num_rows ($result) > 0 && true) {
				$row    = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$course = Course::getInstance ()
					->setCategoryId (intval ($row ['categoryid']))
					->setDescription ($row ['description'])
					->setPaid (false)
					->setId (intval ($row ['courseid']))
					->setImageCourse ($row ['imagecourse'])
					->setImageType ($row ['imagetype'])
					->setLessons ((!$onlyHeader) ? $this->fetchLessons($row['courseid'], 0) : null)
					->setLessonIndex (null)
					->setLessonToPay ($row ['lessontopay'])
					->setLevel ($row ['level'])
					->setName ($row ['coursename'])
					->setPrice (floatval ($row ['price']))
					->setStatus ($row ['status'])
					->setSerieId ($row ['serieid'])
					->setTargetAudience ($row ['targetaudience'])
					->setVideoCourse ($row ['videocurse'])
					->setVideoType ($row ['videotype']);
			} else {
				$course = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $course;
		}

		/**
		 * @param string|null $instanceCode
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return Course[]|null
		 * @throws Exception
		 */
		public function fetchCourses ($instanceCode, $adb, $userId) {
			/*GGC 2024-01-19 Cambiada la consulta para evitar que se duplique el curso en la galería*/
			/*$result = $this->masterAdb->query ('SELECT c.*, cs.seenby FROM vtiger_courses c LEFT JOIN vtiger_courses_seen cs ON cs.courseid = c.courseid ORDER BY coursename');*/
			$result = $this->masterAdb->query ('SELECT c.*, IF((select count(*) FROM vtiger_courses_seen cs WHERE  cs.courseid = c.courseid)>0,1,NULL) AS seenby FROM vtiger_courses c ORDER BY coursename');
			if ($this->masterAdb->num_rows ($result) > 0) {
				$courses = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$courses [] = $this->fillCourse ($row, $instanceCode, $adb, $userId);
				}
			} else {
				$courses = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $courses;
		}

		/**
		 * @param integer $userId
		 *
		 * @return CoursesStatistics[]|null
		 * @throws Exception
		 */
		public function fetchCoursesStatistics ($userId) {
			if (empty($userId) || !is_numeric ($userId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_courses_seen WHERE seenby=? ', array($userId));
			if ($this->adb->num_rows ($result) > 0) {
				$coursesSeen = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$coursesSeen [] = CoursesStatistics::getInstance()
						->setCourseSeen ($this->fetchCourseById($row ['courseid'], true))
						->setLastTime ($row ['lasttime'])
						->setLessons ($this->fetchLessonStatistics ($row, $userId))
						->setSeenDate ($row ['seenon'])
						->setUserId ($row ['seenby']);
				}
			}

			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($coursesSeen)) ? $coursesSeen : null;
		}

		/**
		 * @param $categoryId
		 *
		 * @return Course[]|null
		 */
		public function fetchCoursesByCategoryId ($categoryId) {
			if (empty ($categoryId)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courses WHERE categoryid=?', array ($categoryId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$courses = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$courses [] = $this->fillCourse ($row);
				}
			} else {
				$courses = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $courses;
		}

		/**
		 * @param string $categoryName
		 *
		 * @return Course[]|null
		 */
		public function fetchCoursesByCategoryName ($categoryName) {
			if (empty ($categoryName)) {
				return null;
			}

			$result = $this->masterAdb->pquery (
				'SELECT
					c.*
				FROM
					vtiger_courses c
					INNER JOIN vtiger_coursecategories cc ON cc.categoryid=c.categoryid AND cc.categoryname=?',
				array ($categoryName)
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				$courses = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$courses [] = $this->fillCourse ($row);
				}
			} else {
				$courses = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $courses;
		}

		/**
		 * @param string $targetAudience
		 * @param string|null $instanceCode
		 *
		 * @return Course[]|null
		 * @throws Exception
		 */
		public function fetchCoursesByTargetAudience ($targetAudience, $instanceCode = null) {
			if (empty ($targetAudience)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courses WHERE targetaudience=?', array ($targetAudience));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$courses = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$courses [] = $this->fillCourse ($row, $instanceCode);
				}
			} else {
				$courses = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $courses;
		}
		
		/**
		 * @param boolean $enabledOnly
		 *
		 * @return CourseSerie[]|null
		 * @throws Exception
		 */
		public function fetchSeries ($enabledOnly = true) {
			$where = '';
			if ($enabledOnly) {
				$where = " WHERE status='ENABLED' ";
			}
			$result = $this->masterAdb->query ("SELECT * FROM vtiger_course_serie {$where} ORDER BY seriename");
			if ($this->masterAdb->num_rows ($result) > 0) {
				$series = array ();
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$series[] = CourseSerie::getInstance ()
						->setId (intval ($row ['serieid']))
						->setName ($row ['seriename'])
						->setStatus ($row ['status']);
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($series)) ? $series: null;
		}
		
		/**
		 * @param integer $serieId
		 *
		 * @return CourseSerie|null
		 * @throws Exception
		 */
		public function fetchSerieById ($serieId) {
			if (empty($serieId)) {
				return null;
			}
			
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_course_serie WHERE serieid=?', array($serieId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				while ($row = $this->masterAdb->fetchByAssoc ($result, -1, false)) {
					$category = CourseSerie::getInstance ()
						->setId (intval ($row ['serieid']))
						->setName($row ['seriename'])
						->setStatus ($row ['status']);
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($category)) ? $category : null;
		}
		
		public function fetchResource ($resourceId) {
			if (empty ($resourceId)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courseresources WHERE resourceid=?', array ($resourceId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row      = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$resource = CourseResource::getInstance ()
					->setId (intval ($row ['resourceid']))
					->setLessonId (intval ($row ['lessonid']))
					->setName ($row ['resourcename'])
					->setType ($row ['resourcetype'])
					->setUrl ($row ['url']);
			} else {
				$resource = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $resource;
		}

		/**
		 * @param integer $lessonId
		 *
		 * @return CourseLesson|null
		 * @throws Exception
		 */
		public function fetchLesson ($lessonId, $targetAdb, $userId, $onlyHeader = false) {
			if (empty ($lessonId)) {
				return null;
			}

			$result = $this->masterAdb->pquery (
				'SELECT
				       	cl.*,
				       	cl.lessonid AS lesson_id,
				       	cl.hastest AS lesson_hastest,
				       	le.*
					FROM vtiger_courselessons cl
					LEFT JOIN vtiger_lessons2exercises le ON le.lessonid = cl.lessonid
					WHERE cl.lessonid=?',
				array ($lessonId)
			);
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row      = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$hasTest  = intval($row['lesson_hastest']); // Usar el valor específico de la lección
				$exercise = null;
				if (!empty ($row['lesson2exercisesid'])) {
					$exercise = LessonExercises::getInstance ()
						->setId ($row['lesson2exercisesid'])
						->setLessonId (intval ($row ['lessonid']))
						->setName ($row ['exercises_name'])
						->setDescription ($row ['exercises_description'])
						->setPassingScore (floatval ($row ['passing_score']))
						->setHasTest (intval($row['hastest'])); // Convertir a entero el valor del ejercicio
				}
				$lesson   = CourseLesson::getInstance ()
					->setId (intval ($row ['lesson_id']))
					->setCourseId (intval ($row ['courseid']))
					->setDescription ($row ['description'])
					->setHasTest ($hasTest) // Usar el valor de la lección
					->setLessonExercise ($exercise)
					->setName ($row ['lessonname'])
					->setResources ((!$onlyHeader) ? $this->fetchResources (intval ($row ['lesson_id'])) : null)
					->setTest ((!$onlyHeader && $hasTest) ? $this->fetchTest (intval ($row ['lesson_id'])) : null)
					->setTypeVideo ($row ['videotype'])
					->setVideoUrl ($row ['videourl'])
					->setUserLessonStatus ($this->getUserLessonStatus ($targetAdb, $row['courseid'], $row['lesson_id'], $userId));;
			} else {
				$lesson = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $lesson;
		}

		/**
		 * @param integer $lessonId
		 *
		 * @return CourseTest|null
		 * @throws Exception
		 */
		public function fetchTest ($lessonId) {
			if (empty ($lessonId)) {
				return null;
			}

			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_coursetests WHERE lessonid=?', array ($lessonId));
			if ($this->masterAdb->num_rows ($result) > 0) {
				$row  = $this->masterAdb->fetchByAssoc ($result, -1, false);
				$test = CourseTest::getInstance ()
					->setDescription ($row ['description'])
					->setFeedback ($row ['feedback'])
					->setFeedbackNotApproved ($row ['feedback_no_approved'])
					->setLessonId (intval ($row ['lessonid']))
					->setMinimumScore (floatval ($row ['minimumscore']))
					->setQuestions ($this->fetchTestQuestions (intval ($row ['lessonid'])))
					->setTotalQuestionsPerTest (intval ($row ['totalquestionspertest']));
			} else {
				$test = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $test;
		}

		/**getInstance
		 * Obtiene o crea una instancia de CourseManager para la base de datos especificada.
		 * Utiliza un patrón singleton para asegurar que solo exista una instancia por base de datos.
		 * 
		 * @param PearDatabase $adb Conexión a la base de datos a utilizar para esta instancia.
		 * @return CourseManager Instancia de CourseManager para la base de datos especificada.
		 * 
		 * @author Staff de Time Management
		 * @copyright Todos los derechos reservados. Time Management
		 * @since Versión: 1.0
		 * @version documentación: 1.0 Revisión: 10-04-2025
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}
		
		public function getTotalNewCourseByUser ($userId) {
			if (!$userId || !is_numeric ($userId)) {
				return 0;
			}
			//20250207-GGC Para tener nombre de base de datos hija
			$result = $this->adb->pquery('SELECT count(*) from vtiger_blocks ');
			$childDbase  = $this->adb->dbName;
			//
			
			$result = $this->masterAdb->pquery ('SELECT * FROM vtiger_courses c WHERE NOT EXISTS (SELECT * FROM '. $childDbase . '.vtiger_courses_seen cs WHERE cs.courseid = c.courseid AND cs.seenby=?)', array ($userId));
			DatabaseUtils::closeResult ($result);
			return $this->masterAdb->getRowCount ($result);
		}

		/**
		 * @param Course $course
		 *
		 * @return Course
		 * @throws Exception
		 */
		public function saveCourse ($course) {
			$this->validate ($course);
			$courseId = $course->getId ();
			if (empty ($courseId)) {
				$this->masterAdb->pquery (
					'INSERT INTO vtiger_courses (coursename, description, categoryid, level, price, lessonToPay, status, serieid, imagecourse, imagetype, videocurse, videotype, forum_name, forum_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($course->getName (), $course->getDescription (), $course->getCategoryId (), $course->getLevel (), $course->getPrice (), $course->getLessonToPay (), $course->getStatus (), $course->getSerieid (), $course->getImageCourse(), $course->getImageType(), $course->getVideoCourse (), $course->getVideoType (), $course->getForumName(), $course->getForumUrl())
				);
				$course->setId ($this->masterAdb->getLastInsertID ());
			} else {
				$this->masterAdb->pquery (
					'UPDATE vtiger_courses SET coursename=?, description=?, categoryid=?, level=?, price=?, lessontopay=?, status=?, serieid=?, imagecourse=?, imagetype=?, videocurse=?, videotype=?, forum_name=?, forum_url=? WHERE courseid=?',
					array ($course->getName (), $course->getDescription (), $course->getCategoryId (), $course->getLevel (), $course->getPrice (), $course->getLessonToPay (), $course->getStatus (), $course->getSerieid (), $course->getImageCourse(), $course->getImageType(), $course->getVideoCourse (), $course->getVideoType (), $course->getForumName(), $course->getForumUrl(), $courseId)
				);
			}
			$this->saveLessons ($course);
			return $course;
		}

		/**
		 * @param array $data
		 */
		public function savePaidCourse ($data) {
			$this->masterAdb->run_insert_data ('vtiger_courses_paid', $data);
		}

		/**
		 * @param integer $courseId
		 * @param integer $userId
		 * @param string $today
		 *
		 * @return integer
		 * @throws Exception
		 */
		public function setSeenCourse ($courseId, $userId, $today) {
			$lastTime = time();
			$isSeenCourse = $this->adb->run_query_allrecords ("SELECT * FROM vtiger_courses_seen WHERE courseid={$courseId} AND seenby={$userId}");
			if (!empty ($isSeenCourse)) {
				$this->adb->pquery ('UPDATE vtiger_courses_seen SET lasttime=?  WHERE courseid=? AND seenby=?', array ($lastTime, $courseId, $userId));
				$status = $this->calculateCourseStatus($courseId, $userId);
				if ($status === null) {
					$status = 'NOT_STARTED';
				}
				$this->adb->run_insert_data('vtiger_courses2user', 	array('courseid' => $courseId, 'userid' => $userId,	'start_date' => $today, 'status' => $status ));
				return $this->adb->getLastInsertID('vtiger_courses2user');
			}
			$this->adb->run_insert_data('vtiger_courses_seen', array('courseid' => $courseId,'seenon' => $today,'lasttime' => $lastTime,'seenby' => $userId	));
			$this->adb->run_insert_data('vtiger_courses2user', array('courseid' => $courseId,'userid' => $userId,'start_date' => $today,'status' => 'NOT_STARTED'));
			return $this->adb->getLastInsertID('vtiger_courses2user');
		}

		/**
		 * setSeenLesson
		 * 
		 * Marca una lección como vista para un usuario específico registrando la fecha y hora actuales.
		 * 
		 * @param integer $courseId ID del curso asociado con la lección.
		 * @param integer $lessonId ID de la lección que se marca como vista.
		 * @param integer $userId ID del usuario que ha visto la lección.
		 * @param string $today Fecha y hora actuales en formato 'Y-m-d H:i:s'.
		 * 
		 * @return integer Retorna el ID de la última inserción en la tabla `vtiger_lessons2user`.
		 * 
		 * @throws Exception Lanza una excepción si la operación falla.
		 * 
		 * @usage Se utiliza para registrar cuando un usuario ha accedido a una lección.
		 * 
		 * @author Staff de Time Management
		 * @copyright Todos los derechos reservados. Time Management
		 * @since Versión: 1.0
		 * @version documentación: 1.0 Revisión: 10-04-2025
		 */
		public function setSeenLesson ($courseId, $lessonId, $userId, $today) {
			$lastTime = time();
			$isSeenCourse = $this->adb->run_query_allrecords ("SELECT * FROM vtiger_lessons_seen WHERE courseid={$courseId} AND lessonid={$lessonId} AND seenby={$userId}");
			if (!empty ($isSeenCourse)) {
				$this->adb->pquery ('UPDATE vtiger_lessons_seen SET lasttime=?  WHERE courseid=? AND seenby=?', array ($lastTime, $courseId, $userId));
				$result = $this->adb->pquery ('SELECT status FROM vtiger_lessons2user WHERE courseid=? AND lessonid=? AND userid=? ORDER BY lesson2userid DESC LIMIT 1', array ($courseId, $lessonId, $userId));
				if ($this->adb->num_rows ($result) > 0) {
					$row = $this->adb->fetchByAssoc ($result, -1, false);
					$this->adb->run_insert_data ('vtiger_lessons2user', array ('courseid' => $courseId, 'lessonid' => $lessonId, 'userid' => $userId, 'start_date' => $today, 'status' => $row ['status']));
				} else {
					$this->adb->run_insert_data ('vtiger_lessons2user', array ('courseid' => $courseId, 'lessonid' => $lessonId, 'userid' => $userId, 'start_date' => $today, 'status' => 'LESSON_VISITED'));
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			} else {
				$this->adb->run_insert_data ('vtiger_lessons_seen', array ('courseid' => $courseId, 'lessonid' => $lessonId, 'seenon' => $today, 'lasttime' => $lastTime, 'seenby' => $userId));
				$this->adb->run_insert_data ('vtiger_lessons2user', array ('courseid' => $courseId, 'lessonid' => $lessonId, 'userid' => $userId, 'start_date' => $today, 'status' => 'LESSON_VISITED'));
			}

			// Actualizar el estado del curso
			$status = $this->calculateCourseStatus($courseId, $userId);
			if ($status !== null) {
				$result = $this->adb->pquery('SELECT MAX(course2userid) AS maximoid FROM vtiger_courses2user WHERE courseid = ? AND userid = ?', array($courseId, $userId));
				$maxim = $this->adb->query_result($result, 0, 'maximoid');
				
				$result = $this->adb->pquery(
					'UPDATE vtiger_courses2user SET status = ? WHERE courseid = ? AND userid = ? AND course2userid = ?',
					array($status, $courseId, $userId, $maxim)
				);
				DatabaseUtils::closeResult($result);
			}

			// --- AUDITORÍA DE VISITAS A CURSOS DESDE INSTANCIAS HIJAS ---
			if (isset($_SESSION['platInstancia']) && strtolower($_SESSION['platInstancia']) !== 'madre') {
				$instance_code = $_SESSION['platInstancia'];
				$user_name = isset($_SESSION['authenticated_user_name']) ? $_SESSION['authenticated_user_name'] : '';
				$courseId = intval($courseId);
				// Usar la conexión a la base madre
				if ($this->masterAdb) {
					// Buscar si ya existe registro para este curso+instancia
					$sql = "SELECT id FROM vtiger_courses_instance_seen WHERE courseid = ? AND instance_code = ?";
					$result = $this->masterAdb->pquery($sql, array($courseId, $instance_code));
					if ($this->masterAdb->num_rows($result) > 0) {
						// UPDATE: end_date y last_user_name
						$id = $this->masterAdb->query_result($result, 0, 'id');
						$this->masterAdb->pquery(
							"UPDATE vtiger_courses_instance_seen SET end_date = ?, last_user_name = ?, last_lessonid = ? WHERE id = ?",
							array($today, $user_name, $lessonId, $id)
						);
					} else {
						// INSERT: nuevo registro
						$this->masterAdb->pquery(
							"INSERT INTO vtiger_courses_instance_seen (courseid, instance_code, init_date, init_user_name, init_lessonid, end_date, last_user_name, last_lessonid) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
							array($courseId, $instance_code, $today, $user_name, $lessonId, $today, $user_name, $lessonId)
						);
					}
				}
			}
			// --- FIN AUDITORÍA ---
			return $this->adb->getLastInsertID ('vtiger_lessons2user');
		}



	}
