<?php

require_once('../private/config.php');
require_once('../private/bdd.php');

//var_dump($conn);
$result = pg_query($conn, 'select * from esamyn.esa_formulario ORDER BY frm_clave');

//var_dump(pg_fetch_all($result));
?>

<h1>Formularios ingresados</h1>
<p>Se puede ingresar nuevas encuestas, o editar encuestas ya ingresadas.</p>
<?php
$rows = pg_fetch_all($result);
echo '<ul>';
$tree = array();

foreach($rows as $formulario){
    $frm_id = $formulario['frm_id'];
    echo '<li>';
    echo '<a href="/form/' . $frm_id . '">';
    echo $formulario['frm_clave'] . '. ' . $formulario['frm_titulo'];
    echo '</a>';

    $result = pg_query("SELECT * FROM esamyn.esa_encuesta WHERE enc_formulario = $frm_id");

    $count = 0;
    while ($encuesta = pg_fetch_array($result)) {
        $enc_id = $encuesta['enc_id'];
        if ($count === 0) {
            echo '<ol>';
            echo 'Encuestas ingresadas:';
        }
        echo '<li>';
        echo '<a href="/form/' . $frm_id . '/' . $enc_id . '">';
        echo $encuesta['enc_creado'];
        echo '</a>';

        $result_count = pg_query("SELECT COUNT(*) AS c FROM esamyn.esa_respuesta WHERE res_encuesta=$enc_id AND TRIM(res_valor_texto)<>'' UNION SELECT COUNT(*) AS c FROM esamyn.esa_pregunta WHERE prg_formulario=$frm_id");
        //echo("SELECT COUNT(*) AS 'count' FROM esamyn.esa_respuesta WHERE res_encuesta=$enc_id AND TRIM(res_valor_texto)<>'' UNION SELECT COUNT(*) AS 'count' FROM esamyn.esa_pregunta WHERE prg_formulario=$frm_id");
        $count_respuestas = pg_fetch_array($result_count, 0)[0];
        $count_preguntas = pg_fetch_array($result_count, 1)[0];
        echo ($encuesta['enc_finalizada'] ? " (finalizada)" : " (contestadas $count_respuestas de $count_preguntas preguntas)");

        echo '</li>';
        $count++;
    }

    if ($count > 0) {
        echo '</ol>';
    }

    echo '</li>';
}
echo '</ul>';
?>

