{extends file='base/BaseListView.tpl'}
{assign var='__LIST_VIEW_ENTRIES_TEMPLATE_PATH' value='Home/TabsContents/TasksListViewEntries.tpl'}

{block name='buttons'}{/block}
{block name='templates'}
    {include file='modal/RegisterActivityModal.tpl'}
{/block}