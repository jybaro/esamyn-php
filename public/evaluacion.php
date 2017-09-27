<?php

$ess_id = $_SESSION['ess_id'];
//echo $ess_id;

//PASO 1
//var_dump($_SESSION);
$condiciones_no_aplica = q("SELECT * FROM esamyn.esa_condicion_no_aplica");
foreach($condiciones_no_aplica as $c) {
    $condiciones_no_aplica[$c['cna_id']] = $c;
}

$sql = "
    SELECT 
    * 
    ,(
        SELECT 
        n3.gpa_clave|| '. '||n3.gpa_texto
        FROM 
        esamyn.esa_grupo_parametro AS n1,
        esamyn.esa_grupo_parametro AS n2,
        esamyn.esa_grupo_parametro AS n3
        WHERE 
        n1.gpa_padre = n2.gpa_id
        AND
        n2.gpa_padre = n3.gpa_id
        AND
        n1.gpa_id = par_grupo_parametro
    ) AS grupo
    ,(
        SELECT 
        n2.gpa_clave ||'. '|| n2.gpa_texto
        FROM 
        esamyn.esa_grupo_parametro AS n1,
        esamyn.esa_grupo_parametro AS n2
        WHERE 
        n1.gpa_padre = n2.gpa_id
        AND
        n1.gpa_id = par_grupo_parametro
    ) AS paso
    ,(
        SELECT 
        n1.gpa_clave ||'. '|| n1.gpa_texto
        FROM 
        esamyn.esa_grupo_parametro AS n1
        WHERE 
        n1.gpa_id = par_grupo_parametro
    ) AS directriz
    ,(
        SELECT 
        res_valor_texto
        FROM
        esamyn.esa_cumple_condicion_no_aplica,
        esamyn.esa_pregunta,
        esamyn.esa_encuesta,
        esamyn.esa_respuesta
        WHERE
        ccn_pregunta = prg_id
        AND
        prg_id = res_pregunta
        AND
        res_encuesta = enc_id
        AND 
        prg_formulario = enc_formulario
        AND
        enc_establecimiento_salud = $ess_id
        AND
        ccn_condicion_no_aplica = par_condicion_no_aplica
    ) AS condicion_no_aplica
    FROM 
    esamyn.esa_parametro
    WHERE
    par_padre IS NULL
    ";
//echo $sql;
$result = q($sql);

$count_parametro = 0;
$count_parametro_si_aplica = 0;
//echo count($result);

echo '<table class="table table-bordered  table-hover">';

$grupo = 'primera vez';
$paso = 'primera vez';
$directriz = 'primera vez';

$grupo_count = 0;
$paso_count = 0;
$directriz_count = 0;

$buff = '';

//Agrega un ultimo elemento artificial para poder hacer el cierre del grupo final.
$result[] = array();

$puntaje_base_grupo = 0;
$puntaje_base_total = 0;

$puntaje_total = 0;
$puntaje_grupo = 0;

