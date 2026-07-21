{strip}
    <div class="panel panel-default" style="margin-top: 1em">
    <div class="panel-heading" style="margin-bottom: 0 !important;">
        <h4 class="panel-title">
            <a data-toggle="collapse"
               href="#summary-row-{$idFieldTable}">Fila resumen</a>
        </h4>
    </div>
    <div id="summary-row-{$idFieldTable}" class="panel-collapse collapse in">
        <div class="panel-body" style="padding-top: 0!important;">
            <div class="row">
                <div class="col-md-12 col-xs-12" style="margin-top: 0.5em">
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="field-label-cell" style="width: 45%;text-align: center">Columna</th>
                            <th class="field-name-cell" style="width: 45%; text-align: center">Operación</th>
                            <th class="field-type-cell" style="width: 10%">&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody id="tbody-summary-{$idFieldTable}">
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5" class="text-center">
                                <span class="hide"><i class="fa fa-spinner fa-spin fa-fw fa-2x"></i></span>
                                <button id="btn-ADD-SUMMARY-ROW-{$idFieldTable}" type="button" class="btn btn-primary"
                                        onclick="TableFieldUtils.addSummaryRow (this, 'tbody-summary-{$idFieldTable}');">
                                    <i class="fa fa-plus"></i></button>
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