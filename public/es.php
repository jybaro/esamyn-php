<?php
$rol = $_SESSION['rol'];
$filtro = '';
if ($rol != 1){
    //Los supervisores solo pueden crear y editar a operadores
    $filtro .= " AND ess_borrado IS NULL ";
}

$es_listado = q("SELECT * FROM esamyn.esa_establecimiento_salud WHERE 1=1 $filtro ORDER BY ess_borrado DESC, ess_unicodigo");
?>

<h2>Establecimientos de Salud</h2>

<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>

<hr />
<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Establecimiento de Salud <span id="formulario_titulo"></span></h4>
      </div>
      <div class="modal-body">


<form id="formulario" class="form-horizontal">
<input type="hidden" id="id" name="id" value="">
  <div class="form-group">
    <label for="unicodigo" class="col-sm-2 control-label">UNICODIGO:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="unicodigo" name="unicodigo" placeholder="UNICODIGO">
    </div>
  </div>
  <div class="form-group">
    <label for="nombre" class="col-sm-2 control-label">Nombre:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre">
    </div>
  </div>
  <div class="form-group">
    <label for="canton" class="col-sm-2 control-label">Cantón:</label>
    <div class="col-sm-10">
      <input type="hidden" id="canton" name="canton" value="">
      <input class="form-control" required type="text" id="canton_typeahead" data-provide="typeahead" autocomplete="off" placeholder="Cantón" onblur="p_validar_canton()">
    </div>
  </div>
  <div class="form-group">
    <label for="direccion" class="col-sm-2 control-label">Dirección:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección">
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
    <label for="nombre_responsable" class="col-sm-2 control-label">Nombre responsable:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="nombre_responsable" name="nombre_responsable" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="zona" class="col-sm-2 control-label">Zona:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="zona" name="zona" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="distrito" class="col-sm-2 control-label">Distrito:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="distrito" name="distrito" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="nivel" class="col-sm-2 control-label">Nivel:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="nivel" name="nivel" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="tipologia" class="col-sm-2 control-label">Tipología:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="tipologia" name="tipologia" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="certificacion" class="col-sm-2 control-label">Certificación:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="certificacion" name="certificacion" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="max_usuarios" class="col-sm-2 control-label">Máx usuarios:</label>
    <div class="col-sm-10">
      <input type="number" class="form-control" step="1" min="0" max="100" id="max_usuarios" name="max_usuarios" placeholder="">
    </div>
  </div>
</form>

<hr>
<h5>PERMISOS DE INGRESO</h5>

<form id="formulario_pei" class="form-horizontal">
  <div class="form-group">
    <label for="Usuario" class="col-sm-2 control-label">Usuario:</label>
    <div class="col-sm-8">
      <input type="hidden" id="usuario" name="usuario" value="">
      <input class="form-control" required type="text" id="usuario_typeahead" data-provide="typeahead" autocomplete="off" placeholder="Ingrese al menos 3 caracteres" onblur="p_validar_usuario()">
    </div>
    <div class="col-sm-1">
      <button type="button" class="btn btn-info" id="usuario_agregar" onclick="p_guardar_permiso_ingreso()"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
    </div>
  </div>
</form>

<table class="table table-striped">
<tbody id="antiguos_pei"></tbody>
</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="p_borrar()" id="formulario_eliminar">Eliminar</button>
        <button type="button" class="btn btn-success" onclick="p_recuperar()" id="formulario_recuperar">Recuperar</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script src="/js/bootstrap3-typeahead.min.js"></script>
