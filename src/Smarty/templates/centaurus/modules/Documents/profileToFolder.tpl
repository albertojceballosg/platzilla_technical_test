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
        	getObj(data_td_id).className = 'searchAlph';
    	{rdelim}
    	gPopupAlphaSearchUrl = '';
	search_fld_val= $('bas_searchfield').options[$('bas_searchfield').selectedIndex].value;
	search_txt_val= encodeURIComponent(document.basicSearch.search_text.value);
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
	$("status").style.display="inline";
	new Ajax.Request(
		'index.php',
		{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody:urlstring +'query=true&file=index&module={$MODULE}&action={$MODULE}Ajax&ajax=true&search=true',
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
<div class="row">
	<div class="col-lg-12">
		<div class="col-lg-8 pull-left">
			<h1>Carpetas 
				<small id="path-carpetas"><!--Secondary headline--><!--!--Secondary--></small>
			</h1>
		</div>

		<div class="col-lg-4 pull-right text-right">
			{if $FOLDERACTUAL neq 0}
			<a href="index.php?module=Documents&action=profileToFolder&folderid={$FOLDERPADRE}" class="btn btn-primary btn-sm">{$MOD.LBL_IR_ARRIBA}</a>
			{/if}
		</div>
</div>










<div class="row" id="folderContent">
	
	
<div class="row">
{foreach item=folder from=$FOLDERS}
	<!-- folder division starts -->
	{assign var=foldercount value=$FOLDERS|@count}
	<!--div class="col-lg-3 col-sm-6 col-xs-12" onclick="openFolder({$folder.folderid},'')" data-folderid="{$folder.folderid}"-->
		<div class="col-lg-3 col-sm-6 col-xs-12" onclick="" data-folderid="{$folder.folderid}">

		<div class="main-box infographic-box">
			<a href="index.php?module=Documents&action=profileToFolder&folderid={$folder.folderid}">
			<i class="fa fa-folder emerald-bg"></i>
			<span class="headline">{$folder.foldername}</span>
			<span class="value">
				<span class="timer" data-from="0" data-to="{$folder.childfolders}" data-speed="1000" data-refresh-interval="150">{$folder.childfolders}</span> <i class="fa fa-folder"></i>
			</span>
			</a>

			<br>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
						<td></td>
						<td>Perfil</td>
					</thead>
					<tbody>
						{foreach item=profile from=$PROFILES}
						<tr>
							<td>	
								<!-- Editar -->
								<a href="index.php?module=Documents&action=FolderPermissions&folderid={$folder.folderid}&profileid={$profile.profileid}" class="table-link" alt="Editar" title="Editar permiso">
									<span>
									<i class="fa fa-pencil fa-inverse emerald-bg" style="font-size:1em;width:30px;height:30px;line-height:30px;border-radius:5px;"></i>
									</span>
								</a>
                            </td>
							<td>{$profile.profilename}</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
		</div>
			

			</div>
			
	</div>
{/foreach}
</div>

	
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
	url_alertab= "index.php?module=Documents&action=DocumentsAjax&file=profileToFolder&folderid="+id;
	alert(url_alertab);
	//var id=jQuery(this).attr('data-folderid');
	jQuery('#status').show();
	jQuery.ajax({
		type: "POST",
		url: "index.php?module=Documents&action=DocumentsAjax&file=profileToFolder&folderid="+id,
		data: { folderid: id , mode:'ajax',datatype:"json",foldermodule:mod},
		dataType: "json"
	}).done(function( response ) {
		console.log(response);
		//jQuery('#createfolder').hide();
		jQuery('#levelupfolder').show();
		jQuery('#reloadfolder').show();
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
		
	});
}
jQuery('#levelupfolder').click(function(e){
	var id=jQuery(this).attr('data-folderid');
	selectedFolder=id;
	jQuery('#status').show();
	jQuery.ajax({
		type: "POST",
		url: "index.php?module=Documents&action=DocumentsAjax&file=ListView",
		data: { do:"LIST_FOLDERS" , mode:'ajax',datatype:"json"},
		dataType: "json"
	}).done(function( response ) {
		console.log(response);
		//jQuery('#createfolder').show();
		jQuery('#levelupfolder').hide();
		jQuery('#reloadfolder').hide();
		jQuery('#uploaddoc').hide();
		jQuery('#folderContent').html(response.html);
		jQuery('#path-carpetas').html("");
		jQuery('#status').hide();
		
	});
});


{/literal}
</script>