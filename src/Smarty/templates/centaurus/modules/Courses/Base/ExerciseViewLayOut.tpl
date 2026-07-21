{strip}
    {block name="css"}{/block}
    <div class="row">
        <div class="col-xs-12">
            <h1 class="pull-left">{block name="link_title"}{/block}</h1>
            <div class="pull-right">
                <div class="btn-group">
                    {block name="navi_page"}{/block}
                </div>
            </div>
        </div>
    </div>
    <div class="main-box no-header">
        <div class="main-box-body">
            {block name="exercise_contenet"}{/block}
            {block name="resource_contenet"}{/block}
            {block name="response_contenet"}{/block}
            {block name="btn_finish"}{/block}
        </div>
    </div>
    {block name="script"}{/block}
    {block name="script_template"}{/block}
{/strip}