{extends file="base/MassMailModalLayout.tpl"}

{block name="css"}{/block}
{block name="modal_title"}Enviar correo masivo{/block}

{block name="modal_body"}
    <div class="row">
        <header class="title-section main-box-header" style="padding: 2px">
            <h2 style="margin: 0!important; padding: 5px 0 5px 15px!important;font-weight: bold">Datos para el encabezado</h2></header>
        {*$AVAILABLE_MODULES|var_dump*}
        {* Languages *}
        <div class="col-md-6">
            <div class="col-md-4">
                <div class="label-input"><label for="mass-mail-language">Idioma: <span class="required">*</span></label>
                </div>
            </div>
            {* Languages *}
            <div class="form-group col-md-8 field-container">
                <div class="input-group" style="width: 100%;">
                    <select id="mass-mail-language-{$idMailModal}" name="language" class="form-control parameter-value"
                            onchange="MassActionsUtils.setTemplateOptions (this, '{$idMailModal}');">
                        <option value="">Seleccionar idioma</option>
                        {foreach $AVAILABLE_LANGUAGES as $key => $language}
                            <option value="{$key}">{$language}</option>
                        {/foreach}
                    </select>
                    <span class="help-block danger"></span>
                </div>
            </div>
        </div>
        {* Template *}
        <div class="col-md-6">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="mass-mail-template-name">Plantilla: <span class="required">*</span>
                    </label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container">
                <div class="input-group" style="width: 100%;">
                    <select id="mass-mail-template-name-{$idMailModal}" name="templatename"
                            class="form-control parameter-value"
                            onchange="MassActionsUtils.setVariableOptions (this,'{$idMailModal}');">
                        <option value="" data-language="">Seleccionar plantilla</option>
                        {foreach $TEMPLATES as $template}
                        <option value="{$template['templateid']}"
                                data-variables="{$template['variables']}"
                                data-language="{$template['language']}"
                                class="template-name hide">
                            {$template['templatename']}
                            </optin>
                            {/foreach}
                    </select>
                    <span class="help-block danger"></span>
                </div>
            </div>
        </div>
        {* Destinatario *}
        <div class="col-md-12 parameter">
            <div class="col-md-2">
                <div class="label-input"><label for="mass-mail-recipients-type">Destinatarios: <span
                                class="required">*</span></label></div>
            </div>
            <div class="col-md-3 form-group">
                <div class="input-group" style="width: 100%;">
                    <select id="mass-mail-recipients-type-{$idMailModal}" name="recipients[type]"
                            class="form-control parameter-type"
                            onchange="MassActionsUtils.setParameterValue (this);">
                        <option value=""></option>
                        <option value="SOURCE FIELD">Campo en los registros seleccionados</option>
                        <option value="LITERAL">Valor</option>
                        <option value="VARIABLE">Variable del sistema</option>
                    </select>
                    <span class="help-block danger"></span>
                </div>
            </div>
            <div class="form-group col-md-7 field-container">
                <div class="input-group" style="width: 100%;">
                    <input type="text" name="recipients[value]" value=""
                           class="form-control parameter-value" placeholder="" data-type="LITERAL"
                           disabled="disabled" style="display: none;"/>
                    <select id="mass-mail-recipients-source-fields" name="recipients[value]"
                            class="form-control parameter-value" title="" data-type="SOURCE FIELD"
                            disabled="disabled" style="display: none;">
                        <option>Seleccionar el campo</option>
                        <option value="record_id">(El registro que se está procesando)</option>
                        {if $FIELDS neq NULL}
                            {foreach $FIELDS as $name => $label}
                                <option value="{$name}">{$label}</option>
                            {/foreach}
                        {else}
                            <option value=""> - No hay campos -</option>
                        {/if}
                    </select>
                    <span class="help-block danger"></span>
                    <div class="input-group variable" style="display: none;">
                        <input type="text" name="recipients[value]" class="form-control parameter-value"
                               placeholder="" data-type="VARIABLE" disabled="disabled"
                               style="display: none;"/>
                        <div class="input-group-btn">
                            <button class="btn btn-default" type="button"
                                    title="Campos en la fuente de datos"
                                    onclick="MassActionsUtils.openFieldsModal (this);"><i class="fa fa-code"></i>
                            </button>
                            <button class="btn btn-default" type="button" title="Variables de sistema"
                                    onclick="MassActionsUtils.openVariablesModal (this);"><i class="fa fa-cogs"></i>
                            </button>
                        </div>
                        <span class="help-block danger"></span>
                    </div>
                </div>
            </div>
        </div>
        {* Variables Section *}
        <div id="mass-mail-variables-section-{$idMailModal}" class="row hide">
            <header class="title-section main-box-header">
                <h2 style="font-weight: bold">Datos para el cuerpo del correo</h2>
            </header>
            {* <h4 class="col-md-12">Variables</h4> *}
            {* Module origen *}
            <div id="module-origen-{$idMailModal}" class="col-md-12">
                <div class="col-md-2">
                    <div class="label-input">
                        Módulo origen de datos
                    </div>
                </div>
                <div class="col-md-3 form-group">
                    {if $AVAILABLE_MODULES neq NULL}
                        <div class="input-group" style="width: 100%;">
                            <select id="module_related-{$idMailModal}" style="margin-top: 2px"
                                    name="module_related_record"
                                    data-current-module="{$MODULE}"
                                    data-display-field-id="module_related_record{$idMailModal}_display"
                                    data-field-id="module_related_record{$idMailModal}"
                                    data-referenced-module="" data-title=""
                                    onchange="MassActionsUtils.setModuleOrigen(this, '{$idMailModal}')"
                                    class="form-control">
                                <option value="">Modulo origen</option>
                                {foreach $AVAILABLE_MODULES as $avaModule}
                                    <option value="{$avaModule['value']}">{$avaModule['label']}</option>
                                {/foreach}
                            </select>
                        </div>
                        <input type="hidden" id="module_related_record{$idMailModal}"
                               name="related_record_id"
                               value=""
                               class="for-filter module-reference">
                    {/if}
                </div>
                <div class="form-group col-md-7 field-container">
                    <div class="input-group" style="width: 100%;">
                        <div class="input-group variable" style="width: 100%;">
                            <input type="text" id="module_related_record{$idMailModal}_display"
                                   placeholder="Nombre del registro"
                                   class="form-control parameter-value" readonly value="">
                        </div>
                    </div>
                </div>
            </div>
            {* variable *}
            <div id="mass-mail-variables-{$idMailModal}" class="col-md-12"></div>
        </div>
    </div>
{/block}

