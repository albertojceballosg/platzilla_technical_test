<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

require_once('include/utils/UserInfoUtil.php');
require_once('include/utils/utils.php');
global $adb;
$profilename = $name;
$description= $description;
$profile_id = $adb->getUniqueID("vtiger_profile");
//Inserting values into Profile Table
$sql1 = "insert into vtiger_profile(profileid, profilename, description) values(?,?,?)";
$adb->pquery($sql1, array($profile_id,$profilename, $description));

        //Retreiving the vtiger_profileid
        $sql2 = "select max(profileid) as current_id from vtiger_profile";
        $result2 = $adb->pquery($sql2, array());
        $profileid = $adb->query_result($result2,0,'current_id');


        $sqlApssProfile = "UPDATE vtiger_config_applications SET app_profile = ? WHERE config_applicationsid = ?";
		$paramsApssProfile = array($profileid, $rowinsert);
		$adb->pquery($sqlApssProfile, $paramsApssProfile);


	//Retreiving the vtiger_tabs permission array
	//

	//Retreiving the first profileid
	$prof_query="select profileid from vtiger_profile order by profileid ASC";
	$prof_result = $adb->pquery($prof_query, array());
	$first_prof_id = $adb->query_result($prof_result,0,'profileid');

$tab_perr_result = $adb->pquery("select * from vtiger_profile2tab where profileid=?", array($first_prof_id));
$act_perr_result = $adb->pquery("select * from vtiger_profile2standardpermissions where profileid=?", array($first_prof_id));
$act_utility_result = $adb->pquery("select * from vtiger_profile2utility where profileid=?", array($first_prof_id));
$num_tab_per = $adb->num_rows($tab_perr_result);
$num_act_per = $adb->num_rows($act_perr_result);
$num_act_util_per = $adb->num_rows($act_utility_result);


	//profile2tab permissions
	for($i=0; $i<$num_tab_per; $i++)
	{
		$tab_id = $adb->query_result($tab_perr_result,$i,"tabid");
		$request_var = $tab_id.'_tab';
		if($tab_id != 3 && $tab_id != 16)
		{
	        //EN LOS MODULOS ASIGNADOS
			if(in_array($tab_id, $tabsProfile)){
				$permission = 'on';
			}else{
				$permission = '';
			}
			if($permission == 'on')
			{
				$permission_value = 0;
			}
			else
			{
				$permission_value = 1;
			}
			$sql4="insert into vtiger_profile2tab values(?,?,?,NULL)";
            $adb->pquery($sql4, array($profileid, $tab_id, $permission_value));

			if($tab_id ==9)
			{
				$sql4="insert into vtiger_profile2tab values(?,?,?,NULL)";
                $adb->pquery($sql4, array($profileid,16, $permission_value));
			}
		}
	}

	//profile2standard permissions
	for($i=0; $i<$num_act_per; $i++)
	{
		$tab_id = $adb->query_result($act_perr_result,$i,"tabid");
		$action_id = $adb->query_result($act_perr_result,$i,"operation");
		if($tab_id != 16)
		{
			$action_name = getActionname($action_id);
			if($action_name == 'EditView' || $action_name == 'Delete' || $action_name == 'DetailView')
			{
				$request_var = $tab_id.'_'.$action_name;
			}
			elseif($action_name == 'Save')
			{
				$request_var = $tab_id.'_EditView';
			}
			elseif($action_name == 'index')
			{
				$request_var = $tab_id.'_DetailView';
			}

            //EN LOS MODULOS ASIGNADOS
			if(in_array($tab_id, $tabsProfile)){
				$permission = 'on';
			}else{
				$permission = '';
			}

			if($permission == 'on')
			{
				$permission_value = 0;
			}
			else
			{
				$permission_value = 1;
			}

			$sql7="insert into vtiger_profile2standardpermissions values(?,?,?,?)";
            $adb->pquery($sql7, array($profileid, $tab_id, $action_id, $permission_value));

			if($tab_id ==9)
			{
				$sql7="insert into vtiger_profile2standardpermissions values(?,?,?,?)";
                $adb->pquery($sql7, array($profileid, 16, $action_id, $permission_value));
			}
		}
	}

	//Update Profile 2 utility
	for($i=0; $i<$num_act_util_per; $i++)
	{
		$tab_id = $adb->query_result($act_utility_result,$i,"tabid");

		$action_id = $adb->query_result($act_utility_result,$i,"activityid");
		$action_name = getActionname($action_id);
		$request_var = $tab_id.'_'.$action_name;


        //EN LOS MODULOS ASIGNADOS
		if(in_array($tab_id, $tabsProfile)){
			$permission = 'on';
		}else{
			$permission = '';
		}

		if($permission == 'on')
		{
			$permission_value = 0;
		}
		else
		{
			$permission_value = 1;
		}

		$sql9="insert into vtiger_profile2utility values(?,?,?,?)";
        $adb->pquery($sql9, array($profileid, $tab_id, $action_id, $permission_value));

	}


$modArr=getModuleAccessArray();


foreach($modArr as $fld_module => $fld_label)
{
	$fieldListResult = getProfile2FieldList($fld_module, $first_prof_id);
	$noofrows = $adb->num_rows($fieldListResult);
	$tab_id = getTabid($fld_module);
	for($i=0; $i<$noofrows; $i++)
	{
		$fieldid =  $adb->query_result($fieldListResult,$i,"fieldid");

		//EN LOS MODULOS ASIGNADOS
		if(in_array($tab_id, $tabsProfile)){
			$visible = 'on';
		}else{
			$permission = '';
		}

		if($visible == 'on')
		{
			$visible_value = 0;
		}
		else
		{
			$visible_value = 1;
		}


	    $readOnlyValue = '0';


		//Updating the Mandatory vtiger_fields
		$uitype = $adb->query_result($fieldListResult,$i,"uitype");
		$displaytype =  $adb->query_result($fieldListResult,$i,"displaytype");
		$fieldname =  $adb->query_result($fieldListResult,$i,"fieldname");
		$typeofdata = $adb->query_result($fieldListResult,$i,"typeofdata");
		$fieldtype = explode("~",$typeofdata);
       	if($fieldtype[1] == 'M')
   		{
			$visible_value = 0;
		}
		//Updating the database
		$sql11="insert into vtiger_profile2field values(?,?,?,?,?)";
        $adb->pquery($sql11, array($profileid, $tab_id, $fieldid, $visible_value,$readOnlyValue));
	}
}

	$result = $adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE profileid=?', array ($first_prof_id));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			$adb->pquery (
				'INSERT INTO vtiger_profile2customview (profileid, cvid, tabid, permissions) VALUES (?, ?, ?, ?)',
				array ($profileid, $row ['cvid'], $row ['tabid'], $row ['permissions'])
			);
		}
	} else {
		$adb->query (
			'INSERT INTO vtiger_profile2customview (profileid, cvid, tabid, permissions)
			SELECT
				? AS profileid,
				cv.cvid,
				t.tabid,
				0
			FROM
				vtiger_customview cv
				INNER JOIN vtiger_tab t ON t.name=cv.entitytype
			ORDER BY
				profileid,
				cvid',
			array ($profileid)
		);
	}
