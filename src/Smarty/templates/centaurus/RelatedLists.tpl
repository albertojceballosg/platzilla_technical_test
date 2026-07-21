{strip}
    <link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css"/>
    <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/platzilla-detailview.css"/>
    {include file='Buttons_List.tpl'}
    {if $OP_MODE eq 'edit_view'}
        {assign var="action" value="EditView"}
    {else}
        {assign var="action" value="DetailView"}
    {/if}
    <div class="container-fluid"
            {if !$IS_MODAL}
         style="background-color: #ffffff; margin: 0px -13px!important;border-top: 1px solid #D8D8D8 !important;">
        {else}
        style="background-color: #ffffff; margin: 4px -13px!important;border-top: 1px solid #D8D8D8 !important;">
        {/if}
        <div class="tabs-wrapper row">
            <div id="history-body" class="col-md-12" style="margin-top: 4px">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="main-box clearfix"
                             style="border-top: 1px solid #FFFFFF !important; height: 100% !important;">
                            <header class="main-box-header clearfix">&nbsp;</header>
                            <div class="main-box-body clearfix">
                                <div class="panel-group accordion" id="RLContents">
                                    {include file='RelatedListContents.tpl'}
                                </div>
                            </div>
                            {if !$IS_MODAL}
                                <div style="height: 750px"></div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>

            {if $EDIT_PERMISSION eq 'yes'}
                <div id="edit-record-relatedList" style="display: none">
                    <form action="index.php" method="post" name="DetailView" id="form">
                        {include file='DetailViewHidden.tpl'}
                        {foreach key=header item=detail from=$BLOCKS}
                            {assign var="keyArray" value=key($detail[0])}
                            {assign var="uitype" value=$detail[0][$keyArray]['ui']}
                            {assign var="idField" value=$detail[0][$keyArray]['fldid']}
                            {assign var="fieldName" value=$detail[0][$keyArray]['fldname']}
                            {assign var="isEmpty" value=true}
                            {assign var=detailD value=$detail}
                            {if $header eq $MOD.LBL_COMMENTS || $header eq $MOD.LBL_COMMENT_INFORMATION}
                                &nbsp;
                            {else}
                                {assign var=detailD value=$detail}
                                {foreach item=detail from=$detailD}
                                    {foreach key=label item=data from=$detail}
                                        {assign var=keycntimage value=$data.cntimage}
                                        {assign var=keyadmin value=$data.isadmin}

                                        {if $label ne ''}
                                            {if $keycntimage ne ''}
                                                <input type="hidden" id="hdtxt_IsAdmin"
                                                       value={$keyadmin} />{$keycntimage}
                                            {elseif $keyid eq '14'}
                                                <input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}/>
                                            {/if}
                                        {/if}
                                    {/foreach}
                                {/foreach}
                            {/if}
                        {/foreach}
                    </form>
                </div>
            {/if}
        </div>
    </div>
    {include file='CreateTaskWizard.tpl'}
    <script type="text/javascript">
        function OpenWindow(url) {
            openPopUp('xAttachFile', this, url, 'attachfileWin', 380, 375, 'menubar=no,toolbar=no,location=no,status=no,resizable=no');
        }
    </script>
    <script type="text/javascript" src="include/js/ListView.js"></script>
    <script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
    <script type="text/html" id="instances-data-sharing-share-modal-template">
        {include file='modules/instancesdatasharing/ShareModal.tpl'}
{/strip}