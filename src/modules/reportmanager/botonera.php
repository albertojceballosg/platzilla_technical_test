<div class="btn-group">
	<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
		<i class="fa fa-cubes"></i> <?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_ACTIONS');?> <span class="caret"></span>
	</button>
	<ul class="dropdown-menu" role="menu">
		<li>
			<a href="?module=reportmanager&action=listTemplate&parenttab=<?php echo $_REQUEST['parenttab']?>">
				<i class="fa fa-book"></i><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_TEMPLATE_TITLE');?></a>
		</li>
		<li>
			<a href="?module=reportmanager&action=Report&parenttab=<?php echo $_REQUEST['parenttab']?>">
				<i class="fa fa-link"></i><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_CREATE_REPORT');?></a>
		</li>
	</ul>
</div>

