{if $NOTIFICACTION neq NULL}
    {math equation= rand() assign= "idInputText"}
    {math equation= rand() assign= 'idExitText'}
    {math equation= rand() assign= 'idCheckBox'}
    {math equation= rand() assign='idCustomButtons'}
    {if $NOTIFICACTION->getCustomButton () neq NULL}
        {assign var='customButtons' value=$NOTIFICACTION->getButtonLinks ()}
        {assign var='totalButtons' value=$customButtons|count}
        {math equation= 12/$totalButtons assign='col'}
    {else}
        {assign var='customButtons' value=NULL}
    {/if}
    {assign var='exitText' value=$NOTIFICACTION->getExitText ()}
    {assign var='inputText' value=$NOTIFICACTION->getInputText ()}

    <div id="div-input-text-{$idInputText}" style="margin-top: 12px!important;">
        {$inputText}
    </div>
    <div id="div-exit-text-{$idExitText}" class="hide"   style="margin-top: 12px!important">
        {$exitText}

    </div >
    <div id="div-buttons-{$idCustomButtons}" class="center-block {if $customButtons eq NULL}hide{/if}" style="margin: 6px 0px;">
        {if $customButtons neq NULL}
        <div class="btn-toolbar row" role="toolbar">
            <div id="buttons-{$idCustomButtons}" class="btn-group col-xs-12">
                {foreach $customButtons as $button}
                <a href="{$button.link|replace:"[record]":$RECORD|replace:"[action]":$button.action|replace:"[module]":$button.module}&Ajax=true" role="button" class="col-xs-{$col} btn btn-{$button.style}">{$button.label}</a>
                {/foreach}
            </div>
        </div>
        {/if}
    </div>
    <div id="info-process-{$idCustomButtons}" class="hide">
        <p class="">Estamos procesando tu solicitud</p>
        <img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"  style="width: 75%;height: 75%"/>
    </div>
    <div style="margin-top: 12px!important;" {if !$CHECK_MODAL}class="hide"{/if}>
        <label class="checkbox-inline"><input id="modalCheck-{$idCheckBox}" type="checkbox" value="{$NOTIFICACTION->getId()}" /> No volver a mostrar esta notificación </label>
    </div>
    <input type="hidden" id="title-{$idInputText}" value="{if $TITLE_MODAL neq NULL}{$TITLE_MODAL} {else} {/if}">
<script type="text/javascript">
    jQuery (document).ready (function () {
        var modalBackdrop = jQuery('.modal-backdrop'),
            myHeaderModal = jQuery ('#title-{$idInputText}').parent().parent().parent().parent(),
            myTitleModal  = jQuery ('#title-{$idInputText}').val(),
            checkModal    = jQuery('#modalCheck-{$idCheckBox}');
        modalBackdrop.css('background-color', '#FFFF');
        modalBackdrop.css('opacity', 0.8);
        modalBackdrop.css('bottom', 0);
        modalBackdrop.css('z-index', 1001);

        myHeaderModal.find ('.modal-title').html (myTitleModal);
        checkModal.click(function () {
            var notificationId = jQuery (this).val (),
                arguments      = [
                    'module=notifications',
                    'action=Disable',
                    'record=' + encodeURIComponent (notificationId),
                    'Ajax=true'
                ];

            if(jQuery (this).is(':checked')) {
                arguments [ 1 ] = 'action=Disable';
            } else {
                arguments [ 1 ] = 'action=Enabled';
            }

            jQuery.ajax ('index.php', {
                data: arguments.join ('&'),
                dataType: 'text',
                method: 'post'
            }).done (function (responseText) {

            });
        });

        jQuery ('#buttons-{$idCustomButtons}').off ('click').on ('click','.btn', function (event) {
            var url        = jQuery (this).attr ('href'),
                info       = jQuery ('#info-process-{$idCustomButtons}'),
                divButtons = jQuery ('#div-buttons-{$idCustomButtons}');

            info.removeClass ('hide');
            jQuery (this).parent ().find ('a').attr ('disabled',true);
            jQuery.ajax (url, {
                method: 'get'
            }).done (function (responseText) {
                ekkoLightBox.attr ('data-process','YES');
                info.addClass('hide');
                divButtons.addClass ('hide');
                jQuery ('#div-input-text-{$idInputText}').addClass ('hide');
                jQuery ('#div-exit-text-{$idExitText}').removeClass ('hide');
            });
            event.preventDefault ();
        })
    })
</script>
{else}
    <div class="row">
        <div class="col-md-12">
            <h4>No hay tareas automatizadas para el módulo {$FORMODULE}</h4>
        </div>
    </div>
{/if}