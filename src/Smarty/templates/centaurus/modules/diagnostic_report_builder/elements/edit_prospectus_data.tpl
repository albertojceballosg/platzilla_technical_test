<div class="row-drb justify-content-center" style="margin-top: 10px">
    <div id="gv-div-description" class="form-group col-lg-11 col-md-11 col-sm-11" style="margin-bottom: 2px!important;">
        <div class="row">
            <div class="col-md-4 text-right">
                <label for="fromfieldname">Datos personales:</label>
            </div>
            <div id="div-drb-name" class="form-group col-md-6 field-container">
                {*$DESTINATION_CATEGORY|var_dump*}
                <select class="form-control"
                        id="block-field-{$FIELD_ID}"
                        name="block[{$idRowBuilder}][element-field][]"
                        title="La categoría">
                    {if isset($PROSPECTUS_DATA) && $PROSPECTUS_DATA neq NULL}
                        <option value="">Seleccionar..</option>
                        {foreach $PROSPECTUS_DATA  as $key => $prospectus}
                            <option value="{$key}"
                                    {if $REPORT_ANSWER->getResult() eq $key}
                                        selected
                                    {/if}
                            >{$prospectus}</option>
                        {/foreach}
                    {else}
                        <option value="">Upoo! no hay datos disponibles</option>
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