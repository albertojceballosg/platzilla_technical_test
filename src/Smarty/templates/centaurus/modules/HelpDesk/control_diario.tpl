<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<style type="text/css">@import url("themes/softed/style.css");</style>
<script type="text/javascript" src="include/jquery/jquery-1.7.1.min.js"></script>
</head>
<body style="margin: 0; padding: 0;">



<table cellpadding="0" cellspacing="0" width="100%" style="background-image:url(themes/softed/images/header-bg.png); background-repeat:repeat-x;">
<tr>
<td align="left" width="60%"><span class="dvHeaderText">Control Diario</span></td>
</tr>
</table>

<hr>

<table cellpadding="0" cellspacing="0" width="100%" class="small">
<tr>
<input name="ticketid" type="hidden" value="<?php echo $_REQUEST['record'];?>" />
<table cellpadding="0" cellspacing="0" width="100%" class="small">
	<tr>
	<td class="dvInnerHeader" colspan="4">
	<div style="float: left; font-weight: bold;">
	<b>&nbsp;Informaci&oacute;n de la Orden de Trabajo</b>
	</div>
	</td>


	</tr>
	<tr>
	<td class="dvtCellLabel" style="width:15%;">
	<strong>Pedido:</strong>
	<br>
	</td>
	<td class="dvtCellInfo" style="width:35%;">
	<br>
		<b>{$PEDIDO}<b>
	<br>
	</td>
	<td class="dvtCellLabel" style="width:15%;">
	<strong>Cuenta:</strong>
	</td>
	<td class="dvtCellInfo" style="width:35%;">
	<br>
		<b>{$ACCOUNTNAME}</b>
	<br>
	</td>
	</tr>

	<tr>
	<td class="dvtCellLabel"> <br>
	<strong>Desarrollador:</strong>
	<br>
	</td>
	<td class="dvtCellInfo">
	<b>{$DESARROLLADOR}</b>
	<input name="vendorid" type="hidden" value="{$VENDORID}" />
	</td>
	<td class="dvtCellLabel"> <br>
	<strong>Fechas de la OTs:</strong>
	<br>
	</td>
	<td class="dvtCellInfo">
	<b>{$FECHAINI} - {$FECHAFIN}</b>
	</td>
	<tr>
	<td class="dvtCellLabel"> <br>
	<strong>Descripción del Pedido:</strong>
	</td>
	<td class="dvtCellInfo"><br />
	<div style="margin-left:10px;" >{$DESCRIPCION_PEDIDO}</div>
	</td>
	<td class="dvtCellLabel"> <br>
	<strong>Descripción OT:</strong>
	</td>
	<td class="dvtCellInfo"><br />
	<div style="margin-left:10px;" >{$DESCRIPCION_OT}</div>
	</td>
	</tr>
	{if $COMENTARIOS_COORDINADOR neq ''}
		<tr style="height:40px">
		  <td width="180" class="dvtCellLabel" colspan="2">
			Comentarios Coordinador:
		  </td>
          <td width="320" class="dvtCellInfo" colspan="2">
			  {$COMENTARIOS_COORDINADOR}
			</td>
          </tr>
	{/if}
	{if $COMENTARIOS_TESTER neq ''}
		<tr style="height:40px">
		  <td width="180" class="dvtCellLabel" colspan="2">
			Comentarios Tester:
		  </td>
          <td width="320" class="dvtCellInfo" colspan="2">
			  {$COMENTARIOS_TESTER}
			</td>
          </tr>
	{/if}
	{if $HOWTOS_NAME neq ''}
		<tr style="height:40px">
		  <td width="180" class="dvtCellLabel" colspan="2">
			How To:
		  </td>
          <td width="320" class="dvtCellInfo" colspan="2">
			  {$HOWTOS_NAME}
			</td>
          </tr>
	{/if}
</table>

<table cellpadding="0" cellspacing="0" width="100%" class="small">
	<tr height="30">
		<td style="text-align:center">
		{$DOCUMENTACION}
		</td>
	</tr>
	<tr>
	<td class="dvInnerHeader">
	<div style="float: left; font-weight: bold;">
	<b>TAREAS PENDIENTES</b>
	</div>
	</td>
	</tr>
	<tr>
	<td>
	{if $TAREAS_PENDIENTES|@count gt 0}
		<table class="lvt small" width="100%">
			<tr>
				<td class="lvtCol">
				Tarea
				</td>
				<td class="lvtCol">
				Descripción
				</td>
				<td class="lvtCol">
				Fecha esperada ejecución
				</td>
			</tr>
		{foreach item=option from=$TAREAS_PENDIENTES}
			<tr class="lvtColData">
				<td>
				{$option.title}
				</td>
				<td>
				{$option.description}
				</td>
				<td>
				{$option.date_expected}
				</td>
			</tr>
		{/foreach}
		</table>
	{else}
		<div>NO HAY TAREAS PENDIENTES</div>
	{/if}
	</td>
	</tr>
	<tr>
	<td class="dvInnerHeader">
	<div style="float: left; font-weight: bold;">
	<b>COMENTARIOS DEL DESARROLLOR</b>
	</div>
	</td>
	</tr>
	<tr>
	<td>
	{if $COMENTARIOS_DESARROLLADOR|@count gt 0}
		<table class="lvt small" width="100%">
			<tr>
				<td class="lvtCol">
				Fecha
				</td>
				<td class="lvtCol">
				Responsable
				</td>
				<td class="lvtCol">
				Horas dedicadas
				</td>
				<td class="lvtCol">
				Comentario
				</td>
			</tr>
		{foreach item=option from=$COMENTARIOS_DESARROLLADOR}
			<tr class="lvtColData">
				<td>
				{$option.fecha}
				</td>
				<td>
				{$option.vendorname}
				</td>
				<td>
				{$option.horas}
				</td>
				<td>
				{$option.comentario}
				</td>
			</tr>
		{/foreach}
		</table>
	{else}
		<div>NO HAY TAREAS PENDIENTES</div>
	{/if}
	</td>
	</tr>
	<tr>
	<td>
	<br>
	</td>
	</tr>
</table>