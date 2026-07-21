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
	$sortorder = array();
	$current_module_strings = return_module_language($current_language, 'Reports');

	global $list_max_entries_per_page, $urlPrefix;

	/** @noinspection PhpUndefinedClassInspection */
	$log = LoggerManager::getLogger('report_type');

	global $currentModule, $image_path, $theme;
	$report_group=new vtigerCRM_Smarty;
	$report_group->assign('MOD', $mod_strings);
	$report_group->assign('APP', $app_strings);
	$report_group->assign('IMAGE_PATH',$image_path);

	$maxGroupingRows = 10;
	$groupingRows = array();
	
	if(isset($_REQUEST['record']) && $_REQUEST['record']!='') {
		$reportid = vtlib_purify($_REQUEST['record']);
		$oReport = new Reports($reportid);
		$list_array = $oReport->getSelctedSortingColumns($reportid);

		$oRep = new Reports();
		$secondarymodule = '';
		$secondarymodules = array();

		if(!empty($oRep->related_modules[$oReport->primodule])) {
			foreach($oRep->related_modules[$oReport->primodule] as $key => $value){
				if(isset($_REQUEST['secondarymodule_'.$value])) {
					$secondarymodules []= vtlib_purify($_REQUEST['secondarymodule_'.$value]);
				}
			}
		}
		$secondarymodule = implode(':',$secondarymodules);

		if($secondarymodule!='') {
			$oReport->secmodule = $secondarymodule;
		}

		// Generar filas dinámicamente basadas en los datos existentes
		$numExistingRows = count($list_array);
		for($i = 0; $i < max(3, $numExistingRows); $i++) {
			$selected = isset($list_array[$i]) ? $list_array[$i] : '';
			$block = getPrimaryColumnsGroupingHtml($oReport->primodule, $selected);
			$block .= getSecondaryColumnsGroupingHtml($oReport->secmodule, $selected);
			$groupByTime = getGroupByTimeDiv($i + 1, $reportid);
			
			$sortOrder = isset($oReport->ascdescorder[$i]) ? $oReport->ascdescorder[$i] : 'Ascending';
			$ascDesc = getAscDescHtml($sortOrder);
			
			$groupingRows[] = array(
				'BLOCK' => $block,
				'GROUPBYTIME' => $groupByTime,
				'ASCDESC' => $ascDesc
			);
		}
		
		$sortorder = $oReport->ascdescorder;
	} else {
		$primarymodule = vtlib_purify($_REQUEST['primarymodule']);
		$ogReport = new Reports();
		
		// Generar 3 filas por defecto para nuevos informes
		for($i = 0; $i < 3; $i++) {
			$block = getPrimaryColumnsGroupingHtml($primarymodule);
			if(!empty($ogReport->related_modules[$primarymodule])) {
				foreach($ogReport->related_modules[$primarymodule] as $key => $value){
					$block .= getSecondaryColumnsGroupingHtml($_REQUEST['secondarymodule_'.$value]);
				}
			}
			$groupByTime = getGroupByTimeDiv($i + 1);
			$ascDesc = getAscDescHtml('Ascending');
			
			$groupingRows[] = array(
				'BLOCK' => $block,
				'GROUPBYTIME' => $groupByTime,
				'ASCDESC' => $ascDesc
			);
		}
	}
	
	// Asignar las filas al template
	$report_group->assign('GROUPING_ROWS', $groupingRows);
	$report_group->assign('GROUPING_COUNT', count($groupingRows));
	
	// Generar HTML base para JavaScript (sin selected)
	$baseBlock = isset($primarymodule) ? getPrimaryColumnsGroupingHtml($primarymodule) : getPrimaryColumnsGroupingHtml($oReport->primodule);
	if(isset($oReport) && !empty($oReport->secmodule)) {
		$baseBlock .= getSecondaryColumnsGroupingHtml($oReport->secmodule);
	} else if(isset($ogReport) && !empty($ogReport->related_modules[$primarymodule])) {
		foreach($ogReport->related_modules[$primarymodule] as $key => $value){
			if(isset($_REQUEST['secondarymodule_'.$value])) {
				$baseBlock .= getSecondaryColumnsGroupingHtml($_REQUEST['secondarymodule_'.$value]);
			}
		}
	}
	$report_group->assign('BLOCK_HTML', $baseBlock);
	$report_group->assign('ASCDESC_HTML', getAscDescHtml('Ascending'));

	/**
	 *  Function to get the combo values for the Primary module Columns
	 *  @ param $module(module name) :: Type String
	 *  @ param $selected (<selected or ''>) :: Type String
	 *  This function generates the combo values for the columns  for the given module
	 *  and return a HTML string
	 *
	 * @param $module
	 * @param string $selected

	 * @return string
	 */
	function getPrimaryColumnsGroupingHtml($module, $selected = '') {
		global $ogReport;
		global $app_list_strings;
		global $current_language;
		$id_added=false;
		$mod_strings = return_module_language($current_language,$module);
		$shtml = '';
		$block_listed = array();
		$selected = decode_html($selected);
			foreach ($ogReport->module_list[$module] as $value) {
			if (isset($ogReport->pri_module_columnslist[$module][$value]) && !$block_listed[$value]) {
				$block_listed[$value] = true;
				$shtml .= '<optgroup label="'.$app_list_strings['moduleList'][$module].' '.getTranslatedString($value, $module).'" class="select" style="border:none">';
				if ($id_added==false) {
					$is_selected ='';
					if ($selected == 'vtiger_crmentity:crmid:'.$module.'_ID:crmid:I') {
						$is_selected = 'selected';
					}
					$shtml .= '<option value="vtiger_crmentity:crmid:'.$module."_ID:crmid:I\" {$is_selected}>". getTranslatedString($module, $module).' '.getTranslatedString('ID', $module). '</option>';
					$id_added=true;
				}
				$shtml .= auxGetPrimaryColumnsGroupingHtml($ogReport, $module, $value, $selected, $mod_strings, $shtml);
			}
			}
			return $shtml;
	}

	function auxGetPrimaryColumnsGroupingHtml($auxOgReport, $auxModule, $auxValue, $auxSelected, $auxModStrings, $auxShtml) {
		foreach($auxOgReport->pri_module_columnslist[$auxModule][$auxValue] as $field => $fieldlabel) {
			if (isset($auxModStrings[$fieldlabel])) {
				if ($auxSelected == decode_html($field)) {
					$auxShtml .= '<option selected value="'.$field.'">'.$auxModStrings[$fieldlabel].'</option>';
				} else {
					$auxShtml .= '<option value="'.$field.'">'.$auxModStrings[$fieldlabel].'</option>';
				}
			} else {
				if ($auxSelected == decode_html($field)) {
					$auxShtml .= '<option selected value="'.$field.'">'.$fieldlabel.'</option>';
				} else {
					$auxShtml .= '<option value="'.$field.'">'.$fieldlabel.'</option>';
				}
			}
		}
		return $auxShtml;
	}

	/**
	 *  Function to get the combo values for the Secondary module Columns
	 *  @ param $module(module name) :: Type String
	 *  @ param $selected (<selected or ''>) :: Type String
	 *  This function generates the combo values for the columns for the given module
	 *  and return a HTML string
	 *
	 * @param $module
	 * @param string $selected

	 * @return string
	 */
	function getSecondaryColumnsGroupingHtml($module, $selected = '') {
		global $current_language;
		$shtml = '';
		$selected = decode_html($selected);
		if($module != '') {
			$secmodule = explode(':',$module);
			$countSecModule = count($secmodule);
			for($i=0; $i < $countSecModule; $i++) {
				$mod_strings = return_module_language($current_language,$secmodule[$i]);
				if(vtlib_isModuleActive($secmodule[$i])) {
					$shtml .= auxGetSecondaryColumnsGroupingHtml($secmodule, $i, $shtml, $mod_strings, $selected);
				}
			}
		}
		return $shtml;
	}

	function auxGetSecondaryColumnsGroupingHtml($auxSecModule, $auxI, $auxShtml, $auxModStrings, $auxSelected) {
		global $ogReport;
		global $app_list_strings;
		$block_listed = array();
		foreach($ogReport->module_list[$auxSecModule[$auxI]] as $value) {
			if(isset($ogReport->sec_module_columnslist[$auxSecModule[$auxI]][$value]) && !$block_listed[$value]) {
				$block_listed[$value] = true;
				$auxShtml .= '<optgroup label="'.$app_list_strings['moduleList'][$auxSecModule[$auxI]].' '.getTranslatedString($value).'" class="select" style="border:none">';
				foreach($ogReport->sec_module_columnslist[$auxSecModule[$auxI]][$value] as $field => $fieldlabel)
				{
					if(isset($auxModStrings[$fieldlabel])) {
						if ($auxSelected == decode_html($field)) {
							$auxShtml .= '<option selected value="'.$field.'">'.$auxModStrings[$fieldlabel].'</option>';
						} else {
							$auxShtml .= '<option value="'.$field.'">'.$auxModStrings[$fieldlabel].'</option>';
						}
					} else {
						if ($auxSelected == decode_html($field)) {
							$auxShtml .= '<option selected value="'.$field.'">'.$fieldlabel.'</option>';
						} else {
							$auxShtml .= '<option value="'.$field.'">'.$fieldlabel.'</option>';
						}
					}
				}
			}
		}
		return $auxShtml;
	}

	function getGroupByTimeDiv($sortid, $reportid = '') {
		require_once 'include/utils/CommonUtils.php';
		global $adb;
		global $mod_strings;
		$query = 'SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=? AND sortid=?';
		$result = $adb->pquery($query,array($reportid, $sortid));
		$rows = $adb->num_rows($result);
		$yearselected = '';
		$monthselected = '';
		$quarterselected = '';
		$noneselected='';
		if($rows > 0) {
			$displaystyle = 'inline';
			$selected_groupby = $adb->query_result($result,0,'dategroupbycriteria');
			if($selected_groupby == 'Year') {
				$yearselected = 'selected';
			} else if($selected_groupby == 'Month') {
				$monthselected = 'selected';
			} else if($selected_groupby == 'Quarter') {
				$quarterselected = 'selected';
			} else if(strtolower($selected_groupby)=='none') {
				$noneselected='selected';
			}
		} else{
			$displaystyle = 'none';
			$noneselected = 'selected';
		}
		$divid = 'Group'.$sortid.'time';
		$selectid = 'groupbytime'.$sortid;
		$div = '';
		$div .= "<div id=$divid style='display:$displaystyle'>".$mod_strings['LBL_GROUPING_TIME'].'<br>';
		$div .= "<select id=$selectid name=$selectid  class='txtBox'>";
		$div .= "<option value='None' $noneselected>".$mod_strings['LBL_NONE'].'</option>';
		$div .= "<option value='Year' $yearselected>".$mod_strings['LBL_YEAR'].'</option>';
		$div .= "<option value='Month' $monthselected>".$mod_strings['LBL_MONTH'].'</option>';
		$div .= "<option value='Quarter' $quarterselected>".$mod_strings['LBL_QUARTER'].'</option>';
		$div .= '</select></div>';
		return $div;
	}

	$report_group->display('ReportGrouping.tpl');
