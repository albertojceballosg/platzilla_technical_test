<page backcolor="#FEFEFE" backimg="" backimgx="0mm" backimgy="0mm" backimgw="100%" backtop="0mm" backleft="0mm" backbottom="0mm" backright="0mm" footer="asdf" style="font-size: 8pt; line-height:5mm; width:100%">
	<page_header>
	</page_header>

	<div style="width:100%;">

		
		<div style="width:100%;background-color:#CCCCCC;">
			<table style="width:100%">
				<tr>
					<td colspan="2" style="width:100%;font-size:11pt;text-align:center;background-color:#bbffbb"><b>RECIBO DEPOSITO</b></td>
				</tr>
				<tr>
					<td>A nombre de:</td>
					<td>{$CLIENTE.accountname}</td>
				</tr>
				<tr>
					<td>Por concepto de:</td>
					<td>Deposito de la orden de alquiler Nro.{$ORDEN_ALQUILER.salesorder_no}</td>
				</tr>
				<tr>
					<td>El monto de:</td>
					<td>{$ORDEN_ALQUILER.monto_deposito}</td>
				</tr>
				
			</table>
		</div>
	</div>
		
</page>

<style>
	{literal}
		.color{
			background-color:#AAAAFF
		}
	{/literal}
</style>	