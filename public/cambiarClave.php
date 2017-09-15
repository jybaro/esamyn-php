<?php
if (isset($_POST['oldpassword']) && !empty($_POST['oldpassword']) && isset($_POST['newpassword']) && !empty($_POST['newpassword']) && isset($_POST['new2password']) && !empty($_POST['new2password'])){
    $old = $_POST['oldpassword'];
    $new = $_POST['new'];
    $new2 = $_POST['new2'];

    $usu_id = $_SESSION['usu_id'];

    $password = q("SELECT usu_password FROM esamyn.esa_usuario wHERE usu_id=$usu_id")[0]['usu_password'];

    if (md5($old) == $password) {
        if ($new == $new2) {

            $result = q("UPDATE esamyn.esa_usuario SET usu_password=md5('$newpassword') WHERE usu_id=$usu_id");
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
?>

<h1>Cambiar Contrase&ntilde;a</h1>
<form method="POST" action="">
<div class="row">
  <div class="form-group">
    <div class="col-md-2">
      <label  for="oldpassword">Ingrese su antigua contraseña:</label>
    </div>
    <div class="col-md-4">
      <input type="password" class="form-control" id="oldpassword" name="oldpassword" />
    </div>
  </div>
</div>
<div class="row">
  <div class="form-group">
    <div class="col-md-2">
      <label  for="newpassword">Ingrese su nueva contraseña:</label>
    </div>
    <div class="col-md-4">
      <input type="password" class="form-control" id="newpassword" name="newpassword" />
    </div>
  </div>
</div>
<div class="row">
  <div class="form-group">
    <div class="col-md-2">
      <label  for="new2password">Repita su nueva contraseña:</label>
    </div>
    <div class="col-md-4">
      <input type="password" class="form-control" id="new2password" name="new2password" />
    </div>
  </div>
</div>
<div class="row">
  <div class="form-group">
    <div class="col-md-2">
    </div>
    <div class="col-md-4">
      <input type="submit" class="form-control btn btn-primary" id="new2password" name="new2password" />
    </div>
  </div>
</div>
</form>
