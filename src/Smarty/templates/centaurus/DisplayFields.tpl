{assign var="fromlink" value=""}
{foreach key=label item=subdata from=$data}
	{foreach key=mainlabel item=maindata from=$subdata name=fields}
		{include file='EditViewUI.tpl'}
	{/foreach}
{/foreach}