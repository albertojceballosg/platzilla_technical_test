{strip}
    <div id="email-box" class="clearfix">
        <table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
            <tbody>
            <tr>
                <td rowspan="2" valign="top">
                    <div class="infographic-box" style="width: 30px; padding: 0;">
                        <i class="fa fa-support red-bg"></i>
                    </div>
                </td>
                <td class="heading2" valign="bottom">
                    <ol class="breadcrumb">
                        <li>
                            <a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
                        </li>
                        <li class="active">GESTOR DE AYUDAS</li>
                    </ol>
                </td>
            </tr>
            <tr>
                <td class="small" valign="top">{$MOD.LBL_CONFIG_HELP_DESCRIPTION}</td>
            </tr>
            </tbody>
        </table>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
        <div class="main-box clearfix">
            <div class="tabs-wrapper">
                <ul class="nav nav-tabs">
                    <li{if ((empty ($SELECTED_TAB)) || ($SELECTED_TAB == 'tips') || (!in_array ($SELECTED_TAB, array ('questions', 'tips', 'tutorials', 'usecases', 'configuration', 'fields', 'how_to'))))} class="active"{/if}>
                        <a href="#tips" data-toggle="tab">Tips</a></li>
                    <li{if ($SELECTED_TAB == 'tutorials')} class="active"{/if}><a href="#tutorials" data-toggle="tab">Tutoriales</a>
                    </li>
                    <li{if ($SELECTED_TAB == 'usecases')} class="active"{/if}><a href="#usecases" data-toggle="tab">Casos
                            &nbsp;de uso</a></li>
                    <li{if ($SELECTED_TAB == 'questions')} class="active"{/if}><a href="#questions" data-toggle="tab">Preguntas
                            &nbsp;frecuentes</a></li>
                    <li{if ($SELECTED_TAB == 'configuration')} class="active"{/if}><a href="#configuration"
                                                                                      data-toggle="tab">Configuración</a>
                    </li>
                    <li{if ($SELECTED_TAB == 'fields')} class="active"{/if}><a href="#help-fields" data-toggle="tab">Ayuda
                            &nbsp;en Campos</a></li>
                    <li{if ($SELECTED_TAB == 'how_to')} class="active"{/if}><a href="#help-how_to" data-toggle="tab">HowTo</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div id="tips"
                         class="tab-pane fade{if ((empty ($SELECTED_TAB)) || ($SELECTED_TAB == 'tips') || (!in_array ($SELECTED_TAB, array ('questions', 'tips', 'tutorials', 'usecases', 'configuration', 'fields', 'how_to'))))} in active{/if}">
                        <div class="col-xs-12 text-right" style="margin-top: 1em;">
                            <a href="index.php?module=Settings&action=HelpSettingsTipEditView&parenttab=Settings"
                               class="btn btn-primary"><i class="fa fa-plus-circle"></i> Nuevo tip</a>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table width="100%" cellpadding="5" cellspacing="0" class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-left">Título</th>
                                    <th class="text-left" width="9%">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if (!empty ($TIPS.items))}
                                    {foreach $TIPS.items as $tip}
                                        <tr>
                                            <td>
                                                <a href="index.php?module=Settings&action=HelpSettingsTipEditView&record={$tip.id}&parenttab=Settings">{$tip.title}</a>
                                                <p style="font-size: 0.85em;">{$tip.description}</p>
                                            </td>
                                            <td>
                                                <form method="post" action="index.php" style="display: inline;">
                                                    <input type="hidden" name="module" value="Settings"/>
                                                    <input type="hidden" name="action" value="DeleteHelpTip"/>
                                                    <input type="hidden" name="record" value="{$tip.id}"/>
                                                    <input type="hidden" name="Ajax" value="true"/>
                                                    <button type="submit" class="btn btn-danger"
                                                            onclick="return confirm ('¿Estás seguro de eliminar el elemento seleccionado?');"
                                                            title="{$APP.LBL_DELETE_BUTTON_LABEL}"
                                                            style="margin-left: 0.25em;"><i class="fa fa-trash-o"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="2">No se encuentran tips registrados</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="tutorials" class="tab-pane fade{if ($SELECTED_TAB == 'tutorials')} in active{/if}">
                        <div class="col-xs-12 text-right" style="margin-top: 1em;">
                            <a href="index.php?module=Settings&action=HelpSettingsTutorialEditView&parenttab=Settings"
                               class="btn btn-primary"><i class="fa fa-plus-circle"></i> Nuevo tutorial</a>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table width="100%" cellpadding="5" cellspacing="0" class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-left">Título</th>
                                    <th class="text-left">Enlace</th>
                                    <th class="text-left" width="9%">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if (!empty ($TUTORIALS))}
                                    {foreach $TUTORIALS.items as $tutorial}
                                        <tr>
                                            <td>
                                                <a href="index.php?module=Settings&action=HelpSettingsTutorialEditView&record={$tutorial.id}&parenttab=Settings">{$tutorial.title}</a>
                                            </td>
                                            <td>
                                                <a href="{$tutorial.url}" target="_blank">{$tutorial.url}</a>
                                            </td>
                                            <td>
                                                <form method="post" action="index.php" style="display: inline;">
                                                    <input type="hidden" name="module" value="Settings"/>
                                                    <input type="hidden" name="action" value="DeleteHelpTutorial"/>
                                                    <input type="hidden" name="record" value="{$tutorial.id}"/>
                                                    <input type="hidden" name="Ajax" value="true"/>
                                                    <button type="submit" class="btn btn-danger"
                                                            onclick="return confirm ('¿Estás seguro de eliminar el elemento seleccionado?');"
                                                            title="{$APP.LBL_DELETE_BUTTON_LABEL}"
                                                            style="margin-left: 0.25em;"><i class="fa fa-trash-o"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="3">No se encuentran tutoriales registrados</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="usecases" class="tab-pane fade{if ($SELECTED_TAB == 'usecases')} in active{/if}">
                        <div class="col-xs-12 text-right" style="margin-top: 1em;">
                            <a href="index.php?module=Settings&action=HelpSettingsUseCaseEditView&parenttab=Settings"
                               class="btn btn-primary"><i class="fa fa-plus-circle"></i> Nuevo caso de uso</a>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table width="100%" cellpadding="5" cellspacing="0" class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-left">Título</th>
                                    <th class="text-left">Enlace</th>
                                    <th class="text-left" width="9%">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if (!empty ($USE_CASES.items))}
                                    {foreach $USE_CASES.items as $useCase}
                                        <tr>
                                            <td>
                                                <a href="index.php?module=Settings&action=HelpSettingsUseCaseEditView&record={$useCase.id}&parenttab=Settings">{$useCase.title}</a>
                                            </td>
                                            <td>
                                                <a href="{$useCase.url}" target="_blank">{$useCase.url}</a>
                                            </td>
                                            <td>
                                                <form method="post" action="index.php" style="display: inline;">
                                                    <input type="hidden" name="module" value="Settings"/>
                                                    <input type="hidden" name="action" value="DeleteHelpUseCase"/>
                                                    <input type="hidden" name="record" value="{$useCase.id}"/>
                                                    <input type="hidden" name="Ajax" value="true"/>
                                                    <button type="submit" class="btn btn-danger"
                                                            onclick="return confirm ('¿Estás seguro de eliminar el elemento seleccionado?');"
                                                            title="{$APP.LBL_DELETE_BUTTON_LABEL}"
                                                            style="margin-left: 0.25em;"><i class="fa fa-trash-o"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="3">No se encuentran casos de uso registrados</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="questions" class="tab-pane fade{if ($SELECTED_TAB == 'questions')} in active{/if}">
                        <div class="col-xs-12 text-right" style="margin-top: 1em;">
                            <a href="index.php?module=Settings&action=HelpSettingsQuestionEditView&parenttab=Settings"
                               class="btn btn-primary"><i class="fa fa-plus-circle"></i> Nueva pregunta</a>
                        </div>
                        {if (!empty ($QUESTIONS))}
                            {foreach $QUESTIONS.items as $applicationCode => $applicationQuestions}
                                <h4 class="col-xs-12">Aplicación {$APPLICATIONS[$applicationCode].app_name}</h4>
                                <div class="col-xs-12 table-responsive">
                                    <table width="100%" cellpadding="5" cellspacing="0"
                                           class="table table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th class="text-left">Pregunta</th>
                                            <th class="text-left" width="9%">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $applicationQuestions as $question}
                                            <tr>
                                                <td>
                                                    <a href="index.php?module=Settings&action=HelpSettingsQuestionEditView&record={$question.id}&parenttab=Settings">{$question.title}</a>
                                                    <p style="font-size: 0.85em;">{$question.description}</p>
                                                </td>
                                                <td>
                                                    <form method="post" action="index.php" style="display: inline;">
                                                        <input type="hidden" name="module" value="Settings"/>
                                                        <input type="hidden" name="action" value="DeleteHelpQuestion"/>
                                                        <input type="hidden" name="record" value="{$question.id}"/>
                                                        <input type="hidden" name="Ajax" value="true"/>
                                                        <button type="submit" class="btn btn-danger"
                                                                onclick="return confirm ('¿Estás seguro de eliminar el elemento seleccionado?');"
                                                                title="{$APP.LBL_DELETE_BUTTON_LABEL}"
                                                                style="margin-left: 0.25em;"><i
                                                                    class="fa fa-trash-o"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            {/foreach}
                        {/if}
                    </div>
                    <div id="configuration" class="tab-pane fade{if ($SELECTED_TAB == 'configuration')} in active{/if}">
                        <div class="col-xs-12 text-right" style="margin-top: 1em;">
                            <a href="index.php?module=Settings&action=HelpSettingsConfigurationEditView&parenttab=Settings"
                               class="btn btn-primary"><i class="fa fa-plus-circle"></i> Nuevo tutorial</a>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table width="100%" cellpadding="5" cellspacing="0" class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-left">Título</th>
                                    <th class="text-left">Enlace</th>
                                    <th class="text-left" width="9%">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {if (!empty ($CONFIGURATION))}
                                    {foreach $CONFIGURATION as $tutorial}
                                        <tr>
                                            <td>
                                                <a href="index.php?module=Settings&action=HelpSettingsConfigurationEditView&record={$tutorial.tutorialid}&parenttab=Settings">{$tutorial.title}</a>
                                            </td>
                                            <td>
                                                <a href="{$tutorial.url}" target="_blank">{$tutorial.url}</a>
                                            </td>
                                            <td>
                                                <form method="post" action="index.php" style="display: inline;">
                                                    <input type="hidden" name="module" value="Settings"/>
                                                    <input type="hidden" name="action" value="DeleteHelpTutorial"/>
                                                    <input type="hidden" name="record" value="{$tutorial.id}"/>
                                                    <input type="hidden" name="Ajax" value="true"/>
                                                    <button type="submit" class="btn btn-danger"
                                                            onclick="return confirm ('¿Estás seguro de eliminar el elemento seleccionado?');"
                                                            title="{$APP.LBL_DELETE_BUTTON_LABEL}"
                                                            style="margin-left: 0.25em;"><i class="fa fa-trash-o"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="3">No se encuentran tutoriales registrados</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {* HELP FIELDS *}
                    <div id="help-fields" class="tab-pane fade{if ($SELECTED_TAB == 'fields')} in active{/if}">
                        <div class="col-xs-12">
                            <div class="row">
                                <div class="col-xs-4 text-left" style="margin-top: 1em;">
                                    <select class="form-control" id="modules" title="Módulos"
                                            onchange="HelpSysPanelUtils.filterModule(this)">
                                        <option value="" selected>Todos los módulos</option>
                                        {if ($AVAILABLE_MODULES neq NULL)}
                                            {foreach $AVAILABLE_MODULES as $moduleName}
                                                <option value="{$moduleName->getName()}">{$moduleName->getLabel()}
                                                    ({$moduleName->getName()})
                                                </option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                </div>
                                <div class="col-xs-8 text-right" style="margin-top: 1em;">
                                    <a href="index.php?module=Settings&action=HelpSettingsFieldEditView&parenttab=Settings"
                                       class="btn btn-primary"><i class="fa fa-plus-circle"></i> Ayuda en campo </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table width="100%" cellpadding="5" cellspacing="0" class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-left">Título</th>
                                    <th class="text-left">Campo</th>
                                    <th class="text-left">Módulo</th>
                                    <th class="text-center" style="width: 18%;">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
                                </tr>
                                </thead>
                                <tbody id="help-field-panel-table">
                                {if (!empty ($HELP_FIELDS))}
                                    {foreach $HELP_FIELDS as $helpField}
                                        <tr id="row-{$helpField->getModuleName()}-{$helpField->getId()}">
                                            <td>
                                                <a href="index.php?module=Settings&action=HelpSettingsFieldEditView&record={$helpField->getId()}&parenttab=Settings">{$helpField->getTitle()}</a>
                                            </td>
                                            <td>{$helpField->getFieldLabel()}</td>
                                            <td>
                                                {$helpField->getModuleLabel()}
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?module=Settings&action=HelpSettingsFieldEditView&record={$helpField->getId()}&parenttab=Settings"
                                                       target="_blank" class="btn btn-default btn-sm btn-info"
                                                       title="Editar ayuda"><i class="fa fa-pencil-square-o"
                                                                               aria-hidden="true">
                                                        </i></a>
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                            data-record="{$helpField->getId()}"
                                                            data-status="{$helpField->isEditable()}"
                                                            onclick="HelpSysPanelUtils.changeEditableHelp(this)"
                                                            title="{if $helpField->isEditable() eq 'YES'}Ocultar{else}Mostrar{/if} edición del campo en ayuda">
                                                        {if $helpField->isEditable() eq 'YES'}
                                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                                        {else}
                                                            <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                                        {/if}
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-default"
                                                            title="{if $helpField->getStatus() eq 'ENABLED'}Desactivar{else}Activar{/if} ayuda"
                                                            data-record="{$helpField->getId()}"
                                                            data-status="{$helpField->getStatus()}"
                                                            onclick="HelpSysPanelUtils.changeStatusHelp (this)">
                                                        {if $helpField->getStatus() eq 'ENABLED'}
                                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                                        {else}
                                                            <i class="fa fa-square-o" aria-hidden="true"></i>
                                                        {/if}
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-sm btn-danger"
                                                            data-record="{$helpField->getId()}"
                                                            onclick="HelpSysPanelUtils.deleteHelp(this)"
                                                            title="Eliminar la ayuda"><i class="fa fa-trash-o"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="4">No se encuentró ayuda para campos</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {* /HELP FIELDS *}
                    {* help-how_to *}
                    <div id="help-how_to" class="tab-pane fade{if ($SELECTED_TAB == 'how_to')} in active{/if}">
                        <div class="col-xs-12">
                            <div class="row">
                                <div class="row">
                                    <div class="col-xs-4 text-left" style="margin-top: 1em;">&nbsp;
                                    </div>
                                    <div class="col-xs-8 text-right" style="margin-top: 1em;">
                                        <a href="index.php?module=Settings&action=HowToEditView&parenttab=Settings"
                                           class="btn btn-primary"><i class="fa fa-plus-circle"></i> Nuevo HowTo </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 table-responsive">
                            <table width="100%" cellpadding="5" cellspacing="0" class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-left" style="width: 58%">HowTo: Título</th>
                                    <th class="text-left" style="width: 30%">HowTo: Url</th>
                                    <th class="text-center" style="width: 12%;text-align: center">{$MOD.LBL_CONFIG_APPS_ACTION}</th>
                                </tr>
                                </thead>
                                <tbody id="help-field-panel-table">
                                {if $HOW_TO neq NULL}
                                    {foreach $HOW_TO as $howTo}
                                        <tr>
                                            <td>
                                                <a href="index.php?module=Settings&action=HowToEditView&record={$howTo->getId()}&parenttab=Settings">
                                                    {$howTo->getTitle()}</a>
                                            </td>
                                            <td>
                                                {*<p style="font-size: 0.85em;"></p> *}
                                                <pre>
                                                {$howTo->getUrl ()}
                                                </pre>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?module=Settings&action=HowToEditView&record={$howTo->getId()}&parenttab=Settings"
                                                       class="btn btn-default btn-sm btn-info"
                                                       title="Editar ayuda HowTo">
                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                                                    <button type="button" class="btn btn-sm  btn-warning"
                                                            title="{if $howTo->getStatus() eq 'ENABLED'}Desactivar{else}Activar{/if} ayuda HowTo"
                                                            data-record="{$howTo->getId()}"
                                                            data-status="{$howTo->getStatus()}"
                                                            onclick="HelpSysPanelUtils.changeHowToStatus(this)">
                                                        {if $howTo->getStatus() eq 'ENABLED'}
                                                            <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                                        {else}
                                                            <i class="fa fa-square-o" aria-hidden="true"></i>
                                                        {/if}
                                                    </button>
                                                    {if $howTo->getUrl () neq NULL}
                                                        <button type="button" data-file="{$howTo->getId ()}" class="btn btn-sm btn-success {*btn-icon*}" onclick="HelpSysPanelUtils.copyUrl ('{$howTo->getId ()}')" title="Copiar url"><i class="fa fa-clipboard" aria-hidden="true"></i></button>
                                                        <input type="hidden" id="url-{$howTo->getId ()}" value='{$howTo->getUrl ()}'>

                                                    {/if}
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            data-record="{$howTo->getId()}"
                                                            data-assign="
                                                            {if $howTo->getEntity() neq NULL}
                                                            Este HowTo esta asociado a los siguientes registro:;
                                                                {foreach $howTo->getEntity() as $assign}
                                                                - {$assign->getEntityTitle()}: ({$assign->getTabName()});
                                                                {/foreach}
                                                                ¿Continuar?
                                                            {/if}"
                                                            onclick="HelpSysPanelUtils.deleteHowTop(this)"
                                                            title="Eliminar la ayuda HowTo"><i class="fa fa-trash-o"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="3">No se encuentró ayudas HowTo</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {* /help-how_to *}
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="modules/Settings/help-panel-utils.js"></script>
{/strip}