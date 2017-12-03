<?php
//$us_listado = q("SELECT *, (SELECT rol_nombre FROM esamyn.esa_rol WHERE rol_id=usu_rol) AS rol FROM esamyn.esa_usuario ORDER BY usu_cedula");
$rol = $_SESSION['rol'];

$filtro = '';
if ($rol != 1){
    //Los supervisores solo pueden crear y editar a operadores
    $filtro .= " AND usu_rol=3 ";
    $filtro .= " AND usu_borrado IS NULL ";
}

$us_listado = q("SELECT *, (SELECT rol_nombre FROM esamyn.esa_rol WHERE rol_id=usu_rol) AS rol FROM esamyn.esa_usuario WHERE 1=1 $filtro ORDER BY usu_borrado DESC, usu_apellidos");

?>
<h2>Usuarios</h2>

<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
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
        <option value="2">Supervisor</option>
        <?php endif; ?>
        <option value="3">Operador</option>
      </select>
    </div>
  </div>
</form>

<hr>
<h5>PERMISOS DE INGRESO</h5>

<form id="formulario_pei" class="form-horizontal">
  <div class="form-group">
    <label for="establecimiento_salud" class="col-sm-3 control-label">Establecimiento de Salud:</label>
    <div class="col-sm-7">
      <input type="hidden" id="establecimiento_salud" name="establecimiento_salud" value="">
      <input class="form-control" required type="text" id="establecimiento_salud_typeahead" data-provide="typeahead" autocomplete="off" placeholder="Ingrese al menos 3 caracteres" onblur="p_validar_establecimiento_salud()">
    </div>
    <div class="col-sm-1">
      <button type="button" class="btn btn-info" id="establecimiento_salud_agregar" onclick="p_guardar_permiso_ingreso()"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
    </div>
  </div>

  <div class="form-group">
    <label for="zonal" class="col-sm-1 control-label">Zonal:</label>
    <div class="col-sm-9">
      <input type="hidden" id="zonal" name="zonal" value="">
      <select id="zonal" name="zonal">
<?php
    $result = q("SELECT DISTINCT(ess_zona) AS zonal FROM esamyn.esa_establecimiento_salud");
    if ($result) {
        foreach($result as $r) {
            echo "<option>{$r['zonal']}</option>";
        }
    }
?>
      </select>
    </div>
    <div class="col-sm-1">
      <button type="button" class="btn btn-info" id="zonal_agregar" onclick="p_guardar_permiso_ingreso('zonal')"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
    </div>
  </div>
</form>

<table class="table table-striped">
<tbody id="antiguos_pei"></tbody>
</table>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="p_borrar()" id="formulario_eliminar">Eliminar usuario</button>
        <button type="button" class="btn btn-success" onclick="p_recuperar()" id="formulario_recuperar">Recuperar usuario</button>
        <button type="button" class="btn btn-primary" onclick="p_reiniciar()" id="formulario_reiniciar">Reiniciar contrase&ntilde;a</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button>
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
<tr class="<?php echo (empty($us['usu_borrado']) ? ($us['usu_password'] == md5($us['usu_cedula']) ? 'alert alert-info' : '') : 'alert alert-danger'); ?>">
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
<script src="/js/bootstrap3-typeahead.min.js"></script>
<script src="/js/md5.min.js"></script>
<script>
$(document).ready(function() {
    $('#establecimiento_salud_typeahead').typeahead({
        source:function(query, process){
            $.get('/_listarEstablecimientoSalud/' + query, function(data){
                data = JSON.parse(data);
                process(data.lista);
            });
        },
        displayField:'name',
        valueField:'id',
        highlighter:function(name){
            var ficha = '';
            ficha +='<div>';
            ficha +='<h4>'+name+'</h4>';
            ficha +='</div>';
            return ficha;
        },
        updater:function(item){
            $('#establecimiento_salud').val(item.id);
            $('#establecimiento_salud_agregar').show();
            return item.name;
        }
    });
})

