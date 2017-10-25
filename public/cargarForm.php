<?php

//$result = pg_query($conn, 'select * from esamyn.esa_formulario ORDER BY frm_clave');
//$formularios = pg_fetch_all($result);

$formularios = q('SELECT * FROM esamyn.esa_formulario ORDER BY frm_clave');

//echo '<pre>';
//var_dump($formularios);
//echo '</pre>';
//echo '['.(isset($_POST['formulario'])?1:0).']';
//echo '['.(!empty($_POST['formulario'])?1:0).']';
//echo '['.(isset($_FILES['mindmap'])?1:0).']';
//echo '['.(!empty($_FILES['mindmap'])?1:0).']';

//var_dump($_POST);
//echo (isset($_POST['formulario'])?1:0);
//echo (!empty($_POST['formulario'])?1:0);
//echo (isset($_FILES['mindmap'])?1:0);
//echo (!empty($_FILES['mindmap'])?1:0);

if (isset($_POST['formulario']) && !empty($_POST['formulario']) && isset($_FILES['mindmap']) && !empty($_FILES['mindmap'])) {
    $frm_id = (int)$_POST['formulario'];
    $frm_titulo = '';
    if ($frm_id === 0) {
        //evaluacion
        $frm_titulo = "Formulario de Evaluación";
    } else {
        foreach($formularios as $formulario) {
            if ((int)$formulario['frm_id'] === $frm_id) {
                $frm_titulo = $formulario['frm_titulo'];
            }
        }
    }

    try {
        // Undefined | Multiple Files | $_FILES Corruption Attack
        // If this request falls under any of them, treat it invalid.
        if (
            !isset($_FILES['mindmap']['error']) ||
            is_array($_FILES['mindmap']['error'])
        ) {
            throw new RuntimeException('Invalid parameters.');
        }

        // Check $_FILES['mindmap']['error'] value.
        switch ($_FILES['mindmap']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
        }

        // You should also check filesize here. 
        if ($_FILES['mindmap']['size'] > 1000000) {
            throw new RuntimeException('Exceeded filesize limit.');
        }

        // DO NOT TRUST $_FILES['mindmap']['mime'] VALUE !!
        // Check MIME Type by yourself.
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($_FILES['mindmap']['tmp_name']),
            array(
                'mm' => 'application/x-freemind',
            ),
            true
        )) {
            throw new RuntimeException('Invalid file format.');
        }


        // You should name it uniquely.
        // DO NOT USE $_FILES['mindmap']['name'] WITHOUT ANY VALIDATION !!
        // On this example, obtain safe unique name from its binary data.
        $filename = sha1_file($_FILES['mindmap']['tmp_name']) . date('-Ymd-His') . ".$ext";
        if (!move_uploaded_file(
            $_FILES['mindmap']['tmp_name'],
            sprintf(
                './uploads/%s',
                $filename
            )
        )) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        //echo 'File is uploaded successfully: ' . $filename;
        echo "<div class='alert alert-success'>Archivo cargado exitosamente.</div>";

        $path = "./uploads/$filename";

        $xmlstring = file_get_contents($path);
        $xml = simplexml_load_string($xmlstring, "SimpleXMLElement", LIBXML_NOCDATA);
        $array = json_decode(json_encode($xml), TRUE);
        //echo '<pre>';
        //var_dump($array['node']['node']);
        //echo '</pre>';
        if ($array["node"]["@attributes"]["TEXT"] !== $frm_titulo) {
            throw new RuntimeException('El mapa mental no corresponde al formulario escogido. Elija el formulario correcto, o modifique el texto del nodo raiz en el mapa mental para que se ajuste al título del formulario "'.$frm_titulo.'". El texto del nodo raíz es "'.$array["node"]["@attributes"]["TEXT"].'".');
        }
        if ($frm_id === 0) {
            //evaluacion
            foreach(['parametro_pregunta', 'verificador', 'parametro', 'grupo_parametro', 'cumple_condicion_no_aplica', 'condicion_no_aplica', 'evaluacion'] as $tabla){
                //$sql = ("DELETE FROM esamyn.esa_$tabla RETURNING *");
                $sql = "TRUNCATE esamyn.esa_$tabla RESTART IDENTITY CASCADE";
                //echo "<pre>$sql</pre>";
                $result = q($sql);
                //if (!$result){
                //    echo "<div class='alert alert-danger'>ERROR:<pre>$sql</pre></div>";
               // }
            }
            
            p_guardar_evaluacion($array['node']['node']);

            echo "<div class='alert alert-success'>Se han cargado $count_parametro parámetros de evaluación.</div>";
        } else {
            pg_query("DELETE FROM esamyn.esa_respuesta WHERE res_encuesta in (SELECT enc_id from esamyn.esa_encuesta WHERE enc_formulario = $frm_id)");
            pg_query("DELETE FROM esamyn.esa_encuesta WHERE enc_formulario = $frm_id");
            pg_query("DELETE FROM esamyn.esa_verificador WHERE ver_parametro IN (SELECT ppr_parametro FROM esamyn.esa_parametro_pregunta WHERE ppr_pregunta IN (SELECT prg_id FROM esamyn.esa_pregunta WHERE prg_formulario = $frm_id))");
            pg_query("DELETE FROM esamyn.esa_parametro_pregunta WHERE ppr_pregunta IN (SELECT prg_id FROM esamyn.esa_pregunta WHERE prg_formulario = $frm_id)");
            pg_query("DELETE FROM esamyn.esa_cumple_condicion_no_aplica WHERE ccn_pregunta IN (SELECT prg_id FROM esamyn.esa_pregunta WHERE prg_formulario = $frm_id)");
            pg_query("DELETE FROM esamyn.esa_pregunta WHERE prg_formulario = $frm_id");

            p_guardar_preguntas($array['node']['node']);

            //echo "<div><a href='download.php?frm_id=$frm_id'>Descargar archivo de carga de la tabla esa_pregunta</a></div>";
            echo "<div class='alert alert-success'>Se han cargado $count_pregunta preguntas del formulario <strong><a href=\"/form/$frm_id\">$frm_titulo</a></strong>.</div>";
        }

    } catch (RuntimeException $e) {

        echo '<div class="alert alert-danger">ERROR EN CARGA DEL ARCHIVO: '. $e->getMessage() . '</div>';

    }
}


