{strip}
    <style type="text/css">
        .col-fullname {
            width: 20%;
            vertical-align: top!important;
        }

        .col-status {
            width: 6%;
        }
        .col-modulename {
               width: 15em;
           }

           .col-field {
               width: 15em;
           }

           .col-actions {
               width: 12%;
               text-align: right!important;
           }

           .action {
               display: inline-block;
               list-style: none;
           }

           .action .btn {
               font-size: 14px;
               height: 27px;
               line-height: 27px;
               margin: 0 5px 0 0;
               padding: 0;
               text-align: center;
               width: 27px;
    </style>
    <div id="email-box" class="clearfix">
        <table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
            <tr>
                <td rowspan="2" valign="top">
                    <div class="infographic-box" style="width: 30px; padding: 0;"><i
                                class="fa fa-user-md emerald-bg"></i></div>
                </td>
                <td class="heading2" valign="bottom">
                    <ol class="breadcrumb">
                        <li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a>
                        </li>
                        <li class="active">{$MOD.LBL_AGENTS}</li>
                    </ol>
                </td>
            </tr>
            <tr>
                <td class="small" valign="top">{$MOD.LBL_AGENTNS_DESCRIPTION}</td>
            </tr>
        </table>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        {/if}
        <div class="col-xs-12">
            <div class="main-box clearfix">
                {*$AVAILABLE_AGENTS[0]->getPlatFormInstance()|var_dump*}
                <header class="main-box-header clearfix">
                    <div class="col-xs-12 text-right">
                        <a href="index.php?module=panelusuarios&action=AgentsEditView"
                           class="btn btn-primary">
                            <i class="fa fa-plus-circle"></i> Crear agente.
                        </a>
                    </div>
                </header>
                <div class="main-box-body clearfix" id="ListViewContents">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-fullname">Usuario</th>
                                <th class="col-fullname">Agente</th>
                                <th class="col-fullname">Descripción</th>
                                <th class="col-fullname">Instancias</th>
                                <th class="col-status">Estado</th>
                                <th class="col-actions">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            {if $AVAILABLE_AGENTS neq NULL }
                                {foreach $AVAILABLE_AGENTS as $agent}
                                    <tr class="lvtColData">
                                        <td class="col-fullname">
                                            <div style="display: inline-block; margin-right: 10px; vertical-align: middle;">
                                                <img src="{$agent->getUserAvatar()}"
                                                     style="border-radius: 50%; width: 40px;">
                                            </div>
                                            <div style="display: inline-block; vertical-align: middle;">
                                                {$agent->getUserName ()}
                                            </div>
                                        </td>
                                        <td class="col-fullname">{$agent->getName()}</td>
                                        <td class="col-fullname">
                                            {$agent->getDescription ()}
                                        </td>
                                        <td class="col-fullname">
                                            {if $agent->getPlatFormInstance() neq NULL}
                                                <ul>
                                                    {foreach $agent->getPlatFormInstance() as $instance}
                                                        <li>{$instance->getCode()}:
                                                            &nbsp;{$instance->getAdministrator()->getFirstName()} {$instance->getAdministrator()->getLastName()}</li>
                                                    {/foreach}
                                                </ul>
                                            {else}{/if}
                                        </td>
                                        <td class="col-status">
                                            <span class="label label-{if $agent->getStatus () eq 'ACTIVE'}success{else}danger{/if}">
                                                {$STATUS[$agent->getStatus ()]}
                                            </span>
                                        </td>
                                        <td class="col-actions">
                                            {if ($IS_ADMIN)}
                                            <ul class="actions" style="float: right">
                                                <li class="action">
                                                    <a href="index.php?module=panelusuarios&action=AgentsEditView&parenttab=Settings&record={$agent->getId()}"
                                                       class="btn btn-primary"
                                                       title="Editar"><i class="fa fa-pencil"></i></a></li>
                                                <li class="action">
                                                    <form method="post" action="index.php"
                                                          onsubmit="return changeAgent ('{$agent->getName ()}');">
                                                        <input type="hidden" name="module" value="panelusuarios">
                                                        <input type="hidden" name="action" value="AgentChangeStatus">
                                                        <input type="hidden" name="record" value="{$agent->getId()}">
                                                        <input type="hidden" name="agent_status" value="{$agent->getStatus ()}">
                                                        <input type="hidden" name="Ajax" value="true">
                                                        <button class="btn  btn-default" type="submit" title="{if $agent->getStatus () eq 'ACTIVE'}Suspender{else}Activar{/if}">
                                                            <i class="fa {if $agent->getStatus () eq 'ACTIVE'}fa-check-square-o{else}fa-square-o{/if}" aria-hidden="true">
                                                            </i>
                                                        </button>
                                                    </form>
                                                </li>
                                                <li class="action">
                                                    <form method="post" action="index.php"
                                                          onsubmit="return deleteAgent ('{$agent->getName ()}');">
                                                        <input type="hidden" name="module" value="panelusuarios">
                                                        <input type="hidden" name="action" value="DeleteAgent">
                                                        <input type="hidden" name="record" value="{$agent->getId()}">
                                                        <input type="hidden" name="Ajax" value="true">
                                                        <button class="btn btn-danger" type="submit" title="Eliminar">
                                                            <i class="fa fa-trash-o"></i></button>
                                                    </form>
                                                </li>
                                            </ul>

                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="5" class="text-center">No se encuentran Agentes registrados</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
            deleteAgent = function (label) {
                return confirm ('¿Estás seguro de eliminar el agente "' + label + '"?')
            }
            changeAgent = function (label) {
                return confirm ('¿Estás seguro de cambiar el estado al agente "' + label + '"?')
            }
        </script>
    <script type="text/javascript" src="modules/panelusuarios/panelusuarios.js"></script>
{/strip}