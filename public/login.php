<?php
//if (isset($_GET['destroy'])) {
//if(isset($args[0]) && $args[0] == 'destroy'){
//}

$error = false;

if (isset($_POST['cedula']) && !empty($_POST['cedula']) && isset($_POST['password']) && !empty($_POST['password'])) {
    //var_dump($_POST);
    $cedula = $_POST['cedula'];
    $password = $_POST['password'];
    $ess_id = $_POST['establecimiento_salud'];
    $ess_nombre = q("SELECT * from esamyn.esa_establecimiento_salud WHERE ess_id = $ess_id");
    $ess_nombre = $ess_nombre[0]['ess_nombre'];


    //$usuario = q("SELECT * FROM esamyn.esa_usuario AS usu, esamyn.esa_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_password='$password'");
    $usuario = q("SELECT * FROM esamyn.esa_usuario AS usu, esamyn.esa_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' ");
       //echo "<div>SELECT * FROM esamyn.esa_usuario AS usu, esamyn.esa_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_password='$password'</div>";
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
        <input type="text" id="cedula" name="cedula" class="form-control" placeholder="cedula" required autofocus>
        <label for="inputPassword" class="sr-only">Contraseña</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required>
<label for="establecimiento_salud">Establecimiento de Salud:</label>
<select name="establecimiento_salud" id="establecimiento_salud" class="form_control" required>
<?php
$es = q("SELECT * FROM esamyn.esa_establecimiento_salud" );
foreach($es as $e){
    echo '<option value="'.$e['ess_id'].'">';
    echo $e['ess_nombre'];
    echo "</option>";
}
?>
</select>
        <div class="checkbox">
          <label>
         <input type="checkbox" value="remember-me"> Recordar en esta computadora
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
