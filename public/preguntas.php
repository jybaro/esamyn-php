<?php
$us_listado = q("SELECT *  FROM esamyn.esa_formulario ORDER BY frm_clave");
?>

<table class="table table-striped">
<tr>
<th></th>
<th>Clave</th>
<th>Nombre</th>
</tr>
<?php foreach($us_listado as $i=>$us): ?>
<tr>
<th><?php echo ($i+1).'.&nbsp;'; ?></th>
<td><a href="#" onclick="p_abrir('<?=$us['frm_id']?>');return false;"><?php echo $us['frm_clave']; ?></a></td>
<td><?php echo $us['frm_nombre']; ?> </td>
</tr>
<?php endforeach; ?>
</table>
