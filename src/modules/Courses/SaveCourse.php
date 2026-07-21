<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $adb, $current_user, $site_URL;
	setBugSnag ($site_URL);

	try {
		$isInstance = !empty ($_SESSION ['platInstancia']);
		if (($isInstance) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado');
		}

		$courseId  = PlatzillaUtils::purify ($_POST, 'record');
		$price     = PlatzillaUtils::purify ($_POST, 'price');
		$uploadMax = (PlatzillaUtils::getMaxFileSizeInMb () * (1024 * 1024));
		$photo     = CoursesHelper::getImageCourse ($uploadMax);
		
		if (empty ($photo) && !empty ($courseId)) {
			$course = CoursesHelper::fetchCourseById ($adb, $courseId, null, $adb, $current_user->id);
			$photo  = $course->getImageCourse ();
			unset($course);
		}
		$courseData = array (
			'categoryid'     => PlatzillaUtils::purify ($_POST, 'categoryid'),
			'courseid'       => $courseId,
			'coursename'     => PlatzillaUtils::purify ($_POST, 'coursename'),
			'description'    => PlatzillaUtils::purify ($_POST, 'description'),
			'lessons'        => PlatzillaUtils::purify ($_POST, 'lessons'),
			'level'          => PlatzillaUtils::purify ($_POST, 'level'),
			'price'          => (!empty ($price)) ? $price : 0,
			'status'         => PlatzillaUtils::purify ($_POST, 'status'),
			'targetaudience' => PlatzillaUtils::purify ($_POST, 'targetaudience'),
			'lessonToPay'    => intval (PlatzillaUtils::purify ($_POST, 'lessonToPay')),
			'imagePhoto'     => $photo,
			'imageType'      => PlatzillaUtils::purify ($_POST, 'imageType'),
			'videoCourse'    => PlatzillaUtils::purify ($_POST, 'coursevideourl'),
			'videoType'      => PlatzillaUtils::purify ($_POST, 'coursevideotype'),
			'forumName'      => PlatzillaUtils::purify ($_POST, 'forum_name'),
			'forumUrl'       => PlatzillaUtils::purify ($_POST, 'forum_url'),
		);
		$course = CoursesHelper::buildCourse ($courseData);
		CoursesHelper::saveCourse ($adb, $course);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'El curso ha sido guardado',
		);
		header ('Location: index.php?module=Home&action=index&tab=TRAINING');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => isset ($course) ? $course->serialize () : null,
		);
		$recordUriPart = !empty ($courseId) ? "&record={$courseId}" : '';
		header ("Location: index.php?module=Courses&action=EditView{$recordUriPart}");
	}
	exit ();
