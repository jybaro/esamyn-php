<?php


//echo 'desde ws rest: ';
//var_dump($_POST);
if (isset($_POST['respuestas_json']) && !empty($_POST['respuestas_json'])) {
    $respuestas = json_decode($_POST['respuestas_json']);
    echo 'Se recibieron '.count($respuestas).' respuestas';

    if (count($respuestas) > 0) {

        require_once('../private/bdd.php');

        $id_primera_pregunta = $respuestas[0]->id;

        $result = pg_query($conn, "SELECT prg_formulario FROM esamyn.esa_pregunta WHERE prg_id=$id_primera_pregunta");
        $frm_id = pg_fetch_result($result, 'prg_formulario');

        echo "[frm_id:$frm_id]";

        $enc_id = (int)(isset($_POST['enc_id']) ? $_POST['enc_id'] : '-1');

        if ($enc_id === -1) {
            //No hay encuesta padre, se crea nueva encuesta:
            //
            $result = pg_query($conn, "INSERT INTO esamyn.esa_encuesta(enc_formulario) VALUES ($frm_id) RETURNING enc_id");
            $enc_id = pg_fetch_result($result, 'enc_id');

            foreach($respuestas as $respuesta) {
                //echo "[INSERT resp {$respuesta->id}: {$respuesta->valor}]";
                $result = pg_query($conn, "INSERT INTO esamyn.esa_respuesta(res_encuesta, res_pregunta, res_valor_texto) VALUES ($enc_id, {$respuesta->id}, '{$respuesta->valor}') RETURNING res_id");
            }
        } else {
            //Hay encuesta padre, se la actualiza:
            foreach($respuestas as $respuesta) {
                $result = pg_query($conn, "UPDATE esamyn.esa_respuesta SET res_modificado=now(), res_valor_texto='{$respuesta->valor}' WHERE res_pregunta={$respuesta->id} AND res_encuesta=$enc_id");
            }
            $result = pg_query($conn, "UPDATE esamyn.esa_encuesta SET enc_fecha_final=now(), enc_modificado=now() WHERE enc_id=$enc_id");
        }
    }
}
