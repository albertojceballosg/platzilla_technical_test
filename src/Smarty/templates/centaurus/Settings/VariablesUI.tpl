{strip}
<script type="text/javascript" src="modules/Settings/Settings.js"></script>
<br />
<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">
			<ol class="breadcrumb">
				<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
				<li>Variables del Sistema</li>
			</ol>
		</div>
	</div>
</div>
<div class="col-lg-12">
	<div class="row">
		<div class="main-box no-header clearfix" style="">
			<div class="col-lg-3 pull-right">
				<input type="hidden" id="formodule" value="{$FOR_MODULE}" />
				<input title="{$CMOD.LBL_NEW_GROUP}" class="btn btn-primary btn-sm pull-right" type="button" name="New" value="Nueva Variable" style="padding-right: 40px; margin-bottom: 20px;" />
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<div class="main-box-body clearfix">
				<div class="table-responsive">
{foreach $VAR_GROUPS as $groupname => $variables}
					<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
						<tbody>
						<tr>
							<th>{$groupname|getTranslatedString}</th>
							<th align="right"></th>
							<th align="right"></th>
						</tr>
						</tbody>
					</table>
					<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
						<tbody>
	{foreach $variables as $variable}
						<tr>
							<td width="20%" nowrap="">
								<strong>{$variable.varname|getTranslatedString}</strong>
							</td>
							<td width="70%">
								{$variable.value}
							</td>
							<td width="10%">
								<a href="javascript: void(0);" onclick="editVariable (this);" data-id="{$variable.variableid}" data-value="{$variable.value}" data-name="{$variable.varname}" data-module-id="{$variable.tabid}"><i class="fa fa-pencil"></i></a> |
								<a href="javascript: void(0);" onclick="deleteVariable (this);" data-id="{$variable.variableid}"><i class="fa fa-trash-o"></i></a>
							</td>
						</tr>
	{/foreach}
						</tbody>
					</table>
{/foreach}
				</div>
			</div>
		</div>
	</div>
</div>
<div id="editVarUI" class="calAddEvent layerPopup" style="position: fixed; top: 5%; left: 7%; z-index: 1000; width: 85%; max-height: 600px; max-width: 1200px; display: none;">
	<table border="0" cellspacing="0" cellpadding="5" width="100%" class="layerHeadingULine">
		<tbody>
		<tr>
			<td class="layerPopupHeading" align="left">{"Edición de variable de sistema"|getTranslatedString}</td>
			<td align="right">
				<a href="javascript: void(0);" onclick="cierraidUI ('editVarUI')"><img src="themes/images/close.gif" border="0" align="absmiddle"></a>
			</td>
		</tr>
		</tbody>
	</table>
	<form action="index.php?module=Settings&action=Variables&parenttab=Settings" method="post" id="varEditfrm">
		<input type="hidden" value="" name="variableid" id="variableid" />
		<input type="hidden" value="save" name="function" />
		<input type="hidden" value="{$FOR_MODULE}" name="formodule" />
		<table border="0" cellspacing="0" cellpadding="5" width="100%" bgcolor="white">
			<tr>
				<td width="10%" class="dvtCellLabel" align="right">Código de variable</td>
				<td align="left" class="dvtCellInfo">
					<input type="text" value="" name="varname" id="varname" class="small" style="width: 95%;" placeholder="" />
				</td>
			</tr>
			<tr>
				<td width="10%" class="dvtCellLabel" align="right">Valor</td>
				<td align="left" class="dvtCellInfo">
					<textarea name="value" id="value" placeholder=""></textarea>
				</td>
			</tr>
			<tr>
				<td width="10%" class="dvtCellLabel" align="right">Módulo</td>
				<td align="left" class="dvtCellInfo">
					<select name="tabid" id="tabid" title="">
{foreach $MODULES as $module}
						<option value="{$module.tabid}"{if $module.name eq $FOR_MODULE} selected="selected"{/if}>{$module.tablabel|getTranslatedString}</option>
{/foreach}
					</select>
				</td>
			</tr>
		</table>
		<table border="0" cellspacing="0" cellpadding="5" width="100%" class="layerPopupTransport">
			<tbody>
			<tr>
				<td align="center">
					<input title="Guardar [Alt+S]" accesskey="S" class="crmbutton small save" type="submit" name="button" value="  Guardar  " style="width:70px" />
					<input title="Cancelar [Alt+X]" accesskey="X" class="crmbutton small cancel" onclick="cierraidUI ('editVarUI')" type="button" name="button" value="  Cancelar  " style="width: 70px:" />
				</td>
			</tr>
			</tbody>
		</table>
	</form>
</div>
{/strip}