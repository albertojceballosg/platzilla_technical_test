<script type="text/javascript" src="themes/centaurus/js/jquery.nestable.maxDepth.js"></script>
<script type="text/javascript" src="modules/Settings/Settings.js"></script>
<div class="clearfix">
	<div class="col-lg-12">
		<table class="table" border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="padding: 0; width: 30px;">
						<i class="fa fa-hand-o-up purple-bg"></i>
					</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">
						<li><a href="index.php?module=Settings&action=index&parenttab=Settings">{$MOD.LBL_SETTINGS}</a></li>
						<li>{$MOD.LBL_KPIS_BOXSCORE}</li>
					</ol>
				</td>
			</tr>
			<tr>
				<td class="small" colspan="3" valign="top">{$MOD.LBL_KPIS_BOXSCORE_DESCRIPTION}</td>
			</tr>
		</table>
	</div>
	<br />
	<br />
{if ($MSG_ERROR != '')}
	<div class="col-lg-12">
		<div class="alert alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<i class="fa fa-times-circle fa-fw fa-lg"></i>
			<strong>ERROR!</strong> {$MSG_ERROR}.
		</div>
	</div>
{/if}
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<br />
				<div class="pull-right" style="margin-right: 20px;">
					<a class="btn btn-primary" href="index.php?module=Settings&action=CreateKpisBoxscore">{$MOD.LBL_KPIS_BOXSCORE_CREATE}</a>
				</div>
				<br />
				<div class="main-box-body clearfix">
					<br />
					<div id="appscontents">
{include file='Settings/kpisBoxscoreContents.tpl'}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="editdiv" style="display: none; position: absolute; width: 400px;"></div>
<div class="md-overlay"></div>