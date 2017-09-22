<?php

//var_dump($conn);
//$frm_id = (int)$_GET['id'];
$frm_id = (isset($args[0])) ? $args[0] : -1;;
$result = pg_query($conn, 'select * from esamyn.esa_formulario where frm_id='.$frm_id);

$formulario = pg_fetch_array($result, 0);
$respuestas = array();


if (isset($_GET['enc'])) {
    $enc_id = (int)$_GET['enc'];
    $result = pg_query($conn, "SELECT * FROM esamyn.esa_encuesta WHERE enc_id=$enc_id");
    $encuesta = pg_fetch_array($result, 0);

    if ((int)$encuesta['enc_formulario'] !== $frm_id) {
        echo "ERROR FATAL: El formulario de la encuesta ({$encuesta[enc_formulario]}) no corresponde al formulario referenciado ($frm_id).";
        die();
    }

    $result = pg_query("SELECT * FROM esamyn.esa_respuesta WHERE res_encuesta=$enc_id");
    while ($respuesta = pg_fetch_array($result)) {
        $respuestas[$respuesta['res_pregunta']] = $respuesta['res_valor_texto'];
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
//

function p_render_tree($nodo) {
    global $respuestas;
    global $tipos_pregunta;

    $texto = (isset($nodo['prg_texto'])) ? trim($nodo['prg_texto']) : '';
    $texto = str_replace("\n", "<br>", $texto);
    //$texto = "<pre>$texto</pre>";
    $validacion = (isset($nodo['prg_validacion'])) ? trim($nodo['prg_validacion']) : '';
    $ayuda = (isset($nodo['prg_ayuda'])) ? trim($nodo['prg_ayuda']) : '';
    $prefijo = (isset($nodo['prg_prefijo'])) ? trim($nodo['prg_prefijo']) : '';
    $subfijo = (isset($nodo['prg_subfijo'])) ? trim($nodo['prg_subfijo']) : '';
    $imagen = (isset($nodo['prg_imagen'])) ? trim($nodo['prg_imagen']) : '';

    $prg_id = $nodo['prg_id'];
    //$name = 'prg'.$prg_id;
    $name = $prg_id;
    $id = $name;
    $value = isset($respuestas[$prg_id]) ? $respuestas[$prg_id] : '';

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
        $validacion = (!empty($validacion) && ctype_digit($validacion)) ? 'maxlength="'.$validacion.'"' : $validacion;
        if ($tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']] == 'grupo' ) {

            echo '<div style="border:solid 2px #EEE;margin:5px;padding:5px;">';

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
            echo '<div class="row">';
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
    case 'multitexto':
        echo '<div class="row"><div class="col-md-6">';
          echo '<label for="'.$id.'">'.$texto . ': </label>';
          echo '<textarea class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>' . $value . '</textarea>'; 
          echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '</div></div>';
        break;
    case 'fecha':
        echo '<div class="row"><div class="col-md-6">';
          echo '<label for="'.$id.'">'.$texto . ': </label>';
          echo '<input type="date" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>'; 
          echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '</div></div>';
        break;
    case 'hora':
        echo '<div class="row"><div class="col-md-6">';
          echo '<label for="'.$id.'">'.$texto . ': </label>';
          echo '<input type="time" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>'; 
          echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '</div></div>';
        break;
    case 'numero':
        $validacion = (!empty($validacion) && ctype_digit($validacion)) ? 'maxlength="'.$validacion.'"' : $validacion;
        if ($tipos_pregunta[$nodo['padre']['prg_tipo_pregunta']] == 'grupo' ) {

            echo '<div style="border:solid 2px #EEE;margin:5px;padding:5px;">';

            echo '<div style="background-color:#EEE;color:#333;font-size:20px;padding:5px;">';
            echo $texto . ':';
            echo '</div>';

            echo '<div class="row"><div class="col-md-6">';
            echo (($prefijo != '' || $subfijo != '') ? '<div class="input-group">' : '');
            echo ($prefijo != '' ? '<div class="input-group-addon">'.$prefijo.'</div>' : '');
            echo '<input type="number" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>'; 
            echo ($subfijo != '' ? '<div class="input-group-addon">'.$subfijo.'</div>' : '');
            echo (($prefijo != '' || $subfijo != '') ? '</div>' : '');
            echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
            echo '</div></div>';
            echo '</div>';
        } else {
            echo '<div class="row"><div class="col-md-6">';
            echo '<label for="'.$id.'">'.$texto . ': </label>';
            echo (($prefijo != '' || $subfijo != '') ? '<div class="input-group">' : '');
            echo ($prefijo != '' ? '<div class="input-group-addon">'.$prefijo.'</div>' : '');
            echo '<input type="number" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$validacion.'>'; 
            echo ($subfijo != '' ? '<div class="input-group-addon">'.$subfijo.'</div>' : '');
            echo (($prefijo != '' || $subfijo != '') ? '</div>' : '');
            echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
            echo '</div></div>';
            break;
        }
        break;
    case 'email':
        echo '<div class="row"><div class="col-md-6">';
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
            p_render_tree($hijo);
            echo '</div>';
        }
        echo '</div>';
        break;
    case 'check_no':
        break;
    case 'check':
        //echo 'izzz';
        echo '<div style="border:solid 2px #EEE;margin:5px;padding:5px;">';

        echo '<div style="background-color:#EEE;color:#333;font-size:20px;padding:5px;">';
        echo $texto ;
        echo '</div>';
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '<div style="">';
        foreach($nodo['hijos'] as $hijo){
            echo '<div>';
            echo '<input type="checkbox" name="'.$hijo['prg_id']. '" id="'.$hijo['prg_id'].'" value="'.$hijo['prg_texto'].'" onchange="p_mostrar_ocultar_hijos(this)">';
            echo ' <label for="'.$hijo['prg_id'].'">' . $hijo['prg_texto'] . '</label>';
            
            //echo '<pre>';
            //var_dump($hijo);
            //echo '</pre>';
            //echo "(".count($hijo['hijos']).")";
            $display = 'none';
            echo '<div id="hijos_'.$hijo['prg_id'].'" style="display:'.$display.';">';
            p_render_tree($hijo);
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        break;

    case 'radio':
        echo '<div style="border:solid 2px #EEE;margin:5px;padding:5px;">';

        echo '<div style="background-color:#EEE;color:#333;font-size:20px;padding:5px;">';
        echo $texto ;
        echo '</div>';
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '<div style="padding-left:20px;">';
        $class_radio = count($nodo['hijos']) > 3 ? 'radio' : 'radio-inline';
        foreach($nodo['hijos'] as $hijo){
            echo '<label class="'.$class_radio.'">';
            echo '<input type="radio" name="'.$name. '" id="'.$hijo['prg_id'].'" value="'.$hijo['prg_texto'].'"  onchange="p_mostrar_ocultar_hijos(this)">';
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
            //echo '<div>';
            //echo '<input type="radio" name="'.$name. '" id="'.$hijo['prg_id'].'" value="'.$hijo['prg_texto'].'"  onchange="p_mostrar_ocultar_hijos(this)">';
            //echo ' <label for="'.$hijo['prg_id'].'">' . $hijo['prg_texto'] . '</label>';
            
            //echo '<pre>';
            //var_dump($hijo);
            //echo '</pre>';
            //echo "(".count($hijo['hijos']).")";
            $display = 'none';
            echo '<div id="hijos_'.$hijo['prg_id'].'" style="display:'.$display.';">';
            p_render_tree($hijo);
            echo '</div>';
            //echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        break;

    case 'tabla':
        echo '<div style="">';
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

                p_render_tree($nieto);
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
        echo '<input type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$value.'">'; 
        break;
    case 'cabecera1':
        echo '<div class="container">';
        echo "<h2>$texto</h2>";
        foreach($nodo['hijos'] as $hijo){
            echo '<div class="form-group">';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            p_render_tree($hijo);
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
            p_render_tree($hijo);
            echo '</li>';
        }
        echo '</ul>';
        break;
    case 'grupo':
        echo '<div style="border:solid 2px #000;margin-right:20px;">';

        echo '<div style="background-color:#000;color:#FFF;font-size:40px;padding:5px;">';
        echo $texto ;
        echo '</div>';
        echo ($ayuda != '' ? '<p class="help-block">'.$ayuda.'</p>' : '');
        echo '<div style="">';
        foreach($nodo['hijos'] as $hijo){
            echo '<div>';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            p_render_tree($hijo);
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

            echo '<div style="border:solid 2px #EEE;margin:5px;padding:5px;">';

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
            echo '<img src="/img/'.$imagen.'">';
        }
        echo '<div style="padding:5px;">';
        foreach($nodo['hijos'] as $hijo){
            echo '<div>';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            p_render_tree($hijo);
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        break;

    default:
        //echo $texto ;
        echo '<ul>';
        foreach($nodo['hijos'] as $hijo){
            //echo '<li>';
            //var_dump($hijo);
            //echo "(".count($hijo['hijos']).")";
            p_render_tree($hijo);
            //echo '</li>';
        }
        echo '</ul>';
        break;

    }
}


?>


<h1>
<?php echo $formulario['frm_clave']. '. '. $formulario['frm_titulo']; ?>
</h1>
    <?php if(isset($encuesta)): ?><i>Encuesta llenada <?php echo $encuesta['enc_creado']; ?></i><hr><?php endif; ?>

<form id="formulario" onsubmit="return false;">
  <?php p_render_tree($tree['']); ?>
<!--input type="button" value="<?php echo (isset($encuesta) ? 'Guardar cambios' : 'Registrar nueva encuesta') ; ?>" onclick="p_enviar_formulario()" /-->
<button class="btn btn-primary" onclick="p_enviar_formulario()" />
<?php echo (isset($encuesta) ? 'Guardar cambios' : 'Registrar nueva encuesta') ; ?>
</button>
</form>


<script>
function p_enviar_formulario() {
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
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            console.log('RESPUESTA REST: ', xmlhttp.responseText);
            //window.location.replace('/main');
        }
    }
    console.log('INFO ENVIADA:', jsondata);

    var enc_id = <?php echo (isset($encuesta) ? $encuesta['enc_id'] : '-1'); ?>;
    xmlhttp.send('respuestas_json='+jsondata+'&enc_id='+enc_id);
}

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
            }
            //$('#hijos_' + id).toggle(input.checked);
        }
    }

}
$.validate({
    modules : 'html5,date',
    lang: 'es'
});
</script>
