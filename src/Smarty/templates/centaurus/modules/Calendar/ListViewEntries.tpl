{extends file='base/BaseListViewEntries.tpl'}
{block name="tbody-item"}

{foreach $viewColumns as $index => $viewColumn}
	{if $viewColumn.fieldname eq 'modulename' && !$HAS_RELATED}
    	{continue}
	{/if}
	<td {if $record['isNew']}style="font-weight: bold"{/if} >
        {if ((empty ($VIEW_DATA.entityidentifier)) && ($index === 0)) || ($viewColumn.fieldname == $VIEW_DATA.entityidentifier)}
			<a href="index.php?module={$MODULE}&action=DetailView&record={$record.crmid}">{$record[$viewColumn.fieldname]}</a>
        {elseif (in_array ($viewColumn.fieldname, array ('eventstatus', 'activitytype')))}
            {$record[$viewColumn.fieldname]|@getTranslatedString: $MODULE}
        {elseif (in_array ($viewColumn.fieldname, array ('progress')))}
			<div class="text-center">{intval ($record[$viewColumn.fieldname])} %</div>
			<input type="range" value="{$record[$viewColumn.fieldname]}" class="progress" min="1" max="100"
				   placeholder="" disabled="disabled"/>

        {elseif (($record['related_to'] neq NULL) && $viewColumn.fieldname eq 'related_to')}
			<a href="index.php?module={$record['tab_name']}&action=DetailView&record={$record.related_id}" target="_blank">{$record['related_to']}</a>

        {elseif ($viewColumn.fieldname eq 'assigned_user_id')}
			<figure class="center-block" style="border-radius: 50%; height: 40px; overflow: hidden; width: 40px;"><img class="img-responsive img-circle" alt="Platzi el guapo" title="{$record['assigned_user_id']}o" src="{$record['useravatar']}"></figure>
        {elseif ((($record['reports'] neq NULL) && ($record['reports'] gte 1) && ($record['reports'] neq '0'))  && ($record['related_id'] neq NULL) && ($viewColumn.fieldname eq 'reports') && ($record['related_to'] neq NULL))}
			<a
					data-width="950" data-toggle="lightbox" data-parent="" data-gallery="remoteload" data-title="Reportes sobre actividad:"
					title="Reportes y feedbacks"
					href="index.php?module=grid_view&action=GridViewAjaxUtils&record={$record['related_id']}&formodule={$record['tab_name']}&boxtype=REPORT_ACTIVITY&function=ITERATIONS&Ajax=true" >{$record['reports']}
			</a>
        {elseif ((($record['feedbacks'] neq NULL) && ($record['feedbacks'] gte 1) && ($record['feedbacks'] neq '0'))  && ($record['related_id'] neq NULL) && ($viewColumn.fieldname eq 'feedbacks') && ($record['related_to'] neq NULL))}
			<a
					data-width="950" data-toggle="lightbox" data-parent="" data-gallery="remoteload" data-title="Reportes sobre actividad:"
					title="Reportes y feedbacks"
					href="index.php?module=grid_view&action=GridViewAjaxUtils&record={$record['related_id']}&formodule={$record['tab_name']}&boxtype=REPORT_ACTIVITY&function=ITERATIONS&Ajax=true" >{$record['feedbacks']}
			</a>
        {else}
            {$record[$viewColumn.fieldname]}
        {/if}
	</td>
{/foreach}
{/block}