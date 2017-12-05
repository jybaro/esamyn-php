<h2>Reporte de Evaluaciones</h2>
<?php

$ess_id = $_SESSION['ess_id'];
$rol_id = $_SESSION['rol'];

/*
$evaluacion = q("
    SELECT 
    * 
    FROM 
    esamyn.esa_evaluacion
    ,esamyn.esa_tipo_evaluacion
    WHERE eva_establecimiento_salud = $ess_id
    AND eva_tipo_evaluacion = tev_id
    AND eva_activo = 1
    AND eva_borrado IS NULL
    ");

if (!$evaluacion) {
    echo '<div class="alert alert-danger"><h2>No hay evaluaci&oacute;n activa</h2>Solicite a su supervisor que cree una evaluación para este Establecimiento de Salud.</div>';
    return;
} else {
    $evaluacion = $evaluacion[0];
    $_SESSION['evaluacion'] = $evaluacion;
    $eva_id = $evaluacion['eva_id'];
}
 */

$filtro = '';

if (!empty($_POST)) {
    if (!empty($_POST['zona'])) {
        $filtro .= " AND ess_zona = '{$_POST[zona]}' ";
    }     
    if (!empty($_POST['distrito'])) {
        $filtro .= " AND ess_distrito = '{$_POST[distrito]}' ";
    }     
    if (!empty($_POST['nivel'])) {
        $filtro .= " AND ess_nivel = '{$_POST[nivel]}' ";
    }     
    if (!empty($_POST['tipologia'])) {
        $filtro .= " AND ess_tipologia = '{$_POST[tipologia]}' ";
    }     
    if (!empty($_POST['provincia'])) {
        $filtro .= " AND pro_nombre = '{$_POST[provincia]}' ";
    }     
    if (!empty($_POST['canton'])) {
        $filtro .= " AND can_nombre = '{$_POST[canton]}' ";
    }     
    if (!empty($_POST['tipo'])) {
        $filtro .= " AND tev_nombre = '{$_POST[tipo]}' ";
    }
}


