{strip}
    <div class="row">
        {if $PROGRESS_BAR_OVER}
            <style>
                progress::-webkit-progress-bar {
                    background-color: red;
                    /*border: 0;
                    height: 6px;
                    border-radius: 9px;*/
                }
            </style>
        {/if}
        <div class="col-lg-12 col-md-12 col-sm-12">
            <small style="font-weight: bold">Tiempo planificado y usado (%)
                {if $PROGRESS_BAR_OVER}<br><span style="color: red">La ejecución ha excedido el tiempo planificado en&nbsp;{$OVER_TIME}%</span>{/if}
            </small>
            <div>
                <progress id="file" max="{$PROGRESS_BAR_MAX}" value="{$PROGRESS_BAR_WIDTH}" title="Total horas estimadas: {$ESTIMATED_TIME}Hrs."
                          style="width: 98%;{if $PROGRESS_BAR_OVER}background-color: red{/if}"> {$PROGRESS_BAR_WIDTH}%
                </progress>
            </div>
            <div class="text-left">
                <small>Horas laborables totales para el período:&nbsp;{$WORKED_HOURS}</small><br>
                <small>Horas reportadas como trabajadas:&nbsp;{$REPORTED_HOURS}</small><br>
                <small>Horas extras:&nbsp;{$EXTRA_HOURS}</small>
            </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <small style="font-weight: bold" id="piechart_3d_title"></small>
            <div class="center-block" id="piechart_3d" style="border: 1px solid #ccc;padding-left: 30%"></div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 2px">
            <small style="font-weight: bold" id="piechart_3d_estimated_title"></small>
            <div id="piechart_3d_estimated" class="center-block"
                 style="border: 1px solid #ccc;width:100%;padding-left: 30%"></div>
        </div>
    </div>
{/strip}