{extends file='Home/TabsContents/Base/PanelContentLayOut.tpl'}
{block name="resumen_processes"}
    <table class="table table-bordered table-condensed" name="resumen_processes_t">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td>{$MOD['LBL_RESUMEN_PROCESSES']}</td>
            <td style="text-align: right">{$RESUMEN_PROCESSES}</td>
        </tr>
        <tr>
            <td>{$MOD['LBL_FINALIZED_PROCESSES']}</td>
            <td style="text-align: right">{$TOTAL_CASE_FINISHED}</td>

        </tr>
        <tr>
            <td>{$MOD['LBL_INCOMPLETE_PROCESSES']}</td>
            <td style="text-align: right">{$TOTAL_CASE_UNFINISHED}</td>

        </tr>
        <tr style="background-color: #efefef">
            <td>{$MOD['LBL_TOTAL_CASES']}</td>
            <td>{$TOTAL_CASE}</td>

        </tr>
        </tbody>
    </table>
{/block}
{block name="summay_cases"}
    <table class="table table-bordered table-condensed" name="summary_cases_t">
        <thead>
        </thead>
        <tbody>
        <tr style="background-color: #fcfc76">
            <td>{$MOD['LBL_FINALIZED_CASES']}</td>
            <td>{$FINISHED_OUT_AVERAGE}</td>
        </tr>
        <tr style="background-color: #fcfc76">
            <td>{$MOD['LBL_INCOMPLETE_CASES']}</td>
            <td>{$UNFINISHED_OUT_AVERAGE}</td>

        </tr>
        </tbody>
    </table>
{/block}
{block name="finalized_processes"}
    {*$PROCESS_FINISHED|var_dump*}
    <table class="table table-bordered table-condensed" name="finalized_processes_t">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold">{$MOD['TITLE_FINALIZED_PROCESSES']}</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; background-color: #4285f4">Proceso</td>
        </tr>
        {if $PROCESS_FINISHED neq NULL}
            {foreach $PROCESS_FINISHED as $process}
                <tr>
                    <td style="text-align: left; width: 84%">
                        <a  title="Ver detalles del proceso: {$process['process']}"
                            style="color: #000000"
                            href="#"
                            onclick="ProcessCasesUtils.getProcessDetailView(this, event)"
                            data-id="{if isset($HOME_TAB_ID)}{$HOME_TAB_ID}{else}{$idPanelProcess}{/if}"
                            rel="{$process['processId']}">
                            {$process['process']}
                        </a>
                    </td>
                    <td style="text-align: center; width: 8%">{$process['total']}</td>
                    <td style="text-align: center; width: 8%">
                        <i class="fa fa-circle fa-2x" aria-hidden="true" style="color: {$CONTROL_BANDS[$process['state']]}"></i>
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="3" style="text-align: center; font-weight: bold">{$MOD['LBL_NOT_PROCESSES']}</td>
            </tr>
        {/if}
        </tbody>
    </table>
{/block}
{block name="incomplete processes"}
    {*$PROCESS_UMFINISHED|var_dump*}
    <table class="table table-bordered table-condensed" name="incomplete processes_t">
        <thead>
        </thead>
        <tbody>
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold">{$MOD['TITLE_INCOMPLETE_PROCESSES']}</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left; background-color: #ffab40">Proceso</td>
        </tr>
        {if $PROCESS_UMFINISHED neq NULL}
            {foreach $PROCESS_UMFINISHED as $process}
                <tr>
                    <td style="text-align: left; width: 84%">
                        <a  title="Ver detalles del proceso: {$process['process']}"
                            style="{*text-decoration: none;*} color: #000000"
                            href="#"
                            onclick="ProcessCasesUtils.getProcessDetailView(this, event)"
                            data-id="{if isset($HOME_TAB_ID)}{$HOME_TAB_ID}{else}{$idPanelProcess}{/if}"
                            rel="{$process['processId']}">
                            {$process['process']}
                        </a>
                    </td>
                    <td style="text-align: center; width: 8%">{$process['total']}</td>
                    <td style="text-align: center; width: 8%">
                        <i class="fa fa-circle fa-2x" aria-hidden="true" style="color: {$CONTROL_BANDS[$process['state']]}"></i>
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td colspan="3" style="text-align: center; font-weight: bold">{$MOD['LBL_NOT_PROCESSES']}</td>
            </tr>
        {/if}
        </tbody>
    </table>
{/block}