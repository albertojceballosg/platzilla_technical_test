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
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/bootstrap-wizard.css">
<script type="text/javascript" src="include/js/reflection.js"></script>
<script src="include/scriptaculous/scriptaculous.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript" src="include/js/dtlviewajax.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/RelatedLists.js"></script>

<!--span id="crmspanid" style="display:none;position:absolute;"  onmouseover="show('crmspanid');">
	<a class="link"  align="right" href="javascript:;">{$APP.LBL_EDIT_BUTTON}</a>
</span-->


<script>

var gVTModule = '{$smarty.request.module|@vtlib_purify}';
{literal}
function callConvertLeadDiv(obj,id)
{
	console.log ("entrando callConvertLeadDiv ");

 		$("status").style.display="inline";
       new Ajax.Request(
                'index.php',
                {queue: {position: 'end', scope: 'command'},
                        method: 'post',
                        postBody: 'module=Leads&action=LeadsAjax&file=ConvertLead&record='+id,
                        onComplete: function(response) {
                        		 $("status").style.display="none";
                        		 jQuery("#convertleaddiv").html('');
                                jQuery("#convertleaddiv").append(response.responseText);

                                 //jQuery('#convertleaddiv').modal('show');
       							fnvshobj(obj,"convertleaddiv");


								eval(jQuery("conv_leadcal").innerHTML);

                        }
                }
        );

	/*window.open("index.php?module=Leads&action=LeadsAjax&file=ConvertLead&record="+id+"&parenttab=Leads","leads_popup_window","height=540,width=640,toolbar=no,menubar=no,dependent=yes,resizable=no");*/

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



<div class="tabs-wrapper">

	<ul class="nav nav-tabs">
		<li class="active">
			<a data-toggle="tab" href="#tab-detail">{$APP.LBL_INFORMATION}</a>
		</li>
		{if $COL_ACCIONES neq 'false'}
			{include file='DetailViewActions.tpl'}
		{/if}
		{if $MODULE eq 'Potentials'}
		<li>
			<div style="float:left; padding-top: 12px;">
				&nbsp;&nbsp;Registros&nbsp;
				<span class="badge badge-info">{$REGISTERS}</span>
				&nbsp;&nbsp;
			</div>
		</li>
		{/if}
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
		{if $MODULE eq 'Potentials'}
			{if $CLOSED eq 'no_closed'}
				<div class="col-lg-5 pull-right" style="padding-right: 40px; float:left; padding-top: 4px;">
					<a id="lostPotential" href="javascript:void(0)" tagModule= "{$MODULE}" onclick="changeStatePotential('Closed Lost',{$ID}, 'winnPotential', 'lostPotential');" class="btn btn-danger pull-right" style="margin-right:5px;">
						<span class="fa"></span> {$APP.LBL_LOST_BUTTON_LABEL}
					</a>
					<a id="winnPotential" href="javascript:void(0)" tagModule= "{$MODULE}" onclick="changeStatePotential('Closed Won',{$ID}, 'lostPotential', 'winnPotential');" class="btn btn-success pull-right" style="margin-right:5px;">
						<span class="fa"></span> {$APP.LBL_WINN_BUTTON_LABEL}
					</a>
				</div>
			{else}

				<div class="col-lg-5 pull-right" style="padding-right: 20px; float:right; padding-top: 10px;">
					{if $CLOSED eq 'Closed Lost'}
						<span class="label label-danger label-large">{$APP.LBL_LOST_BUTTON_LABEL}</span>
					{else}
						<span class="label label-success label-large">{$APP.LBL_WINN_BUTTON_LABEL}</span>
					{/if}
				</div>
			{/if}
		{/if}
	</ul>

	<div class="tab-content">
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

					{* if el block es Progreso se muestra el cuadro de Tareas *}
					{if $header eq 'Progreso' || $header eq 'Progress'}

						<div class="row">
							<div class="col-lg-12">
								<div class="main-box">
									<header class="main-box-header clearfix">
										<h2></h2>
									</header>
									<div class="main-box-body clearfix" id="tbl_hitosTareas">
										{include file='modules/proyectos/hitosTareasBundy.tpl'}
									</div>
								</div>
							</div>
						</div>


					{/if}

						<div class="row">
							<div class="col-lg-12">
								<div class="main-box table-responsive">
									<header class="main-box-header clearfix">
										<h2>{$header}</h2>
									</header>
									<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
										<!--table width="100%" cellpadding="5"-->
											{assign var=detailD value=$detail}

												{foreach item=detail from=$detailD}
												<!--tr style="height:25px"-->
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
																<!--td align=right width=25%><input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input>{$keycntimage}</td-->
															{elseif $keyid eq '14'}
																<!--td  align=right width=25%>{$label}<input type="hidden" id="hdtxt_IsAdmin" value={$keyadmin}></input> {"LBL_TIMEFIELD"|@getTranslatedString} </td-->
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
												<!--/tr-->
											{/foreach}
										<!--/table-->
									</div>
								</div>
							</div>
						</div>

					{/if}
				{/foreach}
				{if $SHOW_GANTT eq 1}
						<div class="row">
							<div class="col-lg-12">

								<div class="main-box no-header">

									<header class="main-box-header clearfix">
										<h1>GANTT</h1>
									</header>
									<div class="main-box-body clearfix" id="tbl_GANTT">
										{include file='modules/proyectos/GANTT.tpl'}
									</div>
								</div>
							</div>
						</div>
				{/if}
				{if $CAMPOS_PERSONALIZADOS || $CAMPOS_TIPO_GRID || $CAMPOS_TIPO_MATRIX}

					   {$CAMPOS_PERSONALIZADOS}
					   	   <script type="text/javascript" src="include/js/gridFormValidate.js"></script>
					   {$CAMPOS_TIPO_GRID}
					   {$CAMPOS_TIPO_MATRIX}

				{/if}
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
		</div>


		{if $MODULE eq 'instancias'}
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box">
					<header class="main-box-header clearfix">
						<h2>Aplicaciones Contratadas</h2>
					</header>
					<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
						{include file="modules/instancias/apps_contratadas.tpl"}
					</div>
				</div>
			</div>
		</div>
		{/if}

		<div id="tab-notes" class="tab-pane fade"></div>

	</div>
</div>


	<div id="convertleaddiv" style="background-color:#FFFFFF;display:block;position:absolute;left:225px;top:150px;"></div>

{*-- Inicio del popup de Tareas --*}
<div class="wizard" id="wizard-demo">
	<h1>{"LBL_CREAR"|@getTranslatedString} {"LBL_Task"|@getTranslatedString}</h1>

	<div class="wizard-card" data-onValidated="setServerName" data-cardname="name">
		<h3><span>{"LBL_Tasks"|@getTranslatedString}</span></h3>

		<div class="wizard-input-section">

			<div class="form-group">
				<label for="ticket_title">{"LBL_Titulo"|@getTranslatedString}</label>
				<input type="hidden" class="form-control" id="ticket_idProyecto" name="ticket_idProyecto" value="">
				<input type="hidden" class="form-control" id="ticket_idHito" name="ticket_idHito" value="">
				<input type="hidden" class="form-control" id="ticket_plan" name="ticket_plan" value="{$MODULE}">
				<input type="text" class="form-control" id="ticket_title" data-validate="validarTitle">
			</div>

			<div class="form-group">
				<label for="customerdescription">{"LBL_Descripcion_Tarea"|@getTranslatedString} </label>
				<textarea class="form-control" name="customerdescription" id="customerdescription"  data-validate="validarDesc"></textarea>
			</div>

		</div>

	</div>

	<div class="wizard-card" data-onload="" data-cardname="dates">
		<h3><span>{"LBL_Fechas"|@getTranslatedString}</span></h3>

		<div class="wizard-input-section">

			<div class="form-group">
				<label for="datepickerDate">{"LBL_Fecha_Inicio"|@getTranslatedString}</label>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input class="form-control" id="datepickerDate" name="start_date" type="text">
				</div>
			</div>

			<div class="form-group">
				<label for="datepickerDateEnd">{"LBL_Fecha_fin"|@getTranslatedString}</label>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input class="form-control" id="datepickerDateEnd" name="end_estimated_date" type="text">
				</div>
			</div>

		</div>
	</div>

	<div class="wizard-card" data-cardname="services">
		<h3><span> {"LBL_Responsable_Actividad"|@getTranslatedString}</span></h3>

		<div class="wizard-input-section">

			<div class="form-group">
				<label for="ticketstatus">{"LBL_Estado_Tareas"|@getTranslatedString}</label>
				<select class="form-control" name="ticketstatus" id="ticketstatus">
					{foreach key=key_one item=value from=$TICKETSTATUS}
						<option value="{$value}"> {"$value"|@getTranslatedString}</option>
					{/foreach}
				</select>
			</div>

			<div class="form-group">
				<label for="assigntype">{"LBL_Asignado"|@getTranslatedString}</label>
				<input type="hidden" id="assigntype_text" value="">
				<input type="radio" name="assigntype_p" value="U" onclick="toggleAssignType(this.value)">&nbsp;{$APP.LBL_USER}
            	<input type="radio" name="assigntype_p" value="T" onclick="toggleAssignType(this.value)">&nbsp;{$APP.LBL_GROUP}

            	<div id="assign_team_p" style="display:none">
					<select name="assigned_group_id" id="assigned_group_id_p" class="form-control">
						{foreach key=key_one item=arr from=$GRUPOS}
								<option value="{$arr.value}"> {$arr.label}</option>
						{/foreach}
					</select>
				</div>

	   			<div id="assigned_user_p" style="display:none">
					<select name="assigned_user_id" id="assigned_user_id_p" class="form-control">
						{foreach key=key_one item=arr from=$USUARIOS}
								<option value="{$arr.value}"> {$arr.label}</option>
						{/foreach}
					</select>
				</div>



			</div>

		</div>
	</div>


	<div class="wizard-error">
		<div class="alert alert-error">
			<strong>{"LBL_INFORMACION_TROUBLE"|@getTranslatedString}</strong>.
			{"LBL_PLEASE_VERIFY"|@getTranslatedString}
		</div>
	</div>

	<div class="wizard-failure">
		<div class="alert alert-error">
			{"LBL_TROUBLE_SAVING"|@getTranslatedString}
			{"LBL_PLEASE_TRY_AGAIN"|@getTranslatedString}
		</div>
	</div>

	<div class="wizard-success">
		<div class="alert alert-success">
			<span class="create-server-name"></span>
			{"LBL_SUCCESSFUL_SAVE"|@getTranslatedString}
		</div>

		<a class="btn btn-primary create-another-server">{"LBL_CREAR"|@getTranslatedString} {"LBL_NUEVA"|@getTranslatedString} {"LBL_Task"|@getTranslatedString}</a>
		<a class="btn btn-default im-done">{"LBL_GET_OUT"|@getTranslatedString}</a>
	</div>
</div>
{*-- Fin del popup de Tareas --*}











{*-- funciones del popup de Tareas --*}


<script>

function validarTitle(el) {ldelim}
	var val = el.val();
	ret = {ldelim}
		status: true
	{rdelim};
	if (!val){ldelim}
		ret.status = false;
		ret.msg = "{'LBL_TITLE_EMPTY'|@getTranslatedString}";
	{rdelim}

	return ret;
{rdelim}


function validarDesc(el) {ldelim}
	var val = el.val();
	ret = {ldelim}
		status: true
	{rdelim};
	if (!val){ldelim}
		ret.status = false;
		ret.msg = '{"LBL_DESCRIPTION_EMPTY"|@getTranslatedString}';
	{rdelim}

	return ret;
{rdelim}

</script>

{*-- Funciones del popup de Tareas --*}











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