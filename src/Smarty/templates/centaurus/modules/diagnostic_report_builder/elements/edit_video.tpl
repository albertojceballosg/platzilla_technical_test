<div class="row-drb justify-content-center" style="margin-top: 10px">
    <div id="gv-div-description" class="form-group col-lg-11 col-md-11 col-sm-11" style="margin-bottom: 2px!important;">
        <div class="input-group" style="width: 100%;">
            <span class="input-group-addon" style="cursor: default; background-color: #eee;"><i class="fa fa-wordpress"></i></span>
            <input  type="text"
                    id="block-field-{$FIELD_ID}"
                    name="block[{$idRowBuilder}][element-field][]" placeholder="Url del video"
                    class="form-control" tabindex=""
                    title="Url del video"
                    value="{$REPORT_ANSWER->getResult()}"
                    onblur="DiagnosticRerportBuilderUtls.validateUrl(this);">
        </div>
        <span id="help-field-{$FIELD_ID}"  class="help-block" style="color: red"></span>
    </div>
</div>