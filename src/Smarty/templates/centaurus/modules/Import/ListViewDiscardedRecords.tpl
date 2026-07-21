<link rel="stylesheet" type="text/css" href="themes/softed/style.css"/>
<div id="" class="small" style=" max-width: 940px; padding-bottom: 25px; overflow: auto">
    <table class="searchUIBasic" style="width: 100%; border: 0!important;">
        <tbody>
        <tr>
            <td class="" align="left" colspan="{$LISTHEADER|count}">
                <div class="row">
                    <div class="col-md-8">
                        <h4 style="font-weight: bold">Importar {$MODULE_LABEL} - Registros Descartados</h4>
                    </div>
                    <div class="col-md-4">
                        <a  href="index.php?module=reportmanager&action=View&mode=discarded_records&for_module={$FOR_MODULE}&foruser={$FOR_USER}&pdf=yes&modulename=Import&Ajax=true"
                            target="_blank"
                            class="btn btn-info btn-circle btn-mini"
                            title="Imprimir PDF">
                            <i class="fa fa-print" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

            </td>
        </tr>
        <tr>
            <td class="leftFormBorder1" style="width: 100%; vertical-align: top;border: 0!important;">
                <table border="0" cellspacing="1" cellpadding="3" width="100%" class="lvt small"
                       style="width: 100%; margin-top: 15px">
                    <tbody>
                    <tr>{if $IS_PDF eq 'yes'}{else}{/if}
                        {foreach $LISTHEADER as $fiedName => $fieldLabel}
                            <td style="vertical-align: top; text-align: center;padding: 1px 0;{if $IS_PDF eq 'yes'}border:1px solid #ddd!important;background-color: #e8e8e8{else}border-top:1px solid #ddd!important;{/if}" class="{if $IS_PDF eq 'no'}lvtCol{/if}">
                                <div style="margin: 0 6px;">{$fieldLabel}</div>
                            </td>
                        {/foreach}
                        {foreach $LISTENTITY as $row}
                    <tr class="lvtColData">
                        {foreach $LISTHEADER as $fiedName => $fieldLabel}
                            <td style="vertical-align: top; text-align: left;border-left:1px solid #ddd!important;border-bottom:1px solid #ddd!important;">
                                <div style="margin: 0 6px;">{$row[$fiedName]}</div>
                            </td>
                        {/foreach}
                    </tr>
                    {/foreach}
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>