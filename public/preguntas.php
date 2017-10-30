<?php

function p_render($nodo) {
    global $zebra;
    $zebra = !$zebra;

    if ($nodo['tipo'] !== 'null') {
        echo '<ul style="border:solid 3px #DDD;border-radius:10px;padding:5px;margin:5px;background-color:'.($zebra ? '#EEE': '#FFF').';">';
        if (!empty($nodo['prg_texto'])) {
            echo "<form id='formulario_$nodo[prg_id]' class='form-inline' onsubmit='return false;'>";
            echo '<div class="row"><div class="col-md-6">';
            //echo '<div class="form-group">';
            echo "<input class='form-control' type='text' value='$nodo[prg_texto]' id='prg_$nodo[prg_id]'>";
            //echo '</div>';
            echo "<button class='btn btn-default' onclick='p_guardar($nodo[prg_id])'>Guardar</button>";
            echo '</div></div>';
            echo '</form>';
        }
        if (!empty($nodo['hijos'])){
            foreach($nodo['hijos'] as $hijo){
                p_render($hijo);
            }
        }
        echo '</ul>';
    }
}

$formularios = q("SELECT *  FROM esamyn.esa_formulario ORDER BY frm_clave");
?>

<h1>Contenido de Formularios</h1>
<li>
<?php foreach($formularios as $i=>$frm): ?>
<ul>
<!--div><a href="#" onclick="p_abrir('<?=$frm['frm_id']?>');return false;"><?php echo $frm['frm_clave'] . '. ' . $frm['frm_nombre']; ?></a></div-->
<h2><?php echo $frm['frm_clave'] . '. ' . $frm['frm_nombre']; ?></h2>
<div>
<?php
$zebra = false;

$preguntas = q("SELECT * , (SELECT tpp_clave FROM esamyn.esa_tipo_pregunta WHERE prg_tipo_pregunta = tpp_id) AS tipo FROM esamyn.esa_pregunta WHERE prg_formulario = $frm[frm_id]   ORDER BY prg_orden");

$tree = array();

foreach($preguntas as $p){
    $id = $p['prg_id'];
    $tree[$id] = $p;
    $tree[$id]['hijos'] = array();
    $tree[$id]['padre'] = null;
}

foreach($tree as $p){
    $id = $p['prg_id'];
    $padre = $p['prg_padre'];

    $tree[$padre]['hijos'][$id] = & $tree[$id];
    $tree[$id]['padre'] = & $tree[$padre];
}

p_render($tree['']);
?>
</div>
</ul>
<?php endforeach; ?>
<!--ul>
<div><a href="#" onclick="p_abrir('eva');return false;">Formulario de Evaluación</a></div>
</ul-->
</li>
<script>
function p_guardar(prg_id){
    if ($('#prg_' + prg_id).val() != '') {
        if (confirm('Seguro desea cambiar el texto de esta pregunta?')) {
        var dataset_json = [{id:prg_id, texto:$('#prg_' + prg_id).val()}];
        $.ajax({
        url: '/_guardar/pregunta',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data:JSON.stringify(dataset_json)

        }).done(function(data){
            console.log('Pregunta guardada con éxito', data);
            data = eval(data);

        }).fail(function(xfr, err){
            console.error('Error al guardar', err);
        });
        } else {
            $('#formulario_' + prg_id).trigger('reset');
        }
    } else {
        alert('El texto no puede ser vacío');
        $('#formulario_' + prg_id).trigger('reset');
    }
}
</script>
