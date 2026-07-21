{strip}
{if ($taskTrigger == BackgroundTask::TRIGGER_DAILY_SCHEDULE)}
	{assign var='frequencyAsTime' value=gmdate('H:i:s', $taskFrequency)}
{else}
	{assign var='frequencyAsTime' value=null}
{/if}
<div class="row">
	<div class="form-group col-xs-12 col-md-6">
		<label for="modulename">Módulo <span class="required">*</span></label>
		<select id="modulename" name="modulename" class="form-control modulename" onchange="BackgroundTasksUtils.setModuleName (this);">
			<option value=""></option>
			<option value="--NONE--"{if ($taskScope == BackgroundTask::SCOPE_SYSTEM) && (empty ($taskModuleName))} selected="selected"  {/if} data-scope="BackgroundTask::SCOPE_SYSTEM"{if ($taskScope != BackgroundTask::SCOPE_SYSTEM)} style="display: none;"{/if}>(Definido por las acciones)</option>
{foreach $AVAILABLE_MODULES as $module}
			<option value="{$module.name}"{if ($taskModuleName == $module.name)} selected="selected" {$LIST_MODULES[$module.name] = {$module.tablabel}|cat:'('|cat:{$module.name}|cat:')'}{/if}>{$module.tablabel} ({$module.name})</option>
{/foreach}
		</select>
	</div>
	<div class="form-group col-xs-12 col-md-6">
		<label for="trigger">Ejecutado por <span class="required">*</span></label>
		<select id="trigger" name="trigger" class="form-control trigger" onchange="BackgroundTasksUtils.setTrigger (this);">
			<option value=""></option>
{foreach $AVAILABLE_TRIGGERS as $trigger}
			<option value="{$trigger}"{if ($taskTrigger == $trigger)} selected="selected"{/if}>{$MOD[$trigger]}</option>
{/foreach}
		</select>
	</div>
</div>
<div class="row event-data"{if ($taskTrigger != BackgroundTask::TRIGGER_EVENT)} style="display: none;"{/if}>
	<div class="form-group col-xs-12 col-md-6">
		<label for="event">Evento <span class="required">*</span></label>
		<select id="event" name="event" class="form-control event">
			<option value=""></option>
{foreach $AVAILABLE_EVENTS as $eventName => $eventData}
			<option value="{$eventName}" data-scope="{$eventData.scope}"{if ($taskEvent == $eventName)} selected="selected"{/if}{if ($taskScope != $eventData.scope)} style="display: none;"{/if}>{$eventData.label}</option>
{/foreach}
		</select>
	</div>
	<div class="form-group col-xs-12 col-md-6">
		<label for="eventinstant">Instante <span class="required">*</span></label>
		<select id="eventinstant" name="eventinstant" class="form-control eventinstant">
			<option value=""></option>
{foreach $AVAILABLE_EVENT_INSTANTS as $eventinstant}
			<option value="{$eventinstant}"{if ($taskEventInstant == $eventinstant)} selected="selected"{/if}>{$MOD[$eventinstant]}</option>
{/foreach}
		</select>
	</div>
</div>
<div class="row schedule-data"{if (!in_array ($taskTrigger, array (BackgroundTask::TRIGGER_DAILY_SCHEDULE, BackgroundTask::TRIGGER_TIMED_SCHEDULE)))} style="display: none;"{/if}>
	<div class="form-group col-xs-12 col-md-6 daily-schedule-data" style="width: 15em;{if ($taskTrigger != BackgroundTask::TRIGGER_DAILY_SCHEDULE)} display: none;{/if}">
		<label for="daily-frequency">Hora <span class="required">*</span></label>
		<div class="input-group bootstrap-timepicker timepicker">
			<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
			<input type="text" id="daily-frequency" name="dailyfrequency" value="{$frequencyAsTime}" class="form-control time" readonly="readonly" />
		</div>
	</div>
	<div class="form-group col-xs-12 col-md-6 timed-schedule-data" style="width: 15em;{if ($taskTrigger != BackgroundTask::TRIGGER_TIMED_SCHEDULE)} display: none;{/if}">
		<label for="timed-frequency">Frecuencia (segundos) <span class="required">*</span></label>
		<input type="number" id="timed-frequency" name="timedfrequency" value="{$taskFrequency}" class="form-control frequency" min="0" />
	</div>
</div>
{/strip}