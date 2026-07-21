{strip}
    {math equation= rand() assign= "idMailModal"}
    {block name="css"}{/block}
    <div class="modal fade" id="mass-mail-modal" tabindex="-1" role="dialog" aria-hidden="false" style="top: 0;">
        <form action="index.php" method="post" onsubmit="MassActionsUtils.sendEmail (this,'{$idMailModal}'); return false;">
            <input type="hidden" name="module" value="{$MODULE}"/>
            <input type="hidden" name="action" value="MassMailSend"/>
            <input type="hidden" name="Ajax" value="true"/>
            {block name="modal_hidden"}{/block}
            <div class="modal-dialog" style="width: 90vw;">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">{block name="modal_title"}{/block}</h4>
                    </div>
                    <div class="modal-body"
                         style="max-height: 70vh; min-height: 70vh; overflow-x: hidden; overflow-y: auto;">
                        {block name="modal_body"}{/block}
                    </div>
                    <div class="modal-footer">
                        {block name="modal_footer"}{/block}
                    </div>
                </div>
            </div>
        </form>
    </div>
    {block name="js"}{/block}
{/strip}