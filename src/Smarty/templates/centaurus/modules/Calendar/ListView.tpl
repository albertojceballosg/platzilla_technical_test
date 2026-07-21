{extends file='base/BaseListView.tpl'}
{assign var='__LIST_VIEW_ENTRIES_TEMPLATE_PATH' value='modules/Calendar/ListViewEntries.tpl'}
{block name='buttons'}
{strip}
<div class="row module-buttons">
	<div class="col-xs-12">
		<div class="pull-left">
			<h1>
				<a href="index.php?module={$MODULE}&action=ListView">Tareas</a>
{if (!empty ($TOTAL_SYNCS))}
				<i class="fa fa-exchange" style="font-size: 0.5em; line-height: 1.1em; margin-left: 0.75em;" title="Hay {$TOTAL_SYNCS} registro(s) compartido(s)"></i>
{/if}
			</h1>
		</div>
		<div class="pull-right">
{include file='customButtons.tpl'}
			<a href="index.php?module={$MODULE}&action=EditView&activity_mode=Events&return_module=Calendar&return_action=ListView&viewid={$VIEW->getId ()}" class="btn btn-info" style="margin-left: 5px;">
				<i class="fa fa-plus fa-lg" title="Crear Tarea" style="padding-right: 0.2em;"></i> Crear Tarea
			</a>
			<a href="index.php?module=Calendar&action=index" class="btn btn-warning" style="margin-left: 5px;">Ir al calendario</a>
		</div>
	</div>
</div>
{/strip}
{/block}