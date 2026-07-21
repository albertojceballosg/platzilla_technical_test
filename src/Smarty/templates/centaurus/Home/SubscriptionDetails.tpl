{strip}
    {if (!empty ($SUBSCRIPTION))}
        {assign var='billingPlan' value=$SUBSCRIPTION->getBillingPlan ()}
        {assign var='customer' value=$SUBSCRIPTION->getCustomer ()}
        {assign var='lastGatewayErrorMessage' value=$SUBSCRIPTION->getLastGatewayErrorMessage ()}
        {assign var='subscriptionPaymentDay' value=$SUBSCRIPTION->getRegistrationDate ()}
        {assign var='subscriptionRegistrationDate' value=$SUBSCRIPTION->getRegistrationDate ()->format ('d/m/Y')}
        {assign var='subscriptionServiceEndDate' value=$SUBSCRIPTION->getServiceEndDate ()}
        {assign var='subscriptionServiceStartDate' value=$SUBSCRIPTION->getServiceStartDate ()}
        {assign var='subscriptionStatus' value=$SUBSCRIPTION->getStatus ()}
        {assign var='subscriptionTotalActiveUsers' value=$SUBSCRIPTION->getTotalActiveUsers ()}
    {else}
        {assign var='billingPlan' value=null}
        {assign var='customer' value=null}
        {assign var='lastGatewayErrorMessage' value=null}
        {assign var='subscriptionPaymentDay' value=null}
        {assign var='subscriptionRegistrationDate' value=null}
        {assign var='subscriptionServiceEndDate' value=null}
        {assign var='subscriptionServiceStartDate' value=null}
        {assign var='subscriptionStatus' value=null}
        {assign var='subscriptionTotalActiveUsers' value=null}
    {/if}
    {if (!empty ($billingPlan))}
        {assign var='billingPlanBasePrice' value=$billingPlan->getProduct ()->getBasePrice ()}
        {assign var='billingPlanName' value=$billingPlan->getName ()}
        {assign var='isFreeBillingPlan' value=($billingPlan->getProduct ()->getBasePrice () == 0)}
    {else}
        {assign var='billingPlanName' value=null}
        {assign var='billingPlanBasePrice' value=0}
        {assign var='isFreeBillingPlan' value=true}
    {/if}
    {if (!empty ($subscriptionServiceEndDate))}
        {assign var='subscriptionEndDate' value=$subscriptionServiceEndDate->format ('d/m/Y')}
    {else}
        {assign var='subscriptionEndDate' value=null}
    {/if}
    {if (!empty ($subscriptionServiceStartDate))}
        {assign var='subscriptionStartDate' value=$subscriptionServiceStartDate->format ('d/m/Y')}
    {else}
        {assign var='subscriptionStartDate' value=null}
    {/if}
    {* --- *}
    {assign var='colors' value=array('yellow', 'green', 'blue', 'red')}
    <link rel="stylesheet" type="text/css" href="modules/Home/subscriptions.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/bootstrap/bootstrap-toggle.min.css"/>
    {strip}
        <div class="row">
            <div class="col-xs-12">
                <h1><strong>Mi suscripción mensual</strong></h1>
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
                <li{if (empty ($SELECTED_TAB)) || ($SELECTED_TAB == 'billing-plans')} class="active"{/if}><a
                            href="#tab-billing-plans" data-toggle="tab">Planes</a></li>
                <li{if ($SELECTED_TAB == 'subscription')} class="active"{/if}><a href="#tab-subscription"
                                                                                 data-toggle="tab">Detalles de tu
                        &nbsp;suscripción</a></li>
                <li{if ($SELECTED_TAB == 'organization')} class="active"{/if}><a href="#tab-organization"
                                                                                 data-toggle="tab">Información de la
                        &nbsp;empresa</a></li>
                <li{if ($SELECTED_TAB == 'payment-methods')} class="active"{/if}><a href="#tab-payment-methods"
                                                                                    data-toggle="tab">Métodos de
                        &nbsp;pago</a></li>
                {if (!empty ($INVOICES))}
                    <li{if ($SELECTED_TAB == 'invoices')} class="active"{/if}><a href="#tab-invoices" data-toggle="tab">Facturas
                            &nbsp;uso Platzilla</a></li>
                {/if}
                <li{if ($SELECTED_TAB == 'sistema')} class="active"{/if}><a href="#tab-uso-sistema"
                                                                            data-toggle="tab">Uso del sistema</a></li>
                {if $PROFILE_USE neq NULL}
                    <li{if ($SELECTED_TAB == 'profile')} class="active"{/if}>
                        <a href="#tab-profile-uso" data-toggle="tab">Perfil de uso</a>
                    </li>
                {/if}
            </ul>
            <div class="tab-content">
                {* tab planes *}
                <div id="tab-billing-plans"
                     class="tab-pane fade in{if (empty ($SELECTED_TAB)) || ($SELECTED_TAB == 'billing-plans')} active{/if}">
                    <header class="main-box-header clearfix"></header>
                    <div class="main-box-body clearfix text-center">
                        <h2 style="margin: 1em auto; font-weight: bold;">Coste mensual fácil</h2>
                        {foreach $AVAILABLE_BILLING_PLANS as $availableBillingPlan}
                            {if (empty ($availableBillingPlan->getProduct ())) || ($availableBillingPlan->getProduct ()->getBasePrice () == 0)}
                                {continue}
                            {elseif ($availableBillingPlan->getStatus () == PlatformBillingPlan::STATUS_INACTIVE)}
                                {continue}
                            {/if}
                            <div class="plan pull-left">
                                <a href="javascript:;"
                                   onclick="StoreUtils.openChangeBillingPlanModal (this, {if (!empty ($customer->creditCards))}{count ($customer->creditCards)}{else}0{/if});">
                                    <div class="plan-name-container{if ($SUBSCRIBED_BILLING_PLAN->getName () != $availableBillingPlan->getName ())} gray{else} selected{/if}">
                                        <div class="plan-name" style="text-align: center">
                                            {* <span class="name">{$availableBillingPlan->getName ()}</span> *}
                                            {if ($availableBillingPlan->getTotalUsers ()  <= 1)}
                                                <span class="price"
                                                      style="margin-left: -2px">{$availableBillingPlan->getProduct ()->getPriceBeforeTax ()|number_format: 0 : ',' : '.'}
                                                    &nbsp;EUR / mes</span>
                                            {else}
                                                <span class="price" style="margin-left: -10px"><h1
                                                            style="color: black; font-weight: bold;font-size: 2.5em">{$availableBillingPlan->getProduct ()->getPriceBeforeTax ()|number_format: 0 : ',' : '.'}
                                                        &nbsp;euros<br>usuario</h1></span>
                                            {/if}
                                        </div>
                                    </div>
                                    {* <ul class="plan-description">
                                        <li>{if ($availableBillingPlan->getTotalUsers () == -1)}
                                                Usuarios ilimitados
                                            {elseif ($availableBillingPlan->getTotalUsers () == 1)}
                                                1 Usuario
                                            {else}
                                                Hasta {$availableBillingPlan->getTotalUsers ()} Usuarios
                                            {/if}
                                        </li>
                                    </ul> *}
                                </a>
                            </div>
                        {/foreach}
                    </div>
                    <div class="col-md-8">
                        <p class="pull-left" style="margin: 25px">¿Tienes mas de 20 usuarios? contáctanos: <a
                                    href="mailto:info@platzilla.com?Subject=Plan%20de%20suscripción">info@platzilla.com</a>
                        </p>
                        {* <h2 class="pull-left" style="vertical-align: middle">Precio fácil, como todo Platzilla</h2> *}
                    </div>
                </div>
                {* tab-subscription *}
                <div id="tab-subscription" class="tab-pane fade in{if ($SELECTED_TAB == 'subscription')} active{/if}">
                    <header class="main-box-header clearfix"></header>
                    <div class="main-box-body clearfix">
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="col-md-4">
                                    <div class="label-input">
                                        <label for="registration-date">Fecha de registro</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-8 field-container">
                                    <div class="input-group" style="width: 100%;">
                                        <input type="text" id="registration-date"
                                               value="{$subscriptionRegistrationDate}" class="form-control"
                                               disabled="disabled"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <div class="col-md-4">
                                    <div class="label-input">
                                        <label>Status</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-8 field-container">
                                    <div class="input-group" style="width: 100%;">
                                        <span class="label label-{if ($subscriptionStatus == PlatformSubscription::STATUS_INACTIVE)}danger{elseif ($TRIAL_PERIOD_END_DATE !== null)}warning{else}success{/if}">{if ($subscriptionStatus == PlatformSubscription::STATUS_INACTIVE)}Inactiva{elseif ($TRIAL_PERIOD_END_DATE !== null)}En pruebas{else}Activa{/if}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {if (!$isFreeBillingPlan)}
                            <div class="row">
                                <div class="col-xs-12 col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="subscription-start-date">Fecha de inicio</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="subscription-start-date"
                                                   value="{$subscriptionStartDate}" class="form-control"
                                                   disabled="disabled"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="subscription-end-date">Fecha de fin</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="subscription-end-date" value="{$subscriptionEndDate}"
                                                   class="form-control" disabled="disabled"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {else}
                            <div class="row">
                                <div class="col-xs-12 col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="subscription-total-free-days">Días de prueba</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="subscription-total-free-days" value="14"
                                                   class="form-control" disabled="disabled"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="trial-period-end-date">Fecha de fin</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="trial-period-end-date"
                                                   value="{$TRIAL_PERIOD_END_DATE->format ('d/m/Y')}"
                                                   class="form-control" disabled="disabled"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                        {if ($SUBSCRIPTION->getStatus () == PlatformSubscription::STATUS_ACTIVE)}
                            <div class="row">
                                <div class="col-xs-12 text-center">
                                    <form action="index.php" method="post"
                                          onsubmit="return StoreUtils.cancelSubscription ();" style="display: inline;">
                                        <input type="hidden" name="module" value="Home"/>
                                        <input type="hidden" name="action" value="CancelSubscription"/>
                                        <input type="hidden" name="Ajax" value="true"/>
                                        <button type="submit" class="btn btn-danger">Cancelar suscripción y darte
                                            de&nbsp
                                            baja
                                        </button>
                                    </form>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
                {* tab-organization *}
                <div id="tab-organization" class="tab-pane fade in{if ($SELECTED_TAB == 'organization')} active{/if}">
                    <div class="main-box-header clearfix"></div>
                    <div class="main-box-body clearfix">
                        <p style="margin-bottom: 2em;">La información de la empresa que aparece aquí sirve para 2
                            fines:&nbsp
                            Recibir las facturas de Platzilla con sus datos (en el caso de tener un Plan de pago) y
                            que&nbsp
                            las facturas que realice en el sistema a sus clientes, lleven los datos de su empresa</p>
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <figure style="display: inline-block;">
                                    <img src="{$ORGANIZATION.organization_logopath}/{$ORGANIZATION.logoname}?{$smarty.now}"
                                         class="img-responsive"/>
                                    <figcaption class="text-center">{$ORGANIZATION.organizationname}</figcaption>
                                </figure>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    {* Identificación fiscal *}
                                    <div class="col-md-12">
                                        <div class="col-md-3">
                                            <div class="label-input">
                                                <label for="country-id">Identificación fiscal</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-9 field-container">
                                            <div class="input-group" style="width: 100%;">
                                                <input type="text" id="country-id" value="{$ORGANIZATION.cif}"
                                                       class="form-control" disabled="disabled"/>
                                            </div>
                                        </div>
                                    </div>
                                    {* Moneda *}
                                    <div class="col-md-12">
                                        <div class="col-md-3">
                                            <div class="label-input">
                                                <label for="currency-code">Moneda</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-9 field-container">
                                            <div class="input-group" style="width: 100%;">
                                                <input type="text" id="currency-code"
                                                       value="{$ORGANIZATION_CURRENCY.currency_name} ({$ORGANIZATION_CURRENCY.currency_symbol})"
                                                       class="form-control" disabled="disabled"/>
                                            </div>
                                        </div>
                                    </div>
                                    {* Día inicio de semana *}
                                    <div class="col-md-12">
                                        <div class="col-md-3">
                                            <div class="label-input">
                                                <label for="currency-code">Día inicio de semana</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-9 field-container">
                                            <div class="input-group" style="width: 100%;">
                                                <input type="text" id="day_week"
                                                       value="{$MOD['DAY-WEEK'][$ORGANIZATION.start_day_week]}"
                                                       class="form-control" disabled="disabled"/>
                                            </div>
                                        </div>
                                    </div>
                                    {* Dirección *}
                                    <div class="col-md-12">
                                        <div class="col-md-3">
                                            <div class="label-input">
                                                <label for="address">Dirección</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-9 field-container">
                                            <div class="input-group" style="width: 100%;">
											<textarea id="address" class="form-control" disabled="disabled" rows="5">
												{if (!empty ($ORGANIZATION.address))}{$ORGANIZATION.address}{/if}
                                                {if (!empty ($ORGANIZATION.city))}, {$ORGANIZATION.city}{/if}
                                                {if (!empty ($ORGANIZATION.state))}, {$ORGANIZATION.state}{/if}
                                                {if (!empty ($ORGANIZATION.code))}, {$ORGANIZATION.code}{/if}
                                                {if (!empty ($ORGANIZATION.country))}, {$ORGANIZATION.country}{/if}
											</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    {* Sitio web *}
                                    <div class="col-md-12">
                                        <div class="col-md-3">
                                            <div class="label-input">
                                                <label for="website">Sitio web</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-9 field-container">
                                            <div class="input-group" style="width: 100%;">
                                                <input type="text" id="website" value="{$ORGANIZATION.website}"
                                                       class="form-control" disabled="disabled"/>
                                            </div>
                                        </div>
                                    </div>
                                    {if (!empty ($ORGANIZATION.default_module))}
                                        {assign var='moduleLabel' value=$ORGANIZATION.default_module|getTranslatedString: $ORGANIZATION.default_module}
                                        <div class="col-md-12">
                                            <div class="col-md-3">
                                                <div class="label-input">
                                                    <label for="default-module">Iniciar en</label>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-9 field-container">
                                                <div class="input-group" style="width: 100%;">
                                                    <input type="text" id="default-module" value="{$moduleLabel}"
                                                           class="form-control" disabled="disabled"/>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <a href="index.php?module=Home&action=EditOrganizationProfile"
                                           class="btn btn-primary">Editar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {* tab-payment-methods *}
                <div id="tab-payment-methods"
                     class="tab-pane fade in{if ($SELECTED_TAB == 'payment-methods')} active{/if}">
                    <header class="main-box-header clearfix"></header>
                    <div class="main-box-body clearfix">
                        <p style="margin-bottom: 2em;">La información suministrada de su tarjeta bancaria es enviada y
                            &nbsp
                            gestionada directamente por el proveedor de servicios de cobro <a
                                    href="https://www.braintreepayments.com" target="_blank">Braintree</a>, que
                            forma&nbsp
                            parte de Paypal. Sus datos están seguros, salvaguardados y cumpliendo las más estrictas&nbsp
                            medidas de seguridad. En ningún caso <strong>Platzilla</strong> almacena datos bancarios
                            suyos, pero sí&nbsp
                            gestiona su suscripción al sistema</p>
                        <div class="table-responsive">
                            <table class="table table-hover no-footer" style="margin-bottom: 0;" width="100%"
                                   cellspacing="0" cellpadding="0" border="0">
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
                                            <td>
                                                <img src="{$creditCard->imageUrl}"/><span>{$creditCard->maskedNumber}</span>
                                            </td>
                                            <td>
                                                <p>{$creditCard->cardholderName}</p>
                                                <p style="margin-bottom: 0;">{trim ("{$creditCard->billingAddress->firstName} {$creditCard->billingAddress->lastName}")}</p>
                                                {if (!empty ($creditCard->billingAddress->company))}
                                                    <p style="margin-bottom: 0;">{$creditCard->billingAddress->company}</p>
                                                {/if}
                                                <p style="margin-bottom: 0;">{join (', ', array_filter (array ($creditCard->billingAddress->streetAddress, $creditCard->billingAddress->extendedAddress, $creditCard->billingAddress->locality, $creditCard->billingAddress->region, $creditCard->billingAddress->postalCode, $creditCard->billingAddress->countryName)))}</p>
                                            </td>
                                            <td class="text-center">{$creditCard->expirationMonth}
                                                /{$creditCard->expirationYear}</td>
                                            <td>
                                                <span class="label label-{if ($creditCard->isDefault ())}success{else}default{/if}">{if ($creditCard->isDefault ())}Activa{else}Inactiva{/if}</span>
                                                {if ($creditCard->isExpired ())}
                                                    <span class="label label-danger">Vencida</span>
                                                {/if}
                                            </td>
                                            <td class="text-left">
                                                {if ((!$creditCard->isDefault ()) && (!$creditCard->isExpired ()))}
                                                    <form action="index.php" method="post"
                                                          onsubmit="return StoreUtils.setDefaultPaymentMethod ();"
                                                          style="display: inline;">
                                                        <input type="hidden" name="module" value="panelusuarios"/>
                                                        <input type="hidden" name="action"
                                                               value="SetDefaultPaymentMethod"/>
                                                        <input type="hidden" name="Ajax" value="true"/>
                                                        <input type="hidden" name="paymentmethodid"
                                                               value="{$creditCard->token}"/>
                                                        <button class="btn btn-success btn-icon" type="submit"
                                                                title="Activar"><i class="fa fa-check"></i></button>
                                                    </form>
                                                {/if}
                                                <form action="index.php" method="post"
                                                      onsubmit="return StoreUtils.deletePaymentMethod ();"
                                                      style="display: inline;">
                                                    <input type="hidden" name="module" value="panelusuarios"/>
                                                    <input type="hidden" name="action" value="DeletePaymentMethod"/>
                                                    <input type="hidden" name="Ajax" value="true"/>
                                                    <input type="hidden" name="paymentmethodid"
                                                           value="{$creditCard->token}"/>
                                                    <button class="btn btn-danger btn-icon" type="submit"
                                                            title="Eliminar"><i class="fa fa-trash-o"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <div class="alert alert-{if (empty ($lastGatewayErrorMessage))}info{else}warning{/if}"
                                                 style="margin-bottom: 0;">
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
                                            <a href="index.php?module=panelusuarios&action=AddPaymentMethod"
                                               class="btn btn-info btn-icon"><i class="fa fa-plus"></i></a>
                                        </td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {* *}
                {if (!empty ($INVOICES))}
                    <div id="tab-invoices" class="tab-pane fade in{if ($SELECTED_TAB == 'invoices')} active{/if}">
                        <header class="main-box-header clearfix"></header>
                        <div class="main-box-body clearfix">
                            <div class="table-responsive">
                                <table class="table table-hover dataTable no-footer" width="100%" cellspacing="0"
                                       cellpadding="0" border="0">
                                    <thead>
                                    <tr>
                                        <th aria-controls="table_list">Nº Factura</th>
                                        <th aria-controls="table_list">Fecha de pago</th>
                                        <th aria-controls="table_list">Descripción</th>
                                        <th aria-controls="table_list">Total</th>
                                        <th aria-controls="table_list">Factura PDF</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $INVOICES as $invoice}
                                        <tr>
                                            <td>{$invoice->getNumber ()}</td>
                                            <td>{$invoice->getDueDate ()|date_format: 'd/m/Y'}</td>
                                            <td>{$invoice->getSubject ()}</td>
                                            <td>{$invoice->getTotal ()|number_format:2:'.':','}</td>
                                            <td>
                                                <a href="index.php?module=Home&action=ViewInvoice&record={$invoice->getId ()}&Popup=true"
                                                   target="_blank"><i class="fa fa-file-pdf-o"
                                                                      title="{$APP.LBL_PDF_BUTTON_LABEL}"></i> {$APP.LBL_PDF_BUTTON_LABEL}
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                {/if}
                {* tab-uso-sistema *}
                <div id="tab-uso-sistema" class="tab-pane fade in{if ($SELECTED_TAB == 'sistema')} active{/if}">
                    {* módulos *}
                    <div class="main-box-body clearfix" style="background-color: #f9f8f7!important;">
                        <h2 class="text-center"
                            style="font-weight: bold; margin: 1em auto;">{$MOD.LB_SUBSCRIPTION_TITLE}</h2>
                        <div class="row">
                            <div class="col-xs-12">
                                <ul id="rtl_func">
                                    {foreach $CATEGORIES as $category}
                                        {assign var='color' value=$colors[($category@index % 4)]}
                                        {if (empty ($AVAILABLE_APPLICATIONS[$category.parenttab_label]))}
                                            {continue}
                                        {/if}
                                        <li style="background-color: #ffffff" class="list_root"
                                            id="f_{$category.catappid}">{$category.parenttab_label}
                                            <ul id="c_{$category.catappid}">
                                                {foreach $category.modules as $data}
                                                    <li class="list_child">
                                                        <div class="panel-heading">
                                                            <p class="panel-title"
                                                               style="display:inline-block;width: 70%;vertical-align: middle; margin: 0 auto">{$data.tablabel}</p>
                                                            <span class="pull-right"
                                                                  style="margin: 2px; display: inline-block;width: 15%;">
                                <input class="status-task" id="chck-{$data.name}-{$category.app_code}"
                                       data-status="{$data.presence}"
                                       data-modulerel="{$data.modulerel}" title="{$data.tablabel}"
                                       type="checkbox" {if $data.presence neq "-1"}checked="checked"{/if}
                                       data-toggle="toggle" data-on="On" data-off="Off" data-offstyle="danger"
                                       data-onstyle="success"
                                       data-size="small">
                            </span>
                                                        </div>
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        </li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                    {* /módulos *}
                </div>
                {* tab-profile-uso *}
                {if $PROFILE_USE neq NULL}
                    <div id="tab-profile-uso" class="tab-pane fade in{if ($SELECTED_TAB == 'profile')} active{/if}">
                        <header class="main-box-header clearfix"></header>
                        <div class="main-box-body clearfix">
                            <form class="form-horizontal" name="subcription-profile-form" id="subcription-profile-form"
                                  role="form" method="post" action="index.php">
                                <input type="hidden" name="module" value="how_use"/>
                                <input type="hidden" name="action" value="HowToUseAjaxUtils"/>
                                <input type="hidden" name="function" value="ACTIVATE_PROFILE"/>
                                <input type="hidden" name="Ajax" value="true"/>
                                <div class="row">
                                    {* Company type *}
                                    <div class="form-group">
                                        <label for="profile_to_use_type" class="col-md-3 control-label">Tipo de
                                            empresa:</label>
                                        <div id="pu-div-type" class="col-md-7">
                                            <select class="form-control" name="type" id="profile_type"
                                                    title="El tipo de empresa"
                                                    onchange="HowToUseUtils.searchProfile (this)">
                                                <option value="" selected>Seleccionar</option>
                                                {if $COMPANY_TYPES neq NULL}
                                                    {foreach $COMPANY_TYPES as $type}
                                                        <option value="{$type->getId()}"
                                                                {if in_array($type->getId(), $typeIds)}selected {/if}>
                                                            {$type->getName()}</option>
                                                    {/foreach}
                                                {/if}
                                            </select>
                                            <span id="pu-type" class="help-block"></span>
                                        </div>
                                    </div>
                                    {* company Sector *}
                                    <div class="form-group">
                                        <label for="subcrition_to_use_sector"
                                               class="col-md-3 control-label">Sector:</label>
                                        <div id="pu-div-sector" class="col-md-7">
                                            <select class="form-control" name="sector" id="profile_sector"
                                                    title="El Sector de la económia"
                                                    onchange="HowToUseUtils.searchProfile (this)">
                                                <option value="" selected>Seleccionar</option>
                                                {foreach $COMPANY_SECTOR as $sector}
                                                    <option value="{$sector->getId()}"
                                                            {if in_array($sector->getId(), $sectorIds)}selected{/if} > {$sector->getName()}</option>
                                                {/foreach}
                                            </select>
                                            <span id="pu-sector" class="help-block"></span>
                                        </div>
                                    </div>
                                    {* company phase *}
                                    <div class="form-group">
                                        <label for="subcrition_to_use_phase" class="col-md-3 control-label">Fase de
                                            desarrollo:</label>
                                        <div id="pu-div-phase" class="col-md-7">
                                            <select class="form-control" name="phase" id="profile_phase"
                                                    title="La fase de desarrollo"
                                                    onchange="HowToUseUtils.searchProfile (this)">
                                                <option value="" selected>Seleccionar</option>
                                                {foreach $COMPANY_PHASES as $phase}
                                                    <option value="{$phase->getId()}"
                                                            {if in_array($phase->getId(), $phaseIds)}selected{/if} > {$phase->getName()}</option>
                                                {/foreach}
                                            </select>
                                            <span id="pu-phase" class="help-block"></span>
                                        </div>
                                    </div>
                                    {* profile to use *}
                                    <div class="form-group">
                                        <label for="subcrition_to_use_type" class="col-md-3 control-label">Perfil de
                                            uso:</label>
                                        <div id="pu-div-profile" class="col-md-7">
                                            <select class="form-control" name="profile" id="profile_profile"
                                                    onchange="HowToUseUtils.selectProfile (this)"
                                                    title="El perfil de uso ">
                                                <option value="">Seleccionar..</option>
                                            </select>
                                            <span id="pu-profile" class="help-block"></span>
                                        </div>
                                    </div>
                                    {* Profile - Information *}
                                    <div class="form-group {if $INSTANCE_PROFILE eq NULL}hide{/if}"
                                         id="profile-supcrition-help">
                                        {if $INSTANCE_PROFILE neq NULL}
                                            <label for="profile-info" class="col-md-3 control-label">&nbsp;</label>
                                            <div class="well col-md-7">
                                                <p class="text-justify"><strong>Perfil de uso
                                                        actual:&nbsp;</strong>{$INSTANCE_PROFILE->getName()}</p>
                                                <p class="text-justify">{$INSTANCE_PROFILE->getDescription()}</p>
                                            </div>
                                        {/if}
                                    </div>
                                    {* Seleccionaar *}
                                    <div class="col-md-12 text-center">
                                        <button type="button" class="btn btn-primary"
                                                onclick="HowToUseUtils.activateProfieForm ('')">Activar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    {/strip}
    <script type="text/html" id="change-billing-plan-modal-template">
        {strip}
            <div class="modal fade" id="change-billing-plan-modal" tabindex="-1" role="dialog" aria-hidden="false"
                 style="background-color: rgba(255,255,255,0.8); bottom: 0; left: 0; position: absolute; right: 0; top: 0;">
                <div class="modal-dialog modal-lg" style="bottom: 0; left: 0; position: fixed; right: 0; top: 0;">
                    <div class="modal-content">
                        <form method="post" action="index.php" class="form" onsubmit="return false;">
                            <input type="hidden" name="module" value="store"/>
                            <input type="hidden" name="action" value="ChangeSubscriptionBillingPlan"/>
                            <input type="hidden" name="Ajax" value="true"/>
                            <input type="hidden" id="change-billing-plan-modal-old-billing-plan-id"
                                   value="{$SUBSCRIPTION->getBillingPlan ()->getId ()}"/>
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
                                            <th class="col-number" style="text-align: center; width: 8em;">Aplicaciones
                                                &nbsp
                                                ilimitadas
                                            </th>
                                            <th class="col-number" style="text-align: center; width: 8em;">Usuarios
                                                &nbsp
                                                incluidos
                                            </th>
                                            <th class="col-number" style="text-align: center; width: 8em;">Espacio en
                                                &nbsp
                                                disco
                                            </th>
                                            <th class="col-price" style="text-align: center; width: 7em;">Precio por mes
                                                (*)
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {* $SUBSCRIPTION->getBillingPlan ()->getTotalUsers()|var_dump *}
                                        {foreach $AVAILABLE_BILLING_PLANS as $availableBillingPlan}
                                            {assign var='availableBillingPlanProduct' value=$availableBillingPlan->getProduct ()}
                                            {assign var='availableBillingPlanTotalApplications' value=$availableBillingPlan->getTotalApplications ()}
                                            {assign var='availableBillingPlanTotalDiskSpace' value=$availableBillingPlan->getTotalDiskSpace ()}
                                            {assign var='availableBillingPlanTotalUsers' value=$availableBillingPlan->getTotalUsers ()}
                                            {assign var='activeUsers' value=$SUBSCRIPTION->getTotalActiveUsers ()}
                                            {assign var='costByUsers' value=$SUBSCRIPTION->getBillingPlan ()->getProduct ()->getPriceBeforeTax ()}
                                            {assign var='susTotalUsers' value=$SUBSCRIPTION->getBillingPlan ()->getTotalUsers()}
                                            {assign var='subscribedUsers' value=$SUBSCRIPTION->getSubscribedUsers ()}
                                            {if $susTotalUsers > 1}
                                                {assign var='costUsersTotal' value=$subscribedUsers * $costByUsers}
                                            {else}
                                                {assign var='costUsersTotal' value=$costByUsers}
                                            {/if}
                                            {if (empty ($availableBillingPlanProduct)) || ($availableBillingPlanProduct->getBasePrice () == 0)}
                                                {continue}
                                            {elseif ($availableBillingPlan->getStatus () == PlatformBillingPlan::STATUS_INACTIVE)}
                                                {continue}
                                            {/if}
                                            <tr>
                                                <td class="col-radio">
                                                    <input type="radio" name="billingplanid"
                                                           value="{$availableBillingPlan->getId ()}"{if ($SUBSCRIPTION->getBillingPlan ()->getId () == $availableBillingPlan->getId ())} checked="checked"{/if}
                                                           placeholder=""/>
                                                </td>
                                                <td class="col-name">
                                                    <p style="font-size: 1.1em; font-weight: bold; margin: 0;">{$availableBillingPlan->getName ()}</p>
                                                    <p style="font-style: italic; margin: 0;">{$availableBillingPlan->getDescription ()}</p>
                                                </td>
                                                <td class="col-number text-center">{if ($availableBillingPlanTotalApplications != -1)}{$availableBillingPlanTotalApplications}{else}Ilimitadas{/if}</td>
                                                <td class="col-number text-center">
                                                    {if ($availableBillingPlanTotalUsers == 1)}
                                                        {$availableBillingPlanTotalUsers}
                                                    {elseif ($availableBillingPlanTotalUsers > 1)}
                                                        <select data-cost="{$availableBillingPlanProduct->getPriceAfterTax ()}"
                                                                class="form-control input-sm col-xs-3"
                                                                name="numusers_{$availableBillingPlan->getId ()}"
                                                                onchange="StoreUtils.calculatePrice(this);">
                                                            {section name=users start=1 loop=($availableBillingPlanTotalUsers + 1) step=1}
                                                                <option value="{$smarty.section.users.index}" {if ($SUBSCRIPTION->getBillingPlan ()->getId () == $availableBillingPlan->getId ()) &&($subscribedUsers eq $smarty.section.users.index)} selected="selected" {/if}>{$smarty.section.users.index}</option>
                                                            {/section}
                                                        </select>
                                                    {else}Ilimitados
                                                    {/if}
                                                </td>
                                                <td class="col-number text-center">{if ($availableBillingPlanTotalDiskSpace != -1)}{$availableBillingPlanTotalDiskSpace} MB{else}Ilimitado{/if}</td>
                                                <td class="col-price text-right">
                                                    {if ($availableBillingPlanTotalUsers > 1) && ($SUBSCRIPTION->getBillingPlan ()->getId () == $availableBillingPlan->getId ())}
                                                        {($availableBillingPlanProduct->getPriceAfterTax () * $subscribedUsers)|number_format: 2 : ',' : '.'} EUR
                                                    {elseif ($availableBillingPlanTotalUsers > 1) && ($SUBSCRIPTION->getBillingPlan ()->getId () != $availableBillingPlan->getId ())}
                                                        {($availableBillingPlanProduct->getPriceAfterTax () * 1)|number_format: 2 : ',' : '.'} EUR
                                                    {else}
                                                        {$availableBillingPlanProduct->getPriceAfterTax ()|number_format: 2 : ',' : '.'} EUR
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-center">(*) Precios incluyen impuestos</td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary"
                                        onclick="return StoreUtils.validatePlanChange (this);">Cambiar
                                </button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                            </div>
                            <input type="hidden" name="activeUsers" value="{$activeUsers}"/>
                            <input type="hidden" name="susTotalUsers" value="{$susTotalUsers}"/>
                        </form>
                    </div>
                </div>
            </div>
        {/strip}
    </script>
    <script type="text/javascript" src="modules/store/store.js?v={round(microtime(true) * 1000)}"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-toggle.min.js"></script>
    <script type="text/javascript" src="modules/how_use/how-use-utils.js"></script>
    <script type="text/javascript">
        jQuery(document).on('ready', function () {
            jQuery('input[id ^= chck-]').bootstrapToggle();
        });

        jQuery(function () {
            var resetButton = function (obj) {
                var check = jQuery(obj);

                if (check.parent().hasClass('off')) {
                    check.parent().removeClass('off');
                    check.parent().removeClass('btn-danger');
                    check.parent().addClass('btn-success');
                }
            };

            jQuery('input[id ^= chck-]').change(function (e) {
                var check = jQuery(this),
                    status = check.attr('data-status'),
                    idArr = check.attr('id').split('-'),
                    moduleRel = check.attr('data-modulerel').split(';'),
                    message = 'El módulo ' + check.attr('title') + ' esta vinculado con los siguientes módulos :\n- ',
                    resp = true,
                    arguments = {
                        'module': 'Settings',
                        'action': 'UpdatePresenceModule',
                        'tabname': idArr [1],
                        'appcod': idArr [2],
                        'presence': status,
                        'Ajax': 'true'
                    };

                if ((moduleRel[0] !== '') && (status !== '-1')) {
                    message += moduleRel.join('\n- ') + '\n Al desactivarlo pudiera afectar el funcionamiento de esos módulos ¿Desea continuar?';
                    resp = confirm(message);
                }
                check.bootstrapToggle('disable');
                if (resp) {
                    jQuery.post('index.php', arguments, function (data) {
                        var response,
                            alertMess = 'El módulo ' + check.attr('title') + ' ha sido';
                        alertMess += (status !== '-1') ? ' Desactivado!' : ' Activado!';
                        alertMess += '\nSe recargará esta pagina para actualizar el menú';
                        try {
                            response = JSON.parse(JSON.stringify(data));
                            if (response.error !== 'OK') {
                                throw response.error;
                            } else {
                                alert(alertMess);
                                check.attr('data-status', ((status !== '-1') ? '-1' : '0'));
                                check.bootstrapToggle('enable');
                                if (location.href.indexOf('tab=sistema') === -1) {
                                    location.href += '&tab=sistema';
                                } else {
                                    location.reload();
                                }

                            }
                        } catch (e) {
                            if (e.indexOf('<div') !== -1) {
                                alert('Acceso denegado, contactar al administrador!');
                            }
                            alert(e);
                            resetButton(check);
                            check.bootstrapToggle('enable');
                        }
                    });
                } else {
                    resetButton(check);
                    check.bootstrapToggle('enable');
                }
            })
        });
    </script>
{/strip}