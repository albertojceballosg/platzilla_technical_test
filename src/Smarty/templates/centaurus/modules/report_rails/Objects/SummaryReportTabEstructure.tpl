{strip}
    <div class="main-box-body clearfix">
        <div class="tab-content">
            {* Summary report *}
            <div id="SUMMARY_REPORT-{$idSummaryReport}" style="padding-top: 15px"
                 class="tab-pane fade in{if ($SELECTED_TAB eq 'SUMMARY_REPORT')} active{/if}">
                {block name="summary_report"}{/block}
            </div>
            {* context Planning compliance *}
            <div id="PLANNING_COMPLIANCE-{$idSummaryReport}"
                 class="tab-pane fade in{if ($SELECTED_TAB eq 'PLANNING_COMPLIANCE')} active{/if}">
                {block name="planning_compliance"}{/block}
            </div>
            {* context Next week *}
            <div id="NEXT_WEEK-{$idSummaryReport}"
                 class="tab-pane fade in{if ($SELECTED_TAB eq 'NEXT_WEEK')} active{/if}">
                {block name="next_week"}{/block}
            </div>
            {* context box score *}
            <div id="BOX_SCORE-{$idSummaryReport}"
                 class="tab-pane fade in{if ($SELECTED_TAB eq 'BOX_SCORE')} active{/if}">
                {block name="box_score"}{/block}
            </div>
            {* Objetives *}
            {* Operational objetives *}
            <div id="OBJETIVES_OPERATIONAL-{$idSummaryReport}"
                 class="tab-pane fade in{if ($SELECTED_TAB eq 'OBJETIVES_OPERATIONAL')} active{/if}">
                {block name="objetives_operational"}{/block}
            </div>
            {* Objetives *}
            {* Continuos Improvement *}
            <div id="CONTINUOUS_IMPROVEMENT-{$idSummaryReport}"
                 class="tab-pane fade in{if ($SELECTED_TAB eq 'CONTINUOUS_IMPROVEMENT')} active{/if}">
                {block name="continuous_improvement"}{/block}
            </div>
            {* Objetives *}
            {* traking *}
            <div id="TRACKING-{$idSummaryReport}"
                 class="tab-pane fade in{if ($SELECTED_TAB eq 'TRACKING')} active{/if}">
                {block name="tracking"}{/block}
            </div>
            {* Performace config panel *}
            <div id="PERFORMANCE-{$idSummaryReport}"
                 class="tab-pane fade in{if ($SELECTED_TAB eq 'PERFORMANCE')} active{/if}">
                {block name="performance"}{/block}
            </div>
            {* Performace config panel *}
            <div id="AGREEMENTS-{$idSummaryReport}"
                 class="tab-pane fade in{if ($SELECTED_TAB eq 'AGREEMENTS')} active{/if}">
                {block name="agreements"}{/block}
            </div>
        </div>
    </div>
{/strip}