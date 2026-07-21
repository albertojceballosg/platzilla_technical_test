{strip}
    <section id="documents-tab">
        <div class="main-box no-header" style="padding-top: 0!important;">
            <h1 class="text-center"  style="padding-left: 0!important; font-weight: bold;margin-top: 25px">Documentos de gestión empresarial</h1>
            <div class="main-box-body clearfix">
                <link rel="stylesheet" type="text/css"
                      href="https://cdn3.devexpress.com/jslib/20.1.7/css/dx.common.css"/>
                <link rel="stylesheet" type="text/css"
                      href="https://cdn3.devexpress.com/jslib/20.1.7/css/dx.light.css"/>
                <style>
                    #treeview {
                        height: auto;
                        min-height: 300px;
                        width: 100% !important;
                        color: #000b0d;
                        padding: 25px 12px 25px;
                    }

                    #treeview .dx-treeview-toggle-item-visibility {
                        color: #000b0d !important;
                    }

                    #treeview .dx-state-hover {
                        color: #ffffff !important;
                        background-color: #0d94df !important;
                    }

                    #treeview .dx-texteditor-input {
                        background-color: #ffffff !important;
                    }

                    .options {
                        padding: 20px;
                        position: absolute;
                        bottom: 0;
                        right: 0;
                        width: 260px;
                        top: 0;
                        background-color: #f5f5f5;
                    }

                    .caption {
                        font-size: 18px;
                        font-weight: 500;
                    }

                    .option {
                        margin-top: 10px;
                    }

                    .option > .dx-selectbox {
                        display: inline-block;
                        vertical-align: middle;
                        max-width: 350px;
                        width: 100%;
                        margin-top: 5px;
                    }
                </style>
                {assign var='noPhoto' value='themes/centaurus/img/docs-thumbnails.png'}
                <div class="row" {*style="margin: 25px 6px 3px 6px; padding: 2px"*}>
                    <div class="col-md-12  document-menu">
                        <div class="demo-container">
                            <div id="treeview"></div>
                        </div>
                    </div>
                    <div class="row" style="margin: 12px 3px">
                        <div class="col-md-12">
                            <div class="row">
                                <!--
                                <div class="col-md-4">
                                    <img src="/themes/centaurus/img/documents_resource.png" class="img-responsive">
                                </div> -->
                                <div class="col-md-12">
                                    {* carousel*}
                                    {if $FILES neq NULL}
                                        {assign var="index" value='1D'}
                                    {* caraucel *}
                                    <div class="col-xs-12 category">
                                        <div class="col-xs-12 course-portraits">
                                            {*if (!empty ($COURSES_DATA[$audience]))*}
                                            {math equation='(x / y) + z' x=$FILES|count y=6 z=0 assign='numIndicators'}
                                            {*$numIndicators|floor|var_dump*}
                                            <div id="Carousel-{$index}" class="carousel slide">
                                                <ol class="carousel-indicators {if $numIndicators|floor lt 1} hide{/if}">
                                                    {foreach $FILES as $file}
                                                        {if (($file@iteration gt ($numIndicators|floor)) )}
                                                            {continue}
                                                        {/if}
                                                        <li data-target="#Carousel-{$index}" data-slide-to="{$smarty.foreach.foo.index}"
                                                            class="{if {$smarty.foreach.foo.index} eq 0}active{/if}"></li>
                                                    {/foreach}
                                                </ol>
                                                <div class="carousel-inner">
                                                    {assign var='caroucelItem' value=1}
                                                    {assign var='activeItem' value='active'}
                                                    {foreach from=$FILES item= $file name=foo}
                                                        {assign var='fileId' value=$file->getId ()}
                                                        {assign var="filePhoto" value=$file->getPhoto()}
                                                        {assign var="filePhotoType" value=$file->getPhotoType()}
                                                        {assign var="folderName" value=$file->getFolderName()}
                                                        {assign var="fileName" value=$file->getName ()}
                                                        {assign var="filePublicName" value=$file->getPublicName ()}
                                                        {if $caroucelItem eq 1}
                                                            <div class="item {$activeItem}">
                                                            {assign var='activeItem' value=''}
                                                            <div class="row">
                                                        {/if}
                                                        <div class="col-md-2 center-block">
                                                            <a <a href="#"
                                                                  onclick="MaterialsUtils.showDocumentPage(event, '{$fileId}')"
                                                                  rel="{$fileId}"
                                                                  title="{$filePublicName}"
                                                               class="center-block"
                                                               style="margin-bottom: 1px!important;">
                                                                {if !empty ($filePhoto)}
                                                                    <img id="file-photo-{$fileId}"
                                                                         src="data:{$filePhotoType}; base64, {$filePhoto}"
                                                                         class="center-block img-thumbnail img-responsive">
                                                                {else}
                                                                    <img class="img-thumbnail img-responsive center-block"
                                                                         src="data:{$DEFAULT_PHOTO['type']}; base64,{$DEFAULT_PHOTO['imagen']}"/>
                                                                {/if}</a>
                                                            {*<p class="file-name text-justify">{$fileName}</p>*}
                                                        </div>
                                                        {if $caroucelItem eq 6}
                                                            </div><!--.row-->
                                                            </div><!--.item-->
                                                        {/if}
                                                        <!--</div>.container-->
                                                        {math equation='x + y' x=$caroucelItem y=1 assign='caroucelItem'}
                                                        {if $caroucelItem eq 7}
                                                            {assign var='caroucelItem' value=1}
                                                        {/if}
                                                    {/foreach}
                                                    {if ($caroucelItem neq 4) ||(($caroucelItem neq 1))}
                                                </div><!--.row-->
                                            </div><!--.item-->
                                            {/if}
                                        </div><!--.carousel-inner-->
                                        <a data-slide="prev" href="#Carousel-{$index}"
                                           class="left carousel-control{if $numIndicators|floor lt 1} hide{/if}">‹</a>
                                        <a data-slide="next" href="#Carousel-{$index}"
                                           class="right carousel-control{if $numIndicators|floor lt 1} hide{/if}">›</a>
                                    </div>
                                    {* /caraucel *}
                                    {/if}
                                    {* /carousel*}
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        var documents = [{$MENU}];

                        jQuery('#treeview').off('click').on('click', '.dx-treeview-node-is-leaf', function (event) {
                            MaterialsUtils.showDocumentPage(event, jQuery(this).attr('data-item-id'))
                        });
                    </script>
                    <script>window.jQuery || document.write(decodeURIComponent('%3Cscript src="js/jquery.min.js"%3E%3C/script%3E'))</script>
                    <script type="text/javascript" src="modules/materials/tree-view.js"></script>
                    <script type="text/javascript" src="modules/materials/materials-utils.js"></script>
                </div>
            </div>
    </section>
{/strip}