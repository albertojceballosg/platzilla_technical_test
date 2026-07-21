{strip}
    {math equation= rand() assign= "idBoxScore"}
    {block name="css"}{/block}
    <div class="row">
        <div class="col-lg-12" style="padding-right: 20px;height: 46px!important;">
            <div class="pull-left">
                <h1 class="pull-left" style="font-weight: bold">{$MODSTRING.indicatorspanel}</h1>
            </div>
            <div class="pull-right row" style="display: inline"></div>
        </div>
    </div>
    <div class="container-fluid base-list-container">
        <div class="main-box clearfix" style="padding-top: 10px">
            <div class="tabs-wrapper">
                {block name="nav_tab"}{/block}
                {block name="body_content"}{/block}
            </div>
        </div>
    </div>
    {block name="indicators_modal"}{/block}
    {block name="js"}{/block}
{/strip}