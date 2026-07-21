	<div class="col-lg-12" style="text-align:center">
		<img src="{'isorechazado.png'|@vtiger_imageurl:$THEME}"  border="0" style="width:240px;">
		<br/>
		<br/>
		<img src="{'rechazado.png'|@vtiger_imageurl:$THEME}" border="0"  style="width:92px;">
		<br/>
		<h2>!OH OH!</h2>
		<h2>No hemos podido procesar tu pago</h2>
		<div class="col-xs-12;" style="margin-top:30px;text-align:center;">
		Por favor, intenta de nuevo o utiliza otro medio de pago
		</div>
		
		<div class="col-xs-12;" style="margin-top:30px;text-align:center;">
			<form action="index.php?module=store&action=payment" method="post">
			<input type="hidden" name="amount" id="amount" value={$AMOUNT} />
			<button type="submit" class="btn btn-success" style="width:300px;font-size:120%;">Volver a intentarlo</button>
			</form>
		</div>
	</div>