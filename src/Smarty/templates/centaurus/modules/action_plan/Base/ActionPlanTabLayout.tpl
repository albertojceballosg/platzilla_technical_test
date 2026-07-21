{block name="css"}{/block}
{block name="js"}{/block}
<section class="">
    <div class="container" {block name="container_id"}{/block}">
        <div class="row">
            <div class="card rounded" style="margin-bottom: 2px!important;padding 0.25em 1.2em!important;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 col-xs-12 card-header platzilla-card-header">
                            <p class="text-center" style="font-weight: bold;margin-bottom: 10px">{block name="tab_title"}{/block}</p>
                        </div>
                        <div class="col-lg-12 col-md-12 col-xs-12">
                            {block name="tab_content"}{/block}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>