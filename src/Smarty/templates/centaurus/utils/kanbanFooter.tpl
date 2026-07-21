<span style="font-size: small"><i class="fa fa-calendar" aria-hidden="true"></i>&nbsp;{$dateStart}</span><br>
<span style="font-size: small"><i class="fa fa-calendar" aria-hidden="true"></i>&nbsp;{$dueDate}</span>
<div class="pull-right" style="font-size: small;padding: 0;top: 0; display: none">
    {if $progress eq 0}
            <i class="fa fa-chevron-right" aria-hidden="true"></i></i>&nbsp;{$progress}
    {elseif $progress lte 50}
            <i class="fa fa-chevron-right" aria-hidden="true"></i></i>&nbsp;{$progress}%
    {else}
            <i class="fa fa-chevron-right" aria-hidden="true"></i></i>&nbsp;{$progress}%
    {/if}
</div>