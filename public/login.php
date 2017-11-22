<?php
//if (isset($_GET['destroy'])) {
//if(isset($args[0]) && $args[0] == 'destroy'){
//}

$error = false;

if (isset($_POST['cedula']) && !empty($_POST['cedula']) && isset($_POST['password']) && !empty($_POST['password'])) {
    //var_dump($_POST);
    //die();
    $cedula = $_POST['cedula'];
    $password = $_POST['password'];
    $ess_id = $_POST['establecimiento_salud'];
    $ess = q("SELECT * FROM esamyn.esa_establecimiento_salud WHERE ess_id = $ess_id" );

    if ($ess) {
        $ess = $ess[0];
        $ess_nombre = $ess['ess_nombre'];
        //$ess_unicodigo = $ess[0]['ess_unicodigo'];
    }


    //$usuario = q("SELECT * FROM esamyn.esa_usuario AS usu, esamyn.esa_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_password='$password'");
       //echo "<div>SELECT * FROM esamyn.esa_usuario AS usu, esamyn.esa_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_password='$password'</div>";
    //
    $usuario = q("SELECT * FROM esamyn.esa_usuario, esamyn.esa_rol  WHERE usu_borrado IS NULL AND usu_rol = rol_id AND usu_cedula='$cedula'");
    //$usuario = q("SELECT * FROM esamyn.esa_usuario AS usu, esamyn.esa_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_cedula<>'1713175071'");
    //
    //$usuario = q("SELECT * FROM esamyn.esa_usuario AS usu, esamyn.esa_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_password=md5($password)");
    //$usuario = q("SELECT * FROM esamyn.esa_usuario AS usu, esamyn.esa_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_password='".md5($password)."'");
   // echo count($usuario);

    //var_dump($usuario);
    if (true && is_array($usuario) && count($usuario) == 1){
        echo "<hr>";
        $_SESSION['cedula'] = $cedula;
        $_SESSION['usu_id'] = $usuario[0]['usu_id'];
        $rol = $usuario[0]['usu_rol'];
        $_SESSION['rol'] = $rol;
        $_SESSION['ess_id'] = $ess_id;
        $_SESSION['ess_nombre'] = $ess_nombre;
        $_SESSION['ess'] = $ess;
        
        if (isset($_POST['rememberme']) && !empty($_POST['rememberme'])) {
            $params = session_get_cookie_params();
            setcookie(session_name(), $_COOKIE[session_name()], time() + 60*60*24*30, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }

        $destino = ($rol == 1) ? 'admin' : (($rol == 2) ? 'supervisor' : 'operador');
        header("Location: /$destino");
        //echo "logueado: ";
        //var_dump($_SESSION);
    } else {
        $error = true;
        $_SESSION = array();
    }
} else {
    //si no manda intento de login, destruye sesion:
    //
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    session_start();
}

?>

<div class="container">

      <form action = "/login" method="POST" class="form-signin">
        <h2 class="form-signin-heading">Ingreso a ESAMyN</h2>
        <label for="cedula" class="sr-only">Número de cédula</label>
        <input type="text" id="cedula" name="cedula" class="form-control" placeholder="Usuario" required autofocus>
        <label for="inputPassword" class="sr-only">Contraseña</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required>

<input type="hidden" id="establecimiento_salud" name="establecimiento_salud" value="">
<input class="form-control" required type="text" id="establecimiento_salud_typeahead" data-provide="typeahead" autocomplete="off" placeholder="Establecimientos de Salud" onblur="p_validar_es()">
<!--select name="establecimiento_salud" id="establecimiento_salud" class="form_control" required>
<?php
    $es = q("
        SELECT 
        *
        ,(
            SELECT 
            can_nombre
            FROM
            esamyn.esa_canton
            WHERE
            can_id=ess_canton
        ) AS canton
        ,(
            SELECT
            pro_nombre
            FROM
            esamyn.esa_canton
            ,esamyn.esa_provincia
            WHERE
            can_id=ess_canton
            AND
            can_provincia=pro_id
        ) AS provincia
        FROM 
        esamyn.esa_establecimiento_salud 
        ");
/*
foreach($es as $e){
    echo '<option value="'.$e['ess_id'].'">';
    echo $e['ess_nombre'];
    echo "</option>";
}
 */
?>
</select-->
        <div class="checkbox">
          <label>
         <input type="checkbox" name="rememberme" value="rememberme"> Recordar en esta computadora
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Ingresar</button>
      <?php if($error): ?>
<div class="alert alert-danger alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <strong>Error:</strong> no se encuentra al usuario, inténtelo de nuevo.
</div>
      <?php endif; ?>

      </form>


    </div> <!-- /container -->
<script src="/js/bootstrap3-typeahead.min.js"></script>
<script type="text/javascript">
var escogido = {id:"",name:""};
var es =[<?php
$glue = '';
foreach($es as $e){
    echo $glue.'{id:"'.$e['ess_id'].'",name:"' . str_replace('"', "'", $e['ess_nombre'].' ('.$e['canton'].', '.$e['provincia']) . ') - '.$e['ess_unicodigo'].'"}';
    $glue = ',';
}
?>];
$(document).ready(function() {
    $('#establecimiento_salud_typeahead').typeahead({
        source:es,
        displayField:'name',
        valueField:'id',
        highlighter:function(name){
        //console.log(item);
                var ficha = '';
                ficha +='<div>';
                ficha +='<h4>'+name+'</h4>';
                ficha +='</div>';
                return ficha;

            },
                updater:function(item){
                    console.log(item);
                    $('#establecimiento_salud').val(item.id);
                    escogido.id = item.id;
                    escogido.name = item.name;

                    return item.name;

                }
    });
})

function p_validar_es(){
    console.log('on blur')
    if ($('#establecimiento_salud').val() == ''){
        $('#establecimiento_salud_typeahead').val('');
    }
}
</script>
