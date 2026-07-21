{if (!empty ($PROFILES_USE))}
    <label for="profile-info" class="col-md-3 control-label">&nbsp;</label>
    <div class="well col-md-7">
        {foreach $PROFILES_USE as $profile}
        <p class="text-justify hide profile-help" id="{$profile['code']}" >{$profile['description']}</p>
        {/foreach}
    </div>
{/if}