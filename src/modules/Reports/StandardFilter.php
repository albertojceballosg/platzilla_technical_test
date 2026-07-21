<?php
	require_once('modules/CustomView/CustomView.php');
	global $app_strings;
	global $ogReport;
	global $is_admin;
	if(isset($_REQUEST['record']) == false || $_REQUEST['record']=='') {
	$oReport = new Reports();
	$primarymodule = vtlib_purify($_REQUEST['primarymodule']);

	$blockOne = getPrimaryStdFilterHtml($primarymodule);
	if(!empty($ogReport->related_modules[$primarymodule])) {
	foreach($ogReport->related_modules[$primarymodule] as $key => $value){
			$blockOne .= getSecondaryStdFilterHtml($_REQUEST['secondarymodule_'.$value]);
	}
	}

	$report_std_filter->assign('BLOCK1_STD',$blockOne);
	$BLOCKJS = $oReport->getCriteriaJs();
	$report_std_filter->assign('BLOCKJS_STD',$BLOCKJS);
	$BLOCKCRITERIA = $oReport->getSelectedStdFilterCriteria();
	$report_std_filter->assign('BLOCKCRITERIA_STD',$BLOCKCRITERIA);
	} else if(isset($_REQUEST['record']) == true) {
	//added to fix the ticket #5117
	global $current_user;
	$local_user = clone $current_user;
	require('user_privileges/user_privileges.php');

	$reportid = vtlib_purify($_REQUEST['record']);
	$oReport = new Reports($reportid);
	$oReport->getSelectedStandardCriteria($reportid);

	$oRep = new Reports();
	$secondarymodule = '';
	$secondarymodules =array();

	if(!empty($oRep->related_modules[$oReport->primodule])) {
		foreach($oRep->related_modules[$oReport->primodule] as $key => $value){
			if(isset($_REQUEST['secondarymodule_'.$value])) {
	$secondarymodules []= $_REQUEST['secondarymodule_'.$value];
			}
		}
	}
	$secondarymodule = implode(':',$secondarymodules);

	if($secondarymodule!='') {
		$oReport->secmodule = $secondarymodule;
	}

	$blockOne = getPrimaryStdFilterHtml($oReport->primodule,$oReport->stdselectedcolumn);
	$blockOne .= getSecondaryStdFilterHtml($oReport->secmodule,$oReport->stdselectedcolumn);
	//added to fix the ticket #5117
	$selectedcolumnvalue = '"'. $oReport->stdselectedcolumn . '"';
	if (!$is_admin && isset($oReport->stdselectedcolumn) && strpos($blockOne, $selectedcolumnvalue) === false) {
		$blockOne .= "<option selected value='Not Accessible'>".$app_strings['LBL_NOT_ACCESSIBLE'].'</option>';
	}

	$report_std_filter->assign('BLOCK1_STD',$blockOne);

	$BLOCKJS = $oReport->getCriteriaJs();
	$report_std_filter->assign('BLOCKJS_STD',$BLOCKJS);

	$BLOCKCRITERIA = $oReport->getSelectedStdFilterCriteria($oReport->stdselectedfilter);
	$report_std_filter->assign('BLOCKCRITERIA_STD',$BLOCKCRITERIA);

	if(isset($oReport->startdate) && isset($oReport->enddate)) {
		$report_std_filter->assign('STARTDATE_STD', DateTimeField::convertToUserFormat($oReport->startdate));
		$report_std_filter->assign('ENDDATE_STD', DateTimeField::convertToUserFormat($oReport->enddate));
	} else {
		$report_std_filter->assign('STARTDATE_STD',$oReport->startdate);
		$report_std_filter->assign('ENDDATE_STD',$oReport->enddate);
	}
	}

	/**
	 * Function to get the HTML strings for the primarymodule standard filters

	 * @ param $module : Type String
	 * @ param $selected : Type String(optional)
	 *  This Returns a HTML combo srings

	 * @param $module
	 * @param string $selected

	 * @return string
	 */
	function getPrimaryStdFilterHtml($module, $selected = '') {
	global $ogReport;
	global $current_language;
	$shtml = '';
	$secmodule = array();
	$i = 0;
	$ogReport->oCustomView=new CustomView();
	$result = $ogReport->oCustomView->getStdCriteriaByModule($module);
	$mod_strings = return_module_language($current_language,$module);

	if(isset($result)) {
		foreach($result as $key => $value)
		{
			if(isset($mod_strings[$value])) {
				if($key == $selected) {
					$shtml .= '<option selected value="'.$key.'">'.getTranslatedString($module,$module).' - '.getTranslatedString($value,$secmodule[$i]).'</option>';
				} else
				{
					$shtml .= '<option value="'.$key.'">'.getTranslatedString($module,$module).' - '.getTranslatedString($value,$secmodule[$i]).'</option>';
				}
			} else
			{
				if($key == $selected) {
					$shtml .= '<option selected value="'.$key.'">'.getTranslatedString($module,$module).' - '.$value.'</option>';
			    } else
				{
					$shtml .= '<option value="'.$key.'">'.getTranslatedString($module,$module).' - '.$value.'</option>';
			    }
			}
		}
	}

	return $shtml;
	}

	/**
	 * Function to get the HTML strings for the secondary  standard filters

	 * @ param $module : Type String
	 * @ param $selected : Type String(optional)
	 *  This Returns a HTML combo srings for the secondary modules

	 * @param $module
	 * @param string $selected

	 * @return string
	 */
	function getSecondaryStdFilterHtml($module, $selected = '') {
	global $ogReport;
	$shtml = '';
	$ogReport->oCustomView=new CustomView();
	if($module != '') {
			$secmodule = explode(':',$module);
			$shtml = auxGetSecondaryStdFilterHtml($selected, $ogReport, $shtml, $secmodule);
	}
	return $shtml;
	}

	function auxGetSecondaryStdFilterHtml($auxSelected, $auxOgReport, $auxShtml, $auxSecmodule) {
	global $current_language;
	$countSecModule = count($auxSecmodule);
	for($i=0; $i < $countSecModule; $i++) {
		$result = $auxOgReport->oCustomView->getStdCriteriaByModule($auxSecmodule[$i]);
		$mod_strings = return_module_language($current_language,$auxSecmodule[$i]);
		if(isset($result)) {
			foreach($result as $key => $value) {
				if(isset($mod_strings[$value])) {
					if($key == $auxSelected) {
						$auxShtml .= '<option selected value="'.$key.'">'.getTranslatedString($auxSecmodule[$i],$auxSecmodule[$i]).' - '.getTranslatedString($value,$auxSecmodule[$i]).'</option>';
					} else {
						$auxShtml .= '<option value="'.$key.'">'.getTranslatedString($auxSecmodule[$i],$auxSecmodule[$i]).' - '.getTranslatedString($value,$auxSecmodule[$i]).'</option>';
					}
				} else {
					if($key == $auxSelected) {
						$auxShtml .= '<option selected value="'.$key.'">'.getTranslatedString($auxSecmodule[$i],$auxSecmodule[$i]).' - '.$value.'</option>';
				    } else {
						$auxShtml .= '<option value="'.$key.'">'.getTranslatedString($auxSecmodule[$i],$auxSecmodule[$i]).' - '.$value.'</option>';
				    }
				}
			}
		}
	}
	return $auxShtml;
	}
