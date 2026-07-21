<script type="text/javascript" src="modules/graficosgenerales/graficosgenerales.js"></script>
<div class="row">
    <div class="col-md-12 hide" style="margin-top: -6px;margin-left: -10px!important;">
        {* Tab graphic Move to Smarty/templates/centaurus/Home/TabsContents/Graphs.tpl*}
    </div>
    <div class="col-md-12" style="margin-top: 6px">
        <div class="tab-content" style="margin-top: 6px">
            {assign var='hasActiveTab' value=false}
            {if (!empty ($APPLICATIONS))}
                {assign var='applicationCodes' value=array_keys ($APPLICATIONS)}
                {foreach $applicationCodes as $applicationCode}
                    {if (empty ($GRAPHS.applications[$applicationCode]))}
                        {continue}
                    {/if}
                    <div id="tab-{$applicationCode}"
                         class="tab-pane fade in{if $activeTab eq $applicationCode} active{/if}">
                        <div class="row-graphic">
                            {foreach $GRAPHS.applications[$applicationCode] as $graph}
                                {if (in_array($graph.graficoid,$FAVORITES))}{continue}{/if}
                                {assign var='myTab' value=$applicationCode}
                                {include file='modules/graficosgenerales/BasicModuleGraph.tpl'}
                                {*include file='modules/graficosgenerales/BasicGraph.tpl'*}
                            {/foreach}
                        </div>
                    </div>
                    {assign var='hasActiveTab' value=true}
                {/foreach}
            {/if}
            {if (!empty ($GRAPHS.others))}
                <div id="tab-otros" class="tab-pane fade in{if $activeTab eq 'otros'} active{/if}">
                    <div class="row">
                        {foreach $GRAPHS.others as $graph}
                            {assign var='myTab' value='otros'}
                            {include file='modules/graficosgenerales/BasicGraph.tpl'}
                            {assign var='hasActiveTab' value=true}
                        {/foreach}
                    </div>
                </div>
            {/if}
            {if (!empty ($FAVORITES))}
                <div id="tab-FAVORITES" class="tab-pane fade in{if $activeTab eq 'FAVORITES'} active{/if}">
                    <div class="row-graphic">
                    {foreach $applicationCodes as $applicationCode}
                        {if (empty ($GRAPHS.applications[$applicationCode]))}
                            {continue}
                        {/if}
                        {foreach $GRAPHS.applications[$applicationCode] as $graph}
                            {if (!in_array($graph.graficoid,$FAVORITES))}{continue}{/if}
                            {assign var='myTab' value='FAVORITES'}
                            {include file='modules/graficosgenerales/BasicModuleGraph.tpl'}
                        {/foreach}
                    {/foreach}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>