<?php
@include("../PortalConfig.php");
setPortalCurrentLanguage();
$default_language = getPortalCurrentLanguage();
@include("../language/$default_language.lang.php");

if(!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] == '')
{
	@header("Location: $Authenticate_Path/login.php");
	exit;
}
?>

<table class="dvtContentSpace" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td align="left">
			<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
			<tr><td  align="left"><span class="lvtHeaderText"><?php echo getTranslatedString("LBL_GRAFICO_CUSTOMERPORTAL"); ?></span></td></tr>
			<tr><td colspan="2"><hr noshade="noshade" size="1" width="100%" align="left">
					<table width="95%"  border="0" cellspacing="0" cellpadding="5" align="left"><tr><td  width="70%" valign="top">

<?php	
		global $result;
		global $client;
		$sessionid = $_SESSION['customer_sessionid'];
		$customerid = $_SESSION['customer_id'];
		$modulo= 'GraficoCustomerPortal';
		$datosgrafico= array();
				  
		if ($customerid != '' ) {
			$params = array('id' => $customerid, 'module'=>$modulo,'sessionid'=>$sessionid);
			$datosgrafico = $client->call('get_data_graficocustomerportal', $params, $Server_Path, $Server_Path);
			//var_dump($datosgrafico);

			$meses=Array("Enero","Febrero",'Marzo',"Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

			$mesesXML = "";
			$contratadoXML = "";
			$consumidoXML = "";
			for ($i=0;$i<12;$i++) {
					if (isset($datosgrafico[$i]['anyomes'])) $anyomes = $datosgrafico[$i]['anyomes']; else continue;
					$mes = (int)substr($anyomes,4,2) - 1;
					$anyo = (int)substr($anyomes,0,4);
					$mesesXML .= "<value xid='$i'>".getTranslatedString($meses[$mes])."-".$anyo."</value>";

					if (isset($datosgrafico[$i]['contratado'])) $dato1 = $datosgrafico[$i]['contratado']; else $dato1 = 0;
					if (isset($datosgrafico[$i]['consumido'])) $dato2 = $datosgrafico[$i]['consumido']; else $dato2 = 0;

					$contratadoXML .= "<value xid='$i'>$dato1</value>";
					$consumidoXML .= "<value xid='$i'>$dato2</value>";
			}
?>								<div align="center"><b><?php echo getTranslatedString('Se muestran las horas consumidas de los contratos de desarrollo del cliente'); ?></b></div>
							<script type="text/javascript" src="GraficoCustomerPortal/amcolumn/swfobject.js"></script>
							<div id="flashcontent"><strong>You need to upgrade your Flash Player</strong></div>
							<script type="text/javascript">
								// <![CDATA[		
								var so = new SWFObject("GraficoCustomerPortal/amcolumn/amcolumn.swf", "amcolumn", "700", "450", "8", "#FFFFFF");
								so.addVariable("path", "GraficoCustomerPortal/amcolumn/");
								so.addVariable("settings_file", encodeURIComponent("GraficoCustomerPortal/amcolumn/amcolumn_settings.xml"));
								so.addVariable("chart_data", encodeURIComponent("<chart><series><?php echo $mesesXML; ?></series><graphs><graph gid='1'><?php echo $consumidoXML; ?></graph><graph gid='2'><?php echo $contratadoXML; ?></graph></graphs></chart>"));                    
								so.write("flashcontent");
								// ]]>
							</script>
					</td><td  width="30%" valign="top">
							<script type="text/javascript">
							function minimizar(tabla){
									if (window.document.getElementById(tabla).style.display!='none') {
											window.document.getElementById(tabla).style.display='none';
									} else {
											window.document.getElementById(tabla).style.display='';
									}
							}
							</script>
							<table width="100%"  border="0" cellspacing="0" cellpadding="5" align="center">
							<tr align="center"><td class='detailedViewHeader' width="50%"><?php echo getTranslatedString('Periodo');?></td>
							<td class='detailedViewHeader'><?php echo getTranslatedString('Horas Contrat.');?></td>
							<td class='detailedViewHeader'><?php echo getTranslatedString('Horas Consum.');?></td>
							<td class='detailedViewHeader'>&nbsp;</td></tr>
<?php 
							$username = $_SESSION['customer_name'];
							$customerid = $_SESSION['customer_id'];
							$sessionid = $_SESSION['customer_sessionid'];
							for ($i=0;$i<12;$i++) {
									if (isset($datosgrafico[$i]['anyomes'])) $anyomes = $datosgrafico[$i]['anyomes']; else continue;
									$mes = (int)substr($anyomes,4,2) - 1;
									$mesx = substr($anyomes,4,2);
									$anyo = (int)substr($anyomes,0,4);
									if (isset($datosgrafico[$i]['contratado'])) $dato1 = $datosgrafico[$i]['contratado']; else $dato1 = 0;
									if (isset($datosgrafico[$i]['consumido'])) $dato2 = $datosgrafico[$i]['consumido']; else $dato2 = 0;
									if ($dato2)
										$icono = "<img src='images/details_icon.gif' border='0' onclick='minimizar(\"tabla".$mes.$anyo."\");'  style='cursor:hand'>"; else $icono = "&nbsp;";
									echo "<tr class='dvtLabel'><td> &nbsp; ".getTranslatedString($meses[$mes])."-$anyo</td><td  style='text-align:center'>$dato1</td><td style='text-align:center'>$dato2</td><td>$icono</td></tr>\n";
									if ($dato2) {
										echo "<tr id='tabla".$mes.$anyo."'  style='border: 1px solid orange; display: none;'><td colspan='4'>\n";
										$where = " cf_688 like '$anyo-$mesx%'  ";
										$params = Array(Array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'user_name' => "$username", 'onlymine' => false, 'where' => "$where", 'match' => ""));
										
										$resultticketsmensuales = $client->call('get_tickets_list', $params, $Server_Path, $Server_Path);
										$data = $resultticketsmensuales[1]['data'];
										if($resultticketsmensuales != '') {
												echo "<table width='100%'  border='0' cellspacing='0' cellpadding='5' align='center'>";
												for($j=0;$j<count($data);$j++) {	
														echo "<tr class='dvtInfo'><td width='15%'> &nbsp; <small><i>".$data[$j][0]['fielddata']."</i></small></td><td width='50%'> &nbsp;  <small><i>".$data[$j][1]['fielddata']."</i></small></td><td width='20%'><small><i>".$data[$j][5]['fielddata']." hs</i></small></td></tr> ";
												}
												echo "</table> &nbsp;<br>\n";
										}
										echo "</td></tr>\n";
									}
							}
?>
							</table>
<?php 	}  ?>

					</td></tr></table>
			</td></tr>
			</table>
</td></tr>
</table>
