{extends file="base/DetailView.tpl"}

{block name="css" append}
{/block}

{block name="js" append}

<script language="JavaScript" type="text/javascript" src="include/js/dtlviewajax.js"></script>
<!--span id="crmspanid" style="display:none;position:absolute;"  onmouseover="show('crmspanid');">
	<a class="link"  align="right" href="javascript:;">{$APP.LBL_EDIT_BUTTON}</a>
</span-->

{/block}


{block name="scripts" append}

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
{/literal}
</script>
<style>
{literal}
.table tbody > tr > td {
  font-size: 1.125em;
  font-weight: 300;
}
{/literal}
</style>

{/block}

{block name="navigation-tabs"}
	{*
	{if $COL_ACCIONES neq 'false'}
		{include file='DetailViewActions.tpl'}
	{/if}
	*}

	{if !empty($IS_REL_LIST)}
		<li class="dropdown">
				<a class="dropdown-toggle" href="#" data-toggle="dropdown">{$APP.LBL_MORE} {$APP.LBL_INFORMATION}
					<span class="caret"></span>
				</a>
					<ul class="dropdown-menu" role="menu">
						{foreach key=_RELATION_ID item=_RELATED_MODULE from=$IS_REL_LIST}
							<li ><a role="menuitem" tabindex="-1" href="index.php?action=CallRelatedList&module={$MODULE}&record={$ID}&parenttab={$CATEGORY}&selected_header={$_RELATED_MODULE}&relation_id={$_RELATION_ID}&platdb={$PLATDB}">{$_RELATED_MODULE|@getTranslatedString:$MODULE}</a></li>
						{/foreach}
					</ul>
		</li>
	{/if}

{/block}

{block name="content"}
	<div>
		<div id="tab-detail" class="tab-pane fade in active">
			<form action="index.php" method="post" name="DetailView" id="form">
				{include file='DetailViewHidden.tpl'}

				<!-- Start of File Include by SAKTI on 10th Apr, 2008 -->
				{*   {include_php file="include/DetailViewBlockStatus.php"}   *}
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
									<header class="title-section main-box-header clearfix">
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
									<header class="title-section main-box-header clearfix">
										<h2>{$header}</h2>
									</header>
									<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
										<table width="100%" class="small" cellpadding="5">

											{assign var=detailD value=$detail}

											{foreach item=detail from=$detailD}

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
																{elseif $keyid eq '14'}
																<td class="dvtCellLabel" align=right width=25%>{$label}<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input> {"LBL_TIMEFIELD"|@getTranslatedString} </td>
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
				{if $MODULE eq 'product' || $MODULE eq 'SalesOrder' || $MODULE eq 'quote' || $MODULE eq 'myinvoice'}
				<div class="row">
					<div class="col-lg-12">
						<div class="main-box">
							<header class="title-section main-box-header clearfix">
								<h2>Productos Asociados</h2>
							</header>
							<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
									{$ASSOCIATED_PRODUCT}

							</div>
						</div>
					</div>
				</div>
				{/if}
				{*-- End of Blocks--*}
			</form>
		</div>

		<div id="tab-notes" class="tab-pane fade"></div>

	</div>
{/block}

{block name="scripts" append}

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

{/block}