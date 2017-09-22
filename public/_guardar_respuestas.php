<?php


//echo 'desde ws rest: ';
//var_dump($_POST);
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

        $enc_id = (int)(isset($_POST['enc_id']) ? $_POST['enc_id'] : '-1');

        if ($enc_id === -1) {
            //No hay encuesta padre, se crea nueva encuesta:
            //
            echo '-INSERT-';
            $enc_id = q("INSERT INTO esamyn.esa_encuesta(enc_formulario) VALUES ($frm_id) RETURNING enc_id")[0]['enc_id'];
            $count = 0;

            foreach($respuestas as $respuesta) {
                //echo "[INSERT resp {$respuesta->id}: {$respuesta->valor}]";
                $prg_id = $respuesta->name;
                $valor = trim($respuesta->value);

                $pregunta = $preguntas[$prg_id];


                switch($pregunta['tpp_clave']){
                case 'texto':
                    $campo_valor = 'texto';
                    $valor = "'" . $valor . "'";
                    break;
                case 'multitexto':
                    $campo_valor = 'texto';
                    $valor = "'" . $valor . "'";
                    break;
                case 'numero':
                    $campo_valor = 'numero';
                    $valor = $valor;
                    break;
                case 'fecha':
                    $campo_valor = 'fecha';
                    $valor = "to_timestamp('".$valor."', 'YYYY-MM-DD hh24:mi:ss')";
                    break;
                case 'hora':
                    $campo_valor = 'fecha';
                    $valor = "to_timestamp('".$valor."', 'hh24:mi:ss')";
                    break;
                case 'booleano':
                    $campo_valor = 'booleano';
                    $valor = $valor;
                    break;
                case 'email':
                    $campo_valor = 'texto';
                    $valor = "'".$valor."'";
                    break;
                default:
                    $campo_valor = '';
                    break;
                }
                $valor = (trim($respuesta->value) == '') ? 'null' : $valor;

                if ($campo_valor != ''){
                    $sql = "INSERT INTO esamyn.esa_respuesta(res_encuesta, res_pregunta, res_valor_$campo_valor) VALUES ($enc_id, $prg_id, $valor) RETURNING res_id";
                    $res_id = q($sql);
                    if ($res_id){
                        $res_id = $res_id[0]['res_id'];
                        echo "[R$res_id:$valor(P$prg_id)]";
                        $count++;
                    } else {
                        echo "[ERROR: $sql]";
                    }
                }
            }
            echo "[$count respuestas insertadas]";
        } else {
            echo '-UPDATE-';
            //Hay encuesta padre, se la actualiza:
            foreach($respuestas as $respuesta) {
                $res_id = q("UPDATE esamyn.esa_respuesta SET res_modificado=now(), res_valor_texto='{$respuesta->valor}' WHERE res_pregunta={$respuesta->id} AND res_encuesta=$enc_id  RETURNING res_id")[0]['res_id'];
            }
            $result = q("UPDATE esamyn.esa_encuesta SET enc_fecha_final=now(), enc_modificado=now() WHERE enc_id=$enc_id RETURNING enc_id");
        }
    }
}
