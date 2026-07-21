<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	
	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL;
	
	$function   = PlatzillaUtils::purify($_REQUEST, 'function');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	$masterAdb  = AdbManager::getInstance()->getMasterAdb();
	$moduleName = PlatzillaUtils::purify($_REQUEST, 'flmodule',null);
	
	if ($function == 'LESSON_PASSED') {
		try {
			$courseId = PlatzillaUtils::purify ($_GET, 'course');
			$lessonId = PlatzillaUtils::purify ($_GET, 'record');
			if (empty ($lessonId)) {
				throw new Exception ('No has suministrado el ID de la lección');
			}
			if (empty ($courseId)) {
				throw new Exception ('No has suministrado el ID del curso');
			}
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$couse    = CoursesHelper::fetchLesson ($masterAdb, $lessonId, $_SESSION ['platInstancia'],$adb, $current_user->id, $courseId);
			if (empty ($couse) || empty ($couse['course'])) {
				throw new Exception ('La lección solicitada no existe');
			}
			$lesson = $couse['course'];
			
			if ($lesson->getUserLessonStatus() == 'LESSON_PASSED') {
				throw new Exception ('Ya has pasado la lección');
			}
			
			$result = $adb->pquery ('INSERT INTO vtiger_lesson_evaluated2user (courseid, lessonid, userid, status) VALUES (?, ?, ?, ?)',
				array ($courseId, $lessonId, $current_user->id, 'LESSON_PASSED')
			);
			
			CoursesHelper::setGoodbyeLesson ($adb, $courseId, $lessonId, $current_user->id, 'LESSON_PASSED');
			
			// Asegurarse que no se han enviado headers
			if (!headers_sent()) {
				// Si el botón es "Listo, he terminado la lección", siempre redirige a la vista del curso
				// ya que este botón solo aparece cuando no hay ni evaluación ni ejercicio práctico
				if (!$lesson->getHasTest() && empty($lesson->getLessonExercise())) {
					header ("Location: index.php?module=Courses&action=CourseView&record={$courseId}");
					exit();
				} else if (empty ($lesson->getLessonExercise())) {
					header ("Location: index.php?module=Courses&action=LessonView&course={$courseId}&record={$lessonId}");
					exit();
				} else {
					header ("Location: index.php?module=Courses&action=LessonView&course={$courseId}&record={$lessonId}");
					exit();
				}
			}
		} catch (Exception $e) {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => $e->getMessage (),
			);
			if (!headers_sent()) {
				header ("Location: index.php?module=Courses&action=CourseView&record={$courseId}");
				exit();
			}
		
		}
	} else if ($function == 'TRACK-COURSE') {
		try {
			$trackId = PlatzillaUtils::purify ($_REQUEST, 'track_id');
			
			if (empty ($trackId)) {
				throw new Exception ('No has suministrado el ID del track');
			}
			$today = date ('Y-m-d h:i:s', time());
			
			$adb->pquery (
				'UPDATE vtiger_courses2user SET end_date=?  WHERE course2userid=?',
				array ($today, $trackId)
			);
			
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'TRACK-LESSON') {
		try {
			$trackId = PlatzillaUtils::purify ($_REQUEST, 'track_id');
			
			if (empty ($trackId)) {
				throw new Exception ('No has suministrado el ID del track');
			}
			$today = date ('Y-m-d h:i:s', time());
			
			$adb->pquery (
				'UPDATE vtiger_lessons2user SET end_date=?  WHERE lesson2userid=?',
				array ($today, $trackId)
			);
			
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header('Access-Control-Allow-Origin: *');
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	exit();
