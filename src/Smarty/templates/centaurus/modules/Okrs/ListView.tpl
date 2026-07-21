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
                    <li class="active" style="text-transform: uppercase">{$MOD['okrs']}</li>
                    <li>
                        <a href="#" onclick="OKRsUtils.openModalWizard()">Prueba Asistente</a>
                    </li>
                </ol>
            </td>
        </tr>
        <tr>
            <td class="small" valign="top">{$MOD['LBL_OKRS_DESCRIPTION']}</td>
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
            <li {if ($SELECTED_TAB eq 'objectives')} class="active"{/if}>
                <a data-toggle="tab" href="#objectives-tab">{$MOD['TAB_OBJETIVES']}</a>
            </li>
            <li {if ($SELECTED_TAB eq 'key_results')} class="active"{/if}>
                <a data-toggle="tab" href="#key-results-tab">{$MOD['TAB_KEY_RESULT']}</a>
            </li>
        </ul>
        <div class="main-box-body clearfix">
            <div class="tab-content">
                {* Objectives *}
                <div id="objectives-tab"
                     class="tab-pane fade in{if ($SELECTED_TAB eq 'objectives')} active{/if}">
                    <header class="main-box-header clearfix text-right">
                        <div class="pull-left">
                            <select class="form-control" name="formodule" id="formodule" title="Objectivo"
                                    onchange="OKRsUtils.filterByArea (this)">
                                <option value="" selected>Todos las áreas</option>
                                {if $COMPANY_AREAS neq NULL}
                                    {foreach $COMPANY_AREAS as $areaValue}
                                        <option value="{$areaValue}"> {$MOD[$areaValue]}</option>
                                    {/foreach}
                                {/if}
                            </select>
                        </div>
                        <div class="pull-right">
                            <a href="index.php?module=okrs&action=EditViewObjective&parenttab=Settings"
                               class="btn btn-primary">
                                <i class="fa fa-plus-circle"></i>&nbsp;{$MOD['BTN_CREATE_OBJECTIVE']}
                            </a>
                        </div>
                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-title" style="width: 25%">{$MOD['TITLE_TO_DO']}</th>
                                <th class="col-to" style="width: 25%">{$MOD['TITLE_TYPE']}</th>
                                <th class="col-to" style="width: 20%">{$MOD['TITLE_PHASE']}</th>
                                <th class="col-from"style="width: 10%">{$MOD['TITLE_AREA']}</th>
                                <th class="col-to" style="width: 5%">{$MOD['TITLE_FREQUENCY']}</th>
                                <th class="col-to" style="width: 5%">{$MOD['TITLE_STATUS']}</th>
                                <th class="col-actions" style="width: 5%">{$MOD['TITLE_ON_BORDING']}</th>
                                <th class="col-actions" style="width: 5%">{$MOD['TITLE_ACCION']}</th>
                            </tr>
                            </thead>
                            <tbody id="objectives-table">
                            {if ($OBJECTIVES neq NULL)}
                                {foreach $OBJECTIVES as $objetive}
                                    <tr id="row-objetive-{$objetive->getCompanyArea()}-{$objetive->getId()}">
                                        <td class="col-title">
                                            <a href="index.php?module=okrs&action=EditViewObjective&record={$objetive->getId()}&parenttab=Settings">{$objetive->getToDo()}</a>
                                        </td>
                                        <td class="col-from">{$objetive->getListTypes()}</td>
                                        <td class="col-from">{$objetive->getListPhases()}</td>
                                        <td class="col-from">{$MOD[$objetive->getCompanyArea()]}</td>
                                        <td class="col-to">{$MOD[$objetive->getFrequency()]}</td>
                                        <td class="col-to">{$MOD[$objetive->getStatus()]}</td>
                                        <td class="col-to">{$MOD[$objetive->isOnBoarding()]}</td>
                                        <td class="col-actions">
                                            <form action="index.php" class="form-inline" method="post"
                                                  onclick="return confirm ('{$MOD['CONFIRM_DELETE_OBJECTIVE']}');">
                                                <input type="hidden" name="module" value="okrs"/>
                                                <input type="hidden" name="action" value="DeleteObjective"/>
                                                <input type="hidden" name="record" value="{$objetive->getId()}"/>
                                                <button type="submit" class="btn btn-danger btn-icon"
                                                        title="Eliminar">
                                                    <i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="5" class="text-center">No hay objectivos registrados</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                {* key results *}
                <div id="key-results-tab"
                     class="tab-pane fade in{if ($SELECTED_TAB eq 'key_results')} active{/if}">
                    <header class="main-box-header clearfix text-right">
                        <div class="pull-left">
                            <select class="form-control" name="formodule" id="formodule" title="Objectivo"
                                    onchange="OKRsUtils.filterByObjective (this)">
                                <option value="" selected>Todos los objetivos</option>
                                {if $ARRAY_OBJECTIVES neq NULL}
                                {foreach $ARRAY_OBJECTIVES as $key => $objective}
                                    <option value="{$key}"> {$objective}</option>
                                {/foreach}
                                {/if}
                            </select>
                        </div>
                        <div class="pull-right">
                            <a {if ($ARRAY_OBJECTIVES neq NULL)}href="index.php?module=okrs&action=EditViewKeyResult&parenttab=Settings"{/if}
                               class="btn btn-primary">
                                <i class="fa fa-plus-circle"></i>&nbsp;{$MOD['BTN_CREATE_KEY_RESULT']}
                            </a>
                        </div>
                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-to" style="width: 30%">{$MOD['TITLE_OBJETIVE_ID']}</th>
                                <th class="col-from" style="width: 30%; text-align: left">{$MOD['TITLE_DECRIPCION']}</th>
                                <th class="col-title" style="width: 12%">{$MOD['TITLE_GOALS_VALUES']}</th>
                                <th class="col-title" style="width: 12%">{$MOD['TITLE_FREQUENCY']}</th>
                                <th class="col-to" style="width: 10%">{$MOD['TITLE_STATUS']}</th>
                                <th class="col-actions" style="width: 6%">{$MOD['TITLE_ACCION']}</th>
                            </tr>
                            </thead>
                            <tbody id="key-results-table">
                            {if ($KEY_RESULTS neq NULL) }
                                {foreach $KEY_RESULTS as $keyResult}
                                    <tr id="row-key-{$keyResult->getObjectiveId()}-{$keyResult->getId()}">
                                        <td class="col-from" style="width: 12%">
                                            <a href="index.php?module=okrs&action=EditViewObjective&record={$keyResult->getObjectiveId()}&tab=key_results&parenttab=Settings">{$ARRAY_OBJECTIVES[$keyResult->getObjectiveId()]}</a>
                                        </td>
                                        <td class="col-title">
                                            <a href="index.php?module=okrs&action=EditViewKeyResult&record={$keyResult->getId()}&tab=key_results&parenttab=Settings">{$keyResult->getDescription()}</a>
                                        </td>
                                        <td class="col-from">{$keyResult->getGoalValue()}</td>
                                        <td class="col-to">{$MOD[$keyResult->getFrequency()]}</td>
                                        <td class="col-to">{$MOD[$keyResult->getStatus()]}</td>
                                        <td class="col-actions">
                                            <form action="index.php" class="form-inline" method="post"
                                                  onclick="return confirm ('{$MOD['CONFIRM_DELETE_KEY_RESULT']}');">
                                                <input type="hidden" name="module" value="okrs"/>
                                                <input type="hidden" name="action" value="DeleteKeyResult"/>
                                                <input type="hidden" name="record" value="{$keyResult->getId()}"/>
                                                <button type="submit" class="btn btn-danger btn-icon"
                                                        title="Eliminar">
                                                    <i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="6" class="text-center">No hay resultados claves registrados</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file="modules/Okrs/Wizard/OKRsWizard.tpl"}
    <script type="text/javascript" src="/modules/okrs/okrs-utils.js"></script>
{/strip}