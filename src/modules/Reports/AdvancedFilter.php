<?php
	require_once('include/Zend/Json.php');
	if(isset($_REQUEST['record']) && $_REQUEST['record']!='') {
		$reportid = vtlib_purify($_REQUEST['record']);
		$oReport = new Reports($reportid);
		$oReport->getAdvancedFilterList($reportid);

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

		if($secondarymodule!='') {
			$oReport->secmodule = $secondarymodule;
		}

		$COLUMNS_BLOCK = getPrimaryColumnsAdvFilterHtml($oReport->primodule);
		$COLUMNS_BLOCK .= getSecondaryColumnsAdvFilterHtml($oReport->secmodule);
		$report_std_filter->assign('COLUMNS_BLOCK', $COLUMNS_BLOCK);

		$FILTER_OPTION = Reports::getAdvCriteriaHtml();
		$report_std_filter->assign('FOPTION',$FILTER_OPTION);

		$rel_fields = getRelatedFieldColumns();
		/** @noinspection PhpUndefinedClassInspection */
		$report_std_filter->assign('REL_FIELDS',Zend_Json::encode($rel_fields));

		$report_std_filter->assign('CRITERIA_GROUPS',$oReport->advft_criteria);
	} else {
		$primarymodule = $_REQUEST['primarymodule'];
		$COLUMNS_BLOCK = getPrimaryColumnsAdvFilterHtml($primarymodule);
		$ogReport = new Reports();
		if(!empty($ogReport->related_modules[$primarymodule])) {
			foreach($ogReport->related_modules[$primarymodule] as $key => $value) {
				$COLUMNS_BLOCK .= getSecondaryColumnsAdvFilterHtml($_REQUEST['secondarymodule_'.$value]);
			}
		}
		$report_std_filter->assign('COLUMNS_BLOCK', $COLUMNS_BLOCK);

		$rel_fields = getRelatedFieldColumns();
		/** @noinspection PhpUndefinedClassInspection */
		$report_std_filter->assign('REL_FIELDS',Zend_Json::encode($rel_fields));
	}

	/**
	 * Function to get primary columns for an advanced filter
	 * This function accepts The module as an argument
	 * This generate columns of the primary modules for the advanced filter
	 * It returns a HTML string of combo values

	 * @param $module
	 * @param string $selected

	 * @return string
	 */
	function getPrimaryColumnsAdvFilterHtml($module, $selected = '') {
		global $ogReport, $app_list_strings, $current_language;
		$mod_strings = return_module_language($current_language,$module);
		$block_listed = array();
		$shtml = '';
		foreach($ogReport->module_list[$module] as $value) {
			if(isset($ogReport->pri_module_columnslist[$module][$value]) && !$block_listed[$value]) {
				$block_listed[$value] = true;
				$shtml .= '<optgroup label="'.$app_list_strings['moduleList'][$module].' '.getTranslatedString($value).'" class="select" style="border:none">';
				foreach($ogReport->pri_module_columnslist[$module][$value] as $field => $fieldlabel) {
					if(isset($mod_strings[$fieldlabel])) {
						//fix for ticket 5191
						$selected = decode_html($selected);
						$field = decode_html($field);
						//fix ends
						if ($selected == $field) {
							$shtml .= '<option selected value="'.$field.'">'.$mod_strings[$fieldlabel].'</option>';
						} else {
							$shtml .= '<option value="'.$field.'">'.$mod_strings[$fieldlabel].'</option>';
						}
					} else {
						if ($selected == $field) {
							$shtml .= '<option selected value="'.$field.'">'.$fieldlabel.'</option>';
						} else {
							$shtml .= '<option value="'.$field.'">'.$fieldlabel.'</option>';
						}
					}
				}
		    }
		}
		return $shtml;
	}

	/**
	 * Function to get Secondary columns for an advanced filter
	 * This function accepts The module as an argument
	 * This generate columns of the secondary module for the advanced filter
	 * It returns a HTML string of combo values

	 * @param $module
	 * @param string $selected

	 * @return string
	 */
	function getSecondaryColumnsAdvFilterHtml($module, $selected = '') {
		global $app_list_strings;
		global $current_language;
		global $ogReport;
		$shtml = '';

		if ($module != '') {
			$secmodule = explode(':',$module);
			$countSecModule = count($secmodule);
			for($i=0; $i < $countSecModule; $i++) {
				$mod_strings = return_module_language($current_language,$secmodule[$i]);
				if(vtlib_isModuleActive($secmodule[$i])) {
					$block_listed = array();
					foreach($ogReport->module_list[$secmodule[$i]] as $value) {
						if(isset($ogReport->sec_module_columnslist[$secmodule[$i]][$value]) && !$block_listed[$value]) {
							$block_listed[$value] = true;
							$shtml .= '<optgroup label="'.$app_list_strings['moduleList'][$secmodule[$i]].' '.getTranslatedString($value).'" class="select" style="border:none">';
							$shtml .= auxGetSecondaryColumnsAdvFilterHtml($ogReport, $shtml, $secmodule, $i, $value, $selected, $mod_strings);
						}
					}
				}
			}
		}
		return $shtml;
	}

	function auxGetSecondaryColumnsAdvFilterHtml($auxOgReport, $auxShtml, $auxSecModule, $auxI, $auxValue, $auxSelected, $auxModStrings) {
		foreach($auxOgReport->sec_module_columnslist[$auxSecModule[$auxI]][$auxValue] as $field => $fieldlabel) {
			if(isset($auxModStrings[$fieldlabel])) {
				if($auxSelected == $field) {
					$auxShtml .= '<option selected value="'.$field.'">'.$auxModStrings[$fieldlabel].'</option>';
				} else {
					$auxShtml .= '<option value="'.$field.'">'.$auxModStrings[$fieldlabel].'</option>';
				}
			} else {
				if($auxSelected == $field) {
					$auxShtml .= '<option selected value="'.$field.'">'.$fieldlabel.'</option>';
				} else {
					$auxShtml .= '<option value="'.$field.'">'.$fieldlabel.'</option>';
				}
			}
		}
		return $auxShtml;
	}

	function getRelatedColumns($selected = '') {
		global $ogReport;
		$rel_fields = $ogReport->adv_rel_fields;
		if ($selected!='All') {
			$selected = explode(':',$selected);
		}
		$related_fields = array();
		foreach($rel_fields as $i => $index) {
			$shtml='';
			foreach($index as $value) {
				$fieldarray = explode('::',$value);
				$shtml .= '<option value="'.$fieldarray[0].'">'.$fieldarray[1].'</option>';
			}
			$related_fields[$i] = $shtml;
		}
		if (!empty($selected) && $selected[4]!='') {
			return $related_fields[$selected[4]];
		} else if($selected=='All') {
			return $related_fields;
		} else {
			return null;
		}
	}

	function getRelatedFieldColumns() {
		global $ogReport;
		$rel_fields = $ogReport->adv_rel_fields;
		return $rel_fields;
	}
