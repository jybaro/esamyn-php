<?php

$ess_id = $_SESSION['ess_id'];
$evaluacion = q("
    SELECT 
    * 
    FROM 
    esamyn.esa_evaluacion
    ,esamyn.esa_tipo_evaluacion
    WHERE eva_establecimiento_salud = $ess_id
    AND eva_tipo_evaluacion = tev_id
    AND eva_activo = 1
    ");

if (!$evaluacion) {
    echo '<div class="alert alert-danger"><h2>No hay evaluaci&oacute;n activa</h2>Solicite a su supervisor que cree una evaluación para este Establecimiento de Salud.</div>';
    return;
} else {
    $evaluacion = $evaluacion[0];
    $_SESSION['evaluacion'] = $evaluacion;
    $eva_id = $evaluacion['eva_id'];
}

?>
<h2>Reporte de Evaluaciones</h2>
