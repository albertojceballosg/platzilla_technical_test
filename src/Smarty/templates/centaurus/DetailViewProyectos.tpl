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
<script type="text/javascript" src="include/js/reflection.js"></script>
<script src="include/scriptaculous/scriptaculous.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript" src="include/js/dtlviewajax.js"></script>
<!--span id="crmspanid" style="display:none;position:absolute;"  onmouseover="show('crmspanid');">
	<a class="link"  align="right" href="javascript:;">{$APP.LBL_EDIT_BUTTON}</a>
</span-->

<div id="convertleaddiv" style="display:block;position:absolute;left:225px;top:150px;"></div>
<script>
var gVTModule = '{$smarty.request.module|@vtlib_purify}';
{literal}
function callConvertLeadDiv(id)
{
        new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'module=Leads&action=LeadsAjax&file=ConvertLead&record='+id,
                        onComplete: function(response) {
                                $("convertleaddiv").innerHTML=response.responseText;
				eval($("conv_leadcal").innerHTML);
                        }
                }
        );
}
function showHideStatus(sId,anchorImgId,sImagePath)
{
	oObj = eval(document.getElementById(sId));
	if(oObj.style.display == 'block')
	{
		oObj.style.display = 'none';
		if(anchorImgId !=null){
			eval(document.getElementById(anchorImgId)).src =  'themes/images/inactivate.gif';
			eval(document.getElementById(anchorImgId)).alt = 'Display';
			eval(document.getElementById(anchorImgId)).title = 'Display';
		}
	}
	else
	{
		oObj.style.display = 'block';
		if(anchorImgId !=null){
			eval(document.getElementById(anchorImgId)).src = 'themes/images/activate.gif';
			eval(document.getElementById(anchorImgId)).alt = 'Hide';
			eval(document.getElementById(anchorImgId)).title = 'Hide';
		}
	}
}
<!-- End Of Code modified by SAKTI on 10th Apr, 2008 -->

<!-- Start of code added by SAKTI on 16th Jun, 2008 -->
function setCoOrdinate(elemId){
	oBtnObj = document.getElementById(elemId);
	var tagName = document.getElementById('lstRecordLayout');
	leftpos  = 0;
	toppos = 0;
	aTag = oBtnObj;
	do{
	  leftpos  += aTag.offsetLeft;
	  toppos += aTag.offsetTop;
	} while(aTag = aTag.offsetParent);

	tagName.style.top= toppos + 20 + 'px';
	tagName.style.left= leftpos - 276 + 'px';
}

function getListOfRecords(obj, sModule, iId,sParentTab)
{
		new Ajax.Request(
		'index.php',
		{queue: {position: 'end', scope: 'command'},
			method: 'post',
			postBody: 'module=Users&action=getListOfRecords&ajax=true&CurModule='+sModule+'&CurRecordId='+iId+'&CurParentTab='+sParentTab,
			onComplete: function(response) {
				sResponse = response.responseText;
				$("lstRecordLayout").innerHTML = sResponse;
				Lay = 'lstRecordLayout';
				var tagName = document.getElementById(Lay);
				var leftSide = findPosX(obj);
				var topSide = findPosY(obj);
				var maxW = tagName.style.width;
				var widthM = maxW.substring(0,maxW.length-2);
				var getVal = parseInt(leftSide) + parseInt(widthM);
				if(getVal  > document.body.clientWidth ){
					leftSide = parseInt(leftSide) - parseInt(widthM);
					tagName.style.left = leftSide + 230 + 'px';
					tagName.style.top = topSide + 20 + 'px';
				}else{
					tagName.style.left = leftSide + 230 + 'px';
				}
				setCoOrdinate(obj.id);

				tagName.style.display = 'block';
				tagName.style.visibility = "visible";
			}
		}
	);
}
{/literal}
function tagvalidate()
{ldelim}
	if(trim(document.getElementById('txtbox_tagfields').value) != '')
		SaveTag('txtbox_tagfields','{$ID}','{$MODULE}');
	else
	{ldelim}
		alert("{$APP.PLEASE_ENTER_TAG}");
		return false;
	{rdelim}
{rdelim}

//Added to send a file, in Documents module, as an attachment in an email
function sendfile_email()
{ldelim}
	filename = $('dldfilename').value;
	document.DetailView.submit();
	OpenCompose(filename,'Documents');
{rdelim}

