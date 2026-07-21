<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Zona Clientes, Time Management</title>
<link type="text/css" rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" type="text/css" href="../../include/js/extjs/qtips.css" />
<script type="text/javascript" src="../../include/js/extjs/ext-base.js"></script>
<script type="text/javascript" src="../../include/js/extjs/ext-all.js"></script>
</head>
<body>

<?php

if (	!isset($_REQUEST['customer_id']) || $_REQUEST['customer_id'] == '' ||
		!isset($_REQUEST['customer_sessionid']) || $_REQUEST['customer_sessionid'] == '' ||
		!isset($_REQUEST['customer_name']) || $_REQUEST['customer_name'] == '' ||
		!isset($_REQUEST['onlymine']) ||
		!isset($_REQUEST['search_match']) ||
		!isset($_REQUEST['search_title']) ||
		!isset($_REQUEST['search_ticketstatus']) ||
		!isset($_REQUEST['search_ticketyear'])
	)
{
	exit;
}

chdir('../');

@include_once("PortalConfig.php");
@include_once("include.php");
@include_once("include/utils/utils.php");
global $default_charset, $default_language;
global $version,$default_language,$result;
$username = trim($_REQUEST['username']);
$password = trim($_REQUEST['pw']);

setPortalCurrentLanguage();
$default_language = getPortalCurrentLanguage();
@include("language/$default_language.lang.php");

function getTicketSearchQuery() {
	if(trim($_REQUEST['search_ticketid']) != '') 	{
		$where .= "vtiger_troubletickets.ticketid = '".addslashes($_REQUEST['search_ticketid'])."'&&&";
	}
	if(trim($_REQUEST['search_title']) != '') {
		$where .= "vtiger_troubletickets.title like '%".addslashes(trim($_REQUEST['search_title']))."%'&&&";
	}
	if(trim($_REQUEST['search_ticketstatus']) != '') 	{
		$where .= "vtiger_troubletickets.status = '".$_REQUEST['search_ticketstatus']."'&&&";
	}
	if(trim($_REQUEST['search_ticketpriority']) != '') {
		$where .= "vtiger_troubletickets.priority = '".$_REQUEST['search_ticketpriority']."'&&&";
	}
	if(trim($_REQUEST['search_ticketcategory']) != '') {
		$where .= "vtiger_troubletickets.category = '".$_REQUEST['search_ticketcategory']."'&&&";
	}
	if(trim($_REQUEST['search_ticketyear']) != '') {
		$where .= "vtiger_crmentity.createdtime LIKE '".$_REQUEST['search_ticketyear']."%'&&&";
	}
	$where = trim($where,'&&&');
	
	return $where;
}

$customerid = $_REQUEST['customer_id'];
$sessionid = $_REQUEST['customer_sessionid'];
$username = $_REQUEST['customer_name'];
$onlymine=$_REQUEST['onlymine'];
$search_title = $_REQUEST['search_title'];
$search_ticketstatus = $_REQUEST['search_ticketstatus'];
$search_ticketyear = $_REQUEST['search_ticketyear'];
$match_condition = $_REQUEST['search_match'];
$where = getTicketSearchQuery();

$params = Array(Array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'user_name' => "$username", 'onlymine' => "$onlymine", 'where' => "$where", 'match' => "$match_condition"));
$result = $client->call('get_tickets_list', $params, $Server_Path, $Server_Path);

$closedlist = '';

