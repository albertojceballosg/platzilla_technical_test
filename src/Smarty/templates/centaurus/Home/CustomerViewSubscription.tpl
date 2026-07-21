{strip}
{if (!empty ($SUBSCRIPTION))}
	{assign var='applicationSubscriptions' value=$SUBSCRIPTION->getApplicationSubscriptions ()}
	{assign var='billingPlanName' value=$SUBSCRIPTION->getBillingPlan ()->getName ()}
	{assign var='customer' value=$SUBSCRIPTION->getCustomer ()}
	{assign var='lastGatewayErrorMessage' value=$SUBSCRIPTION->getLastGatewayErrorMessage ()}
	{assign var='moduleSubscriptions' value=$SUBSCRIPTION->getModuleSubscriptions ()}
	{assign var='paymentDay' value=$SUBSCRIPTION->getPaymentDay ()}
	{assign var='pendingPayments' value=$SUBSCRIPTION->getPendingPayments ()}
	{assign var='registrationDate' value=$SUBSCRIPTION->getRegistrationDate ()}
	{assign var='serviceStartDate' value=$SUBSCRIPTION->getServiceStartDate ()}
	{assign var='serviceEndDate' value=$SUBSCRIPTION->getServiceEndDate ()}
	{assign var='status' value=$SUBSCRIPTION->getStatus ()}
	{assign var='totalActiveApplications' value=$SUBSCRIPTION->getTotalSubscribedApplications ()}
	{assign var='totalActiveDiskSpace' value=$SUBSCRIPTION->getTotalDiskSpace ()}
	{assign var='totalActiveUsers' value=$SUBSCRIPTION->getTotalActiveUsers ()}
	{assign var='totalApplications' value=$SUBSCRIPTION->getBillingPlan ()->getTotalApplications ()}
	{assign var='totalDiskSpace' value=$SUBSCRIPTION->getBillingPlan ()->getTotalDiskSpace ()}
	{assign var='totalUsers' value=$SUBSCRIPTION->getBillingPlan ()->getTotalUsers ()}
{else}
	{assign var='applicationSubscriptions' value=null}
	{assign var='billingPlanName' value=null}
	{assign var='customer' value=null}
	{assign var='lastGatewayErrorMessage' value=null}
	{assign var='moduleSubscriptions' value=null}
	{assign var='paymentDay' value=null}
	{assign var='pendingPayments' value=null}
	{assign var='registrationDate' value=null}
	{assign var='serviceStartDate' value=null}
	{assign var='serviceEndDate' value=null}
	{assign var='status' value=null}
	{assign var='totalActiveApplications' value=0}
	{assign var='totalActiveDiskSpace' value=0}
	{assign var='totalActiveUsers' value=0}
	{assign var='totalApplications' value=0}
	{assign var='totalDiskSpace' value=null}
	{assign var='totalUsers' value=null}
{/if}

