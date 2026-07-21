<?php
	require_once("../time/modules/notificaciones/language/".$default_language.".lang.php");
	include('../include/utils/interfazAuxiliar.php');
	include("../time/modules/notificaciones/notificaciones.php");

	$obj = new CNotificaciones;
	$obj->asignarDatosContacto($accountid,$customerid);
	$obj->asignarDatosTicket($ticketid);

	echo $obj->escribeSoloFormaEnviarNotificacion();
	
	function escribeFormaValidacion($ticketid) {
		$bufferSalida = '
			<div id="dlgValidacion" style="display:none; padding:10px; background-color:#FFFFFF;border:3px;border-color:yellow;width:810px; position:fixed; left:100px; top:100px; z-index:89999; -moz-border-radius: 15px;
				border-radius: 15px; -moz-box-shadow: 5px 5px 2px #888; -webkit-box-shadow: 5px 5px 2px #888; box-shadow: 5px 5px 2px #888; max-height:450px; overflow:auto;">
				<div style="float:right;cursor:pointer;"  onclick="jQuery(\'#dlgValidacion\').slideUp();">[x]</div>
				<form method="POST" action="index.php" enctype="multipart/form-data">
				<input type="hidden" name="module" value="HelpDesk">
				<input type="hidden" name="action" value="SaveValidacion">
				<input type="hidden" name="ticketid" value="'.$ticketid.'">
				<table width="100%">
					<tr>
					<td class="dvtCellLabel" width="20%" align="right">'.getTranslatedString('LBL_ES_VALIDA_LA_TAREA').'</td>
					<td class="dvtCellInfo" width="80%" align="left">
					<select id="validaTarea" name="validaTarea" onchange="onValidarTarea(this.value)">
						<option value="Si">'.getTranslatedString('LBL_YES').'</option>
						<option value="No">'.getTranslatedString('LBL_NO').'</option>
					</select>
					</td>
					</tr>
					<tr style="display:none" id="descripcionValidacion">
					<td class="dvtCellLabel" width="20%" align="left">'.getTranslatedString('LBL_DESCRIPCION_NO_VALIDACION').'</td>
					<td class="dvtCellInfo" width="80%" align="right">
					<textarea id="textoNoValidacion" name="textoNoValidacion" style="width:100%;height:100px;"></textarea>
					</td>
					</tr>
				</table>
				<input class="crmbutton small cancel" name="SubmitIncidencia" type="submit" value="  Enviar  "  />
				</form>
			</div>';
		
		return $bufferSalida;
	}
	
	echo escribeFormaValidacion($ticketid);
	
?>
	<span class="lvtHeaderText" >
			<input align="left" class="crmbutton small cancel" type="button" value="<?php echo getTranslatedString('LBL_BACK_BUTTON') ?>" onclick="window.history.back();"/>
	</span>
	
	<span class="lvtHeaderText" >
			<input align="left" class="crmbutton small cancel" type="button" value="<?php echo getTranslatedString('LBL_NOTIFICACION_BUTTON') ?>" onclick="document.getElementById('ticketid').value = '<?php echo $ticketid;?>';jQuery('#dlgNuevaNotificacion').slideDown();"/>
	</span>

	</td><td align="right"  width='50%'>&nbsp;</td></tr>
	<tr><td colspan="2"   width="100%">&nbsp;</td></tr>
</table>
</td></tr>
</form>


<tr><td valign='top' >
<table border='0' valign='top' width='100%'>		
<tr><td valign='top' >
<script language="javascript" type="text/javascript">
function MM_openBrWindow(theURL) { //v2.0
	window.open(theURL,'Documentos','width=400,height=500,status=no,toolbar=no,menubar=no,location=no');
}

function onValidarTarea(valor) {
	ctrl = document.getElementById('descripcionValidacion');
	if (valor == 'Si') {
		ctrl.style.display = 'none';
	} else {
		ctrl.style.display = 'table-row';
	}
}
</script>
<table border='0' valign='top' width='100%'>		
<?PHP
global $result;
global $client;
global $Server_Path;
$customerid = $_SESSION['customer_id'];
$sessionid = $_SESSION['customer_sessionid'];
$sessidhash = base64_encode(base64_encode($_SESSION['customer_account_id'].":$ticketid:$customerid:CP"));

