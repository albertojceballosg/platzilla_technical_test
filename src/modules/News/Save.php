<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');

	global $adb;
	$category     = PlatzillaUtils::purify ($_POST, 'category');
	$content      = PlatzillaUtils::purify ($_POST, 'content');
	$endDate      = PlatzillaUtils::purify ($_POST, 'enddate');
	$endTime      = PlatzillaUtils::purify ($_POST, 'endtime');
	$record       = PlatzillaUtils::purify ($_POST, 'record');
	$queue        = PlatzillaUtils::purify ($_POST, 'queue');
	$returnAction = PlatzillaUtils::purify ($_POST, 'return_action');
	$returnModule = PlatzillaUtils::purify ($_POST, 'return_module');
	$sharingItems = PlatzillaUtils::purify ($_POST, 'sharing');
	$startDate    = PlatzillaUtils::purify ($_POST, 'startdate');
	$startTime    = PlatzillaUtils::purify ($_POST, 'starttime');
	$status       = PlatzillaUtils::purify ($_POST, 'status');
	$title        = PlatzillaUtils::purify ($_POST, 'title');

	try {
		if (!empty ($sharingItems)) {
			$sharingData = array ();
			foreach ($sharingItems as $sharingType => $sharingEntities) {
				foreach ($sharingEntities as $sharingEntity) {
					$sharingData [] = array ($sharingType => $sharingEntity);
				}
			}
		} else {
			$sharingData = null;
		}

		if ($category != 'PLATZILLA') {
			$startDate = null;
			$endDate   = null;
			$startTime = null;
			$endTime   = null;
		}

		$newsData = array (
			'newsid'        => !empty ($record) ? $record : null,
			'category'      => $category,
			'content'       => $content,
			'enddatetime'   => !empty ($endDate) ? "{$endDate} {$endTime}" : null,
			'sharing'       => $sharingData,
			'adQueueId'     => $queue,
			'startdatetime' => !empty ($startDate) ? "{$startDate} {$startTime}" : null,
			'status'        => $status,
			'title'         => $title,
		);
		AdQueueHelper::getInstance()->saveNewsData ($newsData);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha guardado el anuncio',
		);
		header ("Location: index.php?module={$returnModule}&action={$returnAction}");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $newsData,
		);
		header ("Location: index.php?module=News&action=EditView&return_module={$returnModule}&return_action={$returnAction}");
	}
	exit ();
