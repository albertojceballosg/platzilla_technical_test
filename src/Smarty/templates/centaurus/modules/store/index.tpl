{strip}
	{if (!empty ($SUBSCRIPTION))}
		{assign var='customer' value=$SUBSCRIPTION->getCustomer ()}
		{assign var='lastGatewayErrorMessage' value=$SUBSCRIPTION->getLastGatewayErrorMessage ()}
	{else}
		{assign var='customer' value=null}
		{assign var='lastGatewayErrorMessage' value=null}
	{/if}
<link type="text/css" href="modules/store/store.css" />
{assign var='colors' value=array('yellow', 'green', 'blue', 'red')}
<div class="container">
	<div class="row title-content">
		<div class="col-xs-12">
			<h1><strong>Mi suscripción</strong></h1>
		</div>
	</div>
{if (isset ($MESSAGE))}
	<div class="alert alert-{if (!$IS_ERROR)}success{else}danger{/if}">
		<i class="fa fa-{if (!$IS_ERROR)}check{else}times{/if}-circle fa-fw fa-lg"></i>
		<strong>{if (!$IS_ERROR)}Listo{else}Error{/if}!</strong> {$MESSAGE}
	</div>
{/if}
	<div class="main-box clearfix">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#tab-billing-plans" data-toggle="tab">Planes</a></li>
		</ul>
		<div class="tab-content">
			<div id="tab-billing-plans" class="tab-pane fade in active">
				<div class="main-box-header clearfix text-center">
					<h2 style="margin: 1em auto; font-weight: bold;">Te mereces TODO a un precio fácil y claro</h2>
{foreach $AVAILABLE_BILLING_PLANS as $billingPlan}
	{if (empty ($billingPlan->getProduct ())) || ($billingPlan->getProduct ()->getBasePrice () == 0)}
		{continue}
	{/if}
					<div class="plan">
						<a href="javascript:;" onclick="StoreUtils.openChangeBillingPlanModal (this, {if (!empty ($customer->creditCards))}{count ($customer->creditCards)}{else}0{/if});">
							<div class="plan-name{if ($SUBSCRIBED_BILLING_PLAN->getName () != $billingPlan->getName ())} gray{/if}" style="background-color: rgb(0, 255, 0); border: 3px solid {if ($SUBSCRIBED_BILLING_PLAN->getName () == $billingPlan->getName ())}#000000;{else}rgb(0, 255, 0);{/if}">
								<span class="name">{$billingPlan->getName ()}</span>
								<span class="price">{$billingPlan->getProduct ()->getPriceBeforeTax ()|number_format: 2 : ',' : '.'} EUR</span>
							</div>
							<ul class="plan-description">
								<li>{if ($billingPlan->getTotalUsers () == -1)}Usuarios ilimitados{elseif ($billingPlan->getTotalUsers () == 1)}1 Usuario{else}{$billingPlan->getTotalUsers ()} Usuarios{/if}</li>
								<li>{if ($billingPlan->getTotalApplications () == -1)}Todas las aplicaciones ilimitadas{elseif ($billingPlan->getTotalApplications () == 0)}Aplicaciones limitadas{else}{$billingPlan->getTotalApplications ()} aplicaciones ilimitadas{/if}</li>
							</ul>
						</a>
					</div>
{/foreach}
				</div>
				<div class="main-box-body clearfix">
					<h2 class="text-center" style="font-weight: bold; margin: 1em auto;">Aplicaciones de Platzilla</h2>
{foreach $CATEGORIES as $category}
	{assign var='color' value=$colors[($category@index % 4)]}
	{if (empty ($AVAILABLE_APPLICATIONS[$category.name]))}
		{continue}
	{/if}
					<div class="application-category">
						<div class="application-category-label border-{$color}"><span class="label bg-{$color}">{$category.name}</span></div>
						<div class="applications row">
	{foreach $AVAILABLE_APPLICATIONS[$category.name] as $index => $application}
							<div class="application col-xs-12 col-sm-6 col-md-4">
								<div class="col-xs-5 text-center">
									<div class="application-icon">
										<img src="{$APPSIMAGE_PATH}/{$application->getCode ()}.png" alt="{$application->getName ()}" class="img-circle img-responsive"{if (!in_array ($application->getCode (), $SUBSCRIBED_APPLICATION_CODES))} style="-webkit-filter: grayscale(100%); filter: grayscale(100%);"{/if} />
									</div>
								</div>
								<div class="col-xs-7">
									<h2 class="application-title">{$application->getName ()}</h2>
									<p class="application-description">{$application->getDescription ()}</p>
								</div>
								<div class="col-xs-12 text-center">
		{if ($CAN_ADD_APPLICATIONS) && (!in_array ($application->getCode (), $SUBSCRIBED_APPLICATION_CODES))}
									<button type="button" class="btn btn-success btn-icon" style="margin-right: 5px;" title="Contratar" onclick="StoreUtils.subscribeApplication ('{$application->getCode ()}', '{$application->getName ()}', {if (!empty ($customer->creditCards))}{count ($customer->creditCards)}{else}0{/if}, {if (!empty ($SUBSCRIBED_BILLING_PLAN))}{$SUBSCRIBED_BILLING_PLAN->getTotalApplications ()}{else}0{/if}, {count($SUBSCRIBED_APPLICATION_CODES)});">Agregar a suscripción</button>
		{elseif (!in_array ($application->getCode (), $SUBSCRIBED_APPLICATION_CODES)) && (!in_array ($application->getCode (), $INSTALLED_APPLICATION_CODES))}
									<button type="button" class="btn btn-primary" title="Probar" onclick="StoreUtils.addApplication ('{$application->getCode ()}');">Probar</button>
		{/if}
		{if (in_array ($application->getCode (), $SUBSCRIBED_APPLICATION_CODES)) || (in_array ($application->getCode (), $INSTALLED_APPLICATION_CODES))}
									<button type="button" class="btn btn-danger" title="Desinstalar" onclick="StoreUtils.deleteApplication ('{$application->getCode ()}', '{$application->getName ()}');">Desinstalar</button>
		{/if}
								</div>
							</div>
	{/foreach}
						</div>
					</div>
{/foreach}
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/html" id="change-billing-plan-modal-template">
{strip}
<div class="modal fade" id="change-billing-plan-modal" tabindex="-1" role="dialog" aria-hidden="false" style="background-color: rgba(255,255,255,0.8); bottom: 0; left: 0; position: absolute; right: 0; top: 0;">
	<div class="modal-dialog modal-lg" style="bottom: 0; left: 0; position: fixed; right: 0; top: 0;">
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
				<div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
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
	{assign var='availableBillingPlanProduct' value=$availableBillingPlan->getProduct ()}
	{assign var='availableBillingPlanTotalApplications' value=$availableBillingPlan->getTotalApplications ()}
	{assign var='availableBillingPlanTotalDiskSpace' value=$availableBillingPlan->getTotalDiskSpace ()}
	{assign var='availableBillingPlanTotalUsers' value=$availableBillingPlan->getTotalUsers ()}
	{if (empty ($availableBillingPlanProduct)) || ($availableBillingPlanProduct->getBasePrice () == 0)}
		{continue}
	{/if}
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
								<td class="col-price text-right">{$availableBillingPlanProduct->getPriceAfterTax ()|number_format: 2 : ',' : '.'} EUR</td>
							</tr>
{/foreach}
							</tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" onclick="return StoreUtils.validatePlanChange (this);">Cambiar</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</div>
			</form>
		</div>
	</div>
</div>
{/strip}
</script>
<script type="text/javascript" src="modules/store/store.js"></script>
{/strip}