<?php
	require_once ('include/platzilla/Data/EntityComments.php');
	require_once ('include/utils/EntityCommentsUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user, $currentModule;

	try {
		$entityId   = PlatzillaUtils::purify ($_POST, 'entityid');
		$stringNote = PlatzillaUtils::purify ($_POST, 'statementText');
		$voiceNote  = PlatzillaUtils::purify ($_POST, 'voice_note');
		
		if (!empty ($voiceNote)) {
			$note = EntityComments::getInstance ()
				->setCrmId ($entityId)
				->setStatement ($voiceNote)
				->setCommentType ('SOUND')
				->setWrittenBy ($current_user->id)
				->setWrittenOn (null);
			$data = EntityCommentsUtils::saveComment ($adb, $note);
		}
		
		if (!empty($stringNote)) {
			$note = EntityComments::getInstance ()
				->setCrmId ($entityId)
				->setStatement ($stringNote)
				->setCommentType ('TEXT')
				->setWrittenBy ($current_user->id)
				->setWrittenOn (null);
			
			$data = EntityCommentsUtils::saveComment ($adb, $note);
		}
		
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array ('error' => 'OK'));
	} catch (Exception $e) {
		header('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode (array('error' => $e->getMessage ()));
	}
	exit ();
