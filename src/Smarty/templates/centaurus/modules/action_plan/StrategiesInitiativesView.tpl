{math equation= rand() assign= "idStrategieInitiative"}
<script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
<link rel="stylesheet" type="text/css" href="modules/model_action_plan/model-action-plan.css">
<section class="">
    <div class="container" id="main-{$idStrategieInitiative}">
        <div class="row">
            <div class="card rounded" style="margin-bottom: 2px!important;padding 0.25em 1.2em!important;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-xs-12 card-header platzilla-card-header">
                            <p class="text-center" style="font-weight: bold;margin-bottom: 10px">{$PLAN['action_plan_name']}</p>
                        </div>
                        <div class="col-lg-12 col-md-12 col-xs-12">
                            <div class="row">
                                <div class="col-lg-7 col-md-7 col-xs-7">

                                        <ul class="list-group">
                                            <li class="list-group-item" style="background:#f9f8f7;font-weight: bold">
                                                Directrices del plan: Enunciado
                                            </li>
                                            {if $PLAN['plan_directives'] neq NULL}
                                            {foreach $PLAN['plan_directives']['directive_enunciate_'] as $enunciate}
                                                <li class="list-group-item">{$enunciate}</li>
                                            {/foreach}
                                            {else}
                                                <li class="list-group-item">&nbsp;&nbsp;</li>
                                            {/if}
                                        </ul>
                                </div>
                                <div class="col-lg-5 col-md-5 col-xs-5" style="padding-top: 0">
                                    <div class="center-block" id="columnchart_values" style="vertical-align: top;width:100%;padding-left: -30%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 col-xs-12">
                            <div class="row-model-action justify-content-center">
                                <div class="col-md-10">
                                    {if $PLAN['plan_initiatives'] neq NULL}
                                        {assign var="totalInitiative" value=$PLAN['plan_initiatives']['action_plantfid']|count}
                                        {* <div class="col-xs-12 col-md-12 col-lg-12"
                                              style="margin: 10px 0"> *}
                                        <table id="plan_initiatives-table"
                                               class="table table-bordered tablegridvalidate">
                                            <thead>
                                            <tr valign="top">
                                                <td class="text-center" width="45%">
                                                    <span style="font-weight: bold">Iniciativa</span>
                                                </td>
                                                {*
                                                 <td class="text-center" width="10%">
                                                     <span style="font-weight: bold">% importancia</span>
                                                 </td>
                                                 *}
                                                <td class="text-center;" width="45%">
                                                    <span style="font-weight: bold">Beneficios</span>
                                                </td>
                                            </tr>
                                            </thead>
                                            <tbody id="inititives-{$idActionPlan}">
                                            {for $k= 0 to ($totalInitiative -1)}
                                                <tr id="{$PLAN['plan_initiatives']['plan_initiativeid'][$k]}">
                                                    <td>
                                                        <div class="input-group text-left" style="width: 100%;">
                                                            <a href="index.php?module=business_initiatives&;parenttab=&action=DetailView&record={$PLAN['plan_initiatives']['plan_initiativeid'][$k]}"
                                                               target="_blank"
                                                               title="action_plan">{$PLAN['plan_initiatives']['plan_initiative'][$k]}
                                                            </a>
                                                        </div>
                                                    </td>
                                                    {*<td class="text-right"
                                                        style="padding: 0 6px">{$PLAN['plan_initiatives']['importance_factor'][$k]}</td>*}
                                                    <td>{$PLAN['plan_initiatives']['initiative_benefit'][$k]}</td>
                                                </tr>
                                            {/for}
                                            </tbody>
                                        </table>
                                        {* </div> *}
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
<script type="text/javascript" src="modules/action_plan/action_plan.js"></script>

<script type="text/javascript">
    ActionPlanUtls.initGuideline ({$DATA_TABLE}, '');
</script>