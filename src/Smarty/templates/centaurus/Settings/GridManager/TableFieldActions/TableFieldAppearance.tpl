{strip}
    <div class="panel panel-default" style="margin-top: 1em">
    <div class="panel-heading" style="margin-bottom: 0 !important;">
        <h4 class="panel-title">
            <a data-toggle="collapse"
               href="#table-appearance-{$idFieldTable}">Visualización de la tabla</a>
        </h4>
    </div>
    <div id="table-appearance-{$idFieldTable}" class="panel-collapse collapse in">
        <div class="panel-body" style="padding-top: 0!important;">
            <div class="row">
                <div class="col-md-12 col-xs-12" style="margin-top: 0.5em">
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="field-label-cell" style="width: 20%;text-align: center;vertical-align: top">Columna</th>
                            <th class="field-name-cell" style="width: 10%; text-align: center;vertical-align: top">% de Ancho</th>
                            <th class="field-type-cell" style="width: 700%;text-align: center">Estilos para título</th>
                        </tr>
                        </thead>
                        <tbody id="tbody-table-appearance-{$idFieldTable}"></tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5" class="text-center">&nbsp;
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="col-md-12 col-xs-12"></div>
            </div>
        </div>
    </div>
{/strip}