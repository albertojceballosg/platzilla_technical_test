{if (isset ($MESSAGE))}
    <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
        <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
    </div>
    <div style="height: 80px"></div>
{else}
    <div class="row">
        <div class="{*main-box no-header*}">
            <div class="{*main-box-body clearfix*} attachments-section" data-entity-id="{$RECORD}"
                data-module-name="{$MODULE}" data-maximum-file-size="{$UPLOAD_MAXSIZE / (1024 * 1024)}"
                data-modal="{if $REPORT_ID neq NULL}4{else}0{/if}"
                data-report-id="{if $REPORT_ID neq NULL}{$REPORT_ID}{else}0{/if}">
                <div class="col-md-12 drop-zone"
                    style="background-color: #ffffff; border: 1px dashed #DDDDDD; height: 34px; line-height: 34px;margin-bottom: 1em; position: relative; text-align: center;">
                    <input type="file" multiple="multiple" data-entity-id="{$RECORD}"
                        onchange="AttachmentsUtils.addEntityAttachment (event || window.event);"
                        style="bottom: 0; cursor: pointer; left: 0; opacity: 0; position: absolute; top: 0; width: 100%;" />
                    <span class="title">Arrastra archivos o clic aquí (Máx {$UPLOAD_MAXSIZE / (1024 * 1024)}
                        MB)</span>
                </div>
                <ul class="col-xs-12 attachments-container" style="list-style: none; margin-bottom:0; margin-top: 3px;">
                    {foreach $ENTITY_ATTACHMENTS as $attachment}
                        <li class="col-xs-11 attachment"
                            style="border: 1px solid #DDDDDD; margin-bottom: 3px; position: relative; width: 100%;"
                            data-attachment-id="{$attachment.attachmentsid}">
                            <button type="button" class="btn btn-close"
                                onclick="AttachmentsUtils.deleteEntityAttachment (this);"
                                style="background-color: transparent; border: 0; bottom: 0; line-height: 1; right: 0; padding: 0 5px 2px 5px; position: absolute; text-transform: uppercase; z-index: 1000;">
                                X
                            </button>
                            <div class="attachment-container">
                                <a href="{$attachment.uri}" title="{$attachment.name}" target="_blank">
                                    <span class="attachment-name">{$attachment.name}</span><span class="attachment-size">
                                        ({number_format ($attachment.size, 2, '.', '')}
                                        KB)</span>
                                </a>
                            </div>
                        </li>
                    {/foreach}
                </ul>
            </div>
            <div style="height: 300px"></div>
        </div>
    </div>
{/if}