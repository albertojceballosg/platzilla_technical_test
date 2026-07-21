<div class="modal fade" id="related-destinations-{$idDestinationsView}" tabindex="-1" role="dialog"
     aria-labelledby="related-destinations-Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button id="btn-modal-{$idDestinationsView}" type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="related-destinations-Label"><strong>Destinos de Negocio </strong><span
                            id="field-name"></span></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    {*$AVAILABLE_DESTINATIONS|var_dump*}
                    <div class="col-md-12">
                        <div class="card rounded car-task">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <th class="col-to" style="width: 60%">Nombre</th>
                                        <th class="col-to" style="width: 40%;">Categoría</th>
                                    </tr>
                                    </thead>
                                    <tbody id="task-panel-table-{$idDestinationsView}">
                                    {if $AVAILABLE_DESTINATIONS neq NUL}
                                        {foreach $AVAILABLE_DESTINATIONS as $destination}
                                            <tr id="destination-row-{$destination['crmid']}">
                                                <td class="col-from">
                                                    <a href="#" title="Seleccionar este destino"
                                                       onclick="DiagnosticRerportUtils.selectDestination (event, this, '{$destination['crmid']}', '{$idDestinationsView}')">
                                                        {$destination['destinationName']}
                                                    </a>
                                                </td>
                                                <td class="col-to">{$destination['destinationCategory']}</td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr class="lvtColData">
                                            <td colspan="2" class="text-center">No hay destinos relacionados</td>
                                        </tr>
                                    {/if}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>