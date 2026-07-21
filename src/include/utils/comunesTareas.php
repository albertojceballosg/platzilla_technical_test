<?php
	//Definiciones con literales

	//Status de tareas
	define ('TICKET_OPEN', 'TICKET_OPEN');
	define ('TICKET_TO_VALIDATE_COORDINATOR', 'TICKET_TO_VALIDATE_COORDINATOR');
	define ('TICKET_TO_VALIDATE_CUSTOMER', 'TICKET_TO_VALIDATE_CUSTOMER');
	define ('TICKET_VALIDATED_CUSTOMER', 'TICKET_VALIDATED_CUSTOMER');
	define ('TICKET_VALIDATE_COORDINATOR', 'TICKET_VALIDATE_COORDINATOR');
	define ('TICKET_ASSIGNED', 'TICKET_ASSIGNED');
	define ('TICKET_TO_VALIDATE', 'TICKET_TO_VALIDATE');
	define ('TICKET_VERIFIED', 'TICKET_VERIFIED');
	define ('TICKET_ON_PRODUCTION', 'TICKET_ON_PRODUCTION');
	define ('TICKET_PENDING_CONFIRMATION_OF_CUSTOMER', 'TICKET_PENDING_CONFIRMATION_OF_CUSTOMER');
	define ('TICKET_ACCEPTED', 'TICKET_ACCEPTED');
	define ('TICKET_DERIVED', 'TICKET_DERIVED');
	define ('ACCOUNT_ID_TIME', 125);
	define ('MAX_DIAS_ESPERA_NOTIFICACION', 5);
	define ('MAX_DIAS_ESPERA_CIERRE_PEDIDOS', 10);
	define ('TEXTO_PENDIENTE', 'Pendiente');
	define ('PEDIDO_INCOMPLETO', 'Pedido incompleto');

	//Status de Hitos
	define ('HITO_FINALIZADO', 'Finalizado');

	/* Comunes TimeManagement: Archivo que contiene funciones comunes a la plataforma Time pero que son generales a cualquier plataforma */

	function mostrarDocumentacion ($id, $enlace = '', $dirPadre = '../../../', $return_action = '') {
		global $adb;
		//global $conex;

		$sql = "SELECT D.* FROM vtiger_attachments D INNER JOIN vtiger_seattachmentsrel C
					ON (D.attachmentsid = C.attachmentsid)
					INNER JOIN vtiger_notes A
					ON (C.crmid = A.notesid)
					INNER JOIN vtiger_senotesrel B
					ON (A.notesid = B.notesid)
					WHERE B.crmid = " . $id;

		//$result = mysql_query($sql,$conex);
		$result = $adb->query ($sql);

		$bufferSalida = agregarDocumentacion ();

		if (!empty($enlace)) {
			$bufferSalida .= $enlace;
		} else {
			$bufferSalida .= '<input  type="button" name="documentos" id="documentos" class="crmbutton small create"  value="Documentaci&oacute;n" onclick="jQuery(\'#dlgDetalle' . $id . '\').slideDown();">';
		}

		$bufferSalida .= '
		<div id="dlgDetalle' . $id . '" style="display:none; padding:10px; background-color:#FFFFFF;border:1px solid;border-color:blue;position:absolute; top: 50%;left: 50%;width:400px;height: 200px;margin-top: -100px;margin-left: -200px;z-index:89990; -moz-border-radius: 15px;
			border-radius: 15px; -moz-box-shadow: 5px 5px 2px #888; -webkit-box-shadow: 5px 5px 2px #888; box-shadow: 5px 5px 2px #888; max-height:450px; overflow:auto;">
			<div style="float:left;cursor:pointer;">Documentaci&oacute;n Adjunta</div>
			<div style="float:right;cursor:pointer;"  onclick="jQuery(\'#dlgDetalle' . $id . '\').slideUp();">[x]</div>
			<table class="small" width="100%">';
		if ($adb->num_rows ($result) == 0) {
			$bufferSalida .= '<tr><td>El registro no posee documentaci&oacute;n asociada
								</td></tr>';
		}
		//while($row = mysql_fetch_assoc($result)) {
		while ($row = $adb->fetch_array ($result)) {
			$bufferSalida .= '<tr><td>
									<a href="' . $dirPadre . 'index.php?module=uploads&action=downloadfile&entityid=' . $id . '&fileid=' . $row['attachmentsid'] . '" title="Descargar fichero">' . $row['name'] . '</a>
								</td></tr>';
		}

		if ($return_action != '') {
			//$return_action = $_REQUEST['action'];

			$bufferSalida .= '
					<tr>
						<td width="320">
							<input type="button" name="Agregar Documentacion" value="Agregar Documentacion" class="crmbutton small create" onclick="agregarDocumentacion();jQuery(\'#btnEnviar\').show();">
						</td>
					</tr>
					<tr>
					<td>
						<form onsubmit="return validarForm(this);" style="margin:0px;" enctype="multipart/form-data" method="post" class="formDefault" action="index.php" id="crearRegistro">
						<input type="hidden" name="action" value="asociaDocumentos" />
						<input type="hidden" name="module" value="HelpDesk" />
						<input type="hidden" name="Ajax" value="true" />
						<input type="hidden" name="Popup" value="' . $_REQUEST['Popup'] . '" />
						<input type="hidden" name="desarrollador" value="' . $_REQUEST['desarrollador'] . '" />
						<input type="hidden" name="return_action" value="' . $return_action . '" />
						<input type="hidden" name="record" id="record" value="' . $_REQUEST['record'] . '" />
						<input type="hidden" name="id" id="id" value="' . $_REQUEST['id'] . '" />
						<table id="listaArchivos">
						<tr>
							<td>
							</td>
						</tr>
						</table>
						<div id="btnEnviar" style="display:none">
						<input type="submit" name="savedoc" value="' . getTranslatedString ('Enviar Documentacion') . '" class="crmbutton small edit">
						</div>
						</form>
					</td>
					</tr>';
		}
		$bufferSalida .= '</table>
		</div>';

		return $bufferSalida;
	}

	function obtenerCasoTestingTarea ($id) {
		//global $conex;
		global $adb;
		$sql = 'SELECT * FROM vtiger_casosdetesting WHERE ticketid = ' . $id;

		//$result = mysql_query($sql,$conex);
		$result = $adb->query ($sql);

		if ($result) {
			//$row = mysql_fetch_array($result);
			$row = $adb->fetch_array ($result);
			if ($row) {
				return array ($row['casosdetestingid'], $row['titulo']);
			}
		}
		return array ('', '');
	}

	/**
	 *
	 * Retorna el Id del Modulo asociado a la tarea. Modulo asociado es un registro del modulo "ListadoModulos"
	 *
	 **/
	function obtenerModuloTarea ($id) {
		//global $conex;
		global $adb;
		$sql = 'SELECT moduloid FROM vtiger_troubletickets WHERE ticketid = ' . $id;

		//$result = mysql_query($sql,$conex);
		$result = $adb->query ($sql, $conex);

		if ($result) {
			//$row = mysql_fetch_array($result);
			$row = $adb->fetch_array ($result);
			if ($row) {
				return $row['moduloid'];
			}
		}
		return;
	}

	function enviarNotificacionEjecutivoCuenta ($id, $toName, $toMail, $eventcode, $arrayVars) {
		return;
	}

	function enviaNotificacionCambioEstado ($ticketid) {
		global $adb;

		$pedido     = CRMEntity::getInstance ('HelpDesk');
		$pedido->id = $ticketid;
		$pedido->retrieve_entity_info ($ticketid, 'HelpDesk');

		if (existeTabla ('vtiger_ticketdates')) {
			$sql = "REPLACE INTO vtiger_ticketdates VALUES(?,?,?)";

			$adb->pquery ($sql, array ($ticketid, date ('Y-m-d H:i:s'), $pedido->column_fields['ticketstatus']));
		}
	}

	function enviaNotificacionCooordinador ($ticketid) {
		return;
	}

	function enviaNotificacionIntegrador ($ticketid) {
		return;
	}

	function enviaNotificacionInfraestructura ($ticketid) {
		return;
	}

	function enviaNotificacionCierrePorFaltaRespuesta ($ticketid, $eventid = 'NOTIFICACION_CIERRE_POR_FALTA_INFORMACION') {
		return;
	}

	function crearRegistroTesting ($userName, $test_id, $titulot, $parent_id, $cometariod, $casostestingid) {
		//global $conex;
		global $adb;

		$queryUser  = "SELECT id FROM vtiger_users WHERE user_name = '" . $userName . "'";
		$resultUser = $adb->query ($queryUser);

		if ($resultUser) {
			//$rowUser = mysql_fetch_array($resultUser);
			$rowUser = $adb->fetch_array ($resultUser);
			$userid  = $rowUser['id'];
		} else {
			$userid = 1;
		}

		$queryDesarrollador = "SELECT vendorname, vendorid FROM vtiger_vendor WHERE user_id = " . $userid;
		//$resultDesarrollador = mysql_query($queryDesarrollador,$conex);
		$resultDesarrollador = $adb->query ($queryDesarrollador);

		if ($resultDesarrollador) {
			//$rowDesarrollador = mysql_fetch_array($resultDesarrollador);
			$rowDesarrollador = $adb->fetch_array ($resultDesarrollador);
			$userName         = $rowDesarrollador['vendorname'];
			$vendorid         = $rowDesarrollador['vendorid'];
		}

		if ($vendorid == '') {
			$vendorid = -1;
		}

		if ($casostestingid == '') {
			$casostestingid = 0;
		}
		//Se obtiene el crmid del registro.
		$queryId = "SELECT id FROM vtiger_crmentity_seq";

		//$resultId = mysql_query($queryId,$conex);
		$resultId = $adb->query ($queryId);

		if ($resultId) {
			if ($test_id == '1') {
				$test_id = 'Testing';
			} elseif ($test_id == '2') {
				$test_id = 'Video';
			} elseif ($test_id == '3') {
				$test_id = 'Testing y Video';
			}

			//$rowId = mysql_fetch_array($resultId);
			$rowId = $adb->fetch_array ($resultId);

			$id = $rowId['id'];
			$id++;
			$sqlInsert = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,modifiedby,setype,description,createdtime,modifiedtime,viewedtime,status,version,presence,deleted)
							VALUES ($id,$userid,$userid,0,'solicitudTesting',NULL,'" . date ("Y-m-d") . "','" . date ("Y-m-d") . "','" . date ("Y-m-d") . "',NULL,0,1,0)";

			//mysql_query($sqlInsert,$conex);
			$adb->query ($sqlInsert);

			$sqlInsert2 = "INSERT INTO vtiger_solicitudtesting (solicitudTestingid,titulo,ticketid,guid,accountid,casostestingid,vendorid) VALUES
							($id,'" . $titulot . "',0,0," . $parent_id . "," . $casostestingid . "," . $vendorid . ")";
			//mysql_query($sqlInsert2,$conex);
			$adb->query ($sqlInsert2);

			$sqlInsert2 = "INSERT INTO vtiger_solicitudtestingcf (solicitudTestingid,cf_744,cf_745,cf_746,cf_748,cf_749,cf_750,cf_755,cf_769,cf_775)
			VALUES ($id,NULL,NULL,NULL,NULL,NULL,NULL,'" . $userName . "','" . $test_id . "',NULL)";
			//mysql_query($sqlInsert2,$conex);
			$adb->query ($sqlInsert2);

			$sqlUpdate2 = "UPDATE vtiger_crmentity_seq SET id = $id";
			//mysql_query($sqlUpdate2,$conex);
			$adb->query ($sqlUpdate2);

			//Se cargan los puntos del testings
			if (isset($casostestingid) && $casostestingid != '') {
				$sqlInsertPuntos = "SELECT max(pointid) AS maxPointid FROM vtiger_puntosTesting";
				//$resultMaxPuntos = mysql_query($sqlInsertPuntos,$conex);
				$resultMaxPuntos = $adb->query ($sqlInsertPuntos, $conex);
				//$rowMaxPuntos = mysql_fetch_array($resultMaxPuntos);
				$rowMaxPuntos = $adb->fetch_array ($resultMaxPuntos);
				$pointId      = $rowMaxPuntos['maxPointid'];

				//Obtengo los puntos
				$sqlPuntos = "SELECT descripcion FROM  `vtiger_pasosxcasosTesting` WHERE casoTestingid = " . $casostestingid;
				//$resultPuntos = mysql_query($sqlPuntos,$conex);
				$resultPuntos = $adb->query ($sqlPuntos);

				if ($resultPuntos) {
					//while($rowPuntos = mysql_fetch_array($resultPuntos)) {
					while ($rowPuntos = $adb->fetch_array ($resultPuntos)) {
						$pointId++;
						$sqlInsertPuntos = "INSERT INTO vtiger_puntosTesting (testingid,pointid,descripcion,estado,comentario,horae)
												VALUES ($id,$pointId,'" . $rowPuntos['descripcion'] . "','','',0)";
						//mysql_query($sqlInsertPuntos,$conex);
						$adb->query ($sqlInsertPuntos);
					}
				}
			}

			//Asocio la documentaci�n
			for ($i = 0; $i < count ($_FILES['file']['name']); $i++) {
				$_FILES['file']['name'][ $i ] = from_html (preg_replace ('/\s+/', '_', $_FILES['file']['name'][ $i ]));
				$sql3                         = "SELECT id FROM vtiger_crmentity_seq";
				//$idDoc=mysql_fetch_array(mysql_query($sql3,$conex));
				$idDoc = $adb->fetch_array ($adb->query ($sql3));
				$idDoc = $idDoc['id'];
				$idDoc++;

				$sql2 = "UPDATE vtiger_crmentity_seq SET id = " . $idDoc;
				//$re2=mysql_query($sql2,$conex);
				$re2 = $adb->query ($sql2);

				$sql4 = "SELECT prefix,cur_id FROM vtiger_modentity_num WHERE semodule='Documents'";
				//$result = mysql_query($sql4,$conex);
				$result = $adb->query ($sql4);
				//$fila = mysql_fetch_assoc($result);
				$fila = $adb->fetch_array ($result);

				$sql5 = "UPDATE vtiger_modentity_num SET cur_id=cur_id+1 WHERE semodule='Documents'";
				//$result = mysql_query($sql5,$conex);
				$result  = $adb->query ($sql5);
				$note_no = $fila['prefix'] . ($fila['cur_id'] + 1);

				$sql = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
								VALUES(" . $idDoc . "," . $user . "," . $user . ",'Documents','','" . $modifiedtime . "','" . $modifiedtime . "')";

				//$re2=mysql_query($sql,$conex);
				$re2 = $adb->query ($sql);

				$sql = "INSERT INTO vtiger_notes (notesid,note_no,title,filename,notecontent,folderid,filetype,filelocationtype,filedownloadcount,filestatus,filesize,fileversion)
								VALUES(" . $idDoc . ",'" . $note_no . "','" . $_FILES['file']['name'][ $i ] . "','" . $_FILES['file']['name'][ $i ] . "','',17,'" . $_FILES['file']['type'][ $i ] . "','I',NULL,1," . $_FILES['file']['size'][ $i ] . ",'')";
				//$re2=mysql_query($sql,$conex);
				$re2 = $adb->query ($sql);

				$sql_cf = "INSERT INTO vtiger_notescf (notesid) VALUES(" . $idDoc . ")";
				//$re=mysql_query($sql_cf,$conex);
				$re = $adb->query ($sql_cf);

				$dbQuery = "INSERT INTO vtiger_senotesrel VALUES ( " . $id . "," . $idDoc . ")";
				//$re=mysql_query($dbQuery,$conex);
				$re = $adb->query ($dbQuery);

				//$focus = new HelpDesk();
				$current_user->id = $userid;
				$file             = array (
					'name'     => $_FILES['file']['name'][ $i ],
					'type'     => $_FILES['file']['type'][ $i ],
					'tmp_name' => $_FILES['file']['tmp_name'][ $i ],
					'error'    => $_FILES['file']['error'][ $i ],
					'size'     => $_FILES['file']['size'][ $i ],
				);
				//$focus->uploadAndSaveFile($idDoc,'Documents',$file);
				uploadAndSaveFile ($idDoc, 'Documents', $file);
			}
		}

		return $id;
	}

	function uploadAndSaveFile ($id, $module, $file_details) {

		global $adb, $current_user;
		global $upload_badext;

		$date_var = date ('Y-m-d H:i:s');

		$ownerid = $current_user->id;

		if (isset($file_details['original_name']) && $file_details['original_name'] != null) {
			$file_name = $file_details['original_name'];
		} else {
			$file_name = $file_details['name'];
		}

		// Arbitrary File Upload Vulnerability fix - Philip
		$binFile = preg_replace ('/\s+/', '_', $file_name);//replace space with _ in filename
		$ext_pos = strrpos ($binFile, ".");

		$ext = substr ($binFile, $ext_pos + 1);

		if (in_array (strtolower ($ext), $upload_badext)) {
			$binFile .= ".txt";
		}
		// Vulnerability fix ends

		$sql3       = "SELECT id FROM vtiger_crmentity_seq";
		$current_id = $adb->fetch_array ($adb->query ($sql3));
		$current_id = $current_id['id'];
		$current_id++;

		$sql2 = "UPDATE vtiger_crmentity_seq SET id = " . $current_id;
		$re2  = $adb->query ($sql2);

		$filename     = ltrim (basename (" " . $binFile)); //allowed filename like UTF-8 characters
		$filetype     = $file_details['type'];
		$filesize     = $file_details['size'];
		$filetmp_name = $file_details['tmp_name'];

		//get the file path inwhich folder we want to upload the file
		$upload_file_path = decideFilePath ();

		//upload the file in server
		$upload_status = move_uploaded_file ($filetmp_name, $upload_file_path . $current_id . "_" . $binFile);

		$save_file = 'true';

		if ($save_file == 'true' && $upload_status == 'true') {

			$sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
						values($current_id, $current_user->id, $ownerid, '$module', '', '" . date ('Y-m-d H:i:s') . "', '" . date ('Y-m-d H:i:s') . "')";

			$adb->query ($sql1);

			$sql2 = "insert into vtiger_attachments(attachmentsid, name, description, type, path) values($current_id, '$filename', '', '$filetype', '$upload_file_path')";
			$adb->query ($sql2);

			$sql3 = "insert into vtiger_seattachmentsrel values($id,$current_id)";
			$adb->query ($sql3);

			return $current_id;
		} else {
			return false;
		}
	}

	function obtenerSelectCampo ($fieldname, $oldvalue = '', $id = -1, $htmlParams = '') {
		global $adb;
		$sql         = "SELECT " . $fieldname . " FROM vtiger_" . $fieldname;
		$result      = $adb->query ($sql);
		$numrows     = $adb->num_rows ($result);
		$idfieldname = "$fieldname";
		if ($id > 0) {
			$idfieldname = "$fieldname_$id";
			$fsJS        = 'onchange="if (window.actualizarPrioridad) actualizarPrioridad(\'' . $id . '\',this.value);"';
		}
		$filtro = "<select name=\"$fieldname\" id=\"$idfieldname\" class=\"form-control\" $fsJS $htmlParams>" .
				  "<option value=\"\">--Seleccione--</option>";
		for ($i = 0; $i < $numrows; $i++) {
			$selected = "";
			$temp_val = decode_html ($adb->query_result ($result, $i, $fieldname));
			if ($temp_val == $oldvalue) {
				$selected = "selected";
			}
			$value = ($current_module_strings[ $temp_val ] != '') ? $current_module_strings[ $temp_val ] : (($app_strings[ $temp_val ] != '') ? ($app_strings[ $temp_val ]) : $temp_val);
			$filtro .= "<option value=\"$value\" $selected>" . getTranslatedString ($value) . "</option>";
		}
		$filtro .= "</select>";
		return $filtro;
	}

	function mostrarListaModulos ($moduloid) {
		global $adb;

		$query   = "SELECT listadomodulosid, titulo FROM vtiger_listadomodulos INNER JOIN vtiger_crmentity
					ON (vtiger_listadomodulos.listadomodulosid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0) ORDER BY titulo";
		$result  = $adb->query ($query);
		$numrows = $adb->num_rows ($result);

		$filtro = "<select name=\"moduloid\" id=\"moduloid\" class=\"form-control\" >" .
				  "<option value=\"\">--Seleccione--</option>";
		for ($i = 0; $i < $numrows; $i++) {
			$selected = "";
			$id_val   = $adb->query_result ($result, $i, 'listadomodulosid');
			$temp_val = decode_html ($adb->query_result ($result, $i, 'titulo'));
			$filtro .= "<option value=\"$id_val\">$temp_val</option>";

			if ($moduloid == $id_val) {
				$nombreModulo = '<a href="index.php?module=listadomodulos&action=DetailView&record=' . $id_val . '" target="_listadomodulos">' . $temp_val . '</a>';
			}
		}
		$filtro .= "</select>";

		if ($nombreModulo) {
			return $nombreModulo;
		}
		return $filtro;
	}

	/************
	 * Funciones relacionadas a la presentaci�n de los comentarios del d�a anterior
	 *******************/

	function obtenerDatosTicket ($ticketId) {
		return array ();
	}

	function ayer () {
		$hoy = time ();
		$dia = date ("w", $hoy);
		switch ($dia) {
			case 7:
				$resta = (24 * 60 * 60) * 2 + (7 * 60 * 60);   // 24hs * 2dias + 7hs  (2 dias + correccion hora espa�ola y colombiana)
				break;
			case 1:
				$resta = (24 * 60 * 60) * 3 + (7 * 60 * 60);   // 24hs * 3dias + 7hs  (3 dias + correccion hora espa�ola y colombiana)
				break;
			default:
				$resta = (24 * 60 * 60) + (7 * 60 * 60);   // 24hs + 7hs  (1 dia + correccion hora espa�ola y colombiana)
				break;
		}
		return date ("Y-m-d", $hoy - $resta);
	}

	function obtenerOTsAyer ($vendorId) {
		global $adb;
		$ayer = ayer ();
		$sql  = "SELECT DISTINCT ticketid
				FROM  vtiger_ordentrabajo INNER JOIN vtiger_ordentrabajo_informes
				ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_ordentrabajo_informes.ordentrabajoid)
				WHERE vtiger_ordentrabajo_informes.fecha LIKE '$ayer%'
				and vtiger_ordentrabajo_informes.vendorid = $vendorId
			 ";
		//$result = mysql_query($sql);
		$result   = $adb->query ($sql);
		$i        = 0;
		$retornar = array ();
		//while ($reg = mysql_fetch_array($result)){
		while ($reg = $adb->fetch_array ($result)) {
			$retornar[ $i ] = $reg['ticketid'];
			$i++;
		}
		return $retornar;
	}

	function getVendorId ($userId) {
		global $adb;

		if (empty($userId)) {
			return;
		}

		$data   = 0;
		$sql    = "SELECT  v.vendorid
				FROM vtiger_vendor v
				left join vtiger_crmentity crm on v.vendorid=crm.crmid
				where crm.deleted=0
				and v.user_id = $userId

			 ";
		$result = $adb->query ($sql);
		while ($reg = $adb->fetchByAssoc ($result)) {
			$data = $reg['vendorid'];
		}
		return $data;
	}

	function getUserIdByVendorId ($vendorId) {
		global $adb;

		$data   = 0;
		$sql    = "SELECT  v.user_id
				FROM vtiger_proveedor v
				left join vtiger_crmentity crm on v.proveedorid=crm.crmid
				where crm.deleted=0
				and v.proveedorid = $vendorId
			 ";
		$result = $adb->query ($sql);
		if ($result && $adb->num_rows ($result)) {
			while ($reg = $adb->fetchByAssoc ($result)) {
				$data = $reg['user_id'];
			}
			return $data;
		}
	}

	function getUsers2Vendors () {
		global $adb;

		$data   = 0;
		$sql    = "SELECT vtiger_users.*
				FROM vtiger_vendor v
				INNER JOIN vtiger_crmentity crm ON v.vendorid=crm.crmid
				INNER JOIN vtiger_users ON (v.user_id = vtiger_users.id)
				WHERE crm.deleted=0 ORDER BY user_name ASC
			 ";
		$result = $adb->query ($sql);
		while ($row = $adb->fetchByAssoc ($result)) {
			$temp_result[ $row['id'] ] = getFullNameFromArray ('Users', $row);
		}
		return $temp_result;
	}

	function getUserByRol ($roleid) {
		global $adb;

		$data   = array ();
		$sql    = "SELECT vtiger_users.* FROM vtiger_users INNER JOIN vtiger_user2role ON (userid=id) WHERE roleid = ?";
		$result = $adb->pquery ($sql, array ($roleid));
		while ($reg = $adb->fetchByAssoc ($result)) {
			$data[] = $reg;
		}
		return $data;
	}

	function tipoUsuario ($userId) {
		global $adb;
		$data = 'XX';
		if (empty($userId)) {
			$userId = $userId = $_SESSION["authenticated_user_id"];
		}
		$sql    = "SELECT  roleid
				FROM vtiger_user2role r
				left join vtiger_users u on r.userid=u.id
				where u.id = $userId
			 ";
		$result = $adb->query ($sql);
		while ($reg = $adb->fetchByAssoc ($result)) {
			$data = $reg['roleid'];
		}
		return $data;
	}

	function updateDatesHito ($hitoid) {
		global $adb;

		//Se actualiza la fecha de inicio a la fecha mas antigua de sus tareas
		$query = "UPDATE vtiger_hito SET inidate = (SELECT min(start_date)
													FROM vtiger_troubletickets
													INNER JOIN vtiger_crmentity ON (ticketid = crmid AND deleted = 0)
													WHERE vtiger_troubletickets.hitoid = ?)
					WHERE vtiger_hito.hitoid = ? AND inidate > (SELECT min(start_date) FROM vtiger_troubletickets WHERE vtiger_troubletickets.hitoid = ?)";

		$adb->pquery ($query, array ($hitoid, $hitoid, $hitoid));

		//Se actualiza la fecha de fin a la ultima fecha de sus tareas
		$query = "UPDATE vtiger_hito SET enddate = (SELECT max(end_estimated_date)
													FROM vtiger_troubletickets
													INNER JOIN vtiger_crmentity ON (ticketid = crmid AND deleted = 0)
													WHERE vtiger_troubletickets.hitoid = ?)
					WHERE vtiger_hito.hitoid = ? AND enddate < (SELECT max(end_estimated_date) FROM vtiger_troubletickets WHERE vtiger_troubletickets.hitoid = ?)";

		$adb->pquery ($query, array ($hitoid, $hitoid, $hitoid));
	}

	function updateDatesProjects ($projectid) {
		global $adb;

		//Se actualiza la fecha de inicio a la fecha mas antigua de sus tareas
		$query = "UPDATE vtiger_proyectos SET fechainicial = (SELECT min(start_date)
													FROM vtiger_troubletickets
													WHERE vtiger_troubletickets.proyectoid = ?)
					WHERE vtiger_proyectos.proyectosid = ? AND fechainicial > (SELECT min(start_date) FROM vtiger_troubletickets WHERE vtiger_troubletickets.proyectoid = ?)";

		$adb->pquery ($query, array ($projectid, $projectid, $projectid));

		//Se actualiza la fecha de fin a la ultima fecha de sus tareas
		$query = "UPDATE vtiger_proyectos SET fechafinal = (SELECT max(end_estimated_date)
													FROM vtiger_troubletickets
													WHERE vtiger_troubletickets.proyectoid = ?)
					WHERE vtiger_proyectos.proyectosid = ? AND fechafinal < (SELECT max(end_estimated_date) FROM vtiger_troubletickets WHERE vtiger_troubletickets.proyectoid = ?)";

		$adb->pquery ($query, array ($projectid, $projectid, $projectid));
	}

	function updateStatusHito ($hitoid, $status) {
		global $adb;

		if ($status == HITO_FINALIZADO) {
			//Se valida que no tenga ninguna tarea abierta el hito. Sino no puede estar finalizado.
			$query = "SELECT vtiger_troubletickets.ticketid FROM vtiger_troubletickets INNER JOIN vtiger_crmentity
						ON (vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
						WHERE hitoid = ? AND vtiger_troubletickets.status != ?";

			$result = $adb->pquery ($query, array ($hitoid, TICKET_ACCEPTED));

			if ($adb->num_rows ($result) != 0) //Si no quedan tareas pendientes, se puede actualizar el status del hito
			{
				$status = '';
			}
		}

		if (!empty($status)) {
			$query  = "UPDATE  vtiger_hito SET hitostate = ? WHERE hitoid = ?";
			$result = $adb->pquery ($query, array ($status, $hitoid));
		}
	}

	function updateDatesPedido ($ticketid) {
		global $adb;

		//Se actualiza la fecha de inicio a la fecha mas antigua de sus tareas
		$query = "UPDATE vtiger_troubletickets SET end_estimated_date = (SELECT max(enddate)
													FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity
													ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
													WHERE vtiger_ordentrabajo.ticketid = ?)
					WHERE vtiger_troubletickets.ticketid = ? AND end_estimated_date < (SELECT max(enddate)
													FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity
													ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
													WHERE vtiger_ordentrabajo.ticketid = ?)";

		$adb->pquery ($query, array ($ticketid, $ticketid, $ticketid));

		$query = "UPDATE vtiger_troubletickets SET start_date = (SELECT min(date)
													FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity
													ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
													WHERE vtiger_ordentrabajo.ticketid = ? AND date <> '0000-00-00')
					WHERE vtiger_troubletickets.ticketid = ? AND (start_date = '0000-00-00' OR start_date > (SELECT min(date)
													FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity
													ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
													WHERE vtiger_ordentrabajo.ticketid = ? AND date <> '0000-00-00'))";

		$adb->pquery ($query, array ($ticketid, $ticketid, $ticketid));
	}

	function agregarTarea ($post) {
		global $adb;

		$vendorid    = $post['postVendorid'];
		$ticketid    = $post['postTicket'];
		$descripcion = $post['descripcion'];
		$date        = desFormatearFecha ($post['date']);
		$enddate     = desFormatearFecha ($post['enddate']);

		if (!empty($vendorid) and !empty($ticketid) and !empty($descripcion) and !empty($date)) {
			/*
			$sql ="INSERT INTO vtiger_ticketpuntos ( ticketid , description , date , porcentaje , state , desarrollador_id)
					VALUES ( $ticketid,'$descripcion', '$date' , 0 , 'Pendiente', $vendorid ) ";
			$query = $adb->query($sql);
			$ticketpuntoid=$adb->getLastInsertID();

			registrarRePlanificacion($ticketid,$post["postVendorid"],$date,'agregar');

			updateFechasTicket($date , $ticketid);

			agregarTareaCalendario($date,$data['title'],$vendorid,$ticketid,$ticketpuntoid);
			*/
			$data                                     = obtenerDatosTicket ($ticketid);
			$focus                                    = CRMEntity::getInstance ('ordentrabajo');
			$focus->column_fields['date']             = $date;
			$focus->column_fields['enddate']          = $enddate;
			$focus->column_fields['description']      = $descripcion;
			$focus->column_fields['assigned_user_id'] = getUserIdByVendorId ($vendorid);
			$focus->column_fields['ticketid']         = $ticketid;

			$focus->save ('ordentrabajo');

			creaTareaCalendario ($ticketid, $data['title'], $date, $enddate, $vendorid, -1, $focus->id);

			updateDatesPedido ($ticketid);
			return true;
		} else {
			return false;
		}
	}

	function updateFechasTicket ($fecha, $ticketId) {
		global $adb;
		//Fecha fin estimada

		$sql   = "UPDATE  vtiger_troubletickets SET end_estimated_date='$fecha'
				WHERE ticketid = $ticketId  and end_estimated_date < '$fecha' ";
		$query = $adb->query ($sql);

		//Fecha inicio
		$sql   = "UPDATE  vtiger_troubletickets SET start_date='$fecha'
			WHERE ticketid = $ticketId and start_date > '$fecha'";
		$query = $adb->query ($sql);

		$sql   = "SELECT hitoid, proyectoid,p.fechainicial,p.fechafinal  FROM vtiger_troubletickets  LEFT JOIN vtiger_proyectos p ON proyectoid = p.proyectosid  where ticketid=$ticketId";
		$query = $adb->query ($sql);
		if ($reg = $adb->fetch_array ($query)) {
			if (!empty($reg['hitoid'])) {
				/*
				//Fecha hasta
				$sql ="UPDATE  vtiger_hito SET enddate='$fecha'
					WHERE hitoid = ".$reg['hitoid']." and enddate < '$fecha'";
				$query = $adb->query($sql);

				//Fecha desde
				$sql ="UPDATE  vtiger_hito SET inidate='$fecha'
					WHERE hitoid = ".$reg['hitoid']." and inidate > '$fecha'";
				$query = $adb->query($sql);
				*/
				updateDatesHito ($reg['hitoid']);
			}

			if (!empty($reg['proyectoid'])) {

				if ($reg['fechafinal'] < $fecha) {
					//Fecha hasta
					$sql_final   = "UPDATE  vtiger_proyectos SET fechafinal='$fecha' WHERE proyectosid = " . $reg['proyectoid'];
					$query_final = $adb->query ($sql_final);
					registerDateProject ($reg['proyectoid'], $reg['fechainicial'], $reg['fechainicial'], $reg['fechafinal'], $fecha, "end");
				}
				if ($reg['fechainicial'] > $fecha) {
					//Fecha desde
					$sql_inicio   = "UPDATE  vtiger_proyectos SET fechainicial='$fecha'
						WHERE proyectosid = " . $reg['proyectoid'];
					$query_inicio = $adb->query ($sql_inicio);
					registerDateProject ($reg['proyectoid'], $reg['fechainicial'], $fecha, $reg['fechafinal'], $reg['fechafinal'], "start");
				}
			}
		}
	}

	function updateAsignadoA ($desarrollador_id, $ticketId) {
		global $adb;

		$sql   = "UPDATE  vtiger_crmentity SET smownerid = ?
				WHERE crmid = ?";
		$query = $adb->pquery ($sql, array (getUserIdByVendorId ($desarrollador_id), $ticketId));
	}

	function registerDateProject ($projectid, $dateStartPrevious, $dateStartPost, $dateEndPrevious, $dateEndPost, $type) {
		global $adb;

		$sql   = "INSERT INTO vtiger_proyectos_replanificados (proyectoid,fecha_inicio_previa,fecha_inicio_posterior,fecha_fin_previa,fecha_fin_posterior, tipo_replanificacion)
			VALUES ( '$projectid', '$dateStartPrevious', '$dateStartPost', '$dateEndPrevious', '$dateEndPost', '$type');";
		$query = $adb->query ($sql);
	}

	function agregarTareaCalendario ($fecha, $titulo, $desarrollador, $id_registro, $ticketpuntoid) {
		global $adb;
		$sql = "SELECT crmid FROM vtiger_crmentity    ORDER BY crmid DESC LIMIT 0,1";

		$result = $adb->query ($sql, $conex);
		while ($fila = $adb->fetchByAssoc ($result)) {
			$idcrmentity = utf8_encode ($fila['crmid']);
		}

		$idcrmentity++;
		$fechad = date ('Y-m-d h:i:s');

		$sql = "insert into vtiger_crmentity  (crmid,smcreatorid,smownerid,modifiedby,setype,createdtime,modifiedtime,presence,deleted)
		values('$idcrmentity','1','1','0','Calendar','$fecha','$fecha','1','0');";

		$result = $adb->query ($sql, $conex);

		$sql = "update vtiger_crmentity_seq   set id=$idcrmentity;";

		$result   = $adb->query ($sql, $conex);
		$hora_ini = '08:00';
		$hora_fin = '08:59';

		$dbhora_ini = DateTimeField::convertToDBTimeZone ($hora_ini);
		$dbhora_fin = DateTimeField::convertToDBTimeZone ($hora_fin);
		$hora_ini   = $dbhora_ini->format ("H:i");
		$hora_fin   = $dbhora_fin->format ("H:i");

		$sql    = "insert into vtiger_activity  (activityid,subject,activitytype,date_start,due_date,time_start,time_end,status,duration_hours,duration_minutes,eventstatus,priority,visibility,desarrollador_id,tipo_tarea)
			values('$idcrmentity','$titulo','Meeting','$fecha','$fecha','$hora_ini','$hora_fin','Not Started',0,59,'Planned','High','all','$desarrollador',2);";
		$result = $adb->query ($sql, $conex);

		$sql    = "insert into vtiger_activitycf   (activityid) values ($idcrmentity)";
		$result = $adb->query ($sql, $conex);

		$sql    = "insert into vtiger_seactivityrel  (crmid,activityid)
			values('$id_registro','$idcrmentity');";
		$result = $adb->query ($sql, $conex);

		$sql    = "insert into vtiger_ticketpuntos_activityrel  (ticketpuntoid,activityid)
			values('$ticketpuntoid','$idcrmentity');";
		$result = $adb->query ($sql, $conex);
	}

	function agregarDocumentacion () {
		$bufferSalida = '
		<script>
			function agregarDocumentacion(ctrlid) {
				var iNumRows = -1;
				if (!ctrlid)
					ctrlid = \'listaArchivos\';

				ctrlTable = document.getElementById(ctrlid);
				if (ctrlTable) {
					if (iNumRows == -1)
						iNumRows = (ctrlTable.rows.length);
					else
						iNumRows++;

					var row=ctrlTable.insertRow(ctrlTable.rows.length);
					var x1=row.insertCell(0);
					var x2=row.insertCell(1);
					row.id = \'row\'+iNumRows;
					x1.innerHTML=\'<input type="file" id="file\'+iNumRows+\'" name="file[]" />\';
					x2.innerHTML=\'\';
					x1.className = \'crmTableRow small lineOnTop\';
					x2.className = \'crmTableRow small lineOnTop\';
				}
			}
		</script>';

		return $bufferSalida;
	}

	function asociaDocumentacion ($id) {
		global $current_user, $adb;

		$user = $current_user->id;

		//Asocio la documentaci�n
		for ($i = 0; $i < count ($_FILES['file']['name']); $i++) {

			$_FILES['file']['name'][ $i ] = from_html (preg_replace ('/\s+/', '_', $_FILES['file']['name'][ $i ]));
			$sql3                         = "SELECT id FROM vtiger_crmentity_seq";
			$idDoc                        = $adb->fetch_array ($adb->query ($sql3));
			$idDoc                        = $idDoc['id'];
			$idDoc++;

			$sql2 = "UPDATE vtiger_crmentity_seq SET id = " . $idDoc;
			$re2  = $adb->query ($sql2);

			$sql4   = "SELECT prefix,cur_id FROM vtiger_modentity_num WHERE semodule='Documents'";
			$result = $adb->query ($sql4);
			$fila   = $adb->fetchByAssoc ($result);

			$sql5    = "UPDATE vtiger_modentity_num SET cur_id=cur_id+1 WHERE semodule='Documents'";
			$result  = $adb->query ($sql5);
			$note_no = $fila['prefix'] . ($fila['cur_id'] + 1);

			$sql = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
							VALUES(" . $idDoc . "," . $user . "," . $user . ",'Documents','','" . $modifiedtime . "','" . $modifiedtime . "')";

			$re2 = $adb->query ($sql);

			$sql = "INSERT INTO vtiger_notes (notesid,note_no,title,filename,notecontent,folderid,filetype,filelocationtype,filedownloadcount,filestatus,filesize,fileversion)
							VALUES(" . $idDoc . ",'" . $note_no . "','" . $_FILES['file']['name'][ $i ] . "','" . $_FILES['file']['name'][ $i ] . "','',17,'" . $_FILES['file']['type'][ $i ] . "','I',NULL,1," . $_FILES['file']['size'][ $i ] . ",'')";
			$re2 = $adb->query ($sql);

			$sql_cf = "INSERT INTO vtiger_notescf (notesid) VALUES(" . $idDoc . ")";
			$re     = $adb->query ($sql_cf);

			$dbQuery = "INSERT INTO vtiger_senotesrel VALUES ( " . $id . "," . $idDoc . ")";
			$re      = $adb->query ($dbQuery);

			$file = array (
				'name'     => $_FILES['file']['name'][ $i ],
				'type'     => $_FILES['file']['type'][ $i ],
				'tmp_name' => $_FILES['file']['tmp_name'][ $i ],
				'error'    => $_FILES['file']['error'][ $i ],
				'size'     => $_FILES['file']['size'][ $i ],
			);
			uploadAndSaveFile ($idDoc, 'Documents', $file, $user);
		}
	}

	function getDesarrolladores () {

		global $adb;

		$sql = "SELECT p.proveedorid,p.proveedor_name FROM vtiger_proveedor p

					LEFT JOIN vtiger_crmentity ON p.proveedorid=crmid
					LEFT JOIN vtiger_proveedorcf vcf ON vcf.proveedorid=p.proveedorid
					 WHERE
					tipo_proveedor='" . utf8_encode (html_entity_decode (obtenerValorVariable ('TASK_VENDOR_TYPE', 'proveedor'))) . "' AND
					deleted=0 ORDER BY proveedor_name ";

		$result = $adb->query ($sql);
		$i      = 0;

		while ($reg = $adb->fetch_array ($result)) {
			$Vendor[ $i ]['id']   = $reg['proveedorid'];
			$Vendor[ $i ]['name'] = $reg['proveedor_name'];
			$i++;
		}

		return $Vendor;
	}

	//Funcion original haca tabla de proveedores nativa vtiger.
	//cambio por creacion d emodulo personalizado proveedor MP 19/02
	// function getDesarrolladores(){

	// global $adb;

	// $sql = "SELECT v.vendorid,v.vendorname,color FROM vtiger_vendor v

	// left join vtiger_crmentity on v.vendorid=crmid
	// left join vtiger_vendorcf vcf on vcf.vendorid=v.vendorid
	// where
	// vendortype='".utf8_encode(html_entity_decode(obtenerValorVariable('TASK_VENDOR_TYPE','Vendors')))."' and
	// deleted=0 order by vendorname ";

	// $result = $adb->query($sql);
	// $i=0;

	// while ($reg = $adb->fetch_array($result)){
	// $Vendor[$i]['id']=$reg['vendorid'];
	// $Vendor[$i]['name']=$reg['vendorname'];
	// $i++;

	// }

	// return $Vendor;
	// }

	function escribeListaDesarrolladores ($id) {
		$desaSelect   = getDesarrolladores ();
		$bufferSalida = '
		<select name="vendor' . $id . '" class="form-control" id="vendor' . $id . '">
			<option value="" selected>' . getTranslatedString ('Seleccione Desarrollador') . '</option>
		';
		foreach ($desaSelect as $key => $value) {
			$bufferSalida .= '<option ' . isSelected ($value['id'], $_REQUEST[ 'vendor' . $id ]) . ' value="' . $value['id'] . '" >' . utf8_encode ($value['name']) . '</option>
			';
		}
		$bufferSalida .= '</select>';

		return $bufferSalida;
	}

	function isSelected ($x, $y) {
		$return = "";
		if ($x == $y) {
			$return = 'Selected';
		}
		return $return;
	}

	function escribeListaCuentas ($id) {
		return '';
	}

	function getTipos () {
		$Tipos = array (
			array (
				'id'   => 'Incidencia',
				'name' => 'Incidencia',
			),
			array (
				'id'   => 'Peticion',
				'name' => 'Desarrollo',
			),
			array (
				'id'   => 'Adaptacion',
				'name' => 'Adaptacion',
			),
			array (
				'id'   => 'SEO',
				'name' => 'SEO',
			),
		);

		return $Tipos;
	}

	function escribeListaTipos ($id) {
		$tipoSelect   = getTipos ();
		$bufferSalida = '
		<select name="type' . $id . '" class="form-control">
			<option value="" selected>' . getTranslatedString ('Seleccione Tipo') . '</option>
		';

		foreach ($tipoSelect as $key => $value) {
			$bufferSalida .= '<option ' . isSelected ($value['id'], $_REQUEST[ 'type' . $id ]) . ' value="' . $value['id'] . '" >' . utf8_encode ($value['name']) . '</option>
			';
		}
		$bufferSalida .= '</select>';

		return $bufferSalida;
	}

	function escribeListaHowTo ($id_cuenta = '') {
		global $adb;
		$listHowTos = '';

		if (existeTabla ('vtiger_howtos')) {
			$sql = "SELECT howtosid, titulo FROM vtiger_howtos INNER JOIN vtiger_crmentity ON howtosid = crmid WHERE deleted =0";

			if (!empty($id_cuenta)) {
				$sql .= " AND accountht = " . $id_cuenta;
			}
			$result  = $adb->query ($sql);
			$numrows = $adb->num_rows ($result);

			for ($i = 0; $i < $numrows; $i++) {
				$temp_val = $adb->fetch_array ($result);
				$id       = $temp_val['howtosid'];
				$temp_val = $temp_val['titulo'];

				$listHowTos .= "<option value=\"$id\" " . isSelected ($id, $_REQUEST['howtosid']) . ">$temp_val</option>";
			}
		}
		return $listHowTos;
	}

	function escribeListaProyectos ($id_cuenta = '', $proyectoSeleccionado = '') {
		global $adb;
		$sqlProyecto = "SELECT proyectosid, name FROM vtiger_proyectos LEFT JOIN vtiger_crmentity ON proyectosid = crmid WHERE deleted =0";

		if (!empty($id_cuenta)) {
			$sqlProyecto .= " AND accountid = " . $id_cuenta;
		}
		/*
		if (!empty($proyectoSeleccionado)) {
			$sqlProyecto.= " AND proyectosid = ".$proyectoSeleccionado;
		}
		*/
		$resultProyecto  = $adb->query ($sqlProyecto);
		$numrowsProyecto = $adb->num_rows ($resultProyecto);

		$listProjects = '';
		for ($i = 0; $i < $numrowsProyecto; $i++) {
			$temp_val = $adb->fetch_array ($resultProyecto);
			$id       = $temp_val['proyectosid'];
			$temp_val = $temp_val['name'];

			$listProjects .= "<option value=\"$id\" " . isSelected ($id, $_REQUEST['proyectosid']) . ">$temp_val</option>";
		}
		return $listProjects;
	}

	function escribeListaProyectosModelo () {
		global $adb;
		$sqlProyecto = "SELECT proyectosid, name FROM vtiger_proyectos INNER JOIN vtiger_crmentity ON proyectosid = crmid AND deleted=0 WHERE template = 1";

		$resultProyecto  = $adb->query ($sqlProyecto);
		$numrowsProyecto = $adb->num_rows ($resultProyecto);

		$listProjects = '';
		for ($i = 0; $i < $numrowsProyecto; $i++) {
			$temp_val = $adb->fetch_array ($resultProyecto);
			$id       = $temp_val['proyectosid'];
			$temp_val = $temp_val['name'];

			$listProjects .= "<option value=\"$id\" " . isSelected ($id, $_REQUEST['proyectosid']) . ">$temp_val</option>";
		}
		return $listProjects;
	}

	function comprobarFinTicket ($idticket, $vendorId = '') {
		global $adb;
		$sql = "SELECT porcentaje, pointid FROM vtiger_ticketpuntos WHERE ticketid=" . $idticket;

		if (!empty($vendorId)) {
			$sql .= " and desarrollador_id=" . $vendorId;
		}
		$result = $adb->query ($sql, $conex);
		$fin    = 'si';

		while ($row = $adb->fetch_array ($result)) {

			if ($row['porcentaje'] < 100) {
				$fin = 'no';
			}
		}

		return $fin;
	}

	function comprobarFinOTsDeTicket ($ticketid) {
		global $adb;
		$sql = "SELECT * FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND deleted = 0)
				WHERE ticketid = ? AND statusot <> ? AND otadminid != ?";

		$result = $adb->pquery ($sql, array ($ticketid, 'Terminado', obtenerValorVariable ('TIPO_OT_GESTION', 'HelpDesk')));

		if ($adb->num_rows ($result) > 0) {
			return false;
		}

		return true;
	}

	function obtenerTiposOTs ($type) {
		global $adb;

		if ($type == 'Peticion') {
			$type = 'Desarrollo';
		}

		$sql = "SELECT otadminid,typeot FROM vtiger_otadmin INNER JOIN vtiger_crmentity ON (otadminid = crmid AND deleted = 0) WHERE categoriaot = ?";

		$result = $adb->pquery ($sql, array ($type));

		$lstTypeOT = array ();

		while ($row = $adb->fetch_array ($result)) {
			$row['typeot'] = decode_html ($row['typeot']);
			$lstTypeOT[]   = $row;
		}
		return $lstTypeOT;
	}

	function obtenerTareasPorTipoOT ($typeot) {
		global $adb;

		$sql = "SELECT vtiger_otadmin_tasks.taskname, vtiger_otadmin_tasks.taskdescription FROM vtiger_otadmin_tasks INNER JOIN vtiger_otadmin
					ON (vtiger_otadmin_tasks.otadminid = vtiger_otadmin.otadminid)
					INNER JOIN vtiger_crmentity ON (vtiger_otadmin.otadminid = crmid AND deleted = 0)
					WHERE (vtiger_otadmin_tasks.otadminid = ? OR vtiger_otadmin.typeot = ?)";

		$result = $adb->pquery ($sql, array ($typeot, $typeot));

		$lstTaskByTypeOT = array ();

		while ($row = $adb->fetch_array ($result)) {
			$lstTaskByTypeOT[] = $row;
		}
		return $lstTaskByTypeOT;
	}

	function getSEType ($id) {
		global $adb;

		$seType = null;
		$sql    = "SELECT * FROM vtiger_crmentity WHERE crmid=? AND deleted=0";
		$result = $adb->pquery ($sql, array ($id));
		if ($result != null && isset($result)) {
			if ($adb->num_rows ($result) > 0) {
				$seType = $adb->query_result ($result, 0, "setype");
			}
		}
		return $seType;
	}

	/*
		Esta funcion trae un array de los tickets del vendor ingresado,
		que no tengan comenatarios en diarynotes el dia de la fecha con el vendor ingresado ,
		que tengan puntos con  fecha el dia ingresado, y que estos puntos no esten al 100%
	*/
	function obtenerTicketsACerrar ($vendorId, $fecha) {
		global $adb;

		$sql    = "SELECT vtiger_ordentrabajo.ordentrabajoid
				FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity
				ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
				INNER JOIN vtiger_troubletickets
				ON (vtiger_ordentrabajo.ticketid = vtiger_troubletickets.ticketid)
				INNER JOIN vtiger_crmentity crm2
				ON (vtiger_troubletickets.ticketid = crm2.crmid AND crm2.deleted = 0)
		 		INNER JOIN vtiger_ordentrabajo_informes
				ON (vtiger_ordentrabajo_informes.ordentrabajoid = vtiger_ordentrabajo.ordentrabajoid)

				WHERE vtiger_ordentrabajo_informes.vendorid = ? AND vtiger_ordentrabajo_informes.fecha = ?
				AND (nota IS NULL OR nota = 0)";
		$result = $adb->pquery ($sql, array ($vendorId, $fecha));
		$i      = 0;
		if ($result && $adb->num_rows ($result) > 0) {
			while ($reg = $adb->fetch_array ($result)) {
				$retornar[ $i ] = $reg['ordentrabajoid'];
				$i++;
			}
		}
		return $retornar;
	}

	function obtenerTicketsCerrados ($vendorId, $fechaini, $fechafin = '') {
		global $adb;

		if (!empty($fechafin)) {
			$condicionFecha = " vtiger_ordentrabajo_informes.fecha >= '" . $fechaini . "' AND vtiger_ordentrabajo_informes.fecha <= '" . $fechafin . "' ";
		} else {
			$condicionFecha = " vtiger_ordentrabajo_informes.fecha = '" . $fechaini . "'";
		}

		$sql = "SELECT vtiger_ordentrabajo.ordentrabajoid
			FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity
				ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
				INNER JOIN vtiger_ordentrabajo_informes
				ON (vtiger_ordentrabajo_informes.ordentrabajoid = vtiger_ordentrabajo.ordentrabajoid)
				WHERE vtiger_ordentrabajo_informes.vendorid = ? AND " . $condicionFecha . "
				AND (nota IS NOT NULL OR nota > 0)";

		$result = $adb->pquery ($sql, array ($vendorId));
		$i      = 0;
		if ($result && $adb->num_rows ($result) > 0) {
			while ($reg = $adb->fetch_array ($result)) {
				$retornar[ $i ] = $reg['ordentrabajoid'];
				$i++;
			}
		}
		return $retornar;
	}

	function formatearFecha ($fecha) {
		if (!empty($fecha)) {
			$data = substr ($fecha, 8, 2) . '-' . substr ($fecha, 5, 2) . '-' . substr ($fecha, 0, 4);
			return $data;
		}
	}

	function obtenerOrdenTrabajoTickets ($ticketid) {
		global $adb;

		if (!empty($ticketid)) {

			$sql = "SELECT vtiger_ordentrabajo.ordentrabajoid,vtiger_troubletickets.title AS pedidotitle, vtiger_troubletickets.ticketid,
						vtiger_ordentrabajo.description,vtiger_ordentrabajo.date,vtiger_ordentrabajo.enddate,vtiger_vendor.vendorid
						FROM vtiger_ordentrabajo INNER JOIN vtiger_crmentity
						ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
						INNER JOIN vtiger_troubletickets
						ON (vtiger_ordentrabajo.ticketid = vtiger_troubletickets.ticketid)
						INNER JOIN vtiger_crmentity AS crm2
						ON (vtiger_troubletickets.ticketid = crm2.crmid AND crm2.deleted = 0)
						INNER JOIN vtiger_users
						ON (vtiger_users.id = vtiger_crmentity.smownerid)
						INNER JOIN vtiger_vendor
						ON (vtiger_vendor.user_id = vtiger_users.id)
						WHERE vtiger_ordentrabajo.ticketid = ? AND statusot <> 'Terminado'";

			$result = $adb->pquery ($sql, array ($ticketid));
			$i      = 0;
			if ($result && $adb->num_rows ($result) > 0) {
				while ($reg = $adb->fetch_array ($result)) {
					$reg['date']    = formatearFecha ($reg['date']);
					$reg['enddate'] = formatearFecha ($reg['enddate']);
					$retornar[]     = $reg;
					$i++;
				}
			}
		}
		return $retornar;
	}

	function creaTareaCalendario ($id_registro, $titulo, $fecha, $fechafin, $desarrollador, $pointid, $otid) {
		global $adb;
		$turno = 'man';

		/*Aqui comienza a realizar operaciones para insertar una tarea en el calendario*/

		//Extrae el ultimo id del crmentity
		$sql    = "SELECT crmid FROM vtiger_crmentity  ORDER BY crmid DESC LIMIT 0,1";
		$result = $adb->query ($sql);
		while ($fila = $adb->fetchByAssoc ($result)) {
			$idcrmentity = utf8_encode ($fila['crmid']);
		}

		//Incrementa la variable para asignar el nuevo crmid
		$idcrmentity++;
		//Crea un nuevo registro en la tabla crmentity de tipo Calendar
		$sql    = "insert into vtiger_crmentity  (crmid,smcreatorid,smownerid,modifiedby,setype,createdtime,modifiedtime,presence,deleted)
		values('$idcrmentity','1','4722','0','Calendar','$fecha','$fechafin','1','0');";
		$result = $adb->query ($sql);

		//Actualiza el puntero en la tabla crmentity_seq
		$sql    = "update vtiger_crmentity_seq   set id=$idcrmentity;";
		$result = $adb->query ($sql);

		//Aqui define el turno si sera de ma�ana o de tarde. Por default llega de ma�ana
		if ($turno == 'man') {
			$hora_ini = '08:00';
			$hora_fin = '08:59';
		} else {
			$hora_ini = '09:00';
			$hora_fin = '09:59';
		}
		$dbhora_ini = DateTimeField::convertToDBTimeZone ($hora_ini);
		$dbhora_fin = DateTimeField::convertToDBTimeZone ($hora_fin);

		$hora_ini = $dbhora_ini->format ("H:i");
		$hora_fin = $dbhora_fin->format ("H:i");

		//Crea la tarea en el calendario
		$sql    = "insert into vtiger_activity  (activityid,subject,activitytype,date_start,due_date,time_start,time_end,status,duration_hours,duration_minutes,eventstatus,priority,visibility,desarrollador_id,tipo_tarea)
			values('$idcrmentity','$titulo','Meeting','$fecha','$fechafin','$hora_ini','$hora_fin','Not Started',0,59,'Planned','High','all','$desarrollador',2);";
		$result = $adb->query ($sql);

		//Crea el registro en activitycf con el activityid
		$sql    = "insert into vtiger_activitycf   (activityid) values ($idcrmentity)";
		$result = $adb->query ($sql);

		//Crea relacion entre ticket punto y activity
		if (!empty($pointid)) {
			//Crea la relacion entre el activity y el ticket
			$sql    = "insert into vtiger_seactivityrel  (crmid,activityid) values('$id_registro','$idcrmentity');";
			$result = $adb->query ($sql);

			$sql    = "insert into vtiger_ticketpuntos_activityrel  (ticketpuntoid,activityid)
				values('$pointid','$idcrmentity');";
			$result = $adb->query ($sql);
		}

		//Crea relacion entre ticket punto y activity
		if (!empty($otid)) {
			$sql    = "insert into vtiger_seactivityrel  (crmid,activityid)
				values('$otid','$idcrmentity');";
			$result = $adb->query ($sql);
		}

		updateAsignadoA ($desarrollador, $id_registro);
		/*Fin operacion para crear Tarea del calendario */
	}

	function esDiaFestivo ($fecha) {
		global $adb;

		$sqlcheck    = "SHOW TABLES LIKE 'vtiger_diasfestivos'";
		$checkresult = $adb->pquery ($sqlcheck, array ());
		if ($adb->num_rows ($checkresult) < 1) {
			return false;
		}

		$sql = "SELECT * FROM vtiger_diasfestivos WHERE fecha = ?";

		$result = $adb->pquery ($sql, array ($fecha));

		if ($adb->num_rows ($result) > 0) {
			return true;
		}
		return false;
	}

	function getHolidays ($year) {
		global $adb;
		$lst = array ();

		$sqlcheck    = "SHOW TABLES LIKE 'vtiger_holidays'";
		$checkresult = $adb->pquery ($sqlcheck, array ());
		if ($adb->num_rows ($checkresult) < 1) {
			return $lst;
		}

		$sql = "SELECT DISTINCT DAY(date) AS dia,MONTH(date) AS mes, descripcion FROM vtiger_holidays WHERE YEAR(date) = ? OR recurrente = '1'";

		$result = $adb->pquery ($sql, array ($year));

		if ($result) {
			while ($row = $adb->fetchByAssoc ($result)) {
				$lst[] = $row;
			}
		}

		return $lst;
	}

	function calculoProgresoProyecto ($proyectosid) {
		global $adb;
		$sql = "SELECT hitoid FROM vtiger_hito WHERE proyectosid = ?";

		$result = $adb->pquery ($sql, array ($proyectosid));

		$i = 0;
		$j = 0;
		if ($result) {
			while ($row = $adb->fetchByAssoc ($result)) {
				$j += calculoProgresoHito ($row['hitoid']);
				$i++;
			}
		}
		if ($i > 0) {
			return (int) $j / $i;
		}
		return 0;
	}

	function calculoProgresoPlan ($planid, $module) {
		global $adb;
		$sql = "SELECT intervencionid FROM vtiger_intervencion WHERE " . $module . "id = ?";

		$result = $adb->pquery ($sql, array ($planid));

		$i = 0;
		$j = 0;
		if ($result) {
			while ($row = $adb->fetchByAssoc ($result)) {
				$j += calculoProgresoHito ($row['intervencionid']);
				$i++;
			}
		}
		if ($i > 0) {
			return (int) $j / $i;
		}
		return 0;
	}

	function calculoProgresoHito ($hitoid) {
		global $adb;
		$sql = "SELECT count(*) AS totaltareas FROM vtiger_troubletickets WHERE intervencionid = ?";

		$result = $adb->pquery ($sql, array ($hitoid));

		if ($result) {
			$total = $adb->query_result ($result, 0, 'totaltareas');
		}

		$sql = "SELECT count(*) AS totaltareas FROM vtiger_troubletickets WHERE intervencionid = ? AND status = ?";

		$result = $adb->pquery ($sql, array ($hitoid, TICKET_ACCEPTED));

		if ($result) {
			$totalCerrados = $adb->query_result ($result, 0, 'totaltareas');
		}

		if ($total > 0) {
			return $totalCerrados / $total;
		}

		$sql = "SELECT * FROM vtiger_intervencion WHERE intervencionid = ? AND intstate = ?";

		$result = $adb->pquery ($sql, array ($hitoid, 'Finalizado'));

		if ($result) {
			return $adb->num_rows ($result);
		}

		return 0;
	}

	function obtenerHitosProyectos ($proyectosid) {
		global $adb;
		$sql = "SELECT hitoid, name, description FROM vtiger_hito WHERE proyectosid = ?";

		$result = $adb->pquery ($sql, array ($proyectosid));

		while ($row = $adb->fetchByAssoc ($result)) {
			$lst[] = $row;
		}
		return $lst;
	}

	function getClosedTicketsByMonth ($userid, $month, $year) {
		global $adb;
		$sql = "SELECT COUNT(*) AS total FROM vtiger_troubletickets
				INNER JOIN vtiger_crmentity crm ON (vtiger_troubletickets.ticketid = crm.crmid AND crm.deleted = 0)
				INNER JOIN vtiger_ordentrabajo ON (vtiger_troubletickets.ticketid = vtiger_ordentrabajo.ticketid)
				INNER JOIN vtiger_crmentity ON (vtiger_ordentrabajo.ordentrabajoid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
				INNER JOIN vtiger_ticketdates ON (vtiger_troubletickets.ticketid = vtiger_ticketdates.ticketid)
				WHERE vtiger_crmentity.smownerid = ? AND otadminid != ?
				AND YEAR(vtiger_ticketdates.date) = ? AND MONTH(vtiger_ticketdates.date) = ?
				AND vtiger_ticketdates.status = 'TICKET_ACCEPTED'";

		$result = $adb->pquery ($sql, array ($userid, obtenerValorVariable ('TIPO_OT_GESTION', 'HelpDesk'), $year, $month));

		if ($result) {
			return $adb->query_result ($result, 0, 'total');
		}

		return 0;
	}

	function getReportedId ($ticketid) {
		global $adb;
		$sql = "SELECT reportedid FROM vtiger_troubletickets WHERE ticketid = " . $ticketid;

		$result = $adb->query ($sql);

		if ($result) {
			return getUserFullName ($adb->query_result ($result, 0, 'reportedid'));
		}
		return;
	}

	function getMailReportedId ($ticketid) {
		global $adb;
		$sql = "SELECT reportedid FROM vtiger_troubletickets WHERE ticketid = " . $ticketid;

		$result = $adb->query ($sql);

		if ($result) {
			return getUserFullName ($adb->query_result ($result, 0, 'reportedid'));
		}
		return;
	}

	function guardarRegistroTrabajo ($titulo, $cuenta, $tipo, $descripcion, $smownerid, $parentticketid = '', $cerrarVentana = true) {

		global $adb;
		global $current_user;
		if (!is_numeric ($smownerid)) {
			$user    = "SELECT id FROM vtiger_users WHERE user_name='" . $smownerid . "'";
			$rowname = $adb->fetch_array ($adb->query ($user));
			$user    = $rowname['id'];
		} else {
			$user = $smownerid;
		}

		if (empty($user)) {
			$user = -1;
		}//Registro desde el CustomerPortal o Desde Otra Plataforma

		$titulo      = addslashes (($titulo));
		$descripcion = addslashes (($descripcion));

		$modifiedtime = date ("Y-m-d H:i:s");

		$sql3 = "SELECT id FROM vtiger_crmentity_seq";
		$id   = $adb->fetch_array ($adb->query ($sql3));
		$id   = $id['id'];
		$id++;

		$sql2 = "UPDATE vtiger_crmentity_seq SET id = " . $id;
		$re2  = $adb->query ($sql2);

		$sql4   = "SELECT prefix,cur_id FROM vtiger_modentity_num WHERE num_id=6";
		$result = $adb->query ($sql4);
		$fila   = $adb->fetchByAssoc ($result);

		$sql5      = "UPDATE vtiger_modentity_num SET cur_id=cur_id+1 WHERE num_id=6";
		$result    = $adb->query ($sql5);
		$ticket_no = $fila['prefix'] . ($fila['cur_id'] + 1);

		$sql = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
						VALUES(" . $id . "," . $user . "," . $user . ",'HelpDesk','" . $descripcion . "','" . $modifiedtime . "','" . $modifiedtime . "')";

		$re2 = $adb->query ($sql);

		$sql_t = "INSERT INTO vtiger_troubletickets(ticketid,type,customerdescription,ticket_no,parent_id,status,title) VALUES(" . $id . ",'" . $tipo . "','" . $descripcion . "','" . $ticket_no . "','" . $cuenta . "','TICKET_OPEN','" . $titulo . "')";
		$re2   = $adb->query ($sql_t);

		$sql_cf = "INSERT INTO vtiger_ticketcf (ticketid) VALUES(" . $id . ")";
		$re     = $adb->query ($sql_cf);

		if (isset($_REQUEST['contacto_solicitante'])) {
			$sql_cf = "UPDATE vtiger_troubletickets SET contacto_solicitante = '" . $_REQUEST['contacto_solicitante'] . "' WHERE ticketid = " . $id;
			$re     = $adb->query ($sql_cf);
		}

		enviaNotificaciones ($id);

		//Asocio la documentaci�n
		for ($i = 0; $i < count ($_FILES['file']['name']); $i++) {

			$_FILES['file']['name'][ $i ] = from_html (preg_replace ('/\s+/', '_', $_FILES['file']['name'][ $i ]));
			$sql3                         = "SELECT id FROM vtiger_crmentity_seq";
			$idDoc                        = $adb->fetch_array ($adb->query ($sql3));
			$idDoc                        = $idDoc['id'];
			$idDoc++;

			$sql2 = "UPDATE vtiger_crmentity_seq SET id = " . $idDoc;
			$re2  = $adb->query ($sql2);

			$sql4   = "SELECT prefix,cur_id FROM vtiger_modentity_num WHERE semodule='Documents'";
			$result = $adb->query ($sql4);
			$fila   = $adb->fetchByAssoc ($result);

			$sql5    = "UPDATE vtiger_modentity_num SET cur_id=cur_id+1 WHERE semodule='Documents'";
			$result  = $adb->query ($sql5);
			$note_no = $fila['prefix'] . ($fila['cur_id'] + 1);

			$sql = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
							VALUES(" . $idDoc . "," . $user . "," . $user . ",'Documents','','" . $modifiedtime . "','" . $modifiedtime . "')";

			$re2 = $adb->query ($sql);

			$sql = "INSERT INTO vtiger_notes (notesid,note_no,title,filename,notecontent,folderid,filetype,filelocationtype,filedownloadcount,filestatus,filesize,fileversion)
							VALUES(" . $idDoc . ",'" . $note_no . "','" . $_FILES['file']['name'][ $i ] . "','" . $_FILES['file']['name'][ $i ] . "','',17,'" . $_FILES['file']['type'][ $i ] . "','I',NULL,1," . $_FILES['file']['size'][ $i ] . ",'')";
			$re2 = $adb->query ($sql);

			$sql_cf = "INSERT INTO vtiger_notescf (notesid) VALUES(" . $idDoc . ")";
			$re     = $adb->query ($sql_cf);

			$dbQuery = "INSERT INTO vtiger_senotesrel VALUES ( " . $id . "," . $idDoc . ")";
			$re      = $adb->query ($dbQuery);

			$current_user->id = $user;
			$file             = array (
				'name'     => $_FILES['file']['name'][ $i ],
				'type'     => $_FILES['file']['type'][ $i ],
				'tmp_name' => $_FILES['file']['tmp_name'][ $i ],
				'error'    => $_FILES['file']['error'][ $i ],
				'size'     => $_FILES['file']['size'][ $i ],
			);
			uploadAndSaveFile ($idDoc, 'Documents', $file, $user);
		}

		//Asocio la documentacion si viene desde un CustomerPortal
		$i         = 1;
		$bCustomer = false;
		while (isset($_FILES[ 'file' . $i ]['name'])) {
			$_FILES[ 'file' . $i ]['name'] = from_html (preg_replace ('/\s+/', '_', $_FILES[ 'file' . $i ]['name']));
			$sql3                          = "SELECT id FROM vtiger_crmentity_seq";
			$idDoc                         = $adb->fetch_array ($adb->query ($sql3));
			$idDoc                         = $idDoc['id'];
			$idDoc++;

			$sql2 = "UPDATE vtiger_crmentity_seq SET id = " . $idDoc;

			$re2 = $adb->query ($sql2);

			$sql4   = "SELECT prefix,cur_id FROM vtiger_modentity_num WHERE semodule='Documents'";
			$result = $adb->query ($sql4);
			$fila   = $adb->fetchByAssoc ($result);

			$sql5    = "UPDATE vtiger_modentity_num SET cur_id=cur_id+1 WHERE semodule='Documents'";
			$result  = $adb->query ($sql5);
			$note_no = $fila['prefix'] . ($fila['cur_id'] + 1);

			$sql = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
							VALUES(" . $idDoc . "," . $user . "," . $user . ",'Documents','','" . $modifiedtime . "','" . $modifiedtime . "')";

			$re2 = $adb->query ($sql);

			$sql = "INSERT INTO vtiger_notes (notesid,note_no,title,filename,notecontent,folderid,filetype,filelocationtype,filedownloadcount,filestatus,filesize,fileversion)
							VALUES(" . $idDoc . ",'" . $note_no . "','" . $_FILES[ 'file' . $i ]['name'] . "','" . $_FILES[ 'file' . $i ]['name'] . "','',17,'" . $_FILES[ 'file' . $i ]['type'] . "','I',NULL,1," . $_FILES[ 'file' . $i ]['size'] . ",'')";
			$re2 = $adb->query ($sql);

			$sql_cf = "INSERT INTO vtiger_notescf (notesid) VALUES(" . $idDoc . ")";
			$re     = $adb->query ($sql_cf);

			$dbQuery = "INSERT INTO vtiger_senotesrel VALUES ( " . $id . "," . $idDoc . ")";
			$re      = $adb->query ($dbQuery);

			$current_user->id = $user;
			$file             = array (
				'name'     => $_FILES[ 'file' . $i ]['name'],
				'type'     => $_FILES[ 'file' . $i ]['type'],
				'tmp_name' => $_FILES[ 'file' . $i ]['tmp_name'],
				'error'    => $_FILES[ 'file' . $i ]['error'],
				'size'     => $_FILES[ 'file' . $i ]['size'],
			);
			uploadAndSaveFile ($idDoc, 'Documents', $file, $user);
			$i++;
			$bCustomer = true;
		}
		//Si hay informaci�n de proyectos e hitos entonces se registran.
		if (isset($_REQUEST['proyectosid']) && !empty($_REQUEST['proyectosid']) &&
			isset($_REQUEST['hitoid']) && !empty($_REQUEST['hitoid'])
		) {
			$sql = "UPDATE vtiger_troubletickets SET hitoid = " . $_REQUEST['hitoid'] . ", proyectoid = " . $_REQUEST['proyectosid'] . " WHERE ticketid = " . $id;
			$adb->query ($sql);
		}

		if (existeCampoTabla ('reportedid', 'vtiger_troubletickets')) {
			$sql = "UPDATE vtiger_troubletickets SET reportedid = " . $current_user->id . " WHERE ticketid = " . $id;
			$adb->query ($sql);
		}
		if (existeCampoTabla ('parentticketid', 'vtiger_troubletickets') && !empty($parentticketid)) {
			$sql = "UPDATE vtiger_troubletickets SET parentticketid = " . $parentticketid . " WHERE ticketid = " . $id;
			$adb->query ($sql);
		}
		if ($bCustomer) {
			die('OK-CUSTOMER');
		}

		if ($cerrarVentana) {
			echo cierraVentana ();
		}

		return $id;
	}

	function enviaNotificaciones ($id = '') {
		return;
	}

	function cierraVentana () {
		$bufferSalida = '
		<script>
			window.opener.location.reload();
			window.opener.location.href = window.opener.location.href;
		  	window.close();
		</script>
		';

		return $bufferSalida;
	}

	function getCommentsTicket ($type, $ticketid) {
		global $adb;

		if ($type == 'INTEGRATOR') {
			if (existeCampoTabla ('text_integrator', 'vtiger_troubletickets')) {
				$field = 'text_integrator';
			}
		} elseif ($type == 'TESTER') {
			if (existeCampoTabla ('comment_tester', 'vtiger_troubletickets')) {
				$field = 'comment_tester';
			}
		} elseif ($type == 'COORDINATOR') {
			$field = 'texto_val_coordinador';
		} elseif ($type == 'CLIENTE') {
			$field = 'validado_cliente';
		}

		if (!$field) {
			return;
		}

		$sql    = "SELECT $field FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON (ticketid = crmid AND deleted = 0)
					WHERE ticketid = ?";
		$result = $adb->pquery ($sql, array ($ticketid));

		return $adb->query_result ($result, 0, $field);
	}

	function obtenerMailCoordinador ($ticketid) {
		$pedido     = CRMEntity::getInstance ('HelpDesk');
		$pedido->id = $ticketid;
		$pedido->retrieve_entity_info ($ticketid, 'HelpDesk');

		if (!empty($pedido->column_fields['coordinadorid'])) {
			$coordinador = new Users();
			$coordinador->retrieveCurrentUserInfoFromFile ($pedido->column_fields['coordinadorid']);

			return $coordinador->column_fields['email1'];
		}
	}

	function obtenerMailUsuarioQueReporta ($ticketid) {
		global $adb;
		$sql = "SELECT reportedid FROM vtiger_troubletickets WHERE ticketid = " . $ticketid;

		$result   = $adb->query ($sql);
		$reportid = $adb->query_result ($result, 0, 'reportedid');

		if (!empty($reportid)) {
			$usr = new Users();
			$usr->retrieveCurrentUserInfoFromFile ($reportid);

			return $usr->column_fields['email1'];
		}
	}

	function registrosSinRespuestaCliente () {
		global $adb;

		/*$sql = "
		SELECT vtiger_troubletickets.ticketid FROM vtiger_troubletickets INNER JOIN vtiger_crmentity ON (vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
			INNER JOIN
			(SELECT max(date) as date,ticketid FROM `vtiger_notificaciones`
				INNER JOIN `vtiger_notificacionescf` ON (vtiger_notificaciones.notificacionid = vtiger_notificacionescf.notificacionid)
				INNER JOIN `vtiger_crmentity` crm ON (vtiger_notificaciones.notificacionid = crm.crmid AND crm.deleted = 0 AND crm.smownerid <> -1)
				WHERE ticketid > 0 GROUP BY ticketid
			 UNION
			 SELECT max(date) as date,relcrmid FROM `vtiger_notificaciones`
				INNER JOIN `vtiger_crmentity` crm ON (vtiger_notificaciones.notificacionid = crm.crmid AND crm.deleted = 0 AND crm.smownerid <> -1)
				WHERE relcrmid > 0 GROUP BY relcrmid) as t1
			on (t1.ticketid = vtiger_troubletickets.ticketid)
			WHERE TIMESTAMPDIFF(DAY,t1.date,now()) > ? AND vtiger_troubletickets.status = ?";
		*/
		$sql = "SELECT t2.ticketid FROM (SELECT vtiger_troubletickets.ticketid, min(TIMESTAMPDIFF(DAY,t1.date,now())) AS days FROM vtiger_troubletickets INNER JOIN vtiger_crmentity
					ON (vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
					INNER JOIN
					(SELECT ticketid, date FROM `vtiger_ticketdates` WHERE status = 'TICKET_ASSIGNED') AS t1
					ON (t1.ticketid = vtiger_troubletickets.ticketid)
					WHERE vtiger_troubletickets.status =  'TICKET_TO_VALIDATE_CUSTOMER'
					GROUP BY vtiger_troubletickets.ticketid HAVING days > ?) AS t2";

		$result = $adb->pquery ($sql, array (MAX_DIAS_ESPERA_NOTIFICACION));

		$lst = array ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$lst[] = $row['ticketid'];
		}
		return $lst;
	}

	function registrosConPermisosDesfazados () {

		global $adb;
		global $current_user;
		$lst = array ();

		$sql = getListQueryPanel ($id, "((vtiger_troubletickets.status='" . TICKET_ASSIGNED . "' and developing = 0  and end_estimated_date < CURDATE() and end_estimated_date!='0000-00-00') AND comment_infraestructura != '' )");

		$result   = $adb->query ($sql);
		$noofrows = $adb->num_rows ($result);

		for ($i = 0; $i < $noofrows; $i++) {
			$_row              = $adb->fetch_array ($result);
			$id                = $_row["ticketid"];
			$seconds           = strtotime ($_row["estimada"]) - strtotime ($_row["start_date"]);
			$diffDiasEstimados = intval ($seconds / 60 / 60 / 24);

			$seconds  = strtotime ('now') - strtotime ($_row["start_date"]);
			$diffDias = intval ($seconds / 60 / 60 / 24);

			if ($diffDias > $diffDiasEstimados * 1.25) { //25% mas de lo estimado
				$lst[] = $id;
			}
		}

		return $lst;
	}

	function getDateStatus ($ticketid, $status) {
		global $adb;

		$sql    = "SELECT max(date) AS date FROM vtiger_ticketdates WHERE status = ? AND ticketid = ?";
		$result = $adb->pquery ($sql, array ($status, $ticketid));

		return $adb->query_result ($result, 0, 'date');
	}

	function checkStatusAccount ($conditions) {
		global $adb;
		$sql = "SELECT vtiger_troubletickets.ticketid, min(TIMESTAMPDIFF(DAY,t1.date,now())) AS days FROM vtiger_troubletickets INNER JOIN vtiger_crmentity
					ON (vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
					INNER JOIN
					(SELECT ticketid, date FROM `vtiger_ticketdates` WHERE status = 'TICKET_PENDING_CONFIRMATION_OF_CUSTOMER') AS t1
					ON (t1.ticketid = vtiger_troubletickets.ticketid)
					WHERE vtiger_troubletickets.status =  'TICKET_PENDING_CONFIRMATION_OF_CUSTOMER' " . $conditions . "
					GROUP BY vtiger_troubletickets.ticketid HAVING days > ?";

		$result = $adb->pquery ($sql, array (MAX_DIAS_ESPERA_CIERRE_PEDIDOS));

		if ($adb->num_rows ($result) > 0) //		if ($adb->query_result($result,0,'days') > MAX_DIAS_ESPERA_CIERRE_PEDIDOS)
		{
			return 'disabled="disabled"';
		}
		return;
	}

	function obtenerDesarrolladores ($idregistro) {

		global $adb;

		/*$sql = "SELECT distinct vendorname FROM vtiger_reldesa r
				left join vtiger_vendor v on r.idvendor=v.vendorid
				where idticket=$idregistro";*/

		//and (rol='Desarrollo' or rol='')";

		$sql = "SELECT DISTINCT v.proveedorid,v.proveedor_name FROM vtiger_proveedor v
				INNER JOIN vtiger_crmentity crm ON (v.proveedorid = crm.crmid AND crm.deleted = 0)
				INNER JOIN vtiger_users ON (user_id = id)
				INNER JOIN vtiger_crmentity ON (id = vtiger_crmentity.smownerid)
				INNER JOIN vtiger_troubletickets
				WHERE vtiger_troubletickets.ticketid=$idregistro";

		// $conditionCoordinador = obtenerValorVariable('TIPO_OT_GESTION','HelpDesk');
		// if (!empty($conditionCoordinador))
		// $sql.= " AND vtiger_ordentrabajo.otadminid <> ".$conditionCoordinador;

		$result = $adb->query ($sql);
		$i      = 0;

		while ($rowVendor = $adb->fetch_array ($result)) {

			if ($i > 0) {
				$Vendor .= ',';
			}
			$Vendor .= $rowVendor['proveedor_name'];

			$i++;
		}

		return $Vendor;
	}

	function obtenerListaHowTo ($tipoht, $subtipoht) {
		global $adb;

		if (!empty($tipoht) && !empty($subtipoht)) {
			$sql     = "SELECT howtosid, titulo FROM vtiger_howtos INNER JOIN vtiger_crmentity ON howtosid = crmid WHERE deleted =0 AND tipoht = ? AND subtipoht = ?";
			$result  = $adb->pquery ($sql, array ($tipoht, $subtipoht));
			$numrows = $adb->num_rows ($result);

			$lstHT = array ();
			for ($i = 0; $i < $numrows; $i++) {
				$row                       = $adb->fetch_array ($result);
				$row['titulo']             = decode_html ($row['titulo']);
				$lstHT[ $row['howtosid'] ] = $row['titulo'];
			}
			return $lstHT;
		}
	}

	function obtenerTodosPasos () {
		//global $conex;
		global $adb;
		$sql = 'SELECT * FROM vtiger_item_pedido INNER JOIN vtiger_crmentity
				ON (crmid = item_pedidoid AND deleted = 0)';

		//$result = mysql_query($sql,$conex);
		$result = $adb->query ($sql, $conex);

		if ($result) {
			//$row = mysql_fetch_array($result);
			return $result;
		}
		return;
	}

	function obtenerPasosPedido ($id_pedido) {

		global $adb;

		if (!empty($id_pedido)) {
			$sql = "SELECT *  FROM vtiger_pedido_pasos INNER JOIN vtiger_item_pedido ON vtiger_pedido_pasos.paso_rel_id = vtiger_item_pedido.item_pedidoid WHERE vtiger_pedido_pasos.pedidoid =?";

			//$sql="SELECT howtosid, titulo FROM vtiger_howtos INNER JOIN vtiger_crmentity ON howtosid = crmid WHERE deleted =0 AND tipoht = ? AND subtipoht = ?";
			$result = $adb->pquery ($sql, array ($id_pedido));

			if ($result) {
				while ($row = $adb->fetch_array ($result)) {
					$lst[] = $row;
				}
			}

			return $lst;
		}
	}

	function obtenerHorasPedido ($id_pedido) {

		global $adb;

		if (!empty($id_pedido)) {
			$sql = "SELECT SUM(horas_est)  FROM vtiger_pedido_pasos INNER JOIN vtiger_item_pedido ON vtiger_pedido_pasos.paso_rel_id = vtiger_item_pedido.item_pedidoid WHERE vtiger_pedido_pasos.pedidoid =?";

			//$sql="SELECT howtosid, titulo FROM vtiger_howtos INNER JOIN vtiger_crmentity ON howtosid = crmid WHERE deleted =0 AND tipoht = ? AND subtipoht = ?";
			$result = $adb->pquery ($sql, array ($id_pedido));

			if ($result) {
				while ($row = $adb->fetch_array ($result)) {
					$lst[] = $row;
				}
			}

			return $lst;
		}
	}

	function escribeListaModuloPlan () {
		global $adb;
		$sqlmodules = "SELECT mp.module, mp.label
						FROM vtiger_module_plan mp
						INNER JOIN vtiger_tab t ON t.tabid = mp.tabid AND t.presence = 0";

		$resultmodules  = $adb->query ($sqlmodules);
		$numrowsmodules = $adb->num_rows ($resultmodules);

		$listmodules = '';
		for ($i = 0; $i < $numrowsmodules; $i++) {
			$temp_val = $adb->fetch_array ($resultmodules);
			$id       = $temp_val['module'];
			$temp_val = $temp_val['label'];

			$listmodules .= "<option value=\"$id\" " . isSelected ($id, $_REQUEST['moduleplan']) . ">$temp_val</option>";
		}
		return $listmodules;
	}

	function escribeListaPlan ($module) {
		global $adb;
		$sqlmodules = "select mp." . $module . "id, mp.name
								from vtiger_" . $module . " mp
								INNER JOIN vtiger_crmentity c ON c.crmid = " . $module . "id
								WHERE c.deleted =0";

		$resultmodules  = $adb->query ($sqlmodules);
		$numrowsmodules = $adb->num_rows ($resultmodules);

		$listmodules = '';
		$listmodules .= '<option value="" selected>' . $mod_strings['LBL_PLAN'] . '</option>';
		for ($i = 0; $i < $numrowsmodules; $i++) {
			$temp_val = $adb->fetch_array ($resultmodules);
			$id       = $temp_val[ $module . "id" ];
			$temp_val = $temp_val['name'];

			$listmodules .= "<option value=\"$id\" " . isSelected ($id, $_REQUEST['planid']) . ">$temp_val</option>";
		}

		return $listmodules;
	}

?>