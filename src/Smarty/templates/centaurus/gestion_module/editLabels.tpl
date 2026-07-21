<link rel="stylesheet" href="themes/{$THEME}/css/libs/select2.css" type="text/css" />
<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/bootstrap-editable.css">



			<form action="index.php" method="post" name="form" onsubmit="VtigerJS_DialogBox.block();">			
				<div class="col-lg-12">
					<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
						<tr>
							<td rowspan="2" valign="top">
							<div class="infographic-box" style="width:30px;padding:0px;">
							<i class="fa fa-language yellow-bg"></i>
							</div>
							</td>
							<td class="heading2" valign="bottom">
							<ol class="breadcrumb">
								<li><a href="index.php?module=gestion_module&action=ModuleManager&parenttab=gestion_module">{$MOD.LBL_MY_MODULES}</a></li>
								<li class="active">{$MOD.LBL_EDIT_LABELS}</li>
							</ol>
							</td>
						</tr>

						<tr>
							<td class="small" valign="top">{$MOD.LBL_EDIT_LABELS_DESCRIPTION}</td>
						</tr>
					</table>
					<br/>
					<div class="col-lg-10 pull-left">
						<h1>{$MOD.LBL_EDIT_LABELS} - {$LANGUAGE}</h1>
					</div>
					<div class="col-lg-2 pull-right">
						<button type="submit" class="btn btn-success" >{$APP.LBL_SAVE_LABEL}</button>
						<div class="btn-group">
							<button type="button" class="btn btn-success">{$MOD.LBL_EDIT_LABELS}</button>
							<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								{foreach name=lang key=code item=name from=$LISTLANGUAGUES}		
								<li><a href="index.php?module=gestion_module&action=editLabels&fld_module={$FLD_MODULE}&lang={$code}">{$name}</a></li>
								{/foreach}
							</ul>
						</div>
					</div>
				</div>
				<input type="hidden" name="fld_module" value="{$FLD_MODULE}">
				<input type="hidden" name="module" value="gestion_module">
				<input type="hidden" name="parenttab" value="gestion_module">
				<input type="hidden" name="action" value="editLabels">
				<input type="hidden" name="lang" value="{$LANG}">
				<input type="hidden" name="mode" value="edit">
				{foreach item=entries key=id from=$CFENTRIES name=outer}
					{assign var=count value=$smarty.foreach.outer.iteration}
					<div class="main-box clearfix">
						<header class="main-box-header clearfix">
							<h2>{$entries.blocklabel}</h2>
						</header>
						<div class="main-box-body clearfix">
							<div class="table-responsive">
								<table id="user" class="table table-hover" style="clear: both">
									<tbody> 
										{foreach item=value from=$entries.field name=fields}
										{assign var=countfields value=$smarty.foreach.fields.iteration}
										<tr>		 
											<td width="35%">{$value.fieldlabel}</td>
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
				
				{if $LISTLABELS|@count > 0}
					<div class="main-box clearfix">
						<div class="main-box-body clearfix">
							<div class="table-responsive">
								<table id="user" class="table table-hover" style="clear: both">
									<tbody> 
										{foreach item=value from=$LISTLABELS name=fields key=id}
										{if $id neq ''}
										<tr>		 
											<td width="35%">{$id}</td>
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
									
<script src="themes/{$THEME}/js/bootstrap-editable.min.js"></script>
<script src="themes/{$THEME}/js/select2.min.js"></script>
<script src="themes/{$THEME}/js/moment.min.js"></script>
<!-- this page specific inline scripts -->
{literal}
	<script>
	jQuery(document).ready(function(){
		jQuery.fn.editable.defaults.mode = 'inline';
		jQuery( ".editable" ).editable({
			success: function(response, newValue) {
				jQuery("[id='fld_"+this.id+"']").val(newValue);
			}
		});
		
	});
	
	
	</script>
{/literal}