if($result != '') {

		$header = $result[0]['head'][0];
		$nooffields = count($header);
		$data = $result[1]['data'];
		$rowcount = count($data);

		$closedlist .= '<div id="scrollTabxx" style="hxxeight:270px;"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		$closedlist .= '<tr>';

		for($i=0; $i<$nooffields; $i++)
		{
			$header_value = $header[$i]['fielddata'];
			if ($header_value != 'description') {
				if (($header_value != 'fecha_de_inicio') && ($header_value != 'fecha_de_cierre') && ($header_value != 'hours') && ($header_value != 'Status'))
						$headerlist .= '<td class="detailedViewHeader"  style="text-align:center" align="center">'.getTranslatedString($header_value).'</td>';
			}
		}

		$closedlist .= $headerlist .
		'<td class="detailedViewHeader"  style="text-align:center" align="center">'.getTranslatedString('LBL_TICKET_fecha_de_inicio').'</td>'.
		'<td class="detailedViewHeader"  style="text-align:center" align="center">'.getTranslatedString('LBL_TICKET_fecha_de_cierre').'</td>'.
		'<td class="detailedViewHeader"  style="text-align:center" align="center">'.getTranslatedString('LBL_TICKET_hours').'</td></tr>';
		//'<td class="detailedViewHeader"  style="text-align:center" align="center">'.getTranslatedString('Status').'</td></tr>';

		$page_tope = 10;
		if (isset($_REQUEST['page']) and ($_REQUEST['page']))
				$page=$_REQUEST['page'];
		else 	$page=1;
		$link_ini 	= ($page-1) * $page_tope + 1;
		$link_end 	= $page * $page_tope;
		$link_kk 	= 0;
		$bufferSalidaToolTip = '';
		for($i=0;$i<count($data);$i++) {
				$ticketlist = '';
				if ($i%2==0) 
					$ticketlist .= '<tr class="dvtLabel" id="row_'.$i.'">';
				else
					$ticketlist .= '<tr class="dvtInfo" id="row_'.$i.'">';

				$ticket_status = '';
				$ticket_dates = '';
				for($j=0; $j<$nooffields; $j++) {
					if ($header[$j]['fielddata'] != 'description') {
						if ($header[$j]['fielddata'] == 'Status') {
							$ticket_status = $data[$i][$j]['fielddata'];
						} elseif($header[$j]['fielddata'] == 'Subject') {
							$Subject = str_replace('<a href="','<a href="../',$data[$i][$j]['fielddata']);
							$Subject = str_replace('">','" target="_top">',$Subject);
							$ticketlist .= '<td align="center" style="text-align:center">'.$Subject.'</td>';
						} elseif (($header[$j]['fielddata'] == 'fecha_de_inicio') || ($header[$j]['fielddata'] == 'fecha_de_cierre') || ($header[$j]['fielddata'] == 'hours') ) {
								$ticket_dates .= '<td align="center"  style="text-align:center">'.$data[$i][$j]['fielddata'].' </td>';
						} else {
							$ticketlist .= '<td align="center" style="text-align:center">'.$data[$i][$j]['fielddata'].' </td>';
						}
					} else {
						$description = $data[$i][$j]['fielddata'];
					}
				}

				if (($ticket_status == getTranslatedString('LBL_STATUS_CLOSED')) || ($ticket_status == getTranslatedString('LBL_STATUS_CLOSED2') ) ) {
						$link_kk++;
						if ( ($link_ini <= $link_kk) and ($link_kk <= $link_end))
								//$closedlist .= $ticketlist . $ticket_dates .'<td>'.getTranslatedString('STATUS_'.$ticket_status).' </td></tr>';
								$closedlist .= $ticketlist . $ticket_dates.'</tr>';

				}
				
				
				$description = str_replace("\n","<br />",$description);
				$description = str_replace("\r", "", $description);
				
				$bufferSalidaToolTip.= "
					new Ext.ToolTip({
						target: 'row_".$i."',
						title: '',
						width:500,
						html: '".nl2br($description)."',
						trackMouse:false
					});
					";
		}

		$closedlist .= '</table></div>';
		// agrego paginador a las cerradas!!
		$pagestotal = ceil($link_kk / $page_tope);

		$closedlist.= '
		<script>'.
		$bufferSalidaToolTip.'
		</script>
		';
		$closedlist.='<table border="0" cellspacing="0" cellpadding="0" align="center"><tr nowrap=nowrap>';
		if ($page == 1)
				$closedlist .= '<td width="40"> |&laquo; </td>';
		else 	$closedlist .= "<td width='40'><a href='?customer_id=$customerid".
																		"&customer_sessionid=$sessionid".
																		"&customer_name=$username".
																		"&onlymine=$onlymine".
																		"&search_match=$match_condition".
																		"&search_title=$search_title".
																		"&search_ticketstatus=$search_ticketstatus".
																		"&search_ticketyear=$search_ticketyear".
																		"&page=1'> |&laquo; </a></td>";
		for($i=1; $i<=$pagestotal; $i++) {
			if ($page == $i)
					$closedlist .= '<td width="40"><b>'.$i.' </b></td>';
			else 	$closedlist .= "<td width='40'><a href='?customer_id=$customerid".
																			"&customer_sessionid=$sessionid".
																			"&customer_name=$username".
																			"&onlymine=$onlymine".
																			"&search_match=$match_condition".
																			"&search_title=$search_title".
																			"&search_ticketstatus=$search_ticketstatus".
																			"&search_ticketyear=$search_ticketyear".
																			"&page=$i'> ".$i." </a></td>";			
		}
		if ($page == $pagestotal)
				$closedlist .= '<td width="40"> &raquo;| </td>';
		else 	$closedlist .= "<td width='40'><a href='?customer_id=$customerid".
																		"&customer_sessionid=$sessionid".
																		"&customer_name=$username".
																		"&onlymine=$onlymine".
																		"&search_match=$match_condition".
																		"&search_title=$search_title".
																		"&search_ticketstatus=$search_ticketstatus".
																		"&search_ticketyear=$search_ticketyear".
																		"&page=$pagestotal'>  &raquo;|  </a></td>";				
		$closedlist .= '</tr></table>';

}
echo $closedlist;

?>
</body>
</html>