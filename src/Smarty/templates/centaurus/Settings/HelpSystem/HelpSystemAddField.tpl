<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/wizard.css"/>
<link rel="stylesheet" type="text/css" href="modules/Settings/layout-editor.css"/>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
    <div class="row">
        <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
            <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    </div>
{/if}
<div id="help-add-field-{$HELP_id}">
<div  id="field-modal" class="row">
    <span id="field-utils-help-block" class="help-block" style="color: red"></span>
    <div class="col-xs-12">
        <form action="index.php" method="post" onsubmit="FieldUtils.saveField (this); return false;" autocomplete="off">
            <input type="hidden" name="module" value="Settings" />
            <input type="hidden" name="action" value="SaveField" />
            <input type="hidden" name="modulename" value="{$MODULE}" />
            <input type="hidden" name="blockid" value="{$BLOCKS_ID}" />
            <input type="hidden" name="Ajax" value="true" />
            <div class="row modal-body field-container" style="margin-top: -20px!important;">
                <ul class="col-xs-12 col-md-6 field-types">
                    {foreach $FIELD_TYPE_OPTIONS as $fieldType}
                        {if (in_array ($fieldType.value, array (FieldInterface::UI_TYPE_CODE)))}{continue}{/if}
                        <li class="field-type" onclick="FieldUtils.setSelectedFieldType (this, {$fieldType.value});">
                            <i class="fa {$fieldType.icon} fa-fw fa-ed" aria-hidden="true"></i>&nbsp;{$fieldType.text}
                        </li>
                    {/foreach}
                </ul>
                <div class="col-xs-12 col-md-6 field-definitions">
                    <input type="hidden" id="field-type" name="uitype" />
                    <div style="display: none" class="form-group field-definition" data-types="[ {FieldInterface::UI_TYPE_ATTACHMENTS}, {FieldInterface::UI_TYPE_CALCULATED_LINK}, {FieldInterface::UI_TYPE_CHECKBOX}, {FieldInterface::UI_TYPE_CURRENCY}, {FieldInterface::UI_TYPE_DATE}, {FieldInterface::UI_TYPE_EMAIL}, {FieldInterface::UI_TYPE_IMAGE_DISPLAY}, {FieldInterface::UI_TYPE_MODULE_REFERENCE}, {FieldInterface::UI_TYPE_MULTI_SELECT}, {FieldInterface::UI_TYPE_NUMBER}, {FieldInterface::UI_TYPE_PERCENTAGE}, {FieldInterface::UI_TYPE_PHONE}, {FieldInterface::UI_TYPE_PICKLIST}, {FieldInterface::UI_TYPE_PIPELINE}, {FieldInterface::UI_TYPE_TEXT}, {FieldInterface::UI_TYPE_TEXTAREA}, {FieldInterface::UI_TYPE_URL}, {FieldInterface::UI_TYPE_VIDEO} ]">
                        <label for="field-name">Código</label>
                        <input type="text" id="field-name" name="name" class="form-control" disabled="disabled" maxlength="30" onkeydown="FieldUtils.normalizeFieldContents (this, event);" />
                    </div>
                    <div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_ATTACHMENTS}, {FieldInterface::UI_TYPE_CALCULATED_LINK}, {FieldInterface::UI_TYPE_CHECKBOX}, {FieldInterface::UI_TYPE_CURRENCY}, {FieldInterface::UI_TYPE_DATE}, {FieldInterface::UI_TYPE_EMAIL}, {FieldInterface::UI_TYPE_GLOBAL_PICKLIST}, {FieldInterface::UI_TYPE_IMAGE_DISPLAY}, {FieldInterface::UI_TYPE_MODULE_REFERENCE}, {FieldInterface::UI_TYPE_MULTI_SELECT}, {FieldInterface::UI_TYPE_NUMBER}, {FieldInterface::UI_TYPE_PERCENTAGE}, {FieldInterface::UI_TYPE_PHONE}, {FieldInterface::UI_TYPE_PICKLIST}, {FieldInterface::UI_TYPE_PIPELINE}, {FieldInterface::UI_TYPE_TEXT}, {FieldInterface::UI_TYPE_TEXTAREA}, {FieldInterface::UI_TYPE_URL}, {FieldInterface::UI_TYPE_VIDEO} ]">
                        <label for="field-label">Nombre</label>
                        <input autocomplete="off" type="text" id="field-label" name="label" class="form-control" disabled="disabled" maxlength="30" onkeydown="FieldUtils.normalizeFieldContents (this, event);"/>
                        <span class="help-block"><small>Solo admite letras/numeros y los símbolos - y _<br/>La longitud es de 30 Caracteres </small></span>
                    </div>
                    <div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_TEXT} ]">
                        <label for="field-length">Extensión</label>
                        <input type="number" id="field-length" name="length" class="form-control" disabled="disabled" min="1" max="255" onkeyup="FieldUtils.normalizeFieldLength (this, event);"/>
                        <span class="help-block"><small>Solo admite números, máximo valor 255</small></span>
                    </div>
                    <div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_CURRENCY}, {FieldInterface::UI_TYPE_NUMBER} ]">
                        <label for="field-precision">Decimales</label>
                        <input type="number" id="field-precision" name="precision" class="form-control" disabled="disabled" min="1" max="10" />
                    </div>
                    <div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_EMAIL}, {FieldInterface::UI_TYPE_PHONE}, {FieldInterface::UI_TYPE_TEXT}, {FieldInterface::UI_TYPE_URL} ]">
                        <label for="field-unique">Valor único</label>
                        <select id="field-unique" name="unique" class="form-control" disabled="disabled">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                    <div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_MULTI_SELECT}, {FieldInterface::UI_TYPE_PICKLIST}, {FieldInterface::UI_TYPE_PIPELINE} ]">
                        <label for="field-picklist-values">Valores de la lista</label>
                        <textarea id="field-picklist-values" name="picklistvalues" class="form-control" disabled="disabled"></textarea>
                    </div>
                    <div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_MODULE_REFERENCE} ]">
                        <label for="field-referenced-module-name">Referencia a módulo</label>
                        <select id="field-referenced-module-name" name="referencedmodulename" class="form-control" disabled="disabled">
                            <option value=""></option>
                            {foreach $AVAILABLE_ENTITY_MODULES as $module}
                                <option value="{$module.name}">{$module.label}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_GLOBAL_PICKLIST} ]">
                        <label for="field-global-picklist-name">Campo de lista especial</label>
                        {if (!empty ($AVAILABLE_GLOBAL_PICKLISTS))}
                            <select id="field-global-picklist-name" name="globalpicklistname" class="form-control" disabled="disabled">
                                <option value=""></option>
                                {foreach $AVAILABLE_GLOBAL_PICKLISTS as $picklist}
                                    <option value="{$picklist->getName ()}">{$picklist->getLabel ()}</option>
                                {/foreach}
                            </select>
                        {/if}
                    </div>
                    <div class="form-group field-definition hidden" data-types="[ {FieldInterface::UI_TYPE_CALCULATED_LINK} ]">
                        <label for="field-calculation-name">Cálculo</label>
                        {if (!empty ($CALCULATED_SYSTEMS))}
                            <select id="field-calculation-name" name="calculationname" class="form-control" disabled="disabled">
                                <option value=""></option>
                                {foreach $CALCULATED_SYSTEMS as $calculatedSystem}
                                    <option value="{$calculatedSystem->getCalculationName ()}">{$calculatedSystem->getName ()}</option>
                                {/foreach}
                            </select>
                        {/if}
                    </div>
                </div>
            </div>
            <div class="modal-footer text-center">
                <button id="field-utils-submmit" type="submit" class="btn btn-primary center-block">Guardar</button>
            </div>
        </form>
    </div>
</div>
</div>
<script type="text/javascript" src="modules/Settings/field-utils.js"></script>
<script type="text/javascript">
jQuery ( document ).ready(function() {
    FieldUtils.updateModal('{$HELP_id}');
});
</script>