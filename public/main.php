<?php

require_once('../private/config.php');
require_once('../private/bdd.php');

//var_dump($conn);
$formularios = q("
    SELECT * 
    FROM 
    esamyn.esa_formulario 
    ORDER BY frm_clave
    ");

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
    ");

if (!$evaluacion) {
    echo '<div class="alert alert-danger"><h2>No hay evaluaci&oacute;n activa</h2>Solicite a su supervisor que cree una evaluación para este Establecimiento de Salud.</div>';
    return;
} else {
    $evaluacion = $evaluacion[0];
    $_SESSION['evaluacion'] = $evaluacion;
    $eva_id = $evaluacion['eva_id'];
}
//var_dump(pg_fetch_all($result));
?>

    <h1><?=$evaluacion['tev_nombre']?> "<?=$evaluacion['eva_descripcion']?>"</h1>

<div class="alert">(<span id="avance_general"></span>% de avance general en los formularios ingresados)</div>
<?php
echo '<ul>';
$tree = array();

$formularios = empty($formularios) ? array() : $formularios;
$avance_total = 0;

foreach ($formularios as $formulario){
    $frm_id = $formulario['frm_id'];
    $avance_formulario = 0;

    //echo '<li>';
    //echo '<a href="/form/' . $frm_id . '">';
    echo '<h4>';
    echo $formulario['frm_clave'] . '. ' . $formulario['frm_titulo'] . ' (<span id="avance_parcial_'.$formulario['frm_id'].'"></span>% de avance parcial)';
    //echo '</a>';
    echo '</h4>';

    $encuestas = q("
        SELECT * 
        FROM 
        esamyn.esa_encuesta 
        WHERE 
        enc_formulario = $frm_id 
        AND 
        enc_establecimiento_salud=$ess_id
        AND 
        enc_evaluacion=$eva_id
        ");

    echo '<ol>';
    $min = $formulario['frm_umbral_minimo']; 
    $max = $formulario['frm_umbral_maximo'];
    $max = (empty($max)) ? 'no hay máximo' : "máximo $max";

    echo "<div>Formularios finalizados: mínimo $min, $max.</div>";
    //echo '<pre>'.$count_preguntas_respondidas . '-' .print_r($preguntas[0]['count'], true).'</pre>';
    //while ($encuesta = pg_fetch_array($result)) {
    $count = 0;
    $count_finalizado = 0;
    
    $encuestas = (empty($encuestas) ? array() : $encuestas);
    //var_dump($encuestas);

    foreach($encuestas as $encuesta){
        $enc_id = $encuesta['enc_id'];

        $preguntas = array(0=>array(
            'count' => 0,
            'padre' => null,
            'count_respuesta' => 0
        ));
        $sql = ("
        SELECT *
        ,(
            SELECT 
            COUNT(*) 
            FROM 
            esamyn.esa_respuesta
            ,esamyn.esa_encuesta 
            WHERE 
            res_pregunta = prg_id 
            AND 
            res_encuesta = enc_id
            AND
            enc_id = $enc_id
            AND
            enc_establecimiento_salud = $ess_id
            AND NOT (
                res_valor_texto IS NULL 
                AND 
                res_valor_numero IS NULL 
                AND 
                res_valor_fecha IS NULL
            )
        ) AS count_respuesta,
        (
            SELECT 
            tpp_clave
            FROM
            esamyn.esa_tipo_pregunta
            WHERE
            tpp_id = prg_tipo_pregunta
        ) AS tipo 
        FROM 
        esamyn.esa_pregunta 
        WHERE 
        prg_formulario=$frm_id
        ");
        $result = q($sql);
        foreach ($result as $r){
            if ($r['tipo'] !== 'null') {
                $preguntas[$r['prg_id']] = $r;
                $preguntas[$r['prg_id']]['hijos'] = array();
                $preguntas[$r['prg_id']]['padre'] = null;
                $preguntas[$r['prg_id']]['count'] = 0;
            }
        }
        foreach ($result as $r){
            if ($r['tipo'] !== 'null') {
                $preguntas[$r['prg_padre']]['hijos'][$r['prg_id']] = & $preguntas[$r['prg_id']];
                if (!empty($r['prg_padre'])) {
                    $preguntas[$r['prg_id']]['padre'] = & $preguntas[$r['prg_padre']];
                } else {
                    $preguntas[$r['prg_id']]['padre'] = & $preguntas[0];
                }
            }
        }
        foreach($preguntas as & $pregunta){
            $padre = & $pregunta;
            $count_respuesta = (int)$pregunta['count_respuesta'];

            //if ($count_respuesta != 0) echo '<br>'.$padre['count_respuesta'].'[]<br>';
            while (!empty($padre)){
                $padre['count'] += $count_respuesta;
                //if ($count_respuesta != 0) echo $padre['prg_texto'].'['.$padre['count'].']';
                $padre = & $padre['padre'];
            }
        }

        $count_preguntas_respondidas = 0;
        $count_preguntas = 0;
        foreach($preguntas as $pregunta){
            if (!empty($pregunta['padre']) && !empty($pregunta['padre']['padre']) && empty($pregunta['padre']['padre']['padre']) ){
                $count_preguntas ++;
                if (!empty($pregunta['count'])) {
                    $count_preguntas_respondidas ++;
                }
            }
        }
        $count++;
        $enc_id = $encuesta['enc_id'];
        echo '<li>';
        echo '<a href="/form/' . $frm_id . '/' . $enc_id . '">';
        //echo $encuesta['enc_creado'];
        $fecha = p_formatear_fecha($encuesta['enc_creado']);
        $enc_usuario = $encuesta['enc_usuario'];
        $creado_por = q("
            SELECT 
            usu_nombres || ' '||usu_apellidos AS nombre 
            FROM 
            esamyn.esa_usuario 
            WHERE 
            usu_id=$enc_usuario
            ")[0]['nombre'];

        echo 'Creado por ' . $creado_por . ' el ' . $fecha;
        echo '</a>';

        echo ' ';
        if ($encuesta['enc_finalizada']) { 
            $avance_formulario += 100;

            //echo " (finalizada)"; 
            $count_finalizado ++;
            echo "<div style='
                width:100px;
                background-color:#9F9;
                border:solid 1px #000;
                text-align:center;
                display:inline-block;
                '>Finalizada</div>";
        } else {
            $porcentaje = round($count_preguntas_respondidas * 100 / $count_preguntas, 0);
            $avance_formulario += $porcentaje;
            $xpos = $porcentaje - 100;
            echo "<div style='
                width:100px;
            background-image:url(\"/img/degradado.png\");
            background-position:$xpos 0;
            background-repeat:repeat-y;
            border:solid 1px #000;
            text-align:center;
            display:inline-block;
            ' title='$count_preguntas_respondidas de $count_preguntas'>$porcentaje %</div>";
            //print_r(q("SELECT * FROM esamyn.esa_tipo_pregunta"));
            //echo ' ('.(round($count_respuestas * 100 / $count_preguntas, 0)) . "% contestado, $count_respuestas de $count_preguntas preguntas)";
        }

        echo '</li>';
    }

    if ($count == 0) {
        echo '(No hay encuestras ingresadas aún)';
    }

    if (empty($formulario['frm_umbral_maximo']) || $count_finalizado < $formulario['frm_umbral_maximo']) {
        echo '<div class="">';
        echo '<a href="/form/' . $frm_id . '" class="btn btn-info">Crear nuevo</a>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-warning">';
        echo 'Ya no puede crear más formularios de este tipo';
        echo '</div>';
    }
    echo '</ol>';

    //echo ' maximo '.$formulario['frm_umbral_maximo'];
    //echo ' count finalizado ' . $count_finalizado;
    //echo ' - ';

    $porcentaje_avance_formulario = 0;
    if (!empty($formulario['frm_umbral_maximo']) && (int)$formulario['frm_umbral_maximo'] === $count_finalizado) {
        //se finalizaron todos los necesarios
        $porcentaje_avance_formulario = 100;
    } else if ($count > $formulario['frm_umbral_minimo']) {
        //llenados mas de los necesarios
        $porcentaje_avance_formulario = round($avance_formulario / $count ,0);
    } else {
        //aun no se alcance el mínimo
        $porcentaje_avance_formulario = round($avance_formulario / $formulario['frm_umbral_minimo'] ,0);

    }
    //echo "Avance del formulario:".$porcentaje_avance_formulario;
    echo "<script>document.getElementById('avance_parcial_{$formulario[frm_id]}').innerHTML='$porcentaje_avance_formulario'</script>";

    $avance_total += $porcentaje_avance_formulario;


    //echo '</li>';
}

$porcentaje_avance_total = round($avance_total / 7,0);

q("UPDATE esamyn.esa_evaluacion SET eva_porcentaje_avance=$porcentaje_avance_total WHERE eva_id=$eva_id");
//echo "Avance Total: " . $porcentaje_avance_total;
echo '</ul>';
//q("INSERT INTO esamyn.esa_tipo_pregunta(tpp_clave, tpp_etiqueta) VALUES('multitexto', 'multitexto')");
?>

<script>
document.getElementById('avance_general').innerHTML = <?=$porcentaje_avance_total?>;
</script>
