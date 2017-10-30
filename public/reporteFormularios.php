<?php

function p_render($nodo, $texto = ''){
    global $titulos_columna;
    $tipo = $nodo['tipo'];
    if ($tipo !== 'null') {

        if (!empty($texto) && !in_array($tipo, array('grupo', 'subgrupo', 'tabla', 'check', 'radio'))) {
            $titulo_columna = '<li>'. $texto . ' >>> ' . $nodo['prg_texto'] . '</li>';
            $titulos_columna[$nodo['prg_id']] = array('titulo'=>$titulo_columna, 'prg'=>$nodo);
            echo $titulo_columna;
        }  
        
        if (!empty($nodo['hijos'])){
            foreach($nodo['hijos'] as $hijo) {
                $mitexto = $texto . (empty($texto) ? '' : ' >> ');
                p_render($hijo, $mitexto . $nodo['prg_texto']);
            }
        }

    }
}
?>
<h2>Reporte de Formularios</h2>

<?php
$ess_id = $_SESSION['ess']['ess_id'];

$formularios = q("SELECT * FROM esamyn.esa_formulario ORDER BY frm_clave");

foreach($formularios as $formulario) {
    echo "<h1>$formulario[frm_clave]. $formulario[frm_titulo]</h1>";
    $frm_id = $formulario['frm_id'];

    $preguntas = q("SELECT *,(SELECT tpp_clave FROM esamyn.esa_tipo_pregunta WHERE tpp_id=prg_tipo_pregunta) AS tipo FROM esamyn.esa_pregunta WHERE prg_formulario=$frm_id ORDER BY prg_orden");

    $tree = array();
    foreach($preguntas as $pregunta) {
        $id = $pregunta['prg_id'];
        $tree[$id] = $pregunta;
        $tree[$id]['hijos'] = array();
        $tree[$id]['padre'] = null;
    }
    foreach($tree as $pregunta){
        $id = $pregunta['prg_id'];
        $padre = $pregunta['prg_padre'];
        $tree[$padre]['hijos'][$id] = & $tree[$id];
        $tree[$id]['padre'] = $tree[$padre];
    }

    $titulos_columna = array();

    echo '<ol>';
    p_render($tree['']);
    echo '</ol>';


    $respuestas = q("SELECT *,(SELECT usu_nombres || ' ' || usu_apellidos FROM esamyn.esa_usuario WHERE usu_id=enc_usuario) AS usuario FROM esamyn.esa_respuesta, esamyn.esa_encuesta WHERE res_encuesta = enc_id AND enc_formulario = $frm_id AND enc_establecimiento_salud=$ess_id ORDER BY enc_id");

    $encuestas = array();
    if (is_array($respuestas)) {
        foreach($respuestas as $respuesta){

            $enc_id = $respuesta['enc_id'];
            $prg_id = $respuesta['res_pregunta'];
            if (!isset($encuestas[$enc_id])) {
                $encuestas[$enc_id] = array();
            }

            $encuestas[$enc_id][$prg_id] = $respuesta['res_valor_texto'] . $respuesta['res_valor_numero'] .$respuesta['res_valor_fecha'];
            $encuestas[$enc_id]['enc_creado'] = $respuesta['enc_creado'];
            $encuestas[$enc_id]['enc_creado_por'] = $respuesta['usuario'];
        }
    }
?> 
<a href="#" download><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Descargar XML </a>
|
<a href="#" onclick="p_xlsx(<?=$frm_id?>);return false;"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Exportar datos</a>
<?php   
    echo '<table border=1 id="tabla_'.$frm_id.'">';

    $primero = true;
    echo '<tr>';
            echo '<th>';
    echo 'Fecha de ingreso';
            echo '</th>';
            echo '<th>';
    echo 'Usuario';
            echo '</th>';
    $count = 0;
    foreach($titulos_columna as $titulo_columna){
            echo '<th>';
            echo ++$count; 
            echo '</th>';
    }
    echo '</tr>';
    
    foreach($encuestas as $enc_id => $encuesta) {
        echo '<tr>';
        echo '<td>';
        echo $encuesta['enc_creado'];
        echo '</td>';
        echo '<td>';
        echo $encuesta['enc_creado_por'];
        echo '</td>';
        foreach($titulos_columna as $titulo_columna){
            $prg_id = $titulo_columna['prg']['prg_id'];
            echo '<td>';
            echo (isset($encuesta[$prg_id]) ? $encuesta[$prg_id] : '&nbsp;'); 
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}
?>
<script src="/js/Blob.min.js"></script>
<script src="/js/xlsx.full.min.js"></script>
<script src="/js/FileSaver.min.js"></script>
<script src="/js/tableexport.min.js"></script>

<script src="/js/jspdf.min.js"></script>
<script src="/js/html2canvas.min.js"></script>
<script src="/js/html2pdf.js"></script>

<script>

function p_imprimir(frm_id){
    var element = document.getElementById('formulario_evaluacion');
    html2pdf(element, {
        margin:       1,
        filename:     'formulario_evaluacion_<?=$unicodigo?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { dpi: 192, letterRendering: true },
        jsPDF:        { unit: 'cm', format: 'A4', orientation: 'portrait' }
    });
}

function p_xlsx(frm_id){
    $('#tabla_'+frm_id).tableExport();
}
</script>
