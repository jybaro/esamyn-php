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

//inicia petición
ob_start('p_preparar_buffer');
if (file_exists($component . '.php')) {
    //verifica seguridades
    $con_permiso = false;
    if ($component === 'login') {
        $con_permiso = true;
    } else {
        $rol_id = (isset($_SESSION['rol']) ? $_SESSION['rol'] : 0);
        $count_permisos = q("SELECT COUNT(*) FROM esamyn.esa_seguridad, esamyn.esa_modulo, esamyn.esa_rol WHERE seg_modulo=mod_id AND seg_rol=rol_id AND mod_texto='$component' AND rol_id=$rol_id")[0]['count'];
        //echo $count_permisos;
        //echo ("SELECT COUNT(*) FROM esamyn.esa_seguridad, esamyn.esa_modulo, esamyn.esa_rol WHERE seg_modulo=mod_id AND seg_rol=rol_id AND mod_texto='$component' AND rol_id=$rol_id");
        if ($count_permisos  == 0){
            $count_modulos = q("SELECT COUNT(*) FROM esamyn.esa_modulo WHERE mod_texto='$component'")[0]['count'];

            $con_permiso = ($count_modulos == 0);
        } else {
            $con_permiso = true;
        }
    }

    //if ($con_permiso || true) {
    if ($con_permiso) {
        //carga componente
        require_once($component . '.php');
        l("Acceso a componente -$component-, parámetros: " . implode(',', $args));
    } else {
        echo "ERROR: No tiene permisos para acceder al módulo <strong>$component</strong>.";
        l("ERROR: intento de acceso no autorizado al módulo $component");
    }
} else {
    echo "ERROR: Módulo <strong>$component</strong> no instanciado.";
    l("ERROR:  módulo $component no instanciado.");
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
