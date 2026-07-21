<?php

include_once("include/utils/utils.php");
include_once("include/utils/comunesTareas.php");
include_once("modules/Calendar/funciones_panel_jefe_desarrollo.php");

$tipo = tipoUsuario($_SESSION["authenticated_user_id"]);
if(!(isset($_SESSION["authenticated_user_id"]) && (isset($_SESSION["app_unique_key"]) ))){
	echo '<script>


  	window.close();
	</script>
	';
	exit();
}
else {
	$userId = $_SESSION['authenticated_user_id'];
	$mesActual = date( 'm');
	$anoActual = date( 'Y');
	$diaActual = date( 'd');
	$hoy = date('Y-m-d');

	if (tipoUsuario($_SESSION["authenticated_user_id"]) == FALSE or empty($_REQUEST['selectedVendor']) or empty($_REQUEST['ticket']) ){
		echo '<script>


			window.close();
			</script>
			';



		exit();
	}

	else {
		$vendorId = $_REQUEST['selectedVendor'];
		if (esSubordinadoDe($vendorId,$userId,$tipo) == FALSE ){


			echo '<script>


			window.close();
			</script>
			';
			exit();
		}

	}
}

$ticketId = $_REQUEST['ticket'];


if (!empty($_POST['postTicket']) and !empty($_POST['postVendorid'])){
	$tempo = agregarTarea($_POST);
	if ($tempo == true) {
			echo '<script>

			window.opener.location.reload()
			window.close();
			</script>
			';
			exit();

	}
	else {
		$error = '<font color="red">ERROR: Todos los campos son requeridos</font>';
	}

}



?>
<html>

<head>

<style type="text/css">@import url("themes/softed/style.css");</style>
</head>

<body>

<form id="AgregarPunto" name="AgregarPunto"action="" method="post" >
  <input name="postTicket" type="hidden" value="<?= $ticketId ;?>" />
  <input name="postVendorid" type="hidden" value="<?= $vendorId;?>" />
      <div style="text-align:center; font-weight: bold;"><?= $error; ?></div>

  <table cellpadding="0" cellspacing="0" width="100%" class="small">
	<tr>
	<td class="dvInnerHeader">
	<div style="float: left; font-weight: bold;width:100px;">
	<b>AGREGAR TAREA</b>
	</div>
	</td>
	</tr>
	<tr>
	<td>





   <table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">

		<tbody>

		<tr style="height: 25px;" >


		<td class="dvtCellInfo"  width="60%" style="background-color:#DCDCDC;">Descripci&oacute;n</td>


		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Fecha estimada</td>



		</tr>






			<tr style="height: 25px;">


				<td class="dvtCellInfo" width="60%"  ><textarea cols="37" rows="1" id="descripcion" name="descripcion"></textarea><br></td>



				<td class="dvtCellInfo"  >

		<input name="date" type="text" id="jscal_field_date" style="border: 1px solid rgb(186, 186, 186);" tabindex=""   value="<?= date('d-m-Y');?>" size="11" maxlength="10" readonly="readonly">

		<img id="jscal_trigger_date" src="../../themes/softed/images/btnL3Calendar.gif">

		<script id="massedit_calendar_date" type="text/javascript">

			Calendar.setup ({

				inputField : "jscal_field_date", ifFormat : "%d-%m-%Y", showsTime : false, button : "jscal_trigger_date", singleClick : true, step : 1

			})

		</script>

		</td>



			</tr>



	</tbody>

		</table></td>
	</tr>
</table>

<table cellpadding="0" cellspacing="0" width="100%">






  <tr height="50px"><td colspan="3" bgcolor="#CCCCCC" align="center">




<input class="crmbutton small edit" type="button" value="Agregar" name="comentar" id="comentar" onClick="submit()"></td></tr>
</table>
</form>
</body>
