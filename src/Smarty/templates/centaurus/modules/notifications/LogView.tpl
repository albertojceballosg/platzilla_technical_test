{strip}
<style type="text/css">
	{literal}
	label {
		font-size:   1.11em;
		font-weight: 300;
	}
	.btn {
		margin-left: 5px;
	}
	.main-box > .main-box-header {
		padding-bottom: 20px;
		padding-top:    20px;
	}
	.main-box > .main-box-footer {
		padding: 0 20px 20px 20px;
	}
	.panel > .panel-heading {
		border:        1px solid #DDDDDD;
		margin-bottom: 1em;
	}
	.panel > .panel-heading > a > .panel-title {
		display: inline-block;
		width:   90%;
	}
	.panel > .panel-heading > .btn {
		position: absolute;
		right:    5px;
		top:      5px;
	}
	{/literal}
</style>
<div class="row">
	<div class="col-xs-12">
		<h1 class="pull-left"><a href="index.php?module=notifications&action=ListView&parenttab=Settings">{$MOD['EVENT_LOG']}</a></h1>
		<div class="action-bar pull-right">
			<form action="index.php" method="post" onsubmit="return confirm ('Se eliminará el registro de eventos. ¿Estás seguro?');">
				<input type="hidden" name="module" value="notifications" />
				<input type="hidden" name="action" value="DeleteLog" />
				<input type="hidden" name="Ajax" value="true" />
				<input type="hidden" name="record" value="{$NOTIFY_ID}" />
				<button type="submit" class="btn btn-danger">Borrar</button>
			</form>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h2 class="pull-left">Información general</h2>
			</header>
			<div class="main-box-body">
				<div class="row">
					<div class="col-md-12">
						<div class="col-md-2">
							<div class="label-input">
								<label for="taskname">Notificación:</label>
							</div>
						</div>
						<div class="form-group col-md-10 field-container">
							<div class="input-group" style="width: 100%;">
								<input type="text" id="notifyname" name="notifyname" value="{$NOTIFY_NAME}" class="form-control" disabled="disabled" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h2 class="pull-left">Eventos</h2>
			</header>
			<div class="main-box-body">
				<div class="row">
					<div class="col-md-12">
						<div class="input-group" style="width: 100%;">
							<textarea class="form-control" disabled="disabled" placeholder="" style="min-height: 25em;">
{if (!empty ($LOG_FILE_HANDLE))}
	{while (true)}
		{assign var='line' value=fgets ($LOG_FILE_HANDLE)}
		{if ($line !== false)}
								{$line}
		{else}
			{break}
		{/if}
	{/while}
{else}
								No hay eventos registrados para la notificación
{/if}
							</textarea>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}