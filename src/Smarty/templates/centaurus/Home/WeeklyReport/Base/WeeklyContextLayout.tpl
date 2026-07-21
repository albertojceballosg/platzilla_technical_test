{strip}
    {math equation= rand() assign= "idWeeklyPanel"}
    {block name="css"}{/block}
    <div class="row">
        {if $MESSAGE eq NULL}
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 1px; margin-bottom: 0!important;">
                <ul class="nav nav-tabs nav-platzilla pull-right" id="MAIN-TAB">
                    {* Operational objetives *}
                    <li {if ($SELECTED_TAB eq 'PLANNING_COMPLIANCE')} class="active" {else} class="" {/if}>
                        <a data-toggle="tab"  href="#PLANNING_COMPLIANCE-{$idWeeklyPanel}">{$MOD['LBL_CONTEXT_THIS_WEEK']}</a>
                    </li>
                    {* Box Score *}
                    <li {if ($SELECTED_TAB eq 'BOX_SCORE')} class="active" {else} class=""{/if}>
                        <a data-toggle="tab" rel="{$TARGET_INSTANCE}"
                           data-loading="FALSE" data-module="Home" data-period="{$PERIOD_TIME}"
                           onclick="ReportRailesUtils.getBoxScoreReport (this,'{$idWeeklyPanel}')"
                           href="#BOX_SCORE-{$idWeeklyPanel}">{$MOD['LBL_CONTEXT_BOX_SCORE']}</a>
                    </li>
                    {* Continuos Improvement *}
                    <li {if ($SELECTED_TAB eq 'NEXT_WEEK')} class="active" {else} class=""{/if}>
                        <a data-toggle="tab"  href="#NEXT_WEEK-{$idWeeklyPanel}">{$MOD['LBL_CONTEXT_NEXT_WEEK']}</a>
                    </li>
                </ul>
                <div class="main-box-body clearfix">
                    <div class="tab-content">
                        <div id="PLANNING_COMPLIANCE-{$idWeeklyPanel}" style="padding-top: 15px"
                             class="tab-pane fade in{if ($SELECTED_TAB eq 'PLANNING_COMPLIANCE')} active{/if}">
                            {include file="Home/WeeklyReport/Objects/WkeeklyPanelGroup.tpl"}
                        </div>
                        {* context Planning compliance *}
                        <div id="NEXT_WEEK-{$idWeeklyPanel}"  style="padding-top: 15px"
                             class="tab-pane fade in{if ($SELECTED_TAB eq 'NEXT_WEEK')} active{/if}">
                            {include file="Home/WeeklyReport/Objects/UpcomigPanelGroup.tpl"}
                        </div>
                        {* Box Score *}
                        <div id="BOX_SCORE-{$idWeeklyPanel}"  style="padding-top: 15px"
                             class="tab-pane fade in{if ($SELECTED_TAB eq 'BOX_SCORE')} active{/if}">
                            {include file="utils/HTMLPageLoanding.tpl"}
                        </div>
                    </div>
                </div>
            </div>
        {else}
            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
                <div class="alert alert-info" role="alert">
                    <strong>¡Atención!</strong> {$MESSAGE}.
                </div>
            </div>
        {/if}
    </div>
    {block name="js"}{/block}
{/strip}

