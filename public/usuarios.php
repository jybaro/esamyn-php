<?php
$us_listado = q("SELECT *, (SELECT rol_nombre FROM esamyn.esa_rol WHERE rol_id=usu_rol) AS rol FROM esamyn.esa_usuario ORDER BY usu_cedula");
?>

<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Usuario <span id="formulario_titulo"></span></h4>
      </div>
      <div class="modal-body">


<form id="formulario" class="form-horizontal">
<input type="hidden" id="id" name="id" value="">
  <div class="form-group">
    <label for="cedula" class="col-sm-2 control-label">N&uacute;mero de c&eacute;dula:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cedula">
    </div>
  </div>
  <div class="form-group">
    <label for="nombres" class="col-sm-2 control-label">Nombres:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombres">
    </div>
  </div>
  <div class="form-group">
    <label for="apellidos" class="col-sm-2 control-label">Apellidos:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Apellidos">
    </div>
  </div>
  <div class="form-group">
    <label for="telefono" class="col-sm-2 control-label">Teléfono:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="telefono" name="telefono" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="correo_electronico" class="col-sm-2 control-label">Correo electrónico:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="correo_electronico" name="correo_electronico" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="rol" class="col-sm-2 control-label">Rol:</label>
    <div class="col-sm-10">
      <select id="rol" name="rol" class="">
        <option value="1">Administrador</option>
        <option value="2">Supervisor</option>
        <option value="3">Operador</option>
      </select>
    </div>
  </div>
</form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="p_guardar()">Guardar cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<table class="table table-striped">
<tr>
<th></th>
<th>Cédula</th>
<th>Nombre</th>
<th>Rol</th>
</tr>
<?php foreach($us_listado as $i=>$us): ?>
<tr>
<th><?php echo ($i+1).'.&nbsp;'; ?></th>
<td><a href="#" onclick="p_abrir('<?=$us['usu_id']?>');return false;"><?php echo $us['usu_cedula']; ?></a></td>
<td><?php echo $us['usu_nombres'].' '.$us['usu_apellidos']; ?></td>
<td><?php echo $us['rol']; ?></td>
</tr>
<?php endforeach; ?>
</table>
<script>
function p_abrir(usu_id){
    $.ajax({
        'url':'/_listar/usuario/'+usu_id
    }).done(function(data){
        data = eval(data);
        usuario = data[0];
        console.log(usuario);
        $('#formulario_titulo').text(usuario['cedula']);
        for (key in usuario){
            $('#' + key).val(usuario[key]);
        }
    }).fail(function(){
        console.error('ERROR AL ABRIR');
    });
    $('#modal').modal('show');
}

</script>
