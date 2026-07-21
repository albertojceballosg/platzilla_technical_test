{strip}
<style type="text/css">
{literal}
	.main-box > .main-box-body {
		padding-top: 20px;
	}
	.main-box > .main-box-body .input-group {
		margin-bottom: 5px;
	}
	.main-box > .main-box-body .input-group .form-control.body {
		float: none;
	}
	.input-group > .form-control[disabled] {
		background-color: transparent !important;
	}
	label {
		font-size: 1.11em;
		font-weight: 300;
	}
	.attachments-container {
		border: 1px solid #DDDDDD;
		list-style: none;
		padding: 6px 12px;
	}
	.attachments-container > .attachment {
		margin-bottom:  5px;
	}
{/literal}
</style>
{if (!$IS_AJAX_REQUEST)}
<div class="row">
	<div class="col-xs-12">
		<h1>
			<a href="index.php?module=emailmanager&action=EmailHistoryListView&parenttab=Settings{$QUERY_STRING}">Detalles del correo</a>
		</h1>
	</div>
</div>
{/if}
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
<div class="row">
	<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
		<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
</div>
{/if}
<div class="row">
	<div class="col-xs-12">
		<div class="main-box">
			<div class="main-box-body">
				<div class="row">
					<div class="col-xs-1">
						<label for="date">Fecha</label>
					</div>
					<div class="col-xs-11 input-group">
						<input type="text" id="date" value="{$EMAIL.createdon|date_format: 'd/m/Y h:i:s a'}" class="form-control" disabled="disabled" />
					</div>
				</div>
				<div class="row">
					<div class="col-xs-1">
						<label for="from">De</label>
					</div>
					<div class="col-xs-11 input-group">
						<input type="text" id="from" value="{$EMAIL.from}" class="form-control" disabled="disabled" />
					</div>
				</div>
				<div class="row">
					<div class="col-xs-1">
						<label for="to">Para</label>
					</div>
					<div class="col-xs-11 input-group">
						<input type="text" id="to" value="{$EMAIL.to}" class="form-control" disabled="disabled" />
					</div>
				</div>
				<div class="row">
					<div class="col-xs-1">
						<label for="subject">Asunto</label>
					</div>
					<div class="col-xs-11 input-group">
						<input type="text" id="subject" value="{$EMAIL.subject}" class="form-control" disabled="disabled" />
					</div>
				</div>
				<div class="row">
					<div class="col-xs-1">
						<label>Contenido</label>
					</div>
					<div class="col-xs-11 input-group">
						<div class="form-control body">{$EMAIL.body}</div>
					</div>
				</div>
{if (!empty ($EMAIL.attachments))}
				<div class="row">
					<div class="col-xs-1">
						<label for="attachments">Anexos</label>
					</div>
					<div class="col-xs-11 input-group">
						<ul class="attachments-container">
	{foreach $EMAIL.attachments as $attachment}
							<li class="attachment">
								<a href="index.php?module=emailmanager&action=DownloadEmailAttachment&Ajax=true&record={$EMAIL.emailid}&filename={$attachment.file|urlencode}" title="Descargar {$attachment.file}">{$attachment.file}</a>
							</li>
	{/foreach}
						</ul>
					</div>
				</div>
{/if}
				<div class="row">
					<div class="col-xs-1">
						<label for="status">Estado</label>
					</div>
					<div class="col-xs-11 input-group">
						<span class="label label-{if ($EMAIL.status == 'SENT')}success{else}danger{/if}">{$MOD[$EMAIL.status]}</span>
					</div>
				</div>
{if ($EMAIL.status == 'REJECTED')}
				<div class="row">
					<div class="col-xs-1">
						<label for="errormessage">Error</label>
					</div>
					<div class="col-xs-11 input-group">
						<div class="form-control body">{$EMAIL.errormessage}</div>
					</div>
				</div>
{/if}
			</div>
		</div>
	</div>
</div>
{/strip}