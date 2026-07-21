<style type="text/css">
    {literal}
    .calculated-list {
        max-height: 150px;
        overflow-y: auto;
        font-size: .8em
    }
    .calculated-list a {
        padding: 4px 6px !important;
        margin: 1px;
    }

    {/literal}
</style>

<div class="modal fade" id="new-calculated-modal" tabindex="-1" role="dialog" aria-hidden="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php" method="get" class="form">
                <input type="hidden" id="module" name="module" value="calculated_fields">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Crear Cálculo en el Sistema</h4>
                </div>
                <div class="modal-body">
                <div class="modal-body">
                    <div class="row">
                        <p class="col-xs-12">¿Qué quieres?</p>
                        <div class="form-group col-xs-12 field-container">
                            <div class="input-group" style="width: 100%;">
                                <div class="radio-group">
                                    <label><input type="radio" id="action-create" name="action" value="addCalculatedSystem" checked="checked" onchange="CSUtils.setCalculatedPattern (this);" />&nbsp;Crear un cálculo nuevo</label>
                                </div>
                                <div class="radio-group">
                                    <label><input type="radio" id="action-duplicate" name="action" value="duplicateCalculatedSystem" onchange="CSUtils.setCalculatedPattern (this);" />&nbsp;Crear un cálculo basado en un patrón</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="calculated-pattern" class="row hide">
                        <p class="col-xs-12">¿Cuál es el patrón?</p>
                        <div class="col-xs-11" style="padding: 12px">
                            <input class="form-control input-sm search_Calculated" type="text" placeholder="Buscar cálculo" oninput="CSUtils.searchCalculated(this)">
                        </div>
                        <div class="col-xs-11" style="height: 100%; padding: 0px 12px">
                        <input type="hidden" id="calculatedSystemId" name="calculatedSystemId" value="">
                        <div class="list-group calculated-list">
                            {foreach $ACS as $cf}
                                <a href="javascript: void(0);" rel="{$cf->getId ()}" title="{$cf->getDescription ()}" class="list-group-item" onclick="CSUtils.setCalculatedSystem(this)">{$cf->getName ()}</a>
                            {/foreach}
                        </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Crear</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" aria-hidden="true">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>