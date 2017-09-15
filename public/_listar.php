<?php


//echo 'desde ws rest: ';
//var_dump($args);
if (isset($args[0]) && !empty($args[0])) {
    $tabla = 'esa_'.$args[0];
    $id =  (isset($args[1]) && !empty($args[1]) && is_numeric($args[1])) ? $args[1] : null;

    $metadata = q("SELECT *
        FROM information_schema.columns
        WHERE table_schema = 'esamyn'
        AND table_name   = '$tabla'");
    //var_dump($metadata);
    $campo_id = null;
    $prefijo = '';
    $columnas = array();

    foreach($metadata as $columna) {
        //echo $columna['column_name'];
        $columnas[$columna['column_name']] = $columna;

        if (strpos($columna['column_name'], '_id') !== false) {
            $campo_id = $columna['column_name'];
            $prefijo = explode('_', $campo_id)[0] . '_';
        }
    }


    if (!empty($id)) {
        //echo 'CON ID: ' . $id . ' - ' . $campo_id;
        
        $respuesta_raw = q("SELECT * FROM esamyn.$tabla WHERE $campo_id = $id");
        //echo "SELECT * FROM esamyn.$tabla WHERE $campo_id = $id";
        
    } else {
        $where = '';
        $where_glue = ' WHERE ';
        $select_columns = '*';
        $limit = '';
        $offset = '';
        $orderby = '';

        $saltar = false;
        foreach ($args as $index => $arg) {
            if ($index > 0 && !$saltar) {
                if ($arg == 'count') {
                    $select_columns = 'COUNT(*) AS xxx_count';
                } else if(strpos($arg, 'limit-') === 0){
                    $limit = 'LIMIT ' . explode('-', $arg)[1];
                } else if(strpos($arg, 'offset-') === 0){
                    $offset = 'OFFSET ' . explode('-', $arg)[1];
                } else if(strpos($arg, 'orderby-') === 0){
                    $orderby = 'ORDER BY ';
                    $glue = '';
                    foreach(explode('-', $arg) as $index => $column) {
                        if ($index > 0){
                            $partes_column = explode('|', $column);
                            $orderby .= $glue. $prefijo . $partes_column[0];
                            $orderby .= (isset($partes_column[1]) && $partes_column[1] == 'asc') ? ' ASC' : ' DESC';

                            $glue = ',';
                        }
                    }
                } else if(!is_numeric($arg) && isset($args[$index+1]) && !empty($args[$index+1])) {
                    $campo = $prefijo . $arg;
                    $comillas = ($columnas[$campo]['data_type'] == 'text') ? "'" : "";
                    $valor = $args[$index+1];
                    //var_dump($args);
                    //echo '>' .($index+1) . '-' .$args[$index+1]. '<';
                    if ($valor == 'null'){
                        $where .= "$where_glue $campo IS NULL";
                    } else {
                        $where .= "$where_glue $campo =  $comillas{$valor}$comillas";
                    }

                    $where_glue = ' AND ';
                    $saltar = true;
                }
            } else {
                $saltar = false;
            }

        }
        //echo 'sin id';
        if ($select_columns != '') {
            $orderby = '';
            $limit = '';
            $offset = '';
        }
        $sql = "SELECT $select_columns FROM esamyn.$tabla $where $orderby $limit $offset";
        $sql = trim($sql);
        //echo $sql;
        $respuesta_raw = q($sql);
    }

    //var_dump($respuesta_raw);
    $respuesta = [];
    foreach($respuesta_raw as $key => $registro){
        $respuesta[$key] = [];
        foreach($registro as $k => $v){
            $respuesta[$key][substr($k, 4)] = $v;
        }
    }

    echo json_encode($respuesta);
}
