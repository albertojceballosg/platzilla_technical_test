{strip}
    <div id="email-box" class="clearfix" style="padding-bottom: 20px;">
    <table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
        <tbody>
        <tr>
            <td rowspan="2" valign="top">
                <div class="infographic-box" style="width: 30px; padding: 0;"><i
                            class="fa fa-cogs red-bg"></i>
                </div>
            </td>
            <td class="heading2" valign="bottom">
                <ol class="breadcrumb">
                    <li>
                        <a href="index.php?module=Settings&action=index&parenttab=Settings">CONFIGURACIÓN</a>
                    </li>
                    <li class="active" style="text-transform: uppercase">{$MOD['LBL_CONFIG_QUESTIONNAIRE']}</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td class="small" valign="top">{$MOD['LBL_CONFIG_QUESTIONNAIRE_DESCRIPTION']}</td>
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
            <li {if ($SELECTED_TAB eq 'group')} class="active"{/if}>
                <a data-toggle="tab" href="#group-tab">{$MOD['LBL_QUESTIONNAIRE_BASICS']}</a>
            </li>
            <li {if ($SELECTED_TAB eq 'stages')} class="active"{/if}>
                <a data-toggle="tab" href="#stages-tab">{$MOD['LBL_QUESTIONNAIRE_STAGES']}</a>
            </li>
        </ul>
        <div class="main-box-body clearfix">
            <div class="tab-content">
                {* questionnaire group *}
                <div id="group-tab" class="tab-pane fade in{if ($SELECTED_TAB eq 'group')} active{/if}">
                    <header class="main-box-header clearfix text-right">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="pull-left">&nbsp;</div>
                            </div>
                            <div class="col-md-6">
                                <div class="pull-right">
                                    <a href="index.php?module=Settings&action=EditQuestionGroup&parenttab=Settings"
                                       class="btn btn-primary"><i
                                                class="fa fa-plus-circle"></i> {$MOD['LBL_QUESTIONNAIRE_CREATE_BASICS']}</a>
                                </div>
                            </div>
                        </div>

                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-title" style="width: 30%">{$MOD['LBL_QUESTIONNAIRE_BASICS']}</th>
                                <th class="col-to" style="width: 64%">Descripción</th>
                                <th class="col-actions" style="width: 6%">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="group-table">
                            {if ($GROUP neq NULL) }
                                {foreach $GROUP as $group}
                                    <tr id="row-{$group->getId()}">
                                        <td class="col-title">
                                            <a href="index.php?module=Settings&action=EditQuestionGroup&record={$group->getId()}&parenttab=Settings">{$group->getName()}</a>
                                        </td>
                                        <td class="col-from">{$group->getDescription()}</td>
                                        <td class="col-actions">
                                            <form action="index.php" class="form-inline" method="post"
                                                  onclick="return confirm ('¿Estás seguro que quieres eliminar el fundamento de cuestionario seleccionado?');">
                                                <input type="hidden" name="module" value="Settings"/>
                                                <input type="hidden" name="action" value="DeleteQuestionGroup"/>
                                                <input type="hidden" name="record" value="{$group->getId()}"/>
                                                <button type="submit" class="btn btn-danger btn-icon"
                                                        title="Eliminar">
                                                    <i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="3" class="text-center">No hay funfamentos registrados</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                {* Questionnaire stages *}
                <div id="stages-tab"
                     class="tab-pane fade in{if ($SELECTED_TAB eq 'stages')} active{/if}">&nbsp;
                    <header class="main-box-header clearfix text-right">
                        <div class="pull-right">
                            <a href="index.php?module=Settings&action=EditQuestionStage&parenttab=Settings"
                               class="btn btn-primary"><i
                                        class="fa fa-plus-circle"></i> {$MOD['LBL_QUESTIONNAIRE_CREATE_STAGES']}</a>
                        </div>
                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-title" style="width: 25%">{$MOD['LBL_QUESTIONNAIRE_STAGES']}</th>
                                <th class="col-from" style="width: 50%; text-align: left">Descripción</th>
                                <th class="col-actions" style="width: 6%">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="type-table">
                            {if ($STAGES neq NULL) }
                                {foreach $STAGES as $stage}
                                    <tr id="row-type-{$stage->getId()}">
                                        <td class="col-title">
                                            <a href="index.php?module=Settings&action=EditQuestionStage&record={$stage->getId()}&parenttab=Settings">{$stage->getName()}</a>
                                        </td>
                                        <td class="col-from">{$stage->getDescription()}</td>
                                        <td class="col-actions">
                                            <form action="index.php" class="form-inline" method="post"
                                                  onclick="return confirm ('¿Estás seguro que quieres eliminar la etapa seleccionado?');">
                                                <input type="hidden" name="module" value="Settings"/>
                                                <input type="hidden" name="action" value="DeleteQuestionStage"/>
                                                <input type="hidden" name="record" value="{$stage->getId()}"/>
                                                <button type="submit" class="btn btn-danger btn-icon"
                                                        title="Eliminar">
                                                    <i class="fa fa-trash-o"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="3" class="text-center">No hay etapas registradas</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}