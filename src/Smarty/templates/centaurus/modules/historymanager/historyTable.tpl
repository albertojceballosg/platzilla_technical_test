<table class="table table-striped table-hover history-record">
    <tbody>
    {if $HISTORICALRECORDS eq null}
        <div class="alert alert-info alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><strong><span aria-hidden="true">&times;</strong></button>
            <i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>&nbsp;
            No se encontró información de este registro.
        </div>
    {else}
        {foreach $HISTORICALRECORDS as $record}
            {if $record.fieldlabel neq 'Last Modified By'}
                {assign var="newLine" value=""}
                <tr>
                    <td class="text-center">
                        {if $record.uitype eq "1" || $record.uitype eq "2" || $record.uitype eq "7" ||
                        $record.uitype eq "9" || $record.uitype eq "17" || $record.uitype eq "57"}
                            <i class="fa fa-comment"></i>
                        {elseif $record.uitype eq "3" || $record.uitype eq "4"}
                            <i class="fa fa-asterisk"></i>
                        {elseif $record.uitype eq "5" || $record.uitype eq "23" || $record.uitype eq "70"}
                            <i class="fa fa-calendar"></i>
                        {elseif $record.uitype eq "6"}
                            <i class="fa fa-calendar-times-o"></i>
                        {elseif $record.uitype eq "8"}
                            <i class="fa fa-clone"></i>
                        {elseif $record.uitype eq "10"}
                            <i class="fa fa-book"></i>
                        {elseif $record.uitype eq "11"}
                            <i class="fa fa-phone-square"></i>
                        {elseif $record.uitype eq "12" || $record.uitype eq "13" || $record.uitype eq "25"}
                            <i class="fa fa-envelope"></i>
                        {elseif $record.uitype eq "15" || $record.uitype eq "16" || $record.uitype eq "52" || $record.uitype eq "53"}
                            <i class="fa fa-angle-double-down"></i>
                        {elseif $record.uitype eq "19" || $record.uitype eq "20" || $record.uitype eq "21" ||
                        $record.uitype eq "22" || $record.uitype eq "24" || $record.uitype eq "33"}
                            <i class="fa fa-align-justify"></i>
                            {assign var="newLine" value="<br />"}
                        {elseif $record.uitype eq "26"}
                            <i class="fa fa-folder"></i>
                        {elseif $record.uitype eq "27"}
                            <i class="fa fa-file-archive-o"></i>
                        {elseif $record.uitype eq "28"}
                            <i class="fa fa-file-code-o"></i>
                        {elseif $record.uitype eq "30"}
                            <i class="fa fa-caret-square-o-down"></i>
                        {elseif $record.uitype eq "51"}
                            <i class="fa fa-window-restore"></i>
                        {elseif $record.uitype eq "55" || $record.uitype eq "255"}
                            <i class="fa fa-address-card"></i>
                        {elseif $record.uitype eq "56"}
                            <i class="fa fa-check-square"></i>
                        {/if}
                    </td>
                    <td>
                        {if $record.modifiedon neq 0}
                            <a>{$record.first_name}&nbsp;{$record.last_name}</a>&nbsp;modificó el campo
                                                                                &nbsp;<b>{$record.fieldlabel|@getTranslatedString : $MODULE}</b>&nbsp;{$newLine}De:
                            &nbsp;<span class="text-primary" title="{$record.oldvalue}"> {$record.oldvalue}</span>&nbsp;{$newLine}A:
                            &nbsp;<span class="text-success" title="{$record.newvalue}">{$record.newvalue}</span>
                        {else}
                            <a>{$record.first_name}&nbsp;{$record.last_name}</a>&nbsp;{$newLine}Creó el campo:
                            &nbsp;<b>{$record.fieldlabel|@getTranslatedString : $MODULE}</b>&nbsp;{$newLine}Con el valor de:
                            <span class="text-success" title="{$record.newvalue}" >{$record.newvalue}</span>
                        {/if}
                    </td>
                    <td>
                        <i class="fa fa-clock-o"></i>&nbsp;
                        {$record.date|date_format:"%d/%m/%Y"}
                    </td>
                </tr>
            {/if}
        {/foreach}
    {/if}
    </tbody>