{assign var='operationtype' value=$graph.operation}
{if empty($graph.applicationcode)}
    {assign var='appCode' value='otros'}
{else}
    {assign var='appCode' value=$graph.applicationcode}
{/if}
{assign var='divId' value=$appCode|cat:'-'|cat:$graph.tipografico|cat:'-'|cat:$graph.graficoid}
{assign var='functionName' value=$graph.tipografico|cat:'-'|cat:$graph.graficoid}
{if (in_array ($graph.graficoid, $FAVORITES))}
    {assign var='favoriteTitle' value='Ya no es mi favorito'}
    {assign var='favoriteIcon' value='fa fa-star'}
{else}
    {assign var='favoriteTitle' value='Convertir en mi favorito'}
    {assign var='favoriteIcon' value='fa fa-star-o'}
{/if}
<div  {if isset($IS_MODAL)}class="col-lg-8 center-block"{else}class="col-lg-6"{/if}>
	<div class="main-box">
		<header class="main-box-header clearfix text-center ">
			<div class="pull-right graph-chat-btn-goup">
{if ($IS_ADMIN)}
		{if ($GRAPHIC_CATEGORY neq 'STANDARD') || (!$IS_INSTANCE)}
				<a title="Borrar" id="deleteButton" href="javascript: void(0)" onclick="window.GraphUtils.deleteGraph ({$graph.graficoid})" class="btn btn-danger pull-right" style="margin-right: 5px;">
					<span class="fa fa-trash-o"></span>
				</a>
		{/if}
				<a title="Editar" href="index.php?module=graficosgenerales&action=EditGraph&record={$graph.graficoid}&activeTab={$myTab}&return_module={$MODULE}&parenttab=Settings" class="btn btn-primary pull-right" style="margin-right:5px;">
					<span class="fa fa-pencil"></span>
				</a>
{/if}
				<a title="Previsualizar" href="javascript:void(0);"
				   onclick="openGraphPreviewModal({$graph.graficoid}, '{$graph.applicationcode}', '{$graph.tipografico}', '{$graph.title|escape:'javascript'}')"
				   class="btn btn-info pull-right" style="margin-right:5px;">
					<span class="fa fa-eye"></span>
				</a>
				<span>
				<a title="{$favoriteTitle}" rel="{$graph.graficoid}" href="#" class="btn btn-success pull-right"  onclick="GraphUtils.setFavorite(event, this)"  style="margin-right:5px;">
						<span id="fa-{$graph.graficoid}" class="{$favoriteIcon}"></span></a></span>
				{* <span class="label label-info" style="margin: 5px !important; display: inline-block; padding: 5px;">{$OPERATIONS.$operationtype}</span> *}
			</div>
			<h2>{$graph.title} </h2>
		</header>
		<div class="row">
		<div class="main-box-body col-lg-12" style="height:600px;padding: 30px">
			<div id="{$appCode}-{$graph.tipografico}-{$graph.graficoid}" class="graph simple {$graph.tipografico}" style="width: 100%;">
				{* <p class="text-center">Estamos procesando tu solicitud</p> *}
				<img id="loading-graphic"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block" />
				<div class="alert alert-info text-center" style="display: none;">
					<div class="message" style="margin-bottom: 5px;">
						No hay data para graficar
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>
</div>
{*$graph|var_dump*}
{if $graph['google']}
	{* se imprime el javascript asociado a la gráfica *}
    {loadGraphic objGraphic=$graph}
{else}
	<script type="text/javascript">window.GraphUtils.loadBasicGraph ({$graph|@json_encode nofilter}, {$COLORS|@json_encode nofilter});</script>
{/if}