{strip}
    <style type="text/css">
        {literal}
        label {
            font-size: 1.11em;
            font-weight: 300;
        }

        .btn {
            margin-left: 5px;
        }

        .radio-inline {
            font-size: 1em;
            font-weight: 300;
        }

        .required {
            color: #FF0000;
        }

        .image-container {
            border: 1px dashed;
            padding: 5px;
            position: relative;
            text-align: center;
        }

        .image-container > .btn {
            background-color: transparent;
            border: 0;
            bottom: 5px;
            line-height: 1;
            right: 0;
            padding: 0 5px 2px 5px;
            position: absolute;
            text-transform: uppercase;
            z-index: 1;
        }

        .image-container > .image {
            display: inline-block;
        }

        .image-container > .image > .image-data {
            margin: 0 auto;
        }

        .image-container > input[type="file"] {
            bottom: 0;
            cursor: pointer;
            left: 0;
            opacity: 0;
            position: absolute;
            top: 0;
            width: 100%;
        }

        .info {
            display: inline-block;
            position: relative;
            z-index: 1;
        }

        .info .infotext {
            background-color: #555;
            border-radius: 6px;
            color: #fff;
            left: 480%;
            margin-left: -60px;
            opacity: 0;
            padding: 5px 0;
            position: absolute;
            text-align: center;
            top: -5px;
            transition: opacity 1s;
            visibility: hidden;
            width: 300px;
            z-index: 1;
        }

        .info:hover .infotext {
            opacity: 1;
            visibility: visible;
            z-index: 1;
        }

        .form-group {
            z-index: 0;
        }

        /* Large desktops and laptops. */
        @media (min-width: 1200px) {
            .info .infotext {
                left: 480%;
                width: 300px;
            }
        }

        /* Landscape tablets and medium desktops. */
        @media (min-width: 992px) and (max-width: 1199px) {
            .info .infotext {
                left: 480%;
                width: 300px;
            }
        }

        /* Portrait tablets and small desktops. */
        @media (min-width: 768px) and (max-width: 991px) {
            .info .infotext {
                left: 480%;
                width: 300px;
            }
        }

        /* Landscape phones and portrait tablets. */
        @media (min-width: 481px) and (max-width: 767px) {
            .info .infotext {
                left: 560%;
                width: 250px;
            }
        }

        /* Portrait phones and smaller. */
        @media (max-width: 480px) {
            .info .infotext {
                left: 560%;
                width: 250px;
            }
        }

        {/literal}
    </style>
    <form method="post" action="index.php" name="organizationprofile"
          onsubmit="return OrganizationUtils.validateOrganizationProfile ();">
        <input type="hidden" name="module" value="Home"/>
        <input type="hidden" name="action" value="SaveOrganizationProfile"/>
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left"><a href="index.php?module=Home&action=CustomerView">Perfil de la organización</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=Home&action=ViewSubscriptionDetails" class="btn btn-warning">Cancelar</a>
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
                        <div class="col-xs-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="organization-name">Nombre <span
                                                        class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="organization-name" name="organizationname"
                                                   value="{$ORGANIZATION.organizationname}" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="cif">Identificación fiscal <span
                                                        class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="cif" name="cif" value="{$ORGANIZATION.cif}"
                                                   class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="currency_code">Moneda <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <select id="currency_code" name="currencycode" class="form-control">
                                                <option value=""></option>
                                                {foreach $AVAILABLE_CURRENCIES as $availableCurrency}
                                                    <option value="{$availableCurrency.currency_code}"{if ($availableCurrency.currency_code == $ORGANIZATION_CURRENCY.currency_code)} selected="selected"{/if}>{$availableCurrency.currency_name}
                                                        ({$availableCurrency.currency_symbol})
                                                    </option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="">Logo</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="image-container">
                                            <button type="button" class="btn btn-close"
                                                    onclick="OrganizationUtils.restoreImage (this);">X
                                            </button>
                                            <figure class="image">
                                                <img src="{$ORGANIZATION.organization_logopath}/{$ORGANIZATION.logoname}?{$smarty.now}"
                                                     class="img-responsive image-data"
                                                     data-original-src="{$ORGANIZATION.organization_logopath}/{$ORGANIZATION.logoname}"/>
                                                <figcaption class="text-center image-name" data-original-name="Logo">
                                                    Logo
                                                </figcaption>
                                            </figure>
                                            <input type="file"
                                                   onchange="OrganizationUtils.changeImage (event || window.event);"/>
                                            <input type="hidden" name="logocontents"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="default_module">Día inicio de semana</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <select id="start_day_week" name="start_day_week" class="form-control">
                                                <option value=""></option>
                                                {foreach $DAY_OF_WEEK as $day => $dayEs}
                                                    <option value="{$day}"{if ($ORGANIZATION.start_day_week == $day)} selected="selected"{/if}>{$dayEs}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="default_module">Iniciar en</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <select id="default_module" name="default_module" class="form-control">
                                                <option value=""></option>
                                                {foreach $AVAILABLE_DEFAULT_MODULES as $moduleName => $moduleLabel}
                                                    <option value="{$moduleName}"{if ($ORGANIZATION.default_module == $moduleName)} selected="selected"{/if}>{$moduleLabel}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="website">Sitio web</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="website" name="website"
                                                   value="{$ORGANIZATION.website}" class="form-control"/>
                                        </div>
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
                        <h2 class="pull-left">Dirección fiscal</h2>
                    </header>
                    <div class="main-box-body clearfix">
                        <div class="col-xs-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="address">Dirección <span class="required">*</span></label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <textarea id="address" name="address" class="form-control"
                                                      rows="3">{$ORGANIZATION.address}</textarea>
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
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="city" name="city" value="{$ORGANIZATION.city}"
                                                   class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="state">Provincia</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="state" name="state" value="{$ORGANIZATION.state}"
                                                   class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="zipcode">Código postal</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <input type="text" id="zipcode" name="zipcode" value="{$ORGANIZATION.code}"
                                                   class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="country">País</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <div class="input-group" style="width: 100%;">
                                            <select id="country" name="country" class="form-control">
                                                <option value=""></option>
                                                {foreach $AVAILABLE_COUNTRIES as $country}
                                                    <option value="{$country.pais}"{if ($country.pais == $ORGANIZATION.country)} selected="selected"{/if}>{$country.pais}</option>
                                                {/foreach}
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
    <script type="text/javascript" src="modules/Home/organization-utils.js"></script>
{/strip}
