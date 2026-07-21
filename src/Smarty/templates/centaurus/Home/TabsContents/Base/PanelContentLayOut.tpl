{strip}
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12" style="margin-top: 10px">
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="table-responsive">
                {block name="resumen_processes"}{/block}
                </div>
                <div class="table-responsive">
                    {block name="summay_cases"}{/block}
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="table-responsive" style="padding-right: 4.5px;max-height: 500px; overflow-y: auto">
                {block name="finalized_processes"}{/block}
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="table-responsive" style="padding-right: 4.5px;max-height: 500px; overflow-y: auto">
                {block name="incomplete processes"}{/block}
                </div>
            </div>
        </div>
    </div>
{/strip}