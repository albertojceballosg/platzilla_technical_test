{extends file="base/BaseList.tpl"}

{block name="js"}
<script language="JavaScript" type="text/javascript" src="include/js/general.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/ListView.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/search.js"></script>
{/block}

{block name="first-content"}
{include file='Buttons_List.tpl'}
<div  id="ListViewContents">
{/block}

{block name="header-buttons-row-one" prepend}			
<div class="pull-left">
	<div class="pull-right header-button btn-additional">
		<a href="index.php?module=Calendar&amp;action=index" class="btn btn-default">
			<i class="fa fa-default"></i>Regresar a {$APP.Calendar}
		</a>
	</div>
</div>
{/block}

{block name="last-content"}
</div>
{/block}

{block name="scripts"}

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

<script language="javascript">
function checkgroup()
{ldelim}

if(document.change_ownerform_name.user_lead_owner[1].checked)
{ldelim}
		document.change_ownerform_name.lead_group_owner.style.display = "block";
		document.change_ownerform_name.lead_owner.style.display = "none";
{rdelim}
else
	{ldelim}
		document.change_ownerform_name.lead_owner.style.display = "block";
		document.change_ownerform_name.lead_group_owner.style.display = "none";
	{rdelim}    
{rdelim}

{* [ TT11387 ] Correcciones del Calendario - Jesus A. - Se Actualiza la función*}
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
		postBody: 'module='+module+'&action='+module+'Ajax&file=ListView&ajax=true&search=true&'+url,
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

{* [ TT11387 ] Correcciones del Calendario - Jesus A. - Función para el boton de busqueda*}
<script>

function viewSearch(){ldelim}
	if(!jQuery("#divsearch").is(':visible')){ldelim}
		jQuery("#imgsearch").removeClass("fa-search-plus");
		jQuery("#imgsearch").addClass("fa-search-minus");
		jQuery("#divsearch").show();

		{rdelim}else{ldelim}

		jQuery("#imgsearch").removeClass("fa-search-minus");
		jQuery("#imgsearch").addClass("fa-search-plus");
		jQuery("#divsearch").hide();

		{rdelim}

		var module = '{$MODULE}';
		var parent = '{$CATEGORY}';

		//jQuery('#viewname option:contains("Todos")').prop('selected', true);
		//jQuery('#viewname').trigger('change');
		//showDefaultCustomView1(jQuery('#viewname').val(),module,parent);
	{rdelim}
</script>

<script>
{literal}

function ajaxChangeStatus(statusname)
{
	$("status").style.display="inline";
	var viewid = document.massdelete.viewname.value;
	var excludedRecords=document.getElementById("excludedRecords").value;
	var idstring = document.getElementById('allselectedboxes').value;
	if(statusname == 'status')
	{
		fninvsh('changestatus');
		var url='&leadval='+document.getElementById('lead_status').options[document.getElementById('lead_status').options.selectedIndex].value;
		var urlstring ="module=Users&action=updateLeadDBStatus&return_module=Leads"+url+"&viewname="+viewid+"&idlist="+idstring+"&excludedRecords="+excludedRecords;
		}
		else if(statusname == 'owner')
		{

		if($("user_checkbox").checked)
		{
			fninvsh('changeowner');
			var url='&owner_id='+document.getElementById('lead_owner').options[document.getElementById('lead_owner').options.selectedIndex].value+'&owner_type=User';
			{/literal}
			var urlstring ="module=Users&action=updateLeadDBStatus&return_module={$MODULE}"+url+"&viewname="+viewid+"&idlist="+idstring+"&excludedRecords="+excludedRecords;
			{literal}
		}
			else
			{
				fninvsh('changeowner');
				var url='&owner_id='+document.getElementById('lead_group_owner').options[document.getElementById('lead_group_owner').options.selectedIndex].value+'&owner_type=Group';
				{/literal}
				var urlstring ="module=Users&action=updateLeadDBStatus&return_module={$MODULE}"+url+"&viewname="+viewid+"&idlist="+idstring+"&excludedRecords="+excludedRecords;
				{literal}
			}

		}
		new Ajax.Request(
			'index.php',
			{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: urlstring,
			onComplete: function(response) {
				$("status").style.display="none";
				result = response.responseText.split('&#&#&#');
				$("ListViewContents").innerHTML= result[2];
				if(result[1] != '')
					alert(result[1]);
				$('basicsearchcolumns').innerHTML = '';
			}
		}
		);

	}
</script>
{/literal}

	{/block}







































