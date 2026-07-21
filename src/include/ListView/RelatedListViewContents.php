<?php
	require_once ('Smarty_setup.php');
	require_once ('include/ListView/RelatedListViewSession.php');

	global $app_strings, $currentModule, $mod_strings, $theme;

	if ($ajaxaction == "LOADRELATEDLIST") {
		global $relationId, $modalEdit, $adb;
		$relationId = vtlib_purify ($_REQUEST ['relation_id']);
		$modalEdit  = getRelatedListProperty ($relationId, 'modaledit');
		if ((!empty ($relationId)) && ($relationId > 0)) {
			$recordid = vtlib_purify ($_REQUEST ['record']);
			if ($_SESSION ['rlvs'][ $currentModule ][ $relationId ]['currentRecord'] != $recordid) {
				$resetCookie = true;
			} else {
				$resetCookie = false;
			}
			$_SESSION ['rlvs'][ $currentModule ][ $relationId ]['currentRecord'] = $recordid;
			$actions                                                            = vtlib_purify ($_REQUEST ['actions']);
			$header                                                             = vtlib_purify ($_REQUEST ['header']);
			if (!isset($modObj) && isset($cntObj)) {
				$modObj = $cntObj;
			}
			$modObj->id    = $recordid;
			$relationInfo  = getRelatedListInfoById ($relationId);
			$relatedModule = getTabModuleName ($relationInfo ['relatedTabId']);
			$function_name = $relationInfo ['functionName'];

			$relatedListData = $modObj->$function_name(
				$recordid, getTabid ($currentModule),
				$relationInfo['relatedTabId'],
				$actions,
				false,
				$relationId
			);

			$theme_path = "themes/" . $theme . "/";
			$image_path = $theme_path . "images/";

			RelatedListViewSession::addRelatedModuleToSession ($relationId, $header);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ("MOD", $mod_strings);
			$smarty->assign ("APP", $app_strings);
			$smarty->assign ("THEME", $theme);
			$smarty->assign ("IMAGE_PATH", $image_path);
			$smarty->assign ("ID", $recordid);
			$smarty->assign ("MODULE", $currentModule);
			$smarty->assign ("RELATED_MODULE", $relatedModule);
			$smarty->assign ("HEADER", $header);
			$smarty->assign ("RELATEDLISTDATA", $relatedListData);
			$smarty->display ("RelatedListDataContents.tpl");
			if (is_array ($relatedListData)) {
				$smarty->assign ('RESET_COOKIE', $resetCookie);
			}
		}
	} else if ($ajaxaction == "DISABLEMODULE") {
		$relationId = vtlib_purify ($_REQUEST ['relation_id']);
		if (!empty($relationId) && ((int) $relationId) > 0) {
			$header = vtlib_purify ($_REQUEST ['header']);
			require_once ('include/ListView/RelatedListViewSession.php');
			RelatedListViewSession::removeRelatedModuleFromSession ($relationId, $header);
		}
		echo "SUCCESS";
	}
