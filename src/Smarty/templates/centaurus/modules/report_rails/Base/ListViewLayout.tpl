{strip}
    {math equation= rand() assign= "idSummaryReport"}
    {block name="css"}{/block}
    <div id="" class="clearfix summary-body">
        <div class="col-xs-12">
            <div class="main-box clearfix">
                <header class="main-box-header clearfix">
                    <div class="col-xs-12 text-right">
                        <a href="{block name="url_created_file"}{/block}"
                           {block name="click_created_action"}{/block}
                           class="btn btn-primary">
                            <i class="fa fa-plus-circle"></i>{block name="lbl_created_btn"}{/block}
                        </a>
                    </div>
                </header>
                <div class="main-box-body clearfix" id="ListViewContents">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                {block name="table_header"}{/block}
                            </tr>
                            </thead>
                            <tbody>
                            {block name="table_body"}{/block}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {block name="js"}{/block}
{/strip}