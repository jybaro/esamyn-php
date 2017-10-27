<?php


//echo 'desde ws rest: ';
//var_dump($_POST);
//
function p_formatear_valor_sql($raw){
    if (strpos($raw, '(') !== false && substr($raw, -1) == ')') {
        //es funcion
        $result = $raw;
    } else if (is_numeric($raw)) {
        //es numero
        $result = $raw;
    } else {
        //por defecto es texto
        $texto = htmlentities($raw);
        $result = "'$texto'";
    }

    return $result;
}

if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

if (isset($args[0]) && !empty($args[0]) && !empty($dataset_json)) {
    $tabla = 'esa_'.$args[0];
    $dataset = json_decode($dataset_json);
    $dataset = is_array($dataset) ? $dataset : array($dataset);


    $respuesta_dataset = array();

    $metadata = q("SELECT *
        FROM information_schema.columns
        WHERE table_schema = 'esamyn'
        AND table_name   = '$tabla'");
    //var_dump($metadata);
    $campo_id = null;
    
    foreach($metadata as $columna) {
        //echo $columna['column_name'];
        if (strpos($columna['column_name'], '_id') !== false) {
             $campo_id = $columna['column_name'];
        }
    }
    $prefijo = explode('_', $campo_id)[0] . '_';
    $sql = '';
    $respuesta = '';


    foreach($dataset as $data) {
        $data = (array)$data; 
        if (isset($data['id']) && !empty($data['id'])) {
            $sql = "DELETE FROM esamyn.{$tabla} WHERE {$prefijo}id = {$data[id]}";
        }

        if ($sql != '') {
            $sql .= ' RETURNING *';
            //echo $sql;
            $respuesta = [];
            $r = q($sql);
            foreach($r[0] as $k => $v){
                $respuesta[substr($k, 4)] = $v;
            }
        }

        //echo json_encode($respuesta);
        $respuesta_dataset[] = $respuesta;
    }
    echo json_encode($respuesta_dataset);
}