</script>
<style>
{literal}
.table tbody > tr > td {
  font-size: 1.125em;
  font-weight: 300;
}
{/literal}
</style>
{include file="Buttons_List.tpl"}

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">

				<div class="icon-box pull-left">
					<h2>{$APP.LBL_INFORMATION}</h2>
					{*
							<a class="dropdown-toggle" data-toggle="dropdown" href="#">
								{$APP.LBL_MORE} {$APP.LBL_INFORMATION} <span class="caret"></span>
							</a>
							<ul class="dropdown-menu" role="menu">
								{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
									<li ><a role="menuitem" tabindex="-1" href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}&platdb={$PLATDB}">{$_RELATED_MODULE|@getTranslatedString:$MODULE}</a></li>
								{/foreach}
							</ul>
						</li>
					</ul>
					*}
				</div>
				<div class="icon-box pull-right">
					{if $IS_REL_LIST}
					<div class="btn-group">
						<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
							<i class="fa fa-plus"></i> Info <span class="caret"></span>
						</button>
						<ul class="dropdown-menu" role="menu">
							{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
								<li><a href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}&platdb={$PLATDB}">{$_RELATED_MODULE|@getTranslatedString:$MODULE}</a></li>
							{/foreach}
						</ul>
					</div>
					{/if}
					{if $COL_ACCIONES neq 'false'}
						{include file='DetailViewActions.tpl'}
					{/if}

					{*
					<a href="index.php?module={$MODULE}&viewtype={$VIEWTYPE}&action=DetailView&record={$nextrecord}&parenttab={$CATEGORY}&start={$nextrecordstart}" class="btn {if $nextrecord eq ''}disabled{/if}">
						<i class="fa fa-chevron-right"></i>
					</a>
					<a href="index.php?module={$MODULE}&viewtype={$VIEWTYPE}&action=DetailView&record={$privrecord}&parenttab={$CATEGORY}&start={$privrecordstart}" class="btn {if $privrecord eq ''}disabled{/if}">
						<i class="fa fa-chevron-left"></i>
					</a>
					*}

					{if $EDIT_DUPLICATE eq 'permitted'}
						<a href="javascript:void(0)" onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.return_id.value='{$ID}';DetailView.module.value='{$MODULE}';submitFormForAction('DetailView','EditView');" class="btn btn-success">
							<span class="fa fa-pencil"></span> {$APP.LBL_EDIT_BUTTON_LABEL}
						</a>
					{/if}
					{if $PLATDB eq ''}
						{if $EDIT_DUPLICATE eq 'permitted' && $MODULE neq 'Documents'}
							<a href="javascript:void(0)" onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='DetailView'; DetailView.isDuplicate.value='true';DetailView.module.value='{$MODULE}'; submitFormForAction('DetailView','EditView');" class="btn btn-success">
								<span class="fa fa-files-o"></span> {$APP.LBL_DUPLICATE_BUTTON_LABEL}
							</a>
						{/if}
					{/if}
					{if $DELETE eq 'permitted'}
						<a href="javascript:void(0)" onclick="DetailView.return_module.value='{$MODULE}'; DetailView.return_action.value='index'; {if $MODULE eq 'Accounts'} var confirmMsg = '{$APP.NTC_ACCOUNT_DELETE_CONFIRMATION}' {else} var confirmMsg = '{$APP.NTC_DELETE_CONFIRMATION}' {/if}; submitFormForActionWithConfirmation('DetailView', 'Delete', confirmMsg);" class="btn btn-danger">
							<span class="fa fa-trash-o"></span> {$APP.LBL_DELETE_BUTTON_LABEL}
						</a>
					{/if}

				</div>

			</header>
		</div>
	</div>
</div>

