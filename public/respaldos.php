<div class="page-header">
<h1> Respaldos </h1>
</div>
<?php

if (isset($_POST['respaldar']) && !empty($_POST['respaldar'])) {

    $respaldar = $_POST['respaldar'];

    if ($respaldar == 'bdd') {
        $nombre = 'esamyn-bdd-'. date('Ymd-His') . '.backup';
        $comando = ('export PGPASSWORD="'.$bdd_config['password'].'" && pg_dump --file "'.$nombre.'" --host "'.$bdd_config['host'].'" --port "'.$bdd_config['port'].'" --username "'.$bdd_config['user'].'" --no-password --verbose --role "'.$bdd_config['user'].'" --format=c --blobs "'.$bdd_config['dbname'].'"');
        //echo $comando;
        exec($comando . ' 2>&1', $output);

        if (file_exists($nombre) && filesize($nombre) > 100) {
            $size = round(filesize($nombre) / (1024 * 1024));
            echo "<div class='alert alert-success'><h3>Respaldo de la base de datos generado con éxito</h3>";
            echo "<a class='btn btn-warning' href='/$nombre'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span> Descargar $nombre ($size MB)</a>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-danger'>Hubo un error al generar el respaldo de la base de datos.</div>";
        }
    } else if ($respaldar == 'app') {
        sleep(4);
        $nombre = 'esamyn-maven-master.zip';
        if (file_exists($nombre) && filesize($nombre) > 100) {
            $size = round(filesize($nombre) / (1024 * 1024));
            echo "<div class='alert alert-success'><h3>Respaldo del aplicativo generado con éxito</h3>";
            echo "<a class='btn btn-warning' href='/$nombre'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span> Descargar $nombre ($size MB)</a>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-danger'>Hubo un error al generar el respaldo del aplicativo</div>";
        }
    }
    echo "<hr>";
}


?>


<form method="POST">
<input type="hidden" name="respaldar" value="bdd">
<button class="btn btn-info"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Generar respaldo de la base de datos</button>
</form>

<form method="POST">
<input type="hidden" name="respaldar" value="app">
<button class="btn btn-info"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Generar respaldo del aplicativo</button>
</form>
