<div class="row">
    <div class="col-md-12">
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert alert-danger">
                    <strong>Error!</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
    </div>
    <div class="col-md-12">
        {if $VIDEO_TYPE eq 'VIMEO'}
        {math equation= rand() assign= "idVideo"}
        <div id="video-{$idVideo}"
             class="embed-responsive embed-responsive-16by9" style="text-align: center;" data-vimeo-url="{$URL_VIDEO}">
        </div>
        <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
        {elseif ($VIDEO_TYPE eq 'YOUTUBE')}
        <div class="embed-responsive embed-responsive-16by9 video">
            <iframe  class="embed-responsive-item"  src="{$URL_VIDEO}" allow="autoplay; fullscreen" allowfullscreen="" frameborder="0">
            </iframe>
        </div>
        {else}
        <div class="well well-sm"><P class="text-left">Video no encontado!</P></div>
        {/if}
    </div>
</div>