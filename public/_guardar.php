<?php

//header('Content-Type: application/json');

//echo 'desde ws rest: ';
//var_dump($_POST);
//
function p_formatear_valor_sql($raw, $tipo = 'text'){
    if ($raw === null || $raw === '' || $raw === 'null') {
        $result = 'null';
    } else if (strpos($raw, '(') !== false && substr($raw, -1) == ')') {
        //es funcion
        $result = $raw;
    //} else if (is_numeric($raw)) {
    } else if ($tipo == 'text' || strpos('char', $tipo) !== false) {
        //es texto 
        //$texto = htmlentities($raw);
        
        //$texto = ($raw);
        //$result = "'$texto'";
        $result = pg_escape_literal($raw);
    } else {
        //por defecto no lleva comillas
        //$result = $raw;
        $result = pg_escape_string($raw);
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

    //var_dump($dataset_json);
    //var_dump($dataset);

    $dataset = is_array($dataset) ? $dataset : array($dataset);

    $respuesta_dataset = array();

    $metadata = q("SELECT *
        FROM information_schema.columns
        WHERE table_schema = 'esamyn'
        AND table_name   = '$tabla'
        ORDER BY data_type, is_nullable, column_name
        ");
    //var_dump($metadata);
    $campo_id = null;
    
    $tipos = array();

    foreach($metadata as $columna) {
       // echo $columna['column_name'];
        $tipos[substr($columna['column_name'],4)] = $columna['data_type']; 

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
            $sql_parejas = '';
            $glue = '';
            foreach ($data as $columna => $valor){
                if ($columna != 'id' && $columna != 'creado' && $columna != 'modificado') {
                    $valor_sql = p_formatear_valor_sql($valor, $tipos[$columna]);
                    $sql_parejas .= "{$glue}{$prefijo}{$columna}={$valor_sql}";
                    $glue = ',';
                }
            }
            if ($glue == ',') {
                //$sql_parejas .= "{$glue}{$prefijo}modificado=now()";
                $sql = "UPDATE esamyn.{$tabla} SET $sql_parejas WHERE {$prefijo}id = {$data[id]}";
            }
        } else {
            $sql_campos = '';
            $sql_valores = '';
            $glue = '';
            foreach ($data as $columna => $valor){
                if ($columna != 'id' && $columna != 'creado' && $columna != 'modificado') {
                    $sql_campos .= $glue.$prefijo.$columna;

                    $valor_sql = p_formatear_valor_sql($valor, $tipos[$columna]);
                    $sql_valores .= $glue.$valor_sql;

                    $glue = ',';
                }
            }
            if ($glue == ',') {
                $sql = "INSERT INTO esamyn.{$tabla} ({$sql_campos}) VALUES ({$sql_valores})";
            }
        }

        if ($sql != '') {
            $sql .= ' RETURNING *';
           // echo $sql;
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
} else {
    //echo "No hay nada";
    echo '["error":"sin datos"]';
}
