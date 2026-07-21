<?php

require_once('Smarty_setup.php');
require_once('user_privileges/default_module_view.php');

global $mod_strings, $app_strings, $currentModule, $current_user, $theme, $singlepane_view, $adb, $focus;

$smarty = new vtigerCRM_Smarty();

$focus->column_fields['image']=getFileFieldValue($currentModule, 'img_curso', $focus->id);

if($_REQUEST['save'] == 'true' && $_REQUEST['comm'] != '') {
	$sql="INSERT INTO `vtiger_comments` (`module`, `record`, `userid`, `fecha`, `comment`) VALUES 
			('formacion_cursos', 
			 '".$_REQUEST['record']."', 
			 '".$current_user->id."', 
			 '".date('Y-m-d H:i:s')."', 
			 '".mysqli_real_escape_string($_REQUEST['comm'],'')."'); ";
	$adb->query($sql);
}

if ($_REQUEST['save']=='cursovisto' && $_REQUEST['porciento']!='') {
	$cursocompleto=0;
	$return='';
	if ($_REQUEST['porciento']>=95) {
		$cursocompleto=1;
		$return='success';
	}
	$check="SELECT * FROM `vtiger_formacion_cursos_users` WHERE `formacion_cursosid` = '".$_REQUEST['record']."' AND `userid`='".$current_user->id."'";
	$cq=$adb->query($check);
	$ci=$adb->fetchByAssoc($cq);
	if ($adb->num_rows($cq) <= 0) {
		$sql="INSERT INTO `vtiger_formacion_cursos_users` (`formacion_cursosid`, `userid`, `cursocompleto`, `porciento`, `fecha`) VALUES 
				('".$_REQUEST['record']."', 
				 '".$current_user->id."', 
				 '".$cursocompleto."', 
				 '".$_REQUEST['porciento']."', 
				 '".date('Y-m-d H:i:s')."'); ";
	} else {
		if ($ci['cursocompleto'] != '1') {
			$sql = "UPDATE `vtiger_formacion_cursos_users` SET 
				`cursocompleto` = '" . $cursocompleto . "', 
				`porciento`= '" . $_REQUEST['porciento'] . "', 
				`fecha` = '" . date('Y-m-d H:i:s') . "'
				WHERE `formacion_cursosid` = '" . $_REQUEST['record'] . "' AND `userid`='" . $current_user->id . "'";
		}
	}

	if ($sql) {
		$adb->query($sql);
	}
	die($return);
}

function getExtension($str) {
	$pospunto = strrpos($str,'.');
	if (!$pospunto) {
		return '';
	}
	$largo = (strlen($str) - $pospunto);
	$comienzo = ($pospunto + 1);
	$ext = substr($str, $comienzo, $largo);
	return $ext;
}

function getTiempo($fecha) {
	$fechaInicial = $fecha;
	$fechaActual = date('Y-m-d H:i:s');
	$fechaHoraInicial = new DateTime($fechaInicial);
	$fechaHoraActual = new DateTime($fechaActual);
	$interval = $fechaHoraInicial->diff($fechaHoraActual);
	$ret='';
	if ($interval->y != 0) {
		return 'm&ntilde;as de un a&ntilde;o';
	}
	if ($interval->m != 0) {
		$cadena = getCadenaMes($interval->m);
		return $cadena;
	}
	if ($interval->d != 0) {
		$cadena = getCadenaMes($interval->d);
		return $cadena;
	}
	if ($interval->h != 0) {
		$cadena = getCadenaHora($interval->h);
		return $cadena;
	}
	if ($interval->i != 0) {
		$ret .= $interval->i.' minutos, ';
	}
	if ($interval->s !=0) {
		$ret .= $interval->s.' segundos atras ';
	}
	if ($ret=='') {
		$ret = 'Ahora';
	}

	return $ret;
}

function getCadenaMes($cadena) {
	if ($cadena > 1) {
		return $cadena.' meses ';
	}
	return $cadena.' mes ';
}

function getCadenaDia($cadena) {
	if ($cadena > 1) {
		return $cadena.' d&iacute;a ';
	}
	return $cadena.' d&iacute;as ';
}

function getCadenaHora($cadena) {
	if ($cadena > 1) {
		return $cadena.' hora ';
	}
	return $cadena.' horas ';
}

$sql = "SELECT vc.*,CONCAT(vu.`first_name`,' ',vu.`last_name`) AS usuario FROM `vtiger_comments` vc
		INNER JOIN vtiger_users vu ON vu.`id`=vc.`userid`
		WHERE vc.`module`='formacion_cursos' AND vc.`record`='".$_REQUEST['record']."'
		ORDER BY vc.`fecha` DESC";

$COMMENTS = array();
$q=$adb->pquery($sql, array());
while($r=$adb->fetchByAssoc($q)){
	$r['imagen']=getFileFieldValue('Users','imagename',$r['userid']);
	if ($r['imagen']=='' || $r['imagen']=='_') {
		$r['imagen']='themes/images/avatar.gif';
	}
	$r['tiempo']=getTiempo($r['fecha']);
	$COMMENTS[]=$r;
}

$smarty->assign('COMMENTS', $COMMENTS);
$smarty->display('modules/formacion_cursos/DetailViewCursosComentarios.tpl');

?>
