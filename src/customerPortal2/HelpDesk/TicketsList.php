<?php
$onlymine=$_REQUEST['onlymine'];

if($onlymine == 'true') {
    $mine_selected = 'selected';
    $all_selected = '';
} else {
    $mine_selected = '';
    $all_selected = 'selected';
}
?>
	<span class="lvtHeaderText">
	</span>
	</td><td align="right"   width="50%">
	<form name="index" method="post" action="index.php">
	<input type="hidden" name="module">
	<input type="hidden" name="action">
	<input type="hidden" name="fun">
	<input type="hidden" name="login_language" value="<?php echo $default_language;?>">
	<input class="crmbutton small cancel" name="newticket" type="submit" value="<?PHP echo getTranslatedString('LBL_NEW_TICKET');?>" onclick="this.form.module.value='HelpDesk';this.form.action.value='index';this.form.fun.value='newticket'">&nbsp;&nbsp;&nbsp;
	<input class="crmbutton small cancel" name="srch" type="button" value="<?PHP echo getTranslatedString('LBL_SEARCH');?>" onClick="showSearchFormNow('tabSrch','HelpDesk');">
	</form>
	</td></tr>
</table>
</td></tr>
</table>
</td></tr>

<?PHP

global $result;
$list = '';

if($result == '') {
		$list .= '<tr><td>';
		$list .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		$list .= '<tr><td class="pageTitle">'.getTranslatedString('LBL_NONE_SUBMITTED').'</td></tr></table>';
		$list .= '</td></tr>';
} else {

		$header = $result[0]['head'][0];
		$nooffields = count($header);
		$data = $result[1]['data'];
		$rowcount = count($data);
		
		$list .= '<tr><td colspan="2">';
		$list .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		$list .= '<tr><td class="mnu">'.getTranslatedString('LBL_MY_OPEN_TICKETS').'</td></tr></table>';
		$list .= '<div id="scrollTab" style="height:135px;"><table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		$list .= '<tr>';

		for($i=0; $i<$nooffields; $i++)
		{
			$header_value = $header[$i]['fielddata'];
			if ($header_value != 'description' && $header_value != 'ticketid') {
				if (($header_value != 'fecha_de_inicio') && ($header_value != 'fecha_de_cierre') && ($header_value != 'hours') && ($header_value != 'Status')) {
						$headerlist .= '<td class="detailedViewHeader"  style="text-align:center" align="center">'.getTranslatedString($header_value).'</td>';
				}
			}
		}

		$list .= $headerlist . '<td class="detailedViewHeader"  style="text-align:center" align="center">'.getTranslatedString('Status').'</td>';
		$list .=  '<td class="detailedViewHeader"  style="text-align:center" align="center">&nbsp;</td></tr>';

		for($i=0;$i<count($data);$i++) {
				$ticketlist = '';
				if ($i%2==0)
					$ticketlist .= '<tr class="dvtLabel">';
				else 
					$ticketlist .= '<tr class="dvtInfo">';

				$ticket_status = '';
				$ticket_dates = '';
				for($j=0; $j<$nooffields; $j++) {
					if ($header[$j]['fielddata'] == 'ticketid') {
						$ticketid = $data[$i][$j]['fielddata'];
					}
					elseif ($header[$j]['fielddata'] != 'description') {
						if ($header[$j]['fielddata'] == 'Status') {
							$ticket_status = $data[$i][$j]['fielddata'];
						} elseif (($header[$j]['fielddata'] == 'fecha_de_inicio') || ($header[$j]['fielddata'] == 'fecha_de_cierre') || ($header[$j]['fielddata'] == 'hours') ) {
								$ticket_dates .= '<td align="center"  style="text-align:center">'.$data[$i][$j]['fielddata'].' </td>';
						} else {
							$ticketlist .= '<td align="center" style="text-align:center">'.$data[$i][$j]['fielddata'].' </td>';
						}
					} else {
						$description = $data[$i][$j]['fielddata'];
					}
				}
				
				$enlaceNotificacion = '<img src="../themes/images/email.gif" style="cursor:pointer" onclick="document.getElementById(\'ticketid\').value = \''.$ticketid.'\';jQuery(\'#dlgNuevaNotificacion\').slideDown();">';


				if(preg_match('/17. Dev terminado/', $ticket_status)==1 || preg_match('/16. Realizar Doc/', $ticket_status)==1 ){ 
						$list .= $ticketlist .'<td><strong>En espera de validaci&oacute;n</strong></td>';
						$list.= '<td>'.$enlaceNotificacion.'</td></tr>';
				} elseif (preg_match('/13. Dev validado/', $ticket_status)==1){ 
						$list .= $ticketlist .'<td>Petici&oacute;n Validada</td>';
						$list.= '<td>'.$enlaceNotificacion.'</td></tr>';
						
				} elseif (preg_match('/18. Pendiente de Validaci/', $ticket_status)==1){ 
						$list .= $ticketlist .'<td style="font-weight:bolder;Color:Red;"><p>Validar Petici&oacute;n</p></td>';
						$list.= '<td>'.$enlaceNotificacion.'</td></tr>';
	
				} elseif (($ticket_status != '') && ($ticket_status != getTranslatedString('LBL_STATUS_CLOSED')) && ($ticket_status != getTranslatedString('LBL_STATUS_CLOSED2')) ) {
						$list .= $ticketlist .'<td>'.getTranslatedString('STATUS_'.$ticket_status).' </td>';
						$list.= '<td>'.$enlaceNotificacion.'</td></tr>';
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

		$list .= '</table></div></td></tr>';

		$list .= '<tr><td colspan="2">';
		$list .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		$list .= '<tr><td class="mnu">'.getTranslatedString('LBL_CLOSED_TICKETS').'</td></tr></table>';
		$list .= "<iframe src='HelpDesk/TicketsListClosed.php?customer_id=".$_SESSION['customer_id'].
																						"&customer_sessionid=".$_SESSION['customer_sessionid'].
																						"&customer_name=".$_SESSION['customer_name'].
																						"&onlymine=".$_REQUEST['onlymine'].
																						"&search_match=".$_REQUEST['search_match'].
																						"&search_title=".$_REQUEST['search_title'].
																						"&search_ticketstatus=".$_REQUEST['search_ticketstatus'].
																						"&search_ticketyear=".$_REQUEST['search_ticketyear'].
																						"&login_language=".$default_language.
																						"' width='100%' height='300' frameborder='0'></iframe>";
		$list .= '</td></tr></table>';
}
echo $list;

require_once("../time/modules/notificaciones/language/".$default_language.".lang.php");
include('../include/utils/interfazAuxiliar.php');
include("../time/modules/notificaciones/notificaciones.php");

$obj = new CNotificaciones;
$obj->asignarDatosContacto($accountid,$customerid);

echo $obj->escribeSoloFormaEnviarNotificacion();

?>
