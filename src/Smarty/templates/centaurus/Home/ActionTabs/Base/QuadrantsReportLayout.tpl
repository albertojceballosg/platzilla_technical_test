{strip}
    <div class="row" style="margin-top: 6px">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
            {block name="daily-matrix-quadrants"}{/block}
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4"
             style="height: 100%; vertical-align: bottom;text-align: center;margin-left: 0!important;">
            {block name="daily-matrix-graphics"}{/block}
        </div>
    </div>
    <div class="row" style="margin-top: 6px">
        <div class="col-lg-12 col-md-12 col-ms-12">
            {assign var="isCollapsed" value=null}
            {block name="daily-report-suggestions-news"}{/block}
            {block name="daily-report-problems-detected"}{/block}
            {block name="daily-report-achievements"}{/block}

        </div>
    </div>
    {block name="js"}{/block}
{/strip}