<?php

header('Content-Type: application/json');


$ess_id = $_SESSION['ess_id'];



if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);
    $eva_id = $dataset->eva_id;

    q("UPDATE esamyn.esa_evaluacion SET eva_activo=1 WHERE eva_establecimiento_salud=$ess_id AND eva_id=$eva_id");
    q("UPDATE esamyn.esa_evaluacion SET eva_activo=0 WHERE eva_establecimiento_salud=$ess_id AND eva_id<>$eva_id");

    $evaluaciones = q("SELECT * FROM esamyn.esa_evaluacion WHERE eva_establecimiento_salud=$ess_id");
    echo json_encode($evaluaciones);
}else {
    echo '{"error":"No hay dataset"}';
}