function p_validar_establecimiento_salud(){
    console.log('on blur establecimiento_salud')
    if ($('#establecimiento_salud').val() == ''){
        $('#establecimiento_salud_typeahead').val('');
    }
}
function p_abrir(id){
    $.ajax({
        'url':'/_listar/usuario/'+id
    }).done(function(data){
        //data = eval(data);
        data = JSON.parse(data);
        usu = data[0];
        console.log('ABRIENDO USUARIO', usu);

        var badge = '';
        var disabled = false;
        if (usu['borrado'] == null) {
            if (usu['password'] == md5(usu['cedula'])){
                badge = '<span class="badge">CLAVE ES LA CEDULA</span>';
                $('#formulario_reiniciar').hide();
            } else {
                $('#formulario_reiniciar').show();
            }
            $('#formulario_eliminar').show();
            $('#formulario_guardar').show();
            $('#formulario_recuperar').hide();
            disabled = false;
            p_abrir_permiso_ingreso(usu['id']);
        } else {
            badge = '<span class="badge">ELIMINADO</span>';
            $('#formulario_eliminar').hide();
            $('#formulario_reiniciar').hide();
            $('#formulario_guardar').hide();
            $('#formulario_recuperar').show();
            disabled = true;
        }
        $('#formulario_titulo').html(usu['cedula'] + ' "' + usu['nombres'] + ' ' + usu['apellidos'] + '" ' + badge);
        for (key in usu){
            $('#' + key).val(usu[key]);
            $('#' + key).prop('disabled', disabled);
        }

        $('#establecimiento_salud_typeahead').val('');
        $('#establecimiento_salud').val('');
        $('#establecimiento_salud_agregar').hide();
        $("#cedula").prop('disabled', true);
        
        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_abrir_permiso_ingreso(usuario) {
    $('#antiguos_pei').html('');
    $.get('/_listarPermisoIngreso/usuario/' + usuario, function(dataset){
        console.log(dataset);
        dataset = JSON.parse(dataset);

        if (dataset.respuestas) {
        dataset.respuestas.forEach(function(data){
            var numero = $('#antiguos_pei').children().length + 1;
            $('#antiguos_pei').append('<tr class="alert alert-info" id="pei_'+data['id']+'"><th>'+numero+'.</th><td><span id="nombre_pei_'+data['id']+'">'+data['nombre']+'</span></td><td><button class="btn btn-danger" onclick="p_borrar_permiso_ingreso('+data['establecimiento_salud']+')"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>');

        });
        }
    });
}

function p_guardar_permiso_ingreso(){
    if ($('#establecimiento_salud').val() !== '') {
        var respuestas_json = $('#formulario_pei').serializeArray();
        console.log('respuestas json', respuestas_json);
        dataset_json = {};
        dataset_json['establecimiento_salud'] = $('#establecimiento_salud').val();
        dataset_json['usuario'] = $('#id').val();

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardarPermisoIngreso',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Guardado OK', data);
            data = JSON.parse(data);
            data = data[0];
            console.log('eval data:', data);
            if (data['ERROR']) {
                alert(data['ERROR']);
            } else {
                console.log('nuevo permiso');
                var numero = $('#antiguos_pei').children().length + 1;
                var nombre = $('#establecimiento_salud_typeahead').val();
                $('#antiguos_pei').append('<tr class="alert alert-info" id="pei_'+data['id']+'"><th>'+numero+'.</th><td><span id="nombre_pei_'+data['id']+'">'+nombre+'</span></td><td><button class="btn btn-danger" onclick="p_borrar_permiso_ingreso('+data['establecimiento_salud']+')"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>');
            }
            $('#establecimiento_salud_agregar').hide();
            $('#establecimiento_salud').val('');
            $('#establecimiento_salud_typeahead').val('');
        }).fail(function(xhr, err){
            console.error('ERROR AL GUARDAR', xhr, err);
            alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    } else {
        alert ('Ingrese el establecimiento de salud');
    }
}

function p_borrar_permiso_ingreso(ess_id){

    if (confirm('Seguro desea quitar el permiso de ingreso a este establecimiento de salud?')) {
        dataset_json = {};
        dataset_json['usuario'] = $('#id').val();
        dataset_json['establecimiento_salud'] = ess_id;
        dataset_json['borrar'] = 'borrar';

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardarPermisoIngreso',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK, data:', data);
            //data = eval(data)[0];
            data = JSON.parse(data);
            data = data[0];
            console.log('eval data:', data);
            if (data['ERROR']) {
                alert(data['ERROR']);
            } else {
                $('#pei_' + data['id']).remove();
            }
        }).fail(function(xhr, err){
            console.error('ERROR AL BORRAR', xhr, err);
            alert('Hubo un error al borrar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    }
}

function p_nuevo(){

    $('#formulario_titulo').text('nuevo');
    $('#formulario').trigger('reset');
    $('#id').val('');
    $('#formulario_eliminar').hide();
    $('#formulario_reiniciar').hide();
    $('#formulario_recuperar').hide();
    $('#formulario_guardar').show();
 
    $('#cedula').prop('disabled', false);

    $('#formulario').find(':input').each(function() {
        switch(this.type) {
        case 'password':
        case 'text':
        case 'textarea':
        case 'file':
        case 'select-one':
        case 'select-multiple':
        case 'date':
        case 'number':
        case 'tel':
        case 'email':
            $(this).val('');
            $(this).prop('disabled', false);
            break;
        case 'checkbox':
        case 'radio':
            this.checked = false;
            $(this).prop('disabled', false);
            break;
        }
    });

    $('#modal').modal('show');
}

function p_recuperar(){

    dataset_json = {};
    dataset_json['cedula'] = $('#cedula').val();
    dataset_json['id'] = $('#id').val();
    dataset_json['recuperar'] = 'recuperar';

    console.log('dataset_json', dataset_json);
    $.ajax({
    url: '_guardarUsuario',
        type: 'POST',
        //dataType: 'json',
        data: JSON.stringify(dataset_json),
        //contentType: 'application/json'
    }).done(function(data){
        console.log('RECUPERADO OK, data:', data);
        //data = eval(data)[0];
        data = JSON.parse(data);
        data = data[0];
        console.log('eval data:', data);
        if (data['ERROR']) {
            alert(data['ERROR']);
        } else {
            $('#nombre_' + data['id']).parent().parent().removeClass('alert alert-danger alert-info');
            $('#nombre_' + data['id']).parent().parent().addClass('alert alert-success');
            $('#modal').modal('hide');
        }

    }).fail(function(xhr, err){
        console.error('ERROR AL RECUPERAR', xhr, err);
        alert('Hubo un error al recuperar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
        //$('#modal').modal('hide');
    });
}

function p_borrar(){

    if (confirm('Seguro desea eliminar el Usuario ' + $('#cedula').val() + ' "' + $('#nombres').val() + ' ' + $('#apellidos').val() + '"')) {
        dataset_json = {};
        dataset_json['id'] = $('#id').val();
        dataset_json['cedula'] = $('#cedula').val();
        dataset_json['borrar'] = 'borrar';

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardarUsuario',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK, data:', data);
            //data = eval(data)[0];
            data = JSON.parse(data);
            data = data[0];
            console.log('eval data:', data);
            //$('#nombre_' + data['id']).parent().parent().remove();
            if (data['ERROR']) {
                alert(data['ERROR']);
            } else {
                $('#nombre_' + data['id']).parent().parent().removeClass('alert alert-success alert-info');
                $('#nombre_' + data['id']).parent().parent().addClass('alert alert-danger');
                $('#modal').modal('hide');
            }

        }).fail(function(xhr, err){
            console.error('ERROR AL BORRAR', xhr, err);
            alert('Hubo un error al borrar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    }
}

function p_reiniciar(){
    if (confirm('Seguro desea reiniciar la clave del Usuario ' + $('#cedula').val() + ' "' + $('#nombres').val() + ' ' + $('#apellidos').val() + '"')) {

        dataset_json = {};
        dataset_json['id'] = $('#id').val();
        dataset_json['cedula'] = $('#cedula').val();
        dataset_json['reiniciar'] = 'reiniciar';

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardarUsuario',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Reiniciado OK, data:', data);
            //data = eval(data)[0];
            data = JSON.parse(data);
            data = data[0];
            console.log('eval data:', data);
            if (data['ERROR']) {
                alert(data['ERROR']);
            } else {
                $('#nombre_' + data['id']).parent().parent().removeClass('alert alert-success alert-danger');
                $('#nombre_' + data['id']).parent().parent().addClass('alert alert-info');
                $('#modal').modal('hide');
            }

        }).fail(function(xhr, err){
            console.error('ERROR AL REINICIAR', xhr, err);
            alert('Hubo un error al reiniciar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    }
}

function p_guardar(){

    if ($('#nombres').val() !== '' && $('#apellidos').val() !== '' && $('#cedula').val() !== '' && $('#correo_electronico').val() !== '') {
        if (verificarCedula($('#cedula').val())) {
            var respuestas_json = $('#formulario').serializeArray();
            console.log('respuestas json', respuestas_json);
            dataset_json = {};
            respuestas_json.forEach(function(respuesta_json){
                var name =  respuesta_json['name'];
                var value = respuesta_json['value'];
                dataset_json[name] = value;

            });
            dataset_json['cedula'] = $('#cedula').val();

            console.log('dataset_json', dataset_json);
            $.ajax({
                url: '_guardarUsuario',
                    type: 'POST',
                    //dataType: 'json',
                    data: JSON.stringify(dataset_json),
                    //contentType: 'application/json'
            }).done(function(data){
                console.log('Guardado OK, data:', data);
                //data = eval(data)[0];
                data = JSON.parse(data);
                data = data[0];

                console.log('eval data:', data);
                if (data['ERROR']) {
                    alert(data['ERROR']);
                } else {

                    if ($("#nombre_" + data['id']).length) { // 0 == false; >0 == true
                        //ya existe:
                        $('#cedula_' + data['id']).text(data['cedula']);
                        $('#nombre_' + data['id']).text(data['apellidos'] + ' ' + data['nombres']);
                        $('#rol_' + data['id']).text(data['rol'] );
                        $('#correo_electronico_' + data['id']).text(data['correo_electronico']);
                    } else {
                        //nuevo:
                        console.log('nuevo USUARIO');
                        var numero = $('#antiguos').children().length + 1;
                        $('#antiguos').append('<tr class="alert alert-success"><th>'+numero+'.</th><td><a href="#" onclick="p_abrir(\''+data['id']+'\')">'+data['cedula']+'</a></td><td><span id="nombre_' + data['id'] + '">' + data['apellidos'] + ' ' + data['nombres'] + '</span></td><td><span id="rol_' + data['id'] + '">'+data['rol']+'</span></td><td><span id="correo_electronico_'+data['id']+'">'+data['correo_electronico'] + '</span></td></tr>');
                    }
                    $('#modal').modal('hide');
                }
            }).fail(function(xhr, err){
                console.error('ERROR AL GUARDAR', xhr, err);
                alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
                //$('#modal').modal('hide');
            });
        } else {
            alert ('Ingrese un número de cédula válido');
        }
    } else {
        alert ('Ingrese al menos el número de cédula, nombres, apellidos y correo electrónico');
    }
}

function p_guardar_old(){

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
                    //data = eval(data);
                    data = JSON.parse(data);

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

function p_eliminar_old(cedula, nombre){
    if (confirm('Seguro desea eliminar el Usuario ' + $('#cedula').val() + ' "' + $('#nombres').val() + ' ' + $('#apellidos').val() + '"')) {
        var dataset_json = [{id:$('#id').val()}];
        console.log('dataset_json',dataset_json);
        $.ajax({
            url: '_borrar/usuario',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK', data)
                //data = eval(data);
                data = JSON.parse(data);

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
      console.log('Digito calculado', digito_calculado);
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
