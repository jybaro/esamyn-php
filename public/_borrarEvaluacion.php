<?php

//header('Content-Type: application/json');

$eva_id = $_SESSION['eva_id'];
if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);
    if (isset($dataset->id) && !empty($dataset->id)) {
        $id = ( (isset($dataset->id) && !empty($dataset->id)) ? $dataset->id : null);

        if (isset($dataset->borrar) && !empty($dataset->borrar)) {
            $result = q("UPDATE esamyn.esa_evaluacion SET eva_borrado=now() WHERE eva_id=$id RETURNING *");
        } else if (isset($dataset->recuperar) && !empty($dataset->recuperar)) {
            $result = q("UPDATE esamyn.esa_evaluacion SET eva_borrado=null WHERE eva_id=$id RETURNING *");
        }

    } else {
        $result = array(array('ERROR' => 'No se ha enviado la evaluacion', 'dataset' => $dataset));
    }
} else {
    $result = array(array('ERROR' => 'No se han enviado datos'));
}
$respuesta = array();
foreach($result[0] as $k => $v) {
    $respuesta[str_replace('eva_', '', $k)] = $v;
}
/*
if (isset($respuesta['rol'])) {
    $respuesta['rol'] = $roles[$respuesta['rol']];
}
 */
echo json_encode(array($respuesta));

