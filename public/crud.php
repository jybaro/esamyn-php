<?php

$tabla = (isset($args[0]) ? $args[0] : null);
$accion = (isset($args[1]) ? $args[1] : '');

if (empty($tabla)) {
    //despliega todas las tablas
    $result = q("SELECT *
        FROM information_schema.columns
        WHERE table_schema = 'esamyn'
        ORDER BY table_name, data_type, is_nullable, column_name
        ");
    $table_name = null;
    $count_tabla = 0;
    $count_campo = 0;
    echo "<h1>Tablas del sistema</h1>";

    foreach($result as $r){
        if ($table_name != $r['table_name']) {
            $count_tabla++;
            $count_campo = 0;
            $table_name = $r['table_name'];
            $count_registros = q("SELECT COUNT(*) FROM esamyn.$table_name")[0]['count'];
            $agregar_fin_tabla = true;
            echo "</table>";
            echo "<hr>";
            echo "<h2>$count_tabla. <a href='/crud/$table_name'>$table_name</a> <span class='badge'>$count_registros registros</span></h2>";
            echo "<table class='table table-striped table-condensed table-hover'>";
            echo "<tr><th>&nbsp;</th><th>Campo</th><th>Tipo</th><th>Opcional</th><th>Valor por defecto</th></tr>";
        }
        $count_campo++;

        echo "<tr>";
        echo "<th>$count_campo.</th>";
        echo "<td>{$r[column_name]}</td>";
        echo "<td>{$r[data_type]}</td>";
        echo "<td>{$r[is_nullable]}</td>";
        echo "<td>{$r[column_default]}</td>";
        echo "</tr>";
        //var_dump($r);

    }
} else {
    //despliega solo una tabla
    $campos = q("SELECT *
        FROM information_schema.columns
        WHERE table_schema = 'esamyn'
        AND table_name   = '$tabla'
        ORDER BY data_type, is_nullable, column_name
        ");

    echo "<a href='/crud'><< Regresar al listado de tablas</a>";
    echo "<h1>Tabla $tabla</h1>";

    $result = q("SELECT * FROM esamyn.$tabla");


    echo "<table class='table table-striped table-condensed table-hover'>";
    echo "<tr>";
    echo "<th>&nbsp;</th>";
    foreach($campos as $campo){
        $valor = $campo['column_name'];
        echo "<th>$valor</th>";
    }
    echo "</tr>";

    $count = 0;
    foreach($result as $r){
        $count++;
        echo "<tr>";
        echo "<th>$count.</th>";
        foreach($campos as $campo){
            $valor = $r[$campo['column_name']];
            if (strlen($campo['column_name']) == 6 && strpos($campo['column_name'], '_id') == 3) {
                echo "<td><a href='#'>$valor</a></td>";
            } else {
                echo "<td>$valor</td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
}
