{*<!--

/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

-->*}
<!-- this page specific styles -->
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/dropzone.css">
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/magnific-popup.css">

{*<!-- module header -->*}
<script language="JavaScript" type="text/javascript" src="include/js/ListView.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/search.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/Merge.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/dtlviewajax.js"></script>
<script language="javascript" type="text/javascript">
var typeofdata = new Array();
typeofdata['E'] = ['e','n','s','ew','c','k'];
typeofdata['V'] = ['e','n','s','ew','c','k'];
typeofdata['N'] = ['e','n','l','g','m','h'];
typeofdata['NN'] = ['e','n','l','g','m','h'];
typeofdata['T'] = ['e','n','l','g','m','h'];
typeofdata['I'] = ['e','n','l','g','m','h'];
typeofdata['C'] = ['e','n'];
typeofdata['DT'] = ['e','n','l','g','m','h'];
typeofdata['D'] = ['e','n','l','g','m','h'];
var fLabels = new Array();
fLabels['e'] = "{$APP.is}";
fLabels['n'] = "{$APP.is_not}";
fLabels['s'] = "{$APP.begins_with}";
fLabels['ew'] = "{$APP.ends_with}";
fLabels['c'] = "{$APP.contains}";
fLabels['k'] = "{$APP.does_not_contains}";
fLabels['l'] = "{$APP.less_than}";
fLabels['g'] = "{$APP.greater_than}";
fLabels['m'] = "{$APP.less_or_equal}";
fLabels['h'] = "{$APP.greater_or_equal}";
var noneLabel;
{literal}
function trimfValues(value)
{
    var string_array;
    string_array = value.split(":");
    return string_array[4];
}



function updatefOptions(sel, opSelName) {
    var selObj = document.getElementById(opSelName);
    var fieldtype = null ;

    var currOption = selObj.options[selObj.selectedIndex];
    var currField = sel.options[sel.selectedIndex];

    var fld = currField.value.split(":");
    var tod = fld[4];
    if(currField.value != null && currField.value.length != 0)
    {
	fieldtype = trimfValues(currField.value);
	fieldtype = fieldtype.replace(/\\'/g,'');
	ops = typeofdata[fieldtype];
	var off = 0;
	if(ops != null)
	{

		var nMaxVal = selObj.length;
		for(nLoop = 0; nLoop < nMaxVal; nLoop++)
		{
			selObj.remove(0);
		}
		for (var i = 0; i < ops.length; i++)
		{
			var label = fLabels[ops[i]];
			if (label == null) continue;
			var option = new Option (fLabels[ops[i]], ops[i]);
			selObj.options[i] = option;
			if (currOption != null && currOption.value == option.value)
			{
				option.selected = true;
			}
		}
	}
    }else
    {
	var nMaxVal = selObj.length;
	for(nLoop = 0; nLoop < nMaxVal; nLoop++)
	{
		selObj.remove(0);
	}
	selObj.options[0] = new Option ('None', '');
	if (currField.value == '') {
		selObj.options[0].selected = true;
	}
    }

}
{/literal}
</script>
<script language="JavaScript" type="text/javascript" src="modules/{$MODULE}/{$MODULE}.js"></script>
<script language="javascript">
function callSearch(searchtype)
{ldelim}
	for(i=1;i<=26;i++)
    	{ldelim}
        	var data_td_id = 'alpha_'+ eval(i);
        	/*getObj(data_td_id).className = 'searchAlph';*/
    	{rdelim}
    	gPopupAlphaSearchUrl = '';
		/*search_fld_val= $('bas_searchfield').options[$('bas_searchfield').selectedIndex].value;
		search_txt_val= encodeURIComponent(document.basicSearch.search_text.value);*/
        search_fld_val= jQuery('input[name=search_field]:checked').val();
		search_txt_val= encodeURIComponent(jQuery('#search_text').val());
        var urlstring = '';
        if(searchtype == 'Basic')
        {ldelim}
        		var p_tab = document.getElementsByName("parenttab");
                urlstring = 'search_field='+search_fld_val+'&searchtype=BasicSearch&search_text='+search_txt_val+'&';
                urlstring = urlstring + 'parenttab='+p_tab[0].value+ '&';
        {rdelim}
        else if(searchtype == 'Advanced')
        {ldelim}
        		checkAdvancedFilter();
				var advft_criteria = $('advft_criteria').value;
				var advft_criteria_groups = $('advft_criteria_groups').value;
				urlstring += '&advft_criteria='+advft_criteria+'&advft_criteria_groups='+advft_criteria_groups+'&';
				urlstring += 'searchtype=advance&'
        {rdelim}
	jQuery("#status").show();

	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody:urlstring +'query=true&file=index&module={$MODULE}&action={$MODULE}Ajax&ajax=true&search=true',
			onComplete: function(response) {ldelim}
								jQuery("#status").hide();
								console.log("holaa " + response.responseText);
                                result = response.responseText.split('&#&#&#');
                                jQuery("#ListViewContents").html(result[2]);
                                if(result[1] != '')
									alert(result[1]);
								//$('basicsearchcolumns').innerHTML = '';
			{rdelim}
	       {rdelim}
        );
	return false
{rdelim}
function alphabetic(module,url,dataid)
{ldelim}
        for(i=1;i<=26;i++)
        {ldelim}
                var data_td_id = 'alpha_'+ eval(i);
                getObj(data_td_id).className = 'searchAlph';

        {rdelim}
        getObj(dataid).className = 'searchAlphselected';
	$("status").style.display="inline";
	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody: 'module='+module+'&action='+module+'Ajax&file=index&ajax=true&search=true&'+url,
			onComplete: function(response) {ldelim}
				$("status").style.display="none";
				result = response.responseText.split('&#&#&#');
				$("ListViewContents").innerHTML= result[2];
				if(result[1] != '')
			                alert(result[1]);
				$('basicsearchcolumns').innerHTML = '';
			{rdelim}
		{rdelim}
	);
{rdelim}

