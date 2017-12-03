<?php
$usu_id = $_SESSION['usu_id'];
if (isset($_POST['oldpassword']) && !empty($_POST['oldpassword']) && isset($_POST['new1password']) && !empty($_POST['new1password']) && isset($_POST['new2password']) && !empty($_POST['new2password'])){
    $old = $_POST['oldpassword'];
    $new1 = $_POST['new1password'];
    $new2 = $_POST['new2password'];


    $password = q("SELECT usu_password FROM esamyn.esa_usuario WHERE usu_id=$usu_id")[0]['usu_password'];

    if (md5($old) == $password) {
        if ($new1 == $new2) {

            $result = q("UPDATE esamyn.esa_usuario SET usu_password='".md5($new1)."' WHERE usu_id=$usu_id");
            echo '<div class="alert alert-success">';
            echo 'Contrase&ntilde;a cambiada con &eacute;xito.';
            echo '</div>';
        } else {
            echo '<div class="alert alert-danger">';
            echo 'Las contrase&ntilde;as no coinciden.';
            echo '</div>';

        }
    } else {
        echo '<div class="alert alert-danger">';
        //var_dump($password);
        //echo  $password . '-md5 '.$old.': ' . (md5($old));
        echo 'La contrase&ntilde;a actual no es correcta';
        echo '</div>';

    }
}

$result = q("SELECT usu_cedula, usu_password FROM esamyn.esa_usuario WHERE usu_id=$usu_id");

if ($result) {
    $cedula = $result[0]['usu_cedula'];
    $password = $result[0]['usu_password'];
    if (md5($cedula) == $password){
        echo "<div class='alert alert-danger'>Su contrase&ntilde;a actual es su n&uacute;mero de c&eacute;dula, por favor cambie su contrase&ntilde;a lo m&aacute;s pronto posible por seguridad.</div>";
    }
} else {
    echo "<script>alert('ERROR: Usuario no encontrado. Vuelva a ingresar con su clave de acceso.');window.location.replace('/login');</script>";
    return;
}
?>

<div class="page-header">
  <h1>Cambiar Contrase&ntilde;a</h1>
</div>

<form class="form-horizontal" method="POST" action="" onsubmit="return p_validar_nueva_clave()">

<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Contrase&ntilde;a actual</h3>
  </div>
  <div class="panel-body">

  <div class="form-group">
    <label class="col-sm-2 control-label" for="oldpassword">Ingrese su contrase&ntilde;a actual:</label>
    <div class="col-sm-2">
      <input type="password" class="form-control" id="oldpassword" name="oldpassword" />
    </div>
  </div>


  </div>
</div>


<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">Nueva contrase&ntilde;a</h3>
  </div>
  <div class="panel-body">


  <div class="form-group">
    <label class="col-sm-2 control-label" for="new1password">Ingrese su nueva contrase&ntilde;a:</label>
    <div class="col-sm-2">
      <input type="password" class="form-control" id="new1password" name="new1password" />
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label" for="new2password">Repita su nueva contrase&ntilde;a:</label>
    <div class="col-sm-2">
      <input type="password" class="form-control" id="new2password" name="new2password" />
    </div>
  </div>

  </div>
</div>



  <div class="form-group">
    <div class="col-sm-2">
    </div>
    <div class="col-sm-2">
      <input type="submit" class=" btn btn-primary" id="" name="" value="Cambiar" />
    </div>
  </div>


</form>
<script>
function p_validar_nueva_clave(){
    var old = $('#oldpassword').val();
    var new1 = $('#new1password').val();
    var new2 = $('#new2password').val();

    if (old != ''&& new1 != '' && new2 != '') {
        if (old != new1) {
            if (new1 == new2) {
                var regularExpression = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d!@#$%^&*_\-\/\.]{8,}$/;///^(?=.*[0-9])(?=.*[!@#$%^&*_\-\/\.])[a-zA-Z0-9!@#$%^&*_\-\/\.]{6,16}$/;

                if(regularExpression.test(new1)) {
                    return true;
                    //alert('OK');
                } else {
                    alert('Ingrese al menos 8 caracteres entre letras y números, al menos una minúscula, una mayúscula y un número.');
                }
            } else {
                alert('Ingrese el mismo valor en los campos de nueva contraseña.');
            }
        } else {
            alert('La nueva contraseña debe ser distinta a la actual.');
        }
    } else {
        alert('Llene todos los campos.');
    }
    return false;
}
</script>