<div class="main-box">
	<header class="main-box-header clearfix"><h2>Información general</h2></header>
	<div class="main-box-body clearfix">
		<div class="row">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="registrationdate">Cliente desde</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="registrationdate" value="{$registrationDate|date_format: 'd/m/Y'}" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="status">Status</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<span class="label label-{if ($status == PlatformSubscription::STATUS_INACTIVE)}danger{else}success{/if}">{$status}</span>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="plan">Plan</label>
					</div>
				</div>
				<div class="form-group col-md-4 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="plan" value="{$billingPlanName}" class="form-control" disabled="disabled" />
					</div>
				</div>
				<div class="col-md-4">
{if ($status == PlatformSubscription::STATUS_ACTIVE)}
					<button type="button" class="btn btn-success" onclick="CustomerViewUtils.openChangeBillingPlanModal (this, {if (!empty ($customer->creditCards))}{count ($customer->creditCards)}{else}0{/if});">Cambiar plan</button>
{/if}
				</div>
			</div>
{if (!empty ($serviceStartDate))}
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="paymentdate">Fecha de pago</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="paymentdate" value="Día {$paymentDay} de cada mes" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
{/if}
		</div>
{if (!empty ($serviceStartDate)) && (!empty ($serviceEndDate))}
		<div class="row">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="servicestartdate">Desde</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="servicestartdate" value="{$serviceStartDate|date_format: 'd/m/Y'}" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="serviceenddate">Hasta</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="serviceenddate" value="{$serviceEndDate|date_format: 'd/m/Y'}" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
		</div>
{/if}
		<div class="row">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="totalapplications">Aplicaciones</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="totalapplications" value="{if ($totalApplications != -1)}{$totalApplications}{else}Ilimitadas{/if}" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="totalactiveapplications">Suscritas</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="totalactiveapplications" value="{$totalActiveApplications}" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="totalusers">Usuarios</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="totalusers" value="{if ($totalUsers != -1)}{$totalUsers}{else}Ilimitados{/if}" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="totalactiveusers">Activos</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="totalactiveusers" value="{$totalActiveUsers}" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="totaldiskspace">Espacio en disco</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="totaldiskspace" value="{if ($totalDiskSpace != -1)}{$totalDiskSpace|number_format: 2 : ',' : '.'} MB{else}Ilimitado{/if}" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="col-md-4">
					<div class="label-input">
						<label for="totalactivediskspace">En uso</label>
					</div>
				</div>
				<div class="form-group col-md-8 field-container">
					<div class="input-group" style="width: 100%;">
						<input type="text" id="totalactivediskspace" value="{$totalActiveDiskSpace|number_format: 2 : ',' : '.'} MB" class="form-control" disabled="disabled" />
					</div>
				</div>
			</div>
		</div>
{if ($HAS_DEMO_DATA)}
		<div class="row">
			<div class="col-md-12">
				<div class="center-block" style="width: 50%">
					<form action="index.php" method="post" onclick="return confirm ('Se eliminará toda la información de prueba, ¿Estás seguro?');" style="display: inline;">
						<input type="hidden" name="module" value="Home" />
						<input type="hidden" name="action" value="DeleteDemoData" />
						<input type="hidden" name="code" value="{$CODE}" />
						<input type="hidden" name="Ajax" value="true" />
						<button type="submit" class="btn btn-primary btn-block">Eliminar datos de prueba</button>
					</form>
				</div>
			</div>
		</div>
{/if}
	</div>
