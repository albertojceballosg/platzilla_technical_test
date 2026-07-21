{strip}
    {math equation= rand() assign= "idProgressJob"}
    {* Job report *}
    <div class="row-grid-view justify-content-center" {if $isAction eq 'YES'}style="display: none"{/if}>
        <div class="col-md-12" {if $VIEW neq NULL}style="margin-top: 0"{/if}>
            <div class="table-responsive">
                <table id="job-report-table-{$idProgressJob}" class="table table-bordered tablegridvalidate">
                    <thead>
                    {block name="thead-job-report"}{/block}
                    </thead>
                    <tbody id="tbody-job-report-{$idProgressJob}" rowtotal="0" data-num-format="{$NUMBERING_FORMAT}">
                    {block name="tbody-job-report"}{/block}
                    </tbody>
                    <tfoot id="tfoot-job-report-{$idProgressJob}" data-summary-row="" data-operation-row="">
                    {block name="summary-job-report-row"}{/block}
                    {block name="add-job-report-row"}{/block}
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    {* Planned tasks *}
    <div class="row-grid-view justify-content-center">
        <div class="col-md-12" style="margin-top: 5px">
            <div class="table-responsive">
                <table id="planned-tasks-table-{$idProgressJob}" class="table table-bordered tablegridvalidate">
                    <thead>
                    {block name="thead-planned-tasks"}{/block}
                    </thead>
                    <tbody id="tbody-planned-tasks-{$idProgressJob}" rowtotal="0" data-num-format="{$NUMBERING_FORMAT}">
                    {block name="tbody-planned-tasks"}{/block}
                    </tbody>
                    <tfoot id="tfoot-planned-tasks-{$idProgressJob}" data-summary-row="" data-operation-row="">
                    {block name="summary-planned-tasks-row"}{/block}
                    {block name="add-planned-tasks-row"}{/block}
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    {* Tasks performed and not previously recorded *}
    <div class="row-grid-view justify-content-center">
        <div class="col-md-12" style="margin-top: 5px">
            <div class="table-responsive">
                <table id="tasks-performed-table-{$idProgressJob}" class="table table-bordered tablegridvalidate">
                    <thead>
                    {block name="thead-tasks-performed"}{/block}
                    </thead>
                    <tbody id="tbody-tasks-performed-{$idProgressJob}" rowtotal="0" data-num-format="{$NUMBERING_FORMAT}">
                    {block name="tbody-tasks-performed"}{/block}
                    </tbody>
                    <tfoot id="tfoot-tasks-performed-{$idProgressJob}" data-summary-row="" data-operation-row="">
                    {block name="summary-tasks-performed-row"}{/block}
                    {block name="add-tasks-performed-row"}{/block}
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    {block name="script_template"}{/block}
{/strip}