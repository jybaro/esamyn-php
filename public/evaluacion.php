<h1>Formulario de Evaluación</h1>

<?php

$ess_id = $_SESSION['ess_id'];
$evaluacion = q("
    SELECT 
    * 
    FROM 
    esamyn.esa_evaluacion
    ,esamyn.esa_tipo_evaluacion
    WHERE eva_establecimiento_salud = $ess_id
    AND eva_tipo_evaluacion = tev_id
    AND eva_activo = 1
    AND eva_borrado IS NULL
    ");

if (!$evaluacion) {
    echo '<div class="alert alert-danger"><h2>No hay evaluaci&oacute;n activa</h2>Solicite a su supervisor que cree una evaluación para este Establecimiento de Salud.</div>';
    return;
} else {
    $evaluacion = $evaluacion[0];
    $_SESSION['evaluacion'] = $evaluacion;
    $eva_id = $evaluacion['eva_id'];
}


$cumplido_obligatorios = true;
$cumplido_minimos = true;

$formularios = q("
SELECT frm_id, frm_clave, frm_umbral_minimo
,(
    SELECT 
    COUNT(*) 
    FROM 
    esamyn.esa_encuesta 
    WHERE 
    enc_borrado IS NULL
    AND 
    enc_formulario = frm_id
    AND
    enc_evaluacion = $eva_id
    AND 
    enc_finalizada = 1
 ) AS cantidad_llenos
FROM esamyn.esa_formulario
");

foreach($formularios as $frm) {
    $cumplido_minimos = ($cumplido_minimos && ((int)$frm['frm_umbral_minimo'] <= $frm['cantidad_llenos']));
}


?>

<a href="#" download><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Descargar HTML</a>
|
<a href="#" onclick="p_imprimir();return false;"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Generar PDF</a>
|
<a href="#" onclick="p_xlsx();return false;"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Exportar datos</a>
<div id="formulario_evaluacion">
<?php

$ess_id = $_SESSION['ess_id'];
$unicodigo = $_SESSION['ess']['ess_unicodigo'];
$eva_id = $_SESSION['evaluacion']['eva_id'];

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
        enc_borrado IS NULL
        AND
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
        enc_evaluacion = $eva_id
        AND
        ccn_condicion_no_aplica = par_condicion_no_aplica
        LIMIT 1
    ) AS condicion_no_aplica
    ,(
        SELECT 
        COUNT(*)
        FROM 
        esamyn.esa_parametro as par_hijos
        WHERE
        par_hijos.par_padre = padre.par_id
    ) AS count_hijos
    FROM 
    esamyn.esa_parametro AS padre
    WHERE
    par_padre IS NULL
    ORDER BY par_id
    ";
//echo $sql;
$result = q($sql);
if (!$result){
    //echo "ERROR:<pre>$sql";
}

$count_parametro = 0;
$count_parametro_si_aplica = 0;
//echo count($result);

echo '<table id="tabla_formulario_evaluacion" class="table table-bordered  table-hover">';

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
    $paso_count++;
    $directriz_count++;
    $aplica = (empty($r['par_condicion_no_aplica']) || empty($r['condicion_no_aplica']));

    $style = '';
    if(!$aplica){
        $style = 'color:#DDD';
    } else {
        //$puntaje_base_grupo += $r['par_puntaje'];
        //$puntaje_base_total += $r['par_puntaje'];

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

    $cumple_parametro = true;
    $buff_evaluacion = '';

    $buff.= '<td class="'.$class_parametro.'" style="text-align:center">';
    if(!$aplica){
        $buff.= '<div>';
        $razon = $condiciones_no_aplica[$r['par_condicion_no_aplica']]['cna_texto'];

        $puntaje = 0;
        $cumple_parametro = false;
        $buff.= 'NA porque: ' . $razon;
        $buff.= '</div>';
    } else {
        $count_parametro_si_aplica++;
        $grupo_count++;
        $encuestas = array();

        $sql = "
            SELECT *
            FROM esamyn.esa_pregunta

            LEFT OUTER JOIN esamyn.esa_tipo_pregunta
            ON tpp_id = prg_tipo_pregunta

            INNER JOIN esamyn.esa_parametro_pregunta
            ON ppr_pregunta = prg_id

            INNER JOIN esamyn.esa_parametro
            ON ppr_parametro = par_id AND ppr_parametro = $par_id

            LEFT OUTER JOIN esamyn.esa_encuesta
            ON enc_borrado IS NULL
                AND prg_formulario = enc_formulario 
                AND enc_establecimiento_salud = $ess_id 
                AND enc_evaluacion = $eva_id
                AND enc_finalizada = 1

            LEFT OUTER JOIN esamyn.esa_respuesta
            ON prg_id = res_pregunta AND res_encuesta = enc_id
            ";
$misql= $sql;
        $data = q($sql);

        if ($data) {
        foreach($data as $d){
            $frm_id = $d['prg_formulario'];
            $enc_id = $d['enc_id'];
            $prg_id = $d['prg_id'];
            $prg_padre = $d['prg_padre'];

            if (!isset($encuestas[$frm_id])){
                $encuestas[$frm_id] = array();
            }
            if (!isset($encuestas[$frm_id][$enc_id])) {
                $encuestas[$frm_id][$enc_id] = array('padre'=>array(), 'hijo'=>array());
            }
            if (!isset($encuestas[$frm_id][$enc_id]['padre'][$prg_padre])){
                $encuestas[$frm_id][$enc_id]['padre'][$prg_padre] = array();
            }
            $encuestas[$frm_id][$enc_id]['padre'][$prg_padre][$prg_id] = $d;
            $encuestas[$frm_id][$enc_id]['padre'][$prg_padre][$prg_id]['hijos'] = array();
        }
        }

        $sql = "
            SELECT *
            FROM esamyn.esa_pregunta

            LEFT OUTER JOIN esamyn.esa_tipo_pregunta
            ON tpp_id = prg_tipo_pregunta

            INNER JOIN esamyn.esa_parametro_pregunta
            ON ppr_pregunta = prg_id

            INNER JOIN esamyn.esa_parametro
            ON ppr_parametro = par_id AND par_padre = $par_id

            LEFT OUTER JOIN esamyn.esa_encuesta
            ON enc_borrado IS NULL
                AND prg_formulario = enc_formulario 
                AND enc_establecimiento_salud = $ess_id 
                AND enc_evaluacion = $eva_id
                AND enc_finalizada = 1

            LEFT OUTER JOIN esamyn.esa_respuesta
            ON prg_id = res_pregunta AND res_encuesta = enc_id
            ";
        $data = q($sql);

        if ($data) {
            foreach($data as $d){
                $frm_id = $d['prg_formulario'];
                $enc_id = $d['enc_id'];
                $prg_id = $d['prg_id'];
                $encuestas[$frm_id][$enc_id]['hijo'][$prg_id] = $d;
                if (
                    isset($encuestas[$frm_id][$enc_id]['padre'][$d['prg_padre']])
                ){
                    $encuestas[$frm_id][$enc_id]['padre'][$d['prg_padre']]['hijos'][$prg_id] = & $encuestas[$frm_id][$enc_id]['hijo'][$prg_id];
                }

            }
        }

        $porcentaje = $parametro['par_porcentaje'];
        $umbral = $parametro['par_umbral'];
        $operador_logico = $parametro['par_operador_logico'];
        $buff_evaluacion .= "\n[operador_logico:$operador_logico, porcentaje:$porcentaje, cantidad_minima:$cantidad_minima, umbral:$umbral]";
        //$buff_evaluacion .= ($count_parametro != 60) ? '' : print_r($encuestas, true);

        foreach($encuestas as $frm_id => $encuestas_form){
            $count_respuestas_totales = 0;
            $count_respuestas_cumple = 0;
            $buff_evaluacion .= "\n\n[FORM $frm_id]";

            foreach($encuestas_form as $enc_id => $encuesta){

                $buff_evaluacion .= "\n\n[ENCUESTA $frm_id.$enc_id]";
                $valor_numero_padre = array();
                $cumple = ($operador_logico == 1);
                $cumple = $cumple || (count($encuesta['padre']) == 0);//en el caso que no hay padre, y es O, que evalue los hijos
                $cantidad_minima = $parametro['par_cantidad_minima'];


                if (is_array($encuesta['padre'])) {
                    foreach($encuesta['padre'] as $prg_padre => $padres){
                        $evaluando_opciones = false;
                        $count_opciones = 0;
                        $buff_evaluacion .= "\n\n[PADRE $prg_padre]";
                        foreach($padres as $prg_id => $padre){

                            switch($padre['tpp_clave']){
                            case 'numero':
                                $valor_numero_padre[$prg_id] = (int)$padre['res_valor_numero'];
                                $buff_evaluacion .= "\n[PADRE es numero: ".$valor_numero_padre[$prg_id]."]";

                                if ($operador_logico == 1) {
                                    $cumple = $cumple && ($valor_numero_padre[$prg_id] >= $cantidad_minima); 
                                } else {
                                    $cumple = $cumple || ($valor_numero_padre[$prg_id] >= $cantidad_minima); 
                                }
                                break;
                            default:
                                $buff_evaluacion .= "\n-[PADRE {$padre[par_id]}-$prg_id es texto ({$padre[res_valor_texto]})(".(!empty($padre['res_valor_texto'])?'no vacio':'vacio')."):{$padre[tpp_clave]}]-";
                                $evaluando_opciones = true;
                                if (!empty($padre['res_valor_texto'])){
                                    $count_opciones++;
                                }
                                break;
                            }
                        }
                        if ($evaluando_opciones){
                            if ($operador_logico == 1) {
                                $cumple = $cumple && ($count_opciones >= $cantidad_minima);
                                $buff_evaluacion .= "\n[Y]";
                            } else {
                                $cumple = $cumple || ($count_opciones >= $cantidad_minima);
                                $buff_evaluacion .= "\n[O]";
                            }
                            $buff_evaluacion .= "\n[evaluando opciones: $count_opciones >= $cantidad_minima:".(($count_opciones >= $cantidad_minima)?'si':'no').", cumple:".($cumple?'si':'no')."] ";
                        }
                    }
                }
                $buff_evaluacion .= "\n[PADRE cumple ".($cumple?'si':'no').", hijo ".(!empty($encuesta['hijo'])?'si':'no')." ] ";
                if (!empty($encuesta['hijo'])){
                    if ($cumple){

                        //cumplido el padre, ahora se evalua el hijo:

                        $evaluando_opciones = false;
                        $count_opciones = 0;
                        $primer_parametro_hijo = true;
                        foreach($encuesta['hijo'] as $hijo){
                            if ($primer_parametro_hijo){
                                $operador_logico = $hijo['par_operador_logico'];
                                $cantidad_minima = $hijo['par_cantidad_minima'];
                                $porcentaje = $hijo['par_porcentaje'];
                                $cumple = ($operador_logico == 1);

                                $primer_parametro_hijo = false;
                            }
                            $buff_evaluacion .= "\n[HIJO tipo {$hijo[tpp_clave]}]";
                            $prg_padre = $hijo['prg_padre'];

                            switch($hijo['tpp_clave']){
                            case 'numero':
                                $valor_numero_hijo = (int)$hijo['res_valor_numero'];
                                $buff_evaluacion .= "\n[HIJO numero $valor_numero_hijo]";

                                if (isset($valor_numero_padre[$prg_padre]) && !empty($valor_numero_padre[$prg_padre])) {
                                    if ($cantidad_minima > 0) {
                                        $condicion_hijo = ($valor_numero_hijo * 100 / $valor_numero_padre[$prg_padre]) >= $cantidad_minima;
                                        $buff_evaluacion .= "\n[% ($valor_numero_hijo * 100 / ".$valor_numero_padre[$prg_padre].") >= $cantidad_minima: ".($condicion_hijo?'si':'no')."]";
                                    } else {
                                        $cantidad_maxima = -1 * $cantidad_minima;
                                        $condicion_hijo = ($valor_numero_hijo * 100 / $valor_numero_padre[$prg_padre]) <= $cantidad_maxima;
                                        $buff_evaluacion .= "\n[% ($valor_numero_hijo * 100 / ".$valor_numero_padre[$prg_padre].") <= $cantidad_maxima: ".($condicion_hijo?'si':'no')."]";
                                    }
                                } else {
                                    $condicion_hijo = $valor_numero_hijo >= $cantidad_minima;
                                }

                                if ($operador_logico == 1) {
                                    $cumple = $cumple && $condicion_hijo; 
                                    $buff_evaluacion .= "\n[Y]";
                                } else {
                                    $cumple = $cumple || $condicion_hijo; 
                                    $buff_evaluacion .= "\n[O]";
                                }
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
                            $buff_evaluacion .= "\n[HIJO $count_opciones >= $cantidad_minima:".($count_opciones >= $cantidad_minima?'si':'no')." ]";
                            if ($operador_logico == 1) {
                                $cumple = $cumple && ($count_opciones >= $cantidad_minima);
                            } else {
                                $cumple = $cumple || ($count_opciones >= $cantidad_minima);
                            }
                        }
                        if ($cumple){
                            $count_respuestas_cumple++;
                        }
                    }
                    $count_respuestas_totales++;
                } else {

                    // debe ser analizado si debía haber un hijo, para poner falso, o si no habia parámetro hijo
                    $buff_evaluacion.="\n[hijo count: ".$parametro['count_hijos']."]";
                    //if ($parametro['count_hijos'] > 0){
                    //    $cumple = false;
                    // }

                    $count_respuestas_totales++;
                    if ($cumple){
                        $count_respuestas_cumple++;
                    }
                }
            }

            if ($count_respuestas_totales >= $umbral && ($count_respuestas_cumple * 100 / $count_respuestas_totales) >= $porcentaje) {
                $cumple_parametro = $cumple_parametro && true;
            } else {
                $cumple_parametro= false;
                break;
            }
        }


        $buff.= "<$negrilla>" . ($cumple_parametro ? 'SI' : 'NO') . "</$negrilla>";

//$buff.="<pre>[$umbral, $porcentaje%, $cantidad_minima, $count_respuestas_cumple, $count_respuestas_totales]".'</pre>';

        $puntaje_base_grupo += $r['par_puntaje'];
        $puntaje_base_total += $r['par_puntaje'];
    }
    $buff.= '</td>';

    $puntaje = ($cumple_parametro) ? $r['par_puntaje'] : 0;

    if ($r['par_obligatorio'] == 1) {
        $cumplido_obligatorios = ($cumplido_obligatorios && $cumple_parametro);
    }

    $buff.= '<td class="'.$class_parametro.'" xxxstyle="text-align:right">';
    $buff.= "<$negrilla> $puntaje/".$r['par_puntaje']." $asterisco</$negrilla>";
//$buff .= '<pre>'.$buff_evaluacion.'</pre>';
    $buff.= '</td>';

    $puntaje_total += $puntaje;
    $puntaje_grupo += $puntaje;

        //$count_parametro_si_aplica++;

    $buff.= "</tr>";

    $grupo = $r['grupo'];
    $paso = $r['paso'];
    $directriz = $r['directriz'];
}

$porcentaje_cumplimiento_total = round($puntaje_total * 100/$puntaje_base_total);


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
$buff.= $porcentaje_cumplimiento_total . ' %';
$buff.= '</h3>';
$buff.= '</th>';

$buff.= '</tr>';

echo $buff;
echo "</table>";


$eva_cumplido_minimos = ($cumplido_minimos ? 1 : 0);
$eva_cumplido_obligatorios = ($cumplido_obligatorios? 1 : 0);

q("
UPDATE esamyn.esa_evaluacion 
SET 
eva_calificacion=$porcentaje_cumplimiento_total
,eva_cumplido_minimos=$eva_cumplido_minimos
,eva_cumplido_obligatorios=$eva_cumplido_obligatorios

WHERE eva_id=$eva_id");

?></div>


    <?php if ($cumplido_minimos): ?>
    <div class="alert alert-success">Se cumplen con la cantidad mínima de todos los formularios.</div>
    <?php else: ?>
    <div class="alert alert-danger">No se cumplen con la cantidad mínima de todos los formularios.</div>
    <?php endif; ?>

    <?php if ($cumplido_obligatorios): ?>
    <div class="alert alert-success">Se cumplen con todos los par&aacute;metros obligatorios.</div>
    <?php else: ?>
    <div class="alert alert-danger">No se cumplen con todos los par&aacute;metros obligatorios.</div>
    <?php endif; ?>

<div id="modalPreguntas" class="modal fade" role="dialog" tabindex="-1">
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


<script src="/js/Blob.min.js"></script>
<script src="/js/xlsx.full.min.js"></script>
<script src="/js/FileSaver.min.js"></script>
<script src="/js/tableexport.min.js"></script>

<script src="/js/jspdf.min.js"></script>
<script src="/js/html2canvas.min.js"></script>
<script src="/js/html2pdf.js"></script>

<script>

function p_abrir_preguntas(par_id, verificador){
    console.log(par_id);
    $('#verificador').text(verificador);
    $.ajax({
        'url': '/_rutaPregunta/' + par_id
    }).done(function(data){

        $('#par_id').text('');

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

function p_imprimir(){
    var element = document.getElementById('formulario_evaluacion');
    html2pdf(element, {
        margin:       1,
        filename:     'formulario_evaluacion_<?=$unicodigo?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { dpi: 192, letterRendering: true },
        jsPDF:        { unit: 'cm', format: 'A4', orientation: 'portrait' }
    });
}

function p_xlsx(){
    $('#tabla_formulario_evaluacion').tableExport();
}
</script>
