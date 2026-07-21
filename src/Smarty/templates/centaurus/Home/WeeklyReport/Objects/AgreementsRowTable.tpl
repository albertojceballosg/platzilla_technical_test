{foreach $AGREEMENTS as $agreement}
    {if $agreement->getAgreementStatus() neq 'ACTIVE'}{continue}{/if}
    <tr id="tr-row-19070" data-row-id="19070" class="tabla-field-row">
        <td class="text-center" width="7%" style="vertical-align: top">
            <span style="">{$agreement->getSequence()}</span>
        </td>
        <td class="text-left" width="23%" style="vertical-align: top">
            <span style="">{$agreement->getAgreement()}</span>
        </td>
        <td class="text-justify" width="30%" style="vertical-align: top">
            <span style="">{str_replace('<br />', "", str_replace('<br>', "", $agreement->getDescription()))}</span>
        </td>
        <td class="text-left" width="20%" style="vertical-align: top">
            {*$agreement->getUsersInvolved ()|var_dump*}
            {if $agreement->getUsersInvolved () neq NULL}
                <ul>
                    {foreach $agreement->getUsersInvolved() as $userInvolved}
                        <li>{$userInvolved['username']}</li>
                    {/foreach}
                </ul>
            {/if}
        </td>
        <td class="text-left" width="20%" style="vertical-align: top">
            {if  $agreement->getTabName () neq NULL}
                {if $IS_INSTANCE}
                    <a href="index.php?module={$agreement->getTabName ()}&parenttab=&action=DetailView&record={$agreement->getExecution ()}"
                       target="_blank"
                       title="Reportes y feedbacks">{$agreement->getRelatedAgreement()}
                    </a>
                {else}
                    <a data-width="950" data-toggle="lightbox" data-parent="" data-gallery="remoteload"
                       data-title="{$agreement->getTabName ()|module_label: $ADB}: {$agreement->getRelatedAgreement()}"
                       href="index.php?module=Home&action=AjaxHomeUtils&record_id={$agreement->getExecution ()}&flmodule={$agreement->getTabName ()}&function=SHOW_AGREEMENT&code={$codeInstance}&Ajax=true"
                       title="Reportes y feedbacks">{$agreement->getRelatedAgreement()}</a>
                {/if}
            {/if}
        </td>
    </tr>
{/foreach}