<html>
<head>
<style type="text/css">@import url("../../../themes/softed/style.css");</style>
<script type="text/javascript">
{literal}
function verificar(form){
	if(document.getElementById('coment_ticket_'+form).value==''){
		alert('Debe ingresar el comentario de los Objetivos del dia');
		document.getElementById('coment_ticket_'+form).focus();return;
	}
	if(document.getElementById('nota_ticket_'+form).value=='-'){
		alert('Debe indicar la nota del dia');
		document.getElementById('nota_ticket_'+form).focus();return;
	}
	document.getElementById('form_ticket_'+form).submit();
}
{/literal}
</script>
</head>

<body>
<table cellpadding="0" cellspacing="0" width="100%" style="background-image:url(../../../themes/softed/images/header-bg.png); background-repeat:repeat-x;">
<tr><td width="40%">
	<img src="../../../themes/softed/images/vtiger-crm.gif" width="167" height="47">
	</td>
	<td  align="left" width="60%">&nbsp;</td>
</tr>

</table>

{$ERROR}

<table cellpadding="0" cellspacing="0" width="100%" class="small">
	<tr>
	<td class="dvInnerHeader" colspan="2">
	<div style="float: left; font-weight: bold;">
	<b>&nbsp;Informaci&oacute;n del Parte</b>
	</div>
	</td>
	</tr>
	<tr>
	<td class="dvtCellLabel">
	<strong>Registro:</strong>
	<br>
	</td>
	<td class="dvtCellInfo">
	<br>
		<b>{$DATOS_TICKET.title}<b>
	<br>
	</td></tr>
	<tr>
	<td class="dvtCellLabel">
	<strong>Cuenta:</strong>
	</td>
	<td class="dvtCellInfo">
	<br>
		<b>{$DATOS_TICKET.accountname}</b>
	<br>
	</td>
	</tr>
	<tr>
	  <td class="dvtCellLabel"> <br>
      <strong>Fecha de Inicio</strong></td>
		<td class="dvtCellInfo">
		  {$DATOS_TICKET.inicio}
		</td></tr>

      <tr> <td class="dvtCellLabel"> <br>
      <strong>Fecha Estimada</strong></td>
	  <td class="dvtCellInfo">
	  {$DATOS_TICKET.fecha}
       </td>
    </tr>
    <tr>
	<td class="dvtCellLabel"> <br>
	<strong>Incidencia:</strong>
	</td>
	<td class="dvtCellInfo"><br />
	<div style="width:400px;margin-left:10px;" >
	{$DATOS_TICKET.description}
	</div>
	</td>
	</tr>
     <tr>
	<td class="dvtCellLabel"> <br>
	<strong>Desarrolladores:</strong>
	</td>
	<td class="dvtCellInfo"><br />
	{foreach item=value from=$DESARROLLADORES}
	<div style="width:200px;margin-left:5px; margin-bottom:5px; border:thin; border-color:#333;  border-width:thin; border-style:solid; float:left; text-align:center; padding:3px; ">
	<form action="" method="post">
	<div style="width:140px; float:left;padding:0px;">
		<input name="delDesa" type="hidden" value="{$value.vendorid}">
		<input name="delTicket" type="hidden" value="{$TICKETID}">
		<b>{$value.nombre}</b>
	</div>
	<div style="float:left width:20px; margin-right:3px;padding:0px;">
    <input class="crmbutton small delete" type="button" value="x" name="x"  onClick="submit()">
    </div>
	</form>
	</div>
    {/foreach}

	<form id="form_ticket" name="form_ticket"action="" method="post" >
    <div style=" clear:both;"></div>
    <div align="right"><input class="crmbutton small edit" type="button" value="Agregar Desarrollador" name="AgregarDesarrollador"  onClick="window.open('index.php?module=HelpDesk&action=popup_transportador_de_tareas&Popup=true&add=desa&ticket={$TICKETID}','venta','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=900, height=300, top=85, left=140'); "></div>
	</td>
	</tr>
</table>


<form id="form_ticket" name="form_ticket"action="" method="post" >
<input name="module" type="hidden" value="HelpDesk" />
<input name="action" type="hidden" value="transportador_de_tareas" />
<input name="Popup" type="hidden" value="true" />
<input name="modTicketid" type="hidden" value="{$TICKETID}" />

