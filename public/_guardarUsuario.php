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
    if (isset($dataset->cedula && !empty($dataset->cedula))) {
        $cedula = $dataset->cedula;
        $username = $cedula;

        if (isset($dataset->reiniciar) && !empty($dataset->reiniciar)) {
            //resetea clave

            $password = md5($cedula);
            q("UPDATE esamyn.esa_usuario SET usu_password='$password' WHERE usu_cedula='$cedula'");
        } else {
            //guarda datos de usuario
            $campos = 'rol,nombres,apellidos,username,password,cedula,telefono,correo_electronico';
            $campos_array = explode(',', $campos);

            $count_usuarios_cedula = q("SELECT COUNT(*) FROM esamyn.esa_usuario WHERE usu_cedula='$cedula'");

            if ($count_usuarios_cedula === 0) {
                //crea usuario
                $sql_insert_campos = $campos;
                $sql_insert_valores = '';
                foreach ($campos_array as $campo){
                    if (isset($dataset->$campo && !empty($dataset->$campo))) {
                        switch ($campo){
                        case 'rol':
                            break;
                        }
                    }

                }
                q("INSERT INTO esamyn.esa_usuario($sql_insert_campos) VALUES($sql_insert_valores)");
            } else if ($count_usuarios_cedula === 1) {
                //actualiza usuario
                q("UPDATE esamyn.esa_usuario SET $sql_update WHERE usu_cedula='$cedula'");
            } else {
                //borra usuarios con cedula repetida
            }

        }
    }
} else {
    echo '{"error":"No hay dataset"}';
}
