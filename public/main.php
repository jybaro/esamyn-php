<?php

//require_once('../private/config.php');
//require_once('../private/bdd.php');

$usu_id = $_SESSION['usu_id'];
$rol_id = $_SESSION['rol'];
$result = q("SELECT usu_cedula, usu_password FROM esamyn.esa_usuario WHERE usu_id=$usu_id");

if ($result) {
    $cedula = $result[0]['usu_cedula'];
    $password = $result[0]['usu_password'];
    if (md5($cedula) == $password){
        echo "<div class='alert alert-warning'>Su contrase&ntilde;a actual es su n&uacute;mero de c&eacute;dula, por favor <strong><a href='/cambiarClave'>cambie su contrase&ntilde;a</a></strong> lo m&aacute;s pronto posible por seguridad.</div>";
    }
} else {
    echo "<script>alert('ERROR: Usuario no encontrado. Vuelva a ingresar con su clave de acceso.');window.location.replace('/login');</script>";
    return;
}

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

    $filtro = ' AND enc_borrado IS NULL ';
    if ($rol_id == 1) {
        $filtro = '';
    } 

    $encuestas = q("
        SELECT *,
        (
            SELECT 
            usu_nombres || ' '||usu_apellidos AS nombre 
            FROM 
            esamyn.esa_usuario 
            WHERE 
            usu_id = enc_usuario
        ) AS nombre 
        FROM 
        esamyn.esa_encuesta 
        WHERE 
        enc_formulario = $frm_id 
        AND 
        enc_establecimiento_salud=$ess_id
        AND 
        enc_evaluacion=$eva_id
        $filtro
        ORDER BY enc_creado
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
/*
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
*/
        $count++;
        $enc_id = $encuesta['enc_id'];
        echo '<li>';
        $css_clase = '';
        if (!empty($encuesta['enc_borrado'])) {
            $css_clase = 'alert-danger';
        }
        echo "<div id='encuesta_$enc_id' class='alert $css_clase'>";
        echo '<a href="/form/' . $frm_id . '/' . $enc_id . '">';
        //echo $encuesta['enc_creado'];
        $fecha = p_formatear_fecha($encuesta['enc_creado']);
        $enc_usuario = $encuesta['enc_usuario'];
        $creado_por = $encuesta['nombre'];
        /*
        $creado_por = q("
            SELECT 
            usu_nombres || ' '||usu_apellidos AS nombre 
            FROM 
            esamyn.esa_usuario 
            WHERE 
            usu_id=$enc_usuario
            ")[0]['nombre'];
         */

        echo 'Creado por ' . $creado_por . ' el ' . $fecha;
        echo '</a>';

        echo ' ';
        $estado_llenado = '';
        if ($encuesta['enc_finalizada']) { 
            if (empty($encuesta['enc_borrado'])) {
                $avance_formulario += 100;
            }

            //echo " (finalizada)"; 
            if (empty($encuesta['enc_borrado'])) {
                $count_finalizado ++;
            }
            $estado_llenado .= "<div style='
                width:100px;
                background-color:#9F9;
                border:solid 1px #000;
                text-align:center;
                display:inline-block;
                '>Finalizada</div>";
        } else {
            //$porcentaje = round($count_preguntas_respondidas * 100 / $count_preguntas, 0);
            $porcentaje = $encuesta['enc_porcentaje_avance'];
            $count_preguntas = $encuesta['enc_numero_preguntas'];
            $count_preguntas_respondidas = $encuesta['enc_numero_preguntas_respondidas'];
            //$avance_formulario += $porcentaje/100;
            $xpos = $porcentaje - 100;
            $estado_llenado .= "<div style='
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
        echo str_replace("\n", '', $estado_llenado);

        if ($rol_id == 1) {
            $css_borrar = '';
            $css_recuperar = '';

            if (empty($encuesta['enc_borrado'])) {
                $css_recuperar = 'hidden';
            } else {
                $css_borrar = 'hidden';
            }
            echo '<button id="borrar_'.$enc_id.'" class="btn btn-danger '.$css_borrar.'" onclick="p_borrar(\'borrar\', '.$enc_id.')" ><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Borrar</button>';
            echo '<span id="recuperar_'.$enc_id.'" class=" '.$css_recuperar.'"><span class="badge">BORRADO</span> <button class="btn btn-success" onclick="p_borrar(\'recuperar\', '.$enc_id.')" ><span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span> Recuperar</button></span>';
        }

        echo '</div>';
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
    if (!empty($formulario['frm_umbral_maximo']) && (int)$formulario['frm_umbral_maximo'] <= $count_finalizado) {
        //se finalizaron todos los necesarios
        $porcentaje_avance_formulario = 100;
    } else if ($count_finalizado > $formulario['frm_umbral_minimo']) {
        //llenados mas de los necesarios
        //$porcentaje_avance_formulario = round($avance_formulario / $count_finalizado ,0);
        $porcentaje_avance_formulario = 100;
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
<?php if($rol_id == 1): ?>
function p_borrar(accion, enc_id){
    var dataset_json = {'id':enc_id};
    dataset_json[accion] = accion;

    console.log('dataset_json', dataset_json);

    $.ajax({
    url: '_borrarEncuesta',
        type: 'POST',
        //dataType: 'json',
        data: JSON.stringify(dataset_json),
        //contentType: 'application/json'
    }).done(function(data){
        console.log(accion + ' OK, data:', data);
        //data = eval(data)[0];
        data = JSON.parse(data);
        data = data[0];
        console.log('eval data:', data);
        //$('#nombre_' + data['id']).parent().parent().remove();
        if (data['ERROR']) {
            alert(data['ERROR']);
        } else {
            $('#encuesta_' + data['id']).removeClass('alert alert-success alert-danger');
            if (accion == 'borrar') {
                $('#encuesta_' + data['id']).addClass('alert alert-danger');
                $('#recuperar_' + data['id']).removeClass('hidden');
                $('#borrar_' + data['id']).addClass('hidden');
            } else {
                $('#encuesta_' + data['id']).addClass('alert alert-success');
                $('#recuperar_' + data['id']).addClass('hidden');
                $('#borrar_' + data['id']).removeClass('hidden');
            }
        }

    }).fail(function(xhr, err){
        console.error('ERROR AL '+accion, xhr, err);
        alert('Hubo un error al '+accion+', verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
        //$('#modal').modal('hide');
    });

}
<?php endif; ?>
</script>
