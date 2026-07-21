{extends file='modules/action_plan/Base/KeyObjResultLayout.tpl'}
{block name="css"}
    <link rel="stylesheet" type="text/css" href=modules/grid_view/grid-view.css"/>
{/block}
{block name="table_margin"}style="margin-top: 20px"{/block}
{block name="colspan_header"}colspan="4"{/block}
{block name="header_title"}Plan de Mejoras: Objetivos y OKR{/block}
{block name="header_column"}
    <th class="col-lg-6 col-md-6 col-sm-6">Objetivo</th>
    <th class="col-lg-4 col-md-4 col-sm-4" colspan="2">% de avance</th>
    <th class="col-lg-1 col-md-1 col-sm-1 text-center">Acciones</th>
{/block}
{block name="tbody_okr_action_plan"}
    {if $OKR neq NULL}
        {assign var="sequence" value=$OKR|count}
           {include file='modules/action_plan/Objects/KeyObjResultRowEditView.tpl'}
    {else}
        {assign var="sequence" value=0}
    <tr>
        <td colspan="4" style="text-align: center"></td>
    </tr>
    {/if}
{/block}
{block name="summaryRow"}{/block}
{block name="addRow"}
    <tr>
        <td colspan="5" class="text-center">
            <button type="button" data-id-linkage="{$idKeyObjResult}" class="btn btn-primary"
                    data-sequence="{$sequence}"
                    onclick="ActionPlanUtls.addRowToTable (this, 'tbody-okr-action-plan-{$idKeyObjResult}', '{$idKeyObjResult}');">
                <i class="fa fa-plus"></i></button>
        </td>
    </tr>
{/block}
{block name="script_template"}
    <script type="text/html" id="objetice-template-{$idKeyObjResult}">
        {include file='modules/action_plan/Objects/KeyObjResultRowView.tpl'}
    </script>
{/block}