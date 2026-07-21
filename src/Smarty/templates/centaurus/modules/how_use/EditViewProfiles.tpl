{strip}
    {if ($PROFILE_USE neq NULL)}
        {assign var='id' value=$PROFILE_USE->getId()}
        {assign var='phaseIds' value=$PROFILE_USE->getCompanyPhase()}
        {assign var='sectorIds' value=$PROFILE_USE->getCompanySector()}
        {assign var='typeIds' value=$PROFILE_USE->getCompanyType()}
        {assign var='description' value=$PROFILE_USE->getDescription()}
        {assign var='name' value=$PROFILE_USE->getName()}
        {assign var='status' value=$PROFILE_USE->getStatus()}
    {else}
        {assign var='id' value=null}
        {assign var='phaseIds' value=array()}
        {assign var='sectorIds' value=array()}
        {assign var='typeIds' value=array()}
        {assign var='description' value=null}
        {assign var='name' value=null}
        {assign var='status' value=null}

    {/if}
    <style>
        .row-how-use {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px
        }

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack: center !important;
            justify-content: center !important
        }

        .no-gutters > .col,
        .no-gutters > [class*=col-] {
            padding-right: 1px;
            padding-left: 1px;
        }
    </style>
    <link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="modules/News/News.css"/>
    <form class="form-horizontal" name="howToUse-form" role="form" method="post" action="index.php"
          onsubmit="return HowToUseUtils.validateProfileForm (this);">
        <input type="hidden" name="module" value="how_use"/>
        <input type="hidden" name="action" value="SaveProfile"/>
        <input type="hidden" name="record" value="{$id}"/>
        <input type="hidden" name="return_action" value="{$RETURN_ACTION}"/>
        <input type="hidden" name="return_module" value="{$RETURN_MODULE}"/>
        {* <input type="hidden" name="Ajax" value="true"/> *}
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=how_use&action=ListView&tab=profile_use&parenttab=Settings">{$MOD['how_use']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=how_use&action=ListView&tab=profile_use&parenttab=Settings"
                       class="btn btn-warning"
                       style="margin-left: 5px;">Cancelar</a>
                </div>
            </div>
        </div>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Perfil de uso: Información general</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            {* Profile use Name *}
                            <div class="form-group">
                                <label for="profile_to_use_name" class="col-md-3 control-label">Nombre:</label>
                                <div id="pu-div-name" class="col-md-7">
                                    <input type="text" class="form-control" id="profile_to_use_name" name="name"
                                           value="{$name}"
                                           title="El nombre del modo"
                                           placeholder="Nombre del perfil de uso">
                                    <span id="pu-name" class="help-block"></span>
                                </div>
                            </div>
                            {* Profile description *}
                            <div class="form-group">
                                <label for="profile_to_use_description"
                                       class="col-md-3 control-label">Descripción:</label>
                                <div id="pu-div-description" class="col-md-7">
                                    <textarea class="form-control" name="description" id="profile_to_use_description"
                                              rows="3"
                                              placeholder="Breve descripción del perfil de uso">{$description}</textarea>
                                    <span id="pu-description" class="help-block"></span>
                                </div>
                            </div>
                            {* company Sector *}
                            <div class="form-group">
                                <label for="profile_to_use_sector" class="col-md-3 control-label">Sector:</label>
                                <div id="pu-div-formodule" class="col-md-7">
                                    <select multiple class="form-control" name="sector[]" id="sector"
                                            title="El Sector de la económia">
                                        {*<option value="" selected>Seleccionar</option>*}
                                        {foreach $COMPANY_SECTOR as $sector}
                                            <option value="{$sector->getId()}"
                                                    {if in_array($sector->getId(), $sectorIds)}selected{/if} > {$sector->getName()}</option>
                                        {/foreach}
                                    </select>
                                    <span id="pu-formodule" class="help-block"></span>
                                </div>
                            </div>
                            {* company type *}
                            <div class="form-group">
                                <label for="profile_to_use_type" class="col-md-3 control-label">Tipo de empresa:</label>
                                <div id="pu-div-formodule" class="col-md-7">
                                    <select multiple class="form-control" name="type[]" id="profile_type"
                                            title="El tipo de empresa">
                                        {*<option value="" selected>Seleccionar</option>*}
                                        {if $COMPANY_TYPES neq NULL}
                                            {foreach $COMPANY_TYPES as $type}
                                                <option value="{$type->getId()}"
                                                        {if in_array($type->getId(), $typeIds)}selected {/if}>
                                                    {$type->getName()}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                    <span id="pu-formodule" class="help-block"></span>
                                </div>
                            </div>
                            {* company phase *}
                            <div class="form-group">
                                <label for="profile_to_use_phase" class="col-md-3 control-label">Fase de
                                    &nbsp;desarrollo:</label>
                                <div id="pu-div-formodule" class="col-md-7">
                                    <select multiple class="form-control" name="phase[]" id="phase"
                                            title="La fase de desarrollo">
                                        {foreach $COMPANY_PHASES as $phase}
                                            <option value="{$phase->getId()}"
                                                    {if in_array($phase->getId(), $phaseIds)}selected{/if} > {$phase->getName()}</option>
                                        {/foreach}
                                    </select>
                                    <span id="pu-formodule" class="help-block"></span>
                                </div>
                            </div>
                            {* status *}
                            <div class="form-group">
                                <label for="profile_to_use_staus" class="col-md-3 control-label">Estado:</label>
                                <div class="col-md-7">
                                    <select class="form-control" name="status" id="status_profile">
                                        {foreach $AVAILABLE_STATUS as $key => $value}
                                            <option value="{$key}"
                                                    {if $key eq $status}selected{/if} > {$value}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <header class="main-box-header clearfix">
                            <h2 class="pull-left" style="margin-left: -20px">Modos de uso</h2>
                        </header>
                        <div class="row-how-use justify-content-center" id="profile-rows">
                            {* row titles *}
                            <div class="col-md-11" style="margin-top: 20px; margin-left: 20px">
                                <div class="row-how-use justify-content-center">
                                    <div class="col-md-5"><span
                                                style="text-align: center; font-size: small">Modulo</span></div>
                                    <div class="col-md-1"></div>
                                    <div class="col-md-5"><span
                                                style="text-align: center; font-size: small">Modo de uso</span></div>

                                    <div class="col-md-1"><span
                                                style="text-align: center; font-size: small">Acción</span></div>
                                </div>
                            </div>
                            {* /row titles *}
                            {* row number 0 *}
                            <div id="main-profile-row" class="col-md-11" style="margin-top: 5px;margin-left: 15px">
                                <div id="profile-use-row-0" class="row-how-use justify-content-center how-use-row">
                                    <div id="pu-div-profiletab-0" class="col-md-5">
                                        <select class="form-control" name="profiletab[]" title="El módulo"
                                                onchange="HowToUseUtils.selectedProfileTab(this)">
                                            <option value="" selected>Seleccionar</option>
                                            {foreach $AVAILABLE_MODULES as $module}
                                                <option value="{$module->getName()}"
                                                        {if $module->getName() eq $tabName}selected{/if} > {$module->getLabel()}</option>
                                            {/foreach}
                                        </select>
                                        <span id="pu-profiletab-0" class="help-block"></span>
                                    </div>
                                    <div class="col-md-1">&nbsp;</div>
                                    <div id="pu-div-defaultvie-0" class="col-md-5">
                                        <select class="form-control" name="howuse[]"
                                                title="Seleccionar un modo de uso">
                                            <option value="" selected>Seleccionar</option>
                                            {if $HOW_USE neq NULL}
                                                {foreach $HOW_USE as $howUse}
                                                    <option value="{$howUse->getHowUseName()}" class="hide"
                                                            data-module="{$howUse->getTabName()}"> {$howUse->getName()}</option>
                                                {/foreach}
                                            {/if}
                                        </select>
                                        <span id="pu-howuse" class="help-block"></span>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-icon hidden"
                                                onclick="HowToUseUtils.deleteRowProfile(this)"
                                                title="Eliminar">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 20px">
                            <div class="col-md-12">
                                <div class="center-block" style="text-align: center">
                                    <button type="button" class="btn btn-success btn-icon"
                                            onclick="HowToUseUtils.addRowProfile(this)"
                                            title="Incluir vistas">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="modules/how_use/how-use-utils.js"></script>
    <script type="text/html" id="profile-use-template">
        <div class="col-md-11" style="margin-top: 20px; margin-left: 15px">
            <div id="profile-use-row-__ID__" class="row-how-use justify-content-center how-use-row">
                <div id="pu-div-profiletab-__ID__" class="col-md-5">
                    <select class="form-control" name="profiletab[]"
                            onchange="HowToUseUtils.selectedProfileTab(this)"
                            title="El modulo">
                        <option value="">Seleccionar</option>
                        {foreach $AVAILABLE_MODULES as $module}
                            <option value="{$module->getName()}"> {$module->getLabel()}</option>
                        {/foreach}
                    </select>
                    <span id="pu-profiletab-__ID__" class="help-block"></span>
                </div>
                <div class="col-md-1">&nbsp;</div>
                <div id="pu-div-howuse-__ID__" class="col-md-5">
                    <select class="form-control" name="howuse[]" title="El Modo de uso">
                        <option value="">Seleccionar</option>
                        {if $HOW_USE neq NULL}
                            {foreach $HOW_USE as $howUse}
                                <option value="{$howUse->getHowUseName()}" class="hide"
                                        data-module="{$howUse->getTabName()}"> {$howUse->getName()}</option>
                            {/foreach}
                        {/if}
                    </select>
                    <span id="pu-howuse-__ID__" class="help-block"></span>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-icon"
                            onclick="HowToUseUtils.deleteRowProfile(this)"
                            title="Eliminar">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </div>
    </script>
    {if $PROFILES_HOW_USE neq NULL}
        <script type="text/javascript">
            {literal}
            HowToUseUtils.reloadProfile({/literal}{$PROFILES_HOW_USE|json_encode}{literal});
            {/literal}
        </script>
    {/if}
{/strip}