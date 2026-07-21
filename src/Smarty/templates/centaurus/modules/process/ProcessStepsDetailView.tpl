{extends file='modules/process/base/ProcessStepsLayout.tpl'}

{assign var="colStyle1" value="width:16%;vertical-align: top;"}
{assign var="colStyle2" value="width:14%;vertical-align: top;"}
{assign var="colStyle3" value="width:14%;vertical-align: top;"}
{assign var="colStyle4" value="width:13%;vertical-align: top;"}
{assign var="colStyle5" value="width:14%;vertical-align: top;"}
{assign var="colStyle6" value="width:11%;vertical-align: top;;text-align: center"}
{assign var="colStyle7" value="width:10%;vertical-align: top;text-align: center"}

{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
{/block}
{block name="table_margin"}style="margin-top: 6px"{/block}
{block name="card_header"}
    <div class="row card-header platzilla-card-header" style="padding-left: 0!important;">
        <div class="col-md-5">
                <p class="text-center pull-left" style="font-weight: bold">Pasos del proceso</p>
        </div>
        <div class="col-md-7">&nbsp;</div>
    </div>
{/block}
{block name="colspan_header"}colspan="8"{/block}
{block name="col_1"} class="text-center" style="{$colStyle1}" {/block}
{block name="col_2"} class="text-center" style="{$colStyle2}" {/block}
{block name="col_3"} class="text-center" style="{$colStyle3}" {/block}
{block name="col_4"} class="text-center" style="{$colStyle4}" {/block}
{block name="col_5"} class="text-center" style="{$colStyle5}" {/block}
{block name="col_6"} class="text-center" style="{$colStyle6}" {/block}
{block name="col_7"} class="text-center" style="{$colStyle7}" {/block}
{block name="col_action"}&nbsp;{/block}
{block name="tbody_process_steps"}
    {*$PROCESS_STEPS|var_dump*}
    {if $PROCESS_STEPS neq NULL}
        {foreach $PROCESS_STEPS as $key => $processStep}
            {math equation= rand() assign= "idRow"}
            {include file='modules/process/ProcessStepsView_template.tpl'}
        {/foreach}
    {else}
        {assign var="key" value= -1}
        <tr>
            <td colspan="7" style="text-align: center"></td>
        </tr>
    {/if}
{/block}
{block name="summaryRow"}{/block}
{block name="addRow"}{/block}
{block name="global_task"}{/block}
{block name="script"}{/block}
{block name="script_template"}{/block}