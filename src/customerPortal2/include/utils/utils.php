<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

global $result;
global $client;

function checkFileAccess($filepath) {
	$root_directory = '';

	// Set the base directory to compare with
	$use_root_directory = $root_directory;
	if(empty($use_root_directory)) {
		$use_root_directory = realpath(dirname(__FILE__).'/../../.');
	}

	$realfilepath = realpath($filepath);

	/** Replace all \\ with \ first */
	$realfilepath = str_replace('\\\\', '\\', $realfilepath);
	$rootdirpath  = str_replace('\\\\', '\\', $use_root_directory);

	/** Replace all \ with / now */
	$realfilepath = str_replace('\\', '/', $realfilepath);
	$rootdirpath  = str_replace('\\', '/', $rootdirpath);

	if(stripos($realfilepath, $rootdirpath) !== 0) {
		die("Sorry! Attempt to access restricted file.");
	}
	return true;
}

function getblock_header($str,$headerspan='4',$ticketcloselink=false)
{
	if ($ticketcloselink == true) {
		$list .='<tr><td colspan="'. ($headerspan-1) .'" class="detailedViewHeader"><b>'.getTranslatedString($str).'</b></td>';
		$list .='<td class="detailedViewHeader"><div align="right">'.$ticketcloselink.'</div></td></tr>';
	} else {
		$list .='<tr><td colspan="'. ($headerspan) .'" class="detailedViewHeader"><b>'.getTranslatedString($str).'</b></td></tr>';
	}
	return $list;

}


function getblock_fieldlist($block_array)
{
	$list='';$z=0;
	$field_count=count($block_array);
	if($field_count != 0)
	{
		for($i=0;$i<$field_count;$i++,$z++)
		{
			$blockname = $block_array[$i]['blockname'];
			$data = $block_array[$i]['fieldvalue'];
			if($block_array[$i]['fieldlabel'] == 'Note'){
    			$data = html_entity_decode($data);
    		}
    		if($data =='') $data ='&nbsp;';
			if(strcmp($blockname,$block_array[$i-1]['blockname'])){
				//if($blockname != 'Ticket Information') //hardcoded for tickets information block so that it ll not be displayed
				$list .= getblock_header($blockname);
				$z = 0;
			}
			$list .= '<tr>';
			$list .= '<td class="dvtCellLabel" width="20%" align="right">'.getTranslatedString($block_array[$i][fieldlabel]).'</td>';
			$list .= '<td class="dvtCellInfo"     width="80%" >'.$data.'</td>';
			$list .= '</tr>';
		}
	}
	$list.= '<tr><td colspan="2">&nbsp;</td></tr>';
	return $list;
}

function getTranslatedString($str)
{
	global $app_strings;
	return (isset($app_strings[$str]))?$app_strings[$str]:$str;
}

// The function to get html format list data
// input array
// output htmlsource list array
//only for product
function getblock_fieldlistview_product($block_array,$module)
{

 $header = array();
 $header[0] = getTranslatedString($module);
 $header[1] = getTranslatedString('QUOTE_RELATED').getTranslatedString($module);
 $header[2] = getTranslatedString('INVOICE_RELATED').getTranslatedString($module);

 if($block_array == '')
 {
	$list.='<tr><td>'.$module." ".getTranslatedString('LBL_NOT_AVAILABLE').'</td></tr>';
	return $list;
 }

for($k=0;$k<=2;$k++)
{

$header_arr =$block_array[$k][$module]['head'][0];
$nooffields=count($header_arr);
$data_arr=$block_array[$k][$module]['data'];
	$noofdata=count($data_arr);
	$list.=getblock_header($header[$k],$nooffields);
	if($block_array[$k][$module]['data'] == ''){
	$list.='<tr><td>'.$header[$k]." ".getTranslatedString('LBL_NOT_AVAILABLE').'</td></tr>';
	}
	if($nooffields!='')
	{

		$list .='<tr class="detailedViewHeader" align="center">';
		for($i=0;$i<$nooffields;$i++)
			$list .= "<td>".getTranslatedString($header_arr[$i]['fielddata'])."</td>";
		$list .='</tr>';
	}
	if($noofdata != '')
	{
		for($j=0;$j<$noofdata;$j++)
		{
			if($j==0||$j%2==0)
				$list .='<tr class="dvtLabel">';
			else
				$list .='<tr class="dvtInfo">';

			for($i=0;$i<$nooffields;$i++)
			{
				$data =$data_arr[$j][$i]['fielddata'];
				if($data == '')
					$data ='&nbsp;';
				$list .= "<td>".$data."</td>";
			}
			$list .='</tr>';
		}
	}
        $list .= '<tr><td colspan ="'.$nooffields.'">&nbsp;</td></tr>';
}
return $list;
}

// The function to get html format list data
// input array
// output htmlsource list array
//for quotes,notes and invoice

