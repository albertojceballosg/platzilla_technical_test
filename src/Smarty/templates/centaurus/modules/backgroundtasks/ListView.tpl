{strip}
<style type="text/css">
	.col-trigger {
		width: 15em;
	}
	.col-module {
		width: 10em;
	}
	.col-status {
		width: 10em;
	}
	.col-actions {
		width: 15em;
	}
	.action {
		display:    inline-block;
		list-style: none;
	}
	.action .btn {
		font-size:   14px;
		height:      27px;
		line-height: 27px;
		margin:      0 5px 0 0;
		padding:     0;
		text-align:  center;
		width:       27px;
	}
	.radio-group input[type="radio"] {
		display: inline-block;
		margin:         2px 5px 2px 2px;
		vertical-align: top;
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;">
					<i class="fa fa-cogs purple-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active">MOTOR DE TAREAS AUTOMATIZADAS</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Configuración de las tareas que se ejecutan automáticamente</td>
		</tr>
		</tbody>
	</table>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="main-box clearfix">
		<div class="tabs-wrapper">
			<ul class="nav nav-tabs">
{if (!$IS_INSTANCE)}
				<li{if (!$IS_INSTANCE)} class="active"{/if}><a href="#tab-system" data-toggle="tab" style="background-image: url('themes/centaurus/img/platzillaman.png'); background-repeat: no-repeat; background-position: 10px center; background-size: 30px 30px; padding-left: 45px;">Sistema</a></li>
{/if}
				<li{if ($IS_INSTANCE)} class="active"{/if}><a href="#tab-user" data-toggle="tab">{if (!$IS_INSTANCE)}Usuario{else}Tareas{/if}</a></li>
			</ul>
			<div class="tab-content">
				<div id="tab-system" class="tab-pane fade in{if (!$IS_INSTANCE)} active{/if}">
{include file='modules/backgroundtasks/ListViewDetail.tpl' SCOPE=BackgroundTask::SCOPE_SYSTEM DATA=$TASKS.SYSTEM}
				</div>
				<div id="tab-user" class="tab-pane fade in{if ($IS_INSTANCE)} active{/if}">
{include file='modules/backgroundtasks/ListViewDetail.tpl' SCOPE=BackgroundTask::SCOPE_USER DATA=$TASKS.USER}
				</div>
			</div>
		</div>
	</div>
</div>
{include file='modules/backgroundtasks/BackgroundTaskWizard.tpl'}
<script type="text/javascript" src="modules/backgroundtasks/backgroundtasks.js?v=1.1"></script>
{/strip}