<?php


//echo 'desde ws rest: ';
//var_dump($_POST);
ob_start();
if (isset($_POST['respuestas_json']) && !empty($_POST['respuestas_json'])) {
    $respuestas = json_decode($_POST['respuestas_json']);
    echo 'Se recibieron '.count($respuestas).' respuestas';

    if (count($respuestas) > 0) {

        $primera_pregunta = $respuestas[0]->name;

        $frm_id = q("SELECT prg_formulario FROM esamyn.esa_pregunta WHERE prg_id=$primera_pregunta")[0]['prg_formulario'];


        echo "[frm_id:$frm_id]";
        $prg = q("SELECT * FROM esamyn.esa_pregunta, esamyn.esa_tipo_pregunta WHERE prg_tipo_pregunta = tpp_id AND prg_formulario =$frm_id");
        $preguntas = array();
        foreach($prg as $p){
            $preguntas[$p['prg_id']] = $p;
        };
        echo '[preguntas:'.count($preguntas).']';

        $finalizada = (int)(isset($_POST['finalizada']) ? $_POST['finalizada'] : '0');
        $enc_id = (int)(isset($_POST['enc_id']) ? $_POST['enc_id'] : '-1');

        $es_nuevo = false;
        if ($enc_id === -1) {
            //No hay encuesta padre, se crea nueva encuesta:
            //
            echo '-INSERT-';
            $ess_id = $_SESSION['ess_id'];
            $usu_id = $_SESSION['usu_id'];
            $sql = "INSERT INTO esamyn.esa_encuesta(
                enc_formulario,
                enc_usuario,
                enc_establecimiento_salud
            ) VALUES (
                $frm_id,
                $usu_id,
                $ess_id
            ) RETURNING enc_id";

            echo $sql;

            $enc_id = q($sql)[0]['enc_id'];

            $es_nuevo = true;
        } else {
            //Ya exite la encuesta, se borran todas sus respuestas anteriores:
            $result = q("DELETE FROM esamyn.esa_respuesta WHERE res_encuesta=$enc_id");
        }
        $count = 0;

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

            foreach ($valores_a_insertar as $res_pregunta => $res_valor) {
                $res_valor = (trim($respuesta->value) == '') ? 'null' : $res_valor;
                $sql_insert = "INSERT INTO esamyn.esa_respuesta(
                    res_encuesta, 
                    res_pregunta, 
                    res_valor_$campo_valor
                ) VALUES (
                    $enc_id, 
                    $res_pregunta, 
                    $res_valor
                ) RETURNING res_id";

                $sql_update= "UPDATE esamyn.esa_respuesta SET 
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
                    $count++;
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
            }
        }
        echo "[$count respuestas ".($es_nuevo?'insertadas':'actualizadas')."]";
    }
}
$contenido = ob_get_contents();
$contenido = str_replace('"', "'", $contenido);
$contenido = str_replace("\n", " ", $contenido);
ob_end_clean();
echo "{\"log\":\"$contenido\", \"enc_id\":\"$enc_id\"}";
