<?php

$filename = '';
$file_url = '';

$contenido = '';

ob_start();
$contenido .= '1';
echo 2;
if (!isset($_GET['frm_id'])) {
    $file_url = 'https://www.jybaro.com/esamyn/uploads/7aba21cc946ed90424cddb291e6262d83182014a-20170721-105949.mm';
    //$filename = basename($file_url);
    $filename = 'form2.mm'; 
    readfile($file_url); 
} else {
    $frm_id = (int)$_GET['frm_id'];
    echo $frm_id;
    $filename = 'formxxx'.$frm_id.'.sql';
    $result = pg_query($conn, "SELECT * FROM esamyn.esa_pregunta WHERE prg_formulario=$frm_id");
    while ($pregunta = pg_fetch_array($result)) {
        $campos = '';
        $campos .= $pregunta['prg_id'];
        $campos .= ',';
        $campos .= $pregunta['prg_padre'];
        $campos .= ',';
        $campos .= $pregunta['prg_tipo_pregunta'];
        $campos .= ',';
        $campos .= $pregunta['prg_formulario'];
        $campos .= ',';
        $campos .= "'". $pregunta['prg_texto'] . "'";
        $campos .= ',';
        $campos .= $pregunta['prg_orden'];


        $contenido.= "INSERT INTO esamyn.esa_pregunta(prg_id, prg_padre, prg_tipo_pregunta, prg_formulario, prg_texto, prg_orden) VALUES ($campos);\n";
    }
/*
*/
}
$contenido .= ob_get_contents();
ob_end_clean();

//header('Content-Type: application/octet-stream');
//header("Content-Transfer-Encoding: Binary"); 
//header("Content-disposition: attachment; filename=\"" . $filename . "\""); 
echo $contenido;