foreach($result as $r){
    $parametro = $r;
    $par_id = $r['par_id'];
    $grupo_count++;
    $paso_count++;
    $directriz_count++;

    $style = '';
    if(!empty($r['par_condicion_no_aplica']) && !empty($r['condicion_no_aplica'])){
        $style = 'color:#DDD';
    } else {
        //$puntaje_base_grupo += $r['par_puntaje'];
        //$puntaje_base_total += $r['par_puntaje'];

        $count_parametro_si_aplica++;
    }


    //Cierra 
    if ($grupo != $r['grupo'] && $grupo != 'primera vez') {
        $buff.= "</tbody>";

        $nombre_componente = str_replace('ó', '&Oacute;', strtoupper($grupo));
        $buff.= '<tr>';

        $buff.= '<th colspan="5" style="text-align:right;">';
        $buff.= '<h4>';
        $buff.= "TOTAL $nombre_componente ($grupo_count verificadores)";
        $buff.= '</h4>';
        $buff.= '</th>';

        $buff.= '<th colspan="2" style="text-align:right;">';
        $buff.= '<h3>';
        $buff.= "$puntaje_grupo/$puntaje_base_grupo";
        $buff.= '</h3>';
        $buff.= '</th>';

        $buff.= '</tr>';
    }
    if ($paso != $r['paso'] && $paso != 'primera vez') {
        $buff = str_replace('|_paso_rowspan_|', $paso_count, $buff);
    }
    if ($directriz != $r['directriz'] && $directriz != 'primera vez') {
        $buff = str_replace('|_directriz_rowspan_|', $directriz_count, $buff);
    }

    //Si es el ultimo agregado artificialmente, sale del bucle luego de cerrar lo anterior, sin intentar agregar nada nuevo.
    if (!isset($r['par_texto'])) {
        break;
    }
    //pone lo nuevo
    $count_parametro++;

    if ($grupo != $r['grupo']) {
        $buff.= '<tr>';

        $buff.= '<th colspan="7">';
        $buff.= '<h2>';
        $buff.= '<a href="#" onclick="p_alternar_grupo(' . "'grupo_$count_parametro'" . ');return false;">';
        $buff.= '<span id=' . "'icono_grupo_$count_parametro'" . '>&#8861;</span> ' . $r['grupo'];
        $buff.= '</a>';
        $buff.= '</h2>';
        $buff.= '</th>';

        $buff.= '</tr>';

        $buff.= "<tbody id='grupo_$count_parametro'>";

        $buff.= "<tr>";

        $buff.= '<th>&nbsp;</th>';
        $buff.= '<th>Verificador</th>';
        $buff.= '<th>Paso</th>';
        $buff.= '<th>Directriz</th>';
        $buff.= '<th>Parámetro</th>';
        $buff.= '<th>Cumple</th>';
        $buff.= '<th>Puntaje</th>';

        $buff.= "</tr>";

        $grupo_count = 0;
        $puntaje_base_grupo = 0;
        $puntaje_grupo = 0;
    }

    $class_parametro = '';
    $asterisco = '';
    $negrilla = 'span';

    if ($r['par_obligatorio'] == 1) {
        $class_parametro = 'danger';
        $asterisco = '*';
        $negrilla = 'strong';
    }

    $buff.= '<tr style="'.$style.'">';

    $buff.= '<th>';
    $buff.= "$count_parametro. ";
    $buff.= '</th>';
    $buff.= '<td>';
    $buff.= '<a href="#" onclick="p_abrir_preguntas('.$par_id.",'{$r[par_codigo]}'".');return false;">';
    $buff.= str_replace(' ', "<br>", $r['par_codigo']);
    $buff.= '</a>';
    $buff.= '</td>';

    if ($paso != $r['paso']) {
        $buff.= '<td rowspan="|_paso_rowspan_|">';
        $buff.= $r['paso'];
        $buff.= '</td>';
        $paso_count = 0;
    }

    if ($directriz != $r['directriz']) {
        $buff.= '<td rowspan="|_directriz_rowspan_|">';
        $buff.= $r['directriz'];
        $buff.= '</td>';
        $directriz_count = 0;
    }

    $buff.= '<td class="'.$class_parametro.'">';
    $buff.= "<$negrilla>".$r['par_texto']."</$negrilla>";
    $buff.= '</td>';

    $cumple_parametro = 'No';

    $buff.= '<td class="'.$class_parametro.'" style="text-align:center">';
    if(!empty($r['par_condicion_no_aplica']) && !empty($r['condicion_no_aplica'])){
        $buff.= '<div>';
        $razon = $condiciones_no_aplica[$r['par_condicion_no_aplica']]['cna_texto'];

        $buff.= 'NA porque: ' . $razon;
        $buff.= '</div>';
    } else {
        $respuestas = array();

        $sql = "
            SELECT 
            *
            ,(SELECT tpp_clave FROM esamyn.esa_tipo_pregunta WHERE tpp_id=prg_tipo_pregunta) AS tipo
            FROM
            esamyn.esa_parametro_pregunta,
            esamyn.esa_pregunta,
            esamyn.esa_encuesta,
            esamyn.esa_respuesta
            WHERE
            ppr_pregunta = prg_id
            AND
            prg_id = res_pregunta
            AND
            res_encuesta = enc_id
            AND 
            prg_formulario = enc_formulario
            AND
            enc_establecimiento_salud = $ess_id
            AND
            ppr_parametro = $par_id
            ";
        $data = q($sql);

        foreach($data as $d){
            $enc_id = $d['enc_id'];
            $prg_id = $d['prg_id'];
            if (!isset($respuestas[$enc_id])) {
                $respuestas[$enc_id] = array('padre'=>array(), 'hijo'=>array());
            }
            $respuestas[$enc_id]['padre'][$prg_id] = $d;
        }

        $sql = "
            SELECT 
            *
            ,(SELECT tpp_clave FROM esamyn.esa_tipo_pregunta WHERE tpp_id=prg_tipo_pregunta) AS tipo
            FROM
            esamyn.esa_parametro_pregunta,
            esamyn.esa_pregunta,
            esamyn.esa_encuesta,
            esamyn.esa_respuesta,
            esamyn.esa_parametro
            WHERE
            ppr_pregunta = prg_id
            AND
            prg_id = res_pregunta
            AND
            res_encuesta = enc_id
            AND 
            prg_formulario = enc_formulario
            AND
            enc_establecimiento_salud = $ess_id
            AND
            ppr_parametro = par_id
            AND
            par_padre = $par_id
            ";
        $data = q($sql);

        foreach($data as $d){
            $enc_id = $d['enc_id'];
            $prg_id = $d['prg_id'];
            $respuestas[$enc_id]['hijo'][$prg_id] = $d;

        }

        $count_respuestas_totales = 0;
        $count_respuestas_cumple = 0;
        $cantidad_minima = $parametro['par_cantidad_minima'];
        $porcentaje = $parametro['par_porcentaje'];

        foreach($respuestas as $enc_id => $respuesta){
            $cumple = false;
            $evaluando_opciones = false;
            $count_opciones = 0;
            foreach($respuesta['padre'] as $padre){
                switch($padre['tipo']){
                case 'numero':
                    $cumple = ((int)$padre['res_valor_numero'] >= $cantidad_minima); 
                    break;
                default:
                    $evaluando_opciones = true;
                    if (!empty($padre['res_valor_texto'])){
                        $count_opciones++;
                    }
                    break;
                }
            }
            if ($evaluando_opciones){
                $cumple = ($count_opciones >= $cantidad_minima);
            }
            if (!empty($respuesta['hijo'])){
                if ($cumple){

                    //cumplido el padre, ahora se evalua el hijo:
                   
                    $cumple = false;
                    $evaluando_opciones = false;
                    $count_opciones = 0;
                    foreach($respuesta['hijo'] as $hijo){
                        switch($hijo['tipo']){
                        case 'numero':
                            $cumple = ((int)$hijo['res_valor_numero'] >= $cantidad_minima); 
                            break;
                        default:
                            $evaluando_opciones = true;
                            if (!empty($hijo['res_valor_texto'])){
                                $count_opciones++;
                            }
                            break;
                        }
                    }
                    if ($evaluando_opciones){
                        $cumple = ($count_opciones >= $cantidad_minima);
                    }
                    if ($cumple){
                        $count_respuestas_cumple++;
                    }
                }
                $count_respuestas_totales++;
            } else {
                $count_respuestas_totales++;
                if ($cumple){
                    $count_respuestas_cumple++;
                }
            }
        }

        if (($count_respuestas_cumple * 100 / $count_respuestas_totales) >= $porcentaje) {
            $cumple_parametro = 'SI';
        }

        $buff.= "<$negrilla>$cumple_parametro</$negrilla>";
        $puntaje_base_grupo += $r['par_puntaje'];
        $puntaje_base_total += $r['par_puntaje'];
    }
    $buff.= '</td>';

    $puntaje = ($cumple_parametro === 'SI') ? $r['par_puntaje'] : 0;

    $buff.= '<td class="'.$class_parametro.'" style="text-align:right">';
    $buff.= "<$negrilla> $puntaje/".$r['par_puntaje']." $asterisco</$negrilla>";
    $buff.= '</td>';

    $puntaje_total += $puntaje;
    $puntaje_grupo += $puntaje;

        //$count_parametro_si_aplica++;

    $buff.= "</tr>";

    $grupo = $r['grupo'];
    $paso = $r['paso'];
    $directriz = $r['directriz'];
}
$buff.= '<tr>';

