<div class="page-header">
<h1>Buscador de preguntas</h1>
</div>
<form method="POST" class="form-inline">
<input class="form-control" name="texto" placeholder="Ingrese al menos 3 caracteres">
<button class="btn btn-info"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Buscar</button>
</form>
<?php
    if (isset($_POST['texto']) && !empty($_POST['texto'])) {
        $texto = pg_escape_string(trim($_POST['texto']));

        if (strlen($texto) >= 3) {
        $result = q("
            SELECT *
            FROM esamyn.esa_pregunta,
            esamyn.esa_formulario
            WHERE prg_texto ILIKE '%$texto%'
            AND prg_formulario = frm_id
        ");
        if ($result) {
            foreach($result as $r){
                echo "<div class='alert'>";
                echo "<strong>{$r[prg_texto]}</strong>";
                $descripcion = "<a href='/form/{$r[frm_id]}'>{$r[frm_clave]}. {$r[frm_nombre]}</a>";
                echo "<div class=''>$descripcion</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>No hay coincidencias a <strong>$texto</strong>.</div>";
        }
        } else {
            echo "<div class='alert alert-warning'>Ingrese al menos 3 caracteres.</div>";
        } 
    }
?>
