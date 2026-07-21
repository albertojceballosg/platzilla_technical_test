{strip}
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <br>
        <h4 class="modal-title">Copiar el indicador: {$TITLE}</h4>
    </div>
    <form role="form" name="form-share-box-score-{$ID_PAGE}" id="form-share-box-score-{$ID_PAGE}" action="index.php" method="post">
        <div class="modal-body">
            <input type="hidden" name="action" value="AjaxBoxScoreUtils">
            <input type="hidden" name="module" value="Settings">
            <input type="hidden" name="function" value="CLONE_BOXSCORE">
            <input type="hidden" name="Ajax" value="true">
            <input type="hidden" name="box_score_name" value="{$BOX_SCORE_NAME}">
            <input type="hidden" id="ID" value="{$ID_PAGE}">
            <div class="form-group">
                <label for="box_score">En la instancias:</label>
                <select class="form-control " id="instance" name="code_instance">
                    {foreach $INSTANCES as $instance}
                        <option value="{$instance['code']}" {if $instance['code'] eq $SELECTED_INSTANCE} selected {/if}>{$instance['code']}
                            : {$instance['name']} </option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group">
            </div>
            <div class="form-group" align="right">

            </div>
            <div id="info-clon-{$ID_PAGE}" class="form-inline form-inline-box">
                <p class="center">El indicador será clonado en la instancia seleccionada.</p>
            </div>
            <div class="form-group">
                <img id="loading-graphic-{$ID_PAGE}"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block hide" />
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-warning" id="btnclose" data-dismiss="modal">Cancelar</button>
            <button type="submit" id="btnSave" name="btnSave" class="btn btn-success"
                    onclick="BoxScoreInventoryUtils.sendClonData(this, '{$ID_PAGE}')">Copiar</button>
        </div>
    </form>
{/strip}