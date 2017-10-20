<?php

//var_dump($conn);
//$frm_id = (int)$_GET['id'];
$frm_id = (isset($args[0])) ? (int)$args[0] : -1;;
$result = pg_query($conn, 'select * from esamyn.esa_formulario where frm_id='.$frm_id);

$formulario = pg_fetch_array($result, 0);
$respuestas = array();
$encuesta = array();
$solo_lectura = false;


if (isset($args[1])) {
    $enc_id = (int)$args[1];
    $result = q("SELECT * FROM esamyn.esa_encuesta WHERE enc_id=$enc_id");

    if ($result){
        $encuesta = $result[0];
        $solo_lectura = ($encuesta['enc_finalizada'] == 1 );
    }

    if ((int)$encuesta['enc_formulario'] !== $frm_id) {
        //echo "ERROR FATAL: El formulario de la encuesta ({$encuesta[enc_formulario]}) no corresponde al formulario referenciado ($frm_id).";
        //die();
        header('Location:/main');
    }

    $result = q("SELECT * FROM esamyn.esa_respuesta WHERE res_encuesta=$enc_id");
    if ($result) {
        foreach($result as $r){
            $respuestas[$r['res_pregunta']] = $r;
        }
    }
}


$result =  pg_query($conn, "SELECT *, (SELECT tpp_clave FROM esamyn.esa_tipo_pregunta WHERE tpp_id = prg_tipo_pregunta) AS tipo FROM esamyn.esa_pregunta WHERE prg_formulario=$frm_id ORDER BY prg_orden ASC");
$preguntas = pg_fetch_all($result);

//var_dump($preguntas);

$tree = array();

$tipos_pregunta = array();
$tipos = q("SELECT * FROM esamyn.esa_tipo_pregunta");
foreach($tipos as $tipo){
    $tipos_pregunta[$tipo['tpp_id']] = $tipo['tpp_clave'];
    $tipos_pregunta[$tipo['tpp_clave']] = $tipo['tpp_id'];
}
//var_dump($tipos_pregunta);

//inicializa el arbol con ramas vacias:
foreach($preguntas as $prg){
    $id = $prg['prg_id'];
    $padre = $prg['prg_padre'];


    $tree[$id] = $prg;
    $tree[$id]['hijos'] = array();
}

//var_dump($tree);
//llena las ramas con los hijos:
foreach($tree as $id => $prg){
    $id = $prg['prg_id'];
    $padre = $prg['prg_padre'];
    $tree[$padre]['hijos'][$id] = & $tree[$id];
    $tree[$id]['padre'] = &  $tree[$padre];
}

//echo '<pre>';
//var_dump($tree['']);
//echo '</pre>';


