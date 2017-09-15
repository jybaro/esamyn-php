<?php

//$conn = pg_pconnect('host=104.131.40.56 port=9415 dbname=esamyn user=esamyn_user password=esamYn.007');
//$conn = pg_pconnect('host=45.55.147.66 port=6543 dbname=acess user=esamyn_user password=esamYn.2017');
//$conn = pg_pconnect('host=200.7.213.18 port=5432 dbname=acess user=esamyn_user password=esamYn.2017');
$conn = pg_pconnect('host=45.79.192.236 port=5432 dbname=acess user=esamyn_user password=esamYn.2017');

function q($sql, $callback = false) {
    global $conn;
    $data = null;
    $result = pg_query($conn, $sql);
    if ($callback) {
        while($row = pg_fetch_array($result)){
            $callback($row);
        }
    } else {
        $data = pg_fetch_all($result);
        //var_dump($data);
        //$data = count($data) === 1 ? (count($data[0]) === 1 ? $data[0][0] : $data[0]) : $data;
    }
    return $data;
}


