<?php


//header('Content-Type: application/json');
$ess_id = $_SESSION['ess_id'];

if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

$respuesta = array();
$error = array();
if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);
    if (isset($dataset->rol) && !empty($dataset->rol) && isset($dataset->modulo) && !empty($dataset->modulo)) {
        $rol = $dataset->rol;
        $modulo = $dataset->modulo;

        $count_seguridad = q("SELECT COUNT(*) FROM esamyn.esa_seguridad WHERE seg_rol=$rol AND seg_modulo=$modulo")[0]['count'];

        if ($count_seguridad == 0) {
            q("INSERT INTO esamyn.esa_seguridad (seg_modulo, seg_rol) VALUES ($modulo, $rol)");
        } else {
            q("DELETE FROM esamyn.esa_seguridad WHERE seg_modulo=$modulo AND seg_rol=$rol");
        }
        $rol_version = q("UPDATE esamyn.esa_rol SET rol_version=rol_version+1 WHERE rol_id=$rol RETURNING rol_version")[0]['rol_version'];

        $count_seguridad = q("SELECT COUNT(*) FROM esamyn.esa_seguridad WHERE seg_rol=$rol AND seg_modulo=$modulo")[0]['count'];

        $respuesta = array(
            'rol' => $rol,
            'modulo' => $modulo,
            'count_seguridad' => $count_seguridad
        );

    } else {
        $error = array('sinRolModulo' => 'No se ha mandado el rol y/o el modulo.');
    }
}
echo json_encode(array('respuesta'=>$respuesta, 'error'=>$error));
