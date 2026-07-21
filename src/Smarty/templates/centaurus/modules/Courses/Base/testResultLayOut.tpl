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
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12 text-center">
                    <h2 class="danger"> Feedback y Comentarios</h2>
                </div>
                <h4 class="pull-left"><!--Recuerda:-->&nbsp;</h4>
                {block name="feedback_content"}{/block}
                {block name="test_result"}{/block}
            </div>
            {block name="question_result"}{/block}
            {block name="question_result_feedback"}{/block}
        </div>
    </div>
    {block name="script"}{/block}
    {block name="script_template"}{/block}
{/strip}