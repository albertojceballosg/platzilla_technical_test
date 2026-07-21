    {if $CHATS|@count gt 0}
        {foreach $CHATS as $chat}
            <a href="index.php?module={$chat.module}&parenttab=&action=DetailView&record={$chat.recordid}" class="list-group-item {if $chat.parleyname == $CURRENT_USER_NAME}parley-link-own{/if} " title="Responder">
                {if $chat.parleyname == $CURRENT_USER_NAME}
                    <div class="row parley-others rounded">
                        <div class="col-md-1">
                            <img class="img-circle img-responsive"  src="{$chat.usersavatar}" alt="">
                        </div>
                        <div class="col-md-11">
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="pull-left"><strong>{$chat.parleyname}</strong></p>
                                </div>
                                <div class="col-md-5">
                                    <!-- Registro: {$chat.recordid} {$CURRENT_USER_NAME}-->
                                    <p class="associated-to"><small>Asociada a:&nbsp;{$chat.tablabel}</small></p>
                                </div>
                                <div class="col-md-3">
                                    <p class="pull-right"><i class="fa fa-clock-o"></i>&nbsp;<small>{$chat.time_since}</small></p>
                                </div>
                                <div class="col-md-12">
                                    {if $chat.parleytitle neq ''}
                                    <p style="text-align: left; font-style: italic"><small>{$chat.parleytitle}:</small></p>
                                    {/if}
                                    <span>{$chat.message}<span>
                                </div>
                            </div>
                        </div>
                    </div>
                {else}
                    <div class="row parley-own rounded">
                        <div class="col-md-11">
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="pull-left"><i class="fa  fa-clock-o"></i>&nbsp;<small>{$chat.time_since}</small></p>
                                </div>
                                <div class="col-md-5">
                                    <!-- Registro: {$chat.recordid} {$CURRENT_USER_NAME}-->
                                    <p class="associated-to"><small>Asociada a:&nbsp;{$chat.tablabel}</small></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="pull-right"><strong>{$chat.parleyname}</strong></p>
                                </div>

                                <div class="col-md-12">
                                    {if $chat.parleytitle neq ''}
                                    <p style="text-align: left; font-style: italic"><small>{$chat.parleytitle}:</small></p>
                                    {/if}
                                    <span>{$chat.message}<span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <img class="img-circle img-responsive "  src="{$chat.usersavatar}" alt="">
                        </div>

                    </div>

                {/if}
            </a>
        {/foreach}
    {else}
        <a href="#" class="list-group-item ">
        <div class="row parley-own rounded">
            <div class="col-md-1">
                <img class="img-circle img-responsive"  src="../themes/centaurus/img/platzillaman.png" alt="">
            </div>
            <div class="col-md-11">
                <div class="row">
                    <div class="col-md-4">
                        <p class="pull-left"><strong>Platzilla</strong></p>
                    </div>
                    <div class="col-md-4">
                        <p class="associated-to"><small></small></p>
                    </div>
                    <div class="col-md-4">
                        <p class="pull-right"><i class="fa fa-clock-o"></i>&nbsp;<small>00:00</small></p>
                    </div>
                    <div class="col-md-12">
                        <span>No se encontraron conversaciones<span>
                    </div>
                </div>
            </div>
        </div>
        </a>
    {/if}