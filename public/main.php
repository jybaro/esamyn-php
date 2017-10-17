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

//var_dump(pg_fetch_all($result));
?>

<h1>Formularios ingresados</h1>
<p>Haga clic en el enlace para desplegar la encuesta.</p>
<?php
echo '<ul>';
$tree = array();
$ess_id = $_SESSION['ess_id'];

$formularios = empty($formularios) ? array() : $formularios;

foreach ($formularios as $formulario){
    $frm_id = $formulario['frm_id'];


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
            enc_establecimiento_salud = $ess_id
            AND NOT (
                res_valor_texto IS NULL 
                AND 
                res_valor_numero IS NULL 
                AND 
                res_valor_fecha IS NULL
            )
        ) AS count_respuesta 
        FROM 
        esamyn.esa_pregunta 
        WHERE 
        prg_formulario=$frm_id
        ");
    $result = q($sql);
    //echo '<pre>'.print_r($result, true).'</pre>';
    foreach ($result as $r){
        $preguntas[$r['prg_id']] = $r;
        $preguntas[$r['prg_id']]['hijos'] = array();
        $preguntas[$r['prg_id']]['padre'] = null;
        $preguntas[$r['prg_id']]['count'] = 0;
    }
    foreach ($result as $r){
        $preguntas[$r['prg_padre']]['hijos'][$r['prg_id']] = & $preguntas[$r['prg_id']];
        if (!empty($r['prg_padre'])) {
            $preguntas[$r['prg_id']]['padre'] = & $preguntas[$r['prg_padre']];
        } else {
            $preguntas[$r['prg_id']]['padre'] = & $preguntas[0];
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
    //echo '<li>';
    //echo '<a href="/form/' . $frm_id . '">';
    echo '<h4>';
    echo $formulario['frm_clave'] . '. ' . $formulario['frm_titulo'];
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
        ");

//var_dump($result);
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

        //$result_count = pg_query("SELECT COUNT(*) AS c FROM esamyn.esa_respuesta WHERE res_encuesta=$enc_id AND TRIM(res_valor_texto)<>'' UNION SELECT COUNT(*) AS c FROM esamyn.esa_pregunta WHERE prg_formulario=$frm_id");
        //echo("SELECT COUNT(*) AS 'count' FROM esamyn.esa_respuesta WHERE res_encuesta=$enc_id AND TRIM(res_valor_texto)<>'' UNION SELECT COUNT(*) AS 'count' FROM esamyn.esa_pregunta WHERE prg_formulario=$frm_id");
        //$count_respuestas = pg_fetch_array($result_count, 0)[0];
        //$count_preguntas = pg_fetch_array($result_count, 1)[0];
        //
        /*
        $sql = "
            SELECT * FROM 
            esamyn.esa_pregunta
            WHERE 
            prg_formulario=$frm_id
            ";
        $result = q($sql);
        $preguntas = array();

        foreach($result as $r){
            $preguntas[$r['prg_id']] = $r;
            $preguntas[$r['prg_id']]['hijos'] = array();
            $preguntas[$r['prg_id']]['respuesta'] = null;
            $preguntas[$r['prg_id']]['count'] = 0;

        }
        foreach($result as $r){
            $id = $r['prg_id'];
            $padre = $r['prg_padre'];
            $preguntas[$padre]['hijos'][$id] = & $preguntas[$id];

            if (!empty($padre)) {
                $preguntas[$id]['padre'] = & $preguntas[$padre];
            } else {
                $preguntas[$id]['padre'] = null;
            }
        }

        $sql = "
            SELECT * FROM
            esamyn.esa_respuesta
            WHERE
            res_encuesta=$enc_id
            ";
        $result = q($sql);
        foreach($result as $r){
            if(
                !empty($r['res_valor_texto'])
                ||
                !empty($r['res_valor_numero'])
                ||
                !empty($r['res_valor_fecha'])
            ){
                //var_dump($r);
                $preguntas[$r['res_pregunta']]['respuesta'] = $r;
                $padre = & $preguntas[$r['res_pregunta']]['padre'];
                while (!empty($padre)) {
                    $padre['count'] ++;
                    $padre = & $padre['padre'];
                }
            }
        }

        //echo '<ul>';
        $count_respuestas = 0;
        foreach($preguntas as $p){
            if (is_array($p['padre']) && empty($p['padre']['padre'])){
                //echo '<li>';
                //echo $p['count'].'-'.$p['prg_texto'];
                //echo '</li>';
                if ($p['count'] > 0) {
                    $count_respuestas ++;
                }
            }
        }
        //echo '</ul>';


        $sql = "
            SELECT 
            COUNT(*) 
            FROM 
            esamyn.esa_pregunta AS p1,
            esamyn.esa_pregunta AS p2 
            WHERE 
            p1.prg_padre = p2.prg_id
            AND
            p2.prg_padre IS NULL
            AND
            p2.prg_formulario=$frm_id
            ";
        $count_preguntas = q($sql)[0]['count'];
         */
        echo ' ';
        if ($encuesta['enc_finalizada']) { 
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

    if (empty($formulario['frm_umbral_maximo']) || $count_finalizado <= $formulario['frm_umbral_maximo']) {
        echo '<div>';
        echo '<a href="/form/' . $frm_id . '" class="btn btn-info">Crear nuevo</a>';
        echo '</div>';
    } else {
        echo '<div>';
        echo 'Ya no puede crear más formularios de este tipo';
        echo '</div>';
    }
    echo '</ol>';


    //echo '</li>';
}
echo '</ul>';
//q("INSERT INTO esamyn.esa_tipo_pregunta(tpp_clave, tpp_etiqueta) VALUES('multitexto', 'multitexto')");
?>

