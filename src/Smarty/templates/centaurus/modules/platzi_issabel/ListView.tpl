{extends file='modules/platzi_issabel/Base/ListViewLayout.tpl'}
{strip}
    {block name="css"}
        <link type="text/css" rel="stylesheet" href="/themes/centaurus/css/compiled/list-view.css?v=1.1"/>
        <link type="text/css" rel="stylesheet" href="/themes/centaurus/css/compiled/pipeline.css?v=1.0"/>
        <link type="text/css" href="/themes/centaurus/css/libs/ns-style-other.css" rel="stylesheet" />
        <link type="text/css" href="/themes/centaurus/css/libs/ns-style-theme.css" rel="stylesheet" />
        <link type="text/css" href="/themes/centaurus/css/compiled/pipeline.css" rel="stylesheet" />
        <link type="text/css" href="/modules/Settings/editable-fields-utils.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="/themes/centaurus/css/libs/datepicker.css" />
        <link rel="stylesheet" type="text/css" href="/themes/centaurus/css/libs/daterangepicker.css" />
        <link rel="stylesheet" type="text/css" href="/themes/centaurus/css/libs/bootstrap-timepicker.css" />
        <style type="text/css">
            .main-box {
                box-shadow: 0px 0px 0px 0 #FFFFFF !important;
                border-radius: 0px !important;
            }

            .base-list-container {
                background-color: #ffffff;
                margin: 0 -13px !important;
                height: auto;
                min-height: 1150px !important;
            }

            .nav-platzilla {
                margin: 0 -15px !important;
                background: transparent;
            !important;
                border: none !important;
                box-shadow: 1px 1px 2px 0 #FFFFFF !important;
            }

            .nav-platzilla > li {
                border: none !important;
            }

            .nav-platzilla > li:hover {
                background-color: transparent;
            !important;
            }

            .nav-platzilla > li > a {
                font-weight: bold !important;
                color: #cccccc !important;
                border: none !important;
            }

            .nav-platzilla > li > a:hover {
                color: #000000 !important;
                border-bottom: 3px solid #000000 !important;
                background-color: transparent;
            !important;

            }

            .nav-platzilla > li.active {
                background-color: #FFFFFF;
                height: 46px;
            }

            .nav-platzilla > li.active a {
                color: #0266a9 !important;
                border-bottom: 3px solid #0266a9 !important;
                margin: 0 !important;
            }

            .border-left {
                border-left: 1px solid #dee2e6 !important
            }

            #ListViewContents {
                margin-top: 4px;
            }
        </style>
    {/block}
    {block name="page_header"}{/block}
    {block name = "header_title"}
        {include file='modules/platzi_issabel/Base/ActionButtonsBlock.tpl'}
    {/block}
    {block name="main_box_class"}main-box clearfix{/block}
    {block name="table_type"}{/block}
    {block name = "table_header"}
        <th class="text-center" style="width:14%;vertical-align: top; text-align: center;">
            <div style="display: inline-flex;">
                <div class="title-overflow">
                    <a href="#" title="Fecha de grabación" class="title-link" {*onclick=""*}>
                        <span>Fecha</span>
                    </a>
                </div>
            </div>
        </th>
        <th class="text-center" style="width:14%;vertical-align: top; text-align: center;">
            <div style="display: inline-flex;">
                <div class="title-overflow">
                    <a href="#" title="Hora de grabación" class="title-link" {*onclick=""*}>
                        <span>Hora</span>
                    </a>
                </div>
            </div>
        </th>
        <th class="text-center" style="width:14%;vertical-align: top; text-align: center;">
            <div style="display: inline-flex;">
                <div class="title-overflow">
                    <a href="#" title="Origen de grabación" class="title-link" {*onclick=""*}>
                        <span>Origen</span>
                    </a>
                </div>
            </div>
        </th>
        <th class="text-center" style="width:14%;vertical-align: top; text-align: center;">
            <div style="display: inline-flex;">
                <div class="title-overflow">
                    <a href="#" title="Destino" class="title-link" {*onclick=""*}>
                        <span>Destino</span>
                    </a>
                </div>
            </div>
        </th>
        <th class="text-center" style="width:14%;vertical-align: top; text-align: center;">
            <div style="display: inline-flex;">
                <div class="title-overflow">
                    <a href="#" title="Duración" class="title-link" {*onclick=""*}>
                        <span>Duración</span>
                    </a>
                </div>
            </div>
        </th>
        <th class="text-center" style="width:14%;vertical-align: top; text-align: center;">
            <div style="display: inline-flex;">
                <div class="title-overflow">
                    <a href="#" title="Tipo" class="title-link" {*onclick=""*}>
                        <span>Tipo</span>
                    </a>
                </div>
            </div>
        </th>
        <th class="text-center" style="width:15%;vertical-align: top; text-align: center;">
            <div style="display: inline-flex;">
                <div class="title-overflow">
                    <a href="#" title="Mensaje" class="title-link" {*onclick=""*}>
                        <span>Mensaje</span>
                    </a>
                </div>
            </div>
        </th>
    {/block}
    {block name = "table_body"}
        {*$ISSABEL_MONITORING|var_dump*}
        {if $ISSABEL_MONITORING neq NULL}
            {foreach $ISSABEL_MONITORING as $isabel}
                <tr>
                    <td class="text-center">
                        <a href="index.php?module=platzi_issabel&parenttab=&action=DetailView&record=&uniqueid={$isabel->getUniqueId()}"
                           title="Detalle de la grabación del {$isabel->getDate()}">{$isabel->getDate()}</a>
                    </td>
                    <td class="text-center">{$isabel->getTime()}</td>
                    <td class="text-center">{$isabel->getOrigin()}</td>
                    <td class="text-center">{$isabel->getDestination()}</td>
                    <td class="text-center">{$isabel->getDuration()}</td>
                    <td class="text-center">{$MOD[$isabel->getType()]}</td>
                    <td class="text-center">
                    {if $isabel->getMessage() neq NULL}
                        <a data-width="650" data-toggle="lightbox" data-parent="" data-gallery="remoteload" data-title=""
                        href="index.php?module=platzi_issabel&action=AjaxPlatziIssabelUtils&function=AUDIO_MONITORING&uniqueid={$isabel->getUniqueId()}&Ajax=true"
                           title="Reproducir audio de la grabación"><i class="fa fa-bullhorn" aria-hidden="true"></i>
                        </a>
                    {/if}
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr class="">
                <td colspan="7" class="text-center">No se encontró datos de grabación</td>
            </tr>
        {/if}
    {/block}
    {block name="page-data-platzi-issabel"}
            <span>Mostrando registros&nbsp;{$START_RECORD} - {$RECORDS_PER_PAGE}&nbsp;de&nbsp;{$ISSABEL_TOTAL_ROWS}</span>
        {/block}
        {block name = "pager-platzi-issabel"}
            <div id="pager-dv" class="text-right selection-stuff" style="display: block;">
                <ul id="pager-{$idPlatziIsabel}" class="pagination">
                    {if $ISSABEL_PAGER neq NULL}
                        {$ISSABEL_PAGER}
                    {else}
                        <li class="Pages"><a href="#"><strong>1</strong></a></li>
                    {/if}

                </ul>
            </div>
        {/block}
    {block name="js"}
        <script type="text/javascript" src="/themes/centaurus/js/modernizr.custom.js"></script>
        <script type="text/javascript" src="/themes/centaurus/js/snap.svg-min.js"></script>
        <script type="text/javascript" src="/themes/centaurus/js/classie.js"></script>
        <script type="text/javascript" src="/themes/centaurus/js/notificationFx.js"></script>
        <script type="text/javascript" src="/themes/centaurus/js/bootstrap-datepicker.es.js"></script>
        <script type="text/javascript" src="/modules/platzi_issabel/platzi_issabel.js"></script>
    {/block}
{/strip}