{strip}
    <div class="table-responsive field-container">
        <table id="okr-action-plan-table-{$idOKRPlan}" class="table table-bordered tablegridvalidate">
            <thead></thead>
            <tbody id="tbody-okr-action-plan-{$idOKRPlan}">
            {assign var="index" value=0}
            {foreach $OKR as $okr}
                <tr>
                    <td class="col-lg-1 col-md-1 col-sm-1 text-center" style="background-color: #6d9eeb"><strong>Objetivo:</strong></td>
                    <td class="col-lg-7 col-md-7 col-sm-7" style="background-color: #c9daf8">
                        <div class="input-group text-left" style="width: 100%;">
                            <a href="index.php?module=business_objective&parenttab=&action=DetailView&record={$okr['kr_achieve_objective']['business_objectivetfid'][$index]}"
                               target="_blank" title="{$okr['objective_name']}">{$okr['objective_name']}</a>
                        </div>
                    </td>
                    <td class="col-lg-2 col-md-2 col-sm-2 text-center" style="background-color: #6d9eeb"><strong>% avance</strong></td>
                    <td class="col-lg-2 col-md-2 col-sm-2 text-center" style="background-color: #c9daf8">{$okr['goal_progress']}</td>
                </tr>
                <tr>
                    <td class="col-lg-12 col-md-12 col-sm-12" colspan="4">
                        {assign var='TOTAL_ROW' value=$okr['total_objetives']}
                        {assign var='KR' value=$okr['kr_achieve_objective']}
                        {assign var='ID_TABLE' value=$idOKRPlan}
                        {include file="modules/action_plan/KeyResultEditView.tpl"}
                    </td>
                </tr>
                {assign var="index" value=$index+1}
            {/foreach}
            </tbody>
        </table>
    </div>
{/strip}