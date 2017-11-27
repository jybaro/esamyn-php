<?php
if (isset($_POST['respaldar']) && !empty($_POST['respaldar'])) {

    $nombre = date('Ymd-His');
    exec('export PGPASSWORD="esamYn.2017" && pg_dump --file "'.$nombre.'.backup" --host "45.79.192.236" --port "5432" --username "esamyn_user" --no-password --verbose --role "esamyn_user" --format=c --blobs "acess"');
    echo "<a class='btn btn-success' href='/$nombre.backup'><span class='glyphicon glyphicon-download-alt' aria-hidden='true'></span> Descargar respaldo generado</a>";
}


?>


<form method="POST">
<input type="hidden" name="respaldar" value="respaldar">
<button class="btn btn-info"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Generar Respaldo</button>
</form>
