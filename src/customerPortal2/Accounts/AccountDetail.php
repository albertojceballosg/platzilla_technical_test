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
global $Server_Path;

$customerid = $_SESSION['customer_id'];
$sessionid = $_SESSION['customer_sessionid'];
$mode = '';
$page = '1';
$from_date = '';
$to_date = '';
//echo $Server_Path;
//echo $client;
//echo $result;
//echo $module;
function EscribeFormaReporteFactura($module,$page,$accoundid)
{
	echo '
			<form border="0" action="index.php" method="post" name="form" id="form">
			<input type="hidden" name="module" value ="'.$module.'">
			<input type="hidden" name="action" value ="index">
			<input type="hidden" name="mode" value ="">
			<input type="hidden" name="page" value ="'.$page.'">
	        <input type="hidden" name="id" value="'.$accountid.'"> 
			<table border=0 cellspacing=0 cellpadding=0 width=100% class="small" style="border-bottom:1px solid #999999;padding:5px;">
	        <tr>
	        	<td valign=bottom colspan=3><b>Reporte de factura telef&oacute;nica</b></td>
			</tr>
			</table>
			<table border=0 cellspacing=1 cellpadding=3 width=100% style="background-color:#eaeaea;" class="small">
				<tr style="height:25px" bgcolor=white>
					<td class="lvtCol">&Uacute;ltimas facturas:</td>
					<td class="lvtCol" colspan=2>Desde:</td>
					<td class="lvtCol" colspan=2>Hasta:</td>
				</tr>
				<tr>
					<td align="left">
						<input title="Mes actual" class="crmbutton small create" onclick="this.form.mode.value=\'ThisMonth\'" type="submit" name="button" value="Mes actual">
						<input title="Mes anterior" class="crmbutton small create" onclick="this.form.mode.value=\'LastMonth\'" type="submit" name="button" value="Mes anterior">
						<input title="Pen&uacute;ltimo mes" class="crmbutton small create" onclick="this.form.mode.value=\'LastMonth2\'" type="submit" name="button" value="Pen&uacute;ltimo mes">
					</td>				
			    	<td align="left">
			    		<input type="text" name="from_date" id="from_date" class="textbox" style="width:90px" value="">
			    	</td>
					<td align="left"><img border=0 src="themes/softed/images/btnL3Calendar.gif" alt="Set date.." title="Seleccionar fecha.." id="btn_from_date">
					<script type="text/javascript">
		                    Calendar.setup ({
		                    inputField : "from_date", ifFormat : "%Y-%m-%d", showsTime : false, button : "btn_from_date", singleClick : true, step : 1
		                    })
		                    </script>
					</td>
					<td align="left">
						<input type="text" name="to_date" id="to_date" class="textbox" style="width:90px" value="">
					</td>
					<td align="left">
						<img border=0 src="themes/softed/images/btnL3Calendar.gif" alt="Set date.." title="Seleccionar fecha.." id="btn_to_date">
						<script type="text/javascript">
		                    Calendar.setup ({
		                    inputField : "to_date", ifFormat : "%Y-%m-%d", showsTime : false, button : "btn_to_date", singleClick : true, step : 1
		                    })
		                </script>
						<input title="Consultar per&iacute;odo" class="crmbutton small create" onclick="this.form.mode.value=\'Time\'" type="submit" name="button" value="Consultar per&iacute;odo">
					</td>
				</tr>
			</table>
			</form>
			
			<br><br>
	';
	
	return;
}

if (isset($_REQUEST['mode'])) {
	$mode = $_REQUEST['mode'];
}
if (isset($_REQUEST['page'])) {
	$page = $_REQUEST['page'];
}
if (isset($_REQUEST['from_date'])) {
	$from_date = $_REQUEST['from_date'];
}
if (isset($_REQUEST['to_date'])) {
	$to_date = $_REQUEST['to_date'];
}

