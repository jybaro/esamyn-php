<?php
$us_listado = q("SELECT *, (SELECT rol_nombre FROM esamyn.esa_rol WHERE rol_id=usu_rol) AS rol FROM esamyn.esa_usuario ORDER BY usu_cedula");
?>

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
