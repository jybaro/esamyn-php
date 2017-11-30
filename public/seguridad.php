<div class="page-header">
<h1>Seguridad</h1>
</div>

<?php

$roles = q("SELECT * FROM esamyn.esa_rol ORDER BY rol_id");
$modulos = q("SELECT * FROM esamyn.esa_modulo ORDER BY mod_id");
$seguridades = q("SELECT * from esamyn.esa_seguridad ORDER BY seg_id");
$permisos = array();
if ($seguridades){
    foreach($seguridades as $seguridad) {
        if (!isset($permisos[$seguridad['seg_modulo']])) {
            $permisos[$seguridad['seg_modulo']] = array();
        }
        $permisos[$seguridad['seg_modulo']][$seguridad['seg_rol']] = 1;
    }
}
?>

<table class="table table-striped table-condensed table-hover">
<thead>
<tr>
<th>&nbsp;</th>
<?php foreach($roles as $rol): ?>
<th><?=$rol['rol_nombre']?></th>
<?php endforeach; ?>
</tr>
</thead>
<?php foreach($modulos as $count => $modulo): ?>
<tr>
<th><?=($count+1) . '. ' . $modulo['mod_texto']?></th>
<?php foreach($roles as $rol): ?>
<td><a href="#" onclick="p_cambiar_seguridad(<?=$modulo['mod_id']?>, <?=$rol['rol_id']?>);return false;"><img id="permiso_<?=$modulo['mod_id']?>_<?=$rol['rol_id']?>" src="/img/<?=((isset($permisos[$modulo['mod_id']]) && isset($permisos[$modulo['mod_id']][$rol['rol_id']]))?'si':'no')?>.png" style="width:20px;height:20px;" /></a></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
<tbody>
</tbody>
</table>
<script>
function p_cambiar_seguridad(modulo, rol){
    if (confirm('Seguro desea cambiar este permiso?')) {
        var dataset_json = {modulo:modulo, rol:rol};
        console.log('dataset_json',dataset_json);
        $.ajax({
            url: '/_cambiarSeguridad',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Cambiado OK', data);
            //data = eval(data);
            data = JSON.parse(data);
            if (data['error'].length != 0) {
                data['error'].forEach(function(error){
                    alert(error);
                });
            } else {
                data = data['respuesta'];

                var si_no = (data['count_seguridad'] == 0 ? 'no' : 'si');
                $('#permiso_' + data['modulo'] + '_' + data['rol']).attr('src', '/img/' + si_no + '.png');
            }
        }).fail(function(xhr, err){
            console.error('ERROR AL CAMBIAR', xhr, err);
            alert('No se pudo cambiar el permiso.');
        });
    }

}
</script>

