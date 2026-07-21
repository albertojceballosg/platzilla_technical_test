<!-- Resumen del Curso -->
<div class="row">
    <div class="col-lg-12">
        <div class="main-box">
            <header class="main-box-header clearfix">
                <h2>{$FIELDS.titulo}</h2>
            </header>
            <div class="main-box-body clearfix">
                <div class="image-curso" style="background-image:url('{$FIELDS.image}');width:auto;height:400px;background-size: 930px;background-repeat: no-repeat;margin-bottom: 20px;"></div>
                <p class="lead">{$FIELDS.descripcion}</p>

                <div class="progress progress-striped active">
                    <div class="progress-bar" role="progressbar" aria-valuenow="{$PROG}" aria-valuemin="0" aria-valuemax="100" style="width: {$PROG}%">
                        <span class="sr-only">{$PROG} Complete</span>
                    </div>
                </div>
                <h2 class="pull-left">Lecciones</h2>
                <div class="pull-right">
                    <a data-toggle="modal" href="#curso-completo" class="btn btn-primary mrg-b-lg" >Empezar Curso</a>
                </div>

                <table class="table table-products table-hover">
                    {$n=1}
                    {foreach key=num item=leccion from=$LECCIONES}
                        <tr>

                            <td>{$n}</td>
                            <td align="left">{$leccion.titulo}</td>
                            <td aling="left">{$leccion.descripcion}</td>
                            <td class="lec-link">
                                {if $leccion.url_video}
                                    <a href="#curso-completo" data-toggle="modal"  onclick="irLeccion({$num});">
                                        <span class="label label-success" style="font-size: 12px;">Ver</a>
                                {elseif $leccion.materiales && $leccion.ext_arch eq 'pdf'}
                                    <a href="#curso-completo" data-toggle="modal"  onclick="irLeccion({$num});">
                                        <span class="label label-success" style="font-size: 12px;">Ver</a>
                                {else}
                                    <a href="#curso-completo" data-toggle="modal"  onclick="irLeccion({$num});">
                                        <span class="label label-success" style="font-size: 12px;">Ver</a>
                                {/if}
                                <a></a>
                            </td>
                            {$n=$n+1}
                        </tr>
                        {if $leccion.eval}
                            {foreach $leccion.eval as $evalu}

                                <tr>
                                    <td>{$n}</td>
                                    <td align="left">{$evalu.titulo}</td>
                                    <td aling="left">{$evalu.descripcion}</td>
                                    <td class="lec-link">
                                        {if $leccion.test eq '0'}
                                            <a href="#curso-completo" data-toggle="modal"  onclick="realizarTest({$num});">
                                                <span class="label label-success" style="font-size: 12px; background-color: #8A2624">Test</a>
                                        {elseif $leccion.test eq '1'}
                                                <a href="#curso-completo" data-toggle="modal"  onclick="realizarTest({$num});">
                                                    <span class="label label-success" style="font-size: 12px; background-color: #8A2624">Repetir Test</a>
                                            {elseif $leccion.test eq 'Aplazado'}
                                                {$leccion.test}
                                            {elseif $leccion.test eq 'Aprobado'}
                                                {$leccion.test}
                                        {/if}
                                    </td>
                                </tr>
                                {$n=$n+1}
                            {/foreach}


                        {/if}
                    {/foreach}
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    {literal}
    @media (min-width: 992px){.modal-lg{width:100% !important; height: 80% }}
    {/literal}
