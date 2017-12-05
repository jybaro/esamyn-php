<?php
$eva_id = $_SESSION['evaluacion']['eva_id']; 
$ess_id = $_SESSION['ess_id'];
$usu_id = $_SESSION['usu_id'];
$warning = '';
//echo 'desde ws rest: ';
//var_dump($_POST);
ob_start();
if (isset($_POST['respuestas_json']) && !empty($_POST['respuestas_json'])) {
    $respuestas = json_decode($_POST['respuestas_json']);
    echo 'Se recibieron '.count($respuestas).' respuestas';

    if (count($respuestas) > 0) {

        $primera_pregunta = $respuestas[0]->name;

        $frm_id = q("SELECT prg_formulario FROM esamyn.esa_pregunta WHERE prg_id=$primera_pregunta")[0]['prg_formulario'];



        $count_encuestas = q("SELECT COUNT(*) FROM esamyn.esa_encuesta WHERE enc_borrado IS NULL AND enc_evaluacion=$eva_id AND enc_formulario=$frm_id AND enc_establecimiento_salud=$ess_id AND enc_finalizada=1")[0]['count'];
        $umbral_maximo = q("SELECT frm_umbral_maximo FROM esamyn.esa_formulario WHERE frm_id=$frm_id")[0]['frm_umbral_maximo'];




        echo "[frm_id:$frm_id]";
        $prg = q("SELECT * FROM esamyn.esa_pregunta, esamyn.esa_tipo_pregunta WHERE prg_tipo_pregunta = tpp_id AND prg_formulario =$frm_id");
        $preguntas = array();
        foreach($prg as $p){
            $preguntas[$p['prg_id']] = $p;
        };
        echo '[preguntas:'.count($preguntas).']';


        if (empty($umbral_maximo) || $count_encuestas < $umbral_maximo) {
            $finalizada = (int)(isset($_POST['finalizada']) ? $_POST['finalizada'] : '0');
        } else {
            //$finalizada = false;
            $finalizada = 0;
            //$warning = "Ya no se puede finalizar esta encuesta, pues se ha alcanzado la cantidad máxima para este formulario ($count_encuestas - $umbral_maximo)";  
            $warning = "Ya no se puede finalizar esta encuesta, pues se ha alcanzado la cantidad máxima para este formulario ($umbral_maximo)";  
        }

        $enc_id = (int)(isset($_POST['enc_id']) ? $_POST['enc_id'] : '-1');

        $es_nuevo = false;
        if ($enc_id === -1) {
            //No hay encuesta padre, se crea nueva encuesta:
            //
            echo '-INSERT-';
            $sql = "INSERT INTO esamyn.esa_encuesta(
                enc_formulario,
                enc_usuario,
                enc_evaluacion,
                enc_establecimiento_salud
            ) VALUES (
                $frm_id,
                $usu_id,
                $eva_id,
                $ess_id
            ) RETURNING enc_id";

            echo $sql;

            $enc_id = q($sql)[0]['enc_id'];

            $es_nuevo = true;
        } else {
            //Ya exite la encuesta, SI NO ESTÁ FINALIZADA  se borran todas sus respuestas anteriores:
            $enc_finalizada = q("SELECT enc_finalizada FROM esamyn.esa_encuesta WHERE enc_id=$enc_id")[0]['enc_finalizada'];
            if ($enc_finalizada == 0) {
                $result = q("DELETE FROM esamyn.esa_respuesta WHERE res_encuesta=$enc_id");
            } else {
                //no guarda si está finalizada, se para la ejecucion.
                return;
            }
        }
        $count = 0;
        $sql_insert_total = '';
        $glue_insert_total = '';

        foreach($respuestas as $respuesta) {
            //echo "[INSERT resp {$respuesta->id}: {$respuesta->valor}]";
            $prg_id = $respuesta->name;
            $valor = trim($respuesta->value);

            $pregunta = $preguntas[$prg_id];

            $valores_a_insertar = array();

            $tipo = empty($pregunta['tpp_clave']) ? 'texto' : $pregunta['tpp_clave'];

            switch($tipo){
            case 'texto':
                $campo_valor = 'texto';
                $valor = "'" . $valor . "'";
                $valores_a_insertar[$prg_id] = $valor;
                break;
            case 'multitexto':
                $campo_valor = 'texto';
                $valor = "'" . $valor . "'";
                $valores_a_insertar[$prg_id] = $valor;
                break;
            case 'numero':
                $campo_valor = 'numero';
                $valor = $valor;
                $valores_a_insertar[$prg_id] = $valor;
                break;
            case 'fecha':
                $campo_valor = 'fecha';
                $valor = "to_timestamp('".$valor."', 'YYYY-MM-DD hh24:mi:ss')";
                $valores_a_insertar[$prg_id] = $valor;
                break;
            case 'hora':
                $campo_valor = 'fecha';
                $valor = "to_timestamp('".$valor."', 'hh24:mi:ss')";
                $valores_a_insertar[$prg_id] = $valor;
                break;
            case 'booleano':
                $campo_valor = 'booleano';
                $valor = $valor;
                $valores_a_insertar[$prg_id] = $valor;
                break;
            case 'email':
                $campo_valor = 'texto';
                $valor = "'".$valor."'";
                $valores_a_insertar[$prg_id] = $valor;
                break;
            case 'radio':
                $campo_valor = 'texto';
                $sql = "SELECT prg_id, prg_texto FROM esamyn.esa_pregunta WHERE prg_padre=$prg_id";
                echo "[RADIO:$sql]";
                $opciones = q($sql);
                if ($opciones) {
                    foreach($opciones as $opcion){
                        $opcion_valor = ($opcion['prg_texto'] === $valor) ? "'".$valor."'" : 'null';
                        $valores_a_insertar[$opcion['prg_id']] = $opcion_valor;
                        echo "[RADIO OPCION {$opcion[prg_id]}:$opcion_valor]";
                    }
                } else {
                    echo "[ERROR:$sql]";
                }

                break;
            default:
                $campo_valor = '';
                break;
            }

            foreach ($valores_a_insertar as $prg_id => $valor) {
                $res_valor = array(
                    'texto' => 'null',
                    'numero' => 'null',
                    'fecha' => 'null'
                );
                $res_valor[$campo_valor] = (trim($respuesta->value) == '') ? 'null' : $valor;
                $sql_insert_total .= $glue_insert_total . "($enc_id, $prg_id, {$res_valor[texto]}, {$res_valor[numero]}, {$res_valor[fecha]})";
                $glue_insert_total = ',';
                $count++;

                /*
                $sql_insert = "
                    INSERT INTO esamyn.esa_respuesta(
                        res_encuesta, 
                        res_pregunta, 
                        res_valor_$campo_valor
                    ) VALUES (
                        $enc_id, 
                        $res_pregunta, 
                        $res_valor
                    ) RETURNING res_id";

                $sql_update = "UPDATE esamyn.esa_respuesta SET 
                    res_modificado=now(),
                        res_valor_$campo_valor=$res_valor
                        WHERE res_pregunta=$res_pregunta 
                        AND res_encuesta=$enc_id  
                        RETURNING res_id";

                $es_guardado = false;

                //$result = q($sql_update);
                //if ($result) {
                //    $es_guardado = true;
                //} else {
                $result = q($sql_insert);
                if ($result) {
                    $es_guardado = true;
                }
                //}

                if ($es_guardado){
                    $res_id = $result[0]['res_id'];
                    echo "[R$res_id:$res_valor(P$res_pregunta)]";
                    $result = q("UPDATE esamyn.esa_encuesta 
                        SET enc_fecha_final=now(),
                            enc_modificado=now(),
                    enc_finalizada=$finalizada
                    WHERE enc_id=$enc_id 
                    RETURNING enc_id");
                } else {
                    //echo "[ERROR: $sql_update]";
                    echo "[ERROR: $sql_insert]";
                }
                 */
            }
        }
        echo "[$count respuestas ".($es_nuevo?'insertadas':'actualizadas')."]";
        $sql_insert_total = "
                    INSERT INTO esamyn.esa_respuesta(
                        res_encuesta, 
                        res_pregunta, 
                        res_valor_texto,
                        res_valor_numero,
                        res_valor_fecha
                    ) VALUES $sql_insert_total RETURNING *";

        echo "[SQL INSERT TODAL:$sql_insert_total]";
        $result = q($sql_insert_total);
        if ($result) {
            /////INICIO CALCULO PORCENTAJE DE AVANCE DE FORM


            $preguntas = array(0=>array(
                'count' => 0,
                'padre' => null,
                'count_respuesta' => 0
            ));
            $sql = ("SELECT *
                ,(
                    SELECT 
                    COUNT(*) 
                    FROM 
                    esamyn.esa_respuesta
                    ,esamyn.esa_encuesta 
                    WHERE
                    enc_borrado IS NULL
                    AND 
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
            //$enc_id = $encuesta['enc_id'];
            $enc_usuario = $encuesta['enc_usuario'];
            $creado_por = $encuesta['nombre'];

            if ($encuesta['enc_finalizada']) { 
                $enc_porcentaje_avance = 100;
                $avance_formulario += 100;
                $count_finalizado ++;
            } else {
                $enc_porcentaje_avance = round($count_preguntas_respondidas * 100 / $count_preguntas, 0);
            }

            $sql = ("UPDATE esamyn.esa_encuesta 
                SET enc_fecha_final=now(),
                    enc_modificado=now(),
            enc_finalizada=$finalizada,
            enc_porcentaje_avance=$enc_porcentaje_avance,
            enc_numero_preguntas=$count_preguntas,
            enc_numero_preguntas_respondidas=$count_preguntas_respondidas
            WHERE enc_id=$enc_id 
            RETURNING enc_id");
            echo "[$sql]";
            $result = q($sql);
            //FIN DE CALCULO PORCENTAJE DE AVANCE DE FORM
        } else {
            echo "[ERROR:$$sql_insert_total]";
        }

        //} else {

        //    echo "/nSe han alcanzado el máximo número de encuestas para este formulario ($umbral_maximo), por lo que no se puede finalizar el formulario.";
        //}
    }
}
$contenido = ob_get_contents();
$contenido = str_replace('"', "'", $contenido);
$contenido = str_replace("\n", " ", $contenido);
ob_end_clean();
echo "{\"warning\":\"$warning\", \"log\":\"$contenido\", \"enc_id\":\"$enc_id\"}";
