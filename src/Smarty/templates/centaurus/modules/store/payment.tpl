<script language="JavaScript" type="text/javascript" src="include/jquery/inputmask/inputmask.js"></script>
<script language="JavaScript" type="text/javascript" src="include/jquery/inputmask/jquery.inputmask.js"></script>
{literal}
<script>
	function validaForma() {
		var today = new Date();
		var expDate = jQuery("#expiration_date").val();  //static mask
		var cvv = jQuery("#cvv").val();  //static mask
		var number = jQuery("#number").val();  //static mask

		if (jQuery('#braintree-paypal-loggedin').is(':visible')) {
			return true;
		}

		if (expDate.length < 5) {
			alert('Falta dígitos en la Fecha de expiración');
			return false;
		}
		if (cvv.length < 3) {
			alert('Falta dígitos en el Código de seguridad');
			return false;
		}
		if(isNaN(cvv)){
			alert('Solo debe colocar números en el Código de seguridad');
			return false;
		}
		if (number.length < 15) {
			alert('Faltan digitos en el Número de la Tarjeta');
			return false;
		}

		aExpDate = expDate.split("/");
		if (aExpDate[0] > 12) {
			alert('Mes de expiración no puede ser mayor a 12');
			return false;
		}
		if (aExpDate[0] < 1) {
			alert('Mes de expiración no puede ser menor a 01');
			return false;
		}

		if (aExpDate[1] == '__') {
			alert('Año de expiración no puede estar vacío');
			return false;
		}

		if (aExpDate[1] < (today.getFullYear()-2000) || (aExpDate[1] == (today.getFullYear()-2000) && aExpDate[0] <= (today.getMonth()+1))) {
			alert('Fecha de vencimiento no puede ser menor al día de hoy');
			return false;
		}
		if (expDate.length < 5) {
			alert('Falta dígitos en la Fecha de expiración');
			return false;
		}

		return true;
	}
</script>
{/literal}
	<header class="main-box-header clearfix">
		<h2><strong>¡Muy bien!</strong></h2>
		<h2>Haz seleccionado la(s) App(s): <strong>{$PLAN_PAGO}</strong>. <a href="index.php?module=store&action=index">Cambiar App(s)</a></h2>
		<h2>Haz seleccionado <strong>{$TOTALUSERS}</strong> usuario(s) adicional(es). <a href="index.php?module=store&action=confirmacion">Cambiar Usuarios Adicionales</a></h2>
		<h2>Solo falta que selecciones cómo prefieres pagar</h2>
	</header>
	<form action="index.php?module=store&action=checkout" id="my-sample-form" name="mysampleform" method="post">
	<input type="hidden" name="amount" id="amount" value={$PLANES} />
	<div class="row" style="margin-left:auto;margin-right:auto;">
	<div class="col-lg-6 col-md-offset-3" style="border:1px solid;border-color:#e7ebee;border-radius: 3px;padding-bottom:10px;">
		<div class="col-xs-12">
			<h4>Opción 1. Tarjeta de Crédito</h4>
			<div class="form-group">
				<label for="cardNumber">Número de Tarjeta</label>
				<div class="input-group">
					<input type="tel" class="form-control unknown credit" data-braintree-name="number" name="number" id="number" placeholder="" autofocus="">
					<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
				</div>
			</div>
		</div>
		<div class="col-xs-12">
			<div class="form-group">
				<label for="cardExpiry"><span class="hidden-xs">Fecha de Expiración</span></label>
				 <input type="tel" class="form-control" data-braintree-name="expiration_date" id="expiration_date" placeholder="MM / AA" maxlength="5">
			</div>
		</div>
		<div class="col-xs-12">
			<div class="form-group">
				<label for="cardCVC">Código de Seguridad (CVV)</label>
				<input type="password" class="form-control" data-braintree-name="cvv" id="cvv" placeholder="CVV" maxlength="3">
			</div>
		</div>
		<div class="col-xs-12">
			<h4>Opción 2. Con tu cuenta Paypal</h4>
			<div class="form-group">
				<div id="paypal-container"></div>
			</div>
			<div class="form-group">
				<input class="checkbox-m" name="terms_cond" id="terms_cond" type="checkbox" style=" margin-top: -4px;" onclick="activaBtnCrear()">
					<a href="#" onclick='return window.open("index.php?module=Users&action=ViewTermsConditions&Ajax=true","Términos","resizable=1,scrollbars=1");'>Acepto Términos y Condiciones</a>
			</div>
		</div>

		<div class="col-xs-12;" style="margin-top:30px;text-align:center;">
			<button type="submit" id="paymentButton" name="paymentButton" disabled="disabled" class="btn btn-success" style="width:300px;font-size:120%;" onclick="return validaForma();"><span class="fa fa-shopping-cart fa-lg"></span> Pagar {$AMOUNT} €</button>
		</div>
	</div>
	</div>
    </form>

	<script src="https://js.braintreegateway.com/js/beta/braintree-hosted-fields-beta.18.js"></script>
    <script>
	  var clientToken = "{$TOKEN_BRAINTREE}";

	{literal}

      braintree.setup(clientToken, "custom", {
        id: "my-sample-form",
		locale: 'es_es',
		currency: 'EUR',
		paypal: {
			singleUse: true,
			container: "paypal-container",
		}
      });
	{/literal}
    </script>

<link rel="stylesheet" type="text/css" href="include/js/credit/credit.css" />
<script type="text/javascript" src="include/js/credit/credit.js"></script>
<script>
function activaBtnCrear() {ldelim}
		if(jQuery('#terms_cond').prop('checked'))
			jQuery("#paymentButton").prop('disabled', false);
		else
			jQuery("#paymentButton").prop('disabled', true);
	{rdelim}

</script>
{literal}
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery(".credit").credit();
			jQuery("#expiration_date").inputmask("99/99");  //static mask
			//jQuery("#cvv").inputmask("999");  //static mask
		});
	</script>
{/literal}