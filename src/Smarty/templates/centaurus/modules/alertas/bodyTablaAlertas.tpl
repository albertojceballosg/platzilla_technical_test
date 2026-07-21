<table  style="border: 1px solid green;" border="0" width="100%" height="430" style="text-align: center">
	<tr>
	<td   style="text-align: center; vertical-align: top; ">				
	<table   width="85%"  style="text-align: center; font-family: verdana; border: 1px solid green; font-size: 10px"  align='center'>
		<tr>
			<td style='text-align: center; border: 1px solid green;'><b>ID</b></td>
			<td style='text-align: center; border: 1px solid green;'><b>Parámetro</b></td>
			<td style='text-align: center; border: 1px solid green;'><b>Valor esperado</b></td>
			<td style='text-align: center; border: 1px solid green;'><b>Periodicidad de analisis</b></td>
			<td style='text-align: center; border: 1px solid green;'><b>Periodo evaluado</b></td>
			<td style='text-align: center; border: 1px solid green;'><b>Email</b></td>
			<td style='text-align: center; border: 1px solid green;'><b>&nbsp; </b></td>
			<td style='text-align: center; border: 1px solid green;'><b>Estado</b></td>
			<td style='text-align: center; border: 1px solid green;'><b>NC asociada</b></td>
		</tr>
		{foreach from=$REGISTROS item='registro' key=x}
		<tr {$registro.colorrenglon}>
			<td style='text-align: center; border: 1px solid green;'>{$registro.id}</td>
			<td style='text-align: center; border: 1px solid green;' onmouseout="tooltip.untip(false)" onmouseover="tooltip.tip(this, '{$registro.descripcion}');"';>{$registro.titulo}</td>
			<td style='text-align: center; border: 1px solid green;'>{$registro.valoresperado}</td>
			<td style='text-align: center; border: 1px solid green;'>{$registro.periodicidad}</td>
			<td style='text-align: center; border: 1px solid green;'>{$registro.ultimoperiodo}</td>
			<td style='text-align: center; border: 1px solid green;'>{$registro.emailsid}</td>
			<td style='text-align: center; border: 1px solid green;'>{$registro.otrosdatos}</td>
			<td style='text-align: center; border: 1px solid green;'>{$registro.estado}</td>
			<td style='text-align: center; border: 1px solid green;'>{$registro.conformidadid}</td>
		</tr>
		{/foreach}
	</table>
</table>