<h1>Espacios de Evaluaci&oacute;n</h1>
<?php
$rol_id = $_SESSION['rol'];

$ess_id = $_SESSION['ess_id'];
$tipos_evaluacion = q("SELECT * FROM esamyn.esa_tipo_evaluacion");

$filtro = ' AND eva_borrado IS NULL ';
if ($rol_id == 1) {
    $filtro = '';
}

$espacios = q("
    SELECT *
    ,(
        SELECT 
        tev_nombre 
        FROM esamyn.esa_tipo_evaluacion 
        WHERE tev_id=eva_tipo_evaluacion
    ) AS tev_nombre 
    FROM esamyn.esa_evaluacion 
    WHERE eva_establecimiento_salud=$ess_id 
    $filtro
    ORDER BY eva_creado
");
?>

<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Espacio de evaluaci&oacute;n <span id="formulario_titulo"></span></h4>
      </div>
      <div class="modal-body">


<form id="formulario" class="form-horizontal">
<input type="hidden" id="id" name="id" value="">
  <div class="form-group">
    <label for="descripcion" class="col-sm-2 control-label">Descripci&oacute;n:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Descripcion">
    </div>
  </div>
  <div class="form-group">
    <label for="tipo_evaluacion" class="col-sm-2 control-label">Tipo de evaluaci&oacute;n:</label>
    <div class="col-sm-10">
      <select class="form-control" id="tipo_evaluacion" name="tipo_evaluacion" placeholder="Tipo de evaluacion">
        <?php foreach ($tipos_evaluacion as $tipo_evaluacion): ?>
        <option value="<?=$tipo_evaluacion['tev_id']?>"><?=$tipo_evaluacion['tev_nombre']?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="p_borrar('borrar')" id="formulario_eliminar">Eliminar</button>
        <button type="button" class="btn btn-success" onclick="p_borrar('recuperar')" id="formulario_recuperar">Recuperar</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
var eva_activo_id = 0; 
</script>
<table class="table table-striped">
  <tr>
    <th></th>
    <th>Fecha y hora de creaci&oacute;n</th>
    <th>Descripci&oacute;n</th>
    <th>Tipo de evaluaci&oacute;n</th>
    <th>Avance</th>
    <th>Calificación</th>
    <th></th>
  </tr>
<tbody id="antiguos">
<?php if ($espacios): ?>
    <?php foreach ($espacios as $i => $espacio): ?>
    <tr id="espacio_<?=$espacio['eva_id']?>" class="<?php echo (!empty($espacio['eva_borrado']) ? 'alert alert-danger' :($espacio['eva_activo'] ? 'alert alert-warning' : '')); ?>">
    <th><?php echo ($i+1).'.&nbsp;'; ?></th>
    <td><span id=""><a href="#" onclick="p_abrir('<?=$espacio['eva_id']?>');return false;"><?=p_formatear_fecha($espacio['eva_creado'])?></a></span></td>
    <td><span id="descripcion_<?=$espacio['eva_id']?>"><?php echo $espacio['eva_descripcion']; ?></span></td>
    <td><span id="tipo_evaluacion_<?=$espacio['eva_id']?>"><?=$espacio['tev_nombre']?></span></td>
    <td><?=$espacio['eva_porcentaje_avance']?>%</td>
<?php
$clase_destacado = ($espacio['eva_cumplido_minimos'] == 1 && $espacio['eva_cumplido_obligatorios'] == 1) ? 'success': 'danger';
echo '<td class="alert alert-'.$clase_destacado.'">';
$calificacion = (int)$espacio['eva_calificacion'];
echo "$calificacion%";
if ($espacio['eva_cumplido_minimos'] == 0) {
    echo '<div class="alert alert-danger pull-right">No cumple mínimos</div>';
}
if ($espacio['eva_cumplido_obligatorios'] == 0) {
    echo '<div class="alert alert-danger pull-right">No cumple obligatorios</div>';
}
?></td>
    <td><span id="activar_<?=$espacio['eva_id']?>"><?php if ($espacio['eva_activo'] ): ?><strong>ACTIVO</strong><?php else: ?><button class="btn btn-warning" onclick="p_activar(<?=$espacio['eva_id']?>)">Activar</button><?php endif; ?></span></td>
    
    <?php if($espacio['eva_activo']): ?>
    <script>
    eva_activo_id = <?=$espacio['eva_id']?>; 
    </script>
    <?php endif; ?>

  </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>



<script>

var tipos_evaluacion = <?php echo json_encode($tipos_evaluacion); ?>;

function p_abrir(id){
    $.ajax({
        'url':'/_listar/evaluacion/'+id
    }).done(function(data){
        data = eval(data);
        evaluacion = data[0];
        console.log(evaluacion);
        var badge = '';
        if (evaluacion['borrado'] == null) {
            $('#formulario_eliminar').show();
            $('#formulario_guardar').show();
            $('#formulario_recuperar').hide();
        } else {
            badge = '<span class="badge">ELIMINADO</span>';
            $('#formulario_eliminar').hide();
            $('#formulario_recuperar').show();
            $('#formulario_guardar').hide();
        }
        $('#formulario_titulo').html(evaluacion['creado'] + ' "' + evaluacion['descripcion'] + '" ' + badge);
        //$("#cedula").prop('disabled', true);
        for (key in evaluacion){
            $('#' + key).val(evaluacion[key]);
        }
        
        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_guardar(){
    if ($('#descripcion').val() !== '') {
        var respuestas_json = $('#formulario').serializeArray();
        console.log(respuestas_json);
        dataset_json = [];
        dataset_json[0] = {};
        respuestas_json.forEach(function(respuesta_json){
            var name =  respuesta_json['name'];
            var value = respuesta_json['value'];
            dataset_json[0][name] = value;

        });

        dataset_json[0]['establecimiento_salud'] = <?=$ess_id?>;

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardar/evaluacion',
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify(dataset_json),
        //data: dataset_json,
        contentType: 'application/json'
        }).done(function(data){
            console.log('Guardado OK', data);
            data = eval(data);

            var tipo_evaluacion = tipos_evaluacion.reduce(function (valorAnterior, valorActual) {
                return (valorActual.tev_id == data[0]['tipo_evaluacion'] ? valorActual.tev_nombre : valorAnterior);
            }, '');

            console.log('tipo_evaluacion', tipos_evaluacion, tipo_evaluacion);

            if($("#descripcion_" + data[0]['id']).length) { // 0 == false; >0 == true
                //ya existe:
                $('#descripcion_' + data[0]['id']).text(data[0]['descripcion']);
                $('#tipo_evaluacion_' + data[0]['id']).text(tipo_evaluacion);
            } else {
                //nuevo:
                console.log('nuevo ESPACIO');
                var numero = $('#antiguos').children().length + 1;
                $('#antiguos').append('<tr id="espacio_'+data[0]['id']+'"><th>'+numero+'.</th><td><a href="#" onclick="p_abrir(\''+data[0]['id']+'\')">'+data[0]['creado']+'</a></td><td><span id="descripcion_' + data[0]['id'] + '">' + data[0]['descripcion'] + '</span></td><td><span id="tipo_evaluacion_'+data[0]['id']+'">' + tipo_evaluacion + '</span></td><td>0%</td><td class="alert alert-danger">0%<div class="alert alert-danger pull-right">No cumple mínimos</div><div class="alert alert-danger pull-right">No cumple obligatorios</div></td><td><span id="activar_'+data[0]['id']+'"><button class="btn btn-warning" onclick="p_activar('+data[0]['id']+')">Activar</button></span></td></tr>');
            }
            $('#modal').modal('hide');
        }).fail(function(xhr, err){
            console.error('ERROR AL GUARDAR', xhr, err);
            $('#modal').modal('hide');
        });
    } else {
        alert ('Ingrese la descripción del espacio de evaluación');
    }
}

function p_borrar(accion){
    var dataset_json = {};
    dataset_json['id'] = $('#id').val();
    dataset_json[accion] = accion;

    console.log('dataset_json', dataset_json);

    $.ajax({
    url: '_borrarEvaluacion',
        type: 'POST',
        //dataType: 'json',
        data: JSON.stringify(dataset_json),
        //contentType: 'application/json'
    }).done(function(data){
        console.log(accion + ' OK, data:', data);
        //data = eval(data)[0];
        data = JSON.parse(data);
        data = data[0];
        console.log('eval data:', data);
        //$('#nombre_' + data['id']).parent().parent().remove();
        if (data['ERROR']) {
            alert(data['ERROR']);
        } else {
            $('#espacio_' + data['id']).removeClass('alert alert-success alert-danger');
            if (accion == 'borrar') {
                $('#espacio_' + data['id']).addClass('alert alert-danger');
                $('#recuperar_' + data['id']).removeClass('hidden');
                $('#borrar_' + data['id']).addClass('hidden');
            } else {
                $('#espacio_' + data['id']).addClass('alert alert-success');
                $('#recuperar_' + data['id']).addClass('hidden');
                $('#borrar_' + data['id']).removeClass('hidden');
            }
        }

    }).fail(function(xhr, err){
        console.error('ERROR AL '+accion, xhr, err);
        alert('Hubo un error al '+accion+', verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
        //$('#modal').modal('hide');
    });

}
function p_nuevo(){

    $('#formulario_titulo').text('nuevo');
    $('#formulario').trigger('reset');
    $('#id').val('');
    $('#modal').modal('show');
    $('#formulario_eliminar').hide();
 
    $('#cedula').prop('disabled', false);

}

function p_activar(eva_id){

    var dataset_json = {'eva_id':eva_id};

    $.ajax({
        url: '_activar',
        type: 'POST',
        dataType: 'json',
        data: JSON.stringify(dataset_json),
        contentType: 'application/json'
    }).done(function(evaluaciones){
        console.log('Activado OK', evaluaciones);

        evaluaciones.forEach(function(eva){
            if (eva.eva_id == eva_id) {
                $('#espacio_' + eva_id).addClass('alert alert-warning');
                $('#activar_' + eva_id).html('<strong>ACTIVADO</strong>');
            } else {
                 $('#espacio_' + eva.eva_id).removeClass('alert alert-warning');
                 $('#activar_' + eva.eva_id).html('<button class="btn btn-warning" onclick="p_activar('+eva.eva_id+')">Activar</button>');
            }
        });
    }).fail(function(xhr, err){
        console.error('Error al activar', xhr, err);
    });

}


function p_activar_old(eva_id){

    var dataset_json = [{'id':eva_id, 'activo': 1}];
    var anterior_eva_activo_id = eva_activo_id;

    $.ajax({
        url: '_guardar/evaluacion',
        type: 'POST',
        dataType: 'json',
        data: JSON.stringify(dataset_json),
        //data: dataset_json,
        contentType: 'application/json'
    }).done(function(data){
        console.log('Activado OK', data);
        data = eval(data);
        eva_activo_id = data[0]['id'];

        var dataset_json = [{'id':anterior_eva_activo_id, 'activo': 0}];
        $.ajax({
        url: '_guardar/evaluacion',
            type: 'POST',
            dataType: 'json',
            data: JSON.stringify(dataset_json),
        //data: dataset_json,
        contentType: 'application/json'
        }).done(function(data){
            console.log('Activado OK', data);
            data = eval(data);
            $('#espacio_' + eva_activo_id).addClass('alert alert-warning');
            $('#activar_' + eva_activo_id).html('<strong>ACTIVADO</strong>');

            $('#espacio_' + anterior_eva_activo_id).removeClass('alert alert-warning');
            $('#activar_' + anterior_eva_activo_id).html('<button class="btn btn-warning" onclick="p_activar('+anterior_eva_activo_id+')">Activar</button>');

        }).fail(function(xhr, err){
            console.error('ERROR AL GUARDAR', xhr, err);
            $('#modal').modal('hide');
        });

    }).fail(function(xhr, err){
        console.error('ERROR AL GUARDAR', xhr, err);
        $('#modal').modal('hide');
    });

}
</script>
