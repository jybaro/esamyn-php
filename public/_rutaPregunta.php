<?php
//echo '<pre>';
//var_dump( q("SELECT * from esamyn.esa_parametro limit 10"));
if (isset($args[0]) && !empty($args[0])){
    $par_id = (int)$args[0];

    $sql = "
        SELECT 
            prg_id,
            prg_padre,
            prg_texto,
            (SELECT frm_clave ||'. '|| frm_titulo FROM esamyn.esa_formulario WHERE frm_id = prg_formulario) AS formulario
        FROM 
        esamyn.esa_pregunta
        ";
    $result = q($sql);
    $preguntas = array();

    //var_dump($result);
    foreach($result as $r){
        $id = $r['prg_id'];
        $preguntas[$id] = $r;
        $preguntas[$id]['hijos'] = array();
        $preguntas[$id]['padre'] = null;
    }


    foreach($result as $r){
        $id = $r['prg_id'];
        $padre = $r['prg_padre'];

        $preguntas[$padre]['hijos'][$id] = & $preguntas[$id];
        if (!empty($padre)) {
            $preguntas[$id]['padre'] = & $preguntas[$padre];
        }
    }

    //var_dump($preguntas);

    echo '[';

    //parametro padre:
    $parametro = q("
        SELECT 
        * 
        FROM 
        esamyn.esa_parametro, 
        esamyn.esa_parametro_pregunta 
        WHERE 
        par_id=ppr_parametro 
        AND 
        par_id=$par_id
        ");

    if ($parametro){
        $glue = '';
        echo '[';
        foreach($parametro as $par){
            $ppr_pregunta = $par['ppr_pregunta'];
            echo "$glue {";
            echo '"prg_id":"'.$ppr_pregunta.'","ruta":"';
            $pregunta = $preguntas[$ppr_pregunta];
            $glue = '';
            $etiqueta = '';
            while (!empty($pregunta['padre'])) {
                $etiqueta = '<ul>'.$pregunta['prg_texto'] . $glue . $etiqueta.'<ul>';
                $pregunta = $pregunta['padre'];
                //$glue = '; ';
            }
            $etiqueta = str_replace('"', "'", str_replace("\n", '', $etiqueta));
            $etiqueta = 'Form '.$preguntas[$ppr_pregunta]['formulario'] . ': ' . $etiqueta;
            echo $etiqueta;
            echo '"';



            echo "}";
            $glue = ',';

        }
        echo ']';
    }
    //fin parametro padre


    //parametro hijo:
    $parametro = q("
        SELECT 
        * 
        FROM 
        esamyn.esa_parametro, 
        esamyn.esa_parametro_pregunta 
        WHERE 
        par_id=ppr_parametro 
        AND 
        par_padre=$par_id
        ");

    if ($parametro){
        $glue = '';

        echo ',[';
        foreach($parametro as $par){
            $ppr_pregunta = $par['ppr_pregunta'];
            echo "$glue {";
            echo '"prg_id":"'.$ppr_pregunta.'","ruta":"';
            $pregunta = $preguntas[$ppr_pregunta];
            $glue = '';
            $etiqueta = '';
            while (!empty($pregunta['padre'])) {
                $etiqueta = '<ul>'.$pregunta['prg_texto'] . $glue . $etiqueta.'<ul>';
                $pregunta = $pregunta['padre'];
                //$glue = '; ';
            }
            $etiqueta = str_replace('"', "'", str_replace("\n", '', $etiqueta));
            $etiqueta = 'Form '.$preguntas[$ppr_pregunta]['formulario'] . ': ' . $etiqueta;
            echo $etiqueta;
            echo '"';
            echo "}";
            $glue = ',';
        }
        echo ']';
    }
    //fin parametro hijo 
    

    echo ']';
}