<form action="index.php" method="post" name="DetailView" id="form">
	{include file='DetailViewHidden.tpl'}

	<!-- Start of File Include by SAKTI on 10th Apr, 2008 -->
	{include_php file="include/DetailViewBlockStatus.php"}
	<!-- Start of File Include by SAKTI on 10th Apr, 2008 -->
	{if $MODULE eq 'formacion_cursos'}
		<div>
			{include file="modules/formacion_cursos/DetailViewCursos.tpl"}
		</div>

	{/if}
	{foreach key=header item=detail from=$BLOCKS}
		{if $header eq $MOD.LBL_COMMENTS || $header eq $MOD.LBL_COMMENT_INFORMATION}
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box">
						<header class="main-box-header clearfix">
							<h2>{$MOD.LBL_COMMENT_INFORMATION}</h2>
						</header>
						<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
								{$COMMENT_BLOCK}
						</div>
					</div>
				</div>
			</div>
		{else}
			<div class="row">
				<div class="col-lg-12">
					<div class="main-box">
						<header class="main-box-header clearfix">
							<h2>{$header}</h2>
						</header>
						<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
							<table class="table table-bordered">
								{foreach item=detail from=$detail}
									<tr style="height:25px">
										{foreach key=label item=data from=$detail}
											{assign var=keyid value=$data.ui}
											{assign var=keyval value=$data.value}
											{assign var=keytblname value=$data.tablename}
											{assign var=keyfldname value=$data.fldname}
											{assign var=keyfldid value=$data.fldid}
											{assign var=keyoptions value=$data.options}
											{assign var=keysecid value=$data.secid}
											{assign var=keyseclink value=$data.link}
											{assign var=keycursymb value=$data.cursymb}
											{assign var=keysalut value=$data.salut}
											{assign var=keyaccess value=$data.notaccess}
											{assign var=keycntimage value=$data.cntimage}
											{assign var=keyadmin value=$data.isadmin}
											{assign var=display_type value=$data.displaytype}
											{assign var=_readonly value=$data.readonly}

											{if $label ne ''}
												{if $keycntimage ne ''}
													<td class="dvtCellLabel" align=right width=25%><input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input>{$keycntimage}</td>
												{elseif $keyid eq '71' || $keyid eq '72'}<!-- Currency symbol -->
													<td class="dvtCellLabel" align=right width=25%>{$label}<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input> ({$keycursymb})</td>
													{elseif $keyid eq '9'}
													<td class="dvtCellLabel" align=right width=25%><input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input>{$label} {$APP.COVERED_PERCENTAGE}</td>
													{elseif $keyid eq '14'}
													<td class="dvtCellLabel" align=right width=25%>{$label}<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input> {"LBL_TIMEFIELD"|@getTranslatedString} </td>
													{elseif $keyid neq '100' and $keyid neq '101'}
													<td class="dvtCellLabel" align=right width=25%><input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input>{$label}</td>
													{/if}
													{if $EDIT_PERMISSION eq 'yes' && $display_type neq '2' && $_readonly eq '0'}
														{* Performance Optimization Control *}
														{if !empty($DETAILVIEW_AJAX_EDIT) }
															{include file="DetailViewUI.tpl"}
														{else}
															{include file="DetailViewFields.tpl"}
														{/if}
														{* END *}
													{else}
														{include file="DetailViewFields.tpl"}
													{/if}
												{/if}
											{/foreach}
									</tr>
								{/foreach}
							</table>

						</div>
					</div>
				</div>
			</div>
		{/if}
	{/foreach}
	{if $MODULE eq 'Products'}
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2>Productos Asociados</h2>
				</header>
				<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
						{$ASSOCIATED_PRODUCTS}
				</div>
			</div>
		</div>
	</div>
	{/if}
	{*-- End of Blocks--*}

</form>




{if $SHOW_RELATED_HORIZONTAL eq 1}
<div class="row">
		<div class="col-lg-12">
			<div class="main-box no-header">
				<div class="main-box-body clearfix">
					<div class="tabs-wrapper profile-tabs" id="RLContents">
						{include file= 'RelatedListNewHorizontal.tpl'}
					</div>
				</div>
		</div>
	</div>
</div>
{/if}



<script>

function getTagCloud()
{ldelim}
	var obj = $("tagfields");
	if(obj != null && typeof(obj) != undefined) {ldelim}
		new Ajax.Request(
		    'index.php',
			{ldelim}queue: {ldelim}position: 'end', scope: 'command'{rdelim},
			method: 'post',
			postBody: 'module={$MODULE}&action={$MODULE}Ajax&file=TagCloud&ajxaction=GETTAGCLOUD&recordid={$ID}',
			onComplete: function(response) {ldelim}
                                $("tagfields").innerHTML=response.responseText;
                                $("txtbox_tagfields").value ='';
                        {rdelim}
			{rdelim}
		);
	{rdelim}
{rdelim}
getTagCloud();
</script>
<!-- added for validation -->
<script language="javascript">
  var fieldname = new Array({$VALIDATION_DATA_FIELDNAME});
  var fieldlabel = new Array({$VALIDATION_DATA_FIELDLABEL});
  var fielddatatype = new Array({$VALIDATION_DATA_FIELDDATATYPE});
</script>

{if $MODULE eq 'Leads' or $MODULE eq 'Contacts' or $MODULE eq 'Accounts' or $MODULE eq 'Campaigns' or $MODULE eq 'Vendors'}
	<form name="SendMail"><div id="sendmail_cont" style="z-index:100001;position:absolute;"></div></form>
{/if}
{$DLG_DETALLE_NOTIFICACION}
{$DLG_NUEVA_NOTIFICACION}
{$CUSTOM_HTML}