function getblock_fieldlistview($block_array,$block)
{
	if($block_array[0] == "#MODULE INACTIVE#"){
		$list.='<tr><td>'.getTranslatedString($block)." ".getTranslatedString('MODULE_INACTIVE').'</td></tr>';
		return $list;
	}
	if($block_array == ''){
		$list.='<tr><td>'.getTranslatedString($block)." ".getTranslatedString('LBL_NOT_AVAILABLE').'</td></tr>';
		return $list;
	}
	$header_arr =$block_array[0][$block]['head'][0];
	$nooffields=count($header_arr);
	$data_arr=$block_array[1][$block]['data'];
	$noofdata=count($data_arr);
	if($nooffields!='')
	  {
		$list .='<tr align="center">';
		for($i=0;$i<$nooffields;$i++){
		  if(getTranslatedString($header_arr[$i]['fielddata'])=='Enviado' || getTranslatedString($header_arr[$i]['fielddata'])=='Estado')
		    $list .= "<td  class='detailedViewHeader' width='100px' >".getTranslatedString($header_arr[$i]['fielddata'])."</td>";
		  else
		    $list .= "<td  class='detailedViewHeader' >".getTranslatedString($header_arr[$i]['fielddata'])."</td>";
		}
		$list .='</tr>';
	}
	if($_REQUEST["module"]=='Notifications'){
	  if($noofdata != '')
	  {
		  for($j=0;$j<$noofdata;$j++)
		  {
			  if($j==0 || $j%2==0)
				  $list .='<tr class="dvtLabel">';
			  else
				  $list .='<tr class="dvtInfo">';

			  for($i=0;$i<$nooffields;$i++)
			  {
				  $data =$data_arr[$j][$i]['fielddata'];
				  //if ($header_arr[$i]['fielddata'] == 'Estado') $data = getTranslatedString('STATUS_'.$data);
				  // $data = $header_arr[$i]['fielddata'];
				  if($data == '')
					  $data ='&nbsp;';
				  $list .= "<td>".$data."</td>";
			  }
			  $list .='</tr>';
		  }

		$list .= '<tr><td colspan ="'.$nooffields.'">&nbsp;</td></tr>';
	  }

	} else {
	  if($noofdata != '')
	  {
		  for($j=0;$j<$noofdata;$j++)
		  {
			  if($j==0 || $j%2==0)
				  $list .='<tr class="dvtLabel">';
			  else
				  $list .='<tr class="dvtInfo">';

			  for($i=0;$i<$nooffields;$i++)
			  {
				  $data =$data_arr[$j][$i]['fielddata'];
				  //if ($header_arr[$i]['fielddata'] == 'Estado') $data = getTranslatedString('STATUS_'.$data);
				  // $data = $header_arr[$i]['fielddata'];
				  if($data == '')
					  $data ='&nbsp;';
				  $list .= "<td>".$data."</td>";
			  }
			  $list .='</tr>';
		  }

		$list .= '<tr><td colspan ="'.$nooffields.'">&nbsp;</td></tr>';
	  }
	}
return $list;
}






// The function to get html format url_list data
// input array
// output htmlsource list array
function getblockurl_fieldlistview($block_array,$block)
{
	$header_arr =$block_array[0][$block]['head'][0][0];
	$nooffields=count($header_arr);
	$data_arr=$block_array[1][$block]['data'];
	$noofdata=count($data_arr);
	if($nooffields!='')
	{
		$list .='<tr class="detailedViewHeader" align="center">';
		for($i=0;$i<$nooffields;$i++)
			$list .= "<td>".getTranslatedString($header_arr[$i]['fielddata'])."</td>";
		$list .='</tr>';
	}
	if($noofdata != '')
	{
		for($j=0;$j<$noofdata;$j++)
		{
			for($j1=0;$j1<count($data_arr[$j]);$j1++)
			{
				if($j1== '0'||$j1%2==0)
					$list .='<tr class="dvtLabel">';
				else
					$list .='<tr class="dvtInfo">';

				for($i=0;$i<$nooffields;$i++)
				{
					$data = $data_arr[$j][$j1][$i]['fielddata'];
					if($data =='')
						$data ='&nbsp;';
					if($i == '0' || $i== '1')
					{	if($j1 == '0')
						$list .= '<td rowspan="'.count($data_arr[$j]).'" >'.$data."</td>";
					}
					else
						$list .= "<td>".$data."</td>";
				}
				$list .='</tr>';
			}
		}
	}
        $list .= '<tr><td colspan ="'.$nooffields.'">&nbsp;</td></tr>';

return $list;
}
/* 	Function to Show the languages in the Login page
*	Takes an array from PortalConfig.php file $language
*	Returns a list of available Language
*/
function getPortalLanguages() {
	global $languages,$default_language;
	foreach($languages as $name => $label) {
		if(strcmp($default_language,$name) == 0){
			$list .= '<option value='.$name.' selected>'.$label.'</option>';
		} else {
			$list .= '<option value='.$name.'>'.$label.'</option>';
		}
	}
	return $list;
}
/*	Function to set the Current Language
 * 	Sets the Session with the Current Language
 */
function setPortalCurrentLanguage() {
	global $default_language;
	if(isset($_REQUEST['login_language']) && $_REQUEST['login_language'] != ''){
		$_SESSION['portal_login_language'] = $_REQUEST['login_language'];
		$default_language = $_REQUEST['login_language'];
	} /*else {
		$default_language = $_SESSION['portal_login_language'];
	}*/
}

/*	Function to get the Current Language
 * 	Returns the Current Language
 */
function getPortalCurrentLanguage() {
	global $default_language;
	if(isset($_SESSION['portal_login_language']) && $_SESSION['portal_login_language'] != ''){
		$default_language = $_SESSION['portal_login_language'];
	}
	return $default_language;
}

function from_html($string, $encode=true){
	if(is_string($string)){
		if(eregi('(script).*(/script)',$string))
			$string=preg_replace(array('/</', '/>/', '/"/'), array('&lt;', '&gt;', '&quot;'), $string);
	}
       return $string;
}
