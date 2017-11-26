<?php


//header('Content-Type: application/json');
$error = array();
$respuestas = array();
$establecimiento_salud = $args[0];
$result = q("
    SELECT * 
FROM esamyn.esa_permiso_ingreso, esamyn.esa_usuario 
WHERE 
pei_usuario = usu_id
AND usu_borrado IS NULL
AND pei_establecimiento_salud=$establecimiento_salud");

if ($result) {
    foreach($result as $r){
        $respuesta = array();
        $respuesta['nombre'] = $r['usu_apellidos'] . ' ' . $r['usu_nombres'] . ' ('.$r['usu_cedula'].')';
        $respuesta['id'] = $r['pei_id']; 
        $respuesta['usuario'] = $r['pei_usuario']; 
        $respuesta['establecimiento_salud'] = $r['pei_establecimiento_salud']; 

        $respuestas[] = $respuesta;
    }
} else {
    $error[] = array('sinDatos' => 'No hay datos');
}

echo json_encode(array('respuestas' => $respuestas, 'error' => $error));
