{strip}
	<div class="main-box-body clearfix" style="margin-top: 8px">
{include file='Home/TabsContents/TasksListView.tpl'
	MESSAGE=null
	ACTIVE_APPLICATIONS=null
	ALLOW_MASS_ACTIONS=false
	AVAILABLE_VIEWS=$AVAILABLE_TASKS_VIEWS
	IS_RELATED_TO_CALENDAR=false
	MODULE='Calendar'
	returnModule = $RETURN_MODULE
	VIEW=$TASKS_VIEW
	VIEW_DATA=$TASKS_VIEW_DATA
	VIEW_PERMISSIONS=$TASKS_VIEW_PERMISSIONS
}
	</div>
{/strip}