<div class="col-lg-6">
	<div class="main-box">	
	<header class="main-box-header clearfix">
		<h2>Por hacer</h2>
		</header>
		<div class="main-box-body clearfix">
			<ul class="widget-todo">
			{foreach item=TASK from=$TASKS}
				<li class="clearfix">
					<div class="name">
						<div>
						<a href="index.php?module=Calendar&action=DetailView&record={$TASK.activityid}">
						<label for="todo-1">
							{$TASK.subject}
						</label>
						</a>
						</div>
					</div>
					<div class="actions">
						<a href="index.php?module=Calendar&action=EditView&record={$TASK.activityid}" class="table-link">
						<i class="fa fa-pencil"></i>
						</a>
						<a href="javascript:confirmdelete('index.php?module=Calendar&action=Delete&record={$TASK.activityid}&return_module=Home&return_action=index')" class="table-link danger">
						<i class="fa fa-trash-o"></i>
						</a>
					</div>
				</li>
			{/foreach}
			</ul>
			<div style="float:right">
			<a href="index.php?action=EditView&module=Calendar&parenttab=">
			<button type="button" class="btn btn-primary" id="txtSubs">Crear tarea</button>
			</a>
			</div>
			<div style="float:right;padding-right:10px;">
			<a href="index.php?action=index&module=Calendar&parenttab="><h2>Ver más</h2></a>
			</div>
		</div>
		
	</div>
</div>