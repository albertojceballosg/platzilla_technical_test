{assign var="totalButtons" value=$CUSTOM_BUTTONS|@count}
{if $totalButtons lt 3}
    {foreach key=keyCB item=CB from=$CUSTOM_BUTTONS}
        {if ($CB.action  eq $action) || (($CB.module eq $moduleRequest) && ($action eq 'index'))}
            {if $CB.type eq 'js'}
                <input class="btn btn-{$CB.style}" type="button" value="{$CB.label}" onclick="{eval var=$CB.onclick}"
                       style=" font-size: 15px!important;"
                       title="{$CB.description}"/>
            {else}
                <a href="{eval var=$CB.link}" style=" font-size: 15px!important;" class="btn btn-{$CB.style}"{if ($CB.runinnewwindow)} target="_blank"{/if}
                   title="{$CB.description}">{$CB.label}</a>
            {/if}
        {/if}
    {/foreach}
{else}
    <div class="btn-group">
        <button type="button" class="btn btn-primary dropdown-toggle"
                data-toggle="dropdown"><i class="fa fa-plus fa-lg" title="Acciones" style="padding-right: 0.2em;"></i>
            Acciones&nbsp;<span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            {foreach key=keyCB item=CB from=$CUSTOM_BUTTONS}
                {if ($CB.action  eq $action) || (($CB.module eq $moduleRequest) && ($action eq 'index'))}
                    {if $CB.type eq 'js'}
                        <li>
                            <a style="cursor: pointer" title="{$CB.description}"
                               onclick="{eval var=$CB.onclick}">{$CB.label}</a>
                        </li>
                    {else}
                        <li>
                            <a href="{eval var=$CB.link}" {if ($CB.runinnewwindow)} target="_blank"{/if}
                               title="{$CB.description}">{$CB.label}</a>
                        </li>
                    {/if}
                {/if}
            {/foreach}
        </ul>
    </div>
{/if}