if($accountid != '')
{
	if ($mode == '') {
		$params = array('id' => "$accountid", 'block'=>"$block",'contactid'=>$customerid,'sessionid'=>"$sessionid");

// Aqui se carga el objeto client con los datos de la cuenta y se le asigna a result

		$result = $client->call('get_details', $params, $Server_Path, $Server_Path);

		// Check for Authorization
		if (count($result) == 1 && $result[0] == "#NOT AUTHORIZED#") {
			echo '<tr>
				<td colspan="6" align="center"><b>'.getTranslatedString('LBL_NOT_AUTHORISED').'</b></td>
			</tr></table></td></tr></table></td></tr></table>';
			die();
		}
		$noteinfo = $result[0][$block];

		echo '<table><tr><td><input class="crmbutton small cancel" type="button" value="'.getTranslatedString('LBL_BACK_BUTTON').'" onclick="window.history.back();"/></td></tr></table>';



//Extraemos los campos que queremos, en el array  campos_requeridos colocamos los indices a mostrar
$campos_requeridos=array();
$matriz=$noteinfo;
$nueva_matriz=array();


foreach ($matriz as $key => $value) {

  foreach ($campos_requeridos as $value2){
  	if ($key==$value2){
  		array_push($nueva_matriz,$value);
  	}
  	
  }
  
}
$noteinfo=$nueva_matriz;

//Mediante este llamado a funcion se imprimen los detalles pasandole como parametro 	$noteinfo = $result[0][$block];
              echo getblock_fieldlist($noteinfo);
	
		echo '</table></td></tr>';	
		echo '</table></td></tr></table></td></tr></table>';
		
		/* Se detecta la presencia del módulo de facturación telefónica */
		$params = array('module' => "CDR");
		$result = $client->call('get_module_enable', $params, $Server_Path, $Server_Path);
	//	var_dump($result);
		
		if (strcasecmp($result,'true')==0) {
			EscribeFormaReporteFactura($module,"1",$accountid);	
		}
		
		echo '<!-- --End--  -->';
		
		echo '</table>'."\n";
		echo '</table></td></tr>';
		echo '</table></td></tr></table></td></tr></table>';
	} else {
		$reg_pages = 20;
		$total_calls = 0;
		
		$params = array('id' => "$accountid",'module'=>"$module",'action'=>"$action", 'mode'=>"$mode",'page'=>"$page",
							'from_date'=>"$from_date",'to_date'=>"$to_date",'customerid'=>$customerid,'sessionid'=>"$sessionid");
		$result = $client->call('get_cdr_data', $params, $Server_Path, $Server_Path);
		
		$total_calls = $result[0]['call_numbers'];
		$total_pages = ceil($total_calls/$reg_pages);
		
		if ($total_calls > 0) {
			echo '<table width=100%><tr><td class="detailedViewHeader">Origen</td>'.
				 '<td class="detailedViewHeader">Destino</td>'.
				 '<td class="detailedViewHeader">Duracion(seg)</td>'.
				 '<td class="detailedViewHeader">Fecha y hora de llamada</td>'.
				 '<td class="detailedViewHeader">Costo</td></tr>';
			
			for ($i=0;$i<count($result[1]['list']);$i++) {
				echo '<tr><td>'.$result[1]['list'][$i]['src'].'</td>'.
						 '<td>'.$result[1]['list'][$i]['dst'].'</td>'.
						 '<td>'.$result[1]['list'][$i]['billsec'].'</td>'.
				 		 '<td>'.$result[1]['list'][$i]['calldate'].'</td>'.
				 		 '<td>'.number_format($result[1]['list'][$i]['cost_total'],2,',','.').'</td></tr>'."\n";
			}
			echo '</table>'."\n";
			
			echo '</table>'."\n";
			echo '</table></td></tr>';
			echo '</table></td></tr></table></td></tr></table>';
			
			echo '
				<form border="0" action="index.php" method="post" name="nav_form" id="nav_form">
				<input type="hidden" name="module" value ="'.$module.'">
				<input type="hidden" name="action" value ="index">
				<input type="hidden" name="mode" value ="'.$mode.'">
				<input type="hidden" name="id" value="'.$accountid.'">
				<input type="hidden" name="from_date" value="'.$from_date.'">
				<input type="hidden" name="to_date" value="'.$to_date.'">';
	
			//Navegación
			$disabled_start = '';
			$disabled_end = '';
			
			$enlace_form = 'this.nav_form.submit();';
			
			$enlace_start = ' onclick="this.form.page.value=\'1\';"';
			$enlace_previous = ' onclick="this.form.page.value=\''.($page-1).'\';"';
			$enlace_next = ' onclick="this.form.page.value=\''.($page+1).'\';"';
			$enlace_end = ' onclick="this.form.page.value=\''.$total_pages.'\';"';
	
			if ($page == "1") {
				$disabled_start = '_disabled';
				$enlace_start = '';
				$enlace_previous = '';
			} else if ($page == $total_pages) {
				$disabled_end = '_disabled';
				$enlace_next = '';
				$enlace_end = '';
			}
			echo '<table border=0 cellspacing=0 cellpadding=0 class="small" align="center">
					<tr><td align="right" style="padding: 5px;">
						<input title="<<" class="crmbutton small create" '.$enlace_start.' type="submit" name="button" value="<<">
						<input title="<" class="crmbutton small create" '.$enlace_previous.' type="submit" name="button" value="<">
						<input class=\'small\' name=\'page\' type=\'text\' value=\''.$page.'\' style=\'width: 3em;margin-right: 0.7em;\'>
						<span name=\'CDR_listViewCountContainerName\' class=\'small\' style=\'white-space: nowrap;\'>de '.$total_pages.'</span>
						<input title=">" class="crmbutton small create" '.$enlace_next.' type="submit" name="button" value=">">
						<input title=">>" class="crmbutton small create" '.$enlace_end.' type="submit" name="button" value=">>">
					</td></tr>
				</table>';
			
			echo '</form>'."\n";			 
			
			echo '<table align="center"><tr><td>Total llamadas</td>'.
	 			 '<td>'.number_format($result[0]['call_numbers'],0,',','.').'</td></tr>'.
	 			 '<td>Costo total de las llamadas</td>'.
	 			 '<td> &euro; '.number_format($result[0]['cost_total'],2,',','.').'</td></tr></table>';
		} else {
			echo '<table width=100%><tr><td>No existen registros de llamadas para el per&iacute;odo seleccionado...</td></tr></table>';
			echo '</table>'."\n";
			echo '</table></td></tr>';
			echo '</table></td></tr></table></td></tr></table>';
		}

		EscribeFormaReporteFactura($module,"1",$accountid);
	}
}

?>
