{assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}
<div class="container_model1">
<div class="header_model1">
	<div id="info">
		<h1>{$MODULELABEL}</h1>
		<h2>{$CURRENCY} {$TOTAL} </h2>	
		<br>
		<p>{$MODULELABEL}: {$INV_INFO.invoicedate|date_format:"%d/%m/%Y"}</p>
	</div>
</div>


<div style="text-align-center">
	<br>
		<div class="organization">
			<br>
			<div class="model1_invoice">	
				<p><span><b>{$MODULELABEL} # </b></span> {$INV_INFO.invoice_no}</p>
				<br>
				<p><span><b>{$NAME_CUSTOMER}</b></span></p>
			</div>
			<br><br>
			<div class="customer">	
				<p><span><b>Envío</b></span></p>
				<p>{$INV_INFO.bill_street}</p>
				<p>{$INV_INFO.bill_state}</p>
				<p>{$INV_INFO.bill_city}</p>
				<p>{$INV_INFO.bill_country}</p>
			</div>
			<br>
			<div class="date">	
				<p><span><b>Vencimiento</b></span></p>
				<p>{$INV_INFO.duedate|date_format:"%d/%m/ %Y"}</p>
			</div>
		</div>
		<div class="products"><br>
					{$ASSOCIATED_PRODUCTS}
		</div> 
</div>
</div>


<div class="footer_model1">
	<div class="terms">
		<span><b>TÉRMINOS Y CONDICIONES</b></span>
		<br><br>
		<p>Terminos y Condiciones</p>
	</div>
	<div class="logo_organization_model1" style="text-alig:rigth">
			{$IMAGE_ORGANIZATION}
	</div>
</div>