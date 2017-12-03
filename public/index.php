<?php
session_start();
require_once('../private/config.php');
require_once('../private/utils.php');
require_once('../private/bdd.php');

$path = $_GET['path'];

$path = explode('/', $path);

$esamyn_modulo = (empty($path[0])) ? 'main' : array_shift($path);
if ($esamyn_modulo[0] == '_' || isset($_SESSION['cedula'])){
} else {
    $esamyn_modulo = 'login';
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
if (file_exists($esamyn_modulo . '.php')) {
    //verifica seguridades
    $con_permiso = false;

    if ($esamyn_modulo === 'login') {
        $con_permiso = true;
    } else {
        $rol_id = (isset($_SESSION['rol']) ? $_SESSION['rol'] : 0);
        $rol_version = q("SELECT rol_version FROM esamyn.esa_rol WHERE rol_id=$rol_id");

        if ($rol_version) {
            $rol_version = $rol_version[0]['rol_version'];

            $seguridades = array();
            if (isset($_SESSION['rol_version']) && $rol_version == $_SESSION['rol_version']) {
                $seguridades = $_SESSION['seguridades'];
            } else {
                $seguridades = q("SELECT * FROM esamyn.esa_seguridad, esamyn.esa_modulo WHERE seg_modulo = mod_id AND seg_rol=$rol_id");
                $_SESSION['seguridades'] = $seguridades;
                $_SESSION['rol_version'] = $rol_version;
            }
            if (!empty($seguridades)) {
                foreach($seguridades as $seguridad){
                    if ($seguridad['mod_texto'] == $esamyn_modulo) {
                        $con_permiso = true;
                    } 
                }
            }
        }

        if (!$con_permiso) {
            $count_esamyn_modulos = q("SELECT COUNT(*) FROM esamyn.esa_modulo WHERE mod_texto='$esamyn_modulo'")[0]['count'];
            $con_permiso = ($count_esamyn_modulos == 0);
        }



/*
        $count_permisos = q("SELECT COUNT(*) FROM esamyn.esa_seguridad, esamyn.esa_modulo, esamyn.esa_rol WHERE seg_modulo=mod_id AND seg_rol=rol_id AND mod_texto='$esamyn_modulo' AND rol_id=$rol_id")[0]['count'];
        //echo $count_permisos;
        //echo ("SELECT COUNT(*) FROM esamyn.esa_seguridad, esamyn.esa_modulo, esamyn.esa_rol WHERE seg_modulo=mod_id AND seg_rol=rol_id AND mod_texto='$esamyn_modulo' AND rol_id=$rol_id");
        if ($count_permisos  == 0){
            $count_esamyn_modulos = q("SELECT COUNT(*) FROM esamyn.esa_modulo WHERE mod_texto='$esamyn_modulo'")[0]['count'];
            $con_permiso = ($count_esamyn_modulos == 0);
        } else {
            $con_permiso = true;
        }
 */
    }

    //if ($con_permiso || true) {
    if ($con_permiso) {
        //carga esamyn_modulo
        require_once($esamyn_modulo . '.php');
        l("Acceso a módulo -$esamyn_modulo-, parámetros: " . implode(',', $args));
    } else {
        echo "ERROR: No tiene permisos para acceder al módulo <strong>$esamyn_modulo</strong>.";
        l("ERROR: intento de acceso no autorizado al módulo $esamyn_modulo");
    }
} else {
    echo "ERROR: Módulo <strong>$esamyn_modulo</strong> no instanciado.";
    l("ERROR:  módulo $esamyn_modulo no instanciado.");
}
$content = ob_get_contents();
ob_end_clean();

if($esamyn_modulo[0] == '_') {
    $template = 'ws_rest';
    $template_temporal_no_persistente = true;
} else if (isset($_SESSION['template']) && !empty($_SESSION['template'])) {
    $template = $_SESSION['template']; 
}

//if($esamyn_modulo[0] == '_' && $template == 'default') {
//    require_once("../private/templates/ws_rest.template.php");
//} else {
    if ($template != 'default' && $template != 'login' && file_exists("../private/templates/$template.template.php")) {

    } else if (file_exists("../private/templates/$esamyn_modulo.template.php")) {
        $template = $esamyn_modulo;
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
