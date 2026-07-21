<?php
	require_once ('include/QueryGenerator/QueryGenerator.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/CustomView/CustomView.php');

	global $adb, $currentModule, $current_user;

	checkFileAccessForInclusion ("modules/$currentModule/$currentModule.php");
	require_once ("modules/$currentModule/$currentModule.php");

	$viewId = isset ($_REQUEST ['viewid']) ? vtlib_purify ($_REQUEST ['viewid']) : null;

	$customView = new CustomView ();
	$viewInfo = $customView->getCustomViewByCvid ($viewid);

	if ($viewInfo ['viewname'] != 'All') {
		/** @var boxscore|stdClass $entity */
		$entity = new boxscore ();
		$entity->column_fields ['assigned_user_id'] = $current_user->id;
		$entity->column_fields ['titulo'] = $viewInfo['viewname'];
		$entity->column_fields ['fecha'] = date ('Y-m-d');
		$entity->column_fields ['escala'] = 'Month';
		$entity->save ('boxscore');

		$queryGenerator = new QueryGenerator ('proyectos', $current_user);
		$queryGenerator->initForCustomViewById ($viewId);
		$sql    = $queryGenerator->getQuery ();
		$result = $adb->query ($sql);
		if (($result) && ($adb->num_rows ($result))) {
			while ($row = $adb->fetchByAssoc ($result)) {
				$adb->pquery ('INSERT INTO vtiger_box_score_data (box_score, tipo, boxscoreid) VALUES (?, ?, ?)', array ($row ['name'], 1, $entity->id));
				$lastInsertId = $adb->getLastInsertID ();
				$adb->pquery (
					"INSERT INTO vtiger_boxscore_privileges (userid, boxscoreid, box_score_dataid, visible) VALUES (?, ?, ?, '1')",
					array ($current_user->id, $entity->id, $lastInsertId)
				);
			}
		}
		header ("Location: index.php?module=boxscore&action=DetailView&record={$entity->id}");
	} else {
		header ('Location: index.php?module=proyectos&action=index');
	}
	exit ();
