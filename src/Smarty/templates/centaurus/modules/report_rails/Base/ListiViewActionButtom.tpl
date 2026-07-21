{if ($IS_ADMIN)}
    <ul class="actions" style="float: right">
        <li class="action">
            <a href="{$urlEdit}"
               class="btn btn-primary"
               title="Editar"><i class="fa fa-pencil"></i></a></li>
        <li class="action">
            <form method="post" action="index.php"
                  onsubmit="return changeStatusRow ('{$rowName}');">
                <input type="hidden" name="module" value="report_rails">
                <input type="hidden" name="action" value="AjaxRailsUtils">
                <input type="hidden" name="record" value="{$recordId}">
                <input type="hidden" name="function" value="{$functionUpdate}">
                <input type="hidden" name="row_status" value="{$rowStatus}">
                {block name="extraHiddenFields"}{/block}
                <input type="hidden" name="Ajax" value="true">
                <button class="btn  btn-default" type="submit"
                        title="{if $rowStatus eq $activeData}Suspender{else}Activar{/if}">
                    <i class="fa {if $rowStatus eq $activeData}fa-check-square-o{else}fa-square-o{/if}"
                       aria-hidden="true">
                    </i>
                </button>
            </form>
        </li>
        {block name="share_report"}{/block}
        <li class="action">
            <form method="post" action="index.php"
                  onsubmit="return deleteRow ('{$rowName}');">
                <input type="hidden" name="module" value="report_rails">
                <input type="hidden" name="action" value="AjaxRailsUtils">
                <input type="hidden" name="function" value="{$functionDalete}">
                <input type="hidden" name="record" value="{$recordId}">
                {block name="extraHiddenFields"}{/block}
                <input type="hidden" name="Ajax" value="true">
                <button class="btn btn-danger" type="submit" title="Eliminar">
                    <i class="fa fa-trash-o"></i></button>
            </form>
        </li>
    </ul>
{/if}