{block name="modal_hidden"}
    {foreach $RECORD_IDS as $record}
        {if $record eq NULL}{continue}{/if}
        <input type="hidden" name="recordids[]" value="{$record}">
    {/foreach}
{/block}

{block name="modal_footer"}
    <button type="submit" class="btn btn-primary">Enviar</button>
{/block}
{block name="js"}
    <script type="text/javascript" src="include/js/mass-actions-utils.js?v=1.1"></script>
    <script type="text/html" id="mass-mail-fields-modal-template">
        <div class="modal fade" id="mass-mail-auxiliary-modal" tabindex="-1" role="dialog" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Campos del módulo seleccionado</h4>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                {if $FIELDS neq NULL}
                                    {foreach $FIELDS as $name => $label}
                                        <tr>
                                            <td>
                                                <a onclick="MassActionsUtils.setVariableValue ('|{$name}|'); return false;"
                                                   href="#">{$label}</a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td> - No hay campos -</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </script>
    <script type="text/html" id="mass-mail-system-modal-template">
        <div class="modal fade" id="mass-mail-auxiliary-modal" tabindex="-1" role="dialog" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Variables del sistema</h4>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                {if $SYSTEM_VARIABLES neq NULL}
                                    {foreach $SYSTEM_VARIABLES as $name => $label}
                                        <tr>
                                            <td>
                                                <a onclick="MassActionsUtils.setVariableValue ('{literal}{{/literal}{$name}{literal}}{/literal}'); return false;"
                                                   href="#">{$label}</a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td> - No variables definidas -</td>
                                    </tr>
                                {/if}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </script>
    <script type="text/html" id="mass-mail-modal-template-variable-{$idMailModal}">
        <div class="col-md-12 parameter">
            <div class="col-md-2">
                <div class="label-input">
                    <input type="text" class="form-control variable-name" placeholder="" readonly="readonly"/>
                </div>
            </div>
            <div class="col-md-3 form-group">
                <div class="input-group" style="width: 100%;">
                    <select class="form-control parameter-type" title=""
                            onchange="MassActionsUtils.setParameterValue (this);">
                        <option value=""></option>
                        <option value="SOURCE FIELD">Campo en los registros seleccionados</option>
                        <option value="LITERAL">Valor</option>
                        <option value="VARIABLE">Variable del sistema</option>
                        <option value="SOURCE MODULE">Campo en el registro del modulo origen</option>
                    </select>
                </div>
            </div>
            <div class="form-group col-md-7 field-container">
                <div class="input-group" style="width: 100%;">
                    <input type="text" class="form-control parameter-value" placeholder="" data-type="LITERAL"/>
                    <select class="form-control parameter-value" title="" data-type="SOURCE FIELD">
                        <option></option>
                        {if $FIELDS neq NULL}
                            {foreach $FIELDS as $name => $label}
                                <option value="{$name}">{$label}</option>
                            {/foreach}
                        {else}
                            <option value=""> - No hay campos -</option>
                        {/if}
                        <option value="record_id">(El registro que se está procesando)</option>
                    </select>
                    <select class="form-control parameter-value source-module-{$idMailModal}"  title="" data-type="SOURCE MODULE">
                        <option>Modulo no seleccionado</option>
                    </select>
                    <div class="input-group variable">
                        <input type="text" class="form-control parameter-value" placeholder="" data-type="VARIABLE"/>
                        <div class="input-group-btn">
                            <button class="btn btn-default" type="button" title="Campos en la fuente de datos"
                                    onclick="MassActionsUtils.openFieldsModal (this);"><i class="fa fa-code"></i>
                            </button>
                            <button class="btn btn-default" type="button" title="Variables del sistema"
                                    onclick="MassActionsUtils.openVariablesModal (this);"><i class="fa fa-cogs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>
{/block}