function p_render_tree($nodo, $extra = '') {
    global $respuestas;
    global $tipos_pregunta;
    global $solo_lectura;

    $hay_valores = false;

    $texto = (isset($nodo['prg_texto'])) ? trim($nodo['prg_texto']) : '';
    $texto = str_replace("\n", "<br>", $texto);
    //$texto = "<pre>$texto</pre>";
    
    $validacion = (isset($nodo['prg_validacion'])) ? trim($nodo['prg_validacion']) : '';
    $validacion = ($solo_lectura ? 'disabled' : $validacion);

    $ayuda = (isset($nodo['prg_ayuda'])) ? trim($nodo['prg_ayuda']) : '';
    $prefijo = (isset($nodo['prg_prefijo'])) ? trim($nodo['prg_prefijo']) : '';
    $subfijo = (isset($nodo['prg_subfijo'])) ? trim($nodo['prg_subfijo']) : '';
    $imagen = (isset($nodo['prg_imagen'])) ? trim($nodo['prg_imagen']) : '';

    $class = (
        isset($nodo['padre']) 
        && !empty($nodo['padre']) 
        && is_array($nodo['padre']) 
        && isset($nodo['padre']['prg_tipo_pregunta']) 
        && isset($tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']])
        && $tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']] == 'grupo'
        && empty($nodo['padre']['prg_padre'])
    ) ? 'pregunta' : '';

    $prg_id = $nodo['prg_id'];
    //$name = 'prg'.$prg_id;
    $name = $prg_id;
    $id = $name;
    //$value = isset($respuestas[$prg_id]) ? $respuestas[$prg_id] : '';
    $respuesta = isset($respuestas[$prg_id]) ? $respuestas[$prg_id] : null;

    /*
    $tipo = '';
    if (count($nodo['hijos']) == 0) {
        //nodos hoja
        if ($texto == 'Sí' || $texto == 'No sabe/no contesta' || $texto == 'No/ No sabe, no contesta') {
            $tipo = 'check';
        } else if($texto[0] == '('){
            $tipo = 'comentario';
        } else if($texto == 'No'){
            $tipo = 'check_no';
        } else if($texto == 'verificador'){
            $tipo = 'nulo';
        } else if($texto == 'texto'){
            $tipo = 'text';
        } else if($texto == 'numero'){
            $tipo = 'number';
        } else if($texto == 'fecha'){
            $tipo = 'date';
        } else {
            $tipo = 'labeled-text';
        }
    } else {
        //nodos ramas
        if($texto == 'verificador'){
            $tipo = 'nulo';
        } else if($texto == 'respuestas' || $texto == 'respuesta'){
            $tipo = 'sin-texto';
        } else if($texto == 'cabecera'){
            $tipo = 'inicio';
        } else if(empty($nodo['prg_padre'])) {
            $tipo = 'cabecera1';
        }
    }
     */
    
    $tipo = (isset($nodo['prg_tipo_pregunta']) && !empty($nodo['prg_tipo_pregunta']) && isset($tipos_pregunta[$nodo['prg_tipo_pregunta']])) ? $tipos_pregunta[$nodo['prg_tipo_pregunta']] : 'default';

    //echo "TIPO:$tipo";
    switch($tipo){
    case 'texto':
    case 'multitexto':
        $value = $respuesta['res_valor_texto'];
        $validacion = (!empty($validacion) && ctype_digit($validacion)) ? 'maxlength="'.$validacion.'"' : $validacion;
        if ($tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']] == 'grupo' ) {

            echo '<div class="'.$class.'" style="border:solid 2px #EEE;margin:5px;padding:5px;">';

              echo '<div style="background-color:#EEE;color:#333;font-size:20px;padding:5px;">';
                echo $texto . ':';
              echo '</div>';

              echo '<div class="row">';
                echo '<div class="col-md-6">';
                  echo (($prefijo != '' || $subfijo != '') ? '<div class="input-group">' : '');
                    echo '<input type="text" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>'; 
                  echo (($prefijo != '' || $subfijo != '') ? '</div>' : '');
                  echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
                echo '</div>';
              echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="row '.$class.'">';
              echo '<div class="col-md-6">';
                echo '<label for="'.$id.'">'.$texto . ': </label>';
                echo (($prefijo != '' || $subfijo != '') ? '<div class="input-group">' : '');
                  echo '<input type="text" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>'; 
                echo (($prefijo != '' || $subfijo != '') ? '</div>' : '');
                echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
              echo '</div>';
            echo '</div>';
        }
        break;
    case 'XXXmultitexto':
        $value = $respuesta['res_valor_texto'];
        echo '<div class="row '.$class.'"><div class="col-md-6">';
          echo '<label for="'.$id.'">'.$texto . ': </label>';
          echo '<textarea class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>' . $value . '</textarea>'; 
          echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '</div></div>';
        break;
    case 'fecha':
        $value = $respuesta['res_valor_fecha'];
        $value = explode(' ', $value)[0];
        $componente_fecha = <<<EOT
<div class="container">
    <div class="row">
        <div class='col-sm-6'>
            <div class="form-group">
                <div class='input-group date' id='datetimepicker2-$id'>
                    <input type='text' class="form-control" name="$name" id="$id" value="$value" $validacion.' />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(function () {
                $('#datetimepicker2-$id').datetimepicker({
                    locale: 'es',
                    format: 'YYYY-MM-DD'
                });
            });
        </script>
    </div>
</div>
EOT;

        echo '<div class="row '.$class.'"><div class="col-md-6">';
        echo '<label for="'.$id.'">'.$texto . ': </label>';
        echo $componente_fecha;
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '</div></div>';
        /*
        echo '<div class="row '.$class.'"><div class="col-md-6">';
        echo '<label for="'.$id.'">'.$texto . ': </label>';
        echo '<input type="date" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>'; 
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '</div></div>';
        */
        break;
    case 'hora':

        $value = $respuesta['res_valor_fecha'];
        $value = explode(' ', $value)[1];
        $componente_hora = <<<EOT
<div class="container">
    <div class="row">
        <div class='col-sm-6'>
            <div class="form-group">
                <div class='input-group date' id='datetimepicker2-$id'>
                    <input type='text' class="form-control" name="$name" id="$id" value="$value" $validacion.' />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-time"></span>
                    </span>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(function () {
                $('#datetimepicker2-$id').datetimepicker({
                    locale: 'es',
                    format: 'LT'
                });
            });
        </script>
    </div>
</div>
EOT;

        echo '<div class="row '.$class.'"><div class="col-md-6">';
        echo '<label for="'.$id.'">'.$texto . ': </label>';
        //echo '<input type="time" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" min="0" '.$validacion.'>'; 
        echo $componente_hora;
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '</div></div>';
        break;
    case 'numero':
        $value = $respuesta['res_valor_numero'];
        $validacion = (!empty($validacion) && ctype_digit($validacion)) ? 'maxlength="'.$validacion.'"' : $validacion;
        
        if (empty($extra) && count($nodo['hijos']) > 0) {
            $extra = 'onchange=p_evaluar_maximo("'.$id.'")';
        }
        if ($tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']] == 'grupo' ) {

            echo '<div class="'.$class.'" style="border:solid 2px #EEE;margin:5px;padding:5px;">';

            echo '<div style="background-color:#EEE;color:#333;font-size:20px;padding:5px;">';
            echo $texto . ':';
            echo '</div>';

            echo '<div class="row"><div class="col-md-6">';
            echo (($prefijo != '' || $subfijo != '') ? '<div class="input-group">' : '');
            echo ($prefijo != '' ? '<div class="input-group-addon">'.$prefijo.'</div>' : '');
            echo '<input type="number" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" min="0" '.$validacion.' '.$extra.'>'; 
            echo ($subfijo != '' ? '<div class="input-group-addon">'.$subfijo.'</div>' : '');
            echo (($prefijo != '' || $subfijo != '') ? '</div>' : '');
            echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
            echo '</div></div>';
        } else {
            echo '<div class="row '.$class.'"><div class="col-md-6">';
            echo '<label for="'.$id.'">'.$texto . ': </label>';
            echo (($prefijo != '' || $subfijo != '') ? '<div class="input-group">' : '');
            echo ($prefijo != '' ? '<div class="input-group-addon">'.$prefijo.'</div>' : '');
            echo '<input type="number" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" min="0" '.$validacion.' '.$extra.'>'; 
            echo ($subfijo != '' ? '<div class="input-group-addon">'.$subfijo.'</div>' : '');
            echo (($prefijo != '' || $subfijo != '') ? '</div>' : '');
            echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
            echo '</div>';
        }

        if (count($nodo['hijos']) > 0) {

            $display = ($value > 0) ? '' : 'none';
            echo '<div class="col-md-5" id="hijos_'.$id.'" style="display:'.$display.';">';
            foreach($nodo['hijos'] as $hijo){
                $hay_valores = $hay_valores || p_render_tree($hijo, $extra);
            }
            echo '</div>';
        }
        echo '</div>';
        break;
    case 'email':
        $value = $respuesta['res_valor_texto'];
        echo '<div class="row '.$class.'"><div class="col-md-6">';
          echo '<label for="'.$id.'">'.$texto . ': </label>';
          echo '<input type="email" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>'; 
          echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '</div></div>';
        break;
    case 'comentario':
        echo $texto;
        break;
    case 'null':
        break;
    case 'inicio':
        echo '<hr>';

        echo '<div>';
        foreach($nodo['hijos'] as $hijo){
            echo '<div class="form-group">';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            $hay_valores = $hay_valores || p_render_tree($hijo);
            echo '</div>';
        }
        echo '</div>';
        break;
    case 'check_no':
        break;
    case 'check':
        //echo 'izzz';
        echo '<div class="'.$class.'" style="border:solid 2px #EEE;margin:5px;padding:5px;">';

        echo '<div style="background-color:#EEE;color:#333;font-size:20px;padding:5px;">';
        echo $texto ;
        echo '</div>';
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '<div style="">';
        foreach($nodo['hijos'] as $hijo){
            $respuesta = isset($respuestas[$hijo['prg_id']]) ? $respuestas[$hijo['prg_id']] : null;
            $value = (is_array($respuesta) && $respuesta['res_valor_texto'] == $hijo['prg_texto']) ? 'checked' : '';
            echo '<div>';
            echo '<input type="checkbox" name="'.$hijo['prg_id']. '" id="'.$hijo['prg_id'].'" value="'.$hijo['prg_texto'].'" '.$value.' onchange="p_mostrar_ocultar_hijos(this)" '.$validacion.'>';
            echo ' <label for="'.$hijo['prg_id'].'">' . $hijo['prg_texto'] . '</label>';
            
            //echo '<pre>';
            //var_dump($hijo);
            //echo '</pre>';
            //echo "(".count($hijo['hijos']).")";
            //$display = 'none';
            $display = ($value == 'checked') ? '' : 'none';
            echo '<div id="hijos_'.$hijo['prg_id'].'" style="display:'.$display.';">';
            $hay_valores = $hay_valores || p_render_tree($hijo);
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        break;

    case 'radio':
        echo '<div class="'.$class.'" style="border:solid 2px #EEE;margin:5px;padding:5px;">';

        echo '<div style="background-color:#EEE;color:#333;font-size:20px;padding:5px;">';
        echo $texto ;
        echo '</div>';
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        $radiocheck = true;
        foreach($nodo['hijos'] as $hijo){
            $radiocheck = ($tipos_pregunta[$hijo['prg_tipo_pregunta']] == 'check' && empty($hijo['prg_texto'])) && $radiocheck;
        }
        if ($radiocheck) {
            echo '<div>';
            foreach($nodo['hijos'] as $hijo){
                echo '<div id="'.$hijo['prg_id'].'">';
                foreach($hijo['hijos'] as $nieto){
                    $respuesta = isset($respuestas[$nieto['prg_id']]) ? $respuestas[$nieto['prg_id']] : null;
                    $value = (is_array($respuesta) && $respuesta['res_valor_texto'] == $nieto['prg_texto']) ? 'checked' : '';
                    echo '<div>';
                    echo '<input type="checkbox" name="'.$nieto['prg_id']. '" id="'.$nieto['prg_id'].'" value="'.$nieto['prg_texto'].'" '.$value.' onchange="p_radiocheck(this, '.$hijo['prg_id'].');p_mostrar_ocultar_hijos(this)" '.$validacion.'>';
                    echo ' <label for="'.$nieto['prg_id'].'">' . $nieto['prg_texto'] . '</label>';

                    //echo '<pre>';
                    //var_dump($hijo);
                    //echo '</pre>';
                    //echo "(".count($hijo['hijos']).")";
                    //$display = 'none';
                    $display = ($value == 'checked') ? '' : 'none';
                    echo '<div id="hijos_'.$nieto['prg_id'].'" style="display:'.$display.';">';
                    $hay_valores = $hay_valores || p_render_tree($nieto);
                    echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }
            echo '</div>';
        } else {
            //radio normal:
            echo '<div style="padding-left:20px;">';
            $class_radio = count($nodo['hijos']) > 3 ? 'radio' : 'radio-inline';
            foreach($nodo['hijos'] as $hijo){
                $respuesta = isset($respuestas[$hijo['prg_id']]) ? $respuestas[$hijo['prg_id']] : null;
                $value = (is_array($respuesta) && $respuesta['res_valor_texto'] == $hijo['prg_texto']) ? 'checked' : '';
                echo '<label class="'.$class_radio.'">';
                echo '<input type="radio" name="'.$name. '" id="'.$hijo['prg_id'].'" value="'.$hijo['prg_texto'].'"  '.$value.' onchange="p_mostrar_ocultar_hijos(this)" '.$validacion.'>';
                echo $hijo['prg_texto'];
                echo '</label>';

                //echo '<pre>';
                //var_dump($hijo);
                //echo '</pre>';
                //echo "(".count($hijo['hijos']).")";
                //$display = 'none';
                //echo '<div id="hijos_'.$hijo['prg_id'].'" style="display:'.$display.';">';
                //p_render_tree($hijo);
                //echo '</div>';
                //echo '</div>';
            }
            foreach($nodo['hijos'] as $hijo){
                $respuesta = isset($respuestas[$hijo['prg_id']]) ? $respuestas[$hijo['prg_id']] : null;
                $value = (is_array($respuesta) && $respuesta['res_valor_texto'] == $hijo['prg_texto']) ? 'checked' : '';
                //echo '<div>';
                //echo '<input type="radio" name="'.$name. '" id="'.$hijo['prg_id'].'" value="'.$hijo['prg_texto'].'"  onchange="p_mostrar_ocultar_hijos(this)">';
                //echo ' <label for="'.$hijo['prg_id'].'">' . $hijo['prg_texto'] . '</label>';

                //echo '<pre>';
                //var_dump($hijo);
                //echo '</pre>';
                //echo "(".count($hijo['hijos']).")";
                //$display = 'none';
                $display = ($value == 'checked') ? '' : 'none';
                echo '<div id="hijos_'.$hijo['prg_id'].'" style="display:'.$display.';">';
                $hay_valores = $hay_valores || p_render_tree($hijo);
                echo '</div>';
                //echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
        break;

    case 'tabla':
        echo '<div class=" '.$class.'" style="">';
        echo '<div style="background-color:#EEE;color:#333;font-size:20px;padding:5px;">';
        echo $texto ;
        echo '</div>';
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '<table style="">';
        $cabecera = '';
        $cuerpo = '';

        foreach($nodo['hijos'] as $index => $hijo){
            if ($index == 0){
                echo '<tr>';
                echo '<th>&nbsp;</th>';
                foreach($hijo['hijos'] as $nieto){
                    echo '<th>';
                    echo $nieto['prg_texto'];
                    echo '</th>';
                }
                echo '</tr>';
            }
            echo '<tr>';
            echo '<th>';
            echo $hijo['prg_texto'];
            echo '</th>';
            foreach($hijo['hijos'] as $nieto){
                echo '<td>';

                $hay_valores = $hay_valores || p_render_tree($nieto);
                echo '</td>';
            }
            echo '</tr>';
        }
        echo $cabecera;
        echo $cuerpo;
        echo '</table>';
        echo '</div>';
        break;

    case 'check-old':


        echo $texto . ': ';
        //echo '<input type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$value.'">'; 
        break;
    case 'cabecera1':
        echo '<div class="container">';
        echo "<h2>$texto</h2>";
        foreach($nodo['hijos'] as $hijo){
            echo '<div class="form-group">';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            $hay_valores = $hay_valores || p_render_tree($hijo);
            echo '</div>';
        }
        echo '</div>';
        break;
    
    case 'sin-texto':
        echo '<ul>';
        foreach($nodo['hijos'] as $hijo){
            echo '<li>';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            $hay_valores = $hay_valores || p_render_tree($hijo);
            echo '</li>';
        }
        echo '</ul>';
        break;
    case 'grupo':
        echo '<div class=" '.$class.'" style="border:solid 2px #000;margin-right:20px;">';

        echo '<div style="background-color:#000;color:#FFF;font-size:40px;padding:5px;">';
        echo $texto ;
        echo '</div>';
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '<div style="">';
        foreach($nodo['hijos'] as $hijo){
            echo '<div>';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            $hay_valores = $hay_valores || p_render_tree($hijo);
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        break;

    case 'subgrupo':
        //if ($tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']] == 'subgrupo' 
        //    || $tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']] == '' 
        //    || $tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']] == 'radio' 
        //    || $tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']] == 'check' ) {

            echo '<div class=" '.$class.'" style="border:solid 2px #EEE;margin:5px;padding:5px;">';

            echo '<div style="background-color:#EEE;color:#333;font-size:20px;padding:5px;">';
        //} else {
        //var_dump($nodo['padre']);
        //    echo '<div style="border:solid 1px #666;">';

        //    echo '<div style="background-color:#CCC;color:#000;font-size:30px;padding:5px;">';
        // }
        echo $texto ;
        echo '</div>';
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        if ($imagen != '') {
            echo '<img src="/img/'.$imagen.'" style="max-width:100%;height:auto;">';
        }
        echo '<div style="padding:5px;">';
        foreach($nodo['hijos'] as $hijo){
            echo '<div>';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            $hay_valores = $hay_valores || p_render_tree($hijo);
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        break;

    default:
        //echo $texto ;
        echo '<ul class=" '.$class.'">';
        foreach($nodo['hijos'] as $hijo){
            //echo '<li>';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            $hay_valores = $hay_valores || p_render_tree($hijo);
            //echo '</li>';
        }
        echo '</ul>';
        break;

    }
    return $hay_valores;
}
/*
$txt="
    Estimada Señora:
    Como una iniciativa para mejorar la atención a las madres y recién nacidos, así como promover y proteger la lactancia materna, es importante conocer su experiencia en este establecimiento, por lo que le pedimos su autorización para participar a través de una encuesta. La información que Usted nos proporcione será de carácter estrictamente confidencial y anónimo.
   ¿Desea participar? 
   ";
$txt = str_replace("\n", '<br>', $txt);
q("UPDATE esamyn.esa_formulario SET frm_ayuda='$txt' WHERE frm_id=4");
 */
//q("UPDATE esamyn.esa_formulario SET frm_umbral_maximo=null WHERE frm_id=7");

?>

<style>
.formulario{
    margin-left:5%;
    margin-right:5%;
    background-color:#FFF;
    border-radius:15px;
    padding:50px;
}

.formulario>h1{
    text-align:center;
text-transform: uppercase;
    padding-left:20%;
    padding-right:20%;
    padding-bottom:50px;
background-image: url(/img/msp.png), url(/img/acess.png);
background-position: left top, right top;
background-repeat: no-repeat;
}
.formulario>p{
text-align:center;
padding:20px 50px;
font-size:16px;
font-weight:bold;
}
body{
    background-color:rgb(202, 232, 235)
}
</style>

<div class="formulario">
  <h1>
    <?php echo $formulario['frm_clave']. '. '. $formulario['frm_titulo']; ?>
  </h1>
  <p>
    <?php echo $formulario['frm_ayuda']; ?>
  </p>
  <?php if(isset($encuesta) && !empty($encuesta)): ?>
    <i>Encuesta creada el <?php echo p_formatear_fecha($encuesta['enc_creado']); ?></i><hr>
  <?php endif; ?>

  <form id="formulario" onsubmit="return false;">
    <?php p_render_tree($tree['']); ?>
  <!--input type="button" value="<?php //echo (isset($encuesta) ? 'Guardar cambios' : 'Registrar nueva encuesta') ; ?>" onclick="p_enviar_formulario()" /-->
    <?php if(!$solo_lectura):?>
    <div class="alert alert-success" style="display:none;" id="guardado_ok">Formulario guardado con éxito</div>
    <div class="alert alert-danger" style="display:none;" id="guardado_error">No se pudo guardar el formulario</div>
    <button class="btn btn-success" onclick="p_enviar_formulario()" />Guardar</button>
    <button class="btn btn-primary" onclick="p_enviar_formulario('salir')" />Guardar y salir</button>
    <button class="btn btn-danger" onclick="p_finalizar()" />Finalizar</button>
    <?php endif; ?>
  </form>
</div>

<script>
function p_enviar_formulario(accion) {
    accion = ((typeof(accion) === 'undefined') ? '' : accion);
    $('#vm_procesando').modal('show');
    var finalizada = (accion=='finalizada') ? 1: 0;
    var respuestas_json = [];
    var respuestas_json = $('#formulario').serializeArray();
    /*
    var respuestas = document.getElementsByTagName('input');
    console.log(respuestas);
    for(respuesta in respuestas) {
        //console.log(respuesta, typeof(respuesta), typeof(respuestas[respuesta].value));
        if (respuesta.search('prg') !== -1 && typeof(respuestas[respuesta].value) === 'string') {
            var valor = respuestas[respuesta].value;
            var id = respuestas[respuesta].id.replace('prg', '');
            //console.log(respuesta);
            //respuestas_json[id] = {[id]: valor};
            respuestas_json.push({id: id, valor: valor});
            //respuestas_json[id] = valor;
        }
}
     */

    //respuestas.forEach(function(respuesta){
    //    json[respuesta.id] = respuesta.value;
    //});
    console.log('JSON: ', respuestas_json);
    var jsondata = JSON.stringify(respuestas_json);
    var xmlhttp = new XMLHttpRequest();
    var url = "/_guardar_respuestas";
    xmlhttp.open("POST", url, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    //xmlhttp.setRequestHeader("Content-type", "application/json");
    xmlhttp.onreadystatechange = function () { //Call a function when the state changes.
        $('#vm_procesando').modal('hide');
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            //console.log('RESPUESTA REST: ', xmlhttp.responseText);
            $('#guardado_error').hide('fast');
            $('#guardado_ok').show('fast');
            respuesta = JSON.parse(xmlhttp.responseText);
            console.log('RESPUESTA REST: ', respuesta);

            window.enc_id = respuesta['enc_id'];
            if (accion !== '') {
                window.location.replace('/main');
            }
        } else {
            $('#guardado_ok').hide('fast');
            $('#guardado_error').show('fast');
        }
    }
    console.log('INFO ENVIADA:', jsondata);

    xmlhttp.send('respuestas_json='+jsondata+'&enc_id='+enc_id+'&finalizada='+finalizada);
}


var enc_id = <?php echo ((isset($encuesta) && isset($encuesta['enc_id'])) ? $encuesta['enc_id'] : '-1'); ?>;

function p_mostrar_ocultar_hijos(target){
    var id = target.id;
    var hijos = document.getElementById('hijos_' + id);
    /*
    if (target.checked){
        hijos.style.display = '';
    } else {
        hijos.style.display = 'none';
    }
     */
    var inputs = target.parentNode.parentNode.getElementsByTagName('input');
    //console.log(target, inputs);
    for(index in inputs){
        input = inputs[index];
        //console.log(index, input, input.id, input.type, input.checked);
        //if (typeof(input.onchange === 'function')){
        if (input.type=='radio' || input.type == 'checkbox'){
            id = input.id;
            //hijos = document.getElementById('hijos_' + id);
            if (input.checked){
            //    hijos.style.display = '';
                $('#hijos_' + id).show('fast');
            } else {
            //    hijos.style.display = 'none';
                $('#hijos_' + id).hide('fast');

                $('#hijos_' + id).find(':input').each(function() {
                    switch(this.type) {
                    case 'password':
                    case 'text':
                    case 'textarea':
                    case 'file':
                    case 'select-one':
                    case 'select-multiple':
                    case 'date':
                    case 'number':
                    case 'tel':
                    case 'email':
                        $(this).val('');
                        break;
                    case 'checkbox':
                    case 'radio':
                        this.checked = false;
                        break;
                    }
                });
            }
            //$('#hijos_' + id).toggle(input.checked);
        }
    }

}

function p_finalizar(){
    var count_total = 0;
    var count_lleno = 0;

    $('div.pregunta').each(function() {

        var lleno = false;
        count_total ++;

        $(this).find(':input').each(function(){

            switch(this.type) {
            case 'password':
            case 'text':
            case 'textarea':
            case 'file':
            case 'select-one':
            case 'select-multiple':
            case 'date':
            case 'number':
            case 'tel':
            case 'email':
                lleno = lleno || ($(this).val() != '');
                break;
            case 'checkbox':
            case 'radio':
                lleno = lleno || this.checked;
                break;
            }
        });

        if (lleno){
            count_lleno ++;
            $(this).removeClass('alert alert-danger');
        } else {
            $(this).addClass('alert alert-danger');
        }
    });
    console.log(count_total, count_lleno);

    if (count_total == count_lleno) {
        if (confirm('Al finalizar un formulario ya no podrá editar la información.\n\nSeguro desea finalizar el formulario?')) {
            p_enviar_formulario('finalizada');
        } else {
        }
    } else {
        alert('No ha completado todas las respuestas, no puede finalizar el formulario.');
    }
}

function p_evaluar_maximo(id){
    console.log('p_evaluar_maximo',id, $('#'+id).val());
    if($('#'+id).val() > 0) {
        $('#hijos_'+id).show('fast');
        $('#hijos_'+id).find(':input').each(function(){
            $(this).val(Math.min($(this).val(), $('#'+id).val()));
        });
    } else {
        $('#hijos_'+id).hide('fast');
    }

}

function p_radiocheck(target, id){
    //console.log(id);
    if (target.checked) {
        $('#' + id).parent().children('div').each(function(){
            //console.log('en div', this.id, (this.id != id));
            if (this.id != id){
                $(this).find(':input').each(function(){
                    if (this.type == 'checkbox'){
                        //console.log('borrando', this.id);
                        this.checked = false;
                        this.onchange();
                    }
                });
            }

        });

    }
}

$.validate({
    modules : 'html5,date',
    lang: 'es'
});


</script>
