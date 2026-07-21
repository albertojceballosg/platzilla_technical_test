{*<!--
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}

{*<!-- module header -->*}
<script language="JavaScript" type="text/javascript" src="include/js/ListView.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/search.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/Merge.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/dtlviewajax.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/FieldDependencies.js"></script>

<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/wizard.css"/>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-default.css"/>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-growl.css"/>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-bar.css"/>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-attached.css"/>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-other.css"/>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ns-style-theme.css"/>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/nifty-component.css" />


<!-- this page specific scripts -->
<script src="themes/centaurus/js/modernizr.custom.js"></script>
<script src="themes/centaurus/js/snap.svg-min.js"></script> <!-- For Corner Expand and Loading circle effect only -->
<script src="themes/centaurus/js/classie.js"></script>
<script src="themes/centaurus/js/notificationFx.js"></script>


{include file='Buttons_List.tpl'}

{if $smarty.request.ajax neq ''}
    &#&#&#{$ERROR}&#&#&#
{/if}
<script language="JavaScript" type="text/javascript" src="include/js/ListView.js"></script>

<script>
    function callSearch(searchtype) {ldelim}
        for (i = 1; i <= 26; i++) {ldelim}
            var data_td_id = 'alpha_' + eval(i);
            /*getObj(data_td_id).className = 'searchAlph';*/
            {rdelim}
        jQuery("#status").show();
        new Ajax.Request(
            'index.php',
            {ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
                method: 'post',
                postBody:urlstring +'query=true&file=index&module={$MODULE}&action={$MODULE}Ajax&ajax=true&search=true',
                onComplete: function(response) {ldelim}
                    jQuery("#status").hide();
                    result = response.responseText.split('&#&#&#');
                    jQuery("#ListViewContents").html(result[2]);
                    if(result[1] != '')
                        alert(result[1]);
                    //$('basicsearchcolumns').innerHTML = '';
                    {rdelim}
                {rdelim}
        );
        return false;
        {rdelim}

    {literal}

    function changeStatePotential(state, id, idObjectHide, idObject) {
        $("status").style.display = "inline";
        new Ajax.Request(
            'index.php',
            {
                queue: {position: 'end', scope: 'command'},
                method: 'post',
                postBody: 'module=Potentials&action=PotentialsAjax&file=ChangeState&state=' + state + '&record=' + id + '&modeview=viewkanban',
                onComplete: function (response) {
                    $("status").style.display = "none";
                    if (response.responseText == 'status_change') {
                        location.reload();
                    } else {
                        console.log(response);
                        alert("ERROR");
                    }

                }
            }
        );

    }


    function viewSearch() {

        if (!jQuery("#divsearch").is(':visible')) {
            jQuery("#imgsearch").removeClass("fa-search-plus");
            jQuery("#imgsearch").addClass("fa-search-minus");
            jQuery("#divsearch").show();

        } else {

            jQuery("#imgsearch").removeClass("fa-search-minus");
            jQuery("#imgsearch").addClass("fa-search-plus");
            jQuery("#divsearch").hide();

        }
    }

    {/literal}
</script>


<!-- PUBLIC CONTENTS STARTS-->
<div id="ListViewContents">
    <div class="row">
        <div class="col-lg-12">
            <div class="main-box clearfix">
                <header class="main-box-header clearfix">
                    <div id="divsearch" class="filter-block pull-rigth col-lg-4" style="float: left; display: none;">
                        <form name="basicSearch" method="post" action="index.php"
                              onSubmit="return callSearch('Basic');">
                            <input type="hidden" name="searchtype" value="BasicSearch">
                            <input type="hidden" name="module" value="{$MODULE}" id="curmodule">
                            <input name="maxrecords" type="hidden" value="{$MAX_RECORDS}" id='maxrecords'>
                            <input type="hidden" name="parenttab" value="{$CATEGORY}">
                            <input type="hidden" name="action" value="index">
                            <input type="hidden" name="query" value="true">
                            <input type="hidden" name="search_cnt">
                            <div class="input-group-btn">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                    data-toggle="dropdown">{$APP.LBL_IN} <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                {assign var="firstel" value=0}
                                                {foreach item=opt key=count from=$SEARCHLISTHEADER}
                                                    <li>
                                                        <div class="radio" style="padding-left: 15px;">
                                                            <input type="radio" class="search_field" name="search_field"
                                                                   {if $firstel eq 0}checked{/if} id="{$count}"
                                                                   value="{$count}">
                                                            <label for="{$count}">
                                                                {$opt}
                                                            </label>
                                                        </div>
                                                    </li>
                                                    {assign var="firstel" value=$firstel+1}
                                                {/foreach}
                                            </ul>
                                        </div>
                                        <input type="search" name="search_text" id="search_text" class="form-control"
                                               placeholder="Buscar...">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-success" name="submit"
                                                    onClick="callSearch('Basic');">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="filter-block btn-group pull-right col-lg-5" style="float: left">
                        <div class="pull-right" style="float: left">
                            <a class="btn btn-primary" id="viewlist"
                               href="index.php?action=ListView&module={$MODULE}&parenttab={$CATEGORY}&modeview=viewlist"
                               title="Lista">
                                <i class="fa  fa-list-ul"></i>
                            </a>
                            <a class="btn btn-primary" id="viewkanban"
                               href="index.php?action=ListView&module={$MODULE}&parenttab={$CATEGORY}&modeview=viewkanban"
                               title="Kanban">
                                <i class="fa  fa-th"></i>
                            </a>
                        </div>
                        <div class="input-group pull-right col-xs-6">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><i
                                            class="fa fa-filter"></i><span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="index.php?module={$MODULE}&action=CustomView&parenttab={$CATEGORY}">{$APP.LNK_CV_CREATEVIEW}</a>
                                    </li>
                                    {if $CV_EDIT_PERMIT eq 'yes'}
                                        <li>
                                            <a href="index.php?module={$MODULE}&action=CustomView&record={$VIEWID}&parenttab={$CATEGORY}">{$APP.LNK_CV_EDIT}</a>
                                        </li>
                                    {/if}
                                    {if $CV_DELETE_PERMIT eq 'yes'}
                                        <li>
                                            <a href="javascript:confirmdelete('index.php?module=CustomView&action=Delete&dmodule={$MODULE}&record={$VIEWID}&parenttab={$CATEGORY}')">{$APP.LNK_CV_DELETE}</a>
                                        </li>
                                    {/if}
                                    {if $CUSTOMVIEW_PERMISSION.ChangedStatus neq '' && $CUSTOMVIEW_PERMISSION.Label neq ''}
                                        <li><a href="javascript:void(0)" id="customstatus_id"
                                               onClick="ChangeCustomViewStatus({$VIEWID},{$CUSTOMVIEW_PERMISSION.Status},{$CUSTOMVIEW_PERMISSION.ChangedStatus},'{$MODULE}','{$CATEGORY}')">{$CUSTOMVIEW_PERMISSION.Label}</a>
                                        </li>
                                    {/if}
                                </ul>
                            </div>
                            <SELECT name="viewname" id="viewname" class="form-control"
                                    onchange="showDefaultCustomView(this,'{$MODULE}','{$CATEGORY}')">{$CUSTOMVIEW_OPTION}</SELECT>
                        </div>
                        <div class="pull-right" style="float: left">
                            <button type="button" class="btn btn-default" id="viewsearch" onClick="viewSearch();">
                                <i id="imgsearch" class="fa fa-search-plus"></i>
                            </button>
                        </div>
                    </div>
                </header>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="main-box clearfix" style="min-height: 820px;">
                <div class="main-box-body clearfix">
                    <div class="table-responsive">
                        <br/>
                        <table class="table" style="border: 1px solid #d4d4d4;">
                            <tr style="background-color: #f9f9f9; border: 1px solid #d4d4d4;">
                                {assign var="firstel" value=0}
                                {foreach item=opt from=$SALESTAGE}
                                    <td class="col-lg-4" align="center"
                                        {if $firstel eq '0'}style="max-width:14%; border: 1px solid #d4d4d4;"
                                        {elseif $firstel eq '1'}style="max-width:18%; border: 1px solid #d4d4d4;"
                                        {elseif $firstel eq '2'}style="max-width:18%; border: 1px solid #d4d4d4;"
                                        {elseif $firstel eq '3'}style="max-width:14%; border: 1px solid #d4d4d4;"
                                        {elseif $firstel eq '4'}style="max-width:14%; border: 1px solid #d4d4d4;"
                                        {elseif $firstel eq '5'}style="max-width:18%;border: 1px solid #d4d4d4;"{/if}>
                                        <div style="font-size: 13px; with:100%; position:relative; !important">
                                            <font {if $firstel eq '0'}color="#7f8c8d"
                                                  {elseif $firstel eq '1'}color="#8e44ad"
                                                  {elseif $firstel eq '2'}color="#f0ad4e"
                                                  {elseif $firstel eq '3'}color="#27ae60"
                                                  {elseif $firstel eq '4'}color="#337ab7"
                                                  {elseif $firstel eq '5'}color="#BBD934"{/if}>
                                                <small><b>{$opt.sales_stage|@getTranslatedString:$opt.sales_stage}</b>
                                                    &nbsp;
                                                </small>
                                            </font>
                                        </div>
                                        <div style="font-size: 11px;">
                                            <font>
                                                <small>{$opt.amount}</small>
                                            </font>
                                        </div>
                                    </td>
                                    {assign var="firstel" value=$firstel+1}
                                {/foreach}
                            </tr>
                            <tbody>
                            <tr id="elsortable">
                                {assign var="firstela" value=0}
                                {foreach item=opt1 from=$LISTKANBAN}
                                    {if $firstela eq '0'}
                                        {assign var="style" value="font-size: 13px; background-color:#ECEEE6;border-color:#B9BAB3;color:#7f8c8d;"}
                                    {elseif $firstela eq '1'}
                                        {assign var="style" value="font-size: 13px; background-color:#D9A2F0;border-color:#A869C4;color:#8e44ad;"}
                                    {elseif $firstela eq '2'}
                                        {assign var="style" value="font-size: 13px; background-color:#FFFFCC;border-color:#FFCB81;color:#f0ad4e;"}
                                    {elseif $firstela eq '3'}
                                        {assign var="style" value="font-size: 13px; background-color:#ACF0C8;border-color:#27ae60;color:#27ae60;"}
                                    {elseif $firstela eq '4'}
                                        {assign var="style" value="font-size: 13px; background-color:#AAD8FF;border-color:#3D74A4;color:#337ab7;"}
                                    {elseif $firstela eq '5'}
                                        {assign var="style" value="font-size: 13px; background-color:#E1EBB5;border-color:#BBD934;color:#BBD934;"}
                                    {/if}
                                    <td style="font-weight: normal;vertical-align: top;">
                                        {foreach item=entity key=entity_id from=$opt1}
                                            <div class="alert alert-block alert-danger fade in" style="{$style}">
                                                <small>{$entity.potentialname}</small>
                                                <br/>
                                                <b>
                                                    <small>{$entity.amount}</small>
                                                </b>
                                                <div style="float:right;">
                                                    <small>{$entity.relatedlist}</small>
                                                </div>
                                            </div>
                                        {/foreach}
                                    </td>
                                    {assign var="firstela" value=$firstela+1}
                                {/foreach}
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {$SELECT_SCRIPT}
</div>
<style type="text/css">
    .md-modal {
        max-width:870px !important;
        min-width:650px !important;
    }
    .md-effect-7-2 {
        left:50%!important;
        top:0px !important;
    }
</style>
<div class="md-modal md-effect-7-2" id="modal-detail-row">
    <div class="md-content">
        <div class="modal-header">
            <button class="md-close close">&times;</button>
            <h4 class="modal-title">Modal title</h4>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary md-close">Cerrar</button>
        </div>
    </div>
</div>
<script type="text/javascript" src="themes/centaurus/js/modalDetailOverListView.js"></script>
<script>
    {$BUILD_SEARCH}
</script>
