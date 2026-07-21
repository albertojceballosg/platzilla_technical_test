{strip}
    <style type="text/css">
        label {
            font-size: 1.11em;
            font-weight: 300;
        }

        .btn {
            margin-left: 5px;
        }

        .input-group {
            width: 100%;
        }

        .required {
            color: {$UI_COLORS.ERROR};
        }
    </style>
    {assign var='courseId' value=$COURSE->getId ()}
    {assign var='courseDescription' value=$COURSE->getDescription ()}
    {assign var='courseCategoryId' value=$COURSE->getCategoryId ()}
    {assign var='courseLevel' value=$COURSE->getLevel ()}
    {assign var='courseName' value=$COURSE->getName ()}
    {assign var='coursePrice' value=$COURSE->getPrice ()}
    {assign var='courseStatus' value=$COURSE->getStatus ()}
    {assign var='courseLessons' value=$COURSE->getLessons ()|count}
	{assign var='userName' value=$USER_NAME}
	{assign var='usersNotPaid' value=$USERS_NOT_PAID}

    <form method="post" action="index.php" onsubmit="return PaymentCourseUtils.validatePaymentForm (this);">
        <input type="hidden" name="module" value="Courses"/>
        <input type="hidden" name="action" value="SavePaymentCourse"/>
        <input type="hidden" name="nonce" value=""/>
        <input type="hidden" name="record" value="{$courseId}"/>
        <input type="hidden" name="Ajax" value="true"/>
        <div id="paymentmethodcontainer" class="hidden" data-token="{$TOKEN}">
            <input type="hidden" name="hasCredidcart" value="{if $CUSTOMER_CC neq NULL}1{else}0{/if}"/>
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="pull-left"><a href="index.php?module={$RETURN_MODULE}&{$RETUR_ACTION}">Curso: {$courseName}</a>
                    </h1>
                    <div class="action-bar pull-right">
                        <button type="submit" class="btn btn-info">Pagar</button>
                        <a href="index.php?module={$RETURN_MODULE}&{$RETUR_ACTION}" class="btn btn-warning">Cancelar</a>
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
                            <h2 class="pull-left">Información</h2>
                        </header>
                        <div class="main-box-body clearfix">
                            <div class="row">
                                <div class="course-description">
									<table style="border:none;">
										<tr><td style="padding-left: 3em;font-size:1.2em; font-weight: bold">Curso:</td><td style="padding-left: 3em;font-size:1.0em;">{$courseName}</span></td></tr>
										<tr><td style="padding-left: 3em;font-size:1.2em; font-weight: bold">N° de lecciones:</td><td style="padding-left: 3em;font-size:1.0em;">{$courseLessons}</td></tr>
										<tr><td style="padding-left: 3em;font-size:1.2em; font-weight: bold">Inversión:</td><td style="padding-left: 3em;font-size:1.0em;">{number_format($coursePrice, 2, ',', '.')} EUR</td></tr>
										<tr>
										  <td style="padding-left: 3em;font-size:1.2em; font-weight: bold">Usuario para el cual se hace el pago:</td>
										  <td style="padding-left: 3em;font-size:1.0em;padding-top:0.75em;"> 
											<select name="user_name" class="form-control">
											  {foreach from=$usersNotPaid item=user}
												<option value="{$user|escape}"{if $user eq $userName} selected{/if}>{$user|escape}</option>
											  {foreachelse}
												<option>No hay usuarios elegibles</option>
											  {/foreach}
											</select>
										  </td>
										</tr>
									</table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" {if $CUSTOMER_CC neq NULL} style="display: none" {/if}>
                <div class="col-xs-12">
                    <div class="main-box">
                        <header class="title-section main-box-header clearfix">
                            <h2 class="pull-left">Métodos de pago</h2>
                        </header>
                        <div class="main-box-body clearfix">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="cardnumber">Número de tarjeta <span
                                                        class="required">*</span></label>
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
                                            <label for="cardholderName">Titular de tarjeta <span
                                                        class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <div class="input-group">
                                            <input type="text" id="cardholderName" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="expirationmonth">Vencimiento <span
                                                        class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <div class="row input-group">
                                            <div class="col-md-4">
                                                <div id="expirationmonth" class="form-control"></div>
                                                <span class="helper-text"></span>
                                            </div>
                                            <div class="col-md-4">
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
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" {if $CUSTOMER_CC neq NULL} style="display: none" {/if}>
                <div class="col-xs-12">
                    <div class="main-box">
                        <header class="title-section main-box-header clearfix">
                            <h2 class="pull-left">Dirección de facturación</h2>
                        </header>
                        <div class="main-box-body clearfix">
                            {if (!empty ($ADDRESSES))}
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-2">
                                            <div class="label-input">
                                                <label for="addressid">Usar <span class="required">*</span></label>
                                            </div>
                                        </div>

                                        <div class="form-group col-md-10">
                                            <select id="addressid" name="addressid" class="form-control"
                                                    onchange="PaymentCourseUtils.selectBillingAddress (this);">
                                                <option value=""{if (empty ($ADDRESSES))} selected="selected"{/if}>
                                                    Nueva...
                                                </option>

                                                {foreach $ADDRESSES as $addressId => $addressElements}
                                                    <option value="{$addressId}"
                                                            data-firstname="{$addressElements.firstname}"
                                                            data-lastname="{$addressElements.lastname}"
                                                            data-company="{$addressElements.company}"
                                                            data-streetaddress="{$addressElements.streetaddress}"
                                                            data-extendedaddress="{$addressElements.extendedaddress}"
                                                            data-city="{$addressElements.city}"
                                                            data-state="{$addressElements.state}"
                                                            data-zipcode="{$addressElements.zipcode}"
                                                            data-countrycode="{$addressElements.countrycode}">
                                                        {join (', ', array_filter (array (trim ("{$addressElements.firstname} {$addressElements.lastname}"), $addressElements.company, $addressElements.streetaddress, $addressElements.extendedaddress, $addressElements.city, $addressElements.state, $addressElements.zipcode, $addressElements.countryname)))}
                                                    </option>
                                                {/foreach}

                                            </select>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="firstname">Nombre(s) <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <div class="input-group">
                                            <input type="text" id="firstname" name="firstname"
                                                   class="form-control billingaddressfield"/>
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
                                            <input type="text" id="lastname" name="lastname"
                                                   class="form-control billingaddressfield"/>
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
                                            <input type="text" id="company" name="company"
                                                   class="form-control billingaddressfield"/>
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
                                            <input type="text" id="streetaddress" name="streetaddress"
                                                   class="form-control billingaddressfield"/>
                                        </div>
                                        <div class="input-group">
                                            <input type="text" id="extendedaddress" name="extendedaddress"
                                                   class="form-control billingaddressfield" placeholder=""/>
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
                                            <input type="text" id="city" name="city"
                                                   class="form-control billingaddressfield"/>
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
                                            <input type="text" id="state" name="state"
                                                   class="form-control billingaddressfield"/>
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
                                            <input type="text" id="zipcode" name="zipcode"
                                                   class="form-control billingaddressfield"/>
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
                                            <select id="countrycode" name="countrycode"
                                                    class="form-control billingaddressfield">
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
            {if $CUSTOMER_CC neq NULL}
                <div class="row">
                    <div class="col-xs-12">
                        <div class="main-box">
                            <header class="title-section main-box-header clearfix">
                                <h2 class="pull-left">Métodos de pago</h2>
                            </header>
                            <div class="main-box-body clearfix">
                                <p style="margin-bottom: 2em;">La información suministrada de su tarjeta bancaria es
                                    enviada y gestionada directamente por el proveedor de servicios de cobro <a
                                            href="https://www.braintreepayments.com" target="_blank">Braintree</a>, que forma parte de Paypal. Sus datos están seguros, salvaguardados y cumpliendo las más estrictas medidas de seguridad. En ningún caso Platzilla almacena datos bancarios suyos, sólo gestiona su suscripción al sistema.</p>
                                <div class="table-responsive">
                                    <table class="table table-hover no-footer" style="margin-bottom: 0;" width="100%"
                                           cellspacing="0" cellpadding="0" border="0">
                                        <thead>
                                        <tr>
                                            <th aria-controls="table_list">Tarjeta</th>
                                            <th aria-controls="table_list">Titular / Dirección de facturación</th>
                                            <th style="width: 7em;" aria-controls="table_list">Vencimiento</th>
                                            <th aria-controls="table_list">Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {if (!empty ($CUSTOMER_CC))}
                                            {foreach $CUSTOMER_CC as $creditCard}
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
                                                </tr>
                                            {/foreach}
                                        {/if}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="pull-left">&nbsp;</h1>
                    <div class="action-bar pull-right">
                        <button type="submit" class="btn btn-info">Pagar</button>
                        <a href="index.php?module={$RETURN_MODULE}&{$RETUR_ACTION}" class="btn btn-warning">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="https://js.braintreegateway.com/web/3.22.2/js/client.min.js"></script>
    <script type="text/javascript" src="https://js.braintreegateway.com/web/3.22.2/js/hosted-fields.min.js"></script>
    <script type="text/javascript" src="modules/Courses/paymentsCourse.js"></script>
{/strip}