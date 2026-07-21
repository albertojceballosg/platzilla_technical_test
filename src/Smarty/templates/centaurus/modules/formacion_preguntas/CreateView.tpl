{strip}
    {* libraries *}
    <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nanoscroller.css" />
    {* this page specific styles *}
    <link rel="stylesheet" href="themes/{$THEME}/css/libs/datepicker.css" type="text/css" />
    <link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css" />
    <link rel="stylesheet" href="themes/{$THEME}/css/libs/bootstrap-timepicker.css" type="text/css" />
    <link rel="stylesheet" href="themes/{$THEME}/css/libs/select2.css" type="text/css" />
    {block name="css"}{/block}
    {* this page specific scripts *}
    <script src="themes/{$THEME}/js/jquery.maskedinput.min.js"></script>
    <script src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
    <script src="themes/{$THEME}/js/moment.min.js"></script>
    <script src="themes/{$THEME}/js/daterangepicker.js"></script>
    <script src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
    <script src="themes/{$THEME}/js/select2.min.js"></script>
    <script src="themes/{$THEME}/js/hogan.js"></script>
    <script src="themes/{$THEME}/js/typeahead.min.js"></script>
    {* Pedido [ TT11103 ] Validaciones Ajax *}
    <script src="modules/Settings/fieldValidationsAjax.js"></script>
    {if $MODULE neq 'video'}
        <script type="text/javascript" src="{$DIR_PLAT}modules/{$MODULE}/{$MODULE}.js"></script>
    {/if}
    {block name="js"}{/block}
    {assign var="MODULELABEL" value=$MODULE|@getTranslatedString: $MODULE}
    <div class="row">
        <div class="col-xs-12">
            <h1 id="title-view">
                {* vtlib customization: use translated label if available *}
                {if $APP.$SINGLE_MOD}
                    {assign var="SINGLE_MOD_LABEL" value=$APP.SINGLE_MOD}
                {else}
                    {assign var="SINGLE_MOD_LABEL" value=$SINGLE_MOD}
                {/if}
                <a href="index.php?action=ListView&module={$MODULE}&parenttab={$CATEGORY}">{$SINGLE_MOD|@getTranslatedString: $MODULE}</a>
            </h1>
        </div>
    </div>
    {if (!empty ($ACTIVE_APPLICATIONS)) && (count ($ACTIVE_APPLICATIONS) > 1) && ($APPLICATION_VIEWS_ENABLED)}
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box" style="margin-bottom: 0;">
                    <div class="main-box-body clearfix">
                        <form action="index.php" method="get" class="form">
                            <input type="hidden" name="module" value="{$MODULE}" />
                            <input type="hidden" name="action" value="EditView" />
                            {if (isset ($CREATEMODE))}
                                <input type="hidden" name="createmode" value="{$CREATEMODE}" />
                            {/if}
                            {if (isset ($DUPLICATE))}
                                <input type="hidden" name="isDuplicate" value="{$DUPLICATE}" />
                            {/if}
                            {if (isset ($MODE))}
                                <input type="hidden" name="mode" value="{$MODE}" />
                            {/if}
                            {if (isset ($ID))}
                                <input type="hidden" name="record" value="{$ID}" />
                            {/if}
                            {if (isset ($RETURN_ACTION))}
                                <input type="hidden" name="return_action" value="{$RETURN_ACTION}" />
                            {/if}
                            {if (isset ($RETURN_ID))}
                                <input type="hidden" name="return_id" value="{$RETURN_ID}" />
                            {/if}
                            {if (isset ($RETURN_MODULE))}
                                <input type="hidden" name="return_module" value="{$RETURN_MODULE}" />
                            {/if}
                            {if (isset ($RETURN_MODULE))}
                                <input type="hidden" name="return_viewname" value="{$RETURN_VIEWNAME}" />
                            {/if}
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <select id="profileids" name="profileids" class="form-control" onchange="this.form.submit ();" title="Vista por aplicación">
                                        <option value="">Vista por aplicación</option>
                                        {foreach $ACTIVE_APPLICATIONS as $application}
                                            <option value="{$application.app_profile}"{if (!empty ($PROFILE_IDS)) && (in_array ($application.app_profile, $PROFILE_IDS))} selected="selected"{/if}>{$application.app_name}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    {/if}
    {block name="content"}
        {$ERROR_MESSAGE}
        <form action="index.php" method="post" name="EditView" enctype="multipart/form-data" onsubmit="VtigerJS_DialogBox.block ();" role="form">
            {if $MODULE == 'Calendar'}
                <input type="hidden" name="activity_mode" value="{$ACTIVITY_MODE}" />
                <input type="hidden" name="product_id" value="{$PRODUCTID}" />
            {elseif $MODULE eq 'Documents'}
                <input type="hidden" name="max_file_size" value="{$MAX_FILE_SIZE}" />
                <input type="hidden" name="form" />
                <input type="hidden" name="email_id" value="{$EMAILID}" />
                <input type="hidden" name="ticket_id" value="{$TICKETID}" />
                <input type="hidden" name="fileid" value="{$FILEID}" />
                <input type="hidden" name="old_id" value="{$OLD_ID}" />
                <input type="hidden" name="parentid" value="{$PARENTID}" />
            {/if}
            <input type="hidden" name="pagenumber" value="{$smarty.request.start|@vtlib_purify}" />
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="record" value="{$ID}" />
            <input type="hidden" name="mode" value="{$MODE}" />
            <input type="hidden" name="action" />
            <input type="hidden" name="parenttab" value="{$CATEGORY}" />
            <input type="hidden" name="return_module" value="{$RETURN_MODULE}" />
            <input type="hidden" name="return_id" value="{$RETURN_ID}" />
            <input type="hidden" name="return_action" value="{$RETURN_ACTION}" />
            <input type="hidden" name="return_viewname" value="{$RETURN_VIEWNAME}" />
            <input type="hidden" name="createmode" value="{$CREATEMODE}" />
            {if $smarty.request.frontendsid}
                <input type="hidden" name="frontendsid" value="{$smarty.request.frontendsid}" />
            {/if}
            {foreach key=header item=data from=$BLOCKS name=block}
                <div class="row block-container" id="block_{$smarty.foreach.block.iteration}">
                    <div class="col-xs-12">
                        <div class="main-box">
                            <header class="title-section main-box-header clearfix">
                                <h2>{$header}</h2>
                            </header>
                            <div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
                                {include file="DisplayFields.tpl"}
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
            {block name="content-after-blocks"}{/block}
            <div class="clearfix" style="height: 25px; margin-bottom: 16px;"></div>
            <div class="row">
                <div id="fixed-btns-bar" style="display:block">
                    <div class="container">
                        <div class="row">
                            <div class="col-xs-12" style="padding: 25px; height: 75px;">
                                {block name="buttons-bar"}
                                    <input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="btn btn-success" onclick="this.form.action.value='Save'; if (formValidate ()) {ldelim} validationCheckFields ('{$MODULE}',this.form);{rdelim}" type="button" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  " style="margin-right: 5px;">
                                    <input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="btn btn-default" onclick="{if $POPUPCREATE neq 'create'}window.history.back ();{else}window.close ();{/if}" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}  ">
                                {/block}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input name='search_url' id="search_url" type='hidden' value='{$SEARCH}'>


            {if $CAMPOS_TIPO_GRID}
            <script type="text/javascript" src="include/js/gridFormValidate.js"></script>
                {$CAMPOS_TIPO_GRID}
            {/if}
        </form>
    {/block}
    {if ($MODULE == 'Documents')}
        <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
        <script type="text/javascript">
            var textAreaName = 'notecontent';
            CKEDITOR.replace (textAreaName, {ldelim}
                extraPlugins: 'uicolor',
                uiColor:      '#dfdff1',
                height:       '200', width: '800'
                {rdelim});
            var oCKeditor = CKEDITOR.instances[ textAreaName ];
        </script>
    {/if}
    <script type="text/javascript">
        var fieldname = [{$VALIDATION_DATA_FIELDNAME}];
        var fieldlabel = [{$VALIDATION_DATA_FIELDLABEL}];
        var fielddatatype = [{$VALIDATION_DATA_FIELDDATATYPE}];
        var ProductImages = [];
        var count = 0;

        function delRowEmt (imagename) {ldelim}
            ProductImages[ count++ ] = imagename;
            {rdelim}

        function displaydeleted () {ldelim}
            var imagelists = '';
            for (var x = 0; x < ProductImages.length; x++) {ldelim}
                imagelists += ProductImages[ x ] + '###';
                {rdelim}
            if (imagelists != '') {ldelim}
                document.EditView.imagelist.value = imagelists;
                {rdelim}
            {rdelim}

        function openPopup () {ldelim}
            window.open ("index.php?module=Users&action=UsersAjax&file=RolePopup&parenttab=Settings", "roles_popup_window", "height=425,width=640,toolbar=no,menubar=no,dependent=yes,resizable =no");
            {rdelim}
    </script>
    {* vtlib customization: Help information assocaited with the fields *}
    {if $FIELDHELPINFO}
        <script type='text/javascript'>
                    {literal}var fieldhelpinfo = {}; {/literal}
            {foreach item=FIELDHELPVAL key=FIELDHELPKEY from=$FIELDHELPINFO}
            fieldhelpinfo[ "{$FIELDHELPKEY}" ] = "{$FIELDHELPVAL}";
            {/foreach}
        </script>
    {/if}
    {block name="scripts"}{/block}
{/strip}

{block name="scripts" append}

    {if $PICKIST_DEPENDENCY_DATASOURCE neq ''}
        <script type="text/javascript" src="include/js/FieldDependencies.js"></script>
        <script type="text/javascript">
            jQuery(document).ready(function() {ldelim} (new FieldDependencies({$PICKIST_DEPENDENCY_DATASOURCE})).init() {rdelim});
        </script>
    {/if}

{/block}