{if (isset ($MESSAGE))}
    <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
        <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
    </div>
    <div style="height: 80px"></div>
{else}
    {math equation= rand() assign= "ID"}
    <div class="row">
        <div class="main-box no-header">
            <div class="main-box-body clearfix comments-section">
                <div class="grid-container" {if $COMMENTS neq NULL}style="height: 160px;{/if}">
                    {if $COMMENTS neq NULL}
                        <div class="project-box-content">
                            {foreach $COMMENTS as $comment}
                                <div class="row" style="margin-bottom: 4px">
                                    <div class="col-md-2 text-center" style="margin-right: -4px">
                                        <figure class="grid-img">
                                            <img src="{if !empty($comment->getUserAvatar ())}{$comment->getUserAvatar ()}{else}{$altPhoto}{/if}"
                                                 class="img-responsive">
                                        </figure>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="grid-item border-bottom border-secondary">
                                            <strong>{$comment->getUserName ()}</strong>
                                            <small style="float: right"><i
                                                        class="fa fa-calendar"></i>&nbsp;&nbsp;{$comment->getWrittenOn ()}
                                            </small>

                                        </div>
                                        <div class="grid-item"
                                             {if $comment->getCommentType() neq 'TEXT'}style="padding-top: 2px"{/if} >
                                            {if $comment->getCommentType() eq 'TEXT'}
                                                {$comment->getStatement()|truncate:100:"...":true}
                                            {else}
                                                <audio controls src="data:audio/ogg;base64,{$comment->getStatement()}"/>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {else}
                        {* ----- *}
                        <h4 class="text-center" style="margin-bottom: 6px; margin-top: -2px; z-index: 1000;top: -4px">
                            <small>Sin&nbsp;Notas&nbsp;¡Crea el primero!</small>
                        </h4>
                        {* ----- *}
                    {/if}
                </div>
                <div class="col-md-12 col-xs-12" style="margin-top: 12px">
                    <form class="form" id="comment-form-{$ID}" method="post" enctype="multipart/form-data" {*onsubmit="CommentsUtils.addComment ('{$ID}'); return false"*}>
                    <input type="hidden" name="entityid" id="entityid" value="{$RECORD}">
                    <input type="hidden" name="module" id="module" value="{$MODULE}">
                    <input type="hidden" name="action" value="AddComment">
                    <input type="hidden" name="Ajax" value=true>
                    <input type="hidden" id="isModal" value=1>
                        <div class="row">
                            <div class="col-md-12 col-xs-12" style="margin-top: 1px">
                                <textarea name="statementText" class="form-control comment-statement border"
                                          id="statement-text"
                                          placeholder="Ingresa tu nota"
                                          style="margin-bottom: 0.5em;"></textarea>
                            </div>
                            <div class="col-md-12 col-xs-12" style="margin-top: 1px">
                                <button type="button" class="btn btn-primary" style="display:block;"
                                        onclick="CommentsUtils.catchFile(event, 'comment-get-file-{$ID}')"><i
                                            class="fa fa-upload" aria-hidden="true"></i>&nbsp;Nota de voz
                                </button>
                                <span id="comment-help-voice" class="help-block" style="color: red"></span>
                                <input {*name="statementSound"*} style="display: none" id="comment-get-file-{$ID}" type="file" accept="audio/*"
                                       onchange="CommentsUtils.getSound(event, this)">
                                <button type="button" id="comment-play-sound" style="margin-top: 2px"
                                        class="btn btn-success btn-circle" onclick="CommentsUtils.playSound()" disabled>
                                    <i class="fa fa-play" aria-hidden="true"></i></button>&nbsp;&nbsp;
                                <button type="button"id="comment-stop-sound" style="margin-top: 2px"
                                        class="btn btn-danger btn-circle" onclick="CommentsUtils.stopSound()" disabled>
                                    <i class="fa fa-stop" aria-hidden="true"></i></button>&nbsp;<span id="comment-sound-loading"></span>
                            </div>
                        </div>
                        <div class="col-md-12 col-xs-12 hidden" style="margin-top: 1px">
                            <textarea name="voice_note" id="encodedResult" cols="80" rows="6"></textarea>
                        </div>

                <div class="text-center">
                    <button type="button" class="btn btn-primary" onclick="CommentsUtils.addComment ('{$ID}')">Enviar</button>
                </div>
                </form>
                </div>
            </div>
        </div>
        <div style="height: 40px"></div>
    </div>
    </div>
    <script type="text/javascript" src="themes/centaurus/js/comments.js"></script>
{/if}