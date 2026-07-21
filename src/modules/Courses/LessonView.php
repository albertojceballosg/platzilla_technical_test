<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	require_once ('modules/Courses/language/es_es.lang.php'); // Incluir archivo de idioma

	global $adb, $current_user, $site_URL, $mod_strings, $app_strings; // Agregar $mod_strings y $app_strings
	setBugSnag ($site_URL);

	$smarty = new vtigerCRM_Smarty ();

	try {
		$courseId = PlatzillaUtils::purify ($_GET, 'course');
		$lessonId = PlatzillaUtils::purify ($_GET, 'record');
		if (empty ($lessonId)) {
			throw new Exception ('No has suministrado el ID de la lección');
		}
		
		if (empty ($courseId)) {
			throw new Exception ('No has suministrado el ID del curso');
		}
		$masterAdb    = AdbManager::getInstance ()->getMasterAdb ();
		$lesson       = CoursesHelper::fetchLesson ($masterAdb, $lessonId, $_SESSION ['platInstancia'],$adb, $current_user->id, $courseId);
		$lessons      = $masterAdb->run_query_allrecords ("SELECT lessonid, hastest FROM vtiger_courselessons WHERE courseid = {$courseId}  ORDER BY lessonid ASC");
		$hasTest      = array_column ($lessons, 'hastest');
		$lessons      = array_column ($lessons, 'lessonid');
		$posLesson    = array_search ($lessonId, $lessons);
		$totalLessons = (count ($lessons) - 1);
		$prevPos      = ($posLesson > 0) ? ($posLesson - 1) : null;
		$nextPos      = ($posLesson < $totalLessons) ? ($posLesson + 1) : null;
		$currentLesson = $posLesson;

		// Obtener estado de la lección actual
		$lessonUserStatus = 'LESSON_NOT_VISITED';
		$result = $adb->pquery(
			'SELECT status 
			FROM vtiger_lessons2user 
			WHERE lessonid = ? AND courseid = ? AND userid = ? 
			ORDER BY lesson2userid DESC LIMIT 1',
			array($lessonId, $courseId, $current_user->id)
		);
		if ($adb->num_rows($result) > 0) {
			$row = $adb->fetchByAssoc($result, -1, false);
			$lessonUserStatus = $row['status'];
		}

		// Obtener estado de la lección anterior
		$prevLessonStatus = 'LESSON_NOT_VISITED';
		if ($prevPos !== null) {
			$prevLessonId = $lessons[$prevPos];
			$result = $adb->pquery(
				'SELECT status 
				FROM vtiger_lessons2user 
				WHERE lessonid = ? AND courseid = ? AND userid = ? 
				ORDER BY lesson2userid DESC LIMIT 1',
				array($prevLessonId, $courseId, $current_user->id)
			);
			if ($adb->num_rows($result) > 0) {
				$row = $adb->fetchByAssoc($result, -1, false);
				$prevLessonStatus = $row['status'];
			}
		} else {
			// Si es la primera lección, consideramos como PASSED la anterior
			$prevLessonStatus = 'LESSON_PASSED';
		}

		// Obtener estado de la evaluación actual
		$evaluationStatus = '';
		$query = 'SELECT status 
			FROM vtiger_lesson_evaluated2user 
			WHERE lessonid = ? 
			AND courseid = ?
			AND userid = ?
			ORDER BY evaluated2userid DESC LIMIT 1';
		$params = array($lessonId, $courseId, $current_user->id);
	
		$result = $adb->pquery($query, $params);
		
		if ($adb->num_rows($result) > 0) {
			$row = $adb->fetchByAssoc($result, -1, false);
			$evaluationStatus = $row['status'];
		
			// Si la lección solo tiene evaluación y está aprobada, marcarla como completada
			if ($evaluationStatus === 'TEST_PASSED' && 
				(!isset($lesson['course']) || !method_exists($lesson['course'], 'getLessonExercise') || $lesson['course']->getLessonExercise() === NULL)) {
				$lessonUserStatus = 'LESSON_PASSED';
			} elseif ($evaluationStatus === 'TEST_NOT_PASSED' && 
				(!isset($lesson['course']) || !method_exists($lesson['course'], 'getLessonExercise') || $lesson['course']->getLessonExercise() === NULL)) {
				$lessonUserStatus = 'LESSON_ASSESSED_BUT_NOT_PASSED';
			}
		}

		// Obtener estado del ejercicio si existe
		$exerciseStatus = '';
		$lessonExercise = NULL;
		if (isset($lesson['course']) && method_exists($lesson['course'], 'getLessonExercise')) {
			$lessonExercise = $lesson['course']->getLessonExercise();
			if ($lessonExercise !== NULL) {
				$query = 'SELECT status 
					FROM vtiger_lesson_exercise2user 
					WHERE lessonid = ? 
					AND userid = ?';
				$params = array($lessonId, $current_user->id);
				
				$result = $adb->pquery($query, $params);
				if ($adb->num_rows($result) > 0) {
					$row = $adb->fetchByAssoc($result, -1, false);
					$exerciseStatus = $row['status'];
				}
			}
		}
		//Obtener el estado de pago del curso y el número de lección a partir del cual es pago
		$userHasPaid = CourseManager::hasUserPaidForCourse($masterAdb, $courseId, $current_user->user_name);
		$vconsulta = "SELECT c.lessontopay as lessontopay, c.price as price FROM vtiger_courses c WHERE c.courseid = $courseId";
		$row = $masterAdb->run_query_record ($vconsulta);
		$lessontopay = $row['lessontopay'];
		$price = $row['price'];
		// Asignar todas las variables necesarias a Smarty
		$smarty->assign('COURSE_ID', $courseId);
		$smarty->assign ('USERHASPAID', $userHasPaid);
		$smarty->assign ('LESSONTOPAY', $lessontopay);
		$smarty->assign ('PRICE', $price);
		$smarty->assign ('CURRENTLESSON', $currentLesson);
		$smarty->assign('LESSON_USER_STATUS', $lessonUserStatus);
		$smarty->assign('PREV_LESSON_STATUS', $prevLessonStatus);
		$smarty->assign('EVALUATION_STATUS', $evaluationStatus);
		$smarty->assign('EXERCISE_STATUS', $exerciseStatus);
		$smarty->assign('LESSON', $lesson['course']);
		$smarty->assign('LESSON_ID', $lessonId);
		$smarty->assign('HAS_TEST', $hasTest[$posLesson]);
		$smarty->assign('NEXT_LESSON', (isset($lessons)) ? $lessons[$nextPos] : null);
		$smarty->assign('PREV_LESSON', (isset($lessons)) ? $lessons[$prevPos] : null);
		$smarty->assign('TRACK_LESSON_ID', CoursesHelper::setSeenLesson($adb, $courseId, $lessonId, $current_user->id));
		$smarty->assign('SHOW_BTN', ($prevLessonStatus === 'LESSON_PASSED') ? 'YES' : 'NO');
		$smarty->assign('SHOW_RESOURCE', (count($lesson['course']->getResources()) - $lesson['course']->getLessonExercise()) > 0);
		$smarty->assign('STATUS_COLOR', CoursesInterface::LESSON_STATUS_COLOR);
		$smarty->assign('lessonExercise', $lessonExercise);
		$smarty->assign('exerciseId', $lessonExercise);
		$smarty->assign('UI_COLORS', CoursesInterface::UI_COLORS);
		$smarty->assign('FILE_ICONS', CoursesInterface::FILE_ICONS);

		// Debug para ver los valores
		$smarty->assign('DEBUG_VALUES', array(
			'evaluationStatus' => $evaluationStatus,
			'prevLessonStatus' => $prevLessonStatus,
			'hasTest' => $hasTest,
			'lessonUserStatus' => $lessonUserStatus
		));

		// Asignar colores desde CoursesInterface
		$smarty->assign('EVALUATION_COLORS', array(
			'DISABLED' => CoursesInterface::EXERCISE_BUTTON_COLOR['NO_ACCESS'],
			'PASSED' => CoursesInterface::EVALUATION_STATUS_COLOR['TEST_PASSED'],
			'NOT_PASSED' => CoursesInterface::EVALUATION_STATUS_COLOR['TEST_NOT_PASSED'],
			'DEFAULT' => CoursesInterface::EXERCISE_BUTTON_COLOR['READY']
		));

		$smarty->assign('EXERCISE_COLORS', array(
			'DISABLED' => CoursesInterface::EXERCISE_BUTTON_COLOR['NO_ACCESS'],
			'DONE' => CoursesInterface::EXERCISE_BUTTON_COLOR['EXERCISE_DONE'],
			'VISITED' => CoursesInterface::EXERCISE_BUTTON_COLOR['EXERCISE_VISITED'],
			'READY' => CoursesInterface::EXERCISE_BUTTON_COLOR['READY']
		));

		// Asignar variables de idioma a Smarty
		$smarty->assign('MOD', $mod_strings);
		$smarty->assign('APP', $app_strings);

		// Mostrar el template
		$smarty->display('Smarty/templates/centaurus/modules/Courses/LessonView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', 'index.php?module=Home&action=index');
		$smarty->display ('Message.tpl');
	}
