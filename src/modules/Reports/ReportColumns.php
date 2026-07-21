<?php
	require_once('Smarty_setup.php');
	require_once('data/Tracker.php');
	require_once('include/logging.php');
	require_once('include/utils/utils.php');
	require_once('modules/Reports/Reports.php');
	global $app_strings;
	global $app_list_strings;
	global $mod_strings;
	global $current_language;
	$current_module_strings = return_module_language($current_language, 'Reports');

	global $list_max_entries_per_page;
	global $urlPrefix;

	/** @noinspection PhpUndefinedClassInspection */
	$log = LoggerManager::getLogger('report_type');

	global $currentModule;
	global $image_path;
	global $theme;
	$theme_path='themes/'.$theme.'/';
	$report_column=new vtigerCRM_Smarty;
	$report_column->assign('MOD', $mod_strings);
	$report_column->assign('APP', $app_strings);
	$report_column->assign('IMAGE_PATH',$image_path);
	$report_column->assign('THEME_PATH',$theme_path);
	if(isset($_REQUEST['record']) && $_REQUEST['record']!='') {
	$recordid = vtlib_purify($_REQUEST['record']);
	$oReport = new Reports($recordid);
	$blockOne = getPrimaryColumnsHtml($oReport->primodule);

	$oRep = new Reports();
	$secondarymodule = '';
	$secondarymodules = array();
	if(!empty($oRep->related_modules[$oReport->primodule])) {
		foreach($oRep->related_modules[$oReport->primodule] as $key => $value){
			if(isset($_REQUEST['secondarymodule_'.$value])) {
	$secondarymodules []= $_REQUEST['secondarymodule_'.$value];
			}
		}
	}
	$secondarymodule = implode(':',$secondarymodules);

	$oReport->secmodule = $secondarymodule;
	$blockOne .= getSecondaryColumnsHtml($oReport->secmodule);
	$blockTwo = $oReport->getSelectedColumnsList($recordid);
	$report_column->assign('BLOCK1',$blockOne);
	$report_column->assign('BLOCK2',$blockTwo);
	} else
{
	$primarymodule = vtlib_purify($_REQUEST['primarymodule']);
	$blockOne = getPrimaryColumnsHtml($primarymodule);
	$ogReport = new Reports();
	if(!empty($ogReport->related_modules[$primarymodule])) {
		foreach($ogReport->related_modules[$primarymodule] as $key => $value){
			$blockOne .= getSecondaryColumnsHtml($_REQUEST['secondarymodule_'.$value]);
		}
	}

	$report_column->assign('BLOCK1',$blockOne);
	}

	/**
	 * Function to formulate the vtiger_fields for the primary modules
	 *  This function accepts the module name
	 *  as arguments and generates the vtiger_fields for the primary module as
	 *  a HTML Combo values

	 * @param $module

	 * @return string
	 */
	function getPrimaryColumnsHtml($module) {
	global $ogReport;
	global $current_language;
	$shtml = '';
	$id_added=false;
	$mod_strings = return_module_language($current_language,$module);
	$block_listed = array();
	foreach($ogReport->module_list[$module] as $value) {
		if(isset($ogReport->pri_module_columnslist[$module][$value]) && !$block_listed[$value]) {
			$block_listed[$value] = true;
			$shtml .= '<optgroup label="'.getTranslatedString($module, $module).' '.getTranslatedString($value).'" class="select" style="border:none">';
			if($id_added==false) {
				$shtml .= '<option value="vtiger_crmentity:crmid:'.$module.'_ID:crmid:I">'.getTranslatedString($module.' ID', $module).'</option>';
				$id_added=true;
			}
			foreach($ogReport->pri_module_columnslist[$module][$value] as $field => $fieldlabel) {
				if(isset($mod_strings[$fieldlabel])) {
					$shtml .= '<option value="'.$field.'">'.$mod_strings[$fieldlabel].'</option>';
				} else {
					$shtml .= '<option value="'.$field.'">'.$fieldlabel.'</option>';
				}
			}
		}
	}
	return $shtml;
	}

	/**
	 * Function to formulate the vtiger_fields for the secondary modules
	 *  This function accepts the module name
	 *  as arguments and generates the vtiger_fields for the secondary module as
	 *  a HTML Combo values

	 * @param $module

	 * @return string
	 */
	function getSecondaryColumnsHtml($module) {
	global $current_language;
	$shtml = '';
	if ($module != '') {
		$secmodule = explode(':',$module);
		$countSecModule = count($secmodule);
		for ($i=0; $i < $countSecModule; $i++) {
			$mod_strings = return_module_language($current_language,$secmodule[$i]);
			$shtml .= auxGetSecondaryColumnsHtml($secmodule, $i, $mod_strings, $shtml);
		}
	}
	return $shtml;
	}

	function auxGetSecondaryColumnsHtml($auxSecModule, $auxI, $auxModStrings, $auxShtml) {
	global $ogReport;
	global $app_list_strings;
	if (vtlib_isModuleActive($auxSecModule[$auxI])) {
		$block_listed = array();
		foreach ($ogReport->module_list[$auxSecModule[$auxI]] as $value) {
			if (isset($ogReport->sec_module_columnslist[$auxSecModule[$auxI]][$value]) && !$block_listed[$value]) {
				$block_listed[$value] = true;
				$auxShtml .= '<optgroup label="'.$app_list_strings['moduleList'][$auxSecModule[$auxI]].' '.getTranslatedString($value).'" class="select" style="border:none">';
				foreach ($ogReport->sec_module_columnslist[$auxSecModule[$auxI]][$value] as $field => $fieldlabel) {
					if (isset($auxModStrings[$fieldlabel])) {
						$auxShtml .= '<option value="'.$field.'">'.$auxModStrings[$fieldlabel].'</option>';
					} else {
						$auxShtml .= '<option value="'.$field.'">'.$fieldlabel.'</option>';
					}
				}
			}
		}
	}
	return $auxShtml;
	}

	$report_column->display('ReportColumns.tpl');
