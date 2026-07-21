{strip}
<style type="text/css">
	label {
		font-size:   1.11em;
		font-weight: 300;
	}
	.btn {
		margin-left: 5px;
	}
	.input-group {
		width: 100%;
	}
	.required {
		color: #FF0000;
	}
</style>
<form method="post" action="index.php" onsubmit="return PaymentUtils.validatePaymentForm (this);">
	<input type="hidden" name="module" value="panelusuarios" />
	<input type="hidden" name="action" value="SavePaymentMethod" />
	<input type="hidden" name="nonce" value="" />
	<input type="hidden" name="Ajax" value="true" />
	<div id="paymentmethodcontainer" class="hidden" data-token="{$TOKEN}">
		<div class="row">
			<div class="col-xs-12">
				<h1 class="pull-left"><a href="index.php?module=Home&action=ViewSubscriptionDetails&tab=payment-methods">Método de pago</a></h1>
				<div class="action-bar pull-right">
					<button type="submit" class="btn btn-info">Guardar</button>
					<a href="index.php?module=Home&action=ViewSubscriptionDetails&tab=payment-methods" class="btn btn-warning">Cancelar</a>
				</div>
			</div>
		</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
		<div class="row">
			<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
				<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
			</div>
		</div>
{/if}
		<div class="row">
			<div class="col-xs-12">
				<div class="main-box">
					<header class="title-section main-box-header clearfix">
						<h2 class="pull-left">Información general</h2>
					</header>
					<div class="main-box-body clearfix">
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="cardnumber">Número de tarjeta <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="input-group">
										<span id="cardtype" class="input-group-addon">Tarjeta</span>
										<div id="cardnumber" class="form-control"></div>
										<span class="helper-text"></span>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="cardholderName">Titular de tarjeta <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="input-group">
										<input type="text" id="cardholderName" class="form-control" />
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="expirationmonth">Vencimiento <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="row input-group">
										<div class="col-xs-6 col-md-3">
											<div id="expirationmonth" class="form-control"></div>
											<span class="helper-text"></span>
										</div>
										<div class="col-xs-6 col-md-3">
											<div id="expirationyear" class="form-control"></div>
											<span class="helper-text"></span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="cvv">Código de seguridad <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="row input-group">
										<div class="col-xs-6 col-md-3">
											<div id="cvv" class="form-control"></div>
											<span class="helper-text"></span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group col-md-8 col-md-offset-4">
									<div class="checkbox-nice checkbox-inline">
										<input type="checkbox" id="isdefault" name="isdefault" class="form-control" />
										<label for="isdefault">Usar esta tarjeta para pagar mi suscripción (en el caso que quiera cargar más de 1 tarjeta)</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<div class="main-box">
					<header class="title-section main-box-header clearfix">
						<h2 class="pull-left">Dirección de facturación</h2>
					</header>
					<div class="main-box-body clearfix">
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-2">
									<div class="label-input">
										<label for="addressid">Usar <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-10">
									<select id="addressid" name="addressid" class="form-control" onchange="PaymentUtils.selectBillingAddress (this);">
										<option value=""{if (empty ($ADDRESSES))} selected="selected"{/if}>Nueva...</option>
	{if (!empty ($ADDRESSES))}
		{foreach $ADDRESSES as $addressId => $addressElements}
										<option value="{$addressId}" data-firstname="{$addressElements.firstname}" data-lastname="{$addressElements.lastname}" data-company="{$addressElements.company}" data-streetaddress="{$addressElements.streetaddress}" data-extendedaddress="{$addressElements.extendedaddress}" data-city="{$addressElements.city}" data-state="{$addressElements.state}" data-zipcode="{$addressElements.zipcode}" data-countrycode="{$addressElements.countrycode}">
											{join (', ', array_filter (array (trim ("{$addressElements.firstname} {$addressElements.lastname}"), $addressElements.company, $addressElements.streetaddress, $addressElements.extendedaddress, $addressElements.city, $addressElements.state, $addressElements.zipcode, $addressElements.countryname)))}
										</option>
		{/foreach}
	{/if}
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="firstname">Nombre(s) <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="input-group">
										<input type="text" id="firstname" name="firstname" class="form-control billingaddressfield" />
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="lastname">Apellido(s) <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="input-group">
										<input type="text" id="lastname" name="lastname" class="form-control billingaddressfield" />
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="company">Empresa</label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="input-group">
										<input type="text" id="company" name="company" class="form-control billingaddressfield" />
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-2">
									<div class="label-input">
										<label for="streetaddress">Dirección <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-10">
									<div class="input-group" style="margin-bottom: 10px;">
										<input type="text" id="streetaddress" name="streetaddress" class="form-control billingaddressfield" />
									</div>
									<div class="input-group">
										<input type="text" id="extendedaddress" name="extendedaddress" class="form-control billingaddressfield" placeholder="" />
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="city">Ciudad</label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="input-group">
										<input type="text" id="city" name="city" class="form-control billingaddressfield" />
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="state">Provincia</label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="input-group">
										<input type="text" id="state" name="state" class="form-control billingaddressfield" />
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="zipcode">Código postal <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-3">
									<div class="input-group">
										<input type="text" id="zipcode" name="zipcode" class="form-control billingaddressfield" />
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="col-md-4">
									<div class="label-input">
										<label for="countrycode">País <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8">
									<div class="input-group">
										<select id="countrycode" name="countrycode" class="form-control billingaddressfield">
											<option value=""></option>
	{if (!empty ($COUNTRIES))}
		{foreach $COUNTRIES as $countryCode => $countryName}
											<option value="{$countryCode}">{$countryName}</option>
		{/foreach}
	{/if}
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="https://js.braintreegateway.com/web/3.22.2/js/client.min.js"></script>
<script type="text/javascript" src="https://js.braintreegateway.com/web/3.22.2/js/hosted-fields.min.js"></script>
<script type="text/javascript" src="modules/panelusuarios/payments.js"></script>
{/strip}