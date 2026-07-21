{strip}
    <div class="panel panel-default" style="margin-top: 1em">
    <div class="panel-heading" style="margin-bottom: 0 !important;">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#group-linkages-import-{$idFieldTable}"
               href="#__FIELD_NAME__-{$idFieldTable}">__MODULE_FIELDLAEL__</a>
        </h4>
    </div>
    <div id="__FIELD_NAME__-{$idFieldTable}" class="panel-collapse collapse in">
        <div class="panel-body" style="padding-top: 0!important;">
            <div class="row">
                <div class="col-md-12 col-xs-12" style="margin-top: 0.5em">
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="field-name-cell" style="width: 45%; text-align: center">Importar valor del campo</th>
                            <th class="field-label-cell" style="width: 45%;text-align: center">A la Columna</th>
                            <th class="field-type-cell" style="width: 10%">&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody id="tbody-__MODULE_NAME__-{$idFieldTable}">
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5" class="text-center">
                                <span class="hide"><i class="fa fa-spinner fa-spin fa-fw fa-2x"></i></span>
                                <button id="btn-ADD-__FIELD_NAME__" type="button" data-id-linkage="__ID_LINKAGE__" data-related-module="__RE_MODULE__" class="btn btn-primary"
                                        onclick="TableFieldUtils.addRowToImport (this, 'tbody-__MODULE_NAME__-{$idFieldTable}', '__MODULE_NAME__', '__FIELD_NAME__');">
                                    <i class="fa fa-plus"></i></button>
                                <input type="hidden" name="linkages[relatedmodule][]" value="__MODULE_NAME__"/>
                                <input type="hidden" name="linkages[fieldname][]" value="__FIELD_NAME__"/>
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