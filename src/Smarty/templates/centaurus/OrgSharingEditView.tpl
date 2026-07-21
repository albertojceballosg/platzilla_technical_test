{strip}
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-lock emerald-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active">{$MOD.LBL_SHARING_ACCESS|upper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_SHARING_ACCESS_DESCRIPTION}</td>
		</tr>
		</tbody>
	</table>
	<div class="main-box clearfix">
		<form action="index.php" method="post">
			<input type="hidden" name="module" value="Settings" />
			<input type="hidden" name="action" value="SaveOrgSharing" />
			<input type="hidden" name="parenttab" value="Settings" />
			<input type="hidden" name="Ajax" value="true" />
			<header class="main-box-header clearfix">
				<h2 class="pull-left">{$CMOD.LBL_GLOBAL_ACCESS_PRIVILEGES}</h2>
				<div class="pull-right text-right">
					<button class="btn btn-primary" type="submit" style="margin-right: 5px;">{$MOD.LBL_SAVE}</button>
					<a href="index.php?module=Settings&amp;action=OrgSharingDetailView&amp;parenttab=Settings" class="btn btn-warning">{$MOD.LBL_CANCEL_BUTTON}</a>
				</div>
			</header>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table table-striped table-hover">
						<thead>
						<tr>
							<th class="col-modulename"><b>Módulo</b></th>
							<th class="col-access"><b>Acceso</b></th>
						</tr>
						</thead>
						<tbody>
{if (!empty ($DEFAULT_SHARING))}
	{foreach $DEFAULT_SHARING as $module}
						<tr>
							<td class="col-modulename">{$AVAILABLE_MODULES[$module[0]]['tablabel']} ({$module[0]})</td>
							<td class="col-access">
								<select name="access[{$module[0]}]" class="form-control" title="">
									<option value="0"{if ($module[1] == 'Public: Read Only')} selected="selected"{/if}>Público: Solo lectura</option>
									<option value="1"{if ($module[1] == 'Public: Read, Create/Edit')} selected="selected"{/if}>Público: Lectura, creación y edición</option>
									<option value="2"{if ($module[1] == 'Public: Read, Create/Edit, Delete')} selected="selected"{/if}>Público: Lectura, creación, edición y borrado</option>
									<option value="3"{if ($module[1] == 'Private')} selected="selected"{/if}>Privado</option>
								</select>
							</td>
						</tr>
	{/foreach}
{else}
						<tr class="lvtColData">
							<td colspan="2" class="text-center">No hay privilegios registrados</td>
						</tr>
{/if}
						</tbody>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>
{/strip}