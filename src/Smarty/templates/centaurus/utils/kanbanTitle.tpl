<figure class="pull-right"
        style="border-radius: 50%; height: 40px; overflow: hidden; width: 40px;top: 0">
        <img class="img-responsive img-circle"
             alt="{$assignedUser}"
             title="{$assignedUser}"
             src="{$userAvatar}">
</figure>
<span class="kanban-task-title"   style="font-size: small" rel="{if isset($relatedModule)}{$relatedModule}{else}NA{/if}">&nbsp;{$title}</span><br>
