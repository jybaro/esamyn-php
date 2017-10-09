<?php

$sql = "UPDATE esamyn.esa_pregunta SET prg_validacion='max=\"43\" step=\".1\"' WHERE prg_formulario=3 AND prg_texto='1. Semanas de gestación' RETURNING *";
echo "<div>$sql</div>";
$result = q($sql);
if ($result){
    echo '<pre>';
    var_dump($result);
} else {
    echo "ERROR";
}
