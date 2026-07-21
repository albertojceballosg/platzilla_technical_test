<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule;

	$recordIds = PlatzillaUtils::purify ($_GET, 'ids');

	if (!empty ($recordIds)) {
		$arguments     = explode (',', $recordIds);
		$questionMarks = str_repeat ('?, ', (count ($arguments) - 1)) . '?';
		$result        = $adb->pquery (
			"SELECT
				rssn.*,
				m.*
			FROM
				vtiger_rssnews rssn
				INNER JOIN vtiger_crmentity crme ON crme.crmid=rssn.rssnewsid
				LEFT JOIN vtiger_medios_bdi m ON m.medios_bdiid=rssn.media
			WHERE
				crme.deleted=0 AND
				rssn.rssnewsid IN ({$questionMarks})",
			$arguments
		);
		if (($result) && ($adb->num_rows ($result) > 0)) {
			$news = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$news [] = $row;
			}
		} else {
			$news = null;
		}
	} else {
		$news = null;
	}

	$entity = CRMEntity::getInstance ($currentModule);
	setObjectValuesFromRequest ($entity);
	$blocksData = getBlocks ($currentModule, 'create_view', '', $entity->column_fields);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('BLOCKS', $blocksData);
	$smarty->assign ('SELECTED_NEWS', $news);
	$smarty->display ('modules/repercusiones_prensa/MassCreate.tpl');
