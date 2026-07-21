{strip}
    {for $kr=0 to $TOTAL_ROW}
        <tr>
            <td class="col-lg-8 col-md-8 col-sm-8">
                <div class="input-group text-left" style="width: 100%;">
                    <a href="index.php?module=key_result&parenttab=&action=DetailView&record={$KR['kr_selectedid'][$kr]}"
                       target="_blank" title="{$KR['kr_selected'][$kr]}">{$KR['kr_selected'][$kr]}</a>
                </div>
                <input type="hidden" name="app_okr[kr_selectedid][{$kr}]" value="{$KR['kr_selectedid'][$kr]}">
            </td>
            <td class="col-lg-2 col-md-2 col-sm-2">
                {$KR['kr_progress'][$kr]}
            </td>
            <td class="col-lg-2 col-md-2 col-sm-2 text-center">
                {$KR['goal_progress_pc'][$kr]}
            </td>
        </tr>
    {/for}
{/strip}