<?php

//$condiciones = q('SELECT * FROM esamyn.esa_condicion_no_aplica');
$count = 0;
?>

<table>
<tr>
<th>&nbsp;</th>
<th>Descripci&oacute;n<th>
<th>Acci&oacute;n</th>
</tr>
<tbody id="padre">
<?php //foreach($condiciones as $condicion): ?>
 <tr id="plantilla" style="xxxdisplay:none;">
<th><?php //echo ($count++) . '.'; ?></th>
<td><?php //echo $condicion['cna_nombre']; ?></td>
<td><a href="#">Editar</a>
 | 
<td><a href="#">Borrar</a>
</tr>
<?php //endforeach; ?>
</tbody>
</table>

<button type="button" class="btn btn-primary btn-lg" onclick="p_abrir_modal()">
  Nuevo registro
</button>

<div id="myModal" class="modal fade" tabindex="-1" role="dialog"  aria-labelledby="myModalLabel" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 id="titulo" class="modal-title">&nbsp;</h4>
      </div>
      <div class="modal-body">
        <form>


  <div class="form-group">
    <label for="descripcion">Descripci&oacute;n</label>
    <input type="text" class="form-control" id="descripcion" placeholder="Ingrese la descripción de la condición">
  </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="boton" onclick="p_enviar()">&nbsp;</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
function p_abrir_modal(data){
    data = typeof(data) === 'undefined' ? [] : data;
    console.log(data);
    if (data.length == 0) {
        //nuevo
        document.getElementById('titulo').innerHTML = 'Nuevo registro';
        document.getElementById('boton').innerHTML = 'Crear registro';
    } else {
        //editar
        document.getElementById('titulo').innerHTML = 'Editar registro';
        document.getElementById('boton').innerHTML = 'Guardar cambios';
    }
    $('#myModal').modal('show');
}

function p_enviar(){
    var condicion_no_aplica = {};
    $.ajax({
        url: '/_guardar/condicion_no_aplica',
        type: 'POST',
        dataType: 'json',
        success: function(data){
            console.log('Exito', data);
            p_listar();
        },
        data:condicion_no_aplica
    });
}
function p_listar(){
    $.ajax({
        url: '/_listar/condicion_no_aplica',
        success: function(data){
            p_renderizar(eval(data));
        }
    });
}

function p_renderizar(data) {
    var padre = $('#padre');
    console.log(data);
    data.forEach(function(registro){
        var template = $('#template').clone();
        template.attr('id', 'registro') ;
        console.log(template);
        padre.append(template);
    });
}
$( document ).ready(function() {
    p_listar();
});

</script>