$buff.= '<th colspan="5" style="text-align:right;">';
$buff.= '<h3>';
$buff.= "TOTAL ($count_parametro_si_aplica indicadores que sí aplican)";
$buff.= '</h3>';
$buff.= '</th>';

$buff.= '<th colspan="2" style="text-align:right;">';
$buff.= '<h3>';
$buff.= "$puntaje_total/$puntaje_base_total";
$buff.= '</h3>';
$buff.= '</th>';

$buff.= '</tr>';

$buff.= '<tr>';

$buff.= '<th colspan="5" style="text-align:right;">';
$buff.= '<h3>';
$buff.= "PORCENTAJE";
$buff.= '</h3>';
$buff.= '</th>';

$buff.= '<th colspan="2" style="text-align:right;">';
$buff.= '<h3>';
$buff.= round($puntaje_total * 100/$puntaje_base_total) . ' %';
$buff.= '</h3>';
$buff.= '</th>';

$buff.= '</tr>';

echo $buff;
echo "</table>";
//PASO 2



//PASO 3





?>

<div id="modalPreguntas" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Preguntas relacionadas al verificador <span id='verificador'></span></h4>
      </div>
      <div class="modal-body">
        <span id="par_id"></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>

<script>

function p_abrir_preguntas(par_id, verificador){
    console.log(par_id);
    $('#verificador').text(verificador);
    $('#par_id').text('');
    $.ajax({
        'url': '/_rutaPregunta/' + par_id
    }).done(function(data){

        console.log(data);
        data = eval(data);
        console.log(data);
        var count = 0;
        var num_preguntas = data.length;
        if (num_preguntas == 0) {
            $('#par_id').text('No se encuentran datos.');
            $('#modalPreguntas').modal('show');
        } else {
            data[0].forEach(function(d){
                var pregunta = d;
                $('#par_id').append('<div>' + pregunta['ruta'] + '</div>');
                $('#modalPreguntas').modal('show');
            });
            if (typeof(data[1]) !== 'undefined') {
                $('#par_id').append('<hr><h4>Verificador hijo </h4><hr>');
                data[1].forEach(function(d){
                    var pregunta = d;
                    $('#par_id').append('<div>' + pregunta['ruta'] + '</div>');
                    $('#modalPreguntas').modal('show');
                });
            }
        }
        //$('#par_id').text(par_id);
        //$('#modalPreguntas').modal('show');
    }).fail(function(){
        $('#par_id').text('No se encuentran datos.');
        $('#modalPreguntas').modal('show');
    });

}
function p_abrir_preguntas_old(par_id, verificador){
    console.log(par_id);
    $('#verificador').text(verificador);
    $('#par_id').text('');
    $.ajax({
        'url': '/_listar/parametro_pregunta/parametro/' + par_id
    }).done(function(data){
        data = eval(data);
        console.log(data);
        var count = 0;
        var num_preguntas = data.length;
        if (num_preguntas == 0) {
            $('#par_id').text('No se encuentran datos.');
            $('#modalPreguntas').modal('show');
        }
        data.forEach(function(d){

            $.ajax({
                'url': '/_listar/pregunta/' + d['pregunta']
            }).done(function(data){
                data = eval(data);
                console.log(data);
                data.forEach(function(d){
                    var pregunta = d;

                    $.ajax({
                        'url': '/_listar/formulario/' + d['formulario']
                    }).done(function(data){
                        data = eval(data);
                        console.log(data);
                        count ++;
                        data.forEach(function(d){
                            var formulario = d;
                            $('#par_id').append('<div>'+formulario['clave'] + '.' + pregunta['texto']+'</div>');
                        });

                        if (count == num_preguntas) {
                            //$('#par_id').text(par_id);
                            $('#modalPreguntas').modal('show');
                        }
                    }).fail(function(){
                        $('#par_id').text('No se encuentran datos.');
                        $('#modalPreguntas').modal('show');
                    });
                });
            }).fail(function(){
                $('#par_id').text('No se encuentran datos.');
                $('#modalPreguntas').modal('show');
            });
        });
        //$('#par_id').text(par_id);
        //$('#modalPreguntas').modal('show');
    }).fail(function(){
        $('#par_id').text('No se encuentran datos.');
        $('#modalPreguntas').modal('show');
    });

}


function p_alternar_grupo(id){
    //var g = document.getElementById(id);
    //var icono = document.getElementById('icono_' + id);
    var g = $('#' + id);
    var icono = $('#icono_' + id);
    g.toggle('fast', function(){
        if($(this).is(':visible')){
            icono.html('&#8861;');
        } else if ($(this).is(':hidden')) {
            icono.html('&#8853;');
        }; 
    });
}
</script>
