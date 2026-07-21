{extends file='modules/action_plan/Base/KeyResultLayout.tpl'}
{block name="table_margin"}{/block}
{block name="colspan_header"}colspan="3"{/block}
{block name="header_title"}Objetivos y OKR{/block}
{block name="header_column"}
    <th class="col-lg-8 col-md-8 col-sm-8">Objetivo</th>
    <th class="col-lg-2 col-md-2 col-sm-2" >% de avance KR</th>
    <th class="col-lg-2 col-md-2 col-sm-2 text-center" >% de avance Objetivo</th>
{/block}
{block name="tbody_kr-business_objective"}
    {if $KR neq NULL}
        {include file='modules/action_plan/Objects/KeyResultRowsView.tpl'}
    {else}
    <tr>
        <td colspan="3" style="text-align: center">No hay KR definidos</td>
    </tr>
    {/if}
{/block}
