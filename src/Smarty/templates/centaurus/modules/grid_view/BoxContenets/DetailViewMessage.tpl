<div id="grid-modal-messages" class="main-box clearfix">
    <div class="main-box-body clearfix">
        <div class="conversation-wrapper">
            <div class="conversation-content" style="min-height: 350px; padding: 0 4px">
                <div class="row-parley justify-content-center">
                    <div class="col-md-3 users-board">
                        <div class="form-group" style="margin-bottom: 2px">
                            <input class="form-control border" type="text" name="search_contact"
                                id="modal-search_contact" autocomplete="off" data-provide="typeahead"
                                placeholder="Buscar usuario, cliente o contacto">
                        </div>
                        <div id="modal-users-info">
                            <div id="modal-tab-user" class="list-group" style="max-height: 380px; overflow-y: auto">
                                {if (!empty ($RELATED_EMAILS_DATA))}
                                    <div id="modal-related-emails-link" class="list-group-item"
                                        style="padding-left: 1px; border: solid 0 #ffffff !important; height: 64px;">
                                        <a href="#" class="pull-left col-xs-3" style="padding-left: 4px;"
                                            onclick="parleyModalUtils.selectEmails (this); return false;">
                                            <span class="fa-stack fa-2x">
                                                <i class="fa fa-circle-thin fa-stack-2x"></i>
                                                <i class="fa fa-envelope fa-stack-1x"></i>
                                            </span>
                                        </a>
                                        <div class="media-body">
                                            <h5 class="list-group-item-heading" style="margin-top: 3px;">Correos
                                                relacionados</h5>
                                        </div>
                                    </div>
                                {/if}
                                {assign var= userActive value="active"}
                                {foreach from=$USERS_CHATS key=myId item=row}
                                    {if (! empty($row))}
                                        {if $row['id'] eq $CURRENT_USER_ID}
                                            {continue}
                                        {elseif $row['id']|in_array:$ACTIVE_USERS_CHATS}
                                            <div class="list-group-item {if $row['id'] eq $LAST_USERS_CHATS} {$userActive} {$userActive=""}{/if}"
                                                style="padding-left: 1px; border: solid 0 #ffffff !important;">
                                                <a class="pull-left col-xs-3" href="#" rel="{$row['id']}"
                                                    data-user-type="{$row['type']}"
                                                    onclick="parleyModalUtils.selectUser(this); return false;">
                                                    <img class="img-circle img-responsive" src="{$row['image']}" alt="">
                                                </a>
                                                <div class="media-body">
                                                    <h5 class="list-group-item-heading">{$row['name']}
                                                        <span style="font-style: italic" class="help-block">{$row['type']}</span>
                                                    </h5>
                                                </div>
                                            </div>
                                        {/if}
                                    {/if}
                                {/foreach}
                                {foreach from=$USERS_CHATS key=myId item=row}
                                    {if (! empty($row))}
                                        {if $row['id'] eq $CURRENT_USER_ID}
                                            {continue}
                                        {elseif !$row['id']|in_array:$ACTIVE_USERS_CHATS}
                                            <div class="list-group-item {if $row['type'] neq 'Usuario'} hide {/if}"
                                                style="padding-left: 1px; border: solid 0 #ffffff !important;">
                                                <a class="pull-left col-xs-3" href="#" rel="{$row['id']}"
                                                    data-user-type="{$row['type']}"
                                                    onclick="parleyModalUtils.selectUser(this); return false;">
                                                    <img class="img-circle img-responsive" src="{$row['image']}" alt="">
                                                </a>
                                                <div class="media-body">
                                                    <h5 class="list-group-item-heading">{$row['name']}
                                                        <span style="font-style: italic" class="help-block">{$row['type']}</span>
                                                    </h5>
                                                </div>
                                            </div>
                                        {/if}
                                    {/if}
                                {/foreach}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        {if (!empty ($RELATED_EMAILS_DATA))}
                            <div id="modal-emails-conversation" class="conversation-wrapper" style="display: none;">
                                <div class="conversation-content">
                                    <div class="conversation-inner">
                                        {foreach $RELATED_EMAILS_DATA as $emailData}
                                            {if ($emailData.type == WebmailUtils::TYPE_INCOMING)}
                                                {include file='Home/TabsContents/MessageReceived.tpl'
                                                                        IS_EMAIL=true
                                                                        MESSAGE_ID=$emailData.crmid
                                                                        ACCOUNT_NAME=$emailData.account
                                                                        SENDER=$emailData.sender
                                                                        SUBJECT=$emailData.subject
                                                                        SINCE=$emailData.timesince
                                                                        REGISTERED_AS=$emailData.registeredas
                                                                        RELATED_ENTITIES_DATA=$emailData.relatedentities
                                                                        HIDE_ACTIONS=true
                                                                        }
                                            {else}
                                                {include file='Home/TabsContents/MessageSent.tpl'
                                                                        IS_EMAIL=true
                                                                        MESSAGE_ID=$emailData.crmid
                                                                        ACCOUNT_NAME=$emailData.account
                                                                        SENDER=$emailData.sender
                                                                        SUBJECT=$emailData.subject
                                                                        SINCE=$emailData.timesince
                                                                        REGISTERED_AS=$emailData.registeredas
                                                                        RELATED_ENTITIES_DATA=$emailData.relatedentities
                                                                        HIDE_ACTIONS=true
                                                                        }
                                            {/if}
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                        {/if}
                        <div id="modal-instant-messages-section" class="row-parley justify-content-center">
                            <div id="modal-parley_chats" class="col-md-11">
                                <span id="modal-chat-list"></span>
                                {include file="modules/notification_center/chatParley.tpl"}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 users-board">&nbsp;</div>
                    <div class="col-md-9" style="bottom: 2px">
                        <form role="form" name="conversation-new-message" id="modal-conversation-new-message"
                            onsubmit="parleyModalUtils.sendMessage(); return false;">
                            <input type="hidden" name="module" id="module" value="{$MODULE}">
                            <input type="hidden" name="action" value="{$MODULE}Ajax">
                            <input type="hidden" name="file" value="SaveChat">
                            <input type="hidden" name="ajax" value="true">
                            <input type="hidden" name="record" id="record" value="{$ID}">
                            <input type="hidden" id="src-modal-Imag" name="src" value="">
                            <input type="hidden" id="modal-chat-name" name="chatName" value="{$CURRENT_USER_NAME}">
                            <input type="hidden" id="moda-user-id" name="chatUserIdName" value="{$CURRENT_USER_ID}">
                            <input type="hidden" id="modal-totalActiveUser" value="{$TOTAL_ACTIVE_USERS_CHATS}">
                            <input type="hidden" id="modal-new-chat" name="newChat"
                                value="{if $TOTAL_ACTIVE_USERS_CHATS gt 0}0{else}1{/if}">
                            <input type="hidden" id="modal-related-users" name="relatedUsers"
                                value={$RELATED_USERS_CHAT}>
                            <input type="hidden" name="typeShare" id="modal-type-share" value="0">
                            <input type="hidden" name="recordShare" id="modal-share-reg" value="0">
                            <input type="hidden" name="whomShare" id="moda-share-display" value="">
                            <div class="form-group">
                                <div class="input-group">
                                    <textarea class="form-control border" id="modal-comment" name="message"
                                        placeholder="Escriba un mensaje aquí" rows="4"
                                        style="resize: vertical;"></textarea>
                                    <span class="input-group-btn" style="vertical-align: top;"><button type="submit"
                                            class="btn btn-success">{$MODCHAT.LBL_BTN_SEND}</button></span>
                                </div>
                                <span id="modal-message-help-block" class="help-block"
                                    style="color: #ff2222;text-align: center"></span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    // Definir typeaheadSource para el autocomplete
    var typeaheadSource = {$SEARCH_USERS_CHATS};
</script>
<script type="text/javascript" src="modules/grid_view/parleyModalScript.js?v=1.0.1"></script>