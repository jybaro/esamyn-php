<?php

function q($sql, $callback = false) {
    global $conn;
    $data = null;
    $result = pg_query($conn, $sql);
    if ($result) {
        if ($callback) {
            while($row = pg_fetch_array($result)){
                $callback($row);
            }
        } else {
            $data = pg_fetch_all($result);
            //var_dump($data);
            //$data = count($data) === 1 ? (count($data[0]) === 1 ? $data[0][0] : $data[0]) : $data;
        }
    }
    return $data;
}

