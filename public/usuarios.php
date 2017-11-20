<?php
//$us_listado = q("SELECT *, (SELECT rol_nombre FROM esamyn.esa_rol WHERE rol_id=usu_rol) AS rol FROM esamyn.esa_usuario ORDER BY usu_cedula");
$us_listado = q("SELECT *, (SELECT rol_nombre FROM esamyn.esa_rol WHERE rol_id=usu_rol) AS rol FROM esamyn.esa_usuario ORDER BY usu_apellidos");

$rol = $_SESSION['rol'];
?>
<h2>Usuarios</h2>

<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:10px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
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
        <?php if ($rol == 1): ?>
        <option value="1">Administrador</option>
        <?php endif; ?>
        <option value="2">Supervisor</option>
        <option value="3">Operador</option>
      </select>
    </div>
  </div>
</form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="p_eliminar()" id="formulario_eliminar">Eliminar usuario</button>
        <button type="button" class="btn btn-primary" onclick="p_reiniciar()" id="formulario_reiniciar">Reiniciar contrase&ntilde;a</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()">Guardar cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<table class="table table-striped">
  <tr>
    <th></th>
    <th>Número de cédula</th>
    <th>Nombre</th>
    <th>Rol</th>
    <th>Correo electr&oacute;nico</th>
  </tr>
<tbody id="antiguos">
<?php foreach($us_listado as $i=>$us): ?>
  <tr>
    <th><?php echo ($i+1).'.&nbsp;'; ?></th>
    <td><span id=""><a href="#" onclick="p_abrir('<?=$us['usu_id']?>');return false;"><?=$us['usu_cedula']?></a></span></td>
    <td><span id="nombre_<?=$us['usu_id']?>"><?php echo $us['usu_apellidos'].' '.$us['usu_nombres']; ?></span></td>
    <td><span id="rol_<?=$us['usu_id']?>"><?=$us['rol']?></span></td>
    <td><span id="correo_electronico_<?=$us['usu_id']?>"><?=$us['usu_correo_electronico']?></span></td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>


<!--script src="/js/bootstrap3-typeahead.min.js"></script-->
<script src="/js/md5.min.js"></script>
<script>
function p_abrir(id){
    $.ajax({
        'url':'/_listar/usuario/'+id
    }).done(function(data){
        data = eval(data);
        usu = data[0];
        console.log(usu);
        $('#formulario_titulo').text(usu['cedula'] + ' "' + usu['nombres'] + ' ' + usu['apellidos'] + '"');
        $('#formulario_eliminar').show();
        $("#cedula").prop('disabled', true);
        for (key in usu){
            $('#' + key).val(usu[key]);
        }
        
        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_guardar(){
    if ($('#nombres').val() !== '' && $('#apellidos').val() !== '' && $('#cedula').val() !== '' && $('#correo_electronico').val() !== '') {
        if (verificarCedula($('#cedula').val())) {
            var respuestas_json = $('#formulario').serializeArray();
            console.log(respuestas_json);
            dataset_json = [];
            dataset_json[0] = {};
            respuestas_json.forEach(function(respuesta_json){
                var name =  respuesta_json['name'];
                var value = respuesta_json['value'];
                dataset_json[0][name]=value;

            });

            dataset_json[0]['username'] = dataset_json[0]['cedula'];
            dataset_json[0]['password'] = md5(dataset_json[0]['cedula']);

            console.log('dataset_json', dataset_json);
            $.ajax({
                url: '_guardar/usuario',
                    type: 'POST',
                    dataType: 'json',
                    data: JSON.stringify(dataset_json),
        //data: dataset_json,
        contentType: 'application/json'
            }).done(function(data){
                console.log('Guardado OK', data)
                    data = eval(data);

                if($("#nombre_" + data[0]['id']).length) { // 0 == false; >0 == true
                    //ya existe:
                    $('#cedula_' + data[0]['id']).text(data[0]['cedula']);
                    $('#nombre_' + data[0]['id']).text(data[0]['nombres'] + ' ' + data[0]['apellidos']);
                    $('#correo_electronico_' + data[0]['id']).text(data[0]['correo_electronico']);
                } else {
                    //nuevo:
                    console.log('nuevo USUARIO');
                    var numero = $('#antiguos').children().length + 1;
                    $('#antiguos').append('<tr><th>'+numero+'.</th><td><a href="#" onclick="p_abrir(\''+data[0]['id']+'\')">'+data[0]['cedula']+'</a></td><td><span id="nombre_' + data[0]['id'] + '">' + data[0]['nombres'] + ' ' + data[0]['apellidos'] + '</span></td><td><span id="rol_' + data[0]['id'] + '">'+data[0]['rol']+'</span></td><td><span id="correo_electronico_'+data[0]['id']+'">'+data[0]['correo_electronico'] + '</span></td></tr>');
                }
                $('#modal').modal('hide');
            }).fail(function(xhr, err){
                console.error('ERROR AL GUARDAR', xhr, err);
                $('#modal').modal('hide');
            });
        } else {
            alert ('Ingrese un número de cédula válido');
        }
    } else {
        alert ('Ingrese al menos el número de cédula, nombres, apellidos y correo electrónico');
    }
}

function p_nuevo(){

    $('#formulario_titulo').text('nuevo');
    $('#formulario').trigger('reset');
    $('#id').val('');
    $('#modal').modal('show');
    $('#formulario_eliminar').hide();
 
    $('#cedula').prop('disabled', false);

}

function p_eliminar(cedula, nombre){
    if (confirm('Seguro desea eliminar el Usuario ' + $('#cedula').val() + ' "' + $('#nombres').val() + ' ' + $('#apellidos').val() + '"')) {
        var dataset_json = [{id:$('#id').val()}];
        $.ajax({
            url: '_borrar/usuario',
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify(dataset_json),
            //data: dataset_json,
            contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK', data)
                data = eval(data);

            $('#nombre_' + data[0]['id']).parent().parent().remove();
        }).fail(function(xhr, err){
            console.error('ERROR AL BORRAR', xhr, err);
        });
        $('#modal').modal('hide');
    }
}
function verificarCedula(cedula) {
  if (typeof(cedula) == 'string' && cedula.length == 10 && /^\d+$/.test(cedula)) {
    var digitos = cedula.split('').map(Number);
    var codigo_provincia = digitos[0] * 10 + digitos[1];

    //if (codigo_provincia >= 1 && (codigo_provincia <= 24 || codigo_provincia == 30) && digitos[2] < 6) {

    if (codigo_provincia >= 1 && (codigo_provincia <= 24 || codigo_provincia == 30)) {
      var digito_verificador = digitos.pop();

      var digito_calculado = digitos.reduce(
        function (valorPrevio, valorActual, indice) {
          return valorPrevio - (valorActual * (2 - indice % 2)) % 9 - (valorActual == 9) * 9;
        }, 1000) % 10;
      return digito_calculado === digito_verificador;
}
  }
  return false;
}
</script>
<script>
/*
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
 */
</script>
