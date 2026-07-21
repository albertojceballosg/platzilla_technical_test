{strip}
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                <div class="table-responsive">
                    {block name="processes_name"}{/block}
                </div>
                <div class="table-responsive">
                    {block name="summary_finished_cases"}{/block}
                </div>
                {block name="chart_finished_case"}{/block}
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="table-responsive" style="padding-right: 4.5px;max-height: 500px; overflow-y: auto;margin-bottom: 10px">
                    {block name="table_finished_cases_involved"}{/block}
                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                <div class="table-responsive">
                    {block name="summary_unfinished_cases"}{/block}
                </div>
                {block name="chart_unfinished_case"}{/block}
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="table-responsive" style="padding-right: 4.5px;max-height: 500px; overflow-y: auto">
                    {block name="table_unfinished_cases_involved"}{/block}
                </div>
            </div>
        </div>
    </div>
    {block name="script"}{/block}
{/strip}