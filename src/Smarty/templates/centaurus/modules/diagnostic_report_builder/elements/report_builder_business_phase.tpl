<div class="row-drb justify-content-center" style="margin-top: 10px">
    <div id="gv-div-description" class="form-group col-lg-11 col-md-11 col-sm-11" style="margin-bottom: 2px!important;">
        <div class="row">
            <div class="col-md-4 text-right">
                <label for="fromfieldname">Etapa:</label>
            </div>
            <div id="div-drb-name" class="form-group col-md-6 field-container">
                <select class="form-control"
                        id="block-field-{$FIELD_ID}"
                        name="block[{$ID}][element-field][]"
                        title="La etapa">
                    {if isset($BUSINESS_PHASE) && $BUSINESS_PHASE neq NULL}
                        <option value="">Seleccionar etapa</option>
                        {foreach $BUSINESS_PHASE->getValues() as $values}
                            <option value="{$values->getValue()}">{$values->getValue()}</option>
                        {/foreach}
                    {else}
                        <option value="">Upoo! no hay etapas disponibles</option>
                    {/if}
                </select>
                <span id="help-field-{$FIELD_ID}" class="help-block" style="color: red"></span>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4">

            </div>
        </div>
    </div>
</div>