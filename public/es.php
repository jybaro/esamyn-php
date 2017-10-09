<?php
$es_listado = q("SELECT * FROM esamyn.esa_establecimiento_salud ORDER BY ess_unicodigo");
?>

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
    <label for="nombre_responsable" class="col-sm-2 control-label">Nombre del responsable:</label>
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
</form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="p_guardar()">Guardar cambios</button>
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
        data = eval(data);
        es = data[0];
        console.log(es);
        $('#formulario_titulo').text(es['unicodigo']);
        for (key in es){
            $('#' + key).val(es[key]);
        }
    }).fail(function(){
        console.error('ERROR AL ABRIR');
    });
    $('#modal').modal('show');
}

var cantones = <?php
$result = q("SELECT * FROM esamyn.esa_provincia, esamyn.esa_canton WHERE pro_id = can_provincia");
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
})

function p_validar_canton(){
    console.log('on blur')
    if ($('#canton').val() == ''){
        $('#canton_typeahead').val('');
    }
}

function p_guardar(){
    var respuestas_json = $('#formulario').serializeArray();
    console.log(respuestas_json);
    dataset_json = [];
    dataset_json[0] = {};
    respuestas_json.forEach(function(respuesta_json){
        var name = 'ess_' + respuesta_json['name'];
        var value = respuesta_json['value'];
        dataset_json[0][name]=value;

    });

    console.log(dataset_json);
    $.ajax({
        url: '_guardar/establecimiento_salud',
        type:'post',
        dataType: 'json',
        data:dataset_json
    }).done(function(data){
        console.log('Guardado OK', data)
    }).fail(function(){
        console.error('ERROR AL GUARDAR');
    });

}
</script>
<table class="table table-striped">
<tr>
<th></th>
<th>Unicódigo</th>
<th>Nombre</th>
<th>Zona</th>
</tr>
<?php foreach($es_listado as $i=>$es): ?>
<tr>
<th><?php echo ($i+1).'.&nbsp;'; ?></th>
<td><a href="#" onclick="p_abrir('<?=$es['ess_id']?>');return false;"><?php echo $es['ess_unicodigo']; ?></a></td>
<td><?php echo $es['ess_nombre']; ?></td>
<td><?php echo $es['ess_zona']; ?></td>
</tr>
<?php endforeach; ?>
</table>
