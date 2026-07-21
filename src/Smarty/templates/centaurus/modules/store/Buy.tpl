{strip}
{* Favicon *}
<link type="image/x-icon" href="themes/{$THEME}/favicon.png" rel="shortcut icon" />
{* google font libraries *}
<link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css' />
{* bootstrap *}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/store/css/bootstrap/bootstrap.min.css" />
{* global styles *}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/store/css/compiled/theme_styles.css" />
{* this page specific styles *}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/store/css/style.css" />
<link rel="stylesheet" type="text/css" href="modules/store/store.css" />
<form action="index.php" method="post" onsubmit="return validaForm()" role="form">
	<input type="hidden" name="action" value="Payment" />
	<input type="hidden" name="module" value="store" />
	<div class="container">
		<div class="row title-content">
			<div class="col-xs-12">
				<h1><strong>Tienda de Aplicaciones: Comprar</strong></h1>
			</div>
		</div>
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h1>Paso 1: Confirma tus datos</h1>
			</header>
			<div class="row main-box-body clearfix">
				<div class="form-group col-xs-6">
					<label for="nombre">Nombre <span class="required">*</span></label>
					<input class="form-control" id="nombre" name="nombre" placeholder="" value="{$INSTANCE.firstname}" type="text">
				</div>
				<div class="form-group col-xs-6">
					<label for="apellido">Apellido <span class="required">*</span></label>
					<input class="form-control" id="apellido" name="apellido" placeholder="" value="{$INSTANCE.lastname}" type="text">
				</div>
				<div class="form-group col-xs-6">
					<label for="telefono">Teléfono <span class="required">*</span></label>
					<input class="form-control" id="telefono" name="telefono" placeholder="" value="{$INSTANCE.phone}" type="text">
				</div>
				<div class="form-group col-xs-6">
					<label for="pais">País <span class="required">*</span></label>
					<select class="form-control" id="pais" name="pais">
						<option value=""></option>
{foreach $COUNTRIES as $country}
						<option value="{$country}">{$country}</option>
{/foreach}
					</select>
				</div>
				<div class="form-group col-xs-6">
					<label for="empresa">Empresa <span class="required">*</span></label>
					<input class="form-control" id="empresa" name="empresa" placeholder="" value="{$ORGANIZATIONDETAILS.nombreempresa}" type="text">
				</div>
				<div class="form-group col-xs-6">
					<label for="cif">CIF/NIF (Número) <span class="required">*</span></label>
					<input class="form-control" id="cif" name="cif" placeholder="" value="{$ORGANIZATIONDETAILS.cif}" type="text">
				</div>
				<div class="form-group col-xs-6">
					<label for="direccion">Dirección</label>
					<textarea class="form-control" id="direccion" name="direccion"></textarea>
				</div>
				<div class="form-group col-xs-6">
					<label for="codigopostal">Código Postal</label>
					<input class="form-control" id="codigopostal" name="codigopostal" placeholder="" type="text">
				</div>
			</div>
		</div>
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h1>Paso 2: Mira los detalles de tu suscripción</h1>
			</header>
			<div class="row main-box-body clearfix">
{assign var='totalApplicationsPrice' value=0}
{foreach $CART.applications as $application}
				<div class="col-xs-8 application" data-price="{$application.app_price}">
					<div class="col-xs-2 text-center app-icon">
						<img src="{$APPSIMAGE_PATH}/{$application.app_code}.png" width="80" style="border: 4px solid #edeff1; border-radius: 50%;">
					</div>
					<div class="col-xs-8">
						<h2 class="app-title" style="font-size: 1.2em !important;">{$application.app_name}</h2>
						<div class="app-description">{$application.app_descripcion}</div>
					</div>
					<div class="col-xs-2 text-center">
						<span class="app-price-confirmation">{$application.app_price|number_format: 2: ',': '.'} EUR</span>
						<a href="index.php?module=store&action=Buy&deleteapplication={$application.app_code}" class="btn btn-danger btn-sm">ELIMINAR</a>
					</div>
				</div>
	{assign var='totalApplicationsPrice' value=($totalApplicationsPrice + $application.app_price)}
{/foreach}
				<div class="col-xs-4 pull-right">
					<div class="table-responsive">
						<table class="table table-striped">
							<tr>
								<th colspan="2">Suscripción</th>
							</tr>
							<tr>
								<td colspan="2">Usuarios contratados para la fecha: <span>{$INSTANCE.activeusers}</span></td>
							</tr>
							<tr>
								<td width="60%">
									<label for="additionalusers">Usuarios adicionales</label>
									<select id="additionalusers" name="additionalusers" class="form-control" data-price="{$PRICE_FOR_USER}" style="text-align: right; width: 5em;" onchange="StoreUtils.calculateCartPrice ();">
{for $i=0; $i <= 20; $i++}
										<option value="{$i}"{if ($CART.users == $i)} selected="selected"{/if}>{$i}</option>
{/for}
									</select>
								</td>
								<td class="text-right"><span id="priceforuser">{$PRICE_FOR_USER}</span> EUR x Usuario</td>
							</tr>
							<tr class="total">
								<td>
									<strong class="uppercase">Total</strong> / Mes
								</td>
								<td class="text-right" style="font-size: 1em;">
									<strong><span id="totalpriceforusers">{($totalApplicationsPrice + ($PRICE_FOR_USER * $CART.users))|number_format: 2: ',': '.'}</span> EUR</strong>
								</td>
							</tr>
						</table>
					</div>
					<div class="row col-xs-12 text-center">
						<button type="submit" class="btn btn-success btn-lg">Pagar Ahora</button>
						<img class="logo-braintree img-responsive" src="storage/logos/braintree-badge-dark.png" style="display: block; margin: 5px auto 0 auto; width: 150px;" />
					</div>
				</div>

			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="modules/store/store.js"></script>
{/strip}