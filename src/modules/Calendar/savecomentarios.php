<?php

	global $adb;

	$date = date("Y-m-d");


	$coment = $_REQUEST['coment'];




		if(!empty($coment))		
		{
			if ($_GET['semanal'] == 1) {
				$extra = 1;
			}
			else{
				$extra = 0;
			}			
			$sql = "INSERT INTO vtiger_diarynotes_gerente_general VALUES (NULL,'".date("Y-m-d")."','".$coment."' , '".$extra."');";
			$res =$adb->query($sql);
			$mail ='ltramontini@timemanagement.es';
			$asunto= 'Notificacion de comentario';
			$detalle ='El gerente de General ha comentado en el informe diario';
			$headers = "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= "From:'timemanagement.es'<soporte@timemanagement.es>\r\n";
			mail($mail,$asunto,$detalle,$headers);
		}







	$bufferSalida = '
	<script>


  	window.close();
	</script>
	';
	echo $bufferSalida;
?>