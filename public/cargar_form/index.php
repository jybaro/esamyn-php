<?php

require_once('../../private/bdd.php');
$result = pg_query($conn, 'select * from esamyn.esa_formulario ORDER BY frm_clave');
$formularios = pg_fetch_all($result);
//echo '<pre>';
//var_dump($formularios);
//echo '</pre>';
//echo '['.(isset($_POST['formulario'])?1:0).']';
//echo '['.(!empty($_POST['formulario'])?1:0).']';
//echo '['.(isset($_FILES['mindmap'])?1:0).']';
//echo '['.(!empty($_FILES['mindmap'])?1:0).']';


if (isset($_POST['formulario']) && !empty($_POST['formulario']) && isset($_FILES['mindmap']) && !empty($_FILES['mindmap'])) {
    $frm_id = (int)$_POST['formulario'];
    $frm_titulo = '';
    foreach($formularios as $formulario) {
        if ((int)$formulario['frm_id'] === $frm_id) {
            $frm_titulo = $formulario['frm_titulo'];
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

        echo 'File is uploaded successfully: ' . $filename;

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
        pg_query("DELETE FROM esamyn.esa_respuesta WHERE res_encuesta in (SELECT enc_id from esamyn.esa_encuesta WHERE enc_formulario = $frm_id)");
        pg_query("DELETE FROM esamyn.esa_encuesta WHERE enc_formulario = $frm_id");
        pg_query("DELETE FROM esamyn.esa_pregunta WHERE prg_formulario = $frm_id");
        p_guardar_preguntas($array['node']['node']);
        echo "<div><a href='download.php?frm_id=$frm_id'>Descargar archivo de carga de la tabla esa_pregunta</a></div>";

    } catch (RuntimeException $e) {

        echo '<div>ERROR EN CARGA DEL ARCHIVO: '. $e->getMessage() . '</div>';

    }
}

function p_guardar_preguntas($hijos, $padre = null) {
    global $conn;
    global $frm_id;

    if(empty($hijos)) {
        return;
    } else {
        if (isset($hijos["@attributes"])) {
            $hijos = array($hijos);
        }
        $padre = empty($padre) ? 'null' : $padre;
        $count = 0;
        foreach($hijos as $hijo){
            $count++;
            $texto = $hijo["@attributes"]["TEXT"];
            $texto = "'$texto'";
            $result = pg_query("INSERT INTO esamyn.esa_pregunta(prg_formulario, prg_padre, prg_texto, prg_orden) VALUES ($frm_id, $padre, $texto, ".($count*100).") RETURNING prg_id");
            $prg_id = pg_fetch_result($result, 'prg_id');

            if (isset($hijo["node"])){
                p_guardar_preguntas($hijo["node"], $prg_id);
            }
        }
    }
}

?>


<h1>Cargar Formulario</h1>
<a href="index.php">< Regresar</a>
<hr>
<div>
Formulario: 
<form method="POST" action="" enctype="multipart/form-data">
<select name="formulario">
<?php foreach($formularios as $formulario): ?>
<option value="<?php echo $formulario['frm_id']; ?>"><?php echo $formulario['frm_clave'] . '. ' . $formulario['frm_titulo']; ?></option>
<?php endforeach; ?>
</select>
</div>

<div>
Archivo .mm:
<input type="file" name="mindmap" accept=".mm, application/x-freemind" />
</div>

<input type="submit" value="Cargar">
</form>

