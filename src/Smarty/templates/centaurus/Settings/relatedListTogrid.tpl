   <div id="list-{$MODULE_NAME}"  class="btn-group   pull-left module-list" style="margin-top: 2px">
        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" title="Activar línea secundaria con lista relacionada." style="padding: 0px 5px;">
            <i class="fa  fa-link " style="padding: 0.4em 0.4em;"></i><span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu" style="left: -120px;">
            {foreach from=$RELATED_LIST key=myId item=list}
            <li><a href="#" class="list-select" data-record="{$myId}@{$list}@{$MODULE_NAME}" >{$list}</a>
            {/foreach}
            </li>
        </ul>&nbsp;
       <a style="margin-top: 0.2em" id="list-name-{$MODULE_NAME}" title="Lista Seleccionada."></a>&nbsp;&nbsp;
   </div>
