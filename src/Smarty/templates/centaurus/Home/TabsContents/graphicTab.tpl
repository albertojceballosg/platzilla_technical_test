<div class="main-box clearfix" style="background-color: transparent!important;padding: 0!important;">
    <div class="main-box-body clearfix">
        {if ($ALL_BOX_SCORE neq NULL) && ($GRAPHS neq NULL)}
            <div class="panel-group accordion" id="home-control-panel" style="margin-top: -12px">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#home-control-panel" href="#collapse_home-graphcis">
                                Gráficos favoritos
                            </a>
                        </h4>
                    </div>
                    <div id="collapse_home-graphcis" class="panel-collapse collapse in">
                        <div class="panel-body" id="tbl_home-graphcis">
                            {include file='Home/TabsContents/Graphs.tpl'}
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#home-control-panel" href="#collapse_home-boxscore">
                                Indicadores favoritos
                            </a>
                        </h4>
                    </div>
                    <div id="collapse_home-boxscore" class="panel-collapse collapse">
                        <div class="panel-body" id="tbl_home-graphcis">
                            {include file="Home/TabsContents/BoxScoreHome.tpl"}
                        </div>
                    </div>
                </div>
            </div>
        {elseif $GRAPHS neq NULL}
            {include file='Home/TabsContents/Graphs.tpl'}
        {else}
            <div style="padding-top: 12px">
                {include file="Home/TabsContents/BoxScoreHome.tpl"}
            </div>
        {/if}
    </div>
</div>