$count_parametro = 0;
function p_guardar_evaluacion($hijos, $padre = null, $padre2 = null){
    global $conn;
    global $frm_id;
    global $count_parametro;

    //echo "[eva:]";
    if(empty($hijos)) {
        return;
    } else {
        if (isset($hijos["@attributes"])) {
            $hijos = array($hijos);
        }
        $padre = empty($padre) ? 'null' : $padre;
        $count = 0;
        //$condiciones_no_aplica = array();

        foreach($hijos as $hijo){
            //echo "<pre>";
            //var_dump($hijo);
            //echo "</pre><hr>";
            
            $count++;
            $texto = $hijo["@attributes"]["TEXT"];
            $texto = trim($texto);
            $texto = "'$texto'";
            $tipo_grupo_parametro = 'null';
            $sql_tipo_grupo_parametro = 'null';
            $par_tipo_grupo_parametro = 'null';

            $tipo = '';
            $codigo = 'null';
            $codigo_like = 'null';

            $puntaje = 0;
            $condicion_no_aplica = 'null';
            $par_condicion_no_aplica = 'null';
            $sql_par_condicion_no_aplica = 'null';
            $obligatorio = 0;
            $umbral = 0;
            $cantidad_minima = 0;
            $operador_logico = '0';
            $porcentaje = 100;

            if (isset($hijo['attribute'])) {
                //echo "<pre>";
                //var_dump($hijo['attribute']);
                //echo "</pre><hr>";
                $attributes = (!isset( $hijo['attribute']["@attributes"])) ? $hijo['attribute'] : array( $hijo['attribute'] );

                foreach( $attributes as  $attribute) {
                    if ($attribute["@attributes"]["NAME"] == 'tipo'){
                        $tipo_grupo_parametro = trim($attribute["@attributes"]["VALUE"]);
                        if (!empty($tipo_grupo_parametro)) {

                            $sql_tipo_grupo_parametro = "SELECT tgp_id FROM esamyn.esa_tipo_grupo_parametro WHERE tgp_texto='$tipo_grupo_parametro'";
                            $result = q($sql_tipo_grupo_parametro);
                            if (!$result) {
                                $nuevo = q("INSERT INTO esamyn.esa_tipo_grupo_parametro(tgp_texto) VALUES ('$tipo_grupo_parametro') RETURNING tgp_id");
                                $par_tipo_grupo_parametro = $nuevo[0]['tgp_id'];
                            } else {
                                $par_tipo_grupo_parametro = $result[0]['tgp_id'];
                            }
                            $sql_tipo_grupo_parametro = "($sql_tipo_grupo_parametro)";
                        }
                    }
                    if ($attribute["@attributes"]["NAME"] == 'codigo'){
                        $codigo = trim($attribute["@attributes"]["VALUE"]);
                        $codigo_like = "'%[\"$codigo\"]%'";
                        $codigo = "'$codigo'";
                    }


                    if ($attribute["@attributes"]["NAME"] == 'puntaje'){
                        $puntaje = $attribute["@attributes"]["VALUE"];
                    }
                    if ($attribute["@attributes"]["NAME"] == 'no-aplica'){
                        $condicion_no_aplica = $attribute["@attributes"]["VALUE"];
                        if (!empty($condicion_no_aplica)) {
                            $sql_par_condicion_no_aplica = "SELECT cna_id FROM esamyn.esa_condicion_no_aplica WHERE cna_texto='$condicion_no_aplica'";
                            //echo "<pre>$sql_par_condicion_no_aplica</pre>";

                            $condicion = q($sql_par_condicion_no_aplica);
                            //var_dump($condicion);
                            if (!$condicion) {
                                $nueva_condicion = q("INSERT INTO esamyn.esa_condicion_no_aplica (cna_texto) VALUES ('$condicion_no_aplica') RETURNING cna_id");
                                //var_dump($nueva_condicion);
                                $par_condicion_no_aplica = $nueva_condicion[0]['cna_id'];
                                //echo "[nueva condicion]";
                                //
                                //ENLAZA LA CONDICION NO APLICA:
                                $sql2 = ("SELECT prg_id FROM esamyn.esa_pregunta WHERE prg_codigo_no_aplica='$condicion_no_aplica'");
                                $preguntas_enlazadas = q($sql2); 
                                //echo "<pre>$sql2</pre>";

                                //$cna_id = "(SELECT cna_id FROM esamyn.esa_condicion_no_aplica WHERE cna_texto='$condicion_no_aplica')";
                                $cna_id = $par_condicion_no_aplica;

                                foreach($preguntas_enlazadas as $pregunta_enlazada){
                                    $prg_id = $pregunta_enlazada['prg_id'];
                                    $sql2 = ("INSERT INTO esamyn.esa_cumple_condicion_no_aplica(ccn_pregunta, ccn_condicion_no_aplica) VALUES ($prg_id, $cna_id) RETURNING ccn_id");
                                    $result = q($sql2);
                                    if (!$result) {
                                        echo "<div class='alert alert-danger'>ERROR <pre>$sql2</pre></div>";
                                    } else {

                                    } 
                                    //echo "<pre>$sql2</pre>";
                                }
                            } else {
                                //var_dump($condicion);
                                $par_condicion_no_aplica = $condicion[0]['cna_id'];
                            }
                            //echo "[$condicion_no_aplica: $par_condicion_no_aplica]";

                            $sql_par_condicion_no_aplica = "($sql_par_condicion_no_aplica)";
                        }
                    }
                    if ($attribute["@attributes"]["NAME"] == 'obligatorio'){
                        $obligatorio = $attribute["@attributes"]["VALUE"];
                        $obligatorio = ($obligatorio == 'SI') ? 1 : 0;
                    }
                    if ($attribute["@attributes"]["NAME"] == 'umbral'){
                        $umbral = $attribute["@attributes"]["VALUE"];
                    }
                    if ($attribute["@attributes"]["NAME"] == 'cantidad_minima'){
                        $cantidad_minima = $attribute["@attributes"]["VALUE"];
                        $cantidad_minima = empty($cantidad_minima)? 1 : $cantidad_minima;
                    }
                    if ($attribute["@attributes"]["NAME"] == 'operador_logico'){
                        $operador_logico = $attribute["@attributes"]["VALUE"];

                        $operador_logico = ($operador_logico == 'Y') ? 1 : 0;
                    }
                    if ($attribute["@attributes"]["NAME"] == 'porcentaje'){
                        $porcentaje = $attribute["@attributes"]["VALUE"];

                        $porcentaje = empty($porcentaje)? 100 : $porcentaje;
                    }
                }
            }

            $padre = empty($padre) ? 'null' : $padre;
            $padre2 = empty($padre2) ? 'null' : $padre2;
            if ($tipo_grupo_parametro == 'parametro') {
                $sql = "INSERT INTO esamyn.esa_parametro(
                    par_grupo_parametro, 
                    par_padre,
                    par_puntaje, 
                    par_condicion_no_aplica, 
                    par_texto, 
                    par_obligatorio, 
                    par_umbral, 
                    par_cantidad_minima, 
                    par_operador_logico,
                    par_porcentaje, 
                    par_codigo
                ) VALUES (
                    $padre, 
                    $padre2,
                    $puntaje,
                    $par_condicion_no_aplica, 
                    $texto, 
                    $obligatorio, 
                    $umbral, 
                    $cantidad_minima, 
                    $operador_logico, 
                    $porcentaje, 
                    $codigo
                ) RETURNING par_id";

                //$result = pg_query($sql);
                //$par_id = pg_fetch_result($result, 'par_id');
                $result = q($sql);
                if (!$result) {
                    echo "<div class='alert alert-danger'>ERROR <pre>$sql</pre></div>";
                } else {
                    $count_parametro ++;
                    $par_id = $result[0]['par_id'];

                    if (isset($hijo["node"])){
                        p_guardar_evaluacion($hijo["node"], null, $par_id);
                    }

                    //VERIFICADORES:

                    $sql2 = ("SELECT prg_id FROM esamyn.esa_pregunta WHERE prg_codigo_verificacion like $codigo_like");
                    $preguntas_enlazadas = q($sql2); 
                    //echo "<pre>$sql2</pre>";

                    foreach($preguntas_enlazadas as $pregunta_enlazada){
                        $prg_id = $pregunta_enlazada['prg_id'];
                        $sql2 = ("INSERT INTO esamyn.esa_parametro_pregunta(ppr_pregunta, ppr_parametro) VALUES ($prg_id, $par_id) RETURNING ppr_id");
                        $result = q($sql2);
                        if (!$result) {
                            echo "<div class='alert alert-danger'>ERROR <pre>$sql2</pre></div>";
                        } else {

                        } 
                        //echo "<pre>$sql2</pre>";
                    }


                }
            } else {
                $sql = "INSERT INTO esamyn.esa_grupo_parametro(gpa_padre, gpa_texto, gpa_clave, gpa_tipo_grupo_parametro) VALUES ($padre, $texto, $codigo, $par_tipo_grupo_parametro) RETURNING gpa_id";
                //$result = pg_query($sql);
                //$gpa_id = pg_fetch_result($result, 'gpa_id');
                $result = q($sql);
                if (!$result) {
                    echo "<div class='alert alert-danger'>ERROR <pre>$sql</pre></div>";
                } else {
                    $gpa_id = $result[0]['gpa_id'];

                    if (isset($hijo["node"])){
                        p_guardar_evaluacion($hijo["node"], $gpa_id);
                    }
                }
            }
            //echo "<pre>$sql</pre>";
        }
    }
}