if($ticketid != '')
{
	
	$params = array('id' => "$ticketid", 'block'=>"$block",'contactid'=>$customerid,'sessionid'=>"$sessionid", 'description'=>"$description");
	  
        $result = $client->call('get_details', $params, $Server_Path, $Server_Path);	
		
// Check for Authorization
	if (count($result) == 1 && $result[0] == "#NOT AUTHORIZED#") {
		echo '<tr><td colspan="6" align="center"><b>'.getTranslatedString('LBL_NOT_AUTHORISED').'</b></td></tr>';
		include("footer.html");
		die();
	}
	$ticketinfo = $result[0][$block];

	$params = Array(Array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'ticketid' => "$ticketid"));
      
	$commentresult = $client->call('get_ticket_comments', $params, $Server_Path, $Server_Path);
	$commentscount = count($commentresult);
	$params = Array(Array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'ticketid' => "$ticketid"));
	$creator = $client->call('get_ticket_creator', $params, $Server_Path, $Server_Path);

	$ticket_status = '';  $ticket_proceso = '';
        
       
	foreach($ticketinfo as $key=>$value) { 
		if ($value['fieldlabel'] == 'Status') { 	$ticket_status = $value['fieldvalue'];  }
		if ($value['fieldlabel'] == 'Proceso') { 	$ticket_proceso = $value['fieldvalue'];     }
                if ($value['fieldlabel'] == 'Description') {    $descripcion=nl2br($value['fieldvalue']);
                }
                if ($value['fieldlabel'] == 'Fecha de inicio') {    $finicio=$value['fieldvalue'];}
                if (ereg('Fecha estimada de fin',$value['fieldlabel'])=='1') {    $ffinal=$value['fieldvalue'];}
	}
        list($a,$m,$d)=explode("-",$ffinal);
                $ffinal=$d.'-'.$m.'-'.$a;
	if ($ticket_status == '14. Cerrada') $ticket_status = 'Closed';
       
	if ($ticket_status != 'Closed' && $ticket_status != '' ) {
		$campos_requeridos=array("Title","Created Time","Description"); 
	} else{
		$campos_requeridos=array("Title","Created Time","Description","Fecha de inicio","Horas Imputadas","Fecha de cierre");
	}
       
       
	$matriz=$ticketinfo;
	$nueva_matriz=array();
	$blockname_status = '';
	$value_status = '';
	foreach ($matriz as $key => $value) {
			 foreach ($campos_requeridos as $value2){
						if ($value[fieldlabel]==utf8_encode ($value2)){
							array_push($nueva_matriz,$value);
						}
			 }
			if ($value[fieldlabel] == "Fecha de inicio")  $blockname = $value['blockname'];
			if ($value[fieldlabel] == "Status") $value_status = $value;
	}
	if ($ticket_status == 'Closed') {
		$value_status['blockname'] = $blockname;
		$value_status['fieldvalue'] = getTranslatedString('STATUS_'.$value_status['fieldvalue']) ;
		array_push($nueva_matriz,$value_status);
	}
	$ticketinfo=$nueva_matriz;
	if(ereg('16. Realizar Doc', $ticket_status)==1 )
        echo '<tr><td class="dvtCellInfo" width="80%"> <span class="lvtHeaderText" ><input type="button" id="enviar" name="enviar" onClick="window.open(\'../../modules/panel_registro/agregarInfo.php?idcrm='.$ticketid.'\',\'window\',\'width=400px,height=400px,top=200,left=500\')" class="crmbutton small edit" style="Color:White;" value="Validar Petici&oacute;n"></span></td></tr>';        
    	echo '<tr><td><table width="100%"  border="0" cellspacing="0" cellpadding="0">';
	echo getblock_fieldlist($ticketinfo);
        if(ereg('18. Pendiente de Validaci', $ticket_status)== '1' || ereg('17. Dev terminado', $ticket_status)==1 ){

    echo '
        <tr><td class="dvtCellLabel" width="20%" align="right">Incidencia</td><td class="dvtCellInfo" width="80%">'.$descripcion.'</td></tr>
';   echo '
        <tr><td class="dvtCellLabel" width="20%" align="right">Fecha de Inicio</td><td class="dvtCellInfo" width="80%">'.$finicio.'</td></tr>
';   echo '
        <tr><td class="dvtCellLabel" width="20%" align="right">Fecha de finalizaci&oacute;n</td><td class="dvtCellInfo" width="80%">'.$ffinal.'</td></tr>
';
   if(ereg('18. Pendiente de Validaci', $ticket_status)== '1')
   echo '<tr><td class="dvtCellInfo" width="20%" colspan="2" align="center"> <span class="lvtHeaderText" ><input type="button" id="enviar" name="enviar" onClick="window.open(\'../../modules/panel_registro/agregarInfo2.php?idcrm='.$ticketid.'\',\'window\',\'width=400px,height=400px,top=200,left=500\')" class="crmbutton small edit" style="Color:White;" value="Validar Petici&oacute;n"></span></td></tr>';        
    	
	if(ereg('17. Dev terminado', $ticket_status)==1 || ereg('16. Realizar Doc', $ticket_status)==1 )
        echo '<tr><td class="dvtCellInfo" width="80%" colspan="2" align="center"> <span class="lvtHeaderText" ><input type="button" id="enviar" name="enviar" onClick="jQuery(\'#dlgValidacion\').slideDown();" class="crmbutton small edit" style="Color:White;" value="Validar Petici&oacute;n"></span></td></tr>';        
    	
   
}
	echo '</table></td></tr>';
 
	/*if($commentscount >= 1 && is_array($commentresult) && (ereg('18. Pendiente de Validaci', $ticket_status)!= '1') && (ereg('17. Dev terminado', $ticket_status)!=1) && (ereg('16. Realizar Doc', $ticket_status)!=1 )){
		echo 	'<tr><td  class="detailedViewHeader"><b>'.getTranslatedString('LBL_TICKET_COMMENTS').'</b></td></tr>'.
				'<tr><td  class="dvtCellInfo"><table width="100%"  border="0" cellspacing="0" cellpadding="0">';
		for($j=0;$j<$commentscount;$j++) {
			if ($commentresult[$j]['ownertype'] == 'user') { 
					$usuarioname = 'TimeManagement'; 
			} else {
					$usuarioname = $commentresult[$j]['owner']; 
			}
			echo '<tr><td  valign="top" class="dvtCellLabel"  width="20%" align="right">'.$usuarioname.'<br>'.$commentresult[$j]['createdtime'].'</td>';
			if ($commentresult[$j]['ownertype'] == 'user') { 
					echo '<td width="80%" colspan="2" class="dvtCellInfo">';
			} else {
					echo '<td width="5%"  class="dvtCellLabel">=></td><td width="75%" class="dvtCellInfo">';
			}
			echo $commentresult[$j]['comments'].'</td></tr>';
		}
		echo '</table></td></tr>';
	}
	*/
	if ($ticket_status != 'Closed' && $ticket_status != '') {
			$interartuaropcion = 0;
			$titulo = '';
			if ($ticket_proceso == 'Incidencia Express' ) {
					if ($ticket_status == '2. Doc generada. En espera de validaci&oacute;n' ) {
							$interartuaropcion = 1;
							$titulo = "Solicitar información al cliente";
					}
					if ($ticket_status == '10. Dev terminado y testiado.Espera de validacion' ) {
							$interartuaropcion = 2;
							$titulo = "Solicitar al cliente valide si lo realizado es correcto";
					}
			}
			if ($ticket_proceso == 'Incidencia con test incluido' ) {
					if ($ticket_status == '2. Doc generada. En espera de validacion' ) {
							$interartuaropcion = 3;
							$titulo = "Si o No";
					}
					if ($ticket_status == '10. Dev terminado y testiado.Espera de validacion' ) {
							$interartuaropcion = 4;
							$titulo = "Si o No";
					}
			}
			if ($ticket_proceso == 'Desarrollo funcional' ) {
					if ($ticket_status == '2. Doc generada. En espera de validacion' ) {
							$interartuaropcion = 5;
							$titulo = "Si o No";
					}
					if ($ticket_status == '10. Dev terminado y testiado.Espera de validacion' ) {
							$interartuaropcion = 6;
							$titulo = "Si o No";
					}
			}
			if ($ticket_proceso == 'Sistemas' ) {
					if ($ticket_status == '10. Dev terminado y testiado.Espera de validacion' ) {
							$interartuaropcion = 7;
							$titulo = "Si o No";
					}
			}
			if ($ticket_proceso == 'Sistemas internos' ) {
					if ($ticket_status == '2. Doc generada. En espera de validacion' ) {
							$interartuaropcion = 8;
							$titulo = "consultar informacion";
					}
			}

			if ($interartuaropcion) {
				echo '<tr><td><br><table width="100%"  border="0" cellspacing="0" cellpadding="0">';
				echo '<form name="index" method="POST" action="index.php">';
				echo '<input type="hidden" name="module" value="HelpDesk">';
				echo '<input type="hidden" name="action" value="index">';
				echo '<input type="hidden" name="fun" value="saveticketDetail">';
				echo '<input type="hidden" name="validacion" value="">';
				echo '<input type="hidden" name="ticketid" value="'.$ticketid.'">';
				echo '<input type="hidden" name="interartuaropcion" value="'.$interartuaropcion.'">';
				echo '<table  cellpadding="0" cellspacing="0" width="100%" border="0" align="center" class="dvtContentSpace">';
				echo '<tr><td colspan="4" class="detailedViewHeader"><b>Proceso: '.utf8_encode($titulo).'</b></td></tr>';
				echo '<tr>';
				echo '	<td class="dvtCellLabel" width="20%" align="right">Cargar documentos</td>';
				echo '	<td class="dvtCellInfo" width="80%"><input align="left" class="crmbutton small cancel" type="button" value="'.getTranslatedString('LBL_DOCUMENTATION_BUTTON').'" onclick="MM_openBrWindow(\'http://documentos.timemanagement.es/index.php?sessid='.$sessidhash.'\');"/></td>';
				echo '</tr>';
				echo '<tr>';
				echo '	<td class="dvtCellLabel" width="20%" align="right">Descripci&oacute;n:</td>';
				echo '	<td class="dvtCellInfo" width="80%"><textarea name="descripcion" wrap="hard" cols="60" rows="8" style="" wrap="off"  ></textarea></td>';
				echo '</tr>';
				if (in_array($interartuaropcion,array(2,3,4,5,6,7))) {
						echo '<tr>';
						echo '	<td class="dvtCellLabel" width="20%" align="right">Validaci&oacute;n:</td>';
						echo '	<td class="dvtCellInfo" width="80%"><input type="radio" name="validacion" value="si"> SI  <input type="radio" name="validacion" value="no">  NO</td>';
						echo '</tr>';
				}
				echo '<tr><td class="dvtCellLabel" >&nbsp;</td class="dvtCellLabel" ><td align="center"><input class="crmbutton small cancel" name="SubmitIncidenciaDetail" type="submit" value="  Enviar  "  /></td></tr>';
				echo '</table>';
				echo '</form>';
				echo '</table></td></tr>';
			} 
	}
	

}

echo "</table>".$obj->escribirNotificacionesAsociadasRegistro()."</td><td width='420' height='320' valign='top'>";

if($ticketid != '') {
	echo '<iframe id="iframeVideo" src="http://video.timemanagement.es/video.php?id='.$ticketid.'" width="420" height="320" frameborder="0" scrolling="no"></iframe>';
}


?>

</td></tr>
</table>
</td></tr>