<table cellpadding="0" cellspacing="0" width="100%" class="small">
	<tr>
	<td class="dvInnerHeader">
	<div style="float: left; font-weight: bold;">
	<b>ORDENES DE TRABAJO ASOCIADAS</b>
	</div>
	</td>
	</tr>
	<tr>
	<td>





   <table width="100%" cellspacing="0" cellpadding="0" border="0" class="small">

		<tbody>

		<tr style="height: 25px;" >

		<td class="dvtCellInfo" style="background-color:#DCDCDC;">No.</td>

		<td class="dvtCellInfo"  width="60%" style="background-color:#DCDCDC;">Descripci&oacute;n</td>

		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Fecha Inicio</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Fecha Fin</td>

		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Desarrollador</td>
		<td class="dvtCellInfo" style="background-color:#DCDCDC;">Eliminar</td>



		</tr>
	{counter assign=1 start=1 skip=1}
	{foreach item=valor from=$PUNTOSDATA}
		<tr style="height: 25px;">
			<td class="dvtCellInfo"  >{counter}</td>
		<td class="dvtCellInfo" width="60%">
			<input name="ordentrabajoid[]" type="hidden" id="ordentrabajoid_{$valor.ordentrabajoid}" value="{$valor.ordentrabajoid}">
			<textarea cols="37" rows="1" id="descripcion_punto_{$valor.ordentrabajoid}" name="descripcion[]">{$valor.description}</textarea>
		</td>
		<td class="dvtCellInfo"  >
			<input name="date[]" type="text" id="jscal_field_date_{$valor.ordentrabajoid}" style="border: 1px solid rgb(186, 186, 186);" tabindex="" value="{$valor.date}" size="11" maxlength="10" readonly="readonly">
			<img id="jscal_trigger_date_{$valor.ordentrabajoid}" src="../../../themes/softed/images/btnL3Calendar.gif">
			<script id="massedit_calendar_date_{$valor.ordentrabajoid}" type="text/javascript">

			Calendar.setup ({ldelim}

				inputField : "jscal_field_date_{$valor.ordentrabajoid}", ifFormat : "%d-%m-%Y", showsTime : false, button : "jscal_trigger_date_{$valor.ordentrabajoid}", singleClick : true, step : 1

			{rdelim})

			</script>

		</td>
		<td class="dvtCellInfo"  >
			<input name="enddate[]" type="text" id="jscal_field_enddate_{$valor.ordentrabajoid}" style="border: 1px solid rgb(186, 186, 186);" tabindex="" value="{$valor.enddate}" size="11" maxlength="10" readonly="readonly">
			<img id="jscal_trigger_enddate_{$valor.ordentrabajoid}" src="../../../themes/softed/images/btnL3Calendar.gif">
			<script id="massedit_calendar_enddate_{$valor.ordentrabajoid}" type="text/javascript">

			Calendar.setup ({ldelim}

				inputField : "jscal_field_enddate_{$valor.ordentrabajoid}", ifFormat : "%d-%m-%Y", showsTime : false, button : "jscal_trigger_enddate_{$valor.ordentrabajoid}", singleClick : true, step : 1

			{rdelim})

			</script>

		</td>
		<td class="dvtCellInfo" >
		<select id="desarrollador_{$valor.ordentrabajoid}" name="vendorid[]"   class="small">
		{foreach item=valors from=$LISTA_DESARROLLADORES}
			{if $valor.vendorid eq $valors.id}
				{assign var=selectvendor value='selected'}
			{else}
				{assign var=selectvendor value=''}
			{/if}
			<option value="{$valors.id}" {$selectvendor}>{$valors.name}</option>';
		{/foreach}
		</select>
        </td>
		<td class="dvtCellInfo" align="center">
		<input type="checkbox" id="eliminar_{$valor.ordentrabajoid}" name="eliminar_{$valor.ordentrabajoid}"></td>
	</tr>
	{/foreach}
	</tbody>
	</table>
	</td>
	</tr>
</table>







<table cellpadding="0" cellspacing="0" width="100%" class="small">
	<tr>
	  <td class="dvInnerHeader">
	    <div style="font-weight: bold; text-align:right;">


        <input class="crmbutton small edit" type="button" value="Agregar OT" name="Agregar"  onClick="window.open('index.php?module=HelpDesk&action=popup_transportador_de_tareas&Popup=true&add=tarea&ticket={$TICKETID}','venta','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=900, height=300, top=85, left=140'); ">
        </div>
      </td>
    </tr>
</table>
<br>
<br>









<table cellpadding="0" cellspacing="0" width="100%">








<tr height="50px"><td colspan="3" bgcolor="#CCCCCC" align="center">


<input class="crmbutton small edit" type="button" value="Modificar" name="modificar_ticket" id="modificar_ticket" onClick="submit()">
<input class="crmbutton small delete" type="button" value="Salir" name="Salir" id="Salir" onClick="window.opener.location.reload();window.close()">
</td></tr>
</table>
</form><br><br><br><br><br><br>


</body>
</html>