{block name="css"}
    <link type="text/css" rel="stylesheet" href="/modules/Reports/foldrer-report.css"/>
    <style type="text/css">
        .main-box {
            box-shadow: 0px 0px 0px 0 #FFFFFF !important;
            border-radius: 0px !important;
        }

        .base-list-container {
            background-color: #ffffff;
            margin: 0px -13px !important;
            border-top: 1px solid #D8D8D8 !important;
            height: auto;
            min-height: 1150px !important;
        }

        @media (min-width: 768px) {
            .wizard-modal.modal {
                max-width: 1024px;
            }
        }

        @media (max-width: 767px) {
            .linea {
                display: inline-block !important;
                margin-right: 10px !important;
            }
        }
    </style>
{/block}
{block name="first-content"}
    {math equation='x - y' x=12 y=$STATUS_TOTAL_BUTTONS assign='col'}
    <div class="container-fluid base-list-container" {if $REQUEST_FROM eq 'DETALVIEW'}style="background-color: transparent!important;border-top: none!important"{/if}>
        <div class="row">
            <div class="col-lg-12">
                <div class="main-box clearfix" {if $REQUEST_FROM eq 'DETALVIEW'}style="background-color: transparent!important;{/if}">
                    <div class="main-box-header clearfix">
                        <div class="row">
                            <div class="col-xs-6 clo-sm-4 col-md-3 col-lg-3" style="padding-left: 0;width: 50%">
                                {* /new combobxo button to  option *}
                                <div class="btn-group pull-left" {if $REQUEST_FROM eq 'DETALVIEW'}style="margin-left: -2.8em"{/if}>
                                    {if $REQUEST_FROM eq 'DETALVIEW'}
                                        <a data-toggle="tab" href="#tab-metrics-graphics-{$TAB_HOME_ID}" class="btn btn-default" style=" font-size: 15px!important;"><i class="fa fa-bar-chart-o"></i></a>
                                        <button type="button" class="btn btn-primary" title="vista calendario" style=" font-size: 15px!important;"><i class="fa fa-file" aria-hidden="true"></i>
                                        </button>
                                    {else}
                                        {* LIST-VIEW
                                        <a data-toggle="tab" href="#ListViewContents" class="btn btn-default"
                                           style=" font-size: 15px!important;"
                                           onclick="ListViewTabUtils.activeListTab(event)"
                                           data-toggle="tab" title="Listado de registros"><i
                                                    class="fa fa-list-ul"></i></a>
                                         *}
                                        {* LIST-VIEW-KANBAN-VIEW *}
                                        {if $STATUS_BUTTONS['kanban'] && false}
                                            <a data-toggle="tab" href="#LIST-VIEW-KANBAN-VIEW"
                                               class="btn btn-default" style=" font-size: 15px!important;"
                                               title="Vista kanban"
                                               onclick="ListViewTabUtils.activeKanbanTab (event)"
                                               data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
                                        {/if}
                                        {* LIST-VIEW-BOX-SCORE *}
                                        {if $STATUS_BUTTONS['boxscore']}
                                            <a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE" class="btn btn-default"
                                               style="font-size: 15px!important; margin-right:0.05em;margin-left:0.05em;"
                                               onclick="ListViewTabUtils.activeBoxScoreTab (event)"
                                               data-toggle="tab"><i class="fa fa-heart-o"></i></a>
                                        {/if}
                                        {* LIST-VIEW-GRAPHIC *}
                                        {if $STATUS_BUTTONS['graphic']}
                                            <a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default"
                                               style="font-size: 15px!important; margin-right:0.05em;margin-left:0.05em;"
                                               onclick="ListViewTabUtils.activeGraphicTab (event)"
                                               data-toggle="tab"><i class="fa fa-bar-chart-o"></i></a>
                                        {/if}
                                        {* report *}
                                        {if $STATUS_BUTTONS['report']}
                                            <button type="button" class="btn btn-primary"
                                                    title="Informes"
                                                    style="font-size: 15px!important; margin-right:0.05em;margin-left:0.05em;"><i class="fa fa-file"
                                                                                           aria-hidden="true"></i>
                                            </button>
                                        {/if}
                                        {* LIST-VIEW-CALENDAR *}
                                        {if $STATUS_BUTTONS['calendar'] && false}
                                            <a data-toggle="tab" href="#LIST-VIEW-CALENDAR" class="btn btn-default"
                                               style=" font-size: 15px!important;"
                                               title="vista calendario"
                                               onclick="ListViewTabUtils.activeCalendarTab (event)"
                                               data-toggle="tab"><i class="fa fa-calendar"></i></a>
                                        {/if}
                                    {/if}
                                </div>
                                {* new combobxo button to  option *}
                            </div>
                            <div class="col-xs-6 col-sm-4 col-md-6 col-lg-6" style="padding-left: 0;width: 50%">
                                {if (!empty ($AVAILABLE_FOLDERS))}
                                <div class="btn-group pull-right {if empty($FAVORITES_REPORTS) && ($IS_INSTANCE)}hide{/if}" {if $REQUEST_FROM eq 'DETALVIEW'}style="margin-right: -2.7em;"{/if}>
                                    {if empty($FAVORITES_REPORTS)}
                                        {assign var='hasActiveTab' value=true}
                                    {else}
                                        {assign var='hasActiveTab' value=false}
                                    {/if}
                                    {if (!empty ($FOLDERS_TAB))}
                                        {foreach $FOLDERS_TAB as $tabFolder}
                                            {if $tabFolder['foldername'] eq 'Personalizados'}
                                                {assign var='hasActiveTab' value=true}
                                            {/if}
                                            {assign var='tabValue' value=$tabFolder['foldername']|cat:'-'|cat:$tabFolder['folderid']}
                                            <a href="#tab-{$tabValue}"
                                               rel="{$idReport}"
                                               class="btn  {if ($hasActiveTab)}btn-primary{else}btn-default{/if}"
                                               data-toggle="tab"
                                               onclick="GraphUtils.setTab(this, event, '{$tabValue}')">{$tabFolder['foldername']}</a>
                                            {assign var='hasActiveTab' value=false}
                                        {/foreach}
                                    {/if}
                                </div>
                                {/if}
                                {if $IS_INSTANCE && false}
                                    <div class="btn-group" style="float: left!important;margin-right: 0">
                                        <button  id="std-report-btn"  type="button" data-partner="#custom-report-btn" data-category="STANDARD" class="btn btn-primary" onclick="ListViewTabUtils.getReportByCategory (this)">Estándar</button>
                                        <button id="custom-report-btn" type="button" data-partner="#std-report-btn" data-category="CUSTOM" class="btn btn-default" onclick="ListViewTabUtils.getReportByCategory (this)">Personalizado</button>
                                    </div>
                                {/if}
                            </div>
                        </div>
                        {* Report *}
                        <div id="reportContents" class="row" style="margin-top: 10px; padding: 0">
                            {if (!empty ($AVAILABLE_FOLDERS) && !empty($FOLDERS_TAB))}
                                <div class="tab-content" style="margin-top: 18px">
                                    {if empty($FAVORITES_REPORTS)}
                                        {assign var='hasActiveTab' value=true}
                                    {else}
                                        {assign var='hasActiveTab' value=false}
                                    {/if}
                                    {foreach $FOLDERS_TAB as $tabFolder}
                                        {if $tabFolder['foldername'] eq 'Personalizados'}
                                            {assign var='hasActiveTab' value=true}
                                        {/if}
                                        {assign var='tabValue' value=$tabFolder['foldername']|cat:'-'|cat:$tabFolder['folderid']}
                                        <div id="tab-{$tabValue}" class="tab-pane fade in {if ($hasActiveTab)}active{/if}">
                                            {if $tabFolder['foldername'] neq 'Personalizados'}
                                                {assign var='hasActiveTab' value=false}
                                                <div class="row">
                                                    <div class="col-xs-12">
                                                        <ul id="paper-sheet">
                                                            {foreach $AVAILABLE_FOLDERS as $folder}
                                                                {if (($folder['foldername'] neq $tabFolder['foldername']) ||
                                                                ($folder['folderid'] neq $tabFolder['folderid'])||
                                                                (empty($folder['reports'])))  && ($folder['foldername'] neq 'Personalizados')  }
                                                                    {continue}
                                                                {/if}

                                                                {foreach $folder['reports'] as $report}
                                                                    {if (in_array($report.reportid, $FAVORITES_REPORTS))}
                                                                        {continue}
                                                                    {/if}
                                                                    <li id="post-it-{$report.reportid}" class="post-it">
                                                                        <div class="btn-group btn-group-sm pull-right">
                                                                            {if ($report.customizable == '1') && ($report.editable == 'true')}
                                                                                <button type="button" class="btn btn-info" title="{$MOD.LBL_CUSTOMIZE_BUTTON}" onclick="ReportWizardUtils.show ('{$folder.folderid}', '{$report.reportid}');"><i class="fa fa-pencil"></i></button>
                                                                            {/if}
                                                                            {if ($report.state != 'SAVED') && ($report.editable == 'true')}
                                                                                <button type="button" class="btn btn-danger" title="{$MOD.LBL_DELETE}" onclick="deleteReport ('{$report.reportid}');"><i class="fa fa-trash-o"></i></button>
                                                                            {/if}
                                                                        </div>
                                                                        <div  style="margin-top: 2em" {*class="post-it"*}>
                                                                            <i class="post-it__icon fa fa-info-circle"></i>&nbsp;
                                                                            <a href="index.php?module=Reports&action=SaveAndRun&record={$report.reportid}&folderid={$folder.folderid}" target="_blank">{$report.reportname}</a><br>
                                                                            {$report.description}
                                                                        </div>
                                                                    </li>
                                                                {/foreach}
                                                            {/foreach}
                                                        </ul>
                                                    </div>
                                                </div>
                                            {else}
                                                {assign var='hasActiveTab' value=true}
                                                <div class="row">
                                                    <div class="col-xs-12">
                                                        <ul id="paper-sheet">
                                                            {foreach $AVAILABLE_FOLDERS as $folder}
                                                                {foreach $folder['reports'] as $report}
                                                                    {if (!in_array($report.reportid, $FAVORITES_REPORTS))}
                                                                        {continue}
                                                                    {/if}
                                                                    <li id="post-it-{$report.reportid}" class="post-it">
                                                                        <div class="btn-group btn-group-sm pull-right">
                                                                            {if ($report.customizable == '1') && ($report.editable == 'true')}
                                                                                <button type="button" class="btn btn-info" title="{$MOD.LBL_CUSTOMIZE_BUTTON}" onclick="ReportWizardUtils.show ('{$folder.folderid}', '{$report.reportid}');"><i class="fa fa-pencil"></i></button>
                                                                            {/if}
                                                                            {if ($report.state != 'SAVED') && ($report.editable == 'true')}
                                                                                <button type="button" class="btn btn-danger" title="{$MOD.LBL_DELETE}" onclick="deleteReport ('{$report.reportid}');"><i class="fa fa-trash-o"></i></button>
                                                                            {/if}
                                                                        </div>
                                                                        <div  style="margin-top: 2em" {*class="post-it"*}>
                                                                            <i class="fa fa-folder-o" aria-hidden="true"></i>&nbsp;<strong>{$folder['foldername']}</strong><br>
                                                                            <i class="post-it__icon fa fa-info-circle"></i>&nbsp;
                                                                            <a href="index.php?module=Reports&action=SaveAndRun&record={$report.reportid}&folderid={$folder.folderid}" target="_blank">{$report.reportname}</a><br>
                                                                            {$report.description}
                                                                        </div>
                                                                    </li>
                                                                {/foreach}
                                                            {/foreach}
                                                        </ul>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                    {/foreach}
                                </div>
                            {else}
                                <div class="col-lg-12">
                                    <div class="main-box no-header clearfix">
                                        <div class="main-box-body clearfix">
                                            <div class="alert alert-info" style="margin-bottom: 0;">No se encuentran
                                                informes registrados
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        </div>
                        <div id="orgLay" style="display: none;" class="modal fade in" role="dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"
                                            onclick="fninvsh ('orgLay');">X
                                    </button>
                                    <h4 class="modal-title">{$MOD.LBL_ADD_NEW_GROUP}</h4>
                                </div>
                                <div class="row modal-body">
                                    <div class="col-xs-12">
                                        <div class="col-xs-5">
                                            <div class="label-input">
                                                <label for="folder_name">{$MOD.LBL_REP_FOLDER_NAME}</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-7">
                                            <div class="input-group" style="width: 100%;">
                                                <input type="hidden" id="folder_id" name="folderId" value=""/>
                                                <input type="hidden" id="fldrsave_mode" name="folderId" value="save">
                                                <input type="text" id="folder_name" name="folderName"
                                                       class="form-control" maxlength="100"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12">
                                        <div class="col-xs-5">
                                            <div class="label-input">
                                                <label for="folder_desc">{$MOD.LBL_REP_FOLDER_DESC}</label>
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-7" style="margin-bottom: 0;">
                                            <div class="input-group" style="width: 100%;">
                                                <input type="text" id="folder_desc" name="folderDesc"
                                                       class="form-control"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" name="save" class="btn btn-success"
                                            onclick="AddFolder ()">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
                                    <button type="button" name="cancel" class="btn btn-warning"
                                            onclick="closeEditReport ();">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
                                </div>
                            </div>
                        </div>
                        {* Report *}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {*</div> *}

    {if (!empty ($AVAILABLE_FOLDERS) && !empty($FOLDERS_TAB))}
    {math equation= rand() assign= "idFile"}
    {include file='ReportWizard.tpl' VIEW='LIST_VIEW' FILE_ID= $idFile}
    {/if}
{/block}
{block name="js"}
    <script type="text/javascript" src="/modules/Reports/Reports.js"></script>
    <script type="text/javascript">
{if (!empty ($AVAILABLE_FOLDERS) && !empty($FOLDERS_TAB))}
        {literal}
    jQuery.noConflict()(function ($) {
        $(document).ready(function () {
            $('.wizard-modal').removeAttr('style');
            ReportWizardUtils.setCurrentTab ('report');
        });
    });
        {/literal}
{/if}
        {literal}
        function createrepFolder(oLoc, divid) {
            getObj('fldrsave_mode').value = 'save';
            $('folder_id').value = '';
            $('folder_name').value = '';
            $('folder_desc').value = '';
            fnvshobj(oLoc, divid);
        }

        function closeEditReport() {
            $('folder_id').value = '';
            $('folder_name').value = '';
            $('folder_desc').value = '';
            fninvsh('orgLay')
        }

        function DeleteFolder(id) {
            var arguments;
            if (!confirm('¿Estás seguro que quieres eliminar la carpeta seleccionada?')) {
                return;
            }

            arguments = [
                'module=Reports',
                'action=ReportsAjax',
                'mode=ajax',
                'file=DeleteReportFolder',
                'record=' + encodeURIComponent(id)
            ];
            jQuery.ajax('index.php', {
                data: arguments.join('&'),
                dataType: 'text',
                method: 'post'
            }).done(function (response) {
                window.location.reload();
            });
        }

        function AddFolder() {
            if (getObj('folder_name').value.replace(/^\s+/g, '').replace(/\s+$/g, '').length == 0) {
                alert({/literal}'{$APP.FOLDERNAME_CANNOT_BE_EMPTY}'{literal});
            } else if ((getObj('folder_name').value).match(/['"<>/\+]/) || (getObj('folder_desc').value).match(/['"<>/\+]/)) {
                alert(alert_arr.SPECIAL_CHARS + ' ' + alert_arr.NOT_ALLOWED + alert_arr.NAME_DESC);
            } else {
                var foldername = encodeURIComponent(getObj('folder_name').value);
                new Ajax.Request(
                    'index.php',
                    {
                        queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'action=ReportsAjax&mode=ajax&file=CheckReport&module=Reports&check=folderCheck&folderName=' + foldername,
                        onComplete: function (response) {
                            var folderid = getObj('folder_id').value,
                                resresult = response.responseText.split("::"),
                                mode = getObj('fldrsave_mode').value,
                                url;
                            if (resresult[0] != 0 && mode == 'save' && resresult[0] != 999) {
                                alert({/literal}"{$APP.FOLDER_NAME_ALREADY_EXISTS}"{literal});
                                return false;
                            } else if (((resresult[0] != 1 && resresult[0] != 0) || (resresult[0] == 1 && resresult[0] != 0 && resresult[1] != folderid)) && mode == 'Edit' && resresult[0] != 999) {
                                alert({/literal}"{$APP.FOLDER_NAME_ALREADY_EXISTS}"{literal});
                                return false;
                            } else if (response.responseText == 999) {
                                alert({/literal}"{$APP.SPECIAL_CHARS_NOT_ALLOWED}"{literal});
                                return false;
                            } else {
                                fninvsh('orgLay');
                                var folderdesc = encodeURIComponent(getObj('folder_desc').value);
                                getObj('folder_name').value = '';
                                getObj('folder_desc').value = '';
                                foldername = foldername.replace(/^\s+/g, '').replace(/\s+$/g, '');
                                foldername = foldername.replace(/&/gi, '*amp*');
                                folderdesc = folderdesc.replace(/^\s+/g, '').replace(/\s+$/g, '');
                                folderdesc = folderdesc.replace(/&/gi, '*amp*');
                                if (mode == 'save') {
                                    url = '&savemode=Save&foldername=' + foldername + '&folderdesc=' + folderdesc;
                                } else {
                                    folderid = getObj('folder_id').value;
                                    url = '&savemode=Edit&foldername=' + foldername + '&folderdesc=' + folderdesc + '&record=' + folderid;
                                }
                                getObj('fldrsave_mode').value = 'save';
                                new Ajax.Request(
                                    'index.php',
                                    {
                                        queue: {position: 'end', scope: 'command'},
                                        method: 'post',
                                        postBody: 'action=ReportsAjax&mode=ajax&file=SaveReportFolder&module=Reports' + url,
                                        onComplete: function (response) {

                                            window.location.reload(true);
                                        }
                                    }
                                );
                            }
                        }
                    }
                );
            }
        }

        function EditFolder(id, name, desc) {
            $('editfolder_info').innerHTML = {/literal}' {$MOD.LBL_RENAME_FOLDER} '{literal};
            getObj('folder_name').value = name;
            getObj('folder_desc').value = desc;
            getObj('folder_id').value = id;
            getObj('fldrsave_mode').value = 'Edit';
        }

        function massDeleteReport() {
            var folderids = getObj('folder_ids').value,
                folderid_array = folderids.split(','),
                idstring = '',
                count = 0,
                i, row;
            for (i = 0; i < folderid_array.length; i++) {
                var selectopt_id = 'selected_id' + folderid_array[i];
                var objSelectopt = getObj(selectopt_id);
                if (objSelectopt != null) {
                    var length_folder = getObj(selectopt_id).length;
                    if (length_folder != undefined) {
                        var cur_rep = getObj(selectopt_id);
                        for (row = 0; row < length_folder; row++) {
                            var currep_id = cur_rep[row].value;
                            if (cur_rep[row].checked) {
                                count++;
                                idstring = currep_id + ':' + idstring;
                            }
                        }
                    } else {
                        if (getObj(selectopt_id).checked) {
                            count++;
                            idstring = getObj(selectopt_id).value + ':' + idstring;
                        }
                    }
                }
            }
            if (idstring != '') {
                if (confirm({/literal}"{$APP.DELETE_CONFIRMATION}"{literal} + count + "{$APP.RECORDS}")) {
                    new Ajax.Request(
                        'index.php',
                        {
                            queue: {position: 'end', scope: 'command'},
                            method: 'post',
                            postBody: 'action=ReportsAjax&mode=ajax&file=Delete&module=Reports&idlist=' + idstring,
                            onComplete: function (response) {
                                getObj('customizedrep').innerHTML = response.responseText;
                            }
                        }
                    );
                } else {
                    return false;
                }
            } else {
                alert({/literal}'{$APP.SELECT_ATLEAST_ONE_REPORT}'{literal});
                return false;
            }
        }

        function deleteReport(id) {
            var postIt = jQuery('#post-it-' + id);
            if (confirm({/literal}"{$APP.DELETE_REPORT_CONFIRMATION}"{literal})) {
                new Ajax.Request(
                    'index.php',
                    {
                        queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'action=ReportsAjax&file=Delete&module=Reports&from=metrics&record=' + id + '&tab=report',
                        onComplete: function (response) {
                            try {
                                if(response.responseText !== 'OK') {
                                    throw response.responseText
                                } else {
                                    postIt.remove();
                                }
                            }
                            catch (e) {
                                alert(e);
                            }
                        }
                    }
                );
            } else {
                return false;
            }
        }

        function MoveReport(id, foldername) {
            fninvsh('folderLay');
            var folderids = getObj('folder_ids').value,
                folderid_array = folderids.split(','),
                idstring = '',
                count = 0,
                i, row;
            for (i = 0; i < folderid_array.length; i++) {
                var selectopt_id = 'selected_id' + folderid_array[i];
                var objSelectopt = getObj(selectopt_id);
                if (objSelectopt != null) {
                    var length_folder = getObj(selectopt_id).length;
                    if (length_folder != undefined) {
                        var cur_rep = getObj(selectopt_id);
                        for (row = 0; row < length_folder; row++) {
                            var currep_id = cur_rep[row].value;
                            if (cur_rep[row].checked) {
                                count++;
                                idstring = currep_id + ':' + idstring;
                            }
                        }
                    } else {
                        if (getObj(selectopt_id).checked) {
                            count++;
                            idstring = getObj(selectopt_id).value + ':' + idstring;
                        }
                    }
                }
            }
            if (idstring != '') {
                if (confirm({/literal}"{$APP.MOVE_REPORT_CONFIRMATION}" + foldername + "{$APP.FOLDER}"{literal})) {
                    new Ajax.Request(
                        'index.php',
                        {
                            queue: {position: 'end', scope: 'command'},
                            method: 'post',
                            postBody: 'action=ReportsAjax&file=ChangeFolder&module=Reports&folderid=' + id + '&idlist=' + idstring,
                            onComplete: function (response) {
                                getObj('reportContents').innerHTML = response.responseText;
                            }
                        }
                    );
                }
            } else {
                alert({/literal}'{$APP.SELECT_ATLEAST_ONE_REPORT}'{literal});
            }
        };
        {/literal}
    </script>
{/block}