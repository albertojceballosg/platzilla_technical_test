{extends file='base/BaseHowTo.tpl'}
{block name="css"}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
    <style>
        .video-container {
          position: relative;
          padding-top: 56.25%; /* 16:9 aspect ratio */
        }

        .video-container iframe {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
        }
    </style>
{/block}
{strip}
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        {block name="alert"}
            <div class="row">
                <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/block}
    {/if}

    {block name="howTo_html"}
        <div class="grid-container" style="height: 600px; !important;">
            {$howToHtml}
        </div>
        </div>
    {/block}
    {block name="howTo_Video"}
        <div class="">
            {if $howToTypeVideo eq 'VIMEO'}
                <div id="video-{$howToId}"
                     class="embed-responsive embed-responsive-16by9 video"{if (null !== $howToVideo)} data-vimeo-url="{$howToVideo}"{/if}>
                </div>
            {else}
                <div class="video-container" style="text-align: center">
                    <iframe id="video-{$howToId}" class="youtube-video"
                            src="{if (null !== $howToVideo)}{$howToVideo}{/if}" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen="allowfullscreen">
                    </iframe>
                </div>
            {/if}
        </div>
        </div>
    {/block}
    {block name="howTo_Image"}
        <div style="text-align: center; width: 100%; height: 100%">
            <img id="course-photo-{$howToId}"
                 alt="how to"
                 src="data:{$howToImageType}; base64, {$howToImage}"
                 class="img-responsive center-block">
        </div>
    {/block}
{/strip}