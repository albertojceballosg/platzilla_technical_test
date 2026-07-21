{if $CHATS|@count gt 0}
{foreach $CHATS as $chat}
    <div {if $chat.parleyname == $CURRENT_USER_NAME}
            {assign var="textAlign" value="right"}
        class="conversation-item  clearfix item-right" data-user-id="{$chat.usersfrom}-{$chat.userto}"

    {else}
            {assign var="textAlign" value="left"}
        class="conversation-item  clearfix item-left" data-user-id="{$chat.userto}-{$chat.usersfrom}"

    {/if}>
        <div class="conversation-user">
            <img src="{$chat.usersavatar}" alt="" style="width: 100%; height: 100%;">
        </div>
        <div class="conversation-body">
            <div class="name" style="text-align: {$textAlign}">{$chat.parleyname}</div>
            <div class="time hidden-xs">&nbsp;{$chat.time_since}</div>
            <div class="text" style="text-align: {$textAlign}">
                {if $chat.parleytitle neq ''}
                <p style="text-align: left; font-style: italic"><small>{$chat.parleytitle}:</small></p>
                {/if}
                <span>{$chat.message}<span>
            </div>
        </div>
    </div>
{/foreach}
{else}
    <div class="conversation-item item-left clearfix">
        <div class="conversation-user" style="max-height: 300px; overflow-y: auto">
            <img class="img-circle img-responsive"  src="../themes/centaurus/img/platzillaman.png" alt="">
        </div>
        <div class="conversation-body">
            <div class="name">Platzilla</div>
            <div class="time hidden-xs">&nbsp;</div>
            <div class="text">
                <p style="text-align: left">¡Aún no has enviado mensajes relacionados con este registro!</p>
                <p style="text-align: justify">Para enviar mensajes relacionados con el contenido de este registro:</p>
                <ul class="list-unstyled">
                    <li>Selecciona un usuario, cliente o contacto.</li>
                    <li>Escribe el mensaje y luego un clic en el botón Enviar mensaje.</li>
                </ul>

            </div>
        </div>
    </div>
{/if}