



<ul class="pagination pull-right" >
	{* primera *}
	<li class="{if $INSTANCIAS.paginaactual eq 1}disabled{/if}"><a href="{if $INSTANCIAS.paginaactual eq 1}javascript:void(0);{else}javascript:;{/if}" {if $INSTANCIAS.paginaactual neq 1}onclick="getListViewInstancias(1);" {/if}><i class="fa fa-step-backward"></i></a></li>
	{* anterior *}
	<li class="{if $INSTANCIAS.paginaactual eq 1}disabled{/if}"><a href="{if $INSTANCIAS.paginaactual eq 1}javascript:void(0);{else}javascript:;{/if}" {if $INSTANCIAS.paginaactual neq 1}onclick="getListViewInstancias({$INSTANCIAS.paginaanterior});"{/if}><i class="fa fa-chevron-left"></i></a></li>

	<li>
		<span class="pagination-search"> &nbsp;&nbsp;&nbsp;&nbsp;{$INSTANCIAS.paginaactual} de {$INSTANCIAS.totalpaginas}</span>
	</li>
	{* siguiente *}
	<li class="{if $INSTANCIAS.paginaactual eq $INSTANCIAS.totalpaginas}disabled{/if}">
		<a href="{if $INSTANCIAS.paginaactual neq $INSTANCIAS.totalpaginas}javascript:void(0);{else}javascript:;{/if}" {if $INSTANCIAS.paginaactual neq $INSTANCIAS.totalpaginas}onclick="getListViewInstancias({$INSTANCIAS.paginasiguiente});"{/if} alt="Siguiente" title="Siguiente"><i class="fa fa-chevron-right"></i>
		</a>
	</li>
	{* ultima *}
	<li class="{if $INSTANCIAS.paginaactual eq $INSTANCIAS.totalpaginas}disabled{/if}">
		<a href="{if $INSTANCIAS.paginaactual neq $INSTANCIAS.totalpaginas}javascript:void(0);{else}javascript:;{/if}" {if $INSTANCIAS.paginaactual neq $INSTANCIAS.totalpaginas}onclick="getListViewInstancias({$INSTANCIAS.totalpaginas});"{/if} alt="Última" title="Última"><i class="fa fa-step-forward"></i></a>
	</li>
</ul>