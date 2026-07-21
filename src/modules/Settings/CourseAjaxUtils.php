<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');
	
	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL, $masterAdb;
	setBugSnag ($site_URL);
	
	$smarty = new vtigerCRM_Smarty ();
	
	$function = PlatzillaUtils::purify ($_REQUEST, 'function');
	
	if ($function == 'CHAMGE_STATUS_COURSE') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			$status = PlatzillaUtils::purify ($_POST, 'status');
			if (empty ($record)) {
				throw new Exception ('Curso no encontrado');
			} else if (empty($status) || !in_array ($status, array('INACTIVE', 'ACTIVE'))) {
				throw new Exception ('Imposible cambiar el estatus');
			}
			
			$status = ($status == 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
			
			CoursesHelper::changeStatusInCourses ($masterAdb, $record, $status, 'COURSE');
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $customViewHtml));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'CHAMGE_STATUS_SERIE') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			$status = PlatzillaUtils::purify ($_POST, 'status');
			if (empty ($record)) {
				throw new Exception ('Curso no encontrado');
			} else if (empty($status) || !in_array ($status, array('ENABLED', 'DISABLED'))) {
				throw new Exception ('Imposible cambiar el estatus');
			}
			
			$status = ($status == 'ENABLED') ? 'DISABLED' : 'ENABLED';
			
			CoursesHelper::changeStatusInCourses ($masterAdb, $record, $status, 'SERIE');
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $customViewHtml));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'CHAMGE_STATUS_CATEGORY') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			$status = PlatzillaUtils::purify ($_POST, 'status');
			if (empty ($record)) {
				throw new Exception ('Categoria no encontrada');
			} else if (empty($status) || !in_array ($status, array('ENABLED', 'DISABLED'))) {
				throw new Exception ('Imposible cambiar el estatus');
			}
			
			$status = ($status == 'ENABLED') ? 'DISABLED' : 'ENABLED';
			
			CoursesHelper::changeStatusInCourses ($masterAdb, $record, $status, 'CATEGORY');
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $customViewHtml));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'CLONE_COURSE') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			if (empty ($record)) {
				throw new Exception ('Curso no encontrado');
			}
			
			$objCourse = CourseManager::getInstance ($adb);
			
			$theCourse = $objCourse->fetchCourseById ($record, false);
			$theCourse->setId (null);
			$theCourse->setSeenBy (null);
			$theCourse->setStatus ('INACTIVE');
			$theCourse->setName ($theCourse->getName () . ' ( Copia )');
			$theCourse->setLessons ($theCourse->getLessons ()['lessons']);
			$totalLesson = count($theCourse->getLessons ());
			for ($k = 0; $k < $totalLesson; $k++) {
				$theCourse->getLessons ()[$k]->setId (null);
				$theCourse->getLessons ()[$k]->setName ($theCourse->getLessons ()[$k]->getName() . ' (copia)');
			}
			$objCourse->saveCourse ($theCourse);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $customViewHtml));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'CHAMGE_PHOTO') {
		try {
			$record = PlatzillaUtils::purify ($_GET, 'record', null);
			if (empty ($record)) {
				throw new Exception ('Curso no encontrado');
			}
			
			$theCourse = CourseManager::getInstance ($adb)->fetchCourseById ($record, true);
			if (empty ($theCourse)) {
				throw new Exception ('Curso no encontrado');
			}
			
			$smarty = new vtigerCRM_Smarty();
			$smarty->assign ('COURSE', $theCourse);
			$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
		} catch (Exception $e) {
			$smarty->assign ('IS_ERROR', true);
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		}
		$smarty->display ('Settings/Course/UpdateImageCourse.tpl');
	} else if ($function == 'SAVE_COURSE_PHOTO') {
		try {
			$record     = PlatzillaUtils::purify ($_POST, 'record', null);
			$photo      = PlatzillaUtils::purify ($_POST, 'photo');
			$photoType  = PlatzillaUtils::purify ($_POST, 'imageType');
			if (empty ($record)) {
				throw new Exception ('Curso no encontrado');
			}
			$dummy = explode (',', $photo);
			$photo = array_pop ($dummy);
			
			CoursesHelper::updateImageCourse ($adb, $record, $photoType, $photo);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $customViewHtml));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'DELETE_IN_COURSE') {
		try {
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			$type   = PlatzillaUtils::purify ($_POST, 'from');
			if (empty ($record)) {
				throw new Exception ('Curso no encontrado');
			}
			
			CoursesHelper::deleteInCourse ($adb, $record, $type);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $customViewHtml));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	exit();
