{strip}
	{assign var="include_report_wizard_css" value=true}
    {math equation= rand() assign= "idReport"}
    <div class="main-box clearfix" style="background-color: transparent!important;padding: 0!important;">
        <div class="main-box-body clearfix">
            {if (empty ($AVAILABLE_FOLDERS))}
                <div class="alert alert-warning text-center">
                    No hay Informes
                </div>
            {else}
                <div class="row">
                    <div class="col-sm-10 col-md-10 col-lg-10" style="margin-top: 2px!important;">
                        <div class="btn-group">
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
                    </div>
                    <div class="col-sm-2 col-md-2 col-lg-2" style="margin-top: 2px!important;">
                        &nbsp;
                    </div>
                    {* Tabs Reports *}
                </div>
                {* Inicio de Report*}
                <div id="reportContents" class="col-md-12">
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
                </div>
                {*fin de Report*}
            {/if}
        </div>
        <div id="orgLay" style="display: none;" class="modal fade in" role="dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="fninvsh ('orgLay');">X</button>
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
                                <input type="hidden" id="folder_id" name="folderId" value="" />
                                <input type="hidden" id="fldrsave_mode" name="folderId" value="save">
                                <input type="text" id="folder_name" name="folderName" class="form-control" maxlength="100" />
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
                                <input type="text" id="folder_desc" name="folderDesc" class="form-control" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" name="save" class="btn btn-success" onclick="AddFolder ()">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
                    <button type="button" name="cancel" class="btn btn-warning" onclick="closeEditReport ();">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
                </div>
            </div>
        </div>
    </div> {* VIEW='METRICS'   *}
    {include file='ReportWizard.tpl' VIEW='LIST_VIEW' FILE_ID=$idReport}
    <script type="text/javascript">
        {literal}
        jQuery ( document ).ready(function() {
            ReportWizardUtils.setCurrentTab ('report');
        });
        function createrepFolder (oLoc, divid) {
            getObj ('fldrsave_mode').value = 'save';
            $ ('folder_id').value = '';
            $ ('folder_name').value = '';
            $ ('folder_desc').value = '';
            fnvshobj (oLoc, divid);
        }
        function closeEditReport () {
            $ ('folder_id').value = '';
            $ ('folder_name').value = '';
            $ ('folder_desc').value = '';
            fninvsh ('orgLay')
        }
        function DeleteFolder (id) {
            var arguments;
            if (!confirm ('¿Estás seguro que quieres eliminar la carpeta seleccionada?')) {
                return;
            }
            arguments = [
                'module=Reports',
                'action=ReportsAjax',
                'mode=ajax',
                'file=DeleteReportFolder',
                'record=' + encodeURIComponent (id)
            ];
            jQuery.ajax ('index.php', {
                data: arguments.join ('&'),
                dataType: 'text',
                method: 'post'
            }).done (function (response) {
                window.location.reload ();
            });
        }
        function AddFolder () {
            if (getObj ('folder_name').value.replace (/^\s+/g, '').replace (/\s+$/g, '').length == 0) {
                alert ({/literal}'{$APP.FOLDERNAME_CANNOT_BE_EMPTY}'{literal});
            } else if ((getObj ('folder_name').value).match (/['"<>/\+]/) || (getObj ('folder_desc').value).match (/['"<>/\+]/)) {
                alert (alert_arr.SPECIAL_CHARS + ' ' + alert_arr.NOT_ALLOWED + alert_arr.NAME_DESC);
            } else {
                var foldername = encodeURIComponent (getObj ('folder_name').value);
                new Ajax.Request (
                    'index.php',
                    {
                        queue:      { position: 'end', scope: 'command' },
                        method:     'post',
                        postBody:   'action=ReportsAjax&mode=ajax&file=CheckReport&module=Reports&check=folderCheck&folderName=' + foldername,
                        onComplete: function (response) {
                            var folderid = getObj ('folder_id').value,
                                resresult = response.responseText.split ("::"),
                                mode = getObj ('fldrsave_mode').value,
                                url;
                            if (resresult[ 0 ] != 0 && mode == 'save' && resresult[ 0 ] != 999) {
                                alert ({/literal}"{$APP.FOLDER_NAME_ALREADY_EXISTS}"{literal});
                                return false;
                            } else if (((resresult[ 0 ] != 1 && resresult[ 0 ] != 0) || (resresult[ 0 ] == 1 && resresult[ 0 ] != 0 && resresult[ 1 ] != folderid )) && mode == 'Edit' && resresult[ 0 ] != 999) {
                                alert ({/literal}"{$APP.FOLDER_NAME_ALREADY_EXISTS}"{literal});
                                return false;
                            } else if (response.responseText == 999) {
                                alert ({/literal}"{$APP.SPECIAL_CHARS_NOT_ALLOWED}"{literal});
                                return false;
                            } else {
                                fninvsh ('orgLay');
                                var folderdesc = encodeURIComponent (getObj ('folder_desc').value);
                                getObj ('folder_name').value = '';
                                getObj ('folder_desc').value = '';
                                foldername = foldername.replace (/^\s+/g, '').replace (/\s+$/g, '');
                                foldername = foldername.replace (/&/gi, '*amp*');
                                folderdesc = folderdesc.replace (/^\s+/g, '').replace (/\s+$/g, '');
                                folderdesc = folderdesc.replace (/&/gi, '*amp*');
                                if (mode == 'save') {
                                    url = '&savemode=Save&foldername=' + foldername + '&folderdesc=' + folderdesc;
                                } else {
                                    folderid = getObj ('folder_id').value;
                                    url = '&savemode=Edit&foldername=' + foldername + '&folderdesc=' + folderdesc + '&record=' + folderid;
                                }
                                getObj ('fldrsave_mode').value = 'save';
                                new Ajax.Request (
                                    'index.php',
                                    {
                                        queue:      { position: 'end', scope: 'command' },
                                        method:     'post',
                                        postBody:   'action=ReportsAjax&mode=ajax&file=SaveReportFolder&module=Reports' + url,
                                        onComplete: function (response) {
                                            window.location.reload (true);
                                        }
                                    }
                                );
                            }
                        }
                    }
                );
            }
        }
        function EditFolder (id, name, desc) {
            $ ('editfolder_info').innerHTML = {/literal}' {$MOD.LBL_RENAME_FOLDER} '{literal};
            getObj ('folder_name').value = name;
            getObj ('folder_desc').value = desc;
            getObj ('folder_id').value = id;
            getObj ('fldrsave_mode').value = 'Edit';
        }
        function massDeleteReport () {
            var folderids = getObj ('folder_ids').value,
                folderid_array = folderids.split (','),
                idstring = '',
                count = 0,
                i, row;
            for (i = 0; i < folderid_array.length; i++) {
                var selectopt_id = 'selected_id' + folderid_array[ i ];
                var objSelectopt = getObj (selectopt_id);
                if (objSelectopt != null) {
                    var length_folder = getObj (selectopt_id).length;
                    if (length_folder != undefined) {
                        var cur_rep = getObj (selectopt_id);
                        for (row = 0; row < length_folder; row++) {
                            var currep_id = cur_rep[ row ].value;
                            if (cur_rep[ row ].checked) {
                                count++;
                                idstring = currep_id + ':' + idstring;
                            }
                        }
                    } else {
                        if (getObj (selectopt_id).checked) {
                            count++;
                            idstring = getObj (selectopt_id).value + ':' + idstring;
                        }
                    }
                }
            }
            if (idstring != '') {
                if (confirm ({/literal}"{$APP.DELETE_CONFIRMATION}"{literal} + count + "{$APP.RECORDS}")) {
                    new Ajax.Request (
                        'index.php',
                        {
                            queue:      { position: 'end', scope: 'command' },
                            method:     'post',
                            postBody:   'action=ReportsAjax&mode=ajax&file=Delete&module=Reports&idlist=' + idstring,
                            onComplete: function (response) {
                                getObj ('customizedrep').innerHTML = response.responseText;
                            }
                        }
                    );
                } else {
                    return false;
                }
            } else {
                alert ({/literal}'{$APP.SELECT_ATLEAST_ONE_REPORT}'{literal});
                return false;
            }
        }
        function deleteReport (id) {
            var postIt = jQuery('#post-it-' + id);
            if (confirm ({/literal}"{$APP.DELETE_REPORT_CONFIRMATION}"{literal})) {
                new Ajax.Request (
                    'index.php',
                    {
                        queue:      { position: 'end', scope: 'command' },
                        method:     'post',
                        postBody:   'action=ReportsAjax&file=Delete&module=Reports&from=metrics&record=' + id,
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
        function MoveReport (id, foldername) {
            fninvsh ('folderLay');
            var folderids = getObj ('folder_ids').value,
                folderid_array = folderids.split (','),
                idstring = '',
                count = 0,
                i, row;
            for (i = 0; i < folderid_array.length; i++) {
                var selectopt_id = 'selected_id' + folderid_array[ i ];
                var objSelectopt = getObj (selectopt_id);
                if (objSelectopt != null) {
                    var length_folder = getObj (selectopt_id).length;
                    if (length_folder != undefined) {
                        var cur_rep = getObj (selectopt_id);
                        for (row = 0; row < length_folder; row++) {
                            var currep_id = cur_rep[ row ].value;
                            if (cur_rep[ row ].checked) {
                                count++;
                                idstring = currep_id + ':' + idstring;
                            }
                        }
                    } else {
                        if (getObj (selectopt_id).checked) {
                            count++;
                            idstring = getObj (selectopt_id).value + ':' + idstring;
                        }
                    }
                }
            }
            if (idstring != '') {
                if (confirm ({/literal}"{$APP.MOVE_REPORT_CONFIRMATION}" + foldername + "{$APP.FOLDER}"{literal})) {
                    new Ajax.Request (
                        'index.php',
                        {
                            queue:      { position: 'end', scope: 'command' },
                            method:     'post',
                            postBody:   'action=ReportsAjax&file=ChangeFolder&module=Reports&folderid=' + id + '&idlist=' + idstring,
                            onComplete: function (response) {
                                getObj ('reportContents').innerHTML = response.responseText;
                            }
                        }
                    );
                }
            } else {
                alert ({/literal}'{$APP.SELECT_ATLEAST_ONE_REPORT}'{literal});
            }
        }
        {/literal}
    </script>
{/strip}