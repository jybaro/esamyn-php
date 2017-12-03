<?php

//header('Content-Type: application/json');

$ess_id = $_SESSION['ess_id'];

/*
$result = q("SELECT * FROM esamyn.esa_rol");
$roles = array();
foreach($result as $r){
    $roles[$r['rol_id']] = $r['rol_nombre']; 
}
 */
if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);
    if (isset($dataset->unicodigo) && !empty($dataset->unicodigo)) {
        $id = ( (isset($dataset->id) && !empty($dataset->id)) ? $dataset->id : null);
        $unicodigo = $dataset->unicodigo;

        if (isset($dataset->borrar) && !empty($dataset->borrar)) {
            if ($_SESSION['ess_id'] != $id) {
                $result = q("UPDATE esamyn.esa_establecimiento_salud SET ess_borrado=now() WHERE ess_id=$id RETURNING *");
            } else {
                $result = array(array('ERROR'=>"No se puede borrar el mismo establecimiento de salud con el que se encuentra abierta la sesion"));
            }
        } else if (isset($dataset->recuperar) && !empty($dataset->recuperar)) {
            $sql= ("SELECT COUNT(*) FROM esamyn.esa_establecimiento_salud WHERE ess_borrado IS NULL AND ess_unicodigo='$unicodigo'");
            $result = q($sql);
            $count_establecimiento_salud_unicodigo = $result[0]['count']; 

            if ($count_establecimiento_salud_unicodigo == 0) {
                $result = q("UPDATE esamyn.esa_establecimiento_salud SET ess_borrado=null WHERE ess_id=$id RETURNING *");
            } else {
                $result = array(array('ERROR'=>"No se puede recuperar, ya existe un establecimiento de salud con unicodigo $unicodigo"));
            }
        } else {
            //guarda datos de establecimiento_salud

            $sql= ("SELECT COUNT(*) FROM esamyn.esa_establecimiento_salud WHERE ess_borrado IS NULL AND ess_unicodigo='$unicodigo'");
            //echo "[$sql]";
            $result = q($sql);
            $count_establecimiento_salud_unicodigo = $result[0]['count']; 
            //echo "[count_establecimiento_salud_unicodigo: $count_establecimiento_salud_unicodigo]";

            if ($count_establecimiento_salud_unicodigo == 0) {
                //crea establecimiento_salud
                //$campos = 'rol,nombres,apellidos,unicodigo,telefono,correo_electronico';
                $campos = 'canton,nombre,unicodigo,direccion,telefono,correo_electronico,nombre_responsable,zona,distrito,nivel,tipologia,certificacion,max_usuarios';
                $campos_array = explode(',', $campos);
                $sql_insert_campos = '';
                $sql_insert_valores = '';
                $glue = '';
                foreach ($campos_array as $campo){

                    if (isset($dataset->$campo) && !empty($dataset->$campo)) {
                        $_ = '';

                        switch ($campo){
                        case 'canton':
                            break;
                        case 'nombre':
                            $_ = "'";
                            break;
                        case 'unicodigo':
                            $_ = "'";
                            break;
                        case 'direccion':
                            $_ = "'";
                            break;
                        case 'telefono':
                            $_ = "'";
                            break;
                        case 'correo_electronico':
                            $_ = "'";
                            break;
                        case 'nombre_responsable':
                            $_ = "'";
                            break;
                        case 'zona':
                            $_ = "'";
                            break;
                        case 'distrito':
                            $_ = "'";
                            break;
                        case 'nivel':
                            $_ = "'";
                            break;
                        case 'tipologia':
                            $_ = "'";
                            break;
                        case 'certificacion':
                            $_ = "'";
                            break;
                        case 'max_usuarios':
                            break;
                        }

                        $sql_insert_campos .= $glue . 'ess_' . $campo;
                        $sql_insert_valores .= $glue . $_ . $dataset->$campo . $_;
                        $glue = ',';
                    }

                }
                $result = q("INSERT INTO esamyn.esa_establecimiento_salud($sql_insert_campos) VALUES($sql_insert_valores) RETURNING *");
            } else if (!empty($id) && $count_establecimiento_salud_unicodigo == 1) {
                //actualiza establecimiento_salud
                $campos = 'canton,nombre,direccion,telefono,correo_electronico,nombre_responsable,zona,distrito,nivel,tipologia,certificacion,max_usuarios';
                $campos_array = explode(',', $campos);
                $sql_update = '';
                $glue = '';
                foreach ($campos_array as $campo){

                    if (isset($dataset->$campo) && !empty($dataset->$campo)) {
                        $_ = '';

                        switch ($campo){
                        case 'canton':
                            break;
                        case 'nombre':
                            $_ = "'";
                            break;
                        case 'direccion':
                            $_ = "'";
                            break;
                        case 'telefono':
                            $_ = "'";
                            break;
                        case 'correo_electronico':
                            $_ = "'";
                            break;
                        case 'nombre_responsable':
                            $_ = "'";
                            break;
                        case 'zona':
                            $_ = "'";
                            break;
                        case 'distrito':
                            $_ = "'";
                            break;
                        case 'nivel':
                            $_ = "'";
                            break;
                        case 'tipologia':
                            $_ = "'";
                            break;
                        case 'certificacion':
                            $_ = "'";
                            break;
                        case 'max_usuarios':
                            break;
                        }

                        $sql_update .= "$glue ess_$campo = ". $_ . $dataset->$campo . $_;
                        $glue = ',';
                    }

                }
                $sql = ("UPDATE esamyn.esa_establecimiento_salud SET $sql_update WHERE ess_id=$id RETURNING *");
                $result = q($sql);

            } else {
                //borra establecimiento_saluds con unicodigo repetida
                $result = array(array('ERROR' => "Ya existe un establecimiento de salud con unicodigo $unicodigo"));
            }
        }
    } else {
        $result = array(array('ERROR' => 'No se ha enviado el unicodigo', 'dataset' => $dataset));
    }
} else {
    $result = array(array('ERROR' => 'No se han enviado datos'));
}
$respuesta = array();
foreach($result[0] as $k => $v) {
    $respuesta[str_replace('ess_', '', $k)] = $v;
}
/*
if (isset($respuesta['rol'])) {
    $respuesta['rol'] = $roles[$respuesta['rol']];
}
 */
echo json_encode(array($respuesta));