$count_pregunta = 0;
function p_guardar_preguntas($hijos, $padre = null) {
    global $conn;
    global $frm_id;
    global $count_pregunta;

    if(empty($hijos)) {
        return;
    } else {
        if (isset($hijos["@attributes"])) {
            $hijos = array($hijos);
        }
        $padre = empty($padre) ? 'null' : $padre;
        $count = 0;
        foreach($hijos as $hijo){
            //echo "<pre>";
            //var_dump($hijo);
            //echo "</pre><hr>";
            
            $count++;
            $texto = $hijo["@attributes"]["TEXT"];
            $texto = trim($texto);
            $texto = "'$texto'";
            $sql_tipo_pregunta = 'null';
            $codigo_verificacion = 'null';
            $codigo_no_aplica = 'null';
            $ayuda = 'null';
            $prefijo = 'null';
            $subfijo = 'null';
            $validacion = 'null';
            $imagen = 'null';

            //if (isset($hijo['richcontent'])&& isset($hijo['richcontent']['html'])&& isset($hijo['richcontent']['html']['body'])&& isset($hijo['richcontent']['html']['body']['p']) ) {
            if (isset($hijo['richcontent']) && isset($hijo['richcontent']['html']) && isset($hijo['richcontent']['html']['body']) ) {
                //echo "XXX";
                $ayuda = array_to_xml($hijo['richcontent']['html']['body']);
                //echo $ayuda;
                $ayuda = trim(str_replace('[[', '<', str_replace("]]", '>', $ayuda)));
                //$ayuda = trim($hijo['richcontent']['html']['body']['p']);
                $ayuda = "'$ayuda'";
            }
            if (isset($hijo['attribute'])) {
                //echo "<pre>";
                //var_dump($hijo['attribute']);
                //echo "</pre><hr>";
                $attributes = (!isset( $hijo['attribute']["@attributes"])) ? $hijo['attribute'] : array( $hijo['attribute'] );
                $glue = '';
                $codigo_verificacion = '';
                $codigo_no_aplica = '';

                foreach( $attributes as  $attribute) {
                    if ($attribute["@attributes"]["NAME"] == 'prefijo'){
                        $prefijo = $attribute["@attributes"]["VALUE"];
                        $prefijo = "'$prefijo'";
                    }
                    if ($attribute["@attributes"]["NAME"] == 'subfijo'){
                        $subfijo = $attribute["@attributes"]["VALUE"];
                        $subfijo = "'$subfijo'";
                    }
                    if ($attribute["@attributes"]["NAME"] == 'validacion'){
                        $validacion = $attribute["@attributes"]["VALUE"];
                        $validacion = "'$validacion'";
                    }
                    if ($attribute["@attributes"]["NAME"] == 'imagen'){
                        $imagen = $attribute["@attributes"]["VALUE"];
                        $imagen = "'$imagen'";
                    }
                    if ($attribute["@attributes"]["NAME"] == 'tipo'){
                        $clave = $attribute["@attributes"]["VALUE"];
                        $sql_tipo_pregunta = "SELECT tpp_id FROM esamyn.esa_tipo_pregunta WHERE tpp_clave='$clave'";
                        $sql_tipo_pregunta = "($sql_tipo_pregunta)";
                    }
                    if ($attribute["@attributes"]["NAME"] == 'verificador'){
                        $buff = trim($attribute["@attributes"]["VALUE"]);
                        $codigo_verificacion .= $glue.'["'.$buff.'"]';
                    }
                    if ($attribute["@attributes"]["NAME"] == 'no-aplica'){
                        $buff = trim($attribute["@attributes"]["VALUE"]);
                        //$codigo_no_aplica .= $glue.$buff;
                        $codigo_no_aplica = $buff;
                    }
                    $glue = ',';
                }
                 $codigo_verificacion = ( $codigo_verificacion == '') ? 'null' : "'$codigo_verificacion'";
                 $codigo_no_aplica = ( $codigo_no_aplica == '') ? 'null' : "'$codigo_no_aplica'";
            }
            $sql = "INSERT INTO esamyn.esa_pregunta(
                prg_formulario, 
                prg_padre, 
                prg_texto, 
                prg_orden, 
                prg_tipo_pregunta, 
                prg_codigo_verificacion,
                prg_codigo_no_aplica,
                prg_ayuda,
                prg_prefijo,
                prg_subfijo,
                prg_validacion,
                prg_imagen
            ) VALUES (
                $frm_id, 
                $padre, 
                $texto, 
                ".($count*100).", 
                $sql_tipo_pregunta, 
                $codigo_verificacion,
                $codigo_no_aplica,
                $ayuda,
                $prefijo,
                $subfijo,
                $validacion,
                $imagen
            ) RETURNING prg_id";

            //echo "<pre>$sql</pre>";
            //$result = pg_query($sql);
            //$prg_id = pg_fetch_result($result, 'prg_id');
            $result = q($sql);
            if (!$result) {
                echo "<div class='alert alert-danger'>ERROR <pre>$sql</pre></div>";
            } else { 
                $prg_id = $result[0]['prg_id'];
                $count_pregunta++;

                if (isset($hijo["node"])){
                    p_guardar_preguntas($hijo["node"], $prg_id);
                }
            }
        }
    }
}

?>


<h1>Cargar Formulario</h1>
<div>
Formulario: 
<form method="POST" action="" enctype="multipart/form-data">
<select name="formulario">
<?php foreach($formularios as $formulario): ?>
<option value="<?php echo $formulario['frm_id']; ?>"><?php echo $formulario['frm_clave'] . '. ' . $formulario['frm_titulo']; ?></option>
<?php endforeach; ?>
<option value="eva">Formulario de Evaluación</option>
</select>
</div>

<div>
Archivo .mm:
<input type="file" name="mindmap" accept=".mm, application/x-freemind" />
</div>

<input type="submit" value="Cargar" class="btn btn-primary">
</form>

