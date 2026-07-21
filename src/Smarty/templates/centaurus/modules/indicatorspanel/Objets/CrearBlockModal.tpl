{strip}
    <div id="crearblock" class="modal fade" aria-hidden="true" aria-labelledby="myModalLabel" role="dialog"
         tabindex="-1" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="text-align:center">
                    <button class="close" aria-hidden="true" data-dismiss="modal" type="button"
                            onclick="jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim});">
                        ×
                    </button>
                    <h4 class="modal-title">
                        <span id="titleBlock" style="color: black;"></span>
                    </h4>
                </div>
                <div class="modal-body">
                    <form role="form">
                        <input type="hidden" id="type" name="type" value="">
                        <div class="form-group" id="appBlockNew">
                            {if $APPLICATIONS neq NULL}
                            <label for="colorbase">{$MODSTRING.APP}</label>
                            <select class="form-control" id="record" name="record" title="{$MODSTRING.APP}">
                                <option value="">{$MODSTRING.LBL_SELECTION_APP}</option>
                                {foreach $APPLICATIONS as $keyApp => $itemApp}
                                    {if $ALL_BOX_SCORE[$keyApp][3] > 0}
                                        <option value="{$ALL_BOX_SCORE[$keyApp][3]}">{$itemApp.app_name}</option>
                                    {/if}
                                {/foreach}
                            </select>
                            {/if}
                        </div>
                        <div class="form-group">
                            <label for="colorbase">{$MODSTRING.LBL_COLORBASE}</label>
                            <input id="colorbase" class="form-control" type="color" value="#F7D358"
                                   title="{$MODSTRING.LBL_HEXCOLOR}">
                        </div>
                        <div class="form-group">
                            <label for="colordegrade">{$MODSTRING.LBL_COLORDEGRADE}</label>
                            <input id="colordegrade" class="form-control" type="color" value="#F3E2A9"
                                   title="{$MODSTRING.LBL_HEXCOLOR}">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button"
                            onclick="jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim});">{$MODSTRING.LBL_CLOSE}</button>
                    <button class="btn btn-primary" type="button" id="saveBlock"
                            onclick="saveBlock()">{$MODSTRING.LBL_SAVE}</button>
                </div>
            </div>
        </div>
    </div>
{/strip}