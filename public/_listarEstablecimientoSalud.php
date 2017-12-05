<?php

$respuestas = array();
$error = array();
$query = $args[0];

$extension_minima = 2;

if (strlen($query) >= $extension_minima) {

    $result = q("SELECT * FROM esamyn.esa_establecimiento_salud WHERE ess_borrado IS NULL AND (ess_nombre ILIKE '%$query%' OR ess_unicodigo LIKE '%$query%') ORDER BY ess_nombre");

    if ($result) {
        foreach($result as $r){
            $respuesta = array('id' => $r['ess_id'], 'name' => ($r['ess_nombre'] . ' (' . $r['ess_unicodigo'] . ')'));
            $respuestas[] = $respuesta; 
        }
    } else {
        $error[] = array('sinresultados' => 'No hay resultados para la consulta -'.$query.'-.');
    }
} else {
    $error[] = array('muycorto' => 'La extension de la consulta -'.$query.'- es '.strlen($query).', muy corta como para buscarla. La extension minima de la consulta debe ser '.$extension_minima.'.');
}
echo json_encode(array('lista' => $respuestas, 'error' => $error));
