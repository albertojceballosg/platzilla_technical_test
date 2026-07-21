{strip}
    {if (!empty ($MESSAGE))}
        <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
            <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    {/if}
    <div class="row">
        <div class="col-md-6">
            <div class="col-md-5">
                <div class="label-input">
                    <label for="td_telefono" style="line-height: 1.25em !important;"></label>
                </div>
            </div>
            <div class="form-group col-md-7 data-input" id="td_telefono" style="display: block;">
                <div class="input-group">
                    {if $RECORDING_AUDIO neq NULL}
                        <audio controls>
                            <source src="{$RECORDING_AUDIO['fullpath']}" type="{$RECORDING_AUDIO['mimetype']}">
                            Your browser does not support the audio element.
                        </audio>
                    {else}
                        <p>No hay mensaje de voz</p>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/strip}