</div>
<div class="main-box">
	<header class="main-box-header clearfix"><h2>Detalles</h2></header>
	<div class="main-box-body clearfix">
		<div class="table-responsive">
			<table class="table table-hover dataTable no-footer" width="100%" cellspacing="0" cellpadding="0" border="0">
				<thead>
				<tr>
					<th aria-controls="table_list">Aplicación</th>
					<th style="text-align: center;" aria-controls="table_list">Status</th>
					<th style="text-align: center; width: 100px;" aria-controls="table_list">Acciones</th>
				</tr>
				</thead>
				<tbody>
{if (!empty ($applicationSubscriptions))}
	{foreach $applicationSubscriptions as $applicationSubscription}
		{assign var='applicationCode' value=$applicationSubscription->getApplicationCode ()}
		{assign var='applicationName' value=$applicationSubscription->getApplicationName ()}
		{assign var='applicationDescription' value=$applicationSubscription->getApplicationDescription ()}
		{if ($status == PlatformSubscription::STATUS_INACTIVE)}
			{assign var='applicationStatus' value=ApplicationSubscription::STATUS_INACTIVE}
		{else}
			{assign var='applicationStatus' value=$applicationSubscription->getStatus ()}
		{/if}
				<tr>
					<td>
						<p style="margin-bottom: 0;">{$applicationName}</p>
						<p style="font-size: 0.85em; font-style: italic; margin-bottom: 0;">{$applicationDescription}</p>
					</td>
					<td class="text-center">
						<span class="label label-{if ($applicationStatus == ApplicationSubscription::STATUS_INACTIVE)}danger{elseif ($applicationStatus == ApplicationSubscription::STATUS_SUBSCRIBED)}success{else}info{/if}">{if ($applicationStatus == ApplicationSubscription::STATUS_SUBSCRIBED)}Contratada{elseif ($applicationStatus == ApplicationSubscription::STATUS_ACTIVE)}En pruebas{else}{$applicationStatus}{/if}</span>
					</td>
					<td class="text-left action">
		{if ($status == PlatformSubscription::STATUS_ACTIVE) && (in_array ($applicationStatus, array (ApplicationSubscription::STATUS_ACTIVE)))}
						<button class="btn btn-success btn-icon" type="button" title="Contratar" onclick="CustomerViewUtils.subscribeApplication ('{$applicationCode}', '{$applicationName}', {if (!empty ($customer->creditCards))}{count ($customer->creditCards)}{else}0{/if});"><i class="fa fa-shopping-cart"></i></button>
		{elseif ($status == PlatformSubscription::STATUS_ACTIVE) && (in_array ($applicationStatus, array (ApplicationSubscription::STATUS_SUBSCRIBED)))}
						<button class="btn btn-warning btn-icon" type="button" title="Volver a plan de pruebas" onclick="CustomerViewUtils.unsubscribeApplication ('{$applicationCode}', '{$applicationName}');"><i class="fa fa-undo"></i></button>
		{/if}
		{if ($status == PlatformSubscription::STATUS_ACTIVE) && (in_array ($applicationStatus, array (ApplicationSubscription::STATUS_ACTIVE, ApplicationSubscription::STATUS_SUBSCRIBED)))}
						<button class="btn btn-danger btn-icon" type="button" title="Desinstalar" onclick="CustomerViewUtils.deleteApplication ('{$applicationCode}', '{$applicationName}');"><i class="fa fa-trash-o"></i></button>
		{/if}
					</td>
				</tr>
	{/foreach}
{else}
				<tr>
					<td colspan="3" class="text-center">No tienes aplicaciones en tu suscripción</td>
				</tr>
{/if}
				</tbody>
			</table>
		</div>
		<div class="panel-group">
			<div class="panel panel-default">
				<div class="panel-heading" style="background-color: #ffffff;">
					<h4 class="panel-title">
						<a data-toggle="collapse" href="#subscription-modules" style="color: #344644;">Módulos</a>
					</h4>
				</div>
				<div id="subscription-modules" class="panel-collapse collapse">
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table table-hover dataTable no-footer" width="100%" cellspacing="0" cellpadding="0" border="0">
								<thead>
								<tr>
									<th>Módulo</th>
									<th style="text-align: center; width: 10em;">Status</th>
									<th style="text-align: center; width: 10em;">Registros</th>
								</tr>
								</thead>
								<tbody>
{if (!empty ($moduleSubscriptions))}
	{foreach $moduleSubscriptions as $moduleSubscription}
		{assign var='moduleLabel' value=$moduleSubscription->getModuleLabel ()}
		{assign var='moduleMaxRecords' value=$moduleSubscription->getMaxRecords ()}
		{assign var='moduleName' value=$moduleSubscription->getModuleName ()}
		{assign var='moduleTotalRecords' value=$moduleSubscription->getTotalRecords ()}
		{if ($status == PlatformSubscription::STATUS_INACTIVE)}
			{assign var='moduleSubscriptionStatus' value=ModuleSubscription::STATUS_INACTIVE}
		{else}
			{assign var='moduleSubscriptionStatus' value=$moduleSubscription->getStatus ()}
		{/if}
								<tr>
									<td>{$moduleLabel}</td>
									<td class="text-center">
										<span class="label label-{if ($moduleSubscriptionStatus == ModuleSubscription::STATUS_INACTIVE)}danger{elseif ($moduleSubscriptionStatus == ModuleSubscription::STATUS_SUBSCRIBED)}success{else}info{/if}">{$moduleSubscriptionStatus}</span>
									</td>
									<td class="text-right">{$moduleTotalRecords} / {if ($moduleSubscriptionStatus == ModuleSubscription::STATUS_INACTIVE)}0{elseif ($moduleMaxRecords == -1)}ilimitado{else}{$moduleMaxRecords}{/if}</td>
								</tr>
	{/foreach}
{else}
								<tr>
									<td colspan="3" class="text-center">No tienes módulos en tu suscripción</td>
								</tr>
{/if}
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{if (!empty ($pendingPayments))}
<div class="main-box">
	<header class="main-box-header clearfix"><h2>Deudas pendientes</h2></header>
	<div class="main-box-body clearfix">
		<div class="table-responsive">
			<table class="table table-hover no-footer" style="margin-bottom: 0;" width="100%" cellspacing="0" cellpadding="0" border="0">
				<thead>
				<tr>
					<th style="width: 7em;" aria-controls="table_list">Vencimiento</th>
					<th aria-controls="table_list">Descripción</th>
					<th style="width: 7em;" aria-controls="table_list">Monto</th>
					<th aria-controls="table_list">Status</th>
				</tr>
				</thead>
				<tbody>
	{assign var='totalAmount' value=0}
	{foreach $pendingPayments as $payment}
				<tr>
					<td>{$payment->getDueDate ()|date_format: 'd/m/Y'}</td>
					<td>{$payment->getArticleName ()} para el período {$payment->getServiceStartDate ()|date_format: 'd/m/Y'} - {$payment->getServiceEndDate ()|date_format: 'd/m/Y'}</td>
					<td class="text-right">{$payment->getAmount ()|number_format: 2:',':'.'}</td>
					<td>{$payment->getStatus ()}</td>
				</tr>
		{assign var='totalAmount' value=($totalAmount + $payment->getAmount ())}
	{/foreach}
				<tr>
					<td></td>
					<td class="text-right total">Total</td>
					<td id="totaldebt" class="text-right total">{$totalAmount|number_format: 2:',':'.'}</td>
					<td></td>
				</tr>
				</tbody>
			</table>
		</div>
	{if (!empty ($customer->creditCards))}
		<div class="text-center">
			<form action="index.php" method="post" onsubmit="return CustomerViewUtils.chargeDefaultPaymentMethod ();" style="display: inline;">
				<input type="hidden" name="module" value="panelusuarios" />
				<input type="hidden" name="action" value="ChargeDefaultPaymentMethod" />
				<input type="hidden" name="Ajax" value="true" />
				<button class="btn btn-warning btn-icon" type="submit" title="Reintentar pago"><i class="fa fa-credit-card"></i></button>
			</form>
		</div>
	{/if}
	</div>
