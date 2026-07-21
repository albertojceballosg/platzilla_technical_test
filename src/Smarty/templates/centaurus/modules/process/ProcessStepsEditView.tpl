{assign var="$PROCESS_CASES_UTILS_LOADED" value=false}
{extends file='modules/process/base/ProcessStepsLayout.tpl'}

{assign var="colStyle1" value="width:15.2%;vertical-align: top;"}
{assign var="colStyle2" value="width:15%;vertical-align: top;"}
{assign var="colStyle3" value="width:16%;vertical-align: top;"}
{assign var="colStyle4" value="width:11%;vertical-align: top;"}
{assign var="colStyle5" value="width:11%;vertical-align: top;"}
{assign var="colStyle6" value="width:10%;vertical-align: top;;text-align: center"}
{assign var="colStyle7" value="width:10%;vertical-align: top;text-align: center"}
{assign var="colStyle8" value="width:12%;vertical-align: top;text-align: center"}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
{/block}
{block name="table_margin"}style="margin-top: 20px"{/block}
{block name="card_header"}{/block}
{block name="colspan_header"}colspan="8"{/block}
{block name="col_1"} class="text-center" style="{$colStyle1}" {/block}
{block name="col_2"} class="text-center" style="{$colStyle2}" {/block}
{block name="col_3"} class="text-center" style="{$colStyle3}" {/block}
{block name="col_4"} class="text-center" style="{$colStyle4}" {/block}
{block name="col_5"} class="text-center" style="{$colStyle5}" {/block}
{block name="col_6"} class="text-center" style="{$colStyle6}" {/block}
{block name="col_7"} class="text-center" style="{$colStyle7}" {/block}
{block name="col_8"}{/block}
{block name="col_action"}
<td class="text-center" style="{$colStyle8}" class="text-center">Acciones</td>
{/block}
{block name="tbody_process_steps"}
    {*$PROCESS_STEPS|var_dump*}
    {if $PROCESS_STEPS neq NULL}
        {foreach $PROCESS_STEPS as $key => $processStep}
            {math equation= rand() assign= "idRow"}
            {include file='modules/process/ProcessSteps_template.tpl'}
        {/foreach}
    {else}
        {assign var="key" value= -1}
        <tr>
            <td colspan="8" style="text-align: center"></td>
        </tr>
    {/if}
{/block}
{block name="summaryRow"}{/block}
{block name="addRow"}
    <tr>
        <td colspan="8" class="text-center">
            <button type="button" data-id-linkage="{$idProcessSteps}" class="btn btn-primary"
                    data-sequence="{($key + 1)}"
                    onclick="ProcessStepsUtls.addRowToTable (this, 'tbody-process-steps-{$idProcessSteps}', '{$idProcessSteps}');">
                <i class="fa fa-plus"></i></button>
        </td>
    </tr>
{/block}
{block name="global_task"}{/block}
{block name="script"}{/block}
{block name="script_template"}
    <script type="text/html" id="process_steps-template-{$idProcessSteps}">
        {include file='modules/process/ProcessStepsRow_template.tpl'}
    </script>
    <script type="text/html" id="process_steps-tr-{$idProcessSteps}">
        <tr>
            <td colspan="7" style="text-align: center"></td>
        </tr>
    </script>
{/block}