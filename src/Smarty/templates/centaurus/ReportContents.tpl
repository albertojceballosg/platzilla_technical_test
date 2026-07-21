{strip}
{if (!empty ($AVAILABLE_FOLDERS))}
	{foreach $AVAILABLE_FOLDERS as $folder}
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix" {if isset($HIDDEN_TITLE)} style="display: none"{/if}>
				<h2 class="pull-left" style="margin: 0;">{$folder.foldername}{if (!empty ($folder.description))}<i style="font-weight: 300;"> - {$folder.description}</i>{/if}</h2>
		{if ($folder.protected == 0) && (count ($folder.reports) == 0)}
				<div class="action-bar pull-right">
					<button type="button" class="btn btn-link listview-controller" title="Borrar" onclick="DeleteFolder ('{$folder.folderid}');"><i class="fa fa-trash-o"></i></button>
				</div>
		{/if}
			</header>
			<div class="main-box-body clearfix">
				<div class="table-responsive" style="{if !isset($HIDDEN_TITLE)}border-bottom: 1px solid rgb(231, 235, 238); border-top: 1px solid rgb(231, 235, 238);{/if} overflow-y: visible;">
					<table class="table" id="table_list" style="margin-bottom: 0;">
					<thead>
						<tr class="title-overflow">
							<th style="width: 30%;">{$MOD.LBL_REPORT_NAME}</th>
							<th>{$MOD.LBL_DESCRIPTION}</th>
							<th style="text-align: right; width: 10em;"></th>
						</tr>
					</thead>
					<tbody>
		{if (!empty ($folder.reports))}
			{foreach $folder.reports as $report}
						<tr>
							<td style="width: 30%;">
								<a href="index.php?module=Reports&action=SaveAndRun&record={$report.reportid}&folderid={$folder.folderid}">{$report.reportname}</a>
							</td>
							<td>{$report.description}</td>
							<td style="text-align: right; width: 10em;">
				{if ($report.customizable == '1') && ($report.editable == 'true')}
								<button type="button" class="btn btn-link listview-controller" title="{$MOD.LBL_CUSTOMIZE_BUTTON}" onclick="ReportWizardUtils.show ('{$folder.folderid}', '{$report.reportid}');"><i class="fa fa-pencil"></i></button>
				{/if}
				{if ($report.state != 'SAVED') && ($report.editable == 'true')}
								<button type="button" class="btn btn-link listview-controller" title="{$MOD.LBL_DELETE}" onclick="deleteReport ('{$report.reportid}');"><i class="fa fa-trash-o"></i></button>
				{/if}
							</td>
						</tr>
			{/foreach}
		{/if}
						<tr>
							<td colspan="3" class="text-center" style="padding: 0;">
								<button type="button" class="btn btn-link" title="{$MOD.LBL_CREATE_REPORT}" onclick="ReportWizardUtils.show ('{$folder.folderid}');">
									<i class="fa fa-plus"></i>
								</button>
							</td>
						</tr>
					</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	{/foreach}
{else}
	<div class="col-lg-12">
		<div class="main-box no-header clearfix">
			<div class="main-box-body clearfix">
				<div class="alert alert-info" style="margin-bottom: 0;">No se encuentran informes registrados</div>
			</div>
		</div>
	</div>
{/if}
{/strip}