{math equation= rand() assign= "idHowTo"}
{if (isset ($HOW_TO))}
    {assign var='howToHtml' value=$HOW_TO->getHtml()}
    {assign var='howToId' value=$HOW_TO->getId()}
    {assign var='howToImage' value=$HOW_TO->getImage()}
    {assign var="howToImageType" value="image/png;"}
    {assign var='howToStatus' value=$HOW_TO->getStatus()}
    {assign var='howToTitle' value=$HOW_TO->getTitle()}
    {assign var='howToVideo' value=$HOW_TO->getVideo()}
    {assign var='howToTypeVideo' value=$HOW_TO->getVideoType()}
{else}
    {assign var='howToHtml' value=null}
    {assign var='howToId' value=null}
    {assign var='howToImage' value=null}
    {assign var='howToStatus' value=null}
    {assign var='howToTitle' value=null}
    {assign var='howToVideo' value=null}
    {assign var='howToTypeVideo' value=null}
    {assign var="isEdit" value=false}
{/if}
{block name="css"}{/block}
{block name="alert"}{/block}
<div class="container" style="padding-top: 0!important;">
    <nav class="navbar navbar-default navbar-static-top" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex6-collapse">
                <span class="sr-only">Desplegar navegación</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"><strong>How To:</strong></a>
        </div>
        <div class="collapse navbar-collapse navbar-ex6-collapse tabs-wrapper">
            <ul class="nav navbar-nav">
                {if $howToHtml neq NULL}
                    <li class="active">
                        <a data-toggle="tab" href="#html_{$idHowTo}">Inicio
                        </a>
                    </li>
                {/if}
                {if $howToVideo neq NULL}
                    <li class="{if $howToHtml eq NULL}active{/if}">
                        <a href="#video_{$idHowTo}"
                           data-toggle="tab">Video
                        </a>
                    </li>
                {/if}
                {if $howToImage neq NULL}
                    <li class="{if ($howToHtml eq NULL) && ($howToVideo eq NULL)}active{/if}">
                        <a href="#image_{$idHowTo}" data-toggle="tab">Foto
                        </a>
                    </li>
                {/if}
            </ul>
        </div>
    </nav>
    <div class="tab-content">
        {* HTML *}
        {if $howToHtml neq NULL}
            <div id="html_{$idHowTo}" class="tab-pane fade in active">
                <div class="row">
                    <div class="col-xs-12 col-md-12 col-lg-12" style="margin-bottom: 12px">
                        <div class="card">
                            <div class="card-header platzilla-card-header rounded" style="visibility: hidden;">&nbsp;
                            </div>
                            <div class="card-body" style="height: auto !important;">
                                {block name="howTo_html"}{/block}
                                <div class="project-box-footer clearfix" style="visibility: hidden;">&nbsp;</div>
                            </div>
                        </div>
                    </div>
                </div
            </div>
        {/if}
        {*  VIDEOS *}
        {if $howToVideo neq NULL}
            <div id="video_{$idHowTo}" class="tab-pane fade {if $howToHtml eq NULL}in active{/if}">
                <div class="row">
                    <div class="col-xs-12 col-md-12 col-lg-12" style="margin-bottom:0 !important;">
                        <div class="card">
                            {*<div class="card-header platzilla-card-header rounded" style="visibility: hidden;">&nbsp;
                            </div> *}
                            <div class="card-body" style="height: auto !important;">
                                {block name="howTo_Video"}{/block}
                                {*<div class="project-box-footer clearfix" style="visibility: hidden;">&nbsp;</div>*}
                            </div>
                        </div>
                    </div>
                </div
            </div>
        {/if}
        {* IMAGEN *}
        {if $howToImage neq NULL}
            <div id="image_{$idHowTo}"
                 class="tab-pane fade {if ($howToHtml eq NULL) && ($howToVideo eq NULL)}in active{/if}">
                <div class="row">
                    <div class="col-xs-12 col-md-12 col-lg-12" style="margin-bottom: 12px">
                        <div class="card">
                            <div class="card-header platzilla-card-header rounded" style="visibility: hidden;">&nbsp;
                            </div>
                            <div class="card-body" style="height: auto !important;">
                                {block name="howTo_Image"}{/block}
                                <div class="project-box-footer clearfix" style="visibility: hidden;">&nbsp;</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </div>
{block name="js"}{/block}