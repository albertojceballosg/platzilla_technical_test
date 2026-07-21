{strip}
    {math equation= rand() assign= "idProcessDetailView"}
    {block name="css"}{/block}
    {*$AVAILABLE_PROCESS|var_dump*}
    <div class="row" id="process-{$idProcessDetailView}" style="margin-bottom: 6px; margin-left: 10px">
        <input type="hidden" id="module-{$idProcessDetailView}" value="{$MODULE}">
        <input type="hidden" id="record-{$idProcessDetailView}" value="{$RECORD_ID}">
        <input type="hidden" id="case_number" value="">
        <input type="hidden" id="function" value="JOIN-PROCESS-CASE">
        <div class="col-md-6">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="process_area" class="">
                        <span id="p1_business_processes"></span>&nbsp;Modelo de proceso a seguir</label>
                </div>
            </div>
            <div class="form-group col-md-8 field-container" id="td_business_processes">
                {*$OPEN_CASES|var_dump*}
                {block name="open_cases"}{/block}
            </div>
        </div>
        <div class="col-md-6">
            <div class="col-md-4">
                <button type="button"
                        class="btn btn-success"
                        onclick="ProcessCaseUtils.joinProcessCase (this, '{$idProcessDetailView}')"
                        title="Crear caso">&nbsp;<span class="hidden"><i class="fa fa-spinner " aria-hidden="true"></i>
                                </span>Crear caso&nbsp;
                </button>
            </div>
            <div class="col-md-6">&nbsp</div>
        </div>
        <div class="col-md-12 border-bottom" style="margin-bottom: 3px;margin-top: 3px; margin-left: -10px;">
            <p class="text-left" style="font-weight: bold;"></p>
        </div>
    </div>
    {block name="js_script"}{/block}
{/strip}