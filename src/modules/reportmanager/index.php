<?php
	global $adb, $current_user, $mod_strings;
	if (strstr (getcwd (), 'reportmanager')) {
		chdir ('../../');
	}
	require_once ('include/utils/utils.php');
	require_once ('modules/reportmanager/reportmanager.php');

	define ('DEFAULT_MODULE_FOLDER', 'modules/reportmanager/');

	if ((isset ($_REQUEST ['page'])) && (($_REQUEST ['page']))) {
		$actualPage = intval ($_REQUEST['page']);
	} else {
		$actualPage = 1;
	}

	if ((isset ($_GET ['iddelete'])) && ($_GET ['iddelete'])) {
		deleteReport ($_GET ['iddelete']);
	}

	$templates = array ();
	$templates = getReportsAllList ($actualPage);
	$serverPath = $_SERVER ['SERVER_NAME'] . str_replace ('index.php', '', $_SERVER ['PHP_SELF']);
	// @codingStandardsIgnoreStart
?>
<style type="text/css">
	.table {
		margin-bottom: 0;
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;">
					<i class="fa fa-file-pdf-o purple-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active" style="text-transform: uppercase;"><?php echo $mod_strings['ModuleName']; ?></li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top"></td>
		</tr>
		</tbody>
	</table>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<header class="main-box-header clearfix">
					<div class="filter-block pull-right">
						<div class="filter-block pull-right">
							<a href="?module=reportmanager&action=reportTemplate&parenttab=<?php echo $_REQUEST['parenttab']; ?>" id="reporttemplate" class="btn btn-primary  pull-right">
								<i class="fa fa-link fa-lg" title="<?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_CREATE_REPORT'); ?>"></i>
								<?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_CREATE_REPORT'); ?>
							</a>
							<a href="?module=reportmanager&action=listTemplate&parenttab=<?php echo $_REQUEST['parenttab']; ?>" id="listtemplate" class="btn btn-success pull-right">
								<i class="fa fa-book fa-lg" title="<?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_TEMPLATE_TITLE'); ?>"></i>
								<?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_TEMPLATE_TITLE'); ?>
							</a>
						</div>
					</div>
				</header>
				<div class="main-box-body clearfix" id="ListViewContents">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
							<tr>
								<th align="center"><b><?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_NAMEREPORT'); ?></b></th>
								<th align="center" width="20%"><b><?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_MODULE'); ?></b></th>
								<th align="center" width="10%"><b><?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_ACTIVE'); ?></b></th>
								<th align="center" width="20%"><b><?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_ACTIONS'); ?></b></th>
							</tr>
							</thead>
							<tbody>
<?php foreach ($templates as $template): ?>
								<tr bgcolor="white" class="lvtColData">
									<td align="left">
										<p style="margin: 0;"><?php echo $template['name']; ?></p>
										<p style="margin: 0; font-size: 0.85em;">(<?php echo $template['code']; ?>)</p>
									</td>
									<td align="left"><?php echo getTranslatedString ($template['module']); ?></td>
									<td align="left"><?php if ($template['active'] == '1') { echo ' x '; } else { echo '  '; } ?></td>
									<td align="center">
										<a class="table-link" href="index.php?module=reportmanager&action=reportmanagerAjax&file=View&idview=<?php echo $template['code']; ?>&page=<?php echo $actualPage; ?>">
											<span class="fa-stack" title='<?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_ACTIONS_VIEW'); ?>'>
												<i class="fa fa-square fa-stack-2x"></i>
												<i class="fa fa-eye fa-stack-1x fa-inverse"></i>
											</span>
										</a>
										<a class="table-link" href='?module=reportmanager&action=reportTemplate&parenttab=Settings&idedit=<?php echo $template['reportid']; ?>&page=<?php echo $actualPage; ?>'>
											<span class="fa-stack" title='<?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_ACTIONS_EDIT'); ?>'>
												<i class="fa fa-square fa-stack-2x"></i>
												<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
											</span>
										</a>
										<a class="table-link" href='?module=reportmanager&action=reportTemplate&parenttab=Settings&idduplicate=<?php echo $template['reportid']; ?>&page=<?php echo $actualPage; ?>'>
											<span class="fa-stack" title='<?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_ACTIONS_DUPLICATE'); ?>'>
												<i class="fa fa-square fa-stack-2x"></i>
												<i class="fa fa-copy fa-stack-1x fa-inverse"></i>
											</span>
										</a>
	<?php if (!$template['eventid']) { ?>
										<a class="table-link danger" href='?module=reportmanager&action=index&parenttab=Settings&iddelete=<?php echo $template['reportid']; ?>&page=<?php echo $actualPage; ?>'  onclick='return confirm("<?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_ACTIONS_DELETE_REPORT'); ?>")'>
										<span class="fa-stack" title='<?php echo getTranslatedString ('LBL_PLAT_REPORTMANAGER_ACTIONS_REMOVE'); ?>'>
												<i class="fa fa-square fa-stack-2x"></i>
												<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
											</span>
										</a>
	<?php } else { ?>
										&nbsp;
	<?php } ?>
									</td>
								</tr>
<?php endforeach ?>
							</tbody>
						</table>
					</div>
<?php require ('paginador.php'); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php // @codingStandardsIgnoreEnd ?>
