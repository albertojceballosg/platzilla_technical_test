{strip}
    {math equation= rand() assign= "idProcessDetailView"}
    {block name="css"}{/block}
    {*$CASES_PROCESS|var_dump*}
    {*$IS_FINISH_PROCESS|var_dump*}
    <div id="select_process" class="row" style="margin-bottom: 5px; margin-left: 10px">
        <div class="col-md-12" style="margin: 5px 0;padding: 4px 0">
            <div class="col-md-4">
                <div class="label-input">
                    <label for="process_area" class="">
                        {if $process neq NULL}
                            <span id="p1_cases_process">Proceso:&nbsp;{$process}</span>
                        {/if}
                    </label>
                </div>
            </div>
            <div id="process_graphic" class="col-md-8" style="vertical-align: bottom; padding-top: 12px">
                <div class="wrap-platzilla" style="text-align: center">
                    <input type="hidden" id="case_id-{$idProcessDetailView}" value="{$CASE_ID}">
                    <input type="hidden" id="module-{$idProcessDetailView}" value="{$MODULE}">
                    <div class="line" style="width: {$LINE_WIDTH}px;"></div>
                    <p class="step" style="width: 0;"></p>
                    {block name="process_steps"}{/block}
                </div>
            </div>
        </div>
        <div class="col-md-12 border-bottom" style="margin-bottom: 3px;margin-top: 3px; margin-left: -10px;">
            <p class="text-left" style="font-weight: bold;"></p>
        </div>
    </div>
    {block name="js_script"}{/block}
{/strip}