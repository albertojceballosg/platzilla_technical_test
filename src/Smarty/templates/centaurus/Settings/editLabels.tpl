{strip}
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/select2.css" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/bootstrap-editable.css" />
<div class="row">
	<div class="col-lg-12" style="background-color: #ffffff;">
		<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="width: 30px; padding: 0;">
						<i class="fa fa-language yellow-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">
						<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
						<li>{$APP.LBL_EDIT_LABELS}</li>
						<li class="active">{if (empty ($FLD_MODULE))}SISTEMA{else}{$FLD_MODULE|upper}{/if}</li>
					</ol>
				</td>
			</tr>
			<tr>
				<td class="small" colspan="3" valign="top">{$MOD.LBL_EDIT_LABELS_DESCRIPTION}</td>
			</tr>
		</table>
	</div>
	<form action="index.php" method="post" name="form" onsubmit="VtigerJS_DialogBox.block ();">
		<input type="hidden" name="fld_module" value="{$FLD_MODULE}" />
		<input type="hidden" name="module" value="Settings" />
		<input type="hidden" name="parenttab" value="Settings" />
		<input type="hidden" name="action" value="editLabels" />
		<input type="hidden" name="lang" value="{$LANG}" />
		<input type="hidden" name="mode" value="edit" />
		<div class="col-lg-12" style="background-color: #ffffff;">
			<div class="col-lg-10 pull-left">
				<h1>{$LANGUAGE}</h1>
			</div>
			<div class="col-lg-2 pull-right">
				<button type="submit" class="btn btn-success pull-right">{$APP.LBL_SAVE_LABEL}</button>
			</div>
		</div>
{if (isset ($CFENTRIES))}
	{foreach $CFENTRIES as $entries}
		<div class="main-box clearfix col-lg-12">
			<header class="main-box-header clearfix">
				<h2>{$entries.blocklabel}</h2>
			</header>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table id="user" class="table table-hover" style="clear: both">
						<tbody>
		{foreach $entries.field as $value}
						<tr>
							<td width="65%">
								<a href="#" id="{$value.columnname}" data-type="text" data-pk="1" class="editable editable-click">{$value.label}</a>
								<input name="{$value.columnname}" id="fld_{$value.columnname}" type="hidden" value="{$value.label}">
								<input name="label_{$value.columnname}" id="label_{$value.columnname}" type="hidden" value="{$value.fieldlabel}">
							</td>
						</tr>
		{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	{/foreach}
{/if}
{if ($LISTLABELS|@count > 0)}
		<div class="main-box clearfix col-lg-12">
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table id="user" class="table table-hover" style="clear: both">
						<tbody>
	{foreach $LISTLABELS as $id => $value}
		{if ($id != '')}
						<tr>
							<td width="65%">
								<a href="#" id="{$id}" data-type="text" data-pk="1" class="editable editable-click">{$value}</a>
								<input name="{$id}" id="fld_{$id}" type="hidden" value="{$value}">
								<input name="label_{$id}" id="label_{$id}" type="hidden" value="{$id}">
							</td>
						</tr>
		{/if}
	{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
{/if}
	</form>
</div>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-editable.min.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/select2.min.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/moment.min.js"></script>
<script type="text/javascript">
{literal}
	jQuery (document).ready (function () {
		jQuery.fn.editable.defaults.mode = 'inline';
		jQuery (".editable").editable ({
			success: function (response, newValue) {
				jQuery ("[id='fld_" + this.id + "']").val (newValue);
			}
		});
	});
{/literal}
</script>
{/strip}