</div>
{/if}
<div class="main-box">
	<header class="main-box-header clearfix"><h2>Métodos de pago</h2></header>
	<div class="main-box-body clearfix">
		<div class="table-responsive">
			<table class="table table-hover no-footer" style="margin-bottom: 0;" width="100%" cellspacing="0" cellpadding="0" border="0">
				<thead>
				<tr>
					<th aria-controls="table_list">Tarjeta</th>
					<th aria-controls="table_list">Titular / Dirección de facturación</th>
					<th style="width: 7em;" aria-controls="table_list">Vencimiento</th>
					<th aria-controls="table_list">Status</th>
					<th class="text-center" aria-controls="table_list">Acciones</th>
				</tr>
				</thead>
				<tbody>
{if (!empty ($customer)) && (!empty ($customer->creditCards))}
	{foreach $customer->creditCards as $creditCard}
				<tr>
					<td><img src="{$creditCard->imageUrl}" /> <span>{$creditCard->maskedNumber}</span></td>
					<td>
						<p>{$creditCard->cardholderName}</p>
						<p style="margin-bottom: 0;">{trim ("{$creditCard->billingAddress->firstName} {$creditCard->billingAddress->lastName}")}</p>
		{if (!empty ($creditCard->billingAddress->company))}
						<p style="margin-bottom: 0;">{$creditCard->billingAddress->company}</p>
		{/if}
						<p style="margin-bottom: 0;">{join (', ', array_filter (array ($creditCard->billingAddress->streetAddress, $creditCard->billingAddress->extendedAddress, $creditCard->billingAddress->locality, $creditCard->billingAddress->region, $creditCard->billingAddress->postalCode, $creditCard->billingAddress->countryName)))}</p>
					</td>
					<td class="text-center">{$creditCard->expirationMonth}/{$creditCard->expirationYear}</td>
					<td>
						<span class="label label-{if ($creditCard->isDefault ())}success{else}default{/if}">{if ($creditCard->isDefault ())}Activa{else}Inactiva{/if}</span>
		{if ($creditCard->isExpired ())}
						<span class="label label-danger">Vencida</span>
		{/if}
					</td>
					<td class="text-left">
		{if ((!$creditCard->isDefault ()) && (!$creditCard->isExpired ()))}
						<form action="index.php" method="post" onsubmit="return CustomerViewUtils.setDefaultPaymentMethod ();" style="display: inline;">
							<input type="hidden" name="module" value="panelusuarios" />
							<input type="hidden" name="action" value="SetDefaultPaymentMethod" />
							<input type="hidden" name="Ajax" value="true" />
							<input type="hidden" name="paymentmethodid" value="{$creditCard->token}" />
							<button class="btn btn-success btn-icon" type="submit" title="Activar"><i class="fa fa-check"></i>
							</button>
						</form>
		{/if}
						<form action="index.php" method="post" onsubmit="return CustomerViewUtils.deletePaymentMethod ();" style="display: inline;">
							<input type="hidden" name="module" value="panelusuarios" />
							<input type="hidden" name="action" value="DeletePaymentMethod" />
							<input type="hidden" name="Ajax" value="true" />
							<input type="hidden" name="paymentmethodid" value="{$creditCard->token}" />
							<button class="btn btn-danger btn-icon" type="submit" title="Eliminar"><i class="fa fa-trash-o"></i></button>
						</form>
					</td>
				</tr>
	{/foreach}
{else}
				<tr>
					<td colspan="5" class="text-center">
						<div class="alert alert-{if (empty ($lastGatewayErrorMessage))}info{else}warning{/if}" style="margin-bottom: 0;">
	{if (empty ($lastGatewayErrorMessage))}
								No tienes métodos de pago registrados hasta el momento
	{else}
								Se ha presentado un error al intentar obtener los métodos de pago registrados
	{/if}
						</div>
					</td>
				</tr>
{/if}
{if (empty ($lastGatewayErrorMessage))}
				<tr>
					<td colspan="5" class="text-center">
						<a href="index.php?module=panelusuarios&action=AddPaymentMethod" class="btn btn-info btn-icon"><i class="fa fa-plus"></i></a>
					</td>
				</tr>
{/if}
				</tbody>
			</table>
		</div>
	</div>