$evaluaciones = q("
SELECT 
*
FROM esamyn.esa_evaluacion
, esamyn.esa_establecimiento_salud
, esamyn.esa_tipo_evaluacion
, esamyn.esa_canton
, esamyn.esa_provincia
WHERE
eva_establecimiento_salud = ess_id
    AND eva_tipo_evaluacion = tev_id
    AND ess_canton = can_id
    AND can_provincia = pro_id
    AND eva_borrado IS NULL
$filtro
");

?>

<div class="container-fluid">
<div class="panel panel-info">
<div class="panel-heading">Filtro</div>
  <div class="panel-body">

<form method="POST" class="">

<div class="row">
<div class="col-md-2">
<ul class="nav nav-pills nav-stacked">
  <li role="presentation" class="<?=(isset($_POST['zona']) && !empty($_POST['zona'])) ? 'active' : ''?>"><a data-toggle="pill" href="#divzona">Zona</a></li>
  <li role="presentation" class="<?=(isset($_POST['distrito']) && !empty($_POST['distrito'])) ? 'active' : ''?>"><a data-toggle="pill" href="#divdistrito">Distrito</a></li>
  <li role="presentation" class="<?=(isset($_POST['provincia']) && !empty($_POST['provincia'])) ? 'active' : ''?>"><a data-toggle="pill" href="#divprovincia">Provincia</a></li>
  <li role="presentation" class="<?=(isset($_POST['canton']) && !empty($_POST['canton'])) ? 'active' : ''?>"><a data-toggle="pill" href="#divcanton">Cantón</a></li>
</ul>
</div>



<div class="tab-content col-md-6">



<?php $selected = ((isset($_POST['zona']) && !empty($_POST['zona'])) ? 'in active' : ''); ?>
<div id="divzona" class="form-group tab-pane fade <?=$selected?>">
<div class="row">
<div class="col-md-2">
<label for="zona">
Zona:
</label>
</div>
<div class="col-md-4">
<select class="form-control" id="zona" name="zona">
<option value="">- TODAS LAS ZONAS -</option>
<?php
$opciones = q("SELECT DISTINCT(ess_zona) AS nombre FROM esamyn.esa_establecimiento_salud WHERE ess_borrado IS NULL ORDER BY nombre");
?>
<?php foreach($opciones as $opcion): ?>
<?php $selected = ((isset($_POST['zona']) && $opcion['nombre'] == $_POST['zona']) ? 'selected="selected"' : ''); ?>
<option value="<?=$opcion['nombre']?>" <?=$selected?>><?=$opcion['nombre']?></option>
<?php endforeach; ?>
<option value="">- TODAS LAS ZONAS -</option>
</select> 
</div>
</div>
</div>



<?php $selected = ((isset($_POST['distrito']) && !empty($_POST['distrito'])) ? 'in active' : ''); ?>
<div id="divdistrito" class="form-group tab-pane fade <?=$selected?>">
<div class="row">
<div class="col-md-2">
<label for="distrito">
Distrito:
</label>
</div>
<div class="col-md-4">
<select class="form-control" id="distrito" name="distrito">
<option value="">- TODOS LOS DISTRITOS -</option>
<?php
$opciones = q("SELECT DISTINCT(ess_distrito) AS nombre FROM esamyn.esa_establecimiento_salud WHERE ess_borrado IS NULL ORDER BY nombre");
?>
<?php foreach($opciones as $opcion): ?>
<?php $selected = ((isset($_POST['distrito']) && $opcion['nombre'] == $_POST['distrito']) ? 'selected' : ''); ?>
<option value="<?=$opcion['nombre']?>" <?=$selected?>><?=$opcion['nombre']?></option>
<?php endforeach; ?>
<option value="">- TODOS LOS DISTRITOS -</option>
</select> 
</div>
</div>
</div>



<?php $selected = ((isset($_POST['provincia']) && !empty($_POST['provincia'])) ? 'in active' : ''); ?>
<div id="divprovincia" class="form-group tab-pane fade <?=$selected?>">
<div class="row">
<div class="col-md-2">
<label for="provincia">
Provincia:
</label>
</div>
<div class="col-md-4">
<select class="form-control" id="provincia" name="provincia">
<option value="">- TODAS LAS PROVINCIAS -</option>
<?php
$opciones = q("SELECT pro_nombre AS nombre FROM esamyn.esa_provincia ORDER BY pro_nombre");
?>
<?php foreach($opciones as $opcion): ?>
<?php $selected = ((isset($_POST['provincia']) && $opcion['nombre'] == $_POST['provincia']) ? 'selected' : ''); ?>
<option value="<?=$opcion['nombre']?>" <?=$selected?>><?=$opcion['nombre']?></option>
<?php endforeach; ?>
<option value="">- TODAS LAS PROVINCIAS -</option>
</select> 
</div>
</div>
</div>


<?php $selected = ((isset($_POST['canton']) && !empty($_POST['canton'])) ? 'in active' : ''); ?>
<div id="divcanton" class="form-group tab-pane fade <?=$selected?>">
<div class="row">
<div class="col-md-2">
<label for="canton">
Cantón:
</label>
</div>
<div class="col-md-4">
<select class="form-control" id="canton" name="canton">
<option value="">- TODOS LOS CANTONES -</option>
<?php
$opciones = q("SELECT can_nombre AS nombre FROM esamyn.esa_canton ORDER BY can_nombre");
?>
<?php foreach($opciones as $opcion): ?>
<?php $selected = ((isset($_POST['canton']) && $opcion['nombre'] == $_POST['canton']) ? 'selected' : ''); ?>
<option value="<?=$opcion['nombre']?>" <?=$selected?>><?=$opcion['nombre']?></option>
<?php endforeach; ?>
<option value="">- TODOS LOS CANTONES -</option>
</select> 
</div>
</div>
</div>


</div> <!-- col-md-6 -->


<div class="col-md-4">



<div id="divnivel" class="form-group">
<div class="row">
<div class="col-md-3">
<label for="nivel">
Nivel:
</label>
</div>
<div class="col-md-8">
<select class="form-control" id="nivel" name="nivel">
<option value="">- TODOS LOS NIVELES --</option>
<?php
$opciones = q("SELECT DISTINCT(ess_nivel) AS nombre FROM esamyn.esa_establecimiento_salud WHERE ess_borrado IS NULL ORDER BY nombre");
?>
<?php foreach($opciones as $opcion): ?>
<?php $selected = ((isset($_POST['nivel']) && $opcion['nombre'] == $_POST['nivel']) ? 'selected' : ''); ?>
<option value="<?=$opcion['nombre']?>" <?=$selected?>><?=$opcion['nombre']?></option>
<?php endforeach; ?>
<option value="">- TODOS LOS NIVELES -</option>
</select> 
</div>
</div>
</div>



<div id="divtipologia" class="form-group">
<div class="row">
<div class="col-md-3">
<label for="tipologia">
Tipología:
</label>
</div>
<div class="col-md-8">
<select class="form-control" id="tipologia" name="tipologia">
<option value="">- TODAS LAS TIPOLOGIAS --</option>
<?php
//$opciones = q("SELECT REPLACE(nombre, '\"', '') AS nombre FROM (SELECT DISTINCT(ess_tipologia) AS nombre FROM esamyn.esa_establecimiento_salud WHERE ess_borrado IS NULL ORDER BY nombre) AS t");
$opciones = q("SELECT DISTINCT(ess_tipologia) AS nombre FROM esamyn.esa_establecimiento_salud WHERE ess_borrado IS NULL ORDER BY nombre");
?>
<?php foreach($opciones as $opcion): ?>
<?php $selected = ((isset($_POST['tipologia']) && $opcion['nombre'] == $_POST['tipologia']) ? 'selected' : ''); ?>
<option value='<?=$opcion['nombre']?>' <?=$selected?>><?=$opcion['nombre']?></option>
<?php endforeach; ?>
<option value="">- TODAS LAS TIPOLOGIAS -</option>
</select> 
</div>
</div>
</div>



<div id="divtipo" class="form-group">
<div class="row">
<div class="col-md-3">
<label for="tipo">
Tipo de evaluación:
</label>
</div>
<div class="col-md-8">
<select class="form-control" id="tipo" name="tipo">
<option value="">- TODOS LOS TIPOS DE EVALUACION --</option>
<?php
$opciones = q("SELECT tev_nombre AS nombre FROM esamyn.esa_tipo_evaluacion");
?>
<?php foreach($opciones as $opcion): ?>
<?php $selected = ((isset($_POST['tipo']) && $opcion['nombre'] == $_POST['tipo']) ? 'selected' : ''); ?>
<option value="<?=$opcion['nombre']?>" <?=$selected?>><?=$opcion['nombre']?></option>
<?php endforeach; ?>
<option value="">- TODOS LOS TIPOS DE EVALUACION -</option>
</select> 
</div>
</div>
</div>


<button onclick="$('form').submit()" class="btn btn-primary"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></span> Generar</button>


</div> <!-- col-md-4 -->

</div><!-- row -->


</form>

</div>
</div>
</div>
<script>
$(document).ready(function(){
    $('#zona').bind('change', function(){p_reset_geografias('zona')});
    $('#distrito').bind('change', function(){p_reset_geografias('distrito')});
    $('#provincia').bind('change', function(){p_reset_geografias('provincia')});
    $('#canton').bind('change', function(){p_reset_geografias('canton')});
});
function p_reset_geografias(current_id) {
    console.log('p_reset_geografias', current_id);
    ['zona', 'distrito', 'provincia', 'canton'].forEach(function(id){
        if (id !== current_id) {
            $('#' + id).val('');
        }
    });
}

</script>

<?php

if (!$evaluaciones) {
    echo "<div class='alert alert-danger'>No hay resultados</div>";
    return;
}

?>

<a href="#" download><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Descargar XML </a>
|
<a href="#" onclick="p_xlsx();return false;"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Exportar datos</a>
<table id="tabla" class="table table-striped table-condensed table-hover">
<tr>
<th>
&nbsp;
</th>
<th>
Establecimiento de Salud
</th>
<th>
Zona
</th>
<th>
Distrito
</th>
<th>
Provincia
</th>
<th>
Cantón
</th>
<th>
Nivel
</th>
<th>
Tipología
</th>
<th>
Tipo de evaluación
</th>
<th>
Descripción de la evaluación
</th>
<th>
Fecha de creación de la evaluación
</th>
<th>
Avance
</th>
<th>
Cumplimiento
</th>
<th>
Cumple mínimos
</th>
<th>
Cumple obligatorios
</th>
</tr>
<?php foreach($evaluaciones as $i => $eva): ?>
<tr>
<th>
<?=$i+1?>
</th>
<td>
<?=$eva['ess_nombre']?>
</td>
<td>
<?=$eva['ess_zona']?>
</td>
<td>
<?=$eva['ess_distrito']?>
</td>
<td>
<?=$eva['pro_nombre']?>
</td>
<td>
<?=$eva['can_nombre']?>
</td>
<td>
<?=$eva['ess_nivel']?>
</td>
<td>
<?=$eva['ess_tipologia']?>
</td>
<td>
<?=$eva['tev_nombre']?>
</td>
<td>
<?=$eva['eva_descripcion']?>
</td>
<td>
<?=p_formatear_fecha($eva['eva_creado'])?>
</td>
<td>
<?=empty($eva['eva_porcentaje_avance'])?0:$eva['eva_porcentaje_avance']?>%
</td>
<td>
<?=empty($eva['eva_calificacion'])?0:$eva['eva_calificacion']?>%
</td>
<td class="alert alert-<?=$eva['eva_cumplido_minimos']==1?'success':'danger'?>">
<?=$eva['eva_cumplido_minimos']==1?'SI':'no'?>
</td>
<td class="alert alert-<?=$eva['eva_cumplido_obligatorios']==1?'success':'danger'?>">
<?=$eva['eva_cumplido_obligatorios']==1?'SI':'no'?>
</td>
</tr>
<?php endforeach; ?>

</table>


<script src="/js/Blob.min.js"></script>
<script src="/js/xlsx.full.min.js"></script>
<script src="/js/FileSaver.min.js"></script>
<script src="/js/tableexport.min.js"></script>

<script src="/js/jspdf.min.js"></script>
<script src="/js/html2canvas.min.js"></script>
<script src="/js/html2pdf.js"></script>

<script>
function p_imprimir(){
    var element = document.getElementById('formulario_evaluacion');
    html2pdf(element, {
        margin:       1,
        filename:     'reporte<?=$unicodigo?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { dpi: 192, letterRendering: true },
        jsPDF:        { unit: 'cm', format: 'A4', orientation: 'portrait' }
    });
}

function p_xlsx(){
    $('#tabla').tableExport();
}
</script>
