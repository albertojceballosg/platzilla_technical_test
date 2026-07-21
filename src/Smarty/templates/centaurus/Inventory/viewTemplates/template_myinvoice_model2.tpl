{assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}
<div class="container_model2">
	
			<div class="header_model2">
				<table width="100%" cellpadding="5">
					<tr>
						<td width="15%"><span><b>{$MODULELABEL} # </b></span></td>
						<td class="text-left" width="25%">{$INV_INFO.invoice_no}</td>
						<td width="30%"><span><b>Fecha {$MODULELABEL}:</b> </span></td>
						<td width="30%">
							{$INV_INFO.invoicedate|date_format:"%d/%m/%Y"}</td>
					</tr>
					<tr>
						<td width="15%"><span><b>Vencimiento:</b></span></td>
						<td class="text-left" width="25%"> 
							{$INV_INFO.duedate|date_format:"%d/%m/ %Y"}</td>
						<td width="30%"></td>
						<td width="30%"></td>
					</tr>
				</table>
			</div>
		
			<div class="logo_organization">
				{$IMAGE_ORGANIZATION}
			</div>
		
	<br>
	<div class="information">
		<div id="bill_to">
			<span>ENVÍO</span>
			<p>{$NAME_CUSTOMER}</p>
			<p>{$INV_INFO.bill_street}, {$INV_INFO.bill_state} {$INV_INFO.bill_city} {$INV_INFO.bill_country} </p>
		</div>
		<div id="name_organization">
			<b>{$NAME_CUSTOMER}</b>
		</div>	
	</div>
	
	<div id="content_product">
		{$ASSOCIATED_PRODUCTS}
	</div>	

</div>


<div class="footer_model2">
	<b>TÉRMINOS Y CONDICIONES</b>
	<br><br>
	<p>Terminos y Condiciones</p>
</div>


