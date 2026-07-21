<div class="row-drb justify-content-center" style="margin-top: 10px">
    <div id="gv-div-description" class="form-group col-lg-11 col-md-11 col-sm-11" style="margin-bottom: 2px!important;">
        <textarea id="block-field-{$FIELD_ID}" style="" name="block[{$idRowBuilder}][element-field][]" class="form-control"
                  title="descripción del contenido">{$REPORT_ANSWER->getResult()}</textarea>
    </div>
    <span id="help-field-{$FIELD_ID}"  class="help-block" style="color: red"> </span>
    <input type="hidden" name="block[{$idRowBuilder}][attributes][]" id="block-field-attribute-{$FIELD_ID}" value="{$REPORT_ANSWER->getAttributes()}">
</div>
{include file='modules/diagnostic_report_builder/elements/builder_template_variables.tpl'}