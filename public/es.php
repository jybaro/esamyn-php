<?php
$es_listado = q("SELECT * FROM esamyn.esa_establecimiento_salud");
?>

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
<td><?php echo $es['ess_unicodigo']; ?></td>
<td><?php echo $es['ess_nombre']; ?></td>
<td><?php echo $es['ess_zona']; ?></td>
</tr>
<?php endforeach; ?>
</table>
