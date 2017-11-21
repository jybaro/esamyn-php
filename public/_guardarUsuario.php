<?php


//header('Content-Type: application/json');


$ess_id = $_SESSION['ess_id'];



if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);
    if (isset($dataset->cedula) && !empty($dataset->cedula)) {
        $cedula = $dataset->cedula;
        $username = $cedula;

        if (isset($dataset->reiniciar) && !empty($dataset->reiniciar)) {
            //resetea clave

            $password = md5($cedula);
            $result = q("UPDATE esamyn.esa_usuario SET usu_password='$password' WHERE usu_cedula='$cedula'");
            echo json_encode($result[0]);
        } else {
            //guarda datos de usuario

            $result = q("SELECT COUNT(*) FROM esamyn.esa_usuario WHERE usu_cedula='$cedula'");
            $count_usuarios_cedula = (int)$result[0]; 

            if ($count_usuarios_cedula === 0) {
                //crea usuario
                $campos = 'rol,nombres,apellidos,cedula,telefono,correo_electronico';
                $campos_array = explode(',', $campos);
                $sql_insert_campos = '';
                $sql_insert_valores = '';
                $glue = '';
                foreach ($campos_array as $campo){

                    if (isset($dataset->$campo) && !empty($dataset->$campo)) {
                        $_ = '';

                        switch ($campo){
                        case 'rol':
                            break;
                        case 'nombres':
                            $_ = "'";
                            break;
                        case 'apellidos':
                            $_ = "'";
                            break;
                        case 'username':
                            $_ = "'";
                            break;
                        case 'password':
                            $_ = "'";
                            break;
                        case 'cedula':
                            $_ = "'";
                            break;
                        case 'telefono':
                            $_ = "'";
                            break;
                        case 'correo_electronico':
                            $_ = "'";
                            break;
                        }

                        $sql_insert_campos .= 'usu_' . $campo;
                        $sql_insert_valores .= $glue . $_ . $dataset->$campo . $_;
                        $glue = ',';
                    }

                }
                $sql_insert_campos .= $campos . ',username,password';
                $username = $dataset->cedula;
                $password = md5($dataset->cedula);
                $sql_insert_valores .= ",'$username','$password'";
                $result = q("INSERT INTO esamyn.esa_usuario($sql_insert_campos) VALUES($sql_insert_valores) RETURNING *");
                echo json_encode($result[0]);
            } else if ($count_usuarios_cedula === 1) {
                //actualiza usuario
                $campos = 'rol,nombres,apellidos,telefono,correo_electronico';
                $campos_array = explode(',', $campos);
                $sql_update = '';
                $glue = '';
                foreach ($campos_array as $campo){

                    if (isset($dataset->$campo) && !empty($dataset->$campo)) {
                        $_ = '';

                        switch ($campo){
                        case 'rol':
                            break;
                        case 'nombres':
                            $_ = "'";
                            break;
                        case 'apellidos':
                            $_ = "'";
                            break;
                        case 'telefono':
                            $_ = "'";
                            break;
                        case 'correo_electronico':
                            $_ = "'";
                            break;
                        }

                        $sql_update .= "$glue usu_$campo = ". $_ . $dataset->$campo . $_;
                        $glue = ',';
                    }

                }
                $sql = ("UPDATE esamyn.esa_usuario SET $sql_update WHERE usu_cedula='$cedula' RETURNING *");
                $result = q($sql);

                $respuesta = array();
                foreach($result as $k => $v) {
                    $respuesta[str_replace('usu_', '', $k)] = $v;
                }
                //echo json_encode(array('sql'=>$sql, 'data'=>$result[0]));
                echo json_encode($respuesta);
            } else {
                //borra usuarios con cedula repetida
                echo json_encode(array('ERROR' => 'Doble cedula'));
            }

        }
    } else {
        echo json_encode(array('ERROR' => 'Sin cedula', 'dataset' => $dataset));
    }
} else {
    echo '{"error":"No hay dataset"}';
}
