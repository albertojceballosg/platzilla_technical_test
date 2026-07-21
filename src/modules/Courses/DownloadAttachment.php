<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $site_URL;
	setBugSnag ($site_URL);

	try {
		$resourceId = PlatzillaUtils::purify ($_GET, 'record');
		if (empty ($resourceId)) {
			throw new Exception ('No has suministrado el ID del recurso');
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$resource  = CoursesHelper::fetchResource ($masterAdb, $resourceId);
		if (empty ($resource)) {
			throw new Exception ('No se encuentra el recurso solicitado');
		}

		$resourceFolderPath = CourseResource::getFolderPath ();
		$resourceFilePath   = "{$resourceFolderPath}/{$resource->getId ()}.bin";
		if (!file_exists ($resourceFilePath)) {
			throw new Exception ('No se encuentra el recurso solicitado');
		}
		$finfo               = finfo_open (FILEINFO_MIME_TYPE);
		$resourceContentType = finfo_file ($finfo, $resourceFilePath);
		finfo_close ($finfo);

		$dummy        = (!empty ($resourceContentType)) ? explode('/', $resourceContentType) : null;
		$resourceName = CoursesHelper::sanitizeString ($resource->getName());
		$resourceName = (empty ($dummy)) ? $resourceName : "{$resourceName}.{$dummy [ (count ($dummy) - 1)]}";


		header ('Pragma: public');
		header ('Expires: 0');
		header ('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header ('Cache-Control: private', false);
		header ("Content-Type: {$resourceContentType}");
		header ('Content-Disposition: attachment;filename=' . urlencode ($resourceName));
		header ('Content-Transfer-Encoding: binary');
		$file = fopen ($resourceFilePath, 'r');
		while ($chunk = fread ($file, 4096)) {
			echo $chunk;
		}
		fclose ($file);
	} catch (Exception $e) {
		echo $e->getMessage ();
	}
	exit ();