</div>
{if ($status == PlatformSubscription::STATUS_ACTIVE)}
<div class="row">
	<div class="col-xs-12 text-center">
		<form action="index.php" method="post" onsubmit="return CustomerViewUtils.cancelSubscription ();" style="display: inline;">
			<input type="hidden" name="module" value="Home" />
			<input type="hidden" name="action" value="CancelSubscription" />
			<input type="hidden" name="Ajax" value="true" />
			<button type="submit" class="btn btn-danger">Cancelar suscripción y darte de baja</button>
		</form>
	</div>
</div>
{/if}
<script type="text/html" id="change-billing-plan-modal-template">
	<div class="modal fade" id="change-billing-plan-modal" tabindex="-1" role="dialog" aria-hidden="false" style="background-color: rgba(255,255,255,0.8); bottom: 0; left: 0; position: absolute; right: 0; top: 0;">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<form method="post" action="index.php" class="form" onsubmit="return false;">
					<input type="hidden" name="module" value="Home" />
					<input type="hidden" name="action" value="ChangeSubscriptionBillingPlan" />
					<input type="hidden" name="Ajax" value="true" />
					<input type="hidden" id="change-billing-plan-modal-old-billing-plan-id" value="{$SUBSCRIPTION->getBillingPlan ()->getId ()}" />
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">Planes de suscripción mensual</h4>
					</div>
					<div class="modal-body">
						<div class="row table-responsive">
							<table class="table table-striped table-hover">
								<thead>
								<tr>
									<th class="col-radio"></th>
									<th class="col-name">Nombre</th>
									<th class="col-number" style="text-align: center; width: 8em;">Aplicaciones ilimitadas</th>
									<th class="col-number" style="text-align: center; width: 8em;">Usuarios incluidos</th>
									<th class="col-number" style="text-align: center; width: 8em;">Espacio en disco</th>
									<th class="col-price" style="text-align: center; width: 7em;">Precio por mes</th>
								</tr>
								</thead>
								<tbody>
{foreach $AVAILABLE_BILLING_PLANS as $availableBillingPlan}
	{assign var='availableBillingPlanTotalApplications' value=$availableBillingPlan->getTotalApplications ()}
	{assign var='availableBillingPlanTotalDiskSpace' value=$availableBillingPlan->getTotalDiskSpace ()}
	{assign var='availableBillingPlanTotalUsers' value=$availableBillingPlan->getTotalUsers ()}
								<tr>
									<td class="col-radio">
										<input type="radio" name="billingplanid" value="{$availableBillingPlan->getId ()}"{if ($SUBSCRIPTION->getBillingPlan ()->getId () == $availableBillingPlan->getId ())} checked="checked"{/if} placeholder="" />
									</td>
									<td class="col-name">
										<p style="font-size: 1.1em; font-weight: bold; margin: 0;">{$availableBillingPlan->getName ()}</p>
										<p style="font-style: italic; margin: 0;">{$availableBillingPlan->getDescription ()}</p>
									</td>
									<td class="col-number text-center">{if ($availableBillingPlanTotalApplications != -1)}{$availableBillingPlanTotalApplications}{else}Ilimitadas{/if}</td>
									<td class="col-number text-center">{if ($availableBillingPlanTotalUsers != -1)}{$availableBillingPlanTotalUsers}{else}Ilimitados{/if}</td>
									<td class="col-number text-center">{if ($availableBillingPlanTotalDiskSpace != -1)}{$availableBillingPlanTotalDiskSpace} MB{else}Ilimitado{/if}</td>
									<td class="col-price text-right">{$availableBillingPlan->getProduct ()->getPriceAfterTax ()|number_format: 2 : ',' : '.'} EUR</td>
								</tr>
{/foreach}
								</tbody>
							</table>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-primary" onclick="return CustomerViewUtils.validatePlanChange (this);">Cambiar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</script>
{/strip}