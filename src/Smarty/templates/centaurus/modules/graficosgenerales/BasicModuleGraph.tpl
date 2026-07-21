{assign var='operationtype' value=$graph.operation}
{assign var='divId' value=$graph.applicationcode|cat:'-'|cat:$graph.tipografico|cat:'-'|cat:$graph.graficoid}
{assign var='functionName' value=$graph.tipografico|cat:'-'|cat:$graph.graficoid}
{if (in_array ($graph.graficoid, $FAVORITES))}
    {assign var='favoriteTitle' value='Ya no es mi favorito'}
    {assign var='favoriteIcon' value='fa fa-star'}
{else}
    {assign var='favoriteTitle' value='Convertir en mi favorito'}
    {assign var='favoriteIcon' value='fa fa-star-o'}
{/if}
<style type="text/css">
    .google-visualization-table-table {
        margin-left: auto;
        margin-right: auto;
        width: 100%!important;
        max-height: 340px!important;
        overflow-y: auto!important;
        display: block!important;
    }
    .google-visualization-table {
        padding: 10px 0!important;
        width: 100%!important;
        max-height: 380px!important;
        overflow-y: auto!important;
    }
    .google-visualization-table-div-page {
        text-align: center;
        padding: 10px 0;
    }
    /* Control de altura para gráficos de embudo (Flot Charts) */
    div[id*="-funnel-"] {
        max-height: 380px!important;
        height: 380px!important;
        overflow: hidden!important;
    }
</style>
<div {if isset($IS_MODAL) && ($IS_MODAL)}class="col-lg-8 center-block" {elseif $graph.tipografico eq 'column'}class="col-lg-6 col-md-6 col-xs-12"{else}class="col-lg-6 col-md-6 col-xs-12"{/if}
     style="margin-bottom: 15px!important; {if $graph.tipografico eq 'column'}max-height:650px!important;{else}max-height:480px!important;{/if}">
    <div class="rounded" style="background-color: white;padding: 10px 20px;height: auto;min-height: 100%">
        <header class="main-box-header clearfix text-center" style="margin-bottom: 0;z-index: 100000">
            <div class="pull-right graph-chat-btn-goup">
                {if ($IS_ADMIN)}
                    {if ($GRAPHIC_CATEGORY neq 'STANDARD') || (!$IS_INSTANCE)}
                        <a title="Borrar" id="deleteButton" href="javascript: void(0)"
                           onclick="window.GraphUtils.deleteGraph ({$graph.graficoid})"
                           class="btn btn-danger pull-right" style="margin-right: 5px;">
                            <span class="fa fa-trash-o"></span>
                        </a>
                    {/if}
                    <a title="Editar"
                       href="index.php?module=graficosgenerales&action=EditGraph&record={$graph.graficoid}&activeTab={$myTab}&return_module={$MODULE}&parenttab=Settings"
                       class="btn btn-primary pull-right" style="margin-right:5px;">
                        <span class="fa fa-pencil"></span>
                    </a>
                {/if}
                <a title="Previsualizar" href="javascript:void(0);"
                   onclick="openGraphPreviewModal({$graph.graficoid}, '{$graph.applicationcode}', '{$graph.tipografico}', '{$graph.title|escape:'javascript'}')"
                   class="btn btn-info pull-right" style="margin-right:5px;">
                    <span class="fa fa-eye"></span>
                </a>
                <span>
				<a title="{$favoriteTitle}" rel="{$graph.graficoid}" href="#" class="btn btn-success pull-right"
                   onclick="GraphUtils.setFavorite(event, this)" style="margin-right:5px;">
						<span id="fa-{$graph.graficoid}" class="{$favoriteIcon}"></span></a></span>
            </div>
        </header>
        <h4 class="text-center" style="margin-top: -1px">{$graph.title} </h4>
        <div class="row">
            <div id="{$graph.applicationcode}-{$graph.tipografico}-{$graph.graficoid}"
                 class="col-lg-12 graph simple {$graph.tipografico}"
                 style="{if $graph.tipografico neq 'table'}margin: -8px 0 0!important;{else}margin-top: 8px!important;{/if}{if $graph.tipografico eq 'column'}min-height: 500px!important;{/if}">
                <img id="loading-graphic" src="themes/images/loading.gif" alt="Loading"
                     style="padding 0!important;background-color: transparent!important;"
                     class="img-responsive center-block"/>
                <div class="alert alert-info text-center" style="display: none;margin-top: 60px">
                    <div class="message" style="margin-bottom: 5px;">
                        No hay data para graficar
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{if $graph['google']}
    {loadGraphic objGraphic=$graph}
{else}
    <script type="text/javascript">window.GraphUtils.loadBasicGraph({$graph|@json_encode nofilter}, {$COLORS|@json_encode nofilter});</script>
{/if}