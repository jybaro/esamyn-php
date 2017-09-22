<?php

$ess_id = $_SESSION['ess_id'];
//echo $ess_id;

//PASO 1
//var_dump($_SESSION);

$result = q("
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
    FROM 
    esamyn.esa_parametro
    WHERE
    par_padre IS NULL
    ");

$count_parametro = 0;
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
    $grupo_count++;
    $paso_count++;
    $directriz_count++;

    $puntaje_base_grupo += $r['par_puntaje'];
    $puntaje_base_total += $r['par_puntaje'];

    //Cierra 
    if ($grupo != $r['grupo'] && $grupo != 'primera vez') {
        $buff.= "<tbody>";

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

    $buff.= '<tr>';

    $buff.= '<th>';
    $buff.= "$count_parametro. ";
    $buff.= '</th>';
    $buff.= '<td>';
    $buff.= '<a href="#" onclick="p_abrir_preguntas('.$r['par_id'].",'{$r[par_codigo]}'".');return false;">';
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

    $buff.= '<td class="'.$class_parametro.'" style="text-align:center">';
    $buff.= "<$negrilla>No</$negrilla>";
    $buff.= '</td>';

    $buff.= '<td class="'.$class_parametro.'" style="text-align:right">';
    $buff.= "<$negrilla> 0/".$r['par_puntaje']." $asterisco</$negrilla>";
    $buff.= '</td>';

    $buff.= "</tr>";

    $grupo = $r['grupo'];
    $paso = $r['paso'];
    $directriz = $r['directriz'];
}
$buff.= '<tr>';

$buff.= '<th colspan="5" style="text-align:right;">';
$buff.= '<h3>';
$buff.= "TOTAL ($count_parametro indicadores)";
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
$buff.= round($puntaje_total/$puntaje_base_total) . ' %';
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