</script>
<style>
{literal}
.openfolder{cursor:pointer;}
.gallery-photos .actions{
	right: 0px;
    padding-right: 5px;
    position: absolute;
    top: 0px;
}
.gallery-photos .actions > a {
    margin: 0 2px;
    color: #707070;
}
.gallery-photos .actions > a:hover {
    color: #3498db;
}

.gallery-photos .actions > a.badge {
    color: #fff;
}
.gallery-photos .actions > a.badge:hover {
    background-color: #fff;
}
.gallery-photos .actions > a:hover {
    text-decoration: none;
    color: #3498db;
}
{/literal}
</style>
{if (!empty ($NOTIFICATIONS))}
	{foreach $NOTIFICATIONS as $index => $notification}
<div class="alert alert-dismissable notification{if ($index > 1)} hidden{/if}" data-id="{$notification->getId ()}" style="background-color: #ffffff;">
	<button type="button" class="close notification-close" data-dismiss="alert" aria-label="close">&times;</button>
	<div>{$notification->getContents ()|unescape:"html"}</div>
</div>
	{/foreach}
<script type="text/javascript">
	(function (jQuery) {
		jQuery ('.notification').on ('closed.bs.alert', function () {
			var notificationId = jQuery (this).attr ('data-id'),
				arguments      = [
					'module=notifications',
					'action=Disable',
					'record=' + encodeURIComponent (notificationId),
					'Ajax=true'
				];
			jQuery.ajax ('index.php', {
				data:     arguments.join ('&'),
				dataType: 'json',
				method:   'post'
			}).done (function () {
				jQuery ('.notification.hidden:first').removeClass ('hidden');
			});
		});
	} (jQuery));
</script>
{/if}
<div class="row">
	<div class="col-lg-12">
		<div class="col-lg-6 pull-left">
			<h1>Carpetas
				<small id="path-carpetas"><!--Secondary headline--><!--!--Secondary--></small>
			</h1>
		</div>


		<div class="col-lg-6 pull-right text-right">


			<button class="md-trigger btn btn-primary mrg-b-lg pull-right" id="createfolder" data-modal="crear-carpeta" style="margin-left: 10px;">
				<i class="fa fa-plus-circle fa-lg" title="Crear Carpeta"></i> Crear Carpeta
			</button>
			<button class="btn btn-primary pull-right" id="levelupfolder" style="margin-left: 10px;display:none;">
				<i class="fa fa-level-up fa-lg" title="Volver"></i> Volver
			</button>
			<button class="btn btn-primary pull-right" id="reloadfolder" onClick="openFolder(selectedFolder,selectedFoldermod)" style="margin-left: 10px;display:none;">
				<i class="fa fa-retweet" title="Volver"></i> Recargar
			</button>
			<a href="index.php?module=Documents&action=EditView&return_action=DetailView&" id="uploaddoc" class="btn btn-primary pull-right" style="margin-left: 10px;display:none;">
				<i class="fa fa-upload fa-lg" title="Cargar Documento"></i> Cargar Documento
			</a>
		</div>

		<div class="col-lg-12 pull-right text-right">
			{if $IS_ADMIN eq 1}
			<a href="index.php?module=Documents&action=profileToFolder" id="" class="btn btn-primary pull-right" style="margin-left: 10px;">
				<i class="fa fa-cog fa-lg" title="Cargar Documento"></i> Permisología
			</a>
			{/if}
			<button class="btn btn-danger pull-right" id="deletefolder" onClick="DeleteFolderCheckNew()" style="margin-left: 10px;display:none;">
				<i class="fa fa-trash-o" title="eliminarfolder"></i> Eliminar Carpeta
			</button>
			<br>
		</div>
	</div>
</div>


