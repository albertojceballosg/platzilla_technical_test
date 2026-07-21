<link type="text/css" rel="stylesheet" href="themes/centaurus/css/bootstrap/bootstrap-toggle.min.css"/>
<style type="text/css">
    .glyphicon {
        margin-right: 10px;
    }

    .panel-body {
        padding: 0px;
    }

    .panel-body table tr td {
        padding-left: 15px
    }

    .panel-body .table {
        margin-bottom: 0px;
    }
</style>
{if $AUTOMATED_ACTIVITIES neq NULL}
    {math equation= rand() assign='idDesLink'}
    <div class="row">
        <div class="col-md-12">

        </div>
    </div>
    <div class="row">
        <div class="col-md-12" style="max-height: 600px; overflow-y: auto">
            <div class="nav nav-stacked row" id="accordion">
                <div class="panel">
                    <div class="panel-heading">
                        <h6 class="panel-title"
                            style="display:inline-block;width: 70%;vertical-align: middle; margin: 0 auto">
                            Contenido</h6>
                        <span class="pull-right" style="margin: 2px; display: inline-block;width: 15%;">&nbsp;
                            </span>
                        <a class="pull-right"  style="display:inline-block;width:8%; vertical-align:middle; margin: 0 auto"
                           data-toggle="collapse" data-parent="#accordion" href="#{$idDesLink}Link"><span
                                    class="glyphicon glyphicon-eye-close"></span></a>
                    </div>
                    <div id="{$idDesLink}Link" class="panel-collapse collapse in">
                        <div class="panel-body">
                            <table class="table">
                                {if $PROMO_VIDEO neq NULL}
                                <tr>
                                    <td>
                                        <div id="video-{$idDesLink}"
                                             class="embed-responsive embed-responsive-16by9 video"
                                             style="width: 100%; margin: 2px"
                                             data-vimeo-url="{$PROMO_VIDEO}"></div>
                                    </td>
                                </tr>
                                {/if}
                                <tr>
                                    <td>
                                        <div style="width: 100%; margin: 2px">
                                            {$PROMO_TEXT}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                {foreach $AUTOMATED_ACTIVITIES as $task}
                    {assign var='taskCategory' value=$task->getCategory ()}
                    {assign var='taskDescription' value=$task->getDescription ()}
                    {assign var='taskEvent' value=$task->getEvent ()}
                    {assign var='taskEventInstant' value=$task->getEventInstant ()}
                    {assign var='taskFrequency' value=$task->getFrequency ()}
                    {assign var='taskId' value=$task->getId ()}
                    {assign var='taskIsProtected' value=$task->isProtected ()}
                    {assign var='taskModuleLabel' value=$task->getModuleName ()|getTranslatedString: $task->getModuleName ()}
                    {assign var='taskName' value=$task->getName ()}
                    {assign var='taskModuleName' value=$task->getModuleName ()}
                    {assign var='taskStatus' value=$task->getStatus ()}
                    {assign var='taskTrigger' value=$task->getTrigger ()}
                    {assign var='taskVideo' value=$task->getUrlVideo ()}
                    <div  class="panel"  data-module="{$taskModuleName}" title="Modulo: {$taskModuleLabel}" >
                        <div class="panel-heading">
                            <h6 class="panel-title"
                                style="display:inline-block;width: 70%;vertical-align: middle; margin: 0 auto">{$taskName}</h6>
                            <span class="pull-right" style="margin: 2px; display: inline-block;width: 15%;">
                                <input class="status-task"  id="chck-{$taskId}" data-status="{$taskStatus}" type="checkbox" {if $taskStatus neq 'DISABLED'}checked{/if}
                                       data-toggle="toggle" data-on="On" data-off="Off" data-offstyle="danger" data-onstyle="success"
                                       data-size="small">
                            </span>
                            <a class="pull-right" style="display:inline-block;width: 8%;vertical-align: middle; margin: 0 auto"
                               data-toggle="collapse" data-parent="#accordion" href="#{$taskId}Link"><span
                                        class="glyphicon glyphicon-eye-open"></span></a>
                        </div>
                        <div id="{$taskId}Link" class="panel-collapse collapse">
                            <div class="panel-body">
                                <table class="table">
                                    {if $taskVideo neq NULL}
                                        <tr>
                                            <td>
                                                <div class="col-md-12">
                                                    <div id="video-{$taskId}"
                                                         class="embed-responsive embed-responsive-16by9 video"
                                                         style="width: 100%; margin: 2px"
                                                         data-vimeo-url="{$taskVideo}"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    {/if}
                                    <tr>
                                        <td>
                                            <div style="width: 100%; margin: 2px">
                                                {$taskDescription}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
    <script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-toggle.min.js"></script>
    <script type="text/javascript">
        jQuery (document).on ('ready', function () {
            jQuery ('input[id ^= chck-]').bootstrapToggle ();
        });

        jQuery (function () {
            jQuery ('input[id ^= chck-]').change (function(e) {
                var check     = jQuery (this),
                    status    = check.attr ('data-status'),
                    idArr     = check.attr ('id').split ('-'),
                    arguments = [
                        'module=backgroundtasks',
                        'action=UpdateStatusTask',
                        'record=' + encodeURIComponent(idArr [1]),
                        'statustask=' + encodeURIComponent(status),
                        'Ajax=true'
                    ];
                check.bootstrapToggle ('disable');
                jQuery.ajax('index.php', {
                    data: arguments.join('&'),
                    dataType: 'text',
                    method: 'post'
                }).done(function (data) {
                    var response = jQuery.parseJSON(data);
                    if ((response.message === 'DISABLED') || (response.message === 'ENABLED')) {
                        check.attr('data-status', response.message)
                    } else {
                        // info.html(response.message)
                    }
                    check.bootstrapToggle('enable');
                });
            })

        });

        jQuery ('.panel-collapse').on('shown.bs.collapse', function () {
            var contenet = jQuery(this).parent();
            contenet.find('a').eq(0).html('<span class="glyphicon glyphicon-eye-close"></span>')

        });

        jQuery ('.panel-collapse').on('hidden.bs.collapse', function () {
            var contenet = jQuery(this).parent();
            contenet.find('a').eq(0).html('<span class="glyphicon glyphicon-eye-open"></span>')
        });
    </script>
{else}
    <div class="row">
        <div class="col-md-12">
            <h4>No hay tareas automatizadas para el módulo {$FORMODULE}</h4>
        </div>
    </div>
{/if}