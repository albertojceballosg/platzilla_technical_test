{if (!empty ($AVAILABLE_FOLDERS))}
    {foreach $AVAILABLE_FOLDERS as $folder}
        <optgroup label="Carpeta: {$folder.foldername}">
            {foreach $folder.reports as $report}
                {if (!empty ($folder.reports))}
                    <option value="{$report.reportid}">{$report.reportname}</option>
                {/if}
            {/foreach}
        </optgroup>
    {/foreach}
{else}
    <optgroup label="Informes">
        <option value="">No hay informes disponibles</option>
    </optgroup>
{/if}