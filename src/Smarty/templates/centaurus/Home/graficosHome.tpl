<div class="row col-lg-12" id="">
    <div class="main-box clearfix">
        <div class="conversation-wrapper">
            <div class="modal-header">
                <h2><i class="glyphicon glyphicon-time"></i> Indicadores Clave</h2>
            </div>
            <div class="main-box-body clearfix">
                {* GRAFICOS FUNNEL   *}
                {if $FUNNELHOMEGRAPH neq ''}
                    <div class="row col-lg-12">
                        {foreach key=keyF item=graficoFunnel from=$FUNNELHOMEGRAPH}
                            <div class="col-lg-12">
                                <div class="main-box">
                                    <header class="main-box-header clearfix text-center">
                                        <h1>{$graficoFunnel.title}</h1>
                                    </header>

                                    <div class="main-box-body clearfix">
                                        <div id="funnel-{$graficoFunnel.graficoid}"
                                             style="height: 347px; padding: 0px; position: relative;"></div>
                                    </div>
                                </div>
                            </div>
                            <script type='text/javascript'>
                                jQuery(function () {ldelim}

                                    jQuery('#funnel-{$graficoFunnel.graficoid}').highcharts({ldelim}
                                        chart: {ldelim}
                                            type: 'funnel',
                                            marginRight: 100
                                            {rdelim},
                                        title: {ldelim}
                                            text: '',
                                            x: -50
                                            {rdelim},
                                        plotOptions: {ldelim}
                                            series: {ldelim}
                                                dataLabels: {ldelim}
                                                    enabled: true,
                                                    format: '<b>{ldelim}point.name{rdelim}</b> ({ldelim}point.y:,.2f{rdelim})(€)',
                                                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                                                    softConnector: true
                                                    {rdelim},
                                                neckWidth: '30%',
                                                neckHeight: '25%',
                                                cursor: 'pointer',
                                                point: {ldelim}
                                                    events: {ldelim}
                                                        click: function () {ldelim}
                                                            location.href = this.options.url;
                                                            {rdelim}
                                                        {rdelim}
                                                    {rdelim}
                                                {rdelim}
                                            {rdelim},
                                        legend: {ldelim}
                                            enabled: false
                                            {rdelim},
                                        series: [{ldelim}

                                            name: ' ',
                                            data: [

                                                {foreach key=keyFu item=dataFunnel from=$graficoFunnel.dataGrafico}
                                                {ldelim} name: "{$dataFunnel.headers}", y: {$dataFunnel.data} {rdelim},
                                                {/foreach}
                                            ],


                                            {rdelim}]
                                        {rdelim});
                                    {rdelim});
                            </script>
                        {/foreach}
                    </div>
                {/if}
                {* FIN GRAFICOS FUNNEL   *}

                {* GRAFICOS BASICOS   *}
                {foreach key=keyG item=grafico from=$BASICHOMEGRAPH}
                    <div class="col-lg-12">
                        <div class="main-box">
                            <header class="main-box-header clearfix text-center">
                                <h2>{$grafico.title} </h2>
                            </header>

                            <div class="main-box-body clearfix">
                                <div id="{$grafico.tipografico}-{$grafico.graficoid}"></div>
                                {*  Inicio de la tabla *}
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Valor</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {if $grafico.operation eq 2 }
                                            {foreach key=keyV item=valor from=$grafico.dataGrafico}
                                                <tr>
                                                    <td>{$valor.label}</td>
                                                    <td>{$valor.suma}</td>
                                                </tr>
                                            {/foreach}
                                        {else}
                                            {assign var="field_operation" value=$grafico.fieldoperation}
                                            {foreach key=keyV item=valor from=$grafico.dataGrafico}
                                                <tr>
                                                    <td>{$valor.$field_operation}</td>
                                                    <td>{$valor.contador}</td>
                                                </tr>
                                            {/foreach}
                                        {/if}
                                        </tbody>
                                    </table>
                                </div>
                                {* Fin de la tabla *}
                            </div>
                        </div>
                    </div>
                {/foreach}
                {* FIN GRAFICOS BASICOS   *}
            </div>
        </div>
    </div>
</div>


<script src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script src="themes/centaurus/js/moment.min.js"></script>
<script src="themes/centaurus/js/daterangepicker.js"></script>
<script src="themes/centaurus/js/jquery.knob.js"></script>
<script src="themes/centaurus/js/raphael-min.js"></script>
<script src="themes/centaurus/js/morris.js"></script>

<script type="text/javascript">
    jQuery(function () {ldelim}

        {foreach key=keyG item=grafico from=$BASICHOMEGRAPH}

        {* Si la operacion es SUMA *}
        {if $grafico.operation eq 2 }

        graphBar = Morris.Bar({ldelim}
            element: '{$grafico.tipografico}-{$grafico.graficoid}',
            data: [
                {foreach key=keyV item=valor from=$grafico.dataGrafico}
                {ldelim}device: '{$valor.label}', geekbench: {$valor.suma}{rdelim},
                {/foreach}
            ],
            barColors: ['#f39c12', '#3fcfbb', '#626f70', '#8f44ad', '#2ecc71', '#e74c3c'],
            xkey: 'device',
            ykeys: ['geekbench'],
            labels: ['Valor'],
            barRatio: 0.4,
            xLabelAngle: 35,
            hideHover: 'auto',
            resize: true
            {rdelim});

        {else}

        {assign var="field_operation" value=$grafico.fieldoperation}

        graphBar = Morris.Bar({ldelim}
            element: '{$grafico.tipografico}-{$grafico.graficoid}',
            data: [
                {foreach key=keyV item=valor from=$grafico.dataGrafico}
                {ldelim}device: '{$valor.$field_operation}', geekbench: {$valor.contador}{rdelim},
                {/foreach}
            ],
            barColors: ['#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad'],
            xkey: 'device',
            ykeys: ['geekbench'],
            labels: ['Valor'],
            barRatio: 0.4,
            xLabelAngle: 35,
            hideHover: 'auto',
            resize: true
            {rdelim});

        {/if}


        {/foreach}
        {rdelim});
</script>


