<div class="row">
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="col-md-12">
            <div class="alert alert-danger">
                <strong>Error:&nbsp;</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="col-xs-12">
        <form action="index.php" name="form-default-view">
            <input type="hidden" name="module" value="{$MODULE}"/>
            <input type="hidden" name="action" value="AjaxListViewUtils"/>
            <input type="hidden" name="function" value="SAVE-DEFAULT-VIEW"/>
            <input type="hidden" name="Ajax" value="true"/>
            <input type="hidden" name="user_id" value="{$USER_ID}"/>
            <div class="row" id="block">
                <div class="col-xs-12">
                    <div class="col-xs-5 label-input">

                        <label for="assigneduser">Vistas:&nbsp;
                        </label>
                    </div>
                    <div class="form-group col-xs-7 field-container"
                         id="div-tab-view">
                        <select name="tabview" id="tab-view" class="form-control">
                            {if $AVAILABLE_VIEWS neq NULL}
                            {foreach $AVAILABLE_VIEWS as $view}
                                <option value="{$view.viewid}">{$view.name}</option>
                            {/foreach}
                            {/if}
                        </select>

                        <span id="sp-tab-view" class="help-block"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 text-center">
                            <span>
                               <button type="button" class="btn btn-success btn-xs" onclick="updateGeneralView(this)">Guardar</button>
                            </span>
                    <span>&nbsp;
                        <button type="button" class="btn btn-default btn-xs" data-dismiss="modal">Cancelar</button>

                    </span>
                </div>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function () {
        });

        function updateGeneralView(obj) {
            var sendButton = jQuery(obj),
                myForm     = jQuery("form[name='form-default-view']"),
                modalTitle = jQuery('.modal-title');
            sendButton.attr('disabled', 'disabled');
            var arguments = myForm.serialize();
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        alert('La vista ha sido guardada con éxito');
                        modalTitle.html('<span class="help-block" style="color: red">Recargando la vista....</span>');
                        location.reload();
                    }
                }
                catch (e) {
                    alert(e);
                    sendButton.removeAttr('disabled');
                }
            });

        }
    </script>
</div>