<script>
function p_abrir(ess_id){
    $.ajax({
        'url':'/_listar/establecimiento_salud/'+ess_id
    }).done(function(data){
        data = JSON.parse(data);
        //data = eval(data);
        es = data[0];
        console.log('ABRIENDO ES',es);

        var badge = '';
        var disabled = false;
        if (es['borrado'] == null) {
            $('#formulario_eliminar').show();
            $('#formulario_guardar').show();
            $('#formulario_recuperar').hide();
            disabled = false;
            p_abrir_permiso_ingreso(es['id']);
        
        } else {
            badge = '<span class="badge">ELIMINADO</span>';
            $('#formulario_eliminar').hide();
            $('#formulario_guardar').hide();
            $('#formulario_recuperar').show();
            disabled = true;
        }
        $('#formulario_titulo').html(es['unicodigo'] + ' "' + es['nombre'] + '" ' + badge);
        for (key in es){
            $('#' + key).val(es[key]);
            $('#' + key).prop('disabled', disabled);
        }

        $('#canton_typeahead').val(cantones.reduce(function(valorAnterior, valorActual, indice, vector){
            return (valorActual.id == es.canton ? valorActual.name : valorAnterior);
        }, ''));


        $('#usuario_typeahead').val('');
        $('#usuario').val('');
        $('#usuario_agregar').hide();

        $("#unicodigo").prop('disabled', true);
        

        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_abrir_permiso_ingreso(establecimiento_salud) {
    $('#antiguos_pei').html('');
    $.get('/_listarPermisoIngreso/establecimiento_salud/' + establecimiento_salud, function(dataset){
        console.log(dataset);
        dataset = JSON.parse(dataset);

        if (dataset.respuestas) {
        dataset.respuestas.forEach(function(data){
            var numero = $('#antiguos_pei').children().length + 1;
            $('#antiguos_pei').append('<tr class="alert alert-info" id="pei_'+data['id']+'"><th>'+numero+'.</th><td><span id="nombre_pei_'+data['id']+'">'+data['nombre']+'</span></td><td><button class="btn btn-danger" onclick="p_borrar_permiso_ingreso('+data['usuario']+')"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>');

        });
        }
    });
}

var escogido = {id:"",name:""};
var cantones = <?php
$result = q("SELECT * FROM esamyn.esa_provincia, esamyn.esa_canton WHERE pro_id = can_provincia ORDER BY can_nombre");
$cantones = array();
foreach($result as $r){
    $cantones[] = array(
        'id'=>$r['can_id'],
        'name'=>$r['can_nombre'].' ('.$r['pro_nombre'].')'
    );
}
echo json_encode($cantones);
?>;

$(document).ready(function() {
    $('#canton_typeahead').typeahead({
        source:cantones,
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
            $('#canton').val(item.id);
            escogido.id = item.id;
            escogido.name = item.name;

            return item.name;

        }
});

    $('#usuario_typeahead').typeahead({
        source:function(query, process){
            $.get('/_listarUsuario/' + query, function(data){
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
            $('#usuario').val(item.id);
            $('#usuario_agregar').show();
            return item.name;
        }
    });
})

function p_validar_canton(){
    console.log('on blur')
    if ($('#canton').val() == ''){
        $('#canton_typeahead').val('');
    }
}

function p_validar_usuario(){
    console.log('on blur usuario')
    if ($('#usuario').val() == ''){
        $('#usuario_typeahead').val('');
    }
}
function p_recuperar(){

    dataset_json = {};
    dataset_json['unicodigo'] = $('#unicodigo').val();
    dataset_json['id'] = $('#id').val();
    dataset_json['recuperar'] = 'recuperar';

    console.log('dataset_json', dataset_json);
    $.ajax({
    url: '_guardarES',
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

    if (confirm('Seguro desea eliminar el Establecimiento de Salud ' + $('#unicodigo').val() + ' "' + $('#nombre').val() + '"')) {
        var dataset_json = {};
        dataset_json['id'] = $('#id').val();
        dataset_json['unicodigo'] = $('#unicodigo').val();
        dataset_json['borrar'] = 'borrar';

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardarES',
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

function p_guardar(){
    if ($('#nombre').val() !== '' && $('#unicodigo').val() !== '' && $('#canton').val() !== '') {
        if (/^\d{6}$/.test($('#unicodigo').val())) {
            var respuestas_json = $('#formulario').serializeArray();
            console.log('respuestas json', respuestas_json);
            dataset_json = {};
            respuestas_json.forEach(function(respuesta_json){
                var name =  respuesta_json['name'];
                var value = respuesta_json['value'];
                dataset_json[name]=value;

            });
            dataset_json['unicodigo'] = $('#unicodigo').val();

            console.log('dataset_json', dataset_json);
            $.ajax({
            url: '_guardarES',
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
                    if ($("#nombre_" + data['id']).length) { // 0 == false; >0 == true
                        //ya existe:
                        $('#nombre_' + data['id']).text(data['nombre']);
                        $('#zona_' + data['id']).text(data['zona']);
                    } else {
                        //nuevo:
                        console.log('nuevo ES');
                        var numero = $('#antiguos').children().length + 1;
                        $('#antiguos').append('<tr class="alert alert-success"><th>'+numero+'.</th><td><a href="#" onclick="p_abrir(\''+data['id']+'\')">'+data['unicodigo']+'</a></td><td><span id="nombre_'+data['id']+'">'+data['nombre']+'</span></td><td><span id="zona_'+data['id']+'">'+data['zona']+'</span></td></tr>');
                    }
                    $('#modal').modal('hide');
                }
            }).fail(function(xhr, err){
                console.error('ERROR AL GUARDAR', xhr, err);
                alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
                //$('#modal').modal('hide');
            });
        } else {
            alert ('El valor del UNICÓDIGO debe tener seis dígitos.');
        }
    } else {
        alert ('Ingrese el UNICÓDIGO, nombre y el cantón.');
    }
}

function p_guardar_permiso_ingreso(){
    if ($('#usuario').val() !== '') {
        var respuestas_json = $('#formulario_pei').serializeArray();
        console.log('respuestas json', respuestas_json);
        dataset_json = {};
        dataset_json['usuario'] = $('#usuario').val();
        dataset_json['establecimiento_salud'] = $('#id').val();

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
                var nombre = $('#usuario_typeahead').val();
                $('#antiguos_pei').append('<tr class="alert alert-info" id="pei_'+data['id']+'"><th>'+numero+'.</th><td><span id="nombre_pei_'+data['id']+'">'+nombre+'</span></td><td><button class="btn btn-danger" onclick="p_borrar_permiso_ingreso('+data['usuario']+')"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>');
            }
            $('#usuario_agregar').hide();
            $('#usuario').val('');
            $('#usuario_typeahead').val('');
        }).fail(function(xhr, err){
            console.error('ERROR AL GUARDAR', xhr, err);
            alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    } else {
        alert ('Ingrese el usuario');
    }
}


function p_borrar_permiso_ingreso(usu_id){

    if (confirm('Seguro desea quitar el permiso de ingreso a este usuario?')) {
        dataset_json = {};
        dataset_json['establecimiento_salud'] = $('#id').val();
        dataset_json['usuario'] = usu_id;
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






function p_guardar_old(){
    if ($('#nombre').val() !== '' && $('#unicodigo').val() !== '' && $('#canton').val() !== '') {
    var respuestas_json = $('#formulario').serializeArray();
    console.log(respuestas_json);
    dataset_json = [];
    dataset_json[0] = {};
    respuestas_json.forEach(function(respuesta_json){
        var name =  respuesta_json['name'];
        var value = respuesta_json['value'];
        dataset_json[0][name]=value;

    });

    console.log('dataset_json', dataset_json);
    $.ajax({
        url: '_guardar/establecimiento_salud',
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
            $('#nombre_' + data[0]['id']).text(data[0]['nombre']);
            $('#zona_' + data[0]['id']).text(data[0]['zona']);
        } else {
            //nuevo:
            console.log('nuevo ES');
            var numero = $('#antiguos').children().length + 1;
            $('#antiguos').append('<tr><th>'+numero+'.</th><td><a href="#" onclick="p_abrir(\''+data[0]['id']+'\')">'+data[0]['unicodigo']+'</a></td><td><span id="nombre_'+data[0]['id']+'">'+data[0]['nombre']+'</span></td><td><span id="zona_'+data[0]['id']+'">'+data[0]['zona']+'</span></td></tr>');
        }
        $('#modal').modal('hide');
    }).fail(function(xhr, err){
        console.error('ERROR AL GUARDAR', xhr, err);
        $('#modal').modal('hide');
    });
    } else {
        alert ('Ingrese el UNICÓDIGO, nombre y el cantón');
    }
}

function p_nuevo(){

    $('#formulario_titulo').text('nuevo');
    $('#formulario').trigger('reset');
    $('#id').val('');
    $('#canton').val('');
    $('#modal').modal('show');
    $('#formulario_eliminar').hide();
    $('#formulario_recuperar').hide();
    $('#formulario_guardar').show();
 
    $('#unicodigo').prop('disabled', false);

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

function p_eliminar_old(unicodigo, nombre){
    if (confirm('Seguro desea eliminar el Establecimiento de Salud ' + $('#unicodigo').val() + ' "' + $('#nombre').val() + '"')) {
        var dataset_json = [{id:$('#id').val()}];
        $.ajax({
            url: '_borrar/establecimiento_salud',
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
</script>
<table class="table table-striped">
<tr>
<th></th>
<th>Unicódigo</th>
<th>Nombre</th>
<th>Zona</th>
</tr>
<tbody id="nuevos"></tbody>
<tbody id="antiguos">
<?php foreach($es_listado as $i=>$es): ?>
<tr class="<?php echo (empty($es['ess_borrado']) ? '' : 'alert alert-danger'); ?>">
<th><?php echo ($i+1).'.&nbsp;'; ?></th>
<td><a href="#" onclick="p_abrir('<?=$es['ess_id']?>');return false;"><?php echo (empty($es['ess_unicodigo']) ? '[[sin unicodigo]]' : $es['ess_unicodigo']) ; ?></a></td>
<td><span id="nombre_<?=$es['ess_id']?>"><?php echo $es['ess_nombre']; ?></span></td>
<td><span id="zona_<?=$es['ess_id']?>"><?php echo $es['ess_zona']; ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
