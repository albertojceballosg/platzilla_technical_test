{strip}
    {math equation= rand() assign= "idPanel"}
    {block name="css"}{/block}
    <div class="row">
        {if $MESSAGE eq NULL}
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
            <div aria-multiselectable="true" class="panel-group" id="accordion" role="tablist">
                {* Panel Estado *}
                <div class="panel panel-default">
                    <div class="panel-heading" id="panel-heading-status-{$idPanel}" role="tab">
                        <h4 class="panel-title" style="text-decoration: none!important;">
                            <a style="text-decoration: none!important; text-underline: none"
                               aria-controls="panel-status-{$idPanel}"
                               aria-expanded="true"
                               data-parent="#accordion"
                               class=""
                               data-toggle="collapse" href="#panel-status-{$idPanel}" role="button"><strong>Estado</strong></a>
                        </h4>
                    </div>
                    <div aria-labelledby="heading1" class="panel-collapse collapse in" id="panel-status-{$idPanel}"
                         role="tabpanel">
                        <div class="panel-body" style="padding: 10px 10px!important;">
                            {block name="status"}{/block}
                        </div>
                    </div>
                </div>
                {*  panel rendimiento *}
                <div class="panel panel-default">
                    <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                        <h4 class="panel-title" style="text-decoration: none!important;">
                            <a style="text-decoration: none!important; text-underline: none"
                               aria-controls="panel-performance-{$idPanel}"
                               aria-expanded="true"
                               data-parent="#accordion"
                               class="collapsed"
                               data-toggle="collapse" href="#panel-performance-{$idPanel}" role="button"><strong>Rendimiento</strong></a>
                        </h4>
                    </div>
                    <div aria-labelledby="heading1" class="panel-collapse collapse" id="panel-performance-{$idPanel}"
                         role="tabpanel">
                        <div class="panel-body" style="padding: 10px 10px!important;">
                            {block name="performance"}{/block}
                        </div>
                    </div>
                </div>
                {*  panel debates *}
                <div class="panel panel-default" {block name="hide_panel_discussions"}{/block}>
                    <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                        <h4 class="panel-title" style="text-decoration: none!important;">
                            <a style="text-decoration: none!important; text-underline: none"
                               aria-controls="panel-debate-{$idPanel}"
                               aria-expanded="true"
                               data-parent="#accordion"
                               class="collapsed"
                               data-toggle="collapse" href="#panel-debate-{$idPanel}" role="button"><strong>Discusión</strong></a>
                        </h4>
                    </div>
                    <div aria-labelledby="heading1" class="panel-collapse collapse" id="panel-debate-{$idPanel}"
                         role="tabpanel">
                        <div class="panel-body" style="padding: 10px 10px!important;">
                            {block name="debates"}{/block}
                        </div>
                    </div>
                </div>
                {* Panel Agreement *}
                <div class="panel panel-default">
                    <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                        <h4 class="panel-title" style="text-decoration: none!important;">
                            <a style="text-decoration: none!important; text-underline: none"
                               aria-controls="panel-agreement-{$idPanel}"
                               aria-expanded="true"
                               data-parent="#accordion"
                               class="collapsed"
                               data-toggle="collapse" href="#panel-agreement-{$idPanel}" role="button"><strong>Acuerdos</strong></a>
                        </h4>
                    </div>
                    <div aria-labelledby="heading1" class="panel-collapse collapse" id="panel-agreement-{$idPanel}"
                         role="tabpanel">
                        <div class="panel-body" style="padding: 10px 10px!important;">
                            {block name="agreement"}{/block}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {else}
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 15px">
            <div class="alert alert-info" role="alert">
                <strong>¡Atención!</strong> {$MESSAGE}.
            </div>
        </div>
        {/if}
    </div>
    {block name="js"}{/block}
{/strip}

