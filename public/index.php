<?php
session_start();
require_once('../private/config.php');
require_once('../private/utils.php');
require_once('../private/bdd.php');

$path = $_GET['path'];

$path = explode('/', $path);

$component = (empty($path[0])) ? 'main' : array_shift($path);
if ($component[0] == '_' || isset($_SESSION['cedula'])){
} else {
    $component = 'login';
}
$args = $path;
$template_temporal_no_persistente = false;
$template = 'default';
//$template = 'default';

function p_preparar_buffer($buffer) {
    return $buffer;
}

ob_start('p_preparar_buffer');
if (file_exists($component . '.php')) {
    require_once($component . '.php');
} else {
    echo "ERROR: Componente <strong>$component</strong> no instanciado.";
}
$content = ob_get_contents();
ob_end_clean();

if($component[0] == '_') {
    $template = 'ws_rest';
    $template_temporal_no_persistente = true;
} else if (isset($_SESSION['template']) && !empty($_SESSION['template'])) {
    $template = $_SESSION['template']; 
}

//if($component[0] == '_' && $template == 'default') {
//    require_once("../private/templates/ws_rest.template.php");
//} else {
    if ($template != 'default' && $template != 'login' && file_exists("../private/templates/$template.template.php")) {

    } else if (file_exists("../private/templates/$component.template.php")) {
        $template = $component;
    } else if (!file_exists("../private/templates/$template.template.php")) {
        $template = 'default';
    }


if (!$template_temporal_no_persistente) {
    $_SESSION['template'] = $template;
}

//echo '<pre>';
//var_dump($_SESSION);
//echo '</pre>';
    require_once("../private/templates/$template.template.php");
//}