<div class="row">
	<div class="col-lg-12">
		<div class="main-box no-header clearfix">

			<div class="main-box-body clearfix">
				<form role="search" id="UnifiedSearch" name="UnifiedSearch" method="get" action="index.php" style="margin:0px" onsubmit="VtigerJS_DialogBox.block();">
					<div class="form-group col-lg-4">
						<input name="action" value="UnifiedSearch" style="margin:0px" type="hidden">
						<input name="module" value="Home" style="margin:0px" type="hidden">
						<input name="return_module" value="Documents" style="margin:0px" type="hidden">
						<input name="parenttab" value="Settings" style="margin:0px" type="hidden">
						<input name="search_onlyin" value="Documents" style="margin:0px" type="hidden">
						<div class="input-group">
							<input class="form-control" name="query_string"  placeholder="Buscar" type="text">
							<span class="input-group-addon" onclick="jQuery('#UnifiedSearch').submit();"><i class="fa fa-search"></i></span>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>







<div class="md-modal md-effect-1" id="crear-carpeta">
	<div class="md-content">
		<div class="modal-header">
			<button class="md-close close">&times;</button>
			<h4 class="modal-title">Nueva Carpeta</h4>
		</div>
		<div class="modal-body">
			<form role="form">
				<input id="folder_id" name="folder_id" type="hidden" value=''>
				<input id="fldrsave_mode" name="folderId" type="hidden" value='save'>

				<div class="form-group">
					<label for="folder_name">{$MOD.LBL_FOLDER_NAME}</label>
					<input type="text" class="form-control" id="folder_name" name="folderName" placeholder="{$MOD.LBL_FOLDER_NAME}">
				</div>
				<div class="form-group">
					<label for="folder_desc">{$MOD.LBL_FOLDER_DESC}</label>
					<textarea class="form-control" id="folder_desc" name="folderDesc" id="folder_desc" rows="3"></textarea>
				</div>

			</form>
		</div>
		<div class="modal-footer">
			<input name="save" value="{$APP.LBL_SAVE_BUTTON_LABEL}" class="btn btn-primary" onClick="AddFolder();" type="button">
		</div>
	</div>
</div>

<div class="row" id="folderContent">

	{include file="modules/Documents/FoldersListView.tpl"}

</div>
<div class="md-overlay"></div><!-- the overlay element -->
<script language="JavaScript" type="text/javascript" src="modules/Documents/Documents.js"></script>
<!-- this page specific scripts -->
<script src="themes/centaurus/js/modernizr.custom.js"></script>
<script src="themes/centaurus/js/classie.js"></script>
<script src="themes/centaurus/js/modalEffects.js"></script>
<script src="themes/centaurus/js/jquery.countTo.js"></script>
<!-- this page specific scripts -->
<script src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
<script src="themes/centaurus/js/dropzone.js"></script>
<script src="themes/centaurus/js/jquery.magnific-popup.min.js"></script>

<script>
{literal}
jQuery(document).ready(function() {
	jQuery('.infographic-box .value .timer').countTo({});
});
var selectedFolder;
var selectedFoldermod;
function openFolder(id,mod){
	selectedFolder=id;
	selectedFoldermod=mod;
	//url_alertab= "index.php?module=Documents&action=DocumentsAjax&file=ListView&folderid="+id+"&mode='ajax'&foldermodule="+mod;
	//alert(url_alertab);
	//var id=jQuery(this).attr('data-folderid');
	jQuery('#status').show();
	jQuery.ajax({
		type: "POST",
		url: "index.php?module=Documents&action=DocumentsAjax&file=ListView",
		data: { folderid: id , mode:'ajax',datatype:"json",foldermodule:mod},
		dataType: "json"
	}).done(function( response ) {
		console.log(response);
		//jQuery('#createfolder').hide();
		jQuery('#levelupfolder').show();
		jQuery('#reloadfolder').show();

		jQuery('#deletefolder').show();

		jQuery('#folderContent').html(response.html);
		jQuery('#folder_id').val(id);
		if(id!=0){
			jQuery('#uploaddoc').show();
			jQuery('#path-carpetas').html("> "+response.foldername);
		}else{
			jQuery('#path-carpetas').html("> "+mod);
		}
		jQuery('#status').hide();
		jQuery('#gallery-photos-lightbox').magnificPopup({
			type: 'image',
			delegate: 'aimg',
			gallery: {
				enabled: true
			}
		});

	})
	 .fail(function() {
    //alert( "error" );
  });
}
jQuery('#levelupfolder').click(function(e){
	//var id=jQuery(this).attr('data-folderid');
	var id=jQuery('#folderPadre').val();
	selectedFolder=id;
	jQuery('#status').show();
	jQuery.ajax({
		type: "POST",
		url: "index.php?module=Documents&action=DocumentsAjax&file=ListView&folderid="+id,
//		data: { do:"LIST_FOLDERS" , mode:'ajax',datatype:"json"},
		data: { do:"" , mode:'ajax',datatype:"json"},
		dataType: "json"
	}).done(function( response ) {
		//jQuery('#createfolder').show();
		if(id == 0){
			jQuery('#levelupfolder').hide();
			jQuery('#reloadfolder').hide();
			jQuery('#uploaddoc').hide();

			jQuery('#deletefolder').hide();
		}
		jQuery('#folderContent').html(response.html);
		jQuery('#path-carpetas').html("");
		jQuery('#status').hide();

	});
});


{/literal}
</script>