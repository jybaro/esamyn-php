<?php


//header('Content-Type: application/json');
$error = array();
$respuestas = array();

if (isset($args[0]) && !empty($args[0]) && isset($args[1]) && !empty($args[1])) {
    $tabla = $args[0];
    if ($tabla == 'establecimiento_salud') {
        $establecimiento_salud = $args[1];
        $result = q("
            SELECT * 
            FROM esamyn.esa_permiso_ingreso, esamyn.esa_usuario, esamyn.esa_rol 
            WHERE 
            pei_usuario = usu_id
            AND usu_rol = rol_id
            AND usu_borrado IS NULL
            AND pei_establecimiento_salud=$establecimiento_salud");

        if ($result) {
            foreach($result as $r){
                $respuesta = array();
                $respuesta['nombre'] = $r['usu_apellidos'] . ' ' . $r['usu_nombres'] . ' ('.$r['usu_cedula'].') - ' . $r['rol_nombre'];
                $respuesta['id'] = $r['pei_id']; 
                $respuesta['usuario'] = $r['pei_usuario']; 
                $respuesta['establecimiento_salud'] = $r['pei_establecimiento_salud']; 

                $respuestas[] = $respuesta;
            }
        } else {
            $error[] = array('sinDatos' => 'No hay datos');
        }
    } else if ($tabla == 'usuario') {
        $usuario = $args[1];
        $result = q("
            SELECT * 
            FROM esamyn.esa_permiso_ingreso, esamyn.esa_establecimiento_salud
            WHERE 
            pei_establecimiento_salud = ess_id
            AND ess_borrado IS NULL
            AND pei_usuario=$usuario
            ORDER BY ess_nombre
        ");
            

        if ($result) {
            foreach($result as $r){
                $respuesta = array();
                $respuesta['nombre'] = $r['ess_nombre'] . ' ('.$r['ess_unicodigo'].')';
                $respuesta['id'] = $r['pei_id']; 
                $respuesta['usuario'] = $r['pei_usuario']; 
                $respuesta['establecimiento_salud'] = $r['pei_establecimiento_salud']; 

                $respuestas[] = $respuesta;
            }
        } else {
            $error[] = array('sinDatos' => 'No hay datos');
        }
    } 
}
echo json_encode(array('respuestas' => $respuestas, 'error' => $error));
