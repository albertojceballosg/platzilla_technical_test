<?php
	include_once('modules/Calendar/funciones_panel_jefe_desarrollo.php');
	include_once('include/utils/comunesTareas.php');
	include_once('modules/todotasks/todotasks.php');
	global $adb;
      

	function existeRegistroOTTarea($ot,$date = '') {
		global $adb;
		if ($date == '')
			$date = date('Y-m-d');
		$sql = "SELECT * FROM vtiger_ordentrabajo_informes WHERE ordentrabajoid = ? and fecha = ?";
		$result = $adb->pquery($sql,array($ot,$date));
		
		if ($adb->num_rows($result) > 0)
			return true;
			
		return false;
	
	}

	
	$vendorid = $_REQUEST['vendorid'];
				
	for($k = 0;$k < count($_REQUEST['ticketid']);$k++) {
	
		if ($_REQUEST['ticketid_actualizados'][$k] == '1') {
			$ticketid = $_REQUEST['ticketid'][$k];
			$coment_desarrollador = $_REQUEST['coment_desarrollador'][$k];
			$coment_desarrollador = str_replace("\r\n", "<br/>", $coment_desarrollador);
			$coment_desarrollador = str_replace("'", "/", $coment_desarrollador);
			
			$horas_dedicadas = $_REQUEST['horas_dedicadas_noplat'][$k];
			$ordentrabajoid = $_REQUEST['ordentrabajoid'][$k];
			
			if (existeRegistroOTTarea($ordentrabajoid)) {
				$sql = "UPDATE vtiger_ordentrabajo_informes SET vendorid = ?, horas = ?, comentario = ? WHERE ordentrabajoid = ? AND fecha = ?";
				$adb->pquery($sql,array($vendorid,$horas_dedicadas,$coment_desarrollador,$ordentrabajoid,date('Y-m-d')));
			} elseif($coment_desarrollador!='' and $horas_dedicadas!='-' )  {
				$sql = "INSERT INTO vtiger_ordentrabajo_informes VALUES(NULL,?,?,?,?,?,NULL,NULL,NULL)";
				$adb->pquery($sql,array($ordentrabajoid,date('Y-m-d'),$vendorid,$horas_dedicadas,$coment_desarrollador));
			}
			actualizar_horas_trabajo($ticketid,$horas_dedicadas);
		}
			
	}
	
	$j = 0;
	for($i = 0;$i < count($_REQUEST['ots_finalizadas']);$i++) {
		if (isset($_REQUEST['ots_finalizadas'][$j])) {
			$sql = "UPDATE vtiger_ordentrabajo SET statusot = ? WHERE ordentrabajoid = ?";
			$result = $adb->pquery($sql,array('Terminado',$_REQUEST['ots_finalizadas'][$i]));
			$j++;
		}
	}
	
	$j = 0;
	for($i = 0;$i < count($_REQUEST['tasks']);$i++) {
		if (isset($_REQUEST['tasks'][$j])) {
			$modObj = CRMEntity::getInstance('todotasks');
			$modObj->retrieve_entity_info($_REQUEST['tasks'][$i], 'todotasks');
			$modObj->column_fields['executed'] = 'on';
			$modObj->column_fields['date_end'] = date('Y-m-d');
			$modObj->id = $_REQUEST['tasks'][$i];
			$modObj->mode = 'edit';
			$modObj->save('todotasks');
			$j++;
		}
	}
	//Para cada orden de trabajo del informe se valida 
	for($k = 0;$k < count($_REQUEST['ordentrabajoid']);$k++) {
		
		$modObj2 = CRMEntity::getInstance('ordentrabajo');
		$modObj2->actualizarEstadoOTConTareasPredefinidas($_REQUEST['ordentrabajoid'][$k]);
		$modObj2->retrieve_entity_info($_REQUEST['ordentrabajoid'][$k], 'ordentrabajo');
		
		
		if($modObj2->column_fields['statusot'] == 'Terminado' && comprobarFinOTsDeTicket($modObj2->column_fields['ticketid'])) {
			$ticketid = $modObj2->column_fields['ticketid'];
			actualizar_estado($ticketid);
			//Se envia el correo al ejecutivo de cuenta.
			
			//Crea registro de testing.
			if (determinarTipoRegistro($ticketid) == 'Peticion') {
				$site_URL = 'https://time.platzilla.com';
				list($toName,$toMail,$nombreCuenta,$userid,$username) = obtenerDatosEjecutivoCuenta($ticketid);
				list($casotestingid,$tituloCasoTesting) = obtenerCasoTestingTarea($ticketid);
				$moduloid = obtenerModuloTarea($ticketid);
				$solicitudTestingid = crearRegistroTesting($username,'1','Testing de:'.$data['title'],$ticketid,'',$casotestingid);
				
				//Se determina el modulo asociado a la tarea y se coloca el enlace a la edicion
				
				
				$enlaceTarea = '<a href="'.$site_URL.'/index.php?module=HelpDesk&action=DetailView&record='.$ticketid.'">Ver tarea desarrollada</a>';
				$enlaceCasoTesting = '<a href="'.$site_URL.'/index.php?module=casosdetesting&action=DetailView&record='.$casotestingid.'">Ver Caso de testing de la tarea</a>';
				$enlaceSolicitudTesting = '<a href="'.$site_URL.'/index.php?module=solicitudTesting&action=DetailView&record='.$solicitudTestingid.'">Completar resultado de Testing de Tarea</a>';
				$enlaceModuloTarea = '<a href="'.$site_URL.'/index.php?module=listadomodulos&action=EditView&record='.$moduloid.'&notas=1">Actualizar documentaci&oacute;n t&eacute;cnica</a>';
				
				$data = obtenerDatosTicket($ticketid);

				$arrayVars = array(
					'CUSTOM_CUSTOM1' => $toName,
					'CUSTOM_CUSTOM2' => $data['title'],
					'CUSTOM_CUSTOM3' => $enlaceTarea,
					'CUSTOM_CUSTOM4' => $enlaceCasoTesting,
					'CUSTOM_CUSTOM5' => $enlaceSolicitudTesting,
					'CUSTOM_CUSTOM6' => $enlaceModuloTarea,
				);
				enviarNotificacionEjecutivoCuenta($id_registro,$toName,$toMail,'NOTIFICACION_FIN_DESARROLLO_A_EJECUTIVO_CUENTA',$arrayVars);
			
			}
		}
	}
	/*	
	for($i = 0;$i < count($_REQUEST['title_noplat']);$i++) {
		$userid = obtenerIDUserFromDesarrollador($vendorid);
		guardarRegistroTrabajoNoPlanificado($userid,
											$_REQUEST['horas_dedicadas_noplat'][$i],
											utf8_encode($_REQUEST['incidencia_noplat'][$i]),
											utf8_encode($_REQUEST['title_noplat'][$i]),
											$_REQUEST['parent_id_noplat'][$i],
											"1",
											"1",
											"Si",
											utf8_encode($_REQUEST['coment_desarrollador_noplat'][$i]));
	}*/
	
	$bufferSalida = '
	<script>
      window.close();
	</script>
	';
	echo $bufferSalida;
	
?>