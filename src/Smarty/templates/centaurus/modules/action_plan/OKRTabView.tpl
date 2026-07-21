{extends file='modules/action_plan/Base/ActionPlanTabLayout.tpl'}
{math equation= rand() assign= "idOKRPlan"}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/model_action_plan/model-action-plan.css">
{/block}
{block name="js"}
    <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
{/block}
{block name="container_id"}id="main-{$idOKRPlan}"{/block}
{block name="tab_title"}OKRs del Plan:&nbsp;{$PLAN['action_plan_name']}{/block}
{block name="tab_content"}
    {if $OKR neq NULL}
        {include file='modules/action_plan/Objects/KeyObjResultTabDetailView.tpl'}
    {else}
        <div class="alert alert-info" role="alert">
            <strong>¡No hay OKRs!</strong> No se han encontrado OKRs para este plan.<br>
            <div>
                <a href="javascript:void(0)"
                   onclick="DetailView.return_module.value='action_plan';
                           DetailView.return_action.value='DetailView';
                           DetailView.return_id.value='{$PLAN_ID}';
                           DetailView.module.value='action_plan';
                           submitFormForAction('DetailView','EditView');" id="editButton"
                   class="" style="margin-left:.5em; margin-right: 0;">
                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i> Editar Plan para incluir OKRs
                </a>
            </div>
        </div>
    {/if}
{/block}
