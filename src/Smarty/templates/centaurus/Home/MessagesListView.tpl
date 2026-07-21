{strip}
    {*<link type="text/css" rel="stylesheet" href="modules/graficosgenerales/graficosgenerales.css"/> *}
    <link type="text/css" rel="stylesheet" href="modules/materials/materials.css"/>
    <style type="text/css">
        .main-box {
            box-shadow: 0px 0px 0px 0 #FFFFFF !important;
            border-radius: 0px !important;
        }
        .base-list-container {
            margin: -20px -13px 0!important;
            padding: 0!important;
            height: auto;
            min-height: 1150px !important;
            border-top: 1px solid #dee2e6 !important
        }
        .nav-platzilla {
            margin:  -15px !important;
            margin-bottom: -3px !important;
        }
        .nav-platzilla > li > a {
            font-weight: bold !important;
        }
        .nav-platzilla > li.active {
            background-color: #FFFFFF;
            margin-bottom: -3px !important;
            height: 46px;
        }
        .border-left {
            border-left: 1px solid #dee2e6 !important
        }
        #ListViewContents {
            margin-top: 4px;
        }

    </style>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
    <link type="text/css" rel="stylesheet" href="modules/Courses/Courses.css"/>
    <link type="text/css" rel="stylesheet" href="modules/materials/materials.css"/>
    {if (!$CAN_CREATE_RECORDS)}
        <div class="alert alert-danger">
            <span><strong>Advertencia: </strong> El módulo está suscrito en modo de pruebas. Has llegado al límite de registros que puedes crear en este modo.</span>
            {if ($IS_ADMIN)}
                <span>Te invitamos a actualizar
                    <a href="index.php?module=Home&action=ViewSubscriptionDetails&tab=subscription">tu suscripción</a></span>
            {/if}
        </div>
    {/if}
    {if (!empty ($MESSAGE))}
        <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
            <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    {/if}
    {*if $DEFAULT_OPERATING eq 'MANAGEMENT_MODE'*}
        <div class="row module-buttons">
            <div class="col-lg-12" style="padding-right: 30px; padding-bottom: 10px;">
                <div class="pull-left">
                    <h1 style="margin-left: -3px;font-weight: bold">
                        Mensajes
                    </h1>
                </div>
                <div class="pull-right">
                    {if false}
                    <div class="btn-group" style="margin-right: 12px">
                        <a href="index.php?module=Home&action=index&tab_group=task"
                           class="{if $TAB_GROUP eq 'task'}btn btn-primary{else}btn btn-default{/if}"
                           style="height: 34px;">Panel Tareas</a>
                        <a href="index.php?module=Home&action=index&tab_group=records"
                           class="{if $TAB_GROUP eq 'records'}btn btn-primary{else}btn btn-default{/if}"
                           style="height: 34px;">Panel Registros</a>
                    </div>
                    {/if}
                </div>
            </div>
        </div>
    {*/if*}
    <div class="container-fluid base-list-container">
        {include file='Home/TabsContents/Messages.tpl'}
    </div>
    </script>
    <script type="text/html" id="email-viewer-modal-template">
    {include file='Home/EmailViewerModal.tpl'}
    </script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="webmail/program/js/common.min.js"></script>
    <script type="text/javascript" src="modules/webmail/webmail-utils.js?v=1.0.6"></script>
    <script src="themes/centaurus/js/dx.all.js"></script>
{/strip}