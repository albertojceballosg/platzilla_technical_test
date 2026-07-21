<div class="row-drb justify-content-center" style="margin-top: 10px">
    <div id="gv-div-description" class="form-group col-lg-11 col-md-11 col-sm-11" style="margin-bottom: 2px!important;">
        <div class="row">
            <div class="col-md-4 text-right">
                <label for="fromfieldname">Funciones valoradas:</label>
            </div>
            <div id="div-drb-name" class="form-group col-md-6 field-container">
                <input type="text"
                       name="block[{$ID}][element-field][]"
                       id="block-field-function-{$FIELD_ID}"
                       value=""
                       title="La función"
                       class="form-control">
                <span id="help-field-{$FIELD_ID}" class="help-block" style="color: red"></span>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4">

            </div>
        </div>
    </div>
    <div id="gv-div-description" class="form-group col-lg-11 col-md-11 col-sm-11" style="margin-bottom: 2px!important;">
        <textarea  name="block[{$ID}][attributes][]" id="block-field-{$FIELD_ID}" class="form-control"
                  title="descripción del contenido"></textarea>
    </div>
</div>