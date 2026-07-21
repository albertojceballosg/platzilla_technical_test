<?php

@include("../PortalConfig.php");
if(!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] == '') {
	@header("Location: $Authenticate_Path/login.php");
	exit;
}
?>

<?php $only_mine = (isset($_REQUEST['only_mine'])) ? " checked " : ""; ?>
<?php $show= ''; if($_REQUEST['showstatus'] != '') $show = $_REQUEST['showstatus']; ?>
<script language="JavaScript" src="js/general.js"></script>
<table class="dvtContentSpace" border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td align="left" >
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr><td style="padding:5px;" colspan="2">
					<table width="100%"  border="0" cellspacing="0" cellpadding="0">
					<tr><td colspan="2">&nbsp;</td></tr>
					<tr><td align="left"   width="50%">
			


<script>
function verify_data(form,fldval) {
		com = trim(form.fldval.value);
		if(com != '')  {  return true; }
					else	{ return false; }
}
function showTickets(form) {
		var showstatus = form.ticket_status_combo.value;
		var obj = form.only_mine_combo;
		var onlymine = true;
		if (obj != null) {
			var list_type = form.only_mine_combo.value;
			if (list_type == 'all') {  onlymine = false; }
		}
		window.location.href = "index.php?module=HelpDesk&action=index&showstatus="+showstatus+"&onlymine="+onlymine;
}
</script>



<?php
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

global $result;
$username = $_SESSION['customer_name'];
$customerid = $_SESSION['customer_id'];

$sessionid = $_SESSION['customer_sessionid'];

$onlymine=$_REQUEST['onlymine'];
if($onlymine == 'true') {
    $mine_selected = 'selected';
    $all_selected = '';
} else {
    $mine_selected = '';
    $all_selected = 'selected';
}

