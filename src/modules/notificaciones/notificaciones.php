<?php
	define (MAX_REGISTROS_PAGINA,8);
	define (ACCOUNTID_EMPRESAFACIL,798);
	@include_once 'config.inc.php';
	@include_once 'include/utils/interfazAuxiliar.php';
	@include_once 'config.inc.php';
	require_once('include/utils/utils.php');

	class CNotificaciones {
		var $adb;
		var $mod_strings;
		var $Funcion;
		var $gbd;
		var $contactid;
		var $ticketid;
		var $current_language;
		var $idcurrent_language;
		var $eventCode;
		var $regInicial;
		var $orderInicial;
		var $relcrmid;
		var $body;
		var $subject;
		var $accountid;
		var $attachments;
		var $list_fields = Array(
            'Subject'=>Array('vtiger_notificaciones'=>'subject'),
            'Status'=>Array('vtiger_notificaciones'=>'status'),
            'Date'=>Array('vtiger_notificaciones'=>'date'),
    );
    var $list_fields_name = Array(
            'Subject'=>'subject',
            'Status'=>'subject',
            'Date'=>'date',

    );
	 var $search_fields = Array(
            'Subject'=>Array('vtiger_notificaciones'=>'subject'),
            'Status'=>Array('vtiger_notificaciones'=>'status'),
            'Date'=>Array('vtiger_notificaciones'=>'date'),
    );
    var $search_fields_name = Array(
            'Subject'=>'subject',
            'Subject'=>'status',
            'Date'=>'date',
    );
		function ejecutaConsulta($sql, $params = array()) {
			global $adb;

			if (!isset($adb)) {
				$result = mysql_query($sql,$this->gdb);
			} else {
				$result = $this->adb->pquery($sql, $params);
			}
			return $result;
		}

		function retornaFila($result) {
			global $adb;

			if (!isset($adb)) {
				$row = mysql_fetch_array($result);
			} else {
				$row = $this->adb->fetch_array($result);
			}
			return $row;
		}

		function cantidadRegistros($result) {
			global $adb;

			if (!isset($adb)) {
				$row = mysql_num_rows($result);
			} else {
				$row = $this->adb->num_rows($result);
			}
			return $row;
		}

		function CNotificaciones() {
			global $adb;
			global $mod_strings;
			global $current_language;
			global $dbconfig;
			global $current_user;
			global $plat_madre_empresafacil;

			$this->mod_strings = return_module_language($current_user->column_fields['language'],'notificaciones');
			$this->regInicial = 1;
			$this->campoOrderInicial = " A1.date ";
			$this->orderInicial = " DESC ";

			if (isset($_SESSION['orderInicial']))
				$this->orderInicial = $_SESSION['orderInicial'];


			if (isset($_REQUEST['Funcion']))
				$this->Funcion = $_REQUEST['Funcion'];

			if (isset($_REQUEST['relcrmid']))
				$this->relcrmid = $_REQUEST['relcrmid'];

			if (isset($_REQUEST['regInicial']))
				$this->regInicial = $_REQUEST['regInicial'];

			if (isset($current_language))
				$this->asignarIdioma($current_language);

			$this->asignarDatosContacto();

			$this->eventCode = '';
			$this->ticketid = 0;

			if(($_REQUEST['ticketid']=='' || $_REQUEST['ticketid']=='0') && $_REQUEST['status']=='ACCEPTED'){
				$_REQUEST['ticketid']=$_REQUEST['idcrm'];
			}

			if (esVistaCliente($_SESSION['authenticated_user_id'])) {//Por el cambio de arquitectura
				$contactid = $current_user->getContactId();
				$adb = conectaPlataformaHija($plat_madre_empresafacil);
				$focus = CRMEntity::getInstance('Contacts');
				$focus->id = $contactid;
				$focus->retrieve_entity_info($contactid,'Contacts');

				if (isset($focus->column_fields['account_id']) && !empty($focus->column_fields['account_id'])){
					$this->accountid = $focus->column_fields['account_id'];
				}
				$adb = conectaPlataformaHija($_SESSION['plat']);
			}

			if (isset($_REQUEST['ticketid']))
				$this->asignarDatosTicket($_REQUEST['ticketid']);

			if (isset($_REQUEST['plat_customer']))
				$_SESSION['plat'] = $_REQUEST['plat_customer'];
			if (isset($_SESSION['plat'])) {
				$dbconfig['db_name'] = 'pg_crm_'.$_SESSION['plat'];
				$dbconfig['db_username'] = 'usr_'.$_SESSION['plat'];
				$dbconfig['db_password'] = md5('usr_'.$_SESSION['plat']);
			}

			$adb = conectaPlataformaHija($plat_madre_empresafacil);

			$this->adb = $adb;
			$this->attachments = array();
		}

		function asignarIdioma($current_language) {
			if ($current_language == 'es_es')
				$this->idcurrent_language = 585;
			elseif ($current_language == 'en_us')
				$this->idcurrent_language = 586;
			elseif ($current_language == 'pt_pt')
				$this->idcurrent_language = 587;

			$this->current_language = $current_language;
		}

		function asignarDatosContacto() {
			global $current_user,$adb,$plat_madre_empresafacil;
			//Las restricciones deben ser o personalizadas

			$contactid = $current_user->getContactId();
			if (!empty($contactid)) {
				$adb = conectaPlataformaHija($plat_madre_empresafacil);
				$focus = CRMEntity::getInstance('Contacts');
				$focus->id = $contactid;
				$focus->retrieve_entity_info($contactid,'Contacts');

				if (isset($focus->column_fields['account_id']) && !empty($focus->column_fields['account_id']))
					$accountid = $focus->column_fields['account_id'];

				$adb = conectaPlataformaHija($_SESSION['plat']);
			}

			$this->contactid = $contactid;
			$this->accountid = $accountid;
		}

		function asignarDatosTicket($ticketid) {
			$this->ticketid = $ticketid;
		}


		function obtenerCorreoJefeTurno() {
			/*
			$sql = "SELECT email1 FROM vtiger_users WHERE is_jefedesarrollo = 1";

			$result = $this->ejecutaConsulta($sql);

			if ($row = $this->retornaFila($result)) {
				return $row['email1'];
			}
			*/

			return 'dpolo@timemanagement.es';
		}

		function obtenerNombreCuenta($accountid) {
			$sql = "SELECT a.accountid, a.accountname FROM vtiger_account a
						INNER JOIN vtiger_accountscf cf on a.accountid=cf.accountid
						INNER JOIN vtiger_crmentity c on c.crmid=a.accountid
						WHERE 	c.deleted=0
						AND a.accountid = ".$accountid;

			$result = $this->ejecutaConsulta($sql);

			if ($row = $this->retornaFila($result)) {
				return $row['accountname'];
			}

			return;
		}

		function obtenerDatosContacto($contactid) {
			$campoIdioma = "'Español'";
			if (existeCampoTabla('idioma_notificaiones','vtiger_contactdetails'))
				$campoIdioma = 'idioma_notificaiones';
			$sql = "SELECT A.contactid, CONCAT(firstname,' ',lastname) as name, email, $campoIdioma as idioma, accountid  FROM vtiger_contactdetails A INNER JOIN vtiger_crmentity B
						ON (A.contactid = B.crmid AND B.deleted = 0)
						INNER JOIN vtiger_users U ON (A.contactid = U.contactid)
						WHERE A.contactid = ".$contactid;

			$result = $this->ejecutaConsulta($sql);
			return $this->retornaFila($result);
		}

		function getLanguageEmail($language) {
			$sql    = "SELECT picklist_valueid as idiomaid FROM vtiger_emails_idiomas WHERE cf_807 = '$language'";
			$result = $this->ejecutaConsulta($sql);
			return $this->retornaFila($result);
		}

		function obtenerContactosNotificacion($notificacionid,$bSoloId = false) {
			$bufferSalida = '';

			$sql = "SELECT contactid, CONCAT(lastname,', ',firstname) as nombre FROM vtiger_contactdetails A
						INNER JOIN vtiger_crmentity B ON (A.contactid = B.crmid AND B.deleted = 0)
						INNER JOIN vtiger_crmentityrel C ON (A.contactid = C.relcrmid)
						WHERE C.crmid = ".$notificacionid." AND C.module = 'notificaciones' ORDER BY 2";

			$result = $this->ejecutaConsulta($sql);

			//while ($row = $this->adb->fetch_array($result)) {
			while ($row = $this->retornaFila($result)) {
				if (!empty($bufferSalida))
					$bufferSalida.= ', ';
				if ($bSoloId)
					$bufferSalida .= $row['contactid'];
				else
					$bufferSalida.= $row['nombre'];


			}

			return $bufferSalida;
		}

		function SelectCuentas($accountid = null,$isPanel = false) {
			$parametrosAdicionales = 'onchange="actualizarContactos(this.value);"';
			$seleccione = true;
			$condicionPanel = '';

			if ($isPanel)
				$condicionPanel = " AND a.accountid = ".$accountid;

			$sql = "SELECT a.accountid, a.accountname FROM vtiger_account a
						INNER JOIN vtiger_accountscf cf on a.accountid=cf.accountid
						INNER JOIN vtiger_crmentity c on c.crmid=a.accountid
						WHERE 	c.deleted=0 and LENGTH(TRIM(a.accountname)) > 0
						$condicionPanel
						ORDER BY a.accountname";


			if ($this->adb) {
				$result = $this->adb->query($sql);


				while ($row = $this->adb->fetch_array($result)) {
					$datosCuentas[] = array($row['accountid'],$row['accountname']);
				}
			} else {
				$result = mysql_query($sql,$this->gdb);

				while ($row = mysql_fetch_array($result)) {
					$datosCuentas[] = array($row['accountid'],$row['accountname']);
				}
			}

			if ($isPanel) {
				$seleccione = false;
				$parametrosAdicionales = 'onchange="return false;" onclick="return false;"';
			}

			$bufferSalida = escribeSelect("accountid","accountid",$datosCuentas, $disabled.$parametrosAdicionales, $seleccione, $accountid);
			return $bufferSalida;
		}

		function escribeComboFiltro($id,$funcion,$panel = 'received') {
			$bufferSalida = '
			<select id="filterStatus'.$panel.'" name="FiltroNotificaciones" onchange="actualizarListaSegunFiltro(\''.$id.'\',\''.$funcion.'\',\''.$panel.'\');">
				<optgroup label="'.  getTranslatedString("Status") .'">
				<option value="">'.$this->mod_strings['Todas'].'</option>
				<option value="Unread">'.$this->mod_strings['NoLeidas'].'</option>
				<option value="Read">'.$this->mod_strings['Leidas'].'</option>
				</optgroup>
			</select>
			';
			return $bufferSalida;
		}

		function EscribeFormaEnviarNotificacion($isModal = false,$isPanel = false) {
			global $adb, $currentModule, $mod_strings, $app_strings, $theme;

			if (esVistaCliente($_SESSION['authenticated_user_id'])) {
				$_REQUEST['accountid'] = ACCOUNTID_EMPRESAFACIL;
			} else {
				if (isset($_REQUEST['notificacionid']) && !empty($_REQUEST['notificacionid'])) {
					$usr = new Users;
					$sql = "SELECT user_name FROM vtiger_users INNER JOIN vtiger_crmentity ON (smownerid = id) WHERE crmid = ".$_REQUEST['notificacionid'];
					$res = $adb->query($sql);
					$usr->column_fields['user_name'] = $adb->query_result($res,0,'user_name');
					$contactid = $usr->getContactId(null);
					$focus = CRMEntity::getInstance('Contacts');
					$focus->id = $contactid;
					$focus->retrieve_entity_info($contactid,'Contacts');

					if (isset($focus->column_fields['account_id']) && !empty($focus->column_fields['account_id']))
						$_REQUEST['accountid'] = $focus->column_fields['account_id'];
				}
			}
			if (isset($_REQUEST['accountid']))
				$this->accountid = $_REQUEST['accountid'];
			if (isset($_REQUEST['subject']))
				$this->subject = $_REQUEST['subject'];
			if (isset($_REQUEST['ticketid']))
				$this->ticketid = $_REQUEST['ticketid'];
			if (isset($_REQUEST['conversacionid']))
				$this->conversacionid = $_REQUEST['conversacionid'];

			$contactList = 'Seleccione la cuenta para ver los contactos disponibles';
			if ($this->accountid) {
				$contactList = $this->EscribeFormaContactos($this->accountid);
			}

			require_once('Smarty_setup.php');
			$smarty = new vtigerCRM_Smarty;
			$smarty->assign("MOD", $mod_strings);
			$smarty->assign("APP", $app_strings);

			$smarty->assign("THEME", $theme);
			$smarty->assign("IMAGE_PATH", $image_path);
			$smarty->assign('SELECT_ACCOUNTS',$this->SelectCuentas($this->accountid,$isPanel));
			$smarty->assign('LIST_CONTACTS',$contactList);
			$smarty->assign('SUBJECT',$this->subject);
			$smarty->assign('TICKETID',$this->ticketid);
			$smarty->assign('CONVERSACIONID',$this->conversacionid);


			$bufferSalida = $smarty->fetch("modules/".$currentModule."/formaNotificacion.tpl");

			return $bufferSalida;

			/*
			$realpath = '';
			$bufferSalida = '';
			$action = escribeEntradaOculta('action','action','index');

			if ($isModal) {
				$action = escribeEntradaOculta('action','action','ActivityAjax');
				$action .= escribeEntradaOculta('function','function','SAVE_NOTIFICATION');
			}

			if (!$isPanel) {
				$bufferSalida = '
				<form method="POST" action="index.php" enctype="multipart/form-data" id="formanotificacion" onsubmit="return notificationFrmValidate();">'.
				escribeEntradaOculta('module','module','notificaciones').
				$action.
				escribeEntradaOculta('conversacionid','conversacionid','');
				$module = 'notificaciones';
				if (empty($this->contactid)) {
					$bufferSalida.= escribeEntradaOculta('Funcion','Funcion','RegistrarNotificacion').
					escribeEntradaOculta('module','module','notificaciones');
				} else {
					$bufferSalida.= escribeEntradaOculta('Funcion','Funcion','RegistrarNotificacionCliente').
					escribeEntradaOculta('module','module','notificaciones');
				}
			}
			$bufferSalida.=
			escribeEntradaOculta('ticketid','ticketid',$this->ticketid).
			escribeEntradaOculta('relcrmid','relcrmid',$this->relcrmid);

			$bufferSalida.= '
			<table width="99%" class="small">
				<tr>
					<td class="detailedViewHeader" colspan="2">
						<div style="float:left">
							<b>'.$this->mod_strings['Enviar notificacion'].'</b>
						</div>
						<div style="float:right;padding-right:10px;">
							'.((!$isModal && !$isPanel)? escribeBotonForma('guardar','submit','crmbutton small save',$this->mod_strings['Enviar notificacion']):'').'
						</div>
					</td>
				</tr>';

			if (empty($this->contactid)) {
				$contactList = 'Seleccione la cuenta para ver los contactos disponibles';
				if ($this->accountid) {
					$contactList = $this->EscribeFormaContactos($this->accountid);
				}
				$bufferSalida.= '
				<tr>
					<td width="20%" align="right" class="dvtCellLabel">
						<font color="red">*</font>'.$this->mod_strings['Cuenta'].'
					</td>
					<td class="dvtCellInfo">
						'.$this->SelectCuentas($this->accountid,$isPanel).'
					</td>
				</tr>
				<tr>
					<td width="20%" align="right" class="dvtCellLabel">
						<font color="red">*</font>'.$this->mod_strings['Contactos'].'
					</td>
					<td id="listaContactos" class="dvtCellInfo">
						'.$contactList.'
					</td>
				</tr>';
			} else {
				$realpath = '../';
			}
			$bufferSalida.= '
				<tr>
					<td width="20%" align="right" class="dvtCellLabel">
						<font color="red">*</font>'.$this->mod_strings['Asunto'].'
					</td>
					<td class="dvtCellInfo">
						'.escribeEntradaTexto('subject','subject',$this->subject,'style="width:90%"').'
					</td>
				</tr>';

			if (isset($this->relcrmid) && $this->relcrmid) {
				list($relModule) = $adb->fetch_row($adb->pquery("select setype from vtiger_crmentity where crmid=? and deleted=0", array($this->relcrmid)));
				if ($relModule) {
					$objCrm = CRMEntity::getInstance($relModule);
					$objCrm->retrieve_entity_info($this->relcrmid,$relModule);
					$bufferSalida.= '
					<tr>
						<td width="20%" align="right" class="dvtCellLabel">
							'.getTranslatedString('Registro asociado').'
						</td>
						<td class="dvtCellInfo">
							'.$objCrm->column_fields[$objCrm->list_link_field].'
						</td>
					</tr>';
				}
			}

			$bufferSalida .= '
				<tr>
					<td width="180" align="right" class="dvtCellLabel" rowspan="2">
						'.$this->mod_strings['Documentacion'].'
					</td>
					<td width="320" class="dvtCellInfo" >
						<input type="button" name="'.$this->mod_strings['Agregar Documentacion'].'" value="'.$this->mod_strings['Agregar Documentacion'].'" class="crmbutton small create" onclick="agregarDocumentacionNotificacion(\'listaArchivosDoc\');">
					</td>
				</tr>
				<tr>
				<td>
					<table id="listaArchivosDoc">
					<tr>
						<td>
						</td>
					</tr>
					</table>
				</td>
				</tr>
				<tr>
					<td class="detailedViewHeader" colspan="2">
						<b>'.$this->mod_strings['Mensaje'].'</b>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<textarea name="TextoMensaje" id="TextoMensaje">'.$this->body.'</textarea>
					</td>
				</tr>
			</table>
			<div align="center" id="notificationButtons">
				';
			if ($isModal && !$isPanel) {
				$bufferSalida .= '<p>';
				$bufferSalida .= '<input type="button" name="" value="'.$this->mod_strings['Enviar notificacion'].'" class="crmbutton small edit" style="width:150px;" onclick="submitNotificationAjax()">&nbsp;&nbsp;';
				$bufferSalida .= '<input type="button" name="" value="'.getTranslatedString('Cancelar').'" class="crmbutton small cancel" style="width:150px;" onclick="unloadComposeUI()"><br />';
				$bufferSalida .= '</p>';
			}

			$bufferSalida .= '
			</div>';

			if (!$isPanel) {
				$bufferSalida .= '
				</form>';
			}
			if (!$isModal) {
				$bufferSalida .= '<script type="text/javascript" src="'.$realpath.'include/ckeditor/ckeditor.js"></script>
				<script type="text/javascript" src="modules/notificaciones/notificaciones.js"></script>

				<script type="text/javascript" defer="1">
					var jQuery = jQuery.noConflict();
					var textAreaName = \'TextoMensaje\';
					CKEDITOR.replace( textAreaName,	{
						extraPlugins : \'uicolor\',
						uiColor: \'#dfdff1\',
						height:\'200\', width:\'900\'
					} ) ;
					var oCKeditor = CKEDITOR.instances[textAreaName];
				</script>';
			}

			return $bufferSalida;*/
		}

		function EscribeFormaContactos($accountid = '') {
			$bufferSalida = '';
			if ($accountid == '')
				$accountid = $_REQUEST['accountid'];

			if ($accountid) {
				$sql = "SELECT A.contactid, CONCAT(lastname,', ',firstname) as nombre, email FROM vtiger_contactdetails A
							INNER JOIN vtiger_crmentity B ON (A.contactid = B.crmid AND B.deleted = 0)
							INNER JOIN vtiger_users U ON (A.contactid = U.contactid)
							WHERE accountid = ".$accountid." ORDER BY 2";

				$result = $this->adb->query($sql);

				$bufferSalida = '<table class="small">';
				$i = 0;
				while ($row = $this->adb->fetch_array($result)) {
					$parametros = '';
					if (($i % 4)==0)
						$bufferSalida.= '<tr>';
					if (isset($_REQUEST['contactid']) && $_REQUEST['contactid'] == $row['contactid'])
						$parametros = 'checked="checked"';
					$bufferSalida.= '<td>'.escribeEntradaCheck('contactid[]','contactid[]',$row['contactid'],$parametros).'</td><td>'.$row['nombre'].' ('.$row['email'].') </td>';
					$i++;
					if (($i % 4)==0)
						$bufferSalida.= '</tr>';
				}

				$bufferSalida.= '</table>';
			}

			return $bufferSalida;
		}

		function GuardarDocumentacion($id,$user) {
			global $current_user;
			$modifiedtime = date('Y-m-d H:i:s');
			//Asocio la documentaci�n
			for($i = 0;$i < count($_FILES['file']['name']);$i++) {
				$_FILES['file']['name'][$i] = from_html(preg_replace('/\s+/', '_', $_FILES['file']['name'][$i]));
				$sql3="select id from vtiger_crmentity_seq";
				$idDoc=$this->retornaFila($this->ejecutaConsulta($sql3));
				$idDoc=$idDoc['id'];
				$idDoc++;

				$sql2 = "UPDATE vtiger_crmentity_seq SET id = ".$idDoc;
				$re2=$this->ejecutaConsulta($sql2);

				$sql4="SELECT prefix,cur_id FROM vtiger_modentity_num where semodule='Documents'";
				$result = $this->ejecutaConsulta($sql4);
				$fila = $this->retornaFila($result);

				$sql5="UPDATE vtiger_modentity_num SET cur_id=cur_id+1 where semodule='Documents'";
				$result = $this->ejecutaConsulta($sql5);
				$note_no=$fila['prefix'].($fila['cur_id']+1);

				$sql = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
								VALUES(".$idDoc.",".$user.",".$user.",'Documents','','".$modifiedtime."','".$modifiedtime."')";

				$re2=$this->ejecutaConsulta($sql);

				$sql = "INSERT INTO vtiger_notes (notesid,note_no,title,filename,notecontent,folderid,filetype,filelocationtype,filedownloadcount,filestatus,filesize,fileversion)
								VALUES(".$idDoc.",'".$note_no."','".$_FILES['file']['name'][$i]."','".$_FILES['file']['name'][$i]."','',17,'".$_FILES['file']['type'][$i]."','I',NULL,1,".$_FILES['file']['size'][$i].",'')";
				$re2=$this->ejecutaConsulta($sql);

				$sql_cf="INSERT INTO vtiger_notescf (notesid) values(".$idDoc.")";
				$re=$this->ejecutaConsulta($sql_cf);

				$dbQuery = "INSERT INTO vtiger_senotesrel values ( ".$id.",".$idDoc.")";
				$re=$this->ejecutaConsulta($dbQuery);

				$current_user->id = $user;
				$file = array('name'=>$_FILES['file']['name'][$i],
							  'type'=>$_FILES['file']['type'][$i],
							  'tmp_name'=>$_FILES['file']['tmp_name'][$i],
							  'error'=>$_FILES['file']['error'][$i],
							  'size'=>$_FILES['file']['size'][$i]);
				if (!isset($this->adb))
					chdir('../');
				$this->uploadAndSaveFile($idDoc,'Documents',$file);
				if (!isset($this->adb))
					chdir('./customerPortal2');

			}
		}

		function GuardarNotificacion($eventCode,$accountid,$subject,$textoMensaje,$lstContactos) {
			global $current_user;

			global $PORTAL_URL;

			$enlaceNotificacion = '<a href="'.$PORTAL_URL.'/index.php?module=notificaciones&action=index&login_language='.$this->current_language.'&recordid={$RECORDID}">'.$this->mod_strings['Ver notificacion'].'</a>';

			$relacionada=$enlaceTicket = '<a href="index.php?module=HelpDesk&action=DetailView&record='.$this->ticketid.'" target="_registros">'.$this->obtenerTituloRegistro($this->ticketid).'</a>';

			$arrayVars = array(
				'CUSTOM_CUSTOM1' => $this->obtenerNombreCuenta($accountid),
				'CUSTOM_CUSTOM2' => $enlaceNotificacion,
				'CUSTOM_CUSTOM3' => $subject,
				'CUSTOM_CUSTOM4' => $textoMensaje,
				'CUSTOM_CUSTOM5' => $relacionada,
			);

			//Registro los datos en vtiger_crmentity
			$sql = "SELECT id FROM vtiger_crmentity_seq";
			$id = $this->adb->fetch_array($this->adb->query($sql));
			$id = $id['id'];
			$id++;

			$sql2 = "UPDATE vtiger_crmentity_seq SET id = ".$id;
			$this->adb->query($sql2);

			if (esVistaCliente($_SESSION['authenticated_user_id'])) {//Por el cambio de arquitectura
				$userid = $current_user->getUserByContactId($this->contactid);
			} else {
				$userid = $current_user->id;
			}

			$sql = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
					VALUES(".$id.",".$userid.",".$userid.",'notificaciones','".htmlentities($textoMensaje,ENT_QUOTES)."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."')";

			$this->adb->query($sql);

			if (empty($_REQUEST['conversacionid'])) {
				$sql = "SELECT max(conversacionid) as id FROM vtiger_notificaciones";
				$conversacionid = $this->retornaFila($this->ejecutaConsulta($sql));
				if (!$conversacionid)
					$conversacionid = 1;
				else {
					$conversacionid = $conversacionid['id'];
					$conversacionid++;
				}
			} else {
				$conversacionid = $_REQUEST['conversacionid'];
			}

			$sql_t="INSERT INTO vtiger_notificaciones(notificacionid,accountid,subject,date,status,conversacionid, relcrmid)
					VALUES (".$id.",'".$accountid."',?,'".date('Y-m-d H:i:s')."','Unread',".$conversacionid.", ".(int)$this->relcrmid.")";

			$this->adb->pquery($sql_t, array($subject));

			$sql_cf="INSERT INTO vtiger_notificacionescf (notificacionid,ticketid) VALUES (".$id.",".$this->ticketid.")";
			$this->adb->query($sql_cf);

			$this->GuardarDocumentacion($id,$current_user->id);

			//Se ingresa la relacion de la notificacion con los contactos y se manda el correo
			if (isset($lstContactos)) {
				for($i = 0;$i < count($lstContactos);$i++) {
					$sql_rel = "INSERT INTO vtiger_crmentityrel (crmid,module,relcrmid,relmodule) VALUES (".$id.",'notificaciones',".$lstContactos[$i].",'Contacts')";
					$this->adb->query($sql_rel);

					$contactData = $this->obtenerDatosContacto($lstContactos[$i]);

					if($contactData['idioma']!=''){
						$contactData['idioma'] = $this->getLanguageEmail($contactData['idioma']);
					}

					//$this->enviarNotificacion($eventCode,$contactData['name'],$contactData['email'],$arrayVars);
					$recordid = $conversacionid.'-'.$id;

					$arrayVars['CUSTOM_CUSTOM2'] = str_replace('{$RECORDID}',$recordid,$arrayVars['CUSTOM_CUSTOM2']);
					if (!empty($this->accountid))
						$arrayVars['CUSTOM_CUSTOM6'] = $this->obtenerNombreCuenta($this->accountid);
					else
						$arrayVars['CUSTOM_CUSTOM6'] = $this->obtenerNombreCuenta(ACCOUNTID_EMPRESAFACIL);
					$this->enviarNotificacion($eventCode,'',$contactData['email'],$arrayVars,$contactData['idioma']);
					// $this->enviarNotificacion($eventCode,'','tsanchez@timemanagement.es',$arrayVars,$contactData['idioma']);
				}
			}
		}

		function GuardarNotificacionCliente() {
			global $current_user;
			//Registro los datos en vtiger_crmentity
			$sql = "SELECT id FROM vtiger_crmentity_seq";
			$id = $this->retornaFila($this->ejecutaConsulta($sql));
			$id = $id['id'];
			$id++;

			$sql2 = "UPDATE vtiger_crmentity_seq SET id = ".$id;
			$this->ejecutaConsulta($sql2);

			//-1 Identifica la oepracion como hecha desde el customerportal
			$sql = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES(".$id.",$current_user->id,$current_user->id,'notificaciones','".htmlentities($_REQUEST['TextoMensaje'],ENT_QUOTES)."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."')";

			$this->ejecutaConsulta($sql);

			if (empty($_REQUEST['conversacionid'])) {
				$sql = "SELECT max(conversacionid) as id FROM vtiger_notificaciones";
				$conversacionid = $this->retornaFila($this->ejecutaConsulta($sql));
				if (!$conversacionid)
					$conversacionid = 1;
				else {
					$conversacionid = $conversacionid['id'];
					$conversacionid++;
				}
			} else {
				$conversacionid = $_REQUEST['conversacionid'];
			}
			$sql_t="INSERT INTO vtiger_notificaciones(notificacionid,accountid,subject,date,status,conversacionid,relcrmid)
					VALUES (".$id.",'".$this->accountid."',?,'".date('Y-m-d H:i:s')."','Unread',".$conversacionid.",".(int)$this->relcrmid.")";

			$this->ejecutaConsulta($sql_t, array($_REQUEST['subject']));

			$sql_cf="INSERT INTO vtiger_notificacionescf (notificacionid,ticketid) VALUES (".$id.",".$this->ticketid.")";
			$this->ejecutaConsulta($sql_cf);

			//Se ingresa la relacion de la notificacion con los contactos y se manda el correo
			$sql_rel = "INSERT INTO vtiger_crmentityrel (crmid,module,relcrmid,relmodule) VALUES (".$id.",'notificaciones',".$this->contactid.",'Contacts')";
			$this->ejecutaConsulta($sql_rel);

			$this->GuardarDocumentacion($id,$current_user->id);

			//Enviar Mail de notificacion
			//$enlaceNotificacion = '<a href="https://'.$_SERVER['SERVER_NAME'].'/timemanagement.es/index.php?module=notificaciones&action=index&login_language='.$this->current_language.'&recordid='.$id.'">'.$this->mod_strings['Ver notificacion'].'</a>';
			global $site_URL;
			$site_URL = 'http://time.platzilla.com/';
			$enlaceNotificacion = '<a href="'.$site_URL.'index.php?module=notificaciones&action=index&login_language='.$this->current_language.'&recordid='.$conversacionid.'-'.$id.'">'.$this->mod_strings['Ver notificacion'].'</a>';

			$contactData = $this->obtenerDatosContacto($this->contactid);
			$arrayVars = array(
				'CUSTOM_CUSTOM1' => $contactData['name'],
				'CUSTOM_CUSTOM2' => $this->obtenerNombreCuenta($this->accountid),
				'CUSTOM_CUSTOM3' => $_REQUEST['subject'],
				'CUSTOM_CUSTOM4' => $enlaceNotificacion,
			);

			$idcurrent_languagebak = $this->idcurrent_language;
			$this->idcurrent_language = 585;
			$this->enviarNotificacion('NOTIFICACION_DE_CLIENTE','Jefe de turno',$this->obtenerCorreoJefeTurno(),$arrayVars);
			$this->enviarNotificacion('NOTIFICACION_DE_CLIENTE','Jefe de desarrollo','dpolo@timemanagement.es',$arrayVars);
			$this->enviarNotificacion('NOTIFICACION_DE_CLIENTE','Plataforma','lcastillo@timemanagement.es',$arrayVars);
			$this->idcurrent_language = $idcurrent_languagebak;
		}

		function obtenerCampoOrden() {
			if ($_REQUEST['Orden'] == 'S')
				return "A1.subject";
			elseif ($_REQUEST['Orden'] == 'A')
				return "T1.accountname";
			elseif ($_REQUEST['Orden'] == 'D')
				return "A1.date";
			elseif ($_REQUEST['Orden'] == 'C')
				return "T1.contactname";
			elseif ($_REQUEST['Orden'] == 'U')
				return "T1.user";

			return $this->campoOrderInicial;
		}

		function obtenerOrdenInicial() {
			if (isset($_REQUEST['Orden'])) {
				if ($_SESSION['campoOrden'] == $_REQUEST['Orden']) {
					if ($this->orderInicial == " DESC ")
						$this->orderInicial = " ASC ";
					else
						$this->orderInicial = " DESC ";
				}
				$_SESSION['campoOrden'] = $_REQUEST['Orden'];
				$_SESSION['orderInicial'] = $this->orderInicial;
			}

			return $this->orderInicial;
		}

		function obtenerPaginacion($idLista,$Funcion,$sql) {


			$dirPadre = '';
			$onClickStart = '';
			$onClickPrevio = '';
			$onClickPost = '';
			$onClickLast = '';

			if (!isset($this->adb))
				$dirPadre = '../';

			$sql = explode("ORDER BY",$sql);
			$query = "SELECT COUNT(*) as cantidad FROM (".$sql[0].") as T2";

			/*$row = $this->adb->query_result($this->adb->query($query),0,'cantidad');
			var_dump($this->adb);*/
			$row = $this->retornaFila($this->ejecutaConsulta($query));
			$cantidad = ceil($row['cantidad']/MAX_REGISTROS_PAGINA);
			$bufferSalida = '';

			if ($cantidad > 1) {
				if ($this->regInicial > 1) {
					$onClickStart = 'onclick="actualizarListaPaginas(\''.$idLista.'\',\''.$Funcion.'\',1);"';
					$onClickPrevio = 'onclick="actualizarListaPaginas(\''.$idLista.'\',\''.$Funcion.'\','.($this->regInicial-1).');"';
				}
				if ($this->regInicial < $cantidad) {
					$onClickPost = 'onclick="actualizarListaPaginas(\''.$idLista.'\',\''.$Funcion.'\','.($this->regInicial+1).');"';
					$onClickLast = 'onclick="actualizarListaPaginas(\''.$idLista.'\',\''.$Funcion.'\','.($cantidad).');"';
				}
			}

			$bufferSalida.= '
			<div class="row">
					<div class="filter-block col-md-6 pull-left">
					</div>
					<div class="filter-block col-md-6">
					<ul class="pagination pull-right">
					<li><a href="#" '.$onClickStart.'><i class="fa fa-step-backward"></i></a></li>
					<li><a href="#" '.$onClickPrevio.'><i class="fa fa-chevron-left"></i></a></li>
					<li>
					<span>
					<input type="text" style="border: none;width: 40px;padding: 5px;height:20px;text-align: center;display: initial;font-size:14px;color: #3498db;" onkeypress="return VT_disableFormSubmit(event);" onchange="actualizarListaPaginas(\''.$idLista.'\',\''.$Funcion.'\',this.value);" value="'.$this->regInicial.'" name="pagenum" id="pagenum" class="form-control">
					de '.$cantidad.'</span></li>
					<li><a title="Siguiente" alt="Siguiente" '.$onClickPost.'><i class="fa fa-chevron-right"></i></a></li>
					<li><a title="Última" alt="Última" '.$onClickLast.'><i class="fa fa-step-forward"></i>
					</a>
					</li>
					</ul>
				</div>
			</div>';

			if (0) {
				$bufferSalida.= '
				<table cellspacing="0" cellpadding="0" border="0" class="small" align="center" style="background-color:#FFFFFF">
					<tbody>
						<tr>
							<td align="right" style="padding: 5px;">
							<img border="0" align="absmiddle" src="'.$dirPadre.'themes/images/start_disabled.gif" '.$onClickStart.'>&nbsp;
							<img border="0" align="absmiddle" src="'.$dirPadre.'themes/images/previous_disabled.gif" '.$onClickPrevio.'>&nbsp;
							<input type="text" onkeypress="return actualizarLista(\''.$idLista.'\',this.value);" style="width: 3em;margin-right: 0.7em;background-color:#FFFFFF" value="'.$this->regInicial.'" name="pagenum" class="small">
							<span style="white-space: nowrap; background-color:#FFFFFF" class="small" name="Accounts_listViewCountContainerName">de '.$cantidad.'</span>
							<img border="0" align="absmiddle" src="'.$dirPadre.'themes/images/next_disabled.gif" '.$onClickPost.'>&nbsp;
							<img border="0" align="absmiddle" src="'.$dirPadre.'themes/images/end_disabled.gif" '.$onClickLast.'>&nbsp;
							</td>
						</tr>
					</tbody>
				</table>';
			}

			return $bufferSalida;
		}

		function escribeNotificacionesRecibidas($onlyData = false) {

			global $currentModule, $current_user;

			$this->campoOrderInicial = $this->obtenerCampoOrden();
			$this->orderInicial = $this->obtenerOrdenInicial();
			$where = '';

			if (isset($_REQUEST['filtro']) && !empty($_REQUEST['filtro'])) {
				$where = " AND A1.status = '".$_REQUEST['filtro']."' ";
				$this->campoOrderInicial = "A1.date";
				$this->orderInicial = "DESC";
				$_SESSION['campoOrden'] = 'D';
				$_SESSION['orderInicial'] = $this->orderInicial;
			}

			if ((isset($_REQUEST['filtermodule']) && !empty($_REQUEST['filtermodule'])) ||
					$_REQUEST['regInicial'] > '1' && $_SESSION['filtermodule']) {
				if ($_REQUEST['filtermodule']) {
					$where .= " AND T1.relmodule = '".$_REQUEST['filtermodule']."' ";
					$_SESSION['filtermodule'] = $_REQUEST['filtermodule'];
				} else
					$where .= " AND T1.relmodule = '".$_SESSION['filtermodule']."' ";
			}

			if ((isset($_REQUEST['filterregister']) && !empty($_REQUEST['filterregister'])) ||
					  $_REQUEST['regInicial'] > '1' && $_SESSION['filterregister']) {
				if ($_REQUEST['filterregister']) {
					$where .= " AND A1.relcrmid = '".$_REQUEST['filterregister']."' ";
					$_SESSION['filterregister'] = $_REQUEST['filterregister'];
				}
				else
					$where .= " AND A1.relcrmid = '".$_SESSION['filterregister']."' ";
			}


			//Se incluye el filtro si es vista de cliente
			if (esVistaCliente($_SESSION['authenticated_user_id'])) {
				if ($this->accountid)
					$whereCliente = "WHERE B.accountid = ".$this->accountid;
			} elseif (esVistaPartner($_SESSION['authenticated_user_id'])) {
				$wherePartner = "WHERE B.accountid IN (".partnerViewRestriction().")";
			}

			if (esVistaCliente($_SESSION['authenticated_user_id'])) {
				$sql = "SELECT A1.*, T1.smcreatorid, T1.description, T1.accountname, T1.user, T1.ticketid, T1.relmodule FROM vtiger_notificaciones A1
					INNER JOIN (
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, CONCAT(U.first_name,' ',U.last_name) as user, G.ticketid , H.setype as relmodule
						FROM vtiger_notificaciones A
						INNER JOIN vtiger_crmentity C ON (A.notificacionid = C.crmid AND C.deleted = 0)
						INNER JOIN vtiger_crmentityrel D ON (A.notificacionid = D.crmid AND D.relmodule = 'Contacts')
						INNER JOIN vtiger_contactdetails E ON (D.relcrmid = E.contactid AND D.module = 'notificaciones')
						INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
						INNER JOIN vtiger_crmentity F ON (B.accountid = F.crmid AND F.deleted = 0)
						INNER JOIN vtiger_users U ON (C.smcreatorid = U.id AND U.user_name != '".$current_user->column_fields['user_name']."')
						INNER JOIN vtiger_notificacionescf G ON (A.notificacionid = G.notificacionid)
						LEFT JOIN vtiger_crmentity H on (A.relcrmid=H.crmid and H.deleted=0)
						WHERE E.contactid = ".$this->contactid."
						GROUP BY A.conversacionid) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date)
					 ".$where."
					ORDER BY ".$this->campoOrderInicial." ".$this->orderInicial." LIMIT ".(($this->regInicial-1)*MAX_REGISTROS_PAGINA).", ".MAX_REGISTROS_PAGINA;
			} else {

				$sql = "SELECT A1.*, T1.smcreatorid, T1.description, T1.accountname, T1.ticketid, relmodule FROM vtiger_notificaciones A1 INNER JOIN
					(
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, E.ticketid, F.setype as relmodule
					FROM vtiger_notificaciones A
					INNER JOIN vtiger_crmentity C ON ( A.notificacionid = C.crmid AND C.deleted = 0 AND C.smcreatorid <> ".$_SESSION['authenticated_user_id']." )
					INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
					INNER JOIN vtiger_crmentity D ON ( B.accountid = D.crmid AND D.deleted = 0)
					INNER JOIN vtiger_notificacionescf E ON ( A.notificacionid = E.notificacionid )
					LEFT JOIN vtiger_crmentity F ON ( A.relcrmid = F.crmid and F.deleted=0)
					".$whereCliente."
					".$wherePartner."
					GROUP BY A.conversacionid
					) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date) WHERE 1 ".$where."
					ORDER BY ".$this->campoOrderInicial." ".$this->orderInicial." LIMIT ".(($this->regInicial-1)*MAX_REGISTROS_PAGINA).", ".MAX_REGISTROS_PAGINA;

			}

			$result = $this->adb->query($sql);

			if ($onlyData)
				return $result;

			global $theme, $mod_strings, $app_strings, $app_list_strings, $currentModule;

			require_once('Smarty_setup.php');
			$smarty = new vtigerCRM_Smarty;
			$smarty->assign("MOD", $mod_strings);
			$smarty->assign("APP", $app_strings);

			$smarty->assign("THEME", $theme);
			$smarty->assign("IMAGE_PATH", $image_path);

			$lst = array();

			while($row = $this->adb->fetch_array($result)) {
				if (!empty($row['ticketid'])) {
					if (isset($this->adb)) {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=DetailView&record='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					} else {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					}
				}
				else
					$enlaceTicket = $this->mod_strings['Ninguno'];

				if (!empty($row['relcrmid']) && $row['relmodule']) {
					$objCrm = CRMEntity::getInstance($row['relmodule']);
					$objCrm->retrieve_entity_info($row['relcrmid'],$row['relmodule']);
					$enlaceTicket = '<a href="index.php?module='.$row['relmodule'].'&action=DetailView&record='.$row['relcrmid'].'">'.$objCrm->column_fields[$objCrm->list_link_field].'</a>';
				}

				if ($row['status'] == 'Unread') {
					$style=' style="font-weight:bold;" ';
				} else {
					$style=' style="font-weight:normal;" ';
				}

				if (esVistaCliente($_SESSION['authenticated_user_id'])) {
					// $recibidos = $this->obtenerNombreUsuario($row['smcreatorid']);
					// Pedido David
					$recibidos = 'Empresa-Fácil';
				} else {
					$recibidos = $this->obtenerContactosNotificacion($row['notificacionid']);
				}
				$lst[] = array('subject'=>$row['subject'],
								'accountname'=>$this->obtenerNombreCuenta($row['accountid']),
								'date'=>$row['date'],
								'recibidos'=>$recibidos,
								'enlaceticket'=>$enlaceTicket,
								'botonforma'=>escribeBotonForma('VerDetalle','button','md-trigger btn btn-default mrg-b-lg',$this->mod_strings['Ver detalle'],'data-modal="modal-noti" onclick="cargarDetalleCliente('.$row['conversacionid'].','.$row['notificacionid'].');"'));

				if (0) {
				$bufferSalida.= '
				<tr class="lvtColData"'.$style.' id="rowCli_'.$row['conversacionid'].'">
					<td>'.$row['subject'].'</td>
					<td>'.$this->obtenerNombreCuenta($row['accountid']).'</td>
					<td>'.$row['date'].'</td>
					<td>'.$recibidos.'</td>
					<td>'.$enlaceTicket.'</td>
					<td>'.escribeBotonForma('VerDetalle','button','crmbutton small edit',$this->mod_strings['Ver detalle'],'onclick="cargarDetalleCliente('.$row['conversacionid'].','.$row['notificacionid'].');jQuery(\'#dlgDetalle\').slideDown();"').'</td>
				</tr>
				';
				}
			}

			if (isset($_REQUEST['recordid'])) {//Se debe mostrar un registro al apenas iniciar el panel
				list($varConversacionid,$varNotificacionid) = explode("-",$_REQUEST['recordid']);
				$bufferSalida.= '
			<script>
				function activarRegistro() {
					varConversacionid = \''.$varConversacionid.'\';
					varNotificacionid = \''.$varNotificacionid.'\';

					cargarDetalleCliente(varConversacionid,varNotificacionid);
					jQuery(\'#dlgDetalle\').slideDown();
				}

				if (window.addEventListener)
					window.addEventListener(\'load\',activarRegistro,false);
				else
					window.attachEvent(\'load\',activarRegistro,false);

			</script>
			';
			}

			$smarty->assign("NOTIFICACIONES", $lst);
			$smarty->assign("VISTA_CLIENTE", esVistaCliente($_SESSION['authenticated_user_id']));
			$smarty->assign('PAGINACION',$this->obtenerPaginacion('notificacionesRecibidas','ListNotificacionesRecibidas',$sql));
			$bufferSalida = $smarty->fetch("modules/".$currentModule."/recibidas.tpl");

			return $bufferSalida;
		}

		function escribeNotificacionesEnviadas() {
			global $current_user;

			$this->campoOrderInicial = $this->obtenerCampoOrden();
			$this->orderInicial = $this->obtenerOrdenInicial();
			$where = '';

			if (isset($_REQUEST['filtro']) && !empty($_REQUEST['filtro'])) {
				$where = " AND A1.status = '".$_REQUEST['filtro']."' ";
				$this->campoOrderInicial = "A1.date";
				$this->orderInicial = "DESC";
				$_SESSION['campoOrden'] = 'D';
				$_SESSION['orderInicial'] = $this->orderInicial;
			}

			if ((isset($_REQUEST['filtermodule']) && !empty($_REQUEST['filtermodule'])) ||
					$_REQUEST['regInicial'] > '1' && $_SESSION['filtermodule']) {
				if ($_REQUEST['filtermodule']) {
					$where .= " AND T1.relmodule = '".$_REQUEST['filtermodule']."' ";
					$_SESSION['filtermodule'] = $_REQUEST['filtermodule'];
				} else
					$where .= " AND T1.relmodule = '".$_SESSION['filtermodule']."' ";
			}

			if ((isset($_REQUEST['filterregister']) && !empty($_REQUEST['filterregister'])) ||
					  $_REQUEST['regInicial'] > '1' && $_SESSION['filterregister']) {
				if ($_REQUEST['filterregister']) {
					$where .= " AND A1.relcrmid = '".$_REQUEST['filterregister']."' ";
					$_SESSION['filterregister'] = $_REQUEST['filterregister'];
				}
				else
					$where .= " AND A1.relcrmid = '".$_SESSION['filterregister']."' ";
			}

			//Se incluye el filtro si es vista de cliente
			if (esVistaCliente($_SESSION['authenticated_user_id'])) {
				//$whereCliente = "WHERE B.accountid = ".$this->accountid;
			}

			if (esVistaCliente($_SESSION['authenticated_user_id'])) {
				$sql = "SELECT A1.notificacionid,A1.subject,A1.conversacionid, A1.date, A1.status, A1.accountid,
						T1.smcreatorid, T1.description, T1.accountname, T1.ticketid, T1.relmodule, A1.relcrmid
					FROM vtiger_notificaciones A1
					INNER JOIN (
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, G.ticketid, H.setype as relmodule FROM vtiger_notificaciones A
						INNER JOIN vtiger_crmentity C ON (A.notificacionid = C.crmid AND C.deleted = 0)
						INNER JOIN vtiger_crmentityrel D ON (A.notificacionid = D.crmid AND D.relmodule = 'Contacts')
						INNER JOIN vtiger_contactdetails E ON (D.relcrmid = E.contactid AND D.module = 'notificaciones')
						INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
						INNER JOIN vtiger_crmentity F ON (B.accountid = F.crmid AND F.deleted = 0)
						INNER JOIN vtiger_users U ON (C.smcreatorid = U.id AND U.user_name = '".$current_user->column_fields['user_name']."')
						INNER JOIN vtiger_notificacionescf G ON (A.notificacionid = G.notificacionid)
						LEFT JOIN vtiger_crmentity H on (H.crmid=A.relcrmid and H.deleted=0)
						GROUP BY A.conversacionid) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date)
					 ".$where."
					ORDER BY ".$this->campoOrderInicial." ".$this->orderInicial." LIMIT ".(($this->regInicial-1)*MAX_REGISTROS_PAGINA).", ".MAX_REGISTROS_PAGINA;

			} else {
				$sql = "SELECT A1.*, T1.smcreatorid, T1.description, T1.accountname, T1.user, T1.ticketid, T1.relmodule FROM vtiger_notificaciones A1 INNER JOIN
					(
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, G.ticketid, CONCAT(E.first_name,' ',E.last_name) as user, H.setype as relmodule
					FROM vtiger_notificaciones A
					INNER JOIN vtiger_crmentity C ON ( A.notificacionid = C.crmid AND C.deleted = 0	AND C.smcreatorid = ".$_SESSION['authenticated_user_id']." )
					INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
					INNER JOIN vtiger_crmentity D ON ( B.accountid = D.crmid AND D.deleted = 0)
					INNER JOIN vtiger_users E ON (C.smcreatorid = E.id)
					INNER JOIN vtiger_notificacionescf G ON ( A.notificacionid = G.notificacionid )
					LEFT JOIN vtiger_crmentity H on (A.relcrmid=H.crmid and H.deleted=0)
					".$whereCliente."
					GROUP BY A.conversacionid
					) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date) where 1".$where."
					ORDER BY ".$this->campoOrderInicial." ".$this->orderInicial." LIMIT ".(($this->regInicial-1)*MAX_REGISTROS_PAGINA).", ".MAX_REGISTROS_PAGINA;

			}

			$result = $this->adb->query($sql);

			global $theme, $mod_strings, $app_strings, $app_list_strings, $currentModule;

			require_once('Smarty_setup.php');
			$smarty = new vtigerCRM_Smarty;
			$smarty->assign("MOD", $mod_strings);
			$smarty->assign("APP", $app_strings);

			$smarty->assign("THEME", $theme);
			$smarty->assign("IMAGE_PATH", $image_path);

			$lst = array();

			if (0)
			$bufferSalida = '
				<div width="100%" id="notificacionesEnviadas">
				<table width="100%" cellspacing="1" cellpadding="3" border="0" class="lvt small">
				<tr>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesEnviadas\',\'module=notificaciones&action=ActivityAjax&Funcion=ListNotificacionesEnviadas&Orden=S\')">'.$this->mod_strings['Asunto'].'</a></td>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesEnviadas\',\'module=notificaciones&action=ActivityAjax&Funcion=ListNotificacionesEnviadas&Orden=A\')">'.$this->mod_strings['Cuenta'].'</a></td>
					<td class="lvtCol">'.$this->mod_strings['Contactos'].'</td>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesEnviadas\',\'module=notificaciones&action=ActivityAjax&Funcion=ListNotificacionesEnviadas&Orden=D\')">'.$this->mod_strings['Fecha'].'</a></td>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesEnviadas\',\'module=notificaciones&action=ActivityAjax&Funcion=ListNotificacionesEnviadas&Orden=U\')">'.$this->mod_strings['Enviado por'].'</a></td>
					<td class="lvtCol">'.$this->mod_strings['Registro asociado'].'</td>
					<td class="lvtCol"></td>
				</tr>
			';

			while($row = $this->adb->fetch_array($result)) {
				$style=' style="font-weight:normal;" ';

				if (!empty($row['ticketid']))
					if (isset($this->adb)) {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=DetailView&record='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					} else {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					}
				else
					$enlaceTicket = $this->mod_strings['Ninguno'];

				if (!empty($row['relcrmid']) && $row['relmodule']) {
					$objCrm = CRMEntity::getInstance($row['relmodule']);
					$objCrm->retrieve_entity_info($row['relcrmid'],$row['relmodule']);
					$enlaceTicket = '<a href="index.php?module='.$row['relmodule'].'&action=DetailView&record='.$row['relcrmid'].'">'.$objCrm->column_fields[$objCrm->list_link_field].'</a>';
				}
				if (0)
				$bufferSalida.= '
				<tr class="lvtColData" id="row_'.$row['conversacionid'].'">
					<td>'.$row['subject'].'</td>
					<td>'.$this->obtenerNombreCuenta($row['accountid']).'</td>
					<td>'.$this->obtenerContactosNotificacion($row['notificacionid']).'</td>
					<td>'.$row['date'].'</td>
					<td>'.$this->obtenerNombreUsuario($row['smcreatorid']).'</td>
					<td>'.$enlaceTicket.'</td>
					<td>'.escribeBotonForma('VerDetalle','button','crmbutton small edit',$this->mod_strings['Ver detalle'],'onclick="cargarDetalle('.$row['conversacionid'].','.$row['notificacionid'].');jQuery(\'#dlgDetalle\').slideDown();"').'</td>
				</tr>
				';

				$lst[] = array('subject'=>$row['subject'],
								'accountname'=>$this->obtenerNombreCuenta($row['accountid']),
								'contactlist'=>$this->obtenerContactosNotificacion($row['notificacionid']),
								'date'=>$row['date'],
								'recibidos'=>$this->obtenerNombreUsuario($row['smcreatorid']),
								'enlaceticket'=>$enlaceTicket,
								'botonforma'=>escribeBotonForma('VerDetalle','button','md-trigger btn btn-default mrg-b-lg',$this->mod_strings['Ver detalle'],'data-modal="modal-noti" onclick="cargarDetalleCliente('.$row['conversacionid'].','.$row['notificacionid'].');"'));
			}

			if (0) {
				$bufferSalida.= '</table>
				<div style="bottom:0px">
				'.$this->obtenerPaginacion('notificacionesEnviadas','ListNotificacionesEnviadas',$sql).'
				</div>
				</div>';
			}

			$smarty->assign("NOTIFICACIONES", $lst);
			$smarty->assign('PAGINACION',$this->obtenerPaginacion('notificacionesEnviadas','ListNotificacionesEnviadas',$sql));
			$bufferSalida = $smarty->fetch("modules/".$currentModule."/enviadas.tpl");

			return $bufferSalida;
		}

		function escribeNotificacionesRecibidasCliente() {
			global $current_user;
			$this->campoOrderInicial = $this->obtenerCampoOrden();
			$this->orderInicial = $this->obtenerOrdenInicial();
			$where = '';

			if (isset($_REQUEST['filtro']) && !empty($_REQUEST['filtro'])) {
				$where = " WHERE A1.status = '".$_REQUEST['filtro']."' ";
				$this->campoOrderInicial = "A1.date";
				$this->orderInicial = "DESC";
				$_SESSION['campoOrden'] = 'D';
				$_SESSION['orderInicial'] = $this->orderInicial;
			}

			$sql = "SELECT A1.*, T1.smcreatorid, T1.description, T1.accountname, T1.user, T1.ticketid, T1.relmodule FROM vtiger_notificaciones A1
					INNER JOIN (
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, CONCAT(U.first_name,' ',U.last_name) as user, G.ticketid , H.setype as relmodule
						FROM vtiger_notificaciones A
						INNER JOIN vtiger_crmentity C ON (A.notificacionid = C.crmid AND C.deleted = 0 AND C.smcreatorid != ".$current_user->id.")
						INNER JOIN vtiger_crmentityrel D ON (A.notificacionid = D.crmid AND D.relmodule = 'Contacts')
						INNER JOIN vtiger_contactdetails E ON (D.relcrmid = E.contactid AND D.module = 'notificaciones')
						INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
						INNER JOIN vtiger_crmentity F ON (B.accountid = F.crmid AND F.deleted = 0)
						INNER JOIN vtiger_users U ON (C.smcreatorid = U.id)
						INNER JOIN vtiger_notificacionescf G ON (A.notificacionid = G.notificacionid)
						LEFT JOIN vtiger_crmentity H on (A.relcrmid=H.crmid and H.deleted=0)
						WHERE E.contactid = ".$this->contactid."
						GROUP BY A.conversacionid) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date)
					 ".$where."
					ORDER BY ".$this->campoOrderInicial." ".$this->orderInicial." LIMIT ".(($this->regInicial-1)*MAX_REGISTROS_PAGINA).", ".MAX_REGISTROS_PAGINA;



			$result = $this->ejecutaConsulta($sql);

			$bufferSalida = '
				<div width="100%" id="notificacionesRecibidas">
				<table width="100%" cellspacing="1" cellpadding="3" border="0" class="lvt small">
				<tr>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesRecibidas\',\'module=notificaciones&action=ActivityAjax&ajax=true&Funcion=ListNotificacionesRecibidasCliente&Orden=S\')">'.$this->mod_strings['Asunto'].'</a></td>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesRecibidas\',\'module=notificaciones&action=ActivityAjax&ajax=true&Funcion=ListNotificacionesRecibidasCliente&Orden=D\')">'.$this->mod_strings['Fecha'].'</a></td>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesRecibidas\',\'module=notificaciones&action=ActivityAjax&ajax=true&Funcion=ListNotificacionesRecibidasCliente&Orden=U\')">'.$this->mod_strings['Enviado por'].'</a></td>
					<td class="lvtCol">'.$this->mod_strings['Registro asociado'].'</td>
					<td class="lvtCol"></td>
				</tr>
			';

			while($row = $this->retornaFila($result)) {

				if (!empty($row['ticketid']))
					if (isset($this->adb)) {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=DetailView&record='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					} else {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					}
				else
					$enlaceTicket = $this->mod_strings['Ninguno'];

				if ($row['status'] == 'Unread') {
					$style=' style="font-weight:bold;" ';
				} else {
					$style=' style="font-weight:normal;" ';
				}

				if (!empty($row['relcrmid']) && $row['relmodule']) {
					$objCrm = CRMEntity::getInstance($row['relmodule']);
					$objCrm->retrieve_entity_info($row['relcrmid'],$row['relmodule']);
					$enlaceTicket = '<a href="index.php?module='.$row['relmodule'].'&action=DetailView&record='.$row['relcrmid'].'">'.$objCrm->column_fields[$objCrm->list_link_field].'</a>';
				}

				$bufferSalida.= '
				<tr class="lvtColData"'.$style.' id="row_'.$row['conversacionid'].'">
					<td>'.utf8_encode($row['subject']).'</td>
					<td>'.$row['date'].'</td>
					<td>'.$this->obtenerNombreUsuario($row['smcreatorid']).'</td>
					<td>'.$enlaceTicket.'</td>
					<td>'.escribeBotonForma('VerDetalle','button','crmbutton small edit',$this->mod_strings['Ver detalle'],'onclick="cargarDetalle('.$row['conversacionid'].','.$row['notificacionid'].');jQuery(\'#dlgDetalle\').slideDown();"').'</td>
				</tr>
				';
			}

			$bufferSalida.= '</table>
			<div style="bottom:0px">
			'.$this->obtenerPaginacion('notificacionesRecibidas','ListNotificacionesRecibidasCliente',$sql).'
			</div>
			</div>';

			if (isset($_REQUEST['recordid'])) {//Se debe mostrar un registro al apenas iniciar el panel
				list($varConversacionid,$varNotificacionid) = explode("-",$_REQUEST['recordid']);
				$bufferSalida.= '
			<script>
				function activarRegistro() {
					varConversacionid = \''.$varConversacionid.'\';
					varNotificacionid = \''.$varNotificacionid.'\';

					cargarDetalleCliente(varConversacionid,varNotificacionid);
					jQuery(\'#dlgDetalle\').slideDown();
				}

				if (window.addEventListener)
					window.addEventListener(\'load\',activarRegistro,false);
				else
					window.attachEvent(\'load\',activarRegistro,false);

			</script>
			';
			}

			return $bufferSalida;
		}

		function escribeNotificacionesEnviadasCliente() {
			global $current_user;
			$this->campoOrderInicial = $this->obtenerCampoOrden();
			$this->orderInicial = $this->obtenerOrdenInicial();
			$where = '';

			if (isset($_REQUEST['filtro']) && !empty($_REQUEST['filtro'])) {
				$where = " WHERE A1.status = '".$_REQUEST['filtro']."' ";
				$this->campoOrderInicial = "A1.date";
				$this->orderInicial = "DESC";
				$_SESSION['campoOrden'] = 'D';
				$_SESSION['orderInicial'] = $this->orderInicial;
			}

			$sql = "SELECT A1.notificacionid,A1.subject,A1.conversacionid, A1.date, A1.status, T1.smcreatorid, T1.description, T1.accountname, T1.ticketid, T1.relmodule, A1.relcrmid
					FROM vtiger_notificaciones A1
					INNER JOIN (
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, G.ticketid, H.setype as relmodule FROM vtiger_notificaciones A
						INNER JOIN vtiger_crmentity C ON (A.notificacionid = C.crmid AND C.deleted = 0 AND C.smcreatorid = ".$current_user->id.")
						INNER JOIN vtiger_crmentityrel D ON (A.notificacionid = D.crmid AND D.relmodule = 'Contacts')
						INNER JOIN vtiger_contactdetails E ON (D.relcrmid = E.contactid AND D.module = 'notificaciones')
						INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
						INNER JOIN vtiger_crmentity F ON (B.accountid = F.crmid AND F.deleted = 0)
						INNER JOIN vtiger_notificacionescf G ON (A.notificacionid = G.notificacionid)
						LEFT JOIN vtiger_crmentity H on (H.crmid=A.relcrmid and H.deleted=0)
						WHERE E.contactid = ".$this->contactid."
						GROUP BY A.conversacionid) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date)
					 ".$where."
					ORDER BY ".$this->campoOrderInicial." ".$this->orderInicial." LIMIT ".(($this->regInicial-1)*MAX_REGISTROS_PAGINA).", ".MAX_REGISTROS_PAGINA;


			$result = $this->ejecutaConsulta($sql,$this->gdb);

			$bufferSalida = '
				<div width="100%" id="notificacionesEnviadas">
				<table width="100%" cellspacing="1" cellpadding="3" border="0" class="lvt small">
				<tr>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesEnviadas\',\'module=notificaciones&action=ActivityAjax&ajax=true&Funcion=ListNotificacionesEnviadasCliente&Orden=S\')">'.$this->mod_strings['Asunto'].'</a></td>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesEnviadas\',\'module=notificaciones&action=ActivityAjax&ajax=true&Funcion=ListNotificacionesEnviadasCliente&Orden=D\')">'.$this->mod_strings['Fecha'].'</a></td>
					<td class="lvtCol">'.$this->mod_strings['Registro asociado'].'</td>
					<td class="lvtCol"></td>
				</tr>
			';

			while($row = $this->retornaFila($result)) {
				$style=' style="font-weight:normal;" ';

				if (!empty($row['ticketid']))
					if (isset($this->adb)) {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=DetailView&record='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					} else {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					}
				else
					$enlaceTicket = $this->mod_strings['Ninguno'];

				if (!empty($row['relcrmid']) && $row['relmodule']) {
					$objCrm = CRMEntity::getInstance($row['relmodule']);
					$objCrm->retrieve_entity_info($row['relcrmid'],$row['relmodule']);
					$enlaceTicket = '<a href="index.php?module='.$row['relmodule'].'&action=DetailView&record='.$row['relcrmid'].'">'.$objCrm->column_fields[$objCrm->list_link_field].'</a>';
				}
				$bufferSalida.= '
				<tr class="lvtColData"'.$style.' id="rowCli_'.$row['conversacionid'].'">
					<td>'.($row['subject']).'</td>
					<td>'.$row['date'].'</td>
					<td>'.$enlaceTicket.'</td>
					<td>'.escribeBotonForma('VerDetalle','button','crmbutton small edit',$this->mod_strings['Ver detalle'],'onclick="cargarDetalleCliente('.$row['conversacionid'].','.$row['notificacionid'].');jQuery(\'#dlgDetalle\').slideDown();"').'</td>
				</tr>
				';
			}

			$bufferSalida.= '</table>
			<div style="bottom:0px">
			'.$this->obtenerPaginacion('notificacionesEnviadas','ListNotificacionesEnviadasCliente',$sql).'
			</div>
			</div>';

			return $bufferSalida;
		}

		function obtenerNombreUsuario($id) {
			$sql = "SELECT a.id, CONCAT(a.last_name,', ',a.first_name) as nombres FROM vtiger_users a
						WHERE a.id = ".$id;

			if ($this->adb) {
				$result = $this->adb->query($sql);

				if ($row = $this->adb->fetch_array($result)) {
					return $row['nombres'];
				}
			} else {
				$result = mysql_query($sql,$this->gdb);

				if ($row = mysql_fetch_array($result)) {
					return $row['nombres'];
				}
			}
			return;
		}

		function obtenerTituloRegistro($ticketid) {
			$sql = "SELECT title FROM vtiger_troubletickets WHERE ticketid = ".$ticketid;

			$result = $this->ejecutaConsulta($sql);

			if ($row = $this->retornaFila($result)) {
				return $row['title'];
			}
		}

		function mostrarDocumentacion($id,$enlace = '',$dirPadre = '') {
			global $conex;

			$sql = "SELECT D.* FROM vtiger_attachments D INNER JOIN vtiger_seattachmentsrel C
						ON (D.attachmentsid = C.attachmentsid)
						INNER JOIN vtiger_notes A
						ON (C.crmid = A.notesid)
						INNER JOIN vtiger_senotesrel B
						ON (A.notesid = B.notesid)
						WHERE B.crmid = ".$id;

			//$result = mysql_query($sql,$conex);
			$result = $this->ejecutaConsulta($sql);

			$bufferSalida = '
			<script>
				/*function mostrarOcultarDocumentacion() {
					ctrl = document.getElementById(\'listaArchivos\');

					if (ctrl) {
						if (ctrl.style.display == \'table\') {
							ctrl.style.display = \'none\';
						} else {
							ctrl.style.display = \'table\';
						}
					}
				}*/
			</script>

			';

			if (!empty($enlace))
				$bufferSalida.= $enlace;
			else
				$bufferSalida.= '<input  type="button" name="documentos" id="documentos" class="btn btn-primary"  value="Documentaci&oacute;n" onclick="jQuery(\'#dlgDetalle'.$id.'\').slideDown();">';

			$bufferSalida.= '
			<div id="dlgDetalle'.$id.'" style="display:none; padding:10px; background-color:#FFFFFF;border:1px solid;border-color:blue;width:310px; position:fixed; left:100px; top:150px; z-index:2; -moz-border-radius: 15px;
				border-radius: 15px; -moz-box-shadow: 5px 5px 2px #888; -webkit-box-shadow: 5px 5px 2px #888; box-shadow: 5px 5px 2px #888; max-height:450px; overflow:auto;">
				<div style="float:left;cursor:pointer;">Documentaci&oacute;n Adjunta</div>
				<div style="float:right;cursor:pointer;"  onclick="jQuery(\'#dlgDetalle'.$id.'\').slideUp();">[x]</div>
				<table class="small" width="100%">';
				if ($this->cantidadRegistros($result) == 0) {
					$bufferSalida.= '<tr><td>El registro no posee documentaci&oacute;n asociada
									</td></tr>';
				}
				//while($row = mysql_fetch_assoc($result)) {
				//<a href="'.$dirPadre.'index.php?module=uploads&action=downloadfile&entityid='.$id.'&fileid='.$row['attachmentsid'].'" title="Descargar fichero">'.$row['name'].'</a>

				while($row = $this->retornaFila($result)) {
					global $site_URL;
					$bufferSalida.= '<tr><td>
										<a href="'.$row['path'].$row['attachmentsid'].'_'.$row['name'].'" title="Descargar fichero">'.$row['name'].'</a>
									</td></tr>';
				}
				$bufferSalida.= '</table>
			</div>';

			return $bufferSalida;
		}

		function escribeDetalleNotificacionCliente($conversacionid,$notificacionid) {
			global $currentModule,$mod_strings,$app_strings;

			$sql = "SELECT A.*, B.accountid, B.accountname, C.smcreatorid, C.description, C.createdtime, D.ticketid FROM
						vtiger_notificaciones A INNER JOIN vtiger_crmentity C ON (A.notificacionid = C.crmid AND C.deleted = 0)
						INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
						INNER JOIN vtiger_notificacionescf D ON (A.notificacionid = D.notificacionid)
						WHERE A.conversacionid = ".$conversacionid." ORDER BY A.date DESC";

			$result = $this->ejecutaConsulta($sql);
			$bufferSalida = '';
			$i = 0;
			while ($row = $this->retornaFila($result)) {
				$contactid = '';
				if ($row['smcreatorid'] == -1)
					$contactid = $this->obtenerContactosNotificacion($row['notificacionid'],true);
				if ($i == 0) {
					$responderNotificacion = '
						<div style="float:right">
							'.escribeBotonForma('guardar','submit','crmbutton small save',$this->mod_strings['Responder Notificacion'],'onclick="asignarDatosRespuesta(\''.$row['subject'].'\','.$row['conversacionid'].','.$row['accountid'].','.$row['ticketid'].',\''.$contactid.'\');jQuery(\'#dlgNuevaNotificacion\').slideDown();"').'
						</div>';
				}
				if ($i % 2)
					$color = '#d3d9dd';
				else
					$color = '#ffffff';
				$stylePadding = ' style="padding-left:'.($i*10).'px;" ';
				$styleBk = 'style="background-color:'.$color.'"';
				$i++;
				if ($row['smcreatorid'] != -1) {
					$styleHeader = "detailedViewHeader";
				} else {
					$styleHeader = "lvtCol";
				}

				if ($this->adb) {
					if ($row['smcreatorid'] == -1)
						$sendby = $this->obtenerContactosNotificacion($row['notificacionid']);

				} elseif ($row['smcreatorid'] != -1) {
					$sendby = $this->obtenerNombreUsuario($row['smcreatorid']);
				}

				if ($row['ticketid'] != '') {
					if (isset($this->adb)) {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=DetailView&record='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					} else {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					}
				}

				$responderNotificacion = '';

				require_once('Smarty_setup.php');
				$smarty = new vtigerCRM_Smarty;
				$smarty->assign("MOD", $mod_strings);
				$smarty->assign("APP", $app_strings);
				$smarty->assign("ACCOUNTNAME", $row['accountname']);
				$smarty->assign("ACCOUNTID", $row['accountid']);
				$smarty->assign("TICKETID", $row['ticketid']);
				$smarty->assign("CONVERSATIONID", $conversacionid);
				$smarty->assign("NOTIFICACIONID", $notificacionid);
				$smarty->assign("DATETIME", $row['createdtime']);
				$smarty->assign("SENDBY", $this->obtenerNombreUsuario($row['smcreatorid']));
				$smarty->assign("SUBJECT", $row['subject']);
				$smarty->assign("ASSOCIATED_RECORD", $enlaceTicket);
				$smarty->assign("DOCUMENTATION", $this->mostrarDocumentacion($row['notificacionid']));
				$smarty->assign("MESSAGE", html_entity_decode(html_entity_decode($row['description'])));
				$smarty->assign("STYLE", $stylePadding);
				$smarty->assign("STYLEBK", $styleBk);

				$bufferSalida.= $smarty->fetch("modules/".$currentModule."/detalleNotificacion.tpl");
			}

			if (isset($this->adb)) {
				$sqlUpdate = "UPDATE vtiger_notificaciones SET status = 'Read' WHERE notificacionid = ".$notificacionid;
				$this->ejecutaConsulta($sqlUpdate);
			}

			if (!isset($this->adb))
				$bufferSalida = utf8_encode($bufferSalida);
			return $bufferSalida;
		}

		function escribeDetalleNotificacion($conversacionid,$notificacionid) {
			$bufferSalida = '';
			$sql = "SELECT A.*, B.accountid, B.accountname, C.smcreatorid, C.description, C.createdtime, D.ticketid, E.setype as relmodule FROM vtiger_notificaciones A
						INNER JOIN vtiger_crmentity C ON (A.notificacionid = C.crmid AND C.deleted = 0)
						INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
						INNER JOIN vtiger_notificacionescf D ON (A.notificacionid = D.notificacionid)
						LEFT JOIN vtiger_crmentity E ON ( A.relcrmid = E.crmid and E.deleted=0)
						WHERE A.conversacionid = ".$conversacionid." ORDER BY A.date DESC";

			$result = $this->ejecutaConsulta($sql);
			$bufferSalida = '';
			$i = 0;
			while ($row = $this->retornaFila($result)) {
				$contactid = '';
				if ($row['smcreatorid'] == -1)
					$contactid = $this->obtenerContactosNotificacion($row['notificacionid'],true);

				if ($i == 0) {
					$responderNotificacion = '
						<div style="float:right">
							'.escribeBotonForma('guardar','submit','crmbutton small save',$this->mod_strings['Responder Notificacion'],'onclick="asignarDatosRespuesta(\''.$row['subject'].'\','.$row['conversacionid'].','.$row['accountid'].','.$row['ticketid'].',\''.$contactid.'\');jQuery(\'#dlgNuevaNotificacion\').slideDown();"').'
						</div>';
				}
				$i++;
				$stylePadding = ' style="padding-left:'.($i*20).'px" ';
				if ($row['smcreatorid'] != -1) {
					$styleHeader = "detailedViewHeader";
				} else {
					$styleHeader = "lvtCol";
				}
				$bufferSalida.= '
				<a name="noti'.$row['notificacionid'].'"></a>
				<div'.$stylePadding.'>
				<table width="95%">
				<tr>
					<td class="'.$styleHeader.'" colspan="2">
						<div style="float:left">
							<b>'.$this->mod_strings['Detalle Notificacion'].'</b>
						</div>
						'.$responderNotificacion.'
					</td>
				</tr>
				<tr>
					<td width="20%" align="right" class="dvtCellLabel">
						'.$this->mod_strings['Cuenta'].'
					</td>
					<td class="dvtCellLabel">
						'.$row['accountname'].'
					</td>
				</tr>
				<tr>
					<td width="20%" align="right" class="dvtCellLabel">
						'.$this->mod_strings['Fecha Notificacion'].'
					</td>
					<td class="dvtCellLabel">
						'.$row['createdtime'].'
					</td>
				</tr>';

				if ($this->adb) {
					if ($row['smcreatorid'] == -1) {

						$bufferSalida.= '
					<tr>
						<td width="20%" align="right" class="dvtCellLabel">
							<font color="red">*</font>'.$this->mod_strings['Enviado por'].'
						</td>
						<td class="dvtCellLabel">
							'.$this->obtenerNombreUsuario($row['smcreatorid']).'
						</td>
					</tr>';
					}
				} else {
					$bufferSalida.= '
					<tr>
						<td width="20%" align="right" class="dvtCellLabel">
							'.$this->mod_strings['Enviado por'].'
						</td>
						<td class="dvtCellLabel">
							'.$this->obtenerContactosNotificacion($row['notificacionid']).'
						</td>
					</tr>';
				}

				$bufferSalida.= '
				<tr>
					<td width="20%" align="right" class="dvtCellLabel">
						'.$this->mod_strings['Asunto'].'
					</td>
					<td class="dvtCellLabel">
						'.$row['subject'].'
					</td>
				</tr>';

				if ($row['ticketid'] != '') {
					if (isset($this->adb)) {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=DetailView&record='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					} else {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					}
				} else
					$enlaceTicket = $this->mod_strings['Ninguno'];

				if (!empty($row['relcrmid']) && $row['relmodule']) {
					$objCrm = CRMEntity::getInstance($row['relmodule']);
					$objCrm->retrieve_entity_info($row['relcrmid'],$row['relmodule']);
					$enlaceTicket = '<a href="index.php?module='.$row['relmodule'].'&action=DetailView&record='.$row['relcrmid'].'">'.$objCrm->column_fields[$objCrm->list_link_field].'</a>';
				}

				$bufferSalida.= '
			<tr>
				<td width="20%" align="right" class="dvtCellLabel">
					'.$this->mod_strings['Registro asociado'].'
				</td>
				<td class="dvtCellLabel">
					'.$enlaceTicket.'
				</td>
			</tr>';

				$bufferSalida.= '
				<tr>
					<td width="20%" align="right" class="dvtCellLabel">
						'.$this->mod_strings['Documentacion'].'
					</td>
					<td class="dvtCellLabel">
						'.$this->mostrarDocumentacion($row['notificacionid']).'
					</td>
				</tr>
				<tr>
					<td class="'.$styleHeader.'" colspan="2">
						<b>'.$this->mod_strings['Mensaje'].'</b>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="border: solid 1px #DDDDDD; padding:5px;" valign="top">
						<div style="min-height:200px;">
						'.html_entity_decode(html_entity_decode($row['description'])).'</textarea>
						</div>
					</td>
				</tr>
			</table>
			</div>';
				$responderNotificacion = '';
			}

			if (!isset($this->adb)) {
				$sqlUpdate = "UPDATE vtiger_notificaciones SET status = 'Read' WHERE notificacionid = ".$notificacionid;
				$this->ejecutaConsulta($sqlUpdate);
			}

			if (!isset($this->adb))
				$bufferSalida = utf8_encode($bufferSalida);
			return $bufferSalida;
		}

		function escribePanelNotificaciones() {
			global $theme, $mod_strings, $app_strings, $app_list_strings, $currentModule;

			require_once('Smarty_setup.php');
			$smarty = new vtigerCRM_Smarty;
			$smarty->assign("MOD", $mod_strings);
			$smarty->assign("APP", $app_strings);
			$smarty->assign("THEME", $theme);
			$smarty->assign("IMAGE_PATH", $image_path);
			$smarty->assign("NOTIFICACIONES_RECIBIDAS", $this->escribeNotificacionesRecibidas());
			$smarty->assign("NOTIFICACIONES_ENVIADAS", $this->escribeNotificacionesEnviadas());
			$smarty->assign("OPCIONES_CORREO", $this->escribeOpcionesCorreo());
			$smarty->assign("MODULESNAME", $this->getFilterModules());
			$smarty->assign("RECORDS", $this->getFilterRecords('send'));

			$bufferSalida = $smarty->fetch("modules/".$currentModule."/mainpanel.tpl");

			return $bufferSalida;
		}

		function escribeSoloFormaEnviarNotificacion() {
			$bufferSalida = '
			<script>
				var jQuery = jQuery.noConflict();
			</script>
			<div id="dlgNuevaNotificacion" style="display:none; padding:10px; background-color:#FFFFFF;border:3px;border-color:yellow;width:810px; position:fixed; left:100px; top:100px; z-index:89999; -moz-border-radius: 15px;
				border-radius: 15px; -moz-box-shadow: 5px 5px 2px #888; -webkit-box-shadow: 5px 5px 2px #888; box-shadow: 5px 5px 2px #888; max-height:450px; overflow:auto;">
				<div style="float:right;cursor:pointer;"  onclick="jQuery(\'#dlgNuevaNotificacion\').slideUp(); resetNotificationForm();">[x]</div>'.
				$this->EscribeFormaEnviarNotificacion().'
			</div>';

			return $bufferSalida;
		}

		function escribePanelNotificacionesCliente() {
			$bufferSalida = '
			<table width="100%">
				<tr>
					<td class="detailedViewHeader" colspan="2">
						<div style="float:left">
							<b>'.$this->mod_strings['Notificaciones recibidas'].'</b>
						</div>
						<div style="float:right">
							'.$this->escribeComboFiltro('notificacionesRecibidas','ListNotificacionesRecibidasCliente','received').'
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div style="overflow:auto;min-height:200px;max-height:300px;">
						'.$this->escribeNotificacionesRecibidasCliente().'
						</div>
					</td>
				</tr>
				<tr>
					<td class="detailedViewHeader" colspan="2">
						<div style="float:left">
							<b>'.$this->mod_strings['Notificaciones enviadas'].'</b>
						</div>
						<div style="float:right">
							'.escribeBotonForma('guardar','submit','crmbutton small save',$this->mod_strings['Enviar notificacion'],'onclick="jQuery(\'#dlgNuevaNotificacion\').slideDown();"').'
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div style="overflow:auto;min-height:200px;max-height:300px;">
						'.$this->escribeNotificacionesEnviadasCliente().'
						</div>
					</td>
				</tr>
				<tr>
					<td class="detailedViewHeader" colspan="2">
						<div style="float:left">
							<b>'.$this->mod_strings['Envío de Correos'].'</b>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div style="overflow:auto;min-height:200px;max-height:300px;">
						'.$this->escribeNotificacionesEnviadasCliente().'
						</div>
					</td>
				</tr>
			</table>
			<div id="dlgNuevaNotificacion" style="display:none; padding:10px; background-color:#FFFFFF;border:3px;border-color:yellow;width:810px; position:fixed; left:100px; top:100px; z-index:89999; -moz-border-radius: 15px;
				border-radius: 15px; -moz-box-shadow: 5px 5px 2px #888; -webkit-box-shadow: 5px 5px 2px #888; box-shadow: 5px 5px 2px #888; max-height:450px; overflow:auto;">
				<div style="float:right;cursor:pointer;"  onclick="jQuery(\'#dlgNuevaNotificacion\').slideUp(); resetNotificationForm();">[x]</div>
				'.
				$this->EscribeFormaEnviarNotificacion().'
			</div>
			<div id="dlgDetalle" style="display:none; padding:10px; background-color:#FFFFFF;border:3px;border-color:yellow;width:910px; position:fixed; left:100px; top:100px; z-index:2; -moz-border-radius: 15px;
				border-radius: 15px; -moz-box-shadow: 5px 5px 2px #888; -webkit-box-shadow: 5px 5px 2px #888; box-shadow: 5px 5px 2px #888; max-height:450px; overflow:auto;">
				<div style="float:left;cursor:pointer;">'.$this->modstrings['Detalle Notificacion'].'</div>
				<div style="float:right;cursor:pointer;"  onclick="jQuery(\'#dlgDetalle\').slideUp();">[x]</div>
				<div id="detalleNotificacion"></div>
			</div>';

			return $bufferSalida;
		}

		function enviarNotificacion() {
			return null;
		}

		function escribirNotificacionesAsociadasRegistro() {
			$bufferSalida = '
				<div width="100%" id="notificacionesRelacionadas">
				<span class="lvtHeaderText">Notificaciones relacionadas</span>';

			$where = " WHERE T1.ticketid = '".$this->ticketid."' ";

			$sql = "SELECT A1.*, T1.smcreatorid, T1.description, T1.accountname, T1.user, T1.ticketid FROM vtiger_notificaciones A1 INNER JOIN
					(
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, G.ticketid, CONCAT(E.first_name,' ',E.last_name) as user
					FROM vtiger_notificaciones A
					INNER JOIN vtiger_crmentity C ON ( A.notificacionid = C.crmid AND C.deleted = 0 )
					INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
					INNER JOIN vtiger_crmentity D ON ( B.accountid = D.crmid AND D.deleted = 0)
					INNER JOIN vtiger_notificacionescf G ON ( A.notificacionid = G.notificacionid )
					LEFT JOIN vtiger_users E ON (C.smcreatorid = E.id)
					GROUP BY A.conversacionid
					) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date)".$where."
					ORDER BY ".$this->campoOrderInicial." ".$this->orderInicial." LIMIT ".(($this->regInicial-1)*MAX_REGISTROS_PAGINA).", ".MAX_REGISTROS_PAGINA;

			$result = $this->ejecutaConsulta($sql);

			if ($this->cantidadRegistros($result) == 0)
				return $bufferSalida;

			$bufferSalida.= '
				<table width="100%" cellspacing="1" cellpadding="3" border="0" class="lvt small">
				<tr>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesRecibidas\',\'module=notificaciones&action=ActivityAjax&Funcion=ListNotificacionesRecibidas&Orden=S\')">'.$this->mod_strings['Asunto'].'</a></td>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesRecibidas\',\'module=notificaciones&action=ActivityAjax&Funcion=ListNotificacionesRecibidas&Orden=A\')">'.$this->mod_strings['Cuenta'].'</a></td>
					<td class="lvtCol"><a href="#" onclick="actualizarLista(\'notificacionesRecibidas\',\'module=notificaciones&action=ActivityAjax&Funcion=ListNotificacionesRecibidas&Orden=D\')">'.$this->mod_strings['Fecha'].'</a></td>
					<td class="lvtCol">'.$this->mod_strings['Enviado por'].'</td>
					<td class="lvtCol"></td>
				</tr>
			';



			while($row = $this->retornaFila($result)) {
				$style=' style="font-weight:normal;" ';

				if (!empty($row['ticketid']))
					if (isset($this->adb)) {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=DetailView&record='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					} else {
						$enlaceTicket = '<a href="index.php?module=HelpDesk&action=index&fun=detail&ticketid='.$row['ticketid'].'" target="_registros">'.$this->obtenerTituloRegistro($row['ticketid']).'</a>';
					}
				else
					$enlaceTicket = $this->mod_strings['Ninguno'];

				if ($row['smcreatorid'] != -1)
					$enviadoPor = $this->obtenerNombreUsuario($row['smcreatorid']);
				else
					$enviadoPor = $this->obtenerContactosNotificacion($row['notificacionid']);

				$bufferSalida.= '
				<tr class="lvtColData" id="row_'.$row['conversacionid'].'">
					<td>'.$row['subject'].'</td>
					<td>'.$this->obtenerNombreCuenta($row['accountid']).'</td>
					<td>'.$row['date'].'</td>
					<td>'.$enviadoPor.'</td>
					<td>'.escribeBotonForma('VerDetalle','button','crmbutton small edit',$this->mod_strings['Ver detalle'],'onclick="cargarDetalle('.$row['conversacionid'].','.$row['notificacionid'].');jQuery(\'#dlgDetalle\').slideDown();"').'</td>
				</tr>
				';
			}

			$bufferSalida.= '</table>
			<div style="bottom:0px">
			'.$this->obtenerPaginacion('notificacionesRelacionadas','NotificacionesAsociadasRegistro',$sql).'
			</div>
			</div>

			<div id="dlgDetalle" style="display:none; padding:10px; background-color:#FFFFFF;border:3px;border-color:yellow;width:910px; position:fixed; left:100px; top:100px; z-index:2; -moz-border-radius: 15px;
				border-radius: 15px; -moz-box-shadow: 5px 5px 2px #888; -webkit-box-shadow: 5px 5px 2px #888; box-shadow: 5px 5px 2px #888; max-height:450px; overflow:auto;">
				<div style="float:left;cursor:pointer;">'.$this->modstrings['Detalle Notificacion'].'</div>
				<div style="float:right;cursor:pointer;"  onclick="jQuery(\'#dlgDetalle\').slideUp();">[x]</div>
				<div id="detalleNotificacion"></div>
			</div>'.$this->escribeSoloFormaEnviarNotificacion();

			return $bufferSalida;

		}

		function decideFilePath()
		{

			$filepath = $_SESSION['plat'].'/storage/';

			$year  = date('Y');
			$month = date('F');
			$day  = date('j');
			$week   = '';

			if(!is_dir($filepath)){
				//create new folder
				mkdir($filepath);
			}

			if(!is_dir($filepath.$year)){
				//create new folder
				mkdir($filepath.$year);
			}

			if(!is_dir($filepath.$year."/".$month))
			{
				//create new folder
				mkdir($filepath."$year/$month");
			}

			if($day > 0 && $day <= 7)
				$week = 'week1';
			elseif($day > 7 && $day <= 14)
				$week = 'week2';
			elseif($day > 14 && $day <= 21)
				$week = 'week3';
			elseif($day > 21 && $day <= 28 )
				$week = 'week4';
			else
				$week = 'week5';

			if(!is_dir($filepath.$year."/".$month."/".$week))
			{
				//create new folder
				mkdir($filepath."$year/$month/$week");
			}

			$filepath = $filepath.$year."/".$month."/".$week."/";


			return $filepath;
		}

		function uploadAndSaveFile($id,$module,$file_details)
		{

			global $adb, $current_user;
			global $upload_badext;

			$date_var = date('Y-m-d H:i:s');

			$ownerid = $current_user->id;

			if(isset($file_details['original_name']) && $file_details['original_name'] != null) {
				$file_name = $file_details['original_name'];
			} else {
				$file_name = $file_details['name'];
			}

			// Arbitrary File Upload Vulnerability fix - Philip
			$file_name = $this->sanear_string($file_name);
			$binFile = preg_replace('/\s+/', '_', $file_name);//replace space with _ in filename
			$ext_pos = strrpos($binFile, ".");

			$ext = substr($binFile, $ext_pos + 1);

			if (in_array(strtolower($ext), $upload_badext))
			{
				$binFile .= ".txt";
			}
			// Vulnerability fix ends

			$sql3="select id from vtiger_crmentity_seq";
			$current_id=$this->retornaFila($this->ejecutaConsulta($sql3));
			$current_id=$current_id['id'];
			$current_id++;

			$sql2 = "UPDATE vtiger_crmentity_seq SET id = ".$current_id;
			$re2 = $this->ejecutaConsulta($sql2);

			$filename = ltrim(basename(" ".$binFile)); //allowed filename like UTF-8 characters
			$filetype= $file_details['type'];
			$filesize = $file_details['size'];
			$filetmp_name = $file_details['tmp_name'];

			//get the file path inwhich folder we want to upload the file
			$upload_file_path = $this->decideFilePath();

			//upload the file in server
			$upload_status = move_uploaded_file($filetmp_name,$upload_file_path.$current_id."_".$binFile);

			$save_file = 'true';

			if($save_file == 'true' && $upload_status == 'true')
			{

				$sql1 = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime)
							values($current_id, $current_user->id, $ownerid, '$module', '', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s')."')";

				$this->ejecutaConsulta($sql1);

				$sql2="insert into vtiger_attachments(attachmentsid, name, description, type, path) values($current_id, '$filename', '', '$filetype', '$upload_file_path')";
				$this->attachments[] = array('path'=>$upload_file_path,'file'=>$current_id.'_'.$filename);
				$this->ejecutaConsulta($sql2);

				$sql3="insert into vtiger_seattachmentsrel values($id,$current_id)";
				$this->ejecutaConsulta($sql3);

				return true;
			}
			else
			{
				return false;
			}
		}

		function main() {
			if ($this->Funcion == 'EnviarNotificacion') {
				$bufferSalida = $this->EscribeFormaEnviarNotificacion();
			}
			elseif ($this->Funcion == 'ConsultarContactos') {
				$bufferSalida = $this->EscribeFormaContactos();
			}
			elseif ($this->Funcion == 'RegistrarNotificacion') {
				//Enviar Mail de notificacion
				//$enlaceNotificacion = '<a href="https://'.$_SERVER['SERVER_NAME'].'/customerPortal2/index.php?module=notificaciones&action=index&login_language='.$this->current_language.'">'.$this->mod_strings['Ver notificacion'].'</a>';
				$this->GuardarNotificacion('NOTIFICACION_PLANA',
											$_REQUEST['accountid'],
											$_REQUEST['subject'],
											$_REQUEST['TextoMensaje'],
											$_REQUEST['contactid']);
				$_REQUEST['tab'] = 4;
				include 'modules/Home/CustomerView.php';
			}
			elseif ($this->Funcion == 'RegistrarNotificacionCliente') {
				$this->GuardarNotificacionCliente();
				$bufferSalida = $this->escribePanelNotificacionesCliente();
			}
			elseif ($this->Funcion == 'CargarDetalleCliente') {
				$bufferSalida = $this->escribeDetalleNotificacionCliente($_REQUEST['conversacionid'],$_REQUEST['notificacionid']);
			}
			elseif ($this->Funcion == 'CargarDetalle') {
				$bufferSalida = $this->escribeDetalleNotificacion($_REQUEST['conversacionid'],$_REQUEST['notificacionid']);
			}
			elseif ($this->Funcion == 'PanelNotificaciones' || $this->Funcion == '') {
				$bufferSalida = $this->escribePanelNotificaciones();
			}
			elseif ($this->Funcion == 'PanelNotificacionesClientes') {
				$bufferSalida = $this->escribePanelNotificacionesCliente();
			}
			elseif ($this->Funcion == 'ListNotificacionesRecibidas') {
				$bufferSalida = $this->escribeNotificacionesRecibidas();
			}
			elseif ($this->Funcion == 'ListNotificacionesEnviadas') {
				$bufferSalida = $this->escribeNotificacionesEnviadas();
			}
			elseif ($this->Funcion == 'ListNotificacionesRecibidasCliente') {
				$bufferSalida = $this->escribeNotificacionesRecibidasCliente();
			}
			elseif ($this->Funcion == 'ListNotificacionesEnviadasCliente') {
				$bufferSalida = $this->escribeNotificacionesEnviadasCliente();
			}
			elseif ($this->Funcion == 'NotificacionesAsociadasRegistro') {
				$bufferSalida = $this->escribirNotificacionesAsociadasRegistro();
			}

			echo $bufferSalida;
		}

		function loadTemplate4Entity($relcrmid,$ispanel = false) {
			global $adb;


			if (!$relcrmid)
				return;


			list($relModule) = $adb->fetch_row($adb->pquery("select setype from vtiger_crmentity where crmid=? and deleted=0", array($relcrmid)));

			if (!$relModule)
				return;


			$eventcode = '';
			$accountid = null;
			$languange = 'Ingles';
			$whereLanguage = '';
			$params = array();


			$objCrm = CRMEntity::getInstance($relModule);
			$objCrm->retrieve_entity_info($relcrmid, $relModule);


			if ($relModule == 'Invoice') {
				$eventcode = 'NOTIFICATION_FROM_INVOICE_AS_PROVIDER';
				$accountid = $objCrm->column_fields['account_id'];


				if (esVistaCliente(null)) {
					$eventcode = 'NOTIFICATION_FROM_INVOICE_AS_CLIENT';
				}

				$variablesVector = array(
					'CUSTOM_CUSTOM2' => $objCrm->column_fields['subject'],
					'CUSTOM_CUSTOM3' => "<a href=\"index.php?module=$relModule&action=DetailView&record=$relcrmid\">".$objCrm->column_fields['subject']."</a>",
					);
			}

			if ($relModule == 'HelpDesk') {
				if ($ispanel)
					$eventcode = 'NOTIFICATION_FROM_HELPDESK_AS_TESTER';
				else
					$eventcode = 'NOTIFICATION_FROM_HELPDESK_AS_PROVIDER';
				$accountid = $objCrm->column_fields['parent_id'];


				if (esVistaCliente(null)) {
					$eventcode = 'NOTIFICATION_FROM_HELPDESK_AS_CLIENT';
				}

				$variablesVector = array(
					'CUSTOM_CUSTOM2' => $objCrm->column_fields['ticket_no'],
					'CUSTOM_CUSTOM3' => "<a href=\"index.php?module=$relModule&action=DetailView&record=$relcrmid\">".$objCrm->column_fields['ticket_no']."</a>",
					'CUSTOM_CUSTOM4' => $objCrm->column_fields['ticket_title'],
					'CUSTOM_CUSTOM5' => $objCrm->column_fields['customerdescription'],
					);
			}

			if ($accountid) {
				$objCrm = CRMEntity::getInstance("Accounts");
				$objCrm->retrieve_entity_info($accountid, "Accounts");

				if (isset($objCrm->column_fields['cf_807']) && $objCrm->column_fields['cf_807']) {
					$whereLanguage = "AND cf_807 = ?";
					$params[] = html_entity_decode($objCrm->column_fields['cf_807'], null, 'UTF-8');
				}
				$variablesVector['CUSTOM_CUSTOM1'] = $objCrm->column_fields['accountname'];

				if (!esVistaCliente(null))
					$this->accountid = $accountid;
			}


			$query = "SELECT T.subject,T.header,T.body,T.footer,E.days_delay_start,E.days_repeat,E.max_repeat
										FROM vtiger_emailmanager_events E
										INNER JOIN vtiger_emailmanager_template T USING(eventid)
										INNER JOIN vtiger_cf_807 L ON(L.picklist_valueid=T.idiomaid)
										WHERE E.code='$eventcode' $whereLanguage
										LIMIT 1";
			$result = $adb->pquery($query, $params);

			if (!$adb->num_rows($result)) {
				$query = "SELECT T.subject,T.header,T.body,T.footer,E.days_delay_start,E.days_repeat,E.max_repeat
										FROM vtiger_emailmanager_events E
										INNER JOIN vtiger_emailmanager_template T USING(eventid)
										INNER JOIN vtiger_cf_807 L ON(L.picklist_valueid=T.idiomaid)
										WHERE E.code='$eventcode'
										LIMIT 1";
				$result = $adb->pquery($query, array());
			}

			if ($result && $adb->num_rows($result)) {
				list($tpl_subject, $tpl_header, $tpl_body, $tpl_footer, $days_delay_start, $days_repeat, $max_repeat) = $adb->fetch_array($result, false);


				if ($tpl_body) {
					preg_match_all('/{<var name="(.*?)">(.*?)<\/var>}/', $tpl_body, $tpl_variables_all);
					foreach ($tpl_variables_all[1] as $key => $varname)
						if (!isset($tpl_variables[$varname]))
							$tpl_variables[$varname] = $tpl_variables_all[0][$key];

					foreach ($tpl_variables as $varname => $varfulltag) {
						if (isset($variablesVector[$varname]))
							$varvalor = $variablesVector[$varname];
						else
							$varvalor = '';
						$tpl_body = str_replace($varfulltag, $varvalor, $tpl_body);
					}
				}


				$this->subject = $tpl_subject;
				$this->body = $tpl_body;
			}
		}

		function initSortByField($module) {

		}

		function filterInactiveFields($module) {

		}

		function escribeComboFiltroModulos($id,$funcion,$panel,$display='') {
			global $adb;
			$bufferSalida = '
			<select id="filterModule'.$panel.'"  name="filtroModulos" '.(($display=='hide')?'style="display:none"':'').' onchange="actualizarListaSegunFiltro(\''.$id.'\',\''.$funcion.'\',\''.$panel.'\');">';

			$result = $adb->query("select name from vtiger_tab where sends_notifications=1");

			$bufferSalida .= "<optgroup label=\"".  getTranslatedString("Tipo de registro asociado") ."\">";
			$bufferSalida .= "<option value=\"\">".  getTranslatedString("Cualquiera") . "</option>";
			while (list($module) = $adb->fetch_row($result))
				$bufferSalida .= "<option value=\"$module\">".  getTranslatedString($module) . "</option>";

			$bufferSalida .= '</optgroup>';
			$bufferSalida .= '</select>';
			return $bufferSalida;
		}

		function getFilterModules() {
			global $adb;

			$result = $adb->query("select name from vtiger_tab where sends_notifications=1");

			$lst = array();
			while (list($module) = $adb->fetch_row($result))
				$lst[] = array('name'=>$module,'label'=>getTranslatedString($module));

			return $lst;
		}

		function escribeComboFiltroRegistros($id,$funcion,$panel,$popup=false) {
			global $adb;
			$where = $whereCliente = '';
			if (isset($_REQUEST['filtro']) && !empty($_REQUEST['filtro'])) {
				$where = " AND A1.status = '".$_REQUEST['filtro']."' ";
			}

			//Se incluye el filtro si es vista de cliente
			if (esVistaCliente($_SESSION['authenticated_user_id'])) {
				$whereCliente = "WHERE B.accountid = ".$this->accountid;
			}

			$sql = "SELECT distinct relmodule, relcrmid FROM vtiger_notificaciones A1 INNER JOIN
					(
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, E.ticketid, F.setype as relmodule
					FROM vtiger_notificaciones A
					INNER JOIN vtiger_crmentity C ON ( A.notificacionid = C.crmid AND C.deleted = 0 AND C.smcreatorid <> ".$_SESSION['authenticated_user_id']." )
					INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
					INNER JOIN vtiger_crmentity D ON ( B.accountid = D.crmid AND D.deleted = 0)
					INNER JOIN vtiger_notificacionescf E ON ( A.notificacionid = E.notificacionid )
					LEFT JOIN vtiger_crmentity F ON ( A.relcrmid = F.crmid and F.deleted=0)
					".$whereCliente."
					GROUP BY A.conversacionid
					) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date) WHERE 1 and relmodule is not null ".$where;

			if ($panel == 'sent') {
				$sql = "SELECT distinct relmodule, relcrmid FROM vtiger_notificaciones A1 INNER JOIN
					(
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, G.ticketid, CONCAT(E.first_name,' ',E.last_name) as user, H.setype as relmodule
					FROM vtiger_notificaciones A
					INNER JOIN vtiger_crmentity C ON ( A.notificacionid = C.crmid AND C.deleted = 0	AND C.smcreatorid = ".$_SESSION['authenticated_user_id']." )
					INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
					INNER JOIN vtiger_crmentity D ON ( B.accountid = D.crmid AND D.deleted = 0)
					INNER JOIN vtiger_users E ON (C.smcreatorid = E.id)
					INNER JOIN vtiger_notificacionescf G ON ( A.notificacionid = G.notificacionid )
					LEFT JOIN vtiger_crmentity H on (A.relcrmid=H.crmid and H.deleted=0)
					".$whereCliente."
					GROUP BY A.conversacionid
					) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date) WHERE 1 and relmodule is not null ".$where;
			}
			$result = $this->adb->query($sql);

				$bufferSalida = '
				<select id="filterRegister'.$panel.'" '.(($popup=='hide')?('style="display:none"'):('')).' name="filtroModulos" onchange="actualizarListaSegunFiltro(\''.$id.'\',\''.$funcion.'\',\''.$panel.'\');">';
				$bufferSalida .= "<optgroup label=\"".  getTranslatedString("Registro asociado") ."\">";
				$bufferSalida .= "<option value=\"\">".  getTranslatedString("Todos los registros") . "</option>";
				while (list($module, $record) = $adb->fetch_row($result)) {
					$objCrm = CRMEntity::getInstance($module);
					$objCrm->retrieve_entity_info($record, $module);
					$bufferSalida .= "<option value=\"$record\">".$objCrm->column_fields[$objCrm->list_link_field].'</option>';
				}

				$bufferSalida .= '</optgroup>';
				$bufferSalida .= '</select>';
			if($popup===true){
				$result = $this->adb->query($sql);
				$bufferSalida='
						<table width="100%" cellspacing="1" cellpadding="3" border="0" class="lvt small">
							<tbody>
								<tr>
									<td class="lvtCol" colspan="3">'.  getTranslatedString("Registro asociado") .'</td>
								</tr>
								<tr class="lvtColData"><td>&nbsp;</td><td>'.getTranslatedString("Todos los registros").'</td><td><a href="javascript:void(0)" onclick="jQuery(\'#filterRegister'.$panel.'\').val(\'\');jQuery(\'#registerSelected\').val(\'\');actualizarListaSegunFiltro(\''.$id.'\',\''.$funcion.'\',\''.$panel.'\');jQuery(\'#dlgPickRegister\').fadeOut()">Select</a></td></tr>
								';
				while (list($module, $record) = $adb->fetch_row($result)) {
					$objCrm = CRMEntity::getInstance($module);
					$objCrm->retrieve_entity_info($record, $module);
					$bufferSalida .= '<tr class="lvtColData">';
					$bufferSalida .= '<td>'.$record.'</td>';
					$bufferSalida .= '<td>'.$objCrm->column_fields[$objCrm->list_link_field].'</td>';
					$bufferSalida .= '<td><a href="javascript:void(0)" onclick="jQuery(\'#filterRegister'.$panel.'\').val(\''.$record.'\');jQuery(\'#registerSelected\').val(\''.$objCrm->column_fields[$objCrm->list_link_field].'\');actualizarListaSegunFiltro(\''.$id.'\',\''.$funcion.'\',\''.$panel.'\');jQuery(\'#dlgPickRegister\').fadeOut()">Select</a></td>';
					$bufferSalida .= '</tr>';
				}
				$bufferSalida.='
							</tbody>
						</table>
				';
			}
			return $bufferSalida;
		}

		function getFilterRecords($panel = '') {
			global $adb;
			$where = $whereCliente = '';
			if (isset($_REQUEST['filtro']) && !empty($_REQUEST['filtro'])) {
				$where = " AND A1.status = '".$_REQUEST['filtro']."' ";
			}

			//Se incluye el filtro si es vista de cliente
			if (esVistaCliente($_SESSION['authenticated_user_id'])) {
				$whereCliente = "WHERE B.accountid = ".$this->accountid;
			}

			$sql = "SELECT distinct relmodule, relcrmid FROM vtiger_notificaciones A1 INNER JOIN
					(
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, E.ticketid, F.setype as relmodule
					FROM vtiger_notificaciones A
					INNER JOIN vtiger_crmentity C ON ( A.notificacionid = C.crmid AND C.deleted = 0 AND C.smcreatorid <> ".$_SESSION['authenticated_user_id']." )
					INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
					INNER JOIN vtiger_crmentity D ON ( B.accountid = D.crmid AND D.deleted = 0)
					INNER JOIN vtiger_notificacionescf E ON ( A.notificacionid = E.notificacionid )
					LEFT JOIN vtiger_crmentity F ON ( A.relcrmid = F.crmid and F.deleted=0)
					".$whereCliente."
					GROUP BY A.conversacionid
					) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date) WHERE 1 and relmodule is not null ".$where;

			if ($panel == 'sent') {
				$sql = "SELECT distinct relmodule, relcrmid FROM vtiger_notificaciones A1 INNER JOIN
					(
					SELECT A.conversacionid, MAX(A.date) as date, C.smcreatorid, C.description, B.accountname, G.ticketid, CONCAT(E.first_name,' ',E.last_name) as user, H.setype as relmodule
					FROM vtiger_notificaciones A
					INNER JOIN vtiger_crmentity C ON ( A.notificacionid = C.crmid AND C.deleted = 0	AND C.smcreatorid = ".$_SESSION['authenticated_user_id']." )
					INNER JOIN vtiger_account B ON (A.accountid = B.accountid)
					INNER JOIN vtiger_crmentity D ON ( B.accountid = D.crmid AND D.deleted = 0)
					INNER JOIN vtiger_users E ON (C.smcreatorid = E.id)
					INNER JOIN vtiger_notificacionescf G ON ( A.notificacionid = G.notificacionid )
					LEFT JOIN vtiger_crmentity H on (A.relcrmid=H.crmid and H.deleted=0)
					".$whereCliente."
					GROUP BY A.conversacionid
					) as T1
					ON (A1.conversacionid = T1.conversacionid AND A1.date = T1.date) WHERE 1 and relmodule is not null ".$where;
			}
			$result = $this->adb->query($sql);

			while (list($module, $record) = $adb->fetch_row($result)) {
				$objCrm = CRMEntity::getInstance($module);
				$objCrm->retrieve_entity_info($record, $module);
				$lst[] = array('id'=>$record,'value'=>$objCrm->column_fields[$objCrm->list_link_field]);

			}

			return $lst;
		}

		function sanear_string($string) {
			$string = trim($string);
			$string = str_replace( array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
									array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string );
			$string = str_replace( array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string );
			$string = str_replace( array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string );
			$string = str_replace( array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string );
			$string = str_replace( array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string );
			$string = str_replace( array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string );
			//Esta parte se encarga de eliminar cualquier caracter extraño
			return $string;
		}

		/* [ TT11375 ] Notificaciones para “Mi Cuenta en Platzilla” - Pedidos Información Johana Romero 11/10/2016 */
		function getEmails() {
			global $platPrincipal;

			$adbPrincipal = conectaPlataformaHija($platPrincipal);

			$this->campoOrderInicial = "pordefecto";
			$this->orderInicial = "DESC";

			$sql = "SELECT * FROM vtiger_emailmanager_events ORDER BY ".$this->campoOrderInicial." ".$this->orderInicial;
			$result = $adbPrincipal->query($sql);
//			$no_of_rows = $adbPrincipal->num_rows($result);

			$lst = array();

			while($row = $adbPrincipal->fetch_array($result)) {
				if ($_SESSION['esInstancia']){
					if ($row['pordefecto'] == 1){
						$chk = '';
					}else{
						$sql_exists = "SELECT IF( EXISTS(
						             SELECT *
						             FROM vtiger_emanager_events2instance
						             WHERE eventid =  ? AND instancecode = ?), 1, 0)";
						$result_exists = $adbPrincipal->pquery($sql_exists,array($row['eventid'], $_SESSION['plat']));
						$checked = ($result_exists->fields[0] == '1' ? 'checked="checked"' : '');
						$chk = '<input type="checkbox" name="chek_status" id="'.$row['eventid'].'" value="'.$result_exists->fields[0].'"'.$checked.'/>';
					}
				}else{
					$checked = ($row['pordefecto'] == 1 ? 'checked="checked"' : '');
					$chk = '<input type="checkbox" name="chek_status" id="'.$row['eventid'].'"  value="'.$row['pordefecto'].'"'.$checked.'/>';
				}

				$lst[] = array('name'=>$row['label'],
								'checkstatus'=>$chk);
			}

			return $lst;
		}


		function escribeOpcionesCorreo() {
			global $currentModule, $app_strings, $mod_strings, $theme;

			require_once('Smarty_setup.php');
			$smarty = new vtigerCRM_Smarty;
			$smarty->assign("MOD", $mod_strings);
			$smarty->assign("APP", $app_strings);
			$smarty->assign("THEME", $theme);
			$smarty->assign("IMAGE_PATH", $image_path);
			$smarty->assign("NOTIFICACIONES", $this->getEmails());
			$bufferSalida = $smarty->fetch("modules/".$currentModule."/opcionesCorreo.tpl");

			return $bufferSalida;
		}

	}
	// EGC requerido para listas relacionadas
	class notificaciones extends CRMEntity {

		function getListQuery() {
		}

	}

?>