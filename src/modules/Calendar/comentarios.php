<?php

function listarComentarios($semanal)  {// si semanal es 1 lista los comentarios semanales, si no  lista los diarios
	global $adb;
	$sql = "SELECT  * from vtiger_diarynotes_gerente_general where semanal = ".$semanal." order by date  desc,diarynoteid desc limit 15";
	$result = $adb->query($sql);
	while ($reg=$adb->fetchByAssoc($result)) {
		if (!empty($coments[$reg['date']]['gerente'])){		
			$coments[$reg['date']]['gerente'].= '<div align="center"></div>'.'<p ">'.htmlentities($reg['coment']).'</p>';
		}else{
			$coments[$reg['date']]['gerente']='<p ">'.htmlentities($reg['coment']).'</p>';
		}
	}
	$sql = "SELECT  * from vtiger_diarynotes_gerente_produccion where semanal = ".$semanal." order by date  desc,diarynoteid desc limit 15";
	$result = $adb->query($sql);
	while ($reg=$adb->fetchByAssoc($result)) {		
		if (!empty($coments[$reg['date']]['produccion'])){		
			$coments[$reg['date']]['produccion'].= '<div align="center"></div>'.'<p ">'.htmlentities($reg['coment']).'</p>';
		}else{
			$coments[$reg['date']]['produccion']='<p ">'.htmlentities($reg['coment']).'</p>';
		}
	}
	krsort($coments);
	foreach ($coments as $k => $v){
		if (!empty($v['produccion'])){
			echo '
				<tr style="height:25px;">
	
					<td class="dvtCellInfo" width="20%" align="left"><b>Gerente Produccion - '.date("j-n-Y",strtotime($k)).'</b></td>
	
					<td   class="dvtCellInfo" width="80%" align="left"  >'.$v['produccion'].'</td>
	
				</tr>
				';
		}		
		if (!empty($v['gerente'])){
			echo '			<tr style="background-color:#F8F8F8;height:25px;">

				<td class="dvtCellInfo" width="20%" align="left"><b>Gerente General - '.date("j-n-Y",strtotime($k)).'</b></td>

				<td  class="dvtCellInfo" width="80%" align="left"  >'.$v['gerente'].'</td>

			</tr>';
		}
		
	}
	
}

if ($_GET['semanal'] != 1){
	$semanal = 0;
	$titulo = 'Diarios';
}
else {
	$semanal = 1;
	$titulo = 'Semanales';
}

if (!empty($_POST['coment'])){
	$comentClean = mysql_real_escape_string($_POST['coment']);
	$dateInsert = date('Y-m-d');
	if ($_POST['comentarioTipo'] == 1){
		$comenarioTipo = 1;
	}else{
		$comentarioTipo = 0;
	}
	$sql = "INSERT INTO vtiger_diarynotes_gerente_produccion ( date , coment , semanal) 
			VALUES ('$dateInsert' , '$comentClean', $comentarioTipo)";
	$result = $adb->query($sql);
	$mail ='dpolo@timemanagement.es';
	$asunto= 'Notificacion de comentario';
	$detalle ='El gerente de producci&oacute;n ha comentado en el informe diario';
	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From:'timemanagement.es'<soporte@timemanagement.es>\r\n";
	mail($mail,$asunto,$detalle,$headers);
}
?>
<html>
<head>
<style type="text/css">@import url("../../themes/softed/style.css");</style>
<style type="text/css">
a:link { color: black;text-decoration:none; }
a:hover { color: black;text-decoration:none; }
a:visited { color: black;text-decoration:none; }

</style>
<script type="text/javascript">
function verificar(){
	if(document.getElementById('coment').value==''){
		alert('El comentario no puede estar vacio');
		document.getElementById('coment').focus();
		return false;
	}else{
		return true;
	}
}
</script>


</head>
<body>



<table cellpadding="0" cellspacing="0" width="100%" style="background-image:url(../../themes/softed/images/header-bg.png); background-repeat:repeat-x;">
<tr><td width="20%">
	<img src="../../themes/softed/images/vtiger-crm.gif" width="167" height="47">
	</td>
	<td align="left" width="80%">

	<span class="dvHeaderText">Comentarios</span>

	</td>
</tr>

</table>

<hr>
<br>

<table cellpadding="0" cellspacing="0" width="100%" class="small">
<tr><td colspan="2" style="background-color:#FAAC58; border: 1px solid #DEDEDE;
    color: #000000;
    padding: 8px;" align="center"><b>COMENTARIOS</b></td></tr>



</table>



<table cellpadding="0" cellspacing="0" width="100%" class="small">



			<tr style="height: 25px;">

				<td class="dvtCellInfo" width="20%" align="center" ><b>Fecha</b></td>

				<td   class="dvtCellInfo" width="80%" align="left"  ><b>Comentario</b></td>

			</tr>
            <? listarComentarios($semanal);?>


</table>
<br>
<form action="" method="post" >
<input type="hidden" name="comentarioTipo" value="<?=$semanal?>">
<table cellpadding="0" cellspacing="0" width="100%" class="small">
	<tr>
	<td class="dvInnerHeader" colspan="2">
	<div style="float: left; font-weight: bold;">
	<b>Comentarios del Gerente de Producci&oacuten </b>
	</div>
	</td>
	</tr>
	<tr>
	<td class="dvtCellLabel">
	<strong>Comentario:</strong>
	<br>
	</td>
	<td class="dvtCellInfo">
	<br>
		<textarea cols="37" rows="6" id="coment" name="coment"></textarea>
	<br>
	</td>
	</tr>

</table>
<br>
<table cellpadding="0" cellspacing="0" width="100%">


	<tr height="50px"><td colspan="3" bgcolor="#CCCCCC" align="center">



<input class="crmbutton small edit" type="submit" value="Comentar" name="comentar" id="comentar" onClick="return verificar();">

    <input  class="crmbutton small delete" type="button" value="Cerrar" onClick="window.close()">
	</td></tr>
</table>


</form>
</body>
