{strip}
    {if (isset ($HELP_FIELD))}
        {assign var='fieldId' value=$HELP_FIELD->getId()}
        {assign var='fieldDescription' value=$HELP_FIELD->getDescription()}
        {assign var='fieldModuleName' value=$HELP_FIELD->getModuleName()}
        {assign var='fieldFieldName' value=$HELP_FIELD->getFieldName()}
        {assign var='fieldTitle' value=$HELP_FIELD->getTitle()}
        {assign var='fieldIsEditable' value=$HELP_FIELD->isEditable()}
        {assign var='fieldStatus' value=$HELP_FIELD->getStatus()}
        {assign var='fieldImage' value=$HELP_FIELD->getImage()}
        {assign var='fieldVideo' value=$HELP_FIELD->getUrlVideo()}
        {assign var='fieldTypeVideo' value=$HELP_FIELD->getVideoType ()}
    {else}
        {assign var='fieldId' value=null}
        {assign var='fieldDescription' value=null}
        {assign var='fieldModuleName' value=null}
        {assign var='fieldFieldName' value=null}
        {assign var='fieldTitle' value=null}
        {assign var='fieldIsEditable' value=null}
        {assign var='fieldStatus' value=null}
        {assign var='fieldImage' value=null}
        {assign var='fieldVideo' value=null}
        {assign var='fieldTypeVideo' value=null}
    {/if}
    {math equation= rand() assign= "idHelp"}
    <style type="text/css">
        .required {
            color: #FF0000;
        }
        .help-block {
            color: #FF0000;
        }
        .action-bar .btn {
            margin-left: 5px;
        }
        label {
            font-size: 1em;
        }
    </style>
    <form   name="form-{$idHelp}" method="post" action="index.php" id="help-form-{$idHelp}">
        <input type="hidden" name="module" value="Settings" />
        <input type="hidden" name="action" value="SaveHelpField" />
        <input type="hidden" name="tab" value="fields">
        {*<input type="hidden" name="Ajax" value="true" />*}
        {if (isset ($fieldId))}
            <input type="hidden" name="record" value="{$fieldId}" />
        {/if}
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=fields">Ayuda - Campos</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="button" onclick="HelpSettingsUtils.saveHelpField (this, '{$idHelp}');"  class="btn btn-info">Guardar</button>
                    <a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=fields" class="btn btn-warning">Cancelar</a>
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
                        <h2 class="pull-left">Información general</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            {* Module - field*}
                            <div class="col-xs-12 col-md-6">
                                <label for="modulename">{$MOD.LBL_RELATED_MODULE}</label>
                                <div class="form-group field-container">
                                    <div id="help-field-div-modulename" class="input-group" style="width: 100%;">
                                        <select id="help-field-module" name="modulename"
                                                class="form-control no-resize module-name"
                                                onchange="HelpSettingsUtils.setFieldsByModule (this);"
                                                title="{$MOD.LBL_RELATED_MODULE}">
                                            <option value="">Selecciona</option>
                                            {if ($AVAILABLE_MODULES neq NULL)}
                                                {foreach $AVAILABLE_MODULES as $moduleName}
                                                    <option value="{$moduleName->getName()}"{if ($fieldModuleName == $moduleName->getName())} selected="selected"{/if}>{$moduleName->getLabel()} ({$moduleName->getName()})</option>
                                                {/foreach}
                                            {/if}
                                        </select>
                                    </div>
                                    <span id="help-field-modulename" class="help-block"></span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <label for="fieldname">{$MOD.LBL_RELATED_FIELD}</label>
                                <div class="form-group field-container">
                                    <div id="help-field-div-" class="input-group" style="width: 100%;">
                                        <select id="help-field-fieldname" name="fieldname"
                                                class="form-control no-resize field-name"
                                                title="{$MOD.LBL_RELATED_FIELD}">
                                            <option value="" data-module="">Selecciona</option>
                                            {if ($AVAILABLE_FIELDS neq NULL)}
                                                {foreach $AVAILABLE_FIELDS as $moduleName => $fields}
                                                {foreach $fields as  $field}
                                                    <option value="{$field->getName()}"
                                                            data-module="{$moduleName}"
                                                            {if ($fieldFieldName == $field->getName())} selected="selected"{/if}>
                                                        {$field->getLabel()}</option>
                                                {/foreach}
                                                {/foreach}
                                            {/if}
                                        </select>
                                    </div>
                                    <span id="help-field-fieldname" class="help-block"></span>
                                </div>
                            </div>
                            {* Module - field*}
                            {* Title *}
                            <div class="col-xs-12">
                                <label for="title">Titulo de la ayuda</label>
                            </div>
                            <div class="col-xs-12">
                                <div class="form-group field-container">
                                    <div id="help-field-div-title" class="input-group" style="width: 100%;">
                                        <input type="text" id="title" name="title"  title="Titulo de la ayuda" value="{$fieldTitle}" maxlength="255" class="form-control" />
                                    </div>
                                    <span id="help-field-title" class="help-block"></span>
                                </div>
                            </div>
                            {* Description *}
                            <div class="col-xs-12">
                                <label for="description">Descripción</label>
                            </div>
                            <div class="col-xs-12">
                                <div class="form-group field-container">
                                    <div id="help-field-div-description" class="input-group" style="width: 100%;">
                                        <textarea id="help-field-description" name="description" class="form-control">{$fieldDescription}</textarea>
                                    </div>
                                    <span id="help-sys-field-description" class="help-block"></span>
                                </div>
                            </div>
                            {* url video *}
                            <div class="col-xs-12">
                                <label for="video-help-type">Tipo de video</label>
                            </div>
                            <div class="col-xs-12">
                                <div class="form-group field-container">
                                    <div id="help-field-div-video-type" class="input-group" style="width: 100%;">
                                        <select id="video-type" name="videotype" class="form-control">
                                            {if (!empty ($TYPE_VIDEO))}
                                                <option value="">Seleccionar tipo de video</option>
                                                {foreach $TYPE_VIDEO as $typeVideo}
                                                    <option value="{$typeVideo}"{if ($typeVideo == $fieldTypeVideo)} selected="selected"{/if}>{$typeVideo|strtolower|ucfirst}</option>
                                                {/foreach}
                                            {/if}
                                        </select>
                                    </div>
                                    <span id="help-sys-field-video-type" class="help-block"></span>
                                </div>
                            </div>
                            {* url video *}
                            <div class="col-xs-12">
                                <label for="url">URL video</label>
                            </div>
                            <div class="col-xs-12">
                                <div class="form-group field-container">
                                    <div class="input-group" style="width: 100%;">
                                        <input type="text" id="url" name="url" value="{$fieldVideo}" maxlength="2048" class="form-control" />
                                    </div>
                                </div>
                            </div>
                            {* Editable - Status *}
                            <div class="col-xs-12 col-md-6">
                                <label for="iseditable">{$MOD.LBL_HELP_FIELD_EDITABLE}</label>
                                <div class="form-group field-container">
                                    <div id="help-field-div-iseditable" class="input-group" style="width: 100%;">
                                        <select name="iseditable" id="iseditable"
                                                class="form-control no-resize"
                                                title="{$MOD.LBL_HELP_FIELD_EDITABLE}">
                                            <option value="">Selecciona</option>
                                            {if ($IS_EDITABLE neq NULL)}
                                                {foreach $IS_EDITABLE as $value => $label}
                                                    <option value="{$value}"{if ($fieldIsEditable == $value)} selected="selected"{/if}>{$label}</option>
                                                {/foreach}
                                            {/if}
                                        </select>
                                    </div>
                                    <span id="help-field-iseditable" class="help-block"></span>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <label for="statushelp">{$MOD.LBL_HELP_FIELD_STATUS}</label>
                                <div class="form-group field-container">
                                    <div id="help-field-div-statushelp" class="input-group" style="width: 100%;">
                                        <select name="statushelp" class="form-control no-resize field-name" title="{$MOD.LBL_HELP_FIELD_STATUS}">
                                            <option value="">Selecciona</option>
                                            {if ($HELP_STATUS neq NULL)}
                                                {foreach $HELP_STATUS as $value => $label}
                                                    <option value="{$value}"{if ($fieldStatus == $value)} selected="selected"{/if}>{$label}</option>
                                                {/foreach}
                                            {/if}
                                        </select>
                                    </div>
                                    <span id="help-field-statushelp" class="help-block"></span>
                                </div>
                            </div>
                            {* Editable - status *}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="modules/Settings/help-settings.js"></script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>

{/strip}