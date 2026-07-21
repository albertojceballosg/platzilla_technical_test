{strip}
    <div class="main-box clearfix">
        {if (!$IS_INSTANCE)}
            <header class="main-box-header clearfix">
                <div class="text-right">
                    <a href="index.php?module=Courses&action=EditView" class="btn btn-primary">Crear Carpeta</a>
                </div>
            </header>
        {/if}
        <div class="main-box-body clearfix">
            <div class="row">
            {if $FOLDERS neq NULL}
                {foreach $FOLDERS as $folder}
                <div class="col-md-3 border materials-portrait" style="margin:0 5px;min-height:265px;">
                    {if (!empty ($folder->getVideo()))}
                            <div id="video-{$folder->getId()}"
                                 class="embed-responsive embed-responsive-16by9 video-home" data-vimeo-url="{$folder->getVideo()}">
                            </div>
                    {else}

                    <figure class="embed-responsive embed-responsive-16by9">
                        <img src="themes/images/no-video.png" class="center-block {*picture*} img-responsive"/>
                    </figure>

                    {/if}
                    <p class="materials-name text-center"><i class="fa fa-folder" aria-hidden="true"></i>&nbsp;{$folder->getName()}</p>
                </div>
                {/foreach}
            {else}
                <div class="col-xs-12 text-center course">No hay carpetas</div>
            {/if}
        </div>
        </div>
    </div>
    <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
{/strip}