{extends file='modules/process/base/ProcessViewLayout.tpl'}
{block name="css"}
    <style>
        .border{
            border:1px solid #dee2e6!important
        }
    </style>
{/block}
{block name="open_cases"}
    <select id="business_processes-{$idProcessDetailView}" name="business_processes"
            class="form-control for-filter border"
            tabindex="">
            {*onchange="if (window.onchange_process_area) onchange_process_area(this)"*}>
        {if $AVAILABLE_PROCESS neq NULL}
            <optgroup label="Modelos de procesos">
                {foreach $AVAILABLE_PROCESS  as $process}
                    <option data-type="proceso"
                            value="{$process.processid}">{$process.process_title}</option>
                {/foreach}
            </optgroup>
            {if $OPEN_CASES neq NULL}
                <optgroup label="Incluir en un caso abierto">
                    {foreach $OPEN_CASES  as $case}
                        <option value="{$case.case_number}"
                                data-type="open-case">{$case.case_number}</option>
                    {/foreach}
                </optgroup>
            {/if}
        {else}
            <option value="" selected="selected">Modelo de procesos</option>
        {/if}
    </select>
{/block}
{block name="js_script"}{/block}
