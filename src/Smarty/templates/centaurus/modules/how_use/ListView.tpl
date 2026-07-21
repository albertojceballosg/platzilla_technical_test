{strip}
    {*assign var='SELECTED_TAB' value='how_use' *}
    <div id="email-box" class="clearfix" style="padding-bottom: 20px;">
    <table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
        <tbody>
        <tr>
            <td rowspan="2" valign="top">
                <div class="infographic-box" style="width: 30px; padding: 0;"><i
                            class="fa fa-cogs yellow-bg"></i>
                </div>
            </td>
            <td class="heading2" valign="bottom">
                <ol class="breadcrumb">
                    <li>
                        <a href="index.php?module=Settings&action=index&parenttab=Settings">CONFIGURACIÓN</a>
                    </li>
                    <li class="active" style="text-transform: uppercase">{$MOD['how_use']}</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td class="small" valign="top">{$MOD['LBL_HOW_USER_DESCRIPTION']}</td>
        </tr>
        </tbody>
    </table>
    {if (!empty ($MESSAGE))}
        <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
            <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    {/if}
    <div class="main-box clearfix">
        <ul class="nav nav-tabs">
            <li {if ($SELECTED_TAB eq 'how_use')} class="active"{/if}>
                <a data-toggle="tab" href="#how-use-tab">Modos de uso</a>
            </li>
            <li {if ($SELECTED_TAB eq 'profile_use')} class="active"{/if}>
                <a data-toggle="tab" href="#profiles-use-tab">Perfiles de uso</a>
            </li>
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    Variables del perfil <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li {if ($SELECTED_TAB eq 'company_type')} class="active"{/if}>
                        <a data-toggle="tab" href="#company-tab">Tipos de empresas</a>
                    </li>
                    <li {if ($SELECTED_TAB eq 'company_sector')} class="active"{/if}>
                        <a data-toggle="tab" href="#company-sector-tab">Sector</a>
                    </li>
                    <li {if ($SELECTED_TAB eq 'company_phase')} class="active"{/if}>
                        <a data-toggle="tab" href="#company-phase-tab">Fases de desarrollo</a>
                    </li>
                </ul>
            </li>
        </ul>
        <div class="main-box-body clearfix">
            <div class="tab-content">
                {* how to use *}
                <div id="how-use-tab" class="tab-pane fade in{if ($SELECTED_TAB eq 'how_use')} active{/if}">
                    <header class="main-box-header clearfix text-right">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="pull-left">
                                    <select class="form-control" name="formodule" id="formodule" title="El módulo"
                                            onchange="HowToUseUtils.filterModule (this)">
                                        <option value="" selected>Todos los modulos</option>
                                        {foreach $AVAILABLE_MODULES as $module}
                                            <option value="{$module->getName()}"
                                                    {* if $module->getName() eq $tabName}selected{/if *} > {$module->getLabel()}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="pull-right">
                                    <a href="index.php?module=how_use&action=EditView&parenttab=Settings"
                                       class="btn btn-primary"><i
                                                class="fa fa-plus-circle"></i> Crear modo de uso</a>
                                </div>
                            </div>
                        </div>

                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-title" style="width: 30%">Modo de uso</th>
                                <th class="col-from">Descripción</th>
                                <th class="col-to" style="width: 12%">Módulo</th>
                                <th class="col-to" style="width: 10%;text-align: center">Principal</th>
                                <th class="col-to" style="width: 10%">Estado</th>
                                <th class="col-actions" style="width: 6%">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="how-use-table">
                            {if ($HOW_USE neq NULL) }
                                {foreach $HOW_USE as $howToUse}
                                    <tr id="row-{$howToUse->getTabName()}-{$howToUse->getId()}">
                                        <td class="col-title">
                                            <a href="index.php?module=how_use&action=EditView&record={$howToUse->getId()}&parenttab=Settings">{$howToUse->getName()}</a>
                                        </td>
                                        <td class="col-from">{$howToUse->getDescription()}</td>
                                        <td class="col-to">{$howToUse->getTabName()}</td>
                                        <td class="col-to"
                                            style="text-align: center!important;">{if $howToUse->isDefault()}
                                                <i class="fa fa-check-circle-o fa-2x" aria-hidden="true"></i>
                                            {else}
                                                <i class="fa fa-circle-o fa-2x" aria-hidden="true"></i>
                                            {/if}</td>
                                        <td class="col-to">{$MOD[$howToUse->getStatus()]}</td>
                                        <td class="col-actions">
                                            <form action="index.php" class="form-inline" method="post"
                                                  onclick="return confirm ('¿Estás seguro que quieres eliminar el perfil de uso seleccionado?');">
                                                <input type="hidden" name="module" value="how_use"/>
                                                <input type="hidden" name="action" value="Delete"/>
                                                <input type="hidden" name="record" value="{$howToUse->getId()}"/>
                                                <button type="submit" class="btn btn-danger btn-icon"
                                                        title="Eliminar">
                                                    <i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="4" class="text-center">No hay modos de uso registrados</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                {* how to use profile *}
                <div id="profiles-use-tab"
                     class="tab-pane fade in{if ($SELECTED_TAB eq 'profile_use')} active{/if}">&nbsp;
                    <header class="main-box-header clearfix text-right">
                        <div class="pull-right">
                            <a href="index.php?module=how_use&action=EditViewProfile&parenttab=Settings"
                               class="btn btn-primary"><i
                                        class="fa fa-plus-circle"></i> Crear perfil de uso</a>
                        </div>
                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-to" style="width: 12%">Código</th>
                                <th class="col-title" style="width: 30%">Perfil de uso</th>
                                <th class="col-from" style="width: 42%; text-align: left">Descripción</th>
                                <th class="col-to" style="width: 10%">Estado</th>
                                <th class="col-actions" style="width: 6%">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="profile-table">
                            {if ($PROFILES neq NULL) }
                                {foreach $PROFILES as $profile}
                                    <tr id="row-profile-{$profile->getId()}">
                                        <td class="col-from" style="width: 12%">{$profile->getCode()}</td>
                                        <td class="col-title">
                                            <a href="index.php?module=how_use&action=EditViewProfile&record={$profile->getId()}&parenttab=Settings">{$profile->getName()}</a>
                                        </td>
                                        <td class="col-from">{$profile->getDescription()}</td>
                                        <td class="col-to">{$MOD[$profile->getStatus()]}</td>
                                        <td class="col-actions">
                                            <form action="index.php" class="form-inline" method="post"
                                                  onclick="return confirm ('¿Estás seguro que quieres eliminar el perfil de uso seleccionado?');">
                                                <input type="hidden" name="module" value="how_use"/>
                                                <input type="hidden" name="action" value="DeleteProfile"/>
                                                <input type="hidden" name="record" value="{$profile->getId()}"/>
                                                <button type="submit" class="btn btn-danger btn-icon"
                                                        title="Eliminar">
                                                    <i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="5" class="text-center">No hay perfiles de uso registrados</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                {* Company types *}
                <div id="company-tab" class="tab-pane fade in{if ($SELECTED_TAB eq 'company_type')} active{/if}">&nbsp;
                    <header class="main-box-header clearfix text-right">
                        <div class="pull-right">
                            <a href="index.php?module=how_use&action=EditViewType&parenttab=Settings"
                               class="btn btn-primary"><i
                                        class="fa fa-plus-circle"></i> Crear tipo de empresa</a>
                        </div>
                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-title" style="width: 25%">Tipo de empresa</th>
                                <th class="col-from" style="width: 50%; text-align: left">Descripción</th>
                                <th class="col-actions" style="width: 6%">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="type-table">
                            {if ($COMPANY_TYPES neq NULL) }
                                {foreach $COMPANY_TYPES as $type}
                                    <tr id="row-type-{$type->getId()}">
                                        <td class="col-title">
                                            <a href="index.php?module=how_use&action=EditViewType&record={$type->getId()}&parenttab=Settings">{$type->getName()}</a>
                                        </td>
                                        <td class="col-from">{$type->getDescription()}</td>
                                        <td class="col-actions">
                                            <form action="index.php" class="form-inline" method="post"
                                                  onclick="return confirm ('¿Estás seguro que quieres eliminar el tipo de empresa seleccionado?');">
                                                <input type="hidden" name="module" value="how_use"/>
                                                <input type="hidden" name="action" value="DeleteType"/>
                                                <input type="hidden" name="record" value="{$type->getId()}"/>
                                                <button type="submit" class="btn btn-danger btn-icon"
                                                        title="Eliminar">
                                                    <i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="3" class="text-center">No hay tipos de empresas registrados</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                {* Company sector *}
                <div id="company-sector-tab"
                     class="tab-pane fade in{if ($SELECTED_TAB eq 'company_sector')} active{/if}">&nbsp;
                    <header class="main-box-header clearfix text-right">
                        <div class="pull-right">
                            <a href="index.php?module=how_use&action=EditViewSector&parenttab=Settings"
                               class="btn btn-primary"><i
                                        class="fa fa-plus-circle"></i> Crear sector</a>
                        </div>
                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-title" style="width: 30%">Sector</th>
                                <th class="col-from" style="width: 70%; text-align: left">Descripción</th>
                                <th class="col-actions" style="width: 6%">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="sector-table">
                            {if ($COMPANY_SECTOR neq NULL) }
                                {foreach $COMPANY_SECTOR as $sector}
                                    <tr id="row-sector-{$sector->getId()}">
                                        <td class="col-title">
                                            <a href="index.php?module=how_use&action=EditViewSector&record={$sector->getId()}&parenttab=Settings">{$sector->getName()}</a>
                                        </td>
                                        <td class="col-from">{$sector->getDescription()}</td>
                                        <td class="col-actions">
                                            <form action="index.php" class="form-inline" method="post"
                                                  onclick="return confirm ('¿Estás seguro que quieres eliminar el sector seleccionado?');">
                                                <input type="hidden" name="module" value="how_use"/>
                                                <input type="hidden" name="action" value="DeleteSector"/>
                                                <input type="hidden" name="record" value="{$sector->getId()}"/>
                                                <button type="submit" class="btn btn-danger btn-icon"
                                                        title="Eliminar">
                                                    <i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="3" class="text-center">No hay sectores registrados</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                {* Company phases *}
                <div id="company-phase-tab"
                     class="tab-pane fade in{if ($SELECTED_TAB eq 'company_phase')} active{/if}">&nbsp;
                    <header class="main-box-header clearfix text-right">
                        <div class="pull-right">
                            <a href="index.php?module=how_use&action=EditViewPhase&parenttab=Settings"
                               class="btn btn-primary"><i
                                        class="fa fa-plus-circle"></i> Crear fase de desarrollo</a>
                        </div>
                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-title" style="width: 30%">Fase de desarrollo</th>
                                <th class="col-from" style="width: 70%; text-align: left">Descripción</th>
                                <th class="col-actions" style="width: 6%">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="phase-table">
                            {if ($COMPANY_PHASES neq NULL) }
                                {foreach $COMPANY_PHASES as $phase}
                                    <tr id="row-phase-{$phase->getId()}">
                                        <td class="col-title">
                                            <a href="index.php?module=how_use&action=EditViewPhase&record={$phase->getId()}&parenttab=Settings">{$phase->getName()}</a>
                                        </td>
                                        <td class="col-from">{$phase->getDescription()}</td>
                                        <td class="col-actions">
                                            <form action="index.php" class="form-inline" method="post"
                                                  onclick="return confirm ('¿Estás seguro que quieres eliminar la fase seleccionada?');">
                                                <input type="hidden" name="module" value="how_use"/>
                                                <input type="hidden" name="action" value="DeletePhase"/>
                                                <input type="hidden" name="record" value="{$phase->getId()}"/>
                                                <button type="submit" class="btn btn-danger btn-icon"
                                                        title="Eliminar">
                                                    <i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="2" class="text-center">No hay fases de desarrollo registrados</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="modules/how_use/how-use-utils.js"></script>
{/strip}