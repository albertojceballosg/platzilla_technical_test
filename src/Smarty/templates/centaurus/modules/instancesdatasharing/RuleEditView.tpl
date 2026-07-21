{strip}
{if (isset ($RULE))}
	{assign var='ruleId' value=$RULE->getId ()}
	{assign var='ruleDetails' value=$RULE->getDetails ()}
	{assign var='ruleModuleName' value=$RULE->getModuleName ()}
	{assign var='ruleName' value=$RULE->getName ()}
	{assign var='ruleStatus' value=$RULE->getStatus ()}
{else}
	{assign var='ruleId' value=null}
	{assign var='ruleDetails' value=null}
	{assign var='ruleModuleName' value=null}
	{assign var='ruleName' value=null}
	{assign var='ruleStatus' value=null}
{/if}
<link href="modules/instancesdatasharing/instancesdatasharing.css" rel="stylesheet" />
<form method="post" action="index.php" class="instance-data-sharing-element" onsubmit="return DataSharingUtils.validateRule (this);">
	<input type="hidden" name="module" value="instancesdatasharing" />
	<input type="hidden" name="action" value="SaveRule" />
{if (isset ($RECORD))}
	<input type="hidden" name="record" value="{$RECORD}" />
{/if}
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=instancesdatasharing&action=ListView&parenttab=Settings">Regla</a></h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=instancesdatasharing&action=ListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div id="basic-section" class="main-box">
		<header class="main-box-header clearfix">
			<h2 class="pull-left">Define la regla</h2>
		</header>
		<div class="main-box-body">
			<div class="row">
				<div class="form-group col-xs-12 col-md-6">
					<label for="rule-name">Nombre <span class="required">*</span></label>
					<input type="text" id="rule-name" name="rulename" value="{$ruleName}" maxlength="50" class="form-control" />
				</div>
				<div class="form-group col-xs-12 col-md-6">
					<label for="rule-status">Status <span class="required">*</span></label>
					<select id="rule-status" name="rulestatus" class="form-control">
						<option value=""{if (empty ($ruleStatus))} selected="selected"{/if}></option>
{if (!empty ($AVAILABLE_STATUSES))}
	{foreach $AVAILABLE_STATUSES as $status}
						<option value="{$status}"{if ($status == $ruleStatus)} selected="selected"{/if}>{$MOD[$status]}</option>
	{/foreach}
{/if}
					</select>
				</div>
			</div>
		</div>
	</div>
	<div id="details-section" class="main-box">
		<header class="main-box-header clearfix">
			<h2 class="pull-left">¿Qué ocurrirá?</h2>
		</header>
		<div class="main-box-body">
			<div class="row">
				<div class="form-group col-xs-12 col-md-6">
					<label for="module-name">Cuando se comparta un registro del módulo <span class="required">*</span></label>
					<select id="module-name" name="modulename" class="form-control" onchange="DataSharingUtils.setModuleName (this);">
						<option value=""{if (empty ($ruleModuleName))} selected="selected"{/if}></option>
{if (!empty ($AVAILABLE_MODULES))}
	{foreach $AVAILABLE_MODULES as $module}
						<option value="{$module->getName ()}"{if ($module->getName () == $ruleModuleName)} selected="selected"{/if}>{$module->getLabel ()}</option>
	{/foreach}
{/if}
					</select>
				</div>
			</div>
			<div id="rule-details" class="row"{if (empty ($ruleModuleName))} style="display: none;"{/if}>
				<div class="col-xs-12">
					<label for="main-record-options">Se creará al usuario a quien compartiste un registro de ese módulo con los siguientes características <span class="required">*</span></label>
					<div class="row">
						<label class="col-md-3 hidden-xs hidden-sm text-center">Campo</label>
						<label class="col-md-3 hidden-xs hidden-sm text-center">Acción</label>
						<label class="col-md-4 hidden-xs hidden-sm text-center">Valor</label>
						<label class="col-md-2 hidden-xs hidden-sm text-center">Sincronizar</label>
					</div>
					<div id="main-record-options">
{if (!empty ($AVAILABLE_FIELDS))}
	{foreach $AVAILABLE_FIELDS as $availableField}
		{if (!in_array ($availableField['uitype'], array (Field::UI_TYPE_CODE, Field::UI_TYPE_CREATED_TIME, Field::UI_TYPE_OWNER)))}
			{include file='modules/instancesdatasharing/RuleDetail.tpl' FIELD=$availableField}
		{/if}
	{/foreach}
{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/html" id="rule-field-template">
{include file='modules/instancesdatasharing/RuleDetail.tpl' FIELD=null}
</script>
<script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
{/strip}