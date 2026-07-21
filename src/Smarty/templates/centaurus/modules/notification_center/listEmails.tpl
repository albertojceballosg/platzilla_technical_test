{strip}
    {if $EMAILS|@count gt 0}
        {foreach $EMAILS as $emails}
            <a  class="list-group-item {if $emails.setype == 'emailssent'}parley-link-own{/if} " title="Responder">
                {if $emails.setype == 'emailssent'}
                    <div class="row parley-own rounded">
                        <div class="col-md-1">
                            <img class="img-circle img-responsive"  src="{$emails.usersavatar}" alt="">
                        </div>
                        <div class="col-md-11">
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="pull-left"><strong>{$emails.fullname}</strong></p>
                                </div>
                                <div class="col-md-5">
                                    &nbsp;
                                </div>
                                <div class="col-md-3">
                                    <p class="pull-right"><i class="fa fa-clock-o"></i>&nbsp;<small>{$emails.time_since}</small></p>
                                </div>
                                <div class="col-md-12">
                                    {if $emails.send_subject neq ''}
                                        <p style="text-align: left; font-style: italic"><small>{$emails.send_subject}:</small></p>
                                    {/if}

                                </div>

                            </div>
                        </div>
                        {if $emails.send_body neq ''}
                            <div class="col-md-12" style="margin-top: 6px">
                                <button style="font-size: x-small !important;"  type="button" class="btn btn-link btn-sm" data-record="{$emails.send_id}" data-target="#body-send_{$emails.send_id}" onclick="NotificationCenterUtils.redEmail(this)">
                                    ver&nbsp;<span id="btn-chat" class="glyphicon glyphicon-plus"></span>
                                </button>&nbsp;<button style="font-size: x-small !important;"  type="button" class="btn btn-link btn-sm" data-target="#archive-send_{$emails.send_id}" onclick="NotificationCenterUtils.archiveEmail(this)">
                                    <i class="fa fa-archive" aria-hidden="true"></i>&nbsp;Correo</button>
                                <p style="text-align: left">{$emails.send_body|strip_tags:"<html><head><title><body><a>"|character_limiter:210:'&#8230'}</p>
                            </div>
                        <div id="archive-send_{$emails.send_id}"   class="col-md-12 collapse">

                        </div>
                        <div id="body-send_{$emails.send_id}"   class="col-md-12 collapse">
                            <script type="text/html" id="emailBody-{$emails.send_id}">
                                {$emails.send_body|to_charset:'ISO-8859-1'|unescape:"entity"}
                            </script>
                            <iframe class="emailIframe embed-responsive-item" style="width: 100%; border: none;height: 350px"></iframe>
                        </div>
                        {/if}
                    </div>
                {else}
                    <div class="row parley-others rounded">
                        <div class="col-md-11">
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="pull-left"><i class="fa  fa-clock-o"></i>&nbsp;<small>{$emails.time_since}</small></p>
                                </div>
                                <div class="col-md-5">
                                   &nbsp;
                                </div>
                                <div class="col-md-4">
                                    <p class="pull-right"><strong>{$emails.from_}</strong></p>
                                </div>

                                <div class="col-md-12">
                                    {if $emails.recived_subject neq ''}
                                    <p style="text-align: left; font-style: italic"><small>{$emails.recived_subject}:</small></p>
                                    {/if}
                                    <span><span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <img class="img-circle img-responsive "  src="{$emails.usersavatar}" alt="">
                        </div>
                        {if $emails.recived_subject neq ''}
                            <div class="col-md-12">
                                <button style="font-size: x-small !important;"  type="button" class="btn btn-link btn-sm" data-record="{$emails.recived_id}" data-target="#body-recived_{$emails.recived_id}" onclick="NotificationCenterUtils.redEmail(this)">
                                    ver&nbsp;<span id="btn-chat" class="glyphicon glyphicon-plus"></span>
                                </button>&nbsp;<button style="font-size: x-small !important;"  type="button" class="btn btn-link btn-sm"  data-target="#archive-recived_{$emails.recived_id}" onclick="NotificationCenterUtils.archiveEmail(this)">
                                    <i class="fa fa-archive" aria-hidden="true"></i>&nbsp;Correo</button>
                                <p style="text-align: left">{$emails.recived_body|strip_tags:"<html><head><title><body><a>"|character_limiter:210:'&#8230'}</p>
                            </div>
                            <div id="archive-recived_{$emails.recived_id}" class="col-md-12 collapse">

                            </div>
                            <div id="body-recived_{$emails.recived_id}"   class="col-md-12 collapse">
                            <script type="text/html" id="emailBody-{$emails.recived_id}">
                            {$emails.recived_body|to_charset:'ISO-8859-1'|unescape:"entity"}
                            </script>
                                <iframe class="emailIframe embed-responsive-item" style="width: 100%; border: none;height: 350px"></iframe>
                            </div>
                        {/if}

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
                        <span>No se encontraron Correos<span>
                    </div>
                </div>
            </div>
        </div>
        </a>
    {/if}
{/strip}