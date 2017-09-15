<?php

require_once('../../private/bdd.php');
//var_dump($conn);
$frm_id = (int)$_GET['id'];
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
}

//echo '<pre>';
//var_dump($tree['']);
//

$preg_count = 0;
function p_render_tree($nodo) {
    global $respuestas;
    global $preg_count;
    $propagar = true;
    $num = '';
    $ocultar = '';

    if (empty($nodo['prg_padre'])) {
        $preg_count ++;
        //$num = $preg_count . '. ';
    }
    if (isset($nodo['prg_texto'])) {
        $prg_id = $nodo['prg_id'];
        $name = 'prg'.$prg_id;
        $id = $name;

        if ($nodo['prg_texto'] == 'Sí' || $nodo['prg_texto'] == 'No') {
            echo '
                <div class="radio">
                  <label><input type="radio" name="optradio">'.$nodo['prg_texto'].'</label>
                  </div>
                
                ';
    }else if (count($nodo['hijos']) == 2 && $nodo['hijos'][0] = 'Sí') {
            //$propagar = false;
            echo '
                <div class="checkbox">
                <label><input type="checkbox" value="" name="'.$name.'" id="'.$id.'" onclick="p_mostrar(\''.$id.'\')">'.$nodo['prg_texto'].'</label>
                </div>
                ';
            if ($nodo['hijos'][0]['hijos'] && count($nodo['hijos'][0]['hijos']) > 0) {
                //echo '+' . count($nodo['hijos'][0]['hijos']);
                //$ocultar = ' id="'.$id.'_group" style="display:none;"';
                echo '<ul id="'.$id.'_group" style="display:none;">';
                foreach($nodo['hijos'][0]['hijos'] as $hijo){
                    //echo '<li>';
                    echo '[]';
                    p_render_tree($hijo);
                    //echo '</li>';
                }
                echo '</ul>';
            }
        } else if (count($nodo['hijos']) == 0 || (count($nodo['hijos']) == 1 && $nodo['hijos'][0][0] == '(')) {

            $value = isset($respuestas[$prg_id]) ? $respuestas[$prg_id] : '';
            //echo '<input name="'.$name.'" id="'.$id.'" value="'.$value.'">'; 
            echo '
                <div class="form-group">
                <label for="usr">'.$num.$nodo['prg_texto'].':</label>
                <input type="text" class="form-control" name="'.$name.'" id="'.$id.'" value="'.$value.'">
                </div>

                ';
        } else {}
    }

    if ($propagar) {
        echo '<ul '.$ocultar.'>';
        foreach($nodo['hijos'] as $hijo){
            //echo '<li>';
            p_render_tree($hijo);
            //echo '</li>';
        }
        echo '</ul>';
    }
}


?>


<h1>
<?php echo $formulario['frm_titulo']; ?>
</h1>
    <?php if(isset($encuesta)): ?><i>Encuesta llenada <?php echo $encuesta['enc_creado']; ?></i><hr><?php endif; ?>
<a href="/esamyn/">< Regresar</a>

<form onsubmit="return false;">
  <?php p_render_tree($tree['']); ?>
<input type="button" value="<?php echo (isset($encuesta) ? 'Guardar cambios' : 'Registrar nueva encuesta') ; ?>" onclick="p_enviar_formulario()" />
</form>


<script>
function p_enviar_formulario() {
    var respuestas_json = [];
    var respuestas = document.getElementsByTagName('input');
    console.log(respuestas);
    for(respuesta in respuestas) {
        console.log(respuesta, typeof(respuesta), typeof(respuestas[respuesta].value));
        if (respuesta.search('prg') !== -1 && typeof(respuestas[respuesta].value) === 'string') {
            var valor = respuestas[respuesta].value;
            var id = respuestas[respuesta].id.replace('prg', '');
            //console.log(respuesta);
            //respuestas_json[id] = {[id]: valor};
            respuestas_json.push({id: id, valor: valor});
            //respuestas_json[id] = valor;
        }
    }
    //respuestas.forEach(function(respuesta){
    //    json[respuesta.id] = respuesta.value;
    //});
    console.log('JSON: ', respuestas_json);
    var jsondata = JSON.stringify(respuestas_json);
    var xmlhttp = new XMLHttpRequest();
    var url = "/esamyn/_guardar_respuestas.php";
    xmlhttp.open("POST", url, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    //xmlhttp.setRequestHeader("Content-type", "application/json");
    xmlhttp.onreadystatechange = function () { //Call a function when the state changes.
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            console.log('RESPUESTA REST: ', xmlhttp.responseText);
        }
    }
    console.log('INFO ENVIADA:', jsondata);

    var enc_id = <?php echo (isset($encuesta) ? $encuesta['enc_id'] : '-1'); ?>;
    xmlhttp.send('respuestas_json='+jsondata+'&enc_id='+enc_id);
}

function p_mostrar(id){
    console.log(id);
    var element = document.getElementById(id);
    console.log(element, element.checked);
    console.log(document.getElementById(id + '_group'));
    if (element.checked){
        document.getElementById(id + '_group').style.display = '';
    } else {
        document.getElementById(id + '_group').style.display = 'none';
    }
}
</script>