</style>
<!-- Modal del curso completo -->
<div class="modal fade" tabindex="-1" id="curso-completo" role="dialog" aria-labelledby="gridSystemModalLabel">
    <div class="modal fade in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Formaci&oacute;n - <small>{$FIELDS.titulo}</small></h4>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="main-box clearfix">
                                <header class="main-box-header clearfix">
                                </header>
                                <div class="main-box-body clearfix">
                                    <div class="panel-group accordion" id="accordion">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                                        Lecciones
                                                    </a>
                                                </h4>
                                            </div>
                                            <div id="collapseOne" class="panel-collapse collapse in" style="">
                                                <div class="panel-body">

                                                    <ol class="dl-list">
                                                        {foreach key=num item=leccion from=$LECCIONES}
                                                            {if $leccion.url_video}
                                                                {$na=$n}
                                                                {$n=$n+1}
                                                                <li class="dd-item dd-item-list" data-id="{$leccion.formacion_leccionesid}">
                                                                    <div class="dd-handle-list">
                                                                        <i class="fa fa-play"></i>
                                                                    </div>
                                                                    <div class="dd-handle">
                                                                        <a href="#" data-toggle="tooltip" data-placement="right" title="Ir a la lección" onclick="irLeccion('{$num}')">{$leccion.titulo}</a>
                                                                    </div>
                                                                </li>
                                                            {elseif $leccion.materiales && $leccion.ext_arch eq 'pdf'}
                                                                {$n=$n+1}
                                                                <li class="dd-item dd-item-list" data-id="{$leccion.formacion_leccionesid}">
                                                                    <div class="dd-handle-list">
                                                                        <i class="fa fa-file-pdf-o"></i>
                                                                    </div>
                                                                    <div class="dd-handle">
                                                                        <a href="#" onclick="irLeccion('{$num}')">{$leccion.titulo}</a>
                                                                    </div>
                                                                </li>
                                                            {else}
                                                                {$n=$n+1}
                                                                <li class="dd-item dd-item-list" data-id="{$leccion.formacion_leccionesid}">
                                                                    <div class="dd-handle-list">
                                                                        <i class="fa fa-file-text-o"></i>
                                                                    </div>
                                                                    <div class="dd-handle">
                                                                        <a href="#" onclick="irLeccion('{$num}')">{$leccion.titulo}</a>
                                                                    </div>
                                                                </li>
                                                            {/if}
                                                            {if  $leccion.eval}
                                                                {foreach $leccion.eval as $evalu}
                                                                    {$n=$n+1}
                                                                    <li class="dd-item dd-item-list" data-id="{$leccion.formacion_pruebasid}">
                                                                        <div class="dd-handle-list">
                                                                            <i class="fa fa-question"></i>
                                                                        </div>
                                                                        <div class="dd-handle">
                                                                            {if $leccion.test eq '0' || $leccion.test eq '1'}
                                                                                <a href="#" onclick="realizarTest({$num});">{$evalu.titulo}</a>
                                                                            {else}
                                                                                <p>{$evalu.titulo}  <i style="color: #1a8849">({$leccion.test})</i></p>
                                                                            {/if}

                                                                        </div>
                                                                    </li>
                                                                {/foreach}
                                                            {/if}
                                                        {/foreach}
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="main-box clearfix">
                                <header class="main-box-header clearfix" style="text-align: center;">
                                    <h3>{$FIELDS.titulo}</h3>
                                </header>
                                <div class="main-box-body clearfix">

                                </div>
                            </div>
                            {for $lec=0 to $num}
                                <div id="{$lec}" {if $lec!= 0} style="display: none;" {/if}>
                                    <div class="main-box clearfix">
                                        <header class="main-box-header clearfix">
                                        </header>
                                        <div class="main-box-body clearfix">
                                            {if $LECCIONES[$lec].url_video}
                                                {if strpos($LECCIONES[$lec].url_video,'watch?v=')}
                                                    <div id="ma_video" >
                                                        <iframe class="embed-responsive-item" width="90%" height="400" src="{$LECCIONES[$lec].url_video|replace:'watch?v=':'embed/'}" allowfullscreen></iframe>
                                                    </div>

                                                {else}
                                                    <div id="ma_video" class="embed-responsive embed-responsive-16by9 ">
                                                        <iframe class="embed-responsive-item" width="90%" height="400" src="{$LECCIONES[$lec].url_video}" allowfullscreen></iframe>
                                                    </div>
                                                {/if}

                                            {elseif $LECCIONES[$lec].materiales && $LECCIONES[$lec].ext_arch eq 'pdf' && $LECCIONES[$lec].url_pagina eq '' }
                                                <div id="ma_pdf" class="mat_pdf ">
                                                    <embed src="{$LECCIONES[$lec].material}" width="90%" height="400"></embed>
                                                </div>
                                            {elseif $LECCIONES[$lec].url_pagina}
                                                {if strpos($LECCIONES[$lec].url_pagina,'http://')}
                                                    <div id="ma_url" class="mat_pag ">
                                                        <embed src="{$LECCIONES[$lec].url_pagina}" width="90%" height="800"></embed>
                                                    </div>
                                                    {else}

                                                        <div id="ma_url" class="mat_pag ">
                                                            <embed src="http://{$LECCIONES[$lec].url_pagina}" width="90%" height="800"></embed>
                                                        </div>
                                                {/if}
                                            {/if}
                                        </div>
                                        <footer class="main-box-body clearfix">
                                            {if $LECCIONES[$lec].eval}
                                                {foreach $LECCIONES[$lec].eval as $evalu}
                                                    <div  data-id="{$leccion.formacion_pruebasid}">
                                                        {if $LECCIONES[$lec].test eq '0'}
                                                            <div style="text-align: right;">
                                                                <button type="button" class="btn btn-danger" onClick="realizarTest('{$lec}')">Realizar Test</button>
                                                            </div>
                                                            {elseif $LECCIONES[$lec].test eq '1'}
                                                                <div style="text-align: right;">
                                                                    <button type="button" class="btn btn-danger" onClick="realizarTest('{$lec}')">Repetir Test</button>
                                                                </div>
                                                        {/if}

                                                    </div>
                                                {/foreach}

                                            {/if}
                                            <h1 style="text-align: center">{$LECCIONES[$lec].titulo}</h1>
                                            <hr>
                                            {if $LECCIONES[$lec].introduccion}
                                                <h2 style="text-align: center">Introducción</h2>
                                                <p style="text-align: justify">{$LECCIONES[$lec].introduccion|replace:'\r\n':'<br>'}</p>
                                                <hr>
                                            {/if}
                                            {if $LECCIONES[$lec].contenido}
                                                <h2 style="text-align: center">Contenido</h2>
                                                <p style="text-align: justify">{$LECCIONES[$lec].contenido|replace:'\r\n':'<br>'}</p>
                                                <hr>
                                            {/if}
                                            {if $LECCIONES[$lec].actividades}
                                                <h2 style="text-align: center">Actividades</h2>
                                                <p style="text-align: justify">{$LECCIONES[$lec].actividades|replace:'\r\n':'<br>'}</p>
                                                <hr>
                                            {/if}
                                            {if $LECCIONES[$lec].url_video && $LECCIONES[$lec].url_pagina && $LECCIONES[$lec].materiales}
                                                <h2 style="text-align: center">Material Complementario</h2>
                                                {if $LECCIONES[$lec].url_pagina}
                                                    {if strpos($LECCIONES[$lec].url_pagina,'http://')}
                                                        <a href="{$lECCIONES[$lec].url_pagina}">{$LECCIONES[$lec].url_pagina}</a>
                                                        {else}
                                                            <a href="http://{$LECCIONES[$lec].url_pagina}">{$LECCIONES[$lec].url_pagina}</a>
                                                    {/if}
                                                    <br>
                                                    <a href="{$LECCIONES[$lec].material}">{$LECCIONES[$lec].titulo}</a>
                                                    <hr>
                                                {/if}

                                            {else}
                                                {if $LECCIONES[$lec].url_pagina && $LECCIONES[$lec].materiales}
                                                    <h2 style="text-align: center">Material Complementario</h2>
                                                    <a href="{$LECCIONES[$lec].material}">{$LECCIONES[$lec].titulo}</a>
                                                    <hr>
                                                {/if}
                                            {/if}
                                            <div >
                                                {if {$lec}!=0}
                                                    <button type="button" class="btn btn-primary" style="text-align: right" onClick="irAnterior('{$lec}')">Anterior</button>
                                                {/if}
                                                {if {$lec}!={$num}}
                                                    <button type="button" class="btn btn-primary" style="text-align: left" onClick="irSiguiente('{$lec}')">Siguiente</button>
                                                {/if}
                                            </div>
                                        </footer>
                                    </div>
                                </div>
                                <div id="ma_test_{$lec}" style="display: none;" >

                                    {if $LECCIONES[$lec].eval}


                                        <div class="main-box clearfix">
                                            <header class="main-box-header clearfix" style="text-align: center;">
                                                <div  style="text-align: center">
                                                    <h1>Prueba: {$LECCIONES[$lec].titulo}</h1><br><hr>
                                                </div>
                                                <div style="text-align: justify">
                                                    <h2>Prueba: {$LECCIONES[$lec].descripcion}</h2>
                                                    <hr>
                                                </div>
                                            </header>
                                            <div class="main-box-body clearfix">
                                                {foreach $LECCIONES[$lec].eval as $evalu}
                                                    {if $lec eq 0}
                                                        <form></form>
                                                    {/if}

                                                    <form role="form" name="eval_{$lec}" id="eval_{$lec}" >
                                                        <div class="form-group">
                                                            <input type="hidden" id="formacion_pruebasid" name="formacion_pruebasid" value="{$evalu.formacion_pruebasid}">
                                                            <input type="hidden" id="userid" name="userid" value="{$usr_id}">
                                                            <input type="hidden" name="tiempo" id="tiempo" value="00:00">
                                                            <input type="hidden" id="tipo_test" name="tipo_test" value="{$evalu.tipo_test}">
                                                            <input type="hidden", id="idformacion_curso" name="idformacion_curso" value="{$FIELDS.record_id}">
                                                        </div>



                                                        {foreach key=np item=evapreg from=$LECCIONES[$lec].preg}

                                                            {if $evapreg.tipo_pregunta==='Verdadero/Falso'}


                                                                <div class="form-group">
                                                                    <label><h2>{$evapreg.pregunta}</h2></label>

                                                                    {foreach $evapreg.respuestas as $evalu1}
                                                                        {$tipo='falso'}
                                                                        {if ($evalu1.correcta eq 1)}
                                                                            {$tipo='verdadero'}
                                                                        {/if}
                                                                        <div class="radio">
                                                                            <input type="radio" name="preg_resp['{$evapreg.formacion_preguntasid}']" id="{$tipo}_{$evalu1.id}" value="{$evalu1.id}">
                                                                            <label for="{$tipo}_{$evalu1.id}">
                                                                                <h2>{$evalu1.respuesta}</h2>
                                                                            </label>
                                                                        </div>

                                                                    {/foreach}
                                                                    <hr>
                                                                </div>
                                                            {/if}

                                                            {if $evapreg.tipo_pregunta==='Respuesta multiple' || $evapreg.tipo_pregunta==='Multiple choice' }
                                                                <div class="form-group">
                                                                    <label><h2>{$evapreg.pregunta}</h2></label>
                                                                    {foreach $evapreg.respuestas as $evalu3}
                                                                        <div class="checkbox">
                                                                            <input type="checkbox" name="preg_resp['{$evapreg.formacion_preguntasid}'][]" value="{$evalu3.id}" id="check_{$evalu3.id}">
                                                                            <label >
                                                                                <h2>{$evalu3.respuesta}</h2>
                                                                            </label>
                                                                        </div>
                                                                    {/foreach} <!--$LECCIONES[$lec].preg-->
                                                                    <hr>
                                                                </div>
                                                            {/if}
                                                        {/foreach}


                                                        <div id="strat-layer" style="text-align: right; ">
                                                            {if ($lec) eq {$num}}

                                                                <button type="button" class="btn btn-primary" onclick="irEnviarRespuesta('eval_{$lec}')">Guardar</button>
                                                            {else}
                                                                <button type="button" class="btn btn-primary" onclick="irEnviarRespuesta('eval_{$lec}')">Guardar y Continuar</button>
                                                            {/if}
                                                            {if {$lec}!={$num}}
                                                                <button type="button" class="btn btn-primary" onClick="irSiguiente('{$lec}')">Continuar</button>
                                                            {/if}
                                                        </div>
                                                    </form>

                                                    <div id="resultado_1" style="display: none">
                                                        <h1 style="text-align: center" id="resultado"></h1>
                                                        {if {$lec}!={$num}}
                                                            <button type="button" class="btn btn-primary" onClick="irSiguiente('{$lec}')">Continuar</button>
                                                        {/if}

                                                    </div>
                                                {/foreach}
                                            </div>
                                        </div>

                                    {/if}
                                </div>
                            {/for}
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Regresar al Curso</button>

                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>

    </div>
    <script>
        var total='{$num}';
        var lecciones={$LECCIONES};

        function irLeccion(id){
            var idcambiar=parseInt(id);
            console.log('id',id);
            console.log('total',total);
            var to=parseInt(total);
            for (var i=0;i<=to;i++) {
                console.log('i',i)
                if (i==id) {
                    jQuery('#'+i).show();
                    jQuery('#ma_test_'+i).hide();
                }else{
                    jQuery('#'+i).hide();
                    jQuery('#ma_test_'+i).hide();
                }
            }
        }

        function irSiguiente(id){
            var idcambiar=parseInt(id)+parseInt(1);
            jQuery('#'+id).hide();
            jQuery('#ma_test_'+i).hide();
            console.log('idcambiar',idcambiar);
            jQuery('#'+idcambiar).show();
            console.log('total',total);
            var to=parseInt(total);
            var id=parseInt(id);
            console.log('id',id);

        }

        function irAnterior(id){
            var idcambiar=parseInt(id)-parseInt(1);
            jQuery('#'+id).hide();
            jQuery('#ma_test_'+id).hide();
            console.log('idcambiar',idcambiar);
            jQuery('#'+idcambiar).show();
            console.log('total',total);
            var to=parseInt(total);
            var id=parseInt(id);
            console.log('id',id);

        }

        function realizarTest(id){
            jQuery('#ma_test_'+id).show();
            console.log(lecciones);
            var to=parseInt(total);
            for (var i=0;i<=to;i++) {
                if (i!=id) {
                    jQuery('#ma_test_' + i).hide();
                }
                jQuery('#' + i).hide();

            }

        }
        function calcularProgreso(na,n){
            var na1=parseInt(na);
            var n1=parserInt(n);
            var por=na1*100/n1;
            console.log(por);
            return 10;
        }

        function irEnviarRespuesta(forma){
            var datapost=jQuery('#'+forma).serialize();
            console.log(forma);
            new jQuery.ajax({
                type: "POST",
                url:"index.php",
                data: {
                    module: "formacion_cursos",
                    action: "formacion_cursosAjax",
                    file: "DetailView",
                    save: true,
                    datapost:  datapost
                }
            }).done(function (response) {
                jQuery('#'+forma).hide();
                jQuery('#resultado_1').show();
                alert(response);
                location.reload();
                //jQuery('#resultado').html(response);

            })
            return false;


        }

        function verificaSirealizoTest(forma){
            var pruebaid=jQuery('#formacion_pruebasid').val();
            var user=jQuery('#userid').val();
            alert(user);
        }

        function extraerInfoHtmlGestionFacil(){
            alert("estou aqui");
        }
    </script>