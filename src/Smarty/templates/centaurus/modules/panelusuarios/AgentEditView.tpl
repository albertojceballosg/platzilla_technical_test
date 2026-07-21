{strip}
    <style type="text/css">
        label {
            font-size: 1.11em;
            font-weight: 300;
        }

        .btn {
            margin-left: 5px;
        }

        .main-box .fa {
            color: #3498DB;
        }

        .required {
            color: #FF0000;
        }

        .radio-inline {
            font-size: 1em;
            font-weight: 300;
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
            background-color: #3498DB;
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
            margin-right: 5px;
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

        {* Large desktops and laptops. *}
        @media (min-width: 1200px) {
            .info .infotext {
                left: 480%;
                width: 300px;
            }
        }

        {* Landscape tablets and medium desktops. *}
        @media (min-width: 992px) and (max-width: 1199px) {
            .info .infotext {
                left: 480%;
                width: 300px;
            }
        }

        {* Portrait tablets and small desktops. *}
        @media (min-width: 768px) and (max-width: 991px) {
            .info .infotext {
                left: 480%;
                width: 300px;
            }
        }

        {* Landscape phones and portrait tablets. *}
        @media (min-width: 481px) and (max-width: 767px) {
            .info .infotext {
                left: 560%;
                width: 250px;
            }
        }

        {* Portrait phones and smaller. *}
        @media (max-width: 480px) {
            .info .infotext {
                left: 560%;
                width: 250px;
            }
        }
    </style>
    <div class="row">
        <div class="col-xs-12">
            <h1><a href="index.php?module=panelusuarios&action=AgentsListView&parenttab=Settings">Agentes</a></h1>
        </div>
    </div>
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    {if $AGENT neq NULL}
        {assign var="userId" value=$AGENT->getId()}
        {assign var="agentInstances" value=$AGENT->getPlatformInstance()}
        {assign var="agent" value=$AGENT->getName()}
        {assign var="description" value=$AGENT->getDescription()}
        {assign var="status" value=$AGENT->getStatus()}
    {else}
        {assign var="userId" value=null}
        {assign var="agentInstances" value=null}
        {assign var="agent" value=null}
        {assign var="description" value=null}
        {assign var="status" value=null}
    {/if}
    <div class="row">
        <div class="col-xs-12">
            <div class="main-box">
                <form method="post" action="index.php" id="form-agents"  name="form-agents">
                    <input type="hidden" name="module" value="panelusuarios"/>
                    <input type="hidden" name="action" value="SaveAgent"/>
                    {if (isset ($RECORD))}
                        <input type="hidden" name="record" value="{$RECORD}" class="record"/>
                    {/if}
                    <header class="title-section main-box-header clearfix">
                        <h2 class="pull-left">Información general</h2>
                        <div class="action-bar pull-right">
                            <button type="button"
                                    onclick="UsersUtils.saveAgent (this, '#form-agents')"
                                    class="btn btn-info">Guardar</button>
                            <a href="index.php?module=panelusuarios&action=AgentsListView&parenttab=Settings"
                               class="btn btn-warning">Cancelar</a>
                        </div>
                    </header>
                    <div class="main-box-body clearfix">
                        <div class="row">
                            {* User *}
                            <div class="col-md-6">
                                <div class="col-md-4">
                                    <div class="label-input">
                                        <label for="user_name">Usuario <span class="required">*</span></label>
                                    </div>
                                </div>
                                <div id="ag-div-user"  class="form-group col-md-8 field-container">
                                    <select class="form-control" id="user" name="user" title="Usuario">
                                        {if $AVAILABLE_USERS|count gte 1}
                                            {foreach $AVAILABLE_USERS as $id => $user}
                                                <option value="{$id}" {if $id eq $userId}selected{/if}>{$user['name']}</option>
                                            {/foreach}
                                        {else}
                                            <option value="">No hay usuarios</option>
                                        {/if}
                                    </select>
                                    <span  id="ag-user"  class="help-block"></span>
                                </div>
                            </div>
                            {* Instances *}
                            <div class="col-md-6">
                                <div class="col-md-4">
                                    <div class="label-input">
                                        <label for="instances">Instancias <span class="required">*</span></label>
                                    </div>
                                </div>
                                <div id="ag-div-instances" class="form-group col-md-8 field-container">
                                    <select multiple class="form-control" id="instances" name="instances[]" title="Instancia">
                                        {if $INSTANCES|count gte 1}
                                            {foreach $INSTANCES as $id => $instance}
                                                {if $instance->getAdministrator() neq NULL}
                                                    {if $agentInstances neq NULL}
                                                        {assign var="isSelected" value=null}
                                                        {foreach $agentInstances as $agentInstance}
                                                            {if $agentInstance->getCode() eq $instance->getCode()}
                                                                {assign var="isSelected" value='selected'}
                                                            {/if}
                                                        {/foreach}
                                                    {else}
                                                        {assign var="isSelected" value=null}
                                                    {/if}
                                                <option value="{$instance->getCode()};{$instance->getAdministrator()->getEmail()}" {$isSelected}>
                                                    {$instance->getAdministrator()->getFirstName()}  {$instance->getAdministrator()->getLastName()}
                                                </option>
                                                {/if}
                                            {/foreach}
                                        {else}
                                            <option value="">No hay Instancias</option>
                                        {/if}
                                    </select>
                                    <span  id="ag-instances" class="help-block"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            {* Agent name *}
                            <div class="col-md-6">
                                <div class="col-md-4">
                                    <div class="label-input">
                                        <label for="agent_name">Nombre del Agente <span class="required">*</span></label>
                                    </div>
                                </div>
                                <div class="form-group col-md-8 field-container">
                                    <div id="ag-div-agent_name" class="input-group" style="width: 100%;">
                                        <input id="agent_name" name="agent_name" value="{$agent}" title="Agente"
                                               class="form-control firstname" type="text"/>
                                        <span id="ag-agent_name"  class="help-block"></span>
                                    </div>
                                </div>
                            </div>
                            {* Description *}
                            <div class="col-md-6">
                                <div class="col-md-4">
                                    <div class="label-input">
                                        <label for="description">Descripción del agente</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-8 field-container">
                                    <div id="ag-div-agent_description" class="input-group" style="width: 100%;">
                                        <textarea id="agent_description" name="agent_description" class="form-control" tabindex="" rows="2">{$description}</textarea>
                                        <span id="ag-agent_description"  class="help-block"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            {* State *}
                            <div class="col-md-6">
                                <div class="col-md-4">
                                    <div class="label-input">
                                        <div class="info"><i class="fa fa-info-circle"></i><span class="infotext">Cuando creas un Agente puedes elegir si este está activo o inactivo</span>
                                        </div>
                                        <label for="status">Status <span class="required">*</span></label>
                                    </div>
                                </div>
                                <div class="form-group col-md-8 field-container">
                                    <div id="ag-div-agent_status" class="input-group" style="width: 100%;">
                                        <select id="agent_status" name="agent_status" class="form-control status" title="Estado">
                                            {foreach $STATUS  as $value => $title}
                                                <option value="{$value}" {if $value eq $status}selected{/if}>{$title}</option>
                                            {/foreach}
                                        </select>
                                        <span id="ag-agent_status"  class="help-block"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="modules/panelusuarios/panelusuarios.js"></script>
{/strip}
