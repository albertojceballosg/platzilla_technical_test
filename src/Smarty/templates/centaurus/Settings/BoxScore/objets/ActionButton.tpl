{strip}
    <div class="btn-group">
        {*<a href="index.php?module=indicatorspanel&action=index" target="_blank"
           class="btn btn-default btn-sm btn-info" title="Panel indicadores"><i
                    class="fa fa-pencil-square-o" aria-hidden="true"></i></a> *}
       {if ($dataInstance eq 'MOTHER')}
        <button type="button" class="btn btn-sm btn-warning"
                data-record="{$boxScore->getId()}"
                data-bs-name="{$boxScore->getName()}"
                title="Compartir en instancia"
                onclick="BoxScoreInventoryUtils.clonarBoxScore  (this, '{$boxScore->getId()}', '{$idBoxScore}')">
            <i class="fa fa-share-alt" aria-hidden="true"></i>
        </button>
       {/if}
        {if ($dataInstance neq 'MOTHER') && $IS_INSTANCE}
            <button type="button" class="btn btn-sm btn-warning"
                    data-record="{$boxScore->getId()}"
                    data-is_editable="{if $boxScore->isEditable()}YES{else}NO{/if}"
                    data-instance="{$dataInstance}"
                    title="{if $boxScore->isEditable()} Bloquear edición {else} Habilitar edicción {/if}"
                    onclick="BoxScoreInventoryUtils.setEditableBoxScore  (this, '{$boxScore->getId()}', '{$idBoxScore}')">
                <i class="fa {if $boxScore->isEditable()}fa-unlock-alt {else} fa-lock {/if}" aria-hidden="true"></i>
            </button>
        {/if}
        <button type="button" class="btn btn-sm btn-default"
                title="{if $boxScore->getStatus() eq 'ENABLED'}desactivar{else}Activar{/if} indicador"
                data-record="{$boxScore->getId()}"
                data-bs-name="{$boxScore->getName()}"
                data-status="{$boxScore->getStatus()}"
                data-instance="{$dataInstance}"
                onclick="BoxScoreInventoryUtils.changeStatusBoxScore  (this, '{$boxScore->getId()}', '{$idBoxScore}')">
            <i class="fa {if $boxScore->getStatus() eq 'ENABLED'}fa-check-square-o{else}fa-square-o{/if}" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-sm btn-sm btn-danger"
                data-record="{$boxScore->getId()}"
                data-bs-name="{$boxScore->getName()}"
                data-instance="{$dataInstance}"
                title="Eliminar indicador"
                onclick="BoxScoreInventoryUtils.deleteBoxScore  (this, '{$boxScore->getId()}', '{$idBoxScore}')">
            <i class="fa fa-trash-o"></i>
        </button>
    </div>
{/strip}