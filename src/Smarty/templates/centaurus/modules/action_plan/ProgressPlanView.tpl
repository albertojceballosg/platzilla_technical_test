{math equation= rand() assign= "idSummaryPlan"}
<script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
<link rel="stylesheet" type="text/css" href="modules/model_action_plan/model-action-plan.css">
<section class="">
    <div class="container" id="main-{$idSummaryPlan}">
        <div class="row">
            <div class="card rounded" style="margin-bottom: 2px!important;padding 0.25em 1.2em!important;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-xs-12 card-header platzilla-card-header">
                            <p class="text-center" style="font-weight: bold;margin-bottom: 10px">Avance del Plan:&nbsp;{$PLAN['action_plan_name']}</p>
                        </div>
                        <div class="col-lg-12 col-md-12 col-xs-12">
                                    {if $PLAN['plan_initiatives'] neq NULL}
                                        {assign var="totalInitiative" value=$PLAN['plan_initiatives']['action_plantfid']|count}
                                        <table id="plan_initiatives-table"
                                               class="table table-bordered tablegridvalidate">
                                            <thead>
                                            <tr valign="top">
                                                <td class="text-center" width="47%">
                                                    <span style="font-weight: bold">Iniciativa</span>
                                                </td>
                                                 <td class="text-center" width="13%">
                                                     <span style="font-weight: bold">Fecha de inicio</span>
                                                 </td>
                                                <td class="text-center" width="13%">
                                                    <span style="font-weight: bold">% importancia</span>
                                                </td>
                                                <td class="text-center" width="13%">
                                                    <span style="font-weight: bold">% avance inicitiva</span>
                                                </td>
                                                <td class="text-center" width="13%">
                                                    <span style="font-weight: bold">% avance del plan</span>
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
                                                    <td class="text-right"
                                                        style="padding: 0 6px">{$PLAN['plan_initiatives']['init_date'][$k]}</td>
                                                    <td class="text-right"
                                                        style="padding: 0 6px">{$PLAN['plan_initiatives']['importance_factor'][$k]}</td>
                                                    <td class="text-right"
                                                        style="padding: 0 6px">{$PLAN['plan_initiatives']['progress_initiative_'][$k]}</td>
                                                    <td class="text-right"
                                                        style="padding: 0 6px">{$PLAN['plan_initiatives']['progress_plan'][$k]}</td>
                                                </tr>
                                            {/for}
                                            {if $SUMMARY_ROW}
                                                <tr>
                                                    <td><span class="text-center">TOTALES</span></td>
                                                    <td class="text-right"
                                                        style="padding: 0 6px">&nbsp;</td>
                                                    <td class="text-right"
                                                        style="padding: 0 6px">{$SUMMARY_ROW['importance_factor']}</td>
                                                    <td class="text-right"
                                                        style="padding: 0 6px">&nbsp;</td>
                                                    <td class="text-right"
                                                        style="padding: 0 6px">{$SUMMARY_ROW['progress_plan']}</td>
                                                </tr>
                                            {/if}
                                            </tbody>
                                        </table>
                                    {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>