if($_REQUEST['fun'] == '' || $_REQUEST['fun'] == 'home' || $_REQUEST['fun'] == 'search')
{
	// This is an archaic parameter list
	$match_condition = (isset($_REQUEST['search_match']))?$_REQUEST['search_match']:'';
	$where = getTicketSearchQuery();
	$params = Array(Array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'user_name' => "$username", 'onlymine' => $onlymine, 'where' => "$where", 'match' => "$match_condition"));
        $result = $client->call('get_tickets_list', $params, $Server_Path, $Server_Path);

	include("TicketsList.php");
}
elseif($_REQUEST['fun'] == 'newticket') {




?>
	<span class="lvtHeaderText" >
			<input align="left" class="crmbutton small cancel" type="button" value="<?php echo getTranslatedString('LBL_BACK_BUTTON') ?>" onclick="window.history.back();"/>
	</span>
	</td><td align="right">&nbsp;</td></tr>


</table>
</td></tr>
</form>
<tr><td align="center">
	<script language="javascript" type="text/javascript">
	function MM_openBrWindow(theURL) {
		window.open(theURL,'Documentos','width=400,height=500,status=no,toolbar=no,menubar=no,location=no');
	}

	function agregarDocumentacion() {
		var iNumRows = -1;

		
		ctrlTable = document.getElementById('listaArchivos');
		if (ctrlTable) {
			if (iNumRows == -1)
				iNumRows = (ctrlTable.rows.length);
			else
				iNumRows++;
			
			var row=ctrlTable.insertRow(ctrlTable.rows.length);
			var x1=row.insertCell(0);
			var x2=row.insertCell(1);
			row.id = 'row'+iNumRows;
			x1.innerHTML='<input type="file" id="file'+iNumRows+'" name="file[]" />';
			x2.innerHTML='';
			x1.className = 'crmTableRow small lineOnTop';
			x2.className = 'crmTableRow small lineOnTop';
		}
	}
	</script>
	<form name="index" method="POST" action="index.php" enctype="multipart/form-data">
	<input type="hidden" name="module" value="HelpDesk">
	<input type="hidden" name="action" value="index">
        <input type="hidden" name="crm" value="<? echo $idcrm; ?>">
	<input type="hidden" name="fun" value="saveticket">
	<table  cellpadding="5" cellspacing="0" width="100%" border="0" align="center" class="dvtContentSpace">
	<tr><td colspan="4" class="detailedViewHeader"><b>Ingrese la Nueva Petici&oacute;n</b></td></tr>

	<tr>
		<td class="dvtCellLabel" width="20%" align="right">Asunto:</td>
              <td class="dvtCellInfo" width="80%"><input name="referencia" type ="text" size="90" maxlength="200" value='' /></td>
	</tr>
	<tr>
		<td class="dvtCellLabel" width="20%" align="right" rowspan="2">Cargar documentos</td>
		<td class="dvtCellInfo" width="80%"><input align="left" class="crmbutton small cancel" name="BTN_DOC" type="button" value=" <?php echo getTranslatedString('LBL_DOCUMENTATION_BUTTON'); ?>" onclick="agregarDocumentacion();"/></td>
	</tr>
	<tr>
		<td>
			<table id="listaArchivos">
			<tr>
				<td>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="dvtCellLabel" width="20%" align="right">Descripcion:</td>
              <td class="dvtCellInfo" width="80%"><textarea name="descripcion" wrap="hard" cols="80" rows="14" style="" wrap="OFF"  ></textarea></td>
	</tr>
	<tr><td class="dvtCellLabel" >&nbsp;</td class="dvtCellLabel" ><td align="center"><input class="crmbutton small cancel" name="SubmitIncidencia" type='submit' value="  Enviar  "  /></td></tr>
	</table>
	</form>

</td></tr>

<?php


}
elseif($_REQUEST['fun'] == 'detail')
{
	$ticketid = $_REQUEST['ticketid'];
	$block = 'HelpDesk';
	include("TicketDetail.php");
}
elseif($_REQUEST['fun'] == 'saveticket' )
{
	include("SaveTicket.php");
	/*
	if ((isset($_REQUEST['referencia'])) and ($_REQUEST['referencia']) and
		(isset($_REQUEST['descripcion'])) and ($_REQUEST['descripcion']) and
		(isset($_REQUEST['SubmitIncidencia'])) and ($_REQUEST['SubmitIncidencia'])
		) {
			$asunto = addslashes($_REQUEST['referencia']);
			$descripcion = addslashes($_REQUEST['descripcion']);
			$accountid = $_SESSION['customer_account_id'] ;
            $crm=$_REQUEST['crm'];
                       
			$pm_user 		= $_SESSION['customer_name'];
			// $pm_pass 		= $_SESSION['customer_password'];
			$pm_pass 		= 'kQ31NaY917Ja';
			$pm_lastname 	= "CustomerPortal";
			$pm_firstname 	= $_SESSION['lastname'].",  ".$_SESSION['firstname'];


			$PMsessionID = "";
			class variableStruct {
					  public $name;
					  public $value;
			}
                        ini_set("soap.wsdl_cache_enabled", "0");
                        $client = new SoapClient('http://pm.timemanagement.es/sysworkflow/en/green/services/wsdl2');
                        $pass = 'md5:' . md5($pm_pass);
                        $params = array(array('userid'=>'customportal', 'password'=>$pass));
                        $result = $client->__SoapCall('login', $params);
                       
                        if($pm_user=='lionel_tramontini@hotmail.com')
                            $pm_user='ltramontini@timemanagement.es';
if ($result->status_code == 0) {
						$PMsessionID = $result->message;
						$encontro = false;

						$params = array(array('sessionId'=>$PMsessionID));
						$result = $client->__SoapCall('userList', $params);

						$usersArray = $result->users;
						if (is_array($usersArray)) {
								 foreach ($usersArray as $user) {
                                                                     
										if ($user->name == $pm_user) {
												$encontro = true;
												break;
										}
								}
						}


						if ($encontro) {

									$pass = 'md5:' . md5($pm_pass);
									$params = array(array('userid'=>$pm_user, 'password'=>$pass));
									$result = $client->__SoapCall('login', $params);

									if ($result->status_code == 0) {

													$PMsessionID = $result->message;

													$PMprocessID = '3035415854ca30de4efd160050278797';
													$PMtasksID = '5200375464d19c86d99b887040403452';

													$vars = array(
															'userid'=>$username,
															'origen'=>'Customer Portal',
															'referencia'=>$asunto,
															'descripcion'=>$descripcion,
															'asignado'=>4722,
															'cuenta'=>$accountid,
															'preticket'=>$crm
													);
													$aVars = array();

													foreach ($vars as $key => $val)
													{
															     $obj = new variableStruct();
															     $obj->name = $key;
															     $obj->value = $val;
															     $aVars[] = $obj;
													}

													$params = array(array('sessionId'=>$PMsessionID, 'processId'=>$PMprocessID, 'taskId'=>$PMtasksID, 'variables'=>$aVars));

                                                                                                      $resultsoap = $client->__SoapCall('newCase', $params);

													if ($resultsoap->status_code == 0) {
															$PMdelIndex = '';
															$params = array(array('sessionId'=>$PMsessionID));

															$resultsoap2 = $client->__SoapCall('caseList', $params);
															$casesArray = $resultsoap2->cases;

															if (count($casesArray) == 1) {
																	if ($resultsoap->caseId == $key->guid)  {
																				$PMdelIndex = $casesArray->delIndex;
																	}
															} else {
																	foreach ($casesArray as $key) {
																			if ($resultsoap->caseId == $key->guid)  {
																						$PMdelIndex = $key->delIndex;
																			}
																	}
															}

															if  ($PMdelIndex)  {
																$params = array(array('sessionId'=>$PMsessionID,  'caseId'=>$resultsoap->caseId, 'delIndex'=>$PMdelIndex));
																$resultsoap1 = $client->__SoapCall('routeCase', $params);

															}
													}

													if (($resultsoap->status_code == 0) and ($resultsoap1->status_code == 0))

																$mensageBOTON = "ir al listado";
																$mensageOKoERROR = "<b>Hemos recibido su petici&oacute;n, en breve nos pondremos en contacto con Usted</b>";
																$pasosAregresar = "this.form.module.value='HelpDesk';this.form.action.value='index';this.form.fun.value=''";
																$tipoBOTON = 'submit';
																enviar_mail_confirmacion($_REQUEST["referencia"],$_SESSION["customer_name"]);

													} 


?>
	<span class="lvtHeaderText" >
			<input align="left" class="crmbutton small cancel" type="<?php echo $tipoBOTON?>" value="<?php echo $mensageBOTON?>" onclick="<?php echo $pasosAregresar;?>"/>
	</span>
	</td><td align="left"> </td></tr>

</table>



</td></tr>
<tr><td>
<table align="center" >
<tr><td>
<?php echo $mensageOKoERROR;?>
</td></tr>
</table>
</td></tr>

</form>

<?php
									} else {
											print "Unable to login to ProcessMaker.<br>Error Number: $result->status_code<br>Error Message: $result->message<br>";
									}
						
			} else {
						print "Unable to connect to ProcessMaker.<br>Error Number: $result->status_code<br>Error Message: $result->message<br>";
			}

	} else {
		echo "Faltan parametros para ejecutar el proceso<br>";
	}*/
}
elseif($_REQUEST['fun'] == 'saveticketDetail' )
{


	if (  	(isset($_REQUEST['ticketid'])) and ($_REQUEST['ticketid']) and
		(isset($_REQUEST['interartuaropcion'])) and ($_REQUEST['interartuaropcion']) and
		(isset($_REQUEST['descripcion'])) and ($_REQUEST['descripcion']) and
		(isset($_REQUEST['SubmitIncidenciaDetail'])) and ($_REQUEST['SubmitIncidenciaDetail'])
		) {

			$block = 'HelpDesk';
			$ticketid = $_REQUEST['ticketid'];
			$customerid = $_SESSION['customer_id'];
			$z_interartuaropcion = $_REQUEST['interartuaropcion'];
			$z_descripcion = $_REQUEST['descripcion'];
			$z_validacion = $_REQUEST['validacion'];
			$z_accountid = $_SESSION['customer_account_id'] ;



			$params = array('id' => "$ticketid", 'block'=>"$block",'contactid'=>$customerid,'sessionid'=>"$sessionid");
			$result = $client->call('get_details', $params, $Server_Path, $Server_Path);
			$ticketinfo = $result[0][$block];
			$params = Array(Array('id'=>"$customerid", 'sessionid'=>"$sessionid", 'ticketid' => "$ticketid"));
			$commentresult = $client->call('get_ticket_comments', $params, $Server_Path, $Server_Path);

			$comentarios_acumulados = '';
			for($j=0;$j<count($commentresult);$j++) {
				$tmp = str_replace("<br />"," ",$commentresult[$j]['comments']);
				$comentarios_acumulados .= "----------------($j)---------------\n";
				$comentarios_acumulados .= $commentresult[$j]['ownertype'].": ".$commentresult[$j]['owner'].' ('.$commentresult[$j]['createdtime'].")\n";
				$comentarios_acumulados .= $tmp."\n";
			}

			$descripcion_original_del_cliente = '';  $ultimo_desarrollador = '';  $casoPM = '';
			foreach($ticketinfo as $key=>$value) {
				if ($value['fieldlabel'] == utf8_encode('Descripción del Cliente')) $descripcion_original_del_cliente = $value['fieldvalue'];
				if ($value['fieldlabel'] == 'PMuserID') $ultimo_desarrollador = $value['fieldvalue'];
				if ($value['fieldlabel'] == 'Tarea PM') $casoPM = $value['fieldvalue'];
			}

			$pm_user 		= $_SESSION['customer_name'];
			// $pm_pass 		= $_SESSION['customer_password'];
			$pm_pass 		= 'kQ31NaY917Ja';
			$pm_lastname 	= "CustomerPortal";
			$pm_firstname 	= $_SESSION['lastname'].",  ".$_SESSION['firstname'];

			$PMsessionID = "";
			class variableStruct {
					  public $name;
					  public $value;
			}

			ini_set("soap.wsdl_cache_enabled", "0");
			$client = new SoapClient('http://pm.timemanagement.es/sysworkflow/en/green/services/wsdl2');
			$pass = 'md5:' . md5('kQ31NaY917Ja');
			$params = array(array('userid'=>'customportal', 'password'=>$pass));
			$result = $client->__SoapCall('login', $params);

			if ($result->status_code == 0) {

						$PMsessionID = $result->message;
						$encontro = false;

						$params = array(array('sessionId'=>$PMsessionID));
						$result = $client->__SoapCall('userList', $params);
						$usersArray = $result->users;
						if (is_array($usersArray)) {
								 foreach ($usersArray as $user) {
										if ($user->name == $pm_user) {
												$encontro = true;
												break;
										}
								}
						}

						if (!$encontro) {
									$params = array(array('sessionId'=>$PMsessionID,
															'userId' => $pm_user,
															'firstname'=> $pm_firstname,
															'lastname'=> $pm_lastname,
															'email'=> $pm_user,
															'role'=>'PROCESSMAKER_OPERATOR',
															'password'=> $pm_pass  ));
									$result = $client->__SoapCall('createUser', $params);
									if ($result->status_code == 0) {

											$userID = $result->userUID;
											$groupID = '8097543394d138d5918a7b7027841395';

											$params = array(array('sessionId'=>$PMsessionID, 'userId' => $userID, 'groupId'=>$groupID));
											$result = $client->__SoapCall('assignUserToGroup', $params);
											if ($result->status_code == 0) {
													$encontro = true;
											}
									} else {
											print "Unable to create user.<br>Error Number: $result->status_code<br>Error Message: $result->message<br>";
									}
						}

						if ($encontro) {

									$pass = 'md5:' . md5($pm_pass);
									$params = array(array('userid'=>$pm_user, 'password'=>$pass));
									$result = $client->__SoapCall('login', $params);

									if ($result->status_code == 0) {
													$PMsessionID = $result->message;
													$PMcaseID = '';
													$PMdelIndex = '';

													//   1)    PROCESO:  Insidencia Express    y  TAREA:  2. Doc generada. En espera de validación
													if ($z_interartuaropcion == 1) {
															$PMprocessID = '6536039464cb84b6fdff467027687245';
															$especificacionDesarrollo = html_entity_decode($descripcion_original_del_cliente."\n\n".$comentarios_acumulados."\n-------------RESPUESTA----------\n".$z_descripcion."\n------------------------------\n", ENT_NOQUOTES, 'UTF-8');
															$vars = array(
																	'especificacionDesarrollo'=>$especificacionDesarrollo,
																	'ultimo_desarrollador'=>$ultimo_desarrollador,
																	'customerid'=>$customerid,
																	'arearazon'=>$z_descripcion
															);
													}
													//   1)    PROCESO:  Insidencia Express    y  TAREA:  10. Dev terminado y testiado.Espera de validacion
													if ($z_interartuaropcion == 2) {
															$PMprocessID = '6536039464cb84b6fdff467027687245';
															$z_descripcion="(validacion=".$z_validacion.")\n".$z_descripcion;
															$especificacionDesarrollo = html_entity_decode($descripcion_original_del_cliente."\n\n".$comentarios_acumulados."\n-------------RESPUESTA----------\n".$z_descripcion."\n------------------------------\n", ENT_NOQUOTES, 'UTF-8');
															$vars = array(
																	'especificacionDesarrollo'=>$especificacionDesarrollo,
																	'ultimo_desarrollador'=>$ultimo_desarrollador,
																	'customerid'=>$customerid,
																	'esvalido'=>$z_validacion,
																	'causaNoValidacion'=>$z_descripcion
															);
													}

													$params = array(array('sessionId'=>$PMsessionID));
													$resultx = $client->__SoapCall('caseList', $params);
													$casesArray = $resultx->cases;
													if (count($casesArray) == 1) {
															if ($casoPM == $casesArray->name)  {
																		$PMcaseID = $casesArray->guid;
																		$PMdelIndex = $casesArray->delIndex;
															}
													} else {
															foreach ($casesArray as $key) {
																	if ($casoPM == $key->name)  {
																				$PMcaseID = $key->guid;
																				$PMdelIndex = $key->delIndex;
																	}
															}
													}
													if ( ($PMcaseID) and ($PMdelIndex) ) {
															$aVars = array();
															foreach ($vars as $key => $val)
															{
																	     $obj = new variableStruct();
																	     $obj->name = $key;
																	     $obj->value = $val;
																	     $aVars[] = $obj;
															}
															$params = array(array('sessionId'=>$PMsessionID, 'caseId'=>$PMcaseID, 'variables'=>$aVars));
															$resultsoap = $client->__SoapCall('sendVariables', $params);
															if ($resultsoap->status_code == 0) {
																		$params = array(array('sessionId'=>$PMsessionID,  'caseId'=>$PMcaseID, 'delIndex'=>$PMdelIndex));
																		$resultsoap1 = $client->__SoapCall('routeCase', $params);
															}
															if (($resultsoap->status_code == 0) and ($resultsoap1->status_code == 0)) {
																		$mensageBOTON = "ir al listado";
																		$mensageOKoERROR = "<b>Su incidencia ha sido enviada con exito!</b>";
																		$pasosAregresar = "this.form.module.value='HelpDesk';this.form.action.value='index';this.form.fun.value=''";
																		$tipoBOTON = 'submit';
															} else {
																		$mensageBOTON = getTranslatedString('LBL_BACK_BUTTON');
																		$mensageOKoERROR = "<b>Su incidencia NO se puedo cargar, haga click en volver, revice los datos y reintente!</b>";
																		$pasosAregresar = "window.history.back();";
																		$tipoBOTON = 'button';
															}

															// var_dump($params); echo "<br><br>";
															// var_dump($resultsoap); echo "<br><br>";
															// var_dump($resultsoap1); echo "<br><br>";
															// exit();
?>
	<span class="lvtHeaderText" >
			<input align="left" class="crmbutton small cancel" type="<?php echo $tipoBOTON?>" value="<?php echo $mensageBOTON?>" onclick="<?php echo $pasosAregresar;?>"/>
	</span>
	</td><td align="left"><?php echo $mensageOKoERROR;?> </td></tr>

</table>
</td></tr>
</form>
<?php
															} else {
																	print "Case Missing!!<br>";
															}

									} else {
											print "Unable to login to ProcessMaker.<br>Error Number: $result->status_code<br>Error Message: $result->message<br>";
									}
						} else {
									print "Unable to login to ProcessMaker.<br>Error Number: $result->status_code<br>Error Message: $result->message<br>";
						}
			} else {
						print "Unable to connect to ProcessMaker.<br>Error Number: $result->status_code<br>Error Message: $result->message<br>";
			}
	} else {
		echo "Faltan parametros para ejecutar el proceso<br>";
	}
}

?>

			</table>
</td></tr>
</table>


<?


function enviar_mail_confirmacion($titulo,$email)
{

	$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
			$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			$cabeceras .= 'From: gestion@timemanagement.es' . "\r\n";

	$sMessage="
	  Estimado cliente, <br><br>
 Hemos recibido su petición con título '$titulo'. En breve
 nos comunicaremos con usted para darle una resolución a la
 misma o proponerle una fecha de entrega y si es
 necesario documentar su petición con vídeo o texto.
<br><br>
 Un cordial saludo,<br>
 Equipo de Postventa<br>
 Time Management <br><br>
 Muchas Gracias.";


			mail($email,"Servicio Time Management" ,$sMessage,$cabeceras);



}












?>
