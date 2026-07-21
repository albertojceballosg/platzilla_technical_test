<?php
	include_once('modules/Calendar/funciones_panel_jefe_desarrollo.php');
	include_once('include/utils/comunesTareas.php');
	//chdir('../../');
	//include("include/conexion_auxiliar.php");
	//include('modules/HelpDesk/HelpDesk.php');
	global $adb;
      

function int_ok($val){
    return ($val !== true) && ((string)(int) $val) === ((string) $val);
}

function comprobarTipoRegistro($ticketid){
    global $adb;
    $query="SELECT  type FROM vtiger_troubletickets where ticketid=".$ticketid;
    $result=$adb->query($query,$conex);
    while($row=$adb->fetch_array($result))
    {
        
       
        switch ($row['type']) {
            /*case '6.PruebasFuncionamiento':
                $acceso=0;
                break;
            case '8.Sistemas Internos':
                 $acceso=0;
                break;
            case '9.Sistemas de clientes':
                $acceso=0;
                break;
            default:
                 $acceso=1;*/
	    case '4.PruebasFuncionamiento':
                $acceso=0;
                break;
            case '5.Sistemas de clientes':
                $acceso=0;
                break;
            default:
                 $acceso=1;
                
        }
    }
        
    
    return $acceso;
}
        

	/*
	if (determinarSiHorasRegistradasMayorHorasTrabajadas(date('Y-m-d'),$_REQUEST['ticketid'],$_REQUEST['vendorid'])) {
		echo '
		<script>
			javascript:alert(\'Esta registrando más horas de las que realmente ha estado conectado\nPor favor verifique\');
			javascript:history.back(-1);
		</script>'
		;
		die();
	}
	*/

	$vendorid = $_REQUEST['vendorid'];
	
				
	for($k = 0;$k < count($_REQUEST['ticketid']);$k++) {
	
		if ($_REQUEST['ticketid_actualizados'][$k] == '1') {
			$ticketid = $_REQUEST['ticketid'][$k];
			$coment_desarrollador=$_REQUEST['coment_desarrollador'][$k];
			$coment_desarrollador=str_replace("\r\n", "<br/>", $coment_desarrollador);
			$coment_desarrollador=str_replace("'", "/", $coment_desarrollador);
			$pointid = $_REQUEST['pointid'.$k];
			$porcentaje = $_REQUEST['porcentaje'.$k];
			
			$horas_dedicadas = $_REQUEST['horas_dedicadas'][$k];
			
//			$coment_desarrollador = utf8_encode($coment_desarrollador);
			
			for ($i = 0;$i < count($pointid);$i++) {
				$dateFormateado = '0000-00-00';
				$enddateFormateado = '0000-00-00';
				if ($porcentaje[$i] >= 100) {
					$state = 'Finalizado';
				} elseif ($porcentaje[$i] > 100) {
					$state = 'Pendiente';
				} else {
					$state = '';
				}
				if (!empty($date[$i])) {
					list($d,$m,$y) = explode("-",$date[$i]);
					$dateFormateado = date("Y-m-d",mktime(0,0,0,$m,$d,$y));
				}
				if ( $enddate[$i]!='0000-00-00' and $porcentaje[$i] >= 100) {

					list($d,$m,$y) = explode("-",date("d-m-Y"));
					$enddateFormateado = date("Y-m-d",mktime(0,0,0,$m,$d,$y));
				}
				
				if($porcentaje[$i]!='-')
				{
					$consulta1 = "UPDATE vtiger_ticketpuntos SET porcentaje = $porcentaje[$i],
															state = '".$state."'
										WHERE ticketid = ".$ticketid." AND pointid = ".$pointid[$i]." AND desarrollador_id = ".$vendorid;

					$res1=$adb->query($consulta1,$conex);
				}
			}
			   
		   
			if (existeRegistroDesarrollador(date("Y-m-d"),$ticketid,$vendorid) and ($coment_desarrollador!='' and $horas_dedicadas!='-' )) {
				$sql = "UPDATE vtiger_diarynotes_desarrolladores SET coment = '".$coment_desarrollador."', horas_dedicadas = ".$horas_dedicadas." 
						WHERE date = '".date('Y-m-d')."' AND ticketid = ".$ticketid." AND desarrollador_id = ".$vendorid;
			} elseif($coment_desarrollador!='' and $horas_dedicadas!='-' )  {
				$sql = "INSERT INTO vtiger_diarynotes_desarrolladores VALUES (NULL,'".date("Y-m-d")."','".$coment_desarrollador."',".$ticketid.",".$vendorid."," .$horas_dedicadas.");";
			}
			
			
			$res =$adb->query($sql,$conex);
			
			$fin = comprobarFinTicket($ticketid,$vendorid);
			
			actualizar_horas_trabajo($ticketid,$horas_dedicadas);
			
			if($fin == 'si') {
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
	}
		
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
	}
	
       
	$bufferSalida = '
	<script>
      window.close();
	</script>
	';
	echo $bufferSalida;

?>