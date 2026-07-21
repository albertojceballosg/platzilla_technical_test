<script type="text/javascript" src="modules/graficosgenerales/graficosgenerales.js"></script>
<div class="row">
    <div class="col-md-12">
        {assign var='hasGraphic' value=false}
        {assign var='hasActiveTab' value=false}
        {if (!empty ($APPLICATIONS))}
            {assign var='applicationCodes' value=array_keys ($APPLICATIONS)}
            {foreach $applicationCodes as $applicationCode}
                {if (empty ($GRAPHS.applications[$applicationCode]))}
                    {continue}
                {/if}
                <div class="row-graphic">
                    {foreach $GRAPHS.applications[$applicationCode] as $graph}
                        {assign var='myTab' value=$applicationCode}
                        {assign var='hasGraphic' value=true}
                        {include file='modules/graficosgenerales/BasicModuleGraph.tpl'}
                    {/foreach}
                </div>
                {assign var='hasActiveTab' value=true}
            {/foreach}
            {if (!empty ($GRAPHS.others))}
                <div class="row">
                    {foreach $GRAPHS.others as $graph}
                        {assign var='myTab' value='otros'}
                        {assign var='hasGraphic' value=true}
                        {include file='modules/graficosgenerales/BasicModuleGraph.tpl'}
                        {assign var='hasActiveTab' value=true}
                    {/foreach}
                </div>
            {/if}
            {if !$hasGraphic}
                <div class="row-graphic justify-content-center">
                    <div class="col-md-6   alert alert-info" style="margin-top: 30px">No hay gráficos disponibles
                    </div>
                </div>
            {/if}
        {else}
            <div class="row-graphic justify-content-center">
                <div class="col-md-6   alert alert-info" style="margin-top: 30px">No hay gráficos disponibles
                </div>
            </div>
        {/if}
    </div>
</div>