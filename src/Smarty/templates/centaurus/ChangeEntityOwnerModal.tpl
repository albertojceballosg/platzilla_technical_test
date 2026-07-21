<div class="row">
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="col-md-12">
            <div class="alert alert-danger">
                <strong>Error:&nbsp;</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <div class="col-xs-12">
        <form action="index.php" name="kanban-change-owner">
            <input type="hidden" name="module" value="{$MODULE}"/>
            <input type="hidden" name="action" value="Save"/>
            <input type="hidden" name="Ajax" value="true"/>
            <input type="hidden" name="return_action" value="{$RETURN_ACTION}"/>
            <input type="hidden" name="record" value="{$RECORD}"/>
            <input type="hidden" name="assigntype" value="{$ASSINGN_TYPE}"/>
            <input type="hidden" name="mode" value="{$MODE}"/>
            <div class="row" id="block_{$RECORD}">
                <div class="col-xs-12">
                    <div class="col-xs-5 label-input">

                        <label for="assigneduser">Asignado a:&nbsp;
                        </label>
                    </div>
                    <div class="form-group col-xs-7 field-container"
                         id="ce-td_assigned_user_id">
                        <select name="assigned_user_id" class="form-control"
                                style="margin-top: .5em;"
                                title=" Assigned To">
                            {$CHANGE_OWNER}
                        </select>
                        <span id="ce-ce-assigned_user_id" class="help-block"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 text-center">
                            <span>
                               <button type="button" class="btn btn-success btn-xs" onclick="saveEditableFields(this)">Guardar</button>
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
            jQuery('.modal-title').html('{$MODAL_TITLE}');

        });

        function saveEditableFields(obj) {
            var sendButton = jQuery(obj),
                myForm = jQuery("form[name='kanban-change-owner']"),
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
                        alert('El registro ha sido guardado con éxito');
                        modalTitle.html(modalTitle.html() + '&nbsp;<span class="help-block" style="color: red">Recargando el Kanban....</span>');
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