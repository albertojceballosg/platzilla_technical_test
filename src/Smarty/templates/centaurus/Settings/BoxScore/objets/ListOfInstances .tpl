{strip}
    {if $instancesList neq NULL}
        <ul class="inline instance-list">
            {foreach $instancesList as $item}
                <li>{$item['code']}:&nbsp;{$item['name']}</li>
            {/foreach}
        </ul>
    {else}
    <pre>No ha sido compartido</pre>
    {/if}
{/strip}